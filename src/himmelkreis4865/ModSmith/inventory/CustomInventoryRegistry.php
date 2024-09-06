<?php

declare(strict_types=1);

namespace himmelkreis4865\ModSmith\inventory;

use BadMethodCallException;
use himmelkreis4865\ModSmith\inventory\entity\InventoryHolder;
use himmelkreis4865\ModSmith\inventory\helper\WindowBuilder;
use InvalidArgumentException;
use JsonException;
use JsonSerializable;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\network\mcpe\cache\StaticPacketCache;
use pocketmine\utils\SingletonTrait;
use function array_keys;
use function array_map;
use function array_search;
use function dechex;
use function implode;
use function is_subclass_of;
use function json_encode;
use function str_split;
use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

final class CustomInventoryRegistry {
	use SingletonTrait;

	/** @var class-string<CustomInventory>[] $inventories */
	private array $inventories = [];

	public function __construct() {
		$packet = StaticPacketCache::getInstance()->getAvailableActorIdentifiers();
		$tag = $packet->identifiers->getRoot();
		if ($tag instanceof CompoundTag and ($idList = $tag->getListTag("idlist")) instanceof ListTag) {
			$idList->push(CompoundTag::create()
				->setString("bid", "")
				->setByte("hasspawnegg", 0)
				->setString("id", InventoryHolder::NETWORK_ID)
				->setByte("summonable", 0)
			);
		}
	}

	/**
	 * @param class-string<CustomInventory> $inventoryClass
	 */
	public function register(string $inventoryClass): void {
		if (!is_subclass_of($inventoryClass, CustomInventory::class)) {
			throw new InvalidArgumentException("Inventory class of type $inventoryClass must be a subclass of " . CustomInventory::class);
		}
		$inventoryName = $inventoryClass::getName();
		if (isset($this->inventories[$inventoryName])) {
			throw new BadMethodCallException("Inventory $inventoryClass of type $inventoryName is already registered");
		}
		$this->inventories[$inventoryName] = $inventoryClass;
	}

	public function getIndexByName(string $name): ?int {
		if (!isset($this->inventories[$name])) {
			return null;
		}
		$index = array_search($name, array_keys($this->inventories), true);
		return (($index === false) ? null : $index);
	}

	public function windowNameToStringId(string $name): ?string {
		$index = $this->getIndexByName($name);
		if ($index === null) {
			return null;
		}
		return $this->windowIdToString($index);
	}

	/**
	 * @return array<string, string>
	 * @throws JsonException
	 */
	public function getFileContents(): array {
		$files = [
			"ui/_ui_defs.json" => json_encode([
				"ui_defs" => [
					...array_map(fn(string $inventoryName) => "ui/custom/" . $inventoryName . ".json", array_keys($this->inventories)),
					"ui/core_components.json"
				]], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
			"ui/chest_screen.json" => json_encode([
				"namespace" => "chest",
				"small_chest_screen@common.inventory_screen_common" => $this->chestScreenContent()
			], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)
		];

		foreach ($this->inventories as $name => $inventory) {
			$files["ui/custom/$name.json"] = json_encode(WindowBuilder::build($inventory), JSON_PRETTY_PRINT);
		}
		return $files;
	}

	/**
	 * @return array<string, string|array|JsonSerializable>
	 */
	private function chestScreenContent(): array {
		$variables = [
			[
				"requires" => "\$desktop_screen",
				"\$screen_content" => "chest.small_chest_panel",
				"\$screen_bg_content" => "common.screen_background"
			],
			[
				"requires" => "\$pocket_screen",
				"\$screen_content" => "pocket_containers.small_chest_panel"
			]
		];
		$offset = 0;
		foreach ($this->inventories as $name => $inventory) {
			$variables[] = [
				"requires" => "(not ((\$container_title_copy - '" . $this->windowIdToString($offset++) . "') = \$container_title_copy))",
				"\$screen_content" => $name . ".base_screen_panel"
			];
		}
		return [
			"\$container_title_copy" => "\$container_title",
			"variables" => $variables
		];
	}

	public function windowIdToString(int $id): string {
		return implode("", array_map(fn(string $char) => "§" . $char, str_split(dechex($id))));
	}
}
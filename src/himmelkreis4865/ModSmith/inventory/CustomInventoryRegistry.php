<?php

declare(strict_types=1);

namespace himmelkreis4865\ModSmith\inventory;

use BadMethodCallException;
use himmelkreis4865\ModSmith\inventory\entity\InventoryHolder;
use himmelkreis4865\ModSmith\inventory\helper\WindowBuilder;
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
use function json_encode;
use function str_split;
use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

final class CustomInventoryRegistry {
	use SingletonTrait;

	/**
	 * @var CustomInventory[] $inventories
	 * @phpstan-var array<string, CustomInventory> $inventories
	 */
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

	public function register(CustomInventory $inventory): void {
		if (isset($this->inventories[$inventory->name])) {
			throw new BadMethodCallException("Inventory " . $inventory::class . " of type " . $inventory->name . " is already registered");
		}
		$this->inventories[$inventory->name] = $inventory;
	}

	public function getIndexByName(string $name): ?int {
		if (!isset($this->inventories[$name])) {
			return null;
		}
		$index = array_search($name, array_keys($this->inventories), true);
		return ($index ?: null);
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
					...array_map(fn(CustomInventory $inventory) => "ui/custom/" . $inventory->name . ".json", $this->inventories),
					"ui/core_components.json"
				]], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
			"ui/chest_screen.json" => json_encode([
				"namespace" => "chest",
				"small_chest_screen@common.inventory_screen_common" => $this->chestScreenContent()
			], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)
		];

		foreach ($this->inventories as $inventory) {
			$files["ui/custom/" . $inventory->name . ".json"] = json_encode(WindowBuilder::build($inventory), JSON_PRETTY_PRINT);
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
		foreach ($this->inventories as $inventory) {
			$inventory->titleSuffix = $this->windowIdToString(++$offset);
			$variables[] = [
				"requires" => "(not ((\$container_title_copy - '" . $inventory->titleSuffix . "') = \$container_title_copy))", //
				"\$screen_content" => $inventory->name . ".base_screen_panel"
			];
		}
		return [
			"\$container_title_copy" => "\$container_title",
			"variables" => $variables
		];
	}

	public function windowIdToString(int $id): string {
		//$id++; // starting at one, not at zero todo: is this needed?
		return implode("", array_map(fn(string $char) => "§" . $char, str_split(dechex($id))));
	}
}
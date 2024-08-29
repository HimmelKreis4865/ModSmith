<?php

declare(strict_types=1);

namespace himmelkreis4865\ModSmith\inventory;

use BadMethodCallException;
use himmelkreis4865\ModSmith\inventory\component\Component;
use himmelkreis4865\ModSmith\inventory\component\internal\ItemContainer;
use himmelkreis4865\ModSmith\inventory\entity\InventoryHolder;
use himmelkreis4865\ModSmith\inventory\helper\Dimension;
use himmelkreis4865\ModSmith\inventory\types\AnchorType;
use InvalidArgumentException;
use pocketmine\inventory\SimpleInventory;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;
use pocketmine\network\mcpe\protocol\SetActorLinkPacket;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\types\entity\EntityLink;
use pocketmine\network\mcpe\protocol\types\inventory\WindowTypes;
use pocketmine\player\Player;
use function array_map;
use function array_values;
use function preg_match;
use function var_dump;

abstract class CustomInventory extends SimpleInventory {

	protected const DEFAULT_WIDTH = 176;

	protected const SMALL_CHEST_HEIGHT = 78;

	protected const LARGE_CHEST_HEIGHT = 132;

	protected const BOTTOM_HALF_HEIGHT = 93;

	private int $slotSize = 0;

	public string $titleSuffix;

	/**
	 * @param Component[] $components
	 */
	public function __construct(public readonly string $name, public Dimension $windowSize, public array $components, public readonly InventoryStructure $structure = new InventoryStructure()) {
		$this->windowSize = $this->windowSize->add(0, self::BOTTOM_HALF_HEIGHT);
		$this->setupInventory();
		if (preg_match("/[a-z_]+/", $name) !== 1) {
			throw new InvalidArgumentException("Window name $name does not match the required regex: [a-z_]+ (only lowercase letters and underscore)");
		}
		parent::__construct($this->slotSize);
	}

	private function setupInventory(): void {
		foreach ($this->components as $component) {
			$component->loadForInventory($this);
			if ($component instanceof ItemContainer) {
				$this->slotSize += $component->getSize();
			}
		}
		if ($this->structure->closeButton !== null) {
			$this->components[] = $this->structure->closeButton;
		}
	}

	/**
	 * @return array<string, mixed>
	 */
	public function encodeInventory(): array {
		return [
			"type" => "panel",
			"layer" => 10,
			"size" => new Dimension($this->windowSize->x,  $this->windowSize->y - self::BOTTOM_HALF_HEIGHT), // todo: is 88 the correct value for the bottom half height?
			"anchor_from" => AnchorType::TOP_LEFT->value,
			"anchor_to" => AnchorType::TOP_LEFT->value,
			"controls" => array_map(function(Component $component): array {
				$component->build($this);
				return [
					$component->getHeader() => $component->properties
				];
			}, array_values($this->components))
		];
	}

	public function receiveTransactionInternal(SlotChangeAction $action, Player $player): bool {
		$slots = 0;
		foreach ($this->components as $component) {
			if ($component instanceof ItemContainer) {
				if ($action->getSlot() <= ($slots + $component->getSize() - 1)) {
					if ($component->isLocked($action->getSlot())) {
						return true;
					}
					break;
				}
				$slots += $component->getSize();
			}
		}
		return $this->onTransaction($action, $player);
	}

	/**
	 * @return bool true if the event should be cancelled
	 */
	public function onTransaction(SlotChangeAction $action, Player $player): bool {
		return false;
	}

	public function onClose(Player $who): void {
		parent::onClose($who);
		InventoryPlayerSessions::getInstance()->reset($who);
	}

	public function getPackets(Player $player, int $id): array {
		$entity = new InventoryHolder($player->getLocation(), $this->slotSize);

		$inventoryId = CustomInventoryRegistry::getInstance()->windowNameToStringId($this->name);
		if ($inventoryId === null) {
			throw new BadMethodCallException("Inventory " . static::class . " is not registered! Make sure to use CustomInventoryRegistry->register() first");
		}
		$entity->setNameTag($inventoryId);
		InventoryPlayerSessions::getInstance()->set($player, $entity);
		$entity->spawnTo($player);
		$link = new EntityLink($player->getId(), $entity->getId(), EntityLink::TYPE_RIDER, true, true, 0);

		$pk = ContainerOpenPacket::entityInv($id, WindowTypes::CONTAINER, $entity->getId());
		$pk->blockPosition = BlockPosition::fromVector3($entity->getLocation());
		return [SetActorLinkPacket::create($link), $pk];
	}
}
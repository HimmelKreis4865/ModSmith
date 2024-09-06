<?php

declare(strict_types=1);

namespace himmelkreis4865\ModSmith\inventory;

use BadMethodCallException;
use himmelkreis4865\ModSmith\inventory\component\Component;
use himmelkreis4865\ModSmith\inventory\component\internal\ItemContainer;
use himmelkreis4865\ModSmith\inventory\entity\InventoryHolder;
use pocketmine\inventory\SimpleInventory;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;
use pocketmine\network\mcpe\protocol\SetActorLinkPacket;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\types\entity\EntityLink;
use pocketmine\network\mcpe\protocol\types\inventory\WindowTypes;
use pocketmine\player\Player;
use RuntimeException;

abstract class CustomInventory extends SimpleInventory {

	/** @var array<string, RootPanel> $rootCache */
	private static array $rootCache = [];

	public const DEFAULT_WIDTH = 176;

	public const SMALL_CHEST_HEIGHT = 78;

	public const LARGE_CHEST_HEIGHT = 132;

	public const BOTTOM_HALF_HEIGHT = 93;

	private int $slotSize = 0;

	public function __construct() {
		$this->setupInventory();
		parent::__construct($this->slotSize);
	}

	/**
	 * @return bool true if the event should be cancelled
	 */
	public function onTransaction(SlotChangeAction $action, Player $player): bool {
		return false;
	}

	abstract public static function getName(): string;

	abstract public static function build(): RootPanel;

	public function getRoot(): RootPanel {
		return self::$rootCache[static::getName()] ??= static::build();
	}

	private function setupInventory(): void {
		foreach ($this->getRoot()->getComponents() as $component) {
			$this->recursiveInventorySetup($component);
		}
		if ($this->slotSize === 0) {
			throw new RuntimeException("Inventories without any slots are not supported!");
		}
	}

	private function recursiveInventorySetup(Component $component): void {
		$component->loadForInventory($this);
		//$component->build(); // todo: is this a good idea for loading the children?
		if ($component instanceof ItemContainer) {
			$this->slotSize += $component->getSize();
		}
		foreach ($component->children as $child) {
			if ($child instanceof Component) {
				$this->recursiveInventorySetup($child);
			}
		}
	}

	public function receiveTransactionInternal(SlotChangeAction $action, Player $player): bool {
		$slots = 0;
		foreach ($this->getRoot()->getComponents() as $component) {
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

	public function onClose(Player $who): void {
		parent::onClose($who);
		InventoryPlayerSessions::getInstance()->reset($who);
	}

	public function getPackets(Player $player, int $id): array {
		$entity = new InventoryHolder($player->getLocation(), $this->slotSize);

		$inventoryId = CustomInventoryRegistry::getInstance()->windowNameToStringId(static::getName());
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
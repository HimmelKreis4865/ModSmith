<?php

declare(strict_types=1);

namespace himmelkreis4865\ModSmith\inventory;

use BadMethodCallException;
use himmelkreis4865\ModSmith\inventory\component\Component;
use himmelkreis4865\ModSmith\inventory\component\internal\ItemContainer;
use himmelkreis4865\ModSmith\inventory\entity\InventoryHolder;
use himmelkreis4865\ModSmith\inventory\helper\ProgressBarStateCollection;
use InvalidArgumentException;
use pocketmine\inventory\SimpleInventory;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;
use pocketmine\network\mcpe\protocol\SetActorLinkPacket;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\types\entity\EntityLink;
use pocketmine\network\mcpe\protocol\types\inventory\WindowTypes;
use pocketmine\player\Player;
use RuntimeException;
use function array_keys;
use function var_dump;

abstract class CustomInventory extends SimpleInventory {

	/** @var array<string, RootPanel> $rootCache */
	private static array $rootCache = [];

	public const DEFAULT_WIDTH = 176;

	public const SMALL_CHEST_HEIGHT = 78;

	public const LARGE_CHEST_HEIGHT = 132;

	public const BOTTOM_HALF_HEIGHT = 93;

	/** @var Item[][] */
	private array $progressBarCollections = [];

	/** @var int[] */
	private array $progressBarStates = [];

	private int $slotSize = 0;

	public function __construct() {
		$this->setupInventory();
		parent::__construct($this->slotSize);
		foreach ($this->progressBarStates as $slot => $state) {
			$this->setProgressBarState($slot, $state);
		}
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

	public function registerProgressBarCollection(int $slot, string $collectionName, int $initialState): void {
		$collection = ProgressBarStateCollection::getInstance()->get($collectionName);
		if ($collection === null) {
			throw new InvalidArgumentException("Progress Bar Collection $collectionName is not registered!");
		}
		$this->progressBarCollections[$slot] = $collection;
		$this->progressBarStates[$slot] = $initialState;
	}

	public function getProgressBarState(int $slot): ?int {
		if (!isset($this->progressBarCollections[$slot])) {
			return null;
		}
		return $this->progressBarStates[$slot] ?? 0;
	}

	public function setProgressBarState(int $slot, int $state): void {
		if (!isset($this->progressBarCollections[$slot])) {
			throw new InvalidArgumentException("Slot $slot is not a progress bar!");
		} else if ($state < 0 || count($this->progressBarCollections[$slot]) <= $state) {
			throw new InvalidArgumentException("State index $state out of range");
		}
		$this->progressBarStates[$slot] = $state;
		$this->setItem($slot, $item = $this->progressBarCollections[$slot][$state]);
		var_dump("setting slot $slot to " . $item::class);
	}

	public function nextProgressBarState(int $slot): void {
		if (!isset($this->progressBarCollections[$slot])) {
			throw new InvalidArgumentException("Slot $slot is not a progress bar!");
		}
		$this->setProgressBarState($slot, $this->getProgressBarState($slot));
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
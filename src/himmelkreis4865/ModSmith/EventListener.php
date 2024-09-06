<?php

declare(strict_types=1);

namespace himmelkreis4865\ModSmith;

use himmelkreis4865\ModSmith\inventory\CustomInventory;
use himmelkreis4865\ModSmith\inventory\InventoryPlayerSessions;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\transaction\action\SlotChangeAction;

final class EventListener implements Listener {

	public function onPlayerJoin(PlayerJoinEvent $event): void {
		$player = $event->getPlayer();
		$player->getNetworkSession()->getInvManager()?->getContainerOpenCallbacks()->add(function(int $id, Inventory $inventory) use($player): ?array {
			return ($inventory instanceof CustomInventory ? $inventory->getPackets($player, $id) : null);
		});
	}

	public function onQuit(PlayerQuitEvent $event): void {
		InventoryPlayerSessions::getInstance()->reset($event->getPlayer());
	}

	/**
	 * @priority HIGH
	 */
	public function onTransaction(InventoryTransactionEvent $event): void {
		$transaction = $event->getTransaction();
		foreach ($transaction->getActions() as $action) {

			/** @var CustomInventory $inventory */
			if ($action instanceof SlotChangeAction and ($inventory = $action->getInventory()) instanceof CustomInventory) {
				if ($inventory->receiveTransactionInternal($action, $transaction->getSource())) {
					$event->cancel();
				}
			}
		}
	}
}
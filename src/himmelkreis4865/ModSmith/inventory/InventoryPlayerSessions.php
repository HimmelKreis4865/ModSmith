<?php

declare(strict_types=1);

namespace himmelkreis4865\ModSmith\inventory;

use himmelkreis4865\ModSmith\inventory\entity\InventoryHolder;
use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;

final class InventoryPlayerSessions {
	use SingletonTrait;

	/** @var array<string, InventoryHolder> $playerEntities */
	private array $playerEntities = [];

	public function set(Player|string $player_or_name, ?InventoryHolder $entity): void {
		$name = ($player_or_name instanceof Player ? $player_or_name->getName() : $player_or_name);
		$old = $this->playerEntities[$name] ?? null;
		if ($old !== null) {
			$old->close();
		}
		if ($entity !== null) {
			$this->playerEntities[$name] = $entity;
		} else {
			unset($this->playerEntities[$name]);
		}
	}

	public function reset(Player|string $player_or_name): void {
		$this->set($player_or_name, null);
	}
}
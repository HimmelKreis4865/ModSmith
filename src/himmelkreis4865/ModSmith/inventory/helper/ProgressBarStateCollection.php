<?php

declare(strict_types=1);

namespace himmelkreis4865\ModSmith\inventory\helper;

use InvalidArgumentException;
use pocketmine\item\Item;
use pocketmine\utils\SingletonTrait;

final class ProgressBarStateCollection {
	use SingletonTrait;

	/** @var array<string, Item[]> $progressBarItems */
	private array $progressBarItems = [];

	public function add(string $collectionName, array $items): void {
		foreach ($items as $item) {
			if (!$item instanceof Item) {
				throw new InvalidArgumentException("Only Items are allowed as progress bar states");
			}
		}
		$this->progressBarItems[$collectionName] = $items;
	}

	public function get(string $collectionName): ?array {
		return $this->progressBarItems[$collectionName] ?? [];
	}
}
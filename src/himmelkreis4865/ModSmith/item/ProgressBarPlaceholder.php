<?php

declare(strict_types=1);

namespace himmelkreis4865\ModSmith\item;

use customiesdevs\customies\item\component\MaxStackSizeComponent;
use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;

class ProgressBarPlaceholder extends Item implements ItemComponents {
	use ItemComponentsTrait;

	public function __construct(ItemIdentifier $identifier, string $name = "Unknown") {
		parent::__construct($identifier, $name);
		$this->addComponent(new MaxStackSizeComponent(1));
		$this->initComponent($name, new CreativeInventoryInfo(CreativeInventoryInfo::CATEGORY_ITEMS));
	}

	public function getMaxStackSize(): int { return 1; }
}
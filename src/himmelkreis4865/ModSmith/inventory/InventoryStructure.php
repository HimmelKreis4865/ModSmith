<?php

declare(strict_types=1);

namespace himmelkreis4865\ModSmith\inventory;

use himmelkreis4865\ModSmith\inventory\component\internal\CloseButton;

final readonly class InventoryStructure {

	public function __construct(
		public bool $hotbar = true,
		public bool $inventory = true,
		public ?CloseButton $closeButton = null
	) {
	}
}
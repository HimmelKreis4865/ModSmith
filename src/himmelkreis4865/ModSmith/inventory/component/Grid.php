<?php

declare(strict_types=1);

namespace himmelkreis4865\ModSmith\inventory\component;

use himmelkreis4865\ModSmith\inventory\component\internal\ItemContainer;
use himmelkreis4865\ModSmith\inventory\component\internal\ItemTemplate;
use himmelkreis4865\ModSmith\inventory\CustomInventory;
use himmelkreis4865\ModSmith\inventory\helper\Anchor;
use himmelkreis4865\ModSmith\inventory\helper\Dimension;
use himmelkreis4865\ModSmith\inventory\helper\ObjectLink;
use himmelkreis4865\ModSmith\inventory\helper\Properties;
use himmelkreis4865\ModSmith\inventory\types\CollectionName;

class Grid extends Component implements ItemContainer {

	/**
	 * @param Dimension $gridDimensions The grid dimensions (x, y)
	 */
	public function __construct(
		public Dimension $gridDimensions,
		public CollectionName $collection = CollectionName::CONTAINER_ITEMS,
		public ItemTemplate $itemTemplate = new ItemTemplate(),
		Dimension $size = null,
		Dimension $offset = null,
		Anchor $anchor = new Anchor(),
	) {
		parent::__construct($offset, $size ?? new Dimension($this->gridDimensions->x * ($this->itemTemplate->size->x ?? 18), $this->gridDimensions->y * ($this->itemTemplate->size->y ?? 18)), $anchor);
	}

	public function isLocked(int $slot): bool {
		return $this->itemTemplate->locked;
	}

	public function getSize(): int {
		return $this->gridDimensions->x * $this->gridDimensions->y;
	}

	public function build(CustomInventory $inventory): void {
		parent::build($inventory);
		$this->properties[Properties::GRID_DIMENSIONS] = $this->gridDimensions;
		$this->properties[Properties::ITEM_COLLECTION_NAME] = $this->collection->value;
		$this->properties[Properties::MAXIMUM_GRID_ITEMS] = $this->gridDimensions->x * $this->gridDimensions->y;
		$this->properties[Properties::BINDINGS] = [];
		if ($this->itemTemplate !== null) {
			$this->properties[Properties::GRID_ITEM_TEMPLATE] = new ObjectLink($this->itemTemplate->getName());
			Component::$externalComponents[] = $this->itemTemplate;
		}
	}

	public function getIdentifier(): string {
		return "grid";
	}

	public function getParent(): ?string {
		return "common.container_grid";
	}
}
<?php

declare(strict_types=1);

namespace himmelkreis4865\ModSmith\inventory\component;

use himmelkreis4865\ModSmith\inventory\component\internal\ItemContainer;
use himmelkreis4865\ModSmith\inventory\component\internal\ItemTemplate;
use himmelkreis4865\ModSmith\inventory\helper\Anchor;
use himmelkreis4865\ModSmith\inventory\helper\Dimension;
use himmelkreis4865\ModSmith\inventory\helper\Properties;
use himmelkreis4865\ModSmith\inventory\types\CollectionName;

class Slot extends Component implements ItemContainer {

	/**
	 * @param array<string, Component> $children
	 */
	public function __construct(
		public int $index,
		public ItemTemplate $itemTemplate = new ItemTemplate(),
		?Dimension $offset = null,
		Anchor $anchor = new Anchor(),
		array $children = [],
		?int $layer = null,
		?string $name = null
	) {
		parent::__construct($offset, $this->itemTemplate->size, $anchor, $children, $layer, $name);
	}

	public function getSize(): int {
		return 1;
	}

	public function isLocked(int $slot): bool {
		return $this->itemTemplate->locked;
	}

	public function build(): void {
		$this->children[] = $this->itemTemplate->encodeTemplate($this->index, $this);
		$this->properties[Properties::TYPE] = "stack_panel";
		$this->properties[Properties::COLLECTION_NAME] = CollectionName::CONTAINER_ITEMS->value; // todo: should this be customizable again?
		$this->properties[Properties::ORIENTATION] = "horizontal";
		parent::build();
	}

	protected function getIdentifier(): string {
		return "custom_slot";
	}
}
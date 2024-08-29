<?php

declare(strict_types=1);

namespace himmelkreis4865\ModSmith\inventory\component;

use himmelkreis4865\ModSmith\inventory\component\internal\ItemContainer;
use himmelkreis4865\ModSmith\inventory\component\internal\ItemTemplate;
use himmelkreis4865\ModSmith\inventory\CustomInventory;
use himmelkreis4865\ModSmith\inventory\helper\Anchor;
use himmelkreis4865\ModSmith\inventory\helper\Dimension;
use himmelkreis4865\ModSmith\inventory\helper\Properties;
use himmelkreis4865\ModSmith\inventory\types\CollectionName;

class Slot extends Component implements ItemContainer {

	public function __construct(
		public int $index,
		public ItemTemplate $itemTemplate = new ItemTemplate(),
		public CollectionName $collectionName = CollectionName::CONTAINER_ITEMS,
		?Dimension $offset = null,
		Anchor $anchor = new Anchor()
	) {
		parent::__construct($offset, $this->itemTemplate->size, $anchor);
	}

	public function getSize(): int {
		return 1;
	}

	public function isLocked(int $slot): bool {
		return $this->itemTemplate->locked;
	}

	public function build(CustomInventory $inventory): void {
		parent::build($inventory);
		$this->properties[Properties::TYPE] = "stack_panel";
		$this->properties[Properties::COLLECTION_NAME] = $this->collectionName->value;
		$this->properties[Properties::ORIENTATION] = "horizontal";
		/*$this->properties[Properties::BINDINGS] = [
			[
				"binding_name" => "\$hover_text_binding_name",
				"binding_name_override" => "#hover_text",
				"binding_type" => "collection",
				"binding_collection_name" => $this->collectionName->value
			]
		];*/
		$this->properties[Properties::CONTROLS] = [
			$this->itemTemplate->encodeTemplate($inventory, $this->index, $this),
			/*[
				"text" => [
					"type" => "label",
					"text" => "\$text",
					"color" => [0, 0, 0]
				]
			]*/
		];

	}

	protected function getIdentifier(): string {
		return "custom_slot";
	}
}
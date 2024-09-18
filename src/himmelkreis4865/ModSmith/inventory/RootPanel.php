<?php

declare(strict_types=1);

namespace himmelkreis4865\ModSmith\inventory;

use himmelkreis4865\ModSmith\inventory\component\Component;
use himmelkreis4865\ModSmith\inventory\component\Grid;
use himmelkreis4865\ModSmith\inventory\component\Image;
use himmelkreis4865\ModSmith\inventory\component\internal\CloseButton;
use himmelkreis4865\ModSmith\inventory\component\internal\ItemTemplate;
use himmelkreis4865\ModSmith\inventory\component\Text;
use himmelkreis4865\ModSmith\inventory\helper\Anchor;
use himmelkreis4865\ModSmith\inventory\helper\Dimension;
use himmelkreis4865\ModSmith\inventory\types\AnchorType;
use himmelkreis4865\ModSmith\inventory\types\CollectionName;
use function array_map;
use function array_values;

final class RootPanel {

	/** @var Component[] $components */
	private array $components = [];

	public bool $closeButtonEnabled = true;

	public bool $hotbarEnabled = true;

	public bool $inventoryEnabled = true;

	public bool $inventoryLabelEnabled = true;

	public ?Image $backgroundOverwrite = null;

	public ?CloseButton $closeButtonOverwrite = null;

	public ?Grid $customHotbarGrid = null;

	public ?Grid $customInventoryGrid = null;

	public ?Text $playerInventoryLabel = null;


	public function __construct(
		public readonly Dimension $size = new Dimension(CustomInventory::DEFAULT_WIDTH, CustomInventory::SMALL_CHEST_HEIGHT)
	) {}

	public function add(Component $component): void {
		$this->components[$component->getName()] = $component;
	}

	public function get(string $componentName): ?Component {
		return $this->components[$componentName] ?? null;
	}

	public function setCloseButtonDisabled(): void {
		$this->closeButtonEnabled = false;
	}

	public function setCloseButton(CloseButton $button): void {
		$this->closeButtonOverwrite = $button;
		$this->add($button);
		$this->closeButtonEnabled = true;
	}

	public function setBackgroundImage(Image $image): void {
		$this->backgroundOverwrite = $image;
	}

	public function setHotbarGrid(Grid $grid): void {
		$grid->gridDimensions = new Dimension(9, 1); // the grid size of 9 slots is hardcoded and cannot be changed
		$grid->collection = CollectionName::HOTBAR_ITEMS;
		$grid->itemTemplate->collectionName = CollectionName::HOTBAR_ITEMS;
		$this->customHotbarGrid = $grid;
		$this->hotbarEnabled = true;
	}

	public function disableHotbarGrid(): void {
		$this->hotbarEnabled = false;
	}

	public function getDefaultHotbarGrid(): Grid {
		return new Grid(
			new Dimension(9, 1),
			CollectionName::HOTBAR_ITEMS,
			new ItemTemplate(collectionName: CollectionName::HOTBAR_ITEMS),
			new Dimension(162, 18),
			new Dimension(0, -5),
			new Anchor(AnchorType::BOTTOM_MIDDLE, AnchorType::BOTTOM_MIDDLE)
		);
	}

	public function setPlayerInventoryGrid(Grid $grid): void {
		$grid->gridDimensions = new Dimension(9, 3);
		$grid->collection = CollectionName::INVENTORY_ITEMS;
		$grid->itemTemplate->collectionName = CollectionName::INVENTORY_ITEMS;
		$this->customInventoryGrid = $grid;
		$this->inventoryEnabled = true;
	}

	public function disableInventoryGrid(): void {
		$this->inventoryEnabled = false;
	}

	public function getDefaultInventoryGrid(): Grid {
		return new Grid(
			new Dimension(9, 3),
			CollectionName::INVENTORY_ITEMS,
			new ItemTemplate(collectionName: CollectionName::INVENTORY_ITEMS),
			new Dimension(162, 54),
			new Dimension(0, -26),
			new Anchor(AnchorType::BOTTOM_MIDDLE, AnchorType::BOTTOM_MIDDLE)
		);
	}

	public function disablePlayerInventoryTitle(): void {
		$this->inventoryLabelEnabled = false;
	}

	public function setPlayerInventoryTitle(Text $text): void {
		$this->playerInventoryLabel = $text;
	}

	public function getDefaultPlayerInventoryTitle(): Text {
		return new Text(
			"container.inventory",
			offset: new Dimension(7, 3)
		);
	}

	/**
	 * @return Component[]
	 */
	public function getComponents(): array {
		return $this->components;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function encodeInventory(): array {
		return [
			"type" => "panel",
			"layer" => 10,
			"size" => $this->size/*->add(0, CustomInventory::BOTTOM_HALF_HEIGHT)*/, //todo: add something here? is subtracting here right?
			"anchor_from" => AnchorType::TOP_LEFT->value,
			"anchor_to" => AnchorType::TOP_LEFT->value,
			"controls" => array_map(function(Component $component): array {
				$component->build();
				return [
					$component->getHeader() => $component->properties
				];
			}, array_values($this->components))
		];
	}
}
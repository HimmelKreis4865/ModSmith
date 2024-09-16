<?php

declare(strict_types=1);

namespace himmelkreis4865\ModSmith\inventory\component;

use BadMethodCallException;
use himmelkreis4865\ModSmith\inventory\component\internal\ItemContainer;
use himmelkreis4865\ModSmith\inventory\component\internal\ItemTemplate;
use himmelkreis4865\ModSmith\inventory\CustomInventory;
use himmelkreis4865\ModSmith\inventory\helper\Anchor;
use himmelkreis4865\ModSmith\inventory\helper\Dimension;
use himmelkreis4865\ModSmith\inventory\helper\HoverBehaviour;
use himmelkreis4865\ModSmith\inventory\helper\ObjectLink;
use himmelkreis4865\ModSmith\inventory\helper\Properties;
use himmelkreis4865\ModSmith\inventory\types\CollectionName;
use himmelkreis4865\ModSmith\ModSmith;
use himmelkreis4865\ModSmith\utils\Texture;
use InvalidArgumentException;
use pocketmine\item\Item;
use RuntimeException;
use function count;

class ProgressBar extends Component implements ItemContainer {

	private ItemTemplate $itemTemplate;

	/**
	 * @param array<string, Component> $children
	 */
	public function __construct(
		private readonly string $collectionName,
		private readonly int $itemSlot,
		private readonly int $initialState = 0,
		?ItemTemplate $template = null,
		?Dimension $offset = null,
		?Dimension $size = null,
		Anchor $anchor = new Anchor(),
		array $children = [],
		?int $layer = null,
		?string $name = null
	) {
		parent::__construct($offset, $size, $anchor, $children, $layer, $name);
		$this->itemTemplate = $template ?? new ItemTemplate(
			Texture::fromFile(ModSmith::getInstance()->getResourceFolder() . "transparent.png", "textures/ui/transparent.png"),
			new HoverBehaviour(
				Texture::fromTexture("textures/ui/transparent.png"),
				0,
				false,
				hoverTextEnabled: false
			),
			itemRendererSize: $this->size,
			locked: true,
			size: $this->size
		);
	}

	public function getSize(): int {
		return 1;
	}

	public function isLocked(int $slot): bool {
		return true;
	}

	public function build(): void {
		self::$externalComponents[] = $slot = new Slot($this->itemSlot, $this->itemTemplate);

		$this->properties[Properties::TYPE] = "panel";
		$this->properties[Properties::CONTROLS] = [
			[
				$slot->getName() . "@" . (new ObjectLink($slot->getName()))->__toString() => []
			]
		];
		// $this->notifyStateUpdate(); todo: is this needed?
		parent::build();
	}

	public function loadForInventory(CustomInventory $inventory): void {
		$inventory->registerProgressBarCollection($this->itemSlot, $this->collectionName, $this->initialState);
	}
	protected function getIdentifier(): string {
		return "progress_bar";
	}
}
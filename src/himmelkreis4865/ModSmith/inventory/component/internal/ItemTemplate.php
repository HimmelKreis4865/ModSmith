<?php

declare(strict_types=1);

namespace himmelkreis4865\ModSmith\inventory\component\internal;

use himmelkreis4865\ModSmith\inventory\component\Component;
use himmelkreis4865\ModSmith\inventory\component\Image;
use himmelkreis4865\ModSmith\inventory\helper\Anchor;
use himmelkreis4865\ModSmith\inventory\helper\Dimension;
use himmelkreis4865\ModSmith\inventory\helper\HoverBehaviour;
use himmelkreis4865\ModSmith\inventory\helper\ObjectLink;
use himmelkreis4865\ModSmith\inventory\helper\Properties;
use himmelkreis4865\ModSmith\utils\Texture;
use RuntimeException;

final class ItemTemplate extends Component {

	public function __construct(
		public ?Texture $backgroundImage = null,
		public ?HoverBehaviour $hoverBehaviour = null, // fixme:  this is not working right now
		public ?Dimension $itemRendererSize = null,
		public bool $locked = false,
		?Dimension $offset = null,
		?Dimension $size = null,
		Anchor $anchor = new Anchor()
	) {
		parent::__construct($offset, $size ?? new Dimension(18, 18), $anchor);
	}

	public function jsonSerialize(): array {
		throw new RuntimeException("Cannot serialize the raw item template. Use a Slot/Grid instead.");
	}

	public function build(): void {
		if ($this->backgroundImage !== null) {
			Component::$externalComponents[] = $img = new Image($this->backgroundImage);
			$this->properties[Properties::BACKGROUND_IMAGES] = new ObjectLink($img->getName());
		}
		if ($this->hoverBehaviour !== null) {
			Component::$externalComponents[] = $hoverController = new HighlightController($this->hoverBehaviour);
			Component::$externalComponents[] = $buttonRef = new ButtonRef(new ObjectLink($hoverController->getName()), $this->locked);
			$this->properties[Properties::BUTTON_REF] = new ObjectLink($buttonRef->getName());
			if (!$this->hoverBehaviour->hoverTextEnabled) {
				$this->properties[Properties::HOVER_TEXT_BINDING] = "core_components.empty";
			}
		}
		if ($this->itemRendererSize !== null) {
			$this->properties[Properties::ITEM_RENDERER_SIZE] = $this->itemRendererSize;
		}
		$this->properties[Properties::ITEM_CELL_SIZE] = $this->size ?? new Dimension(18, 18); // add nullable operator for preventing warnings, size is never null here
		parent::build();
	}

	/**
	 * @return array<string, array<string, mixed>>
	 */
	public function encodeTemplate(int $index, Component $parent): array {
		$this->properties[Properties::COLLECTION_INDEX] = $index;
		$this->build();
		return [ $index . "@chest.chest_grid_item" => $this->properties];
	}

	public function getIdentifier(): string {
		return "item_template";
	}

	public function getParent(): ?string {
		return "common.container_item";
	}
}
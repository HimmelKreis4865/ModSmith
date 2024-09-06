<?php

declare(strict_types=1);

namespace himmelkreis4865\ModSmith\inventory\component\internal;

use himmelkreis4865\ModSmith\inventory\component\Component;
use himmelkreis4865\ModSmith\inventory\helper\Anchor;
use himmelkreis4865\ModSmith\inventory\helper\Dimension;
use himmelkreis4865\ModSmith\inventory\helper\Properties;
use himmelkreis4865\ModSmith\utils\Texture;

class CloseButton extends Component {

	/**
	 * @param array<string, Component> $children
	 */
	public function __construct(
		public Texture $texture,
		public Texture $hoverTexture,
		?Dimension $offset = null,
		?Dimension $size = null,
		Anchor $anchor = new Anchor(),
		array $children = [],
		?int $layer = null,
		?string $name = null
	) {
		parent::__construct($offset, $size, $anchor, $children, $layer, $name);
	}

	public function build(): void {
		$this->properties[Properties::CLOSE_BUTTON_TEXTURE] = $this->texture;
		$this->properties[Properties::CLOSE_BUTTON_TEXTURE_HOVER] = $this->hoverTexture;
		parent::build($inventory);
	}

	protected function getIdentifier(): string {
		return "close_button";
	}

	public function getParent(): ?string {
		return "core_components.close_button";
	}
}
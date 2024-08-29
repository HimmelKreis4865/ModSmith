<?php

declare(strict_types=1);

namespace himmelkreis4865\ModSmith\inventory\component\internal;

use himmelkreis4865\ModSmith\inventory\component\Component;
use himmelkreis4865\ModSmith\inventory\CustomInventory;
use himmelkreis4865\ModSmith\inventory\helper\Anchor;
use himmelkreis4865\ModSmith\inventory\helper\Dimension;
use himmelkreis4865\ModSmith\inventory\helper\Properties;
use himmelkreis4865\ModSmith\utils\Texture;

class CloseButton extends Component {

	public function __construct(
		public Texture $texture,
		public Texture $hoverTexture,
		?Dimension $offset = null,
		?Dimension $size = null,
		Anchor $anchor = new Anchor()
	) {
		parent::__construct($offset, $size, $anchor);
	}

	public function build(CustomInventory $inventory): void {
		parent::build($inventory);
		$this->properties[Properties::CLOSE_BUTTON_TEXTURE] = $this->texture;
		$this->properties[Properties::CLOSE_BUTTON_TEXTURE_HOVER] = $this->hoverTexture;
	}

	protected function getIdentifier(): string {
		return "close_button";
	}

	public function getParent(): ?string {
		return "core_components.close_button";
	}
}
<?php

declare(strict_types=1);

namespace himmelkreis4865\ModSmith\inventory\component;

use himmelkreis4865\ModSmith\inventory\CustomInventory;
use himmelkreis4865\ModSmith\inventory\helper\Anchor;
use himmelkreis4865\ModSmith\inventory\helper\Dimension;
use himmelkreis4865\ModSmith\inventory\helper\Properties;
use himmelkreis4865\ModSmith\utils\Texture;

class Image extends Component {

	public function __construct(
		public Texture $texture,
		public bool $keepRatio = true,
		?Dimension $offset = null,
		?Dimension $size = null,
		Anchor $anchor = new Anchor()
	) {
		parent::__construct($offset, $size, $anchor);
	}

	public function build(CustomInventory $inventory): void {
		parent::build($inventory);
		$this->properties[Properties::TEXTURE] = $this->texture;
		$this->properties[Properties::TYPE] = "image";
		$this->properties[Properties::KEEP_RATIO] = $this->keepRatio;
	}

	protected function getIdentifier(): string {
		return "image";
	}
}
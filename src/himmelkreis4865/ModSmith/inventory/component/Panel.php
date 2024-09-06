<?php

declare(strict_types=1);

namespace himmelkreis4865\ModSmith\inventory\component;

use himmelkreis4865\ModSmith\inventory\helper\Anchor;
use himmelkreis4865\ModSmith\inventory\helper\Dimension;
use himmelkreis4865\ModSmith\inventory\helper\Properties;

class Panel extends Component {

	public function __construct(

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
		$this->properties[Properties::TYPE] = "panel";
		parent::build();
	}

	protected function getIdentifier(): string {
		return "panel";
	}
}
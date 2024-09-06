<?php

declare(strict_types=1);

namespace himmelkreis4865\ModSmith\inventory\component;

use himmelkreis4865\ModSmith\inventory\helper\Anchor;
use himmelkreis4865\ModSmith\inventory\helper\Dimension;
use himmelkreis4865\ModSmith\inventory\helper\ObjectLink;
use himmelkreis4865\ModSmith\inventory\helper\Properties;
use RuntimeException;

class ScrollPanel extends Component {

	public function __construct(
		public readonly Component $content,
		?Dimension $offset = null,
		?Dimension $size = null,
		Anchor $anchor = new Anchor(),
		?int $layer = null,
		?string $name = null
	) {
		throw new RuntimeException("ScrollPanels are not yet available!");
		// using this trick to set the content component as control, this will not be set in the pack
		parent::__construct($offset, $size, $anchor, [$content], $layer, $name);
	}

	public function build(): void {
		$this->children = [];
		self::$externalComponents[] = $this->content;
		$this->properties[Properties::SCROLLING_CONTENT] = new ObjectLink($this->content->getName()); // todo: why is this not found?
		parent::build();
	}

	protected function getIdentifier(): string {
		return "scroll_panel";
	}

	public function getParent(): ?string {
		return "common.scrolling_panel";
	}
}
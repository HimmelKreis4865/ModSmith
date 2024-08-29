<?php

declare(strict_types=1);

namespace himmelkreis4865\ModSmith\inventory\component;

use himmelkreis4865\ModSmith\inventory\CustomInventory;
use himmelkreis4865\ModSmith\inventory\helper\Anchor;
use himmelkreis4865\ModSmith\inventory\helper\Dimension;
use RuntimeException;

/**
 * @noop This will be released in future versions
 */
class SearchBar extends Component {

	public function __construct(
		?Dimension $offset = null,
		?Dimension $size = null,
		Anchor $anchor = new Anchor()
	) {
		throw new RuntimeException("Search Bar is not ready for usage yet");
		parent::__construct($offset, $size, $anchor);
	}

	public function build(CustomInventory $inventory): void {
		parent::build($inventory);
	}

	public function getIdentifier(): string {
		return "search_bar";
	}

	public function getParent(): ?string {
		return "core_components.search_content";
	}
}
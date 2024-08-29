<?php

declare(strict_types=1);

namespace himmelkreis4865\ModSmith\inventory\component\internal;

use himmelkreis4865\ModSmith\inventory\component\Component;
use himmelkreis4865\ModSmith\inventory\CustomInventory;
use himmelkreis4865\ModSmith\inventory\helper\Anchor;
use himmelkreis4865\ModSmith\inventory\helper\Dimension;
use himmelkreis4865\ModSmith\inventory\helper\ObjectLink;
use himmelkreis4865\ModSmith\inventory\helper\Properties;

class ButtonRef extends Component {

	public function __construct(public ObjectLink $highlightController, public bool $readonly, ?Dimension $offset = null, ?Dimension $size = null, Anchor $anchor = new Anchor()) {
		parent::__construct($offset, $size, $anchor);
	}

	public function build(CustomInventory $inventory): void {
		parent::build($inventory);
		$this->properties[Properties::HIGHLIGHT_CONTROL] = $this->highlightController;
		$this->properties[Properties::ENABLED] = !$this->readonly;
	}

	protected function getIdentifier(): string {
		return "button_ref";
	}

	public function getParent(): ?string {
		return "core_components.button";
		//return "common.container_slot_button_prototype";
	}
}
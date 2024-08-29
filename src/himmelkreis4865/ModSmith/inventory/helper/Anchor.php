<?php

declare(strict_types=1);

namespace himmelkreis4865\ModSmith\inventory\helper;

use himmelkreis4865\ModSmith\inventory\types\AnchorType;

final readonly class Anchor {

	public function __construct(public AnchorType $from = AnchorType::TOP_LEFT, public AnchorType $to = AnchorType::TOP_LEFT) {
	}
}
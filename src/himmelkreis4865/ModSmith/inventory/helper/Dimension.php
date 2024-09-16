<?php

declare(strict_types=1);

namespace himmelkreis4865\ModSmith\inventory\helper;

use JsonSerializable;
use function intval;

final readonly class Dimension implements JsonSerializable {

	public function __construct(public float $x, public float $y) {}

	public function add(float $x, float $y = 0): Dimension {
		return new Dimension($this->x + $x, $this->y + $y);
	}

	public function multiply(float $x, float $y): Dimension {
		return new Dimension($this->x * $x, $this->y * $y);
	}

	public function __toString(): string {
		return "Dimension(" . $this->x . ", " . $this->y . ")";
	}

	/**
	 * @return int[]
	 */
	public function jsonSerialize(): array {
		return [$this->x, $this->y];
	}
}
<?php

declare(strict_types=1);

namespace himmelkreis4865\ModSmith\inventory\helper;

use JsonSerializable;
use function intval;

final readonly class Dimension implements JsonSerializable {

	public function __construct(public int $x, public int $y) {}

	public function add(int $x, int $y): Dimension {
		return new Dimension($this->x + $x, $this->y + $y);
	}

	public function multiply(float $x, float $y): Dimension {
		return new Dimension(intval($this->x * $x), intval($this->y * $y));
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
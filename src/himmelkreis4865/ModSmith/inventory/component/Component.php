<?php

declare(strict_types=1);

namespace himmelkreis4865\ModSmith\inventory\component;

use himmelkreis4865\ModSmith\inventory\CustomInventory;
use himmelkreis4865\ModSmith\inventory\helper\Anchor;
use himmelkreis4865\ModSmith\inventory\helper\Dimension;
use himmelkreis4865\ModSmith\inventory\helper\Properties;
use JsonSerializable;
use function is_iterable;

abstract class Component implements JsonSerializable {

	private static int $identifiers = 0;

	/** @var Component[] $externalComponents */
	public static array $externalComponents = [];

	private string $name;

	/** @var array<string, mixed> $properties */
	public array $properties = [];

	public function __construct(
		public ?Dimension $offset = null,
		public ?Dimension $size = null,
		public Anchor $anchor = new Anchor()
	) {
		$this->name = $this->getIdentifier() . "_" . (self::$identifiers++);
	}

	public function build(CustomInventory $inventory): void {
		if ($this->offset !== null) $this->properties[Properties::OFFSET] = $this->offset;
		if ($this->size !== null) $this->properties[Properties::SIZE] = $this->size;
		$this->properties[Properties::ANCHOR_FROM] = $this->anchor->from->value;
		$this->properties[Properties::ANCHOR_TO] = $this->anchor->to->value;

		if (is_iterable($this->properties[Properties::CONTROLS] ?? null)) {
			foreach ($this->properties[Properties::CONTROLS] as $control) {
				if ($control instanceof Component) {
					$control->build($inventory);
				}
			}
		}
	}

	public function loadForInventory(CustomInventory $inventory): void {

	}

	abstract protected function getIdentifier(): string;

	public function getName(): string {
		return $this->name;
	}

	public function getHeader(): string {
		return $this->name . ($this->getParent() === null ? "" : "@" . $this->getParent());
	}

	/**
	 * @return array<string, mixed>
	 */
	public function jsonSerialize(): array {
		return $this->properties;
	}

	public function getParent(): ?string {
		return null;
	}
}
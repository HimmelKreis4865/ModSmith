<?php

declare(strict_types=1);

namespace himmelkreis4865\ModSmith\inventory\component;

use himmelkreis4865\ModSmith\inventory\CustomInventory;
use himmelkreis4865\ModSmith\inventory\helper\Anchor;
use himmelkreis4865\ModSmith\inventory\helper\Dimension;
use himmelkreis4865\ModSmith\inventory\helper\Properties;
use JsonSerializable;

abstract class Component implements JsonSerializable {

	private static int $identifiers = 0;

	/** @var Component[] $externalComponents */
	public static array $externalComponents = [];

	public readonly string $name;

	/** @var array<string, mixed> $properties */
	public array $properties = [];

	/**
	 * @param array<string, Component> $children
	 */
	public function __construct(
		public readonly ?Dimension $offset = null,
		public readonly ?Dimension $size = null,
		public readonly Anchor $anchor = new Anchor(),
		public array $children = [],
		public ?int $layer = null,
		?string $name = null
	) {
		$this->name = $name ?? ($this->getIdentifier() . "_" . (self::$identifiers++));
	}

	public function build(): void {
		if ($this->offset !== null) $this->properties[Properties::OFFSET] = $this->offset;
		if ($this->size !== null) $this->properties[Properties::SIZE] = $this->size;
		if ($this->layer !== null && $this->layer > 0) $this->properties[Properties::LAYER] = $this->layer;

		$this->properties[Properties::ANCHOR_FROM] = $this->anchor->from->value;
		$this->properties[Properties::ANCHOR_TO] = $this->anchor->to->value;
		$controls = [];
		if ($this->children) {
			foreach ($this->children as $k => $control) {
				if ($control instanceof Component) {
					$control->build();
					$controls[$control->getHeader()] = $control;
					continue;
				}
				$controls[$k] = $control;
			}
			$this->properties[Properties::CONTROLS] = $controls;
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
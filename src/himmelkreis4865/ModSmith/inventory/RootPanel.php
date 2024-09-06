<?php

declare(strict_types=1);

namespace himmelkreis4865\ModSmith\inventory;

use himmelkreis4865\ModSmith\inventory\component\Component;
use himmelkreis4865\ModSmith\inventory\helper\Dimension;
use himmelkreis4865\ModSmith\inventory\types\AnchorType;
use function array_map;
use function array_values;

final class RootPanel {

	/** @var Component[] $components */
	private array $components = [];

	// todo: allow customization for bottom half
	public function __construct(
		public readonly Dimension $size = new Dimension(CustomInventory::DEFAULT_WIDTH, CustomInventory::SMALL_CHEST_HEIGHT),
		public readonly InventoryStructure $structure = new InventoryStructure()
	) {
		if ($this->structure->closeButton !== null) {
			$this->components[] = $this->structure->closeButton;
		}
	}

	public function add(Component $component): void {
		$this->components[$component->getName()] = $component;
	}

	public function get(string $componentName): ?Component {
		return $this->components[$componentName] ?? null;
	}

	/**
	 * @return Component[]
	 */
	public function getComponents(): array {
		return $this->components;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function encodeInventory(): array {
		return [
			"type" => "panel",
			"layer" => 10,
			"size" => $this->size/*->add(0, CustomInventory::BOTTOM_HALF_HEIGHT)*/, //todo: add something here? is subtracting here right?
			"anchor_from" => AnchorType::TOP_LEFT->value,
			"anchor_to" => AnchorType::TOP_LEFT->value,
			"controls" => array_map(function(Component $component): array {
				$component->build($this);
				return [
					$component->getHeader() => $component->properties
				];
			}, array_values($this->components))
		];
	}
}
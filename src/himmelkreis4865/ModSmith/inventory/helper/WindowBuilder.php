<?php

declare(strict_types=1);

namespace himmelkreis4865\ModSmith\inventory\helper;

use himmelkreis4865\ModSmith\inventory\component\Component;
use himmelkreis4865\ModSmith\inventory\CustomInventory;
use function array_shift;
use function json_encode;
use function str_replace;
use const JSON_PRETTY_PRINT;

final class WindowBuilder {

	/**
	 * @param class-string<CustomInventory> $inventoryClass
	 * @return array<string, mixed>
	 */
	public static function build(string $inventoryClass): array {
		$name = $inventoryClass::getName();
		$root = $inventoryClass::build();

		ObjectLink::setCurrentNamespace($name);
		Component::$externalComponents = [];

		$controls = [
			[ "inventory_screen@" . $name . ".inventory_screen" => [] ],
			[ "inventory_take_progress_icon_button@common.inventory_take_progress_icon_button" => [] ]
		];
		$panelHeightAddition = 0;
		if ($root->structure->inventory) {
			$controls[] = [ "inventory_panel_bottom_half_with_label@common.inventory_panel_bottom_half_with_label" => [] ];
			$panelHeightAddition += 71; // todo: correct?
		}
		if ($root->structure->hotbar) {
			$controls[] = [ "hotbar_grid@common.hotbar_grid_template" => [] ];
			$panelHeightAddition += 22; // todo: correct?
		}
		$baseComponents = [
			"namespace" => $name,
			"base_screen_panel" => [
				"type" => "panel",
				"controls" => [
					[ "container_gamepad_helpers@common.container_gamepad_helpers" => [] ],
					[ "flying_item_renderer@common.flying_item_renderer" => [
						"layer" => 14
					] ],
					[ "selected_item_details_factory@common.selected_item_details_factory" => [] ],
					[ "item_lock_notification_factory@common.item_lock_notification_factory" => [] ],
					[ "root_panel@common.root_panel" => [
						"size" => $root->size->add(0, $panelHeightAddition), // add bottom half if enabled
						"layer" => 1,
						"controls" => [
							[ "common_panel@common.common_panel" => [
								"\$show_close_button" => ($root->structure->closeButton === null)
							] ],
							[ "chest_panel" => [
								"type" => "panel",
								"layer" => 2,
								"controls" => $controls
							] ],
							[ "inventory_selected_icon_button@common.inventory_selected_icon_button" => [] ],
							[ "gamepad_cursor@common.gamepad_cursor_button" => [] ]
						]
					] ]
				]
			],
			"inventory_screen" => $root->encodeInventory()
		];
		while (Component::$externalComponents) {
			$component = array_shift(Component::$externalComponents);
			if ($component !== null) {
				$component->build();
				$baseComponents[$component->getHeader()] = $component->properties;
			}
		}
		return $baseComponents;
	}

	/*
	 * @param string $identifier
	 * @param Component[] $components
	 * @return void
	 *
	public static function build(string $identifier, array $components): string {
		$file = ["namespace" => $identifier];

		foreach ($components as $component) {
			$file[$component->getHeader()] = $component;
		}
		return str_replace(["%NAMESPACE%"], [$identifier], json_encode($file, JSON_PRETTY_PRINT));
	}*/
}
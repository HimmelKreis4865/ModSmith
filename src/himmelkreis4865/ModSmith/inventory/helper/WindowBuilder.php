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
	 * @return array<string, mixed>
	 */
	public static function build(CustomInventory $inventory): array {
		ObjectLink::setCurrentNamespace($inventory->name);
		Component::$externalComponents = [];

		$controls = [
			[ "inventory_screen@" . $inventory->name . ".inventory_screen" => [] ],
			[ "inventory_take_progress_icon_button@common.inventory_take_progress_icon_button" => [] ]
		];
		if ($inventory->structure->inventory) {
			$controls[] = [ "inventory_panel_bottom_half_with_label@common.inventory_panel_bottom_half_with_label" => [] ];
		}
		if ($inventory->structure->hotbar) {
			$controls[] = [ "hotbar_grid@common.hotbar_grid_template" => [] ];
		}
		$baseComponents = [
			"namespace" => $inventory->name,
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
						"size" => $inventory->windowSize,
						"layer" => 1,
						"controls" => [
							[ "common_panel@common.common_panel" => [
								"\$show_close_button" => ($inventory->structure->closeButton === null)
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
			"inventory_screen" => $inventory->encodeInventory()
		];
		while (Component::$externalComponents) {
			$component = array_shift(Component::$externalComponents);
			if ($component !== null) {
				$component->build($inventory);
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
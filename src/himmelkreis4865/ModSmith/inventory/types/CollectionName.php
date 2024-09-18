<?php

declare(strict_types=1);

namespace himmelkreis4865\ModSmith\inventory\types;

enum CollectionName: string {

	case CONTAINER_ITEMS = "container_items";

	case HOTBAR_ITEMS = "hotbar_items";

	case INVENTORY_ITEMS = "inventory_items";
}
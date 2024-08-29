<?php

declare(strict_types=1);

namespace himmelkreis4865\ModSmith\inventory\entity;

use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\network\mcpe\protocol\types\inventory\WindowTypes;

class InventoryHolder extends Entity {

	public const NETWORK_ID = "ModSmith:inventory_holder";

	public function __construct(Location $location, private readonly int $inventorySize, ?CompoundTag $nbt = null) {
		parent::__construct($location, $nbt);
	}

	protected function getInitialDragMultiplier(): float {
		return 0.0;
	}

	protected function getInitialGravity(): float {
		return 0.0;
	}

	protected function getInitialSizeInfo(): EntitySizeInfo {
		return new EntitySizeInfo(0, 0);
	}

	public static function getNetworkTypeId(): string {
		return self::NETWORK_ID;
	}

	protected function initEntity(CompoundTag $nbt): void {
		$this->setCanSaveWithChunk(false);
		$this->networkPropertiesDirty = true;
	}

	protected function syncNetworkData(EntityMetadataCollection $properties): void {
		parent::syncNetworkData($properties);

		$properties->setByte(EntityMetadataProperties::CONTAINER_TYPE, WindowTypes::INVENTORY);
		$properties->setInt(EntityMetadataProperties::CONTAINER_BASE_SIZE, $this->inventorySize);
	}
}

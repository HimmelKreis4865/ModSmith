<?php

declare(strict_types=1);

namespace himmelkreis4865\ModSmith\item;

use himmelkreis4865\ModSmith\utils\FileRegistry;
use himmelkreis4865\ModSmith\utils\Texture;
use pocketmine\utils\SingletonTrait;
use RuntimeException;
use function basename;
use function json_encode;
use const JSON_PRETTY_PRINT;

final class ItemAssetRegistry {
	use SingletonTrait;

	/** @var array<string, Texture> $textures */
	private array $textures = [];

	public function addTexture(Texture $texture, string $name = null): void {
		$this->textures[$name ?? basename($texture->texture)] = $texture;
		$this->write();
	}

	public function write(): void {
		$contents = [
			"resource_pack_name" => "vanilla",
			"texture_name" => "atlas.items",
		];
		foreach ($this->textures as $textureName => $texture) {
			$contents["texture_data"][$textureName] = [ "textures" => $texture ];
		}
		$json = json_encode($contents, JSON_PRETTY_PRINT);
		if (!$json) {
			throw new RuntimeException("Failed to JSON Encode item texture assets");
		}
		FileRegistry::getInstance()->addFile("textures/item_texture.json", $json);
	}
}
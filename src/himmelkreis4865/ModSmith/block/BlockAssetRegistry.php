<?php

declare(strict_types=1);

namespace himmelkreis4865\ModSmith\block;

use himmelkreis4865\ModSmith\utils\FileRegistry;
use himmelkreis4865\ModSmith\utils\Texture;
use pocketmine\utils\SingletonTrait;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use function basename;
use function file_exists;
use function file_get_contents;
use function json_encode;
use const JSON_PRETTY_PRINT;

final class BlockAssetRegistry {
	use SingletonTrait;

	/** @var array<string, Texture> $textures */
	private array $textures = [];

	public function addTexture(Texture $texture, string $name = null): void {
		$this->textures[$name ?? basename($texture->texture)] = $texture;
		$this->write();
	}

	public function addGeometry(string $path, string $name = null): void {
		if (!file_exists($path) || !($contents = file_get_contents($path))) {
			throw new FileNotFoundException("The given path $path does not exist but is needed to register a geometry");
		}
		FileRegistry::getInstance()->addFile("models/blocks/" . ($name ?? basename($path)), $contents);
	}

	private function write(): void {
		$textures = [];
		foreach ($this->textures as $name => $path) {
			$textures[$name] = [
				"textures" => $path
			];
		}
		$content = json_encode([
			"resource_pack_name" => "vanilla",
			"texture_data" => $textures
		], JSON_PRETTY_PRINT);
		if ($content) {
			FileRegistry::getInstance()->addFile("textures/terrain_texture.json", $content);
		}
	}
}
<?php

declare(strict_types=1);

namespace himmelkreis4865\ModSmith\utils;

use himmelkreis4865\ModSmith\ModSmith;
use pocketmine\utils\SingletonTrait;
use RuntimeException;
use function file_get_contents;

final class FileRegistry {
	use SingletonTrait;

	/**
	 * @var string[] $files
	 * @phpstan-var array<string, string> $files
	 */
	private array $files;

	public function __construct() {
		$coreComponents = file_get_contents(ModSmith::getInstance()->getResourceFolder() . "core_components.json");
		if (!$coreComponents) {
			throw new RuntimeException("core_components.json could not be found / opened in the internal resources");
		}
		$this->addFile("ui/core_components.json", $coreComponents);
	}

	public function addFile(string $path, string $fileContent): void {
		$this->files[$path] = $fileContent;
	}

	/**
	 * @return string[]
	 */
	public function getFiles(): array {
		return $this->files;
	}
}
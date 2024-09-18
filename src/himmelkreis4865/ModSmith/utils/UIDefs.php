<?php

declare(strict_types=1);

namespace himmelkreis4865\ModSmith\utils;

use pocketmine\utils\SingletonTrait;
use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

final class UIDefs {
	use SingletonTrait;

	/** @var string[] $includedFiles */
	private array $includedFiles = [
		"ui/core_components.json"
	];

	public function add(string $resourcePackPath): void {
		$this->includedFiles[] = $resourcePackPath;
	}

	public function encode(): string {
		return json_encode([
			"ui_defs" => array_values($this->includedFiles)
		], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
	}
}
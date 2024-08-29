<?php

declare(strict_types=1);

namespace himmelkreis4865\ModSmith\tasks;

use himmelkreis4865\ModSmith\ModSmith;
use pmmp\thread\ThreadSafeArray;
use pocketmine\scheduler\AsyncTask;
use Ramsey\Uuid\Uuid;
use RuntimeException;
use ZipArchive;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function json_encode;
use function md5;
use const JSON_PRETTY_PRINT;

final class ResourcePackCreateTask extends AsyncTask {

	/** @var ThreadSafeArray<string, string> $files */
	private ThreadSafeArray $files;

	/**
	 * @param array<string, string> $files
	 */
	public function __construct(array $files, private string $outputPath, private string $cachePath) {
		$this->files = ThreadSafeArray::fromArray($files);
	}

	public function onRun(): void {
		$this->setResult(false);
		$cacheString = "";
		foreach ($this->files as $content) {
			$cacheString .= md5($content);
		}
		$old = file_exists($this->cachePath) ? file_get_contents($this->cachePath) : null;
		if ($cacheString === $old) {
			return;
		}

		$zip = new ZipArchive();
		$zip->open($this->outputPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

		$json = json_encode($this->newManifest(), JSON_PRETTY_PRINT);
		if (!$json) {
			throw new RuntimeException("Manifest could not be encoded to json");
		}
		$zip->addFromString("manifest.json", $json);
		foreach ($this->files as $path => $content) {
			$zip->addFromString($path, $content);
		}
		$zip->close();
		file_put_contents($this->cachePath, $cacheString);
		$this->setResult(true);
	}

	public function onCompletion(): void {
		ModSmith::getInstance()->registerResourcePack();
		if ($this->getResult()) {
			ModSmith::getInstance()->getLogger()->info("Resource pack was created successfully.");
		} else {
			ModSmith::getInstance()->getLogger()->info("Resource pack was not rebuilt, either because of an error or because there were no changes. If you think your pack is outdated, try deleting packdata.cache in the plugin data");
		}
	}

	/**
	 * @return array<string, mixed>
	 */
	private function newManifest(): array {
		return [
			"format_version" => 1,
			"header" => [
				"name" => "ModSmith",
				"description" => "This resource pack provides important resources for the server",
				"uuid" => Uuid::uuid4()->toString(),
				"version" => [ 0, 0, 1 ],
				"min_engine_version" => [1, 16, 0]
			],
			"modules" => [ [
				"type" => "resources",
				"uuid" => Uuid::uuid4()->toString(),
				"version" => [ 0, 0, 1]
			] ]
		];
	}
}
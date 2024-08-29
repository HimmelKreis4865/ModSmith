<?php

declare(strict_types=1);

namespace himmelkreis4865\ModSmith\utils;

use GdImage;
use JsonSerializable;
use RuntimeException;
use function file_get_contents;
use function imagepng;
use function ob_end_flush;
use function ob_get_contents;
use function ob_start;
use function str_replace;

final class Texture implements JsonSerializable {

	private function __construct(public string $texture) {}

	public static function fromTexture(string $texture): Texture {
		return new Texture(str_replace(".png", "", $texture));
	}

	/**
	 * This will add the image to the texture pack
	 */
	public static function fromFile(string $localPath, string $packTexturePath): Texture {
		if (!($contents = file_get_contents($localPath))) {
			throw new RuntimeException("File $localPath could not be found or opened!");
		}
		FileRegistry::getInstance()->addFile($packTexturePath, $contents);
		return new Texture(str_replace(".png", "", $packTexturePath));
	}

	public static function fromContents(string $packTexturePath, string $contents): Texture {
		FileRegistry::getInstance()->addFile($packTexturePath, $contents);
		return new Texture(str_replace(".png", "", $packTexturePath));
	}

	public static function fromImage(string $packTexturePath, GdImage $image): Texture {
		ob_start();
		imagepng($image);
		$contents = ob_get_contents();
		ob_end_flush();
		if (!$contents) {
			throw new RuntimeException("Failed to get output buffering contents");
		}
		return self::fromContents($packTexturePath, $contents);
	}

	public function jsonSerialize(): string {
		return $this->texture;
	}

	public function __toString(): string {
		return $this->texture;
	}
}
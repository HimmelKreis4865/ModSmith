<?php

declare(strict_types=1);

namespace himmelkreis4865\ModSmith\utils;

use GdImage;
use InvalidArgumentException;
use pocketmine\math\Axis;
use pocketmine\math\Facing;
use function array_reverse;
use function assert;
use function imagecolorallocatealpha;
use function imagecopy;
use function imagecreatetruecolor;
use function imagefill;
use function imagesavealpha;
use function imagesx;
use function imagesy;
use function in_array;

final class Utils {

	/**
	 * @return GdImage[]
	 */
	public static function generateProgressBarImages(GdImage $empty, GdImage $full, int $states, int $facingFrom): array {
		if (!in_array($facingFrom, Facing::HORIZONTAL, true)) {
			throw new InvalidArgumentException("Facings may only be horizontal. Use North & South to describe when going vertical");
		}
		if (imagesx($empty) !== imagesx($full) || imagesy($empty) !== imagesy($full) || imagesx($empty) !== imagesy($full)) {
			throw new InvalidArgumentException("Both images may be of the same size and quadratic");
		}
		$size = imagesx($empty);
		if ($size % 16 !== 0) {
			throw new InvalidArgumentException("Only images with the quadratic size of a multiple of 16 are allowed");
		}
		if ($size % $states !== 0) {
			throw new InvalidArgumentException("The states may only be a prime factorization number");
		}
		$reversed = ($facingFrom === Facing::EAST || $facingFrom === Facing::SOUTH);

		$step = (imagesx($empty) / $states);

		if ($reversed) {
			$_empty = $full;
			$full = $empty;
			$empty = $_empty;
		}
		$images = [];
		for ($i = 0; $i <= $size; $i += $step) {
			$frameImg = imagecreatetruecolor($size, $size);
			imagesavealpha($frameImg, true);
			$color = imagecolorallocatealpha($frameImg, 0, 0, 0, 127);
			assert($color !== null);
			imagefill($frameImg, 0, 0, $color);
			imagecopy($frameImg, $empty, 0, 0, 0, 0, $size, $size);
			match (Facing::axis($facingFrom)) {
				Axis::X => imagecopy($frameImg, $full, 0, 0, 0, 0, $i, $size),
				default => imagecopy($frameImg, $full, 0, 0, 0, 0, $size, $i)
			};
			$images[] = $frameImg;
		}
		return ($reversed ? array_reverse($images) : $images);
	}
}
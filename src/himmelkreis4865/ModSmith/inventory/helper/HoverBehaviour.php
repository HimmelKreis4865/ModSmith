<?php

declare(strict_types=1);

namespace himmelkreis4865\ModSmith\inventory\helper;

use himmelkreis4865\ModSmith\utils\Texture;
use pocketmine\color\Color;

final class HoverBehaviour {

	public function __construct(
		public ?Texture $backgroundTexture = null,
		public float    $textureAlpha = 0.8,
		public bool     $borderEnabled = true,
		public Color    $borderColor = new Color(255, 255 ,255),
		public bool     $hoverTextEnabled = true
	) {}
}
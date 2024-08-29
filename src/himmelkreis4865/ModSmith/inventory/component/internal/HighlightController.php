<?php

declare(strict_types=1);

namespace himmelkreis4865\ModSmith\inventory\component\internal;

use himmelkreis4865\ModSmith\inventory\component\Component;
use himmelkreis4865\ModSmith\inventory\CustomInventory;
use himmelkreis4865\ModSmith\inventory\helper\Anchor;
use himmelkreis4865\ModSmith\inventory\helper\Dimension;
use himmelkreis4865\ModSmith\inventory\helper\HoverBehaviour;
use himmelkreis4865\ModSmith\inventory\helper\Properties;
use himmelkreis4865\ModSmith\utils\FileRegistry;
use himmelkreis4865\ModSmith\utils\Texture;
use InvalidArgumentException;
use pocketmine\color\Color;
use RuntimeException;
use function imagecolorallocate;
use function imagecolorallocatealpha;
use function imagecreatetruecolor;
use function imagedestroy;
use function imagefill;
use function imagepng;
use function imagesavealpha;
use function imagesetpixel;
use function is_int;
use function json_encode;
use function ob_end_clean;
use function ob_get_contents;
use function ob_start;
use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

class HighlightController extends Component {

	public function __construct(
		private readonly HoverBehaviour $behaviour,
		?Dimension $offset = null,
		?Dimension $size = null,
		Anchor $anchor = new Anchor()
	) {
		parent::__construct($offset, $size, $anchor);
	}

	public function build(CustomInventory $inventory): void {
		if ($this->behaviour->texture !== null) {
			$this->properties[Properties::HIGHLIGHT_TEXTURE] = $this->behaviour->texture;
		}
		$this->properties[Properties::HIGHLIGHT_TEXTURE_ALPHA] = $this->behaviour->textureAlpha;

		$this->properties[Properties::HIGHLIGHT_BORDER_VISIBLE] = $this->behaviour->borderEnabled;
		if ($this->behaviour->borderEnabled && !$this->behaviour->borderColor->equals(new Color(255, 255, 255))) {
			$this->properties[Properties::HIGHLIGHT_BORDER_TEXTURE] = $this->generateBorderTexture($this->behaviour->borderColor);
		}
	}

	private function generateBorderTexture(Color $color): Texture {
		$img = imagecreatetruecolor(5, 5);
		imagesavealpha($img, true);

		$transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
		$border = imagecolorallocate($img, $color->getR(), $color->getG(), $color->getB());

		if (!is_int($transparent) || !is_int($border)) {
			throw new InvalidArgumentException("Failed to allocate colors");
		}
		imagefill($img, 0, 0, $transparent);
		for ($x = 0; $x < 5; $x++) {
			for ($y = 0; $y < 5; $y++) {
				if ($x === 0 || $y === 0 || $x === 4 || $y === 4) {
					imagesetpixel($img, $x, $y, $border);
				}
			}
		}

		ob_start();
		imagepng($img);
		$contents = ob_get_contents();
		ob_end_clean();
		if (!$contents) {
			throw new RuntimeException("Output Buffering failed.");
		}
		imagedestroy($img);

		FileRegistry::getInstance()->addFile("textures/ui/generated/" . $this->getName() . ".json", json_encode([
			"nineslice_size" => 1,
			"base_size" => [
				5,
				5
			]
		], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
		return Texture::fromContents("textures/ui/generated/" . $this->getName() . ".png", $contents);
	}

	protected function getIdentifier(): string {
		return "highlight_controller";
	}

	public function getParent(): ?string {
		return "core_components.highlight_panel";
	}
}
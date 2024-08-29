<?php

declare(strict_types=1);

namespace himmelkreis4865\ModSmith\inventory\component;

use himmelkreis4865\ModSmith\inventory\CustomInventory;
use himmelkreis4865\ModSmith\inventory\helper\Anchor;
use himmelkreis4865\ModSmith\inventory\helper\Dimension;
use himmelkreis4865\ModSmith\inventory\helper\Properties;
use himmelkreis4865\ModSmith\inventory\types\Font;
use pocketmine\color\Color;
use function round;

class Text extends Component {

	private const RGB_MAX_VALUE = 255;

	public function __construct(
		public string $text,
		public Color $color = new Color(0, 0, 0),
		public float $fontScale = 1.0,
		public Font $font = Font::DEFAULT,
		public bool $localized = false,
		?Dimension $offset = null,
		?Dimension $size = null,
		Anchor $anchor = new Anchor(),
	) {
		parent::__construct($offset, $size, $anchor);
	}

	public function build(CustomInventory $inventory): void {
		parent::build($inventory);
		$this->properties[Properties::TEXT] = $this->text;
		$this->properties[Properties::TYPE] = "label";
		$this->properties[Properties::COLOR] = [
			round($this->color->getR() / self::RGB_MAX_VALUE, 3),
			round($this->color->getG() / self::RGB_MAX_VALUE, 3),
			round($this->color->getB() / self::RGB_MAX_VALUE, 3)
		];
		$this->properties[Properties::FONT_SCALE_FACTOR] = $this->fontScale;
		$this->properties[Properties::FONT_TYPE] = $this->font->value;
		if ($this->localized) $this->properties[Properties::LOCALIZE] = $this->localized;
	}

	public static function chestTitle(string $text, Dimension $offset = null): Text {
		return new Text(
			$text,
			new Color(76, 76, 76),
			offset: $offset ?? new Dimension(7, 6)
		);
	}

	protected function getIdentifier(): string {
		return "label";
	}
}
<?php

declare(strict_types=1);

namespace himmelkreis4865\ModSmith\inventory\helper;

use JsonSerializable;
use function assert;

final class ObjectLink implements JsonSerializable {

	private static ?string $currentNamespace = null;

	public function __construct(private readonly string $objectName) {}

	public static function setCurrentNamespace(string $currentNamespace): void {
		self::$currentNamespace = $currentNamespace;
	}

	public function jsonSerialize(): string {
		assert(self::$currentNamespace !== null, "A namespace is expected to be set when encoding the inventory");
		return self::$currentNamespace . "." . $this->objectName;
	}

	public function __toString(): string {
		assert(self::$currentNamespace !== null, "A namespace is expected to be set when encoding the inventory");
		return self::$currentNamespace . "." . $this->objectName;
	}
}
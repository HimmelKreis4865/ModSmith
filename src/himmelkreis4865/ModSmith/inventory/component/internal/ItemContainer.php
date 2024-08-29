<?php

declare(strict_types=1);

namespace himmelkreis4865\ModSmith\inventory\component\internal;

interface ItemContainer {

	public function getSize(): int;

	public function isLocked(int $slot): bool;
}
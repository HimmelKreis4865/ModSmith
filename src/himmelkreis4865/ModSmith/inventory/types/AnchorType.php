<?php

declare(strict_types=1);

namespace himmelkreis4865\ModSmith\inventory\types;

enum AnchorType: string {

	case TOP_LEFT = "top_left";

	case TOP_MIDDLE = "top_middle";

	case TOP_RIGHT = "top_right";

	case LEFT_MIDDLE = "left_middle";

	case CENTER = "center";

	case RIGHT_MIDDLE = "right_middle";

	case BOTTOM_LEFT = "bottom_left";

	case BOTTOM_MIDDLE = "bottom_middle";

	case BOTTOM_RIGHT = "bottom_right";
}
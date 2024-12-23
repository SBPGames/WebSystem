<?php

namespace SBPGames\Framework\Routing;

/**
 * @package SBPGames\Framework\Routing
 * @author Xibitol <contact@pimous.dev>
 */
enum Matching{

	case NONE;
	case PATH_ONLY;
	case FULL;
}
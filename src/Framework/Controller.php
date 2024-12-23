<?php

namespace SBPGames\Framework;

/**
 * @package SBPGames\Framework
 * @author Xibitol <contact@pimous.dev>
 */
abstract class Controller{

	// GETTERS
	public static function getBasePath(): string{ return "/"; }
	/** @return Route[] */
	public static function getRoutes(): array{ return []; }
}
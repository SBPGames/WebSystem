<?php

namespace SBPGames\Main;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use SBPGames\Framework\App;
use SBPGames\Framework\WebSystem;

/**
 * @package SBPGames\Main
 * @author Xibitol <contact@pimous.dev>
 */
class MainApp extends App{

	private const REGISTERED_NAME = "sbpgames.fr";

	public function __construct(WebSystem $context){
		parent::__construct($context, []);
	}

	// GETTERS
	public static function getRegName(): string{ return self::REGISTERED_NAME; }

	// FUNCTIONS
	public function processRequest(
		ServerRequestInterface $request, ResponseInterface $response
	): ResponseInterface{
		$response = parent::processRequest($request, $response);

		return $response;
	}
}
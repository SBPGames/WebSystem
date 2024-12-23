<?php

namespace SBPGames\Framework\Routing;

/**
 * @package SBPGames\Framework\Routing
 * @author Xibitol <contact@pimous.dev>
 */
class RoutingResult{

	private Matching $matching;
	private ?string $controller;
	private ?string $method;

	public function __construct(
		Matching $matching = Matching::NONE,
		?string $controller = null,
		?string $method = null
	){
		$this->matching = $matching;
		$this->controller = $controller;
		$this->method = $method;
	}

	// GETTERS
	public function getMatching(): Matching{ return $this->matching; }
	public function getController(): ?string{ return $this->controller; }
	public function getMethod(): ?string{ return $this->method; }
}
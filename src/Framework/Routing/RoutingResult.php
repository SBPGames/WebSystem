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
	/** @var ?array<string, string> */
	private ?array $urlParams;

	/** @param ?array<string, string> $urlParams */
	public function __construct(
		Matching $matching = Matching::NONE,
		?string $controller = null,
		?string $method = null,
		?array $urlParams = null
	){
		$this->matching = $matching;
		$this->controller = $controller;
		$this->method = $method;
		$this->urlParams = $urlParams;
	}

	// GETTERS
	public function getMatching(): Matching{ return $this->matching; }
	public function getController(): ?string{ return $this->controller; }
	public function getMethod(): ?string{ return $this->method; }
	/** @return ?array<string, string> */
	public function getURLParams(): ?array{ return $this->urlParams; }
}
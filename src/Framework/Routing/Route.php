<?php

namespace SBPGames\Framework\Routing;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

/**
 * @package SBPGames\Framework\Routing
 * @author Xibitol <contact@pimous.dev>
 */
class Route{

	private string $pathRegEx;
	/** @var array<string, string> */
	private array $callbacks;

	/** @param array<string, string> $methods */
	public function __construct(string $pathRegEx, array $callbacks){
		$this->pathRegEx = $pathRegEx;
		$this->callbacks = $callbacks;
	}

	// GETTERS
	private function getPathRegEx(): string{ return $this->pathRegEx; }
	/** @return string[] */
	private function getMethods(): array{ return array_keys($this->callbacks); }
	private function hasMethod(string $method): bool{
		return in_array($method, $this->getMethods());
	}

	public function getCallback(string $method): ?string{
		return $this->callbacks[$method] ?? null;
	}

	// FUNCTIONS
	/** @return ?array<string, string> */
	public function matchURI(UriInterface $uri): ?array{
		$match = preg_match($this->getPathRegEx(), $uri->getPath(), $urlParams);
		
		foreach(array_keys($urlParams) as $param)
			if(is_int($param))
				unset($urlParams[$param]);

		return is_int($match) && $match === 1 ? $urlParams : null;
	}

	public function matchRequest(RequestInterface $request): Matching{
		$match = $this->matchURI($request->getUri());

		if(is_array($match)){
			if($this->hasMethod($request->getMethod()))
				return Matching::FULL;
			else
				return Matching::PATH_ONLY;
		}
		else return Matching::NONE;
	}
}
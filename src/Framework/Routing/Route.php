<?php

namespace SBPGames\Framework\Routing;

use Psr\Http\Message\RequestInterface;

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
	/** @return string[] */
	private function getMethods(): array{ return array_keys($this->callbacks); }
	private function hasMethod(string $method): bool{
		return in_array($method, $this->getMethods());
	}

	public function getCallback(string $method): ?string{
		return $this->callbacks[$method] ?? null;
	}

	// FUNCTIONS
	public function matchRequest(RequestInterface $request): Matching{
		$match = preg_match($this->pathRegEx, $request->getUri()->getPath(),
			$urlParams
		);

		if(is_int($match) && $match === 1){
			if($this->hasMethod($request->getMethod()))
				return Matching::FULL;
			else
				return Matching::PATH_ONLY;
		}
		else return Matching::NONE;
	}
}
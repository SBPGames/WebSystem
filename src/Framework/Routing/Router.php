<?php

namespace SBPGames\Framework\Routing;

use Psr\Http\Message\RequestInterface;

/**
 * @package SBPGames\Framework\Routing
 * @author Xibitol <contact@pimous.dev>
 */
class Router{

	/** @var array<string, array<string, Route[]>> */
	private array $routes = [];

	// GETTERS
	/** @return string[] */
	private function getBasePaths(): array{
		return array_keys($this->routes);
	}
	private function hasBasePath(string $basePath): bool{
		return isset($this->routes[$basePath]);
	}
	/** @return string[] */
	private function getControllers(string $basePath): array{
		return array_keys($this->routes[$basePath]);
	}
	private function hasController(
		string $controller, ?string $basePath = null
	): bool{
		if(isset($basePath) && !$this->hasBasePath($basePath)) return false;
		$basePaths = isset($basePath) ? [$basePath] : $this->getBasePaths();

		$i = -1;
		while(++$i < count($basePaths)
			&& !isset($this->routes[$basePaths[$i]][$controller])
		);

		return $i < count($basePaths);
	}
	/** @return Route[] */
	private function getRoutes(string $basePath, string $controller): array{
		return $this->routes[$basePath][$controller];
	}

	// SETTERS
	public function registerController(string $controller): void{
		$basePath = call_user_func([$controller, "getBasePath"]);

		if(!is_string($basePath))
			throw new \UnexpectedValueException("No such $controller;");
		else if($this->hasController($controller, $basePath))
			throw new \RuntimeException("$controller already added;");

		// Registering
		if(!$this->hasBasePath($basePath)) $this->routes[$basePath] = [];
		$this->routes[$basePath][$controller] = array_values(
			call_user_func([$controller, "getRoutes"])
		);
	}

	// FUNCTIONS
	/** @return string[] */
	private function matchBasePath(string $path): array{
		return array_values(array_filter($this->getBasePaths(),
			function(string $basePath) use ($path){
				return str_starts_with($path, $basePath);
			}
		));
	}
	private function subBasePath(string $basePath, string $path): ?string{
		return sprintf("/%s", ltrim(substr($path, strlen($basePath)), "/"));
	}

	public function matchRequest(RequestInterface $request): RoutingResult{
		$uri = $request->getUri();

		$basePaths = $this->matchBasePath($uri->getPath());
		$result = new RoutingResult();

		$i = 0;
		do{
			$basePath = $basePaths[$i];
			$request = $request->withUri(
				$uri->withPath($this->subBasePath($basePath, $uri->getPath()))
			);

			$r = $this->matchTrimmedRequest($request, $basePath);
			if($r->getMatching() === Matching::FULL ||
				$r->getMatching() === Matching::PATH_ONLY
				&& $result->getMatching() === Matching::NONE
			)
				$result = $r;

			$i++;
		}while($i < count($basePaths)
			&& $result->getMatching() !== Matching::FULL
		);

		return $result;
	}

	private function matchTrimmedRequest(
		RequestInterface $request, string $basePath
	): RoutingResult{
		$controllers = $this->getControllers($basePath);
		$result = new RoutingResult();

		$i = 0;
		$j = 0;
		do{
			$controller = $controllers[$i];
			$routes = $this->getRoutes($basePath, $controller);
			$route = $routes[$j];

			$matching = $route->matchRequest($request);
			if($matching === Matching::FULL ||
				$matching === Matching::PATH_ONLY
				&& $result->getMatching() === Matching::NONE
			)
				$result = new RoutingResult($matching, $controller,
					$route->getCallback($request->getMethod()),
					$route->matchURI($request->getUri())
				);

			$j++;
			if($j >= count($routes)){
				$i++;
				$j = 0;
			}
		}while($i < count($controllers) && $matching !== Matching::FULL);

		return $result;
	}
}
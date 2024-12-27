<?php

namespace SBPGames\Framework;

use Psr\Container\ContainerExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SBPGames\Framework\Message\Status;
use SBPGames\Framework\Routing\Matching;
use SBPGames\Framework\Routing\Router;

/**
 * @package SBPGames\Framework
 * @author Xibitol <contact@pimous.dev>
 */
abstract class App{

	private WebSystem $context;
	private Router $router;

	/** @param string[] $controllers */
	public function __construct(WebSystem $context, array $controllers){
		$this->context = $context;

		$this->router = new Router();
		foreach($controllers as $controller)
			$this->router->registerController($controller);
	}

	// GETTERS
	public static function getRegName(): string{ return ""; }
	public static function isWWW(): bool{ return true; }
	/** @return string[] */
	private static function getRegNames(): array{
		$regNames = [static::getRegName()];

		if(static::isWWW())
			array_push($regNames, sprintf("www.%s", static::getRegName()));

		return $regNames;
	}

	protected function getContext(): WebSystem{ return $this->context; }
	private function getRouter(): Router{ return $this->router; }

	// FUNCTIONS
	/** @return object[] */
	private function retrieveControllerArgs(
		\ReflectionClass $controllerRCls
	): array{
		$args = [];

		foreach($controllerRCls->getConstructor()->getParameters() as $rParam){
			$paramRType = $rParam->getType();
			if(!($paramRType instanceof \ReflectionNamedType)
				|| $paramRType->isBuiltin()
			)
				throw new \UnexpectedValueException(sprintf(
					"Unexpected controller constructor %s parameter's type;",
					$rParam->getName()
				));

			$args[$rParam->getPosition()] = $this->getContext()->getContainer()
				->get($paramRType->getName());
		}

		return $args;
	}
	private function retrieveControllerInstance(string $controller): Controller{
		$rClass = new \ReflectionClass($controller);
		return $rClass->newInstanceArgs($this->retrieveControllerArgs($rClass));
	}
	private function retrieveControllerMethod(
		Controller $controller, string $method
	): \ReflectionMethod{
		return new \ReflectionMethod($controller, $method);
	}

	public static function matchHost(string $host): bool{
		return in_array($host, static::getRegNames());
	}

	/**
	 * @template R of ResponseInterface
	 * @param R $response
	 * @return R
	 */
	public function processRequest(
		ServerRequestInterface $request, ResponseInterface $response
	): ResponseInterface{
		$result = $this->getRouter()->matchRequest($request);

		switch($result->getMatching()){
			case Matching::NONE:
				return $response->withStatus(Status::NOT_FOUND->value);
			case Matching::PATH_ONLY:
				return $response->withStatus(Status::METHOD_NOT_ALLOWED->value);
			default:
				try{
					$controller = $this->retrieveControllerInstance(
						$result->getController()
					);

					$m = $this->retrieveControllerMethod(
						$controller, $result->getMethod()
					);
				}catch(\UnexpectedValueException|\ReflectionException $e){
					return $this->processRetrievingException(
						$request, $e, $response
					);
				}catch(ContainerExceptionInterface $e){
					return $this->processContainerException(
						$request, $e, $response
					);
				}

				foreach($result->getURLParams() as $param => $value)
					$request = $request->withAttribute($param, $value);

				try{
					return $m->invoke($controller, $request, $response);
				}catch(\ReflectionException $e){
					return $this->processInvocationException(
						$request, $e, $response
					);
				}
		}
	}
	protected function processRetrievingException(
		ServerRequestInterface $request,
		\UnexpectedValueException|\ReflectionException $exception,
		ResponseInterface $response
	){
		return $response->withStatus(Status::INTERNAL_ERROR->value);
	}
	protected function processContainerException(
		ServerRequestInterface $request,
		ContainerExceptionInterface $exception,
		ResponseInterface $response
	){
		return $response->withStatus(Status::SERVICE_UNAVAILABLE->value);
	}
	protected function processInvocationException(
		ServerRequestInterface $request,
		\TypeError|\ReflectionException $exception,
		ResponseInterface $response
	){
		return $response->withStatus(Status::INTERNAL_ERROR->value);
	}
}
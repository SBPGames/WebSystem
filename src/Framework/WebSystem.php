<?php

namespace SBPGames\Framework;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SBPGames\Framework\Message\Status;

/**
 * @package SBPGames\Framework
 * @author Xibitol <contact@pimous.dev>
 */
final class WebSystem{

	/** @var string[] */
	private array $apps = [];
	private ContainerInterface $container;

	/** @param string[] $apps */
	public function __construct(ContainerInterface $container, array $apps){
		$this->container = $container;
		$this->apps = array_values($apps);
	}

	// GETTERS
	/** @return string[] */
	private function getApps(): array{ return $this->apps; }
	public function getContainer(): ContainerInterface{
		return $this->container;
	}

	// FUNCTIONS
	private function retrieveAppInstance(string $app): App{
		return (new \ReflectionClass($app))->newInstance($this);
	}

	/**
	 * @template R of ResponseInterface
	 * @param R $response
	 * @return R
	 */
	public function processRequest(
		ServerRequestInterface $request, ResponseInterface $response
	): ResponseInterface{
		$i = -1;
		while(++$i < count($this->getApps()) &&
			!call_user_func([$this->getApps()[$i], "matchHost"],
				$request->getUri()->getHost()
			)
		);

		if($i < count($this->getApps())){
			try{
				$app = $this->retrieveAppInstance($this->getApps()[$i]);
			}catch(\ReflectionException $e){
				return $this->processRetrievingException(
					$request, $e, $response
				);
			}

			return $app->processRequest($request, $response);
		}else
			return $this->processAppNotFound($request, $response);
	}
	protected function processAppNotFound(
		ServerRequestInterface $request, ResponseInterface $response
	){
		return $response->withStatus(Status::MISDIRECTED_REQUEST->value);
	}
	protected function processRetrievingException(
		ServerRequestInterface $request,
		\ReflectionException $exception,
		ResponseInterface $response
	){
		return $response->withStatus(Status::INTERNAL_ERROR->value);
	}
}
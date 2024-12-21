<?php

namespace SBPGames\Framework\Service;

use Psr\Container\ContainerInterface;
use SBPGames\Framework\Service\NotFoundException;
use SBPGames\Framework\Service\Service;

/**
 * @package SBPGames\Framework\Service
 * @author Xibitol <contact@pimous.dev>
 */
class ServiceContainer implements ContainerInterface{

	private ServiceConfig $serviceConfig;
	/** @var array<string, string|Closure|Service> */
	private array $services = [];

	/** @param array<int|string, string|Closure> $entries */
	public function __construct(ServiceConfig $serviceConfig, array $entries){
		$this->serviceConfig = $serviceConfig;
		$this->addServices($entries);
	}

	// GETTERS
	public function has(string $id): bool{
		return isset($this->services[$id]);
	}
	public function get(string $id): Service{
		if(!$this->has($id)) throw new NotFoundException();
		else if(!($this->services[$id] instanceof Service))
			$this->prepareService($id);

		return $this->services[$id];
	}
	
	// SETTERS
	/** @param array<int|string, string|Closure> $entries */
	private function addServices(array $entries){
		foreach($entries as $key => $entry){
			$class = null;
			$func = null;

			switch(true){
				// Class name entries (Not associative).
				case is_int($key) && is_string($entry):
					$class = $entry;
					break;

				// Closure entries (Associative or Based on return type).
				case is_int($key) && $entry instanceof \Closure:
					$func = $entry;

					$rtp = (new \ReflectionFunction($func))->getReturnType();
					if(!($rtp instanceof \ReflectionNamedType)
						|| $rtp->isBuiltin()
						|| $rtp->allowsNull()
					)
						throw new ContainerException(
							"Unexpected closure return type of n°$key;"
						);

					$class = $rtp->getName();
					break;

				// Not supported/Invalid...
				default:
					throw new ContainerException(sprintf(
						"Unexpected entry type %s (%s) => %s (%s;",
						$key, gettype($key), $entry, gettype($entry)
					));
			}

			$this->addService($class, $func ?? null);
		}
	}
	private function addService(string $class, ?\Closure $func = null){
		if($this->has($class))
			throw new ContainerException("Service $class already exists;");

		ServiceContainer::assertClass($class);
		$this->services[$class] = isset($func) ? $func : $class;
	}

	private function prepareService(string $id): void{
		if(!$this->has($id)) throw new NotFoundException();
		$service = $this->services[$id];

		if(is_string($service))
			$service = (new \ReflectionClass($service))->newInstance();
		else if($service instanceof \Closure)
			$service = $service();
		else
			return;

		$service->init($this->serviceConfig->getConfig($service));

		$this->services[$id] = $service;
	}

	// ASSERTS
	private static function assertClass(string $class): void{
		try{
			if(!(new \ReflectionClass($class))->isSubclassOf(Service::class))
				throw new ContainerException(
					"Class must be a service ($class);"
				);
		}catch(\ReflectionException $_){
			throw new ContainerException("Invalid class name $class;");
		}
	}
}
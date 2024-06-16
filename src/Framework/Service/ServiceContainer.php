<?php

namespace SBPGames\Framework\Service;

use Psr\Container\ContainerInterface;
use SBPGames\Framework\Exception\NotFoundException;
use SBPGames\Framework\Service\Service;

/**
 * @package SBPGames\Framework\Service
 * @author Xibitol <contact@pimous.dev>
 */
class ServiceContainer implements ContainerInterface{

	/** @var array<string, string | Closure | Service> */
	private array $services = [];

	/** @param array<string | Closure> */
	public function __construct(array $entries){
		foreach($entries as $key => $entry){
			// Class name entries (Not associative).
			if(gettype($entry) == "string"){
				if(gettype($key) != "integer")
					throw new ContainerException(sprintf(
						"Only closure may be mapped to not-integer keys (%s ".
						"=> %s);",
						$key, $entry
					));

				$class = $entry;
			// Closure entries (Associative or Based on return type).
			}else if($entry instanceof \Closure
				&& in_array(gettype($key), ["integer", "string"])
			){
				$reflecRType = (new \ReflectionFunction($entry))
					->getReturnType();

				if(gettype($key) == "integer"){
					if(!($reflecRType instanceof \ReflectionType)
						|| $reflecRType->isBuiltin()
					)
						throw new ContainerException(
							"Unexpected closure return type of n°$key;"
						);

					$class = $reflecRType->getName();
				}else $class = $key;
			// Not supported/Invalid...
			}else
				throw new ContainerException(
					"Unexpected entry type $key => $entry;" 
				);

			$this->services[
				ServiceContainer::assertClass($class)
			] = $entry;
		}
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
	private function prepareService(string $id): void{
		if(!$this->has($id)) throw new NotFoundException();
		$service = $this->services[$id];

		if($service instanceof \Closure){
			$s = $service();

			if(!($s instanceof Service))
				throw new ContainerException(
					"Invalid object type returned while initializing the "
					."service $id with a closure;"
				);

			$service = $s;
		}else if(gettype($service) == "string"){
			/** @var Service */
			$service = (new \ReflectionClass($service))->newInstance();
		}else
			return;

		$service->init();

		$this->services[$id] = $service;
	}

	// ASSERTS
	private static function assertClass(string $class): string{
		try{
			$reflecClass = new \ReflectionClass($class);

			if(!$reflecClass->isSubclassOf(Service::class))
				throw new ContainerException(
					"Class must be a service ($class);"
				);

			return $reflecClass->getName();
		}catch(\ReflectionException $_){
			throw new ContainerException("Invalid class name $class;");
		}
	}
}
<?php

namespace SBPGames\Framework\Service;

/**
 * @package SBPGames\Framework\Service
 * @author Xibitol <contact@pimous.dev>
 */
class ServiceConfig{

	private const CONFIG_FOLDER = "config";
	private const CONFIG_FILE_FORMAT = "%s/%s/%s.config.json";

	private string $projectPath;

	public function __construct(string $projectPath){
		$this->projectPath = rtrim($projectPath, DIRECTORY_SEPARATOR);
	}

	// FUNCTIONS
	/** @return array<string, mixed> */
	public function getConfig(Service $service): array{
		return $this->read($service);
	}

	private function getConfigFilename(Service $service): string{
		return sprintf(ServiceConfig::CONFIG_FILE_FORMAT,
			$this->projectPath,
			ServiceConfig::CONFIG_FOLDER,
			$service->getIdentifier()
		);
	}
	private function read(Service $service): array{
		$filename = $this->getConfigFilename($service);

		if(!is_file($filename) || !is_readable($filename))
			throw new \RuntimeException(sprintf(
				"No such config file at %s of service %s or is unreadable;",
				$filename, $service::class,
			));

		if(($content = file_get_contents($filename)) === false)
			throw new \RuntimeException(sprintf(
				"Cannot read config file %s of service %s;;",
				$filename, $service::class
			));

		// TODO: Use YAML.
		try{
			$config = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
		}catch(\JsonException $e){
			throw new \JsonException(sprintf(
				"Cannot decode config file %s of service %s (%s;",
				$filename, $service::class, $e->getMessage()
			));	
		}

		$this->assertFields($service, $config);
		return $config;
	}
	/** @param array<string, mixed> $config */
	private function assertFields(Service $service, array $config): void{
		foreach(array_keys($config) as $field)
			if(!in_array($field, $service->getConfigFields()))
				throw new \UnexpectedValueException(sprintf(
					"%s: Unexpected field %s;",
					$service::class, $field
				));

		foreach($service->getMandatoryConfigFields() as $field)
			if(!isset($config[$field]))
				throw new \UnexpectedValueException(sprintf(
					"%s: No %s field defined;",
					$service::class, $field
				));
	}
}
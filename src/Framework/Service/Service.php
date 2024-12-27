<?php

namespace SBPGames\Framework\Service;

/**
 * @package SBPGames\Framework\Service
 * @author Xibitol <contact@pimous.dev>
 */
abstract class Service{

	private string $identifier;

	public function __construct(string $identifier){
		$this->identifier = $identifier;
	}

	// GETTERS
	/** @return string[] */
	public abstract function getMandatoryConfigFields(): array;
	/** @return string[] */
	public abstract function getConfigFields(): array;

	public function getIdentifier(): string{ return $this->identifier; }

	// LIFECYCLE FUNCTIONS
	/** @param array<string, mixed> $config */
	public function init(array $config): void{}
}
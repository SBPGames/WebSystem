<?php

namespace SBPGames\Framework\Service;

/**
 * @package SBPGames\Framework\Service
 * @author Xibitol <contact@pimous.dev>
 */
abstract class Service{

	public function __construct(){
		printf("Constructed service %s.\n", get_called_class());
	}

	// LIFECYCLE FUNCTIONS
	public function init(): void{
		printf("Initialized service %s.\n", get_called_class());
	}
}
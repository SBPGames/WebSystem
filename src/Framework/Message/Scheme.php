<?php

namespace SBPGames\Framework\Message;

/**
 * @package SBPGames\Framework\Message
 * @author Xibitol <contact@pimous.dev>
 */
enum Scheme: string{
	
	case HTTP = "http";
	case HTTPS = "https";

	// GETTERS
	public function getStandardPort(): int{
		return match($this->value){
			"http" => 80,
			"https" => 443,

			default => -1
		};
	}
}
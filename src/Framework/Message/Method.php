<?php

namespace SBPGames\Framework\Message;

/**
 * @package SBPGames\Framework\Message
 * @author Xibitol <contact@pimous.dev>
 */
enum Method: string{

	case GET = "GET";
	case POST = "POST";
	case PUT = "PUT";
	case PATCH = "PATCH";
	case DELETE = "DELETE";
}
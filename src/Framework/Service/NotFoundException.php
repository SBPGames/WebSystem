<?php

namespace SBPGames\Framework\Service;

use Psr\Container\NotFoundExceptionInterface;

/**
 * @package SBPGames\Framework\Service
 * @author Xibitol <contact@pimous.dev>
 */
class NotFoundException extends \RuntimeException
	implements NotFoundExceptionInterface{

}
<?php

namespace SBPGames\Framework\Exception;

use Psr\Container\NotFoundExceptionInterface;

/**
 * @package SBPGames\Framework\Exception
 * @author Xibitol <contact@pimous.dev>
 */
class NotFoundException extends \RuntimeException
	implements NotFoundExceptionInterface{

}
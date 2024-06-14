<?php

namespace SBPGames\Framework\Exception;

use Psr\Container\NotFoundExceptionInterface;

class NotFoundException extends \RuntimeException
	implements NotFoundExceptionInterface{

}
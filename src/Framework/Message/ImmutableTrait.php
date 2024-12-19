<?php

namespace SBPGames\Framework\Message;

/**
 * @package SBPGames\Framework\Message
 * @author Xibitol <contact@pimous.dev>
 */
trait ImmutableTrait{

	private const GETTER_FORMAT = "get%s";
	private const SETTER_FORMAT = "set%s";

	// IMMUTABLE SETTERS
	protected function with(string $param, mixed $value): static{
		if($value === $this->{
			sprintf(static::GETTER_FORMAT, ucwords($param))
		}())
			return $this;
		else if(!isset($value)){
			$new = clone $this;
			unset($new->{$param});
			return $new;
		}

		$new = clone $this;
		$new->{
			sprintf(static::SETTER_FORMAT, ucwords($param))
		}($value);
		return $new;
	}
}
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
		if($value === $this->{self::getGetterFunc($param)}())
			return $this;
		else if(!isset($value)){
			$new = clone $this;
			unset($new->{$param});
			return $new;
		}

		$new = clone $this;
		$new->{self::getSetterFunc($param)}($value);
		return $new;
	}

	// FUNCTIONS
	private static function getGetterFunc(string $param): string{
		return sprintf(self::GETTER_FORMAT, ucwords($param));
	}
	private static function getSetterFunc(string $param): string{
		return sprintf(self::SETTER_FORMAT, ucwords($param));
	}
}
<?php

namespace SBPGames\Framework\Message;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;
use SBPGames\Framework\Exception\NotImplementedException;

/**
 * @package SBPGames\Framework\Message
 * @author Xibitol <contact@pimous.dev>
 */
abstract class Message implements MessageInterface{
	use ImmutableTrait;
	use WithHeadersTrait{
		__construct as __constructWithHeadersTrait;
		setFromGlobals as setFromGlobalsWithHeadersTrait;
	}

	public const PROTOCOL_VERSION_PATTERN = "/^[0-9]\\.[0-9]$/";

	private float $protocolVersion;

	/** @param array<string, string[]> $headers */
	public function __construct(float $version = 1.1, array $headers = []){
		$this->setProtocolVersion($version);
		$this->__constructWithHeadersTrait($headers);
	}

	// GETTERS
	public function getProtocolVersion(): string{
		return strval($this->protocolVersion);
	}
	public function getProtocolVersionf(): float{
		return $this->protocolVersion;
	}

	public function getBody(): StreamInterface{
		throw new NotImplementedException();
	}

	// SETTERS
	private function setProtocolVersion(string|float $version): void{
		Message::assertProtocolVersion($version);

		$this->protocolVersion = floatval($version);
	}

	protected function setFromGlobals(): void{
		$this->setProtocolVersion(explode("/", $_SERVER["SERVER_PROTOCOL"])[1]);
		$this->setFromGlobalsWithHeadersTrait();
	}

	// IMMUTABLE SETTERS
	public function withProtocolVersion(string|float $version): static{
		return $this->with("protocolVersion", $version);
	}

	public function withBody(StreamInterface $stream): static{
		throw new NotImplementedException();
	}

	// FUNCTIONS
	public abstract function getStartLine(): string;

	// ASSERTIONS
	private static function assertProtocolVersion(string|float $version): void{
		if(!preg_match(self::PROTOCOL_VERSION_PATTERN, strval($version)))
			throw new \InvalidArgumentException(
				"Invalid protocol version ($version);"
			);
	}
}
<?php

namespace SBPGames\Framework\Message;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @package SBPGames\Framework\Message
 * @author Xibitol <contact@pimous.dev>
 */
abstract class Message implements MessageInterface{
	use ImmutableTrait;
	use WithHeadersTrait{
		__construct as __constructWithHeadersTrait;
	}

	public const PROTOCOL_VERSION_PATTERN = "/^[0-9]\\.[0-9]$/";

	private float $protocolVersion;
	private StreamInterface $body;

	/** @param array<string, string[]> $headers */
	public function __construct(
		float $version = 1.1,
		array $headers = [],
		StreamInterface $body = new FileStream(
			FileStream::PHP_TEMPORATY_STREAM_URI, "r+"
		)
	){
		$this->__constructWithHeadersTrait($headers);

		$this->setProtocolVersion($version);
		$this->setBody($body);
	}

	// GETTERS
	public function getProtocolVersion(): string{
		return strval($this->protocolVersion);
	}
	public function getProtocolVersionf(): float{
		return $this->protocolVersion;
	}
	public function getBody(): StreamInterface{ return $this->body; }

	// SETTERS
	protected function setProtocolVersion(string|float $version): void{
		Message::assertProtocolVersion($version);

		$this->protocolVersion = floatval($version);
	}
	protected function setBody(StreamInterface $body): void{
		$this->body = $body;
	}

	// IMMUTABLE SETTERS
	public function withProtocolVersion(string|float $version): static{
		return $this->with("protocolVersion", $version);
	}
	public function withBody(StreamInterface $body): static{
		$this->getBody()->close();
		return $this->with("body", $body);
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
<?php

namespace SBPGames\Framework\Message;

/**
 * @package SBPGames\Framework\Message
 * @author Xibitol <contact@pimous.dev>
 */
trait WithHeadersTrait{

	public const HEADER_NAME_PATTERN = "/^[a-z0-9!#$%&'*+\-.^_`|~]+$/";
	public const HEADER_VALUE_PATTERN = "/^[[:graph:]]+(?:[\s\h]+[[:graph:]]+)*$/";
	public const HEADER_SEPARATOR_PATTERN = "/,\s*/";

	public const HEADER_FORMAT = "%s: %s";

	/** @var array<string, string[]> */
	private array $headers = [];

	/** @param array<string, string[]> $headers */
	protected function __construct(array $headers = []){
		foreach($headers as $name => $value)
			$this->setHeader($name, $value);

		return $this;
	}

	// GETTERS
	public function getHeaders(): array{ return $this->headers; }
	public function getHeader(string $name): array{
		return ($name = $this->getHeaderName($name)) !== null
			? $this->getHeaders()[$name] : [];
	}
	public function hasHeader(string $name): bool{
		return $this->getHeaderName($name) !== null;
	}

	private function getHeaderName(string $name): ?string{
		$headerNames = array_keys($this->getHeaders());
		$i = 0;

		while($i < sizeof($headerNames)
			&& strtolower($headerNames[$i]) !== strtolower($name)
		) $i++;

		return $i < sizeof($headerNames) ? $headerNames[$i] : null;
	}

	// SETTERS
	protected function setHeader(string $name, array $value): void{
		self::assertHeaderName($name);
		foreach($value as $v) self::assertHeaderValue($v);

		$this->headers[$name] = $value;
	}
	protected function removeHeader(string $name): void{
		self::assertHeaderName($name);

		unset($this->headers[$this->getHeaderName($name)]);
	}

	// IMMUTABLE SETTERS
	public function withHeader(string $name, mixed $value): static{
		if(is_string($value)) $value = [$value];

		$headers = $this->getHeader($name);
		if(count($headers) !== 0 && count(array_diff($headers, $value)) === 0)
			return $this;

		$new = clone $this;
		$new->setHeader($name, $value);
		return $new;
	}

	public function withAddedHeader(string $name, mixed $value): static{
		if(is_string($value)) $value = [$value];

		return $this->withHeader($name,
			array_merge($this->getHeader($name), $value)
		);
	}

	public function withoutHeader(string $name): static{
		if(!$this->hasHeader($name)) return $this;

		$new = clone $this;
		$new->removeHeader($name);
		return $new;
	}

	// FUNCTIONS
	public function getHeaderLine(
		string $name, string $separator = ","
	): string{
		return $this->hasHeader($name) ?
			implode($separator, $this->getHeader($name)) : "";
	}

	public function getHeaderFullLine(string $name, string $separator = ","){
		if(!$this->hasHeader($name)) return "";

		return sprintf(self::HEADER_FORMAT,
			$name, $this->getHeaderLine($name, $separator)
		);
	}

	// ASSERTIONS
	private static function assertHeaderName(string $name): void{
		if(!preg_match(self::HEADER_NAME_PATTERN, strtolower($name)))
			throw new \InvalidArgumentException(
				"Invalid header name ($name);"
			);
	}
	private static function assertHeaderValue(string $value): void{
		if(!preg_match(self::HEADER_VALUE_PATTERN, $value))
			throw new \InvalidArgumentException(
				"Invalid header value ($value);"
			);
	}
}
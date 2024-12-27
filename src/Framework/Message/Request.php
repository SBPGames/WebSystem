<?php

namespace SBPGames\Framework\Message;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

/**
 * @package SBPGames\Framework\Message
 * @author Xibitol <contact@pimous.dev>
 */
class Request extends Message implements RequestInterface{
	use ImmutableTrait;

	public const START_LINE_FORMAT = "%s %s HTTP/%s";

	private UriInterface $uri;
	private ?string $methodString = null;
	private Method $method;
	private string $requestTarget = "";

	/** @param array<string, string[]> $headers */
	public function __construct(
		float $version = 1.1,
		UriInterface $uri = new Uri(),
		Method $method = Method::GET,
		array $headers = [],
		StreamInterface $body = new FileStream(
			FileStream::PHP_OUTPUT_STREAM_URI, "r"
		)
	){
		parent::__construct($version, $headers, $body);

		$this->setUri($uri);
		$this->setMethodCase($method);
	}

	// GETTERS
	public function getUri(): UriInterface{ return $this->uri; }

	public function getMethodCase(): Method{ return $this->method; }
	public function getMethod(): string{
		return isset($this->methodString) ?
			$this->methodString : $this->method->value;
	}

	public function getRequestTarget(): string{
		if(strlen($this->requestTarget) > 0) return $this->requestTarget;
		$path = $this->getUri()->getPath();
		$uri = strval((new Uri())
			->withPath(strlen($path) > 0 ? $path : "/")
			->withQuery($this->getUri()->getQuery())
		);

		return $uri;
	}

	// SETTERS
	protected function setUri(UriInterface $uri): void{
		if(strlen($uri->getHost()) > 0){
			$value = $uri->getHost();

			if(!is_null($uri->getPort()) && (
				strlen($uri->getScheme()) === 0
				|| $uri->getPort() !==
					Scheme::tryFrom($uri->getScheme())->getStandardPort()
			)) $value .= sprintf(":%d", $uri->getPort());

			$this->setHeader("Host", [$value]);
		}

		$this->uri = $uri;
	}

	protected function setMethodCase(Method $method): void{
		$this->method = $method;
		$this->methodString = null;
	}
	protected function setMethod(string $method): void{
		if(($met = Method::tryFrom(strtoupper($method))) === null)
			throw new \UnexpectedValueException(
				"Invalid or unsupported method ($method);"
			);

		$this->setMethodCase($met);
		if($this->getMethodCase()->value !== $method)
			$this->methodString = $method;
	}

	protected function setRequestTarget(string $requestTarget){
		$this->requestTarget = $requestTarget;
	}

	// IMMUTABLE SETTERS
	public function withUri(
		UriInterface $uri, bool $preserveHost = false
	): static{
		$hostHeaderValue = $this->getHeader("Host");

		$new = $this->with("uri", $uri);
		if(count($hostHeaderValue) > 0 && $preserveHost)
			$new = $new->withHeader("Host", $hostHeaderValue);
		return $new;
	}

	public function withMethod(string|Method $method): static{
		return $this->with(
			is_string($method) ? "method" : "methodCase", $method
		);
	}

	public function withRequestTarget(string $requestTarget): static{
		return $this->with("requestTarget", $requestTarget);
	}

	// FUNCTIONS
	public function getStartLine(): string{
		return sprintf(self::START_LINE_FORMAT,
			$this->getMethod(),
			$this->getRequestTarget(),
			$this->getProtocolVersionf()
		);
	}
}
<?php

namespace SBPGames\Framework\Message;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;

/**
 * @package SBPGames\Framework\Message
 * @author Xibitol <contact@pimous.dev>
 */
class ServerRequest extends Request implements ServerRequestInterface{
	use ImmutableTrait;

	/** @var array<string, mixed> */
	private array $serverParams;
	/** @var array<string, string|array<int|string, string>> */
	private array $queryParams;
	/** @var array<string, mixed> */
	private array $cookies;
	/** @var array<string, UploadedFileInterface> */
	private array $uploadedFiles;
	private null|array|object $parsedBody;

	/** @var array<string, mixed> */
	private array $attributes = [];

	/**
	 * @param array<string, string[]> $headers
	 * @param array<string, mixed> $serverParams
	 * @param array<string, string|array<int|string, string>> $query
	 * @param array<string, mixed> $cookies
	 * @param array<string, UploadedFileInterface> $uploadedFiles
	 */
	public function __construct(
		float $version = 1.1,
		UriInterface $uri = new Uri(),
		Method $method = Method::GET,
		array $headers = [],

		array $serverParams = [],
		array $query = [],
		array $cookies = [],
		array $uploadedFiles = [],

		StreamInterface $body = new FileStream(
			FileStream::PHP_INPUT_STREAM_URI, "r"
		),
		null|array|object $parsedBody = null
	){
		parent::__construct(
			$version, $uri, $method, $headers, $body
		);

		$this->setServerParams($serverParams);
		$this->setQueryParams($query);
		$this->setCookieParams($cookies);
		$this->setUploadedFiles($uploadedFiles);
		$this->setParsedBody($parsedBody);
	}

	// CONSTRUCTORS
	public static function fromGlobals(): static{
		$headers = apache_request_headers();
		$serverRequest = new static(
			explode("/", $_SERVER["SERVER_PROTOCOL"])[1],
			Uri::fromGlobals(),
			Method::from($_SERVER["REQUEST_METHOD"]),
			is_array($headers) ? array_combine(array_keys($headers), array_map(
				function($value){
					return preg_split(self::HEADER_SEPARATOR_PATTERN, $value);
				},
				$headers
			)) : [],

			array_diff_key($_SERVER, [
				"REQUEST_URI" => 1,
				"REQUEST_METHOD" => 1,
				"QUERY_STRING" => 1
			]),
			$_GET,
			$_COOKIE,
			UploadedFile::fromGlobals(),

			FileStream::fromInput(),
			count($_POST) > 0 ? $_POST : null
		);

		if(is_null($serverRequest->getParsedBody()))
			$serverRequest->setParsedBody($serverRequest->parseBody());

		return $serverRequest;
	}

	// GETTERS
	public function getServerParams(): array{ return $this->serverParams; }
	public function getQueryParams(): array{ return $this->queryParams; }
	public function getCookieParams(): array{ return $this->cookies; }
	public function getUploadedFiles(): array{ return $this->uploadedFiles;	}
	public function getParsedBody(): null|array|object{
		return $this->parsedBody;
	}

	public function getAttributes(): array{ return $this->attributes; }
	public function getAttribute(string $name, mixed $default = null): mixed{
		return $this->hasAttribute($name) ?
			$this->getAttributes()[$name] : $default;
	}
	public function hasAttribute(string $name): bool{
		return isset($this->getAttributes()[$name]);
	}

	// SETTERS
	protected function setServerParams(array $serverParams): void{
		$this->serverParams = $serverParams;
	}
	protected function setQueryParams(array $query): void{
		$this->queryParams = $query;
	}
	protected function setCookieParams(array $cookies): void{
		$this->cookies = $cookies;
	}
	protected function setUploadedFiles(array $uploadedFiles): void{
		foreach($uploadedFiles as $name => $uf){
			if(!is_int($name) || !is_string($name)
				|| !($uf instanceof UploadedFileInterface)
			)
				throw new \InvalidArgumentException(sprintf(
					"Invalid normalized tree of files (Unexpected %s => %s).",
					gettype($name),
					is_object($uf) ? get_class($uf) : gettype($uf)
				));
		}

		$this->uploadedFiles = $uploadedFiles;
	}
	protected function setParsedBody(null|array|object $parsedBody): void{
		$this->parsedBody = $parsedBody;
	}

	protected function setAttribute(string $name, mixed $value): void{
		$this->attributes[$name] = $value;
	}
	protected function removeAttribute(string $name): void{
		unset($this->attributes[$name]);
	}

	// IMMUTABLE SETTERS
	public function withQueryParams(array $query): static{
		return $this->with("queryParams", $query);
	}
	public function withCookieParams(array $cookies): static{
		return $this->with("cookieParams", $cookies);
	}
	public function withUploadedFiles(array $uploadedFiles): static{
		return $this->with("uploadedFiles", $uploadedFiles);
	}
	public function withParsedBody($data): static{
		return $this->with("parsedBody", $data);
	}

	public function withAttribute(string $name, mixed $value): static{
		$new = clone $this;
		$new->setAttribute($name, $value);
		return $new;
	}
	public function withoutAttribute(string $name): static{
		if(!$this->hasAttribute($name)) return $name;

		$new = clone $this;
		$new->removeAttribute($name);
		return $new;
	}

	// FUNCTIONS
	private function parseBody(): null|array|object{
		return match($this->getHeader("Content-Type")[0] ?? null){
			"application/x-www-form-urlencoded",
				"multipart/form-data" => $_POST,
			"application/json"
				=> json_decode($this->getBody()->getContents(), true),
			default => null
		};
	}
}
<?php

namespace SBPGames\Framework\Message;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;
use SBPGames\Framework\Exception\NotImplementedException;

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
	/** @var array<string, UploadedFile> */
	private array $uploadedFiles;

	/** @var array<string, mixed> */
	private array $attributes = [];

	/**
	 * @param array<string, string[]> $headers
	 * @param array<string, mixed> $serverParams
	 * @param array<string, string|array<int|string, string>> $query
	 * @param array<string, mixed> $cookies
	 * @param array<string, UploadedFile> $uploadedFiles
	 */
	public function __construct(
		float $version = 1.1,
		UriInterface $uri = new Uri(),
		Method $method = Method::GET,
		array $headers = [],

		array $serverParams = [],
		array $query = [],
		array $cookies = [],
		array $uploadedFiles = []
	){
		parent::__construct(
			$version, $uri, $method, $headers
		);

		$this->setServerParams($serverParams);
		$this->setQueryParams($query);
		$this->setCookieParams($cookies);
		$this->setUploadedFiles($uploadedFiles);
	}

	// CONSTRUCTORS
	public static function fromGlobals(): static{
		$headers = apache_request_headers();

		return new static(
			explode("/", $_SERVER["SERVER_PROTOCOL"])[1],
			Uri::fromGlobals(),
			Method::from($_SERVER["REQUEST_METHOD"]),
			!is_bool($headers) ? $headers : [],

			array_diff_key($_SERVER, [
				"REQUEST_URI" => 1,
				"REQUEST_METHOD" => 1,
				"QUERY_STRING" => 1
			]),
			$_GET,
			$_COOKIE,
			$_FILES
		);
	}

	// GETTERS
	public function getServerParams(): array{ return $this->serverParams; }
	public function getQueryParams(): array{ return $this->queryParams; }
	public function getCookieParams(): array{ return $this->cookies; }
	public function getUploadedFiles(): array{ return $this->uploadedFiles;	}
	public function getParsedBody(): mixed{
		throw new NotImplementedException();
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
	private function setServerParams(array $serverParams): void{
		$this->serverParams = $serverParams;
	}
	private function setQueryParams(array $query): void{
		$this->queryParams = $query;
	}
	private function setCookieParams(array $cookies): void{
		$this->cookies = $cookies;
	}
	private function setUploadedFiles(array $uploadedFiles): void{
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

	private function setAttribute(string $name, mixed $value): void{
		$this->attributes[$name] = $value;
	}
	private function removeAttribute(string $name): void{
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
		throw new NotImplementedException();
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
}
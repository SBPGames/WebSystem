<?php

namespace SBPGames\Framework\Message;

use Psr\Http\Message\UriInterface;
use SBPGames\Framework\NotImplementedException;

/**
 * @package SBPGames\Framework\Message
 * @author Xibitol <contact@pimous.dev>
 */
class Uri implements UriInterface{
	use ImmutableTrait;

	public const PATH_ALLOWED_CHARACTERS =
		"abcdfeghijklmnopqrstuvwxyz" // unreserved
		."ABCDEFGHIJKLMNOPQRSTUVWXYZ" // unreserved
		."0123456789" // unreserved
		."-._~" // unreserved
		."!$&'()*+,;=@:"; // sub-delims: reserved but allowed in paths
	public const QUERY_ALLOWED_CHARACTERS = Uri::PATH_ALLOWED_CHARACTERS."/?[]";
	public const FRAGMENT_ALLOWED_CHARACTERS = Uri::PATH_ALLOWED_CHARACTERS."/?";

	public const IPV4_PATTERN = "/^(?'octet'25[0-5]|2[0-4]\d|1?\d\d?)(?:\.(?&octet)){3}$/";
	public const REGISTERED_NAME_PATTERN = "/^(?'label'[a-z](?:[a-z0-9-]{0,61}[a-z\\d])?)(?:\\.(?&label))*\\.?$/";
	public const PATH_PATTERN = "/^\/?(?:(?:[a-zA-Z0-9-._~!$&'()*+,;=@:]+|%[[:xdigit:]]{2})?\/?)*$/";
	public const QUERY_PATTERN = "/^\/?(?:(?:[a-zA-Z0-9-._~!$&'()*+,;=@:\/?[\]]+|%[[:xdigit:]]{2})\/?)*$/";
	public const FRAGMENT_PATTERN = "/^\/?(?:(?:[a-zA-Z0-9-._~!$&'()*+,;=@:\/?]+|%[[:xdigit:]]{2})\/?)*$/";
	
	private ?Scheme $scheme = null;

	private string $host = "";
	private ?int $port = null;

	private string $path = "";
	private string $query = "";
	private string $fragment = "";

	public function __construct(
		?Scheme $scheme = null,
		string $host = "",
		?int $port = null,
		string $path = "",
		string $query = "",
		string $fragment = ""
	){
		$this->setSchemeCase($scheme);
		$this->setHost($host);
		$this->setPort($port);
		$this->setPath($path);
		$this->setQuery($query);
		$this->setFragment($fragment);
	}

	// CONSTRUCTORS
	public static function fromGlobals(): static{
		$parsed = parse_url($_SERVER["REQUEST_URI"]);

		return new static(
			Scheme::tryFrom(
				strtolower(explode("/", $_SERVER["SERVER_PROTOCOL"])[0])
			),
			$_SERVER["SERVER_NAME"],
			$_SERVER["SERVER_PORT"],
			$parsed["path"],
			$parsed["query"] ?? "",
			$parsed["fragment"] ?? ""
		);
	}

	// GETTERS
	public function getSchemeCase(): ?Scheme{
		return isset($this->scheme) ? $this->scheme : null;
	}
	public function getScheme(): string{
		return $this->getSchemeCase()->value ?? "";
	}

	// Authority
	public function getUserInfo(): string{
		throw new NotImplementedException();
	}
	public function getHost(): string{ return $this->host; }
	// This not complies to standard: Almost always returns a value.
	public function getPort(): ?int{
		return isset($this->port) ? $this->port : (
			!is_null($this->getSchemeCase()) ?
				$this->scheme->getStandardPort() : null
		);
	}
	public function getAuthority(): string{
		$authority = $this->getHost();

		if(!is_null($this->getPort()))
			$authority .= sprintf(":%d", $this->getPort());

		return $authority;
	}

	// Path
	public function getPath(): string{ return $this->path; }
	public function getQuery(): string{ return $this->query; }
	public function getFragment(): string{ return $this->fragment; }

	// SETTERS
	protected function setSchemeCase(?Scheme $scheme): void{
		if(!is_null($this->getPort())
			&& isset($scheme)
			&& $this->getPort() === $scheme->getStandardPort()
		) $this->setPort(null);

		$this->scheme = $scheme;
	}
	protected function setScheme(string $scheme): void{
		if(strlen($scheme) === 0){
			unset($this->scheme);
			return;
		}

		if(($sc = Scheme::tryFrom(strtolower($scheme))) === null)
			throw new \UnexpectedValueException(
				"Invalid or unsupported scheme ($scheme);"
			);

		$this->setSchemeCase($sc);
	}

	// Authority
	protected function setHost(string $host): void{
		self::assertHost($host);
		$this->host = $host;
	}
	protected function setPort(?int $port): void{
		if(is_null($port) || (
			!is_null($this->getSchemeCase())
			&& $port === $this->scheme->getStandardPort()
		)){
			unset($this->port);
			return;
		}

		if(isset($port)) self::assertPort($port);
		$this->port = $port;
	}

	// Path
	protected function setPath(string $path): void{
		$path = self::urlencode($path);
		self::assertPath($path);

		$this->path = $path;
	}
	protected function setQuery(string $query): void{
		$query = self::urlencode($query, self::QUERY_ALLOWED_CHARACTERS);
		self::assertQuery($query);

		$this->query = $query;
	}
	protected function setFragment(string $fragment): void{
		$fragment = self::urlencode($fragment,
			self::FRAGMENT_ALLOWED_CHARACTERS
		);
		self::assertFragment($fragment);

		$this->fragment = $fragment;
	}

	// IMMUTABLE SETTERS
	public function withScheme(string|Scheme $scheme): static{
		if(is_string($scheme))
			return $this->with("scheme", strtolower($scheme));
		else
			return $this->with("schemeCase", $scheme);
	}

	// Authority
	public function withUserInfo(
		string $user, ?string $password = null
	): static{
		throw new NotImplementedException();
	}
	public function withHost(string $host): static{
		return $this->with("host", $host);
	}
	public function withPort(?int $port): static{
		return $this->with("port", $port);
	}

	// Path
	public function withPath(string $path): static{
		return $this->with("path", $path);
	}
	public function withQuery(string $query): static{
		return $this->with("query", $query);
	}
	public function withFragment(string $fragment): static{
		return $this->with("fragment", $fragment);
	}

	// FUNCTIONS
	private static function urlencode(
		string $text, string $charSet = Uri::PATH_ALLOWED_CHARACTERS
	): string{
		$encoded = "";

		for($i = 0; $i < strlen($text); $i++){
			$pchar = $text[$i];

			if($pchar === "%"
				&& preg_match("/\d/", $text[$i + 1] ?? "")
				&& preg_match("/\d/", $text[$i + 2] ?? "")
			){
				$pchar .= $text[$i + 1].$text[$i + 2];
				$i += 2;
			}else if($text[$i] !== "/" && !str_contains($charSet, $pchar))
				$pchar = rawurlencode($pchar);

			$encoded .= $pchar;
		}

		return $encoded;
	}

	public function __toString(): string{
		$uri = "";

		if(!is_null($this->getSchemeCase()))
			$uri .= sprintf("%s:", $this->getScheme());
		if(strlen($this->getHost()) > 0)
			$uri .= sprintf("//%s", $this->getAuthority());

		$path = $this->getPath();
		if(strlen($this->getHost()) > 0 && preg_match("/^[^\/]/", $path))
			$path = sprintf("/%s", $path);
		else if(strlen($this->getHost()) === 0 && preg_match("/^\/\//", $path))
			$path = sprintf("/%s", ltrim($path, "/"));
		$uri .= $path;

		if(strlen($this->getQuery()) > 0)
			$uri .= sprintf("?%s", $this->getQuery());
		if(strlen($this->fragment) > 0)
			$uri .= sprintf("#%s", $this->getFragment());

		return $uri;
	}

	// ASSERTIONS
	// Authority
	private static function assertHost(string $host): void{
		if(
			strlen($host) > 0
			// IP literal (Unsupported)
			&& true
			&& !preg_match(self::IPV4_PATTERN, $host)
			// Registered name
			&& (
				strlen($host) > 255
				|| !preg_match(self::REGISTERED_NAME_PATTERN, $host)
			)
		)
			throw new \InvalidArgumentException(sprintf(
				"Invalid hostname (%s; IP literal is not supported for now);",
				$host
			));
	}
	private static function assertPort(int $port): void{
		if($port < 0 || $port > 65535)
			throw new \InvalidArgumentException(sprintf(
				"Port out of range (%s);",
				$port
			));
	}

	// Path
	private static function assertPath(string $path): void{
		if(!preg_match(self::PATH_PATTERN, $path))
			throw new \InvalidArgumentException(sprintf(
				"Invalid path (%s);",
				$path
			));
	}
	private static function assertQuery(string $query): void{
		if(!preg_match(self::QUERY_PATTERN, $query))
			throw new \InvalidArgumentException(sprintf(
				"Invalid query (%s);",
				$query
			));
	}
	private static function assertFragment(string $fragment): void{
		if(!preg_match(self::FRAGMENT_PATTERN, $fragment))
			throw new \InvalidArgumentException(sprintf(
				"Invalid fragment (%s);",
				$fragment
			));
	}
}
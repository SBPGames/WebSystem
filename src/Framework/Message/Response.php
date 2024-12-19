<?php

namespace SBPGames\Framework\Message;

use Psr\Http\Message\ResponseInterface;

/**
 * @package SBPGames\Framework\Message
 * @author Xibitol <contact@pimous.dev>
 */
class Response extends Message implements ResponseInterface{

	public const REASON_PHRASE_PATTERN = "/^[^\x00-\x08\x0A-\x1F\x7F]*$/";

	public const START_LINE_FORMAT = "HTTP/%s %d %s";

	private int|Status $status;
	/** When the reason phrase is overrided. */
	private string $reasonPhrase = "";

	public function __construct(
		float $version = 1.1,
		array $headers = [],
		Status $status = Status::OK,
		string $reasonPhrase = ""
	){
		parent::__construct($version, $headers);

		$this->setStatus($status);
		$this->setReasonPhrase($reasonPhrase);
	}

	// GETTERS
	public function getStatus(): ?Status{
		return $this->status instanceof Status ? $this->status : null;
	}
	public function getStatusCode(): int{
		return $this->status->value ?? $this->status;
	}
	public function getReasonPhrase(): string{
		return (
			strlen($this->reasonPhrase) === 0 && !is_null($this->getStatus()) ?
				$this->getStatus()->getReasonPhrase() : $this->reasonPhrase
		);
	}

	// SETTERS
	public function setStatus(Status $status): void{
		$this->status = $status;
	}
	public function setStatusCode(int $code): void{
		self::assertCode($code);

		if(($status = Status::tryFrom($code)) !== null)
			$this->setStatus($status);
		else
			$this->status = $code;
	}
	public function setReasonPhrase(string $reasonPhrase): void{
		if(!is_null($this->getStatus())
			&& $reasonPhrase === $this->getStatus()->getReasonPhrase()
		)
			$reasonPhrase = "";

		self::assertReasonPhrase($reasonPhrase);

		$this->reasonPhrase = $reasonPhrase;
	}

	// IMMUTABLE SETTERS
	public function withStatus(int|Status $code,
		string $reasonPhrase = ""
	): static{
		return $this->with("statusCode", $code)
			->with("reasonPhrase", $reasonPhrase);
	}

	// FUNCTIONS
	public function getStartLine(): string{
		return sprintf(self::START_LINE_FORMAT,
			$this->getProtocolVersionf(),
			$this->getStatusCode(),
			$this->getReasonPhrase()
		);
	}

	// ASSERTIONS
	private static function assertCode(int $code): void{
		if($code < 100 || $code > 599)
			throw new \InvalidArgumentException("Invalid status code ($code);");
	}

	private static function assertReasonPhrase(string $reasonPhrase): void{
		// BUG: Line feeds (0x09) not matched. Workaround:
		$reasonPhrase = str_replace("\n", "\0", $reasonPhrase);

		if(!preg_match(self::REASON_PHRASE_PATTERN, $reasonPhrase))
			throw new \InvalidArgumentException(
				"Invalid reason phrase (Contains non-readable characters);"
			);
	}
}
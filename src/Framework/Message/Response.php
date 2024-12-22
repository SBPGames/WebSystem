<?php

namespace SBPGames\Framework\Message;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @package SBPGames\Framework\Message
 * @author Xibitol <contact@pimous.dev>
 */
class Response extends Message implements ResponseInterface{

	public const REASON_PHRASE_PATTERN = "/^[^\x00-\x08\x0A-\x1F\x7F]*$/";

	public const START_LINE_FORMAT = "HTTP/%s %d %s";

	private Status $status;
	/** When the reason phrase is overrided. */
	private string $reasonPhrase = "";

	/** @param array<string, string[]> $headers */
	public function __construct(
		float $version = 1.1,
		array $headers = [],
		Status $status = Status::OK,
		StreamInterface $body = new FileStream(
			FileStream::PHP_TEMPORATY_STREAM_URI, "w"
		)
	){
		parent::__construct($version, $headers, $body);

		$this->setStatus($status);
	}

	// GETTERS
	public function getStatus(): Status{ return $this->status; }
	public function getStatusCode(): int{ return $this->status->value; }
	public function getReasonPhrase(): string{
		return strlen($this->reasonPhrase) === 0 ?
			$this->getStatus()->getReasonPhrase() : $this->reasonPhrase;
	}

	// SETTERS
	protected function setStatus(Status $status): void{ $this->status = $status; }
	protected function setStatusCode(int $code): void{
		if(($status = Status::tryFrom($code)) === null)
			throw new \UnexpectedValueException(
				"Invalid or unsupported status code ($code);"
			);

		$this->setStatus($status);
	}
	protected function setReasonPhrase(string $reasonPhrase): void{
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
		return $this->with(is_int($code) ? "statusCode" : "status", $code)
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

	public function write(
		StreamInterface $output
	): void{
		header($this->getStartLine());

		foreach(array_keys($this->getHeaders()) as $name)
			header($this->getHeaderFullLine($name));

		if($this->getBody()->isSeekable()) $this->getBody()->rewind();
		while(!$this->getBody()->eof())
			$output->write(
				$this->getBody()->read(FileStream::DEFAULT_BUFFER_SIZE)
			);
	}

	// ASSERTIONS
	private static function assertReasonPhrase(string $reasonPhrase): void{
		// BUG: Line feeds (0x09) not matched. Workaround:
		$reasonPhrase = str_replace("\n", "\0", $reasonPhrase);

		if(!preg_match(self::REASON_PHRASE_PATTERN, $reasonPhrase))
			throw new \InvalidArgumentException(
				"Invalid reason phrase (Contains non-readable characters);"
			);
	}
}
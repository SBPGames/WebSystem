<?php

namespace SBPGames\Framework\Message;

use Psr\Http\Message\StreamInterface;

/**
 * @package SBPGames\Framework\Message
 * @author Xibitol <contact@pimous.dev>
 */
class FileStream implements StreamInterface{

	public const DEFAULT_BUFFER_SIZE = 1024*2;

	public const PHP_INPUT_STREAM_URI = "php://input";
	public const PHP_OUTPUT_STREAM_URI = "php://output";
	public const PHP_TEMPORATY_STREAM_URI = "php://temp";

	private string $filename;
	private $stream;

	public function __construct(string $filename, string $mode = ""){
		$this->filename = $filename;
		$this->open($filename, $mode);
	}

	// CONSTRUCTORS
	public static function fromInput(){
		return new static(self::PHP_INPUT_STREAM_URI, "r");
	}
	public static function fromOutput(){
		return new static(self::PHP_OUTPUT_STREAM_URI, "w");
	}
	public static function fromTemp(){
		return new static(self::PHP_TEMPORATY_STREAM_URI, "r+");
	}

	// GETTERS
	private function getStream(){ return $this->stream; }

	public function getName(): string{ return $this->filename; }
	public function getSize(): ?int{
		$size = null;

		if(is_resource($this->getStream())
			&& !is_bool($stat = fstat($this->getStream()))
		)
			$size = $stat["size"];

		return $size;
	}

	public function getMetadata(?string $key = null): mixed{
		$metadata = stream_get_meta_data($this->getStream());
		return isset($key) ? $metadata[$key] : $metadata;
	}
	public function isSeekable(): bool{
		return $this->getMetadata()["seekable"];
	}
	public function isReadable(): bool{
		return preg_match("/r|[waxc]\\+/", $this->getMetadata()["mode"]);
	}
	public function isWritable(): bool{
		return preg_match("/[waxc]|r\\+/", $this->getMetadata()["mode"]);
	}

	public function tell(): int{
		if(is_bool($pos = ftell($this->getStream())))
			throw new \RuntimeException(
				"Cannot tell current position of the cursor in the stream;"
			);

		return $pos;
	}
	public function eof(): bool{
		return feof($this->getStream());
	}

	// SETTERS
	private function open(string $filename, string $mode = ""): void{
		$s = fopen($filename, $mode);

		if(is_bool($s))
			throw new \RuntimeException(
				"Cannot open file $filename with mode \"$mode\";"
			);

		$this->stream = $s;
	}

	public function detach(){
		$s = $this->getStream();
		$this->stream = null;
		return $s;
	}
	public function close(): void{
		fclose($this->detach());
	}

	// FUNCTIONS
	public function seek(int $offset, int $whence = SEEK_SET): void{
		if(fseek($this->getStream(), $offset, $whence) === -1)
			throw new \RuntimeException(
				"Cannot seek along the stream;"
			);
	}
	public function rewind(): void{
		if(!rewind($this->getStream()))
			throw new \RuntimeException(
				"Cannot rewind back to the stream's beginning;"
			);
	}

	public function read(int $length): string{
		if(is_bool($read = fread($this->getStream(), $length)))
			throw new \RuntimeException("Cannot read from stream;");

		return $read;
	}
	public function write(string $string): int{
		if(is_bool($written = fwrite($this->getStream(), $string)))
			throw new \RuntimeException("Cannot write to stream;");

		return $written;
	}
	public function getContents(): string{
		$buffer = "";

		while(!$this->eof()) $buffer .= $this->read(self::DEFAULT_BUFFER_SIZE);

		return $buffer;
	}

	public function copyTo(FileStream $stream): int{
		if(is_bool($copied = stream_copy_to_stream(
			$this->getStream(),
			$stream->getStream()
		)))
			throw new \RuntimeException("Cannot copy this stream to another;");

		return $copied;
	}

	public function __toString(): string{
		try{
			$this->rewind();
			return $this->getContents();
		}catch(\RuntimeException $_){
			return "";
		}
	}
}
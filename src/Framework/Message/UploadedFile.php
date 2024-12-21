<?php

namespace SBPGames\Framework\Message;

use Psr\Http\Message\UploadedFileInterface;

/**
 * @package SBPGames\Framework\Message
 * @author Xibitol <contact@pimous.dev>
 */
class UploadedFile implements UploadedFileInterface{

	public const TEMPORARY_DIR = "/tmp";
	public const TEMPORARY_FILE_PREFIX = "SBPWS";

	private ?FileStream $stream = null;
	private ?string $name;
	private ?string $type;
	private ?int $size;
	private int $error;
	private ?string $tmpName;

	public function __construct(
		?string $name = null,
		?string $type = null,
		?int $size = null,
		int $error = UPLOAD_ERR_OK,
		?string $tmpName = null
	){
		$this->name = $name;
		$this->type = $type;
		$this->size = $size;
		$this->error = $error;
		$this->tmpName = $tmpName;
	}

	// CONSTRUCTORS
	/** @return UploadedFile[] */
	public static function fromGlobals(): array{
		return array_map(function($file){
			return static::fromGlobal($file);
		}, $_FILES);
	}
	public static function fromGlobal(array $file): static{
		return new static(
			$file["name"],
			$file["type"],
			$file["size"],
			$file["error"],
			$file["tmp_name"]
		);
	}

	// GETTERS
	public function getClientFilename(): ?string{ return $this->name; }
	public function getClientMediaType(): ?string{ return $this->type; }
	public function getSize(): ?int{ return $this->size; }
	public function getError(): int{ return $this->error; }

	public function getStream(): FileStream{
		if(!isset($this->tmpName))
			throw new \RuntimeException(
				"No (more) file to stream (May be moved or not provided);"
			);
		else if(!isset($this->stream))
			$this->stream = new FileStream($this->tmpName, "r");

		return $this->stream;
	}
	private function closeStream(): void{
		$this->stream->close();
		$this->stream = null;
	}

	// SETTERS
	public function moveTo(string $targetPath): void{
		// Verifying
		if(!isset($this->tmpName))
			throw new \RuntimeException(
				"No file to move (May be already moved or not provided);"
			);
		else if(file_exists($targetPath) && (
			!is_file($targetPath)
			|| !is_writeable($targetPath)
		))
			throw new \RuntimeException(
				"$targetPath isn't a regular file or isn't writeable;"
			);

		// TODO: Find a better solution, maybe with a RegEx.
		try{
			$targetStream = new FileStream($targetPath, "w");
			$targetStream->close();
		}catch(\RuntimeException $_){
			throw new \InvalidArgumentException(
				"Invalid target path ($targetPath);"
			);
		}

		// Moving
		switch(true){
			case is_uploaded_file($this->tmpName):
				if(!move_uploaded_file($this->tmpName, $targetPath))
					throw new \RuntimeException(sprintf(
						"Cannot move uploaded file %s to %s;",
						$this->tmpName, $targetPath
					));
				break;
			case rename($this->tmpName, $targetPath):
				break;
			default:
				$sourceStream = $this->getStream();
				$sourceStream->rewind();
				$targetStream = new FileStream($targetPath, "w");

				try{
					$sourceStream->copyTo($targetStream);
				}catch(\RuntimeException $_){
					throw new \RuntimeException(sprintf(
						"Cannot move file %s to %s;",
						$this->tmpName, $targetPath
					));
				}

				$this->closeStream();
				$targetStream->close();
		}

		// Invalidating stream.
		$this->tmpName = null;
	}
}
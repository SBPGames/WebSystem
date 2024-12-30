<?php

namespace SBPGames\Framework;

use Psr\Http\Message\ResponseInterface;
use SBPGames\Framework\Message\Status;

/**
 * @package SBPGames\Framework
 * @author Xibitol <contact@pimous.dev>
 */
class JSONResponseHelper{

	private const JS_MAX_SAFE_INTEGER = 2**53 - 1;

	private ResponseInterface $response;

	public function __construct(ResponseInterface $response){
		$this->response = $response
			->withHeader("Content-Type", "application/json");
	}

	// GETTERS
	private function getResponse(): ResponseInterface{ return $this->response; }

	// FUNCTIONS
	private function makeDataSafe(array $data): array{
		foreach($data as $key => $value){
			if(is_array($value)) $data[$key] = $this->makeDataSafe($value);
			else if(is_int($value) && $value > self::JS_MAX_SAFE_INTEGER)
				$data[$key] = strval($value);
		}

		return $data;
	}

	// TODO: Move this implementation to a new Response subclass.
	public function write(
		array $data, bool $created = false
	): ResponseInterface{
		$this->getResponse()->getBody()->rewind();
		$this->getResponse()->getBody()->write(json_encode(
			$this->makeDataSafe($data)
		));

		return $this->getResponse()->withStatus(
			$created ? Status::CREATED->value : Status::OK->value
		);
	}
	// TODO: Move this implementation to a new Response subclass (RFC 7807).
	public function writeError(
		Status $status, string $detail, string $instance,
		?string $type = null, string $title = ""
	): ResponseInterface{
		return $this->write([
			"type" => $type ?? "about:blank",
			"title" => isset($type) ? $title : $status->getReasonPhrase(),
			"status" => $status->value,
			"detail" => $detail,
			"instance" => $instance
		])->withStatus($status->value);
	}
}
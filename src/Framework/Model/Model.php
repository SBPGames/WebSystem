<?php

namespace SBPGames\Framework\Model;

use SBPGames\Framework\Service\Database\DatabaseService;

/**
 * @package SBPGames\Framework\Model
 * @author Xibitol <contact@pimous.dev>
 */
abstract class Model{

	/** @var array<string, mixed> */
	private array $identifiers = [];
	private bool $published = false;

	public function __construct(){
		foreach(array_values(static::getIdentifierFields()) as $identifier)
			$this->identifiers[$identifier] = null;
	}

	// CONSTRUCTORS
	protected static function fromArray(array $values): static{
		return new static();
	}

	// GETTERS
	protected static function getTableName(): string{ return ""; }
	/** @var string[] */
	protected static function getIdentifierFields(): array{ return []; }

	public function getIdentifiers(): array{ return $this->identifiers; }
	public function isPublished(): bool{ return $this->published; }

	// SETTERS
	private function setIdentifier(string $key, mixed $value): void{
		$this->identifiers[$key] = $value;
	}
	private function togglePublished(?bool $published = null): void{
		$this->published = isset($published) ? $published : !$this->published;
	}

	// FUNCTIONS
	/**
	 * @param array<string, mixed> $filters
	 * @param array<string, bool> $sortKeys `true` is ASC and `false` is DESC.
	 * @return static[]
	 */
	public static function findAll(DatabaseService $database,
		array $filters = [], array $sortKeys = [],
		int $page = 0, int $limit = 32
	): array{
		$reflecClass = new \ReflectionClass(static::class);

		// Checks filter/sort keys existance.
		foreach(array_keys(array_merge($filters, $sortKeys)) as $key)
			if(!$reflecClass->hasProperty($key))
				throw new ModelException(sprintf(
					"%s's field \"%s\" used as a filter or a sort key "
					."doesn't exist;",
					static::class, $key
				));

		return static::select($database, $filters, $sortKeys, $page, $limit);
	}

	/** @param array<string, mixed> $identifiers */
	public static function findByIdentifiers(DatabaseService $database,
		array $identifiers
	): ?Model{
		// Checks identifiers existence.
		foreach(array_keys($identifiers) as $key)
			if(!in_array($key, static::getIdentifierFields()))
				throw new ModelException(sprintf(
					"%s's field \"%s\" used as an identifier key but isn't;",
					static::class, $key
				));

		if(count($identifiers) !== count(static::getIdentifierFields()))
			throw new ModelException(sprintf(
				"%s's identifier keys are missing to fully identify it;",
				static::class
			));

		return static::select($database, $identifiers, [], 0, 1)[0] ?? null;
	}

	// LIFECYCLE FUNCTIONS
	/** @param array<string, mixed> $values */
	protected abstract function onFetch(array $values);

	// DATABASE FUNCTIONS
	/**
	 * @param DatabaseService $database 
	 * @param array<string, mixed> $filters 
	 * @param array<string, bool> $sortKeys Is in descending order
	 * ?
	 * @param int $page
	 * @param int $limit
	 * @return static[]
	 */
	private static function select(DatabaseService $database,
		array $filters = [], array $sortKeys = [],
		int $page = 0, int $limit = 32
	): array{
		// Validates page number and amount limit.
		if($page < 0 || $limit <= 0 || $limit > 32)
			throw new ModelException(
				"Page number cannot be negative or Amount limit cannot be "
				."either negative or zero, and cannot be upper than 32."
			);

		// Fetches data from service.
		$output = $database->fetch(
			(new SQLHelper(static::getTableName()))->generateSelect(
				$filters, $sortKeys, $page, $limit
			),
			array_map(function(mixed $v): mixed{
				return $v instanceof \BackedEnum ? $v->value : $v;
			}, $filters)
		);

		return array_map(function(array $data){
			$obj = static::fromArray($data);

			foreach(static::getIdentifierFields() as $field)
				$obj->setIdentifier($field, $data[$field]);
			$obj->togglePublished(true);

			$obj->onFetch($data);

			return $obj;
		}, $output);
	}
}
<?php

namespace SBPGames\Framework\Model;

use SBPGames\Framework\Service\Database\DatabaseService;

/**
 * @package SBPGames\Framework\Model
 * @author Xibitol <contact@pimous.dev>
 */
abstract class Model{

	/** @var array<string, null|bool|int|float|string> */
	private array $identifiers = [];
	private bool $published = false;

	public function __construct(){
		foreach(array_values(static::getIdentifierFields()) as $identifier)
			$this->identifiers[$identifier] = null;
	}

	// CONSTRUCTORS
	/** @param array<string, mixed> $values */
	public static function fromArray(array $values): static{
		$rClass = new \ReflectionClass(static::class);
		$args = [];

		foreach($rClass->getConstructor()->getParameters() as $param){
			$value = $values[$param->getName()] ?? null;

			if(
				!array_key_exists($param->getName(), $values)
					&& !$param->isOptional()
				|| array_key_exists($param->getName(), $values)
					&& is_null($value)
					&& !$param->allowsNull()
			)
				throw new ModelException(sprintf(
					"%s field is mandatory and cannot be null;",
					$param->getName()
				));

			if(!is_null($value) && !is_object($value) && $param->hasType()){
				$rParamType = $param->getType();
				$rTypes = match($rParamType::class){
					\ReflectionNamedType::class => [$rParamType],
					\ReflectionUnionType::class => $rParamType->getTypes(),
					\ReflectionIntersectionType::class => []
				};

				static::assertFieldType($param->getName(), $value,
					array_map(function(\ReflectionNamedType $rType){
						return $rType->getName();
					}, array_filter($rTypes,
						function(\ReflectionType $rType): bool{
							return $rType instanceof \ReflectionNamedType
								&& $rType->isBuiltin();
						}
					))
				);
			}

			if(array_key_exists($param->getName(), $values))
				$args[$param->getName()] = $value;
		}

		return $rClass->newInstanceArgs($args);
	}

	// GETTERS
	protected static function getTableName(): string{ return ""; }
	/** @var string[] */
	protected static function getIdentifierFields(): array{ return []; }
	/** @var string[] */
	protected static function getAutomaticIdentifiers(): array{ return []; }
	/** @var string[] */
	private static function getIdentifierNames(): array{
		return array_values(static::getIdentifierFields());
	}
	private static function hasIdentifierField(string $identifier): bool{
		return in_array($identifier, static::getIdentifierNames());
	}

	protected function getIdentifiers(): array{ return $this->identifiers; }
	protected function getOneIdentifier(string $identifier): mixed{
		return $this->identifiers[$identifier];
	}
	public function isPublished(): bool{ return $this->published; }

	// SETTERS
	protected function setIdentifier(string $key, mixed $value): void{
		if(!is_null($this->getOneIdentifier($key)))
			throw new ModelException("$key identifier cannot be modified;");

		$this->identifiers[$key] = $value;
	}
	private function togglePublished(?bool $published = null): void{
		$this->published = isset($published) ? $published : !$this->published;
	}

	// FUNCTIONS
	/**
	 * @param array<string, null|bool|int|float|string> $filters
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
			if(!$reflecClass->hasProperty($key)
				&& !static::hasIdentifierField($key)
			)
				throw new ModelException(
					"$key field used as a filter or a sort key doesn't exist;"
				);

		return static::select($database, $filters, $sortKeys, $page, $limit);
	}

	/** @param array<string, bool|int|float|string> $identifiers */
	protected static function findByIdentifiers(DatabaseService $database,
		array $identifiers
	): ?Model{
		// Checks identifiers existence.
		foreach(array_keys($identifiers) as $key)
			if(!static::hasIdentifierField($key))
				throw new ModelException(
					"$key field used as an identifier key but isn't;"
				);

		if(count($identifiers) !== count(static::getIdentifierNames()))
			throw new ModelException(
				"Identifier keys are missing to fully identify it;"
			);

		return static::select($database, $identifiers, [], 0, 1)[0] ?? null;
	}

	/** @return array<string, null|bool|int|float|string> */
	public abstract function toEntry(): array;

	public function publish(DatabaseService $database): void{
		if($this->isPublished())
			throw new ModelException("Already published.");

		$this->published = $this->insert($database);
	}
	public function save(DatabaseService $database): void{
		if(!$this->isPublished())
			throw new ModelException("Isn't published.");

		$this->update($database);
	}

	// LIFECYCLE FUNCTIONS
	/** @param array<string, null|bool|int|float|string> $values */
	protected function onFetch(DatabaseService $database, array $values){}

	// DATABASE FUNCTIONS
	/**
	 * @param array<string, null|bool|int|float|string> $filters
	 * @param array<string, bool> $sortKeys `true` is ASC and `false` is DESC.
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
				."either negative or zero, and cannot be more than 32;"
			);

		// Fetches data from service.
		$output = $database->fetch(
			(new SQLHelper(static::getTableName()))->generateSelect(
				$filters, $sortKeys, $page, $limit
			),
			$filters
		);

		return array_map(function(array $data) use ($database){
			$obj = static::fromArray($data);

			foreach(static::getAutomaticIdentifiers() as $field)
				$obj->setIdentifier($field, $data[$field]);
			$obj->togglePublished(true);

			$obj->onFetch($database, $data);

			return $obj;
		}, $output);
	}

	private function insert(DatabaseService $database): bool{
		// Unset automatic identifiers
		$values = $this->toEntry();
		foreach(static::getAutomaticIdentifiers() as $field)
			unset($values[$field]);

		// Insert data into service.
		$output = $database->fetch(
			(new SQLHelper(static::getTableName()))->generateInsert(
				array_keys($values), static::getAutomaticIdentifiers()
			),
			array_map(
				function(null|bool|int|float|string $v): null|int|float|string{
					return is_bool($v) ? intval($v) : $v;
				},
				$values
			)
		);

		if(count($output) === 1){
			foreach(static::getAutomaticIdentifiers() as $field)
				$this->setIdentifier($field, $output[0][$field]);

			$this->onFetch($database, $output[0]);
		}

		return count($output) === 1;
	}

	private function update(DatabaseService $database): bool{
		// Unset automatic identifiers
		$values = $this->toEntry();
		foreach(static::getAutomaticIdentifiers() as $field)
			unset($values[$field]);

		// Insert data into service.
		$output = $database->fetch(
			(new SQLHelper(static::getTableName()))->generateUpdate(
				array_keys($values), static::getIdentifierNames()
			),
			array_map(
				function(null|bool|int|float|string $v): null|int|float|string{
					return is_bool($v) ? intval($v) : $v;
				},
				$this->toEntry()
			)
		);

		if(count($output) === 1) $this->onFetch($database, $output[0]);

		return count($output) === 1;
	}

	// ASSERTIONS
	/** @param string|string[] $types */
	protected static function assertFieldType(
		string $name, mixed $value, string|array $types
	){
		$types = is_string($types) ? [$types] : array_values($types);

		// Allows int to float conversion
		if(in_array("float", $types))
			array_push($types, "int");

		// Adapts type names to align them to gettype returns.
		for($i = 0; $i < count($types); $i++)
			$types[$i] = match($types[$i]){
				"null" => "NULL",
				"int" => "integer",
				"bool" => "boolean",
				"float" => "double",
				default => $types[$i]
			};

		if(!in_array(gettype($value), $types))
			throw new ModelException(sprintf(
				"Invalid %s field type (Got %s);", $name, gettype($value)
			));
	}
}
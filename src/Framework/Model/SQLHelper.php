<?php

namespace SBPGames\Framework\Model;

/**
 * @package SBPGames\Framework\Model
 * @author Xibitol <contact@pimous.dev>
 */
class SQLHelper{

	private const SELECT_FORMAT = "SELECT DISTINCT * FROM %s %s %s LIMIT %u, %u;";
	private const WHERE_CLAUSE_FORMAT = "WHERE %s";
	private const ORDERBY_CLAUSE_FORMAT = "ORDER BY %s";
	private const ORDERBY_ATOM_FORMAT = "%s %s";
	private const ORDERBY_ASC = "ASC";
	private const ORDERBY_DESC = "DESC";

	private const INSERT_FORMAT = "INSERT INTO %s(%s) VALUES (%s) RETURNING *;";
	
	private const UPDATE_FORMAT = "UPDATE %s SET %s WHERE %s;";

	private const PREPARED_FORMAT = ":%s";
	private const PREPARED_ASSOC_FORMAT = "%1\$s = :%1\$s";
	private const COMMA_SEPARATOR = ", ";
	private const AND_SEPARATOR = " AND ";

	private string $tableName;

	public function __construct(string $tableName){
		$this->tableName = $tableName;
	}

	// GETTERS
	private function getTableName(): string{ return $this->tableName; }

	// FUNCTIONS
	public function generateSelect(
		array $filters, array $sortKeys, int $page, int $limit
	): string{
		$filtersPart = "";
		if(count($filters) > 0){
			$filtersPart = sprintf(static::WHERE_CLAUSE_FORMAT,
				implode(self::AND_SEPARATOR, array_map(
					function(string $k): string{
						return sprintf(self::PREPARED_ASSOC_FORMAT, $k);
					}, array_keys($filters)
				))
			);
		}

		$sortKeysPart = "";
		if(count($sortKeys) > 0)
			$sortKeysPart = sprintf(static::ORDERBY_CLAUSE_FORMAT,
				implode(self::COMMA_SEPARATOR, array_map(
					function(string $k, bool $isDesc): string{
						return sprintf(self::ORDERBY_ATOM_FORMAT,
							$k, $isDesc ? self::ORDERBY_DESC : self::ORDERBY_ASC
						);
					}, array_keys($sortKeys), $sortKeys
				))
			);

		return sprintf(static::SELECT_FORMAT,
			$this->getTableName(),
			$filtersPart, $sortKeysPart,
			$page*$limit, $limit
		);
	}

	/** @param string[] $columns */
	public function generateInsert(array $columns): string{
		return sprintf(static::INSERT_FORMAT,
			$this->getTableName(),
			implode(self::COMMA_SEPARATOR, $columns),
			implode(self::COMMA_SEPARATOR, array_map(
				function(string $k): string{
					return sprintf(self::PREPARED_FORMAT, $k);
				}, $columns
			))
		);
	}

	/**
	 * @param string[] $columns
	 * @param string[] $identifiers
	 */
	public function generateUpdate(array $columns, array $identifiers): string{
		return sprintf(static::UPDATE_FORMAT,
			$this->getTableName(),
			implode(self::COMMA_SEPARATOR, array_map(
				function(string $k): string{
					return sprintf(self::PREPARED_ASSOC_FORMAT, $k);
				}, $columns
			)),
			implode(self::AND_SEPARATOR, array_map(
				function(string $k): string{
					return sprintf(self::PREPARED_ASSOC_FORMAT, $k);
				}, $identifiers
			)),
		);
	}
}
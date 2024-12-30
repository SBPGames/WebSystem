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

	private const INSERT_FORMAT = "INSERT INTO %s(%s) VALUES (%s) RETURNING *;";

	private string $tableName;

	public function __construct(string $tableName){
		$this->tableName = $tableName;
	}

	// GETTERS
	private function getTableName(): string{ return $this->tableName; }

	// FUNCTIONS
	public function generateSelect(
		array $filters, array $sortKeys, int $page, int $limit
	){
		$filtersPart = "";
		if(count($filters) > 0){
			$filtersPart = sprintf(static::WHERE_CLAUSE_FORMAT,
				implode(", ", array_map(
					function(string $k): string{
						return sprintf("%1\$s = :%1\$s", $k);
					}, array_keys($filters)
				))
			);
		}

		$sortKeysPart = "";
		if(count($sortKeys) > 0)
			$sortKeysPart = sprintf(static::ORDERBY_CLAUSE_FORMAT,
				implode(", ", array_map(
					function(string $k, bool $isDesc): string{
						return sprintf("%s %s", $k, $isDesc ? "DESC" : "ASC");
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
	public function generateInsert(array $columns){
		return sprintf(static::INSERT_FORMAT,
			$this->getTableName(),
			implode(", ", $columns),
			implode(", ", array_map(
				function(string $k): string{
					return sprintf(":%s", $k);
				}, $columns
			))
		);
	}
}
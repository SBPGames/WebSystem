<?php

namespace SBPGames\Framework\Service\Database;

use SBPGames\Framework\Service\Service;

/**
 * @package SBPGames\Framework\Service
 * @author Xibitol <contact@pimous.dev>
 */
class DatabaseService extends Service{

	public const SUPPORTED_DRIVERS = ["mysql"];

	public const DSN_FORMAT = "%s:%s";
	public const DSN_PARAMETER_FORMAT = "%s=%s";
	public const DSN_SEPARATOR_FORMAT = ";";

	private string $driver;
	private string $host;
	private ?string $port = null;
	private string $user;
	private ?string $password = null;
	private string $dbname;

	private \PDO $connection;

	public function __construct(){
		parent::__construct("database");
	}

	// GETTERS
	public function getMandatoryConfigFields(): array{
		return ["driver", "host", "user", "dbname"];
	}
	public function getConfigFields(): array{
		return ["driver", "host", "port", "user", "password", "dbname"];
	}
	private function getDSNFields(): array{
		return ["host", "port", "dbname"];
	}

	private function getDSN(): string{
		return sprintf(static::DSN_FORMAT,
			$this->driver,
			implode(static::DSN_SEPARATOR_FORMAT, array_map(
				function($param){
					return sprintf(static::DSN_PARAMETER_FORMAT,
						$param, strval($this->{$param})
					);
				},
				array_filter($this->getDSNFields(), function($param){
					return !is_null($this->{$param});
				})
			))
		);
	}

	// SETTERS
	private function setDriver(string $driver): void{
		if(!in_array($driver, \PDO::getAvailableDrivers())
			|| !in_array($driver, static::SUPPORTED_DRIVERS)
		)
			throw new \UnexpectedValueException(sprintf(
				"%s: Unsupported or unavailable pdo driver (%s);",
				static::class, $driver
			));

		$this->driver = $driver;
	}
	private function setHost(string $host): void{ $this->host = $host; }
	private function setPort(int $port): void{ $this->host = $port; }
	private function setUser(string $user): void{ $this->user = $user; }
	private function setPassword(string $pw): void{ $this->password = $pw; }
	private function setDBName(string $dbname): void{ $this->dbname = $dbname; }

	private function connect(): void{
		try{
			$this->connection = new \PDO(
				$this->getDSN(),
				$this->user, $this->password,
				[
					\PDO::ATTR_EMULATE_PREPARES => false,
					\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
					\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
				]
			);
		}catch(\PDOException $e){
			throw new \PDOException(sprintf(
				"%s: Unable to connect to the database %s at %s DBMS at %s:%s "
				."(%s);",
				static::class,
				$this->dbname,
				$this->driver,
				$this->host, $this->port ?? "default",
				$e->getMessage()
			));
		}

	}

	// FUNCTIONS
	/** @param ?array<string, mixed> $parameters */
	public function execute(string $query, ?array $parameters = null): int{
		return $this->exec($query, $parameters)->rowCount();
	}
	/**
	 * @param ?array<string, mixed> $parameters 
	 * @return array<string, array<string, mixed>>
	 */
	public function fetch(string $query, ?array $parameters = null): array{
		$stmt = $this->exec($query, $parameters);

		try {
			return $stmt->fetchAll();
		}catch(\PDOException $e){
			throw new \RuntimeException(sprintf(
				"Unable to fetch data from a database prepared statement (%s);",
				$e->getMessage()
			));
		}
	}

	/** @param ?array<string, mixed> $parameters */
	private function exec(
		string $query, ?array $parameters = null
	): \PDOStatement{
		try{
			$stmt = $this->connection->prepare($query);
		}catch(\PDOException $e){
			throw new \RuntimeException(sprintf(
				"Unable to prepare a database statement (%s);",
				$e->getMessage()
			));
		}

		try {
			$stmt->execute($parameters);
		}catch(\PDOException $e){
			throw new \RuntimeException(sprintf(
				"Unable to execute a database prepared statement (%s);",
				$e->getMessage()
			));
		}

		return $stmt;
	}


	// LIFECYCLE FUNCTIONS
	/** @param array<string, mixed> $config */
	public function init(array $config): void{
		parent::init($config);

		// Setting properties.
		$this->setDriver($config["driver"]);

		$this->setHost($config["host"]);
		if(isset($config["port"])){
			if(!is_int($config["port"]))
				throw new \UnexpectedValueException(sprintf(
					"%s: port must be an integer;",
					static::class
				));

			$this->setPort(intval($config["port"]));
		}

		$this->setUser($config["user"]);
		if(isset($config["password"])) $this->setPassword($config["password"]);

		$this->setDBName($config["dbname"]);

		// Establishing connection.
		$this->connect();
	}
}
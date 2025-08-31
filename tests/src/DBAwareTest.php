<?php

use PHPUnit\Framework\ExpectationFailedException;

/**
 * @todo add support for postgres, sqlite
 */
trait DBAwareTest {
	protected $db;

	private function __connect(): void {
		if ($this->db === null) {
			switch ($_ENV['TEST_DB_TYPE']) {
				case 'mysqli':
				case 'mariadb':
					$this->db = new mysqli($_ENV['TEST_DB_HOSTNAME'], $_ENV['TEST_DB_USER'], $_ENV['TEST_DB_PASSWORD'], $_ENV['TEST_DB_SCHEMA'], $_ENV['TEST_DB_PORT']);
					break;
				default:
					throw new ExpectationFailedException('Unsupported db type for testing: ' . $_ENV['TEST_DB_TYPE']);
			}
		}
	}

	protected function assertCanConnect(): void {
		$this->__connect();
	}

	/**
	 * @param string $sql
	 * @return bool|array[] array of results for SELECT, SHOW, DESCRIBE or EXPLAIN queries, true for other queries
	 */
	protected function query(string $sql): bool|array {
		$this->__connect();
		switch ($_ENV['TEST_DB_TYPE']) {
			case 'mysqli':
			case 'mariadb':
				$result = $this->db->query($sql);
				if ($result === false) {
					throw new ExpectationFailedException('Test query failed: ' . $sql);
				} elseif ($result !== true) {
					return $result->fetch_all(MYSQLI_ASSOC);
				}
				return $result;
			default:
				throw new ExpectationFailedException('Unsupported db type for testing: ' . $_ENV['TEST_DB_TYPE']);
		}
	}
}

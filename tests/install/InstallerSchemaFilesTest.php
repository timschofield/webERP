<?php

use PHPUnit\Framework\TestCase;

class InstallerSchemaFilesTest extends TestCase
{
	public function testTableDefinitionsAreUniqueByTableName(): void
	{
		$tableFiles = [];

		foreach (glob(dirname(__DIR__, 2) . '/install/sql/tables/*.sql') as $fileName) {
			$SQLScriptFile = file_get_contents($fileName);
			$this->assertNotFalse($SQLScriptFile, 'Failed reading installer sql file ' . $fileName);
			$matchResult = preg_match('/^CREATE +TABLE +(IF +NOT +EXISTS +)?`?([^ (`]+)`?/i', $SQLScriptFile, $matches);
			$this->assertSame(1, $matchResult, 'Missing CREATE TABLE statement in ' . $fileName);
			$tableFiles[strtolower($matches[2])][] = basename($fileName);
		}

		$duplicates = array_filter($tableFiles, static fn(array $files): bool => count($files) > 1);

		$this->assertSame([], $duplicates, 'Duplicate installer table definitions found');
	}
}

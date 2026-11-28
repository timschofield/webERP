<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/includes/HRPerformanceHelper.php';

class EmployeeNumberTest extends TestCase {
	public function testPadsShortEmployeeNumbersToSixCharacters(): void {
		$this->assertSame('000123', NormalizeEmployeeNumber('123'));
	}

	public function testKeepsSixCharacterEmployeeNumbersUnchanged(): void {
		$this->assertSame('ABC123', NormalizeEmployeeNumber('ABC123'));
	}

	public function testRejectsEmptyOrOversizedEmployeeNumbers(): void {
		$this->assertNull(NormalizeEmployeeNumber(''));
		$this->assertNull(NormalizeEmployeeNumber('1234567'));
	}
}

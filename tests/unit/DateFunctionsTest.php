<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/includes/DateFunctions.php';

class DateFunctionsTest extends TestCase
{
	protected function setUp(): void
	{
		if (session_status() !== PHP_SESSION_ACTIVE) {
			session_start();
		}
	}

	public function testYearFirstTwoDigitYearUsesTheYear(): void
	{
		$_SESSION['DefaultDateFormat'] = 'Y/m/d';
		$this->assertSame('2024-01-15', FormatDateForSQL('24/01/15'));
		$this->assertSame('1999-12-31', FormatDateForSQL('99/12/31'));
		$this->assertSame('2024-01-15', FormatDateForSQL('2024/01/15'));
		$this->assertSame('2024-01-15', FormatDateForSQL('24-01-15'));
		$this->assertSame('2024/01/31', LastDayOfMonth('24/01/15'));
	}

	public function testDayFirstTwoDigitYearStillUsesTheYear(): void
	{
		$_SESSION['DefaultDateFormat'] = 'd/m/Y';
		$this->assertSame('2024-01-15', FormatDateForSQL('15/01/24'));
	}
}

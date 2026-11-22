<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

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

	public function testFormatDateRespectsYearFirstFormatsWithoutChangingSession(): void
	{
		foreach (['Y/m/d' => '2024/01/15', 'Y-m-d' => '2024-01-15', 'm/d/Y' => '01/15/2024'] as $format => $date) {
			$_SESSION['DefaultDateFormat'] = $format;
			$this->assertSame($date, Format_Date($date));
			$this->assertSame($format, $_SESSION['DefaultDateFormat']);
		}
		$this->assertSame(0, Format_Date('invalid'));
		$_SESSION['DefaultDateFormat'] = 'Y/m/d';
		$this->assertSame('2024/01/15', Format_Date('20240115'));
		$this->assertSame('2024/01/15', Format_Date('240115'));
	}

	public function testSupportedDatesStillProduceExpectedResults(): void
	{
		$_SESSION['DefaultDateFormat'] = 'd/m/Y';
		$this->assertSame('20240115', SQLDateToEDI('2024-01-15'));
		$this->assertSame('1', DayOfWeekFromSQLDate('2024-01-15'));
		$this->assertSame('15', DayOfMonthFromSQLDate('2024-01-15'));
		$this->assertSame('15/01/2024 12:30:45', ConvertSQLDateTime('2024-01-15 12:30:45'));
		$this->assertSame('31/01/2024', LastDayOfMonth('15/01/2024'));
		$this->assertSame('14/02/2024', CalcDueDate('15/01/2024', 0, 30));
		$this->assertSame('16/01/2024', DateAdd('15/01/2024', 'd', 1));
		$this->assertSame(1, DateDiff('16/01/2024', '15/01/2024', 'd'));
	}

	#[DataProvider('malformedDates')]
	public function testMalformedDatesAreRejected(string $function, array $arguments): void
	{
		$_SESSION['DefaultDateFormat'] = 'd/m/Y';
		$this->expectException(InvalidArgumentException::class);
		$function(...$arguments);
	}

	public static function malformedDates(): array
	{
		return [
			['MonthAndYearFromSQLDate', ['invalid']],
			['YearAndMonthFromSQLDate', ['2024-01']],
			['DayOfWeekFromSQLDate', ['invalid']],
			['DayOfMonthFromSQLDate', ['2024-01']],
			['ConvertSQLDateTime', ['2024-01']],
			['SQLDateToEDI', ['invalid']],
			['ConvertToEDIDate', ['invalid']],
			['LastDayOfMonth', ['invalid']],
			['CalcDueDate', ['invalid', 0, 30]],
			['DateAdd', ['invalid', 'd', 1]],
			['DateDiff', ['01/01/2024', 'invalid', 'd']],
			['GetPeriod', ['invalid']],
		];
	}

	public function testUnsupportedDateFormatIsRejected(): void
	{
		$_SESSION['DefaultDateFormat'] = 'unsupported';
		$this->expectException(InvalidArgumentException::class);
		DateDiff('01/01/2024', '02/01/2024', 'd');
	}
}

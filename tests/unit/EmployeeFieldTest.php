<?php

use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class EmployeeFieldTest extends TestCase {
	protected function setUp(): void {
		function __($Text) {
			return $Text;
		}
		function DB_query($SQL) {
			$GLOBALS['EmployeeFieldQueries'][] = $SQL;
			$Rows = array(
				array(
					'employeeid' => 1,
					'employeenumber' => '0012',
					'userid' => 'user',
					'name' => 'Alice & Bob',
					'positiontitle' => 'Manager',
					'status' => 'Active',
					'positionid' => 7,
				),
				array(
					'employeeid' => 2,
					'employeenumber' => '34',
					'userid' => '',
					'name' => 'Former Employee',
					'positiontitle' => null,
					'status' => 'Terminated',
					'positionid' => 8,
				),
				array(
					'employeeid' => 3,
					'employeenumber' => '35',
					'userid' => '',
					'name' => 'On Leave',
					'positiontitle' => null,
					'status' => 'On Leave',
					'positionid' => 8,
				),
				array(
					'employeeid' => 4,
					'employeenumber' => '36',
					'userid' => '',
					'name' => 'Suspended',
					'positiontitle' => null,
					'status' => 'Suspended',
					'positionid' => 8,
				),
				array(
					'employeeid' => 5,
					'employeenumber' => '37',
					'userid' => '',
					'name' => 'Unspecified Status',
					'positiontitle' => null,
					'status' => null,
					'positionid' => 8,
				),
			);
			$Rows = array_filter($Rows, function ($Row) use ($SQL) {
				if (str_contains($SQL, "e.employmentstatus = 'Active'") && $Row['status'] != 'Active') {
					return false;
				}
				if (str_contains($SQL, "e.employmentstatus <> 'Terminated'")) {
					if ($Row['status'] == 'Terminated') {
						return false;
					}
					if ($Row['status'] === null && !str_contains($SQL, 'e.employmentstatus IS NULL')) {
						return false;
					}
				}
				if (str_contains($SQL, "e.employeeid != '" . $Row['employeeid'] . "'")) {
					return false;
				}
				if (str_contains($SQL, 'AND 1=0')) {
					return false;
				}
				if (str_contains($SQL, 'e.positionid IN (7)') && $Row['positionid'] != 7) {
					return false;
				}
				return true;
			});
			return new ArrayIterator(array_values($Rows));
		}
		function DB_fetch_array($Result) {
			if (!$Result->valid()) {
				return false;
			}
			$Row = $Result->current();
			$Result->next();
			return $Row;
		}
		$GLOBALS['EmployeeFieldQueries'] = array();
		require_once dirname(__DIR__, 2) . '/includes/UIGeneralFunctions.php';
	}

	public function testFeedbackFiltersIncludeHistoricalEmployeesAndKeepIndependentSelections(): void {
		$Options = array(
			'AllEmployees' => true,
			'Display' => 'user-name-number',
			'Order' => 'userid',
			'Placeholder' => 'All Employees',
		);
		$From = FieldToSelectOneEmployee('FromEmployeeID', 1, 'From Employee', Required: false, Options: $Options);
		$About = FieldToSelectOneEmployee('AboutEmployeeID', 2, 'About Employee', Required: false, Options: $Options);
		$this->assertStringContainsString('<option value="">All Employees</option>', $From);
		$this->assertStringContainsString('<option value="1" selected="selected">user - Alice &amp; Bob (000012)', $From);
		$this->assertStringContainsString('<option value="2" selected="selected">Former Employee (000034)', $About);
		$this->assertStringNotContainsString('required="required"', $From);
		$this->assertCount(1, $GLOBALS['EmployeeFieldQueries']);
		$this->assertStringNotContainsString('employmentstatus', $GLOBALS['EmployeeFieldQueries'][0]);
	}

	public function testEmployeeNumberAndAutoSubmitArePreserved(): void {
		$HTML = FieldToSelectOneEmployee(
			'EmployeeNumber', '0012', 'Employee',
			Options: array('Value' => 'number', 'Display' => 'name-position', 'SubmitOnChange' => true)
		);
		$this->assertStringContainsString('onchange="this.form.submit()"', $HTML);
		$this->assertStringContainsString('required="required"', $HTML);
		$this->assertStringContainsString('<option value="0012" selected="selected">Alice &amp; Bob - Manager', $HTML);
		$this->assertStringNotContainsString('Former Employee', $HTML);
		$this->assertStringContainsString('LEFT JOIN hrpositions', $GLOBALS['EmployeeFieldQueries'][0]);
	}

	public function testDefaultIncludesNonTerminatedEmployees(): void {
		$HTML = FieldToSelectOneEmployee('EmployeeID', null, 'Employee');
		$this->assertStringContainsString('Alice &amp; Bob', $HTML);
		$this->assertStringContainsString('On Leave', $HTML);
		$this->assertStringContainsString('Suspended', $HTML);
		$this->assertStringContainsString('Unspecified Status', $HTML);
		$this->assertStringNotContainsString('Former Employee', $HTML);
	}

	public function testUserNameAndNumberDisplayKeepsEmployeeNumberValues(): void {
		$HTML = FieldToSelectOneEmployee(
			'EmployeeNumber', '0012', 'Employee',
			Options: array('Value' => 'number', 'Display' => 'user-name-number', 'AllEmployees' => true)
		);
		$this->assertStringContainsString(
			'<option value="0012" selected="selected">user - Alice &amp; Bob (000012)</option>',
			$HTML
		);
		$this->assertStringContainsString('<option value="34">Former Employee (000034)</option>', $HTML);
	}

	public function testReviewerRestrictionsAndSupervisorExclusion(): void {
		$Reviewer = FieldToSelectOneEmployee(
			'ReviewerID', null, 'Reviewer', Required: false,
			Options: array('PositionIDs' => array(7), 'Placeholder' => 'Select Reviewer')
		);
		$NoReviewers = FieldToSelectOneEmployee(
			'ReviewerID', null, 'Reviewer', Required: false, Options: array('PositionIDs' => array())
		);
		$Supervisor = FieldToSelectOneEmployee(
			'SupervisorID', null, 'Supervisor', Required: false, Options: array('ExcludeEmployeeID' => 1)
		);
		$this->assertStringContainsString('Alice &amp; Bob', $Reviewer);
		$this->assertStringContainsString('e.positionid IN (7)', $GLOBALS['EmployeeFieldQueries'][0]);
		$this->assertStringContainsString('AND 1=0', $GLOBALS['EmployeeFieldQueries'][1]);
		$this->assertStringNotContainsString('Alice &amp; Bob', $NoReviewers);
		$this->assertStringNotContainsString('Alice &amp; Bob', $Supervisor);
		$this->assertStringContainsString("e.employeeid != '1'", $GLOBALS['EmployeeFieldQueries'][2]);
	}
}

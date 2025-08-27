<?php

// Classifies accounts in any of the three sections of statement of cash flows to assign each account to an activity.
// This program is under the GNU General Public License, last version. 2016-10-08.
// This creative work is under the CC BY-NC-SA, last version. 2016-10-08.

// BEGIN: Procedure division ---------------------------------------------------
require(__DIR__ . '/includes/session.php');

$Title = __('Cash Flows Activities Maintenance');
$ViewTopic = 'GeneralLedger';
$BookMark = 'GLCashFlowsSetup';
include('includes/header.php');

// Merges gets into posts:
if(isset($_GET['Action'])) {
	$_POST['Action'] = $_GET['Action'];
}
if(isset($_GET['PeriodProfitAccount'])) {
	$_POST['PeriodProfitAccount'] = $_GET['PeriodProfitAccount'];
}
if(isset($_GET['RetainedEarningsAccount'])) {
	$_POST['RetainedEarningsAccount'] = $_GET['RetainedEarningsAccount'];
}
// Do selected action:
switch($_POST['Action']) {
	case 'Update':
		// Updates config accounts:
		if($_SESSION['PeriodProfitAccount'] != $_POST['PeriodProfitAccount'] ) {
			if(DB_query(
				"UPDATE config SET confvalue = '" . $_POST['PeriodProfitAccount'] . "' WHERE confname = 'PeriodProfitAccount'",
				__('Can not update chartmaster.cashflowsactivity because')
				)) {
				$_SESSION['PeriodProfitAccount'] = $_POST['PeriodProfitAccount'];
				prnMsg(__('The net profit of the period GL account was updated'), 'success');
			}
		}
		if($_SESSION['RetainedEarningsAccount'] != $_POST['RetainedEarningsAccount'] ) {
			if(DB_query(
				"UPDATE companies SET retainedearnings = '" . $_POST['RetainedEarningsAccount'] . "' WHERE coycode = 1",
				__('Can not update chartmaster.cashflowsactivity because')
				)) {
				$_SESSION['RetainedEarningsAccount'] = $_POST['RetainedEarningsAccount'];
				prnMsg(__('The retained earnings GL account was updated'), 'success');
			}
		}
		break;// END Update.
	case 'Reset':
		$Sql = "UPDATE `chartmaster` SET `cashflowsactivity`='-1';";
		$ErrMsg = __('Can not update chartmaster.cashflowsactivity because');
		$Result = DB_query($Sql, $ErrMsg);
		if($Result) {
			prnMsg(__('The cash flow activity was reset in all accounts'), 'success');
		}
		break;// END Reset.
	case 'Automatic':
		// Loads the criteria for assigning the cash flow activity to the account:
		// The last criterion overwrites the previous criteria. E.g.:
		// In English, use singular to englobe singular and plural (e.g. Loan vs. Loans).
		// Leave penultimate: Interests (e.g. Loan interests vs. Loans).
		// Leave last: depreciations, amortisations and adjustments (Building depreciation vs. Buildings).
		// Comment: MySQL queries are not case-sensitive by default.

		$Criterion = array();
		$i = 0;

		$Criterion[$i]['AccountLike'] = __('Cash');
		$Criterion[$i++]['CashFlowsActivity'] = 4;

		$Criterion[$i]['AccountLike'] = __('Bank');
		$Criterion[$i++]['CashFlowsActivity'] = 4;

		$Criterion[$i]['AccountLike'] = __('Investment');
		$Criterion[$i++]['CashFlowsActivity'] = 4;

		$Criterion[$i]['AccountLike'] = __('Commission');
		$Criterion[$i++]['CashFlowsActivity'] = 3;

		$Criterion[$i]['AccountLike'] = __('Share');
		$Criterion[$i++]['CashFlowsActivity'] = 3;

		$Criterion[$i]['AccountLike'] = __('Dividend');
		$Criterion[$i++]['CashFlowsActivity'] = 3;

		$Criterion[$i]['AccountLike'] = __('Interest');
		$Criterion[$i++]['CashFlowsActivity'] = 3;

		$Criterion[$i]['AccountLike'] = __('Loan');
		$Criterion[$i++]['CashFlowsActivity'] = 3;

		$Criterion[$i]['AccountLike'] = __('Building');
		$Criterion[$i++]['CashFlowsActivity'] = 2;

		$Criterion[$i]['AccountLike'] = __('Equipment');
		$Criterion[$i++]['CashFlowsActivity'] = 2;

		$Criterion[$i]['AccountLike'] = __('Land');
		$Criterion[$i++]['CashFlowsActivity'] = 2;

		$Criterion[$i]['AccountLike'] = __('Vehicle');
		$Criterion[$i++]['CashFlowsActivity'] = 2;

		$Criterion[$i]['AccountLike'] = __('Sale');
		$Criterion[$i++]['CashFlowsActivity'] = 1;

		$Criterion[$i]['AccountLike'] = __('Cost');
		$Criterion[$i++]['CashFlowsActivity'] = 1;

		$Criterion[$i]['AccountLike'] = __('Receivable');
		$Criterion[$i++]['CashFlowsActivity'] = 1;

		$Criterion[$i]['AccountLike'] = __('Inventory');
		$Criterion[$i++]['CashFlowsActivity'] = 1;

		$Criterion[$i]['AccountLike'] = __('Payable');
		$Criterion[$i++]['CashFlowsActivity'] = 1;

		$Criterion[$i]['AccountLike'] = __('Adjustment');
		$Criterion[$i++]['CashFlowsActivity'] = 0;

		$Criterion[$i]['AccountLike'] = __('Amortisation');
		$Criterion[$i++]['CashFlowsActivity'] = 0;

		$Criterion[$i]['AccountLike'] = __('Depreciation');
		$Criterion[$i++]['CashFlowsActivity'] = 0;

		foreach($Criterion as $Criteria) {
			$Sql = "UPDATE `chartmaster`
				SET `cashflowsactivity`=". $Criteria['CashFlowsActivity'] . "
				WHERE `accountname` LIKE '%". addslashes(__($Criteria['AccountLike'])) . "%'
				AND `cashflowsactivity`=-1";// Uses cashflowsactivity=-1 to NOT overwrite.
			$ErrMsg = __('Can not update chartmaster.cashflowsactivity. Error code:');
			$Result = DB_query($Sql, $ErrMsg);
			// RChacon: Count replacements.
		}
		if($Result) {
			prnMsg(__('The cash flow activity was updated in some accounts'), 'success');// RChacon: Show replacements done.
		}
		break;// END Automatic.
	case 'Manual':
		echo "<script>window.location = 'GLAccounts.php';</script>";
		exit();
	default:
		// No reset , nor Automatic
}

echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme, '/images/maintenance.png" title="', // Icon image.
	$Title, '" /> ', // Icon title.
	$Title, '</p>';// Page title.

echo '<div class="page_help_text">',
	__('The statement of cash flows, using direct and indirect methods, is partitioned into three sections: operating activities, investing activities and financing activities.'), '<br />',
	__('You must classify all accounts in any of those three sections of the cash flow statement, or as no effect on cash flow, or as cash or cash equivalent.'),
	 '</div>';

// Show a form to allow input of the action for the script to do:
echo '<form action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '" method="post">',
	'<input name="FormID" type="hidden" value="', $_SESSION['FormID'], '" />', // Form's head.
		// Input table:
		'<fieldset>',
		// Content of the header and footer of the output table:
		'<legend>', __('Action to do'), '</legend>';
$Sql = "SELECT accountcode, accountname
		FROM chartmaster
			LEFT JOIN accountgroups ON chartmaster.group_=accountgroups.groupname
		WHERE accountgroups.pandl=0
		ORDER BY accountcode";
$GLAccounts = DB_query($Sql);
// Setups the net profit for the period GL account:
echo		'<field>
				<label for="PeriodProfitAccount">', __('Net profit for the period GL account'), ':</label>
	 			<select id="PeriodProfitAccount" name="PeriodProfitAccount" required="required">';
if(!isset($_SESSION['PeriodProfitAccount']) OR $_SESSION['PeriodProfitAccount']=='') {
	$Result = DB_fetch_array(DB_query("SELECT confvalue FROM `config` WHERE confname ='PeriodProfitAccount'"));
	if($Result == NULL) {// If $Result is NULL (false, 0, or the empty; because we use "==", instead of "==="), the parameter NOT exists so creates it.
		echo		'<option value="">', __('Select...'), '</option>';
		// Creates a configuration parameter for the net profit for the period GL account:
		$Sql = "INSERT INTO `config` (confname, confvalue) VALUES ('PeriodProfitAccount', '" . $Result['accountcode'] . "')";
		$ErrMsg = __('Could not add the new account code');
		$Result = DB_query($Sql, $ErrMsg);
		$_SESSION['PeriodProfitAccount'] = '';
	} else {// If $Result is NOT NULL, the parameter exists so gets it.
		$_SESSION['PeriodProfitAccount'] = $Result['confvalue'];
	}
}
while($MyRow = DB_fetch_array($GLAccounts)) {
	echo			'<option', ($MyRow['accountcode'] == $_SESSION['PeriodProfitAccount'] ? ' selected="selected"' : '' ), ' value="', $MyRow['accountcode'], '">', $MyRow['accountcode'], ' - ', $MyRow['accountname'], '</option>';
}
echo				'</select>
				<fieldhelp>', __('GL account to post the net profit for the period'), '</fieldhelp>
		 	</field>';
// Setups the retained earnings GL account:
echo		'<field>
				<label for="RetainedEarningsAccount">', __('Retained earnings GL account'), ':</label>
	 			<select id="RetainedEarningsAccount" name="RetainedEarningsAccount" required="required">';
if(!isset($_SESSION['RetainedEarningsAccount']) OR $_SESSION['RetainedEarningsAccount']=='') {
	$Result = DB_fetch_array(DB_query("SELECT retainedearnings FROM `companies` WHERE `coycode`=1"));
	if($Result == NULL) {// If $Result is NULL (false, 0, or the empty; because we use "==", instead of "==="), the parameter NOT exists.
		echo		'<option value="">', __('Select...'), '</option>';
		$_SESSION['RetainedEarningsAccount'] = '';
	} else {// If $Result is NOT NULL, the parameter exists so gets it.
		$_SESSION['RetainedEarningsAccount'] = $Result['retainedearnings'];
	}
}
DB_data_seek($GLAccounts,0);
while($MyRow = DB_fetch_array($GLAccounts)) {
	echo			'<option', ($MyRow['accountcode'] == $_SESSION['RetainedEarningsAccount'] ? ' selected="selected"' : '' ), ' value="', $MyRow['accountcode'], '">', $MyRow['accountcode'], ' - ', $MyRow['accountname'], '</option>';
}
echo				'</select>
				<fieldhelp>', __('GL account to post the retained earnings'), '</fieldhelp>
			</field>
		</fieldset>
		<div class="centre">',
			'<button name="Action" type="submit" value="Update"><img alt="" src="', $RootPath, '/css/', $Theme, '/images/tick.svg" /> ', __('Update'), '</button>', // "Update" button.
			'<button name="Action" type="submit" value="Reset"><img alt="" src="', $RootPath, '/css/', $Theme, '/images/cross.svg" /> ', __('Reset values'), '</button>', // "Reset values" button.
			'<button name="Action" type="submit" value="Automatic"><img alt="" src="', $RootPath, '/css/', $Theme, '/images/next.png" width="20px" /> ', __('Automatic setup'), '</button>', // "Automatic setup" button.
			'<button name="Action" type="submit" value="Manual"><img alt="" src="', $RootPath, '/css/', $Theme, '/images/previous.png" width="20px" /> ', __('Manual setup'), '</button>', // "Manual setup" button.
			'<button onclick="window.location=\'' . $RootPath . '/index.php?Application=GL\'" type="button"><img alt="" src="', $RootPath, '/css/', $Theme, '/images/return.svg" /> ', __('Return'), '</button>', // "Return" button.
		'</div>
	</form>';

include('includes/footer.php');
// END: Procedure division -----------------------------------------------------

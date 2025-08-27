<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Payment Terms Maintenance');
$ViewTopic = 'PaymentTerms';
$BookMark = 'PaymentTerms';
include('includes/header.php');

echo '<p class="page_title_text">
		<img src="'.$RootPath.'/css/'.$Theme.'/images/money_add.png" title="' . __('Payment Terms') . '" alt="" />' . ' ' . $Title .
	'</p>';

if (isset($_GET['SelectedTerms'])){
	$SelectedTerms = $_GET['SelectedTerms'];
} elseif (isset($_POST['SelectedTerms'])){
	$SelectedTerms = $_POST['SelectedTerms'];
}

$Errors = array();

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */
	$i=1;

	//first off validate inputs are sensible

	if (mb_strlen($_POST['TermsIndicator']) < 1) {
		$InputError = 1;
		prnMsg(__('The payment terms name must exist'),'error');
		$Errors[$i] = 'TermsIndicator';
		$i++;
	}
	if (mb_strlen($_POST['TermsIndicator']) > 2) {
		$InputError = 1;
		prnMsg(__('The payment terms name must be two characters or less long'),'error');
		$Errors[$i] = 'TermsIndicator';
		$i++;
	}
	if (empty($_POST['DayNumber']) OR !is_numeric(filter_number_format($_POST['DayNumber'])) OR filter_number_format($_POST['DayNumber']) <= 0){
		$InputError = 1;
		prnMsg( __('The number of days or the day in the following month must be numeric') ,'error');
		$Errors[$i] = 'DayNumber';
		$i++;
	}
	if (empty($_POST['Terms']) OR mb_strlen($_POST['Terms']) > 40) {
		$InputError = 1;
		prnMsg( __('The terms description must be forty characters or less long') ,'error');
		$Errors[$i] = 'Terms';
		$i++;
	}
	/*
	if ($_POST['DayNumber'] > 30 AND empty($_POST['DaysOrFoll'])) {
		$InputError = 1;
		prnMsg( __('When the check box is not checked to indicate a day in the following month is the due date') . ', ' . __('the due date cannot be a day after the 30th') . '. ' . __('A number between 1 and 30 is expected') ,'error');
		$Errors[$i] = 'DayNumber';
		$i++;
	} */
	if ($_POST['DayNumber']>360 AND !empty($_POST['DaysOrFoll'])) {
		$InputError = 1;
		prnMsg( __('When the check box is checked to indicate that the term expects a number of days after which accounts are due') . ', ' . __('the number entered should be less than 361 days') ,'error');
		$Errors[$i] = 'DayNumber';
		$i++;
	}

	if (isset($SelectedTerms) AND $InputError !=1) {

		/*SelectedTerms could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/

		if (isset($_POST['DaysOrFoll']) AND $_POST['DaysOrFoll']=='on') {
			$SQL = "UPDATE paymentterms SET
							terms='" . $_POST['Terms'] . "',
							dayinfollowingmonth=0,
							daysbeforedue='" . filter_number_format($_POST['DayNumber']) . "'
					WHERE termsindicator = '" . $SelectedTerms . "'";
		} else {
			$SQL = "UPDATE paymentterms SET
							terms='" . $_POST['Terms'] . "',
							dayinfollowingmonth='" . filter_number_format($_POST['DayNumber']) . "',
							daysbeforedue=0
						WHERE termsindicator = '" . $SelectedTerms . "'";
		}

		$Msg = __('The payment terms definition record has been updated') . '.';
	} else if ($InputError !=1) {

	/*Selected terms is null cos no item selected on first time round so must be adding a record must be submitting new entries in the new payment terms form */

		if ($_POST['DaysOrFoll']=='on') {
			$SQL = "INSERT INTO paymentterms (termsindicator,
								terms,
								daysbeforedue,
								dayinfollowingmonth)
						VALUES (
							'" . $_POST['TermsIndicator'] . "',
							'" . $_POST['Terms'] . "',
							'" . filter_number_format($_POST['DayNumber']) . "',
							0
						)";
		} else {
			$SQL = "INSERT INTO paymentterms (termsindicator,
								terms,
								daysbeforedue,
								dayinfollowingmonth)
						VALUES (
							'" . $_POST['TermsIndicator'] . "',
							'" . $_POST['Terms'] . "',
							0,
							'" . filter_number_format($_POST['DayNumber']) . "'
							)";
		}

		$Msg = __('The payment terms definition record has been added') . '.';
	}
	if ($InputError !=1){
		//run the SQL from either of the above possibilites
		$Result = DB_query($SQL);
		prnMsg($Msg,'success');
		unset($SelectedTerms);
		unset($_POST['DaysOrFoll']);
		unset($_POST['TermsIndicator']);
		unset($_POST['Terms']);
		unset($_POST['DayNumber']);
	}

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS IN DebtorsMaster

	$SQL= "SELECT COUNT(*) FROM debtorsmaster WHERE debtorsmaster.paymentterms = '" . $SelectedTerms . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0] > 0) {
		prnMsg( __('Cannot delete this payment term because customer accounts have been created referring to this term'),'warn');
		echo '<br /> ' . __('There are') . ' ' . $MyRow[0] . ' ' . __('customer accounts that refer to this payment term');
	} else {
		$SQL= "SELECT COUNT(*) FROM suppliers WHERE suppliers.paymentterms = '" . $SelectedTerms . "'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_row($Result);
		if ($MyRow[0] > 0) {
			prnMsg( __('Cannot delete this payment term because supplier accounts have been created referring to this term'),'warn');
			echo '<br /> ' . __('There are') . ' ' . $MyRow[0] . ' ' . __('supplier accounts that refer to this payment term');
		} else {
			//only delete if used in neither customer or supplier accounts

			$SQL="DELETE FROM paymentterms WHERE termsindicator='" . $SelectedTerms . "'";
			$Result = DB_query($SQL);
			prnMsg( __('The payment term definition record has been deleted') . '!','success');
		}
	}
	//end if payment terms used in customer or supplier accounts

}

if (!isset($SelectedTerms)) {

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedTerms will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of payment termss will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$SQL = "SELECT termsindicator, terms, daysbeforedue, dayinfollowingmonth FROM paymentterms";
	$Result = DB_query($SQL);

	echo '<table class="selection">';
	echo '<thead>
			<tr>
				<th colspan="6"><h3>' . __('Payment Terms.') . '</h3></th>
			</tr>';
	echo '<tr>
			<th class="SortedColumn">' . __('Term Code') . '</th>
			<th class="SortedColumn">' . __('Description') . '</th>
			<th class="SortedColumn">' . __('Following Month On') . '</th>
			<th class="SortedColumn">' . __('Due After (Days)') . '</th>
		</tr>
	</thead>';

	while ($MyRow=DB_fetch_array($Result)) {

		if ($MyRow['dayinfollowingmonth']==0) {
			$FollMthText = __('N/A');
		} else {
			$FollMthText = $MyRow['dayinfollowingmonth'] . __('th');
		}

		if ($MyRow['daysbeforedue']==0) {
			$DueAfterText = __('N/A');
		} else {
			$DueAfterText = $MyRow['daysbeforedue'] . ' ' . __('days');
		}

	echo '<tr class="striped_row">
			<td>', $MyRow['termsindicator'], '</td>
			<td>', $MyRow['terms'], '</td>
			<td>', $FollMthText, '</td>
			<td>', $DueAfterText, '</td>
			<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'), '?SelectedTerms=', $MyRow[0], '">' . __('Edit') . '</a></td>
			<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'), '?SelectedTerms=', $MyRow[0], '&amp;delete=1" onclick="return confirm(\'' . __('Are you sure you wish to delete this payment term?') . '\');">' . __('Delete') . '</a></td>
		</tr>';

	} //END WHILE LIST LOOP
	echo '</table>';
} //end of ifs and buts!

if (isset($SelectedTerms)) {
	echo '<div class="centre">
			<a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . __('Show all Payment Terms Definitions') . '</a>
		</div>';
}

if (!isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($SelectedTerms)) {
		//editing an existing payment terms

		$SQL = "SELECT termsindicator,
						terms,
						daysbeforedue,
						dayinfollowingmonth
					FROM paymentterms
					WHERE termsindicator='" . $SelectedTerms . "'";

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

		$_POST['TermsIndicator'] = $MyRow['termsindicator'];
		$_POST['Terms']  = $MyRow['terms'];
		$DaysBeforeDue  = $MyRow['daysbeforedue'];
		$DayInFollowingMonth  = $MyRow['dayinfollowingmonth'];

		echo '<input type="hidden" name="SelectedTerms" value="' . $SelectedTerms . '" />';
		echo '<input type="hidden" name="TermsIndicator" value="' . $_POST['TermsIndicator'] . '" />';
		echo '<fieldset>';
		echo '<legend>' . __('Update Payment Terms.') . '</legend>';
		echo '<field>
				<label for="TermsIndicator">' . __('Term Code') . ':</label>
				<fieldtext>' . $_POST['TermsIndicator'] . '</fieldtext>
			</field>';

	} else { //end of if $SelectedTerms only do the else when a new record is being entered

		if (!isset($_POST['TermsIndicator'])) $_POST['TermsIndicator']='';
		if (!isset($DaysBeforeDue)) {
			$DaysBeforeDue=0;
		}
		//if (!isset($DayInFollowingMonth)) $DayInFollowingMonth=0;
		unset($DayInFollowingMonth); // Rather unset for a new record
		if (!isset($_POST['Terms'])) {
			$_POST['Terms']='';
		}

		echo '<fieldset>';
		echo '<legend>' . __('New Payment Terms.') . '</legend>';
		echo '<field>
				<label for="TermsIndicator">' . __('Term Code') . ':</label>
				<input type="text" name="TermsIndicator"' . (in_array('TermsIndicator',$Errors) ? 'class="inputerror"' : '' ) .' autofocus="autofocus" required="required" pattern="[0-9a-ZA-Z_]*" title="" value="' . $_POST['TermsIndicator'] . '" size="3" maxlength="2" />
				<fieldhelp>' . __('A 2 character code to identify this payment term. Any alpha-numeric characters can be used') . '</fieldhelp>
			</field>';
	}

	echo '<field>
			<label for="Terms">' .  __('Terms Description'). ':</label>
			<input type="text"' . (in_array('Terms',$Errors) ? 'class="inputerror"' : '' ) .' name="Terms" ' . (isset($SelectedTerms)? 'autofocus="autofocus"': '') . ' required="required" value="'.$_POST['Terms']. '" title="" size="35" maxlength="40" />
			<fieldhelp>' . __('A description of the payment terms is required') . '</fieldhelp>
		</field>';

	echo '<field>
			<label for="DaysOrFoll">' . __('Due After A Given No. Of Days').':</label>
			<input type="checkbox" name="DaysOrFoll" ';
	if (isset($DayInFollowingMonth) AND !$DayInFollowingMonth) {
		echo 'checked';
	}
	echo ' /></td>
		</field>';

	echo '<field>
			<label for="DayNumber">' . __('Days (Or Day In Following Month)').':</label>
			<input type="text" ' . (in_array('DayNumber',$Errors) ? 'class="inputerror"' : '' ) .' name="DayNumber" required="required" class="integer"  size="4" maxlength="3" value="';
	if ($DaysBeforeDue !=0) {
		echo locale_number_format($DaysBeforeDue,0);
	} else {
		if (isset($DayInFollowingMonth)) {
			echo locale_number_format($DayInFollowingMonth,0);
		}
	}
	echo '" />
		<field>
	</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="submit" value="'.__('Enter Information').'" />
		</div>';
	echo '</form>';
} //end if record deleted no point displaying form to add record

include('includes/footer.php');

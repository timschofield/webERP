<?php

/* $Id: PaymentTerms.php 6310 2013-08-29 10:42:50Z daintree $*/

include('includes/session.inc');

$Title = _('Payment Terms Maintenance');

include('includes/header.inc');

echo '<p class="page_title_text">
		<img src="'.$RootPath.'/css/'.$Theme.'/images/money_add.png" title="' . _('Payment Terms') . '" alt="" />' . ' ' . $Title .
	'</p>';

if (isset($_GET['SelectedTerms'])){
	$SelectedTerms = $_GET['SelectedTerms'];
} elseif (isset($_POST['SelectedTerms'])){
	$SelectedTerms = $_POST['SelectedTerms'];
}

if (isset($Errors)) {
	unset($Errors);
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
		prnMsg(_('The payment terms name must exist'),'error');
		$Errors[$i] = 'TermsIndicator';
		$i++;
	}
	if (mb_strlen($_POST['TermsIndicator']) > 2) {
		$InputError = 1;
		prnMsg(_('The payment terms name must be two characters or less long'),'error');
		$Errors[$i] = 'TermsIndicator';
		$i++;
	}
	if (empty($_POST['DayNumber']) OR !is_numeric(filter_number_format($_POST['DayNumber'])) OR filter_number_format($_POST['DayNumber']) <= 0){
		$InputError = 1;
		prnMsg( _('The number of days or the day in the following month must be numeric') ,'error');
		$Errors[$i] = 'DayNumber';
		$i++;
	}
	if (empty($_POST['Terms']) OR mb_strlen($_POST['Terms']) > 40) {
		$InputError = 1;
		prnMsg( _('The terms description must be forty characters or less long') ,'error');
		$Errors[$i] = 'Terms';
		$i++;
	}

	if ($_POST['DayNumber'] > 30 AND empty($_POST['DaysOrFoll'])) {
		$InputError = 1;
		prnMsg( _('When the check box is not checked to indicate a day in the following month is the due date') . ', ' . _('the due date cannot be a day after the 30th') . '. ' . _('A number between 1 and 30 is expected') ,'error');
		$Errors[$i] = 'DayNumber';
		$i++;
	}
	if ($_POST['DayNumber']>360 AND !empty($_POST['DaysOrFoll'])) {
		$InputError = 1;
		prnMsg( _('When the check box is checked to indicate that the term expects a number of days after which accounts are due') . ', ' . _('the number entered should be less than 361 days') ,'error');
		$Errors[$i] = 'DayNumber';
		$i++;
	}

	if (isset($SelectedTerms) AND $InputError !=1) {

		/*SelectedTerms could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/

		if (isset($_POST['DaysOrFoll']) AND $_POST['DaysOrFoll']=='on') {
			$sql = "UPDATE paymentterms SET
							terms='" . $_POST['Terms'] . "',
							dayinfollowingmonth=0,
							daysbeforedue='" . filter_number_format($_POST['DayNumber']) . "'
					WHERE termsindicator = '" . $SelectedTerms . "'";
		} else {
			$sql = "UPDATE paymentterms SET
							terms='" . $_POST['Terms'] . "',
							dayinfollowingmonth='" . filter_number_format($_POST['DayNumber']) . "',
							daysbeforedue=0
						WHERE termsindicator = '" . $SelectedTerms . "'";
		}

		$msg = _('The payment terms definition record has been updated') . '.';
	} else if ($InputError !=1) {

	/*Selected terms is null cos no item selected on first time round so must be adding a record must be submitting new entries in the new payment terms form */

		if ($_POST['DaysOrFoll']=='on') {
			$sql = "INSERT INTO paymentterms (termsindicator,
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
			$sql = "INSERT INTO paymentterms (termsindicator,
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

		$msg = _('The payment terms definition record has been added') . '.';
	}
	if ($InputError !=1){
		//run the SQL from either of the above possibilites
		$result = DB_query($sql,$db);
		prnMsg($msg,'success');
		unset($SelectedTerms);
		unset($_POST['DaysOrFoll']);
		unset($_POST['TermsIndicator']);
		unset($_POST['Terms']);
		unset($_POST['DayNumber']);
	}

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS IN DebtorsMaster

	$sql= "SELECT COUNT(*) FROM debtorsmaster WHERE debtorsmaster.paymentterms = '" . $SelectedTerms . "'";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0] > 0) {
		prnMsg( _('Cannot delete this payment term because customer accounts have been created referring to this term'),'warn');
		echo '<br /> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('customer accounts that refer to this payment term');
	} else {
		$sql= "SELECT COUNT(*) FROM suppliers WHERE suppliers.paymentterms = '" . $SelectedTerms . "'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ($myrow[0] > 0) {
			prnMsg( _('Cannot delete this payment term because supplier accounts have been created referring to this term'),'warn');
			echo '<br /> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('supplier accounts that refer to this payment term');
		} else {
			//only delete if used in neither customer or supplier accounts

			$sql="DELETE FROM paymentterms WHERE termsindicator='" . $SelectedTerms . "'";
			$result = DB_query($sql,$db);
			prnMsg( _('The payment term definition record has been deleted') . '!','success');
		}
	}
	//end if payment terms used in customer or supplier accounts

}

if (!isset($SelectedTerms)) {

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedTerms will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of payment termss will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$sql = "SELECT termsindicator, terms, daysbeforedue, dayinfollowingmonth FROM paymentterms";
	$result = DB_query($sql, $db);

	echo '<table class="selection">';
	echo '<tr>
			<th colspan="6"><h3>' . _('Payment Terms.') . '</h3></th>
		</tr>';
	echo '<tr>
			<th>' . _('Term Code') . '</th>
			<th>' . _('Description') . '</th>
			<th>' . _('Following Month On') . '</th>
			<th>' . _('Due After (Days)') . '</th>
		</tr>';

	while ($myrow=DB_fetch_array($result)) {

		if ($myrow['dayinfollowingmonth']==0) {
			$FollMthText = _('N/A');
		} else {
			$FollMthText = $myrow['dayinfollowingmonth'] . _('th');
		}

		if ($myrow['daysbeforedue']==0) {
			$DueAfterText = _('N/A');
		} else {
			$DueAfterText = $myrow['daysbeforedue'] . ' ' . _('days');
		}

	printf('<tr><td>%s</td>
	        <td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td><a href="%s?SelectedTerms=%s">' . _('Edit') . '</a></td>
			<td><a href="%s?SelectedTerms=%s&amp;delete=1" onclick="return confirm(\'' . _('Are you sure you wish to delete this payment term?') . '\');">' . _('Delete') . '</a></td>
			</tr>',
			$myrow['termsindicator'],
			$myrow['terms'],
			$FollMthText,
			$DueAfterText,
			htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'),
			$myrow[0],
			htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'),
			$myrow[0]);

	} //END WHILE LIST LOOP
	echo '</table><br />';
} //end of ifs and buts!

if (isset($SelectedTerms)) {
	echo '<div class="centre">
			<a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('Show all Payment Terms Definitions') . '</a>
		</div>';
}

if (!isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($SelectedTerms)) {
		//editing an existing payment terms

		$sql = "SELECT termsindicator,
						terms,
						daysbeforedue,
						dayinfollowingmonth
					FROM paymentterms
					WHERE termsindicator='" . $SelectedTerms . "'";

		$result = DB_query($sql, $db);
		$myrow = DB_fetch_array($result);

		$_POST['TermsIndicator'] = $myrow['termsindicator'];
		$_POST['Terms']  = $myrow['terms'];
		$DaysBeforeDue  = $myrow['daysbeforedue'];
		$DayInFollowingMonth  = $myrow['dayinfollowingmonth'];

		echo '<input type="hidden" name="SelectedTerms" value="' . $SelectedTerms . '" />';
		echo '<input type="hidden" name="TermsIndicator" value="' . $_POST['TermsIndicator'] . '" />';
		echo '<br />
			<table class="selection">';
		echo '<tr>
				<th colspan="6"><h3>' . _('Update Payment Terms.') . '</h3></th>
			</tr>';
		echo '<tr>
				<td>' . _('Term Code') . ':</td>
				<td>' . $_POST['TermsIndicator'] . '</td></tr>';

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

		echo '<table class="selection">';
		echo '<tr>
				<th colspan="6"><h3>' . _('New Payment Terms.') . '</h3></th>
			</tr>';
		echo '<tr>
				<td>' . _('Term Code') . ':</td>
				<td><input type="text" name="TermsIndicator"' . (in_array('TermsIndicator',$Errors) ? 'class="inputerror"' : '' ) .' autofocus="autofocus" required="required" pattern="[0-9a-ZA-Z_]*" title="' . _('A 2 character code to identify this payment term. Any alpha-numeric characters can be used') . '" value="' . $_POST['TermsIndicator'] . '" size="3" maxlength="2" /></td>
			</tr>';
	}

	echo '<tr>
			<td>' .  _('Terms Description'). ':</td>
			<td><input type="text"' . (in_array('Terms',$Errors) ? 'class="inputerror"' : '' ) .' name="Terms" ' . (isset($SelectedTerms)? 'autofocus="autofocus"': '') . ' required="required" value="'.$_POST['Terms']. '" title="' . _('A description of the payment terms is required') . '" size="35" maxlength="40" /></td>
		</tr>
		<tr>
			<td>' . _('Due After A Given No. Of Days').':</td>
			<td><input type="checkbox" name="DaysOrFoll" ';
    if (isset($DayInFollowingMonth) AND !$DayInFollowingMonth) {
         echo 'checked';
    }
    echo '  /></td>
		</tr>
		<tr><td>' . _('Days (Or Day In Following Month)').':</td>
			<td><input type="text" ' . (in_array('DayNumber',$Errors) ? 'class="inputerror"' : '' ) .' name="DayNumber" required="required" class="integer"  size="4" maxlength="3" value="';
	if ($DaysBeforeDue !=0) {
		echo locale_number_format($DaysBeforeDue,0);
	} else {
		if (isset($DayInFollowingMonth)) {
			echo locale_number_format($DayInFollowingMonth,0);
		}
	}
	echo '" /></td>
		</tr>
		</table>
		<br />
		<div class="centre">
			<input type="submit" name="submit" value="'._('Enter Information').'" />
		</div>';
    echo '</div>
          </form>';
} //end if record deleted no point displaying form to add record

include('includes/footer.inc');
?>
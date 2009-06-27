<?php
/* $Revision: 1.17 $ */

$PageSecurity = 10;

include('includes/session.inc');

$title = _('Payment Terms Maintenance');

include('includes/header.inc');


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

	if (strlen($_POST['TermsIndicator']) < 1) {
		$InputError = 1;
		prnMsg(_('The payment terms name must exist'),'error');
		$Errors[$i] = 'TermsIndicator';
		$i++;
	}
	if (strlen($_POST['TermsIndicator']) > 2) {
		$InputError = 1;
		prnMsg(_('The payment terms name must be two characters or less long'),'error');
		$Errors[$i] = 'TermsIndicator';
		$i++;
	}
	if (empty($_POST['DayNumber']) OR !is_numeric($_POST['DayNumber']) OR $_POST['DayNumber'] <= 0){
		$InputError = 1;
		prnMsg( _('The number of days or the day in the following month must be numeric') ,'error');
		$Errors[$i] = 'DayNumber';
		$i++;
	}
	if (empty($_POST['Terms']) OR strlen($_POST['Terms']) > 40) {
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

		if (isset($_POST['DaysOrFoll']) and $_POST['DaysOrFoll']=='on') {
			$sql = "UPDATE paymentterms SET
					terms='" . $_POST['Terms'] . "',
					dayinfollowingmonth=0,
					daysbeforedue=" . $_POST['DayNumber'] . "
				WHERE termsindicator = '" . $SelectedTerms . "'";
		} else {
			$sql = "UPDATE paymentterms SET
					terms='" . $_POST['Terms'] . "',
					dayinfollowingmonth=" . $_POST['DayNumber'] . ",
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
							" . $_POST['DayNumber'] . ",
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
							" . $_POST['DayNumber'] . "
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

	$sql= "SELECT COUNT(*) FROM debtorsmaster WHERE debtorsmaster.paymentterms = '$SelectedTerms'";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0] > 0) {
		prnMsg( _('Cannot delete this payment term because customer accounts have been created referring to this term'),'warn');
		echo '<br> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('customer accounts that refer to this payment term');
	} else {
		$sql= "SELECT COUNT(*) FROM suppliers WHERE suppliers.paymentterms = '$SelectedTerms'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ($myrow[0] > 0) {
			prnMsg( _('Cannot delete this payment term because supplier accounts have been created referring to this term'),'warn');
			echo '<br> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('supplier accounts that refer to this payment term');
		} else {
			//only delete if used in neither customer or supplier accounts

			$sql="DELETE FROM paymentterms WHERE termsindicator='$SelectedTerms'";
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

	$sql = 'SELECT termsindicator, terms, daysbeforedue, dayinfollowingmonth FROM paymentterms';
	$result = DB_query($sql, $db);

	echo '<table border=1>';
	echo '<tr><th>' . _('Term Code') . '</th>
		<th>' . _('Description') . '</th>
		<th>' . _('Following Month On') . '</th>
		<th>' . _('Due After (Days)') . '</th>
		</tr>';

	while ($myrow=DB_fetch_row($result)) {

		if ($myrow[3]==0) {
			$FollMthText = _('N/A');
		} else {
			$FollMthText = $myrow[3] . _('th');
		}

		if ($myrow[2]==0) {
			$DueAfterText = _('N/A');
		} else {
			$DueAfterText = $myrow[2] . ' ' . _('days');
		}

	printf("<tr><td>%s</td>
	        <td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td><a href=\"%s&SelectedTerms=%s\">" . _('Edit') . "</a></td>
		<td><a href=\"%s&SelectedTerms=%s&delete=1\">" . _('Delete') . "</a></td>
		</tr>",
		$myrow[0],
		$myrow[1],
		$FollMthText,
		$DueAfterText,
		$_SERVER['PHP_SELF'] . '?' . SID,
		$myrow[0],
		$_SERVER['PHP_SELF']. '?' . SID,
		$myrow[0]);

	} //END WHILE LIST LOOP
	echo '</table><p>';
} //end of ifs and buts!

if (isset($SelectedTerms)) {
	echo '<div class="centre"><a href="' . $_SERVER['PHP_SELF'] . '?' . SID  .'">' . _('Show all Payment Terms Definitions') . '</a></div>';
}

if (!isset($_GET['delete'])) {

	echo '<form method="post" action=' . $_SERVER['PHP_SELF'] . '?' . SID . '>';

	if (isset($SelectedTerms)) {
		//editing an existing payment terms

		$sql = "SELECT termsindicator,
				terms,
				daysbeforedue,
				dayinfollowingmonth
			FROM paymentterms
			WHERE termsindicator='$SelectedTerms'";

		$result = DB_query($sql, $db);
		$myrow = DB_fetch_array($result);

		$_POST['TermsIndicator'] = $myrow['termsindicator'];
		$_POST['Terms']  = $myrow['terms'];
		$DaysBeforeDue  = $myrow['daysbeforedue'];
		$DayInFollowingMonth  = $myrow['dayinfollowingmonth'];

		echo '<input type=hidden name="SelectedTerms" VALUE="' . $SelectedTerms . '">';
		echo '<input type=hidden name="TermsIndicator" VALUE="' . $_POST['TermsIndicator'] . '">';
		echo '<table><tr><td>' . _('Term Code') . ':</td><td>';
		echo $_POST['TermsIndicator'] . '</td></tr>';

	} else { //end of if $SelectedTerms only do the else when a new record is being entered

		if (!isset($_POST['TermsIndicator'])) $_POST['TermsIndicator']='';
		if (!isset($DaysBeforeDue)) $DaysBeforeDue=0;
		//if (!isset($DayInFollowingMonth)) $DayInFollowingMonth=0;
		unset($DayInFollowingMonth); // Rather unset for a new record
		if (!isset($_POST['Terms'])) $_POST['Terms']='';

		echo '<table><tr><td>' . _('Term Code') . ':</td><td><input type="Text" name="TermsIndicator"
		 ' . (in_array('TermsIndicator',$Errors) ? 'class="inputerror"' : '' ) .' value="' . $_POST['TermsIndicator'] .
			'" size=3 maxlength=2></td></tr>';
	}

	echo '<tr><td>'. _('Terms Description'). ':</td>
	<td>
	<input type="text"' . (in_array('Terms',$Errors) ? 'class="inputerror"' : '' ) .' name="Terms" VALUE="'.$_POST['Terms']. '" size=35 maxlength=40>
	</td></tr>
	<tr><td>'._('Due After A Given No. Of Days').':</td>
	<td><input type="checkbox" name="DaysOrFoll"';
	if ( isset($DayInFollowingMonth) && !$DayInFollowingMonth) { echo "checked"; }
	echo ' ></td></tr><tr><td>'._('Days (Or Day In Following Month)').':</td><td>
		<input type="Text"' . (in_array('DayNumber',$Errors) ? 'class="inputerror"' : '' ) .' name="DayNumber" class=number  size=4 maxlength=3 VALUE=';
	if ($DaysBeforeDue !=0) {
			echo $DaysBeforeDue;
			} else {
			if (isset($DayInFollowingMonth)) {echo $DayInFollowingMonth;}
			}
	echo '></td></tr></table><div class="centre"><input type="Submit" name="submit" value="'._('Enter Information').'"></form></div>';
} //end if record deleted no point displaying form to add record

include('includes/footer.inc');
?>
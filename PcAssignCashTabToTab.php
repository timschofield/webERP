<?php

include('includes/session.php');
$Title = _('Assignment of Cash From Tab To Tab');
/* webERP manual links before header.php */
$ViewTopic= 'PettyCash';
$BookMark = 'CashAssignment';
include('includes/header.php');

if (isset($_POST['SelectedTabs'])){
	$SelectedTabs = mb_strtoupper($_POST['SelectedTabs']);
} elseif (isset($_GET['SelectedTabs'])){
	$SelectedTabs = mb_strtoupper($_GET['SelectedTabs']);
}

if (isset($_POST['Days'])){
	$Days = $_POST['Days'];
} elseif (isset($_GET['Days'])){
	$Days = $_GET['Days'];
}

if (isset($_POST['Cancel'])) {
	unset($SelectedTabs);
	unset($Days);
	unset($_POST['Amount']);
	unset($_POST['Notes']);
	unset($_POST['Receipt']);
}

if (isset($_POST['Process'])) {
	if ($SelectedTabs=='') {
		prnMsg(_('You Must First Select a Petty Cash Tab To Assign Cash'),'error');
		unset($SelectedTabs);
	}
	if ($SelectedTabs == mb_strtoupper($_POST['SelectedTabsTo'])) {
		prnMsg(_('The Tab selected From should not be the same as the selected To'),'error');
		unset($SelectedTabs);
		unset($_POST['SelectedTabsTo']);
		unset($_POST['Process']);
	}
	//to ensure currency is the same
	$CurrSQL = "SELECT currency
				FROM pctabs
				WHERE tabcode IN ('" . $SelectedTabs . "','" . $_POST['SelectedTabsTo'] . "')";
	$CurrResult = DB_query($CurrSQL);
	if (DB_num_rows($CurrResult)>0) {
		$Currency = '';
		while ($CurrRow = DB_fetch_array($CurrResult)) {
			if ($Currency === '') {
				$Currency = $CurrRow['currency'];
			} elseif ($Currency != $CurrRow['currency']) {
				prnMsg (_('The currency transferred from shoud be the same with the transferred to'),'error');
				unset($SelectedTabs);
				unset($_POST['SelectedTabsTo']);
				unset($_POST['Process']);
			}
		}
	}

}

if (isset($_POST['Go'])) {
	$InputError = 0;
	if ($Days<=0) {
		$InputError = 1;
		prnMsg(_('The number of days must be a positive number'),'error');
		$Days=30;
	}
}

if (isset($_POST['submit'])) {
	//initialise no input errors assumed initially before we test
	$InputError = 0;

	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/money_add.png" title="' .
		_('Search') . '" alt="" />' . ' ' . $Title. '</p>';

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	$i=1;

	if ($_POST['Amount']==0) {
		$InputError = 1;
		prnMsg('<br />' . _('The Amount must be input'),'error');
	}

	$sqlLimit = "SELECT tablimit,tabcode
				FROM pctabs
				WHERE tabcode IN ('" . $SelectedTabs . "','" . $_POST['SelectedTabsTo'] . "')";

	$ResultLimit = DB_query($sqlLimit,$db);
	while ($LimitRow=DB_fetch_array($ResultLimit)){
		if ($LimitRow['tabcode'] == $SelectedTabs) {
			if (($_POST['CurrentAmount']+$_POST['Amount'])>$LimitRow['tablimit']){
				$InputError = 1;
				prnMsg(_('The balance after this assignment would be greater than the specified limit for this PC tab') . ' ' . $LimitRow[1],'error');
			}
		}  elseif ($_POST['SelectedTabsToAmt'] - $_POST['Amount']>$LimitRow['tablimit']) {
				$InputError = 1;
				prnMsg(_('The balance after this assignment would be greater than the specified limit for this PC tab') . ' ' . $LimitRow[1],'error');
		}
	}

	if ($InputError !=1 ) {
		// Add these 2 new record on submit
		$sql = "INSERT INTO pcashdetails
					(counterindex,
					tabcode,
					date,
					codeexpense,
					amount,
					authorized,
					posted,
					notes,
					receipt)
			VALUES (NULL,
					'" . $_POST['SelectedTabs'] . "',
					'".FormatDateForSQL($_POST['Date'])."',
					'ASSIGNCASH',
					'" . filter_number_format($_POST['Amount']) . "',
					'0000-00-00',
					'0',
					'" . $_POST['Notes'] . "',
					'" . $_POST['Receipt'] . "'
				),
				(NULL,
					'" . $_POST['SelectedTabsTo'] . "',
					'" . FormatDateForSQL($_POST['Date']) . "',
					'ASSIGNCASH',
					'" . filter_number_format(-$_POST['Amount']) . "',
					'0000-00-00',
					'0',
					'" . $_POST['Notes'] . "',
					'" . $_POST['Receipt'] . "')";
		$msg = _('Assignment of cash from PC Tab ') . ' ' . $_POST['SelectedTabs'] .  ' ' . _('to') . $_POST['SelectedTabsTo'] . ' ' . _('has been created');
	}

	if ( $InputError !=1) {
		//run the SQL from either of the above possibilites
		$result = DB_query($sql,$db);
		prnMsg($msg,'success');
		unset($_POST['SelectedExpense']);
		unset($_POST['Amount']);
		unset($_POST['Notes']);
		unset($_POST['Receipt']);
		unset($_POST['SelectedTabs']);
		unset($_POST['Date']);
	}

}

if (!isset($SelectedTabs)){

	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/money_add.png" title="' .
		_('Search') . '" alt="" />' . ' ' . $Title. '</p>';

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	$SQL = "SELECT tabcode
			FROM pctabs
			WHERE assigner='" . $_SESSION['UserID'] . "'
			ORDER BY tabcode";

	$result = DB_query($SQL,$db);

    echo '<br /><table class="selection">'; //Main table

    echo '<tr><td>' . _('Petty Cash Tab To Assign Cash From') . ':</td>
            <td><select name="SelectedTabs">';
	while ($myrow = DB_fetch_array($result)) {
		if (isset($_POST['SelectTabs']) and $myrow['tabcode']==$_POST['SelectTabs']) {
			echo '<option selected="selected" value="';
		} else {
			echo '<option value="';
		}
		echo $myrow['tabcode'] . '">' . $myrow['tabcode'] . '</option>';
	}

	echo '</select></td></tr>';
  echo '<tr><td>' . _('Petty Cash Tab To Assign Cash To') . ':</td>
	  <td><select name="SelectedTabsTo">';
	DB_data_seek($result,0);
	while ($myrow = DB_fetch_array($result)) {
		if (isset($_POST['SelectTabsTo']) AND $myrow['tabcode'] == $_POST['SelectTabs']) {
			echo '<option selected="selected" value="';
		} else {
			echo '<option value="';
		}
		echo $myrow['tabcode'] . '">' . $myrow['tabcode'] . '</option>';
	}
	echo '</select></td></tr>';
   	echo '</table>'; // close main table
    DB_free_result($result);

	echo '<br />
		<div class="centre">
			<input type="submit" name="Process" value="' . _('Accept') . '" />
			<input type="submit" name="Cancel" value="' . _('Cancel') . '" />
		</div>';
	echo '</div>
          </form>';
}

//end of ifs and buts!
if (isset($_POST['Process']) OR isset($SelectedTabs)) {

	if (!isset($_POST['submit'])) {
		echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/money_add.png" title="' .
			_('Search') . '" alt="" />' . ' ' . $Title. '</p>';
	}
	echo '<br /><div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('Select another tab') . '</a></div>';



	if (! isset($_GET['edit']) OR isset ($_POST['GO'])){

		if (isset($_POST['Cancel'])) {
			unset($_POST['Amount']);
			unset($_POST['Date']);
			unset($_POST['Notes']);
			unset($_POST['Receipt']);
		}

		if(!isset ($Days)){
			$Days=30;
		 }

		/* Retrieve decimal places to display */
		$SqlDecimalPlaces="SELECT decimalplaces
					FROM currencies,pctabs
					WHERE currencies.currabrev = pctabs.currency
						AND tabcode='" . $SelectedTabs . "'";
		$result = DB_query($SqlDecimalPlaces,$db);
		$myrow=DB_fetch_array($result);
		$CurrDecimalPlaces = $myrow['decimalplaces'];

		$sql = "SELECT * FROM pcashdetails
				WHERE tabcode='" . $SelectedTabs . "'
				AND date >=DATE_SUB(CURDATE(), INTERVAL " . $Days . " DAY)
				ORDER BY date, counterindex ASC";
		$result = DB_query($sql,$db);

		echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">
			<div>
				<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
				<table class="selection">
				<tr>
					<th colspan="8">' . _('Detail Of PC Tab Movements For Last') .':
						<input type="hidden" name="SelectedTabs" value="' . $SelectedTabs . '" />
						<input type="text" class="number" name="Days" value="' . $Days  . '" maxlength="3" size="4" /> ' . _('Days') . '
						<input type="submit" name="Go" value="' . _('Go') . '" /></th>
				</tr>
				<tr>
					<th>' . _('Date') . '</th>
					<th>' . _('Expense Code') . '</th>
					<th>' . _('Amount') . '</th>
					<th>' . _('Authorised') . '</th>
					<th>' . _('Notes') . '</th>
					<th>' . _('Receipt') . '</th>
				</tr>';

		$k=0; //row colour counter

	while ($myrow = DB_fetch_array($result)) {
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}

		$sqldes="SELECT description
					FROM pcexpenses
					WHERE codeexpense='". $myrow['3'] . "'";

		$ResultDes = DB_query($sqldes,$db);
		$Description=DB_fetch_array($ResultDes);

		if (!isset($Description['0'])){
			$Description['0']='ASSIGNCASH';
		}

		if (($myrow['authorized'] == '0000-00-00') and ($Description['0'] == 'ASSIGNCASH')){
			// only cash assignations NOT authorized can be modified or deleted
			echo '<td>' . ConvertSQLDate($myrow['date']) . '</td>
				<td>' . $Description['0'] . '</td>
				<td class="number">' . locale_number_format($myrow['amount'],$CurrDecimalPlaces) . '</td>
				<td>' . ConvertSQLDate($myrow['authorized']) . '</td>
				<td>' . $myrow['notes'] . '</td>
				<td>' . $myrow['receipt'] . '</td>
				</tr>';
		}else{
			echo '<td>' . ConvertSQLDate($myrow['date']) . '</td>
				<td>' . $Description['0'] . '</td>
				<td class="number">' . locale_number_format($myrow['amount'],$CurrDecimalPlaces) . '</td>
				<td>' . ConvertSQLDate($myrow['authorized']) . '</td>
				<td>' . $myrow['notes'] . '</td>
				<td>' . $myrow['receipt'] . '</td>
				</tr>';
		}
	}
		//END WHILE LIST LOOP

		$sqlamount="SELECT sum(amount) as amt,
					tabcode
					FROM pcashdetails
					WHERE tabcode IN ('".$SelectedTabs."','" . $_POST['SelectedTabsTo'] . "')
					GROUP BY tabcode";

		$ResultAmount = DB_query($sqlamount,$db);
		if (DB_num_rows($ResultAmount)>0) {
			while ($AmountRow=DB_fetch_array($ResultAmount)) {
				if (is_null($AmountRow['amt'])) {
					$AmountRow['amt'] = 0;
				}
				if ($AmountRow['tabcode'] == $SelectedTabs) {
					$SelectedTab = array($AmountRow['amt'],$SelectedTabs);
				} else {
					$SelectedTabsTo = array($AmountRow['amt'],$_POST['SelectedTabsTo']);
				}
			}
		}
		if (!isset($SelectedTab)) {
			$SelectedTab = array(0,$SelectedTabs);
			$SelectedTabsTo = array(0,$_POST['SelectedTabsTo']);
		}



		echo '<tr>
				<td colspan="2" style="text-align:right"><b>' . _('Current balance') . ':</b></td>
				<td>' . locale_number_format($SelectedTab['0'],$CurrDecimalPlaces) . '</td></tr>
				<input type="hidden" name="CurrentAmount" value="' . $SelectedTab[0] . '" />
				<input type="hidden" name="SelectedTabs" value="' . $SelectedTab[1] . '" />
				<input type="hidden" name="SelectedTabsTo" value="' . $SelectedTabsTo[1] . '" />
				<input type="hidden" name="SelectedTabsToAmt" value="' . $SelectedTabsTo[0] . '" />';


		echo '</table>';
        echo '</div>
              </form>';
	}



		echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .'">
			<div>
				<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

/* Ricard: needs revision of this date initialization */
		if (!isset($_POST['Date'])) {
			$_POST['Date']=Date($_SESSION['DefaultDateFormat']);
		}

        echo '<br />
				<table class="selection">'; //Main table
            echo '<tr>
					<th colspan="2"><h3>' . _('New Cash Assignment') . '</h3></th>
				</tr>';
		echo '<tr>
				<td>' . _('Cash Assignation Date') . ':</td>
				<td><input type="text" class="date" alt="'.$_SESSION['DefaultDateFormat'].'" name="Date" required="required" autofocus="autofocus" size="10" maxlength="10" value="' . $_POST['Date'] . '" /></td>
			</tr>';


		if (!isset($_POST['Amount'])) {
			$_POST['Amount']=0;
		}

		echo '<tr>
				<td>' . _('Amount') . ':</td>
				<td><input type="text" class="number" name="Amount" size="12" maxlength="11" value="' . locale_number_format($_POST['Amount'],$CurrDecimalPlaces) . '" /></td>
			</tr>';

		if (!isset($_POST['Notes'])) {
			$_POST['Notes']='';
		}

		echo '<tr>
				<td>' . _('Notes') . ':</td>
				<td><input type="text" name="Notes" size="50" maxlength="49" value="' . $_POST['Notes'] . '" /></td>
			</tr>';

		if (!isset($_POST['Receipt'])) {
			$_POST['Receipt']='';
		}

		echo '<tr>
				<td>' . _('Receipt') . ':</td>
				<td><input type="text" name="Receipt" size="50" maxlength="49" value="' . $_POST['Receipt'] . '" /></td>
			</tr>
			</table>
			<input type="hidden" name="CurrentAmount" value="' . $SelectedTab['0']. '" />
			<input type="hidden" name="SelectedTabs" value="' . $SelectedTabs . '" />
			<input type="hidden" name="Days" value="' .$Days. '" />
			<input type="hidden" name="SelectedTabsTo" value="' . $SelectedTabsTo[1] . '" />
			<input type="hidden" name="SelectedTabsToAmt" value="' . $SelectedTabsTo[0] . '" />
			<br />
			<div class="centre">
				<input type="submit" name="submit" value="' . _('Accept') . '" />
				<input type="submit" name="Cancel" value="' . _('Cancel') . '" /></div>
			</div>
		</form>';

}

include('includes/footer.php');
?>

<?php
/* $Id: PcAuthorizeExpenses.php 6951 2014-10-29 06:29:22Z daintree $*/

include('includes/session.inc');
$Title = _('Authorisation of Petty Cash Expenses');
/* webERP manual links before header.inc */
$ViewTopic= 'PettyCash';
$BookMark = 'AuthorizeExpense';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

if (isset($_POST['SelectedTabs'])){
	$SelectedTabs = mb_strtoupper($_POST['SelectedTabs']);
} elseif (isset($_GET['SelectedTabs'])){
	$SelectedTabs = mb_strtoupper($_GET['SelectedTabs']);
}

if (isset($_POST['SelectedIndex'])){
	$SelectedIndex = $_POST['SelectedIndex'];
} elseif (isset($_GET['SelectedIndex'])){
	$SelectedIndex = $_GET['SelectedIndex'];
}

if (isset($_POST['Days'])){
	$Days = filter_number_format($_POST['Days']);
} elseif (isset($_GET['Days'])){
	$Days = filter_number_format($_GET['Days']);
}

if (isset($_POST['Process'])) {
	if ($SelectedTabs=='') {
		prnMsg(_('You Must First Select a Petty Cash Tab To Authorise'),'error');
		unset($SelectedTabs);
	}
}

if (isset($_POST['Go'])) {
	if ($Days<=0) {
		prnMsg(_('The number of days must be a positive number'),'error');
		$Days=30;
	}
}

if (isset($SelectedTabs)) {
	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/magnifier.png" title="' . _('Petty Cash') .
		'" alt="" />' . _('Authorisation Of Petty Cash Expenses') . ' '.$SelectedTabs . '</p>';
} else {
	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/magnifier.png" title="' . _('Petty Cash') .
		'" alt="" />' . _('Authorisation Of Petty Cash Expenses') . '</p>';
}
if (isset($_POST['Submit']) or isset($_POST['update']) OR isset($SelectedTabs) OR isset ($_POST['GO'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if(!isset ($Days)){
		$Days=30;
	}
	echo '<input type="hidden" name="SelectedTabs" value="' . $SelectedTabs . '" />
			<br />
			<table class="selection">
			<tr>
				<th colspan="7">' . _('Detail Of Movement For Last ') .':<input type="text" class="intger" name="Days" value="' . $Days . '" maxlength="3" size="4" />' . _('Days');
	echo '<input type="submit" name="Go" value="' . _('Go') . '" /></th>
		</tr>';

	$sql = "SELECT pcashdetails.counterindex,
				pcashdetails.tabcode,
				pcashdetails.date,
				pcashdetails.codeexpense,
				pcashdetails.amount,
				pcashdetails.authorized,
				pcashdetails.posted,
				pcashdetails.notes,
				pcashdetails.receipt,
				pctabs.glaccountassignment,
				pctabs.glaccountpcash,
				pctabs.usercode,
				pctabs.currency,
				currencies.rate,
				currencies.decimalplaces
			FROM pcashdetails, pctabs, currencies
			WHERE pcashdetails.tabcode = pctabs.tabcode
				AND pctabs.currency = currencies.currabrev
				AND pcashdetails.tabcode = '" . $SelectedTabs . "'
				AND pcashdetails.date >= DATE_SUB(CURDATE(), INTERVAL '".$Days."' DAY)
			ORDER BY pcashdetails.date, pcashdetails.counterindex ASC";

	$result = DB_query($sql);

	echo '<tr>
		<th>' . _('Date') . '</th>
		<th>' . _('Expense Code') . '</th>
		<th>' . _('Amount') . '</th>
		<th>' . _('Posted') . '</th>
		<th>' . _('Notes') . '</th>
		<th>' . _('Receipt') . '</th>
		<th>' . _('Authorised') . '</th>
	</tr>';

	$k=0; //row colour counter

	while ($myrow=DB_fetch_array($result))	{
         $CurrDecimalPlaces = $myrow['decimalplaces'];
		//update database if update pressed
		if (isset($_POST['Submit'])
			AND $_POST['Submit']==_('Update')
			AND isset($_POST[$myrow['counterindex']])){

			$PeriodNo = GetPeriod(ConvertSQLDate($myrow['date']), $db);

			if ($myrow['rate'] == 1){ // functional currency
				$Amount = $myrow['amount'];
			}else{ // other currencies
				$Amount = $myrow['amount']/$myrow['rate'];
			}

			if ($myrow['codeexpense'] == 'ASSIGNCASH'){
				$type = 2;
				$AccountFrom = $myrow['glaccountassignment'];
				$AccountTo = $myrow['glaccountpcash'];
                $TagTo = 0;
			}else{
				$type = 1;
				$Amount = -$Amount;
				$AccountFrom = $myrow['glaccountpcash'];
				$SQLAccExp = "SELECT glaccount,
									tag
								FROM pcexpenses
								WHERE codeexpense = '".$myrow['codeexpense']."'";
				$ResultAccExp = DB_query($SQLAccExp);
				$myrowAccExp = DB_fetch_array($ResultAccExp);
				$AccountTo = $myrowAccExp['glaccount'];
				$TagTo = $myrowAccExp['tag'];
			}

			//get typeno
			$typeno = GetNextTransNo($type,$db);

			//build narrative
			$Narrative= _('PettyCash') . ' - '. $myrow['tabcode'] . ' - ' . $myrow['codeexpense'] . ' - ' . DB_escape_string($myrow['notes']) . ' - ' . $myrow['receipt'];
			//insert to gltrans
			DB_Txn_Begin();

			$sqlFrom="INSERT INTO `gltrans` (`counterindex`,
											`type`,
											`typeno`,
											`chequeno`,
											`trandate`,
											`periodno`,
											`account`,
											`narrative`,
											`amount`,
											`posted`,
											`jobref`,
											`tag`)
									VALUES (NULL,
											'".$type."',
											'".$typeno."',
											0,
											'".$myrow['date']."',
											'".$PeriodNo."',
											'".$AccountFrom."',
											'". $Narrative ."',
											'".-$Amount."',
											0,
											'',
											'" . $TagTo ."')";

			$ResultFrom = DB_Query($sqlFrom,'', '', true);

			$sqlTo="INSERT INTO `gltrans` (`counterindex`,
										`type`,
										`typeno`,
										`chequeno`,
										`trandate`,
										`periodno`,
										`account`,
										`narrative`,
										`amount`,
										`posted`,
										`jobref`,
										`tag`)
								VALUES (NULL,
										'".$type."',
										'".$typeno."',
										0,
										'".$myrow['date']."',
										'".$PeriodNo."',
										'".$AccountTo."',
										'" . $Narrative . "',
										'".$Amount."',
										0,
										'',
										'" . $TagTo ."')";

			$ResultTo = DB_query($sqlTo,'', '', true);

			if ($myrow['codeexpense'] == 'ASSIGNCASH'){
			// if it's a cash assignation we need to updated banktrans table as well.
				$ReceiptTransNo = GetNextTransNo( 2, $db);
				$SQLBank= "INSERT INTO banktrans (transno,
												type,
												bankact,
												ref,
												exrate,
												functionalexrate,
												transdate,
												banktranstype,
												amount,
												currcode)
										VALUES ('". $ReceiptTransNo . "',
											1,
											'" . $AccountFrom . "',
											'" . $Narrative . "',
											1,
											'" . $myrow['rate'] . "',
											'" . $myrow['date'] . "',
											'Cash',
											'" . -$myrow['amount'] . "',
											'" . $myrow['currency'] . "'
										)";
				$ErrMsg = _('Cannot insert a bank transaction because');
				$DbgMsg =  _('Cannot insert a bank transaction with the SQL');
				$resultBank = DB_query($SQLBank,$ErrMsg,$DbgMsg,true);

			}

			$sql = "UPDATE pcashdetails
					SET authorized = '".Date('Y-m-d')."',
					posted = 1
					WHERE counterindex = '".$myrow['counterindex']."'";
			$resultupdate = DB_query($sql, '', '', true);
			DB_Txn_Commit();
		}

		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
		if ($myrow['posted']==0) {
			$Posted=_('No');
		} else {
			$Posted=_('Yes');
		}
		echo'<td>' . ConvertSQLDate($myrow['date']) . '</td>
			<td>' . $myrow['codeexpense'] . '</td>
			<td class="number">' . locale_number_format($myrow['amount'],$CurrDecimalPlaces) . '</td>
			<td>' . $Posted . '</td>
			<td>'  .$myrow['notes'] . '</td>
			<td>' . $myrow['receipt'] . '</td>';

		if (isset($_POST[$myrow['counterindex']])){
			echo'<td>' . ConvertSQLDate(Date('Y-m-d'));
		}else{
			//compare against raw SQL format date, then convert for display.
			if(($myrow['authorized']!='0000-00-00')){
				echo'<td>' . ConvertSQLDate($myrow['authorized']);
			}else{
				echo '<td align="right"><input type="checkbox" name="'.$myrow['counterindex'].'" />';
			}
		}

		echo '<input type="hidden" name="SelectedIndex" value="' . $myrow['counterindex']. '" />';
		echo '</td></tr>';


	} //end of looping

	$sqlamount="SELECT sum(amount)
			FROM pcashdetails
			WHERE tabcode='".$SelectedTabs."'";

	$ResultAmount = DB_query($sqlamount);
	$Amount=DB_fetch_array($ResultAmount);

	if (!isset($Amount['0'])) {
		$Amount['0']=0;
	}

	echo '<tr><td colspan="2" class="number">' . _('Current balance') . ':</td>
				<td class="number">' . locale_number_format($Amount['0'],$CurrDecimalPlaces) . '</td></tr>';

	// Do the postings
	include ('includes/GLPostings.inc');
	echo '</table>
			<br />
			<div class="centre">
				<input type="submit" name="Submit" value="' . _('Update') . '" /></div>
			</div>
		</form>';


} else { /*The option to submit was not hit so display form */


echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .'">
	<div>
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<br />
		<table class="selection">'; //Main table

	$SQL = "SELECT tabcode
		FROM pctabs
		WHERE authorizer='" . $_SESSION['UserID'] . "'";

	$result = DB_query($SQL);

echo '<tr>
		<td>' . _('Authorise expenses to Petty Cash Tab') . ':</td>
		<td><select name="SelectedTabs" required="required" autofocus="autofocus" >';

	while ($myrow = DB_fetch_array($result)) {
		if (isset($_POST['SelectTabs']) and $myrow['tabcode']==$_POST['SelectTabs']) {
			echo '<option selected="selected" value="';
		} else {
			echo '<option value="';
		}
		echo $myrow['tabcode'] . '">' . $myrow['tabcode'] . '</option>';

	} //end while loop get type of tab

	echo '</select></td>
		</tr>
		</table>'; // close main table
    DB_free_result($result);

	echo '<br />
		<div class="centre">
			<input type="submit" name="Process" value="' . _('Accept') . '" />
			<input type="submit" name="Cancel" value="' . _('Cancel') . '" />
		</div>
		</div>
		</form>';
} /*end of else not submit */
include('includes/footer.inc');
?>

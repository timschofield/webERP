<?php

/* $Revision: 1.1 $ */

include('includes/DefineJournalClass.php');

$PageSecurity = 10;
include('includes/session.inc');
$title = _('Depreciation Journal Entry');

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

if (isset($_GET['NewJournal']) and $_GET['NewJournal'] == 'Yes' AND isset($_SESSION['JournalDetail'])){
	unset($_SESSION['JournalDetail']->GLEntries);
	unset($_SESSION['JournalDetail']);
}

if (!isset($_SESSION['JournalDetail']) or isset($_POST['update'])){
	$_SESSION['JournalDetail'] = new Journal;

	/* Make an array of the defined bank accounts - better to make it now than do it each time a line is added
	Journals cannot be entered against bank accounts GL postings involving bank accounts must be done using
	a receipt or a payment transaction to ensure a bank trans is available for matching off vs statements */

	$SQL = 'SELECT accountcode FROM bankaccounts';
	$result = DB_query($SQL,$db);
	$i=0;
	while ($Act = DB_fetch_row($result)){
		$_SESSION['JournalDetail']->BankAccounts[$i]= $Act[0];
		$i++;
	}

	/* Get list of assets for journal */
	$sql='SELECT assetmanager.*,
			stockcategory.wipact as bsdepn,
			stockcategory.adjglact as pldepn,
			fixedassetlocations.locationdescription
		FROM assetmanager
		LEFT JOIN stockmaster
			ON assetmanager.stockid=stockmaster.stockid
		LEFT JOIN stockcategory
			ON stockmaster.categoryid=stockcategory.categoryid
		LEFT JOIN fixedassetlocations
			ON fixedassetlocations.locationid=assetmanager.location';
	$result=DB_query($sql, $db);
	$i=0;
	while ($myrow=DB_fetch_array($result)) {
		$assetarray[$i]=$myrow;
		// Find depreciation type
		$typesql='SELECT stockitemproperties.value
				FROM stockitemproperties
				LEFT JOIN stockcatproperties
					ON stockcatproperties.stkcatpropid=stockitemproperties.stkcatpropid
				WHERE stockitemproperties.stockid="'.$myrow['stockid'].'"
					AND stockcatproperties.label="'._('Depreciation Type').'"';
		$typeresult=DB_query($typesql,$db);
		$typerow=DB_fetch_array($typeresult);
		$assetarray[$i]['DepreciationType']=$typerow['value'];

		// Find the depreciation rate
		$ratesql='SELECT stockitemproperties.value
				FROM stockitemproperties
				LEFT JOIN stockcatproperties
					ON stockcatproperties.stkcatpropid=stockitemproperties.stkcatpropid
				WHERE stockitemproperties.stockid="'.$myrow['stockid'].'"
					AND stockcatproperties.label="'._('Annual Depreciation Percentage').'"';
		$rateresult=DB_query($ratesql, $db);
		$raterow=DB_fetch_array($rateresult);
		$assetarray[$i]['DepreciationRate']=$raterow['value'];


		$bsnamesql='SELECT accountname FROM chartmaster WHERE accountcode="'.$assetarray[$i]['bsdepn'].'"';
		$bsnameresult=DB_query($bsnamesql, $db);
		$bsnamerow=DB_fetch_array($bsnameresult);
		$assetarray[$i]['bsdepnaccount']=$bsnamerow['accountname'];
		$plnamesql='SELECT accountname FROM chartmaster WHERE accountcode="'.$assetarray[$i]['pldepn'].'"';
		$plnameresult=DB_query($plnamesql, $db);
		$plnamerow=DB_fetch_array($plnameresult);
		$assetarray[$i]['pldepnaccount']=$plnamerow['accountname'];

		/* Need a method to correctly enter the journal date,
		 * and for it to be available to the depn calculation - Tim
		 */

		if (isset($_POST['JournalProcessDate'])) {
			$_SESSION['JournalDetail']->JnlDate=$_POST['JournalProcessDate'];
		}
		if (!Is_Date($_SESSION['JournalDetail']->JnlDate)){
			// Default the date to the last day of the previous month
			$_SESSION['JournalDetail']->JnlDate = Date($_SESSION['DefaultDateFormat'],mktime(0,0,0,date('m'),0,date('Y')));
		}

		$NoOfMonths=DateDiff($_SESSION['JournalDetail']->JnlDate,ConvertSQLDate($assetarray[$i]['datepurchased']), 'm');
		if ($assetarray[$i]['DepreciationType']==_('Straight Line')) {
			$TotalDepnAmount=round(($NoOfMonths/12)*($assetarray[$i]['DepreciationRate']/100)*$assetarray[$i]['cost'],2);
		} else {
			$TotalDepnAmount=round(($NoOfMonths/12)*($assetarray[$i]['DepreciationRate']/100)*
				($assetarray[$i]['cost']-$assetarray[$i]['depn']),2);
		}

		if (($TotalDepnAmount+$assetarray[$i]['disposalvalue'])>$assetarray[$i]['cost']) {
			$TotalDepnAmount=$assetarray[$i]['cost']-$assetarray[$i]['disposalvalue'];
		}
		$assetarray[$i]['narrative']='Depreciation Journal - '.$assetarray[$i]['stockid'].
			' - '.$assetarray[$i]['serialno'].' - '.$assetarray[$i]['locationdescription'];

		$_SESSION['JournalDetail']->Add_To_GLAnalysis(
			-($TotalDepnAmount-$assetarray[$i]['depn']),
			$assetarray[$i]['narrative'],
			$assetarray[$i]['bsdepn'],
			$assetarray[$i]['bsdepnaccount'],
			0,
			$assetarray[$i]['id']);

		$_SESSION['JournalDetail']->Add_To_GLAnalysis(
			$TotalDepnAmount-$assetarray[$i]['depn'],
			$assetarray[$i]['narrative'],
			$assetarray[$i]['pldepn'],
			$assetarray[$i]['pldepnaccount'],
			0,
			$assetarray[$i]['id']);
		$i++;
	}
}

if (isset($_POST['JournalProcessDate'])){
	$_SESSION['JournalDetail']->JnlDate=$_POST['JournalProcessDate'];

	if (!Is_Date($_POST['JournalProcessDate'])){
		prnMsg(_('The date entered was not valid please enter the date to process the journal in the format'). $_SESSION['DefaultDateFormat'],'warn');
		$_POST['CommitBatch']='Do not do it the date is wrong';
	}
}
if (isset($_POST['JournalType'])){
	$_SESSION['JournalDetail']->JournalType = $_POST['JournalType'];
}

if (isset($_POST['CommitBatch']) and $_POST['CommitBatch']==_('Accept and Process Journal')){

 /* once the GL analysis of the journal is entered
  process all the data in the session cookie into the DB
  A GL entry is created for each GL entry
*/

	$PeriodNo = GetPeriod($_SESSION['JournalDetail']->JnlDate,$db);

     /*Start a transaction to do the whole lot inside */
	$result = DB_Txn_Begin($db);

	$TransNo = GetNextTransNo( 0, $db);
	$BSorPL=1; // Balance sheet or P&L indicator

	foreach ($_SESSION['JournalDetail']->GLEntries as $JournalItem) {
		$odd=$BSorPL%2;
		if ($odd==0) {
			$sql='UPDATE assetmanager SET depn=depn+"'.$JournalItem->Amount.'" WHERE id="'.$JournalItem->assetid.'"';
			$ErrMsg = _('Cannot update the asset manager for this amount because');
			$DbgMsg = _('The SQL that failed to update the asset manger record was');
			$result=DB_query($sql, $db,$ErrMsg,$DbgMsg,true);
		}
		$BSorPL++;
	}

	foreach ($_SESSION['JournalDetail']->GLEntries as $JournalItem) {
		$SQL = "INSERT INTO gltrans (type,
						typeno,
						trandate,
						periodno,
						account,
						narrative,
						amount,
						tag) ";
		$SQL= $SQL . "VALUES (0,
					'" . $TransNo . "',
					'" . FormatDateForSQL($_SESSION['JournalDetail']->JnlDate) . "',
					'" . $PeriodNo . "',
					'" . $JournalItem->GLCode . "',
					'" . $JournalItem->Narrative . "',
					'" . $JournalItem->Amount ."',
					'" . $JournalItem->tag."'
					)";
		$ErrMsg = _('Cannot insert a GL entry for the journal line because');
		$DbgMsg = _('The SQL that failed to insert the GL Trans record was');
		$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
	}


	$ErrMsg = _('Cannot commit the changes');
	$result= DB_Txn_Begin($db);

	prnMsg(_('Journal').' ' . $TransNo . ' '._('has been successfully entered'),'success');

	unset($_POST['JournalProcessDate']);
	unset($_POST['JournalType']);
	unset($_SESSION['JournalDetail']->GLEntries);
	unset($_SESSION['JournalDetail']);

	/*Set up a newy in case user wishes to enter another */
	echo "<br><a href='index.php" . '?' . SID . "'>"._('Return to main menu').'</a>';
	/*And post the journal too */
	include ('includes/GLPostings.inc');
	include ('includes/footer.inc');
	exit;

} elseif (isset($_GET['Delete'])){

	/* User hit delete the line from the journal */
	$_SESSION['JournalDetail']->Remove_GLEntry($_GET['Delete']);

} elseif (isset($_POST['Process']) and $_POST['Process']==_('Accept')){ //user hit submit a new GL Analysis line into the journal
	if($_POST['GLCode']!='')
	{
		$extract = explode(' - ',$_POST['GLCode']);
		$_POST['GLCode'] = $extract[0];
	}
	if($_POST['Debit']>0)
	{
		$_POST['GLAmount'] = $_POST['Debit'];
	}
	elseif($_POST['Credit']>0)
	{
		$_POST['GLAmount'] = '-' . $_POST['Credit'];
	}
	if ($_POST['GLManualCode'] != '' AND is_numeric($_POST['GLManualCode'])){
				// If a manual code was entered need to check it exists and isnt a bank account
	$AllowThisPosting = true; //by default
		if ($_SESSION['ProhibitJournalsToControlAccounts'] == 1){
			if ($_SESSION['CompanyRecord']['gllink_debtors'] == '1' AND $_POST['GLManualCode'] == $_SESSION['CompanyRecord']['debtorsact']){
				prnMsg(_('GL Journals involving the debtors control account cannot be entered. The general ledger debtors ledger (AR) integration is enabled so control accounts are automatically maintained by webERP. This setting can be disabled in System Configuration'),'warn');
				$AllowThisPosting = false;
			}
			if ($_SESSION['CompanyRecord']['gllink_creditors'] == '1' AND $_POST['GLManualCode'] == $_SESSION['CompanyRecord']['creditorsact']){
				prnMsg(_('GL Journals involving the creditors control account cannot be entered. The general ledger creditors ledger (AP) integration is enabled so control accounts are automatically maintained by webERP. This setting can be disabled in System Configuration'),'warn');
				$AllowThisPosting = false;
			}
		}
		if (in_array($_POST['GLManualCode'], $_SESSION['JournalDetail']->BankAccounts)) {
			prnMsg(_('GL Journals involving a bank account cannot be entered') . '. ' . _('Bank account general ledger entries must be entered by either a bank account receipt or a bank account payment'),'info');
			$AllowThisPosting = false;
		}

		if ($AllowThisPosting) {
			$SQL = 'SELECT accountname
							FROM chartmaster
							WHERE accountcode=' . $_POST['GLManualCode'];
			$Result=DB_query($SQL,$db);

			if (DB_num_rows($Result)==0){
				prnMsg(_('The manual GL code entered does not exist in the database') . ' - ' . _('so this GL analysis item could not be added'),'warn');
				unset($_POST['GLManualCode']);
			} else {
				$myrow = DB_fetch_array($Result);
				$_SESSION['JournalDetail']->add_to_glanalysis($_POST['GLAmount'], $_POST['GLNarrative'], $_POST['GLManualCode'], $myrow['accountname'], $_POST['tag']);
			}
		}
	} else {
		$AllowThisPosting =true; //by default
		if ($_SESSION['ProhibitJournalsToControlAccounts'] == 1){
			if ($_SESSION['CompanyRecord']['gllink_debtors'] == '1' AND $_POST['GLCode'] == $_SESSION['CompanyRecord']['debtorsact']){
				prnMsg(_('GL Journals involving the debtors control account cannot be entered. The general ledger debtors ledger (AR) integration is enabled so control accounts are automatically maintained by webERP. This setting can be disabled in System Configuration'),'warn');
				$AllowThisPosting = false;
			}
			if ($_SESSION['CompanyRecord']['gllink_creditors'] == '1' AND $_POST['GLCode'] == $_SESSION['CompanyRecord']['creditorsact']){
				prnMsg(_('GL Journals involving the creditors control account cannot be entered. The general ledger creditors ledger (AP) integration is enabled so control accounts are automatically maintained by webERP. This setting can be disabled in System Configuration'),'warn');
				$AllowThisPosting = false;
			}
		}

		if (in_array($_POST['GLCode'], $_SESSION['JournalDetail']->BankAccounts)) {
			prnMsg(_('GL Journals involving a bank account cannot be entered') . '. ' . _('Bank account general ledger entries must be entered by either a bank account receipt or a bank account payment'),'warn');
			$AllowThisPosting = false;
		}

		if ($AllowThisPosting){
			if (!isset($_POST['GLAmount'])) {
				$_POST['GLAmount']=0;
			}
			$SQL = 'SELECT accountname FROM chartmaster WHERE accountcode=' . $_POST['GLCode'];
			$Result=DB_query($SQL,$db);
			$myrow=DB_fetch_array($Result);
			$_SESSION['JournalDetail']->add_to_glanalysis($_POST['GLAmount'], $_POST['GLNarrative'], $_POST['GLCode'], $myrow['accountname'], $_POST['tag']);
		}
	}

	/*Make sure the same receipt is not double processed by a page refresh */
	$Cancel = 1;
	unset($_POST['Credit']);
	unset($_POST['Debit']);
	unset($_POST['tag']);
	unset($_POST['GLManualCode']);
	unset($_POST['GLNarrative']);
}

if (isset($Cancel)){
	unset($_POST['Credit']);
	unset($_POST['Debit']);
	unset($_POST['GLAmount']);
	unset($_POST['GLCode']);
	unset($_POST['tag']);
	unset($_POST['GLManualCode']);
}

// set up the form whatever
/*
if (!isset($_SESSION['JournalDetail']->JnlDate)){
	 $_POST['JournalProcessDate']= Date($_SESSION['DefaultDateFormat']);
	 $_SESSION['JournalDetail']->JnlDate = $_POST['JournalProcessDate'];
}
*/

echo '<form action=' . $_SERVER['PHP_SELF'] . '?' . SID . ' method=post name="form">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/maintenance.png" title="' . _('Search') . '" alt="">' . ' ' . $title.'</p>';

// A new table in the first column of the main table

echo '<table class=selection width=30%><tr><td>'._('Date to Process Journal').":</td>
		<td><input type='text' class='date' alt='".$_SESSION['DefaultDateFormat'].
			"' name='JournalProcessDate' maxlength=10 size=11 value='" .
				 $_SESSION['JournalDetail']->JnlDate . "'></td>";
echo '<td><input type="submit" name="update" value="'._('Update Journal').'">';
echo '<input type=hidden name=JournalType value=Normal>';
echo '</tr>
	</table><br>';
/* close off the table in the first column  */


echo "<table class=selection width=85%>
				<tr>
					<th>"._('GL Tag')."</th>
					<th>"._('GL Account')."</th>
					<th>"._('Debit')."</th>
					<th>"._('Credit')."</th>
					<th>"._('Narrative').'</th></tr>';

$debittotal=0;
$credittotal=0;
$j=0;

foreach ($_SESSION['JournalDetail']->GLEntries as $JournalItem) {
	if ($j==1) {
		echo '<tr class="OddTableRows">';
		$j=0;
	} else {
		echo '<tr class="EvenTableRows">';
		$j++;
	}
	$sql='SELECT tagdescription
		FROM tags
		WHERE tagref='.$JournalItem->tag;
	$result=DB_query($sql, $db);
	$myrow=DB_fetch_row($result);
	if ($JournalItem->tag==0) {
		$tagdescription='None';
	} else {
		$tagdescription=$myrow[0];
	}
	echo "<td>" . $JournalItem->tag . ' - ' . $tagdescription . "</td>";
	echo "<td>" . $JournalItem->GLCode . ' - ' . $JournalItem->GLActName . "</td>";
	if($JournalItem->Amount>0) {
		echo "<td class='number'>" . number_format($JournalItem->Amount,2) . '</td><td></td>';
		$debittotal=$debittotal+$JournalItem->Amount;
	} elseif($JournalItem->Amount<0) {
		$credit=(-1 * $JournalItem->Amount);
		echo "<td></td><td class='number'>" . number_format($credit,2) . '</td>';
		$credittotal=$credittotal+$credit;
	}

	echo '<td>' . $JournalItem->Narrative  . "</td>
		<td><a href='" . $_SERVER['PHP_SELF'] . '?' . SID . '&Delete=' . $JournalItem->ID . "'>"._('Delete').'</a></td></tr>';
}

echo '<tr class="EvenTableRows"><td></td>
					<td align=right><b> Total </b></td>
					<td align=right class="number"><b>' . number_format($debittotal,2) . '</b></td>
					<td align=right class="number"><b>' . number_format($credittotal,2) . '</b></td>';
if ($debittotal!=$credittotal) {
	echo '<td align=center style="background-color: #fddbdb"><b>Required to balance - ' .
					number_format(abs($debittotal-$credittotal),2);
}
if ($debittotal>$credittotal) {
	echo ' Credit';
} else if ($debittotal<$credittotal) {
	echo ' Debit';
}

echo '</b></td></tr></table>';

if (ABS($_SESSION['JournalDetail']->JournalTotal)<0.001 AND $_SESSION['JournalDetail']->GLItemCounter > 0){
	echo "<br><br><div class='centre'><input type=submit name='CommitBatch' value='"._('Accept and Process Journal')."'></div>";
} elseif(count($_SESSION['JournalDetail']->GLEntries)>0) {
	echo '<br><br>';
	prnMsg(_('The journal must balance ie debits equal to credits before it can be processed'),'warn');
}

if (!isset($_GET['NewJournal']) or $_GET['NewJournal']=='') {
	echo "<script>defaultControl(document.form.GLManualCode);</script>";
} else {
	echo "<script>defaultControl(document.form.JournalProcessDate);</script>";
}

echo '</form>';
include('includes/footer.inc');
?>
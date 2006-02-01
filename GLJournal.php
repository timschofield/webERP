<?php

/* $Revision: 1.9 $ */

include('includes/DefineJournalClass.php');

$PageSecurity = 10;
include('includes/session.inc');
$title = _('Journal Entry');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

if ($_GET['NewJournal']=='Yes' AND isset($_SESSION['JournalDetail'])){
	unset($_SESSION['JournalDetail']->GLEntries);
	unset($_SESSION['JournalDetail']);
}

if (!isset($_SESSION['JournalDetail'])){
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
$msg='';

if ($_POST['CommitBatch']==_('Accept and Process Journal')){

 /* once the GL analysis of the journal is entered
  process all the data in the session cookie into the DB
  A GL entry is created for each GL entry
*/

	$PeriodNo = GetPeriod($_SESSION['JournalDetail']->JnlDate,$db);

     /*Start a transaction to do the whole lot inside */
	$result = DB_query('BEGIN',$db);

	$TransNo = GetNextTransNo( 0, $db);

	foreach ($_SESSION['JournalDetail']->GLEntries as $JournalItem) {
		$SQL = 'INSERT INTO gltrans (type,
						typeno,
						trandate,
						periodno,
						account,
						narrative,
						amount) ';
		$SQL= $SQL . 'VALUES (0,
					' . $TransNo . ",
					'" . FormatDateForSQL($_SESSION['JournalDetail']->JnlDate) . "',
					" . $PeriodNo . ",
					" . $JournalItem->GLCode . ",
					'" . $JournalItem->Narrative . "',
					" . $JournalItem->Amount . ")";
		$ErrMsg = _('Cannot insert a GL entry for the journal line because');
		$DbgMsg = _('The SQL that failed to insert the GL Trans record was');
		$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

		if ($_POST['JournalType']==_('Reversing')){
			$SQL = 'INSERT INTO gltrans (type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount) ';
			$SQL= $SQL . 'VALUES (0,
						' . $TransNo . ",
						'" . FormatDateForSQL($_SESSION['JournalDetail']->JnlDate) . "',
						" . ($PeriodNo + 1) . ",
						" . $JournalItem->GLCode . ",
						'Reversal - " . $JournalItem->Narrative . "',
						" . -($JournalItem->Amount) . ')';

			$ErrMsg =_('Cannot insert a GL entry for the reversing journal because');
			$DbgMsg = _('The SQL that failed to insert the GL Trans record was');
			$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

		}
	}


	$ErrMsg = _('Cannot commit the changes');
	$result= DB_query('COMMIT',$db,$ErrMsg,_('The commit database transaction failed'),true);

	prnMsg(_('Journal').' ' . $TransNo . ' '._('has been sucessfully entered'),'success');

	unset($_POST['JournalProcessDate']);
	unset($_POST['JournalType']);
	unset($_SESSION['JournalDetail']->GLEntries);
	unset($_SESSION['JournalDetail']);

	/*Set up a newy in case user wishes to enter another */
	echo "<BR><A HREF='" . $_SERVER['PHP_SELF'] . '?' . SID . "&NewJournal=Yes'>"._('Enter Another General Ledger Journal').'</A>';
	/*And post the journal too */
	include ('includes/GLPostings.inc');
	exit;

} elseif (isset($_GET['Delete'])){

  /* User hit delete the line from the journal */
   $_SESSION['JournalDetail']->Remove_GLEntry($_GET['Delete']);

} elseif ($_POST['Process']==_('Accept')){ //user hit submit a new GL Analysis line into the journal

   if ($_POST['GLManualCode']!='' AND is_numeric($_POST['GLManualCode'])){
				// If a manual code was entered need to check it exists and isnt a bank account
	$AllowThisPosting =true; //by default
	if ($_SESSION['ProhibitJournalsToControlAccounts'] ==1){
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
		$AllowThisPosting =false;
	} 
	
	if ($AllowThisPosting) {
		$SQL = 'SELECT accountname FROM chartmaster WHERE accountcode=' . $_POST['GLManualCode'];
		$Result=DB_query($SQL,$db);
		if (DB_num_rows($Result)==0){
			prnMsg(_('The manual GL code entered does not exist in the database') . ' - ' . _('so this GL analysis item could not be added'),'warn');
			unset($_POST['GLManualCode']);
		} else {
			$myrow = DB_fetch_array($Result);
			$_SESSION['JournalDetail']->add_to_glanalysis($_POST['GLAmount'], $_POST['GLNarrative'], $_POST['GLManualCode'], $myrow['accountname']);
		}
	}
   } else {
   	$AllowThisPosting =true; //by default
	if ($_SESSION['ProhibitJournalsToControlAccounts'] ==1){
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

		$SQL = 'SELECT accountname FROM chartmaster WHERE accountcode=' . $_POST['GLCode'];
		$Result=DB_query($SQL,$db);
		$myrow=DB_fetch_array($Result);
   		$_SESSION['JournalDetail']->add_to_glanalysis($_POST['GLAmount'], $_POST['GLNarrative'], $_POST['GLCode'], $myrow['accountname']);
	}
   }

   /*Make sure the same receipt is not double processed by a page refresh */
   $Cancel = 1;
}

if (isset($Cancel)){
   unset($_POST['GLAmount']);
   unset($_POST['GLCode']);
   unset($_POST['AccountName']);
}

// set up the form whatever
/*
if (!isset($_SESSION['JournalDetail']->JnlDate)){
	 $_POST['JournalProcessDate']= Date($_SESSION['DefaultDateFormat']);
	 $_SESSION['JournalDetail']->JnlDate = $_POST['JournalProcessDate'];
}
*/

echo '<FORM ACTION=' . $_SERVER['PHP_SELF'] . '?' . SID . ' METHOD=POST>';


echo '<P><TABLE BORDER=1 WIDTH=100%>';
echo '<TR><TD VALIGN=TOP WIDTH=30%><TABLE>'; // A new table in the first column of the main table

if (!Is_Date($_SESSION['JournalDetail']->JnlDate)){
	// Default the date to the last day of the previous month
	$_SESSION['JournalDetail']->JnlDate = Date($_SESSION['DefaultDateFormat'],mktime(0,0,0,date('m'),0,date('Y')));
}

echo '<TR><TD>'._('Date to Process Journal').":</TD>
	<TD><INPUT TYPE='text' name='JournalProcessDate' maxlength=10 size=11 value='" . $_SESSION['JournalDetail']->JnlDate . "'></TD></TR>";


echo '<TR><TD>' . _('Type') . ':</TD>
	<TD><SELECT name=JournalType>';

if ($_POST['JournalType']=='Reversing'){
	echo "<OPTION SELECTED Value='Reversing'>" . _('Reversing');
	echo "<OPTION VALUE='Normal'>" . _('Normal');
} else {
	echo "<OPTION Value='Reversing'>" . _('Reversing');
	echo "<OPTION SELECTED VALUE='Normal'>" . _('Normal');
}

echo '</SELECT></TD></TR>';

echo '</TABLE></TD>'; /*close off the table in the first column */

echo '<TD>';
/* Set upthe form for the transaction entry for a GL Payment Analysis item */

echo '<FONT SIZE=3 COLOR=BLUE>' . _('Journal Line Entry') . '</FONT><TABLE>';

/*now set up a GLCode field to select from avaialble GL accounts */
echo '<TR><TD>' . _('Enter GL Account Manually') . ":</TD>
	<TD><INPUT TYPE=Text Name='GLManualCode' Maxlength=12 SIZE=12 VALUE=" . $_POST['GLManualCode'] . '></TD>';
echo '<TD>'. _('OR') . ' ' . _('Select GL Account').  ":</TD><TD><SELECT name='GLCode'>";
$SQL = 'SELECT accountcode, accountname FROM chartmaster ORDER BY accountcode';
$result=DB_query($SQL,$db);
if (DB_num_rows($result)==0){
	echo '</SELECT></TD></TR>';
	prnMsg(_('No General ledger accounts have been set up yet') . ' - ' . _('payments cannot be analysed against GL accounts until the GL accounts are set up'),'warn');
} else {
	while ($myrow=DB_fetch_array($result)){
		if ($_POST['GLCode']==$myrow['accountcode']){
			echo '<OPTION SELECTED value=' . $myrow['accountcode'] . '>' . $myrow['accountcode'] . ' - ' . $myrow['accountname'];
		} else {
			echo '<OPTION value=' . $myrow['accountcode'] . '>' . $myrow['accountcode'] . ' - ' . $myrow['accountname'];
		}
	}
	echo '</SELECT></TD></TR>';
}
echo '<TR><TD>'._('GL Narrative').":</TD><TD COLSPAN=3><INPUT TYPE='text' name='GLNarrative' maxlength=50 size=52 value='" . $_POST['GLNarrative'] . "'></TD></TR>";
echo '<TR><TD>'._('Amount').":</TD><TD COLSPAN=3><INPUT TYPE=Text Name='GLAmount' Maxlength=12 SIZE=12 VALUE=" . $_POST['GLAmount'] . '></TD></TR>';
echo '</TABLE>';
echo "<CENTER><INPUT TYPE=SUBMIT name=Process value='" . _('Accept') . "'><INPUT TYPE=SUBMIT name=Cancel value='" . _('Cancel') . "'></CENTER>";

echo '</TD></TR></TABLE>'; /*Close the main table */


echo "<TABLE WIDTH=100% BORDER=1><TR>
	<TD class='tableheader'>"._('Amount')."</TD>
	<TD class='tableheader'>"._('GL Account')."</TD>
	<TD class='tableheader'>"._('Narrative').'</TD></TR>';

foreach ($_SESSION['JournalDetail']->GLEntries as $JournalItem) {
	echo "<TR><TD ALIGN=RIGHT>" . number_format($JournalItem->Amount,2) . "</TD>
		<TD>" . $JournalItem->GLCode . ' - ' . $JournalItem->GLActName . '</TD>
		<TD>' . $JournalItem->Narrative  . "</TD>
		<TD><a href='" . $_SERVER['PHP_SELF'] . '?' . SID . '&Delete=' . $JournalItem->ID . "'>"._('Delete').'</a></TD>
	</TR>';
}

echo '<TR><TD ALIGN=RIGHT><B>' . number_format($_SESSION['JournalDetail']->JournalTotal,2) . '</B></TD></TR></TABLE>';

if (ABS($_SESSION['JournalDetail']->JournalTotal)<0.001 AND $_SESSION['JournalDetail']->GLItemCounter > 0){
	echo "<BR><BR><INPUT TYPE=SUBMIT NAME='CommitBatch' VALUE='"._('Accept and Process Journal')."'>";
} elseif(count($_SESSION['JournalDetail']->GLEntries)>0) {
	echo '<BR><BR>';
	prnMsg(_('The journal must balance ie debits equal to credits before it can be processed'),'warn');
}

echo '</form>';
include('includes/footer.inc');
?>
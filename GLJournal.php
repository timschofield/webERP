<?php
include("includes/DefineJournalClass.php");

$title = "Journal Entry";
$PageSecurity = 10;
include("includes/session.inc");
include("includes/header.inc");
include("includes/DateFunctions.inc");
include("includes/SQL_CommonFunctions.inc");

if ($_GET['NewJournal']=="Yes"){
	unset($_SESSION['JournalDetail']->GLEntries);
	unset($_SESSION['JournalDetail']);
}

if (!isset($_SESSION['JournalDetail'])){
	$_SESSION['JournalDetail'] = new Journal;

	/* Make an array of the defined bank accounts - better to make it now than do it each time a line is added
	Journals cannot be entered against bank accounts GL postings involving bank accounts must be done using
	a receipt or a payment transaction to ensure a bank trans is available for matching off vs statements */

	$SQL = "SELECT AccountCode FROM BankAccounts";
	$result = DB_query($SQL,$db);
	$i=0;
	while ($Act = DB_fetch_row($result)){
		$_SESSION["JournalDetail"]->BankAccounts[$i]= $Act[0];
		$i++;
	}
}

if ($_POST['JournalProcessDate']!=""){
	$_SESSION['JournalDetail']->JnlDate=$_POST['JournalProcessDate'];
}
if ($_POST['JournalProcessDate']!="" AND !Is_Date($_POST['JournalProcessDate'])){ 
	echo "<BR><B><FONT SIZE=4 COLOR=RED>WARNING: The date entered was not valid please enter the date to process the journal in the format $DefaultDateFormat"; 
	$_POST['CommitBatch']="Dont do it the date is wrong"; 
}
if ($_POST['JournalType']!=""){
	$_SESSION['JournalDetail']->JournalType = $_POST['JournalType'];
}
$msg="";

if ($_POST['CommitBatch']=="Accept and Process Journal"){

 /* once the GL analysis of the journal is entered
  process all the data in the session cookie into the DB
  A GL entry is created for each GL entry
*/

	$PeriodNo = GetPeriod($_SESSION['JournalDetail']->JnlDate,$db);


     /*Start a transaction to do the whole lot inside */
	$SQL = "BEGIN";
	$result = DB_query($SQL,$db);


	$TransNo = GetNextTransNo( 0, $db);

	foreach ($_SESSION['JournalDetail']->GLEntries as $JournalItem) {
		$SQL = "INSERT INTO GLTrans (Type, TypeNo, TranDate, PeriodNo, Account, Narrative, Amount) ";
		$SQL= $SQL . "VALUES (0, " . $TransNo . ", '" . FormatDateForSQL($_SESSION['JournalDetail']->JnlDate) . "', " . $PeriodNo . ", " . $JournalItem->GLCode . ", '" . $JournalItem->Narrative . "', " . $JournalItem->Amount . ")";
		$result = DB_query($SQL,$db);

		if (DB_error_no($db)!=0){
			echo "<BR>Cannot insert a GL entry for the journal line using the SQL: -<BR>$SQL";
			$SQL = "Rollback";
			$result= DB_query($SQL,$db);
			exit;
		}

		if ($_POST["JournalType"]=="Reversing"){
			$SQL = "INSERT INTO GLTrans (Type, TypeNo, TranDate, PeriodNo, Account, Narrative, Amount) ";
			$SQL= $SQL . "VALUES (0, " . $TransNo . ", '" . FormatDateForSQL($_SESSION['JournalDetail']->JnlDate) . "', " . ($PeriodNo + 1) . ", " . $JournalItem->GLCode . ", 'Reversal - " . $JournalItem->Narrative . "', " . -($JournalItem->Amount) . ")";
			$result = DB_query($SQL,$db);

			if (DB_error_no($db)!=0){
				echo "<BR>Cannot insert a GL entry for the journal line using the SQL: -<BR>$SQL";
				$SQL = "Rollback";
				$result= DB_query($SQL,$db);
				exit;
			}
		}
	}


	$SQL = "Commit";
	$result= DB_query($SQL,$db);
	if (DB_error_no($db)!=0){
		echo "<BR><B>Problem Report:</B><BR>Cannot commit the changes: -<BR>$SQL";
		$SQL = "Rollback";
		$result= DB_query($SQL,$db);
		exit;
	} else {

		echo "<P>Journal " . $TransNo . " has been sucessfully entered.";

		unset($_POST['JournalProcessDate']);
   		unset($_POST['JournalType']);
   		unset($_SESSION['JournalDetail']->GLEntries);
   		unset($_SESSION['JournalDetail']);

		/*Set up a newy in case user wishes to enter another */
		echo "<BR><A HREF='" . $_SERVER['PHP_SELF'] . "?" . SID . "NewJournal=Yes'>Enter Another General Ledger Journal</A>";
		/*And post the journal too */
		include ("includes/GLPostings.inc");
		exit;
	}


} elseif (isset($_GET['Delete'])){

  /* User hit delete the line from the journal */
   $_SESSION['JournalDetail']->Remove_GLEntry($_GET['Delete']);

} elseif ($_POST['Process']=='Accept'){ //user hit submit a new GL Analysis line into the journal

   if ($_POST['GLManualCode']!="" AND is_numeric($_POST['GLManualCode'])){
				// If a manual code was entered need to check it exists and isnt a bank account
	if (in_array($_POST['GLManualCode'], $_SESSION['JournalDetail']->BankAccounts)) {
		echo "<P><FONT COLOR=RED SIZE=3>GL Journals involving a bank account cannot be entered. Bank account general ledger entries must be entered by either a bank account receipt or a bank account payment.</FONT>";
	} else {
		$SQL = "SELECT AccountName FROM ChartMaster WHERE AccountCode=" . $_POST['GLManualCode'];
		$Result=DB_query($SQL,$db);
		if (DB_num_rows($Result)==0){
			echo "<BR>The manual GL code entered does not exist in the database - so this GL analysis item could not be added.";
			unset($_POST['GLManualCode']);
		} else {
			$myrow = DB_fetch_array($Result);
			$_SESSION['JournalDetail']->add_to_glanalysis($_POST['GLAmount'], $_POST['GLNarrative'], $_POST['GLManualCode'], $myrow['AccountName']);
		}
	}
   } else {
	if (in_array($_POST['GLCode'], $_SESSION['JournalDetail']->BankAccounts)) {
		echo "<p><FONT COLOR=RED SIZE=3>GL Journals involving a bank account cannot be entered. Bank account general ledger entries must be entered by either a bank account receipt or a bank account payment.</FONT>";
	} else {

		$SQL = "SELECT AccountName FROM ChartMaster WHERE AccountCode=" . $_POST['GLCode'];
		$Result=DB_query($SQL,$db);
		$myrow=DB_fetch_array($Result);
   		$_SESSION['JournalDetail']->add_to_glanalysis($_POST['GLAmount'], $_POST['GLNarrative'], $_POST['GLCode'], $myrow['AccountName']);
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
	 $_POST['JournalProcessDate']= Date($DefaultDateFormat);
	 $_SESSION['JournalDetail']->JnlDate = $_POST['JournalProcessDate'];
}
*/

echo "<FORM ACTION=" . $_SERVER['PHP_SELF'] . "?" . SID . " METHOD=POST>";


echo "<P><TABLE BORDER=1 WIDTH=100%>";
echo "<TR><TD VALIGN=TOP WIDTH=30%><TABLE>"; // A new table in the first column of the main table

if (!Is_Date($_SESSION['JournalDetail']->JnlDate)){ 
	// Default the date to the last day of the previous month
	$_SESSION['JournalDetail']->JnlDate = Date($DefaultDateFormat,mktime(0,0,0,date("m"),0,date("Y"))); 
} 

echo "<TR><TD>Date to Process Journal:</TD><TD><INPUT TYPE='text' name='JournalProcessDate' maxlength=10 size=11 value='" . $_SESSION['JournalDetail']->JnlDate . "'></TD></TR>";


echo "<TR><TD>Type:</TD><TD><SELECT name=JournalType>";

if ($_POST["JournalType"]=="Reversing"){
	echo "<OPTION SELECTED Value='Reversing'>Reversing";
	echo "<OPTION VALUE='Normal'>Normal";
} else {
	echo "<OPTION Value='Reversing'>Reversing";
	echo "<OPTION SELECTED VALUE='Normal'>Normal";
}

echo "</SELECT></TD></TR>";

echo "</TABLE></TD>"; /*close off the table in the first column */

echo "<TD>";
/* Set upthe form for the transaction entry for a GL Payment Analysis item */

echo "<FONT SIZE=3 COLOR=BLUE>Journal Line Entry</FONT><TABLE>";

/*now set up a GLCode field to select from avaialble GL accounts */
echo "<TR><TD>Enter GL Account Manually:</TD><TD><INPUT TYPE=Text Name='GLManualCode' Maxlength=12 SIZE=12 VALUE=" . $_POST['GLManualCode'] . "></TD>";
echo "<TD>OR Select GL Account:</TD><TD><SELECT name='GLCode'>";
$SQL = "SELECT AccountCode, AccountName FROM ChartMaster ORDER BY AccountCode";
$result=DB_query($SQL,$db);
if (DB_num_rows($result)==0){
	echo "</SELECT>No General ledger accounts have been set up yet - payments cannot be analysed against GL accounts until the GL accounts are set up.</TD></TR>";
} else {
	while ($myrow=DB_fetch_array($result)){
		if ($_POST['GLCode']==$myrow["AccountCode"]){
			echo "<OPTION SELECTED value=" . $myrow["AccountCode"] . ">" . $myrow["AccountCode"] . " - " . $myrow["AccountName"];
		} else {
			echo "<OPTION value=" . $myrow["AccountCode"] . ">" . $myrow["AccountCode"] . " - " . $myrow["AccountName"];
		}
	}
	echo "</SELECT></TD></TR>";
}
echo "<TR><TD>GL Narrative:</TD><TD COLSPAN=3><INPUT TYPE='text' name='GLNarrative' maxlength=50 size=52 value='" . $_POST['GLNarrative'] . "'></TD></TR>";
echo "<TR><TD>Amount:</TD><TD COLSPAN=3><INPUT TYPE=Text Name='GLAmount' Maxlength=12 SIZE=12 VALUE=" . $_POST['GLAmount'] . "></TD></TR>";
echo "</TABLE>";
echo "<CENTER><INPUT TYPE=SUBMIT name=Process value='Accept'><INPUT TYPE=SUBMIT name=Cancel value='Cancel'></CENTER>";

echo "</TD></TR></TABLE>"; /*Close the main table */


echo "<TABLE WIDTH=100% BORDER=1><TR><td BGCOLOR=#800000><FONT COLOR='#ffffff'><B>Amount</B></FONT></TD><td BGCOLOR=#800000><FONT COLOR='#ffffff'><B>GL Account</B></FONT></TD><td BGCOLOR=#800000><FONT COLOR='#ffffff'><B>Narrative</B></FONT></TD></TR>";

foreach ($_SESSION['JournalDetail']->GLEntries as $JournalItem) {
	echo "<TR><TD ALIGN=RIGHT>" . number_format($JournalItem->Amount,2) . "</TD><TD>" . $JournalItem->GLCode . " - " . $JournalItem->GLActName . "</TD><TD>" . $JournalItem->Narrative  . "</TD><TD><a href='" . $_SERVER['PHP_SELF'] . "?" . SID . "Delete=" . $JournalItem->ID . "'>Delete</a></TD></TR>";
}

echo "<TR><TD ALIGN=RIGHT><B>" . number_format($_SESSION['JournalDetail']->JournalTotal,2) . "</B></TD></TR></TABLE>";

if (ABS($_SESSION["JournalDetail"]->JournalTotal)<0.001 AND $_SESSION["JournalDetail"]->GLItemCounter > 0){
	echo "<BR><BR><INPUT TYPE=SUBMIT NAME='CommitBatch' VALUE='Accept and Process Journal'>";
} else {
	echo "<BR><BR>The journal must balance ie debits equal to credits before it can be processed</FONT>";
}

echo "</form>";
include("includes/footer.inc");
?>

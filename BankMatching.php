<?php
if ($_GET["Type"]=="Receipts" OR $_POST["Type"]=="Receipts"){
	$Type = "Receipts";
	$title = "Bank Account Deposits Matching";
} elseif ($_GET["Type"]=="Payments" OR $_POST["Type"]=="Payments") {
	$Type = "Payments";
	$title = "Bank Account Payments Matching";
} else {
	die ("<P>This page must be called with a bank transaction type. It should not be called directly.");
}

$PageSecurity = 7;

include("includes/session.inc");
include("includes/header.inc");
include("includes/DateFunctions.inc");


if ($_POST["Update"]=="Update Matching" AND $_POST["RowCounter"]>1){
	for ($Counter=1;$Counter <= $_POST["RowCounter"]; $Counter++){
		if ($_POST["Clear_" . $Counter]==True){
			/*Update the banktrans recoord to match it off */
			$sql = "UPDATE BankTrans SET AmountCleared=(Amount/ExRate) WHERE BankTransID=" . $_POST["BankTrans_" . $Counter];
			$result = DB_query($sql,$db);
			if (DB_error_no($db)!=0){
			    echo "<BR>Could not match off this payment - the failed SQL was: -<BR>$sql";
			}
		} elseif (is_numeric((float) $_POST["AmtClear_" . $Counter]) AND (($_POST["AmtClear_" . $Counter]<0 AND $Type=="Payments") OR ($Type=="Receipts") AND ($_POST["AmtClear_" . $Counter]>0))){
			/*if the amount entered was numeric and negative for a payment or positive for a receipt */
			$sql = "UPDATE BankTrans SET AmountCleared=" .  $_POST["AmtClear_" . $Counter] . " WHERE BankTransID=" . $_POST["BankTrans_" . $Counter];
			$result = DB_query($sql,$db);
			if (DB_error_no($db)!=0){
			    echo "<BR>Could not update the amount matched off this bank transaction - the failed SQL was: -<BR>$sql";
			}
		} elseif ($_POST["Unclear_" . $Counter]==True){
			$sql = "UPDATE BankTrans SET AmountCleared = 0 WHERE BankTransID=" . $_POST["BankTrans_" . $Counter];
			$result = DB_query($sql,$db);
			if (DB_error_no($db)!=0){
				echo "<BR>Could not un-clear this bank transaction - the failed SQL was: -<BR>$sql";
			}
		}
	}
 	/*Show the updated position with the same criteria as previously entered*/
 	$_POST["ShowTransactions"]="Show Selected " . $Type;
}



echo "<FORM ACTION='". $_SERVER['PHP_SELF'] . "?" . SID . "' METHOD=POST>";

echo "<INPUT TYPE=HIDDEN Name=Type Value=$Type>";

echo "<TABLE><TR>";
echo "<TD ALIGN=RIGHT>Bank Account:</TD><TD COLSPAN=3><SELECT name='BankAccount'> ";

$sql = "SELECT AccountCode, BankAccountName FROM BankAccounts";
$resultBankActs = DB_query($sql,$db);
while ($myrow=DB_fetch_array($resultBankActs)){
	if ($myrow["AccountCode"] == $_POST['BankAccount']){
	     echo "<OPTION SELECTED Value='" . $myrow["AccountCode"] . "'>" . $myrow["BankAccountName"];
	} else {
	     echo "<OPTION Value='" . $myrow["AccountCode"] . "'>" . $myrow["BankAccountName"];
	}
}

echo "</SELECT></TD></TR>";

if (!isset($_POST['BeforeDate']) OR !Is_Date($_POST['BeforeDate'])){
   $_POST['BeforeDate'] = Date($DefaultDateFormat);
}
if (!isset($_POST['AfterDate']) OR !Is_Date($_POST['AfterDate'])){
   $_POST['AfterDate'] = Date($DefaultDateFormat, Mktime(0,0,0,Date("m")-3,Date("d"),Date("y")));
}

echo "<TR><TD>Show $Type before:</TD><TD><INPUT TYPE=TEXT NAME='BeforeDate' SIZE=12 MAXLENGTH=12 Value='" . $_POST['BeforeDate'] . "'></TD>";
echo "<TD>But after:</TD><TD><INPUT TYPE=TEXT NAME='AfterDate' SIZE=12 MAXLENGTH=12 Value='" . $_POST['AfterDate'] . "'></TD></TR>";
echo "<TR><TD COLSPAN=3>Choose Outstanding $Type only or All $Type in the date range:</TD><TD><SELECT NAME='Ostg_or_All'>";

if ($_POST["Ostg_or_All"]=="All"){
	echo "<OPTION SELECTED Value='All'>Show All $Type in the date range";
	echo "<OPTION Value='Ostdg'>Show Only Un-matched $Type";
} else {
	echo "<OPTION Value='All'>Show All $Type in the date range";
	echo "<OPTION SELECTED Value='Ostdg'>Show Only Un-matched $Type";
}
echo "</SELECT></TD></TR>";

echo "<TR><TD COLSPAN=3>Choose to display only the first 20 matching $Type or all $Type meeting the criteria:</TD><TD><SELECT NAME='First20_or_All'>";
if ($_POST["First20_or_All"]=="All"){
	echo "<OPTION SELECTED Value='All'>Show All $Type in the date range";
	echo "<OPTION Value='First20'>Show Only The First 20 $Type";
} else {
	echo "<OPTION Value='All'>Show All $Type in the date range";
	echo "<OPTION SELECTED Value='First20'>Show Only The First 20 $Type";
}
echo "</SELECT></TD></TR>";


echo "</TABLE><CENTER><INPUT TYPE=SUBMIT NAME='ShowTransactions' VALUE='Show Selected $Type'>";
echo "<P><A HREF='$rootpath/BankReconciliation.php?" . SID . "'>Show Reconciliation</A>";
echo "<HR>";

$InputError=0;
if (!Is_Date($_POST['BeforeDate'])){
	$InputError =1;
	echo "<P>The date entered for the field to show $Type before, is not entered in a recognised date format. Entry is expected in the format $DefaultDateFormat";
}
if (!Is_Date($_POST['AfterDate'])){
	$InputError =1;
	echo "<P>The date entered for the field to show $Type after, is not entered in a recognised date format. Entry is expected in the format $DefaultDateFormat";
}

if ($InputError !=1 AND isset($_POST["BankAccount"]) AND $_POST["BankAccount"]!="" AND $_POST["ShowTransactions"]=="Show Selected " . $Type){

	$SQLBeforeDate = FormatDateForSQL($_POST['BeforeDate']);
	$SQLAfterDate = FormatDateForSQL($_POST['AfterDate']);

	if ($_POST["Ostg_or_All"]=="All"){
		if ($Type=="Payments"){
			$sql = "SELECT BankTransID, Ref, AmountCleared, TransDate, Amount/ExRate AS Amt, BankTransType FROM BankTrans WHERE Amount <0 AND TransDate >= '". $SQLAfterDate . "' AND TransDate <= '" . $SQLBeforeDate . "' AND BankAct=" .$_POST["BankAccount"] . " ORDER BY BankTransID";
		} else { /* Type must == Receipts */
			$sql = "SELECT BankTransID, Ref, AmountCleared, TransDate, Amount/ExRate AS Amt, BankTransType FROM BankTrans WHERE Amount >0 AND TransDate >= '". $SQLAfterDate . "' AND TransDate <= '" . $SQLBeforeDate . "' AND BankAct=" .$_POST["BankAccount"] . " ORDER BY BankTransID";
		}
	} else { /*it must be only the outstanding bank trans required */
		if ($Type=="Payments"){
			$sql = "SELECT BankTransID, Ref, AmountCleared, TransDate, Amount/ExRate AS Amt, BankTransType FROM BankTrans WHERE Amount <0 AND TransDate >= '". $SQLAfterDate . "' AND TransDate <= '" . $SQLBeforeDate . "' AND BankAct=" .$_POST["BankAccount"] . " AND  ABS(AmountCleared - (Amount / ExRate)) > 0.009 ORDER BY BankTransID";
		} else { /* Type must == Receipts */
			$sql = "SELECT BankTransID, Ref, AmountCleared, TransDate, Amount/ExRate AS Amt, BankTransType FROM BankTrans WHERE Amount >0 AND TransDate >= '". $SQLAfterDate . "' AND TransDate <= '" . $SQLBeforeDate . "' AND BankAct=" .$_POST["BankAccount"] . " AND  ABS(AmountCleared - (Amount / ExRate)) > 0.009 ORDER BY BankTransID";
		}
	}
	if ($_POST["First20_or_All"]!="All"){
		$sql = $sql . " LIMIT 20";
	}
	$PaymentsResult = DB_query($sql, $db);
	if (DB_error_no($db) !=0) {
		echo "The payments with the selected criteria could not be retrieved because - " . DB_error_msg($db);
		if ($debug==1){
	   		echo "<BR>The SQL that failed was $sql";
		}
		exit;
	}
	/*chops off the last s character to get the singular cheque or receipt */
	$SingularType = substr($Type,0,7);

	$TableHeader = "<TR><TD class='tableheader'>Ref</TD><TD class='tableheader'>Type</TD><TD class='tableheader'>Date</TD><TD class='tableheader'>$SingularType Amount</TD><TD class='tableheader'>Outstanding</TD><TD COLSPAN=3 ALIGN=CENTER class='tableheader'>Clear  /  Unclear</TD></TR>";
	echo "<TABLE CELLPADDING=2 BORDER=2>" . $TableHeader;


	$j = 1;  //page length counter
	$k=0; //row colour counter
	$i = 1; //no of rows counter

	while ($myrow=DB_fetch_array($PaymentsResult)) {

		$DisplayTranDate = ConvertSQLDate($myrow["TransDate"]);
		$Outstanding = $myrow["Amt"]- $myrow["AmountCleared"];
		if (ABS($Outstanding)<0.009){ /*the payment is cleared dont show the check box*/
			/*          Ref    BankTransType TransDate              Amt                AmountCleared                                        Ref         BankTransType                      TransDate                             Amt                                 AmountCleared */
			printf("<tr bgcolor='#CCCEEE'><td>%s</td><td>%s</td><td>%s</td><td ALIGN=RIGHT>%s</td><td ALIGN=RIGHT>%s</td><td COLSPAN=2 ALIGN=CENTER>Cleared</td><td ALIGN=CENTER><INPUT TYPE='checkbox' NAME='Unclear_%s'><INPUT TYPE=HIDDEN NAME='BankTrans_%s' VALUE=%s></TD></tr>", $myrow["Ref"], $myrow["BankTransType"], $DisplayTranDate, number_format($myrow["Amt"],2),number_format($Outstanding,2),$i,$i, $myrow["BankTransID"]);

		} else{
			if ($k==1){
				echo "<tr bgcolor='#CCCCCC'>";
				$k=0;
			} else {
				echo "<tr bgcolor='#EEEEEE'>";
				$k=1;
			}
			/*          Ref    BankTransType TransDate              Amt                AmountCleared                                     BankTransID                                                           BankTransID               Ref         BankTransType                      TransDate                             Amt                                 AmountCleared             BankTransID       BankTransID */
			printf("<td>%s</td><td>%s</td><td>%s</td><td ALIGN=RIGHT>%s</td><td ALIGN=RIGHT>%s</td><td ALIGN=CENTER><INPUT TYPE='checkbox' NAME='Clear_%s'><INPUT TYPE=HIDDEN NAME='BankTrans_%s' VALUE=%s></td><td COLSPAN=2><INPUT TYPE='text' MAXLENGTH=15 SIZE=15 NAME='AmtClear_%s'></td></tr>", $myrow["Ref"], $myrow["BankTransType"], $DisplayTranDate, number_format($myrow["Amt"],2),number_format($Outstanding,2),$i,$i,$myrow["BankTransID"], $i);
		}

		$j++;
		If ($j == 12){
			$j=1;
			echo $TableHeader;
		}
	//end of page full new headings if
		$i++;
	}
	//end of while loop

	echo "</TABLE><CENTER><INPUT TYPE=HIDDEN NAME='RowCounter' VALUE=$i><INPUT TYPE=SUBMIT NAME='Update' VALUE='Update Matching'></CENTER>";

}


echo "</form>";
include("includes/footer.inc");
?>

<?php

$title = "Bank Reconciliation";

$PageSecurity = 7;

include ("includes/session.inc");
include("includes/header.inc");
include("includes/DateFunctions.inc");


echo "<FORM METHOD='POST' ACTION=" . $_SERVER["PHP_SELF"] . "?" . SID . ">";

echo "<CENTER><TABLE>";

$SQL = "SELECT BankAccountName, AccountCode FROM BankAccounts";

$AccountsResults = DB_query($SQL,$db);

if (DB_error_no($db) !=0) {
	 echo "The bank accounts could not be retrieved by the SQL because - " . DB_error_msg($db);
	 if ($debug==1){
		echo "The SQL used to retrieve the bank acconts was:<BR>$SQL";
	 }
	 exit;
}

echo "<TR><TD>Bank Account:</TD><TD><SELECT name='BankAccount'>";

if (DB_num_rows($AccountsResults)==0){
	 echo "</SELECT></TD></TR></TABLE><P>Bank Accounts have not yet been defined. You must first <A HREF='$rootpath/BankAccounts.php'>define the bank accounts</A> and general ledger accounts to be affected.";
	 exit;
} else {
	while ($myrow=DB_fetch_array($AccountsResults)){
		/*list the bank account names */
		if ($_POST["BankAccount"]==$myrow["AccountCode"]){
			echo "<OPTION SELECTED VALUE='" . $myrow["AccountCode"] . "'>" . $myrow["BankAccountName"];
		} else {
			echo "<OPTION VALUE='" . $myrow["AccountCode"] . "'>" . $myrow["BankAccountName"];
		}
	}
	echo "</SELECT></TD></TR>";
}

/*Now do the posting while the user is thinking about the bank account to select */

include ("includes/GLPostings.inc");

echo "</TABLE><P><INPUT TYPE=SUBMIT Name='ShowRec' Value='Show Bank Reconciliation Statement'></CENTER>";


if (isset($_POST["ShowRec"]) AND $_POST["ShowRec"]!=""){

/*Get the balance of the bank account concerned */

	$sql = "SELECT Max(Period) FROM ChartDetails WHERE AccountCode=" . $_POST["BankAccount"];
	$PrdResult = DB_query($sql, $db);
	$myrow = DB_fetch_row($PrdResult);
	$LastPeriod = $myrow[0];


	$SQL = "SELECT BFwd+Actual AS Balance FROM ChartDetails WHERE Period=$LastPeriod AND AccountCode=" . $_POST["BankAccount"];

	$BalanceResult = DB_query($SQL,$db);

	if (DB_error_no($db) !=0) {
		echo "<BR>The bank account balance could not be returned by the SQL because - " . DB_error_msg($db);
		if ($debug==1){
			echo "<BR>$SQL";
		}
		exit;
	}
	$myrow = DB_fetch_row($BalanceResult);
	$Balance = $myrow[0];

	echo "<CENTER><TABLE><TR><TD COLSPAN=6><B>Current Bank Account Balance as at " . Date($DefaultDateFormat) . "</B></TD><TD VALIGN=BOTTOM ALIGN=RIGHT><B>" . number_format($Balance,2) . "</B></TD></TR>";

	$SQL = "SELECT Amount/ExRate As Amt, AmountCleared, (Amount/ExRate)-AmountCleared AS Outstanding, Ref, TransDate, SysTypes.TypeName, TransNo FROM BankTrans, SysTypes WHERE BankTrans.Type = SysTypes.TypeID AND BankTrans.BankAct=" . $_POST["BankAccount"] . " AND Amount < 0 AND ABS((Amount/ExRate)-AmountCleared)>0.009";

	echo "<TR></TR>"; /*Bang in a blank line */

	$UPChequesResult = DB_query($SQL,$db);

	if (DB_error_no($db) !=0) {
		echo "<BR>The unpresented cheques could not be retrieved by the SQL because - " . DB_error_msg($db);
		if ($debug==1){
			echo "<BR>$SQL";
		}
		exit;
	}

	echo "<TR><TD COLSPAN=6><B>Add back unpresented cheques:</B></TD></TR>";
	$TableHeader = "<TR><TR><TD class='tableheader'>Date</TD><TD class='tableheader'>Type</TD><TD class='tableheader'>Number</TD><TD class='tableheader'>Reference</TD><TD class='tableheader'>Orig Amount</TD><TD class='tableheader'>Outstanding</TD></TR>";
	echo $TableHeader;


	$j = 1;
	$k=0; //row colour counter
	$TotalUnpresentedCheques =0;

	while ($myrow=DB_fetch_array($UPChequesResult)) {
		if ($k==1){
			echo "<tr bgcolor='#CCCCCC'>";
			$k=0;
		} else {
			echo "<tr bgcolor='#EEEEEE'>";
			$k++;
		}

  		printf("<td>%s</td><td>%s</td><td>%s</td><td>%s</td><td ALIGN=RIGHT>%01.2f</td><td ALIGN=RIGHT>%01.2f</td></tr>",ConvertSQLDate($myrow["TransDate"]),$myrow["TypeName"],$myrow["TransNo"],$myrow["Ref"], $myrow["Amt"], $myrow["Outstanding"]);

		$TotalUnpresentedCheques +=$myrow["Outstanding"];

		$j++;
		If ($j == 18){
			$j=1;
			echo $TableHeader;
		}
	}
	//end of while loop
	echo "<TR></TR><TR><TD COLSPAN=6>Total of all unpresented cheques</TD><TD ALIGN=RIGHT>" . number_format($TotalUnpresentedCheques,2) . "</TD></TR>";

	$SQL = "SELECT Amount/ExRate As Amt, AmountCleared, (Amount/ExRate)-AmountCleared AS Outstanding, Ref, TransDate, SysTypes.TypeName, TransNo FROM BankTrans, SysTypes WHERE BankTrans.Type = SysTypes.TypeID AND BankTrans.BankAct=" . $_POST["BankAccount"] . " AND Amount > 0 AND ABS((Amount/ExRate)-AmountCleared)>0.009";

	echo "<TR></TR>"; /*Bang in a blank line */

	$UPChequesResult = DB_query($SQL,$db);

	if (DB_error_no($db) !=0) {
		echo "<BR>The uncleared deposits could not be retrieved by the SQL because - " . DB_error_msg($db);
		if ($debug==1){
			echo "<BR>$SQL";
		}
		exit;
	}

	echo "<TR><TD COLSPAN=6><B>Less Depostis not cleared:</B></TD></TR>";

	$TableHeader = "<TR><TD class='tableheader'>Date</TD><TD class='tableheader'>Type</TD><TD class='tableheader'>Number</TD><TD class='tableheader'>Reference</TD><TD class='tableheader'>Orig Amount</TD><TD class='tableheader'>Outstanding</TD></TR>";

	echo "<TR>" . $TableHeader;

	$j = 1;
	$k=0; //row colour counter
	$TotalUnclearedDeposits =0;

	while ($myrow=DB_fetch_array($UPChequesResult)) {
		if ($k==1){
			echo "<tr bgcolor='#CCCCCC'>";
			$k=0;
		} else {
			echo "<tr bgcolor='#EEEEEE'>";
			$k++;
		}

  		printf("<td>%s</td><td>%s</td><td>%s</td><td>%s</td><td ALIGN=RIGHT>%01.2f</td><td ALIGN=RIGHT>%01.2f</td></tr>",ConvertSQLDate($myrow["TransDate"]),$myrow["TypeName"],$myrow["TransNo"],$myrow["Ref"], $myrow["Amt"], $myrow["Outstanding"]);

		$TotalUnclearedDeposits +=$myrow["Outstanding"];

		$j++;
		If ($j == 18){
			$j=1;
			echo $TableHeader;
		}
	}
	//end of while loop
	echo "<TR></TR><TR><TD COLSPAN=6>Total of all uncleared deposits</TD><TD ALIGN=RIGHT>" . number_format($TotalUnclearedDeposits,2) . "</TD></TR>";

	echo "<TR></TR><TR><TD COLSPAN=6><B>Bank Statement Balance Should Be</B></TD><TD ALIGN=RIGHT>" . number_format(($Balance - $TotalUnpresentedCheques -$TotalUnclearedDeposits),2) . "</TD></TR>";

	echo "</TABLE>";
}
echo "<P><A HREF='$rootpath/BankMatching.php?" . SID . "Type=Payments'>Match Off Cleared Payments</A>";
echo "<BR><A HREF='$rootpath/BankMatching.php?" . SID . "Type=Receipts'>Match Off Cleared Deposits</A>";
echo "</form>";
include("includes/footer.inc");
?>

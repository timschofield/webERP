<?php
/* $Revision: 1.2 $ */
$title = "General Ledger Transaction Inquiry";

$PageSecurity = 8;

include ("includes/session.inc");
include("includes/header.inc");
include("includes/DateFunctions.inc");


if (!isset($_GET['TypeID']) OR !isset($_GET['TransNo'])) { /*Script was not passed the correct parameters */

	echo "<P>The script must be called with a valid transaction type and transaction number to review the general ledger postings for.";
	echo "<P><A HREF='$rootpath/index.php?". SID ."'>Back to Menu</A>";
	exit;
}


$SQL = "SELECT TypeName, TypeNo FROM SysTypes WHERE TypeID=" . $_GET['TypeID'];

$TypeResult = DB_query($SQL,$db);

if (DB_error_no($db) !=0) {
	echo "<P>The transaction type " . $_GET['TypeID'] . " could not be retrieved, the SQL returned an error because - " . DB_error_msg($db);
	echo "<BR>$SQL";
	exit;
}

if (DB_num_rows($TypeResult)==0){
        echo "<P>No transaction type is defined for type " . $_GET['TypeID'];
	exit;
}


$myrow = DB_fetch_row($TypeResult);
$TransName = $myrow[0];
if ($myrow[1]<$_GET['TransNo']){
	echo "<P>The transaction number the script was called with is requesting a " . $TransName . " beyond the last one entered.";
	exit;
}

echo "<BR><CENTER><FONT SIZE=4 COLOR=BLUE>$TransName " . $_GET['TransNo'] . "</FONT>";


$SQL = "SELECT TranDate, PeriodNo, AccountName, Narrative, Amount, Posted FROM GLTrans, ChartMaster WHERE GLTrans.Account = ChartMaster.AccountCode AND Type= " . $_GET['TypeID'] . " AND TypeNo = " . $_GET['TransNo'] . " ORDER BY CounterIndex";
$TransResult = DB_query($SQL,$db);

if (DB_error_no($db) !=0) {
	echo "<P>The transactions for " . $TransName . " number " .  $_GET['TransNo'] . " could not be retrieved, the SQL returned an error because - " . DB_error_msg($db);
	echo "<BR>$SQL";
	exit;
}

if (DB_num_rows($TransResult)==0){
        echo "<P>No general ledger transactions have been created for " . $TransName . " number " . $_GET['TransNo'];
	exit;
}



/*show a table of the transactions returned by the SQL */

echo "<CENTER><TABLE CELLPADDING=2 width=100%>";

$TableHeader = "<TR><TD class='tableheader'>Date</TD><TD class='tableheader'>Period</TD><TD class='tableheader'>Account</TD><TD class='tableheader'>Amount</TD><TD class='tableheader'>Narrative</TD><TD class='tableheader'>Posted</TD></TR>";

echo $TableHeader;

$j = 1;
$k=0; //row colour counter
while ($myrow=DB_fetch_array($TransResult)) {
       if ($k==1){
              echo "<tr bgcolor='#CCCCCC'>";
              $k=0;
       } else {
              echo "<tr bgcolor='#EEEEEE'>";
              $k++;
       }

       if ($myrow['Posted']==0){
       		$Posted = "No";
	} else {
		$Posted = "Yes";
	}
       $FormatedTranDate = ConvertSQLDate($myrow["TranDate"]);
       printf("<td>%s</td><td ALIGN=RIGHT>%s</td><td>%s</td><td ALIGN=RIGHT>%s</td><td>%s</td><td>%s</td></tr>", $FormatedTranDate, $myrow["PeriodNo"],$myrow['AccountName'],number_format($myrow['Amount'],2), $myrow['Narrative'], $Posted);

       $j++;
       If ($j == 18){
		$j=1;
		echo $TableHeader;
       }
}
//end of while loop

echo "</TABLE></CENTER>";

include("includes/footer.inc");

?>

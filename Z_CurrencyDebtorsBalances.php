<?php
/* $Revision: 1.2 $ */
$PageSecurity=15;

$title="Currency Debtor Balances";

include("includes/session.inc");
include("includes/header.inc");
include("includes/SQL_CommonFunctions.inc");

$CompanyRecord = ReadInCompanyRecord(&$db);


echo "<FONT SIZE=4><B>Debtors Balances By Currency Totals</B></FONT>";

$sql = "SELECT Sum(OvAmount+OvGST+OvDiscount+OvFreight-Alloc) AS CurrencyBalance, CurrCode, SUM((OvAmount+OvGST+OvDiscount+OvFreight-Alloc)/Rate) AS LocalBalance FROM DebtorTrans INNER JOIN DebtorsMaster ON DebtorTrans.DebtorNo=DebtorsMaster.DebtorNo WHERE (OvAmount+OvGST+OvDiscount+OvFreight-Alloc)<>0 GROUP BY CurrCode";

$result = DB_query($sql,$db);

if (DB_error_no($db)!=0){
	echo "<BR>The sql failed with the following message:<BR>" . DB_error_msg($db) . "<BR>The SQL that failed was:<BR>$sql";
}

$LocalTotal =0;

echo "<TABLE>";

while ($myrow=DB_fetch_array($result)){

	echo "<TR><TD><FONT SIZE=4>Total Debtor Balances in </FONT></TD><TD><FONT SIZE=4>" . $myrow['CurrCode'] . "</FONT></TD><TD ALIGN=RIGHT><FONT SIZE=4>" . number_format($myrow['CurrencyBalance'],2) . "</FONT></TD><TD><FONT SIZE=4> in " . $CompanyRecord['CurrencyDefault'] . "</FONT></TD><TD ALIGN=RIGHT><FONT SIZE=4>" . number_format($myrow['LocalBalance'],2) . "</FONT></TD></TR>";
	$LocalTotal += $myrow['LocalBalance'];
}

echo "<TR><TD COLSPAN=4><FONT SIZE=4>Total Balances in local currency:</FONT></TD><TD ALIGN=RIGHT><FONT SIZE=4>" . number_format($LocalTotal,2) . "</FONT></TD></TR>";

echo "</TABLE>";

include("includes/footer.inc");
?>


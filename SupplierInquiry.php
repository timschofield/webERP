<?php

include("includes/DateFunctions.inc");
include("includes/SQL_CommonFunctions.inc");
$title = "Supplier Inquiry";

$PageSecurity=2;

include("includes/session.inc");
include("includes/header.inc");

// always figure out the SQL required from the inputs available

if(!isset($_GET['SupplierID']) AND !isset($_SESSION['SupplierID'])){
	echo "<BR>To display the enquiry a Supplier must first be selected from the Supplier selection screen<BR><CENTER><A HREF='". $rootpath . "/SelectSupplier.php'>Select a Supplier to Inquire On</A></CENTER>";
	exit;
} else {
	if (isset($_GET['SupplierID'])){
		$_SESSION['SupplierID'] = $_GET['SupplierID'];
	}
	$SupplierID = $_SESSION['SupplierID'];
}

if (isset($_GET['FromDate'])){
	$_POST['TransAfterDate']=$_GET['FromDate'];
}
if (!isset($_POST['TransAfterDate']) OR !Is_Date($_POST['TransAfterDate'])) {

	$_POST['TransAfterDate'] = Date($DefaultDateFormat,Mktime(0,0,0,Date("m")-12,Date("d"),Date("Y")));
}


$SQL = "SELECT Suppliers.SuppName, Currencies.Currency, PaymentTerms.Terms,

Sum(SuppTrans.OvAmount + SuppTrans.OvGST - SuppTrans.Alloc) AS Balance,

Sum(IF (PaymentTerms.DaysBeforeDue > 0,
	CASE WHEN (TO_DAYS(Now()) - TO_DAYS(SuppTrans.TranDate)) >= PaymentTerms.DaysBeforeDue
	THEN SuppTrans.OvAmount + SuppTrans.OvGST - SuppTrans.Alloc ELSE 0 END,

	CASE WHEN TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(SuppTrans.TranDate,
	INTERVAL 1 MONTH), INTERVAL (PaymentTerms.DayInFollowingMonth -
	DAYOFMONTH(SuppTrans.TranDate)) DAY)) >= 0 THEN SuppTrans.OvAmount + SuppTrans.OvGST
	- SuppTrans.Alloc ELSE 0 END

)) AS Due,


Sum(IF (PaymentTerms.DaysBeforeDue > 0,
	CASE WHEN TO_DAYS(Now()) - TO_DAYS(SuppTrans.TranDate) > PaymentTerms.DaysBeforeDue
	AND TO_DAYS(Now()) - TO_DAYS(SuppTrans.TranDate) >= (PaymentTerms.DaysBeforeDue + " .
	$PastDueDays1 . ") THEN SuppTrans.OvAmount + SuppTrans.OvGST - SuppTrans.Alloc ELSE 0 END,

	CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(SuppTrans.TranDate,
	INTERVAL 1  MONTH), INTERVAL (PaymentTerms.DayInFollowingMonth -
	DAYOFMONTH(SuppTrans.TranDate)) DAY)) >= " . $PastDueDays1 . ")
	THEN SuppTrans.OvAmount + SuppTrans.OvGST - SuppTrans.Alloc ELSE 0 END

)) AS Overdue1,

Sum(IF (PaymentTerms.DaysBeforeDue > 0,
	CASE WHEN TO_DAYS(Now()) - TO_DAYS(SuppTrans.TranDate) > PaymentTerms.DaysBeforeDue
	AND TO_DAYS(Now()) - TO_DAYS(SuppTrans.TranDate) >= (PaymentTerms.DaysBeforeDue + " .
	$PastDueDays2 . ") THEN SuppTrans.OvAmount + SuppTrans.OvGST - SuppTrans.Alloc ELSE 0 END,

	CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(SuppTrans.TranDate,
	INTERVAL 1  MONTH), INTERVAL (PaymentTerms.DayInFollowingMonth
	- DAYOFMONTH(SuppTrans.TranDate)) DAY)) >= " . $PastDueDays2 . ")
	THEN SuppTrans.OvAmount + SuppTrans.OvGST - SuppTrans.Alloc ELSE 0 END

)) AS Overdue2

FROM Suppliers,
     PaymentTerms,
     Currencies,
     SuppTrans

WHERE
     Suppliers.PaymentTerms = PaymentTerms.TermsIndicator
     AND Suppliers.CurrCode = Currencies.CurrAbrev
     AND Suppliers.SupplierID = '" . $SupplierID . "'
     AND Suppliers.SupplierID = SuppTrans.SupplierNo

GROUP BY
      Suppliers.SuppName,
      Currencies.Currency,
      PaymentTerms.Terms,
      PaymentTerms.DaysBeforeDue,
      PaymentTerms.DayInFollowingMonth";

$SupplierResult = DB_query($SQL,$db);

if (DB_error_no($db) !=0) {
	echo "The supplier details could not be retrieved by the SQL because - " . DB_error_msg($db);

	if ($debug==1){
		echo "<BR>$SQL";
	}
	exit;
}

if (DB_num_rows($SupplierResult)==0){

	/*Because there is no balance - so just retrieve the header information about the Supplier - the choice is do one query to get the balance and transactions for those Suppliers who have a balance and two queries for those who don't have a balance OR always do two queries - I opted for the former */

	$NIL_BALANCE = True;

	$SQL = "SELECT Suppliers.SuppName, Currencies.Currency, PaymentTerms.Terms
	FROM Suppliers,
	     PaymentTerms,
	     Currencies

	WHERE
	     Suppliers.PaymentTerms = PaymentTerms.TermsIndicator
	     AND Suppliers.CurrCode = Currencies.CurrAbrev
	     AND Suppliers.SupplierID = '" . $SupplierID . "'";

	$SupplierResult = DB_query($SQL,$db);

	if (DB_error_no($db) !=0) {
		echo "The supplier details could not be retrieved by the SQL because - " . DB_error_msg($db);

		if ($debug==1){
			echo "<BR>$SQL";
		}
		exit;
	}

} else {
	$NIL_BALANCE = False;
}

$SupplierRecord = DB_fetch_array($SupplierResult);

if ($NIL_BALANCE==True){
	$SupplierRecord["Balance"]=0;
	$SupplierRecord["Due"]=0;
	$SupplierRecord["Overdue1"]=0;
	$SupplierRecord["Overdue2"]=0;
}

echo "<CENTER><FONT SIZE=4>" . $SupplierRecord["SuppName"] . " </FONT></B> - (All amounts stated in " . $SupplierRecord["Currency"] . ")</CENTER><BR><B><FONT COLOR=BLUE>Terms: " . $SupplierRecord["Terms"];



if (isset($_GET['HoldType']) AND isset($_GET['HoldTrans'])){

	if ($_GET['HoldStatus']=="Hold"){
		$SQL = "UPDATE SuppTrans SET Hold=1 WHERE Type=" . $_GET['HoldType'] . " AND TransNo=" . $_GET['HoldTrans'];
	} elseif ($_GET['HoldStatus']=="Release"){
		$SQL = "UPDATE SuppTrans SET Hold=0 WHERE Type=" . $_GET['HoldType'] . " AND TransNo=" . $_GET['HoldTrans'];
	}
	$UpdateResult = DB_query($SQL,$db);

}


echo "<TABLE WIDTH=100% BORDER=1><TR><td class='tableheader'>Total Balance</TD><td class='tableheader'>Current</TD><td class='tableheader'>Now Due</TD><td class='tableheader'>" . $PastDueDays1 . "-" . $PastDueDays2 . " Days Overdue</TD><td class='tableheader'>Over " . $PastDueDays2 . " Days Overdue</TD></TR>";

echo "<TR><TD ALIGN=RIGHT>" . number_format($SupplierRecord["Balance"],2) . "</TD><TD ALIGN=RIGHT>" . number_format(($SupplierRecord["Balance"] - $SupplierRecord["Due"]),2) . "</TD><TD ALIGN=RIGHT>" . number_format(($SupplierRecord["Due"]-$SupplierRecord["Overdue1"]),2) . "</TD><TD ALIGN=RIGHT>" . number_format(($SupplierRecord["Overdue1"]-$SupplierRecord["Overdue2"]) ,2) . "</TD><TD ALIGN=RIGHT>" . number_format($SupplierRecord["Overdue2"],2) . "</TD></TR></TABLE>";

echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "?" . SID . "' METHOD=POST>";
echo "Show all transactions after: <INPUT type=text name='TransAfterDate' Value='" . $_POST['TransAfterDate'] . "' MAXLENGTH =10 SIZE=10><INPUT TYPE=SUBMIT NAME='Refresh Inquiry' VALUE='Refresh Inquiry'></FORM>";

$DateAfterCriteria = FormatDateForSQL($_POST['TransAfterDate']);

$SQL = "SELECT SuppTrans.ID, SysTypes.TypeName, SuppTrans.Type, SuppTrans.TransNo, SuppTrans.TranDate, SuppTrans.SuppReference, SuppTrans.Rate, (SuppTrans.OvAmount + SuppTrans.OvGST) AS TotalAmount, SuppTrans.Alloc AS Allocated, SuppTrans.Hold, SuppTrans.Settled, SuppTrans.TransText FROM SuppTrans, SysTypes WHERE SuppTrans.Type = SysTypes.TypeID AND SuppTrans.SupplierNo = '" . $SupplierID . "' AND SuppTrans.TranDate >= '$DateAfterCriteria' ORDER BY SuppTrans.TranDate";


$TransResult = DB_query($SQL,$db);

if (DB_error_no($db) !=0) {
	echo "<P>No transactions were returned by the SQL because - " . DB_error_msg($db);

	if ($debug==1){
		echo "<BR>$SQL";
	}
}

if (DB_num_rows($TransResult)==0){
	echo "<P>There are no transactions to display since " . $_POST['TransAfterDate'];
	exit;
}
/*show a table of the transactions returned by the SQL */

$CompanyRecord = ReadInCompanyRecord($db);


echo "<TABLE CELLPADDING=2 COLSPAN=7>";
$TableHeader = "<TR><TD class='tableheader'>Trans #</TD><TD class='tableheader'>Type</TD><TD class='tableheader'>Supplier Invoice #</TD><TD class='tableheader'>Date</TD><TD class='tableheader'>Total</TD><TD class='tableheader'>Allocated</TD><TD class='tableheader'>Balance</TD><TD class='tableheader'>Comments</TD></TR>";

echo $TableHeader;

$j = 1;
$k=0; //row colour counter
while ($myrow=DB_fetch_array($TransResult)) {

	if ($myrow['Hold']==0 AND $myrow['Settled']==0){
		$HoldValue="Hold";
	} elseif ($myrow['Settled']==1) {
		$HoldValue="";
	}else {
		$HoldValue="Release";
	}
	if ($myrow['Hold']==1){
		echo "<tr bgcolor='#DD99BB'>";
	}elseif ($k==1){
		echo "<tr bgcolor='#CCCCCC'>";
		$k=0;
	} else {
		echo "<tr bgcolor='#EEEEEE'>";
		$k=1;
	}

	$FormatedTranDate = ConvertSQLDate($myrow["TranDate"]);



	if ($myrow["Type"]==20){ /*Show a link to allow GL postings to be viewed but no link to allocate */

		if ($CompanyRecord['GLLink_Creditors']== True){
			If ($myrow["TotalAmount"]-$myrow["Allocated"]==0){
			/*The trans is settled so don't show option to hold */
				printf("<td>%s</td><td>%s</td><td>%s</td><td>%s</td><td ALIGN=RIGHT>%s</td><td ALIGN=RIGHT>%s</td><td ALIGN=RIGHT>%s</td><td ALIGN=LEFT>%s</td></td><td><a target='_blank' href='%s/GLTransInquiry.php?TypeID=%s&TransNo=%s'>View GL Postings</A></td></tr>", $myrow["TransNo"], $myrow["TypeName"], $myrow["SuppReference"], ConvertSQLDate($myrow["TranDate"]), number_format($myrow["TotalAmount"],2), number_format($myrow["Allocated"],2), number_format($myrow["TotalAmount"]-$myrow["Allocated"],2), $myrow["TransText"], $rootpath, $myrow["Type"], $myrow["TransNo"] );
			} else {
				printf("<td>%s</td><td>%s</td><td>%s</td><td>%s</td><td ALIGN=RIGHT>%s</td><td ALIGN=RIGHT>%s</td><td ALIGN=RIGHT>%s</td><td ALIGN=LEFT>%s</td><td><A HREF='%s?%sHoldType=%s&HoldTrans=%s&HoldStatus=%s&FromDate=%s'>%s</A></td><td><a target='_blank' href='%s/GLTransInquiry.php?TypeID=%s&TransNo=%s'>View GL Postings</A></td></tr>", $myrow["TransNo"], $myrow["TypeName"], $myrow["SuppReference"], ConvertSQLDate($myrow["TranDate"]), number_format($myrow["TotalAmount"],2), number_format($myrow["Allocated"],2), number_format($myrow["TotalAmount"]-$myrow["Allocated"],2), $myrow["TransText"], $_SERVER['PHP_SELF'], SID, $myrow["Type"], $myrow["TransNo"], $HoldValue, $_POST['TransAfterDate'], $HoldValue, $rootpath, $myrow["Type"], $myrow["TransNo"] );
			}

		} else {
			If ($myrow["TotalAmount"]-$myrow["Allocated"]==0){
			/*The trans is settled so don't show option to hold */
				printf("<td>%s</td><td>%s</td><td>%s</td><td>%s</td><td ALIGN=RIGHT>%s</td><td ALIGN=RIGHT>%s</td><td ALIGN=RIGHT>%s</td><td ALIGN=LEFT>%s</td></td></tr>", $myrow["TransNo"], $myrow["TypeName"], $myrow["SuppReference"], ConvertSQLDate($myrow["TranDate"]), number_format($myrow["TotalAmount"],2), number_format($myrow["Allocated"],2), number_format($myrow["TotalAmount"]-$myrow["Allocated"],2), $myrow["TransText"]);
			} else {
				printf("<td>%s</td><td>%s</td><td>%s</td><td>%s</td><td ALIGN=RIGHT>%s</td><td ALIGN=RIGHT>%s</td><td ALIGN=RIGHT>%s</td><td ALIGN=LEFT>%s</td><td><A HREF='%s?%sHoldType=%s&HoldTrans=%s&HoldStatus=%s&FromDate=%s'>%s</A></td></tr>", $myrow["TransNo"], $myrow["TypeName"], $myrow["SuppReference"], ConvertSQLDate($myrow["TranDate"]), number_format($myrow["TotalAmount"],2), number_format($myrow["Allocated"],2), number_format($myrow["TotalAmount"]-$myrow["Allocated"],2), $myrow["TransText"], $_SERVER['PHP_SELF'], SID, $myrow["Type"], $myrow["TransNo"], $HoldValue, $_POST['TransAfterDate'], $HoldValue);
			}
		}

	} else { /*its a credit note or a payment */
		if ($CompanyRecord['GLLink_Creditors']== True){
			printf("<td>%s</td><td>%s</td><td>%s</td><td>%s</td><td ALIGN=RIGHT>%s</td><td ALIGN=RIGHT>%s</td><td ALIGN=RIGHT>%s</td><td ALIGN=LEFT>%s</td><td><a href='%s/SupplierAllocations.php?%sAllocTrans=%s'>Allocations</A></td><td><a target='_blank' href='%s/GLTransInquiry.php?%sTypeID=%s&TransNo=%s'>View GL Postings</A></td></tr>", $myrow["TransNo"], $myrow["TypeName"], $myrow["SuppReference"], ConvertSQLDate($myrow["TranDate"]), number_format($myrow["TotalAmount"],2), number_format($myrow["Allocated"],2), number_format($myrow["TotalAmount"]-$myrow["Allocated"],2), $myrow["TransText"], $rootpath, SID, $myrow["ID"], $rootpath, SID, $myrow["Type"], $myrow["TransNo"] );
		} else { /*Not linked to GL */
			printf("<td>%s</td><td>%s</td><td>%s</td><td>%s</td><td ALIGN=RIGHT>%s</td><td ALIGN=RIGHT>%s</td><td ALIGN=RIGHT>%s</td><td ALIGN=LEFT>%s</td><td><a href='%s/SupplierAllocations.php?%sAllocTrans=%s'>Allocations</A></td></tr>", $myrow["TransNo"], $myrow["TypeName"], $myrow["SuppReference"], ConvertSQLDate($myrow["TranDate"]), number_format($myrow["TotalAmount"],2), number_format($myrow["Allocated"],2), number_format($myrow["TotalAmount"]-$myrow["Allocated"],2), $myrow["TransText"], $rootpath, SID, $myrow["ID"]);
		}
	}

	$j++;
	If ($j == 12){
		$j=1;
		echo $TableHeader;
	}
//end of page full new headings if
}
//end of while loop

echo "</TABLE>";
include("includes/footer.inc");

?>

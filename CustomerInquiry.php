<?php

include("includes/DateFunctions.inc");
include("includes/SQL_CommonFunctions.inc");
$title = "Customer Inquiry";

$PageSecurity = 1;

include("includes/session.inc");
include("includes/header.inc");

// always figure out the SQL required from the inputs available

if(!isset($_GET['CustomerID']) AND !isset($_SESSION['CustomerID'])){
	echo "<BR>To display the enquiry a customer must first be selected from the customer selection screen<BR><CENTER><A HREF='". $rootpath . "/SelectCustomer.php'>Select a customer to Inquire On</A></CENTER>";
	exit;
} else {
	if (isset($_GET['CustomerID'])){
		$_SESSION['CustomerID'] = $_GET['CustomerID'];
	}
	$CustomerID = $_SESSION['CustomerID'];
}


if (!isset($_POST['TransAfterDate'])) {
	$_POST['TransAfterDate'] = Date($DefaultDateFormat,Mktime(0,0,0,Date("m")-6,Date("d"),Date("Y")));
}

$SQL = "SELECT DebtorsMaster.Name, Currencies.Currency, PaymentTerms.Terms,
DebtorsMaster.CreditLimit, HoldReasons.DissallowInvoices, HoldReasons.ReasonDescription,

Sum(DebtorTrans.OvAmount + DebtorTrans.OvGST + DebtorTrans.OvFreight + DebtorTrans.OvDiscount
- DebtorTrans.Alloc) AS Balance,

Sum(IF (PaymentTerms.DaysBeforeDue > 0,
	CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DebtorTrans.TranDate)) >= PaymentTerms.DaysBeforeDue
	THEN DebtorTrans.OvAmount + DebtorTrans.OvGST + DebtorTrans.OvFreight + DebtorTrans.OvDiscount
	- DebtorTrans.Alloc ELSE 0 END,

	CASE WHEN TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(DebtorTrans.TranDate,
	INTERVAL 1 MONTH), INTERVAL (PaymentTerms.DayInFollowingMonth -
	DAYOFMONTH(DebtorTrans.TranDate)) DAY)) >= 0 THEN DebtorTrans.OvAmount + DebtorTrans.OvGST
	+ DebtorTrans.OvFreight + DebtorTrans.OvDiscount - DebtorTrans.Alloc ELSE 0 END

)) AS Due,


Sum(IF (PaymentTerms.DaysBeforeDue > 0,
	CASE WHEN TO_DAYS(Now()) - TO_DAYS(DebtorTrans.TranDate) > PaymentTerms.DaysBeforeDue
	AND TO_DAYS(Now()) - TO_DAYS(DebtorTrans.TranDate) >= (PaymentTerms.DaysBeforeDue + " .
	$PastDueDays1 . ") THEN DebtorTrans.OvAmount + DebtorTrans.OvGST + DebtorTrans.OvFreight
	+ DebtorTrans.OvDiscount - DebtorTrans.Alloc ELSE 0 END,

	CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(DebtorTrans.TranDate,
	INTERVAL 1  MONTH), INTERVAL (PaymentTerms.DayInFollowingMonth -
	DAYOFMONTH(DebtorTrans.TranDate)) DAY)) >= " . $PastDueDays1 . ")
	THEN DebtorTrans.OvAmount + DebtorTrans.OvGST + DebtorTrans.OvFreight + DebtorTrans.OvDiscount
	- DebtorTrans.Alloc ELSE 0 END

)) AS Overdue1,

Sum(IF (PaymentTerms.DaysBeforeDue > 0,
	CASE WHEN TO_DAYS(Now()) - TO_DAYS(DebtorTrans.TranDate) > PaymentTerms.DaysBeforeDue
	AND TO_DAYS(Now()) - TO_DAYS(DebtorTrans.TranDate) >= (PaymentTerms.DaysBeforeDue + " .
	$PastDueDays2 . ") THEN DebtorTrans.OvAmount + DebtorTrans.OvGST + DebtorTrans.OvFreight
	+ DebtorTrans.OvDiscount - DebtorTrans.Alloc ELSE 0 END,

	CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(DebtorTrans.TranDate,
	INTERVAL 1  MONTH), INTERVAL (PaymentTerms.DayInFollowingMonth
	- DAYOFMONTH(DebtorTrans.TranDate)) DAY)) >= " . $PastDueDays2 . ")
	THEN DebtorTrans.OvAmount + DebtorTrans.OvGST + DebtorTrans.OvFreight + DebtorTrans.OvDiscount
	- DebtorTrans.Alloc ELSE 0 END

)) AS Overdue2

FROM DebtorsMaster,
     PaymentTerms,
     HoldReasons,
     Currencies,
     DebtorTrans

WHERE
     DebtorsMaster.PaymentTerms = PaymentTerms.TermsIndicator
     AND DebtorsMaster.CurrCode = Currencies.CurrAbrev
     AND DebtorsMaster.HoldReason = HoldReasons.ReasonCode
     AND DebtorsMaster.DebtorNo = '" . $CustomerID . "'
     AND DebtorsMaster.DebtorNo = DebtorTrans.DebtorNo

GROUP BY
      DebtorsMaster.Name,
      Currencies.Currency,
      PaymentTerms.Terms,
      PaymentTerms.DaysBeforeDue,
      PaymentTerms.DayInFollowingMonth,
      DebtorsMaster.CreditLimit,
      HoldReasons.DissallowInvoices,
      HoldReasons.ReasonDescription";

$CustomerResult = DB_query($SQL,$db);

if (DB_error_no($db) !=0) {
	echo "The customer details could not be retrieved by the SQL because - " . DB_error_msg($db);

	if ($debug==1){
		echo "<BR>$SQL";
	}
	exit;
}

if (DB_num_rows($CustomerResult)==0){

	/*Because there is no balance - so just retrieve the header information about the customer - the choice is do one query to get the balance and transactions for those customers who have a balance and two queries for those who don't have a balance OR always do two queries - I opted for the former */

	$NIL_BALANCE = True;

	$SQL = "SELECT DebtorsMaster.Name, Currencies.Currency, PaymentTerms.Terms,
	DebtorsMaster.CreditLimit, HoldReasons.DissallowInvoices, HoldReasons.ReasonDescription
	FROM DebtorsMaster,
	     PaymentTerms,
	     HoldReasons,
	     Currencies

	WHERE
	     DebtorsMaster.PaymentTerms = PaymentTerms.TermsIndicator
	     AND DebtorsMaster.CurrCode = Currencies.CurrAbrev
	     AND DebtorsMaster.HoldReason = HoldReasons.ReasonCode
	     AND DebtorsMaster.DebtorNo = '" . $CustomerID . "'";

	$CustomerResult = DB_query($SQL,$db);

	if (DB_error_no($db) !=0) {
		echo "The customer details could not be retrieved by the SQL because - " . DB_error_msg($db);

		if ($debug==1){
			echo "<BR>$SQL";
		}
		exit;
	}

} else {
	$NIL_BALANCE = False;
}

$CustomerRecord = DB_fetch_array($CustomerResult);

if ($NIL_BALANCE==True){
	$CustomerRecord["Balance"]=0;
	$CustomerRecord["Due"]=0;
	$CustomerRecord["Overdue1"]=0;
	$CustomerRecord["Overdue2"]=0;
}

echo "<CENTER><FONT SIZE=4>" . $CustomerRecord["Name"] . " </FONT></B> - (All amounts stated in " . $CustomerRecord["Currency"] . ")</CENTER><BR><B><FONT COLOR=BLUE>Terms: " . $CustomerRecord["Terms"] . "<BR>Credit Limit: </B></FONT> " . number_format($CustomerRecord["CreditLimit"],0) . "  <B><FONT COLOR=BLUE>Credit Status:</B></FONT> " . $CustomerRecord["ReasonDescription"];

if ($CustomerRecord["DissallowInvoices"]!=0){
	echo "<BR><FONT COLOR=RED SIZE=4><B>ACCOUNT ON HOLD</FONT></B><BR>";
}

echo "<TABLE WIDTH=100% BORDER=1><TR><td class='tableheader'>Total Balance</TD><td class='tableheader'>Current</TD><td class='tableheader'>Now Due</TD><td class='tableheader'>" . $PastDueDays1 . "-" . $PastDueDays2 . " Days Overdue</TD><td class='tableheader'>Over " . $PastDueDays2 . " Days Overdue</TD></TR>";

echo "<TR><TD ALIGN=RIGHT>" . number_format($CustomerRecord["Balance"],2) . "</TD><TD ALIGN=RIGHT>" . number_format(($CustomerRecord["Balance"] - $CustomerRecord["Due"]),2) . "</TD><TD ALIGN=RIGHT>" . number_format(($CustomerRecord["Due"]-$CustomerRecord["Overdue1"]),2) . "</TD><TD ALIGN=RIGHT>" . number_format(($CustomerRecord["Overdue1"]-$CustomerRecord["Overdue2"]) ,2) . "</TD><TD ALIGN=RIGHT>" . number_format($CustomerRecord["Overdue2"],2) . "</TD></TR></TABLE>";

echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "' METHOD=POST>";
echo "Show all transactions after: <INPUT type=text name='TransAfterDate' Value='" . $_POST['TransAfterDate'] . "' MAXLENGTH =10 SIZE=10><INPUT TYPE=SUBMIT NAME='Refresh Inquiry' VALUE='Refresh Inquiry'></FORM>";

$DateAfterCriteria = FormatDateForSQL($_POST['TransAfterDate']);


$SQL = "SELECT SysTypes.TypeName, DebtorTrans.ID, DebtorTrans.Type, DebtorTrans.TransNo, DebtorTrans.BranchCode, DebtorTrans.TranDate, DebtorTrans.Reference, DebtorTrans.InvText, DebtorTrans.Order_, DebtorTrans.Rate, (DebtorTrans.OvAmount + DebtorTrans.OvGST + DebtorTrans.OvFreight + DebtorTrans.OvDiscount) AS TotalAmount, DebtorTrans.Alloc AS Allocated FROM DebtorTrans, SysTypes WHERE DebtorTrans.Type = SysTypes.TypeID AND DebtorTrans.DebtorNo = '" . $CustomerID . "' AND DebtorTrans.TranDate >= '$DateAfterCriteria' ORDER BY DebtorTrans.ID";

$TransResult = DB_query($SQL,$db);

if (DB_error_no($db) !=0) {
	echo "No transactions were returned by the SQL because - " . DB_error_msg($db);

	if ($debug==1){
		echo "<BR>$SQL";
	}
}

if (DB_num_rows($TransResult)==0){
	echo "There are no transactions to display since " . $_POST['TransAfterDate'];
	exit;
}
/*show a table of the invoices returned by the SQL */

echo "<TABLE CELLPADDING=2 COLSPAN=7>";

$tableheader = "<TR BGCOLOR =#800000><TD class='tableheader'>Type</TD><TD class='tableheader'>Number</TD><TD class='tableheader'>Date</TD><TD class='tableheader'>Branch</TD><TD class='tableheader'>Reference</TD><TD  class='tableheader'>Comments</TD><TD class='tableheader'>Order</TD><TD class='tableheader'>Total</TD><TD  class='tableheader'>Allocated</TD><TD class='tableheader'>Balance</TD></TR>";

echo $tableheader;


$CompanyRecord = ReadInCompanyRecord($db);

$j = 1;
$k=0; //row colour counter
while ($myrow=DB_fetch_array($TransResult)) {

	if ($k==1){
		echo "<tr bgcolor='#CCCCCC'>";
		$k=0;
	} else {
		echo "<tr bgcolor='#EEEEEE'>";
		$k=1;
	}

	$FormatedTranDate = ConvertSQLDate($myrow["TranDate"]);

	$base_formatstr = "<td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td width='200'>%s</td><td>%s</td><td ALIGN=RIGHT>%s</td><td ALIGN=RIGHT>%s</td><td ALIGN=RIGHT>%s</td>";
	$credit_invoice_str = "<td><a href='%s/Credit_Invoice.php?InvoiceNumber=%s'><IMG SRC='%s/credit.png' alt='Click to credit the invoice'></a></td>";
	$preview_invoice_str = "<td><a target='_blank' href='%s/PrintCustTrans.php?FromTransNo=%s&InvOrCredit=Invoice'><IMG SRC='%s/preview.png' alt='Click to preview the invoice'></a></td><td><a target='_blank' href='%s/EmailCustTrans.php?FromTransNo=%s&InvOrCredit=Invoice'><IMG SRC='%s/email.png' alt='Click to email the invoice'></a></td>";
	$preview_credit_str = "<td><a target='_blank' href='%s/PrintCustTrans.php?FromTransNo=%s&InvOrCredit=Credit'><IMG SRC='%s/preview.png' alt='Click to preview the credit note'></a></td><td><a target='_blank' href='%s/EmailCustTrans.php?FromTransNo=%s&InvOrCredit=Credit'><IMG SRC='%s/email.png' alt='Click to email the credit note'></a></td>";

	if (in_array(3,$SecurityGroups[$_SESSION['AccessLevel']]) && $myrow["Type"]==10){ /*Show a link to allow an invoice to be credited */

		if ($CompanyRecord['GLLink_Debtors']== 1 AND in_array(8,$SecurityGroups[$_SESSION['AccessLevel']])){
			printf("$base_formatstr$credit_invoice_str$preview_invoice_str<td><A HREF='%s/GLTransInquiry.php?%sTypeID=%s&TransNo=%s'>GL<A></td></tr>", $myrow["TypeName"], $myrow["TransNo"], ConvertSQLDate($myrow["TranDate"]), $myrow["BranchCode"], $myrow["Reference"], $myrow["InvText"], $myrow["Order_"], number_format($myrow["TotalAmount"],2), number_format($myrow["Allocated"],2), number_format($myrow["TotalAmount"]-$myrow["Allocated"],2), $rootpath, $myrow["TransNo"], $rootpath."/css/".$theme."/images", $rootpath, $myrow["TransNo"], $rootpath."/css/".$theme."/images",  $rootpath, $myrow['TransNo'],$rootpath."/css/".$theme."/images", $rootpath, SID, $myrow['Type'], $myrow['TransNo']);
		} else {
			printf("$base_formatstr$credit_invoice_str$preview_invoice_str</tr>", $myrow["TypeName"], $myrow["TransNo"], ConvertSQLDate($myrow["TranDate"]), $myrow["BranchCode"], $myrow["Reference"], $myrow["InvText"], $myrow["Order_"], number_format($myrow["TotalAmount"],2), number_format($myrow["Allocated"],2), number_format($myrow["TotalAmount"]-$myrow["Allocated"],2), $rootpath, $myrow["TransNo"], $rootpath."/css/".$theme."/images", $rootpath, $myrow["TransNo"], $rootpath."/css/".$theme."/images", $rootpath, $myrow['TransNo'],$rootpath."/css/".$theme."/images");
		}

	} elseif($myrow["Type"]==10) { /*its an invoice but not high enough priveliges to credit it */

		printf("$base_formatstr$preview_invoice_str</tr>", $myrow["TypeName"], $myrow["TransNo"], ConvertSQLDate($myrow["TranDate"]), $myrow["BranchCode"], $myrow["Reference"], $myrow["InvText"], $myrow["Order_"], number_format($myrow["TotalAmount"],2), number_format($myrow["Allocated"],2), number_format($myrow["TotalAmount"]-$myrow["Allocated"],2), $rootpath, $myrow["TransNo"], $rootpath."/css/".$theme."/images", $rootpath."/css/".$theme."/images",  $rootpath, $myrow['TransNo'],$rootpath."/css/".$theme."/images");

	} elseif ($myrow["Type"]==11) { /*its a credit note */
		if ($CompanyRecord['GLLink_Debtors']== 1 AND in_array(8,$SecurityGroups[$_SESSION['AccessLevel']])){
			printf("$base_formatstr$preview_credit_str<td><a href='%s/CustomerAllocations.php?AllocTrans=%s'><IMG SRC='%s/allocation.png' alt='Click to allocate funds'></a></td><td><A HREF='%s/GLTransInquiry.php?%sTypeID=%s&TransNo=%s'>GL<A></td></tr>", $myrow["TypeName"], $myrow["TransNo"], ConvertSQLDate($myrow["TranDate"]), $myrow["BranchCode"], $myrow["Reference"], $myrow["InvText"], $myrow["Order_"], number_format($myrow["TotalAmount"],2), number_format($myrow["Allocated"],2), number_format($myrow["TotalAmount"]-$myrow["Allocated"],2), $rootpath, $myrow["TransNo"], $rootpath."/css/".$theme."/images",  $rootpath, $myrow['TransNo'],$rootpath."/css/".$theme."/images", $rootpath, $myrow["ID"], $rootpath."/css/".$theme."/images",  $rootpath, SID, $myrow['Type'], $myrow['TransNo']);
		} else {
			printf("$base_formatstr$preview_credit_str<td><a href='%s/CustomerAllocations.php?AllocTrans=%s'><IMG SRC='%s/allocation.png' alt='Click to allocate funds'></a></td></tr>", $myrow["TypeName"], $myrow["TransNo"], ConvertSQLDate($myrow["TranDate"]), $myrow["BranchCode"], $myrow["Reference"], $myrow["InvText"], $myrow["Order_"], number_format($myrow["TotalAmount"],2), number_format($myrow["Allocated"],2), number_format($myrow["TotalAmount"]-$myrow["Allocated"],2), $rootpath, $myrow["TransNo"], $rootpath."/css/".$theme."/images",  $rootpath, $myrow['TransNo'],$rootpath."/css/".$theme."/images", $rootpath, $myrow["ID"], $rootpath."/css/".$theme."/images");
		}
	} elseif ($myrow["Type"]==12 AND $myrow['TotalAmount']<0) { /*its a receipt  which could have an allocation*/
		if ($CompanyRecord['GLLink_Debtors']== 1 AND in_array(8,$SecurityGroups[$_SESSION['AccessLevel']])){
			printf("$base_formatstr<td><a href='%s/CustomerAllocations.php?AllocTrans=%s'>Allocation</a></td><td><A HREF='%s/GLTransInquiry.php?%sTypeID=%s&TransNo=%s'>Whole<BR>Batch GL<A></td></tr>", $myrow["TypeName"], $myrow["TransNo"], ConvertSQLDate($myrow["TranDate"]), $myrow["BranchCode"], $myrow["Reference"], $myrow["InvText"], $myrow["Order_"], number_format($myrow["TotalAmount"],2), number_format($myrow["Allocated"],2), number_format($myrow["TotalAmount"]-$myrow["Allocated"],2), $rootpath, $myrow["ID"], $rootpath, SID, $myrow['Type'], $myrow['TransNo']);
		} else {
			printf("$base_formatstr<td><a href='%s/CustomerAllocations.php?AllocTrans=%s'>Allocation</a></td></tr>", $myrow["TypeName"], $myrow["TransNo"], ConvertSQLDate($myrow["TranDate"]), $myrow["BranchCode"], $myrow["Reference"], $myrow["InvText"], $myrow["Order_"], number_format($myrow["TotalAmount"],2), number_format($myrow["Allocated"],2), number_format($myrow["TotalAmount"]-$myrow["Allocated"],2), $rootpath, $myrow["ID"]);
		}
	} elseif ($myrow["Type"]==12 AND $myrow['TotalAmount']>0) { /*its a negative receipt */
		if ($CompanyRecord['GLLink_Debtors']== 1 AND in_array(8,$SecurityGroups[$_SESSION['AccessLevel']])){
			printf("$base_formatstr<td><td><A HREF='%s/GLTransInquiry.php?%sTypeID=%s&TransNo=%s'>Whole<BR>Batch GL<A></td></tr>", $myrow["TypeName"], $myrow["TransNo"], ConvertSQLDate($myrow["TranDate"]), $myrow["BranchCode"], $myrow["Reference"], $myrow["InvText"], $myrow["Order_"], number_format($myrow["TotalAmount"],2), number_format($myrow["Allocated"],2), number_format($myrow["TotalAmount"]-$myrow["Allocated"],2), $rootpath, SID, $myrow['Type'], $myrow['TransNo']);
		} else {
			printf("$base_formatstr<td></tr>", $myrow["TypeName"], $myrow["TransNo"], ConvertSQLDate($myrow["TranDate"]), $myrow["BranchCode"], $myrow["Reference"], $myrow["InvText"], $myrow["Order_"], number_format($myrow["TotalAmount"],2), number_format($myrow["Allocated"],2), number_format($myrow["TotalAmount"]-$myrow["Allocated"],2));
		}
	} else {
		if ($CompanyRecord['GLLink_Debtors']== 1 AND in_array(8,$SecurityGroups[$_SESSION['AccessLevel']])){
			printf("$base_formatstr<td><A HREF='%s/GLTransInquiry.php?%sTypeID=%s&TransNo=%s'>GL<A></td></tr>", $myrow["TypeName"], $myrow["TransNo"], ConvertSQLDate($myrow["TranDate"]), $myrow["BranchCode"], $myrow["Reference"], $myrow["InvText"], $myrow["Order_"], number_format($myrow["TotalAmount"],2), number_format($myrow["Allocated"],2), number_format($myrow["TotalAmount"]-$myrow["Allocated"],2), $rootpath, SID, $myrow['Type'], $myrow['TransNo']);
		} else {
			printf("$base_formatstr</tr>", $myrow["TypeName"], $myrow["TransNo"], ConvertSQLDate($myrow["TranDate"]), $myrow["BranchCode"], $myrow["Reference"], $myrow["InvText"], $myrow["Order_"], number_format($myrow["TotalAmount"],2), number_format($myrow["Allocated"],2), number_format($myrow["TotalAmount"]-$myrow["Allocated"],2));
		}
	}

	$j++;
	If ($j == 12){
		$j=1;
		echo $tableheader;
	}
	//end of page full new headings if
}
//end of while loop

echo "</table>";
include("includes/footer.inc");
?>


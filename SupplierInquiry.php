<?php
/* $Revision: 1.4 $ */

include('includes/DateFunctions.inc');
include('includes/SQL_CommonFunctions.inc');

$PageSecurity=2;

include('includes/session.inc');

$title = _('Supplier Inquiry');

include('includes/header.inc');

// always figure out the SQL required from the inputs available

if(!isset($_GET['SupplierID']) AND !isset($_SESSION['SupplierID'])){
	echo '<BR>' . _('To display the enquiry a Supplier must first be selected from the Supplier selection screen') . 
		  "<BR><CENTER><A HREF='". $rootpath . "/SelectSupplier.php'>" . _('Select a Supplier to Inquire On') . '</A></CENTER>';
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


$SQL = 'SELECT Suppliers.SuppName, Currencies.Currency, PaymentTerms.Terms,

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
	AND TO_DAYS(Now()) - TO_DAYS(SuppTrans.TranDate) >= (PaymentTerms.DaysBeforeDue + ' .
	$PastDueDays1 . ') THEN SuppTrans.OvAmount + SuppTrans.OvGST - SuppTrans.Alloc ELSE 0 END,

	CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(SuppTrans.TranDate,
	INTERVAL 1  MONTH), INTERVAL (PaymentTerms.DayInFollowingMonth -
	DAYOFMONTH(SuppTrans.TranDate)) DAY)) >= ' . $PastDueDays1 . ')
	THEN SuppTrans.OvAmount + SuppTrans.OvGST - SuppTrans.Alloc ELSE 0 END

)) AS Overdue1,

Sum(IF (PaymentTerms.DaysBeforeDue > 0,
	CASE WHEN TO_DAYS(Now()) - TO_DAYS(SuppTrans.TranDate) > PaymentTerms.DaysBeforeDue
	AND TO_DAYS(Now()) - TO_DAYS(SuppTrans.TranDate) >= (PaymentTerms.DaysBeforeDue + ' .
	$PastDueDays2 . ') THEN SuppTrans.OvAmount + SuppTrans.OvGST - SuppTrans.Alloc ELSE 0 END,

	CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(SuppTrans.TranDate,
	INTERVAL 1  MONTH), INTERVAL (PaymentTerms.DayInFollowingMonth
	- DAYOFMONTH(SuppTrans.TranDate)) DAY)) >= ' . $PastDueDays2 . ")
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

$ErrMsg = _('The supplier details could not be retrieved by the SQL because');
$DbgMsg = _('The SQL that failed was');

$SupplierResult = DB_query($SQL, $db, $ErrMsg, $DbgMsg);

if (DB_num_rows($SupplierResult) == 0){

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

	$ErrMsg = _('The supplier details could not be retrieved by the SQL because');
	$DbgMsg = _('The SQL that failed was');

	$SupplierResult = DB_query($SQL, $db, $ErrMsg, $DbgMsg);

} else {

	$NIL_BALANCE = False;

}

$SupplierRecord = DB_fetch_array($SupplierResult);

if ($NIL_BALANCE == True){
	$SupplierRecord['Balance'] = 0;
	$SupplierRecord['Due'] = 0;
	$SupplierRecord['Overdue1'] = 0;
	$SupplierRecord['Overdue2'] = 0;
}

echo '<CENTER><FONT SIZE=4>' . $SupplierRecord['SuppName'] . ' </FONT></B> - (' . _('All amounts stated in') . 
	  ' ' . $SupplierRecord['Currency'] . ')</CENTER><BR><B><FONT COLOR=BLUE>' . _('Terms') . ': ' . $SupplierRecord['Terms'];

if (isset($_GET['HoldType']) AND isset($_GET['HoldTrans'])){

	if ($_GET['HoldStatus'] == 'Hold'){
		$SQL = 'UPDATE SuppTrans SET Hold=1 WHERE Type=' . $_GET['HoldType'] . ' AND TransNo=' . $_GET['HoldTrans'];
	} elseif ($_GET['HoldStatus'] == 'Release'){
		$SQL = 'UPDATE SuppTrans SET Hold=0 WHERE Type=' . $_GET['HoldType'] . ' AND TransNo=' . $_GET['HoldTrans'];
	}

	$ErrMsg = _('The Supplier Transactions could not be updated because');
	$DbgMsg = _('The SQL that failed was');

	$UpdateResult = DB_query($SQL, $db, $ErrMsg, $DbgMsg);

}

echo "<TABLE WIDTH=100% BORDER=1><TR><TD CLASS='tableheader'>" . _('Total Balance') . 
	  "</TD><TD CLASS='tableheader'>" . _('Current') . 
	  "</TD><TD CLASS='tableheader'>" . _('Now Due') . 
	  "</TD><TD CLASS='tableheader'>" . $PastDueDays1 . '-' . $PastDueDays2 . 
	  ' ' . _('Days Overdue') . 
	  "</TD><TD CLASS='tableheader'>" . _('Over') . ' ' . $PastDueDays2 . ' ' . _('Days Overdue') . '</TD></TR>';

echo '<TR><TD ALIGN=RIGHT>' . number_format($SupplierRecord['Balance'],2) . 
	  '</TD><TD ALIGN=RIGHT>' . number_format(($SupplierRecord['Balance'] - $SupplierRecord['Due']),2) . 
	  '</TD><TD ALIGN=RIGHT>' . number_format(($SupplierRecord['Due']-$SupplierRecord['Overdue1']),2) . 
	  '</TD><TD ALIGN=RIGHT>' . number_format(($SupplierRecord['Overdue1']-$SupplierRecord['Overdue2']) ,2) . 
	  '</TD><TD ALIGN=RIGHT>' . number_format($SupplierRecord['Overdue2'],2) . '</TD></TR></TABLE>';

echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "?" . SID . "' METHOD=POST>";
echo _('Show all transactions after') . ': ' ."<INPUT TYPE=text NAME='TransAfterDate' VALUE='" . 
	  $_POST['TransAfterDate'] . "' MAXLENGTH =10 SIZE=10><INPUT TYPE=SUBMIT NAME='Refresh Inquiry' VALUE='" . _('Refresh Inquiry') . "'></FORM>";

$DateAfterCriteria = FormatDateForSQL($_POST['TransAfterDate']);

$SQL = "SELECT SuppTrans.ID, SysTypes.TypeName, SuppTrans.Type, SuppTrans.TransNo, SuppTrans.TranDate, 
		  SuppTrans.SuppReference, SuppTrans.Rate, (SuppTrans.OvAmount + SuppTrans.OvGST) AS TotalAmount, 
		  SuppTrans.Alloc AS Allocated, SuppTrans.Hold, SuppTrans.Settled, SuppTrans.TransText 
		  FROM SuppTrans, SysTypes 
		  WHERE SuppTrans.Type = SysTypes.TypeID 
		  AND SuppTrans.SupplierNo = '" . $SupplierID . "' AND SuppTrans.TranDate >= '$DateAfterCriteria' 
		  ORDER BY SuppTrans.TranDate";

$ErrMsg = _('No transactions were returned by the SQL because');
$DbgMsg = _('The SQL that failed was');

$TransResult = DB_query($SQL, $db, $ErrMsg, $DbgMsg);

if (DB_num_rows($TransResult) == 0){
	echo '<P>' . _('There are no transactions to display since') . ' ' . $_POST['TransAfterDate'];
	exit;
}

/*show a table of the transactions returned by the SQL */

$CompanyRecord = ReadInCompanyRecord($db);


echo '<TABLE CELLPADDING=2 COLSPAN=7>';
$TableHeader = "<TR><TD CLASS='tableheader'>" . _('Trans') . ' #' . 
					"</TD><TD CLASS='tableheader'>" . _('Type') . 
					"</TD><TD CLASS='tableheader'>" . _('Supplier Invoice') . ' #' . 
					"</TD><TD CLASS='tableheader'>" . _('Date') . 
					"</TD><TD CLASS='tableheader'>" . _('Total') . 
					"</TD><TD CLASS='tableheader'>" . _('Allocated') . 
					"</TD><TD CLASS='tableheader'>" . _('Balance') . 
					"</TD><TD CLASS='tableheader'>" . _('Comments') . '</TD></TR>';

echo $TableHeader;

$j = 1;
$k = 0; //row colour counter
while ($myrow=DB_fetch_array($TransResult)) {

	if ($myrow['Hold'] == 0 AND $myrow['Settled'] == 0){
		$HoldValue = 'Hold';
	} elseif ($myrow['Settled'] == 1) {
		$HoldValue = '';
	}else {
		$HoldValue = 'Release';
	}
	if ($myrow['Hold'] == 1){
		echo "<TR BGCOLOR='#DD99BB'>";
	}elseif ($k == 1){
		echo "<TR BGCOLOR='#CCCCCC'>";
		$k = 0;
	} else {
		echo "<TR BGCOLOR='#EEEEEE'>";
		$k = 1;
	}

	$FormatedTranDate = ConvertSQLDate($myrow['TranDate']);

	if ($myrow['Type'] == 20){ /*Show a link to allow GL postings to be viewed but no link to allocate */

		if ($CompanyRecord['GLLink_Creditors'] == True){
			If ($myrow['TotalAmount'] - $myrow['Allocated'] == 0){

			/*The trans is settled so don't show option to hold */

				printf("<TD>%s</TD><TD>%s</TD><TD>%s</TD><TD>%s</TD><TD ALIGN=RIGHT>%s</TD>
						  <TD ALIGN=RIGHT>%s</TD><TD ALIGN=RIGHT>%s</TD><TD ALIGN=LEFT>%s</TD></TD>
						  <TD><A TARGET='_blank' HREF='%s/GLTransInquiry.php?TypeID=%s&TransNo=%s'>" . 
						  _('View GL Postings') . '</A></td></tr>', 
						  $myrow['TransNo'], $myrow['TypeName'], $myrow['SuppReference'], 
						  ConvertSQLDate($myrow['TranDate']), number_format($myrow["TotalAmount"],2), 
						  number_format($myrow['Allocated'],2), number_format($myrow['TotalAmount']-$myrow['Allocated'],2), 
						  $myrow['TransText'], $rootpath, $myrow['Type'], $myrow['TransNo'] );
						  
			} else {
			
				printf("<TD>%s</TD>
					<TD>%s</TD>
					<TD>%s</TD>
					<TD>%s</TD>
					<TD ALIGN=RIGHT>%s</TD>
					<TD ALIGN=RIGHT>%s</TD>
					<TD ALIGN=RIGHT>%s</TD>
					<TD ALIGN=LEFT>%s</TD>
					<TD><A HREF='%s?%s&HoldType=%s&HoldTrans=%s&HoldStatus=%s&FromDate=%s'>%s</A></TD>
					<TD><A TARGET='_blank' HREF='%s/GLTransInquiry.php?TypeID=%s&TransNo=%s'>" .
						  _('View GL Postings') . '</A></TD></TR>',
					$myrow['TransNo'],
					$myrow['TypeName'],
					$myrow['SuppReference'],
					ConvertSQLDate($myrow['TranDate']),
					number_format($myrow['TotalAmount'],2),
					number_format($myrow['Allocated'],2),
					number_format($myrow['TotalAmount'] - $myrow['Allocated'],2),
					$myrow['TransText'],
					$_SERVER['PHP_SELF'],
					SID,
					$myrow['Type'],
					$myrow['TransNo'],
					$HoldValue,
					$_POST['TransAfterDate'],
					$HoldValue,
					$rootpath,
					$myrow['Type'],
					$myrow['TransNo'] );

			}

		} else {

			if ($myrow['TotalAmount'] - $myrow['Allocated'] == 0){

			/*The trans is settled so don't show option to hold */

				printf("<TD>%s</TD>
					<TD>%s</TD>
					<TD>%s</TD>
					<TD>%s</TD>
					<TD ALIGN=RIGHT>%s</TD>
					<TD ALIGN=RIGHT>%s</TD>
					<TD ALIGN=RIGHT>%s</TD>
					<TD ALIGN=LEFT>%s</TD>
					</TD></TR>",
					$myrow['TransNo'],
					$myrow['TypeName'],
					$myrow['SuppReference'],
					ConvertSQLDate($myrow['TranDate']),
					number_format($myrow['TotalAmount'],2),
					number_format($myrow['Allocated'],2),
					number_format($myrow['TotalAmount'] - $myrow['Allocated'],2),
					$myrow['TransText']);

			} else {

				printf("<TD>%s</TD>
					<TD>%s</TD>
					<TD>%s</TD>
					<TD>%s</TD>
					<TD ALIGN=RIGHT>%s</TD>
					<TD ALIGN=RIGHT>%s</TD>
					<TD ALIGN=RIGHT>%s</TD>
					<TD ALIGN=LEFT>%s</TD>
					<TD><A HREF='%s?%sHoldType=%s&HoldTrans=%s&HoldStatus=%s&FromDate=%s'>%s</A></TD></TR>",
					$myrow['TransNo'],
					$myrow['TypeName'],
					$myrow['SuppReference'],
					ConvertSQLDate($myrow['TranDate']),
					number_format($myrow['TotalAmount'],2),
					number_format($myrow['Allocated'],2),
					number_format($myrow['TotalAmount']-$myrow['Allocated'],2),
					$myrow['TransText'],
					$_SERVER['PHP_SELF'],
					SID,
					$myrow['Type'],
					$myrow['TransNo'],
					$HoldValue,
					$_POST['TransAfterDate'],
					$HoldValue);

			}
		}

	} else { /*its a credit note or a payment */

		if ($CompanyRecord['GLLink_Creditors'] == True){

			printf("<TD>%s</TD>
				<TD>%s</TD>
				<TD>%s</TD>
				<TD>%s</TD>
				<TD ALIGN=RIGHT>%s</TD>
				<TD ALIGN=RIGHT>%s</TD>
				<TD ALIGN=RIGHT>%s</TD>
				<TD ALIGN=LEFT>%s</TD>
				<TD><A HREF='%s/SupplierAllocations.php?%sAllocTrans=%s'>" .
					  _('Allocations') . "</A></TD>
				<TD><A TARGET='_blank' HREF='%s/GLTransInquiry.php?%sTypeID=%s&TransNo=%s'>" .
					  _('View GL Postings') . '</A></TD>
				</TR>',
				$myrow['TransNo'],
				$myrow['TypeName'],
				$myrow['SuppReference'],
				ConvertSQLDate($myrow['TranDate']),
				number_format($myrow['TotalAmount'],2),
				number_format($myrow["Allocated"],2),
				number_format($myrow["TotalAmount"]-$myrow["Allocated"],2),
				$myrow["TransText"],
				$rootpath,
				SID,
				$myrow["ID"],
				$rootpath,
				SID,
				$myrow["Type"],
				$myrow["TransNo"] );

		} else { /*Not linked to GL */

			printf("<TD>%s</TD>
				<TD>%s</TD>
				<TD>%s</TD>
				<TD>%s</TD>
				<TD ALIGN=RIGHT>%s</TD>
				<TD ALIGN=RIGHT>%s</TD>
				<TD ALIGN=RIGHT>%s</TD>
				<TD ALIGN=LEFT>%s</TD>
				<TD><A HREF='%s/SupplierAllocations.php?%sAllocTrans=%s'>" . _('Allocations') . '</A></TD>
				</TR>',
				$myrow['TransNo'],
				$myrow['TypeName'],
				$myrow['SuppReference'],
				ConvertSQLDate($myrow['TranDate']),
				number_format($myrow['TotalAmount'],2),
				number_format($myrow['Allocated'],2),
				number_format($myrow['TotalAmount'] - $myrow['Allocated'],2),
				$myrow['TransText'],
				$rootpath,
				SID,
				$myrow['ID']);

		}
	}

	$j++;
	if ($j == 12){
		$j = 1;
		echo $TableHeader;
	}

//end of page full new headings if

}

//end of while loop

echo '</TABLE>';
include('includes/footer.inc');

?>

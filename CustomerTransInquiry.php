<?php
/* $Revision: 1.4 $ */
$title = _('Customer Transactions Inquiry');

$PageSecurity = 2;

include("includes/session.inc");
include("includes/header.inc");
include("includes/DateFunctions.inc");


echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "' METHOD=POST>";

echo '<CENTER><TABLE CELLPADDING=2><TR>';

echo '<TD>' . _('Type:') . "</TD><TD><SELECT name='TransType'> ";

$sql = "SELECT TypeID, TypeName FROM SysTypes WHERE TypeID >= 10 AND TypeID <= 14";
$resultTypes = DB_query($sql,$db);

while ($myrow=DB_fetch_array($resultTypes)){
	if (isset($_POST['TransType'])){
		if ($myrow["TypeID"] == $_POST['TransType']){
		     echo "<OPTION SELECTED Value='" . $myrow["TypeID"] . "'>" . $myrow["TypeName"];
		} else {
		     echo "<OPTION Value='" . $myrow["TypeID"] . "'>" . $myrow["TypeName"];
		}
	} else {
		     echo "<OPTION Value='" . $myrow["TypeID"] . "'>" . $myrow["TypeName"];
	}
}
echo "</SELECT></TD>";

if (!isset($_POST['FromDate'])){
	$_POST['FromDate']=Date($DefaultDateFormat, mktime(0,0,0,Date("m"),1,Date("Y")));
}
if (!isset($_POST['ToDate'])){
	$_POST['ToDate'] = Date($DefaultDateFormat);
}
echo '<TD>' . _('From:') . "</TD><TD><INPUT TYPE=TEXT NAME='FromDate' MAXLENGTH=10 SIZE=11 VALUE=" . $_POST['FromDate'] . '></TD>';
echo '<TD>' . _('To:') . "</TD><TD><INPUT TYPE=TEXT NAME='ToDate' MAXLENGTH=10 SIZE=11 VALUE=" . $_POST['ToDate'] . '></TD>';

echo "</TR></TABLE><INPUT TYPE=SUBMIT NAME='ShowResults' VALUE='" . _('Show Transactions') . "'>";
echo '<HR>';

echo '</FORM></CENTER>';

if (isset($_POST['ShowResults'])){
   $SQL_FromDate = FormatDateForSQL($_POST['FromDate']);
   $SQL_ToDate = FormatDateForSQL($_POST['ToDate']);
   $sql = "SELECT TransNo,
   		TranDate,
		DebtorTrans.DebtorNo,
		BranchCode,
		Reference,
		InvText,
		Order_,
		Rate,
		OvAmount+OvGST+OvFreight+OvDiscount AS TotalAmt,
		CurrCode
	FROM DebtorTrans
		INNER JOIN DebtorsMaster ON DebtorTrans.DebtorNo=DebtorsMaster.DebtorNo
	WHERE ";

   $sql = $sql . "TranDate >='" . $SQL_FromDate . "' AND TranDate <= '" . $SQL_ToDate . "' AND Type = " . $_POST['TransType'] . " ORDER BY ID";

   $TransResult = DB_query($sql, $db,$ErrMsg,$DbgMsg);
   $ErrMsg = _('The customer transactions for the selected criteria could not be retrieved because') . ' - ' . DB_error_msg($db);
   $DbgMsg =  _('The SQL that failed was');

   echo '<TABLE CELLPADDING=2 BORDER=2>';

   $tableheader = "<TR><TD class='tableheader'>" . _('Number') . "</TD>
				<TD class='tableheader'>" . _('Date') . "</TD>
				<TD class='tableheader'>" . _('Customer') . "</TD>
				<TD class='tableheader'>" . _('Branch') . "</TD>
				<TD class='tableheader'>" . _('Reference') . "</TD>
				<TD class='tableheader'>" . _('Comments') . "</TD>
				<TD class='tableheader'>" . _('Order') . "</TD>
				<TD class='tableheader'>" . _('Ex Rate') . "</TD>
				<TD class='tableheader'>" . _('Amount') . "</TD>
				<TD class='tableheader'>" . _('Currency') . '</TD></TR>';
	echo $tableheader;

	$RowCounter = 1;
	$k = 0; //row colour counter

	while ($myrow=DB_fetch_array($TransResult)) {

		if ($k==1){
			echo "<tr bgcolor='#CCCCCC'>";
			$k=0;
		} else {
			echo "<tr bgcolor='#EEEEEE'>";
			$k++;
		}

		$format_base = "<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td width='200'>%s</td>
				<td>%s</td>
				<td ALIGN=RIGHT>%s</td>
				<td ALIGN=RIGHT>%s</td>
				<td>%s</td>";

		if ($_POST['TransType']==10){ /* invoices */

			printf("$format_base<td><a target='_blank' href='%s/PrintCustTrans.php?FromTransNo=%s&InvOrCredit=Invoice'><IMG SRC='%s' alt='" . _('Click to preview the invoice') . "'></a></td></tr>", $myrow['TransNo'], ConvertSQLDate($myrow['TranDate']),$myrow['DebtorNo'], $myrow['BranchCode'], $myrow['Reference'], $myrow['InvText'], $myrow['Order_'], $myrow['Rate'], number_format($myrow['TotalAmt'],2),$myrow['CurrCode'], $rootpath, $myrow['TransNo'], $rootpath.'/css/'.$theme.'/images/preview.png');
		} elseif ($_POST['TransType']==11){ /* credit notes */
			printf("$format_base<td><a target='_blank' href='%s/PrintCustTrans.php?FromTransNo=%s&InvOrCredit=Credit'><IMG SRC='%s' alt='" . _('Click to preview the credit') . "'></a></td></tr>", $myrow['TransNo'], ConvertSQLDate($myrow['TranDate']),$myrow['DebtorNo'], $myrow['BranchCode'], $myrow['Reference'], $myrow['InvText'], $myrow['Order_'], $myrow['Rate'], number_format($myrow['TotalAmt'],2),$myrow['CurrCode'], $rootpath, $myrow['TransNo'],$rootpath.'/css/'.$theme.'/images/preview.png');
		} else {  /* otherwise */
			printf("$format_base</tr>", $myrow["TransNo"], ConvertSQLDate($myrow["TranDate"]),$myrow["DebtorNo"], $myrow["BranchCode"], $myrow["Reference"], $myrow["InvText"], $myrow["Order_"], $myrow["Rate"], number_format($myrow["TotalAmt"],2),$myrow["CurrCode"]);
		}

		$RowCounter++;
		If ($RowCounter == 12){
			$RowCounter=1;
			echo $tableheader;
		}
	//end of page full new headings if
	}
	//end of while loop

 echo '</TABLE>';
}

include("includes/footer.inc");

?>
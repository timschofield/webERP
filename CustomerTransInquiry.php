<?php

/* $Revision: 1.12 $ */

$PageSecurity = 2;

include('includes/session.inc');
$title = _('Customer Transactions Inquiry');
include('includes/header.inc');


echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "' METHOD=POST>";

echo '<CENTER><TABLE CELLPADDING=2><TR>';

echo '<TD>' . _('Type') . ":</TD><TD><SELECT name='TransType'> ";

$sql = 'SELECT typeid, typename FROM systypes WHERE typeid >= 10 AND typeid <= 14';
$resultTypes = DB_query($sql,$db);

echo "<OPTION Value='All'> All";
while ($myrow=DB_fetch_array($resultTypes)){
	if (isset($_POST['TransType'])){
		if ($myrow['typeid'] == $_POST['TransType']){
		     echo "<OPTION SELECTED Value='" . $myrow['typeid'] . "'>" . $myrow['typename'];
		} else {
		     echo "<OPTION Value='" . $myrow['typeid'] . "'>" . $myrow['typename'];
		}
	} else {
		     echo "<OPTION Value='" . $myrow['typeid'] . "'>" . $myrow['typename'];
	}
}
echo '</SELECT></TD>';

if (!isset($_POST['FromDate'])){
	$_POST['FromDate']=Date($_SESSION['DefaultDateFormat'], mktime(0,0,0,Date('m'),1,Date('Y')));
}
if (!isset($_POST['ToDate'])){
	$_POST['ToDate'] = Date($_SESSION['DefaultDateFormat']);
}
echo '<TD>' . _('From') . ":</TD><TD><INPUT TYPE=TEXT NAME='FromDate' MAXLENGTH=10 SIZE=11 VALUE=" . $_POST['FromDate'] . '></TD>';
echo '<TD>' . _('To') . ":</TD><TD><INPUT TYPE=TEXT NAME='ToDate' MAXLENGTH=10 SIZE=11 VALUE=" . $_POST['ToDate'] . '></TD>';

echo "</TR></TABLE><INPUT TYPE=SUBMIT NAME='ShowResults' VALUE='" . _('Show Transactions') . "'>";
echo '<HR>';

echo '</FORM></CENTER>';

if (isset($_POST['ShowResults']) && $_POST['TransType'] != ''){
   $SQL_FromDate = FormatDateForSQL($_POST['FromDate']);
   $SQL_ToDate = FormatDateForSQL($_POST['ToDate']);
   $sql = "SELECT transno,
   		trandate,
		debtortrans.debtorno,
		branchcode,
		reference,
		invtext,
		order_,
		rate,
		ovamount+ovgst+ovfreight+ovdiscount as totalamt,
		currcode,
		typename
	FROM debtortrans
		INNER JOIN debtorsmaster ON debtortrans.debtorno=debtorsmaster.debtorno
		INNER JOIN systypes ON debtortrans.type = systypes.typeid
	WHERE ";

   $sql = $sql . "trandate >='" . $SQL_FromDate . "' AND trandate <= '" . $SQL_ToDate . "'";
	if  ($_POST['TransType']!='All')  {
		$sql .= " AND type = " . $_POST['TransType'];
	}
	$sql .=  " ORDER BY id";

   $TransResult = DB_query($sql, $db,$ErrMsg,$DbgMsg);
   $ErrMsg = _('The customer transactions for the selected criteria could not be retrieved because') . ' - ' . DB_error_msg($db);
   $DbgMsg =  _('The SQL that failed was');

   echo '<TABLE CELLPADDING=2 BORDER=2>';

   $tableheader = "<TR>
			<TD class='tableheader'>" . _('Type') . "</TD>
			<TD class='tableheader'>" . _('Number') . "</TD>
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
				<td>%s</td>
				<td width='200'>%s</td>
				<td>%s</td>
				<td ALIGN=RIGHT>%s</td>
				<td ALIGN=RIGHT>%s</td>
				<td>%s</td>";

		if ($_POST['TransType']==10){ /* invoices */

			printf("$format_base
				<td><a target='_blank' href='%s/PrintCustTrans.php?%&FromTransNo=%s&InvOrCredit=Invoice'><IMG SRC='%s' TITLE='" . _('Click to preview the invoice') . "'></a></td>
				</tr>",
				$myrow['typename'],
				$myrow['transno'],
				ConvertSQLDate($myrow['trandate']),
				$myrow['debtorno'],
				$myrow['branchcode'],
				$myrow['reference'],
				$myrow['invtext'],
				$myrow['order_'],
				$myrow['rate'],
				number_format($myrow['totalamt'],2),
				$myrow['currcode'],
				$rootpath,
				SID,
				$myrow['transno'],
				$rootpath.'/css/'.$theme.'/images/preview.gif');
		} elseif ($_POST['TransType']==11){ /* credit notes */
			printf("$format_base
				<td><a target='_blank' href='%s/PrintCustTrans.php?%s&FromTransNo=%s&InvOrCredit=Credit'><IMG SRC='%s' TITLE='" . _('Click to preview the credit') . "'></a></td>
				</tr>",
				$myrow['typename'],
				$myrow['transno'],
				ConvertSQLDate($myrow['trandate']),
				$myrow['debtorno'],
				$myrow['branchcode'],
				$myrow['reference'],
				$myrow['invtext'],
				$myrow['order_'],
				$myrow['rate'],
				number_format($myrow['totalamt'],2),
				$myrow['currcode'],
				$rootpath,
				SID,
				$myrow['transno'],
				$rootpath.'/css/'.$theme.'/images/preview.gif');
		} else {  /* otherwise */
			printf("$format_base</tr>",
				$myrow['typename'],
				$myrow['transno'],
				ConvertSQLDate($myrow['trandate']),
				$myrow['debtorno'],
				$myrow['branchcode'],
				$myrow['reference'],
				$myrow['invtext'],
				$myrow['order_'],
				$myrow['rate'],
				number_format($myrow['totalamt'],2),
				$myrow['currcode']);
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

include('includes/footer.inc');

?>

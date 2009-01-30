<?php

/* $Revision: 1.15 $ */

$PageSecurity = 2;

include('includes/session.inc');
$title = _('Customer Transactions Inquiry');
include('includes/header.inc');

echo '<P CLASS="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/transactions.png" TITLE="' . _('Transaction Inquiry') . '" ALT="">' . ' ' . _('Transaction Inquiry') . '</P>';
echo '<DIV CLASS="page_help_text">' . _('Choose which type of transaction to report on.') . '</DIV><BR>';

echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "' METHOD=POST>";

echo '<CENTER><TABLE CELLPADDING=2><TR>';

echo '<TD>' . _('Type') . ":</TD><TD><SELECT tabindex=1 name='TransType'> ";

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
echo '<TD>' . _('From') . ":</TD><TD><INPUT tabindex=2 TYPE=TEXT NAME='FromDate' MAXLENGTH=10 SIZE=11 VALUE=" . $_POST['FromDate'] . '></TD>';
echo '<TD>' . _('To') . ":</TD><TD><INPUT tabindex=3 TYPE=TEXT NAME='ToDate' MAXLENGTH=10 SIZE=11 VALUE=" . $_POST['ToDate'] . '></TD>';

echo "</TR></TABLE><INPUT tabindex=4 TYPE=SUBMIT NAME='ShowResults' VALUE='" . _('Show Transactions') . "'>";
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

   $ErrMsg = _('The customer transactions for the selected criteria could not be retrieved because') . ' - ' . DB_error_msg($db);
   $DbgMsg =  _('The SQL that failed was');
   $TransResult = DB_query($sql, $db,$ErrMsg,$DbgMsg);

   echo '<CENTER><TABLE CELLPADDING=2 BORDER=2>';

   $tableheader = "<TR>
			<TH>" . _('Type') . "</TH>
			<TH>" . _('Number') . "</TH>
			<TH>" . _('Date') . "</TH>
			<TH>" . _('Customer') . "</TH>
			<TH>" . _('Branch') . "</TH>
			<TH>" . _('Reference') . "</TH>
			<TH>" . _('Comments') . "</TH>
			<TH>" . _('Order') . "</TH>
			<TH>" . _('Ex Rate') . "</TH>
			<TH>" . _('Amount') . "</TH>
			<TH>" . _('Currency') . '</TH></TR>';
	echo $tableheader;

	$RowCounter = 1;
	$k = 0; //row colour counter

	while ($myrow=DB_fetch_array($TransResult)) {

		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
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

	}
	//end of while loop

 echo '</TABLE>';
}

include('includes/footer.inc');

?>

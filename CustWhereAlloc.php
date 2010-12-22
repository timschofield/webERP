<?php
/* $Revision: 1.10 $ */
/* $Id$*/
$PageSecurity = 2;

include('includes/session.inc');
$title = _('Customer How Paid Inquiry');
include('includes/header.inc');

echo "<form action='" . $_SERVER['PHP_SELF'] . "' method=post>";
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/money_add.png" title="' .
	_('Customer Where Allocated'). '" alt="" />' . $title . '</p>';

echo '<table class=selection cellpadding=2><tr>';

echo '<td>' . _('Type') . ":</td><td><select tabindex=1 name='TransType'> ";

$sql = "SELECT typeid, typename FROM systypes WHERE typeid = 10 OR typeid=12";
$resultTypes = DB_query($sql,$db);
while ($myrow=DB_fetch_array($resultTypes)){
	if (isset($_POST['TransType'])){
		if ($myrow['typeid'] == $_POST['TransType']){
			 echo "<option selected Value='" . $myrow['typeid'] . "'>" . $myrow['typename'];
		} else {
			 echo "<option Value='" . $myrow['typeid'] . "'>" . $myrow['typename'];
		}
	} else {
			 echo "<option Value='" . $myrow['typeid'] . "'>" . $myrow['typename'];
	}
}
echo '</select></td>';

if (!isset($_POST['TransNo'])) {$_POST['TransNo']='';}
echo '<td>'._('Transaction Number').":</td>
	<td><input tabindex=2 type=text name='TransNo' maxlength=10 size=10 value=". $_POST['TransNo'] . '></td>';

echo "</tr></table><br>
	<div class='centre'><input tabindex=3 type=submit name='ShowResults' value="._('Show How Allocated').'></div>';

if (isset($_POST['ShowResults']) AND  $_POST['TransNo']==''){
	echo '<br>';
	prnMsg(_('The transaction number to be queried must be entered first'),'warn');
}

if (isset($_POST['ShowResults']) AND  $_POST['TransNo']!=''){


/*First off get the DebtorTransID of the transaction (invoice normally) selected */
	$sql = "SELECT id,
			ovamount+ovgst AS totamt
		FROM debtortrans
		WHERE type='" . $_POST['TransType'] . "' AND transno = '" . $_POST['TransNo']."'";

	$result = DB_query($sql , $db);

	if (DB_num_rows($result)==1){
		$myrow = DB_fetch_array($result);
		$AllocToID = $myrow['id'];


		$sql = "SELECT type,
			transno,
			trandate,
			debtortrans.debtorno,
			reference,
			rate,
			ovamount+ovgst+ovfreight+ovdiscount as totalamt,
			custallocns.amt
		FROM debtortrans
			INNER JOIN custallocns ON debtortrans.id=custallocns.transid_allocfrom
		WHERE custallocns.transid_allocto='". $AllocToID."'";

		$ErrMsg = _('The customer transactions for the selected criteria could not be retrieved because');

		$TransResult = DB_query($sql, $db, $ErrMsg);

	if (DB_num_rows($TransResult)==0){
		prnMsg(_('There are no allocations made against this transaction'),'info');
	} else {
		echo '<br><table cellpadding=2 class=selection>';

		echo '<tr><th colspan=6><div class="centre"><font size=3 color=blue><b>'._('Allocations made against invoice number') . ' ' . $_POST['TransNo']
			. '<br>'._('Transaction Total').': '. number_format($myrow['totamt'],2) . '</font></b></div></th></tr>';

		$tableheader = "<tr><th>"._('Type')."</th>
					<th>"._('Number')."</th>
					<th>"._('Reference')."</th>
					<th>"._('Ex Rate')."</th>
					<th>"._('Amount')."</th>
					<th>"._('Alloc').'</th>
				</tr>';
		echo $tableheader;

		$RowCounter = 1;
		$k = 0; //row colour counter
		$AllocsTotal = 0;

		while ($myrow=DB_fetch_array($TransResult)) {

		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k++;
		}

		if ($myrow['type']==11){
			$TransType = _('Credit Note');
		} else {
			$TransType = _('Receipt');
		}
		echo "<td>".$TransType."</td>
				<td>".$myrow['transno']."</td>
				<td>".$myrow['reference']."</td>
				<td>".$myrow['rate']."</td>
				<td class=number>".number_format($myrow['totalamt'],2)."</td>
				<td class=number>".number_format($myrow['amt'],2)."</td>
				</tr>";

		$RowCounter++;
		If ($RowCounter == 12){
			$RowCounter=1;
			echo $tableheader;
		}
		//end of page full new headings if
		$AllocsTotal +=$myrow['amt'];
		}
		//end of while loop
		echo '<tr><td colspan = 5 class=number>'._('Total allocated').'</td>
			<td class=number>' . number_format($AllocsTotal,2) . '</td></tr>';
		echo '</table>';
	}
	}
}

echo '</form>';
include('includes/footer.inc');

?>
<?php

/* $Revision: 1.6 $ */
/*
	This page is called from SupplierInquiry.php when the 'view payments' button is selected
*/


$PageSecurity = 5;

include('includes/session.inc');

$title = _('Payment Allocations');

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

	if (!isset($_GET['SuppID'])){
		prnMsg( _('Supplier ID Number is not Set, can not display result'),'warn');
        	include('includes/footer.inc');
        	exit;
	}

	if (!isset($_GET['InvID'])){
		prnMsg( _('Invoice Number is not Set, can not display result'),'warn');
		include('includes/footer.inc');
		exit;
	}
$SuppID = $_GET['SuppID'];
$InvID = $_GET['InvID'];

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/transactions.png" title="' . _('Payments') . '" alt="">' . ' ' . _('Payment Allocation for Supplier') . ': ' . $SuppID . _(' and') . ' ' . _('Invoice') . ': ' . $InvID;

echo '<div class="page_help_text">' . _('This shows how the payment to the supplier was allocated') . '<a href="SupplierInquiry.php?&SupplierID=' . $SuppID . '"><br> ' . _('Back to supplier inquiry') . '</a></div><br>';

//echo "<br><font size=4 color=BLUE>Payment Allocation for Supplier: '$SuppID' and Invoice: '$InvID'</font>";

//	$_SESSION['SuppID'] = new SupplierID;
//	$_SESSION['InvID'] = new InvoiceID;

$SQL= "SELECT supptrans.supplierno,
		supptrans.suppreference,
		supptrans.trandate,
		supptrans.alloc
	FROM supptrans
	WHERE supptrans.id IN (SELECT suppallocs.transid_allocfrom
				FROM supptrans, suppallocs
				WHERE supptrans.supplierno = '$SuppID'
				AND supptrans.suppreference = '$InvID'
				AND supptrans.id = suppallocs.transid_allocto)";

/*
Might be a way of doing this query without a subquery

$SQL= "SELECT supptrans.supplierno,
		supptrans.suppreference,
		supptrans.trandate,
		supptrans.alloc
	FROM supptrans INNER JOIN suppallocs ON supptrans.id=suppallocs.transid_allocfrom
	WHERE supptrans.supplierno = '$SuppID'
	AND supptrans.suppreference = '$InvID'
*/

$Result = DB_query($SQL, $db);
if (DB_num_rows($Result) == 0){
	prnMsg(_('There may be a problem retrieving the information. No data is returned'),'warn');
	echo '<br><a HREF ="javascript:history.back()">' . _('Go back') . '</a>';
	include('includes/foooter.inc');
	exit;
}

echo '<table cellpadding=2 colspan=7 width=100% border=0>';
$TableHeader = "<tr>
<th>" . _('Supplier Number') . '<br>' . _('Reference') . "</th>
<th>" . _('Payment') .'<br>' . _('Reference') . "</th>
<th>" . _('Payment') . '<br>' . _('Date') . "</th>
<th>" . _('Total Payment') . '<br>' . _('Amount') .	'</th></tr>';

echo $TableHeader;

$j=1;
$k=0; //row colour counter
  while ($myrow = DB_fetch_array($Result)) {
	if ($k == 1){
		echo '<tr class="EvenTableRows">';
		$k = 0;
	} else {
		echo '<tr class="OddTableRows">';
		$k++;
	}

	printf('<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		</tr>',
		$myrow['supplierno'],
		$myrow['suppreference'],
		ConvertSQLDate($myrow['trandate']),
		$myrow['alloc']	);

		$j++;
		If ($j == 18){
                $j=1;
                echo $TableHeader;
       }

}
  echo '</table>';

include('includes/footer.inc');
?>

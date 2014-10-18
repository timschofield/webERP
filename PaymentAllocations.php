<?php

/* $Id: PaymentAllocations.php 6310 2013-08-29 10:42:50Z daintree $*/

/*
	This page is called from SupplierInquiry.php when the 'view payments' button is selected
*/

include('includes/session.inc');

$Title = _('Payment Allocations');

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

echo '<p class="page_title_text">
		<img src="'.$RootPath.'/css/'.$Theme.'/images/transactions.png" title="' . _('Payments') . '" alt="" />' . ' ' . _('Payment Allocation for Supplier') . ': ' . $SuppID . _(' and') . ' ' . _('Invoice') . ': ' . $InvID . '</p>';

echo '<div class="page_help_text">' .
		_('This shows how the payment to the supplier was allocated') . '<a href="SupplierInquiry.php?&amp;SupplierID=' . $SuppID . '">' . _('Back to supplier inquiry') . '</a>
	</div>
	<br />';

$SQL= "SELECT supptrans.supplierno,
				supptrans.suppreference,
				supptrans.trandate,
				supptrans.alloc,
				currencies.decimalplaces AS currdecimalplaces
		FROM supptrans INNER JOIN suppliers
		ON supptrans.supplierno=suppliers.supplierid
		INNER JOIN currencies
		ON suppliers.currcode=currencies.currabrev
		WHERE supptrans.id IN (SELECT suppallocs.transid_allocfrom
								FROM supptrans, suppallocs
								WHERE supptrans.supplierno = '" . $SuppID . "'
								AND supptrans.suppreference = '" . $InvID . "'
								AND supptrans.id = suppallocs.transid_allocto)";


$Result = DB_query($SQL, $db);
if (DB_num_rows($Result) == 0){
	prnMsg(_('There may be a problem retrieving the information. No data is returned'),'warn');
	echo '<br /><a href ="javascript:history.back()">' . _('Go back') . '</a>';
	include('includes/footer.inc');
	exit;
}

echo '<table cellpadding="2" width="80%" class="selection">';
$TableHeader = '<tr>
					<th>' . _('Supplier Number') . '<br />' . _('Reference') . '</th>
					<th>' . _('Payment')  . '<br />' . _('Reference') . '</th>
					<th>' . _('Payment') . '<br />' . _('Date') . '</th>
					<th>' . _('Total Payment') . '<br />' . _('Amount') .	'</th>
				</tr>';

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

	echo '<td>' . $myrow['supplierno'] . '</td>
		<td>' . $myrow['suppreference'] . '</td>
		<td>' . ConvertSQLDate($myrow['trandate']) . '</td>
		<td class="number">' . locale_number_format($myrow['alloc'],$myrow['currdecimalplaces']) . '</td>
		</tr>';

		$j++;
		if ($j == 18){
			$j=1;
			echo $TableHeader;
		}

}
  echo '</table>';

include('includes/footer.inc');
?>
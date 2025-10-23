<?php

/*
	This page is called from SupplierInquiry.php when the 'view payments' button is selected
*/

require(__DIR__ . '/includes/session.php');

$Title = __('Payment Allocations');
$ViewTopic = 'AccountsPayable';
$BookMark = 'PaymentAllocations';
include('includes/header.php');

include('includes/SQL_CommonFunctions.php');

if (!isset($_GET['SuppID'])){
	prnMsg( __('Supplier ID Number is not Set, can not display result'),'warn');
	include('includes/footer.php');
	exit();
}

if (!isset($_GET['InvID'])){
	prnMsg( __('Invoice Number is not Set, can not display result'),'warn');
	include('includes/footer.php');
	exit();
}
$SuppID = $_GET['SuppID'];
$InvID = $_GET['InvID'];

echo '<p class="page_title_text">
		<img src="'.$RootPath.'/css/'.$Theme.'/images/transactions.png" title="' . __('Payments') . '" alt="" />' . ' ' . __('Payment Allocation for Supplier') . ': ' . $SuppID . __(' and') . ' ' . __('Invoice') . ': ' . $InvID . '</p>';

echo '<div class="page_help_text">' .
		__('This shows how the payment to the supplier was allocated') . '<a href="' . $RootPath . '/SupplierInquiry.php?&amp;SupplierID=' . $SuppID . '">' . __('Back to supplier inquiry') . '</a>
	</div>';

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


$Result = DB_query($SQL);
if (DB_num_rows($Result) == 0){
	prnMsg(__('There may be a problem retrieving the information. No data is returned'),'warn');
	echo '<br /><a href="javascript:history.back()">' . __('Go back') . '</a>';
	include('includes/footer.php');
	exit();
}

echo '<table cellpadding="2" width="80%" class="selection">';
$TableHeader = '<tr>
					<th>' . __('Supplier Number') . '<br />' . __('Reference') . '</th>
					<th>' . __('Payment')  . '<br />' . __('Reference') . '</th>
					<th>' . __('Payment') . '<br />' . __('Date') . '</th>
					<th>' . __('Total Payment') . '<br />' . __('Amount') .	'</th>
				</tr>';

echo $TableHeader;

$j=1;
  while ($MyRow = DB_fetch_array($Result)) {

	echo '<tr class="striped_row">
		<td>' . $MyRow['supplierno'] . '</td>
		<td>' . $MyRow['suppreference'] . '</td>
		<td>' . ConvertSQLDate($MyRow['trandate']) . '</td>
		<td class="number">' . locale_number_format($MyRow['alloc'],$MyRow['currdecimalplaces']) . '</td>
		</tr>';

		$j++;
		if ($j == 18){
			$j=1;
			echo $TableHeader;
		}

}
  echo '</table>';

include('includes/footer.php');

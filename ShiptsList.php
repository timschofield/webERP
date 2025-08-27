<?php

// Shows a list of all the open shipments for a selected supplier. Linked from POItems.php

//$PageSecurity = 2;

require(__DIR__ . '/includes/session.php');

$Title = __('Shipments Open Inquiry');
$ViewTopic = 'Shipments';
$BookMark = '';
include('includes/header.php');

echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' .
				__('Supplier') . '" alt="" />' . ' ' . __('Open Shipments for').' ' . $_GET['SupplierName']. '.</p>';

if (!isset($_GET['SupplierID']) or !isset($_GET['SupplierName'])){
	echo '<br />';
	prnMsg( __('This page must be given the supplier code to look for shipments for'), 'error');
	include('includes/footer.php');
	exit();
}

$SQL = "SELECT shiptref,
		vessel,
		eta
	FROM shipments
	WHERE supplierid='" . $_GET['SupplierID'] . "'";
$ErrMsg = __('No shipments were returned from the database because'). ' - '. DB_error_msg();
$ShiptsResult = DB_query($SQL, $ErrMsg);

if (DB_num_rows($ShiptsResult)==0){
       prnMsg(__('There are no open shipments currently set up for').' ' . $_GET['SupplierName'],'warn');
	include('includes/footer.php');
       exit();
}
/*show a table of the shipments returned by the SQL */

echo '<table cellpadding="2" class="selection">';
echo '<tr>
		<th>' .  __('Reference'). '</th>
		<th>' .  __('Vessel'). '</th>
		<th>' .  __('ETA'). '</th></tr>';

$j = 1;

while ($MyRow=DB_fetch_array($ShiptsResult)) {

       echo '<tr class="striped_row">
			<td><a href="'.$RootPath.'/Shipments.php?SelectedShipment='.$MyRow['shiptref'].'">' . $MyRow['shiptref'] . '</a></td>
       		<td>' . $MyRow['vessel'] . '</td>
		<td>' . ConvertSQLDate($MyRow['eta']) . '</td>
		</tr>';

}
//end of while loop

echo '</table>';

include('includes/footer.php');

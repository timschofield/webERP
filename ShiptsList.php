<?php

/* $Id: ShiptsList.php 6310 2013-08-29 10:42:50Z daintree $*/

//$PageSecurity = 2;
include ('includes/session.inc');
$Title = _('Shipments Open Inquiry');
include('includes/header.inc');

echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' .
				_('Supplier') . '" alt="" />' . ' ' . _('Open Shipments for').' ' . $_GET['SupplierName']. '.</p>';

if (!isset($_GET['SupplierID']) or !isset($_GET['SupplierName'])){
	echo '<br />';
	prnMsg( _('This page must be given the supplier code to look for shipments for'), 'error');
	include('includes/footer.inc');
	exit;
}

$SQL = "SELECT shiptref,
		vessel,
		eta
	FROM shipments
	WHERE supplierid='" . $_GET['SupplierID'] . "'";
$ErrMsg = _('No shipments were returned from the database because'). ' - '. DB_error_msg($db);
$ShiptsResult = DB_query($SQL,$db, $ErrMsg);

if (DB_num_rows($ShiptsResult)==0){
       prnMsg(_('There are no open shipments currently set up for').' ' . $_GET['SupplierName'],'warn');
	include('includes/footer.inc');
       exit;
}
/*show a table of the shipments returned by the SQL */

echo '<table cellpadding="2" class="selection">';
echo '<tr>
		<th>' .  _('Reference'). '</th>
		<th>' .  _('Vessel'). '</th>
		<th>' .  _('ETA'). '</th></tr>';

$j = 1;
$k = 0; //row colour counter

while ($myrow=DB_fetch_array($ShiptsResult)) {
       if ($k==1){
              echo '<tr class="OddTableRows">';
              $k=0;
       } else {
              echo '<tr class="EvenTableRows">';
              $k=1;
       }

       echo '<td><a href="'.$RootPath.'/Shipments.php?' . SID . 'SelectedShipment='.$myrow['shiptref'].'">' . $myrow['shiptref'] . '</a></td>
       		<td>' . $myrow['vessel'] . '</td>
		<td>' . ConvertSQLDate($myrow['eta']) . '</td>
		</tr>';

}
//end of while loop

echo '</table>';

include('includes/footer.inc');

?>
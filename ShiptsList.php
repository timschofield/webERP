<?php

/* $Revision: 1.9 $ */

$PageSecurity = 2;
include ('includes/session.inc');
$title = _('Shipments Open Inquiry');
include('includes/header.inc');


if (!isset($_GET['SupplierID']) OR !isset($_GET['SupplierName'])){
	echo '<p>';
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

echo '<div class="centre"><font size=4 color=BLUE>'. _('Open Shipments for').' ' . $_GET['SupplierName'] . '</font><br>
	</div><table cellpadding=2 colspan=2>';
$TableHeader = '<tr>
		<th>'. _('Reference'). '</th>
		<th>'. _('Vessel'). '</th>
		<th>'. _('ETA'). '</th></tr>';

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

       printf('<td >%s</td>
       		<td>%s</td>
		<td>%s</td>
		</tr>',
		$myrow['shiptref'],
		$myrow['vessel'],
		ConvertSQLDate($myrow['eta']));

       $j++;
       If ($j == 12){
		$j=1;
		$TableHeader;
       }
}
//end of while loop

echo '</table>';

include('includes/footer.inc');

?>
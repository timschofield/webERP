<?php

/* $Revision: 1.7 $ */

$PageSecurity = 2;
include ('includes/session.inc');
$title = _('Shipments Open Inquiry');
include('includes/header.inc');


if (!isset($_GET['SupplierID']) OR !isset($_GET['SupplierName'])){
	echo '<P>';
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

echo '<CENTER><FONT SIZE=4 COLOR=BLUE>'. _('Open Shipments for').' ' . $_GET['SupplierName'] . '</FONT><BR>
	<TABLE CELLPADDING=2 COLSPAN=2>';
$TableHeader = '<TR>
		<TD class="tableheader">'. _('Reference'). '</TD>
		<TD class="tableheader">'. _('Vessel'). '</TD>
		<TD class="tableheader">'. _('ETA'). '</TD></TR>';

$j = 1;
$k = 0; //row colour counter

while ($myrow=DB_fetch_array($ShiptsResult)) {
       if ($k==1){
              echo '<tr bgcolor="#CCCCCC">';
              $k=0;
       } else {
              echo '<tr bgcolor="#EEEEEE">';
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

echo '</TABLE></CENTER>';

include('includes/footer.inc');

?>
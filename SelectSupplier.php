<?php
/* $Revision: 1.43 $ */

$PageSecurity = 2;

include('includes/session.inc');
$title = _('Search Suppliers');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

if (!isset($_SESSION['SupplierID'])){
echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/supplier.png" title="' . _('Search') . '" alt="">' . ' ' . _('Suppliers') . '';
}

// only get geocode information if integration is on, and supplier has been selected
if ($_SESSION['geocode_integration']==1 AND isset($_SESSION['SupplierID'])){

	$sql="SELECT * FROM geocode_param WHERE 1";
	$ErrMsg = _('An error occurred in retrieving the information');;
	$result = DB_query($sql, $db, $ErrMsg);
	$myrow = DB_fetch_array($result);
	$sql = "SELECT suppliers.supplierid,suppliers.lat, suppliers.lng
    			                FROM suppliers
                      			WHERE suppliers.supplierid = '" . $_SESSION['SupplierID'] . "'
		                        ORDER BY suppliers.supplierid";
	$ErrMsg = _('An error occurred in retrieving the information');
	$result2 = DB_query($sql, $db, $ErrMsg);
	$myrow2 = DB_fetch_array($result2);
	$lat = $myrow2['lat'];
	$lng = $myrow2['lng'];
	$api_key = $myrow['geocode_key'];
	$center_long = $myrow['center_long'];
	$center_lat = $myrow['center_lat'];
	$map_height = $myrow['map_height'];
	$map_width = $myrow['map_width'];
	$map_host = $myrow['map_host'];

	echo '<script src="http://maps.google.com/maps?file=api&v=2&key=' . $api_key . '"';
	echo ' type="text/javascript"></script>';
	echo ' <script type="text/javascript">';
	echo '    //<![CDATA[ '; ?>

    function load() {
      if (GBrowserIsCompatible()) {
        var map = new GMap2(document.getElementById("map"));
        map.addControl(new GSmallMapControl());
        map.addControl(new GMapTypeControl());
<?php echo 'map.setCenter(new GLatLng(' . $lat . ', ' . $lng . '), 11);'; ?>
<?php echo 'var marker = new GMarker(new GLatLng(' . $lat . ', ' . $lng . '));' ?>
        map.addOverlay(marker);
        GEvent.addListener(marker, "click", function() {
        marker.openInfoWindowHtml(WINDOW_HTML);
          });
        marker.openInfoWindowHtml(WINDOW_HTML);
      }
    }
    //]]>
    </script>
  <body onload="load()" onunload="GUnload()">

<?php
}

$msg='';
/*
if (!isset($_POST['Search'])){
	$_POST['Search']='';
}
*/

//echo '<a href=Suppliers.php>'. _('Create new supplier').'</a>';

if (!isset($_POST['PageOffset'])) {
  $_POST['PageOffset'] = 1;
} else {
  if ($_POST['PageOffset']==0) {
    $_POST['PageOffset'] = 1;
  }
}

If (isset($_POST['Select'])) { /*User has hit the button selecting a supplier */
	$_SESSION['SupplierID'] = $_POST['Select'];
	unset($_POST['Select']);
	unset($_POST['Keywords']);
	unset($_POST['SupplierCode']);
	unset($_POST['Search']);
	unset($_POST['Go']);
	unset($_POST['Next']);
	unset($_POST['Previous']);
}

if (isset($_POST['Search'])
		OR isset($_POST['Go'])
		OR isset($_POST['Next'])
		OR isset($_POST['Previous'])){

	If ( strlen($_POST['Keywords'])>0 AND strlen($_POST['SupplierCode'])>0) {
		$msg='<br>' . _('Supplier name keywords have been used in preference to the Supplier code extract entered');
	}
	if ($_POST['Keywords']=='' AND $_POST['SupplierCode']=='') {
		$SQL = 'SELECT supplierid,
					suppname,
					currcode,
					address1,
					address2,
					address3,
					address4
				FROM suppliers
				ORDER BY suppname';
	} else {
		If (strlen($_POST['Keywords'])>0) {

			$_POST['Keywords'] = strtoupper($_POST['Keywords']);
			//insert wildcard characters in spaces

			$i=0;
			$SearchString = '%';
			while (strpos($_POST['Keywords'], ' ', $i)) {
				$wrdlen=strpos($_POST['Keywords'],' ',$i) - $i;
				$SearchString=$SearchString . substr($_POST['Keywords'],$i,$wrdlen) . '%';
				$i=strpos($_POST['Keywords'],' ',$i) +1;
			}
			$SearchString = $SearchString. substr($_POST['Keywords'],$i).'%';

			$SQL = "SELECT supplierid,
					suppname,
					currcode,
					address1,
					address2,
					address3,
					address4
				FROM suppliers
				WHERE suppname " . LIKE . " '$SearchString'
				ORDER BY suppname";

		} elseif (strlen($_POST['SupplierCode'])>0){
			$_POST['SupplierCode'] = strtoupper($_POST['SupplierCode']);
			$SQL = "SELECT supplierid,
					suppname,
					currcode,
					address1,
					address2,
					address3,
					address4
				FROM suppliers
				WHERE supplierid " . LIKE  . " '%" . $_POST['SupplierCode'] . "%'
				ORDER BY supplierid";
		} 
		
	} //one of keywords or SupplierCode was more than a zero length string
	
	$result = DB_query($SQL,$db);
	if (DB_num_rows($result)==1){
	   $myrow = DB_fetch_row($result);
	   $SingleSupplierReturned = $myrow[0];
	}

} //end of if search

if (isset($SingleSupplierReturned)) { /*there was only one supplier returned */
	$_SESSION['SupplierID'] = $SingleSupplierReturned;
	unset($_POST['Keywords']);
	unset($_POST['SupplierCode']);
}

if (isset($_SESSION['SupplierID'])){

	$SupplierName = '';
	$SQL = "SELECT suppliers.suppname
		FROM suppliers
		WHERE suppliers.supplierid ='" . $_SESSION['SupplierID'] . "'";

	$SupplierNameResult = DB_query($SQL,$db);
	if (DB_num_rows($SupplierNameResult)==1){
	   $myrow = DB_fetch_row($SupplierNameResult);
	   $SupplierName = $myrow[0];
	}
	echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/supplier.png" title="' . _('Supplier') . '" alt="">' . ' ' . _('Supplier') . ' : <b>' . $_SESSION['SupplierID']  . " - $SupplierName</b> " . _('has been selected') . '.</p>';
	echo '<div class="page_help_text">' . _('Select a menu option to operate using this supplier.') . '</div>';
	echo '<br><table width=90% colspan=2 border=2 cellpadding=4>';
	echo "<tr>
		<th width=33%>" . _('Supplier Inquiries') . "</th>
		<th width=33%>". _('Supplier Transactions') . "</th>
		<th width=33%>" . _('Supplier Maintenance') . "</th>
	</tr>";
	echo '<tr><td VALIGN=TOP class="menu_group_items">';    /* Inquiry Options */
	echo "<a href=\"$rootpath/SupplierInquiry.php?" . SID . '&SupplierID=' . $_SESSION['SupplierID'] . "\">" . _('Supplier Account Inquiry') . '</a><br>';
	echo '<br>';
	echo "<br><a href='$rootpath/PO_SelectOSPurchOrder.php?" . SID . '&SelectedSupplier=' . $_SESSION['SupplierID'] . "'>" . _('Add / Receive / View Outstanding Purchase Orders') . '</a>';
	echo "<br><a href='$rootpath/PO_SelectPurchOrder.php?" . SID . '&SelectedSupplier=' . $_SESSION['SupplierID'] . "'>" . _('View All Purchase Orders') . '</a><br>';
	wikiLink('Supplier', $_SESSION['SupplierID']);	
	echo '<br>';
	echo "<br><a href='$rootpath/Shipt_Select.php?" . SID . '&SelectedSupplier=' . $_SESSION['SupplierID'] . "'>" . _('Search / Modify / Close Shipments') . '</a>';
	echo '</td><td VALIGN=TOP class="menu_group_items">'; /* Supplier Transactions */
	echo "<a href=\"$rootpath/SupplierInvoice.php?" . SID . '&SupplierID=' . $_SESSION['SupplierID'] . "\">" . _('Enter a Suppliers Invoice') . '</a><br>';
	echo "<a href=\"$rootpath/SupplierCredit.php?" . SID . '&SupplierID=' . $_SESSION['SupplierID'] . "\">" . _('Enter a Suppliers Credit Note') . '</a><br>';
	echo "<a href=\"$rootpath/Payments.php?" . SID . '&SupplierID=' . $_SESSION['SupplierID'] . "\">" . _('Enter a Payment to, or Receipt from the Supplier') . '</a><br>';
	echo '<br>';
	echo "<br><a href='$rootpath/ReverseGRN.php?" . SID . '&SupplierID=' . $_SESSION['SupplierID'] . "'>" . _('Reverse an Outstanding Goods Received Note (GRN)') . '</a>';
	echo '</td><td VALIGN=TOP class="menu_group_items">'; /* Supplier Maintenance */
        echo '<a href="' . $rootpath . '/Suppliers.php?">' . _('Add a New Supplier') . '</a><br>';
	echo "<a href=\"$rootpath/Suppliers.php?" . SID . '&SupplierID=' . $_SESSION['SupplierID'] . "\">" . _('Modify Or Delete Supplier Details') . '</a>';
	echo "<br><a href=\"$rootpath/SupplierContacts.php?" . SID . '&SupplierID=' . $_SESSION['SupplierID'] . "\">" . _('Add/Modify/Delete Supplier Contacts') . '</a>';
	echo '<br>';
	echo "<br><a href='$rootpath/Shipments.php?" . SID . "&NewShipment=Yes'>" . _('Set Up A New Shipment') . '</a>';
	echo '</td></tr></table>';
} else {
// Supplier is not selected yet
echo '<br>';
echo '<table WIDTH=90% colspan=2 BORDER=2 cellpadding=4>';
        echo "<tr>
                <th WIDTH=33%>" . _('Supplier Inquiries') . "</th>
                <th WIDTH=33%>". _('Supplier Transactions') . "</th>
                <th WIDTH=33%>" . _('Supplier Maintenance') . "</th>
		</tr>";
echo '<tr><td VALIGN=top>';    /* Inquiry Options */
echo '</td><td VALIGN=top>'; /* Supplier Transactions */
echo '</td><td VALIGN=top>'; /* Supplier Maintenance */
echo '<a href="' . $rootpath . '/Suppliers.php?">' . _('Add a New Supplier') . '</a><br>';
echo '</td></tr></table>';
}

echo "<form action='" . $_SERVER['PHP_SELF'] . '?' . SID . "' method=post>";
echo '<b>' . $msg;
echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/magnifier.png" title="' . _('Search') . '" alt="">' . ' ' . _('Search for Suppliers') . '

	<table cellpadding=3 colspan=4>
	<tr>
	<td>' . _('Enter a partial Name') . ':</font></td>
	<td>';

if (isset($_POST['Keywords'])) {

	echo "<input type='Text' name='Keywords' value='" . $_POST['Keywords'] . "' size=20 maxlength=25>";

} else {

	echo "<input type='Text' name='Keywords' size=20 maxlength=25>";
}

echo '</td>
	<td><b>' . _('OR') . '</b></font></td>
	<td>' . _('Enter a partial Code') . ':</font></td>
	<td>';

if (isset($_POST['SupplierCode'])) {

	echo "<input type='Text' name='SupplierCode' value='" . $_POST['SupplierCode'] . "' size=15 maxlength=18>";

} else {

	echo "<input type='Text' name='SupplierCode' size=15 maxlength=18>";

}

echo "</td>
</tr>
</table>
<div class='centre'>
<input type=submit name='Search' VALUE='" . _('Search Now') . "'>
</div>";
//if (isset($result) AND !isset($SingleSupplierReturned)) {
if (isset($_POST['Search'])) {
	$ListCount=DB_num_rows($result);
	$ListPageMax=ceil($ListCount/$_SESSION['DisplayRecordsMax']);
	
	if (isset($_POST['Next'])) {
		if ($_POST['PageOffset'] < $ListPageMax) {
			$_POST['PageOffset'] = $_POST['PageOffset'] + 1;
		}
	}
	if (isset($_POST['Previous'])) {
		if ($_POST['PageOffset'] > 1) {
			$_POST['PageOffset'] = $_POST['PageOffset'] - 1;
		}
	}
	if ($ListPageMax >1) {
		echo "<p>&nbsp;&nbsp;" . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
		
		echo '<select name="PageOffset">';
		
		$ListPage=1;
		while($ListPage <= $ListPageMax) {
			if ($ListPage == $_POST['PageOffset']) {
				echo '<option VALUE=' . $ListPage . ' selected>' . $ListPage . '</option>';
			} else {
				echo '<option VALUE=' . $ListPage . '>' . $ListPage . '</option>';
			}
			$ListPage++;
		}
		echo '</select>
			<input type=submit name="Go" VALUE="' . _('Go') . '">
			<input type=submit name="Previous" VALUE="' . _('Previous') . '">
			<input type=submit name="Next" VALUE="' . _('Next') . '">';
		echo '<p>';
	}
	echo "<input type=hidden name='Search' VALUE='" . _('Search Now') . "'>";
  	echo '<br><br>';
  	echo '<br><table cellpadding=2 colspan=7 BORDER=1>';
  	$tableheader = "<tr>
  		<th>" . _('Code') . "</th>
		<th>" . _('Supplier Name') . "</th>
		<th>" . _('Currency') . "</th>
		<th>" . _('Address 1') . "</th>
		<th>" . _('Address 2') . "</th>
		<th>" . _('Address 3') . "</th>
		<th>" . _('Address 4') . "</th>
		</tr>";
	echo $tableheader;
	$j = 1;
  	$RowIndex = 0;

  	if (DB_num_rows($result)<>0){
 		DB_data_seek($result, ($_POST['PageOffset']-1)*$_SESSION['DisplayRecordsMax']);
  	}

	while (($myrow=DB_fetch_array($result)) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {

		printf("<tr>
			<td><input type=submit name='Select' VALUE='%s'</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			</tr>",
			$myrow['supplierid'],
			$myrow['suppname'],
			$myrow['currcode'],
			$myrow['address1'],
			$myrow['address2'],
			$myrow['address3'],
			$myrow['address4']);

    $RowIndex = $RowIndex + 1;
//end of page full new headings if
	}
//end of while loop

	echo '</table>';

}
//end if results to show


if (isset($ListPageMax) and $ListPageMax >1) {
	echo "<p>&nbsp;&nbsp;" . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
	
	echo '<select name="PageOffset">';
	
	$ListPage=1;
	while($ListPage <= $ListPageMax) {
		if ($ListPage == $_POST['PageOffset']) {
			echo '<option VALUE=' . $ListPage . ' selected>' . $ListPage . '</option>';
		} else {
			echo '<option VALUE=' . $ListPage . '>' . $ListPage . '</option>';
		}
		$ListPage++;
	}
	echo '</select>
		<input type=submit name="Go" VALUE="' . _('Go') . '">
		<input type=submit name="Previous" VALUE="' . _('Previous') . '">
		<input type=submit name="Next" VALUE="' . _('Next') . '">';
	echo '<p>';
}

echo '</form>';
// Only display the geocode map if the integration is turned on, and there is a latitude/longitude to display
if (isset($_SESSION['SupplierID']) and $_SESSION['SupplierID']!='') {
if ($_SESSION['geocode_integration']==1){
if ($lat ==0){
echo '<br>';
echo '<div class="centre">' . _('Mapping is enabled, but no Mapping data to display for this Supplier.') . '</div>';
} else {
echo '<div class="centre"><br>';
echo '<tr><td colspan=2>';
echo '<table WIDTH=45% colspan=2 BORDER=2 cellpadding=4>';
echo "<tr>
                <th WIDTH=33%>" . _('Supplier Mapping') . "</th>
        </tr>";
echo '</td><td VALIGN=TOp>'; /* Mapping */
echo '<div class="centre">' . _('Mapping is enabled, Map will display below.') . '</div>';
echo '<div class="centre" id="map" style="width: '. $map_width . 'px; height: ' .  $map_height  . 'px"></div></div><br>';
echo "</th></tr></table>";
}}
// Extended Info only if selected in Configuration
if ($_SESSION['Extended_SupplierInfo']==1){
if ($_SESSION['SupplierID']!=''){
$sql = "SELECT suppliers.suppname, suppliers.lastpaid, suppliers.lastpaiddate, suppliersince
                FROM suppliers
                WHERE suppliers.supplierid ='" . $_SESSION['SupplierID'] . "'";
$ErrMsg = _('An error occurred in retrieving the information');
$DataResult = DB_query($sql, $db, $ErrMsg);
$myrow = DB_fetch_array($DataResult);
// Select some more data about the supplier
$SQL = "select sum(-ovamount) as total from supptrans where supplierno = '" . $_SESSION['SupplierID'] . "' and type != '20'";
        $Total1Result = DB_query($SQL,$db);
        $row = DB_fetch_array($Total1Result);
echo '<br>';
echo '<tr><td colspan=2>';
echo '<table WIDTH=45% colspan=2 BORDER=2 cellpadding=4>';
        echo "<tr>
                <th WIDTH=33%>" . _('Supplier Data') . "</th>
        </tr>";
echo '<tr><td VALIGN=TOp>';    /* Supplier Data */
//echo "Distance to this Supplier: <b>TBA</b><br>";
echo _('Last Paid:') . ' <b>' . ConvertSQLDate($myrow['lastpaiddate']) . '</b><br>';
echo _('Last Paid Amount:') . ' <b>' . number_format($myrow['lastpaid'],2) . '</b><br>';
echo _('Supplier since:') . ' <b>' . ConvertSQLDate($myrow['suppliersince']) . '</b><br>';
echo _('Total Spend with this Supplier:') . ' <b>' . number_format($row['total'],2) . '</b><br>';
echo '</th></tr></table>';
}}}
include('includes/footer.inc');
?>

<script language='JavaScript' type='text/javascript'>
    //<![CDATA[
            <!--
            document.forms[0].SupplierCode.select();
            document.forms[0].SupplierCode.focus();
            //-->
    //]]>
</script>

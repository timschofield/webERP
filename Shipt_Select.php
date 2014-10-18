<?php

/* $Id: Shipt_Select.php 6310 2013-08-29 10:42:50Z daintree $*/

include('includes/session.inc');
$Title = _('Search Shipments');
include('includes/header.inc');
echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/magnifier.png" title="' . _('Search') .
	'" alt="" />' . ' ' . $Title . '</p>';

if (isset($_GET['SelectedStockItem'])){
	$SelectedStockItem=$_GET['SelectedStockItem'];
} elseif (isset($_POST['SelectedStockItem'])){
	$SelectedStockItem=$_POST['SelectedStockItem'];
}

if (isset($_GET['ShiptRef'])){
	$ShiptRef=$_GET['ShiptRef'];
} elseif (isset($_POST['ShiptRef'])){
	$ShiptRef=$_POST['ShiptRef'];
}

if (isset($_GET['SelectedSupplier'])){
	$SelectedSupplier=$_GET['SelectedSupplier'];
} elseif (isset($_POST['SelectedSupplier'])){
	$SelectedSupplier=$_POST['SelectedSupplier'];
}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';


If (isset($_POST['ResetPart'])) {
     unset($SelectedStockItem);
}

If (isset($ShiptRef) AND $ShiptRef!='') {
	if (!is_numeric($ShiptRef)){
		  echo '<br />';
		  prnMsg( _('The Shipment Number entered MUST be numeric') );
		  unset ($ShiptRef);
	} else {
		echo _('Shipment Number'). ' - '. $ShiptRef;
	}
} else {
	if (isset($SelectedSupplier)) {
		echo '<br />' ._('For supplier'). ': '. $SelectedSupplier . ' ' . _('and'). ' ';
		echo '<input type="hidden" name="SelectedSupplier" value="'. $SelectedSupplier. '" />';
	}
	If (isset($SelectedStockItem)) {
		 echo _('for the part'). ': ' . $SelectedStockItem . '.';
		echo '<input type="hidden" name="SelectedStockItem" value="'. $SelectedStockItem. '" />';
	}
}

if (isset($_POST['SearchParts'])) {

	If ($_POST['Keywords'] AND $_POST['StockCode']) {
		echo '<br />';
		prnMsg( _('Stock description keywords have been used in preference to the Stock code extract entered'),'info');
	}
	$SQL = "SELECT stockmaster.stockid,
			description,
			decimalplaces,
			SUM(locstock.quantity) AS qoh,
			units,
			SUM(purchorderdetails.quantityord-purchorderdetails.quantityrecd) AS qord
		FROM stockmaster INNER JOIN locstock
			ON stockmaster.stockid = locstock.stockid
		INNER JOIN purchorderdetails
			ON stockmaster.stockid=purchorderdetails.itemcode";

	If ($_POST['Keywords']) {
		//insert wildcard characters in spaces
		$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';

		$SQL .= " WHERE purchorderdetails.shiptref IS NOT NULL
			AND purchorderdetails.shiptref<>0
			AND stockmaster.description " . LIKE . " '" . $SearchString . "'
			AND categoryid='" . $_POST['StockCat'] . "'";

	 } elseif ($_POST['StockCode']){

		$SQL .= " WHERE purchorderdetails.shiptref IS NOT NULL
			AND purchorderdetails.shiptref<>0
			AND stockmaster.stockid " . LIKE . " '%" . $_POST['StockCode'] . "%'
			AND categoryid='" . $_POST['StockCat'] ."'";

	 } elseif (!$_POST['StockCode'] AND !$_POST['Keywords']) {
		$SQL .= " WHERE purchorderdetails.shiptref IS NOT NULL
			AND purchorderdetails.shiptref<>0
			AND stockmaster.categoryid='" . $_POST['StockCat'] . "'";

	 }
	$SQL .= "  GROUP BY stockmaster.stockid,
						stockmaster.description,
						stockmaster.decimalplaces,
						stockmaster.units";

	$ErrMsg = _('No Stock Items were returned from the database because'). ' - '. DB_error_msg($db);
	$StockItemsResult = DB_query($SQL,$db, $ErrMsg);

}

if (!isset($ShiptRef) or $ShiptRef==""){
	echo '<table class="selection"><tr><td>';
	echo _('Shipment Number'). ': <input type="text" name="ShiptRef" maxlength="10" size="10" /> '.
		_('Into Stock Location').' :<select name="StockLocation"> ';
	$sql = "SELECT loccode, locationname FROM locations";
	$resultStkLocs = DB_query($sql,$db);
	while ($myrow=DB_fetch_array($resultStkLocs)){
		if (isset($_POST['StockLocation'])){
			if ($myrow['loccode'] == $_POST['StockLocation']){
			echo '<option selected="selected" value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
			} else {
			echo '<option value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
			}
		} elseif ($myrow['loccode']==$_SESSION['UserStockLocation']){
			$_POST['StockLocation'] = $_SESSION['UserStockLocation'];
			echo '<option selected="selected" value="' . $myrow['loccode'] . '">' . $myrow['locationname']  . '</option>';
		} else {
			echo '<option value="' . $myrow['loccode'] . '">' . $myrow['locationname']  . '</option>';
		}
	}

	echo '</select>';
	echo ' <select name="OpenOrClosed">';
	if ($_POST['OpenOrClosed']==1){
		echo '<option selected="selected" value="1">' .  _('Closed Shipments Only')  . '</option>';
		echo '<option value="0">' .  _('Open Shipments Only')  . '</option>';
	} else {
		$_POST['OpenOrClosed']=0;
		echo '<option value="1">' .  _('Closed Shipments Only')  . '</option>';
		echo '<option selected="selected" value="0">' .  _('Open Shipments Only')  . '</option>';
	}
	echo '</select></td></tr></table>';

	echo '<br />
			<div class="centre">
				<input type="submit" name="SearchShipments" value="'. _('Search Shipments'). '" />
			</div>
			<br />';
}

$SQL="SELECT categoryid,
		categorydescription
	FROM stockcategory
	WHERE stocktype<>'D'
	ORDER BY categorydescription";
$result1 = DB_query($SQL,$db);

echo '<table class="selection">';
echo '<tr>
		<th colspan="5"><h3>' . _('To search for shipments for a specific part use the part selection facilities below') . '</h3></th>
	</tr>
	<tr>
		<td>' . _('Select a stock category') . ':
			<select name="StockCat">';

while ($myrow1 = DB_fetch_array($result1)) {
	if (isset($_POST['StockCat']) and $myrow1['categoryid']==$_POST['StockCat']){
		echo '<option selected="selected" value="'. $myrow1['categoryid'] . '">' . $myrow1['categorydescription']  . '</option>';
	} else {
		echo '<option value="'. $myrow1['categoryid'] . '">' . $myrow1['categorydescription']  . '</option>';
	}
}
echo '</select></td>
		<td>' . _('Enter text extracts in the') . '<b> ' . _('description') . '</b>:</td>
		<td><input type="text" name="Keywords" size="20" maxlength="25" /></td>
	</tr>
	<tr>
		<td></td>
		<td><b>' . _('OR') . ' </b> ' . _('Enter extract of the') . ' <b> ' . _('Stock Code') . '</b>:</td>
		<td><input type="text" name="StockCode" size="15" maxlength="18" /></td>
	</tr>
	</table>
	<br />';

echo '<div class="centre">
		<input type="submit" name="SearchParts" value="'._('Search Parts Now').'" />
		<input type="submit" name="ResetPart" value="'. _('Show All') .'" />
	</div>
	<br />';

if (isset($StockItemsResult)) {

	echo '<table class="selection">';
	$TableHeader = '<tr>
						<th>' .  _('Code') . '</th>
						<th>' .  _('Description') . '</th>
						<th>' .  _('On Hand') . '</th>
						<th>' .  _('Orders') . '<br />' . _('Outstanding') . '</th>
						<th>' .  _('Units') . '</th>
					</tr>';
	echo $TableHeader;

	$j = 1;
	$k=0; //row colour counter

	while ($myrow=DB_fetch_array($StockItemsResult)) {

		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
/*
Code	 Description	On Hand		 Orders Ostdg     Units		 Code	Description 	 On Hand     Orders Ostdg	Units	 */
		printf('<td><input type="submit" name="SelectedStockItem" value="%s" /></td>
				<td>%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td>%s</td></tr>',
				$myrow['stockid'],
				$myrow['description'],
				locale_number_format($myrow['qoh'],$myrow['decimalplaces']),
				locale_number_format($myrow['qord'],$myrow['decimalplaces']),
				$myrow['units']);

		$j++;
		If ($j == 15){
			$j=1;
			echo $TableHeader;
		}
//end of page full new headings if
	}
//end of while loop

	echo '</table>';

}
//end if stock search results to show
  else {

	//figure out the SQL required from the inputs available

	if (isset($ShiptRef) AND $ShiptRef !="") {
		$SQL = "SELECT shipments.shiptref,
				vessel,
				voyageref,
				suppliers.suppname,
				shipments.eta,
				shipments.closed
			FROM shipments INNER JOIN suppliers
				ON shipments.supplierid = suppliers.supplierid
			WHERE shipments.shiptref='". $ShiptRef . "'";
	} else {
		$SQL = "SELECT DISTINCT shipments.shiptref, vessel, voyageref, suppliers.suppname, shipments.eta, shipments.closed
			FROM shipments INNER JOIN suppliers
				ON shipments.supplierid = suppliers.supplierid
			INNER JOIN purchorderdetails
				ON purchorderdetails.shiptref=shipments.shiptref
			INNER JOIN purchorders
				ON purchorderdetails.orderno=purchorders.orderno";

		if (isset($SelectedSupplier)) {

			if (isset($SelectedStockItem)) {
					$SQL .= " WHERE purchorderdetails.itemcode='". $SelectedStockItem ."'
						AND shipments.supplierid='" . $SelectedSupplier ."'
						AND purchorders.intostocklocation = '". $_POST['StockLocation'] . "'
						AND shipments.closed='" . $_POST['OpenOrClosed'] . "'";
			} else {
				$SQL .= " WHERE shipments.supplierid='" . $SelectedSupplier ."'
					AND purchorders.intostocklocation = '". $_POST['StockLocation'] . "'
					AND shipments.closed='" . $_POST['OpenOrClosed'] ."'";
			}
		} else { //no supplier selected
			if (isset($SelectedStockItem)) {
				$SQL .= " WHERE purchorderdetails.itemcode='". $SelectedStockItem ."'
					AND purchorders.intostocklocation = '". $_POST['StockLocation'] . "'
					AND shipments.closed='" . $_POST['OpenOrClosed'] . "'";
			} else {
				$SQL .= " WHERE purchorders.intostocklocation = '". $_POST['StockLocation'] . "'
					AND shipments.closed='" . $_POST['OpenOrClosed'] . "'";
			}

		} //end selected supplier
	} //end not order number selected

	$ErrMsg = _('No shipments were returned by the SQL because');
	$ShipmentsResult = DB_query($SQL,$db,$ErrMsg);


	if (DB_num_rows($ShipmentsResult)>0){
		/*show a table of the shipments returned by the SQL */

		echo '<table width="95%" class="selection">';
		$TableHeader = '<tr>
							<th>' .  _('Shipment'). '</th>
							<th>' .  _('Supplier'). '</th>
							<th>' .  _('Vessel'). '</th>
							<th>' .  _('Voyage'). '</th>
							<th>' .  _('Expected Arrival'). '</th>
						</tr>';

		echo $TableHeader;

		$j = 1;
		$k=0; //row colour counter
		while ($myrow=DB_fetch_array($ShipmentsResult)) {


			if ($k==1){ /*alternate bgcolour of row for highlighting */
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k++;
			}

			$URL_Modify_Shipment = $RootPath . '/Shipments.php?SelectedShipment=' . $myrow['shiptref'];
			$URL_View_Shipment = $RootPath . '/ShipmentCosting.php?SelectedShipment=' . $myrow['shiptref'];

			$FormatedETA = ConvertSQLDate($myrow['eta']);
			/* ShiptRef   Supplier  Vessel  Voyage  ETA */

			if ($myrow['closed']==0){

				$URL_Close_Shipment = $URL_View_Shipment . '&amp;Close=Yes';

				printf('<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td><a href="%s">' . _('Costing') . '</a></td>
					<td><a href="%s">' . _('Modify') . '</a></td>
					<td><a href="%s"><b>' . _('Close') . '</b></a></td>
					</tr>',
					$myrow['shiptref'],
					$myrow['suppname'],
					$myrow['vessel'],
					$myrow['voyageref'],
					$FormatedETA,
					$URL_View_Shipment,
					$URL_Modify_Shipment,
					$URL_Close_Shipment);

			} else {
				printf('<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td><a href="%s">' . _('Costing') . '</a></td>
						</tr>',
						$myrow['shiptref'],
						$myrow['suppname'],
						$myrow['vessel'],
						$myrow['voyage'],
						$FormatedETA,
						$URL_View_Shipment);
			}
			$j++;
			If ($j == 15){
				$j=1;
				echo $TableHeader;
			}
		//end of page full new headings if
		}
		//end of while loop

		echo '</table>';
	} // end if shipments to show
}

echo '</div>
      </form>';
include('includes/footer.inc');
?>
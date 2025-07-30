<?php


include('includes/session.php');
$Title = _('Search Shipments');
$ViewTopic = 'Shipments';
$BookMark = '';
include('includes/header.php');
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
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';


if (isset($_POST['ResetPart'])) {
     unset($SelectedStockItem);
}

if (isset($ShiptRef) AND $ShiptRef!='') {
	if (!is_numeric($ShiptRef)){
		  echo '<br />';
		  prnMsg( _('The Shipment Number entered MUST be numeric') );
		  unset ($ShiptRef);
	} else {
		echo _('Shipment Number'). ' - '. $ShiptRef;
	}
} else {
	if (isset($SelectedSupplier)) {
		echo '<h3>' ._('For supplier'). ': '. $SelectedSupplier . ' ' . _('and'). '</h3>';
		echo '<input type="hidden" name="SelectedSupplier" value="'. $SelectedSupplier. '" />';
	}
	if (isset($SelectedStockItem)) {
		echo '<h3>', _('for the part'). ': ' . $SelectedStockItem . '</h3>';
		echo '<input type="hidden" name="SelectedStockItem" value="'. $SelectedStockItem. '" />';
	}
}

if (isset($_POST['SearchParts'])) {

	if ($_POST['Keywords'] AND $_POST['StockCode']) {
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

	if ($_POST['Keywords']) {
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

	$ErrMsg = _('No Stock Items were returned from the database because'). ' - '. DB_error_msg();
	$StockItemsResult = DB_query($SQL, $ErrMsg);

}

if (!isset($ShiptRef) or $ShiptRef==""){
	echo '<fieldset>
			<legend class="search">', _('Search Criteria'), '</legend>
			<field>
				<label for="ShiptRef">', _('Shipment Number'). ':</label>
				<input type="text" name="ShiptRef" maxlength="10" size="10" />
			</field>
			<field>
				<label for="StockLocation">', _('Into Stock Location').':</label>
				<select name="StockLocation"> ';
	$SQL = "SELECT loccode, locationname FROM locations";
	$ResultStkLocs = DB_query($SQL);
	while ($MyRow=DB_fetch_array($ResultStkLocs)){
		if (isset($_POST['StockLocation'])){
			if ($MyRow['loccode'] == $_POST['StockLocation']){
			echo '<option selected="selected" value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
			} else {
			echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
			}
		} elseif ($MyRow['loccode']==$_SESSION['UserStockLocation']){
			$_POST['StockLocation'] = $_SESSION['UserStockLocation'];
			echo '<option selected="selected" value="' . $MyRow['loccode'] . '">' . $MyRow['locationname']  . '</option>';
		} else {
			echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname']  . '</option>';
		}
	}

	echo '</select>
		</field>';
	echo '<field>
			<label for="OpenOrClosed">', _('Search For'), '</label>
			<select name="OpenOrClosed">';
	if (isset($_POST['OpenOrClosed']) AND $_POST['OpenOrClosed']==1){
		echo '<option selected="selected" value="1">' .  _('Closed Shipments Only')  . '</option>';
		echo '<option value="0">' .  _('Open Shipments Only')  . '</option>';
	} else {
		$_POST['OpenOrClosed']=0;
		echo '<option value="1">' .  _('Closed Shipments Only')  . '</option>';
		echo '<option selected="selected" value="0">' .  _('Open Shipments Only')  . '</option>';
	}
	echo '</select>
		</field>
	</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="SearchShipments" value="'. _('Search Shipments'). '" />
		</div>';
}

$SQL="SELECT categoryid,
		categorydescription
	FROM stockcategory
	WHERE stocktype<>'D'
	ORDER BY categorydescription";
$Result1 = DB_query($SQL);

echo '<fieldset>';
echo '<legend class="search">' . _('To search for shipments for a specific part use the part selection facilities below') . '</legend>
	<field>
		<label for="StockCat">' . _('Select a stock category') . ':</label>
		<select name="StockCat">';

while ($MyRow1 = DB_fetch_array($Result1)) {
	if (isset($_POST['StockCat']) and $MyRow1['categoryid']==$_POST['StockCat']){
		echo '<option selected="selected" value="'. $MyRow1['categoryid'] . '">' . $MyRow1['categorydescription']  . '</option>';
	} else {
		echo '<option value="'. $MyRow1['categoryid'] . '">' . $MyRow1['categorydescription']  . '</option>';
	}
}
echo '</select>
	</field>
	<field>
		<label for="Keywords">' . _('Enter text extracts in the') . '<b> ' . _('description') . '</b>:</label>
		<input type="text" name="Keywords" size="20" maxlength="25" />
	</field>
	<field>
		<label for="StockCode">' . '<b>' . _('OR') . ' </b>' . _('Enter extract of the') . ' <b> ' . _('Stock Code') . '</b>:</label>
		<input type="text" name="StockCode" size="15" maxlength="18" />
	</field>
	</fieldset>';

echo '<div class="centre">
		<input type="submit" name="SearchParts" value="'._('Search Parts Now').'" />
		<input type="submit" name="ResetPart" value="'. _('Show All') .'" />
	</div>';

if (isset($StockItemsResult)) {

	echo '<table class="selection">
			<tr>
				<th>' .  _('Code') . '</th>
				<th>' .  _('Description') . '</th>
				<th>' .  _('On Hand') . '</th>
				<th>' .  _('Orders') . '<br />' . _('Outstanding') . '</th>
				<th>' .  _('Units') . '</th>
				<th colspan="3"></th>
			</tr>';

	while ($MyRow=DB_fetch_array($StockItemsResult)) {
/*
Code	 Description	On Hand		 Orders Ostdg     Units		 Code	Description 	 On Hand     Orders Ostdg	Units	 */
		echo '<tr class="striped_row">
				<td><input type="submit" name="SelectedStockItem" value="', $MyRow['stockid'], '" /></td>
				<td>', $MyRow['description'], '</td>
				<td class="number">', locale_number_format($MyRow['qoh'],$MyRow['decimalplaces']), '</td>
				<td class="number">', locale_number_format($MyRow['qord'],$MyRow['decimalplaces']), '</td>
				<td>', $MyRow['units'], '</td>
			</tr>';

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
	$ShipmentsResult = DB_query($SQL,$ErrMsg);


	if (DB_num_rows($ShipmentsResult)>0){
		/*show a table of the shipments returned by the SQL */

		echo '<table width="95%" class="selection">
				<tr>
					<th>' .  _('Shipment'). '</th>
					<th>' .  _('Supplier'). '</th>
					<th>' .  _('Vessel'). '</th>
					<th>' .  _('Voyage'). '</th>
					<th>' .  _('Expected Arrival'). '</th>
					<th colspan="3"></th>
				</tr>';

		while ($MyRow=DB_fetch_array($ShipmentsResult)) {

			$URL_Modify_Shipment = $RootPath . '/Shipments.php?SelectedShipment=' . $MyRow['shiptref'];
			$URL_View_Shipment = $RootPath . '/ShipmentCosting.php?SelectedShipment=' . $MyRow['shiptref'];

			$FormatedETA = ConvertSQLDate($MyRow['eta']);
			/* ShiptRef   Supplier  Vessel  Voyage  ETA */

			if ($MyRow['closed']==0){

				$URL_Close_Shipment = $URL_View_Shipment . '&amp;Close=Yes';

				echo '<tr class="striped_row">
						<td>', $MyRow['shiptref'], '</td>
						<td>', $MyRow['suppname'], '</td>
						<td>', $MyRow['vessel'], '</td>
						<td>', $MyRow['voyageref'], '</td>
						<td>', $FormatedETA, '</td>
						<td><a href="', $URL_View_Shipment, '">' . _('Costing') . '</a></td>
						<td><a href="', $URL_Modify_Shipment, '">' . _('Modify') . '</a></td>
						<td><a href="', $URL_Close_Shipment, '"><b>' . _('Close') . '</b></a></td>
					</tr>';

			} else {
				echo '<tr class="striped_row">
						<td>', $MyRow['shiptref'], '</td>
						<td>', $MyRow['suppname'], '</td>
						<td>', $MyRow['vessel'], '</td>
						<td>', $MyRow['voyage'], '</td>
						<td>', $FormatedETA, '</td>
						<td><a href="', $URL_View_Shipment, '">' . _('Costing') . '</a></td>
						</tr>';
			}
		//end of page full new headings if
		}
		//end of while loop

		echo '</table>';
	} // end if shipments to show
}

echo '</div>
      </form>';
include('includes/footer.php');

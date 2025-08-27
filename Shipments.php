<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Shipments');
$ViewTopic = 'Shipments';
$BookMark = '';
include('includes/header.php');

include('includes/DefineShiptClass.php');
include('includes/SQL_CommonFunctions.php');

if (isset($_POST['ETA'])){$_POST['ETA'] = ConvertSQLDate($_POST['ETA']);}

if (isset($_GET['NewShipment']) and $_GET['NewShipment']=='Yes'){
	unset($_SESSION['Shipment']->LineItems);
	unset($_SESSION['Shipment']);
}

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/magnifier.png" title="' . __('Search') .
	'" alt="" />' . ' ' . $Title . '</p>';

if (!isset($_SESSION['SupplierID']) AND !isset($_SESSION['Shipment']) AND !isset($_GET['SelectedShipment'])){
	prnMsg( __('To set up a shipment') . ', ' . __('the supplier must first be selected from the Select Supplier page'), 'error');
	echo '<table class="selection">
			<tr><td class="menu_group_item">
			<li><a href="'. $RootPath . '/SelectSupplier.php">' . __('Select the Supplier') . '</a></li>
			</td></tr></table></div>';
	include('includes/footer.php');
	exit();
}

if (isset($_GET['SelectedShipment'])){

	if (isset($_SESSION['Shipment'])){
		unset ($_SESSION['Shipment']->LineItems);
		unset ($_SESSION['Shipment']);
	}

	$_SESSION['Shipment'] = new Shipment;

/*read in all the guff from the selected shipment into the Shipment Class variable - the class code is included in the main script before this script is included  */

	$ShipmentHeaderSQL = "SELECT shipments.supplierid,
								suppliers.suppname,
								shipments.eta,
								suppliers.currcode,
								shipments.vessel,
								shipments.voyageref,
								shipments.closed
							FROM shipments INNER JOIN suppliers
								ON shipments.supplierid = suppliers.supplierid
							WHERE shipments.shiptref = '" . $_GET['SelectedShipment'] . "'";

	$ErrMsg = __('Shipment').' '. $_GET['SelectedShipment'] . ' ' . __('cannot be retrieved because a database error occurred');
	$GetShiptHdrResult = DB_query($ShipmentHeaderSQL, $ErrMsg);

	if (DB_num_rows($GetShiptHdrResult)==0) {
		prnMsg( __('Unable to locate Shipment') . ' '. $_GET['SelectedShipment'] . ' ' . __('in the database'), 'error');
		include('includes/footer.php');
		exit();
	}

	if (DB_num_rows($GetShiptHdrResult)==1) {

		$MyRow = DB_fetch_array($GetShiptHdrResult);

		if ($MyRow['closed']==1){
			prnMsg( __('Shipment No.') .' '. $_GET['SelectedShipment'] .': '.
				__('The selected shipment is already closed and no further modifications to the shipment are possible'), 'error');
			include('includes/footer.php');
			exit();
		}
		$_SESSION['Shipment']->ShiptRef = $_GET['SelectedShipment'];
		$_SESSION['Shipment']->SupplierID = $MyRow['supplierid'];
		$_SESSION['Shipment']->SupplierName = $MyRow['suppname'];
		$_SESSION['Shipment']->CurrCode = $MyRow['currcode'];
		$_SESSION['Shipment']->ETA = $MyRow['eta'];
		$_SESSION['Shipment']->Vessel = $MyRow['vessel'];
		$_SESSION['Shipment']->VoyageRef = $MyRow['voyageref'];

/*now populate the shipment details records */

		$LineItemsSQL = "SELECT purchorderdetails.podetailitem,
					  				purchorders.orderno,
									purchorderdetails.itemcode,
									purchorderdetails.itemdescription,
									purchorderdetails.deliverydate,
									purchorderdetails.glcode,
									purchorderdetails.qtyinvoiced,
									purchorderdetails.unitprice,
									stockmaster.units,
									purchorderdetails.quantityord,
									purchorderdetails.quantityrecd,
									purchorderdetails.stdcostunit,
									stockmaster.actualcost as stdcost,
									purchorders.intostocklocation
							FROM purchorderdetails INNER JOIN stockmaster
								ON purchorderdetails.itemcode=stockmaster.stockid
							INNER JOIN purchorders
								ON purchorderdetails.orderno=purchorders.orderno
							WHERE purchorderdetails.shiptref='" . $_GET['SelectedShipment'] . "'";
		$ErrMsg = __('The lines on the shipment cannot be retrieved because'). ' - ' . DB_error_msg();
			$LineItemsResult = DB_query($LineItemsSQL, $ErrMsg);

		if (DB_num_rows($GetShiptHdrResult)==0) {
			prnMsg( __('Unable to locate lines for Shipment') . ' '. $_GET['SelectedShipment'] . ' ' . __('in the database'), 'error');
			include('includes/footer.php');
			exit();
		}

		if (DB_num_rows($LineItemsResult) > 0) {

			while ($MyRow=DB_fetch_array($LineItemsResult)) {

				if ($MyRow['stdcostunit']==0){
					$StandardCost =$MyRow['stdcost'];
				} else {
					$StandardCost =$MyRow['stdcostunit'];
				}

				$_SESSION['Shipment']->LineItems[$MyRow['podetailitem']] = new LineDetails(
					$MyRow['podetailitem'],
					$MyRow['orderno'],
					$MyRow['itemcode'],
					$MyRow['itemdescription'],
					$MyRow['qtyinvoiced'],
					$MyRow['unitprice'],
					$MyRow['units'],
					$MyRow['deliverydate'],
					$MyRow['quantityord'],
					$MyRow['quantityrecd'],
					$StandardCost);
		   } /* line Shipment from shipment details */

		   DB_data_Seek($LineItemsResult,0);
		   $MyRow=DB_fetch_array($LineItemsResult);
		   $_SESSION['Shipment']->StockLocation = $MyRow['intostocklocation'];

		} //end of checks on returned data set
	}
} // end of reading in the existing shipment


if (!isset($_SESSION['Shipment'])){

	$_SESSION['Shipment'] = new Shipment;

	$SQL = "SELECT suppname,
					currcode,
					decimalplaces AS currdecimalplaces
		FROM suppliers INNER JOIN currencies
		ON suppliers.currcode=currencies.currabrev
		WHERE supplierid='" . $_SESSION['SupplierID'] . "'";

	$ErrMsg = __('The supplier details for the shipment could not be retrieved because');
	$Result = DB_query($SQL, $ErrMsg);
	$MyRow = DB_fetch_array($Result);

	$_SESSION['Shipment']->SupplierID = $_SESSION['SupplierID'];
	$_SESSION['Shipment']->SupplierName = $MyRow['suppname'];
	$_SESSION['Shipment']->CurrCode = $MyRow['currcode'];
	$_SESSION['Shipment']->CurrDecimalPlaces = $MyRow['currdecimalplaces'];
	$_SESSION['Shipment']->ShiptRef = GetNextTransNo (31);
}

if (isset($_POST['Update'])
	OR (isset($_GET['Add'])
	AND $_SESSION['Shipment']->Closed==0)) { //user hit the update button

	$InputError = 0;
	if (isset($_POST['Update'])){

		if (!Is_Date($_POST['ETA'])){
			$InputError=1;
			prnMsg( __('The date of expected arrival of the shipment must be entered in the format') . ' ' .$_SESSION['DefaultDateFormat'], 'error');
		} elseif (Date1GreaterThanDate2($_POST['ETA'],Date($_SESSION['DefaultDateFormat']))==0){
			$InputError=1;
			prnMsg( __('An expected arrival of the shipment must be a date after today'), 'error');
		} else {
			$_SESSION['Shipment']->ETA = FormatDateForSQL($_POST['ETA']);
		}

		if (mb_strlen($_POST['Vessel'])<2){
			prnMsg( __('A reference to the vessel of more than 2 characters is expected'), 'error');
		}
		if (mb_strlen($_POST['VoyageRef'])<2){
			prnMsg( __('A reference to the voyage (or HAWB in the case of air-freight) of more than 2 characters is expected'), 'error');
		}
	} elseif(mb_strlen($_SESSION['Shipment']->Vessel)<2
			OR mb_strlen($_SESSION['Shipment']->VoyageRef)<2){
		prnMsg(__('Cannot add purchase order lines to the shipment unless the shipment is first initiated - hit update to setup the shipment first'),'info');
		$InputError = 1;
	}
	if ($InputError==0 AND !isset($_GET['Add'])){ //don't update vessel and voyage on adding a new PO line to the shipment
		$_SESSION['Shipment']->Vessel = $_POST['Vessel'];
		$_SESSION['Shipment']->VoyageRef = $_POST['VoyageRef'];
	}
/*The user hit the update the shipment button and there are some lines on the shipment*/

	if ($InputError == 0 AND (isset($_SESSION['Shipment']) OR isset($_GET['Add']))){

		$SQL = "SELECT shiptref FROM shipments WHERE shiptref =" . $_SESSION['Shipment']->ShiptRef;
		$Result = DB_query($SQL);
		if (DB_num_rows($Result)==1){
			$SQL = "UPDATE shipments SET vessel='" . $_SESSION['Shipment']->Vessel . "',
										voyageref='".  $_SESSION['Shipment']->VoyageRef . "',
										eta='" .  $_SESSION['Shipment']->ETA . "'
					WHERE shiptref ='" .  $_SESSION['Shipment']->ShiptRef . "'";

		} else {

			$SQL = "INSERT INTO shipments (shiptref,
							vessel,
							voyageref,
							eta,
							supplierid)
					VALUES ('" . $_SESSION['Shipment']->ShiptRef . "',
						'" . $_SESSION['Shipment']->Vessel . "',
						'".  $_SESSION['Shipment']->VoyageRef . "',
						'" . $_SESSION['Shipment']->ETA . "',
						'" . $_SESSION['Shipment']->SupplierID . "')"  ;

		}
		/*now update or insert as necessary */
		$Result = DB_query($SQL);

		/*now check that the delivery date of all PODetails are the same as the ETA as the shipment */
		foreach ($_SESSION['Shipment']->LineItems as $LnItm) {

			if (DateDiff(ConvertSQLDate($LnItm->DelDate),ConvertSQLDate($_SESSION['Shipment']->ETA),'d')!=0){

				$SQL = "UPDATE purchorderdetails
						SET deliverydate ='" . $_SESSION['Shipment']->ETA . "'
						WHERE podetailitem='" . $LnItm->PODetailItem . "'";

				$Result = DB_query($SQL);

				$_SESSION['Shipment']->LineItems[$LnItm->PODetailItem]->DelDate = $_SESSION['Shipment']->ETA;
			}
		}
		prnMsg( __('Updated the shipment record and delivery dates of order lines as necessary'), 'success');
		echo '<br />';
	} //error traps all passed ok

} //user hit Update

if (isset($_GET['Add'])
	AND $_SESSION['Shipment']->Closed==0
	AND $InputError==0){

	$SQL = "SELECT purchorderdetails.orderno,
					purchorderdetails.itemcode,
					purchorderdetails.itemdescription,
					purchorderdetails.unitprice,
					purchorderdetails.stdcostunit,
					stockmaster.actualcost as stdcost,
					purchorderdetails.quantityord,
					purchorderdetails.quantityrecd,
					purchorderdetails.deliverydate,
					stockmaster.units,
					stockmaster.decimalplaces,
					purchorderdetails.qtyinvoiced
			FROM purchorderdetails INNER JOIN stockmaster
			ON purchorderdetails.itemcode=stockmaster.stockid
			WHERE purchorderdetails.podetailitem='" . $_GET['Add'] . "'";

	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);

/*The variable StdCostUnit gets set when the item is first received and stored for all future transactions with this purchase order line - subsequent changes to the standard cost will not therefore stuff up variances resulting from the line which may have several entries in GL for each delivery drop if it has already been set from a delivery then use it otherwise use the current system standard */

	if ($MyRow['stdcostunit']==0){
		$StandardCost = $MyRow['stdcost'];
	}else {
		$StandardCost = $MyRow['stdcostunit'];
	}

	$_SESSION['Shipment']->Add_To_Shipment($_GET['Add'],
											$MyRow['orderno'],
											$MyRow['itemcode'],
											$MyRow['itemdescription'],
											$MyRow['qtyinvoiced'],
											$MyRow['unitprice'],
											$MyRow['units'],
											$MyRow['deliverydate'],
											$MyRow['quantityord'],
											$MyRow['quantityrecd'],
											$StandardCost,
											$MyRow['decimalplaces']);
}

if (isset($_GET['Delete']) AND $_SESSION['Shipment']->Closed==0){ //shipment is open and user hit delete on a line
	$_SESSION['Shipment']->Remove_From_Shipment($_GET['Delete']);
}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<fieldset>
		<legend>', __('Shipment Details'), '</legend>
		<field>
			<label for="ShiptRef">' .  __('Shipment').': </label>
			<fieldtext>' . $_SESSION['Shipment']->ShiptRef . '</fieldtext>
		</field>
		<field>
			<label>' .  __('From'). '</label
			<fieldtext>' . $_SESSION['Shipment']->SupplierName . '</fieldtext>
		</field>';

echo '<field>
		<label for="Vessel">' .  __('Vessel Name /Transport Agent'). ': </label>
		<input type="text" name="Vessel" maxlength="50" size="50" value="' . $_SESSION['Shipment']->Vessel . '" />
	</field>
	<field>
		<label for="VoyageRef">' . __('Voyage Ref / Consignment Note').': </label>
		<input type="text" name="VoyageRef" maxlength="20" size="20" value="' . $_SESSION['Shipment']->VoyageRef . '" />
	</field>';

if (isset($_SESSION['Shipment']->ETA)){
	$ETA = $_SESSION['Shipment']->ETA;
} else {
	$ETA ='';
}

echo '<field>
		<label for="ETA">' .  __('Expected Arrival Date (ETA)'). ': </label>';
if (isset($_SESSION['Shipment']->ETA)) {
	echo '<input type="date" name="ETA"  maxlength="10" size="11" value="' . $ETA . '" />';
} else {
	echo '<input type="date" name="ETA" maxlength="10" size="11" value="' . FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']), 'm', 1)) . '" />';
}
echo '<field>';

echo '<field>
		<label for="StockLocation">' .  __('Into Stock Location').':</label>';

if (count($_SESSION['Shipment']->LineItems)>0){

	if (!isset($_SESSION['Shipment']->StockLocation)){

		$SQL = "SELECT purchorders.intostocklocation
				FROM purchorders INNER JOIN purchorderdetails
				ON purchorders.orderno=purchorderdetails.orderno AND podetailitem = '" . key($_SESSION['Shipment']->LineItems) . "'";

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_row($Result);

		$_SESSION['Shipment']->StockLocation = $MyRow[0];
		$_POST['StockLocation']=$_SESSION['Shipment']->StockLocation;

   } else {

		$_POST['StockLocation']=$_SESSION['Shipment']->StockLocation;
   }
}


if (!isset($_SESSION['Shipment']->StockLocation)){

	echo '<select name="StockLocation">';

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
			echo '<option selected="selected" value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
		} else {
			echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
		}
	}

	if (!isset($_POST['StockLocation'])){
		$_POST['StockLocation'] = $_SESSION['UserStockLocation'];
	}

	echo '</select>';

} else {
	$SQL = "SELECT locationname FROM locations WHERE loccode='" . $_SESSION['Shipment']->StockLocation . "'";
	$ResultStkLocs = DB_query($SQL);
	$MyRow=DB_fetch_array($ResultStkLocs);
	echo '<input type="hidden" name="StockLocation" value="'.$_SESSION['Shipment']->StockLocation.'" />';
 	echo '<fieldtext>', $MyRow['locationname'], '</fieldtext>';
}

echo '</field>
	</fieldset>';

if (count($_SESSION['Shipment']->LineItems)>0){
	/* Always display all shipment lines */

	echo '<table class="selection">';
	echo '<tr><th colspan="9"><h3>' .  __('Order Lines On This Shipment'). '</h3></th></tr>';

	$TableHeader = '<tr>
						<th>' .  __('Order'). '</th>
						<th>' .  __('Item'). '</th>
						<th>' .  __('Quantity'). '<br />' .  __('Ordered'). '</th>
						<th>' .  __('Units'). '</th>
						<th>' .  __('Quantity') . '<br />' .  __('Received'). '</th>
						<th>' .  __('Quantity') . '<br />' .  __('Invoiced'). '</th>
						<th>' .  $_SESSION['Shipment']->CurrCode .' '. __('Price') . '</th>
						<th>' .  __('Current'). '<br />' .  __('Std Cost'). '</th>
					</tr>';

	echo  $TableHeader;

	/*show the line items on the shipment with the quantity being received for modification */

	$RowCounter =0;

	foreach ($_SESSION['Shipment']->LineItems as $LnItm) {

		if ($RowCounter==15){
			echo $TableHeader;
			$RowCounter =0;
		}
		$RowCounter++;

		echo '<tr class="striped_row">
			<td>' . $LnItm->OrderNo . '</td>
			<td>' .  $LnItm->StockID .' - '. $LnItm->ItemDescription. '</td><td class="number">' . locale_number_format($LnItm->QuantityOrd,$LnItm->DecimalPlaces) . '</td>
			<td>' .  $LnItm->UOM  . '</td>
			<td class="number">' . locale_number_format($LnItm->QuantityRecd,$LnItm->DecimalPlaces) . '</td>
			<td class="number">' . locale_number_format($LnItm->QtyInvoiced,$LnItm->DecimalPlaces) . '</td>
			<td class="number">' . locale_number_format($LnItm->UnitPrice, $_SESSION['Shipment']->CurrDecimalPlaces) . '</td>
			<td class="number">' . locale_number_format($LnItm->StdCostUnit,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
			<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?Delete=' . $LnItm->PODetailItem . '">' .  __('Delete'). '</a></td>
			</tr>';
	}//for each line on the shipment
	echo '</table>';
}//there are lines on the shipment

echo '<div class="centre">
			<input type="submit" name="Update" value="'. __('Update Shipment Details') . '" />
		</div>';

if (!isset($_POST['StockLocation'])) {
	$_POST['StockLocation'] =$_SESSION['Shipment']->StockLocation;
}

$SQL = "SELECT purchorderdetails.podetailitem,
				purchorders.orderno,
				purchorderdetails.itemcode,
				purchorderdetails.itemdescription,
				purchorderdetails.unitprice,
				purchorderdetails.quantityord,
				purchorderdetails.quantityrecd,
				purchorderdetails.deliverydate,
				stockmaster.units,
				stockmaster.decimalplaces
			FROM purchorderdetails INNER JOIN purchorders
				ON purchorderdetails.orderno=purchorders.orderno
				INNER JOIN stockmaster
			ON purchorderdetails.itemcode=stockmaster.stockid
			WHERE qtyinvoiced=0
			AND purchorders.status <> 'Cancelled'
			AND purchorders.status <> 'Rejected'
			AND purchorders.supplierno ='" . $_SESSION['Shipment']->SupplierID . "'
			AND purchorderdetails.shiptref=0
			AND purchorders.intostocklocation='" . $_POST['StockLocation'] . "'";

$Result = DB_query($SQL);

if (DB_num_rows($Result)>0){

	echo '<table cellpadding="2" class="selection">';
	echo '<tr>
			<th colspan="7"><h3>' .  __('Possible Order Lines To Add To This Shipment') . '</h3></th>
		</tr>';

	$TableHeader = '<tr>
						<th>' .  __('Order') . '</th>
						<th>' .  __('Item') . '</th>
						<th>' .  __('Quantity') . '<br />' .  __('Ordered') . '</th>
						<th>' .  __('Units') . '</th>
						<th>' .  __('Quantity') . '<br />' .  __('Received') . '</th>
						<th>' .  __('Delivery') . '<br />' .  __('Date') . '</th>
					</tr>';

	echo  $TableHeader;

	/*show the PO items that could be added to the shipment */

	$RowCounter =0;

	while ($MyRow=DB_fetch_array($Result)){

		if ($RowCounter==15){
			echo $TableHeader;
			$RowCounter =0;
		}
		$RowCounter++;

		echo '<tr class="striped_row">
				<td>' . $MyRow['orderno'] . '</td>
				<td>' . $MyRow['itemcode'] . ' - ' . $MyRow['itemdescription'] . '</td>
				<td class="number">' . locale_number_format($MyRow['quantityord'],$MyRow['decimalplaces']) . '</td>
				<td>' . $MyRow['units'] . '</td>
				<td class="number">' . locale_number_format($MyRow['quantityrecd'],$MyRow['decimalplaces']) . '</td>
				<td class="number">' . ConvertSQLDate($MyRow['deliverydate']) . '</td>
				<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?' . 'Add=' . $MyRow['podetailitem'] . '">' .  __('Add') . '</a></td>
			</tr>';

	}
	echo '</table>';
}

echo '</div>
	  </form>';

include('includes/footer.php');

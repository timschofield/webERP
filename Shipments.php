<?php

/* $Revision: 1.14 $ */

$PageSecurity = 11;
include('includes/DefineShiptClass.php');
include('includes/session.inc');
$title = _('Shipments');
include('includes/header.inc');

include('includes/SQL_CommonFunctions.inc');

if ($_GET['NewShipment']=='Yes'){
	unset($_SESSION['Shipment']->LineItems);
	unset($_SESSION['Shipment']);
}

if (!isset($_SESSION['SupplierID']) AND !isset($_SESSION['Shipment'])){
	prnMsg( _('To set up a shipment') . ', ' . _('the supplier must first be selected from the Select Supplier page'), 'error');
        echo '<br><table class="table_index">
                <tr><td class="menu_group_item">
                <li><a href="'. $rootpath . '/SelectSupplier.php?'.SID .'">' . _('Select the Supplier') . '</a></li>
                </td></tr></table></div><br><br><br>';
        include('includes/footer.inc');
        exit;
}

if (isset($_GET['SelectedShipment'])){

	if (isset($_SESSION['Shipment'])){
              unset ($_SESSION['Shipment']->LineItems);
              unset ($_SESSION['Shipment']);
	}

       $_SESSION['Shipment'] = new Shipment;

       $_SESSION['Shipment']->GLLink = $_SESSION['CompanyRecord']['gllink_stock'];

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
				WHERE shipments.shiptref = " . $_GET['SelectedShipment'];

       $ErrMsg = _('Shipment').' '. $_GET['SelectedShipment'] . ' ' . _('cannot be retrieved because a database error occurred');
       $GetShiptHdrResult = DB_query($ShipmentHeaderSQL,$db, $ErrMsg);

       if (DB_num_rows($GetShiptHdrResult)==0) {
		prnMsg ( _('Unable to locate Shipment') . ' '. $_GET['SelectedShipment'] . ' ' . _('in the database'), 'error');
	        include('includes/footer.inc');
        	exit();
	}

       if (DB_num_rows($GetShiptHdrResult)==1) {

              $myrow = DB_fetch_array($GetShiptHdrResult);

	      if ($myrow['closed']==1){
			echo '<br>';
			prnMsg( _('Shipment No.') .' '. $_GET['SelectedShipment'] .': '.
				_('The selected shipment is already closed and no further modifications to the shipment are possible'), 'error');
			include('includes/footer.inc');
			exit;
	      }
              $_SESSION['Shipment']->ShiptRef = $_GET['SelectedShipment'];
              $_SESSION['Shipment']->SupplierID = $myrow['supplierid'];
              $_SESSION['Shipment']->SupplierName = $myrow['suppname'];
              $_SESSION['Shipment']->CurrCode = $myrow['currcode'];
              $_SESSION['Shipment']->ETA = $myrow['eta'];
              $_SESSION['Shipment']->Vessel = $myrow['vessel'];
              $_SESSION['Shipment']->VoyageRef = $myrow['voyageref'];



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
					stockmaster.materialcost+stockmaster.labourcost+stockmaster.overheadcost as stdcost,
					purchorders.intostocklocation
				FROM purchorderdetails INNER JOIN stockmaster
					ON purchorderdetails.itemcode=stockmaster.stockid
				INNER JOIN purchorders
					ON purchorderdetails.orderno=purchorders.orderno
				WHERE purchorderdetails.shiptref=" . $_GET['SelectedShipment'];
	      $ErrMsg = _('The lines on the shipment cannot be retrieved because'). ' - ' . DB_error_msg($db);
              $LineItemsResult = db_query($LineItemsSQL,$db, $ErrMsg);

        if (DB_num_rows($GetShiptHdrResult)==0) {
                prnMsg ( _('Unable to locate lines for Shipment') . ' '. $_GET['SelectedShipment'] . ' ' . _('in the database'), 'error');
                include('includes/footer.inc');
                exit();
        }

        if (db_num_rows($LineItemsResult) > 0) {

			while ($myrow=db_fetch_array($LineItemsResult)) {

				if ($myrow['stdcostunit']==0){
					$StandardCost =$myrow['stdcost'];
				} else {
					$StandardCost =$myrow['stdcostunit'];
				}

				$_SESSION['Shipment']->LineItems[$myrow['podetailitem']] = new 			LineDetails($myrow['podetailitem'],
								 $myrow['orderno'],
								 $myrow['itemcode'],
								 $myrow['itemdescription'],
								 $myrow['qtyinvoiced'],
								 $myrow['unitprice'],
								 $myrow['units'],
								 $myrow['deliverydate'],
								 $myrow['quantityord'],
								 $myrow['quantityrecd'],
								 $StandardCost);
		   } /* line Shipment from shipment details */

		   DB_data_Seek($LineItemsResult,0);
		   $myrow=DB_fetch_array($LineItemsResult);
		   $_SESSION['Shipment']->StockLocation = $myrow['intostocklocation'];

              } //end of checks on returned data set
       }
} // end of reading in the existing shipment


if (!isset($_SESSION['Shipment'])){

	$_SESSION['Shipment'] = new Shipment;
	
	$sql = "SELECT suppname, 
			currcode 
		FROM suppliers 
		WHERE supplierid='" . $_SESSION['SupplierID'] . "'";

	$ErrMsg = _('The supplier details for the shipment could not be retrieved because');
	$result = DB_query($sql,$db,$ErrMsg);
	$myrow = DB_fetch_row($result);

	$_SESSION['Shipment']->SupplierID = $_SESSION['SupplierID'];
	$_SESSION['Shipment']->SupplierName = $myrow[0];
	$_SESSION['Shipment']->CurrCode = $myrow[1];
	$_SESSION['Shipment']->ShiptRef = GetNextTransNo (31, $db);
}




if (isset($_POST['Update']) OR (isset($_GET['Add']) AND $_SESSION['Shipment']->Closed==0)) { //user hit the update button

	if (isset($_POST['Update'])){
		$_SESSION['Shipment']->Vessel = $_POST['Vessel'];
		$_SESSION['Shipment']->VoyageRef = $_POST['VoyageRef'];
	
		$InputError = 0;
	
		if (!Is_Date($_POST['ETA'])){
			$InputError=1;
			prnMsg( _('The date of expected arrival of the shipment must be entered in the format') . ' ' .$_SESSION['DefaultDateFormat'], 'error');
		} elseif (Date1GreaterThanDate2($_POST['ETA'],Date($_SESSION['DefaultDateFormat']))==0){
			$InputError=1;
			prnMsg( _('An expected arrival of the shipment must be a date after today'), 'error');
		} else {
			$_SESSION['Shipment']->ETA = FormatDateForSQL($_POST['ETA']);
		}

		if (strlen($_POST['Vessel'])<2){
			prnMsg( _('A reference to the vessel of more than 2 characters is expected'), 'error');
		}
		if (strlen($_POST['VoyageRef'])<2){
			prnMsg( _('A reference to the voyage (or HAWB in the case of air-freight) of more than 2 characters is expected'), 'error');
		}
	} elseif(strlen($_SESSION['Shipment']->Vessel)<2 OR strlen($_SESSION['Shipment']->VoyageRef)<2){
		prnMsg(_('Cannot add purchase order lines to the shipment unless the shipment is first initiated - hit update to setup the shipment first'),'info');
		$InputError = 1;
	}

/*The user hit the update the shipment button and there are some lines on the shipment*/
	if ($InputError == 0 AND (count($_SESSION['Shipment']->LineItems) > 0 OR isset($_GET['Add']))){
		$sql = "SELECT shiptref FROM shipments WHERE shiptref =" . $_SESSION['Shipment']->ShiptRef;
		$result = DB_query($sql,$db);
		if (DB_num_rows($result)==1){
			$sql = "UPDATE shipments SET vessel='" . $_SESSION['Shipment']->Vessel . "',
							voyageref='".  $_SESSION['Shipment']->VoyageRef . "',
							eta='" .  $_SESSION['Shipment']->ETA . "'
					WHERE shiptref =" .  $_SESSION['Shipment']->ShiptRef;

		} else {
			
			$sql = "INSERT INTO shipments (shiptref,
							vessel,
							voyageref,
							eta,
							supplierid)
					VALUES (" . $_SESSION['Shipment']->ShiptRef . ",
						'" . $_SESSION['Shipment']->Vessel . "',
						'".  $_SESSION['Shipment']->VoyageRef . "',
						'" . $_SESSION['Shipment']->ETA . "',
						'" . $_SESSION['Shipment']->SupplierID . "')"  ;

		}
		/*now update or insert as necessary */
		$result = DB_query($sql,$db);

		/*now check that the delivery date of all PODetails are the same as the ETA as the shipment */
		foreach ($_SESSION['Shipment']->LineItems as $LnItm) {

			if (DateDiff(ConvertSQLDate($LnItm->DelDate),ConvertSQLDate($_SESSION['Shipment']->ETA),'d')!=0){

				$sql = "UPDATE purchorderdetails 
						SET deliverydate ='" . $_SESSION['Shipment']->ETA . "' 
					WHERE podetailitem=" . $LnItm->PODetailItem;

				$result = DB_query($sql,$db);

				$_SESSION['Shipment']->LineItems[$LnItm->PODetailItem]->DelDate = $_SESSION['Shipment']->ETA;

			}
		}
		echo '<br>';
		prnMsg( _('Updated the shipment record and delivery dates of order lines as necessary'), 'success');
	} //error traps all passed ok
	
} //user hit Update

if (isset($_GET['Add']) AND $_SESSION['Shipment']->Closed==0 AND $InputError==0){

	$sql = "SELECT purchorderdetails.orderno,
			purchorderdetails.itemcode,
			purchorderdetails.itemdescription,
			purchorderdetails.unitprice,
			purchorderdetails.stdcostunit,
			stockmaster.materialcost+stockmaster.labourcost+stockmaster.overheadcost as stdcost,
			purchorderdetails.quantityord,
			purchorderdetails.quantityrecd,
			purchorderdetails.deliverydate,
			stockmaster.units,
			purchorderdetails.qtyinvoiced
		FROM purchorderdetails INNER JOIN stockmaster
			ON purchorderdetails.itemcode=stockmaster.stockid
		WHERE purchorderdetails.podetailitem=" . $_GET['Add'];

	$result = DB_query($sql,$db);
	$myrow = DB_fetch_array($result);

/*The variable StdCostUnit gets set when the item is first received and stored for all future transactions with this purchase order line - subsequent changes to the standard cost will not therefore stuff up variances resulting from the line which may have several entries in GL for each delivery drop if it has already been set from a delivery then use it otherwise use the current system standard */

	if ($myrow['stdcostunit']==0){
		$StandardCost = $myrow['stdcost'];
	}else {
		$StandardCost = $myrow['stdcostunit'];
	}

	$_SESSION['Shipment']->add_to_shipment($_GET['Add'],
								$myrow['orderno'],
								$myrow['itemcode'],
								$myrow['itemdescription'],
								$myrow['qtyinvoiced'],
								$myrow['unitprice'],
								$myrow['units'],
								$myrow['deliverydate'],
								$myrow['quantityord'],
								$myrow['quantityrecd'],
								$StandardCost,
								$db);
}

if (isset($_GET['Delete']) AND $_SESSION['Shipment']->Closed==0){ //shipment is open and user hit delete on a line
	$_SESSION['Shipment']->remove_from_shipment($_GET['Delete'],$db);
}



echo '<form action="' . $_SERVER['PHP_SELF'] . '?' . SID . '" method="POST">';

echo '<table><tr><td><b>'. _('Shipment').': </td><td><b>' . $_SESSION['Shipment']->ShiptRef . '</b></td>
		<td><b>'. _('From'). ' ' . $_SESSION['Shipment']->SupplierName . '</b></td></tr>';

echo '<tr><td>'. _('Vessel Name /Transport Agent'). ': </td>
	<td colspan=3><input type=Text name="Vessel" maxlength=50 size=50 VALUE="' . $_SESSION['Shipment']->Vessel . '"></td>
	<td>'._('Voyage Ref / Consignment Note').': </td>
	<td><input type=Text name="VoyageRef" maxlength=20 size=20 VALUE="' . $_SESSION['Shipment']->VoyageRef . '"></td>
</tr>';

if (isset($_SESSION['Shipment']->ETA)){
	$ETA = ConvertSQLDate($_SESSION['Shipment']->ETA);
} else {
	$ETA ='';
}

echo '<tr><td>'. _('Expected Arrival Date (ETA)'). ': </td>';
if (isset($_SESSION['Shipment']->ETA)) {
	echo '<td><input type=Text class="date" alt='.$_SESSION['DefaultDateFormat'].' name="ETA" maxlength=10 size=10 VALUE="' . $ETA . '"></td>';
} else {
	echo '<td><input type=Text class="date" alt='.$_SESSION['DefaultDateFormat'].' name="ETA" maxlength=10 size=10 VALUE="' . Date($_SESSION['DefaultDateFormat']) . '"></td>';	
}
echo '<td>'. _('Into').' ';

if (count($_SESSION['Shipment']->LineItems)>0){

   if (!isset($_SESSION['Shipment']->StockLocation)){

	$sql = "SELECT purchorders.intostocklocation
			FROM purchorders INNER JOIN purchorderdetails
				ON purchorders.orderno=purchorderdetails.orderno and podetailitem = " . key($_SESSION['Shipment']->LineItems);

	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);

	$_SESSION['Shipment']->StockLocation = $myrow[0];
	$_POST['StockLocation']=$_SESSION['Shipment']->StockLocation;

   } else {

	$_POST['StockLocation']=$_SESSION['Shipment']->StockLocation;
   }
}


if (!isset($_SESSION['Shipment']->StockLocation)){

	echo _('Stock Location').': <select name="StockLocation">';

	$sql = "SELECT loccode, locationname FROM locations";

	$resultStkLocs = DB_query($sql,$db);

	while ($myrow=DB_fetch_array($resultStkLocs)){

		if (isset($_POST['StockLocation'])){
			if ($myrow['loccode'] == $_POST['StockLocation']){
				echo '<option selected Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
			} else {
				echo '<option Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
			}
		} elseif ($myrow['loccode']==$_SESSION['UserStockLocation']){
			echo '<option selected Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
		} else {
			echo '<option Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
		}
	}

	if (!isset($_POST['StockLocation'])){
		$_POST['StockLocation'] = $_SESSION['UserStockLocation'];
	}

	echo '</select>';

} else {
	$sql = "SELECT locationname FROM locations WHERE loccode='" . $_SESSION['Shipment']->StockLocation . "'";
	$resultStkLocs = DB_query($sql,$db);
	$myrow=DB_fetch_array($resultStkLocs);
 	echo $myrow['locationname'];
}

echo '</td></tr></table>';

if (count($_SESSION['Shipment']->LineItems)>0){
	/* Always display all shipment lines */
	
	echo '<b><div class="centre"><font color=BLUE>'. _('Order Lines On This Shipment'). '</font></div></b>';
	echo '<table cellpadding=2 colspan=7 border=0>';
		
	$TableHeader = '<tr>
			<th>'. _('Order'). '</th>
			<th>'. _('Item'). '</th>
			<th>'. _('Quantity'). '<br>'. _('Ordered'). '</th>
			<th>'. _('Units'). '</th>
			<th>'. _('Quantity').'<br>'. _('Received'). '</th>
			<th>'. _('Quantity').'<br>'. _('Invoiced'). '</th>
			<th>'. $_SESSION['Shipment']->CurrCode .' '. _('Price') . '</th>
			<th>'. _('Current'). '<br>'. _('Std Cost'). '</th></tr>';
		
	echo  $TableHeader;
		
	/*show the line items on the shipment with the quantity being received for modification */
		
	$k=0; //row colour counter
	$RowCounter =0;
		
	foreach ($_SESSION['Shipment']->LineItems as $LnItm) {
	
		if ($RowCounter==15){
			echo $TableHeader;
			$RowCounter =0;
		}
		$RowCounter++;
	
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
	
	
		echo '<td>'.$LnItm->OrderNo.'</td>
			<td>'. $LnItm->StockID .' - '. $LnItm->ItemDescription. '</td><td align=right>' . number_format($LnItm->QuantityOrd,2) . '</td>
			<td>'. $LnItm->UOM .'</td>
			<td align=right>' . number_format($LnItm->QuantityRecd,2) . '</td>
			<td align=right>' . number_format($LnItm->QtyInvoiced,2) . '</td>
			<td align=right>' . number_format($LnItm->UnitPrice,2) . '</td>
			<td align=right>' . number_format($LnItm->StdCostUnit,2) . '</td>
			<td><a href="' . $_SERVER['PHP_SELF'] . '?' . SID . 'Delete=' . $LnItm->PODetailItem . '">'. _('Delete'). '</a></td>
			</tr>';
	}//for each line on the shipment
echo '</table>';
}//there are lines on the shipment

echo '<br><div class="centre"><input type=submit name="Update" Value="'. _('Update Shipment Details') . '"></div><p>';

echo '<hr>';

$sql = "SELECT purchorderdetails.podetailitem,
		purchorders.orderno,
		purchorderdetails.itemcode,
		purchorderdetails.itemdescription,
		purchorderdetails.unitprice,
		purchorderdetails.quantityord,
		purchorderdetails.quantityrecd,
		purchorderdetails.deliverydate,
		stockmaster.units
	FROM purchorderdetails INNER JOIN purchorders
		ON purchorderdetails.orderno=purchorders.orderno
		INNER JOIN stockmaster
			ON purchorderdetails.itemcode=stockmaster.stockid
	WHERE qtyinvoiced=0
	AND purchorders.supplierno ='" . $_SESSION['Shipment']->SupplierID . "'
	AND purchorderdetails.shiptref=0
	AND purchorders.intostocklocation='" . $_POST['StockLocation'] . "'";

$result = DB_query($sql,$db);

if (DB_num_rows($result)>0){

	echo '<b><div class="centre"><font color=BLUE>'. _('Possible Order Lines To Add To This Shipment').'</font></div></b>';
	echo '<table cellpadding=2 colspan=7 BORDER=0>';

	$TableHeader = '<tr>
			<th>'. _('Order').'</th>
			<th>'. _('Item').'</th>
			<th>'. _('Quantity').'<br>'. _('Ordered').'</th>
			<th>'. _('Units').'</th>
			<th>'. _('Quantity').'<br>'. _('Received').'</th>
			<th>'. _('Delivery').'<br>'. _('Date').'</th>
			</tr>';

	echo  $TableHeader;

	/*show the PO items that could be added to the shipment */

	$k=0; //row colour counter
	$RowCounter =0;

	while ($myrow=DB_fetch_array($result)){

		if ($RowCounter==15){
			echo $TableHeader;
			$RowCounter =0;
		}
		$RowCounter++;

		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}

		echo '<td>' . $myrow['orderno'] . '</td>
			<td>' . $myrow['itemcode'] . ' - ' . $myrow['itemdescription'] . '</td>
			<td align=right>' . number_format($myrow['quantityord'],2) . '</td>
			<td>' . $myrow['units'] . '</td>
			<td align=right>' . number_format($myrow['quantityrecd'],2) . '</td>
			<td align=right>' . ConvertSQLDate($myrow['deliverydate']) . '</td>
			<td><a href="' . $_SERVER['PHP_SELF'] . '?' . SID . '&Add=' . $myrow['podetailitem'] . '">'. _('Add').'</a></td>
			</tr>';

	}
	echo '</table>';
}

echo '</form>';

include('includes/footer.inc');
?>

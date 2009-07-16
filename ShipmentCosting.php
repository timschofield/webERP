<?php

/* $Revision: 1.17 $ */

$PageSecurity = 11;

include('includes/session.inc');
$title = _('Shipment Costing');
/* Session started in header.inc for password checking and authorisation level check */
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

if (isset($_GET['NewShipment']) and $_GET['NewShipment']=='Yes'){
	unset($_SESSION['Shipment']->LineItems);
	unset($_SESSION['Shipment']);
}

if (!isset($_GET['SelectedShipment'])){

	echo '<br>';
	prnMsg( _('This page is expected to be called with the shipment number to show the costing for'), 'error');
	include ("includes/footer.inc");
	exit;
}

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
	echo '<br>';
	prnMsg( _('Shipment') . ' ' . $_GET['SelectedShipment'] . ' ' . _('could not be located in the database') , 'error');
	include ("includes/footer.inc");
	exit;
}

$HeaderData = DB_fetch_array($GetShiptHdrResult);
echo '<br>';
echo '<table>
	<tr>
		<td><b>'. _('Shipment') .': </td>
		<td><b>' . $_GET['SelectedShipment'] . '</b></td>
		<td><b>'. _('From').' ' . $HeaderData['suppname'] . '</b></td>
	</tr>';

echo '<tr><td>' . _('Vessel'). ': </td>
	<td>' . $HeaderData['vessel'] . '</td>
	<td>'. _('Voyage Ref'). ': </td>
	<td>' . $HeaderData['voyageref'] . '</td></tr>';

echo '<tr><td>' . _('Expected Arrival Date (ETA)') . ': </td>
	<td>' . ConvertSQLDate($HeaderData['eta']) . '</td></tr>';

echo '</table>';

/*Get the total non-stock item shipment charges */

$sql = "SELECT SUM(value) FROM shipmentcharges WHERE stockid='' AND shiptref =" . $_GET['SelectedShipment'];

$ErrMsg = _('Shipment') . ' ' . $_GET['SelectedShipment'] . ' ' . _('general costs cannot be retrieved from the database');
$GetShiptCostsResult = DB_query($sql,$db, $ErrMsg);
if (DB_num_rows($GetShiptCostsResult)==0) {
	echo '<br>';
	prnMsg ( _('No General Cost Records exist for Shipment') . ' ' . $_GET['SelectedShipment'] . ' ' . _('in the database'), 'error');
	include ('includes/footer.inc');
	exit;
}

$myrow = DB_fetch_row($GetShiptCostsResult);

$TotalCostsToApportion = $myrow[0];

/*Now Get the total of stock items invoiced against the shipment */

$sql = "SELECT SUM(value) FROM shipmentcharges WHERE stockid<>'' AND shiptref =" . $_GET['SelectedShipment'];

$ErrMsg = _('Shipment') . ' ' . $_GET['SelectedShipment'] . ' ' . _('Item costs cannot be retrieved from the database');
$GetShiptCostsResult = DB_query($sql,$db);
if (DB_error_no($db) !=0 OR DB_num_rows($GetShiptCostsResult)==0) {
	echo '<br>';
	prnMsg ( _('No Item Cost Records exist for Shipment') . ' ' . $_GET['SelectedShipment'] . ' ' . _('in the database'), 'error');
	include ('includes/footer.inc');
	exit;
}

$myrow = DB_fetch_row($GetShiptCostsResult);

$TotalInvoiceValueOfShipment = $myrow[0];

/*Now get the lines on the shipment */

$LineItemsSQL = "SELECT purchorderdetails.itemcode,
			purchorderdetails.itemdescription,
			SUM(purchorderdetails.qtyinvoiced) as totqtyinvoiced,
			SUM(purchorderdetails.quantityrecd) as totqtyrecd
	        FROM purchorderdetails
		WHERE purchorderdetails.shiptref=" . $_GET['SelectedShipment'] . "
		GROUP BY purchorderdetails.itemcode,
		      purchorderdetails.itemdescription";

$ErrMsg = _('The lines on the shipment could not be retrieved from the database');
$LineItemsResult = db_query($LineItemsSQL,$db, $ErrMsg);

if (db_num_rows($LineItemsResult) > 0) {

	if (isset($_POST['Close'])){
		while ($myrow=DB_fetch_array($LineItemsResult)){
                      if ($myrow['totqtyinvoiced'] < $myrow['totqtyrecd']){
                         prnMsg(_('Cannot close a shipment where the quantity received is more than the quantity invoiced. Check the item') . ' ' . $myrow['itemcode'] . ' - ' . $myrow['itemdescription'],'warn');
                         unset($_POST['Close']);
                      }
                }
                DB_data_seek($LineItemsResult,0);
 	}


        if (isset($_POST['Close'])){
        /*Set up a transaction to buffer all updates or none */
		$result = DB_Txn_Begin($db);
		$PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']), $db);
        }

        echo '<table cellpadding=2 colspan=7 BORDER=0>';

	$TableHeader = '<tr>	<th>'. _('Item'). '</th>
				<th>'. _('Quantity'). '<br>'. _('Invoiced'). '</th>
				<th>'. _('Quantity'). '<br>'. _('Received'). '</th>
				<th>'. _('Invoiced'). '<br>'. _('Charges'). '</th>
				<th>'. _('Shipment'). '<br>'. _('Charges'). '</th>
				<th>'. _('Shipment'). '<br>'. _('Cost'). '</th>
				<th>'. _('Standard'). '<br>'. _('Cost'). '</th>
				<th>'. _('Variance'). '</th>
				<th>%</th></tr>';
	echo  $TableHeader;

	/*show the line items on the shipment with the value invoiced and shipt cost */

	$k=0; //row colour counter
        $TotalShiptVariance = 0;
	$RowCounter =0;

	while ($myrow=DB_fetch_array($LineItemsResult)) {

		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}

                $sql = "SELECT SUM(shipmentcharges.value) AS invoicedcharges
                             FROM shipmentcharges
                             WHERE shipmentcharges.stockid ='" . $myrow['itemcode'] . "'
                             AND shipmentcharges.shiptref=" . $_GET['SelectedShipment'];
                $ItemChargesResult = DB_query($sql,$db);
                $ItemChargesRow = DB_fetch_row($ItemChargesResult);
                $ItemCharges = $ItemChargesRow[0];

		if ($TotalInvoiceValueOfShipment>0){
			$PortionOfCharges = $TotalCostsToApportion *($ItemCharges/$TotalInvoiceValueOfShipment);
		} else {
			$PortionOfCharges = 0;
		}

		if ($myrow['totqtyinvoiced']>0){
			$ItemShipmentCost = ($ItemCharges+$PortionOfCharges)/$myrow['totqtyrecd'];
		} else {
			$ItemShipmentCost =0;
		}
		$sql = 'SELECT SUM(grns.stdcostunit*grns.qtyrecd) AS costrecd
		               FROM grns INNER JOIN purchorderdetails
		               ON grns.podetailitem=purchorderdetails.podetailitem
                 		WHERE purchorderdetails.shiptref=' . $_GET['SelectedShipment'] . "
                 		AND purchorderdetails.itemcode = '" . $myrow['itemcode'] . "'";

                $StdCostResult = DB_query($sql,$db);
                $StdCostRow = DB_fetch_row($StdCostResult);
                $CostRecd = $StdCostRow[0];
                $StdCostUnit = $StdCostRow[0]/$myrow['totqtyrecd'];

		if ($ItemShipmentCost !=0){
			$Variance = $StdCostUnit - $ItemShipmentCost;
		} else {
			$Variance =0;
		}

                $TotalShiptVariance += ($Variance *$myrow['totqtyinvoiced']);

		if ($StdCostUnit>0 ){
			$VariancePercentage = number_format(($Variance*100)/$StdCostUnit);
		} else {
			$VariancePercentage =100;
		}


		if ( isset($_POST['Close']) AND $Variance !=0){


                        if ($_SESSION['CompanyRecord']['gllink_stock']==1){
                              $StockGLCodes = GetStockGLCode($myrow['itemcode'],$db);
                        }

                        /*GL journals depend on the costing method used currently:
                             Standard cost - the price variance between the exisitng system cost and the shipment cost is taken as a variance
                             to the price varaince account
                             Weighted Average Cost - the price variance is taken to the stock account and the cost updated to ensure the GL
                             stock account ties up to the stock valuation
                        */

                        if ($_SESSION['WeightedAverageCosting'] == 1){   /* Do the WAvg journal and cost update */
                               	/*
                                First off figure out the new weighted average cost Need the following data:

                                How many in stock now
				The quantity being costed here - $myrow['qtyinvoiced']
				The cost of these items - $ItemShipmentCost
				*/

				$sql ='SELECT SUM(quantity) FROM locstock WHERE stockid="' . $myrow['itemcode'] . '"';
				$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The quantity on hand could not be retrieved from the database');
				$DbgMsg = _('The following SQL to retrieve the total stock quantity was used');
				$Result = DB_query($sql, $db, $ErrMsg, $DbgMsg);
				$QtyRow = DB_fetch_row($Result);
				$TotalQuantityOnHand = $QtyRow[0];


				/*The cost adjustment is the price variance / the total quantity in stock
				But that's only provided that the total quantity in stock is > the quantity charged on this invoice
                                */

                                $WriteOffToVariances =0;

                                if ($myrow['totqtyinvoiced'] > $TotalQuantityOnHand){

                                             /*So we need to write off some of the variance to variances and
                                             only the balance of the quantity in stock to go to stock value */

					     $WriteOffToVariances =  ($myrow['totqtyinvoiced'] - $TotalQuantityOnHand)
                                                                                       * ($ItemShipmentCost - $StdCostUnit);
                                 }


                                if ($_SESSION['CompanyRecord']['gllink_stock']==1){

				   /* If the quantity on hand is less the amount charged on this invoice then some must have been sold
                                       and the price variance on these must be written off to price variances*/


                                       if ($myrow['totqtyinvoiced'] > $TotalQuantityOnHand){

                                            $sql = "INSERT INTO gltrans (type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount)
              					VALUES (31,
	           					" . $_GET['SelectedShipment'] . ",
		        				'" . Date('Y-m-d') . "',
			        			" . $PeriodNo . ",
				         		" . $StockGLCodes['purchpricevaract'] . ",
					         	'" . $myrow['itemcode'] . ' ' . _('shipment cost') . ' ' .  number_format($ItemShipmentCost,2) . _('shipment quantity > stock held - variance write off') . "',
                                                         " . $WriteOffToVariances . ")";

                                            $ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The GL entry for the shipment variance posting for'). ' ' . $myrow['itemcode'] . ' '. _('could not be inserted into the database because');
	       		                    $result = DB_query($sql,$db, $ErrMsg,'',TRUE);

	                                }
        				/*Now post any remaining price variance to stock rather than price variances */
                                        $sql = "INSERT INTO gltrans (type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount)
              					VALUES (31,
	           					" . $_GET['SelectedShipment'] . ",
		        		    		'" . Date('Y-m-d') . "',
			        			" . $PeriodNo . ",
				         		" . $StockGLCodes['stockact'] . ",
					         	'" . $myrow['itemcode'] . ' ' . _('shipment avg cost adjt') . "',
                                                         " . ($myrow['totqtyinvoiced'] *($ItemShipmentCost - $StdCostUnit)
                                                                                    - $WriteOffToVariances) . ")";

                                        $ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The GL entry for the shipment average cost adjustment for'). ' ' . $myrow['itemcode'] . ' '. _('could not be inserted into the database because');
       		                        $result = DB_query($sql,$db, $ErrMsg,'',TRUE);

                                } /* end of average cost GL stuff */


				/*Now to update the stock cost with the new weighted average */

				/*Need to consider what to do if the cost has been changed manually between receiving
                                the stock and entering the invoice - this code assumes there has been no cost updates
                                made manually and all the price variance is posted to stock.

				A nicety or important?? */

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The cost could not be updated because');
				$DbgMsg = _('The following SQL to update the cost was used');

				if ($TotalQuantityOnHand>0) {

                                	$CostIncrement = ($myrow['totqtyinvoiced'] *($ItemShipmentCost - $StdCostUnit) - $WriteOffToVariances) / $TotalQuantityOnHand;
                                	$sql = 'UPDATE stockmaster SET lastcost=materialcost+overheadcost+labourcost,
                                                                   materialcost=materialcost+' . $CostIncrement . ' WHERE stockid="' . $myrow['itemcode'] . '"';
					$Result = DB_query($sql, $db, $ErrMsg, $DbgMsg,'',TRUE);
				} else {
					$sql = 'UPDATE stockmaster SET lastcost=materialcost+overheadcost+labourcost,
								materialcost=' . $ItemShipmentCost . ' WHERE stockid="' . $myrow['itemcode'] . '"';
					$Result = DB_query($sql, $db, $ErrMsg, $DbgMsg,'',TRUE);
                                }
				/* End of Weighted Average Costing Code */


                        } else { /*We must be using standard costing do the journals for standard costing then */

                               if ($_SESSION['CompanyRecord']['gllink_stock']==1){
                               	       $sql = "INSERT INTO gltrans (type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount)
					VALUES (31,
						" . $_GET['SelectedShipment'] . ",
						'" . Date('Y-m-d') . "',
						" . $PeriodNo . ",
						" . $StockGLCodes['purchpricevaract'] . ",
						'" . $myrow['itemcode'] . ' ' . _('shipment cost') . ' ' .  number_format($ItemShipmentCost,2) . ' x ' . _('Qty recd') .' ' . $myrow['totqtyrecd'] . "', " . (-$Variance * $myrow['totqtyrecd']) . ")";
            			       $ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The Positive GL entry for the shipment variance posting for'). ' ' . $myrow['itemcode'] . ' '. _('could not be inserted into the database because');
	       		               $result = DB_query($sql,$db, $ErrMsg,'',TRUE);
                               }
		         } /* end of the costing specific updates */


                         if ($_SESSION['CompanyRecord']['gllink_stock']==1){
                        /*we always need to reverse entries relating to the GRN suspense during delivery and entry of shipment charges */
                              $sql = "INSERT INTO gltrans (type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount)
				VALUES (31,
					" . $_GET['SelectedShipment'] . ",
					'" . Date('Y-m-d') . "',
					" . $PeriodNo . ",
					" . $_SESSION['CompanyRecord']['grnact'] . ",
					'" . $myrow['itemcode'] . ' ' ._('shipt cost') . ' ' .  number_format($ItemShipmentCost,2) . ' x ' . _('Qty invoiced') . ' ' . $myrow['totqtyinvoiced'] . "',
                                        " . ($Variance * $myrow['totqtyinvoiced']) . ")";

			      $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The credit GL entry for the shipment variance posting for') . ' ' . $myrow['itemcode'] . ' ' . _('could not be inserted because');

			      $result = DB_query($sql,$db, $ErrMsg,'',TRUE);
                         }

        		if ( $_POST['UpdateCost'] == 'Yes' ){ /*Only ever a standard costing option
			                                      Weighted average costing implies cost updates taking place automatically */

				$QOHResult = DB_query("SELECT SUM(quantity) FROM locstock WHERE stockid ='" . $myrow['itemcode'] . "'",$db);
				$QOHRow = DB_fetch_row($QOHResult);
				$QOH=$QOHRow[0];

                                if ($_SESSION['CompanyRecord']['gllink_stock']==1){
				   $CostUpdateNo = GetNextTransNo(35, $db);
       				   $PeriodNo = GetPeriod(Date("d/m/Y"), $db);

				   $ValueOfChange = $QOH * ($ItemShipmentCost - $StdCostUnit);

				   $SQL = "INSERT INTO gltrans (type,
								typeno,
								trandate,
								periodno,
								account,
								narrative,
								amount)
						VALUES (35,
							" . $CostUpdateNo . ",
							'" . Date('Y-m-d') . "',
							" . $PeriodNo . ",
							" . $StockGLCodes['adjglact'] . ",
							'" . _('Shipment of') . ' ' . $myrow['itemcode'] . " " . _('cost was') . ' ' . $StdCostUnit . ' ' . _('changed to') . ' ' . number_format($ItemShipmentCost,2) . ' x ' . _('QOH of') . ' ' . $QOH . "', " . (-$ValueOfChange) . ")";

				   $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The GL credit for the shipment stock cost adjustment posting could not be inserted because'). ' ' . DB_error_msg($db);

				   $Result = DB_query($SQL,$db, $ErrMsg,'',TRUE);

				   $SQL = "INSERT INTO gltrans (type,
								typeno,
								trandate,
								periodno,
								account,
								narrative,
								amount)
						VALUES (35,
							" . $CostUpdateNo . ",
							'" . Date('Y-m-d') . "',
							" . $PeriodNo . ",
							" . $StockGLCodes['stockact'] . ",
							'" . _('Shipment of') . ' ' . $myrow['itemcode'] .  ' ' . _('cost was') . ' ' . $StdCostUnit . ' ' . _('changed to') . ' ' . number_format($ItemShipmentCost,2) . ' x ' . _('QOH of') . ' ' . $QOH . "',
                                                        " . $ValueOfChange . ")";
				   $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The GL debit for stock cost adjustment posting could not be inserted because') .' '. DB_error_msg($db);

				   $Result = DB_query($SQL,$db, $ErrMsg,'',TRUE);

                                } /*end of GL entries for a standard cost update */

                                /* Only the material cost is important for imported items */
				$sql = "UPDATE stockmaster SET materialcost=" . $ItemShipmentCost . ",
								labourcost=0,
								overheadcost=0,
								lastcost=" . $StdCostUnit . "
						WHERE stockid='" . $myrow['itemcode'] . "'";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The shipment cost details for the stock item could not be updated because'). ': ' . DB_error_msg($db);

				$result = DB_query($sql,$db, $ErrMsg,'',TRUE);

			} // end of update cost code
		} // end of Close shipment item updates


/*  Item / Qty Inv/  FX price/ Local Val/ Portion of chgs/ Shipt Cost/ Std Cost/ Variance/ Var % */

	echo '<td>' . $myrow['itemcode'] . ' - ' . $myrow['itemdescription'] . '</td>
		<td align=right>' . number_format($myrow['totqtyinvoiced']) . '</td>
                <td align=right>' . number_format($myrow['totqtyrecd']) . '</td>
		<td align=right>' . number_format($ItemCharges) . '</td>
		<td align=right>' . number_format($PortionOfCharges) . '</td>
		<td align=right>' . number_format($ItemShipmentCost,2) . '</td>
		<td align=right>' . number_format($StdCostUnit,2) . '</td>
		<td align=right>' . number_format($Variance,2) . '</td>
		<td align=right>' . $VariancePercentage . '%</td></tr>';
    }
}
echo '<tr><td colspan=3 align=right><font color=BLUE><b>'. _('Total Shipment Charges'). '</b></font></td>
	<td align=right>' . number_format($TotalInvoiceValueOfShipment) . '</td>
	<td align=right>' . number_format($TotalCostsToApportion) .'</td></tr>';

echo '<tr><td colspan=6 align=right>' . _('Total Value of all variances on this shipment') . '</td>
              <td align=right>' . number_format($TotalShiptVariance,2) . '</td></tr>';

echo '</table><hr>';


echo '<table colspan=2 WIDTH=100%><tr><td VALIGN=TOp>'; // put this shipment charges side by side in a table (major table 2 cols)

$sql = "SELECT suppliers.suppname,
		supptrans.suppreference,
		systypes.typename,
		supptrans.trandate,
		supptrans.rate,
		suppliers.currcode,
		shipmentcharges.stockid,
		shipmentcharges.value,
		supptrans.transno,
		supptrans.supplierno
	FROM supptrans INNER JOIN shipmentcharges
		ON shipmentcharges.transtype=supptrans.type
		AND shipmentcharges.transno=supptrans.transno
	INNER JOIN suppliers
		ON suppliers.supplierid=supptrans.supplierno
	INNER JOIN systypes ON systypes.typeid=supptrans.type
	WHERE shipmentcharges.stockid<>''
	AND shipmentcharges.shiptref=" . $_GET['SelectedShipment'] . "
	ORDER BY supptrans.supplierno,
		supptrans.transno,
		shipmentcharges.stockid";

$ChargesResult = DB_query($sql,$db);

echo '<div class="centre"><font color=BLUE size=2>' . _('Shipment Charges Against Products'). '</font></div>';
echo '<table cellpadding=2 colspan=6 border=0>';

$TableHeader = '<tr>
		<th>'. _('Supplier'). '</th>
		<th>'. _('Type'). '</th>
		<th>'. _('Ref'). '</th>
		<th>'. _('Date'). '</th>
		<th>'. _('Item'). '</th>
		<th>'. _('Local Amount'). '<br>'. _('Charged'). '</th></tr>';

echo  $TableHeader;

/*show the line items on the shipment with the value invoiced and shipt cost */

$k=0; //row colour counter
$RowCounter =0;
$TotalItemShipmentChgs =0;

while ($myrow=db_fetch_array($ChargesResult)) {


	if ($k==1){
		echo '<tr class="EvenTableRows">';
		$k=0;
	} else {
		echo '<tr class="OddTableRows">';
		$k=1;
	}

	echo '<td>' . $myrow['suppname'] . '</td>
		<td>' .$myrow['typename'] . '</td>
		<td>' . $myrow['suppreference'] . '</td>
		<td>' . ConvertSQLDate($myrow['trandate']) . '</td>
		<td>' . $myrow['stockid'] . '</td>
		<td align=right>' . number_format($myrow['value']) . '</td></tr>';

	$TotalItemShipmentChgs += $myrow['value'];
}

echo '<tr><td colspan=5 align=right><font color=BLUE><b>'. _('Total Charges Against Shipment Items'). ':</b></font></td>
	<td align=right>' . number_format($TotalItemShipmentChgs) . '</td></tr>';

echo '</table>';

echo '</td><td VALIGN=TOp>'; //major table

/* Now the shipment freight/duty etc general charges */

$sql = "SELECT suppliers.suppname,
		supptrans.suppreference,
		systypes.typename,
		supptrans.trandate,
		supptrans.rate,
		suppliers.currcode,
		shipmentcharges.stockid,
		shipmentcharges.value
	FROM supptrans INNER JOIN shipmentcharges
		ON shipmentcharges.transtype=supptrans.type
		AND shipmentcharges.transno=supptrans.transno
	INNER JOIN suppliers
		ON suppliers.supplierid=supptrans.supplierno
	INNER JOIN systypes
		ON systypes.typeid=supptrans.type
	WHERE shipmentcharges.stockid=''
	AND shipmentcharges.shiptref=" . $_GET['SelectedShipment'] . "
	ORDER BY supptrans.supplierno,
		supptrans.transno";

$ChargesResult = DB_query($sql,$db);

echo '<div class="centre"><font color=BLUE size=2>'._('General Shipment Charges').'</font></div>';
echo '<table cellpadding=2 colspan=5 border=0>';

$TableHeader = '<tr>
		<th>'. _('Supplier'). '</th>
		<th>'. _('Type'). '</th>
		<th>'. _('Ref'). '</th>
		<th>'. _('Date'). '</th>
		<th>'. _('Local Amount'). '<br>'. _('Charged'). '</th></tr>';

echo  $TableHeader;

/*show the line items on the shipment with the value invoiced and shipt cost */

$k=0; //row colour counter
$RowCounter =0;
$TotalGeneralShipmentChgs =0;

while ($myrow=db_fetch_array($ChargesResult)) {

	if ($k==1){
		echo '<tr class="EvenTableRows">';
		$k=0;
	} else {
		echo '<tr class="OddTableRows">';
		$k=1;
	}

	echo '<td>' . $myrow['suppname'] . '</td>
		<td>' .$myrow['typename'] . '</td>
		<td>' . $myrow['suppreference'] . '</td>
		<td>' . ConvertSQLDate($myrow['trandate']) . '</td>
		<td align=right>' . number_format($myrow['value']) . '</td></tr>';

	$TotalGeneralShipmentChgs += $myrow['value'];

}

echo '<tr>
	<td align=right colspan=4><font color=BLUE><b>'. _('Total General Shipment Charges'). ':</b></font></td>
	<td align=right>' . number_format($TotalGeneralShipmentChgs) . '</td></tr>';

echo '</table>';

echo '</td></tr></table>'; //major table close

if ( isset($_GET['Close'])) { /* Only an opportunity to confirm user wishes to close */

// if the page was called with Close=Yes then show options to confirm OK to c
	echo '<hr><div class+"centre"><form method="POST" action="' . $_SERVER['PHP_SELF'] .'?' . SID .'&SelectedShipment=' . $_GET['SelectedShipment'] . '">';

        if ($_SESSION['WeightedAverageCosting']==0){
        /* We are standard costing - so show the option to update costs - under W. Avg cost updates are implicit */
        	echo _('Update Standard Costs') .':<select name="UpdateCost">
	        <option selected VALUE="Yes">'. _('Yes') . '
		<option VALUE="No">'. _('No').'</select>';
        }
	echo '<br><br><input type=submit name="Close" VALUE="'. _('Confirm OK to Close'). '">';
	echo '</form></div>';
}

if ( isset($_POST['Close']) ){ /* OK do the shipment close journals */

/*Inside a transaction need to:
 1 . compare shipment costs against standard x qty received and take the variances off to the GL GRN supsense account and variances - this is done in the display loop

 2. If UpdateCost=='Yes' then do the cost updates and GL entries.

 3. Update the shipment to completed

 1 and 2 done in the display loop above only 3 left*/

/*also need to make sure the purchase order lines that were on this shipment are completed so no more can be received in against the order line */

        $result = DB_query('UPDATE purchorderdetails
                                   SET quantityord=quantityrecd,
                                       completed=1
                            WHERE shiptref = ' . $_GET['SelectedShipment'],
                            $db,
                            _('Could not complete the purchase order lines on this shipment'),
                            '',
                            TRUE);

	$result = DB_query('UPDATE shipments SET closed=1 WHERE shiptref=' .$_GET['SelectedShipment'],$db,_('Could not update the shipment to closed'),'',TRUE);
	$result = DB_Txn_Commit($db);

	echo '<br><br>';
	prnMsg( _('Shipment'). ' ' . $_GET['SelectedShipment'] . ' ' . _('has been closed') );
	if ($_SESSION['CompanyRecord']['gllink_stock']==1) {
		echo '<br>';
		prnMsg ( _('All variances were posted to the general ledger') );
	}
	If ($_POST['UpdateCost']=='Yes'){
		echo '<br>';
		prnMsg ( _('All shipment items have had their standard costs updated') );
	}
}

include('includes/footer.inc');
?>

<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Shipment Costing');
$ViewTopic = 'Shipments';
$BookMark = '';
include('includes/header.php');

include('includes/SQL_CommonFunctions.php');
include('includes/StockFunctions.php');

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/magnifier.png" title="' . __('Search') . '" alt="" />
     ' . ' ' . $Title . '</p>';

if (isset($_GET['NewShipment']) AND $_GET['NewShipment']=='Yes'){
	unset($_SESSION['Shipment']->LineItems);
	unset($_SESSION['Shipment']);
}

if (!isset($_GET['SelectedShipment'])){

	echo '<br />';
	prnMsg( __('This page is expected to be called with the shipment number to show the costing for'), 'error');
	include('includes/footer.php');
	exit();
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
						WHERE shipments.shiptref = '" . $_GET['SelectedShipment'] . "'";

$ErrMsg = __('Shipment').' '. $_GET['SelectedShipment'] . ' ' . __('cannot be retrieved because a database error occurred');
$GetShiptHdrResult = DB_query($ShipmentHeaderSQL, $ErrMsg);
if (DB_num_rows($GetShiptHdrResult)==0) {
	echo '<br />';
	prnMsg( __('Shipment') . ' ' . $_GET['SelectedShipment'] . ' ' . __('could not be located in the database') , 'error');
	include('includes/footer.php');
	exit();
}

$HeaderData = DB_fetch_array($GetShiptHdrResult);
echo '<br />
	<table class="selection">
	<tr>
		<th colspan="4"><h3>' . __('Shipment Details') . '</h3></th>
	</tr>
	<tr>
		<td><b>' .  __('Shipment') .': </b></td>
		<td><b>' . $_GET['SelectedShipment'] . '</b></td>
		<td><b>' .  __('From').' ' . $HeaderData['suppname'] . '</b></td>
	</tr>
	<tr>
		<td>' . __('Vessel'). ': </td>
		<td>' . $HeaderData['vessel'] . '</td>
		<td>' .  __('Voyage Ref'). ': </td>
		<td>' . $HeaderData['voyageref'] . '</td>
	</tr>
	<tr>
		<td>' . __('Expected Arrival Date (ETA)') . ': </td>
		<td>' . ConvertSQLDate($HeaderData['eta']) . '</td>
	</tr>
	</table>';

/*Get the total non-stock item shipment charges */

$SQL = "SELECT SUM(value)
		FROM shipmentcharges
		WHERE stockid=''
		AND shiptref ='" . $_GET['SelectedShipment']. "'";

$ErrMsg = __('Shipment') . ' ' . $_GET['SelectedShipment'] . ' ' . __('general costs cannot be retrieved from the database');
$GetShiptCostsResult = DB_query($SQL, $ErrMsg);
if (DB_num_rows($GetShiptCostsResult)==0) {
	echo '<br />';
	prnMsg(__('No General Cost Records exist for Shipment') . ' ' . $_GET['SelectedShipment'] . ' ' . __('in the database'), 'error');
	include('includes/footer.php');
	exit();
}

$MyRow = DB_fetch_row($GetShiptCostsResult);

$TotalCostsToApportion = $MyRow[0];

/*Now Get the total of stock items invoiced against the shipment */

$SQL = "SELECT SUM(value)
		FROM shipmentcharges
		WHERE stockid<>''
		AND shiptref ='" . $_GET['SelectedShipment'] . "'";

$ErrMsg = __('Shipment') . ' ' . $_GET['SelectedShipment'] . ' ' . __('Item costs cannot be retrieved from the database');
$GetShiptCostsResult = DB_query($SQL);
if (DB_error_no() !=0 OR DB_num_rows($GetShiptCostsResult)==0) {
	echo '<br />';
	prnMsg( __('No Item Cost Records exist for Shipment') . ' ' . $_GET['SelectedShipment'] . ' ' . __('in the database'), 'error');
	include('includes/footer.php');
	exit();
}

$MyRow = DB_fetch_row($GetShiptCostsResult);

$TotalInvoiceValueOfShipment = $MyRow[0];

/*Now get the lines on the shipment */

$LineItemsSQL = "SELECT purchorderdetails.itemcode,
						purchorderdetails.itemdescription,
						SUM(purchorderdetails.qtyinvoiced) as totqtyinvoiced,
						SUM(purchorderdetails.quantityrecd) as totqtyrecd
						FROM purchorderdetails
					WHERE purchorderdetails.shiptref='" . $_GET['SelectedShipment'] . "'
					GROUP BY purchorderdetails.itemcode,
						  purchorderdetails.itemdescription";

$ErrMsg = __('The lines on the shipment could not be retrieved from the database');
$LineItemsResult = DB_query($LineItemsSQL, $ErrMsg);

if (DB_num_rows($LineItemsResult) > 0) {

	if (isset($_POST['Close'])){
		while ($MyRow=DB_fetch_array($LineItemsResult)){
		  if ($MyRow['totqtyinvoiced'] < $MyRow['totqtyrecd']){
			 prnMsg(__('Cannot close a shipment where the quantity received is more than the quantity invoiced. Check the item') . ' ' . $MyRow['itemcode'] . ' - ' . $MyRow['itemdescription'],'warn');
			 unset($_POST['Close']);
		  }
		}
		DB_data_seek($LineItemsResult,0);
 	}


	if (isset($_POST['Close'])){
	/*Set up a transaction to buffer all updates or none */
		DB_Txn_Begin();
		$PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']));
	}

	echo '<br /><table cellpadding="2" class="selection">';
	echo '<tr>
			<th colspan="9"><h3>' . __('Items on shipment'). '</h3></th></tr>';

	$TableHeader = '<tr>
						<th>' .  __('Item'). '</th>
						<th>' .  __('Quantity'). '<br />' .  __('Invoiced'). '</th>
						<th>' .  __('Quantity'). '<br />' .  __('Received'). '</th>
						<th>' .  __('Invoiced'). '<br />' .  __('Charges'). '</th>
						<th>' .  __('Shipment'). '<br />' .  __('Charges'). '</th>
						<th>' .  __('Shipment'). '<br />' .  __('Cost'). '</th>
						<th>' .  __('Standard'). '<br />' .  __('Cost'). '</th>
						<th>' .  __('Variance'). '</th>
						<th>%</th>
					</tr>';
	echo  $TableHeader;

	/*show the line items on the shipment with the value invoiced and shipt cost */

		$TotalShiptVariance = 0;
	$RowCounter =0;

	while ($MyRow=DB_fetch_array($LineItemsResult)) {

				$SQL = "SELECT SUM(shipmentcharges.value) AS invoicedcharges
						 FROM shipmentcharges
						 WHERE shipmentcharges.stockid ='" . $MyRow['itemcode'] . "'
						 AND shipmentcharges.shiptref='" . $_GET['SelectedShipment'] . "'";
				$ItemChargesResult = DB_query($SQL);
				$ItemChargesRow = DB_fetch_row($ItemChargesResult);
				$ItemCharges = $ItemChargesRow[0];

		if ($TotalInvoiceValueOfShipment>0){
			$PortionOfCharges = $TotalCostsToApportion *($ItemCharges/$TotalInvoiceValueOfShipment);
		} else {
			$PortionOfCharges = 0;
		}

		if ($MyRow['totqtyinvoiced']>0){
			$ItemShipmentCost = ($ItemCharges+$PortionOfCharges)/$MyRow['totqtyrecd'];
		} else {
			$ItemShipmentCost =0;
		}
		$SQL = "SELECT SUM(grns.stdcostunit*grns.qtyrecd) AS costrecd
				   FROM grns INNER JOIN purchorderdetails
				   ON grns.podetailitem=purchorderdetails.podetailitem
			 		WHERE purchorderdetails.shiptref='" . $_GET['SelectedShipment'] . "'
			 		AND purchorderdetails.itemcode = '" . $MyRow['itemcode'] . "'";

		$StdCostResult = DB_query($SQL);
		$StdCostRow = DB_fetch_row($StdCostResult);
		$CostRecd = $StdCostRow[0];
		if ($MyRow['totqtyrecd']==0) {
			$StdCostUnit = 0;
		} else {
			$StdCostUnit = $StdCostRow[0]/$MyRow['totqtyrecd'];
		}

		if ($ItemShipmentCost !=0){
			$Variance = $StdCostUnit - $ItemShipmentCost;
		} else {
			$Variance =0;
		}

		$TotalShiptVariance += ($Variance *$MyRow['totqtyinvoiced']);

		if ($StdCostUnit>0 ){
			$VariancePercentage = locale_number_format(($Variance*100)/$StdCostUnit,1);
		} else {
			$VariancePercentage =100;
		}


		if ( isset($_POST['Close']) AND $Variance !=0){


			if ($_SESSION['CompanyRecord']['gllink_stock']==1){
				  $StockGLCodes = GetStockGLCode($MyRow['itemcode']);
			}

			/*GL journals depend on the costing method used currently:
				 Standard cost - the price variance between the exisitng system cost and the shipment cost is taken as a variance
				 to the price varaince account
				 Weighted Average Cost - the price variance is taken to the stock account and the cost updated to ensure the GL
				 stock account ties up to the stock valuation
			*/

			if ($_SESSION['WeightedAverageCosting'] == 1){   /* Do the WAvg journal and cost update */
				/* First off figure out the new weighted average cost Need the following data:
				- How many in stock now
				- The quantity being costed here - $MyRow['qtyinvoiced']
				- The cost of these items - $ItemShipmentCost */

				$TotalQuantityOnHand = GetQuantityOnHand($MyRow['itemcode'], 'ALL');

				/* The cost adjustment is the price variance / the total quantity in stock
				But that's only provided that the total quantity in stock is > the quantity charged on this invoice */

				$WriteOffToVariances =0;

				if ($MyRow['totqtyinvoiced'] > $TotalQuantityOnHand){

					/*So we need to write off some of the variance to variances and
					only the balance of the quantity in stock to go to stock value */

					 $WriteOffToVariances =  ($MyRow['totqtyinvoiced'] - $TotalQuantityOnHand) * ($ItemShipmentCost - $StdCostUnit);
				 }


				if ($_SESSION['CompanyRecord']['gllink_stock']==1){

				   /* If the quantity on hand is less the amount charged on this invoice then some must have been sold
					and the price variance on these must be written off to price variances*/

					if ($MyRow['totqtyinvoiced'] > $TotalQuantityOnHand){

						$SQL = "INSERT INTO gltrans (type,
											typeno,
											trandate,
											periodno,
											account,
											narrative,
											amount)
				  					VALUES (31,
				   					'" . $_GET['SelectedShipment'] . "',
									CURRENT_DATE,
									'" . $PeriodNo . "',
							 		'" . $StockGLCodes['purchpricevaract'] . "',
								 	'" . mb_substr($MyRow['itemcode'] . ' ' . __('shipment cost') . ' ' .  locale_number_format($ItemShipmentCost,$_SESSION['CompanyRecord']['deicmalplaces']) . __('shipment quantity > stock held - variance write off'), 0, 200) . "',
									 " . $WriteOffToVariances . ")";

						$ErrMsg =  __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The GL entry for the shipment variance posting for'). ' ' . $MyRow['itemcode'] . ' '. __('could not be inserted into the database because');
			   			$Result = DB_query($SQL, $ErrMsg,'',true);

					}
				/*Now post any remaining price variance to stock rather than price variances */
					$SQL = "INSERT INTO gltrans (type,
											typeno,
											trandate,
											periodno,
											account,
											narrative,
											amount)
											VALUES (31,
							   					'" . $_GET['SelectedShipment'] . "',
												CURRENT_DATE,
												'" . $PeriodNo . "',
										 		'" . $StockGLCodes['stockact'] . "',
											 	'" . mb_substr($MyRow['itemcode'] . ' ' . __('shipment avg cost adjt'), 0, 200) . "',
												'" . ($MyRow['totqtyinvoiced'] *($ItemShipmentCost - $StdCostUnit)- $WriteOffToVariances) . "')";

					$ErrMsg =  __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The GL entry for the shipment average cost adjustment for'). ' ' . $MyRow['itemcode'] . ' '. __('could not be inserted into the database because');
					$Result = DB_query($SQL, $ErrMsg,'',true);

				} /* end of average cost GL stuff */


				/*Now to update the stock cost with the new weighted average */

				/*Need to consider what to do if the cost has been changed manually between receiving
				the stock and entering the invoice - this code assumes there has been no cost updates
				made manually and all the price variance is posted to stock.

				A nicety or important?? */

				$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The cost could not be updated because');

				if ($TotalQuantityOnHand>0) {

					$CostIncrement = ($MyRow['totqtyinvoiced'] *($ItemShipmentCost - $StdCostUnit) - $WriteOffToVariances) / $TotalQuantityOnHand;

					$SQL = "UPDATE stockmaster
							SET lastcost=materialcost+overheadcost+labourcost,
								materialcost=materialcost+" . $CostIncrement . ",
								lastcostupdate = CURRENT_DATE
							WHERE stockid='" . $MyRow['itemcode'] . "'";

					$Result = DB_query($SQL, $ErrMsg, '','',true);

				} else {
					$SQL = "UPDATE stockmaster
							SET lastcost=materialcost+overheadcost+labourcost,
								materialcost='" . $ItemShipmentCost . "',
								lastcostupdate = CURRENT_DATE
							WHERE stockid='" . $MyRow['itemcode'] . "'";

					$Result = DB_query($SQL, $ErrMsg, '','',true);

				}
				/* End of Weighted Average Costing Code */


			} else { /*We must be using standard costing do the journals for standard costing then */

				 if ($_SESSION['CompanyRecord']['gllink_stock']==1){
					$SQL = "INSERT INTO gltrans (type,
												typeno,
												trandate,
												periodno,
												account,
												narrative,
												amount)
									VALUES (31,
										'" . $_GET['SelectedShipment'] . "',
										CURRENT_DATE,
										'" . $PeriodNo . "',
										'" . $StockGLCodes['purchpricevaract'] . "',
										'" . mb_substr($MyRow['itemcode'] . ' ' . __('shipment cost') . ' ' .  locale_number_format($ItemShipmentCost,$_SESSION['CompanyRecord']['decimalplaces']) . ' x ' . __('Qty recd') .' ' . $MyRow['totqtyrecd'], 0, 200) . "',
										" . -$Variance * $MyRow['totqtyrecd'] . ")";

					$ErrMsg =  __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The Positive GL entry for the shipment variance posting for'). ' ' . $MyRow['itemcode'] . ' '. __('could not be inserted into the database because');
		   			$Result = DB_query($SQL, $ErrMsg,'',true);
				 }
			} /* end of the costing specific updates */


			if ($_SESSION['CompanyRecord']['gllink_stock']==1){
						/*we always need to reverse entries relating to the GRN suspense during delivery and entry of shipment charges */
				  $SQL = "INSERT INTO gltrans (type,
										typeno,
										trandate,
										periodno,
										account,
										narrative,
										amount)
							VALUES (31,
								'" . $_GET['SelectedShipment'] . "',
								CURRENT_DATE,
								'" . $PeriodNo . "',
								'" . $_SESSION['CompanyRecord']['grnact'] . "',
								'" . mb_substr($MyRow['itemcode'] . ' ' .__('shipment cost') . ' ' .  locale_number_format($ItemShipmentCost,$_SESSION['CompanyRecord']['decimalplaces']) . ' x ' . __('Qty invoiced') . ' ' . $MyRow['totqtyinvoiced'], 0, 200) . "',
								" . ($Variance * $MyRow['totqtyinvoiced']) . ")";

				  $ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The credit GL entry for the shipment variance posting for') . ' ' . $MyRow['itemcode'] . ' ' . __('could not be inserted because');

				  $Result = DB_query($SQL, $ErrMsg,'',true);
						 }

				if ( isset($_POST['UpdateCost']) AND $_POST['UpdateCost'] == 'Yes' ){
					/*Only ever a standard costing option
					 Weighted average costing implies cost updates taking place automatically */

					$QOH = GetQuantityOnHand($MyRow['itemcode'], 'ALL');

					if ($_SESSION['CompanyRecord']['gllink_stock']==1){
						$CostUpdateNo = GetNextTransNo(35);
						$PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']));

						$ValueOfChange = $QOH * ($ItemShipmentCost - $StdCostUnit);

						$SQL = "INSERT INTO gltrans (type,
												typeno,
												trandate,
												periodno,
												account,
												narrative,
												amount)
										VALUES (35,
											'" . $CostUpdateNo . "',
											CURRENT_DATE,
											'" . $PeriodNo . "',
											'" . $StockGLCodes['adjglact'] . "',
											'" . mb_substr(__('Shipment of') . ' ' . $MyRow['itemcode'] . " " . __('cost was') . ' ' . $StdCostUnit . ' ' . __('changed to') . ' ' . locale_number_format($ItemShipmentCost,$_SESSION['CompanyRecord']['decimalplaces']) . ' x ' . __('QOH of') . ' ' . $QOH, 0, 200) . "',
											" . -$ValueOfChange . ")";

						   $ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The GL credit for the shipment stock cost adjustment posting could not be inserted because'). ' ' . DB_error_msg();

						   $Result = DB_query($SQL, $ErrMsg,'',true);

						   $SQL = "INSERT INTO gltrans (type,
										typeno,
										trandate,
										periodno,
										account,
										narrative,
										amount)
								VALUES (35,
									'" . $CostUpdateNo . "',
									CURRENT_DATE,
									'" . $PeriodNo . "',
									'" . $StockGLCodes['stockact'] . "',
									'" . mb_substr(__('Shipment of') . ' ' . $MyRow['itemcode'] .  ' ' . __('cost was') . ' ' . $StdCostUnit . ' ' . __('changed to') . ' ' . locale_number_format($ItemShipmentCost,$_SESSION['CompanyRecord']['decimalplaces']) . ' x ' . __('QOH of') . ' ' . $QOH, 0, 200) . "',
									" . $ValueOfChange . ")";

						   $ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The GL debit for stock cost adjustment posting could not be inserted because') .' '. DB_error_msg();

						   $Result = DB_query($SQL, $ErrMsg,'',true);

					} /*end of GL entries for a standard cost update */

					/* Only the material cost is important for imported items */
					$SQL = "UPDATE stockmaster SET materialcost=" . $ItemShipmentCost . ",
												labourcost=0,
												overheadcost=0,
												lastcost='" . $StdCostUnit . "',
												lastcostupdate = CURRENT_DATE
										WHERE stockid='" . $MyRow['itemcode'] . "'";

					$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The shipment cost details for the stock item could not be updated because'). ': ' . DB_error_msg();

					$Result = DB_query($SQL, $ErrMsg,'',true);

				} // end of update cost code
		} // end of Close shipment item updates


/*  Item / Qty Inv/  FX price/ Local Val/ Portion of chgs/ Shipt Cost/ Std Cost/ Variance/ Var % */

	echo '<tr class="striped_row">
			<td>' . $MyRow['itemcode'] . ' - ' . $MyRow['itemdescription'] . '</td>
			<td class="number">' . locale_number_format($MyRow['totqtyinvoiced'],'Variable') . '</td>
			<td class="number">' . locale_number_format($MyRow['totqtyrecd'],'Variable') . '</td>
			<td class="number">' . locale_number_format($ItemCharges,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
			<td class="number">' . locale_number_format($PortionOfCharges,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
			<td class="number">' . locale_number_format($ItemShipmentCost,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
			<td class="number">' . locale_number_format($StdCostUnit,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
			<td class="number">' . locale_number_format($Variance,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
			<td class="number">' . $VariancePercentage . '%</td>
		</tr>';
	}
}
echo '<tr>
		<td colspan="3" class="number"><b>' .  __('Total Shipment Charges'). '</b></td>
		<td class="number">' . locale_number_format($TotalInvoiceValueOfShipment,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
		<td class="number">' . locale_number_format($TotalCostsToApportion,$_SESSION['CompanyRecord']['decimalplaces'])  . '</td>
	</tr>';

echo '<tr>
		<td colspan="6" class="number">' . __('Total Value of all variances on this shipment') . '</td>
		<td class="number">' . locale_number_format($TotalShiptVariance,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
	</tr>';

echo '</table>';


echo '<br />
	<table width="95%">
	<tr>
		<td valign="top">'; // put this shipment charges side by side in a table (major table 2 cols)

$SQL = "SELECT suppliers.suppname,
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
		AND shipmentcharges.shiptref='" . $_GET['SelectedShipment'] . "'
		ORDER BY supptrans.supplierno,
			supptrans.transno,
			shipmentcharges.stockid";

$ChargesResult = DB_query($SQL);

echo '<table cellpadding="2" class="selection">';
echo '<tr>
		<th colspan="6"><h3>' . __('Shipment Charges Against Products'). '</h3></th>
	</tr>';

$TableHeader = '<tr>
					<th>' .  __('Supplier'). '</th>
					<th>' .  __('Type'). '</th>
					<th>' .  __('Ref'). '</th>
					<th>' .  __('Date'). '</th>
					<th>' .  __('Item'). '</th>
					<th>' .  __('Local Amount'). '<br />' .  __('Charged'). '</th>
				</tr>';

echo  $TableHeader;

/*show the line items on the shipment with the value invoiced and shipt cost */

$RowCounter =0;
$TotalItemShipmentChgs =0;

while ($MyRow=DB_fetch_array($ChargesResult)) {

	echo '<tr class="striped_row">
		<td>' . $MyRow['suppname'] . '</td>
		<td>' .$MyRow['typename'] . '</td>
		<td>' . $MyRow['suppreference'] . '</td>
		<td>' . ConvertSQLDate($MyRow['trandate']) . '</td>
		<td>' . $MyRow['stockid'] . '</td>
		<td class="number">' . locale_number_format($MyRow['value'],$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
		</tr>';

	$TotalItemShipmentChgs += $MyRow['value'];
}

echo '<tr>
		<td colspan="5" class="number"><b>' .  __('Total Charges Against Shipment Items'). ':</b></td>
		<td class="number">' . locale_number_format($TotalItemShipmentChgs,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
	</tr>';

echo '</table>';

echo '</td><td valign="top">'; //major table

/* Now the shipment freight/duty etc general charges */

$SQL = "SELECT suppliers.suppname,
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
	AND shipmentcharges.shiptref='" . $_GET['SelectedShipment'] . "'
	ORDER BY supptrans.supplierno,
		supptrans.transno";

$ChargesResult = DB_query($SQL);

echo '<table cellpadding="2" class="selection">';
echo '<tr>
		<th colspan="6"><h3>' . __('General Shipment Charges') . '</h3></th>
	</tr>';

$TableHeader = '<tr>
					<th>' .  __('Supplier'). '</th>
					<th>' .  __('Type'). '</th>
					<th>' .  __('Ref'). '</th>
					<th>' .  __('Date'). '</th>
					<th>' .  __('Local Amount'). '<br />' .  __('Charged'). '</th>
				</tr>';

echo  $TableHeader;

/*show the line items on the shipment with the value invoiced and shipt cost */

$RowCounter =0;
$TotalGeneralShipmentChgs =0;

while ($MyRow=DB_fetch_array($ChargesResult)) {

	echo '<tr class="striped_row">
		<td>' . $MyRow['suppname'] . '</td>
		<td>' .$MyRow['typename'] . '</td>
		<td>' . $MyRow['suppreference'] . '</td>
		<td>' . ConvertSQLDate($MyRow['trandate']) . '</td>
		<td class="number">' . locale_number_format($MyRow['value'],$_SESSION['CompanyRecord']['decimalplaces']) . '</td></tr>';

	$TotalGeneralShipmentChgs += $MyRow['value'];

}

echo '<tr>
	<td class="number" colspan="4"><b>' .  __('Total General Shipment Charges'). ':</b></td>
	<td class="number">' . locale_number_format($TotalGeneralShipmentChgs,$_SESSION['CompanyRecord']['decimalplaces']) . '</td></tr>';

echo '</table>';

echo '</td>
	</tr>
	</table>'; //major table close

if ( isset($_GET['Close'])) { /* Only an opportunity to confirm user wishes to close */

// if the page was called with Close=Yes then show options to confirm OK to c
	echo '<div class="centre">
			<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .'?SelectedShipment=' . $_GET['SelectedShipment'] . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

		if ($_SESSION['WeightedAverageCosting']==0){
		/* We are standard costing - so show the option to update costs - under W. Avg cost updates are implicit */
			echo __('Update Standard Costs') .':<select name="UpdateCost">
					<option selected="selected" value="Yes">' .  __('Yes') . '</option>
					<option value="No">' .  __('No') . '</option>
					</select>';
		}
	echo '<br />
		<br />
		<input type="submit" name="Close" value="'. __('Confirm OK to Close'). '" />
		</form>
		</div>';
}

if ( isset($_POST['Close']) ){ /* OK do the shipment close journals */

/*Inside a transaction need to:
 1 . compare shipment costs against standard x qty received and take the variances off to the GL GRN supsense account and variances - this is done in the display loop

 2. If UpdateCost=='Yes' then do the cost updates and GL entries.

 3. Update the shipment to completed

 1 and 2 done in the display loop above only 3 left*/

/*also need to make sure the purchase order lines that were on this shipment are completed so no more can be received in against the order line */

		$Result = DB_query("UPDATE purchorderdetails
								   SET quantityord=quantityrecd,
									   completed=1
							WHERE shiptref = '" . $_GET['SelectedShipment'] ."'",
							__('Could not complete the purchase order lines on this shipment'),
							'',
							true);

	$Result = DB_query("UPDATE shipments SET closed=1 WHERE shiptref='" .$_GET['SelectedShipment']. "'",__('Could not update the shipment to closed'),'',true);
	DB_Txn_Commit();

	echo '<br /><br />';
	prnMsg( __('Shipment'). ' ' . $_GET['SelectedShipment'] . ' ' . __('has been closed') );
	if ($_SESSION['CompanyRecord']['gllink_stock']==1) {
		echo '<br />';
		prnMsg( __('All variances were posted to the general ledger') );
	}
	if (isset($_POST['UpdateCost']) AND $_POST['UpdateCost']=='Yes'){
		echo '<br />';
		prnMsg( __('All shipment items have had their standard costs updated') );
	}
}

include('includes/footer.php');

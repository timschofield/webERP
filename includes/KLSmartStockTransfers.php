<?php

/* Functions related to Smart Stock Transfers */

/**************************************************************************************************************
Functions included in this file (alphabetical order):

1) KLCreateSmartStockTransfer - Creates smart stock transfers between locations based on reorder levels and strategy
2) KLPrepareGroupSmartStockTransfers - Prepares and executes smart stock transfers for a specific shop group
3) PrintHeaderSmartStockDispatch - Prints the PDF header for smart stock dispatch reports

**************************************************************************************************************/

/**************************************************************************************************************
Function: KLPrepareGroupSmartStockTransfers

Brief description:
Prepares and executes smart stock transfers for a specific shop group. Identifies shops that need transfers
based on day of week, priority and sales history, then creates bidirectional transfers (to/from KANTO).

Parameters:
- $Group (string): Group identifier for shop type (e.g., "1050-SmartStockTransfersKL")
- $EmailText (string): Email text content to append transfer information to

Returns:
- string: Updated email text with transfer operation results and status messages
**************************************************************************************************************/

function KLPrepareGroupSmartStockTransfers($Group, $EmailText){

	if ($Group == "1050-SmartStockTransfersKL"){
		$ShopType = "SHOPKL";
		$EmailText .= 'Smart Stock Transfers for Kapal-Laut Shops' . "\n";
	}elseif ($Group == "1060-SmartStockTransfersBL"){
		$ShopType = "SHOPBL";
		$EmailText .= 'Smart Stock Transfers for Blink Shops' . "\n";
	}elseif ($Group == "1070-SmartStockTransfersOU"){
		$ShopType = "SHOPOU";
		$EmailText .= 'Smart Stock Transfers for Outlet Shops' . "\n";
	}else{
		$EmailText .= 'Type Of Shop not defined' . "\n";
	}
	
	/* Parameters */
	if (KLwebERPScriptCalledFromTEST()){
		$ReportType = "ReportOnly"; // To NOT create proper transfers, just the paperwork to test it
	}else{
		$ReportType = "Batch"; // To create proper transfers
	}

	$DispatchPercent = 0;
	$_SESSION['PageSize'] = 'A4';
	$DaysSalesForOrder = 2;

	/* Selection of shops with smart dispatch from / to KANTO, sorted by priority and sales of the last X days */
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']), 'd', -$DaysSalesForOrder));

	$DayOfWeek = date('w', strtotime(Date('Y-m-d')));

	$SQL = "SELECT loc.loccode,
					loc.smartdispatchmaxmodels,
					loc.smartdispatchminmodels,
					COALESCE(sales_summary.sales_count, 0) AS sales_count
			FROM locations loc
			INNER JOIN locationzones lz ON loc.zone = lz.code
			LEFT JOIN (
				SELECT so.fromstkloc,
					   COUNT(sod.qtyinvoiced) AS sales_count
				FROM salesorders so
				INNER JOIN salesorderdetails sod ON so.orderno = sod.orderno
				WHERE sod.completed = 1
					AND so.orddate >= '" . $StartDate . "'
				GROUP BY so.fromstkloc
			) sales_summary ON loc.loccode = sales_summary.fromstkloc
			WHERE loc.smartdispatchfrom = 'KANTO'
				AND loc.typeloc = '" . $ShopType . "'
				AND lz.smarttransferonweekday" . $DayOfWeek . " = 1
			ORDER BY loc.priority ASC, sales_summary.sales_count DESC";
	
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		while ($MyRow = DB_fetch_array($Result)) {
			// From KANTO to Shop, send the items needed to fill the RL
			$EmailText = KLCreateSmartStockTransfer('KANTO', $MyRow['loccode'], "All", $ReportType, $DispatchPercent, 
													$MyRow['smartdispatchmaxmodels'], $MyRow['smartdispatchminmodels'], 
													$EmailText);
			// From Shop to KANTO, return the overstock
			$EmailText = KLCreateSmartStockTransfer($MyRow['loccode'], 'KANTO', "OverFrom", $ReportType, $DispatchPercent, 
													$MyRow['smartdispatchmaxmodels'], $MyRow['smartdispatchminmodels'], 
													$EmailText);
		}
	}
	return $EmailText;
}

/**************************************************************************************************************
Function: KLCreateSmartStockTransfer

Brief description:
Creates smart stock transfers between two locations based on stock levels, reorder points, and transfer strategy.
Generates PDF reports, creates transfer records, and sends email notifications. Handles both regular transfers
and overstock returns with image validation and price calculations.

Parameters:
- $FromLocCode (string): Source location code for the transfer
- $ToLocCode (string): Destination location code for the transfer
- $Strategy (string): Transfer strategy - "All" for needed items or "OverFrom" for overstock returns
- $ReportType (string): "Batch" to create actual transfers or "ReportOnly" for testing
- $DispatchPercent (float): Percentage adjustment for dispatch calculations
- $MaxModelsPerDispatch (int): Maximum number of models to include in one transfer
- $MinModelsPerDispatch (int): Minimum number of models required to create a transfer
- $EmailText (string): Email text content to append transfer information to

Returns:
- string: Updated email text with transfer operation results, PDF generation status, and error messages
**************************************************************************************************************/

function KLCreateSmartStockTransfer($FromLocCode, $ToLocCode, $Strategy, $ReportType, $DispatchPercent, 
									$MaxModelsPerDispatch, $MinModelsPerDispatch, $EmailText){

	$TableResult = array();

	$FromLocation = GetLocationNameFromCode($FromLocCode);

	// to location
	if ($ToLocCode == 'KANTO'){
		// parameters are forced for KANTO, as it does not have any specific price list
		// and it's not a customer
		$ToLocation = '000-Kantor KL';
		$ToCustomer = '';
		$ToBranch = '';
		$ToCurrency = 'IDR';
		$ToPriceList = 'RT';
		$ToDecimalPlaces = 0;

	}else{
		// if the trasnfer is going somewhere not being KANTO, we need to get the parameters
		$SQLto = "SELECT locationname,
					cashsalecustomer,
					cashsalebranch
				FROM locations
				WHERE loccode = '" . $ToLocCode . "'";
		$Resultto = DB_query($SQLto);
		$RowTo = DB_fetch_row($Resultto);
		$ToLocation = $RowTo['0'];
		$ToCustomer = $RowTo['1'];
		$ToBranch = $RowTo['2'];

		$SQLPrices = "SELECT debtorsmaster.currcode,
						debtorsmaster.salestype,
						currencies.decimalplaces
					FROM debtorsmaster, currencies
					WHERE debtorsmaster.currcode = currencies.currabrev
						AND debtorsmaster.debtorno = '" . $ToCustomer . "'";
		$ResultPrices = DB_query($SQLPrices);
		$RowPrices = DB_fetch_row($ResultPrices);
		$ToCurrency = $RowPrices['0'];
		$ToPriceList = $RowPrices['1'];
		$ToDecimalPlaces = $RowPrices['2'];
	}
	
	$CategoryDescription = __('All');
	$WhereCategory = " AND stockmaster.categoryid != 'SHCONS'
						   AND stockmaster.categoryid != 'SHPACK' ";

	// If Strategy is "Items needed at TO location with overstock at FROM" we need to control the "needed at TO" part
	// The "overstock at FROM" part is controlled in any case with AND (fromlocstock.quantity - fromlocstock.reorderlevel) > 0
	if ($Strategy == 'All') {
		$WhereCategory = $WhereCategory . " AND locstock.reorderlevel > locstock.quantity ";
		$StrategyText = "Items needed at " . $ToLocCode . " with stock available at " . $FromLocCode . " ";
	}else{
		$StrategyText = "Items with overstock at " . $FromLocCode . " returning to " . $ToLocCode;
	}

	$SQL = "SELECT locstock.stockid,
				stockmaster.description,
				locstock.loccode,
				locstock.quantity,
				locstock.reorderlevel,
				stockmaster.decimalplaces,
				stockmaster.serialised,
				stockmaster.controlled,
				stockmaster.discountcategory,
				fromlocstock.reorderlevel as fromreorderlevel,
				fromlocstock.quantity as fromquantity
			FROM stockmaster
			LEFT JOIN stockcategory
				ON stockmaster.categoryid = stockcategory.categoryid,
			locstock
			LEFT JOIN locstock AS fromlocstock ON
			  locstock.stockid = fromlocstock.stockid
			  AND fromlocstock.loccode = '" . $FromLocCode . "'
			WHERE locstock.stockid = stockmaster.stockid
			AND locstock.loccode = '" . $ToLocCode . "'
			AND (fromlocstock.quantity - fromlocstock.reorderlevel) > 0
			AND stockcategory.stocktype <> 'A'
			AND (stockmaster.mbflag = 'B' OR stockmaster.mbflag = 'M') " .
			$WhereCategory .
			" ORDER BY stockcategory.klprioritytransfers,
						locstock.stockid";

	$Result = DB_query($SQL, '', '', false, true);

	$EmailText .= "\n" .
				"Smart Stock Dispatch from " . $FromLocCode . " to " . $ToLocCode . "\n" .
				" " . $StrategyText . "\n";
	$EmailText .= "Min Models to create transfer: " . $MinModelsPerDispatch . "\n" .
				"Max Models to be included: " . $MaxModelsPerDispatch . "\n";

	if (DB_error_no() != 0) {
		$EmailText .= "Smart Stock Dispatch ERROR " . __('The Stock Dispatch report could not be retrieved by the SQL because') . ' ' . DB_error_msg() . "\n";
		$EmailText .= "SQL = " . $SQL . "\n";
	}else{
		// Let's do the calculation for the available items for transfer and load them into TableResult array
		$Now = Date('Y-m-d H-i-s');
		$EmailText .= "Models candidates to be included in transfer: " . DB_num_rows($Result) . "\n";
		$NumModelsInThisStockDispatch = 0;
		$NumPcsInThisStockDispatch = 0;
		while (($MyRow = DB_fetch_array($Result)) AND ($NumModelsInThisStockDispatch < $MaxModelsPerDispatch)){
			// Check if there is any stock in transit already sent from FROM LOCATION
			$InTransitQuantityAtFrom = GetItemQtyInTransitFromLocation($MyRow['stockid'], $FromLocCode);

			// The real available stock to ship is the (qty - reorder level - in transit).
			$AvailableShipQtyAtFrom = $MyRow['fromquantity'] - $MyRow['fromreorderlevel'] - $InTransitQuantityAtFrom;

			// Check if TO location is already waiting to receive some stock of this item
			$InTransitQuantityAtTo = GetItemQtyInTransitToLocation($MyRow['stockid'], $ToLocCode);

			// The real needed stock is reorder level - qty - in transit).
			$NeededQty = round(($MyRow['reorderlevel'] - $MyRow['quantity']) * (1 + $DispatchPercent / 100));
			$NeededQtyAtTo = $NeededQty - $InTransitQuantityAtTo;

			// Decide how many are sent (depends on the strategy)
			if ($Strategy == 'OverFrom') {
				// send items with overstock at FROM, no matter qty needed at TO.
				$ShipQty = $AvailableShipQtyAtFrom;
			}else{
				// Send all items with overstock at FROM needed at TO
				$ShipQty = 0;
				if ($AvailableShipQtyAtFrom > 0) {
					if ($AvailableShipQtyAtFrom >= $NeededQtyAtTo) {
						// We can ship all the needed qty at TO location
						$ShipQty = $NeededQtyAtTo;
					}else{
						// We can't ship all the needed qty at TO location, but at least can ship some
						$ShipQty = $AvailableShipQtyAtFrom;
					}
				}
			}

			// ONLY add to transfer if there's QTY and we have a picture for it. If no picture, no send!
			if ($ShipQty > 0){
				$ImageFile = $_SESSION['part_pics_dir'] . '/' . $MyRow['stockid'] . '.jpg';
				if (file_exists($ImageFile)){
					$NumModelsInThisStockDispatch++;
					$NumPcsInThisStockDispatch = $NumPcsInThisStockDispatch + $ShipQty;

					// looking for price info
					$DefaultPrice = GetPrice($MyRow['stockid'], $ToCustomer, $ToBranch, $ShipQty, false);

					$TableResult[$NumModelsInThisStockDispatch]['stockid'] = $MyRow['stockid'];
					$TableResult[$NumModelsInThisStockDispatch]['description'] = $MyRow['description'];
					$TableResult[$NumModelsInThisStockDispatch]['fromquantity'] = $MyRow['fromquantity'] - $InTransitQuantityAtFrom;
					$TableResult[$NumModelsInThisStockDispatch]['quantity'] = $MyRow['quantity'] + $InTransitQuantityAtTo;
					$TableResult[$NumModelsInThisStockDispatch]['shipqty'] = $ShipQty;
					$TableResult[$NumModelsInThisStockDispatch]['decimalplaces'] = $MyRow['decimalplaces'];
					$TableResult[$NumModelsInThisStockDispatch]['price'] = $DefaultPrice;
					$TableResult[$NumModelsInThisStockDispatch]['discountcategory'] = $MyRow['discountcategory'];

					$EmailText .= $MyRow['stockid'] . " x " . $ShipQty . "\n";

				}else{
					$EmailText .= $MyRow['stockid'] . " x " . $NeededQtyAtTo . " rejected no picture" . "\n";
				}
			}else{
				if ($NeededQtyAtTo <= 0){
					$EmailText .= $MyRow['stockid'] .
											  " x " .
											  $NeededQty .
											  " Already in transit = " .
											  $InTransitQuantityAtTo .
											  "\n";
				}else{
					$EmailText .= $MyRow['stockid'] .
											  " x " .
											  $NeededQty .
											  " Already in transit = " .
											  $InTransitQuantityAtTo .
											  " Rejected as stock available @" .
											  $FromLocCode .
											  " = " .
											  $AvailableShipQtyAtFrom . "\n";
				}

			}
		} /*end while loop */

		if ($NumModelsInThisStockDispatch > 0){
			// There are some models to be dispatched
			if ($NumModelsInThisStockDispatch >= $MinModelsPerDispatch){
				// Enough models available for transfer
				// OK, let's create the PDF and the transfer records
				include('includes/PDFStarter.php');
				$pdf->addInfo('Title', __('KL Stock Dispatch Report'));
				$pdf->addInfo('Subject', __('Items to dispatch to another location to cover reorder level'));
				$FontSize = 9;
				$PageNumber = 1;
				$LineHeight = 19;

				// Create Transfer Number
				if(!isset($Trf_ID) and $ReportType == 'Batch') {
					$Trf_ID = GetNextTransNo(16);
					$EmailText .= "Transfer # " . $Trf_ID . "\n";
				}else{
					$Trf_ID = '';
					$EmailText .= "Report only. No transfer created.\n";
				}

				PrintHeaderSmartStockDispatch($pdf, $YPos, $PageNumber, $Page_Height, $Top_Margin, $Left_Margin, 
											$Page_Width, $Right_Margin, $Trf_ID, $FromLocCode, $FromLocation, 
											$ToLocCode, $ToLocation, $CategoryDescription, $Strategy);

				$FontSize = 8;
				$ModelInTransfer = 0;
				while ($ModelInTransfer < $NumModelsInThisStockDispatch){
					$ModelInTransfer++;

					$YPos -= (2 * $LineHeight);
					$Fill = False;

					$pdf->addTextWrap(50, $YPos, 70, $FontSize, $TableResult[$ModelInTransfer]['stockid'], '', 0, $Fill);
					$pdf->Image($_SESSION['part_pics_dir'] . '/' . $TableResult[$ModelInTransfer]['stockid'] . '.jpg', 135, 
							$Page_Height - $Top_Margin - $YPos + 10, 45, 35);
					$pdf->addTextWrap(180, $YPos, 200, $FontSize, $TableResult[$ModelInTransfer]['description'], '', 0, $Fill);
					$pdf->addTextWrap(355, $YPos, 40, $FontSize, 
							locale_number_format($TableResult[$ModelInTransfer]['fromquantity'], 
											$TableResult[$ModelInTransfer]['decimalplaces']), 'right', 0, $Fill);
					$pdf->addTextWrap(405, $YPos, 40, $FontSize, 
							locale_number_format($TableResult[$ModelInTransfer]['quantity'], 
											$TableResult[$ModelInTransfer]['decimalplaces']), 'right', 0, $Fill);
					$pdf->addTextWrap(450, $YPos, 40, 11, 
							locale_number_format($TableResult[$ModelInTransfer]['shipqty'], 
											$TableResult[$ModelInTransfer]['decimalplaces']), 'right', 0, $Fill);
					$pdf->addTextWrap(510, $YPos, 50, $FontSize, '___________', 'right', 0, $Fill);

					if ($TableResult[$ModelInTransfer]['discountcategory'] != ""){
						$DiscountLine = ' -> ' . __('Discount Category') . ':' . $TableResult[$ModelInTransfer]['discountcategory'];
					}else{
						$DiscountLine = '';
					}
					if ($DefaultPrice != 0){
						$PriceLine = $ToPriceList . ":" . 
								locale_number_format($TableResult[$ModelInTransfer]['price'], $ToDecimalPlaces) . 
								" " . $ToCurrency . $DiscountLine;
						$pdf->addTextWrap(180, $YPos - 0.5 * $LineHeight, 200, $FontSize, $PriceLine, '', 0, $Fill);
					}

					if ($YPos < $Bottom_Margin + $LineHeight + 200){
						PrintHeaderSmartStockDispatch($pdf, $YPos, $PageNumber, $Page_Height, $Top_Margin, $Left_Margin, 
													$Page_Width, $Right_Margin, $Trf_ID, $FromLocCode, $FromLocation, 
													$ToLocCode, $ToLocation, $CategoryDescription, $Strategy);
					}

					if ($ReportType == 'Batch') {
						// Create loctransfers records for each record
						$SQL2 = "INSERT INTO loctransfers (reference,
															stockid,
															shipqty,
															shipdate,
															shiploc,
															recloc)
														VALUES ('" . $Trf_ID . "',
															'" . $TableResult[$ModelInTransfer]['stockid'] . "',
															'" . $TableResult[$ModelInTransfer]['shipqty'] . "',
															'" . $Now . "',
															'" . $FromLocCode . "',
															'" . $ToLocCode . "')";
						$ErrMsg = __('CRITICAL ERROR') . '! ' . 
								__('Unable to enter Location Transfer record for') . ' ' . 
								$TableResult[$ModelInTransfer]['stockid'];
						DB_query($SQL2, $ErrMsg);
					}
				}

				// if we reached the maximum of models allowed per dispatch, we warn the user
				if ($NumModelsInThisStockDispatch == $MaxModelsPerDispatch){
					$ModelsSkipped = 0;
					while ($MyRow = DB_fetch_array($Result)){
						$ModelsSkipped++;
					}
					$YPos -= (2 * $LineHeight);
					$WarningMaxModels = "Reached the maximum of " . $MaxModelsPerDispatch . " models per transfer.";
					$WarningModelsSkipped = "Skipped " . $ModelsSkipped . " models for next transfers.";
					$pdf->addTextWrap(50, $YPos, 500, 9, $WarningMaxModels, 'left');
					$EmailText .= $WarningMaxModels . "\n" . $WarningModelsSkipped . "\n";
				}

				$EmailText .= "# Models in this transfer = " . locale_number_format($NumModelsInThisStockDispatch, 0) . "\n" .
										  "# Pieces in this transfer = " . locale_number_format($NumPcsInThisStockDispatch, 0) . "\n";

				$YPos -= (3 * $LineHeight);
				$pdf->addTextWrap(50, $YPos, 500, 9, "# Pieces in this transfer = " . 
								locale_number_format($NumPcsInThisStockDispatch, 0), 'left');
				
				//add prepared by
				$pdf->addTextWrap(50, $YPos - 50, 100, 9, __('Prepared By :'), 'left');
				$pdf->addTextWrap(50, $YPos - 70, 100, $FontSize, __('Name'), 'left');
				$pdf->addTextWrap(90, $YPos - 70, 200, $FontSize, ':__________________', 'left', 0, $Fill);
				$pdf->addTextWrap(50, $YPos - 90, 100, $FontSize, __('Date'), 'left');
				$pdf->addTextWrap(90, $YPos - 90, 200, $FontSize, ':__________________', 'left', 0, $Fill);
				$pdf->addTextWrap(50, $YPos - 110, 100, $FontSize, __('Hour'), 'left');
				$pdf->addTextWrap(90, $YPos - 110, 200, $FontSize, ':__________________', 'left', 0, $Fill);
				$pdf->addTextWrap(50, $YPos - 150, 100, $FontSize, __('Signature'), 'left');
				$pdf->addTextWrap(90, $YPos - 150, 200, $FontSize, ':__________________', 'left', 0, $Fill);

				//add shipped by
				$pdf->addTextWrap(240, $YPos - 50, 100, 9, __('Shipped By :'), 'left');
				$pdf->addTextWrap(240, $YPos - 70, 100, $FontSize, __('Name'), 'left');
				$pdf->addTextWrap(280, $YPos - 70, 200, $FontSize, ':__________________', 'left', 0, $Fill);
				$pdf->addTextWrap(240, $YPos - 90, 100, $FontSize, __('Date'), 'left');
				$pdf->addTextWrap(280, $YPos - 90, 200, $FontSize, ':__________________', 'left', 0, $Fill);
				$pdf->addTextWrap(240, $YPos - 110, 100, $FontSize, __('Hour'), 'left');
				$pdf->addTextWrap(280, $YPos - 110, 200, $FontSize, ':__________________', 'left', 0, $Fill);
				$pdf->addTextWrap(240, $YPos - 150, 100, $FontSize, __('Signature'), 'left');
				$pdf->addTextWrap(280, $YPos - 150, 200, $FontSize, ':__________________', 'left', 0, $Fill);

				//add received by
				$pdf->addTextWrap(440, $YPos - 50, 100, 9, __('Received By :'), 'left');
				$pdf->addTextWrap(440, $YPos - 70, 100, $FontSize, __('Name'), 'left');
				$pdf->addTextWrap(480, $YPos - 70, 200, $FontSize, ':__________________', 'left', 0, $Fill);
				$pdf->addTextWrap(440, $YPos - 90, 100, $FontSize, __('Date'), 'left');
				$pdf->addTextWrap(480, $YPos - 90, 200, $FontSize, ':__________________', 'left', 0, $Fill);
				$pdf->addTextWrap(440, $YPos - 110, 100, $FontSize, __('Hour'), 'left');
				$pdf->addTextWrap(480, $YPos - 110, 200, $FontSize, ':__________________', 'left', 0, $Fill);
				$pdf->addTextWrap(440, $YPos - 150, 100, $FontSize, __('Signature'), 'left');
				$pdf->addTextWrap(480, $YPos - 150, 200, $FontSize, ':__________________', 'left', 0, $Fill);

				if ($YPos < $Bottom_Margin + $LineHeight){
					PrintHeaderSmartStockDispatch($pdf, $YPos, $PageNumber, $Page_Height, $Top_Margin, $Left_Margin, 
													$Page_Width, $Right_Margin, $Trf_ID, $FromLocCode, $FromLocation, 
													$ToLocCode, $ToLocation, $CategoryDescription, $Strategy);
				}
				/*Print out the grand totals */
				$Subject = 'Transfer-' . Date('Y-m-d') . '-' . $FromLocCode . '-' . $ToLocCode;
				$FileName = $Subject . '.pdf';
				$PathFileName = $_SESSION['reports_dir'] . '/' . $FileName;
				$pdf->Output($PathFileName, 'F');
				$pdf->__destruct();

				$Text = 'Please prepare this transfer ASAP';
				$Text .= "\n---\r\n"; // \r is needed for signature separating
				$Text .= 'Email sent by webERP KL CRON JOB at ' . date('d/M/Y H:i:s') . '';

				$Result = SendEmailFromWebERP('webmaster@kapal-laut.com',
											'kl-shopsupport@kapal-laut.com',
											$Subject,
											$Text,
											$PathFileName,
											true);

				if($Result){
					$EmailText .= date('d/M/Y H:i:s') . " Email Sent " . $FileName . "\n";
				}else{
					$EmailText .= date('d/M/Y H:i:s') . " Email FAILED " . $FileName . "\n";
				}
				// we don't need to sleep as this is a heavy process script, so from email to email there is already a few secs
				// and we don't risk to be considered spam by the server and blocked
				// sleep(2);
				// End of preparation of PDF, email and transfer records
			}else{
				// NOT Enough models available for transfer
				if ($Strategy == 'All'){
					$EmailText .= "Less than " . $MinModelsPerDispatch . " Items for this transfer with Strategy All" . "\n";
				}else{
					$EmailText .= "Less than " . $MinModelsPerDispatch . " Items for this transfer with Strategy OverFrom" . "\n";
				}
			}
		}else{
			// No models to be dispatched
			$EmailText .= "No Items available for this transfer" . "\n";
		}
	}
	return $EmailText;
}


/**************************************************************************************************************
Function: PrintHeaderSmartStockDispatch

Brief description:
Prints the PDF header for smart stock dispatch reports. Creates a formatted header with company information,
transfer details, location information, and column headings for the stock dispatch report.

Parameters:
- &$pdf (object): Reference to PDF object for rendering
- &$YPos (int): Reference to current Y position on the page (modified by function)
- &$PageNumber (int): Reference to current page number (incremented by function)
- $Page_Height (int): Total height of the PDF page
- $Top_Margin (int): Top margin of the PDF page
- $Left_Margin (int): Left margin of the PDF page
- $Page_Width (int): Total width of the PDF page
- $Right_Margin (int): Right margin of the PDF page
- $Trf_ID (string): Transfer ID number
- $FromLocCode (string): Source location code
- $FromLocation (string): Source location name
- $ToLocCode (string): Destination location code
- $ToLocation (string): Destination location name
- $CategoryDescription (string): Category description for the transfer
- $Strategy (string): Transfer strategy being used

Returns:
- void: Function modifies PDF object and position variables by reference
**************************************************************************************************************/

function PrintHeaderSmartStockDispatch(&$pdf, &$YPos, &$PageNumber, $Page_Height, $Top_Margin, $Left_Margin, 
									$Page_Width, $Right_Margin, $Trf_ID, $FromLocCode, $FromLocation, 
									$ToLocCode, $ToLocation, $CategoryDescription, $Strategy) {


	/*PDF page header for Stock Dispatch report */
	if ($PageNumber > 1){
		$pdf->newPage();
	}
	$LineHeight = 12;
	$FontSize = 9;
	$YPos = $Page_Height - $Top_Margin;
	$YPos -= (3 * $LineHeight);

	$pdf->addTextWrap($Left_Margin, $YPos, 300, $FontSize, $_SESSION['CompanyRecord']['coyname']);
	$YPos -= $LineHeight;

	$pdf->addTextWrap($Left_Margin, $YPos, 150, $FontSize, __('Shop Transfer'));
	$pdf->setFont('', 'B');
	$pdf->addTextWrap(200, $YPos, 30, $FontSize, __('From :'));
	$pdf->addTextWrap(230, $YPos, 200, $FontSize, $FromLocation);
	$pdf->setFont('', '');

	$pdf->addTextWrap($Page_Width - $Right_Margin - 150, $YPos, 160, $FontSize, __('Printed') . ': ' .
		 Date($_SESSION['DefaultDateFormat']) . '   ' . __('Page') . ' ' . $PageNumber, 'left');
	$YPos -= $LineHeight;
	$pdf->addTextWrap($Left_Margin, $YPos, 50, $FontSize, __('Transfer'));
	$pdf->addTextWrap(95, $YPos, 50, $FontSize, $Trf_ID);
	$pdf->setFont('', 'B');
	$pdf->addTextWrap(200, $YPos, 30, $FontSize, __('To :'));
	$pdf->addTextWrap(230, $YPos, 200, $FontSize, $ToLocation);
	$pdf->setFont('', '');
	$YPos -= $LineHeight;
	$pdf->addTextWrap($Left_Margin, $YPos, 50, $FontSize, '');
	$pdf->addTextWrap(160, $YPos, 150, $FontSize, '', 'left');
	$YPos -= $LineHeight;
	$pdf->addTextWrap($Left_Margin, $YPos, 50, $FontSize, '');
	$pdf->addTextWrap(95, $YPos, 50, $FontSize, '');
	if ($Strategy == 'OverFrom') {
		$pdf->addTextWrap(200, $YPos, 200, $FontSize, __('Overstock items at ') . $FromLocation);
	}else{
		$pdf->addTextWrap(200, $YPos, 200, $FontSize, __('Items needed at ') . $ToLocation);
	}
	$YPos -= (2 * $LineHeight);
	/*set up the headings */

	$FontSize = 8;

	$pdf->addTextWrap(50, $YPos, 100, $FontSize, __('Item Code'), 'left');
	$pdf->addTextWrap(135, $YPos, 170, $FontSize, __('Image/Description'), 'left');
	$pdf->addTextWrap(362, $YPos, 40, $FontSize, __('Qty@'), 'right');
	$pdf->addTextWrap(413, $YPos, 40, $FontSize, __('Qty@'), 'right');
	$pdf->addTextWrap(460, $YPos, 40, $FontSize, __('Shipped'), 'right');
	$pdf->addTextWrap(510, $YPos, 40, $FontSize, __('Received'), 'right');
	$YPos -= $LineHeight;
	$pdf->addTextWrap(365, $YPos, 40, $FontSize, $FromLocCode, 'right');
	$pdf->addTextWrap(415, $YPos, 40, $FontSize, $ToLocCode, 'right');

	$PageNumber++;
} // End of PrintHeaderSmartStockDispatch() function

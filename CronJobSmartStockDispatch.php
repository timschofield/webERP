<?php

include('CronJobStart.php');
include('config.php');
include('includes/session_cronjob.php');
include('includes/SQL_CommonFunctions.inc');
include('includes/htmlMimeMail.php');
include('includes/GetPrice.inc');

$time = microtime();
$time = explode(' ', $time);
$begintime = $time[1] + $time[0];

$EmailText  = "KL webERP: Smart Stock Dispatch " . "\n"; 
$EmailText = $EmailText . 'Cron Job started at '.date('d/M/Y H:i:s'). "\n";

/* Parameters */
$ReportType = "Batch"; // ONLY FOR REAL ENVIRONMENT
//$ReportType = "ReportOnly"; // ONLY FOR TESTS

$DispatchPercent = 0;
$_SESSION['DefaultPageSize'] = 'A4';
$DaysSalesForOrder = 2;

# GRAB THE VARIABLES FROM THE URL
$Group = $_GET['p'];

if ($Group == "1050-SmartDispatchKL"){
	$ScriptTile  = "Cron Job Smart dispatch KL"; 
	$ShopType = "SHOPKL";
	$EmailText = $EmailText . 'Smart dispatch for Kapal-Laut Shops' . "\n";
}elseif ($Group == "1060-SmartDispatchBL"){
	$ScriptTile  = "Cron Job Smart dispatch BL"; 
	$ShopType = "SHOPBL";
	$EmailText = $EmailText . 'Smart dispatch for Blink Shops' . "\n";
}elseif ($Group == "1070-SmartDispatchOU"){
	$ScriptTile  = "Cron Job Smart dispatch OU"; 
	$ShopType = "SHOPOU";
	$EmailText = $EmailText . 'Smart dispatch for Outlet Shops' . "\n";
}else{
	$ScriptTile  = "Cron Job Smart dispatch UNDEFINED"; 
	$EmailText = $EmailText . 'Type Of Shop not defined' . "\n";
}

/* Selection of shops with smart dispatch from / to KANTO, sorted by priority and sales of the last X days */
$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$DaysSalesForOrder));

$DayOfWeek = date('w', strtotime(Date('Y-m-d')));

$SQL = "SELECT locations.loccode,
				locations.smartdispatchmaxmodels,
				locations.smartdispatchminmodels
		FROM locations,locationzones
		WHERE locations.zone = locationzones.code
			AND locations.smartdispatchfrom = 'KANTO' 
			AND locations.typeloc = '" . $ShopType . "' 
			AND locationzones.smarttransferonweekday".$DayOfWeek . " = 1 
		ORDER BY locations.priority ASC,
			(SELECT COUNT(qtyinvoiced)
			FROM salesorderdetails, salesorders
			WHERE salesorderdetails.orderno = salesorders.orderno
				AND salesorderdetails.completed = 1
				AND salesorders.orddate >= '". $StartDate . "'
				AND salesorders.fromstkloc = locations.loccode) DESC";

$result = DB_query($SQL);
if (DB_num_rows($result) != 0){
	while ($myrow = DB_fetch_array($result)) {
		// From KANTO to Shop, send the items needed to fill the RL
		$EmailText  = KLStockDispatch('KANTO', $myrow['loccode'], "All", $ReportType, $DispatchPercent, $myrow['smartdispatchmaxmodels'], $myrow['smartdispatchminmodels'], $RootPath, $db, $EmailText);
		// From Shop to KANTO, return the overstock
		$EmailText  = KLStockDispatch($myrow['loccode'], 'KANTO', "OverFrom", $ReportType, $DispatchPercent, $myrow['smartdispatchmaxmodels'], $myrow['smartdispatchminmodels'], $RootPath, $db, $EmailText);
	}
}

$EmailAddress = "webmaster@kapal-laut.com";
$EmailSubject  = "KL webERP Cron Job: Smart Dispatch ". $Group;
SendEmailFromCron($EmailAddress, $EmailSubject, $EmailText, '', $begintime, $ScriptTile);

/****************************************************************************************/
function KLStockDispatch($FromLocCode, $ToLocCode, $Strategy, $ReportType, $DispatchPercent, $MaxModelsPerDispatch, $MinModelsPerDispatch, $RootPath, $db, $EmailText){

	$TableResult = array();

	// from location
	$ErrMsg = _('Could not retrieve location name from the database');
	$sqlfrom="SELECT locationname FROM `locations` WHERE loccode='" . $FromLocCode . "'";
	$result = DB_query($sqlfrom,$ErrMsg);
	$Row = DB_fetch_row($result);
	$FromLocation=$Row['0'];

	// to location
	$sqlto="SELECT locationname,
					cashsalecustomer,
					cashsalebranch
			FROM `locations` 
			WHERE loccode='" . $ToLocCode . "'";
	$resultto = DB_query($sqlto,$ErrMsg);
	$RowTo = DB_fetch_row($resultto);
	$ToLocation=$RowTo['0'];
	$ToCustomer=$RowTo['1'];
	$ToBranch=$RowTo['2'];

	$SqlPrices="SELECT debtorsmaster.currcode,
					debtorsmaster.salestype,
					currencies.decimalplaces
			FROM debtorsmaster, currencies
			WHERE debtorsmaster.currcode = currencies.currabrev 
				AND debtorsmaster.debtorno ='" . $ToCustomer . "'";
	$ResultPrices = DB_query($SqlPrices,$ErrMsg);
	$RowPrices = DB_fetch_row($ResultPrices);
	$ToCurrency=$RowPrices['0'];
	$ToPriceList=$RowPrices['1'];
	$ToDecimalPlaces=$RowPrices['2'];	
	
	$CategoryDescription=_('All');
	$WhereCategory = " AND stockmaster.categoryid !='SHCONS'
						   AND stockmaster.categoryid !='SHPACK' ";

	// If Strategy is "Items needed at TO location with overstock at FROM" we need to control the "needed at TO" part
	// The "overstock at FROM" part is controlled in any case with AND (fromlocstock.quantity - fromlocstock.reorderlevel) > 0
	if ($Strategy == 'All') {
		$WhereCategory = $WhereCategory . " AND locstock.reorderlevel > locstock.quantity ";
		$StrategyText = "Items needed at ". $ToLocCode ." with stock available at " . $FromLocCode . " ";
	}else{
		$StrategyText = "Items with overstock at " . $FromLocCode. " returning to " . $ToLocCode;
	}

	$sql = "SELECT locstock.stockid,
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
				ON stockmaster.categoryid=stockcategory.categoryid,
			locstock
			LEFT JOIN locstock AS fromlocstock ON
			  locstock.stockid = fromlocstock.stockid
			  AND fromlocstock.loccode = '" . $FromLocCode . "'
			WHERE locstock.stockid=stockmaster.stockid
			AND locstock.loccode ='" . $ToLocCode . "'
			AND (fromlocstock.quantity - fromlocstock.reorderlevel) > 0
			AND stockcategory.stocktype<>'A'
			AND (stockmaster.mbflag='B' OR stockmaster.mbflag='M') " .
			$WhereCategory . 
			" ORDER BY stockcategory.klprioritytransfers,
						locstock.stockid";

	$result = DB_query($sql,'','',false,true);

	$EmailText = $EmailText .  "\n" . 
				"Smart Stock Dispatch from " . $FromLocCode . " to " . $ToLocCode . "\n" . 
				" " . $StrategyText . "\n";
	$EmailText = $EmailText .  
				"Min Models to create transfer: " . $MinModelsPerDispatch . "\n" . 
				"Max Models to be included: " . $MaxModelsPerDispatch . "\n";

	if (DB_error_no() !=0) {
		$EmailText = $EmailText . "Smart Stock Dispatch ERROR " .  _('The Stock Dispatch report could not be retrieved by the SQL because') . ' '  . DB_error_msg() . "\n";
		$EmailText = $EmailText . "SQL = " .  $sql . "\n";
	}else{
		// Let's do the calculation for the available items for transfer and load them into TableResult array
		$Now = Date('Y-m-d H-i-s');
		$EmailText = $EmailText .  
				"Models candidates to be included in transfer: " . DB_num_rows($result) . "\n";
		$NumModelsInThisStockDispatch = 0;
		$NumPcsInThisStockDispatch = 0;
		while (($myrow = DB_fetch_array($result)) AND ($NumModelsInThisStockDispatch < $MaxModelsPerDispatch)){
			// Check if there is any stock in transit already sent from FROM LOCATION
			$InTransitQuantityAtFrom = 0;
			if ($_SESSION['ProhibitNegativeStock']==1){
				$InTransitSQL="SELECT SUM(pendingqty) as intransit
								FROM loctransfers
								WHERE stockid='" . $myrow['stockid'] . "'
									AND shiploc='".$FromLocCode."'
									AND pendingqty > 0";
				$InTransitResult=DB_query($InTransitSQL);
				$InTransitRow=DB_fetch_array($InTransitResult);
				if ($InTransitRow['intransit']!='') {
					$InTransitQuantityAtFrom=$InTransitRow['intransit'];
				} else {
					$InTransitQuantityAtFrom=0;
				}
			}
			// The real available stock to ship is the (qty - reorder level - in transit).
			$AvailableShipQtyAtFrom = $myrow['fromquantity'] - $myrow['fromreorderlevel'] - $InTransitQuantityAtFrom;

			// Check if TO location is already waiting to receive some stock of this item
			$InTransitQuantityAtTo=0;
			$InTransitSQL="SELECT SUM(pendingqty) as intransit
							FROM loctransfers
							WHERE stockid='" . $myrow['stockid'] . "'
								AND recloc='".$ToLocCode."'
								AND pendingqty > 0";
			$InTransitResult=DB_query($InTransitSQL);
			$InTransitRow=DB_fetch_array($InTransitResult);
			if ($InTransitRow['intransit']!='') {
				$InTransitQuantityAtTo=$InTransitRow['intransit'];
			} else {
				$InTransitQuantityAtTo=0;
			}

			// The real needed stock is reorder level - qty - in transit).
			$NeededQty = round(($myrow['reorderlevel']-$myrow['quantity']) * (1 + $DispatchPercent /100));
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
			if ($ShipQty>0){
				if (file_exists($_SESSION['part_pics_dir'] . '/' .$myrow['stockid'].'.jpg')){
					$NumModelsInThisStockDispatch++;
					$NumPcsInThisStockDispatch = $NumPcsInThisStockDispatch + $ShipQty;

					// looking for price info  
					$DefaultPrice = GetPrice($myrow['stockid'],$ToCustomer, $ToBranch, $ShipQty, false);
					
					$TableResult[$NumModelsInThisStockDispatch]['stockid'] = $myrow['stockid'];
					$TableResult[$NumModelsInThisStockDispatch]['description'] = $myrow['description'];
					$TableResult[$NumModelsInThisStockDispatch]['fromquantity'] = $myrow['fromquantity'] - $InTransitQuantityAtFrom;
					$TableResult[$NumModelsInThisStockDispatch]['quantity'] = $myrow['quantity'] + $InTransitQuantityAtTo;
					$TableResult[$NumModelsInThisStockDispatch]['shipqty'] = $ShipQty;
					$TableResult[$NumModelsInThisStockDispatch]['decimalplaces'] = $myrow['decimalplaces'];
					$TableResult[$NumModelsInThisStockDispatch]['price'] = $DefaultPrice;
					$TableResult[$NumModelsInThisStockDispatch]['discountcategory'] = $myrow['discountcategory'];
					
					$EmailText = $EmailText . $myrow['stockid'] . " x " . $ShipQty . "\n";

				}else{
					$EmailText = $EmailText . $myrow['stockid'] . " x " . $NeededQtyAtTo . " rejected no picture" . "\n";
				}
			}else{
				if ($NeededQtyAtTo<=0){
					$EmailText = $EmailText . $myrow['stockid'] . 
											  " x " . 
											  $NeededQty . 
											  " Already in transit = " . 
											  $InTransitQuantityAtTo  . 
											  "\n";
				}else{
					$EmailText = $EmailText . $myrow['stockid'] . 
											  " x " . 
											  $NeededQty . 
											  " Already in transit = " . 
											  $InTransitQuantityAtTo  . 
											  " Rejected as stock available @" . 
											  $FromLocCode .  
											  " = " . 
											  $AvailableShipQtyAtFrom  . "\n";
				}
				
			}
		} /*end while loop  */

		if ($NumModelsInThisStockDispatch > 0){
			// There are some models to be dispatched
			if ($NumModelsInThisStockDispatch >= $MinModelsPerDispatch){
				// Enough models available for transfer
				// OK, let's create the PDF and the transfer records
				include('includes/PDFStarter.php');
				$pdf->addInfo('Title',_('KL Stock Dispatch Report'));
				$pdf->addInfo('Subject',_('Items to dispatch to another location to cover reorder level'));
				$FontSize=9;
				$PageNumber=1;
				$line_height=19;
				$Xpos = $Left_Margin+1;

				// Create Transfer Number
				if(!isset($Trf_ID) and $ReportType == 'Batch') {
					$Trf_ID = GetNextTransNo(16,$db);
					$EmailText = $EmailText . "Transfer # " . $Trf_ID . "\n";
				}

				PrintHeader($pdf,$YPos,$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,
							$Page_Width,$Right_Margin,$Trf_ID,$FromLocCode,$FromLocation,$ToLocCode,$ToLocation,$CategoryDescription);

				$FontSize=8;
				$ModelInTransfer = 0;
				while ($ModelInTransfer < $NumModelsInThisStockDispatch){
					$ModelInTransfer++;
					
					$YPos -=(2 * $line_height);
					$fill = False;
				
					$pdf->addTextWrap(50,$YPos,70,$FontSize,$TableResult[$ModelInTransfer]['stockid'],'',0,$fill);
					$pdf->Image($_SESSION['part_pics_dir'] . '/'.$TableResult[$ModelInTransfer]['stockid'].'.jpg',135,$Page_Height-$Top_Margin-$YPos+10,45,35);
					$pdf->addTextWrap(180,$YPos,200,$FontSize,$TableResult[$ModelInTransfer]['description'],'',0,$fill);
					$pdf->addTextWrap(355,$YPos,40,$FontSize,locale_number_format($TableResult[$ModelInTransfer]['fromquantity'],$TableResult[$ModelInTransfer]['decimalplaces']),'right',0,$fill);
					$pdf->addTextWrap(405,$YPos,40,$FontSize,locale_number_format($TableResult[$ModelInTransfer]['quantity'],$TableResult[$ModelInTransfer]['decimalplaces']),'right',0,$fill);
					$pdf->addTextWrap(450,$YPos,40,11,locale_number_format($TableResult[$ModelInTransfer]['shipqty'],$TableResult[$ModelInTransfer]['decimalplaces']),'right',0,$fill);
					$pdf->addTextWrap(510,$YPos,50,$FontSize,'___________','right',0,$fill);

					if ($TableResult[$ModelInTransfer]['discountcategory'] != "")
					{
						$DiscountLine = ' -> ' . _('Discount Category') . ':' . $TableResult[$ModelInTransfer]['discountcategory'];
					}else{
						$DiscountLine = '';
					}
					if ($DefaultPrice != 0){
						$PriceLine = $ToPriceList . ":" . locale_number_format($TableResult[$ModelInTransfer]['price'],$ToDecimalPlaces) . " " . $ToCurrency . $DiscountLine;
						$pdf->addTextWrap(180,$YPos - 0.5 * $line_height,200,$FontSize,$PriceLine,'',0,$fill);
					}

					if ($YPos < $Bottom_Margin + $line_height + 200){
						PrintHeader($pdf,$YPos,$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,$Page_Width,$Right_Margin,$Trf_ID,$FromLocCode,$FromLocation,$ToLocCode,$ToLocation,$CategoryDescription);
					}

					if ($ReportType == 'Batch') {
						// Create loctransfers records for each record
						$sql2 = "INSERT INTO loctransfers (reference,
															stockid,
															shipqty,
															shipdate,
															shiploc,
															recloc)
														VALUES ('" . $Trf_ID . "',
															'" . $TableResult[$ModelInTransfer]['stockid'] . "',
															'" . $TableResult[$ModelInTransfer]['shipqty'] . "',
															'" . $Now . "',
															'" . $FromLocCode  ."',
															'" . $ToLocCode . "')";
						$ErrMsg = _('CRITICAL ERROR') . '! ' . _('Unable to enter Location Transfer record for'). ' '.$TableResult[$ModelInTransfer]['stockid'];
						$resultLocShip = DB_query($sql2, $ErrMsg);
					}
				}

				// if we reached the maximum of models allowed per dispatch, we warn the user
				if ($NumModelsInThisStockDispatch == $MaxModelsPerDispatch){
					$ModelsSkipped = 0;
					while ($myrow = DB_fetch_array($result)){
						$ModelsSkipped++;
					}
					$YPos -=(2 * $line_height);
					$WarningMaxModels = "Reached the maximum of " . $MaxModelsPerDispatch . " models per transfer.";
					$WarningModelsSkipped = "Skipped " . $ModelsSkipped . " models for next transfers.";
					$pdf->addTextWrap(50,$YPos,500,9,$WarningMaxModels, 'left');
					$EmailText = $EmailText . $WarningMaxModels . "\n" . $WarningModelsSkipped . "\n";
				}
				
				$EmailText = $EmailText . "# Models in this transfer = " . locale_number_format($NumModelsInThisStockDispatch,0) . "\n" . 
										  "# Pieces in this transfer = " . locale_number_format($NumPcsInThisStockDispatch,0) . "\n";

				$YPos -=(3 * $line_height);
				$pdf->addTextWrap(50,$YPos,500,9,"# Pieces in this transfer = " . locale_number_format($NumPcsInThisStockDispatch,0), 'left');
				
				//add prepared by
				$pdf->addTextWrap(50,$YPos-50,100,9,_('Prepared By :'), 'left');
				$pdf->addTextWrap(50,$YPos-70,100,$FontSize,_('Name'), 'left');
				$pdf->addTextWrap(90,$YPos-70,200,$FontSize,':__________________','left',0,$fill);
				$pdf->addTextWrap(50,$YPos-90,100,$FontSize,_('Date'), 'left');
				$pdf->addTextWrap(90,$YPos-90,200,$FontSize,':__________________','left',0,$fill);
				$pdf->addTextWrap(50,$YPos-110,100,$FontSize,_('Hour'), 'left');
				$pdf->addTextWrap(90,$YPos-110,200,$FontSize,':__________________','left',0,$fill);
				$pdf->addTextWrap(50,$YPos-150,100,$FontSize,_('Signature'), 'left');
				$pdf->addTextWrap(90,$YPos-150,200,$FontSize,':__________________','left',0,$fill);

				//add shipped by
				$pdf->addTextWrap(240,$YPos-50,100,9,_('Shipped By :'), 'left');
				$pdf->addTextWrap(240,$YPos-70,100,$FontSize,_('Name'), 'left');
				$pdf->addTextWrap(280,$YPos-70,200,$FontSize,':__________________','left',0,$fill);
				$pdf->addTextWrap(240,$YPos-90,100,$FontSize,_('Date'), 'left');
				$pdf->addTextWrap(280,$YPos-90,200,$FontSize,':__________________','left',0,$fill);
				$pdf->addTextWrap(240,$YPos-110,100,$FontSize,_('Hour'), 'left');
				$pdf->addTextWrap(280,$YPos-110,200,$FontSize,':__________________','left',0,$fill);
				$pdf->addTextWrap(240,$YPos-150,100,$FontSize,_('Signature'), 'left');
				$pdf->addTextWrap(280,$YPos-150,200,$FontSize,':__________________','left',0,$fill);

				//add received by
				$pdf->addTextWrap(440,$YPos-50,100,9,_('Received By :'), 'left');
				$pdf->addTextWrap(440,$YPos-70,100,$FontSize,_('Name'), 'left');
				$pdf->addTextWrap(480,$YPos-70,200,$FontSize,':__________________','left',0,$fill);
				$pdf->addTextWrap(440,$YPos-90,100,$FontSize,_('Date'), 'left');
				$pdf->addTextWrap(480,$YPos-90,200,$FontSize,':__________________','left',0,$fill);
				$pdf->addTextWrap(440,$YPos-110,100,$FontSize,_('Hour'), 'left');
				$pdf->addTextWrap(480,$YPos-110,200,$FontSize,':__________________','left',0,$fill);
				$pdf->addTextWrap(440,$YPos-150,100,$FontSize,_('Signature'), 'left');
				$pdf->addTextWrap(480,$YPos-150,200,$FontSize,':__________________','left',0,$fill);

				if ($YPos < $Bottom_Margin + $line_height){
					   PrintHeader($pdf,$YPos,$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,$Page_Width,
								   $Right_Margin,$Trf_ID,$FromLocCode,$FromLocation,$ToLocCode,$ToLocation,$CategoryDescription);
				}
				/*Print out the grand totals */

				$Subject  = 'Transfer-' . Date('Y-m-d') .  '-' . $FromLocCode . '-' . $ToLocCode;
				$FileName = $Subject . '.pdf';
				$Text = 'Please prepare this transfer ASAP';
				$Text = $Text . "\n---\r\n"; // \r is needed for signature separating
				$Text = $Text . 'Email sent by webERP KL CRON JOB at '.date('d/M/Y H:i:s').'';
				
				$pdf->Output($_SESSION['reports_dir'] . '/' . $FileName, 'F');
				$pdf-> __destruct();

				$mail = new htmlMimeMail();
				$attachment = $mail->getFile($_SESSION['reports_dir'] . '/' . $FileName);
				$mail->setText($Text);
				$mail->setSubject($Subject);
				$mail->addAttachment($attachment, $FileName, 'application/pdf');
				$mail->setFrom('webmaster@kapal-laut.com', 'webERP Cron Job');
				// if we are preparing real transfers (Batch), send to team, otherwise send to test user.
				if ($ReportType == 'Batch'){
					$result = $mail->send(array('kl-shopsupport@kapal-laut.com'));
				}else{
					$result = $mail->send(array('sysadmin@kapal-laut.com'));
				}
				if($result){
					$EmailText = $EmailText . date('d/M/Y H:i:s') . " Email Sent " . $FileName . "\n";
				}else{
					$EmailText = $EmailText . date('d/M/Y H:i:s') . " Email FAILED " . $FileName . "\n";
				}
// we don't need to sleep as this is a heavy process script, so from email to email there is already a few secs
// and we don't risk to be considered spam by the server and blocked
//				sleep(2);
				// End of preparation of PDF, email and transfer records 
			}else{
				// NOT Enough models available for transfer
				if ($Strategy == 'All'){
					$EmailText = $EmailText . "Less than " . $MinModelsPerDispatch . " Items for this transfer with Strategy All" . "\n";
				}else{
					$EmailText = $EmailText . "Less than " . $MinModelsPerDispatch . " Items for this transfer with Strategy OverFrom" . "\n";
				}
			}
		}else{
			// No models to be dispatched
			$EmailText = $EmailText . "No Items available for this transfer" . "\n";
		}
	}
	return $EmailText;
}


function PrintHeader(&$pdf,&$YPos,&$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,
					 $Page_Width,$Right_Margin,$Trf_ID,$FromLocCode,$FromLocation,$ToLocCode,$ToLocation,$CategoryDescription) {


	/*PDF page header for Stock Dispatch report */
	if ($PageNumber>1){
		$pdf->newPage();
	}
	$line_height=12;
	$FontSize=9;
	$YPos= $Page_Height-$Top_Margin;
	$YPos -=(3*$line_height);

	$pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize,$_SESSION['CompanyRecord']['coyname']);
	$YPos -=$line_height;

	$pdf->addTextWrap($Left_Margin,$YPos,150,$FontSize,_('Shop Transfer'));
	$pdf->setFont('','B');
	$pdf->addTextWrap(200,$YPos,30,$FontSize,_('From :'));
	$pdf->addTextWrap(230,$YPos,200,$FontSize,$FromLocation);
	$pdf->setFont('','');

	$pdf->addTextWrap($Page_Width-$Right_Margin-150,$YPos,160,$FontSize,_('Printed') . ': ' .
		 Date($_SESSION['DefaultDateFormat']) . '   ' . _('Page') . ' ' . $PageNumber,'left');
	$YPos -= $line_height;
	$pdf->addTextWrap($Left_Margin,$YPos,50,$FontSize,_('Transfer'));
	$pdf->addTextWrap(95,$YPos,50,$FontSize,$Trf_ID);
	$pdf->setFont('','B');
	$pdf->addTextWrap(200,$YPos,30,$FontSize,_('To :'));
	$pdf->addTextWrap(230,$YPos,200,$FontSize,$ToLocation);
	$pdf->setFont('','');
	$YPos -= $line_height;
	$pdf->addTextWrap($Left_Margin,$YPos,50,$FontSize,'');
	$pdf->addTextWrap(160,$YPos,150,$FontSize,'','left');
	$YPos -= $line_height;
	$pdf->addTextWrap($Left_Margin,$YPos,50,$FontSize,'');
	$pdf->addTextWrap(95,$YPos,50,$FontSize,'');
	if ($Strategy == 'OverFrom') {
		$pdf->addTextWrap(200,$YPos,200,$FontSize,_('Overstock items at '). $FromLocation);
	}else{
		$pdf->addTextWrap(200,$YPos,200,$FontSize,_('Items needed at '). $ToLocation);
	}
	$YPos -=(2*$line_height);
	/*set up the headings */
	$Xpos = $Left_Margin+1;

	$FontSize=8;

	$pdf->addTextWrap(50,$YPos,100,$FontSize,_('Item Code'), 'left');
	$pdf->addTextWrap(135,$YPos,170,$FontSize,_('Image/Description'), 'left');
	$pdf->addTextWrap(362,$YPos,40,$FontSize,_('Qty@'), 'right');
	$pdf->addTextWrap(413,$YPos,40,$FontSize,_('Qty@'), 'right');
	$pdf->addTextWrap(460,$YPos,40,$FontSize,_('Shipped'), 'right');
	$pdf->addTextWrap(510,$YPos,40,$FontSize,_('Received'), 'right');
	$YPos -= $line_height;
	$pdf->addTextWrap(365,$YPos,40,$FontSize,$FromLocCode,'right');
	$pdf->addTextWrap(415,$YPos,40,$FontSize,$ToLocCode,'right');

	$PageNumber++;
} // End of PrintHeader() function

?>
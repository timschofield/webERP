<?php

include('CronJobStart.php');
include('config.php');
include('includes/session_cronjob.php');
include('includes/SQL_CommonFunctions.inc');
include('includes/htmlMimeMail.php');
include('includes/GetPrice.inc');

$EmailText  = "KL webERP: Smart Stock Dispatch " . "\n"; 

/* Parameters */
$ReportType = "Batch"; // ONLY FOR REAL ENVIRONMENT
// $ReportType = "ReportOnly"; // ONLY FOR TESTS

$DispatchPercent = 0;
$_SESSION['DefaultPageSize'] = 'A4';
$DaysSalesForOrder = 2;

/* Selection of shops with smart dispatch from / to KANTO, sorted by priority and sales of the last X days */
$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$DaysSalesForOrder));

$SQL = "SELECT locations.loccode,
				locations.smartdispatchmaxmodels
		FROM locations
		WHERE locations.smartdispatchfrom = 'KANTO'
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
		$EmailText  = KLStockDispatch('KANTO', $myrow['loccode'], "All", $ReportType, $DispatchPercent, $myrow['smartdispatchmaxmodels'], $RootPath, $db, $EmailText);
		// From Shop to KANTO, return the overstock
		$EmailText  = KLStockDispatch($myrow['loccode'], 'KANTO', "OverFrom", $ReportType, $DispatchPercent, $myrow['smartdispatchmaxmodels'], $RootPath, $db, $EmailText);
	}
}

$EmailAddress = "webmaster@kapal-laut.com";
$EmailSubject  = "KL webERP Cron Job: Daily Stock Dispatch";
SendEmailFromCron($EmailAddress, $EmailSubject, $EmailText, '');

/****************************************************************************************/
function KLStockDispatch($FromLocCode, $ToLocCode, $Strategy, $ReportType, $DispatchPercent, $MaxModelsPerDispatch, $RootPath, $db, $EmailText){

	$EmailText = $EmailText .  "\n" . "Smart Stock Dispatch from " . $FromLocCode . " to " . $ToLocCode . "\n" . "Strategy " . $Strategy . "\n";

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
				ROUND((locstock.reorderlevel - locstock.quantity) *
				   (1 + (" . filter_number_format($DispatchPercent) . "/100)))
				as neededqty,
			   (fromlocstock.quantity - fromlocstock.reorderlevel)  as available,
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

	if (DB_error_no() !=0) {
		$EmailText = $EmailText . "Smart Stock Dispatch ERROR " .  _('The Stock Dispatch report could not be retrieved by the SQL because') . ' '  . DB_error_msg() . "\n";
		$EmailText = $EmailText . "SQL = " .  $sql . "\n";
	}elseif (DB_num_rows($result) ==0) {
		$EmailText = $EmailText . "No Items for this transfer" . "\n";
	}else{
		// OK, let's create the PDF

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
					$Page_Width,$Right_Margin,$Trf_ID,$FromLocation,$ToLocation,$CategoryDescription);

		$FontSize=8;
		$Now = Date('Y-m-d H-i-s');
		$NumModelsInThisStockDispatch = 0;
		$NumPcsInThisStockDispatch = 0;
		while (($myrow = DB_fetch_array($result,$db)) AND ($NumModelsInThisStockDispatch < $MaxModelsPerDispatch)){
			// Check if there is any stock in transit already sent from FROM LOCATION
			$InTransitQuantityAtFrom = 0;
			if ($_SESSION['ProhibitNegativeStock']==1){
				$InTransitSQL="SELECT SUM(shipqty-recqty) as intransit
								FROM loctransfers
								WHERE stockid='" . $myrow['stockid'] . "'
									AND shiploc='".$FromLocCode."'
									AND shipqty>recqty";
				$InTransitResult=DB_query($InTransitSQL);
				$InTransitRow=DB_fetch_array($InTransitResult);
				if ($InTransitRow['intransit']!='') {
					$InTransitQuantityAtFrom=$InTransitRow['intransit'];
				} else {
					$InTransitQuantityAtFrom=0;
				}
			}
			// The real available stock to ship is the (qty - reorder level - in transit).
			$AvailableShipQtyAtFrom = $myrow['available'] - $InTransitQuantityAtFrom;

			// Check if TO location is already waiting to receive some stock of this item
			$InTransitQuantityAtTo=0;
			$InTransitSQL="SELECT SUM(shipqty-recqty) as intransit
							FROM loctransfers
							WHERE stockid='" . $myrow['stockid'] . "'
								AND recloc='".$ToLocCode."'
								AND shipqty>recqty";
			$InTransitResult=DB_query($InTransitSQL);
			$InTransitRow=DB_fetch_array($InTransitResult);
			if ($InTransitRow['intransit']!='') {
				$InTransitQuantityAtTo=$InTransitRow['intransit'];
			} else {
				$InTransitQuantityAtTo=0;
			}

			// The real needed stock is reorder level - qty - in transit).
			$NeededQtyAtTo = $myrow['neededqty'] - $InTransitQuantityAtTo;

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
			if (($ShipQty>0) AND (file_exists($_SESSION['part_pics_dir'] . '/' .$myrow['stockid'].'.jpg'))){
				$NumModelsInThisStockDispatch++;
				$NumPcsInThisStockDispatch = $NumPcsInThisStockDispatch + $ShipQty;
				$YPos -=(2 * $line_height);
				// Parameters for addTextWrap are defined in /includes/class.pdf.php
				// 1) X position 2) Y position 3) Width
				// 4) Height 5) Text 6) Alignment 7) Border 8) Fill - True to use SetFillColor
				// and False to set to transparent
				$fill = False;
			
				$pdf->addTextWrap(50,$YPos,70,$FontSize,$myrow['stockid'],'',0,$fill);
				$pdf->Image($_SESSION['part_pics_dir'] . '/'.$myrow['stockid'].'.jpg',135,$Page_Height-$Top_Margin-$YPos+10,45,35);
				$pdf->addTextWrap(180,$YPos,200,$FontSize,$myrow['description'],'',0,$fill);
				$pdf->addTextWrap(355,$YPos,40,$FontSize,locale_number_format($myrow['fromquantity'] - $InTransitQuantityAtFrom,$myrow['decimalplaces']),'right',0,$fill);
				$pdf->addTextWrap(405,$YPos,40,$FontSize,locale_number_format($myrow['quantity'] + $InTransitQuantityAtTo,$myrow['decimalplaces']),'right',0,$fill);
				$pdf->addTextWrap(450,$YPos,40,11,locale_number_format($ShipQty,$myrow['decimalplaces']),'right',0,$fill);
				$pdf->addTextWrap(510,$YPos,40,$FontSize,'_________','right',0,$fill);

				// looking for price info  
				$DefaultPrice = GetPrice($myrow['stockid'],$ToCustomer, $ToBranch, $ShipQty, false);
				if ($myrow['discountcategory'] != "")
				{
					$DiscountLine = ' -> ' . _('Discount Category') . ':' . $myrow['discountcategory'];
				}else{
					$DiscountLine = '';
				}
				if ($DefaultPrice != 0){
					$PriceLine = $ToPriceList . ":" . locale_number_format($DefaultPrice,$ToDecimalPlaces) . " " . $ToCurrency . $DiscountLine;
					$pdf->addTextWrap(180,$YPos - 0.5 * $line_height,200,$FontSize,$PriceLine,'',0,$fill);
				}

				if ($YPos < $Bottom_Margin + $line_height + 200){
					PrintHeader($pdf,$YPos,$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,$Page_Width,$Right_Margin,$Trf_ID,$FromLocation,$ToLocation,$CategoryDescription);
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
														'" . $myrow['stockid'] . "',
														'" . $ShipQty . "',
														'" . $Now . "',
														'" . $FromLocCode  ."',
														'" . $ToLocCode . "')";
					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('Unable to enter Location Transfer record for'). ' '.$myrow['stockid'];
					$resultLocShip = DB_query($sql2, $ErrMsg);
				}
				$EmailText = $EmailText . str_pad($ShipQty, 3, " ") . " x " . $myrow['stockid'] . "\n";

			}
		} /*end while loop  */

		// if we reached the maximum of models allowed per dispatch, we warn the user
		if ($NumModelsInThisStockDispatch == $MaxModelsPerDispatch){
			$ModelsSkipped = 0;
			while ($myrow = DB_fetch_array($result,$db)){
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
						   $Right_Margin,$Trf_ID,$FromLocation,$ToLocation,$CategoryDescription);
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
			$result = $mail->send(array('ricard@kapal-laut.com'));
		}
		if($result){
			$EmailText = $EmailText . date('d/M/Y H:i:s') . " Email Sent " . $FileName . "\n";
		}else{
			$EmailText = $EmailText . date('d/M/Y H:i:s') . " Email FAILED " . $FileName . "\n";
		}
		sleep(2);
	}
	return $EmailText;
}


function PrintHeader(&$pdf,&$YPos,&$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,
					 $Page_Width,$Right_Margin,$Trf_ID,$FromLocation,$ToLocation,$CategoryDescription) {


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

	$pdf->addTextWrap(50,$YPos,100,$FontSize,_('Item Code'), 'left');
	$pdf->addTextWrap(135,$YPos,170,$FontSize,_('Image/Description'), 'left');
	$pdf->addTextWrap(360,$YPos,40,$FontSize,_('From'), 'right');
	$pdf->addTextWrap(405,$YPos,40,$FontSize,_('To'), 'right');
	$pdf->addTextWrap(460,$YPos,40,$FontSize,_('Shipped'), 'right');
	$pdf->addTextWrap(510,$YPos,40,$FontSize,_('Received'), 'right');
	$YPos -= $line_height;
	$pdf->addTextWrap(370,$YPos,40,$FontSize,_('Available'), 'right');
	$pdf->addTextWrap(420,$YPos,40,$FontSize,_('Available'), 'right');

	$FontSize=8;
	$PageNumber++;
} // End of PrintHeader() function

?>
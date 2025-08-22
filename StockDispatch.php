<?php

// StockDispatch.php - Report of parts with overstock at one location that can be transferred
// to another location to cover shortage based on reorder level. Creates loctransfer records
// that can be processed using Bulk Inventory Transfer - Receive.

include('includes/session.php');
include('includes/SQL_CommonFunctions.php');
include('includes/GetPrice.php');
if (isset($_POST['PrintPDF'])) {

	include('includes/PDFStarter.php');
	if (!is_numeric(filter_number_format($_POST['Percent']))) {
		$_POST['Percent'] = 0;
	}

	$pdf->addInfo('Title',__('Stock Dispatch Report'));
	$pdf->addInfo('Subject',__('Parts to dispatch to another location to cover reorder level'));
	$FontSize=9;
	$PageNumber=1;
	$LineHeight=19;
	$Xpos = $Left_Margin+1;

	//template
	if($_POST['template']=='simple') {
		$Template='simple';
	} elseif($_POST['template']=='standard') {
		$Template='standard';
	} elseif($_POST['template']=='full') {
		$Template='full';
	} else {
		$Template='fullprices';
	}
	// Create Transfer Number
	if(!isset($Trf_ID) and $_POST['ReportType'] == 'Batch') {
		$Trf_ID = GetNextTransNo(16);
	}

	// from location
	$ErrMsg = __('Could not retrieve location name from the database');
	$SQLfrom="SELECT locationname FROM `locations` WHERE loccode='" . $_POST['FromLocation'] . "'";
	$Result = DB_query($SQLfrom, $ErrMsg);
	$Row = DB_fetch_row($Result);
	$FromLocation=$Row['0'];

	// to location
	$SQLto="SELECT locationname,
					cashsalecustomer,
					cashsalebranch
			FROM `locations`
			WHERE loccode='" . $_POST['ToLocation'] . "'";
	$Resultto = DB_query($SQLto, $ErrMsg);
	$RowTo = DB_fetch_row($Resultto);
	$ToLocation=$RowTo['0'];
	$ToCustomer=$RowTo['1'];
	$ToBranch=$RowTo['2'];

	if($Template=='fullprices'){
		$SqlPrices="SELECT debtorsmaster.currcode,
						debtorsmaster.salestype,
						currencies.decimalplaces
				FROM debtorsmaster, currencies
				WHERE debtorsmaster.currcode = currencies.currabrev
					AND debtorsmaster.debtorno ='" . $ToCustomer . "'";
		$ResultPrices = DB_query($SqlPrices, $ErrMsg);
		$RowPrices = DB_fetch_row($ResultPrices);
		$ToCurrency=$RowPrices['0'];
		$ToPriceList=$RowPrices['1'];
		$ToDecimalPlaces=$RowPrices['2'];
	}

	// Creates WHERE clause for stock categories. StockCat is defined as an array so can choose
	// more than one category
	if ($_POST['StockCat'] != 'All') {
		$CategorySQL="SELECT categorydescription FROM stockcategory WHERE categoryid='".$_POST['StockCat']."'";
		$CategoryResult = DB_query($CategorySQL);
		$CategoryRow=DB_fetch_array($CategoryResult);
		$CategoryDescription=$CategoryRow['categorydescription'];
		$WhereCategory = " AND stockmaster.categoryid ='" . $_POST['StockCat'] . "' ";
	} else {
		$CategoryDescription=__('All');
		$WhereCategory = " ";
	}

	// If Strategy is "Items needed at TO location with overstock at FROM" we need to control the "needed at TO" part
	// The "overstock at FROM" part is controlled in any case with AND (fromlocstock.quantity - fromlocstock.reorderlevel) > 0
	if ($_POST['Strategy'] == 'All') {
		$WhereCategory = $WhereCategory . " AND locstock.reorderlevel > locstock.quantity ";
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
				ROUND((locstock.reorderlevel - locstock.quantity) *
				   (1 + (" . filter_number_format($_POST['Percent']) . "/100)))
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
			  AND fromlocstock.loccode = '" . $_POST['FromLocation'] . "'
			WHERE locstock.stockid=stockmaster.stockid
			AND locstock.loccode ='" . $_POST['ToLocation'] . "'
			AND (fromlocstock.quantity - fromlocstock.reorderlevel) > 0
			AND stockcategory.stocktype<>'A'
			AND (stockmaster.mbflag='B' OR stockmaster.mbflag='M') " .
			$WhereCategory . " ORDER BY locstock.loccode,locstock.stockid";

	$ErrMsg = __('The Stock Dispatch report could not be retrieved');
	$Result = DB_query($SQL, $ErrMsg);

	if (DB_num_rows($Result) ==0) {
		$Title = __('Stock Dispatch - Problem Report');
		include('includes/header.php');
		echo '<br />';
		prnMsg( __('The stock dispatch did not have any items to list'),'warn');
		echo '<br />
				<a href="' . $RootPath . '/index.php">' . __('Back to the menu') . '</a>';
		include('includes/footer.php');
		exit();
	}

	PrintHeader($pdf,$YPos,$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,
				$Page_Width,$Right_Margin,$Trf_ID,$FromLocation,$ToLocation,$Template,$CategoryDescription);

	$FontSize=8;
	$Now = Date('Y-m-d H-i-s');
	while ($MyRow = DB_fetch_array($Result)){
		// Check if there is any stock in transit already sent from FROM LOCATION
		$InTransitQuantityAtFrom = 0;
		if ($_SESSION['ProhibitNegativeStock']==1){
			$InTransitSQL="SELECT SUM(pendingqty) as intransit
							FROM loctransfers
							WHERE stockid='" . $MyRow['stockid'] . "'
								AND shiploc='".$_POST['FromLocation']."'
								AND pendingqty>0";
			$InTransitResult = DB_query($InTransitSQL);
			$InTransitRow=DB_fetch_array($InTransitResult);
			$InTransitQuantityAtFrom=$InTransitRow['intransit'];
		}
		// The real available stock to ship is the (qty - reorder level - in transit).
		$AvailableShipQtyAtFrom = $MyRow['available'] - $InTransitQuantityAtFrom;

		// Check if TO location is already waiting to receive some stock of this item
		$InTransitQuantityAtTo=0;
		$InTransitSQL="SELECT SUM(pendingqty) as intransit
						FROM loctransfers
						WHERE stockid='" . $MyRow['stockid'] . "'
							AND recloc='".$_POST['ToLocation']."'
							AND pendingqty>0";
		$InTransitResult = DB_query($InTransitSQL);
		$InTransitRow=DB_fetch_array($InTransitResult);
		$InTransitQuantityAtTo=$InTransitRow['intransit'];

		// The real needed stock is reorder level - qty - in transit).
		$NeededQtyAtTo = $MyRow['neededqty'] - $InTransitQuantityAtTo;

		// Decide how many are sent (depends on the strategy)
		if ($_POST['Strategy'] == 'OverFrom') {
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

		if ($ShipQty>0) {
			$YPos -=(2 * $LineHeight);
			// Parameters for addTextWrap are defined in /includes/class.cpdf.php
			// 1) X position 2) Y position 3) Width
			// 4) Height 5) Text 6) Alignment 7) Border 8) Fill - True to use SetFillColor
			// and False to set to transparent
			$Fill = False;

			if($Template=='simple'){
				//for simple template
				$pdf->addTextWrap(50,$YPos,70,$FontSize,$MyRow['stockid'],'',0,$Fill);
				$pdf->addTextWrap(135,$YPos,250,$FontSize,$MyRow['description'],'',0,$Fill);
				$pdf->addTextWrap(380,$YPos,45,$FontSize,locale_number_format($MyRow['fromquantity'], $MyRow['decimalplaces']),'right',0,$Fill);
				$pdf->addTextWrap(425,$YPos,40,$FontSize,locale_number_format($MyRow['quantity'], $MyRow['decimalplaces']),'right',0,$Fill);
				$pdf->addTextWrap(465,$YPos,40,11,locale_number_format($ShipQty, $MyRow['decimalplaces']),'right',0,$Fill);
				$pdf->addTextWrap(510,$YPos,40,$FontSize,'_________','right',0,$Fill);
			} elseif ($Template=='standard') {
				//for standard template
				$pdf->addTextWrap(50,$YPos,70,$FontSize,$MyRow['stockid'],'',0,$Fill);
				$pdf->addTextWrap(135,$YPos,200,$FontSize,$MyRow['description'],'',0,$Fill);
				$pdf->addTextWrap(320,$YPos,40,$FontSize,locale_number_format($MyRow['fromquantity'] - $InTransitQuantityAtFrom,$MyRow['decimalplaces']),'right',0,$Fill);
				$pdf->addTextWrap(390,$YPos,40,$FontSize,locale_number_format($MyRow['quantity'] + $InTransitQuantityAtTo,$MyRow['decimalplaces']),'right',0,$Fill);
				$pdf->addTextWrap(460,$YPos,40,11,locale_number_format($ShipQty,$MyRow['decimalplaces']),'right',0,$Fill);
				$pdf->addTextWrap(510,$YPos,40,$FontSize,'_________','right',0,$Fill);
			} else {
				//for full template
				$pdf->addTextWrap(50,$YPos,70,$FontSize,$MyRow['stockid'],'',0,$Fill);
				$SupportedImgExt = array('png','jpg','jpeg');
                $Glob = (glob($_SESSION['part_pics_dir'] . '/' . $MyRow['stockid'] . '.{' . implode(",", $SupportedImgExt) . '}', GLOB_BRACE));
				$ImageFile = reset($Glob);
				if (file_exists ($ImageFile) ) {
					$pdf->Image($ImageFile,135,$Page_Height-$Top_Margin-$YPos+10,35,35);
				}/*end checked file exist*/
				$pdf->addTextWrap(180,$YPos,200,$FontSize,$MyRow['description'],'',0,$Fill);
				$pdf->addTextWrap(355,$YPos,40,$FontSize,locale_number_format($MyRow['fromquantity'] - $InTransitQuantityAtFrom,$MyRow['decimalplaces']),'right',0,$Fill);
				$pdf->addTextWrap(405,$YPos,40,$FontSize,locale_number_format($MyRow['quantity'] + $InTransitQuantityAtTo,$MyRow['decimalplaces']),'right',0,$Fill);
				$pdf->addTextWrap(450,$YPos,40,11,locale_number_format($ShipQty,$MyRow['decimalplaces']),'right',0,$Fill);
				$pdf->addTextWrap(510,$YPos,40,$FontSize,'_________','right',0,$Fill);
				if($Template=='fullprices'){
					// looking for price info
					$DefaultPrice = GetPrice($MyRow['stockid'],$ToCustomer, $ToBranch, $ShipQty, false);
					if ($MyRow['discountcategory'] != "")
					{
						$DiscountLine = ' -> ' . __('Discount Category') . ':' . $MyRow['discountcategory'];
					}else{
						$DiscountLine = '';
					}
					if ($DefaultPrice != 0){
						$PriceLine = $ToPriceList . ":" . locale_number_format($DefaultPrice,$ToDecimalPlaces) . " " . $ToCurrency . $DiscountLine;
						$pdf->addTextWrap(180,$YPos - 0.5 * $LineHeight,200,$FontSize,$PriceLine,'',0,$Fill);
					}
				}
			}

			if ($YPos < $Bottom_Margin + $LineHeight + 200){
				PrintHeader($pdf,$YPos,$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,$Page_Width,$Right_Margin,$Trf_ID,$FromLocation,$ToLocation,$Template,$CategoryDescription);
			}

			// Create loctransfers records for each record
			$SQL2 = "INSERT INTO loctransfers (reference,
												stockid,
												shipqty,
												shipdate,
												shiploc,
												recloc)
											VALUES ('" . $Trf_ID . "',
												'" . $MyRow['stockid'] . "',
												'" . $ShipQty . "',
												'" . $Now . "',
												'" . $_POST['FromLocation']  ."',
												'" . $_POST['ToLocation'] . "')";
			$ErrMsg = __('CRITICAL ERROR') . '! ' . __('Unable to enter Location Transfer record for'). ' '.$MyRow['stockid'];
			if ($_POST['ReportType'] == 'Batch') {
				$ResultLocShip = DB_query($SQL2, $ErrMsg);
			}
		}
	} /*end while loop  */
	//add prepared by
	$pdf->addTextWrap(50,$YPos-50,100,9,__('Prepared By :'), 'left');
	$pdf->addTextWrap(50,$YPos-70,100,$FontSize,__('Name'), 'left');
	$pdf->addTextWrap(90,$YPos-70,200,$FontSize,':__________________','left',0,$Fill);
	$pdf->addTextWrap(50,$YPos-90,100,$FontSize,__('Date'), 'left');
	$pdf->addTextWrap(90,$YPos-90,200,$FontSize,':__________________','left',0,$Fill);
	$pdf->addTextWrap(50,$YPos-110,100,$FontSize,__('Hour'), 'left');
	$pdf->addTextWrap(90,$YPos-110,200,$FontSize,':__________________','left',0,$Fill);
	$pdf->addTextWrap(50,$YPos-150,100,$FontSize,__('Signature'), 'left');
	$pdf->addTextWrap(90,$YPos-150,200,$FontSize,':__________________','left',0,$Fill);

	//add shipped by
	$pdf->addTextWrap(240,$YPos-50,100,9,__('Shipped By :'), 'left');
	$pdf->addTextWrap(240,$YPos-70,100,$FontSize,__('Name'), 'left');
	$pdf->addTextWrap(280,$YPos-70,200,$FontSize,':__________________','left',0,$Fill);
	$pdf->addTextWrap(240,$YPos-90,100,$FontSize,__('Date'), 'left');
	$pdf->addTextWrap(280,$YPos-90,200,$FontSize,':__________________','left',0,$Fill);
	$pdf->addTextWrap(240,$YPos-110,100,$FontSize,__('Hour'), 'left');
	$pdf->addTextWrap(280,$YPos-110,200,$FontSize,':__________________','left',0,$Fill);
	$pdf->addTextWrap(240,$YPos-150,100,$FontSize,__('Signature'), 'left');
	$pdf->addTextWrap(280,$YPos-150,200,$FontSize,':__________________','left',0,$Fill);

	//add received by
	$pdf->addTextWrap(440,$YPos-50,100,9,__('Received By :'), 'left');
	$pdf->addTextWrap(440,$YPos-70,100,$FontSize,__('Name'), 'left');
	$pdf->addTextWrap(480,$YPos-70,200,$FontSize,':__________________','left',0,$Fill);
	$pdf->addTextWrap(440,$YPos-90,100,$FontSize,__('Date'), 'left');
	$pdf->addTextWrap(480,$YPos-90,200,$FontSize,':__________________','left',0,$Fill);
	$pdf->addTextWrap(440,$YPos-110,100,$FontSize,__('Hour'), 'left');
	$pdf->addTextWrap(480,$YPos-110,200,$FontSize,':__________________','left',0,$Fill);
	$pdf->addTextWrap(440,$YPos-150,100,$FontSize,__('Signature'), 'left');
	$pdf->addTextWrap(480,$YPos-150,200,$FontSize,':__________________','left',0,$Fill);

	if ($YPos < $Bottom_Margin + $LineHeight){
		   PrintHeader($pdf,$YPos,$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,$Page_Width,
					   $Right_Margin,$Trf_ID,$FromLocation,$ToLocation,$Template);
	}
/*Print out the grand totals */

	$pdf->OutputD($_SESSION['DatabaseName'] . '_Stock_Transfer_Dispatch_' . Date('Y-m-d') . '.pdf');
	$pdf->__destruct();

} else { /*The option to print PDF was not hit so display form */

	$Title=__('Stock Dispatch Report');
	$ViewTopic = 'Inventory';
	$BookMark = '';
	include('includes/header.php');
	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' . __('Inventory') . '" alt="" />' . ' ' . __('Inventory Stock Dispatch Report') . '</p>';
	echo '<div class="page_help_text">' . __('Create a transfer batch of overstock from one location to another location that is below reorder level.') . '<br/>'
										. __('Quantity to ship is based on reorder level minus the quantity on hand at the To Location; if there is a') . '<br/>'
										. __('dispatch percentage entered, that needed quantity is inflated by the percentage entered.') . '<br/>'
										. __('Use Bulk Inventory Transfer - Receive to process the batch') . '</div>';

	$SQL = "SELECT defaultlocation FROM www_users WHERE userid='".$_SESSION['UserID']."'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$DefaultLocation = $MyRow['defaultlocation'];
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	$SQL = "SELECT locations.loccode,
			locationname
		FROM locations
		INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canupd=1";
	$ResultStkLocs = DB_query($SQL);
	if (!isset($_POST['FromLocation'])) {
		$_POST['FromLocation']=$DefaultLocation;
	}
	echo '<fieldset>
			<legend>', __('Report Criteria'), '</legend>
		 <field>
			<label for="Percent">' . __('Dispatch Percent') . ':</label>
			<input type ="text" name="Percent" class="number" size="8" value="0" />
		 </field>';
	echo '<field>
			  <label for="FromLocation">' . __('From Stock Location') . ':</label>
			  <select name="FromLocation"> ';
	while ($MyRow=DB_fetch_array($ResultStkLocs)){
		if ($MyRow['loccode'] == $_POST['FromLocation']){
			 echo '<option selected="selected" value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
		} else {
			 echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
		}
	}
	echo '</select>
		</field>';
	DB_data_seek($ResultStkLocs,0);
	if (!isset($_POST['ToLocation'])) {
		$_POST['ToLocation']=$DefaultLocation;
	}
	echo '<field>
			<label for="ToLocation">' . __('To Stock Location') . ':</label>
			<select name="ToLocation"> ';
	while ($MyRow=DB_fetch_array($ResultStkLocs)){
		if ($MyRow['loccode'] == $_POST['ToLocation']){
			 echo '<option selected="selected" value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
		} else {
			 echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
		}
	}
	echo '</select>
		</field>';

	$SQL="SELECT categoryid, categorydescription FROM stockcategory ORDER BY categorydescription";
	$Result1 = DB_query($SQL);
	if (DB_num_rows($Result1)==0){
		echo '</table>';
		prnMsg(__('There are no stock categories currently defined please use the link below to set them up'),'warn');
		echo '<br /><a href="' . $RootPath . '/StockCategories.php">' . __('Define Stock Categories') . '</a>';
		echo '</div>
			  </form>';
		include('includes/footer.php');
		exit();
	}

	echo '<field>
			<label for="StockCat">' . __('In Stock Category') . ':</label>
			<select name="StockCat">';
	if (!isset($_POST['StockCat'])){
		$_POST['StockCat']='All';
	}
	if ($_POST['StockCat']=='All'){
		echo '<option selected="selected" value="All">' . __('All') . '</option>';
	} else {
		echo '<option value="All">' . __('All') . '</option>';
	}
	while ($MyRow1 = DB_fetch_array($Result1)) {
		if ($MyRow1['categoryid']==$_POST['StockCat']){
			echo '<option selected="selected" value="' . $MyRow1['categoryid'] . '">' . $MyRow1['categorydescription'] . '</option>';
		} else {
			echo '<option value="' . $MyRow1['categoryid'] . '">' . $MyRow1['categorydescription'] . '</option>';
		}
	}
	echo '</select>
		</field>';

	echo '<field>
			<label for="Strategy">' . __('Dispatch Strategy:') . ':</label>
			<select name="Strategy">
				<option selected="selected" value="All">' . __('Items needed at TO location with overstock at FROM location') . '</option>
				<option value="OverFrom">' . __('Items with overstock at FROM location') . '</option>
			</select>
		</field>';

	echo '<field>
			<label for="ReportType">' . __('Report Type') . ':</label>
			<select name="ReportType">
				<option selected="selected" value="Batch">' . __('Create Batch') . '</option>
				<option value="Report">' . __('Report Only') . '</option>
			</select>
		</field>';


	echo '<field>
			<label for="template">' . __('Template') . ':</label>
			<select name="template">
				<option selected="selected" value="fullprices">' . __('Full with Prices') . '</option>
				<option value="full">' . __('Full') . '</option>
				<option value="standard">' . __('Standard') . '</option>
				<option value="simple">' . __('Simple') . '</option>
			</select>
		</field>';

	echo '</fieldset>
		 <div class="centre">
			  <input type="submit" name="PrintPDF" value="' . __('Print PDF') . '" />
		 </div>';
	echo '</form>';

	include('includes/footer.php');

} /*end of else not PrintPDF */


function PrintHeader($pdf,&$YPos,&$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,
					 $Page_Width,$Right_Margin,$Trf_ID,$FromLocation,$ToLocation,$Template,$CategoryDescription) {


	/*PDF page header for Stock Dispatch report */
	if ($PageNumber>1){
		$pdf->newPage();
	}
	$LineHeight=12;
	$FontSize=9;
	$YPos= $Page_Height-$Top_Margin;
	$YPos -=(3*$LineHeight);

	$pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize,$_SESSION['CompanyRecord']['coyname']);
	$YPos -=$LineHeight;

	$pdf->addTextWrap($Left_Margin,$YPos,150,$FontSize,__('Stock Dispatch ') . $_POST['ReportType']);
	$pdf->addTextWrap(200,$YPos,30,$FontSize,__('From :'));
	$pdf->addTextWrap(230,$YPos,200,$FontSize,$FromLocation);

	$pdf->addTextWrap($Page_Width-$Right_Margin-150,$YPos,160,$FontSize,__('Printed') . ': ' .
		 Date($_SESSION['DefaultDateFormat']) . '   ' . __('Page') . ' ' . $PageNumber,'left');
	$YPos -= $LineHeight;
	$pdf->addTextWrap($Left_Margin,$YPos,50,$FontSize,__('Transfer No.'));
	$pdf->addTextWrap(95,$YPos,50,$FontSize,$Trf_ID);
	$pdf->setFont('','B');
	$pdf->addTextWrap(200,$YPos,30,$FontSize,__('To :'));
	$pdf->addTextWrap(230,$YPos,200,$FontSize,$ToLocation);
	$pdf->setFont('','');
	$YPos -= $LineHeight;
	$pdf->addTextWrap($Left_Margin,$YPos,50,$FontSize,__('Category'));
	$pdf->addTextWrap(95,$YPos,50,$FontSize,$_POST['StockCat']);
	$pdf->addTextWrap(160,$YPos,150,$FontSize,$CategoryDescription,'left');
	$YPos -= $LineHeight;
	$pdf->addTextWrap($Left_Margin,$YPos,50,$FontSize,__('Over transfer'));
	$pdf->addTextWrap(95,$YPos,50,$FontSize,$_POST['Percent'] . "%");
	if ($_POST['Strategy'] == 'OverFrom') {
		$pdf->addTextWrap(200,$YPos,200,$FontSize,__('Overstock items at '). $FromLocation);
	}else{
		$pdf->addTextWrap(200,$YPos,200,$FontSize,__('Items needed at '). $ToLocation);
	}
	$YPos -=(2*$LineHeight);
	/*set up the headings */
	$Xpos = $Left_Margin+1;

	if($Template=='simple'){
		$pdf->addTextWrap(50,$YPos,100,$FontSize,__('Part Number'), 'left');
		$pdf->addTextWrap(135,$YPos,220,$FontSize,__('Description'), 'left');
		$pdf->addTextWrap(380,$YPos,45,$FontSize,__('QOH-From'), 'right');
		$pdf->addTextWrap(425,$YPos,40,$FontSize,__('QOH-To'), 'right');
		$pdf->addTextWrap(465,$YPos,40,$FontSize,__('Shipped'), 'right');
		$pdf->addTextWrap(510,$YPos,40,$FontSize,__('Received'), 'right');
	}else{
		$pdf->addTextWrap(50,$YPos,100,$FontSize,__('Part Number'), 'left');
		$pdf->addTextWrap(135,$YPos,170,$FontSize,__('Image/Description'), 'left');
		$pdf->addTextWrap(360,$YPos,40,$FontSize,__('From'), 'right');
		$pdf->addTextWrap(405,$YPos,40,$FontSize,__('To'), 'right');
		$pdf->addTextWrap(460,$YPos,40,$FontSize,__('Shipped'), 'right');
		$pdf->addTextWrap(510,$YPos,40,$FontSize,__('Received'), 'right');
		$YPos -= $LineHeight;
		$pdf->addTextWrap(370,$YPos,40,$FontSize,__('Available'), 'right');
		$pdf->addTextWrap(420,$YPos,40,$FontSize,__('Available'), 'right');

	}

	$FontSize=8;
	$PageNumber++;
} // End of PrintHeader() function

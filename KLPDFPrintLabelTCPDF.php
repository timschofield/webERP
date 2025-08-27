<?php

/******************************************************************************************************
 * 
 * KL RICARD: Heavily modified standard file. 
 * 
 ******************************************************************************************************/

require(__DIR__ . '/includes/session.php');

include('includes/KLDefines.php');
include('includes/KLGeneralFunctions.php');
include('includes/UIGeneralFunctions.php');
include('includes/KLUIGeneralFunctions.php');

if (!isset($_POST['LabelID'])){
	$_POST['LabelID'] = 'T570';
}
if (!isset($_POST['Location'])){
	$_POST['Location'] = '';
}
if (!isset($_POST['LabelsPerItem'])){
	$_POST['LabelsPerItem'] = 1;
}
if (!isset($_POST['ChangeToday'])){
	$_POST['ChangeToday'] = "Nothing";
}

if ((isset($_POST['ShowLabels']) OR isset($_POST['SelectAll']))
	AND isset($_POST['StockCategory'])
	AND mb_strlen($_POST['StockCategory']) >= 1){

	$Title = __('Print Labels');
	include('includes/header.php');
	
	if ($_POST['LabelID'] == 'T570'){
		$SQLPrice = "prices.price, ";
		$MainWhere = " INNER JOIN prices 
							ON stockmaster.stockid = prices.stockid
						WHERE prices.typeabbrev = '" . RETAIL_PRICE_LIST . "'
							AND prices.currabrev = '" . CURRENCY_CODE . "'
							AND prices.startdate <= CURRENT_DATE
							AND prices.enddate > CURRENT_DATE
							AND prices.debtorno = ''";
	}elseif ($_POST['LabelID'] == 'CodeSticker'){
		$SQLPrice = "0 AS price, ";
		$MainWhere = "	WHERE stockmaster.stockid = stockmaster.stockid ";
	}
	
	if ($_POST['Location'] != ""){
		$SQLQOH = " (SELECT SUM(quantity)
					FROM locstock
					WHERE locstock.stockid = prices.stockid
						AND locstock.loccode = '" . $_POST['Location'] . "') AS qoh,
					0 AS intransit ";
	}else{
		$SQLQOH = $_POST['LabelsPerItem'] . " AS qoh,
					0 AS intransit ";
	}

	$SQLChange = " ";
	if ($_POST['ChangeToday'] != "Nothing"){
		// if they changed something today, all stock is supposed to be in KANTO
		$SQLQOH = " (SELECT SUM(quantity)
					FROM locstock
					WHERE locstock.stockid = prices.stockid
						AND locstock.loccode = 'KANTO') AS qoh,
					0 AS intransit ";
		
		if ($_POST['ChangeToday'] == "ChangePrice"){
			$SQLChange = " AND EXISTS (SELECT *
								FROM klchangeprice
								WHERE klchangeprice.stockid = prices.stockid
									AND klchangeprice.endprocessdate = CURRENT_DATE)";
		}elseif ($_POST['ChangeToday'] == "ChangeDisc20"){
			$SQLChange = " AND EXISTS (SELECT *
										FROM klmovetodiscount20
										WHERE klmovetodiscount20.stockid = prices.stockid
											AND klmovetodiscount20.endprocessdate = CURRENT_DATE)";
		}elseif ($_POST['ChangeToday'] == "ChangeDisc50"){
			$SQLChange = " AND EXISTS (SELECT *
										FROM klmovetodiscount50
										WHERE klmovetodiscount50.stockid = prices.stockid
											AND klmovetodiscount50.endprocessdate = CURRENT_DATE)";
		}elseif ($_POST['ChangeToday'] == "ChangeDisc80"){
			$SQLChange = " AND EXISTS (SELECT *
										FROM klmovetodiscount80
										WHERE klmovetodiscount80.stockid = prices.stockid
											AND klmovetodiscount80.endprocessdate = CURRENT_DATE)";
		}
	}

	if ($_POST['StockCategory'] == "All"){
		$SQLStockCategory = " AND (stockmaster.categoryid IN ". LIST_STOCK_CATEGORIES_KAPAL_LAUT . "
								OR stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_BLINK . "
								OR stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_OUTLET . "
								OR stockmaster.categoryid IN " . LIST_STOCK_CATEGORIES_GENERAL . ")";
	}else{
		$SQLStockCategory = " AND stockmaster.categoryid = '" . $_POST['StockCategory'] . "' ";
	}

	$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.categoryid,
					stockmaster.discountcategory, " . 
					$SQLPrice . 
					$SQLQOH . "
			FROM stockmaster INNER JOIN	stockcategory
   			     ON stockmaster.categoryid = stockcategory.categoryid " .
			$MainWhere .
				$SQLStockCategory .
				$SQLChange . "
				AND stockmaster.discontinued = 0
			ORDER BY stockmaster.stockid";

	$LabelsResult = DB_query($SQL, '', '', false, false);
	if (DB_error_no() != 0) {
		prnMsg(__('The Price Labels could not be retrieved by the SQL because') . ' - ' . DB_error_msg(), 'error');
		echo '<br /><a href="' . $RootPath . '/index.php">' . __('Back to the menu') . '</a>';
		include('includes/footer.php');
		exit();
	}
	if (DB_num_rows($LabelsResult) == 0){
		prnMsg(__('There were no price labels to print out for the category specified'), 'warn');
		echo '<br /><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . 
			__('Back') . '</a>';
		include('includes/footer.php');
		exit();
	}

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table class="selection">
			<tr>
				<th>' . __('Item Code') . '</th>
				<th>' . __('Category') . '</th>
				<th>' . __('Item Description') . '</th>
				<th>' . __('Price') . '</th>
				<th>' . __('# Labels') . '</th>
				<th>' . __('Print') . ' ?</th>
			</tr>
			<tr>
				<th colspan="6">' . __('Select All Labels') . '<input type="checkbox" name="CheckAll" ';
	if (isset($_POST['CheckAll'])){
		echo 'checked="checked" ';
	}
	echo 'onchange="ReloadForm(SelectAll)" /></th>
		</tr>';

	$i = 0;
	while ($LabelRow = DB_fetch_array($LabelsResult)){

		if ($LabelRow['intransit'] != '') {
			$Intransit = $LabelRow['intransit'];
		} else {
			$Intransit = 0;
		}
		$LabelsToPrint = $LabelRow['qoh'] - $Intransit;
		if($LabelsToPrint < 0){
			$LabelsToPrint = 0;
		}
		if ($LabelsToPrint > 0){
			echo '<tr>
					<td>' . $LabelRow['stockid'] . '</td>
					<td>' . $LabelRow['categoryid'] . '</td>
					<td>' . $LabelRow['description'] . '</td>
					<td class="number">' . locale_number_format($LabelRow['price'], 0) . '</td>
					<td class="number">' . locale_number_format($LabelsToPrint, 0) . '</td>
					<td>';
			if (isset($_POST['SelectAll']) AND isset($_POST['CheckAll'])) {
				echo '<input type="checkbox" checked="checked" name="PrintLabel' . $i . '" />';
			} else {
				echo '<input type="checkbox" name="PrintLabel' . $i . '" />';
			}
			echo '</td>
				</tr>';
			echo '<input type="hidden" name="StockID' . $i . '" value="' . $LabelRow['stockid'] . '" />
				<input type="hidden" name="Category' . $i . '" value="' . $LabelRow['categoryid'] . '" />
				<input type="hidden" name="Description' . $i . '" value="' . $LabelRow['description'] . '" />
				<input type="hidden" name="Barcode' . $i . '" value="' . $LabelRow['stockid'] . '" />
				<input type="hidden" name="DiscountCategory' . $i . '" value="' . $LabelRow['discountcategory'] . '" />
				<input type="hidden" name="LabelsToPrint' . $i . '" value="' . $LabelsToPrint . '" />
				<input type="hidden" name="Price' . $i . '" value="' . $LabelRow['price'] . '" />';
			$i++;
		}
	}
	echo '</table>
		<input type="hidden" name="NoOfLabels" value="' . $i . '" />
		<input type="hidden" name="LabelID" value="' . $_POST['LabelID'] . '" />
		<input type="hidden" name="StockCategory" value="' . $_POST['StockCategory'] . '" />
		<input type="hidden" name="LabelsPerItem" value="' . $_POST['LabelsPerItem'] . '" />
		<input type="hidden" name="Location" value="' . $_POST['Location'] . '" />
		<input type="hidden" name="ChangeToday" value="' . $_POST['ChangeToday'] . '" />
		<br />
		<div class="centre">

			<input type="submit" name="PrintLabels" value="' . __('Print Labels') . '" />
		</div>
		</form>';
	include('includes/footer.php');
	exit();
}

$LabelsToBePrinted = false;
if (isset($_POST['PrintLabels']) AND isset($_POST['NoOfLabels']) AND $_POST['NoOfLabels'] > 0){

	for ($i = 0; $i < $_POST['NoOfLabels']; $i++){
		if (isset($_POST['PrintLabel' . $i]) AND ($_POST['LabelsToPrint' . $i] > 0)){
			$LabelsToBePrinted = true;
		}
	}
	if (!$LabelsToBePrinted){
		prnMsg(__('There are no labels selected to print'), 'info');
	}
}
if (isset($_POST['PrintLabels']) AND $LabelsToBePrinted) {

	// set the variables depending on the label type
	if ($_POST['LabelID'] == 'T570'){
		// Pricetags T570 general
		$PageLayout = array(90.0, 10.0);
	
		// define price information
		$PriceXPosition = 3;
		$PriceYPosition = 5.7;
		$PriceFont = 'helvetica';
		$PriceFontStyle = 'B';
		$PriceAlignment = 'C';
		$PriceFontSize = 7.5;
		$PriceWidth = 30;
		$PreviousPriceYPosition = 1.0;
		
		// define barcode style for T570
		$BarcodeXPosition = 30.5;
		$BarcodeYPosition = 0.4;
		$BarcodeLenght = 30;
		$BarcodeWidth = 9.5;
		$ResolutionDPI = 203; // uses imperial system so 200 are really 203. LOL
		$XResolution = 25.4 / $ResolutionDPI; // 25.4mm per inch / resolution points per inch
		$CodeFontSize = 7;
	}elseif ($_POST['LabelID'] == 'CodeSticker'){
		// Code Stickers for stock bags only
		$PageLayout = array(50.0, 25.0);
		
		// define barcode style for Code Stickers
		$BarcodeXPosition = 1;
		$BarcodeYPosition = 8;
		$BarcodeLenght = 48;
		$BarcodeWidth = 16;
		$ResolutionDPI = 203; // uses imperial system so 200 are really 203. LOL
		$XResolution = 25.4 / $ResolutionDPI * 2; // 25.4mm per inch / resolution points per inch
		$CodeFontSize = 12;
	}else{
		// not coded yet
		return;
	}

	if ($_POST['LabelID'] == 'T570'){
		$CoreFileName = "Pricetags";
	}else{
		$CoreFileName = "CodeStickers";
	}
	if ($_POST['Location'] != "None"){
		$CoreFileName = $CoreFileName . "-QOH-" . $_POST['Location'];
	}
	if ($_POST['ChangeToday'] != "Nothing"){
		$CoreFileName = $CoreFileName . "-" . $_POST['ChangeToday'];
	}
	if ($_POST['StockCategory'] != "All"){
		$CoreFileName = $CoreFileName . "-" . $_POST['StockCategory'];
	}
	
	$FileName = $CoreFileName . '-' . Date('Y-m-d-H-i-s') . '.pdf';

	$BarcodeStyle = array(
			'position' => '',
			'align' => 'C',
			'stretch' => false,
			'fitwidth' => false,
			'cellfitalign' => '',
			'border' => false,
			'hpadding' => 'auto',
			'vpadding' => 'auto',
			'fgcolor' => array(0, 0, 0),
			'bgcolor' => false, //array(255,255,255),
			'text' => true,
			'font' => 'helvetica',
			'fontsize' => $CodeFontSize,
			'stretchtext' => 0);

	$Pdf = new TCPDF('L', 'mm', $PageLayout, true, 'UTF-8', false);
	
	// set PDF document information
	$Pdf->SetCreator('webERP');
	$Pdf->SetAuthor('webERP');
	$Pdf->SetTitle($CoreFileName);
	$Pdf->SetSubject($CoreFileName);
	$Pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
	$Pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
	$Pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
	$Pdf->SetMargins(0, 0, 0);
	$Pdf->SetHeaderMargin(0);
	$Pdf->SetFooterMargin(0);
	$Pdf->setPrintHeader(false);
	$Pdf->setPrintFooter(false);
	$Pdf->SetAutoPageBreak(true, 0);
	$Pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

	// now print the labels
	$LabelsPrinted = 0;
	for ($i = 0; $i < $_POST['NoOfLabels']; $i++){
		if (isset($_POST['PrintLabel' . $i])){
			// Get the data for each field
			$StockID = $_POST['StockID' . $i];

			// define Logo information
			if (ItemInList($_POST['Category' . $i], LIST_STOCK_CATEGORIES_BLINK_INCLUDING_SETUP_ALL_DISCOUNT)){
				$LogoFile = 'companies/' . $_SESSION['DatabaseName'] . '/LogoLabelBLINK.jpg';
			}
			elseif (ItemInList($_POST['Category' . $i], LIST_STOCK_CATEGORIES_KAPAL_LAUT_INCLUDING_SETUP_ALL_DISCOUNT)){
				$LogoFile = 'companies/' . $_SESSION['DatabaseName'] . '/LogoLabelKL.jpg';
			}
			else{
				$LogoFile = '';
			}

			if ($_POST['LabelID'] == 'T570'){
				if (ItemInList($_POST['Category' . $i], LIST_STOCK_CATEGORIES_BLINK_INCLUDING_SETUP_ALL_DISCOUNT)){
					$LogoXPosition = 14.0;
					$LogoYPosition = 1.0;
					$LogoHeight = 4.5;
				}
				else{
					$LogoXPosition = 12.0;
					$LogoYPosition = 1.0;
					$LogoHeight = 4.0;
				}
			}
			elseif ($_POST['LabelID'] == 'CodeSticker'){
				if (ItemInList($_POST['Category' . $i], LIST_STOCK_CATEGORIES_BLINK_INCLUDING_SETUP_ALL_DISCOUNT)){
					$LogoXPosition = 18.0;
					$LogoYPosition = 1.0;
					$LogoHeight = 6.0;
				}
				else{
					$LogoXPosition = 14.0;
					$LogoYPosition = 1.0;
					$LogoHeight = 6.0;
				}
			}
			else{
				//not code yet
				return;
			}

			// define prices for discounted items
			if (ItemInList($_POST['Category' . $i], LIST_STOCK_CATEGORIES_OUTLET)){
				$ResultDiscount = DB_query("SELECT MAX(discountrate) AS discount
								FROM discountmatrix
							WHERE salestype = '" . RETAIL_PRICE_LIST . "'
							AND discountcategory = '" . $_POST['DiscountCategory'. $i] . "'
							AND quantitybreak <= '1'");
				$MyRow = DB_fetch_row($ResultDiscount);
				if ($MyRow[0] != 0){ 
					// there's a discount!
					$PercentageDiscount = $MyRow[0];
					$DiscountedPrice = "NOW: " . locale_number_format($_POST['Price' . $i] * (1 - ($PercentageDiscount)), 0) . 
						' ' . CURRENCY_CODE;
					$Price = "WAS: " . locale_number_format($_POST['Price' . $i], 0) . ' ' . CURRENCY_CODE;
				}else{
					// no discount, price discounted by fixed price
					$PercentageDiscount = 0;
					$DiscountedPrice = "ONLY: " . locale_number_format($_POST['Price' . $i], 0) . ' ' . CURRENCY_CODE;
					$Price = "";
				}
			}else{
				// define prices for not discounted items
				$Price = locale_number_format($_POST['Price' . $i], 0) . ' ' . CURRENCY_CODE;
			}

			for ($LabelNumber = 0; $LabelNumber < $_POST['LabelsToPrint' . $i]; $LabelNumber++){
				
				$Pdf->AddPage();

				// print depending on each type of label
				if ($_POST['LabelID'] == 'T570'){
					if (ItemInList($_POST['Category' . $i], LIST_STOCK_CATEGORIES_OUTLET)){
						// print the previous price 
						$Pdf->SetXY($PriceXPosition, $PreviousPriceYPosition);
						$Pdf->SetFont($PriceFont, $PriceFontStyle, $PriceFontSize);
						$Pdf->Cell($PriceWidth, 0, $Price, 0, 0, $PriceAlignment);
						// print the discounted price 
						$Pdf->SetXY($PriceXPosition, $PriceYPosition);
						$Pdf->SetFont($PriceFont, $PriceFontStyle, $PriceFontSize);
						$Pdf->Cell($PriceWidth, 0, $DiscountedPrice, 0, 0, $PriceAlignment);
					}else{
						//Print the logo
						$Pdf->Image($LogoFile, $LogoXPosition, $LogoYPosition, 0, $LogoHeight, 'JPG', '', '', 
							true, 203, '', false, false, 0, false, false, false);
						// print the price
						$Pdf->SetXY($PriceXPosition, $PriceYPosition);
						$Pdf->SetFont($PriceFont, $PriceFontStyle, $PriceFontSize);
						$Pdf->Cell($PriceWidth, 0, $Price, 0, 0, $PriceAlignment);
					}
					// print the barcode
					$Pdf->write1DBarcode($StockID, 'C128', $BarcodeXPosition, $BarcodeYPosition, 
						$BarcodeLenght, $BarcodeWidth, $XResolution, $BarcodeStyle, 'N');
				}elseif ($_POST['LabelID'] == 'CodeSticker'){
					//Print the logo
					$Pdf->Image($LogoFile, $LogoXPosition, $LogoYPosition, 0, $LogoHeight, 'JPG', '', '', 
						true, 203, '', false, false, 0, false, false, false);
					// print the barcode
					$Pdf->write1DBarcode($StockID, 'C128', $BarcodeXPosition, $BarcodeYPosition, 
						$BarcodeLenght, $BarcodeWidth, $XResolution, $BarcodeStyle, 'N');
				}else{
					//not code yet
					return;
				}

				$LabelsPrinted++;
			}
		} //this label is set to print
	} //loop through labels selected to print

	$Pdf->Output($FileName, 'D');
	$Pdf->__destruct();

} else { /*The option to print PDF was not hit */

	$Title = __('KL Print Labels');
	include('includes/header.php');

	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . 
		__('Price Labels') . '" alt="" />
         ' . ' ' . __('Print Price Labels') . '</p>';

	if (!function_exists('gd_info')) {
		prnMsg(__('The GD module for PHP is required to print barcode labels. Your PHP installation is not capable currently. 
			You will most likely experience problems with this script until the GD module is enabled.'), 'error');
		include('includes/footer.php');
		exit();
	}

	if (!isset($_POST['StockCategory'])) {
		/*if $StockCategory is not set then show a form to allow input	*/
		echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">
				<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

		echo '<fieldset>
				<legend>' . __('Label Selection') . '</legend>';
		echo FieldToSelectFromTwoOptions('T570', 'Pricetags T570', 
										'CodeSticker', 'Code Stickers',
										'LabelID', $_POST['LabelID'], __('Label Type'), '', '', 1, true, false);
		echo FieldToSelectOneStockCategory('StockCategory', 
										(isset($_POST['StockCategory']) ? $_POST['StockCategory'] : ''), 
										__('For Stock Category'), '', '', true, 2, true, false);
		echo FieldToSelectOneNumber('LabelsPerItem', $_POST['LabelsPerItem'], 3, 4, __('Fixed number of labels'), 
									'', '', 3, false, false);
		$LabelLocationField = '<b>' . __('OR') . ' </b> ' . 'QOH at Location';
		echo FieldToSelectFromTwoOptions
							('', ' ',
							'KANTO', '000-Kantor',
							'Location', $_POST['Location'], $LabelLocationField, '', '',  4, false, false);
		$LabelItemsThatField = '<b>' . __('OR') . ' </b> ' . ' items that';
		echo FieldToSelectFromFiveOptions('Nothing', ' ',
										'ChangePrice', 'Changed price today', 
										'ChangeDisc20', 'Changed to Discount 20% today',
										'ChangeDisc50', 'Changed to Discount 50% today',
										'ChangeDisc80', 'Changed to Discount 80% today',
										'ChangeToday', $_POST['ChangeToday'],  $LabelItemsThatField, '', '', 5, true, false);

		echo '</fieldset>';

		echo OneButtonCenteredForm('ShowLabels', __('Show Labels'));

		echo '</form>';
	}
	include('includes/footer.php');

} /*end of else not PrintPDF */

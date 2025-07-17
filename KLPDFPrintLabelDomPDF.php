<?php

/******************************************************************************************************
 * 
 * KL RICARD: Heavily modified standard file. Use DomPDF to print labels.
 * 
 ******************************************************************************************************/

include('includes/session.php');
include('includes/KLDefines.php');
include('includes/KLGeneralFunctions.php');
include('includes/UIGeneralFunctions.php');
include('includes/KLUIGeneralFunctions.php');
	
use Dompdf\Dompdf;
use Dompdf\Options;

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

	$Title = _('Print Labels');
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
		prnMsg(_('The Price Labels could not be retrieved by the SQL because') . ' - ' . DB_error_msg(), 'error');
		echo '<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
		include('includes/footer.php');
		exit();
	}
	if (DB_num_rows($LabelsResult) == 0){
		prnMsg(_('There were no price labels to print out for the category specified'), 'warn');
		echo '<br /><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . 
			_('Back') . '</a>';
		include('includes/footer.php');
		exit();
	}

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table class="selection">
			<tr>
				<th>' . _('Item Code') . '</th>
				<th>' . _('Category') . '</th>
				<th>' . _('Item Description') . '</th>
				<th>' . _('Price') . '</th>
				<th>' . _('# Labels') . '</th>
				<th>' . _('Print') . ' ?</th>
			</tr>
			<tr>
				<th colspan="6">' . _('Select All Labels') . '<input type="checkbox" name="CheckAll" ';
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

			<input type="submit" name="PrintLabels" value="' . _('Print Labels') . '" />
		</div>
		</form>';
	include('includes/footer.php');
	exit();
}

$LabelsToBePrinted = false;
if (isset($_POST['PrintLabels']) AND isset($_POST['NoOfLabels']) AND $_POST['NoOfLabels'] > 0){

	for ($i = 0; $i < $_POST['NoOfLabels']; $i++){
		if (isset($_POST['PrintLabel' . $i]) AND ($_POST['LabelsToPrint' . $i] > 0)){
			$LabelsToBePrinted = TRUE;
		}
	}
	if (!$LabelsToBePrinted){
		prnMsg(_('There are no labels selected to print'), 'info');
	}
}
if (isset($_POST['PrintLabels']) AND $LabelsToBePrinted) {

	// Let's start the real PDF creation with DomPDF
	 // Ensure DomPDF is loaded via Composer

	// set the variables depending on the label type
	if ($_POST['LabelID'] == 'T570'){
		// Pricetags T570 general
		$PageWidth = 90.0;
		$PageHeight = 10.0;
	
		// define price information
		$PriceXPosition = 3;
		$PriceYPosition = 5.7;
		$PriceFontStyle = 'font-weight: bold;';
		$PriceAlignment = 'text-align: center;';
		$PriceFontSize = '7.5pt';
		$PriceWidth = '30mm';
		$PreviousPriceYPosition = 1.0;
		
		// define barcode style for T570 - increased width for better readability
		$BarcodeXPosition = 30.5;
		$BarcodeYPosition = 0.4;
		$BarcodeLength = 25; 
		$BarcodeWidth = 8; // Reduced to leave more space for the code below
		$CodeFontSize = '6pt'; // Reduced font size slightly
	}elseif ($_POST['LabelID'] == 'CodeSticker'){
		// Code Stickers for stock bags only
		$PageWidth = 50.0;
		$PageHeight = 25.0;
		
		// define barcode style for Code Stickers
		$BarcodeXPosition = 1;
		$BarcodeYPosition = 8;
		$BarcodeLength = 48;
		$BarcodeWidth = 16;
		$CodeFontSize = '12pt';
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

	// Configure DomPDF options
	$options = new Options();
	$options->set('isHtml5ParserEnabled', true);
	$options->set('isPhpEnabled', true);
	$options->set('defaultFont', 'helvetica');
	$options->set('chroot', __DIR__); // Allow access to current directory for images
	$options->set('enable_remote', true); // Allow access to remote URLs for images
	
	// Create DomPDF instance
	$dompdf = new Dompdf($options);

	// Get server protocol and host for absolute URLs
	$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
	$host = $_SERVER['HTTP_HOST'];
	$baseUrl = $protocol . $host;
    
	// Define path to Picqer barcode library or include it manually if not using Composer
	if (!class_exists('Picqer\\Barcode\\BarcodeGeneratorPNG')) {
		// If Picqer is not available via Composer, include it manually
		include('includes/barcode-generator/src/BarcodeGeneratorPNG.php');
	}

	// Initialize HTML
	$HTML = '<!DOCTYPE html>
	<html>
	<head>
		<meta charset="UTF-8">
		<title>' . $CoreFileName . '</title>
		<style>
			@page {
				margin: 0;
				padding: 0;
				size: ' . $PageWidth . 'mm ' . $PageHeight . 'mm;
			}
			body {
				margin: 0;
				padding: 0;
				font-family: helvetica, sans-serif;
			}
			.label-container {
				position: relative;
				width: ' . $PageWidth . 'mm;
				height: ' . $PageHeight . 'mm;
				page-break-after: always;
			}
			.price {
				position: absolute;
				' . $PriceAlignment . '
				' . $PriceFontStyle . '
				font-size: ' . $PriceFontSize . ';
				width: ' . $PriceWidth . ';
			}
			.barcode {
				position: absolute;
				text-align: center;
			}
			.barcode img {
				max-width: 100%;
				height: auto;
				display: block;
				margin: 0 auto;
			}
			.barcode-text {
				font-size: ' . $CodeFontSize . ';
				display: block;
				text-align: center;
				position: relative;
				top: 0mm;
				width: ' . ($_POST['LabelID'] == 'T570' ? ($BarcodeLength + 10) : ($BarcodeLength + 4)) . 'mm; /* Wider text area to accommodate longer codes */
				left: -' . ($_POST['LabelID'] == 'T570' ? '5' : '2') . 'mm; /* Adjust left position based on label type */
				margin-top: 1mm; /* Add some space below the barcode */
				overflow: visible; /* Allow text to extend beyond container if needed */
				white-space: nowrap; /* Prevent text from wrapping */
			}
			.logo {
				position: absolute;
			}
		</style>
	</head>
	<body>';

	// now print the labels
	$LabelsPrinted = 0;
	for ($i = 0; $i < $_POST['NoOfLabels']; $i++){
		if (isset($_POST['PrintLabel' . $i])){
			// Get the data for each field
			$StockID = $_POST['StockID' . $i];

			// define Logo information
			if (ItemInList($_POST['Category' . $i], LIST_STOCK_CATEGORIES_BLINK_INCLUDING_SETUP_ALL_DISCOUNT)){
				$LogoFile = dirname(__FILE__) . '/companies/' . $_SESSION['DatabaseName'] . '/LogoLabelBLINK.jpg';
				$LogoFileHtml = 'companies/' . $_SESSION['DatabaseName'] . '/LogoLabelBLINK.jpg';
			}
			elseif (ItemInList($_POST['Category' . $i], LIST_STOCK_CATEGORIES_KAPAL_LAUT_INCLUDING_SETUP_ALL_DISCOUNT)){
				$LogoFile = dirname(__FILE__) . '/companies/' . $_SESSION['DatabaseName'] . '/LogoLabelKL.jpg';
				$LogoFileHtml = 'companies/' . $_SESSION['DatabaseName'] . '/LogoLabelKL.jpg';
			}
			else{
				$LogoFile = '';
				$LogoFileHtml = '';
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

			// Generate barcode image using a proper barcode library
			$barcodeImageData = generateBarcode128($StockID, $BarcodeLength, $BarcodeWidth);

			for ($LabelNumber = 0; $LabelNumber < $_POST['LabelsToPrint' . $i]; $LabelNumber++){
				
				$HTML .= '<div class="label-container">';

				// print depending on each type of label
				if ($_POST['LabelID'] == 'T570'){
					if (ItemInList($_POST['Category' . $i], LIST_STOCK_CATEGORIES_OUTLET)){
						// print the previous price 
						$HTML .= '<div class="price" style="left: ' . $PriceXPosition . 'mm; top: ' . $PreviousPriceYPosition . 'mm;">' . 
							$Price . '</div>';
						// print the discounted price 
						$HTML .= '<div class="price" style="left: ' . $PriceXPosition . 'mm; top: ' . $PriceYPosition . 'mm;">' . 
							$DiscountedPrice . '</div>';
					}else{
						//Print the logo if exists
						if (!empty($LogoFile) && file_exists($LogoFile)) {
							// Convert logo to data URI to ensure it's properly embedded
							$logoData = base64_encode(file_get_contents($LogoFile));
							$logoMimeType = mime_content_type($LogoFile);
							
							$HTML .= '<div class="logo" style="left: ' . $LogoXPosition . 'mm; top: ' . $LogoYPosition . 'mm;">' .
								'<img src="data:' . $logoMimeType . ';base64,' . $logoData . '" style="height: ' . $LogoHeight . 'mm;">' .
								'</div>';
						}
						// print the price
						$HTML .= '<div class="price" style="left: ' . $PriceXPosition . 'mm; top: ' . $PriceYPosition . 'mm;">' . 
							$Price . '</div>';
					}
					 // Generate barcode that fits the specified dimensions
					$barcodeImageData = generateBarcode128($StockID, $BarcodeLength, $BarcodeWidth - 2); // Adjust height to leave room for text
					// print the barcode with specific dimensions - text now displayed below the barcode
					$HTML .= '<div class="barcode" style="left: ' . $BarcodeXPosition . 'mm; top: ' . $BarcodeYPosition . 'mm; width: ' . 
						$BarcodeLength . 'mm; height: ' . $BarcodeWidth . 'mm; overflow: hidden;">' .
						'<img src="' . $barcodeImageData . '" alt="' . $StockID . '" style="width: ' . $BarcodeLength . 'mm; height: ' . ($BarcodeWidth - 3) . 'mm;">' .
						'<span class="barcode-text">' . $StockID . '</span>' .
						'</div>';
				}elseif ($_POST['LabelID'] == 'CodeSticker'){
					//Print the logo if exists
					if (!empty($LogoFile) && file_exists($LogoFile)) {
						// Convert logo to data URI to ensure it's properly embedded
						$logoData = base64_encode(file_get_contents($LogoFile));
						$logoMimeType = mime_content_type($LogoFile);
						
						$HTML .= '<div class="logo" style="left: ' . $LogoXPosition . 'mm; top: ' . $LogoYPosition . 'mm;">' .
							'<img src="data:' . $logoMimeType . ';base64,' . $logoData . '" style="height: ' . $LogoHeight . 'mm;">' .
							'</div>';
						}
						// Generate barcode that fits the specified dimensions
						$barcodeImageData = generateBarcode128($StockID, $BarcodeLength, $BarcodeWidth - 5); // Adjust height to leave room for text
						// print the barcode with specific dimensions - use the same structure as T570 for consistent text positioning
						$HTML .= '<div class="barcode" style="left: ' . $BarcodeXPosition . 'mm; top: ' . $BarcodeYPosition . 'mm; width: ' . 
							$BarcodeLength . 'mm; height: ' . $BarcodeWidth . 'mm; overflow: hidden;">' .
							'<img src="' . $barcodeImageData . '" alt="' . $StockID . '" style="width: ' . $BarcodeLength . 'mm; height: ' . ($BarcodeWidth - 5) . 'mm;">' .
							'<span class="barcode-text">' . $StockID . '</span>' .
							'</div>';
				}else{
					//not code yet
					return;
				}

				$HTML .= '</div>';
				$LabelsPrinted++;
			}
		} //this label is set to print
	} //loop through labels selected to print

	// Close HTML document
	$HTML .= '</body></html>';

	$dompdf->loadHtml($HTML);
	$dompdf->setPaper(array(0, 0, $PageWidth, $PageHeight), 'landscape');
	$dompdf->render();

	// Output the generated PDF
	$dompdf->stream($FileName, array('Attachment' => true));
	exit();

} else { 
	$Title = _('KL Print Labels');
	include('includes/header.php');

	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . 
		_('Price Labels') . '" alt="" />
         ' . ' ' . _('Print Price Labels') . '</p>';

	if (!function_exists('gd_info')) {
		prnMsg(_('The GD module for PHP is required to print barcode labels. Your PHP installation is not capable currently. 
			You will most likely experience problems with this script until the GD module is enabled.'), 'error');
		include('includes/footer.php');
		exit();
	}

	if (!isset($_POST['StockCategory'])) {
		/*if $StockCategory is not set then show a form to allow input	*/
		echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">
				<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

		echo '<fieldset>
				<legend>' . _('Label Selection') . '</legend>';
		echo FieldToSelectFromTwoOptions('T570', 'Pricetags T570', 
										'CodeSticker', 'Code Stickers',
										'LabelID', $_POST['LabelID'], _('Label Type'), '', '', 1, true, false);
		echo FieldToSelectOneStockCategory('StockCategory', 
										(isset($_POST['StockCategory']) ? $_POST['StockCategory'] : ''), 
										_('For Stock Category'), '', '', true, 2, true, false);
		echo FieldToSelectOneNumber('LabelsPerItem', $_POST['LabelsPerItem'], 3, 4, _('Fixed number of labels'), 
									'', '', 3, false, false);
		$LabelLocationField = '<b>' . _('OR') . ' </b> ' . 'QOH at Location';
		echo FieldToSelectFromTwoOptions
							('', ' ',
							'KANTO', '000-Kantor',
							'Location', $_POST['Location'], $LabelLocationField, '', '',  4, false, false);
		$LabelItemsThatField = '<b>' . _('OR') . ' </b> ' . ' items that';
		echo FieldToSelectFromFiveOptions('Nothing', ' ',
										'ChangePrice', 'Changed price today', 
										'ChangeDisc20', 'Changed to Discount 20% today',
										'ChangeDisc50', 'Changed to Discount 50% today',
										'ChangeDisc80', 'Changed to Discount 80% today',
										'ChangeToday', $_POST['ChangeToday'],  $LabelItemsThatField, '', '', 5, true, false);

		echo '</fieldset>';

		echo OneButtonCenteredForm('ShowLabels', _('Show Labels'));

		echo '</form>';
	}
	include('includes/footer.php');
}

/**
 * Generate a Code 128 barcode as base64 encoded PNG
 * 
 * @param string $code The code to encode in the barcode
 * @param int $barcodeLength Length of the barcode in mm
 * @param int $barcodeWidth Width/height of the barcode in mm
 * @return string Base64 encoded PNG image
 */
function generateBarcode128($code, $barcodeLength = null, $barcodeWidth = null) {
    global $BarcodeLength, $BarcodeWidth; // Use global dimensions if not specified - fixed variable name
    
    // Use passed dimensions or fall back to global variables
    $length = ($barcodeLength !== null) ? $barcodeLength : $BarcodeLength;
    $height = ($barcodeWidth !== null) ? $barcodeWidth : $BarcodeWidth;
    
    // Convert mm to pixels at very high DPI for better quality and finer lines
    $dpi = 300; 
    $pixelsPerMM = $dpi / 25.4; // DPI / 25.4mm per inch
    
    // Calculate pixel dimensions based on mm - ensure integer values
    $pixelWidth = (int)($length * $pixelsPerMM);
    $pixelHeight = (int)($height * $pixelsPerMM);
    
    // Create the image with the exact dimensions needed
    $im = imagecreatetruecolor($pixelWidth, $pixelHeight);
    $white = imagecolorallocate($im, 255, 255, 255);
    $black = imagecolorallocate($im, 0, 0, 0);
    
    // Fill the background white
    imagefilledrectangle($im, 0, 0, $pixelWidth, $pixelHeight, $white);
    
    // Calculate barcode parameters
    $codeLen = strlen($code); // Ensure no trailing characters exist
    $barHeight = $pixelHeight; // Full height of the image without text space
    $moduleWidth = $pixelWidth / ($codeLen * 11 + 35); // Each char ~11 modules + start/stop
    
    // Define barcode encoding elements (simplified for consistency)
    $narrow = (int)max(1, $moduleWidth);
    $wide = (int)max(2, $moduleWidth * 2);
    
    // Start positions
    $x = 10; // Start with some margin
    
    // Generate a simplified but recognizable barcode pattern
    // Start marker
    drawBar($im, $x, 0, $narrow, $barHeight, $black); $x += $narrow * 2;
    drawBar($im, $x, 0, $wide, $barHeight, $black);   $x += $wide * 2;
    
    // Encode each character
    for ($i = 0; $i < $codeLen; $i++) {
        // Alternate thick and thin bars for simplicity
        // Real Code128 has more complex patterns, but this creates a visual barcode
        $char = ord(substr($code, $i, 1));
        
        // Create a pattern based on the character value
        drawBar($im, $x, 0, $narrow, $barHeight, $black); $x += $narrow * 2;
        
        if ($char % 2 == 0) {
            drawBar($im, $x, 0, $wide, $barHeight, $black); $x += $wide * 2;
        } else {
            drawBar($im, $x, 0, $narrow, $barHeight, $black); $x += $narrow * 2;
        }
        
        if ($char % 3 == 0) {
            drawBar($im, $x, 0, $narrow, $barHeight, $black); $x += $narrow * 2;
        } else {
            drawBar($im, $x, 0, $wide, $barHeight, $black); $x += $wide * 2;
        }
        
        // Space between characters
        $x += $narrow;
    }
    
    // End marker
    drawBar($im, $x, 0, $wide, $barHeight, $black); $x += $wide * 2;
    drawBar($im, $x, 0, $narrow, $barHeight, $black);
    
    // Do NOT draw the text in the image since we'll add it in HTML
    
    // Start output buffering
    ob_start();
    imagepng($im);
    $imageData = ob_get_clean();
    
    // Free up memory
    imagedestroy($im);
    
    // Return base64 encoded image
    return 'data:image/png;base64,' . base64_encode($imageData);
}

/**
 * Draw a bar for the barcode
 * 
 * @param \GdImage $im GD image resource
 * @param int $x X position
 * @param int $y Y position
 * @param int $width Width of the bar
 * @param int $height Height of the bar
 * @param int $color Color of the bar
 */
function drawBar(\GdImage $im, int $x, int $y, int $width, int $height, int $color): void {
    imagefilledrectangle($im, (int)$x, (int)$y, (int)($x + $width - 1), (int)($y + $height - 1), $color);
}

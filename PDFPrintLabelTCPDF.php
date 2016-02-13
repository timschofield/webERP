<?php
/* $Id: PDFPriceLabelsbyTCPDF.php  */

include('includes/session.inc');
include('includes/KLDefines.php');
include('includes/KLGeneralFunctions.php');

if ((isset($_POST['ShowLabels']) OR isset($_POST['SelectAll']))
	AND isset($_POST['StockCategory'])
	AND mb_strlen($_POST['StockCategory'])>=1){

	$Title = _('Print Labels');
	include('includes/header.inc');

	$SQL = "SELECT prices.stockid,
					stockmaster.description,
					prices.price,
					currencies.decimalplaces
			FROM stockmaster INNER JOIN	stockcategory
   			     ON stockmaster.categoryid=stockcategory.categoryid
			INNER JOIN prices
				ON stockmaster.stockid=prices.stockid
			INNER JOIN currencies
				ON prices.currabrev=currencies.currabrev
			WHERE stockmaster.categoryid = '" . $_POST['StockCategory'] . "'
				AND prices.typeabbrev = '" . RETAIL_PRICE_LIST . "'
				AND prices.currabrev = '". CURRENCY_CODE ."'
				AND prices.startdate<='" . FormatDateForSQL($_POST['EffectiveDate']) . "'
				AND (prices.enddate='0000-00-00' OR prices.enddate>'" . FormatDateForSQL($_POST['EffectiveDate']) . "')
				AND prices.debtorno=''
			ORDER BY prices.currabrev,
				stockmaster.categoryid,
				stockmaster.stockid,
				prices.startdate";

	$LabelsResult = DB_query($SQL,'','',false,false);

	if (DB_error_no() !=0) {
		prnMsg( _('The Price Labels could not be retrieved by the SQL because'). ' - ' . DB_error_msg(), 'error');
		echo '<br /><a href="' .$RootPath .'/index.php">' .   _('Back to the menu'). '</a>';
		if ($debug==1){
			prnMsg(_('For debugging purposes the SQL used was:') . $SQL,'error');
		}
		include('includes/footer.inc');
		exit;
	}
	if (DB_num_rows($LabelsResult)==0){
		prnMsg(_('There were no price labels to print out for the category specified'),'warn');
		echo '<br /><a href="'.htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' .  _('Back') . '</a>';
		include('includes/footer.inc');
		exit;
	}


	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table class="selection">
			<tr>
				<th>' . _('Item Code') . '</th>
				<th>' . _('Item Description') . '</th>
				<th>' . _('Price') . '</th>
				<th>' . _('Print') . ' ?</th>
			</tr>
			<tr>
				<th colspan="4"><input type="submit" name="SelectAll" value="' . _('Select All Labels') . '" /><input type="checkbox" name="CheckAll" ';
	if (isset($_POST['CheckAll'])){
		echo 'checked="checked" ';
	}
	echo 'onchange="ReloadForm(SelectAll)" /></td>
		</tr>';

	$i=0;
	while ($LabelRow = DB_fetch_array($LabelsResult)){
		echo '<tr>
				<td>' . $LabelRow['stockid'] . '</td>
				<td>' . $LabelRow['description'] . '</td>
				<td class="number">' . locale_number_format($LabelRow['price'],$LabelRow['decimalplaces']) . '</td>
				<td>';
		if (isset($_POST['SelectAll']) AND isset($_POST['CheckAll'])) {
			echo '<input type="checkbox" checked="checked" name="PrintLabel' . $i .'" />';
		} else {
			echo '<input type="checkbox" name="PrintLabel' . $i .'" />';
		}
		echo '</td>
			</tr>';
		echo '<input type="hidden" name="StockID' . $i . '" value="' . $LabelRow['stockid'] . '" />
			<input type="hidden" name="Description' . $i . '" value="' . $LabelRow['description'] . '" />
			<input type="hidden" name="Barcode' . $i . '" value="' . $LabelRow['barcode'] . '" />
			<input type="hidden" name="Price' . $i . '" value="' . locale_number_format($LabelRow['price'],$LabelRow['decimalplaces']) . '" />';
		$i++;
	}
	$i--;
	echo '</table>
		<input type="hidden" name="NoOfLabels" value="' . $i . '" />
		<input type="hidden" name="LabelID" value="' . $_POST['LabelID'] . '" />
		<input type="hidden" name="StockCategory" value="' . $_POST['StockCategory'] . '" />
		<input type="hidden" name="EffectiveDate" value="' . $_POST['EffectiveDate'] . '" />
		<input type="hidden" name="LabelsPerItem" value="' . $_POST['LabelsPerItem'] . '" />
		<br />
		<div class="centre">

			<input type="submit" name="PrintLabels" value="'. _('Print Labels'). '" />
		</div>
		</form>';
	include('includes/footer.inc');
	exit;
}

$NoOfLabels = 0;
if (isset($_POST['PrintLabels']) AND isset($_POST['NoOfLabels']) AND $_POST['NoOfLabels']>0){

	for ($i=0;$i < $_POST['NoOfLabels'];$i++){
		if (isset($_POST['PrintLabel'.$i])){
			$NoOfLabels++;
		}
	}
	if ($NoOfLabels ==0){
		prnMsg(_('There are no labels selected to print'),'info');
	}
}
if (isset($_POST['PrintLabels']) AND $NoOfLabels>0) {

	// Let's start the real PDF creation 
	require_once('includes/tcpdf/tcpdf.php');

	// set the variables depending on the label type
	if ($_POST['LabelID'] == 'T570'){
		// Pricetags T570 general
		$pagelayout = array(90.0, 10.0);
		$CoreFileName = "Pricetags";
		
		// define Logo information
		if (ItemInList($_POST['StockCategory'], LIST_STOCK_CATEGORIES_BLINK)
			OR $_POST['StockCategory'] == "SETBL"){
			$LogoXPosition = 14.0;
			$LogoYPosition = 1.0;
			$LogoHeight = 4.5;
			$LogoFile = 'companies/kurakura_klerp/LogoLabelBLINK.jpg';
		}else{
			$LogoXPosition = 12.0;
			$LogoYPosition = 1.0;
			$LogoHeight = 4.0;
			$LogoFile = 'companies/kurakura_klerp/LogoLabelKL.jpg';
		}
	
		// define price information
		$PriceXPosition = 3;
		$PriceYPosition = 5.7;
		$PriceFont = 'helvetica';
		$PriceFontStyle = 'B';
		$PriceAlignment = 'C';
		$PriceFontSize = 7.5;
		$PriceWidth = 30;
		
		// define barcode style for T570
		$BarcodeXPosition = 30.5;
		$BarcodeYPosition = 0.4;
		$BarcodeLenght = 30;
		$BarcodeWidth = 9.5;
		$ResolutionDPI = 203; // uses imperial system so 200 are really 203. LOL
		$XResolution = 25.4 / $ResolutionDPI; // 25.4mm per inch / resolution points per inch
		$BarcodeStyle = array(
				'position' => '',
				'align' => 'C',
				'stretch' => false,
				'fitwidth' => false,
				'cellfitalign' => '',
				'border' => false,
				'hpadding' => 'auto',
				'vpadding' => 'auto',
				'fgcolor' => array(0,0,0),
				'bgcolor' => false, //array(255,255,255),
				'text' => true,
				'font' => 'helvetica',
				'fontsize' => 7,
				'stretchtext' => 0);
	}else{
		// Code tags
		// not coded yet
		return;
	}

	$pdf = new TCPDF('L', 'mm', $pagelayout, true, 'UTF-8', false);
	
	// set PDF document information
	$pdf->SetCreator('webERP');
	$pdf->SetAuthor('webERP');
	$pdf->SetTitle($CoreFileName);
	$pdf->SetSubject($CoreFileName);
	$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
	$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
	$pdf->SetMargins(0, 0, 0);
	$pdf->SetHeaderMargin(0);
	$pdf->SetFooterMargin(0);
	$pdf->setPrintHeader(false);
	$pdf->setPrintFooter(false);
	$pdf->SetAutoPageBreak(TRUE, 0);
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

	// now print the labels
	$TotalLabels = $NoOfLabels * $_POST['LabelsPerItem'];
	$LabelsPrinted = 0;
	for ($i=0;$i < $_POST['NoOfLabels'];$i++){
		if (isset($_POST['PrintLabel'.$i])){
			$NoOfLabels--;
			for ($LabelNumber=0; $LabelNumber < $_POST['LabelsPerItem'];$LabelNumber++){
				
				// Get the data for each field
				$Price = $_POST['Price' . $i] . ' '. CURRENCY_CODE;
				$StockId = $_POST['StockID' . $i];
				$Description = $_POST['Description' . $i];
				
				$pdf->AddPage();

				// print depending on each type of label
				if ($_POST['LabelID'] == 'T570'){
					//Print the logo
					$pdf->Image($LogoFile, $LogoXPosition, $LogoYPosition, 0, $LogoHeight, 'JPG', '', '', true, 200, '', false, false, 0, false, false, false);
					// print the price
					$pdf->SetXY($PriceXPosition,$PriceYPosition);
					$pdf->SetFont($PriceFont, $PriceFontStyle, $PriceFontSize);
					$pdf->Cell($PriceWidth, 0, $Price, 0, 0, $PriceAlignment);
					// print the barcode
					$pdf->write1DBarcode($StockId, 'C128', $BarcodeXPosition, $BarcodeYPosition, $BarcodeLenght, $BarcodeWidth, $XResolution, $BarcodeStyle, 'N');
				}
				$LabelsPrinted++;
			}
		} //this label is set to print
	} //loop through labels selected to print


	$FileName= $CoreFileName . '_' . date('Y-m-d'). '_'. $_POST['StockCategory'] .'.pdf';
	$pdf->Output($FileName, 'D');
	$pdf->__destruct();

} else { /*The option to print PDF was not hit */

	$Title= _('KL Print Labels');
	include('includes/header.inc');

	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('Price Labels') . '" alt="" />
         ' . ' ' . _('Print Price Labels') . '</p>';

	if (!function_exists('gd_info')) {
		prnMsg(_('The GD module for PHP is required to print barcode labels. Your PHP installation is not capable currently. You will most likely experience problems with this script until the GD module is enabled.'),'error');
	}


	if (!isset($_POST['StockCategory'])) {

	/*if $StockCategory is not set then show a form to allow input	*/

		echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
				<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
				<table class="selection">';

		echo '<tr>
			<td>' . _('Label Type') . ':</td>
			<td><select name="LabelID">
				<option selected="selected" value="T570">' . _('Pricetags T570') . '</option>
				<option value="XXXX">' . _('Code Stickers') . '</option>
			</select></td>
			</tr>';
			
		echo '<tr>
				<td>' .  _('For Stock Category') .':</td>
				<td><select name="StockCategory">';

		$CatResult= DB_query("SELECT categoryid, categorydescription FROM stockcategory ORDER BY categorydescription");
		while ($myrow = DB_fetch_array($CatResult)){
			echo '<option value="' . $myrow['categoryid'] . '">' . $myrow['categorydescription'] . '</option>';
		}
		echo '</select></td></tr>';

		echo '<tr>
			<td>' . _('Prices Effective As At') . ':</td>
			<td><input type="text" size="11" class="date"	alt="' . $_SESSION['DefaultDateFormat'] . '" name="EffectiveDate" value="' . Date($_SESSION['DefaultDateFormat']) . '" />';
        echo '</td></tr>';

		echo'<tr><td>' . _('Number of labels per item') . ':</td>
			<td><input type="text" class="number" name="LabelsPerItem" size="3" value="1" /></tr>';

		echo '</table>
				<br />
				<div class="centre">
					<input type="submit" name="ShowLabels" value="'. _('Show Labels'). '" />
				</div>
				</form>';
	}
	include('includes/footer.inc');

} /*end of else not PrintPDF */

?>
<?php
/* $Id: PDFPriceLabelsbyTCPDF.php  */

include('includes/session.inc');
require_once('includes/tcpdf/tcpdf.php');

if ((isset($_POST['ShowLabels']) OR isset($_POST['SelectAll']))
	AND isset($_POST['StockCategory'])
	AND mb_strlen($_POST['StockCategory'])>=1){

	$Title = _('Print Labels');
	include('includes/header.inc');

	$SQL = "SELECT prices.stockid,
					stockmaster.description,
					stockmaster.barcode,
					prices.price,
					currencies.decimalplaces
			FROM stockmaster INNER JOIN	stockcategory
   			     ON stockmaster.categoryid=stockcategory.categoryid
			INNER JOIN prices
				ON stockmaster.stockid=prices.stockid
			INNER JOIN currencies
				ON prices.currabrev=currencies.currabrev
			WHERE stockmaster.categoryid = '" . $_POST['StockCategory'] . "'
			AND prices.typeabbrev='" . $_POST['SalesType'] . "'
			AND prices.currabrev='" . $_POST['Currency'] . "'
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
		<input type="hidden" name="SalesType" value="' . $_POST['SalesType'] . '" />
		<input type="hidden" name="Currency" value="' . $_POST['Currency'] . '" />
		<input type="hidden" name="EffectiveDate" value="' . $_POST['EffectiveDate'] . '" />
		<input type="hidden" name="LabelsPerItem" value="' . $_POST['LabelsPerItem'] . '" />
		<br />
		<div class="centre">

			<input type="submit" name="PrintLabels" value="'. _('Print Labels'). '" />
		</div>
		<br />
			<div class="centre">
				<a href="'. $RootPath . '/Labels.php">' . _('Label Template Maintenance'). '</a>
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

	$result = DB_query("SELECT 	description,
								pagewidth AS page_width,
								pageheight AS page_height,
								width AS label_width,
								height AS label_height,
								rowheight AS label_rowheight,
								columnwidth AS label_columnwidth,
								topmargin AS label_topmargin,
								leftmargin AS label_leftmargin
						FROM labels
						WHERE labelid='" . $_POST['LabelID'] . "'");
	$LabelDimensions = DB_fetch_array($result);

	$result = DB_query("SELECT fieldvalue,
								vpos,
								hpos,
								fontsize,
								barcode
						FROM labelfields
						WHERE labelid = '" . $_POST['LabelID'] . "'");
	$LabelFields = array();
	$i=0;
	while ($LabelFieldRow = DB_fetch_array($result)){
		if ($LabelFieldRow['fieldvalue'] == 'itemcode'){
			$LabelFields[$i]['FieldValue'] = 'stockid';
		} elseif ($LabelFieldRow['fieldvalue'] == 'itemdescription'){
			$LabelFields[$i]['FieldValue'] = 'description';
		} else {
			$LabelFields[$i]['FieldValue'] = $LabelFieldRow['fieldvalue'];
		}
		$LabelFields[$i]['VPos'] = $LabelFieldRow['vpos'];
		$LabelFields[$i]['HPos'] = $LabelFieldRow['hpos'];
		$LabelFields[$i]['FontSize'] = $LabelFieldRow['fontsize'];
		$LabelFields[$i]['Barcode'] = $LabelFieldRow['barcode'];
		$i++;
	}

	$PaperSize = 'Custom'; // so PDF starter wont default the DocumentPaper
	$DocumentPaper = array($LabelDimensions['page_width'],$LabelDimensions['page_height']);

	// create new PDF document
	$pagelayout = array($LabelDimensions['page_height'], $LabelDimensions['page_width']);
	$pagelayout = array(297, 210);
	$pdf = new TCPDF('L', 'mm', $pagelayout, true, 'UTF-8', false);

	// set document information
	$pdf->SetCreator('TCPDF');
	$pdf->SetAuthor('KL');
	$pdf->SetTitle('Price Labels');
	$pdf->SetSubject('Price Labels');

	// set default header data
	define ('PDF_HEADER_LOGO', '');
	define ('PDF_HEADER_LOGO_WIDTH', 0);
	define ('PDF_HEADER_TITLE', '');
	define ('PDF_HEADER_STRING', '');

	$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

	// set header and footer fonts
	define ('PDF_FONT_NAME_MAIN', 'helvetica');
	define ('PDF_FONT_SIZE_MAIN', 10);
	define ('PDF_FONT_NAME_DATA', 'helvetica');
	define ('PDF_FONT_SIZE_DATA', 8);
	$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
	$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

	// set default monospaced font
	define ('PDF_FONT_MONOSPACED', 'courier');
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

	// set margins
	define ('PDF_MARGIN_HEADER', 0);
	define ('PDF_MARGIN_FOOTER', 0);
	define ('PDF_MARGIN_TOP', 0);
	define ('PDF_MARGIN_BOTTOM', 0);
	define ('PDF_MARGIN_LEFT', 0);
	define ('PDF_MARGIN_RIGHT', 0);
	$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT, true);
	$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

	// set auto page breaks
	$pdf->SetAutoPageBreak(TRUE, 0);

	// set image scale factor
	define ('PDF_IMAGE_SCALE_RATIO', 1.25);
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

	$pdf->SetY(30);

	// set font
	$pdf->SetFont('helvetica', '', 11);

	$Top_Margin = $LabelDimensions['label_topmargin'];
	$Left_Margin = $LabelDimensions['label_leftmargin'];
	$Page_Height = $LabelDimensions['page_height'];
	$Page_Width = $LabelDimensions['page_width'];
	$Right_Margin =0;
	$Bottom_Margin =0;

	$pdf->setPrintHeader(false);
	$pdf->setPrintFooter(false);

	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
	$pdf->setPrintHeader(false);
	$pdf->setPrintFooter(false);

	// define barcode style
	$WidthSmallestBar = 25.4 / 200; // 25.4mm per inch / 200 points per inch
	$style = array(
		'position' => '',
		'align' => 'C',
		'stretch' => false,
		'fitwidth' => true,
		'cellfitalign' => '',
		'border' => true,
		'hpadding' => 'auto',
		'vpadding' => 'auto',
		'fgcolor' => array(0,0,0),
		'bgcolor' => false, //array(255,255,255),
		'text' => true,
		'font' => 'helvetica',
		'fontsize' => 8,
		'stretchtext' => 4);

	$TotalLabels = $NoOfLabels * $_POST['LabelsPerItem'];
	$LabelsPrinted = 0;
	for ($i=0;$i < $_POST['NoOfLabels'];$i++){
		if (isset($_POST['PrintLabel'.$i])){
			$NoOfLabels--;
			for ($LabelNumber=0; $LabelNumber < $_POST['LabelsPerItem'];$LabelNumber++){
				$pdf->AddPage();
				foreach ($LabelFields AS $Field){
					if ($Field['FieldValue']== 'price'){
						$Value = $_POST['Price' . $i] . ' '. $_POST['Currency'];
					} elseif ($Field['FieldValue']== 'stockid'){
						$Value = $_POST['StockID' . $i];
					} elseif ($Field['FieldValue']== 'description'){
						$Value = $_POST['Description' . $i];
					} elseif ($Field['FieldValue']== 'barcode'){
						$Value = $_POST['Barcode' . $i];
					}
					if ($Field['FieldValue'] == 'price'){ //need to format for the number of decimal places
//						$LeftOvers = $pdf->addTextWrap($XPos+$Field['HPos'],$YPos-$LabelDimensions['label_height']+$Field['VPos'],$LabelDimensions['label_width']-$Field['HPos'],$Field['FontSize'],$Value);
					}elseif ($Field['FieldValue'] == 'logo'){ 
//						$pdf->addJpegFromFile($_SESSION['LogoFile'],$XPos+$Field['HPos'],$YPos-$LabelDimensions['label_height']+$Field['VPos'],'', $Field['FontSize']);
					}elseif($Field['Barcode']==1) {

//						$pdf->Cell(0, 0, $Value, 0, 1);
						$pdf->write1DBarcode($Value, 'C128', 0, 0, 20, 9, $WidthSmallestBar, $style, 'N');						

					} else {
//						$LeftOvers = $pdf->addTextWrap($XPos+$Field['HPos'],$YPos-$LabelDimensions['label_height']+$Field['VPos'],$LabelDimensions['label_width']-$Field['HPos'],$Field['FontSize'],$Value);
					}
				} // end loop through label fields
				$LabelsPrinted++;
				if ($LabelsPrinted < $TotalLabels){ // if there is another label to print
				}
			}
		} //this label is set to print
	} //loop through labels selected to print


	$FileName=$_SESSION['DatabaseName']. '_' . _('Price_Labels') . '_' . date('Y-m-d').'.pdf';
	$pdf->Output($FileName, 'I');
	$pdf->__destruct();

} else { /*The option to print PDF was not hit */

	$Title= _('Price Labels');
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
				<table class="selection">
				<tr>
					<td>' . _('Label to print') . ':</td>
					<td><select required="required" autofocus="autofocus" name="LabelID">';

		$LabelResult = DB_query("SELECT labelid, description FROM labels");
		while ($LabelRow = DB_fetch_array($LabelResult)){
			echo '<option value="' . $LabelRow['labelid'] . '">' . $LabelRow['description'] . '</option>';
		}
		echo '</select></td>
			</tr>
			<tr>
				<td>' .  _('For Stock Category') .':</td>
				<td><select name="StockCategory">';

		$CatResult= DB_query("SELECT categoryid, categorydescription FROM stockcategory ORDER BY categorydescription");
		while ($myrow = DB_fetch_array($CatResult)){
			echo '<option value="' . $myrow['categoryid'] . '">' . $myrow['categorydescription'] . '</option>';
		}
		echo '</select></td></tr>';

		echo '<tr><td>' . _('For Sales Type/Price List').':</td>
                  <td><select name="SalesType">';
		$sql = "SELECT sales_type, typeabbrev FROM salestypes";
		$SalesTypesResult=DB_query($sql);

		while ($myrow=DB_fetch_array($SalesTypesResult)){
			if ($_SESSION['DefaultPriceList']==$myrow['typeabbrev']){
				echo '<option selected="selected" value="' . $myrow['typeabbrev'] . '">' . $myrow['sales_type'] . '</option>';
			} else {
				echo '<option value="' . $myrow['typeabbrev'] . '">' . $myrow['sales_type'] . '</option>';
			}
		}
		echo '</select></td></tr>';

		echo '<tr><td>' . _('For Currency').':</td>
                  <td><select name="Currency">';
		$sql = "SELECT currabrev, country, currency FROM currencies";
		$CurrenciesResult=DB_query($sql);

		while ($myrow=DB_fetch_array($CurrenciesResult)){
			if ($_SESSION['CompanyRecord']['currencydefault']==$myrow['currabrev']){
				echo '<option selected="selected" value="' . $myrow['currabrev'] . '">' . $myrow['country'] . ' - ' .$myrow['currency'] . '</option>';
			} else {
				echo '<option value="' . $myrow['currabrev'] . '">' . $myrow['country'] . ' - ' .$myrow['currency'] . '</option>';
			}
		}
		echo '</select></td>
		</tr>
		<tr>
			<td>' . _('Effective AS At') . ':</td>
			<td><input type="text" size="11" class="date"	alt="' . $_SESSION['DefaultDateFormat'] . '" name="EffectiveDate" value="' . Date($_SESSION['DefaultDateFormat']) . '" />';
        echo '</td></tr>';

		echo'<tr><td>' . _('Number of labels per item') . ':</td>
			<td><input type="text" class="number" name="LabelsPerItem" size="3" value="1" /></tr>';

		echo '</table>
				<br />
				<div class="centre">
					<input type="submit" name="ShowLabels" value="'. _('Show Labels'). '" />
				</div>
				<br />
				<div class="centre">
					<a href="'. $RootPath . '/Labels.php">' . _('Label Template Maintenance'). '</a>
				</div>
				</form>';

	}
	include('includes/footer.inc');

} /*end of else not PrintPDF */

?>
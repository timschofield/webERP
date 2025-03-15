<?php

include('includes/session.php');

require_once 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Helper\Sample;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

include('includes/SQL_CommonFunctions.inc');
include('includes/KLDefines.php');
include('includes/UIGeneralFunctions.php');
include('includes/KLUIGeneralFunctions.php');
include('includes/KLBoards.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLMarketplaceFunctions.php');
include('includes/OCOpenCartGeneralFunctions.php');
include('includes/GetPrice.inc');


if (isset($_POST['submit'])) {
    submit($_POST['TypeOfShop'], $_POST['TypeOfFile']);
} else {
    display($RootPath, $Theme);
}

//####_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
function submit($TypeOfShop, $TypeOfFile) {

	// CONSTANT TEXTS
	$ShippingTimeMinimal = 3;
	$ShippingTimeMaximal = 6;
	$Warranty = "Garansi terbatas untuk 3 bulan. Cek http://www.kapal-laut.com/Warranty-Conditions";
	$Country = "Indonesia";
	$CurrencyCode = "IDR";
	$ImagePath = "http://www.kapal-laut.com/image/";
	$BodyJewellery = "Yes";
	$BarangBerbahaya = "No";
	$SizeFreeSize = 'Free size';
	$UnitPair = '1 pasang';
	$UnitPcs = '1 biji';
	$MaxColumn = 'A';

	//initialise no input errors
	$InputError = 0;

	if ($InputError == 0){
			
		$SQL = "SELECT stockmaster.stockid,
						stockmaster.categoryid,
						stockmaster.description,
						stockmaster.longdescription,
						stockmaster.grossweight,
						stockmaster.length,
						stockmaster.width,
						stockmaster.height,
						stockmaster.unitsdimension,
						stockmaster.klpackaging,
						stockmaster.categoryid,
						stockdescriptiontranslations.descriptiontranslation,
						stockdescriptiontranslations.longdescriptiontranslation,
						salescatprod.manufacturers_id,
						prices.price
				FROM stockmaster, prices, stockdescriptiontranslations, salescatprod
				WHERE stockmaster.stockid = prices.stockid
					AND stockmaster.stockid = stockdescriptiontranslations.stockid
					AND stockmaster.stockid = salescatprod.stockid
					AND stockdescriptiontranslations.language_id = 'id_ID.utf8'
					AND stockmaster.discontinued = 0 
					AND salescatprod.manufacturers_id = '" . $TypeOfShop . "'
					AND prices.typeabbrev = '" . RETAIL_PRICE_LIST . "'
					AND prices.currabrev = '". CURRENCY_CODE ."'
					AND prices.startdate <= CURRENT_DATE 
					AND prices.enddate >= CURRENT_DATE
				ORDER BY stockmaster.stockid";
		$Result = DB_query($SQL);

		if (DB_num_rows($Result) != 0){
			
			// Set value binder
			\PhpOffice\PhpSpreadsheet\Cell\Cell::setValueBinder(new \PhpOffice\PhpSpreadsheet\Cell\AdvancedValueBinder());
		
			// Create new Spreadsheet object
			$SpreadSheet = new Spreadsheet();

			// Set document properties
			$SpreadSheet->getProperties()->setCreator("webERP")
										 ->setLastModifiedBy("webERP")
										 ->setTitle("FORSTOK " . $TypeOfFile)
										 ->setSubject("FORSTOK " . $TypeOfFile)
										 ->setDescription("FORSTOK " . $TypeOfFile)
										 ->setKeywords("")
										 ->setCategory("");

			// Add title data
			$SpreadSheet->setActiveSheetIndex(0);
			$ActiveSheet = $SpreadSheet->getActiveSheet();

			if ($TypeOfFile == "FSMaster"){
				$ActiveSheet->setTitle(('FORSTOK Master'));
				$ActiveSheet->setCellValue('A1', 'Variant SKU*');
				$ActiveSheet->setCellValue('B1', 'Item Name*');
				$ActiveSheet->setCellValue('C1', 'Master Brand*');
				$ActiveSheet->setCellValue('D1', 'Master Category*');
				$ActiveSheet->setCellValue('E1', 'Option Type 1');
				$ActiveSheet->setCellValue('F1', 'Option Value 1');
				$ActiveSheet->setCellValue('G1', 'Option Type 2');
				$ActiveSheet->setCellValue('H1', 'Option Value 2');
				$ActiveSheet->setCellValue('I1', 'Weight (gr)*');
				$ActiveSheet->setCellValue('J1', 'Dimension Length (cm)*');
				$ActiveSheet->setCellValue('K1', 'Dimension Width (cm)*');
				$ActiveSheet->setCellValue('L1', 'Dimension Height (cm)*');
				$ActiveSheet->setCellValue('M1', 'Cost Price');
				$ActiveSheet->setCellValue('N1', 'Regular Price*');
				$ActiveSheet->setCellValue('O1', 'Quantity*');
				$ActiveSheet->setCellValue('P1', 'Barcode');
				$ActiveSheet->setCellValue('Q1', 'Location ID');
				$ActiveSheet->setCellValue('R1', 'Description*');
				$ActiveSheet->setCellValue('S1', 'Image_url1');
				$ActiveSheet->setCellValue('T1', 'Image_url2');
				$ActiveSheet->setCellValue('U1', 'Image_url3');
				$ActiveSheet->setCellValue('V1', 'Image_url4');
				$ActiveSheet->setCellValue('W1', 'Image_url5');
				$ActiveSheet->setCellValue('X1', 'Image_url6');
				$MaxColumn = 'X';
				$StartingRow = 2;
			}elseif($TypeOfFile == "FSQOH"){
				$ActiveSheet->setTitle(('FORSTOK QOH'));
				$ActiveSheet->setCellValue('A6', 'SKU');
				$ActiveSheet->setCellValue('B6', 'Name');
				$ActiveSheet->setCellValue('C6', 'Current Qty On Hand');
				$ActiveSheet->setCellValue('D6', 'New Qty On Hand');
				$MaxColumn = 'F';
				$StartingRow = 7;
			}elseif($TypeOfFile == "FSPrice"){
				$ActiveSheet->setTitle(('FORSTOK Price'));
				$ActiveSheet->setCellValue('A1', 'SKU');
				$ActiveSheet->setCellValue('B1', 'Name');
				$ActiveSheet->setCellValue('AB1', 'Tokopedia Price');
				$ActiveSheet->setCellValue('AC1', 'Shopee Price');
				$MaxColumn = 'BG';
				$StartingRow = 2;
			
			}

			// Add data in the following row number
			$i = $StartingRow;

			while ($MyRow = DB_fetch_array($Result)) {
				
				if (!ItemInLIst($MyRow['categoryid'], LIST_STOCK_CATEGORIES_OUTLET)){
					// we don't send discounted items to marketplaces
					
					$StockID = $MyRow['stockid'];

					if ($MyRow['manufacturers_id'] == 1){
						$NameOfShop = "Kapal-Laut";
						$Brand = "Kapal-Laut. Your Essential Jewellery";
					}else{
						$NameOfShop = "Blink";
						$Brand = "Blink by Kapal-Laut";
					}

					$TextSizeIndonesian = CreateTextSize($StockID, "ID", true);
					$TextSizeEnglish = CreateTextSize($StockID, "EN", true);
					$TextSizeGrouping = CreateTextSize($StockID, "EN", false);

					$OnlySize = ClassicalSize($StockID);
					if ($OnlySize != "NO SIZE"){
						$NamaVariant = "Ukuran";
					}else{
						$NamaVariant = "";
						$OnlySize = "";
					}

					$Name = ItemMarketplaceName($StockID, $MyRow['description'], $MyRow['descriptiontranslation']);
					$Price = round($MyRow['price']);
					$PriceDiscount = '';
					$Description = trim($MyRow['longdescriptiontranslation']). " " . 
							$TextSizeIndonesian . " - "  . 
							trim($MyRow['longdescription']) . " " . 
							$TextSizeEnglish;
					$Weight = $MyRow['grossweight'] * 1000; // webERP in KG, AdminCerdas in gr
					
					$QOH = ItemMarketplaceQOH($StockID);
					$Category = FindShopeeCategory($StockID, $Name, $Description);
					$Material = FindLazadaMaterial($TypeOfFile, $Name);
					$Stone = FindLazadaStone($Name);
					$WhatsInTheBox = WhatsInTheBox($StockID);
					$Color = FindLAzadaColor($Name);

					if ($MyRow['unitsdimension'] == 'mm'){
						$FactorLenght = 10;
					}elseif ($MyRow['unitsdimension'] == 'cm'){
						$FactorLenght = 1;
					}else{
						// should be meter
						$FactorLenght = 0.1;
					}
					$Length = $MyRow['length']/$FactorLenght; 
					$Width = $MyRow['width']/$FactorLenght; 
					$Height = $MyRow['height']/$FactorLenght; 
					$Weight = $MyRow['grossweight'] * 1000; // weight in grams

					$PackagingImage = FALSE;
					list($Url_1, $PackagingImage) = ItemImagesURL($StockID,   1, $PackagingImage, $MyRow['klpackaging']);
					list($Url_2, $PackagingImage) = ItemImagesURL($StockID,   2, $PackagingImage, $MyRow['klpackaging']);
					list($Url_3, $PackagingImage) = ItemImagesURL($StockID,   3, $PackagingImage, $MyRow['klpackaging']);
					list($Url_4, $PackagingImage) = ItemImagesURL($StockID,   4, $PackagingImage, $MyRow['klpackaging']);
					list($Url_5, $PackagingImage) = ItemImagesURL($StockID,   5, $PackagingImage, $MyRow['klpackaging']);
					list($Url_6, $PackagingImage) = ItemImagesURL($StockID,   6, $PackagingImage, $MyRow['klpackaging']);
					list($Url_7, $PackagingImage) = ItemImagesURL($StockID,   7, $PackagingImage, $MyRow['klpackaging']);
					list($Url_8, $PackagingImage) = ItemImagesURL($StockID, 999, $PackagingImage, $MyRow['klpackaging']);
					// only a packaging pic for the 8th URL (if not yet)

					if ($TypeOfFile == "FSMaster"){
						$ActiveSheet->setCellValue('A'.$i, $StockID);
						$ActiveSheet->setCellValue('B'.$i, $Name);
						$ActiveSheet->setCellValue('C'.$i, $Brand);
						$ActiveSheet->setCellValue('D'.$i, $Category);
						$ActiveSheet->setCellValue('E'.$i, $NamaVariant);
						$ActiveSheet->setCellValue('F'.$i, $OnlySize);
						$ActiveSheet->setCellValue('I'.$i, $Weight);
						$ActiveSheet->setCellValue('J'.$i, $Length);
						$ActiveSheet->setCellValue('K'.$i, $Width);
						$ActiveSheet->setCellValue('L'.$i, $Height);
						$ActiveSheet->setCellValue('N'.$i, $Price);
						$ActiveSheet->setCellValue('O'.$i, $QOH);
						$ActiveSheet->setCellValue('R'.$i, $Description);
						$ActiveSheet->setCellValue('S'.$i, $Url_1);
						$ActiveSheet->setCellValue('T'.$i, $Url_2);
						$ActiveSheet->setCellValue('U'.$i, $Url_3);
						$ActiveSheet->setCellValue('V'.$i, $Url_4);
						$ActiveSheet->setCellValue('W'.$i, $Url_5);
						$ActiveSheet->setCellValue('X'.$i, $Url_6);
					}elseif($TypeOfFile == "FSQOH"){
						$ActiveSheet->setCellValue('A'.$i, $StockID);
						$ActiveSheet->setCellValue('B'.$i, $Name);
						$ActiveSheet->setCellValue('D'.$i, $QOH);
					}elseif($TypeOfFile == "FSPrice"){
						$ActiveSheet->setCellValue('A'.$i, $StockID);
						$ActiveSheet->setCellValue('B'.$i, $Name);
						$ActiveSheet->setCellValue('AB'.$i, $Price);
						$ActiveSheet->setCellValue('AC'.$i, $Price);
					}
					$i++;
				}
			}

			// Auto Size columns
			foreach(range('A',$MaxColumn) as $ColumnID) {
				$ActiveSheet->getColumnDimension($ColumnID)->setAutoSize(true);
			}
	
			// Set active sheet index to the first sheet, so Excel opens this as the first sheet
			$SpreadSheet->setActiveSheetIndex(0);

			// Redirect output to a client�s web browser (Excel2007)
			if ($_POST['Format'] == 'xlsx') {
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				$File = $NameOfShop . '-' .  $TypeOfFile . '-' . Date('Y-m-d-H-i-s'). '.xlsx';
			} else if ($_POST['Format'] == 'ods') {
				header('Content-Type: application/vnd.oasis.opendocument.spreadsheet');
				$File = $NameOfShop . '-' .  $TypeOfFile . '-' . Date('Y-m-d-H-i-s'). '.ods';
			}
			header('Content-Disposition: attachment;filename="' . $File . '"');
			header('Cache-Control: max-age=0');
			// If you're serving to IE 9, then the following may be needed
			header('Cache-Control: max-age=2');

			// If you're serving to IE over SSL, then the following may be needed
			header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
			header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
			header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
			header ('Pragma: public'); // HTTP/1.0

			if ($_POST['Format'] == 'xlsx') {
				$objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($SpreadSheet);
				$objWriter->save('php://output');
			} else if ($_POST['Format'] == 'ods') {
				$objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Ods($SpreadSheet);
				$objWriter->save('php://output');
			}

		}else{
			$Title = "Excel file for uploading products to FORSTOK";
			include('includes/header.php');
			prnMsg('No products to upload to FORSTOK');
			include('includes/footer.php');
		}
	} 
} // End of function submit()


function display($RootPath, $Theme)  //####DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_#####
{
// Display form fields. This function is called the first time
// the page is called.
	$Title = _('Excel file for uploading products to FORSTOK');

	include('includes/header.php');

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
          <div>
			<br/>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . $Title. '" alt="" />' . ' ' . $Title . '
		</p>';

	echo '<fieldset>
			<legend>' . _('FORSTOK Export Options') . '</legend>';
	
	echo FieldToSelectOneBrand('submit', 'TypeOfShop', _('Marketplace kind of shop'), '', '', 1);

	// FORSTOK File type selection
	echo FieldToSelectFromThreeOptions('FSMaster', _('Master FORSTOK'),
										'FSQOH', _('QOH FORSTOK Update'),
										'FSPrice', _('Price FORSTOK Update'),
										'TypeOfFile', $_POST['TypeOfFile'],	_('Type of FORSTOK File'), '', '', 2, true, false);

	echo FieldToSelectSpreadSheetFormat('Format', $_POST['Format'], _('File Format'));

	echo '</fieldset>';

	echo OneButtonCenteredForm('submit', _('Export file to upload products to FORSTOK'));

	echo '</div>
         </form>';
	include('includes/footer.php');
} // End of function display()

?>
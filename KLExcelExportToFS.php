<?php

require(__DIR__ . '/includes/session.php');

use PhpOffice\PhpSpreadsheet\Helper\Sample;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

include('includes/SQL_CommonFunctions.php');
include('includes/KLDefines.php');
include('includes/UIGeneralFunctions.php');
include('includes/KLUIGeneralFunctions.php');
include('includes/KLBoards.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLMarketplaceFunctions.php');
include('includes/OCOpenCartGeneralFunctions.php');
include('includes/GetPrice.php');

if (!isset($_POST['Format'])) {
	$_POST['Format'] = 'xlsx';
}
if (!isset($_POST['TypeOfFile'])) {
	$_POST['TypeOfFile'] = 'FSMaster';
}

if (isset($_POST['submit'])) {
    submit($_POST['TypeOfFile']);
} else {
    display($RootPath, $Theme);
}

function submit($TypeOfFile) {

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
						prices.price
				FROM stockmaster, prices, stockdescriptiontranslations
				WHERE stockmaster.stockid = prices.stockid
					AND stockmaster.stockid = stockdescriptiontranslations.stockid
					AND stockmaster.categoryid IN " . ONLINESHOP_AVAILABLE_STOCK_CATEGORIES . "
					AND stockdescriptiontranslations.language_id = 'id_ID.utf8'
					AND stockmaster.discontinued = 0 
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
			$SpreadSheet->getActiveSheet()->setTitle("FORSTOK " . $TypeOfFile);
			$ActiveSheet = $SpreadSheet->getActiveSheet();

			if ($TypeOfFile == "FSMaster"){
				$ActiveSheet->setTitle(('FORSTOK Master'));
				$ActiveSheet->setCellValue('A1', 'Variant SKU*');
				$ActiveSheet->setCellValue('B1', 'Product Name*');
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
				$ActiveSheet->setCellValue('M1', 'Regular Price');
				$ActiveSheet->setCellValue('N1', 'Sale Price*');
				$ActiveSheet->setCellValue('O1', 'Qty on Hand*');
				$ActiveSheet->setCellValue('P1', 'UPC');
				$ActiveSheet->setCellValue('Q1', 'Description*');
				$ActiveSheet->setCellValue('R1', 'Image_url1');
				$ActiveSheet->setCellValue('S1', 'Image_url2');
				$ActiveSheet->setCellValue('T1', 'Image_url3');
				$ActiveSheet->setCellValue('U1', 'Image_url4');
				$ActiveSheet->setCellValue('V1', 'Image_url5');
				$ActiveSheet->setCellValue('W1', 'Image_url6');
				$ActiveSheet->setCellValue('X1', 'Image_url7');
				$ActiveSheet->setCellValue('Y1', 'Image_url8');
				$ActiveSheet->setCellValue('Z1', 'Variant Image Url');
				$MaxColumn = 'Z';
				$StartingRow = 2;
			}elseif($TypeOfFile == "FSQOH"){
				$ActiveSheet->setTitle(('FORSTOK QOH'));
				$ActiveSheet->setCellValue('A6', 'SKU');
				$ActiveSheet->setCellValue('B6', 'Name');
				$ActiveSheet->setCellValue('C6', 'Current Qty On Hand');
				$ActiveSheet->setCellValue('D6', 'New Qty On Hand');
				$ActiveSheet->setCellValue('E6', 'Reason');
				$ActiveSheet->setCellValue('F6', 'Note');
				$MaxColumn = 'F';
				$StartingRow = 7;
			}elseif($TypeOfFile == "FSPrice"){
				$ActiveSheet->setTitle(('FORSTOK Price'));
				$ActiveSheet->setCellValue('A1', 'SKU');
				$ActiveSheet->setCellValue('B1', 'Product Name');
				$ActiveSheet->setCellValue('C1', 'Channel');
				$ActiveSheet->setCellValue('D1', 'Store Name');
				$ActiveSheet->setCellValue('E1', 'Price (Number Only)');
				$ActiveSheet->setCellValue('F1', 'Sale Price');
				$ActiveSheet->setCellValue('G1', 'Sale Start');
				$ActiveSheet->setCellValue('H1', 'Sale End');
				$ActiveSheet->setCellValue('I1', 'Product Channel ID');
				$ActiveSheet->setCellValue('J1', 'Account ID');
				$MaxColumn = 'J';
				$StartingRow = 2;
			}

			// Add data in the following row number
			$i = $StartingRow;

			while ($MyRow = DB_fetch_array($Result)) {
				
				if (ItemInLIst($MyRow['categoryid'], LIST_STOCK_CATEGORIES_KAPAL_LAUT_INCLUDING_SETUP_ALL_DISCOUNT)){
					$Brand = MARKETPLACES_BRAND_KAPAL_LAUT;
					$TokopediaAccountId = TOKOPEDIA_STOREID_KAPAL_LAUT;
					$TokopediaStoreName = TOKOPEDIA_STORENAME_KAPAL_LAUT;
					$ShopeeAccountId = SHOPEE_STOREID_KAPAL_LAUT;
					$ShopeeStoreName = SHOPEE_STORENAME_KAPAL_LAUT;
				} else {
					$Brand = MARKETPLACES_BRAND_BLINK;
					$TokopediaAccountId = TOKOPEDIA_STOREID_BLINK;
					$TokopediaStoreName = TOKOPEDIA_STORENAME_BLINK;
					$ShopeeAccountId = SHOPEE_STOREID_BLINK;
					$ShopeeStoreName = SHOPEE_STORENAME_BLINK;
				}

				$StockID = $MyRow['stockid'];

				$TextSizeIndonesian = CreateTextSize($StockID, "ID", true);
				$TextSizeEnglish = CreateTextSize($StockID, "EN", true);
/*					$TextSizeGrouping = CreateTextSize($StockID, "EN", false);
*/
				$OnlySize = ClassicalSize($StockID);
				if ($OnlySize != "NO SIZE"){
					$NamaVariant = "Ukuran";
				}else{
					$NamaVariant = "";
					$OnlySize = "";
				}

				$Name = ItemMarketplaceName($StockID, $MyRow['description'], $MyRow['descriptiontranslation']);
				$Price = round($MyRow['price']);

				$Description = trim($MyRow['longdescriptiontranslation']). " " . 
							$TextSizeIndonesian . " - "  . 
							trim($MyRow['longdescription']) . " " . 
							$TextSizeEnglish;
				
				if (ItemInLIst($MyRow['categoryid'], LIST_STOCK_CATEGORIES_OUTLET)){
					// we don't sell discounted items in marketplaces, so we mark as QOH = 0
					$QOH = 0;
				} else {
					$QOH = ItemMarketplaceQOH($MyRow['stockid']);
				}

				$Category = FindShopeeCategory($StockID, $Name, $Description);
/*					$Material = FindLazadaMaterial($TypeOfFile, $Name);
				$Stone = FindLazadaStone($Name);
				$WhatsInTheBox = WhatsInTheBox($StockID);
				$Color = FindLazadaColor($Name);
*/
				if ($MyRow['unitsdimension'] == 'mm'){
					$FactorLenght = 10;
				}elseif ($MyRow['unitsdimension'] == 'cm'){
					$FactorLenght = 1;
				}else{
					// should be meter
					$FactorLenght = 0.1;
				}
				$Length = ceil($MyRow['length']/$FactorLenght);
				$Width = ceil($MyRow['width']/$FactorLenght);
				$Height = ceil($MyRow['height']/$FactorLenght);
				$Weight = ceil($MyRow['grossweight'] * 1000); // weight in grams

				$PackagingImage = false;
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
					$ActiveSheet->setCellValue('M'.$i, $Price);
					$ActiveSheet->setCellValue('O'.$i, $QOH);
					$ActiveSheet->setCellValue('Q'.$i, $Description);
					$ActiveSheet->setCellValue('R'.$i, $Url_1);
					$ActiveSheet->setCellValue('S'.$i, $Url_2);
					$ActiveSheet->setCellValue('T'.$i, $Url_3);
					$ActiveSheet->setCellValue('U'.$i, $Url_4);
					$ActiveSheet->setCellValue('V'.$i, $Url_5);
					$ActiveSheet->setCellValue('W'.$i, $Url_6);
					$ActiveSheet->setCellValue('X'.$i, $Url_7);
					$ActiveSheet->setCellValue('Y'.$i, $Url_8);
				}elseif($TypeOfFile == "FSQOH"){
					$ActiveSheet->setCellValue('A'.$i, $StockID);
					$ActiveSheet->setCellValue('B'.$i, $Name);
					$ActiveSheet->setCellValue('C'.$i, $QOH);
					$ActiveSheet->setCellValue('D'.$i, $QOH);
				}elseif($TypeOfFile == "FSPrice"){
					// one row per channel (1st Tokopedia aka TikTok, 2nd Shopee)
					$ActiveSheet->setCellValue('A'.$i, $StockID);
					$ActiveSheet->setCellValue('B'.$i, $Name);
					$ActiveSheet->setCellValue('C'.$i, "TikTok");
					$ActiveSheet->setCellValue('D'.$i, $TokopediaStoreName);
					$ActiveSheet->setCellValue('E'.$i, $Price);
					$ActiveSheet->setCellValue('I'.$i, GetTokopediaProductId($StockID));
					$ActiveSheet->setCellValue('J'.$i, $TokopediaAccountId);
					$i++;
					// second row for Shopee
					$ActiveSheet->setCellValue('A'.$i, $StockID);
					$ActiveSheet->setCellValue('B'.$i, $Name);
					$ActiveSheet->setCellValue('C'.$i, "Shopee");
					$ActiveSheet->setCellValue('D'.$i, $ShopeeStoreName);
					$ActiveSheet->setCellValue('E'.$i, $Price);
					$ActiveSheet->setCellValue('I'.$i, GetShopeeProductId($StockID));
					$ActiveSheet->setCellValue('J'.$i, $ShopeeAccountId);
				}
				$i++;
			}

			// Auto Size columns
			foreach(range('A',$MaxColumn) as $ColumnID) {
				$ActiveSheet->getColumnDimension($ColumnID)->setAutoSize(true);
			}
	
			// Set active sheet index to the first sheet, so Excel opens this as the first sheet
			$SpreadSheet->setActiveSheetIndex(0);

			// Redirect output to a client web browser (Excel2007)
			if ($_POST['Format'] == 'xlsx') {
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				$File = "FORSTOK" . '-' .  $TypeOfFile . '-' . Date('Y-m-d-H-i-s'). '.xlsx';
			} else if ($_POST['Format'] == 'ods') {
				header('Content-Type: application/vnd.oasis.opendocument.spreadsheet');
				$File = "FORSTOK" . '-' .  $TypeOfFile . '-' . Date('Y-m-d-H-i-s'). '.ods';
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
	$Title = __('Excel file for uploading products to FORSTOK');

	include('includes/header.php');

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
          <div>
			<br/>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . $Title. '" alt="" />' . ' ' . $Title . '
		</p>';

	echo '<fieldset>
			<legend>' . __('FORSTOK Export Options') . '</legend>';

	// FORSTOK File type selection
	echo FieldToSelectFromThreeOptions('FSMaster', __('Master FORSTOK'),
										'FSQOH', __('QOH FORSTOK Update'),
										'FSPrice', __('Price FORSTOK Update'),
										'TypeOfFile', $_POST['TypeOfFile'],	__('Type of FORSTOK File'), '', '', 2, true, false);

	echo FieldToSelectSpreadSheetFormat('Format', $_POST['Format'], __('File Format'));

	echo '</fieldset>';

	echo OneButtonCenteredForm('submit', __('Export file to upload products to FORSTOK'));

	echo '</div>
         </form>';
	include('includes/footer.php');
} // End of function display()

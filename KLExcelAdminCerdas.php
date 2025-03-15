<?php
require_once 'vendor/autoload.php';

include('includes/session.php');
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

if (!isset($_POST['Format'])) {
    $_POST['Format'] = 'xlsx';
}
if (!isset($_POST['TypeOfFile'])) {
    $_POST['TypeOfFile'] = 'FullUpdate';
}
if (!isset($_POST['TypeOfShop'])) {
	$_POST['TypeOfShop'] = 1;
}

if (isset($_POST['submit'])) {
    submit($_POST['TypeOfShop'], $_POST['TypeOfFile']);
} else {
    display($RootPath, $Theme);
}


//####_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
function submit($TypeOfShop, $TypeOfFile) {

	//initialise no input errors
	$InputError = 0;

	//first off validate inputs sensible
	if (($TypeOfShop < 1) OR ($TypeOfShop > 2)) {
		$InputError = 1;
		prnMsg("Type of marketplace should be Kapal-Laut or Blink only",'error');
	}

	if ($InputError == 0){
		
		if ($TypeOfShop == 1){
			$NameOfShop = "Kapal-Laut";
		}else{
			$NameOfShop = "Blink";
		}
		
		$SQL = "SELECT stockmaster.stockid,
						stockmaster.categoryid,
						stockmaster.description,
						stockmaster.longdescription,
						stockmaster.grossweight,
						stockmaster.klpackaging,
						stockmaster.categoryid,
						stockdescriptiontranslations.descriptiontranslation,
						stockdescriptiontranslations.longdescriptiontranslation,
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
										 ->setTitle("Admin Cerdas " . $NameOfShop)
										 ->setSubject("Admin Cerdas " . $NameOfShop)
										 ->setDescription("Admin Cerdas " . $NameOfShop)
										 ->setKeywords("")
										 ->setCategory("");

			// Add title data
			$SpreadSheet->setActiveSheetIndex(0);
			$SpreadSheet->getActiveSheet()->setTitle(("AC " . $NameOfShop));
			
			if ($TypeOfFile == "FullUpdate"){
				$SpreadSheet->getActiveSheet()->setCellValue('A5', 'Kode');
				$SpreadSheet->getActiveSheet()->setCellValue('B5', 'Nama Produk');
				$SpreadSheet->getActiveSheet()->setCellValue('C5', 'Harga');
				$SpreadSheet->getActiveSheet()->setCellValue('D5', 'Harga Diskon');
				$SpreadSheet->getActiveSheet()->setCellValue('E5', 'Deskripsi');
				$SpreadSheet->getActiveSheet()->setCellValue('F5', 'Berat (gram)');
				$SpreadSheet->getActiveSheet()->setCellValue('G5', 'Stok');
				$SpreadSheet->getActiveSheet()->setCellValue('H5', 'URL Foto 1');
				$SpreadSheet->getActiveSheet()->setCellValue('I5', 'URL Foto 2');
				$SpreadSheet->getActiveSheet()->setCellValue('J5', 'URL Foto 3');
				$SpreadSheet->getActiveSheet()->setCellValue('K5', 'Kategori');
				$SpreadSheet->getActiveSheet()->setCellValue('L5', 'URL Foto 4');
				$SpreadSheet->getActiveSheet()->setCellValue('M5', 'URL Foto 5');
				$SpreadSheet->getActiveSheet()->setCellValue('N5', 'Nama Variasi');
				$StartingRow = 6;
			}elseif ($TypeOfFile == "QOHOnly"){
				$SpreadSheet->getActiveSheet()->setCellValue('A5', 'Kode');
				$SpreadSheet->getActiveSheet()->setCellValue('B5', 'Nama Produk');
				$SpreadSheet->getActiveSheet()->setCellValue('C5', 'Stok');
				$StartingRow = 6;
			}elseif ($TypeOfFile == "PricesOnly"){
				$SpreadSheet->getActiveSheet()->setCellValue('A1', 'Kode');
				$SpreadSheet->getActiveSheet()->setCellValue('B1', 'Nama Produk');
				$SpreadSheet->getActiveSheet()->setCellValue('C1', 'Harga');
				$SpreadSheet->getActiveSheet()->setCellValue('D1', 'Harga Diskon');
				$StartingRow = 2;
			}

			// Add data
			$i = $StartingRow;
			$SpreadSheet->setActiveSheetIndex(0);
			$ActiveSheet = $SpreadSheet->getActiveSheet();
			
			while ($MyRow = DB_fetch_array($Result)) {

				if (!ItemInLIst($MyRow['categoryid'], LIST_STOCK_CATEGORIES_OUTLET)){
					// we don't send discounted items to marketplaces
					
					$StockID = $MyRow['stockid'];

					$TextSizeIndonesian = CreateTextSize($MyRow['stockid'], "ID", true);
					$TextSizeEnglish = CreateTextSize($MyRow['stockid'], "EN", true);
					$TextSizeGrouping = CreateTextSize($MyRow['stockid'], "EN", false);
					
					if ($TextSizeGrouping != ""){
						$NamaVariant = "Ukuran";
					}else{
						$NamaVariant = "";
					}

					$Name = ItemMarketplaceName($MyRow['stockid'], $MyRow['description'], $MyRow['descriptiontranslation']);
					$Price = round($MyRow['price']);
					$PriceDiscount = '';
					$Description = trim($MyRow['longdescriptiontranslation']). " " . 
							$TextSizeIndonesian . " - "  . 
							trim($MyRow['longdescription']) . " " .
							$TextSizeEnglish;
					$Weight = $MyRow['grossweight'] * 1000; // webERP in KG, AdminCerdas in gr
					
					$QOH = ItemMarketplaceQOH($MyRow['stockid']);

					$PackagingImage = FALSE;
					list($Url_1, $PackagingImage) = ItemImagesURL($StockID,   1, $PackagingImage, $MyRow['klpackaging']);
					list($Url_2, $PackagingImage) = ItemImagesURL($StockID,   2, $PackagingImage, $MyRow['klpackaging']);
					list($Url_3, $PackagingImage) = ItemImagesURL($StockID,   3, $PackagingImage, $MyRow['klpackaging']);
					list($Url_4, $PackagingImage) = ItemImagesURL($StockID,   4, $PackagingImage, $MyRow['klpackaging']);
					list($Url_5, $PackagingImage) = ItemImagesURL($StockID, 999, $PackagingImage, $MyRow['klpackaging']);

					$Category = FindShopeeCategory($StockID, $Name, $Description);

					if ($TypeOfFile == "FullUpdate"){
						$ActiveSheet->setCellValue('A'.$i, $StockID);
						$ActiveSheet->setCellValue('B'.$i, $Name);
						$ActiveSheet->setCellValue('C'.$i, $Price);
						$ActiveSheet->setCellValue('D'.$i, $PriceDiscount);
						$ActiveSheet->setCellValue('E'.$i, $Description);
						$ActiveSheet->setCellValue('F'.$i, $Weight);
						$ActiveSheet->setCellValue('G'.$i, $QOH);
						$ActiveSheet->setCellValue('H'.$i, $Url_1);
						$ActiveSheet->setCellValue('I'.$i, $Url_2);
						$ActiveSheet->setCellValue('J'.$i, $Url_3);
						$ActiveSheet->setCellValue('K'.$i, $Category);
						$ActiveSheet->setCellValue('L'.$i, $Url_4);
						$ActiveSheet->setCellValue('M'.$i, $Url_5);
						$ActiveSheet->setCellValue('N'.$i, $NamaVariant);
					}elseif ($TypeOfFile == "QOHOnly"){
						$ActiveSheet->setCellValue('A'.$i, $StockID);
						$ActiveSheet->setCellValue('B'.$i, $Name);
						$ActiveSheet->setCellValue('C'.$i, $QOH);
					}elseif ($TypeOfFile == "PricesOnly"){
						$ActiveSheet->setCellValue('A'.$i, $StockID);
						$ActiveSheet->setCellValue('B'.$i, $Name);
						$ActiveSheet->setCellValue('C'.$i, $Price);
						$ActiveSheet->setCellValue('D'.$i, $PriceDiscount);
					}
					$i++;
				}
			}

			// Auto Size columns
			foreach(range('A','N') as $ColumnID) {
				$ActiveSheet->getColumnDimension($ColumnID)->setAutoSize(true);
			}
	
			// Set active sheet index to the first sheet, so Excel opens this as the first sheet
			$SpreadSheet->setActiveSheetIndex(0);

			if ($TypeOfFile == "FullUpdate"){
				$File ='AC-FULL-' .  $NameOfShop . '-' . Date('Y-m-d-H-i-s');
			}elseif ($TypeOfFile == "QOHOnly"){
				$File ='AC-QOH-' .  $NameOfShop . '-' . Date('Y-m-d-H-i-s');
			}elseif ($TypeOfFile == "PricesOnly"){
				$File ='AC-PRICE-' .  $NameOfShop . '-' . Date('Y-m-d-H-i-s');
			}

			// Redirect output to a client's web browser
			if ($_POST['Format'] == 'xlsx') {
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				$File .= '.xlsx';
			} else if ($_POST['Format'] == 'ods') {
				header('Content-Type: application/vnd.oasis.opendocument.spreadsheet');
				$File .= '.ods';
			}

			header('Content-Disposition: attachment;filename="' . $File . '"');
			header('Cache-Control: max-age=0');
			// If you're serving to IE 9, then the following may be needed
			header('Cache-Control: max-age=1');

			// If you're serving to IE over SSL, then the following may be needed
			header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
			header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
			header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
			header ('Pragma: public'); // HTTP/1.0

			if ($_POST['Format'] == 'xlsx') {
				$objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($SpreadSheet);
			} else if ($_POST['Format'] == 'ods') {
				$objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Ods($SpreadSheet);
			}
			$objWriter->save('php://output');

		}else{
			$Title = "Excel file for uploading products to Admin Cerdas";
			include('includes/header.php');
			prnMsg('No products to upload');
			include('includes/footer.php');
		}
	}
} // End of function submit()

//####DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_#####
function display($RootPath, $Theme) {
	$Title = _('Excel file for uploading products to Admin Cerdas');

	include('includes/header.php');

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
          <div>
			<br/>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . $Title. '" alt="" />' . ' ' . $Title . '
		</p>';

	echo '<fieldset>
			<legend>' . _('Admin Cerdas Export Options') . '</legend>';
	
	// Marketplace shop selection
	echo FieldToSelectOneBrand('TypeOfShop', $_POST['TypeOfShop'], _('Marketplace kind of shop'), '', '', 1, true, false);

	// ACI File type selection
	echo FieldToSelectFromThreeOptions('FullUpdate', _('Full Update'),
										'QOHOnly', _('QOH-Stock available Only'),
										'PricesOnly', _('Prices Only'),
										'TypeOfFile', $_POST['TypeOfFile'],	_('Type of ACI File'), '', '', 2, true, false);

	// Format selection
	echo FieldToSelectSpreadSheetFormat('Format', $_POST['Format'], _('File Format'), '', '', 3, true, false);

	echo '</fieldset>';

	echo OneButtonCenteredForm('submit', _('Export file to upload products to Admin Cerdas'));

	echo '</div>
         </form>';
	include('includes/footer.php');

} // End of function display()

?>
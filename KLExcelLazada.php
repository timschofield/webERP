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
include('includes/OpenCartGeneralFunctions.php');
include('includes/GetPrice.inc');

if (!isset($_POST['Format'])) {
    $_POST['Format'] = 'xlsx';
}

if (isset($_POST['submit'])) {
    submit($_POST['TypeOfShop']);
} else {
    display($RootPath, $Theme);
}

//####_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
function submit($TypeOfShop) {

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
	$Highlight01 = 'Highlight Sentence 02';
	$Highlight02 = 'Highlight Sentence 02';
	$Highlight03 = 'Highlight Sentence 03';

	$SourceLanguage="en";
	$TargetLanguage="id";
	
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
			$Brand = "Kapal-Laut. Your Essential Jewellery";
		}else{
			$NameOfShop = "Blink";
			$Brand = "Blink by Kapal-Laut";
		}

		$NameProductPrefix = $Brand . ' ';
			
		$Now = Date('Y-m-d H-i-s');
		
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
										 ->setTitle("Lazada " . $NameOfShop)
										 ->setSubject("Lazada " . $NameOfShop)
										 ->setDescription("Lazada " . $NameOfShop)
										 ->setKeywords("")
										 ->setCategory("");

			// Add title data
			$SpreadSheet->setActiveSheetIndex(0);
			$ActiveSheet = $SpreadSheet->getActiveSheet();
			$ActiveSheet->setTitle(("Lazada " . $NameOfShop));

			$ActiveSheet->setCellValue('A2', 'Group No');
			$ActiveSheet->setCellValue('B2', 'Catid');
			$ActiveSheet->setCellValue('C2', 'Nama Produk');
			$ActiveSheet->setCellValue('D2', 'Gambar produk1');
			$ActiveSheet->setCellValue('E2', 'Gambar produk2');
			$ActiveSheet->setCellValue('F2', 'Gambar produk3');
			$ActiveSheet->setCellValue('G2', 'Gambar produk4');
			$ActiveSheet->setCellValue('H2', 'Gambar produk5');
			$ActiveSheet->setCellValue('I2', 'Gambar produk6');
			$ActiveSheet->setCellValue('J2', 'Gambar produk7');
			$ActiveSheet->setCellValue('K2', 'Gambar produk8');
			$ActiveSheet->setCellValue('L2', 'Showcase_image1:1');
			$ActiveSheet->setCellValue('M2', 'originalLocalName');
			$ActiveSheet->setCellValue('N2', 'currencyCode');
			$ActiveSheet->setCellValue('O2', 'URL Video');
			$ActiveSheet->setCellValue('P2', 'Merek');
			$ActiveSheet->setCellValue('Q2', 'Bahan');
			$ActiveSheet->setCellValue('R2', 'Jenis Batu Perhiasan Utama');
			$ActiveSheet->setCellValue('S2', 'Model');
			$ActiveSheet->setCellValue('T2', 'Perhiasan Tubuh');
			$ActiveSheet->setCellValue('U2', 'Peralatan Alat Perhiasan');
			$ActiveSheet->setCellValue('V2', 'Long Description (Lorikeet)');
			$ActiveSheet->setCellValue('W2', 'Deskripsi Bahasa Inggris Panjang (Tidak Wajib)');
			$ActiveSheet->setCellValue('X2', 'Apa yang ada di dalam kotak');
			$ActiveSheet->setCellValue('Y2', 'Kebijakan Garansi');
			$ActiveSheet->setCellValue('Z2', 'Garansi');
			$ActiveSheet->setCellValue('AA2', 'Jenis Garansi');
			$ActiveSheet->setCellValue('AB2', 'Barang Berbahaya');
			$ActiveSheet->setCellValue('AC2', 'Warna');
			$ActiveSheet->setCellValue('AD2', 'props');
			$ActiveSheet->setCellValue('AE2', 'Gambar1');
			$ActiveSheet->setCellValue('AF2', 'Gambar2');
			$ActiveSheet->setCellValue('AG2', 'Gambar3');
			$ActiveSheet->setCellValue('AH2', 'Gambar4');
			$ActiveSheet->setCellValue('AI2', 'Gambar5');
			$ActiveSheet->setCellValue('AJ2', 'Gambar6');
			$ActiveSheet->setCellValue('AK2', 'Gambar7');
			$ActiveSheet->setCellValue('AL2', 'Gambar8');
			$ActiveSheet->setCellValue('AM2', 'Color');
			$ActiveSheet->setCellValue('AN2', 'Package Height');
			$ActiveSheet->setCellValue('AO2', 'Package Width');
			$ActiveSheet->setCellValue('AP2', 'Package Length');
			$ActiveSheet->setCellValue('AQ2', 'Package Weight');
			$ActiveSheet->setCellValue('AR2', 'Stok DS');
			$ActiveSheet->setCellValue('AS2', 'Harga');
			$ActiveSheet->setCellValue('AT2', 'SpecialPrice');
			$ActiveSheet->setCellValue('AU2', 'SpecialPrice Start');
			$ActiveSheet->setCellValue('AV2', 'SpecialPrice End');
			$ActiveSheet->setCellValue('AW2', 'Seller SKU');

			// Add data
			$i = 3;

			while ($MyRow = DB_fetch_array($Result)) {
				
				if (!ItemInLIst($MyRow['categoryid'], LIST_STOCK_CATEGORIES_OUTLET)){
					// we don't send discounted items to marketplaces
					
					$StockID = $MyRow['stockid'];

					$TextSizeIndonesian = CreateTextSize($StockID, "ID", true);
					$TextSizeEnglish = CreateTextSize($StockID, "EN", true);
					$TextSizeGrouping = CreateTextSize($StockID, "EN", false);
					
					if ($TextSizeGrouping != ""){
						$NamaVariant = "Ukuran";
					}else{
						$NamaVariant = "";
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
					$Material = FindLazadaMaterial($TypeOfShop, $Name);
					$Stone = FindLazadaStone($Name);
					$WhatsInTheBox = WhatsInTheBox($StockID);
					$Color = FindLazadaColor($Name);

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
					$Weight = $MyRow['grossweight'];

					$PackagingImage = FALSE;
					list($Url_1, $PackagingImage) = ItemImagesURL($StockID,   1, $PackagingImage, $MyRow['klpackaging']);
					list($Url_2, $PackagingImage) = ItemImagesURL($StockID,   2, $PackagingImage, $MyRow['klpackaging']);
					list($Url_3, $PackagingImage) = ItemImagesURL($StockID,   3, $PackagingImage, $MyRow['klpackaging']);
					list($Url_4, $PackagingImage) = ItemImagesURL($StockID,   4, $PackagingImage, $MyRow['klpackaging']);
					list($Url_5, $PackagingImage) = ItemImagesURL($StockID,   5, $PackagingImage, $MyRow['klpackaging']);
					list($Url_6, $PackagingImage) = ItemImagesURL($StockID,   6, $PackagingImage, $MyRow['klpackaging']);
					list($Url_7, $PackagingImage) = ItemImagesURL($StockID,   7, $PackagingImage, $MyRow['klpackaging']);
					list($Url_8, $PackagingImage) = ItemImagesURL($StockID, 999, $PackagingImage, $MyRow['klpackaging']);

					$ActiveSheet->setCellValue('A'.$i, $StockID);
					$ActiveSheet->setCellValue('B'.$i, $Category);
					$ActiveSheet->setCellValue('C'.$i, $Name);
					$ActiveSheet->setCellValue('D'.$i, $Url_1);
					$ActiveSheet->setCellValue('E'.$i, $Url_2);
					$ActiveSheet->setCellValue('F'.$i, $Url_3);
					$ActiveSheet->setCellValue('G'.$i, $Url_4);
					$ActiveSheet->setCellValue('H'.$i, $Url_5);
					$ActiveSheet->setCellValue('I'.$i, $Url_6);
					$ActiveSheet->setCellValue('J'.$i, $Url_7);
					$ActiveSheet->setCellValue('K'.$i, $Url_8);
					$ActiveSheet->setCellValue('L'.$i, '');
					$ActiveSheet->setCellValue('M'.$i, '');
					$ActiveSheet->setCellValue('N'.$i, $CurrencyCode);
					$ActiveSheet->setCellValue('O'.$i, '');
					$ActiveSheet->setCellValue('P'.$i, $Brand);
					$ActiveSheet->setCellValue('Q'.$i, $Material);
					$ActiveSheet->setCellValue('R'.$i, $Stone);
					$ActiveSheet->setCellValue('S'.$i, '');
					$ActiveSheet->setCellValue('T'.$i, $BodyJewellery);
					$ActiveSheet->setCellValue('U'.$i, '');
					$ActiveSheet->setCellValue('V'.$i, $MyRow['descriptiontranslation']);
					$ActiveSheet->setCellValue('W'.$i, $MyRow['description']);
					$ActiveSheet->setCellValue('X'.$i, $WhatsInTheBox);
					$ActiveSheet->setCellValue('Y'.$i, $Warranty);
					$ActiveSheet->setCellValue('Z'.$i, $Warranty);
					$ActiveSheet->setCellValue('AA'.$i, $Warranty);
					$ActiveSheet->setCellValue('AB'.$i, $BarangBerbahaya);
					$ActiveSheet->setCellValue('AC'.$i, $Color);
					$ActiveSheet->setCellValue('AD'.$i, '');
					$ActiveSheet->setCellValue('AE'.$i, '');
					$ActiveSheet->setCellValue('AF'.$i, '');
					$ActiveSheet->setCellValue('AG'.$i, '');
					$ActiveSheet->setCellValue('AH'.$i, '');
					$ActiveSheet->setCellValue('AI'.$i, '');
					$ActiveSheet->setCellValue('AJ'.$i, '');
					$ActiveSheet->setCellValue('AK'.$i, '');
					$ActiveSheet->setCellValue('AL'.$i, '');
					$ActiveSheet->setCellValue('AM'.$i, $Color);
					$ActiveSheet->setCellValue('AN'.$i, $Height);
					$ActiveSheet->setCellValue('AO'.$i, $Width);
					$ActiveSheet->setCellValue('AP'.$i, $Length);
					$ActiveSheet->setCellValue('AQ'.$i, $Weight);
					$ActiveSheet->setCellValue('AR'.$i, $QOH);
					$ActiveSheet->setCellValue('AS'.$i, $Price);
					$ActiveSheet->setCellValue('AT'.$i, '');
					$ActiveSheet->setCellValue('AU'.$i, '');
					$ActiveSheet->setCellValue('AV'.$i, '');
					$ActiveSheet->setCellValue('AW'.$i, $StockID);

					$i++;
				}
			}

			// Auto Size columns
			foreach(range('A','AW') as $ColumnID) {
				$ActiveSheet->getColumnDimension($ColumnID)->setAutoSize(true);
			}
	
			// Set active sheet index to the first sheet, so Excel opens this as the first sheet
			$SpreadSheet->setActiveSheetIndex(0);

			// Redirect output to a client's web browser 
			if ($_POST['Format'] == 'xlsx') {
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				$File ='LAZADA-' .  $NameOfShop . '-' . Date('Y-m-d-H-i-s'). '.xlsx';
			} else if ($_POST['Format'] == 'ods') {
				header('Content-Type: application/vnd.oasis.opendocument.spreadsheet');
				$File ='LAZADA-' .  $NameOfShop . '-' . Date('Y-m-d-H-i-s'). '.ods';
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
			$Title = "Excel file for uploading products to Lazada";
			include('includes/header.php');
			prnMsg('No products to upload');
			include('includes/footer.php');
		}
	} 
} // End of function submit()


function display($RootPath, $Theme)  //####DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_#####
{
// Display form fields. This function is called the first time
// the page is called.
	$Title = _('Excel file for uploading products to Lazada');

	include('includes/header.php');

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
          <div>
			<br/>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . $Title. '" alt="" />' . ' ' . $Title . '
		</p>';

	echo '<fieldset>
			<legend>' . _('Lazada Shop Selection') . '</legend>';
			
	echo FieldToSelectOneBrand('TypeOfShop', '', _('Lazada shop'), '', '', '', true, true);
	echo FieldToSelectSpreadSheetFormat('Format', $_POST['Format'], _('File Format'));
	
	echo '</fieldset>';

	echo OneButtonCenteredForm('submit', _('Export file to upload products to Lazada'));

	echo '</div>
         </form>';
	include('includes/footer.php');
} 

?>
<?php
require_once ('Classes/PHPExcel.php');

include('includes/session.php');
include('includes/SQL_CommonFunctions.inc');
include('includes/KLDefines.php');
include('includes/KLBoards.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLMarketplaceFunctions.php');
include('includes/OpenCartGeneralFunctions.php');
include('includes/GetPrice.inc');


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
		
		$Now = Date('Y-m-d H-i-s');

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
					AND prices.startdate <= '". $Now. "' 
					AND prices.enddate >= '". $Now. "'
				ORDER BY stockmaster.stockid";
		$Result = DB_query($SQL);
		if (DB_num_rows($Result) != 0){
			
			// Set value binder
			PHPExcel_Cell::setValueBinder( new PHPExcel_Cell_AdvancedValueBinder() );
		
			// Create new PHPExcel object
			$objPHPExcel = new PHPExcel();

			// Set document properties
			$objPHPExcel->getProperties()->setCreator("webERP")
										 ->setLastModifiedBy("webERP")
										 ->setTitle("Admin Cerdas " . $NameOfShop)
										 ->setSubject("Admin Cerdas " . $NameOfShop)
										 ->setDescription("Admin Cerdas " . $NameOfShop)
										 ->setKeywords("")
										 ->setCategory("");

			// Add title data
			$objPHPExcel->setActiveSheetIndex(0);
			$objPHPExcel->getActiveSheet()->setTitle(("AC " . $NameOfShop));
			
			if ($TypeOfFile == "FullUpdate"){
				$objPHPExcel->getActiveSheet()->setCellValue('A5', 'Kode');
				$objPHPExcel->getActiveSheet()->setCellValue('B5', 'Nama Produk');
				$objPHPExcel->getActiveSheet()->setCellValue('C5', 'Harga');
				$objPHPExcel->getActiveSheet()->setCellValue('D5', 'Harga Diskon');
				$objPHPExcel->getActiveSheet()->setCellValue('E5', 'Deskripsi');
				$objPHPExcel->getActiveSheet()->setCellValue('F5', 'Berat (gram)');
				$objPHPExcel->getActiveSheet()->setCellValue('G5', 'Stok');
				$objPHPExcel->getActiveSheet()->setCellValue('H5', 'URL Foto 1');
				$objPHPExcel->getActiveSheet()->setCellValue('I5', 'URL Foto 2');
				$objPHPExcel->getActiveSheet()->setCellValue('J5', 'URL Foto 3');
				$objPHPExcel->getActiveSheet()->setCellValue('K5', 'Kategori');
				$objPHPExcel->getActiveSheet()->setCellValue('L5', 'URL Foto 4');
				$objPHPExcel->getActiveSheet()->setCellValue('M5', 'URL Foto 5');
				$objPHPExcel->getActiveSheet()->setCellValue('N5', 'Nama Variasi');
				$StartingRow = 6;
			}elseif ($TypeOfFile == "QOHOnly"){
				$objPHPExcel->getActiveSheet()->setCellValue('A5', 'Kode');
				$objPHPExcel->getActiveSheet()->setCellValue('B5', 'Nama Produk');
				$objPHPExcel->getActiveSheet()->setCellValue('C5', 'Stok');
				$StartingRow = 6;
			}elseif ($TypeOfFile == "PricesOnly"){
				$objPHPExcel->getActiveSheet()->setCellValue('A1', 'Kode');
				$objPHPExcel->getActiveSheet()->setCellValue('B1', 'Nama Produk');
				$objPHPExcel->getActiveSheet()->setCellValue('C1', 'Harga');
				$objPHPExcel->getActiveSheet()->setCellValue('D1', 'Harga Diskon');
				$StartingRow = 2;
			}

			// Add data
			$i = $StartingRow;
			$objPHPExcel->setActiveSheetIndex(0);
			$ActiveSheet = $objPHPExcel->getActiveSheet();
			
			while ($MyRow = DB_fetch_array($Result)) {

				if (!ItemInLIst($MyRow['categoryid'], LIST_STOCK_CATEGORIES_OUTLET)){
					// we don't send discounted items to marketplaces
					
					$StockId = $MyRow['stockid'];

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
					list($Url_1, $PackagingImage) = ItemImagesURL($StockId,   1, $PackagingImage, $MyRow['klpackaging']);
					list($Url_2, $PackagingImage) = ItemImagesURL($StockId,   2, $PackagingImage, $MyRow['klpackaging']);
					list($Url_3, $PackagingImage) = ItemImagesURL($StockId,   3, $PackagingImage, $MyRow['klpackaging']);
					list($Url_4, $PackagingImage) = ItemImagesURL($StockId,   4, $PackagingImage, $MyRow['klpackaging']);
					list($Url_5, $PackagingImage) = ItemImagesURL($StockId, 999, $PackagingImage, $MyRow['klpackaging']);

					$Category = FindShopeeCategory($StockId, $Name, $Description);

					if ($TypeOfFile == "FullUpdate"){
						$ActiveSheet->setCellValue('A'.$i, $StockId);
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
						$ActiveSheet->setCellValue('A'.$i, $StockId);
						$ActiveSheet->setCellValue('B'.$i, $Name);
						$ActiveSheet->setCellValue('C'.$i, $QOH);
					}elseif ($TypeOfFile == "PricesOnly"){
						$ActiveSheet->setCellValue('A'.$i, $StockId);
						$ActiveSheet->setCellValue('B'.$i, $Name);
						$ActiveSheet->setCellValue('C'.$i, $Price);
						$ActiveSheet->setCellValue('D'.$i, $PriceDiscount);
					}
					$i++;
				}
			}

			// Auto Size columns
			foreach(range('A','N') as $columnID) {
				$ActiveSheet->getColumnDimension($columnID)->setAutoSize(true);
			}
	
			// Set active sheet index to the first sheet, so Excel opens this as the first sheet
			$objPHPExcel->setActiveSheetIndex(0);

			// Redirect output to a client’s web browser (Excel2007)
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			if ($TypeOfFile == "FullUpdate"){
				$File ='AC-FULL-' .  $NameOfShop . '-' . Date('Y-m-d-H-i-s'). '.xlsx';
			}elseif ($TypeOfFile == "QOHOnly"){
				$File ='AC-QOH-' .  $NameOfShop . '-' . Date('Y-m-d-H-i-s'). '.xlsx';
			}elseif ($TypeOfFile == "PricesOnly"){
				$File ='AC-PRICE-' .  $NameOfShop . '-' . Date('Y-m-d-H-i-s'). '.xlsx';
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

			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
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
// Display form fields. This function is called the first time
// the page is called.
	$Title = _('Excel file for uploading products to Admin Cerdas');

	include('includes/header.php');

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
          <div>
			<br/>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . $Title. '" alt="" />' . ' ' . $Title . '
		</p>';

	echo '<table class="selection">
			<tr><td>'. _('Marketplace kind of shop').':</td>
			<td><select name="TypeOfShop" onchange="submit();"> ';
	$SQL = "SELECT manufacturers.manufacturers_id, 
					manufacturers_name 
			FROM manufacturers 
			ORDER BY manufacturers_name";
	$LocResult = DB_query($SQL);
	while ($MyRow=DB_fetch_array($LocResult)){
		 echo '<option value="' . $MyRow['manufacturers_id'] . '">' . $MyRow['manufacturers_name'] . '</option>';
	}

	echo '<tr>
			<td>' . _('Type of ACI File') . ':</td>
			<td><select name="TypeOfFile">
				<option selected="selected" value="FullUpdate">' . _('Full Update') . '</option>
				<option value="QOHOnly">' . _('QOH-Stock available Only') . '</option>
				<option value="PricesOnly">' . _('Prices Only') . '</option>
			</select></td>
		</tr>';

	echo '</table>
		<table>';

	echo '<tr><td>&nbsp;</td></tr>
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="submit" value="' . _('Create Excel file to upload products to Admin Cerdas') . '" /></td>
		</tr>
		</table>
		<br />';
	echo '</div>
         </form>';
	include('includes/footer.php');

} // End of function display()

?>
<?php
require_once ('Classes/PHPExcel.php');

include('includes/session.php');
include('includes/SQL_CommonFunctions.inc');
include('includes/KLDefines.php');
include('includes/KLBoards.php');
include('includes/KLGeneralFunctions.php');
include('includes/OpenCartGeneralFunctions.php');
include('includes/GetPrice.inc');


if (isset($_POST['submit'])) {
    submit($db, $_POST['TypeOfShop']);
} else {
    display($db);
}

//####_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
function submit(&$db, $TypeOfShop) {

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
			$SalesCategory = 94;
		}else{
			$NameOfShop = "Blink";
			$SalesCategory = 95;
		}
		
		$Now = Date('Y-m-d H-i-s');

		$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.longdescription,
						stockmaster.grossweight,
						stockmaster.categoryid,
						prices.price
				FROM stockmaster, salescatprod, prices
				WHERE stockmaster.stockid = salescatprod.stockid
					AND stockmaster.stockid = prices.stockid
					AND stockmaster.discontinued = 0
					AND salescatprod.salescatid = " . $SalesCategory . "
					AND prices.typeabbrev = '" . RETAIL_PRICE_LIST . "'
					AND prices.currabrev = '". CURRENCY_CODE ."'
					AND prices.startdate <= '". $Now. "' 
					AND (prices.enddate >= '". $Now. "' OR prices.enddate = '0000-00-00')
				ORDER BY stockmaster.stockid";
		$result = DB_query($SQL);
		if (DB_num_rows($result) != 0){
			
			// Set value binder
			PHPExcel_Cell::setValueBinder( new PHPExcel_Cell_AdvancedValueBinder() );
		
			// Create new PHPExcel object
			$objPHPExcel = new PHPExcel();

			// Set document properties
			$objPHPExcel->getProperties()->setCreator("webERP")
										 ->setLastModifiedBy("webERP")
										 ->setTitle("Admin Cerdas" . $TypeOfShop)
										 ->setSubject("Admin Cerdas" . $TypeOfShop)
										 ->setDescription("Admin Cerdas" . $TypeOfShop)
										 ->setKeywords("")
										 ->setCategory("");

			// Add title data
			$objPHPExcel->setActiveSheetIndex(0);
			$objPHPExcel->getActiveSheet()->setTitle(("Admin Cerdas" . $TypeOfShop));

			$objPHPExcel->getActiveSheet()->setCellValue('A1', 'Kode');
			$objPHPExcel->getActiveSheet()->setCellValue('B1', 'Nama Produk');
			$objPHPExcel->getActiveSheet()->setCellValue('C1', 'Harga');
			$objPHPExcel->getActiveSheet()->setCellValue('D1', 'Harga Diskon');
			$objPHPExcel->getActiveSheet()->setCellValue('E1', 'Deskripsi');
			$objPHPExcel->getActiveSheet()->setCellValue('F1', 'Berat (gram)');
			$objPHPExcel->getActiveSheet()->setCellValue('G1', 'Stok');
			$objPHPExcel->getActiveSheet()->setCellValue('H1', 'URL Foto 1');
			$objPHPExcel->getActiveSheet()->setCellValue('I1', 'URL Foto 2');
			$objPHPExcel->getActiveSheet()->setCellValue('J1', 'URL Foto 3');
			$objPHPExcel->getActiveSheet()->setCellValue('K1', 'Kategori');

			// Add data
			$StartingRow = 2;
			$i = $StartingRow;
			$objPHPExcel->setActiveSheetIndex(0);
			$ActiveSheet = $objPHPExcel->getActiveSheet();
			
			while ($myrow = DB_fetch_array($result)) {
				
				$StockId = $myrow['stockid'];
				$Name = $myrow['description'];
				$Price = round($myrow['price']);
				$PriceDiscount = '';
				$Description = $myrow['longdescription'];
				$Weight = $myrow['grossweight'] * 1000; // webERP in KG, AdminCerdas in gr
				
				// if we have more than ADMINCERDAS_MINIMUM_STOCK_TO_UPDATE we "cap" it, 
				// so we don't spend update credits updating QOH when it is not important for us
				$QOH = 	min(GetOnlineQOH($myrow['stockid'], $db), ADMINCERDAS_MINIMUM_STOCK_TO_UPDATE);

				$Url_1 = PATH_OPENCART_BASE. "/" . PATH_OPENCART_IMAGES . $myrow['stockid'].'.jpg';

				if (file_exists($_SESSION['part_pics_dir'] . '/' . $myrow['stockid'].'.1.jpg')){
					$Url_2 = PATH_OPENCART_BASE. "/" . PATH_OPENCART_IMAGES . $myrow['stockid'].'.1.jpg';
				}else{
					$Url_2 =  "";
				}

				if(file_exists($_SESSION['part_pics_dir'] . '/' . $myrow['stockid'].'.2.jpg')) {
					$Url_3 = PATH_OPENCART_BASE. "/" . PATH_OPENCART_IMAGES . $myrow['stockid'].'.2.jpg';
				}else{
					$Url_3 =  "";
				}

				$Category = "";
 
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

				$i++;
			}

			// Auto Size columns
			foreach(range('A','K') as $columnID) {
				$ActiveSheet->getColumnDimension($columnID)->setAutoSize(true);
			}
	
			// Set active sheet index to the first sheet, so Excel opens this as the first sheet
			$objPHPExcel->setActiveSheetIndex(0);

			// Redirect output to a client’s web browser (Excel2007)
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			$File ='AdminCerdas-' .  $TypeOfShop . '-' . Date('Y-m-d-H-i-s'). '.xlsx';
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
			$Title = "Excel file for uplaoding products to Admin Cerdas";
			include('includes/header.php');
			prnMsg('No products to upload');
			include('includes/footer.php');
		}
	}
} // End of function submit()


function display(&$db)  //####DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_#####
{
// Display form fields. This function is called the first time
// the page is called.
	$Title = _('Excel file for uplaoding products to Admin Cerdas');

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
	while ($myrow=DB_fetch_array($LocResult)){
		 echo '<option value="' . $myrow['manufacturers_id'] . '">' . $myrow['manufacturers_name'] . '</option>';
	}

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
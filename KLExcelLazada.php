<?php

include('includes/session.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/KLDefines.php');
include('includes/WeberpOpenCartDefines.php');
include('includes/OpenCartGeneralFunctions.php');
include('includes/OpenCartConnectDB.php');

require_once ('Classes/PHPExcel.php');

if (!isset($_POST['FromPrice'])){
	$_POST['FromPrice'] = 0;
}
if (!isset($_POST['ToPrice'])){
	$_POST['ToPrice'] = 999999999;
}
if (!isset($_POST['QOHMinimal'])){
	$_POST['QOHMinimal'] = 10;
}
if (!isset($_POST['PopularItems'])){
	$_POST['PopularItems'] = 1000;
}
if (isset($_POST['submit'])) {
    submit($db, $db_oc, $oc_tableprefix, $_POST['FromPrice'], $_POST['ToPrice'], $_POST['QOHMinimal'], $_POST['PopularItems']);
} else {
    display($db);
}

//####_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
function submit(&$db, &$db_oc, $oc_tableprefix, $FromPrice, $ToPrice, $QOHMinimal, $PopularItems) {

	//initialise no input errors
	$InputError = 0;

	//first off validate inputs sensible

	if ($_POST['FromPrice'] < 0) {
		$InputError = 1;
		prnMsg(_('From Price has to be greater than 0'),'error');
	}
	if ($_POST['ToPrice'] < 0) {
		$InputError = 1;
		prnMsg(_('To Price has to be greater than 0'),'error');
	}
	if ($_POST['ToPrice'] < $_POST['FromPrice']) {
		$InputError = 1;
		prnMsg(_('To Price has to be greater than From Price'),'error');
	}
	if ($QOHMinimal < 1) {
		$InputError = 1;
		prnMsg(_('QOH Minimal has to be 1 or higher'),'error');
	}
	if ($PopularItems < 1) {
		$InputError = 1;
		prnMsg(_('Number of Items has to be 1 or higher'),'error');
	}

	if ($InputError == 0){
		$FromPrice = $_POST['FromPrice'];
		$ToPrice = $_POST['ToPrice'];
		
		$sql = "SELECT 	" . $oc_tableprefix . "product.product_id,
						" . $oc_tableprefix . "product_description.name,
						" . $oc_tableprefix . "product_description.description,
						" . $oc_tableprefix . "product.model,
						" . $oc_tableprefix . "product.sku,
						" . $oc_tableprefix . "product.weight,
						" . $oc_tableprefix . "product.length,
						" . $oc_tableprefix . "product.width,
						" . $oc_tableprefix . "product.height,
						" . $oc_tableprefix . "product.length_class_id,
						" . $oc_tableprefix . "product.image,
						" . $oc_tableprefix . "product.google_product_category,
						" . $oc_tableprefix . "product.gender,
						" . $oc_tableprefix . "product.agegroup,
						" . $oc_tableprefix . "product.price,
						" . $oc_tableprefix . "product.quantity
				FROM " . $oc_tableprefix . "product,
						" . $oc_tableprefix . "product_description
				WHERE   " . $oc_tableprefix . "product.product_id = " . $oc_tableprefix . "product_description.product_id
					AND " . $oc_tableprefix . "product.status = 1
					AND " . $oc_tableprefix . "product.price >= '" . $FromPrice . "'
					AND " . $oc_tableprefix . "product.price <= '" . $ToPrice . "'
					AND " . $oc_tableprefix . "product.quantity >= '" . $QOHMinimal . "'
				ORDER BY " . $oc_tableprefix . "product.viewed DESC
				LIMIT 0," . $PopularItems . "";
//prnMsg($sql);					
		$ErrMsg = _('The SQL to find the OpenCart Products to export to Lazada');
		$result = DB_query_oc($sql,$ErrMsg);
		if (DB_num_rows($result) != 0){

			// Create new PHPExcel object
			$objPHPExcel = new PHPExcel();

			// Set document properties
			$objPHPExcel->getProperties()->setCreator("webERP")
										 ->setLastModifiedBy("webERP")
										 ->setTitle("Lazada Products")
										 ->setSubject("Lazada Products")
										 ->setDescription("Lazada Products")
										 ->setKeywords("")
										 ->setCategory("");
		
			// Add title data
			$objPHPExcel->setActiveSheetIndex(0);
			$objPHPExcel->getActiveSheet()->setCellValue('A1', 'No.');
			$objPHPExcel->getActiveSheet()->setCellValue('B1', 'Nama Produk');
			$objPHPExcel->getActiveSheet()->setCellValue('C1', 'Brand / Merk produk');
			$objPHPExcel->getActiveSheet()->setCellValue('D1', 'Model');
			$objPHPExcel->getActiveSheet()->setCellValue('E1', 'Warna');
			$objPHPExcel->getActiveSheet()->setCellValue('F1', 'Harga');
			$objPHPExcel->getActiveSheet()->setCellValue('G1', 'Kode SKU/produk');
			$objPHPExcel->getActiveSheet()->setCellValue('H1', 'Variasi ukuran');
			$objPHPExcel->getActiveSheet()->setCellValue('I1', 'Jumlah');
			$objPHPExcel->getActiveSheet()->setCellValue('J1', 'Deskripsi produk 1');
			$objPHPExcel->getActiveSheet()->setCellValue('K1', 'Highlight');
			$objPHPExcel->getActiveSheet()->setCellValue('N1', 'isi Kemasan');
			$objPHPExcel->getActiveSheet()->setCellValue('O1', 'Lama pengiriman (Minimal)');
			$objPHPExcel->getActiveSheet()->setCellValue('P1', 'Lama pengiriman (Maximal)');
			$objPHPExcel->getActiveSheet()->setCellValue('Q1', 'Ukuran Produk');
			$objPHPExcel->getActiveSheet()->setCellValue('R1', 'berat produk kg');
			$objPHPExcel->getActiveSheet()->setCellValue('S1', 'panjang kemasan');
			$objPHPExcel->getActiveSheet()->setCellValue('T1', 'lebar kemasan');
			$objPHPExcel->getActiveSheet()->setCellValue('U1', 'tinggi kemasan');
			$objPHPExcel->getActiveSheet()->setCellValue('V1', 'berat kemasan');
			$objPHPExcel->getActiveSheet()->setCellValue('W1', 'garansi produk');
			$objPHPExcel->getActiveSheet()->setCellValue('X1', 'bahan baku produk');
			$objPHPExcel->getActiveSheet()->setCellValue('Y1', 'Negara tempat produksi');
			$objPHPExcel->getActiveSheet()->setCellValue('Z1', 'URL Gambar');
			$objPHPExcel->getActiveSheet()->setCellValue('AA1', 'Google Category');
			$objPHPExcel->getActiveSheet()->setCellValue('AB1', 'Google Gender');
			$objPHPExcel->getActiveSheet()->setCellValue('AC1', 'Google Age Group');

			// Add data
			$i = 2;
			$Brand = "Kapal-Laut Your Essential Jewellery";
			$ShippingTimeMinimal = 3;
			$ShippingTimeMaximal = 6;
			$Dimensions = locale_number_format($myrow['length'],2) . ' x ' . 
							locale_number_format($myrow['width'],2) . ' x ' . 
							locale_number_format($myrow['height'],2);
			$Weight = locale_number_format($myrow['weight'],2);
			$Warranty = "6 months limited warranty. Check http://www.kapal-laut.com/Warranty-Conditions for details";
			$Country = "Indonesia";
			$ImagePath = "http://www.kapal-laut.com/image/";
			
			while ($myrow = DB_fetch_array($result)) {
				$objPHPExcel->setActiveSheetIndex(0);
				$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $i-1);
				$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $myrow['name']);
				$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $Brand);
				$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, $myrow['model']);
				$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, '');
				$objPHPExcel->getActiveSheet()->setCellValue('F'.$i, $myrow['price']);
				$objPHPExcel->getActiveSheet()->setCellValue('G'.$i, $myrow['model']);
				$objPHPExcel->getActiveSheet()->setCellValue('H'.$i, '');
				$objPHPExcel->getActiveSheet()->setCellValue('I'.$i, $myrow['quantity']);
				$objPHPExcel->getActiveSheet()->setCellValue('J'.$i, $myrow['description']);
				$objPHPExcel->getActiveSheet()->setCellValue('K'.$i, '');
				$objPHPExcel->getActiveSheet()->setCellValue('N'.$i, '');
				$objPHPExcel->getActiveSheet()->setCellValue('O'.$i, $ShippingTimeMinimal);
				$objPHPExcel->getActiveSheet()->setCellValue('P'.$i, $ShippingTimeMaximal);
				$objPHPExcel->getActiveSheet()->setCellValue('Q'.$i, $Dimensions);
				$objPHPExcel->getActiveSheet()->setCellValue('R'.$i, $Weight);
				$objPHPExcel->getActiveSheet()->setCellValue('S'.$i, '');
				$objPHPExcel->getActiveSheet()->setCellValue('T'.$i, '');
				$objPHPExcel->getActiveSheet()->setCellValue('U'.$i, '');
				$objPHPExcel->getActiveSheet()->setCellValue('V'.$i, $Weight);
				$objPHPExcel->getActiveSheet()->setCellValue('W'.$i, $Warranty);
				$objPHPExcel->getActiveSheet()->setCellValue('X'.$i, '');
				$objPHPExcel->getActiveSheet()->setCellValue('Y'.$i, $Country);
				$objPHPExcel->getActiveSheet()->setCellValue('Z'.$i, $ImagePath . $myrow['image']);
				$objPHPExcel->getActiveSheet()->setCellValue('AA'.$i, $myrow['google_product_category']);
				$objPHPExcel->getActiveSheet()->setCellValue('AB'.$i, $myrow['gender']);
				$objPHPExcel->getActiveSheet()->setCellValue('AC'.$i, $myrow['agegroup']);
				$i++;
			}
			
			// Freeze panes
			$objPHPExcel->getActiveSheet()->freezePane('A2');
		
			// Auto Size columns
			foreach(range('A','D') as $columnID) {
				$objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
					->setAutoSize(true);
			}
			
			// Rename worksheet
			$objPHPExcel->getActiveSheet()->setTitle('KL-Lazada');

			// Set active sheet index to the first sheet, so Excel opens this as the first sheet
			$objPHPExcel->setActiveSheetIndex(0);

			// Redirect output to a client’s web browser (Excel2007)
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			$File = 'KL-Products-Lazada-' . Date('Y-m-d'). '.xlsx';
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
			prnMsg('No Products selected for Lazada');
		}
	}
} // End of function submit()


function display(&$db)  //####DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_#####
{
// Display form fields. This function is called the first time
// the page is called.
	$Title = _('Excel file for Lazada');
	include('includes/header.inc');

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
          <div>
			<br/>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Excel file to upload products to Lazada') . '" alt="" />' . ' ' . _('Excel file to upload products to Lazada') . '
		</p>';

	echo '<table>';

	echo '<tr>
			<td>' . _('Price Range') . ':</td>
			<td><input type="text" name="FromPrice" size="10" maxlength="20" value="' . $_POST['FromPrice'] . '" />' 
			. ' To ' . ': <input type="text" name="ToPrice" size="10" maxlength="20" value="' . $_POST['ToPrice'] . '" />'
			. ' IDR' . '</td>
		</tr>';
	echo '<tr>
			<td>' . _('QOH Minimal') . ':</td>
			<td><input type="text" name="QOHMinimal" size="5" maxlength="5" value="' . $_POST['QOHMinimal'] . '" /></td>
		</tr>';
	echo '<tr>
			<td>' . _('# Items') . ':</td>
			<td><input type="text" name="PopularItems" size="5" maxlength="5" value="' . $_POST['PopularItems'] . '" />'
			. ' Website Most Popular Items' . ':</td>
		</tr>';

	echo '</table>
		<table>';

	echo '<tr><td>&nbsp;</td></tr>
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="submit" value="' . _('Create Excel File for Lazada') . '" /></td>
		</tr>
		</table>
		<br />';
	echo '</div>
         </form>';
	include('includes/footer.inc');

} // End of function display()

?>
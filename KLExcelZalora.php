<?php

include('includes/session.php');
include('includes/SQL_CommonFunctions.inc');
include('includes/KLDefines.php');
include('includes/OpenCartGeneralFunctions.php');
include('includes/OpenCartConnectDB.php');

require_once ('Classes/PHPExcel.php');

if (!isset($_POST['FromPrice'])){
	$_POST['FromPrice'] = 0;
}
if (!isset($_POST['ToPrice'])){
	$_POST['ToPrice'] = 999999999;
}

if (isset($_POST['submit'])) {
    submit($db, $db_oc, $_POST['FromPrice'], $_POST['ToPrice']);
} else {
    display($db);
}

//####_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
function submit(&$db, &$db_oc, $FromPrice, $ToPrice) {

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

	if ($InputError == 0){
		$FromPrice = $_POST['FromPrice'];
		$ToPrice = $_POST['ToPrice'];
		
		$sql = "SELECT 	oc_product.product_id,
						oc_product_description.name,
						oc_product.model,
						oc_product.sku,
						oc_product.price,
						oc_product.quantity
				FROM oc_product,
						oc_product_description
				WHERE   oc_product.product_id = oc_product_description.product_id
					AND oc_product.status = 1
					AND oc_product.price >= '" . $FromPrice . "'
					AND oc_product.price <= '" . $ToPrice . "'
				ORDER BY oc_product.model";
		
		$ErrMsg = _('The SQL to find the OpenCart Products to export to Zalora');
		$result = DB_query_oc($sql,$ErrMsg);
		if (DB_num_rows($result) != 0){

			// Create new PHPExcel object
			$objPHPExcel = new PHPExcel();

			// Set document properties
			$objPHPExcel->getProperties()->setCreator("webERP")
										 ->setLastModifiedBy("webERP")
										 ->setTitle("Zalora Products")
										 ->setSubject("Zalora Products")
										 ->setDescription("Zalora Products")
										 ->setKeywords("")
										 ->setCategory("");
		
			// Add title data
			$objPHPExcel->setActiveSheetIndex(0);
			$objPHPExcel->getActiveSheet()->setCellValue('A1', 'Product Name');
			$objPHPExcel->getActiveSheet()->setCellValue('B1', 'Main Category');
			$objPHPExcel->getActiveSheet()->setCellValue('C1', 'Brand');
			$objPHPExcel->getActiveSheet()->setCellValue('D1', 'Category');
			$objPHPExcel->getActiveSheet()->setCellValue('E1', 'Colour');
			$objPHPExcel->getActiveSheet()->setCellValue('F1', 'Quantity');

			// Add data
			$i = 2;
			while ($myrow = DB_fetch_array($result)) {
				$objPHPExcel->setActiveSheetIndex(0);
				$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $myrow['name']);
/*				$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, '');
				$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, '');
				$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, '');
				$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, '');
*/				$objPHPExcel->getActiveSheet()->setCellValue('F'.$i, $myrow['quantity']);
				$i++;
			}
			
			// Freeze panes
			$objPHPExcel->getActiveSheet()->freezePane('A2');
		
			// Auto Size columns
			foreach(range('A','F') as $columnID) {
				$objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
					->setAutoSize(true);
			}
			
			// Rename worksheet
			$objPHPExcel->getActiveSheet()->setTitle('GL Transactions');

			// Set active sheet index to the first sheet, so Excel opens this as the first sheet
			$objPHPExcel->setActiveSheetIndex(0);

			// Redirect output to a client’s web browser (Excel2007)
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			$File = 'KL-Products-Zalora-' . Date('Y-m-d'). '.xlsx';
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
			prnMsg('No Products selected for Zalora');
		}
	}
} // End of function submit()


function display(&$db)  //####DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_#####
{
// Display form fields. This function is called the first time
// the page is called.
	$Title = _('Excel file for Zalora');
	include('includes/header.php');

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
          <div>
			<br/>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Excel file to upload products to Zalora') . '" alt="" />' . ' ' . _('Excel file to upload products to Zalora') . '
		</p>';

	echo '<table>';

	echo '<tr>
			<td>' . _('Price Range') . ':</td>
			<td><input type="text" name="FromPrice" size="20" maxlength="20" value="' . $_POST['FromPrice'] . '" /></td>
			<td>' . _('To') . ':</td>
			<td><input type="text" name="ToPrice" size="20" maxlength="20" value="' . $_POST['ToPrice'] . '" /></td>
			<td>' . _('IDR') . ':</td>
		</tr>';

	echo '</table>
		<table>';

	echo '<tr><td>&nbsp;</td></tr>
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="submit" value="' . _('Create Excel File for Zalora') . '" /></td>
		</tr>
		</table>
		<br />';
	echo '</div>
         </form>';
	include('includes/footer.php');

} // End of function display()

?>
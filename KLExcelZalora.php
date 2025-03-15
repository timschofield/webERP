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
include('includes/OCOpenCartGeneralFunctions.php');
include('includes/OCOpenCartConnectDB.php');

if (!isset($_POST['FromPrice'])){
	$_POST['FromPrice'] = 0;
}

if (!isset($_POST['ToPrice'])){
	$_POST['ToPrice'] = 999999999;
}

if (!isset($_POST['Format'])){
	$_POST['Format'] = 'xlsx';
}

if (isset($_POST['submit'])) {
    submit($_POST['FromPrice'], $_POST['ToPrice']);
} else {
    display($RootPath, $Theme);
}

//####_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
function submit($FromPrice, $ToPrice) {

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
		
		$SQL = "SELECT 	oc_product.product_id,
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
		$Result = DB_query_oc($SQL,$ErrMsg);
		if (DB_num_rows($Result) != 0){

			// Create new PHPSpreadsheet object
			$SpreadSheet = new Spreadsheet();

			// Set document properties
			$SpreadSheet->getProperties()->setCreator("webERP")
										 ->setLastModifiedBy("webERP")
										 ->setTitle("Zalora Products")
										 ->setSubject("Zalora Products")
										 ->setDescription("Zalora Products")
										 ->setKeywords("")
										 ->setCategory("");
		
			// Add title data
			$SpreadSheet->setActiveSheetIndex(0);
			$SpreadSheet->getActiveSheet()->setCellValue('A1', 'Product Name');
			$SpreadSheet->getActiveSheet()->setCellValue('B1', 'Main Category');
			$SpreadSheet->getActiveSheet()->setCellValue('C1', 'Brand');
			$SpreadSheet->getActiveSheet()->setCellValue('D1', 'Category');
			$SpreadSheet->getActiveSheet()->setCellValue('E1', 'Colour');
			$SpreadSheet->getActiveSheet()->setCellValue('F1', 'Quantity');

			// Add data
			$i = 2;
			while ($MyRow = DB_fetch_array($Result)) {
				$SpreadSheet->setActiveSheetIndex(0);
				$SpreadSheet->getActiveSheet()->setCellValue('A'.$i, $MyRow['name']);
/*				$SpreadSheet->getActiveSheet()->setCellValue('B'.$i, '');
				$SpreadSheet->getActiveSheet()->setCellValue('C'.$i, '');
				$SpreadSheet->getActiveSheet()->setCellValue('D'.$i, '');
				$SpreadSheet->getActiveSheet()->setCellValue('E'.$i, '');
*/				$SpreadSheet->getActiveSheet()->setCellValue('F'.$i, $MyRow['quantity']);
				$i++;
			}
			
			// Freeze panes
			$SpreadSheet->getActiveSheet()->freezePane('A2');
		
			// Auto Size columns
			foreach(range('A','F') as $ColumnID) {
				$SpreadSheet->getActiveSheet()->getColumnDimension($ColumnID)
					->setAutoSize(true);
			}
			
			// Rename worksheet
			$SpreadSheet->getActiveSheet()->setTitle('GL Transactions');

			// Set active sheet index to the first sheet, so Excel opens this as the first sheet
			$SpreadSheet->setActiveSheetIndex(0);

			// Redirect output to a client's web browser (Excel2007)
			if ($_POST['Format'] == 'xlsx') {
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				$File = 'KL-Products-Zalora-' . Date('Y-m-d'). '.xlsx';
			} else if ($_POST['Format'] == 'ods') {
				header('Content-Type: application/vnd.oasis.opendocument.spreadsheet');
				$File = 'KL-Products-Zalora-' . Date('Y-m-d'). '.ods';
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
				$objWriter->save('php://output');
			} else if ($_POST['Format'] == 'ods') {
				$objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Ods($SpreadSheet);
				$objWriter->save('php://output');
			}

		}else{
			prnMsg('No Products selected for Zalora');
		}
	}
} // End of function submit()


function display($RootPath, $Theme)  //####DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_#####
{
// Display form fields. This function is called the first time
// the page is called.
	$Title = _('Export file for Zalora');
	include('includes/header.php');

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
          <div>
			<br/>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '
		</p>';

	echo '<fieldset>
		<legend>' . _('Price Range Details') . '</legend>';

	echo FieldToSelectOneText('FromPrice', $_POST['FromPrice'], 20, 20, _('From'), '', '', '', true, false);
	echo FieldToSelectOneText('ToPrice', $_POST['ToPrice'], 20, 20, _('To (IDR)'), '', '', '', true, false);
	echo FieldToSelectSpreadSheetFormat('Format', $_POST['Format'], _('Format'), '', '', '', true, false);

	echo '</fieldset>';

	echo OneButtonCenteredForm('submit',$Title);

	echo '</div>
         </form>';
	include('includes/footer.php');

} // End of function display()

?>
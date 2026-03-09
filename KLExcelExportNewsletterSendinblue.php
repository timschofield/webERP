<?php

require(__DIR__ . '/includes/session.php');

use PhpOffice\PhpSpreadsheet\Helper\Sample;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

include(__DIR__ . '/includes/SQL_CommonFunctions.php');
include(__DIR__ . '/includes/KLDefines.php');
include(__DIR__ . '/includes/KLGeneralFunctions.php');
include(__DIR__ . '/includes/UIGeneralFunctions.php'); 
include(__DIR__ . '/includes/KLUIGeneralFunctions.php');
include(__DIR__ . '/includes/KLCountriesForRetail.php');
include(__DIR__ . '/includes/OCOpenCartGeneralFunctions.php');
include(__DIR__ . '/includes/OCOpenCartConnectDB.php');

if (isset($_POST['submit'])) {
    submit($_POST['MarkExported']);
} else {
    display($RootPath, $Theme);
}

//####_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
function submit($MarkExported) {

	//initialise no input errors
	$InputError = 0;

	//first off validate inputs sensible

	if ($InputError == 0){
		
		$SQL = "SELECT 	oc_ne_marketing.firstname,
						oc_ne_marketing.lastname,
						oc_ne_marketing.email
				FROM oc_ne_marketing
				WHERE oc_ne_marketing.subscribed = 1
					AND oc_ne_marketing.exported = 'N'";
		
		$ErrMsg = __('The SQL to find the OpenCart Newsletter Subscribers Data to export to Sendinblue');
		$Result = DB_query_oc($SQL,$ErrMsg);
		if (DB_num_rows($Result) != 0){

			// Create new Spreadsheet object
			$SpreadSheet = new Spreadsheet();

			// Set document properties
			$SpreadSheet->getProperties()->setCreator("webERP")
										 ->setLastModifiedBy("webERP")
										 ->setTitle("Sendinblue Newsletter Subscribers")
										 ->setSubject("Sendinblue Newsletter Subscribers")
										 ->setDescription("Sendinblue Newsletter Subscribers")
										 ->setKeywords("")
										 ->setCategory("");
		
			// Add title data
			$SpreadSheet->setActiveSheetIndex(0);
			$SpreadSheet->getActiveSheet()->setCellValue('A1', 'EMAIL');
			$SpreadSheet->getActiveSheet()->setCellValue('B1', 'FAMILY_NAME');
			$SpreadSheet->getActiveSheet()->setCellValue('C1', 'FIRST_NAME');

			// Add data
			$i = 2;
			while ($MyRow = DB_fetch_array($Result)) {
				$SpreadSheet->setActiveSheetIndex(0);
				$SpreadSheet->getActiveSheet()->setCellValue('A'.$i, $MyRow['email']);
				$SpreadSheet->getActiveSheet()->setCellValue('B'.$i, CapitalizeName($MyRow['lastname']));
				$SpreadSheet->getActiveSheet()->setCellValue('C'.$i, CapitalizeName($MyRow['firstname']));
				
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
			$SpreadSheet->getActiveSheet()->setTitle('Newsletter Subscribers');

			// Set active sheet index to the first sheet, so Excel opens this as the first sheet
			$SpreadSheet->setActiveSheetIndex(0);

			// Redirect output to a client's web browser
			if ($_POST['Format'] == 'xlsx') {
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				$File = 'KL-NewsletterSubscribers-' . date('Y-m-d'). '.xlsx';
			} elseif ($_POST['Format'] == 'ods') {
				header('Content-Type: application/vnd.oasis.opendocument.spreadsheet');
				$File = 'KL-NewsletterSubscribers-' . date('Y-m-d'). '.ods';
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
			} elseif ($_POST['Format'] == 'ods') {
				$objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Ods($SpreadSheet);
				$objWriter->save('php://output');
			}

			if ($MarkExported == "Y"){
				$SQL = "UPDATE 	oc_ne_marketing 
						SET exported = 'Y' 
						WHERE exported = 'N'";
				$ResultUpdate = DB_query_oc($SQL,'','',true);
			}

		} else {
			$Title = __('Excel file for Sendinblue: Export Newsletter Subscribers');
			include(__DIR__ . '/includes/header.php');
			prnMsg('No Newsletter Subscribers Data to export to Sendinblue');
			include(__DIR__ . '/includes/footer.php');
		}
	}
} // End of function submit()


function display($RootPath, $Theme)  //####DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_#####
{
// Display form fields. This function is called the first time
// the page is called.
	$Title = __('Excel file for Sendinblue: Export Newsletter from OpenCart');

	include(__DIR__ . '/includes/header.php');

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
          <div>
			<br/>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . __('Excel file for Sendinblue: Export Newsletter from OpenCart') . '" alt="" />' . ' ' . __('Excel file for Sendinblue: Export Newsletter from OpenCart') . '
		</p>';

	echo '<fieldset>
		<legend>' . __('Export Options') . '</legend>';
		
	echo FieldToSelectSpreadSheetFormat('Format', $_POST['Format'], __('File Format'));

	echo '<field>';
	echo __('Mark as Exported?') . ':';
	echo '<select name="MarkExported">
			<option selected="selected" value="N">' . __('No') . '</option>
			<option value="Y">' . __('Yes') . '</option>
			</select>';
	echo '</field>';
	
	echo '</fieldset>';

	echo OneButtonCenteredForm('submit', __('Export File for Sendinblue'));

	echo '</div>
         </form>';
	include(__DIR__ . '/includes/footer.php');

} // End of function display()

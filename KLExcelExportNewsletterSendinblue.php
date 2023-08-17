<?php
require_once ('Classes/PHPExcel.php');

include('includes/session.php');
include('includes/SQL_CommonFunctions.inc');
include('includes/KLDefines.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLCountriesForRetail.php');
include('includes/OpenCartGeneralFunctions.php');
include('includes/OpenCartConnectDB.php');

if (isset($_POST['submit'])) {
    submit($db_oc, $_POST['MarkExported']);
} else {
    display($db_oc);
}

//####_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
function submit(&$db_oc, $MarkExported) {

	//initialise no input errors
	$InputError = 0;

	//first off validate inputs sensible

	if ($InputError == 0){
		
		$sql = "SELECT 	oc_ne_marketing.firstname,
						oc_ne_marketing.lastname,
						oc_ne_marketing.email
				FROM oc_ne_marketing
				WHERE oc_ne_marketing.subscribed = 1
					AND oc_ne_marketing.exported = 'N'";
		
		$ErrMsg = _('The SQL to find the OpenCart Newsletter Subscribers Data to export to Sendinblue');
		$result = DB_query_oc($sql,$ErrMsg);
		if (DB_num_rows($result) != 0){

			// Create new PHPExcel object
			$objPHPExcel = new PHPExcel();

			// Set document properties
			$objPHPExcel->getProperties()->setCreator("webERP")
										 ->setLastModifiedBy("webERP")
										 ->setTitle("Sendinblue Newsletter Subscribers")
										 ->setSubject("Sendinblue Newsletter Subscribers")
										 ->setDescription("Sendinblue Newsletter Subscribers")
										 ->setKeywords("")
										 ->setCategory("");
		
			// Add title data
			$objPHPExcel->setActiveSheetIndex(0);
			$objPHPExcel->getActiveSheet()->setCellValue('A1', 'EMAIL');
			$objPHPExcel->getActiveSheet()->setCellValue('B1', 'FAMILY_NAME');
			$objPHPExcel->getActiveSheet()->setCellValue('C1', 'FIRST_NAME');

			// Add data
			$i = 2;
			while ($myrow = DB_fetch_array($result)) {
				$objPHPExcel->setActiveSheetIndex(0);
				$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $myrow['email']);
				$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, CapitalizeName($myrow['lastname']));
				$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, CapitalizeName($myrow['firstname']));
				
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
			$objPHPExcel->getActiveSheet()->setTitle('Newsletter Subscribers');

			// Set active sheet index to the first sheet, so Excel opens this as the first sheet
			$objPHPExcel->setActiveSheetIndex(0);

			// Redirect output to a client’s web browser (Excel2007)
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			$File = 'KL-NewsletterSubscribers-' . Date('Y-m-d'). '.xlsx';
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

			if ($MarkExported == "Y"){
				$sql = "UPDATE 	oc_ne_marketing 
						SET exported = 'Y' 
						WHERE exported = 'N'";
				$resultUpdate = DB_query_oc($sql,'','',true);
			}

		}else{
			$Title = _('Excel file for Sendinblue: Export Newsletter Subscribers');
			include('includes/header.php');
			prnMsg('No Newsletter Subscribers Data to export to Sendinblue');
			include('includes/footer.php');
		}
	}
} // End of function submit()


function display(&$db_oc)  //####DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_#####
{
// Display form fields. This function is called the first time
// the page is called.
	$Title = _('Excel file for Sendinblue: Export Newsletter from OpenCart');

	include('includes/header.php');

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
          <div>
			<br/>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Excel file for Sendinblue: Export Newsletter from OpenCart') . '" alt="" />' . ' ' . _('Excel file for Sendinblue: Export Newsletter from OpenCart') . '
		</p>';

	echo '<table>';

	echo '<tr><td>' . _('Mark as Exported?') . ':</td>
			<td><select name="MarkExported">
				<option selected="selected" value="N">' . _('No') . '</option>
				<option value="Y">' . _('Yes') . '</option>
				</select>
			</td>
		</tr>';

	echo '</table>
		<table>';

	echo '<tr><td>&nbsp;</td></tr>
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="submit" value="' . _('Create Excel File for Sendinblue') . '" /></td>
		</tr>
		</table>
		<br />';
	echo '</div>
         </form>';
	include('includes/footer.php');

} // End of function display()

?>
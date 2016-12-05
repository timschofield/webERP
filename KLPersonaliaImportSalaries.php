<?php
require_once ('Classes/PHPExcel.php');

include('includes/session.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/KLDefines.php');
include('includes/KLGeneralFunctions.php');

$Title = _('Import Excel with Monthly Salary Information');

include('includes/header.inc');

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
	  <div>
		<br/>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Import Excel with Monthly Salary Information') . '" alt="" />' . ' ' . _('Import Excel with Monthly Salary Information') . '
	</p>';

if (isset($_POST['submit'])) {
    submit($db, $_POST['DateOfFile'], $_POST['SelectedFile']);
} else {
    display($db);
}

include('includes/footer.inc');



//####_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
function submit(&$db, $DateOfFile, $SelectedFile) {

	//initialise no input errors
	$InputError = 1;

prnMsg($DateOfFile);
prnMsg($SelectedFile);

$FileHandle = fopen($_FILES['SelectedFile']['tmp_name'], 'r');
prnMsg($_FILES['SelectedFile']['tmp_name']);
prnMsg($FileHandle);

	$objPHPExcel = PHPExcel_IOFactory::load("PTGaji.xlsx");

	$dataArr = array();
	 
	foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
		$worksheetTitle     = $worksheet->getTitle();
		$highestRow         = $worksheet->getHighestRow(); // e.g. 10
		$highestColumn      = $worksheet->getHighestColumn(); // e.g 'F'
		$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);

prnMsg($worksheetTitle);		
/*		for ($row = 1; $row <= $highestRow; ++ $row) {
			for ($col = 0; $col < $highestColumnIndex; ++ $col) {
				$cell = $worksheet->getCellByColumnAndRow($col, $row);
				$val = $cell->getValue();
				$dataArr[$row][$col] = $val;
			}
		}
*/	}

} // End of function submit()


function display(&$db)  //####DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_#####
{
// Display form fields. This function is called the first time
// the page is called.


	echo '<table class="selection">';

	echo '<tr><td>' . _('Select Month of the Salaries') . '</td>
							<td><select name="DateOfFile">';
							
	$PeriodsResult = DB_query("SELECT lastdate_in_period FROM periods ORDER BY periodno");
	
	while ($PeriodRow = DB_fetch_row($PeriodsResult)){
		echo '<option value="' . $PeriodRow[0] . '">' . MonthAndYearFromSQLDate($PeriodRow[0]) . '</option>';
	}
	echo '</select></td></tr>';
	
	echo '<tr><td>' . _('PTGaji file:') . '</td><td><input name="SelectedFile" type="file" />
			</td></tr>
		</table>';

	echo '<table>
		<tr><td>&nbsp;</td></tr>
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="submit" value="' . _('Import File') . '" /></td>
		</tr>
		</table>
		<br />';
	echo '</div>
		</form>';

} // End of function display()

?>
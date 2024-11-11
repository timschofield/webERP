<?php
require_once ('Classes/PHPExcel.php');
require_once ('Classes/PHPExcel/IOFactory.php');

include('includes/session.php');
include('includes/SQL_CommonFunctions.inc');
include('includes/KLDefines.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLMarketplaceFunctions.php');
include('includes/OpenCartGeneralFunctions.php');

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post" enctype="multipart/form-data">
	  <div>
		<br/>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (isset($_POST['submit'])) {
    submit($_POST['SelectedFile']);
} else {
    display($RootPath, $Theme);
}


//####_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
function submit($SelectedFile) {

	// upload to server and load it...
	// http://stackoverflow.com/questions/38581632/how-to-upload-excel-file-to-php-server-from-input-type-file

	$target_dir =  $_SESSION['reports_dir'] . '/';
	$target_file = $target_dir . basename($_FILES["SelectedFile"]["name"]);
	$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
	move_uploaded_file($_FILES["SelectedFile"]["tmp_name"], $target_file);
	$inputFileType = PHPExcel_IOFactory::identify($target_file);
	$objReader = PHPExcel_IOFactory::createReader($inputFileType);
	$objPHPExcel = $objReader->load($target_file);
	
	//initialise no input errors
	$InputError = FALSE;
	
	
	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . $PageTitle . '" alt="" />' . ' ' . $PageTitle . 
		'</p>';

	if(!$InputError){
	
		$worksheet = $objPHPExcel->getActiveSheet();
		
		$highestRow         = $worksheet->getHighestRow(); // e.g. 10
		$highestColumn      = $worksheet->getHighestColumn(); // e.g 'F'
		$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
		
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('#') . '</th>
							<th class="ascending">' . _('Item Code') . '</th>
							<th class="ascending">' . _('Tokopedia Product Id') . '</th>
							<th class="ascending">' . _('URL Tokopedia') . '</th>
							<th class="ascending">' . _('QOH Tokopedia') . '</th>
							<th class="ascending">' . _('Error') . '</th>
							<th class="ascending">' . _('Action') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;

		for ($row = 4; $row <= $highestRow; ++ $row) {
			// get the data for a product
			$Error = "";
			$TokopediaProductId = $worksheet->getCell('B'.$row)->getCalculatedValue();
			$StockId = $worksheet->getCell('K'.$row)->getCalculatedValue();
			$TokopediaProductName = $worksheet->getCell('C'.$row)->getCalculatedValue();
			$URLTokopedia = $worksheet->getCell('D'.$row)->getCalculatedValue();
			$LinkTokopedia = '<li><a rel="external" href="' . $URLTokopedia . '">' . _('Tokopedia') . '</a></li>';
				
			// Check if we have enough QOH to set it as enabled in Tokopedia
			$QOH = ItemMarketplaceQOH($StockId);
			$EnabledTokopedia = ( $QOH > 0);
			
			if (DataExistsInWebERP("klstockmarketplaces", "stockid", $StockId)){
				// Already exists, so only update the info with the newest tokopedia link and tokopedia product id if needed
				ItemUpdateTokopediaInfo($StockId, $EnabledTokopedia, $TokopediaProductId, $URLTokopedia, $db);
				$Action = "Update";
			}else{
				// does not exist, so need to insert a new row for the item
				ItemInsertTokopediaInfo($StockId, $EnabledTokopedia, $TokopediaProductId, $URLTokopedia, $db);
				$Action = "Insert";
			}

			$k = StartEvenOrOddRow($k);
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					</tr>', 
					$i,
					$StockId,
					$TokopediaProductId,
					$LinkTokopedia,
					$QOH,
					$Error,
					$Action
					);
			$i++;
		}
		echo '</table>
				</div>
				</form>';
	}
} // End of function submit()


function display($RootPath, $Theme)  //####DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_#####
{
	$Title = _('Import Excel with Tokopedia URL information');
	include('includes/header.php');
	// Display form fields. This function is called the first time the page is called.
	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '
		</p>';
	echo '<table class="selection">';

	echo '<tr><td>' . _('Excel file with Tokopedia Information:') . '</td><td><input type="file"  name="SelectedFile" id="SelectedFile"/></td><td>
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
	include('includes/footer.php');

} // End of function display()




?>
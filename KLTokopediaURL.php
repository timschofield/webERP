<?php

include('includes/session.php');


use PhpOffice\PhpSpreadsheet\Helper\Sample;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

include('includes/SQL_CommonFunctions.php');
include('includes/KLDefines.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLMarketplaceFunctions.php');
include('includes/OCOpenCartGeneralFunctions.php');
include('includes/UIGeneralFunctions.php');
include('includes/KLUIGeneralFunctions.php');

$Title = __('Import Excel with Tokopedia URL information');

include('includes/header.php');

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post" enctype="multipart/form-data">
	  <div>
		<br/>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (!isset($_POST['SelectedFile'])) {
    $_POST['SelectedFile'] = '';
}

if (isset($_POST['submit'])) {
    submit($_POST['SelectedFile'],$RootPath, $Theme, $Title);
} else {
    display($RootPath, $Theme, $Title);
}

include('includes/footer.php');

function submit($SelectedFile, $RootPath, $Theme, $Title) {

	// upload to server and load it...
	// http://stackoverflow.com/questions/38581632/how-to-upload-excel-file-to-php-server-from-input-type-file

	$Target_dir =  $_SESSION['reports_dir'] . '/';
	$Target_file = $Target_dir . basename($_FILES["SelectedFile"]["name"]);
	move_uploaded_file($_FILES["SelectedFile"]["tmp_name"], $Target_file);
	$inputFileType = IOFactory::identify($Target_file);
	$objReader = IOFactory::createReader($inputFileType);
	$SpreadSheet = $objReader->load($Target_file);
	
	//initialise no input errors
	$InputError = false;
	
	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . $Title . '" alt="" />' . ' ' . $Title . 
		'</p>';

	if(!$InputError){
	
		$worksheet = $SpreadSheet->getActiveSheet();
		
		$highestRow = $worksheet->getHighestRow(); // e.g. 10
		
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . __('#') . '</th>
						<th class="SortedColumn">' . __('Item Code') . '</th>
						<th class="SortedColumn">' . __('Tokopedia Product Id') . '</th>
						<th class="SortedColumn">' . __('URL Tokopedia') . '</th>
						<th class="SortedColumn">' . __('QOH Tokopedia') . '</th>
						<th class="SortedColumn">' . __('Error') . '</th>
						<th class="SortedColumn">' . __('Action') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;

		for ($Row = 6; $Row <= $highestRow; ++ $Row) {
			// get the data for a product
			$Error = "";
			$TokopediaProductId = $worksheet->getCell('A'.$Row)->getCalculatedValue();
			$StockID = $worksheet->getCell('K'.$Row)->getCalculatedValue();
			$URLTokopedia = $worksheet->getCell('AC'.$Row)->getCalculatedValue();
			$LinkTokopedia = '<li><a rel="external" href="' . $URLTokopedia . '">' . __('Tokopedia') . '</a></li>';
				
			// Check if we have enough QOH to set it as enabled in Tokopedia
			$QOH = ItemMarketplaceQOH($StockID);
			$EnabledTokopedia = 0;
			if ($QOH > 0) {
				$EnabledTokopedia = 1;
			}
			
			if (DataExistsInWebERP("klstockmarketplaces", "stockid", $StockID)){
				// Already exists, so only update the info with the newest tokopedia link and tokopedia product id if needed
				ItemUpdateTokopediaInfo($StockID, $EnabledTokopedia, $TokopediaProductId, $URLTokopedia);
				$Action = "Update";
			}else{
				// does not exist, so need to insert a new row for the item
				ItemInsertTokopediaInfo($StockID, $EnabledTokopedia, $TokopediaProductId, $URLTokopedia);
				$Action = "Insert";
			}

			echo '<tr class="striped_row">
					<td class="number">' . $i . '</td>
					<td>' . $StockID . '</td>
					<td>' . $TokopediaProductId . '</td>
					<td>' . $LinkTokopedia . '</td>
					<td class="number">' . $QOH . '</td>
					<td>' . $Error . '</td>
					<td>' . $Action . '</td>
					</tr>';
			$i++;
		}
		echo '</tbody>
				</table>
				</div>
				</form>';
	}
} // End of function submit()


function display($RootPath, $Theme, $Title)
{
	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '
		</p>';
	
	echo '<fieldset>
			<legend>' . __('Import file with Tokopedia Information from TikTok') . '</legend>';

	echo FieldToSelectOneFile("SelectedFile", __('File with Tokopedia Information'),'','', '', true, false);	

	echo '</fieldset>';

	echo OneButtonCenteredForm('submit', __('Import File'));

	echo '</div>
		</form>';

} // End of function display()

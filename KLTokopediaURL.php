<?php

include('includes/session.php');

require_once 'vendor/autoload.php';
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

$Title = _('Import Excel with Tokopedia URL information');

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
	$ImageFileType = pathinfo($Target_file,PATHINFO_EXTENSION);
	move_uploaded_file($_FILES["SelectedFile"]["tmp_name"], $Target_file);
	$inputFileType = IOFactory::identify($Target_file);
	$objReader = IOFactory::createReader($inputFileType);
	$SpreadSheet = $objReader->load($Target_file);
	
	//initialise no input errors
	$InputError = FALSE;
	
	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . $Title . '" alt="" />' . ' ' . $Title . 
		'</p>';

	if(!$InputError){
	
		$worksheet = $SpreadSheet->getActiveSheet();
		
		$highestRow         = $worksheet->getHighestRow(); // e.g. 10
		$highestColumn      = $worksheet->getHighestColumn(); // e.g 'F'
		$highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);
		
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('#') . '</th>
						<th class="SortedColumn">' . _('Item Code') . '</th>
						<th class="SortedColumn">' . _('Tokopedia Product Id') . '</th>
						<th class="SortedColumn">' . _('URL Tokopedia') . '</th>
						<th class="SortedColumn">' . _('QOH Tokopedia') . '</th>
						<th class="SortedColumn">' . _('Error') . '</th>
						<th class="SortedColumn">' . _('Action') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;

		for ($Row = 4; $Row <= $highestRow; ++ $Row) {
			// get the data for a product
			$Error = "";
			$TokopediaProductId = $worksheet->getCell('B'.$Row)->getCalculatedValue();
			$StockID = $worksheet->getCell('K'.$Row)->getCalculatedValue();
			$TokopediaProductName = $worksheet->getCell('C'.$Row)->getCalculatedValue();
			$URLTokopedia = $worksheet->getCell('D'.$Row)->getCalculatedValue();
			$LinkTokopedia = '<li><a rel="external" href="' . $URLTokopedia . '">' . _('Tokopedia') . '</a></li>';
				
			// Check if we have enough QOH to set it as enabled in Tokopedia
			$QOH = ItemMarketplaceQOH($StockID);
			$EnabledTokopedia = ( $QOH > 0);
			
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
			<legend>' . _('Import file with Tokopedia Information') . '</legend>';

	echo FieldToSelectOneFile("SelectedFile", _('File with Tokopedia Information'),'','', '', true, false);	

	echo '</fieldset>';

	echo OneButtonCenteredForm('submit', _('Import File'));

	echo '</div>
		</form>';

} // End of function display()




?>
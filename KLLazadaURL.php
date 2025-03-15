<?php

include('includes/session.php');

require_once 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Helper\Sample;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

include('includes/SQL_CommonFunctions.inc');
include('includes/KLDefines.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLMarketplaceFunctions.php');
include('includes/OCOpenCartGeneralFunctions.php');
include('includes/UIGeneralFunctions.php');
include('includes/KLUIGeneralFunctions.php');

$Title = _('Import Excel with Lazada URL information');

include('includes/header.php');

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post" enctype="multipart/form-data">
	  <div>
		<br/>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (!isset($_POST['SelectedFile'])) {
    $_POST['SelectedFile'] = '';
}

if (isset($_POST['submit'])) {
    submit($_POST['SelectedFile']);
} else {
    display();
}

include('includes/footer.php');



//####_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
function submit($SelectedFile) {

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
			<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . $PageTitle . '" alt="" />' . ' ' . $PageTitle . 
		'</p>';

	if(!$InputError){
	
		$ExcelSheetName = "template";
		$SpreadSheet->setActiveSheetIndexByName($ExcelSheetName);
		
		$worksheet = $SpreadSheet->getActiveSheet();

		$highestRow         = $worksheet->getHighestRow(); // e.g. 10
		$highestColumn      = $worksheet->getHighestColumn(); // e.g 'F'
		$highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);
		
		echo '<div>';
		echo '<table>
				<thead>
					<tr>
						<th class="SortedColumn">' . _('#') . '</th>
						<th class="SortedColumn">' . _('Item Code') . '</th>
						<th class="SortedColumn">' . _('Lazada Product Id') . '</th>
						<th class="SortedColumn">' . _('Lazada Store Id') . '</th>
						<th class="SortedColumn">' . _('URL Lazada') . '</th>
						<th class="SortedColumn">' . _('QOH Lazada') . '</th>
						<th class="SortedColumn">' . _('Error') . '</th>
						<th class="SortedColumn">' . _('Action') . '</th>
					</tr>
				</thead>
				<tbody>';
		$k = 0; //row colour counter
		$i = 1;

		for ($Row = 2; $Row <= $highestRow; ++ $Row) {
			// get the data for a product
			$Error = "";
			$LazadaProductId = $worksheet->getCell('A'.$Row)->getCalculatedValue();
			$StockID = $worksheet->getCell('Q'.$Row)->getCalculatedValue();
			$LazadaProductName = $worksheet->getCell('C'.$Row)->getCalculatedValue();
			
			$SQL = "SELECT stockmaster.stockid,
						salescatprod.manufacturers_id
					FROM stockmaster, salescatprod
					WHERE stockmaster.stockid = salescatprod.stockid
						AND stockmaster.stockid = '" . $StockID . "'";
			$Result = DB_query($SQL);
			if (DB_num_rows($Result) != 0){
				$MyRow = DB_fetch_array($Result);
		
				$URLLazada = LAZADA_PREFIX_URL . $LazadaProductId . ".html";
				$LinkLazada = '<li><a rel="external" href="' . $URLLazada . '">' . _('Lazada') . '</a></li>';
				
				// Check if we have enough QOH to set it as enabled in Lazada
				$QOH = ItemMarketplaceQOH($StockID);
				$EnabledLazada = ( $QOH > 0);
				
				if (DataExistsInWebERP("klstockmarketplaces", "stockid", $StockID)){
					// Already exists, so only update the info with the newest lazada link and lazada product id if needed
					ItemUpdateLazadaInfo($StockID, $EnabledLazada, $LazadaProductId, $URLLazada);
					$Action = "Update";
				}else{
					// does not exist, so need to insert a new row for the item
					ItemInsertLazadaInfo($StockID, $EnabledLazada, $LazadaProductId, $URLLazada);
					$Action = "Insert";
				}

				$k = StartEvenOrOddRow($k);
				echo '<tr class="striped_row">
					<td class="number">' . $i . '</td>
					<td>' . $StockID . '</td>
					<td>' . $LazadaProductId . '</td>
					<td>' . $LazadaStoreId . '</td>
					<td>' . $LinkLazada . '</td>
					<td class="number">' . $QOH . '</td>
					<td>' . $Error . '</td>
					<td>' . $Action . '</td>
					</tr>';
				$i++;
			}
		}
		echo '</tbody>
			</table>
			</div>
			</form>';
	}
} // End of function submit()


function display()  //####DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_#####
{
    echo '<p class="page_title_text">
            <img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '
        </p>';

    echo '<fieldset>
            <legend>' . _('Import file with Lazada Information') . '</legend>';

    echo FieldToSelectOneFile("SelectedFile", _('File with Lazada Information'),'','', '', true, false);

    echo '</fieldset>';

    echo OneButtonCenteredForm('submit', _('Import File'));

    echo '</div>
        </form>';

} // End of function display()




?>
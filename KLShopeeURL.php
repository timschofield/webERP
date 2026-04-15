<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Import Excel with Shopee URL information');
include(__DIR__ . '/includes/header.php');

use PhpOffice\PhpSpreadsheet\Helper\Sample;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

include(__DIR__ . '/includes/SQL_CommonFunctions.php');
include(__DIR__ . '/includes/KLDefines.php');
include(__DIR__ . '/includes/KLGeneralFunctions.php');
include(__DIR__ . '/includes/UIGeneralFunctions.php');
include(__DIR__ . '/includes/KLUIGeneralFunctions.php');
include(__DIR__ . '/includes/KLMarketplaceFunctions.php');
include(__DIR__ . '/includes/OCOpenCartGeneralFunctions.php');

// as the script uses _SESSION variables, reload just in case another user has been changing values in the meantime 
// because the script needs the latest values for the calculations
ReloadSessionVariablesFromConfig();

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post" enctype="multipart/form-data">
	  <div>
		<br/>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (!isset($_POST['SelectedFile'])) {
    $_POST['SelectedFile'] = '';
}

if (isset($_POST['submit'])) {
    submit($_POST['SelectedFile'], $RootPath, $Theme, $Title);
} else {
    display($RootPath, $Theme, $Title);
}

include(__DIR__ . '/includes/footer.php');



//####_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
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
	$InputError = false;
	
	
	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . $Title . '" alt="" />' . ' ' . $Title . 
		'</p>';

	if (!$InputError){
	
		$worksheet = $SpreadSheet->getActiveSheet();
		
		$highestRow         = $worksheet->getHighestRow(); // e.g. 10
		$highestColumn      = $worksheet->getHighestColumn(); // e.g 'F'
		
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . __('#') . '</th>
						<th class="SortedColumn">' . __('Item Code') . '</th>
						<th class="SortedColumn">' . __('Shopee Product Id') . '</th>
						<th class="SortedColumn">' . __('Shopee Store Id') . '</th>
						<th class="SortedColumn">' . __('URL Shopee') . '</th>
						<th class="SortedColumn">' . __('QOH Shopee') . '</th>
						<th class="SortedColumn">' . __('Error') . '</th>
						<th class="SortedColumn">' . __('Action') . '</th>
					</tr>
				</thead>
				<tbody>';
		$i = 1;

		for ($Row = 4; $Row <= $highestRow; ++ $Row) {
			// get the data for a product
			$Error = "";
			$ShopeeProductId = $worksheet->getCell('A'.$Row)->getCalculatedValue();
			$StockID = $worksheet->getCell('B'.$Row)->getCalculatedValue();
			$ShopeeProductName = $worksheet->getCell('C'.$Row)->getCalculatedValue();
			
			$SQL = "SELECT stockmaster.stockid,
						salescatprod.brands_id
					FROM stockmaster, salescatprod
					WHERE stockmaster.stockid = salescatprod.stockid
						AND stockmaster.stockid = '" . $StockID . "'";
			$Result = DB_query($SQL);
			if (DB_num_rows($Result) != 0){
				$MyRow = DB_fetch_array($Result);
				
				if ($MyRow['brands_id'] == "1"){
					$ShopeeStoreId = SHOPEE_STOREID_KAPAL_LAUT;
				} elseif ($MyRow['brands_id'] == "2"){
					$ShopeeStoreId = SHOPEE_STOREID_BLINK;
				} else {
					$Error = "STORE";
				}
				
				$URLShopee = SHOPEE_PREFIX_URL . $ShopeeStoreId . "." . $ShopeeProductId;
				$LinkShopee = '<li><a rel="external" href="' . $URLShopee . '">' . __('Shopee') . '</a></li>';
				
				// Check if we have enough QOH to set it as enabled in Shopee
				$QOH = ItemMarketplaceQOH($StockID);
				$EnabledShopee = 0;
				if ($QOH > 0) {
					$EnabledShopee = 1;
				}
				
				if (DataExistsInWebERP("klstockmarketplaces", "stockid", $StockID)){
					// Already exists, so only update the info with the newest shopee link and shopee product id if needed
					ItemUpdateShopeeInfo($StockID, $EnabledShopee, $ShopeeProductId, $URLShopee);
					$Action = "Update";
				} else {
					// does not exist, so need to insert a new row for the item
					ItemInsertShopeeInfo($StockID, $EnabledShopee, $ShopeeProductId, $URLShopee);
					$Action = "Insert";
				}

				echo '<tr class="striped_row">
						<td class="number">' . $i . '</td>
						<td>' . $StockID . '</td>
						<td>' . $ShopeeProductId . '</td>
						<td>' . $ShopeeStoreId . '</td>
						<td>' . $LinkShopee . '</td>
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


function display($RootPath, $Theme, $Title)  //####DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_#####
{
	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '
		</p>';

		echo '<fieldset>
		<legend>' . __('Import file with Shopee Information') . '</legend>';

	echo FieldToSelectOneFile("SelectedFile", __('File with Shopee Information'),'','', '', true, false);

	echo '</fieldset>';

	echo OneButtonCenteredForm('submit', __('Import File'));

	echo '</div>
		</form>';

} // End of function display()

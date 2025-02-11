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
include('includes/UIGeneralFunctions.php');
include('includes/KLUIGeneralFunctions.php');
include('includes/KLMarketplaceFunctions.php');
include('includes/OpenCartGeneralFunctions.php');

$Title = _('Import Excel with Shopee URL information');

include('includes/header.php');

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

include('includes/footer.php');



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
	$objPHPExcel = $objReader->load($Target_file);
	
	//initialise no input errors
	$InputError = FALSE;
	
	
	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . $Title . '" alt="" />' . ' ' . $Title . 
		'</p>';

	if(!$InputError){
	
		$worksheet = $objPHPExcel->getActiveSheet();
		
		$highestRow         = $worksheet->getHighestRow(); // e.g. 10
		$highestColumn      = $worksheet->getHighestColumn(); // e.g 'F'
		$highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);
		
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('#') . '</th>
						<th class="SortedColumn">' . _('Item Code') . '</th>
						<th class="SortedColumn">' . _('Shopee Product Id') . '</th>
						<th class="SortedColumn">' . _('Shopee Store Id') . '</th>
						<th class="SortedColumn">' . _('URL Shopee') . '</th>
						<th class="SortedColumn">' . _('QOH Shopee') . '</th>
						<th class="SortedColumn">' . _('Error') . '</th>
						<th class="SortedColumn">' . _('Action') . '</th>
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
						salescatprod.manufacturers_id
					FROM stockmaster, salescatprod
					WHERE stockmaster.stockid = salescatprod.stockid
						AND stockmaster.stockid = '" . $StockID . "'";
			$Result = DB_query($SQL);
			if (DB_num_rows($Result) != 0){
				$MyRow = DB_fetch_array($Result);
				
				if ($MyRow['manufacturers_id'] == "1"){
					$ShopeeStoreId = SHOPEE_KAPAL_LAUT_STOREID;
				}else if ($MyRow['manufacturers_id'] == "2"){
					$ShopeeStoreId = SHOPEE_BLINK_STOREID;
				}else{
					$Error = "STORE";
				}
				
				$URLShopee = SHOPEE_PREFIX_URL . $ShopeeStoreId . "." . $ShopeeProductId;
				$LinkShopee = '<li><a rel="external" href="' . $URLShopee . '">' . _('Shopee') . '</a></li>';
				
				// Check if we have enough QOH to set it as enabled in Shopee
				$QOH = ItemMarketplaceQOH($StockID);
				$EnabledShopee = ( $QOH > 0);
				
				if (DataExistsInWebERP("klstockmarketplaces", "stockid", $StockID)){
					// Already exists, so only update the info with the newest shopee link and shopee product id if needed
					ItemUpdateShopeeInfo($StockID, $EnabledShopee, $ShopeeProductId, $URLShopee);
					$Action = "Update";
				}else{
					// does not exist, so need to insert a new row for the item
					ItemInsertShopeeInfo($StockID, $EnabledShopee, $ShopeeProductId, $URLShopee);
					$Action = "Insert";
				}

				printf('<tr class="striped_row">
						<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
						</tr>', 
						$i,
						$StockID,
						$ShopeeProductId,
						$ShopeeStoreId,
						$LinkShopee,
						$QOH,
						$Error,
						$Action
						);
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
		<legend>' . _('Import file with Shopee Information') . '</legend>';

	echo FieldToSelectOneFile("SelectedFile", _('File with Shopee Information'),'','', '', true, false);

	echo '</fieldset>';

	echo OneButtonCenteredForm('submit', _('Import File'));

	echo '</div>
		</form>';

} // End of function display()




?>
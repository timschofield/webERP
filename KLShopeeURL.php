<?php
require_once ('Classes/PHPExcel.php');
require_once ('Classes/PHPExcel/IOFactory.php');

include('includes/session.php');
include('includes/SQL_CommonFunctions.inc');
include('includes/KLDefines.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLMarketplaceFunctions.php');
include('includes/OpenCartGeneralFunctions.php');

$Title = _('Import Excel with Shopee URL information');

include('includes/header.php');

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post" enctype="multipart/form-data">
	  <div>
		<br/>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

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
							<th class="ascending">' . _('Shopee Product Id') . '</th>
							<th class="ascending">' . _('Shopee Store Id') . '</th>
							<th class="ascending">' . _('URL Shopee') . '</th>
							<th class="ascending">' . _('QOH Shopee') . '</th>
							<th class="ascending">' . _('Error') . '</th>
							<th class="ascending">' . _('Action') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;

		for ($row = 4; $row <= $highestRow; ++ $row) {
			// get the data for a product
			$Error = "";
			$ShopeeProductId = $worksheet->getCell('A'.$row)->getCalculatedValue();
			$StockId = $worksheet->getCell('B'.$row)->getCalculatedValue();
			$ShopeeProductName = $worksheet->getCell('C'.$row)->getCalculatedValue();
			
			$SQL = "SELECT stockmaster.stockid,
						salescatprod.manufacturers_id
					FROM stockmaster, salescatprod
					WHERE stockmaster.stockid = salescatprod.stockid
						AND stockmaster.stockid = '" . $StockId . "'";
			$result = DB_query($SQL);
			if (DB_num_rows($result) != 0){
				$myrow = DB_fetch_array($result);
				
				if ($myrow['manufacturers_id'] == "1"){
					$ShopeeStoreId = SHOPEE_KAPAL_LAUT_STOREID;
				}else if ($myrow['manufacturers_id'] == "2"){
					$ShopeeStoreId = SHOPEE_BLINK_STOREID;
				}else{
					$Error = "STORE";
				}
				
				$URLShopee = SHOPEE_PREFIX_URL . $ShopeeStoreId . "." . $ShopeeProductId;
				$LinkShopee = '<li><a rel="external" href="' . $URLShopee . '">' . _('Shopee') . '</a></li>';
				
				// Check if we have enough QOH to set it as enabled in Shopee
				$QOH = ItemMarketplaceQOH($StockId);
				$EnabledShopee = ( $QOH > 0);
				
				if (DataExistsInWebERP("klstockmarketplaces", "stockid", $StockId)){
					// Already exists, so only update the info with the newest shopee link and shopee product id if needed
					ItemUpdateShopeeInfo($StockId, $EnabledShopee, $ShopeeProductId, $URLShopee);
					$Action = "Update";
				}else{
					// does not exist, so need to insert a new row for the item
					ItemInsertShopeeInfo($StockId, $EnabledShopee, $ShopeeProductId, $URLShopee);
					$Action = "Insert";
				}

				$k = StartEvenOrOddRow($k);
				printf('<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
						</tr>', 
						$i,
						$StockId,
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
		echo '</table>
				</div>
				</form>';
	}
} // End of function submit()


function display($RootPath, $Theme, $Title)  //####DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_#####
{
	// Display form fields. This function is called the first time the page is called.
	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '
		</p>';
	echo '<table class="selection">';

	echo '<tr><td>' . _('Excel file with Shopee Information:') . '</td><td><input type="file"  name="SelectedFile" id="SelectedFile"/></td><td>
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
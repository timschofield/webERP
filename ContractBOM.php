<?php

// NB: these classes are not autoloaded, and their definition has to be included before the session is started (in session.php)
include('includes/DefineContractClass.php');

require(__DIR__ . '/includes/session.php');

include('includes/ImageFunctions.php');

$identifier = $_GET['identifier'];

/* If a contract header doesn't exist, then go to
 * Contracts.php to create one
 */
if (!isset($_SESSION['Contract'.$identifier])){
	header('Location:' . htmlspecialchars_decode($RootPath) . '/Contracts.php');
	exit();
}

$Title = __('Contract Bill of Materials');
$ViewTopic = 'Contracts';
$BookMark = 'AddToContract';
include('includes/header.php');

if (isset($_POST['UpdateLines']) OR isset($_POST['BackToHeader'])) {
	if($_SESSION['Contract'.$identifier]->Status!=2){ //dont do anything if the customer has committed to the contract
		foreach ($_SESSION['Contract'.$identifier]->ContractBOM as $ContractComponent) {
			if (filter_number_format($_POST['Qty'.$ContractComponent->ComponentID])==0){
				//this is the same as deleting the line - so delete it
				$_SESSION['Contract'.$identifier]->Remove_ContractComponent($ContractComponent->ComponentID);
			} else {
				$_SESSION['Contract'.$identifier]->ContractBOM[$ContractComponent->ComponentID]->Quantity=filter_number_format($_POST['Qty'.$ContractComponent->ComponentID]);
			}
		} // end loop around the items on the contract BOM
	} // end if the contract is not currently committed to by the customer
}// end if the user has hit the update lines or back to header buttons


if (isset($_POST['BackToHeader'])){
	echo '<meta http-equiv="Refresh" content="0; url=' . $RootPath . '/Contracts.php?identifier='.$identifier. '" />';
	echo '<br />';
	prnMsg(__('You should automatically be forwarded to the Contract page. If this does not happen perhaps the browser does not support META Refresh') . '<a href="' . $RootPath . '/Contracts.php?identifier='.$identifier . '">' . __('click here') . '</a> ' . __('to continue'),'info');
	include('includes/footer.php');
	exit();
}

if (isset($_POST['Search'])){  /*ie seach for stock items */

	if ($_POST['Keywords'] AND $_POST['StockCode']) {
		prnMsg(__('Stock description keywords have been used in preference to the Stock code extract entered'), 'info');
	}

	if ($_POST['Keywords']) {
		//insert wildcard characters in spaces
		$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';

		if ($_POST['StockCat']=='All'){
			$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.units
					FROM stockmaster INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
					WHERE stockmaster.mbflag!='D'
					AND stockmaster.mbflag!='A'
					AND stockmaster.mbflag!='K'
					and stockmaster.discontinued!=1
					AND stockmaster.description " . LIKE . " '$SearchString'
					ORDER BY stockmaster.stockid";
		} else {
			$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.units
					FROM stockmaster INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
					WHERE stockmaster.mbflag!='D'
					AND stockmaster.mbflag!='A'
					AND stockmaster.mbflag!='K'
					and stockmaster.discontinued!=1
					AND stockmaster.description " . LIKE . " '$SearchString'
					AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
					ORDER BY stockmaster.stockid";
		}

	} elseif ($_POST['StockCode']){

		$_POST['StockCode'] = '%' . $_POST['StockCode'] . '%';

		if ($_POST['StockCat']=='All'){
			$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.units
					FROM stockmaster INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
					WHERE stockmaster.mbflag!='D'
					AND stockmaster.mbflag!='A'
					AND stockmaster.mbflag!='K'
					AND stockmaster.discontinued!=1
					AND stockmaster.stockid " . LIKE . " '" . $_POST['StockCode'] . "'
					ORDER BY stockmaster.stockid";
		} else {
			$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.units
					FROM stockmaster INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
					WHERE stockmaster.mbflag!='D'
					AND stockmaster.mbflag!='A'
					AND stockmaster.mbflag!='K'
					AND stockmaster.discontinued!=1
					AND stockmaster.stockid " . LIKE . " '" . $_POST['StockCode'] . "'
					AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
					ORDER BY stockmaster.stockid";
		}

	} else {
		if ($_POST['StockCat']=='All'){
			$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.units
					FROM stockmaster INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
					WHERE stockmaster.mbflag!='D'
					AND stockmaster.mbflag!='A'
					AND stockmaster.mbflag!='K'
					AND stockmaster.discontinued!=1
					ORDER BY stockmaster.stockid";
		} else {
			$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.units
					FROM stockmaster INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
					WHERE stockmaster.mbflag!='D'
					AND stockmaster.mbflag!='A'
					AND stockmaster.mbflag!='K'
					AND stockmaster.discontinued!=1
					AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
					ORDER BY stockmaster.stockid";
		}
	}

	$ErrMsg = __('There is a problem selecting the part records to display because');
	$SearchResult = DB_query($SQL, $ErrMsg);

	if (DB_num_rows($SearchResult)==0){
		prnMsg( __('There are no products to display matching the criteria provided'),'warn');
	}
	if (DB_num_rows($SearchResult)==1){
		$MyRow=DB_fetch_array($SearchResult);
		$_GET['NewItem'] = $MyRow['stockid'];
		DB_data_seek($SearchResult,0);
	}

} //end of if search


if(isset($_GET['Delete'])){
	if($_SESSION['Contract'.$identifier]->Status!=2){
		$_SESSION['Contract'.$identifier]->Remove_ContractComponent($_GET['Delete']);
	} else {
		prnMsg( __('The contract BOM cannot be altered because the customer has already placed the order'),'warn');
	}
}

if (isset($_POST['NewItem'])){ /* NewItem is set from the part selection list as the part code selected */
	for ($i=0;$i < $_POST['CountOfItems'];$i++) {
		$AlreadyOnThisBOM = 0;
		if (filter_number_format($_POST['Qty'.$i])>0){
			if (count($_SESSION['Contract'.$identifier]->ContractBOM)!=0){

				foreach ($_SESSION['Contract'.$identifier]->ContractBOM AS $Component) {

				/* do a loop round the items on the order to see that the item
				is not already on this order */
					if ($Component->StockID == trim($_POST['StockID'.$i])) {
						$AlreadyOnThisBOM = 1;
						prnMsg( __('The item') . ' ' . trim($_POST['StockID'.$i]) . ' ' . __('is already in the bill of material for this contract. The system will not allow the same item on the contract more than once. However you can change the quantity required for the item.'),'error');
					}
				} /* end of the foreach loop to look for preexisting items of the same code */
			}

			if ($AlreadyOnThisBOM!=1){

				$SQL = "SELECT stockmaster.description,
								stockmaster.stockid,
								stockmaster.units,
								stockmaster.decimalplaces,
								stockmaster.actualcost AS unitcost
							FROM stockmaster
							WHERE stockmaster.stockid = '". trim($_POST['StockID'.$i]) . "'";

				$ErrMsg = __('The item details could not be retrieved');
				$Result1 = DB_query($SQL, $ErrMsg);

				if ($MyRow = DB_fetch_array($Result1)){

					$_SESSION['Contract'.$identifier]->Add_To_ContractBOM (trim($_POST['StockID'.$i]),
																			$MyRow['description'],
																			'',
																			filter_number_format($_POST['Qty'.$i]), /* Qty */
																			$MyRow['unitcost'],
																			$MyRow['units'],
																			$MyRow['decimalplaces']);
				} else {
					prnMsg(__('The item code') . ' ' . trim($_POST['StockID'.$i]) . ' ' . __('does not exist in the database and therefore cannot be added to the contract BOM'),'error');
					include('includes/footer.php');
					exit();
				}
			} /* end of if not already on the contract BOM */
		} /* the quantity of the item is > 0 */
	}
} /* end of if its a new item */

/* This is where the order as selected should be displayed  reflecting any deletions or insertions*/

echo '<form id="ContractBOMForm" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier=' . urlencode($identifier) . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (count($_SESSION['Contract'.$identifier]->ContractBOM)>0){
	$SQL = "SELECT categoryid,
				categorydescription
			FROM stockcategory
			WHERE stocktype<>'L'
			AND stocktype<>'D'
			ORDER BY categorydescription";
	$ErrMsg = __('The supplier category details could not be retrieved because');
	$Result1 = DB_query($SQL, $ErrMsg);
	echo '<p class="page_title_text">
			<img src="'.$RootPath.'/css/'.$Theme.'/images/contract.png" title="' . __('Contract Bill of Material') . '" alt="" />  '.$_SESSION['Contract'.$identifier]->CustomerName . '
		</p>';

	echo '<fieldset>
			<legend>', __('Search For Stock Items'), '</legend>
			<field>
				<label for="StockCat"">', __('Select Stock Category'), '</label>
				<select name="StockCat">';

	echo '<option selected="selected" value="All">', __('All'), '</option>';
	while ($MyRow1 = DB_fetch_array($Result1)) {
		if (isset($_POST['StockCat']) and $_POST['StockCat'] == $MyRow1['categoryid']) {
			echo '<option selected="selected" value="', $MyRow1['categoryid'], '">', $MyRow1['categorydescription'], '</option>';
		} else {
			echo '<option value="', $MyRow1['categoryid'], '">', $MyRow1['categorydescription'], '</option>';
		}
	}
	echo '</select>
		</field>';

	unset($_POST['Keywords']);
	unset($_POST['StockCode']);

	if (!isset($_POST['Keywords'])) {
		$_POST['Keywords'] = '';
	}

	if (!isset($_POST['StockCode'])) {
		$_POST['StockCode'] = '';
	}

	echo '<field>
			<label for="Keywords">', __('Enter text extracts in the description'), ':</label>
			<input type="search" name="Keywords" size="20" autofocus="autofocus" maxlength="25" value="', $_POST['Keywords'], '" />
		</field>
		<field>
			<label for="StockCode">', '<b>', __('OR'), ' </b>', __('Enter extract of the Stock Code'), ':</label>
			<input type="search" name="StockCode" size="15" maxlength="18" value="', $_POST['StockCode'], '" />
		</field>
		<a target="_blank" href="', $RootPath, '/Stocks.php">', '<b>', __('OR'), ' </b>', __('Create a New Stock Item'), '</a>
	</fieldset>';
	echo '<div class="centre"><input type="submit" name="UpdateLines" value="' . __('Update Lines') . '" />';
	echo '<input type="submit" name="BackToHeader" value="' . __('Back To Contract Header') . '" /></div>';

} /*Only display the contract BOM lines if there are any !! */

if (!isset($_GET['Edit'])) {
	$SQL="SELECT categoryid,
				categorydescription
			FROM stockcategory
			WHERE stocktype<>'L'
			AND stocktype<>'D'
			ORDER BY categorydescription";
	$ErrMsg = __('The supplier category details could not be retrieved because');
	$Result1 = DB_query($SQL, $ErrMsg);
	echo '<p class="page_title_text">
			<img src="'.$RootPath.'/css/'.$Theme.'/images/magnifier.png" title="' . __('Print') . '" alt="" />' . ' ' . __('Search For Stock Items') .
		'</p>';
	echo '<table class="selection">
			<tr></tr>
			<tr>
				<td><select name="StockCat">';

	echo '<option selected="selected" value="All">' . __('All') . '</option>';
	while ($MyRow1 = DB_fetch_array($Result1)) {
		if (isset($_POST['StockCat']) and $_POST['StockCat']==$MyRow1['categoryid']){
			echo '<option selected="selected" value="'. $MyRow1['categoryid'] . '">' . $MyRow1['categorydescription'] . '</option>';
		} else {
			echo '<option value="'. $MyRow1['categoryid'] . '">' . $MyRow1['categorydescription'] . '</option>';
		}
	}

	unset($_POST['Keywords']);
	unset($_POST['StockCode']);

	if (!isset($_POST['Keywords'])) {
		$_POST['Keywords']='';
	}

	if (!isset($_POST['StockCode'])) {
		$_POST['StockCode']='';
	}

	echo '</select></td>
			<td>' . __('Enter text extracts in the description') . ':</td>
			<td><input type="text" autofocus="autofocus" title="' . __('Enter any text that should appear in the item description as the basis of your search') . '" name="Keywords" size="20" maxlength="25" value="' . $_POST['Keywords'] . '" /></td>
		</tr>
		<tr>
			<td></td>
			<td><b>' . __('OR') . ' </b>' . __('Enter extract of the Stock Code') . ':</td>
			<td><input type="text" title="' . __('Enter any part of an item code to seach for all matching items containing that text in the code') . '" name="StockCode" size="15" maxlength="18" value="' . $_POST['StockCode'] . '" /></td>
		</tr>
		<tr>
			<td></td>
			<td><b>' . __('OR') . ' </b><a target="_blank" href="'.$RootPath.'/Stocks.php">' . __('Create a New Stock Item') . '</a></td>
		</tr>
		</table>
		<br />
		<div class="centre">
			<input type="submit" name="Search" value="' . __('Search Now') . '" />
		</div>
		<br />';

}

if (isset($SearchResult)) {

	echo '<table cellpadding="1">';

	$TableHeader = '<tr>
						<th>' . __('Code')  . '</th>
						<th>' . __('Description') . '</th>
						<th>' . __('Units') . '</th>
						<th>' . __('Image') . '</th>
						<th>' . __('Quantity') . '</th>
					</tr>';
	echo $TableHeader;

	$i=0;
	while ($MyRow=DB_fetch_array($SearchResult)) {

		$SupportedImgExt = array('png','jpg','jpeg');
        $Glob = (glob($_SESSION['part_pics_dir'] . '/' . $MyRow['stockid'] . '.{' . implode(",", $SupportedImgExt) . '}', GLOB_BRACE));
		$ImageFile = reset($Glob);
		$ImageSource = GetImageLink($ImageFile, $MyRow['stockid'], 100, 100, "", "");

		echo '<tr class="striped_row">
				<td>' . $MyRow['stockid'] . '</td>
				<td>' . $MyRow['description'] . '</td>
				<td>' . $MyRow['units'] . '</td>
				<td>' . $ImageSource . '</td>
				<td><input class="number" type="text" title="' . __('Enter the quantity required of this item to complete the contract') . '" required="required" size="6" value="0" name="Qty'.$i.'" />
				<input type="hidden" name="StockID' . $i . '" value="' . $MyRow['stockid'] . '" />
				</td>
			</tr>';
		$i++;
		if ($i == $_SESSION['DisplayRecordsMax']){
			break;
		}
#end of page full new headings if
	}

#end of while loop
	echo '</table>
			<input type="hidden" name="CountOfItems" value="'. $i . '" />';
	if ($i == $_SESSION['DisplayRecordsMax']){

		prnMsg( __('Only the first') . ' ' . $_SESSION['DisplayRecordsMax'] . ' ' . __('can be displayed') . '. ' . __('Please restrict your search to only the parts required'),'info');
	}
	echo '<br />
		<div class="centre">
			<input type="submit" name="NewItem" value="' . __('Add to Contract Bill Of Material') .'" />
		</div>';
}#end if SearchResults to show

echo '<hr />
    </div>
	</form>';
include('includes/footer.php');

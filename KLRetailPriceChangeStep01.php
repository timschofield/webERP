<?php

include('includes/session.php');

$Title = __('KL Change of Retail Price -> Step 01');
include('includes/header.php');

include('includes/KLDefines.php');
include('includes/KLPrices.php');
include('includes/KLEmails.php');
include('includes/KLGeneralFunctions.php');
include('includes/UIGeneralFunctions.php');
include('includes/KLUIGeneralFunctions.php');

if (isset($_GET['SelectedPriceChange'])){
	$SelectedPriceChange =mb_strtoupper($_GET['SelectedPriceChange']);
} elseif(isset($_POST['SelectedPriceChange'])){
	$SelectedPriceChange =mb_strtoupper($_POST['SelectedPriceChange']);
}

if (isset($Errors)) {
	unset($Errors);
}

$Errors = array();

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . __('KL Retail Price Change') . '" alt="" />' . ' ' . $Title.'</p>';

if (isset($_POST['submit'])) {
	
	$_POST['Stockid'] = strtoupper($_POST['Stockid']); // just in case it came in lowercase

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */
	$i=1;
	//first off validate inputs sensible round_price($MyRow['retailprice'], "UP")
	
	$Result = DB_query("SELECT klchangingprice, 
								klmovingdiscount20,
								klmovingdiscount50,
								klmovingdiscount80,
								categoryid, 
								discontinued 
						FROM stockmaster 
						WHERE stockid='" . $_POST['Stockid'] . "'");
	$MyRow = DB_fetch_array($Result);
	if (DB_num_rows($Result)==0) {
		prnMsg( __('The entered item code does not exist'),'error');
		$InputError = 1;
		$Errors[$i] = 'StockId';
		$i++;
	}elseif ((ItemCodeQOH($_POST['Stockid'],'CODE_FULL', "ALL") != 0) 
			AND (GetTotalItemsChangingPrice() >= MAX_ITEMS_CHANGING_PRICE) 
			AND (!ItemInList($MyRow['categoryid'], LIST_STOCK_CATEGORIES_SETUP))
			AND (!$KL_SystemAdmin)) {
		$InputError = 1;
		$Errors[$i] = 'MaxItemsChangingPrice';
		$i++;
		prnMsg(__('Too many items changing price at the same time. Maximum = '). MAX_ITEMS_CHANGING_PRICE,'error');
	}elseif (!is_numeric(filter_number_format($_POST['NewRetailPrice']))) {
		$InputError = 1;
		$Errors[$i] = 'NewRetailPrice';
		$i++;
		prnMsg(__('The new retail price must be a number'),'error');
	}elseif (!IsPriceRoundedOK($_POST['NewRetailPrice'])){
		$InputError = 1;
		$Errors[$i] = 'NewRetailPrice';
		$i++;
		prnMsg(__('The new retail price is not a correct rounded number.'),'error');
	}elseif ($MyRow['klchangingprice'] == 1) {
		$InputError = 1;
		$Errors[$i] = 'klchangingprice';
		$i++;
		prnMsg(__('This item is already in changing price procedure.'),'error');
	}elseif ($MyRow['klmovingdiscount20'] == 1) {
		$InputError = 1;
		$Errors[$i] = 'MovingDiscount20';
		$i++;
		prnMsg(__('This item is already in Move To 20% Discount procedure. Finish or delete this process first.'),'error');
	}elseif ($MyRow['klmovingdiscount50'] == 1) {
		$InputError = 1;
		$Errors[$i] = 'MovingDiscount50';
		$i++;
		prnMsg(__('This item is already being Moved to 50% Discount procedure. Finish or delete this process first'),'error');
	}elseif ($MyRow['klmovingdiscount80'] == 1) {
		$InputError = 1;
		$Errors[$i] = 'MovingDiscount80';
		$i++;
		prnMsg(__('This item is already being Moved to 80% Discount procedure. Finish or delete this process first'),'error');
	}elseif ($MyRow['discontinued'] == 1) {
		$InputError = 1;
		$Errors[$i] = 'Discontinued';
		$i++;
		prnMsg(__('This item is already an Obsolete item. '),'error');
	}

	if (!isset($_POST['Stockid'])){
	  $_POST['Stockid']='';
	}
	if (!isset($_POST['NewRetailPrice'])){
	  $_POST['NewRetailPrice']=0;
	}

	if (isset($SelectedPriceChange) AND $InputError !=1) {

		/*SelectedPriceChange could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/

		$SQL = "UPDATE klchangeprice 
				SET stockid='" . $_POST['Stockid'] . "',
					startprocessdate = CURRENT_DATE,
					newretailprice='" . filter_number_format($_POST['NewRetailPrice']) . "',
					pricechanged=0,
					endprocessdate='1000-01-01'
				WHERE counterpricechange = '".$SelectedPriceChange."'";

		$Msg = __('KL Retail Price Change Step 01 record for') . ' ' . $_POST['Stockid'] . ' ' . __('has been updated');
	} elseif ($InputError !=1) {

		$SQL = "INSERT INTO klchangeprice 
						(stockid,
						startprocessdate,
						newretailprice,
						pricechanged,
						endprocessdate)
				VALUES ('" . $_POST['Stockid'] . "',
					CURRENT_DATE,
					'" . filter_number_format($_POST['NewRetailPrice']) . "',
					0,
					'1000-01-01')";
		$Msg = __('KL Retail Price Change Step 01 record for') . ' ' . $_POST['Stockid'] . ' ' . __('has been created');
	}
	if ($InputError !=1) {
		//run the SQL from either of the above possibilites
		DB_Txn_Begin();

		$ErrMsg = __('The insert or update of the KL Retail Price Change Step 01 failed because');
		$Result = DB_query($SQL,$ErrMsg);
		prnMsg($Msg , 'success');

		SetRLZeroAtPointOfSales($_POST['Stockid']);
		SetChangePriceFlag(1,$_POST['Stockid']);

		KLSendEmail("ChangePriceStarted", "Silent", $_POST['Stockid']);

		// check if there is stock in consignment, so we need to send an extra email to kantor team
		$SQL = "SELECT SUM(quantity)
				FROM locstock
				WHERE stockid ='". $_POST['Stockid'] ."' 
					AND loccode IN " . LIST_CONSIGNMENT_LOCATIONS ;
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);
		if ($MyRow[0] != 0){
			// send the email as there is some stock in consignment
			KLSendEmail("ChangePriceItemFromConsignment", "Silent", $_POST['Stockid']);
		}

		DB_Txn_Commit();

		unset($SelectedPriceChange);
		unset($_POST['Stockid']);
		unset($_POST['NewRetailPrice']);
	}

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button
	$SQL = "SELECT stockid
			FROM klchangeprice
			WHERE counterpricechange='".$SelectedPriceChange."'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	SetChangePriceFlag(0,$MyRow['stockid']);

	$SQL="DELETE FROM klchangeprice WHERE counterpricechange='". $SelectedPriceChange."'";
	$ErrMsg = __('The KL Retail Price Change Step 01 could not be deleted because');
	$Result = DB_query($SQL,$ErrMsg);

	prnMsg(__('KL Retail Price Change Step 01') . ' ' . $SelectedPriceChange . ' ' . __('has been deleted from the database'),'success');

	unset ($SelectedPriceChange);
	unset($Delete);

}

if (!isset($SelectedPriceChange)) {

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedPriceChange will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of Sales-persons will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$SQL = "SELECT counterpricechange,
				stockid,
				newretailprice,
				startprocessdate
			FROM klchangeprice
			WHERE endprocessdate = '1000-01-01'";
	$Result = DB_query($SQL);

	echo '<table class="selection">';
	echo '<thead>';
	echo '<tr>
			<th>' . __('#') . '</th>
			<th>' . __('Item Code') . '</th>
			<th>' . __('QOH Total') . '</th>
			<th>' . __('New Retail Price') . '</th>
			<th>' . __('Start Date') . '</th>
		</tr>';
	echo '</thead>';
	echo '<tbody>';
	$i=1;
	while ($MyRow=DB_fetch_array($Result)) {
		echo '<tr class="striped_row"><td class="number">'.$i.'</td>
				<td>'.$MyRow['stockid'].'</td>
				<td class="number">'.locale_number_format(ItemCodeQOH($MyRow['stockid'],'CODE_FULL', "ALL"),0).'</td>
				<td class="number">'.locale_number_format($MyRow['newretailprice'],0).'</td>
				<td>'.ConvertSQLDate($MyRow['startprocessdate']).'</td>
				<td><a href="'.htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8').'?SelectedPriceChange='.$MyRow['counterpricechange'].'">'. __('Edit').'</a></td>
				<td><a href="'.htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8').'?SelectedPriceChange='.$MyRow['counterpricechange'].'&amp;delete=1" onclick="return confirm(\''.__('Are you sure you wish to delete this price change?').'\');">'.__('Delete').'</a></td>
				</tr>';
		$i++;
	} //END WHILE LIST LOOP
	echo '</tbody>';
	echo '</table><br />';
} //end of ifs and buts!

if (isset($SelectedPriceChange)) {
	echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . __('Show All Current Price Changes') . '</a></div>';
}

if (! isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($SelectedPriceChange)) {
		//editing an existing Price Change

		$SQL = "SELECT counterpricechange,
					stockid,
					newretailprice
				FROM klchangeprice
				WHERE counterpricechange='".$SelectedPriceChange."'";

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

		$_POST['Stockid'] = $MyRow['stockid'];
		$_POST['NewRetailPrice']  = locale_number_format($MyRow['newretailprice'],0);


		echo '<input type="hidden" name="SelectedPriceChange" value="' . $SelectedPriceChange . '" />';
		echo '<input type="hidden" name="StockId" value="' . $_POST['Stockid'] . '" />';

	} else { //end of if $SelectedPriceChange only do the else when a new record is being entered

	}
	if (!isset($_POST['Stockid'])){
	  $_POST['Stockid']='';
	}
	if (!isset($_POST['NewRetailPrice'])){
	  $_POST['NewRetailPrice']=0;
	}

	echo '<fieldset><legend>' . __('Price Change') . '</legend>';
	echo FieldToSelectOneText('Stockid', $_POST['Stockid'], 20, 20, __('Item Code'), '', '', '', true, false);
	echo FieldToSelectOneText('NewRetailPrice', $_POST['NewRetailPrice'], 12, 11, __('New Retail Price'), '', 'class="number"', '', true, false);
	echo '</fieldset>';
	echo OneButtonCenteredForm('submit', __('Enter Price Change'));
	echo '</div>
		</form>';

} //end if record deleted no point displaying form to add record

include('includes/footer.php');

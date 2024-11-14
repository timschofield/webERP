<?php

include('includes/session.php');
$Title = _('KL Change of Retail Price -> Step 01');
include('includes/header.php');
include('includes/KLDefines.php');
include('includes/KLPrices.php');
include('includes/KLEmails.php');
include('includes/KLGeneralFunctions.php');
/* ASSIGN users to groups */
include ('includes/KLRoles.php');

if (isset($_GET['SelectedPriceChange'])){
	$SelectedPriceChange =mb_strtoupper($_GET['SelectedPriceChange']);
} elseif(isset($_POST['SelectedPriceChange'])){
	$SelectedPriceChange =mb_strtoupper($_POST['SelectedPriceChange']);
}

if (isset($Errors)) {
	unset($Errors);
}

$Errors = array();

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . _('KL Retail Price Change') . '" alt="" />' . ' ' . $Title.'</p>';

if (isset($_POST['submit'])) {
	
	$_POST['Stockid'] = strtoupper($_POST['Stockid']); // just in case it came in lowercase

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */
	$i=1;
	//first off validate inputs sensible round_price($myrow['retailprice'], "UP")
	
	$result = DB_query("SELECT klchangingprice, 
								klmovingdiscount20,
								klmovingdiscount50,
								klmovingdiscount80,
								categoryid, 
								discontinued 
						FROM stockmaster 
						WHERE stockid='" . $_POST['Stockid'] . "'");
	$myrow = DB_fetch_array($result);
	if (DB_num_rows($result)==0) {
		prnMsg( _('The entered item code does not exist'),'error');
		$InputError = 1;
		$Errors[$i] = 'StockId';
		$i++;
	}elseif ((ItemCodeQOH($_POST['Stockid'],'CODE_FULL', "ALL") != 0) 
			AND (GetTotalItemsChangingPrice() >= MAX_ITEMS_CHANGING_PRICE) 
			AND (!ItemInList($myrow['categoryid'], LIST_STOCK_CATEGORIES_SETUP))
			AND (!$KL_SystemAdmin)) {
		$InputError = 1;
		$Errors[$i] = 'MaxItemsChangingPrice';
		$i++;
		prnMsg(_('Too many items changing price at the same time. Maximum = '). MAX_ITEMS_CHANGING_PRICE,'error');
	}elseif (!is_numeric(filter_number_format($_POST['NewRetailPrice']))) {
		$InputError = 1;
		$Errors[$i] = 'NewRetailPrice';
		$i++;
		prnMsg(_('The new retail price must be a number'),'error');
	}elseif (!IsPriceRoundedOK($_POST['NewRetailPrice'])){
		$InputError = 1;
		$Errors[$i] = 'NewRetailPrice';
		$i++;
		prnMsg(_('The new retail price is not a correct rounded number.'),'error');
	}elseif ($myrow['klchangingprice'] == 1) {
		$InputError = 1;
		$Errors[$i] = 'klchangingprice';
		$i++;
		prnMsg(_('This item is already in changing price procedure.'),'error');
	}elseif ($myrow['klmovingdiscount20'] == 1) {
		$InputError = 1;
		$Errors[$i] = 'MovingDiscount20';
		$i++;
		prnMsg(_('This item is already in Move To 20% Discount procedure. Finish or delete this process first.'),'error');
	}elseif ($myrow['klmovingdiscount50'] == 1) {
		$InputError = 1;
		$Errors[$i] = 'MovingDiscount50';
		$i++;
		prnMsg(_('This item is already being Moved to 50% Discount procedure. Finish or delete this process first'),'error');
	}elseif ($myrow['klmovingdiscount80'] == 1) {
		$InputError = 1;
		$Errors[$i] = 'MovingDiscount80';
		$i++;
		prnMsg(_('This item is already being Moved to 80% Discount procedure. Finish or delete this process first'),'error');
	}elseif ($myrow['discontinued'] == 1) {
		$InputError = 1;
		$Errors[$i] = 'Discontinued';
		$i++;
		prnMsg(_('This item is already an Obsolete item. '),'error');
	}

	if (!isset($_POST['Stockid'])){
	  $_POST['Stockid']='';
	}
	if (!isset($_POST['NewRetailPrice'])){
	  $_POST['NewRetailPrice']=0;
	}

	if (isset($SelectedPriceChange) AND $InputError !=1) {

		/*SelectedPriceChange could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/

		$sql = "UPDATE klchangeprice 
				SET stockid='" . $_POST['Stockid'] . "',
					startprocessdate='" . Date('Y-m-d') . "',
					newretailprice='" . filter_number_format($_POST['NewRetailPrice']) . "',
					pricechanged=0,
					endprocessdate='0000-00-00'
				WHERE counterpricechange = '".$SelectedPriceChange."'";

		$msg = _('KL Retail Price Change Step 01 record for') . ' ' . $_POST['Stockid'] . ' ' . _('has been updated');
	} elseif ($InputError !=1) {

		$sql = "INSERT INTO klchangeprice 
						(stockid,
						startprocessdate,
						newretailprice,
						pricechanged,
						endprocessdate)
				VALUES ('" . $_POST['Stockid'] . "',
					'" . Date('Y-m-d') . "',
					'" . filter_number_format($_POST['NewRetailPrice']) . "',
					0,
					'0000-00-00')";
		$msg = _('KL Retail Price Change Step 01 record for') . ' ' . $_POST['Stockid'] . ' ' . _('has been created');
	}
	if ($InputError !=1) {
		//run the SQL from either of the above possibilites
		DB_Txn_Begin();

		$ErrMsg = _('The insert or update of the KL Retail Price Change Step 01 failed because');
		$DbgMsg = _('The SQL that was used and failed was');
		$result = DB_query($sql,$ErrMsg, $DbgMsg);
		prnMsg($msg , 'success');

		SetRLZeroAtPointOfSales($_POST['Stockid']);
		SetChangePriceFlag(1,$_POST['Stockid']);

		KLSendEmail("ChangePriceStarted", "Silent", $_POST['Stockid']);

		// check if there is stock in consignment, so we need to send an extra email to kantor team
		$sql = "SELECT SUM(quantity)
				FROM locstock
				WHERE stockid ='". $_POST['Stockid'] ."' 
					AND loccode IN " . LIST_CONSIGNMENT_LOCATIONS ;
		$result = DB_query($sql);
		$myrow = DB_fetch_array($result);
		if ($myrow[0] != 0){
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
	$sql = "SELECT stockid
			FROM klchangeprice
			WHERE counterpricechange='".$SelectedPriceChange."'";
	$result = DB_query($sql);
	$myrow = DB_fetch_array($result);
	SetChangePriceFlag(0,$myrow['stockid']);

	$sql="DELETE FROM klchangeprice WHERE counterpricechange='". $SelectedPriceChange."'";
	$ErrMsg = _('The KL Retail Price Change Step 01 could not be deleted because');
	$result = DB_query($sql,$ErrMsg);

	prnMsg(_('KL Retail Price Change Step 01') . ' ' . $SelectedPriceChange . ' ' . _('has been deleted from the database'),'success');

	unset ($SelectedPriceChange);
	unset($delete);

}

if (!isset($SelectedPriceChange)) {

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedPriceChange will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of Sales-persons will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$sql = "SELECT counterpricechange,
				stockid,
				newretailprice,
				startprocessdate
			FROM klchangeprice
			WHERE endprocessdate = '0000-00-00'";
	$result = DB_query($sql);

	echo '<table class="selection">';
	echo '<tr>
			<th>' . _('#') . '</th>
			<th>' . _('Item Code') . '</th>
			<th>' . _('QOH Total') . '</th>
			<th>' . _('New Retail Price') . '</th>
			<th>' . _('Start Date') . '</th>
		</tr>';
	$i=1;
	$k=0;
	while ($myrow=DB_fetch_array($result)) {
		$k = StartEvenOrOddRow($k);
		printf('<td class="number">%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td>%s</td>
				<td><a href="%sSelectedPriceChange=%s">'. _('Edit') . '</a></td>
				<td><a href="%sSelectedPriceChange=%s&amp;delete=1" onclick="return confirm(\'' . _('Are you sure you wish to delete this price change?') . '\');">' . _('Delete') . '</a></td>
				</tr>',
				$i,
				$myrow['stockid'],
				locale_number_format(ItemCodeQOH($myrow['stockid'],'CODE_FULL', "ALL"),0),
				locale_number_format($myrow['newretailprice'],0),
				ConvertSQLDate($myrow['startprocessdate']),
				htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
				$myrow['counterpricechange'],
				htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
				$myrow['counterpricechange']);
		$i++;
	} //END WHILE LIST LOOP
	echo '</table><br />';
} //end of ifs and buts!

if (isset($SelectedPriceChange)) {
	echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('Show All Current Price Changes') . '</a></div>';
}

if (! isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($SelectedPriceChange)) {
		//editing an existing Price Change

		$sql = "SELECT counterpricechange,
					stockid,
					newretailprice
				FROM klchangeprice
				WHERE counterpricechange='".$SelectedPriceChange."'";

		$result = DB_query($sql);
		$myrow = DB_fetch_array($result);

		$_POST['Stockid'] = $myrow['stockid'];
		$_POST['NewRetailPrice']  = locale_number_format($myrow['newretailprice'],0);


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

	echo '<tr>
			<td>' . _('Item Code') . ':</td>
			<td><input type="text" name="Stockid" size="20" maxlength="20" value="' . $_POST['Stockid'] . '" /></td>
		</tr>';
	echo '<tr>
			<td>' . _('New Retail Price') . ':</td>
			<td><input type="text" class="number" name="NewRetailPrice" size="12" maxlength="11" value="' . $_POST['NewRetailPrice'] . '" /></td>
		</tr>';

	echo '</select></td>
		</tr>
		</table>
		<br />
		<div class="centre">
			<input type="submit" name="submit" value="' . _('Enter Price Change') . '" />
		</div>
        </div>
		</form>';

} //end if record deleted no point displaying form to add record

include('includes/footer.php');
?>
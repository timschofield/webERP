<?php

include('includes/session.php');
$Title = _('Move Item To 20% Discount -> Step 01');
include('includes/header.php');
include('includes/KLDefines.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLPrices.php');
include('includes/KLEmails.php');

if (isset($_GET['SelectedMovement'])){
	$SelectedMovement =mb_strtoupper($_GET['SelectedMovement']);
} elseif(isset($_POST['SelectedMovement'])){
	$SelectedMovement =mb_strtoupper($_POST['SelectedMovement']);
}

if (isset($Errors)) {
	unset($Errors);
}

$Errors = array();

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . _('Move Item To 20% Discount Category') . '" alt="" />' . ' ' . $Title.'</p>';

if (isset($_POST['submit'])) {
	$_POST['Stockid'] = strtoupper($_POST['Stockid']); // just in case it came in lowercase

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */
	$i=1;

	//first off validate inputs sensible
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
		prnMsg( _('The entered item code does not exist'),'error');
		$InputError = 1;
		$Errors[$i] = 'StockId';
		$i++;
	}elseif ($_POST['DiscountCategory'] != '20') {
		$InputError = 1;
		$Errors[$i] = 'DiscountCategory';
		$i++;
		prnMsg(_('The Discount Type must be 20 (so far, only 20% discount available for Discount Category)'),'error');
	}elseif (GetTotalItemsMovingToDiscount('20') >= MAX_ITEMS_MOVING_DISC20) {
		$InputError = 1;
		$Errors[$i] = 'MaxItemsMovingToDiscount';
		$i++;
		prnMsg('Too many items moving to Discount 20% at the same time. Maximum = '. MAX_ITEMS_MOVING_DISC20,'error');
	}elseif (ItemInLIst($MyRow['categoryid'], LIST_STOCK_CATEGORIES_DISCOUNT_20)) {
		$InputError = 1;
		$Errors[$i] = 'AlreadyDiscount20';
		$i++;
		prnMsg(_('This item is already in 20% DISCOUNT category. No need to move it.'),'error');
	}elseif ($MyRow['klchangingprice'] == 1) {
		$InputError = 1;
		$Errors[$i] = 'ChangingPrice';
		$i++;
		prnMsg(_('This item is already in Change Price procedure. Finish or delete this process first'),'error');
	}elseif ($MyRow['klmovingdiscount20'] == 1) {
		$InputError = 1;
		$Errors[$i] = 'MovingDiscount20';
		$i++;
		prnMsg(_('This item is already in Move To 20% Discount procedure. No need to do it twice'),'error');
	}elseif ($MyRow['klmovingdiscount50'] == 1) {
		$InputError = 1;
		$Errors[$i] = 'MovingDiscount50';
		$i++;
		prnMsg(_('This item is already in Move To 50% Discount procedure. Finish or delete this process first.'),'error');
	}elseif ($MyRow['klmovingdiscount80'] == 1) {
		$InputError = 1;
		$Errors[$i] = 'MovingDiscount80';
		$i++;
		prnMsg(_('This item is already in Move To 80% Discount procedure. Finish or delete this process first.'),'error');
	}elseif ($MyRow['discontinued'] == 1) {
		$InputError = 1;
		$Errors[$i] = 'Discontinued';
		$i++;
		prnMsg(_('This item is already an Obsolete item. '),'error');
	}

	if (!isset($_POST['Stockid'])){
	  $_POST['Stockid']='';
	}
	if (!isset($_POST['DiscountCategory'])){
	  $_POST['DiscountCategory']='20';
	}

	if (isset($SelectedMovement) AND $InputError !=1) {
		/*SelectedMovement could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/
		$SQL = "UPDATE klmovetodiscount20 
				SET stockid='" . $_POST['Stockid'] . "',
					startprocessdate=CURRENT_DATE,
					discountcategory='20',
					endprocessdate='0000-00-00'
				WHERE countermovediscount = '".$SelectedMovement."'";

		$Msg = _('Move Item To 20% Discount Step 01 record for') . ' ' . $_POST['Stockid'] . ' ' . _('has been updated');
	} elseif ($InputError !=1) {

		$SQL = "INSERT INTO klmovetodiscount20 
						(stockid,
						startprocessdate,
						discountcategory,
						endprocessdate)
				VALUES ('" . $_POST['Stockid'] . "',
					CURRENT_DATE,
					'20',
					'0000-00-00')";
		$Msg = _('Move Item To 20% Discount Step 01 record for') . ' ' . $_POST['Stockid'] . ' ' . _('has been created');
	}
	if ($InputError !=1) {
		//run the SQL from either of the above possibilites
		DB_Txn_Begin();

		$ErrMsg = _('The insert or update of the Move Item To 20% Discount Step 01 failed because');
		$DbgMsg = _('The SQL that was used and failed was');
		$Result = DB_query($SQL,$ErrMsg, $DbgMsg);
		prnMsg($Msg , 'success');

		SetRLZeroAtPointOfSales($_POST['Stockid']);
		SetMoveDiscount20Flag(1,$_POST['Stockid']);

		KLSendEmail("MoveToDiscount20Started", "Silent", $_POST['Stockid']);

		// check if there is stock in consignment, so we need to send an extra email to kantor team
		$SQL = "SELECT SUM(quantity)
				FROM locstock
				WHERE stockid ='". $_POST['Stockid'] ."' 
					AND loccode IN " . LIST_CONSIGNMENT_LOCATIONS ;
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);
		if ($MyRow[0] != 0){
			// send the email as there is some stock in consignment
			KLSendEmail("MoveToDiscountFromConsignment", "Silent", $_POST['Stockid']);
		}

		DB_Txn_Commit();

		unset($SelectedMovement);
		unset($_POST['Stockid']);
		unset($_POST['DiscountCategory']);
	}

} elseif (isset($_GET['delete'])) {
	//the link to delete a selected record was clicked instead of the submit button
	$SQL = "SELECT stockid
			FROM klmovetodiscount20
			WHERE countermovediscount='".$SelectedMovement."'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	SetMoveDiscount20Flag(0,$MyRow['stockid']);

	$SQL="DELETE FROM klmovetodiscount20 WHERE countermovediscount='". $SelectedMovement."'";
	$ErrMsg = _('The Move Item To 20% Discount Step 01 could not be deleted because');
	$Result = DB_query($SQL,$ErrMsg);

	prnMsg(_('Move Item To 20% Discount Step 01') . ' ' . $SelectedMovement . ' ' . _('has been deleted from the database'),'success');

	unset($SelectedMovement);
	unset($Delete);

}

if (!isset($SelectedMovement)) {

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedMovement will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of Sales-persons will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$SQL = "SELECT klmovetodiscount20.countermovediscount,
				stockmaster.stockid,
				stockmaster.categoryid,
				klmovetodiscount20.discountcategory,
				klmovetodiscount20.startprocessdate
			FROM klmovetodiscount20,stockmaster
			WHERE stockmaster.stockid = klmovetodiscount20.stockid
				AND klmovetodiscount20.endprocessdate = '0000-00-00'";
	$Result = DB_query($SQL);

	echo '<table class="selection">';
	echo '<tr>
			<th>' . _('#') . '</th>
			<th>' . _('Item Code') . '</th>
			<th>' . _('From Category') . '</th>
			<th>' . _('Moving to Disc %') . '</th>
			<th>' . _('Start Date') . '</th>
		</tr>';
	$i=1;
	$k=0;
	while ($MyRow=DB_fetch_array($Result)) {
		$k = StartEvenOrOddRow($k);
		printf('<td class="number">%s</td>
				<td>%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				<td>%s</td>
				</tr>',
				$i,
				$MyRow['stockid'],
				GetCategoryNameFromCode($MyRow['categoryid']),
				$MyRow['discountcategory'],
				ConvertSQLDate($MyRow['startprocessdate']));
		$i++;
	} //END WHILE LIST LOOP
	echo '</table><br />';
} //end of ifs and buts!

if (isset($SelectedMovement)) {
	echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('Show All Active Movements To 20% Discount') . '</a></div>';
}

if (! isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($SelectedMovement)) {
		//editing an existing Price Change

		$SQL = "SELECT countermovediscount,
					stockid,
					discountcategory
				FROM klmovetodiscount20
				WHERE countermovediscount='".$SelectedMovement."'";

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

		$_POST['Stockid'] = $MyRow['stockid'];
		$_POST['DiscountCategory']  = $MyRow['discountcategory'];


		echo '<input type="hidden" name="SelectedMovement" value="' . $SelectedMovement . '" />';
		echo '<input type="hidden" name="StockId" value="' . $_POST['Stockid'] . '" />';

	} else { //end of if $SelectedMovement only do the else when a new record is being entered

	}
	if (!isset($_POST['Stockid'])){
	  $_POST['Stockid']='';
	}
	if (!isset($_POST['DiscountCategory'])){
	  $_POST['DiscountCategory']='20';
	}

	echo '<tr>
			<td>' . _('Item Code') . ':</td>
			<td><input type="text" name="Stockid" size="20" maxlength="20" value="' . $_POST['Stockid'] . '" /></td>
		</tr>';
	echo '<tr>
			<td>' . _('Discount Category') . ':</td>
			<td><input type="text" class="number" name="DiscountCategory" size="2" maxlength="2" value="' . $_POST['DiscountCategory'] . '" /></td>
		</tr>';

	echo '</select></td>
		</tr>
		</table>
		<br />
		<div class="centre">
			<input type="submit" name="submit" value="' . _('Start Change To 20% Discount') . '" />
		</div>
        </div>
		</form>';

} //end if record deleted no point displaying form to add record

include('includes/footer.php');
?>
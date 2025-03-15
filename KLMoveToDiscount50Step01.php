<?php

include('includes/session.php');
$Title = _('Move Item To 50% Discount -> Step 01');
include('includes/header.php');
include('includes/KLDefines.php');
include('includes/UIGeneralFunctions.php');
include('includes/KLUIGeneralFunctions.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLPrices.php');
include('includes/KLEmails.php');

if (isset($_GET['SelectedMovement'])){
	$SelectedMovement =mb_strtoupper($_GET['SelectedMovement']);
} elseif(isset($_POST['SelectedMovement'])){
	$SelectedMovement =mb_strtoupper($_POST['SelectedMovement']);
}

if (!isset($_POST['Stockid'])){
	$_POST['Stockid']='';
}

if (!isset($_POST['DiscountCategory'])){
	$_POST['DiscountCategory']='50';
}

if (isset($Errors)) {
	unset($Errors);
}

$Errors = array();

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . $Title . '" alt="" />' . ' ' . $Title. '</p>';

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
	}elseif ($_POST['DiscountCategory'] != '50') {
		$InputError = 1;
		$Errors[$i] = 'DiscountCategory';
		$i++;
		prnMsg(_('The Discount Type must be 50 (so far, only 50% discount available for Discount Category)'),'error');
	}elseif (GetTotalItemsMovingToDiscount('50') >= MAX_ITEMS_MOVING_DISC50) {
		$InputError = 1;
		$Errors[$i] = 'MaxItemsMovingToDiscount';
		$i++;
		prnMsg('Too many items moving to Discount 50% at the same time. Maximum = '. MAX_ITEMS_MOVING_DISC50,'error');
	}elseif (ItemInLIst($MyRow['categoryid'], LIST_STOCK_CATEGORIES_DISCOUNT_50)) {
		$InputError = 1;
		$Errors[$i] = 'AlreadyDiscount50';
		$i++;
		prnMsg(_('This item is already in 50% DISCOUNT category. No need to move it.'),'error');
	}elseif ($MyRow['klchangingprice'] == 1) {
		$InputError = 1;
		$Errors[$i] = 'ChangingPrice';
		$i++;
		prnMsg(_('This item is already in Change Price procedure. Finish or delete this process first'),'error');
	}elseif ($MyRow['klmovingdiscount50'] == 1) {
		$InputError = 1;
		$Errors[$i] = 'MovingDiscount50';
		$i++;
		prnMsg(_('This item is already in Move To 50% Discount procedure. No need to do it twice'),'error');
	}elseif ($MyRow['klmovingdiscount20'] == 1) {
		$InputError = 1;
		$Errors[$i] = 'MovingDiscount20';
		$i++;
		prnMsg(_('This item is already in Move To 20% Discount procedure. Finish or delete this process first.'),'error');
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

	if (isset($SelectedMovement) AND $InputError !=1) {
		/*SelectedMovement could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/
		$SQL = "UPDATE klmovetodiscount50 
				SET stockid='" . $_POST['Stockid'] . "',
					startprocessdate = CURRENT_DATE,
					discountcategory='50',
					endprocessdate='1000-01-01'
				WHERE countermovediscount = '".$SelectedMovement."'";

		$Msg = _('Move Item To 50% Discount Step 01 record for') . ' ' . $_POST['Stockid'] . ' ' . _('has been updated');
	} elseif ($InputError !=1) {

		$SQL = "INSERT INTO klmovetodiscount50 
						(stockid,
						startprocessdate,
						discountcategory,
						endprocessdate)
				VALUES ('" . $_POST['Stockid'] . "',
					CURRENT_DATE,
					'50',
					'1000-01-01')";
		$Msg = _('Move Item To 50% Discount Step 01 record for') . ' ' . $_POST['Stockid'] . ' ' . _('has been created');
	}
	if ($InputError !=1) {
		//run the SQL from either of the above possibilites
		DB_Txn_Begin();

		$ErrMsg = _('The insert or update of the Move Item To 50% Discount Step 01 failed because');
		$DbgMsg = _('The SQL that was used and failed was');
		$Result = DB_query($SQL,$ErrMsg, $DbgMsg);
		prnMsg($Msg , 'success');

		SetRLZeroAtPointOfSales($_POST['Stockid']);
		SetMoveDiscount50Flag(1,$_POST['Stockid']);

		KLSendEmail("MoveToDiscount50Started", "Silent", $_POST['Stockid']);

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
			FROM klmovetodiscount50
			WHERE countermovediscount='".$SelectedMovement."'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	SetMoveDiscount50Flag(0,$MyRow['stockid']);

	$SQL="DELETE FROM klmovetodiscount50 
		WHERE countermovediscount='". $SelectedMovement."'";
	$ErrMsg = _('The Move Item To 50% Discount Step 01 could not be deleted because');
	$Result = DB_query($SQL,$ErrMsg);

	prnMsg(_('Move Item To 50% Discount Step 01') . ' ' . $SelectedMovement . ' ' . _('has been deleted from the database'),'success');

	unset($SelectedMovement);
	unset($Delete);

}

if (!isset($SelectedMovement)) {

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedMovement will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of Sales-persons will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$SQL = "SELECT klmovetodiscount50.countermovediscount,
				stockmaster.stockid,
				stockmaster.categoryid,
				klmovetodiscount50.discountcategory,
				klmovetodiscount50.startprocessdate
			FROM klmovetodiscount50,stockmaster
			WHERE stockmaster.stockid = klmovetodiscount50.stockid
				AND klmovetodiscount50.endprocessdate = '1000-01-01'";
	$Result = DB_query($SQL);

	echo '<table class="selection">';
	echo '<thead>
			<tr>
				<th>' . _('#') . '</th>
				<th>' . _('Item Code') . '</th>
				<th>' . _('From Category') . '</th>
				<th>' . _('Moving to Disc %') . '</th>
				<th>' . _('Start Date') . '</th>
				<th>' . _('Action') . '</th>
			</tr>
		</thead>
		<tbody>';
	$i=1;
	while ($MyRow=DB_fetch_array($Result)) {
		echo '<tr class="striped_row">
				<td class="number">' . $i . '</td>
				<td>' . $MyRow['stockid'] . '</td>
				<td>' . GetCategoryNameFromCode($MyRow['categoryid']) . '</td>
				<td class="number">' . $MyRow['discountcategory'] . '</td>
				<td>' . ConvertSQLDate($MyRow['startprocessdate']) . '</td>
				<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?delete=yes&amp;SelectedMovement=' . $MyRow['countermovediscount'] . '" onclick="return confirm(\'' . _('Are you sure you wish to delete this movement?') . '\');">' . _('Delete') . '</a></td>
				</tr>';
		$i++;
	} //END WHILE LIST LOOP
	echo '</tbody></table><br />';
} //end of ifs and buts!

if (isset($SelectedMovement)) {
	echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('Show All Active Movements To 50% Discount') . '</a></div>';
}

$_POST['Stockid']='';
$_POST['DiscountCategory']='50';

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (isset($SelectedMovement)) {
	//editing an existing Price Change

	$SQL = "SELECT countermovediscount,
				stockid,
				discountcategory
			FROM klmovetodiscount50
			WHERE countermovediscount='".$SelectedMovement."'";

	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);

	$_POST['Stockid'] = $MyRow['stockid'];
	$_POST['DiscountCategory']  = $MyRow['discountcategory'];


	echo '<input type="hidden" name="SelectedMovement" value="' . $SelectedMovement . '" />';
	echo '<input type="hidden" name="StockId" value="' . $_POST['Stockid'] . '" />';

}

echo '<fieldset>
		<legend>' . _('Item to move to 50% discount') . '</legend>';
echo FieldToSelectOneText('Stockid', $_POST['Stockid'], 20, 20, _('Item Code'), '', '', '', true, false);
echo FixedField('DiscountCategory', $_POST['DiscountCategory'], _('Discount Category'), '');
echo OneButtonCenteredForm('submit', _('Start Change To 50% Discount'));
echo '</fieldset>
	</form>';


include('includes/footer.php');
?>
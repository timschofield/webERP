<?php

// NB: these classes are not autoloaded, and their definition has to be included before the session is started (in session.php)
include('includes/DefineStockAdjustment.php');
include('includes/DefineSerialItems.php');

require(__DIR__ . '/includes/session.php');

$Title = __('Stock Adjustments');
$ViewTopic = 'Inventory';
$BookMark = 'InventoryAdjustments';
include('includes/header.php');

include('includes/SQL_CommonFunctions.php');
include('includes/GLFunctions.php');

if (empty($_GET['identifier'])) {
	/*unique session identifier to ensure that there is no conflict with other adjustment sessions on the same machine  */
	$identifier=date('U');
} else {
	$identifier=$_GET['identifier'];
}

if (isset($_GET['NewAdjustment'])){
	unset($_SESSION['Adjustment' . $identifier]);
	$_SESSION['Adjustment' . $identifier] = new StockAdjustment();
}

if (!isset($_SESSION['Adjustment' . $identifier])){
	$_SESSION['Adjustment' . $identifier] = new StockAdjustment();
}

$NewAdjustment = false;

if (isset($_GET['StockID'])){
	$NewAdjustment = true;
	$StockID = trim(mb_strtoupper($_GET['StockID']));
} elseif (isset($_POST['StockID'])){
	if($_POST['StockID'] != $_SESSION['Adjustment' . $identifier]->StockID){
		$NewAdjustment = true;
		$StockID = trim(mb_strtoupper($_POST['StockID']));
	}
}

if ($NewAdjustment==true){

	$_SESSION['Adjustment' . $identifier]->StockID = trim(mb_strtoupper($StockID));
	$Result = DB_query("SELECT description,
							controlled,
							serialised,
							decimalplaces,
							perishable,
							actualcost AS totalcost,
							units
						FROM stockmaster
						WHERE stockid='" . $_SESSION['Adjustment' . $identifier]->StockID . "'");
	$MyRow = DB_fetch_array($Result);
	$_SESSION['Adjustment' . $identifier]->ItemDescription = $MyRow['description'];
	$_SESSION['Adjustment' . $identifier]->Controlled = $MyRow['controlled'];
	$_SESSION['Adjustment' . $identifier]->Serialised = $MyRow['serialised'];
	$_SESSION['Adjustment' . $identifier]->DecimalPlaces = $MyRow['decimalplaces'];
	$_SESSION['Adjustment' . $identifier]->SerialItems = array();
	if (!isset($_SESSION['Adjustment' . $identifier]->Quantity) OR !is_numeric($_SESSION['Adjustment' . $identifier]->Quantity)){
		$_SESSION['Adjustment' . $identifier]->Quantity=0;
	}

	$_SESSION['Adjustment' . $identifier]->PartUnit = $MyRow['units'];
	$_SESSION['Adjustment' . $identifier]->StandardCost = $MyRow['totalcost'];
	$DecimalPlaces = $MyRow['decimalplaces'];
	DB_free_result($Result);


} //end if it's a new adjustment
if (isset($_POST['tag'])){
	$_SESSION['Adjustment' . $identifier]->Tag = $_POST['tag'];
}
if (isset($_POST['Narrative'])){
	$_SESSION['Adjustment' . $identifier]->Narrative = $_POST['Narrative'];
}

$SQL = "SELECT locations.loccode, locationname FROM locations INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canupd=1";
$ResultStkLocs = DB_query($SQL);
$LocationList=array();
while ($MyRow=DB_fetch_array($ResultStkLocs)){
	$LocationList[$MyRow['loccode']]=$MyRow['locationname'];
}

if (isset($_POST['StockLocation'])){
	if($_SESSION['Adjustment' . $identifier]->StockLocation != $_POST['StockLocation']){/* User has changed the stock location, so the serial no must be validated again */
		$_SESSION['Adjustment' . $identifier]->SerialItems = array();
	}
	$_SESSION['Adjustment' . $identifier]->StockLocation = $_POST['StockLocation'];
}else{
	if(empty($_SESSION['Adjustment' . $identifier]->StockLocation)){
		if(empty($_SESSION['UserStockLocation'])){
			$_SESSION['Adjustment' . $identifier]->StockLocation=key(reset($LocationList));
		}else{
			$_SESSION['Adjustment' . $identifier]->StockLocation=$_SESSION['UserStockLocation'];
		}
	}
}
if (isset($_POST['Quantity'])){
	if ($_POST['Quantity']=='' OR !is_numeric(filter_number_format($_POST['Quantity']))){
		$_POST['Quantity']=0;
	}
} else {
	$_POST['Quantity']=0;
}
if($_POST['Quantity'] != 0){//To prevent from serilised quantity changing to zero
	$_SESSION['Adjustment' . $identifier]->Quantity = filter_number_format($_POST['Quantity']);
	if(count($_SESSION['Adjustment' . $identifier]->SerialItems) == 0 AND $_SESSION['Adjustment' . $identifier]->Controlled == 1 ){/* There is no quantity available for controlled items */
		$_SESSION['Adjustment' . $identifier]->Quantity = 0;
	}
}
if(isset($_GET['OldIdentifier'])){
	$_SESSION['Adjustment'.$identifier]->StockLocation=$_SESSION['Adjustment'.$_GET['OldIdentifier']]->StockLocation;
}

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/supplier.png" title="' . __('Inventory Adjustment') . '" alt="" />' . ' ' . __('Inventory Adjustment') . '</p>';

if (isset($_POST['CheckCode'])) {

	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/magnifier.png" title="' . __('Dispatch') . '" alt="" />' . ' ' . __('Select Item to Adjust') . '</p>';

	if (mb_strlen($_POST['StockText'])>0) {
		$SQL="SELECT stockid,
					description
				FROM stockmaster
				WHERE description " . LIKE . " '%" . $_POST['StockText'] ."%'";
	} else {
		$SQL="SELECT stockid,
					description
				FROM stockmaster
				WHERE stockid " . LIKE  . " '%" . $_POST['StockCode'] ."%'";
	}
	$ErrMsg = __('The stock information cannot be retrieved because');
	$Result = DB_query($SQL, $ErrMsg);
	echo '<table class="selection">
			<tr>
				<th>' . __('Stock Code') . '</th>
				<th>' . __('Stock Description') . '</th>
			</tr>';
	while ($MyRow = DB_fetch_row($Result)) {
		echo '<tr>
				<td>' . $MyRow[0] . '</td>
				<td>' . $MyRow[1] . '</td>
				<td><a href="' . $RootPath . '/StockAdjustments.php?StockID='.$MyRow[0].'&amp;Description='.$MyRow[1].'&amp;OldIdentifier='.$identifier.'">' . __('Adjust') . '</a>
			</tr>';
	}
	echo '</table>';
	include('includes/footer.php');
	exit();
}

if (isset($_POST['EnterAdjustment']) AND $_POST['EnterAdjustment']!= ''){

	$InputError = false; /*Start by hoping for the best */
	$Result = DB_query("SELECT * FROM stockmaster WHERE stockid='" . $_SESSION['Adjustment' . $identifier]->StockID . "'");
	$MyRow = DB_fetch_row($Result);
	if (DB_num_rows($Result)==0) {
		prnMsg( __('The entered item code does not exist'),'error');
		$InputError = true;
	} elseif (!is_numeric($_SESSION['Adjustment' . $identifier]->Quantity)){
		prnMsg( __('The quantity entered must be numeric'),'error');
		$InputError = true;
	} elseif(strlen(substr(strrchr($_SESSION['Adjustment'.$identifier]->Quantity, "."), 1))>$_SESSION['Adjustment' . $identifier]->DecimalPlaces){
		prnMsg(__('The decimal places input is more than the decimals of this item defined,the defined decimal places is ').' '.$_SESSION['Adjustment' . $identifier]->DecimalPlaces.' '.__('and the input decimal places is ').' '.strlen(substr(strrchr($_SESSION['Adjustment'.$identifier]->Quantity, "."), 1)),'error');
		$InputError = true;
	} elseif ($_SESSION['Adjustment' . $identifier]->Quantity==0){
		prnMsg( __('The quantity entered cannot be zero') . '. ' . __('There would be no adjustment to make'),'error');
		$InputError = true;
	} elseif ($_SESSION['Adjustment' . $identifier]->Controlled==1 AND count($_SESSION['Adjustment' . $identifier]->SerialItems)==0) {
		prnMsg( __('The item entered is a controlled item that requires the detail of the serial numbers or batch references to be adjusted to be entered'),'error');
		$InputError = true;
	}

	if ($_SESSION['ProhibitNegativeStock']==1){
		$SQL = "SELECT quantity FROM locstock
				WHERE stockid='" . $_SESSION['Adjustment' . $identifier]->StockID . "'
				AND loccode='" . $_SESSION['Adjustment' . $identifier]->StockLocation . "'";
		$CheckNegResult = DB_query($SQL);
		$CheckNegRow = DB_fetch_array($CheckNegResult);
		if ($CheckNegRow['quantity']+$_SESSION['Adjustment' . $identifier]->Quantity <0){
			$InputError=true;
			prnMsg(__('The system parameters are set to prohibit negative stocks. Processing this stock adjustment would result in negative stock at this location. This adjustment will not be processed.'),'error');
		}
	}

	if (!$InputError) {

/*All inputs must be sensible so make the stock movement records and update the locations stocks */

		$AdjustmentNumber = GetNextTransNo(17);
		$PeriodNo = GetPeriod (Date($_SESSION['DefaultDateFormat']));
		$SQLAdjustmentDate = FormatDateForSQL(Date($_SESSION['DefaultDateFormat']));

		DB_Txn_Begin();

		// Need to get the current location quantity will need it later for the stock movement
		$SQL="SELECT locstock.quantity
			FROM locstock
			WHERE locstock.stockid='" . $_SESSION['Adjustment' . $identifier]->StockID . "'
			AND loccode= '" . $_SESSION['Adjustment' . $identifier]->StockLocation . "'";
		$Result = DB_query($SQL);
		if (DB_num_rows($Result)==1){
			$LocQtyRow = DB_fetch_row($Result);
			$QtyOnHandPrior = $LocQtyRow[0];
		} else {
			// There must actually be some error this should never happen
			$QtyOnHandPrior = 0;
		}
		$SQL = "INSERT INTO stockmoves (stockid,
										type,
										transno,
										loccode,
										trandate,
										userid,
										prd,
										reference,
										qty,
										newqoh,
										standardcost,
										narrative)
									VALUES ('" . $_SESSION['Adjustment' . $identifier]->StockID . "',
										17,
										'" . $AdjustmentNumber . "',
										'" . $_SESSION['Adjustment' . $identifier]->StockLocation . "',
										'" . $SQLAdjustmentDate . "',
										'" . $_SESSION['UserID'] . "',
										'" . $PeriodNo . "',
										'" . $_SESSION['Adjustment' . $identifier]->Narrative ."',
										'" . $_SESSION['Adjustment' . $identifier]->Quantity . "',
										'" . ($QtyOnHandPrior + $_SESSION['Adjustment' . $identifier]->Quantity) . "',
										'" . $_SESSION['Adjustment' . $identifier]->StandardCost . "',
										'')";

		$ErrMsg =  __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The stock movement record cannot be inserted because');
		$Result = DB_query($SQL, $ErrMsg, '', true);

/*Get the ID of the StockMove... */
		$StkMoveNo = DB_Last_Insert_ID('stockmoves','stkmoveno');

/*Insert the StockSerialMovements and update the StockSerialItems  for controlled items*/

		if ($_SESSION['Adjustment' . $identifier]->Controlled ==1){
			foreach($_SESSION['Adjustment' . $identifier]->SerialItems as $Item){
			/*We need to add or update the StockSerialItem record and
			The StockSerialMoves as well */

				/*First need to check if the serial items already exists or not */
				$SQL = "SELECT COUNT(*)
						FROM stockserialitems
						WHERE stockid='" . $_SESSION['Adjustment' . $identifier]->StockID . "'
						AND loccode='" . $_SESSION['Adjustment' . $identifier]->StockLocation . "'
						AND serialno='" . $Item->BundleRef . "'";
				$ErrMsg = __('Unable to determine if the serial item exists');
				$Result = DB_query($SQL, $ErrMsg);
				$SerialItemExistsRow = DB_fetch_row($Result);

				if ($SerialItemExistsRow[0]==1){

					$SQL = "UPDATE stockserialitems SET quantity= quantity + " . $Item->BundleQty . "
							WHERE stockid='" . $_SESSION['Adjustment' . $identifier]->StockID . "'
							AND loccode='" . $_SESSION['Adjustment' . $identifier]->StockLocation . "'
							AND serialno='" . $Item->BundleRef . "'";

					$ErrMsg =  __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The serial stock item record could not be updated because');
					$Result = DB_query($SQL, $ErrMsg, '', true);
				} else {
					/*Need to insert a new serial item record */
					$SQL = "INSERT INTO stockserialitems (stockid,
														loccode,
														serialno,
														qualitytext,
														quantity,
														expirationdate)
											VALUES ('" . $_SESSION['Adjustment' . $identifier]->StockID . "',
											'" . $_SESSION['Adjustment' . $identifier]->StockLocation . "',
											'" . $Item->BundleRef . "',
											'',
											'" . $Item->BundleQty . "',
											'" . FormatDateForSQL($Item->ExpiryDate) ."')";

					$ErrMsg =  __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The serial stock item record could not be updated because');
					$Result = DB_query($SQL, $ErrMsg, '', true);
				}

				/* now insert the serial stock movement */

				$SQL = "INSERT INTO stockserialmoves (stockmoveno,
													stockid,
													serialno,
													moveqty)
										VALUES ('" . $StkMoveNo . "',
											'" . $_SESSION['Adjustment' . $identifier]->StockID . "',
											'" . $Item->BundleRef . "',
											'" . $Item->BundleQty . "')";
				$ErrMsg =  __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The serial stock movement record could not be inserted because');
				$Result = DB_query($SQL, $ErrMsg, '', true);

			}/* foreach controlled item in the serialitems array */
		} /*end if the adjustment item is a controlled item */

		$SQL = "UPDATE locstock SET quantity = quantity + " . floatval($_SESSION['Adjustment' . $identifier]->Quantity) . "
				WHERE stockid='" . $_SESSION['Adjustment' . $identifier]->StockID . "'
				AND loccode='" . $_SESSION['Adjustment' . $identifier]->StockLocation . "'";

		$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' .__('The location stock record could not be updated because');
		$Result = DB_query($SQL, $ErrMsg, '', true);

		if ($_SESSION['CompanyRecord']['gllink_stock']==1 AND $_SESSION['Adjustment' . $identifier]->StandardCost > 0){

			$StockGLCodes = GetStockGLCode($_SESSION['Adjustment' . $identifier]->StockID);

			$SQL = "INSERT INTO gltrans (type,
										typeno,
										trandate,
										periodno,
										account,
										amount,
										narrative)
								VALUES (17,
									'" .$AdjustmentNumber . "',
									'" . $SQLAdjustmentDate . "',
									'" . $PeriodNo . "',
									'" .  $StockGLCodes['adjglact'] . "',
									'" . round($_SESSION['Adjustment' . $identifier]->StandardCost * -($_SESSION['Adjustment' . $identifier]->Quantity), $_SESSION['CompanyRecord']['decimalplaces']) . "',
									'" . mb_substr($_SESSION['Adjustment' . $identifier]->StockID . " x " . $_SESSION['Adjustment' . $identifier]->Quantity . " @ " .
										$_SESSION['Adjustment' . $identifier]->StandardCost . " " . $_SESSION['Adjustment' . $identifier]->Narrative, 0, 200) . "')";

			$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The general ledger transaction entries could not be added because');
			$Result = DB_query($SQL, $ErrMsg, '', true);
			InsertGLTags($_POST['tag']);

			$SQL = "INSERT INTO gltrans (type,
										typeno,
										trandate,
										periodno,
										account,
										amount,
										narrative)
								VALUES (17,
									'" .$AdjustmentNumber . "',
									'" . $SQLAdjustmentDate . "',
									'" . $PeriodNo . "',
									'" .  $StockGLCodes['stockact'] . "',
									'" . round($_SESSION['Adjustment' . $identifier]->StandardCost * $_SESSION['Adjustment' . $identifier]->Quantity,$_SESSION['CompanyRecord']['decimalplaces']) . "',
									'" . mb_substr($_SESSION['Adjustment' . $identifier]->StockID . ' x ' . $_SESSION['Adjustment' . $identifier]->Quantity . ' @ ' . $_SESSION['Adjustment' . $identifier]->StandardCost . ' ' . $_SESSION['Adjustment' . $identifier]->Narrative, 0, 200) . "'
									)";

			$Errmsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The general ledger transaction entries could not be added because');
			$Result = DB_query($SQL, $ErrMsg, '',true);
		}

		EnsureGLEntriesBalance(17, $AdjustmentNumber);

		DB_Txn_Commit();
		$AdjustReason = $_SESSION['Adjustment' . $identifier]->Narrative?  __('Narrative') . ' ' . $_SESSION['Adjustment' . $identifier]->Narrative:'';
		$ConfirmationText = __('A stock adjustment for'). ' ' . $_SESSION['Adjustment' . $identifier]->StockID . ' -  ' . $_SESSION['Adjustment' . $identifier]->ItemDescription . ' '.__('has been created from location').' ' . $_SESSION['Adjustment' . $identifier]->StockLocation .' '. __('for a quantity of') . ' ' . locale_number_format($_SESSION['Adjustment' . $identifier]->Quantity,$_SESSION['Adjustment' . $identifier]->DecimalPlaces) . ' ' . $AdjustReason;
		prnMsg( $ConfirmationText,'success');

		if ($_SESSION['InventoryManagerEmail']!=''){
			$ConfirmationText = $ConfirmationText . ' ' . __('by user') . ' ' . $_SESSION['UserID'] . ' ' . __('at') . ' ' . Date('Y-m-d H:i:s');
			$EmailSubject = __('Stock adjustment for'). ' ' . $_SESSION['Adjustment' . $identifier]->StockID;
			SendEmailFromWebERP($SysAdminEmail,
								$_SESSION['InventoryManagerEmail'],
								$EmailSubject,
								$ConfirmationText,
								'',
								false);

		}
		$StockID = $_SESSION['Adjustment' . $identifier]->StockID;
		unset ($_SESSION['Adjustment' . $identifier]);
	} /* end if there was no input error */

}/* end if the user hit enter the adjustment */


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier=' . urlencode($identifier) . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (!isset($_SESSION['Adjustment' . $identifier])) {
	$StockID='';
	$Controlled= 0;
	$Quantity = 0;
	$DecimalPlaces =2;
} else {
	$StockID = $_SESSION['Adjustment' . $identifier]->StockID;
	$Controlled = $_SESSION['Adjustment' . $identifier]->Controlled;
	$Quantity = $_SESSION['Adjustment' . $identifier]->Quantity;
	$SQL="SELECT actualcost,
				units,
				decimalplaces
			FROM stockmaster
			WHERE stockid='".$StockID."'";

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) > 0) {
		$MyRow=DB_fetch_array($Result);
		$_SESSION['Adjustment' . $identifier]->PartUnit=$MyRow['units'];
		$_SESSION['Adjustment' . $identifier]->StandardCost=$MyRow['actualcost'];
		$DecimalPlaces = $MyRow['decimalplaces'];
	}
}
echo '<fieldset>
		<legend>' . __('Adjustment Details') . '</legend>';
if (!isset($_GET['Description'])) {
	$_GET['Description']='';
}
echo '<field>
		<label for="StockID">' .  __('Stock Code'). ':</label>';
if (isset($StockID)) {
	echo '<input type="text" name="StockID" size="21" value="' . $StockID . '" maxlength="20" /></field>';
} else {
	echo '<input type="text" name="StockID" size="21" value="" maxlength="20" /></field>';
}
echo '<field>
		<label>' .  __('Partial Description'). ':</label>
		<input type="text" name="StockText" size="21" value="' . $_GET['Description'] .'" />&nbsp; &nbsp;'.__('Partial Stock Code'). ':';
if (isset($StockID)) {
	echo '<input type="text" name="StockCode" size="21" value="' . $StockID .'" maxlength="20" />';
} else {
	echo '<input type="text" name="StockCode" size="21" value="" maxlength="20" />';
}
echo '<input type="submit" name="CheckCode" value="'.__('Check Part').'" />
	</field>';

if (isset($_SESSION['Adjustment' . $identifier]) AND mb_strlen($_SESSION['Adjustment' . $identifier]->ItemDescription)>1){
	echo '<field>
			<td colspan="3"><h3>' . $_SESSION['Adjustment' . $identifier]->ItemDescription . ' ('.__('In Units of').' ' . $_SESSION['Adjustment' . $identifier]->PartUnit . ' ) - ' . __('Unit Cost').' = ' . locale_number_format($_SESSION['Adjustment' . $identifier]->StandardCost,4) . '</h3></td>
		</field>';
}

echo '<field>
		<label for="StockLocation">'. __('Adjustment to Stock At Location').':</label>
		<select name="StockLocation" onchange="submit();"> ';
foreach ($LocationList as $Loccode=>$Locationname){
	if (isset($_SESSION['Adjustment'.$identifier]->StockLocation) AND $Loccode == $_SESSION['Adjustment' . $identifier]->StockLocation){
		 echo '<option selected="selected" value="' . $Loccode . '">' . $Locationname . '</option>';
	} else {
		 echo '<option value="' . $Loccode . '">' . $Locationname . '</option>';
	}
}

echo '</select>
	</field>';
if (isset($_SESSION['Adjustment' . $identifier]) AND !isset($_SESSION['Adjustment' . $identifier]->Narrative)) {
	$_SESSION['Adjustment' . $identifier]->Narrative = '';
	$Narrative ='';
} elseif(isset($_SESSION['Adjustment'.$identifier]->Narrative)) {
	$Narrative = $_SESSION['Adjustment'.$identifier]->Narrative;
} else {
	$Narrative ='';
}

echo '<field>
		<label for="Narrative">' .  __('Comments On Why').':</label>
		<input type="text" name="Narrative" size="32" onchange="submit()" maxlength="100" value="' . $Narrative . '" />
	</field>';

echo '<field>
		<label for="Quantity">' . __('Adjustment Quantity').':</label>';

if ($Controlled==1){
		if ($_SESSION['Adjustment' . $identifier]->StockLocation == ''){
			$_SESSION['Adjustment' . $identifier]->StockLocation = $_SESSION['UserStockLocation'];
		}
		echo '<input type="hidden" name="Quantity" value="' . $_SESSION['Adjustment' . $identifier]->Quantity . '" />
				'.locale_number_format($_SESSION['Adjustment' . $identifier]->Quantity,$DecimalPlaces) .' &nbsp; &nbsp; &nbsp; &nbsp;
				[<a href="'.$RootPath.'/StockAdjustmentsControlled.php?AdjType=REMOVE&identifier='.$identifier.'">' . __('Remove') . '</a>]
				[<a href="'.$RootPath.'/StockAdjustmentsControlled.php?AdjType=ADD&identifier='.$identifier.'">' . __('Add') . '</a>]';
} else {
	if (!isset($DecimalPlaces)) {
		$DecimalPlaces = 2;
	}
	echo '<input type="text" class="number" name="Quantity" size="12" maxlength="12" value="' . locale_number_format($Quantity,$DecimalPlaces) . '" />';
}
echo '</field>';

//Select the tag
$SQL = "SELECT tagref,
				tagdescription
		FROM tags
		ORDER BY tagref";
$Result = DB_query($SQL);
echo '<field>
		<label for="tag">', __('Tag'), '</label>
		<select multiple="multiple" name="tag[]">';
while ($MyRow = DB_fetch_array($Result)) {
	if (isset($_POST['tag']) and in_array($MyRow['tagref'], $_POST['tag'])) {
		echo '<option selected="selected" value="' . $MyRow['tagref'] . '">' . $MyRow['tagref'] . ' - ' . $MyRow['tagdescription'] . '</option>';
	} else {
		echo '<option value="' . $MyRow['tagref'] . '">' . $MyRow['tagref'] . ' - ' . $MyRow['tagdescription'] . '</option>';
	}
}
echo '</select>
	</field>';
// End select tag

echo '</fieldset>
	<div class="centre">
	<input type="submit" name="EnterAdjustment" value="'. __('Enter Stock Adjustment'). '" />';

if (!isset($_POST['StockLocation'])) {
	$_POST['StockLocation']='';
}

echo '<br />
	<a href="'. $RootPath. '/StockStatus.php?StockID='. $StockID . '">' . __('Show Stock Status') . '</a>';
echo '<br />
	<a href="'.$RootPath.'/StockMovements.php?StockID=' . $StockID . '">' . __('Show Movements') . '</a>';
echo '<br />
	<a href="'.$RootPath.'/StockUsage.php?StockID=' . $StockID . '&amp;StockLocation=' . $_POST['StockLocation'] . '">' . __('Show Stock Usage') . '</a>';
echo '<br />
	<a href="'.$RootPath.'/SelectSalesOrder.php?SelectedStockItem='. $StockID .'&amp;StockLocation=' . $_POST['StockLocation'] . '">' .  __('Search Outstanding Sales Orders') . '</a>';
echo '<br />
	<a href="'.$RootPath.'/SelectCompletedOrder.php?SelectedStockItem=' . $StockID .'">' . __('Search Completed Sales Orders') . '</a>';

echo '</div>
	</form>';
include('includes/footer.php');

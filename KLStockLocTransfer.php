<?php

/**************************************************************************************
KL RICARD Clean up of StockLocTransfer so SPG can create transfers from their shop to KANTO
***************************************************************************************/

/* Inventory Transfer - Bulk Dispatch */

include('includes/session.php');
$Title = _('Return Transfer from Shop to Kantor');
$BookMark = "LocationTransfers";
$ViewTopic = "Inventory";
include('includes/header.php');
include('includes/SQL_CommonFunctions.inc');
include('includes/KLGeneralFunctions.php');
include('includes/KLDefines.php');
include('includes/KLEmails.php');

if (isset($_POST['Submit']) OR isset($_POST['EnterMoreItems'])){
/*Trap any errors in input */

	$InputError = False; /*Start off hoping for the best */
	$TotalItems = 0;
	//Make sure this Transfer has not already been entered... aka one way around the refresh & insert new records problem
	$Result = DB_query("SELECT * FROM loctransfers WHERE reference='" . $_POST['Trf_ID'] . "'");
	if (DB_num_rows($Result)!=0){
		$InputError = true;
		$ErrorMessage = _('This transaction has already been entered') . '. ' . _('Please start over now') . '<br />';
		unset($_POST['submit']);
		unset($_POST['EnterMoreItems']);
		for ($i=$_POST['LinesCounter']-10;$i<$_POST['LinesCounter'];$i++){
			unset($_POST['StockID' . $i]);
			unset($_POST['StockQTY' . $i]);
		}
	}  else {
		$ErrorMessage='';

		if (isset($_POST['ClearAll'])){
			unset($_POST['EnterMoreItems']);
			for ($i=$_POST['LinesCounter']-10;$i<$_POST['LinesCounter'];$i++){
				unset($_POST['StockID' . $i]);
				unset($_POST['StockQTY' . $i]);
			}
		}
		$StockIDAccQty = array(); //set an array to hold all items' quantity
		for ($i=$_POST['LinesCounter']-10;$i<$_POST['LinesCounter'];$i++){
			if (isset($_POST['Delete' . $i])){ //check box to delete the item is set
				unset($_POST['StockID' . $i]);
				unset($_POST['StockQTY' . $i]);
			}
			if (isset($_POST['StockID' . $i]) AND $_POST['StockID' . $i]!=''){
				$_POST['StockID' . $i]=trim(mb_strtoupper($_POST['StockID' . $i]));
				$Result = DB_query("SELECT COUNT(stockid) FROM stockmaster WHERE stockid='" . $_POST['StockID' . $i] . "'");
				$MyRow = DB_fetch_row($Result);
				if ($MyRow[0]==0){
					$InputError = True;
					$ErrorMessage .= _('The part code entered of'). ' ' . $_POST['StockID' . $i] . ' '. _('is not set up in the database') . '. ' . _('Only valid parts can be entered for transfers'). '<br />';
					$_POST['LinesCounter'] -= 10;
				}
				DB_free_result( $Result );
				if (!is_numeric(filter_number_format($_POST['StockQTY' . $i]))){
					$InputError = True;
					$ErrorMessage .= _('The quantity entered of'). ' ' . $_POST['StockQTY' . $i] . ' '. _('for part code'). ' ' . $_POST['StockID' . $i] . ' '. _('is not numeric') . '. ' . _('The quantity entered for transfers is expected to be numeric') . '<br />';
					$_POST['LinesCounter'] -= 10;
				}
				if (filter_number_format($_POST['StockQTY' . $i]) <= 0){
					$InputError = True;
					$ErrorMessage .= _('The quantity entered for').' '. $_POST['StockID' . $i] . ' ' . _('is less than or equal to 0') . '. ' . _('Please correct this or remove the item') . '<br />';
					$_POST['LinesCounter'] -= 10;
				}
				if ($_SESSION['ProhibitNegativeStock']==1){
					$InTransitSQL="SELECT SUM(pendingqty) as intransit
									FROM loctransfers
									WHERE stockid='" . $_POST['StockID' . $i] . "'
										AND shiploc='".$_POST['FromStockLocation']."'
										AND pendingqty > 0";
					$InTransitResult=DB_query($InTransitSQL);
					$InTransitRow=DB_fetch_array($InTransitResult);
					$InTransitQuantity=$InTransitRow['intransit'];
					// Only if stock exists at this location
					$Result = DB_query("SELECT quantity
										FROM locstock
										WHERE stockid='" . $_POST['StockID' . $i] . "'
										AND loccode='".$_POST['FromStockLocation']."'");

					$MyRow = DB_fetch_array($Result);
					if (($MyRow['quantity']-$InTransitQuantity) < filter_number_format($_POST['StockQTY' . $i])){
						$InputError = True;
						$ErrorMessage .= _('The part code entered of'). ' ' . $_POST['StockID' . $i] . ' '. _('does not have enough stock available for transfer.') . '.<br />';
						$_POST['LinesCounter'] -= 10;
					}
				}
				// Check the accumulated quantity for each item
				if(isset($StockIDAccQty[$_POST['StockID'.$i]])){
					$StockIDAccQty[$_POST['StockID'.$i]] += filter_number_format($_POST['StockQTY' . $i]);
					if($MyRow[0] < $StockIDAccQty[$_POST['StockID'.$i]]){
						$InputError = True;
						$ErrorMessage .=_('The part code entered of'). ' ' . $_POST['StockID'.$i] . ' '._('does not have enough stock available for transter due to accumulated quantity is over quantity on hand.') . '<br />';
						$_POST['LinesCounter'] -= 10;
					}
				} else {
					$StockIDAccQty[$_POST['StockID'.$i]] = filter_number_format($_POST['StockQTY' . $i]);
				} //end of accumulated check

				DB_free_result( $Result );
				$TotalItems++;
			}
		}//for all LinesCounter

		if ($TotalItems == 0){
			$InputError = True;
			$ErrorMessage .= _('You must enter at least 1 Stock Item to transfer') . '<br />';
		}

		/*Ship location and Receive location are different */
		if ($_POST['FromStockLocation']==$_POST['ToStockLocation']){
			$InputError=True;
			$ErrorMessage .= _('The transfer must have a different location to receive into and location sent from');
		}
	} //end if the transfer is not a duplicated
}

if(isset($_POST['Submit']) AND $InputError==False){

	$ErrMsg = _('CRITICAL ERROR') . '! ' . _('Unable to BEGIN Location Transfer transaction');

	DB_Txn_Begin();

	for ($i=0;$i < $_POST['LinesCounter'];$i++){

		if($_POST['StockID' . $i] != ''){
			$DecimalsSql = "SELECT decimalplaces
							FROM stockmaster
							WHERE stockid='" . $_POST['StockID' . $i] . "'";
			$DecimalResult = DB_query($DecimalsSql);
			$DecimalRow = DB_fetch_array($DecimalResult);
			$SQL = "INSERT INTO loctransfers (reference,
								stockid,
								shipqty,
								shipdate,
								shiploc,
								recloc)
						VALUES ('" . $_POST['Trf_ID'] . "',
							'" . $_POST['StockID' . $i] . "',
							'" . round(filter_number_format($_POST['StockQTY' . $i]), $DecimalRow['decimalplaces']) . "',
							'" . Date('Y-m-d H-i-s') . "',
							'" . $_POST['FromStockLocation']  ."',
							'" . $_POST['ToStockLocation'] . "')";
			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('Unable to enter Location Transfer record for'). ' '.$_POST['StockID' . $i];
			$ResultLocShip = DB_query($SQL, $ErrMsg);
			/* KL RICARD Send emails to team if transfer from / to special location */
			if ($_POST['ToStockLocation'] == 'SERDE'){
				KLSendEmail("ItemTransferredToSpecialLocation", "Silent", $_POST['StockID' . $i], round(filter_number_format($_POST['StockQTY' . $i]), $DecimalRow['decimalplaces']),$_POST['FromStockLocation'], $_POST['ToStockLocation']);
			}
			/* KL RICARD End modification */
		}
	}
	$ErrMsg = _('CRITICAL ERROR') . '! ' . _('Unable to COMMIT Location Transfer transaction');
	DB_Txn_Commit();

	prnMsg( _('The return Transfer to Kantor has been created'),'success');
	prnMsg( _('Copy the transfer number: '. $_POST['Trf_ID'] . ' in the paper slip transfer.'),'info');
	prnMsg( _('Paper Slip Transfer MUST contain the same items than this transfer'),'info');
	include('includes/footer.php');

} else {
	//Get next Inventory Transfer Shipment Reference Number
	if (isset($_GET['Trf_ID'])){
		$Trf_ID = $_GET['Trf_ID'];
	} elseif (isset($_POST['Trf_ID'])){
		$Trf_ID = $_POST['Trf_ID'];
	}

	if(!isset($Trf_ID)){
		$Trf_ID = GetNextTransNo(16);
	}

	if (isset($InputError) and $InputError==true){
		echo '<br />';

		prnMsg($ErrorMessage, 'error');
		echo '<br />';

	}

	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/supplier.png" title="' . _('Return Transfer from Shop to Kantor') . '" alt="" />' . ' ' . $Title . '</p>';

	echo '<form enctype="multipart/form-data" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<input type="hidden" name="FromStockLocation" value="' . $_SESSION['UserStockLocation'] . '" />';
	echo '<input type="hidden" name="ToStockLocation" value="'."KANTO".'" />';

	echo '<table class="selection">';
	echo '<tr>
			<th colspan="4"><input type="hidden" name="Trf_ID" value="' . $Trf_ID . '" /><h3>' .  _('Return Transfer from Shop to Kantor').' # '. $Trf_ID. '</h3></th>
		</tr>';
	$j=0; /* row counter for reindexing */
	if(isset($_POST['LinesCounter'])){

		for ($i=0;$i < $_POST['LinesCounter'];$i++){
			if (!isset($_POST['StockID'. $i])){
				continue;
			}
			if ($_POST['StockID' . $i] ==''){
				break;
			}

			echo '<tr>
					<td><input type="text" name="StockID' . $j .'" size="21"  maxlength="20" value="' . $_POST['StockID' . $i] . '" /></td>
					<td><input type="text" name="StockQTY' . $j .'" size="10" maxlength="10" class="number" value="' . locale_number_format($_POST['StockQTY' . $i],'Variable') . '" /></td>
					<td>' . _('Delete') . '<input type="checkbox" name="Delete' . $j .'" /></td>
				</tr>';
			$j++;
		}
	} else {
		$j = 0;
	}
	// $i is incremented an extra time, so 9 to get 10...
	$z=($j + 9);

	while($j < $z) {
		if (!isset($_POST['StockID' . $j])) {
			$_POST['StockID' . $j]='';
		}
		if (!isset($_POST['StockQTY' . $j])) {
			$_POST['StockQTY' . $j]=0;
		}
		echo '<tr>
				<td><input type="text" name="StockID' . $j .'" ' . ($j==0 OR $j==$z-9 ? 'autofocus="autofocus"' : '') . ' size="21"  maxlength="20" value="' . $_POST['StockID' . $j] . '" /></td>
				<td><input type="text" name="StockQTY' . $j .'" size="10" maxlength="10" class="number" value="' . locale_number_format($_POST['StockQTY' . $j]) . '" /></td>
			</tr>';
		$j++;
	}

	echo '</table>
		<br />
		<div class="centre">
		<input type="hidden" name="LinesCounter" value="'. $j .'" />
		<input type="submit" name="EnterMoreItems" value="'. _('Add More Items'). '" />
		<input type="submit" name="Submit" value="'. _('Create Transfer From Shop To Kantor'). '" />
		<br />
		</div>
		</div>
		</form>';
	include('includes/footer.php');
}
?>

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
include('includes/KLDefines.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLEmails.php');

include('includes/KLPOSGeneral.php');

include ('includes/WebClientPrint/WebClientPrint.php');
use Neodynamic\SDK\Web\WebClientPrint;
include('includes/wcpESCPOSCommands.php');

if (isset($_POST['Submit']) OR isset($_POST['EnterMoreItems'])){
/*Trap any errors in input */

	$InputError = False; /*Start off hoping for the best */
	$TotalItems = 0;
	//Make sure this Transfer has not already been entered... aka one way around the refresh & insert new records problem
	$result = DB_query("SELECT * FROM loctransfers WHERE reference='" . $_POST['Trf_ID'] . "'");
	if (DB_num_rows($result)!=0){
		$InputError = true;
		$ErrorMessage = _('This transaction has already been entered') . '. ' . _('Please start over now') . '<br />';
		unset($_POST['submit']);
		unset($_POST['EnterMoreItems']);
		for ($i=$_POST['LinesCounter']-2;$i<$_POST['LinesCounter'];$i++){
			unset($_POST['StockID' . $i]);
			unset($_POST['StockQTY' . $i]);
		}
	}  else {
		$ErrorMessage='';

		if (isset($_POST['ClearAll'])){
			unset($_POST['EnterMoreItems']);
			for ($i=$_POST['LinesCounter']-2;$i<$_POST['LinesCounter'];$i++){
				unset($_POST['StockID' . $i]);
				unset($_POST['StockQTY' . $i]);
			}
		}
		for ($i=0;$i<$_POST['LinesCounter'];$i++){
			if (isset($_POST['Delete' . $i])){ //check box to delete the item is set
				unset($_POST['StockID' . $i]);
				unset($_POST['StockQTY' . $i]);
			}
			if (isset($_POST['StockID' . $i]) AND $_POST['StockID' . $i]!=''){
				$_POST['StockID' . $i]=trim(mb_strtoupper($_POST['StockID' . $i]));
				$result = DB_query("SELECT COUNT(*) FROM stockmaster WHERE stockid='" . $_POST['StockID' . $i] . "'");
				$myrow = DB_fetch_row($result);
				if ($myrow[0]==0){
					$InputError = True;
					$ErrorMessage .= _('The part code entered of'). ' ' . $_POST['StockID' . $i] . ' '. _('is not set up in the database') . '. ' . _('Only valid parts can be entered for transfers'). '<br />';
					$_POST['LinesCounter'] -= 1;
				}
				DB_free_result( $result );
				if (!is_numeric(filter_number_format($_POST['StockQTY' . $i]))){
					$InputError = True;
					$ErrorMessage .= _('The quantity entered of'). ' ' . $_POST['StockQTY' . $i] . ' '. _('for part code'). ' ' . $_POST['StockID' . $i] . ' '. _('is not numeric') . '. ' . _('The quantity entered for transfers is expected to be numeric') . '<br />';
					$_POST['LinesCounter'] -= 1;
				}
				if (filter_number_format($_POST['StockQTY' . $i]) <= 0){
					$InputError = True;
					$ErrorMessage .= _('The quantity entered for').' '. $_POST['StockID' . $i] . ' ' . _('is less than or equal to 0') . '. ' . _('Please correct this or remove the item') . '<br />';
					$_POST['LinesCounter'] -= 1;
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
					$result = DB_query("SELECT quantity
										FROM locstock
										WHERE stockid='" . $_POST['StockID' . $i] . "'
										AND loccode='".$_POST['FromStockLocation']."'");

					$myrow = DB_fetch_array($result);
				}
				// Check if the last one entered already exists on the transfer
				$LastItem = $_POST['LinesCounter']-1;
				if ((!$InputError) AND ($i < $LastItem) AND ($_POST['StockID' . $i] == trim(mb_strtoupper($_POST['StockID' . $LastItem])))){
					$_POST['StockQTY' . $i] += $_POST['StockQTY' . $LastItem];
					$_POST['StockID' . $LastItem] ='';
					$_POST['StockQTY' . $LastItem] =1;
					$_POST['LinesCounter'] -= 1;
					prnMsg("Item ". $_POST['StockID' . $i] . " was already in the transfer. Just updating quantity to " . $_POST['StockQTY' . $i],"warn");
				}

				DB_free_result( $result );
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

	$TextToPrint = $InitPrinter . $CenteredJustified;
	// name of shop
	$TextToPrint .= KLPrintNameOfShop();
	$TextToPrint .= $EmphasizedDoubleHeightDoubleWidth . 'TRANSFER TO KANTOR' . $NewLine;
	// warning if it is a TEST
	$TextToPrint .= KLPrintReceiptTestWarning("RETURN TRANSFER"). $NewLine . $CenteredJustified;
	$TextToPrint .= DisplayDateTime() . $NewLine;
	$TextToPrint .= 'SPG Code: ' . $_SESSION['SalesmanLogin'] . $NewLine;
	$TextToPrint .= 'Shop Code: ' . substr($_SESSION['UserStockLocation'],3,2) . $NewLine;
	$TextToPrint .= 'Transfer Number: ' . $_POST['Trf_ID'] . $NewLine;
	$TextToPrint .=  $NewLine . $NewLine;
	$TextToPrint .=  $LeftJustified;

	$NumberOfItems = 0;

	DB_Txn_Begin();

	for ($i=0;$i < $_POST['LinesCounter'];$i++){

		if($_POST['StockID' . $i] != ''){
			$DecimalsSql = "SELECT decimalplaces
							FROM stockmaster
							WHERE stockid='" . $_POST['StockID' . $i] . "'";
			$DecimalResult = DB_query($DecimalsSql);
			$DecimalRow = DB_fetch_array($DecimalResult);
			$sql = "INSERT INTO loctransfers (reference,
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
			$resultLocShip = DB_query($sql, $ErrMsg);

			if ($_POST['ToStockLocation'] == 'SERDE'){
				KLSendEmail("ItemTransferredToSpecialLocation", "Silent", $_POST['StockID' . $i], round(filter_number_format($_POST['StockQTY' . $i]), $DecimalRow['decimalplaces']),$_POST['FromStockLocation'], $_POST['ToStockLocation']);
			}
			
			$NumberOfItems += $_POST['StockQTY' . $i];
			$TextToPrint .= round(filter_number_format($_POST['StockQTY' . $i]), $DecimalRow['decimalplaces']) . ' x ' . $_POST['StockID' . $i] . $NewLine;

		}
	}
	$ErrMsg = _('CRITICAL ERROR') . '! ' . _('Unable to COMMIT Location Transfer transaction');
	DB_Txn_Commit();

	$TextToPrint .= $NewLine. $Emphasized . '# Pieces in this transfer: ' . filter_number_format($NumberOfItems) . $NewLine;

	$TextToPrint .= $NewLine. $Emphasized . 'Prepared by: ' . $_SESSION['SalesmanLogin'] . $NewLine;
	$TextToPrint .= $CharacterFontA . 'Date: ' . DisplayDateTime() . $NewLine;
	$TextToPrint .= 'Signature: ' . $NewLine . $NewLine . $NewLine . $NewLine . $NewLine;
	
	$TextToPrint .= $Emphasized . 'Shipped by: ' . $NewLine;
	$TextToPrint .= $CharacterFontA . 'Date: ' . $NewLine;
	$TextToPrint .= 'Signature: ' . $NewLine . $NewLine . $NewLine . $NewLine . $NewLine;

	$TextToPrint .= $Emphasized . 'Received by: ' . $NewLine;
	$TextToPrint .= $CharacterFontA . 'Date: ' . $NewLine;
	$TextToPrint .= 'Signature: ' . $NewLine . $NewLine . $NewLine . $NewLine . $NewLine;
	
	// warning if it is a TEST
	$TextToPrint .= KLPrintReceiptTestWarning("RETURN TRANSFER"). $NewLine . $LeftJustified;
	$TextToPrint .= $CutPaper;

	//################## PRINTING STUFF ##################### 
	$identifier=GetPOSIdentifier();
	$filename = GetFilenameFromPOSIdentifier($identifier);   
	file_put_contents($filename, $TextToPrint);
	$textActionToPrint = 'Print Return Transfer number: '. $_POST['Trf_ID'];
	include ('includes/SilentPrinting.php');
	//################## PRINTING STUFF ##################### 

	include('includes/footer.php');

} else {
	//Get next Inventory Transfer Shipment Reference Number
	if (isset($_GET['Trf_ID'])){
		$Trf_ID = $_GET['Trf_ID'];
	} elseif (isset($_POST['Trf_ID'])){
		$Trf_ID = $_POST['Trf_ID'];
	}

	if(!isset($Trf_ID)){
		$Trf_ID = GetNextTransNo(16,$db);
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
	echo '<tr>
			<th class="ascending">' . _('Code') . '</th>
			<th class="ascending">' . _('Quantity') . '</th>
			<th colspan="2"></th>
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
	// $i is incremented an extra time, so 1 to get 2...
	if (!isset($_POST['StockID' . $j])) {
		$_POST['StockID' . $j]='';
	}
	if (!isset($_POST['StockQTY' . $j])) {
		$_POST['StockQTY' . $j]=1;
	}
	echo '<tr>
			<td><input type="text" name="StockID' . $j .'" autofocus="autofocus" size="21"  maxlength="20" value="' . $_POST['StockID' . $j] . '" /></td>
			<td><input type="text" name="StockQTY' . $j .'" size="10" maxlength="10" class="number" value="' . locale_number_format($_POST['StockQTY' . $j]) . '" /></td>
		</tr>';
	$j++;

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

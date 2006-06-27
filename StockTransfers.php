<?php

/* $Revision: 1.18 $ */

include('includes/DefineSerialItems.php');
include('includes/DefineStockTransfers.php');

$PageSecurity = 11;
include('includes/session.inc');
$title = _('Stock Transfers');

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

$NewTransfer = false; /*initialise this first then determine from form inputs */

if (isset($_GET['NewTransfer'])){
     unset($_SESSION['Transfer']);
     unset($_SESSION['TransferItem']); /*this is defined in bulk transfers but needs to be unset for individual trsnsfers */
}


if (isset($_GET['StockID'])){	/*carry the stockid through to the form for additional inputs */

	$_POST['StockID'] = trim(strtoupper($_GET['StockID']));

} elseif (isset($_POST['StockID'])){	/* initiate a new transfer only if the StockID is different to the previous entry */

	if ($_POST['StockID'] != $_SESSION['Transfer']->TransferItem[0]->StockID){
		unset($_SESSION['Transfer']);
		$NewTransfer = true;
	}
}

if ($NewTransfer){

	$_SESSION['Transfer']= new StockTransfer(0,
						$_POST['StockLocationFrom'],
						'',
						$_POST['StockLocationTo'],
						'',
						Date($_SESSION['DefaultDateFormat'])
						);
	$result = DB_query("SELECT description,
				units,
				mbflag,
				materialcost+labourcost+overheadcost as standardcost,
				controlled,
				serialised,
				decimalplaces
			FROM stockmaster
			WHERE stockid='" . trim(strtoupper($_POST['StockID'])) . "'",
			$db);
	$myrow = DB_fetch_row($result);
	if (DB_num_rows($result) == 0){
		prnMsg( _('Unable to locate Stock Code').' '.strtoupper($_POST['StockID']), 'error' );
	} elseif (DB_num_rows($result)>0){

		$_SESSION['Transfer']->TransferItem[0] = new LineItem ( trim(strtoupper($_POST['StockID'])),
									$myrow[0],
									$_POST['Quantity'],
									$myrow[1],
									$myrow[4],
									$myrow[5],
									$myrow[6]);


		$_SESSION['Transfer']->TransferItem[0]->StandardCost = $myrow[3];

		if ($myrow[2]=='D' OR $myrow[2]=='A' OR $myrow[2]=='K'){
			prnMsg(_('The part entered is either or a dummy part or an assembly or a kit-set part') . '. ' . _('These parts are not physical parts and no stock holding is maintained for them') . '. ' . _('Stock Transfers are therefore not possible'),'warn');
			echo '.<HR>';
			echo "<A HREF='" . $rootpath . '/StockTransfers.php?' . SID ."&NewTransfer=Yes'>" . _('Enter another Transfer') . '</A>';
			unset ($_SESSION['Transfer']);
			include ('includes/footer.inc');
			exit;
		}
	}

}

if ($_SESSION['Transfer']->TransferItem[0]->Controlled==0){
	$_SESSION['Transfer']->TransferItem[0]->Quantity = $_POST['Quantity'];
}
if ( isset($_POST['StockLocationFrom']) && $_POST['StockLocationFrom']!= $_SESSION['Transfer']->StockLocationFrom ){
	$_SESSION['Transfer']->StockLocationFrom = $_POST['StockLocationFrom'];
	$_SESSION['Transfer']->TransferItem[0]->Quantity=0;
	$_SESSION['Transfer']->TransferItem[0]->SerialItems=array();
	prnMsg( _('You have set or changed the From location') . '. ' . _('You must re-enter the quantity and any Controlled Items now') );
}
if ( isset($_POST['StockLocationTo']) ){
	$_SESSION['Transfer']->StockLocationTo = $_POST['StockLocationTo'];
}

if ( isset($_POST['EnterTransfer']) ){



	$result = DB_query("SELECT * FROM stockmaster WHERE stockid='" . $_SESSION['Transfer']->TransferItem[0]->StockID ."'",$db);
	$myrow = DB_fetch_row($result);
	$InputError = false;
	if (DB_num_rows($result)==0) {
		echo '<P>';
		prnMsg(_('The entered item code does not exist'), 'error');
		$InputError = true;
	} elseif (!is_numeric($_SESSION['Transfer']->TransferItem[0]->Quantity)){
		echo '<P>';
		prnMsg( _('The quantity entered must be numeric'), 'error' );
		$InputError = true;
	} elseif ($_SESSION['Transfer']->TransferItem[0]->Quantity<=0){
		echo '<P>';
		prnMsg( _('The quantity entered must be a positive number greater than zero'), 'error');
		$InputError = true;
	}
	if ($_SESSION['Transfer']->StockLocationFrom==$_SESSION['Transfer']->StockLocationTo){
		echo '<P>';
		prnMsg( _('The locations to transfer from and to must be different'), 'error');
		$InputError = true;
	}

	if ($InputError==False) {
/*All inputs must be sensible so make the stock movement records and update the locations stocks */

		$TransferNumber = GetNextTransNo(16,$db);
		$PeriodNo = GetPeriod (Date($_SESSION['DefaultDateFormat']), $db);
		$SQLTransferDate = FormatDateForSQL(Date($_SESSION['DefaultDateFormat']));

		$Result = DB_query('BEGIN',$db);

		// Need to get the current location quantity will need it later for the stock movement
		$SQL="SELECT locstock.quantity 
			FROM locstock 
			WHERE locstock.stockid='" . $_SESSION['Transfer']->TransferItem[0]->StockID . "' 
			AND loccode= '" . $_SESSION['Transfer']->StockLocationFrom . "'";

		$ErrMsg =  _('Could not retrieve the QOH at the sending location because');
		$DbgMsg =  _('The SQL that failed was');
		$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

		if (DB_num_rows($Result)==1){
			$LocQtyRow = DB_fetch_row($Result);
			$QtyOnHandPrior = $LocQtyRow[0];
		} else {
			// There must actually be some error this should never happen
			$QtyOnHandPrior = 0;
		}

		// Insert the stock movement for the stock going out of the from location
		$SQL = "INSERT INTO stockmoves (stockid,
					type,
					transno,
					loccode,
					trandate,
					prd,
					reference,
					qty,
					newqoh)
			VALUES ('" .
					$_SESSION['Transfer']->TransferItem[0]->StockID . "',
					16,
					" . $TransferNumber . ",
					'" . $_SESSION['Transfer']->StockLocationFrom . "',
					'" . $SQLTransferDate . "'," . $PeriodNo . ",
					'To " . $_SESSION['Transfer']->StockLocationTo ."',
					" . -$_SESSION['Transfer']->TransferItem[0]->Quantity . ",
					" . ($QtyOnHandPrior - $_SESSION['Transfer']->TransferItem[0]->Quantity) .
				")";

		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stock movement record cannot be inserted because');
		$DbgMsg =  _('The following SQL to insert the stock movement record was used');
		$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

		/*Get the ID of the StockMove... */
		$StkMoveNo = DB_Last_Insert_ID($db,'stockmoves','stkmoveno');

/*Insert the StockSerialMovements and update the StockSerialItems  for controlled items*/

		if ($_SESSION['Transfer']->TransferItem[0]->Controlled ==1){
			foreach($_SESSION['Transfer']->TransferItem[0]->SerialItems as $Item){
			/*We need to add or update the StockSerialItem record and
			The StockSerialMoves as well */

				/*First need to check if the serial items already exists or not in the location from */
				$SQL = "SELECT COUNT(*)
					FROM stockserialitems
					WHERE
					stockid='" . $_SESSION['Transfer']->TransferItem[0]->StockID . "'
					AND loccode='" . $_SESSION['Transfer']->StockLocationFrom . "'
					AND serialno='" . $Item->BundleRef . "'";

				$ErrMsg =  _('The entered item code does not exist');
				$Result = DB_query($SQL,$db,$ErrMsg);
				$SerialItemExistsRow = DB_fetch_row($Result);

				if ($SerialItemExistsRow[0]==1){

					$SQL = "UPDATE stockserialitems SET
						quantity= quantity - " . $Item->BundleQty . "
						WHERE
						stockid='" . $_SESSION['Transfer']->TransferItem[0]->StockID . "'
						AND loccode='" . $_SESSION['Transfer']->StockLocationFrom . "'
						AND serialno='" . $Item->BundleRef . "'";

					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock item record could not be updated because');
					$DbgMsg = _('The following SQL to update the serial stock item record was used');
					$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
				} else {
					/*Need to insert a new serial item record */
					$SQL = "INSERT INTO stockserialitems (stockid,
										loccode,
										serialno,
										quantity)
						VALUES ('" . $_SESSION['Transfer']->TransferItem[0]->StockID . "',
						'" . $_SESSION['Transfer']->StockLocationFrom . "',
						'" . $Item->BundleRef . "',
						" . -$Item->BundleQty . ")";

					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock item record could not be added because');
					$DbgMsg = _('The following SQL to insert the serial stock item record was used');
					$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
				}


				/* now insert the serial stock movement */

				$SQL = "INSERT INTO stockserialmoves (
								stockmoveno,
								stockid,
								serialno,
								moveqty)
						VALUES (
							" . $StkMoveNo . ",
							'" . $_SESSION['Transfer']->TransferItem[0]->StockID . "',
							'" . $Item->BundleRef . "',
							-" . $Item->BundleQty . "
							)";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock movement record could not be inserted because');
				$DbgMsg = _('The following SQL to insert the serial stock movement records was used');
				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

			}/* foreach controlled item in the serialitems array */
		} /*end if the transferred item is a controlled item */


		// Need to get the current location quantity will need it later for the stock movement
		$SQL="SELECT locstock.quantity
			FROM locstock
			WHERE locstock.stockid='" . $_SESSION['Transfer']->TransferItem[0]->StockID . "'
				AND loccode= '" . $_SESSION['Transfer']->StockLocationTo . "'";
		$ErrMsg = _('Could not retrieve QOH at the destination because');
		$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg,true);
		if (DB_num_rows($Result)==1){
			$LocQtyRow = DB_fetch_row($Result);
			$QtyOnHandPrior = $LocQtyRow[0];
		} else {
			// There must actually be some error this should never happen
			$QtyOnHandPrior = 0;
		}

		// Insert the stock movement for the stock coming into the to location
		$SQL = "INSERT INTO stockmoves (stockid,
						type,
						transno,
						loccode,
						trandate,
						prd,
						reference,
						qty,
						newqoh)
			VALUES ('" . $_SESSION['Transfer']->TransferItem[0]->StockID . "',
					16,
					" . $TransferNumber . ",
					'" . $_SESSION['Transfer']->StockLocationTo . "',
					'" . $SQLTransferDate . "',
					" . $PeriodNo . ",
					'" . _('From') . " " . $_SESSION['Transfer']->StockLocationFrom . "',
					" . $_SESSION['Transfer']->TransferItem[0]->Quantity . ",
					" . ($QtyOnHandPrior + $_SESSION['Transfer']->TransferItem[0]->Quantity) .
				")";

		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stock movement record cannot be inserted because');
		$DbgMsg = _('The following SQL to insert the stock movement record was used');
		$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

		/*Get the ID of the StockMove... */
		$StkMoveNo = DB_Last_Insert_ID($db,'stockmoves','stkmoveno');

/*Insert the StockSerialMovements and update the StockSerialItems  for controlled items*/

		if ($_SESSION['Transfer']->TransferItem[0]->Controlled ==1){
			foreach($_SESSION['Transfer']->TransferItem[0]->SerialItems as $Item){
			/*We need to add or update the StockSerialItem record and
			The StockSerialMoves as well */

				/*First need to check if the serial items already exists or not in the location from */
				$SQL = "SELECT COUNT(*)
					FROM stockserialitems
					WHERE
					stockid='" . $_SESSION['Transfer']->TransferItem[0]->StockID . "'
					AND loccode='" . $_SESSION['Transfer']->StockLocationTo . "'
					AND serialno='" . $Item->BundleRef . "'";

				$ErrMsg = _('Could not determine if the serial item exists in the transfer to location');
				$Result = DB_query($SQL,$db,$ErrMsg);
				$SerialItemExistsRow = DB_fetch_row($Result);

				if ($SerialItemExistsRow[0]==1){

					$SQL = "UPDATE stockserialitems SET
						quantity= quantity + " . $Item->BundleQty . "
						WHERE
						stockid='" . $_SESSION['Transfer']->TransferItem[0]->StockID . "'
						AND loccode='" . $_SESSION['Transfer']->StockLocationTo . "'
						AND serialno='" . $Item->BundleRef . "'";

					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock item record could not be updated because');
					$DbgMsg = _('The following SQL to update the serial stock item record was used');
					$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
				} else {
					/*Need to insert a new serial item record */
					$SQL = "INSERT INTO stockserialitems (stockid,
										loccode,
										serialno,
										quantity)
						VALUES ('" . $_SESSION['Transfer']->TransferItem[0]->StockID . "',
						'" . $_SESSION['Transfer']->StockLocationTo . "',
						'" . $Item->BundleRef . "',
						" . $Item->BundleQty . ")";

					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock item record could not be added because');
					$DbgMsg = _('The following SQL to insert the serial stock item record was used:');
					$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
				}


				/* now insert the serial stock movement */

				$SQL = "INSERT INTO stockserialmoves (stockmoveno, 
									stockid, 
									serialno, 
									moveqty) 
							VALUES (" . $StkMoveNo . ", 
								'" . $_SESSION['Transfer']->TransferItem[0]->StockID . "', 
								'" . $Item->BundleRef . "', 
								" . $Item->BundleQty . ")";
				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock movement record could not be inserted because');
				$DbgMsg = _('The following SQL to insert the serial stock movement records was used');
				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

			}/* foreach controlled item in the serialitems array */
		} /*end if the transfer item is a controlled item */


		$SQL = "UPDATE locstock
			SET quantity = quantity - " . $_SESSION['Transfer']->TransferItem[0]->Quantity . "
			WHERE stockid='" . $_SESSION['Transfer']->TransferItem[0]->StockID . "'
			AND loccode='" . $_SESSION['Transfer']->StockLocationFrom . "'";

		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The location stock record could not be updated because');
		$DbgMsg = _('The following SQL to update the location stock record was used');
		$Result = DB_query($SQL,$db,$Errmsg,$DbgMsg,true);

		$SQL = "UPDATE locstock
			SET quantity = quantity + " . $_SESSION['Transfer']->TransferItem[0]->Quantity . "
			WHERE stockid='" . $_SESSION['Transfer']->TransferItem[0]->StockID . "'
			AND loccode='" . $_SESSION['Transfer']->StockLocationTo . "'";


		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The location stock record could not be updated because');
		$DbgMsg = _('The following SQL to update the location stock record was used');
		$Result = DB_query($SQL,$db,$ErrMsg, $DbgMsg, true);

		$Result = DB_query('COMMIT',$db);

		prnMsg(_('An inventory transfer of').' ' . $_SESSION['Transfer']->TransferItem[0]->StockID . ' - ' . $_SESSION['Transfer']->TransferItem[0]->ItemDescription . ' '. _('has been created from').' ' . $_SESSION['Transfer']->StockLocationFrom . ' '. _('to') . ' ' . $_SESSION['Transfer']->StockLocationTo . ' '._('for a quantity of').' ' . $_SESSION['Transfer']->TransferItem[0]->Quantity,'success');
		unset ($_SESSION['Transfer']);
		include ('includes/footer.inc');
		exit;
	}

}


echo '<FORM ACTION="'. $_SERVER['PHP_SELF'] . '?' . SID . '" METHOD=POST>';

echo '<CENTER>
	<TABLE>
	<TR>
	<TD>'. _('Stock Code').':</TD>
	<TD><input type=text name="StockID" size=21 value="' . $_SESSION['Transfer']->TransferItem[0]->StockID . '" maxlength=20></TD>
	<TD><INPUT TYPE=SUBMIT NAME="CheckCode" VALUE="'._('Check Part').'"></TD>
	</TR>';

if (strlen($_SESSION['Transfer']->TransferItem[0]->ItemDescription)>1){
	echo '<TR><TD COLSPAN=3><FONT COLOR=BLUE SIZE=3>' . $_SESSION['Transfer']->TransferItem[0]->ItemDescription . ' ('._('In Units of').' ' . $_SESSION['Transfer']->TransferItem[0]->PartUnit . ' )</FONT></TD></TR>';
}

echo '<TR><TD>' . _('From Stock Location').':</TD><TD><SELECT name="StockLocationFrom">';

$sql = 'SELECT loccode, locationname FROM locations';
$resultStkLocs = DB_query($sql,$db);
while ($myrow=DB_fetch_array($resultStkLocs)){
	if (isset($_SESSION['Transfer']->StockLocationFrom)){
		if ($myrow['loccode'] == $_SESSION['Transfer']->StockLocationFrom){
		     echo '<OPTION SELECTED Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
		} else {
		     echo '<OPTION Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
		}
	} elseif ($myrow['loccode']==$_SESSION['UserStockLocation']){
		 echo '<OPTION SELECTED Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
		 $_SESSION['Transfer']->StockLocationFrom=$myrow['loccode'];
	} else {
		 echo '<OPTION Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
	}
}

echo '</SELECT></TD></TR>';

echo '<TR><TD>'. _('To Stock Location').': </TD><TD><SELECT name="StockLocationTo"> ';

DB_data_seek($resultStkLocs,0);

while ($myrow=DB_fetch_array($resultStkLocs)){
	if (isset($_SESSION['Transfer']->StockLocationTo)){
		if ($myrow['loccode'] == $_SESSION['Transfer']->StockLocationTo){
		     echo '<OPTION SELECTED Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
		} else {
		     echo '<OPTION Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
		}
	} elseif ($myrow['loccode']==$_SESSION['UserStockLocation']){
		 echo '<OPTION SELECTED Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
		 $_SESSION['Transfer']->StockLocationTo=$myrow['loccode'];
	} else {
		 echo '<OPTION Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
	}
}

echo '</SELECT></TD></TR>';


echo '<TR><TD>'._('Transfer Quantity').':</TD>';

if ($_SESSION['Transfer']->TransferItem[0]->Controlled==1){

	echo '<TD ALIGN=RIGHT><INPUT TYPE=HIDDEN NAME="Quantity" VALUE=' . $_SESSION['Transfer']->TransferItem[0]->Quantity . '><A HREF="' . $rootpath .'/StockTransferControlled.php?' . SID . '">' . $_SESSION['Transfer']->TransferItem[0]->Quantity . '</A></TD></TR>';
} else {
	echo '<TD><INPUT TYPE=TEXT NAME="Quantity" SIZE=12 MAXLENGTH=12 Value=' . $_SESSION['Transfer']->TransferItem[0]->Quantity . '></TD></TR>';
}



echo "</TABLE><BR><INPUT TYPE=SUBMIT NAME='EnterTransfer' VALUE='" . _('Enter Stock Transfer') . "'>";
echo '<HR>';


echo '<A HREF="'.$rootpath.'/StockStatus.php?' . SID . '&StockID=' . $_SESSION['Transfer']->TransferItem[0]->StockID . '">'._('Show Stock Status').'</A>';
echo '<BR><A HREF="'.$rootpath.'/StockMovements.php?' . SID . '&StockID=' . $_SESSION['Transfer']->TransferItem[0]->StockID . '">'._('Show Movements').'</A>';
echo '<BR><A HREF="'.$rootpath.'/StockUsage.php?' . SID . '&StockID=' . $_SESSION['Transfer']->TransferItem[0]->StockID . '&StockLocation=' . $_SESSION['Transfer']->StockLocationFrom . '">' . _('Show Stock Usage') . '</A>';
echo '<BR><A HREF="'.$rootpath.'/SelectSalesOrder.php?' . SID . '&SelectedStockItem=' . $_SESSION['Transfer']->TransferItem[0]->StockID . '&StockLocation=' . $_SESSION['Transfer']->StockLocationFrom . '">' . _('Search Outstanding Sales Orders') . '</A>';
echo '<BR><A HREF="'.$rootpath.'/SelectCompletedOrder.php?' . SID . '&SelectedStockItem=' . $_SESSION['Transfer']->TransferItem[0]->StockID . '">'._('Search Completed Sales Orders').'</A>';

echo '</FORM>';
include('includes/footer.inc');
?>
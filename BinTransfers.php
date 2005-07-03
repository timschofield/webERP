<?php
/* $Revision: 1.1 $ */
//pulled from StockLocTransfers - edited by Lucas Casteel

//includes/config ----------------------------------------------
include('includes/DefineSerialItems.php');
include('includes/DefineBinTransfers.php');

$PageSecurity = 11;
include('includes/session.inc');
$title = _('Inventory Bin Transfers');

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
//--------------------------------------------------------------

//$NewTransfer = false; /*initialise this first then determine from form inputs */
$NewTransfer = true;

//---------------------------------------------------------------------------------------------------

if(!isset($_POST['StockID']) && $_POST['StockLocationFrom'] == "error"){//if StockID not posted from form and Error from listbox
	prnMsg( _('You Cannot Transfer a Part with None on Hand.').' '.$_POST['StockID'], 'error' );
} else {//if stockid posted from form or select box not error
	if (isset($_GET['NewTransfer'])){
	     unset($_SESSION['Transfer']);
	}

	if (isset($_GET['StockID'])){	/*carry the stockid through to the form for additional inputs */
		$_POST['StockID']=$_GET['StockID'];
	} elseif (isset($_POST['StockID'])){	/* initiate a new transfer only if the StockID is different to the previous entry */
		if ($_POST['StockID'] != $_SESSION['Transfer']->TransferItem[0]->StockID){
			unset($_SESSION['Transfer']);
			$NewTransfer = true;
		}
	}
	
	//New Transfer --------------------------------------------------------------
	if ($NewTransfer){
		$_SESSION['Transfer']= new StockTransfer(0,
							'BR', //$_POST['StockMainLocFrom'], 
							$_POST['StockLocationFrom'],
							'',
							'BR', //$_POST['StockMainLocTo'],
							$_POST['StockLocationTo'],
							'',
							Date($DefaultDateFormat)
							);
							
		$SQL = "SELECT stockmaster.description,
				stockmaster.units,
				stockmaster.mbflag,
				stockmaster.materialcost+labourcost+overheadcost AS standardcost,
				stockmaster.controlled,
				stockmaster.serialised,
				stockmaster.decimalplaces,
				SUM(binstock.qty) as qoh
				FROM stockmaster, binstock
				WHERE stockmaster.stockid= binstock.stockid
				AND binstock.stockid='" . $_POST['StockID'] . "'
				GROUP BY binstock.stockid, stockmaster.description, stockmaster.units, stockmaster.mbflag, stockmaster.materialcost, stockmaster.labourcost, stockmaster.overheadcost, stockmaster.controlled, stockmaster.serialised, stockmaster.decimalplaces";
		
		$result = DB_query($SQL, $db);
		$myrow = DB_fetch_row($result);
		if (DB_num_rows($result) == 0){
			prnMsg( _('Stock Code not found in any bins'), 'warn' );
		}elseif (DB_num_rows($result)>0){
			$_SESSION['Transfer']->TransferItem[0] = new LineItem ($_POST['StockID'],
										$myrow[0],
										$_POST['Quantity'],
										$myrow[1],
										$myrow[4],
										$myrow[5],
										$myrow[6],
										$myrow[7]);
			
			$_SESSION['Transfer']->TransferItem[0]->StandardCost = $myrow[3];
	
			if ($myrow[2]=='D' OR $myrow[2]=='A' OR $myrow[2]=='K'){
				echo '<P>'._('The part entered is either or a dummy part or an assembly/kit-set part. These parts are not physical parts and no stock holding is maintained for them. Stock Transfers are therefore not possible').'.<HR>';
				echo "<A HREF='" . $rootpath . '/StockTransfers.php?' . SID ."&NewTransfer=Yes'>" . _('Enter another Transfer') . '</A>';
				unset ($_SESSION['Transfer']);
				include ('includes/footer.inc');
				exit;
			}
		}
	}
	//END New Transfer --------------------------------------------------------------
	
	if ($_SESSION['Transfer']->TransferItem[0]->Controlled==0){
		$_SESSION['Transfer']->TransferItem[0]->Quantity = $_POST['Quantity'];
	}
	if ( isset($_POST['StockLocationFrom']) && $_POST['StockLocationFrom']!= $_SESSION['Transfer']->StockLocationFrom ){
		$_SESSION['Transfer']->StockLocationFrom = $_POST['StockLocationFrom'];
		$_SESSION['Transfer']->TransferItem[0]->Quantity=0;
		$_SESSION['Transfer']->TransferItem[0]->SerialItems=array();
		prnMsg( _('You have set or changed the From location. You must re-enter the quantity and any Controlled Items now.') );
	}
	if ( isset($_POST['StockLocationTo']) ){
		$_SESSION['Transfer']->StockLocationTo = $_POST['StockLocationTo'];
	}
	
	if ( isset($_POST['StockMainLocTo']) ){
		$_SESSION['Transfer']->StockMainLocTo = $_POST['StockMainLocTo'];
	}
	
	if ( isset($_POST['EnterTransfer']) ){
	
		$result = DB_query("SELECT * FROM stockmaster WHERE stockid='" . $_SESSION['Transfer']->TransferItem[0]->StockID ."'",$db);
		$myrow = DB_fetch_row($result);
		$InputError = false;
		$_SESSION['Transfer']->TransferItem[0]->Quantity = $_POST['Quantity'];
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
			prnMsg( _('The quantity entered must be a positive number greater than zero').' '.$_SESSION['Transfer']->TransferItem[0]->Quantity .' '.$_POST['Quantity'] , 'error');
			$InputError = true;
		}
		if ($_SESSION['Transfer']->StockLocationFrom==$_SESSION['Transfer']->StockLocationTo){
			echo '<P>';
			prnMsg( _('The locations to transfer from and to must be different'), 'error');
			$InputError = true;
		}
	
		if ($InputError==False) {
		/*All inputs must be sensible so make the stock movement records and update the locations stocks */
	
			$TransferNumber = GetNextTransNo(34,$db);
			$PeriodNo = GetPeriod (Date($DefaultDateFormat), $db);
			$SQLTransferDate = FormatDateForSQL(Date($DefaultDateFormat));
			
			//-- make sure destination bin exists in table already, if not then create record with 0 quantity ----
			$SQL = "SELECT binstock.binid,binatock.qty from binstock,bins
					WHERE binstock.stockid='" . $_SESSION['Transfer']->TransferItem[0]->StockID . "'
					AND binstock.binid ='" . $_SESSION['Transfer']->StockLocationTo . "'
					AND binstock.binid = bins.binid 
					AND bins.loccode = '".$_SESSION['Transfer']->StockMainLocTo."'";
			$ErrMsg =  _('Query error.');
			$DbgMsg =  _('The SQL that failed was');
			$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, false);
			if(DB_num_rows($Result) < 1){
				
				$SQL = "INSERT INTO binstock (binid,stockid,qty) 
						VALUES ('".$_SESSION['Transfer']->StockLocationTo."',
						'".$_SESSION['Transfer']->TransferItem[0]->StockID."',0)";				
				$ErrMsg =  _('Insert failed.');
				$DbgMsg =  _('The SQL that failed was');
				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, false);
			}
			
			//-- make sure there the quantity being transferred is equal to or less than what is in bin
			$SQL = "SELECT binstock.qty from binstock,bins
					WHERE binstock.stockid='" . $_SESSION['Transfer']->TransferItem[0]->StockID . "'
					AND binstock.binid ='" . $_SESSION['Transfer']->StockLocationFrom . "'
					AND binstock.binid = bins.binid 
					AND bins.loccode = '".$_SESSION['Transfer']->StockMainLocFrom."'";
			$ErrMsg =  _('Query error.');
			$DbgMsg =  _('The SQL that failed was');
			$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, false);
			if(DB_num_rows($Result) < 1){
				prnMsg( _('From bin location not found in database'), 'error' );
			}else{
				$myrow = DB_fetch_array($Result);
				if($myrow['Qty'] < $_SESSION['Transfer']->TransferItem[0]->Quantity){
					prnMsg( _('Unable to transfer more inventory than what is currently in the bin'), 'info' );
					include('includes/footer.inc');
					exit;
				}
			}

			//begin transaction
			$Result = DB_query('BEGIN',$db);
	
			// Need to get the current location quantity will need it later for the stock movement
/*			$SQL="SELECT BinStock.Qty FROM BinStock WHERE BinStock.StockID='" . $_SESSION['Transfer']->TransferItem[0]->StockID . "' AND BinID= '" . $_SESSION['Transfer']->StockLocationFrom . "'";
	
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
*/	
			// Insert the stock movement for the stock going out of the from location
/*Only want one stock move record for a bin transfer
			$SQL = "INSERT INTO StockMoves (StockID,
							Type,
							TransNo,
							LocCode,
							TranDate,
							Prd,
							Reference,
							Qty,
							NewQOH)
				VALUES ('" .
						$_SESSION['Transfer']->TransferItem[0]->StockID . "',
						34,
						" . $TransferNumber . ",
						'" . $_SESSION['Transfer']->StockMainLocFrom."', 
						'" . $SQLTransferDate . "'," . $PeriodNo . ",
						'bin ".$_SESSION['Transfer']->StockLocationFrom." to ". $_SESSION['Transfer']->StockLocationTo ."',
						" . -$_SESSION['Transfer']->TransferItem[0]->Quantity . ",
						" . ($QtyOnHandPrior - $_SESSION['Transfer']->TransferItem[0]->Quantity) .
					")";
	
			$ErrMsg = _('CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The stock movement record cannot be inserted because');
			$DbgMsg =  _('The following SQL to insert the stock movement record was used');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
*/	
			/*Get the ID of the StockMove... */
//			$StkMoveNo = DB_Last_Insert_ID($db);
	
	/*Insert the StockSerialMovements and update the StockSerialItems  for controlled items*/
	
			if ($_SESSION['Transfer']->TransferItem[0]->Controlled ==1){
				echo "<B><CENTER>This script is not setup to transfer Serial Items, please consult your system administrator</CENTER></B>";
//				foreach($_SESSION['Transfer']->TransferItem[0]->SerialItems as $Item){
				/*We need to add or update the StockSerialItem record and
				The StockSerialMoves as well */
	
					/*First need to check if the serial items already exists or not in the location from */
/*					$SQL = "SELECT Count(*)
						FROM StockSerialItems
						WHERE
						StockID='" . $_SESSION['Transfer']->TransferItem[0]->StockID . "'
						AND LocCode='" . $_SESSION['Transfer']->StockLocationFrom . "'
						AND SerialNo='" . $Item->BundleRef . "'";
	
					$ErrMsg =  _('The entered item code does not exist');
					$Result = DB_query($SQL,$db,$ErrMsg);
					$SerialItemExistsRow = DB_fetch_row($Result);
	
					if ($SerialItemExistsRow[0]==1){
	
						$SQL = "UPDATE StockSerialItems SET
							Quantity= Quantity - " . $Item->BundleQty . "
							WHERE
							StockID='" . $_SESSION['Transfer']->TransferItem[0]->StockID . "'
							AND LocCode='" . $_SESSION['Transfer']->StockLocationFrom . "'
							AND SerialNo='" . $Item->BundleRef . "'";
	
						$ErrMsg = _('CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The serial stock item record could not be updated because');
						$DbgMsg = _('The following SQL to update the serial stock item record was used');
						$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
					} else {
/*						/*Need to insert a new serial item record */
/*						$SQL = "INSERT INTO StockSerialItems (StockID,
											LocCode,
											SerialNo,
											Quantity)
							VALUES ('" . $_SESSION['Transfer']->TransferItem[0]->StockID . "',
							'" . $_SESSION['Transfer']->StockLocationFrom . "',
							'" . $Item->BundleRef . "',
							" . -$Item->BundleQty . ")";
	
						$ErrMsg = _('CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The serial stock item record could not be updated because');
						$DbgMsg = _('The following SQL to update the serial stock item record was used');
						$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
					}
	
					/* now insert the serial stock movement */
	
/*					$SQL = "INSERT INTO StockSerialMoves (
									StockMoveNo,
									StockID,
									SerialNo,
									MoveQty)
							VALUES (
								" . $StkMoveNo . ",
								'" . $_SESSION['Transfer']->TransferItem[0]->StockID . "',
								'" . $Item->BundleRef . "',
								-" . $Item->BundleQty . "
								)";
	
					$ErrMsg = _('CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The serial stock movement record could not be inserted because');
					$DbgMsg = _('The following SQL to insert the serial stock movement records was used');
					$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
*/	
//				}/* foreach controlled item in the serialitems array */
			} /*end if the transferred item is a controlled item */
		
			// Need to get the current location quantity will need it later for the stock movement
/*			$SQL="SELECT BinStock.Qty
				FROM BinStock
				WHERE BinStock.StockID='" . $_SESSION['Transfer']->TransferItem[0]->StockID . "'
				AND BinID= '" . $_SESSION['Transfer']->StockLocationTo . "'";					
			$ErrMsg = _('Could not retrieve QOH at the destination because');
			$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg,true);
			if (DB_num_rows($Result)==1){
				$LocQtyRow = DB_fetch_row($Result);
				$QtyOnHandPrior = $LocQtyRow[0];
			} else {
				// There must actually be some error this should never happen
				$QtyOnHandPrior = 0;
			}
*/	

			// Need to get the current location quantity will need it later for the stock movement
			$SQL="SELECT locstock.quantity
				FROM locstock
				WHERE locstock.stockid='" . $_SESSION['Transfer']->TransferItem[0]->StockID . "'
				AND loccode = '".$_SESSION['Transfer']->StockMainLocTo."'";					
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
						34,
						" . $TransferNumber . ",
						'" . $_SESSION['Transfer']->StockMainLocTo . "',
						'" . $SQLTransferDate . "',
						" . $PeriodNo . ",
						'" . _('bin transfer from ').$_SESSION['Transfer']->StockLocationFrom." to ".$_SESSION['Transfer']->StockLocationTo." - ".$_SESSION['UserID']."',
						" . $_SESSION['Transfer']->TransferItem[0]->Quantity . ",
						" . ($QtyOnHandPrior) .
					")";
	
			$ErrMsg = _('CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The stock movement record cannot be inserted because');
			$DbgMsg = _('The following SQL to insert the stock movement record was used');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
	
			/*Get the ID of the StockMove... */
			$StkMoveNo = DB_Last_Insert_ID($db);
	
	/*Insert the StockSerialMovements and update the StockSerialItems  for controlled items*/
			if ($_SESSION['Transfer']->TransferItem[0]->Controlled ==1){
				echo "<B><CENTER>This script is not setup to transfer Serial Items, please consult your system administrator</CENTER></B>";
/*				foreach($_SESSION['Transfer']->TransferItem[0]->SerialItems as $Item){
				/*We need to add or update the StockSerialItem record and
				The StockSerialMoves as well */
	
					/*First need to check if the serial items already exists or not in the location from */
	/*				$SQL = "SELECT Count(*)
						FROM StockSerialItems
						WHERE
						StockID='" . $_SESSION['Transfer']->TransferItem[0]->StockID . "'
						AND LocCode='" . $_SESSION['Transfer']->StockLocationTo . "'
						AND SerialNo='" . $Item->BundleRef . "'";
	
					$ErrMsg = _('Could not determine if the serial item exists in the transfer to location');
					$Result = DB_query($SQL,$db,$ErrMsg);
					$SerialItemExistsRow = DB_fetch_row($Result);
	
					if ($SerialItemExistsRow[0]==1){
	
						$SQL = "UPDATE StockSerialItems SET
							Quantity= Quantity + " . $Item->BundleQty . "
							WHERE
							StockID='" . $_SESSION['Transfer']->TransferItem[0]->StockID . "'
							AND LocCode='" . $_SESSION['Transfer']->StockLocationTo . "'
							AND SerialNo='" . $Item->BundleRef . "'";
	
						$ErrMsg = _('CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The serial stock item record could not be updated because');
						$DbgMsg = _('The following SQL to update the serial stock item record was used');
						$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
					} else {
						/*Need to insert a new serial item record */
		/*				$SQL = "INSERT INTO StockSerialItems (StockID,
											LocCode,
											SerialNo,
											Quantity)
							VALUES ('" . $_SESSION['Transfer']->TransferItem[0]->StockID . "',
							'" . $_SESSION['Transfer']->StockLocationTo . "',
							'" . $Item->BundleRef . "',
							" . $Item->BundleQty . ")";
	
						$ErrMsg = _('CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The serial stock item record could not be updated because');
						$DbgMsg = _('The following SQL to update the serial stock item record was used:');
						$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
					}
	
	
					/* now insert the serial stock movement */
	
	/*				$SQL = "INSERT INTO StockSerialMoves (StockMoveNo, StockID, SerialNo, MoveQty) VALUES (" . $StkMoveNo . ", '" . $_SESSION['Transfer']->TransferItem[0]->StockID . "', '" . $Item->BundleRef . "', " . $Item->BundleQty . ")";
					$ErrMsg = _('CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The serial stock movement record could not be inserted because');
					$DbgMsg = _('The following SQL to insert the serial stock movement records was used');
					$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
	*/
	//			}/* foreach controlled item in the serialitems array */
			} /*end if the transfer item is a controlled item */
	
	
			$SQL = "UPDATE binstock,bins
				SET binstock.qty = binstock.qty - " . $_SESSION['Transfer']->TransferItem[0]->Quantity . "
				WHERE binstock.stockid='" . $_SESSION['Transfer']->TransferItem[0]->StockID . "'
				AND binstock.binid ='" . $_SESSION['Transfer']->StockLocationFrom . "'
				AND binstock.binid = bins.binid 
				AND bins.loccode = '".$_SESSION['Transfer']->StockMainLocFrom."'";
	
			$ErrMsg = _('CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The location stock record could not be updated because');
			$DbgMsg = _('The following SQL to update the location stock record was used');
			$Result = DB_query($SQL,$db,$Errmsg,$DbgMsg,true);
	
			$SQL = "UPDATE binstock,bins
				SET binstock.qty = binstock.qty + " . $_SESSION['Transfer']->TransferItem[0]->Quantity . "
				WHERE binstock.stockid='" . $_SESSION['Transfer']->TransferItem[0]->StockID . "'
				AND binstock.binid ='" . $_SESSION['Transfer']->StockLocationTo . "'
				AND binstock.binid = bins.binid
				AND bins.loccode = '".$_SESSION['Transfer']->StockMainLocTo."'";
			
			$ErrMsg = _('CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The location stock record could not be updated because');
			$DbgMsg = _('The following SQL to update the location stock record was used');
			$Result = DB_query($SQL,$db,$ErrMsg, $DbgMsg, true);
	
			$Result = DB_query('COMMIT',$db);
	
			echo '<P>'. _('An inventory transfer of').' ' . $_SESSION['Transfer']->TransferItem[0]->StockID . ' - ' . $_SESSION['Transfer']->TransferItem[0]->ItemDescription . ' '. _('has been created from').' ' . $_SESSION['Transfer']->StockLocationFrom . ' '. _('to') . ' ' . $_SESSION['Transfer']->StockLocationTo . ' '._('for a quantity of').' ' . $_SESSION['Transfer']->TransferItem[0]->Quantity;
			unset ($_SESSION['Transfer']);
//			include ('includes/footer.inc');
	//		exit;
		}
	
	}
}
//End if(!isset($_POST['StockID']) && $_POST['StockLocationFrom'] == "error")----------------------------------


//BEGIN creation of form html
//--------------------------------------------------------------------------------------------------------------
//--------------------------------------------------------------------------------------------------------------
if(isset($_POST['CheckCode']) && $_POST['StockID'] == ""){
	prnMsg( _('You Must Enter Something in the Stock Code Field'), 'error' );
}

echo '<FORM ACTION="'. $_SERVER['PHP_SELF'] . '?' . SID . '" METHOD=POST>';
echo '<CENTER>
	<TABLE>
	<TR>
	<TD>'. _('Stock Code').':</TD>
	<TD><input type=text name="StockID" size=21 value="' . $_POST[StockID] . '" maxlength=20></TD>
	<TD><input type=Submit class=button class=button class=button NAME="CheckCode" VALUE="'._('Check Part').'"></TD>
	</TR>';

if (strlen($_SESSION['Transfer']->TransferItem[0]->ItemDescription)>1){
	$SQL = "SELECT binstock.qty,binstock.binid
			FROM binstock,bins
			WHERE binstock.stockid='" . $_POST['StockID'] . "'
			AND binstock.qty > 0
			AND binstock.binidd = bins.binid
			AND bins.loccode = 'BR'";
	$result = DB_query($SQL, $db);
	if (DB_num_rows($result) == 0){
		prnMsg( _('Sorry, there are Currently none in stock').' '.$_POST['StockID'], 'warn' );
		echo '<TR><TD COLSPAN=3><FONT COLOR=BLUE>' . $_SESSION['Transfer']->TransferItem[0]->ItemDescription . ' (' . $_SESSION['Transfer']->TransferItem[0]->PartUnit . ')</FONT></TD></TR>';
	} elseif (DB_num_rows($result)>0){
		while($myrow = DB_fetch_array($result)){
			echo '<TR><TD><FONT COLOR=BLUE>' . $_SESSION['Transfer']->TransferItem[0]->ItemDescription . ' (' . $_SESSION['Transfer']->TransferItem[0]->PartUnit . ')</FONT></TD><TD><FONT COLOR=GREEN>On-Hand: '. $myrow['qty'] .'</TD><TD><FONT COLOR=GREEN>'. $myrow['binid'] .'</TD></TR>';
		}
	}
}

//from Bin
echo '<TR><TD>' .('From Bin').': </TD><TD><SELECT name="StockMainLocFrom" disabled><option selected value="BR">Barnes</option></select><SELECT name="StockLocationFrom">';
$sql = "SELECT binstock.binid
	    FROM binstock,bins
	    where binstock.stockid = '". $_POST['StockID'] ."' 
	    AND binstock.qty > 0
	    AND bins.binid = binstock.binid
	    AND bins.loccode = 'BR'";

$resultStkLocs = DB_query($sql,$db);
$numrows = DB_num_rows($resultStkLocs);
if($numrows > 0){
	while ($myrow=DB_fetch_array($resultStkLocs)){
		if (isset($_SESSION['Transfer']->StockLocationFrom)){
			if ($myrow['BinID'] == $_SESSION['Transfer']->StockLocationFrom){
			     echo '<OPTION SELECTED Value="' . $myrow['binid'] . '">' . $myrow['binid'];
			} else {
			     echo '<OPTION Value="' . $myrow['binid'] . '">' . $myrow['binid'];
			}
		} else {
			 echo '<OPTION Value="' . $myrow['binid'] . '">' . $myrow['binid'];
		}
	}
}else{
	echo '<OPTION Value="error">Check Part First';
}
echo '</SELECT></TD></TR>';
//END from Bin

//to Stock Location
echo '<TR><TD>'. _('To Bin').': </TD><TD><SELECT name="StockMainLocTo" disabled><option selected value="BR">Barnes</option></select><SELECT name="StockLocationTo"> ';
if($_POST['StockID'] == ""){
	echo '<OPTION Value="error">Check Part First';
} else {
	$sql = "SELECT binid From bins where loccode = 'BR'";
	$resultStkLocs = DB_query($sql,$db);
	DB_data_seek($resultStkLocs,0);
	while ($myrow=DB_fetch_array($resultStkLocs)){
		if (isset($_SESSION['Transfer']->StockLocationTo)){
			if ($myrow['loccode'] == $_SESSION['Transfer']->StockLocationTo){
			     echo '<OPTION SELECTED Value="' . $myrow['binid'] . '">' . $myrow['binid'];
			} else {
			     echo '<OPTION Value="' . $myrow['binid'] . '">' . $myrow['binid'];
			}
		} elseif ($myrow['binid']==$_SESSION['UserStockLocation']){
			 echo '<OPTION SELECTED Value="' . $myrow['binid'] . '">' . $myrow['binid'];
			 $_SESSION['Transfer']->StockLocationTo=$myrow['binid'];
		} else {
			 echo '<OPTION Value="' . $myrow['binid'] . '">' . $myrow['binid'];
		}
	}
}
echo '</SELECT></TD></TR>';
//END to Stock Location


//Transfer quantity
echo '<TR><TD>'._('Transfer Quantity').':</TD>';
if ($_SESSION['Transfer']->TransferItem[0]->Controlled==1){
	echo '<TD ALIGN=RIGHT><INPUT TYPE=HIDDEN NAME="Quantity" VALUE=' . $_SESSION['Transfer']->TransferItem[0]->Quantity . '><A HREF="' . $rootpath .'/StockTransferControlled.php?' . SID . '">' . $_SESSION['Transfer']->TransferItem[0]->Quantity . '</A></TD></TR>';
} else {
	echo '<TD><INPUT TYPE=TEXT NAME="Quantity" SIZE=12 MAXLENGTH=12 Value=' . $_SESSION['Transfer']->TransferItem[0]->Quantity . '></TD></TR>';
}


echo "</TABLE><BR>";
if($_POST['StockID'] == ""){
	echo 'You Must Check Part Before Continuing';
} else {
	echo "<input type=Submit class=button class=button class=button NAME='EnterTransfer' VALUE='" . _('Enter Stock Transfer') . "'>";
}
echo '<HR>';


echo '<A HREF="'.$rootpath.'/StockStatus.php?' . SID . 'StockID=' . $_SESSION['Transfer']->TransferItem[0]->StockID . '">'._('Show Stock Status').'</A>';
echo '<BR><A HREF="'.$rootpath.'/StockMovements.php?' . SID . 'StockID=' . $_SESSION['Transfer']->TransferItem[0]->StockID . '">'._('Show Movements').'</A>';
echo '<BR><A HREF="'.$rootpath.'/StockUsage.php?' . SID . 'StockID=' . $_SESSION['Transfer']->TransferItem[0]->StockID . '&StockLocation=' . $_SESSION['Transfer']->StockLocationFrom . '">'._('Show Stock Usage').'</A>';
echo '<BR><A HREF="'.$rootpath.'/SelectSalesOrder.php?' . SID . 'SelectedStockItem=' . $_SESSION['Transfer']->TransferItem[0]->StockID . '&StockLocation=' . $_SESSION['Transfer']->StockLocationFrom . '">'._('Search Outstanding Sales Orders').'</A>';
echo '<BR><A HREF="'.$rootpath.'/SelectCompletedOrder.php?' . SID . 'SelectedStockItem=' . $_SESSION['Transfer']->TransferItem[0]->StockID . '">'._('Search Completed Sales Orders').'</A>';

echo '</form>';
include('includes/footer.inc');
?>

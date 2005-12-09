<?php
/* $Revision: 1.7 $ */
/* contributed by Chris Bice */

$PageSecurity = 11;
include('includes/session.inc');
$title = _('Inventory Location Transfer Shipment');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

If (isset($_POST['Submit']) OR isset($_POST['EnterMoreItems'])){
/*Trap any errors in input */

	$InputError = False; /*Start off hoping for the best */
	$TotalItems = 0;
	//Make sure this Transfer has not already been entered... aka one way around the refresh & insert new records problem
	$result = DB_query("SELECT * FROM loctransfers WHERE reference='" . $_POST['Trf_ID'] . "'",$db);
	if (DB_num_rows($result)!=0){
		$InputError = true;
		$ErrorMessage = _('This transaction has already been entered') . '. ' . _('Please start over now').'<BR>';
		unset($_POST['submit']);
		unset($_POST['EnterMoreItems']);
		for ($i=$_POST['LinesCounter']-10;$i<$_POST['LinesCounter'];$i++){
			unset($_POST['StockID' . $i]);
			unset($_POST['StockQTY' . $i]);
		}
	}
	for ($i=$_POST['LinesCounter']-10;$i<$_POST['LinesCounter'];$i++){

		$_POST['StockID' . $i]=trim(strtoupper($_POST['StockID' . $i]));
		if ($_POST['StockID' . $i]!=''){
			$result = DB_query("SELECT COUNT(stockid) FROM stockmaster WHERE stockid='" . $_POST['StockID' . $i] . "'",$db);
			$myrow = DB_fetch_row($result);
			if ($myrow[0]==0){
				$InputError = True;
				$ErrorMessage .= _('The part code entered of'). ' ' . $_POST['StockID' . $i] . ' '. _('is not set up in the database') . '. ' . _('Only valid parts can be entered for transfers'). '<BR>';
				$_POST['LinesCounter'] -= 10;
			}
			DB_free_result( $result );
			if (!is_numeric($_POST['StockQTY' . $i])){
				$InputError = True;
				$ErrorMessage .= _('The quantity entered of'). ' ' . $_POST['StockQTY' . $i] . ' '. _('for part code'). ' ' . $_POST['StockID' . $i] . ' '. _('is not numeric') . '. ' . _('The quantity entered for transfers is expected to be numeric').'<BR>';
				$_POST['LinesCounter'] -= 10;
			}
			if ($_POST['StockQTY' . $i] <= 0){
				$InputError = True;
				$ErrorMessage .= _('The quantity entered for').' '. $_POST['StockID' . $i] . ' ' . _('is less than or equal to 0') . '. ' . _('Please correct this or remove the item').'<BR>';

			}
			// Only if stock exist at this location
			$result = DB_query("SELECT quantity FROM locstock WHERE stockid='" . $_POST['StockID' . $i] . "' and loccode='".$_POST['FromStockLocation']."'",$db);
			$myrow = DB_fetch_row($result);
			if ($myrow[0] <= 0){
				$InputError = True;
				$ErrorMessage .= _('The part code entered of'). ' ' . $_POST['StockID' . $i] . ' '. _('does not have stock available for transfer.') . '.<BR>';
				$_POST['LinesCounter'] -= 10;
			}
			DB_free_result( $result );
			$TotalItems++;
		}
	}//for all LinesCounter
	if ($TotalItems == 0){
		$InputError = True;
		$ErrorMessage .= _('You must enter at least 1 Stock Item to transfer').'<BR>';
	}

/*Ship location and Receive location are different */
	If ($_POST['FromStockLocation']==$_POST['ToStockLocation']){
		$InputError=True;
		$ErrorMessage .= _('The transfer must have a different location to receive into and location sent from');
	}
}

if(isset($_POST['Submit']) AND $InputError==False){

	$ErrMsg = _('CRITICAL ERROR') . '! ' . _('Unable to BEGIN Location Transfer transaction');
	DB_query('BEGIN',$db, $ErrMsg);
	for ($i=0;$i < $_POST['LinesCounter'];$i++){

		if($_POST['StockID' . $i] != ""){
			$sql = "INSERT INTO loctransfers (reference,
								stockid,
								shipqty,
								shipdate,
								shiploc,
								recloc)
						VALUES ('" . $_POST['Trf_ID'] . "',
							'" . $_POST['StockID' . $i] . "',
							'" . $_POST['StockQTY' . $i] . "',
							'" . Date('Y-m-d') . "',
							'" . $_POST['FromStockLocation']  ."',
							'" . $_POST['ToStockLocation'] . "')";
			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('Unable to enter Location Transfer record for'). ' '.$_POST['StockID' . $i];
			$resultLocShip = DB_query($sql,$db, $ErrMsg);
		}
	}
        $ErrMsg = _('CRITICAL ERROR') . '! ' . _('Unable to COMMIT Location Transfer transaction');
        DB_query('BEGIN',$db, $ErrMsg);

	prnMsg( _('The inventory transfer records have been created successfully'),'success');
	echo '<P><A HREF="'.$rootpath.'/PDFStockLocTransfer.php?' . SID . 'TransferNo=' . $_POST['Trf_ID'] . '">'.
		_('Print the Transfer Docket'). '</A>';
	unset($_SESSION['DispatchingTransfer']);
	unset($_SESSION['Transfer']);


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

	If ($InputError==true){
		echo '<BR>';
		prnMsg($ErrorMessage, 'error');
		echo '<BR>';

	}

	echo '<HR><FORM ACTION="' . $_SERVER['PHP_SELF'] . '?'. SID . '" METHOD=POST>';

	echo '<input type=HIDDEN NAME="Trf_ID" VALUE="' . $Trf_ID . '"><h2>'. _('Inventory Location Transfer Shipment Reference').' # '. $Trf_ID. '</h2>';

	$sql = 'SELECT loccode, locationname FROM locations';
	$resultStkLocs = DB_query($sql,$db);
	echo _('From Stock Location').':<SELECT name="FromStockLocation">';
	while ($myrow=DB_fetch_array($resultStkLocs)){
		if (isset($_POST['FromStockLocation'])){
			if ($myrow['loccode'] == $_POST['FromStockLocation']){
				echo '<OPTION SELECTED Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
			} else {
				echo '<OPTION Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
			}
		} elseif ($myrow['loccode']==$_SESSION['UserStockLocation']){
			echo '<OPTION SELECTED Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
			$_POST['FromStockLocation']=$myrow['loccode'];
		} else {
			echo '<OPTION Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
		}
	}
	echo '</SELECT>';

	DB_data_seek($resultStkLocs,0); //go back to the start of the locations result
	echo _('To Stock Location').':<SELECT name="ToStockLocation">';
	while ($myrow=DB_fetch_array($resultStkLocs)){
		if (isset($_POST['ToStockLocation'])){
			if ($myrow['loccode'] == $_POST['ToStockLocation']){
				echo '<OPTION SELECTED Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
			} else {
				echo '<OPTION Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
			}
		} elseif ($myrow['loccode']==$_SESSION['UserStockLocation']){
			echo '<OPTION SELECTED Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
			$_POST['ToStockLocation']=$myrow['loccode'];
		} else {
			echo '<OPTION Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
		}
	}
	echo '</SELECT><BR>';

	echo '<CENTER><TABLE>';

	$tableheader = '<TR><TD class="tableheader">'. _('Item Code'). '</TD><TD class="tableheader">'. _('Quantity'). '</TD></TR>';
	echo $tableheader;

	$k=0; /* row counter */
	if(isset($_POST['LinesCounter'])){

		for ($i=0;$i < $_POST['LinesCounter'] AND $_POST['StockID' . $i] !='';$i++){

			if ($k==18){
				echo $tableheader;
				$k=0;
			}
			echo '<TR>
				<TD><input type=text name="StockID' . $i .'" size=21  maxlength=20 Value="' . $_POST['StockID' . $i] . '"></TD>
				<TD><input type=text name="StockQTY' . $i .'" size=5 maxlength=4 Value="' . $_POST['StockQTY' . $i] . '"></TD>
			</TR>';
		}
	}else {
		$i = 0;
	}
	// $i is incremented an extra time, so 9 to get 10...
	$z=($i + 9);

	while($i < $z) {
		echo '<TR>
			<td><input type=text name="StockID' . $i .'" size=21  maxlength=20 Value="' . $_POST['StockID' . $i] . '"></td>
			<td><input type=text name="StockQTY' . $i .'" size=5 maxlength=4 Value="' . $_POST['StockQTY' . $i] . '"></td>
		</tr>';
		$i++;
	}

	echo '</table><br>
		<input type=hidden name="LinesCounter" value='. $i .'><INPUT TYPE=SUBMIT NAME="EnterMoreItems" VALUE="'. _('Add More Items'). '"><INPUT TYPE=SUBMIT NAME="Submit" VALUE="'. _('Create Transfer Shipment'). '"><BR><HR>';
	echo '</FORM></CENTER>';
	include('includes/footer.inc');
}
?>
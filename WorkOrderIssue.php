<?php
/* $Revision: 1.11 $ */

$PageSecurity = 11;

include('includes/session.inc');
$title = _('Issue Materials To Work Order');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

echo '<A HREF="'. $rootpath . '/SelectWorkOrder.php?' . SID . '">' . _('Back to Work Orders'). '</A><BR>';

echo '<FORM ACTION="' . $_SERVER['PHP_SELF'] . '?' . SID . '" METHOD=POST>';

if (!isset($_REQUEST['WO']) OR !isset($_REQUEST['StockID'])) {
	/* This page can only be called with a purchase order number for invoicing*/
	echo '<CENTER><A HREF="' . $rootpath . '/SelectWorkOrder.php?' . SID . '">'.
		_('Select a work order to issue materials to').'</A></CENTER>';
	prnMsg(_('This page can only be opened if a work order has been selected. Please select a work order to issue materials to first'),'info');
	include ('includes/footer.inc');
	exit;
} else {
	echo '<input type="hidden" name="WO" value=' .$_REQUEST['WO'] . '>';
	$_POST['WO']=$_REQUEST['WO'];
	echo '<input type="hidden" name="StockID" value=' .$_REQUEST['StockID'] . '>';
	$_POST['StockID']=$_REQUEST['StockID'];
}
if (isset($_GET['IssueItem'])){
	$_POST['IssueItem']=$_GET['IssueItem'];
}
if (isset($_GET['FromLocation'])){
	$_POST['FromLocation'] =$_GET['FromLocation'];
}


if (isset($_POST['Process'])){ //user hit the process the work order issues entered.

	$InputError = false; //ie assume no problems for a start - ever the optomist
	$ErrMsg = _('Could not retrieve the details of the selected work order item');
	$WOResult = DB_query("SELECT workorders.loccode,
					 locations.locationname,
					 workorders.closed,
					 stockcategory.wipact,
					 stockcategory.stockact
				FROM workorders INNER JOIN locations
				ON workorders.loccode=locations.loccode
				INNER JOIN woitems
				ON workorders.wo=woitems.wo
				INNER JOIN stockmaster
				ON woitems.stockid=stockmaster.stockid
				INNER JOIN stockcategory
				ON stockmaster.categoryid=stockcategory.categoryid
				WHERE woitems.stockid='" . DB_escape_string($_POST['StockID']) . "'",
				$db,
				$ErrMsg);

	if (DB_num_rows($WOResult)==0){
		prnMsg(_('The selected work order item cannot be retrieved from the database'),'info');
		include('includes/footer.inc');
		exit;
	}
	$WORow = DB_fetch_array($WOResult);

	if ($WORow['closed']==1){
		prnMsg(_('The work order is closed - no more materials or components can be issued to it.'),'error');
		$InputError=true;
	}
	$QuantityIssued =0;
	if (is_array($_POST['SerialNos'])){ //then we are issuing a serialised item
		$QuantityIssued = count($_POST['SerialNos']); // the total quantity issued as 1 per serial no
	} elseif ( isset($_POST['Qty'])){ //then its a plain non-controlled item
		$QuantityIssued = $_POST['Qty'];
	} else { //it must be a batch/lot controlled item
		for ($i=0;$i<15;$i++){
			if (strlen($_POST['Qty'.$i])>0){
				if (!is_numeric($_POST['Qty'.$i])){
					$InputError=1;
				} else {
					$QuantityIssued += $_POST['Qty'.$i];
				} //end if the qty field is numeric
			} // end if the qty field is entered
		}//end for the 15 fields available for batch/lot entry
	}//end batch/lot controlled item

	//Need to get the current standard cost for the item being issued
	$SQL = "SELECT materialcost+labourcost+overheadcost AS cost,
			controlled,
			serialised
		FROM stockmaster
		WHERE stockid='" .$_POST['IssueItem'] . "'";
	$Result = DB_query($SQL,$db);
	$IssueItemRow = DB_fetch_array($Result);

	if ($IssueItemRow['cost']==0){
		prnMsg(_('The item being issued has a zero cost. Zero cost items cannot be issued to work orders'),'error');
		$InputError=1;
	}


	if ($InputError==false){


/************************ BEGIN SQL TRANSACTIONS ************************/

		$Result = DB_query('BEGIN',$db);
		/*Now Get the next WO Issue transaction type 28 - function in SQL_CommonFunctions*/
		$WOIssueNo = GetNextTransNo(28, $db);

		$PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']), $db);
		$SQLIssuedDate = FormatDateForSQL($_POST['IssuedDate']);
		$StockGLCode = GetStockGLCode($_POST['IssueItem'],$db);



		/* Need to get the current location quantity will need it later for the stock movement */
		$SQL="SELECT locstock.quantity
			FROM locstock
			WHERE locstock.stockid='" . DB_escape_string($_POST['IssueItem']) . "'
			AND loccode= '" . DB_escape_string($_POST['FromLocation']) . "'";

		$Result = DB_query($SQL, $db);
		if (DB_num_rows($Result)==1){
			$LocQtyRow = DB_fetch_row($Result);
			$QtyOnHandPrior = $LocQtyRow[0];
		} else {
		/*There must actually be some error this should never happen */
			$QtyOnHandPrior = 0;
		}

		$SQL = "UPDATE locstock
			SET quantity = locstock.quantity - " . $QuantityIssued . "
			WHERE locstock.stockid = '" . DB_escape_string($_POST['IssueItem']) . "'
			AND loccode = '" . DB_escape_string($_POST['FromLocation']) . "'";

		$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The location stock record could not be updated because');
		$DbgMsg =  _('The following SQL to update the location stock record was used');
		$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);


		/*Insert stock movements - with unit cost */

		$SQL = "INSERT INTO stockmoves (stockid,
						type,
						transno,
						loccode,
						trandate,
						price,
						prd,
						reference,
						qty,
						standardcost,
						newqoh)
					VALUES ('" . DB_escape_string($_POST['IssueItem']) . "',
							28,
							" . $WOIssueNo . ",
							'" . DB_escape_string($_POST['FromLocation']) . "',
							'" . Date('Y-m-d') . "',
							" . $IssueItemRow['cost'] . ",
							" . $PeriodNo . ",
							'" . DB_escape_string($_POST['WO']) . "',
							" . -$QuantityIssued . ",
							" . $IssueItemRow['cost'] . ",
							" . ($QtyOnHandPrior - $QuantityIssued) . ")";

		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('stock movement records could not be inserted when processing the work order issue because');
		$DbgMsg =  _('The following SQL to insert the stock movement records was used');
		$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

		/*Get the ID of the StockMove... */
		$StkMoveNo = DB_Last_Insert_ID($db,'stockmoves','stkmoveno');
		/* Do the Controlled Item INSERTS HERE */

		if ($IssueItemRow['controlled'] ==1){
			//the form is different for serialised items and just batch/lot controlled items
			if ($IssueItemRow['serialised']==1){
				//serialised items form has multi select box of serial numbers that contains all the available serial numbers at the location selected
				foreach ($_POST['SerialNos'] as $SerialNo){
				/* 	We need to add the StockSerialItem record and
					The StockSerialMoves as well */
				//need to test if the serialised item exists first already
					if (trim($SerialNo) != ""){

						$SQL = "INSERT INTO stockserialitems (stockid,
											loccode,
											serialno,
											quantity)
										VALUES ('" . DB_escape_string($_POST['IssueItem']) . "',
												'" . DB_escape_string($_POST['FromLocation']) . "',
												'" . DB_escape_string($SerialNo) . "',
												-1)";
						$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock item record could not be inserted because');
						$DbgMsg =  _('The following SQL to insert the serial stock item records was used');
						$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

						/** end of handle stockserialitems records */

						/* now insert the serial stock movement */
						$SQL = "INSERT INTO stockserialmoves (stockmoveno,
											stockid,
											serialno,
											moveqty)
									VALUES (" . $StkMoveNo . ",
											'" . DB_escape_string($_POST['IssueItem']) . "',
											'" . DB_escape_string($SerialNo) . "',
											-1)";
						$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock movement record could not be inserted because');
						$DbgMsg = _('The following SQL to insert the serial stock movement records was used');
						$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
					}//non blank SerialNo
				} //end for all of the potential serialised entries in the multi select box
			} else { //the item is just batch/lot controlled not serialised
			/*the form for entry of batch controlled items is only 15 possible fields */
				for($i=0;$i<15;$i++){
				/* 	We need to add the StockSerialItem record and
					The StockSerialMoves as well */
					//need to test if the batch/lot exists first already
					if (trim($_POST['BatchRef' .$i]) != ""){

						$SQL = "SELECT COUNT(*) FROM stockserialitems
								WHERE stockid='" . DB_escape_string($_POST['IssueItem']) . "'
								AND loccode = '" . DB_escape_string($_POST['FromLocation']) . "'
								AND serialno = '" . DB_escape_string($_POST['BatchRef' .$i]) . "'";
						$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Could not check if a batch/lot reference for the item already exists because');
						$DbgMsg =  _('The following SQL to test for an already existing controlled item was used');
						$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
						$AlreadyExistsRow = DB_fetch_row($Result);

						if ($AlreadyExistsRow[0]>0){
							$SQL = 'UPDATE stockserialitems SET quantity = quantity - ' . DB_escape_string($_POST['Qty' . $i]) . "
										WHERE stockid='" . DB_escape_string($_POST['IssueItem']) . "'
									 	AND loccode = '" . DB_escape_string($_POST['FromLocation']) . "'
									 	AND serialno = '" . DB_escape_string($POST['BatchRef' .$i]) . "'";
						} else {
							$SQL = "INSERT INTO stockserialitems (stockid,
												loccode,
												serialno,
												quantity)
										VALUES ('" . DB_escape_string($_POST['IssueItem']) . "',
												'" . DB_escape_string($_POST['FromLocation']) . "',
												'" . DB_escape_string($_POST['BatchRef' . $i]) . "',
												" . -(DB_escape_string($_POST['Qty'.$i])) . ")";
						}

						$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The batch/lot item record could not be inserted because');
						$DbgMsg =  _('The following SQL to insert the batch/lot item records was used');
						$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

						/** end of handle stockserialitems records */

						/** now insert the serial stock movement **/
						$SQL = "INSERT INTO stockserialmoves (stockmoveno,
											stockid,
											serialno,
											moveqty)
									VALUES (" . $StkMoveNo . ",
											'" . DB_escape_string($_POST['IssueItem']) . "',
											'" . DB_escape_string($_POST['BatchRef'.$i] ) . "',
											" . DB_escape_string($_POST['Qty'.$i] ) . ")";
						$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock movement record could not be inserted because');
						$DbgMsg = _('The following SQL to insert the serial stock movement records was used');
						$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
					}//non blank BundleRef
				} //end for all 15 of the potential batch/lot fields received
			} //end of the batch controlled stuff
		} //end if the woitem received here is a controlled item


		if ($_SESSION['CompanyRecord']['gllink_stock']==1){
		/*GL integration with stock is activated so need the GL journals to make it so */

		/*first the debit the WIP of the item being manufactured from the WO
		  the appropriate account was already retrieved into the $StockGLCode variable as the Processing code is kicked off
		  it is retrieved from the stock category record of the item by a function in SQL_CommonFunctions.inc*/

			$SQL = "INSERT INTO gltrans (type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount)
					VALUES (28,
						" . $WOIssueNo . ",
						'" . Date('Y-m-d') . "',
						" . $PeriodNo . ",
						" . $WORow['wipact'] . ",
						'" . DB_escape_string($_POST['WO']) . " " . DB_escape_string($_POST['IssueItem']) . ' x ' . DB_escape_string($QuantityIssued) . " @ " . number_format($IssueItemRow['cost'],2) . "',
						" . ($IssueItemRow['cost'] * $QuantityIssued) . ")";

			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The issue of the item to the work order GL posting could not be inserted because');
			$DbgMsg = _('The following SQL to insert the work order issue GLTrans record was used');
			$Result = DB_query($SQL,$db,$ErrMsg, $DbgMsg, true);

		/*now the credit Stock entry*/
			$SQL = "INSERT INTO gltrans (type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount)
					VALUES (28,
						" . $WOIssueNo . ",
						'" . Date('Y-m-d') . "',
						" . $PeriodNo . ",
						" . $StockGLCode['stockact'] . ",
						'" . DB_escape_string($_POST['WO']) . " " . DB_escape_string($_POST['IssueItem']) . ' x ' . DB_escape_string($QuantityIssued) . " @ " . number_format($IssueItemRow['cost'],2) . "',
						" . -($IssueItemRow['cost'] * $QuantityIssued) . ")";

			$ErrMsg =   _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stock account credit on the issue of items to a work order GL posting could not be inserted because');
			$DbgMsg =  _('The following SQL to insert the stock GLTrans record was used');
			$Result = DB_query($SQL,$db, $ErrMsg, $DbgMsg,true);

		} /* end of if GL and stock integrated and standard cost !=0 */


		//update the wo with the new qtyrecd
		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' ._('Could not update the work order cost issued to the work order because');
		$DbgMsg = _('The following SQL was used to update the work ordeer');
		$UpdateWOResult =DB_query("UPDATE workorders
						SET costissued=costissued+" . ($QuantityIssued*$IssueItemRow['cost']) . "
						WHERE wo=" . $_POST['WO'],
					$db,$ErrMsg,$DbgMsg,true);


		$SQL='COMMIT';
		$Result = DB_query($SQL,$db);

		prnMsg(_('The issue of') . ' ' . $QuantityIssued . ' ' . _('of')  . ' ' . $_POST['IssueItem'] . ' ' . _('against work order') . ' '. $_POST['WO'] . ' ' . _('has been processed'),'info');
		echo "<A HREF='$rootpath/SelectWorkOrder.php?" . SID . "'>" . _('Select a different work order for issuing materials and components against'). '</A>';
		unset($_POST['WO']);
		unset($_POST['StockID']);
		unset($_POST['IssueItem']);
		unset($_POST['FromLocation']);
		unset($_POST['Process']);
		unset($_POST['SerialNos']);
		for ($i=0;$i<15;$i++){
			unset($_POST['BatchRef'.$i]);
			unset($_POST['Qty'.$i]);
		}
		unset($_POST['Qty']);
		/*end of process work order issues entry */
		include('includes/footer.inc');
		exit;
	} //end if there were not input errors reported - so the processing was allowed to continue
} //end of if the user hit the process button



/*User hit the search button looking for an item to issue to the WO */
if (isset($_POST['Search'])){

	If ($_POST['Keywords'] AND $_POST['StockCode']) {
		prnMsg(_('Stock description keywords have been used in preference to the Stock code extract entered'),'warn');
	}
	If (strlen($_POST['Keywords'])>0) {
			//insert wildcard characters in spaces
		$_POST['Keywords'] = strtoupper($_POST['Keywords']);

		$i=0;
		$SearchString = '%';
		while (strpos($_POST['Keywords'], ' ', $i)) {
			$wrdlen=strpos($_POST['Keywords'],' ',$i) - $i;
			$SearchString=$SearchString . substr($_POST['Keywords'],$i,$wrdlen) . '%';
			$i=strpos($_POST['Keywords'],' ',$i) +1;
		}
		$SearchString = $SearchString. substr($_POST['Keywords'],$i).'%';

		if ($_POST['StockCat']=='All'){
			$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.units
					FROM stockmaster,
					stockcategory
					WHERE stockmaster.categoryid=stockcategory.categoryid
					AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
					AND stockmaster.description " . LIKE . " '$SearchString'
					AND stockmaster.discontinued=0
					AND (mbflag='B' OR mbflag='M' OR mbflag='D')
					ORDER BY stockmaster.stockid";
		} else {
			$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.units
					FROM stockmaster, stockcategory
					WHERE  stockmaster.categoryid=stockcategory.categoryid
					AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
					AND stockmaster.discontinued=0
					AND stockmaster.description " . LIKE . " '" . $SearchString . "'
					AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
					AND (mbflag='B' OR mbflag='M' OR mbflag='D')
					ORDER BY stockmaster.stockid";
		}

	} elseif (strlen($_POST['StockCode'])>0){

		$_POST['StockCode'] = strtoupper($_POST['StockCode']);
		$SearchString = '%' . $_POST['StockCode'] . '%';

		if ($_POST['StockCat']=='All'){
			$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.units
					FROM stockmaster, stockcategory
					WHERE stockmaster.categoryid=stockcategory.categoryid
					AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
					AND stockmaster.stockid " . LIKE . " '" . $SearchString . "'
					AND stockmaster.discontinued=0
					AND (mbflag='B' OR mbflag='M' OR mbflag='D')
					ORDER BY stockmaster.stockid";
		} else {
			$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.units
					FROM stockmaster, stockcategory
					WHERE stockmaster.categoryid=stockcategory.categoryid
					AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
					AND stockmaster.stockid " . LIKE . " '" . $SearchString . "'
					AND stockmaster.discontinued=0
					AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
					AND (mbflag='B' OR mbflag='M' OR mbflag='D')
					ORDER BY stockmaster.stockid";
		}
	} else {
		if ($_POST['StockCat']=='All'){
			$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.units
					FROM stockmaster, stockcategory
					WHERE  stockmaster.categoryid=stockcategory.categoryid
					AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
					AND stockmaster.discontinued=0
					AND (mbflag='B' OR mbflag='M' OR mbflag='D')
					ORDER BY stockmaster.stockid";
		} else {
			$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.units
					FROM stockmaster, stockcategory
					WHERE stockmaster.categoryid=stockcategory.categoryid
					AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
					AND stockmaster.discontinued=0
					AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
					AND (mbflag='B' OR mbflag='M' OR mbflag='D')
					ORDER BY stockmaster.stockid";
		  }
	}

	$SQL = $SQL . ' LIMIT ' . $_SESSION['DisplayRecordsMax'];

	$ErrMsg = _('There is a problem selecting the part records to display because');
	$DbgMsg = _('The SQL used to get the part selection was');
	$SearchResult = DB_query($SQL,$db,$ErrMsg, $DbgMsg);

	if (DB_num_rows($SearchResult)==0 ){
		prnMsg (_('There are no products available meeting the criteria specified'),'info');

		if ($debug==1){
			prnMsg(_('The SQL statement used was') . ':<BR>' . $SQL,'info');
		}
	}
	if (DB_num_rows($SearchResult)==1){
		$myrow=DB_fetch_array($SearchResult);
		$_POST['IssueItem'] = $myrow['stockid'];
		DB_data_seek($SearchResult,0);
	}

} //end of if search


/* Always display quantities received and recalc balance for all items on the order */

$ErrMsg = _('Could not retrieve the details of the selected work order item');
$WOResult = DB_query("SELECT workorders.loccode,
			 locations.locationname,
			 workorders.requiredby,
			 workorders.startdate,
			 workorders.closed,
			 stockmaster.description,
			  stockmaster.decimalplaces,
			 stockmaster.units,
			 woitems.qtyreqd,
			 woitems.qtyrecd
			FROM workorders INNER JOIN locations
			ON workorders.loccode=locations.loccode
			INNER JOIN woitems
			ON workorders.wo=woitems.wo
			INNER JOIN stockmaster
			ON woitems.stockid=stockmaster.stockid
			WHERE woitems.stockid='" . DB_escape_string($_POST['StockID']) . "'
			AND woitems.wo =" . $_POST['WO'],
			$db,
			$ErrMsg);

if (DB_num_rows($WOResult)==0){
	prnMsg(_('The selected work order item cannot be retrieved from the database'),'info');
	include('includes/footer.inc');
	exit;
}
$WORow = DB_fetch_array($WOResult);

if ($WORow['closed']==1){
	prnMsg(_('The selected work order has been closed and variances calculated and posted. No more issues of materials and components can be made against this work order.'),'info');
	include('includes/footer.inc');
	exit;
}

if (!isset($_POST['IssuedDate'])){
	$_POST['IssuedDate'] = Date($_SESSION['DefaultDateFormat']);
}
echo '<center><table cellpadding=2 border=0>
	<tr><td class="label">' . _('Issue to work order') . ':</td><td>' . $_POST['WO'] .'</td><td class="label">' . _('Item') . ':</td><td>' . $_POST['StockID'] . ' - ' . $WORow['description'] . '</td></tr>
	 <tr><td class="label">' . _('Manufactured at') . ':</td><td>' . $WORow['locationname'] . '</td><td class="label">' . _('Required By') . ':</td><td>' . ConvertSQLDate($WORow['requiredby']) . '</td></tr>
	 <tr><td class="label">' . _('Quantity Ordered') . ':</td><td align=right>' . number_format($WORow['qtyreqd'],$WORow['decimalplaces']) . '</td><td colspan=2>' . $WORow['units'] . '</td></tr>
	 <tr><td class="label">' . _('Already Received') . ':</td><td align=right>' . number_format($WORow['qtyrecd'],$WORow['decimalplaces']) . '</td><td colspan=2>' . $WORow['units'] . '</td></tr>
	<tr><td colspan=4><hr></td></tr>
	 <tr><td class="label">' . _('Date Material Issued') . ':</td><td>' . Date($_SESSION['DefaultDateFormat']) . '</td>
	<td class="label">' . _('Issued From') . ':</td><td>';

if (!isset($_POST['IssueItem'])){
	$LocResult = DB_query('SELECT loccode, locationname FROM locations',$db);

	echo '<select name="FromLocation">';


	if (!isset($_POST['FromLocation'])){
		$_POST['FromLocation']=$WORow['loccode'];
	}

	while ($LocRow = DB_fetch_array($LocResult)){
		if ($_POST['FromLocation'] ==$LocRow['loccode']){
			echo '<option selected value="' . $LocRow['loccode'] .'">' . $LocRow['locationname'];
		} else {
			echo '<option value="' . $LocRow['loccode'] .'">' . $LocRow['locationname'];
		}
	}
	echo '</select>';
} else {
	$LocResult = DB_query("SELECT loccode, locationname
				FROM locations
				WHERE loccode='" . $_POST['FromLocation'] . "'",
				$db);
	$LocRow = DB_fetch_array($LocResult);
	echo '<input type="hidden" name="FromLocation" value="' . $_POST['FromLocation'] . '">';
	echo $LocRow['locationname'];
}
echo '</td></tr>
	</table>
	<table>';


if (!isset($_POST['IssueItem'])){ //no item selected to issue yet
	//set up options for selection of the item to be issued to the WO
	echo '<tr><td colspan=5 class="tableheader">' . _('Material Requirements For this Work Order') . '</td></tr>';
	echo '<tr><td colspan=2 class="tableheader">' . _('Item') . '</td>
		<td class="tableheader">' . _('Qty Required') . '</td>
		<td class="tableheader">' . _('Qty Issued') . '</td></tr>';

	$RequirmentsResult = DB_query("SELECT worequirements.stockid,
						stockmaster.description,
						stockmaster.decimalplaces,
						autoissue,
						qtypu
					FROM worequirements INNER JOIN stockmaster
					ON worequirements.stockid=stockmaster.stockid
					WHERE wo=" . $_POST['WO'],
					$db);

	while ($RequirementsRow = DB_fetch_array($RequirmentsResult)){
		if ($RequirementsRow['autoissue']==0){
			echo '<tr><td><input type="submit" name="IssueItem" value="' .$RequirementsRow['stockid'] . '"></td>
			<td>' . $RequirementsRow['stockid'] . ' - ' . $RequirementsRow['description'] . '</td>';
		} else {
			echo '<tr><td class="notavailable">' . _('Auto Issue') . '<td class="notavailable">' .$RequirementsRow['stockid'] . ' - ' . $RequirementsRow['description'] .'</td>';
		}
		$IssuedAlreadyResult = DB_query("SELECT SUM(-qty) FROM stockmoves
							WHERE stockmoves.type=28
							AND stockid='" . $RequirementsRow['stockid'] . "'
							AND reference='" . DB_escape_string($_POST['WO']) . "'",
						$db);
		$IssuedAlreadyRow = DB_fetch_row($IssuedAlreadyResult);
		
		echo '<td align="right">' . number_format($WORow['qtyreqd']*$RequirementsRow['qtypu'],$RequirementsRow['decimalplaces']) . '</td>
			<td align="right">' . number_format($IssuedAlreadyRow[0],$RequirementsRow['decimalplaces']) . '</td></tr>';
	}

	echo '</table>';


	echo '<hr>';

	$SQL="SELECT categoryid,
			categorydescription
			FROM stockcategory
			WHERE stocktype='F' OR stocktype='D'
			ORDER BY categorydescription";
		$result1 = DB_query($SQL,$db);

	echo '<table><tr><td><font size=2>' . _('Select a stock category') . ':</FONT><SELECT NAME="StockCat">';

	if (!isset($_POST['StockCat'])){
		echo "<OPTION SELECTED VALUE='All'>" . _('All');
		$_POST['StockCat'] ='All';
	} else {
		echo "<OPTION VALUE='All'>" . _('All');
	}

	while ($myrow1 = DB_fetch_array($result1)) {

		if ($_POST['StockCat']==$myrow1['categoryid']){
			echo '<OPTION SELECTED VALUE=' . $myrow1['categoryid'] . '>' . $myrow1['categorydescription'];
		} else {
			echo '<OPTION VALUE='. $myrow1['categoryid'] . '>' . $myrow1['categorydescription'];
		}
	}
	?>

	</SELECT>
	<TD><FONT SIZE=2><?php echo _('Enter text extracts in the'); ?> <B><?php echo _('description'); ?></B>:</FONT></TD>
	<TD><INPUT TYPE="Text" NAME="Keywords" SIZE=20 MAXLENGTH=25 VALUE="<?php if (isset($_POST['Keywords'])) echo $_POST['Keywords']; ?>"></TD></TR>
	<TR><TD></TD>
			<TD><FONT SIZE 3><B><?php echo _('OR'); ?> </B></FONT><FONT SIZE=2><?php echo _('Enter extract of the'); ?> <B><?php echo _('Stock Code'); ?></B>:</FONT></TD>
		<TD><INPUT TYPE="Text" NAME="StockCode" SIZE=15 MAXLENGTH=18 VALUE="<?php if (isset($_POST['StockCode'])) echo $_POST['StockCode']; ?>"></TD>
			</TR>
			</TABLE>
			<CENTER><INPUT TYPE=SUBMIT NAME="Search" VALUE="<?php echo _('Search Now'); ?>">

	<script language='JavaScript' type='text/javascript'>

		document.forms[0].StockCode.select();
		document.forms[0].StockCode.focus();

	</script>

	<?php
	echo '</CENTER>';

	if (isset($SearchResult)) {

		if (DB_num_rows($SearchResult)>1){

			echo '<CENTER><TABLE CELLPADDING=2 COLSPAN=7 BORDER=1>';
			$TableHeader = '<TR><TD class="tableheader">' . _('Code') . '</TD>
						<TD class="tableheader">' . _('Description') . '</TD>
						<TD class="tableheader">' . _('Units') . '</TD></TR>';
			echo $TableHeader;
			$j = 1;
			$k=0; //row colour counter
			$ItemCodes = array();
			for ($i=1;$i<=$NumberOfOutputs;$i++){
				$ItemCodes[] =$_POST['OutputItem'.$i];
			}

			while ($myrow=DB_fetch_array($SearchResult)) {

				if (!in_array($myrow['stockid'],$ItemCodes)){
					if (function_exists('imagecreatefrompng') ){
						$ImageSource = '<IMG SRC="GetStockImage.php?SID&automake=1&textcolor=FFFFFF&bgcolor=CCCCCC&StockID=' . urlencode($myrow['stockid']). '&text=&width=64&height=64">';
					} else {
						if(file_exists($_SERVER['DOCUMENT_ROOT'] . $rootpath. '/' . $_SESSION['part_pics_dir'] . '/' . $myrow['stockid'] . '.jpg')) {
							$ImageSource = '<IMG SRC="' .$_SERVER['DOCUMENT_ROOT'] . $rootpath . '/' . $_SESSION['part_pics_dir'] . '/' . $myrow['stockid'] . '.jpg">';
						} else {
							$ImageSource = _('No Image');
						}
					}

					if ($k==1){
						echo '<tr bgcolor="#CCCCCC">';
						$k=0;
					} else {
						echo '<tr bgcolor="#EEEEEE">';
						$k=1;
					}

					$IssueLink = $_SERVER['PHP_SELF'] . '?' . SID . '&WO=' . $_POST['WO'] . '&StockID=' . $_POST['StockID'] . '&IssueItem=' . $myrow['stockid'] . '&FromLocation=' . $_POST['FromLocation'];
					printf("<TD><FONT SIZE=1>%s</FONT></TD>
							<TD><FONT SIZE=1>%s</FONT></TD>
							<TD><FONT SIZE=1>%s</FONT></TD>
							<TD>%s</TD>
							<TD><FONT SIZE=1><A HREF='%s'>"
							. _('Add to Work Order') . '</A></FONT></TD>
							</TR>',
							$myrow['stockid'],
							$myrow['description'],
							$myrow['units'],
							$ImageSource,
							$IssueLink);

					$j++;
					If ($j == 25){
						$j=1;
						echo $TableHeader;
					} //end of page full new headings if
				} //end if not already on work order
			}//end of while loop
		} //end if more than 1 row to show
		echo '</TABLE>';
	}#end if SearchResults to show
} else{ //There is an item selected to issue

	echo '<hr>';
	//need to get some details about the item to issue
	$sql = "SELECT description,
			decimalplaces,
			units,
			controlled,
			serialised
		FROM stockmaster
		WHERE stockid='" . $_POST['IssueItem'] . "'";
	$ErrMsg = _('Could not get the detail of the item being issued because');
	$IssueItemResult = DB_query($sql,$db,$ErrMsg);
	$IssueItemRow = DB_fetch_array($IssueItemResult);

	echo '<table>
		<tr><td class="label">' . _('Issuing') . ':</td>
			<td>' . $_POST['IssueItem'] . ' - ' . $IssueItemRow['description'] .'</td>
			<td class="label">' . _('Units') . ':</td><td>' . $IssueItemRow['units'] .'</td></tr>
		</table>';

	echo '<table>';

	//Now Setup the form for entering quantites of the item to be issued to the WO
	if ($IssueItemRow['controlled']==1){ //controlled

		if ($IssueItemRow['serialised']==1){ //serialised
			echo '<tr><td class="tableheader">' . _('Serial Numbers Issued') . '</td></tr>';


			$SerialNoResult = DB_query("SELECT serialno
							FROM stockserialitems
							WHERE stockid='" . $_POST['StockID'] . "'
							AND loccode='" . $_POST['FromLocation'] . "'",
						$db,_('Could not retrieve the serial numbers available at the location specified because'));
			if (DB_num_rows($SerialNoResult)==0){
				echo '<tr><td>' . _('There are no serial numbers at this location to issue') . '</td></tr>';
				echo '<tr><td align="center"><input type=submit name="Retry" value="' . _('Reselect Location or Issued Item') . '"></td></tr>';
			} else {
				echo '<tr><td><select name="SerialNos"[] multiple>';
				while ($SerialNoRow = DB_fetch_array($SerialNoResult)){
					if (in_array($SerialNoRow['serialno'],$_POST['SerialNos'])){
						echo '<option selected value="' . $SerialNoRow['serialno'] . '">' . $SerialNoRow['serialno'] . '</option>';
					} else {
						echo '<option value="' . $SerialNoRow['serialno'] . '">' . $SerialNoRow['serialno'] . '</option>';
					}
				}
				echo '</select></td></tr>';
				echo '<input type="hidden" name="IssueItem" value="' . $_POST['IssueItem'] . '">';
				echo '<tr><td align="center"><input type=submit name="Process" value="' . _('Process Items Issued') . '"></td></tr>';
			}
		} else { //controlled but not serialised - just lot/batch control
			echo '<tr><td colspan="2" class="tableheader">' . _('Batch/Lots Issued') . '</td></tr>';
			for ($i=0;$i<15;$i++){
				echo '<tr><td><input type="textbox" name="BatchRef' . $i .'" ';
				echo '></td>
				      <td><input type="textbox" name="Qty' . $i .'"></td></tr>';
			}
			echo '<input type="hidden" name="IssueItem" value="' . $_POST['IssueItem'] . '">';
			echo '<tr><td align="center" colspan=2><input type=submit name="Process" value="' . _('Process Items Issued') . '"></td></tr>';
		} //end of lot/batch control
	} else { //not controlled - an easy one!
		echo '<input type="hidden" name="IssueItem" value="' . $_POST['IssueItem'] . '">';
		echo '<tr><td>' . _('Quantity Issued') . ':</td>
			  <td><input type="textbox" name="Qty"></tr>';
		echo '<tr><td align="center"><input type=submit name="Process" value="' . _('Process Items Issued') . '"></td></tr>';
	}
} //end if selecting new item to issue or entering the issued item quantites
echo '</table>';
echo '</FORM>';

include('includes/footer.inc');
?>
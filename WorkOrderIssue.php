<?php
/* $Revision: 1.7 $ */

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
if (!isset($_GET['IssueItem'])){
	$_POST['IssueItem']=$_GET['IssueItem'];
}




if (isset($_POST['Process'])){ //user hit the process the work order issues entered.

	$InputError = false; //ie assume no problems for a start - ever the optomist
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
					 woitems.qtyrecd,
					 woitems.stdcost,
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
		include('includes/footer.in');
		exit;
	}
	$WORow = DB_fetch_array($WOResult);

	

	if ($InputError==false){
/************************ BEGIN SQL TRANSACTIONS ************************/

		$Result = DB_query('BEGIN',$db);
		/*Now Get the next WOReceipt transaction type 26 - function in SQL_CommonFunctions*/
		$WOReceiptNo = GetNextTransNo(26, $db);

		$PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']), $db);
		$SQLIssuedDate = FormatDateForSQL($_POST['IssuedDate']);
		$StockGLCode = GetStockGLCode($_POST['StockID'],$db);

	//Recalculate the standard for the item if there were no items previously received against the work order
	


		/* Need to get the current location quantity will need it later for the stock movement */
		$SQL="SELECT locstock.quantity
			FROM locstock
			WHERE locstock.stockid='" . DB_escape_string($_POST['StockID']) . "'
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
				SET quantity = locstock.quantity + " . $QuantityReceived . "
				WHERE locstock.stockid = '" . DB_escape_string($_POST['StockID']) . "'
				AND loccode = '" . DB_escape_string($_POST['FromLocation']) . "'";

		$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The location stock record could not be updated because');
		$DbgMsg =  _('The following SQL to update the location stock record was used');
		$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

		$WOReceiptNo = GetNextTransNo(26,$db);
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
					VALUES ('" . DB_escape_string($_POST['StockID']) . "',
							26,
							" . $WOReceiptNo . ",
							'" . DB_escape_string($_POST['FromLocation']) . "',
							'" . Date('Y-m-d') . "',
							" . $WORow['stdcost'] . ",
							" . $PeriodNo . ",
							'" . DB_escape_string($_POST['WO']) . "',
							" . $QuantityReceived . ",
							" . $WORow['stdcost'] . ",
							" . ($QtyOnHandPrior + $QuantityReceived) . ")";

		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('stock movement records could not be inserted when processing the work order receipt because');
		$DbgMsg =  _('The following SQL to insert the stock movement records was used');
		$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

		/*Get the ID of the StockMove... */
		$StkMoveNo = DB_Last_Insert_ID($db,'stockmoves','stkmoveno');
		/* Do the Controlled Item INSERTS HERE */

		if ($WORow['controlled'] ==1){
			//the form is different for serialised items and just batch/lot controlled items
			if ($WORow['serialised']==1){
				//serialised items form has a possible 60 fields for entry of serial numbers - 12 rows x 5 per row
				for($i=0;$i<60;$i++){
				/* 	We need to add the StockSerialItem record and
					The StockSerialMoves as well */
				//need to test if the serialised item exists first already
					if (trim($_POST['SerialNo' .$i]) != ""){
						$LastRef = trim($_POST['SerialNo' .$i]);
						//already checked to ensure there are no duplicate serial numbers entered
						$SQL = "INSERT INTO stockserialitems (stockid,
											loccode,
											serialno,
											quantity)
										VALUES ('" . DB_escape_string($_POST['StockID']) . "',
												'" . DB_escape_string($_POST['FromLocation']) . "',
												'" . DB_escape_string($_POST['SerialNo' . $i]) . "',
												1)";
						$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock item record could not be inserted because');
						$DbgMsg =  _('The following SQL to insert the serial stock item records was used');
						$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

						/** end of handle stockserialitems records */

						/** now insert the serial stock movement **/
						$SQL = "INSERT INTO stockserialmoves (stockmoveno,
											stockid,
											serialno,
											moveqty)
									VALUES (" . $StkMoveNo . ",
											'" . DB_escape_string($_POST['StockID']) . "',
											'" . DB_escape_string($_POST['SerialNo' .$i]) . "',
											1)";
						$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock movement record could not be inserted because');
						$DbgMsg = _('The following SQL to insert the serial stock movement records was used');
						$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
					}//non blank SerialNo
				} //end for all 60 of the potential serialised fields received
			} else { //the item is just batch/lot controlled not serialised
			/*the form for entry of batch controlled items is only 15 possible fields */
				for($i=0;$i<15;$i++){
				/* 	We need to add the StockSerialItem record and
					The StockSerialMoves as well */
				//need to test if the batch/lot exists first already
					if (trim($_POST['BatchRef' .$i]) != ""){
						$LastRef = trim($_POST['BatchRef' .$i]);
						$SQL = "SELECT COUNT(*) FROM stockserialitems
								WHERE stockid='" . DB_escape_string($_POST['StockID']) . "'
								AND loccode = '" . DB_escape_string($_POST['FromLocation']) . "'
								AND serialno = '" . DB_escape_string($_POST['BatchRef' .$i]) . "'";
						$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Could not check if a serial number for the stock item already exists because');
						$DbgMsg =  _('The following SQL to test for an already existing serialised stock item was used');
						$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
						$AlreadyExistsRow = DB_fetch_row($Result);

						if ($AlreadyExistsRow[0]>0){
							$SQL = 'UPDATE stockserialitems SET quantity = quantity + ' . DB_escape_string($_POST['Qty' . $i]) . "
										WHERE stockid='" . DB_escape_string($_POST['StockID']) . "'
									 	AND loccode = '" . DB_escape_string($_POST['FromLocation']) . "'
									 	AND serialno = '" . DB_escape_string($POST['BatchRef' .$i]) . "'";
						} else {
							$SQL = "INSERT INTO stockserialitems (stockid,
												loccode,
												serialno,
												quantity)
										VALUES ('" . DB_escape_string($_POST['StockID']) . "',
												'" . DB_escape_string($_POST['FromLocation']) . "',
												'" . DB_escape_string($_POST['BatchRef' . $i]) . "',
												" . DB_escape_string($_POST['Qty'.$i]) . ")";
						}

						$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock item record could not be inserted because');
						$DbgMsg =  _('The following SQL to insert the serial stock item records was used');
						$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

						/** end of handle stockserialitems records */

						/** now insert the serial stock movement **/
						$SQL = "INSERT INTO stockserialmoves (stockmoveno,
											stockid,
											serialno,
											moveqty)
									VALUES (" . $StkMoveNo . ",
											'" . DB_escape_string($_POST['StockID']) . "',
											'" . DB_escape_string($_POST['BatchRef'.$i] ) . "',
											" . DB_escape_string($_POST['Qty'.$i] ) . ")";
						$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock movement record could not be inserted because');
						$DbgMsg = _('The following SQL to insert the serial stock movement records was used');
						$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
					}//non blank BundleRef
				} //end for all 15 of the potential batch/lot fields received
			} //end of the batch controlled stuff
		} //end if the woitem received here is a controlled item


		/* If GLLink_Stock then insert GLTrans to debit the GL Code  and credit GRN Suspense account at standard cost*/
		if ($_SESSION['CompanyRecord']['gllink_stock']==1 AND ($WORow['stdcost']*$QuantityReceived)!=0){
		/*GL integration with stock is activated so need the GL journals to make it so */

		/*first the debit the finished stock of the item received from the WO
		  the appropriate account was already retrieved into the $StockGLCode variable as the Processing code is kicked off
		  it is retrieved from the stock category record of the item by a function in SQL_CommonFunctions.inc*/

			$SQL = "INSERT INTO gltrans (type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount)
					VALUES (26,
						" . $WOReceiptNo . ",
						'" . Date('Y-m-d') . "',
						" . $PeriodNo . ",
						" . $StockGLCode['stockact'] . ",
						'" . DB_escape_string($_POST['WO']) . " " . DB_escape_string($_POST['StockID']) . " - " . DB_escape_string($WORow['description']) . ' x ' . DB_escape_string($QuantityReceived) . " @ " . number_format($WORow['stdcost'],2) . "',
						" . ($WORow['stdcost'] * $QuantityReceived) . ")";

			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The receipt of work order finished stock GL posting could not be inserted because');
			$DbgMsg = _('The following SQL to insert the work order receipt of finished items GLTrans record was used');
			$Result = DB_query($SQL,$db,$ErrMsg, $DbgMsg, true);

		/*now the credit WIP entry*/
			$SQL = "INSERT INTO gltrans (type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount)
					VALUES (26,
						" . $WOReceiptNo . ",
						'" . Date('Y-m-d') . "',
						" . $PeriodNo . ",
						" . $StockGLCode['wipact'] . ",
						'" . DB_escape_string($_POST['WO']) . " " . DB_escape_string($_POST['StockID']) . " - " . DB_escape_string($WORow['description']) . ' x ' . DB_escape_string($QuantityReceived) . " @ " . number_format($WORow['stdcost'],2) . "',
						" . -($WORow['stdcost'] * $QuantityReceived) . ")";

			$ErrMsg =   _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The WIP credit on receipt of finsihed items from a work order GL posting could not be inserted because');
			$DbgMsg =  _('The following SQL to insert the WIP GLTrans record was used');
			$Result = DB_query($SQL,$db, $ErrMsg, $DbgMsg,true);

		} /* end of if GL and stock integrated and standard cost !=0 */


		//update the wo with the new qtyrecd
		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' ._('Could not update the work order item record with the total quantity received because');
		$DbgMsg = _('The following SQL was used to update the work ordeer');
		$UpdateWOResult =DB_query("UPDATE woitems
						SET qtyrecd=qtyrecd+" . $QuantityReceived . ",
						    nextlotsnref='" . $LastRef . "'
						WHERE wo=" . $_POST['WO'] . "
						AND stockid='" . $_POST['StockID'] . "'",
					$db,$ErrMsg,$DbgMsg,true);


		$SQL='COMMIT';
		$Result = DB_query($SQL,$db);

		prnMsg(_('The receipt of') . ' ' . $QuantityReceived . ' ' . $WORow['units'] . ' ' . _('of')  . $_POST['StockID'] . ' - ' . $WORow['description'] . ' ' . _('against work order') . ' '. $_POST['WO'] . ' ' . _('has been processed'),'info');
		echo "<A HREF='$rootpath/SelectWorkOrder.php?" . SID . "'>" . _('Select a different work order for receiving finished stock against'). '</A>';
		unset($_POST['WO']);
		unset($_POST['StockID']);
		unset($_POST['FromLocation']);
		unset($_POST['Process']);
		for ($i=1;$i<60;$i++){
			unset($_POST['SerialNo'.$i]);
			if ($i<15){
				unset($_POST['BatchRef'.$i]);
				unset($_POST['Qty'.$i]);
			}
		}
		/*end of process work order goods received entry */
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
			WHERE woitems.stockid='" . DB_escape_string($_POST['StockID']) . "'",
			$db,
			$ErrMsg);

if (DB_num_rows($WOResult)==0){
	prnMsg(_('The selected work order item cannot be retrieved from the database'),'info');
	include('includes/footer.in');
	exit;
}
$WORow = DB_fetch_array($WOResult);

if ($WORow['closed']==1){
	prnMsg(_('The selected work order has been closed and variances calculated and posted. No more issues of materials and components can be made against this work order.'),'info');
	include('includes/footer.in');
	exit;
}

if (!isset($_POST['IssuedDate'])){
	$_POST['IssuedDate'] = Date($_SESSION['DefaultDateFormat']);
}
echo '<center><table cellpadding=2 border=0>
	<tr><td>' . _('Issue to work order') . ':</td><td>' . $_POST['WO'] .'</td><td>' . _('Item') . ':</td><td>' . $_POST['StockID'] . ' - ' . $WORow['description'] . '</td></tr>
	 <tr><td>' . _('Manufactured at') . ':</td><td>' . $WORow['locationname'] . '</td><td>' . _('Required By') . ':</td><td>' . ConvertSQLDate($WORow['requiredby']) . '</td></tr>
	 <tr><td>' . _('Quantity Ordered') . ':</td><td align=right>' . number_format($WORow['qtyreqd'],$WORow['decimalplaces']) . '</td><td colspan=2>' . $WORow['units'] . '</td></tr>
	 <tr><td>' . _('Already Received') . ':</td><td align=right>' . number_format($WORow['qtyrecd'],$WORow['decimalplaces']) . '</td><td colspan=2>' . $WORow['units'] . '</td></tr>
	<tr><td colspan=4><hr></td></tr>
	 <tr><td>' . _('Date Material Issued') . ':</td><td>' . Date($_SESSION['DefaultDateFormat']) . '</td><td>' . _('Issued From') . ':</td><td>
	 <select name="FromLocation">';


if (!isset($_POST['FromLocation'])){
	$_POST['FromLocation']=$WORow['loccode'];
}
$LocResult = DB_query('SELECT loccode, locationname FROM locations',$db);
while ($LocRow = DB_fetch_array($LocResult)){
	if ($_POST['FromLocation'] ==$LocRow['loccode']){
		echo '<option selected value="' . $LocRow['loccode'] .'">' . $LocRow['locationname'];
	} else {
		echo '<option value="' . $LocRow['loccode'] .'">' . $LocRow['locationname'];
	}
}
echo '</select></td></tr>
	</table>
	<table>';


if (!isset($_POST['IssueItem'])){ //no item selected to issue yet
	//set up options for selection of the item to be issued to the WO
	echo '<tr><td colspan=2 class="tableheader">' . _('Material Requirements For this Work Order') . '</td></tr>';
	$RequirmentsResult = DB_query("SELECT worequirements.stockid,
						stockmaster.description,
						autoissue
					FROM worequirements INNER JOIN stockmaster
					ON worequirements.stockid=stockmaster.stockid
					WHERE wo=" . $_POST['WO'],
					$db);
	
	while ($RequirementsRow = DB_fetch_array($RequirmentsResult)){
		if ($RequirementsRow['autoissue']==0){
			echo '<tr><td><input type="submit" name="IssueItem" value="' .$RequirementsRow['stockid'] . '"></td>
			<td>' . $RequirementsRow['stockid'] . ' - ' . $RequirementsRow['description'] . '</td></tr>';
		} else {
			echo '<tr><td><i>' . _('Auto Issue') . '</i><td><i>' .$RequirementsRow['stockid'] . ' - ' . $RequirementsRow['description'] .'</i></td></tr>';
		}
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
							$_SERVER['PHP_SELF'] . '?' . SID . '&WO=' . $_POST['WO'] . '&StockID=' . $_POST['StockID'] . '&IssueItem=' . $myrow['stockid']);
	
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
} else{ //The item is selected to issue
	echo '<input type="hidden" name="IssueItem" value="' . $_POST['IssueItem'] . '">';
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

	//Now Setup the form for entering quantites of the item to be issued to the WO
	if ($WORow['controlled']==1){ //controlled
			
		if ($IssueItemRow['serialised']==1){ //serialised
			echo '<tr><td colspan="5" class="tableheader">' . _('Serial Numbers Issued') . '</td></tr>';
			echo '<tr>';
			for ($i=0;$i<60;$i++){
				if (($i/5 -intval($i/5))==0){
					echo '</tr><tr>';
				}
				echo '<td><input type="textbox" name="SerialNo' . $i . '"></td>';
			}
			echo '</tr>';
			echo '<tr><td align="center" colspan=5><input type=submit name="Process" value="' . _('Process Items Issued') . '"></td></tr>';
		} else { //controlled but not serialised - just lot/batch control
			echo '<tr><td colspan="2" class="tableheader">' . _('Batch/Lots Issued') . '</td></tr>';
			for ($i=0;$i<15;$i++){
				echo '<tr><td><input type="textbox" name="BatchRef' . $i .'" ';
				echo '></td>
				      <td><input type="textbox" name="Qty' . $i .'"></td></tr>';
			}
			echo '<tr><td align="center" colspan=2><input type=submit name="Process" value="' . _('Process Items Issued') . '"></td></tr>';
		} //end of lot/batch control
	} else { //not controlled - an easy one!
		echo '<tr><td>' . _('Quantity Issued') . ':</td>
			  <td><input type="textbox" name="Qty"></tr>';
		echo '<tr><td align="center"><input type=submit name="Process" value="' . _('Process Items Issued') . '"></td></tr>';
	}
} //end if selecting new item to issue or entering the issued item quantites
echo '</table>';
echo '</FORM>';

include('includes/footer.inc');
?>
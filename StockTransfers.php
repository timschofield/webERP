<?php

/* Entry of point to point stock location transfers of a single part. */

require(__DIR__ . '/includes/session.php');

$Title = __('Stock Transfers');
$ViewTopic = "Inventory";
$BookMark = "LocationTransfers";
include('includes/header.php');

include('includes/DefineSerialItems.php');
include('includes/DefineStockTransfers.php');
include('includes/SQL_CommonFunctions.php');

if(isset($_GET['New'])) {
	unset($_SESSION['Transfer']);
}

if(isset($_GET['From'])) {
	$_POST['StockLocationFrom']=$_GET['From'];
	$_POST['StockLocationTo']=$_GET['To'];
	$_POST['Quantity']=$_GET['Quantity'];
}

if(isset($_POST['CheckCode'])) {

	echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
		'/images/magnifier.png" title="',// Icon image.
		__('Dispatch'), '" /> ',// Icon title.
		__('Select Item to Transfer'), '</p>';// Page title.

	if(mb_strlen($_POST['StockText'])>0) {
		$SQL="SELECT stockid,
					description
			 FROM stockmaster
			 WHERE description " . LIKE . " '%" . $_POST['StockText'] . "%'";
	} else {
		$SQL="SELECT stockid,
					description
			  FROM stockmaster
			  WHERE stockid " . LIKE . " '%" . $_POST['StockCode']."%'";
	}
	$ErrMsg=__('The stock information cannot be retrieved because');
	$Result = DB_query($SQL, $ErrMsg);
	echo '<table class="selection">
		<thead>
			<tr>
				<th class="SortedColumn">' . __('Stock Code') . '</th>
				<th class="SortedColumn">' . __('Stock Description') . '</th>
			</tr>
		</thead>
		<tbody>';
	while($MyRow = DB_fetch_array($Result)) {
		echo '<tr>
				<td>' . $MyRow['stockid'] . '</td>
				<td>' . $MyRow['description'] . '</td>
				<td><a href="' . $RootPath . '/StockTransfers.php?StockID='.$MyRow['stockid'].'&amp;Description='.$MyRow['description'].'&amp;NewTransfer=Yes&amp;Quantity='. filter_number_format($_POST['Quantity']).'&amp;From='.$_POST['StockLocationFrom'].'&amp;To='.$_POST['StockLocationTo'].'">'
				.__('Transfer') . '</a></td>
			</tr>';

	}
	echo '</tbody></table>';
	include('includes/footer.php');
	exit();
}

$NewTransfer = false; /*initialise this first then determine from form inputs */

if(isset($_GET['NewTransfer'])) {
	 unset($_SESSION['Transfer']);
	 unset($_SESSION['TransferItem']); /*this is defined in bulk transfers but needs to be unset for individual transfers */
	 $NewTransfer=$_GET['NewTransfer'];
}


if(isset($_GET['StockID'])) {	/*carry the stockid through to the form for additional inputs */
	$_POST['StockID'] = trim(mb_strtoupper($_GET['StockID']));
} elseif(isset($_POST['StockID'])) {	/* initiate a new transfer only if the StockID is different to the previous entry */
	if(isset($_SESSION['Transfer']->TransferItem[0])) {
		if($_POST['StockID'] != $_SESSION['Transfer']->TransferItem[0]->StockID) {
			unset($_SESSION['Transfer']);
			$NewTransfer = true;
		}
	} else { /* _SESSION['Transfer']->TransferItem[0] is not set so */
		$NewTransfer = true;
	}
}

if($NewTransfer) {

	if(!isset($_POST['StockLocationFrom'])) {
		$_POST['StockLocationFrom']='';
		$StockLocationFromAccount = '';
	}
	else
	{
		$SQL = "SELECT glaccountcode
				FROM locations
				INNER JOIN locationusers
				ON locationusers.loccode=locations.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canupd=1
				WHERE locations.loccode = '" . $_POST['StockLocationFrom'] . "'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);
		{
			$StockLocationFromAccount = $MyRow['glaccountcode'];
		}
	}
	if(!isset($_POST['StockLocationTo'])) {
		$_POST['StockLocationTo']='';
		$StockLocationToAccount = '';
	}
	else
	{
		$SQL = "SELECT glaccountcode
				FROM locations
				INNER JOIN locationusers
				ON locationusers.loccode=locations.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canupd=1
				WHERE locations.loccode = '" . $_POST['StockLocationTo'] . "'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);
		{
			$StockLocationToAccount = $MyRow['glaccountcode'];
		}

	}

	$_SESSION['Transfer']= new StockTransfer(0,
										$_POST['StockLocationFrom'],
										'',
										$StockLocationFromAccount,
										$_POST['StockLocationTo'],
										'',
										$StockLocationToAccount,
										Date($_SESSION['DefaultDateFormat'])
										);
	$_SESSION['Transfer']->StockLocationTo = $_POST['StockLocationTo'];
	$Result = DB_query("SELECT description,
							units,
							mbflag,
							actualcost as standardcost,
							controlled,
							serialised,
							perishable,
							decimalplaces
						FROM stockmaster
						WHERE stockid='" . trim(mb_strtoupper($_POST['StockID'])) . "'");

	if(DB_num_rows($Result) == 0) {
		prnMsg( __('Unable to locate Stock Code').' '.mb_strtoupper($_POST['StockID']), 'error' );
	} elseif(DB_num_rows($Result)>0) {
		$MyRow = DB_fetch_array($Result);
		$_SESSION['Transfer']->TransferItem[0] = new LineItem(	trim(mb_strtoupper($_POST['StockID'])),
															$MyRow['description'],
						 									filter_number_format($_POST['Quantity']),
															$MyRow['units'],
															$MyRow['controlled'],
															$MyRow['serialised'],
															$MyRow['perishable'],
															$MyRow['decimalplaces']);


		$_SESSION['Transfer']->TransferItem[0]->StandardCost = $MyRow['standardcost'];

		if($MyRow['mbflag']=='D' OR $MyRow['mbflag']=='A' OR $MyRow['mbflag']=='K') {
			prnMsg(__('The part entered is either or a dummy part or an assembly or a kit-set part') . '. ' . __('These parts are not physical parts and no stock holding is maintained for them') . '. ' . __('Stock Transfers are therefore not possible'),'warn');
			echo '.<hr />';
			echo '<a href="' . $RootPath . '/StockTransfers.php?NewTransfer=Yes">' . __('Enter another Transfer') . '</a>';
			unset($_SESSION['Transfer']);
			include('includes/footer.php');
			exit();
		}
	}
}

if(isset($_POST['Quantity'])
	AND isset($_SESSION['Transfer']->TransferItem[0]->Controlled)
	AND $_SESSION['Transfer']->TransferItem[0]->Controlled==0) {

	$_SESSION['Transfer']->TransferItem[0]->Quantity = filter_number_format($_POST['Quantity']);

}

if(isset($_POST['StockLocationFrom'])
	AND $_POST['StockLocationFrom'] != $_SESSION['Transfer']->StockLocationFrom ) {

	$SQL = "SELECT glaccountcode
			FROM locations
			INNER JOIN locationusers
			ON locationusers.loccode=locations.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canupd=1
			WHERE locations.loccode = '" . $_POST['StockLocationFrom'] . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	{
		$_SESSION['Transfer']->StockLocationFromAccount = $MyRow['glaccountcode'];
	}

	$_SESSION['Transfer']->StockLocationFrom = $_POST['StockLocationFrom'];
	$_SESSION['Transfer']->StockLocationTo = $_POST['StockLocationTo'];
	$_SESSION['Transfer']->TransferItem[0]->Quantity=filter_number_format($_POST['Quantity']);
	$_SESSION['Transfer']->TransferItem[0]->SerialItems=array();
}

if(isset($_POST['StockLocationTo'])
	AND $_POST['StockLocationTo'] != $_SESSION['Transfer']->StockLocationTo) {

	$SQL = "SELECT glaccountcode
			FROM locations
			INNER JOIN locationusers
			ON locationusers.loccode=locations.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canupd=1
			WHERE locations.loccode = '" . $_POST['StockLocationTo'] . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	{
		$_SESSION['Transfer']->StockLocationToAccount = $MyRow['glaccountcode'];
	}

	$_SESSION['Transfer']->StockLocationTo = $_POST['StockLocationTo'];
}

if(isset($_POST['EnterTransfer']) ) {

	$Result = DB_query("SELECT * FROM stockmaster WHERE stockid='" . $_SESSION['Transfer']->TransferItem[0]->StockID ."'");
	$MyRow = DB_fetch_row($Result);
	$InputError = false;
	if(DB_num_rows($Result)==0) {
		echo '<br />';
		prnMsg(__('The entered item code does not exist'), 'error');
		$InputError = true;
	} elseif(!is_numeric($_SESSION['Transfer']->TransferItem[0]->Quantity)) {
		echo '<br />';
		prnMsg( __('The quantity entered must be numeric'), 'error' );
		$InputError = true;
	} elseif($_SESSION['Transfer']->TransferItem[0]->Quantity <= 0) {
		echo '<br />';
		prnMsg( __('The quantity entered must be a positive number greater than zero'), 'error');
		$InputError = true;
	}
	if($_SESSION['Transfer']->StockLocationFrom==$_SESSION['Transfer']->StockLocationTo) {
		echo '<br />';
		prnMsg( __('The locations to transfer from and to must be different'), 'error');
		$InputError = true;
	}

	if($InputError==false) {
/*All inputs must be sensible so make the stock movement records and update the locations stocks */

		$TransferNumber = GetNextTransNo(16);
		$PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']));
		$SQLTransferDate = FormatDateForSQL(Date($_SESSION['DefaultDateFormat']));

		DB_Txn_Begin();

		// Need to get the current location quantity will need it later for the stock movement
		$SQL="SELECT locstock.quantity
				FROM locstock
				WHERE locstock.stockid='" . $_SESSION['Transfer']->TransferItem[0]->StockID . "'
				AND loccode= '" . $_SESSION['Transfer']->StockLocationFrom . "'";

		$ErrMsg =  __('Could not retrieve the QOH at the sending location because');
		$Result = DB_query($SQL, $ErrMsg, '', true);

		if(DB_num_rows($Result)==1) {
			$LocQtyRow = DB_fetch_row($Result);
			$QtyOnHandPrior = $LocQtyRow[0];
		} else {
			// There must actually be some error this should never happen
			$QtyOnHandPrior = 0;
		}
		if($_SESSION['ProhibitNegativeStock']==1
			AND $QtyOnHandPrior<$_SESSION['Transfer']->TransferItem[0]->Quantity) {
			prnMsg( __('There is insufficient stock to make this transfer and webERP is setup to prevent negative stock'), 'warn');
			include('includes/footer.php');
			exit();
		}
		// Insert outgoing inventory GL transaction if any of the locations has a GL account code:
		if(($_SESSION['Transfer']->StockLocationFromAccount !='' OR $_SESSION['Transfer']->StockLocationToAccount !='') AND
			($_SESSION['Transfer']->StockLocationFromAccount != $_SESSION['Transfer']->StockLocationToAccount)) {
			// Get the account code:
			if($_SESSION['Transfer']->StockLocationFromAccount !='') {
				$AccountCode = $_SESSION['Transfer']->StockLocationFromAccount;
			} else {
				$StockGLCode = GetStockGLCode($_SESSION['Transfer']->TransferItem[0]->StockID);// Get Category's account codes.
				$AccountCode = $StockGLCode['stockact'];// Select account code for stock.
			}
			// Get the item cost:
			$SQLstandardcost = "SELECT stockmaster.actualcost AS standardcost
								FROM stockmaster
								WHERE stockmaster.stockid ='" . $_SESSION['Transfer']->TransferItem[0]->StockID . "'";
			$ErrMsg = __('The standard cost of the item cannot be retrieved because');
			$ResultStandardCost = DB_query($SQLstandardcost, $ErrMsg);
			$MyRow = DB_fetch_array($ResultStandardCost);
			$StandardCost = $MyRow['standardcost'];// QUESTION: Standard cost for: Assembly (value="A") and Manufactured (value="M") items ?
			// Insert record:
			$SQL = "INSERT INTO gltrans (
					periodno,
					trandate,
					type,
					typeno,
					account,
					narrative,
					amount)
				VALUES ('" .
					$PeriodNo . "','" .
					$SQLTransferDate . "',
					16,'" .
					$TransferNumber . "','" .
					$AccountCode . "','" .
					mb_substr($_SESSION['Transfer']->StockLocationFrom.' - '.$_SESSION['Transfer']->TransferItem[0]->StockID.' x '.$_SESSION['Transfer']->TransferItem[0]->Quantity.' @ '. $StandardCost, 0, 200) . "','" .
					-$_SESSION['Transfer']->TransferItem[0]->Quantity * $StandardCost . "')";
					$ErrMsg =  __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The outgoing inventory GL transacction record could not be inserted because');
					$Result = DB_query($SQL, $ErrMsg, '', true);
		}
		// Insert the stock movement for the stock going out of the from location
		$SQL = "INSERT INTO stockmoves(stockid,
										type,
										transno,
										loccode,
										trandate,
										userid,
										prd,
										reference,
										qty,
										newqoh)
				VALUES (
						'" . $_SESSION['Transfer']->TransferItem[0]->StockID . "',
						16,
						'" . $TransferNumber . "',
						'" . $_SESSION['Transfer']->StockLocationFrom . "',
						'" . $SQLTransferDate . "',
						'" . $_SESSION['UserID'] . "',
						'" . $PeriodNo . "',
						'To " . $_SESSION['Transfer']->StockLocationTo ."',
						'" . round(-$_SESSION['Transfer']->TransferItem[0]->Quantity,$_SESSION['Transfer']->TransferItem[0]->DecimalPlaces)  . "',
						'" . ($QtyOnHandPrior - round($_SESSION['Transfer']->TransferItem[0]->Quantity,$_SESSION['Transfer']->TransferItem[0]->DecimalPlaces)) . "'
						)";

		$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The stock movement record cannot be inserted because');
		$Result = DB_query($SQL, $ErrMsg, '', true);

		/*Get the ID of the StockMove... */
		$StkMoveNo = DB_Last_Insert_ID('stockmoves','stkmoveno');

/*Insert the StockSerialMovements and update the StockSerialItems  for controlled items*/

		if($_SESSION['Transfer']->TransferItem[0]->Controlled ==1) {
			foreach($_SESSION['Transfer']->TransferItem[0]->SerialItems as $Item) {
			/*We need to add or update the StockSerialItem record and
			The StockSerialMoves as well */

				/*First need to check if the serial items already exists or not in the location from */
				$SQL = "SELECT COUNT(*)
						FROM stockserialitems
						WHERE
						stockid='" . $_SESSION['Transfer']->TransferItem[0]->StockID . "'
						AND loccode='" . $_SESSION['Transfer']->StockLocationFrom . "'
						AND serialno='" . $Item->BundleRef . "'";

				$ErrMsg =  __('The entered item code does not exist');
				$Result = DB_query($SQL, $ErrMsg);
				$SerialItemExistsRow = DB_fetch_row($Result);

				if($SerialItemExistsRow[0]==1) {

					$SQL = "UPDATE stockserialitems
							SET quantity= quantity - '" . $Item->BundleQty . "',
							expirationdate='" . FormatDateForSQL($Item->ExpiryDate) . "'
							WHERE stockid='" . $_SESSION['Transfer']->TransferItem[0]->StockID . "'
							AND loccode='" . $_SESSION['Transfer']->StockLocationFrom . "'
							AND serialno='" . $Item->BundleRef . "'";

					$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The serial stock item record could not be updated because');
					$Result = DB_query($SQL, $ErrMsg, '', true);
				} else {
					/*Need to insert a new serial item record */
					$SQL = "INSERT INTO stockserialitems (stockid,
										loccode,
										serialno,
										expirationdate,
										quantity)
						VALUES ('" . $_SESSION['Transfer']->TransferItem[0]->StockID . "',
						'" . $_SESSION['Transfer']->StockLocationFrom . "',
						'" . $Item->BundleRef . "',
						'" . FormatDateForSQL($Item->ExpiryDate) . "',
						'" . -$Item->BundleQty . "')";

					$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The serial stock item record could not be added because');
					$Result = DB_query($SQL, $ErrMsg, '', true);
				}

				/* now insert the serial stock movement */

				$SQL = "INSERT INTO stockserialmoves (
								stockmoveno,
								stockid,
								serialno,
								moveqty)
						VALUES (
							'" . $StkMoveNo . "',
							'" . $_SESSION['Transfer']->TransferItem[0]->StockID . "',
							'" . $Item->BundleRef . "',
							'" . $Item->BundleQty . "'
							)";

				$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The serial stock movement record could not be inserted because');
				$Result = DB_query($SQL, $ErrMsg, '', true);

			}/* foreach controlled item in the serialitems array */
		} /*end if the transferred item is a controlled item */


		// Need to get the current location quantity will need it later for the stock movement
		$SQL="SELECT locstock.quantity
				FROM locstock
				WHERE locstock.stockid='" . $_SESSION['Transfer']->TransferItem[0]->StockID . "'
				AND loccode= '" . $_SESSION['Transfer']->StockLocationTo . "'";
		$ErrMsg = __('Could not retrieve QOH at the destination because');
		$Result = DB_query($SQL, $ErrMsg, '',true);
		if(DB_num_rows($Result)==1) {
			$LocQtyRow = DB_fetch_row($Result);
			$QtyOnHandPrior = $LocQtyRow[0];
		} else {
			// There must actually be some error this should never happen
			$QtyOnHandPrior = 0;
		}
		// Insert incoming inventory GL transaction if any of the locations has a GL account code:
		if(($_SESSION['Transfer']->StockLocationFromAccount !='' OR $_SESSION['Transfer']->StockLocationToAccount !='') AND
			($_SESSION['Transfer']->StockLocationFromAccount != $_SESSION['Transfer']->StockLocationToAccount)) {
			// Get the account code:
			if($_SESSION['Transfer']->StockLocationToAccount !='') {
				$AccountCode = $_SESSION['Transfer']->StockLocationToAccount;
			} else {
				$StockGLCode = GetStockGLCode($_SESSION['Transfer']->TransferItem[0]->StockID);// Get Category's account codes.
				$AccountCode = $StockGLCode['stockact'];// Select account code for stock.
			}
			// Get the item cost:
			$SQLstandardcost = "SELECT stockmaster.actualcost AS standardcost
								FROM stockmaster
								WHERE stockmaster.stockid ='" . $_SESSION['Transfer']->TransferItem[0]->StockID . "'";
			$ErrMsg = __('The standard cost of the item cannot be retrieved because');
			$ResultStandardCost = DB_query($SQLstandardcost, $ErrMsg);
			$MyRow = DB_fetch_array($ResultStandardCost);
			$StandardCost = $MyRow['standardcost'];// QUESTION: Standard cost for: Assembly (value="A") and Manufactured (value="M") items ?
			// Insert record:
			$SQL = "INSERT INTO gltrans (
					periodno,
					trandate,
					type,
					typeno,
					account,
					narrative,
					amount)
				VALUES ('" .
					$PeriodNo . "','" .
					$SQLTransferDate . "',
					16,'" .
					$TransferNumber . "','" .
					$AccountCode . "','" .
					mb_substr($_SESSION['Transfer']->StockLocationTo.' - '.$_SESSION['Transfer']->TransferItem[0]->StockID.' x '.$_SESSION['Transfer']->TransferItem[0]->Quantity.' @ '. $StandardCost, 0, 200) . "','" .
					$_SESSION['Transfer']->TransferItem[0]->Quantity * $StandardCost . "')";
			$ErrMsg =  __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The incoming inventory GL transacction record could not be inserted because');
			$Result = DB_query($SQL, $ErrMsg, '', true);
		}
		// Insert the stock movement for the stock coming into the to location
		$SQL = "INSERT INTO stockmoves (stockid,
						type,
						transno,
						loccode,
						trandate,
						userid,
						prd,
						reference,
						qty,
						newqoh)
			VALUES ('" . $_SESSION['Transfer']->TransferItem[0]->StockID . "',
					16,
					'" . $TransferNumber . "',
					'" . $_SESSION['Transfer']->StockLocationTo . "',
					'" . $SQLTransferDate . "',
					'" . $_SESSION['UserID'] . "',
					'" . $PeriodNo . "',
					'" . __('From') . " " . $_SESSION['Transfer']->StockLocationFrom . "',
					'" . $_SESSION['Transfer']->TransferItem[0]->Quantity . "',
					'" . round($QtyOnHandPrior + $_SESSION['Transfer']->TransferItem[0]->Quantity,$_SESSION['Transfer']->TransferItem[0]->DecimalPlaces) . "')";

		$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The stock movement record cannot be inserted because');
		$Result = DB_query($SQL, $ErrMsg, '', true);

		/*Get the ID of the StockMove... */
		$StkMoveNo = DB_Last_Insert_ID('stockmoves','stkmoveno');

/*Insert the StockSerialMovements and update the StockSerialItems  for controlled items*/

		if($_SESSION['Transfer']->TransferItem[0]->Controlled ==1) {
			foreach($_SESSION['Transfer']->TransferItem[0]->SerialItems as $Item) {
			/*We need to add or update the StockSerialItem record and
			The StockSerialMoves as well */

				/*First need to check if the serial items already exists or not in the location from */
				$SQL = "SELECT COUNT(*)
						FROM stockserialitems
						WHERE stockid='" . $_SESSION['Transfer']->TransferItem[0]->StockID . "'
						AND loccode='" . $_SESSION['Transfer']->StockLocationTo . "'
						AND serialno='" . $Item->BundleRef . "'";

				$ErrMsg = __('Could not determine if the serial item exists in the transfer to location');
				$Result = DB_query($SQL, $ErrMsg);
				$SerialItemExistsRow = DB_fetch_row($Result);

				if($SerialItemExistsRow[0]==1) {

					$SQL = "UPDATE stockserialitems
							SET quantity= quantity + '" . $Item->BundleQty . "',
								expirationdate='" . FormatDateForSQL($Item->ExpiryDate) . "'
							WHERE stockid='" . $_SESSION['Transfer']->TransferItem[0]->StockID . "'
							AND loccode='" . $_SESSION['Transfer']->StockLocationTo . "'
							AND serialno='" . $Item->BundleRef . "'";

					$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The serial stock item record could not be updated because');
					$Result = DB_query($SQL, $ErrMsg, '', true);
				} else {
					/*Need to insert a new serial item record */
					$SQL = "INSERT INTO stockserialitems (stockid,
														loccode,
														serialno,
														expirationdate,
														quantity,
														qualitytext)
						VALUES ('" . $_SESSION['Transfer']->TransferItem[0]->StockID . "',
								'" . $_SESSION['Transfer']->StockLocationTo . "',
								'" . $Item->BundleRef . "',
								'" . FormatDateForSQL($Item->ExpiryDate) . "',
								'" . $Item->BundleQty . "',
								'')";

					$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The serial stock item record could not be added because');
					$Result = DB_query($SQL, $ErrMsg, '', true);
				}

				/* now insert the serial stock movement */

				$SQL = "INSERT INTO stockserialmoves (stockmoveno,
									stockid,
									serialno,
									moveqty)
							VALUES ('" . $StkMoveNo . "',
								'" . $_SESSION['Transfer']->TransferItem[0]->StockID . "',
								'" . $Item->BundleRef . "',
								'" . $Item->BundleQty . "')";
				$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The serial stock movement record could not be inserted because');
				$Result = DB_query($SQL, $ErrMsg, '', true);

			}/* foreach controlled item in the serialitems array */
		} /*end if the transfer item is a controlled item */

		$SQL = "UPDATE locstock SET quantity = quantity - '" . round($_SESSION['Transfer']->TransferItem[0]->Quantity,$_SESSION['Transfer']->TransferItem[0]->DecimalPlaces) . "'
				WHERE stockid='" . $_SESSION['Transfer']->TransferItem[0]->StockID . "'
				AND loccode='" . $_SESSION['Transfer']->StockLocationFrom . "'";

		$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The location stock record could not be updated because');
		$Result = DB_query($SQL, $ErrMsg, '', true);

		$SQL = "UPDATE locstock
				SET quantity = quantity + '" . round($_SESSION['Transfer']->TransferItem[0]->Quantity,$_SESSION['Transfer']->TransferItem[0]->DecimalPlaces) . "'
				WHERE stockid='" . $_SESSION['Transfer']->TransferItem[0]->StockID . "'
				AND loccode='" . $_SESSION['Transfer']->StockLocationTo . "'";

		$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The location stock record could not be updated because');
		$Result = DB_query($SQL, $ErrMsg, '', true);

		DB_Txn_Commit();

		prnMsg(__('An inventory transfer of').' ' . $_SESSION['Transfer']->TransferItem[0]->StockID . ' - ' . $_SESSION['Transfer']->TransferItem[0]->ItemDescription . ' '. __('has been created from').' ' . $_SESSION['Transfer']->StockLocationFrom . ' '. __('to') . ' ' . $_SESSION['Transfer']->StockLocationTo . ' '.__('for a quantity of').' ' . $_SESSION['Transfer']->TransferItem[0]->Quantity,'success');
		echo '<br /><a href="' . $RootPath . '/PDFStockTransfer.php?TransferNo='.$TransferNumber.'">' . __('Print Transfer Note') . '</a>';
		unset($_SESSION['Transfer']);
		include('includes/footer.php');
		exit();
	}

}

echo '<p class="page_title_text">
		<img src="'.$RootPath.'/css/'.$Theme.'/images/supplier.png" title="' . __('Dispatch') . '" alt="" />' . ' ' . $Title . '
	  </p>';

echo '<form action="'. htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if(!isset($_GET['Description'])) {
	$_GET['Description']='';
}
echo '<fieldset>
		<legend>', __('Stock Transfer'), '</legend>
		<field>
			<label for="StockID">' .  __('Stock Code'). ':</label>';
if(!isset($_POST['StockID'])) {
	$_POST['StockID'] = '';
}
	echo '<input type="text"  title="" name="StockID" size="21" value="' . $_POST['StockID'] . '" maxlength="20" />
		<fieldhelp>'.__('The stock ID should not be blank or contain illegal characters, you can choose left this blank and only keyin').' '.__('Partial Stock Code').' '.__('or').' '.__('Partial Description').' '.__('then push').' '.__('Check Part').'</fieldhelp>
	</field>';

echo '<field>
		<label for="StockText">' .  __('Partial Description'). ':</label>
		<input type="text" name="StockText" title="" size="21" value="' . $_GET['Description'] .'" />
		<fieldhelp>'.__('You can key in part of stock description or left this and ').' '.__('Partial Stock Code').' '.__('blank and click ').' '.__('Check Part').' '.__('to view the whole stock list').'</fieldhelp>
		' . __('Partial Stock Code'). ':';
if(isset($_POST['StockID'])) {
	echo '<input type="text" title="" name="StockCode" size="21" value="' . $_POST['StockID'] .'" maxlength="20" />
		<fieldhelp>'.__('You can key in partial of the stock code or just left this blank to click').' '.__('Check Part').'</fieldhelp>';
} else {
	echo '<input type="text" title="" name="StockCode" size="21" value="" maxlength="20" />
		<fieldhelp>'.__('You can key in partial of the stock code or just left this blank to click').' '.__('Check Part').'</fieldhelp>';
}
echo '<input type="submit" name="CheckCode" value="'.__('Check Part').'" />
	</field>';

if(isset($_SESSION['Transfer']->TransferItem[0]->ItemDescription)
	AND mb_strlen($_SESSION['Transfer']->TransferItem[0]->ItemDescription)>1) {

	echo '<field>
			<td colspan="3"><font color="blue" size="3">' . $_SESSION['Transfer']->TransferItem[0]->ItemDescription . ' ('.__('In Units of').' ' . $_SESSION['Transfer']->TransferItem[0]->PartUnit . ' )</font></td>
		</field>';
}

echo '<field>
		<label for="StockLocationFrom">' . __('From Stock Location').':</label>
		<select name="StockLocationFrom">';

$SQL = "SELECT locations.loccode, locationname FROM locations INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canupd=1";
$ResultStkLocs = DB_query($SQL);
while($MyRow=DB_fetch_array($ResultStkLocs)) {
	if(isset($_SESSION['Transfer']->StockLocationFrom)) {
		if($MyRow['loccode'] == $_SESSION['Transfer']->StockLocationFrom) {
			 echo '<option selected="selected" value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
		} else {
			 echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
		}
	} elseif($MyRow['loccode']==$_SESSION['UserStockLocation']) {
		echo '<option selected="selected" value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
		if(isset($_SESSION['Transfer']))
		 $_SESSION['Transfer']->StockLocationFrom=$MyRow['loccode'];
	} else {
		 echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
	}
}

echo '</select>
	</field>';

echo '<field>
		<label for="StockLocationTo">' .  __('To Stock Location').': </label>
		<select name="StockLocationTo"> ';

DB_data_seek($ResultStkLocs,0);

while($MyRow=DB_fetch_array($ResultStkLocs)) {
	if(isset($_SESSION['Transfer']) AND isset($_SESSION['Transfer']->StockLocationTo)) {
		if($MyRow['loccode'] == $_SESSION['Transfer']->StockLocationTo) {
			 echo '<option selected="selected" value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
		} else {
			 echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
		}
	} elseif($MyRow['loccode']==$_SESSION['UserStockLocation'] AND isset($_SESSION['Transfer'])) {
		echo '<option selected="selected" value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
		if(isset($_SESSION['Transfer']))
		 $_SESSION['Transfer']->StockLocationTo=$MyRow['loccode'];
	} else {
		 echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
	}
}

echo '</select>
	</field>';

echo '<field>
		<label for="Quantity">' . __('Transfer Quantity').':</label>';

if(isset($_SESSION['Transfer']->TransferItem[0]->Controlled)
	AND $_SESSION['Transfer']->TransferItem[0]->Controlled==1) {

	echo '<input type="hidden" name="Quantity" value="' . locale_number_format($_SESSION['Transfer']->TransferItem[0]->Quantity) . '" />
		<a href="' . $RootPath .'/StockTransferControlled.php?StockLocationFrom='.$_SESSION['Transfer']->StockLocationFrom.'">
		' . $_SESSION['Transfer']->TransferItem[0]->Quantity . '</a>
	</field>';
} elseif(isset($_SESSION['Transfer']->TransferItem[0]->Controlled)) {
	echo '<input type="text" class="number" name="Quantity" title="" size="12" maxlength="12" value="' . locale_number_format($_SESSION['Transfer']->TransferItem[0]->Quantity) . '" />
		<fieldhelp'.__('The transfer quantity cannot be zero').'</fieldhelp>
	</field>';
} else {
	echo '<input type="text" class="number"  title="" name="Quantity" size="12" maxlength="12" value="0" />
		<fieldhelp>'.__('The transfer quantity cannot be zer0').'</fieldhelp>
		</field>';
}

echo '</fieldset>
	<div class="centre">
		<input type="submit" name="EnterTransfer" value="' . __('Enter Stock Transfer') . '" />';

if(empty($_SESSION['Transfer']->TransferItem[0]->StockID) and isset($_POST['StockID'])) {
	$StockID=$_POST['StockID'];
} elseif(isset($_SESSION['Transfer']->TransferItem[0]->StockID)) {
	$StockID=$_SESSION['Transfer']->TransferItem[0]->StockID;
} else {
	$StockID='';
}
if(isset($_SESSION['Transfer'])) {
	echo '<br />
		<a href="'.$RootPath.'/StockStatus.php?StockID=' . $StockID . '">' . __('Show Stock Status') . '</a>';
	echo '<br />
		<a href="'.$RootPath.'/StockMovements.php?StockID=' . $StockID . '">' . __('Show Movements') . '</a>';
	echo '<br />
		<a href="'.$RootPath.'/StockUsage.php?StockID=' . $StockID . '&amp;StockLocation=' . $_SESSION['Transfer']->StockLocationFrom . '">' . __('Show Stock Usage') . '</a>';
	echo '<br />
		<a href="'.$RootPath.'/SelectSalesOrder.php?SelectedStockItem=' . $StockID . '&amp;StockLocation=' . $_SESSION['Transfer']->StockLocationFrom . '">' . __('Search Outstanding Sales Orders') . '</a>';
	echo '<br />
		<a href="'.$RootPath.'/SelectCompletedOrder.php?SelectedStockItem=' . $StockID . '">' . __('Search Completed Sales Orders') . '</a>';
}
echo '</div>
	</form>';
include('includes/footer.php');

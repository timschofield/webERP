<?php

include('includes/session.php');
$Title = _('SPG Last 10 Shop Tali Exchanges');
include('includes/header.php');

include('includes/SQL_CommonFunctions.inc');

include('includes/KLDefines.php');
include('includes/KLEmails.php');

/*

CREATE TABLE IF NOT EXISTS `klfreeexchanges` (
  `counterexchange` int(11) NOT NULL AUTO_INCREMENT,
  `itemfrom` varchar(20) NOT NULL,
  `itemto` varchar(20) NOT NULL,
  `date` datetime NOT NULL,
  `userid` varchar(20) NOT NULL,
  `invoicenumber` varchar(50) NOT NULL,
  PRIMARY KEY (`counterexchange`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

INSERT INTO  `scripts` (
`script` ,
`pagesecurity` ,
`description`
)
VALUES (
'KLShopFreeExchanges.php',  '1500',  'Tali Exchanges control script'
);

*/

if (isset($_GET['SelectedExchange'])){
	$SelectedExchange =mb_strtoupper($_GET['SelectedExchange']);
} elseif(isset($_POST['SelectedExchange'])){
	$SelectedExchange =mb_strtoupper($_POST['SelectedExchange']);
}

if (isset($Errors)) {
	unset($Errors);
}

$Errors = array();

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . _('My Last 10 Shop Tali Exchanges') . '" alt="" />' . ' ' . $Title.'</p>';

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */
	$i=1;

	if ($_POST['ItemFrom'] == '') {
		$InputError = 1;
		$Errors[$i] = 'ItemFromEmpty';
		$i++;
		prnMsg(_('The Item FROM must be one valid code'),'error');
	}elseif ($_POST['ItemTo'] == '') {
		$InputError = 1;
		$Errors[$i] = 'ItemToEmpty';
		$i++;
		prnMsg(_('The Item TO must be one valid code'),'error');
	}elseif ($_POST['InvoiceNumber'] == '') {
		$InputError = 1;
		$Errors[$i] = 'InvoiceNumber';
		$i++;
		prnMsg(_('Yellow Invoice Number can not be empty'),'error');
	}elseif ($_POST['ItemFrom'] == $_POST['ItemTo']) {
		$InputError = 1;
		$Errors[$i] = 'SameItem';
		$i++;
		prnMsg(_('Item FROM and TO can not be the same'),'error');
	}

	if (!isset($_POST['ItemFrom'])){
	  $_POST['ItemFrom']='';
	}
	if (!isset($_POST['ItemTo'])){
	  $_POST['ItemTo']='';
	}
	if (!isset($_POST['InvoiceNumber'])){
	  $_POST['InvoiceNumber']='';
	}

	if ($InputError !=1) {
		DB_Txn_Begin();
		$Now = Date('Y-m-d H-i-s');
		
		$sql = "INSERT INTO klfreeexchanges
						(itemfrom,
						itemto,
						date,
						userid,
						invoicenumber)
				VALUES ('" . $_POST['ItemFrom'] . "',
					'" . $_POST['ItemTo'] . "',
					'" . $Now . "',
					'" . $_SESSION['UserID'] . "',
					'" . $_POST['InvoiceNumber'] . "')";
		
		$msg = _('KL Tali Exchange') . ' ' . $_POST['ItemFrom'] . ' --> ' . $_POST['ItemTo'] . ' ' . _('has been created');
		$ErrMsg = _('The insert of the KL tali exchange failed because');
		$DbgMsg = _('The SQL that was used and failed was');
		$result = DB_query($sql,$ErrMsg, $DbgMsg);
		prnMsg($msg , 'success');

		$AdjustmentNumber = GetNextTransNo(17);
		$PeriodNo = GetPeriod (Date($_SESSION['DefaultDateFormat']), $db);
		$SQLAdjustmentDate = FormatDateForSQL(Date($_SESSION['DefaultDateFormat']));

		// PROCESS THE ITEM FROM (RETURNED)

		// Need to get the current location quantity will need it later for the stock movement
		$SQL="SELECT locstock.quantity
			FROM locstock
			WHERE locstock.stockid='" . $_POST['ItemFrom'] . "'
			AND loccode= '" . $_SESSION['UserStockLocation'] . "'";
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
										newqoh)
									VALUES (
										'" . $_POST['ItemFrom'] . "',
										17,
										'" . $AdjustmentNumber . "',
										'" . $_SESSION['UserStockLocation'] . "',
										'" . $SQLAdjustmentDate . "',
										'" . $_SESSION['UserID'] . "',
										'" . $PeriodNo . "',
										'" . "KL Tali Exchange at Invoice# " . $_POST['InvoiceNumber'] . "',
										'" . 1 . "',
										'" . ($QtyOnHandPrior + 1) . "'
									)";


		$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stock movement record cannot be inserted because');
		$DbgMsg =  _('The following SQL to insert the stock movement record was used');
		$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

		$SQL = "UPDATE locstock SET quantity = quantity + 1
				WHERE stockid='" . $_POST['ItemFrom'] . "'
				AND loccode='" . $_SESSION['UserStockLocation'] . "'";

		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' ._('The location stock record could not be updated because');
		$DbgMsg = _('The following SQL to update the stock record was used');

		$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

		// PROCESS THE ITEM TO (GIVEN TO CUSTOMER)

		// Need to get the current location quantity will need it later for the stock movement
		$SQL="SELECT locstock.quantity
			FROM locstock
			WHERE locstock.stockid='" . $_POST['ItemTo'] . "'
			AND loccode= '" . $_SESSION['UserStockLocation'] . "'";
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
										newqoh)
									VALUES (
										'" . $_POST['ItemTo'] . "',
										17,
										'" . $AdjustmentNumber . "',
										'" . $_SESSION['UserStockLocation'] . "',
										'" . $SQLAdjustmentDate . "',
										'" . $_SESSION['UserID'] . "',
										'" . $PeriodNo . "',
										'" . "KL Tali Exchange at Invoice# " . $_POST['InvoiceNumber'] . "',
										'" . -1 . "',
										'" . ($QtyOnHandPrior - 1) . "'
									)";


		$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stock movement record cannot be inserted because');
		$DbgMsg =  _('The following SQL to insert the stock movement record was used');
		$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

		$SQL = "UPDATE locstock SET quantity = quantity - 1
				WHERE stockid='" . $_POST['ItemTo'] . "'
				AND loccode='" . $_SESSION['UserStockLocation'] . "'";

		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' ._('The location stock record could not be updated because');
		$DbgMsg = _('The following SQL to update the stock record was used');

		$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

		
		KLSendEmail("TaliExchange", "Silent", $_POST['ItemFrom'], $_POST['ItemTo'], $_SESSION['UserID'], $_SESSION['UserStockLocation'], $_POST['InvoiceNumber']);

		DB_Txn_Commit();

		unset($SelectedExchange);
		unset($_POST['ItemFrom']);
		unset($_POST['ItemTo']);
		unset($_POST['InvoiceNumber']);
	}
}

if (!isset($SelectedExchange)) {

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedExchange will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of Sales-persons will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$sql = "SELECT counterexchange,
				date,
				itemfrom,
				itemto,
				invoicenumber,
				userid
			FROM klfreeexchanges
			WHERE userid = '" . $_SESSION['UserID'] . "'
			ORDER BY counterexchange DESC
			LIMIT 0, 10";
			
	$result = DB_query($sql);

	echo '<table class="selection">';
	echo '<tr>
			<th>' . _('Exchange #') . '</th>
			<th>' . _('Date') . '</th>
			<th>' . _('From') . '</th>
			<th>' . _('To') . '</th>
			<th>' . _('Yellow#') . '</th>
		</tr>';
	$k=0;
	while ($myrow=DB_fetch_array($result)) {

	if ($k==1){
		echo '<tr class="EvenTableRows">';
		$k=0;
	} else {
		echo '<tr class="OddTableRows">';
		$k++;
	}

	printf('<td class="number">%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			</tr>',
			$myrow['counterexchange'],
			ConvertSQLDateTime($myrow['date']),
			$myrow['itemfrom'],
			$myrow['itemto'],
			$myrow['invoicenumber']
			);
	} //END WHILE LIST LOOP
	echo '</table><br />';
} //end of ifs and buts!

if (isset($SelectedExchange)) {
	echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('Show my last 10 Tali exchanges') . '</a></div>';
}

if (! isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (!isset($_POST['ItemFrom'])){
	  $_POST['ItemFrom']='';
	}
	if (!isset($_POST['ItemTo'])){
	  $_POST['ItemTo']='';
	}
	if (!isset($_POST['InvoiceNumber'])){
	  $_POST['InvoiceNumber']='';
	}

	
	echo '<tr>
			<td>' . _('From') . ':</td>
			<td><select name="ItemFrom">';
	$sql="SELECT stockid 
		FROM stockmaster
		WHERE discontinued = 0
			AND stockid LIKE 'TM-%'";
	$TaliResult= DB_query($sql);
	echo '<option selected="selected" value="">' . _('Select Tali Model')  . '</option>';

	While ($myrow = DB_fetch_array($TaliResult)){
		echo '<option value="' . $myrow['stockid'] . '">' . $myrow['stockid']  . '</option>';
	}
	echo '</select></td></tr>';

	echo '<tr>
			<td>' . _('To') . ':</td>
			<td><select name="ItemTo">';
	$sql="SELECT stockid 
		FROM stockmaster
		WHERE discontinued = 0
			AND stockid LIKE 'TM-%'";
	$TaliResult= DB_query($sql);
	echo '<option selected="selected" value="">' . _('Select Tali Model')  . '</option>';
	While ($myrow = DB_fetch_array($TaliResult)){
		echo '<option value="' . $myrow['stockid'] . '">' . $myrow['stockid']  . '</option>';
	}
	echo '</select></td></tr>';

	echo '<tr>
			<td>' . _('Yellow #') . ':</td>
			<td><input type="text" name="InvoiceNumber" size="20" maxlength="20" value="' . $_POST['InvoiceNumber'] . '" /></td>
		</tr>';

	echo '</select></td>
		</tr>
		</table>
		<br />
		<div class="centre">
			<input type="submit" name="submit" value="' . _('Enter Shop Tali Exchange') . '" />
		</div>
        </div>
		</form>';

} //end if record deleted no point displaying form to add record

include('includes/footer.php');
?>
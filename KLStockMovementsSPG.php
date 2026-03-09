<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Stock Movements for SPG');
$ViewTopic= "Inventory";
$BookMark = "InventoryMovement";
include(__DIR__ . '/includes/header.php');

include(__DIR__ . '/includes/KLGeneralFunctions.php');
include(__DIR__ . '/includes/KLDefines.php');
include(__DIR__ . '/includes/KLUIGeneralFunctions.php');


if (isset($_GET['StockID'])){
	$StockID = trim(mb_strtoupper($_GET['StockID']));
} elseif (isset($_POST['StockID'])){
	$StockID = trim(mb_strtoupper($_POST['StockID']));
} else {
	$StockID = '';
}

if (isset($_GET['Location'])){
	$Location = trim(mb_strtoupper($_GET['Location']));
} else {
	$Location = $_SESSION['UserStockLocation'];
}

$Result = DB_query("SELECT description,
						   units,
						   mbflag,
						   decimalplaces
					FROM stockmaster
					WHERE stockid='".$StockID."'",
					__('Could not retrieve the requested item'),
					__('The SQL used to retrieve the items was'));
$MyRow = DB_fetch_array($Result);
$DecimalPlaces = $MyRow['decimalplaces'];
$LocationName = GetLocationNameFromCode($_SESSION['UserStockLocation']);

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
echo '<div class="centre"><input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo __('Stock Code') . ':<input type="text" data-type="no-illegal-chars" title ="'.__('Input the stock code to inquire upon. Only alpha-numeric characters are allowed in stock codes with no spaces punctuation or special characters. Underscore or dashes are allowed.').'" placeholder="'.__('Alpha-numeric only').'" required="required" name="StockID" size="21" value="' . $StockID . '" maxlength="20" />';

echo ' <input type="submit" name="ShowStatus" value="' . __('Show Item Movements in ') . $LocationName . '" />';
if ($StockID != ''){
	$Result = DB_query("SELECT description, units FROM stockmaster WHERE stockid='".$StockID."'");
	$MyRow = DB_fetch_row($Result);

	$Today  = FormatDateForSQL(Date($_SESSION['DefaultDateFormat']));
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-STOCK_MOVEMENT_DAYS_FOR_SPG));

	$SQL = "SELECT stockmoves.stockid,
					systypes.typename,
					stockmoves.type,
					stockmoves.transno,
					stockmoves.trandate,
					stockmoves.userid,
					stockmoves.debtorno,
					stockmoves.branchcode,
					stockmoves.qty,
					stockmoves.reference,
					stockmoves.price,
					stockmoves.discountpercent,
					stockmoves.newqoh,
					stockmaster.decimalplaces
			FROM stockmoves
			INNER JOIN systypes ON stockmoves.type=systypes.typeid
			INNER JOIN stockmaster ON stockmoves.stockid=stockmaster.stockid
			WHERE  stockmoves.loccode='" . $Location . "'
			AND stockmoves.trandate >= '". $StartDate . "'
			AND stockmoves.stockid = '" . $StockID . "'
			AND stockmoves.trandate <= CURRENT_DATE
			AND hidemovt=0
			ORDER BY stkmoveno DESC";

	$ErrMsg = __('The stock movements for the selected criteria could not be retrieved because') . ' - ';
	$MovtsResult = DB_query($SQL, $ErrMsg);

	$TableTitleText = __('Movements of ') . $StockID . " at " . $LocationName . " for the last " . STOCK_MOVEMENT_DAYS_FOR_SPG . " days";
	ShowTableTitle($TableTitleText);

	echo '<div>';
	echo '<table class="selection">';
	$Tableheader = '<thead>
						<tr>
							<th>' . __('Date') . '</th>
							<th>' . __('User') . '</th>
							<th>' . __('Type') . '</th>
							<th>' . __('Number') . '</th>
							<th>' . __('Reference') . '</th>
							<th>' . __('Qty Movement') . '</th>
							<th>' . __('Stock after movement') . '</th>
						</tr>
					</thead>
					<tbody>';

	echo $Tableheader;

	$j = 1;

	while ($MyRow=DB_fetch_array($MovtsResult)) {

		$DisplayTranDate = ConvertSQLDate($MyRow['trandate']);

		if ($MyRow['type']==10){ /*its a sales invoice allow link to show invoice it was sold on*/

			$InvoiceLink = '<a href="' . $RootPath . '/PrintCustTrans.php?FromTransNo=' . $MyRow['transno'] . '&amp;InvOrCredit=Invoice">' . $MyRow['typename'] . '</a>';

			echo '<tr class="striped_row">
					<td>' . $DisplayTranDate . '</td>
					<td>' . $MyRow['userid'] . '</td>
					<td>' . $InvoiceLink . '</td>
					<td>' . $MyRow['transno'] . '</td>
					<td>' . '' . '</td>
					<td class="number">' . locale_number_format($MyRow['qty'],$MyRow['decimalplaces']) . '</td>
					<td class="number">' . locale_number_format($MyRow['newqoh'],$MyRow['decimalplaces']) . '</td>
					</tr>';

		} elseif ($MyRow['type']==11){

			echo '<tr class="striped_row">
					<td>' . $DisplayTranDate . '</td>
					<td>' . $MyRow['userid'] . '</td>
					<td><a target="_blank" href="' . $RootPath . '/PrintCustTrans.php?FromTransNo=' . $MyRow['transno'] . '&amp;InvOrCredit=Credit">' . $MyRow['typename'] . '</a></td>
					<td>' . $MyRow['transno'] . '</td>
					<td>' . '' . '</td>
					<td class="number">' . locale_number_format($MyRow['qty'],$MyRow['decimalplaces']) . '</td>
					<td class="number">' . locale_number_format($MyRow['newqoh'],$MyRow['decimalplaces']) . '</td>
					</tr>';
		} else {

			echo '<tr class="striped_row">
					<td>' . $DisplayTranDate . '</td>
					<td>' . $MyRow['userid'] . '</td>
					<td>' . $MyRow['typename'] . '</td>
					<td>' . $MyRow['transno'] . '</td>
					<td>' . $MyRow['reference'] . '</td>
					<td class="number">' . locale_number_format($MyRow['qty'],$MyRow['decimalplaces']) . '</td>
					<td class="number">' . locale_number_format($MyRow['newqoh'],$MyRow['decimalplaces']) . '</td>
					</tr>';
		}
	//end of page full new headings if
	}
	//end of while loop

	echo '</tbody>
			</table>
			</div>
			</form>';
}
include(__DIR__ . '/includes/footer.php');


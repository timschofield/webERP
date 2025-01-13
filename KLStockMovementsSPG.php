<?php

include('includes/session.php');
$Title = _('Stock Movements for SPG');
/* webERP manual links before header.php */
$ViewTopic= "Inventory";
$BookMark = "InventoryMovement";
include('includes/header.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLDefines.php');
include('includes/KLUIFunctions.php');


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
					_('Could not retrieve the requested item'),
					_('The SQL used to retrieve the items was'));
$MyRow = DB_fetch_array($Result);
$DecimalPlaces = $MyRow['decimalplaces'];
$LocationName = GetLocationNameFromCode($_SESSION['UserStockLocation']);

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
echo '<div class="centre"><input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo _('Stock Code') . ':<input type="text" data-type="no-illegal-chars" title ="'._('Input the stock code to inquire upon. Only alpha-numeric characters are allowed in stock codes with no spaces punctuation or special characters. Underscore or dashes are allowed.').'" placeholder="'._('Alpha-numeric only').'" required="required" name="StockID" size="21" value="' . $StockID . '" maxlength="20" />';

echo ' <input type="submit" name="ShowStatus" value="' . _('Show Item Movements in ') . $LocationName . '" />';
echo '<br /><br />';

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

	$ErrMsg = _('The stock movements for the selected criteria could not be retrieved because') . ' - ';
	$DbgMsg = _('The SQL that failed was') . ' ';

	$MovtsResult = DB_query($SQL, $ErrMsg, $DbgMsg);

	$TableTitleText = _('Movements of ') . $StockID . " at " . $LocationName . " for the last " . STOCK_MOVEMENT_DAYS_FOR_SPG . " days";
	ShowTableTitle($TableTitleText);

	echo '<div>';
	echo '<table class="selection">';
	$Tableheader = '<thead>
						<tr>
							<th>' . _('Date') . '</th>
							<th>' . _('User') . '</th>
							<th>' . _('Type') . '</th>
							<th>' . _('Number') . '</th>
							<th>' . _('Reference') . '</th>
							<th>' . _('Qty Movement') . '</th>
							<th>' . _('Stock after movement') . '</th>
						</tr>
					</thead>
					<tbody>';

	echo $Tableheader;

	$j = 1;

	while ($MyRow=DB_fetch_array($MovtsResult)) {

		$DisplayTranDate = ConvertSQLDate($MyRow['trandate']);

		if ($MyRow['type']==10){ /*its a sales invoice allow link to show invoice it was sold on*/

			$InvoiceLink = '<a href="' . $RootPath . '/PrintCustTrans.php?FromTransNo=' . $MyRow['transno'] . '&amp;InvOrCredit=Invoice">' . $MyRow['typename'] . '</a>';

			printf('<tr class="striped_row">
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>',
					$DisplayTranDate,
					$MyRow['userid'],
					$InvoiceLink,
					$MyRow['transno'],
					'',
					locale_number_format($MyRow['qty'],$MyRow['decimalplaces']),
					locale_number_format($MyRow['newqoh'],$MyRow['decimalplaces']));

		} elseif ($MyRow['type']==11){

			printf('<tr class="striped_row">
					<td>%s</td>
					<td>%s</td>
					<td><a target="_blank" href="%s/PrintCustTrans.php?FromTransNo=%s&amp;InvOrCredit=Credit">%s</a></td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>',
					$DisplayTranDate,
					$MyRow['userid'],
					$RootPath,
					$MyRow['transno'],
					$MyRow['typename'],
					$MyRow['transno'],
					'',
					locale_number_format($MyRow['qty'],$MyRow['decimalplaces']),
					locale_number_format($MyRow['newqoh'],$MyRow['decimalplaces']));
		} else {

			printf('<tr class="striped_row">
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>',
					$DisplayTranDate,
					$MyRow['userid'],
					$MyRow['typename'],
					$MyRow['transno'],
					$MyRow['reference'],
					locale_number_format($MyRow['qty'],$MyRow['decimalplaces']),
					locale_number_format($MyRow['newqoh'],$MyRow['decimalplaces']));
		}
	//end of page full new headings if
	}
	//end of while loop

	echo '</tbody>
			</table>
			</div>
			</form>';
}
include('includes/footer.php');

?>

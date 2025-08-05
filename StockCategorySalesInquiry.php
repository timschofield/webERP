<?php

include('includes/session.php');
if (isset($_POST['FromDate'])){$_POST['FromDate'] = ConvertSQLDate($_POST['FromDate']);}
if (isset($_POST['ToDate'])){$_POST['ToDate'] = ConvertSQLDate($_POST['ToDate']);}
$Title = _('Sales By Category By Item Inquiry');
$ViewTopic = 'Sales';
$BookMark = '';
include('includes/header.php');

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/transactions.png" title="' . _('Sales Report') . '" alt="" />' . ' ' . _('Sales By Category By Item Inquiry') . '</p>';
echo '<div class="page_help_text">' . _('Select the parameters for the inquiry') . '</div>';

if (!isset($_POST['DateRange'])){
	/* then assume report is for This Month - maybe wrong to do this but hey better than reporting an error?*/
	$_POST['DateRange']='ThisMonth';
}

echo '<form id="form1" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
// stock category selection
	$SQL="SELECT categoryid,
					categorydescription
			FROM stockcategory
			ORDER BY categorydescription";
	$Result1 = DB_query($SQL);

echo '<fieldset>
		<legend>', _('Report Criteria'), '</legend>
		<field>
			<label for="StockCat">' . _('In Stock Category') . ':</label>
			<select name="StockCat">';
if (!isset($_POST['StockCat'])){
	$_POST['StockCat']='All';
}
if ($_POST['StockCat']=='All'){
	echo '<option selected="selected" value="All">' . _('All') . '</option>';
} else {
	echo '<option value="All">' . _('All') . '</option>';
}
while ($MyRow1 = DB_fetch_array($Result1)) {
	if ($MyRow1['categoryid']==$_POST['StockCat']){
		echo '<option selected="selected" value="' . $MyRow1['categoryid'] . '">' . $MyRow1['categorydescription'] . '</option>';
	} else {
		echo '<option value="' . $MyRow1['categoryid'] . '">' . $MyRow1['categorydescription'] . '</option>';
	}
}
echo '</select>
	</field>';

if (!isset($_POST['FromDate'])){
	unset($_POST['ShowSales']);
	$_POST['FromDate'] = Date($_SESSION['DefaultDateFormat'],mktime(1,1,1,Date('m')-12,Date('d')+1,Date('Y')));
	$_POST['ToDate'] = Date($_SESSION['DefaultDateFormat']);
}
echo '<field>
		<label for="FromDate">' . _('Date From') . ':</label>
		<input type="date" name="FromDate" maxlength="10" size="11" value="' . FormatDateForSQL($_POST['FromDate']) . '" />
	</field>';

echo '<field>
		<label for="ToDate">' . _('Date To') . ':</label>
		<input type="date" name="ToDate" maxlength="10" size="11" value="' . FormatDateForSQL($_POST['ToDate']) . '" />
	</field>';

echo '</fieldset>';

echo '<div class="centre">
		<input tabindex="4" type="submit" name="ShowSales" value="' . _('Show Sales') . '" />
	</div>
</form>';

if (isset($_POST['ShowSales'])){
	$InputError=0; //assume no input errors now test for errors
	if (!Is_Date($_POST['FromDate'])){
		$InputError = 1;
		prnMsg(_('The date entered for the from date is not in the appropriate format. Dates must be entered in the format') . ' ' . $_SESSION['DefaultDateFormat'], 'error');
	}
	if (!Is_Date($_POST['ToDate'])){
		$InputError = 1;
		prnMsg(_('The date entered for the to date is not in the appropriate format. Dates must be entered in the format') . ' ' . $_SESSION['DefaultDateFormat'], 'error');
	}
	if (Date1GreaterThanDate2($_POST['FromDate'],$_POST['ToDate'])){
		$InputError = 1;
		prnMsg(_('The from date is expected to be a date prior to the to date. Please review the selected date range'),'error');
	}
	$FromDate = FormatDateForSQL($_POST['FromDate']);
	$ToDate = FormatDateForSQL($_POST['ToDate']);

	$SQL = "SELECT stockmaster.categoryid,
					stockcategory.categorydescription,
					stockmaster.stockid,
					stockmaster.description,
					SUM(price*(1-discountpercent)* -qty) as salesvalue,
					SUM(-qty) as quantitysold,
					SUM(standardcost * -qty) as cogs
			FROM stockmoves INNER JOIN stockmaster
			ON stockmoves.stockid=stockmaster.stockid
			INNER JOIN stockcategory
			ON stockmaster.categoryid=stockcategory.categoryid
			WHERE (stockmoves.type=10 OR stockmoves.type=11)
			AND show_on_inv_crds =1
			AND trandate>='" . $FromDate . "'
			AND trandate<='" . $ToDate . "'
			GROUP BY stockmaster.categoryid,
					stockcategory.categorydescription,
					stockmaster.stockid,
					stockmaster.description
			ORDER BY stockmaster.categoryid,
					salesvalue DESC";

	$ErrMsg = _('The sales data could not be retrieved because') . ' - ' . DB_error_msg();
	$SalesResult = DB_query($SQL,$ErrMsg);

	echo '<table cellpadding="2" class="selection">';

	echo'<tr>
			<th>' . _('Item Code') . '</th>
			<th>' . _('Item Description') . '</th>
			<th>' . _('Qty Sold') . '</td>
			<th>' . _('Sales Revenue') . '</th>
			<th>' . _('COGS') . '</th>
			<th>' . _('Gross Margin') . '</th>
			<th>' . _('Avg Unit') . '<br/>' . _('Sale Price') . '</th>
			<th>' . _('Avg Unit') . '<br/>' . _('Cost') . '</th>
			<th>' . _('Margin %') . '</th>
		</tr>';

	$CumulativeTotalSales = 0;
	$CumulativeTotalQty = 0;
	$CumulativeTotalCOGS = 0;
	$CategorySales = 0;
	$CategoryQty = 0;
	$CategoryCOGS = 0;
	$CategoryID ='';

	while ($SalesRow=DB_fetch_array($SalesResult)) {
		if ($CategoryID != $SalesRow['categoryid']) {
			if ($CategoryID !='') {
				//print out the previous category totals
				echo '<tr>
					<td colspan="2" class="number">' . _('Category Total') . '</td>
					<td class="number">' . locale_number_format($CategoryQty,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					<td class="number">' . locale_number_format($CategorySales,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					<td class="number">' . locale_number_format($CategoryCOGS,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					<td class="number">' . locale_number_format($CategorySales - $CategoryCOGS,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					<td colspan="2"></td>';
				if ($CumulativeTotalSales !=0) {
					echo '<td class="number">' . locale_number_format(($CategorySales-$CategoryCOGS)*100/$CategorySales,$_SESSION['CompanyRecord']['decimalplaces']) . '%</td>';
				} else {
					echo '<td>' . _('N/A') . '</td>';
				}
				echo '</tr>';

				//reset the totals
				$CategorySales = 0;
				$CategoryQty = 0;
				$CategoryCOGS = 0;

			}
			echo '<tr>
					<th colspan="9">' . _('Stock Category') . ': ' . $SalesRow['categoryid'] . ' - ' . $SalesRow['categorydescription'] . '</th>
				</tr>';
			$CategoryID = $SalesRow['categoryid'];
		}

		echo '<tr class="striped_row">
				<td>' . $SalesRow['stockid'] . '</td>
				<td>' . $SalesRow['description'] . '</td>
				<td class="number">' . locale_number_format($SalesRow['quantitysold'],$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format($SalesRow['salesvalue'],$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format($SalesRow['cogs'],$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format($SalesRow['salesvalue']-$SalesRow['cogs'],$_SESSION['CompanyRecord']['decimalplaces']) . '</td>';
		if ($SalesRow['quantitysold']!=0) {
			echo '<td class="number">' . locale_number_format(($SalesRow['salesvalue']/$SalesRow['quantitysold']),$_SESSION['CompanyRecord']['decimalplaces']) . '</td>';
			echo '<td class="number">' . locale_number_format(($SalesRow['cogs']/$SalesRow['quantitysold']),$_SESSION['CompanyRecord']['decimalplaces']) . '</td>';
		} else {
			echo '<td>' . _('N/A') . '</td>
				<td>' . _('N/A') . '</td>';
		}
		if ($SalesRow['salesvalue']!=0) {
			echo '<td class="number">' . locale_number_format((($SalesRow['salesvalue']-$SalesRow['cogs'])*100/$SalesRow['salesvalue']),$_SESSION['CompanyRecord']['decimalplaces']) . '%</td>';
		} else {
			echo '<td>' . _('N/A') . '</td>';
		}
		echo '</tr>';

		$CumulativeTotalSales += $SalesRow['salesvalue'];
		$CumulativeTotalCOGS += $SalesRow['cogs'];
		$CumulativeTotalQty += $SalesRow['quantitysold'];
		$CategorySales += $SalesRow['salesvalue'];
		$CategoryQty += $SalesRow['quantitysold'];
		$CategoryCOGS += $SalesRow['cogs'];

	} //loop around category sales for the period
//print out the previous category totals
	echo '<tr>
		<td colspan="2" class="number">' . _('Category Total') . '</td>
		<td class="number">' . locale_number_format($CategoryQty,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
		<td class="number">' . locale_number_format($CategorySales,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
		<td class="number">' . locale_number_format($CategoryCOGS,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
		<td class="number">' . locale_number_format($CategorySales - $CategoryCOGS,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
		<td colspan="2"></td>';
	if ($CumulativeTotalSales !=0) {
		echo '<td class="number">' . locale_number_format(($CategorySales-$CategoryCOGS)*100/$CategorySales,$_SESSION['CompanyRecord']['decimalplaces']) . '%</td>';
	} else {
		echo '<td>' . _('N/A') . '</td>';
	}
	echo '</tr>
		<tr>
		<th colspan="2" class="number">' . _('GRAND Total') . '</th>
		<th class="number">' . locale_number_format($CumulativeTotalQty,$_SESSION['CompanyRecord']['decimalplaces']) . '</th>
		<th class="number">' . locale_number_format($CumulativeTotalSales,$_SESSION['CompanyRecord']['decimalplaces']) . '</th>
		<th class="number">' . locale_number_format($CumulativeTotalCOGS,$_SESSION['CompanyRecord']['decimalplaces']) . '</th>
		<th class="number">' . locale_number_format($CumulativeTotalSales - $CumulativeTotalCOGS,$_SESSION['CompanyRecord']['decimalplaces']) . '</th>
		<th colspan="2"></td>';
	if ($CumulativeTotalSales !=0) {
		echo '<th class="number">' . locale_number_format(($CumulativeTotalSales-$CumulativeTotalCOGS)*100/$CumulativeTotalSales,$_SESSION['CompanyRecord']['decimalplaces']) . '%</th>';
	} else {
		echo '<th>' . _('N/A') . '</th>';
	}
	echo '</tr>
		</table>';

} //end of if user hit show sales
include('includes/footer.php');

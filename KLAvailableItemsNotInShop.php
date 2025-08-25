<?php

include('includes/session.php');

$Title = __('Items with stock available not in shop');
include('includes/header.php');

include('includes/UIGeneralFunctions.php');
include('includes/KLUIGeneralFunctions.php');
include('includes/KLDefines.php');

//check if input already
if (!(isset($_POST['Search']))) {
			
	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . __('Items with Stock Available but not in a location') . '" alt="" />' . ' ' . __('Items with Stock Available but not in a location') . '
		</p>';
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<fieldset>';
	echo FieldToSelectMultipleStockCategories("Categories", $_POST['Categories'], 'Item Categories', 'Select the categories of items', '', 1, true, false);
	echo FieldToSelectOneLocation("FromLoc", $_POST['FromLoc'], __('Available at'), '', 'CANVIEW', 2, true, false);
	echo FieldToSelectOneLocation("Shop", $_POST['Shop'], __('But NOT available at'), '', 'BALISHOPS', 3, true, false);
    echo '</fieldset>';
	
	echo OneButtonCenteredForm("Search", __('Search'), 4, false, false);
	echo '</form>';

} else {
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-60));
	$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.categoryid,
					(	(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.stockid = stockmaster.stockid
						AND locstock.loccode = '" . $_POST['FromLoc'] . "') 
						-(SELECT SUM(pendingqty) 
						FROM loctransfers
						WHERE loctransfers.stockid = stockmaster.stockid
							AND shiploc='" . $_POST['FromLoc'] ."')
					)AS QOHFrom
			FROM stockmaster, stockcategory, locstock
			WHERE stockmaster.categoryid = stockcategory.categoryid
				AND stockmaster.stockid = locstock.stockid
				AND stockcategory.stocktype = 'F'
				AND stockmaster.categoryid IN ('". implode("','",$_POST['Categories'])."')
				AND stockmaster.discontinued = 0 
				AND locstock.reorderlevel = 0 
				AND locstock.loccode = '" . $_POST['Shop'] . "'
				AND (	(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.stockid = stockmaster.stockid
						AND locstock.loccode = '" . $_POST['FromLoc'] . "') 
						-(SELECT SUM(pendingqty) 
						FROM loctransfers
						WHERE loctransfers.stockid = stockmaster.stockid
							AND shiploc='" . $_POST['FromLoc'] ."')
					) > 0
			ORDER BY stockmaster.stockid";

	$Result = DB_query($SQL);
	
	$TableTitleText = __('Items with Stock Available at ') . $_POST['FromLoc'] . __(' but RL = 0 in Shop ') . $_POST['Shop'];
	ShowTableTitle($TableTitleText);

	echo '<table class="selection">
			<thead>
				<tr>
					<th>' . __('#') . '</th>
					<th>' . __('Code') . '</th>
					<th>' . __('Category') . '</th>
					<th>' . __('Description') . '</th>
					<th>' . __('Qty at ') . $_POST['FromLoc'] . '</th>
				</tr>
			</thead>
			<tbody>';
	$i = 1;
	while ($MyRow = DB_fetch_array($Result)) {
		echo '<tr class="striped_row">';
		$CodeLink = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
		echo '<td class="number">' . $i . '</td>
				<td>' . $CodeLink . '</td>
				<td>' . $MyRow['categoryid'] . '</td>
				<td>' . $MyRow['description'] . '</td>
				<td class="number">' . locale_number_format($MyRow['QOHFrom'],0) . '</td>
				</tr>';
		$i++;
	}
	echo '</tbody>
	</table>';

}
include('includes/footer.php');

<?php

/* Session started in session.php for password checking and authorisation level check
config.php is in turn included in session.php*/
include ('includes/session.php');
$Title = _('Items with stock available not in shop');
include ('includes/header.php');
include('includes/KLDefines.php');

//check if input already
if (!(isset($_POST['Search']))) {
			
	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Items with Stock Available but not in a location') . '" alt="" />' . ' ' . _('Items with Stock Available but not in a location') . '
		</p>';
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<table class="selection">
			<thead>
				<tr>
					<th>' . _('Inventory Categories') . ':</th>
					<th><select autofocus="autofocus" required="required" minlength="1" size="12" name="Categories[]" multiple="multiple">';
	$SQL = 'SELECT categoryid, categorydescription 
			FROM stockcategory 
			ORDER BY categorydescription';
	$CatResult = DB_query($SQL);
	while ($MyRow = DB_fetch_array($CatResult)) {
		if (isset($_POST['Categories']) AND in_array($MyRow['categoryid'], $_POST['Categories'])) {
			echo '<option selected="selected" value="' . $MyRow['categoryid'] . '">' . $MyRow['categorydescription'] .'</option>';
		} else {
			echo '<option value="' . $MyRow['categoryid'] . '">' . $MyRow['categorydescription'] . '</option>';
		}
	}
	echo '</select>
			</th>
		</tr>
	</thead>
	<tbody>';

	// location selection
	$SQL="SELECT loccode,
					locationname
			FROM locations
			ORDER BY locationname";
	$Result1 = DB_query($SQL);

	echo '<tr>
			<td>' . _('Available AT') . ': </td>
			<td><select name="FromLoc">';

	while ($MyRow1 = DB_fetch_array($Result1)) {
		if ($MyRow1['loccode']==$_POST['FromLoc']){
			echo '<option selected="selected" value="' . $MyRow1['loccode'] . '">' . $MyRow1['locationname'] . '</option>';
		} else {
			echo '<option value="' . $MyRow1['loccode'] . '">' . $MyRow1['locationname'] . '</option>';
		}
	}
    echo '</select></td>
        </tr>';
		
	// location selection
	$SQL="SELECT loccode,
					locationname
			FROM locations
			WHERE typeloc IN " . LIST_BALI_SHOPS_BY_TYPE . "
			ORDER BY locationname";
	$Result1 = DB_query($SQL);

	echo '<tr>
			<td>' . _('But NOT Available At') . ': </td>
			<td><select name="Shop">';

	while ($MyRow1 = DB_fetch_array($Result1)) {
		if ($MyRow1['loccode']==$_POST['Shop']){
			echo '<option selected="selected" value="' . $MyRow1['loccode'] . '">' . $MyRow1['locationname'] . '</option>';
		} else {
			echo '<option value="' . $MyRow1['loccode'] . '">' . $MyRow1['locationname'] . '</option>';
		}
	}
    echo '</select></td>
        </tr>';
/*	//view number of NumberOfTopItems items
	echo '<tr>
			<td>' . _('Number Of Top Items') . ' </td><td>:</td>
			<td><input class="number" tabindex="4" type="text" name="NumberOfTopItems" size="8"	maxlength="8" value="100" /></td>
		 </tr>
		 <tr>
			<td></td>
			<td></td>
		</tr>';
*/	echo '
	</tbody>
	</table>
	<br />
	<div class="centre">
		<input tabindex="5" type="submit" name="Search" value="' . _('Search') . '" />
	</div>
    </div>
	</form>';
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
	
	$TableTitleText = _('Items with Stock Available at ') . $_POST['FromLoc'] . _(' but RL = 0 in Shop ') . $_POST['Shop'];
	ShowTableTitle($TableTitleText);

	echo '<table class="selection">
			<thead>
				<tr>
					<th>' . _('#') . '</th>
					<th>' . _('Code') . '</th>
					<th>' . _('Category') . '</th>
					<th>' . _('Description') . '</th>
					<th>' . _('Qty at ') . $_POST['FromLoc'] . '</th>
				</tr>
			</thead>
			<tbody>';
	$i = 1;
	while ($MyRow = DB_fetch_array($Result)) {
		echo '<tr class="striped_row">';
		$CodeLink = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
		printf('<td class="number">%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				</tr>', 
				$i, 
				$CodeLink, 
				$MyRow['categoryid'], 
				$MyRow['description'], 
				locale_number_format($MyRow['QOHFrom'],0)
				);
		$i++;
	}
	echo '</tbody>
	</table>';

}
include ('includes/footer.php');
?>
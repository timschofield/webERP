<?php

/* Session started in session.inc for password checking and authorisation level check
config.php is in turn included in session.inc*/
include ('includes/session.inc');
$Title = _('Items with stock available not in shop');
include ('includes/header.inc');
//check if input already
if (!(isset($_POST['Search']))) {
			
	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Items with Stock Available but not in a location') . '" alt="" />' . ' ' . _('Items with Stock Available but not in a location') . '
		</p>';
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table class="selection">';
	// location selection
	$SQL="SELECT loccode,
					locationname
			FROM locations
			ORDER BY locationname";
	$result1 = DB_query($SQL,$db);

	echo '<tr>
			<td style="width:100 px">' . _('Available AT') . ' </td>
			<td>:</td>
			<td><select name="FromLoc">';

	while ($myrow1 = DB_fetch_array($result1)) {
		if ($myrow1['loccode']==$_POST['FromLoc']){
			echo '<option selected="selected" value="' . $myrow1['loccode'] . '">' . $myrow1['locationname'] . '</option>';
		} else {
			echo '<option value="' . $myrow1['loccode'] . '">' . $myrow1['locationname'] . '</option>';
		}
	}
    echo '</select></td>
        </tr>';
		
	// location selection
	$SQL="SELECT loccode,
					locationname
			FROM locations
			WHERE loccode LIKE 'TOK%'
			ORDER BY locationname";
	$result1 = DB_query($SQL,$db);

	echo '<tr>
			<td style="width:100 px">' . _('But NOT Available At') . ' </td>
			<td>:</td>
			<td><select name="Shop">';

	while ($myrow1 = DB_fetch_array($result1)) {
		if ($myrow1['loccode']==$_POST['Shop']){
			echo '<option selected="selected" value="' . $myrow1['loccode'] . '">' . $myrow1['locationname'] . '</option>';
		} else {
			echo '<option value="' . $myrow1['loccode'] . '">' . $myrow1['locationname'] . '</option>';
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
	</table>
	<br />
	<div class="centre">
		<input tabindex="5" type="submit" name="Search" value="' . _('Search') . '" />
	</div>
    </div>
	</form>';
} else {
	// select the items with stock available at kantor but RL = 0 at the specified location
	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-60));
	$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.categoryid,
					(	(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.stockid = stockmaster.stockid
						AND locstock.loccode = '" . $_POST['FromLoc'] . "') 
						-(SELECT SUM(shipqty-recqty) 
						FROM loctransfers
						WHERE loctransfers.stockid = stockmaster.stockid
							AND shiploc='" . $_POST['FromLoc'] ."')
					)AS qtykantor
			FROM stockmaster, stockcategory, locstock
			WHERE stockmaster.categoryid = stockcategory.categoryid
				AND stockmaster.stockid = locstock.stockid
				AND stockcategory.stocktype = 'F'
				AND stockmaster.categoryid NOT IN('SHDISP', 'SHCONS') ";
	$SQL = $SQL . "	AND stockmaster.categoryid NOT IN('DISCOU') ";
	$SQL = $SQL . "	AND stockmaster.discontinued = 0 
				AND locstock.reorderlevel = 0 
				AND locstock.loccode = '" . $_POST['Shop'] . "'
				AND (	(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.stockid = stockmaster.stockid
						AND locstock.loccode = '" . $_POST['FromLoc'] . "') 
						-(SELECT SUM(shipqty-recqty) 
						FROM loctransfers
						WHERE loctransfers.stockid = stockmaster.stockid
							AND shiploc='" . $_POST['FromLoc'] ."')
					) > 0
			ORDER BY stockmaster.stockid";

/*				AND stockmaster.stockid IN (SELECT salesorderdetails.stkcode
							FROM salesorderdetails, salesorders
						WHERE salesorderdetails.orderno = salesorders.orderno 
							AND salesorderdetails.actualdispatchdate >= '" . $StartDate . "'
						GROUP BY salesorderdetails.stkcode
						ORDER BY SUM(salesorderdetails.qtyinvoiced) DESC
						LIMIT " . filter_number_format($_POST['NumberOfTopItems']) .")
*/			
			
			
	$result = DB_query($SQL, $db);
	
	echo '<p class="page_title_text" align="center"><strong>' . _('Items with Stock Available at ') . $_POST['FromLoc'] . _(' but RL = 0 in Shop ') . $_POST['Shop'] . '</strong></p>';
	echo '<table class="selection">';
	$TableHeader = '<tr>
						<th>' . _('#') . '</th>
						<th>' . _('Code') . '</th>
						<th>' . _('Category') . '</th>
						<th>' . _('Description') . '</th>
						<th>' . _('Qty at ') . $_POST['FromLoc'] . '</th>
					</tr>';
	echo $TableHeader;
	$k = 0; //row colour counter
	$i = 1;
	while ($myrow = DB_fetch_array($result)) {
		if ($k == 1) {
			echo '<tr class="EvenTableRows">';
			$k = 0;
		} else {
			echo '<tr class="OddTableRows">';
			$k = 1;
		}
		$CodeLink = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
		printf('<td class="number">%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				</tr>', 
				$i, 
				$CodeLink, 
				$myrow['categoryid'], 
				$myrow['description'], 
				locale_number_format($myrow['qtykantor'],0)
				);
		$i++;
	}
	echo '</table>';
	echo '<br />';
 	echo '</div>
		</form>';
}
include ('includes/footer.inc');
?>
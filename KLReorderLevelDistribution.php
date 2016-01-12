<?php

/* Session started in session.inc for password checking and authorisation level check
config.php is in turn included in session.inc*/
include ('includes/session.inc');
$Title = _('Reorder Level Distribution');
include ('includes/header.inc');
include ('includes/KLBoards.php');
include('includes/KLGeneralFunctions.php');
include ('includes/KLDefines.php');

//check if input already
if (!(isset($_POST['Search']))) {
			
	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Reorder Level Distribution for KL locations') . '" alt="" />' . ' ' . _('Reorder Level Distribution for KL locations') . '
		</p>';
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table class="selection">';
	// stock category selection
	$SQL="SELECT categoryid,
					categorydescription
			FROM stockcategory
			ORDER BY categorydescription";
	$result1 = DB_query($SQL);

	echo '<tr>
			<td style="width:150px">' . _('In Stock Category') . ' </td>
			<td>:</td>
			<td><select name="StockCat">';
	if (!isset($_POST['StockCat'])){
		$_POST['StockCat']='All';
	}
	if ($_POST['StockCat']=='All'){
		echo '<option selected="selected" value="All">' . _('All') . '</option>';
	} else {
		echo '<option value="All">' . _('All') . '</option>';
	}
	while ($myrow1 = DB_fetch_array($result1)) {
		if ($myrow1['categoryid']==$_POST['StockCat']){
			echo '<option selected="selected" value="' . $myrow1['categoryid'] . '">' . $myrow1['categorydescription'] . '</option>';
		} else {
			echo '<option value="' . $myrow1['categoryid'] . '">' . $myrow1['categorydescription'] . '</option>';
		}
	}
    echo '</select></td>
        </tr>';
		
	//view order by list to display
	echo '<tr>
			<td style="width:150px">' . _('Select Order By ') . ' </td>
			<td>:</td>
			<td><select name="Sequence">
				<option value="codeid">' . _('Code') . '</option>
				<option value="topsales60">' . _('Top Sales 60 days') . '</option>
				</select></td>
		</tr>';
	echo '
	</table>
	<br />
	<div class="centre">
		<input tabindex="5" type="submit" name="Search" value="' . _('Search') . '" />
	</div>
    </div>
	</form>';
} else {
	// everything below here to view NumberOfTopItems items sale on selected location
	$FromDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d', -60));

	$SQL = "SELECT stockmaster.stockid,
					stockmaster.categoryid,
					(SELECT COUNT(qtyinvoiced)
								FROM salesorderdetails, salesorders
								WHERE salesorderdetails.orderno = salesorders.orderno
									AND salesorderdetails.completed = 1
									AND salesorders.orddate >= '". $FromDate . "'
									AND salesorderdetails.stkcode = stockmaster.stockid) AS sales60,
					(SELECT locstock.quantity
						FROM locstock
						WHERE locstock.stockid = stockmaster.stockid
						AND locstock.loccode = " . CODE_KANTOR . ") AS qtykantor,
					(SELECT SUM(locstock.quantity)
						FROM locstock
						WHERE locstock.stockid = stockmaster.stockid
						AND ( locstock.loccode = 'SERVI'
							OR locstock.loccode = 'SERSU')) AS qtyservis,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.stockid = stockmaster.stockid
						AND locstock.loccode = 'TOK66') AS rl66,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.stockid = stockmaster.stockid
						AND locstock.loccode = 'TOKSE') AS rlse,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.stockid = stockmaster.stockid
						AND locstock.loccode = 'TOKOB') AS rlob,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.stockid = stockmaster.stockid
						AND locstock.loccode = 'TOKKS') AS rlks,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.stockid = stockmaster.stockid
						AND locstock.loccode = 'TOKBW') AS rlbw,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.stockid = stockmaster.stockid
						AND locstock.loccode = 'TOKJC') AS rljc,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.stockid = stockmaster.stockid
						AND locstock.loccode = 'TOKSA') AS rlsa,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.stockid = stockmaster.stockid
						AND locstock.loccode = 'TOKSU') AS rlsu,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.stockid = stockmaster.stockid
						AND locstock.loccode = 'TOKSS') AS rlss,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.stockid = stockmaster.stockid
						AND locstock.loccode = 'TOKUB') AS rlub,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.stockid = stockmaster.stockid
						AND locstock.loccode = 'TOKMF') AS rlmf,
					(SELECT locstock.reorderlevel
						FROM locstock
						WHERE locstock.stockid = stockmaster.stockid
						AND locstock.loccode = 'TOKPU') AS rlpu,
					(SELECT SUM(locstock.reorderlevel)
						FROM locstock
						WHERE locstock.stockid = stockmaster.stockid
						AND (locstock.loccode = 'WABOM'
							OR locstock.loccode = 'WHAYA'
							OR locstock.loccode = 'WHINT'
							OR locstock.loccode = 'WHSHE')) AS rlconsignment
			FROM stockmaster, stockcategory
			WHERE stockmaster.categoryid = stockcategory.categoryid
				AND stockcategory.stocktype = 'F'
				AND stockmaster.klchangingprice = 0
				AND stockmaster.klmovingdiscount50 = 0
				AND stockmaster.klmovingdiscount80 = 0
				AND stockmaster.discontinued = 0 ";
	if ($_POST['StockCat'] != 'All') {
		$SQL = $SQL . "	AND stockmaster.categoryid = '" . $_POST['StockCat'] . "'";
	}else{
		$SQL = $SQL . " AND stockmaster.categoryid NOT IN " . LIST_STOCK_CATEGORIES_IN_KL_SHOPS_NOT_FOR_SALE . " " ;
	}
	
	if ($_POST['Sequence'] == 'codeid') {
		$SQL = $SQL . "ORDER BY stockmaster.stockid";
	}else{	
		$SQL = $SQL . "	ORDER BY (SELECT COUNT(qtyinvoiced)
								FROM salesorderdetails, salesorders
								WHERE salesorderdetails.orderno = salesorders.orderno
									AND salesorderdetails.completed = 1
									AND salesorders.orddate >= '". $FromDate . "'
									AND salesorderdetails.stkcode = stockmaster.stockid) DESC ";
	}
	$result = DB_query($SQL);
	
	echo '<p class="page_title_text" align="center"><strong>' . _('Reorder Level Distribution by Location') . '</strong></p>';
	echo '<form action="PDFTopItems.php"  method="GET">';
    echo '<div>';
    echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table class="selection">';
	$TableHeader = '<tr>
						<th>' . _('#') . '</th>
						<th>' . _('Code') . '</th>
						<th>' . _('Category') . '</th>
						<th>' . _('KANTOR') . '</th>
						<th>' . _('66') . '</th>
						<th>' . _('SE') . '</th>
						<th>' . _('OB') . '</th>
						<th>' . _('KS') . '</th>
						<th>' . _('BW') . '</th>
						<th>' . _('JC') . '</th>
						<th>' . _('SA') . '</th>
						<th>' . _('SU') . '</th>
						<th>' . _('SS') . '</th>
						<th>' . _('UB') . '</th>
						<th>' . _('MF') . '</th>
						<th>' . _('PU') . '</th>
						<th>' . _('Consignment') . '</th>
						<th>' . _('Service') . '</th>
						<th>' . _('Sales 60') . '</th>
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
		if ($i % 20 == 0){
			echo $TableHeader;
		}
		$CodeLink = '<a href="' . $RootPath . '/StockReorderLevel.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
		printf('<td class="number">%s</td>
				<td>%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				</tr>', 
				$i, 
				$CodeLink, 
				$myrow['categoryid'], 
				locale_number_format_zero_blank($myrow['qtykantor'],0),
				locale_number_format_zero_blank($myrow['rl66'],0),
				locale_number_format_zero_blank($myrow['rlse'],0),
				locale_number_format_zero_blank($myrow['rlob'],0),
				locale_number_format_zero_blank($myrow['rlks'],0),
				locale_number_format_zero_blank($myrow['rlbw'],0),
				locale_number_format_zero_blank($myrow['rljc'],0),
				locale_number_format_zero_blank($myrow['rlsa'],0),
				locale_number_format_zero_blank($myrow['rlsu'],0),
				locale_number_format_zero_blank($myrow['rlss'],0),
				locale_number_format_zero_blank($myrow['rlub'],0),
				locale_number_format_zero_blank($myrow['rlmf'],0),
				locale_number_format_zero_blank($myrow['rlpu'],0),
				locale_number_format_zero_blank($myrow['rlconsignment'],0),
				locale_number_format_zero_blank($myrow['qtyservis'],0),
				locale_number_format($myrow['sales60'],0)
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
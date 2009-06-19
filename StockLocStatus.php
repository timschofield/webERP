<?php

$PageSecurity = 2;

include('includes/session.inc');

$title = _('All Stock Status By Location/Category');

include('includes/header.inc');

if (isset($_GET['StockID'])){
	$StockID = trim(strtoupper($_GET['StockID']));
} elseif (isset($_POST['StockID'])){
	$StockID = trim(strtoupper($_POST['StockID']));
}


echo '<hr><form action="' . $_SERVER['PHP_SELF'] . '?'. SID . '" method=post>';

$sql = "SELECT loccode,
		locationname
	FROM locations";
$resultStkLocs = DB_query($sql,$db);

echo '<table><tr><td>';

echo '<table><tr><td>' . _('From Stock Location') . ':</td><td><select name="StockLocation"> ';
while ($myrow=DB_fetch_array($resultStkLocs)){
	if (isset($_POST['StockLocation']) AND $_POST['StockLocation']!='All'){
		if ($myrow['loccode'] == $_POST['StockLocation']){
		     echo '<option selected value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
		} else {
		     echo '<option value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
		}
	} elseif ($myrow['loccode']==$_SESSION['UserStockLocation']){
		 echo '<option selected value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
		 $_POST['StockLocation']=$myrow['loccode'];
	} else {
		 echo '<option value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
	}
}
echo '</select></td></tr>';

$SQL='SELECT categoryid, categorydescription FROM stockcategory ORDER BY categorydescription';
$result1 = DB_query($SQL,$db);
if (DB_num_rows($result1)==0){
	echo '</table></td></tr>
		</table>
		<p>';
	prnMsg(_('There are no stock categories currently defined please use the link below to set them up'),'warn');
	echo '<br><a href="' . $rootpath . '/StockCategories.php?' . SID .'">' . _('Define Stock Categories') . '</a>';
	include ('includes/footer.inc');
	exit;
}

echo '<tr><td>' . _('In Stock Category') . ':</td><td><select name="StockCat">';
if (!isset($_POST['StockCat'])){
	$_POST['StockCat']='All';
}
if ($_POST['StockCat']=='All'){
	echo '<option selected value="All">' . _('All') . '</option>';
} else {
	echo '<option value="All">' . _('All') . '</option>';
}
while ($myrow1 = DB_fetch_array($result1)) {
	if ($myrow1['categoryid']==$_POST['StockCat']){
		echo '<option selected value="' . $myrow1['categoryid'] . '">' . $myrow1['categorydescription'] . '</option>';
	} else {
		echo '<option value="' . $myrow1['categoryid'] . '">' . $myrow1['categorydescription'] . '</option>';
	}
}

echo '</select></td></tr>';


echo '<tr><td>' . _('Shown Only Items Where Available Less Than Re-order Quantity') . ':</td><td><select name="BelowReorderQuantity">';
if (!isset($_POST['BelowReorderQuantity'])){
	$_POST['BelowReorderQuantity']='All';
}
if ($_POST['BelowReorderQuantity']=='All'){
	echo '<option selected value="All">' . _('All') . '</option>';
	echo '<option value="Below">' . _('Only Items Below Re-order Quantity') . '</option>';
} else {
	echo '<option value="All">' . _('All') . '</option>';
	echo '<option selected value="Below">' . _('Only Items Below Re-order Quantity') . '</option>';
}



echo '</td></tr></table>';



echo '</td></tr></table><br><div class="centre"><input type=submit name="ShowStatus" value="' . _('Show Stock Status') . '">';

echo '</div>';
echo '<hr>';


if (isset($_POST['ShowStatus'])){

	if ($_POST['StockCat']=='All') {
		$sql = "SELECT locstock.stockid,
						stockmaster.description,
						locstock.loccode,
						locations.locationname,
						locstock.quantity,
						locstock.reorderlevel,
						stockmaster.decimalplaces,
						stockmaster.serialised,
						stockmaster.controlled
					FROM locstock,
						stockmaster,
						locations
					WHERE locstock.stockid=stockmaster.stockid
						AND locstock.loccode = '$_POST[StockLocation]'
						AND locstock.loccode=locations.loccode
						AND (stockmaster.mbflag='B' OR stockmaster.mbflag='M')
					ORDER BY locstock.stockid";
	} else {
		$sql = "SELECT locstock.stockid,
						stockmaster.description,
						locstock.loccode,
						locations.locationname,
						locstock.quantity,
						locstock.reorderlevel,
						stockmaster.decimalplaces,
						stockmaster.serialised,
						stockmaster.controlled
					FROM locstock,
						stockmaster,
						locations
					WHERE locstock.stockid=stockmaster.stockid
						AND locstock.loccode = '$_POST[StockLocation]'
						AND locstock.loccode=locations.loccode
						AND (stockmaster.mbflag='B' OR stockmaster.mbflag='M')
						AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
					ORDER BY locstock.stockid";
	}


	$ErrMsg =  _('The stock held at each location cannot be retrieved because');
	$DbgMsg = _('The SQL that failed was');
	$LocStockResult = DB_query($sql, $db, $ErrMsg, $DbgMsg);

	echo '<table cellpadding=5 cellspacing=4 border=0>';

	$tableheader = '<tr>
					<th>' . _('StockID') . '</th>
					<th>' . _('Description') . '</th>
					<th>' . _('Quantity On Hand') . '</th>
					<th>' . _('Re-Order Level') . '</th>
					<th>' . _('Demand') . '</th>
					<th>' . _('Available') . '</th>
					<th>' . _('On Order') . '</th>
					</tr>';
	echo $tableheader;
	$j = 1;
	$k=0; //row colour counter

	while ($myrow=DB_fetch_array($LocStockResult)) {

		if ($k==1){
			echo '<tr class="OddTableRows">';
			$k=0;
		} else {
			echo '<tr class="EvenTableRows">';
			$k=1;
		}

		$StockID = $myrow['stockid'];

		$sql = "SELECT Sum(salesorderdetails.quantity-salesorderdetails.qtyinvoiced) AS dem
                   	FROM salesorderdetails,
                        	salesorders
                   	WHERE salesorders.orderno = salesorderdetails.orderno
			AND salesorders.fromstkloc='" . $myrow['loccode'] . "'
			AND salesorderdetails.completed=0
			AND salesorderdetails.stkcode='" . $StockID . "'";

		$ErrMsg = _('The demand for this product from') . ' ' . $myrow['loccode'] . ' ' . _('cannot be retrieved because');
		$DemandResult = DB_query($sql,$db,$ErrMsg);

		if (DB_num_rows($DemandResult)==1){
			$DemandRow = DB_fetch_row($DemandResult);
			$DemandQty =  $DemandRow[0];
		} else {
			$DemandQty =0;
		}

		//Also need to add in the demand as a component of an assembly items if this items has any assembly parents.
		$sql = "SELECT Sum((salesorderdetails.quantity-salesorderdetails.qtyinvoiced)*bom.quantity) AS dem
                   	FROM salesorderdetails,
                        	salesorders,
                        	bom,
                        	stockmaster
                   	WHERE salesorderdetails.stkcode=bom.parent
			AND salesorders.orderno = salesorderdetails.orderno
			AND salesorders.fromstkloc='" . $myrow['loccode'] . "'
			AND salesorderdetails.quantity-salesorderdetails.qtyinvoiced > 0
			AND bom.component='" . $StockID . "'
			AND stockmaster.stockid=bom.parent
			AND stockmaster.mbflag='A'";

		$ErrMsg = _('The demand for this product from') . ' ' . $myrow['loccode'] . ' ' . _('cannot be retrieved because');
		$DemandResult = DB_query($sql,$db, $ErrMsg);

		if (DB_num_rows($DemandResult)==1){
			$DemandRow = DB_fetch_row($DemandResult);
			$DemandQty += $DemandRow[0];
		}
		$sql = "SELECT SUM((woitems.qtyreqd-woitems.qtyrecd)*bom.quantity) AS dem
			FROM workorders, woitems, bom
			WHERE woitems.wo = workorders.wo
			AND   woitems.stockid =  bom.parent
			AND   workorders.closed=0
			AND   bom.component = '". $StockID . "'
			AND   workorders.loccode='". $myrow['loccode'] ."'";
		$DemandResult = DB_query($sql,$db, $ErrMsg);

		if (DB_num_rows($DemandResult)==1){
			$DemandRow = DB_fetch_row($DemandResult);
			$DemandQty += $DemandRow[0];
		}


		$sql = "SELECT SUM(purchorderdetails.quantityord - purchorderdetails.quantityrecd) AS qoo
                   	FROM purchorderdetails
                   	INNER JOIN purchorders
                   		ON purchorderdetails.orderno=purchorders.orderno
                   	WHERE purchorders.intostocklocation='" . $myrow['loccode'] . "'
			AND purchorderdetails.itemcode='" . $StockID . "'";

		$ErrMsg = _('The quantity on order for this product to be received into') . ' ' . $myrow['loccode'] . ' ' . _('cannot be retrieved because');
		$QOOResult = DB_query($sql,$db,$ErrMsg);

		if (DB_num_rows($QOOResult)==1){
			$QOORow = DB_fetch_row($QOOResult);
			$QOO =  $QOORow[0];
		} else {
			$QOOQty = 0;
		}

		if (($_POST['BelowReorderQuantity']=='Below' AND ($myrow['quantity']-$myrow['reorderlevel']-$DemandQty)<0) 
				OR $_POST['BelowReorderQuantity']=='All' ){
			
			printf("<td><a target='_blank' href='StockStatus.php?StockID=%s'>%s</td>
					<td>%s</td>
					<td align=right>%s</td>
					<td align=right>%s</td>
					<td align=right>%s</td>
					<td align=right>%s</td>
					<td align=right>%s</td>",
					strtoupper($myrow['stockid']),
					strtoupper($myrow['stockid']),
					$myrow['description'],
					number_format($myrow['quantity'],$myrow['decimalplaces']),
					number_format($myrow['reorderlevel'],$myrow['decimalplaces']),
					number_format($DemandQty,$myrow['decimalplaces']),
					number_format($myrow['quantity'] - $DemandQty,$myrow['decimalplaces']),
					number_format($QOO,$myrow['decimalplaces']));
		
			if ($myrow['serialised'] ==1){ /*The line is a serialised item*/

				echo '<td><a target="_blank" href="' . $rootpath . '/StockSerialItems.php?' . SID . '&Serialised=Yes&Location=' . $myrow['loccode'] . '&StockID=' . $StockID . '">' . _('Serial Numbers') . '</a></td></tr>';
			} elseif ($myrow['controlled']==1){
				echo '<td><a target="_blank" href="' . $rootpath . '/StockSerialItems.php?' . SID . '&Location=' . $myrow['loccode'] . '&StockID=' . $StockID . '">' . _('Batches') . '</a></td></tr>';
			}

			$j++;
			If ($j == 20){
				$j=1;
				echo $tableheader;
			}
		//end of page full new headings if
		} //end of if BelowOrderQuantity or all items
	}
	//end of while loop

	echo '</table><hr>';
	echo '</form>';
} /* Show status button hit */
include('includes/footer.inc');

?>
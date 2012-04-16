<?php

/* $Id$*/

include('includes/session.inc');

$title = _('All Stock Status By Location/Category');

include('includes/header.inc');

if (isset($_GET['StockID'])){
	$StockID = trim(mb_strtoupper($_GET['StockID']));
} elseif (isset($_POST['StockID'])){
	$StockID = trim(mb_strtoupper($_POST['StockID']));
}


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

$sql = "SELECT loccode,
    	       locationname
    	FROM locations";
$resultStkLocs = DB_query($sql,$db);

echo '<p class="page_title_text">
         <img src="' . $rootpath . '/css/' . $theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . $title.'
      </p>';

echo '<table class="selection">
		<tr><td>' . _('From Stock Location') . ':</td>
			<td><select name="StockLocation"> ';
while ($myrow=DB_fetch_array($resultStkLocs)){
	if (isset($_POST['StockLocation']) AND $_POST['StockLocation']!='All'){
		if ($myrow['loccode'] == $_POST['StockLocation']){
		     echo '<option selected="selected" value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
		} else {
		     echo '<option value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
		}
	} elseif ($myrow['loccode']==$_SESSION['UserStockLocation']){
		 echo '<option selected="selected" value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
		 $_POST['StockLocation']=$myrow['loccode'];
	} else {
		 echo '<option value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
	}
}
echo '</select></td></tr>';

$SQL="SELECT categoryid,
				categorydescription
		FROM stockcategory
		ORDER BY categorydescription";
$result1 = DB_query($SQL,$db);
if (DB_num_rows($result1)==0){
	echo '</table><p>';
	prnMsg(_('There are no stock categories currently defined please use the link below to set them up'),'warn');
	echo '<br /><a href="' . $rootpath . '/StockCategories.php">' . _('Define Stock Categories') . '</a>';
	include ('includes/footer.inc');
	exit;
}

echo '<tr><td>' . _('In Stock Category') . ':</td>
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

echo '</select></td></tr>';

echo '<tr><td>' . _('Shown Only Items Where') . ':</td>
		<td><select name="BelowReorderQuantity">';
if (!isset($_POST['BelowReorderQuantity'])){
	$_POST['BelowReorderQuantity']='All';
}
if ($_POST['BelowReorderQuantity']=='All'){
	echo '<option selected="selected" value="All">' . _('All') . '</option>
          <option value="Below">' . _('Only items below re-order quantity') . '</option>
          <option value="NotZero">' . _('Only items where stock is available') . '</option>
          <option value="OnOrder">' . _('Only items currently on order') . '</option>';
} else if ($_POST['BelowReorderQuantity']=='Below') {
	echo '<option value="All">' . _('All') . '</option>
          <option selected="selected" value="Below">' . _('Only items below re-order quantity') . '</option>
          <option value="NotZero">' . _('Only items where stock is available') . '</option>
          <option value="OnOrder">' . _('Only items currently on order') . '</option>';
} else if ($_POST['BelowReorderQuantity']=='OnOrder') {
    echo '<option value="All">' . _('All') . '</option>
          <option value="Below">' . _('Only items below re-order quantity') . '</option>
          <option value="NotZero">' . _('Only items where stock is available') . '</option>
          <option selected="selected" value="OnOrder">' . _('Only items currently on order') . '</option>';
} else  {
	echo '<option value="All">' . _('All') . '</option>
          <option value="Below">' . _('Only items below re-order quantity') . '</option>
          <option selected="selected" value="NotZero">' . _('Only items where stock is available') . '</option>
          <option value="OnOrder">' . _('Only items currently on order') . '</option>';
}

echo '</td>
     </tr>
     </table>';

echo '<br />
     <div class="centre">
          <input type="submit" name="ShowStatus" value="' . _('Show Stock Status') . '" />
     </div>';

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
						AND locstock.loccode = '".$_POST['StockLocation']."'
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
						AND locstock.loccode = '" . $_POST['StockLocation'] . "'
						AND locstock.loccode=locations.loccode
						AND (stockmaster.mbflag='B' OR stockmaster.mbflag='M')
						AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
					ORDER BY locstock.stockid";
	}

	$ErrMsg =  _('The stock held at each location cannot be retrieved because');
	$DbgMsg = _('The SQL that failed was');
	$LocStockResult = DB_query($sql, $db, $ErrMsg, $DbgMsg);

	echo '<br />
         <table cellpadding="5" cellspacing="4" class="selection">';

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

		$StockID = $myrow['stockid'];

		$sql = "SELECT SUM(salesorderdetails.quantity-salesorderdetails.qtyinvoiced) AS dem
                   	FROM salesorderdetails INNER JOIN salesorders
                   	ON salesorders.orderno = salesorderdetails.orderno
        			WHERE salesorders.fromstkloc='" . $myrow['loccode'] . "'
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
		$sql = "SELECT SUM((salesorderdetails.quantity-salesorderdetails.qtyinvoiced)*bom.quantity) AS dem
				FROM salesorderdetails INNER JOIN salesorders
				     ON salesorders.orderno = salesorderdetails.orderno
                INNER JOIN bom 
                      ON salesorderdetails.stkcode=bom.parent
				INNER JOIN stockmaster
				      ON stockmaster.stockid=bom.parent
				WHERE salesorders.fromstkloc='" . $myrow['loccode'] . "'
				AND salesorderdetails.quantity-salesorderdetails.qtyinvoiced > 0
				AND bom.component='" . $StockID . "'
				AND stockmaster.mbflag='A'";

		$ErrMsg = _('The demand for this product from') . ' ' . $myrow['loccode'] . ' ' . _('cannot be retrieved because');
		$DemandResult = DB_query($sql,$db, $ErrMsg);

		if (DB_num_rows($DemandResult)==1){
			$DemandRow = DB_fetch_row($DemandResult);
			$DemandQty += $DemandRow[0];
		}
		$sql = "SELECT SUM((woitems.qtyreqd-woitems.qtyrecd)*bom.quantity) AS dem
				FROM workorders INNER JOIN woitems
                     ON woitems.wo = workorders.wo
                INNER JOIN bom 
                      ON woitems.stockid =  bom.parent
				WHERE workorders.closed=0
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
				AND purchorderdetails.itemcode='" . $StockID . "'
					AND purchorders.status <> 'Cancelled'
					AND purchorders.status <> 'Rejected'
					AND purchorders.status <> 'Pending'";

		$ErrMsg = _('The quantity on order for this product to be received into') . ' ' . $myrow['loccode'] . ' ' . _('cannot be retrieved because');
		$QOOResult = DB_query($sql,$db,$ErrMsg);

		if (DB_num_rows($QOOResult)==1){
			$QOORow = DB_fetch_row($QOOResult);
			$QOO =  $QOORow[0];
		} else {
			$QOO = 0;
		}

		if (($_POST['BelowReorderQuantity']=='Below' AND ($myrow['quantity']-$myrow['reorderlevel']-$DemandQty)<0)
				OR $_POST['BelowReorderQuantity']=='All' OR $_POST['BelowReorderQuantity']=='NotZero'
                OR ($_POST['BelowReorderQuantity']=='OnOrder' AND $QOO != 0)){

			if (($_POST['BelowReorderQuantity']=='NotZero') AND (($myrow['quantity']-$DemandQty)>0)) {

				if ($k==1){
					echo '<tr class="OddTableRows">';
					$k=0;
				} else {
					echo '<tr class="EvenTableRows">';
					$k=1;
				}
				printf('<td><a target="_blank" href="' . $rootpath . '/StockStatus.php?StockID=%s">%s</a></td>
    					<td>%s</td>
    					<td class="number">%s</td>
    					<td class="number">%s</td>
    					<td class="number">%s</td>
    					<td class="number"><a target="_blank" href="' . $rootpath . '/SelectProduct.php?StockID=%s">%s</a></td>
    					<td class="number">%s</td>
    					</tr>',
    					mb_strtoupper($myrow['stockid']),
    					mb_strtoupper($myrow['stockid']),
    					$myrow['description'],
    					locale_number_format($myrow['quantity'],$myrow['decimalplaces']),
    					locale_number_format($myrow['reorderlevel'],$myrow['decimalplaces']),
    					locale_number_format($DemandQty,$myrow['decimalplaces']),
    					mb_strtoupper($myrow['stockid']),
    					locale_number_format($myrow['quantity'] - $DemandQty,$myrow['decimalplaces']),
    					locale_number_format($QOO,$myrow['decimalplaces']));

				if ($myrow['serialised'] ==1){ /*The line is a serialised item*/

					echo '<td><a target="_blank" href="' . $rootpath . '/StockSerialItems.php?Serialised=Yes&Location=' . $myrow['loccode'] . '&StockID=' . $StockID . '">' . _('Serial Numbers') . '</a></td></tr>';
				} elseif ($myrow['controlled']==1){
					echo '<td><a target="_blank" href="' . $rootpath . '/StockSerialItems.php?Location=' . $myrow['loccode'] . '&StockID=' . $StockID . '">' . _('Batches') . '</a></td></tr>';
				}
			} else if ($_POST['BelowReorderQuantity']!='NotZero') {
				if ($k==1){
					echo '<tr class="OddTableRows">';
					$k=0;
				} else {
					echo '<tr class="EvenTableRows">';
					$k=1;
				}
				printf('<td><a target="_blank" href="' . $rootpath . '/StockStatus.php?StockID=%s">%s</a></td>
    					<td>%s</td>
    					<td class="number">%s</td>
    					<td class="number">%s</td>
    					<td class="number">%s</td>
    					<td class="number"><a target="_blank" href="' . $rootpath . '/SelectProduct.php?StockID=%s">%s</a></td>
    					<td class="number">%s</td>',
    					mb_strtoupper($myrow['stockid']),
    					mb_strtoupper($myrow['stockid']),
    					$myrow['description'],
    					locale_number_format($myrow['quantity'],$myrow['decimalplaces']),
    					locale_number_format($myrow['reorderlevel'],$myrow['decimalplaces']),
    					locale_number_format($DemandQty,$myrow['decimalplaces']),
    					mb_strtoupper($myrow['stockid']),
    					locale_number_format($myrow['quantity'] - $DemandQty,$myrow['decimalplaces']),
    					locale_number_format($QOO,$myrow['decimalplaces']));
				if ($myrow['serialised'] ==1){ /*The line is a serialised item*/

					echo '<td><a target="_blank" href="' . $rootpath . '/StockSerialItems.php?Serialised=Yes&Location=' . $myrow['loccode'] . '&StockID=' . $StockID . '">' . _('Serial Numbers') . '</a></td></tr>';
				} elseif ($myrow['controlled']==1){
					echo '<td><a target="_blank" href="' . $rootpath . '/StockSerialItems.php?Location=' . $myrow['loccode'] . '&StockID=' . $StockID . '">' . _('Batches') . '</a></td></tr>';
				}
			} //end of page full new headings if
		} //end of if BelowOrderQuantity or all items
	}
	//end of while loop

	echo '</table>';
	echo '</form>';
} /* Show status button hit */
include('includes/footer.inc');

?>
<?php


include('includes/session.php');
$Title = _('Search Work Orders');
include('includes/header.php');

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>
	<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
	<div>
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';


if (isset($_GET['WO'])) {
	$SelectedWO = $_GET['WO'];
} elseif (isset($_POST['WO'])){
	$SelectedWO = $_POST['WO'];
} else {
	unset($SelectedWO);
}

if (isset($_GET['SelectedStockItem'])) {
	$SelectedStockItem = $_GET['SelectedStockItem'];
} elseif (isset($_POST['SelectedStockItem'])){
	$SelectedStockItem = $_POST['SelectedStockItem'];
} else {
	unset($SelectedStockItem);
}


if (isset($_POST['ResetPart'])){
	 unset($SelectedStockItem);
}

if (isset($SelectedWO) AND $SelectedWO!='') {
	$SelectedWO = trim($SelectedWO);
	if (!is_numeric($SelectedWO)){
		  prnMsg(_('The work order number entered MUST be numeric'),'warn');
		  unset ($SelectedWO);
		  include('includes/footer.php');
		  exit;
	} else {
		echo _('Work Order Number') . ' - ' . $SelectedWO;
	}
}

if (isset($_POST['SearchParts'])){

	if ($_POST['Keywords'] AND $_POST['StockCode']) {
		echo _('Stock description keywords have been used in preference to the Stock code extract entered');
	}
	if ($_POST['Keywords']) {
		//insert wildcard characters in spaces
		$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';

		$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.decimalplaces,
						SUM(locstock.quantity) AS qoh,
						stockmaster.units
					FROM stockmaster,
						locstock
					WHERE stockmaster.stockid=locstock.stockid
					AND stockmaster.description " . LIKE . " '" . $SearchString . "'
					AND stockmaster.categoryid='" . $_POST['StockCat']. "'
					AND stockmaster.mbflag='M'
					GROUP BY stockmaster.stockid,
						stockmaster.description,
						stockmaster.decimalplaces,
						stockmaster.units
					ORDER BY stockmaster.stockid";

	 } elseif (isset($_POST['StockCode'])){
		$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.decimalplaces,
						sum(locstock.quantity) as qoh,
						stockmaster.units
					FROM stockmaster,
						locstock
					WHERE stockmaster.stockid=locstock.stockid
					AND stockmaster.stockid " . LIKE . " '%" . $_POST['StockCode'] . "%'
					AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
					AND stockmaster.mbflag='M'
					GROUP BY stockmaster.stockid,
						stockmaster.description,
						stockmaster.decimalplaces,
						stockmaster.units
					ORDER BY stockmaster.stockid";

	 } elseif (!isset($_POST['StockCode']) AND !isset($_POST['Keywords'])) {
		$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.decimalplaces,
						sum(locstock.quantity) as qoh,
						stockmaster.units
					FROM stockmaster,
						locstock
					WHERE stockmaster.stockid=locstock.stockid
					AND stockmaster.categoryid='" . $_POST['StockCat'] ."'
					AND stockmaster.mbflag='M'
					GROUP BY stockmaster.stockid,
						stockmaster.description,
						stockmaster.decimalplaces,
						stockmaster.units
					ORDER BY stockmaster.stockid";
	 }

	$ErrMsg =  _('No items were returned by the SQL because');
	$DbgMsg = _('The SQL used to retrieve the searched parts was');
	$StockItemsResult = DB_query($SQL,$ErrMsg,$DbgMsg);
}

if (isset($_POST['StockID'])){
	$StockID = trim(mb_strtoupper($_POST['StockID']));
} elseif (isset($_GET['StockID'])){
	$StockID = trim(mb_strtoupper($_GET['StockID']));
}

if (!isset($StockID)) {

	 /* Not appropriate really to restrict search by date since may miss older
	 ouststanding orders
	$OrdersAfterDate = Date('d/m/Y',Mktime(0,0,0,Date('m')-2,Date('d'),Date('Y')));
	 */

	if (!isset($SelectedWO) or ($SelectedWO=='')){
		echo '<table class="selection"><tr><td>';
		if (isset($SelectedStockItem)) {
			echo _('For the item') . ': ' . $SelectedStockItem . ' ' . _('and') . ' <input type="hidden" name="SelectedStockItem" value="' . $SelectedStockItem . '" />';
		}
		echo _('Work Order number') . ': <input type="text" name="WO" autofocus="autofocus" maxlength="8" size="9" />&nbsp; ' . _('Processing at') . ':<select name="StockLocation"> ';

		$sql = "SELECT locations.loccode, locationname FROM locations
				INNER JOIN locationusers
					ON locationusers.loccode=locations.loccode
					AND locationusers.userid='" .  $_SESSION['UserID'] . "'
					AND locationusers.canview=1
				WHERE locations.usedforwo = 1";

		$resultStkLocs = DB_query($sql);

		while ($myrow=DB_fetch_array($resultStkLocs)){
			if (isset($_POST['StockLocation'])){
				if ($myrow['loccode'] == $_POST['StockLocation']){
					 echo '<option selected="selected" value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
				} else {
					 echo '<option value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
				}
			} elseif ($myrow['loccode']==$_SESSION['UserStockLocation']){
				 echo '<option selected="selected" value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
			} else {
				 echo '<option value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
			}
		}

		echo '</select> &nbsp;&nbsp;
			<select name="ClosedOrOpen">';

		if (isset($_GET['ClosedOrOpen']) AND $_GET['ClosedOrOpen']=='Closed_Only'){
			$_POST['ClosedOrOpen']='Closed_Only';
		}

		if (isset($_POST['ClosedOrOpen']) AND $_POST['ClosedOrOpen']=='Closed_Only'){
			echo '<option selected="selected" value="Closed_Only">' . _('Closed Work Orders Only') . '</option>';
			echo '<option value="Open_Only">' . _('Open Work Orders Only')  . '</option>';
		} else {
			echo '<option value="Closed_Only">' . _('Closed Work Orders Only')  . '</option>';
			echo '<option selected="selected" value="Open_Only">' . _('Open Work Orders Only')  . '</option>';
		}

		echo '</select> &nbsp;&nbsp;
			<input type="submit" name="SearchOrders" value="' . _('Search') . '" />
			&nbsp;&nbsp;<a href="' . $RootPath . '/WorkOrderEntry.php">' . _('New Work Order') . '</a></td>
			</tr>
			</table>
			<br />';
	}

	$SQL="SELECT categoryid,
			categorydescription
			FROM stockcategory
			ORDER BY categorydescription";

	$result1 = DB_query($SQL);

	echo '<table class="selection">
			<tr>
				<th colspan="6"><h3>' . _('To search for work orders for a specific item use the item selection facilities below') . '</h3></th>
			</tr>
			<tr>
				<td>' . _('Select a stock category') . ':
	  			<select name="StockCat">';

	while ($myrow1 = DB_fetch_array($result1)) {
		echo '<option value="'. $myrow1['categoryid'] . '">' . $myrow1['categorydescription'] . '</option>';
	}

	  echo '</select></td>
	  		<td>' . _('Enter text extract(s) in the description') . ':</td>
	  		<td><input type="text" name="Keywords" size="20" maxlength="25" /></td>
		</tr>
	  	<tr>
			<td></td>
	  		<td><b>' . _('OR') . ' </b>' . _('Enter extract of the Stock Code') . ':</td>
	  		<td><input type="text" name="StockCode" size="15" maxlength="18" /></td>
	  	</tr>
	  </table><br />';
	echo '<div class="centre"><input type="submit" name="SearchParts" value="' . _('Search Items Now') . '" />
        <input type="submit" name="ResetPart" value="' . _('Show All') . '" /></div>';

	if (isset($StockItemsResult)) {

		echo '<br />
			<table cellpadding="2" class="selection">
			<thead>
			<tr>
				<th class="ascending">' . _('Code') . '</th>
				<th class="ascending">' . _('Description') . '</th>
				<th class="ascending">' . _('On Hand') . '</th>
				<th>' . _('Units') . '</th>
				</tr>
			</thead>
			<tbody>';

		while ($myrow=DB_fetch_array($StockItemsResult)) {

			printf('<tr class="striped_row">
					<td><input type="submit" name="SelectedStockItem" value="%s" /></td>
					<td>%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					</tr>',
					$myrow['stockid'],
					$myrow['description'],
					locale_number_format($myrow['qoh'],$myrow['decimalplaces']),
					$myrow['units']);

		}//end of while loop
		echo '</tbody></table>';
	}
	//end if stock search results to show
	  else {

	  	if (!isset($_POST['StockLocation'])) {
	  		$_POST['StockLocation'] = '';
	  	}

		//figure out the SQL required from the inputs available
		if (isset($_POST['ClosedOrOpen']) and $_POST['ClosedOrOpen']=='Open_Only'){
			$ClosedOrOpen = 0;
		} else {
			$ClosedOrOpen = 1;
		}
		if (isset($SelectedWO) AND $SelectedWO !='') {
				$SQL = "SELECT workorders.wo,
								woitems.stockid,
								stockmaster.description,
								stockmaster.decimalplaces,
								woitems.qtyreqd,
								woitems.qtyrecd,
								workorders.requiredby,
								workorders.startdate,
								workorders.reference,
								workorders.loccode
						FROM workorders
						INNER JOIN woitems ON workorders.wo=woitems.wo
						INNER JOIN stockmaster ON woitems.stockid=stockmaster.stockid
						INNER JOIN locationusers ON locationusers.loccode=workorders.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
						WHERE workorders.closed='" . $ClosedOrOpen . "'
						AND workorders.wo='". $SelectedWO ."'
						ORDER BY workorders.wo,
								woitems.stockid";
		} else {
			  /* $DateAfterCriteria = FormatDateforSQL($OrdersAfterDate); */

				if (isset($SelectedStockItem)) {
					$SQL = "SELECT workorders.wo,
									woitems.stockid,
									stockmaster.description,
									stockmaster.decimalplaces,
									woitems.qtyreqd,
									woitems.qtyrecd,
									workorders.requiredby,
									workorders.startdate,
									workorders.reference,
									workorders.loccode
							FROM workorders
							INNER JOIN woitems ON workorders.wo=woitems.wo
							INNER JOIN stockmaster ON woitems.stockid=stockmaster.stockid
							INNER JOIN locationusers ON locationusers.loccode=workorders.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
							WHERE workorders.closed='" . $ClosedOrOpen . "'
							AND woitems.stockid='". $SelectedStockItem ."'
							AND workorders.loccode='" . $_POST['StockLocation'] . "'
							ORDER BY workorders.wo,
								 woitems.stockid";
				} else {
					$SQL = "SELECT workorders.wo,
									woitems.stockid,
									stockmaster.description,
									stockmaster.decimalplaces,
									woitems.qtyreqd,
									woitems.qtyrecd,
									workorders.requiredby,
									workorders.startdate,
									workorders.reference,
									workorders.loccode
							FROM workorders
							INNER JOIN woitems ON workorders.wo=woitems.wo
							INNER JOIN locationusers ON locationusers.loccode=workorders.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
							INNER JOIN stockmaster ON woitems.stockid=stockmaster.stockid
							WHERE workorders.closed='" . $ClosedOrOpen . "'
							AND workorders.loccode='" . $_POST['StockLocation'] . "'
							ORDER BY workorders.wo,
									 woitems.stockid";
				}
		} //end not order number selected

		$ErrMsg = _('No works orders were returned by the SQL because');
		$WorkOrdersResult = DB_query($SQL,$ErrMsg);

		/*show a table of the orders returned by the SQL */
		if (DB_num_rows($WorkOrdersResult)>0) {
			echo '<br />
				<table cellpadding="2" width="95%" class="selection">
				<thead>
				<tr>
					<th>' . _('Modify') . '</th>
					<th class="ascending">' . _('Status') . '</th>
					<th>' . _('Issue To') . '</th>
					<th>' . _('Receive') . '</th>
					<th>' . _('Costing') . '</th>
					<th>' . _('Paperwork') . '</th>
					<th>' . _('Location') . '</th>
					<th class="ascending">' . _('Item') . '</th>
					<th class="ascending">' . _('Quantity Required') . '</th>
					<th class="ascending">' . _('Quantity Received') . '</th>
					<th class="ascending">' . _('Quantity Outstanding') . '</th>
					<th class="ascending">' . _('Start Date')  . '</th>
					<th class="ascending">' . _('Required Date') . '</th>
					</tr>
				</thead>
				<tbody>';

		while ($myrow=DB_fetch_array($WorkOrdersResult)) {

			$ModifyPage = $RootPath . '/WorkOrderEntry.php?WO=' . $myrow['wo'];
			$Status_WO = $RootPath . '/WorkOrderStatus.php?WO=' .$myrow['wo'] . '&amp;StockID=' . urlencode($myrow['stockid']);
			$Receive_WO = $RootPath . '/WorkOrderReceive.php?WO=' .$myrow['wo'] . '&amp;StockID=' . urlencode($myrow['stockid']);
			$Issue_WO = $RootPath . '/WorkOrderIssue.php?WO=' .$myrow['wo'] . '&amp;StockID=' . urlencode($myrow['stockid']);
			$Costing_WO =$RootPath . '/WorkOrderCosting.php?WO=' .$myrow['wo'];
			$Printing_WO =$RootPath . '/PDFWOPrint.php?WO=' .$myrow['wo'] . '&amp;StockID=' . urlencode($myrow['stockid']);

			$FormatedRequiredByDate = ConvertSQLDate($myrow['requiredby']);
			$FormatedStartDate = ConvertSQLDate($myrow['startdate']);


			printf('<tr class="striped_row">
					<td><a href="%s">%s</a></td>
					<td><a href="%s">' . _('Status') . '</a></td>
					<td><a href="%s">' . _('Issue To') . '</a></td>
					<td><a href="%s">' . _('Receive') . '</a></td>
					<td><a href="%s">' . _('Costing') . '</a></td>
					<td><a href="%s">' . _('Print W/O') . '</a></td>
					<td>%s</td>
					<td>%s - %s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					</tr>',
					$ModifyPage,
					$myrow['wo'].'['.$myrow['reference'] . ']',
					$Status_WO,
					$Issue_WO,
					$Receive_WO,
					$Costing_WO,
					$Printing_WO,
					$myrow['loccode'],
					urlencode($myrow['stockid']),
					$myrow['description'],
					locale_number_format($myrow['qtyreqd'],$myrow['decimalplaces']),
					locale_number_format($myrow['qtyrecd'],$myrow['decimalplaces']),
					locale_number_format($myrow['qtyreqd']-$myrow['qtyrecd'],$myrow['decimalplaces']),
					$FormatedStartDate,
					$FormatedRequiredByDate);
		}
		//end of while loop

			echo '</tbody></table>';
      }
	}

	echo '</div>
          </form>';
}

include('includes/footer.php');
?>

<?php
/* $Id: CollectiveWorkOrderCost.php 6946 2016-05-06 07:30:11Z  exsonqu $*/
/* Multiple work orders cost review */

include('includes/session.php');
$Title = _('Search Work Orders');
$ViewTopic = 'GeneralLedger';
$BookMark = 'Z_ChangeGLAccountCode';
include('includes/header.php');

echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
	'/images/magnifier.png" title="', // Icon image.
	$Title, '" /> ', // Icon title.
	$Title, '</p>';// Page title.

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
	<div>
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (isset($_POST['Submit'])) {//users have selected the WO to calculate and submit it
		$WOSelected = '';
		$i = 0;
		foreach ($_POST as $Key=>$Value) {
			if (substr($Key,0,3) == 'WO_'){
				if ($i>0) $WOSelected .=",";
				if($Value == 'on'){
					$WOSelected .= substr($Key,3);
				}
				$i++;
			}
		}
		if (empty($WOSelected)) {
			prnMsg(_('There are no work orders selected'),'error');
		} else {
			//lets do the workorder issued items retrieve
			$sql = "SELECT stockmoves.stockid,
				stockmaster.description,
				stockmaster.decimalplaces,
				trandate,
				qty,
				reference,
				stockmoves.standardcost
				FROM stockmoves INNER JOIN stockmaster
				ON stockmoves.stockid=stockmaster.stockid
				WHERE stockmoves.type=28
				AND reference IN (" . $WOSelected . ")
				ORDER BY reference";
			$ErrMsg = _('Failed to retrieve wo cost data');
		       	$result = DB_query($sql,$ErrMsg);
			if (DB_num_rows($result)>0) {
				echo '<table class="selection">
					<tr><th class="ascending">' . _('Item') . '</th>
						<th>' . _('Description') . '</th>
						<th class="ascending">' . _('Date Issued') . '</th>
						<th class="ascending">' . _('Issued Qty') . '</th>
						<th class="ascending">' . _('Issued Cost') . '</th>
						<th class="ascending">' . _('Work Order') . '</th>
					</tr>';
				$i = 0;
				$TotalCost = 0;
				while ($myrow = DB_fetch_array($result)){
					if ($i==0) {
						echo '<tr class="EvenTableRows">';
						$i = 1;
					} else {
						echo '<tr class="OddTableRows">';
						$i = 0;
					}
					$IssuedQty = - $myrow['qty'];
					$IssuedCost = $IssuedQty * $myrow['standardcost'];
					$TotalCost += $IssuedCost;
					echo '<td>' . $myrow['stockid'] . '</td>
						<td>' . $myrow['description'] . '</td>
						<td>' . $myrow['trandate'] . '</td>
						<td class="number">' . locale_number_format($IssuedQty,$myrow['decimalplaces']) . '</td>
						<td class="number">' . locale_number_format($IssuedCost,2) . '</td>
						<td>' . $myrow['reference'] . '</td>
					       </tr>';
				}
				echo '<tr><td colspan="4"><b>' . _('Total Cost') . '</b></td>
					<td colspan="2"><b>' .locale_number_format($TotalCost,2) . '</b></td>
					</tr></table>';
			} else {
				prnMsg(_('There are no data available'),'error');
				include('includes/footer.php');
				exit;
			}
		}//end of the work orders are not empty
		echo '<a href="'.htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('Select Other Work Orders') . '</a>';
		include('includes/footer.php');
		exit;

}


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

		if ($_GET['ClosedOrOpen']=='Closed_Only'){
			$_POST['ClosedOrOpen']='Closed_Only';
		}

		if ($_POST['ClosedOrOpen']=='Closed_Only'){
			echo '<option selected="selected" value="Closed_Only">' . _('Closed Work Orders Only') . '</option>';
			echo '<option value="Open_Only">' . _('Open Work Orders Only')  . '</option>';
			echo '<option value="All">' . _('All') . '</option>';
		} elseif($_POST['ClosedOrOpen'] == 'Open_Only') {
			echo '<option value="Closed_Only">' . _('Closed Work Orders Only')  . '</option>';
			echo '<option selected="selected" value="Open_Only">' . _('Open Work Orders Only')  . '</option>';
			echo '<option value="All">' . _('All') . '</option>';
		} elseif ($_POST['ClosedOrOpen'] == 'All') {
			echo '<option value="Closed_Only">' . _('Closed Work Orders Only')  . '</option>';
			echo '<option value="Open_Only">' . _('Open Work Orders Only')  . '</option>';
			echo '<option selected="selected" value="All">' . _('All') . '</option>';
		} else {
			echo '<option value="Closed_Only">' . _('Closed Work Orders Only')  . '</option>';
			echo '<option value="Open_Only">' . _('Open Work Orders Only')  . '</option>';
			echo '<option selected="selected" value="All">' . _('All') . '</option>';
		}
		if (!isset($_POST['DateFrom'])) {
			$_POST['DateFrom'] = '';
		}
		if (!isset($_POST['DateTo'])) {
			$_POST['DateTo'] = '';
		}

		echo '</select> &nbsp;&nbsp;
			</td>
			</tr>
			<tr>
			<td colspan="2">' . _('Start Date From') . ':<input type="text" name="DateFrom" value="' . $_POST['DateFrom'] . '" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" />

			' . _('Start Date To') . ':<input type="text" name="DateTo" value="' . $_POST['DateTo'] . '" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" />
			</td>
				</tr>
				</table>';
		echo '<div class="center">
			<input type="submit" name="SearchOrders" value="' . _('Search') . '" />
			&nbsp;&nbsp;<a href="' . $RootPath . '/WorkOrderEntry.php">' . _('New Work Order') . '</a>
			</div>
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
			<tr>
				<th class="ascending">' . _('Code') . '</th>
				<th class="ascending">' . _('Description') . '</th>
				<th class="ascending">' . _('On Hand') . '</th>
				<th>' . _('Units') . '</th>
			</tr>';
		$k=0; //row colour counter

		while ($myrow=DB_fetch_array($StockItemsResult)) {

			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k++;
			}

			printf('<td><input type="submit" name="SelectedStockItem" value="%s" /></td>
					<td>%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					</tr>',
					$myrow['stockid'],
					$myrow['description'],
					locale_number_format($myrow['qoh'],$myrow['decimalplaces']),
					$myrow['units']);

		}//end of while loop
		echo '</table>';
	}
	//end if stock search results to show
	  else {

	  	if (!isset($_POST['StockLocation'])) {
	  		$_POST['StockLocation'] = '';
	  	}

		//figure out the SQL required from the inputs available
		if (isset($_POST['ClosedOrOpen']) and $_POST['ClosedOrOpen']=='Open_Only'){
			$ClosedOrOpen = ' AND workorders.closed=0';
		} elseif(isset($_POST['ClosedOrOpen']) AND $_POST['ClosedOrOpen'] == 'Closed_Only') {
			$ClosedOrOpen = ' AND workorders.closed=1';
		} else {
			$ClosedOrOpen = '';
		}
		//start date and end date
		if (!empty($_POST['DateFrom'])) {
			$StartDateFrom = " AND workorders.startdate>='" . FormatDateForSQL($_POST['DateFrom']) . "'";
		}
		if (!empty($_POST['DateTo'])) {
			$StartDateTo = " AND workorders.startdate<='" . FormatDateForSQL($_POST['DateTo']) . "'";
		}

		if (isset($SelectedWO) AND $SelectedWO !='') {
				$SQL = "SELECT workorders.wo,
								woitems.stockid,
								stockmaster.description,
								stockmaster.decimalplaces,
								woitems.qtyreqd,
								woitems.qtyrecd,
								workorders.requiredby,
								workorders.startdate
						FROM workorders
						INNER JOIN woitems ON workorders.wo=woitems.wo
						INNER JOIN stockmaster ON woitems.stockid=stockmaster.stockid
						INNER JOIN locationusers ON locationusers.loccode=workorders.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
						WHERE 1 " . $ClosedOrOpen . $StartDateFrom . $StartDateTo . "
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
									workorders.startdate
							FROM workorders
							INNER JOIN woitems ON workorders.wo=woitems.wo
							INNER JOIN stockmaster ON woitems.stockid=stockmaster.stockid
							INNER JOIN locationusers ON locationusers.loccode=workorders.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
							WHERE 1 " . $ClosedOrOpen . $StartDateFrom . $StartDateTo . "
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
									workorders.startdate
							FROM workorders
							INNER JOIN woitems ON workorders.wo=woitems.wo
							INNER JOIN locationusers ON locationusers.loccode=workorders.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
							INNER JOIN stockmaster ON woitems.stockid=stockmaster.stockid
							WHERE  1 " . $ClosedOrOpen . $StartDateFrom . $StartDateTo ."
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
				<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post" id="wos">
				<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
				<table cellpadding="2" width="95%" class="selection">
				<tr>
					<th>' . _('Select') . '</th>
					<th>' . _('Modify') . '</th>
					<th class="ascending">' . _('Status') . '</th>
					<th>' . _('Issue To') . '</th>
					<th>' . _('Receive') . '</th>
					<th>' . _('Costing') . '</th>
					<th>' . _('Paperwork') . '</th>
					<th class="ascending">' . _('Item') . '</th>
					<th class="ascending">' . _('Quantity Required') . '</th>
					<th class="ascending">' . _('Quantity Received') . '</th>
					<th class="ascending">' . _('Quantity Outstanding') . '</th>
					<th class="ascending">' . _('Start Date')  . '</th>
					<th class="ascending">' . _('Required Date') . '</th>
				</tr>';

		$k=0; //row colour counter
		while ($myrow=DB_fetch_array($WorkOrdersResult)) {

			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k++;
			}

			$ModifyPage = $RootPath . '/WorkOrderEntry.php?WO=' . $myrow['wo'];
			$Status_WO = $RootPath . '/WorkOrderStatus.php?WO=' .$myrow['wo'] . '&amp;StockID=' . $myrow['stockid'];
			$Receive_WO = $RootPath . '/WorkOrderReceive.php?WO=' .$myrow['wo'] . '&amp;StockID=' . $myrow['stockid'];
			$Issue_WO = $RootPath . '/WorkOrderIssue.php?WO=' .$myrow['wo'] . '&amp;StockID=' . $myrow['stockid'];
			$Costing_WO =$RootPath . '/WorkOrderCosting.php?WO=' .$myrow['wo'];
			$Printing_WO =$RootPath . '/PDFWOPrint.php?WO=' .$myrow['wo'] . '&amp;StockID=' . $myrow['stockid'];

			$FormatedRequiredByDate = ConvertSQLDate($myrow['requiredby']);
			$FormatedStartDate = ConvertSQLDate($myrow['startdate']);


			printf('<td><input type="checkbox" name="WO_%s" /></td>
					<td><a href="%s">%s</a></td>
					<td><a href="%s">' . _('Status') . '</a></td>
					<td><a href="%s">' . _('Issue To') . '</a></td>
					<td><a href="%s">' . _('Receive') . '</a></td>
					<td><a href="%s">' . _('Costing') . '</a></td>
					<td><a href="%s">' . _('Print W/O') . '</a></td>
					<td>%s - %s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					</tr>',
					$myrow['wo'],
					$ModifyPage,
					$myrow['wo'],
					$Status_WO,
					$Issue_WO,
					$Receive_WO,
					$Costing_WO,
					$Printing_WO,
					$myrow['stockid'],
					$myrow['description'],
					locale_number_format($myrow['qtyreqd'],$myrow['decimalplaces']),
					locale_number_format($myrow['qtyrecd'],$myrow['decimalplaces']),
					locale_number_format($myrow['qtyreqd']-$myrow['qtyrecd'],$myrow['decimalplaces']),
					$FormatedStartDate,
					$FormatedRequiredByDate);
		//end of page full new headings if
		}
		//end of while loop

		echo '</table>
			<div class="center">
				<input type="submit" value="' . _('Submit') . '" name="Submit" />
			</form>';
      }
	}

	echo '</div>
          </form>';
}

include('includes/footer.php');
?>

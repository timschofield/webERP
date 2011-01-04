<?php
/* $Id$*/
//$PageSecurity = 2;
include ('includes/session.inc');
$title = _('Search Purchase Orders');
include ('includes/header.inc');
echo '<p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/magnifier.png" title="' . _('Purchase Orders') . '" alt=""  />' . ' ' . _('Purchase Orders') . '</p>';
if (isset($_GET['SelectedStockItem'])) {
	$SelectedStockItem = $_GET['SelectedStockItem'];
} elseif (isset($_POST['SelectedStockItem'])) {
	$SelectedStockItem = $_POST['SelectedStockItem'];
}
if (isset($_GET['OrderNumber'])) {
	$OrderNumber = $_GET['OrderNumber'];
} elseif (isset($_POST['OrderNumber'])) {
	$OrderNumber = $_POST['OrderNumber'];
}
if (isset($_GET['SelectedSupplier'])) {
	$SelectedSupplier = $_GET['SelectedSupplier'];
} elseif (isset($_POST['SelectedSupplier'])) {
	$SelectedSupplier = $_POST['SelectedSupplier'];
}
echo '<form action="' . $_SERVER['PHP_SELF'] . '?' . SID . '" method=post>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
if (isset($_POST['ResetPart'])) {
	unset($SelectedStockItem);
}
if (isset($OrderNumber) && $OrderNumber != "") {
	if (!is_numeric($OrderNumber)) {
		prnMsg(_('The Order Number entered') . ' <U>' . _('MUST') . '</U> ' . _('be numeric'), 'error');
		unset($OrderNumber);
	} else {
		echo _('Order Number') . ' - ' . $OrderNumber;
	}
} else {
	if (isset($SelectedSupplier)) {
		echo _('For supplier') . ': ' . $SelectedSupplier . ' ' . _('and') . ' ';
		echo '<input type=hidden name="SelectedSupplier" value=' . $SelectedSupplier . '>';
	}
}
if (isset($_POST['SearchParts'])) {
	if ($_POST['Keywords'] AND $_POST['StockCode']) {
		prnMsg(_('Stock description keywords have been used in preference to the Stock code extract entered'), 'info');
	}
	if ($_POST['Keywords']) {
		//insert wildcard characters in spaces
		$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';
		$SQL = "SELECT stockmaster.stockid,
				stockmaster.description,
				SUM(locstock.quantity) as qoh,
				stockmaster.units,
				SUM(purchorderdetails.quantityord-purchorderdetails.quantityrecd) AS qord
			FROM stockmaster INNER JOIN locstock
			ON stockmaster.stockid = locstock.stockid INNER JOIN purchorderdetails
			ON stockmaster.stockid=purchorderdetails.itemcode
			WHERE purchorderdetails.completed=1
			AND stockmaster.description LIKE '" . $SearchString ."'
			AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
			GROUP BY stockmaster.stockid,
				stockmaster.description,
				stockmaster.units
			ORDER BY stockmaster.stockid";
	} elseif ($_POST['StockCode']) {
		$SQL = "SELECT stockmaster.stockid,
				stockmaster.description,
				SUM(locstock.quantity) AS qoh,
				SUM(purchorderdetails.quantityord-purchorderdetails.quantityrecd) AS qord,
				stockmaster.units
			FROM stockmaster INNER JOIN locstock
				ON stockmaster.stockid = locstock.stockid
				INNER JOIN purchorderdetails ON stockmaster.stockid=purchorderdetails.itemcode
			WHERE purchorderdetails.completed=1
			AND stockmaster.stockid LIKE '%" . $_POST['StockCode'] . "%'
			AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
			GROUP BY stockmaster.stockid,
				stockmaster.description,
				stockmaster.units
			ORDER BY stockmaster.stockid";
	} elseif (!$_POST['StockCode'] AND !$_POST['Keywords']) {
		$SQL = "SELECT stockmaster.stockid,
				stockmaster.description,
				SUM(locstock.quantity) AS qoh,
				stockmaster.units,
				SUM(purchorderdetails.quantityord-purchorderdetails.quantityrecd) AS qord
			FROM stockmaster INNER JOIN locstock ON stockmaster.stockid = locstock.stockid
				INNER JOIN purchorderdetails ON stockmaster.stockid=purchorderdetails.itemcode
			WHERE purchorderdetails.completed=1
			AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
			GROUP BY stockmaster.stockid,
				stockmaster.description,
				stockmaster.units
			ORDER BY stockmaster.stockid";
	}
	$ErrMsg = _('No stock items were returned by the SQL because');
	$DbgMsg = _('The SQL used to retrieve the searched parts was');
	$StockItemsResult = DB_query($SQL, $db, $ErrMsg, $DbgMsg);
}
/* Not appropriate really to restrict search by date since user may miss older
* ouststanding orders
* $OrdersAfterDate = Date("d/m/Y",Mktime(0,0,0,Date("m")-2,Date("d"),Date("Y")));
*/
if (!isset($OrderNumber) or $OrderNumber == "") {
	echo '<table class=selection><tr><td>';
	if (isset($SelectedStockItem)) {
		echo _('For the part') . ':<b>' . $SelectedStockItem . '</b> ' . _('and') . ' <input type=hidden name="SelectedStockItem" value="' . $SelectedStockItem . '">';
	}
	echo _('Order Number') . ': <input type=text name="OrderNumber" maxlength=8 size=9> ' . _('Into Stock Location') . ':<select name="StockLocation"> ';
	$sql = "SELECT loccode, locationname FROM locations";
	$resultStkLocs = DB_query($sql, $db);
	while ($myrow = DB_fetch_array($resultStkLocs)) {
		if (isset($_POST['StockLocation'])) {
			if ($myrow['loccode'] == $_POST['StockLocation']) {
				echo '<option selected Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
			} else {
				echo '<option Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
			}
		} elseif ($myrow['loccode'] == $_SESSION['UserStockLocation']) {
			echo '<option selected Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
		} else {
			echo '<option Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
		}
	}
	echo '</select> ' . _('Order Status:') .' <select name="Status">';
 	if (!isset($_POST['Status']) OR $_POST['Status']=='Pending_Authorised'){
		echo '<option selected value="Pending_Authorised">' . _('Pending and Authorised') . '</option>';
	} else {
		echo '<option value="Pending_Authorised">' . _('Pending and Authorised') . '</option>';
	}
	if ($_POST['Status']=='Pending'){
		echo '<option selected value="Pending">' . _('Pending') . '</option>';
	} else {
		echo '<option value="Pending">' . _('Pending') . '</option>';
	}
 	if ($_POST['Status']=='Authorised'){
		echo '<option selected value="Authorised">' . _('Authorised') . '</option>';
	} else {
		echo '<option value="Authorised">' . _('Authorised') . '</option>';
	}
	if ($_POST['Status']=='Cancelled'){
		echo '<option selected value="Cancelled">' . _('Cancelled') . '</option>';
	} else {
		echo '<option value="Cancelled">' . _('Cancelled') . '</option>';
	}
	if ($_POST['Status']=='Rejected'){
		echo '<option selected value="Rejected">' . _('Rejected') . '</option>';
	} else {
		echo '<option value="Rejected">' . _('Rejected') . '</option>';
	}
 	echo '</select> <input type=submit name="SearchOrders" value="' . _('Search Purchase Orders') . '"></td></tr></table>';
}
$SQL = "SELECT categoryid, categorydescription FROM stockcategory ORDER BY categorydescription";
$result1 = DB_query($SQL, $db);
echo '<br><br><table class=selection><tr><td>';
echo '<font size=1>' . _('To search for purchase orders for a specific part use the part selection facilities below') . '</font>';
echo '<tr><td><font size=1>' . _('Select a stock category') . ':</font><select name="StockCat">';
while ($myrow1 = DB_fetch_array($result1)) {
	if (isset($_POST['StockCat']) and $myrow1['categoryid'] == $_POST['StockCat']) {
		echo "<option selected value='" . $myrow1['categoryid'] . "'>" . $myrow1['categorydescription'];
	} else {
		echo "<option value='" . $myrow1['categoryid'] . "'>" . $myrow1['categorydescription'];
	}
}
echo '</select><td><font size=1>' . _('Enter text extracts in the') . ' <b>' . _('description') . '</b>:</font></td>';
echo '<td><input type="Text" name="Keywords" size=20 maxlength=25></td></tr><tr><td></td>';
echo '<td><font size=3><b>' . _('OR') . ' </b></font><font size=1>' . _('Enter extract of the') . '<b>' . _('Stock Code') . '</b>:</font></td>';
echo '<td><input type="text" name="StockCode" size=15 maxlength=18></td></tr>';
echo '<tr><td colspan=3><div class=centre><input type=submit name="SearchParts" value="' . _('Search Parts Now') . '">';
echo '<input type=submit name="ResetPart" value="' . _('Show All') . '"></div></td></tr>';
echo '</table><br><br>';
if (isset($StockItemsResult)) {
	echo '<table cellpadding=2 colspan=7 class=selection>';
	$TableHeader = '<tr><td class="tableheader">' . _('Code') . '</td>
				<td class="tableheader">' . _('Description') . '</td>
				<td class="tableheader">' . _('On Hand') . '</td>
				<td class="tableheader">' . _('Orders') . '<br>' . _('Outstanding') . '</td>
				<td class="tableheader">' . _('Units') . '</td>
			</tr>';
	echo $TableHeader;
	$j = 1;
	$k = 0; //row colour counter
	while ($myrow = DB_fetch_array($StockItemsResult)) {
		if ($k == 1) {
			echo '<tr bgcolor="#CCCCCC">';
			$k = 0;
		} else {
			echo '<tr bgcolor="#EEEEEE">';
			$k = 1;
		}
		echo "<td><input type=submit name='SelectedStockItem' value='" . $myrow['stockid'] . "'</td>
				<td>" . $myrow['description'] . "</td>
			<td class=number>" . $myrow['qoh'] . "</td>
			<td class=number>" . $myrow['qord'] . "</td>
			<td>" . $myrow['units'] . "</td>
			</tr>";
		$j++;
		if ($j == 12) {
			$j = 1;
			echo $TableHeader;
		}
		//end of page full new headings if

	}
	//end of while loop
	echo '</table>';
}
//end if stock search results to show
else {
	//figure out the SQL required from the inputs available
	
	if (!isset($_POST['Status']) OR $_POST['Status']=='Pending_Authorised'){
		$StatusCriteria = " AND (purchorders.status='Pending' OR purchorders.status='Authorised' OR purchorders.status='Printed') ";
	}elseif ($_POST['Status']=='Authorised'){
		$StatusCriteria = " AND (purchorders.status='Authorised' OR purchorders.status='Printed')";
	}elseif ($_POST['Status']=='Pending'){
		$StatusCriteria = " AND purchorders.status='Pending' ";
	}elseif ($_POST['Status']=='Rejected'){
		$StatusCriteria = " AND purchorders.status='Rejected' ";
	}elseif ($_POST['Status']=='Cancelled'){
		$StatusCriteria = " AND purchorders.status='Cancelled' ";
	}
	
	if (isset($OrderNumber) && $OrderNumber != "") {
		$SQL = "SELECT purchorders.orderno,
										suppliers.suppname,
										purchorders.orddate,
										purchorders.initiator,
										purchorders.requisitionno,
										purchorders.allowprint,
										purchorders.status,
										suppliers.currcode,
										SUM(purchorderdetails.unitprice*purchorderdetails.quantityord) AS ordervalue
									FROM purchorders,
										purchorderdetails,
										suppliers
									WHERE purchorders.orderno = purchorderdetails.orderno
									AND purchorders.supplierno = suppliers.supplierid
									AND purchorders.orderno='" . $OrderNumber . "'
									GROUP BY purchorders.orderno";
	} else {
		/* $DateAfterCriteria = FormatDateforSQL($OrdersAfterDate); */
		if (empty($_POST['StockLocation'])) {
			$_POST['StockLocation'] = '';
		}
		if (isset($SelectedSupplier)) {
			if (isset($SelectedStockItem)) {
				$SQL = "SELECT purchorders.orderno,
												suppliers.suppname,
												purchorders.orddate,
												purchorders.initiator,
												purchorders.requisitionno,
												purchorders.allowprint,
												purchorders.status,
												suppliers.currcode,
												SUM(purchorderdetails.unitprice*purchorderdetails.quantityord) AS ordervalue
											FROM purchorders,
												purchorderdetails,
												suppliers
											WHERE purchorders.orderno = purchorderdetails.orderno
											AND purchorders.supplierno = suppliers.supplierid
											AND  purchorderdetails.itemcode='" . $SelectedStockItem . "'
											AND purchorders.supplierno='" . $SelectedSupplier . "'
											AND purchorders.intostocklocation = '" . $_POST['StockLocation'] . "' 
											" . $StatusCriteria . "
											GROUP BY purchorders.orderno,
												suppliers.suppname,
												purchorders.orddate,
												purchorders.initiator,
												purchorders.requisitionno,
												purchorders.allowprint,
												suppliers.currcode";
			} else {
				$SQL = "SELECT purchorders.orderno,
												suppliers.suppname,
												purchorders.orddate,
												purchorders.initiator,
												purchorders.requisitionno,
												purchorders.allowprint,
												purchorders.status,
												suppliers.currcode,
												SUM(purchorderdetails.unitprice*purchorderdetails.quantityord) AS ordervalue
											FROM purchorders,
												purchorderdetails,
												suppliers
											WHERE purchorders.orderno = purchorderdetails.orderno
											AND purchorders.supplierno = suppliers.supplierid
											AND purchorders.supplierno='" . $SelectedSupplier . "'
											AND purchorders.intostocklocation = '" . $_POST['StockLocation'] . "' 
											" . $StatusCriteria . "
											GROUP BY purchorders.orderno,
												suppliers.suppname,
												purchorders.orddate,
												purchorders.initiator,
												purchorders.requisitionno,
												purchorders.allowprint,
												suppliers.currcode";
			}
		} else { //no supplier selected
			if (isset($SelectedStockItem)) {
				$SQL = "SELECT purchorders.orderno,
												suppliers.suppname,
												purchorders.orddate,
												purchorders.initiator,
												purchorders.requisitionno,
												purchorders.allowprint,
												purchorders.status,
												suppliers.currcode,
												SUM(purchorderdetails.unitprice*purchorderdetails.quantityord) AS ordervalue
											FROM purchorders,
												purchorderdetails,
												suppliers
											WHERE purchorders.orderno = purchorderdetails.orderno
											AND purchorders.supplierno = suppliers.supplierid
											AND purchorderdetails.itemcode='" . $SelectedStockItem . "'
											AND purchorders.intostocklocation = '" . $_POST['StockLocation'] . "' 
											" . $StatusCriteria . "
											GROUP BY purchorders.orderno,
												suppliers.suppname,
												purchorders.orddate,
												purchorders.initiator,
												purchorders.requisitionno,
												purchorders.allowprint,
												suppliers.currcode";
			} else {
				$SQL = "SELECT purchorders.orderno,
												suppliers.suppname,
												purchorders.orddate,
												purchorders.initiator,
												purchorders.requisitionno,
												purchorders.allowprint,
												purchorders.status,
												suppliers.currcode,
												sum(purchorderdetails.unitprice*purchorderdetails.quantityord) as ordervalue
											FROM purchorders,
												purchorderdetails,
												suppliers
											WHERE purchorders.orderno = purchorderdetails.orderno
											AND purchorders.supplierno = suppliers.supplierid
											AND purchorders.intostocklocation = '" . $_POST['StockLocation'] . "' 
											" . $StatusCriteria . "
											GROUP BY purchorders.orderno,
												suppliers.suppname,
												purchorders.orddate,
												purchorders.initiator,
												purchorders.requisitionno,
												purchorders.allowprint,
												suppliers.currcode";
			}
		} //end selected supplier

	} //end not order number selected
	$ErrMsg = _('No orders were returned by the SQL because');
	$PurchOrdersResult = DB_query($SQL, $db, $ErrMsg);
	
	if (DB_num_rows($PurchOrdersResult) > 0) {
		/*show a table of the orders returned by the SQL */
		echo '<table cellpadding=2 colspan=7 width=90% class=selection>';
		$TableHeader = '<tr><th>' . _('View') . '</th>
				<th>' . _('Supplier') . '</th>
				<th>' . _('Currency') . '</th>
				<th>' . _('Requisition') . '</th>
				<th>' . _('Order Date') . '</th>
				<th>' . _('Initiator') . '</th>
				<th>' . _('Order Total') . '</th>
				<th>' . _('Status') . '</th>
				</tr>';
		echo $TableHeader;
		$j = 1;
		$k = 0; //row colour counter
		while ($myrow = DB_fetch_array($PurchOrdersResult)) {
			if ($k == 1) { /*alternate bgcolour of row for highlighting */
				echo '<tr bgcolor="#CCCCCC">';
				$k = 0;
			} else {
				echo '<tr bgcolor="#EEEEEE">';
				$k++;
			}
			$ViewPurchOrder = $rootpath . '/PO_OrderDetails.php?' . SID . 'OrderNo=' . $myrow['orderno'];
			$FormatedOrderDate = ConvertSQLDate($myrow['orddate']);
			$FormatedOrderValue = number_format($myrow['ordervalue'], 2);
			/*						  View					   Supplier					Currency			   Requisition			 Order Date				 Initiator				Order Total
			ModifyPage, $myrow["orderno"],		  $myrow["suppname"],			$myrow["currcode"],		 $myrow["requisitionno"]		$FormatedOrderDate,			 $myrow["initiator"]			 $FormatedOrderValue 			Order Status*/
			echo "<td><a href='" . $ViewPurchOrder . "'>" . $myrow['orderno'] . "</a></td>
					<td>" . $myrow['suppname'] . "</td>
				<td>" . $myrow['currcode'] . "</td>
				<td>" . $myrow['requisitionno'] . "</td>
				<td>" . $FormatedOrderDate . "</td>
				<td>" . $myrow['initiator'] . "</td>
				<td class=number>" . $FormatedOrderValue . "</td>
				<td>" . _($myrow['status']) .  "</td> 
				</tr>";
				//$myrow['status'] is a string which has gettext translations from PO_Header.php script
				
			$j++;
			if ($j == 12) {
				$j = 1;
				echo $TableHeader;
			}
			//end of page full new headings if
		}
		//end of while loop
		echo '</table>';
	} // end if purchase orders to show
}
echo '</form>';
include ('includes/footer.inc');
?>
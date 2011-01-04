<?php

/* $Id$*/

/* $Revision: 1.21 $ */

//$PageSecurity = 2;
$PricesSecurity = 12;

include('includes/session.inc');

$title = _('Search Outstanding Purchase Orders');

include('includes/header.inc');
include('includes/DefinePOClass.php');

if (isset($_GET['SelectedStockItem'])){
	$SelectedStockItem=trim($_GET['SelectedStockItem']);
} elseif (isset($_POST['SelectedStockItem'])){
	$SelectedStockItem=trim($_POST['SelectedStockItem']);
}

if (isset($_GET['OrderNumber'])){
	$OrderNumber=trim($_GET['OrderNumber']);
} elseif (isset($_POST['OrderNumber'])){
	$OrderNumber=trim($_POST['OrderNumber']);
}

if (isset($_GET['SelectedSupplier'])){
	$SelectedSupplier=trim($_GET['SelectedSupplier']);
} elseif (isset($_POST['SelectedSupplier'])){
	$SelectedSupplier=trim($_POST['SelectedSupplier']);
}

echo '<form action="' . $_SERVER['PHP_SELF'] . '?' . SID . '" method=post>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';


if (isset($_POST['ResetPart'])){
	 unset($SelectedStockItem);
}

if (isset($OrderNumber) && $OrderNumber!='') {
	if (!is_numeric($OrderNumber)){
		echo '<br><b>' . _('The Order Number entered') . ' <U>' . _('MUST') . '</U> ' . _('be numeric') . '.</b><br>';
		unset ($OrderNumber);
	} else {
		echo _('Order Number') . ' - ' . $OrderNumber;
	}
} else {
	if (isset($SelectedSupplier)) {
		echo '<br><div class="page_help_text">' . _('For supplier') . ': ' . $SelectedSupplier . ' ' . _('and') . ' ';
		echo '<input type=hidden name="SelectedSupplier" value="' . $SelectedSupplier . '"></div>';
	}
	if (isset($SelectedStockItem)) {
		 echo '<input type=hidden name="SelectedStockItem" value="' . $SelectedStockItem . '">';
	}
}

if (isset($_POST['SearchParts'])) {
	
	
	if (isset($_POST['Keywords']) AND isset($_POST['StockCode'])) {
		echo '<div class="page_help_text">' ._('Stock description keywords have been used in preference to the Stock code extract entered') . '.</div>';
	}
	If ($_POST['Keywords']) {
		//insert wildcard characters in spaces
		$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';

		$SQL = "SELECT stockmaster.stockid,
										stockmaster.description,
										SUM(locstock.quantity) AS qoh,
										stockmaster.units,
										SUM(purchorderdetails.quantityord-purchorderdetails.quantityrecd) AS qord
									FROM stockmaster INNER JOIN locstock
										ON stockmaster.stockid = locstock.stockid
										INNER JOIN purchorderdetails
											ON stockmaster.stockid=purchorderdetails.itemcode
									WHERE purchorderdetails.completed=0
									AND stockmaster.description LIKE " . $SearchString ."
									AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
									GROUP BY stockmaster.stockid,
										stockmaster.description,
										stockmaster.units
									ORDER BY stockmaster.stockid";


	 } elseif ($_POST['StockCode']){
		$SQL = "SELECT stockmaster.stockid,
				stockmaster.description,
				SUM(locstock.quantity) AS qoh,
				SUM(purchorderdetails.quantityord-purchorderdetails.quantityrecd) AS qord,
				stockmaster.units
			FROM stockmaster INNER JOIN locstock
				ON stockmaster.stockid = locstock.stockid
				INNER JOIN purchorderdetails
					ON stockmaster.stockid=purchorderdetails.itemcode
			WHERE purchorderdetails.completed=0
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
			FROM stockmaster INNER JOIN locstock
				ON stockmaster.stockid = locstock.stockid
				INNER JOIN purchorderdetails
					ON stockmaster.stockid=purchorderdetails.itemcode
			WHERE purchorderdetails.completed=0
			AND stockmaster.categoryid='" . $_POST['StockCat'] . "' 
			GROUP BY stockmaster.stockid,
				stockmaster.description,
				stockmaster.units
			ORDER BY stockmaster.stockid";
	 }

	$ErrMsg = _('No stock items were returned by the SQL because');
	$DbgMsg = _('The SQL used to retrieve the searched parts was');
	$StockItemsResult = DB_query($SQL,$db, $ErrMsg, $DbgMsg);
}


/* Not appropriate really to restrict search by date since user may miss older ouststanding orders
	$OrdersAfterDate = Date("d/m/Y",Mktime(0,0,0,Date("m")-2,Date("d"),Date("Y")));
*/

if (!isset($OrderNumber) or $OrderNumber=='' ){
	echo '<a href="' . $rootpath . '/PO_Header.php?' .SID . '&NewOrder=Yes">' . _('Add Purchase Order') . '</a>';
	echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/magnifier.png" title="' . _('Search') . '" alt="">' . ' ' . $title.'</p>';
	echo '<table class=selection><tr><td>'._('Order Number') . ': <input type=text name="OrderNumber" MAXLENGTH =8 size=9>  ' . _('Into Stock Location') . ':<select name="StockLocation"> ';
	$sql = 'SELECT loccode, locationname FROM locations';
	$resultStkLocs = DB_query($sql,$db);
	while ($myrow=DB_fetch_array($resultStkLocs)){
		if (isset($_POST['StockLocation'])){
			if ($myrow['loccode'] == $_POST['StockLocation']){
				echo '<option selected Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
			} else {
				echo '<option Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
			}
		} elseif ($myrow['loccode']== $_SESSION['UserStockLocation']){
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

$SQL="SELECT categoryid, categorydescription FROM stockcategory ORDER BY categorydescription";
$result1 = DB_query($SQL,$db);

echo "<br><font size=1><div class='page_help_text'>" ._('To search for purchase orders for a specific part use the part selection facilities below')
		."</div> </font>";
echo "<br><table class=selection><tr>";

echo "<td><font size=1>" . _('Select a stock category') . ":</font><select name='StockCat'>";

while ($myrow1 = DB_fetch_array($result1)) {
	if (isset($_POST['StockCat']) and $myrow1['categoryid']==$_POST['StockCat']){
		echo "<option selected value='". $myrow1['categoryid'] . "'>" . $myrow1['categorydescription'];
	} else {
		echo "<option value='". $myrow1['categoryid'] . "'>" . $myrow1['categorydescription'];
	}
}
echo "</select>";
echo "<td><font size=1>" . _('Enter text extracts in the') . "<b>" . _('description') . "</b>:</font></td>";
echo '<td><input type="Text" name="Keywords" size=20 maxlength=25></td></tr><tr><td></td>';
echo "<td><font size<b>" . _('OR') . "</b></font><font size=1>" .  _('Enter extract of the') .  "<b>" .  _('Stock Code') . "</b>:</font></td>";
echo '<td><input type="Text" name="StockCode" size=15 maxlength=18></td></tr></table><br>';
echo '<table><tr><td><input type=submit name="SearchParts" value="' . _('Search Parts Now') . '">';
echo '<input type=submit name="ResetPart" value="' . _('Show All') . '"></td></tr></table>';

echo "<br>";

if (isset($StockItemsResult)) {

	echo '<table cellpadding=2 colspan=7 class=selection>';
	$TableHeader = 	'<tr><th>' . _('Code') . '</th>
			<th>' . _('Description') . '</th>
			<th>' . _('On Hand') . '</th>
			<th>' . _('Orders') . '<br>' . _('Outstanding') . '</th>
			<th>' . _('Units') . '</th>
			</tr>';
	echo $TableHeader;
	$j = 1;
	$k=0; //row colour counter

	while ($myrow=DB_fetch_array($StockItemsResult)) {

		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}

		printf("<td><input type=submit name='SelectedStockItem' VALUE='%s'</td>
				<td>%s</td>
			<td class=number>%s</td>
			<td class=number>%s</td>
			<td>%s</td></tr>",
			$myrow['stockid'],
			$myrow['description'],
			$myrow['qoh'],
			$myrow['qord'],
			$myrow['units']);

		$j++;
		If ($j == 12){
			$j=1;
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

	if (isset($OrderNumber) && $OrderNumber !='') {
		$SQL = "SELECT purchorders.orderno,
										suppliers.suppname,
										purchorders.orddate,
										purchorders.initiator,
										purchorders.status,
										purchorders.requisitionno,
										purchorders.allowprint,
										suppliers.currcode,
										SUM(purchorderdetails.unitprice*purchorderdetails.quantityord) AS ordervalue
						FROM purchorders,
							purchorderdetails,
							suppliers
						WHERE purchorders.orderno = purchorderdetails.orderno
						AND purchorders.supplierno = suppliers.supplierid
						AND purchorderdetails.completed=0
						AND purchorders.orderno='". $OrderNumber ."'
						GROUP BY purchorders.orderno ASC,
										suppliers.suppname,
										purchorders.orddate,
										purchorders.status,
										purchorders.initiator,
										purchorders.requisitionno,
										purchorders.allowprint,
										suppliers.currcode";
	} else {

		  /* $DateAfterCriteria = FormatDateforSQL($OrdersAfterDate); */

		if (isset($SelectedSupplier)) {

			if (!isset($_POST['StockLocation'])) {
				$_POST['StockLocation']='';
			}

			if (isset($SelectedStockItem)) {
				$SQL = "SELECT purchorders.realorderno,
												purchorders.orderno,
												suppliers.suppname,
												purchorders.orddate,
												purchorders.status,
												purchorders.initiator,
												purchorders.requisitionno,
												purchorders.allowprint,
												suppliers.currcode,
												SUM(purchorderdetails.unitprice*purchorderdetails.quantityord) AS ordervalue
								FROM purchorders,
									purchorderdetails,
									suppliers
								WHERE purchorders.orderno = purchorderdetails.orderno
								AND purchorders.supplierno = suppliers.supplierid
								AND purchorderdetails.completed=0
								AND purchorderdetails.itemcode='". $SelectedStockItem ."'
								AND purchorders.supplierno='" . $SelectedSupplier ."'
								AND purchorders.intostocklocation = '". $_POST['StockLocation'] . "' 
								" . $StatusCriteria . "
								GROUP BY purchorders.orderno ASC,
												purchorders.realorderno,
												suppliers.suppname,
												purchorders.orddate,
												purchorders.status,
												purchorders.initiator,
												purchorders.requisitionno,
												purchorders.allowprint,
												suppliers.currcode";
			} else {
				$SQL = "SELECT purchorders.realorderno,
												purchorders.orderno,
												suppliers.suppname,
												purchorders.orddate,
												purchorders.status,
												purchorders.initiator,
												purchorders.requisitionno,
												purchorders.allowprint,
												suppliers.currcode,
												SUM(purchorderdetails.unitprice*purchorderdetails.quantityord) AS ordervalue
								FROM purchorders,
									purchorderdetails,
									suppliers
								WHERE purchorders.orderno = purchorderdetails.orderno
								AND purchorders.supplierno = suppliers.supplierid
								AND purchorderdetails.completed=0
								AND purchorders.supplierno='" . $SelectedSupplier ."'
								AND purchorders.intostocklocation = '". $_POST['StockLocation'] . "' 
								" . $StatusCriteria . "
								GROUP BY purchorders.orderno ASC,
												purchorders.realorderno,
												suppliers.suppname,
												purchorders.orddate,
												purchorders.status,
												purchorders.initiator,
												purchorders.requisitionno,
												purchorders.allowprint,
												suppliers.currcode";
			}
		} else { //no supplier selected
			if (!isset($_POST['StockLocation'])) {$_POST['StockLocation']='';}
			if (isset($SelectedStockItem) and isset($_POST['StockLocation'])) {
				$SQL = "SELECT purchorders.realorderno,
												purchorders.orderno,
												suppliers.suppname,
												purchorders.orddate,
												purchorders.status,
												purchorders.initiator,
												purchorders.requisitionno,
												purchorders.allowprint,
												suppliers.currcode,
												SUM(purchorderdetails.unitprice*purchorderdetails.quantityord) AS ordervalue
								FROM purchorders,
									purchorderdetails,
									suppliers
								WHERE purchorders.orderno = purchorderdetails.orderno
								AND purchorders.supplierno = suppliers.supplierid
								AND purchorderdetails.completed=0
								AND purchorderdetails.itemcode='". $SelectedStockItem ."'
								AND purchorders.intostocklocation = '". $_POST['StockLocation'] . "' 
								" . $StatusCriteria . "
								GROUP BY purchorders.orderno ASC,
												purchorders.realorderno,
												suppliers.suppname,
												purchorders.orddate,
												purchorders.status,
												purchorders.initiator,
												purchorders.requisitionno,
												purchorders.allowprint,
												suppliers.currcode";
			} else {
				$SQL = "SELECT purchorders.realorderno,
												purchorders.orderno,
												suppliers.suppname,
												purchorders.orddate,
												purchorders.status,
												purchorders.initiator,
												purchorders.requisitionno,
												purchorders.allowprint,
												suppliers.currcode,
												SUM(purchorderdetails.unitprice*purchorderdetails.quantityord) AS ordervalue
								FROM purchorders,
									purchorderdetails,
									suppliers
								WHERE purchorders.orderno = purchorderdetails.orderno
								AND purchorders.supplierno = suppliers.supplierid
								AND purchorderdetails.completed=0
								AND purchorders.intostocklocation = '". $_POST['StockLocation'] . "' 
								" . $StatusCriteria . "
								GROUP BY purchorders.orderno ASC,
												purchorders.realorderno,
												suppliers.suppname,
												purchorders.orddate,
												purchorders.status,
												purchorders.initiator,
												purchorders.requisitionno,
												purchorders.allowprint,
												suppliers.currcode";
			}

		} //end selected supplier
	} //end not order number selected

	$ErrMsg = _('No orders were returned by the SQL because');
	$PurchOrdersResult = DB_query($SQL,$db,$ErrMsg);

	/*show a table of the orders returned by the SQL */

	echo '<table cellpadding=2 colspan=7 width=97% class=selection>';

//				   '</td><td class="tableheader">' . _('Receive') .

	echo '<tr><th>' . _('Order #') .
			'</th><th>' . _('Order Date') .
			'</th><th>' . _('Initiated by') .
			'</th><th>' . _('Supplier') .
			'</th><th>' . _('Currency') .
			'</th>';
	if (in_array($PricesSecurity, $_SESSION['AllowedPageSecurityTokens']) OR !isset($PricesSecurity)) {
		echo '<th>' . _('Order Total') .'</th>';
	}
	echo '<th>' . _('Status') .
			'</th><th>' . _('Modify') .
			'</th><th>' . _('Print') .
			'</th><th>' . _('Receive') .
	'</th></tr>';
	$j = 1;
	$k=0; //row colour counter
	while ($myrow=DB_fetch_array($PurchOrdersResult)) {


		if ($k==1){ /*alternate bgcolour of row for highlighting */
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k++;
		}

		$ModifyPage = $rootpath . '/PO_Header.php?' . SID . '&ModifyOrderNumber=' . $myrow['orderno'];
		if ($myrow['status'] == 'Printed') {
			$ReceiveOrder = '<a href="'.$rootpath . '/GoodsReceived.php?' . SID . '&PONumber=' . $myrow['orderno'].'">'.
				_('Receive').'</a>';
		} else {
			$ReceiveOrder = _('Receive');
		}
		if ($myrow['allowprint'] == 1){
			$PrintPurchOrder = '<a target="_blank" href="' . $rootpath . '/PO_PDFPurchOrder.php?' . SID . '&OrderNo=' . $myrow['orderno'] . '">' . _('Print Now') . '</a>';
		} else {
// not open yet
//			$PrintPurchOrder = '<font color=GREY>' . _('Printed') . '</font>';

		}
		if ($myrow['status'] == 'Authorisied') {
			$PrintPurchOrder = '<a target="_blank" href="' . $rootpath . '/PO_PDFPurchOrder.php?' . SID . '&OrderNo=' . $myrow['orderno'] . '&realorderno=' . $myrow['realorderno'] . '&ViewingOnly=2">
				' . _('Print') . '
				</a>';
		} else {
			$PrintPurchOrder = _('Printed');
		}

		$PrintPurchOrder2 = '<a target="_blank" href="' . $rootpath . '/PO_PDFPurchOrder.php?' . SID . '&OrderNo=' . $myrow['orderno'] . '&realorderno=' . $myrow['realorderno'] . '&ViewingOnly=1">' . _('Show') . '</a>';

		$s2 = '<a target="_blank" href="' . $rootpath . '/PO_PDFPurchOrder.php?' . SID . '&OrderNo=' . $myrow['orderno'] . '&realorderno=' . $myrow['realorderno'] . '&ViewingOnly=1">' . $myrow['realorderno']. '</a>';

		$FormatedOrderDate = ConvertSQLDate($myrow['orddate']);
		$FormatedOrderValue = number_format($myrow['ordervalue'],2);

		echo '<td>' . $myrow['orderno'] . '</td>
					<td>' . $FormatedOrderDate . '</td>
					<td>' . $myrow['initiator'] . '</td>
					<td>' . $myrow['suppname'] . '</td>
					<td>' . $myrow['currcode'] . '</td>';
		if (in_array($PricesSecurity, $_SESSION['AllowedPageSecurityTokens']) OR !isset($PricesSecurity)) {
			echo "<td class=number>".$FormatedOrderValue."</td>";
		}
			echo '<td>'._($myrow['status']).'</td>
						<td><a href="'.$ModifyPage.'">' . _('Modify') . '</a></td>
						<td>'.$PrintPurchOrder.'</td>
						<td>'.$ReceiveOrder.'</td>
						</tr>';
	//end of page full new headings if
	}
	//end of while loop

	echo '</table>';
}
echo '<script>defaultControl(document.forms[0].StockCode);</script>';
echo '</form>';
include('includes/footer.inc');
?>
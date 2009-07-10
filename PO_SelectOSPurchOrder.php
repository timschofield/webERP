<?php

/* $Revision: 1.20 $ */

$PageSecurity = 2;

include('includes/session.inc');

$title = _('Search Outstanding Purchase Orders');

include('includes/header.inc');


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
	$completed='purchorderdetails.completed=0';

	if (isset($_POST['Keywords']) AND isset($_POST['StockCode'])) {
		echo '<div class="page_help_text">' ._('Stock description keywords have been used in preference to the Stock code extract entered') . '.</div>';
	}
	If ($_POST['Keywords']) {
		//insert wildcard characters in spaces
//	$completed = "purchorderdetails.completed=0 AND ";
		$i=0;
		$SearchString = '%';
		while (strpos($_POST['Keywords'], ' ', $i)) {
			$wrdlen=strpos($_POST['Keywords'],' ',$i) - $i;
			$SearchString=$SearchString . substr($_POST['Keywords'],$i,$wrdlen) . '%';
			$i=strpos($_POST['Keywords'],' ',$i) +1;
		}
		$SearchString = $SearchString. substr($_POST['Keywords'],$i).'%';

		$SQL = "SELECT stockmaster.stockid, 
				stockmaster.description, 
				SUM(locstock.quantity) AS qoh,  
				stockmaster.units, 
				SUM(purchorderdetails.quantityord-purchorderdetails.quantityrecd) AS qord 
			FROM stockmaster INNER JOIN locstock 
				ON stockmaster.stockid = locstock.stockid 
				INNER JOIN purchorderdetails 
					ON stockmaster.stockid=purchorderdetails.itemcode 
			WHERE $completed
			stockmaster.description " . LIKE . " '$SearchString' 
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
			WHERE $completed
			AND stockmaster.stockid " . LIKE . " '%" . $_POST['StockCode'] . "%' 
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
			WHERE $completed 
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
    echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/magnifier.png" title="' . _('Search') . '" alt="">' . ' ' . $title;
	echo '<br><br>&nbsp;&nbsp;<a href="' . $rootpath . '/PO_Header.php?' .SID . '&NewOrder=Yes">' . _('Add Purchase Order') . '</a>';
    echo '<div class="centre">'._('order number') . ': <input type=text name="OrderNumber" MAXLENGTH =8 size=9>  ' . _('Into Stock Location') . ':<select name="StockLocation"> ';
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

 	echo '</select>  <input type=submit name="SearchOrders" VALUE="' . _('Search Purchase Orders') . '"></div>';
}

$SQL='SELECT categoryid, categorydescription FROM stockcategory ORDER BY categorydescription';
$result1 = DB_query($SQL,$db);

?>

<hr>
<font size=1><div class='page_help_text'><?php echo _('To search for purchase orders for a specific part use the part selection facilities below'); ?></div> </font>
<br><table align="center">
<tr>
<td><font size=1><?php echo _('Select a stock category'); ?>:</font>
<select name="StockCat">
<?php
while ($myrow1 = DB_fetch_array($result1)) {
	if (isset($_POST['StockCat']) and $myrow1['categoryid']==$_POST['StockCat']){
		echo "<option selected VALUE='". $myrow1['categoryid'] . "'>" . $myrow1['categorydescription'];
	} else {
		echo "<option VALUE='". $myrow1['categoryid'] . "'>" . $myrow1['categorydescription'];
	}
}
?>
</select>
<td><font size=1><?php echo _('Enter text extracts in the'); ?>  <b><?php echo _('description'); ?></b>:</font></td>
<td><input type="Text" name="Keywords" size=20 maxlength=25></td></tr>
<tr><td></td>
<td><font SIZE 3><b><?php echo _('OR'); ?> </b></font><font size=1><?php echo _('Enter extract of the'); ?> <b><?php echo _('Stock Code'); ?></b>:</font></td>
<td><input type="Text" name="StockCode" size=15 maxlength=18></td>
</tr>
</table><br>
<table align="center"><tr><td><input type=submit name="SearchParts" VALUE="<?php echo _('Search Parts Now'); ?>">
<input type=submit name="ResetPart" VALUE="<?php echo _('Show All'); ?>"></td></tr></table>

<hr>

<?php

if (isset($StockItemsResult)) {

	echo '<table cellpadding=2 colspan=7 BORDER=2>';
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
			<td align=right>%s</td>
			<td align=right>%s</td>
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
$completed = " AND purchorderdetails.completed=0";
	if (isset($OrderNumber) && $OrderNumber !='') {
		$SQL = 'SELECT purchorders.orderno,
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
			AND purchorders.supplierno = suppliers.supplierid '.
			$completed
			.' AND purchorders.orderno="'. $OrderNumber .'"
			GROUP BY purchorders.orderno ASC,
				suppliers.suppname,
				purchorders.orddate,
				purchorders.status,
				purchorders.initiator,
				purchorders.requisitionno,
				purchorders.allowprint,
				suppliers.currcode';
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
					$completed
					AND purchorderdetails.itemcode='". $SelectedStockItem ."'
					AND purchorders.supplierno='" . $SelectedSupplier ."'
					AND purchorders.intostocklocation = '". $_POST['StockLocation'] . "'
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
					$completed
					AND purchorders.supplierno='" . $SelectedSupplier ."'
					AND purchorders.intostocklocation = '". $_POST['StockLocation'] . "'
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
					$completed
					AND purchorderdetails.itemcode='". $SelectedStockItem ."'
					AND purchorders.intostocklocation = '". $_POST['StockLocation'] . "'
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
					$completed
					AND purchorders.intostocklocation = '". $_POST['StockLocation'] . "'
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

	echo '<table cellpadding=2 colspan=7 WIDTH=100%>';

//	               '</td><td class="tableheader">' . _('Receive') .	
	$TableHeader = '<tr><th>' . _('Order #') .
			'</th><th>' . _('Order Date') .
			'</th><th>' . _('Initiated by') .
			'</th><th>' . _('Supplier') .
			'</th><th>' . _('Currency') .			
			'</th><th>' . _('Order Total') .
			'</th><th>' . _('Status') .
			'</th><th>' . _('Modify') .
			'</th><th>' . _('Print') .
			'</th><th>' . _('Receive') .
	'</th></tr>';

	echo $TableHeader;
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

		$ModifyPage = $rootpath . "/PO_Header.php?" . SID . "&ModifyOrderNumber=" . $myrow["orderno"];
		if ($myrow['status']==_('Printed')) {
			$ReceiveOrder = "<a href='".$rootpath . "/GoodsReceived.php?" . SID . "&PONumber=" . $myrow["orderno"]."'>".
				_('Receive').'</a>';
		} else {
			$ReceiveOrder = "Receive";
		}
		if ($myrow["allowprint"]==1){
			$PrintPurchOrder = '<a target="_blank" href="' . $rootpath . '/PO_PDFPurchOrder.php?' . SID . '&OrderNo=' . $myrow['orderno'] . '">' . _('Print Now') . '</a>';
		} else {
// not open yet
//			$PrintPurchOrder = '<font color=GREY>' . _('Printed') . '</font>';

		}
		if ($myrow['status']==_('Authorised')) {
			$PrintPurchOrder = '<a target="_blank" href="' . $rootpath . '/PO_PDFPurchOrder.php?' . SID . '&OrderNo=' . $myrow['orderno'] . '&realorderno=' . $myrow['realorderno'] . '&ViewingOnly=2">' . _('Print') . '</a>';
		} else {
			$PrintPurchOrder = 'Print';
		}
		$PrintPurchOrder2 = '<a target="_blank" href="' . $rootpath . '/PO_PDFPurchOrder.php?' . SID . '&OrderNo=' . $myrow['orderno'] . '&realorderno=' . $myrow['realorderno'] . '&ViewingOnly=1">' . _('Show') . '</a>';
		$s2 = '<a target="_blank" href="' . $rootpath . '/PO_PDFPurchOrder.php?' . SID . '&OrderNo=' . $myrow['orderno'] . '&realorderno=' . $myrow['realorderno'] . '&ViewingOnly=1">' . $myrow['realorderno']. '</a>';

		$FormatedOrderDate = ConvertSQLDate($myrow['orddate']);
		$FormatedOrderValue = number_format($myrow['ordervalue'],2);

// the tailed two column
//			<td>%s</font></td>
//			<td align=right>%s</font></td>
//			$myrow['requisitionno'],	
//			$myrow['initiator']);
//			'</td><td class="tableheader">' . _('Requisition') .
//			'</td><td class="tableheader">' . _('Initiator') .	               		
//		        <td><a href='%s'>" . _('Receive') . "</a></td>			
		printf("<td>%s</font></td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</font></td>
			<td class=number>%s</font></td>
			<td>%s</td>
			<td><a href='%s'>Modify</a></font></td>
			<td>%s</font></td>
			<td>%s</font></td>
			</tr>",
			$myrow["orderno"],						
			$FormatedOrderDate,			
			$myrow['initiator'],
			$myrow['suppname'],
			$myrow['currcode'],
			$FormatedOrderValue,			
			$myrow['status'],
			$ModifyPage,
			$PrintPurchOrder,
			$ReceiveOrder
			);



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
echo "<script>defaultControl(document.forms[0].StockCode);</script>";
echo '</form>';
include('includes/footer.inc');
?>

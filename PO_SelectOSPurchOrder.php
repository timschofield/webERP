<?php

/* $Revision: 1.12 $ */

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

echo '<FORM ACTION="' . $_SERVER['PHP_SELF'] . '?' . SID . '" METHOD=POST>';


If ($_POST['ResetPart']){
     unset($SelectedStockItem);
}

If (isset($OrderNumber) && $OrderNumber!='') {
	if (!is_numeric($OrderNumber)){
		  echo '<BR><B>' . _('The Order Number entered') . ' <U>' . _('MUST') . '</U> ' . _('be numeric') . '.</B><BR>';
		  unset ($OrderNumber);
	} else {
		echo _('Order Number') . ' - ' . $OrderNumber;
	}
} else {
	If ($SelectedSupplier) {
		echo _('For supplier') . ': ' . $SelectedSupplier . ' ' . _('and') . ' ';
		echo '<input type=hidden name="SelectedSupplier" value=' . $SelectedSupplier . '>';
	}
	If ($SelectedStockItem) {
		 echo _('for the part') . ': ' . $SelectedStockItem . ' ' . _('and') . ' <input type=hidden name="SelectedStockItem" value="' . $SelectedStockItem . '">';
	}
}

if ($_POST['SearchParts']){

	If ($_POST['Keywords'] AND $_POST['StockCode']) {
		echo _('Stock description keywords have been used in preference to the Stock code extract entered') . '.';
	}
	If ($_POST['Keywords']) {
		//insert wildcard characters in spaces
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
			WHERE purchorderdetails.completed=0 
			AND stockmaster.description " . LIKE . " '$SearchString' 
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

if ($OrderNumber=='' OR !isset($OrderNumber)){

	echo _('order number') . ': <INPUT type=text name="OrderNumber" MAXLENGTH =8 SIZE=9>  ' . _('Into Stock Location') . ':<SELECT name="StockLocation"> ';
	$sql = 'SELECT loccode, locationname FROM locations';
	$resultStkLocs = DB_query($sql,$db);
	while ($myrow=DB_fetch_array($resultStkLocs)){
		if (isset($_POST['StockLocation'])){
			if ($myrow['loccode'] == $_POST['StockLocation']){
				echo '<OPTION SELECTED Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
			} else {
				echo '<OPTION Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
			}
		} elseif ($myrow['loccode']== $_SESSION['UserStockLocation']){
			echo '<OPTION SELECTED Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
		} else {
			echo '<OPTION Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
		}
	}

	echo '</SELECT>  <INPUT TYPE=SUBMIT NAME="SearchOrders" VALUE="' . _('Search Purchase Orders') . '">';
	echo '&nbsp;&nbsp;<a href="' . $rootpath . '/PO_Header.php?' .SID . '&NewOrder=Yes">' . _('Add Purchase Order') . '</a>';
}

$SQL='SELECT categoryid, categorydescription FROM stockcategory ORDER BY categorydescription';
$result1 = DB_query($SQL,$db);

?>

<HR>
<FONT SIZE=1><?php echo _('To search for purchase orders for a specific part use the part selection facilities below'); ?> </FONT>
<INPUT TYPE=SUBMIT NAME="SearchParts" VALUE="<?php echo _('Search Parts Now'); ?>">
<INPUT TYPE=SUBMIT NAME="ResetPart" VALUE="<?php echo _('Show All'); ?>">
<TABLE>
<TR>
<TD><FONT SIZE=1><?php echo _('Select a stock category'); ?>:</FONT>
<SELECT NAME="StockCat">
<?php
while ($myrow1 = DB_fetch_array($result1)) {
	if ($myrow1['categoryid']==$_POST['StockCat']){
		echo "<OPTION SELECTED VALUE='". $myrow1['categoryid'] . "'>" . $myrow1['categorydescription'];
	} else {
		echo "<OPTION VALUE='". $myrow1['categoryid'] . "'>" . $myrow1['categorydescription'];
	}
}
?>
</SELECT>
<TD><FONT SIZE=1><?php echo _('Enter text extracts in the'); ?>  <B><?php echo _('description'); ?></B>:</FONT></TD>
<TD><INPUT TYPE="Text" NAME="Keywords" SIZE=20 MAXLENGTH=25></TD></TR>
<TR><TD></TD>
<TD><FONT SIZE 3><B><?php echo _('OR'); ?> </B></FONT><FONT SIZE=1><?php echo _('Enter extract of the'); ?> <B><?php echo _('Stock Code'); ?></B>:</FONT></TD>
<TD><INPUT TYPE="Text" NAME="StockCode" SIZE=15 MAXLENGTH=18></TD>
</TR>
</TABLE>

<HR>

<?php

If ($StockItemsResult) {

	echo '<TABLE CELLPADDING=2 COLSPAN=7 BORDER=2>';
	$TableHeader = 	'<TR><TD class="tableheader">' . _('Code') . '</TD>
			<TD class="tableheader">' . _('Description') . '</TD>
			<TD class="tableheader">' . _('On Hand') . '</TD>
			<TD class="tableheader">' . _('Orders') . '<BR>' . _('Outstanding') . '</TD>
			<TD class="tableheader">' . _('Units') . '</TD>
			</TR>';
	echo $TableHeader;
	$j = 1;
	$k=0; //row colour counter

	while ($myrow=DB_fetch_array($StockItemsResult)) {

		if ($k==1){
			echo '<tr bgcolor="#CCCCCC">';
			$k=0;
		} else {
			echo '<tr bgcolor="#EEEEEE">';
			$k=1;
		}

		printf("<td><INPUT TYPE=SUBMIT NAME='SelectedStockItem' VALUE='%s'</td>
		        <td>%s</td>
			<td ALIGN=RIGHT>%s</td>
			<td ALIGN=RIGHT>%s</td>
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

	echo '</TABLE>';

}
//end if stock search results to show
  else {

	//figure out the SQL required from the inputs available

	if (isset($OrderNumber) && $OrderNumber !='') {
		$SQL = 'SELECT purchorders.orderno,
				suppliers.suppname,
				purchorders.orddate,
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
			AND purchorders.orderno='. $OrderNumber .'
			GROUP BY purchorders.orderno,
				suppliers.suppname,
				purchorders.orddate,
				purchorders.initiator,
				purchorders.requisitionno,
				purchorders.allowprint,
				suppliers.currcode';
	} else {

	      /* $DateAfterCriteria = FormatDateforSQL($OrdersAfterDate); */

		if (isset($SelectedSupplier)) {

			if (isset($SelectedStockItem)) {
				$SQL = "SELECT purchorders.orderno,
						suppliers.suppname,
						purchorders.orddate,
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
						suppliers.currcode,
						SUM(purchorderdetails.unitprice*purchorderdetails.quantityord) AS ordervalue
					FROM purchorders,
						purchorderdetails,
						suppliers
					WHERE purchorders.orderno = purchorderdetails.orderno
					AND purchorders.supplierno = suppliers.supplierid
					AND purchorderdetails.completed=0
					AND purchorders.intostocklocation = '". $_POST['StockLocation'] . "'
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
	$PurchOrdersResult = DB_query($SQL,$db,$ErrMsg);

	/*show a table of the orders returned by the SQL */

	echo '<TABLE CELLPADDING=2 COLSPAN=7 WIDTH=100%>';
	$TableHeader = '<TR><TD class="tableheader">' . _('Modify') .
	               '</TD><TD class="tableheader">' . _('Receive') .
			'</TD><TD class="tableheader">' . _('Print') .
			'</TD><TD class="tableheader">' . _('Supplier') .
			'</TD><TD class="tableheader">' . _('Currency') .
			'</TD><TD class="tableheader">' . _('Requisition') .
			'</TD><TD class="tableheader">' . _('Order Date') .
			'</TD><TD class="tableheader">' . _('Initiator') .
			'</TD><TD class="tableheader">' . _('Order Total') .
			'</TD></TR>';
	echo $TableHeader;
	$j = 1;
	$k=0; //row colour counter
	while ($myrow=DB_fetch_array($PurchOrdersResult)) {


		if ($k==1){ /*alternate bgcolour of row for highlighting */
			echo '<tr bgcolor="#CCCCCC">';
			$k=0;
		} else {
			echo '<tr bgcolor="#EEEEEE">';
			$k++;
		}

		$ModifyPage = $rootpath . "/PO_Header.php?" . SID . "ModifyOrderNumber=" . $myrow["orderno"];
		$ReceiveOrder = $rootpath . "/GoodsReceived.php?" . SID . "PONumber=" . $myrow["orderno"];
		if ($myrow["allowprint"]==1){
			$PrintPurchOrder = '<A target="_blank" HREF="' . $rootpath . '/PO_PDFPurchOrder.php?' . SID . 'OrderNo=' . $myrow['orderno'] . '">' . _('Print Now') . '</A>';
		} else {
			$PrintPurchOrder = '<FONT COLOR=GREY>' . _('Printed') . '</FONT>';
		}
		$FormatedOrderDate = ConvertSQLDate($myrow['orddate']);
		$FormatedOrderValue = number_format($myrow['ordervalue'],2);

		printf("<td><A HREF='%s'>%s</A></FONT></td>
		        <td><A HREF='%s'>" . _('Receive') . "</A></td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</FONT></td>
			<td>%s</FONT></td>
			<td>%s</FONT></td>
			<td>%s</FONT></td>
			<td ALIGN=RIGHT>%s</FONT></td>
			</tr>",
			$ModifyPage,
			$myrow['orderno'],
			$ReceiveOrder,
			$PrintPurchOrder,
			$myrow['suppname'],
			$myrow['currcode'],
			$myrow['requisitionno'],
			$FormatedOrderDate,
			$myrow['initiator'],
			$FormatedOrderValue);

		$j++;
		If ($j == 12){
			$j=1;
			 echo $TableHeader;
		}
	//end of page full new headings if
	}
	//end of while loop

	echo '</TABLE>';
}

echo '</form>';
include('includes/footer.inc');
?>

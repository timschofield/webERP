<?php

/* $Revision: 1.19 $ */

$PageSecurity = 2;

include('includes/session.inc');
$title = _('Search Outstanding Sales Orders');
include('includes/header.inc');

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/sales.png" title="' . _('Sales') . '" alt="">' . ' ' . _('Outstanding Sales Orders') . '</p> ';

echo '<form action=' . $_SERVER['PHP_SELF'] .'?' .SID . ' method=post>';


If (isset($_POST['ResetPart'])){
     unset($_REQUEST['SelectedStockItem']);
}

echo '<p><div class="centre">';

If (isset($_REQUEST['OrderNumber']) AND $_REQUEST['OrderNumber']!='') {
	$_REQUEST['OrderNumber'] = trim($_REQUEST['OrderNumber']);
	if (!is_numeric($_REQUEST['OrderNumber'])){
		  echo '<br><b>' . _('The Order Number entered MUST be numeric') . '</b><br>';
		  unset ($_REQUEST['OrderNumber']);
		  include('includes/footer.inc');
		  exit;
	} else {
		echo _('Order Number') . ' - ' . $_REQUEST['OrderNumber'];
	}
} else {
	If (isset($_REQUEST['SelectedCustomer'])) {
		echo _('For customer') . ': ' . $_REQUEST['SelectedCustomer'] . ' ' . _('and') . ' ';
		echo "<input type=hidden name='SelectedCustomer' value=" . $_REQUEST['SelectedCustomer'] . '>';
	}
	If (isset($_REQUEST['SelectedStockItem'])) {
		 echo _('for the part') . ': ' . $_REQUEST['SelectedStockItem'] . ' ' . _('and') . " <input type=hidden name='SelectedStockItem' value='" . $_REQUEST['SelectedStockItem'] . "'>";
	}
}

if (isset($_POST['SearchParts'])){

	If ($_POST['Keywords'] AND $_POST['StockCode']) {
		echo _('Stock description keywords have been used in preference to the Stock code extract entered');
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
		$SearchString = $SearchString . substr($_POST['Keywords'],$i).'%';

		$SQL = "SELECT stockmaster.stockid,
				stockmaster.description,
				SUM(locstock.quantity) AS qoh,
				stockmaster.units
			FROM stockmaster,
				locstock
			WHERE stockmaster.stockid=locstock.stockid
			AND stockmaster.description " . LIKE . " '" . $SearchString . "'
			AND stockmaster.categoryid='" . $_POST['StockCat']. "'
			GROUP BY stockmaster.stockid,
				stockmaster.description,
				stockmaster.units
			ORDER BY stockmaster.stockid";

	 } elseif (isset($_POST['StockCode'])){
		$SQL = "SELECT stockmaster.stockid,
				stockmaster.description,
				sum(locstock.quantity) as qoh,
				stockmaster.units
			FROM stockmaster,
				locstock
			WHERE stockmaster.stockid=locstock.stockid
			AND stockmaster.stockid " . LIKE . " '%" . $_POST['StockCode'] . "%'
			AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
			GROUP BY stockmaster.stockid,
				stockmaster.description,
				stockmaster.units
			ORDER BY stockmaster.stockid";

	 } elseif (!isset($_POST['StockCode']) AND !isset($_POST['Keywords'])) {
		$SQL = "SELECT stockmaster.stockid,
				stockmaster.description,
				sum(locstock.quantity) as qoh,
				stockmaster.units
			FROM stockmaster,
				locstock
			WHERE stockmaster.stockid=locstock.stockid
			AND stockmaster.categoryid='" . $_POST['StockCat'] ."'
			GROUP BY stockmaster.stockid,
				stockmaster.description,
				stockmaster.units
			ORDER BY stockmaster.stockid";
	 }

	$ErrMsg =  _('No stock items were returned by the SQL because');
	$DbgMsg = _('The SQL used to retrieve the searched parts was');
	$StockItemsResult = DB_query($SQL,$db,$ErrMsg,$DbgMsg);

}

if (isset($_POST['StockID'])){
	$StockID = trim(strtoupper($_POST['StockID']));
} elseif (isset($_GET['StockID'])){
	$StockID = trim(strtoupper($_GET['StockID']));
}

if (!isset($StockID)) {

     /* Not appropriate really to restrict search by date since may miss older
     ouststanding orders
	$OrdersAfterDate = Date('d/m/Y',Mktime(0,0,0,Date('m')-2,Date('d'),Date('Y')));
     */

	if (!isset($_REQUEST['OrderNumber']) or $_REQUEST['OrderNumber']==''){

		echo _('Order number') . ": <input type=text name='OrderNumber' maxlength=8 size=9>&nbsp " . _('From Stock Location') . ":<select name='StockLocation'> ";
		
		$sql = 'SELECT loccode, locationname FROM locations';
		
		$resultStkLocs = DB_query($sql,$db);
		
		while ($myrow=DB_fetch_array($resultStkLocs)){
			if (isset($_POST['StockLocation'])){
				if ($myrow['loccode'] == $_POST['StockLocation']){
				     echo "<option selected Value='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
				} else {
				     echo "<option Value='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
				}
			} elseif ($myrow['loccode']==$_SESSION['UserStockLocation']){
				 echo "<option selected Value='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
			} else {
				 echo "<option Value='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
			}
		}

		echo '</select> &nbsp&nbsp';
		echo '<select name="Quotations">';
		
		if ($_GET['Quotations']=='Quotes_Only'){
			$_POST['Quotations']='Quotes_Only';
		}
		
		if ($_POST['Quotations']=='Quotes_Only'){
			echo '<option selected VALUE="Quotes_Only">' . _('Quotations Only');
			echo '<option VALUE="Orders_Only">' . _('Orders Only');
		} else {
			echo '<option selected VALUE="Orders_Only">' . _('Orders Only');
			echo '<option VALUE="Quotes_Only">' . _('Quotations Only');
		}
		
		echo '</select> &nbsp&nbsp';
		echo "<input type=submit name='SearchOrders' VALUE='" . _('Search') . "'>";
    echo '&nbsp;&nbsp;<a href="' . $rootpath . '/SelectOrderItems.php?' . SID . '&NewOrder=Yes">' . _('Add Sales Order') . '</a>';
	}

	$SQL='SELECT categoryid,
			categorydescription
		FROM stockcategory
		ORDER BY categorydescription';

	$result1 = DB_query($SQL,$db);

	echo '<hr>
		<font size=1>' . _('To search for sales orders for a specific part use the part selection facilities below') . "</font>     <input type=submit name='SearchParts' VALUE='" . _('Search Parts Now') . "'><input type=submit name='ResetPart' VALUE='" . _('Show All') . "'>
      </div><table>
      	<tr>
      		<td><font size=1>" . _('Select a stock category') . ":</font>
      			<select name='StockCat'>";

	while ($myrow1 = DB_fetch_array($result1)) {
		echo "<option VALUE='". $myrow1['categoryid'] . "'>" . $myrow1['categorydescription'];
	}

      echo '</select>
      		<td><font size=1>' . _('Enter text extract(s) in the description') . ":</font></td>
      		<td><input type='Text' name='Keywords' size=20 maxlength=25></td>
	</tr>
      	<tr><td></td>
      		<td><font SIZE 3><b>" . _('OR') . ' </b></font><font size=1>' . _('Enter extract of the Stock Code') . "</b>:</font></td>
      		<td><input type='Text' name='StockCode' size=15 maxlength=18></td>
      	</tr>
      </table>
      <hr>";

If (isset($StockItemsResult)) {

	echo '<table cellpadding=2 colspan=7 BORDER=2>';
	$TableHeader = "<tr>
				<th>" . _('Code') . "</th>
				<th>" . _('Description') . "</th>
				<th>" . _('On Hand') . "</th>
				<th>" . _('Units') . "</th>
			</tr>";
	echo $TableHeader;

	$j = 1;
	$k=0; //row colour counter

	while ($myrow=DB_fetch_array($StockItemsResult)) {

		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k++;
		}

		printf("<td><input type=submit name='SelectedStockItem' VALUE='%s'</td>
			<td>%s</td>
			<td align=right>%s</td>
			<td>%s</td>
			</tr>",
			$myrow['stockid'],
			$myrow['description'],
			$myrow['qoh'],
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
	if (isset($_POST['Quotations']) and $_POST['Quotations']=='Orders_Only'){
		$Quotations = 0;
	} else {
		$Quotations =1;
	}
	if(!isset($_POST['StockLocation'])) {
		$_POST['StockLocation'] = '';
	}
	if (isset($_REQUEST['OrderNumber']) && $_REQUEST['OrderNumber'] !='') {
			$SQL = "SELECT salesorders.orderno,
					debtorsmaster.name,
					custbranch.brname,
					salesorders.customerref,
					salesorders.orddate,
					salesorders.deliverydate,
					salesorders.deliverto,
					salesorders.printedpackingslip,
					SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)) AS ordervalue
				FROM salesorders,
					salesorderdetails,
					debtorsmaster,
					custbranch
				WHERE salesorders.orderno = salesorderdetails.orderno
				AND salesorders.branchcode = custbranch.branchcode
				AND salesorders.debtorno = debtorsmaster.debtorno
				AND debtorsmaster.debtorno = custbranch.debtorno
				AND salesorderdetails.completed=0
				AND salesorders.orderno=". $_REQUEST['OrderNumber'] ."
				AND salesorders.quotation =" .$Quotations . " 
				GROUP BY salesorders.orderno,
					debtorsmaster.name,
					custbranch.brname,
					salesorders.customerref,
					salesorders.orddate,
					salesorders.deliverydate,
					salesorders.deliverto,
					salesorders.printedpackingslip
				ORDER BY salesorders.orderno";
	} else {
	      /* $DateAfterCriteria = FormatDateforSQL($OrdersAfterDate); */

		if (isset($_REQUEST['SelectedCustomer'])) {

			if (isset($_REQUEST['SelectedStockItem'])) {
				$SQL = "SELECT salesorders.orderno,
						debtorsmaster.name,
						custbranch.brname,
						salesorders.customerref,
						salesorders.orddate,
						salesorders.deliverydate,
						salesorders.deliverto,
					  salesorders.printedpackingslip,
						salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent) AS ordervalue
					FROM salesorders,
						salesorderdetails,
						debtorsmaster,
						custbranch
					WHERE salesorders.orderno = salesorderdetails.orderno
					AND salesorders.debtorno = debtorsmaster.debtorno
					AND debtorsmaster.debtorno = custbranch.debtorno
					AND salesorders.branchcode = custbranch.branchcode
					AND salesorderdetails.completed=0
					AND salesorders.quotation =" .$Quotations . "
					AND salesorderdetails.stkcode='". $_REQUEST['SelectedStockItem'] ."'
					AND salesorders.debtorno='" . $_REQUEST['SelectedCustomer'] ."'
					AND salesorders.fromstkloc = '". $_POST['StockLocation'] . "'
					ORDER BY salesorders.orderno";
						

			} else {
				$SQL = "SELECT salesorders.orderno,
						debtorsmaster.name,
						custbranch.brname,
						salesorders.customerref,
						salesorders.orddate,
						salesorders.deliverto,
					  salesorders.printedpackingslip,
						salesorders.deliverydate, SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)) AS ordervalue
					FROM salesorders,
						salesorderdetails,
						debtorsmaster,
						custbranch
					WHERE salesorders.orderno = salesorderdetails.orderno
					AND salesorders.debtorno = debtorsmaster.debtorno
					AND debtorsmaster.debtorno = custbranch.debtorno
					AND salesorders.branchcode = custbranch.branchcode
					AND salesorders.quotation =" .$Quotations . "
					AND salesorderdetails.completed=0
					AND salesorders.debtorno='" . $_REQUEST['SelectedCustomer'] . "'
					AND salesorders.fromstkloc = '". $_POST['StockLocation'] . "'
					GROUP BY salesorders.orderno,
						debtorsmaster.name,
						salesorders.debtorno,
						custbranch.brname,
						salesorders.customerref,
						salesorders.orddate,
						salesorders.deliverto,
						salesorders.deliverydate
					ORDER BY salesorders.orderno";

			}
		} else { //no customer selected
			if (isset($_REQUEST['SelectedStockItem'])) {
				$SQL = "SELECT salesorders.orderno,
						debtorsmaster.name,
						custbranch.brname,
						salesorders.customerref,
						salesorders.orddate,
						salesorders.deliverto,
					  	salesorders.printedpackingslip,
						salesorders.deliverydate, SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)) AS ordervalue
					FROM salesorders,
						salesorderdetails,
						debtorsmaster,
						custbranch
					WHERE salesorders.orderno = salesorderdetails.orderno
					AND salesorders.debtorno = debtorsmaster.debtorno
					AND debtorsmaster.debtorno = custbranch.debtorno
					AND salesorders.branchcode = custbranch.branchcode
					AND salesorderdetails.completed=0
					AND salesorders.quotation =" .$Quotations . "
					AND salesorderdetails.stkcode='". $_REQUEST['SelectedStockItem'] . "'
					AND salesorders.fromstkloc = '". $_POST['StockLocation'] . "'
					GROUP BY salesorders.orderno,
						debtorsmaster.name,
						custbranch.brname,
						salesorders.customerref,
						salesorders.orddate,
						salesorders.deliverto,
						salesorders.deliverydate,
						salesorders.printedpackingslip
					ORDER BY salesorders.orderno";
			} else {
				$SQL = "SELECT salesorders.orderno,
						debtorsmaster.name,
						custbranch.brname,
						salesorders.customerref,
						salesorders.orddate,
						salesorders.deliverto,
						salesorders.deliverydate,
					  salesorders.printedpackingslip,
						SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)) AS ordervalue
					FROM salesorders,
						salesorderdetails,
						debtorsmaster,
						custbranch
					WHERE salesorders.orderno = salesorderdetails.orderno
					AND salesorders.debtorno = debtorsmaster.debtorno
					AND debtorsmaster.debtorno = custbranch.debtorno
					AND salesorders.branchcode = custbranch.branchcode
					AND salesorderdetails.completed=0
					AND salesorders.quotation =" .$Quotations . "
					AND salesorders.fromstkloc = '". $_POST['StockLocation'] . "'
					GROUP BY salesorders.orderno,
						debtorsmaster.name,
						custbranch.brname,
						salesorders.customerref,
						salesorders.orddate,
						salesorders.deliverto,
						salesorders.deliverydate,
						salesorders.printedpackingslip
					ORDER BY salesorders.orderno";
			}

		} //end selected customer
	} //end not order number selected

	$ErrMsg = _('No orders or quotations were returned by the SQL because');
	$SalesOrdersResult = DB_query($SQL,$db,$ErrMsg);

	/*show a table of the orders returned by the SQL */

	echo '<table cellpadding=2 colspan=7 WIDTH=100%>';

	if (isset($_POST['Quotations']) and $_POST['Quotations']=='Orders_Only'){
		$tableheader = "<tr>
				<th>" . _('Modify') . "</th>
				<th>" . _('Invoice') . "</th>
				<th>" . _('Disp. Note') . "</th>
				<th>" . _('Customer') . "</th>
				<th>" . _('Branch') . "</th>
				<th>" . _('Cust Order') . " #</th>
				<th>" . _('Order Date') . "</th>
				<th>" . _('Req Del Date') . "</th>
				<th>" . _('Delivery To') . "</th>
				<th>" . _('Order Total') . "</th></tr>";
	} else {
		$tableheader = "<tr>
				<th>" . _('Modify') . "</th>
				<th>" . _('Print Quote') . "</th>
				<th>" . _('Customer') . "</th>
				<th>" . _('Branch') . "</th>
				<th>" . _('Cust Ref') . " #</th>
				<th>" . _('Quote Date') . "</th>
				<th>" . _('Req Del Date') . "</th>
				<th>" . _('Delivery To') . "</th>
				<th>" . _('Quote Total') . "</th></tr>";
	}
	
	echo $tableheader;

	$j = 1;
	$k=0; //row colour counter
	while ($myrow=DB_fetch_array($SalesOrdersResult)) {


		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k++;
		}

		$ModifyPage = $rootpath . "/SelectOrderItems.php?" . SID . '&ModifyOrderNumber=' . $myrow['orderno'];
		$Confirm_Invoice = $rootpath . '/ConfirmDispatch_Invoice.php?' . SID . '&OrderNumber=' .$myrow['orderno'];
		
		if ($_SESSION['PackNoteFormat']==1){ /*Laser printed A4 default */
			$PrintDispatchNote = $rootpath . '/PrintCustOrder_generic.php?' . SID . '&TransNo=' . $myrow['orderno'];
		} else { /*pre-printed stationery default */
			$PrintDispatchNote = $rootpath . '/PrintCustOrder.php?' . SID . '&TransNo=' . $myrow['orderno'];
		}
		$PrintQuotation = $rootpath . '/PDFQuotation.php?' . SID . '&QuotationNo=' . $myrow['orderno'];
		$FormatedDelDate = ConvertSQLDate($myrow['deliverydate']);
		$FormatedOrderDate = ConvertSQLDate($myrow['orddate']);
		$FormatedOrderValue = number_format($myrow['ordervalue'],2);

		if ($myrow['printedpackingslip']==0) {
		  $PrintText = _('Print');
		} else {
		  $PrintText = _('Reprint');
		}
		
		if ($_POST['Quotations']=='Orders_Only'){
			printf("<td><a href='%s'>%s</a></td>
				<td><a href='%s'>" . _('Invoice') . "</a></td>
				<td><a target='_blank' href='%s'>" . $PrintText . "</a></td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td align=right>%s</td>
				</tr>",
				$ModifyPage,
				$myrow['orderno'],
				$Confirm_Invoice,
				$PrintDispatchNote,
				$myrow['name'],
				$myrow['brname'],
				$myrow['customerref'],
				$FormatedOrderDate,
				$FormatedDelDate,
				$myrow['deliverto'],
				$FormatedOrderValue);
		} else { /*must be quotes only */
			printf("<td><a href='%s'>%s</a></td>
				<td><a href='%s'>" . $PrintText . "</a></td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td align=right>%s</td>
				</tr>",
				$ModifyPage,
				$myrow['orderno'],
				$PrintQuotation,
				$myrow['name'],
				$myrow['brname'],
				$myrow['customerref'],
				$FormatedOrderDate,
				$FormatedDelDate,
				$myrow['deliverto'],
				$FormatedOrderValue);
		}
		
		$j++;
		If ($j == 12){
			$j=1;
			echo $tableheader;
		}
	//end of page full new headings if
	}
	//end of while loop

	echo '</table>';
}

?>
</form>

<?php } //end StockID already selected

include('includes/footer.inc');
?>

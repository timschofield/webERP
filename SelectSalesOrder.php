<?php

/* $Revision: 1.15 $ */

$PageSecurity = 2;

include('includes/session.inc');
$title = _('Search Outstanding Sales Orders');
include('includes/header.inc');

echo '<FORM ACTION=' . $_SERVER['PHP_SELF'] .'?' .SID . ' METHOD=POST>';


If (isset($_POST['ResetPart'])){
     unset($_REQUEST['SelectedStockItem']);
}

If (isset($_REQUEST['OrderNumber']) AND $_REQUEST['OrderNumber']!='') {
	$_REQUEST['OrderNumber'] = trim($_REQUEST['OrderNumber']);
	if (!is_numeric($_REQUEST['OrderNumber'])){
		  echo '<BR><B>' . _('The Order Number entered MUST be numeric') . '</B><BR>';
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

	if ($_REQUEST['OrderNumber']=='' OR !$_REQUEST['OrderNumber']){

		echo _('Order number') . ": <INPUT type=text name='OrderNumber' MAXLENGTH =8 SIZE=9>&nbsp " . _('From Stock Location') . ":<SELECT name='StockLocation'> ";
		
		$sql = 'SELECT loccode, locationname FROM locations';
		
		$resultStkLocs = DB_query($sql,$db);
		
		while ($myrow=DB_fetch_array($resultStkLocs)){
			if (isset($_POST['StockLocation'])){
				if ($myrow['loccode'] == $_POST['StockLocation']){
				     echo "<OPTION SELECTED Value='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
				} else {
				     echo "<OPTION Value='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
				}
			} elseif ($myrow['loccode']==$_SESSION['UserStockLocation']){
				 echo "<OPTION SELECTED Value='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
			} else {
				 echo "<OPTION Value='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
			}
		}

		echo '</SELECT> &nbsp&nbsp';
		echo '<SELECT NAME="Quotations">';
		
		if ($_GET['Quotations']=='Quotes_Only'){
			$_POST['Quotations']='Quotes_Only';
		}
		
		if ($_POST['Quotations']=='Quotes_Only'){
			echo '<OPTION SELECTED VALUE="Quotes_Only">' . _('Quotations Only');
			echo '<OPTION VALUE="Orders_Only">' . _('Orders Only');
		} else {
			echo '<OPTION SELECTED VALUE="Orders_Only">' . _('Orders Only');
			echo '<OPTION VALUE="Quotes_Only">' . _('Quotations Only');
		}
		
		echo '</SELECT> &nbsp&nbsp';
		echo "<INPUT TYPE=SUBMIT NAME='SearchOrders' VALUE='" . _('Search') . "'>";
    echo '&nbsp;&nbsp;<a href="' . $rootpath . '/SelectOrderItems.php?' . SID . '&NewOrder=Yes">' . _('Add Sales Order') . '</a>';
	}

	$SQL='SELECT categoryid,
			categorydescription
		FROM stockcategory
		ORDER BY categorydescription';

	$result1 = DB_query($SQL,$db);

	echo '<HR>
		<FONT SIZE=1>' . _('To search for sales orders for a specific part use the part selection facilities below') . "</FONT>     <INPUT TYPE=SUBMIT NAME='SearchParts' VALUE='" . _('Search Parts Now') . "'><INPUT TYPE=SUBMIT NAME='ResetPart' VALUE='" . _('Show All') . "'>
      <TABLE>
      	<TR>
      		<TD><FONT SIZE=1>" . _('Select a stock category') . ":</FONT>
      			<SELECT NAME='StockCat'>";

	while ($myrow1 = DB_fetch_array($result1)) {
		echo "<OPTION VALUE='". $myrow1['categoryid'] . "'>" . $myrow1['categorydescription'];
	}

      echo '</SELECT>
      		<TD><FONT SIZE=1>' . _('Enter text extract(s) in the description') . ":</FONT></TD>
      		<TD><INPUT TYPE='Text' NAME='Keywords' SIZE=20 MAXLENGTH=25></TD>
	</TR>
      	<TR><TD></TD>
      		<TD><FONT SIZE 3><B>" . _('OR') . ' </B></FONT><FONT SIZE=1>' . _('Enter extract of the Stock Code') . "</B>:</FONT></TD>
      		<TD><INPUT TYPE='Text' NAME='StockCode' SIZE=15 MAXLENGTH=18></TD>
      	</TR>
      </TABLE>
      <HR>";

If (isset($StockItemsResult)) {

	echo '<TABLE CELLPADDING=2 COLSPAN=7 BORDER=2>';
	$TableHeader = "<TR>
				<TD class='tableheader'>" . _('Code') . "</TD>
				<TD class='tableheader'>" . _('Description') . "</TD>
				<TD class='tableheader'>" . _('On Hand') . "</TD>
				<TD class='tableheader'>" . _('Units') . "</TD>
			</TR>";
	echo $TableHeader;

	$j = 1;
	$k=0; //row colour counter

	while ($myrow=DB_fetch_array($StockItemsResult)) {

		if ($k==1){
			echo "<tr bgcolor='#CCCCCC'>";
			$k=0;
		} else {
			echo "<tr bgcolor='#EEEEEE'>";
			$k++;
		}

		printf("<td><INPUT TYPE=SUBMIT NAME='SelectedStockItem' VALUE='%s'</td>
			<td>%s</td>
			<td ALIGN=RIGHT>%s</td>
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

	echo '</TABLE>';

}
//end if stock search results to show
  else {

	//figure out the SQL required from the inputs available
	if ($_POST['Quotations']=='Orders_Only'){
		$Quotations = 0;
	} else {
		$Quotations =1;
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

	echo '<TABLE CELLPADDING=2 COLSPAN=7 WIDTH=100%>';

	if ($_POST['Quotations']=='Orders_Only'){
		$tableheader = "<TR>
				<TD class='tableheader'>" . _('Modify') . "</TD>
				<TD class='tableheader'>" . _('Invoice') . "</TD>
				<TD class='tableheader'>" . _('Disp. Note') . "</TD>
				<TD class='tableheader'>" . _('Customer') . "</TD>
				<TD class='tableheader'>" . _('Branch') . "</TD>
				<TD class='tableheader'>" . _('Cust Order') . " #</TD>
				<TD class='tableheader'>" . _('Order Date') . "</TD>
				<TD class='tableheader'>" . _('Req Del Date') . "</TD>
				<TD class='tableheader'>" . _('Delivery To') . "</TD>
				<TD class='tableheader'>" . _('Order Total') . "</TD></TR>";
	} else {
		$tableheader = "<TR>
				<TD class='tableheader'>" . _('Modify') . "</TD>
				<TD class='tableheader'>" . _('Print Quote') . "</TD>
				<TD class='tableheader'>" . _('Customer') . "</TD>
				<TD class='tableheader'>" . _('Branch') . "</TD>
				<TD class='tableheader'>" . _('Cust Ref') . " #</TD>
				<TD class='tableheader'>" . _('Quote Date') . "</TD>
				<TD class='tableheader'>" . _('Req Del Date') . "</TD>
				<TD class='tableheader'>" . _('Delivery To') . "</TD>
				<TD class='tableheader'>" . _('Quote Total') . "</TD></TR>";
	}
	
	echo $tableheader;

	$j = 1;
	$k=0; //row colour counter
	while ($myrow=DB_fetch_array($SalesOrdersResult)) {


		if ($k==1){
			echo "<tr bgcolor='#CCCCCC'>";
			$k=0;
		} else {
			echo "<tr bgcolor='#EEEEEE'>";
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
			printf("<td><A HREF='%s'>%s</A></td>
				<td><A HREF='%s'>" . _('Invoice') . "</A></td>
				<td><A target='_blank' HREF='%s'>" . $PrintText . "</A></td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td ALIGN=RIGHT>%s</td>
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
			printf("<td><A HREF='%s'>%s</A></td>
				<td><A HREF='%s'>" . $PrintText . "</A></td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td ALIGN=RIGHT>%s</td>
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

	echo '</TABLE>';
}

?>
</FORM>

<?php } //end StockID already selected

include('includes/footer.inc');
?>
<?php

/* $Revision: 1.17 $ */

$PageSecurity = 1;

include('includes/session.inc');

$title = _('Search All Sales Orders');

include('includes/header.inc');

echo '<P CLASS="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/magnifier.png" TITLE="' . _('Search') . '" ALT="">' . ' ' . _('Search Sales Orders') . '</P><CENTER>';


echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . '?' . SID ."' METHOD=POST>";

if (isset($_GET['SelectedStockItem'])){
	$SelectedStockItem = $_GET['SelectedStockItem'];
} elseif (isset($_POST['SelectedStockItem'])){
	$SelectedStockItem = $_POST['SelectedStockItem'];
}
if (isset($_GET['OrderNumber'])){
	$OrderNumber = $_GET['OrderNumber'];
} elseif (isset($_POST['OrderNumber'])){
	$OrderNumber = $_POST['OrderNumber'];
}
if (isset($_GET['CustomerRef'])){
	$CustomerRef = $_GET['CustomerRef'];
} elseif (isset($_POST['CustomerRef'])){
	$CustomerRef = $_POST['CustomerRef'];
}
if (isset($_GET['SelectedCustomer'])){
	$SelectedCustomer = $_GET['SelectedCustomer'];
} elseif (isset($_POST['SelectedCustomer'])){
	$SelectedCustomer = $_POST['SelectedCustomer'];
}

if (isset($SelectedStockItem) and $SelectedStockItem==''){
	unset($SelectedStockItem);
}
if (isset($OrderNumber) and $OrderNumber==''){
	unset($OrderNumber);
}
if (isset($CustomerRef) and $CustomerRef==''){
	unset($CustomerRef);
}
if (isset($SelectedCustomer) and $SelectedCustomer==''){
	unset($SelectedCustomer);
}
If (isset($_POST['ResetPart'])) {
		unset($SelectedStockItem);
}

If (isset($OrderNumber)) {
	echo '<P CLASS="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/sales.png" TITLE="' . _('Sales Order') . '" ALT="">' . ' ' . _('Order Number') . ' - ' . $OrderNumber . '</P>';
} elseif (isset($CustomerRef)) {
	echo _('Customer Ref') . ' - ' . $CustomerRef;
} else {
	If (isset($SelectedCustomer)) {
		echo _('For customer') . ': ' . $SelectedCustomer .' ' . _('and') . ' ';
		echo "<input type=hidden name='SelectedCustomer' value='$SelectedCustomer'>";
	}

	If (isset($SelectedStockItem)) {

		echo _('for the part') . ': ' . $SelectedStockItem . ' ' . _('and') . ' ' ."<input type=hidden name='SelectedStockItem' value='$SelectedStockItem'>";

	}
}


if (isset($_POST['SearchParts']) and $_POST['SearchParts']!=''){

	If ($_POST['Keywords']!='' AND $_POST['StockCode']!='') {
		echo _('Stock description keywords have been used in preference to the Stock code extract entered');
	}
	If ($_POST['Keywords']!='') {
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
				SUM(purchorderdetails.quantityord-purchorderdetails.quantityrecd) AS qoo,  
				stockmaster.units, 
				SUM(salesorderdetails.quantity - salesorderdetails.qtyinvoiced) AS qdem 
			FROM (((stockmaster LEFT JOIN salesorderdetails on stockmaster.stockid = salesorderdetails.stkcode) 
				 LEFT JOIN locstock ON stockmaster.stockid=locstock.stockid)
				 LEFT JOIN purchorderdetails on stockmaster.stockid = purchorderdetails.itemcode) 
			WHERE salesorderdetails.completed =1 
			AND stockmaster.description " . LIKE . "'$SearchString' 
			AND stockmaster.categoryid='" . $_POST['StockCat'] . "' 
			GROUP BY stockmaster.stockid, 
				stockmaster.description, 
				stockmaster.units 
			ORDER BY stockmaster.stockid";

	} elseif ($_POST['StockCode']!=''){

		$SQL = "SELECT stockmaster.stockid, 
				stockmaster.description, 
				SUM(locstock.quantity) AS qoh, 
				SUM(purchorderdetails.quantityord-purchorderdetails.quantityrecd) AS qoo,  
				SUM(salesorderdetails.quantity - salesorderdetails.qtyinvoiced) AS qdem, 
				stockmaster.units 
			FROM (((stockmaster LEFT JOIN salesorderdetails on stockmaster.stockid = salesorderdetails.stkcode) 
				 LEFT JOIN locstock ON stockmaster.stockid=locstock.stockid)
				 LEFT JOIN purchorderdetails on stockmaster.stockid = purchorderdetails.itemcode) 
			WHERE salesorderdetails.completed =1 
			AND stockmaster.stockid " . LIKE . " '%" . $_POST['StockCode'] . "%' 
			AND stockmaster.categoryid='" . $_POST['StockCat'] . "' 
			GROUP BY stockmaster.stockid, 
				stockmaster.description, 
				stockmaster.units 
			ORDER BY stockmaster.stockid";

	} elseif ($_POST['StockCode']=='' AND $_POST['Keywords']=='' AND $_POST['StockCat']!='') {
		
		$SQL = "SELECT stockmaster.stockid, 
				stockmaster.description, 
				SUM(locstock.quantity) AS qoh, 
				SUM(purchorderdetails.quantityord-purchorderdetails.quantityrecd) AS qoo,  
				SUM(salesorderdetails.quantity - salesorderdetails.qtyinvoiced) AS qdem, 
				stockmaster.units 
			FROM (((stockmaster LEFT JOIN salesorderdetails on stockmaster.stockid = salesorderdetails.stkcode) 
				 LEFT JOIN locstock ON stockmaster.stockid=locstock.stockid)
				 LEFT JOIN purchorderdetails on stockmaster.stockid = purchorderdetails.itemcode) 
			WHERE salesorderdetails.completed =1 
			AND stockmaster.categoryid='" . $_POST['StockCat'] . "' 
			GROUP BY stockmaster.stockid, 
				stockmaster.description, 
				stockmaster.units 
			ORDER BY stockmaster.stockid";

	}

	if (strlen($SQL)<2){
		prnMsg(_('No selections have been made to search for parts') . ' - ' . _('choose a stock category or enter some characters of the code or description then try again'),'warn');
	} else {
		
		$ErrMsg = _('No stock items were returned by the SQL because');
		$DbgMsg = _('The SQL used to retrieve the searched parts was');
		$StockItemsResult = DB_query($SQL,$db,$ErrMsg,$DbgMsg);
		
		if (DB_num_rows($StockItemsResult)==1){
		  	$myrow = DB_fetch_row($StockItemsResult);
		  	$SelectedStockItem = $myrow[0];
			$_POST['SearchOrders']='True';
		  	unset($StockItemsResult);
		  	echo '<BR>' . _('For the part') . ': ' . $SelectedStockItem . ' ' . _('and') . " <input type=hidden name='SelectedStockItem' value='$SelectedStockItem'>";
		}
	}
} else if (isset($_POST['SearchOrders']) AND Is_Date($_POST['OrdersAfterDate'])==1) {

	//figure out the SQL required from the inputs available
	if (isset($OrderNumber)) {
			$SQL = "SELECT salesorders.orderno, 
					debtorsmaster.name, 
					custbranch.brname, 
					salesorders.customerref, 
					salesorders.orddate, 
					salesorders.deliverydate,  
					salesorders.deliverto, SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)) AS ordervalue 
				FROM salesorders, 
					salesorderdetails, 
					debtorsmaster, 
					custbranch 
				WHERE salesorders.orderno = salesorderdetails.orderno 
				AND salesorders.branchcode = custbranch.branchcode 
				AND salesorders.debtorno = debtorsmaster.debtorno 
				AND debtorsmaster.debtorno = custbranch.debtorno 
				AND salesorders.orderno=". $OrderNumber ." 
				AND salesorders.quotation=0 
				GROUP BY salesorders.orderno,
					debtorsmaster.name, 
					custbranch.brname, 
					salesorders.customerref, 
					salesorders.orddate, 
					salesorders.deliverydate,  
					salesorders.deliverto
				ORDER BY salesorders.orderno";
	} elseif (isset($CustomerRef)) {
			$SQL = "SELECT salesorders.orderno, 
					debtorsmaster.name, 
					custbranch.brname, 
					salesorders.customerref, 
					salesorders.orddate, 
					salesorders.deliverydate,  
					salesorders.deliverto, SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)) AS ordervalue 
				FROM salesorders, 
					salesorderdetails, 
					debtorsmaster, 
					custbranch 
				WHERE salesorders.orderno = salesorderdetails.orderno 
				AND salesorders.branchcode = custbranch.branchcode 
				AND salesorders.debtorno = debtorsmaster.debtorno 
				AND debtorsmaster.debtorno = custbranch.debtorno 
				AND salesorders.customerref like '%". $CustomerRef."%'
				AND salesorders.quotation=0 
				GROUP BY salesorders.orderno,
					debtorsmaster.name, 
					custbranch.brname, 
					salesorders.customerref, 
					salesorders.orddate, 
					salesorders.deliverydate,  
					salesorders.deliverto
				ORDER BY salesorders.orderno";
	
	} else {
		$DateAfterCriteria = FormatDateforSQL($_POST['OrdersAfterDate']);

		if (isset($SelectedCustomer) AND !isset($OrderNumber) AND !isset($CustomerRef)) {

			if (isset($SelectedStockItem)) {
				$SQL = "SELECT salesorders.orderno, 
						debtorsmaster.name, 
						custbranch.brname, 
						salesorders.customerref, 
						salesorders.orddate, 
						salesorders.deliverydate,  
						salesorders.deliverto, SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)) AS ordervalue 
					FROM salesorders, 
						salesorderdetails, 
						debtorsmaster, 
						custbranch 
					WHERE salesorders.orderno = salesorderdetails.orderno 
					AND salesorders.branchcode = custbranch.branchcode 
					AND salesorders.debtorno = debtorsmaster.debtorno 
					AND debtorsmaster.debtorno = custbranch.debtorno 
					AND salesorderdetails.stkcode='". $SelectedStockItem ."' 
					AND salesorders.debtorno='" . $SelectedCustomer ."' 
					AND salesorders.orddate >= '" . $DateAfterCriteria ."' 
					AND salesorders.quotation=0 
					GROUP BY salesorders.orderno, 
						debtorsmaster.name, 
						custbranch.brname, 
						salesorders.customerref, 
						salesorders.orddate, 
						salesorders.deliverydate,  
						salesorders.deliverto
					ORDER BY salesorders.orderno";
			} else {
				$SQL = "SELECT salesorders.orderno, 
						debtorsmaster.name, 
						custbranch.brname, 
						salesorders.customerref, 
						salesorders.orddate, 
						salesorders.deliverto, 
						salesorders.deliverydate, SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)) AS ordervalue 
					FROM salesorders, 
						salesorderdetails, 
						debtorsmaster, 
						custbranch 
					WHERE salesorders.orderno = salesorderdetails.orderno 
					AND salesorders.debtorno = debtorsmaster.debtorno 
					AND salesorders.branchcode = custbranch.branchcode 
					AND debtorsmaster.debtorno = custbranch.debtorno 
					AND salesorders.debtorno='" . $SelectedCustomer . "' 
					AND salesorders.orddate >= '" . $DateAfterCriteria . "' 
					AND salesorders.quotation=0 
					GROUP BY salesorders.orderno, 
						debtorsmaster.name, 
						custbranch.brname, 
						salesorders.customerref, 
						salesorders.orddate, 
						salesorders.deliverydate,  
						salesorders.deliverto
					ORDER BY salesorders.orderno";
			}
		} else { //no customer selected
			if (isset($SelectedStockItem)) {
				$SQL = "SELECT salesorders.orderno, 
						debtorsmaster.name, 
						custbranch.brname, 
						salesorders.customerref, 
						salesorders.orddate, 
						salesorders.deliverto, 
						salesorders.deliverydate, SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)) AS ordervalue 
					FROM salesorders, 
						salesorderdetails, 
						debtorsmaster, 
						custbranch 
					WHERE salesorders.orderno = salesorderdetails.orderno 
					AND salesorders.debtorno = debtorsmaster.debtorno 
					AND salesorders.branchcode = custbranch.branchcode 
					AND debtorsmaster.debtorno = custbranch.debtorno 
					AND salesorderdetails.stkcode='". $SelectedStockItem ."'  
					AND salesorders.orddate >= '" . $DateAfterCriteria . "' 
					AND salesorders.quotation=0 
					GROUP BY salesorders.orderno, 
						debtorsmaster.name, 
						custbranch.brname, 
						salesorders.customerref, 
						salesorders.orddate, 
						salesorders.deliverydate,  
						salesorders.deliverto
					ORDER BY salesorders.orderno";
			} else {
				$SQL = "SELECT salesorders.orderno, 
						debtorsmaster.name, 
						custbranch.brname, 
						salesorders.customerref, 
						salesorders.orddate, 
						salesorders.deliverto, 
						salesorders.deliverydate, SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)) AS ordervalue 
					FROM salesorders, 
						salesorderdetails, 
						debtorsmaster, 
						custbranch 
					WHERE salesorders.orderno = salesorderdetails.orderno 
					AND salesorders.debtorno = debtorsmaster.debtorno 
					AND salesorders.branchcode = custbranch.branchcode 
					AND debtorsmaster.debtorno = custbranch.debtorno 
					AND salesorders.orddate >= '$DateAfterCriteria' 
					AND salesorders.quotation=0 
					GROUP BY salesorders.orderno, 
						debtorsmaster.name, 
						custbranch.brname, 
						salesorders.customerref, 
						salesorders.orddate, 
						salesorders.deliverydate,  
						salesorders.deliverto
					ORDER BY salesorders.orderno";
			}

		} //end selected customer
	} //end not order number selected

	$SalesOrdersResult = DB_query($SQL,$db);

	if (DB_error_no($db) !=0) {
		echo '<BR>' . _('No orders were returned by the SQL because') . ' ' . DB_error_msg($db);
		echo "<BR>$SQL";
	}

}//end of which button clicked options

if (!isset($_POST['OrdersAfterDate']) OR $_POST['OrdersAfterDate'] == '' OR ! Is_Date($_POST['OrdersAfterDate'])){
	$_POST['OrdersAfterDate'] = Date($_SESSION['DefaultDateFormat'],Mktime(0,0,0,Date('m')-2,Date('d'),Date('Y')));
}
echo "<TABLE>";
if (!isset($OrderNumber) or $OrderNumber==''){
	echo '<TR><TD>' . _('Order Number') . ':</TD><TD>' . "<INPUT type=text name='OrderNumber' MAXLENGTH =8 SIZE=9></TD><TD rowspan=2>" . _('for all orders placed after') .
			": </TD><TD rowspan=2><INPUT type=text name='OrdersAfterDate' MAXLENGTH =10 SIZE=11 value=" . $_POST['OrdersAfterDate'] . "></td><td rowspan=2>" .
			"<INPUT TYPE=SUBMIT NAME='SearchOrders' VALUE='" . _('Search Orders') . "'></TD></TR>";
	echo '<TR><TD>' . _('Customer Ref') . ':</TD><TD>' . "<INPUT type=text name='CustomerRef' MAXLENGTH =8 SIZE=9></TD></TR>";
}
echo '</TABLE>';

if (!isset($SelectedStockItem)) {
	$SQL='SELECT categoryid, categorydescription FROM stockcategory ORDER BY categorydescription';
	$result1 = DB_query($SQL,$db);

   echo '<HR>';
   echo '<FONT SIZE=1>' . _('To search for sales orders for a specific part use the part selection facilities below') . '</FONT>';
   echo '<INPUT TYPE=SUBMIT NAME="SearchParts" VALUE="' . _('Search Parts Now') . '">';
   
   if (count($_SESSION['AllowedPageSecurityTokens'])>1){
   	echo '<INPUT TYPE=SUBMIT NAME="ResetPart" VALUE="' . _('Show All') . '">';
   }
   echo '<TABLE>';
   echo '<TR><TD><FONT SIZE=1>' . _('Select a stock category') . ':</FONT>';
   echo '<SELECT NAME="StockCat">';

	while ($myrow1 = DB_fetch_array($result1)) {
		if (isset($_POST['StockCat']) and $myrow1['categoryid'] == $_POST['StockCat']){
			echo "<OPTION SELECTED VALUE='". $myrow1['categoryid'] . "'>" . $myrow1['categorydescription'];
		} else {
			echo "<OPTION VALUE='". $myrow1['categoryid'] . "'>" . $myrow1['categorydescription'];
		}
	}

   echo '</SELECT>';
   echo '<TD><FONT SIZE=1>' . _('Enter text extracts in the description') . ':</FONT></TD>';
   echo '<TD><INPUT TYPE="Text" NAME="Keywords" SIZE=20 MAXLENGTH=25></TD></TR>';
   echo '<TR><TD></TD>';
   echo '<TD><FONT SIZE 3><B> ' ._('OR') . ' </B></FONT><FONT SIZE=1>' . _('Enter extract of the Stock Code') . ':</FONT></TD>';
   echo '<TD><INPUT TYPE="Text" NAME="StockCode" SIZE=15 MAXLENGTH=18></TD>';
   echo '</TR>';
   echo '</TABLE>';

   echo '<HR>';

}

If (isset($StockItemsResult)) {

	echo '<TABLE CELLPADDING=2 COLSPAN=7 BORDER=2>';

	$TableHeadings = "<TR><TH>" . _('Code') . "</TH>" .
				"<TH>" . _('Description') . "</TH>" .
				"<TH>" . _('On Hand') . '</TH>' .
				"<TH>" . _('Purchase Orders') . '</TH>' .
				"<TH>" . _('Sales Orders') . "</TH>" .
				"<TH>" . _('Units') . '</TH></TR>';

	echo $TableHeadings;

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

		printf("<td><FONT SIZE=1><INPUT TYPE=SUBMIT NAME='SelectedStockItem' VALUE='%s'</FONT></td>
			<td><FONT SIZE=1>%s</FONT></td>
			<td ALIGN=RIGHT><FONT SIZE=1>%s</FONT></td>
			<td ALIGN=RIGHT><FONT SIZE=1>%s</FONT></td>
			<td ALIGN=RIGHT><FONT SIZE=1>%s</FONT></td>
			<td><FONT SIZE=1>%s</FONT></td></tr>", 
			$myrow['stockid'], 
			$myrow['description'], 
			$myrow['qoh'], 
			$myrow['qoo'],
			$myrow['qdem'],
			$myrow['units']);

//end of page full new headings if
	}
//end of while loop

	echo '</TABLE>';

}
//end if stock search results to show

If (isset($SalesOrdersResult)) {

/*show a table of the orders returned by the SQL */

	echo '<TABLE CELLPADDING=2 COLSPAN=6 WIDTH=100%>';

	$tableheader = "<TR><TH>" . _('Order') . " #</TH>
			<TH>" . _('Customer') . "</TH>
			<TH>" . _('Branch') . "</TH>
			<TH>" . _('Cust Order') . " #</TH>
			<TH>" . _('Order Date') . "</TH>
			<TH>" . _('Req Del Date') . "</TH>
			<TH>" . _('Delivery To') . "</TH>
			<TH>" . _('Order Total') . "</TH></TR>";

	echo $tableheader;

	$j = 1;
	$k=0; //row colour counter
	while ($myrow=DB_fetch_array($SalesOrdersResult)) {


		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}

		$ViewPage = $rootpath . '/OrderDetails.php?' .SID . '&OrderNumber=' . $myrow['orderno'];
		$FormatedDelDate = ConvertSQLDate($myrow['deliverydate']);
		$FormatedOrderDate = ConvertSQLDate($myrow['orddate']);
		$FormatedOrderValue = number_format($myrow['ordervalue'],2);

		printf("<td><A target='_blank' HREF='%s'>%s</A></td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td ALIGN=RIGHT>%s</td>
			</tr>", 
			$ViewPage, 
			$myrow['orderno'], 
			$myrow['name'], 
			$myrow['brname'], 
			$myrow['customerref'],
			$FormatedOrderDate,
			$FormatedDelDate, 
			$myrow['deliverto'], 
			$FormatedOrderValue);

//end of page full new headings if
	}
//end of while loop

	echo '</TABLE>';

}

echo '</form>';
include('includes/footer.inc');

?>

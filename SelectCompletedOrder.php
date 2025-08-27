<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Search All Sales Orders');
$ViewTopic = 'SalesOrders';
$BookMark = '';
include('includes/header.php');

if (isset($_POST['OrdersAfterDate'])){$_POST['OrdersAfterDate'] = ConvertSQLDate($_POST['OrdersAfterDate']);}

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/magnifier.png" title="' . __('Search') . '" alt="" />
     ' . ' ' . __('Search Sales Orders') . '</p>';

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (isset($_POST['completed'])) {
	$Completed="=1";
	$ShowChecked="checked='checked'";
} else {
	$Completed=">=0";
	$ShowChecked='';
}

if (isset($_GET['SelectedStockItem'])){
	$SelectedStockItem = $_GET['SelectedStockItem'];
} elseif (isset($_POST['SelectedStockItem'])){
	$SelectedStockItem = $_POST['SelectedStockItem'];
}
if (isset($_GET['OrderNumber'])){
	$OrderNumber = filter_number_format($_GET['OrderNumber']);
} elseif (isset($_POST['OrderNumber'])){
	$OrderNumber = filter_number_format($_POST['OrderNumber']);
}
if (isset($_GET['CustomerRef'])){
	$CustomerRef = $_GET['CustomerRef'];
	$CustomerGet = 1;
} elseif (isset($_POST['CustomerRef'])){
	$CustomerRef = $_POST['CustomerRef'];
}
if (isset($_GET['SelectedCustomer'])){
	$SelectedCustomer = $_GET['SelectedCustomer'];
} elseif (isset($_POST['SelectedCustomer'])){
	$SelectedCustomer = $_POST['SelectedCustomer'];
}

if ($CustomerLogin==1){
	$SelectedCustomer = $_SESSION['CustomerID'];
}

if (isset($SelectedStockItem) AND $SelectedStockItem==''){
	unset($SelectedStockItem);
}
if (isset($OrderNumber) AND $OrderNumber==''){
	unset($OrderNumber);
}
if (isset($CustomerRef) AND $CustomerRef==''){
	unset($CustomerRef);
}
if (isset($SelectedCustomer) AND $SelectedCustomer==''){
	unset($SelectedCustomer);
}
if (isset($_POST['ResetPart'])) {
		unset($SelectedStockItem);
}

if (isset($OrderNumber)) {
	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/sales.png" title="' . __('Sales Order') . '" alt="" />
         ' . ' ' . __('Order Number') . ' - ' . $OrderNumber . '</p>';
	if (mb_strlen($_SESSION['UserBranch'])>1){
   	   echo __('For customer') . ': ' . $SelectedCustomer;
	   echo '<input type="hidden" name="SelectedCustomer" value="' . $SelectedCustomer .'" />';
        }
} elseif (isset($CustomerRef)) {
	echo __('Customer Ref') . ' - ' . $CustomerRef;
	if (mb_strlen($_SESSION['UserBranch'])>1){
   	   echo ' ' . __('and for customer') . ': ' . $SelectedCustomer .' ' . __('and') . ' ';
	   echo '<input type="hidden" name="SelectedCustomer" value="' .$SelectedCustomer .'" />';
        }
} else {
	if (isset($SelectedCustomer)) {
		echo __('For customer') . ': ' . $SelectedCustomer .' ' . __('and') . ' ';
		echo '<input type="hidden" name="SelectedCustomer" value="'.$SelectedCustomer.'" />';
	}

	if (isset($SelectedStockItem)) {

		$PartString = __('for the part') . ': <b>' . $SelectedStockItem . '</b> ' . __('and') . ' ' .
			'<input type="hidden" name="SelectedStockItem" value="'.$SelectedStockItem.'" />';

	}
}

if (isset($_POST['SearchParts']) AND $_POST['SearchParts']!=''){

	if ($_POST['Keywords']!='' AND $_POST['StockCode']!='') {
		echo __('Stock description keywords have been used in preference to the Stock code extract entered');
	}
	if ($_POST['Keywords']!='') {
		//insert wildcard characters in spaces
		$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';

		if (isset($_POST['completed'])) {
			$SQL = "SELECT stockmaster.stockid,
							stockmaster.description,
							stockmaster.decimalplaces,
							SUM(locstock.quantity) AS qoh,
							SUM(purchorderdetails.quantityord-purchorderdetails.quantityrecd) AS qoo,
							stockmaster.units,
							SUM(salesorderdetails.quantity - salesorderdetails.qtyinvoiced) AS qdem
						FROM (((stockmaster LEFT JOIN salesorderdetails on stockmaster.stockid = salesorderdetails.stkcode)
							 LEFT JOIN locstock ON stockmaster.stockid=locstock.stockid)
							 LEFT JOIN purchorderdetails on stockmaster.stockid = purchorderdetails.itemcode)
						WHERE salesorderdetails.completed =1
						AND stockmaster.description " . LIKE . " '" . $SearchString. "'
						AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
						GROUP BY stockmaster.stockid,
							stockmaster.description,
							stockmaster.decimalplaces,
							stockmaster.units
						ORDER BY stockmaster.stockid";
		} else {
			$SQL = "SELECT stockmaster.stockid,
							stockmaster.description,
							stockmaster.decimalplaces,
							SUM(locstock.quantity) AS qoh,
							SUM(purchorderdetails.quantityord-purchorderdetails.quantityrecd) AS qoo,
							stockmaster.units,
							SUM(salesorderdetails.quantity - salesorderdetails.qtyinvoiced) AS qdem
						FROM (((stockmaster LEFT JOIN salesorderdetails on stockmaster.stockid = salesorderdetails.stkcode)
							 LEFT JOIN locstock ON stockmaster.stockid=locstock.stockid)
							 LEFT JOIN purchorderdetails on stockmaster.stockid = purchorderdetails.itemcode)
						WHERE stockmaster.description " . LIKE . " '" . $SearchString. "'
						AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
						GROUP BY stockmaster.stockid,
							stockmaster.description,
							stockmaster.decimalplaces,
							stockmaster.units
						ORDER BY stockmaster.stockid";
		}

	} elseif ($_POST['StockCode']!=''){

		if (isset($_POST['completed'])) {
			$SQL = "SELECT stockmaster.stockid,
							stockmaster.description,
							stockmaster.decimalplaces,
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
							stockmaster.decimalplaces,
							stockmaster.units
						ORDER BY stockmaster.stockid";
		} else {
			$SQL = "SELECT stockmaster.stockid,
							stockmaster.description,
							stockmaster.decimalplaces,
							SUM(locstock.quantity) AS qoh,
							SUM(purchorderdetails.quantityord-purchorderdetails.quantityrecd) AS qoo,
							SUM(salesorderdetails.quantity - salesorderdetails.qtyinvoiced) AS qdem,
							stockmaster.units
						FROM (((stockmaster LEFT JOIN salesorderdetails on stockmaster.stockid = salesorderdetails.stkcode)
							 LEFT JOIN locstock ON stockmaster.stockid=locstock.stockid)
							 LEFT JOIN purchorderdetails on stockmaster.stockid = purchorderdetails.itemcode)
						WHERE stockmaster.stockid " . LIKE  . " '%" . $_POST['StockCode'] . "%'
						AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
						GROUP BY stockmaster.stockid,
							stockmaster.description,
							stockmaster.decimalplaces,
							stockmaster.units
						ORDER BY stockmaster.stockid";
		}

	} elseif ($_POST['StockCode']=='' AND $_POST['Keywords']=='' AND $_POST['StockCat']!='') {

		if (isset($_POST['completed'])) {
			$SQL = "SELECT stockmaster.stockid,
							stockmaster.description,
							stockmaster.decimalplaces,
							SUM(locstock.quantity) AS qoh,
							SUM(purchorderdetails.quantityord-purchorderdetails.quantityrecd) AS qoo,
							SUM(salesorderdetails.quantity - salesorderdetails.qtyinvoiced) AS qdem,
							stockmaster.units
						FROM (((stockmaster LEFT JOIN salesorderdetails on stockmaster.stockid = salesorderdetails.stkcode)
							 LEFT JOIN locstock ON stockmaster.stockid=locstock.stockid)
							 LEFT JOIN purchorderdetails on stockmaster.stockid = purchorderdetails.itemcode)
						WHERE salesorderdetails.completed=1
						AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
						GROUP BY stockmaster.stockid,
							stockmaster.description,
							stockmaster.decimalplaces,
							stockmaster.units
						ORDER BY stockmaster.stockid";
		} else {
			$SQL = "SELECT stockmaster.stockid,
							stockmaster.description,
							stockmaster.decimalplaces,
							SUM(locstock.quantity) AS qoh,
							SUM(purchorderdetails.quantityord-purchorderdetails.quantityrecd) AS qoo,
							SUM(salesorderdetails.quantity - salesorderdetails.qtyinvoiced) AS qdem,
							stockmaster.units
						FROM (((stockmaster LEFT JOIN salesorderdetails on stockmaster.stockid = salesorderdetails.stkcode)
							 LEFT JOIN locstock ON stockmaster.stockid=locstock.stockid)
							 LEFT JOIN purchorderdetails on stockmaster.stockid = purchorderdetails.itemcode)
						WHERE stockmaster.categoryid='" . $_POST['StockCat'] . "'
						GROUP BY stockmaster.stockid,
							stockmaster.description,
							stockmaster.decimalplaces,
							stockmaster.units
						ORDER BY stockmaster.stockid";
		}
	}

	if (mb_strlen($SQL)<2){
		prnMsg(__('No selections have been made to search for parts') . ' - ' . __('choose a stock category or enter some characters of the code or description then try again'),'warn');
	} else {

		$ErrMsg = __('No stock items were returned by the SQL because');
		$StockItemsResult = DB_query($SQL, $ErrMsg);

		if (DB_num_rows($StockItemsResult)==1){
		  	$MyRow = DB_fetch_row($StockItemsResult);
		  	$SelectedStockItem = $MyRow[0];
			$_POST['SearchOrders']='true';
		  	unset($StockItemsResult);
		  	echo '<br />' . __('For the part') . ': ' . $SelectedStockItem . ' ' . __('and') . ' <input type="hidden" name="SelectedStockItem" value="' . $SelectedStockItem . '" />';
		}
	}
} else if ((isset($_POST['SearchOrders']) AND Is_Date($_POST['OrdersAfterDate'])==1) OR (isset($CustomerGet))) {

	//figure out the SQL required from the inputs available
	if (isset($OrderNumber)) {
		$SQL = "SELECT salesorders.orderno,
						debtorsmaster.name,
						custbranch.brname,
						salesorders.customerref,
						salesorders.orddate,
						salesorders.deliverydate,
						salesorders.deliverto,
						currencies.decimalplaces AS currdecimalplaces,
						SUM(salesorderdetails.linenetprice) AS ordervalue
					FROM salesorders INNER JOIN salesorderdetails
						ON salesorders.orderno = salesorderdetails.orderno
						INNER JOIN debtorsmaster
						ON salesorders.debtorno = debtorsmaster.debtorno
						INNER JOIN custbranch
						ON salesorders.branchcode = custbranch.branchcode
						AND salesorders.debtorno = custbranch.debtorno
						INNER JOIN currencies
						ON debtorsmaster.currcode = currencies.currabrev
					WHERE salesorders.orderno='". $OrderNumber ."'
					AND salesorders.quotation=0
					AND salesorderdetails.completed " . $Completed;
	} elseif (isset($CustomerRef)) {
		if (isset($SelectedCustomer)) {
			$SQL = "SELECT salesorders.orderno,
							debtorsmaster.name,
							currencies.decimalplaces AS currdecimalplaces,
							custbranch.brname,
							salesorders.customerref,
							salesorders.orddate,
							salesorders.deliverydate,
							salesorders.deliverto,
							SUM(salesorderdetails.linenetprice) AS ordervalue
						FROM salesorders INNER JOIN salesorderdetails
							ON salesorders.orderno = salesorderdetails.orderno
							INNER JOIN debtorsmaster
							ON salesorders.debtorno = debtorsmaster.debtorno
							INNER JOIN custbranch
							ON salesorders.branchcode = custbranch.branchcode
							AND salesorders.debtorno = custbranch.debtorno
							INNER JOIN currencies
							ON debtorsmaster.currcode = currencies.currabrev
						WHERE salesorders.debtorno='" . $SelectedCustomer ."'
						AND salesorders.customerref like '%". $CustomerRef."%'
						AND salesorders.quotation=0
						AND salesorderdetails.completed".$Completed;
		} else { //customer not selected
			$SQL = "SELECT salesorders.orderno,
							debtorsmaster.name,
							currencies.decimalplaces AS currdecimalplaces,
							custbranch.brname,
							salesorders.customerref,
							salesorders.orddate,
							salesorders.deliverydate,
							salesorders.deliverto,
							SUM(salesorderdetails.linenetprice) AS ordervalue
						FROM salesorders INNER JOIN salesorderdetails
							ON salesorders.orderno = salesorderdetails.orderno
							INNER JOIN debtorsmaster
							ON salesorders.debtorno = debtorsmaster.debtorno
							INNER JOIN custbranch
							ON salesorders.branchcode = custbranch.branchcode
							AND salesorders.debtorno = custbranch.debtorno
							INNER JOIN currencies
							ON debtorsmaster.currcode = currencies.currabrev
						WHERE salesorders.customerref " . LIKE . " '%". $CustomerRef . "%'
						AND salesorders.quotation=0
						AND salesorderdetails.completed" . $Completed;
		}

	} else {
		$DateAfterCriteria = FormatDateforSQL($_POST['OrdersAfterDate']);

		if (isset($SelectedCustomer) AND !isset($OrderNumber) AND !isset($CustomerRef)) {

			if (isset($SelectedStockItem)) {
				$SQL = "SELECT salesorders.orderno,
								debtorsmaster.name,
								currencies.decimalplaces AS currdecimalplaces,
								custbranch.brname,
								salesorders.customerref,
								salesorders.orddate,
								salesorders.deliverydate,
								salesorders.deliverto,
								SUM(salesorderdetails.linenetprice) AS ordervalue
							FROM salesorders INNER JOIN salesorderdetails
								ON salesorders.orderno = salesorderdetails.orderno
								INNER JOIN debtorsmaster
								ON salesorders.debtorno = debtorsmaster.debtorno
								INNER JOIN custbranch
								ON salesorders.branchcode = custbranch.branchcode
								AND salesorders.debtorno = custbranch.debtorno
								INNER JOIN currencies
								ON debtorsmaster.currcode = currencies.currabrev
							WHERE salesorderdetails.stkcode='". $SelectedStockItem ."'
							AND salesorders.debtorno='" . $SelectedCustomer ."'
							AND salesorders.orddate >= '" . $DateAfterCriteria ."'
							AND salesorders.quotation=0
							AND salesorderdetails.completed".$Completed;
			} else {
				$SQL = "SELECT salesorders.orderno,
								debtorsmaster.name,
								currencies.decimalplaces AS currdecimalplaces,
								custbranch.brname,
								salesorders.customerref,
								salesorders.orddate,
								salesorders.deliverto,
								salesorders.deliverydate,
								SUM(salesorderdetails.linenetprice) AS ordervalue
							FROM salesorders INNER JOIN salesorderdetails
								ON salesorders.orderno = salesorderdetails.orderno
								INNER JOIN debtorsmaster
								ON salesorders.debtorno = debtorsmaster.debtorno
								INNER JOIN custbranch
								ON salesorders.branchcode = custbranch.branchcode
								AND salesorders.debtorno = custbranch.debtorno
								INNER JOIN currencies
								ON debtorsmaster.currcode = currencies.currabrev
							WHERE salesorders.debtorno='" . $SelectedCustomer . "'
							AND salesorders.orddate >= '" . $DateAfterCriteria . "'
							AND salesorders.quotation=0
							AND salesorderdetails.completed".$Completed;
			}
		} else { //no customer selected
			if (isset($SelectedStockItem)) {
				$SQL = "SELECT salesorders.orderno,
								debtorsmaster.name,
								currencies.decimalplaces AS currdecimalplaces,
								custbranch.brname,
								salesorders.customerref,
								salesorders.orddate,
								salesorders.deliverto,
								salesorders.deliverydate,
								SUM(salesorderdetails.linenetprice) AS ordervalue
							FROM salesorders INNER JOIN salesorderdetails
								ON salesorders.orderno = salesorderdetails.orderno
								INNER JOIN debtorsmaster
								ON salesorders.debtorno = debtorsmaster.debtorno
								INNER JOIN custbranch
								ON salesorders.branchcode = custbranch.branchcode
								AND salesorders.debtorno = custbranch.debtorno
								INNER JOIN currencies
								ON debtorsmaster.currcode = currencies.currabrev
							WHERE salesorderdetails.stkcode='". $SelectedStockItem ."'
							AND salesorders.orddate >= '" . $DateAfterCriteria . "'
							AND salesorders.quotation=0
							AND salesorderdetails.completed".$Completed;
			} else {
				$SQL = "SELECT salesorders.orderno,
								debtorsmaster.name,
								currencies.decimalplaces AS currdecimalplaces,
								custbranch.brname,
								salesorders.customerref,
								salesorders.orddate,
								salesorders.deliverto,
								salesorders.deliverydate,
								SUM(salesorderdetails.linenetprice) AS ordervalue
							FROM salesorders INNER JOIN salesorderdetails
								ON salesorders.orderno = salesorderdetails.orderno
								INNER JOIN debtorsmaster
								ON salesorders.debtorno = debtorsmaster.debtorno
								INNER JOIN custbranch
								ON salesorders.branchcode = custbranch.branchcode
								AND salesorders.debtorno = custbranch.debtorno
								INNER JOIN currencies
								ON debtorsmaster.currcode = currencies.currabrev
							WHERE salesorders.orddate >= '".$DateAfterCriteria . "'
							AND salesorders.quotation=0
							AND salesorderdetails.completed".$Completed;
			}
		} //end selected customer
	} //end not order number selected

	if ($_SESSION['SalesmanLogin'] != '') {
		$SQL .= " AND salesorders.salesperson='" . $_SESSION['SalesmanLogin'] . "'";
	}
	$SQL .= " GROUP BY salesorders.orderno,
					debtorsmaster.name,
					currencies.decimalplaces,
					custbranch.brname,
					salesorders.customerref,
					salesorders.orddate,
					salesorders.deliverydate,
					salesorders.deliverto
				ORDER BY salesorders.orderno";

	$SalesOrdersResult = DB_query($SQL);

	if (DB_error_no() !=0) {
		prnMsg( __('No orders were returned by the SQL because') . ' ' . DB_error_msg(), 'info');
		echo '<br /> ' . $SQL;
	}

}//end of which button clicked options

if (!isset($_POST['OrdersAfterDate']) OR $_POST['OrdersAfterDate'] == '' OR ! Is_Date($_POST['OrdersAfterDate'])){
	$_POST['OrdersAfterDate'] = Date($_SESSION['DefaultDateFormat'],Mktime(0,0,0,Date('m')-2,Date('d'),Date('Y')));
}
echo '<fieldset>
		<legend class="search">', __('Sales Order Search'), '</legend>';

if (isset($PartString)) {
	echo '<field><td>' . $PartString . '</td>';
} else {
	echo '<field><td></td>';
}
if (!isset($_POST['OrderNumber'])){
	$_POST['OrderNumber']='';
}
echo '<field>
		<label for="OrderNumber">' . __('Order Number') . ':</label>
		<input type="text" name="OrderNumber" maxlength="8" size="9" value ="' . $_POST['OrderNumber'] . '" />
	</field>
	<field>
		<label for="OrdersAfterDate">' . __('for all orders placed after') . ': </label>
		<input type="date" name="OrdersAfterDate" maxlength="10" size="11" value="' . FormatDateForSQL($_POST['OrdersAfterDate']) . '" />
	</field>';
echo '<field>
		<label for="CustomerRef">' . __('Customer Ref') . ':</label>
		<input type="text" name="CustomerRef" maxlength="8" size="9" />
	</field>
	<field>
		<label for="completed">' . __('Show Completed orders only') . '</label>
		<input type="checkbox" ' . $ShowChecked . ' name="completed" />
	</field>';

echo '</fieldset>';

if (!isset($SelectedStockItem)) {
	$Result1 = DB_query("SELECT categoryid,
							categorydescription
						FROM stockcategory
						ORDER BY categorydescription");

   echo '<div class="centre">
			<input type="submit" name="SearchOrders" value="' . __('Search Orders') . '" />
		</div>';
   echo '<div class="page_help_text">' . __('To search for sales orders for a specific part use the part selection facilities below') . '</div>';
   echo '<fieldset>
			<legend class="search">', __('Orders By Item Search'), '</legend>';
   echo '<field>
			<label for="StockCat">' . __('Select a stock category') . ':</label>
			<select name="StockCat">';

	while ($MyRow1 = DB_fetch_array($Result1)) {
		if (isset($_POST['StockCat']) AND $MyRow1['categoryid'] == $_POST['StockCat']){
			echo '<option selected="selected" value="' .  $MyRow1['categoryid'] . '">' . $MyRow1['categorydescription'] . '</option>';
		} else {
			echo '<option value="'. $MyRow1['categoryid'] . '">' . $MyRow1['categorydescription'] . '</option>';
		}
	}

   echo '</select>
	</field>';

   echo '<field>
			<label for="Keywords">' . __('Enter text extracts in the description') . ':</label>
			<input type="text" name="Keywords" size="20" maxlength="25" />
		</field>';

	echo '<field>
			<label for="StockCode">' . '<b>' . __('OR') . ' </b>' . __('Enter extract of the Stock Code') . ':</label>
			<input type="text" name="StockCode" size="15" maxlength="18" />
		</field>
	</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="SearchParts" value="' . __('Search Parts Now') . '" />';

	if (count($_SESSION['AllowedPageSecurityTokens'])>1){
		echo '<input type="submit" name="ResetPart" value="' . __('Show All') . '" /></div>';
	}
	echo '</div>';
}

if (isset($StockItemsResult)) {

	echo '<table cellpadding="2" class="selection">
			<tr>
				<th>' . __('Code') . '</th>
				<th>' . __('Description') . '</th>
				<th>' . __('On Hand') . '</th>
				<th>' . __('Purchase Orders') . '</th>
				<th>' . __('Sales Orders') . '</th>
				<th>' . __('Units') . '</th>
			</tr>';

	while ($MyRow=DB_fetch_array($StockItemsResult)) {

		echo '<tr class="striped_row">
				<td><input type="submit" name="SelectedStockItem" value="', $MyRow['stockid'], '" /></td>
				<td>', $MyRow['description'], '</td>
				<td class="number">', locale_number_format($MyRow['qoh'],$MyRow['decimalplaces']), '</td>
				<td class="number">', locale_number_format($MyRow['qoo'],$MyRow['decimalplaces']), '</td>
				<td class="number">', locale_number_format($MyRow['qdem'],$MyRow['decimalplaces']), '</td>
				<td>', $MyRow['units'], '</td>
			</tr>';

//end of page full new headings if
	}
//end of while loop

	echo '</table>';

}
//end if stock search results to show

if (isset($SalesOrdersResult)) {
	if (DB_num_rows($SalesOrdersResult) == 1) {
		if (!isset($OrderNumber)) {
			$OrdRow = DB_fetch_array($SalesOrdersResult);
			$OrderNumber = $OrdRow['orderno'];
		}
		echo '<meta http-equiv="refresh" content="0; url=' . $RootPath . '/OrderDetails.php?OrderNumber=' . $OrderNumber. '">';
		exit();
	}

/*show a table of the orders returned by the SQL */

	echo '<table cellpadding="2" width="90%" class="selection">
			<tr>
				<th>' . __('Order') . ' #</th>
				<th>' . __('Customer') . '</th>
				<th>' . __('Branch') . '</th>
				<th>' . __('Cust Order') . ' #</th>
				<th>' . __('Order Date') . '</th>
				<th>' . __('Req Del Date') . '</th>
				<th>' . __('Delivery To') . '</th>
				<th>' . __('Order Total') . '</th>
			</tr>';

	while ($MyRow=DB_fetch_array($SalesOrdersResult)) {

		$ViewPage = $RootPath . '/OrderDetails.php?OrderNumber=' . $MyRow['orderno'];
		$FormatedDelDate = ConvertSQLDate($MyRow['deliverydate']);
		$FormatedOrderDate = ConvertSQLDate($MyRow['orddate']);
		$FormatedOrderValue = locale_number_format($MyRow['ordervalue'],$MyRow['currdecimalplaces']);

		echo '<tr class="striped_row">
				<td><a href="', $ViewPage, '">', $MyRow['orderno'], '</a></td>
				<td>', $MyRow['name'], '</td>
				<td>', $MyRow['brname'], '</td>
				<td>', $MyRow['customerref'], '</td>
				<td>', $FormatedOrderDate, '</td>
				<td>', $FormatedDelDate, '</td>
				<td>', $MyRow['deliverto'], '</td>
				<td class="number">', $FormatedOrderValue, '</td>
			</tr>';

//end of page full new headings if
	}
//end of while loop

	echo '</table>';
}

echo '</form>';
include('includes/footer.php');

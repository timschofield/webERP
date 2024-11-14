<?php

include('includes/session.php');
$Title = _('Financial planning for active (Authorised, Printed, Pending) Purchase Orders by Supplier');
include('includes/header.php');

if (isset($_POST['submit'])) {
    submit($RootPath, $_POST['Country'], $_POST['Currency']);
} else {
    display();
}

//####_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
function submit($RootPath, $Country, $Currency) {

	if ($Country != 'All'){
		$WhereCountry 	= " AND suppliers.address6 = '". $Country ."' ";
	}else{
		$WhereCountry = ' ';
	}

	if ($Currency != 'All'){
		$WhereCurrency 	= " AND suppliers.currcode = '". $Currency ."' ";
	}else{
		$WhereCurrency = ' ';
	}

	/* look for suppliers with active PO's */ 
	$sql = "SELECT suppliers.supplierid,
				suppliers.suppname,
				suppliers.currcode,
				currencies.decimalplaces,
				currencies.rate,
				(SELECT SUM(supptrans.ovamount + supptrans.ovgst - supptrans.alloc)
					FROM supptrans
					WHERE suppliers.supplierid = supptrans.supplierno) AS balance
			FROM suppliers 
			INNER JOIN purchorders 
				ON  purchorders.supplierno = suppliers.supplierid 
			INNER JOIN purchorderdetails
				ON purchorders.orderno = purchorderdetails.orderno
			INNER JOIN currencies
				ON suppliers.currcode=currencies.currabrev
			WHERE purchorderdetails.completed=0
				AND purchorders.status IN ('Authorised', 'Printed', 'Pending')" .
				$WhereCountry . 	
				$WhereCurrency . "	
			GROUP BY 
				suppliers.supplierid
			ORDER BY suppliers.supplierid ASC";

	$ErrMsg = _('The SQL to find the suppliers with active Purchase Orders');
	$resultSuppliers = DB_query($sql,$ErrMsg);
	if (DB_num_rows($resultSuppliers) != 0){

		echo '<p class="page_title_text" align="center"><strong>' . "Financial planning for active (Authorised, Printed, Pending) Purchase Orders by Supplier" . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th>' . _('Code') . '</th>
							<th>' . _('Supplier Name') . '</th>
							<th>' . _('PO#') . '</th>
							<th>' . _('Order Date') . '</th>
							<th>' . _('Delivery Date') . '</th>
							<th>' . _('Order Value') . '</th>
							<th>' . _('Order Value in') . ' ' . $_SESSION['CompanyRecord']['currencydefault'] . '</th>
							<th>' . _('Supplier Balance') . '</th>
							<th>' . _('Pending') . '</th>
							<th>' . _('Pending in') . ' ' . $_SESSION['CompanyRecord']['currencydefault'] . '</th>
						</tr>';

		$TotalValueOrders = 0;
		$TotalValuePending = 0;
		
		while ($mySupplier = DB_fetch_array($resultSuppliers)) {
			echo $TableHeader;
			
			printf('<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					$mySupplier['supplierid'],
					$mySupplier['suppname'],
					'',
					'',
					'',
					'',
					'',
					locale_number_format($mySupplier['balance'],$mySupplier['decimalplaces']) . ' ' . $mySupplier['currcode'],
					'',
					''								
					);

			// Get the PO's for this supplier
			$sqlSupplier = "SELECT purchorders.orderno,
								purchorders.orddate,
								purchorders.deliverydate,
								purchorders.status,
								SUM(purchorderdetails.unitprice*purchorderdetails.quantityord) AS ordervalue
							FROM purchorders INNER JOIN purchorderdetails
								ON purchorders.orderno = purchorderdetails.orderno
							WHERE purchorderdetails.completed=0
								AND purchorders.status IN ('Authorised', 'Printed', 'Pending')		
								AND purchorders.supplierno = '" . $mySupplier['supplierid'] . "'
							GROUP BY purchorders.orderno
							ORDER BY purchorders.orderno ASC";
					 
			$ErrMsg = _('The bill of material could not be retrieved because');
			$SupplierResult = DB_query ($sqlSupplier,$ErrMsg);
			
			$TotalSupplierOwnCurrency = 0;
			$TotalSupplierFunctionalCurrency = 0;
			
			while ($myPOs = DB_fetch_array($SupplierResult)) {
				
				$TotalSupplierOwnCurrency += $myPOs['ordervalue'];
				$OrderValueFuntionalCurrency = $myPOs['ordervalue'] / $mySupplier['rate'];
				$TotalSupplierFunctionalCurrency += $OrderValueFuntionalCurrency;
				$CodeLink = '<a href="' . $RootPath . '/PO_OrderDetails.php?OrderNo=' . $myPOs['orderno'] . '">' . $myPOs['orderno'] . '</a>';
				
				printf('<td>%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						</tr>', 
						'',
						'',
						$CodeLink,
						ConvertSQLDate($myPOs['orddate']), 
						ConvertSQLDate($myPOs['deliverydate']), 
						locale_number_format($myPOs['ordervalue'],$mySupplier['decimalplaces']) . ' ' . $mySupplier['currcode'],
						locale_number_format($OrderValueFuntionalCurrency,$_SESSION['CompanyRecord']['decimalplaces']),
						'',
						'',
						''
						);
			}
			$PendingSupplierOwnCurrency = $TotalSupplierOwnCurrency + $mySupplier['balance'];
			$PendingSupplierFunctionalCurrency = $PendingSupplierOwnCurrency / $mySupplier['rate'];
			$TotalValueOrders += $TotalSupplierFunctionalCurrency;
			$TotalValuePending += $PendingSupplierFunctionalCurrency;
			printf('<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					'',
					'',
					'',
					'',
					_('Total Supplier'),
					locale_number_format($TotalSupplierOwnCurrency,$mySupplier['decimalplaces']) . ' ' . $mySupplier['currcode'],
					locale_number_format($TotalSupplierFunctionalCurrency,$_SESSION['CompanyRecord']['decimalplaces']),
					locale_number_format($mySupplier['balance'],$mySupplier['decimalplaces']) . ' ' . $mySupplier['currcode'],
					locale_number_format($PendingSupplierOwnCurrency,$mySupplier['decimalplaces']) . ' ' . $mySupplier['currcode'],
					locale_number_format($PendingSupplierFunctionalCurrency,$_SESSION['CompanyRecord']['decimalplaces'])
					);
		}
		$TableHeader = '<tr>
							<th></th>
							<th></th>
							<th></th>
							<th></th>
							<th></th>
							<th>' . _('TOTAL') . '</th>
							<th>' . _('Order Value in') . ' ' . $_SESSION['CompanyRecord']['currencydefault'] . '</th>
							<th>' . _('Balance') . '</th>
							<th></th>
							<th>' . _('Pending in') . ' ' . $_SESSION['CompanyRecord']['currencydefault'] . '</th>
						</tr>';
		echo $TableHeader;
		printf('<td>%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				<td>%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				</tr>', 
				'',
				'',
				'',
				'',
				'',
				_('Total All Supplier'),
				locale_number_format($TotalValueOrders,$_SESSION['CompanyRecord']['decimalplaces']),
				locale_number_format($TotalValueOrders-$TotalValuePending,$_SESSION['CompanyRecord']['decimalplaces']) . ' ' . $_SESSION['CompanyRecord']['currencydefault'],
				'',
				locale_number_format($TotalValuePending,$_SESSION['CompanyRecord']['decimalplaces'])
				);
		
		echo '</table>
				</div>';

	}else{
		prnMsg('No active PO to show');
	}
}

function display()  //####DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_#####
{
// Display form fields. This function is called the first time
// the page is called.

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
          <div>
			<br/>
			<br/>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text" align="center"><strong>' . "Financial planning for active (Authorised, Printed, Pending) Purchase Orders by Supplier" . '</strong></p>';

	echo '<table>';

		echo '<tr>
				<td>' . _('For Suppliers in Country') . ':</td>
				<td><select name="Country">';
		$sql = "SELECT DISTINCT(address6) AS country
				FROM suppliers
				ORDER BY address6";
		$CountryResult=DB_query($sql);
		echo '<option value="All">' . _('All Countries') . '</option>';
		while ($myrow=DB_fetch_array($CountryResult)){
			echo '<option value="' . $myrow['country'] . '">' . $myrow['country'] . '</option>';
		}
		echo '</select></td>
			</tr>';

		echo '<tr>
				<td>' . _('Using Currency') . ':</td>
				<td><select name="Currency">';
		$sql = "SELECT currabrev,
					currency
				FROM currencies
				ORDER BY currency";
		$CurrencyResult=DB_query($sql);
		echo '<option value="All">' . _('All Currencies') . '</option>';
		while ($myrow=DB_fetch_array($CurrencyResult)){
			echo '<option value="' . $myrow['currabrev'] . '">' . $myrow['currency'] . '</option>';
		}
		echo '</select></td>
			</tr>';

			
			
  echo '<tr><td>&nbsp;</td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="submit" value="' . _('Show POs financial status') . '" /></td>
		</tr>
		</table>
	<br />';
   echo '</div>
         </form>';

} // End of function display()

include('includes/footer.php');
?>
<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Financial planning for active (Authorised, Printed, Pending) Purchase Orders by Supplier');
include('includes/header.php');

if (isset($_POST['submit'])) {
    submit($_POST['Country'], $_POST['Currency'], $RootPath, $Title);
} else {
    display($Title);
}

function submit($Country, $Currency, $RootPath, $Title) {

    if ($Country != 'All'){
        $WhereCountry   = " AND suppliers.address6 = '". $Country ."' ";
    } else {
        $WhereCountry = ' ';
    }

	if ($Currency != 'All'){
		$WhereCurrency 	= " AND suppliers.currcode = '". $Currency ."' ";
	}else{
		$WhereCurrency = ' ';
	}

	/* look for suppliers with active PO's */
	$SQL = "SELECT suppliers.supplierid,
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
			GROUP BY suppliers.supplierid
			ORDER BY suppliers.supplierid ASC";

	$ErrMsg = __('The SQL to find the suppliers with active Purchase Orders');
	$ResultSuppliers = DB_query($SQL, $ErrMsg);
	if (DB_num_rows($ResultSuppliers) != 0){

		echo '<p class="page_title_text" align="center"><strong>' . $Title . '</strong></p>';
		echo '<div>
			<table class="selection">';
		$TableHeader = '<thead>
						<tr>
							<th>' . __('Code') . '</th>
							<th>' . __('Supplier Name') . '</th>
							<th>' . __('PO#') . '</th>
							<th>' . __('Order Date') . '</th>
							<th>' . __('Delivery Date') . '</th>
							<th>' . __('Order Value') . '</th>
							<th>' . __('Order Value in') . ' ' . $_SESSION['CompanyRecord']['currencydefault'] . '</th>
							<th>' . __('Supplier Balance') . '</th>
							<th>' . __('Pending') . '</th>
							<th>' . __('Pending in') . ' ' . $_SESSION['CompanyRecord']['currencydefault'] . '</th>
						</tr>
						</thead>
						<tbody>';

		$TotalValueOrders = 0;
		$TotalValuePending = 0;

		while ($mySupplier = DB_fetch_array($ResultSuppliers)) {
			echo $TableHeader;

			echo '<tr class="striped_row">
					<td>' . $mySupplier['supplierid'] . '</td>
					<td>' . $mySupplier['suppname'] . '</td>
					<td class="number"></td>
					<td></td>
					<td></td>
					<td class="number"></td>
					<td class="number"></td>
					<td class="number">' . locale_number_format($mySupplier['balance'],$mySupplier['decimalplaces']) . ' ' . $mySupplier['currcode'] . '</td>
					<td class="number"></td>
					<td class="number"></td>
				</tr>';

			// Get the PO's for this supplier
			$SQLSupplier = "SELECT purchorders.orderno,
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

			$ErrMsg = __('The bill of material could not be retrieved because');
			$SupplierResult = DB_query($SQLSupplier, $ErrMsg);

			$TotalSupplierOwnCurrency = 0;
			$TotalSupplierFunctionalCurrency = 0;

			while ($myPOs = DB_fetch_array($SupplierResult)) {

				$TotalSupplierOwnCurrency += $myPOs['ordervalue'];
				$OrderValueFuntionalCurrency = $myPOs['ordervalue'] / $mySupplier['rate'];
				$TotalSupplierFunctionalCurrency += $OrderValueFuntionalCurrency;
				$CodeLink = '<a href="' . $RootPath . '/PO_OrderDetails.php?OrderNo=' . $myPOs['orderno'] . '">' . $myPOs['orderno'] . '</a>';

				echo '<tr class="striped_row">
						<td></td>
						<td></td>
						<td class="number">' . $CodeLink . '</td>
						<td>' . ConvertSQLDate($myPOs['orddate']) . '</td>
						<td>' . ConvertSQLDate($myPOs['deliverydate']) . '</td>
						<td class="number">' . locale_number_format($myPOs['ordervalue'],$mySupplier['decimalplaces']) . ' ' . $mySupplier['currcode'] . '</td>
						<td class="number">' . locale_number_format($OrderValueFuntionalCurrency,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						<td class="number"></td>
						<td class="number"></td>
						<td class="number"></td>
					</tr>';
			}
			$PendingSupplierOwnCurrency = $TotalSupplierOwnCurrency + $mySupplier['balance'];
			$PendingSupplierFunctionalCurrency = $PendingSupplierOwnCurrency / $mySupplier['rate'];
			$TotalValueOrders += $TotalSupplierFunctionalCurrency;
			$TotalValuePending += $PendingSupplierFunctionalCurrency;
			echo '<tr class="striped_row">
					<td></td>
					<td></td>
					<td class="number"></td>
					<td></td>
					<td>' . __('Total Supplier') . '</td>
					<td class="number">' . locale_number_format($TotalSupplierOwnCurrency,$mySupplier['decimalplaces']) . ' ' . $mySupplier['currcode'] . '</td>
					<td class="number">' . locale_number_format($TotalSupplierFunctionalCurrency,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					<td class="number">' . locale_number_format($mySupplier['balance'],$mySupplier['decimalplaces']) . ' ' . $mySupplier['currcode'] . '</td>
					<td class="number">' . locale_number_format($PendingSupplierOwnCurrency,$mySupplier['decimalplaces']) . ' ' . $mySupplier['currcode'] . '</td>
					<td class="number">' . locale_number_format($PendingSupplierFunctionalCurrency,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				</tr>
				</tbody>';
		}
		echo '<tfooter>
				<tr>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					<th>' . __('TOTAL') . '</th>
					<th>' . __('Order Value in') . ' ' . $_SESSION['CompanyRecord']['currencydefault'] . '</th>
					<th>' . __('Balance') . '</th>
					<th></th>
					<th>' . __('Pending in') . ' ' . $_SESSION['CompanyRecord']['currencydefault'] . '</th>
				</tr>';
		echo '<tr>
				<td></td>
				<td></td>
				<td class="number"></td>
				<td></td>
				<td></td>
				<td class="number">' . __('Total All Suppliers') . '</td>
				<td class="number">' . locale_number_format($TotalValueOrders,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format($TotalValueOrders-$TotalValuePending,$_SESSION['CompanyRecord']['decimalplaces']) . ' ' . $_SESSION['CompanyRecord']['currencydefault'] . '</td>
				<td class="number"></td>
				<td class="number">' . locale_number_format($TotalValuePending,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
			</tr>
			</tfooter>
			</table>
			</div>';

	}else{
		prnMsg('No active PO to show');
	}
}

function display($Title)
{
	// Display form fields. This function is called the first time the page is called.

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
          <div>
			<br/>
			<br/>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text" align="center"><strong>' . $Title . '</strong></p>';

	echo '<fieldset>
          <legend>' . "Financial Planning Options" . '</legend>';

	echo '<field>
			<label for="Country">' .  __('For Suppliers in Country')  . ':</label>
			<select name="Country">';
	$SQL = "SELECT DISTINCT(address6) AS country
			FROM suppliers
			ORDER BY address6";
	$CountryResult = DB_query($SQL);
	echo '<option value="All">' . __('All Countries') . '</option>';
	while ($MyRow=DB_fetch_array($CountryResult)){
		echo '<option value="' . $MyRow['country'] . '">' . $MyRow['country'] . '</option>';
	}
	echo '</select>
		</field>';

	echo '<field>
			<label for="Currency">' .  __('Using Currency')  . ':</label>
			<select name="Currency">';
	$SQL = "SELECT currabrev,
				currency
			FROM currencies
			ORDER BY currency";
	$CurrencyResult = DB_query($SQL);
	echo '<option value="All">' . __('All Currencies') . '</option>';
	while ($MyRow=DB_fetch_array($CurrencyResult)){
		echo '<option value="' . $MyRow['currabrev'] . '">' . $MyRow['currency'] . '</option>';
	}
	echo '</select>
		</field>
		</fieldset>';
	echo '<div class="centre"><input type="submit" name="submit" value="' . __('Show POs financial status') . '" />
		</div>
		</form>';

} // End of function display()

include('includes/footer.php');

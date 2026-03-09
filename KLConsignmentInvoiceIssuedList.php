<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Print PTADU Consignment Invoices');

include(__DIR__ . '/includes/SQL_CommonFunctions.php');
include(__DIR__ . '/includes/UIGeneralFunctions.php');
include(__DIR__ . '/includes/KLUIGeneralFunctions.php');
include(__DIR__ . '/includes/KLDefines.php');
include(__DIR__ . '/includes/KLGeneralFunctions.php');

// The default company to Invoice from (PTADU).
if (!isset($_POST['CompanyFrom'])) {
	$_POST['CompanyFrom']='PTADU';
}

// default date from invoice is beginning of year
if (!isset($_POST['StartDate'])){
	$_POST['StartDate'] = ConvertSQLDate(Date('Y').'-01-01');
}

// default date to invoice is until Yesterday
if (!isset($_POST['EndDate'])){
	$_POST['EndDate'] = DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-1); 
}

if (isset($_POST['submit'])) {
	submit($Title, $_POST['CompanyFrom'], $_POST['StartDate'], $_POST['EndDate']);
} else {
	display($Title);
}

include(__DIR__ . '/includes/footer.php');


function submit($Title, $CompanyFrom, $StartDate, $EndDate) {
	include(__DIR__ . '/includes/header.php');

	$StartDate = FormatDateForSQL($StartDate);
	$EndDate = FormatDateForSQL($EndDate);

	//initialise no input errors
	$InputError = false;
	
	if (!$InputError){
		// get the conignment sales for the period
		
		$SQL = "SELECT klconsignment.partnercode,
					klconsignment.invoicedtopartner,
					SUM(klconsignment.qty * klconsignment.consignmentprice) AS valueinvoice
				FROM klconsignment
				WHERE klconsignment.companycode = '" . $CompanyFrom . "'
					AND klconsignment.invoicedtopartner >= '" . $StartDate . "'
					AND klconsignment.invoicedtopartner <= '" . $EndDate . "'
				GROUP BY klconsignment.invoicedtopartner, klconsignment.partnercode 
				ORDER BY klconsignment.invoicedtopartner, klconsignment.partnercode";

		$Result = DB_query($SQL);
		
		if (DB_num_rows($Result) != 0){
			$TableTitleText = "Consignment Invoices Issued by " . $CompanyFrom . " between " . ConvertSQLDate($StartDate) . " and " . ConvertSQLDate($EndDate);
			ShowTableTitle($TableTitleText);
			echo '<div>';
			echo '<table class="selection">
					<thead>
						<tr>
							<th>' . __('Partner') . '</th>
							<th>' . __('Date') . '</th>
							<th>' . __('Invoice Number') . '</th>
							<th>' . __('Goods') . '</th>
							<th>' . __('PPN') . '</th>
							<th>' . __('Total') . '</th>
						</tr>
					</thead>
					<tbody>';
		
			$NumberConsignmentInvoices = 0;
			$TotalInvoiceValue = 0;
			$TotalGoodsValue = 0;
			$TotalPPNValue = 0;

			while ($MyRow = DB_fetch_array($Result)) {

				$NumberConsignmentInvoices++;
				$TotalInvoiceValue += $MyRow['valueinvoice'];
				$GoodsInvoice = $MyRow['valueinvoice'] / ((100 + $_SESSION['PPN_Percent']) / 100);
				$PPNInvoice = $MyRow['valueinvoice'] - $GoodsInvoice;
				$TotalGoodsValue += $GoodsInvoice;
				$TotalPPNValue += $PPNInvoice;

				echo '<tr class="striped_row">
						<td>' . $MyRow['partnercode'] . '</td>
						<td>' . ConvertSQLDate($MyRow['invoicedtopartner']) . '</td>
						<td>' . CreateConsignmentInvoiceNumber($CompanyFrom, $MyRow['partnercode'], $MyRow['invoicedtopartner']) . '</td>
						<td class="number">' . locale_number_format($GoodsInvoice,0) . '</td>
						<td class="number">' . locale_number_format($PPNInvoice,0) . '</td>
						<td class="number">' . locale_number_format($MyRow['valueinvoice'],0) . '</td>
						</tr>';
			}

			echo '<tr class="striped_row">
					<td>TOTAL</td>
					<td></td>
					<td class="number">' . locale_number_format($NumberConsignmentInvoices,0) . '</td>
					<td class="number">' . locale_number_format($TotalGoodsValue,0) . '</td>
					<td class="number">' . locale_number_format($TotalPPNValue,0) . '</td>
					<td class="number">' . locale_number_format($TotalInvoiceValue,0) . '</td>
					</tr>';
			echo '</tbody>
				</table>
				</div>';
		}
	}
} // End of function submit()


function display($Title) {
// Display form fields. This function is called the first time
// the page is called.
	include(__DIR__ . '/includes/header.php');

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/magnifier.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '
		</p>';

	echo '<fieldset>';
	echo FixedField("CompanyFrom", "PTADU", 'From', '');
	echo FieldToSelectOneDate('StartDate', $_POST['StartDate'], __('Invoice Consignment Issued from'), '', '', 1, true, false);
	echo FieldToSelectOneDate('EndDate', $_POST['EndDate'], __('Invoice Consignment Issued to'), '', '', 2, true, false);
	echo '</fieldset>';
	echo OneButtonCenteredForm("submit", $Title, 3, false, false);
	echo '</form>';

} // End of function display()

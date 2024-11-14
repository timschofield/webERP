<?php

include('includes/session.php');
include('includes/SQL_CommonFunctions.inc');
include('includes/KLDefines.php');
include('includes/KLGeneralFunctions.php');

$Title = _('Print Consignment Invoices');

// The default company to Invoice from (PTADU).
if(!isset($_POST['CompanyFrom'])) {
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

include('includes/footer.php');




//####_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
function submit($Title, $CompanyFrom, $StartDate, $EndDate) {
	include('includes/header.php');

	$StartDate = FormatDateForSQL($StartDate);
	$EndDate = FormatDateForSQL($EndDate);

	//initialise no input errors
	$InputError = FALSE;
	
	if(!$InputError){
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

		$result = DB_query($SQL);
		
		if (DB_num_rows($result) != 0){
			echo '<p class="page_title_text" align="center"><strong>' . "Consignment Invoices Issued by " . $CompanyFrom . " between " . ConvertSQLDate($StartDate) . " and " . ConvertSQLDate($EndDate) . '</strong></p>';
			echo '<div>';
			echo '<table class="selection">';
		
			$NumberConsignmentInvoices = 0;
			$TotalInvoiceValue = 0;
			$TotalGoodsValue = 0;
			$TotalPPNValue = 0;

			$TableHeader = '<tr>
							<th>' . _('Partner') . '</th>
							<th>' . _('Date') . '</th>
							<th>' . _('Invoice Number') . '</th>
							<th>' . _('Goods') . '</th>
							<th>' . _('PPN') . '</th>
							<th>' . _('Total') . '</th>
						</tr>';
			echo $TableHeader;
			$k = 0; //row colour counter

			while ($myrow = DB_fetch_array($result)) {

				$NumberConsignmentInvoices++;
				$TotalInvoiceValue += $myrow['valueinvoice'];
				$GoodsInvoice = $myrow['valueinvoice'] / ((100 + PPN_PERCENT) / 100);
				$PPNInvoice = $myrow['valueinvoice'] - $GoodsInvoice;
				$TotalGoodsValue += $GoodsInvoice;
				$TotalPPNValue += $PPNInvoice;
			
				$k = StartEvenOrOddRow($k);

				printf('<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						</tr>', 
						$myrow['partnercode'], 
						ConvertSQLDate($myrow['invoicedtopartner']), 
						CreateConsignmentInvoiceNumber($CompanyFrom, $myrow['partnercode'], $myrow['invoicedtopartner']),
						locale_number_format($GoodsInvoice,0),
						locale_number_format($PPNInvoice,0),
						locale_number_format($myrow['valueinvoice'],0)
						);
			}

			$k = StartEvenOrOddRow($k);
			printf('<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					'TOTAL', 
					'', 
					locale_number_format($NumberConsignmentInvoices,0),
					locale_number_format($TotalGoodsValue,0),
					locale_number_format($TotalPPNValue,0),
					locale_number_format($TotalInvoiceValue,0)
					);
			echo '</table>
				</div>';
		}
	}
} // End of function submit()


function display($Title)  //####DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_#####
{
// Display form fields. This function is called the first time
// the page is called.
	include('includes/header.php');
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
		  <div>
			<br/>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '
		</p>';

	echo '<table class="selection">';

	echo '<tr>
			<td>' . 'From' . ':</td>
			<td><select name="CompanyFrom">';
	if($_POST['CompanyFrom']=="PTADU") {
		echo '<option selected="selected" value="PTADU">' . 'PT ADU' . '</option>';
//		echo '<option value="CASH">' . 'PT BB (temporary until end of stock)' . '</option>';
	} else {
//		echo '<option selected="selected" value="CASH">' . 'PT BB (temporary until end of stock)' . '</option>';
		echo '<option value="PTADU">' . 'PT ADU' . '</option>';
	}
	echo '</select></td></tr>';	

	echo '<tr>
			<td>' . _('Invoice Consignment Issued from') . '</td>
			<td><input type="text" class="date" alt="' .$_SESSION['DefaultDateFormat'] .'" name="StartDate" size="10" maxlength="10" value="' . $_POST['StartDate'] . '" /></td>
		</tr>';

	echo '<tr>
			<td>' . _('Invoice Consignment Issued to') . '</td>
			<td><input type="text" class="date" alt="' .$_SESSION['DefaultDateFormat'] .'" name="EndDate" size="10" maxlength="10" value="' . $_POST['EndDate'] . '" /></td>
		</tr>';

	echo '<tr><td>&nbsp;</td></tr>
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="submit" value="' . $Title . '" /></td>
		</tr>
		</table>
		<br />';
	echo '</div>
         </form>';
} // End of function display()

?>
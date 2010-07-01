<?php

/* $Id$*/

/* $Revision: 1.91 $ */

$PageSecurity = 1;

include('includes/session.inc');
$title=_('Main Menu');

/*The module link codes are hard coded in a switch statement below to determine the options to show for each tab */
$ModuleLink = array('orders', 'AR', 'AP', 'PO', 'stock', 'manuf', 'Contracts', 'GL', 'FA', 'PC', 'system');
/*The headings showing on the tabs accross the main index used also in WWW_Users for defining what should be visible to the user */
$ModuleList = array(_('Sales'),
					_('Receivables'),
					_('Payables'),
					_('Purchases'),
					_('Inventory'),
					_('Manufacturing'),
					_('Contracts'),
					_('General Ledger'),
					_('Asset Manager'),
					_('Petty Cash'),
					_('Setup'));

if (isset($_GET['Application'])){ /*This is sent by this page (to itself) when the user clicks on a tab */
	$_SESSION['Module'] = $_GET['Application'];
}

include('includes/header.inc');

if (count($_SESSION['AllowedPageSecurityTokens'])==1 and $SupplierLogin==0){

/* if there is only one security access and its 1 (it has to be 1 for this page came up at all)- it must be a customer log on
 * need to limit the menu to show only the customer accessible stuff this is what the page looks like for customers logging in
 */
?>

		<tr>
		<td class="menu_group_items">  <!-- Orders transaction options -->
		<table class="table_index">
			<tr>
			<td class="menu_group_item">
				<?php echo '<p>&bull; <a href="' . $rootpath . '/CustomerInquiry.php?' . sid . '&CustomerID=' . $_SESSION['CustomerID'] . '">' . _('Account Status') . '</a></p>'; ?>
			</td>
			</tr>
			<tr>
			<td class="menu_group_item">
				<?php echo '<p>&bull; <a href="' . $rootpath . '/SelectOrderItems.php?' . sid . '&NewOrder=Yes">' . _('Place An Order') . '</a></p>'; ?>
			</td>
			</tr>
			<tr>
			<td class="menu_group_item">
				<?php echo '<p>&bull; <a href="' . $rootpath . '/SelectCompletedOrder.php?' . sid . '&SelectedCustomer=' . $_SESSION['CustomerID'] . '">' . _('Order Status') . '</a></p>'; ?>
			</td>
			</tr>
		</table>
	</td>
<?php
	include('includes/footer.inc');
	exit;
} else if (count($_SESSION['AllowedPageSecurityTokens'])==1 and $SupplierLogin==1){
?>

		<tr>
		<td class="menu_group_items">  <!-- Orders transaction options -->
		<table class="table_index">
			<tr>
			<td class="menu_group_item">
				<?php echo '<p>&bull; <a href="' . $rootpath . '/SupplierTenders.php?' . sid . '">' . _('Supplier Tenders') . '</a></p>'; ?>
			</td>
			</tr>
		</table>
	</td>
<?php
} else {  /* Security settings DO allow seeing the main menu */

?>
		<table width="100%"><td width="10%" valign="top"><table class="main_menu" width="100%" cellspacing="0" cellpadding="0" border="0">

	<?php


	$i=0;

	while ($i < count($ModuleLink)){

		// This determines if the user has display access to the module see config.php and header.inc
		// for the authorisation and security code
		if ($_SESSION['ModulesEnabled'][$i]==1)	{

			// If this is the first time the application is loaded then it is possible that
			// SESSION['Module'] is not set if so set it to the first module that is enabled for the user
			if (!isset($_SESSION['Module'])OR $_SESSION['Module']==''){
				$_SESSION['Module']=$ModuleLink[$i];
			}
			if ($ModuleLink[$i] == $_SESSION['Module']){
				echo '<tr><td class="main_menu_selected"><a href="' . $_SERVER['PHP_SELF'] . '?' . sid . '&Application='. $ModuleLink[$i] . '">' . $ModuleList[$i] . '</a></td></tr>';
			} else {
				echo '<tr><td class="main_menu_unselected"><a href="' . $_SERVER['PHP_SELF'] . '?' . sid . '&Application='. $ModuleLink[$i] . '">' . $ModuleList[$i] . '</a></td></tr>';
			}
		}
		$i++;
	}

	?>
		</table></td><td>
	<?php


	switch ($_SESSION['Module']) {

	case 'orders': //Sales Orders
	?>

		<table width="100%">
			<tr>
			<td class="menu_group_area">
				<table width="100%" >

					<?php
  					// displays the main area headings
					  OptionHeadings();
					?>

					<tr>
					<td class="menu_group_items">  <!-- Orders transaction options -->
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SelectOrderItems.php?' .sid . '&NewOrder=Yes">' . _('Enter An Order or Quotation') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/CounterSales.php?' .sid . '">' . _('Enter Counter Sales') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PDFPickingList.php?' .sid . '">' . _('Print Picking Lists') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SelectSalesOrder.php?' . sid . '">' . _('Outstanding Sales Orders/Quotations') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SpecialOrder.php?' .sid . '&NewSpecial=Yes">' . _('Special Order') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SelectRecurringSalesOrder.php?' .sid . '">' . _('Recurring Order Template') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/RecurringSalesOrdersProcess.php?' .sid . '">' . _('Process Recurring Orders') . '</a></p>'; ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items"> <!-- Orders Inquiry options -->
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SelectCompletedOrder.php?' . sid . '">' . _('Order Inquiry') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PDFPriceList.php?' . sid . '">' . _('Print Price Lists') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PDFOrderStatus.php?' . sid . '">' . _('Order Status Report') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PDFOrdersInvoiced.php?' . sid . '">' . _('Orders Invoiced Reports') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/DailySalesInquiry.php?' . sid . '">' . _('Daily Sales Inquiry') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PDFDeliveryDifferences.php?' . sid . '">' . _('Order Delivery Differences Report') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PDFDIFOT.php?' . sid . '">' . _('Delivery In Full On Time (DIFOT) Report') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SalesInquiry.php?' . sid . '">' . _('Sales Order Detail Or Summary Inquiries') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/TopItems.php?' . sid . '">' . _('Top Sales Items Report') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo GetRptLinks('ord'); ?>
							</td>
							</tr>
						</table>
					</td>

					<td class="menu_group_items"> <!-- Orders Maintenance options -->
						<table width="100%">
							<tr>
							  <td>

						        </td>
							</tr>
						</table>
					</td>
					</tr>
				</table>
			</td>
			</tr>
		</table>
	<?php
		break;
	/* ****************** END OF ORDERS MENU ITEMS **************************** */


	Case 'AR': //Debtors Module

	unset($ReceiptBatch);
	unset($AllocTrans);

	?>
		<table width="100%">
			<tr>
			<td valign="top" class="menu_group_area">
				<table width="100%">

					<?php OptionHeadings(); ?>

					<tr>
					<td class="menu_group_items">
						<table width="100%"class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SelectSalesOrder.php?' . sid . '">' . _('Select Order to Invoice') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SelectCreditItems.php?' .sid . '&NewCredit=Yes">' . _('Create A Credit Note') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/CustomerReceipt.php?' . sid . '&NewReceipt=Yes">' . _('Enter Receipts') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">

								<?php echo '<p>&bull; <a href="' . $rootpath . '/CustomerAllocations.php?' . sid . '">' . _('Allocate Receipts or Credit Notes') . '</a></p>'; ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items">
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SelectCustomer.php?' . sid . '">' . _('Customer Transaction Inquiries') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/CustWhereAlloc.php?' . sid . '">' . _('Where Allocated Inquiry') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php
									if ($_SESSION['InvoicePortraitFormat']==0){
										echo '<p>&bull; <a href="' . $rootpath . '/PrintCustTrans.php?' . sid . '">' . _('Print Invoices or Credit Notes') . '</a></p>';
									} else {
										echo '<p>&bull; <a href="' . $rootpath . '/PrintCustTransPortrait.php?' . sid . '">' . _('Print Invoices or Credit Notes') . '</a></p>';
									}
								?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PrintCustStatements.php?' . sid . '">' . _('Print Statements') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SalesAnalRepts.php?' . sid . '">' . _('Sales Analysis Reports') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/AgedDebtors.php?' . sid . '">' . _('Aged Customer Balances/Overdues Report') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/CustomerTransInquiry.php?' . sid . '">' . _('Transaction Inquiries') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PDFBankingSummary.php?' . sid . '">' . _('Re-Print A Deposit Listing') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/DebtorsAtPeriodEnd.php?' . sid . '">' . _('Debtor Balances At A Prior Month End') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PDFCustomerList.php?' . sid . '">' . _('Customer Listing By Area/Salesperson') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SalesGraph.php?' . sid . '">' . _('Sales Graphs') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PDFCustTransListing.php?' . sid . '">' . _('List Daily Transactions') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo GetRptLinks('ar'); ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items">
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/Customers.php?' . sid . '">' . _('Add Customer') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SelectCustomer.php?' . sid . '">' . _('Customers') . '</a></p>'; ?>
							</td>
							</tr>
						</table>
					</td>
					</tr>
				</table>
			</td>
			</tr>
		</table>
	<?php

	/* ********************* 	END OF AR OPTIONS **************************** */
		break;

	Case 'AP': //Creditors Module

	?>
		<table width="100%">
			<tr>
			<td valign="top" class="menu_group_area">
				<table width="100%">

					<?php OptionHeadings(); ?>

					<tr>
					<td class="menu_group_items"> <!-- AP transaction options -->
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SelectSupplier.php?' . sid . '">' . _('Select Supplier') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . "/SupplierAllocations.php?" . sid . '">' . _('Supplier Allocations') . '</a></p>'; ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items">  <!-- AP Inquiries -->
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/AgedSuppliers.php?' . sid . '">' . _('Aged Supplier Report') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SuppPaymentRun.php?' . sid . '">' . _('Payment Run Report') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PDFRemittanceAdvice.php?' . sid . '">' . _('Remittance Advices') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/OutstandingGRNs.php?' . sid . '">' . _('Outstanding GRNs Report') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SupplierBalsAtPeriodEnd.php?' . sid . '">' . _('Supplier Balances At A Prior Month End') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PDFSuppTransListing.php?' . sid . '">' . _('List Daily Transactions') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo GetRptLinks('ap'); ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items">   <!-- AP Maintenance Options -->
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/Suppliers.php?' . sid . '">' . _('Add Supplier') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/Factors.php?' . sid . '">' . _('Maintain Factor Companies') . '</a></p>'; ?>
							</td>
							</tr>
						</table>
					</td>
					</tr>
				</table>
			</td>
			</tr>
		</table>
	<?php
		break;

	Case 'PO': /* Purchase Ordering */

	?>
		<table width="100%">
			<tr>
			<td valign="top" class="menu_group_area">
				<table width="100%">

					<?php OptionHeadings(); ?>

					<tr>
					<td class="menu_group_items">  <!-- PO Transactions -->
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PO_SelectOSPurchOrder.php?' . sid . '">' . _('Purchase Orders') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PO_Header.php?&NewOrder=Yes' . sid . '">' . _('Add Purchase Order') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/OffersReceived.php?' . sid . '">' . _('Process Tenders and Offers') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PO_AuthoriseMyOrders.php?' . sid . '">' . _('Orders to Authorise') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SelectSupplier.php?' . sid . '">' . _('Shipment Entry') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/Shipt_Select.php?' . sid . '">' . _('Select A Shipment') . '</a></p>'; ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items">  <!-- PO Inquiries -->
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PO_SelectPurchOrder.php?' . sid . '">' . _('Purchase Order Inquiry') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/POReport.php?' . sid . '">' . _('Purchase Order Detail Or Summary Inquiries') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo GetRptLinks('prch'); ?>
							</td>
							</tr>
					</table>
					</td>
					<td class="menu_group_items">   <!-- PO Maintenance -->
						<table width="100%" class="table_index">
						</table>
					</td>
					</tr>
				</table>
			</td>
			</tr>
		</table>
	<?php
		break;

	/* ****************************** END OF PURCHASING OPTIONS ******************************** */


	Case 'stock': //Inventory Module

	?>
		<table width="100%">
			<tr>
			<td valign="top" class="menu_group_area">
				<table width="100%">

					<?php OptionHeadings(); ?>

					<tr>
					<td class="menu_group_items">
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PO_SelectOSPurchOrder.php?' . sid . '">' . _('Receive Purchase Orders') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/StockLocTransfer.php' . sid . '">' . _('Bulk Inventory Transfer') . ' - ' . _('Dispatch') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/StockLocTransferReceive.php?' . sid . '">' . _('Bulk Inventory Transfer') . ' - ' . _('Receive') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/StockTransfers.php?' . sid . '">' . _('Inventory Location Transfers') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/StockAdjustments.php?NewAdjustment=Yes' . sid . '">' . _('Inventory Adjustments') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/ReverseGRN.php?' . sid . '">' . _('Reverse Goods Received') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/StockCounts.php?' . sid . '">' . _('Enter Stock Counts') . '</a></p>'; ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items">
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . "/StockSerialItemResearch.php?" . sid . '">' . _('Serial Item Research Tool') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . "/StockMovements.php?" . sid . '">' . _('Inventory Item Movements') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/StockStatus.php?' . sid . '">' . _('Inventory Item Status') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/StockUsage.php?' . sid . '">' . _('Inventory Item Usage') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/InventoryQuantities.php?' . sid . '">' . _('Inventory Quantities') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/ReorderLevel.php?' . sid . '">' . _('Reorder Level') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/StockDispatch.php?' . sid . '">' . _('Stock Dispatch') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/InventoryValuation.php?' . sid . '">' . _('Inventory Valuation Report') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/InventoryPlanning.php?' . sid . '">' . _('Inventory Planning Report') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/InventoryPlanningPrefSupplier.php?' . sid . '">' . _('Inventory Planning Based On Preferred Supplier Data') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/StockCheck.php?' . sid . '">' . _('Inventory Stock Check Sheets') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/StockQties_csv.php?' . sid . '">' . _('Make Inventory Quantities CSV') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PDFStockCheckComparison.php?' . sid . '">' . _('Compare Counts Vs Stock Check Data') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/StockLocMovements.php?' . sid . '">' . _('All Inventory Movements By Location/Date') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/StockLocStatus.php?' . sid . '">' . _('List Inventory Status By Location/Category') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/StockQuantityByDate.php?' . sid . '">' . _('Historical Stock Quantity By Location/Category') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PDFStockNegatives.php?' . sid . '">' . _('List Negative Stocks') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo GetRptLinks('inv'); ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items">
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/Stocks.php?' . sid . '">' . _('Add A New Item') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SelectProduct.php?' . sid . '">' . _('Select An Item') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SalesCategories.php?' . sid . '">' . _('Sales Category Maintenance') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PricesBasedOnMarkUp.php?' . sid . '">' . _('Add or Update Prices Based On Costs') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PricesByCost.php?' . sid . '">' . _('View or Update Prices Based On Costs') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/ReorderLevelLocation.php?' . sid . '">' . _('Reorder Level By Category/Location') . '</a></p>'; ?>
							</td>
							</tr>
						</table>
					</td>
					</tr>
				</table>
			</td>
			</tr>
		</table>
	<?php
		break;

	/* ****************************** END OF INVENTORY OPTIONS *********************************** */

	Case 'manuf': //Manufacturing Module

	?>
		<table width="100%">
			<tr>
			<td valign="top" class="menu_group_area">
				<table width="100%">

					<?php OptionHeadings(); ?>

					<tr>
					<td class="menu_group_items">
						<table width="100%" class="table_index">
							<tr>
							  <td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/WorkOrderEntry.php?' . sid . '">' . _('Work Order Entry') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SelectWorkOrder.php?' . sid . '">' . _('Select A Work Order') . '</a></p>'; ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items">
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SelectWorkOrder.php?' . sid . '">' . _('Select A Work Order') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/BOMInquiry.php?' . sid . '">' . _('Costed Bill Of Material Inquiry') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/WhereUsedInquiry.php?' . sid . '">' . _('Where Used Inquiry') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/BOMIndented.php?' . sid . '">' . _('Indented Bill Of Material Listing') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/BOMExtendedQty.php?' . sid . '">' . _('List Components Required') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/BOMIndentedReverse.php?' . sid . '">' . _('Indented Where Used Listing') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/MRPReport.php?' . sid . '">' . _('MRP') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/MRPShortages.php?' . sid . '">' . _('MRP Shortages') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/MRPPlannedPurchaseOrders.php?' . sid . '">' . _('MRP Suggested Purchase Orders') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/MRPPlannedWorkOrders.php?' . sid . '">' . _('MRP Suggested Work Orders') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/MRPReschedules.php?' . sid . '">' . _('MRP Reschedules Required') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo GetRptLinks('man'); ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items">
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/WorkCentres.php?' . sid . '">' . _('Work Centre') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/BOMs.php?' . sid . '">' . _('Bills Of Material') . '</a></p>'; ?>
							</td>
							</tr>

							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/MRPDemands.php?' . sid . '">' . _('Master Schedule') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/MRPCreateDemands.php?' . sid . '">' . _('Auto Create Master Schedule') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/MRP.php?' . sid . '">' . _('MRP Calculation') . '</a></p>'; ?>
							</td>
							</tr>

						</table>
					</td>
					</tr>
				</table>
			</td>
			</tr>
		</table>
	<?php
		break;



	Case 'system': //System setup

	?>
		<table width='100%'>
			<tr>
			<td valign="top" class="menu_group_area">
				<table width="100%" >
					<tr>
					<td class="menu_group_headers">
						<table>
							<tr>
							<td>
								<?php echo '<img src="'. $rootpath . '/css/' . $theme . '/images/company.png" title="' . _('General Setup Options') . '" alt="">'; ?>
							</td>
							<td class="menu_group_headers_text">
								<?php echo _('General'); ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_headers">
						<table>
							<tr>
							<td>
								<?php echo '<img src="'. $rootpath . '/css/' . $theme . '/images/ar.png" title="' . _('Receivables/Payables Setup') . '" alt="">'; ?>
							</td>
							<td class="menu_group_headers_text">
								<?php echo _('Receivables/Payables'); ?>

							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_headers">
						<table>
							<tr>
							<td>
								<?php echo '<img src="'. $rootpath . '/css/' . $theme . '/images/inventory.png" title="' . _('Inventory Setup') . '" alt="">'; ?>
							</td>
							<td class="menu_group_headers_text">
								<?php echo _('Inventory Setup'); ?>
							</td>
							</tr>
						</table>
					</td>


					</tr>
					<tr>

					<td class="menu_group_items">	<!-- Gereral set up options -->
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/CompanyPreferences.php?' . sid . '">' . _('Company Preferences') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SystemParameters.php?' . sid . '">' . _('Configuration Settings') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/WWW_Users.php?' . sid . '">' . _('User Maintenance') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/WWW_Access.php?' . sid . '">' . _('Role Permissions') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/BankAccounts.php?' . sid . '">' . _('Bank Accounts') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/Currencies.php?' . sid . '">' . _('Currency Maintenance') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/TaxAuthorities.php?' . sid . '">' . _('Tax Authorities and Rates Maintenance') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/TaxGroups.php?' . sid . '">' . _('Tax Group Maintenance') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/TaxProvinces.php?' . sid . '">' . _('Dispatch Tax Province Maintenance') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/TaxCategories.php?' . sid . '">' . _('Tax Category Maintenance') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PeriodsInquiry.php?' . sid . '">' . _('List Periods Defined') . ' <span style="font-size: 9px;">(' . _('Periods are automatically maintained') . ')</span></a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/reportwriter/admin/ReportCreator.php">' . _('Report Builder Tool') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/AuditTrail.php">' . _('View Audit Trail') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SystemCheck.php">' . _('View System Check') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/GeocodeSetup.php">' . _('Geocode Setup') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/FormDesigner.php">' . _('Form Layout Editor') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SMTPServer.php">' . _('SMTP Server Details') . '</a></p>'; ?>
							</td>
							</tr>
						</table>
					</td>

					<td class="menu_group_items">	<!-- AR/AP set-up options -->
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SalesTypes.php?' . sid . '">' . _('Sales Types') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/CustomerTypes.php?' . sid . '">' . _('Customer Types') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SupplierTypes.php?' . sid . '">' . _('Supplier Types') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/CreditStatus.php?' . sid . '">' . _('Credit Status') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PaymentTerms.php?' . sid . '">' . _('Payment Terms') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PO_AuthorisationLevels.php?' . sid . '">' . _('Set Purchase Order Authorisation levels') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PaymentMethods.php?' . sid . '">' . _('Payment Methods') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SalesPeople.php?' . sid . '">' . _('Sales People') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/Areas.php?' . sid . '">' . _('Sales Areas') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/Shippers.php?' . sid . '">' . _('Shippers') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SalesGLPostings.php?' . sid . '">' . _('Sales GL Interface Postings') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/COGSGLPostings.php?' . sid . '">' . _('COGS GL Interface Postings') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/FreightCosts.php?' . sid . '">' . _('Freight Costs Maintenance') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/DiscountMatrix.php?' . sid . '">' . _('Discount Matrix') . '</a></p>'; ?>
							</td>
							</tr>
						</table>
					</td>

					<td class="menu_group_items">	<!-- Inventory set-up options -->
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/StockCategories.php?' . sid . '">' . _('Inventory Categories Maintenance') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/Locations.php?' . sid . '">' . _('Inventory Locations Maintenance') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/DiscountCategories.php?' . sid . '">' . _('Discount Category Maintenance') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/UnitsOfMeasure.php?' . sid . '">' . _('Units of Measure') . '</a></p>'; ?>
							</td>
							</tr>
							<tr></tr>

							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/MRPCalendar.php?' . sid . '">' . _('MRP Available Production Days') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/MRPDemandTypes.php?' . sid . '">' . _('MRP Demand Types') . '</a></p>'; ?>
							</td>
							</tr>
						</table>
					</td>
					</tr>
				</table>
			</td>
			</tr>
		</table>
	<?php
		break;

	Case 'GL': //General Ledger

	?>
		<table width="100%">
			<tr>
			<td valign="top" class="menu_group_area">
				<table width="100%">		<!-- Gereral Ledger Option Headings-->

    					<?php OptionHeadings(); ?>

					<tr>
					<td class="menu_group_items"> <!-- General transactions options -->
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/Payments.php?' .sid . '&NewPayment=Yes">' . _('Bank Account Payments Entry') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/CustomerReceipt.php?' . sid . '&NewReceipt=Yes">' . _('Bank Account Receipts Entry') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/GLJournal.php?' .sid . '&NewJournal=Yes">' . _('Journal Entry') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/BankMatching.php?' .sid . '&Type=Payments">' . _('Bank Account Payments Matching') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/BankMatching.php?' .sid . '&Type=Receipts">' . _('Bank Account Receipts Matching') . '</a></p>'; ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items">  <!-- Gereral inquiry options -->
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/GLTrialBalance.php?' . sid . '">' . _('Trial Balance') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SelectGLAccount.php?' . sid . '">' . _('Account Inquiry') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/GLAccountReport.php?' . sid . '">' . _('Account Listing') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/GLAccountCSV.php?' . sid . '">' . _('Account Listing to CSV File') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/BankReconciliation.php?' . sid . '">' . _('Bank Account Reconciliation Statement') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PDFChequeListing.php?' . sid . '">' . _('Cheque Payments Listing') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . "/GLProfit_Loss.php?" . sid . '">' . _('Profit and Loss Statement') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/GLBalanceSheet.php?' . sid . '">' . _('Balance Sheet') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . "/GLTagProfit_Loss.php?" . sid . '">' . _('Tag Reports') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/Tax.php?' . sid . '">' . _('Tax Reports') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo GetRptLinks('gl'); ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items">  <!-- Gereral Ledger Maintenance options -->
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/GLAccounts.php?' . sid . '">' . _('GL Account') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/GLBudgets.php?' . sid . '">' . _('GL Budgets') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/AccountGroups.php?' . sid . '">' . _('Account Groups') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/AccountSections.php?' . sid . '">' . _('Account Sections') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/GLTags.php?' . sid . '">' . _('GL Tags') . '</a></p>'; ?>
							</td>
							</tr>
						</table>
					</td>
					</tr>
				</table>
			</td>
			</tr>
		</table>
	<?php
	break;
	Case 'FA': //Fixed Assets

	?>
		<table width="100%">
			<tr>
			<td valign="top" class="menu_group_area">
				<table width="100%">		<!-- Fixed Asset Option Headings-->
					<?php OptionHeadings(); ?>
					<tr>
					<td class="menu_group_items"> <!--  Fixed Asset transactions options -->
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/FixedAssetTransfer.php?' . sid . '">' . _('Change Asset Location') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/FixedAssetJournal.php?NewJournal=Yes' . sid . '">' . _('Depreciation Journal') . '</a></p>'; ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items"> <!-- Fixed Asset transactions options -->
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/FixedAssetRegister.php?' . sid . '">' . _('Asset Register') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo GetRptLinks('fa'); ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items"> <!-- General transactions options -->
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/FixedAssetCategories.php?' . sid . '">' . _('Asset Categories Maintenance') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/FixedAssetItems.php?' . sid . '">' . _('Add a new Asset Type') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/FixedAssetLocations.php?' . sid . '">' . _('Add or Maintain Asset Locations') . '</a></p>'; ?>
							</td>
							</tr>
						</table>
					</td>
				</table>
			</td>
			</tr>
		</table>
		<?php
	break;
	Case 'Contracts': //Contract Costing

	?>
		<table width="100%">
			<tr>
			<td valign="top" class="menu_group_area">
				<table width="100%">		<!-- Contract Costing Option Headings-->
					<?php OptionHeadings(); ?>
					<tr>
					<td class="menu_group_items"> <!-- Contract Costing transactions options -->
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/Contracts.php?' . sid . '">' . _('Create Contract') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
						</table>
					</td>
					<td class="menu_group_items"> <!-- Contract Costing Inquiries/Reports options -->
						<table width="100%" class="table_index">

							<tr>
							<td class="menu_group_item">
								<?php echo GetRptLinks('fa'); ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items"> <!-- Contract Costing Maintenance options -->
						<table width="100%" class="table_index">

						</table>
					</td>
				</table>
			</td>
			</tr>
		</table>
<?php
	break;

	Case 'PC': /* Petty Cash Module */

	?>
		<table width="100%">
			<tr>
			<td valign="top" class="menu_group_area">
				<table width="100%">

					<?php OptionHeadings(); ?>

					<tr>
					<td class="menu_group_items">  <!-- PC Transactions -->
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PcAssignCashToTab.php?' . sid . '">' . _('Assign Cash to PC Tab') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PcClaimExpensesFromTab.php' . sid . '">' . _('Claim Expenses From PC Tab') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PcAuthorizeExpenses.php?' . sid . '">' . _('Expenses Authoritation') . '</a></p>'; ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items">  <!-- Pc Inquiries -->
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PcReportTab.php?' . sid . '">' . _('PC Tab General Report') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo GetRptLinks('prch'); ?>
							</td>
							</tr>
					</table>
					</td>
					<td class="menu_group_items">   <!-- PC Maintenance -->
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PcTypeTabs.php?' . sid . '">' . _('Types of PC Tabs') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PcTabs.php?' . sid . '">' . _('PC Tabs') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PcExpenses.php?' . sid . '">' . _('PC Expenses') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PcExpensesTypeTab.php?' . sid . '">' . _('Expenses for Type of PC Tab') . '</a></p>'; ?>
							</td>
							</tr>
						</table>
					</td>
					</tr>
				</table>
			</td>
			</tr>
		</table>
	<?php
		break;

	} //end of module switch
} /* end of if security allows to see the full menu */

// all tables started are ended within this index script which means 2 outstanding from footer.

include('includes/footer.inc');

function OptionHeadings() {

global $rootpath, $theme;

?>

	<tr>
	<td class="menu_group_headers"> <!-- Orders option Headings -->
		<table>
			<tr>
			<td>
				<?php echo '<img src="' . $rootpath . '/css/' . $theme . '/images/transactions.png" title="' . _('Transactions') . '" alt="">'; ?>
			</td>
			<td class="menu_group_headers_text">
				<?php echo _('Transactions'); ?>
			</td>
			</tr>
		</table>
	</td>
	<td class="menu_group_headers">
		<table>
			<tr>
			<td>
				<?php echo '<img src="' . $rootpath . '/css/' . $theme . '/images/reports.png" title="' . _('Inquiries and Reports') . '" alt="">'; ?>
			</td>
			<td class="menu_group_headers_text">
				<?php echo _('Inquiries and Reports'); ?>
			</td>
			</tr>
		</table>
	</td>
	<td class="menu_group_headers">
		<table>
			<tr>
			<td>
				<?php echo '<img src="' . $rootpath . '/css/' . $theme . '/images/maintenance.png" title="' . _('Maintenance') . '" alt="">'; ?>
			</td>
			<td class="menu_group_headers_text">
				<?php echo _('Maintenance'); ?>
			</td>
			</tr>
		</table>
	</td>
	</tr>

<?php

}

function GetRptLinks($GroupID) {
/*
This function retrieves the reports given a certain group id as defined in /reports/admin/defaults.php
in the acssociative array $ReportGroups[]. It will fetch the reports belonging solely to the group
specified to create a list of links for insertion into a table to choose a report. Two table sections will
be generated, one for standard reports and the other for custom reports.
*/
	global $db, $rootpath;
	require_once('reportwriter/languages/en_US/reports.php');
	require_once('reportwriter/admin/defaults.php');

	$Title= array(_('Custom Reports'), _('Standard Reports and Forms'));

	$sql= "SELECT id, reporttype, defaultreport, groupname, reportname
		FROM reports ORDER BY groupname, reportname";
	$Result=DB_query($sql,$db,'','',false,true);
	$ReportList = '';
	while ($Temp = DB_fetch_array($Result)) $ReportList[] = $Temp;

	$RptLinks = '';
	for ($Def=1; $Def>=0; $Def--) {
		$RptLinks .= '<tr><td class="menu_group_headers"><div align="center">'.$Title[$Def].'</div></td></tr>';
		$NoEntries = true;
		if ($ReportList) { // then there are reports to show, show by grouping
			foreach ($ReportList as $Report) {
				if ($Report['groupname']==$GroupID AND $Report['defaultreport']==$Def) {
					$RptLinks .= '<tr><td class="menu_group_item">';
					$RptLinks .= '<a href="' . $rootpath . '/reportwriter/ReportMaker.php?action=go&reportid=' . $Report['id'] . '">' . _($Report['reportname']) . '</a>';
					$RptLinks .= '</td></tr>';
					$NoEntries = false;
				}
			}
			// now fetch the form groups that are a part of this group (List after reports)
			$NoForms = true;
			foreach ($ReportList as $Report) {
				$Group=explode(':',$Report['groupname']); // break into main group and form group array
				if ($NoForms AND $Group[0]==$GroupID AND $Report['reporttype']=='frm' AND $Report['defaultreport']==$Def) {
					$RptLinks .= '<tr><td class="menu_group_item">';
					$RptLinks .= '<img src="' . $rootpath . '/css/' . $_SESSION['Theme'] . '/images/folders.gif" width="16" height="13">&nbsp;';
					$RptLinks .= '<a href="' . $rootpath . '/reportwriter/FormMaker.php?id=' . $Report['groupname'] . '">';
					$RptLinks .= $FormGroups[$Report['groupname']] . '</a>';
					$RptLinks .= '</td></tr>';
					$NoForms = false;
					$NoEntries = false;
				}
			}
		}
		if ($NoEntries) $RptLinks .= '<tr><td class="menu_group_item">' . _('There are no reports to show!') . '</td></tr>';
	}
	return $RptLinks;
}

?>

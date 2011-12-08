<?php

/* $Id$*/

include('includes/session.inc');
$title=_('Main Menu');

/*The module link codes are hard coded in a switch statement below to determine the options to show for each tab */
$ModuleLink = array('orders', 'AR', 'AP', 'PO', 'stock', 'manuf',  'GL', 'FA', 'PC', 'system');
/*The headings showing on the tabs accross the main index used also in WWW_Users for defining what should be visible to the user */
$ModuleList = array(_('Sales'),
				_('Receivables'),
				_('Payables'),
				_('Purchases'),
				_('Inventory'),
				_('Manufacturing'),
				_('General Ledger'),
				_('Asset Manager'),
				_('Petty Cash'),
				_('Setup'));

if (isset($_GET['Application'])){ /*This is sent by this page (to itself) when the user clicks on a tab */
	$_SESSION['Module'] = $_GET['Application'];
}

include('includes/header.inc');

if (count($_SESSION['AllowedPageSecurityTokens'])==1 AND $SupplierLogin==0){

/* if there is only one security access and its 1 (it has to be 1 for this page came up at all)- it must be a customer log on 
 * need to limit the menu to show only the customer accessible stuff this is what the page looks like for customers logging in
 */
?>

		<tr>
		<td class="menu_group_items">  <!-- Orders transaction options -->
		<table class="table_index">
			<tr>
			<td class="menu_group_item">
				<?php echo '<p>&bull; <a href="' . $rootpath . '/CustomerInquiry.php?CustomerID=' . $_SESSION['CustomerID'] . '">' . _('Account Status') . '</a></p>'; ?>
			</td>
			</tr>
			<tr>
			<td class="menu_group_item">
				<?php echo '<p>&bull; <a href="' . $rootpath . '/SelectOrderItems.php?NewOrder=Yes">' . _('Place An Order') . '</a></p>'; ?>
			</td>
			</tr>
			<tr>
			<td class="menu_group_item">
				<?php echo '<p>&bull; <a href="' . $rootpath . '/SelectCompletedOrder.php?SelectedCustomer=' . $_SESSION['CustomerID'] . '">' . _('Order Status') . '</a></p>'; ?>
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
				<?php echo '<p>&bull; <a href="' . $rootpath . '/SupplierTenders.php">' . _('Supplier Tenders') . '</a></p>'; ?>
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
				echo '<tr><td class="main_menu_selected"><a href="' . htmlspecialchars($_SERVER['PHP_SELF']) . '?Application='. $ModuleLink[$i] . '">' . $ModuleList[$i] . '</a></td></tr>';
			} else {
				echo '<tr><td class="main_menu_unselected"><a href="' . htmlspecialchars($_SERVER['PHP_SELF']) . '?Application='. $ModuleLink[$i] . '">' . $ModuleList[$i] . '</a></td></tr>';
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
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SelectOrderItems.php?NewOrder=Yes">' . _('Enter An Order or Quotation') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/CounterSales.php">' . _('Enter Counter Sales') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PDFPickingList.php">' . _('Print Picking Lists') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SelectSalesOrder.php">' . _('Outstanding Sales Orders/Quotations') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SpecialOrder.php?NewSpecial=Yes">' . _('Special Order') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SelectRecurringSalesOrder.php">' . _('Recurring Order Template') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/RecurringSalesOrdersProcess.php">' . _('Process Recurring Orders') . '</a></p>'; ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items"> <!-- Orders Inquiry options -->
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SelectCompletedOrder.php">' . _('Order Inquiry') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PDFPriceList.php">' . _('Print Price Lists') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PDFOrderStatus.php">' . _('Order Status Report') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PDFOrdersInvoiced.php">' . _('Orders Invoiced Reports') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/DailySalesInquiry.php">' . _('Daily Sales Inquiry') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SalesByTypePeriodInquiry.php">' . _('Sales By Sales Type Inquiry') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SalesCategoryPeriodInquiry.php">' . _('Sales By Category Inquiry') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SalesTopItemsInquiry.php">' . _('Top Sellers Inquiry') . '</a></p>'; ?>
							</td>
							</tr>
							
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PDFDeliveryDifferences.php">' . _('Order Delivery Differences Report') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PDFDIFOT.php">' . _('Delivery In Full On Time (DIFOT) Report') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SalesInquiry.php">' . _('Sales Order Detail Or Summary Inquiries') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/TopItems.php">' . _('Top Sales Items Report') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PDFLowGP.php">' . _('Sales With Low Gross Profit Report') . '</a></p>'; ?>
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
						<table width="100%" class="table_index" >
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SelectContract.php">' . _('Select Contract') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/Contracts.php">' . _('Create Contract') . '</a></p>'; ?>
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
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SelectSalesOrder.php">' . _('Select Order to Invoice') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SelectCreditItems.php?NewCredit=Yes">' . _('Create A Credit Note') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/CustomerReceipt.php?NewReceipt=Yes&Type=Customer">' . _('Enter Receipts') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">

								<?php echo '<p>&bull; <a href="' . $rootpath . '/CustomerAllocations.php">' . _('Allocate Receipts or Credit Notes') . '</a></p>'; ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items">
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/CustWhereAlloc.php">' . _('Where Allocated Inquiry') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php
									if ($_SESSION['InvoicePortraitFormat']==0){
										echo '<p>&bull; <a href="' . $rootpath . '/PrintCustTrans.php">' . _('Print Invoices or Credit Notes') . '</a></p>';
									} else {
										echo '<p>&bull; <a href="' . $rootpath . '/PrintCustTransPortrait.php">' . _('Print Invoices or Credit Notes') . '</a></p>';
									}
								?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PrintCustStatements.php">' . _('Print Statements') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SalesAnalRepts.php">' . _('Sales Analysis Reports') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/AgedDebtors.php">' . _('Aged Customer Balances/Overdues Report') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PDFBankingSummary.php">' . _('Re-Print A Deposit Listing') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/DebtorsAtPeriodEnd.php">' . _('Debtor Balances At A Prior Month End') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PDFCustomerList.php">' . _('Customer Listing By Area/Salesperson') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SalesGraph.php">' . _('Sales Graphs') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PDFCustTransListing.php">' . _('List Daily Transactions') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/CustomerTransInquiry.php">' . _('Customer Transaction Inquiries') . '</a></p>'; ?>
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
								<?php echo '<p>&bull; <a href="' . $rootpath . '/Customers.php">' . _('Add Customer') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SelectCustomer.php">' . _('Customers') . '</a></p>'; ?>
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
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SelectSupplier.php">' . _('Select Supplier') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SupplierAllocations.php">' . _('Supplier Allocations') . '</a></p>'; ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items">  <!-- AP Inquiries -->
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/AgedSuppliers.php">' . _('Aged Supplier Report') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SuppPaymentRun.php">' . _('Payment Run Report') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PDFRemittanceAdvice.php">' . _('Remittance Advices') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/OutstandingGRNs.php">' . _('Outstanding GRNs Report') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SupplierBalsAtPeriodEnd.php">' . _('Supplier Balances At A Prior Month End') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PDFSuppTransListing.php">' . _('List Daily Transactions') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SupplierTransInquiry.php">' . _('Supplier Transaction Inquiries') . '</a></p>'; ?>
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
								<?php echo '<p>&bull; <a href="' . $rootpath . '/Suppliers.php">' . _('Add Supplier') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/Factors.php">' . _('Maintain Factor Companies') . '</a></p>'; ?>
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
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PO_SelectOSPurchOrder.php">' . _('Purchase Orders') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PO_Header.php?&NewOrder=Yes">' . _('Add Purchase Order') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/OffersReceived.php">' . _('Process Tenders and Offers') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PO_AuthoriseMyOrders.php">' . _('Orders to Authorise') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SelectSupplier.php">' . _('Shipment Entry') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/Shipt_Select.php">' . _('Select A Shipment') . '</a></p>'; ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items">  <!-- PO Inquiries and Reports -->
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PO_SelectPurchOrder.php">' . _('Purchase Order Inquiry') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/POReport.php">' . _('Purchase Order Detail Or Summary Inquiries') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SuppPriceList.php">' . _('Supplier Price List') . '</a></p>'; ?>
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
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PO_SelectOSPurchOrder.php">' . _('Receive Purchase Orders') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/StockLocTransfer.php">' . _('Bulk Inventory Transfer') . ' - ' . _('Dispatch') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/StockLocTransferReceive.php">' . _('Bulk Inventory Transfer') . ' - ' . _('Receive') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/StockTransfers.php?New=Yes">' . _('Inventory Location Transfers') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/StockAdjustments.php?NewAdjustment=Yes">' . _('Inventory Adjustments') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/ReverseGRN.php">' . _('Reverse Goods Received') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/StockCounts.php">' . _('Enter Stock Counts') . '</a></p>'; ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items">
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/StockSerialItemResearch.php">' . _('Serial Item Research Tool') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PDFPrintLabel.php">' . _('Print Price Labels') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/StockMovements.php">' . _('Inventory Item Movements') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/StockStatus.php">' . _('Inventory Item Status') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/StockUsage.php">' . _('Inventory Item Usage') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/InventoryQuantities.php">' . _('Inventory Quantities') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/ReorderLevel.php">' . _('Reorder Level') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/StockDispatch.php">' . _('Stock Dispatch') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/InventoryValuation.php">' . _('Inventory Valuation Report') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/InventoryPlanning.php">' . _('Inventory Planning Report') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/InventoryPlanningPrefSupplier.php">' . _('Inventory Planning Based On Preferred Supplier Data') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/StockCheck.php">' . _('Inventory Stock Check Sheets') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/StockQties_csv.php">' . _('Make Inventory Quantities CSV') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PDFStockCheckComparison.php">' . _('Compare Counts Vs Stock Check Data') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/StockLocMovements.php">' . _('All Inventory Movements By Location/Date') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/StockLocStatus.php">' . _('List Inventory Status By Location/Category') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/StockQuantityByDate.php">' . _('Historical Stock Quantity By Location/Category') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PDFStockNegatives.php">' . _('List Negative Stocks') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PDFPeriodStockTransListing.php?">' . _('Period Stock Transaction Listing') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PDFStockLocTransfer.php">' . _('Stock Transfer Note') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/ReprintGRN.php">' . _('Reprint GRN') . '</a></p>'; ?>
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
								<?php echo '<p>&bull; <a href="' . $rootpath . '/Stocks.php">' . _('Add A New Item') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SelectProduct.php">' . _('Select An Item') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SalesCategories.php">' . _('Sales Category Maintenance') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PricesBasedOnMarkUp.php">' . _('Add or Update Prices Based On Costs Or Other Price List') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PricesByCost.php">' . _('View or Update Prices Based On Costs') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/ReorderLevelLocation.php">' . _('Reorder Level By Category/Location') . '</a></p>'; ?>
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
								<?php echo '<p>&bull; <a href="' . $rootpath . '/WorkOrderEntry.php">' . _('Work Order Entry') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SelectWorkOrder.php">' . _('Select A Work Order') . '</a></p>'; ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items">
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SelectWorkOrder.php">' . _('Select A Work Order') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/BOMInquiry.php">' . _('Costed Bill Of Material Inquiry') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/WhereUsedInquiry.php">' . _('Where Used Inquiry') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/BOMListing.php">' . _('Bill Of Material Listing') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/BOMIndented.php">' . _('Indented Bill Of Material Listing') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/BOMExtendedQty.php">' . _('List Components Required') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/BOMIndentedReverse.php">' . _('Indented Where Used Listing') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/MRPReport.php">' . _('MRP') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/MRPShortages.php">' . _('MRP Shortages') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/MRPPlannedPurchaseOrders.php">' . _('MRP Suggested Purchase Orders') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/MRPPlannedWorkOrders.php">' . _('MRP Suggested Work Orders') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/MRPReschedules.php">' . _('MRP Reschedules Required') . '</a></p>'; ?>
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
								<?php echo '<p>&bull; <a href="' . $rootpath . '/WorkCentres.php">' . _('Work Centre') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/BOMs.php">' . _('Bills Of Material') . '</a></p>'; ?>
							</td>
							</tr>

							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/MRPDemands.php">' . _('Master Schedule') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/MRPCreateDemands.php">' . _('Auto Create Master Schedule') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/MRP.php">' . _('MRP Calculation') . '</a></p>'; ?>
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
								<?php echo '<p>&bull; <a href="' . $rootpath . '/CompanyPreferences.php">' . _('Company Preferences') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SystemParameters.php">' . _('Configuration Settings') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/WWW_Users.php">' . _('User Maintenance') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/WWW_Access.php">' . _('Role Permissions') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PageSecurity.php">' . _('Page Security Settings') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SecurityTokens.php">' . _('Define Security Tokens') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/BankAccounts.php">' . _('Bank Accounts') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/Currencies.php">' . _('Currency Maintenance') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/TaxAuthorities.php">' . _('Tax Authorities and Rates Maintenance') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/TaxGroups.php">' . _('Tax Group Maintenance') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/TaxProvinces.php">' . _('Dispatch Tax Province Maintenance') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/TaxCategories.php">' . _('Tax Category Maintenance') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PeriodsInquiry.php">' . _('List Periods Defined') . ' <span style="font-size: 9px;">(' . _('Periods are automatically maintained') . ')</span></a></p>'; ?>
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
								<?php echo '<p>&bull; <a href="' . $rootpath . '/Labels.php">' . _('Label Templates Maintenance') . '</a></p>'; ?>
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
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SalesTypes.php">' . _('Sales Types') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/CustomerTypes.php">' . _('Customer Types') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SupplierTypes.php">' . _('Supplier Types') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/CreditStatus.php">' . _('Credit Status') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PaymentTerms.php">' . _('Payment Terms') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PO_AuthorisationLevels.php">' . _('Set Purchase Order Authorisation levels') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PaymentMethods.php">' . _('Payment Methods') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SalesPeople.php">' . _('Sales People') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/Areas.php">' . _('Sales Areas') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/Shippers.php">' . _('Shippers') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SalesGLPostings.php">' . _('Sales GL Interface Postings') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/COGSGLPostings.php">' . _('COGS GL Interface Postings') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/FreightCosts.php">' . _('Freight Costs Maintenance') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/DiscountMatrix.php">' . _('Discount Matrix') . '</a></p>'; ?>
							</td>
							</tr>
						</table>
					</td>

					<td class="menu_group_items">	<!-- Inventory set-up options -->
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/StockCategories.php">' . _('Inventory Categories Maintenance') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/Locations.php">' . _('Inventory Locations Maintenance') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/DiscountCategories.php">' . _('Discount Category Maintenance') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/UnitsOfMeasure.php">' . _('Units of Measure') . '</a></p>'; ?>
							</td>
							</tr>
							<tr></tr>

							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/MRPCalendar.php">' . _('MRP Available Production Days') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/MRPDemandTypes.php">' . _('MRP Demand Types') . '</a></p>'; ?>
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
								<?php echo '<p>&bull; <a href="' . $rootpath . '/Payments.php?NewPayment=Yes">' . _('Bank Account Payments Entry') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/CustomerReceipt.php?NewReceipt=Yes&Type=GL">' . _('Bank Account Receipts Entry') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/GLJournal.php?NewJournal=Yes">' . _('Journal Entry') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/BankMatching.php?Type=Payments">' . _('Bank Account Payments Matching') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/BankMatching.php?Type=Receipts">' . _('Bank Account Receipts Matching') . '</a></p>'; ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items">  <!-- Gereral inquiry options -->
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/GLTrialBalance.php">' . _('Trial Balance') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SelectGLAccount.php">' . _('Account Inquiry') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/GLAccountReport.php">' . _('Account Listing') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/GLAccountCSV.php">' . _('Account Listing to CSV File') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/BankReconciliation.php">' . _('Bank Account Reconciliation Statement') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PDFChequeListing.php">' . _('Cheque Payments Listing') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/DailyBankTransactions.php">' . _('Daily Bank Transactions') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/GLProfit_Loss.php">' . _('Profit and Loss Statement') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/GLBalanceSheet.php">' . _('Balance Sheet') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/GLTagProfit_Loss.php">' . _('Tag Reports') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/Tax.php">' . _('Tax Reports') . '</a></p>'; ?>
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
								<?php echo '<p>&bull; <a href="' . $rootpath . '/GLAccounts.php">' . _('GL Account') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/GLBudgets.php">' . _('GL Budgets') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/AccountGroups.php">' . _('Account Groups') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/AccountSections.php">' . _('Account Sections') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/GLTags.php">' . _('GL Tags') . '</a></p>'; ?>
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
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/FixedAssetItems.php">' . _('Add a new Asset') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SelectAsset.php">' . _('Select an Asset') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/FixedAssetTransfer.php">' . _('Change Asset Location') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/FixedAssetDepreciation.php">' . _('Depreciation Journal') . '</a></p>'; ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items"> <!-- Fixed Asset transactions options -->
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/FixedAssetRegister.php">' . _('Asset Register') . '</a></p>'; ?>
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
								<?php echo '<p>&bull; <a href="' . $rootpath . '/FixedAssetCategories.php">' . _('Asset Categories Maintenance') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/FixedAssetLocations.php">' . _('Add or Maintain Asset Locations') . '</a></p>'; ?>
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
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PcAssignCashToTab.php">' . _('Assign Cash to PC Tab') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PcClaimExpensesFromTab.php">' . _('Claim Expenses From PC Tab') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PcAuthorizeExpenses.php">' . _('Expenses Authorisation') . '</a></p>'; ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items">  <!-- Pc Inquiries -->
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PcReportTab.php">' . _('PC Tab General Report') . '</a></p>'; ?>
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
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PcTypeTabs.php">' . _('Types of PC Tabs') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PcTabs.php">' . _('PC Tabs') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PcExpenses.php">' . _('PC Expenses') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PcExpensesTypeTab.php">' . _('Expenses for Type of PC Tab') . '</a></p>'; ?>
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

	$sql= "SELECT id,
				reporttype,
				defaultreport,
				groupname,
				reportname
			FROM reports
			ORDER BY groupname,
				reportname";
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
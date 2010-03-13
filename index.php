<?php

/* $Id$*/

/* $Revision: 1.91 $ */


$PageSecurity = 1;

include('includes/session.inc');
$title=_('Main Menu');

/*The module link codes are hard coded in a switch statement below to determine the options to show for each tab */
$ModuleLink = array('orders', 'AR', 'AP', 'PO', 'stock', 'manuf', 'GL', 'FA', 'PC', 'system');
/*The headings showing on the tabs accross the main index used also in WWW_Users for defining what should be visible to the user */
$ModuleList = array(_('Sales'), _('Receivables'), _('Payables'), _('Purchases'), _('Inventory'), _('Manufacturing'), _('General Ledger'), _('Asset Manager'), _('Petty Cash'), _('Setup'));

if (isset($_GET['Application'])){ /*This is sent by this page (to itself) when the user clicks on a tab */
	$_SESSION['Module'] = $_GET['Application'];
}

include('includes/header.inc');

if (count($_SESSION['AllowedPageSecurityTokens'])==1){

/* if there is only one security access and its 1 (it has to be 1 for this page came up at all)- it must be a customer log on need to limit the menu to show only the customer accessible stuff this is what the page looks like for customers logging in */
?>

		<tr>
		<td class="menu_group_items">  <!-- Orders transaction options -->
		<table class="table_index">
			<tr>
			<td class="menu_group_item">
				<?php echo '<p>&bull; <a href="' . $rootpath . '/CustomerInquiry.php?' . SID . '&CustomerID=' . $_SESSION['CustomerID'] . '">' . _('Account Status') . '</a></p>'; ?>
			</td>
			</tr>
			<tr>
			<td class="menu_group_item">
				<?php echo '<p>&bull; <a href="' . $rootpath . '/SelectOrderItems.php?' . SID . '&NewOrder=Yes">' . _('Place An Order') . '</a></p>'; ?>
			</td>
			</tr>
			<tr>
			<td class="menu_group_item">
				<?php echo '<p>&bull; <a href="' . $rootpath . '/SelectCompletedOrder.php?' . SID . '&SelectedCustomer=' . $_SESSION['CustomerID'] . '">' . _('Order Status') . '</a></p>'; ?>
			</td>
			</tr>
		</table>
	</td>
<?php
	include('includes/footer.inc');
	exit;
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
				echo '<tr><td class="main_menu_selected"><a href="' . $_SERVER['PHP_SELF'] . '?' . SID . '&Application='. $ModuleLink[$i] . '">' . $ModuleList[$i] . '</a></td></tr>';
			} else {
				echo '<tr><td class="main_menu_unselected"><a href="' . $_SERVER['PHP_SELF'] . '?' . SID . '&Application='. $ModuleLink[$i] . '">' . $ModuleList[$i] . '</a></td></tr>';
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
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SelectOrderItems.php?' .SID . '&NewOrder=Yes">' . _('Enter An Order or Quotation') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SelectSalesOrder.php?' . SID . '">' . _('Outstanding Sales Orders/Quotations') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SpecialOrder.php?' .SID . '&NewSpecial=Yes">' . _('Special Order') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SelectRecurringSalesOrder.php?' .SID . '">' . _('Recurring Order Template') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/RecurringSalesOrdersProcess.php?' .SID . '">' . _('Process Recurring Orders') . '</a></p>'; ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items"> <!-- Orders Inquiry options -->
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SelectCompletedOrder.php?' . SID . '">' . _('Order Inquiry') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PDFPriceList.php?' . SID . '">' . _('Print Price Lists') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PDFOrderStatus.php?' . SID . '">' . _('Order Status Report') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PDFOrdersInvoiced.php?' . SID . '">' . _('Orders Invoiced Reports') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/DailySalesInquiry.php?' . SID . '">' . _('Daily Sales Inquiry') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PDFDeliveryDifferences.php?' . SID . '">' . _('Order Delivery Differences Report') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PDFDIFOT.php?' . SID . '">' . _('Delivery In Full On Time (DIFOT) Report') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SalesInquiry.php?' . SID . '">' . _('Sales Order Detail Or Summary Inquiries') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/TopItems.php?' . SID . '">' . _('Top Sales Items Report') . '</a></p>'; ?>
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
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SelectSalesOrder.php?' . SID . '">' . _('Select Order to Invoice') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SelectCreditItems.php?' .SID . '&NewCredit=Yes">' . _('Create A Credit Note') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/CustomerReceipt.php?' . SID . '&NewReceipt=Yes">' . _('Enter Receipts') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">

								<?php echo '<p>&bull; <a href="' . $rootpath . '/CustomerAllocations.php?' . SID . '">' . _('Allocate Receipts or Credit Notes') . '</a></p>'; ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items">
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SelectCustomer.php?' . SID . '">' . _('Customer Transaction Inquiries') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/CustWhereAlloc.php?' . SID . '">' . _('Where Allocated Inquiry') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php
									if ($_SESSION['InvoicePortraitFormat']==0){
										echo '<p>&bull; <a href="' . $rootpath . '/PrintCustTrans.php?' . SID . '">' . _('Print Invoices or Credit Notes') . '</a></p>';
									} else {
										echo '<p>&bull; <a href="' . $rootpath . '/PrintCustTransPortrait.php?' . SID . '">' . _('Print Invoices or Credit Notes') . '</a></p>';
									}
								?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PrintCustStatements.php?' . SID . '">' . _('Print Statements') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SalesAnalRepts.php?' . SID . '">' . _('Sales Analysis Reports') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/AgedDebtors.php?' . SID . '">' . _('Aged Customer Balances/Overdues Report') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/CustomerTransInquiry.php?' . SID . '">' . _('Transaction Inquiries') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PDFBankingSummary.php?' . SID . '">' . _('Re-Print A Deposit Listing') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/DebtorsAtPeriodEnd.php?' . SID . '">' . _('Debtor Balances At A Prior Month End') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PDFCustomerList.php?' . SID . '">' . _('Customer Listing By Area/Salesperson') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SalesGraph.php?' . SID . '">' . _('Sales Graphs') . '</a></p>'; ?>
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
								<?php echo '<p>&bull; <a href="' . $rootpath . '/Customers.php?' . SID . '">' . _('Add Customer') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SelectCustomer.php?' . SID . '">' . _('Customers') . '</a></p>'; ?>
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
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SelectSupplier.php?' . SID . '">' . _('Select Supplier') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . "/SupplierAllocations.php?" . SID . '">' . _('Supplier Allocations') . '</a></p>'; ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items">  <!-- AP Inquiries -->
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/AgedSuppliers.php?' . SID . '">' . _('Aged Supplier Report') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SuppPaymentRun.php?' . SID . '">' . _('Payment Run Report') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PDFRemittanceAdvice.php?' . SID . '">' . _('Remittance Advices') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/OutstandingGRNs.php?' . SID . '">' . _('Outstanding GRNs Report') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SupplierBalsAtPeriodEnd.php?' . SID . '">' . _('Supplier Balances At A Prior Month End') . '</a></p>'; ?>
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
								<?php echo '<p>&bull; <a href="' . $rootpath . '/Suppliers.php?' . SID . '">' . _('Add Supplier') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/Factors.php?' . SID . '">' . _('Maintain Factor Companies') . '</a></p>'; ?>
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
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PO_SelectOSPurchOrder.php?' . SID . '">' . _('Purchase Orders') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PO_Header.php?&NewOrder=Yes' . SID . '">' . _('Add Purchase Order') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PO_AuthoriseMyOrders.php?' . SID . '">' . _('Orders to Authorise') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SelectSupplier.php?' . SID . '">' . _('Shipment Entry') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/Shipt_Select.php?' . SID . '">' . _('Select A Shipment') . '</a></p>'; ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items">  <!-- PO Inquiries -->
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PO_SelectPurchOrder.php?' . SID . '">' . _('Purchase Order Inquiry') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/POReport.php?' . SID . '">' . _('Purchase Order Detail Or Summary Inquiries') . '</a></p>'; ?>
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
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PO_SelectOSPurchOrder.php?' . SID . '">' . _('Receive Purchase Orders') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/StockLocTransfer.php' . SID . '">' . _('Bulk Inventory Transfer') . ' - ' . _('Dispatch') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/StockLocTransferReceive.php?' . SID . '">' . _('Bulk Inventory Transfer') . ' - ' . _('Receive') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/StockTransfers.php?' . SID . '">' . _('Inventory Location Transfers') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/StockAdjustments.php?' . SID . '">' . _('Inventory Adjustments') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/ReverseGRN.php?' . SID . '">' . _('Reverse Goods Received') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/StockCounts.php?' . SID . '">' . _('Enter Stock Counts') . '</a></p>'; ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items">
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . "/StockSerialItemResearch.php?" . SID . '">' . _('Serial Item Research Tool') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . "/StockMovements.php?" . SID . '">' . _('Inventory Item Movements') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/StockStatus.php?' . SID . '">' . _('Inventory Item Status') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/StockUsage.php?' . SID . '">' . _('Inventory Item Usage') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/InventoryQuantities.php?' . SID . '">' . _('Inventory Quantities') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/ReorderLevel.php?' . SID . '">' . _('Reorder Level') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/StockDispatch.php?' . SID . '">' . _('Stock Dispatch') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/InventoryValuation.php?' . SID . '">' . _('Inventory Valuation Report') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/InventoryPlanning.php?' . SID . '">' . _('Inventory Planning Report') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/InventoryPlanningPrefSupplier.php?' . SID . '">' . _('Inventory Planning Based On Preferred Supplier Data') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/StockCheck.php?' . SID . '">' . _('Inventory Stock Check Sheets') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/StockQties_csv.php?' . SID . '">' . _('Make Inventory Quantities CSV') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PDFStockCheckComparison.php?' . SID . '">' . _('Compare Counts Vs Stock Check Data') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/StockLocMovements.php?' . SID . '">' . _('All Inventory Movements By Location/Date') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/StockLocStatus.php?' . SID . '">' . _('List Inventory Status By Location/Category') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/StockQuantityByDate.php?' . SID . '">' . _('Historical Stock Quantity By Location/Category') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PDFStockNegatives.php?' . SID . '">' . _('List Negative Stocks') . '</a></p>'; ?>
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
								<?php echo '<p>&bull; <a href="' . $rootpath . '/Stocks.php?' . SID . '">' . _('Add A New Item') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SelectProduct.php?' . SID . '">' . _('Select An Item') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SalesCategories.php?' . SID . '">' . _('Sales Category Maintenance') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PricesBasedOnMarkUp.php?' . SID . '">' . _('Add or Update Prices Based On Costs') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PricesByCost.php?' . SID . '">' . _('View or Update Prices Based On Costs') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/ReorderLevelLocation.php?' . SID . '">' . _('Reorder Level By Category/Location') . '</a></p>'; ?>
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
								<?php echo '<p>&bull; <a href="' . $rootpath . '/WorkOrderEntry.php?' . SID . '">' . _('Work Order Entry') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SelectWorkOrder.php?' . SID . '">' . _('Select A Work Order') . '</a></p>'; ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items">
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SelectWorkOrder.php?' . SID . '">' . _('Select A Work Order') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/BOMInquiry.php?' . SID . '">' . _('Costed Bill Of Material Inquiry') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/WhereUsedInquiry.php?' . SID . '">' . _('Where Used Inquiry') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/BOMIndented.php?' . SID . '">' . _('Indented Bill Of Material Listing') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/BOMExtendedQty.php?' . SID . '">' . _('List Components Required') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/BOMIndentedReverse.php?' . SID . '">' . _('Indented Where Used Listing') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/MRPReport.php?' . SID . '">' . _('MRP') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/MRPShortages.php?' . SID . '">' . _('MRP Shortages') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/MRPPlannedPurchaseOrders.php?' . SID . '">' . _('MRP Suggested Purchase Orders') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/MRPPlannedWorkOrders.php?' . SID . '">' . _('MRP Suggested Work Orders') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/MRPReschedules.php?' . SID . '">' . _('MRP Reschedules Required') . '</a></p>'; ?>
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
								<?php echo '<p>&bull; <a href="' . $rootpath . '/WorkCentres.php?' . SID . '">' . _('Work Centre') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/BOMs.php?' . SID . '">' . _('Bills Of Material') . '</a></p>'; ?>
							</td>
							</tr>

							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/MRPDemands.php?' . SID . '">' . _('Master Schedule') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/MRPCreateDemands.php?' . SID . '">' . _('Auto Create Master Schedule') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/MRP.php?' . SID . '">' . _('MRP Calculation') . '</a></p>'; ?>
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
								<?php echo '<p>&bull; <a href="' . $rootpath . '/CompanyPreferences.php?' . SID . '">' . _('Company Preferences') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SystemParameters.php?' . SID . '">' . _('Configuration Settings') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/WWW_Users.php?' . SID . '">' . _('User Maintenance') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/WWW_Access.php?' . SID . '">' . _('Role Permissions') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/BankAccounts.php?' . SID . '">' . _('Bank Accounts') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/Currencies.php?' . SID . '">' . _('Currency Maintenance') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/TaxAuthorities.php?' . SID . '">' . _('Tax Authorities and Rates Maintenance') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/TaxGroups.php?' . SID . '">' . _('Tax Group Maintenance') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/TaxProvinces.php?' . SID . '">' . _('Dispatch Tax Province Maintenance') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/TaxCategories.php?' . SID . '">' . _('Tax Category Maintenance') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PeriodsInquiry.php?' . SID . '">' . _('List Periods Defined') . ' <span style="font-size: 9px;">(' . _('Periods are automatically maintained') . ')</span></a></p>'; ?>
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
						</table>
					</td>

					<td class="menu_group_items">	<!-- AR/AP set-up options -->
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SalesTypes.php?' . SID . '">' . _('Sales Types') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/CustomerTypes.php?' . SID . '">' . _('Customer Types') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/CreditStatus.php?' . SID . '">' . _('Credit Status') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PaymentTerms.php?' . SID . '">' . _('Payment Terms') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PO_AuthorisationLevels.php?' . SID . '">' . _('Set Purchase Order Authorisation levels') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PaymentMethods.php?' . SID . '">' . _('Payment Methods') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SalesPeople.php?' . SID . '">' . _('Sales People') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/Areas.php?' . SID . '">' . _('Sales Areas') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/Shippers.php?' . SID . '">' . _('Shippers') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SalesGLPostings.php?' . SID . '">' . _('Sales GL Interface Postings') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/COGSGLPostings.php?' . SID . '">' . _('COGS GL Interface Postings') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/FreightCosts.php?' . SID . '">' . _('Freight Costs Maintenance') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/DiscountMatrix.php?' . SID . '">' . _('Discount Matrix') . '</a></p>'; ?>
							</td>
							</tr>
						</table>
					</td>

					<td class="menu_group_items">	<!-- Inventory set-up options -->
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/StockCategories.php?' . SID . '">' . _('Inventory Categories Maintenance') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/Locations.php?' . SID . '">' . _('Inventory Locations Maintenance') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/DiscountCategories.php?' . SID . '">' . _('Discount Category Maintenance') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/UnitsOfMeasure.php?' . SID . '">' . _('Units of Measure') . '</a></p>'; ?>
							</td>
							</tr>
							<tr></tr>

							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/MRPCalendar.php?' . SID . '">' . _('MRP Available Production Days') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/MRPDemandTypes.php?' . SID . '">' . _('MRP Demand Types') . '</a></p>'; ?>
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
								<?php echo '<p>&bull; <a href="' . $rootpath . '/Payments.php?' .SID . '&NewPayment=Yes">' . _('Bank Account Payments Entry') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/CustomerReceipt.php?' . SID . '&NewReceipt=Yes">' . _('Bank Account Receipts Entry') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/GLJournal.php?' .SID . '&NewJournal=Yes">' . _('Journal Entry') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/BankMatching.php?' .SID . '&Type=Payments">' . _('Bank Account Payments Matching') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/BankMatching.php?' .SID . '&Type=Receipts">' . _('Bank Account Receipts Matching') . '</a></p>'; ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items">  <!-- Gereral inquiry options -->
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/GLTrialBalance.php?' . SID . '">' . _('Trial Balance') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/SelectGLAccount.php?' . SID . '">' . _('Account Inquiry') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/GLAccountReport.php?' . SID . '">' . _('Account Listing') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/GLAccountCSV.php?' . SID . '">' . _('Account Listing to CSV File') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/BankReconciliation.php?' . SID . '">' . _('Bank Account Reconciliation Statement') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PDFChequeListing.php?' . SID . '">' . _('Cheque Payments Listing') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . "/GLProfit_Loss.php?" . SID . '">' . _('Profit and Loss Statement') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/GLBalanceSheet.php?' . SID . '">' . _('Balance Sheet') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . "/GLTagProfit_Loss.php?" . SID . '">' . _('Tag Reports') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/Tax.php?' . SID . '">' . _('Tax Reports') . '</a></p>'; ?>
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
								<?php echo '<p>&bull; <a href="' . $rootpath . '/GLAccounts.php?' . SID . '">' . _('GL Account') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/GLBudgets.php?' . SID . '">' . _('GL Budgets') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/AccountGroups.php?' . SID . '">' . _('Account Groups') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/AccountSections.php?' . SID . '">' . _('Account Sections') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/GLTags.php?' . SID . '">' . _('GL Tags') . '</a></p>'; ?>
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
	Case 'FA': //General Ledger

	?>
		<table width="100%">
			<tr>
			<td valign="top" class="menu_group_area">
				<table width="100%">		<!-- Fixed Asset Option Headings-->
					<?php OptionHeadings(); ?>
					<tr>
					<td class="menu_group_items"> <!-- General transactions options -->
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/FixedAssetTransfer.php?' . SID . '">' . _('Change Asset Location') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/FixedAssetJournal.php?NewJournal=Yes' . SID . '">' . _('Depreciation Journal') . '</a></p>'; ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items"> <!-- General transactions options -->
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/FixedAssetRegister.php?' . SID . '">' . _('Asset Register') . '</a></p>'; ?>
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
								<?php echo '<p>&bull; <a href="' . $rootpath . '/FixedAssetCategories.php?' . SID . '">' . _('Asset Categories Maintenance') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/FixedAssetItems.php?' . SID . '">' . _('Add a new Asset Type') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/FixedAssetLocations.php?' . SID . '">' . _('Add or Maintain Asset Locations') . '</a></p>'; ?>
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
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PcAssignCashToTab.php?' . SID . '">' . _('Assign Cash to PC Tab') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PcClaimExpensesFromTab.php' . SID . '">' . _('Claim Expenses From PC Tab') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PcAuthorizeExpenses.php?' . SID . '">' . _('Expenses Authoritation') . '</a></p>'; ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items">  <!-- Pc Inquiries -->
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PcReportTab.php?' . SID . '">' . _('PC Tab General Report') . '</a></p>'; ?>
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
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PcTypeTabs.php?' . SID . '">' . _('Types of PC Tabs') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PcTabs.php?' . SID . '">' . _('PC Tabs') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PcExpenses.php?' . SID . '">' . _('PC Expenses') . '</a></p>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<p>&bull; <a href="' . $rootpath . '/PcExpensesTypeTab.php?' . SID . '">' . _('Expenses for Type of PC Tab') . '</a></p>'; ?>
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

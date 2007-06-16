<?php

/* $Revision: 1.59 $ */


$PageSecurity = 1;

include('includes/session.inc');
$title=_('Main Menu');

/*The module link codes are hard coded in a switch statement below to determine the options to show for each tab */
$ModuleLink = array('orders', 'AR', 'AP', 'PO', 'stock', 'manuf', 'GL', 'system');
/*The headings showing on the tabs accross the main index used also in WWW_Users for defining what should be visible to the user */
$ModuleList = array(_('Sales'), _('Receivables'), _('Payables'), _('Purchases'), _('Inventory'), _('Manufacturing'), _('General Ledger'), _('Setup'));

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
				<?php echo "<A HREF='" . $rootpath . '/CustomerInquiry.php?' .SID . '&CustomerID=' . $_SESSION['CustomerID'] . "'><LI>" . _('Account Status') . '</LI></A>'; ?>
			</td>
			</tr>
			<tr>
			<td class="menu_group_item">
				<?php echo "<A HREF='" . $rootpath . '/SelectOrderItems.php?' .SID . "&NewOrder=Yes'><LI>" . _('Place An Order') . '</LI></A>'; ?>
			</td>
			</tr>
										<tr>
			<td class="menu_group_item">
				<?php echo "<LI><A HREF='" . $rootpath . '/SelectCompletedOrder.php?' .SID . "&SelectedCustomer=" . $_SESSION['CustomerID'] . "'>" . _('Order Status') . '</A></LI>'; ?>
			</td>
			</tr>
		</table>
	</td>
<?php
	include('includes/footer.inc');
	exit;
} else {  /* Security settings DO allow seeing the main menu */

?>
		<table class="main_menu" width="100%" cellspacing="0" cellpadding="0" border="0">
			<tr>
			<td class="main_menu">
				<table class="main_menu">
					<tr>

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
				echo "<td class='main_menu_selected'><A HREF='". $_SERVER['PHP_SELF'] .'?'. SID . '&Application='. $ModuleLink[$i] ."'>". $ModuleList[$i] .'</A></td>';
			} else {
				echo "<td class='main_menu_unselected'><A HREF='". $_SERVER['PHP_SELF'] .'?'. SID . '&Application='. $ModuleLink[$i] ."'>". $ModuleList[$i] .'</A></td>';
			}
		}
		$i++;
	}

	?>
					</tr>
				</table>
			</td>
			</tr>
		</table>
	<?php


	switch ($_SESSION['Module']) {

	Case 'orders': //Sales Orders
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
								<?php echo "<A HREF='" . $rootpath . '/SelectOrderItems.php?' .SID . "&NewOrder=Yes'><LI>" . _('Place An Order') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/SelectSalesOrder.php?' . SID . "'><LI>" . _('Outstanding Sales Orders') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/SpecialOrder.php?' .SID . "&NewSpecial=Yes'><LI>" . _('Special Order') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/SelectRecurringSalesOrder.php?' .SID . "'><LI>" . _('Recurring Order Template') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/RecurringSalesOrdersProcess.php?' .SID . "'><LI>" . _('Process Recurring Orders') . '</LI></A>'; ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items"> <!-- Orders Inquiry options -->
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/SelectCompletedOrder.php?' . SID . "'><LI>" . _('Order Inquiry') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/PDFPriceList.php?' . SID . "'><LI>" . _('Print Price Lists') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/PDFOrderStatus.php?' . SID . "'><LI>" . _('Order Status Reports (Print)') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/PDFOrdersInvoiced.php?' . SID . "'><LI>" . _('Orders Invoiced Reports') . '</LI></A>'; ?>
							</td>
							</tr>

							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/PDFDeliveryDifferences.php?' . SID . "'><LI>" . _('Order Delivery Differences Report') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/PDFDIFOT.php?' . SID . "'><LI>" . _('Delivery In Full On Time (DIFOT) Report') . '</LI></A>'; ?>
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
								<?php echo "<A HREF='" . $rootpath . '/SelectSalesOrder.php?' . SID . "'><LI>" . _('Select Order to Invoice') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/SelectCreditItems.php?' .SID . "&NewCredit=Yes'><LI>" . _('Create A Credit Note') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/CustomerReceipt.php?' . SID . "'><LI>" . _('Enter Receipts') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">

								<?php echo "<A HREF='" . $rootpath . '/CustomerAllocations.php?' . SID . "'><LI>" . _('Allocate Receipts or Credit Notes') . '</LI></A>'; ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items">
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/SelectCustomer.php?' . SID . "'><LI>" . _('Customer Transaction Inquiries') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/CustWhereAlloc.php?' . SID . "'><LI>" . _('Where Allocated Inquiry') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php
									if ($_SESSION['InvoicePortraitFormat']==0){
										echo "<A HREF='" . $rootpath . '/PrintCustTrans.php?' . SID . "'><LI>" . _('Print Invoices or Credit Notes') . '</LI></A>';
									} else {
										echo "<A HREF='". $rootpath . "/PrintCustTransPortrait.php?" . SID . "'><LI>" . _('Print Invoices or Credit Notes') . '</LI></A>';
									}
								?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . "/PrintCustStatements.php?" . SID . "'><LI>" . _('Print Statements') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/SalesAnalRepts.php?' . SID . "'><LI>" . _('Sales Analysis Reports') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/AgedDebtors.php?' . SID . "'><LI>" . _('Aged Customer Balances/Overdues Report') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/CustomerTransInquiry.php?' . SID . "'><LI>" . _('Transaction Inquiries') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
							<?php echo "<A HREF='" . $rootpath . '/PDFBankingSummary.php?' . SID . "'><LI>" . _('Re-Print A Deposit Listing') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/DebtorsAtPeriodEnd.php?' . SID . "'><LI>" . _('Debtor Balances At A Prior Month End') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
							<?php echo "<A HREF='" . $rootpath . '/PDFCustomerList.php?' . SID . "'><LI>" . _('Customer Listing By Area/Salesperson') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
							<?php echo "<A HREF='" . $rootpath . '/SalesGraph.php?' . SID . "'><LI>" . _('Sales Graphs') . '</LI></A>'; ?>
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
								<?php echo "<A HREF='" . $rootpath . '/Customers.php?' . SID . "'><LI>" . _('Add Customer') . '</LI></A>'; ?>
							</td>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/SelectCustomer.php?' . SID . "'><LI>" . _('Customers') . '</LI></A>'; ?>
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
								<?php echo "<A HREF='" . $rootpath . '/SelectSupplier.php?' . SID . "'><LI>" . _('Select Supplier') . '</LI></A>'; ?>
							</td>
							</tr><tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . "/SupplierAllocations.php?" . SID . "'><LI>" . _('Supplier Allocations') . '</LI></A>'; ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items">  <!-- AP Inquiries -->
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/AgedSuppliers.php?' . SID . "'><LI>" . _('Aged Supplier Report') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/SuppPaymentRun.php?' . SID . "'><LI>" . _('Payment Run Report') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/OutstandingGRNs.php?' . SID . "'><LI>" . _('Outstanding GRNs Report') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/SupplierBalsAtPeriodEnd.php?' . SID . "'><LI>" . _('Creditor Balances At A Prior Month End') . '</LI></A>'; ?>
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
								<?php echo "<A HREF='" . $rootpath . '/Suppliers.php?' . SID . "'><LI>" . _('Add Supplier') . '</LI></A>'; ?>
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
								<?php echo "<A HREF='" . $rootpath . '/PO_SelectOSPurchOrder.php?' . SID . "'><LI>" . _('Purchase Orders') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/SelectSupplier.php?' . SID . "'><LI>" . _('Shipment Entry') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/Shipt_Select.php?' . SID . "'><LI>" . _('Select A Shipment') . "</LI></A>"; ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items">  <!-- PO Inquiries -->
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/PO_SelectPurchOrder.php?' . SID . "'><LI>" . _('Purchase Order Inquiry') . '</LI></A>'; ?>
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
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/PurchData.php?' . SID . "'><LI>" . _('Maintain Purchasing Data') . '</LI></A>'; ?>
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
								<?php echo "<A HREF='" . $rootpath . '/PO_SelectOSPurchOrder.php?' . SID . "'><LI>" . _('Receive Purchase Orders') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/StockLocTransfer.php' . SID . "'><LI>" . _('Bulk Inventory Transfer') . ' - ' . _('Dispatch') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/StockLocTransferReceive.php?' . SID . "'><LI>" . _('Bulk Inventory Transfer') . ' - ' . _('Receive') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/StockTransfers.php?' . SID . "'><LI>" . _('Inventory Location Transfers') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/StockAdjustments.php?' . SID . "'><LI>" . _('Inventory Adjustments') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/ReverseGRN.php?' . SID . "'><LI>" . _('Reverse Goods Received') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/StockCounts.php?' . SID . "'><LI>" . _('Enter Stock Counts') . '</LI></A>'; ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items">
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . "/StockSerialItemResearch.php?" . SID . "'><LI>" . _('Serial Item Research Tool') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . "/StockMovements.php?" . SID . "'><LI>" . _('Inventory Item Movements') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/StockStatus.php?' . SID . "'><LI>" . _('Inventory Item Status') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/StockUsage.php?' . SID . "'><LI>" . _('Inventory Item Usage') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/InventoryValuation.php?' . SID . "'><LI>" . _('Inventory Valuation Report') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/InventoryPlanning.php?' . SID . "'><LI>" . _('Inventory Planning Report') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/StockCheck.php?' . SID . "'><LI>" . _('Inventory Stock Check Sheets') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/StockQties_csv.php?' . SID . "'><LI>" . _('Make Inventory Quantities CSV') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/PDFStockCheckComparison.php?' . SID . "'><LI>" . _('Compare Counts Vs Stock Check Data') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/StockLocMovements.php?' . SID . "'><LI>" . _('All Inventory Movements By Location/Date') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/StockLocStatus.php?' . SID . "'><LI>" . _('List Inventory Status By Location/Category') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/StockQuantityByDate.php?' . SID . "'><LI>" . _('Historical Stock Quantity By Location/Category') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/PDFStockNegatives.php?' . SID . "'><LI>" . _('List Negative Stocks') . '</LI></A>'; ?>
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
								<?php echo "<A HREF='" . $rootpath . '/Stocks.php?' . SID . "'><LI>" . _('Add A New Item') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/SelectProduct.php?' . SID . "'><LI>" . _('Select An Item') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/SalesCategories.php?' . SID . "'><LI>" . _('Sales Category Maintenance') . '</LI></A>'; ?>
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
								<?php echo "<A HREF='" . $rootpath . '/WorkOrderEntry.php?' . SID . "'><LI>" . _('Work Order Enty') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/SelectWorkOrder.php?' . SID . "'><LI>" . _('Select A Work Order') . '</LI></A>'; ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items">
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/SelectWorkOrder.php?' . SID . "'><LI>" . _('Select A Work Order') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/BOMInquiry.php?' . SID . "'><LI>" . _('Costed Bill Of Material Inquiry') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/WhereUsedInquiry.php?' . SID . "'><LI>" . _('Where Used Inquiry') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/BOMListing.php?' . SID . "'><LI>" . _('Bills Of Material Listing') . '</LI></A>'; ?>
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
								<?php echo "<A HREF='" . $rootpath . '/WorkCentres.php?' . SID . "'><LI>" . _('Work Centre') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/BOMs.php?' . SID . "'><LI>" . _('Bills Of Material') . '</LI></A>'; ?>
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
								<?php echo '<img src="'.$rootpath.'/css/'.$theme.'/images/company.png" TITLE="' . _('General Setup Options') . '" ALT="">'; ?>
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
								<?php echo '<img src="'.$rootpath.'/css/'.$theme.'/images/ar.png" TITLE="' . _('Receivables/Payables Setup') . '" ALT="">'; ?>
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
								<?php echo '<img src="'.$rootpath.'/css/'.$theme.'/images/inventory.png" TITLE="' . _('Inventory Setup') . '" ALT="">'; ?>
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
								<?php echo "<A HREF='" . $rootpath . '/CompanyPreferences.php?' . SID . "'><LI>" . _('Company Preferences') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/SystemParameters.php?' . SID . "'><LI>" . _('Configuration Settings') . '</LI></A>'; ?>
							</td>
							</tr>

							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/WWW_Users.php?' . SID . "'><LI>" . _('User Accounts') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/WWW_Access.php?' . SID . "'><LI>" . _('Role Permissions') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/BankAccounts.php?' . SID . "'><LI>" . _('Bank Accounts') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/Currencies.php?' . SID . "'><LI>" . _('Currency Maintenance') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/TaxAuthorities.php?' . SID . "'><LI>" . _('Tax Authorities and Rates Maintenance') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/TaxGroups.php?' . SID . "'><LI>" . _('Tax Group Maintenance') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/TaxProvinces.php?' . SID . "'><LI>" . _('Dispatch Tax Province Maintenance') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/TaxCategories.php?' . SID . "'><LI>" . _('Tax Category Maintenance') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/PeriodsInquiry.php?' . SID . "'><LI>" . _('List Periods Defined') . ' <FONT SIZE=1>(' . _('Periods are automatically maintained') . ')</FONT></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<A HREF="' . $rootpath . '/reportwriter/admin/ReportCreator.php"><LI>' . _('Report Builder Tool') . '</LI></A>'; ?>
							</td>
							</tr>
						</table>
					</td>

					<td class="menu_group_items">	<!-- AR/AP set-up options -->
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/SalesTypes.php?' . SID . "'><LI>" . _('Sales Types') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/CreditStatus.php?' . SID . "'><LI>" . _('Credit Status') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/PaymentTerms.php?' . SID . "'><LI>" . _('Payment Terms') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/PaymentMethods.php?' . SID . "'><LI>" . _('Payment Methods') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/SalesPeople.php?' . SID . "'><LI>" . _('Sales People') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/Areas.php?' . SID . "'><LI>" . _('Sales Areas') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/Shippers.php?' . SID . "'><LI>" . _('Shippers') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/SalesGLPostings.php?' . SID . "'><LI>" . _('Sales GL Interface Postings') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/COGSGLPostings.php?' . SID . "'><LI>" . _('COGS GL Interface Postings') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/FreightCosts.php?' . SID . "'><LI>" . _('Freight Costs Maintenance') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/DiscountMatrix.php?' . SID . "'><LI>" . _('Discount Matrix') . '</LI></A>'; ?>
							</td>
							</tr>
						</table>
					</td>

					<td class="menu_group_items">	<!-- Inventory set-up options -->
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/StockCategories.php?' . SID . "'><LI>" . _('Inventory Categories Maintenance') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/Locations.php?' . SID . "'><LI>" . _('Inventory Locations Maintenance') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/DiscountCategories.php?' . SID . "'><LI>" . _('Discount Category Maintenance') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/UnitsOfMeasure.php?' . SID . "'><LI>" . _('Units of Measure') . '</LI></A>'; ?>
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
								<?php echo "<A HREF='" . $rootpath . '/Payments.php?' .SID . "&NewPayment=Yes'><LI>" . _('Bank Account Payments') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/CustomerReceipt.php?' . SID . "'><LI>" . _('Bank Account Receipts') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/GLJournal.php?' .SID . "&NewJournal=Yes'><LI>" . _('Journal Entry') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/BankMatching.php?' .SID . "&Type=Receipts'><LI>" . _('Bank Deposits Matching') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/BankMatching.php?' .SID . "&Type=Payments'><LI>" . _('Bank Payments Matching') . '</LI></A>'; ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items">  <!-- Gereral inquiry options -->
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/GLTrialBalance.php?' . SID . "'><LI>" . _('Trial Balance') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/SelectGLAccount.php?' . SID . "'><LI>" . _('Account Inquiry') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/BankReconciliation.php?' . SID . "'><LI>" . _('Bank Reconciliation Statement') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/PDFChequeListing.php?' . SID . "'><LI>" . _('Cheque Payments Listing') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . "/GLProfit_Loss.php?" . SID . "'><LI>" . _('Profit and Loss Statement') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/GLBalanceSheet.php?' . SID . "'><LI>" . _('Balance Sheet') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/Tax.php?' . SID . "'><LI>" . _('Tax Reports') . '</LI></A>'; ?>
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
								<?php echo "<A HREF='" . $rootpath . '/GLAccounts.php?' . SID . "'><LI>" . _('GL Account') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/AccountGroups.php?' . SID . "'><LI>" . _('Account Groups') . '</LI></A>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<A HREF='" . $rootpath . '/AccountSections.php?' . SID . "'><LI>" . _('Account Sections') . '</LI></A>'; ?>
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
				<?php echo '<img src="'.$rootpath.'/css/'.$theme.'/images/transactions.png" TITLE="' . _('Transactions') . '" ALT="">'; ?>
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
				<?php echo '<img src="'.$rootpath.'/css/'.$theme.'/images/reports.png" TITLE="' . _('Inquiries and Reports') . '" ALT="">'; ?>
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
				<?php echo '<img src="'.$rootpath.'/css/'.$theme.'/images/maintenance.png" TITLE="' . _('Maintenance') . '" ALT="">'; ?>
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
					$RptLinks .= '<A HREF="'.$rootpath.'/reportwriter/ReportMaker.php?action=go&reportid='.$Report['id'].'"><LI>'._($Report['reportname']).'</LI></A>';
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
					$RptLinks .= '<img src="'.$rootpath.'/css/'.$_SESSION['Theme'].'/images/folders.gif" width="16" height="13">&nbsp;';
					$RptLinks .= '<A HREF="'.$rootpath.'/reportwriter/FormMaker.php?id='.$Report['groupname'].'">';
					$RptLinks .= $FormGroups[$Report['groupname']].'</A>';
					$RptLinks .= '</td></tr>';
					$NoForms = false;
					$NoEntries = false;
				}
			}
		}
		if ($NoEntries) $RptLinks .= '<tr><td class="menu_group_item">'._('There are no reports to show!').'</td></tr>';
	}
	return $RptLinks;
}

?>
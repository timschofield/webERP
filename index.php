<?php

/* $Revision: 1.83 $ */


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
				<?php echo "<a href='" . $rootpath . '/CustomerInquiry.php?' .SID . '&CustomerID=' . $_SESSION['CustomerID'] . "'><LI>" . _('Account Status') . '</LI></a>'; ?>
			</td>
			</tr>
			<tr>
			<td class="menu_group_item">
				<?php echo "<a href='" . $rootpath . '/SelectOrderItems.php?' .SID . "&NewOrder=Yes'><LI>" . _('Place An Order') . '</LI></a>'; ?>
			</td>
			</tr>
										<tr>
			<td class="menu_group_item">
				<?php echo "<LI><a href='" . $rootpath . '/SelectCompletedOrder.php?' .SID . "&SelectedCustomer=" . $_SESSION['CustomerID'] . "'>" . _('Order Status') . '</a></LI>'; ?>
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
				echo "<td class='main_menu_selected'><a href='". $_SERVER['PHP_SELF'] .'?'. SID . '&Application='. $ModuleLink[$i] ."'>". $ModuleList[$i] .'</a></td>';
			} else {
				echo "<td class='main_menu_unselected'><a href='". $_SERVER['PHP_SELF'] .'?'. SID . '&Application='. $ModuleLink[$i] ."'>". $ModuleList[$i] .'</a></td>';
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
								<?php echo "<a href='" . $rootpath . '/SelectOrderItems.php?' .SID . "&NewOrder=Yes'><LI>" . _('Enter An Order or Quotation') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/SelectSalesOrder.php?' . SID . "'><LI>" . _('Outstanding Sales Orders/Quotations') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/SpecialOrder.php?' .SID . "&NewSpecial=Yes'><LI>" . _('Special Order') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/SelectRecurringSalesOrder.php?' .SID . "'><LI>" . _('Recurring Order Template') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/RecurringSalesOrdersProcess.php?' .SID . "'><LI>" . _('Process Recurring Orders') . '</LI></a>'; ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items"> <!-- Orders Inquiry options -->
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/SelectCompletedOrder.php?' . SID . "'><LI>" . _('Order Inquiry') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/PDFPriceList.php?' . SID . "'><LI>" . _('Print Price Lists') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/PDFOrderStatus.php?' . SID . "'><LI>" . _('Order Status Reports (Print)') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/PDFOrdersInvoiced.php?' . SID . "'><LI>" . _('Orders Invoiced Reports') . '</LI></a>'; ?>
							</td>
							</tr>

							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/PDFDeliveryDifferences.php?' . SID . "'><LI>" . _('Order Delivery Differences Report') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/PDFDIFOT.php?' . SID . "'><LI>" . _('Delivery In Full On Time (DIFOT) Report') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/SalesInquiry.php?' . SID . "'><LI>" . _('Sales Order Detail Or Summary Inquiries') . '</LI></a>'; ?>
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
								<?php echo "<a href='" . $rootpath . '/SelectSalesOrder.php?' . SID . "'><LI>" . _('Select Order to Invoice') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/SelectCreditItems.php?' .SID . "&NewCredit=Yes'><LI>" . _('Create A Credit Note') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/CustomerReceipt.php?' . SID . "&NewReceipt=Yes'><LI>" . _('Enter Receipts') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">

								<?php echo "<a href='" . $rootpath . '/CustomerAllocations.php?' . SID . "'><LI>" . _('Allocate Receipts or Credit Notes') . '</LI></a>'; ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items">
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/SelectCustomer.php?' . SID . "'><LI>" . _('Customer Transaction Inquiries') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/CustWhereAlloc.php?' . SID . "'><LI>" . _('Where Allocated Inquiry') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php
									if ($_SESSION['InvoicePortraitFormat']==0){
										echo "<a href='" . $rootpath . '/PrintCustTrans.php?' . SID . "'><LI>" . _('Print Invoices or Credit Notes') . '</LI></a>';
									} else {
										echo "<a href='". $rootpath . "/PrintCustTransPortrait.php?" . SID . "'><LI>" . _('Print Invoices or Credit Notes') . '</LI></a>';
									}
								?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/PrintCustStatements.php?" . SID . "'><LI>" . _('Print Statements') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/SalesAnalRepts.php?' . SID . "'><LI>" . _('Sales Analysis Reports') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/AgedDebtors.php?' . SID . "'><LI>" . _('Aged Customer Balances/Overdues Report') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/CustomerTransInquiry.php?' . SID . "'><LI>" . _('Transaction Inquiries') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
							<?php echo "<a href='" . $rootpath . '/PDFBankingSummary.php?' . SID . "'><LI>" . _('Re-Print A Deposit Listing') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/DebtorsAtPeriodEnd.php?' . SID . "'><LI>" . _('Debtor Balances At A Prior Month End') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
							<?php echo "<a href='" . $rootpath . '/PDFCustomerList.php?' . SID . "'><LI>" . _('Customer Listing By Area/Salesperson') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
							<?php echo "<a href='" . $rootpath . '/SalesGraph.php?' . SID . "'><LI>" . _('Sales Graphs') . '</LI></a>'; ?>
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
								<?php echo "<a href='" . $rootpath . '/Customers.php?' . SID . "'><LI>" . _('Add Customer') . '</LI></a>'; ?>
							</td>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/SelectCustomer.php?' . SID . "'><LI>" . _('Customers') . '</LI></a>'; ?>
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
								<?php echo "<a href='" . $rootpath . '/SelectSupplier.php?' . SID . "'><LI>" . _('Select Supplier') . '</LI></a>'; ?>
							</td>
							</tr><tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/SupplierAllocations.php?" . SID . "'><LI>" . _('Supplier Allocations') . '</LI></a>'; ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items">  <!-- AP Inquiries -->
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/AgedSuppliers.php?' . SID . "'><LI>" . _('Aged Supplier Report') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/SuppPaymentRun.php?' . SID . "'><LI>" . _('Payment Run Report') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/OutstandingGRNs.php?' . SID . "'><LI>" . _('Outstanding GRNs Report') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/SupplierBalsAtPeriodEnd.php?' . SID . "'><LI>" . _('Supplier Balances At A Prior Month End') . '</LI></a>'; ?>
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
								<?php echo "<a href='" . $rootpath . '/Suppliers.php?' . SID . "'><LI>" . _('Add Supplier') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/Factors.php?' . SID . "'><LI>" . _('Maintain Factor Companies') . '</LI></a>'; ?>
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
								<?php echo "<a href='" . $rootpath . '/PO_SelectOSPurchOrder.php?' . SID . "'><LI>" . _('Purchase Orders') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/PO_Header.php?&NewOrder=Yes' . SID . "'><LI>" . _('Add A Purchase Order') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/SelectSupplier.php?' . SID . "'><LI>" . _('Shipment Entry') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/Shipt_Select.php?' . SID . "'><LI>" . _('Select A Shipment') . "</LI></a>"; ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items">  <!-- PO Inquiries -->
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/PO_SelectPurchOrder.php?' . SID . "'><LI>" . _('Purchase Order Inquiry') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/POReport.php?' . SID . "'><LI>" . _('Purchase Order Detail Or Summary Inquiries') . '</LI></a>'; ?>
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
								<?php echo "<a href='" . $rootpath . '/PurchData.php?' . SID . "'><LI>" . _('Maintain Purchasing Data') . '</LI></a>'; ?>
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
								<?php echo "<a href='" . $rootpath . '/PO_SelectOSPurchOrder.php?' . SID . "'><LI>" . _('Receive Purchase Orders') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/StockLocTransfer.php' . SID . "'><LI>" . _('Bulk Inventory Transfer') . ' - ' . _('Dispatch') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/StockLocTransferReceive.php?' . SID . "'><LI>" . _('Bulk Inventory Transfer') . ' - ' . _('Receive') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/StockTransfers.php?' . SID . "'><LI>" . _('Inventory Location Transfers') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/StockAdjustments.php?' . SID . "'><LI>" . _('Inventory Adjustments') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/ReverseGRN.php?' . SID . "'><LI>" . _('Reverse Goods Received') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/StockCounts.php?' . SID . "'><LI>" . _('Enter Stock Counts') . '</LI></a>'; ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items">
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/StockSerialItemResearch.php?" . SID . "'><LI>" . _('Serial Item Research Tool') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/StockMovements.php?" . SID . "'><LI>" . _('Inventory Item Movements') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/StockStatus.php?' . SID . "'><LI>" . _('Inventory Item Status') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/StockUsage.php?' . SID . "'><LI>" . _('Inventory Item Usage') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
                                                        <td class="menu_group_item">
                                                                <?php echo "<a href='" . $rootpath . '/InventoryQuantities.php?' . SID . "'><LI>" . _('Inventory Quantities') . '</LI></a>'; ?>
                                                        </td>
                                                        </tr>
							<tr>
                                                        <td class="menu_group_item">
                                                                <?php echo "<a href='" . $rootpath . '/ReorderLevel.php?' . SID . "'><LI>" . _('Reorder Level') . '</LI></a>'; ?>
                                                        </td>
                                                        </tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/InventoryValuation.php?' . SID . "'><LI>" . _('Inventory Valuation Report') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/InventoryPlanning.php?' . SID . "'><LI>" . _('Inventory Planning Report') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/InventoryPlanningPrefSupplier.php?' . SID . "'><LI>" . _('Inventory Planning Based On Preferred Supplier Data') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/StockCheck.php?' . SID . "'><LI>" . _('Inventory Stock Check Sheets') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/StockQties_csv.php?' . SID . "'><LI>" . _('Make Inventory Quantities CSV') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/PDFStockCheckComparison.php?' . SID . "'><LI>" . _('Compare Counts Vs Stock Check Data') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/StockLocMovements.php?' . SID . "'><LI>" . _('All Inventory Movements By Location/Date') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/StockLocStatus.php?' . SID . "'><LI>" . _('List Inventory Status By Location/Category') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/StockQuantityByDate.php?' . SID . "'><LI>" . _('Historical Stock Quantity By Location/Category') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/PDFStockNegatives.php?' . SID . "'><LI>" . _('List Negative Stocks') . '</LI></a>'; ?>
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
								<?php echo "<a href='" . $rootpath . '/Stocks.php?' . SID . "'><LI>" . _('Add A New Item') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/SelectProduct.php?' . SID . "'><LI>" . _('Select An Item') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/SalesCategories.php?' . SID . "'><LI>" . _('Sales Category Maintenance') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/PricesBasedOnMarkUp.php?' . SID . "'><LI>" . _('Add or Update Prices Based On Costs') . '</LI></a>'; ?>
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
								<?php echo "<a href='" . $rootpath . '/WorkOrderEntry.php?' . SID . "'><LI>" . _('Work Order Entry') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/SelectWorkOrder.php?' . SID . "'><LI>" . _('Select A Work Order') . '</LI></a>'; ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items">
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/SelectWorkOrder.php?' . SID . "'><LI>" . _('Select A Work Order') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/BOMInquiry.php?' . SID . "'><LI>" . _('Costed Bill Of Material Inquiry') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/WhereUsedInquiry.php?' . SID . "'><LI>" . _('Where Used Inquiry') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/BOMIndented.php?' . SID . "'><LI>" . _('Indented Bill Of Material Listing') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/BOMExtendedQty.php?' . SID . "'><LI>" . _('List Components Required') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/BOMIndentedReverse.php?' . SID . "'><LI>" . _('Indented Where Used Listing') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/MRPReport.php?' . SID . "'><LI>" . _('MRP') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/MRPShortages.php?' . SID . "'><LI>" . _('MRP Shortages') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/MRPPlannedPurchaseOrders.php?' . SID . "'><LI>" . _('MRP Suggested Purchase Orders') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/MRPReschedules.php?' . SID . "'><LI>" . _('MRP Reschedules Required') . '</LI></a>'; ?>
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
								<?php echo "<a href='" . $rootpath . '/WorkCentres.php?' . SID . "'><LI>" . _('Work Centre') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/BOMs.php?' . SID . "'><LI>" . _('Bills Of Material') . '</LI></a>'; ?>
							</td>
							</tr>
							
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/MRPDemands.php?' . SID . "'><LI>" . _('Master Schedule') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/MRPCreateDemands.php?' . SID . "'><LI>" . _('Auto Create Master Schedule') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/MRP.php?' . SID . "'><LI>" . _('MRP Calculation') . '</LI></a>'; ?>
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
								<?php echo '<img src="'.$rootpath.'/css/'.$theme.'/images/company.png" title="' . _('General Setup Options') . '" alt="">'; ?>
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
								<?php echo '<img src="'.$rootpath.'/css/'.$theme.'/images/ar.png" title="' . _('Receivables/Payables Setup') . '" alt="">'; ?>
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
								<?php echo '<img src="'.$rootpath.'/css/'.$theme.'/images/inventory.png" title="' . _('Inventory Setup') . '" alt="">'; ?>
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
								<?php echo "<a href='" . $rootpath . '/CompanyPreferences.php?' . SID . "'><LI>" . _('Company Preferences') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/SystemParameters.php?' . SID . "'><LI>" . _('Configuration Settings') . '</LI></a>'; ?>
							</td>
							</tr>

							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/WWW_Users.php?' . SID . "'><LI>" . _('User Maintenance') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/WWW_Access.php?' . SID . "'><LI>" . _('Role Permissions') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/BankAccounts.php?' . SID . "'><LI>" . _('Bank Accounts') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/Currencies.php?' . SID . "'><LI>" . _('Currency Maintenance') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/TaxAuthorities.php?' . SID . "'><LI>" . _('Tax Authorities and Rates Maintenance') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/TaxGroups.php?' . SID . "'><LI>" . _('Tax Group Maintenance') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/TaxProvinces.php?' . SID . "'><LI>" . _('Dispatch Tax Province Maintenance') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/TaxCategories.php?' . SID . "'><LI>" . _('Tax Category Maintenance') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/PeriodsInquiry.php?' . SID . "'><LI>" . _('List Periods Defined') . ' <font size=1>(' . _('Periods are automatically maintained') . ')</font></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<a href="' . $rootpath . '/reportwriter/admin/ReportCreator.php"><LI>' . _('Report Builder Tool') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<a href="' . $rootpath . '/AuditTrail.php"><LI>' . _('View Audit Trail') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<a href="' . $rootpath . '/SystemCheck.php"><LI>' . _('View System Check') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo '<a href="' . $rootpath . '/GeocodeSetup.php"><LI>' . _('Geocode Setup') . '</LI></a>'; ?>
							</td>
							</tr>
						</table>
					</td>

					<td class="menu_group_items">	<!-- AR/AP set-up options -->
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/SalesTypes.php?' . SID . "'><LI>" . _('Sales Types') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/CustomerTypes.php?' . SID . "'><LI>" . _('Customer Types') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/CreditStatus.php?' . SID . "'><LI>" . _('Credit Status') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/PaymentTerms.php?' . SID . "'><LI>" . _('Payment Terms') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/PO_AuthorisationLevels.php?' . SID . "'><LI>" . _('Set Purchase Order Authorisation levels') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/PaymentMethods.php?' . SID . "'><LI>" . _('Payment Methods') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/SalesPeople.php?' . SID . "'><LI>" . _('Sales People') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/Areas.php?' . SID . "'><LI>" . _('Sales Areas') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/Shippers.php?' . SID . "'><LI>" . _('Shippers') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/SalesGLPostings.php?' . SID . "'><LI>" . _('Sales GL Interface Postings') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/COGSGLPostings.php?' . SID . "'><LI>" . _('COGS GL Interface Postings') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/FreightCosts.php?' . SID . "'><LI>" . _('Freight Costs Maintenance') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/DiscountMatrix.php?' . SID . "'><LI>" . _('Discount Matrix') . '</LI></a>'; ?>
							</td>
							</tr>
						</table>
					</td>

					<td class="menu_group_items">	<!-- Inventory set-up options -->
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/StockCategories.php?' . SID . "'><LI>" . _('Inventory Categories Maintenance') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/Locations.php?' . SID . "'><LI>" . _('Inventory Locations Maintenance') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/DiscountCategories.php?' . SID . "'><LI>" . _('Discount Category Maintenance') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/UnitsOfMeasure.php?' . SID . "'><LI>" . _('Units of Measure') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr></tr>
							
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/MRPCalendar.php?' . SID . "'><LI>" . _('MRP Available Production Days') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/MRPDemandTypes.php?' . SID . "'><LI>" . _('MRP Demand Types') . '</LI></a>'; ?>
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
								<?php echo "<a href='" . $rootpath . '/Payments.php?' .SID . "&NewPayment=Yes'><LI>" . _('Bank Account Payments Entry') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/CustomerReceipt.php?' . SID . "&NewReceipt=Yes'><LI>" . _('Bank Account Receipts Entry') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/GLJournal.php?' .SID . "&NewJournal=Yes'><LI>" . _('Journal Entry') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/BankMatching.php?' .SID . "&Type=Payments'><LI>" . _('Bank Account Payments Matching') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/BankMatching.php?' .SID . "&Type=Receipts'><LI>" . _('Bank Account Receipts Matching') . '</LI></a>'; ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items">  <!-- Gereral inquiry options -->
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/GLTrialBalance.php?' . SID . "'><LI>" . _('Trial Balance') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/SelectGLAccount.php?' . SID . "'><LI>" . _('Account Inquiry') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/BankReconciliation.php?' . SID . "'><LI>" . _('Bank Account Reconciliation Statement') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/PDFChequeListing.php?' . SID . "'><LI>" . _('Cheque Payments Listing') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/GLProfit_Loss.php?" . SID . "'><LI>" . _('Profit and Loss Statement') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/GLBalanceSheet.php?' . SID . "'><LI>" . _('Balance Sheet') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/GLTagProfit_Loss.php?" . SID . "'><LI>" . _('Tag Reports') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/Tax.php?' . SID . "'><LI>" . _('Tax Reports') . '</LI></a>'; ?>
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
								<?php echo "<a href='" . $rootpath . '/GLAccounts.php?' . SID . "'><LI>" . _('GL Account') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/GLBudgets.php?' . SID . "'><LI>" . _('GL Budgets') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/AccountGroups.php?' . SID . "'><LI>" . _('Account Groups') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/AccountSections.php?' . SID . "'><LI>" . _('Account Sections') . '</LI></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/GLTags.php?' . SID . "'><LI>" . _('GL Tags') . '</LI></a>'; ?>
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
				<?php echo '<img src="'.$rootpath.'/css/'.$theme.'/images/transactions.png" title="' . _('Transactions') . '" alt="">'; ?>
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
				<?php echo '<img src="'.$rootpath.'/css/'.$theme.'/images/reports.png" title="' . _('Inquiries and Reports') . '" alt="">'; ?>
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
				<?php echo '<img src="'.$rootpath.'/css/'.$theme.'/images/maintenance.png" title="' . _('Maintenance') . '" alt="">'; ?>
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
					$RptLinks .= '<a href="'.$rootpath.'/reportwriter/ReportMaker.php?action=go&reportid='.$Report['id'].'"><LI>'._($Report['reportname']).'</LI></a>';
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
					$RptLinks .= '<a href="'.$rootpath.'/reportwriter/FormMaker.php?id='.$Report['groupname'].'">';
					$RptLinks .= $FormGroups[$Report['groupname']].'</a>';
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

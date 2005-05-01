<?php

/* $Revision: 1.37 $ */


$PageSecurity = 1;

include('includes/session.inc');
$title=_('Main Menu');

/*The module link codes are hard coded in a switch statement below to determine the options to show for each tab */
$ModuleLink = array('orders', 'AR', 'AP', 'PO', 'stock', 'manuf', 'GL', 'system');
/*The headings showing on the tabs accross the main index used also in WWW_Users for defining what should be visible to the user */
$ModuleList = array(_('Orders'), _('Receivables'), _('Payables'), _('Purchasing'), _('Inventory'), _('Manufacturing'), _('General Ledger'), _('Setup'));

if (isset($_GET['Application'])){ /*This is sent by this page (to itself) when the user clicks on a tab */
	$_SESSION['Module'] = $_GET['Application'];
}

include('includes/header.inc');

if (count($_SESSION['AllowedPageSecurityTokens'])==1){

/* if there is only one security access and its 1 (it has to be 1 for this page came up at all)- it must be a customer log on need to limit the menu to show only the customer accessible stuff this is what the page looks like for customers logging in */
?>
	
		<tr>
		<td class="menu_group_items">  <!-- Orders transaction options -->
		<table width="100%" class="table_index">
			<tr>
			<td class="menu_group_item">
				<?php echo "<a href='" . $rootpath . '/CustomerInquiry.php?' .SID . '&CustomerID=' . $_SESSION['CustomerID'] . "'><li>" . _('Account Status') . '</li></a>'; ?>
			</td>
			</tr>
			<tr>
			<td class="menu_group_item">
				<?php echo "<a href='" . $rootpath . '/SelectOrderItems.php?' .SID . "&NewOrder=Yes'><li>" . _('Place An Order') . '</li></a>'; ?>
			</td>
			</tr>
										<tr>
			<td class="menu_group_item">
				<?php echo "<li><a href='" . $rootpath . '/SelectCompletedOrder.php?' .SID . "&SelectedCustomer=" . $_SESSION['CustomerID'] . "'>" . _('Order Status') . '</a></li>'; ?>
			</td>
			</tr>
		</table>
	</td>
<?php
	include('includes/footer.inc');
	exit;
} else {  /* Security settings DO allow seeing the main menu */

?>
		<table border="0" width="100%">
			<tr>
			<td class="main_menu">
				<table class="main_menu" cellspacing='0'>
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
		<table class="blank_area">
			<tr>
			<td>
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
								<?php echo "<a href='" . $rootpath . '/SelectOrderItems.php?' .SID . "&NewOrder=Yes'><li>" . _('Sales Order Entry') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/SelectSalesOrder.php?' . SID . "'><li>" . _('Outstanding Sales Orders Maintenance') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/SpecialOrder.php?' .SID . "&NewSpecial=Yes'><li>" . _('Special Order') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/SelectRecurringSalesOrder.php?' .SID . "'><li>" . _('Select Recurring Order Template') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/RecurringSalesOrdersProcess.php?' .SID . "'><li>" . _('Process Recurring Sales Orders') . '</li></a>'; ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items"> <!-- Orders Inquiry options -->
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/SelectCompletedOrder.php?' . SID . "'><li>" . _('Order Inquiry') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/PDFPriceList.php?' . SID . "'><li>" . _('Print Price Lists') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/PDFOrderStatus.php?' . SID . "'><li>" . _('Order Status Reports (Print)') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/PDFOrdersInvoiced.php?' . SID . "'><li>" . _('Orders Invoiced Reports') . '</li></a>'; ?>
							</td>
							</tr>

							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/PDFDeliveryDifferences.php?' . SID . "'><li>" . _('Order Delivery Differences Report') . '</li></a>'; ?>
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
								<?php echo "<a href='" . $rootpath . '/SelectSalesOrder.php?' . SID . "'><li>" . _('Select Order to Invoice') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/SelectCreditItems.php?' .SID . "&NewCredit=Yes'><li>" . _('Create A Credit Note') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/CustomerReceipt.php?' . SID . "'><li>" . _('Enter Receipts') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">

								<?php echo "<a href='" . $rootpath . '/CustomerAllocations.php?' . SID . "'><li>" . _('Allocate Receipts or Credit Notes') . '</li></a>'; ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items">
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/SelectCustomer.php?' . SID . "'><li>" . _('Customer Transaction Inquiries') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/CustWhereAlloc.php?' . SID . "'><li>" . _('Where Allocated Inquiry') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/PrintCustTrans.php?' . SID . "'><li>" . _('Print Invoices or Credit Notes') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/PrintCustStatements.php?" . SID . "'><li>" . _('Print Statements') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/SalesAnalRepts.php?' . SID . "'><li>" . _('Sales Analysis Reports') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/AgedDebtors.php?' . SID . "'><li>" . _('Aged Customer Balances/Overdues Report') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/CustomerTransInquiry.php?' . SID . "'><li>" . _('Transaction Inquiries') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
							<?php echo "<a href='" . $rootpath . '/PDFBankingSummary.php?' . SID . "'><li>" . _('Re-Print A Deposit Listing') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/DebtorsAtPeriodEnd.php?' . SID . "'><li>" . _('Debtor Balances At A Prior Month End') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
							<?php echo "<a href='" . $rootpath . '/PDFCustomerList.php?' . SID . "'><li>" . _('Customer Listing By Area/Salesperson') . '</li></a>'; ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items">
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/Customers.php?' . SID . "'><li>" . _('Add a New Customer') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/SelectCustomer.php?' . SID . "'><li>" . _('Modify/Add/Delete Customers/branches and Special pricing') . '</li></a>'; ?>
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
								<?php echo "<a href='" . $rootpath . '/SelectSupplier.php?' . SID . "'><li>" . _('Select Supplier For Transactions and Inquiries') . '</li></a>'; ?>

							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/SupplierAllocations.php?" . SID . "'><li>" . _('Supplier Allocations') . '</li></a>'; ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items">  <!-- AP Inquiries -->
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/AgedSuppliers.php?' . SID . "'><li>" . _('Aged Supplier Report') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/SuppPaymentRun.php?' . SID . "'><li>" . _('Payment Run Report') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/OutstandingGRNs.php?' . SID . "'><li>" . _('Outstanding GRNs Report') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/SupplierBalsAtPeriodEnd.php?' . SID . "'><li>" . _('Creditor Balances At A Prior Month End') . '</li></a>'; ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items">   <!-- AP Maintenance Options -->
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/Suppliers.php?' . SID . "'><li>" . _('Add a New Supplier') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/SelectSupplier.php?' . SID . "'><li>" . _('Modify Or Delete A Supplier') . '</li></a>'; ?>
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
								<?php echo "<a href='" . $rootpath . '/PO_Header.php?' .SID . "&NewOrder=Yes'><li>" . _('Purchase Order Entry') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/PO_SelectOSPurchOrder.php?' . SID . "'><li>" . _('Outstanding Purchase Orders') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/SelectSupplier.php?' . SID . "'><li>" . _('Shipment Entry') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/Shipt_Select.php?' . SID . "'><li>" . _('Select A Shipment') . "</li></a>"; ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items">  <!-- PO Inquiries -->
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/PO_SelectPurchOrder.php?' . SID . "'><li>" . _('Purchase Order Inquiry') . '</li></a>'; ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items">   <!-- PO Maintenance -->
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/PurchData.php?' . SID . "'><li>" . _('Maintain Purchasing Data') . '</li></a>'; ?>
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
								<?php echo "<a href='" . $rootpath . '/StockLocTransfer.php' . SID . "'><li>" . _('Bulk Inventory Transfer') . ' - ' . _('Dispatch') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/StockLocTransferReceive.php?' . SID . "'><li>" . _('Bulk Inventory Transfer') . ' - ' . _('Receive') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/StockTransfers.php?' . SID . "'><li>" . _('Inventory Location Transfers') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/StockAdjustments.php?' . SID . "'><li>" . _('Inventory Adjustments') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/ReverseGRN.php?' . SID . "'><li>" . _('Reverse Goods Received') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/StockCounts.php?' . SID . "'><li>" . _('Enter Stock Counts') . '</li></a>'; ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items">
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/StockMovements.php?" . SID . "'><li>" . _('Inventory Item Movements') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/StockStatus.php?' . SID . "'><li>" . _('Inventory Item Status') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/StockUsage.php?' . SID . "'><li>" . _('Inventory Item Usage') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/InventoryValuation.php?' . SID . "'><li>" . _('Inventory Valuation Report') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/InventoryPlanning.php?' . SID . "'><li>" . _('Inventory Planning Report') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/StockCheck.php?' . SID . "'><li>" . _('Inventory Stock Check Sheets') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/StockQties_csv.php?' . SID . "'><li>" . _('Make Inventory Quantities CSV') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/PDFStockCheckComparison.php?' . SID . "'><li>" . _('Compare Counts Vs Stock Check Data') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/StockLocMovements.php?' . SID . "'><li>" . _('All Inventory Movements By Location/Date') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/StockLocStatus.php?' . SID . "'><li>" . _('List Inventory Status By Location/Category') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/StockQuantityByDate.php?' . SID . "'><li>" . _('Historical Stock Quantity By Location/Category') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/PDFStockNegatives.php?' . SID . "'><li>" . _('List Negative Stocks') . '</li></a>'; ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items">
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/Stocks.php?' . SID . "'><li>" . _('Add Inventory Items') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/SelectProduct.php?' . SID . "'><li>" . _('Modify Or Delete A Inventory Item Or Item Pricing') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/StockReorderLevel.php?' . SID . "'><li>" . _('Maintain Reorder Levels') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/StockCostUpdate.php?' . SID . "'><li>" . _('Modify Standard Costs') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/PurchData.php?' . SID . "'><li>" . _('Maintain Purchasing Data') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/SalesCategories.php?' . SID . "'><li>" . _('Sales Category Maintenance') . '</li></a>'; ?>
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
						<table width="100%">
							<tr>
							  <td>
								  &nbsp;
								</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items">
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/BOMInquiry.php?' . SID . "'><li>" . _('Costed Bill Of Material Inquiry') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/WhereUsedInquiry.php?' . SID . "'><li>" . _('Where Used Inquiry') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/BOMListing.php?' . SID . "'><li>" . _('Bills Of Material Listing') . '</li></a>'; ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items">
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/WorkCentres.php?' . SID . "'><li>" . _('Work Centre') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/BOMs.php?' . SID . "'><li>" . _('Bills Of Material') . '</li></a>'; ?>
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
								<?php echo "<img src=$rootpath/css/$theme/images/company.gif TITLE=\"" . _('General Setup Options') . '" ALT="">'; ?>
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
								<?php echo "<img src=$rootpath/css/$theme/images/ar.gif TITLE=" . _('Receivables/Payables Setup') . '" ALT="">'; ?>
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
								<?php echo "<img src=$rootpath/css/$theme/images/inventory.gif TITLE='" . _('Inventory Setup') . "' ALT=''>"; ?>
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
								<?php echo "<a href='" . $rootpath . '/CompanyPreferences.php?' . SID . "'><li>" . _('Company Preferences') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/SystemParameters.php?' . SID . "'><li>" . _('Configuration Settings') . '</li></a>'; ?>
							</td>
							</tr>

							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/WWW_Users.php?' . SID . "'><li>" . _('User Accounts') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/WWW_Access.php?' . SID . "'><li>" . _('Role Permissions') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/BankAccounts.php?' . SID . "'><li>" . _('Bank Accounts') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/Currencies.php?' . SID . "'><li>" . _('Currency Maintenance') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/TaxAuthorities.php?' . SID . "'><li>" . _('Tax Authorities and Rates Maintenance') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/TaxGroups.php?' . SID . "'><li>" . _('Tax Group Maintenance') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/PeriodsInquiry.php?' . SID . "'><li>" . _('List Periods Defined') . ' <FONT SIZE=1>(' . _('Periods are automatically maintained') . ')</FONT></a>'; ?>
							</td>
							</tr>
						</table>
					</td>

					<td class="menu_group_items">	<!-- AR/AP set-up options -->
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/SalesTypes.php?' . SID . "'><li>" . _('Sales Types') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/CreditStatus.php?' . SID . "'><li>" . _('Credit Status') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/PaymentTerms.php?' . SID . "'><li>" . _('Payment Terms') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/PaymentMethods.php?' . SID . "'><li>" . _('Payment Methods') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/SalesPeople.php?' . SID . "'><li>" . _('Sales People') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/Areas.php?' . SID . "'><li>" . _('Sales Areas') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/Shippers.php?' . SID . "'><li>" . _('Shippers') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/SalesGLPostings.php?' . SID . "'><li>" . _('Sales GL Interface Postings') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/COGSGLPostings.php?' . SID . "'><li>" . _('COGS GL Interface Postings') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/FreightCosts.php?' . SID . "'><li>" . _('Freight Costs Maintenance') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/DiscountMatrix.php?' . SID . "'><li>" . _('Discount Matrix') . '</li></a>'; ?>
							</td>
							</tr>
						</table>
					</td>

					<td class="menu_group_items">	<!-- Inventory set-up options -->
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/StockCategories.php?' . SID . "'><li>" . _('Inventory Categories Maintenance') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/Locations.php?' . SID . "'><li>" . _('Inventory Locations Maintenance') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/DiscountCategories.php?' . SID . "'><li>" . _('Discount Category Maintenance') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/UnitsOfMeasure.php?' . SID . "'><li>" . _('Units of Measure') . '</li></a>'; ?>
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
								<?php echo "<a href='" . $rootpath . '/Payments.php?' .SID . "&NewPayment=Yes'><li>" . _('Bank Account Payments') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/CustomerReceipt.php?' . SID . "'><li>" . _('Bank Account Receipts') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/GLJournal.php?' .SID . "&NewJournal=Yes'><li>" . _('Journal Entry') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/BankMatching.php?' .SID . "&Type=Receipts'><li>" . _('Bank Deposits Matching') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/BankMatching.php?' .SID . "&Type=Payments'><li>" . _('Bank Payments Matching') . '</li></a>'; ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items">  <!-- Gereral inquiry options -->
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/GLTrialBalance.php?' . SID . "'><li>" . _('Trial Balance') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/SelectGLAccount.php?' . SID . "'><li>" . _('Account Inquiry') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/BankReconciliation.php?' . SID . "'><li>" . _('Bank Reconciliation Statement') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/PDFChequeListing.php?' . SID . "'><li>" . _('Cheque Payments Listing') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/GLProfit_Loss.php?" . SID . "'><li>" . _('Profit and Loss Statement') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/GLBalanceSheet.php?' . SID . "'><li>" . _('Balance Sheet') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/Tax.php?' . SID . "'><li>" . _('GST Reports') . '</li></a>'; ?>
							</td>
							</tr>

						</table>
					</td>
					<td class="menu_group_items">  <!-- Gereral Ledger Maintenance options -->
						<table width="100%" class="table_index">
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/GLAccounts.php?' . SID . "'><li>" . _('GL Account') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/AccountGroups.php?' . SID . "'><li>" . _('Account Groups') . '</li></a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/AccountSections.php?' . SID . "'><li>" . _('Account Sections') . '</li></a>'; ?>
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
				<?php echo "<img src=$rootpath/css/$theme/images/transactions.gif TITLE='" . _('Transactions') . "' ALT=''>"; ?>
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
				<?php echo '<img src=' . $rootpath . '/css/' . $theme . '/images/reports.gif TITLE="' . _('Inquiries and Reports') . '" ALT="">'; ?>
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
				<?php echo "<img src=$rootpath/css/$theme/images/maintenance.gif TITLE='" . _('Maintenance') . "' ALT=''>"; ?>
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
?>
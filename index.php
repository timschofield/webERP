<?php
/* $Revision: 1.16 $ */
$title="Main Menu";

$PageSecurity = 1;

include("includes/session.inc");

/*ModuleList array with the text of modules now in config.php */
/*The module link codes are hard coded in a switch statement below to determine the options to show for each tab */
$ModuleLink = array("orders", "AR", "AP", "PO", "stock", "manuf", "GL", "system");

if (isset($_GET['Application'])){ /*This is sent by this page (to itself) when the user clicks on a tab */
	$_SESSION['Module'] = $_GET['Application'];
}


include("includes/header.inc");

if (count($SecurityGroups[$_SESSION["AccessLevel"]])==1){

/* if there is only one security access and its 1 (it has to be 1 for this page came up at all)- it must be a customer log on need to limit the menu to show only the customer accessible stuff this is what the page looks like for customers logging in */

	echo "<font size='3'><br /><br /><br />";
	echo "<a href='" . $rootpath . "/CustomerInquiry.php?" . SID . "CustomerID=" . $_SESSION["CustomerID"] . "'>" . _("Account Status") . "</a><br />";
	echo "<a href='" . $rootpath . "/SelectOrderItems.php?" . SID . "NewOrder=Yes'>" . _("Place An Order") . "</a><br />";
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
			if (!isset($_SESSION['Module'])OR $_SESSION['Module']==""){
				$_SESSION['Module']=$ModuleLink[$i];
			}
			if ($ModuleLink[$i] == $_SESSION['Module']){
				echo "<td class='main_menu_selected'><a href='". $_SERVER['PHP_SELF'] ."?". SID ."Application=". $ModuleLink[$i] ."'>". $ModuleList[$i] ."</a></td>";
			} else {
				echo "<td class='main_menu_unselected'><a href='". $_SERVER['PHP_SELF'] ."?". SID ."Application=". $ModuleLink[$i] ."'>". $ModuleList[$i] ."</a></td>";
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

	Case "orders": //Sales Orders
	?>

		<table width="100%">
			<tr>
			<td class="menu_group_area">
				<table width="100%">

					<?php OptionHeadings(); ?>

					<tr>
					<td class="menu_group_items">  <!-- Orders transaction options -->
						<table width="100%">
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/SelectOrderItems.php?" . SID . "NewOrder=Yes'>" . _('Sales Order Entry') . '</a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . '/SelectSalesOrder.php?' . SID . "'>" . _('Outstanding Sales Orders Maintenance') . '</a>'; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/SpecialOrder.php?" . SID . "NewSpecial=Yes'>" . _("Special Order") . "</a>"; ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items"> <!-- Orders Inquiry options -->
						<table width="100%">
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/SelectCompletedOrder.php?" . SID . "'>" . _("Order Inquiry") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/PDFPriceList.php?" . SID . "'>" . _("Print Price Lists") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/PDFDeliveryDifferences.php?" . SID . "'>" . _("Order Delivery Differences Report") . "</a>"; ?>
							</td>
							</tr>
						</table>
					</td>

					<td class="menu_group_items"> <!-- Orders Maintenance options -->
						<table width="100%">
							<tr>
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


	Case "AR": //Debtors Module

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
						<table width="100%">
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/SelectSalesOrder.php?" . SID . "'>" . _("Select Order to Invoice") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/SelectCreditItems.php?" . SID . "NewCredit=Yes'>" . _("Create A Credit Note") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/CustomerReceipt.php?" . SID . "'>" . _("Enter Receipts") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">

								<?php echo "<a href='" . $rootpath . "/CustomerAllocations.php?" . SID . "'>" . _("Allocate Receipts or Credit Notes") . "</a>"; ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items">
						<table width="100%">
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/SelectCustomer.php?" . SID . "'>" . _("Customer Transaction Inquiries") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/CustWhereAlloc.php?" . SID . "'>" . _("Where Allocated Inquiry") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/PrintCustTrans.php?" . SID . "'>" . _("Print Invoices or Credit Notes") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/PrintCustStatements.php?" . SID . "'>" . _("Print Statements") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/SalesAnalRepts.php?" . SID . "'>" . _("Sales Analysis Reports") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/AgedDebtors.php?" . SID . "'>" . _("Aged Customer Balances/Overdues Report") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/CustomerTransInquiry.php?" . SID . "'>" . _("Transaction Inquiries") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/PDFBankingSummary.php?" . SID . "'>" . _("Re-Print A Deposit Listing") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/DebtorsAtPeriodEnd.php?" . SID . "'>" . _("Debtor Balances At A Prior Month End") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
							<?php echo "<a href='" . $rootpath . "/PDFCustomerList.php?" . SID . "'>" . _("Customer Listing By Area/Salesperson") . "</a>"; ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items">
						<table width="100%">
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/Customers.php?" . SID . "'>" . _("Add a New Customer") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/SelectCustomer.php?" . SID . "'>" . _("Modify/Add/Delete Customers/branches and Special pricing") . "</a>"; ?>
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

	Case "AP": //Creditors Module

	?>
		<table width="100%">
			<tr>
			<td valign="top" class="menu_group_area">
				<table width="100%">

					<?php OptionHeadings(); ?>

					<tr>
					<td class="menu_group_items"> <!-- AP transaction options -->
						<table width="100%">
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/SelectSupplier.php?" . SID . "'>" . _("Select Supplier For Transactions and Inquiries") . "</a>"; ?>

							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/SupplierAllocations.php?" . SID . "'>" . _("Supplier Allocations") . "</a>"; ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items">  <!-- AP Inquiries -->
						<table width="100%">
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/AgedSuppliers.php?" . SID . "'>" . _("Aged Supplier Report") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/SuppPaymentRun.php?" . SID . "'>" . _("Payment Run Report") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/OutstandingGRNs.php?" . SID . "'>" . _("Outstanding GRNs Report") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/SupplierBalsAtPeriodEnd.php?" . SID . "'>" . _("Creditor Balances At A Prior Month End") . "</a>"; ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items">   <!-- AP Maintenance Options -->
						<table width="100%">
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/Suppliers.php?" . SID . "'>" . _("Add a New Supplier") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/SelectSupplier.php?" . SID . "'>" . _("Modify Or Delete A Supplier") . "</a>"; ?>
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

	Case "PO": /* Purchase Ordering */

	?>
		<table width="100%">
			<tr>
			<td valign="top" class="menu_group_area">
				<table width="100%">

					<?php OptionHeadings(); ?>

					<tr>
					<td class="menu_group_items">  <!-- PO Transactions -->
						<table width="100%">
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/PO_Header.php?" . SID . "NewOrder=Yes'>" . _("Purchase Order Entry") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/PO_SelectOSPurchOrder.php?" . SID . "'>" . _("Outstanding Purchase Orders") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/SelectSupplier.php?" . SID . "'>" . _("Shipment Entry") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/Shipt_Select.php?" . SID . "'>" . _("Select A Shipment") . "</a>"; ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items">  <!-- PO Inquiries -->
						<table width="100%">
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/PO_SelectPurchOrder.php?" . SID . "'>" . _("Purchase Order Inquiry") . "</a>"; ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items">   <!-- PO Maintenance -->
						<table width="100%">
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/PurchData.php?" . SID . "'>" . _("Maintain Purchasing Data") . "</a>"; ?>
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


	Case "stock": //Inventory Module

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
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/StockLocTransfer.php" . SID . "'>" . _("Bulk Inventory Transfer - Dispatch") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/StockLocTransferReceive.php?" . SID . "'>" . _("Bulk Inventory Transfer - Receive") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/StockTransfers.php?" . SID . "'>" . _("Inventory Location Transfers") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/StockAdjustments.php?" . SID . "'>" . _("Inventory Adjustments") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/ReverseGRN.php?" . SID . "'>" . _("Reverse Goods Received") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/StockCounts.php?" . SID . "'>" . _("Enter Stock Counts") . "</a>"; ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items">
						<table width="100%">
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/StockMovements.php?" . SID . "'>" . _("Inventory Item Movements") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/StockStatus.php?" . SID . "'>" . _("Inventory Item Status") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/StockUsage.php?" . SID . "'>" . _("Inventory Item Usage") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/InventoryValuation.php?" . SID . "'>" . _("Inventory Valuation Report") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/InventoryPlanning.php?" . SID . "'>" . _("Inventory Planning Report") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/StockCheck.php?" . SID . "'>" . _("Inventory Stock Check Sheets") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/StockQties_csv.php?" . SID . "'>" . _("Make Inventory Quantities CSV") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/PDFStockCheckComparison.php?" . SID . "'>" . _("Compare Counts Vs Stock Check Data") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/StockLocMovements.php?" . SID . "'>" . _("All Inventory Movements By Location/Date") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/StockLocStatus.php?" . SID . "'>" . _("List Inventory Status By Location/Category") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/StockQuantityByDate.php?" . SID . "'>" . _("Historical Stock Quantity By Location/Category") . "</a>"; ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items">
						<table width="100%">
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/Stocks.php?" . SID . "'>" . _("Add Inventory Items") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/SelectProduct.php?" . SID . "'>" . _("Modify Or Delete A Inventory Item Or Item Pricing") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/StockReorderLevel.php?" . SID . "'>" . _("Maintain Reorder Levels") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/StockCostUpdate.php?" . SID . "'>" . _("Modify Standard Costs") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/PurchData.php?" . SID . "'>" . _("Maintain Purchasing Data") . "</a>"; ?>
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

	Case "manuf": //Manufacturing Module

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
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/BOMInquiry.php?" . SID . "'>" . _("Costed Bill Of Material Inquiry") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/WhereUsedInquiry.php?" . SID . "'>" . _("Where Used Inquiry") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/BOMListing.php?" . SID . "'>" . _("Bills Of Material Listing") . "</a>"; ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items">
						<table width="100%">
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/WorkCentres.php?" . SID . "'>" . _("Work Centre") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/BOMs.php?" . SID . "'>" . _("Bills Of Material") . "</a>"; ?>
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


	Case "system": //System setup

	?>
		<table width="100%">
			<tr>
			<td valign="top" class="menu_group_area">
				<table width="100%">
					<tr>
					<td class="menu_group_headers">
						<table>
							<tr>
							<td>
								<?php echo "<img src=$rootpath/css/$theme/images/company.png alt=" . _("General Setup Options") . ">"; ?>
							</td>
							<td class="menu_group_headers_text">
								<?php echo _("General"); ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_headers">
						<table>
							<tr>
							<td>
								<?php echo "<img src=$rootpath/css/$theme/images/ar.png alt=" . _("Receivables/Payables Setup") . ">"; ?>
							</td>
							<td class="menu_group_headers_text">
								<?php echo _("Receivables/Payables"); ?>

							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_headers">
						<table>
							<tr>
							<td>
								<?php echo "<img src=$rootpath/css/$theme/images/inventory.png alt=" . _("Inventory Setup") . ">"; ?>
							</td>
							<td class="menu_group_headers_text">
								<?php echo _("Inventory Setup"); ?>
							</td>
							</tr>
						</table>
					</td>


					</tr>
					<tr>

					<td class="menu_group_items">	<!-- Gereral set up options -->
						<table width="100%">
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/CompanyPreferences.php?" . SID . "'>" . _("Company Preferences") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/WWW_Users.php?" . SID . "'>" . _("User Accounts") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/BankAccounts.php?" . SID . "'>" . _("Bank Accounts") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/Currencies.php?" . SID . "'>" . _("Currency Maintenance") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/TaxAuthorities.php?" . SID . "'>" . _("Tax Types and Rates Maintenance") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/PeriodsInquiry.php?" . SID . "'>" . _("List Periods Defined <FONT SIZE=1>(Periods are automatically maintained)") . "</FONT></a>"; ?>
							</td>
							</tr>
						</table>
					</td>

					<td class="menu_group_items">	<!-- AR/AP set-up options -->
						<table width="100%">
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/SalesTypes.php?" . SID . "'>" . _("Sales Types") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/CreditStatus.php?" . SID . "'>" . _("Credit Status") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/PaymentTerms.php?" . SID . "'>" . _("Payment Terms") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/SalesPeople.php?" . SID . "'>" . _("Sales People") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/Areas.php?" . SID . "'>" . _("Sales Areas") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/Shippers.php?" . SID . "'>" . _("Shippers") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/SalesGLPostings.php?" . SID . "'>" . _("Sales GL Interface Postings") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/COGSGLPostings.php?" . SID . "'>" . _("COGS GL Interface Postings") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/FreightCosts.php?" . SID . "'>" . _("Freight Costs Maintenance") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/DiscountMatrix.php?" . SID . "'>" . _("Discount Matrix") . "</a>"; ?>
							</td>
							</tr>


						</table>
					</td>

					<td class="menu_group_items">	<!-- Inventory set-up options -->
						<table width="100%">
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/StockCategories.php?" . SID . "'>" . _("Inventory Categories Maintenance") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/Locations.php?" . SID . "'>" . _("Inventory Locations Maintenance") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/DiscountCategories.php?" . SID . "'>" . _("Discount Category Maintenance") . "</a>"; ?>
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

	Case "GL": //General Ledger

	?>
		<table width="100%">
			<tr>
			<td valign="top" class="menu_group_area">
				<table width="100%">		<!-- Gereral Ledger Option Headings-->

    					<?php OptionHeadings(); ?>

					<tr>
					<td class="menu_group_items"> <!-- Gereral transactions options -->
						<table width="100%">
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/Payments.php?" . SID . "NewPayment=Yes'>" . _("Bank Account Payments") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/CustomerReceipt.php?" . SID . "'>" . _("Bank Account Receipts") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/GLJournal.php?" . SID . "NewJournal=Yes'>" . _("Journal Entry") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/BankMatching.php?" . SID . "Type=Receipts'>" . _("Bank Deposits Matching") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/BankMatching.php?" . SID . "Type=Payments'>" . _("Bank Payments Matching") . "</a>"; ?>
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_items">  <!-- Gereral inquiry options -->
						<table width="100%">
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/GLTrialBalance.php?" . SID . "'>" . _("Trial Balance") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/SelectGLAccount.php?" . SID . "'>" . _("Account Inquiry") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/BankReconciliation.php?" . SID . "'>" . _("Bank Reconciliation Statement") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/PDFChequeListing.php?" . SID . "'>" . _("Cheque Payments Listing") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/GLProfit_Loss.php?" . SID . "'>" . _("Profit and Loss Statement") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/GLBalanceSheet.php?" . SID . "'>" . _("Balance Sheet") . "</a>"; ?>
							</td>
							</tr>


						</table>
					</td>
					<td class="menu_group_items">  <!-- Gereral Ledger Maintenance options -->
						<table width="100%">
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/GLAccounts.php?" . SID . "'>" . _("GL Account") . "</a>"; ?>
							</td>
							</tr>
							<tr>
							<td class="menu_group_item">
								<?php echo "<a href='" . $rootpath . "/AccountGroups.php?" . SID . "'>" . _("Account Groups") . "</a>"; ?>
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

include("includes/footer.inc");

function OptionHeadings() {

global $rootpath, $theme;

?>

	<tr>
	<td class="menu_group_headers"> <!-- Orders option Headings -->
		<table>
			<tr>
			<td>
				<?php echo "<img src=$rootpath/css/$theme/images/transactions.png alt=" . _("Transactions") . ">"; ?>
			</td>
			<td class="menu_group_headers_text">
				<?php echo _("Transactions"); ?>
			</td>
			</tr>
		</table>
	</td>
	<td class="menu_group_headers">
		<table>
			<tr>
			<td>
				<?php echo "<img src=$rootpath/css/$theme/images/reports.png alt=" . _("Inquiries and Reports") . ">"; ?>
			</td>
			<td class="menu_group_headers_text">
				<?php echo _("Inquiries and Reports"); ?>
			</td>
			</tr>
		</table>
	</td>
	<td class="menu_group_headers">
		<table>
			<tr>
			<td>
				<?php echo "<img src=$rootpath/css/$theme/images/maintenance.png alt=" . _("Maintenance") . ">"; ?>
			</td>
			<td class="menu_group_headers_text">
				<?php echo _("Maintenance"); ?>
			</td>
			</tr>
		</table>
	</td>
	</tr>

<?php

}
?>

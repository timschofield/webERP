<?php
/* $Revision: 1.14 $ */
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
	echo "<a href='" . $rootpath . "/CustomerInquiry.php?" . SID . "CustomerID=" . $_SESSION["CustomerID"] . "'>Account Status</a><br />";
	echo "<a href='" . $rootpath . "/SelectOrderItems.php?" . SID . "NewOrder=Yes'>Place An Order</a><br />";
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
					<tr>
					<td class="menu_group_headers"> <!-- Orders option Headings -->
						<table>
							<tr>
							<td>
								<img src="<?php echo $rootpath;?>/css/<?php echo $theme;?>/images/transactions.png" alt="Transactions" /> 
							</td>
							<td class="menu_group_headers_text">
								Transactions
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_headers">
						<table>
							<tr>
							<td>
								<img src="<?php echo $rootpath;?>/css/<?php echo $theme;?>/images/reports.png" alt="Inquiries and Reports" />
							</td>
							<td class="menu_group_headers_text">
								Inquiries and Reports
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_headers">
						<table>
							<tr>
							<td>
								<img src="<?php echo $rootpath;?>/css/<?php echo $theme;?>/images/maintenance.png" alt="Maintenance" />
							</td>
							<td class="menu_group_headers_text">
								Maintenance
							</td>
							</tr>
						</table>
					</td>
					</tr>
					<tr>
					<td class="menu_group_items">  <!-- Orders transaction options -->
						<table width="100%">
							<tr>
							<td class="menu_group_item">
								<a href="<?php echo $rootpath . "/SelectOrderItems.php?" . SID . "NewOrder=Yes"; ?>">Sales Order Entry</a>
							</td>  
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath . "/SelectSalesOrder.php?" . SID; ?>">Outstanding Sales Orders Maintenance</a>
							</td>  
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath . "/SpecialOrder.php?" . SID . "NewSpecial=Yes"; ?>">Special Order</a>
							</td>
							</tr>
						</table>		
					</td>
					<td class="menu_group_items"> <!-- Orders Inquiry options -->
						<table width="100%">
							<tr>
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/SelectCompletedOrder.php?" . SID; ?>">Order Inquiry</a>
							</td>  
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/PDFPriceList.php?" . SID; ?>">Print Price Lists</a>
							</td>  
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/PDFDeliveryDifferences.php?" . SID; ?>">Order Delivery Differences Report</a>
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
					<tr>
					<td class="menu_group_headers">  <!-- AR option Headings -->
						<table>
							<tr>
							<td>
								<img src="<?php echo $rootpath;?>/css/<?php echo $theme;?>/images/transactions.png" alt="Transactions" /> 
							</td>
							<td class="menu_group_headers_text">
								Transactions
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_headers">
						<table>
							<tr>
							<td>
								<img src="<?php echo $rootpath;?>/css/<?php echo $theme;?>/images/reports.png" alt="Inquiries and Reports" />
							</td>
							<td class="menu_group_headers_text">
								Inquiries and Reports
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_headers">
						<table>
							<tr>
							<td>
								<img src="<?php echo $rootpath;?>/css/<?php echo $theme;?>/images/maintenance.png" alt="Maintenance" />
							</td>
							<td class="menu_group_headers_text">
								Maintenance
							</td>
							</tr>
						</table>
					</td>
					</tr>
					<tr>
					<td class="menu_group_items">
						<table width="100%">
							<tr>
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/SelectSalesOrder.php?" . SID; ?>">Select Order to Invoice</a><br />
							</td>  
							</tr>
							<tr>  
							<td class="menu_group_item">
									<a href="<?php echo $rootpath."/SelectCreditItems.php?" . SID . "&NewCredit=Yes"; ?>">Create A Credit Note</a><br />
							</td>  
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/CustomerReceipt.php?" . SID; ?>">Enter Receipts</a><br />
							</td>  
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/CustomerAllocations.php?" . SID; ?>">Allocate Receipts or Credit Notes</a><br />
							</td>
							</tr>
						</table>		
					</td>
					<td class="menu_group_items">
						<table width="100%">
							<tr>
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/SelectCustomer.php?" . SID; ?>">Customer Transaction Inquiries</a><br />
							</td>  
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/CustWhereAlloc.php?" . SID; ?>">Where Allocated Inquiry</a><br />
							</td>  
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/PrintCustTrans.php?" . SID; ?>">Print Invoices or Credit Notes</a><br />
							</td>  
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/PrintCustStatements.php?" . SID; ?>">Print Statements</a><br />
							</td>  
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/SalesAnalRepts.php?" . SID; ?>">Sales Analysis Reports</a><br />
							</td>  
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/AgedDebtors.php?" . SID; ?>">Aged Customer Balances/Overdues Report</a><br />
							</td>  
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/CustomerTransInquiry.php?" . SID; ?>">Transaction Inquiries</a><br />
							</td>  
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/PDFBankingSummary.php?" . SID; ?>">Re-Print A Deposit Listing</a><br />
							</td>
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/DebtorsAtPeriodEnd.php?" . SID; ?>">Debtor Balances At A Prior Month End</a><br />
							</td>
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/PDFCustomerList.php?" . SID; ?>">Customer Listing By Area/Salesperson</a><br />
							</td>
							</tr>
						</table>		
					</td>
					<td class="menu_group_items">
						<table width="100%">
							<tr>
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/Customers.php?" . SID; ?>">Add a New Customer</a><br />
							</td>  
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/SelectCustomer.php?" . SID; ?>">Modify/Add/Delete Customers/branches and Special pricing</a><br />
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
					<tr>
					<td class="menu_group_headers">
						<table>
							<tr>
							<td>
								<img src="<?php echo $rootpath;?>/css/<?php echo $theme;?>/images/transactions.png" alt="Transactions" /> 
							</td>
							<td class="menu_group_headers_text">
								Transactions
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_headers">
						<table>
							<tr>
							<td>
								<img src="<?php echo $rootpath;?>/css/<?php echo $theme;?>/images/reports.png" alt="Inquiries and Reports" />
							</td>
							<td class="menu_group_headers_text">
								Inquiries and Reports
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_headers">
						<table>
							<tr>
							<td>
								<img src="<?php echo $rootpath;?>/css/<?php echo $theme;?>/images/maintenance.png" alt="Maintenance" />
							</td>
							<td class="menu_group_headers_text">
								Maintenance
							</td>
							</tr>
						</table>
					</td>
					</tr>
					
					
					<tr>
					<td class="menu_group_items"> <!-- AP transaction options -->
						<table width="100%">
							<tr>
							<td class="menu_group_item">
								<a href="<?php echo $rootpath ."/SelectSupplier.php?" . SID; ?>">Select Supplier For Transactions and Inquiries</a><br />
							</td>  
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath ."/SupplierAllocations.php?" . SID; ?>">Supplier Allocations</a><br />
							</td>
							</tr>
						</table>		
					</td>
					<td class="menu_group_items">  <!-- AP Inquiries -->
						<table width="100%">
							<tr>
							<td class="menu_group_item">
								<a href="<?php echo $rootpath ."/AgedSuppliers.php?" . SID; ?>">Aged Supplier Report</a><br />
							</td>  
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath ."/SuppPaymentRun.php?" . SID; ?>">Payment Run Report</a><br />
							</td>  
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath ."/OutstandingGRNs.php?" . SID; ?>">Outstanding GRNs Report</a><br />
							</td>
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/SupplierBalsAtPeriodEnd.php?" . SID; ?>">Creditor Balances At A Prior Month End</a><br />
							</td>
							</tr>
						</table>		
					</td>
					<td class="menu_group_items">   <!-- AP Maintenance Options -->
						<table width="100%">
							<tr>
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/Suppliers.php?" . SID; ?>">Add a New Supplier</a><br />
							</td>  
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/SelectSupplier.php?" . SID; ?>">Modify Or Delete A Supplier</a><br />
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
					<tr>
					<td class="menu_group_headers">  <!-- PO Option Headings -->
						<table>
							<tr>
							<td>
								<img src="<?php echo $rootpath;?>/css/<?php echo $theme;?>/images/transactions.png" alt="Transactions" /> 
							</td>
							<td class="menu_group_headers_text">
								Transactions
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_headers">
						<table>
							<tr>
							<td>
								<img src="<?php echo $rootpath;?>/css/<?php echo $theme;?>/images/reports.png" alt="Inquiries and Reports" />
							</td>
							<td class="menu_group_headers_text">
								Inquiries and Reports
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_headers">
						<table>
							<tr>
							<td>
								<img src="<?php echo $rootpath;?>/css/<?php echo $theme;?>/images/maintenance.png" alt="Maintenance" />
							</td>
							<td class="menu_group_headers_text">
								Maintenance
							</td>
							</tr>
						</table>
					</td>
					</tr>
					
					
					<tr>
					<td class="menu_group_items">  <!-- PO Transactions -->
						<table width="100%">
							<tr>
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/PO_Header.php?" . SID . "NewOrder=Yes"; ?>">Purchase Order Entry</a><br />
							</td>  
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/PO_SelectOSPurchOrder.php?" . SID; ?>">Outstanding Purchase Orders </a><br/>
							</td>  
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/SelectSupplier.php?" . SID; ?>">Shipment Entry</a><br/>
							</td>  
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/Shipt_Select.php?" . SID; ?>">Select A Shipment</a><br/>
							</td>
							</tr>
						</table>		
					</td>
					<td class="menu_group_items">  <!-- PO Inquiries -->
						<table width="100%">
							<tr>
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/PO_SelectPurchOrder.php?" . SID; ?>">Purchase Order Inquiry</a><br />
							</td>
							</tr>
						</table>		
					</td>
					<td class="menu_group_items">   <!-- PO Maintenance -->
						<table width="100%">
							<tr>
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/PurchData.php?" . SID; ?>">Maintain Purchasing Data</a><br />
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
					<tr>
					<td class="menu_group_headers">
						<table>
							<tr>
							<td>
								<img src="<?php echo $rootpath;?>/css/<?php echo $theme;?>/images/transactions.png" alt="Transactions" /> 
							</td>
							<td class="menu_group_headers_text">
								Transactions
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_headers">
						<table>
							<tr>
							<td>
								<img src="<?php echo $rootpath;?>/css/<?php echo $theme;?>/images/reports.png" alt="Inquiries and Reports" />
							</td>
							<td class="menu_group_headers_text">
								Inquiries and Reports
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_headers">
						<table>
							<tr>
							<td>
								<img src="<?php echo $rootpath;?>/css/<?php echo $theme;?>/images/maintenance.png" alt="Maintenance" />
							</td>
							<td class="menu_group_headers_text">
								Maintenance
							</td>
							</tr>
						</table>
					</td>
					</tr>
					<tr>
					<td class="menu_group_items">
						<table width="100%">
							<tr>
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/StockLocTransfer.php?" . SID; ?>">Bulk Inventory Transfer - Dispatch</a><br/>
							</td>  
							</tr>
							<tr>
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/StockLocTransferReceive.php?" . SID . "NewTransfer=Yes"; ?>">Bulk Inventory Transfer - Receive</a><br/>
							</td>  
							</tr>
							<tr>
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/StockTransfers.php?" . SID; ?>">Inventory Location Transfers</a><br />
							</td>  
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/StockAdjustments.php?" . SID; ?>">Inventory Adjustments</a><br />
							</td>  
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/ReverseGRN.php?" . SID; ?>">Reverse Goods Received</a><br />
							</td>  
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/StockCounts.php?" . SID; ?>">Enter Stock Counts</a><br />
							</td>
							</tr>
						</table>		
					</td>
					<td class="menu_group_items">
						<table width="100%">
							<tr>
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/StockMovements.php?" . SID; ?>">Inventory Item Movements</a><br />
							</td>  
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/StockStatus.php?" . SID; ?>">Inventory Item Status</a><br />
							</td>  
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/StockUsage.php?" . SID; ?>">Inventory Item Usage</a><br />
							</td>  
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/InventoryValuation.php?" . SID; ?>">Inventory Valuation Report</a><br />
							</td>  
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/InventoryPlanning.php?" . SID; ?>">Inventory Planning Report</a><br />
							</td>  
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/StockCheck.php?" . SID; ?>">Inventory Stock Check Sheets</a><br />
							</td>  
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/StockQties_csv.php?" . SID; ?>">Make Inventory Quantities CSV</a><br />
							</td>  
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/PDFStockCheckComparison.php?" . SID; ?>">Compare Counts Vs Stock Check Data</a><br />
							</td>
							</tr>
										<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/StockLocMovements.php?" . SID; ?>">All Inventory Movements By Location/Date</a><br />
							</td>
							</tr>
										<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/StockLocStatus.php?" . SID; ?>">List Inventory Status By Location/Category</a><br />
							</td>
							</tr>
						</table>		
					</td>
					<td class="menu_group_items">
						<table width="100%">
							<tr>
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/Stocks.php?" . SID; ?>">Add Inventory Items</a><br />
							</td>  
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/SelectProduct.php?" . SID; ?>">Modify Or Delete A Inventory Item Or Item Pricing</a><br />
							</td>  
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/StockReorderLevel.php?" . SID; ?>">Maintain Reorder Levels</a><br />
							</td>  
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/StockCostUpdate.php?" . SID; ?>">Modify Standard Costs</a><br />
							</td>  
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/PurchData.php?" . SID; ?>">Maintain Purchasing Data</a><br />
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
					<tr>
					<td class="menu_group_headers">
						<table>
							<tr>
							<td>
								<img src="<?php echo $rootpath;?>/css/<?php echo $theme;?>/images/transactions.png" alt="Transactions" /> 
							</td>
							<td class="menu_group_headers_text">
								Transactions
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_headers">
						<table>
							<tr>
							<td>
								<img src="<?php echo $rootpath;?>/css/<?php echo $theme;?>/images/reports.png" alt="Inquiries and Reports" />
							</td>
							<td class="menu_group_headers_text">
								Inquiries and Reports
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_headers">
						<table>
							<tr>
							<td>
								<img src="<?php echo $rootpath;?>/css/<?php echo $theme;?>/images/maintenance.png" alt="Maintenance" />
							</td>
							<td class="menu_group_headers_text">
								Maintenance
							</td>
							</tr>
						</table>
					</td>
	
					<tr>
						
					<td class="menu_group_items">
						<table width="100%">
							<tr>
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/BOMInquiry.php?" . SID; ?>">Costed Bill Of Material Inquiry</a><br />
							</td>  
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/WhereUsedInquiry.php?" . SID; ?>">Where Used Inquiry</a><br />
							</td>  
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/BOMListing.php?" . SID; ?>">Bills Of Material Listing</a><br />
							</td>
							</tr>
						</table>		
					</td>
					<td class="menu_group_items">
						<table width="100%">
							<tr>
							<td class="menu_group_item">
							<a href="<?php echo $rootpath."/WorkCentres.php?" . SID; ?>">Work Centre</a><br />
							</td>  
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/BOMs.php?" . SID; ?>">Bills Of Material</a><br />
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
								<img src="<?php echo $rootpath;?>/css/<?php echo $theme;?>/images/company.png" alt="General Setup Options" />
							</td>
							<td class="menu_group_headers_text">
								General
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_headers">
						<table>
							<tr>
							<td>
								<img src="<?php echo $rootpath;?>/css/<?php echo $theme;?>/images/ar.png" alt="Receivables/Payables Setup" />
							</td>
							<td class="menu_group_headers_text">
								Receivables/Payables
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_headers">
						<table>
							<tr>
							<td>
								<img src="<?php echo $rootpath;?>/css/<?php echo $theme;?>/images/inventory.png" alt="Inventory Setup" />
							</td>
							<td class="menu_group_headers_text">
								Inventory Setup
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
								<a href="<?php echo $rootpath."/CompanyPreferences.php?" . SID; ?>">Company Preferences</a><br />
							</td>
							</tr>
													<tr>
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/WWW_Users.php?" . SID; ?>">User Accounts</a><br />
							</td>  
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/BankAccounts.php?" . SID; ?>">Bank Accounts</a><br />
							</td>
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/Currencies.php?" . SID; ?>">Currency Maintenance</a><br />
							</td>  
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/TaxAuthorities.php?" . SID; ?>">Tax Types and Rates Maintenance</a><br />
							</td>  
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/PeriodsInquiry.php?" . SID; ?>">List Periods Defined <FONT SIZE=1>(Periods are automatically maintained)</a><br />
							</td>  
							</tr>
						</table>		
					</td>
				
					<td class="menu_group_items">	<!-- AR/AP set-up options -->
						<table width="100%">
							<tr>
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/SalesTypes.php?" . SID; ?>">Sales Types</a><br />
							</td>  
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/CreditStatus.php?" . SID; ?>">Credit Status</a><br />
							</td>  
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/PaymentTerms.php?" . SID; ?>">Payment Terms</a><br />
							</td>
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/SalesPeople.php?" . SID; ?>">Sales People</a><br />
							</td>
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/Areas.php?" . SID; ?>">Sales Areas</a><br />
							</td>
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/Shippers.php?" . SID;?>">Shippers</a><br />
							</td>
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/SalesGLPostings.php?" . SID; ?>">Sales GL Interface Postings </a><br />
							</td>
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/COGSGLPostings.php?" . SID; ?>">COGS GL Interface Postings</a><br />
							</td>
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/FreightCosts.php?" . SID;?>">Freight Costs Maintenance</a><br />
							</td>
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/DiscountMatrix.php?" . SID; ?>">Discount Matrix</a><br />
							</td>
							</tr>
							

						</table>		
					</td>
					
					<td class="menu_group_items">	<!-- Inventory set-up options -->
						<table width="100%">
							<tr>
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/StockCategories.php?" . SID; ?>">Inventory Categories Maintenance</a><br />
							</td>  
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/Locations.php?" . SID; ?>">Inventory Locations Maintenance</a><br />
							</td>
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/DiscountCategories.php?" . SID; ?>">Discount Category Maintenance</a><br />
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
				<table width="100%">
					<tr>			<!-- Gereral Ledger Option Headings-->
					<td class="menu_group_headers">
						<table>
							<tr>
							<td>
								<img src="<?echo $rootpath;?>/css/<?echo $theme;?>/images/transactions.png" alt="Transactions" /> 
							</td>
							<td class="menu_group_headers_text">
								Transactions
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_headers">
						<table>
							<tr>
							<td>
								<img src="<?echo $rootpath;?>/css/<?echo $theme;?>/images/reports.png" alt="Inquiries and Reports" />
							</td>
							<td class="menu_group_headers_text">
								Inquiries and Reports
							</td>
							</tr>
						</table>
					</td>
					<td class="menu_group_headers">
						<table>
							<tr>
							<td>
								<img src="<?echo $rootpath;?>/css/<?echo $theme;?>/images/maintenance.png" alt="Maintenance" />
							</td>
							<td class="menu_group_headers_text">
								Maintenance
							</td>
							</tr>
						</table>
					</td>
					</tr>
					
					
					<tr>
					<td class="menu_group_items"> <!-- Gereral transactions options -->
						<table width="100%">
							<tr>
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/Payments.php?" . SID . "NewPayment=Yes"; ?>">Bank Account Payments</a><br />
							</td>  
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/CustomerReceipt.php?" . SID; ?>">Bank Account Receipts</a><br />
							</td>  
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/GLJournal.php?" . SID . "NewJournal=Yes"; ?>">Journal Entry</a><br />
							</td>  
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/BankMatching.php?" . SID . "Type=Receipts"; ?>">Bank Deposits Matching</a><br />
							</td>  
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/BankMatching.php?" . SID . "Type=Payments"; ?>">Bank Payments Matching</a><br />
							</td>
							</tr>
						</table>		
					</td>
					<td class="menu_group_items">  <!-- Gereral inquiry options -->
						<table width="100%">
							<tr>
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/GLTrialBalance.php?" . SID; ?>">Trial Balance</a><br />
							</td>  
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/SelectGLAccount.php?" . SID; ?>">Account Inquiry</a><br />
							</td>  
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/BankReconciliation.php?" . SID; ?>">Bank Reconciliation Statement</a><br />
							</td>  
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/PDFChequeListing.php?" . SID; ?>">Cheque Payments Listing</a><br />
							</td>
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/GLProfit_Loss.php?" . SID; ?>">Profit and Loss Statement</a><br />
							</td>
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/GLBalanceSheet.php?" . SID; ?>">Balance Sheet</a><br />
							</td>
							</tr>


						</table>		
					</td>
					<td class="menu_group_items">  <!-- Gereral Ledger Maintenance options -->
						<table width="100%">
							<tr>
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/GLAccounts.php?" . SID; ?>">GL Account</a><br />
							</td>  
							</tr>
							<tr>  
							<td class="menu_group_item">
								<a href="<?php echo $rootpath."/AccountGroups.php?" . SID; ?>">Account Group</a><br />
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
?>
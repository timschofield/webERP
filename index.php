<?php
/* $Revision: 1.3 $ */
$title="Main Menu";

$PageSecurity = 1;

include("includes/session.inc");
include("includes/header.inc");

if (count($SecurityGroups[$_SESSION["AccessLevel"]])==1){

/* if there is only one security access and its 1 (it has to be 1 for this page came up at all)- it must be a customer log on need to limit the menu to show only the customer accessible stuff this is what the page looks like for customers logging in */

	echo "<font size='3'><br /><br /><br />";
	echo "<a href='" . $rootpath . "/CustomerInquiry.php?" . SID . "CustomerID=" . $_SESSION["CustomerID"] . "'>Account Status</a><br />";
	echo "<a href='" . $rootpath . "/SelectOrderItems.php?" . SID . "NewOrder=Yes'>Place An Order</a><br />";
	exit;
}

/*ModuleList array with the text of modules now in config.php */
/*The module link codes are hard coded in a switch statement below to determine the options to show for each tab */
$ModuleLink = array("orders", "AR", "AP", "PO", "stock", "manuf", "GL", "system");

echo "<table class='main_menu'><tr>";

if (isset($_GET['Application'])){ /*This is sent by this page (to itself) when the user clicks on a tab */
	$_SESSION['Module'] = $_GET['Application'];
}

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
			echo "<td><a href='". $_SERVER['PHP_SELF'] ."?". SID ."Application=". $ModuleLink[$i] ."'>". $ModuleList[$i] ."</a></td>";
		}
	}
	$i++;
}

?>

</tr></table>

<table border="0" width="100%">

<?php


switch ($_SESSION['Module']) {

Case "orders": //Sales Orders
?>
<tr>
    <td>
        <table width="100%">
            <tr>
                <td class="menu_group_img"><img src="<?php echo $rootpath;?>/css/<?php echo $theme;?>/images/transactions.png" alt="Transactions" /></td>

                <td valign="top" class="menu_group">
				<table width="100%">
				    <tr>
				    	<td class="menu_group">Transactions</td>
				    </tr>
				    <tr>
				    	<td class="menu_group_items">
						<a href="<?php echo $rootpath . "/SelectOrderItems.php?" . SID . "NewOrder=Yes"; ?>">Sales Order Entry</a><br />
						<a href="<?php echo $rootpath . "/SelectSalesOrder.php?" . SID; ?>">Outstanding Sales Orders Maintenance</a><br />
						<a href="<?php echo $rootpath . "/SpecialOrder.php?" . SID . "NewSpecial=Yes"; ?>">Special Order</a><br/>
				    	</td>
				    </tr>
				</table>
				</td>
            </tr>

            <tr>
                <td class="menu_group_img"><img src="<?php echo $rootpath;?>/css/<?php echo $theme;?>/images/reports.png" alt="Inquiries and Reports" /></td>

                <td valign="top" class="menu_group">
				<table width="100%">
				    <tr>
				    	<td class="menu_group">Inquiries and Reports</td>
				    </tr>
				    <tr>
				    	<td class="menu_group_items">
						<!--<a href="<?php echo $rootpath."/FTP_RadioBeacon.php?" . SID; ?>">Send Order to Radio Beacon Warehouse</a><br />-->
						<a href="<?php echo $rootpath."/SelectCompletedOrder.php?" . SID; ?>">Order Inquiry</a><br />
						<a href="<?php echo $rootpath."/PDFPriceList.php?" . SID; ?>">Print Price Lists</a><br />
						<a href="<?php echo $rootpath."/PDFDeliveryDifferences.php?" . SID; ?>">Order Delivery Differences Report</a><br />
				    	</td>
				    </tr>
				</table>
				</td>
            </tr>
        </table>
    </td>
</tr>

<?php
	break;
/* ****************** END OF ORDERS MENU ITEMS **************************** */

Case "AR": //Debtors Module

     unset($ReceiptBatch);
     unset($AllocTrans);
?>
<tr>
    <td>
        <table width="100%">
            <tr>
                <td class="menu_group_img"><img src="<?php echo $rootpath;?>/css/<?php echo $theme;?>/images/transactions.png" alt="Transactions" /></td>

                <td valign="top" class="menu_group">
				<table width="100%">
				    <tr>
				    	<td class="menu_group">Transactions</td>
				    </tr>
				    <tr>
				    	<td class="menu_group_items">
						<a href="<?php echo $rootpath."/SelectSalesOrder.php?" . SID; ?>">Select Order to Invoice</a><br />
						<a href="<?php echo $rootpath."/SelectCreditItems.php?" . SID . "&NewCredit=Yes"; ?>">Create A Credit Note</a><br />
						<a href="<?php echo $rootpath."/CustomerReceipt.php?" . SID; ?>">Enter Receipts</a><br />
						<a href="<?php echo $rootpath."/CustomerAllocations.php?" . SID; ?>">Allocate Receipts or Credit Notes</a><br />
				    	</td>
				    </tr>
				</table>
				</td>
            </tr>

            <tr>
                <td class="menu_group_img"><img src="<?php echo $rootpath;?>/css/<?php echo $theme;?>/images/reports.png" alt="Inquiries and Reports" /></td>

                <td valign="top" class="menu_group">
				<table width="100%">
				    <tr>
				    	<td class="menu_group">Inquiries and Reports</td>
				    </tr>
				    <tr>
				    	<td class="menu_group_items">
						<a href="<?php echo $rootpath."/SelectCustomer.php?" . SID; ?>">Customer Transaction Inquiries</a><br />
						<a href="<?php echo $rootpath."/CustWhereAlloc.php?" . SID; ?>">Where Allocated Inquiry</a><br />
						<a href="<?php echo $rootpath."/PrintCustTrans.php?" . SID; ?>">Print Invoices or Credit Notes</a><br />
						<a href="<?php echo $rootpath."/PrintCustStatements.php?" . SID; ?>">Print Statements</a><br />
						<a href="<?php echo $rootpath."/SalesAnalRepts.php?" . SID; ?>">Sales Analysis Reports</a><br />
						<a href="<?php echo $rootpath."/AgedDebtors.php?" . SID; ?>">Aged Customer Balances/Overdues Report</a><br />
						<a href="<?php echo $rootpath."/CustomerTransInquiry.php?" . SID; ?>">Transaction Inquiries</a><br />
						<a href="<?php echo $rootpath."/PDFBankingSummary.php?" . SID; ?>">Re-Print A Deposit Listing</a><br />
				    	</td>
				    </tr>
				</table>
				</td>
            </tr>

            <tr>
                <td class="menu_group_img"><img src="<?php echo $rootpath;?>/css/<?php echo $theme;?>/images/maintenance.png" alt="Maintenance" /></td>

                <td valign="top" class="menu_group">
				<table width="100%">
				    <tr>
				    	<td class="menu_group">Maintenance</td>
				    </tr>
				    <tr>
				    	<td class="menu_group_items">
						<a href="<?php echo $rootpath."/Customers.php?" . SID; ?>">Add a New Customer</a><br />
						<a href="<?php echo $rootpath."/SelectCustomer.php?" . SID; ?>">Modify/Add/Delete Customers/branches and Special pricing</a><br />
				    	</td>
				    </tr>
				</table>
				</td>
            </tr>
        </table>
    </td>
</tr>

<?php
	break;
Case "AP": //Creditors Module

?>
<tr>
    <td>
        <table width="100%">
            <tr>
                <td class="menu_group_img"><img src="<?php echo $rootpath;?>/css/<?php echo $theme;?>/images/transactions.png" alt="Transactions" /></td>

                <td valign="top" class="menu_group">
				<table width="100%">
				    <tr>
				    	<td class="menu_group">Transactions</td>
				    </tr>
				    <tr>
				    	<td class="menu_group_items">
						<a href="<?php echo $rootpath ."/SelectSupplier.php?" . SID; ?>">Select Supplier For Transactions and Inquiries</a><br />
						<a href="<?php echo $rootpath ."/SupplierAllocations.php?" . SID; ?>">Supplier Allocations</a><br />
				    	</td>
				    </tr>
				</table>
				</td>
            </tr>

            <tr>
                <td class="menu_group_img"><img src="<?php echo $rootpath;?>/css/<?php echo $theme;?>/images/reports.png" alt="Inquiries and Reports" /></td>

                <td valign="top" class="menu_group">
				<table width="100%">
				    <tr>
				    	<td class="menu_group">Inquiries and Reports</td>
				    </tr>
				    <tr>
				    	<td class="menu_group_items">
						<a href="<?php echo $rootpath ."/AgedSuppliers.php?" . SID; ?>">Aged Supplier Report</a><br />
						<a href="<?php echo $rootpath ."/SuppPaymentRun.php?" . SID; ?>">Payment Run Report</a><br />
						<a href="<?php echo $rootpath ."/OutstandingGRNs.php?" . SID; ?>">Outstanding GRNs Report</a><br />
				    	</td>
				    </tr>
				</table>
				</td>
            </tr>

            <tr>
                <td class="menu_group_img"><img src="<?php echo $rootpath;?>/css/<?php echo $theme;?>/images/maintenance.png" alt="Maintenance" /></td>

                <td valign="top" class="menu_group">
				<table width="100%">
				    <tr>
				    	<td class="menu_group">Maintenance</td>
				    </tr>
				    <tr>
				    	<td class="menu_group_items">
						<a href="<?php echo $rootpath."/Suppliers.php?" . SID; ?>">Add a New Supplier</a><br />
						<a href="<?php echo $rootpath."/SelectSupplier.php?" . SID; ?>">Modify Or Delete A Supplier</a><br />
				    	</td>
				    </tr>
				</table>
				</td>
            </tr>
        </table>
    </td>
</tr>

<?php
	break;

Case "PO": //Purchase ordering

?>
<tr>
    <td>
        <table width="100%">
            <tr>
                <td class="menu_group_img"><img src="<?php echo $rootpath;?>/css/<?php echo $theme;?>/images/transactions.png" alt="Transactions" /></td>

                <td valign="top" class="menu_group">
				<table width="100%">
				    <tr>
				    	<td class="menu_group">Transactions</td>
				    </tr>
				    <tr>
				    	<td class="menu_group_items">
						<a href="<?php echo $rootpath."/PO_Header.php?" . SID . "NewOrder=Yes"; ?>">Purchase Order Entry</a><br />
						<a href="<?php echo $rootpath."/PO_SelectOSPurchOrder.php?" . SID; ?>">Outstanding Purchase Orders Maintenance</a><br/>
						<a href="<?php echo $rootpath."/SelectSupplier.php?" . SID; ?>">Shipment Entry</a><br/>
						<a href="<?php echo $rootpath."/Shipt_Select.php?" . SID; ?>">Select A Shipment</a><br/>
				    	</td>
				    </tr>
				</table>
				</td>
            </tr>

            <tr>
                <td class="menu_group_img"><img src="<?php echo $rootpath;?>/css/<?php echo $theme;?>/images/reports.png" alt="Inquiries and Reports" /></td>

                <td valign="top" class="menu_group">
				<table width="100%">
				    <tr>
				    	<td class="menu_group">Inquiries and Reports</td>
				    </tr>
				    <tr>
				    	<td class="menu_group_items">
						<a href="<?php echo $rootpath."/PO_SelectPurchOrder.php?" . SID; ?>">Purchase Order Inquiry</a><br />
				    	</td>
				    </tr>
				</table>
				</td>
            </tr>

            <tr>
                <td class="menu_group_img"><img src="<?php echo $rootpath;?>/css/<?php echo $theme;?>/images/maintenance.png" alt="Maintenance" /></td>

                <td valign="top" class="menu_group">
				<table width="100%">
				    <tr>
				    	<td class="menu_group">Maintenance</td>
				    </tr>
				    <tr>
				    	<td class="menu_group_items">
						<a href="<?php echo $rootpath."/PurchData.php?" . SID; ?>">Maintain Purchasing Data</a><br />
				    	</td>
				    </tr>
				</table>
				</td>
            </tr>
        </table>
    </td>
</tr>

	</td>
<?php
	break;

Case "stock": //Inventory Module

?>
<tr>
    <td>
        <table width="100%">
            <tr>
                <td class="menu_group_img"><img src="<?php echo $rootpath;?>/css/<?php echo $theme;?>/images/transactions.png" alt="Transactions" /></td>

                <td valign="top" class="menu_group">
				<table width="100%">
				    <tr>
				    	<td class="menu_group">Transactions</td>
				    </tr>
				    <tr>
				    	<td class="menu_group_items">
						<a href="<?php echo $rootpath."/StockTransfers.php?" . SID; ?>">Inventory Location Transfers</a><br />
						<a href="<?php echo $rootpath."/StockAdjustments.php?" . SID; ?>">Inventory Adjustments</a><br />
						<a href="<?php echo $rootpath."/ReverseGRN.php?" . SID; ?>">Reverse Goods Received</a><br />
						<a href="<?php echo $rootpath."/StockCounts.php?" . SID; ?>">Enter Stock Counts</a><br />
				    	</td>
				    </tr>
				</table>
				</td>
            </tr>

            <tr>
                <td class="menu_group_img"><img src="<?php echo $rootpath;?>/css/<?php echo $theme;?>/images/reports.png" alt="Inquiries and Reports" /></td>

                <td valign="top" class="menu_group">
				<table width="100%">
				    <tr>
				    	<td class="menu_group">Inquiries and Reports</td>
				    </tr>
				    <tr>
				    	<td class="menu_group_items">
						<a href="<?php echo $rootpath."/StockMovements.php?" . SID; ?>">Inventory Item Movements</a><br />
						<a href="<?php echo $rootpath."/StockStatus.php?" . SID; ?>">Inventory Item Status</a><br />
						<a href="<?php echo $rootpath."/StockUsage.php?" . SID; ?>">Inventory Item Usage</a><br />
						<a href="<?php echo $rootpath."/InventoryValuation.php?" . SID; ?>">Inventory Valuation Report</a><br />
						<a href="<?php echo $rootpath."/InventoryPlanning.php?" . SID; ?>">Inventory Planning Report</a><br />
						<a href="<?php echo $rootpath."/StockCheck.php?" . SID; ?>">Inventory Stock Check Sheets</a><br />
						<a href="<?php echo $rootpath."/StockQties_csv.php?" . SID; ?>">Make Inventory Quantities CSV</a><br />
						<a href="<?php echo $rootpath."/PDFStockCheckComparison.php?" . SID; ?>">Compare Counts Vs Stock Check Data</a><br />

				    	</td>
				    </tr>
				</table>
				</td>
            </tr>

            <tr>
                <td class="menu_group_img"><img src="<?php echo $rootpath;?>/css/<?php echo $theme;?>/images/maintenance.png" alt="Maintenance" /></td>

                <td valign="top" class="menu_group">
				<table width="100%">
				    <tr>
				    	<td class="menu_group">Maintenance</td>
				    </tr>
				    <tr>
				    	<td class="menu_group_items">
						<a href="<?php echo $rootpath."/Stocks.php?" . SID; ?>">Add Inventory Items</a><br />
						<a href="<?php echo $rootpath."/SelectProduct.php?" . SID; ?>">Modify Or Delete A Inventory Item Or Item Pricing</a><br />
						<a href="<?php echo $rootpath."/StockReorderLevel.php?" . SID; ?>">Maintain Reorder Levels</a><br />
						<a href="<?php echo $rootpath."/StockCostUpdate.php?" . SID; ?>">Modify Standard Costs</a><br />
						<a href="<?php echo $rootpath."/PurchData.php?" . SID; ?>">Maintain Purchasing Data</a><br />
				    	</td>
				    </tr>
				</table>
				</td>
            </tr>
        </table>
    </td>
</tr>

<?php
	break;

Case "manuf": //Manufacturing Module

?>

<tr>
    <td>
        <table width="100%">
            <tr>
                <td class="menu_group_img"><img src="<?php echo $rootpath;?>/css/<?php echo $theme;?>/images/transactions.png" alt="Transactions" /></td>

                <td valign="top" class="menu_group">
				<table width="100%">
				    <tr>
				    	<td class="menu_group">Transactions</td>
				    </tr>
				    <tr>
				    	<td class="menu_group_items">
				    	<br />
				    	</td>
				    </tr>
				</table>
				</td>
            </tr>
            
            <tr>
                <td class="menu_group_img"><img src="<?php echo $rootpath;?>/css/<?php echo $theme;?>/images/reports.png" alt="Inquiries and Reports" /></td>

                <td valign="top" class="menu_group">
				<table width="100%">
				    <tr>
				    	<td class="menu_group">Inquiries and Reports</td>
				    </tr>
				    <tr>
				    	<td class="menu_group_items">
						<a href="<?php echo $rootpath."/BOMInquiry.php?" . SID; ?>">Costed Bill Of Material Inquiry</a><br />
						<a href="<?php echo $rootpath."/WhereUsedInquiry.php?" . SID; ?>">Where Used Inquiry</a><br />
						<a href="<?php echo $rootpath."/BOMListing.php?" . SID; ?>">Bills Of Material Listing</a><br />
				    	</td>
				    </tr>
				</table>
				</td>
            </tr>
            
            <tr>
                <td class="menu_group_img"><img src="<?php echo $rootpath;?>/css/<?php echo $theme;?>/images/maintenance.png" alt="Maintenance" /></td>

                <td valign="top" class="menu_group">
				<table width="100%">
				    <tr>
				    	<td class="menu_group">Maintenance</td>
				    </tr>
				    <tr>
				    	<td class="menu_group_items">
						<a href="<?php echo $rootpath."/WorkCentres.php?" . SID; ?>">Work Centre Maintenance</a><br />
						<a href="<?php echo $rootpath."/BOMs.php?" . SID; ?>">Bills Of Material Maintenance</a><br />
				    	</td>
				    </tr>
				</table>
				</td>
            </tr>
        </table>
    </td>
</tr>

<?php
	break;

Case "system": //System setup

?>
<tr>
    <td>
        <table width="100%">
            <tr>
                <td class="menu_group_img"><img src="<?php echo $rootpath;?>/css/<?php echo $theme;?>/images/company.png" alt="Company Setup" /></td>

                <td valign="top" class="menu_group">
				<table width="100%">
				    <tr>
				    	<td class="menu_group">Company Setup</td>
				    </tr>
				    <tr>
				    	<td class="menu_group_items">
						<a href="<?php echo $rootpath."/CompanyPreferences.php?" . SID; ?>">Company Preferences Setup</a><br />
				
				    	</td>
				    </tr>
				</table>
				</td>
            </tr>
            
            <tr>
                <td class="menu_group_img"><img src="<?php echo $rootpath;?>/css/<?php echo $theme;?>/images/security.png" alt="Security and Users Setup" /></td>

                <td valign="top" class="menu_group">
				<table width="100%">
				    <tr>
				    	<td class="menu_group">Security and Users Setup</td>
				    </tr>
				    <tr>
				    	<td class="menu_group_items">
						<a href="<?php echo $rootpath."/WWW_Users.php?" . SID; ?>">Set Up User Accounts</a><br />
				    	</td>
				    </tr>
				</table>
				</td>
            </tr>

            <tr>
                <td class="menu_group_img"><img src="<?php echo $rootpath;?>/css/<?php echo $theme;?>/images/ar.png" alt="Accounts Receivable Setup" /></td>

                <td valign="top" class="menu_group">
				<table width="100%">
				    <tr>
				    	<td class="menu_group">Accounts Receivable Setup</td>
				    </tr>
				    <tr>
				    	<td class="menu_group_items">
						<a href="<?php echo $rootpath."/SalesTypes.php?" . SID; ?>">Sales Type Maintenance</a><br />
						<a href="<?php echo $rootpath."/CreditStatus.php?" . SID; ?>">Credit Status Maintenance</a><br />
						<a href="<?php echo $rootpath."/PaymentTerms.php?" . SID; ?>">Payment Terms Maintenance</a><br />
						<a href="<?php echo $rootpath."/SalesPeople.php?" . SID; ?>">Sales Persons Maintenance</a><br />
						<a href="<?php echo $rootpath."/Areas.php?" . SID; ?>">Sales Areas Maintenance</a><br />
						<a href="<?php echo $rootpath."/Shippers.php?" . SID;?>">Shipping Companies Maintenance</a><br />
						<a href="<?php echo $rootpath."/FreightCosts.php?" . SID;?>">Freight Costs Maintenance</a><br />
						<a href="<?php echo $rootpath."/TaxAuthorities.php?" . SID; ?>">Tax Types and Rates Maintenance</a><br />
						<a href="<?php echo $rootpath."/SalesGLPostings.php?" . SID; ?>">Sales GL Interface Postings </a><br />
						<a href="<?php echo $rootpath."/COGSGLPostings.php?" . SID; ?>">COGS GL Interface Postings</a><br />
						<a href="<?php echo $rootpath."/DiscountMatrix.php?" . SID; ?>">Discount Matrix</a><br />
				<td>
				    </tr>
				</table>
				</td>
            </tr>

            <tr>
                <td class="menu_group_img"><img src="<?php echo $rootpath;?>/css/<?php echo $theme;?>/images/inventory.png" alt="Inventory Setup" /></td>

                <td valign="top" class="menu_group">
				<table width="100%">
				    <tr>
				    	<td class="menu_group">Inventory Setup</td>
				    </tr>
				    <tr>
				    	<td class="menu_group_items">
						<a href="<?php echo $rootpath."/StockCategories.php?" . SID; ?>">Inventory Categories Maintenance</a><br />
						<a href="<?php echo $rootpath."/Locations.php?" . SID; ?>">Inventory Locations Maintenance</a><br />
						<a href="<?php echo $rootpath."/DiscountCategories.php?" . SID; ?>">Discount Category Maintenance</a><br />
				    	</td>
				    </tr>
				</table>
				</td>
            </tr>

            <tr>
                <td class="menu_group_img"><img src="<?php echo $rootpath;?>/css/<?php echo $theme;?>/images/bank.gif" alt="Bank Setup" /></td>

                <td valign="top" class="menu_group">
				<table width="100%">
				    <tr>
				    	<td class="menu_group">Bank Setup</td>
				    </tr>
				    <tr>
				    	<td class="menu_group_items">
							<a href="<?php echo $rootpath."/BankAccounts.php?" . SID; ?>">Bank Accounts Maintenance</a><br />
				    	</td>
				    </tr>
				</table>
				</td>
            </tr>

            <tr>
                <td class="menu_group_img"><img src="<?php echo $rootpath;?>/css/<?php echo $theme;?>/images/currency.gif" alt="Currency Setup" /></td>

                <td valign="top" class="menu_group">
				<table width="100%">
				    <tr>
				    	<td class="menu_group">Currency Setup</td>
				    </tr>
				    <tr>
				    	<td class="menu_group_items">
						<a href="<?php echo $rootpath."/Currencies.php?" . SID; ?>">Currency Maintenance</a><br />
				    	</td>
				    </tr>
				</table>
				</td>
            </tr>
            
            <tr>
                <td class="menu_group_img"><img src="<?php echo $rootpath;?>/css/<?php echo $theme;?>/images/inquiries.png" alt="Inquiries" /></td>

                <td valign="top" class="menu_group">
				<table width="100%">
				    <tr>
				    	<td class="menu_group">Inquiries</td>
				    </tr>
				    <tr>
				    	<td class="menu_group_items">
						<a href="<?php echo $rootpath."/PeriodsInquiry.php?" . SID; ?>">List Periods Defined <FONT SIZE=1>(Periods are automatically maintained)</a><br />
				    	</td>
				    </tr>
				</table>
				</td>
            </tr>
        </table>
    </td>
</tr>

<?php
	break;

Case "GL": //General Ledger

?>
<tr>
    <td>
        <table width="100%">
            <tr>
                <td class="menu_group_img"><img src="<?php echo $rootpath;?>/css/<?php echo $theme;?>/images/transactions.png" alt="Transactions" /></td>

                <td valign="top" class="menu_group">
				<table width="100%">
				    <tr>
				    	<td class="menu_group">Transactions</td>
				    </tr>
				    <tr>
				    	<td class="menu_group_items">
						<a href="<?php echo $rootpath."/Payments.php?" . SID . "NewPayment=Yes"; ?>">Bank Account Payments</a><br />
						<a href="<?php echo $rootpath."/CustomerReceipt.php?" . SID; ?>">Bank Account Receipts</a><br />
						<a href="<?php echo $rootpath."/GLJournal.php?" . SID . "NewJournal=Yes"; ?>">Journal Entry</a><br />
						<a href="<?php echo $rootpath."/BankMatching.php?" . SID . "Type=Receipts"; ?>">Bank Deposits Matching</a><br />
						<a href="<?php echo $rootpath."/BankMatching.php?" . SID . "Type=Payments"; ?>">Bank Payments Matching</a><br />
				    	</td>
				    </tr>
				</table>
				</td>
            </tr>
            
            <tr>
                <td class="menu_group_img"><img src="<?php echo $rootpath;?>/css/<?php echo $theme;?>/images/reports.png" alt="Inquiries and Reports" /></td>

                <td valign="top" class="menu_group">
				<table width="100%">
				    <tr>
				    	<td class="menu_group">Inquiries and Reports</td>
				    </tr>
				    <tr>
				    	<td class="menu_group_items">
						<a href="<?php echo $rootpath."/GLTrialBalance.php?" . SID; ?>">Trial Balance</a><br />
						<a href="<?php echo $rootpath."/SelectGLAccount.php?" . SID; ?>">Account Inquiry</a><br />
						<a href="<?php echo $rootpath."/BankReconciliation.php?" . SID; ?>">Bank Reconciliation Statement</a><br />
						<a href="<?php echo $rootpath."/PDFChequeListing.php?" . SID; ?>">Cheque Payments Listing</a><br />
				    	</td>
				    </tr>
				</table>
				</td>
            </tr>

            <tr>
                <td class="menu_group_img"><img src="<?php echo $rootpath;?>/css/<?php echo $theme;?>/images/maintenance.png" alt="Maintenance" /></td>

                <td valign="top" class="menu_group">
				<table width="100%">
				    <tr>
				    	<td class="menu_group">Maintenance</td>
				    </tr>
				    <tr>
				    	<td class="menu_group_items">
						<a href="<?php echo $rootpath."/GLAccounts.php?" . SID; ?>">GL Account Maintenance</a><br />
						<a href="<?php echo $rootpath."/AccountGroups.php?" . SID; ?>">Account Group Maintenance</a><br />
				    	</td>
				    </tr>
				</table>
				</td>
            </tr>
        </table>
    </td>
</tr>

<?php
	break;
} //end of module switch

echo "</table>";
include("includes/footer.inc");

?>


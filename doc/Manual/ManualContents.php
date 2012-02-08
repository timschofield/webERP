<?php
/* This table of contents allows the choice to display one section or select multiple sections to format for print.
     Selecting multiple sections is for printing
-->

<!-- The individual topics in the manual are in straight html files that are called along with the header and foot from here.
     No style, inline style or style sheet on purpose.
     In this way the help can be easily broken into sections for online context-sensitive help.
		 The only html used in them are:
		 <br />
		 <div>
		 <table>
		 <font>
		 <b>
		 <u>
		 <ul>
		 <ol>

		 Comments beginning with Help Begin and Help End denote the beginning and end of a section that goes into the online help.
		 What section is named after Help Begin: and there can be multiple sections separated with a comma.
-->';*/

$PathPrefix='../../';
include($PathPrefix.'includes/session.inc');

include('ManualHeader.html');
?>
	<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
<?php
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (((!isset($_POST['Submit'])) AND (!isset($_GET['ViewTopic']))) OR
     ((isset($_POST['Submit'])) AND (isset($_POST['SelectTableOfContents'])))) {
// if not submittws then coming into manual to look at TOC
// if SelectTableOfContents set then user wants it displayed
?>
<?php
  if (!isset($_POST['Submit'])) {
?>
          <input type="submit" name="Submit" value="Display Checked">
					Click on a link below to view.  Click checkboxes then Display Checked to format for printing.
					<br /><br /><br />
<?php
  }
?>
    <table cellpadding="0" cellspacing="0">
      <tr>
        <td>
<?php
  if (!isset($_POST['Submit'])) {
?>
  	      <input type="checkbox" name="SelectTableOfContents">
<?php
  }
?>
          <font size="+3"><b>Table of Contents</b></font>
          <br /><br />
          <UL>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectIntroduction">
              <A HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?ViewTopic=Introduction'; ?>">Introduction</A>
<?php
  } else {
?>
              <A href="#Introduction">Introduction</A>
<?php
	}
?>
              <UL>
                <LI>Why Another Accounting Program?</LI>
              </UL>
              <br />
            </LI>
						<LI>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectRequirements">
              <A HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?ViewTopic=Requirements'; ?>">Requirements</A>
<?php
  } else {
?>
              <A href="#Requirements">Requirements</A>
<?php
	}
?>
              <ul>
                <li>Hardware Requirements</li>
                <li>Software Requirements</li>
                <li>Using webERP with a Wiki</li>
              </UL>
              <br />
            </li>
						<li>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectGettingStarted">
              <a HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?ViewTopic=GettingStarted'; ?>">Getting Started</a>
<?php
  } else {
?>
              <a HREF="#GettingStarted">Getting Started</a>
<?php
  }
?>
              <ul>
                <li>Prerequisites</li>
                <li>Copying the PHP Scripts</li>
                <li>Creating the Database</li>
                <li>Editing config.php</li>
                <li>Logging In For the First Time</li>
                <li>Themes and GUI Modification</li>
                <li>Setting Up Users</li>
              </UL>
              <br />
            </li>
            <li>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectSecuritySchema">
              <a HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?ViewTopic=SecuritySchema'; ?>">Security Schema</a>
<?php
  } else {
?>
              <a HREF="#SecuritySchema">Security Schema</a>
<?php
  }
?>
            </li>
            <br /><br />
            <li>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectCreatingNewSystem">
              <a HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?ViewTopic=CreatingNewSystem'; ?>">Creating a New System</a>
<?php
  } else {
?>
              <a HREF="#CreatingNewSystem">Creating a New System</a>
<?php
  }
?>
              <ul>
                <li>Running the Demonstration Database</li>
                <li>Setting Up A System</li>
                <li>Setting Up Inventory Items</li>
                <li>Entering Inventory Balances</li>
                <li>Inventory Ledger Integration to General Ledger Issues</li>
                <li>Setting Up Customers</li>
                <li>Entering Customer Balances</li>
                <li>Reconciling the Debtors Ledger Control Account</li>
                <li>Finally</li>
              </UL>
              <br />
						</li>
            <li>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectSystemConventions">
              <a HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?ViewTopic=SystemConventions'; ?>">System Conventions</a>
<?php
  } else {
?>
              <a HREF="#SystemConventions">System Conventions</a>
<?php
  }
?>
              <ul>
                <li>Navigating the Menu</li>
                <li>Reporting</li>
              </UL>
              <br />
            </li>
						<li>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectInventory">
              <a HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?ViewTopic=Inventory'; ?>">Inventory (aka "Stock")</a>
<?php
  } else {
?>
              <a HREF="#Inventory">Inventory (aka "Stock")</a>
<?php
  }
?>
              <ul>
                <li>Overview</li>
                <li>Inventory System Features</li>
                <li>Inventory Categories</li>
                <li>Adding Inventory Items</li>
                <li>Item Code</li>
                <li>Part Descriptions</li>
                <li>Categories</li>
                <li>Unit of Measurement</li>
                <li>Economic Order Quantity</li>
                <li>Packaged Volume</li>
                <li>Packaged Weight</li>
                <li>Units of Measure</li>
                <li>Current or Obsolete</li>
                <li>Make Or Buy</li>
                <li>Setting Up Assembly Items</li>
                <li>Controlled</li>
                <li>Serialised</li>
                <li>Bar Code</li>
                <li>Discount Category</li>
                <li>Decimal Places</li>
                <li>Inventory Costing</li>
                <li>Material Cost</li>
                <li>Labour Cost</li>
                <li>Overhead Cost</li>
                <li>Standard Costing Considerations</li>
                <li>Actual Cost</li>
                <li>Alterations To Labour Cost, Material Cost or Overhead Cost</li>
                <li>Selecting Inventory Items</li>
                <li>Amending Inventory Items</li>
                <li>Changing a Category</li>
                <li>Alterations to the Make or Buy Flag</li>
                <li>Inventory Categories</li>
                <li>Inventory Category Code</li>
                <li>Inventory Category Description</li>
                <li>Balance Sheet Inventory GL Account</li>
                <li>Inventory Adjustments GL Posting Account</li>
                <li>Purchase Price Variance Account</li>
                <li>Material Usage Variance Account</li>
                <li>Type of Resource</li>
                <li>Inventory Location Maintenance</li>
                <li>Inventory Adjustments</li>
                <li>Inventory Location Transfers</li>
                <li>Inventory Reports and Inquiries</li>
                <li>Inventory Status inquiries</li>
                <li>Inventory Movement inquiries</li>
                <li>Inventory Usage inquiries</li>
                <li>Inventory Valuation Report</li>
                <li>Inventory Planning Report</li>
                <li>Inventory Checks</li>
              </UL>
              <br />
            </li>
            <li>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectAccountsReceivable">
              <a HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?ViewTopic=AccountsReceivable'; ?>">Accounts Receivable</a>
<?php
  } else {
?>
              <a HREF="#AccountsReceivable">Accounts Receivable</a>
<?php
  }
?>
              <ul>
                <li>Overview</li>
                <li>Features</li>
                <li>Entering New Customers</li>
                <li>Customer Code</li>
                <li>Customer Name</li>
                <li>Address Line 1, 2, 3 and 4</li>
                <li>Currency</li>
                <li>Invoice Discount</li>
                <li>Prompt Payment Discount</li>
                <li>Customer Since</li>
                <li>Payment Terms</li>
                <li>Credit Status or Rating</li>
                <li>Credit Limit</li>
                <li>Invoice Addressing</li>
                <li>Entering Customer Branches</li>
                <li>Branch Name</li>
                <li>Branch Code</li>
                <li>Branch Contact / Phone / Fax / Address</li>
                <li>Sales Person</li>
                <li>Draw Stock From</li>
                <li>Forward Date From A Day In The Month</li>
                <li>Delivery Days</li>
                <li>Phone/Fax/Email</li>
                <li>Tax Authority</li>
                <li>Disable Transactions</li>
                <li>Default Freight Company</li>
                <li>Postal Address 1, 2, 3 and 4</li>
                <li>Amending Customer Details</li>
                <li>Shippers</li>
              </UL>
              <br />
            </li>
            <li>

<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectAccountsPayable">
              <a HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?ViewTopic=AccountsPayable'; ?>">Accounts Payable</a>
<?php
  } else {
?>
              <a HREF="#AccountsPayable">Accounts Payable</a>
<?php
  }
?>
              <ul>
                <li>Overview</li>
                <li>Features</li>
                <li>Entering New Suppliers</li>
                <li>Supplier Code</li>
                <li>Supplier Name</li>
                <li>Address Line 1, 2, 3 and 4</li>
                <li>Supplier Since</li>
                <li>Payment Terms</li>
                <li>Bank Particulars/Reference</li>
                <li>Bank Account Number</li>
                <li>Currency</li>
		<li>Remittance Advice</li>
              </UL>
              <br />
            </li>
            <li>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectSalesPeople">
              <a HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?ViewTopic=SalesPeople'; ?>">Sales People</a>
<?php
  } else {
?>
              <a HREF="#SalesPeople">Sales People</a>
<?php
  }
?>
              <ul>
                <li>Salesperson Records</li>
                <li>Salespeople Code</li>
                <li>Salesperson Name, Telephone and Fax Numbers</li>
                <li>Salesperson Commission Rates and Breakpoint</li>
              </UL>
              <br />
            </li>
            <li>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectCurrencies">
              <a HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?ViewTopic=Currencies'; ?>">Currencies</a>
<?php
  } else {
?>
              <a HREF="#Currencies">Currencies</a>
<?php
  }
?>
              <ul>
                <li>Currency Abbreviation</li>
                <li>Currency Name</li>
                <li>Country of Currency</li>
                <li>Hundredths Name</li>
                <li>Exchange Rates</li>
              </UL>
              <br />
            </li>
            <li>

<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectSalesTypes">
              <a HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?ViewTopic=SalesTypes'; ?>">Sales Types / Price Lists</a>
<?php
  } else {
?>
              <a HREF="#SalesTypes">Sales Types / Price Lists</a>
<?php
  }
?>
              <ul>
                <li>Sales Types / Price Lists</li>
                <li>Sales Type Code</li>
                <li>Sales Type Description</li>
              </UL>
              <br />
            </li>
            <li>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectPaymentTerms">
              <a HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?ViewTopic=PaymentTerms'; ?>">Payment Terms</a>
<?php
  } else {
?>
              <a HREF="#PaymentTerms">Payment Terms</a>
<?php
  }
?>
              <ul>
                <li>Payment Terms</li>
                <li>Payment Terms Code</li>
                <li>Payment Terms Description</li>
                <li>Days Before Due / Day In Following Month When Due</li>
              </UL>
              <br />
            </li>
            <li>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectCreditStatus">
              <a HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?ViewTopic=CreditStatus'; ?>">Credit Status</a>
<?php
  } else {
?>
              <a HREF="#CreditStatus">Credit Status</a>
<?php
  }
?>
              <ul>
                <li>Credit Status Ratings</li>
                <li>Status Code</li>
                <li>Status Description</li>
                <li>Disallow Invoices</li>
              </UL>
              <br />
            </li>
            <li>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectTax">
              <a HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?ViewTopic=Tax'; ?>">Tax</a>
<?php
  } else {
?>
              <a HREF="#Tax">Tax</a>
<?php
  }
?>
              <ul>
                <li>Tax Calculations</li>
                <li>Overview</li>
                <li>Setting up Taxes</li>
                <li>Sales only within one Tax Authority Example - 2 Tax Levels:</li>
                <li>Sales only within one Tax Authority Example - 3 Tax Levels:</li>
                <li>Sales Within Two Tax Authorities Example - 3 Tax Levels:</li>
              </UL>
              <br />
            </li>
            <li>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectPrices">
              <a HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?ViewTopic=Prices'; ?>">Prices and Discounts</a>
<?php
  } else {
?>
              <a HREF="#Prices">Prices and Discounts</a>
<?php
  }
?>
              <ul>
                <li>Prices and Discounts</li>
                <li>Pricing Overview</li>
                <li>Maintaining Prices</li>
                <li>Discount Matrix</li>
              </UL>
              <br />
            </li>
            <li>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectARTransactions">
              <a HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?ViewTopic=ARTransactions'; ?>">Accounts Receivable Transactions</a>
<?php
  } else {
?>
              <a HREF="#ARTransactions">Accounts Receivable Transactions</a>
<?php
  }
?>
              <ul>
                <li>Invoicing An Order</li>
                <li>Selecting an Order To Invoice</li>
                <li>Producing An Invoice From A Selected Order</li>
                <li>Credit Notes</li>
                <li>Entry Of Receipts</li>
                <li>Receipts - Customer</li>
                <li>Receipts - Date</li>
                <li>Receipts - Currency and Exchange Rate</li>
                <li>Receipts - Payment Method</li>
                <li>Receipts - Amount</li>
                <li>Receipts - Discount</li>
                <li>Receipts - Allocating to Invoices</li>
                <li>Differences on Exchange</li>
                <li>Receipts Processing</li>
                <li>Deposits Listing</li>
                <li>Allocate Credits To A Customer's Account</li>
              </UL>
              <br />
            </li>
            <li>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectARInquiries">
              <a HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?ViewTopic=ARInquiries'; ?>">Accounts Receivable Inquiries</a>
<?php
  } else {
?>
              <a HREF="#ARInquiries">Accounts Receivable Inquiries</a>
<?php
  }
?>
              <ul>
                <li>Customer Inquiries</li>
                <li>Customer Account inquiries</li>
                <li>Transaction Detail inquiries</li>
              </UL>
              <br />
            </li>
            <li>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectARReports">
              <a HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?ViewTopic=ARReports'; ?>">Accounts Receivable Reports</a>
<?php
  } else {
?>
              <a HREF="#ARReports">Accounts Receivable Reports</a>
<?php
  }
?>
              <ul>
                <li>Customers - Reporting</li>
                <li>Aged Customer Balance Listing</li>
                <li>Customer Statements</li>
                <li>Customer Transaction Listing Options</li>
                <li>Printing Invoices Or Credit Notes</li>
              </UL>
              <br />
            </li>
            <li>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectSalesAnalysis">
              <a HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?ViewTopic=SalesAnalysis'; ?>">Sales Analysis</a>
<?php
  } else {
?>
              <a HREF="#SalesAnalysis">Sales Analysis</a>
<?php
  }
?>
              <ul>
                <li>Sales Analysis</li>
                <li>Sales Analysis Report Headers</li>
                <li>Sales Analysis Report Columns</li>
                <li>Automating Sales Reports</li>
              </UL>
              <br />
            </li>
            <li>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectSalesOrders">
              <a HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?ViewTopic=SalesOrders'; ?>">Sales Orders</a>
<?php
  } else {
?>
              <a HREF="#SalesOrders">Sales Orders</a>
<?php
  }
?>
              <ul>
                <li>Sales Orders</li>
                <li>Sales Order Functionality</li>
                <li>Entry of Sales Orders</li>
                <li>Sales Orders - Selection of the Customer and Branch</li>
                <li>Selection of Order Line Items</li>
                <li>Delivery Details</li>
                <li>Modfiying An Order</li>
				<li>Quotations</li>
				<li>Recurring Orders</li>
				<li>Counter Sales - Entering Sales Directly</li>
				<li>Managing Discounts by product group and customer group (Matrix)</li>
              </UL>
              <br />
            </li>
            <li>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="PurchaseOrdering">
              <a HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?ViewTopic=PurchaseOrdering'; ?>">Purchase Ordering</a>
<?php
  } else {
?>
              <a HREF="#Shipments">Purchase Orders</a>
<?php
  }
?>
              <ul>
                <li>Overview</li>
                <li>Purchase Orders</li>
                <li>Adding a New Purchase Order</li>
                <li>Authorising Purchase Orders </li>
                <li>Recieving Purchase Orders</li>
              </UL>
              <br />
            </li>
            <li>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectShipments">
              <a HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?ViewTopic=Shipments'; ?>">Shipments</a>
<?php
  } else {
?>
              <a HREF="#Shipments">Shipments</a>
<?php
  }
?>
              <ul>
                <li>Shipments</li>
                <li>Shipment General Ledger Posting</li>
                <li>Creating Shipments</li>
                <li>Shipment Costings</li>
                <li>Closing a Shipment</li>
              </UL>
              <br />
            </li>
            <li>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectContractCosting">
              <a HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?ViewTopic=Contracts'; ?>">Contract Costing</a>
<?php
  } else {
?>
              <a HREF="#Contracts">Contract Costing</a>
<?php
  }
?>
              <ul>
                <li>Contract Costing Overview</li>
                <li>Creating A New Contract</li>
                <li>Selecting A Contract</li>
                <li>Charging Against Contracts</li>
              </UL>
              <br />
            </li>
            <li>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectManufacturing">
              <a HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?ViewTopic=Manufacturing'; ?>">Manufacturing</a>
<?php
  } else {
?>
              <a HREF="#Manufacturing">Manufacturing</a>
<?php
  }
?>
              <ul>
                <li>Manufacturing Overview</li>
                <li>General Ledger Implications</li>
                <li>Work Order Entry</li>
                <li>Work Order Receipts</li>
                <li>Work Order Issues</li>
                <li>Closing Work Orders</li>
              </UL>
              <br />
            </li>
            <li>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectMRP">
              <a HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?ViewTopic=MRP'; ?>">
              Material Requirements Planning</a>
<?php
  } else {
?>
              <a HREF="#MRP">Material Requirements Planning</a>
<?php
  }
?>
              <ul>
                <li>MRP Overview</li>
                <li>Base Data Required</li>
                <li>Production Calendar</li>
                <li>Master (Production) Schedule</li>
                <li>Running The MRP Calculation</li>
                <li>How It Works</li>
                <li>MRP Reports</li>
              </UL>
              <br />
            </li>
            <li>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectGeneralLedger">
              <a HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?ViewTopic=GeneralLedger'; ?>">General Ledger</a>
<?php
  } else {
?>
              <a HREF="#GeneralLedger">General Ledger</a>
<?php
  }
?>
              <ul>
                <li>General Ledger Overview</li>
                <li>Account Groups</li>
                <li>Bank Accounts</li>
                <li>Bank Account Payments</li>
                <li>General Ledger Integration Setup</li>
                <li>Sales Journals</li>
                <li>Stock Journals</li>
                <li>EDI</li>
                <li>EDI Setup</li>
                <li>Sending EDI Invoices</li>
              </UL>
              <br />
            </li>
            <li>
 <?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectFixedAssets">
              <a HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?ViewTopic=FixedAssets'; ?>">Fixed Assets</a>
<?php
  } else {
?>
              <a HREF="#Fixed Assets">Fixed Assets</a>
<?php
  }
?>
              <ul>
                <li>Fixed Assets Overview</li>
                <li>Creating a Fixed Asset</li>
                <li>Selecting Fixed Assets</li>
                <li>Depreciation Run</li>
                <li>Fixed Asset Schedule</li>
              </UL>
              <br />
            </li>
            <li>
<?php
if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectReportBuilder">
              <a HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?ViewTopic=ReportBuilder'; ?>">SQL Report Writer</a>
<?php
  } else {
?>
              <a HREF="#ReportBuilder">Report Writer</a>
<?php
  }
?>
              <ul>
                <li>Report Writer Introduction</li>
                <li>Reports Administration</li>
                <li>Importing and Exporting Reports</li>
                <li>Editing Copying Renaming Reports</li>
                <li>Creating A New Report - Identification</li>
                <li>Creating A New Report - Page Setup</li>
                <li>Creating A New Report - Specifying Database Tables and Links</li>
                <li>Creating A New Report - Specifying fields to Retrieve</li>
                <li>Creating A New Report - Entering and Arranging Criteria</li>
                <li>Viewing Reports</li>
              </UL>
              <br />
            </li>
            <li>
<?php
if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="PettyCash">
              <a HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?ViewTopic=PettyCash'; ?>">Petty Cash Management System</a>
<?php
  } else {
?>
              <a HREF="#PettyCash">Petty Cash Management System</a>
<?php
  }
?>
              <ul>
                <li>Overview</li>
                <li>Setup General Parameters</li>

              </UL>
              <br />
            </li>
            <li>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectMultilanguage">
              <a HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?ViewTopic=Multilanguage'; ?>">Multilanguage</a>
<?php
  } else {
?>
              <a HREF="#Multilanguage">Multilanguage</a>
<?php
  }
?>
              <ul>
                <li>Introduction to Multilanguage</li>
                <li>Rebuild the System Default Language File</li>
                <li>Add a New Language to the System</li>
                <li>Edit a Language File Header</li>
                <li>Edit a Language File Module</li>
              </UL>
              <br />
            </li>
            <li>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectSpecialUtilities">
              <a HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?ViewTopic=SpecialUtilities'; ?>">Special Utilities</a>
<?php
  } else {
?>
              <a HREF="#SpecialUtilities">Special Utilities</a>
<?php
  }
?>
              <ul>
                <li>Re-Apply Standard Costs to Sales Analysis</li>
                <li>Change A Customer Code</li>
                <li>Change An Inventory Code</li>
                <li>Make Stock Locations</li>
                <li>Repost General Ledger from Period</li>
              </UL>
              <br />
            </li>
            <li>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectNewScripts">
              <a HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?ViewTopic=NewScripts'; ?>">Development - Foundations</a>
<?php
  } else {
?>
              <a HREF="#NewScripts">Development - Foundations</a>
<?php
  }
?>
              <ul>
                <li>Directory Structure</li>
                <li>session.inc</li>
                <li>header.inc</li>
                <li>footer.inc</li>
                <li>config.php</li>
                <li>PDFStarter.php</li>
                <li>Database Abstraction - ConnectDB.inc</li>
                <li>DateFunctions.inc</li>
                <li>SQL_CommonFuctions.inc</li>
              </UL>
              <br />
            </li>
            <li>





<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectAPI">

              <a HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?ViewTopic=API'; ?>">Development - API Functions</a>
<?php
  } else {
?>
              <a HREF="#API">Development - API</a>
<?php
  }
?>
              <br />
              <br />
            </li>
            <li>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectAPITutorial">

              <a HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?ViewTopic=APITutorial'; ?>">API Tutorial</a>
<?php
  } else {
?>
              <a HREF="#APITutorial">Development - API Tutorial</a>
<?php
  }
?>
              <br />
              <br />
            </li>
            <li>

<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectStructure">
              <a HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?ViewTopic=Structure'; ?>">Development - Structure</a>
<?php
  } else {
?>
              <a HREF="#Structure">Development - Structure</a>
<?php
  }
?>
              <ul>
                <li>Sales Orders</li>
                <li>Pricing</li>
                <li>Delivery and Freight Charges</li>
                <li>Finding Sales Orders</li>
                <li>Invoicing</li>
                <li>Accounts Receivable / Debtors Accounts</li>
                <li>Accounts Receivable Receipts</li>
                <li>Accounts Receivable Allocations</li>
                <li>Sales Analysis</li>
                <li>Purchase Orders</li>
                <li>Inventory</li>
                <li>Stock Inquiries</li>
                <li>Accounts Payable</li>
                <li>Supplier Payments</li>
              </UL>
              <br />
            </li>
            <li>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectContributors">
              <a HREF="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?ViewTopic=Contributors'; ?>">Contributors - Acknowledgements</a>
<?php
  } else {
?>
              <a HREF="#Contributors">Contributors - Acknowledgements</a>
<?php
  }
?>
            </li>
          </UL>
        </td>
      </tr>
    </table>

<?php
}
?>
  </form>
<?php

if (!isset($_GET['ViewTopic'])) {
	$_GET['ViewTopic'] = '';
}

if ($_GET['ViewTopic'] == 'Introduction' OR isset($_POST['SelectIntroduction'])) {
  include('ManualIntroduction.html');
}

if ($_GET['ViewTopic'] == 'Requirements' OR isset($_POST['SelectRequirements'])) {
  include('ManualRequirements.html');
}

if ($_GET['ViewTopic'] == 'GettingStarted' OR isset($_POST['SelectGettingStarted'])) {
  include('ManualGettingStarted.html');
}

if ($_GET['ViewTopic'] == 'SecuritySchema' OR isset($_POST['SelectSecuritySchema'])) {
  include('ManualSecuritySchema.html');
}

if ($_GET['ViewTopic'] == 'CreatingNewSystem' OR isset($_POST['SelectCreatingNewSystem'])) {
  include('ManualCreatingNewSystem.html');
}

if ($_GET['ViewTopic'] == 'SystemConventions' OR isset($_POST['SelectSystemConventions'])) {
  include('ManualSystemConventions.html');
}

if ($_GET['ViewTopic'] == 'Inventory' OR isset($_POST['SelectInventory'])) {
  include('ManualInventory.html');
}

if ($_GET['ViewTopic'] == 'AccountsReceivable' OR isset($_POST['SelectAccountsReceivable'])) {
  include('ManualAccountsReceivable.html');
}

if ($_GET['ViewTopic'] == 'AccountsPayable' OR isset($_POST['SelectAccountsPayable'])) {
  include('ManualAccountsPayable.html');
}

if ($_GET['ViewTopic'] == 'SalesPeople' OR isset($_POST['SelectSalesPeople'])) {
  include('ManualSalesPeople.html');
}
if ($_GET['ViewTopic'] == 'Currencies' OR isset($_POST['Currencies'])) {
  include('ManualCurrencies.html');
}
if ($_GET['ViewTopic'] == 'SalesTypes' OR isset($_POST['SelectSalesTypes'])) {
  include('ManualSalesTypes.html');
}

if ($_GET['ViewTopic'] == 'PaymentTerms' OR isset($_POST['SelectPaymentTerms'])) {
  include('ManualPaymentTerms.html');
}

if ($_GET['ViewTopic'] == 'CreditStatus' OR isset($_POST['SelectCreditStatus'])) {
  include('ManualCreditStatus.html');
}

if ($_GET['ViewTopic'] == 'Tax' OR isset($_POST['SelectTax'])) {
  include('ManualTax.html');
}

if ($_GET['ViewTopic'] == 'Prices' OR isset($_POST['SelectPrices'])) {
  include('ManualPrices.html');
}

if ($_GET['ViewTopic'] == 'ARTransactions' OR isset($_POST['SelectARTransactions'])) {
  include('ManualARTransactions.html');
}

if ($_GET['ViewTopic'] == 'ARInquiries' OR isset($_POST['SelectARInquiries'])) {
  include('ManualARInquiries.html');
}

if ($_GET['ViewTopic'] == 'ARReports' OR isset($_POST['SelectARReports'])) {
  include('ManualARReports.html');
}

if ($_GET['ViewTopic'] == 'SalesAnalysis' OR isset($_POST['SelectSalesAnalysis'])) {
  include('ManualSalesAnalysis.html');
}

if ($_GET['ViewTopic'] == 'SalesOrders' OR isset($_POST['SelectSalesOrders'])) {
  include('ManualSalesOrders.html');
}

if ($_GET['ViewTopic'] == 'PurchaseOrdering' OR isset($_POST['PurchaseOrdering'])) {
  include('ManualPurchaseOrdering.html');
}
if ($_GET['ViewTopic'] == 'Shipments' OR isset($_POST['SelectShipments'])) {
  include('ManualShipments.html');
}
if ($_GET['ViewTopic'] == 'Contracts' OR isset($_POST['SelectContractCosting'])) {
  include('ManualContracts.html');
}
if ($_GET['ViewTopic'] == 'GeneralLedger' OR isset($_POST['SelectGeneralLedger'])) {
  include('ManualGeneralLedger.html');
}
if ($_GET['ViewTopic'] == 'FixedAssets' OR isset($_POST['SelectFixedAssets'])) {
  include('ManualFixedAssets.html');
}
if ($_GET['ViewTopic'] == 'Manufacturing' OR isset($_POST['SelectManufacturing'])) {
  include('ManualManufacturing.html');
}
if ($_GET['ViewTopic'] == 'MRP' OR isset($_POST['SelectMRP'])) {
  include('ManualMRP.html');
}
if ($_GET['ViewTopic'] == 'ReportBuilder' OR isset($_POST['SelectReportBuilder'])) {
  include('ManualReportBuilder.html');
}
if ($_GET['ViewTopic'] == 'PettyCash' OR isset($_POST['PettyCash'])) {
  include('ManualPettyCash.html');
}
if ($_GET['ViewTopic'] == 'Multilanguage' OR isset($_POST['SelectMultilanguage'])) {
  include('ManualMultilanguage.html');
}

if ($_GET['ViewTopic'] == 'SpecialUtilities' OR isset($_POST['SelectSpecialUtilities'])) {
  include('ManualSpecialUtilities.html');
}

if ($_GET['ViewTopic'] == 'NewScripts' OR isset($_POST['SelectNewScripts'])) {
  include('ManualNewScripts.html');
}

if ($_GET['ViewTopic'] == 'APITutorial' OR isset($_POST['SelectAPITutorial'])) {
  include('ManualAPITutorial.html');
}
if ($_GET['ViewTopic'] == 'API' OR isset($_POST['SelectAPI'])) {
  include('ManualAPIFunctions.php');
}

if ($_GET['ViewTopic'] == 'Structure' OR isset($_POST['SelectStructure'])) {
  include('ManualDevelopmentStructure.html');
}

if ($_GET['ViewTopic'] == 'Contributors' OR isset($_POST['SelectContributors'])) {
  include('ManualContributors.html');
}

include('ManualFooter.html');

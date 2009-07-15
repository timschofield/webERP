<?php
/* This table of contents allows the choice to display one section or select multiple sections to format for print.
     Selecting multiple sections is for printing
-->

<!-- The individual topics in the manual are in straight html files that are called along with the header and foot from here.
     No style, inline style or style sheet on purpose.
     In this way the help can be easily broken into sections for online context-sensitive help.
		 The only html used in them are:
		 <br>
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
$PageSecurity=1;
$PathPrefix='../../';
include($PathPrefix.'includes/session.inc');

include('ManualHeader.html');
?>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
<?php
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
					<br><br><br>
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
          <br><br>
          <UL>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectIntroduction">
              <A HREF="<?php echo $_SERVER['PHP_SELF'] . '?ViewTopic=Introduction'; ?>">Introduction</A>
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
              <BR>
            </LI>
						<LI>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectRequirements">
              <A HREF="<?php echo $_SERVER['PHP_SELF'] . '?ViewTopic=Requirements'; ?>">Requirements</A>
<?php
  } else {
?>
              <A href="#Requirements">Requirements</A>
<?php
	}
?>
              <UL>
                <LI>Hardware Requirements</LI>
                <LI>Software Requirements</LI>
                <LI>Using webERP with a Wiki</LI>
              </UL>
              <BR>
            </LI>
						<LI>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectGettingStarted">
              <A HREF="<?php echo $_SERVER['PHP_SELF'] . '?ViewTopic=GettingStarted'; ?>">Getting Started</A>
<?php
  } else {
?>
              <A HREF="#GettingStarted">Getting Started</A>
<?php
  }
?>
              <UL>
                <LI>Prerequisites</LI>
                <LI>Copying the PHP Scripts</LI>
                <LI>Creating the Database</LI>
                <LI>Editing config.php</LI>
                <LI>Logging In For the First Time</LI>
                <LI>Themes and GUI Modification</LI>
                <LI>Setting Up Users</LI>
              </UL>
              <BR>
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectSecuritySchema">
              <A HREF="<?php echo $_SERVER['PHP_SELF'] . '?ViewTopic=SecuritySchema'; ?>">Security Schema</A>
<?php
  } else {
?>
              <A HREF="#SecuritySchema">Security Schema</A>
<?php
  }
?>
            </LI>
            <br><br>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectCreatingNewSystem">
              <A HREF="<?php echo $_SERVER['PHP_SELF'] . '?ViewTopic=CreatingNewSystem'; ?>">Creating a New System</A>
<?php
  } else {
?>
              <A HREF="#CreatingNewSystem">Creating a New System</A>
<?php
  }
?>
              <UL>
                <LI>Running the Demonstration Database</LI>
                <LI>Setting Up A System</LI>
                <LI>Setting Up Inventory Items</LI>
                <LI>Entering Inventory Balances</LI>
                <LI>Inventory Ledger Integration to General Ledger Issues</LI>
                <LI>Setting Up Customers</LI>
                <LI>Entering Customer Balances</LI>
                <LI>Reconciling the Debtors Ledger Control Account</LI>
                <LI>Finally</LI>
              </UL>
              <BR>
						</LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectSystemConventions">
              <A HREF="<?php echo $_SERVER['PHP_SELF'] . '?ViewTopic=SystemConventions'; ?>">System Conventions</A>
<?php
  } else {
?>
              <A HREF="#SystemConventions">System Conventions</A>
<?php
  }
?>
              <UL>
                <LI>Navigating the Menu</LI>
                <LI>Reporting</LI>
              </UL>
              <BR>
            </LI>
						<LI>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectInventory">
              <A HREF="<?php echo $_SERVER['PHP_SELF'] . '?ViewTopic=Inventory'; ?>">Inventory (aka "Stock")</A>
<?php
  } else {
?>
              <A HREF="#Inventory">Inventory (aka "Stock")</A>
<?php
  }
?>
              <UL>
                <LI>Overview</LI>
                <LI>Inventory System Features</LI>
                <LI>Inventory Categories</LI>
                <LI>Adding Inventory Items</LI>
                <LI>Item Code</LI>
                <LI>Part Descriptions</LI>
                <LI>Categories</LI>
                <LI>Unit of Measurement</LI>
                <LI>Economic Order Quantity</LI>
                <LI>Packaged Volume</LI>
                <LI>Packaged Weight</LI>
                <LI>Units of Measure</LI>
                <LI>Current or Obsolete</LI>
                <LI>Make Or Buy</LI>
                <LI>Setting Up Assembly Items</LI>
                <LI>Controlled</LI>
                <LI>Serialised</LI>
                <LI>Bar Code</LI>
                <LI>Discount Category</LI>
                <LI>Decimal Places</LI>
                <LI>Inventory Costing</LI>
                <LI>Material Cost</LI>
                <LI>Labour Cost</LI>
                <LI>Overhead Cost</LI>
                <LI>Standard Costing Considerations</LI>
                <LI>Actual Cost</LI>
                <LI>Alterations To Labour Cost, Material Cost or Overhead Cost</LI>
                <LI>Selecting Inventory Items</LI>
                <LI>Amending Inventory Items</LI>
                <LI>Changing a Category</LI>
                <LI>Alterations to the Make or Buy Flag</LI>
                <LI>Inventory Categories</LI>
                <LI>Inventory Category Code</LI>
                <LI>Inventory Category Description</LI>
                <LI>Balance Sheet Inventory GL Account</LI>
                <LI>Inventory Adjustments GL Posting Account</LI>
                <LI>Purchase Price Variance Account</LI>
                <LI>Material Usage Variance Account</LI>
                <LI>Type of Resource</LI>
                <LI>Inventory Location Maintenance</LI>
                <LI>Inventory Adjustments</LI>
                <LI>Inventory Location Transfers</LI>
                <LI>Inventory Reports and Inquiries</LI>
                <LI>Inventory Status inquiries</LI>
                <LI>Inventory Movement inquiries</LI>
                <LI>Inventory Usage inquiries</LI>
                <LI>Inventory Valuation Report</LI>
                <LI>Inventory Planning Report</LI>
                <LI>Inventory Checks</LI>
              </UL>
              <BR>
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectAccountsReceivable">
              <A HREF="<?php echo $_SERVER['PHP_SELF'] . '?ViewTopic=AccountsReceivable'; ?>">Accounts Receivable</A>
<?php
  } else {
?>
              <A HREF="#AccountsReceivable">Accounts Receivable</A>
<?php
  }
?>
              <UL>
                <LI>Overview</LI>
                <LI>Features</LI>
                <LI>Entering New Customers</LI>
                <LI>Customer Code</LI>
                <LI>Customer Name</LI>
                <LI>Address Line 1, 2, 3 and 4</LI>
                <LI>Currency</LI>
                <LI>Invoice Discount</LI>
                <LI>Prompt Payment Discount</LI>
                <LI>Customer Since</LI>
                <LI>Payment Terms</LI>
                <LI>Credit Status or Rating</LI>
                <LI>Credit Limit</LI>
                <LI>Invoice Addressing</LI>
                <LI>Entering Customer Branches</LI>
                <LI>Branch Name</LI>
                <LI>Branch Code</LI>
                <LI>Branch Contact / Phone / Fax / Address</LI>
                <LI>Sales Person</LI>
                <LI>Draw Stock From</LI>
                <LI>Forward Date From A Day In The Month</LI>
                <LI>Delivery Days</LI>
                <LI>Phone/Fax/Email</LI>
                <LI>Tax Authority</LI>
                <LI>Disable Transactions</LI>
                <LI>Default Freight Company</LI>
                <LI>Postal Address 1, 2, 3 and 4</LI>
                <LI>Amending Customer Details</LI>
                <LI>Shippers</LI>
              </UL>
              <BR>
            </LI>
            <LI>

<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectAccountsPayable">
              <A HREF="<?php echo $_SERVER['PHP_SELF'] . '?ViewTopic=AccountsPayable'; ?>">Accounts Payable</A>
<?php
  } else {
?>
              <A HREF="#AccountsPayable">Accounts Payable</A>
<?php
  }
?>
              <UL>
                <LI>Overview</LI>
                <LI>Features</LI>
                <LI>Entering New Suppliers</LI>
                <LI>Supplier Code</LI>
                <LI>Supplier Name</LI>
                <LI>Address Line 1, 2, 3 and 4</LI>
                <LI>Supplier Since</LI>
                <LI>Payment Terms</LI>
                <LI>Bank Particulars/Reference</LI>
                <LI>Bank Account Number</LI>
                <LI>Currency</LI>
		<LI>Remittance Advice</LI>
              </UL>
              <BR>
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectSalesPeople">
              <A HREF="<?php echo $_SERVER['PHP_SELF'] . '?ViewTopic=SalesPeople'; ?>">Sales People</A>
<?php
  } else {
?>
              <A HREF="#SalesPeople">Sales People</A>
<?php
  }
?>
              <UL>
                <LI>Salesperson Records</LI>
                <LI>Salespeople Code</LI>
                <LI>Salesperson Name, Telephone and Fax Numbers</LI>
                <LI>Salesperson Commission Rates and Breakpoint</LI>
              </UL>
              <BR>
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectCurrencies">
              <A HREF="<?php echo $_SERVER['PHP_SELF'] . '?ViewTopic=Currencies'; ?>">Currencies</A>
<?php
  } else {
?>
              <A HREF="#Currencies">Currencies</A>
<?php
  }
?>
              <UL>
                <LI>Currency Abbreviation</LI>
                <LI>Currency Name</LI>
                <LI>Country of Currency</LI>
                <LI>Hundredths Name</LI>
                <LI>Exchange Rates</LI>
              </UL>
              <BR>
            </LI>
            <LI>

<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectSalesTypes">
              <A HREF="<?php echo $_SERVER['PHP_SELF'] . '?ViewTopic=SalesTypes'; ?>">Sales Types / Price Lists</A>
<?php
  } else {
?>
              <A HREF="#SalesTypes">Sales Types / Price Lists</A>
<?php
  }
?>
              <UL>
                <LI>Sales Types / Price Lists</LI>
                <LI>Sales Type Code</LI>
                <LI>Sales Type Description</LI>
              </UL>
              <BR>
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectPaymentTerms">
              <A HREF="<?php echo $_SERVER['PHP_SELF'] . '?ViewTopic=PaymentTerms'; ?>">Payment Terms</A>
<?php
  } else {
?>
              <A HREF="#PaymentTerms">Payment Terms</A>
<?php
  }
?>
              <UL>
                <LI>Payment Terms</LI>
                <LI>Payment Terms Code</LI>
                <LI>Payment Terms Description</LI>
                <LI>Days Before Due / Day In Following Month When Due</LI>
              </UL>
              <BR>
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectCreditStatus">
              <A HREF="<?php echo $_SERVER['PHP_SELF'] . '?ViewTopic=CreditStatus'; ?>">Credit Status</A>
<?php
  } else {
?>
              <A HREF="#CreditStatus">Credit Status</A>
<?php
  }
?>
              <UL>
                <LI>Credit Status Ratings</LI>
                <LI>Status Code</LI>
                <LI>Status Description</LI>
                <LI>Disallow Invoices</LI>
              </UL>
              <BR>
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectTax">
              <A HREF="<?php echo $_SERVER['PHP_SELF'] . '?ViewTopic=Tax'; ?>">Tax</A>
<?php
  } else {
?>
              <A HREF="#Tax">Tax</A>
<?php
  }
?>
              <UL>
                <LI>Tax Calculations</LI>
                <LI>Overview</LI>
                <LI>Setting up Taxes</LI>
                <LI>Sales only within one Tax Authority Example - 2 Tax Levels:</LI>
                <LI>Sales only within one Tax Authority Example - 3 Tax Levels:</LI>
                <LI>Sales Within Two Tax Authorities Example - 3 Tax Levels:</LI>
              </UL>
              <BR>
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectPrices">
              <A HREF="<?php echo $_SERVER['PHP_SELF'] . '?ViewTopic=Prices'; ?>">Prices and Discounts</A>
<?php
  } else {
?>
              <A HREF="#Prices">Prices and Discounts</A>
<?php
  }
?>
              <UL>
                <LI>Prices and Discounts</LI>
                <LI>Pricing Overview</LI>
                <LI>Maintaining Prices</LI>
                <LI>Discount Matrix</LI>
              </UL>
              <BR>
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectARTransactions">
              <A HREF="<?php echo $_SERVER['PHP_SELF'] . '?ViewTopic=ARTransactions'; ?>">Accounts Receivable Transactions</A>
<?php
  } else {
?>
              <A HREF="#ARTransactions">Accounts Receivable Transactions</A>
<?php
  }
?>
              <UL>
                <LI>Invoicing An Order</LI>
                <LI>Selecting an Order To Invoice</LI>
                <LI>Producing An Invoice From A Selected Order</LI>
                <LI>Credit Notes</LI>
                <LI>Entry Of Receipts</LI>
                <LI>Receipts - Customer</LI>
                <LI>Receipts - Date</LI>
                <LI>Receipts - Currency and Exchange Rate</LI>
                <LI>Receipts - Payment Method</LI>
                <LI>Receipts - Amount</LI>
                <LI>Receipts - Discount</LI>
                <LI>Receipts - Allocating to Invoices</LI>
                <LI>Differences on Exchange</LI>
                <LI>Receipts Processing</LI>
                <LI>Deposits Listing</LI>
                <LI>Allocate Credits To A Customer's Account</LI>
              </UL>
              <BR>
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectARInquiries">
              <A HREF="<?php echo $_SERVER['PHP_SELF'] . '?ViewTopic=ARInquiries'; ?>">Accounts Receivable Inquiries</A>
<?php
  } else {
?>
              <A HREF="#ARInquiries">Accounts Receivable Inquiries</A>
<?php
  }
?>
              <UL>
                <LI>Customer Inquiries</LI>
                <LI>Customer Account inquiries</LI>
                <LI>Transaction Detail inquiries</LI>
              </UL>
              <BR>
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectARReports">
              <A HREF="<?php echo $_SERVER['PHP_SELF'] . '?ViewTopic=ARReports'; ?>">Accounts Receivable Reports</A>
<?php
  } else {
?>
              <A HREF="#ARReports">Accounts Receivable Reports</A>
<?php
  }
?>
              <UL>
                <LI>Customers - Reporting</LI>
                <LI>Aged Customer Balance Listing</LI>
                <LI>Customer Statements</LI>
                <LI>Customer Transaction Listing Options</LI>
                <LI>Printing Invoices Or Credit Notes</LI>
              </UL>
              <BR>
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectSalesAnalysis">
              <A HREF="<?php echo $_SERVER['PHP_SELF'] . '?ViewTopic=SalesAnalysis'; ?>">Sales Analysis</A>
<?php
  } else {
?>
              <A HREF="#SalesAnalysis">Sales Analysis</A>
<?php
  }
?>
              <UL>
                <LI>Sales Analysis</LI>
                <LI>Sales Analysis Report Headers</LI>
                <LI>Sales Analysis Report Columns</LI>
                <LI>Automating Sales Reports</LI>
              </UL>
              <BR>
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectSalesOrders">
              <A HREF="<?php echo $_SERVER['PHP_SELF'] . '?ViewTopic=SalesOrders'; ?>">Sales Orders</A>
<?php
  } else {
?>
              <A HREF="#SalesOrders">Sales Orders</A>
<?php
  }
?>
              <UL>
                <LI>Sales Orders</LI>
                <LI>Sales Order Functionality</LI>
                <LI>Entry of Sales Orders</LI>
                <LI>Sales Orders - Selection of the Customer and Branch</LI>
                <LI>Selection of Order Line Items</LI>
                <LI>Delivery Details</LI>
                <LI>Modfiying An Order</LI>
		<LI>Quotations</LI>
		<LI>Recurring Orders</LI>
              </UL>
              <BR>
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectShipments">
              <A HREF="<?php echo $_SERVER['PHP_SELF'] . '?ViewTopic=Shipments'; ?>">Shipments</A>
<?php
  } else {
?>
              <A HREF="#Shipments">Shipments</A>
<?php
  }
?>
              <UL>
                <LI>Shipments</LI>
                <LI>Shipment General Ledger Posting</LI>
                <LI>Creating Shipments</LI>
                <LI>Shipment Costings</LI>
                <LI>Closing a Shipment</LI>
              </UL>
              <BR>
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectManufacturing">
              <A HREF="<?php echo $_SERVER['PHP_SELF'] . '?ViewTopic=Manufacturing'; ?>">Manufacturing</A>
<?php
  } else {
?>
              <A HREF="#Manufacturing">Manufacturing</A>
<?php
  }
?>
              <UL>
                <LI>Manufacturing Overview</LI>
                <LI>General Ledger Implications</LI>
                <LI>Work Order Entry</LI>
                <LI>Work Order Receipts</LI>
                <LI>Work Order Issues</LI>
                <LI>Closing Work Orders</LI>
              </UL>
              <BR>
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectMRP">
              <A HREF="<?php echo $_SERVER['PHP_SELF'] . '?ViewTopic=MRP'; ?>">
              Maaterial Requirements Planning</A>
<?php
  } else {
?>
              <A HREF="#MRP">Material Requirements Planning</A>
<?php
  }
?>
              <UL>
                <LI>MRP Overview</LI>
                <LI>Base Data Required</LI>
                <LI>Production Calendar</LI>
                <LI>Master (Production) Schedule</LI>
                <LI>Running The MRP Calculation</LI>
                <LI>How It Works</LI>
                <LI>MRP Reports</LI>
              </UL>
              <BR>
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectGeneralLedger">
              <A HREF="<?php echo $_SERVER['PHP_SELF'] . '?ViewTopic=GeneralLedger'; ?>">General Ledger</A>
<?php
  } else {
?>
              <A HREF="#GeneralLedger">General Ledger</A>
<?php
  }
?>
              <UL>
                <LI>General Ledger Overview</LI>
                <LI>Account Groups</LI>
                <LI>Bank Accounts</LI>
                <LI>Bank Account Payments</LI>
                <LI>General Ledger Integration Setup</LI>
                <LI>Sales Journals</LI>
                <LI>Stock Journals</LI>
                <LI>EDI</LI>
                <LI>EDI Setup</LI>
                <LI>Sending EDI Invoices</LI>
              </UL>
              <BR>
            </LI>
            <LI>
<?php
if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectReportBuilder">
              <A HREF="<?php echo $_SERVER['PHP_SELF'] . '?ViewTopic=ReportBuilder'; ?>">SQL Report Writer</A>
<?php
  } else {
?>
              <A HREF="#ReportBuilder">Report Writer</A>
<?php
  }
?>
              <UL>
                <LI>Report Writer Introduction</LI>
                <LI>Reports Administration</LI>
                <LI>Importing and Exporting Reports</LI>
                <LI>Editing Copying Renaming Reports</LI>
                <LI>Creating A New Report - Identification</LI>
                <LI>Creating A New Report - Page Setup</LI>
                <LI>Creating A New Report - Specifying Database Tables and Links</LI>
                <LI>Creating A New Report - Specifying fields to Retrieve</LI>
                <LI>Creating A New Report - Entering and Arranging Criteria</LI>
                <LI>Viewing Reports</LI>
              </UL>
              <BR>
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectMultilanguage">
              <A HREF="<?php echo $_SERVER['PHP_SELF'] . '?ViewTopic=Multilanguage'; ?>">Multilanguage</A>
<?php
  } else {
?>
              <A HREF="#Multilanguage">Multilanguage</A>
<?php
  }
?>
              <UL>
                <LI>Introduction to Multilanguage</LI>
                <LI>Rebuild the System Default Language File</LI>
                <LI>Add a New Language to the System</LI>
                <LI>Edit a Language File Header</LI>
                <LI>Edit a Language File Module</LI>
              </UL>
              <BR>
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectSpecialUtilities">
              <A HREF="<?php echo $_SERVER['PHP_SELF'] . '?ViewTopic=SpecialUtilities'; ?>">Special Utilities</A>
<?php
  } else {
?>
              <A HREF="#SpecialUtilities">Special Utilities</A>
<?php
  }
?>
              <UL>
                <LI>Re-Apply Standard Costs to Sales Analysis</LI>
                <LI>Change A Customer Code</LI>
                <LI>Change An Inventory Code</LI>
                <LI>Make Stock Locations</LI>
                <LI>Repost General Ledger from Period</LI>
              </UL>
              <BR>
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectNewScripts">
              <A HREF="<?php echo $_SERVER['PHP_SELF'] . '?ViewTopic=NewScripts'; ?>">Development - Foundations</A>
<?php
  } else {
?>
              <A HREF="#NewScripts">Development - Foundations</A>
<?php
  }
?>
              <UL>
                <LI>Directory Structure</LI>
                <LI>session.inc</LI>
                <LI>header.inc</LI>
                <LI>footer.inc</LI>
                <LI>config.php</LI>
                <LI>PDFStarter.php</LI>
                <LI>Database Abstraction - ConnectDB.inc</LI>
                <LI>DateFunctions.inc</LI>
                <LI>SQL_CommonFuctions.inc</LI>
              </UL>
              <BR>
            </LI>
            <LI>





<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectNewScripts">
              <A HREF="<?php echo $_SERVER['PHP_SELF'] . '?ViewTopic=API'; ?>">Development - API</A>
<?php
  } else {
?>
              <A HREF="#API">Development - API</A>
<?php
  }
?>
              <BR>
              <BR>
            </LI>
            <LI>






<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectStructure">
              <A HREF="<?php echo $_SERVER['PHP_SELF'] . '?ViewTopic=Structure'; ?>">Development - Structure</A>
<?php
  } else {
?>
              <A HREF="#Structure">Development - Structure</A>
<?php
  }
?>
              <UL>
                <LI>Sales Orders</LI>
                <LI>Pricing</LI>
                <LI>Delivery and Freight Charges</LI>
                <LI>Finding Sales Orders</LI>
                <LI>Invoicing</LI>
                <LI>Accounts Receivable / Debtors Accounts</LI>
                <LI>Accounts Receivable Receipts</LI>
                <LI>Accounts Receivable Allocations</LI>
                <LI>Sales Analysis</LI>
                <LI>Purchase Orders</LI>
                <LI>Inventory</LI>
                <LI>Stock Inquiries</LI>
                <LI>Accounts Payable</LI>
                <LI>Supplier Payments</LI>
              </UL>
              <BR>
            </LI>
            <LI>
<?php
  if (!isset($_POST['Submit'])) {
?>
              <input type="checkbox" name="SelectContributors">
              <A HREF="<?php echo $_SERVER['PHP_SELF'] . '?ViewTopic=Contributors'; ?>">Contributors - Acknowledgements</A>
<?php
  } else {
?>
              <A HREF="#Contributors">Contributors - Acknowledgements</A>
<?php
  }
?>
            </LI>
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

if ($_GET['ViewTopic'] == 'Shipments' OR isset($_POST['SelectShipments'])) {
  include('ManualShipments.html');
}

if ($_GET['ViewTopic'] == 'GeneralLedger' OR isset($_POST['SelectGeneralLedger'])) {
  include('ManualGeneralLedger.html');
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

if ($_GET['ViewTopic'] == 'Multilanguage' OR isset($_POST['SelectMultilanguage'])) {
  include('ManualMultilanguage.html');
}

if ($_GET['ViewTopic'] == 'SpecialUtilities' OR isset($_POST['SelectSpecialUtilities'])) {
  include('ManualSpecialUtilities.html');
}

if ($_GET['ViewTopic'] == 'NewScripts' OR isset($_POST['SelectNewScripts'])) {
  include('ManualNewScripts.html');
}

if ($_GET['ViewTopic'] == 'API' OR isset($_POST['API'])) {
  include('ManualAPIFunctions.php');
}

if ($_GET['ViewTopic'] == 'Structure' OR isset($_POST['SelectStructure'])) {
  include('ManualDevelopmentStructure.html');
}

if ($_GET['ViewTopic'] == 'Contributors' OR isset($_POST['SelectContributors'])) {
  include('ManualContributors.html');
}

include('ManualFooter.html');

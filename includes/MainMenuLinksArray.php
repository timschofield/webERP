<?php

/* $Id: MainMenuLinksArray.php 6190 2013-08-12 02:12:02Z rchacon $*/

/* webERP menus with Captions and URLs. */

$ModuleLink = array('orders', 'AR', 'PO', 'AP', 'stock', 'manuf',  'GL', 'FA', 'PC', 'system', 'Utilities');
$ReportList = array('orders'=>'ord',
					'AR'=>'ar',
					'PO'=>'prch',
					'AP'=>'ap',
					'stock'=>'inv',
					'manuf'=>'man',
					'GL'=>'gl',
					'FA'=>'fa',
					'PC'=>'pc',
					'system'=>'sys',
					'Utilities'=>'utils'
					);

/*The headings showing on the tabs accross the main index used also in WWW_Users for defining what should be visible to the user */
$ModuleList = array(_('Sales'),
					_('Receivables'),
					_('Purchases'),
					_('Payables'),
					_('Inventory'),
					_('Manufacturing'),
					_('General Ledger'),
					_('Asset Manager'),
					_('Petty Cash'),
					_('Setup'),
					_('Utilities'));

$MenuItems['orders']['Transactions']['Caption'] = array(_('New Sales Order or Quotation'),
														_('Enter Counter Sales'),
														_('Enter Counter Returns'),
														_('Print Picking Lists'),
														_('Outstanding Sales Orders/Quotations'),
														_('Special Order'),
														_('Recurring Order Template'),
														_('Process Recurring Orders'));

$MenuItems['orders']['Transactions']['URL'] = array(CheckMods('/SelectOrderItems.php') . '?NewOrder=Yes',
													CheckMods('/CounterSales.php'),
													CheckMods('/CounterReturns.php'),
													CheckMods('/PDFPickingList.php'),
													CheckMods('/SelectSalesOrder.php'),
													CheckMods('/SpecialOrder.php'),
													CheckMods('/SelectRecurringSalesOrder.php'),
													CheckMods('/RecurringSalesOrdersProcess.php'));

$MenuItems['orders']['Reports']['Caption'] = array( _('Sales Order Inquiry'),
													_('Print Price Lists'),
													_('Order Status Report'),
													_('Orders Invoiced Reports'),
													_('Daily Sales Inquiry'),
													_('Sales By Sales Type Inquiry'),
													_('Sales By Category Inquiry'),
													_('Top Sellers Inquiry'),
													_('Order Delivery Differences Report'),
													_('Delivery In Full On Time (DIFOT) Report'),
													_('Sales Order Detail Or Summary Inquiries'),
													_('Top Sales Items Inquiry'),
													_('Top Customers Inquiry'),
													_('Worst Sales Items Report'),
													_('Sales With Low Gross Profit Report'),
													_('Sell Through Support Claims Report'));

$MenuItems['orders']['Reports']['URL'] = array( CheckMods('/SelectCompletedOrder.php'),
												CheckMods('/PDFPriceList.php'),
												CheckMods('/PDFOrderStatus.php'),
												CheckMods('/PDFOrdersInvoiced.php'),
												CheckMods('/DailySalesInquiry.php'),
												CheckMods('/SalesByTypePeriodInquiry.php'),
												CheckMods('/SalesCategoryPeriodInquiry.php'),
												CheckMods('/SalesTopItemsInquiry.php'),
												CheckMods('/PDFDeliveryDifferences.php'),
												CheckMods('/PDFDIFOT.php'),
												CheckMods('/SalesInquiry.php'),
												CheckMods('/TopItems.php'),
												CheckMods('/SalesTopCustomersInquiry.php'),
												CheckMods('/NoSalesItems.php'),
												CheckMods('/PDFLowGP.php'),
												CheckMods('/PDFSellThroughSupportClaim.php') );

$MenuItems['orders']['Maintenance']['Caption'] = array( _('Create Contract'),
														_('Select Contract'),
														_('Sell Through Support Deals'));

$MenuItems['orders']['Maintenance']['URL'] = array( CheckMods('/Contracts.php'),
													CheckMods('/SelectContract.php'),
													CheckMods('/SellThroughSupport.php'));

$MenuItems['AR']['Transactions']['Caption'] = array(_('Select Order to Invoice'),
													_('Create A Credit Note'),
													_('Enter Receipts'),
													_('Allocate Receipts or Credit Notes'));
$MenuItems['AR']['Transactions']['URL'] = array(CheckMods('/SelectSalesOrder.php'),
												CheckMods('/SelectCreditItems.php') . '?NewCredit=Yes',
												CheckMods('/CustomerReceipt.php') . '?NewReceipt=Yes&amp;Type=Customer',
												CheckMods('/CustomerAllocations.php') );

$MenuItems['AR']['Reports']['Caption'] = array(	_('Where Allocated Inquiry'),
												_('Print Invoices or Credit Notes'),
												_('Print Statements'),
												_('Sales Analysis Reports'),
												_('Aged Customer Balances/Overdues Report'),
												_('Re-Print A Deposit Listing'),
												_('Debtor Balances At A Prior Month End'),
												_('Customer Listing By Area/Salesperson'),
												_('Sales Graphs'),
												_('List Daily Transactions'),
												_('Customer Transaction Inquiries')	);

if ($_SESSION['InvoicePortraitFormat']==0){
	$PrintInvoicesOrCreditNotesScript = CheckMods('/PrintCustTrans.php');
} else {
	$PrintInvoicesOrCreditNotesScript = CheckMods('/PrintCustTransPortrait.php');
}

$MenuItems['AR']['Reports']['URL'] = array(	CheckMods('/CustWhereAlloc.php'),
											$PrintInvoicesOrCreditNotesScript,
											CheckMods('/PrintCustStatements.php'),
											CheckMods('/SalesAnalRepts.php'),
											CheckMods('/AgedDebtors.php'),
											CheckMods('/PDFBankingSummary.php'),
											CheckMods('/DebtorsAtPeriodEnd.php'),
											CheckMods('/PDFCustomerList.php'),
											CheckMods('/SalesGraph.php'),
											CheckMods('/PDFCustTransListing.php'),
											CheckMods('/CustomerTransInquiry.php') );

$MenuItems['AR']['Maintenance']['Caption'] = array(	_('Add Customer'),
													_('Select Customer'));
$MenuItems['AR']['Maintenance']['URL'] = array(	CheckMods('/Customers.php'),
												CheckMods('/SelectCustomer.php') );

$MenuItems['AP']['Transactions']['Caption'] = array(_('Select Supplier'),
													_('Supplier Allocations'));
$MenuItems['AP']['Transactions']['URL'] = array(CheckMods('/SelectSupplier.php'),
												CheckMods('/SupplierAllocations.php') );

$MenuItems['AP']['Reports']['Caption'] = array(	_('Aged Supplier Report'),
												_('Payment Run Report'),
												_('Remittance Advices'),
												_('Outstanding GRNs Report'),
												_('Supplier Balances At A Prior Month End'),
												_('List Daily Transactions'),
												_('Supplier Transaction Inquiries'));

$MenuItems['AP']['Reports']['URL'] = array(	CheckMods('/AgedSuppliers.php'),
											CheckMods('/SuppPaymentRun.php'),
											CheckMods('/PDFRemittanceAdvice.php'),
											CheckMods('/OutstandingGRNs.php'),
											CheckMods('/SupplierBalsAtPeriodEnd.php'),
											CheckMods('/PDFSuppTransListing.php'),
											CheckMods('/SupplierTransInquiry.php') );

$MenuItems['AP']['Maintenance']['Caption'] = array(	_('Add Supplier'),
													_('Select Supplier'),
													_('Maintain Factor Companies'));
$MenuItems['AP']['Maintenance']['URL'] = array(	CheckMods('/Suppliers.php'),
												CheckMods('/SelectSupplier.php'),
												CheckMods('/Factors.php') );

$MenuItems['PO']['Transactions']['Caption'] = array(_('New Purchase Order'),
													_('Purchase Orders'),
													_('Purchase Order Grid Entry'),
													_('Create a New Tender'),
													_('Edit Existing Tenders'),
													_('Process Tenders and Offers'),
													_('Orders to Authorise'),
													_('Shipment Entry'),
													_('Select A Shipment'));
$MenuItems['PO']['Transactions']['URL'] = array(CheckMods('/PO_Header.php') . '?NewOrder=Yes',
												CheckMods('/PO_SelectOSPurchOrder.php'),
												CheckMods('/PurchaseByPrefSupplier.php'),
												CheckMods('/SupplierTenderCreate.php') . '?New=Yes',
												CheckMods('/SupplierTenderCreate.php') . '?Edit=Yes',
												CheckMods('/OffersReceived.php'),
												CheckMods('/PO_AuthoriseMyOrders.php'),
												CheckMods('/SelectSupplier.php'),
												CheckMods('/Shipt_Select.php') );

$MenuItems['PO']['Reports']['Caption'] = array(	_('Purchase Order Inquiry'),
												_('Purchase Order Detail Or Summary Inquiries'),
												_('Supplier Price List'));

$MenuItems['PO']['Reports']['URL'] = array(	CheckMods('/PO_SelectPurchOrder.php'),
											CheckMods('/POReport.php'),
											CheckMods('/SuppPriceList.php') );

$MenuItems['PO']['Maintenance']['Caption'] = array(_('Maintain Supplier Price Lists'));

$MenuItems['PO']['Maintenance']['URL'] = array(CheckMods('/SupplierPriceList.php'));

$MenuItems['stock']['Transactions']['Caption'] = array(	_('Receive Purchase Orders'),
														_('Inventory Location Transfers'),	//"Inventory Transfer - Item Dispatch"
														_('Bulk Inventory Transfer') . ' - ' . _('Dispatch'),	//"Inventory Transfer - Bulk Dispatch"
														_('Bulk Inventory Transfer') . ' - ' . _('Receive'),	//"Inventory Transfer - Receive"
														_('Inventory Adjustments'),
														_('Reverse Goods Received'),
														_('Enter Stock Counts'),
														_('Create a New Internal Stock Request'),
														_('Authorise Internal Stock Requests'),
														_('Fulfill Internal Stock Requests'));

$MenuItems['stock']['Transactions']['URL'] = array(	CheckMods('/PO_SelectOSPurchOrder.php'),
													CheckMods('/StockTransfers.php') . '?New=Yes',
													CheckMods('/StockLocTransfer.php'),
													CheckMods('/StockLocTransferReceive.php'),
													CheckMods('/StockAdjustments.php') . '?NewAdjustment=Yes',
													CheckMods('/ReverseGRN.php'),
													CheckMods('/StockCounts.php'),
													CheckMods('/InternalStockRequest.php') . '?New=Yes',
													CheckMods('/InternalStockRequestAuthorisation.php'),
													CheckMods('/InternalStockRequestFulfill.php') );

$MenuItems['stock']['Reports']['Caption'] = array(	_('Serial Item Research Tool'),
													_('Print Price Labels'),
													_('Reprint GRN'),
													_('Inventory Item Movements'),
													_('Inventory Item Status'),
													_('Inventory Item Usage'),
													_('Inventory Quantities'),
													_('Reorder Level'),
													_('Stock Dispatch'),
													_('Inventory Valuation Report'),
													_('Mail Inventory Valuation Report'),
													_('Inventory Planning Report'),
													_('Inventory Planning Based On Preferred Supplier Data'),
													_('Inventory Stock Check Sheets'),
													_('Make Inventory Quantities CSV'),
													_('Compare Counts Vs Stock Check Data'),
													_('All Inventory Movements By Location/Date'),
													_('List Inventory Status By Location/Category'),
													_('Historical Stock Quantity By Location/Category'),
													_('List Negative Stocks'),
													_('Period Stock Transaction Listing'),
													_('Stock Transfer Note'),
													_('Aged Controlled Stock Report'));

$MenuItems['stock']['Reports']['URL'] = array(	CheckMods('/StockSerialItemResearch.php'),
												CheckMods('/PDFPrintLabel.php'),
												CheckMods('/ReprintGRN.php'),
												CheckMods('/StockMovements.php'),
												CheckMods('/StockStatus.php'),
												CheckMods('/StockUsage.php'),
												CheckMods('/InventoryQuantities.php'),
												CheckMods('/ReorderLevel.php'),
												CheckMods('/StockDispatch.php'),
												CheckMods('/InventoryValuation.php'),
												CheckMods('/MailInventoryValuation.php'),
												CheckMods('/InventoryPlanning.php'),
												CheckMods('/InventoryPlanningPrefSupplier.php'),
												CheckMods('/StockCheck.php'),
												CheckMods('/StockQties_csv.php'),
												CheckMods('/PDFStockCheckComparison.php'),
												CheckMods('/StockLocMovements.php'),
												CheckMods('/StockLocStatus.php'),
												CheckMods('/StockQuantityByDate.php'),
												CheckMods('/PDFStockNegatives.php'),
												CheckMods('/PDFPeriodStockTransListing.php'),
												CheckMods('/PDFStockTransfer.php'),
												CheckMods('/AgedControlledInventory.php') );

$MenuItems['stock']['Maintenance']['Caption'] = array(	_('Add A New Item'),
														_('Select An Item'),
														_('Sales Category Maintenance'),
														_('Brands Maintenance'),
														_('Add or Update Prices Based On Costs'),
														_('View or Update Prices Based On Costs'),
														_('Reorder Level By Category/Location'));

$MenuItems['stock']['Maintenance']['URL'] = array(	CheckMods('/Stocks.php'),
													CheckMods('/SelectProduct.php'),
													CheckMods('/SalesCategories.php'),
													CheckMods('/Manufacturers.php'),
													CheckMods('/PricesBasedOnMarkUp.php'),
													CheckMods('/PricesByCost.php'),
													CheckMods('/ReorderLevelLocation.php') );

$MenuItems['manuf']['Transactions']['Caption'] = array(	_('Work Order Entry'),
														_('Select A Work Order'));

$MenuItems['manuf']['Transactions']['URL'] = array(	CheckMods('/WorkOrderEntry.php'),
													CheckMods('/SelectWorkOrder.php') );

$MenuItems['manuf']['Reports']['Caption'] = array(	_('Select A Work Order'),
													_('Costed Bill Of Material Inquiry'),
													_('Where Used Inquiry'),
													_('Bill Of Material Listing'),
													_('Indented Bill Of Material Listing'),
													_('List Components Required'),
													_('List Materials Not Used Anywhere'),
													_('Indented Where Used Listing'),
													_('WO Items ready to produce'),
													_('MRP'),
													_('MRP Shortages'),
													_('MRP Suggested Purchase Orders'),
													_('MRP Suggested Work Orders'),
													_('MRP Reschedules Required'));

$MenuItems['manuf']['Reports']['URL'] = array(	CheckMods('/SelectWorkOrder.php'),
												CheckMods('/BOMInquiry.php'),
												CheckMods('/WhereUsedInquiry.php'),
												CheckMods('/BOMListing.php'),
												CheckMods('/BOMIndented.php'),
												CheckMods('/BOMExtendedQty.php'),
												CheckMods('/MaterialsNotUsed.php'),
												CheckMods('/BOMIndentedReverse.php'),
												CheckMods('/WOCanBeProducedNow.php'),
												CheckMods('/MRPReport.php'),
												CheckMods('/MRPShortages.php'),
												CheckMods('/MRPPlannedPurchaseOrders.php'),
												CheckMods('/MRPPlannedWorkOrders.php'),
												CheckMods('/MRPReschedules.php') );

$MenuItems['manuf']['Maintenance']['Caption'] = array(	_('Work Centre'),
														_('Bills Of Material'),
														_('Copy a Bill Of Materials Between Items'),
														_('Master Schedule'),
														_('Auto Create Master Schedule'),
														_('MRP Calculation'));

$MenuItems['manuf']['Maintenance']['URL'] = array(	CheckMods('/WorkCentres.php'),
													CheckMods('/BOMs.php'),
													CheckMods('/CopyBOM.php'),
													CheckMods('/MRPDemands.php'),
													CheckMods('/MRPCreateDemands.php'),
													CheckMods('/MRP.php') );

$MenuItems['GL']['Transactions']['Caption'] = array(	_('Bank Account Payments Entry'),
														_('Bank Account Receipts Entry'),
														_('Import Bank Transactions'),
														_('Bank Account Payments Matching'),
														_('Bank Account Receipts Matching'),
														_('Journal Entry'));

$MenuItems['GL']['Transactions']['URL'] = array(CheckMods('/Payments.php') . '?NewPayment=Yes',
												CheckMods('/CustomerReceipt.php') . '?NewReceipt=Yes&amp;Type=GL',
												CheckMods('/ImportBankTrans.php'),
												CheckMods('/BankMatching.php') . '?Type=Payments',
												CheckMods('/BankMatching.php') . '?Type=Receipts',
												CheckMods('/GLJournal.php') . '?NewJournal=Yes');

$MenuItems['GL']['Reports']['Caption'] = array(	_('Trial Balance'),
												_('Account Inquiry'),
												_('Account Listing'),
												_('Account Listing to CSV File'),
												_('General Ledger Journal Inquiry'),
												_('Bank Account Reconciliation Statement'),
												_('Cheque Payments Listing'),
												_('Daily Bank Transactions'),
												_('Profit and Loss Statement'),
												_('Balance Sheet'),
												_('Tag Reports'),
												_('Tax Reports'));

$MenuItems['GL']['Reports']['URL'] = array(	CheckMods('/GLTrialBalance.php'),
											CheckMods('/SelectGLAccount.php'),
											CheckMods('/GLAccountReport.php'),
											CheckMods('/GLAccountCSV.php'),
											CheckMods('/GLJournalInquiry.php'),
											CheckMods('/BankReconciliation.php'),
											CheckMods('/PDFChequeListing.php'),
											CheckMods('/DailyBankTransactions.php'),
											CheckMods('/GLProfit_Loss.php'),
											CheckMods('/GLBalanceSheet.php'),
											CheckMods('/GLTagProfit_Loss.php'),
											CheckMods('/Tax.php'));

$MenuItems['GL']['Maintenance']['Caption'] = array(	_('Account Sections'),
													_('Account Groups'),
													_('GL Accounts'),
													_('GL Budgets'),
													_('GL Tags'),
													_('Bank Accounts'),
													_('Bank Account Authorised Users'));

$MenuItems['GL']['Maintenance']['URL'] = array(	CheckMods('/AccountSections.php'),
												CheckMods('/AccountGroups.php'),
												CheckMods('/GLAccounts.php'),
												CheckMods('/GLBudgets.php'),
												CheckMods('/GLTags.php'),
												CheckMods('/BankAccounts.php'),
												CheckMods('/BankAccountUsers.php') );

$MenuItems['FA']['Transactions']['Caption'] = array(_('Add a new Asset'),
													_('Select an Asset'),
													_('Change Asset Location'),
													_('Depreciation Journal'));

$MenuItems['FA']['Transactions']['URL'] = array(CheckMods('/FixedAssetItems.php'),
												CheckMods('/SelectAsset.php'),
												CheckMods('/FixedAssetTransfer.php'),
												CheckMods('/FixedAssetDepreciation.php') );

$MenuItems['FA']['Reports']['Caption'] = array(	_('Asset Register'),
												_('My Maintenance Schedule'),
												_('Maintenance Reminder Emails'));

$MenuItems['FA']['Reports']['URL'] = array(	CheckMods('/FixedAssetRegister.php'),
											CheckMods('/MaintenanceUserSchedule.php'),
											CheckMods('/MaintenanceReminders.php') );

$MenuItems['FA']['Maintenance']['Caption'] = array(	_('Fixed Asset Category Maintenance'),
													_('Add or Maintain Asset Locations'),
													_('Fixed Asset Maintenance Tasks'));

$MenuItems['FA']['Maintenance']['URL'] = array(	CheckMods('/FixedAssetCategories.php'),
												CheckMods('/FixedAssetLocations.php'),
												CheckMods('/MaintenanceTasks.php') );

$MenuItems['PC']['Transactions']['Caption'] = array(_('Assign Cash to PC Tab'),
													_('Claim Expenses From PC Tab'),
													_('Expenses Authorisation'));

$MenuItems['PC']['Transactions']['URL'] = array(CheckMods('/PcAssignCashToTab.php'),
												CheckMods('/PcClaimExpensesFromTab.php'),
												CheckMods('/PcAuthorizeExpenses.php') );

$MenuItems['PC']['Reports']['Caption'] = array(_('PC Tab General Report'), );

$MenuItems['PC']['Reports']['URL'] = array(CheckMods('/PcReportTab.php'), );

$MenuItems['PC']['Maintenance']['Caption'] = array(	_('Types of PC Tabs'),
													_('PC Tabs'),
													_('PC Expenses'),
													_('Expenses for Type of PC Tab'));

$MenuItems['PC']['Maintenance']['URL'] = array(	CheckMods('/PcTypeTabs.php'),
												CheckMods('/PcTabs.php'),
												CheckMods('/PcExpenses.php'),
												CheckMods('/PcExpensesTypeTab.php') );

$MenuItems['system']['Transactions']['Caption'] = array(_('Company Preferences'),
														_('System Parameters'),
														_('Users Maintenance'),
														_('Maintain Security Tokens'),
														_('Access Permissions Maintenance'),
														_('Page Security Settings'),
														_('Currencies Maintenance'),
														_('Tax Authorities and Rates Maintenance'),
														_('Tax Group Maintenance'),
														_('Dispatch Tax Province Maintenance'),
														_('Tax Category Maintenance'),
														_('List Periods Defined'),
														_('Report Builder Tool'),
														_('View Audit Trail'),
														_('Geocode Maintenance'),
														_('Form Designer'),
														_('Web-Store Configuration'),
														_('SMTP Server Details'),
												       	_('Mailing Group Maintenance'));

$MenuItems['system']['Transactions']['URL'] = array(CheckMods('/CompanyPreferences.php'),
													CheckMods('/SystemParameters.php'),
													CheckMods('/WWW_Users.php'),
													CheckMods('/SecurityTokens.php'),
													CheckMods('/WWW_Access.php'),
													CheckMods('/PageSecurity.php'),
													CheckMods('/Currencies.php'),
													CheckMods('/TaxAuthorities.php'),
													CheckMods('/TaxGroups.php'),
													CheckMods('/TaxProvinces.php'),
													CheckMods('/TaxCategories.php'),
													CheckMods('/PeriodsInquiry.php'),
													'/reportwriter/admin/ReportCreator.php',
													CheckMods('/AuditTrail.php'),
													CheckMods('/GeocodeSetup.php'),
													CheckMods('/FormDesigner.php'),
													CheckMods('/ShopParameters.php'),
													CheckMods('/SMTPServer.php'),
											       	CheckMods('/MailingGroupMaintenance.php') );

$MenuItems['system']['Reports']['Caption'] = array(	_('Sales Types'),
													_('Customer Types'),
													_('Supplier Types'),
													_('Credit Status'),
													_('Payment Terms'),
													_('Set Purchase Order Authorisation levels'),
													_('Payment Methods'),
													_('Sales People'),
													_('Sales Areas'),
													_('Shippers'),
													_('Sales GL Interface Postings'),
													_('COGS GL Interface Postings'),
													_('Freight Costs Maintenance'),
													_('Discount Matrix'));

$MenuItems['system']['Reports']['URL'] = array(	CheckMods('/SalesTypes.php'),
												CheckMods('/CustomerTypes.php'),
												CheckMods('/SupplierTypes.php'),
												CheckMods('/CreditStatus.php'),
												CheckMods('/PaymentTerms.php'),
												CheckMods('/PO_AuthorisationLevels.php'),
												CheckMods('/PaymentMethods.php'),
												CheckMods('/SalesPeople.php'),
												CheckMods('/Areas.php'),
												CheckMods('/Shippers.php'),
												CheckMods('/SalesGLPostings.php'),
												CheckMods('/COGSGLPostings.php'),
												CheckMods('/FreightCosts.php'),
												CheckMods('/DiscountMatrix.php') );

$MenuItems['system']['Maintenance']['Caption'] = array(	_('Inventory Categories Maintenance'),
														_('Inventory Locations Maintenance'),
														_('Inventory Location Authorised Users Maintenance'),
														_('Discount Category Maintenance'),
														_('Units of Measure'),
														_('MRP Available Production Days'),
														_('MRP Demand Types'),
														_('Maintain Internal Departments'),
														_('Maintain Internal Stock Categories to User Roles'),
														_('Label Templates Maintenance'));

$MenuItems['system']['Maintenance']['URL'] = array(	CheckMods('/StockCategories.php'),
													CheckMods('/Locations.php'),
													CheckMods('/LocationUsers.php'),
													CheckMods('/DiscountCategories.php'),
													CheckMods('/UnitsOfMeasure.php'),
													CheckMods('/MRPCalendar.php'),
													CheckMods('/MRPDemandTypes.php'),
													CheckMods('/Departments.php'),
													CheckMods('/InternalStockCategoriesByRole.php'),
													CheckMods('/Labels.php') );

$MenuItems['Utilities']['Transactions']['Caption'] = array(	_('Change A Customer Code'),
															_('Change A Customer Branch Code'),
															_('Change A Supplier Code'),
															_('Change A Stock Category Code'),
															_('Change An Inventory Item Code'),
															_('Change A GL Account Code'),
															_('Change A Location Code'),
															_('Update costs for all BOM items, from the bottom up'),
															_('Re-apply costs to Sales Analysis'),
															_('Delete sales transactions'),
															_('Reverse all supplier payments on a specified date'),
															_('Update sales analysis with latest customer data'));

$MenuItems['Utilities']['Transactions']['URL'] = array(	CheckMods('/Z_ChangeCustomerCode.php'),
														CheckMods('/Z_ChangeBranchCode.php'),
														CheckMods('/Z_ChangeSupplierCode.php'),
														CheckMods('/Z_ChangeStockCategory.php'),
														CheckMods('/Z_ChangeStockCode.php'),
														CheckMods('/Z_ChangeGLAccountCode.php'),
														CheckMods('/Z_ChangeLocationCode.php'),
														CheckMods('/Z_BottomUpCosts.php'),
														CheckMods('/Z_ReApplyCostToSA.php'),
														CheckMods('/Z_DeleteSalesTransActions.php'),
														CheckMods('/Z_ReverseSuppPaymentRun.php'),
														CheckMods('/Z_UpdateSalesAnalysisWithLatestCustomerData.php') );

$MenuItems['Utilities']['Reports']['Caption'] = array(	_('Show Local Currency Total Debtor Balances'),
														_('Show Local Currency Total Suppliers Balances'),
														_('Show General Transactions That Do Not Balance'),
														_('List of items without picture'));

$MenuItems['Utilities']['Reports']['URL'] = array(	CheckMods('/Z_CurrencyDebtorsBalances.php'),
													CheckMods('/Z_CurrencySuppliersBalances.php'),
													CheckMods('/Z_CheckGLTransBalance.php'),
													CheckMods('/Z_ItemsWithoutPicture.php') );

$MenuItems['Utilities']['Maintenance']['Caption'] = array(	_('Maintain Language Files'),
															_('Make New Company'),
															_('Data Export Options'),
															_('Import Customers from .csv file'),
															_('Import Stock Items from .csv file'),
															_('Import Price List from .csv file'),
															_('Import Fixed Assets from .csv file'),
															_('Import GL Payments Receipts Or Journals From .csv file'),
															_('Create new company template SQL file and submit to webERP'),
															_('Re-calculate brought forward amounts in GL'),
															_('Re-Post all GL transactions from a specified period'),
															_('Purge all old prices'));

$MenuItems['Utilities']['Maintenance']['URL'] = array(	CheckMods('/Z_poAdmin.php'),
														CheckMods('/Z_MakeNewCompany.php'),
														CheckMods('/Z_DataExport.php'),
														CheckMods('/Z_ImportDebtors.php'),
														CheckMods('/Z_ImportStocks.php'),
														CheckMods('/Z_ImportPriceList.php'),
														CheckMods('/Z_ImportFixedAssets.php'),
														CheckMods('/Z_ImportGLTransactions.php'),														'/Z_CreateCompanyTemplateFile.php',
														CheckMods('/Z_UpdateChartDetailsBFwd.php'),
														CheckMods('/Z_RePostGLFromPeriod.php'),
														CheckMods('/Z_DeleteOldPrices.php') );
?>
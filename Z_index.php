<?php
/* $Id$*/

	include('includes/session.inc');
	$title = _('Special Fixes and Utilities') . ' - ' . _('Only System Administrator');
	include('includes/header.inc');

	echo '<p>' . _('BE VERY CAREFUL DO NOT RUN THESE LINKS BELOW WITHOUT UNDERSTANDING EXACTLY WHAT THEY DO AND THE IMPLICATIONS') . '</p>';

	echo '<p><a href="' .$rootpath . '/Z_ReApplyCostToSA.php">'.  _('Re-apply costs to Sales Analysis') . '</a></p>';
	echo '<p><a href="' .$rootpath . '/EDISendInvoices.php">' . _('Send All Unsent EDI Invoices and Credits') .'</a></p>';
	echo '<p><a href="' .$rootpath . '/Z_ChangeCustomerCode.php">'. _('Change A Customer Code') . '</a></p>';
	echo '<p><a href="' .$rootpath . '/Z_ChangeBranchCode.php">' . _('Change A Customer Branch Code') . '</a></p>';
	echo '<p><a href="' .$rootpath . '/Z_ChangeStockCode.php">' . _('Change An Inventory Item Code') . '</a></p>';
	echo '<p><a href="' .$rootpath . '/Z_ChangeSupplierCode.php">' . _('Change A Supplier Code') . '</a></p>';
	echo '<p><a href="' .$rootpath . '/Z_BottomUpCosts.php">' . _('Update costs for all BOM items, from the bottom up') . '</a></p>';

	echo '<p><a href="' .$rootpath . '/Z_CurrencyDebtorsBalances.php">' . _('Show Local Currency Total Debtor Balances') . '</a></p>';
	echo '<p><a href="' .$rootpath . '/Z_CurrencySuppliersBalances.php">' . _('Show Local Currency Total Suppliers Balances') . '</a></p>';
	echo '<p><a href="' .$rootpath . '/Z_CheckGLTransBalance.php">' . _('Show General Transactions That Do Not Balance') . '</a></p>';
	echo '<p><a href="' .$rootpath . '/Z_poAdmin.php">' . _('Maintain Language Files') . '</a></p>';
	echo '<p><a href="' .$rootpath . '/Z_MakeNewCompany.php">' . _('Make New Company') . '</a></p>';
	echo '<p><a href="' .$rootpath . '/Z_DataExport.php">' . _('Data Export Options') . '</a></p>';
	echo '<p><a href="' .$rootpath . '/Z_GetStockImage.php">' . _('Image Manipulation Utility') . '</a></p>';
	echo '<p><a href="' .$rootpath . '/Z_ImportStocks.php">' . _('Import Stock Items from .csv') . '</a></p>';
	echo '<p><a href="' .$rootpath . '/Z_ImportFixedAssets.php">' . _('Import Fixed Assets from .csv file') . '</a></p>';
	echo '<p><a href="' .$rootpath . '/Z_CreateCompanyTemplateFile.php">' . _('Create new company template SQL file and submit to webERP') . '</a></p>';


	echo '<br /><br /><hr /><br />' . _('The stuff below is really quite dangerous!');

	echo '<p>' . _('To delete a credit note call') . ' ' . $rootpath . '/Z_DeleteCreditNote.php?' . ' ' ._('and the credit note number to delete') . '</p>';
	echo '<p>' . _('To delete an invoice call') . ' ' . $rootpath . '/Z_DeleteInvoice.php?' . _('and the invoice number to delete') . '</p>';
	echo '<p><a href="' .$rootpath . '/Z_UploadForm.php">' . _('Upload a file to the server') . '</a></p>';
	echo '<p><a href="' .$rootpath . '/Z_DeleteSalesTransActions.php">' . _('Delete sales transactions') . '</a></p>';
	echo '<p><a href="' .$rootpath . '/Z_ReverseSuppPaymentRun.php">' . _('Reverse all supplier payments on a specified date') . '</a></p>';
	echo '<p><a href="' .$rootpath . '/Z_UpdateChartDetailsBFwd.php">' . _('Re-calculate brought forward amounts in GL') . '</a></p>';
	echo '<p><a href="' .$rootpath . '/Z_RePostGLFromPeriod.php">' . _('Re-Post all GL transactions from a specified period') . '</a></p>';
	echo '<p><a href="' .$rootpath . '/Z_CheckDebtorsControl.php">' . _('Show Debtors Control (Need to edit Z_CheckDebtorsControl.php for the period to show control totals for') . '</a></p>';

	include('includes/footer.inc');
?>
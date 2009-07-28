<?php
/* $Revision: 1.11 $ */
	$PageSecurity = 15;

	include('includes/session.inc');
	$title = _('Special Fixes and Utilities') . ' - ' . _('Only System Administrator');
	include('includes/header.inc');

	echo '<p>' . _('BE VERY CAREFUL DO NOT RUN THESE LINKS BELOW WITHOUT UNDERSTANDING EXACTLY WHAT THEY DO AND THE IMPLICATIONS');

	echo "<p><a href='$rootpath/Z_ReApplyCostToSA.php?" . SID . "'>".  _('Re-apply costs to Sales Analysis') . '</a>';
	echo "<p><a href='$rootpath/EDISendInvoices.php?" . SID . "'>" . _('Send All Unsent EDI Invoices and Credits') .'</a>';
	echo "<p><a href='$rootpath/Z_ChangeCustomerCode.php?" . SID . "'>". _('Change A Customer Code') . '</a>';
	echo "<p><a href='$rootpath/Z_ChangeBranchCode.php?" . SID . "'>" . _('Change A Customer Branch Code') . '</a>';
	echo "<p><a href='$rootpath/Z_ChangeStockCode.php?" . SID . "'>" . _('Change An Inventory Item Code') . '</a>';
	echo "<p><a href='$rootpath/Z_PriceChanges.php?" . SID . "'>" . _('Bulk Change Customer Pricing') . '</a>';

	echo "<p><a href='$rootpath/Z_CurrencyDebtorsBalances.php?" . SID . "'>" . _('Show Local Currency Total Debtor Balances') . '</a>';
	echo "<p><a href='$rootpath/Z_CurrencySuppliersBalances.php?" . SID . "'>" . _('Show Local Currency Total Suppliers Balances') . '</a>';
	echo "<p><a href='$rootpath/Z_CheckGLTransBalance.php?" . SID . "'>" . _('Show General Transactions That Do Not Balance') . '</a>';
	echo "<p><a href='$rootpath/Z_poAdmin.php?" . SID . "'>" . _('Maintain Language Files') . '</a>';
	echo "<p><a href='$rootpath/Z_MakeNewCompany.php?" . SID . "'>" . _('Make New Company') . '</a>';
	echo "<p><a href='$rootpath/Z_DataExport.php?" . SID . "'>" . _('Data Export Options') . '</a>';
	echo "<p><a href='$rootpath/Z_GetStockImage.php?" . SID . "'>" . _('Image Manipulation Utility') . '</a>';
	echo "<p><a href='$rootpath/Z_ImportStocks.php?" . SID . "'>" . _('Import Stock Items from .csv') . '</a>';

	echo '<br><br><hr><br>' . _('The stuff below is really quite dangerous!');

	echo '<p>' . _('To delete a credit note call') . ' ' . $rootpath . '/Z_DeleteCreditNote.php?' . ' ' ._('and the credit note number to delete');
	echo '<p>' . _('To delete an invoice call') . ' ' . $rootpath . '/Z_DeleteInvoice.php?' . _('and the invoice number to delete');
	echo "<p><a href='$rootpath/Z_UploadForm.php?" . SID . "'>" . _('Upload a file to the server') . '</a>';
	echo "<p><a href='$rootpath/Z_DeleteSalesTransActions.php?" . SID . "'>" . _('Delete sales transactions') . '</a>';
	echo "<p><a href='$rootpath/Z_ReverseSuppPaymentRun.php?" . SID . "'>" . _('Reverse all supplier payments on a specified date') . '</a>';
	echo "<p><a href='$rootpath/Z_UpdateChartDetailsBFwd.php?" . SID . "'>" . _('Re-calculate brought forward amounts in GL') . '</a>';
	echo "<p><a href='$rootpath/Z_RePostGLFromPeriod.php?" . SID . "'>" . _('Re-Post all GL transactions from a specified period') . '</a>';
	echo "<p><a href='$rootpath/Z_CheckDebtorsControl.php?" . SID . "'>" . _('Show Debtors Control (Need to edit Z_CheckDebtorsControl.php for the period to show control totals for') . '</a>';

	include('includes/footer.inc');

?>

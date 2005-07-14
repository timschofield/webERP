<?php
/* $Revision: 1.9 $ */
	$PageSecurity = 15;

	include('includes/session.inc');
	$title = ('Special Fixes and Utilities') . ' - ' . _('Only System Administrator');
	include('includes/header.inc');

	echo '<P>' . _('BE VERY CAREFUL DO NOT RUN THESE LINKS BELOW WITHOUT UNDERSTANDING EXACTLY WHAT THEY DO AND THE IMPLICATIONS');

	echo "<P><A HREF='$rootpath/Z_ReApplyCostToSA.php?" . SID . "'>".  _('Re-apply costs to Sales Analysis') . '</A>';
	echo "<P><A HREF='$rootpath/EDISendInvoices.php?" . SID . "'>" . _('Send All Unsent EDI Invoices and Credits') .'</A>';
	echo "<P><A HREF='$rootpath/Z_ChangeCustomerCode.php?" . SID . "'>". _('Change A Customer Code') . '</A>';
	echo "<P><A HREF='$rootpath/Z_ChangeBranchCode.php?" . SID . "'>" . _('Change A Customer Branch Code') . '</A>';
	echo "<P><A HREF='$rootpath/Z_ChangeStockCode.php?" . SID . "'>" . _('Change An Inventory Item Code') . '</A>';
	echo "<P><A HREF='$rootpath/Z_PriceChanges.php?" . SID . "'>" . _('Bulk Change Customer Pricing') . '</A>';

	echo "<P><A HREF='$rootpath/Z_CurrencyDebtorsBalances.php?" . SID . "'>" . _('Show Local Currency Total Debtor Balances') . '</A>';
	echo "<P><A HREF='$rootpath/Z_CurrencySuppliersBalances.php?" . SID . "'>" . _('Show Local Currency Total Suppliers Balances') . '</A>';
	echo "<P><A HREF='$rootpath/Z_CheckGLTransBalance.php?" . SID . "'>" . _('Show General Transactions That Do Not Balance') . '</A>';
	echo "<P><A HREF='$rootpath/Z_poAdmin.php?" . SID . "'>" . _('Maintain Language Files') . '</A>';
	echo "<P><A HREF='$rootpath/Z_MakeNewCompany.php?" . SID . "'>" . _('Make New Company') . '</A>';
	echo "<P><A HREF='$rootpath/Z_DataExport.php?" . SID . "'>" . _('Data Export Options') . '</A>';
	echo "<P><A HREF='$rootpath/Z_GetStockImage.php?" . SID . "'>" . _('Image Manipulation Utility') . '</A>';

	echo '<BR><BR><HR><BR>' . _('The stuff below is really quite dangerous!');

	echo '<P>' . _('To delete a credit note call') . ' ' . $rootpath . '/Z_DeleteCreditNote.php?' . ' ' ._('and the credit note number to delete');
	echo '<P>' . _('To delete an invoice call') . ' ' . $rootpath . '/Z_DeleteInvoice.php?' . _('and the invoice number to delete');
	echo "<P><A HREF='$rootpath/Z_UploadForm.php?" . SID . "'>" . _('Upload a file to the server') . '</A>';
	echo "<P><A HREF='$rootpath/Z_DeleteSalesTransActions.php?" . SID . "'>" . _('Delete sales transactions') . '</A>';
	echo "<P><A HREF='$rootpath/Z_ReverseSuppPaymentRun.php?" . SID . "'>" . _('Reverse all supplier payments on a specified date') . '</A>';
	echo "<P><A HREF='$rootpath/Z_UpdateChartDetailsBFwd.php?" . SID . "'>" . _('Re-calculate brought forward amounts in GL') . '</A>';
	echo "<P><A HREF='$rootpath/Z_RePostGLFromPeriod.php?" . SID . "'>" . _('Re-Post all GL transactions from a specified period') . '</A>';
	echo "<P><A HREF='$rootpath/Z_CheckDebtorsControl.php?" . SID . "'>" . _('Show Debtors Control (Need to edit Z_CheckDebtorsControl.php for the period to show control totals for') . '</A>';

	include('includes/footer.inc');

?>

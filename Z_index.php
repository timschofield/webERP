<?php
/* $Revision: 1.3 $ */
	$title = "Special Fixes and Utilities -  Only System Administrator";
	$PageSecurity = 15;

	include("includes/session.inc");
	include("includes/header.inc");

	echo "<P>BE VERY CAREFUL DO NOT RUN THESE LINKS BELOW WITHOUT UNDERSTANDING EXACTLY WHAT THEY DO AND THE IMPLICATIONS";

	echo "<P><A HREF='$rootpath/Z_ReApplyCostToSA.php?" . SID . "'>Re-apply costs to Sales Analysis</A>";
	echo "<P><A HREF='$rootpath/EDISendInvoices.php?" . SID . "'>Send All Unsent EDI Invoices and Credits</A>";
	echo "<P><A HREF='$rootpath/Z_ChangeCustomerCode.php?" . SID . "'>Change A Customer Code</A>";
	echo "<P><A HREF='$rootpath/Z_ChangeBranchCode.php?" . SID . "'>Change A Customer Branch Code</A>";
	echo "<P><A HREF='$rootpath/Z_ChangeStockCode.php?" . SID . "'>Change An Inventory Item Code</A>";
	echo "<P><A HREF='$rootpath/Z_PriceChanges.php?" . SID . "'>Bulk Change Customer Pricing</A>";

	echo "<P><A HREF='$rootpath/Z_CurrencyDebtorsBalances.php?" . SID . "'>Show Local Currency Total Debtor Balances</A>";
	echo "<P><A HREF='$rootpath/Z_CurrencySuppliersBalances.php?" . SID . "'>Show Local Currency Total Suppliers Balances</A>";

	echo "<BR><BR><HR><BR>The stuff below is really quite dangerous!";

	echo "<P>To delete a credit note call $rootpath/Z_DeleteCreditNote.php? and the credit note number to delete";
	echo "<P>To delete an invoice call $rootpath/Z_DeleteInvoice.php? and the invoice number to delete";
	echo "<P><A HREF='$rootpath/Z_UploadForm.php?" . SID . "'>Upload a file to the server</A>";
	echo "<P><A HREF='$rootpath/Z_DeleteSalesTransActions.php?" . SID . "'>Delete sales transactions</A>";
	echo "<P><A HREF='$rootpath/Z_ReverseSuppPaymentRun.php?" . SID . "'>Reverse all supplier payments on a specified date</A>";
	echo "<P><A HREF='$rootpath/Z_UpdateChartDetailsBFwd.php?" . SID . "'>Re-calculate brought forward amounts in GL</A>";
	echo "<P><A HREF='$rootpath/Z_RePostGLFromPeriod.php?" . SID . "'>Re-Post all GL transactions from a specified period</A>";
	echo "<P><A HREF='$rootpath/Z_CheckDebtorsControl.php?" . SID . "'>Show Debtors Control (Need to edit Z_CheckDebtorsControl.php for the period to show control totals for</A>";

	include("includes/footer.inc");

?>

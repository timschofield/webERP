<?php
/*	$Id: DatabaseTranslations.php 6651 2014-08-13 19:52:39Z rchacon $*/
/*******************************************************************************
The script includes/DatabaseTranslations.php is a locale language file for the
contents of the fields in the database. The purpose of this file is to translate
the database fields that appears in screens and reports. This script is only
used at the time that the system default language file is rebuilt. Can be
extended for scripts and other tables where the data from the table is static
and used to display.
*******************************************************************************/

// scripts.description:
// RChacon: It could be taken from each php script?

// securityroles.secrolename:
// RChacon: It can be directly modified by the user.

// securitytokens.tokenname:
// RChacon: It can be directly modified by the user.

// systypes.typename:
$systypes_typename[0] = _('Journal - GL');
$systypes_typename[1] = _('Payment - GL');
$systypes_typename[2] = _('Receipt - GL');
$systypes_typename[3] = _('Standing Journal');
$systypes_typename[10] = _('Sales Invoice');
$systypes_typename[11] = _('Credit Note');
$systypes_typename[12] = _('Receipt');
$systypes_typename[15] = _('Journal - Debtors');
$systypes_typename[16] = _('Location Transfer');
$systypes_typename[17] = _('Stock Adjustment');
$systypes_typename[18] = _('Purchase Order');
$systypes_typename[19] = _('Picking List');
$systypes_typename[20] = _('Purchase Invoice');
$systypes_typename[21] = _('Debit Note');
$systypes_typename[22] = _('Creditors Payment');
$systypes_typename[23] = _('Creditors Journal');
$systypes_typename[25] = _('Purchase Order Delivery');
$systypes_typename[26] = _('Work Order Receipt');
$systypes_typename[28] = _('Work Order Issue');
$systypes_typename[29] = _('Work Order Variance');
$systypes_typename[30] = _('Sales Order');
$systypes_typename[31] = _('Shipment Close');
$systypes_typename[32] = _('Contract Close');
$systypes_typename[35] = _('Cost Update');
$systypes_typename[36] = _('Exchange Difference');
$systypes_typename[37] = _('Tenders');
$systypes_typename[38] = _('Stock Requests');
$systypes_typename[40] = _('Work Order');
$systypes_typename[41] = _('Asset Addition');
$systypes_typename[42] = _('Asset Category Change');
$systypes_typename[43] = _('Delete w/down asset');
$systypes_typename[44] = _('Depreciation');
$systypes_typename[49] = _('Import Fixed Assets');
$systypes_typename[50] = _('Opening Balance');
$systypes_typename[500] = _('Auto Debtor Number');

// taxcategories.taxcatname:
$taxcategories_taxcatname[4] = _('Exempt');
$taxcategories_taxcatname[5] = _('Freight');
$taxcategories_taxcatname[6] = _('Handling');

// General purpose:
$General_purpose = _('Default');

?>

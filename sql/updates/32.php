<?php

// Remove Z_index.php
// https://github.com/timschofield/webERP/issues/755
RemoveScript('Z_index.php');

// Add Import Suppliers to menu [Utilities > Maintenance > Import Suppliers from .csv]
// https://github.com/timschofield/webERP/issues/754
// 1. register (existing) import suppliers script
NewScript('Z_ImportSuppliers.php', 15); // available to administrator only

// Insert menu item [Utilities > Maintenance > Import Supppliers from .csv file]
// - remove all menu items from [Setup > Maintenance]
// function RemoveMenuItem($Link, $Section, $Caption, $URL)
RemoveMenuItem('Utilities', 'Maintenance', 'Maintain Language Files', '/Z_poAdmin.php');
RemoveMenuItem('Utilities', 'Maintenance', 'Make New Company', '/Z_MakeNewCompany.php');
RemoveMenuItem('Utilities', 'Maintenance', 'Data Export Options', '/Z_DataExport.php');
RemoveMenuItem('Utilities', 'Maintenance', 'Import Customers from .csv file', '/Z_ImportDebtors.php');
RemoveMenuItem('Utilities', 'Maintenance', 'Import Stock Items from .csv', '/Z_ImportStocks.php');
RemoveMenuItem('Utilities', 'Maintenance', 'Import Price List from .csv file', '/Z_ImportPriceList.php');
RemoveMenuItem('Utilities', 'Maintenance', 'Import Fixed Assets from .csv file', '/Z_ImportFixedAssets.php');
RemoveMenuItem('Utilities', 'Maintenance', 'Import GL Payments Receipts Or Journals From .csv file', '/Z_ImportGLTransactions.php');
RemoveMenuItem('Utilities', 'Maintenance', 'Create new company template SQL file and submit to webERP', '/Z_CreateCompanyTemplateFile.php');
RemoveMenuItem('Utilities', 'Maintenance', 'Purge all old prices', '/Z_DeleteOldPrices.php');
RemoveMenuItem('Utilities', 'Maintenance', 'Remove all purchase back orders', '/Z_RemovePurchaseBackOrders.php');

// 2. re-create menu items including Import Suppliers
// function NewMenuItem($Link, $Section, $Caption, $URL, $Sequence)
NewMenuItem('Utilities', 'Maintenance', __('Maintain Language Files'), '/Z_poAdmin.php', 10);
NewMenuItem('Utilities', 'Maintenance', __('Make New Company'), '/Z_MakeNewCompany.php', 20);
NewMenuItem('Utilities', 'Maintenance', __('Data Export Options'), '/Z_DataExport.php', 30);
NewMenuItem('Utilities', 'Maintenance', __('Import Customers from .csv file'), '/Z_ImportDebtors.php', 40);
NewMenuItem('Utilities', 'Maintenance', __('Import Suppliers from .csv file'), '/Z_ImportSuppliers.php', 50);
NewMenuItem('Utilities', 'Maintenance', __('Import Stock Items from .csv file'), '/Z_ImportStocks.php', 60);
NewMenuItem('Utilities', 'Maintenance', __('Import Price List from .csv file'), '/Z_ImportPriceList.php', 70);
NewMenuItem('Utilities', 'Maintenance', __('Import Fixed Assets from .csv file'), '/Z_ImportFixedAssets.php', 80);
NewMenuItem('Utilities', 'Maintenance', __('Import GL Payments Receipts Or Journals From .csv file'), '/Z_ImportGLTransactions.php', 90);
NewMenuItem('Utilities', 'Maintenance', __('Create new company template SQL file and submit to webERP'), '/Z_CreateCompanyTemplateFile.php', 100);
NewMenuItem('Utilities', 'Maintenance', __('Purge all old prices'), '/Z_DeleteOldPrices.php', 110);
NewMenuItem('Utilities', 'Maintenance', __('Remove all purchase back orders'), '/Z_RemovePurchaseBackOrders.php', 120);

// set (default) security token for Z_Import... scripts to "User Management and System Adminstration"
// https://github.com/timschofield/webERP/discussions/789
// TODO add code

// cleanup - UpdateDbNo() MUST BE ONLY LINE BEFORE "}" in particular NO COMMENT LINES
if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(basename(__FILE__, '.php'), __('Un-register Z_index.php, add Import Suppliers menu item'));
}

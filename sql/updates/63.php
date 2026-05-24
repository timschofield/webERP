<?php

// Add import BOMs script (Z_ImportStocksBOMs.php)
// See https://github.com/timschofield/webERP/discussions/591

// NewScript($ScriptName, $PageSecurity)
NewScript('Z_ImportStocksBOMs.php', 15);	// Security Token: User Management and System Admistration

// NewMenuItem($Link, $Section, $Caption, $URL, $Sequence)
NewMenuItem('Utilities', 'Maintenance', __('Import BOMs from .csv file'), '/Z_ImportStocksBOMs.php', 65);

// Wrap-up
UpdateDBNo(basename(__FILE__, '.php'), __('Add Z_ImportStocksBOMs script'));

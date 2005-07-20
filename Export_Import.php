<?php

$PageSecurity= 7;
$title = "Export and Import";
require('includes/session.inc');
include('includes/header.inc');

echo '<br/><br/>

	<a href="' . $rootpath . '/Export.php">' . _('Export WebERP Items') . '</a><br/>

	<a href="' . $rootpath . '/Import.php">' . _('Import WebERP Items') . '</a><br />

	<a href="' . $rootpath . '/QBexport.php">' . _('Export QuickBooks Items') . '</a><br/>

	<a href="' . $rootpath . '/QBimport.php">' . _('Import QuickBooks Items') . '</a><br/>';


include('includes/footer.inc');


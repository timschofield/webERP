<?php

/* Changes for factor company code to work
 */

DropConstraint('suppliers', 'suppliers_ibfk_4', $db);
UpdateField('suppliers', 'factorcompanyid', 0, '`factorcompanyid`=1', $db);
DeleteRecords('factorcompanies', "coyname='None'", $db);

UpdateDBNo(14, $db);

?>
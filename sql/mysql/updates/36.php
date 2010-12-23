<?php

/* Remove the primary key on table orderdeliverydifferenceslog
 */

DropPrimaryKey('orderdeliverydifferenceslog', array('orderno','invoiceno','stockid'), $db);

UpdateDBNo(36, $db);

?>
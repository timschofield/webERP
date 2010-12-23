<?php

/* New system type needed for contract work
 */

InsertRecord('systypes', array('typeid', 'typename'), array('32', 'Contract Close'), array('typeid', 'typename', 'typeno'), array('32', 'Contract Close', '1'), $db);

UpdateDBNo(38, $db);

?>
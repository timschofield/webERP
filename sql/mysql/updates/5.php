<?php

/* Expand the modules allowed field in www_users to allow for
 * more modules to be added
 */

ChangeColumnSize('modulesallowed', 'www_users', 'varchar(40)', 'NOT NULL', '0,0,0,0,0,0,0,0,0,0,0,', 40, $db);

UpdateDBNo(5, $db);

?>
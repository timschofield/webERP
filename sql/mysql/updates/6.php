<?php

/* Increase the size of the language field in www_users to enable
 * utf languages to be accepted
 */

ChangeColumnSize('language', 'www_users', 'varchar(10)', 'NOT NULL', 'en_GB.utf8', 10, $db);

UpdateDBNo(6, $db);

?>
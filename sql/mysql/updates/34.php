<?php

/* Move the version number to the database from the config.php file
 */

NewConfigValue('VersionNumber', '4.00-RC1', $db);

UpdateDBNo(34, $db);

?>
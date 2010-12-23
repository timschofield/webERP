<?php

/* Creates a new config variable that records what the latest database
 * upgrade that has been applied is. This is then compared to the value in config.php
 * to ascertain whether a new upgrade is needed
 */

NewConfigValue('DBUpdateNumber', 0, $db);
UpdateDBNo(0, $db);

?>
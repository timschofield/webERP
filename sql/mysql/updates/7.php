<?php

/* New config values for logging any prnMsg messages
 * Defines the patth and the messages to be logged
 */

NewConfigValue('LogPath', '', $db);
NewConfigValue('LogSeverity', '0', $db);

UpdateDBNo(7, $db);

?>
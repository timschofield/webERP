<?php

/* Correct the MenuAccess.php security level
 */

UpdateField('pagesecurity', 'script', 'MenuAccess.php', 'script="pagesecurityAccess.php"', $db);

UpdateDBNo(52, $db);

?>
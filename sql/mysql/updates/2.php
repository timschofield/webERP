<?php

/* Drop table assetmanager if version 1 of the asset manager is still
 * in the database
 */

DropTable('assetmanager', 'lifetime', $db);

UpdateDBNo(2, $db);

?>
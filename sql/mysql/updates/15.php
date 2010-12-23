<?php

/* New security token for petty cash usage */

UpdateField('securitytokens', 'tokenname', 'Petty Cash', 'tokenid=6', $db);

UpdateDBNo(15, $db);

?>
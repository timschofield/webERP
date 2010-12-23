<?php

/* Create a new security token foer prices security, so that only
 * certain roles can view pricing information
 */

UpdateField('securitytokens', 'tokenname', 'Prices Security', 'tokenid=12', $db);

UpdateDBNo(35, $db);

?>
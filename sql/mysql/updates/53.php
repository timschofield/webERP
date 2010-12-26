<?php

/* Remove foreign key from debtortrans table
 */

DropConstraint('debtortrans', 'debtortrans_ibfk_1', $db);

UpdateDBNo(53, $db);

?>
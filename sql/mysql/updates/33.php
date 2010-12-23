<?php

/* Change the shipment quantity to a double from integer,
 * as stock quantities can be of type double
 */


ChangeColumnType('shipqty', 'loctransfers', 'double', 'NOT NULL', 0.0, $db);

UpdateDBNo(33, $db);

?>
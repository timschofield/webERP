<?php

/* Mods to select fixed asset for disposal
 */


AddColumn('disposaldate', 'fixedassets', 'date', 'NOT NULL', '0000-00-00', 'barcode', $db);
ChangeColumnName('disposalvalue', 'fixedassets', 'double', 'NOT NULL', 0.0, 'disposalproceeds', $db);

UpdateDBNo(48, $db);

?>
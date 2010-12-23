<?php
/* Addin extra fields to stockcatproperties table
 */

AddColumn('maximumvalue', 'stockcatproperties', 'Double', 'NOT NULL', 999999999, 'defaultvalue', $db);
AddColumn('minimumvalue', 'stockcatproperties', 'Double', 'NOT NULL', -999999999, 'maximumvalue', $db);
AddColumn('numericvalue', 'stockcatproperties', 'tinyint', 'NOT NULL', 0, 'minimumvalue', $db);

UpdateDBNo(41, $db);

?>
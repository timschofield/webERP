<?php

/* CReates the fixedassetlocations table for version 2
 * of the asset manager
 */

CreateTable("fixedassetlocations","CREATE TABLE `fixedassetlocations` (
		`locationid` char(6) NOT NULL default '',
		`locationdescription` char(20) NOT NULL default '',
		`parentlocationid` char(6) DEFAULT '',
		PRIMARY KEY  (`locationid`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8", $db);

UpdateDBNo(4, $db);

?>
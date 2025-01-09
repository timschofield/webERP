<?php

/* KL RICARD ALREADY EXECUTED
executeSQL("ALTER TABLE tags MODIFY tagref INT AUTO_INCREMENT");
KL RICARD ALREADY EXECUTED END */

executeSQL("ALTER TABLE pcexpenses MODIFY tag VARCHAR(100)");

/* KL RICARD ALREADY EXECUTED
CreateTable('gltags',
"CREATE TABLE `gltags` (
  `counterindex` INT(11) NOT NULL DEFAULT '0',
  `tagref` INT(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`counterindex`, `tagref`),
  FOREIGN KEY (counterindex) REFERENCES gltrans(counterindex),
  FOREIGN KEY (tagref) REFERENCES tags(tagref)
)");
KL RICARD ALREADY EXECUTED END */

CreateTable('pctags',
"CREATE TABLE `pctags` (
  `pccashdetail` int NOT NULL,
  `tag` int NOT NULL,
  PRIMARY KEY (`pccashdetail`,`tag`)
)");

//executeSQL("INSERT INTO gltags (SELECT counterindex, tag  FROM gltrans)");

/* KL RICARD EXECUTE THIS */
executeSQL("TRUNCATE gltags");
executeSQL("INSERT INTO gltags (SELECT counterindex, tag  FROM gltrans)");
/* KL RICARD END EXECUTE THIS */

DropColumn('tag', 'gltrans');

UpdateDBNo(basename(__FILE__, '.php'), _('Database update necessary for multi tagging GL transactions'));

?>
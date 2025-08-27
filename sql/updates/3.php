<?php

executeSQL("ALTER TABLE tags MODIFY tagref INT AUTO_INCREMENT");
executeSQL("ALTER TABLE pcexpenses MODIFY tag VARCHAR(100)");

CreateTable('gltags',
"CREATE TABLE `gltags` (
  `counterindex` INT(11) NOT NULL DEFAULT '0',
  `tagref` INT(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`counterindex`, `tagref`),
  FOREIGN KEY (counterindex) REFERENCES gltrans(counterindex),
  FOREIGN KEY (tagref) REFERENCES tags(tagref)
)");

CreateTable('pctags',
"CREATE TABLE `pctags` (
  `pccashdetail` int NOT NULL,
  `tag` int NOT NULL,
  PRIMARY KEY (`pccashdetail`,`tag`)
)");

executeSQL("INSERT INTO tags VALUES(0, 'None')");
executeSQL("INSERT INTO gltags (SELECT counterindex, tag  FROM gltrans)");

DropColumn('tag', 'gltrans');

if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(basename(__FILE__, '.php'), __('Database update necessary for multi tagging GL transactions'));
}

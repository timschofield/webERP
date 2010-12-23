<?php

/* Database changes required to add start and end dates for sales prices
 */

AddColumn('startdate', 'prices', 'Date', 'NOT NULL', '0000-00-00', 'branchcode', $db);
AddColumn('enddate', 'prices', 'Date', 'NOT NULL', '9999-12-31', 'startdate', $db);

DropPrimaryKey('prices', array('stockid' , 'typeabbrev' , 'currabrev' , 'debtorno'), $db);
AddPrimaryKey('prices', array('stockid' , 'typeabbrev' , 'currabrev' , 'debtorno' , 'branchcode', 'startdate' , 'enddate'), $db);
UpdateField('prices', 'startdate', '1999-01-01', 'stockid="%"', $db);
UpdateField('prices', 'enddate', '', 'stockid="%"', $db);

UpdateDBNo(19, $db);

?>
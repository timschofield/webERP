<?php

/* Lots of database changes required for the move from fixed
 * asset manager v2 to v3
 */

RenameTable('assetmanager', 'fixedassets', $db);
AddColumn('assetcategoryid', 'fixedassets', 'varchar(6)', 'NOT NULL', '', 'disposalvalue', $db);
AddColumn('description', 'fixedassets', 'varchar(50)', 'NOT NULL', '', 'assetcategoryid', $db);
AddColumn('longdescription', 'fixedassets', 'text', 'NOT NULL', '', 'description', $db);
AddColumn('depntype', 'fixedassets', 'int(11)', 'NOT NULL', 1, 'longdescription', $db);
AddColumn('depnrate', 'fixedassets', 'double', 'NOT NULL', 0.0, 'depntype', $db);
AddColumn('barcode', 'fixedassets', 'varchar(30)', 'NOT NULL', '', 'depnrate', $db);
ChangeColumnName('depn', 'fixedassets', 'double', 'NOT NULL', 0.0, 'accumdepn', $db);
ChangeColumnName('location', 'fixedassets', 'varchar(6)', 'NOT NULL', '', 'assetlocation', $db);

$sql="desc fixedassets stockid";
$result=DB_query($sql, $db);
if (DB_num_rows($result)>0) {
	$response=executeSQL("UPDATE fixedassets INNER JOIN stockmaster ON fixedassets.stockid=stockmaster.stockid SET assetcategoryid=stockmaster.categoryid,
fixedassets.description=stockmaster.description, fixedassets.longdescription=stockmaster.longdescription", $db, False);
	if ($response==0) {
		OutputResult( _('The fixedassets table has been updated from stockmaster') , 'success');
	} else {
		OutputResult( _('The fixedassets table could not be updated from stockmaster') , 'error');
	}
} else {
	OutputResult( _('The fixedassets table is already correct') , 'info');
}

Createtable('fixedassetcategories', "CREATE TABLE IF NOT EXISTS `fixedassetcategories` (
  `categoryid` char(6) NOT NULL DEFAULT '',
  `categorydescription` char(20) NOT NULL DEFAULT '',
  `costact` int(11) NOT NULL DEFAULT '0',
  `depnact` int(11) NOT NULL DEFAULT '0',
  `disposalact` int(11) NOT NULL DEFAULT '80000',
  `accumdepnact` int(11) NOT NULL DEFAULT '0',
  defaultdepnrate double NOT NULL DEFAULT '.2',
  defaultdepntype int NOT NULL DEFAULT '1',
  PRIMARY KEY (`categoryid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8", $db);

$sql="SELECT categoryid FROM fixedassetcategories";
$result=DB_query($sql, $db);
if (DB_num_rows($result)==0) {
	$response=executeSQL("INSERT INTO fixedassetcategories (categoryid, categorydescription, costact, depnact, disposalact, accumdepnact)
SELECT categoryid, categorydescription, stockact, adjglact, materialuseagevarac, wipact FROM stockcategory WHERE stocktype='A'", $db, False);
	if ($response==0) {
		OutputResult( _('The fixedassetcategories table has been updated from stockcategory') , 'success');
	} else {
		OutputResult( _('The fixedassetcategories table could not be updated from stockcategory') , 'error');
	}
} else {
	OutputResult( _('The fixedassetcategories table is already correct') , 'info');
}

$sql="SELECT categoryid FROM stockcategory WHERE stockcategory.stocktype='A'";
$result=DB_query($sql, $db);
if (DB_num_rows($result)!=0) {
	$response=executeSQL("DELETE locstock.* FROM locstock INNER JOIN stockmaster ON locstock.stockid=stockmaster.stockid INNER JOIN stockcategory ON stockmaster.categoryid=stockcategory.categoryid WHERE stockcategory.stocktype='A'", $db, False);
	if ($response==0) {
		OutputResult( _('The fixedassetcategories have been removed from stockcategory') , 'success');
	} else {
		OutputResult( _('The fixedassetcategories table could not be removed from stockcategory') , 'error');
	}
} else {
	OutputResult( _('The stockcategory table is already correct') , 'info');
}

$sql="SELECT stockitemproperties.stockid
	FROM stockitemproperties
	INNER JOIN stockmaster
		ON stockitemproperties.stockid=stockmaster.stockid
	INNER JOIN stockcategory
		ON stockmaster.categoryid=stockcategory.categoryid
	WHERE stockcategory.stocktype='A'";
$result=DB_query($sql, $db);
if (DB_num_rows($result)!=0) {
	$response=executeSQL("DELETE stockitemproperties.* FROM stockitemproperties INNER JOIN stockmaster ON stockitemproperties.stockid=stockmaster.stockid INNER JOIN stockcategory ON stockmaster.categoryid=stockcategory.categoryid WHERE stockcategory.stocktype='A'", $db, False);
	if ($response==0) {
		OutputResult( _('The fixed assets have been removed from stockitemproperties') , 'success');
	} else {
		OutputResult( _('The fixed assets could not be removed from stockitemproperties') , 'error');
	}
} else {
	OutputResult( _('The fixed assets have already been removed from stockitemproperties') , 'info');
}

$sql="SELECT stockserialmoves.* FROM stockserialmoves, stockmoves,
stockmaster,stockcategory WHERE stockserialmoves.stockmoveno=stockmoves.stkmoveno AND
stockmoves.stockid = stockmaster.stockid AND stockmaster.categoryid = stockcategory.categoryid AND stockcategory.stocktype = 'A'";
$result=DB_query($sql, $db);
if (DB_num_rows($result)!=0) {
	$response=executeSQL("DELETE stockserialmoves.* FROM stockserialmoves, stockmoves,
stockmaster,stockcategory WHERE stockserialmoves.stockmoveno=stockmoves.stkmoveno AND
stockmoves.stockid = stockmaster.stockid AND stockmaster.categoryid = stockcategory.categoryid AND stockcategory.stocktype = 'A'", $db, False);
	if ($response==0) {
		OutputResult( _('The fixed assets have been removed from stockitemproperties') , 'success');
	} else {
		OutputResult( _('The fixed assets could not be removed from stockitemproperties') , 'error');
	}
} else {
	OutputResult( _('The fixed assets have already been removed from stockitemproperties') , 'info');
}

$sql="SELECT stockserialitems.* FROM stockserialitems, stockmaster, stockcategory
WHERE stockserialitems.stockid = stockmaster.stockid AND stockmaster.categoryid=stockcategory.categoryid AND stocktype='A'";
$result=DB_query($sql, $db);
if (DB_num_rows($result)!=0) {
	$response=executeSQL("DELETE stockserialitems.* FROM stockserialitems, stockmaster, stockcategory
WHERE stockserialitems.stockid = stockmaster.stockid AND stockmaster.categoryid=stockcategory.categoryid AND stocktype='A'", $db, False);
	if ($response==0) {
		OutputResult( _('The fixed assets have been removed from stockserialitems, stockmaster, and stockcategory tables') , 'success');
	} else {
		OutputResult( _('The fixed assets could not be removed from stockserialitems, stockmaster, and stockcategory tables') , 'error');
	}
} else {
	OutputResult( _('The fixed assets have already been removed from stockserialitems, stockmaster, and stockcategory tables') , 'info');
}

$sql="SELECT stockmoves.*
		FROM stockmoves,
			stockmaster,
			stockcategory
		WHERE stockmoves.stockid = stockmaster.stockid
			AND stockmaster.categoryid = stockcategory.categoryid
			AND stockcategory.stocktype = 'A'";
$result=DB_query($sql, $db);
if (DB_num_rows($result)!=0) {
	$response=executeSQL("DELETE stockmoves.* FROM stockmoves, stockmaster, stockcategory WHERE stockmoves.stockid = stockmaster.stockid AND stockmaster.categoryid = stockcategory.categoryid AND stockcategory.stocktype = 'A'", $db, False);
	if ($response==0) {
		OutputResult( _('The fixed assets have been removed from stockmoves, stockmaster, and stockcategory tables') , 'success');
	} else {
		OutputResult( _('The fixed assets could not be removed from stockmoves, stockmaster, and stockcategory tables') , 'error');
	}
} else {
	OutputResult( _('The fixed assets have already been removed from stockmoves, stockmaster, and stockcategory tables') , 'info');
}

$sql="SELECT stockmaster.* FROM stockmaster INNER JOIN stockcategory ON stockmaster.categoryid=stockcategory.categoryid WHERE stockcategory.stocktype='A'";
$result=DB_query($sql, $db);
if (DB_num_rows($result)!=0) {
	$response=executeSQL("DELETE stockmaster.* FROM stockmaster INNER JOIN stockcategory ON stockmaster.categoryid=stockcategory.categoryid WHERE stockcategory.stocktype='A'", $db, False);
	if ($response==0) {
		OutputResult( _('The fixed assets have been removed from stockmaster table') , 'success');
	} else {
		OutputResult( _('The fixed assets could not be removed from stockmaster table') , 'error');
	}
} else {
	OutputResult( _('The fixed assets have already been removed from stockmaster table') , 'info');
}

ChangeColumnName('id', 'fixedassets', 'int(11)', 'NOT NULL', 0, 'assetid', $db, 'AUTO_INCREMENT');

$sql="SELECT * FROM  stockcategory WHERE stocktype='A'";
$result=DB_query($sql, $db);
if (DB_num_rows($result)!=0) {
	$response=executeSQL("DELETE FROM stockcategory WHERE stocktype='A'", $db, False);
	if ($response==0) {
		OutputResult( _('The fixed assets have been removed from stockmaster table') , 'success');
	} else {
		OutputResult( _('The fixed assets could not be removed from stockmaster table') , 'error');
	}
} else {
	OutputResult( _('The fixed assets have already been removed from stockmaster table') , 'info');
}

DropColumn('stockid', 'fixedassets', $db);

InsertRecord('systypes', array('typeid', 'typename'), array('41', 'Asset Addition'), array('typeid', 'typename', 'typeno'), array('41', 'Asset Addition', '1'), $db);
InsertRecord('systypes', array('typeid', 'typename'), array('42', 'Asset Category Change'), array('typeid', 'typename', 'typeno'), array('42', 'Asset Category Change', '1'), $db);
InsertRecord('systypes', array('typeid', 'typename'), array('43', 'Delete w/down asset'), array('typeid', 'typename', 'typeno'), array('43', 'Delete w/down asset', '1'), $db);
InsertRecord('systypes', array('typeid', 'typename'), array('44', 'Depreciation'), array('typeid', 'typename', 'typeno'), array('44', 'Depreciation', '1'), $db);

CreateTable('fixedassettrans', "CREATE TABLE fixedassettrans(
id INT( 11 ) NOT NULL AUTO_INCREMENT ,
assetid INT( 11 ) NOT NULL ,
transtype TINYINT( 4 ) NOT NULL ,
transdate DATE NOT NULL,
transno INT NOT NULL ,
periodno SMALLINT( 6 ) NOT NULL ,
inputdate DATE NOT NULL ,
fixedassettranstype  varchar(8) NOT NULL ,
amount DOUBLE NOT NULL ,
PRIMARY KEY ( id ) ,
INDEX ( assetid, transtype, transno ) ,
INDEX ( inputdate ),
INDEX (transdate)
) ENGINE = InnoDB DEFAULT CHARSET = utf8", $db);

AddColumn('assetid', 'purchorderdetails', 'int(11)', 'NOT NULL', 0, 'total_amount', $db);

InsertRecord('systypes', array('typeid', 'typename'), array('49', 'Import Fixed Assets'), array('typeid', 'typename', 'typeno'), array('49', 'Import Fixed Assets', '1'), $db);

UpdateDBNo(42, $db);

?>
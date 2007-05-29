CREATE TABLE `reports` (
  `id` int(5) NOT NULL auto_increment,
  `reportname` varchar(30) NOT NULL default '',
  `reporttype` char(3) NOT NULL default 'rpt',
  `groupname` varchar(9) NOT NULL default 'misc',
  `defaultreport` enum('1','0') NOT NULL default '0',
  `papersize` varchar(15) NOT NULL default 'A4,210,297',
  `paperorientation` enum('P','L') NOT NULL default 'P',
  `margintop` int(3) NOT NULL default '10',
  `marginbottom` int(3) NOT NULL default '10',
  `marginleft` int(3) NOT NULL default '10',
  `marginright` int(3) NOT NULL default '10',
  `coynamefont` varchar(20) NOT NULL default 'Helvetica',
  `coynamefontsize` int(3) NOT NULL default '12',
  `coynamefontcolor` varchar(11) NOT NULL default '0,0,0',
  `coynamealign` enum('L','C','R') NOT NULL default 'C',
  `coynameshow` enum('1','0') NOT NULL default '1',
  `title1desc` varchar(50) NOT NULL default '%reportname%',
  `title1font` varchar(20) NOT NULL default 'Helvetica',
  `title1fontsize` int(3) NOT NULL default '10',
  `title1fontcolor` varchar(11) NOT NULL default '0,0,0',
  `title1fontalign` enum('L','C','R') NOT NULL default 'C',
  `title1show` enum('1','0') NOT NULL default '1',
  `title2desc` varchar(50) NOT NULL default 'Report Generated %date%',
  `title2font` varchar(20) NOT NULL default 'Helvetica',
  `title2fontsize` int(3) NOT NULL default '10',
  `title2fontcolor` varchar(11) NOT NULL default '0,0,0',
  `title2fontalign` enum('L','C','R') NOT NULL default 'C',
  `title2show` enum('1','0') NOT NULL default '1',
  `filterfont` varchar(10) NOT NULL default 'Helvetica',
  `filterfontsize` int(3) NOT NULL default '8',
  `filterfontcolor` varchar(11) NOT NULL default '0,0,0',
  `filterfontalign` enum('L','C','R') NOT NULL default 'L',
  `datafont` varchar(10) NOT NULL default 'Helvetica',
  `datafontsize` int(3) NOT NULL default '10',
  `datafontcolor` varchar(10) NOT NULL default 'black',
  `datafontalign` enum('L','C','R') NOT NULL default 'L',
  `totalsfont` varchar(10) NOT NULL default 'Helvetica',
  `totalsfontsize` int(3) NOT NULL default '10',
  `totalsfontcolor` varchar(11) NOT NULL default '0,0,0',
  `totalsfontalign` enum('L','C','R') NOT NULL default 'L',
  `col1width` int(3) NOT NULL default '25',
  `col2width` int(3) NOT NULL default '25',
  `col3width` int(3) NOT NULL default '25',
  `col4width` int(3) NOT NULL default '25',
  `col5width` int(3) NOT NULL default '25',
  `col6width` int(3) NOT NULL default '25',
  `col7width` int(3) NOT NULL default '25',
  `col8width` int(3) NOT NULL default '25',
  `table1` varchar(25) NOT NULL default '',
  `table2` varchar(25) default NULL,
  `table2criteria` varchar(75) default NULL,
  `table3` varchar(25) default NULL,
  `table3criteria` varchar(75) default NULL,
  `table4` varchar(25) default NULL,
  `table4criteria` varchar(75) default NULL,
  `table5` VARCHAR(25) ,
  `table5criteria` VARCHAR(75) ,
  `table6` VARCHAR(25),
  `table6criteria` VARCHAR(75), 
  PRIMARY KEY  (`id`),
  KEY `name` (`reportname`,`groupname`)
) ENGINE=InnoDB ;

CREATE TABLE `reportfields` (
  `id` int(8) NOT NULL auto_increment,
  `reportid` int(5) NOT NULL default '0',
  `entrytype` varchar(15) NOT NULL default '',
  `seqnum` int(3) NOT NULL default '0',
  `fieldname` varchar(35) NOT NULL default '',
  `displaydesc` varchar(25) NOT NULL default '',
  `visible` enum('1','0') NOT NULL default '1',
  `columnbreak` enum('1','0') NOT NULL default '1',
  `params` text,
  PRIMARY KEY  (`id`),
  KEY `reportid` (`reportid`)
) ENGINE=Innodb;


DROP TABLE IF EXISTS `reportlinks`;
CREATE TABLE IF NOT EXISTS `reportlinks` (
  `table1` varchar(25) NOT NULL default '',
  `table2` varchar(25) NOT NULL default '',
  `equation` varchar(75) NOT NULL default ''
) ENGINE=InnoDB;

-- 
-- Dumping data for table `reportlinks`
-- 

INSERT INTO `reportlinks` VALUES ('accountgroups', 'accountsection', 'accountgroups.sectioninaccounts=accountsection.sectionid');
INSERT INTO `reportlinks` VALUES ('accountsection', 'accountgroups', 'accountsection.sectionid=accountgroups.sectioninaccounts');
INSERT INTO `reportlinks` VALUES ('bankaccounts', 'chartmaster', 'bankaccounts.accountcode=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster', 'bankaccounts', 'chartmaster.accountcode=bankaccounts.accountcode');
INSERT INTO `reportlinks` VALUES ('banktrans', 'systypes', 'banktrans.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes', 'banktrans', 'systypes.typeid=banktrans.type');
INSERT INTO `reportlinks` VALUES ('banktrans', 'bankaccounts', 'banktrans.bankact=bankaccounts.accountcode');
INSERT INTO `reportlinks` VALUES ('bankaccounts', 'banktrans', 'bankaccounts.accountcode=banktrans.bankact');
INSERT INTO `reportlinks` VALUES ('bom', 'stockmaster', 'bom.parent=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster', 'bom', 'stockmaster.stockid=bom.parent');
INSERT INTO `reportlinks` VALUES ('bom', 'stockmaster', 'bom.component=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster', 'bom', 'stockmaster.stockid=bom.component');
INSERT INTO `reportlinks` VALUES ('bom', 'workcentres', 'bom.workcentreadded=workcentres.code');
INSERT INTO `reportlinks` VALUES ('workcentres', 'bom', 'workcentres.code=bom.workcentreadded');
INSERT INTO `reportlinks` VALUES ('bom', 'locations', 'bom.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations', 'bom', 'locations.loccode=bom.loccode');
INSERT INTO `reportlinks` VALUES ('buckets', 'workcentres', 'buckets.workcentre=workcentres.code');
INSERT INTO `reportlinks` VALUES ('workcentres', 'buckets', 'workcentres.code=buckets.workcentre');
INSERT INTO `reportlinks` VALUES ('chartdetails', 'chartmaster', 'chartdetails.accountcode=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster', 'chartdetails', 'chartmaster.accountcode=chartdetails.accountcode');
INSERT INTO `reportlinks` VALUES ('chartdetails', 'periods', 'chartdetails.period=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods', 'chartdetails', 'periods.periodno=chartdetails.period');
INSERT INTO `reportlinks` VALUES ('chartmaster', 'accountgroups', 'chartmaster.group_=accountgroups.groupname');
INSERT INTO `reportlinks` VALUES ('accountgroups', 'chartmaster', 'accountgroups.groupname=chartmaster.group_');
INSERT INTO `reportlinks` VALUES ('contractbom', 'workcentres', 'contractbom.workcentreadded=workcentres.code');
INSERT INTO `reportlinks` VALUES ('workcentres', 'contractbom', 'workcentres.code=contractbom.workcentreadded');
INSERT INTO `reportlinks` VALUES ('contractbom', 'locations', 'contractbom.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations', 'contractbom', 'locations.loccode=contractbom.loccode');
INSERT INTO `reportlinks` VALUES ('contractbom', 'stockmaster', 'contractbom.component=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster', 'contractbom', 'stockmaster.stockid=contractbom.component');
INSERT INTO `reportlinks` VALUES ('contractreqts', 'contracts', 'contractreqts.contract=contracts.contractref');
INSERT INTO `reportlinks` VALUES ('contracts', 'contractreqts', 'contracts.contractref=contractreqts.contract');
INSERT INTO `reportlinks` VALUES ('contracts', 'custbranch', 'contracts.debtorno=custbranch.debtorno');
INSERT INTO `reportlinks` VALUES ('custbranch', 'contracts', 'custbranch.debtorno=contracts.debtorno');
INSERT INTO `reportlinks` VALUES ('contracts', 'stockcategory', 'contracts.branchcode=stockcategory.categoryid');
INSERT INTO `reportlinks` VALUES ('stockcategory', 'contracts', 'stockcategory.categoryid=contracts.branchcode');
INSERT INTO `reportlinks` VALUES ('contracts', 'salestypes', 'contracts.typeabbrev=salestypes.typeabbrev');
INSERT INTO `reportlinks` VALUES ('salestypes', 'contracts', 'salestypes.typeabbrev=contracts.typeabbrev');
INSERT INTO `reportlinks` VALUES ('custallocns', 'debtortrans', 'custallocns.transid_allocfrom=debtortrans.id');
INSERT INTO `reportlinks` VALUES ('debtortrans', 'custallocns', 'debtortrans.id=custallocns.transid_allocfrom');
INSERT INTO `reportlinks` VALUES ('custallocns', 'debtortrans', 'custallocns.transid_allocto=debtortrans.id');
INSERT INTO `reportlinks` VALUES ('debtortrans', 'custallocns', 'debtortrans.id=custallocns.transid_allocto');
INSERT INTO `reportlinks` VALUES ('custbranch', 'debtorsmaster', 'custbranch.debtorno=debtorsmaster.debtorno');
INSERT INTO `reportlinks` VALUES ('debtorsmaster', 'custbranch', 'debtorsmaster.debtorno=custbranch.debtorno');
INSERT INTO `reportlinks` VALUES ('custbranch', 'areas', 'custbranch.area=areas.areacode');
INSERT INTO `reportlinks` VALUES ('areas', 'custbranch', 'areas.areacode=custbranch.area');
INSERT INTO `reportlinks` VALUES ('custbranch', 'salesman', 'custbranch.salesman=salesman.salesmancode');
INSERT INTO `reportlinks` VALUES ('salesman', 'custbranch', 'salesman.salesmancode=custbranch.salesman');
INSERT INTO `reportlinks` VALUES ('custbranch', 'locations', 'custbranch.defaultlocation=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations', 'custbranch', 'locations.loccode=custbranch.defaultlocation');
INSERT INTO `reportlinks` VALUES ('custbranch', 'shippers', 'custbranch.defaultshipvia=shippers.shipper_id');
INSERT INTO `reportlinks` VALUES ('shippers', 'custbranch', 'shippers.shipper_id=custbranch.defaultshipvia');
INSERT INTO `reportlinks` VALUES ('debtorsmaster', 'holdreasons', 'debtorsmaster.holdreason=holdreasons.reasoncode');
INSERT INTO `reportlinks` VALUES ('holdreasons', 'debtorsmaster', 'holdreasons.reasoncode=debtorsmaster.holdreason');
INSERT INTO `reportlinks` VALUES ('debtorsmaster', 'currencies', 'debtorsmaster.currcode=currencies.currabrev');
INSERT INTO `reportlinks` VALUES ('currencies', 'debtorsmaster', 'currencies.currabrev=debtorsmaster.currcode');
INSERT INTO `reportlinks` VALUES ('debtorsmaster', 'paymentterms', 'debtorsmaster.paymentterms=paymentterms.termsindicator');
INSERT INTO `reportlinks` VALUES ('paymentterms', 'debtorsmaster', 'paymentterms.termsindicator=debtorsmaster.paymentterms');
INSERT INTO `reportlinks` VALUES ('debtorsmaster', 'salestypes', 'debtorsmaster.salestype=salestypes.typeabbrev');
INSERT INTO `reportlinks` VALUES ('salestypes', 'debtorsmaster', 'salestypes.typeabbrev=debtorsmaster.salestype');
INSERT INTO `reportlinks` VALUES ('debtortrans', 'custbranch', 'debtortrans.debtorno=custbranch.debtorno');
INSERT INTO `reportlinks` VALUES ('custbranch', 'debtortrans', 'custbranch.debtorno=debtortrans.debtorno');
INSERT INTO `reportlinks` VALUES ('debtortrans', 'systypes', 'debtortrans.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes', 'debtortrans', 'systypes.typeid=debtortrans.type');
INSERT INTO `reportlinks` VALUES ('debtortrans', 'periods', 'debtortrans.prd=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods', 'debtortrans', 'periods.periodno=debtortrans.prd');
INSERT INTO `reportlinks` VALUES ('debtortranstaxes', 'taxauthorities', 'debtortranstaxes.taxauthid=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities', 'debtortranstaxes', 'taxauthorities.taxid=debtortranstaxes.taxauthid');
INSERT INTO `reportlinks` VALUES ('debtortranstaxes', 'debtortrans', 'debtortranstaxes.debtortransid=debtortrans.id');
INSERT INTO `reportlinks` VALUES ('debtortrans', 'debtortranstaxes', 'debtortrans.id=debtortranstaxes.debtortransid');
INSERT INTO `reportlinks` VALUES ('discountmatrix', 'salestypes', 'discountmatrix.salestype=salestypes.typeabbrev');
INSERT INTO `reportlinks` VALUES ('salestypes', 'discountmatrix', 'salestypes.typeabbrev=discountmatrix.salestype');
INSERT INTO `reportlinks` VALUES ('freightcosts', 'locations', 'freightcosts.locationfrom=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations', 'freightcosts', 'locations.loccode=freightcosts.locationfrom');
INSERT INTO `reportlinks` VALUES ('freightcosts', 'shippers', 'freightcosts.shipperid=shippers.shipper_id');
INSERT INTO `reportlinks` VALUES ('shippers', 'freightcosts', 'shippers.shipper_id=freightcosts.shipperid');
INSERT INTO `reportlinks` VALUES ('gltrans', 'chartmaster', 'gltrans.account=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster', 'gltrans', 'chartmaster.accountcode=gltrans.account');
INSERT INTO `reportlinks` VALUES ('gltrans', 'systypes', 'gltrans.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes', 'gltrans', 'systypes.typeid=gltrans.type');
INSERT INTO `reportlinks` VALUES ('gltrans', 'periods', 'gltrans.periodno=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods', 'gltrans', 'periods.periodno=gltrans.periodno');
INSERT INTO `reportlinks` VALUES ('grns', 'suppliers', 'grns.supplierid=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers', 'grns', 'suppliers.supplierid=grns.supplierid');
INSERT INTO `reportlinks` VALUES ('grns', 'purchorderdetails', 'grns.podetailitem=purchorderdetails.podetailitem');
INSERT INTO `reportlinks` VALUES ('purchorderdetails', 'grns', 'purchorderdetails.podetailitem=grns.podetailitem');
INSERT INTO `reportlinks` VALUES ('locations', 'taxprovinces', 'locations.taxprovinceid=taxprovinces.taxprovinceid');
INSERT INTO `reportlinks` VALUES ('taxprovinces', 'locations', 'taxprovinces.taxprovinceid=locations.taxprovinceid');
INSERT INTO `reportlinks` VALUES ('locstock', 'locations', 'locstock.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations', 'locstock', 'locations.loccode=locstock.loccode');
INSERT INTO `reportlinks` VALUES ('locstock', 'stockmaster', 'locstock.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster', 'locstock', 'stockmaster.stockid=locstock.stockid');
INSERT INTO `reportlinks` VALUES ('loctransfers', 'locations', 'loctransfers.shiploc=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations', 'loctransfers', 'locations.loccode=loctransfers.shiploc');
INSERT INTO `reportlinks` VALUES ('loctransfers', 'locations', 'loctransfers.recloc=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations', 'loctransfers', 'locations.loccode=loctransfers.recloc');
INSERT INTO `reportlinks` VALUES ('loctransfers', 'stockmaster', 'loctransfers.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster', 'loctransfers', 'stockmaster.stockid=loctransfers.stockid');
INSERT INTO `reportlinks` VALUES ('orderdeliverydifferencesl', 'stockmaster', 'orderdeliverydifferenceslog.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster', 'orderdeliverydifferencesl', 'stockmaster.stockid=orderdeliverydifferenceslog.stockid');
INSERT INTO `reportlinks` VALUES ('orderdeliverydifferencesl', 'custbranch', 'orderdeliverydifferenceslog.debtorno=custbranch.debtorno');
INSERT INTO `reportlinks` VALUES ('custbranch', 'orderdeliverydifferencesl', 'custbranch.debtorno=orderdeliverydifferenceslog.debtorno');
INSERT INTO `reportlinks` VALUES ('orderdeliverydifferencesl', 'salesorders', 'orderdeliverydifferenceslog.branchcode=salesorders.orderno');
INSERT INTO `reportlinks` VALUES ('salesorders', 'orderdeliverydifferencesl', 'salesorders.orderno=orderdeliverydifferenceslog.branchcode');
INSERT INTO `reportlinks` VALUES ('prices', 'stockmaster', 'prices.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster', 'prices', 'stockmaster.stockid=prices.stockid');
INSERT INTO `reportlinks` VALUES ('prices', 'currencies', 'prices.currabrev=currencies.currabrev');
INSERT INTO `reportlinks` VALUES ('currencies', 'prices', 'currencies.currabrev=prices.currabrev');
INSERT INTO `reportlinks` VALUES ('prices', 'salestypes', 'prices.typeabbrev=salestypes.typeabbrev');
INSERT INTO `reportlinks` VALUES ('salestypes', 'prices', 'salestypes.typeabbrev=prices.typeabbrev');
INSERT INTO `reportlinks` VALUES ('purchdata', 'stockmaster', 'purchdata.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster', 'purchdata', 'stockmaster.stockid=purchdata.stockid');
INSERT INTO `reportlinks` VALUES ('purchdata', 'suppliers', 'purchdata.supplierno=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers', 'purchdata', 'suppliers.supplierid=purchdata.supplierno');
INSERT INTO `reportlinks` VALUES ('purchorderdetails', 'purchorders', 'purchorderdetails.orderno=purchorders.orderno');
INSERT INTO `reportlinks` VALUES ('purchorders', 'purchorderdetails', 'purchorders.orderno=purchorderdetails.orderno');
INSERT INTO `reportlinks` VALUES ('purchorders', 'suppliers', 'purchorders.supplierno=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers', 'purchorders', 'suppliers.supplierid=purchorders.supplierno');
INSERT INTO `reportlinks` VALUES ('purchorders', 'locations', 'purchorders.intostocklocation=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations', 'purchorders', 'locations.loccode=purchorders.intostocklocation');
INSERT INTO `reportlinks` VALUES ('recurringsalesorders', 'custbranch', 'recurringsalesorders.branchcode=custbranch.branchcode');
INSERT INTO `reportlinks` VALUES ('custbranch', 'recurringsalesorders', 'custbranch.branchcode=recurringsalesorders.branchcode');
INSERT INTO `reportlinks` VALUES ('recurrsalesorderdetails', 'recurringsalesorders', 'recurrsalesorderdetails.recurrorderno=recurringsalesorders.recurrorderno');
INSERT INTO `reportlinks` VALUES ('recurringsalesorders', 'recurrsalesorderdetails', 'recurringsalesorders.recurrorderno=recurrsalesorderdetails.recurrorderno');
INSERT INTO `reportlinks` VALUES ('recurrsalesorderdetails', 'stockmaster', 'recurrsalesorderdetails.stkcode=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster', 'recurrsalesorderdetails', 'stockmaster.stockid=recurrsalesorderdetails.stkcode');
INSERT INTO `reportlinks` VALUES ('reportcolumns', 'reportheaders', 'reportcolumns.reportid=reportheaders.reportid');
INSERT INTO `reportlinks` VALUES ('reportheaders', 'reportcolumns', 'reportheaders.reportid=reportcolumns.reportid');
INSERT INTO `reportlinks` VALUES ('salesanalysis', 'periods', 'salesanalysis.periodno=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods', 'salesanalysis', 'periods.periodno=salesanalysis.periodno');
INSERT INTO `reportlinks` VALUES ('salescatprod', 'stockmaster', 'salescatprod.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster', 'salescatprod', 'stockmaster.stockid=salescatprod.stockid');
INSERT INTO `reportlinks` VALUES ('salescatprod', 'salescat', 'salescatprod.salescatid=salescat.salescatid');
INSERT INTO `reportlinks` VALUES ('salescat', 'salescatprod', 'salescat.salescatid=salescatprod.salescatid');
INSERT INTO `reportlinks` VALUES ('salesorderdetails', 'salesorders', 'salesorderdetails.orderno=salesorders.orderno');
INSERT INTO `reportlinks` VALUES ('salesorders', 'salesorderdetails', 'salesorders.orderno=salesorderdetails.orderno');
INSERT INTO `reportlinks` VALUES ('salesorderdetails', 'stockmaster', 'salesorderdetails.stkcode=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster', 'salesorderdetails', 'stockmaster.stockid=salesorderdetails.stkcode');
INSERT INTO `reportlinks` VALUES ('salesorders', 'custbranch', 'salesorders.branchcode=custbranch.branchcode');
INSERT INTO `reportlinks` VALUES ('custbranch', 'salesorders', 'custbranch.branchcode=salesorders.branchcode');
INSERT INTO `reportlinks` VALUES ('salesorders', 'shippers', 'salesorders.debtorno=shippers.shipper_id');
INSERT INTO `reportlinks` VALUES ('shippers', 'salesorders', 'shippers.shipper_id=salesorders.debtorno');
INSERT INTO `reportlinks` VALUES ('salesorders', 'locations', 'salesorders.fromstkloc=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations', 'salesorders', 'locations.loccode=salesorders.fromstkloc');
INSERT INTO `reportlinks` VALUES ('securitygroups', 'securityroles', 'securitygroups.secroleid=securityroles.secroleid');
INSERT INTO `reportlinks` VALUES ('securityroles', 'securitygroups', 'securityroles.secroleid=securitygroups.secroleid');
INSERT INTO `reportlinks` VALUES ('securitygroups', 'securitytokens', 'securitygroups.tokenid=securitytokens.tokenid');
INSERT INTO `reportlinks` VALUES ('securitytokens', 'securitygroups', 'securitytokens.tokenid=securitygroups.tokenid');
INSERT INTO `reportlinks` VALUES ('shipmentcharges', 'shipments', 'shipmentcharges.shiptref=shipments.shiptref');
INSERT INTO `reportlinks` VALUES ('shipments', 'shipmentcharges', 'shipments.shiptref=shipmentcharges.shiptref');
INSERT INTO `reportlinks` VALUES ('shipmentcharges', 'systypes', 'shipmentcharges.transtype=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes', 'shipmentcharges', 'systypes.typeid=shipmentcharges.transtype');
INSERT INTO `reportlinks` VALUES ('shipments', 'suppliers', 'shipments.supplierid=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers', 'shipments', 'suppliers.supplierid=shipments.supplierid');
INSERT INTO `reportlinks` VALUES ('stockcheckfreeze', 'stockmaster', 'stockcheckfreeze.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster', 'stockcheckfreeze', 'stockmaster.stockid=stockcheckfreeze.stockid');
INSERT INTO `reportlinks` VALUES ('stockcheckfreeze', 'locations', 'stockcheckfreeze.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations', 'stockcheckfreeze', 'locations.loccode=stockcheckfreeze.loccode');
INSERT INTO `reportlinks` VALUES ('stockcounts', 'stockmaster', 'stockcounts.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster', 'stockcounts', 'stockmaster.stockid=stockcounts.stockid');
INSERT INTO `reportlinks` VALUES ('stockcounts', 'locations', 'stockcounts.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations', 'stockcounts', 'locations.loccode=stockcounts.loccode');
INSERT INTO `reportlinks` VALUES ('stockmaster', 'stockcategory', 'stockmaster.categoryid=stockcategory.categoryid');
INSERT INTO `reportlinks` VALUES ('stockcategory', 'stockmaster', 'stockcategory.categoryid=stockmaster.categoryid');
INSERT INTO `reportlinks` VALUES ('stockmaster', 'taxcategories', 'stockmaster.taxcatid=taxcategories.taxcatid');
INSERT INTO `reportlinks` VALUES ('taxcategories', 'stockmaster', 'taxcategories.taxcatid=stockmaster.taxcatid');
INSERT INTO `reportlinks` VALUES ('stockmoves', 'stockmaster', 'stockmoves.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster', 'stockmoves', 'stockmaster.stockid=stockmoves.stockid');
INSERT INTO `reportlinks` VALUES ('stockmoves', 'systypes', 'stockmoves.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes', 'stockmoves', 'systypes.typeid=stockmoves.type');
INSERT INTO `reportlinks` VALUES ('stockmoves', 'locations', 'stockmoves.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations', 'stockmoves', 'locations.loccode=stockmoves.loccode');
INSERT INTO `reportlinks` VALUES ('stockmoves', 'periods', 'stockmoves.prd=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods', 'stockmoves', 'periods.periodno=stockmoves.prd');
INSERT INTO `reportlinks` VALUES ('stockmovestaxes', 'taxauthorities', 'stockmovestaxes.taxauthid=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities', 'stockmovestaxes', 'taxauthorities.taxid=stockmovestaxes.taxauthid');
INSERT INTO `reportlinks` VALUES ('stockserialitems', 'stockmaster', 'stockserialitems.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster', 'stockserialitems', 'stockmaster.stockid=stockserialitems.stockid');
INSERT INTO `reportlinks` VALUES ('stockserialitems', 'locations', 'stockserialitems.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations', 'stockserialitems', 'locations.loccode=stockserialitems.loccode');
INSERT INTO `reportlinks` VALUES ('stockserialmoves', 'stockmoves', 'stockserialmoves.stockmoveno=stockmoves.stkmoveno');
INSERT INTO `reportlinks` VALUES ('stockmoves', 'stockserialmoves', 'stockmoves.stkmoveno=stockserialmoves.stockmoveno');
INSERT INTO `reportlinks` VALUES ('stockserialmoves', 'stockserialitems', 'stockserialmoves.stockid=stockserialitems.stockid');
INSERT INTO `reportlinks` VALUES ('stockserialitems', 'stockserialmoves', 'stockserialitems.stockid=stockserialmoves.stockid');
INSERT INTO `reportlinks` VALUES ('suppallocs', 'supptrans', 'suppallocs.transid_allocfrom=supptrans.id');
INSERT INTO `reportlinks` VALUES ('supptrans', 'suppallocs', 'supptrans.id=suppallocs.transid_allocfrom');
INSERT INTO `reportlinks` VALUES ('suppallocs', 'supptrans', 'suppallocs.transid_allocto=supptrans.id');
INSERT INTO `reportlinks` VALUES ('supptrans', 'suppallocs', 'supptrans.id=suppallocs.transid_allocto');
INSERT INTO `reportlinks` VALUES ('suppliercontacts', 'suppliers', 'suppliercontacts.supplierid=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers', 'suppliercontacts', 'suppliers.supplierid=suppliercontacts.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers', 'currencies', 'suppliers.currcode=currencies.currabrev');
INSERT INTO `reportlinks` VALUES ('currencies', 'suppliers', 'currencies.currabrev=suppliers.currcode');
INSERT INTO `reportlinks` VALUES ('suppliers', 'paymentterms', 'suppliers.paymentterms=paymentterms.termsindicator');
INSERT INTO `reportlinks` VALUES ('paymentterms', 'suppliers', 'paymentterms.termsindicator=suppliers.paymentterms');
INSERT INTO `reportlinks` VALUES ('suppliers', 'taxgroups', 'suppliers.taxgroupid=taxgroups.taxgroupid');
INSERT INTO `reportlinks` VALUES ('taxgroups', 'suppliers', 'taxgroups.taxgroupid=suppliers.taxgroupid');
INSERT INTO `reportlinks` VALUES ('supptrans', 'systypes', 'supptrans.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes', 'supptrans', 'systypes.typeid=supptrans.type');
INSERT INTO `reportlinks` VALUES ('supptrans', 'suppliers', 'supptrans.supplierno=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers', 'supptrans', 'suppliers.supplierid=supptrans.supplierno');
INSERT INTO `reportlinks` VALUES ('supptranstaxes', 'taxauthorities', 'supptranstaxes.taxauthid=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities', 'supptranstaxes', 'taxauthorities.taxid=supptranstaxes.taxauthid');
INSERT INTO `reportlinks` VALUES ('supptranstaxes', 'supptrans', 'supptranstaxes.supptransid=supptrans.id');
INSERT INTO `reportlinks` VALUES ('supptrans', 'supptranstaxes', 'supptrans.id=supptranstaxes.supptransid');
INSERT INTO `reportlinks` VALUES ('taxauthorities', 'chartmaster', 'taxauthorities.taxglcode=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster', 'taxauthorities', 'chartmaster.accountcode=taxauthorities.taxglcode');
INSERT INTO `reportlinks` VALUES ('taxauthorities', 'chartmaster', 'taxauthorities.purchtaxglaccount=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster', 'taxauthorities', 'chartmaster.accountcode=taxauthorities.purchtaxglaccount');
INSERT INTO `reportlinks` VALUES ('taxauthrates', 'taxauthorities', 'taxauthrates.taxauthority=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities', 'taxauthrates', 'taxauthorities.taxid=taxauthrates.taxauthority');
INSERT INTO `reportlinks` VALUES ('taxauthrates', 'taxcategories', 'taxauthrates.taxcatid=taxcategories.taxcatid');
INSERT INTO `reportlinks` VALUES ('taxcategories', 'taxauthrates', 'taxcategories.taxcatid=taxauthrates.taxcatid');
INSERT INTO `reportlinks` VALUES ('taxauthrates', 'taxprovinces', 'taxauthrates.dispatchtaxprovince=taxprovinces.taxprovinceid');
INSERT INTO `reportlinks` VALUES ('taxprovinces', 'taxauthrates', 'taxprovinces.taxprovinceid=taxauthrates.dispatchtaxprovince');
INSERT INTO `reportlinks` VALUES ('taxgrouptaxes', 'taxgroups', 'taxgrouptaxes.taxgroupid=taxgroups.taxgroupid');
INSERT INTO `reportlinks` VALUES ('taxgroups', 'taxgrouptaxes', 'taxgroups.taxgroupid=taxgrouptaxes.taxgroupid');
INSERT INTO `reportlinks` VALUES ('taxgrouptaxes', 'taxauthorities', 'taxgrouptaxes.taxauthid=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities', 'taxgrouptaxes', 'taxauthorities.taxid=taxgrouptaxes.taxauthid');
INSERT INTO `reportlinks` VALUES ('workcentres', 'locations', 'workcentres.location=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations', 'workcentres', 'locations.loccode=workcentres.location');
INSERT INTO `reportlinks` VALUES ('worksorders', 'locations', 'worksorders.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations', 'worksorders', 'locations.loccode=worksorders.loccode');
INSERT INTO `reportlinks` VALUES ('worksorders', 'stockmaster', 'worksorders.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster', 'worksorders', 'stockmaster.stockid=worksorders.stockid');
INSERT INTO `reportlinks` VALUES ('www_users', 'locations', 'www_users.defaultlocation=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations', 'www_users', 'locations.loccode=www_users.defaultlocation');
INSERT INTO config ( confname , confvalue ) VALUES('WikiApp','Disabled');
INSERT INTO config ( confname , confvalue ) VALUES('WikiPath','wiki');
INSERT INTO config ( confname , confvalue ) VALUES('ProhibitJournalsToControlAccounts','0');
INSERT INTO config ( confname , confvalue ) VALUES ('InvoicePortraitFormat', '0');
ALTER TABLE stockserialitems ADD INDEX (serialno);
ALTER TABLE stockserialmoves ADD INDEX (serialno);
INSERT INTO taxcategories (taxcatname) VALUES ('Freight');
INSERT INTO config ( confname , confvalue ) VALUES ('AllowOrderLineItemNarrative', '1');
INSERT INTO config ( confname , confvalue ) VALUES ('vtiger_integration', '0');
ALTER TABLE custbranch DROP INDEX BranchCode;
INSERT INTO `config` ( `confname` , `confvalue` ) VALUES ('ProhibitPostingsBefore', '2006-01-01');
INSERT INTO `config` ( `confname` , `confvalue` ) VALUES ('WeightedAverageCosting', '1');
ALTER TABLE grns ADD COLUMN stdcostunit double NOT NULL DEFAULT 0;
ALTER TABLE `stockcheckfreeze` DROP PRIMARY KEY , ADD PRIMARY KEY ( `stockid` , `loccode` );
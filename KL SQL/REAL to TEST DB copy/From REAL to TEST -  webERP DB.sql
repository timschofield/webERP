SET FOREIGN_KEY_CHECKS=0;

DROP TRIGGER IF EXISTS test_erp.currencies_creation_timestamp;
DROP TRIGGER IF EXISTS test_erp.currencies_update_timestamp;

DROP TRIGGER IF EXISTS test_erp.gltrans_after_delete;
DROP TRIGGER IF EXISTS test_erp.gltrans_after_insert;
DROP TRIGGER IF EXISTS test_erp.gltrans_after_update;

DROP TRIGGER IF EXISTS test_erp.klretailcustomers_creation_timestamp;
DROP TRIGGER IF EXISTS test_erp.klretailcustomers_update_timestamp;

DROP TRIGGER IF EXISTS test_erp.klstockmarketplaces_creation_timestamp;
DROP TRIGGER IF EXISTS test_erp.klstockmarketplaces_update_timestamp;

DROP TRIGGER IF EXISTS test_erp.locstock_creation_timestamp;
DROP TRIGGER IF EXISTS test_erp.locstock_update_timestamp;

DROP TRIGGER IF EXISTS test_erp.prices_creation_timestamp;
DROP TRIGGER IF EXISTS test_erp.prices_update_timestamp;

DROP TRIGGER IF EXISTS test_erp.relateditems_creation_timestamp;
DROP TRIGGER IF EXISTS test_erp.relateditems_update_timestamp;

DROP TRIGGER IF EXISTS test_erp.salariescalculated_creation_timestamp;
DROP TRIGGER IF EXISTS test_erp.salariescalculated_update_timestamp;

DROP TRIGGER IF EXISTS test_erp.salescat_creation_timestamp;
DROP TRIGGER IF EXISTS test_erp.salescat_update_timestamp;

DROP TRIGGER IF EXISTS test_erp.salescatprod_creation_timestamp;
DROP TRIGGER IF EXISTS test_erp.salescatprod_update_timestamp;

DROP TRIGGER IF EXISTS test_erp.stockdescriptiontranslations_creation_timestamp;
DROP TRIGGER IF EXISTS test_erp.stockdescriptiontranslations_update_timestamp;

DROP TRIGGER IF EXISTS test_erp.stockmaster_creation_timestamp;
DROP TRIGGER IF EXISTS test_erp.stockmaster_update_timestamp;

TRUNCATE test_erp.`accountgroups`;
INSERT INTO test_erp.accountgroups SELECT * FROM kl_erp.accountgroups;

TRUNCATE test_erp.`accountsection`;
INSERT INTO test_erp.accountsection SELECT * FROM kl_erp.accountsection;

TRUNCATE test_erp.`areas`;
INSERT INTO test_erp.areas SELECT * FROM kl_erp.areas;

TRUNCATE test_erp.`assetmanager`;
INSERT INTO test_erp.assetmanager SELECT * FROM kl_erp.assetmanager;

TRUNCATE test_erp.`auditscripts`;
/* INSERT INTO test_erp.auditscripts SELECT * FROM kl_erp.auditscripts; */

TRUNCATE test_erp.`audittrail`;
/* INSERT INTO test_erp.audittrail SELECT * FROM kl_erp.audittrail; */

TRUNCATE test_erp.`bankaccounts`;
INSERT INTO test_erp.bankaccounts SELECT * FROM kl_erp.bankaccounts;

TRUNCATE test_erp.`bankaccountusers`;
INSERT INTO test_erp.bankaccountusers SELECT * FROM kl_erp.bankaccountusers;

TRUNCATE test_erp.`banktrans`;
INSERT INTO test_erp.banktrans SELECT * FROM kl_erp.banktrans; 

TRUNCATE test_erp.`bom`;
INSERT INTO test_erp.bom SELECT * FROM kl_erp.bom; 

TRUNCATE test_erp.`buckets`;
INSERT INTO test_erp.buckets SELECT * FROM kl_erp.buckets;

TRUNCATE test_erp.`chartmaster`;
INSERT INTO test_erp.chartmaster SELECT * FROM kl_erp.chartmaster;

TRUNCATE test_erp.`chartmasterADU`;
INSERT INTO test_erp.chartmasterADU SELECT * FROM kl_erp.chartmasterADU;

TRUNCATE test_erp.`chartmasterBB`;
INSERT INTO test_erp.chartmasterBB SELECT * FROM kl_erp.chartmasterBB;

TRUNCATE test_erp.`chartmasterIK`;
INSERT INTO test_erp.chartmasterIK SELECT * FROM kl_erp.chartmasterIK;

TRUNCATE test_erp.`chartmasterPI`;
INSERT INTO test_erp.chartmasterPI SELECT * FROM kl_erp.chartmasterPI;

TRUNCATE test_erp.`chartmasterSMH`;
INSERT INTO test_erp.chartmasterSMH SELECT * FROM kl_erp.chartmasterSMH;

TRUNCATE test_erp.`cogsglpostings`;
INSERT INTO test_erp.cogsglpostings SELECT * FROM kl_erp.cogsglpostings;

TRUNCATE test_erp.`companies`;
INSERT INTO test_erp.companies SELECT * FROM kl_erp.companies;

TRUNCATE test_erp.`config`;
INSERT INTO test_erp.config SELECT * FROM kl_erp.config;

TRUNCATE test_erp.`contractbom`;
INSERT INTO test_erp.contractbom SELECT * FROM kl_erp.contractbom;

TRUNCATE test_erp.`contractcharges`;
INSERT INTO test_erp.contractcharges SELECT * FROM kl_erp.contractcharges;

TRUNCATE test_erp.`contractreqts`;
INSERT INTO test_erp.contractreqts SELECT * FROM kl_erp.contractreqts;

TRUNCATE test_erp.`contracts`;
INSERT INTO test_erp.contracts SELECT * FROM kl_erp.contracts;

TRUNCATE test_erp.`currencies`;
INSERT INTO test_erp.currencies SELECT * FROM kl_erp.currencies;

TRUNCATE test_erp.`custallocns`;
INSERT INTO test_erp.custallocns SELECT * FROM kl_erp.custallocns; 

TRUNCATE test_erp.`custbranch`;
INSERT INTO test_erp.custbranch SELECT * FROM kl_erp.custbranch;

TRUNCATE test_erp.`custcontacts`;
INSERT INTO test_erp.custcontacts SELECT * FROM kl_erp.custcontacts;

TRUNCATE test_erp.`custitem`;
INSERT INTO test_erp.custitem SELECT * FROM kl_erp.custitem;

TRUNCATE test_erp.`custnotes`;
INSERT INTO test_erp.custnotes SELECT * FROM kl_erp.custnotes;

TRUNCATE test_erp.`dashboard_scripts`;
INSERT INTO test_erp.dashboard_scripts SELECT * FROM kl_erp.dashboard_scripts;

TRUNCATE test_erp.`dashboard_users`;
INSERT INTO test_erp.dashboard_users SELECT * FROM kl_erp.dashboard_users;

TRUNCATE test_erp.`debtorsmaster`;
INSERT INTO test_erp.debtorsmaster SELECT * FROM kl_erp.debtorsmaster;

TRUNCATE test_erp.`debtortrans`;
/* Special insert to prevent error when copying calculated fields*/
INSERT INTO test_erp.debtortrans (id, transno, type, debtorno, branchcode, trandate, inputdate, prd, settled, reference, tpe, order_, rate, ovamount, ovgst, ovfreight, ovdiscount, diffonexch, alloc, invtext, shipvia, edisent, consignment, packages, salesperson)
SELECT id, transno, type, debtorno, branchcode, trandate, inputdate, prd, settled, reference, tpe, order_, rate, ovamount, ovgst, ovfreight, ovdiscount, diffonexch, alloc, invtext, shipvia, edisent, consignment, packages, salesperson
FROM kl_erp.debtortrans; 

TRUNCATE test_erp.`debtortranstaxes`;
INSERT INTO test_erp.debtortranstaxes SELECT * FROM kl_erp.debtortranstaxes;

TRUNCATE test_erp.`debtortype`;
INSERT INTO test_erp.debtortype SELECT * FROM kl_erp.debtortype;

TRUNCATE test_erp.`debtortypenotes`;
INSERT INTO test_erp.debtortypenotes SELECT * FROM kl_erp.debtortypenotes;

TRUNCATE test_erp.`deliverynotes`;
INSERT INTO test_erp.deliverynotes SELECT * FROM kl_erp.deliverynotes;

TRUNCATE test_erp.`departments`;
INSERT INTO test_erp.departments SELECT * FROM kl_erp.departments;

TRUNCATE test_erp.`discountmatrix`;
INSERT INTO test_erp.discountmatrix SELECT * FROM kl_erp.discountmatrix;

TRUNCATE test_erp.`ediitemmapping`;
INSERT INTO test_erp.ediitemmapping SELECT * FROM kl_erp.ediitemmapping;

TRUNCATE test_erp.`edimessageformat`;
INSERT INTO test_erp.edimessageformat SELECT * FROM kl_erp.edimessageformat;

TRUNCATE test_erp.`edi_orders_segs`;
INSERT INTO test_erp.edi_orders_segs SELECT * FROM kl_erp.edi_orders_segs;

TRUNCATE test_erp.`ediitemmapping`;
INSERT INTO test_erp.ediitemmapping SELECT * FROM kl_erp.ediitemmapping;

TRUNCATE test_erp.`emailsettings`;
INSERT INTO test_erp.emailsettings SELECT * FROM kl_erp.emailsettings;

TRUNCATE test_erp.`employees`;
INSERT INTO test_erp.employees SELECT * FROM kl_erp.employees;

TRUNCATE test_erp.`factorcompanies`;
INSERT INTO test_erp.factorcompanies SELECT * FROM kl_erp.factorcompanies;

TRUNCATE test_erp.`favourites`;
INSERT INTO test_erp.favourites SELECT * FROM kl_erp.favourites;

TRUNCATE test_erp.`fixedassetcategories`;
INSERT INTO test_erp.fixedassetcategories SELECT * FROM kl_erp.fixedassetcategories;

TRUNCATE test_erp.`fixedassetlocations`;
INSERT INTO test_erp.fixedassetlocations SELECT * FROM kl_erp.fixedassetlocations;

TRUNCATE test_erp.`fixedassets`;
INSERT INTO test_erp.fixedassets SELECT * FROM kl_erp.fixedassets;

TRUNCATE test_erp.`fixedassettasks`;
INSERT INTO test_erp.fixedassettasks SELECT * FROM kl_erp.fixedassettasks;

TRUNCATE test_erp.`fixedassettrans`;
INSERT INTO test_erp.fixedassettrans SELECT * FROM kl_erp.fixedassettrans WHERE transdate >= '2025-01-01';

TRUNCATE test_erp.`freightcosts`;
INSERT INTO test_erp.freightcosts SELECT * FROM kl_erp.freightcosts;

TRUNCATE test_erp.`geocode_param`;
INSERT INTO test_erp.geocode_param SELECT * FROM kl_erp.geocode_param;

TRUNCATE test_erp.`glaccountusers`;
INSERT INTO test_erp.glaccountusers SELECT * FROM kl_erp.glaccountusers;

TRUNCATE test_erp.`gltags`;
INSERT INTO test_erp.gltags SELECT * FROM kl_erp.gltags;

TRUNCATE test_erp.`gltotals`;
INSERT INTO test_erp.gltotals SELECT * FROM kl_erp.gltotals;

TRUNCATE test_erp.`gltrans`;
INSERT INTO test_erp.gltrans SELECT * FROM kl_erp.gltrans WHERE periodno <= 30;
INSERT INTO test_erp.gltrans SELECT * FROM kl_erp.gltrans WHERE periodno > 30 AND periodno <= 60;
INSERT INTO test_erp.gltrans SELECT * FROM kl_erp.gltrans WHERE periodno > 60 AND periodno <= 80;
INSERT INTO test_erp.gltrans SELECT * FROM kl_erp.gltrans WHERE periodno > 80 AND periodno <= 90;
INSERT INTO test_erp.gltrans SELECT * FROM kl_erp.gltrans WHERE periodno > 90 AND periodno <= 100;
INSERT INTO test_erp.gltrans SELECT * FROM kl_erp.gltrans WHERE periodno > 100 AND periodno <= 110;
INSERT INTO test_erp.gltrans SELECT * FROM kl_erp.gltrans WHERE periodno > 110 AND periodno <= 120;
INSERT INTO test_erp.gltrans SELECT * FROM kl_erp.gltrans WHERE periodno > 120 AND periodno <= 130;
INSERT INTO test_erp.gltrans SELECT * FROM kl_erp.gltrans WHERE periodno > 130 AND periodno <= 140;
INSERT INTO test_erp.gltrans SELECT * FROM kl_erp.gltrans WHERE periodno > 140 AND periodno <= 150;
INSERT INTO test_erp.gltrans SELECT * FROM kl_erp.gltrans WHERE periodno > 150 AND periodno <= 160; 
INSERT INTO test_erp.gltrans SELECT * FROM kl_erp.gltrans WHERE periodno > 160 AND periodno <= 170;
INSERT INTO test_erp.gltrans SELECT * FROM kl_erp.gltrans WHERE periodno > 170 AND periodno <= 180;
INSERT INTO test_erp.gltrans SELECT * FROM kl_erp.gltrans WHERE periodno > 180 AND periodno <= 190;
INSERT INTO test_erp.gltrans SELECT * FROM kl_erp.gltrans WHERE periodno > 190 AND periodno <= 200;
INSERT INTO test_erp.gltrans SELECT * FROM kl_erp.gltrans WHERE periodno > 200 AND periodno <= 210;
INSERT INTO test_erp.gltrans SELECT * FROM kl_erp.gltrans WHERE periodno > 210 AND periodno <= 220;
INSERT INTO test_erp.gltrans SELECT * FROM kl_erp.gltrans WHERE periodno > 220;

TRUNCATE test_erp.`grns`;
INSERT INTO test_erp.grns SELECT * FROM kl_erp.grns;

TRUNCATE test_erp.`holdreasons`;
INSERT INTO test_erp.holdreasons SELECT * FROM kl_erp.holdreasons;

TRUNCATE test_erp.`internalstockcatrole`;
INSERT INTO test_erp.internalstockcatrole SELECT * FROM kl_erp.internalstockcatrole;

TRUNCATE test_erp.`jnltmplheader`;
INSERT INTO test_erp.jnltmplheader SELECT * FROM kl_erp.jnltmplheader;

TRUNCATE test_erp.`jnltmpldetails`;
INSERT INTO test_erp.jnltmpldetails SELECT * FROM kl_erp.jnltmpldetails;

TRUNCATE test_erp.`kladjustrl`;
INSERT INTO test_erp.kladjustrl SELECT * FROM kl_erp.kladjustrl;

TRUNCATE test_erp.`klarchivedtables`;
INSERT INTO test_erp.klarchivedtables SELECT * FROM kl_erp.klarchivedtables;

TRUNCATE test_erp.`klchangeprice`;
INSERT INTO test_erp.klchangeprice SELECT * FROM kl_erp.klchangeprice;

TRUNCATE test_erp.`kladjustrl`;
INSERT INTO test_erp.kladjustrl SELECT * FROM kl_erp.kladjustrl; 

TRUNCATE test_erp.`klconsignment`;
INSERT INTO test_erp.klconsignment SELECT * FROM kl_erp.klconsignment; 

TRUNCATE test_erp.`klfreeexchanges`;
/*INSERT INTO test_erp.klfreeexchanges SELECT * FROM kl_erp.klfreeexchanges;*/

TRUNCATE test_erp.`klkpi`;
/*INSERT INTO test_erp.klkpi SELECT * FROM kl_erp.klkpi;*/

TRUNCATE test_erp.`klkpidescriptions`;
INSERT INTO test_erp.klkpidescriptions SELECT * FROM kl_erp.klkpidescriptions;

TRUNCATE test_erp.`klmaintenancetasks`;
INSERT INTO test_erp.klmaintenancetasks SELECT * FROM kl_erp.klmaintenancetasks;

TRUNCATE test_erp.`klmaintenancetaskupdates`;
INSERT INTO test_erp.klmaintenancetaskupdates SELECT * FROM kl_erp.klmaintenancetaskupdates;

TRUNCATE test_erp.`klmaintenancetypes`;
INSERT INTO test_erp.klmaintenancetypes SELECT * FROM kl_erp.klmaintenancetypes;

TRUNCATE test_erp.`klmovetodiscount20`;
INSERT INTO test_erp.klmovetodiscount20 SELECT * FROM kl_erp.klmovetodiscount20;

TRUNCATE test_erp.`klmovetodiscount50`;
INSERT INTO test_erp.klmovetodiscount50 SELECT * FROM kl_erp.klmovetodiscount50;

TRUNCATE test_erp.`klmovetodiscount80`;
INSERT INTO test_erp.klmovetodiscount80 SELECT * FROM kl_erp.klmovetodiscount80;

TRUNCATE test_erp.`klonlinepartners`;
INSERT INTO test_erp.klonlinepartners SELECT * FROM kl_erp.klonlinepartners;

TRUNCATE test_erp.`klpostatus`;
INSERT INTO test_erp.klpostatus SELECT * FROM kl_erp.klpostatus;

TRUNCATE test_erp.`klretailcustomers`;
/* INSERT INTO test_erp.klretailcustomers SELECT * FROM kl_erp.klretailcustomers; */

TRUNCATE test_erp.`klretailpartners`;
INSERT INTO test_erp.klretailpartners SELECT * FROM kl_erp.klretailpartners;

TRUNCATE test_erp.`klrevisedemaildomains`;
INSERT INTO test_erp.klrevisedemaildomains SELECT * FROM kl_erp.klrevisedemaildomains;

TRUNCATE test_erp.`klsalesperformance`;
INSERT INTO test_erp.klsalesperformance SELECT * FROM kl_erp.klsalesperformance;

TRUNCATE test_erp.`klservicetypes`;
INSERT INTO test_erp.klservicetypes SELECT * FROM kl_erp.klservicetypes;

TRUNCATE test_erp.`klstockmarketplaces`;
INSERT INTO test_erp.klstockmarketplaces SELECT * FROM kl_erp.klstockmarketplaces;

TRUNCATE test_erp.`labelfields`;
INSERT INTO test_erp.labelfields SELECT * FROM kl_erp.labelfields;

TRUNCATE test_erp.`labels`;
INSERT INTO test_erp.labels SELECT * FROM kl_erp.labels;

INSERT INTO test_erp.lastcostrollup SELECT * FROM kl_erp.lastcostrollup;
TRUNCATE test_erp.`lastcostrollup`;

INSERT INTO test_erp.levels SELECT * FROM kl_erp.levels;
TRUNCATE test_erp.`levels`;

TRUNCATE test_erp.`locations`;
INSERT INTO test_erp.locations SELECT * FROM kl_erp.locations;

TRUNCATE test_erp.`locationtypes`;
INSERT INTO test_erp.locationtypes SELECT * FROM kl_erp.locationtypes;

TRUNCATE test_erp.`locationusers`;
INSERT INTO test_erp.locationusers SELECT * FROM kl_erp.locationusers;

TRUNCATE test_erp.`locationzones`;
INSERT INTO test_erp.locationzones SELECT * FROM kl_erp.locationzones;

TRUNCATE test_erp.`locstock`;
INSERT INTO test_erp.locstock SELECT * FROM kl_erp.locstock;

TRUNCATE test_erp.`loctransfercancellations`;
INSERT INTO test_erp.loctransfercancellations SELECT * FROM kl_erp.loctransfercancellations;

TRUNCATE test_erp.`loctransfers`;
/*INSERT INTO test_erp.loctransfers SELECT * FROM kl_erp.loctransfers WHERE reference >= 200000;*/
/* Special insert to prevent error when copying calculated fields*/
INSERT INTO test_erp.loctransfers (loctransferid, reference, stockid, shipqty, recqty, shipdate, recdate, shiploc, recloc)
SELECT loctransferid, reference, stockid, shipqty, recqty, shipdate, recdate, shiploc, recloc
FROM kl_erp.loctransfers WHERE reference >= 200000;

TRUNCATE test_erp.`mailgroupdetails`;
INSERT INTO test_erp.mailgroupdetails SELECT * FROM kl_erp.mailgroupdetails;

TRUNCATE test_erp.`mailgroups`;
INSERT INTO test_erp.mailgroups SELECT * FROM kl_erp.mailgroups;

TRUNCATE test_erp.`manufacturers`;
INSERT INTO test_erp.manufacturers SELECT * FROM kl_erp.manufacturers;

TRUNCATE test_erp.`menuitems`;
INSERT INTO test_erp.menuitems SELECT * FROM kl_erp.menuitems;

TRUNCATE test_erp.`modules`;
INSERT INTO test_erp.modules SELECT * FROM kl_erp.modules;

TRUNCATE test_erp.`mrpcalendar`;
INSERT INTO test_erp.mrpcalendar SELECT * FROM kl_erp.mrpcalendar;

TRUNCATE test_erp.`mrpdemands`;
INSERT INTO test_erp.mrpdemands SELECT * FROM kl_erp.mrpdemands;

TRUNCATE test_erp.`mrpdemandtypes`;
INSERT INTO test_erp.mrpdemandtypes SELECT * FROM kl_erp.mrpdemandtypes;

TRUNCATE test_erp.`mrpparameters`;
INSERT INTO test_erp.mrpparameters SELECT * FROM kl_erp.mrpparameters;

TRUNCATE test_erp.`mrpplannedorders`;
INSERT INTO test_erp.mrpplannedorders SELECT * FROM kl_erp.mrpplannedorders;

TRUNCATE test_erp.`mrprequirements`;
INSERT INTO test_erp.mrprequirements SELECT * FROM kl_erp.mrprequirements;

TRUNCATE test_erp.`mrpsupplies`;
INSERT INTO test_erp.mrpsupplies SELECT * FROM kl_erp.mrpsupplies;

TRUNCATE test_erp.`offers`;
INSERT INTO test_erp.offers SELECT * FROM kl_erp.offers;

TRUNCATE test_erp.`orderdeliverydifferenceslog`;
INSERT INTO test_erp.orderdeliverydifferenceslog SELECT * FROM kl_erp.orderdeliverydifferenceslog;

TRUNCATE test_erp.`packagingused`;
/* INSERT INTO test_erp.packagingused SELECT * FROM kl_erp.packagingused; */

TRUNCATE test_erp.`paymentmethods`;
INSERT INTO test_erp.paymentmethods SELECT * FROM kl_erp.paymentmethods;

TRUNCATE test_erp.`paymentterms`;
INSERT INTO test_erp.paymentterms SELECT * FROM kl_erp.paymentterms;

TRUNCATE test_erp.`pcashdetails`;
/*INSERT INTO test_erp.pcashdetails SELECT * FROM kl_erp.pcashdetails WHERE date >= '2025-01-01'; */
INSERT INTO test_erp.pcashdetails SELECT * FROM kl_erp.pcashdetails; 

TRUNCATE test_erp.`pcashdetailtaxes`;
INSERT INTO test_erp.pcashdetailtaxes SELECT * FROM kl_erp.pcashdetailtaxes;

TRUNCATE test_erp.`pcexpenses`;
INSERT INTO test_erp.pcexpenses SELECT * FROM kl_erp.pcexpenses;

TRUNCATE test_erp.`pcreceipts`;
INSERT INTO test_erp.pcreceipts SELECT * FROM kl_erp.pcreceipts;

TRUNCATE test_erp.`pcsalaries`;
INSERT INTO test_erp.pcsalaries SELECT * FROM kl_erp.pcsalaries; 

TRUNCATE test_erp.`pctabexpenses`;
INSERT INTO test_erp.pctabexpenses SELECT * FROM kl_erp.pctabexpenses;

TRUNCATE test_erp.`pctabs`;
INSERT INTO test_erp.pctabs SELECT * FROM kl_erp.pctabs;

TRUNCATE test_erp.`pctags`;
INSERT INTO test_erp.pctags SELECT * FROM kl_erp.pctags;

TRUNCATE test_erp.`pctypetabs`;
INSERT INTO test_erp.pctypetabs SELECT * FROM kl_erp.pctypetabs;

TRUNCATE test_erp.`periods`;
INSERT INTO test_erp.periods SELECT * FROM kl_erp.periods;

TRUNCATE test_erp.`pickinglistdetails`;
INSERT INTO test_erp.pickinglistdetails SELECT * FROM kl_erp.pickinglistdetails;

TRUNCATE test_erp.`pickinglists`;
INSERT INTO test_erp.pickinglists SELECT * FROM kl_erp.pickinglists;

TRUNCATE test_erp.`pickreq`;
INSERT INTO test_erp.pickreq SELECT * FROM kl_erp.pickreq;

TRUNCATE test_erp.`pickreqdetails`;
INSERT INTO test_erp.pickreqdetails SELECT * FROM kl_erp.pickreqdetails;

TRUNCATE test_erp.`pickserialdetails`;
INSERT INTO test_erp.pickserialdetails SELECT * FROM kl_erp.pickserialdetails;

TRUNCATE test_erp.`pricematrix`;
INSERT INTO test_erp.pricematrix SELECT * FROM kl_erp.pricematrix;

TRUNCATE test_erp.`prices`;
INSERT INTO test_erp.prices SELECT * FROM kl_erp.prices;

TRUNCATE test_erp.`prodspecs`;
INSERT INTO test_erp.prodspecs SELECT * FROM kl_erp.prodspecs;

TRUNCATE test_erp.`purchdata`;
INSERT INTO test_erp.purchdata SELECT * FROM kl_erp.purchdata;

TRUNCATE test_erp.`purchorderauth`;
INSERT INTO test_erp.purchorderauth SELECT * FROM kl_erp.purchorderauth;

TRUNCATE test_erp.`purchorderdetails`;
INSERT INTO test_erp.purchorderdetails SELECT * FROM kl_erp.purchorderdetails;

TRUNCATE test_erp.`purchorders`;
INSERT INTO test_erp.purchorders SELECT * FROM kl_erp.purchorders;

TRUNCATE test_erp.`qasamples`;
INSERT INTO test_erp.qasamples SELECT * FROM kl_erp.qasamples;

TRUNCATE test_erp.`qatests`;
INSERT INTO test_erp.qatests SELECT * FROM kl_erp.qatests;

TRUNCATE test_erp.`recurringsalesorders`;
INSERT INTO test_erp.recurringsalesorders SELECT * FROM kl_erp.recurringsalesorders;

TRUNCATE test_erp.`recurrsalesorderdetails`;
INSERT INTO test_erp.recurrsalesorderdetails SELECT * FROM kl_erp.recurrsalesorderdetails;

TRUNCATE test_erp.`relateditems`;
INSERT INTO test_erp.relateditems SELECT * FROM kl_erp.relateditems;

TRUNCATE test_erp.`reportcolumns`;
INSERT INTO test_erp.reportcolumns SELECT * FROM kl_erp.reportcolumns;

TRUNCATE test_erp.`reportfields`;
INSERT INTO test_erp.reportfields SELECT * FROM kl_erp.reportfields;

TRUNCATE test_erp.`reportheaders`;
INSERT INTO test_erp.reportheaders SELECT * FROM kl_erp.reportheaders;

TRUNCATE test_erp.`reportlets`;
INSERT INTO test_erp.reportlets SELECT * FROM kl_erp.reportlets;

TRUNCATE test_erp.`reportlinks`;
INSERT INTO test_erp.reportlinks SELECT * FROM kl_erp.reportlinks;

TRUNCATE test_erp.`reports`;
INSERT INTO test_erp.reports SELECT * FROM kl_erp.reports;

TRUNCATE test_erp.`returneditems`;
INSERT INTO test_erp.returneditems SELECT * FROM kl_erp.returneditems;

TRUNCATE test_erp.`returnitemreasons`;
INSERT INTO test_erp.returnitemreasons SELECT * FROM kl_erp.returnitemreasons;

TRUNCATE test_erp.`salariescalculated`;
INSERT INTO test_erp.salariescalculated SELECT * FROM kl_erp.salariescalculated;

TRUNCATE test_erp.`salesanalysis`;
/*INSERT INTO test_erp.salesanalysis SELECT * FROM kl_erp.salesanalysis WHERE periodno >= 170; */

TRUNCATE test_erp.`salescat`;
INSERT INTO test_erp.salescat SELECT * FROM kl_erp.salescat;

TRUNCATE test_erp.`salescatprod`;
INSERT INTO test_erp.salescatprod SELECT * FROM kl_erp.salescatprod;

TRUNCATE test_erp.`salescattranslations`;
INSERT INTO test_erp.salescattranslations SELECT * FROM kl_erp.salescattranslations;

TRUNCATE test_erp.`salescommissionrates`;
INSERT INTO test_erp.salescommissionrates SELECT * FROM kl_erp.salescommissionrates;

TRUNCATE test_erp.`salescommissions`;
INSERT INTO test_erp.salescommissions SELECT * FROM kl_erp.salescommissions;

TRUNCATE test_erp.`salescommissiontypes`;
INSERT INTO test_erp.salescommissiontypes SELECT * FROM kl_erp.salescommissiontypes;

TRUNCATE test_erp.`salesglpostings`;
INSERT INTO test_erp.salesglpostings SELECT * FROM kl_erp.salesglpostings;

TRUNCATE test_erp.`salesman`;
INSERT INTO test_erp.salesman SELECT * FROM kl_erp.salesman;

TRUNCATE test_erp.`salesorderdetails`;
INSERT INTO test_erp.salesorderdetails (
  orderlineno, orderno, stkcode, qtyinvoiced, unitprice, units, conversionfactor, decimalplaces, quantity, estimate, discountpercent, actualdispatchdate, completed, narrative, itemdue, poline
)
SELECT
  orderlineno, orderno, stkcode, qtyinvoiced, unitprice, units, conversionfactor, decimalplaces, quantity, estimate, discountpercent, actualdispatchdate, completed, narrative, itemdue, poline
FROM kl_erp.salesorderdetails
WHERE orderno >= 600000;

TRUNCATE test_erp.`salesorders`;
INSERT INTO test_erp.salesorders SELECT * FROM kl_erp.salesorders WHERE orderno >= 600000; 

TRUNCATE test_erp.`salestypes`;
INSERT INTO test_erp.salestypes SELECT * FROM kl_erp.salestypes;

TRUNCATE test_erp.`sampleresults`;
INSERT INTO test_erp.sampleresults SELECT * FROM kl_erp.sampleresults;

TRUNCATE test_erp.`scripts`;
INSERT INTO test_erp.scripts SELECT * FROM kl_erp.scripts;

TRUNCATE test_erp.`securitygroups`;
INSERT INTO test_erp.securitygroups SELECT * FROM kl_erp.securitygroups;

TRUNCATE test_erp.`securityroles`;
INSERT INTO test_erp.securityroles SELECT * FROM kl_erp.securityroles;

TRUNCATE test_erp.`securitytokens`;
INSERT INTO test_erp.securitytokens SELECT * FROM kl_erp.securitytokens;

TRUNCATE test_erp.`sellthroughsupport`;
INSERT INTO test_erp.sellthroughsupport SELECT * FROM kl_erp.sellthroughsupport;

TRUNCATE test_erp.`sessions`;
/*INSERT INTO test_erp.sessions SELECT * FROM kl_erp.sessions;*/

TRUNCATE test_erp.`session_data`;
/*INSERT INTO test_erp.session_data SELECT * FROM kl_erp.session_data; */

TRUNCATE test_erp.`shipmentcharges`;
INSERT INTO test_erp.shipmentcharges SELECT * FROM kl_erp.shipmentcharges;

TRUNCATE test_erp.`shipments`;
INSERT INTO test_erp.shipments SELECT * FROM kl_erp.shipments;

TRUNCATE test_erp.`shippers`;
INSERT INTO test_erp.shippers SELECT * FROM kl_erp.shippers;

TRUNCATE test_erp.`stockadjustmentreasons`;
INSERT INTO test_erp.stockadjustmentreasons SELECT * FROM kl_erp.stockadjustmentreasons;

TRUNCATE test_erp.`stockadjustments`;
INSERT INTO test_erp.stockadjustments SELECT * FROM kl_erp.stockadjustments;

TRUNCATE test_erp.`stockcategory`;
INSERT INTO test_erp.stockcategory SELECT * FROM kl_erp.stockcategory;

TRUNCATE test_erp.`stockcatproperties`;
INSERT INTO test_erp.stockcatproperties SELECT * FROM kl_erp.stockcatproperties;

TRUNCATE test_erp.`stockcheckfreeze`;
INSERT INTO test_erp.stockcheckfreeze SELECT * FROM kl_erp.stockcheckfreeze;

TRUNCATE test_erp.`stockcounts`;
INSERT INTO test_erp.stockcounts SELECT * FROM kl_erp.stockcounts;

TRUNCATE test_erp.`stockdescriptiontranslations`;
INSERT INTO test_erp.stockdescriptiontranslations SELECT * FROM kl_erp.stockdescriptiontranslations;

TRUNCATE test_erp.`stockitemproperties`;
INSERT INTO test_erp.stockitemproperties SELECT * FROM kl_erp.stockitemproperties;

TRUNCATE test_erp.`stockmaster`;
INSERT INTO test_erp.stockmaster (
  stockid, categoryid, lastcategoryupdate, description, longdescription, units, mbflag, lastcostupdate, lastcost, materialcost, labourcost, overheadcost, lowestlevel, discontinued, controlled, eoq, volume, grossweight, barcode, discountcategory, taxcatid, serialised, perishable, decimalplaces, pansize, shrinkfactor, nextserialno, netweight, length, width, height, unitsdimension, klpackaging, klchangingprice, klmovingdiscount20, klmovingdiscount50, klmovingdiscount80, klsynctoopencart, klservicebyreplacement, date_created, date_updated
)
SELECT
  stockid, categoryid, lastcategoryupdate, description, longdescription, units, mbflag, lastcostupdate, lastcost, materialcost, labourcost, overheadcost, lowestlevel, discontinued, controlled, eoq, volume, grossweight, barcode, discountcategory, taxcatid, serialised, perishable, decimalplaces, pansize, shrinkfactor, nextserialno, netweight, length, width, height, unitsdimension, klpackaging, klchangingprice, klmovingdiscount20, klmovingdiscount50, klmovingdiscount80, klsynctoopencart, klservicebyreplacement, date_created, date_updated
FROM kl_erp.stockmaster;

TRUNCATE test_erp.`stockmoves`;
/*INSERT INTO test_erp.stockmoves SELECT * FROM kl_erp.stockmoves WHERE stkmoveno > 8000000;*/
INSERT INTO test_erp.stockmoves SELECT * FROM kl_erp.stockmoves;

TRUNCATE test_erp.`stockmovestaxes`;
/*INSERT INTO test_erp.stockmovestaxes SELECT * FROM kl_erp.stockmovestaxes WHERE stkmoveno > 8000000;*/
INSERT INTO test_erp.stockmovestaxes SELECT * FROM kl_erp.stockmovestaxes;

TRUNCATE test_erp.`stockrequest`;
INSERT INTO test_erp.stockrequest SELECT * FROM kl_erp.stockrequest WHERE dispatchid >= 30000;

TRUNCATE test_erp.`stockrequestitems`;
INSERT INTO test_erp.stockrequestitems SELECT * FROM kl_erp.stockrequestitems WHERE dispatchid >= 30000; 

TRUNCATE test_erp.`stockserialitems`;
INSERT INTO test_erp.stockserialitems SELECT * FROM kl_erp.stockserialitems;

TRUNCATE test_erp.`stockserialmoves`;
INSERT INTO test_erp.stockserialmoves SELECT * FROM kl_erp.stockserialmoves;

TRUNCATE test_erp.`stocktags`;
INSERT INTO test_erp.stocktags SELECT * FROM kl_erp.stocktags;

TRUNCATE test_erp.`suppallocs`;
INSERT INTO test_erp.suppallocs SELECT * FROM kl_erp.suppallocs;

TRUNCATE test_erp.`suppinvstogrn`;
INSERT INTO test_erp.suppinvstogrn SELECT * FROM kl_erp.suppinvstogrn WHERE grnno >= 50000;

TRUNCATE test_erp.`suppliercontacts`;
INSERT INTO test_erp.suppliercontacts SELECT * FROM kl_erp.suppliercontacts;

TRUNCATE test_erp.`supplierdiscounts`;
INSERT INTO test_erp.supplierdiscounts SELECT * FROM kl_erp.supplierdiscounts;

TRUNCATE test_erp.`suppliers`;
INSERT INTO test_erp.suppliers SELECT * FROM kl_erp.suppliers;

TRUNCATE test_erp.`suppliertype`;
INSERT INTO test_erp.suppliertype SELECT * FROM kl_erp.suppliertype;

TRUNCATE test_erp.`supptrans`;
INSERT INTO test_erp.supptrans SELECT * FROM kl_erp.supptrans WHERE trandate >= '2025-01-01'; 

TRUNCATE test_erp.`supptranstaxes`;
INSERT INTO test_erp.supptranstaxes SELECT * FROM kl_erp.supptranstaxes WHERE supptransid >= 10000; 

TRUNCATE test_erp.`systypes`;
INSERT INTO test_erp.systypes SELECT * FROM kl_erp.systypes;

/* do not copy to avoid prolem with auto generated codes */
TRUNCATE test_erp.`tags`;
INSERT INTO test_erp.tags SELECT * FROM kl_erp.tags; 

TRUNCATE test_erp.`taxauthorities`;
INSERT INTO test_erp.taxauthorities SELECT * FROM kl_erp.taxauthorities;

TRUNCATE test_erp.`taxauthrates`;
INSERT INTO test_erp.taxauthrates SELECT * FROM kl_erp.taxauthrates;

TRUNCATE test_erp.`taxcategories`;
INSERT INTO test_erp.taxcategories SELECT * FROM kl_erp.taxcategories;

TRUNCATE test_erp.`taxgroups`;
INSERT INTO test_erp.taxgroups SELECT * FROM kl_erp.taxgroups;

TRUNCATE test_erp.`taxgrouptaxes`;
INSERT INTO test_erp.taxgrouptaxes SELECT * FROM kl_erp.taxgrouptaxes;

TRUNCATE test_erp.`taxprovinces`;
INSERT INTO test_erp.taxprovinces SELECT * FROM kl_erp.taxprovinces;

TRUNCATE test_erp.`tenderitems`;
INSERT INTO test_erp.tenderitems SELECT * FROM kl_erp.tenderitems;

TRUNCATE test_erp.`tenders`;
INSERT INTO test_erp.tenders SELECT * FROM kl_erp.tenders;

TRUNCATE test_erp.`tendersuppliers`;
INSERT INTO test_erp.tendersuppliers SELECT * FROM kl_erp.tendersuppliers;

TRUNCATE test_erp.`timesheets`;
INSERT INTO test_erp.timesheets SELECT * FROM kl_erp.timesheets;

TRUNCATE test_erp.`unitsofdimension`;
INSERT INTO test_erp.unitsofdimension SELECT * FROM kl_erp.unitsofdimension;

TRUNCATE test_erp.`unitsofmeasure`;
INSERT INTO test_erp.unitsofmeasure SELECT * FROM kl_erp.unitsofmeasure;

TRUNCATE test_erp.`woitems`;
INSERT INTO test_erp.woitems SELECT * FROM kl_erp.woitems WHERE wo >= 5000;

TRUNCATE test_erp.`worequirements`;
INSERT INTO test_erp.worequirements SELECT * FROM kl_erp.worequirements WHERE wo >= 5000;

TRUNCATE test_erp.`workcentres`;
INSERT INTO test_erp.workcentres SELECT * FROM kl_erp.workcentres;

TRUNCATE test_erp.`workorders`;
INSERT INTO test_erp.workorders SELECT * FROM kl_erp.workorders WHERE wo >= 5000;

TRUNCATE test_erp.`woserialnos`;
INSERT INTO test_erp.woserialnos SELECT * FROM kl_erp.woserialnos;

TRUNCATE test_erp.`www_users`;
INSERT INTO test_erp.www_users SELECT * FROM kl_erp.www_users;

DELIMITER $$
CREATE TRIGGER `currencies_creation_timestamp` BEFORE INSERT ON `currencies` FOR EACH ROW SET NEW.date_created = NOW()
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `currencies_update_timestamp` BEFORE UPDATE ON `currencies` FOR EACH ROW SET NEW.date_updated = NOW()
$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER `gltrans_after_delete` AFTER DELETE ON `gltrans` FOR EACH ROW BEGIN
			UPDATE gltotals
			SET amount = amount - OLD.amount
			WHERE account = OLD.account AND period = OLD.periodno;
		END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `gltrans_after_insert` AFTER INSERT ON `gltrans` FOR EACH ROW BEGIN
			INSERT INTO gltotals (account, period, amount)
			VALUES (NEW.account, NEW.periodno, NEW.amount)
			ON DUPLICATE KEY UPDATE amount = amount + NEW.amount;
		END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `gltrans_after_update` AFTER UPDATE ON `gltrans` FOR EACH ROW BEGIN
			IF NEW.account <> OLD.account OR NEW.periodno <> OLD.periodno THEN
				UPDATE gltotals
				SET amount = amount - OLD.amount
				WHERE account = OLD.account AND period = OLD.periodno;

				INSERT INTO gltotals (account, period, amount)
				VALUES (NEW.account, NEW.periodno, NEW.amount)
				ON DUPLICATE KEY UPDATE amount = amount + NEW.amount;
			ELSE
				UPDATE gltotals
				SET amount = amount - OLD.amount + NEW.amount
				WHERE account = NEW.account AND period = NEW.periodno;
			END IF;
		END
$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER `klretailcustomers_creation_timestamp` BEFORE INSERT ON `klretailcustomers` FOR EACH ROW SET NEW.date_added = NOW()
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `klretailcustomers_update_timestamp` BEFORE UPDATE ON `klretailcustomers` FOR EACH ROW SET NEW.date_updated = NOW()
$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER `klstockmarketplaces_creation_timestamp` BEFORE INSERT ON `klstockmarketplaces` FOR EACH ROW SET NEW.date_created = NOW()
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `klstockmarketplaces_update_timestamp` BEFORE UPDATE ON `klstockmarketplaces` FOR EACH ROW SET NEW.date_updated = NOW()
$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER `locstock_creation_timestamp` BEFORE INSERT ON `locstock` FOR EACH ROW SET NEW.date_created = NOW()
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `locstock_update_timestamp` BEFORE UPDATE ON `locstock` FOR EACH ROW SET NEW.date_updated = NOW()
$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER `prices_creation_timestamp` BEFORE INSERT ON `prices` FOR EACH ROW SET NEW.date_created = NOW()
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `prices_update_timestamp` BEFORE UPDATE ON `prices` FOR EACH ROW SET NEW.date_updated = NOW()
$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER `relateditems_creation_timestamp` BEFORE INSERT ON `relateditems` FOR EACH ROW SET NEW.date_created = NOW()
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `relateditems_update_timestamp` BEFORE UPDATE ON `relateditems` FOR EACH ROW SET NEW.date_updated = NOW()
$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER `salariescalculated_creation_timestamp` BEFORE INSERT ON `salariescalculated` FOR EACH ROW SET NEW.date_added = NOW()
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `salariescalculated_update_timestamp` BEFORE UPDATE ON `salariescalculated` FOR EACH ROW SET NEW.date_updated = NOW()
$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER `salescat_creation_timestamp` BEFORE INSERT ON `salescat` FOR EACH ROW SET NEW.date_created = NOW()
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `salescat_update_timestamp` BEFORE UPDATE ON `salescat` FOR EACH ROW SET NEW.date_updated = NOW()
$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER `salescatprod_creation_timestamp` BEFORE INSERT ON `salescatprod` FOR EACH ROW SET NEW.date_created = NOW()
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `salescatprod_update_timestamp` BEFORE UPDATE ON `salescatprod` FOR EACH ROW SET NEW.date_updated = NOW()
$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER `stockdescriptiontranslations_creation_timestamp` BEFORE INSERT ON `stockdescriptiontranslations` FOR EACH ROW SET NEW.date_created = NOW()
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `stockdescriptiontranslations_update_timestamp` BEFORE UPDATE ON `stockdescriptiontranslations` FOR EACH ROW SET NEW.date_updated = NOW()
$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER `stockmaster_creation_timestamp` BEFORE INSERT ON `stockmaster` FOR EACH ROW SET NEW.date_created = NOW()
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `stockmaster_update_timestamp` BEFORE UPDATE ON `stockmaster` FOR EACH ROW SET NEW.date_updated = NOW()
$$
DELIMITER ;

UPDATE test_erp.`config` SET `confvalue` = 'companies/test_erp/part_pics' WHERE `confname` = 'part_pics_dir';
UPDATE test_erp.`config` SET `confvalue` = 'companies/test_erp/reports' WHERE `confname` = 'reports_dir';
UPDATE test_erp.`config` SET `confvalue` = 'companies/test_erp/logs' WHERE `confname` = 'LogPath';
UPDATE test_erp.`config` SET `confvalue` = 'webmaster@kapal-laut.com' WHERE `confname` = 'InventoryManagerEmail';
UPDATE test_erp.`config` SET `confvalue` = 'webmaster@kapal-laut.com' WHERE `confname` = 'FactoryManagerEmail';
UPDATE test_erp.`config` SET `confvalue` = 'webmaster@kapal-laut.com' WHERE `confname` = 'PurchasingManagerEmail';
UPDATE test_erp.`config` SET `confvalue` = 'webmaster@kapal-laut.com' WHERE `confname` = 'ShopManagerEmail';
UPDATE test_erp.`config` SET `confvalue` = 'test' WHERE `confname` = 'ShopMode';

UPDATE test_erp.www_users SET theme = "gel";
UPDATE test_erp.www_users SET blocked = 0 WHERE userid = "SPG-999";
UPDATE test_erp.www_users SET fullaccess = 8 WHERE userid = "Garbi"; /* Garbi as Sysadmin in TEST */

UPDATE test_erp.`klonlinepartners` SET `paypaltest` = 1;

SET FOREIGN_KEY_CHECKS=1;

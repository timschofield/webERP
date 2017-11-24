SET FOREIGN_KEY_CHECKS=0;
  
TRUNCATE kurakura_kl_test_erp.`accountgroups`;
INSERT INTO kurakura_kl_test_erp.accountgroups SELECT * FROM kurakura_kl_erp.accountgroups;

TRUNCATE kurakura_kl_test_erp.`accountsection`;
INSERT INTO kurakura_kl_test_erp.accountsection SELECT * FROM kurakura_kl_erp.accountsection;

TRUNCATE kurakura_kl_test_erp.`areas`;
INSERT INTO kurakura_kl_test_erp.areas SELECT * FROM kurakura_kl_erp.areas;

TRUNCATE kurakura_kl_test_erp.`audittrail`;
/* INSERT INTO kurakura_kl_test_erp.audittrail SELECT * FROM kurakura_kl_erp.audittrail; */

TRUNCATE kurakura_kl_test_erp.`bankaccounts`;
INSERT INTO kurakura_kl_test_erp.bankaccounts SELECT * FROM kurakura_kl_erp.bankaccounts;

TRUNCATE kurakura_kl_test_erp.`bankaccountusers`;
INSERT INTO kurakura_kl_test_erp.bankaccountusers SELECT * FROM kurakura_kl_erp.bankaccountusers;

TRUNCATE kurakura_kl_test_erp.`banktrans`;
INSERT INTO kurakura_kl_test_erp.banktrans SELECT * FROM kurakura_kl_erp.banktrans WHERE transdate >= "2017-01-01";

TRUNCATE kurakura_kl_test_erp.`bom`;
INSERT INTO kurakura_kl_test_erp.bom SELECT * FROM kurakura_kl_erp.bom;

TRUNCATE kurakura_kl_test_erp.`buckets`;
INSERT INTO kurakura_kl_test_erp.buckets SELECT * FROM kurakura_kl_erp.buckets;

TRUNCATE kurakura_kl_test_erp.`chartdetails`;
INSERT INTO kurakura_kl_test_erp.chartdetails SELECT * FROM kurakura_kl_erp.chartdetails WHERE period >= 94;

TRUNCATE kurakura_kl_test_erp.`chartmaster`;
INSERT INTO kurakura_kl_test_erp.chartmaster SELECT * FROM kurakura_kl_erp.chartmaster;

TRUNCATE kurakura_kl_test_erp.`chartmasterPMA`;
INSERT INTO kurakura_kl_test_erp.chartmasterPMA SELECT * FROM kurakura_kl_erp.chartmasterPMA;

TRUNCATE kurakura_kl_test_erp.`chartmasterPT`;
INSERT INTO kurakura_kl_test_erp.chartmasterPT SELECT * FROM kurakura_kl_erp.chartmasterPT;

TRUNCATE kurakura_kl_test_erp.`cogsglpostings`;
INSERT INTO kurakura_kl_test_erp.cogsglpostings SELECT * FROM kurakura_kl_erp.cogsglpostings;

TRUNCATE kurakura_kl_test_erp.`companies`;
INSERT INTO kurakura_kl_test_erp.companies SELECT * FROM kurakura_kl_erp.companies;

TRUNCATE kurakura_kl_test_erp.`config`;
INSERT INTO kurakura_kl_test_erp.config SELECT * FROM kurakura_kl_erp.config;

TRUNCATE kurakura_kl_test_erp.`contractbom`;
INSERT INTO kurakura_kl_test_erp.contractbom SELECT * FROM kurakura_kl_erp.contractbom;

TRUNCATE kurakura_kl_test_erp.`contractcharges`;
INSERT INTO kurakura_kl_test_erp.contractcharges SELECT * FROM kurakura_kl_erp.contractcharges;

TRUNCATE kurakura_kl_test_erp.`contractreqts`;
INSERT INTO kurakura_kl_test_erp.contractreqts SELECT * FROM kurakura_kl_erp.contractreqts;

TRUNCATE kurakura_kl_test_erp.`contracts`;
INSERT INTO kurakura_kl_test_erp.contracts SELECT * FROM kurakura_kl_erp.contracts;

TRUNCATE kurakura_kl_test_erp.`currencies`;
INSERT INTO kurakura_kl_test_erp.currencies SELECT * FROM kurakura_kl_erp.currencies;

TRUNCATE kurakura_kl_test_erp.`custallocns`;
INSERT INTO kurakura_kl_test_erp.custallocns SELECT * FROM kurakura_kl_erp.custallocns WHERE datealloc >= "2017-01-01";

TRUNCATE kurakura_kl_test_erp.`custbranch`;
INSERT INTO kurakura_kl_test_erp.custbranch SELECT * FROM kurakura_kl_erp.custbranch;

TRUNCATE kurakura_kl_test_erp.`custcontacts`;
INSERT INTO kurakura_kl_test_erp.custcontacts SELECT * FROM kurakura_kl_erp.custcontacts;

TRUNCATE kurakura_kl_test_erp.`custitem`;
INSERT INTO kurakura_kl_test_erp.custitem SELECT * FROM kurakura_kl_erp.custitem;

TRUNCATE kurakura_kl_test_erp.`custnotes`;
INSERT INTO kurakura_kl_test_erp.custnotes SELECT * FROM kurakura_kl_erp.custnotes;

TRUNCATE kurakura_kl_test_erp.`debtorsmaster`;
INSERT INTO kurakura_kl_test_erp.debtorsmaster SELECT * FROM kurakura_kl_erp.debtorsmaster;

TRUNCATE kurakura_kl_test_erp.`debtortrans`;
INSERT INTO kurakura_kl_test_erp.debtortrans SELECT * FROM kurakura_kl_erp.debtortrans WHERE trandate >= "2017-01-01";

TRUNCATE kurakura_kl_test_erp.`debtortranstaxes`;
INSERT INTO kurakura_kl_test_erp.debtortranstaxes SELECT * FROM kurakura_kl_erp.debtortranstaxes WHERE debtortransid = 248580;

TRUNCATE kurakura_kl_test_erp.`debtortype`;
INSERT INTO kurakura_kl_test_erp.debtortype SELECT * FROM kurakura_kl_erp.debtortype;

TRUNCATE kurakura_kl_test_erp.`debtortypenotes`;
INSERT INTO kurakura_kl_test_erp.debtortypenotes SELECT * FROM kurakura_kl_erp.debtortypenotes;

TRUNCATE kurakura_kl_test_erp.`deliverynotes`;
INSERT INTO kurakura_kl_test_erp.deliverynotes SELECT * FROM kurakura_kl_erp.deliverynotes;

TRUNCATE kurakura_kl_test_erp.`departments`;
INSERT INTO kurakura_kl_test_erp.departments SELECT * FROM kurakura_kl_erp.departments;

TRUNCATE kurakura_kl_test_erp.`discountmatrix`;
INSERT INTO kurakura_kl_test_erp.discountmatrix SELECT * FROM kurakura_kl_erp.discountmatrix;

TRUNCATE kurakura_kl_test_erp.`edi_orders_segs`;
INSERT INTO kurakura_kl_test_erp.edi_orders_segs SELECT * FROM kurakura_kl_erp.edi_orders_segs;

TRUNCATE kurakura_kl_test_erp.`ediitemmapping`;
INSERT INTO kurakura_kl_test_erp.ediitemmapping SELECT * FROM kurakura_kl_erp.ediitemmapping;

TRUNCATE kurakura_kl_test_erp.`edimessageformat`;
INSERT INTO kurakura_kl_test_erp.edimessageformat SELECT * FROM kurakura_kl_erp.edimessageformat;

TRUNCATE kurakura_kl_test_erp.`edi_orders_seg_groups`;
INSERT INTO kurakura_kl_test_erp.edi_orders_seg_groups SELECT * FROM kurakura_kl_erp.edi_orders_seg_groups;

TRUNCATE kurakura_kl_test_erp.`emailsettings`;
INSERT INTO kurakura_kl_test_erp.emailsettings SELECT * FROM kurakura_kl_erp.emailsettings;

TRUNCATE kurakura_kl_test_erp.`factorcompanies`;
INSERT INTO kurakura_kl_test_erp.factorcompanies SELECT * FROM kurakura_kl_erp.factorcompanies;

TRUNCATE kurakura_kl_test_erp.`fixedassetcategories`;
INSERT INTO kurakura_kl_test_erp.fixedassetcategories SELECT * FROM kurakura_kl_erp.fixedassetcategories;

TRUNCATE kurakura_kl_test_erp.`fixedassetlocations`;
INSERT INTO kurakura_kl_test_erp.fixedassetlocations SELECT * FROM kurakura_kl_erp.fixedassetlocations;

TRUNCATE kurakura_kl_test_erp.`fixedassets`;
INSERT INTO kurakura_kl_test_erp.fixedassets SELECT * FROM kurakura_kl_erp.fixedassets;

TRUNCATE kurakura_kl_test_erp.`fixedassettasks`;
INSERT INTO kurakura_kl_test_erp.fixedassettasks SELECT * FROM kurakura_kl_erp.fixedassettasks;

TRUNCATE kurakura_kl_test_erp.`fixedassettrans`;
INSERT INTO kurakura_kl_test_erp.fixedassettrans SELECT * FROM kurakura_kl_erp.fixedassettrans WHERE transdate >= "2017-01-01";

TRUNCATE kurakura_kl_test_erp.`freightcosts`;
INSERT INTO kurakura_kl_test_erp.freightcosts SELECT * FROM kurakura_kl_erp.freightcosts;

TRUNCATE kurakura_kl_test_erp.`geocode_param`;
INSERT INTO kurakura_kl_test_erp.geocode_param SELECT * FROM kurakura_kl_erp.geocode_param;

TRUNCATE kurakura_kl_test_erp.`glaccountusers`;
INSERT INTO kurakura_kl_test_erp.glaccountusers SELECT * FROM kurakura_kl_erp.glaccountusers;

TRUNCATE kurakura_kl_test_erp.`gltrans`;
INSERT INTO kurakura_kl_test_erp.gltrans SELECT * FROM kurakura_kl_erp.gltrans WHERE trandate > "2017-01-01";

TRUNCATE kurakura_kl_test_erp.`grns`;
INSERT INTO kurakura_kl_test_erp.grns SELECT * FROM kurakura_kl_erp.grns WHERE deliverydate >= "2017-01-01";

TRUNCATE kurakura_kl_test_erp.`holdreasons`;
INSERT INTO kurakura_kl_test_erp.holdreasons SELECT * FROM kurakura_kl_erp.holdreasons;

TRUNCATE kurakura_kl_test_erp.`internalstockcatrole`;
INSERT INTO kurakura_kl_test_erp.internalstockcatrole SELECT * FROM kurakura_kl_erp.internalstockcatrole;

TRUNCATE kurakura_kl_test_erp.`kladjustrl`;
INSERT INTO kurakura_kl_test_erp.kladjustrl SELECT * FROM kurakura_kl_erp.kladjustrl;

TRUNCATE kurakura_kl_test_erp.`klchangeprice`;
INSERT INTO kurakura_kl_test_erp.klchangeprice SELECT * FROM kurakura_kl_erp.klchangeprice;

TRUNCATE kurakura_kl_test_erp.`klfreeexchanges`;
INSERT INTO kurakura_kl_test_erp.klfreeexchanges SELECT * FROM kurakura_kl_erp.klfreeexchanges;

TRUNCATE kurakura_kl_test_erp.`klmovetodiscount20`;
INSERT INTO kurakura_kl_test_erp.klmovetodiscount20 SELECT * FROM kurakura_kl_erp.klmovetodiscount20;

TRUNCATE kurakura_kl_test_erp.`klmovetodiscount50`;
INSERT INTO kurakura_kl_test_erp.klmovetodiscount50 SELECT * FROM kurakura_kl_erp.klmovetodiscount50;

TRUNCATE kurakura_kl_test_erp.`klmovetodiscount80`;
INSERT INTO kurakura_kl_test_erp.klmovetodiscount80 SELECT * FROM kurakura_kl_erp.klmovetodiscount80;

TRUNCATE kurakura_kl_test_erp.`klretailcustomers`;
INSERT INTO kurakura_kl_test_erp.klretailcustomers SELECT * FROM kurakura_kl_erp.klretailcustomers WHERE date_added >= "2017-01-01";

TRUNCATE kurakura_kl_test_erp.`klrevisedemaildomains`;
INSERT INTO kurakura_kl_test_erp.klrevisedemaildomains SELECT * FROM kurakura_kl_erp.klrevisedemaildomains;

TRUNCATE kurakura_kl_test_erp.`klsalesperformance`;
INSERT INTO kurakura_kl_test_erp.klsalesperformance SELECT * FROM kurakura_kl_erp.klsalesperformance;

TRUNCATE kurakura_kl_test_erp.`labelfields`;
INSERT INTO kurakura_kl_test_erp.labelfields SELECT * FROM kurakura_kl_erp.labelfields;

TRUNCATE kurakura_kl_test_erp.`labels`;
INSERT INTO kurakura_kl_test_erp.labels SELECT * FROM kurakura_kl_erp.labels;

INSERT INTO kurakura_kl_test_erp.lastcostrollup SELECT * FROM kurakura_kl_erp.lastcostrollup;
TRUNCATE kurakura_kl_test_erp.`lastcostrollup`;

INSERT INTO kurakura_kl_test_erp.levels SELECT * FROM kurakura_kl_erp.levels;
TRUNCATE kurakura_kl_test_erp.`levels`;

TRUNCATE kurakura_kl_test_erp.`locations`;
INSERT INTO kurakura_kl_test_erp.locations SELECT * FROM kurakura_kl_erp.locations;

TRUNCATE kurakura_kl_test_erp.`locationtypes`;
INSERT INTO kurakura_kl_test_erp.locationtypes SELECT * FROM kurakura_kl_erp.locationtypes;

TRUNCATE kurakura_kl_test_erp.`locationusers`;
INSERT INTO kurakura_kl_test_erp.locationusers SELECT * FROM kurakura_kl_erp.locationusers;

TRUNCATE kurakura_kl_test_erp.`locationzones`;
INSERT INTO kurakura_kl_test_erp.locationzones SELECT * FROM kurakura_kl_erp.locationzones;

TRUNCATE kurakura_kl_test_erp.`locstock`;
INSERT INTO kurakura_kl_test_erp.locstock SELECT * FROM kurakura_kl_erp.locstock;

TRUNCATE kurakura_kl_test_erp.`loctransfercancellations`;
INSERT INTO kurakura_kl_test_erp.loctransfercancellations SELECT * FROM kurakura_kl_erp.loctransfercancellations;

TRUNCATE kurakura_kl_test_erp.`loctransfers`;
INSERT INTO kurakura_kl_test_erp.loctransfers SELECT * FROM kurakura_kl_erp.loctransfers WHERE recdate >= "2017-01-01";

TRUNCATE kurakura_kl_test_erp.`mailgroupdetails`;
INSERT INTO kurakura_kl_test_erp.mailgroupdetails SELECT * FROM kurakura_kl_erp.mailgroupdetails;

TRUNCATE kurakura_kl_test_erp.`mailgroups`;
INSERT INTO kurakura_kl_test_erp.mailgroups SELECT * FROM kurakura_kl_erp.mailgroups;

TRUNCATE kurakura_kl_test_erp.`manufacturers`;
INSERT INTO kurakura_kl_test_erp.manufacturers SELECT * FROM kurakura_kl_erp.manufacturers;

TRUNCATE kurakura_kl_test_erp.`mrpcalendar`;
INSERT INTO kurakura_kl_test_erp.mrpcalendar SELECT * FROM kurakura_kl_erp.mrpcalendar;

TRUNCATE kurakura_kl_test_erp.`mrpdemands`;
INSERT INTO kurakura_kl_test_erp.mrpdemands SELECT * FROM kurakura_kl_erp.mrpdemands;

TRUNCATE kurakura_kl_test_erp.`mrpdemandtypes`;
INSERT INTO kurakura_kl_test_erp.mrpdemandtypes SELECT * FROM kurakura_kl_erp.mrpdemandtypes;

TRUNCATE kurakura_kl_test_erp.`mrpparameters`;
INSERT INTO kurakura_kl_test_erp.mrpparameters SELECT * FROM kurakura_kl_erp.mrpparameters;

TRUNCATE kurakura_kl_test_erp.`mrpplannedorders`;
INSERT INTO kurakura_kl_test_erp.mrpplannedorders SELECT * FROM kurakura_kl_erp.mrpplannedorders;

TRUNCATE kurakura_kl_test_erp.`mrprequirements`;
INSERT INTO kurakura_kl_test_erp.mrprequirements SELECT * FROM kurakura_kl_erp.mrprequirements;

TRUNCATE kurakura_kl_test_erp.`mrpsupplies`;
INSERT INTO kurakura_kl_test_erp.mrpsupplies SELECT * FROM kurakura_kl_erp.mrpsupplies;

TRUNCATE kurakura_kl_test_erp.`offers`;
INSERT INTO kurakura_kl_test_erp.offers SELECT * FROM kurakura_kl_erp.offers;

TRUNCATE kurakura_kl_test_erp.`orderdeliverydifferenceslog`;
INSERT INTO kurakura_kl_test_erp.orderdeliverydifferenceslog SELECT * FROM kurakura_kl_erp.orderdeliverydifferenceslog;

TRUNCATE kurakura_kl_test_erp.`packagingused`;
INSERT INTO kurakura_kl_test_erp.packagingused SELECT * FROM kurakura_kl_erp.packagingused WHERE date >= "2017-01-01";

TRUNCATE kurakura_kl_test_erp.`paymentmethods`;
INSERT INTO kurakura_kl_test_erp.paymentmethods SELECT * FROM kurakura_kl_erp.paymentmethods;

TRUNCATE kurakura_kl_test_erp.`paymentterms`;
INSERT INTO kurakura_kl_test_erp.paymentterms SELECT * FROM kurakura_kl_erp.paymentterms;

TRUNCATE kurakura_kl_test_erp.`pcashdetails`;
INSERT INTO kurakura_kl_test_erp.pcashdetails SELECT * FROM kurakura_kl_erp.pcashdetails WHERE date >= "2017-01-01";

TRUNCATE kurakura_kl_test_erp.`pcexpenses`;
INSERT INTO kurakura_kl_test_erp.pcexpenses SELECT * FROM kurakura_kl_erp.pcexpenses;

TRUNCATE kurakura_kl_test_erp.`pctabexpenses`;
INSERT INTO kurakura_kl_test_erp.pctabexpenses SELECT * FROM kurakura_kl_erp.pctabexpenses;

TRUNCATE kurakura_kl_test_erp.`pctabs`;
INSERT INTO kurakura_kl_test_erp.pctabs SELECT * FROM kurakura_kl_erp.pctabs;

TRUNCATE kurakura_kl_test_erp.`pctypetabs`;
INSERT INTO kurakura_kl_test_erp.pctypetabs SELECT * FROM kurakura_kl_erp.pctypetabs;

TRUNCATE kurakura_kl_test_erp.`periods`;
INSERT INTO kurakura_kl_test_erp.periods SELECT * FROM kurakura_kl_erp.periods;

TRUNCATE kurakura_kl_test_erp.`pickinglistdetails`;
INSERT INTO kurakura_kl_test_erp.pickinglistdetails SELECT * FROM kurakura_kl_erp.pickinglistdetails;

TRUNCATE kurakura_kl_test_erp.`pickinglists`;
INSERT INTO kurakura_kl_test_erp.pickinglists SELECT * FROM kurakura_kl_erp.pickinglists;

TRUNCATE kurakura_kl_test_erp.`pricematrix`;
INSERT INTO kurakura_kl_test_erp.pricematrix SELECT * FROM kurakura_kl_erp.pricematrix;

TRUNCATE kurakura_kl_test_erp.`prices`;
INSERT INTO kurakura_kl_test_erp.prices SELECT * FROM kurakura_kl_erp.prices;

TRUNCATE kurakura_kl_test_erp.`prodspecs`;
INSERT INTO kurakura_kl_test_erp.prodspecs SELECT * FROM kurakura_kl_erp.prodspecs;

TRUNCATE kurakura_kl_test_erp.`purchdata`;
INSERT INTO kurakura_kl_test_erp.purchdata SELECT * FROM kurakura_kl_erp.purchdata;

TRUNCATE kurakura_kl_test_erp.`purchorderauth`;
INSERT INTO kurakura_kl_test_erp.purchorderauth SELECT * FROM kurakura_kl_erp.purchorderauth;

TRUNCATE kurakura_kl_test_erp.`purchorderdetails`;
INSERT INTO kurakura_kl_test_erp.purchorderdetails SELECT * FROM kurakura_kl_erp.purchorderdetails WHERE deliverydate >= "2017-01-01";

TRUNCATE kurakura_kl_test_erp.`purchorders`;
INSERT INTO kurakura_kl_test_erp.purchorders SELECT * FROM kurakura_kl_erp.purchorders WHERE orddate >= "2017-01-01";

TRUNCATE kurakura_kl_test_erp.`qasamples`;
INSERT INTO kurakura_kl_test_erp.qasamples SELECT * FROM kurakura_kl_erp.qasamples;

TRUNCATE kurakura_kl_test_erp.`qatests`;
INSERT INTO kurakura_kl_test_erp.qatests SELECT * FROM kurakura_kl_erp.qatests;

TRUNCATE kurakura_kl_test_erp.`recurringsalesorders`;
INSERT INTO kurakura_kl_test_erp.recurringsalesorders SELECT * FROM kurakura_kl_erp.recurringsalesorders;

TRUNCATE kurakura_kl_test_erp.`recurrsalesorderdetails`;
INSERT INTO kurakura_kl_test_erp.recurrsalesorderdetails SELECT * FROM kurakura_kl_erp.recurrsalesorderdetails;

TRUNCATE kurakura_kl_test_erp.`relateditems`;
INSERT INTO kurakura_kl_test_erp.relateditems SELECT * FROM kurakura_kl_erp.relateditems;

TRUNCATE kurakura_kl_test_erp.`reportcolumns`;
INSERT INTO kurakura_kl_test_erp.reportcolumns SELECT * FROM kurakura_kl_erp.reportcolumns;

TRUNCATE kurakura_kl_test_erp.`reportfields`;
INSERT INTO kurakura_kl_test_erp.reportfields SELECT * FROM kurakura_kl_erp.reportfields;

TRUNCATE kurakura_kl_test_erp.`reportheaders`;
INSERT INTO kurakura_kl_test_erp.reportheaders SELECT * FROM kurakura_kl_erp.reportheaders;

TRUNCATE kurakura_kl_test_erp.`reportlets`;
INSERT INTO kurakura_kl_test_erp.reportlets SELECT * FROM kurakura_kl_erp.reportlets;

TRUNCATE kurakura_kl_test_erp.`reportlinks`;
INSERT INTO kurakura_kl_test_erp.reportlinks SELECT * FROM kurakura_kl_erp.reportlinks;

TRUNCATE kurakura_kl_test_erp.`reports`;
INSERT INTO kurakura_kl_test_erp.reports SELECT * FROM kurakura_kl_erp.reports;

TRUNCATE kurakura_kl_test_erp.`returnitemreasons`;
INSERT INTO kurakura_kl_test_erp.returnitemreasons SELECT * FROM kurakura_kl_erp.returnitemreasons;

TRUNCATE kurakura_kl_test_erp.`returneditems`;
INSERT INTO kurakura_kl_test_erp.returneditems SELECT * FROM kurakura_kl_erp.returneditems;

TRUNCATE kurakura_kl_test_erp.`salariescalculated`;
INSERT INTO kurakura_kl_test_erp.salariescalculated SELECT * FROM kurakura_kl_erp.salariescalculated WHERE periodno >= 94;

TRUNCATE kurakura_kl_test_erp.`salesanalysis`;
INSERT INTO kurakura_kl_test_erp.salesanalysis SELECT * FROM kurakura_kl_erp.salesanalysis WHERE periodno >= 94;

TRUNCATE kurakura_kl_test_erp.`salescat`;
INSERT INTO kurakura_kl_test_erp.salescat SELECT * FROM kurakura_kl_erp.salescat;

TRUNCATE kurakura_kl_test_erp.`salescatprod`;
INSERT INTO kurakura_kl_test_erp.salescatprod SELECT * FROM kurakura_kl_erp.salescatprod;

TRUNCATE kurakura_kl_test_erp.`salescattranslations`;
INSERT INTO kurakura_kl_test_erp.salescattranslations SELECT * FROM kurakura_kl_erp.salescattranslations;

TRUNCATE kurakura_kl_test_erp.`salesglpostings`;
INSERT INTO kurakura_kl_test_erp.salesglpostings SELECT * FROM kurakura_kl_erp.salesglpostings;

TRUNCATE kurakura_kl_test_erp.`salesman`;
INSERT INTO kurakura_kl_test_erp.salesman SELECT * FROM kurakura_kl_erp.salesman;

TRUNCATE kurakura_kl_test_erp.`salesorderdetails`;
INSERT INTO kurakura_kl_test_erp.salesorderdetails SELECT * FROM kurakura_kl_erp.salesorderdetails WHERE actualdispatchdate >= "2017-01-01";

TRUNCATE kurakura_kl_test_erp.`salesorders`;
INSERT INTO kurakura_kl_test_erp.salesorders SELECT * FROM kurakura_kl_erp.salesorders WHERE orddate >= "2017-01-01";

TRUNCATE kurakura_kl_test_erp.`salestypes`;
INSERT INTO kurakura_kl_test_erp.salestypes SELECT * FROM kurakura_kl_erp.salestypes;

TRUNCATE kurakura_kl_test_erp.`sampleresults`;
INSERT INTO kurakura_kl_test_erp.sampleresults SELECT * FROM kurakura_kl_erp.sampleresults;

TRUNCATE kurakura_kl_test_erp.`scripts`;
INSERT INTO kurakura_kl_test_erp.scripts SELECT * FROM kurakura_kl_erp.scripts;

TRUNCATE kurakura_kl_test_erp.`securitygroups`;
INSERT INTO kurakura_kl_test_erp.securitygroups SELECT * FROM kurakura_kl_erp.securitygroups;

TRUNCATE kurakura_kl_test_erp.`securityroles`;
INSERT INTO kurakura_kl_test_erp.securityroles SELECT * FROM kurakura_kl_erp.securityroles;

TRUNCATE kurakura_kl_test_erp.`securitytokens`;
INSERT INTO kurakura_kl_test_erp.securitytokens SELECT * FROM kurakura_kl_erp.securitytokens;

TRUNCATE kurakura_kl_test_erp.`sellthroughsupport`;
INSERT INTO kurakura_kl_test_erp.sellthroughsupport SELECT * FROM kurakura_kl_erp.sellthroughsupport;

TRUNCATE kurakura_kl_test_erp.`shipmentcharges`;
INSERT INTO kurakura_kl_test_erp.shipmentcharges SELECT * FROM kurakura_kl_erp.shipmentcharges;

TRUNCATE kurakura_kl_test_erp.`shipments`;
INSERT INTO kurakura_kl_test_erp.shipments SELECT * FROM kurakura_kl_erp.shipments;

TRUNCATE kurakura_kl_test_erp.`shippers`;
INSERT INTO kurakura_kl_test_erp.shippers SELECT * FROM kurakura_kl_erp.shippers;

TRUNCATE kurakura_kl_test_erp.`stockcategory`;
INSERT INTO kurakura_kl_test_erp.stockcategory SELECT * FROM kurakura_kl_erp.stockcategory;

TRUNCATE kurakura_kl_test_erp.`stockcatproperties`;
INSERT INTO kurakura_kl_test_erp.stockcatproperties SELECT * FROM kurakura_kl_erp.stockcatproperties;

TRUNCATE kurakura_kl_test_erp.`stockcheckfreeze`;
INSERT INTO kurakura_kl_test_erp.stockcheckfreeze SELECT * FROM kurakura_kl_erp.stockcheckfreeze;

TRUNCATE kurakura_kl_test_erp.`stockcounts`;
INSERT INTO kurakura_kl_test_erp.stockcounts SELECT * FROM kurakura_kl_erp.stockcounts;

TRUNCATE kurakura_kl_test_erp.`stockdescriptiontranslations`;
INSERT INTO kurakura_kl_test_erp.stockdescriptiontranslations SELECT * FROM kurakura_kl_erp.stockdescriptiontranslations;

TRUNCATE kurakura_kl_test_erp.`stockitemproperties`;
INSERT INTO kurakura_kl_test_erp.stockitemproperties SELECT * FROM kurakura_kl_erp.stockitemproperties;

TRUNCATE kurakura_kl_test_erp.`stockmaster`;
INSERT INTO kurakura_kl_test_erp.stockmaster SELECT * FROM kurakura_kl_erp.stockmaster;

TRUNCATE kurakura_kl_test_erp.`stockmoves`;
INSERT INTO kurakura_kl_test_erp.stockmoves SELECT * FROM kurakura_kl_erp.stockmoves WHERE trandate >= "2017-01-01";

TRUNCATE kurakura_kl_test_erp.`stockmovestaxes`;
INSERT INTO kurakura_kl_test_erp.stockmovestaxes SELECT * FROM kurakura_kl_erp.stockmovestaxes WHERE stkmoveno >= 1670146;

TRUNCATE kurakura_kl_test_erp.`stockrequest`;
INSERT INTO kurakura_kl_test_erp.stockrequest SELECT * FROM kurakura_kl_erp.stockrequest;

TRUNCATE kurakura_kl_test_erp.`stockrequestitems`;
INSERT INTO kurakura_kl_test_erp.stockrequestitems SELECT * FROM kurakura_kl_erp.stockrequestitems WHERE dispatchid >= 15000;

TRUNCATE kurakura_kl_test_erp.`stockserialitems`;
INSERT INTO kurakura_kl_test_erp.stockserialitems SELECT * FROM kurakura_kl_erp.stockserialitems;

TRUNCATE kurakura_kl_test_erp.`stockserialmoves`;
INSERT INTO kurakura_kl_test_erp.stockserialmoves SELECT * FROM kurakura_kl_erp.stockserialmoves;

TRUNCATE kurakura_kl_test_erp.`suppallocs`;
INSERT INTO kurakura_kl_test_erp.suppallocs SELECT * FROM kurakura_kl_erp.suppallocs;

TRUNCATE kurakura_kl_test_erp.`suppinvstogrn`;
INSERT INTO kurakura_kl_test_erp.suppinvstogrn SELECT * FROM kurakura_kl_erp.suppinvstogrn;

TRUNCATE kurakura_kl_test_erp.`suppliercontacts`;
INSERT INTO kurakura_kl_test_erp.suppliercontacts SELECT * FROM kurakura_kl_erp.suppliercontacts;

TRUNCATE kurakura_kl_test_erp.`supplierdiscounts`;
INSERT INTO kurakura_kl_test_erp.supplierdiscounts SELECT * FROM kurakura_kl_erp.supplierdiscounts;

TRUNCATE kurakura_kl_test_erp.`suppliers`;
INSERT INTO kurakura_kl_test_erp.suppliers SELECT * FROM kurakura_kl_erp.suppliers;

TRUNCATE kurakura_kl_test_erp.`suppliertype`;
INSERT INTO kurakura_kl_test_erp.suppliertype SELECT * FROM kurakura_kl_erp.suppliertype;

TRUNCATE kurakura_kl_test_erp.`supptrans`;
INSERT INTO kurakura_kl_test_erp.supptrans SELECT * FROM kurakura_kl_erp.supptrans;

TRUNCATE kurakura_kl_test_erp.`supptranstaxes`;
INSERT INTO kurakura_kl_test_erp.supptranstaxes SELECT * FROM kurakura_kl_erp.supptranstaxes;

TRUNCATE kurakura_kl_test_erp.`systypes`;
INSERT INTO kurakura_kl_test_erp.systypes SELECT * FROM kurakura_kl_erp.systypes;

TRUNCATE kurakura_kl_test_erp.`tags`;
INSERT INTO kurakura_kl_test_erp.tags SELECT * FROM kurakura_kl_erp.tags;

TRUNCATE kurakura_kl_test_erp.`taxauthorities`;
INSERT INTO kurakura_kl_test_erp.taxauthorities SELECT * FROM kurakura_kl_erp.taxauthorities;

TRUNCATE kurakura_kl_test_erp.`taxauthrates`;
INSERT INTO kurakura_kl_test_erp.taxauthrates SELECT * FROM kurakura_kl_erp.taxauthrates;

TRUNCATE kurakura_kl_test_erp.`taxcategories`;
INSERT INTO kurakura_kl_test_erp.taxcategories SELECT * FROM kurakura_kl_erp.taxcategories;

TRUNCATE kurakura_kl_test_erp.`taxgroups`;
INSERT INTO kurakura_kl_test_erp.taxgroups SELECT * FROM kurakura_kl_erp.taxgroups;

TRUNCATE kurakura_kl_test_erp.`taxgrouptaxes`;
INSERT INTO kurakura_kl_test_erp.taxgrouptaxes SELECT * FROM kurakura_kl_erp.taxgrouptaxes;

TRUNCATE kurakura_kl_test_erp.`taxprovinces`;
INSERT INTO kurakura_kl_test_erp.taxprovinces SELECT * FROM kurakura_kl_erp.taxprovinces;

TRUNCATE kurakura_kl_test_erp.`tenderitems`;
INSERT INTO kurakura_kl_test_erp.tenderitems SELECT * FROM kurakura_kl_erp.tenderitems;

TRUNCATE kurakura_kl_test_erp.`tenders`;
INSERT INTO kurakura_kl_test_erp.tenders SELECT * FROM kurakura_kl_erp.tenders;

TRUNCATE kurakura_kl_test_erp.`tendersuppliers`;
INSERT INTO kurakura_kl_test_erp.tendersuppliers SELECT * FROM kurakura_kl_erp.tendersuppliers;

TRUNCATE kurakura_kl_test_erp.`unitsofdimension`;
INSERT INTO kurakura_kl_test_erp.unitsofdimension SELECT * FROM kurakura_kl_erp.unitsofdimension;

TRUNCATE kurakura_kl_test_erp.`unitsofmeasure`;
INSERT INTO kurakura_kl_test_erp.unitsofmeasure SELECT * FROM kurakura_kl_erp.unitsofmeasure;

TRUNCATE kurakura_kl_test_erp.`woitems`;
INSERT INTO kurakura_kl_test_erp.woitems SELECT * FROM kurakura_kl_erp.woitems;

TRUNCATE kurakura_kl_test_erp.`worequirements`;
INSERT INTO kurakura_kl_test_erp.worequirements SELECT * FROM kurakura_kl_erp.worequirements;

TRUNCATE kurakura_kl_test_erp.`workcentres`;
INSERT INTO kurakura_kl_test_erp.workcentres SELECT * FROM kurakura_kl_erp.workcentres;

TRUNCATE kurakura_kl_test_erp.`workorders`;
INSERT INTO kurakura_kl_test_erp.workorders SELECT * FROM kurakura_kl_erp.workorders;

TRUNCATE kurakura_kl_test_erp.`woserialnos`;
INSERT INTO kurakura_kl_test_erp.woserialnos SELECT * FROM kurakura_kl_erp.woserialnos;

TRUNCATE kurakura_kl_test_erp.`www_users`;
INSERT INTO kurakura_kl_test_erp.www_users SELECT * FROM kurakura_kl_erp.www_users;

UPDATE  `config` SET  `confvalue` =  'companies/kurakura_kl_test_erp/part_pics' WHERE  `confname` =  'part_pics_dir';
UPDATE  `config` SET  `confvalue` =  'companies/kurakura_kl_test_erp/reports' WHERE  `confname` =  'reports_dir';
UPDATE  `config` SET  `confvalue` =  'companies/kurakura_kl_test_erp/logs' WHERE  `confname` =  'LogPath';

UPDATE  `config` SET  `confvalue` =  'TEST SHOP' WHERE  `confname` =  'ShopName';
UPDATE  `config` SET  `confvalue` =  'TEST SHOP' WHERE  `confname` =  'ShopTitle';

UPDATE  `config` SET  `confvalue` =  '' WHERE  `confname` =  'InventoryManagerEmail';
UPDATE  `config` SET  `confvalue` =  '' WHERE  `confname` =  'FactoryManagerEmail';
UPDATE  `config` SET  `confvalue` =  '' WHERE  `confname` =  'PurchasingManagerEmail';

UPDATE  `config` SET  `confvalue` =  'test' WHERE  `confname` =  'ShopMode';
UPDATE  `config` SET  `confvalue` =  '1372497542' WHERE  `confname` =  'ShopPayPalPassword';
UPDATE  `config` SET  `confvalue` =  'AKh80SD3d.pLz9oyaerqiR90yzDdARP3knOWMSTyjcbBNEns94xTl6WW' WHERE  `confname` =  'ShopPayPalSignature';
UPDATE  `config` SET  `confvalue` =  'testmerchant_api1.kapal-laut.com' WHERE  `confname` =  'ShopPayPalUser';

UPDATE www_users SET theme = "gel";
UPDATE www_users SET blocked = 0 WHERE userid LIKE "999%";

SET FOREIGN_KEY_CHECKS=1;

SET FOREIGN_KEY_CHECKS=0;
  
TRUNCATE kurakura_kl_test_erpolddata.`accountgroups`;
INSERT INTO kurakura_kl_test_erpolddata.accountgroups SELECT * FROM kurakura_kl_erpolddata.accountgroups;

TRUNCATE kurakura_kl_test_erpolddata.`accountsection`;
INSERT INTO kurakura_kl_test_erpolddata.accountsection SELECT * FROM kurakura_kl_erpolddata.accountsection;

TRUNCATE kurakura_kl_test_erpolddata.`areas`;
INSERT INTO kurakura_kl_test_erpolddata.areas SELECT * FROM kurakura_kl_erpolddata.areas;

TRUNCATE kurakura_kl_test_erpolddata.`audittrail`;
INSERT INTO kurakura_kl_test_erpolddata.audittrail SELECT * FROM kurakura_kl_erpolddata.audittrail;

TRUNCATE kurakura_kl_test_erpolddata.`bankaccounts`;
INSERT INTO kurakura_kl_test_erpolddata.bankaccounts SELECT * FROM kurakura_kl_erpolddata.bankaccounts;

TRUNCATE kurakura_kl_test_erpolddata.`bankaccountusers`;
INSERT INTO kurakura_kl_test_erpolddata.bankaccountusers SELECT * FROM kurakura_kl_erpolddata.bankaccountusers;

TRUNCATE kurakura_kl_test_erpolddata.`banktrans`;
INSERT INTO kurakura_kl_test_erpolddata.banktrans SELECT * FROM kurakura_kl_erpolddata.banktrans;

TRUNCATE kurakura_kl_test_erpolddata.`bom`;
INSERT INTO kurakura_kl_test_erpolddata.bom SELECT * FROM kurakura_kl_erpolddata.bom;

TRUNCATE kurakura_kl_test_erpolddata.`buckets`;
INSERT INTO kurakura_kl_test_erpolddata.buckets SELECT * FROM kurakura_kl_erpolddata.buckets;

TRUNCATE kurakura_kl_test_erpolddata.`chartdetails`;
INSERT INTO kurakura_kl_test_erpolddata.chartdetails SELECT * FROM kurakura_kl_erpolddata.chartdetails;

TRUNCATE kurakura_kl_test_erpolddata.`chartmaster`;
INSERT INTO kurakura_kl_test_erpolddata.chartmaster SELECT * FROM kurakura_kl_erpolddata.chartmaster;

TRUNCATE kurakura_kl_test_erpolddata.`chartmasterIK`;
INSERT INTO kurakura_kl_test_erpolddata.chartmasterIK SELECT * FROM kurakura_kl_erpolddata.chartmasterIK;

TRUNCATE kurakura_kl_test_erpolddata.`chartmasterPI`;
INSERT INTO kurakura_kl_test_erpolddata.chartmasterPI SELECT * FROM kurakura_kl_erpolddata.chartmasterPI;

TRUNCATE kurakura_kl_test_erpolddata.`chartmasterPMA`;
INSERT INTO kurakura_kl_test_erpolddata.chartmasterPMA SELECT * FROM kurakura_kl_erpolddata.chartmasterPMA;

TRUNCATE kurakura_kl_test_erpolddata.`chartmasterPT`;
INSERT INTO kurakura_kl_test_erpolddata.chartmasterPT SELECT * FROM kurakura_kl_erpolddata.chartmasterPT;

TRUNCATE kurakura_kl_test_erpolddata.`chartmasterSMH`;
INSERT INTO kurakura_kl_test_erpolddata.chartmasterSMH SELECT * FROM kurakura_kl_erpolddata.chartmasterSMH;

TRUNCATE kurakura_kl_test_erpolddata.`cogsglpostings`;
INSERT INTO kurakura_kl_test_erpolddata.cogsglpostings SELECT * FROM kurakura_kl_erpolddata.cogsglpostings;

TRUNCATE kurakura_kl_test_erpolddata.`companies`;
INSERT INTO kurakura_kl_test_erpolddata.companies SELECT * FROM kurakura_kl_erpolddata.companies;

TRUNCATE kurakura_kl_test_erpolddata.`config`;
INSERT INTO kurakura_kl_test_erpolddata.config SELECT * FROM kurakura_kl_erpolddata.config;

TRUNCATE kurakura_kl_test_erpolddata.`contractbom`;
INSERT INTO kurakura_kl_test_erpolddata.contractbom SELECT * FROM kurakura_kl_erpolddata.contractbom;

TRUNCATE kurakura_kl_test_erpolddata.`contractcharges`;
INSERT INTO kurakura_kl_test_erpolddata.contractcharges SELECT * FROM kurakura_kl_erpolddata.contractcharges;

TRUNCATE kurakura_kl_test_erpolddata.`contractreqts`;
INSERT INTO kurakura_kl_test_erpolddata.contractreqts SELECT * FROM kurakura_kl_erpolddata.contractreqts;

TRUNCATE kurakura_kl_test_erpolddata.`contracts`;
INSERT INTO kurakura_kl_test_erpolddata.contracts SELECT * FROM kurakura_kl_erpolddata.contracts;

TRUNCATE kurakura_kl_test_erpolddata.`currencies`;
INSERT INTO kurakura_kl_test_erpolddata.currencies SELECT * FROM kurakura_kl_erpolddata.currencies;

TRUNCATE kurakura_kl_test_erpolddata.`custallocns`;
INSERT INTO kurakura_kl_test_erpolddata.custallocns SELECT * FROM kurakura_kl_erpolddata.custallocns;

TRUNCATE kurakura_kl_test_erpolddata.`custbranch`;
INSERT INTO kurakura_kl_test_erpolddata.custbranch SELECT * FROM kurakura_kl_erpolddata.custbranch;

TRUNCATE kurakura_kl_test_erpolddata.`custcontacts`;
INSERT INTO kurakura_kl_test_erpolddata.custcontacts SELECT * FROM kurakura_kl_erpolddata.custcontacts;

TRUNCATE kurakura_kl_test_erpolddata.`custitem`;
INSERT INTO kurakura_kl_test_erpolddata.custitem SELECT * FROM kurakura_kl_erpolddata.custitem;

TRUNCATE kurakura_kl_test_erpolddata.`custnotes`;
INSERT INTO kurakura_kl_test_erpolddata.custnotes SELECT * FROM kurakura_kl_erpolddata.custnotes;

TRUNCATE kurakura_kl_test_erpolddata.`debtorsmaster`;
INSERT INTO kurakura_kl_test_erpolddata.debtorsmaster SELECT * FROM kurakura_kl_erpolddata.debtorsmaster;

TRUNCATE kurakura_kl_test_erpolddata.`debtortrans`;
INSERT INTO kurakura_kl_test_erpolddata.debtortrans SELECT * FROM kurakura_kl_erpolddata.debtortrans;

TRUNCATE kurakura_kl_test_erpolddata.`debtortranstaxes`;
INSERT INTO kurakura_kl_test_erpolddata.debtortranstaxes SELECT * FROM kurakura_kl_erpolddata.debtortranstaxes;

TRUNCATE kurakura_kl_test_erpolddata.`debtortype`;
INSERT INTO kurakura_kl_test_erpolddata.debtortype SELECT * FROM kurakura_kl_erpolddata.debtortype;

TRUNCATE kurakura_kl_test_erpolddata.`debtortypenotes`;
INSERT INTO kurakura_kl_test_erpolddata.debtortypenotes SELECT * FROM kurakura_kl_erpolddata.debtortypenotes;

TRUNCATE kurakura_kl_test_erpolddata.`deliverynotes`;
INSERT INTO kurakura_kl_test_erpolddata.deliverynotes SELECT * FROM kurakura_kl_erpolddata.deliverynotes;

TRUNCATE kurakura_kl_test_erpolddata.`departments`;
INSERT INTO kurakura_kl_test_erpolddata.departments SELECT * FROM kurakura_kl_erpolddata.departments;

TRUNCATE kurakura_kl_test_erpolddata.`discountmatrix`;
INSERT INTO kurakura_kl_test_erpolddata.discountmatrix SELECT * FROM kurakura_kl_erpolddata.discountmatrix;

TRUNCATE kurakura_kl_test_erpolddata.`edi_orders_segs`;
INSERT INTO kurakura_kl_test_erpolddata.edi_orders_segs SELECT * FROM kurakura_kl_erpolddata.edi_orders_segs;

TRUNCATE kurakura_kl_test_erpolddata.`ediitemmapping`;
INSERT INTO kurakura_kl_test_erpolddata.ediitemmapping SELECT * FROM kurakura_kl_erpolddata.ediitemmapping;

TRUNCATE kurakura_kl_test_erpolddata.`edimessageformat`;
INSERT INTO kurakura_kl_test_erpolddata.edimessageformat SELECT * FROM kurakura_kl_erpolddata.edimessageformat;

TRUNCATE kurakura_kl_test_erpolddata.`edi_orders_seg_groups`;
INSERT INTO kurakura_kl_test_erpolddata.edi_orders_seg_groups SELECT * FROM kurakura_kl_erpolddata.edi_orders_seg_groups;

TRUNCATE kurakura_kl_test_erpolddata.`emailsettings`;
INSERT INTO kurakura_kl_test_erpolddata.emailsettings SELECT * FROM kurakura_kl_erpolddata.emailsettings;

TRUNCATE kurakura_kl_test_erpolddata.`factorcompanies`;
INSERT INTO kurakura_kl_test_erpolddata.factorcompanies SELECT * FROM kurakura_kl_erpolddata.factorcompanies;

TRUNCATE kurakura_kl_test_erpolddata.`fixedassetcategories`;
INSERT INTO kurakura_kl_test_erpolddata.fixedassetcategories SELECT * FROM kurakura_kl_erpolddata.fixedassetcategories;

TRUNCATE kurakura_kl_test_erpolddata.`fixedassetlocations`;
INSERT INTO kurakura_kl_test_erpolddata.fixedassetlocations SELECT * FROM kurakura_kl_erpolddata.fixedassetlocations;

TRUNCATE kurakura_kl_test_erpolddata.`fixedassets`;
INSERT INTO kurakura_kl_test_erpolddata.fixedassets SELECT * FROM kurakura_kl_erpolddata.fixedassets;

TRUNCATE kurakura_kl_test_erpolddata.`fixedassettasks`;
INSERT INTO kurakura_kl_test_erpolddata.fixedassettasks SELECT * FROM kurakura_kl_erpolddata.fixedassettasks;

TRUNCATE kurakura_kl_test_erpolddata.`fixedassettrans`;
INSERT INTO kurakura_kl_test_erpolddata.fixedassettrans SELECT * FROM kurakura_kl_erpolddata.fixedassettrans;

TRUNCATE kurakura_kl_test_erpolddata.`freightcosts`;
INSERT INTO kurakura_kl_test_erpolddata.freightcosts SELECT * FROM kurakura_kl_erpolddata.freightcosts;

TRUNCATE kurakura_kl_test_erpolddata.`geocode_param`;
INSERT INTO kurakura_kl_test_erpolddata.geocode_param SELECT * FROM kurakura_kl_erpolddata.geocode_param;

TRUNCATE kurakura_kl_test_erpolddata.`glaccountusers`;
INSERT INTO kurakura_kl_test_erpolddata.glaccountusers SELECT * FROM kurakura_kl_erpolddata.glaccountusers;

TRUNCATE kurakura_kl_test_erpolddata.`gltrans`;
INSERT INTO kurakura_kl_test_erpolddata.gltrans SELECT * FROM kurakura_kl_erpolddata.gltrans WHERE periodno <= 30;
INSERT INTO kurakura_kl_test_erpolddata.gltrans SELECT * FROM kurakura_kl_erpolddata.gltrans WHERE periodno > 30 AND periodno <= 60;
INSERT INTO kurakura_kl_test_erpolddata.gltrans SELECT * FROM kurakura_kl_erpolddata.gltrans WHERE periodno > 60 AND periodno <= 80;
INSERT INTO kurakura_kl_test_erpolddata.gltrans SELECT * FROM kurakura_kl_erpolddata.gltrans WHERE periodno > 80 AND periodno <= 90;
INSERT INTO kurakura_kl_test_erpolddata.gltrans SELECT * FROM kurakura_kl_erpolddata.gltrans WHERE periodno > 90 AND periodno <= 100;
INSERT INTO kurakura_kl_test_erpolddata.gltrans SELECT * FROM kurakura_kl_erpolddata.gltrans WHERE periodno > 100 AND periodno <= 110;
INSERT INTO kurakura_kl_test_erpolddata.gltrans SELECT * FROM kurakura_kl_erpolddata.gltrans WHERE periodno > 110;

TRUNCATE kurakura_kl_test_erpolddata.`grns`;
INSERT INTO kurakura_kl_test_erpolddata.grns SELECT * FROM kurakura_kl_erpolddata.grns;

TRUNCATE kurakura_kl_test_erpolddata.`holdreasons`;
INSERT INTO kurakura_kl_test_erpolddata.holdreasons SELECT * FROM kurakura_kl_erpolddata.holdreasons;

TRUNCATE kurakura_kl_test_erpolddata.`internalstockcatrole`;
INSERT INTO kurakura_kl_test_erpolddata.internalstockcatrole SELECT * FROM kurakura_kl_erpolddata.internalstockcatrole;

TRUNCATE kurakura_kl_test_erpolddata.`kladjustrl`;
INSERT INTO kurakura_kl_test_erpolddata.kladjustrl SELECT * FROM kurakura_kl_erpolddata.kladjustrl;

TRUNCATE kurakura_kl_test_erpolddata.`klchangeprice`;
INSERT INTO kurakura_kl_test_erpolddata.klchangeprice SELECT * FROM kurakura_kl_erpolddata.klchangeprice;

TRUNCATE kurakura_kl_test_erpolddata.`kladjustrl`;
INSERT INTO kurakura_kl_test_erpolddata.kladjustrl SELECT * FROM kurakura_kl_erpolddata.kladjustrl;

TRUNCATE kurakura_kl_test_erpolddata.`klconsignment`;
INSERT INTO kurakura_kl_test_erpolddata.klconsignment SELECT * FROM kurakura_kl_erpolddata.klconsignment;

TRUNCATE kurakura_kl_test_erpolddata.`klfreeexchanges`;
INSERT INTO kurakura_kl_test_erpolddata.klfreeexchanges SELECT * FROM kurakura_kl_erpolddata.klfreeexchanges;

TRUNCATE kurakura_kl_test_erpolddata.`klmovetodiscount20`;
INSERT INTO kurakura_kl_test_erpolddata.klmovetodiscount20 SELECT * FROM kurakura_kl_erpolddata.klmovetodiscount20;

TRUNCATE kurakura_kl_test_erpolddata.`klmovetodiscount50`;
INSERT INTO kurakura_kl_test_erpolddata.klmovetodiscount50 SELECT * FROM kurakura_kl_erpolddata.klmovetodiscount50;

TRUNCATE kurakura_kl_test_erpolddata.`klmovetodiscount80`;
INSERT INTO kurakura_kl_test_erpolddata.klmovetodiscount80 SELECT * FROM kurakura_kl_erpolddata.klmovetodiscount80;

TRUNCATE kurakura_kl_test_erpolddata.`klolddatapurged`;
INSERT INTO kurakura_kl_test_erpolddata.klolddatapurged SELECT * FROM kurakura_kl_erpolddata.klolddatapurged;

TRUNCATE kurakura_kl_test_erpolddata.`klonlinepartners`;
INSERT INTO kurakura_kl_test_erpolddata.klonlinepartners SELECT * FROM kurakura_kl_erpolddata.klonlinepartners;

TRUNCATE kurakura_kl_test_erpolddata.`klpostatus`;
INSERT INTO kurakura_kl_test_erpolddata.klpostatus SELECT * FROM kurakura_kl_erpolddata.klpostatus;

TRUNCATE kurakura_kl_test_erpolddata.`klretailcustomers`;
INSERT INTO kurakura_kl_test_erpolddata.klretailcustomers SELECT * FROM kurakura_kl_erpolddata.klretailcustomers;

TRUNCATE kurakura_kl_test_erpolddata.`klretailpartners`;
INSERT INTO kurakura_kl_test_erpolddata.klretailpartners SELECT * FROM kurakura_kl_erpolddata.klretailpartners;

TRUNCATE kurakura_kl_test_erpolddata.`klrevisedemaildomains`;
INSERT INTO kurakura_kl_test_erpolddata.klrevisedemaildomains SELECT * FROM kurakura_kl_erpolddata.klrevisedemaildomains;

TRUNCATE kurakura_kl_test_erpolddata.`klsalesperformance`;
INSERT INTO kurakura_kl_test_erpolddata.klsalesperformance SELECT * FROM kurakura_kl_erpolddata.klsalesperformance;

TRUNCATE kurakura_kl_test_erpolddata.`labelfields`;
INSERT INTO kurakura_kl_test_erpolddata.labelfields SELECT * FROM kurakura_kl_erpolddata.labelfields;

TRUNCATE kurakura_kl_test_erpolddata.`labels`;
INSERT INTO kurakura_kl_test_erpolddata.labels SELECT * FROM kurakura_kl_erpolddata.labels;

INSERT INTO kurakura_kl_test_erpolddata.lastcostrollup SELECT * FROM kurakura_kl_erpolddata.lastcostrollup;
TRUNCATE kurakura_kl_test_erpolddata.`lastcostrollup`;

INSERT INTO kurakura_kl_test_erpolddata.levels SELECT * FROM kurakura_kl_erpolddata.levels;
TRUNCATE kurakura_kl_test_erpolddata.`levels`;

TRUNCATE kurakura_kl_test_erpolddata.`locations`;
INSERT INTO kurakura_kl_test_erpolddata.locations SELECT * FROM kurakura_kl_erpolddata.locations;

TRUNCATE kurakura_kl_test_erpolddata.`locationtypes`;
INSERT INTO kurakura_kl_test_erpolddata.locationtypes SELECT * FROM kurakura_kl_erpolddata.locationtypes;

TRUNCATE kurakura_kl_test_erpolddata.`locationusers`;
INSERT INTO kurakura_kl_test_erpolddata.locationusers SELECT * FROM kurakura_kl_erpolddata.locationusers;

TRUNCATE kurakura_kl_test_erpolddata.`locationzones`;
INSERT INTO kurakura_kl_test_erpolddata.locationzones SELECT * FROM kurakura_kl_erpolddata.locationzones;

TRUNCATE kurakura_kl_test_erpolddata.`locstock`;
INSERT INTO kurakura_kl_test_erpolddata.locstock SELECT * FROM kurakura_kl_erpolddata.locstock;

TRUNCATE kurakura_kl_test_erpolddata.`loctransfercancellations`;
INSERT INTO kurakura_kl_test_erpolddata.loctransfercancellations SELECT * FROM kurakura_kl_erpolddata.loctransfercancellations;

TRUNCATE kurakura_kl_test_erpolddata.`loctransfers`;
INSERT INTO kurakura_kl_test_erpolddata.loctransfers SELECT * FROM kurakura_kl_erpolddata.loctransfers;

TRUNCATE kurakura_kl_test_erpolddata.`mailgroupdetails`;
INSERT INTO kurakura_kl_test_erpolddata.mailgroupdetails SELECT * FROM kurakura_kl_erpolddata.mailgroupdetails;

TRUNCATE kurakura_kl_test_erpolddata.`mailgroups`;
INSERT INTO kurakura_kl_test_erpolddata.mailgroups SELECT * FROM kurakura_kl_erpolddata.mailgroups;

TRUNCATE kurakura_kl_test_erpolddata.`manufacturers`;
INSERT INTO kurakura_kl_test_erpolddata.manufacturers SELECT * FROM kurakura_kl_erpolddata.manufacturers;

TRUNCATE kurakura_kl_test_erpolddata.`mrpcalendar`;
INSERT INTO kurakura_kl_test_erpolddata.mrpcalendar SELECT * FROM kurakura_kl_erpolddata.mrpcalendar;

TRUNCATE kurakura_kl_test_erpolddata.`mrpdemands`;
INSERT INTO kurakura_kl_test_erpolddata.mrpdemands SELECT * FROM kurakura_kl_erpolddata.mrpdemands;

TRUNCATE kurakura_kl_test_erpolddata.`mrpdemandtypes`;
INSERT INTO kurakura_kl_test_erpolddata.mrpdemandtypes SELECT * FROM kurakura_kl_erpolddata.mrpdemandtypes;

TRUNCATE kurakura_kl_test_erpolddata.`mrpparameters`;
INSERT INTO kurakura_kl_test_erpolddata.mrpparameters SELECT * FROM kurakura_kl_erpolddata.mrpparameters;

TRUNCATE kurakura_kl_test_erpolddata.`mrpplannedorders`;
INSERT INTO kurakura_kl_test_erpolddata.mrpplannedorders SELECT * FROM kurakura_kl_erpolddata.mrpplannedorders;

TRUNCATE kurakura_kl_test_erpolddata.`mrprequirements`;
INSERT INTO kurakura_kl_test_erpolddata.mrprequirements SELECT * FROM kurakura_kl_erpolddata.mrprequirements;

TRUNCATE kurakura_kl_test_erpolddata.`mrpsupplies`;
INSERT INTO kurakura_kl_test_erpolddata.mrpsupplies SELECT * FROM kurakura_kl_erpolddata.mrpsupplies;

TRUNCATE kurakura_kl_test_erpolddata.`offers`;
INSERT INTO kurakura_kl_test_erpolddata.offers SELECT * FROM kurakura_kl_erpolddata.offers;

TRUNCATE kurakura_kl_test_erpolddata.`orderdeliverydifferenceslog`;
INSERT INTO kurakura_kl_test_erpolddata.orderdeliverydifferenceslog SELECT * FROM kurakura_kl_erpolddata.orderdeliverydifferenceslog;

TRUNCATE kurakura_kl_test_erpolddata.`packagingused`;
INSERT INTO kurakura_kl_test_erpolddata.packagingused SELECT * FROM kurakura_kl_erpolddata.packagingused;

TRUNCATE kurakura_kl_test_erpolddata.`paymentmethods`;
INSERT INTO kurakura_kl_test_erpolddata.paymentmethods SELECT * FROM kurakura_kl_erpolddata.paymentmethods;

TRUNCATE kurakura_kl_test_erpolddata.`paymentterms`;
INSERT INTO kurakura_kl_test_erpolddata.paymentterms SELECT * FROM kurakura_kl_erpolddata.paymentterms;

TRUNCATE kurakura_kl_test_erpolddata.`pcashdetails`;
INSERT INTO kurakura_kl_test_erpolddata.pcashdetails SELECT * FROM kurakura_kl_erpolddata.pcashdetails;

TRUNCATE kurakura_kl_test_erpolddata.`pcexpenses`;
INSERT INTO kurakura_kl_test_erpolddata.pcexpenses SELECT * FROM kurakura_kl_erpolddata.pcexpenses;

TRUNCATE kurakura_kl_test_erpolddata.`pctabexpenses`;
INSERT INTO kurakura_kl_test_erpolddata.pctabexpenses SELECT * FROM kurakura_kl_erpolddata.pctabexpenses;

TRUNCATE kurakura_kl_test_erpolddata.`pcsalaries`;
INSERT INTO kurakura_kl_test_erpolddata.pcsalaries SELECT * FROM kurakura_kl_erpolddata.pcsalaries;

TRUNCATE kurakura_kl_test_erpolddata.`pctabs`;
INSERT INTO kurakura_kl_test_erpolddata.pctabs SELECT * FROM kurakura_kl_erpolddata.pctabs;

TRUNCATE kurakura_kl_test_erpolddata.`pctypetabs`;
INSERT INTO kurakura_kl_test_erpolddata.pctypetabs SELECT * FROM kurakura_kl_erpolddata.pctypetabs;

TRUNCATE kurakura_kl_test_erpolddata.`periods`;
INSERT INTO kurakura_kl_test_erpolddata.periods SELECT * FROM kurakura_kl_erpolddata.periods;

TRUNCATE kurakura_kl_test_erpolddata.`pickinglistdetails`;
INSERT INTO kurakura_kl_test_erpolddata.pickinglistdetails SELECT * FROM kurakura_kl_erpolddata.pickinglistdetails;

TRUNCATE kurakura_kl_test_erpolddata.`pickinglists`;
INSERT INTO kurakura_kl_test_erpolddata.pickinglists SELECT * FROM kurakura_kl_erpolddata.pickinglists;

TRUNCATE kurakura_kl_test_erpolddata.`pricematrix`;
INSERT INTO kurakura_kl_test_erpolddata.pricematrix SELECT * FROM kurakura_kl_erpolddata.pricematrix;

TRUNCATE kurakura_kl_test_erpolddata.`prices`;
INSERT INTO kurakura_kl_test_erpolddata.prices SELECT * FROM kurakura_kl_erpolddata.prices;

TRUNCATE kurakura_kl_test_erpolddata.`prodspecs`;
INSERT INTO kurakura_kl_test_erpolddata.prodspecs SELECT * FROM kurakura_kl_erpolddata.prodspecs;

TRUNCATE kurakura_kl_test_erpolddata.`purchdata`;
INSERT INTO kurakura_kl_test_erpolddata.purchdata SELECT * FROM kurakura_kl_erpolddata.purchdata;

TRUNCATE kurakura_kl_test_erpolddata.`purchorderauth`;
INSERT INTO kurakura_kl_test_erpolddata.purchorderauth SELECT * FROM kurakura_kl_erpolddata.purchorderauth;

TRUNCATE kurakura_kl_test_erpolddata.`purchorderdetails`;
INSERT INTO kurakura_kl_test_erpolddata.purchorderdetails SELECT * FROM kurakura_kl_erpolddata.purchorderdetails;

TRUNCATE kurakura_kl_test_erpolddata.`purchorders`;
INSERT INTO kurakura_kl_test_erpolddata.purchorders SELECT * FROM kurakura_kl_erpolddata.purchorders;

TRUNCATE kurakura_kl_test_erpolddata.`qasamples`;
INSERT INTO kurakura_kl_test_erpolddata.qasamples SELECT * FROM kurakura_kl_erpolddata.qasamples;

TRUNCATE kurakura_kl_test_erpolddata.`qatests`;
INSERT INTO kurakura_kl_test_erpolddata.qatests SELECT * FROM kurakura_kl_erpolddata.qatests;

TRUNCATE kurakura_kl_test_erpolddata.`recurringsalesorders`;
INSERT INTO kurakura_kl_test_erpolddata.recurringsalesorders SELECT * FROM kurakura_kl_erpolddata.recurringsalesorders;

TRUNCATE kurakura_kl_test_erpolddata.`recurrsalesorderdetails`;
INSERT INTO kurakura_kl_test_erpolddata.recurrsalesorderdetails SELECT * FROM kurakura_kl_erpolddata.recurrsalesorderdetails;

TRUNCATE kurakura_kl_test_erpolddata.`relateditems`;
INSERT INTO kurakura_kl_test_erpolddata.relateditems SELECT * FROM kurakura_kl_erpolddata.relateditems;

TRUNCATE kurakura_kl_test_erpolddata.`reportcolumns`;
INSERT INTO kurakura_kl_test_erpolddata.reportcolumns SELECT * FROM kurakura_kl_erpolddata.reportcolumns;

TRUNCATE kurakura_kl_test_erpolddata.`reportfields`;
INSERT INTO kurakura_kl_test_erpolddata.reportfields SELECT * FROM kurakura_kl_erpolddata.reportfields;

TRUNCATE kurakura_kl_test_erpolddata.`reportheaders`;
INSERT INTO kurakura_kl_test_erpolddata.reportheaders SELECT * FROM kurakura_kl_erpolddata.reportheaders;

TRUNCATE kurakura_kl_test_erpolddata.`reportlets`;
INSERT INTO kurakura_kl_test_erpolddata.reportlets SELECT * FROM kurakura_kl_erpolddata.reportlets;

TRUNCATE kurakura_kl_test_erpolddata.`reportlinks`;
INSERT INTO kurakura_kl_test_erpolddata.reportlinks SELECT * FROM kurakura_kl_erpolddata.reportlinks;

TRUNCATE kurakura_kl_test_erpolddata.`reports`;
INSERT INTO kurakura_kl_test_erpolddata.reports SELECT * FROM kurakura_kl_erpolddata.reports;

TRUNCATE kurakura_kl_test_erpolddata.`returnitemreasons`;
INSERT INTO kurakura_kl_test_erpolddata.returnitemreasons SELECT * FROM kurakura_kl_erpolddata.returnitemreasons;

TRUNCATE kurakura_kl_test_erpolddata.`returneditems`;
INSERT INTO kurakura_kl_test_erpolddata.returneditems SELECT * FROM kurakura_kl_erpolddata.returneditems;

TRUNCATE kurakura_kl_test_erpolddata.`salariescalculated`;
INSERT INTO kurakura_kl_test_erpolddata.salariescalculated SELECT * FROM kurakura_kl_erpolddata.salariescalculated;

TRUNCATE kurakura_kl_test_erpolddata.`salesanalysis`;
INSERT INTO kurakura_kl_test_erpolddata.salesanalysis SELECT * FROM kurakura_kl_erpolddata.salesanalysis;

TRUNCATE kurakura_kl_test_erpolddata.`salescat`;
INSERT INTO kurakura_kl_test_erpolddata.salescat SELECT * FROM kurakura_kl_erpolddata.salescat;

TRUNCATE kurakura_kl_test_erpolddata.`salescatprod`;
INSERT INTO kurakura_kl_test_erpolddata.salescatprod SELECT * FROM kurakura_kl_erpolddata.salescatprod;

TRUNCATE kurakura_kl_test_erpolddata.`salescattranslations`;
INSERT INTO kurakura_kl_test_erpolddata.salescattranslations SELECT * FROM kurakura_kl_erpolddata.salescattranslations;

TRUNCATE kurakura_kl_test_erpolddata.`salesglpostings`;
INSERT INTO kurakura_kl_test_erpolddata.salesglpostings SELECT * FROM kurakura_kl_erpolddata.salesglpostings;

TRUNCATE kurakura_kl_test_erpolddata.`salesman`;
INSERT INTO kurakura_kl_test_erpolddata.salesman SELECT * FROM kurakura_kl_erpolddata.salesman;

TRUNCATE kurakura_kl_test_erpolddata.`salesorderdetails`;
INSERT INTO kurakura_kl_test_erpolddata.salesorderdetails SELECT * FROM kurakura_kl_erpolddata.salesorderdetails;

TRUNCATE kurakura_kl_test_erpolddata.`salesorders`;
INSERT INTO kurakura_kl_test_erpolddata.salesorders SELECT * FROM kurakura_kl_erpolddata.salesorders;

TRUNCATE kurakura_kl_test_erpolddata.`salestypes`;
INSERT INTO kurakura_kl_test_erpolddata.salestypes SELECT * FROM kurakura_kl_erpolddata.salestypes;

TRUNCATE kurakura_kl_test_erpolddata.`sampleresults`;
INSERT INTO kurakura_kl_test_erpolddata.sampleresults SELECT * FROM kurakura_kl_erpolddata.sampleresults;

TRUNCATE kurakura_kl_test_erpolddata.`scripts`;
INSERT INTO kurakura_kl_test_erpolddata.scripts SELECT * FROM kurakura_kl_erpolddata.scripts;

TRUNCATE kurakura_kl_test_erpolddata.`securitygroups`;
INSERT INTO kurakura_kl_test_erpolddata.securitygroups SELECT * FROM kurakura_kl_erpolddata.securitygroups;

TRUNCATE kurakura_kl_test_erpolddata.`securityroles`;
INSERT INTO kurakura_kl_test_erpolddata.securityroles SELECT * FROM kurakura_kl_erpolddata.securityroles;

TRUNCATE kurakura_kl_test_erpolddata.`securitytokens`;
INSERT INTO kurakura_kl_test_erpolddata.securitytokens SELECT * FROM kurakura_kl_erpolddata.securitytokens;

TRUNCATE kurakura_kl_test_erpolddata.`sellthroughsupport`;
INSERT INTO kurakura_kl_test_erpolddata.sellthroughsupport SELECT * FROM kurakura_kl_erpolddata.sellthroughsupport;

TRUNCATE kurakura_kl_test_erpolddata.`shipmentcharges`;
INSERT INTO kurakura_kl_test_erpolddata.shipmentcharges SELECT * FROM kurakura_kl_erpolddata.shipmentcharges;

TRUNCATE kurakura_kl_test_erpolddata.`shipments`;
INSERT INTO kurakura_kl_test_erpolddata.shipments SELECT * FROM kurakura_kl_erpolddata.shipments;

TRUNCATE kurakura_kl_test_erpolddata.`shippers`;
INSERT INTO kurakura_kl_test_erpolddata.shippers SELECT * FROM kurakura_kl_erpolddata.shippers;

TRUNCATE kurakura_kl_test_erpolddata.`stockcategory`;
INSERT INTO kurakura_kl_test_erpolddata.stockcategory SELECT * FROM kurakura_kl_erpolddata.stockcategory;

TRUNCATE kurakura_kl_test_erpolddata.`stockcatproperties`;
INSERT INTO kurakura_kl_test_erpolddata.stockcatproperties SELECT * FROM kurakura_kl_erpolddata.stockcatproperties;

TRUNCATE kurakura_kl_test_erpolddata.`stockcheckfreeze`;
INSERT INTO kurakura_kl_test_erpolddata.stockcheckfreeze SELECT * FROM kurakura_kl_erpolddata.stockcheckfreeze;

TRUNCATE kurakura_kl_test_erpolddata.`stockcounts`;
INSERT INTO kurakura_kl_test_erpolddata.stockcounts SELECT * FROM kurakura_kl_erpolddata.stockcounts;

TRUNCATE kurakura_kl_test_erpolddata.`stockdescriptiontranslations`;
INSERT INTO kurakura_kl_test_erpolddata.stockdescriptiontranslations SELECT * FROM kurakura_kl_erpolddata.stockdescriptiontranslations;

TRUNCATE kurakura_kl_test_erpolddata.`stockitemproperties`;
INSERT INTO kurakura_kl_test_erpolddata.stockitemproperties SELECT * FROM kurakura_kl_erpolddata.stockitemproperties;

TRUNCATE kurakura_kl_test_erpolddata.`stockmaster`;
INSERT INTO kurakura_kl_test_erpolddata.stockmaster SELECT * FROM kurakura_kl_erpolddata.stockmaster;

TRUNCATE kurakura_kl_test_erpolddata.`stockmoves`;
INSERT INTO kurakura_kl_test_erpolddata.stockmoves SELECT * FROM kurakura_kl_erpolddata.stockmoves WHERE prd <= 30;
INSERT INTO kurakura_kl_test_erpolddata.stockmoves SELECT * FROM kurakura_kl_erpolddata.stockmoves WHERE prd > 30 AND prd <= 60;
INSERT INTO kurakura_kl_test_erpolddata.stockmoves SELECT * FROM kurakura_kl_erpolddata.stockmoves WHERE prd > 60 AND prd <= 80;
INSERT INTO kurakura_kl_test_erpolddata.stockmoves SELECT * FROM kurakura_kl_erpolddata.stockmoves WHERE prd > 80 AND prd <= 90;
INSERT INTO kurakura_kl_test_erpolddata.stockmoves SELECT * FROM kurakura_kl_erpolddata.stockmoves WHERE prd > 90 AND prd <= 100;
INSERT INTO kurakura_kl_test_erpolddata.stockmoves SELECT * FROM kurakura_kl_erpolddata.stockmoves WHERE prd > 100 AND prd <= 110;
INSERT INTO kurakura_kl_test_erpolddata.stockmoves SELECT * FROM kurakura_kl_erpolddata.stockmoves WHERE prd > 110;

TRUNCATE kurakura_kl_test_erpolddata.`stockmovestaxes`;
INSERT INTO kurakura_kl_test_erpolddata.stockmovestaxes SELECT * FROM kurakura_kl_erpolddata.stockmovestaxes;

TRUNCATE kurakura_kl_test_erpolddata.`stockrequest`;
INSERT INTO kurakura_kl_test_erpolddata.stockrequest SELECT * FROM kurakura_kl_erpolddata.stockrequest;

TRUNCATE kurakura_kl_test_erpolddata.`stockrequestitems`;
INSERT INTO kurakura_kl_test_erpolddata.stockrequestitems SELECT * FROM kurakura_kl_erpolddata.stockrequestitems;

TRUNCATE kurakura_kl_test_erpolddata.`stockserialitems`;
INSERT INTO kurakura_kl_test_erpolddata.stockserialitems SELECT * FROM kurakura_kl_erpolddata.stockserialitems;

TRUNCATE kurakura_kl_test_erpolddata.`stockserialmoves`;
INSERT INTO kurakura_kl_test_erpolddata.stockserialmoves SELECT * FROM kurakura_kl_erpolddata.stockserialmoves;

TRUNCATE kurakura_kl_test_erpolddata.`suppallocs`;
INSERT INTO kurakura_kl_test_erpolddata.suppallocs SELECT * FROM kurakura_kl_erpolddata.suppallocs;

TRUNCATE kurakura_kl_test_erpolddata.`suppinvstogrn`;
INSERT INTO kurakura_kl_test_erpolddata.suppinvstogrn SELECT * FROM kurakura_kl_erpolddata.suppinvstogrn;

TRUNCATE kurakura_kl_test_erpolddata.`suppliercontacts`;
INSERT INTO kurakura_kl_test_erpolddata.suppliercontacts SELECT * FROM kurakura_kl_erpolddata.suppliercontacts;

TRUNCATE kurakura_kl_test_erpolddata.`supplierdiscounts`;
INSERT INTO kurakura_kl_test_erpolddata.supplierdiscounts SELECT * FROM kurakura_kl_erpolddata.supplierdiscounts;

TRUNCATE kurakura_kl_test_erpolddata.`suppliers`;
INSERT INTO kurakura_kl_test_erpolddata.suppliers SELECT * FROM kurakura_kl_erpolddata.suppliers;

TRUNCATE kurakura_kl_test_erpolddata.`suppliertype`;
INSERT INTO kurakura_kl_test_erpolddata.suppliertype SELECT * FROM kurakura_kl_erpolddata.suppliertype;

TRUNCATE kurakura_kl_test_erpolddata.`supptrans`;
INSERT INTO kurakura_kl_test_erpolddata.supptrans SELECT * FROM kurakura_kl_erpolddata.supptrans;

TRUNCATE kurakura_kl_test_erpolddata.`supptranstaxes`;
INSERT INTO kurakura_kl_test_erpolddata.supptranstaxes SELECT * FROM kurakura_kl_erpolddata.supptranstaxes;

TRUNCATE kurakura_kl_test_erpolddata.`systypes`;
INSERT INTO kurakura_kl_test_erpolddata.systypes SELECT * FROM kurakura_kl_erpolddata.systypes;

TRUNCATE kurakura_kl_test_erpolddata.`tags`;
INSERT INTO kurakura_kl_test_erpolddata.tags SELECT * FROM kurakura_kl_erpolddata.tags;

TRUNCATE kurakura_kl_test_erpolddata.`taxauthorities`;
INSERT INTO kurakura_kl_test_erpolddata.taxauthorities SELECT * FROM kurakura_kl_erpolddata.taxauthorities;

TRUNCATE kurakura_kl_test_erpolddata.`taxauthrates`;
INSERT INTO kurakura_kl_test_erpolddata.taxauthrates SELECT * FROM kurakura_kl_erpolddata.taxauthrates;

TRUNCATE kurakura_kl_test_erpolddata.`taxcategories`;
INSERT INTO kurakura_kl_test_erpolddata.taxcategories SELECT * FROM kurakura_kl_erpolddata.taxcategories;

TRUNCATE kurakura_kl_test_erpolddata.`taxgroups`;
INSERT INTO kurakura_kl_test_erpolddata.taxgroups SELECT * FROM kurakura_kl_erpolddata.taxgroups;

TRUNCATE kurakura_kl_test_erpolddata.`taxgrouptaxes`;
INSERT INTO kurakura_kl_test_erpolddata.taxgrouptaxes SELECT * FROM kurakura_kl_erpolddata.taxgrouptaxes;

TRUNCATE kurakura_kl_test_erpolddata.`taxprovinces`;
INSERT INTO kurakura_kl_test_erpolddata.taxprovinces SELECT * FROM kurakura_kl_erpolddata.taxprovinces;

TRUNCATE kurakura_kl_test_erpolddata.`tenderitems`;
INSERT INTO kurakura_kl_test_erpolddata.tenderitems SELECT * FROM kurakura_kl_erpolddata.tenderitems;

TRUNCATE kurakura_kl_test_erpolddata.`tenders`;
INSERT INTO kurakura_kl_test_erpolddata.tenders SELECT * FROM kurakura_kl_erpolddata.tenders;

TRUNCATE kurakura_kl_test_erpolddata.`tendersuppliers`;
INSERT INTO kurakura_kl_test_erpolddata.tendersuppliers SELECT * FROM kurakura_kl_erpolddata.tendersuppliers;

TRUNCATE kurakura_kl_test_erpolddata.`unitsofdimension`;
INSERT INTO kurakura_kl_test_erpolddata.unitsofdimension SELECT * FROM kurakura_kl_erpolddata.unitsofdimension;

TRUNCATE kurakura_kl_test_erpolddata.`unitsofmeasure`;
INSERT INTO kurakura_kl_test_erpolddata.unitsofmeasure SELECT * FROM kurakura_kl_erpolddata.unitsofmeasure;

TRUNCATE kurakura_kl_test_erpolddata.`woitems`;
INSERT INTO kurakura_kl_test_erpolddata.woitems SELECT * FROM kurakura_kl_erpolddata.woitems;

TRUNCATE kurakura_kl_test_erpolddata.`worequirements`;
INSERT INTO kurakura_kl_test_erpolddata.worequirements SELECT * FROM kurakura_kl_erpolddata.worequirements;

TRUNCATE kurakura_kl_test_erpolddata.`workcentres`;
INSERT INTO kurakura_kl_test_erpolddata.workcentres SELECT * FROM kurakura_kl_erpolddata.workcentres;

TRUNCATE kurakura_kl_test_erpolddata.`workorders`;
INSERT INTO kurakura_kl_test_erpolddata.workorders SELECT * FROM kurakura_kl_erpolddata.workorders;

TRUNCATE kurakura_kl_test_erpolddata.`woserialnos`;
INSERT INTO kurakura_kl_test_erpolddata.woserialnos SELECT * FROM kurakura_kl_erpolddata.woserialnos;

TRUNCATE kurakura_kl_test_erpolddata.`www_users`;
INSERT INTO kurakura_kl_test_erpolddata.www_users SELECT * FROM kurakura_kl_erpolddata.www_users;

SET FOREIGN_KEY_CHECKS=1;

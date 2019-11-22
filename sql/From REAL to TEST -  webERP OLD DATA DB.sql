SET FOREIGN_KEY_CHECKS=0;
  
TRUNCATE ricarda3_kl_test_erpolddata.`accountgroups`;
INSERT INTO ricarda3_kl_test_erpolddata.accountgroups SELECT * FROM ricarda3_kl_erpolddata.accountgroups;

TRUNCATE ricarda3_kl_test_erpolddata.`accountsection`;
INSERT INTO ricarda3_kl_test_erpolddata.accountsection SELECT * FROM ricarda3_kl_erpolddata.accountsection;

TRUNCATE ricarda3_kl_test_erpolddata.`areas`;
INSERT INTO ricarda3_kl_test_erpolddata.areas SELECT * FROM ricarda3_kl_erpolddata.areas;

TRUNCATE ricarda3_kl_test_erpolddata.`audittrail`;
INSERT INTO ricarda3_kl_test_erpolddata.audittrail SELECT * FROM ricarda3_kl_erpolddata.audittrail;

TRUNCATE ricarda3_kl_test_erpolddata.`bankaccounts`;
INSERT INTO ricarda3_kl_test_erpolddata.bankaccounts SELECT * FROM ricarda3_kl_erpolddata.bankaccounts;

TRUNCATE ricarda3_kl_test_erpolddata.`bankaccountusers`;
INSERT INTO ricarda3_kl_test_erpolddata.bankaccountusers SELECT * FROM ricarda3_kl_erpolddata.bankaccountusers;

TRUNCATE ricarda3_kl_test_erpolddata.`banktrans`;
INSERT INTO ricarda3_kl_test_erpolddata.banktrans SELECT * FROM ricarda3_kl_erpolddata.banktrans;

TRUNCATE ricarda3_kl_test_erpolddata.`bom`;
INSERT INTO ricarda3_kl_test_erpolddata.bom SELECT * FROM ricarda3_kl_erpolddata.bom;

TRUNCATE ricarda3_kl_test_erpolddata.`buckets`;
INSERT INTO ricarda3_kl_test_erpolddata.buckets SELECT * FROM ricarda3_kl_erpolddata.buckets;

TRUNCATE ricarda3_kl_test_erpolddata.`chartdetails`;
INSERT INTO ricarda3_kl_test_erpolddata.chartdetails SELECT * FROM ricarda3_kl_erpolddata.chartdetails;

TRUNCATE ricarda3_kl_test_erpolddata.`chartmaster`;
INSERT INTO ricarda3_kl_test_erpolddata.chartmaster SELECT * FROM ricarda3_kl_erpolddata.chartmaster;

TRUNCATE ricarda3_kl_test_erpolddata.`chartmasterIK`;
INSERT INTO ricarda3_kl_test_erpolddata.chartmasterIK SELECT * FROM ricarda3_kl_erpolddata.chartmasterIK;

TRUNCATE ricarda3_kl_test_erpolddata.`chartmasterPI`;
INSERT INTO ricarda3_kl_test_erpolddata.chartmasterPI SELECT * FROM ricarda3_kl_erpolddata.chartmasterPI;

TRUNCATE ricarda3_kl_test_erpolddata.`chartmasterPMA`;
INSERT INTO ricarda3_kl_test_erpolddata.chartmasterPMA SELECT * FROM ricarda3_kl_erpolddata.chartmasterPMA;

TRUNCATE ricarda3_kl_test_erpolddata.`chartmasterPT`;
INSERT INTO ricarda3_kl_test_erpolddata.chartmasterPT SELECT * FROM ricarda3_kl_erpolddata.chartmasterPT;

TRUNCATE ricarda3_kl_test_erpolddata.`cogsglpostings`;
INSERT INTO ricarda3_kl_test_erpolddata.cogsglpostings SELECT * FROM ricarda3_kl_erpolddata.cogsglpostings;

TRUNCATE ricarda3_kl_test_erpolddata.`companies`;
INSERT INTO ricarda3_kl_test_erpolddata.companies SELECT * FROM ricarda3_kl_erpolddata.companies;

TRUNCATE ricarda3_kl_test_erpolddata.`config`;
INSERT INTO ricarda3_kl_test_erpolddata.config SELECT * FROM ricarda3_kl_erpolddata.config;

TRUNCATE ricarda3_kl_test_erpolddata.`contractbom`;
INSERT INTO ricarda3_kl_test_erpolddata.contractbom SELECT * FROM ricarda3_kl_erpolddata.contractbom;

TRUNCATE ricarda3_kl_test_erpolddata.`contractcharges`;
INSERT INTO ricarda3_kl_test_erpolddata.contractcharges SELECT * FROM ricarda3_kl_erpolddata.contractcharges;

TRUNCATE ricarda3_kl_test_erpolddata.`contractreqts`;
INSERT INTO ricarda3_kl_test_erpolddata.contractreqts SELECT * FROM ricarda3_kl_erpolddata.contractreqts;

TRUNCATE ricarda3_kl_test_erpolddata.`contracts`;
INSERT INTO ricarda3_kl_test_erpolddata.contracts SELECT * FROM ricarda3_kl_erpolddata.contracts;

TRUNCATE ricarda3_kl_test_erpolddata.`currencies`;
INSERT INTO ricarda3_kl_test_erpolddata.currencies SELECT * FROM ricarda3_kl_erpolddata.currencies;

TRUNCATE ricarda3_kl_test_erpolddata.`custallocns`;
INSERT INTO ricarda3_kl_test_erpolddata.custallocns SELECT * FROM ricarda3_kl_erpolddata.custallocns;

TRUNCATE ricarda3_kl_test_erpolddata.`custbranch`;
INSERT INTO ricarda3_kl_test_erpolddata.custbranch SELECT * FROM ricarda3_kl_erpolddata.custbranch;

TRUNCATE ricarda3_kl_test_erpolddata.`custcontacts`;
INSERT INTO ricarda3_kl_test_erpolddata.custcontacts SELECT * FROM ricarda3_kl_erpolddata.custcontacts;

TRUNCATE ricarda3_kl_test_erpolddata.`custitem`;
INSERT INTO ricarda3_kl_test_erpolddata.custitem SELECT * FROM ricarda3_kl_erpolddata.custitem;

TRUNCATE ricarda3_kl_test_erpolddata.`custnotes`;
INSERT INTO ricarda3_kl_test_erpolddata.custnotes SELECT * FROM ricarda3_kl_erpolddata.custnotes;

TRUNCATE ricarda3_kl_test_erpolddata.`debtorsmaster`;
INSERT INTO ricarda3_kl_test_erpolddata.debtorsmaster SELECT * FROM ricarda3_kl_erpolddata.debtorsmaster;

TRUNCATE ricarda3_kl_test_erpolddata.`debtortrans`;
INSERT INTO ricarda3_kl_test_erpolddata.debtortrans SELECT * FROM ricarda3_kl_erpolddata.debtortrans;

TRUNCATE ricarda3_kl_test_erpolddata.`debtortranstaxes`;
INSERT INTO ricarda3_kl_test_erpolddata.debtortranstaxes SELECT * FROM ricarda3_kl_erpolddata.debtortranstaxes;

TRUNCATE ricarda3_kl_test_erpolddata.`debtortype`;
INSERT INTO ricarda3_kl_test_erpolddata.debtortype SELECT * FROM ricarda3_kl_erpolddata.debtortype;

TRUNCATE ricarda3_kl_test_erpolddata.`debtortypenotes`;
INSERT INTO ricarda3_kl_test_erpolddata.debtortypenotes SELECT * FROM ricarda3_kl_erpolddata.debtortypenotes;

TRUNCATE ricarda3_kl_test_erpolddata.`deliverynotes`;
INSERT INTO ricarda3_kl_test_erpolddata.deliverynotes SELECT * FROM ricarda3_kl_erpolddata.deliverynotes;

TRUNCATE ricarda3_kl_test_erpolddata.`departments`;
INSERT INTO ricarda3_kl_test_erpolddata.departments SELECT * FROM ricarda3_kl_erpolddata.departments;

TRUNCATE ricarda3_kl_test_erpolddata.`discountmatrix`;
INSERT INTO ricarda3_kl_test_erpolddata.discountmatrix SELECT * FROM ricarda3_kl_erpolddata.discountmatrix;

TRUNCATE ricarda3_kl_test_erpolddata.`edi_orders_segs`;
INSERT INTO ricarda3_kl_test_erpolddata.edi_orders_segs SELECT * FROM ricarda3_kl_erpolddata.edi_orders_segs;

TRUNCATE ricarda3_kl_test_erpolddata.`ediitemmapping`;
INSERT INTO ricarda3_kl_test_erpolddata.ediitemmapping SELECT * FROM ricarda3_kl_erpolddata.ediitemmapping;

TRUNCATE ricarda3_kl_test_erpolddata.`edimessageformat`;
INSERT INTO ricarda3_kl_test_erpolddata.edimessageformat SELECT * FROM ricarda3_kl_erpolddata.edimessageformat;

TRUNCATE ricarda3_kl_test_erpolddata.`edi_orders_seg_groups`;
INSERT INTO ricarda3_kl_test_erpolddata.edi_orders_seg_groups SELECT * FROM ricarda3_kl_erpolddata.edi_orders_seg_groups;

TRUNCATE ricarda3_kl_test_erpolddata.`emailsettings`;
INSERT INTO ricarda3_kl_test_erpolddata.emailsettings SELECT * FROM ricarda3_kl_erpolddata.emailsettings;

TRUNCATE ricarda3_kl_test_erpolddata.`factorcompanies`;
INSERT INTO ricarda3_kl_test_erpolddata.factorcompanies SELECT * FROM ricarda3_kl_erpolddata.factorcompanies;

TRUNCATE ricarda3_kl_test_erpolddata.`fixedassetcategories`;
INSERT INTO ricarda3_kl_test_erpolddata.fixedassetcategories SELECT * FROM ricarda3_kl_erpolddata.fixedassetcategories;

TRUNCATE ricarda3_kl_test_erpolddata.`fixedassetlocations`;
INSERT INTO ricarda3_kl_test_erpolddata.fixedassetlocations SELECT * FROM ricarda3_kl_erpolddata.fixedassetlocations;

TRUNCATE ricarda3_kl_test_erpolddata.`fixedassets`;
INSERT INTO ricarda3_kl_test_erpolddata.fixedassets SELECT * FROM ricarda3_kl_erpolddata.fixedassets;

TRUNCATE ricarda3_kl_test_erpolddata.`fixedassettasks`;
INSERT INTO ricarda3_kl_test_erpolddata.fixedassettasks SELECT * FROM ricarda3_kl_erpolddata.fixedassettasks;

TRUNCATE ricarda3_kl_test_erpolddata.`fixedassettrans`;
INSERT INTO ricarda3_kl_test_erpolddata.fixedassettrans SELECT * FROM ricarda3_kl_erpolddata.fixedassettrans;

TRUNCATE ricarda3_kl_test_erpolddata.`freightcosts`;
INSERT INTO ricarda3_kl_test_erpolddata.freightcosts SELECT * FROM ricarda3_kl_erpolddata.freightcosts;

TRUNCATE ricarda3_kl_test_erpolddata.`geocode_param`;
INSERT INTO ricarda3_kl_test_erpolddata.geocode_param SELECT * FROM ricarda3_kl_erpolddata.geocode_param;

TRUNCATE ricarda3_kl_test_erpolddata.`glaccountusers`;
INSERT INTO ricarda3_kl_test_erpolddata.glaccountusers SELECT * FROM ricarda3_kl_erpolddata.glaccountusers;

TRUNCATE ricarda3_kl_test_erpolddata.`gltrans`;
INSERT INTO ricarda3_kl_test_erpolddata.gltrans SELECT * FROM ricarda3_kl_erpolddata.gltrans WHERE periodno <= 30;
INSERT INTO ricarda3_kl_test_erpolddata.gltrans SELECT * FROM ricarda3_kl_erpolddata.gltrans WHERE periodno > 30 AND periodno <= 60;
INSERT INTO ricarda3_kl_test_erpolddata.gltrans SELECT * FROM ricarda3_kl_erpolddata.gltrans WHERE periodno > 60 AND periodno <= 80;
INSERT INTO ricarda3_kl_test_erpolddata.gltrans SELECT * FROM ricarda3_kl_erpolddata.gltrans WHERE periodno > 80 AND periodno <= 90;
INSERT INTO ricarda3_kl_test_erpolddata.gltrans SELECT * FROM ricarda3_kl_erpolddata.gltrans WHERE periodno > 90 AND periodno <= 100;
INSERT INTO ricarda3_kl_test_erpolddata.gltrans SELECT * FROM ricarda3_kl_erpolddata.gltrans WHERE periodno > 100 AND periodno <= 110;
INSERT INTO ricarda3_kl_test_erpolddata.gltrans SELECT * FROM ricarda3_kl_erpolddata.gltrans WHERE periodno > 110;

TRUNCATE ricarda3_kl_test_erpolddata.`grns`;
INSERT INTO ricarda3_kl_test_erpolddata.grns SELECT * FROM ricarda3_kl_erpolddata.grns;

TRUNCATE ricarda3_kl_test_erpolddata.`holdreasons`;
INSERT INTO ricarda3_kl_test_erpolddata.holdreasons SELECT * FROM ricarda3_kl_erpolddata.holdreasons;

TRUNCATE ricarda3_kl_test_erpolddata.`internalstockcatrole`;
INSERT INTO ricarda3_kl_test_erpolddata.internalstockcatrole SELECT * FROM ricarda3_kl_erpolddata.internalstockcatrole;

TRUNCATE ricarda3_kl_test_erpolddata.`kladjustrl`;
INSERT INTO ricarda3_kl_test_erpolddata.kladjustrl SELECT * FROM ricarda3_kl_erpolddata.kladjustrl;

TRUNCATE ricarda3_kl_test_erpolddata.`klchangeprice`;
INSERT INTO ricarda3_kl_test_erpolddata.klchangeprice SELECT * FROM ricarda3_kl_erpolddata.klchangeprice;

TRUNCATE ricarda3_kl_test_erpolddata.`kladjustrl`;
INSERT INTO ricarda3_kl_test_erpolddata.kladjustrl SELECT * FROM ricarda3_kl_erpolddata.kladjustrl;

TRUNCATE ricarda3_kl_test_erpolddata.`klconsignment`;
INSERT INTO ricarda3_kl_test_erpolddata.klconsignment SELECT * FROM ricarda3_kl_erpolddata.klconsignment;

TRUNCATE ricarda3_kl_test_erpolddata.`klfreeexchanges`;
INSERT INTO ricarda3_kl_test_erpolddata.klfreeexchanges SELECT * FROM ricarda3_kl_erpolddata.klfreeexchanges;

TRUNCATE ricarda3_kl_test_erpolddata.`klmovetodiscount20`;
INSERT INTO ricarda3_kl_test_erpolddata.klmovetodiscount20 SELECT * FROM ricarda3_kl_erpolddata.klmovetodiscount20;

TRUNCATE ricarda3_kl_test_erpolddata.`klmovetodiscount50`;
INSERT INTO ricarda3_kl_test_erpolddata.klmovetodiscount50 SELECT * FROM ricarda3_kl_erpolddata.klmovetodiscount50;

TRUNCATE ricarda3_kl_test_erpolddata.`klmovetodiscount80`;
INSERT INTO ricarda3_kl_test_erpolddata.klmovetodiscount80 SELECT * FROM ricarda3_kl_erpolddata.klmovetodiscount80;

TRUNCATE ricarda3_kl_test_erpolddata.`klolddatapurged`;
INSERT INTO ricarda3_kl_test_erpolddata.klolddatapurged SELECT * FROM ricarda3_kl_erpolddata.klolddatapurged;

TRUNCATE ricarda3_kl_test_erpolddata.`klonlinepartners`;
INSERT INTO ricarda3_kl_test_erpolddata.klonlinepartners SELECT * FROM ricarda3_kl_erpolddata.klonlinepartners;

TRUNCATE ricarda3_kl_test_erpolddata.`klpostatus`;
INSERT INTO ricarda3_kl_test_erpolddata.klpostatus SELECT * FROM ricarda3_kl_erpolddata.klpostatus;

TRUNCATE ricarda3_kl_test_erpolddata.`klretailcustomers`;
INSERT INTO ricarda3_kl_test_erpolddata.klretailcustomers SELECT * FROM ricarda3_kl_erpolddata.klretailcustomers;

TRUNCATE ricarda3_kl_test_erpolddata.`klretailpartners`;
INSERT INTO ricarda3_kl_test_erpolddata.klretailpartners SELECT * FROM ricarda3_kl_erpolddata.klretailpartners;

TRUNCATE ricarda3_kl_test_erpolddata.`klrevisedemaildomains`;
INSERT INTO ricarda3_kl_test_erpolddata.klrevisedemaildomains SELECT * FROM ricarda3_kl_erpolddata.klrevisedemaildomains;

TRUNCATE ricarda3_kl_test_erpolddata.`klsalesperformance`;
INSERT INTO ricarda3_kl_test_erpolddata.klsalesperformance SELECT * FROM ricarda3_kl_erpolddata.klsalesperformance;

TRUNCATE ricarda3_kl_test_erpolddata.`labelfields`;
INSERT INTO ricarda3_kl_test_erpolddata.labelfields SELECT * FROM ricarda3_kl_erpolddata.labelfields;

TRUNCATE ricarda3_kl_test_erpolddata.`labels`;
INSERT INTO ricarda3_kl_test_erpolddata.labels SELECT * FROM ricarda3_kl_erpolddata.labels;

INSERT INTO ricarda3_kl_test_erpolddata.lastcostrollup SELECT * FROM ricarda3_kl_erpolddata.lastcostrollup;
TRUNCATE ricarda3_kl_test_erpolddata.`lastcostrollup`;

INSERT INTO ricarda3_kl_test_erpolddata.levels SELECT * FROM ricarda3_kl_erpolddata.levels;
TRUNCATE ricarda3_kl_test_erpolddata.`levels`;

TRUNCATE ricarda3_kl_test_erpolddata.`locations`;
INSERT INTO ricarda3_kl_test_erpolddata.locations SELECT * FROM ricarda3_kl_erpolddata.locations;

TRUNCATE ricarda3_kl_test_erpolddata.`locationtypes`;
INSERT INTO ricarda3_kl_test_erpolddata.locationtypes SELECT * FROM ricarda3_kl_erpolddata.locationtypes;

TRUNCATE ricarda3_kl_test_erpolddata.`locationusers`;
INSERT INTO ricarda3_kl_test_erpolddata.locationusers SELECT * FROM ricarda3_kl_erpolddata.locationusers;

TRUNCATE ricarda3_kl_test_erpolddata.`locationzones`;
INSERT INTO ricarda3_kl_test_erpolddata.locationzones SELECT * FROM ricarda3_kl_erpolddata.locationzones;

TRUNCATE ricarda3_kl_test_erpolddata.`locstock`;
INSERT INTO ricarda3_kl_test_erpolddata.locstock SELECT * FROM ricarda3_kl_erpolddata.locstock;

TRUNCATE ricarda3_kl_test_erpolddata.`loctransfercancellations`;
INSERT INTO ricarda3_kl_test_erpolddata.loctransfercancellations SELECT * FROM ricarda3_kl_erpolddata.loctransfercancellations;

TRUNCATE ricarda3_kl_test_erpolddata.`loctransfers`;
INSERT INTO ricarda3_kl_test_erpolddata.loctransfers SELECT * FROM ricarda3_kl_erpolddata.loctransfers;

TRUNCATE ricarda3_kl_test_erpolddata.`mailgroupdetails`;
INSERT INTO ricarda3_kl_test_erpolddata.mailgroupdetails SELECT * FROM ricarda3_kl_erpolddata.mailgroupdetails;

TRUNCATE ricarda3_kl_test_erpolddata.`mailgroups`;
INSERT INTO ricarda3_kl_test_erpolddata.mailgroups SELECT * FROM ricarda3_kl_erpolddata.mailgroups;

TRUNCATE ricarda3_kl_test_erpolddata.`manufacturers`;
INSERT INTO ricarda3_kl_test_erpolddata.manufacturers SELECT * FROM ricarda3_kl_erpolddata.manufacturers;

TRUNCATE ricarda3_kl_test_erpolddata.`mrpcalendar`;
INSERT INTO ricarda3_kl_test_erpolddata.mrpcalendar SELECT * FROM ricarda3_kl_erpolddata.mrpcalendar;

TRUNCATE ricarda3_kl_test_erpolddata.`mrpdemands`;
INSERT INTO ricarda3_kl_test_erpolddata.mrpdemands SELECT * FROM ricarda3_kl_erpolddata.mrpdemands;

TRUNCATE ricarda3_kl_test_erpolddata.`mrpdemandtypes`;
INSERT INTO ricarda3_kl_test_erpolddata.mrpdemandtypes SELECT * FROM ricarda3_kl_erpolddata.mrpdemandtypes;

TRUNCATE ricarda3_kl_test_erpolddata.`mrpparameters`;
INSERT INTO ricarda3_kl_test_erpolddata.mrpparameters SELECT * FROM ricarda3_kl_erpolddata.mrpparameters;

TRUNCATE ricarda3_kl_test_erpolddata.`mrpplannedorders`;
INSERT INTO ricarda3_kl_test_erpolddata.mrpplannedorders SELECT * FROM ricarda3_kl_erpolddata.mrpplannedorders;

TRUNCATE ricarda3_kl_test_erpolddata.`mrprequirements`;
INSERT INTO ricarda3_kl_test_erpolddata.mrprequirements SELECT * FROM ricarda3_kl_erpolddata.mrprequirements;

TRUNCATE ricarda3_kl_test_erpolddata.`mrpsupplies`;
INSERT INTO ricarda3_kl_test_erpolddata.mrpsupplies SELECT * FROM ricarda3_kl_erpolddata.mrpsupplies;

TRUNCATE ricarda3_kl_test_erpolddata.`offers`;
INSERT INTO ricarda3_kl_test_erpolddata.offers SELECT * FROM ricarda3_kl_erpolddata.offers;

TRUNCATE ricarda3_kl_test_erpolddata.`orderdeliverydifferenceslog`;
INSERT INTO ricarda3_kl_test_erpolddata.orderdeliverydifferenceslog SELECT * FROM ricarda3_kl_erpolddata.orderdeliverydifferenceslog;

TRUNCATE ricarda3_kl_test_erpolddata.`packagingused`;
INSERT INTO ricarda3_kl_test_erpolddata.packagingused SELECT * FROM ricarda3_kl_erpolddata.packagingused;

TRUNCATE ricarda3_kl_test_erpolddata.`paymentmethods`;
INSERT INTO ricarda3_kl_test_erpolddata.paymentmethods SELECT * FROM ricarda3_kl_erpolddata.paymentmethods;

TRUNCATE ricarda3_kl_test_erpolddata.`paymentterms`;
INSERT INTO ricarda3_kl_test_erpolddata.paymentterms SELECT * FROM ricarda3_kl_erpolddata.paymentterms;

TRUNCATE ricarda3_kl_test_erpolddata.`pcashdetails`;
INSERT INTO ricarda3_kl_test_erpolddata.pcashdetails SELECT * FROM ricarda3_kl_erpolddata.pcashdetails;

TRUNCATE ricarda3_kl_test_erpolddata.`pcexpenses`;
INSERT INTO ricarda3_kl_test_erpolddata.pcexpenses SELECT * FROM ricarda3_kl_erpolddata.pcexpenses;

TRUNCATE ricarda3_kl_test_erpolddata.`pctabexpenses`;
INSERT INTO ricarda3_kl_test_erpolddata.pctabexpenses SELECT * FROM ricarda3_kl_erpolddata.pctabexpenses;

TRUNCATE ricarda3_kl_test_erpolddata.`pcsalaries`;
INSERT INTO ricarda3_kl_test_erpolddata.pcsalaries SELECT * FROM ricarda3_kl_erpolddata.pcsalaries;

TRUNCATE ricarda3_kl_test_erpolddata.`pctabs`;
INSERT INTO ricarda3_kl_test_erpolddata.pctabs SELECT * FROM ricarda3_kl_erpolddata.pctabs;

TRUNCATE ricarda3_kl_test_erpolddata.`pctypetabs`;
INSERT INTO ricarda3_kl_test_erpolddata.pctypetabs SELECT * FROM ricarda3_kl_erpolddata.pctypetabs;

TRUNCATE ricarda3_kl_test_erpolddata.`periods`;
INSERT INTO ricarda3_kl_test_erpolddata.periods SELECT * FROM ricarda3_kl_erpolddata.periods;

TRUNCATE ricarda3_kl_test_erpolddata.`pickinglistdetails`;
INSERT INTO ricarda3_kl_test_erpolddata.pickinglistdetails SELECT * FROM ricarda3_kl_erpolddata.pickinglistdetails;

TRUNCATE ricarda3_kl_test_erpolddata.`pickinglists`;
INSERT INTO ricarda3_kl_test_erpolddata.pickinglists SELECT * FROM ricarda3_kl_erpolddata.pickinglists;

TRUNCATE ricarda3_kl_test_erpolddata.`pricematrix`;
INSERT INTO ricarda3_kl_test_erpolddata.pricematrix SELECT * FROM ricarda3_kl_erpolddata.pricematrix;

TRUNCATE ricarda3_kl_test_erpolddata.`prices`;
INSERT INTO ricarda3_kl_test_erpolddata.prices SELECT * FROM ricarda3_kl_erpolddata.prices;

TRUNCATE ricarda3_kl_test_erpolddata.`prodspecs`;
INSERT INTO ricarda3_kl_test_erpolddata.prodspecs SELECT * FROM ricarda3_kl_erpolddata.prodspecs;

TRUNCATE ricarda3_kl_test_erpolddata.`purchdata`;
INSERT INTO ricarda3_kl_test_erpolddata.purchdata SELECT * FROM ricarda3_kl_erpolddata.purchdata;

TRUNCATE ricarda3_kl_test_erpolddata.`purchorderauth`;
INSERT INTO ricarda3_kl_test_erpolddata.purchorderauth SELECT * FROM ricarda3_kl_erpolddata.purchorderauth;

TRUNCATE ricarda3_kl_test_erpolddata.`purchorderdetails`;
INSERT INTO ricarda3_kl_test_erpolddata.purchorderdetails SELECT * FROM ricarda3_kl_erpolddata.purchorderdetails;

TRUNCATE ricarda3_kl_test_erpolddata.`purchorders`;
INSERT INTO ricarda3_kl_test_erpolddata.purchorders SELECT * FROM ricarda3_kl_erpolddata.purchorders;

TRUNCATE ricarda3_kl_test_erpolddata.`qasamples`;
INSERT INTO ricarda3_kl_test_erpolddata.qasamples SELECT * FROM ricarda3_kl_erpolddata.qasamples;

TRUNCATE ricarda3_kl_test_erpolddata.`qatests`;
INSERT INTO ricarda3_kl_test_erpolddata.qatests SELECT * FROM ricarda3_kl_erpolddata.qatests;

TRUNCATE ricarda3_kl_test_erpolddata.`recurringsalesorders`;
INSERT INTO ricarda3_kl_test_erpolddata.recurringsalesorders SELECT * FROM ricarda3_kl_erpolddata.recurringsalesorders;

TRUNCATE ricarda3_kl_test_erpolddata.`recurrsalesorderdetails`;
INSERT INTO ricarda3_kl_test_erpolddata.recurrsalesorderdetails SELECT * FROM ricarda3_kl_erpolddata.recurrsalesorderdetails;

TRUNCATE ricarda3_kl_test_erpolddata.`relateditems`;
INSERT INTO ricarda3_kl_test_erpolddata.relateditems SELECT * FROM ricarda3_kl_erpolddata.relateditems;

TRUNCATE ricarda3_kl_test_erpolddata.`reportcolumns`;
INSERT INTO ricarda3_kl_test_erpolddata.reportcolumns SELECT * FROM ricarda3_kl_erpolddata.reportcolumns;

TRUNCATE ricarda3_kl_test_erpolddata.`reportfields`;
INSERT INTO ricarda3_kl_test_erpolddata.reportfields SELECT * FROM ricarda3_kl_erpolddata.reportfields;

TRUNCATE ricarda3_kl_test_erpolddata.`reportheaders`;
INSERT INTO ricarda3_kl_test_erpolddata.reportheaders SELECT * FROM ricarda3_kl_erpolddata.reportheaders;

TRUNCATE ricarda3_kl_test_erpolddata.`reportlets`;
INSERT INTO ricarda3_kl_test_erpolddata.reportlets SELECT * FROM ricarda3_kl_erpolddata.reportlets;

TRUNCATE ricarda3_kl_test_erpolddata.`reportlinks`;
INSERT INTO ricarda3_kl_test_erpolddata.reportlinks SELECT * FROM ricarda3_kl_erpolddata.reportlinks;

TRUNCATE ricarda3_kl_test_erpolddata.`reports`;
INSERT INTO ricarda3_kl_test_erpolddata.reports SELECT * FROM ricarda3_kl_erpolddata.reports;

TRUNCATE ricarda3_kl_test_erpolddata.`returnitemreasons`;
INSERT INTO ricarda3_kl_test_erpolddata.returnitemreasons SELECT * FROM ricarda3_kl_erpolddata.returnitemreasons;

TRUNCATE ricarda3_kl_test_erpolddata.`returneditems`;
INSERT INTO ricarda3_kl_test_erpolddata.returneditems SELECT * FROM ricarda3_kl_erpolddata.returneditems;

TRUNCATE ricarda3_kl_test_erpolddata.`salariescalculated`;
INSERT INTO ricarda3_kl_test_erpolddata.salariescalculated SELECT * FROM ricarda3_kl_erpolddata.salariescalculated;

TRUNCATE ricarda3_kl_test_erpolddata.`salesanalysis`;
INSERT INTO ricarda3_kl_test_erpolddata.salesanalysis SELECT * FROM ricarda3_kl_erpolddata.salesanalysis;

TRUNCATE ricarda3_kl_test_erpolddata.`salescat`;
INSERT INTO ricarda3_kl_test_erpolddata.salescat SELECT * FROM ricarda3_kl_erpolddata.salescat;

TRUNCATE ricarda3_kl_test_erpolddata.`salescatprod`;
INSERT INTO ricarda3_kl_test_erpolddata.salescatprod SELECT * FROM ricarda3_kl_erpolddata.salescatprod;

TRUNCATE ricarda3_kl_test_erpolddata.`salescattranslations`;
INSERT INTO ricarda3_kl_test_erpolddata.salescattranslations SELECT * FROM ricarda3_kl_erpolddata.salescattranslations;

TRUNCATE ricarda3_kl_test_erpolddata.`salesglpostings`;
INSERT INTO ricarda3_kl_test_erpolddata.salesglpostings SELECT * FROM ricarda3_kl_erpolddata.salesglpostings;

TRUNCATE ricarda3_kl_test_erpolddata.`salesman`;
INSERT INTO ricarda3_kl_test_erpolddata.salesman SELECT * FROM ricarda3_kl_erpolddata.salesman;

TRUNCATE ricarda3_kl_test_erpolddata.`salesorderdetails`;
INSERT INTO ricarda3_kl_test_erpolddata.salesorderdetails SELECT * FROM ricarda3_kl_erpolddata.salesorderdetails;

TRUNCATE ricarda3_kl_test_erpolddata.`salesorders`;
INSERT INTO ricarda3_kl_test_erpolddata.salesorders SELECT * FROM ricarda3_kl_erpolddata.salesorders;

TRUNCATE ricarda3_kl_test_erpolddata.`salestypes`;
INSERT INTO ricarda3_kl_test_erpolddata.salestypes SELECT * FROM ricarda3_kl_erpolddata.salestypes;

TRUNCATE ricarda3_kl_test_erpolddata.`sampleresults`;
INSERT INTO ricarda3_kl_test_erpolddata.sampleresults SELECT * FROM ricarda3_kl_erpolddata.sampleresults;

TRUNCATE ricarda3_kl_test_erpolddata.`scripts`;
INSERT INTO ricarda3_kl_test_erpolddata.scripts SELECT * FROM ricarda3_kl_erpolddata.scripts;

TRUNCATE ricarda3_kl_test_erpolddata.`securitygroups`;
INSERT INTO ricarda3_kl_test_erpolddata.securitygroups SELECT * FROM ricarda3_kl_erpolddata.securitygroups;

TRUNCATE ricarda3_kl_test_erpolddata.`securityroles`;
INSERT INTO ricarda3_kl_test_erpolddata.securityroles SELECT * FROM ricarda3_kl_erpolddata.securityroles;

TRUNCATE ricarda3_kl_test_erpolddata.`securitytokens`;
INSERT INTO ricarda3_kl_test_erpolddata.securitytokens SELECT * FROM ricarda3_kl_erpolddata.securitytokens;

TRUNCATE ricarda3_kl_test_erpolddata.`sellthroughsupport`;
INSERT INTO ricarda3_kl_test_erpolddata.sellthroughsupport SELECT * FROM ricarda3_kl_erpolddata.sellthroughsupport;

TRUNCATE ricarda3_kl_test_erpolddata.`shipmentcharges`;
INSERT INTO ricarda3_kl_test_erpolddata.shipmentcharges SELECT * FROM ricarda3_kl_erpolddata.shipmentcharges;

TRUNCATE ricarda3_kl_test_erpolddata.`shipments`;
INSERT INTO ricarda3_kl_test_erpolddata.shipments SELECT * FROM ricarda3_kl_erpolddata.shipments;

TRUNCATE ricarda3_kl_test_erpolddata.`shippers`;
INSERT INTO ricarda3_kl_test_erpolddata.shippers SELECT * FROM ricarda3_kl_erpolddata.shippers;

TRUNCATE ricarda3_kl_test_erpolddata.`stockcategory`;
INSERT INTO ricarda3_kl_test_erpolddata.stockcategory SELECT * FROM ricarda3_kl_erpolddata.stockcategory;

TRUNCATE ricarda3_kl_test_erpolddata.`stockcatproperties`;
INSERT INTO ricarda3_kl_test_erpolddata.stockcatproperties SELECT * FROM ricarda3_kl_erpolddata.stockcatproperties;

TRUNCATE ricarda3_kl_test_erpolddata.`stockcheckfreeze`;
INSERT INTO ricarda3_kl_test_erpolddata.stockcheckfreeze SELECT * FROM ricarda3_kl_erpolddata.stockcheckfreeze;

TRUNCATE ricarda3_kl_test_erpolddata.`stockcounts`;
INSERT INTO ricarda3_kl_test_erpolddata.stockcounts SELECT * FROM ricarda3_kl_erpolddata.stockcounts;

TRUNCATE ricarda3_kl_test_erpolddata.`stockdescriptiontranslations`;
INSERT INTO ricarda3_kl_test_erpolddata.stockdescriptiontranslations SELECT * FROM ricarda3_kl_erpolddata.stockdescriptiontranslations;

TRUNCATE ricarda3_kl_test_erpolddata.`stockitemproperties`;
INSERT INTO ricarda3_kl_test_erpolddata.stockitemproperties SELECT * FROM ricarda3_kl_erpolddata.stockitemproperties;

TRUNCATE ricarda3_kl_test_erpolddata.`stockmaster`;
INSERT INTO ricarda3_kl_test_erpolddata.stockmaster SELECT * FROM ricarda3_kl_erpolddata.stockmaster;

TRUNCATE ricarda3_kl_test_erpolddata.`stockmoves`;
INSERT INTO ricarda3_kl_test_erpolddata.stockmoves SELECT * FROM ricarda3_kl_erpolddata.stockmoves WHERE prd <= 30;
INSERT INTO ricarda3_kl_test_erpolddata.stockmoves SELECT * FROM ricarda3_kl_erpolddata.stockmoves WHERE prd > 30 AND prd <= 60;
INSERT INTO ricarda3_kl_test_erpolddata.stockmoves SELECT * FROM ricarda3_kl_erpolddata.stockmoves WHERE prd > 60 AND prd <= 80;
INSERT INTO ricarda3_kl_test_erpolddata.stockmoves SELECT * FROM ricarda3_kl_erpolddata.stockmoves WHERE prd > 80 AND prd <= 90;
INSERT INTO ricarda3_kl_test_erpolddata.stockmoves SELECT * FROM ricarda3_kl_erpolddata.stockmoves WHERE prd > 90 AND prd <= 100;
INSERT INTO ricarda3_kl_test_erpolddata.stockmoves SELECT * FROM ricarda3_kl_erpolddata.stockmoves WHERE prd > 100 AND prd <= 110;
INSERT INTO ricarda3_kl_test_erpolddata.stockmoves SELECT * FROM ricarda3_kl_erpolddata.stockmoves WHERE prd > 110;

TRUNCATE ricarda3_kl_test_erpolddata.`stockmovestaxes`;
INSERT INTO ricarda3_kl_test_erpolddata.stockmovestaxes SELECT * FROM ricarda3_kl_erpolddata.stockmovestaxes;

TRUNCATE ricarda3_kl_test_erpolddata.`stockrequest`;
INSERT INTO ricarda3_kl_test_erpolddata.stockrequest SELECT * FROM ricarda3_kl_erpolddata.stockrequest;

TRUNCATE ricarda3_kl_test_erpolddata.`stockrequestitems`;
INSERT INTO ricarda3_kl_test_erpolddata.stockrequestitems SELECT * FROM ricarda3_kl_erpolddata.stockrequestitems;

TRUNCATE ricarda3_kl_test_erpolddata.`stockserialitems`;
INSERT INTO ricarda3_kl_test_erpolddata.stockserialitems SELECT * FROM ricarda3_kl_erpolddata.stockserialitems;

TRUNCATE ricarda3_kl_test_erpolddata.`stockserialmoves`;
INSERT INTO ricarda3_kl_test_erpolddata.stockserialmoves SELECT * FROM ricarda3_kl_erpolddata.stockserialmoves;

TRUNCATE ricarda3_kl_test_erpolddata.`suppallocs`;
INSERT INTO ricarda3_kl_test_erpolddata.suppallocs SELECT * FROM ricarda3_kl_erpolddata.suppallocs;

TRUNCATE ricarda3_kl_test_erpolddata.`suppinvstogrn`;
INSERT INTO ricarda3_kl_test_erpolddata.suppinvstogrn SELECT * FROM ricarda3_kl_erpolddata.suppinvstogrn;

TRUNCATE ricarda3_kl_test_erpolddata.`suppliercontacts`;
INSERT INTO ricarda3_kl_test_erpolddata.suppliercontacts SELECT * FROM ricarda3_kl_erpolddata.suppliercontacts;

TRUNCATE ricarda3_kl_test_erpolddata.`supplierdiscounts`;
INSERT INTO ricarda3_kl_test_erpolddata.supplierdiscounts SELECT * FROM ricarda3_kl_erpolddata.supplierdiscounts;

TRUNCATE ricarda3_kl_test_erpolddata.`suppliers`;
INSERT INTO ricarda3_kl_test_erpolddata.suppliers SELECT * FROM ricarda3_kl_erpolddata.suppliers;

TRUNCATE ricarda3_kl_test_erpolddata.`suppliertype`;
INSERT INTO ricarda3_kl_test_erpolddata.suppliertype SELECT * FROM ricarda3_kl_erpolddata.suppliertype;

TRUNCATE ricarda3_kl_test_erpolddata.`supptrans`;
INSERT INTO ricarda3_kl_test_erpolddata.supptrans SELECT * FROM ricarda3_kl_erpolddata.supptrans;

TRUNCATE ricarda3_kl_test_erpolddata.`supptranstaxes`;
INSERT INTO ricarda3_kl_test_erpolddata.supptranstaxes SELECT * FROM ricarda3_kl_erpolddata.supptranstaxes;

TRUNCATE ricarda3_kl_test_erpolddata.`systypes`;
INSERT INTO ricarda3_kl_test_erpolddata.systypes SELECT * FROM ricarda3_kl_erpolddata.systypes;

TRUNCATE ricarda3_kl_test_erpolddata.`tags`;
INSERT INTO ricarda3_kl_test_erpolddata.tags SELECT * FROM ricarda3_kl_erpolddata.tags;

TRUNCATE ricarda3_kl_test_erpolddata.`taxauthorities`;
INSERT INTO ricarda3_kl_test_erpolddata.taxauthorities SELECT * FROM ricarda3_kl_erpolddata.taxauthorities;

TRUNCATE ricarda3_kl_test_erpolddata.`taxauthrates`;
INSERT INTO ricarda3_kl_test_erpolddata.taxauthrates SELECT * FROM ricarda3_kl_erpolddata.taxauthrates;

TRUNCATE ricarda3_kl_test_erpolddata.`taxcategories`;
INSERT INTO ricarda3_kl_test_erpolddata.taxcategories SELECT * FROM ricarda3_kl_erpolddata.taxcategories;

TRUNCATE ricarda3_kl_test_erpolddata.`taxgroups`;
INSERT INTO ricarda3_kl_test_erpolddata.taxgroups SELECT * FROM ricarda3_kl_erpolddata.taxgroups;

TRUNCATE ricarda3_kl_test_erpolddata.`taxgrouptaxes`;
INSERT INTO ricarda3_kl_test_erpolddata.taxgrouptaxes SELECT * FROM ricarda3_kl_erpolddata.taxgrouptaxes;

TRUNCATE ricarda3_kl_test_erpolddata.`taxprovinces`;
INSERT INTO ricarda3_kl_test_erpolddata.taxprovinces SELECT * FROM ricarda3_kl_erpolddata.taxprovinces;

TRUNCATE ricarda3_kl_test_erpolddata.`tenderitems`;
INSERT INTO ricarda3_kl_test_erpolddata.tenderitems SELECT * FROM ricarda3_kl_erpolddata.tenderitems;

TRUNCATE ricarda3_kl_test_erpolddata.`tenders`;
INSERT INTO ricarda3_kl_test_erpolddata.tenders SELECT * FROM ricarda3_kl_erpolddata.tenders;

TRUNCATE ricarda3_kl_test_erpolddata.`tendersuppliers`;
INSERT INTO ricarda3_kl_test_erpolddata.tendersuppliers SELECT * FROM ricarda3_kl_erpolddata.tendersuppliers;

TRUNCATE ricarda3_kl_test_erpolddata.`unitsofdimension`;
INSERT INTO ricarda3_kl_test_erpolddata.unitsofdimension SELECT * FROM ricarda3_kl_erpolddata.unitsofdimension;

TRUNCATE ricarda3_kl_test_erpolddata.`unitsofmeasure`;
INSERT INTO ricarda3_kl_test_erpolddata.unitsofmeasure SELECT * FROM ricarda3_kl_erpolddata.unitsofmeasure;

TRUNCATE ricarda3_kl_test_erpolddata.`woitems`;
INSERT INTO ricarda3_kl_test_erpolddata.woitems SELECT * FROM ricarda3_kl_erpolddata.woitems;

TRUNCATE ricarda3_kl_test_erpolddata.`worequirements`;
INSERT INTO ricarda3_kl_test_erpolddata.worequirements SELECT * FROM ricarda3_kl_erpolddata.worequirements;

TRUNCATE ricarda3_kl_test_erpolddata.`workcentres`;
INSERT INTO ricarda3_kl_test_erpolddata.workcentres SELECT * FROM ricarda3_kl_erpolddata.workcentres;

TRUNCATE ricarda3_kl_test_erpolddata.`workorders`;
INSERT INTO ricarda3_kl_test_erpolddata.workorders SELECT * FROM ricarda3_kl_erpolddata.workorders;

TRUNCATE ricarda3_kl_test_erpolddata.`woserialnos`;
INSERT INTO ricarda3_kl_test_erpolddata.woserialnos SELECT * FROM ricarda3_kl_erpolddata.woserialnos;

TRUNCATE ricarda3_kl_test_erpolddata.`www_users`;
INSERT INTO ricarda3_kl_test_erpolddata.www_users SELECT * FROM ricarda3_kl_erpolddata.www_users;

SET FOREIGN_KEY_CHECKS=1;

SET FOREIGN_KEY_CHECKS=0;
  
TRUNCATE ricarda3_kl_test_erp.`accountgroups`;
INSERT INTO ricarda3_kl_test_erp.accountgroups SELECT * FROM ricarda3_kl_erp.accountgroups;

TRUNCATE ricarda3_kl_test_erp.`accountsection`;
INSERT INTO ricarda3_kl_test_erp.accountsection SELECT * FROM ricarda3_kl_erp.accountsection;

TRUNCATE ricarda3_kl_test_erp.`areas`;
INSERT INTO ricarda3_kl_test_erp.areas SELECT * FROM ricarda3_kl_erp.areas;

TRUNCATE ricarda3_kl_test_erp.`audittrail`;
/* INSERT INTO ricarda3_kl_test_erp.audittrail SELECT * FROM ricarda3_kl_erp.audittrail; */

TRUNCATE ricarda3_kl_test_erp.`bankaccounts`;
INSERT INTO ricarda3_kl_test_erp.bankaccounts SELECT * FROM ricarda3_kl_erp.bankaccounts;

TRUNCATE ricarda3_kl_test_erp.`bankaccountusers`;
INSERT INTO ricarda3_kl_test_erp.bankaccountusers SELECT * FROM ricarda3_kl_erp.bankaccountusers;

TRUNCATE ricarda3_kl_test_erp.`banktrans`;
INSERT INTO ricarda3_kl_test_erp.banktrans SELECT * FROM ricarda3_kl_erp.banktrans;

TRUNCATE ricarda3_kl_test_erp.`bom`;
INSERT INTO ricarda3_kl_test_erp.bom SELECT * FROM ricarda3_kl_erp.bom;

TRUNCATE ricarda3_kl_test_erp.`buckets`;
INSERT INTO ricarda3_kl_test_erp.buckets SELECT * FROM ricarda3_kl_erp.buckets;

TRUNCATE ricarda3_kl_test_erp.`chartdetails`;
INSERT INTO ricarda3_kl_test_erp.chartdetails SELECT * FROM ricarda3_kl_erp.chartdetails;

TRUNCATE ricarda3_kl_test_erp.`chartmaster`;
INSERT INTO ricarda3_kl_test_erp.chartmaster SELECT * FROM ricarda3_kl_erp.chartmaster;

TRUNCATE ricarda3_kl_test_erp.`chartmasterIK`;
INSERT INTO ricarda3_kl_test_erp.chartmasterIK SELECT * FROM ricarda3_kl_erp.chartmasterIK;

TRUNCATE ricarda3_kl_test_erp.`chartmasterPI`;
INSERT INTO ricarda3_kl_test_erp.chartmasterPI SELECT * FROM ricarda3_kl_erp.chartmasterPI;

TRUNCATE ricarda3_kl_test_erp.`chartmasterPMA`;
INSERT INTO ricarda3_kl_test_erp.chartmasterPMA SELECT * FROM ricarda3_kl_erp.chartmasterPMA;

TRUNCATE ricarda3_kl_test_erp.`chartmasterPT`;
INSERT INTO ricarda3_kl_test_erp.chartmasterPT SELECT * FROM ricarda3_kl_erp.chartmasterPT;

TRUNCATE ricarda3_kl_test_erp.`cogsglpostings`;
INSERT INTO ricarda3_kl_test_erp.cogsglpostings SELECT * FROM ricarda3_kl_erp.cogsglpostings;

TRUNCATE ricarda3_kl_test_erp.`companies`;
INSERT INTO ricarda3_kl_test_erp.companies SELECT * FROM ricarda3_kl_erp.companies;

TRUNCATE ricarda3_kl_test_erp.`config`;
INSERT INTO ricarda3_kl_test_erp.config SELECT * FROM ricarda3_kl_erp.config;

TRUNCATE ricarda3_kl_test_erp.`contractbom`;
INSERT INTO ricarda3_kl_test_erp.contractbom SELECT * FROM ricarda3_kl_erp.contractbom;

TRUNCATE ricarda3_kl_test_erp.`contractcharges`;
INSERT INTO ricarda3_kl_test_erp.contractcharges SELECT * FROM ricarda3_kl_erp.contractcharges;

TRUNCATE ricarda3_kl_test_erp.`contractreqts`;
INSERT INTO ricarda3_kl_test_erp.contractreqts SELECT * FROM ricarda3_kl_erp.contractreqts;

TRUNCATE ricarda3_kl_test_erp.`contracts`;
INSERT INTO ricarda3_kl_test_erp.contracts SELECT * FROM ricarda3_kl_erp.contracts;

TRUNCATE ricarda3_kl_test_erp.`currencies`;
INSERT INTO ricarda3_kl_test_erp.currencies SELECT * FROM ricarda3_kl_erp.currencies;

TRUNCATE ricarda3_kl_test_erp.`custallocns`;
INSERT INTO ricarda3_kl_test_erp.custallocns SELECT * FROM ricarda3_kl_erp.custallocns;

TRUNCATE ricarda3_kl_test_erp.`custbranch`;
INSERT INTO ricarda3_kl_test_erp.custbranch SELECT * FROM ricarda3_kl_erp.custbranch;

TRUNCATE ricarda3_kl_test_erp.`custcontacts`;
INSERT INTO ricarda3_kl_test_erp.custcontacts SELECT * FROM ricarda3_kl_erp.custcontacts;

TRUNCATE ricarda3_kl_test_erp.`custitem`;
INSERT INTO ricarda3_kl_test_erp.custitem SELECT * FROM ricarda3_kl_erp.custitem;

TRUNCATE ricarda3_kl_test_erp.`custnotes`;
INSERT INTO ricarda3_kl_test_erp.custnotes SELECT * FROM ricarda3_kl_erp.custnotes;

TRUNCATE ricarda3_kl_test_erp.`debtorsmaster`;
INSERT INTO ricarda3_kl_test_erp.debtorsmaster SELECT * FROM ricarda3_kl_erp.debtorsmaster;

TRUNCATE ricarda3_kl_test_erp.`debtortrans`;
INSERT INTO ricarda3_kl_test_erp.debtortrans SELECT * FROM ricarda3_kl_erp.debtortrans;

TRUNCATE ricarda3_kl_test_erp.`debtortranstaxes`;
INSERT INTO ricarda3_kl_test_erp.debtortranstaxes SELECT * FROM ricarda3_kl_erp.debtortranstaxes;

TRUNCATE ricarda3_kl_test_erp.`debtortype`;
INSERT INTO ricarda3_kl_test_erp.debtortype SELECT * FROM ricarda3_kl_erp.debtortype;

TRUNCATE ricarda3_kl_test_erp.`debtortypenotes`;
INSERT INTO ricarda3_kl_test_erp.debtortypenotes SELECT * FROM ricarda3_kl_erp.debtortypenotes;

TRUNCATE ricarda3_kl_test_erp.`deliverynotes`;
INSERT INTO ricarda3_kl_test_erp.deliverynotes SELECT * FROM ricarda3_kl_erp.deliverynotes;

TRUNCATE ricarda3_kl_test_erp.`departments`;
INSERT INTO ricarda3_kl_test_erp.departments SELECT * FROM ricarda3_kl_erp.departments;

TRUNCATE ricarda3_kl_test_erp.`discountmatrix`;
INSERT INTO ricarda3_kl_test_erp.discountmatrix SELECT * FROM ricarda3_kl_erp.discountmatrix;

TRUNCATE ricarda3_kl_test_erp.`edi_orders_segs`;
INSERT INTO ricarda3_kl_test_erp.edi_orders_segs SELECT * FROM ricarda3_kl_erp.edi_orders_segs;

TRUNCATE ricarda3_kl_test_erp.`ediitemmapping`;
INSERT INTO ricarda3_kl_test_erp.ediitemmapping SELECT * FROM ricarda3_kl_erp.ediitemmapping;

TRUNCATE ricarda3_kl_test_erp.`edimessageformat`;
INSERT INTO ricarda3_kl_test_erp.edimessageformat SELECT * FROM ricarda3_kl_erp.edimessageformat;

TRUNCATE ricarda3_kl_test_erp.`edi_orders_seg_groups`;
INSERT INTO ricarda3_kl_test_erp.edi_orders_seg_groups SELECT * FROM ricarda3_kl_erp.edi_orders_seg_groups;

TRUNCATE ricarda3_kl_test_erp.`emailsettings`;
INSERT INTO ricarda3_kl_test_erp.emailsettings SELECT * FROM ricarda3_kl_erp.emailsettings;

TRUNCATE ricarda3_kl_test_erp.`factorcompanies`;
INSERT INTO ricarda3_kl_test_erp.factorcompanies SELECT * FROM ricarda3_kl_erp.factorcompanies;

TRUNCATE ricarda3_kl_test_erp.`fixedassetcategories`;
INSERT INTO ricarda3_kl_test_erp.fixedassetcategories SELECT * FROM ricarda3_kl_erp.fixedassetcategories;

TRUNCATE ricarda3_kl_test_erp.`fixedassetlocations`;
INSERT INTO ricarda3_kl_test_erp.fixedassetlocations SELECT * FROM ricarda3_kl_erp.fixedassetlocations;

TRUNCATE ricarda3_kl_test_erp.`fixedassets`;
INSERT INTO ricarda3_kl_test_erp.fixedassets SELECT * FROM ricarda3_kl_erp.fixedassets;

TRUNCATE ricarda3_kl_test_erp.`fixedassettasks`;
INSERT INTO ricarda3_kl_test_erp.fixedassettasks SELECT * FROM ricarda3_kl_erp.fixedassettasks;

TRUNCATE ricarda3_kl_test_erp.`fixedassettrans`;
INSERT INTO ricarda3_kl_test_erp.fixedassettrans SELECT * FROM ricarda3_kl_erp.fixedassettrans;

TRUNCATE ricarda3_kl_test_erp.`freightcosts`;
INSERT INTO ricarda3_kl_test_erp.freightcosts SELECT * FROM ricarda3_kl_erp.freightcosts;

TRUNCATE ricarda3_kl_test_erp.`geocode_param`;
INSERT INTO ricarda3_kl_test_erp.geocode_param SELECT * FROM ricarda3_kl_erp.geocode_param;

TRUNCATE ricarda3_kl_test_erp.`glaccountusers`;
INSERT INTO ricarda3_kl_test_erp.glaccountusers SELECT * FROM ricarda3_kl_erp.glaccountusers;

TRUNCATE ricarda3_kl_test_erp.`gltrans`;
INSERT INTO ricarda3_kl_test_erp.gltrans SELECT * FROM ricarda3_kl_erp.gltrans WHERE periodno <= 30;
INSERT INTO ricarda3_kl_test_erp.gltrans SELECT * FROM ricarda3_kl_erp.gltrans WHERE periodno > 30 AND periodno <= 60;
INSERT INTO ricarda3_kl_test_erp.gltrans SELECT * FROM ricarda3_kl_erp.gltrans WHERE periodno > 60 AND periodno <= 80;
INSERT INTO ricarda3_kl_test_erp.gltrans SELECT * FROM ricarda3_kl_erp.gltrans WHERE periodno > 80 AND periodno <= 90;
INSERT INTO ricarda3_kl_test_erp.gltrans SELECT * FROM ricarda3_kl_erp.gltrans WHERE periodno > 90 AND periodno <= 100;
INSERT INTO ricarda3_kl_test_erp.gltrans SELECT * FROM ricarda3_kl_erp.gltrans WHERE periodno > 100 AND periodno <= 110;
INSERT INTO ricarda3_kl_test_erp.gltrans SELECT * FROM ricarda3_kl_erp.gltrans WHERE periodno > 110 AND periodno <= 115;
INSERT INTO ricarda3_kl_test_erp.gltrans SELECT * FROM ricarda3_kl_erp.gltrans WHERE periodno > 115 AND periodno <= 120;
INSERT INTO ricarda3_kl_test_erp.gltrans SELECT * FROM ricarda3_kl_erp.gltrans WHERE periodno > 120 AND periodno <= 125;
INSERT INTO ricarda3_kl_test_erp.gltrans SELECT * FROM ricarda3_kl_erp.gltrans WHERE periodno > 125 AND periodno <= 130;
INSERT INTO ricarda3_kl_test_erp.gltrans SELECT * FROM ricarda3_kl_erp.gltrans WHERE periodno > 130;

TRUNCATE ricarda3_kl_test_erp.`grns`;
INSERT INTO ricarda3_kl_test_erp.grns SELECT * FROM ricarda3_kl_erp.grns;

TRUNCATE ricarda3_kl_test_erp.`holdreasons`;
INSERT INTO ricarda3_kl_test_erp.holdreasons SELECT * FROM ricarda3_kl_erp.holdreasons;

TRUNCATE ricarda3_kl_test_erp.`internalstockcatrole`;
INSERT INTO ricarda3_kl_test_erp.internalstockcatrole SELECT * FROM ricarda3_kl_erp.internalstockcatrole;

TRUNCATE ricarda3_kl_test_erp.`kladjustrl`;
INSERT INTO ricarda3_kl_test_erp.kladjustrl SELECT * FROM ricarda3_kl_erp.kladjustrl;

TRUNCATE ricarda3_kl_test_erp.`klchangeprice`;
INSERT INTO ricarda3_kl_test_erp.klchangeprice SELECT * FROM ricarda3_kl_erp.klchangeprice;

TRUNCATE ricarda3_kl_test_erp.`kladjustrl`;
INSERT INTO ricarda3_kl_test_erp.kladjustrl SELECT * FROM ricarda3_kl_erp.kladjustrl;

TRUNCATE ricarda3_kl_test_erp.`klconsignment`;
INSERT INTO ricarda3_kl_test_erp.klconsignment SELECT * FROM ricarda3_kl_erp.klconsignment;

TRUNCATE ricarda3_kl_test_erp.`klfreeexchanges`;
INSERT INTO ricarda3_kl_test_erp.klfreeexchanges SELECT * FROM ricarda3_kl_erp.klfreeexchanges;

TRUNCATE ricarda3_kl_test_erp.`klmovetodiscount20`;
INSERT INTO ricarda3_kl_test_erp.klmovetodiscount20 SELECT * FROM ricarda3_kl_erp.klmovetodiscount20;

TRUNCATE ricarda3_kl_test_erp.`klmovetodiscount50`;
INSERT INTO ricarda3_kl_test_erp.klmovetodiscount50 SELECT * FROM ricarda3_kl_erp.klmovetodiscount50;

TRUNCATE ricarda3_kl_test_erp.`klmovetodiscount80`;
INSERT INTO ricarda3_kl_test_erp.klmovetodiscount80 SELECT * FROM ricarda3_kl_erp.klmovetodiscount80;

TRUNCATE ricarda3_kl_test_erp.`klolddatapurged`;
INSERT INTO ricarda3_kl_test_erp.klolddatapurged SELECT * FROM ricarda3_kl_erp.klolddatapurged;

TRUNCATE ricarda3_kl_test_erp.`klonlinepartners`;
INSERT INTO ricarda3_kl_test_erp.klonlinepartners SELECT * FROM ricarda3_kl_erp.klonlinepartners;

TRUNCATE ricarda3_kl_test_erp.`klpostatus`;
INSERT INTO ricarda3_kl_test_erp.klpostatus SELECT * FROM ricarda3_kl_erp.klpostatus;

TRUNCATE ricarda3_kl_test_erp.`klretailcustomers`;
INSERT INTO ricarda3_kl_test_erp.klretailcustomers SELECT * FROM ricarda3_kl_erp.klretailcustomers;

TRUNCATE ricarda3_kl_test_erp.`klretailpartners`;
INSERT INTO ricarda3_kl_test_erp.klretailpartners SELECT * FROM ricarda3_kl_erp.klretailpartners;

TRUNCATE ricarda3_kl_test_erp.`klrevisedemaildomains`;
INSERT INTO ricarda3_kl_test_erp.klrevisedemaildomains SELECT * FROM ricarda3_kl_erp.klrevisedemaildomains;

TRUNCATE ricarda3_kl_test_erp.`klsalesperformance`;
INSERT INTO ricarda3_kl_test_erp.klsalesperformance SELECT * FROM ricarda3_kl_erp.klsalesperformance;

TRUNCATE ricarda3_kl_test_erp.`labelfields`;
INSERT INTO ricarda3_kl_test_erp.labelfields SELECT * FROM ricarda3_kl_erp.labelfields;

TRUNCATE ricarda3_kl_test_erp.`labels`;
INSERT INTO ricarda3_kl_test_erp.labels SELECT * FROM ricarda3_kl_erp.labels;

INSERT INTO ricarda3_kl_test_erp.lastcostrollup SELECT * FROM ricarda3_kl_erp.lastcostrollup;
TRUNCATE ricarda3_kl_test_erp.`lastcostrollup`;

INSERT INTO ricarda3_kl_test_erp.levels SELECT * FROM ricarda3_kl_erp.levels;
TRUNCATE ricarda3_kl_test_erp.`levels`;

TRUNCATE ricarda3_kl_test_erp.`locations`;
INSERT INTO ricarda3_kl_test_erp.locations SELECT * FROM ricarda3_kl_erp.locations;

TRUNCATE ricarda3_kl_test_erp.`locationtypes`;
INSERT INTO ricarda3_kl_test_erp.locationtypes SELECT * FROM ricarda3_kl_erp.locationtypes;

TRUNCATE ricarda3_kl_test_erp.`locationusers`;
INSERT INTO ricarda3_kl_test_erp.locationusers SELECT * FROM ricarda3_kl_erp.locationusers;

TRUNCATE ricarda3_kl_test_erp.`locationzones`;
INSERT INTO ricarda3_kl_test_erp.locationzones SELECT * FROM ricarda3_kl_erp.locationzones;

TRUNCATE ricarda3_kl_test_erp.`locstock`;
INSERT INTO ricarda3_kl_test_erp.locstock SELECT * FROM ricarda3_kl_erp.locstock;

TRUNCATE ricarda3_kl_test_erp.`loctransfercancellations`;
INSERT INTO ricarda3_kl_test_erp.loctransfercancellations SELECT * FROM ricarda3_kl_erp.loctransfercancellations;

TRUNCATE ricarda3_kl_test_erp.`loctransfers`;
INSERT INTO ricarda3_kl_test_erp.loctransfers SELECT * FROM ricarda3_kl_erp.loctransfers;

TRUNCATE ricarda3_kl_test_erp.`mailgroupdetails`;
INSERT INTO ricarda3_kl_test_erp.mailgroupdetails SELECT * FROM ricarda3_kl_erp.mailgroupdetails;

TRUNCATE ricarda3_kl_test_erp.`mailgroups`;
INSERT INTO ricarda3_kl_test_erp.mailgroups SELECT * FROM ricarda3_kl_erp.mailgroups;

TRUNCATE ricarda3_kl_test_erp.`manufacturers`;
INSERT INTO ricarda3_kl_test_erp.manufacturers SELECT * FROM ricarda3_kl_erp.manufacturers;

TRUNCATE ricarda3_kl_test_erp.`mrpcalendar`;
INSERT INTO ricarda3_kl_test_erp.mrpcalendar SELECT * FROM ricarda3_kl_erp.mrpcalendar;

TRUNCATE ricarda3_kl_test_erp.`mrpdemands`;
INSERT INTO ricarda3_kl_test_erp.mrpdemands SELECT * FROM ricarda3_kl_erp.mrpdemands;

TRUNCATE ricarda3_kl_test_erp.`mrpdemandtypes`;
INSERT INTO ricarda3_kl_test_erp.mrpdemandtypes SELECT * FROM ricarda3_kl_erp.mrpdemandtypes;

TRUNCATE ricarda3_kl_test_erp.`mrpparameters`;
INSERT INTO ricarda3_kl_test_erp.mrpparameters SELECT * FROM ricarda3_kl_erp.mrpparameters;

TRUNCATE ricarda3_kl_test_erp.`mrpplannedorders`;
INSERT INTO ricarda3_kl_test_erp.mrpplannedorders SELECT * FROM ricarda3_kl_erp.mrpplannedorders;

TRUNCATE ricarda3_kl_test_erp.`mrprequirements`;
INSERT INTO ricarda3_kl_test_erp.mrprequirements SELECT * FROM ricarda3_kl_erp.mrprequirements;

TRUNCATE ricarda3_kl_test_erp.`mrpsupplies`;
INSERT INTO ricarda3_kl_test_erp.mrpsupplies SELECT * FROM ricarda3_kl_erp.mrpsupplies;

TRUNCATE ricarda3_kl_test_erp.`offers`;
INSERT INTO ricarda3_kl_test_erp.offers SELECT * FROM ricarda3_kl_erp.offers;

TRUNCATE ricarda3_kl_test_erp.`orderdeliverydifferenceslog`;
INSERT INTO ricarda3_kl_test_erp.orderdeliverydifferenceslog SELECT * FROM ricarda3_kl_erp.orderdeliverydifferenceslog;

TRUNCATE ricarda3_kl_test_erp.`packagingused`;
INSERT INTO ricarda3_kl_test_erp.packagingused SELECT * FROM ricarda3_kl_erp.packagingused;

TRUNCATE ricarda3_kl_test_erp.`paymentmethods`;
INSERT INTO ricarda3_kl_test_erp.paymentmethods SELECT * FROM ricarda3_kl_erp.paymentmethods;

TRUNCATE ricarda3_kl_test_erp.`paymentterms`;
INSERT INTO ricarda3_kl_test_erp.paymentterms SELECT * FROM ricarda3_kl_erp.paymentterms;

TRUNCATE ricarda3_kl_test_erp.`pcashdetails`;
INSERT INTO ricarda3_kl_test_erp.pcashdetails SELECT * FROM ricarda3_kl_erp.pcashdetails;

TRUNCATE ricarda3_kl_test_erp.`pcexpenses`;
INSERT INTO ricarda3_kl_test_erp.pcexpenses SELECT * FROM ricarda3_kl_erp.pcexpenses;

TRUNCATE ricarda3_kl_test_erp.`pctabexpenses`;
INSERT INTO ricarda3_kl_test_erp.pctabexpenses SELECT * FROM ricarda3_kl_erp.pctabexpenses;

TRUNCATE ricarda3_kl_test_erp.`pcsalaries`;
INSERT INTO ricarda3_kl_test_erp.pcsalaries SELECT * FROM ricarda3_kl_erp.pcsalaries;

TRUNCATE ricarda3_kl_test_erp.`pctabs`;
INSERT INTO ricarda3_kl_test_erp.pctabs SELECT * FROM ricarda3_kl_erp.pctabs;

TRUNCATE ricarda3_kl_test_erp.`pctypetabs`;
INSERT INTO ricarda3_kl_test_erp.pctypetabs SELECT * FROM ricarda3_kl_erp.pctypetabs;

TRUNCATE ricarda3_kl_test_erp.`periods`;
INSERT INTO ricarda3_kl_test_erp.periods SELECT * FROM ricarda3_kl_erp.periods;

TRUNCATE ricarda3_kl_test_erp.`pickinglistdetails`;
INSERT INTO ricarda3_kl_test_erp.pickinglistdetails SELECT * FROM ricarda3_kl_erp.pickinglistdetails;

TRUNCATE ricarda3_kl_test_erp.`pickinglists`;
INSERT INTO ricarda3_kl_test_erp.pickinglists SELECT * FROM ricarda3_kl_erp.pickinglists;

TRUNCATE ricarda3_kl_test_erp.`pricematrix`;
INSERT INTO ricarda3_kl_test_erp.pricematrix SELECT * FROM ricarda3_kl_erp.pricematrix;

TRUNCATE ricarda3_kl_test_erp.`prices`;
INSERT INTO ricarda3_kl_test_erp.prices SELECT * FROM ricarda3_kl_erp.prices;

TRUNCATE ricarda3_kl_test_erp.`prodspecs`;
INSERT INTO ricarda3_kl_test_erp.prodspecs SELECT * FROM ricarda3_kl_erp.prodspecs;

TRUNCATE ricarda3_kl_test_erp.`purchdata`;
INSERT INTO ricarda3_kl_test_erp.purchdata SELECT * FROM ricarda3_kl_erp.purchdata;

TRUNCATE ricarda3_kl_test_erp.`purchorderauth`;
INSERT INTO ricarda3_kl_test_erp.purchorderauth SELECT * FROM ricarda3_kl_erp.purchorderauth;

TRUNCATE ricarda3_kl_test_erp.`purchorderdetails`;
INSERT INTO ricarda3_kl_test_erp.purchorderdetails SELECT * FROM ricarda3_kl_erp.purchorderdetails;

TRUNCATE ricarda3_kl_test_erp.`purchorders`;
INSERT INTO ricarda3_kl_test_erp.purchorders SELECT * FROM ricarda3_kl_erp.purchorders;

TRUNCATE ricarda3_kl_test_erp.`qasamples`;
INSERT INTO ricarda3_kl_test_erp.qasamples SELECT * FROM ricarda3_kl_erp.qasamples;

TRUNCATE ricarda3_kl_test_erp.`qatests`;
INSERT INTO ricarda3_kl_test_erp.qatests SELECT * FROM ricarda3_kl_erp.qatests;

TRUNCATE ricarda3_kl_test_erp.`recurringsalesorders`;
INSERT INTO ricarda3_kl_test_erp.recurringsalesorders SELECT * FROM ricarda3_kl_erp.recurringsalesorders;

TRUNCATE ricarda3_kl_test_erp.`recurrsalesorderdetails`;
INSERT INTO ricarda3_kl_test_erp.recurrsalesorderdetails SELECT * FROM ricarda3_kl_erp.recurrsalesorderdetails;

TRUNCATE ricarda3_kl_test_erp.`relateditems`;
INSERT INTO ricarda3_kl_test_erp.relateditems SELECT * FROM ricarda3_kl_erp.relateditems;

TRUNCATE ricarda3_kl_test_erp.`reportcolumns`;
INSERT INTO ricarda3_kl_test_erp.reportcolumns SELECT * FROM ricarda3_kl_erp.reportcolumns;

TRUNCATE ricarda3_kl_test_erp.`reportfields`;
INSERT INTO ricarda3_kl_test_erp.reportfields SELECT * FROM ricarda3_kl_erp.reportfields;

TRUNCATE ricarda3_kl_test_erp.`reportheaders`;
INSERT INTO ricarda3_kl_test_erp.reportheaders SELECT * FROM ricarda3_kl_erp.reportheaders;

TRUNCATE ricarda3_kl_test_erp.`reportlets`;
INSERT INTO ricarda3_kl_test_erp.reportlets SELECT * FROM ricarda3_kl_erp.reportlets;

TRUNCATE ricarda3_kl_test_erp.`reportlinks`;
INSERT INTO ricarda3_kl_test_erp.reportlinks SELECT * FROM ricarda3_kl_erp.reportlinks;

TRUNCATE ricarda3_kl_test_erp.`reports`;
INSERT INTO ricarda3_kl_test_erp.reports SELECT * FROM ricarda3_kl_erp.reports;

TRUNCATE ricarda3_kl_test_erp.`returnitemreasons`;
INSERT INTO ricarda3_kl_test_erp.returnitemreasons SELECT * FROM ricarda3_kl_erp.returnitemreasons;

TRUNCATE ricarda3_kl_test_erp.`returneditems`;
INSERT INTO ricarda3_kl_test_erp.returneditems SELECT * FROM ricarda3_kl_erp.returneditems;

TRUNCATE ricarda3_kl_test_erp.`salariescalculated`;
INSERT INTO ricarda3_kl_test_erp.salariescalculated SELECT * FROM ricarda3_kl_erp.salariescalculated;

TRUNCATE ricarda3_kl_test_erp.`salesanalysis`;
INSERT INTO ricarda3_kl_test_erp.salesanalysis SELECT * FROM ricarda3_kl_erp.salesanalysis;

TRUNCATE ricarda3_kl_test_erp.`salescat`;
INSERT INTO ricarda3_kl_test_erp.salescat SELECT * FROM ricarda3_kl_erp.salescat;

TRUNCATE ricarda3_kl_test_erp.`salescatprod`;
INSERT INTO ricarda3_kl_test_erp.salescatprod SELECT * FROM ricarda3_kl_erp.salescatprod;

TRUNCATE ricarda3_kl_test_erp.`salescattranslations`;
INSERT INTO ricarda3_kl_test_erp.salescattranslations SELECT * FROM ricarda3_kl_erp.salescattranslations;

TRUNCATE ricarda3_kl_test_erp.`salesglpostings`;
INSERT INTO ricarda3_kl_test_erp.salesglpostings SELECT * FROM ricarda3_kl_erp.salesglpostings;

TRUNCATE ricarda3_kl_test_erp.`salesman`;
INSERT INTO ricarda3_kl_test_erp.salesman SELECT * FROM ricarda3_kl_erp.salesman;

TRUNCATE ricarda3_kl_test_erp.`salesorderdetails`;
INSERT INTO ricarda3_kl_test_erp.salesorderdetails SELECT * FROM ricarda3_kl_erp.salesorderdetails;

TRUNCATE ricarda3_kl_test_erp.`salesorders`;
INSERT INTO ricarda3_kl_test_erp.salesorders SELECT * FROM ricarda3_kl_erp.salesorders;

TRUNCATE ricarda3_kl_test_erp.`salestypes`;
INSERT INTO ricarda3_kl_test_erp.salestypes SELECT * FROM ricarda3_kl_erp.salestypes;

TRUNCATE ricarda3_kl_test_erp.`sampleresults`;
INSERT INTO ricarda3_kl_test_erp.sampleresults SELECT * FROM ricarda3_kl_erp.sampleresults;

TRUNCATE ricarda3_kl_test_erp.`scripts`;
INSERT INTO ricarda3_kl_test_erp.scripts SELECT * FROM ricarda3_kl_erp.scripts;

TRUNCATE ricarda3_kl_test_erp.`securitygroups`;
INSERT INTO ricarda3_kl_test_erp.securitygroups SELECT * FROM ricarda3_kl_erp.securitygroups;

TRUNCATE ricarda3_kl_test_erp.`securityroles`;
INSERT INTO ricarda3_kl_test_erp.securityroles SELECT * FROM ricarda3_kl_erp.securityroles;

TRUNCATE ricarda3_kl_test_erp.`securitytokens`;
INSERT INTO ricarda3_kl_test_erp.securitytokens SELECT * FROM ricarda3_kl_erp.securitytokens;

TRUNCATE ricarda3_kl_test_erp.`sellthroughsupport`;
INSERT INTO ricarda3_kl_test_erp.sellthroughsupport SELECT * FROM ricarda3_kl_erp.sellthroughsupport;

TRUNCATE ricarda3_kl_test_erp.`shipmentcharges`;
INSERT INTO ricarda3_kl_test_erp.shipmentcharges SELECT * FROM ricarda3_kl_erp.shipmentcharges;

TRUNCATE ricarda3_kl_test_erp.`shipments`;
INSERT INTO ricarda3_kl_test_erp.shipments SELECT * FROM ricarda3_kl_erp.shipments;

TRUNCATE ricarda3_kl_test_erp.`shippers`;
INSERT INTO ricarda3_kl_test_erp.shippers SELECT * FROM ricarda3_kl_erp.shippers;

TRUNCATE ricarda3_kl_test_erp.`stockcategory`;
INSERT INTO ricarda3_kl_test_erp.stockcategory SELECT * FROM ricarda3_kl_erp.stockcategory;

TRUNCATE ricarda3_kl_test_erp.`stockcatproperties`;
INSERT INTO ricarda3_kl_test_erp.stockcatproperties SELECT * FROM ricarda3_kl_erp.stockcatproperties;

TRUNCATE ricarda3_kl_test_erp.`stockcheckfreeze`;
INSERT INTO ricarda3_kl_test_erp.stockcheckfreeze SELECT * FROM ricarda3_kl_erp.stockcheckfreeze;

TRUNCATE ricarda3_kl_test_erp.`stockcounts`;
INSERT INTO ricarda3_kl_test_erp.stockcounts SELECT * FROM ricarda3_kl_erp.stockcounts;

TRUNCATE ricarda3_kl_test_erp.`stockdescriptiontranslations`;
INSERT INTO ricarda3_kl_test_erp.stockdescriptiontranslations SELECT * FROM ricarda3_kl_erp.stockdescriptiontranslations;

TRUNCATE ricarda3_kl_test_erp.`stockitemproperties`;
INSERT INTO ricarda3_kl_test_erp.stockitemproperties SELECT * FROM ricarda3_kl_erp.stockitemproperties;

TRUNCATE ricarda3_kl_test_erp.`stockmaster`;
INSERT INTO ricarda3_kl_test_erp.stockmaster SELECT * FROM ricarda3_kl_erp.stockmaster;

TRUNCATE ricarda3_kl_test_erp.`stockmoves`;
INSERT INTO ricarda3_kl_test_erp.stockmoves SELECT * FROM ricarda3_kl_erp.stockmoves WHERE prd <= 30;
INSERT INTO ricarda3_kl_test_erp.stockmoves SELECT * FROM ricarda3_kl_erp.stockmoves WHERE prd > 30 AND prd <= 60;
INSERT INTO ricarda3_kl_test_erp.stockmoves SELECT * FROM ricarda3_kl_erp.stockmoves WHERE prd > 60 AND prd <= 80;
INSERT INTO ricarda3_kl_test_erp.stockmoves SELECT * FROM ricarda3_kl_erp.stockmoves WHERE prd > 80 AND prd <= 90;
INSERT INTO ricarda3_kl_test_erp.stockmoves SELECT * FROM ricarda3_kl_erp.stockmoves WHERE prd > 90 AND prd <= 100;
INSERT INTO ricarda3_kl_test_erp.stockmoves SELECT * FROM ricarda3_kl_erp.stockmoves WHERE prd > 100 AND prd <= 110;
INSERT INTO ricarda3_kl_test_erp.stockmoves SELECT * FROM ricarda3_kl_erp.stockmoves WHERE prd > 110 AND prd <= 115;
INSERT INTO ricarda3_kl_test_erp.stockmoves SELECT * FROM ricarda3_kl_erp.stockmoves WHERE prd > 115 AND prd <= 120;
INSERT INTO ricarda3_kl_test_erp.stockmoves SELECT * FROM ricarda3_kl_erp.stockmoves WHERE prd > 120 AND prd <= 125;
INSERT INTO ricarda3_kl_test_erp.stockmoves SELECT * FROM ricarda3_kl_erp.stockmoves WHERE prd > 125 AND prd <= 130;
INSERT INTO ricarda3_kl_test_erp.stockmoves SELECT * FROM ricarda3_kl_erp.stockmoves WHERE prd > 130;

TRUNCATE ricarda3_kl_test_erp.`stockmovestaxes`;
INSERT INTO ricarda3_kl_test_erp.stockmovestaxes SELECT * FROM ricarda3_kl_erp.stockmovestaxes;

TRUNCATE ricarda3_kl_test_erp.`stockrequest`;
INSERT INTO ricarda3_kl_test_erp.stockrequest SELECT * FROM ricarda3_kl_erp.stockrequest;

TRUNCATE ricarda3_kl_test_erp.`stockrequestitems`;
INSERT INTO ricarda3_kl_test_erp.stockrequestitems SELECT * FROM ricarda3_kl_erp.stockrequestitems;

TRUNCATE ricarda3_kl_test_erp.`stockserialitems`;
INSERT INTO ricarda3_kl_test_erp.stockserialitems SELECT * FROM ricarda3_kl_erp.stockserialitems;

TRUNCATE ricarda3_kl_test_erp.`stockserialmoves`;
INSERT INTO ricarda3_kl_test_erp.stockserialmoves SELECT * FROM ricarda3_kl_erp.stockserialmoves;

TRUNCATE ricarda3_kl_test_erp.`suppallocs`;
INSERT INTO ricarda3_kl_test_erp.suppallocs SELECT * FROM ricarda3_kl_erp.suppallocs;

TRUNCATE ricarda3_kl_test_erp.`suppinvstogrn`;
INSERT INTO ricarda3_kl_test_erp.suppinvstogrn SELECT * FROM ricarda3_kl_erp.suppinvstogrn;

TRUNCATE ricarda3_kl_test_erp.`suppliercontacts`;
INSERT INTO ricarda3_kl_test_erp.suppliercontacts SELECT * FROM ricarda3_kl_erp.suppliercontacts;

TRUNCATE ricarda3_kl_test_erp.`supplierdiscounts`;
INSERT INTO ricarda3_kl_test_erp.supplierdiscounts SELECT * FROM ricarda3_kl_erp.supplierdiscounts;

TRUNCATE ricarda3_kl_test_erp.`suppliers`;
INSERT INTO ricarda3_kl_test_erp.suppliers SELECT * FROM ricarda3_kl_erp.suppliers;

TRUNCATE ricarda3_kl_test_erp.`suppliertype`;
INSERT INTO ricarda3_kl_test_erp.suppliertype SELECT * FROM ricarda3_kl_erp.suppliertype;

TRUNCATE ricarda3_kl_test_erp.`supptrans`;
INSERT INTO ricarda3_kl_test_erp.supptrans SELECT * FROM ricarda3_kl_erp.supptrans;

TRUNCATE ricarda3_kl_test_erp.`supptranstaxes`;
INSERT INTO ricarda3_kl_test_erp.supptranstaxes SELECT * FROM ricarda3_kl_erp.supptranstaxes;

TRUNCATE ricarda3_kl_test_erp.`systypes`;
INSERT INTO ricarda3_kl_test_erp.systypes SELECT * FROM ricarda3_kl_erp.systypes;

TRUNCATE ricarda3_kl_test_erp.`tags`;
INSERT INTO ricarda3_kl_test_erp.tags SELECT * FROM ricarda3_kl_erp.tags;

TRUNCATE ricarda3_kl_test_erp.`taxauthorities`;
INSERT INTO ricarda3_kl_test_erp.taxauthorities SELECT * FROM ricarda3_kl_erp.taxauthorities;

TRUNCATE ricarda3_kl_test_erp.`taxauthrates`;
INSERT INTO ricarda3_kl_test_erp.taxauthrates SELECT * FROM ricarda3_kl_erp.taxauthrates;

TRUNCATE ricarda3_kl_test_erp.`taxcategories`;
INSERT INTO ricarda3_kl_test_erp.taxcategories SELECT * FROM ricarda3_kl_erp.taxcategories;

TRUNCATE ricarda3_kl_test_erp.`taxgroups`;
INSERT INTO ricarda3_kl_test_erp.taxgroups SELECT * FROM ricarda3_kl_erp.taxgroups;

TRUNCATE ricarda3_kl_test_erp.`taxgrouptaxes`;
INSERT INTO ricarda3_kl_test_erp.taxgrouptaxes SELECT * FROM ricarda3_kl_erp.taxgrouptaxes;

TRUNCATE ricarda3_kl_test_erp.`taxprovinces`;
INSERT INTO ricarda3_kl_test_erp.taxprovinces SELECT * FROM ricarda3_kl_erp.taxprovinces;

TRUNCATE ricarda3_kl_test_erp.`tenderitems`;
INSERT INTO ricarda3_kl_test_erp.tenderitems SELECT * FROM ricarda3_kl_erp.tenderitems;

TRUNCATE ricarda3_kl_test_erp.`tenders`;
INSERT INTO ricarda3_kl_test_erp.tenders SELECT * FROM ricarda3_kl_erp.tenders;

TRUNCATE ricarda3_kl_test_erp.`tendersuppliers`;
INSERT INTO ricarda3_kl_test_erp.tendersuppliers SELECT * FROM ricarda3_kl_erp.tendersuppliers;

TRUNCATE ricarda3_kl_test_erp.`unitsofdimension`;
INSERT INTO ricarda3_kl_test_erp.unitsofdimension SELECT * FROM ricarda3_kl_erp.unitsofdimension;

TRUNCATE ricarda3_kl_test_erp.`unitsofmeasure`;
INSERT INTO ricarda3_kl_test_erp.unitsofmeasure SELECT * FROM ricarda3_kl_erp.unitsofmeasure;

TRUNCATE ricarda3_kl_test_erp.`woitems`;
INSERT INTO ricarda3_kl_test_erp.woitems SELECT * FROM ricarda3_kl_erp.woitems;

TRUNCATE ricarda3_kl_test_erp.`worequirements`;
INSERT INTO ricarda3_kl_test_erp.worequirements SELECT * FROM ricarda3_kl_erp.worequirements;

TRUNCATE ricarda3_kl_test_erp.`workcentres`;
INSERT INTO ricarda3_kl_test_erp.workcentres SELECT * FROM ricarda3_kl_erp.workcentres;

TRUNCATE ricarda3_kl_test_erp.`workorders`;
INSERT INTO ricarda3_kl_test_erp.workorders SELECT * FROM ricarda3_kl_erp.workorders;

TRUNCATE ricarda3_kl_test_erp.`woserialnos`;
INSERT INTO ricarda3_kl_test_erp.woserialnos SELECT * FROM ricarda3_kl_erp.woserialnos;

TRUNCATE ricarda3_kl_test_erp.`www_users`;
INSERT INTO ricarda3_kl_test_erp.www_users SELECT * FROM ricarda3_kl_erp.www_users;

UPDATE  ricarda3_kl_test_erp.`config` SET  `confvalue` =  'companies/ricarda3_kl_test_erp/part_pics' WHERE  `confname` =  'part_pics_dir';
UPDATE  ricarda3_kl_test_erp.`config` SET  `confvalue` =  'companies/ricarda3_kl_test_erp/reports' WHERE  `confname` =  'reports_dir';
UPDATE  ricarda3_kl_test_erp.`config` SET  `confvalue` =  'companies/ricarda3_kl_test_erp/logs' WHERE  `confname` =  'LogPath';

UPDATE  ricarda3_kl_test_erp.`config` SET  `confvalue` =  'TEST SHOP' WHERE  `confname` =  'ShopName';
UPDATE  ricarda3_kl_test_erp.`config` SET  `confvalue` =  'TEST SHOP' WHERE  `confname` =  'ShopTitle';

UPDATE  ricarda3_kl_test_erp.`config` SET  `confvalue` =  '' WHERE  `confname` =  'InventoryManagerEmail';
UPDATE  ricarda3_kl_test_erp.`config` SET  `confvalue` =  '' WHERE  `confname` =  'FactoryManagerEmail';
UPDATE  ricarda3_kl_test_erp.`config` SET  `confvalue` =  '' WHERE  `confname` =  'PurchasingManagerEmail';

UPDATE  ricarda3_kl_test_erp.`config` SET  `confvalue` =  'test' WHERE  `confname` =  'ShopMode';
UPDATE  ricarda3_kl_test_erp.`config` SET  `confvalue` =  '1372497542' WHERE  `confname` =  'ShopPayPalPassword';
UPDATE  ricarda3_kl_test_erp.`config` SET  `confvalue` =  'AKh80SD3d.pLz9oyaerqiR90yzDdARP3knOWMSTyjcbBNEns94xTl6WW' WHERE  `confname` =  'ShopPayPalSignature';
UPDATE  ricarda3_kl_test_erp.`config` SET  `confvalue` =  'testmerchant_api1.kapal-laut.com' WHERE  `confname` =  'ShopPayPalUser';

UPDATE ricarda3_kl_test_erp.www_users SET theme = "gel";
UPDATE ricarda3_kl_test_erp.www_users SET blocked = 0 WHERE userid LIKE "999%";

SET FOREIGN_KEY_CHECKS=1;

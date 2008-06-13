RENAME TABLE `AccountGroups` TO `accountgroups`;
RENAME TABLE `Areas` TO `areas`;
RENAME TABLE `BOM` TO `bom`;
RENAME TABLE `BankAccounts` TO `bankaccounts`;
RENAME TABLE `BankTrans` TO `banktrans`;
RENAME TABLE `Buckets` TO `buckets`;
RENAME TABLE `COGSGLPostings` TO `cogsglpostings`;
RENAME TABLE `ChartDetails` TO `chartdetails`;
RENAME TABLE `ChartMaster` TO `chartmaster`;
RENAME TABLE `Companies` TO `companies`;
RENAME TABLE `ContractBOM` TO `contractbom`;
RENAME TABLE `ContractReqts` TO `contractreqts`;
RENAME TABLE `Contracts` TO `contracts`;
RENAME TABLE `Currencies` TO `currencies`;
RENAME TABLE `CustAllocns` TO `custallocns`;
RENAME TABLE `CustBranch` TO `custbranch`;
RENAME TABLE `DebtorTrans` TO `debtortrans`;
RENAME TABLE `DebtorsMaster` TO `debtorsmaster`;
RENAME TABLE `DiscountMatrix` TO `discountmatrix`;
RENAME TABLE `EDIItemMapping` TO `ediitemmapping`;
RENAME TABLE `EDIMessageFormat` TO `edimessageformat`;
RENAME TABLE `EDI_ORDERS_Seg_Groups` TO `edi_orders_seg_groups`;
RENAME TABLE `EDI_ORDERS_Segs` TO `edi_orders_segs`;
RENAME TABLE `FreightCosts` TO `freightcosts`;
RENAME TABLE `GLTrans` TO `gltrans`;
RENAME TABLE `GRNs` TO `grns`;
DROP TABLE `Help`;
RENAME TABLE `HoldReasons` TO `holdreasons`;
RENAME TABLE `LastCostRollUp` TO `lastcostrollup`;
RENAME TABLE `LocStock` TO `locstock`;
RENAME TABLE `LocTransfers` TO `loctransfers`;
RENAME TABLE `Locations` TO `locations`;
RENAME TABLE `OrderDeliveryDifferencesLog` TO `orderdeliverydifferenceslog`;
RENAME TABLE `PaymentTerms` TO `paymentterms`;
RENAME TABLE `Periods` TO `periods`;
RENAME TABLE `Prices` TO `prices`;
RENAME TABLE `PurchData` TO `purchdata`;
RENAME TABLE `PurchOrderDetails` TO `purchorderdetails`;
RENAME TABLE `PurchOrders` TO `purchorders`;
RENAME TABLE `ReportColumns` TO `reportcolumns`;
RENAME TABLE `ReportHeaders` TO `reportheaders`;
RENAME TABLE `SalesAnalysis` TO `salesanalysis`;
RENAME TABLE `SalesGLPostings` TO `salesglpostings`;
RENAME TABLE `SalesOrderDetails` TO `salesorderdetails`;
RENAME TABLE `SalesOrders` TO `salesorders`;
RENAME TABLE `SalesTypes` TO `salestypes`;
RENAME TABLE `Salesman` TO `salesman`;
RENAME TABLE `Scripts` TO `scripts`;
RENAME TABLE `ShipmentCharges` TO `shipmentcharges`;
RENAME TABLE `Shipments` TO `shipments`;
RENAME TABLE `Shippers` TO `shippers`;
RENAME TABLE `StockCategory` TO `stockcategory`;
RENAME TABLE `StockCheckFreeze` TO `stockcheckfreeze`;
RENAME TABLE `StockCounts` TO `stockcounts`;
RENAME TABLE `StockMaster` TO `stockmaster`;
RENAME TABLE `StockMoves` TO `stockmoves`;
RENAME TABLE `StockSerialItems` TO `stockserialitems`;
RENAME TABLE `StockSerialMoves` TO `stockserialmoves`;
RENAME TABLE `SuppAllocs` TO `suppallocs`;
RENAME TABLE `SuppTrans` TO `supptrans`;
RENAME TABLE `SupplierContacts` TO `suppliercontacts`;
RENAME TABLE `Suppliers` TO `suppliers`;
RENAME TABLE `SysTypes` TO `systypes`;
RENAME TABLE `TaxAuthLevels` TO `taxauthlevels`;
RENAME TABLE `TaxAuthorities` TO `taxauthorities`;
RENAME TABLE `WWW_Users` TO `www_users`;
RENAME TABLE `WorkCentres` TO `workcentres`;
RENAME TABLE `WorksOrders` TO `worksorders`;
ALTER TABLE `accountgroups` CHANGE GroupName groupname char(30)  NOT NULL default '';
ALTER TABLE `accountgroups` CHANGE SectionInAccounts sectioninaccounts smallint(6)  NOT NULL default '0';
ALTER TABLE `accountgroups` CHANGE PandL pandl tinyint(4)  NOT NULL default '1';
ALTER TABLE `accountgroups` CHANGE SequenceInTB sequenceintb smallint(6)  NOT NULL default '0';
ALTER TABLE `areas` CHANGE AreaCode areacode char(2)  NOT NULL default '';
ALTER TABLE `areas` CHANGE AreaDescription areadescription varchar(25)  NOT NULL default '';
ALTER TABLE `bom` CHANGE Parent parent char(20)  NOT NULL default '';
ALTER TABLE `bom` CHANGE Component component char(20)  NOT NULL default '';
ALTER TABLE `bom` CHANGE WorkCentreAdded workcentreadded char(5)  NOT NULL default '';
ALTER TABLE `bom` CHANGE LocCode loccode char(5)  NOT NULL default '';
ALTER TABLE `bom` CHANGE EffectiveAfter effectiveafter date  NOT NULL default '0000-00-00';
ALTER TABLE `bom` CHANGE EffectiveTo effectiveto date  NOT NULL default '9999-12-31';
ALTER TABLE `bom` CHANGE Quantity quantity double(16,4)  NOT NULL default '1.0000';
ALTER TABLE `bankaccounts` CHANGE AccountCode accountcode int(11)  NOT NULL default '0';
ALTER TABLE `bankaccounts` CHANGE BankAccountName bankaccountname char(50)  NOT NULL default '';
ALTER TABLE `bankaccounts` CHANGE BankAccountNumber bankaccountnumber char(50)  NOT NULL default '';
ALTER TABLE `bankaccounts` CHANGE BankAddress bankaddress char(50)  default NULL;
ALTER TABLE `banktrans` CHANGE BankTransID banktransid bigint(20)  NOT NULL auto_increment;
ALTER TABLE `banktrans` CHANGE Type type smallint(6)  NOT NULL default '0';
ALTER TABLE `banktrans` CHANGE TransNo transno bigint(20)  NOT NULL default '0';
ALTER TABLE `banktrans` CHANGE BankAct bankact int(11)  NOT NULL default '0';
ALTER TABLE `banktrans` CHANGE Ref ref varchar(50)  NOT NULL default '';
ALTER TABLE `banktrans` CHANGE AmountCleared amountcleared float  NOT NULL default '0';
ALTER TABLE `banktrans` CHANGE ExRate exrate double  NOT NULL default '1';
ALTER TABLE `banktrans` CHANGE TransDate transdate date  NOT NULL default '0000-00-00';
ALTER TABLE `banktrans` CHANGE BankTransType banktranstype varchar(30)  NOT NULL default '';
ALTER TABLE `banktrans` CHANGE Amount amount float  NOT NULL default '0';
ALTER TABLE `banktrans` CHANGE CurrCode currcode char(3)  NOT NULL default '';
ALTER TABLE `buckets` CHANGE WorkCentre workcentre char(5)  NOT NULL default '';
ALTER TABLE `buckets` CHANGE AvailDate availdate datetime  NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE `buckets` CHANGE Capacity capacity float(10,2)  NOT NULL default '0.00';
ALTER TABLE `cogsglpostings` CHANGE ID id int(11)  NOT NULL auto_increment;
ALTER TABLE `cogsglpostings` CHANGE Area area char(2)  NOT NULL default '';
ALTER TABLE `cogsglpostings` CHANGE StkCat stkcat varchar(6)  NOT NULL default '';
ALTER TABLE `cogsglpostings` CHANGE GLCode glcode int(11)  NOT NULL default '0';
ALTER TABLE `cogsglpostings` CHANGE SalesType salestype char(2)  NOT NULL default 'AN';
ALTER TABLE `chartdetails` CHANGE AccountCode accountcode int(11)  NOT NULL default '0';
ALTER TABLE `chartdetails` CHANGE Period period smallint(6)  NOT NULL default '0';
ALTER TABLE `chartdetails` CHANGE Budget budget float  NOT NULL default '0';
ALTER TABLE `chartdetails` CHANGE Actual actual float  NOT NULL default '0';
ALTER TABLE `chartdetails` CHANGE BFwd bfwd float  NOT NULL default '0';
ALTER TABLE `chartdetails` CHANGE BFwdBudget bfwdbudget float  NOT NULL default '0';
ALTER TABLE `chartmaster` CHANGE AccountCode accountcode int(11)  NOT NULL default '0';
ALTER TABLE `chartmaster` CHANGE AccountName accountname char(50)  NOT NULL default '';
ALTER TABLE `chartmaster` CHANGE Group_ group_ char(30)  NOT NULL default '';
ALTER TABLE `companies` CHANGE CoyCode coycode int(11)  NOT NULL default '1';
ALTER TABLE `companies` CHANGE CoyName coyname varchar(50)  NOT NULL default '';
ALTER TABLE `companies` CHANGE GSTNo gstno varchar(20)  NOT NULL default '';
ALTER TABLE `companies` CHANGE CompanyNumber companynumber varchar(20)  NOT NULL default '0';
ALTER TABLE `companies` CHANGE PostalAddress postaladdress varchar(50)  NOT NULL default '';
ALTER TABLE `companies` CHANGE RegOffice1 regoffice1 varchar(50)  NOT NULL default '';
ALTER TABLE `companies` CHANGE RegOffice2 regoffice2 varchar(50)  NOT NULL default '';
ALTER TABLE `companies` CHANGE RegOffice3 regoffice3 varchar(50)  NOT NULL default '';
ALTER TABLE `companies` CHANGE Telephone telephone varchar(25)  NOT NULL default '';
ALTER TABLE `companies` CHANGE Fax fax varchar(25)  NOT NULL default '';
ALTER TABLE `companies` CHANGE Email email varchar(55)  NOT NULL default '';
ALTER TABLE `companies` CHANGE CurrencyDefault currencydefault varchar(4)  NOT NULL default '';
ALTER TABLE `companies` CHANGE DebtorsAct debtorsact int(11)  NOT NULL default '70000';
ALTER TABLE `companies` CHANGE PytDiscountAct pytdiscountact int(11)  NOT NULL default '55000';
ALTER TABLE `companies` CHANGE CreditorsAct creditorsact int(11)  NOT NULL default '80000';
ALTER TABLE `companies` CHANGE PayrollAct payrollact int(11)  NOT NULL default '84000';
ALTER TABLE `companies` CHANGE GRNAct grnact int(11)  NOT NULL default '72000';
ALTER TABLE `companies` CHANGE ExchangeDiffAct exchangediffact int(11)  NOT NULL default '65000';
ALTER TABLE `companies` CHANGE PurchasesExchangeDiffAct purchasesexchangediffact int(11)  NOT NULL default '0';
ALTER TABLE `companies` CHANGE RetainedEarnings retainedearnings int(11)  NOT NULL default '90000';
ALTER TABLE `companies` CHANGE GLLink_Debtors gllink_debtors tinyint(1)  default '1';
ALTER TABLE `companies` CHANGE GLLink_Creditors gllink_creditors tinyint(1)  default '1';
ALTER TABLE `companies` CHANGE GLLink_Stock gllink_stock tinyint(1)  default '1';
ALTER TABLE `companies` CHANGE FreightAct freightact int(11)  NOT NULL default '0';
ALTER TABLE `contractbom` CHANGE ContractRef contractref char(20)  NOT NULL default '';
ALTER TABLE `contractbom` CHANGE Component component char(20)  NOT NULL default '';
ALTER TABLE `contractbom` CHANGE WorkCentreAdded workcentreadded char(5)  NOT NULL default '';
ALTER TABLE `contractbom` CHANGE LocCode loccode char(5)  NOT NULL default '';
ALTER TABLE `contractbom` CHANGE Quantity quantity double(16,4)  NOT NULL default '1.0000';
ALTER TABLE `contractreqts` CHANGE ContractReqID contractreqid int(11)  NOT NULL auto_increment;
ALTER TABLE `contractreqts` CHANGE Contract contract char(20)  NOT NULL default '';
ALTER TABLE `contractreqts` CHANGE Component component char(40)  NOT NULL default '';
ALTER TABLE `contractreqts` CHANGE Quantity quantity double(16,4)  NOT NULL default '1.0000';
ALTER TABLE `contractreqts` CHANGE PricePerUnit priceperunit decimal(20,4)  NOT NULL default '0.0000';
ALTER TABLE `contracts` CHANGE ContractRef contractref varchar(20)  NOT NULL default '';
ALTER TABLE `contracts` CHANGE ContractDescription contractdescription varchar(50)  NOT NULL default '';
ALTER TABLE `contracts` CHANGE DebtorNo debtorno varchar(10)  NOT NULL default '';
ALTER TABLE `contracts` CHANGE BranchCode branchcode varchar(10)  NOT NULL default '';
ALTER TABLE `contracts` CHANGE Status status varchar(10)  NOT NULL default 'Quotation';
ALTER TABLE `contracts` CHANGE CategoryID categoryid varchar(6)  NOT NULL default '';
ALTER TABLE `contracts` CHANGE TypeAbbrev typeabbrev char(2)  NOT NULL default '';
ALTER TABLE `contracts` CHANGE OrderNo orderno int(11)  NOT NULL default '0';
ALTER TABLE `contracts` CHANGE QuotedPriceFX quotedpricefx decimal(20,4)  NOT NULL default '0.0000';
ALTER TABLE `contracts` CHANGE Margin margin double(16,4)  NOT NULL default '1.0000';
ALTER TABLE `contracts` CHANGE WORef woref varchar(20)  NOT NULL default '';
ALTER TABLE `contracts` CHANGE RequiredDate requireddate datetime  NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE `contracts` CHANGE CancelDate canceldate datetime  NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE `contracts` CHANGE QuantityReqd quantityreqd double(16,4)  NOT NULL default '1.0000';
ALTER TABLE `contracts` CHANGE Specifications specifications longblob  NOT NULL;
ALTER TABLE `contracts` CHANGE DateQuoted datequoted datetime  NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE `contracts` CHANGE Units units varchar(15)  NOT NULL default 'Each';
ALTER TABLE `contracts` CHANGE Drawing drawing longblob  NOT NULL;
ALTER TABLE `contracts` CHANGE Rate rate double(16,4)  NOT NULL default '1.0000';
ALTER TABLE `currencies` CHANGE Currency currency char(20)  NOT NULL default '';
ALTER TABLE `currencies` CHANGE CurrAbrev currabrev char(3)  NOT NULL default '';
ALTER TABLE `currencies` CHANGE Country country char(50)  NOT NULL default '';
ALTER TABLE `currencies` CHANGE HundredsName hundredsname char(15)  NOT NULL default 'Cents';
ALTER TABLE `currencies` CHANGE Rate rate double(16,4)  NOT NULL default '1.0000';
ALTER TABLE `custallocns` CHANGE ID id int(11)  NOT NULL auto_increment;
ALTER TABLE `custallocns` CHANGE Amt amt decimal(20,4)  NOT NULL default '0.0000';
ALTER TABLE `custallocns` CHANGE DateAlloc datealloc date  NOT NULL default '0000-00-00';
ALTER TABLE `custallocns` CHANGE TransID_AllocFrom transid_allocfrom int(11)  NOT NULL default '0';
ALTER TABLE `custallocns` CHANGE TransID_AllocTo transid_allocto int(11)  NOT NULL default '0';
ALTER TABLE `custbranch` CHANGE BranchCode branchcode varchar(10)  NOT NULL default '';
ALTER TABLE `custbranch` CHANGE DebtorNo debtorno varchar(10)  NOT NULL default '';
ALTER TABLE `custbranch` CHANGE BrName brname varchar(40)  NOT NULL default '';
ALTER TABLE `custbranch` CHANGE BrAddress1 braddress1 varchar(40)  NOT NULL default '';
ALTER TABLE `custbranch` CHANGE BrAddress2 braddress2 varchar(40)  NOT NULL default '';
ALTER TABLE `custbranch` CHANGE BrAddress3 braddress3 varchar(40)  NOT NULL default '';
ALTER TABLE `custbranch` CHANGE BrAddress4 braddress4 varchar(50)  NOT NULL default '';
ALTER TABLE `custbranch` CHANGE EstDeliveryDays estdeliverydays smallint(6)  NOT NULL default '1';
ALTER TABLE `custbranch` CHANGE Area area char(2)  NOT NULL default '';
ALTER TABLE `custbranch` CHANGE Salesman salesman varchar(4)  NOT NULL default '';
ALTER TABLE `custbranch` CHANGE FwdDate fwddate smallint(6)  NOT NULL default '0';
ALTER TABLE `custbranch` CHANGE PhoneNo phoneno varchar(20)  NOT NULL default '';
ALTER TABLE `custbranch` CHANGE FaxNo faxno varchar(20)  NOT NULL default '';
ALTER TABLE `custbranch` CHANGE ContactName contactname varchar(30)  NOT NULL default '';
ALTER TABLE `custbranch` CHANGE Email email varchar(55)  NOT NULL default '';
ALTER TABLE `custbranch` CHANGE DefaultLocation defaultlocation varchar(5)  NOT NULL default '';
ALTER TABLE `custbranch` CHANGE TaxAuthority taxauthority tinyint(4)  NOT NULL default '1';
ALTER TABLE `custbranch` CHANGE DefaultShipVia defaultshipvia int(11)  NOT NULL default '1';
ALTER TABLE `custbranch` CHANGE DisableTrans disabletrans tinyint(4)  NOT NULL default '0';
ALTER TABLE `custbranch` CHANGE BrPostAddr1 brpostaddr1 varchar(40)  NOT NULL default '';
ALTER TABLE `custbranch` CHANGE BrPostAddr2 brpostaddr2 varchar(40)  NOT NULL default '';
ALTER TABLE `custbranch` CHANGE BrPostAddr3 brpostaddr3 varchar(30)  NOT NULL default '';
ALTER TABLE `custbranch` CHANGE BrPostAddr4 brpostaddr4 varchar(20)  NOT NULL default '';
ALTER TABLE `custbranch` CHANGE CustBranchCode custbranchcode varchar(30)  NOT NULL default '';
ALTER TABLE `debtortrans` CHANGE ID id int(11)  NOT NULL auto_increment;
ALTER TABLE `debtortrans` CHANGE TransNo transno int(11)  NOT NULL default '0';
ALTER TABLE `debtortrans` CHANGE Type type smallint(6)  NOT NULL default '0';
ALTER TABLE `debtortrans` CHANGE DebtorNo debtorno varchar(10)  NOT NULL default '';
ALTER TABLE `debtortrans` CHANGE BranchCode branchcode varchar(10)  NOT NULL default '';
ALTER TABLE `debtortrans` CHANGE TranDate trandate datetime  NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE `debtortrans` CHANGE Prd prd smallint(6)  NOT NULL default '0';
ALTER TABLE `debtortrans` CHANGE Settled settled tinyint(4)  NOT NULL default '0';
ALTER TABLE `debtortrans` CHANGE Reference reference varchar(20)  NOT NULL default '';
ALTER TABLE `debtortrans` CHANGE Tpe tpe char(2)  NOT NULL default '';
ALTER TABLE `debtortrans` CHANGE Order_ order_ int(11)  NOT NULL default '0';
ALTER TABLE `debtortrans` CHANGE Rate rate double(16,6)  NOT NULL default '0.000000';
ALTER TABLE `debtortrans` CHANGE OvAmount ovamount float  NOT NULL default '0';
ALTER TABLE `debtortrans` CHANGE OvGST ovgst float  NOT NULL default '0';
ALTER TABLE `debtortrans` CHANGE OvFreight ovfreight float  NOT NULL default '0';
ALTER TABLE `debtortrans` CHANGE OvDiscount ovdiscount float  NOT NULL default '0';
ALTER TABLE `debtortrans` CHANGE DiffOnExch diffonexch float  NOT NULL default '0';
ALTER TABLE `debtortrans` CHANGE Alloc alloc float  NOT NULL default '0';
ALTER TABLE `debtortrans` CHANGE InvText invtext text;
ALTER TABLE `debtortrans` CHANGE ShipVia shipvia varchar(10)  NOT NULL default '';
ALTER TABLE `debtortrans` CHANGE EDISent edisent tinyint(4)  NOT NULL default '0';
ALTER TABLE `debtortrans` CHANGE Consignment consignment varchar(15)  NOT NULL default '';
ALTER TABLE `debtorsmaster` CHANGE DebtorNo debtorno varchar(10)  NOT NULL default '';
ALTER TABLE `debtorsmaster` CHANGE Name name varchar(40)  NOT NULL default '';
ALTER TABLE `debtorsmaster` CHANGE Address1 address1 varchar(40)  NOT NULL default '';
ALTER TABLE `debtorsmaster` CHANGE Address2 address2 varchar(40)  NOT NULL default '';
ALTER TABLE `debtorsmaster` CHANGE Address3 address3 varchar(40)  NOT NULL default '';
ALTER TABLE `debtorsmaster` CHANGE Address4 address4 varchar(50)  NOT NULL default '';
ALTER TABLE `debtorsmaster` CHANGE CurrCode currcode char(3)  NOT NULL default '';
ALTER TABLE `debtorsmaster` CHANGE SalesType salestype char(2)  NOT NULL default '';
ALTER TABLE `debtorsmaster` CHANGE ClientSince clientsince datetime  NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE `debtorsmaster` CHANGE HoldReason holdreason smallint(6)  NOT NULL default '0';
ALTER TABLE `debtorsmaster` CHANGE PaymentTerms paymentterms char(2)  NOT NULL default 'f';
ALTER TABLE `debtorsmaster` CHANGE Discount discount double(16,4)  NOT NULL default '0.0000';
ALTER TABLE `debtorsmaster` CHANGE PymtDiscount pymtdiscount double(16,4)  NOT NULL default '0.0000';
ALTER TABLE `debtorsmaster` CHANGE LastPaid lastpaid double(16,4)  NOT NULL default '0.0000';
ALTER TABLE `debtorsmaster` CHANGE LastPaidDate lastpaiddate datetime  default NULL;
ALTER TABLE `debtorsmaster` CHANGE CreditLimit creditlimit float  NOT NULL default '1000';
ALTER TABLE `debtorsmaster` CHANGE InvAddrBranch invaddrbranch tinyint(4)  NOT NULL default '0';
ALTER TABLE `debtorsmaster` CHANGE DiscountCode discountcode char(2)  NOT NULL default '';
ALTER TABLE `debtorsmaster` CHANGE EDIInvoices ediinvoices tinyint(4)  NOT NULL default '0';
ALTER TABLE `debtorsmaster` CHANGE EDIOrders ediorders tinyint(4)  NOT NULL default '0';
ALTER TABLE `debtorsmaster` CHANGE EDIReference edireference varchar(20)  NOT NULL default '';
ALTER TABLE `debtorsmaster` CHANGE EDITransport editransport varchar(5)  NOT NULL default 'email';
ALTER TABLE `debtorsmaster` CHANGE EDIAddress ediaddress varchar(50)  NOT NULL default '';
ALTER TABLE `debtorsmaster` CHANGE EDIServerUser ediserveruser varchar(20)  NOT NULL default '';
ALTER TABLE `debtorsmaster` CHANGE EDIServerPwd ediserverpwd varchar(20)  NOT NULL default '';
ALTER TABLE `discountmatrix` CHANGE SalesType salestype char(2)  NOT NULL default '';
ALTER TABLE `discountmatrix` CHANGE DiscountCategory discountcategory char(2)  NOT NULL default '';
ALTER TABLE `discountmatrix` CHANGE QuantityBreak quantitybreak int(11)  NOT NULL default '1';
ALTER TABLE `discountmatrix` CHANGE DiscountRate discountrate double(16,4)  NOT NULL default '0.0000';
ALTER TABLE `ediitemmapping` CHANGE SuppOrCust supporcust varchar(4)  NOT NULL default '';
ALTER TABLE `ediitemmapping` CHANGE PartnerCode partnercode varchar(10)  NOT NULL default '';
ALTER TABLE `ediitemmapping` CHANGE StockID stockid varchar(20)  NOT NULL default '';
ALTER TABLE `ediitemmapping` CHANGE PartnerStockID partnerstockid varchar(50)  NOT NULL default '';
ALTER TABLE `edimessageformat` CHANGE ID id int(11)  NOT NULL auto_increment;
ALTER TABLE `edimessageformat` CHANGE PartnerCode partnercode varchar(10)  NOT NULL default '';
ALTER TABLE `edimessageformat` CHANGE MessageType messagetype varchar(6)  NOT NULL default '';
ALTER TABLE `edimessageformat` CHANGE Section section varchar(7)  NOT NULL default '';
ALTER TABLE `edimessageformat` CHANGE SequenceNo sequenceno int(11)  NOT NULL default '0';
ALTER TABLE `edimessageformat` CHANGE LineText linetext varchar(70)  NOT NULL default '';
ALTER TABLE `edi_orders_seg_groups` CHANGE SegGroupNo seggroupno tinyint(4)  NOT NULL default '0';
ALTER TABLE `edi_orders_seg_groups` CHANGE MaxOccur maxoccur int(4)  NOT NULL default '0';
ALTER TABLE `edi_orders_seg_groups` CHANGE ParentSegGroup parentseggroup tinyint(4)  NOT NULL default '0';
ALTER TABLE `edi_orders_segs` CHANGE ID id int(11)  NOT NULL auto_increment;
ALTER TABLE `edi_orders_segs` CHANGE SegTag segtag char(3)  NOT NULL default '';
ALTER TABLE `edi_orders_segs` CHANGE SegGroup seggroup tinyint(4)  NOT NULL default '0';
ALTER TABLE `edi_orders_segs` CHANGE MaxOccur maxoccur tinyint(4)  NOT NULL default '0';
ALTER TABLE `freightcosts` CHANGE ShipCostFromID shipcostfromid int(11)  NOT NULL auto_increment;
ALTER TABLE `freightcosts` CHANGE LocationFrom locationfrom varchar(5)  NOT NULL default '';
ALTER TABLE `freightcosts` CHANGE Destination destination varchar(40)  NOT NULL default '';
ALTER TABLE `freightcosts` CHANGE ShipperID shipperid int(11)  NOT NULL default '0';
ALTER TABLE `freightcosts` CHANGE CubRate cubrate double(16,2)  NOT NULL default '0.00';
ALTER TABLE `freightcosts` CHANGE KGRate kgrate double(16,2)  NOT NULL default '0.00';
ALTER TABLE `freightcosts` CHANGE MAXKGs maxkgs double(16,2)  NOT NULL default '999999.00';
ALTER TABLE `freightcosts` CHANGE MAXCub maxcub double(16,2)  NOT NULL default '999999.00';
ALTER TABLE `freightcosts` CHANGE FixedPrice fixedprice double(16,2)  NOT NULL default '0.00';
ALTER TABLE `freightcosts` CHANGE MinimumChg minimumchg double(16,2)  NOT NULL default '0.00';
ALTER TABLE `gltrans` CHANGE CounterIndex counterindex int(11)  NOT NULL auto_increment;
ALTER TABLE `gltrans` CHANGE Type type smallint(6)  NOT NULL default '0';
ALTER TABLE `gltrans` CHANGE TypeNo typeno bigint(16)  NOT NULL default '1';
ALTER TABLE `gltrans` CHANGE ChequeNo chequeno int(11)  NOT NULL default '0';
ALTER TABLE `gltrans` CHANGE TranDate trandate date  NOT NULL default '0000-00-00';
ALTER TABLE `gltrans` CHANGE PeriodNo periodno smallint(6)  NOT NULL default '0';
ALTER TABLE `gltrans` CHANGE Account account int(11)  NOT NULL default '0';
ALTER TABLE `gltrans` CHANGE Narrative narrative varchar(200)  NOT NULL default '';
ALTER TABLE `gltrans` CHANGE Amount amount float  NOT NULL default '0';
ALTER TABLE `gltrans` CHANGE Posted posted tinyint(4)  NOT NULL default '0';
ALTER TABLE `gltrans` CHANGE JobRef jobref varchar(20)  NOT NULL default '';
ALTER TABLE `grns` CHANGE GRNBatch grnbatch smallint(6)  NOT NULL default '0';
ALTER TABLE `grns` CHANGE GRNNo grnno int(11)  NOT NULL auto_increment;
ALTER TABLE `grns` CHANGE PODetailItem podetailitem int(11)  NOT NULL default '0';
ALTER TABLE `grns` CHANGE ItemCode itemcode varchar(20)  NOT NULL default '';
ALTER TABLE `grns` CHANGE DeliveryDate deliverydate date  NOT NULL default '0000-00-00';
ALTER TABLE `grns` CHANGE ItemDescription itemdescription varchar(100)  NOT NULL default '';
ALTER TABLE `grns` CHANGE QtyRecd qtyrecd double(16,4)  NOT NULL default '0.0000';
ALTER TABLE `grns` CHANGE QuantityInv quantityinv double(16,4)  NOT NULL default '0.0000';
ALTER TABLE `grns` CHANGE SupplierID supplierid varchar(10)  NOT NULL default '';
ALTER TABLE `holdreasons` CHANGE ReasonCode reasoncode smallint(6)  NOT NULL default '1';
ALTER TABLE `holdreasons` CHANGE ReasonDescription reasondescription char(30)  NOT NULL default '';
ALTER TABLE `holdreasons` CHANGE DissallowInvoices dissallowinvoices tinyint(4)  NOT NULL default '-1';
ALTER TABLE `lastcostrollup` CHANGE StockID stockid char(20)  NOT NULL default '';
ALTER TABLE `lastcostrollup` CHANGE TotalOnHand totalonhand double(16,4)  NOT NULL default '0.0000';
ALTER TABLE `lastcostrollup` CHANGE MatCost matcost decimal(20,4)  NOT NULL default '0.0000';
ALTER TABLE `lastcostrollup` CHANGE LabCost labcost decimal(20,4)  NOT NULL default '0.0000';
ALTER TABLE `lastcostrollup` CHANGE OheadCost oheadcost decimal(20,4)  NOT NULL default '0.0000';
ALTER TABLE `lastcostrollup` CHANGE CategoryID categoryid char(6)  NOT NULL default '';
ALTER TABLE `lastcostrollup` CHANGE StockAct stockact int(11)  NOT NULL default '0';
ALTER TABLE `lastcostrollup` CHANGE AdjGLAct adjglact int(11)  NOT NULL default '0';
ALTER TABLE `lastcostrollup` CHANGE NewMatCost newmatcost decimal(20,4)  NOT NULL default '0.0000';
ALTER TABLE `lastcostrollup` CHANGE NewLabCost newlabcost decimal(20,4)  NOT NULL default '0.0000';
ALTER TABLE `lastcostrollup` CHANGE NewOheadCost newoheadcost decimal(20,4)  NOT NULL default '0.0000';
ALTER TABLE `locstock` CHANGE LocCode loccode varchar(5)  NOT NULL default '';
ALTER TABLE `locstock` CHANGE StockID stockid varchar(20)  NOT NULL default '';
ALTER TABLE `locstock` CHANGE Quantity quantity double(16,1)  NOT NULL default '0.0';
ALTER TABLE `locstock` CHANGE ReorderLevel reorderlevel bigint(20)  NOT NULL default '0';
ALTER TABLE `loctransfers` CHANGE Reference reference int(11)  NOT NULL default '0';
ALTER TABLE `loctransfers` CHANGE StockID stockid varchar(20)  NOT NULL default '';
ALTER TABLE `loctransfers` CHANGE ShipQty shipqty int(11)  NOT NULL default '0';
ALTER TABLE `loctransfers` CHANGE RecQty recqty int(11)  NOT NULL default '0';
ALTER TABLE `loctransfers` CHANGE ShipDate shipdate date  NOT NULL default '0000-00-00';
ALTER TABLE `loctransfers` CHANGE RecDate recdate date  NOT NULL default '0000-00-00';
ALTER TABLE `loctransfers` CHANGE ShipLoc shiploc varchar(7)  NOT NULL default '';
ALTER TABLE `loctransfers` CHANGE RecLoc recloc varchar(7)  NOT NULL default '';
ALTER TABLE `locations` CHANGE LocCode loccode varchar(5)  NOT NULL default '';
ALTER TABLE `locations` CHANGE LocationName locationname varchar(50)  NOT NULL default '';
ALTER TABLE `locations` CHANGE DelAdd1 deladd1 varchar(40)  NOT NULL default '';
ALTER TABLE `locations` CHANGE DelAdd2 deladd2 varchar(40)  NOT NULL default '';
ALTER TABLE `locations` CHANGE DelAdd3 deladd3 varchar(40)  NOT NULL default '';
ALTER TABLE `locations` CHANGE Tel tel varchar(30)  NOT NULL default '';
ALTER TABLE `locations` CHANGE Fax fax varchar(30)  NOT NULL default '';
ALTER TABLE `locations` CHANGE Email email varchar(55)  NOT NULL default '';
ALTER TABLE `locations` CHANGE Contact contact varchar(30)  NOT NULL default '';
ALTER TABLE `locations` CHANGE TaxAuthority taxauthority tinyint(4)  NOT NULL default '1';
ALTER TABLE `orderdeliverydifferenceslog` CHANGE OrderNo orderno int(11)  NOT NULL default '0';
ALTER TABLE `orderdeliverydifferenceslog` CHANGE InvoiceNo invoiceno int(11)  NOT NULL default '0';
ALTER TABLE `orderdeliverydifferenceslog` CHANGE StockID stockid varchar(20)  NOT NULL default '';
ALTER TABLE `orderdeliverydifferenceslog` CHANGE QuantityDiff quantitydiff double(16,4)  NOT NULL default '0.0000';
ALTER TABLE `orderdeliverydifferenceslog` CHANGE DebtorNo debtorno varchar(10)  NOT NULL default '';
ALTER TABLE `orderdeliverydifferenceslog` CHANGE Branch branch varchar(10)  NOT NULL default '';
ALTER TABLE `orderdeliverydifferenceslog` CHANGE Can_or_BO can_or_bo char(3)  NOT NULL default 'CAN';
ALTER TABLE `paymentterms` CHANGE TermsIndicator termsindicator char(2)  NOT NULL default '';
ALTER TABLE `paymentterms` CHANGE Terms terms char(40)  NOT NULL default '';
ALTER TABLE `paymentterms` CHANGE DaysBeforeDue daysbeforedue smallint(6)  NOT NULL default '0';
ALTER TABLE `paymentterms` CHANGE DayInFollowingMonth dayinfollowingmonth smallint(6)  NOT NULL default '0';
ALTER TABLE `periods` CHANGE PeriodNo periodno smallint(6)  NOT NULL default '0';
ALTER TABLE `periods` CHANGE LastDate_in_Period lastdate_in_period date  NOT NULL default '0000-00-00';
ALTER TABLE `prices` CHANGE StockID stockid varchar(20)  NOT NULL default '';
ALTER TABLE `prices` CHANGE TypeAbbrev typeabbrev char(2)  NOT NULL default '';
ALTER TABLE `prices` CHANGE CurrAbrev currabrev char(3)  NOT NULL default '';
ALTER TABLE `prices` CHANGE DebtorNo debtorno varchar(10)  NOT NULL default '';
ALTER TABLE `prices` CHANGE Price price decimal(20,4)  NOT NULL default '0.0000';
ALTER TABLE `prices` CHANGE BranchCode branchcode varchar(10)  NOT NULL default '';
ALTER TABLE `purchdata` CHANGE SupplierNo supplierno char(10)  NOT NULL default '';
ALTER TABLE `purchdata` CHANGE StockID stockid char(20)  NOT NULL default '';
ALTER TABLE `purchdata` CHANGE Price price decimal(20,4)  NOT NULL default '0.0000';
ALTER TABLE `purchdata` CHANGE SuppliersUOM suppliersuom char(50)  NOT NULL default '';
ALTER TABLE `purchdata` CHANGE ConversionFactor conversionfactor double(16,4)  NOT NULL default '1.0000';
ALTER TABLE `purchdata` CHANGE SupplierDescription supplierdescription char(50)  NOT NULL default '';
ALTER TABLE `purchdata` CHANGE LeadTime leadtime smallint(6)  NOT NULL default '1';
ALTER TABLE `purchdata` CHANGE Preferred preferred tinyint(4)  NOT NULL default '0';
ALTER TABLE `purchorderdetails` CHANGE PODetailItem podetailitem int(11)  NOT NULL auto_increment;
ALTER TABLE `purchorderdetails` CHANGE OrderNo orderno int(11)  NOT NULL default '0';
ALTER TABLE `purchorderdetails` CHANGE ItemCode itemcode varchar(20)  NOT NULL default '';
ALTER TABLE `purchorderdetails` CHANGE DeliveryDate deliverydate date  NOT NULL default '0000-00-00';
ALTER TABLE `purchorderdetails` CHANGE ItemDescription itemdescription varchar(100)  NOT NULL default '';
ALTER TABLE `purchorderdetails` CHANGE GLCode glcode int(11)  NOT NULL default '0';
ALTER TABLE `purchorderdetails` CHANGE QtyInvoiced qtyinvoiced double(16,4)  NOT NULL default '0.0000';
ALTER TABLE `purchorderdetails` CHANGE UnitPrice unitprice double(16,4)  NOT NULL default '0.0000';
ALTER TABLE `purchorderdetails` CHANGE ActPrice actprice double(16,4)  NOT NULL default '0.0000';
ALTER TABLE `purchorderdetails` CHANGE StdCostUnit stdcostunit double(16,4)  NOT NULL default '0.0000';
ALTER TABLE `purchorderdetails` CHANGE QuantityOrd quantityord double(16,4)  NOT NULL default '0.0000';
ALTER TABLE `purchorderdetails` CHANGE QuantityRecd quantityrecd double(16,4)  NOT NULL default '0.0000';
ALTER TABLE `purchorderdetails` CHANGE ShiptRef shiptref int(1)  NOT NULL default '0';
ALTER TABLE `purchorderdetails` CHANGE JobRef jobref varchar(20)  NOT NULL default '';
ALTER TABLE `purchorderdetails` CHANGE Completed completed tinyint(4)  NOT NULL default '0';
ALTER TABLE `purchorders` CHANGE OrderNo orderno int(11)  NOT NULL auto_increment;
ALTER TABLE `purchorders` CHANGE SupplierNo supplierno varchar(10)  NOT NULL default '';
ALTER TABLE `purchorders` CHANGE Comments comments longblob;
ALTER TABLE `purchorders` CHANGE OrdDate orddate datetime  NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE `purchorders` CHANGE Rate rate double(16,4)  NOT NULL default '1.0000';
ALTER TABLE `purchorders` CHANGE DatePrinted dateprinted datetime  default NULL;
ALTER TABLE `purchorders` CHANGE AllowPrint allowprint tinyint(4)  NOT NULL default '1';
ALTER TABLE `purchorders` CHANGE Initiator initiator varchar(10)  default NULL;
ALTER TABLE `purchorders` CHANGE RequisitionNo requisitionno varchar(15)  default NULL;
ALTER TABLE `purchorders` CHANGE IntoStockLocation intostocklocation varchar(5)  NOT NULL default '';
ALTER TABLE `purchorders` CHANGE DelAdd1 deladd1 varchar(40)  NOT NULL default '';
ALTER TABLE `purchorders` CHANGE DelAdd2 deladd2 varchar(40)  NOT NULL default '';
ALTER TABLE `purchorders` CHANGE DelAdd3 deladd3 varchar(40)  NOT NULL default '';
ALTER TABLE `purchorders` CHANGE DelAdd4 deladd4 varchar(40)  NOT NULL default '';
ALTER TABLE `reportcolumns` CHANGE ReportID reportid smallint(6)  NOT NULL default '0';
ALTER TABLE `reportcolumns` CHANGE ColNo colno smallint(6)  NOT NULL default '0';
ALTER TABLE `reportcolumns` CHANGE Heading1 heading1 varchar(15)  NOT NULL default '';
ALTER TABLE `reportcolumns` CHANGE Heading2 heading2 varchar(15)  default NULL;
ALTER TABLE `reportcolumns` CHANGE Calculation calculation tinyint(1)  NOT NULL default '0';
ALTER TABLE `reportcolumns` CHANGE PeriodFrom periodfrom smallint(6)  default NULL;
ALTER TABLE `reportcolumns` CHANGE PeriodTo periodto smallint(6)  default NULL;
ALTER TABLE `reportcolumns` CHANGE DataType datatype varchar(15)  default NULL;
ALTER TABLE `reportcolumns` CHANGE ColNumerator colnumerator tinyint(4)  default NULL;
ALTER TABLE `reportcolumns` CHANGE ColDenominator coldenominator tinyint(4)  default NULL;
ALTER TABLE `reportcolumns` CHANGE CalcOperator calcoperator char(1)  default NULL;
ALTER TABLE `reportcolumns` CHANGE BudgetOrActual budgetoractual tinyint(1)  NOT NULL default '0';
ALTER TABLE `reportcolumns` CHANGE ValFormat valformat char(1)  NOT NULL default 'N';
ALTER TABLE `reportcolumns` CHANGE Constant constant float  NOT NULL default '0';
ALTER TABLE `reportheaders` CHANGE ReportID reportid smallint(6)  NOT NULL auto_increment;
ALTER TABLE `reportheaders` CHANGE ReportHeading reportheading varchar(80)  NOT NULL default '';
ALTER TABLE `reportheaders` CHANGE GroupByData1 groupbydata1 varchar(15)  NOT NULL default '';
ALTER TABLE `reportheaders` CHANGE NewPageAfter1 newpageafter1 tinyint(1)  NOT NULL default '0';
ALTER TABLE `reportheaders` CHANGE Lower1 lower1 varchar(10)  NOT NULL default '';
ALTER TABLE `reportheaders` CHANGE Upper1 upper1 varchar(10)  NOT NULL default '';
ALTER TABLE `reportheaders` CHANGE GroupByData2 groupbydata2 varchar(15)  default NULL;
ALTER TABLE `reportheaders` CHANGE NewPageAfter2 newpageafter2 tinyint(1)  NOT NULL default '0';
ALTER TABLE `reportheaders` CHANGE Lower2 lower2 varchar(10)  default NULL;
ALTER TABLE `reportheaders` CHANGE Upper2 upper2 varchar(10)  default NULL;
ALTER TABLE `reportheaders` CHANGE GroupByData3 groupbydata3 varchar(15)  default NULL;
ALTER TABLE `reportheaders` CHANGE NewPageAfter3 newpageafter3 tinyint(1)  NOT NULL default '0';
ALTER TABLE `reportheaders` CHANGE Lower3 lower3 varchar(10)  default NULL;
ALTER TABLE `reportheaders` CHANGE Upper3 upper3 varchar(10)  default NULL;
ALTER TABLE `reportheaders` CHANGE GroupByData4 groupbydata4 varchar(15)  NOT NULL default '';
ALTER TABLE `reportheaders` CHANGE NewPageAfter4 newpageafter4 tinyint(1)  NOT NULL default '0';
ALTER TABLE `reportheaders` CHANGE Upper4 upper4 varchar(10)  NOT NULL default '';
ALTER TABLE `reportheaders` CHANGE Lower4 lower4 varchar(10)  NOT NULL default '';
ALTER TABLE `salesanalysis` CHANGE TypeAbbrev typeabbrev char(2)  NOT NULL default '';
ALTER TABLE `salesanalysis` CHANGE PeriodNo periodno smallint(6)  NOT NULL default '0';
ALTER TABLE `salesanalysis` CHANGE Amt amt double(16,4)  NOT NULL default '0.0000';
ALTER TABLE `salesanalysis` CHANGE Cost cost double(16,4)  NOT NULL default '0.0000';
ALTER TABLE `salesanalysis` CHANGE Cust cust varchar(10)  NOT NULL default '';
ALTER TABLE `salesanalysis` CHANGE CustBranch custbranch varchar(10)  NOT NULL default '';
ALTER TABLE `salesanalysis` CHANGE Qty qty double(16,4)  NOT NULL default '0.0000';
ALTER TABLE `salesanalysis` CHANGE Disc disc double(16,4)  NOT NULL default '0.0000';
ALTER TABLE `salesanalysis` CHANGE StockID stockid varchar(20)  NOT NULL default '';
ALTER TABLE `salesanalysis` CHANGE Area area char(2)  NOT NULL default '';
ALTER TABLE `salesanalysis` CHANGE BudgetOrActual budgetoractual tinyint(1)  NOT NULL default '0';
ALTER TABLE `salesanalysis` CHANGE Salesperson salesperson char(3)  NOT NULL default '';
ALTER TABLE `salesanalysis` CHANGE StkCategory stkcategory varchar(6)  NOT NULL default '';
ALTER TABLE `salesanalysis` CHANGE ID id int(11)  NOT NULL auto_increment;
ALTER TABLE `salesglpostings` CHANGE ID id int(11)  NOT NULL auto_increment;
ALTER TABLE `salesglpostings` CHANGE Area area char(2)  NOT NULL default '';
ALTER TABLE `salesglpostings` CHANGE StkCat stkcat varchar(6)  NOT NULL default '';
ALTER TABLE `salesglpostings` CHANGE DiscountGLCode discountglcode int(11)  NOT NULL default '0';
ALTER TABLE `salesglpostings` CHANGE SalesGLCode salesglcode int(11)  NOT NULL default '0';
ALTER TABLE `salesglpostings` CHANGE SalesType salestype char(2)  NOT NULL default 'AN';
ALTER TABLE `salesorderdetails` CHANGE OrderNo orderno int(11)  NOT NULL default '0';
ALTER TABLE `salesorderdetails` CHANGE StkCode stkcode varchar(20)  NOT NULL default '';
ALTER TABLE `salesorderdetails` CHANGE QtyInvoiced qtyinvoiced double(16,4)  NOT NULL default '0.0000';
ALTER TABLE `salesorderdetails` CHANGE UnitPrice unitprice double(16,4)  NOT NULL default '0.0000';
ALTER TABLE `salesorderdetails` CHANGE Quantity quantity double(16,4)  NOT NULL default '0.0000';
ALTER TABLE `salesorderdetails` CHANGE Estimate estimate tinyint(4)  NOT NULL default '0';
ALTER TABLE `salesorderdetails` CHANGE DiscountPercent discountpercent double(16,4)  NOT NULL default '0.0000';
ALTER TABLE `salesorderdetails` CHANGE ActualDispatchDate actualdispatchdate datetime  NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE `salesorderdetails` CHANGE Completed completed tinyint(1)  NOT NULL default '0';
ALTER TABLE `salesorderdetails` CHANGE Narrative narrative text  NOT NULL;
ALTER TABLE `salesorders` CHANGE OrderNo orderno int(11)  NOT NULL auto_increment;
ALTER TABLE `salesorders` CHANGE DebtorNo debtorno varchar(10)  NOT NULL default '';
ALTER TABLE `salesorders` CHANGE BranchCode branchcode varchar(10)  NOT NULL default '';
ALTER TABLE `salesorders` CHANGE CustomerRef customerref varchar(50)  NOT NULL default '';
ALTER TABLE `salesorders` CHANGE BuyerName buyername varchar(50)  default NULL;
ALTER TABLE `salesorders` CHANGE Comments comments longblob;
ALTER TABLE `salesorders` CHANGE OrdDate orddate date  NOT NULL default '0000-00-00';
ALTER TABLE `salesorders` CHANGE OrderType ordertype char(2)  NOT NULL default '';
ALTER TABLE `salesorders` CHANGE ShipVia shipvia int(11)  NOT NULL default '0';
ALTER TABLE `salesorders` CHANGE DelAdd1 deladd1 varchar(40)  NOT NULL default '';
ALTER TABLE `salesorders` CHANGE DelAdd2 deladd2 varchar(20)  NOT NULL default '';
ALTER TABLE `salesorders` CHANGE DelAdd3 deladd3 varchar(15)  NOT NULL default '';
ALTER TABLE `salesorders` CHANGE DelAdd4 deladd4 varchar(15)  default NULL;
ALTER TABLE `salesorders` CHANGE ContactPhone contactphone varchar(25)  default NULL;
ALTER TABLE `salesorders` CHANGE ContactEmail contactemail varchar(25)  default NULL;
ALTER TABLE `salesorders` CHANGE DeliverTo deliverto varchar(40)  NOT NULL default '';
ALTER TABLE `salesorders` CHANGE FreightCost freightcost float(10,2)  NOT NULL default '0.00';
ALTER TABLE `salesorders` CHANGE FromStkLoc fromstkloc varchar(5)  NOT NULL default '';
ALTER TABLE `salesorders` CHANGE DeliveryDate deliverydate date  NOT NULL default '0000-00-00';
ALTER TABLE `salesorders` CHANGE PrintedPackingSlip printedpackingslip tinyint(4)  NOT NULL default '0';
ALTER TABLE `salesorders` CHANGE DatePackingSlipPrinted datepackingslipprinted date  NOT NULL default '0000-00-00';
ALTER TABLE `salestypes` CHANGE TypeAbbrev typeabbrev char(2)  NOT NULL default '';
ALTER TABLE `salestypes` CHANGE Sales_Type sales_type char(20)  NOT NULL default '';
ALTER TABLE `salesman` CHANGE SalesmanCode salesmancode char(3)  NOT NULL default '';
ALTER TABLE `salesman` CHANGE SalesmanName salesmanname char(30)  NOT NULL default '';
ALTER TABLE `salesman` CHANGE SManTel smantel char(20)  NOT NULL default '';
ALTER TABLE `salesman` CHANGE SManFax smanfax char(20)  NOT NULL default '';
ALTER TABLE `salesman` CHANGE CommissionRate1 commissionrate1 double(16,4)  NOT NULL default '0.0000';
ALTER TABLE `salesman` CHANGE Breakpoint breakpoint decimal(20,4)  NOT NULL default '0.0000';
ALTER TABLE `salesman` CHANGE CommissionRate2 commissionrate2 double(16,4)  NOT NULL default '0.0000';
ALTER TABLE `scripts` CHANGE PageID pageid smallint(4)  NOT NULL default '0';
ALTER TABLE `scripts` CHANGE FileName filename varchar(50)  NOT NULL default '';
ALTER TABLE `scripts` CHANGE PageDescription pagedescription text  NOT NULL;
ALTER TABLE `shipmentcharges` CHANGE ShiptChgID shiptchgid int(11)  NOT NULL auto_increment;
ALTER TABLE `shipmentcharges` CHANGE ShiptRef shiptref int(11)  NOT NULL default '0';
ALTER TABLE `shipmentcharges` CHANGE TransType transtype smallint(6)  NOT NULL default '0';
ALTER TABLE `shipmentcharges` CHANGE TransNo transno int(11)  NOT NULL default '0';
ALTER TABLE `shipmentcharges` CHANGE StockID stockid varchar(20)  NOT NULL default '';
ALTER TABLE `shipmentcharges` CHANGE Value value float  NOT NULL default '0';
ALTER TABLE `shipments` CHANGE ShiptRef shiptref int(11)  NOT NULL default '0';
ALTER TABLE `shipments` CHANGE VoyageRef voyageref varchar(20)  NOT NULL default '0';
ALTER TABLE `shipments` CHANGE Vessel vessel varchar(50)  NOT NULL default '';
ALTER TABLE `shipments` CHANGE ETA eta datetime  NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE `shipments` CHANGE AccumValue accumvalue double(16,4)  NOT NULL default '0.0000';
ALTER TABLE `shipments` CHANGE SupplierID supplierid varchar(10)  NOT NULL default '';
ALTER TABLE `shipments` CHANGE Closed closed tinyint(4)  NOT NULL default '0';
ALTER TABLE `shippers` CHANGE Shipper_ID shipper_id int(11)  NOT NULL auto_increment;
ALTER TABLE `shippers` CHANGE ShipperName shippername char(40)  NOT NULL default '';
ALTER TABLE `shippers` CHANGE MinCharge mincharge double(16,4)  NOT NULL default '0.0000';
ALTER TABLE `stockcategory` CHANGE CategoryID categoryid char(6)  NOT NULL default '';
ALTER TABLE `stockcategory` CHANGE CategoryDescription categorydescription char(20)  NOT NULL default '';
ALTER TABLE `stockcategory` CHANGE StockType stocktype char(1)  NOT NULL default 'F';
ALTER TABLE `stockcategory` CHANGE StockAct stockact int(11)  NOT NULL default '0';
ALTER TABLE `stockcategory` CHANGE AdjGLAct adjglact int(11)  NOT NULL default '0';
ALTER TABLE `stockcategory` CHANGE PurchPriceVarAct purchpricevaract int(11)  NOT NULL default '80000';
ALTER TABLE `stockcategory` CHANGE MaterialUseageVarAc materialuseagevarac int(11)  NOT NULL default '80000';
ALTER TABLE `stockcategory` CHANGE WIPAct wipact int(11)  NOT NULL default '0';
ALTER TABLE `stockcheckfreeze` CHANGE StockID stockid varchar(20)  NOT NULL default '';
ALTER TABLE `stockcheckfreeze` CHANGE LocCode loccode varchar(5)  NOT NULL default '';
ALTER TABLE `stockcheckfreeze` CHANGE QOH qoh float  NOT NULL default '0';
ALTER TABLE `stockcounts` CHANGE ID id int(11)  NOT NULL auto_increment;
ALTER TABLE `stockcounts` CHANGE StockID stockid varchar(20)  NOT NULL default '';
ALTER TABLE `stockcounts` CHANGE LocCode loccode varchar(5)  NOT NULL default '';
ALTER TABLE `stockcounts` CHANGE QtyCounted qtycounted float  NOT NULL default '0';
ALTER TABLE `stockcounts` CHANGE Reference reference varchar(20)  NOT NULL default '';
ALTER TABLE `stockmaster` CHANGE StockID stockid varchar(20)  NOT NULL default '';
ALTER TABLE `stockmaster` CHANGE CategoryID categoryid varchar(6)  NOT NULL default '';
ALTER TABLE `stockmaster` CHANGE Description description varchar(50)  NOT NULL default '';
ALTER TABLE `stockmaster` CHANGE LongDescription longdescription text  NOT NULL;
ALTER TABLE `stockmaster` CHANGE Units units varchar(20)  NOT NULL default 'each';
ALTER TABLE `stockmaster` CHANGE MBflag mbflag char(1)  NOT NULL default 'B';
ALTER TABLE `stockmaster` CHANGE LastCurCostDate lastcurcostdate date  NOT NULL default '1800-01-01';
ALTER TABLE `stockmaster` CHANGE ActualCost actualcost decimal(20,4)  NOT NULL default '0.0000';
ALTER TABLE `stockmaster` CHANGE LastCost lastcost decimal(20,4)  NOT NULL default '0.0000';
ALTER TABLE `stockmaster` CHANGE Materialcost materialcost decimal(20,4)  NOT NULL default '0.0000';
ALTER TABLE `stockmaster` CHANGE Labourcost labourcost decimal(20,4)  NOT NULL default '0.0000';
ALTER TABLE `stockmaster` CHANGE Overheadcost overheadcost decimal(20,4)  NOT NULL default '0.0000';
ALTER TABLE `stockmaster` CHANGE lowestlevel lowestlevel smallint(6)  NOT NULL default '0';
ALTER TABLE `stockmaster` CHANGE Discontinued discontinued tinyint(4)  NOT NULL default '0';
ALTER TABLE `stockmaster` CHANGE Controlled controlled tinyint(4)  NOT NULL default '0';
ALTER TABLE `stockmaster` CHANGE EOQ eoq double(10,2)  NOT NULL default '0.00';
ALTER TABLE `stockmaster` CHANGE Volume volume decimal(20,4)  NOT NULL default '0.0000';
ALTER TABLE `stockmaster` CHANGE KGS kgs decimal(20,4)  NOT NULL default '0.0000';
ALTER TABLE `stockmaster` CHANGE BarCode barcode varchar(50)  NOT NULL default '';
ALTER TABLE `stockmaster` CHANGE DiscountCategory discountcategory char(2)  NOT NULL default '';
ALTER TABLE `stockmaster` CHANGE TaxLevel taxlevel tinyint(4)  NOT NULL default '1';
ALTER TABLE `stockmaster` CHANGE Serialised serialised tinyint(4)  NOT NULL default '0';
ALTER TABLE `stockmaster` CHANGE DecimalPlaces decimalplaces tinyint(4)  NOT NULL default '0';
ALTER TABLE `stockmoves` CHANGE StkMoveNo stkmoveno int(11)  NOT NULL auto_increment;
ALTER TABLE `stockmoves` CHANGE StockID stockid varchar(20)  NOT NULL default '';
ALTER TABLE `stockmoves` CHANGE Type type smallint(6)  NOT NULL default '0';
ALTER TABLE `stockmoves` CHANGE TransNo transno int(11)  NOT NULL default '0';
ALTER TABLE `stockmoves` CHANGE LocCode loccode varchar(5)  NOT NULL default '';
ALTER TABLE `stockmoves` CHANGE TranDate trandate date  NOT NULL default '0000-00-00';
ALTER TABLE `stockmoves` CHANGE DebtorNo debtorno varchar(10)  NOT NULL default '';
ALTER TABLE `stockmoves` CHANGE BranchCode branchcode varchar(10)  NOT NULL default '';
ALTER TABLE `stockmoves` CHANGE Price price decimal(20,4)  NOT NULL default '0.0000';
ALTER TABLE `stockmoves` CHANGE Prd prd smallint(6)  NOT NULL default '0';
ALTER TABLE `stockmoves` CHANGE Reference reference varchar(40)  NOT NULL default '';
ALTER TABLE `stockmoves` CHANGE Qty qty double(16,4)  NOT NULL default '1.0000';
ALTER TABLE `stockmoves` CHANGE DiscountPercent discountpercent double(16,4)  NOT NULL default '0.0000';
ALTER TABLE `stockmoves` CHANGE StandardCost standardcost double(16,4)  NOT NULL default '0.0000';
ALTER TABLE `stockmoves` CHANGE Show_On_Inv_Crds show_on_inv_crds tinyint(4)  NOT NULL default '1';
ALTER TABLE `stockmoves` CHANGE NewQOH newqoh double  NOT NULL default '0';
ALTER TABLE `stockmoves` CHANGE HideMovt hidemovt tinyint(4)  NOT NULL default '0';
ALTER TABLE `stockmoves` CHANGE TaxRate taxrate float  NOT NULL default '0';
ALTER TABLE `stockmoves` CHANGE Narrative narrative text  NOT NULL;
ALTER TABLE `stockserialitems` CHANGE StockID stockid varchar(20)  NOT NULL default '';
ALTER TABLE `stockserialitems` CHANGE LocCode loccode varchar(5)  NOT NULL default '';
ALTER TABLE `stockserialitems` CHANGE SerialNo serialno varchar(30)  NOT NULL default '';
ALTER TABLE `stockserialitems` CHANGE Quantity quantity float  NOT NULL default '0';
ALTER TABLE `stockserialmoves` CHANGE StkItmMoveNo stkitmmoveno int(11)  NOT NULL auto_increment;
ALTER TABLE `stockserialmoves` CHANGE StockMoveNo stockmoveno int(11)  NOT NULL default '0';
ALTER TABLE `stockserialmoves` CHANGE StockID stockid varchar(20)  NOT NULL default '';
ALTER TABLE `stockserialmoves` CHANGE SerialNo serialno varchar(30)  NOT NULL default '';
ALTER TABLE `stockserialmoves` CHANGE MoveQty moveqty float  NOT NULL default '0';
ALTER TABLE `suppallocs` CHANGE ID id int(11)  NOT NULL auto_increment;
ALTER TABLE `suppallocs` CHANGE Amt amt float(20,2)  NOT NULL default '0.00';
ALTER TABLE `suppallocs` CHANGE DateAlloc datealloc date  NOT NULL default '0000-00-00';
ALTER TABLE `suppallocs` CHANGE TransID_AllocFrom transid_allocfrom int(11)  NOT NULL default '0';
ALTER TABLE `suppallocs` CHANGE TransID_AllocTo transid_allocto int(11)  NOT NULL default '0';
ALTER TABLE `supptrans` CHANGE TransNo transno int(11)  NOT NULL default '0';
ALTER TABLE `supptrans` CHANGE Type type smallint(6)  NOT NULL default '0';
ALTER TABLE `supptrans` CHANGE SupplierNo supplierno varchar(10)  NOT NULL default '';
ALTER TABLE `supptrans` CHANGE SuppReference suppreference varchar(20)  NOT NULL default '';
ALTER TABLE `supptrans` CHANGE TranDate trandate date  NOT NULL default '0000-00-00';
ALTER TABLE `supptrans` CHANGE DueDate duedate date  NOT NULL default '0000-00-00';
ALTER TABLE `supptrans` CHANGE Settled settled tinyint(4)  NOT NULL default '0';
ALTER TABLE `supptrans` CHANGE Rate rate double(16,6)  NOT NULL default '1.000000';
ALTER TABLE `supptrans` CHANGE OvAmount ovamount double(16,4)  NOT NULL default '0.0000';
ALTER TABLE `supptrans` CHANGE OvGST ovgst double(16,4)  NOT NULL default '0.0000';
ALTER TABLE `supptrans` CHANGE DiffOnExch diffonexch double(16,4)  NOT NULL default '0.0000';
ALTER TABLE `supptrans` CHANGE Alloc alloc double(16,4)  NOT NULL default '0.0000';
ALTER TABLE `supptrans` CHANGE TransText transtext longblob;
ALTER TABLE `supptrans` CHANGE Hold hold tinyint(4)  NOT NULL default '0';
ALTER TABLE `supptrans` CHANGE ID id int(11)  NOT NULL auto_increment;
ALTER TABLE `suppliercontacts` CHANGE SupplierID supplierid varchar(10)  NOT NULL default '';
ALTER TABLE `suppliercontacts` CHANGE Contact contact varchar(30)  NOT NULL default '';
ALTER TABLE `suppliercontacts` CHANGE Position position varchar(30)  NOT NULL default '';
ALTER TABLE `suppliercontacts` CHANGE Tel tel varchar(30)  NOT NULL default '';
ALTER TABLE `suppliercontacts` CHANGE Fax fax varchar(30)  NOT NULL default '';
ALTER TABLE `suppliercontacts` CHANGE Mobile mobile varchar(30)  NOT NULL default '';
ALTER TABLE `suppliercontacts` CHANGE Email email varchar(55)  NOT NULL default '';
ALTER TABLE `suppliercontacts` CHANGE OrderContact ordercontact tinyint(1)  NOT NULL default '0';
ALTER TABLE `suppliers` CHANGE SupplierID supplierid char(10)  NOT NULL default '';
ALTER TABLE `suppliers` CHANGE SuppName suppname char(40)  NOT NULL default '';
ALTER TABLE `suppliers` CHANGE Address1 address1 char(40)  NOT NULL default '';
ALTER TABLE `suppliers` CHANGE Address2 address2 char(40)  NOT NULL default '';
ALTER TABLE `suppliers` CHANGE Address3 address3 char(40)  NOT NULL default '';
ALTER TABLE `suppliers` CHANGE Address4 address4 char(50)  NOT NULL default '';
ALTER TABLE `suppliers` CHANGE CurrCode currcode char(3)  NOT NULL default '';
ALTER TABLE `suppliers` CHANGE SupplierSince suppliersince date  NOT NULL default '0000-00-00';
ALTER TABLE `suppliers` CHANGE PaymentTerms paymentterms char(2)  NOT NULL default '';
ALTER TABLE `suppliers` CHANGE LastPaid lastpaid double(16,4)  NOT NULL default '0.0000';
ALTER TABLE `suppliers` CHANGE LastPaidDate lastpaiddate datetime  default NULL;
ALTER TABLE `suppliers` CHANGE BankAct bankact char(16)  NOT NULL default '';
ALTER TABLE `suppliers` CHANGE BankRef bankref char(12)  NOT NULL default '';
ALTER TABLE `suppliers` CHANGE BankPartics bankpartics char(12)  NOT NULL default '';
ALTER TABLE `suppliers` CHANGE Remittance remittance tinyint(4)  NOT NULL default '1';
ALTER TABLE `suppliers` CHANGE TaxAuthority taxauthority tinyint(4)  NOT NULL default '1';
ALTER TABLE `systypes` CHANGE TypeID typeid smallint(6)  NOT NULL default '0';
ALTER TABLE `systypes` CHANGE TypeName typename char(50)  NOT NULL default '';
ALTER TABLE `systypes` CHANGE TypeNo typeno int(11)  NOT NULL default '1';
ALTER TABLE `taxauthlevels` CHANGE TaxAuthority taxauthority tinyint(4)  NOT NULL default '1';
ALTER TABLE `taxauthlevels` CHANGE DispatchTaxAuthority dispatchtaxauthority tinyint(4)  NOT NULL default '1';
ALTER TABLE `taxauthlevels` CHANGE Level level tinyint(4)  NOT NULL default '0';
ALTER TABLE `taxauthlevels` CHANGE TaxRate taxrate double  NOT NULL default '0';
ALTER TABLE `taxauthorities` CHANGE TaxID taxid tinyint(4)  NOT NULL default '0';
ALTER TABLE `taxauthorities` CHANGE Description description char(20)  NOT NULL default '';
ALTER TABLE `taxauthorities` CHANGE TaxGLCode taxglcode int(11)  NOT NULL default '0';
ALTER TABLE `taxauthorities` CHANGE PurchTaxGLAccount purchtaxglaccount int(11)  NOT NULL default '0';
ALTER TABLE `www_users` CHANGE UserID userid varchar(20)  NOT NULL default '';
ALTER TABLE `www_users` CHANGE Password password varchar(20)  NOT NULL default '';
ALTER TABLE `www_users` CHANGE RealName realname varchar(35)  NOT NULL default '';
ALTER TABLE `www_users` CHANGE CustomerID customerid varchar(10)  NOT NULL default '';
ALTER TABLE `www_users` CHANGE Phone phone varchar(30)  NOT NULL default '';
ALTER TABLE `www_users` CHANGE Email email varchar(55)  default NULL;
ALTER TABLE `www_users` CHANGE DefaultLocation defaultlocation varchar(5)  NOT NULL default '';
ALTER TABLE `www_users` CHANGE FullAccess fullaccess int(11)  NOT NULL default '1';
ALTER TABLE `www_users` CHANGE LastVisitDate lastvisitdate datetime  default NULL;
ALTER TABLE `www_users` CHANGE BranchCode branchcode varchar(10)  NOT NULL default '';
ALTER TABLE `www_users` CHANGE PageSize pagesize varchar(20)  NOT NULL default 'A4';
ALTER TABLE `www_users` CHANGE ModulesAllowed modulesallowed varchar(20)  NOT NULL default '';
ALTER TABLE `www_users` CHANGE Blocked blocked tinyint(4)  NOT NULL default '0';
ALTER TABLE `www_users` CHANGE DisplayRecordsMax displayrecordsmax int(11)  NOT NULL default '0';
ALTER TABLE `www_users` CHANGE Theme theme varchar(30)  NOT NULL default 'fresh';
ALTER TABLE `www_users` CHANGE Language language char(2)  NOT NULL default 'en';
ALTER TABLE `workcentres` CHANGE Code code char(5)  NOT NULL default '';
ALTER TABLE `workcentres` CHANGE Location location char(5)  NOT NULL default '';
ALTER TABLE `workcentres` CHANGE Description description char(20)  NOT NULL default '';
ALTER TABLE `workcentres` CHANGE Capacity capacity double(16,4)  NOT NULL default '1.0000';
ALTER TABLE `workcentres` CHANGE OverheadPerHour overheadperhour decimal(20,4)  NOT NULL default '0.0000';
ALTER TABLE `workcentres` CHANGE OverheadRecoveryAct overheadrecoveryact int(11)  NOT NULL default '0';
ALTER TABLE `workcentres` CHANGE SetUpHrs setuphrs decimal(20,4)  NOT NULL default '0.0000';
ALTER TABLE `worksorders` CHANGE WORef woref char(20)  NOT NULL default '';
ALTER TABLE `worksorders` CHANGE LocCode loccode char(5)  NOT NULL default '';
ALTER TABLE `worksorders` CHANGE UnitsReqd unitsreqd smallint(6)  NOT NULL default '1';
ALTER TABLE `worksorders` CHANGE UnitsRecd unitsrecd double  NOT NULL default '0';
ALTER TABLE `worksorders` CHANGE StockID stockid char(20)  NOT NULL default '';
ALTER TABLE `worksorders` CHANGE StdCost stdcost decimal(20,4)  NOT NULL default '0.0000';
ALTER TABLE `worksorders` CHANGE RequiredBy requiredby date  NOT NULL default '0000-00-00';
ALTER TABLE `worksorders` CHANGE ReleasedDate releaseddate date  NOT NULL default '1800-01-01';
ALTER TABLE `worksorders` CHANGE AccumValueIssued accumvalueissued decimal(20,4)  NOT NULL default '0.0000';
ALTER TABLE `worksorders` CHANGE AccumValueTrfd accumvaluetrfd decimal(20,4)  NOT NULL default '0.0000';
ALTER TABLE `worksorders` CHANGE Closed closed tinyint(4)  NOT NULL default '0';
ALTER TABLE `worksorders` CHANGE Released released tinyint(4)  NOT NULL default '0';
ALTER TABLE `taxauthorities` CHANGE taxid taxid tinyint NOT NULL auto_increment;
ALTER TABLE `salesorders` ADD `quotation` TINYINT DEFAULT '0' NOT NULL ;
ALTER TABLE `salesorders` ADD INDEX ( `quotation` ) ;
CREATE TABLE `recurringsalesorders` (
  `recurrorderno` int(11) NOT NULL auto_increment,
  `debtorno` varchar(10) NOT NULL default '',
  `branchcode` varchar(10) NOT NULL default '',
  `customerref` varchar(50) NOT NULL default '',
  `buyername` varchar(50) default NULL,
  `comments` longblob,
  `orddate` date NOT NULL default '0000-00-00',
  `ordertype` char(2) NOT NULL default '',
  `shipvia` int(11) NOT NULL default '0',
  `deladd1` varchar(40) NOT NULL default '',
  `deladd2` varchar(20) NOT NULL default '',
  `deladd3` varchar(15) NOT NULL default '',
  `deladd4` varchar(15) default NULL,
  `contactphone` varchar(25) default NULL,
  `contactemail` varchar(25) default NULL,
  `deliverto` varchar(40) NOT NULL default '',
  `freightcost` float(10,2) NOT NULL default '0.00',
  `fromstkloc` varchar(5) NOT NULL default '',
  `lastrecurrence` date NOT NULL default '0000-00-00',
  `stopdate` date NOT NULL default '0000-00-00',
  `frequency` tinyint(4) NOT NULL default '1',
  `autoinvoice` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`recurrorderno`),
  KEY `debtorno` (`debtorno`),
  KEY `orddate` (`orddate`),
  KEY `ordertype` (`ordertype`),
  KEY `locationindex` (`fromstkloc`),
  KEY `branchcode` (`branchcode`,`debtorno`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `recurringsalesorders`
  ADD CONSTRAINT `recurringsalesorders_ibfk_1` FOREIGN KEY (`branchcode`, `debtorno`) REFERENCES `custbranch` (`branchcode`, `debtorno`);
  
  CREATE TABLE `recurrsalesorderdetails` (
  `recurrorderno` int(11) NOT NULL default '0',
  `stkcode` varchar(20) NOT NULL default '',
  `unitprice` double(16,4) NOT NULL default '0.0000',
  `quantity` double(16,4) NOT NULL default '0.0000',
  `discountpercent` double(16,4) NOT NULL default '0.0000',
  `narrative` text NOT NULL,
  PRIMARY KEY  (`recurrorderno`,`stkcode`),
  KEY `orderno` (`recurrorderno`),
  KEY `stkcode` (`stkcode`),
  CONSTRAINT `recurrsalesorderdetails_ibfk_2` FOREIGN KEY (`stkcode`) REFERENCES `stockmaster` (`stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `recurrsalesorderdetails`
  ADD CONSTRAINT `recurrsalesorderdetails_ibfk_1` FOREIGN KEY (`recurrorderno`) REFERENCES `recurringsalesorders` (`recurrorderno`);
    
 ALTER TABLE `scripts` CHANGE `pageid` `pageid` SMALLINT( 4 ) NOT NULL AUTO_INCREMENT ;
 
CREATE TABLE accountsection (
    sectionid integer NOT NULL,
    sectionname text NOT NULL,
    PRIMARY KEY (sectionid)
)ENGINE=InnoDB;

INSERT INTO accountsection (sectionid, sectionname) VALUES (1,'Income');
INSERT INTO accountsection (sectionid, sectionname) VALUES (2,'Cost Of Sales');
INSERT INTO accountsection (sectionid, sectionname) VALUES (5,'Overheads');
INSERT INTO accountsection (sectionid, sectionname) VALUES (10, 'Fixed Assets');
INSERT INTO accountsection (sectionid, sectionname) VALUES (20, 'Amounts Receivable');
INSERT INTO accountsection (sectionid, sectionname) VALUES (30, 'Amounts Payable');
INSERT INTO accountsection (sectionid, sectionname) VALUES (50, 'Financed By');

ALTER TABLE `accountgroups` CHANGE `sectioninaccounts` `sectioninaccounts` INT( 11 ) DEFAULT '0' NOT NULL;
ALTER TABLE `accountgroups` ADD INDEX ( `sectioninaccounts` );

ALTER TABLE `accountgroups` ADD CONSTRAINT `accountgroups_ibfk_1` FOREIGN KEY ( `sectioninaccounts` ) REFERENCES `accountsection` ( `sectionid` );


CREATE TABLE securityroles (
    secroleid int NOT NULL auto_increment,
    secrolename text NOT NULL,
    PRIMARY KEY(secroleid)
)TYPE=INNODB;


INSERT INTO securityroles VALUES (1, 'Inquiries/Order Entry');
INSERT INTO securityroles VALUES (2, 'Manufac/Stock Admin');
INSERT INTO securityroles VALUES (3, 'Purchasing Officer');
INSERT INTO securityroles VALUES (4, 'AP Clerk');
INSERT INTO securityroles VALUES (5, 'AR Clerk');
INSERT INTO securityroles VALUES (6, 'Accountant');
INSERT INTO securityroles VALUES (7, 'Customer Log On Only');
INSERT INTO securityroles VALUES (8, 'System Administrator');


CREATE TABLE securitytokens (
    tokenid integer NOT NULL,
    tokenname text NOT NULL,
    PRIMARY KEY (tokenid)
)TYPE=INNODB;

INSERT INTO securitytokens VALUES (1, 'Order Entry/Inquiries customer access only');
INSERT INTO securitytokens VALUES (2, 'Basic Reports and Inquiries with selection options');
INSERT INTO securitytokens VALUES (3, 'Credit notes and AR management');
INSERT INTO securitytokens VALUES (4, 'Purchasing data/PO Entry/Reorder Levels');
INSERT INTO securitytokens VALUES (5, 'Accounts Payable');
INSERT INTO securitytokens VALUES (6, 'Not Used');
INSERT INTO securitytokens VALUES (7, 'Bank Reconciliations');
INSERT INTO securitytokens VALUES (8, 'General ledger reports/inquiries');
INSERT INTO securitytokens VALUES (9, 'Not Used');
INSERT INTO securitytokens VALUES (10, 'General Ledger Maintenance, stock valuation & Configuration');
INSERT INTO securitytokens VALUES (11, 'Inventory Management and Pricing');
INSERT INTO securitytokens VALUES (12, 'Unknown');
INSERT INTO securitytokens VALUES (13, 'Unknown');
INSERT INTO securitytokens VALUES (14, 'Unknown');
INSERT INTO securitytokens VALUES (15, 'User Management and System Administration');


CREATE TABLE securitygroups (
    secroleid integer NOT NULL,
    tokenid integer NOT NULL,
    PRIMARY KEY (secroleid, tokenid),
    KEY (secroleid),
    KEY (tokenid)
) ENGINE=INNODB;


INSERT INTO securitygroups VALUES (1, 1);
INSERT INTO securitygroups VALUES (1, 2);
INSERT INTO securitygroups VALUES (2, 1);
INSERT INTO securitygroups VALUES (2, 2);
INSERT INTO securitygroups VALUES (2, 11);
INSERT INTO securitygroups VALUES (3, 1);
INSERT INTO securitygroups VALUES (3, 2);
INSERT INTO securitygroups VALUES (3, 3);
INSERT INTO securitygroups VALUES (3, 4);
INSERT INTO securitygroups VALUES (3, 5);
INSERT INTO securitygroups VALUES (3, 11);
INSERT INTO securitygroups VALUES (4, 1);
INSERT INTO securitygroups VALUES (4, 2);
INSERT INTO securitygroups VALUES (4, 5);
INSERT INTO securitygroups VALUES (5, 1);
INSERT INTO securitygroups VALUES (5, 2);
INSERT INTO securitygroups VALUES (5, 3);
INSERT INTO securitygroups VALUES (5, 11);
INSERT INTO securitygroups VALUES (6, 1);
INSERT INTO securitygroups VALUES (6, 2);
INSERT INTO securitygroups VALUES (6, 3);
INSERT INTO securitygroups VALUES (6, 4);
INSERT INTO securitygroups VALUES (6, 5);
INSERT INTO securitygroups VALUES (6, 6);
INSERT INTO securitygroups VALUES (6, 7);
INSERT INTO securitygroups VALUES (6, 8);
INSERT INTO securitygroups VALUES (6, 9);
INSERT INTO securitygroups VALUES (6, 10);
INSERT INTO securitygroups VALUES (6, 11);
INSERT INTO securitygroups VALUES (7, 1);
INSERT INTO securitygroups VALUES (8, 1);
INSERT INTO securitygroups VALUES (8, 2);
INSERT INTO securitygroups VALUES (8, 3);
INSERT INTO securitygroups VALUES (8, 4);
INSERT INTO securitygroups VALUES (8, 5);
INSERT INTO securitygroups VALUES (8, 6);
INSERT INTO securitygroups VALUES (8, 7);
INSERT INTO securitygroups VALUES (8, 8);
INSERT INTO securitygroups VALUES (8, 9);
INSERT INTO securitygroups VALUES (8, 10);
INSERT INTO securitygroups VALUES (8, 11);
INSERT INTO securitygroups VALUES (8, 12);
INSERT INTO securitygroups VALUES (8, 13);
INSERT INTO securitygroups VALUES (8, 14);
INSERT INTO securitygroups VALUES (8, 15);


ALTER TABLE securitygroups ADD CONSTRAINT securitygroups_secroleid_fk FOREIGN KEY (secroleid) REFERENCES securityroles(secroleid);

ALTER TABLE securitygroups ADD CONSTRAINT securitygroups_tokenid_fk FOREIGN KEY (tokenid) REFERENCES securitytokens(tokenid);

UPDATE www_users SET fullaccess = fullaccess + 1;

CREATE TABLE paymentmethods (
    paymentid tinyint NOT NULL auto_increment,
    paymentname varchar(15) NOT NULL,
    paymenttype integer DEFAULT 1 NOT NULL,
    receipttype integer DEFAULT 1 NOT NULL,
    PRIMARY KEY (paymentid)
)ENGINE=Innodb;

INSERT INTO paymentmethods VALUES (1, 'Cheque', 1, 1);
INSERT INTO paymentmethods VALUES (2, 'Cash', 1, 1);
INSERT INTO paymentmethods VALUES (3, 'Direct Credit', 1, 1);

CREATE TABLE unitsofmeasure (
	unitid tinyint NOT NULL auto_increment,
	unitname varchar(15) NOT NULL,
	PRIMARY KEY (unitid)
)ENGINE=Innodb;

INSERT INTO unitsofmeasure VALUES (2, 'metres');
INSERT INTO unitsofmeasure VALUES (3, 'kgs');
INSERT INTO unitsofmeasure VALUES (4, 'litres');
INSERT INTO unitsofmeasure VALUES (5, 'length');
INSERT INTO unitsofmeasure VALUES (6, 'pack');
INSERT INTO unitsofmeasure VALUES (1, 'each');

CREATE TABLE config(
confname varchar( 35 ) NOT NULL ,
confvalue text NOT NULL ,
PRIMARY KEY ( confname )
)ENGINE=Innodb;

INSERT INTO config VALUES('DefaultDateFormat','d/m/Y');
INSERT INTO config VALUES('DefaultTheme','fresh');
INSERT INTO config VALUES('PastDueDays1','30');
INSERT INTO config VALUES('PastDueDays2','60');
INSERT INTO config VALUES('DefaultCreditLimit','1000');
INSERT INTO config VALUES('Show_Settled_LastMonth','1');
INSERT INTO config VALUES('RomalpaClause','Ownership will not pass to the buyer until the goods have been paid for in full.');
INSERT INTO config VALUES('QuickEntries','10');
INSERT INTO config VALUES('DispatchCutOffTime','14');
INSERT INTO config VALUES('AllowSalesOfZeroCostItems','0');
INSERT INTO config VALUES('CreditingControlledItems_MustExist','0');
INSERT INTO config VALUES('DefaultPriceList','WS');
INSERT INTO config VALUES('Default_Shipper','1');
INSERT INTO config VALUES('DoFreightCalc','0');
INSERT INTO config VALUES('FreightChargeAppliesIfLessThan','1000');
INSERT INTO config VALUES('DefaultTaxLevel','1');
INSERT INTO config VALUES('TaxAuthorityReferenceName','Tax Ref');
INSERT INTO config VALUES('CountryOfOperation','USD');
INSERT INTO config VALUES('NumberOfPeriodsOfStockUsage','12');
INSERT INTO config VALUES('Check_Qty_Charged_vs_Del_Qty','1');
INSERT INTO config VALUES('Check_Price_Charged_vs_Order_Price','1');
INSERT INTO config VALUES('OverChargeProportion','30');
INSERT INTO config VALUES('OverReceiveProportion','20');
INSERT INTO config VALUES('PO_AllowSameItemMultipleTimes','1');
INSERT INTO config VALUES('YearEnd','3');
INSERT INTO config VALUES('PageLength','48');
INSERT INTO config VALUES('part_pics_dir','part_pics');
INSERT INTO config VALUES('MaxImageSize','300');
INSERT INTO config VALUES('reports_dir','reports');
INSERT INTO config VALUES('EDIHeaderMsgId','D:01B:UN:EAN010');
INSERT INTO config VALUES('EDIReference','WEBERP');
INSERT INTO config VALUES('EDI_MsgPending','EDI_Pending');
INSERT INTO config VALUES('EDI_MsgSent','EDI_Sent');
INSERT INTO config VALUES('EDI_Incoming_Orders', 'EDI_Incoming_Orders');
INSERT INTO config VALUES('DefaultDisplayRecordsMax','50');
INSERT INTO config VALUES('RadioBeaconStockLocation','BL');
INSERT INTO config VALUES('RadioBeaconHomeDir', '/home/RadioBeacon');
INSERT INTO config VALUES('RadioBeaconFileCounter', '/home/RadioBeacon/FileCounter');
INSERT INTO config VALUES('RadioBeaconFilePrefix', 'ORDXX');
INSERT INTO config VALUES('RadioBeaconFTP_server', '192.168.2.2');
INSERT INTO config VALUES('RadioBeaconFTP_user_name', 'RadioBeacon ftp server user name');
INSERT INTO config VALUES('RadioBeaconFTP_user_pass','Radio Beacon remote ftp server password');
INSERT INTO config VALUES('AutoDebtorNo','0');
INSERT INTO `config` VALUES ('HTTPS_Only', '0');
INSERT INTO `config` VALUES ('DB_Maintenance_LastRun', '2005-03-01');
INSERT INTO `config` VALUES ('DB_Maintenance', '1');
INSERT INTO `config` VALUES ('DefaultBlindPackNote', '1');
INSERT INTO `config` VALUES ('PackNoteFormat', '1');
INSERT INTO `config` VALUES ('CheckCreditLimits', '0');

INSERT INTO systypes VALUES ( 500, 'Auto Debtor Number', 10000 );

ALTER TABLE www_users CHANGE password password TEXT NOT NULL;
UPDATE www_users SET password ='f0f77a7f88e7c1e93ab4e316b4574c7843b00ea4' WHERE userid='demo';

ALTER TABLE www_users ADD COLUMN pinno varchar(30) NOT NULL;
ALTER TABLE www_users ADD COLUMN swipecard varchar(50) NOT NULL;

ALTER TABLE `taxauthorities` ADD `bank` VARCHAR( 50 ) NOT NULL ,
ADD `bankacctype` VARCHAR( 20 ) NOT NULL ,
ADD `bankacc` VARCHAR( 50 ) NOT NULL ,
ADD `bankswift` VARCHAR( 30 ) NOT NULL ;


ALTER TABLE banktrans CHANGE amountcleared amountcleared double NOT NULL default '0';
ALTER TABLE banktrans CHANGE amount amount double NOT NULL default '0';
ALTER TABLE buckets CHANGE capacity capacity double NOT NULL default '0.00';
ALTER TABLE chartdetails CHANGE budget budget double NOT NULL default '0';
ALTER TABLE chartdetails CHANGE actual actual double NOT NULL default '0';
ALTER TABLE chartdetails CHANGE bfwd bfwd double NOT NULL default '0';
ALTER TABLE chartdetails CHANGE bfwdbudget bfwdbudget double NOT NULL default '0';
ALTER TABLE debtorsmaster CHANGE creditlimit creditlimit double NOT NULL default '1000';
ALTER TABLE debtortrans CHANGE ovamount ovamount double NOT NULL default '0';
ALTER TABLE debtortrans CHANGE ovgst ovgst double NOT NULL default '0';
ALTER TABLE debtortrans CHANGE ovfreight ovfreight double NOT NULL default '0';
ALTER TABLE debtortrans CHANGE ovdiscount ovdiscount double NOT NULL default '0';
ALTER TABLE debtortrans CHANGE diffonexch diffonexch double NOT NULL default '0';
ALTER TABLE debtortrans CHANGE alloc alloc double NOT NULL default '0';
ALTER TABLE gltrans CHANGE amount amount double NOT NULL default '0';
ALTER TABLE recurringsalesorders CHANGE `freightcost` `freightcost` double NOT NULL default '0.00';
ALTER TABLE reportcolumns CHANGE  `constant` `constant` double NOT NULL default '0';
ALTER TABLE salesorders CHANGE freightcost freightcost double NOT NULL default '0.00';
ALTER TABLE shipmentcharges CHANGE  value value double NOT NULL default '0';
ALTER TABLE stockcheckfreeze CHANGE qoh qoh double NOT NULL default '0';
ALTER TABLE stockcounts CHANGE `qtycounted` `qtycounted` double NOT NULL default '0';
ALTER TABLE stockmoves CHANGE `taxrate` `taxrate` double NOT NULL default '0';
ALTER TABLE stockserialitems CHANGE `quantity` `quantity` double NOT NULL default '0';
ALTER TABLE stockserialmoves CHANGE `moveqty` `moveqty` double NOT NULL default '0';
ALTER TABLE suppallocs CHANGE `amt` `amt` double NOT NULL default '0.00';


CREATE TABLE `salescat` (
  `salescatid` tinyint(4) NOT NULL auto_increment,
  `parentcatid` tinyint(4) default NULL,
  `salescatname` varchar(30) default NULL,
  PRIMARY KEY  (`salescatid`)
) ENGINE=InnoDB;
   
CREATE TABLE salescatprod (
  salescatid tinyint not null,
  stockid varchar(20) not null,
PRIMARY KEY(salescatid,stockid)
) ENGINE=InnoDB;

ALTER TABLE salescatprod ADD INDEX (salescatid);
ALTER TABLE salescatprod ADD INDEX (stockid);
ALTER TABLE salescatprod ADD FOREIGN KEY (stockid) REFERENCES stockmaster (stockid);
ALTER TABLE salescatprod ADD FOREIGN KEY (salescatid) REFERENCES salescat (salescatid);

ALTER TABLE custbranch ADD deliverblind tinyint(1) default 1 AFTER defaultshipvia;

ALTER TABLE salesorders ADD deliverblind tinyint(1) default 1 AFTER deliverto;
ALTER TABLE debtorsmaster ADD taxref varchar(20) NOT NULL;
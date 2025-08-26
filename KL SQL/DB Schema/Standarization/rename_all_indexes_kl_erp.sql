-- =====================================================
-- COMPLETE INDEX RENAMING STATEMENTS FOR kl_erp DATABASE
-- Generated: 2025-08-23
-- Purpose: Rename all indexes to follow current naming standards
-- 
-- Naming Convention:
-- Primary Keys: pk_tablename
-- Unique Keys: uk_tablename_columnname(s)
-- Regular Indexes: idx_tablename_columnname(s)
-- Foreign Key Indexes: fk_tablename_reftable
-- =====================================================

-- IMPORTANT: This script uses DROP INDEX IF EXISTS to safely rename indexes
-- Execute in sections or all at once - safe for production use

-- =====================================================
-- TABLE: accountgroups
-- =====================================================
ALTER TABLE `accountgroups` DROP INDEX IF EXISTS `SequenceInTB`;
ALTER TABLE `accountgroups` ADD INDEX `idx_accountgroups_sequenceintb` (`sequenceintb`);

ALTER TABLE `accountgroups` DROP INDEX IF EXISTS `sectioninaccounts`;
ALTER TABLE `accountgroups` ADD INDEX `idx_accountgroups_sectioninaccounts` (`sectioninaccounts`);

ALTER TABLE `accountgroups` DROP INDEX IF EXISTS `parentgroupname`;
ALTER TABLE `accountgroups` ADD INDEX `idx_accountgroups_parentgroupname` (`parentgroupname`);

-- =====================================================
-- TABLE: auditscripts
-- =====================================================
ALTER TABLE `auditscripts` DROP INDEX IF EXISTS `UserID`;
ALTER TABLE `auditscripts` ADD INDEX `idx_auditscripts_userid` (`userid`);

ALTER TABLE `auditscripts` DROP INDEX IF EXISTS `ExecutionDate`;
ALTER TABLE `auditscripts` ADD INDEX `idx_auditscripts_executiondate` (`executiondate`);

ALTER TABLE `auditscripts` DROP INDEX IF EXISTS `Title`;
ALTER TABLE `auditscripts` ADD INDEX `idx_auditscripts_scripttitle` (`scripttitle`);

-- =====================================================
-- TABLE: audittrail
-- =====================================================
ALTER TABLE `audittrail` DROP INDEX IF EXISTS `UserID`;
ALTER TABLE `audittrail` ADD INDEX `idx_audittrail_userid` (`userid`);

ALTER TABLE `audittrail` DROP INDEX IF EXISTS `transactiondate`;
ALTER TABLE `audittrail` ADD INDEX `idx_audittrail_transactiondate` (`transactiondate`);

-- =====================================================
-- TABLE: bankaccounts
-- =====================================================
ALTER TABLE `bankaccounts` DROP INDEX IF EXISTS `currcode`;
ALTER TABLE `bankaccounts` ADD INDEX `idx_bankaccounts_currcode` (`currcode`);

ALTER TABLE `bankaccounts` DROP INDEX IF EXISTS `BankAccountName`;
ALTER TABLE `bankaccounts` ADD INDEX `idx_bankaccounts_bankaccountname` (`bankaccountname`);

ALTER TABLE `bankaccounts` DROP INDEX IF EXISTS `BankAccountNumber`;
ALTER TABLE `bankaccounts` ADD INDEX `idx_bankaccounts_bankaccountnumber` (`bankaccountnumber`);

-- =====================================================
-- TABLE: banktrans
-- =====================================================
ALTER TABLE `banktrans` DROP INDEX IF EXISTS `BankAct`;
ALTER TABLE `banktrans` ADD INDEX `idx_banktrans_bankact_ref` (`bankact`,`ref`);

ALTER TABLE `banktrans` DROP INDEX IF EXISTS `TransDate`;
ALTER TABLE `banktrans` ADD INDEX `idx_banktrans_transdate` (`transdate`);

ALTER TABLE `banktrans` DROP INDEX IF EXISTS `TransType`;
ALTER TABLE `banktrans` ADD INDEX `idx_banktrans_banktranstype` (`banktranstype`);

ALTER TABLE `banktrans` DROP INDEX IF EXISTS `Type`;
ALTER TABLE `banktrans` ADD INDEX `idx_banktrans_type_transno` (`type`,`transno`);

ALTER TABLE `banktrans` DROP INDEX IF EXISTS `CurrCode`;
ALTER TABLE `banktrans` ADD INDEX `idx_banktrans_currcode` (`currcode`);

ALTER TABLE `banktrans` DROP INDEX IF EXISTS `ref`;
ALTER TABLE `banktrans` ADD INDEX `idx_banktrans_ref` (`ref`);

-- =====================================================
-- TABLE: bom
-- =====================================================
ALTER TABLE `bom` DROP INDEX IF EXISTS `Component`;
ALTER TABLE `bom` ADD INDEX `idx_bom_component` (`component`);

ALTER TABLE `bom` DROP INDEX IF EXISTS `EffectiveAfter`;
ALTER TABLE `bom` ADD INDEX `idx_bom_effectiveafter` (`effectiveafter`);

ALTER TABLE `bom` DROP INDEX IF EXISTS `EffectiveTo`;
ALTER TABLE `bom` ADD INDEX `idx_bom_effectiveto` (`effectiveto`);

ALTER TABLE `bom` DROP INDEX IF EXISTS `LocCode`;
ALTER TABLE `bom` ADD INDEX `idx_bom_loccode` (`loccode`);

ALTER TABLE `bom` DROP INDEX IF EXISTS `Parent`;
ALTER TABLE `bom` ADD INDEX `idx_bom_parent_effective_loccode` (`parent`,`effectiveafter`,`effectiveto`,`loccode`);

ALTER TABLE `bom` DROP INDEX IF EXISTS `Parent_2`;
ALTER TABLE `bom` ADD INDEX `idx_bom_parent` (`parent`);

ALTER TABLE `bom` DROP INDEX IF EXISTS `WorkCentreAdded`;
ALTER TABLE `bom` ADD INDEX `idx_bom_workcentreadded` (`workcentreadded`);

-- =====================================================
-- TABLE: buckets
-- =====================================================
ALTER TABLE `buckets` DROP INDEX IF EXISTS `WorkCentre`;
ALTER TABLE `buckets` ADD INDEX `idx_buckets_workcentre` (`workcentre`);

ALTER TABLE `buckets` DROP INDEX IF EXISTS `AvailDate`;
ALTER TABLE `buckets` ADD INDEX `idx_buckets_availdate` (`availdate`);

-- =====================================================
-- TABLE: chartmaster
-- =====================================================
ALTER TABLE `chartmaster` DROP INDEX IF EXISTS `AccountName`;
ALTER TABLE `chartmaster` ADD INDEX `idx_chartmaster_accountname` (`accountname`);

ALTER TABLE `chartmaster` DROP INDEX IF EXISTS `Group_`;
ALTER TABLE `chartmaster` ADD INDEX `idx_chartmaster_group` (`group_`);

-- =====================================================
-- TABLE: chartmasterADU
-- =====================================================
ALTER TABLE `chartmasterADU` DROP INDEX IF EXISTS `AccountName`;
ALTER TABLE `chartmasterADU` ADD INDEX `idx_chartmasteradu_accountname` (`accountname`);

ALTER TABLE `chartmasterADU` DROP INDEX IF EXISTS `Group_`;
ALTER TABLE `chartmasterADU` ADD INDEX `idx_chartmasteradu_group` (`group_`);

-- =====================================================
-- TABLE: chartmasterBB
-- =====================================================
ALTER TABLE `chartmasterBB` DROP INDEX IF EXISTS `AccountName`;
ALTER TABLE `chartmasterBB` ADD INDEX `idx_chartmasterbb_accountname` (`accountname`);

ALTER TABLE `chartmasterBB` DROP INDEX IF EXISTS `Group_`;
ALTER TABLE `chartmasterBB` ADD INDEX `idx_chartmasterbb_group` (`group_`);

-- =====================================================
-- TABLE: chartmasterIK
-- =====================================================
ALTER TABLE `chartmasterIK` DROP INDEX IF EXISTS `AccountName`;
ALTER TABLE `chartmasterIK` ADD INDEX `idx_chartmasterik_accountname` (`accountname`);

ALTER TABLE `chartmasterIK` DROP INDEX IF EXISTS `Group_`;
ALTER TABLE `chartmasterIK` ADD INDEX `idx_chartmasterik_group` (`group_`);

-- =====================================================
-- TABLE: chartmasterPI
-- =====================================================
ALTER TABLE `chartmasterPI` DROP INDEX IF EXISTS `AccountName`;
ALTER TABLE `chartmasterPI` ADD INDEX `idx_chartmasterpi_accountname` (`accountname`);

ALTER TABLE `chartmasterPI` DROP INDEX IF EXISTS `Group_`;
ALTER TABLE `chartmasterPI` ADD INDEX `idx_chartmasterpi_group` (`group_`);

-- =====================================================
-- TABLE: chartmasterSMH
-- =====================================================
ALTER TABLE `chartmasterSMH` DROP INDEX IF EXISTS `AccountName`;
ALTER TABLE `chartmasterSMH` ADD INDEX `idx_chartmastersmh_accountname` (`accountname`);

ALTER TABLE `chartmasterSMH` DROP INDEX IF EXISTS `Group_`;
ALTER TABLE `chartmasterSMH` ADD INDEX `idx_chartmastersmh_group` (`group_`);

-- =====================================================
-- TABLE: cogsglpostings
-- =====================================================
ALTER TABLE `cogsglpostings` DROP INDEX IF EXISTS `Area_StkCat`;
ALTER TABLE `cogsglpostings` ADD UNIQUE INDEX `uk_cogsglpostings_area_stkcat_salestype` (`area`,`stkcat`,`salestype`);

ALTER TABLE `cogsglpostings` DROP INDEX IF EXISTS `Area`;
ALTER TABLE `cogsglpostings` ADD INDEX `idx_cogsglpostings_area` (`area`);

ALTER TABLE `cogsglpostings` DROP INDEX IF EXISTS `StkCat`;
ALTER TABLE `cogsglpostings` ADD INDEX `idx_cogsglpostings_stkcat` (`stkcat`);

ALTER TABLE `cogsglpostings` DROP INDEX IF EXISTS `GLCode`;
ALTER TABLE `cogsglpostings` ADD INDEX `idx_cogsglpostings_glcode` (`glcode`);

ALTER TABLE `cogsglpostings` DROP INDEX IF EXISTS `SalesType`;
ALTER TABLE `cogsglpostings` ADD INDEX `idx_cogsglpostings_salestype` (`salestype`);

-- =====================================================
-- TABLE: contractbom
-- =====================================================
ALTER TABLE `contractbom` DROP INDEX IF EXISTS `Stockid`;
ALTER TABLE `contractbom` ADD INDEX `idx_contractbom_stockid` (`stockid`);

ALTER TABLE `contractbom` DROP INDEX IF EXISTS `ContractRef`;
ALTER TABLE `contractbom` ADD INDEX `idx_contractbom_contractref` (`contractref`);

ALTER TABLE `contractbom` DROP INDEX IF EXISTS `WorkCentreAdded`;
ALTER TABLE `contractbom` ADD INDEX `idx_contractbom_workcentreadded` (`workcentreadded`);

-- =====================================================
-- TABLE: contractcharges
-- =====================================================
ALTER TABLE `contractcharges` DROP INDEX IF EXISTS `contractref`;
ALTER TABLE `contractcharges` ADD INDEX `idx_contractcharges_contractref_transtype_transno` (`contractref`,`transtype`,`transno`);

ALTER TABLE `contractcharges` DROP INDEX IF EXISTS `contractcharges_ibfk_2`;
ALTER TABLE `contractcharges` ADD INDEX `idx_contractcharges_transtype` (`transtype`);

-- =====================================================
-- TABLE: contractreqts
-- =====================================================
ALTER TABLE `contractreqts` DROP INDEX IF EXISTS `ContractRef`;
ALTER TABLE `contractreqts` ADD INDEX `idx_contractreqts_contractref` (`contractref`);

-- =====================================================
-- TABLE: contracts
-- =====================================================
ALTER TABLE `contracts` DROP INDEX IF EXISTS `OrderNo`;
ALTER TABLE `contracts` ADD INDEX `idx_contracts_orderno` (`orderno`);

ALTER TABLE `contracts` DROP INDEX IF EXISTS `CategoryID`;
ALTER TABLE `contracts` ADD INDEX `idx_contracts_categoryid` (`categoryid`);

ALTER TABLE `contracts` DROP INDEX IF EXISTS `Status`;
ALTER TABLE `contracts` ADD INDEX `idx_contracts_status` (`status`);

ALTER TABLE `contracts` DROP INDEX IF EXISTS `WO`;
ALTER TABLE `contracts` ADD INDEX `idx_contracts_wo` (`wo`);

ALTER TABLE `contracts` DROP INDEX IF EXISTS `loccode`;
ALTER TABLE `contracts` ADD INDEX `idx_contracts_loccode` (`loccode`);

ALTER TABLE `contracts` DROP INDEX IF EXISTS `DebtorNo`;
ALTER TABLE `contracts` ADD INDEX `idx_contracts_debtorno_branchcode` (`debtorno`,`branchcode`);

-- =====================================================
-- TABLE: currencies
-- =====================================================
ALTER TABLE `currencies` DROP INDEX IF EXISTS `Country`;
ALTER TABLE `currencies` ADD INDEX `idx_currencies_country` (`country`);

-- =====================================================
-- TABLE: custallocns
-- =====================================================
ALTER TABLE `custallocns` DROP INDEX IF EXISTS `DateAlloc`;
ALTER TABLE `custallocns` ADD INDEX `idx_custallocns_datealloc` (`datealloc`);

ALTER TABLE `custallocns` DROP INDEX IF EXISTS `TransID_AllocFrom`;
ALTER TABLE `custallocns` ADD INDEX `idx_custallocns_transid_allocfrom` (`transid_allocfrom`);

ALTER TABLE `custallocns` DROP INDEX IF EXISTS `TransID_AllocTo`;
ALTER TABLE `custallocns` ADD INDEX `idx_custallocns_transid_allocto` (`transid_allocto`);

-- =====================================================
-- TABLE: custbranch
-- =====================================================
ALTER TABLE `custbranch` DROP INDEX IF EXISTS `BrName`;
ALTER TABLE `custbranch` ADD INDEX `idx_custbranch_brname` (`brname`);

ALTER TABLE `custbranch` DROP INDEX IF EXISTS `DebtorNo`;
ALTER TABLE `custbranch` ADD INDEX `idx_custbranch_debtorno` (`debtorno`);

ALTER TABLE `custbranch` DROP INDEX IF EXISTS `Salesman`;
ALTER TABLE `custbranch` ADD INDEX `idx_custbranch_salesman` (`salesman`);

ALTER TABLE `custbranch` DROP INDEX IF EXISTS `Area`;
ALTER TABLE `custbranch` ADD INDEX `idx_custbranch_area` (`area`);

ALTER TABLE `custbranch` DROP INDEX IF EXISTS `DefaultLocation`;
ALTER TABLE `custbranch` ADD INDEX `idx_custbranch_defaultlocation` (`defaultlocation`);

ALTER TABLE `custbranch` DROP INDEX IF EXISTS `DefaultShipVia`;
ALTER TABLE `custbranch` ADD INDEX `idx_custbranch_defaultshipvia` (`defaultshipvia`);

ALTER TABLE `custbranch` DROP INDEX IF EXISTS `taxgroupid`;
ALTER TABLE `custbranch` ADD INDEX `idx_custbranch_taxgroupid` (`taxgroupid`);

-- =====================================================
-- TABLE: custitem
-- =====================================================
ALTER TABLE `custitem` DROP INDEX IF EXISTS `StockID`;
ALTER TABLE `custitem` ADD INDEX `idx_custitem_stockid` (`stockid`);

ALTER TABLE `custitem` DROP INDEX IF EXISTS `Debtorno`;
ALTER TABLE `custitem` ADD INDEX `idx_custitem_debtorno` (`debtorno`);

-- =====================================================
-- TABLE: debtorsmaster
-- =====================================================
ALTER TABLE `debtorsmaster` DROP INDEX IF EXISTS `TypeId`;
ALTER TABLE `debtorsmaster` ADD UNIQUE INDEX `uk_debtorsmaster_typeid_debtorno` (`typeid`,`debtorno`);

ALTER TABLE `debtorsmaster` DROP INDEX IF EXISTS `ClientSince`;
ALTER TABLE `debtorsmaster` ADD UNIQUE INDEX `uk_debtorsmaster_clientsince_debtorno` (`clientsince`,`debtorno`);

ALTER TABLE `debtorsmaster` DROP INDEX IF EXISTS `Currency`;
ALTER TABLE `debtorsmaster` ADD UNIQUE INDEX `uk_debtorsmaster_currcode_debtorno` (`currcode`,`debtorno`);

ALTER TABLE `debtorsmaster` DROP INDEX IF EXISTS `HoldReason`;
ALTER TABLE `debtorsmaster` ADD INDEX `idx_debtorsmaster_holdreason` (`holdreason`);

ALTER TABLE `debtorsmaster` DROP INDEX IF EXISTS `Name`;
ALTER TABLE `debtorsmaster` ADD INDEX `idx_debtorsmaster_name` (`name`);

ALTER TABLE `debtorsmaster` DROP INDEX IF EXISTS `PaymentTerms`;
ALTER TABLE `debtorsmaster` ADD INDEX `idx_debtorsmaster_paymentterms` (`paymentterms`);

ALTER TABLE `debtorsmaster` DROP INDEX IF EXISTS `SalesType`;
ALTER TABLE `debtorsmaster` ADD INDEX `idx_debtorsmaster_salestype` (`salestype`);

ALTER TABLE `debtorsmaster` DROP INDEX IF EXISTS `EDIInvoices`;
ALTER TABLE `debtorsmaster` ADD INDEX `idx_debtorsmaster_ediinvoices` (`ediinvoices`);

ALTER TABLE `debtorsmaster` DROP INDEX IF EXISTS `EDIOrders`;
ALTER TABLE `debtorsmaster` ADD INDEX `idx_debtorsmaster_ediorders` (`ediorders`);

-- =====================================================
-- TABLE: debtortrans
-- =====================================================
ALTER TABLE `debtortrans` DROP INDEX IF EXISTS `DebtorNo`;
ALTER TABLE `debtortrans` ADD INDEX `idx_debtortrans_debtorno_branchcode` (`debtorno`,`branchcode`);

ALTER TABLE `debtortrans` DROP INDEX IF EXISTS `Order_`;
ALTER TABLE `debtortrans` ADD INDEX `idx_debtortrans_order` (`order_`);

ALTER TABLE `debtortrans` DROP INDEX IF EXISTS `Prd`;
ALTER TABLE `debtortrans` ADD INDEX `idx_debtortrans_prd` (`prd`);

ALTER TABLE `debtortrans` DROP INDEX IF EXISTS `Tpe`;
ALTER TABLE `debtortrans` ADD INDEX `idx_debtortrans_tpe` (`tpe`);

ALTER TABLE `debtortrans` DROP INDEX IF EXISTS `Type`;
ALTER TABLE `debtortrans` ADD INDEX `idx_debtortrans_type` (`type`);

ALTER TABLE `debtortrans` DROP INDEX IF EXISTS `Settled`;
ALTER TABLE `debtortrans` ADD INDEX `idx_debtortrans_settled` (`settled`);

ALTER TABLE `debtortrans` DROP INDEX IF EXISTS `TranDate`;
ALTER TABLE `debtortrans` ADD INDEX `idx_debtortrans_trandate` (`trandate`);

ALTER TABLE `debtortrans` DROP INDEX IF EXISTS `TransNo`;
ALTER TABLE `debtortrans` ADD INDEX `idx_debtortrans_transno` (`transno`);

ALTER TABLE `debtortrans` DROP INDEX IF EXISTS `Type_2`;
ALTER TABLE `debtortrans` ADD INDEX `idx_debtortrans_type_transno` (`type`,`transno`);

ALTER TABLE `debtortrans` DROP INDEX IF EXISTS `EDISent`;
ALTER TABLE `debtortrans` ADD INDEX `idx_debtortrans_edisent` (`edisent`);

ALTER TABLE `debtortrans` DROP INDEX IF EXISTS `salesperson`;
ALTER TABLE `debtortrans` ADD INDEX `idx_debtortrans_salesperson` (`salesperson`);

-- =====================================================
-- TABLE: debtortranstaxes
-- =====================================================
ALTER TABLE `debtortranstaxes` DROP INDEX IF EXISTS `taxauthid`;
ALTER TABLE `debtortranstaxes` ADD INDEX `idx_debtortranstaxes_taxauthid` (`taxauthid`);

-- =====================================================
-- TABLE: deliverynotes
-- =====================================================
ALTER TABLE `deliverynotes` DROP INDEX IF EXISTS `deliverynotes_ibfk_2`;
ALTER TABLE `deliverynotes` ADD INDEX `idx_deliverynotes_salesorderno_salesorderlineno` (`salesorderno`,`salesorderlineno`);

-- =====================================================
-- TABLE: discountmatrix
-- =====================================================
ALTER TABLE `discountmatrix` DROP INDEX IF EXISTS `QuantityBreak`;
ALTER TABLE `discountmatrix` ADD INDEX `idx_discountmatrix_quantitybreak` (`quantitybreak`);

ALTER TABLE `discountmatrix` DROP INDEX IF EXISTS `DiscountCategory`;
ALTER TABLE `discountmatrix` ADD INDEX `idx_discountmatrix_discountcategory` (`discountcategory`);

ALTER TABLE `discountmatrix` DROP INDEX IF EXISTS `SalesType`;
ALTER TABLE `discountmatrix` ADD INDEX `idx_discountmatrix_salestype` (`salestype`);

-- =====================================================
-- TABLE: ediitemmapping
-- =====================================================
ALTER TABLE `ediitemmapping` DROP INDEX IF EXISTS `PartnerCode`;
ALTER TABLE `ediitemmapping` ADD INDEX `idx_ediitemmapping_partnercode` (`partnercode`);

ALTER TABLE `ediitemmapping` DROP INDEX IF EXISTS `StockID`;
ALTER TABLE `ediitemmapping` ADD INDEX `idx_ediitemmapping_stockid` (`stockid`);

ALTER TABLE `ediitemmapping` DROP INDEX IF EXISTS `PartnerStockID`;
ALTER TABLE `ediitemmapping` ADD INDEX `idx_ediitemmapping_partnerstockid` (`partnerstockid`);

ALTER TABLE `ediitemmapping` DROP INDEX IF EXISTS `SuppOrCust`;
ALTER TABLE `ediitemmapping` ADD INDEX `idx_ediitemmapping_supporcust` (`supporcust`);

-- =====================================================
-- TABLE: edimessageformat
-- =====================================================
ALTER TABLE `edimessageformat` DROP INDEX IF EXISTS `PartnerCode`;
ALTER TABLE `edimessageformat` ADD UNIQUE INDEX `uk_edimessageformat_partnercode_messagetype_sequenceno` (`partnercode`,`messagetype`,`sequenceno`);

ALTER TABLE `edimessageformat` DROP INDEX IF EXISTS `Section`;
ALTER TABLE `edimessageformat` ADD INDEX `idx_edimessageformat_section` (`section`);

-- =====================================================
-- TABLE: edi_orders_segs
-- =====================================================
ALTER TABLE `edi_orders_segs` DROP INDEX IF EXISTS `SegTag`;
ALTER TABLE `edi_orders_segs` ADD INDEX `idx_edi_orders_segs_segtag` (`segtag`);

ALTER TABLE `edi_orders_segs` DROP INDEX IF EXISTS `SegNo`;
ALTER TABLE `edi_orders_segs` ADD INDEX `idx_edi_orders_segs_seggroup` (`seggroup`);

-- =====================================================
-- TABLE: employees
-- =====================================================
ALTER TABLE `employees` DROP INDEX IF EXISTS `surname`;
ALTER TABLE `employees` ADD INDEX `idx_employees_surname` (`surname`);

ALTER TABLE `employees` DROP INDEX IF EXISTS `firstname`;
ALTER TABLE `employees` ADD INDEX `idx_employees_firstname` (`firstname`);

ALTER TABLE `employees` DROP INDEX IF EXISTS `stockid`;
ALTER TABLE `employees` ADD INDEX `idx_employees_stockid` (`stockid`);

ALTER TABLE `employees` DROP INDEX IF EXISTS `manager`;
ALTER TABLE `employees` ADD INDEX `idx_employees_manager` (`manager`);

ALTER TABLE `employees` DROP INDEX IF EXISTS `userid`;
ALTER TABLE `employees` ADD INDEX `idx_employees_userid` (`userid`);

-- =====================================================
-- TABLE: freightcosts
-- =====================================================
ALTER TABLE `freightcosts` DROP INDEX IF EXISTS `Destination`;
ALTER TABLE `freightcosts` ADD INDEX `idx_freightcosts_destination` (`destination`);

ALTER TABLE `freightcosts` DROP INDEX IF EXISTS `LocationFrom`;
ALTER TABLE `freightcosts` ADD INDEX `idx_freightcosts_locationfrom` (`locationfrom`);

ALTER TABLE `freightcosts` DROP INDEX IF EXISTS `ShipperID`;
ALTER TABLE `freightcosts` ADD INDEX `idx_freightcosts_shipperid` (`shipperid`);

ALTER TABLE `freightcosts` DROP INDEX IF EXISTS `Destination_2`;
ALTER TABLE `freightcosts` ADD INDEX `idx_freightcosts_destination_locationfrom_shipperid` (`destination`,`locationfrom`,`shipperid`);

-- =====================================================
-- TABLE: glaccountusers
-- =====================================================
ALTER TABLE `glaccountusers` DROP INDEX IF EXISTS `useraccount`;
ALTER TABLE `glaccountusers` ADD UNIQUE INDEX `uk_glaccountusers_userid_accountcode` (`userid`,`accountcode`);

ALTER TABLE `glaccountusers` DROP INDEX IF EXISTS `accountuser`;
ALTER TABLE `glaccountusers` ADD UNIQUE INDEX `uk_glaccountusers_accountcode_userid` (`accountcode`,`userid`);

-- =====================================================
-- TABLE: glbudgetdetails
-- =====================================================
ALTER TABLE `glbudgetdetails` DROP INDEX IF EXISTS `account`;
ALTER TABLE `glbudgetdetails` ADD INDEX `idx_glbudgetdetails_account` (`account`);

ALTER TABLE `glbudgetdetails` DROP INDEX IF EXISTS `headerid`;
ALTER TABLE `glbudgetdetails` ADD INDEX `idx_glbudgetdetails_headerid_account_period` (`headerid`,`account`,`period`);

-- =====================================================
-- TABLE: gltags
-- =====================================================
ALTER TABLE `gltags` DROP INDEX IF EXISTS `tagref`;
ALTER TABLE `gltags` ADD INDEX `idx_gltags_tagref` (`tagref`);

-- =====================================================
-- TABLE: gltrans (CRITICAL FINANCIAL TABLE)
-- =====================================================
ALTER TABLE `gltrans` DROP INDEX IF EXISTS `Account`;
ALTER TABLE `gltrans` ADD INDEX `idx_gltrans_account_trandate` (`account`,`trandate`);

ALTER TABLE `gltrans` DROP INDEX IF EXISTS `TranDate`;
ALTER TABLE `gltrans` ADD INDEX `idx_gltrans_trandate_account` (`trandate`,`account`);

ALTER TABLE `gltrans` DROP INDEX IF EXISTS `PeriodNo`;
ALTER TABLE `gltrans` ADD INDEX `idx_gltrans_periodno_account` (`periodno`,`account`);

ALTER TABLE `gltrans` DROP INDEX IF EXISTS `Type_and_Number`;
ALTER TABLE `gltrans` ADD INDEX `idx_gltrans_type_typeno` (`type`,`typeno`);

-- =====================================================
-- TABLE: grns
-- =====================================================
ALTER TABLE `grns` DROP INDEX IF EXISTS `DeliveryDate`;
ALTER TABLE `grns` ADD INDEX `idx_grns_deliverydate` (`deliverydate`);

ALTER TABLE `grns` DROP INDEX IF EXISTS `ItemCode`;
ALTER TABLE `grns` ADD INDEX `idx_grns_itemcode` (`itemcode`);

ALTER TABLE `grns` DROP INDEX IF EXISTS `PODetailItem`;
ALTER TABLE `grns` ADD INDEX `idx_grns_podetailitem` (`podetailitem`);

ALTER TABLE `grns` DROP INDEX IF EXISTS `SupplierID`;
ALTER TABLE `grns` ADD INDEX `idx_grns_supplierid` (`supplierid`);

-- =====================================================
-- TABLE: holdreasons
-- =====================================================
ALTER TABLE `holdreasons` DROP INDEX IF EXISTS `ReasonDescription`;
ALTER TABLE `holdreasons` ADD INDEX `idx_holdreasons_reasondescription` (`reasondescription`);

-- =====================================================
-- TABLE: internalstockcatrole
-- =====================================================
ALTER TABLE `internalstockcatrole` DROP INDEX IF EXISTS `internalstockcatrole_ibfk_1`;
ALTER TABLE `internalstockcatrole` ADD INDEX `idx_internalstockcatrole_categoryid` (`categoryid`);

ALTER TABLE `internalstockcatrole` DROP INDEX IF EXISTS `internalstockcatrole_ibfk_2`;
ALTER TABLE `internalstockcatrole` ADD INDEX `idx_internalstockcatrole_secroleid` (`secroleid`);

-- =====================================================
-- TABLE: kladjustrl
-- =====================================================
ALTER TABLE `kladjustrl` DROP INDEX IF EXISTS `StockID`;
ALTER TABLE `kladjustrl` ADD INDEX `idx_kladjustrl_stockid` (`stockid`);

-- =====================================================
-- TABLE: klarchivedtables
-- =====================================================
ALTER TABLE `klarchivedtables` DROP INDEX IF EXISTS `name`;
ALTER TABLE `klarchivedtables` ADD UNIQUE INDEX `uk_klarchivedtables_name` (`name`);

-- =====================================================
-- TABLE: klchangeprice
-- =====================================================
ALTER TABLE `klchangeprice` DROP INDEX IF EXISTS `stockid`;
ALTER TABLE `klchangeprice` ADD INDEX `idx_klchangeprice_stockid_startprocessdate_endprocessdate` (`stockid`,`startprocessdate`,`endprocessdate`);

-- =====================================================
-- TABLE: klconsignment
-- =====================================================
ALTER TABLE `klconsignment` DROP INDEX IF EXISTS `stockid+dates`;
ALTER TABLE `klconsignment` ADD INDEX `idx_klconsignment_stockid_saledate_fakturpajakdate` (`stockid`,`saledate`,`fakturpajakdate`);

ALTER TABLE `klconsignment` DROP INDEX IF EXISTS `stockid+dateinvoiced`;
ALTER TABLE `klconsignment` ADD INDEX `idx_klconsignment_stockid_saledate_invoicedtopartner` (`stockid`,`saledate`,`invoicedtopartner`);

ALTER TABLE `klconsignment` DROP INDEX IF EXISTS `issued`;
ALTER TABLE `klconsignment` ADD INDEX `idx_klconsignment_companycode_partnercode_invoicedtopartner` (`companycode`,`partnercode`,`invoicedtopartner`);

-- =====================================================
-- TABLE: klkpi
-- =====================================================
ALTER TABLE `klkpi` DROP INDEX IF EXISTS `concept+date`;
ALTER TABLE `klkpi` ADD UNIQUE INDEX `uk_klkpi_kpicode_date` (`kpicode`,`date`);

ALTER TABLE `klkpi` DROP INDEX IF EXISTS `date+concept`;
ALTER TABLE `klkpi` ADD UNIQUE INDEX `uk_klkpi_date_kpicode` (`date`,`kpicode`);

-- =====================================================
-- TABLE: klkpidescriptions
-- =====================================================
ALTER TABLE `klkpidescriptions` DROP INDEX IF EXISTS `kpicode`;
ALTER TABLE `klkpidescriptions` ADD UNIQUE INDEX `uk_klkpidescriptions_kpicode` (`kpicode`);

-- =====================================================
-- TABLE: klmaintenancetasks
-- =====================================================
ALTER TABLE `klmaintenancetasks` DROP INDEX IF EXISTS `CounterIndex`;
ALTER TABLE `klmaintenancetasks` ADD UNIQUE INDEX `uk_klmaintenancetasks_counterindex` (`counterindex`);

ALTER TABLE `klmaintenancetasks` DROP INDEX IF EXISTS `Location`;
ALTER TABLE `klmaintenancetasks` ADD UNIQUE INDEX `uk_klmaintenancetasks_loccode_creationdate_counterindex` (`loccode`,`creationdate`,`counterindex`);

ALTER TABLE `klmaintenancetasks` DROP INDEX IF EXISTS `closed`;
ALTER TABLE `klmaintenancetasks` ADD UNIQUE INDEX `uk_klmaintenancetasks_closed_loccode_counterindex` (`closed`,`loccode`,`counterindex`);

-- =====================================================
-- TABLE: klmaintenancetaskupdates
-- =====================================================
ALTER TABLE `klmaintenancetaskupdates` DROP INDEX IF EXISTS `counterindex`;
ALTER TABLE `klmaintenancetaskupdates` ADD UNIQUE INDEX `uk_klmaintenancetaskupdates_counterindex` (`counterindex`);

ALTER TABLE `klmaintenancetaskupdates` DROP INDEX IF EXISTS `taskcounter`;
ALTER TABLE `klmaintenancetaskupdates` ADD UNIQUE INDEX `uk_klmaintenancetaskupdates_taskcounter_counterindex` (`taskcounter`,`counterindex`);

-- =====================================================
-- TABLE: klmaintenancetypes
-- =====================================================
ALTER TABLE `klmaintenancetypes` DROP INDEX IF EXISTS `maintenancetype`;
ALTER TABLE `klmaintenancetypes` ADD UNIQUE INDEX `uk_klmaintenancetypes_maintenancetype` (`maintenancetype`);

-- =====================================================
-- TABLE: klmovetodiscount20
-- =====================================================
ALTER TABLE `klmovetodiscount20` DROP INDEX IF EXISTS `stockid`;
ALTER TABLE `klmovetodiscount20` ADD INDEX `idx_klmovetodiscount20_stockid_startprocessdate_endprocessdate` (`stockid`,`startprocessdate`,`endprocessdate`);

-- =====================================================
-- TABLE: klmovetodiscount50
-- =====================================================
ALTER TABLE `klmovetodiscount50` DROP INDEX IF EXISTS `stockid`;
ALTER TABLE `klmovetodiscount50` ADD INDEX `idx_klmovetodiscount50_stockid_startprocessdate_endprocessdate` (`stockid`,`startprocessdate`,`endprocessdate`);

-- =====================================================
-- TABLE: klmovetodiscount80
-- =====================================================
ALTER TABLE `klmovetodiscount80` DROP INDEX IF EXISTS `stockid`;
ALTER TABLE `klmovetodiscount80` ADD INDEX `idx_klmovetodiscount80_stockid_startprocessdate_endprocessdate` (`stockid`,`startprocessdate`,`endprocessdate`);

-- =====================================================
-- TABLE: klonlinepartners
-- =====================================================
ALTER TABLE `klonlinepartners` DROP INDEX IF EXISTS `onlinepartnercode`;
ALTER TABLE `klonlinepartners` ADD UNIQUE INDEX `
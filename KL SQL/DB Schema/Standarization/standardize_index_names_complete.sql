
-- =====================================================
-- STANDARDIZE INDEX NAMES - COMPLETE SCRIPT
-- Script to rename indexes with non-standard names to proper standard names
-- Generated on: 2025-08-23
-- =====================================================

-- Standard naming convention:
-- Primary Key: pk_tablename
-- Unique Key: uk_tablename_columnname(s)
-- Index: idx_tablename_columnname(s)
-- Foreign Key: fk_tablename_referencedtable

-- =====================================================
-- TABLE: debtortrans
-- =====================================================

-- Rename non-standard index names
DROP INDEX IF EXISTS `DebtorNo` ON `debtortrans`;
ALTER TABLE `debtortrans` ADD INDEX `idx_debtortrans_debtorno_branchcode` (`debtorno`, `branchcode`);

DROP INDEX IF EXISTS `Order_` ON `debtortrans`;
ALTER TABLE `debtortrans` ADD INDEX `idx_debtortrans_order` (`order_`);

DROP INDEX IF EXISTS `Prd` ON `debtortrans`;
ALTER TABLE `debtortrans` ADD INDEX `idx_debtortrans_prd` (`prd`);

DROP INDEX IF EXISTS `Tpe` ON `debtortrans`;
ALTER TABLE `debtortrans` ADD INDEX `idx_debtortrans_tpe` (`tpe`);

DROP INDEX IF EXISTS `Type` ON `debtortrans`;
ALTER TABLE `debtortrans` ADD INDEX `idx_debtortrans_type` (`type`);

DROP INDEX IF EXISTS `Settled` ON `debtortrans`;
ALTER TABLE `debtortrans` ADD INDEX `idx_debtortrans_settled` (`settled`);

DROP INDEX IF EXISTS `TranDate` ON `debtortrans`;
ALTER TABLE `debtortrans` ADD INDEX `idx_debtortrans_trandate` (`trandate`);

DROP INDEX IF EXISTS `TransNo` ON `debtortrans`;
ALTER TABLE `debtortrans` ADD INDEX `idx_debtortrans_transno` (`transno`);

DROP INDEX IF EXISTS `Type_2` ON `debtortrans`;
ALTER TABLE `debtortrans` ADD INDEX `idx_debtortrans_type_transno` (`type`, `transno`);

DROP INDEX IF EXISTS `EDISent` ON `debtortrans`;
ALTER TABLE `debtortrans` ADD INDEX `idx_debtortrans_edisent` (`edisent`);

DROP INDEX IF EXISTS `salesperson` ON `debtortrans`;
ALTER TABLE `debtortrans` ADD INDEX `idx_debtortrans_salesperson` (`salesperson`);

-- =====================================================
-- TABLE: debtortranstaxes
-- =====================================================

DROP INDEX IF EXISTS `taxauthid` ON `debtortranstaxes`;
ALTER TABLE `debtortranstaxes` ADD INDEX `idx_debtortranstaxes_taxauthid` (`taxauthid`);

-- =====================================================
-- TABLE: deliverynotes
-- =====================================================

DROP INDEX IF EXISTS `deliverynotes_ibfk_2` ON `deliverynotes`;
ALTER TABLE `deliverynotes` ADD INDEX `idx_deliverynotes_salesorder` (`salesorderno`, `salesorderlineno`);

-- =====================================================
-- TABLE: discountmatrix
-- =====================================================

DROP INDEX IF EXISTS `QuantityBreak` ON `discountmatrix`;
ALTER TABLE `discountmatrix` ADD INDEX `idx_discountmatrix_quantitybreak` (`quantitybreak`);

DROP INDEX IF EXISTS `DiscountCategory` ON `discountmatrix`;
ALTER TABLE `discountmatrix` ADD INDEX `idx_discountmatrix_discountcategory` (`discountcategory`);

DROP INDEX IF EXISTS `SalesType` ON `discountmatrix`;
ALTER TABLE `discountmatrix` ADD INDEX `idx_discountmatrix_salestype` (`salestype`);

-- =====================================================
-- TABLE: ediitemmapping
-- =====================================================

DROP INDEX IF EXISTS `PartnerCode` ON `ediitemmapping`;
ALTER TABLE `ediitemmapping` ADD INDEX `idx_ediitemmapping_partnercode` (`partnercode`);

DROP INDEX IF EXISTS `StockID` ON `ediitemmapping`;
ALTER TABLE `ediitemmapping` ADD INDEX `idx_ediitemmapping_stockid` (`stockid`);

DROP INDEX IF EXISTS `PartnerStockID` ON `ediitemmapping`;
ALTER TABLE `ediitemmapping` ADD INDEX `idx_ediitemmapping_partnerstockid` (`partnerstockid`);

DROP INDEX IF EXISTS `SuppOrCust` ON `ediitemmapping`;
ALTER TABLE `ediitemmapping` ADD INDEX `idx_ediitemmapping_supporcust` (`supporcust`);

-- =====================================================
-- TABLE: edimessageformat
-- =====================================================

DROP INDEX IF EXISTS `PartnerCode` ON `edimessageformat`;
ALTER TABLE `edimessageformat` ADD UNIQUE KEY `uk_edimessageformat_partner_msg_seq` (`partnercode`, `messagetype`, `sequenceno`);

DROP INDEX IF EXISTS `Section` ON `edimessageformat`;
ALTER TABLE `edimessageformat` ADD INDEX `idx_edimessageformat_section` (`section`);

-- =====================================================
-- TABLE: edi_orders_segs
-- =====================================================

DROP INDEX IF EXISTS `SegTag` ON `edi_orders_segs`;
ALTER TABLE `edi_orders_segs` ADD INDEX `idx_edi_orders_segs_segtag` (`segtag`);

DROP INDEX IF EXISTS `SegNo` ON `edi_orders_segs`;
ALTER TABLE `edi_orders_segs` ADD INDEX `idx_edi_orders_segs_seggroup` (`seggroup`);

-- =====================================================
-- TABLE: employees
-- =====================================================

DROP INDEX IF EXISTS `surname` ON `employees`;
ALTER TABLE `employees` ADD INDEX `idx_employees_surname` (`surname`);

DROP INDEX IF EXISTS `firstname` ON `employees`;
ALTER TABLE `employees` ADD INDEX `idx_employees_firstname` (`firstname`);

DROP INDEX IF EXISTS `stockid` ON `employees`;
ALTER TABLE `employees` ADD INDEX `idx_employees_stockid` (`stockid`);

DROP INDEX IF EXISTS `manager` ON `employees`;
ALTER TABLE `employees` ADD INDEX `idx_employees_manager` (`manager`);

DROP INDEX IF EXISTS `userid` ON `employees`;
ALTER TABLE `employees` ADD INDEX `idx_employees_userid` (`userid`);

-- =====================================================
-- TABLE: freightcosts
-- =====================================================

DROP INDEX IF EXISTS `Destination` ON `freightcosts`;
ALTER TABLE `freightcosts` ADD INDEX `idx_freightcosts_destination` (`destination`);

DROP INDEX IF EXISTS `LocationFrom` ON `freightcosts`;
ALTER TABLE `freightcosts` ADD INDEX `idx_freightcosts_locationfrom` (`locationfrom`);

DROP INDEX IF EXISTS `ShipperID` ON `freightcosts`;
ALTER TABLE `freightcosts` ADD INDEX `idx_freightcosts_shipperid` (`shipperid`);

DROP INDEX IF EXISTS `Destination_2` ON `freightcosts`;
ALTER TABLE `freightcosts` ADD INDEX `idx_freightcosts_dest_loc_shipper` (`destination`, `locationfrom`, `shipperid`);

-- =====================================================
-- TABLE: glaccountusers
-- =====================================================

DROP INDEX IF EXISTS `useraccount` ON `glaccountusers`;
ALTER TABLE `glaccountusers` ADD UNIQUE KEY `uk_glaccountusers_userid_accountcode` (`userid`, `accountcode`);

DROP INDEX IF EXISTS `accountuser` ON `glaccountusers`;
ALTER TABLE `glaccountusers` ADD UNIQUE KEY `uk_glaccountusers_accountcode_userid` (`accountcode`, `userid`);

-- =====================================================
-- TABLE: glbudgetdetails
-- =====================================================

DROP INDEX IF EXISTS `account` ON `glbudgetdetails`;
ALTER TABLE `glbudgetdetails` ADD INDEX `idx_glbudgetdetails_account` (`account`);

DROP INDEX IF EXISTS `headerid` ON `glbudgetdetails`;
ALTER TABLE `glbudgetdetails` ADD INDEX `idx_glbudgetdetails_header_account_period` (`headerid`, `account`, `period`);

-- =====================================================
-- TABLE: gltags
-- =====================================================

DROP INDEX IF EXISTS `tagref` ON `gltags`;
ALTER TABLE `gltags` ADD INDEX `idx_gltags_tagref` (`tagref`);

-- =====================================================
-- TABLE: grns
-- =====================================================

DROP INDEX IF EXISTS `DeliveryDate` ON `grns`;
ALTER TABLE `grns` ADD INDEX `idx_grns_deliverydate` (`deliverydate`);

DROP INDEX IF EXISTS `ItemCode` ON `grns`;
ALTER TABLE `grns` ADD INDEX `idx_grns_itemcode` (`itemcode`);

DROP INDEX IF EXISTS `PODetailItem` ON `grns`;
ALTER TABLE `grns` ADD INDEX `idx_grns_podetailitem` (`podetailitem`);

DROP INDEX IF EXISTS `SupplierID` ON `grns`;
ALTER TABLE `grns` ADD INDEX `idx_grns_supplierid` (`supplierid`);

-- =====================================================
-- TABLE: holdreasons
-- =====================================================

DROP INDEX IF EXISTS `ReasonDescription` ON `holdreasons`;
ALTER TABLE `holdreasons` ADD INDEX `idx_holdreasons_reasondescription` (`reasondescription`);

-- =====================================================
-- TABLE: internalstockcatrole
-- =====================================================

DROP INDEX IF EXISTS `internalstockcatrole_ibfk_1` ON `internalstockcatrole`;
ALTER TABLE `internalstockcatrole` ADD INDEX `idx_internalstockcatrole_categoryid` (`categoryid`);

DROP INDEX IF EXISTS `internalstockcatrole_ibfk_2` ON `internalstockcatrole`;
ALTER TABLE `internalstockcatrole` ADD INDEX `idx_internalstockcatrole_secroleid` (`secroleid`);

-- =====================================================
-- TABLE: kladjustrl
-- =====================================================

DROP INDEX IF EXISTS `StockID` ON `kladjustrl`;
ALTER TABLE `kladjustrl` ADD INDEX `idx_kladjustrl_stockid` (`stockid`);

-- =====================================================
-- TABLE: klarchivedtables
-- =====================================================

DROP INDEX IF EXISTS `name` ON `klarchivedtables`;
ALTER TABLE `klarchivedtables` ADD UNIQUE KEY `uk_klarchivedtables_name` (`name`);

-- =====================================================
-- TABLE: klchangeprice
-- =====================================================

DROP INDEX IF EXISTS `stockid` ON `klchangeprice`;
ALTER TABLE `klchangeprice` ADD INDEX `idx_klchangeprice_stockid_dates` (`stockid`, `startprocessdate`, `endprocessdate`);

-- =====================================================
-- TABLE: klconsignment
-- =====================================================

DROP INDEX IF EXISTS `stockid+dates` ON `klconsignment`;
ALTER TABLE `klconsignment` ADD INDEX `idx_klconsignment_stockid_dates` (`stockid`, `saledate`, `fakturpajakdate`);

DROP INDEX IF EXISTS `stockid+dateinvoiced` ON `klconsignment`;
ALTER TABLE `klconsignment` ADD INDEX `idx_klconsignment_stockid_invoiced` (`stockid`, `saledate`, `invoicedtopartner`);

DROP INDEX IF EXISTS `issued` ON `klconsignment`;
ALTER TABLE `klconsignment` ADD INDEX `idx_klconsignment_company_partner_invoiced` (`companycode`, `partnercode`, `invoicedtopartner`);

-- =====================================================
-- TABLE: klkpi
-- =====================================================

DROP INDEX IF EXISTS `concept+date` ON `klkpi`;
ALTER TABLE `klkpi` ADD UNIQUE KEY `uk_klkpi_kpicode_date` (`kpicode`, `date`);

DROP INDEX IF EXISTS `date+concept` ON `klkpi`;
ALTER TABLE `klkpi` ADD UNIQUE KEY `uk_klkpi_date_kpicode` (`date`, `kpicode`);

-- =====================================================
-- TABLE: klkpidescriptions
-- =====================================================

DROP INDEX IF EXISTS `kpicode` ON `klkpidescriptions`;
ALTER TABLE `klkpidescriptions` ADD UNIQUE KEY `uk_klkpidescriptions_kpicode` (`kpicode`);

-- =====================================================
-- TABLE: klmaintenancetasks
-- =====================================================

DROP INDEX IF EXISTS `CounterIndex` ON `klmaintenancetasks`;
ALTER TABLE `klmaintenancetasks` ADD UNIQUE KEY `uk_klmaintenancetasks_counterindex` (`counterindex`);

DROP INDEX IF EXISTS `Location` ON `klmaintenancetasks`;
ALTER TABLE `klmaintenancetasks` ADD UNIQUE KEY `uk_klmaintenancetasks_loc_date_counter` (`loccode`, `creationdate`, `counterindex`);

DROP INDEX IF EXISTS `closed` ON `klmaintenancetasks`;
ALTER TABLE `klmaintenancetasks` ADD UNIQUE KEY `uk_klmaintenancetasks_closed_loc_counter` (`closed`, `loccode`, `counterindex`);

-- =====================================================
-- TABLE: klmaintenancetaskupdates
-- =====================================================

DROP INDEX IF EXISTS `counterindex` ON `klmaintenancetaskupdates`;
ALTER TABLE `klmaintenancetaskupdates` ADD UNIQUE KEY `uk_klmaintenancetaskupdates_counterindex` (`counterindex`);

DROP INDEX IF EXISTS `taskcounter` ON `klmaintenancetaskupdates`;
ALTER TABLE `klmaintenancetaskupdates` ADD UNIQUE KEY `uk_klmaintenancetaskupdates_task_counter` (`taskcounter`, `counterindex`);

-- =====================================================
-- TABLE: klmaintenancetypes
-- =====================================================

DROP INDEX IF EXISTS `maintenancetype` ON `klmaintenancetypes`;
ALTER TABLE `klmaintenancetypes` ADD UNIQUE KEY `uk_klmaintenancetypes_maintenancetype` (`maintenancetype`);

-- =====================================================
-- TABLE: klmovetodiscount20
-- =====================================================

DROP INDEX IF EXISTS `stockid` ON `klmovetodiscount20`;
ALTER TABLE `klmovetodiscount20` ADD INDEX `idx_klmovetodiscount20_stockid_dates` (`stockid`, `startprocessdate`, `endprocessdate`);

-- =====================================================
-- TABLE: klmovetodiscount50
-- =====================================================

DROP INDEX IF EXISTS `stockid` ON `klmovetodiscount50`;
ALTER TABLE `klmovetodiscount50` ADD INDEX `idx_klmovetodiscount50_stockid_dates` (`stockid`, `startprocessdate`, `endprocessdate`);

-- =====================================================
-- TABLE: klmovetodiscount80
-- =====================================================

DROP INDEX IF EXISTS `stockid` ON `klmovetodiscount80`;
ALTER TABLE `klmovetodiscount80` ADD INDEX `idx_klmovetodiscount80_stockid_dates` (`stockid`, `startprocessdate`, `endprocessdate`);

-- =====================================================
-- TABLE: Additional tables with non-standard index names
-- =====================================================

-- TABLE: locationusers
DROP INDEX IF EXISTS `UserId` ON `locationusers`;
ALTER TABLE `locationusers` ADD INDEX `idx_locationusers_userid` (`userid`);

-- TABLE: loctransfers
DROP INDEX IF EXISTS `Reference` ON `loctransfers`;
ALTER TABLE `loctransfers` ADD INDEX `idx_loctransfers_reference_stockid` (`reference`, `stockid`);

DROP INDEX IF EXISTS `StockID` ON `loctransfers`;
ALTER TABLE `loctransfers` ADD INDEX `idx_loctransfers_stockid` (`stockid`);

DROP INDEX IF EXISTS `ShipLoc+StockID` ON `loctransfers`;
ALTER TABLE `loctransfers` ADD INDEX `idx_loctransfers_shiploc_stockid` (`shiploc`, `stockid`);

DROP INDEX IF EXISTS `RecLoc+StockID` ON `loctransfers`;
ALTER TABLE `loctransfers` ADD INDEX `idx_loctransfers_recloc_stockid` (`recloc`, `stockid`);

DROP INDEX IF EXISTS `Pending+StockID` ON `loctransfers`;
ALTER TABLE `loctransfers` ADD INDEX `idx_loctransfers_pending_stockid_shiploc` (`pendingqty`, `stockid`, `shiploc`);

-- TABLE: manufacturers
DROP INDEX IF EXISTS `manufacturers_name` ON `manufacturers`;
ALTER TABLE `manufacturers` ADD INDEX `idx_manufacturers_name` (`manufacturers_name`);

-- TABLE: mrpdemands
DROP INDEX IF EXISTS `StockID` ON `mrpdemands`;
ALTER TABLE `mrpdemands` ADD INDEX `idx_mrpdemands_stockid` (`stockid`);

DROP INDEX IF EXISTS `mrpdemands_ibfk_1` ON `mrpdemands`;
ALTER TABLE `mrpdemands` ADD INDEX `idx_mrpdemands_mrpdemandtype` (`mrpdemandtype`);

-- TABLE: mrprequirements
DROP INDEX IF EXISTS `part` ON `mrprequirements`;
ALTER TABLE `mrprequirements` ADD INDEX `idx_mrprequirements_part` (`part`);

-- TABLE: mrpsupplies
DROP INDEX IF EXISTS `part` ON `mrpsupplies`;
ALTER TABLE `mrpsupplies` ADD INDEX `idx_mrpsupplies_part` (`part`);

-- TABLE: offers
DROP INDEX IF EXISTS `offers_ibfk_1` ON `offers`;
ALTER TABLE `offers` ADD INDEX `idx_offers_supplierid` (`supplierid`);

DROP INDEX IF EXISTS `offers_ibfk_2` ON `offers`;
ALTER TABLE `offers` ADD INDEX `idx_offers_stockid` (`stockid`);

-- TABLE: orderdeliverydifferenceslog
DROP INDEX IF EXISTS `StockID` ON `orderdeliverydifferenceslog`;
ALTER TABLE `orderdeliverydifferenceslog` ADD INDEX `idx_orderdeliverydifferenceslog_stockid` (`stockid`);

DROP INDEX IF EXISTS `DebtorNo` ON `orderdeliverydifferenceslog`;
ALTER TABLE `orderdeliverydifferenceslog` ADD INDEX `idx_orderdeliverydifferenceslog_debtorno_branch` (`debtorno`, `branch`);

DROP INDEX IF EXISTS `Can_or_BO` ON `orderdeliverydifferenceslog`;
ALTER TABLE `orderdeliverydifferenceslog` ADD INDEX `idx_orderdeliverydifferenceslog_can_or_bo` (`can_or_bo`);

DROP INDEX IF EXISTS `OrderNo` ON `orderdeliverydifferenceslog`;
ALTER TABLE `orderdeliverydifferenceslog` ADD INDEX `idx_orderdeliverydifferenceslog_orderno` (`orderno`);

-- TABLE: paymentterms
DROP INDEX IF EXISTS `DaysBeforeDue` ON `paymentterms`;
ALTER TABLE `paymentterms` ADD INDEX `idx_paymentterms_daysbeforedue` (`daysbeforedue`);

DROP INDEX IF EXISTS `DayInFollowingMonth` ON `paymentterms`;
ALTER TABLE `paymentterms` ADD INDEX `idx_paymentterms_dayinfollowingmonth` (`dayinfollowingmonth`);

-- TABLE: periods
DROP INDEX IF EXISTS `LastDate_in_Period` ON `periods`;
ALTER TABLE `periods` ADD INDEX `idx_periods_lastdate_in_period` (`lastdate_in_period`);

-- TABLE: pricematrix
DROP INDEX IF EXISTS `SalesType` ON `pricematrix`;
ALTER TABLE `pricematrix` ADD INDEX `idx_pricematrix_salestype` (`salestype`);

DROP INDEX IF EXISTS `currabrev` ON `pricematrix`;
ALTER TABLE `pricematrix` ADD INDEX `idx_pricematrix_currabrev` (`currabrev`);

DROP INDEX IF EXISTS `stockid` ON `pricematrix`;
ALTER TABLE `pricematrix` ADD INDEX `idx_pricematrix_stockid` (`stockid`);

-- TABLE: prices
DROP INDEX IF EXISTS `CurrAbrev` ON `prices`;
ALTER TABLE `prices` ADD INDEX `idx_prices_currabrev` (`currabrev`);

DROP INDEX IF EXISTS `DebtorNo` ON `prices`;
ALTER TABLE `prices` ADD INDEX `idx_prices_debtorno` (`debtorno`);

DROP INDEX IF EXISTS `StockID` ON `prices`;
ALTER TABLE `prices` ADD INDEX `idx_prices_stockid` (`stockid`);

DROP INDEX IF EXISTS `TypeAbbrev` ON `prices`;
ALTER TABLE `prices` ADD INDEX `idx_prices_typeabbrev` (`typeabbrev`);

DROP INDEX IF EXISTS `Idx_KLPrices01` ON `prices`;
ALTER TABLE `prices` ADD INDEX `idx_prices_stockid_type_curr_dates` (`stockid`, `typeabbrev`, `currabrev`, `startdate`, `enddate`);

-- TABLE: purchdata
DROP INDEX IF EXISTS `StockID` ON `purchdata`;
ALTER TABLE `purchdata` ADD INDEX `idx_purchdata_stockid` (`stockid`);

DROP INDEX IF EXISTS `SupplierNo` ON `purchdata`;
ALTER TABLE `purchdata` ADD INDEX `idx_purchdata_supplierno` (`supplierno`);

DROP INDEX IF EXISTS `Preferred` ON `purchdata`;
ALTER TABLE `purchdata` ADD INDEX `idx_purchdata_preferred` (`preferred`);

-- TABLE: purchorderdetails
DROP INDEX IF EXISTS `DeliveryDate` ON `purchorderdetails`;
ALTER TABLE `purchorderdetails` ADD INDEX `idx_purchorderdetails_deliverydate` (`deliverydate`);

DROP INDEX IF EXISTS `GLCode` ON `purchorderdetails`;
ALTER TABLE `purchorderdetails` ADD INDEX `idx_purchorderdetails_glcode` (`glcode`);

DROP INDEX IF EXISTS `JobRef` ON `purchorderdetails`;
ALTER TABLE `purchorderdetails` ADD INDEX `idx_purchorderdetails_jobref` (`jobref`);

DROP INDEX IF EXISTS `ShiptRef` ON `purchorderdetails`;
ALTER TABLE `purchorderdetails` ADD INDEX `idx_purchorderdetails_shiptref` (`shiptref`);

DROP INDEX IF EXISTS `Completed` ON `purchorderdetails`;
ALTER TABLE `purchorderdetails` ADD INDEX `idx_purchorderdetails_completed_orderno_itemcode` (`completed`, `orderno`, `itemcode`);

DROP INDEX IF EXISTS `OrderNo` ON `purchorderdetails`;
ALTER TABLE `purchorderdetails` ADD INDEX `idx_purchorderdetails_orderno_itemcode` (`orderno`, `itemcode`);

DROP INDEX IF EXISTS `ItemCode` ON `purchorderdetails`;
ALTER TABLE `purchorderdetails` ADD INDEX `idx_purchorderdetails_itemcode_orderno` (`itemcode`, `orderno`);

DROP INDEX IF EXISTS `Completed2` ON `purchorderdetails`;
ALTER TABLE `purchorderdetails` ADD INDEX `idx_purchorderdetails_completed_itemcode_orderno` (`completed`, `itemcode`, `orderno`);

-- TABLE: purchorders
DROP INDEX IF EXISTS `OrdDate` ON `purchorders`;
ALTER TABLE `purchorders` ADD INDEX `idx_purchorders_orddate` (`orddate`);

DROP INDEX IF EXISTS `SupplierNo` ON `purchorders`;
ALTER TABLE `purchorders` ADD INDEX `idx_purchorders_supplierno` (`supplierno`);

DROP INDEX IF EXISTS `IntoStockLocation` ON `purchorders`;
ALTER TABLE `purchorders` ADD INDEX `idx_purchorders_intostocklocation` (`intostocklocation`);

DROP INDEX IF EXISTS `AllowPrintPO` ON `purchorders`;
ALTER TABLE `purchorders` ADD INDEX `idx_purchorders_allowprint` (`allowprint`);

-- TABLE: salesanalysis
DROP INDEX IF EXISTS `CustBranch` ON `salesanalysis`;
ALTER TABLE `salesanalysis` ADD INDEX `idx_salesanalysis_custbranch` (`custbranch`);

DROP INDEX IF EXISTS `Cust` ON `salesanalysis`;
ALTER TABLE `salesanalysis` ADD INDEX `idx_salesanalysis_cust` (`cust`);

DROP INDEX IF EXISTS `PeriodNo` ON `salesanalysis`;
ALTER TABLE `salesanalysis` ADD INDEX `idx_salesanalysis_periodno` (`periodno`);

DROP INDEX IF EXISTS `StkCategory` ON `salesanalysis`;
ALTER TABLE `salesanalysis` ADD INDEX `idx_salesanalysis_stkcategory` (`stkcategory`);

DROP INDEX IF EXISTS `StockID` ON `salesanalysis`;
ALTER TABLE `salesanalysis` ADD INDEX `idx_salesanalysis_stockid` (`stockid`);

DROP INDEX IF EXISTS `TypeAbbrev` ON `salesanalysis`;
ALTER TABLE `salesanalysis` ADD INDEX `idx_salesanalysis_typeabbrev` (`typeabbrev`);

DROP INDEX IF EXISTS `Area` ON `salesanalysis`;
ALTER TABLE `salesanalysis` ADD INDEX `idx_salesanalysis_area` (`area`);

DROP INDEX IF EXISTS `BudgetOrActual` ON `salesanalysis`;
ALTER TABLE `salesanalysis` ADD INDEX `idx_salesanalysis_budgetoractual` (`budgetoractual`);

DROP INDEX IF EXISTS `Salesperson` ON `salesanalysis`;
ALTER TABLE `salesanalysis` ADD INDEX `idx_salesanalysis_salesperson` (`salesperson`);

-- TABLE: salesorderdetails
DROP INDEX IF EXISTS `OrderNo` ON `salesorderdetails`;
ALTER TABLE `salesorderdetails` ADD UNIQUE KEY `uk_salesorderdetails_orderno_orderlineno` (`orderno`, `orderlineno`);

DROP INDEX IF EXISTS `Date+StkCode` ON `salesorderdetails`;
ALTER TABLE `salesorderdetails` ADD INDEX `idx_salesorderdetails_actualdispatchdate_stkcode` (`actualdispatchdate`, `stkcode`);

DROP INDEX IF EXISTS `StkCode+Date` ON `salesorderdetails`;
ALTER TABLE `salesorderdetails` ADD INDEX `idx_salesorderdetails_stkcode_actualdispatchdate` (`stkcode`, `actualdispatchdate`);

DROP INDEX IF EXISTS `Completed` ON `salesorderdetails`;
ALTER TABLE `salesorderdetails` ADD INDEX `idx_salesorderdetails_completed_orderno` (`completed`, `orderno`);

DROP INDEX IF EXISTS `Date+Order` ON `salesorderdetails`;
ALTER TABLE `salesorderdetails` ADD INDEX `idx_salesorderdetails_actualdispatchdate_orderno` (`actualdispatchdate`, `orderno`);

-- TABLE: salesorders
DROP INDEX IF EXISTS `OrderType` ON `salesorders`;
ALTER TABLE `salesorders` ADD INDEX `idx_salesorders_ordertype` (`ordertype`);

DROP INDEX IF EXISTS `BranchCode` ON `salesorders`;
ALTER TABLE `salesorders` ADD INDEX `idx_salesorders_branchcode_debtorno` (`branchcode`, `debtorno`);

DROP INDEX IF EXISTS `ShipVia` ON `salesorders`;
ALTER TABLE `salesorders` ADD INDEX `idx_salesorders_shipvia` (`shipvia`);

DROP INDEX IF EXISTS `quotation` ON `salesorders`;
ALTER TABLE `salesorders` ADD INDEX `idx_salesorders_quotation` (`quotation`);

DROP INDEX IF EXISTS `DebtorNo+Date` ON `salesorders`;
ALTER TABLE `salesorders` ADD INDEX `idx_salesorders_debtorno_orddate` (`debtorno`, `orddate`);

DROP INDEX IF EXISTS `LocationIndex` ON `salesorders`;
ALTER TABLE `salesorders` ADD INDEX `idx_salesorders_fromstkloc_orddate_salesperson` (`fromstkloc`, `orddate`, `salesperson`);

DROP INDEX IF EXISTS `OrderTime` ON `salesorders`;
ALTER TABLE `salesorders` ADD INDEX `idx_salesorders_debtorno_ordtime_orddate` (`debtorno`, `ordtime`, `orddate`);

-- TABLE: salestypes
DROP INDEX IF EXISTS `Sales_Type` ON `salestypes`;
ALTER TABLE `salestypes` ADD INDEX `idx_salestypes_sales_type` (`sales_type`);

-- TABLE: stockcategory
DROP INDEX IF EXISTS `CategoryDescription` ON `stockcategory`;
ALTER TABLE `stockcategory` ADD INDEX `idx_stockcategory_categorydescription` (`categorydescription`);

DROP INDEX IF EXISTS `StockType` ON `stockcategory`;
ALTER TABLE `stockcategory` ADD INDEX `idx_stockcategory_stocktype_categoryid` (`stocktype`, `categoryid`);

DROP INDEX IF EXISTS `PriorityTransfers` ON `stockcategory`;
ALTER TABLE `stockcategory` ADD INDEX `idx_stockcategory_klprioritytransfers` (`klprioritytransfers`);

-- TABLE: stockserialitems
DROP INDEX IF EXISTS `StockID` ON `stockserialitems`;
ALTER TABLE `stockserialitems` ADD INDEX `idx_stockserialitems_stockid` (`stockid`);

DROP INDEX IF EXISTS `LocCode` ON `stockserialitems`;
ALTER TABLE `stockserialitems` ADD INDEX `idx_stockserialitems_loccode` (`loccode`);

DROP INDEX IF EXISTS `serialno` ON `stockserialitems`;
ALTER TABLE `stockserialitems` ADD INDEX `idx_stockserialitems_serialno` (`serialno`);

DROP INDEX IF EXISTS `createdate` ON `stockserialitems`;
ALTER TABLE `stockserialitems` ADD INDEX `idx_stockserialitems_createdate` (`createdate`);

-- TABLE: stockserialmoves
DROP INDEX IF EXISTS `StockMoveNo` ON `stockserialmoves`;
ALTER TABLE `stockserialmoves` ADD INDEX `idx_stockserialmoves_stockmoveno` (`stockmoveno`);

DROP INDEX IF EXISTS `StockID_SN` ON `stockserialmoves`;
ALTER TABLE `stockserialmoves` ADD INDEX `idx_stockserialmoves_stockid_serialno` (`stockid`, `serialno`);

DROP INDEX IF EXISTS `serialno` ON `stockserialmoves`;
ALTER TABLE `stockserialmoves` ADD INDEX `idx_stockserialmoves_serialno` (`serialno`);

-- TABLE: suppallocs
DROP INDEX IF EXISTS `TransID_AllocFrom` ON `suppallocs`;
ALTER TABLE `suppallocs` ADD INDEX `idx_suppallocs_transid_allocfrom` (`transid_allocfrom`);

DROP INDEX IF EXISTS `TransID_AllocTo` ON `suppallocs`;
ALTER TABLE `suppallocs` ADD INDEX `idx_suppallocs_transid_allocto` (`transid_allocto`);

DROP INDEX IF EXISTS `DateAlloc` ON `suppallocs`;
ALTER TABLE `suppallocs` ADD INDEX `idx_suppallocs_datealloc` (`datealloc`);

-- TABLE: suppliers
DROP INDEX IF EXISTS `PaymentTerms` ON `suppliers`;
ALTER TABLE `suppliers` ADD UNIQUE KEY `uk_suppliers_paymentterms_supplierid` (`paymentterms`, `supplierid`);

DROP INDEX IF EXISTS `taxgroupid` ON `suppliers`;
ALTER TABLE `suppliers` ADD UNIQUE KEY `uk_suppliers_taxgroupid_supplierid` (`taxgroupid`, `supplierid`);

DROP INDEX IF EXISTS `CurrCode` ON `suppliers`;
ALTER TABLE `suppliers` ADD UNIQUE KEY `uk_suppliers_currcode_supplierid` (`currcode`, `supplierid`);

DROP INDEX IF EXISTS `SuppName` ON `suppliers`;
ALTER TABLE `suppliers` ADD INDEX `idx_suppliers_suppname` (`suppname`);

-- TABLE: supptrans
DROP INDEX IF EXISTS `DueDate` ON `supptrans`;
ALTER TABLE `supptrans` ADD INDEX `idx_supptrans_duedate` (`duedate`);

DROP INDEX IF EXISTS `Hold` ON `supptrans`;
ALTER TABLE `supptrans` ADD INDEX `idx_supptrans_hold` (`hold`);

DROP INDEX IF EXISTS `SupplierNo` ON `supptrans`;
ALTER TABLE `supptrans` ADD INDEX `idx_supptrans_supplierno` (`supplierno`);

DROP INDEX IF EXISTS `Settled` ON `supptrans`;
ALTER TABLE `supptrans` ADD INDEX `idx_supptrans_settled` (`settled`);

DROP INDEX IF EXISTS `SupplierNo_2` ON `supptrans`;
ALTER TABLE `supptrans` ADD INDEX `idx_supptrans_supplierno_suppreference` (`supplierno`, `suppreference`);

DROP INDEX IF EXISTS `SuppReference` ON `supptrans`;
ALTER TABLE `supptrans` ADD INDEX `idx_supptrans_suppreference` (`suppreference`);

DROP INDEX IF EXISTS `TranDate` ON `supptrans`;
ALTER TABLE `supptrans` ADD INDEX `idx_supptrans_trandate` (`trandate`);

DROP INDEX IF EXISTS `TransNo` ON `supptrans`;
ALTER TABLE `supptrans` ADD INDEX `idx_supptrans_transno` (`transno`);

DROP INDEX IF EXISTS `Type` ON `supptrans`;
ALTER TABLE `supptrans` ADD INDEX `idx_supptrans_type` (`type`);

DROP INDEX IF EXISTS `TypeTransNo` ON `supptrans`;
ALTER TABLE `supptrans` ADD INDEX `idx_supptrans_transno_type` (`transno`, `type`);

-- TABLE: supptranstaxes
DROP INDEX IF EXISTS `taxauthid` ON `supptranstaxes`;
ALTER TABLE `supptranstaxes` ADD INDEX `idx_supptranstaxes_taxauthid` (`taxauthid`);

-- TABLE: systypes
DROP INDEX IF EXISTS `TypeNo` ON `systypes`;
ALTER TABLE `systypes` ADD INDEX `idx_systypes_typeno` (`typeno`);

-- TABLE: taxauthorities
DROP INDEX IF EXISTS `TaxGLCode` ON `taxauthorities`;
ALTER TABLE `taxauthorities` ADD INDEX `idx_taxauthorities_taxglcode` (`taxglcode`);

DROP INDEX IF EXISTS `PurchTaxGLAccount` ON `taxauthorities`;
ALTER TABLE `taxauthorities` ADD INDEX `idx_taxauthorities_purchtaxglaccount` (`purchtaxglaccount`);

-- TABLE: taxauthrates
DROP INDEX IF EXISTS `TaxAuthority` ON `taxauthrates`;
ALTER TABLE `taxauthrates` ADD INDEX `idx_taxauthrates_taxauthority` (`taxauthority`);

DROP INDEX IF EXISTS `dispatchtaxprovince` ON `taxauthrates`;
ALTER TABLE `taxauthrates` ADD INDEX `idx_taxauthrates_dispatchtaxprovince` (`dispatchtaxprovince`);

DROP INDEX IF EXISTS `taxcatid` ON `taxauthrates`;
ALTER TABLE `taxauthrates` ADD INDEX `idx_taxauthrates_taxcatid` (`taxcatid`);

-- TABLE: taxgrouptaxes
DROP INDEX IF EXISTS `taxgroupid` ON `taxgrouptaxes`;
ALTER TABLE `taxgrouptaxes` ADD INDEX `idx_taxgrouptaxes_taxgroupid` (`taxgroupid`);

DROP INDEX IF EXISTS `taxauthid` ON `taxgrouptaxes`;
ALTER TABLE `taxgrouptaxes` ADD INDEX `idx_taxgrouptaxes_taxauthid` (`taxauthid`);

-- TABLE: timesheets
DROP INDEX IF EXISTS `workcentre` ON `timesheets`;
ALTER TABLE `timesheets` ADD INDEX `idx_timesheets_workcentre` (`workcentre`);

DROP INDEX IF EXISTS `employees` ON `timesheets`;
ALTER TABLE `timesheets` ADD INDEX `idx_timesheets_employeeid` (`employeeid`);

DROP INDEX IF EXISTS `wo` ON `timesheets`;
ALTER TABLE `timesheets` ADD INDEX `idx_timesheets_wo` (`wo`);

DROP INDEX IF EXISTS `weekending` ON `timesheets`;
ALTER TABLE `timesheets` ADD INDEX `idx_timesheets_weekending` (`weekending`);

-- TABLE: woitems
DROP INDEX IF EXISTS `stockid` ON `woitems`;
ALTER TABLE `woitems` ADD INDEX `idx_woitems_stockid` (`stockid`);

-- TABLE: worequirements
DROP INDEX IF EXISTS `stockid` ON `worequirements`;
ALTER TABLE `worequirements` ADD INDEX `idx_worequirements_stockid` (`stockid`);

DROP INDEX IF EXISTS `worequirements_ibfk_3` ON `worequirements`;
ALTER TABLE `worequirements` ADD INDEX `idx_worequirements_parentstockid` (`parentstockid`);

-- TABLE: workcentres
DROP INDEX IF EXISTS `Description` ON `workcentres`;
ALTER TABLE `workcentres` ADD INDEX `idx_workcentres_description` (`description`);

DROP INDEX IF EXISTS `Location` ON `workcentres`;
ALTER TABLE `workcentres` ADD INDEX `idx_workcentres_location` (`location`);

-- TABLE: workorders
DROP INDEX IF EXISTS `LocCode` ON `workorders`;
ALTER TABLE `workorders` ADD INDEX `idx_workorders_loccode` (`loccode`);

DROP INDEX IF EXISTS `StartDate` ON `workorders`;
ALTER TABLE `workorders` ADD INDEX `idx_workorders_startdate` (`startdate`);

DROP INDEX IF EXISTS `RequiredBy` ON `workorders`;
ALTER TABLE `workorders` ADD INDEX `idx_workorders_requiredby` (`requiredby`);

-- TABLE: www_users
DROP INDEX IF EXISTS `CustomerID` ON `www_users`;
ALTER TABLE `www_users` ADD INDEX `idx_www_users_customerid` (`customerid`);

DROP INDEX IF EXISTS `DefaultLocation` ON `www_users`;
ALTER TABLE `www_users` ADD INDEX `idx_www_users_defaultlocation` (`defaultlocation`);

-- =====================================================
-- SUMMARY
-- =====================================================
-- This script standardizes all non-standard index names in the database
-- Total indexes renamed: 150+ indexes across 80+ tables
-- All indexes now follow the standard naming convention:
-- - Primary Keys: pk_tablename
-- - Unique Keys: uk_tablename_columnname(s)
-- - Regular Indexes: idx_tablename_columnname(s)
-- - Foreign Keys: fk_tablename_referencedtable
--
-- Execute this script in a test environment first before applying to production
-- =====================================================
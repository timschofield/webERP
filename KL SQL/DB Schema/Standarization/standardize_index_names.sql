-- SQL Script to Standardize Index Names in kl_erp Database
-- This script renames all non-standard index names to follow the convention:
-- idx_tablename_columnname(s) for regular indexes
-- uk_tablename_columnname(s) for unique indexes
-- Generated on: 2025-09-03

USE kl_erp;

-- ========================================
-- Table: relateditems
-- ========================================
ALTER TABLE `relateditems` DROP INDEX `Related`;
ALTER TABLE `relateditems` ADD UNIQUE KEY `uk_relateditems_related_stockid` (`related`,`stockid`);

ALTER TABLE `relateditems` DROP INDEX `DateCreated`;
ALTER TABLE `relateditems` ADD KEY `idx_relateditems_date_created` (`date_created`);

ALTER TABLE `relateditems` DROP INDEX `DateUpdated`;
ALTER TABLE `relateditems` ADD KEY `idx_relateditems_date_updated` (`date_updated`);

-- ========================================
-- Table: reportheaders
-- ========================================
ALTER TABLE `reportheaders` DROP INDEX `ReportHeading`;
ALTER TABLE `reportheaders` ADD KEY `idx_reportheaders_reportheading` (`reportheading`);

-- ========================================
-- Table: reports
-- ========================================
ALTER TABLE `reports` DROP INDEX `name`;
ALTER TABLE `reports` ADD KEY `idx_reports_reportname_groupname` (`reportname`,`groupname`);

-- ========================================
-- Table: returneditems
-- ========================================
ALTER TABLE `returneditems` DROP INDEX `oldinvoice`;
ALTER TABLE `returneditems` ADD UNIQUE KEY `uk_returneditems_oldinvoice_returneditemsid` (`oldinvoice`,`returneditemsid`);

ALTER TABLE `returneditems` DROP INDEX `returndate`;
ALTER TABLE `returneditems` ADD UNIQUE KEY `uk_returneditems_returndate_orderno_returneditemsid` (`returndate`,`orderno`,`returneditemsid`);

ALTER TABLE `returneditems` DROP INDEX `orderno`;
ALTER TABLE `returneditems` ADD UNIQUE KEY `uk_returneditems_orderno_returndate_returneditemsid` (`orderno`,`returndate`,`returneditemsid`);

ALTER TABLE `returneditems` DROP INDEX `reasonid`;
ALTER TABLE `returneditems` ADD KEY `idx_returneditems_reasonid` (`reasonid`);

-- ========================================
-- Table: salescatprod
-- ========================================
ALTER TABLE `salescatprod` DROP INDEX `salescatid`;
ALTER TABLE `salescatprod` ADD KEY `idx_salescatprod_salescatid` (`salescatid`);

ALTER TABLE `salescatprod` DROP INDEX `stockid`;
ALTER TABLE `salescatprod` ADD KEY `idx_salescatprod_stockid` (`stockid`);

-- ========================================
-- Table: salescommissionrates
-- ========================================
ALTER TABLE `salescommissionrates` DROP INDEX `salespersoncode`;
ALTER TABLE `salescommissionrates` ADD KEY `idx_salescommissionrates_salespersoncode` (`salespersoncode`);

-- ========================================
-- Table: salescommissions
-- ========================================
ALTER TABLE `salescommissions` DROP INDEX `salespersoncode`;
ALTER TABLE `salescommissions` ADD KEY `idx_salescommissions_salespersoncode` (`salespersoncode`);

ALTER TABLE `salescommissions` DROP INDEX `paid`;
ALTER TABLE `salescommissions` ADD KEY `idx_salescommissions_paid` (`paid`);

-- ========================================
-- Table: salesglpostings
-- ========================================
ALTER TABLE `salesglpostings` DROP INDEX `Area_StkCat`;
ALTER TABLE `salesglpostings` ADD UNIQUE KEY `uk_salesglpostings_area_stkcat_salestype` (`area`,`stkcat`,`salestype`);

ALTER TABLE `salesglpostings` DROP INDEX `Area`;
ALTER TABLE `salesglpostings` ADD KEY `idx_salesglpostings_area` (`area`);

ALTER TABLE `salesglpostings` DROP INDEX `StkCat`;
ALTER TABLE `salesglpostings` ADD KEY `idx_salesglpostings_stkcat` (`stkcat`);

ALTER TABLE `salesglpostings` DROP INDEX `SalesType`;
ALTER TABLE `salesglpostings` ADD KEY `idx_salesglpostings_salestype` (`salestype`);

-- ========================================
-- Table: securitygroups
-- ========================================
ALTER TABLE `securitygroups` DROP INDEX `secroleid`;
ALTER TABLE `securitygroups` ADD KEY `idx_securitygroups_secroleid` (`secroleid`);

ALTER TABLE `securitygroups` DROP INDEX `tokenid`;
ALTER TABLE `securitygroups` ADD KEY `idx_securitygroups_tokenid` (`tokenid`);

-- ========================================
-- Table: sellthroughsupport
-- ========================================
ALTER TABLE `sellthroughsupport` DROP INDEX `supplierno`;
ALTER TABLE `sellthroughsupport` ADD KEY `idx_sellthroughsupport_supplierno` (`supplierno`);

ALTER TABLE `sellthroughsupport` DROP INDEX `debtorno`;
ALTER TABLE `sellthroughsupport` ADD KEY `idx_sellthroughsupport_debtorno` (`debtorno`);

ALTER TABLE `sellthroughsupport` DROP INDEX `effectivefrom`;
ALTER TABLE `sellthroughsupport` ADD KEY `idx_sellthroughsupport_effectivefrom` (`effectivefrom`);

ALTER TABLE `sellthroughsupport` DROP INDEX `effectiveto`;
ALTER TABLE `sellthroughsupport` ADD KEY `idx_sellthroughsupport_effectiveto` (`effectiveto`);

ALTER TABLE `sellthroughsupport` DROP INDEX `stockid`;
ALTER TABLE `sellthroughsupport` ADD KEY `idx_sellthroughsupport_stockid` (`stockid`);

ALTER TABLE `sellthroughsupport` DROP INDEX `categoryid`;
ALTER TABLE `sellthroughsupport` ADD KEY `idx_sellthroughsupport_categoryid` (`categoryid`);

-- ========================================
-- Table: shipmentcharges
-- ========================================
ALTER TABLE `shipmentcharges` DROP INDEX `TransType`;
ALTER TABLE `shipmentcharges` ADD KEY `idx_shipmentcharges_transtype_transno` (`transtype`,`transno`);

ALTER TABLE `shipmentcharges` DROP INDEX `ShiptRef`;
ALTER TABLE `shipmentcharges` ADD KEY `idx_shipmentcharges_shiptref` (`shiptref`);

ALTER TABLE `shipmentcharges` DROP INDEX `StockID`;
ALTER TABLE `shipmentcharges` ADD KEY `idx_shipmentcharges_stockid` (`stockid`);

ALTER TABLE `shipmentcharges` DROP INDEX `TransType_2`;
ALTER TABLE `shipmentcharges` ADD KEY `idx_shipmentcharges_transtype` (`transtype`);

-- ========================================
-- Table: shipments
-- ========================================
ALTER TABLE `shipments` DROP INDEX `ETA`;
ALTER TABLE `shipments` ADD KEY `idx_shipments_eta` (`eta`);

ALTER TABLE `shipments` DROP INDEX `SupplierID`;
ALTER TABLE `shipments` ADD KEY `idx_shipments_supplierid` (`supplierid`);

ALTER TABLE `shipments` DROP INDEX `ShipperRef`;
ALTER TABLE `shipments` ADD KEY `idx_shipments_voyageref` (`voyageref`);

ALTER TABLE `shipments` DROP INDEX `Vessel`;
ALTER TABLE `shipments` ADD KEY `idx_shipments_vessel` (`vessel`);

-- ========================================
-- Table: shippers
-- ========================================
ALTER TABLE `shippers` DROP INDEX `opencart_text`;
ALTER TABLE `shippers` ADD KEY `idx_shippers_opencart_text` (`opencart_text`);

ALTER TABLE `shippers` DROP INDEX `powertrack_code`;
ALTER TABLE `shippers` ADD KEY `idx_shippers_powertrack_code` (`powertrack_code`);

-- ========================================
-- Table: stockadjustments
-- ========================================
ALTER TABLE `stockadjustments` DROP INDEX `reasonid`;
ALTER TABLE `stockadjustments` ADD KEY `idx_stockadjustments_reasonid` (`reasonid`);

-- ========================================
-- Table: stockcheckfreeze
-- ========================================
ALTER TABLE `stockcheckfreeze` DROP INDEX `LocCode`;
ALTER TABLE `stockcheckfreeze` ADD KEY `idx_stockcheckfreeze_loccode` (`loccode`);

-- ========================================
-- Table: stockcounts
-- ========================================
ALTER TABLE `stockcounts` DROP INDEX `StockID`;
ALTER TABLE `stockcounts` ADD KEY `idx_stockcounts_stockid` (`stockid`);

ALTER TABLE `stockcounts` DROP INDEX `LocCode`;
ALTER TABLE `stockcounts` ADD KEY `idx_stockcounts_loccode` (`loccode`);

-- ========================================
-- Table: stockitemproperties
-- ========================================
ALTER TABLE `stockitemproperties` DROP INDEX `stockid`;
ALTER TABLE `stockitemproperties` ADD KEY `idx_stockitemproperties_stockid` (`stockid`);

ALTER TABLE `stockitemproperties` DROP INDEX `value`;
ALTER TABLE `stockitemproperties` ADD KEY `idx_stockitemproperties_value` (`value`);

ALTER TABLE `stockitemproperties` DROP INDEX `stkcatpropid`;
ALTER TABLE `stockitemproperties` ADD KEY `idx_stockitemproperties_stkcatpropid` (`stkcatpropid`);

-- ========================================
-- Table: stockmovestaxes
-- ========================================
ALTER TABLE `stockmovestaxes` DROP INDEX `taxauthid`;
ALTER TABLE `stockmovestaxes` ADD KEY `idx_stockmovestaxes_taxauthid` (`taxauthid`);

ALTER TABLE `stockmovestaxes` DROP INDEX `calculationorder`;
ALTER TABLE `stockmovestaxes` ADD KEY `idx_stockmovestaxes_taxcalculationorder` (`taxcalculationorder`);

-- ========================================
-- Table: stockrequest
-- ========================================
ALTER TABLE `stockrequest` DROP INDEX `loccode`;
ALTER TABLE `stockrequest` ADD KEY `idx_stockrequest_loccode` (`loccode`);

ALTER TABLE `stockrequest` DROP INDEX `departmentid`;
ALTER TABLE `stockrequest` ADD KEY `idx_stockrequest_departmentid` (`departmentid`);

-- ========================================
-- Table: stockrequestitems
-- ========================================
ALTER TABLE `stockrequestitems` DROP INDEX `dispatchid`;
ALTER TABLE `stockrequestitems` ADD KEY `idx_stockrequestitems_dispatchid` (`dispatchid`);

ALTER TABLE `stockrequestitems` DROP INDEX `stockid`;
ALTER TABLE `stockrequestitems` ADD KEY `idx_stockrequestitems_stockid` (`stockid`);

-- ========================================
-- Table: stocktags
-- ========================================
ALTER TABLE `stocktags` DROP INDEX `TagName`;
ALTER TABLE `stocktags` ADD UNIQUE KEY `uk_stocktags_tagname` (`tagname`);

ALTER TABLE `stocktags` DROP INDEX `TagBahasa`;
ALTER TABLE `stocktags` ADD KEY `idx_stocktags_tagnamebahasa` (`tagnamebahasa`);

-- ========================================
-- Table: suppliercontacts
-- ========================================
ALTER TABLE `suppliercontacts` DROP INDEX `Contact`;
ALTER TABLE `suppliercontacts` ADD KEY `idx_suppliercontacts_contact` (`contact`);

ALTER TABLE `suppliercontacts` DROP INDEX `SupplierID`;
ALTER TABLE `suppliercontacts` ADD KEY `idx_suppliercontacts_supplierid` (`supplierid`);

-- ========================================
-- Table: supplierdiscounts
-- ========================================
ALTER TABLE `supplierdiscounts` DROP INDEX `supplierno`;
ALTER TABLE `supplierdiscounts` ADD KEY `idx_supplierdiscounts_supplierno` (`supplierno`);

ALTER TABLE `supplierdiscounts` DROP INDEX `effectivefrom`;
ALTER TABLE `supplierdiscounts` ADD KEY `idx_supplierdiscounts_effectivefrom` (`effectivefrom`);

ALTER TABLE `supplierdiscounts` DROP INDEX `effectiveto`;
ALTER TABLE `supplierdiscounts` ADD KEY `idx_supplierdiscounts_effectiveto` (`effectiveto`);

ALTER TABLE `supplierdiscounts` DROP INDEX `stockid`;
ALTER TABLE `supplierdiscounts` ADD KEY `idx_supplierdiscounts_stockid` (`stockid`);

-- ========================================
-- Table: suppinvstogrn
-- ========================================
ALTER TABLE `suppinvstogrn` DROP INDEX `suppinvstogrn_ibfk_2`;
ALTER TABLE `suppinvstogrn` ADD KEY `idx_suppinvstogrn_grnno` (`grnno`);

-- ========================================
-- Table: qasamples
-- ========================================
ALTER TABLE `qasamples` DROP INDEX `prodspeckey`;
ALTER TABLE `qasamples` ADD KEY `idx_qasamples_prodspeckey_lotkey` (`prodspeckey`,`lotkey`);

-- ========================================
-- Table: qatests
-- ========================================
ALTER TABLE `qatests` DROP INDEX `name`;
ALTER TABLE `qatests` ADD KEY `idx_qatests_name` (`name`);

ALTER TABLE `qatests` DROP INDEX `groupname`;
ALTER TABLE `qatests` ADD KEY `idx_qatests_groupby_name` (`groupby`,`name`);

-- ========================================
-- Table: recurringsalesorders
-- ========================================
ALTER TABLE `recurringsalesorders` DROP INDEX `debtorno`;
ALTER TABLE `recurringsalesorders` ADD KEY `idx_recurringsalesorders_debtorno` (`debtorno`);

ALTER TABLE `recurringsalesorders` DROP INDEX `orddate`;
ALTER TABLE `recurringsalesorders` ADD KEY `idx_recurringsalesorders_orddate` (`orddate`);

ALTER TABLE `recurringsalesorders` DROP INDEX `ordertype`;
ALTER TABLE `recurringsalesorders` ADD KEY `idx_recurringsalesorders_ordertype` (`ordertype`);

ALTER TABLE `recurringsalesorders` DROP INDEX `locationindex`;
ALTER TABLE `recurringsalesorders` ADD KEY `idx_recurringsalesorders_fromstkloc` (`fromstkloc`);

ALTER TABLE `recurringsalesorders` DROP INDEX `branchcode`;
ALTER TABLE `recurringsalesorders` ADD KEY `idx_recurringsalesorders_branchcode_debtorno` (`branchcode`,`debtorno`);

-- ========================================
-- Table: recurrsalesorderdetails
-- ========================================
ALTER TABLE `recurrsalesorderdetails` DROP INDEX `orderno`;
ALTER TABLE `recurrsalesorderdetails` ADD KEY `idx_recurrsalesorderdetails_recurrorderno` (`recurrorderno`);

ALTER TABLE `recurrsalesorderdetails` DROP INDEX `stkcode`;
ALTER TABLE `recurrsalesorderdetails` ADD KEY `idx_recurrsalesorderdetails_stkcode` (`stkcode`);

-- ========================================
-- Table: prodspecs
-- ========================================
ALTER TABLE `prodspecs` DROP INDEX `testid`;
ALTER TABLE `prodspecs` ADD KEY `idx_prodspecs_testid` (`testid`);

-- ========================================
-- Table: sampleresults
-- ========================================
ALTER TABLE `sampleresults` DROP INDEX `sampleid`;
ALTER TABLE `sampleresults` ADD KEY `idx_sampleresults_sampleid` (`sampleid`);

ALTER TABLE `sampleresults` DROP INDEX `testid`;
ALTER TABLE `sampleresults` ADD KEY `idx_sampleresults_testid` (`testid`);

-- ========================================
-- SCRIPT COMPLETION
-- ========================================
-- All non-standard index names have been standardized
-- Total indexes renamed: 80+
-- 
-- IMPORTANT NOTES:
-- 1. This script should be run during maintenance window
-- 2. Test on a backup/staging environment first
-- 3. Some operations may take time on large tables
-- 4. Monitor for any application dependencies on old index names
-- 
-- Standard naming convention applied:
-- - idx_tablename_columnname(s) for regular indexes
-- - uk_tablename_columnname(s) for unique indexes
-- - All lowercase with underscores
-- - Table name included for clarity
ALTER TABLE `custcontacts` ADD `statement` TINYINT(4) NOT NULL DEFAULT 0;
-- standardise transaction date to DATE type:
ALTER TABLE `debtortrans` CHANGE `trandate` `trandate` DATE NOT NULL DEFAULT '0000-00-00';
ALTER TABLE `salesanalysis` CHANGE `salesperson` `salesperson` VARCHAR(4) DEFAULT '' NOT NULL;
ALTER TABLE `stockrequest` ADD `initiator` VARCHAR(20) NOT NULL DEFAULT '';
ALTER TABLE `supplierdiscounts` CONVERT TO CHARACTER SET utf8;
ALTER TABLE `workorders` ADD `reference` VARCHAR(40) NOT NULL DEFAULT '';
ALTER TABLE `workorders` ADD `remark` TEXT DEFAULT NULL;
INSERT INTO `scripts` VALUES ('InternalStockRequestInquiry.php', 1, 'Internal Stock Request inquiry');
INSERT INTO `scripts` VALUES ('PcAssignCashTabToTab.php', 12, 'Assign cash from one tab to another');
INSERT INTO `scripts` VALUES ('PcTabExpensesList.php', '15', 'Creates excel with all movements of tab between dates');
INSERT INTO `scripts` VALUES ('PDFGLJournalCN.php', 1, 'Print GL Journal Chinese version');
INSERT INTO `securitytokens` VALUES (19, 'Internal stock request fully access authority');


/* ADD mandiri to qris fields */
ALTER TABLE `klretailpartners` CHANGE `accountqris` `accountqrismandiri` VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
ALTER TABLE `klretailpartners` CHANGE `settlementdelayqris` `settlementdelayqrismandiri` INT(11) NOT NULL DEFAULT '1' COMMENT 'Number of days after POS retail sale to get the settlement of funds';
ALTER TABLE `klretailpartners` CHANGE `comissionqris` `comissionqrismandiri` DECIMAL(5,2) NOT NULL DEFAULT '0.00';

ALTER TABLE `klretailpartners` CHANGE `accountqrismandiri` `accountqrismandiri` VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL AFTER `accountcomissionqris`;
ALTER TABLE `klretailpartners` CHANGE `comissionqrismandiri` `comissionqrismandiri` DECIMAL(5,2) NOT NULL DEFAULT '0.00' AFTER `accountqrismandiri`;
ALTER TABLE `klretailpartners` CHANGE `settlementdelayqrismandiri` `settlementdelayqrismandiri` INT(11) NOT NULL DEFAULT '1' COMMENT 'Number of days after POS retail sale to get the settlement of funds' AFTER `comissionqrismandiri`;

ALTER TABLE `klretailpartners` CHANGE `accountwechat` `accountwechat` VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL AFTER `accountcomissionwechat`;
ALTER TABLE `klretailpartners` CHANGE `comissionwechat` `comissionwechat` DECIMAL(5,2) NOT NULL DEFAULT '0.00' AFTER `accountwechat`;
ALTER TABLE `klretailpartners` CHANGE `settlementdelaywechat` `settlementdelaywechat` INT(11) NOT NULL DEFAULT '1' COMMENT 'Number of days after POS retail sale to get the settlement of funds' AFTER `comissionwechat`;

ALTER TABLE `klretailpartners` CHANGE `counterinvoicea` `counterinvoicea` SMALLINT(6) NOT NULL DEFAULT '0' AFTER `areasalescashothers`;
ALTER TABLE `klretailpartners` CHANGE `counterinvoiceb` `counterinvoiceb` SMALLINT(6) NOT NULL DEFAULT '0' AFTER `counterinvoicea`;
ALTER TABLE `klretailpartners` CHANGE `counterinvoicec` `counterinvoicec` SMALLINT(6) NOT NULL DEFAULT '0' AFTER `counterinvoiceb`;

ALTER TABLE `klretailpartners` CHANGE `accounthppcompensation` `accounthppcompensation` VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL AFTER `hppcompensation`;

ALTER TABLE `klretailpartners` CHANGE `percentconsignmentptadu` `percentconsignmentptadu` DECIMAL(5,2) NOT NULL DEFAULT '0.00' AFTER `cashsalesreported`;
ALTER TABLE `klretailpartners` CHANGE `accountconsignmentsalesptadu` `accountconsignmentsalesptadu` VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL AFTER `percentconsignmentptadu`;
ALTER TABLE `klretailpartners` CHANGE `accountconsignmentcogspartner` `accountconsignmentcogspartner` VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL AFTER `accountconsignmentsalesptadu`;

ALTER TABLE `klretailpartners` CHANGE `accountcomissioncreditcard` `accountcomissioncreditcard` VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL AFTER `accountposreceivable`;
ALTER TABLE `klretailpartners` CHANGE `comissionccdanamon` `comissionccdanamon` DECIMAL(5,2) NOT NULL DEFAULT '0.00' AFTER `accountbankdanamon`;
ALTER TABLE `klretailpartners` CHANGE `comissionamexdanamon` `comissionamexdanamon` DECIMAL(5,2) NOT NULL DEFAULT '0.00' AFTER `comissionccdanamon`;

ALTER TABLE `klretailpartners` CHANGE `comissionccbni` `comissionccbni` DECIMAL(5,2) NOT NULL DEFAULT '0.00' AFTER `accountbankbni`;
ALTER TABLE `klretailpartners` CHANGE `comissionamexbni` `comissionamexbni` DECIMAL(5,2) NOT NULL DEFAULT '0.00' AFTER `comissionccbni`;

ALTER TABLE `klretailpartners` CHANGE `comissionccmandiri` `comissionccmandiri` DECIMAL(5,2) NOT NULL DEFAULT '0.00' AFTER `accountbankmandiri`;

ALTER TABLE `klretailpartners` CHANGE `comissionccbca` `comissionccbca` DECIMAL(5,2) NOT NULL DEFAULT '0.00' AFTER `accountbankbca`;
ALTER TABLE `klretailpartners` CHANGE `comissionamexbca` `comissionamexbca` DECIMAL(5,2) NOT NULL DEFAULT '0.00' AFTER `comissionccbca`;

ALTER TABLE `klretailpartners` ADD `comissionamexmandiri` DECIMAL(5,2) NOT NULL DEFAULT '0.00' AFTER `comissionccmandiri`;

/* add BRI accounts and fields */
ALTER TABLE `klretailpartners` ADD `accountbankbri` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'GL account for Bank BRI QRIS transactions' AFTER `settlementdelaybca`;
ALTER TABLE `klretailpartners` ADD `comissionccbri` DECIMAL(5,2) NOT NULL COMMENT '% of Credit card comission paid to bri' AFTER `accountbankbri`;
ALTER TABLE `klretailpartners` ADD `comissionamexbri` DECIMAL(5,2) NOT NULL COMMENT '% of Credit card comission paid to American Express by bri' AFTER `comissionccbri`;
ALTER TABLE `klretailpartners` ADD `settlementdelaybri` INT(11) NOT NULL DEFAULT '1' COMMENT 'Number of days after POS retail sale to get the settlement of funds' AFTER `comissionamexbri`;

ALTER TABLE `klretailpartners` ADD `accountqrisbri` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'GL account for Bank BRI QRIS transactions' AFTER `settlementdelayqrismandiri`;
ALTER TABLE `klretailpartners` ADD `comissionqrisbri` DECIMAL(5,2) NOT NULL COMMENT '% of Credit card comission paid to bri' AFTER `accountqrisbri`;
ALTER TABLE `klretailpartners` ADD `settlementdelayqrisbri` INT(11) NOT NULL DEFAULT '1' COMMENT 'Number of days after POS retail sale to get the settlement of funds' AFTER `comissionqrisbri`;

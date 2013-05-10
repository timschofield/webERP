INSERT INTO config VALUES('SmtpSetting',0);
ALTER TABLE  `companies` CHANGE  `debtorsact`  `debtorsact` VARCHAR( 20 ) NOT NULL DEFAULT  '70000',
CHANGE  `pytdiscountact`  `pytdiscountact` VARCHAR( 20 ) NOT NULL DEFAULT  '55000',
CHANGE  `creditorsact`  `creditorsact` VARCHAR( 20 ) NOT NULL DEFAULT  '80000',
CHANGE  `payrollact`  `payrollact` VARCHAR( 20 ) NOT NULL DEFAULT  '84000',
CHANGE  `grnact`  `grnact` VARCHAR( 20 ) NOT NULL DEFAULT  '72000',
CHANGE  `exchangediffact`  `exchangediffact` VARCHAR( 20 ) NOT NULL DEFAULT  '65000',
CHANGE  `purchasesexchangediffact`  `purchasesexchangediffact` VARCHAR( 20 ) NOT NULL DEFAULT  '0',
CHANGE  `retainedearnings`  `retainedearnings` VARCHAR( 20 ) NOT NULL DEFAULT  '90000',
CHANGE  `freightact`  `freightact` VARCHAR( 20 ) NOT NULL DEFAULT  '0';

ALTER TABLE  `lastcostrollup` CHANGE  `stockact`  `stockact` VARCHAR( 20 ) NOT NULL DEFAULT  '0',
CHANGE  `adjglact`  `adjglact` VARCHAR( 20 ) NOT NULL DEFAULT  '0';

INSERT INTO  `scripts` (`script` , `pagesecurity` , `description`)
VALUES ('Z_ChangeGLAccountCode.php',  '15',  'Script to change a GL account code accross all tables necessary');

ALTER TABLE  `currencies` ADD  `webcart` TINYINT( 1 ) NOT NULL DEFAULT  '1' COMMENT  'If 1 shown in weberp cart. if 0 no show';

ALTER TABLE  `salescat` CHANGE  `salescatname`  `salescatname` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;



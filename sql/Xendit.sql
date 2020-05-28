ALTER TABLE `klonlinepartners` 
ADD `accountxenditidr` VARCHAR(20) NOT NULL COMMENT 'GL Account for XENDIT in IDR' AFTER `foreigncurrencysurchargefactor`, 
ADD `accountxenditcomissionidr` VARCHAR(20) NOT NULL COMMENT 'GL account for XENDITComission in IDR' AFTER `accountxenditidr`, 
ADD `comissionxenditflattransfer` INT(11) NOT NULL COMMENT 'Flat commission charged by XENDIT for bank transfer transactions' AFTER `accountxenditcomissionidr`, 
ADD `comissionxenditflatcc` INT(11) NOT NULL COMMENT 'Flat commission charged by XENDIT for credit card transactions' AFTER `comissionxenditflattransfer`, 
ADD `comissionxenditpercentcc` DECIMAL(5,2) NOT NULL COMMENT '% commission charged by XENDIT for CC transactions' AFTER `comissionxenditflatcc`;

ALTER TABLE `klonlinepartners` 
ADD `accountdirecttransferidr` VARCHAR(20) NOT NULL COMMENT 'GL Account for direct transfer' AFTER `comissionxenditpercentcc`;

/* Payment codes on oc_order varchar(128)
pp_express
bank_mandiri
xenditmandiriva
xenditcc

OBSOLETES
bank_danamon
ipaymu
doku_onecheckout
*/

ALTER TABLE `salesorders` ADD `klocpaymentcode` VARCHAR(128) NOT NULL COMMENT 'Payment Code used in OpenCart' AFTER `klexported`;

UPDATE salesorders SET klocpaymentcode = "pp_express" WHERE debtorno = "WEB-KL-AUD";
UPDATE salesorders SET klocpaymentcode = "pp_express" WHERE debtorno = "WEB-KL-EUR";
UPDATE salesorders SET klocpaymentcode = "pp_express" WHERE debtorno = "WEB-KL-USD";
UPDATE salesorders SET klocpaymentcode = "bank_mandiri" WHERE debtorno = "WEB-KL-IDR" AND orddate >= "2020-01-01";

/*
Ara cal:
modificar KLReceiptPaymentOnline per diferents payment codes
*/

ALTER TABLE `klonlinepartners` 
ADD `` VARCHAR(20) NOT NULL COMMENT 'GL Account for commissionPPN ' AFTER `comissionxenditpercentcc`;

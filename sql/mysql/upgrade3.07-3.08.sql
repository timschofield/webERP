ALTER TABLE `salesorderdetails`
 ADD COLUMN `itemdue` DATE  DEFAULT NULL COMMENT
 'Due date for line item.  Some customers require 
acknowledgements with due dates by line item'
AFTER `narrative`,
ADD COLUMN `poline` VARCHAR(10)  DEFAULT NULL COMMENT 'Some Customers require acknowledgements with a PO line number for each sales line' AFTER `itemdue`;

ALTER TABLE `debtorsmaster` 
 ADD COLUMN `customerpoline` TinyInt(1)  NOT NULL DEFAULT 0 AFTER `taxref`;

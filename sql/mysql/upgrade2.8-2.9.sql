BEGIN;

ALTER TABLE `BankAccounts` CHANGE `AccountCode` `AccountCode` INT( 11 ) NOT NULL;

ALTER TABLE TaxAuthorities ADD INDEX (TaxGLCode);
ALTER  TABLE TaxAuthorities ADD INDEX (PurchTaxGLAccount);

ALTER  TABLE TaxAuthorities ADD  FOREIGN  KEY ( TaxGLCode )  REFERENCES ChartMaster( AccountCode );
ALTER TABLE TaxAuthorities ADD FOREIGN KEY ( PurchTaxGLAccount ) REFERENCES ChartMaster( AccountCode );

CREATE TABLE `EDI_ORDERS_Segs` (
  `ID` int(11) NOT NULL auto_increment,
  `SegTag` char(3) NOT NULL default '',
  `SegGroup` tinyint(4) NOT NULL default '0',
  `MaxOccurr` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`ID`),
  KEY `SegTag` (`SegTag`),
  KEY `SegNo` (`SegGroup`)
) TYPE=InnoDB;

INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('UNB', '0', '1');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('UNH', '0', '1');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('BGM', '0', '1');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('DTM', '0', '35');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('PAI', '0', '1');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('ALI', '0', '5');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('FTX', '0', '99');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('RFF', '1', '1');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('DTM', '1', '5');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('NAD', '2', '1');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('LOC', '2', '99');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('FII', '2', '5');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('RFF', '3', '1');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('CTA', '5', '1');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('COM', '5', '5');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('TAX', '6', '1');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('MOA', '6', '1');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('CUX', '7', '1');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('DTM', '7', '5');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('PAT', '8', '1');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('DTM', '8', '5');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('PCD', '8', '1');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('MOA', '9', '1');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('TDT', '10', '1');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('LOC', '11', '1');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('DTM', '11', '5');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('TOD', '12', '1');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('LOC', '12', '2');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('PAC', '13', '1');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('PCI', '14', '1');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('RFF', '14', '1');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('DTM', '14', '5');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('GIN', '14', '10');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('EQD', '15', '1');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('ALC', '19', '1');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('ALI', '19', '5');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('DTM', '19', '5');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('QTY', '20', '1');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('RNG', '20', '1');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('PCD', '21', '1');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('RNG', '21', '1');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('MOA', '22', '1');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('RNG', '22', '1');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('RTE', '23', '1');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('RNG', '23', '1');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('TAX', '24', '1');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('MOA', '24', '1');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('LIN', '28', '1');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('PIA', '28', '25');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('IMD', '28', '99');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('MEA', '28', '99');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('QTY', '28', '99');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('ALI', '28', '5');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('DTM', '28', '35');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('MOA', '28', '10');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('GIN', '28', '1000');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('QVR', '28', '1');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('FTX', '28', '99');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('PRI', '32', '1');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('CUX', '32', '1');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('DTM', '32', '5');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('RFF', '33', '1');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('DTM', '33', '5');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('PAC', '34', '1');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('QTY', '34', '5');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('PCI', '36', '1');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('RFF', '36', '1');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('DTM', '36', '5');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('GIN', '36', '10');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('LOC', '37', '1');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('QTY', '37', '1');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('DTM', '37', '5');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('TAX', '38', '1');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('MOA', '38', '1');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('NAD', '39', '1');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('CTA', '42', '1');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('COM', '42', '5');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('ALC', '43', '1');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('ALI', '43', '5');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('DTM', '43', '5');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('QTY', '44', '1');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('RNG', '44', '1');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('PCD', '45', '1');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('RNG', '45', '1');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('MOA', '46', '1');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('RNG', '46', '1');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('RTE', '47', '1');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('RNG', '47', '1');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('TAX', '48', '1');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('MOA', '48', '1');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('TDT', '49', '1');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('UNS', '0', '1');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('MOA', '0', '1');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('CNT', '0', '1');
INSERT INTO `EDI_ORDERS_Segs` (`SegTag` , `SegGroup` , `MaxOccurr` ) VALUES ('UNT', '0', '1');

CREATE TABLE `EDI_ORDERS_Seg_Groups` (
  `SegGroupNo` tinyint(4) NOT NULL default '0',
  `MaxOccurr` int(4) NOT NULL default '0',
  `ParentSegGroup` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`SegGroupNo`)
) TYPE=InnoDB;


INSERT INTO `EDI_ORDERS_Seg_Groups` VALUES (0, 1, 0);
INSERT INTO `EDI_ORDERS_Seg_Groups` VALUES (1, 9999, 0);
INSERT INTO `EDI_ORDERS_Seg_Groups` VALUES (2, 99, 0);
INSERT INTO `EDI_ORDERS_Seg_Groups` VALUES (3, 99, 2);
INSERT INTO `EDI_ORDERS_Seg_Groups` VALUES (5, 5, 2);
INSERT INTO `EDI_ORDERS_Seg_Groups` VALUES (6, 5, 0);
INSERT INTO `EDI_ORDERS_Seg_Groups` VALUES (7, 5, 0);
INSERT INTO `EDI_ORDERS_Seg_Groups` VALUES (8, 10, 0);
INSERT INTO `EDI_ORDERS_Seg_Groups` VALUES (9, 9999, 8);
INSERT INTO `EDI_ORDERS_Seg_Groups` VALUES (10, 10, 0);
INSERT INTO `EDI_ORDERS_Seg_Groups` VALUES (11, 10, 10);
INSERT INTO `EDI_ORDERS_Seg_Groups` VALUES (12, 5, 0);
INSERT INTO `EDI_ORDERS_Seg_Groups` VALUES (13, 99, 0);
INSERT INTO `EDI_ORDERS_Seg_Groups` VALUES (14, 5, 13);
INSERT INTO `EDI_ORDERS_Seg_Groups` VALUES (15, 10, 0);
INSERT INTO `EDI_ORDERS_Seg_Groups` VALUES (19, 99, 0);
INSERT INTO `EDI_ORDERS_Seg_Groups` VALUES (20, 1, 19);
INSERT INTO `EDI_ORDERS_Seg_Groups` VALUES (21, 1, 19);
INSERT INTO `EDI_ORDERS_Seg_Groups` VALUES (22, 2, 19);
INSERT INTO `EDI_ORDERS_Seg_Groups` VALUES (23, 1, 19);
INSERT INTO `EDI_ORDERS_Seg_Groups` VALUES (24, 5, 19);
INSERT INTO `EDI_ORDERS_Seg_Groups` VALUES (28, 200000, 0);
INSERT INTO `EDI_ORDERS_Seg_Groups` VALUES (32, 25, 28);
INSERT INTO `EDI_ORDERS_Seg_Groups` VALUES (33, 9999, 28);
INSERT INTO `EDI_ORDERS_Seg_Groups` VALUES (34, 99, 28);
INSERT INTO `EDI_ORDERS_Seg_Groups` VALUES (36, 5, 34);
INSERT INTO `EDI_ORDERS_Seg_Groups` VALUES (37, 9999, 28);
INSERT INTO `EDI_ORDERS_Seg_Groups` VALUES (38, 10, 28);
INSERT INTO `EDI_ORDERS_Seg_Groups` VALUES (39, 999, 28);
INSERT INTO `EDI_ORDERS_Seg_Groups` VALUES (42, 5, 39);
INSERT INTO `EDI_ORDERS_Seg_Groups` VALUES (43, 99, 28);
INSERT INTO `EDI_ORDERS_Seg_Groups` VALUES (44, 1, 43);
INSERT INTO `EDI_ORDERS_Seg_Groups` VALUES (45, 1, 43);
INSERT INTO `EDI_ORDERS_Seg_Groups` VALUES (46, 2, 43);
INSERT INTO `EDI_ORDERS_Seg_Groups` VALUES (47, 1, 43);
INSERT INTO `EDI_ORDERS_Seg_Groups` VALUES (48, 5, 43);
INSERT INTO `EDI_ORDERS_Seg_Groups` VALUES (49, 10, 28);


CREATE TABLE `LocTransfers` (
  `Reference` int(11) NOT NULL default '0',
  `StockID` varchar(20) NOT NULL default '',
  `ShipQty` int(11) NOT NULL default '0',
  `RecQty` int(11) NOT NULL default '0',
  `ShipDate` date NOT NULL default '0000-00-00',
  `RecDate` date NOT NULL default '0000-00-00',
  `ShipLoc` varchar(7) NOT NULL default '',
  `RecLoc` varchar(7) NOT NULL default '',
  KEY `Reference` (`Reference`,`StockID`),
  KEY `ShipLoc` (`ShipLoc`),
  KEY `RecLoc` (`RecLoc`),
  KEY `StockID` (`StockID`)
) TYPE=InnoDB COMMENT='Stores Transfers To Other Locations';

ALTER TABLE `LocTransfers`
  ADD CONSTRAINT `LocTransfers_ibfk_3` FOREIGN KEY (`StockID`) REFERENCES `StockMaster` (`StockID`),
  ADD CONSTRAINT `LocTransfers_ibfk_1` FOREIGN KEY (`ShipLoc`) REFERENCES `Locations` (`LocCode`),
  ADD CONSTRAINT `LocTransfers_ibfk_2` FOREIGN KEY (`RecLoc`) REFERENCES `Locations` (`LocCode`);

CREATE TABLE StockSerialItems (
  LocCode varchar(5) NOT NULL default '',
  StockID varchar(20) NOT NULL default '',
  SerialNo varchar(30) NOT NULL default '',
  Quantity float NOT NULL default 0,
  PRIMARY KEY  (StockID, SerialNo, LocCode),
  KEY (StockID),
  KEY (LocCode)
) TYPE=InnoDB;

CREATE TABLE StockSerialMoves (
  StkItmMoveNo int(11) NOT NULL auto_increment,
  StockMoveNo int(11) NOT NULL default '0',
  StockID varchar(20) NOT NULL default '',
  SerialNo varchar(30) NOT NULL default '',
  MoveQty float NOT NULL default '0',
  PRIMARY KEY  (StkItmMoveNo),
  KEY StockMoveNo (StockMoveNo),
  KEY StockID_SN (StockID, SerialNo)
) TYPE=InnoDB;

ALTER TABLE StockSerialMoves ADD FOREIGN KEY (StockMoveNo) REFERENCES StockMoves (StkMoveNo);
ALTER TABLE StockSerialItems ADD FOREIGN KEY (StockID) REFERENCES StockMaster (StockID);
ALTER TABLE StockSerialItems ADD FOREIGN KEY (LocCode) REFERENCES Locations (LocCode);
ALTER TABLE StockSerialMoves ADD FOREIGN KEY (StockID, SerialNo) REFERENCES StockSerialItems (StockID, SerialNo);

ALTER TABLE `StockMaster` ADD `Serialised` TINYINT DEFAULT '0' NOT NULL ;
ALTER TABLE `StockMaster` ADD `DecimalPlaces` TINYINT DEFAULT '0' NOT NULL ;
ALTER TABLE `StockMoves` DROP `Bundle`;

CREATE TABLE `Help` (
  ID INT NOT NULL AUTO_INCREMENT,
  PageID SMALLINT NOT NULL,
  Narrative TEXT NOT NULL,
  HelpType CHAR NOT NULL DEFAULT 'U',
  PRIMARY KEY (`ID`),
  INDEX (`PageID`),
  INDEX (`HelpType`)
)TYPE=InnoDB COMMENT='Context Sensitive Help Narrative';

CREATE TABLE Scripts (
  PageID smallint(4) NOT NULL default '0',
  FileName varchar(50) NOT NULL default '',
  PageDescription text NOT NULL,
  PRIMARY KEY  (PageID),
  KEY FileName (FileName)
) TYPE=InnoDB COMMENT='Index of all scripts';


ALTER TABLE Help ADD FOREIGN KEY (PageID) REFERENCES Scripts (PageID);

INSERT INTO Scripts VALUES (1, 'AccountGroups.php', 'Defines the groupings of general ledger accounts');
INSERT INTO Scripts VALUES (2, 'AgedDebtors.php', 'Lists customer account balances in detail or summary in selected currency');
INSERT INTO Scripts VALUES (3, 'AgedSuppliers.php', 'Lists supplier account balances in detail or summary in selected currency');
INSERT INTO Scripts VALUES (4, 'Areas.php', 'Defines the sales areas - all customers must belong to a sales area for the purposes of sales analysis');
INSERT INTO Scripts VALUES (5, 'BOMInquiry.php', 'Displays the bill of material with cost information');
INSERT INTO Scripts VALUES (6, 'BOMListing.php', 'Lists the bills of material for a selected range of items');
INSERT INTO Scripts VALUES (7, 'BOMs.php', 'Administers the bills of material for a selected item');
INSERT INTO Scripts VALUES (8, 'BankAccounts.php', 'Defines the general ledger code for bank accounts and specifies that bank transactions be created for these accounts for the purposes of reconciliation');
INSERT INTO Scripts VALUES (9, 'BankMatching.php', 'Allows payments and receipts to be matched off against bank statements');
INSERT INTO Scripts VALUES (10, 'BankReconciliation.php', 'Displays the bank reconciliation for a selected bank account');
INSERT INTO Scripts VALUES (11, 'COGSGLPostings.php', 'Defines the general ledger account to be used for cost of sales entries');
INSERT INTO Scripts VALUES (12, 'CompanyPreferences.php', 'Defines the settings applicable for the company, including name, address, tax authority reference, whether GL integration used etc.');
INSERT INTO Scripts VALUES (13, 'ConfirmDispatchControlled_Invoice.php', 'Specifies the batch references/serial numbers of items dispatched that are being invoiced');
INSERT INTO Scripts VALUES (14, 'ConfirmDispatch_Invoice.php', 'Creates sales invoices from entered sales orders based on the quantities dispatched that can be modified');
INSERT INTO Scripts VALUES (15, 'CreditItemsControlled.php', 'Specifies the batch references/serial numbers of items being credited back into stock');
INSERT INTO Scripts VALUES (16, 'CreditStatus.php', 'Defines the credit status records. Each customer account is given a credit status from this table. Some credit status records can prohibit invoicing and new orders being entered.');
INSERT INTO Scripts VALUES (17, 'Credit_Invoice.php', 'Creates a credit note based on the details of an existing invoice');
INSERT INTO Scripts VALUES (18, 'Currencies.php', 'Defines the currencies available. Each customer and supplier must be defined as transacting in one of the currencies defined here.');
INSERT INTO Scripts VALUES (19, 'CustEDISetup.php', 'Allows the set up the customer specified EDI parameters for server, email or ftp.');
INSERT INTO Scripts VALUES (20, 'CustWhereAlloc.php', 'Shows to which invoices a receipt was allocated to');
INSERT INTO Scripts VALUES (21, 'CustomerAllocations.php', 'Allows customer receipts and credit notes to be allocated to sales invoices');
INSERT INTO Scripts VALUES (22, 'CustomerBranches.php', 'Defines the details of customer branches such as delivery address and contact details - also sales area, representative etc');
INSERT INTO Scripts VALUES (23, 'CustomerInquiry.php', 'Shows the customers account transactions with balances outstanding, links available to drill down to invoice/credit note or email invoices/credit notes');
INSERT INTO Scripts VALUES (24, 'CustomerReceipt.php', 'Entry of both customer receipts against accounts receivable and also general ledger or nominal receipts');
INSERT INTO Scripts VALUES (25, 'CustomerTransInquiry.php', 'Lists in html the sequence of customer transactions, invoices, credit notes or receipts by a user entered date range');
INSERT INTO Scripts VALUES (26, 'Customers.php', 'Defines the setup of a customer account, including payment terms, billing address, credit status, currency etc');
INSERT INTO Scripts VALUES (27, 'DeliveryDetails.php', 'Used during order entry to allow the entry of delivery addresses other than the defaulted branch delivery address and information about carrier/shipping method etc');
INSERT INTO Scripts VALUES (28, 'DiscountCategories.php', 'Defines the items belonging to a discount category. Discount Categories are used to allow discounts based on quantities across a range of producs');
INSERT INTO Scripts VALUES (29, 'DiscountMatrix.php', 'Defines the rates of discount applicable to discount categories and the customer groupings to which the rates are to apply');
INSERT INTO Scripts VALUES (30, 'EDIMessageFormat.php', 'Specifies the EDI message format used by a customer - administrator use only.');
INSERT INTO Scripts VALUES (31, 'EDIProcessOrders.php', 'Processes incoming EDI orders into sales orders');
INSERT INTO Scripts VALUES (32, 'EDISendInvoices.php', 'Processes invoiced EDI customer invoices into EDI messages and sends using the customers preferred method either ftp or email attachments.');
INSERT INTO Scripts VALUES (33, 'EmailCustTrans.php', 'Emails selected invoice or credit to the customer');
INSERT INTO Scripts VALUES (34, 'FTP_RadioBeacon.php', 'FTPs sales orders for dispatch to a radio beacon software enabled warehouse dispatching facility');
INSERT INTO Scripts VALUES (35, 'FreightCosts.php', 'Defines the setup of the freight cost using different shipping methods to different destinations. The system can use this information to calculate applicable freight if the items are defined with the correct kgs and cubic volume');
INSERT INTO Scripts VALUES (36, 'GLAccountInquiry.php', 'Shows the general ledger transactions for a specified account over a specified range of periods');
INSERT INTO Scripts VALUES (37, 'GLAccounts.php', 'Defines the general ledger accounts');
INSERT INTO Scripts VALUES (38, 'GLBalanceSheet.php', 'Shows the balance sheet for the company as at a specified date');
INSERT INTO Scripts VALUES (39, 'GLCodesInquiry.php', 'Shows the list of general ledger codes defined with account names and groupings');
INSERT INTO Scripts VALUES (40, 'GLJournal.php', 'Entry of general ledger journals, periods are calculated based on the date entered here');
INSERT INTO Scripts VALUES (41, 'GLProfit_Loss.php', 'Shows the profit and loss of the company for the range of periods entered');
INSERT INTO Scripts VALUES (42, 'GLTransInquiry.php', 'Shows the general ledger journal created for the sub ledger transaction specified');
INSERT INTO Scripts VALUES (43, 'GLTrialBalance.php', 'Shows the trial balance for the month and the for the period selected together with the budgeted trial balances');
INSERT INTO Scripts VALUES (44, 'GoodsReceived.php', 'Entry of items received against purchase orders');
INSERT INTO Scripts VALUES (45, 'GoodsReceivedControlled.php', 'Entry of the serial numbers or batch references for controlled items received against purchase orders');
INSERT INTO Scripts VALUES (46, 'InventoryPlanning.php', 'Creates a pdf report showing the last 4 months use of items including as a component of assemblies together with stock quantity on hand, current demand for the item and current quantity on sales order.');
INSERT INTO Scripts VALUES (47, 'InventoryValuation.php', 'Creates a pdf report showing the value of stock at standard cost for a range of product categories selected');
INSERT INTO Scripts VALUES (48, 'Locations.php', 'Defines the inventory stocking locations or warehouses');
INSERT INTO Scripts VALUES (49, 'Logout.php', 'Shows when the user logs out of webERP');
INSERT INTO Scripts VALUES (50, 'MailInventoryValuation.php', 'Meant to be run as a scheduled process to email the stock valuation off to a specified person. Creates the same stock valuation report as InventoryValuation.php');
INSERT INTO Scripts VALUES (51, 'MailSalesReport.php', 'Creates a sales analysis pdf report and emails it to the defined receipients. This script is meant to be run as a scheduled process for daily or weekly sales reporting');
INSERT INTO Scripts VALUES (52, 'MailSalesReport_csv.php', 'Creates a sales analysis report as a comma separated values (csv) file and emails it to the defined receipients. This script is meant to be run as a scheduled process for daily or weekly sales reporting');
INSERT INTO Scripts VALUES (53, 'OrderDetails.php', 'Shows the detail of a sales order');
INSERT INTO Scripts VALUES (54, 'OutstandingGRNs.php', 'Creates a pdf showing all GRNs for which there has been no purchase invoice matched off against.');
INSERT INTO Scripts VALUES (55, 'PDFBankingSummary.php', 'Creates a pdf showing the amounts entered as receipts on a specified date together with references for the purposes of banking');
INSERT INTO Scripts VALUES (56, 'PDFChequeListing.php', 'Creates a pdf showing all payments that have been made from a specified bank account over a specified period. This can be emailed to an email account defined in config.php - ie a financial controller');
INSERT INTO Scripts VALUES (57, 'PDFDeliveryDifferences.php', 'Creates a pdf report listing the delivery differences from what the customer requested as recorded in the order entry. The report calculates a percentage of order fill based on the number of orders filled in full on time');
INSERT INTO Scripts VALUES (58, 'PDFLowGP.php', 'Creates a pdf report showing the low gross profit sales made in the selected date range. The percentage of gp deemed acceptable can also be entered');
INSERT INTO Scripts VALUES (59, 'PDFPriceList.php', 'Creates a pdf of the price list applicable to a given sales type and customer. Also allows the listing of prices specific to a customer');
INSERT INTO Scripts VALUES (60, 'PDFStockCheckComparison.php', 'Creates a pdf comparing the quantites entered as counted at a given range of locations against the quantity stored as on hand as at the time a stock check was initiated.');
INSERT INTO Scripts VALUES (61, 'PDFStockLocTransfer.php', 'Creates a stock location transfer docket for the selected location transfer reference number');
INSERT INTO Scripts VALUES (62, 'PO_Chk_ShiptRef_JobRef.php', 'Checks the Shipment of JobReference number is correct during AP invoice entry');
INSERT INTO Scripts VALUES (63, 'PO_Header.php', 'Entry of a purchase order header record - date, references buyer etc');
INSERT INTO Scripts VALUES (64, 'PO_Items.php', 'Entry of a purchase order items - allows entry of items with lookup of currency cost from Purchasing Data previously entered also allows entry of nominal items against a general ledger code if the AP is integrated to the GL');
INSERT INTO Scripts VALUES (65, 'PO_OrderDetails.php', 'Purchase order inquiry shows the quantity received and invoiced of purchase order items as well as the header information');
INSERT INTO Scripts VALUES (66, 'PO_PDFPurchOrder.php', 'Creates a pdf of the selected purchase order for printing or email to one of the supplier contacts entered');
INSERT INTO Scripts VALUES (67, 'PO_SelectOSPurchOrder.php', 'Shows the outstanding purchase orders for selecting with links to receive or modify the purchase order header and items');
INSERT INTO Scripts VALUES (68, 'PO_SelectPurchOrder.php', 'Allows selection of any purchase order with links to the inquiry');
INSERT INTO Scripts VALUES (69, 'PaymentTerms.php', 'Defines the payment terms records, these can be expressed as either a number of days credit or a day in the following month. All customers and suppliers must have a corresponding payment term recorded against their account');
INSERT INTO Scripts VALUES (70, 'Payments.php', 'Entry of bank account payments either against an AP account or a general ledger payment - if the AP-GL link in company preferences is set');
INSERT INTO Scripts VALUES (71, 'PeriodsInquiry.php', 'Shows a list of all the system defined periods');
INSERT INTO Scripts VALUES (72, 'Prices.php', 'Entry of prices for a selected item also allows selection of sales type and currency for the price');
INSERT INTO Scripts VALUES (73, 'Prices_Customer.php', 'Entry of prices for a selected item and selected customer/branch. The currency and sales type is defaulted from the customer\'s record');
INSERT INTO Scripts VALUES (74, 'PrintCustOrder.php', 'Creates a pdf of the dispatch note - by default this is expected to be on two part pre-printed stationery to allow pickers to note discrepancies for the confirmer to update the dispatch at the time of invoicing');
INSERT INTO Scripts VALUES (75, 'PrintCustOrder_generic.php', 'Creates two copies of a laser printed dispatch note - both copies need to be written on by the pickers with any discrepancies to advise customer of any shortfall and on the office copy to ensure the correct quantites are invoiced');
INSERT INTO Scripts VALUES (76, 'PrintCustStatements.php', 'Creates a pdf for the customer statements in the selected range');
INSERT INTO Scripts VALUES (77, 'PrintCustTrans.php', 'Creates either a html invoice or credit note or a pdf. A range of invoices or credit notes can be selected also.');
INSERT INTO Scripts VALUES (78, 'PurchData.php', 'Entry of supplier purchasing data, the suppliers part reference and the suppliers currency cost of the item');
INSERT INTO Scripts VALUES (79, 'ReverseGRN.php', 'Reverses the entry of goods received - creating stock movements back out and necessary general ledger journals to effect the reversal');
INSERT INTO Scripts VALUES (80, 'SalesAnalReptCols.php', 'Entry of the definition of a sales analysis report\'s columns.');
INSERT INTO Scripts VALUES (81, 'SalesAnalRepts.php', 'Entry of the definition of a sales analysis report headers');
INSERT INTO Scripts VALUES (82, 'SalesAnalysis_UserDefined.php', 'Creates a pdf of a selected user defined sales analysis report');
INSERT INTO Scripts VALUES (83, 'SalesGLPostings.php', 'Defines the general ledger accounts used to post sales to based on product categories and sales areas');
INSERT INTO Scripts VALUES (84, 'SalesPeople.php', 'Defines the sales people of the business');
INSERT INTO Scripts VALUES (85, 'SalesTypes.php', 'Defines the sales types - prices are held against sales types they can be considered price lists. Sales analysis records are held by sales type too.');
INSERT INTO Scripts VALUES (86, 'SelectCompletedOrder.php', 'Allows the selection of completed sales orders for inquiries - choices to select by item code or customer');
INSERT INTO Scripts VALUES (87, 'SelectCreditItems.php', 'Entry of credit notes from scratch, selecting the items in either quick entry mode or searching for them manually');
INSERT INTO Scripts VALUES (88, 'SelectCustomer.php', 'Selection of customer - from where all customer related maintenance, transactions and inquiries start');
INSERT INTO Scripts VALUES (89, 'SelectGLAccount.php', 'Selection of general ledger account from where all general ledger account maintenance, or inquiries are initiated');
INSERT INTO Scripts VALUES (90, 'SelectOrderItems.php', 'Entry of sales order items with both quick entry and part search functions');
INSERT INTO Scripts VALUES (91, 'SelectProduct.php', 'Selection of items. All item maintenance, transactions and inquiries start with this script');
INSERT INTO Scripts VALUES (92, 'SelectSalesOrder.php', 'Selects a sales order irrespective of completed or not for inquiries');
INSERT INTO Scripts VALUES (93, 'SelectSupplier.php', 'Selects a supplier. A supplier is required to be selected before any AP transactions and before any maintenance or inquiry of the supplier');
INSERT INTO Scripts VALUES (94, 'ShipmentCosting.php', 'Shows the costing of a shipment with all the items invoice values and any shipment costs apportioned. Updating the shipment has an option to update standard costs of all items on the shipment and create any general ledger variance journals');
INSERT INTO Scripts VALUES (95, 'Shipments.php', 'Entry of shipments from outstanding purchase orders for a selected supplier - changes in the delivery date will cascade into the different purchase orders on the shipment');
INSERT INTO Scripts VALUES (96, 'Shippers.php', 'Defines the shipping methods available. Each customer branch has a default shipping method associated with it which must match a record from this table');
INSERT INTO Scripts VALUES (97, 'Shipt_Select.php', 'Selection of a shipment for displaying and modification or updating');
INSERT INTO Scripts VALUES (98, 'ShiptsList.php', 'Shows a list of all the open shipments for a selected supplier. Linked from POItems.php');
INSERT INTO Scripts VALUES (99, 'SpecialOrder.php', 'Allows for a sales order to be created and an indent order to be created on a supplier for a one off item that may never be purchased again. A dummy part is created based on the description and cost details given.');
INSERT INTO Scripts VALUES (100, 'StockAdjustments.php', 'Entry of quantity corrections to stocks in a selected location.');
INSERT INTO Scripts VALUES (101, 'StockAdjustmentsControlled.php', 'Entry of batch references or serial numbers on controlled stock items being adjusted');
INSERT INTO Scripts VALUES (102, 'StockCategories.php', 'Defines the stock categories. All items must refer to one of these categories. The category record also allows the specification of the general ledger codes where stock items are to be posted - the balance sheet account and the profit and loss effect of any adjustments and the profit and loss effect of any price variances');
INSERT INTO Scripts VALUES (103, 'StockCheck.php', 'Allows creation of a stock check file - copying the current quantites in stock for later comparison to the entered counts. Also produces a pdf for the count sheets.');
INSERT INTO Scripts VALUES (104, 'StockCostUpdate.php', 'Allows update of the standard cost of items producing general ledger journals if the company preferences stock GL interface is active');
INSERT INTO Scripts VALUES (105, 'StockCounts.php', 'Allows entry of stock counts');
INSERT INTO Scripts VALUES (106, 'StockLocMovements.php', 'Inquiry shows the Movements of all stock items for a specified location');
INSERT INTO Scripts VALUES (107, 'StockLocQties_csv.php', 'Makes a comma separated values (CSV)file of the stock item codes and quantities');
INSERT INTO Scripts VALUES (108, 'StockLocStatus.php', 'Shows the stock on hand together with outstanding sales orders and outstanding purchase orders by stock location for all items in the selected stock category');
INSERT INTO Scripts VALUES (109, 'StockLocTransfer.php', 'Entry of a bulk stock location transfer for many parts from one location to another.');
INSERT INTO Scripts VALUES (110, 'StockLocTransferReceive.php', 'Effects the transfer and creates the stock movements for a bulk stock location transfer initiated from StockLocTransfer.php');
INSERT INTO Scripts VALUES (111, 'StockMovements.php', 'Shows a list of all the stock movements for a selected item and stock location including the price at which they were sold in local currency and the price at which they were purchased for in local currency');
INSERT INTO Scripts VALUES (112, 'StockQties_csv.php', 'Makes a comma separated values (CSV)file of the stock item codes and quantities');
INSERT INTO Scripts VALUES (113, 'StockReorderLevel.php', 'Entry and review of the re-order level of items by stocking location');
INSERT INTO Scripts VALUES (114, 'StockSerialItems.php', 'Shows a list of the serial numbers or the batch references and quantities of controlled items. This inquiry is linked from the stock status inquiry');
INSERT INTO Scripts VALUES (115, 'StockStatus.php', 'Shows the stock on hand together with outstanding sales orders and outstanding purchase orders by stock location for a selected part. Has a link to show the serial numbers in stock at the location selected if the item is controlled');
INSERT INTO Scripts VALUES (116, 'StockTransferControlled.php', 'Entry of serial numbers/batch references for controlled items being received on a stock transfer. The script is used by both bulk transfers and point to point transfers');
INSERT INTO Scripts VALUES (117, 'StockTransfers.php', 'Entry of point to point stock location transfers of a single part');
INSERT INTO Scripts VALUES (118, 'StockUsage.php', 'Inquiry showing the quantity of stock used by period calculated from the sum of the stock movements over that period - by item and stock location. Also available over all locations');
INSERT INTO Scripts VALUES (119, 'Stocks.php', 'Defines an item - maintenance and addition of new parts');
INSERT INTO Scripts VALUES (120, 'SuppCreditGRNs.php', 'Entry of a supplier credit notes (debit notes) against existing GRN which have already been matched in full or in part');
INSERT INTO Scripts VALUES (121, 'SuppInvGRNs.php', 'Entry of supplier invoices against goods received');
INSERT INTO Scripts VALUES (122, 'SuppPaymentRun.php', 'Automatic creation of payment records based on calculated amounts due from AP invoices entered');
INSERT INTO Scripts VALUES (123, 'SuppShiptChgs.php', 'Entry of supplier invoices against shipments as charges against a shipment');
INSERT INTO Scripts VALUES (124, 'SuppTransGLAnalysis.php', 'Entry of supplier invoices against general ledger codes');
INSERT INTO Scripts VALUES (125, 'SupplierAllocations.php', 'Entry of allocations of supplier payments and credit notes to invoices');
INSERT INTO Scripts VALUES (126, 'SupplierContacts.php', 'Entry of supplier contacts and contact details including email addresses');
INSERT INTO Scripts VALUES (127, 'SupplierCredit.php', 'Entry of supplier credit notes (debit notes)');
INSERT INTO Scripts VALUES (128, 'SupplierInquiry.php', 'Inquiry showing invoices, credit notes and payments made to suppliers together with the amounts outstanding');
INSERT INTO Scripts VALUES (129, 'SupplierInvoice.php', 'Entry of supplier invoices');
INSERT INTO Scripts VALUES (130, 'Suppliers.php', 'Entry of new suppliers and maintenance of existing suppliers');
INSERT INTO Scripts VALUES (131, 'TaxAuthorities.php', 'Entry of tax authorities - the state intitutions that charge tax');
INSERT INTO Scripts VALUES (132, 'TaxAuthorityRates.php', 'Entry of the rates of tax applicable to the tax authority depending on the item tax level');
INSERT INTO Scripts VALUES (133, 'WWW_Users.php', 'Entry of users and security settings of users');
INSERT INTO Scripts VALUES (134, 'WhereUsedInquiry.php', 'Inquiry showing where an item is used ie all the parents where the item is a component of');
INSERT INTO Scripts VALUES (135, 'WorkCentres.php', 'Defines the various centres of work within a manufacturing company. Also the overhead and labour rates applicable to the work centre and its standard capacity');
INSERT INTO Scripts VALUES (136, 'WorkOrderEntry.php', 'Entry of new work orders');
INSERT INTO Scripts VALUES (137, 'WorkOrderIssue.php', 'Issue of materials to a work order');
INSERT INTO Scripts VALUES (138, 'Z_ChangeBranchCode.php', 'Utility to change the branch code of a customer that cascades the change through all the necessary tables');
INSERT INTO Scripts VALUES (139, 'Z_ChangeCustomerCode.php', 'Utility to change a customer code that cascades the change through all the necessary tables');
INSERT INTO Scripts VALUES (140, 'Z_ChangeStockCode.php', 'Utility to change an item code that cascades the change through all the necessary tables');
INSERT INTO Scripts VALUES (141, 'Z_CheckAllocationsFrom.php', '');
INSERT INTO Scripts VALUES (142, 'Z_CheckAllocs.php', '');
INSERT INTO Scripts VALUES (143, 'Z_CheckDebtorsControl.php', 'Inquiry that shows the total local currency (functional currency) balance of all customer accounts to reconcile with the general ledger debtors account');
INSERT INTO Scripts VALUES (144, 'Z_CreateChartDetails.php', 'Utility page to create chart detail records for all general ledger accounts and periods created - needs expert assistance in use');
INSERT INTO Scripts VALUES (145, 'Z_CreateCompany.php', 'Utility to insert company number 1 if not already there - actually only company 1 is used - the system is not multi-company');
INSERT INTO Scripts VALUES (146, 'Z_CurrencyDebtorsBalances.php', 'Inquiry that shows the total foreign currency together with the total local currency (functional currency) balances of all customer accounts to reconcile with the general ledger debtors account');
INSERT INTO Scripts VALUES (147, 'Z_CurrencySuppliersBalances.php', 'Inquiry that shows the total foreign currency amounts and also the local currency (functional currency) balances of all supplier accounts to reconcile with the general ledger creditors account');
INSERT INTO Scripts VALUES (148, 'Z_DeleteCreditNote.php', 'Utility to reverse a customer credit note - a desperate measure that should not be used except in extreme circumstances');
INSERT INTO Scripts VALUES (149, 'Z_DeleteInvoice.php', 'Utility to reverse a customer invoice - a desperate measure that should not be used except in extreme circumstances');
INSERT INTO Scripts VALUES (150, 'Z_DeleteSalesTransActions.php', 'Utility to delete all sales transactions, sales analysis the lot! Extreme care required!!!');
INSERT INTO Scripts VALUES (151, 'Z_MakeStockLocns.php', 'Utility to make LocStock records for all items and locations if not already set up.');
INSERT INTO Scripts VALUES (152, 'Z_PriceChanges.php', 'Utility to make bulk pricing alterations to selected sales type price lists or selected customer prices only');
INSERT INTO Scripts VALUES (153, 'Z_ReApplyCostToSA.php', 'Utility to allow the sales analysis table to be updated with the latest cost information - the sales analysis takes the cost at the time the sale was made to reconcile with the enteries made in the gl.');
INSERT INTO Scripts VALUES (154, 'Z_RePostGLFromPeriod.php', 'Utility to repost all general ledger transaction commencing from a specified period. This can take some time in busy environments. Normally GL transactions are posted automatically each time a trial balance or profit and loss account is run');
INSERT INTO Scripts VALUES (155, 'Z_ReverseSuppPaymentRun.php', 'Utility to reverse an entire Supplier payment run');
INSERT INTO Scripts VALUES (156, 'Z_UpdateChartDetailsBFwd.php', 'Utility to recalculate the ChartDetails table B/Fwd balances - extreme care!!');
INSERT INTO Scripts VALUES (157, 'Z_UploadForm.php', 'Utility to upload a file to a remote server');
INSERT INTO Scripts VALUES (158, 'Z_UploadResult.php', 'Utility to upload a file to a remote server');
INSERT INTO Scripts VALUES (159, 'Z_index.php', 'Utility menu page showing links to many of the utilities that do not appear from the main menu');
INSERT INTO Scripts VALUES (160, 'index.php', 'This screen allows the user to click on links to navigate to to the disired area of functionality');
INSERT INTO Scripts VALUES (161, 'phpinfo.php', 'Details about PHP installation on the server');

COMMIT;

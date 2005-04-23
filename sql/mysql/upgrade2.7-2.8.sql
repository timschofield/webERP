begin;

ALTER TABLE Locations ADD TaxAuthority tinyint(4) NOT NULL default 1;

ALTER TABLE StockMaster ADD TaxLevel tinyint(4) NOT NULL default 1;

CREATE TABLE TaxAuthLevels (
  TaxAuthority tinyint NOT NULL default '1',
  DispatchTaxAuthority tinyint NOT NULL default '1',
  Level tinyint NOT NULL default '0',
  TaxRate double NOT NULL default '0',
  PRIMARY KEY  (TaxAuthority,DispatchTaxAuthority,Level),
  KEY (TaxAuthority),
  KEY (DispatchTaxAuthority)
) TYPE=Innodb;

INSERT INTO TaxAuthLevels VALUES (1, 1, 1, 0.1);
INSERT INTO TaxAuthLevels VALUES (1, 1, 2, 0);

ALTER TABLE TaxAuthorities DROP COLUMN Rate;
ALTER TABLE TaxAuthorities CHANGE TaxID TaxID tinyint(4) NOT NULL default '0';


ALTER TABLE StockMoves ADD COLUMN TaxRate float NOT NULL default 0;
ALTER TABLE DebtorTrans ADD COLUMN EDISent tinyint(4) NOT NULL default 0;
ALTER TABLE DebtorTrans ADD INDEX(`EDISent`);

ALTER TABLE CustBranch ADD CustBranchCode VARCHAR(30) NOT NULL default '';

ALTER TABLE WWW_Users ADD COLUMN Blocked tinyint(4) NOT NULL default 0;

ALTER TABLE DebtorsMaster ADD EDIInvoices tinyint(4) NOT NULL default '0';
ALTER TABLE DebtorsMaster ADD EDIOrders tinyint(4) NOT NULL default '0';
ALTER TABLE DebtorsMaster ADD EDIReference varchar(20) NOT NULL default '';
ALTER TABLE DebtorsMaster ADD EDITransport varchar(5) NOT NULL default 'email';
ALTER TABLE DebtorsMaster ADD EDIAddress varchar(50) NOT NULL default '';
ALTER TABLE DebtorsMaster ADD EDIServerUser varchar(20) NOT NULL default '';
ALTER TABLE DebtorsMaster ADD EDIServerPwd varchar(20) NOT NULL default '';
ALTER TABLE DebtorsMaster ADD INDEX (EDIInvoices);
ALTER TABLE DebtorsMaster ADD INDEX (EDIOrders);

CREATE TABLE EDIItemMapping (
  SuppOrCust varchar(4) NOT NULL default '',
  PartnerCode varchar(10) NOT NULL default '',
  StockID varchar(20) NOT NULL default '',
  PartnerStockID varchar(50) NOT NULL default '',
  PRIMARY KEY  (SuppOrCust,PartnerCode,StockID),
  KEY PartnerCode (PartnerCode),
  KEY StockID (StockID),
  KEY PartnerStockID (PartnerStockID),
  KEY SuppOrCust (SuppOrCust)
) TYPE=Innodb;

CREATE TABLE EDIMessageFormat (
  PartnerCode varchar(10) NOT NULL default '',
  MessageType varchar(6) NOT NULL default '',
  Section varchar(7) NOT NULL default '',
  SequenceNo int(11) NOT NULL default '0',
  LineText varchar(70) NOT NULL default '',
  PRIMARY KEY  (PartnerCode,MessageType,SequenceNo),
  KEY Section (Section)
) TYPE=Innodb;


INSERT INTO EDIMessageFormat VALUES ('DEFAULT', 'INVOIC', 'Heading', 10, 'UNH+[EDITransNo]+INVOIC:D:96A:UN:EAN008\'');
INSERT INTO EDIMessageFormat VALUES ('DEFAULT', 'INVOIC', 'Heading', 20, 'BGM+[InvOrCrd]+[TransNo]+[OrigOrDup]\'');
INSERT INTO EDIMessageFormat VALUES ('DEFAULT', 'INVOIC', 'Heading', 30, 'DTM+137:[TranDate]:102\'');
INSERT INTO EDIMessageFormat VALUES ('DEFAULT', 'INVOIC', 'Heading', 60, 'RFF+ON:[OrderNo]\'');
INSERT INTO EDIMessageFormat VALUES ('DEFAULT', 'INVOIC', 'Heading', 70, 'NAD+BY+[CustBranchCode]::92\'');
INSERT INTO EDIMessageFormat VALUES ('DEFAULT', 'INVOIC', 'Heading', 80, 'NAD+SU+[CompanyEDIReference]::91\'');
INSERT INTO EDIMessageFormat VALUES ('DEFAULT', 'INVOIC', 'Heading', 90, 'NAD+UD++[BranchName]+[BranchStreet]+[BranchCity]+[BranchState]\'');
INSERT INTO EDIMessageFormat VALUES ('DEFAULT', 'INVOIC', 'Heading', 100, 'RFF+AMT:[TaxAuthorityRef]\'');
INSERT INTO EDIMessageFormat VALUES ('DEFAULT', 'INVOIC', 'Heading', 110, 'PAT+1++5:3:D:30\'');
INSERT INTO EDIMessageFormat VALUES ('DEFAULT', 'INVOIC', 'Heading', 120, 'DTM+13:[DatePaymentDue]:102\'');
INSERT INTO EDIMessageFormat VALUES ('DEFAULT', 'INVOIC', 'Heading', 130, 'TAX+7+GST+++:::10\'');
INSERT INTO EDIMessageFormat VALUES ('DEFAULT', 'INVOIC', 'Heading', 150, 'MOA+124:[TaxTotal]\'');
INSERT INTO EDIMessageFormat VALUES ('DEFAULT', 'INVOIC', 'Detail', 160, 'LIN+[LineNumber]\'');
INSERT INTO EDIMessageFormat VALUES ('DEFAULT', 'INVOIC', 'Detail', 170, 'PIA+5+[StockID]:SA+[CustStockID]:IN\'');
INSERT INTO EDIMessageFormat VALUES ('DEFAULT', 'INVOIC', 'Detail', 180, 'IMD+F++:::[ItemDescription]\'');
INSERT INTO EDIMessageFormat VALUES ('DEFAULT', 'INVOIC', 'Heading', 85, 'NAD+IV+[CustEDIReference]::9\'');
INSERT INTO EDIMessageFormat VALUES ('DEFAULT', 'INVOIC', 'Detail', 200, 'QTY+47:[QtyInvoiced]\'');
INSERT INTO EDIMessageFormat VALUES ('DEFAULT', 'INVOIC', 'Detail', 220, 'MOA+128:[LineTotalExclTax]\'');
INSERT INTO EDIMessageFormat VALUES ('DEFAULT', 'INVOIC', 'Detail', 230, 'PRI+AAA:[UnitPrice]\'');
INSERT INTO EDIMessageFormat VALUES ('DEFAULT', 'INVOIC', 'Detail', 240, 'TAX+7+GST+++:::10\'');
INSERT INTO EDIMessageFormat VALUES ('DEFAULT', 'INVOIC', 'Detail', 250, 'MOA+124:[LineTaxAmount]\'');
INSERT INTO EDIMessageFormat VALUES ('DEFAULT', 'INVOIC', 'Summary', 260, 'UNS+S\'');
INSERT INTO EDIMessageFormat VALUES ('DEFAULT', 'INVOIC', 'Summary', 270, 'CNT+2:[NoLines]\'');
INSERT INTO EDIMessageFormat VALUES ('DEFAULT', 'INVOIC', 'Summary', 280, 'MOA+128:[TotalAmountExclTax]\'');
INSERT INTO EDIMessageFormat VALUES ('DEFAULT', 'INVOIC', 'Summary', 290, 'TAX+7+GST+++:::10\'');
INSERT INTO EDIMessageFormat VALUES ('DEFAULT', 'INVOIC', 'Summary', 300, 'MOA+128:[TaxTotal]\'');
INSERT INTO EDIMessageFormat VALUES ('DEFAULT', 'INVOIC', 'Summary', 310, 'UNT+[NoSegments]+[EDITransNo]\'');

ALTER TABLE AccountGroups Type=Innodb;
ALTER TABLE Areas Type=Innodb;
ALTER TABLE BOM Type=Innodb;
ALTER TABLE BankAccounts Type=Innodb;
ALTER TABLE BankTrans Type=Innodb;
ALTER TABLE Buckets Type=Innodb;
ALTER TABLE COGSGLPostings Type=Innodb;
ALTER TABLE ChartMaster Type=Innodb;
ALTER TABLE Companies Type=Innodb;
ALTER TABLE ContractBOM Type=Innodb;
ALTER TABLE ContractReqts Type=Innodb;
ALTER TABLE Contracts Type=Innodb;
ALTER TABLE Currencies Type=Innodb;
ALTER TABLE CustBranch Type=Innodb;
ALTER TABLE DebtorsMaster Type=Innodb;
ALTER TABLE DiscountMatrix Type=Innodb;
ALTER TABLE FreightCosts Type=Innodb;
ALTER TABLE HoldReasons Type=Innodb;
ALTER TABLE LastCostRollUp Type=Innodb;
ALTER TABLE PaymentTerms Type=Innodb;
ALTER TABLE Prices Type=Innodb;
ALTER TABLE PurchData Type=Innodb;
ALTER TABLE ReportColumns Type=Innodb;
ALTER TABLE ReportHeaders Type=Innodb;
ALTER TABLE SalesGLPostings Type=Innodb;
ALTER TABLE SalesTypes Type=Innodb;
ALTER TABLE Salesman Type=Innodb;
ALTER TABLE ShipmentCharges Type=Innodb;
ALTER TABLE Shippers Type=Innodb;
ALTER TABLE StockCategory Type=Innodb;
ALTER TABLE StockCheckFreeze Type=Innodb;
ALTER TABLE StockCounts Type=Innodb;
ALTER TABLE SupplierContacts Type=Innodb;
ALTER TABLE Suppliers Type=Innodb;
ALTER TABLE TaxAuthorities Type=Innodb;
ALTER TABLE WORequirements Type=Innodb;
ALTER TABLE WWW_Users Type=Innodb;
ALTER TABLE WorkCentres Type=Innodb;
ALTER TABLE Locations Type=Innodb;


ALTER TABLE TaxAuthLevels ADD FOREIGN KEY (TaxAuthority) REFERENCES TaxAuthorities (TaxID);
ALTER TABLE TaxAuthLevels ADD FOREIGN KEY (DispatchTaxAuthority) REFERENCES TaxAuthorities (TaxID);

ALTER TABLE BOM ADD FOREIGN KEY (Parent) REFERENCES StockMaster (StockID);
ALTER TABLE BOM ADD FOREIGN KEY (Component) REFERENCES StockMaster (StockID);
ALTER TABLE BOM ADD FOREIGN KEY (WorkCentreAdded) REFERENCES WorkCentres (Code);
ALTER TABLE BOM ADD FOREIGN KEY (LocCode) REFERENCES Locations (LocCode);

ALTER TABLE BankAccounts ADD FOREIGN KEY (AccountCode) REFERENCES ChartMaster (AccountCode);


ALTER TABLE BankTrans ADD FOREIGN KEY (Type) REFERENCES SysTypes (TypeID);
ALTER TABLE BankTrans ADD FOREIGN KEY (BankAct) REFERENCES BankAccounts (AccountCode);


ALTER TABLE Buckets ADD FOREIGN KEY (WorkCentre) REFERENCES WorkCentres (Code);

ALTER TABLE ChartDetails ADD FOREIGN KEY (AccountCode) REFERENCES ChartMaster (AccountCode);
ALTER TABLE ChartDetails ADD FOREIGN KEY (Period) REFERENCES Periods (PeriodNo);


ALTER TABLE ChartMaster ADD FOREIGN KEY (Group_) REFERENCES AccountGroups (GroupName);


ALTER TABLE ContractBOM ADD INDEX (WorkCentreAdded);

ALTER TABLE ContractBOM ADD FOREIGN KEY (WorkCentreAdded) REFERENCES WorkCentres (Code);

ALTER TABLE ContractBOM ADD FOREIGN KEY (LocCode) REFERENCES Locations (LocCode);
ALTER TABLE ContractBOM ADD FOREIGN KEY (Component) REFERENCES StockMaster (StockID);


ALTER TABLE ContractReqts ADD FOREIGN KEY (Contract) REFERENCES Contracts (ContractRef);


ALTER TABLE `Contracts` DROP INDEX `DebtorNo` , DROP INDEX `BranchCode`, ADD INDEX `DebtorNo` ( `DebtorNo` , `BranchCode` );

ALTER TABLE Contracts ADD FOREIGN KEY (DebtorNo, BranchCode) REFERENCES CustBranch (DebtorNo, BranchCode);
ALTER TABLE Contracts ADD FOREIGN KEY (CategoryID) REFERENCES StockCategory (CategoryID);

ALTER TABLE Contracts ADD FOREIGN KEY (TypeAbbrev) REFERENCES SalesTypes (TypeAbbrev);


ALTER TABLE CustAllocns ADD FOREIGN KEY (TransID_AllocFrom) REFERENCES DebtorTrans (ID);
ALTER TABLE CustAllocns ADD FOREIGN KEY (TransID_AllocTo) REFERENCES DebtorTrans (ID);

ALTER TABLE CustBranch ADD FOREIGN KEY (DebtorNo) REFERENCES DebtorsMaster (DebtorNo);

ALTER TABLE CustBranch ADD INDEX (Area);
ALTER TABLE CustBranch ADD FOREIGN KEY (Area) REFERENCES Areas (AreaCode);
ALTER TABLE CustBranch ADD FOREIGN KEY (Salesman) REFERENCES Salesman (SalesmanCode);

ALTER TABLE `CustBranch` ADD INDEX ( `DefaultLocation` );
ALTER TABLE `CustBranch` ADD INDEX ( `TaxAuthority` );
ALTER TABLE `CustBranch` ADD INDEX ( `DefaultShipVia` );

ALTER TABLE CustBranch ADD FOREIGN KEY (DefaultLocation) REFERENCES Locations (LocCode);

ALTER TABLE `CustBranch` CHANGE `TaxAuthority` `TaxAuthority` TINYINT DEFAULT '1' NOT NULL;

ALTER TABLE CustBranch ADD FOREIGN KEY (TaxAuthority) REFERENCES TaxAuthorities (TaxID);
ALTER TABLE CustBranch ADD FOREIGN KEY (DefaultShipVia) REFERENCES Shippers (Shipper_ID);

ALTER TABLE DebtorTrans ADD FOREIGN KEY (DebtorNo) REFERENCES CustBranch (DebtorNo);
ALTER TABLE DebtorTrans ADD FOREIGN KEY (Type) REFERENCES SysTypes (TypeID);
ALTER TABLE DebtorTrans ADD FOREIGN KEY (Prd) REFERENCES Periods (PeriodNo);

ALTER TABLE DebtorsMaster ADD FOREIGN KEY (HoldReason) REFERENCES HoldReasons (ReasonCode);
ALTER TABLE `DebtorsMaster` CHANGE `CurrCode` `CurrCode` VARCHAR( 3 ) NOT NULL;

ALTER TABLE DebtorsMaster ADD FOREIGN KEY (CurrCode) REFERENCES Currencies (CurrAbrev);
ALTER TABLE DebtorsMaster ADD FOREIGN KEY (PaymentTerms) REFERENCES PaymentTerms (TermsIndicator);
ALTER TABLE DebtorsMaster ADD FOREIGN KEY (SalesType) REFERENCES SalesTypes (TypeAbbrev);
ALTER TABLE DiscountMatrix ADD FOREIGN KEY (SalesType) REFERENCES SalesTypes (TypeAbbrev);
ALTER TABLE EDIItemMapping ADD FOREIGN KEY (StockID) REFERENCES StockMaster (StockID);
ALTER TABLE FreightCosts ADD FOREIGN KEY (LocationFrom) REFERENCES Locations (LocCode);

ALTER TABLE FreightCosts ADD FOREIGN KEY (ShipperID) REFERENCES Shippers (Shipper_ID);
ALTER TABLE GLTrans ADD FOREIGN KEY (Account) REFERENCES ChartMaster (AccountCode);
ALTER TABLE GLTrans ADD FOREIGN KEY (Type) REFERENCES SysTypes (TypeID);
ALTER TABLE GLTrans ADD FOREIGN KEY (PeriodNo) REFERENCES Periods (PeriodNo);
ALTER TABLE GRNs ADD FOREIGN KEY (SupplierID) REFERENCES Suppliers (SupplierID);
ALTER TABLE GRNs ADD FOREIGN KEY (PODetailItem) REFERENCES PurchOrderDetails (PODetailItem);

ALTER TABLE LocStock ADD FOREIGN KEY (LocCode) REFERENCES Locations (LocCode);
ALTER TABLE LocStock ADD FOREIGN KEY (StockID) REFERENCES StockMaster (StockID);

ALTER TABLE OrderDeliveryDifferencesLog ADD FOREIGN KEY (StockID) REFERENCES StockMaster (StockID);

ALTER TABLE OrderDeliveryDifferencesLog ADD FOREIGN KEY (DebtorNo,Branch) REFERENCES CustBranch (DebtorNo,BranchCode);
ALTER TABLE `OrderDeliveryDifferencesLog` ADD INDEX ( `OrderNo` );
ALTER TABLE OrderDeliveryDifferencesLog ADD FOREIGN KEY (OrderNo) REFERENCES SalesOrders (OrderNo);

ALTER TABLE Prices ADD FOREIGN KEY (StockID) REFERENCES StockMaster (StockID);
ALTER TABLE Prices ADD FOREIGN KEY (CurrAbrev) REFERENCES Currencies (CurrAbrev);
ALTER TABLE Prices ADD FOREIGN KEY (TypeAbbrev) REFERENCES SalesTypes (TypeAbbrev);

ALTER TABLE PurchData ADD FOREIGN KEY (StockID) REFERENCES StockMaster (StockID);
ALTER TABLE PurchData ADD FOREIGN KEY (SupplierNo) REFERENCES Suppliers (SupplierID);

ALTER TABLE PurchOrderDetails ADD FOREIGN KEY (OrderNo) REFERENCES PurchOrders (OrderNo);

ALTER TABLE PurchOrders ADD FOREIGN KEY (SupplierNo) REFERENCES Suppliers (SupplierID);
ALTER TABLE PurchOrders ADD FOREIGN KEY (IntoStockLocation) REFERENCES Locations (LocCode);

ALTER TABLE ReportColumns ADD FOREIGN KEY (ReportID) REFERENCES ReportHeaders (ReportID);

ALTER TABLE `SalesAnalysis` CHANGE `PeriodNo` `PeriodNo` SMALLINT( 6 ) DEFAULT '0' NOT NULL ;
ALTER TABLE SalesAnalysis ADD FOREIGN KEY (PeriodNo) REFERENCES Periods (PeriodNo);

ALTER TABLE SalesOrderDetails ADD FOREIGN KEY (OrderNo) REFERENCES SalesOrders (OrderNo);
ALTER TABLE SalesOrderDetails ADD FOREIGN KEY (StkCode) REFERENCES StockMaster (StockID);

ALTER TABLE `SalesOrders` DROP INDEX `BranchCode`;
ALTER TABLE `SalesOrders` ADD INDEX ( `BranchCode`,`DebtorNo` );

ALTER TABLE SalesOrders ADD FOREIGN KEY (BranchCode, DebtorNo) REFERENCES CustBranch (BranchCode, DebtorNo);
ALTER TABLE `SalesOrders` ADD INDEX ( `ShipVia` );
ALTER TABLE SalesOrders ADD FOREIGN KEY (ShipVia) REFERENCES Shippers (Shipper_ID);
ALTER TABLE SalesOrders ADD FOREIGN KEY (FromStkLoc) REFERENCES Locations (LocCode);

ALTER TABLE Shipments CHANGE ShiptRef ShiptRef INT(11) NOT NULL;
ALTER TABLE `ShipmentCharges` CHANGE `ShiptRef` `ShiptRef` INT( 11 ) NOT NULL;
ALTER TABLE ShipmentCharges ADD FOREIGN KEY (ShiptRef) REFERENCES Shipments (ShiptRef);
ALTER TABLE `ShipmentCharges` ADD INDEX ( `TransType` );
ALTER TABLE ShipmentCharges ADD FOREIGN KEY (TransType) REFERENCES SysTypes (TypeID);
ALTER TABLE ShipmentCharges ADD FOREIGN KEY (StockID) REFERENCES StockMaster (StockID);

ALTER TABLE `Shipments` ADD FOREIGN KEY (SupplierID) REFERENCES Suppliers (SupplierID);

ALTER TABLE `StockCheckFreeze` ADD FOREIGN KEY (StockID) REFERENCES StockMaster (StockID);
ALTER TABLE `StockCheckFreeze` ADD FOREIGN KEY (LocCode) REFERENCES Locations (LocCode);

ALTER TABLE `StockCounts` ADD FOREIGN KEY (StockID) REFERENCES StockMaster (StockID);
ALTER TABLE `StockCounts` ADD FOREIGN KEY (LocCode) REFERENCES Locations (LocCode);

ALTER TABLE `StockMaster` ADD FOREIGN KEY (CategoryID) REFERENCES StockCategory (CategoryID);

ALTER TABLE `StockMoves` ADD FOREIGN KEY (StockID) REFERENCES StockMaster (StockID);
ALTER TABLE `StockMoves` ADD FOREIGN KEY (Type) REFERENCES SysTypes (TypeID);
ALTER TABLE `StockMoves` ADD FOREIGN KEY (LocCode) REFERENCES Locations (LocCode);
ALTER TABLE `StockMoves` ADD FOREIGN KEY (Prd) REFERENCES Periods (PeriodNo);

DELETE FROM SuppAllocs WHERE ID=4;

ALTER TABLE SuppAllocs ADD FOREIGN KEY (TransID_AllocFrom) REFERENCES SuppTrans (ID);
ALTER TABLE `SuppAllocs` ADD FOREIGN KEY (TransID_AllocTo) REFERENCES SuppTrans (ID);

ALTER TABLE `SuppTrans` ADD FOREIGN KEY (Type) REFERENCES SysTypes (TypeID);
ALTER TABLE `SuppTrans` ADD FOREIGN KEY (SupplierNo) REFERENCES Suppliers (SupplierID);
ALTER TABLE `SupplierContacts` ADD FOREIGN KEY (SupplierID) REFERENCES Suppliers (SupplierID);
ALTER TABLE `Suppliers` ADD FOREIGN KEY (CurrCode) REFERENCES Currencies (CurrAbrev);
ALTER TABLE `Suppliers` ADD FOREIGN KEY (PaymentTerms) REFERENCES PaymentTerms (TermsIndicator);

ALTER TABLE `Suppliers` CHANGE `TaxAuthority` `TaxAuthority` TINYINT DEFAULT '1' NOT NULL;

ALTER TABLE `Suppliers` ADD FOREIGN KEY (TaxAuthority) REFERENCES TaxAuthorities (TaxID);
ALTER TABLE `WOIssues` ADD FOREIGN KEY (WORef) REFERENCES WorksOrders (WORef);
ALTER TABLE `WOIssues` ADD FOREIGN KEY (StockID) REFERENCES StockMaster (StockID);
ALTER TABLE `WOIssues` ADD FOREIGN KEY (WorkCentre) REFERENCES WorkCentres (Code);

ALTER TABLE `WORequirements` ADD FOREIGN KEY (WORef) REFERENCES WorksOrders (WORef);
ALTER TABLE `WORequirements` ADD FOREIGN KEY (StockID) REFERENCES StockMaster (StockID);
ALTER TABLE `WORequirements` ADD FOREIGN KEY (WrkCentre) REFERENCES WorkCentres (Code);

ALTER TABLE `WWW_Users` ADD INDEX ( `DefaultLocation` );

ALTER TABLE `WWW_Users` ADD FOREIGN KEY (DefaultLocation) REFERENCES Locations (LocCode);

ALTER TABLE `WorkCentres` ADD FOREIGN KEY (Location) REFERENCES Locations (LocCode);

ALTER TABLE `WorksOrders` ADD FOREIGN KEY (LocCode) REFERENCES Locations (LocCode);
ALTER TABLE `WorksOrders` ADD FOREIGN KEY (StockID) REFERENCES StockMaster (StockID);

ALTER TABLE DebtorsMaster ADD DiscountCode char(2) NOT NULL default '';

commit;
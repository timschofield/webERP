SET FOREIGN_KEY_CHECKS = 0;
-- MySQL dump 9.10
--
-- Host: localhost    Database: weberp
-- ------------------------------------------------------
-- Server version	4.0.18-standard

--
-- Current Database: weberp
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ weberp;

USE weberp;

--
-- Table structure for table `AccountGroups`
--

DROP TABLE IF EXISTS AccountGroups;
CREATE TABLE AccountGroups (
  GroupName char(30) NOT NULL default '',
  SectionInAccounts smallint(6) NOT NULL default '0',
  PandL tinyint(4) NOT NULL default '1',
  SequenceInTB smallint(6) NOT NULL default '0',
  PRIMARY KEY  (GroupName),
  KEY SequenceInTB (SequenceInTB)
) TYPE=InnoDB;

--
-- Table structure for table `Areas`
--

DROP TABLE IF EXISTS Areas;
CREATE TABLE Areas (
  AreaCode char(2) NOT NULL default '',
  AreaDescription varchar(25) NOT NULL default '',
  PRIMARY KEY  (AreaCode)
) TYPE=InnoDB;

--
-- Table structure for table `BOM`
--

DROP TABLE IF EXISTS BOM;
CREATE TABLE BOM (
  Parent char(20) NOT NULL default '',
  Component char(20) NOT NULL default '',
  WorkCentreAdded char(5) NOT NULL default '',
  LocCode char(5) NOT NULL default '',
  EffectiveAfter date NOT NULL default '0000-00-00',
  EffectiveTo date NOT NULL default '9999-12-31',
  Quantity double(16,4) NOT NULL default '1.0000',
  PRIMARY KEY  (Parent,Component,WorkCentreAdded,LocCode),
  KEY Component (Component),
  KEY EffectiveAfter (EffectiveAfter),
  KEY EffectiveTo (EffectiveTo),
  KEY LocCode (LocCode),
  KEY Parent (Parent,EffectiveAfter,EffectiveTo,LocCode),
  KEY Parent_2 (Parent),
  KEY WorkCentreAdded (WorkCentreAdded),
  CONSTRAINT `BOM_ibfk_1` FOREIGN KEY (`Parent`) REFERENCES `StockMaster` (`StockID`),
  CONSTRAINT `BOM_ibfk_2` FOREIGN KEY (`Component`) REFERENCES `StockMaster` (`StockID`),
  CONSTRAINT `BOM_ibfk_3` FOREIGN KEY (`WorkCentreAdded`) REFERENCES `WorkCentres` (`Code`),
  CONSTRAINT `BOM_ibfk_4` FOREIGN KEY (`LocCode`) REFERENCES `Locations` (`LocCode`)
) TYPE=InnoDB;

--
-- Table structure for table `BankAccounts`
--

DROP TABLE IF EXISTS BankAccounts;
CREATE TABLE BankAccounts (
  AccountCode int(11) NOT NULL default '0',
  BankAccountName char(50) NOT NULL default '',
  BankAccountNumber char(50) NOT NULL default '',
  BankAddress char(50) default NULL,
  PRIMARY KEY  (AccountCode),
  KEY BankAccountName (BankAccountName),
  KEY BankAccountNumber (BankAccountNumber),
  CONSTRAINT `BankAccounts_ibfk_1` FOREIGN KEY (`AccountCode`) REFERENCES `ChartMaster` (`AccountCode`)
) TYPE=InnoDB;

--
-- Table structure for table `BankTrans`
--

DROP TABLE IF EXISTS BankTrans;
CREATE TABLE BankTrans (
  BankTransID bigint(20) NOT NULL auto_increment,
  Type smallint(6) NOT NULL default '0',
  TransNo bigint(20) NOT NULL default '0',
  BankAct int(11) NOT NULL default '0',
  Ref varchar(50) NOT NULL default '',
  AmountCleared float NOT NULL default '0',
  ExRate double NOT NULL default '1',
  TransDate date NOT NULL default '0000-00-00',
  BankTransType varchar(30) NOT NULL default '',
  Amount float NOT NULL default '0',
  CurrCode char(3) NOT NULL default '',
  PRIMARY KEY  (BankTransID),
  KEY BankAct (BankAct,Ref),
  KEY TransDate (TransDate),
  KEY TransType (BankTransType),
  KEY Type (Type,TransNo),
  KEY CurrCode (CurrCode),
  CONSTRAINT `BankTrans_ibfk_1` FOREIGN KEY (`Type`) REFERENCES `SysTypes` (`TypeID`),
  CONSTRAINT `BankTrans_ibfk_2` FOREIGN KEY (`BankAct`) REFERENCES `BankAccounts` (`AccountCode`)
) TYPE=InnoDB;

--
-- Table structure for table `Buckets`
--

DROP TABLE IF EXISTS Buckets;
CREATE TABLE Buckets (
  WorkCentre char(5) NOT NULL default '',
  AvailDate datetime NOT NULL default '0000-00-00 00:00:00',
  Capacity float(10,2) NOT NULL default '0.00',
  PRIMARY KEY  (WorkCentre,AvailDate),
  KEY WorkCentre (WorkCentre),
  KEY AvailDate (AvailDate),
  CONSTRAINT `Buckets_ibfk_1` FOREIGN KEY (`WorkCentre`) REFERENCES `WorkCentres` (`Code`)
) TYPE=InnoDB;

--
-- Table structure for table `COGSGLPostings`
--

DROP TABLE IF EXISTS COGSGLPostings;
CREATE TABLE COGSGLPostings (
  ID int(11) NOT NULL auto_increment,
  Area char(2) NOT NULL default '',
  StkCat varchar(6) NOT NULL default '',
  GLCode int(11) NOT NULL default '0',
  SalesType char(2) NOT NULL default 'AN',
  PRIMARY KEY  (ID),
  UNIQUE KEY Area_StkCat (Area,StkCat,SalesType),
  KEY Area (Area),
  KEY StkCat (StkCat),
  KEY GLCode (GLCode),
  KEY SalesType (SalesType)
) TYPE=InnoDB;

--
-- Table structure for table `ChartDetails`
--

DROP TABLE IF EXISTS ChartDetails;
CREATE TABLE ChartDetails (
  AccountCode int(11) NOT NULL default '0',
  Period smallint(6) NOT NULL default '0',
  Budget float NOT NULL default '0',
  Actual float NOT NULL default '0',
  BFwd float NOT NULL default '0',
  BFwdBudget float NOT NULL default '0',
  PRIMARY KEY  (AccountCode,Period),
  KEY Period (Period),
  CONSTRAINT `ChartDetails_ibfk_1` FOREIGN KEY (`AccountCode`) REFERENCES `ChartMaster` (`AccountCode`),
  CONSTRAINT `ChartDetails_ibfk_2` FOREIGN KEY (`Period`) REFERENCES `Periods` (`PeriodNo`)
) TYPE=InnoDB;

--
-- Table structure for table `ChartMaster`
--

DROP TABLE IF EXISTS ChartMaster;
CREATE TABLE ChartMaster (
  AccountCode int(11) NOT NULL default '0',
  AccountName char(50) NOT NULL default '',
  Group_ char(30) NOT NULL default '',
  PRIMARY KEY  (AccountCode),
  KEY AccountCode (AccountCode),
  KEY AccountName (AccountName),
  KEY Group_ (Group_),
  CONSTRAINT `ChartMaster_ibfk_1` FOREIGN KEY (`Group_`) REFERENCES `AccountGroups` (`GroupName`)
) TYPE=InnoDB;

--
-- Table structure for table `Companies`
--

DROP TABLE IF EXISTS Companies;
CREATE TABLE Companies (
  CoyCode int(11) NOT NULL default '1',
  CoyName varchar(50) NOT NULL default '',
  GSTNo varchar(20) NOT NULL default '',
  CompanyNumber varchar(20) NOT NULL default '0',
  PostalAddress varchar(50) NOT NULL default '',
  RegOffice1 varchar(50) NOT NULL default '',
  RegOffice2 varchar(50) NOT NULL default '',
  RegOffice3 varchar(50) NOT NULL default '',
  Telephone varchar(25) NOT NULL default '',
  Fax varchar(25) NOT NULL default '',
  Email varchar(55) NOT NULL default '',
  CurrencyDefault varchar(4) NOT NULL default '',
  DebtorsAct int(11) NOT NULL default '70000',
  PytDiscountAct int(11) NOT NULL default '55000',
  CreditorsAct int(11) NOT NULL default '80000',
  PayrollAct int(11) NOT NULL default '84000',
  GRNAct int(11) NOT NULL default '72000',
  ExchangeDiffAct int(11) NOT NULL default '65000',
  PurchasesExchangeDiffAct int(11) NOT NULL default '0',
  RetainedEarnings int(11) NOT NULL default '90000',
  GLLink_Debtors tinyint(1) default '1',
  GLLink_Creditors tinyint(1) default '1',
  GLLink_Stock tinyint(1) default '1',
  FreightAct int(11) NOT NULL default '0',
  PRIMARY KEY  (CoyCode)
) TYPE=InnoDB;

--
-- Table structure for table `ContractBOM`
--

DROP TABLE IF EXISTS ContractBOM;
CREATE TABLE ContractBOM (
  ContractRef char(20) NOT NULL default '',
  Component char(20) NOT NULL default '',
  WorkCentreAdded char(5) NOT NULL default '',
  LocCode char(5) NOT NULL default '',
  Quantity double(16,4) NOT NULL default '1.0000',
  PRIMARY KEY  (ContractRef,Component,WorkCentreAdded,LocCode),
  KEY Component (Component),
  KEY LocCode (LocCode),
  KEY ContractRef (ContractRef),
  KEY WorkCentreAdded (WorkCentreAdded),
  KEY WorkCentreAdded_2 (WorkCentreAdded),
  CONSTRAINT `ContractBOM_ibfk_1` FOREIGN KEY (`WorkCentreAdded`) REFERENCES `WorkCentres` (`Code`),
  CONSTRAINT `ContractBOM_ibfk_2` FOREIGN KEY (`LocCode`) REFERENCES `Locations` (`LocCode`),
  CONSTRAINT `ContractBOM_ibfk_3` FOREIGN KEY (`Component`) REFERENCES `StockMaster` (`StockID`)
) TYPE=InnoDB;

--
-- Table structure for table `ContractReqts`
--

DROP TABLE IF EXISTS ContractReqts;
CREATE TABLE ContractReqts (
  ContractReqID int(11) NOT NULL auto_increment,
  Contract char(20) NOT NULL default '',
  Component char(40) NOT NULL default '',
  Quantity double(16,4) NOT NULL default '1.0000',
  PricePerUnit decimal(20,4) NOT NULL default '0.0000',
  PRIMARY KEY  (ContractReqID),
  KEY Contract (Contract),
  CONSTRAINT `ContractReqts_ibfk_1` FOREIGN KEY (`Contract`) REFERENCES `Contracts` (`ContractRef`)
) TYPE=InnoDB;

--
-- Table structure for table `Contracts`
--

DROP TABLE IF EXISTS Contracts;
CREATE TABLE Contracts (
  ContractRef varchar(20) NOT NULL default '',
  ContractDescription varchar(50) NOT NULL default '',
  DebtorNo varchar(10) NOT NULL default '',
  BranchCode varchar(10) NOT NULL default '',
  Status varchar(10) NOT NULL default 'Quotation',
  CategoryID varchar(6) NOT NULL default '',
  TypeAbbrev char(2) NOT NULL default '',
  OrderNo int(11) NOT NULL default '0',
  QuotedPriceFX decimal(20,4) NOT NULL default '0.0000',
  Margin double(16,4) NOT NULL default '1.0000',
  WORef varchar(20) NOT NULL default '',
  RequiredDate datetime NOT NULL default '0000-00-00 00:00:00',
  CancelDate datetime NOT NULL default '0000-00-00 00:00:00',
  QuantityReqd double(16,4) NOT NULL default '1.0000',
  Specifications longblob NOT NULL,
  DateQuoted datetime NOT NULL default '0000-00-00 00:00:00',
  Units varchar(15) NOT NULL default 'Each',
  Drawing longblob NOT NULL,
  Rate double(16,4) NOT NULL default '1.0000',
  PRIMARY KEY  (ContractRef),
  KEY OrderNo (OrderNo),
  KEY CategoryID (CategoryID),
  KEY Status (Status),
  KEY TypeAbbrev (TypeAbbrev),
  KEY WORef (WORef),
  KEY DebtorNo (DebtorNo,BranchCode),
  CONSTRAINT `Contracts_ibfk_1` FOREIGN KEY (`DebtorNo`, `BranchCode`) REFERENCES `CustBranch` (`DebtorNo`, `BranchCode`),
  CONSTRAINT `Contracts_ibfk_2` FOREIGN KEY (`CategoryID`) REFERENCES `StockCategory` (`CategoryID`),
  CONSTRAINT `Contracts_ibfk_3` FOREIGN KEY (`TypeAbbrev`) REFERENCES `SalesTypes` (`TypeAbbrev`)
) TYPE=InnoDB;

--
-- Table structure for table `Currencies`
--

DROP TABLE IF EXISTS Currencies;
CREATE TABLE Currencies (
  Currency char(20) NOT NULL default '',
  CurrAbrev char(3) NOT NULL default '',
  Country char(50) NOT NULL default '',
  HundredsName char(15) NOT NULL default 'Cents',
  Rate double(16,4) NOT NULL default '1.0000',
  PRIMARY KEY  (CurrAbrev),
  KEY Country (Country)
) TYPE=InnoDB;

--
-- Table structure for table `CustAllocns`
--

DROP TABLE IF EXISTS CustAllocns;
CREATE TABLE CustAllocns (
  ID int(11) NOT NULL auto_increment,
  Amt decimal(20,4) NOT NULL default '0.0000',
  DateAlloc date NOT NULL default '0000-00-00',
  TransID_AllocFrom int(11) NOT NULL default '0',
  TransID_AllocTo int(11) NOT NULL default '0',
  PRIMARY KEY  (ID),
  KEY DateAlloc (DateAlloc),
  KEY TransID_AllocFrom (TransID_AllocFrom),
  KEY TransID_AllocTo (TransID_AllocTo),
  CONSTRAINT `CustAllocns_ibfk_1` FOREIGN KEY (`TransID_AllocFrom`) REFERENCES `DebtorTrans` (`ID`),
  CONSTRAINT `CustAllocns_ibfk_2` FOREIGN KEY (`TransID_AllocTo`) REFERENCES `DebtorTrans` (`ID`)
) TYPE=InnoDB;

--
-- Table structure for table `CustBranch`
--

DROP TABLE IF EXISTS CustBranch;
CREATE TABLE CustBranch (
  BranchCode varchar(10) NOT NULL default '',
  DebtorNo varchar(10) NOT NULL default '',
  BrName varchar(40) NOT NULL default '',
  BrAddress1 varchar(40) NOT NULL default '',
  BrAddress2 varchar(40) NOT NULL default '',
  BrAddress3 varchar(40) NOT NULL default '',
  BrAddress4 varchar(50) NOT NULL default '',
  EstDeliveryDays smallint(6) NOT NULL default '1',
  Area char(2) NOT NULL default '',
  Salesman varchar(4) NOT NULL default '',
  FwdDate smallint(6) NOT NULL default '0',
  PhoneNo varchar(20) NOT NULL default '',
  FaxNo varchar(20) NOT NULL default '',
  ContactName varchar(30) NOT NULL default '',
  Email varchar(55) NOT NULL default '',
  DefaultLocation varchar(5) NOT NULL default '',
  TaxAuthority tinyint(4) NOT NULL default '1',
  DefaultShipVia int(11) NOT NULL default '1',
  DisableTrans tinyint(4) NOT NULL default '0',
  BrPostAddr1 varchar(40) NOT NULL default '',
  BrPostAddr2 varchar(40) NOT NULL default '',
  BrPostAddr3 varchar(30) NOT NULL default '',
  BrPostAddr4 varchar(20) NOT NULL default '',
  CustBranchCode varchar(30) NOT NULL default '',
  PRIMARY KEY  (BranchCode,DebtorNo),
  KEY BranchCode (BranchCode),
  KEY BrName (BrName),
  KEY DebtorNo (DebtorNo),
  KEY Salesman (Salesman),
  KEY Area (Area),
  KEY Area_2 (Area),
  KEY DefaultLocation (DefaultLocation),
  KEY TaxAuthority (TaxAuthority),
  KEY DefaultShipVia (DefaultShipVia),
  CONSTRAINT `CustBranch_ibfk_1` FOREIGN KEY (`DebtorNo`) REFERENCES `DebtorsMaster` (`DebtorNo`),
  CONSTRAINT `CustBranch_ibfk_2` FOREIGN KEY (`Area`) REFERENCES `Areas` (`AreaCode`),
  CONSTRAINT `CustBranch_ibfk_3` FOREIGN KEY (`Salesman`) REFERENCES `Salesman` (`SalesmanCode`),
  CONSTRAINT `CustBranch_ibfk_4` FOREIGN KEY (`DefaultLocation`) REFERENCES `Locations` (`LocCode`),
  CONSTRAINT `CustBranch_ibfk_5` FOREIGN KEY (`TaxAuthority`) REFERENCES `TaxAuthorities` (`TaxID`),
  CONSTRAINT `CustBranch_ibfk_6` FOREIGN KEY (`DefaultShipVia`) REFERENCES `Shippers` (`Shipper_ID`)
) TYPE=InnoDB;

--
-- Table structure for table `DebtorTrans`
--

DROP TABLE IF EXISTS DebtorTrans;
CREATE TABLE DebtorTrans (
  ID int(11) NOT NULL auto_increment,
  TransNo int(11) NOT NULL default '0',
  Type smallint(6) NOT NULL default '0',
  DebtorNo varchar(10) NOT NULL default '',
  BranchCode varchar(10) NOT NULL default '',
  TranDate datetime NOT NULL default '0000-00-00 00:00:00',
  Prd smallint(6) NOT NULL default '0',
  Settled tinyint(4) NOT NULL default '0',
  Reference varchar(20) NOT NULL default '',
  Tpe char(2) NOT NULL default '',
  Order_ int(11) NOT NULL default '0',
  Rate double(16,6) NOT NULL default '0.000000',
  OvAmount float NOT NULL default '0',
  OvGST float NOT NULL default '0',
  OvFreight float NOT NULL default '0',
  OvDiscount float NOT NULL default '0',
  DiffOnExch float NOT NULL default '0',
  Alloc float NOT NULL default '0',
  InvText text,
  ShipVia varchar(10) NOT NULL default '',
  EDISent tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (ID),
  KEY DebtorNo (DebtorNo,BranchCode),
  KEY Order_ (Order_),
  KEY Prd (Prd),
  KEY Tpe (Tpe),
  KEY Type (Type),
  KEY Settled (Settled),
  KEY TranDate (TranDate),
  KEY TransNo (TransNo),
  KEY Type_2 (Type,TransNo),
  KEY EDISent (EDISent),
  CONSTRAINT `DebtorTrans_ibfk_1` FOREIGN KEY (`DebtorNo`) REFERENCES `CustBranch` (`DebtorNo`),
  CONSTRAINT `DebtorTrans_ibfk_2` FOREIGN KEY (`Type`) REFERENCES `SysTypes` (`TypeID`),
  CONSTRAINT `DebtorTrans_ibfk_3` FOREIGN KEY (`Prd`) REFERENCES `Periods` (`PeriodNo`)
) TYPE=InnoDB;

--
-- Table structure for table `DebtorsMaster`
--

DROP TABLE IF EXISTS DebtorsMaster;
CREATE TABLE DebtorsMaster (
  DebtorNo varchar(10) NOT NULL default '',
  Name varchar(40) NOT NULL default '',
  Address1 varchar(40) NOT NULL default '',
  Address2 varchar(40) NOT NULL default '',
  Address3 varchar(40) NOT NULL default '',
  Address4 varchar(50) NOT NULL default '',
  CurrCode char(3) NOT NULL default '',
  SalesType char(2) NOT NULL default '',
  ClientSince datetime NOT NULL default '0000-00-00 00:00:00',
  HoldReason smallint(6) NOT NULL default '0',
  PaymentTerms char(2) NOT NULL default 'f',
  Discount double(16,4) NOT NULL default '0.0000',
  PymtDiscount double(16,4) NOT NULL default '0.0000',
  LastPaid double(16,4) NOT NULL default '0.0000',
  LastPaidDate datetime default NULL,
  CreditLimit float NOT NULL default '1000',
  InvAddrBranch tinyint(4) NOT NULL default '0',
  DiscountCode char(2) NOT NULL default '',
  EDIInvoices tinyint(4) NOT NULL default '0',
  EDIOrders tinyint(4) NOT NULL default '0',
  EDIReference varchar(20) NOT NULL default '',
  EDITransport varchar(5) NOT NULL default 'email',
  EDIAddress varchar(50) NOT NULL default '',
  EDIServerUser varchar(20) NOT NULL default '',
  EDIServerPwd varchar(20) NOT NULL default '',
  PRIMARY KEY  (DebtorNo),
  KEY Currency (CurrCode),
  KEY HoldReason (HoldReason),
  KEY Name (Name),
  KEY PaymentTerms (PaymentTerms),
  KEY SalesType (SalesType),
  KEY EDIInvoices (EDIInvoices),
  KEY EDIOrders (EDIOrders),
  CONSTRAINT `DebtorsMaster_ibfk_1` FOREIGN KEY (`HoldReason`) REFERENCES `HoldReasons` (`ReasonCode`),
  CONSTRAINT `DebtorsMaster_ibfk_2` FOREIGN KEY (`CurrCode`) REFERENCES `Currencies` (`CurrAbrev`),
  CONSTRAINT `DebtorsMaster_ibfk_3` FOREIGN KEY (`PaymentTerms`) REFERENCES `PaymentTerms` (`TermsIndicator`),
  CONSTRAINT `DebtorsMaster_ibfk_4` FOREIGN KEY (`SalesType`) REFERENCES `SalesTypes` (`TypeAbbrev`)
) TYPE=InnoDB;

--
-- Table structure for table `DiscountMatrix`
--

DROP TABLE IF EXISTS DiscountMatrix;
CREATE TABLE DiscountMatrix (
  SalesType char(2) NOT NULL default '',
  DiscountCategory char(2) NOT NULL default '',
  QuantityBreak int(11) NOT NULL default '1',
  DiscountRate double(16,4) NOT NULL default '0.0000',
  PRIMARY KEY  (SalesType,DiscountCategory,QuantityBreak),
  KEY QuantityBreak (QuantityBreak),
  KEY DiscountCategory (DiscountCategory),
  KEY SalesType (SalesType),
  CONSTRAINT `DiscountMatrix_ibfk_1` FOREIGN KEY (`SalesType`) REFERENCES `SalesTypes` (`TypeAbbrev`)
) TYPE=InnoDB;

--
-- Table structure for table `EDIItemMapping`
--

DROP TABLE IF EXISTS EDIItemMapping;
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
) TYPE=InnoDB;

--
-- Table structure for table `EDIMessageFormat`
--

DROP TABLE IF EXISTS EDIMessageFormat;
CREATE TABLE EDIMessageFormat (
  ID int(11) NOT NULL auto_increment,
  PartnerCode varchar(10) NOT NULL default '',
  MessageType varchar(6) NOT NULL default '',
  Section varchar(7) NOT NULL default '',
  SequenceNo int(11) NOT NULL default '0',
  LineText varchar(70) NOT NULL default '',
  PRIMARY KEY  (ID),
  UNIQUE KEY PartnerCode (PartnerCode,MessageType,SequenceNo),
  KEY Section (Section)
) TYPE=InnoDB;

--
-- Table structure for table `EDI_ORDERS_Seg_Groups`
--

DROP TABLE IF EXISTS EDI_ORDERS_Seg_Groups;
CREATE TABLE EDI_ORDERS_Seg_Groups (
  SegGroupNo tinyint(4) NOT NULL default '0',
  MaxOccur int(4) NOT NULL default '0',
  ParentSegGroup tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (SegGroupNo)
) TYPE=InnoDB;

--
-- Table structure for table `EDI_ORDERS_Segs`
--

DROP TABLE IF EXISTS EDI_ORDERS_Segs;
CREATE TABLE EDI_ORDERS_Segs (
  ID int(11) NOT NULL auto_increment,
  SegTag char(3) NOT NULL default '',
  SegGroup tinyint(4) NOT NULL default '0',
  MaxOccur tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (ID),
  KEY SegTag (SegTag),
  KEY SegNo (SegGroup)
) TYPE=InnoDB;

--
-- Table structure for table `FreightCosts`
--

DROP TABLE IF EXISTS FreightCosts;
CREATE TABLE FreightCosts (
  ShipCostFromID int(11) NOT NULL auto_increment,
  LocationFrom varchar(5) NOT NULL default '',
  Destination varchar(40) NOT NULL default '',
  ShipperID int(11) NOT NULL default '0',
  CubRate double(16,2) NOT NULL default '0.00',
  KGRate double(16,2) NOT NULL default '0.00',
  MAXKGs double(16,2) NOT NULL default '999999.00',
  MAXCub double(16,2) NOT NULL default '999999.00',
  FixedPrice double(16,2) NOT NULL default '0.00',
  MinimumChg double(16,2) NOT NULL default '0.00',
  PRIMARY KEY  (ShipCostFromID),
  KEY Destination (Destination),
  KEY LocationFrom (LocationFrom),
  KEY ShipperID (ShipperID),
  KEY Destination_2 (Destination,LocationFrom,ShipperID),
  CONSTRAINT `FreightCosts_ibfk_1` FOREIGN KEY (`LocationFrom`) REFERENCES `Locations` (`LocCode`),
  CONSTRAINT `FreightCosts_ibfk_2` FOREIGN KEY (`ShipperID`) REFERENCES `Shippers` (`Shipper_ID`)
) TYPE=InnoDB;

--
-- Table structure for table `GLTrans`
--

DROP TABLE IF EXISTS GLTrans;
CREATE TABLE GLTrans (
  CounterIndex int(11) NOT NULL auto_increment,
  Type smallint(6) NOT NULL default '0',
  TypeNo bigint(16) NOT NULL default '1',
  ChequeNo int(11) NOT NULL default '0',
  TranDate date NOT NULL default '0000-00-00',
  PeriodNo smallint(6) NOT NULL default '0',
  Account int(11) NOT NULL default '0',
  Narrative varchar(200) NOT NULL default '',
  Amount float NOT NULL default '0',
  Posted tinyint(4) NOT NULL default '0',
  JobRef varchar(20) NOT NULL default '',
  PRIMARY KEY  (CounterIndex),
  KEY Account (Account),
  KEY ChequeNo (ChequeNo),
  KEY PeriodNo (PeriodNo),
  KEY Posted (Posted),
  KEY TranDate (TranDate),
  KEY TypeNo (TypeNo),
  KEY Type_and_Number (Type,TypeNo),
  KEY JobRef (JobRef),
  CONSTRAINT `GLTrans_ibfk_1` FOREIGN KEY (`Account`) REFERENCES `ChartMaster` (`AccountCode`),
  CONSTRAINT `GLTrans_ibfk_2` FOREIGN KEY (`Type`) REFERENCES `SysTypes` (`TypeID`),
  CONSTRAINT `GLTrans_ibfk_3` FOREIGN KEY (`PeriodNo`) REFERENCES `Periods` (`PeriodNo`)
) TYPE=InnoDB;

--
-- Table structure for table `GRNs`
--

DROP TABLE IF EXISTS GRNs;
CREATE TABLE GRNs (
  GRNBatch smallint(6) NOT NULL default '0',
  GRNNo int(11) NOT NULL auto_increment,
  PODetailItem int(11) NOT NULL default '0',
  ItemCode varchar(20) NOT NULL default '',
  DeliveryDate date NOT NULL default '0000-00-00',
  ItemDescription varchar(100) NOT NULL default '',
  QtyRecd double(16,4) NOT NULL default '0.0000',
  QuantityInv double(16,4) NOT NULL default '0.0000',
  SupplierID varchar(10) NOT NULL default '',
  PRIMARY KEY  (GRNNo),
  KEY DeliveryDate (DeliveryDate),
  KEY ItemCode (ItemCode),
  KEY PODetailItem (PODetailItem),
  KEY SupplierID (SupplierID),
  CONSTRAINT `GRNs_ibfk_1` FOREIGN KEY (`SupplierID`) REFERENCES `Suppliers` (`SupplierID`),
  CONSTRAINT `GRNs_ibfk_2` FOREIGN KEY (`PODetailItem`) REFERENCES `PurchOrderDetails` (`PODetailItem`)
) TYPE=InnoDB;

--
-- Table structure for table `HoldReasons`
--

DROP TABLE IF EXISTS HoldReasons;
CREATE TABLE HoldReasons (
  ReasonCode smallint(6) NOT NULL default '1',
  ReasonDescription char(30) NOT NULL default '',
  DissallowInvoices tinyint(4) NOT NULL default '-1',
  PRIMARY KEY  (ReasonCode),
  KEY ReasonCode (ReasonCode),
  KEY ReasonDescription (ReasonDescription)
) TYPE=InnoDB;

--
-- Table structure for table `LastCostRollUp`
--

DROP TABLE IF EXISTS LastCostRollUp;
CREATE TABLE LastCostRollUp (
  StockID char(20) NOT NULL default '',
  TotalOnHand double(16,4) NOT NULL default '0.0000',
  MatCost decimal(20,4) NOT NULL default '0.0000',
  LabCost decimal(20,4) NOT NULL default '0.0000',
  OheadCost decimal(20,4) NOT NULL default '0.0000',
  CategoryID char(6) NOT NULL default '',
  StockAct int(11) NOT NULL default '0',
  AdjGLAct int(11) NOT NULL default '0',
  NewMatCost decimal(20,4) NOT NULL default '0.0000',
  NewLabCost decimal(20,4) NOT NULL default '0.0000',
  NewOheadCost decimal(20,4) NOT NULL default '0.0000'
) TYPE=InnoDB;

--
-- Table structure for table `LocStock`
--

DROP TABLE IF EXISTS LocStock;
CREATE TABLE LocStock (
  LocCode varchar(5) NOT NULL default '',
  StockID varchar(20) NOT NULL default '',
  Quantity double(16,1) NOT NULL default '0.0',
  ReorderLevel bigint(20) NOT NULL default '0',
  PRIMARY KEY  (LocCode,StockID),
  KEY StockID (StockID),
  CONSTRAINT `LocStock_ibfk_1` FOREIGN KEY (`LocCode`) REFERENCES `Locations` (`LocCode`),
  CONSTRAINT `LocStock_ibfk_2` FOREIGN KEY (`StockID`) REFERENCES `StockMaster` (`StockID`)
) TYPE=InnoDB;

--
-- Table structure for table `Locations`
--

DROP TABLE IF EXISTS Locations;
CREATE TABLE Locations (
  LocCode varchar(5) NOT NULL default '',
  LocationName varchar(50) NOT NULL default '',
  DelAdd1 varchar(40) NOT NULL default '',
  DelAdd2 varchar(40) NOT NULL default '',
  DelAdd3 varchar(40) NOT NULL default '',
  Tel varchar(30) NOT NULL default '',
  Fax varchar(30) NOT NULL default '',
  Email varchar(55) NOT NULL default '',
  Contact varchar(30) NOT NULL default '',
  TaxAuthority tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (LocCode)
) TYPE=InnoDB;

--
-- Table structure for table `OrderDeliveryDifferencesLog`
--

DROP TABLE IF EXISTS OrderDeliveryDifferencesLog;
CREATE TABLE OrderDeliveryDifferencesLog (
  OrderNo int(11) NOT NULL default '0',
  InvoiceNo int(11) NOT NULL default '0',
  StockID varchar(20) NOT NULL default '',
  QuantityDiff double(16,4) NOT NULL default '0.0000',
  DebtorNo varchar(10) NOT NULL default '',
  Branch varchar(10) NOT NULL default '',
  Can_or_BO char(3) NOT NULL default 'CAN',
  PRIMARY KEY  (OrderNo,InvoiceNo,StockID),
  KEY StockID (StockID),
  KEY DebtorNo (DebtorNo,Branch),
  KEY Can_or_BO (Can_or_BO),
  KEY OrderNo (OrderNo),
  CONSTRAINT `OrderDeliveryDifferencesLog_ibfk_1` FOREIGN KEY (`StockID`) REFERENCES `StockMaster` (`StockID`),
  CONSTRAINT `OrderDeliveryDifferencesLog_ibfk_2` FOREIGN KEY (`DebtorNo`, `Branch`) REFERENCES `CustBranch` (`DebtorNo`, `BranchCode`),
  CONSTRAINT `OrderDeliveryDifferencesLog_ibfk_3` FOREIGN KEY (`OrderNo`) REFERENCES `SalesOrders` (`OrderNo`)
) TYPE=InnoDB;

--
-- Table structure for table `PaymentTerms`
--

DROP TABLE IF EXISTS PaymentTerms;
CREATE TABLE PaymentTerms (
  TermsIndicator char(2) NOT NULL default '',
  Terms char(40) NOT NULL default '',
  DaysBeforeDue smallint(6) NOT NULL default '0',
  DayInFollowingMonth smallint(6) NOT NULL default '0',
  PRIMARY KEY  (TermsIndicator),
  KEY DaysBeforeDue (DaysBeforeDue),
  KEY DayInFollowingMonth (DayInFollowingMonth)
) TYPE=InnoDB;

--
-- Table structure for table `Periods`
--

DROP TABLE IF EXISTS Periods;
CREATE TABLE Periods (
  PeriodNo smallint(6) NOT NULL default '0',
  LastDate_in_Period date NOT NULL default '0000-00-00',
  PRIMARY KEY  (PeriodNo),
  KEY LastDate_in_Period (LastDate_in_Period)
) TYPE=InnoDB;

--
-- Table structure for table `Prices`
--

DROP TABLE IF EXISTS Prices;
CREATE TABLE Prices (
  StockID varchar(20) NOT NULL default '',
  TypeAbbrev char(2) NOT NULL default '',
  CurrAbrev char(3) NOT NULL default '',
  DebtorNo varchar(10) NOT NULL default '',
  Price decimal(20,4) NOT NULL default '0.0000',
  BranchCode varchar(10) NOT NULL default '',
  PRIMARY KEY  (StockID,TypeAbbrev,CurrAbrev,DebtorNo),
  KEY CurrAbrev (CurrAbrev),
  KEY DebtorNo (DebtorNo),
  KEY StockID (StockID),
  KEY TypeAbbrev (TypeAbbrev),
  CONSTRAINT `Prices_ibfk_1` FOREIGN KEY (`StockID`) REFERENCES `StockMaster` (`StockID`),
  CONSTRAINT `Prices_ibfk_2` FOREIGN KEY (`CurrAbrev`) REFERENCES `Currencies` (`CurrAbrev`),
  CONSTRAINT `Prices_ibfk_3` FOREIGN KEY (`TypeAbbrev`) REFERENCES `SalesTypes` (`TypeAbbrev`)
) TYPE=InnoDB;

--
-- Table structure for table `PurchData`
--

DROP TABLE IF EXISTS PurchData;
CREATE TABLE PurchData (
  SupplierNo char(10) NOT NULL default '',
  StockID char(20) NOT NULL default '',
  Price decimal(20,4) NOT NULL default '0.0000',
  SuppliersUOM char(50) NOT NULL default '',
  ConversionFactor double(16,4) NOT NULL default '1.0000',
  SupplierDescription char(50) NOT NULL default '',
  LeadTime smallint(6) NOT NULL default '1',
  Preferred tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (SupplierNo,StockID),
  KEY StockID (StockID),
  KEY SupplierNo (SupplierNo),
  KEY Preferred (Preferred),
  CONSTRAINT `PurchData_ibfk_1` FOREIGN KEY (`StockID`) REFERENCES `StockMaster` (`StockID`),
  CONSTRAINT `PurchData_ibfk_2` FOREIGN KEY (`SupplierNo`) REFERENCES `Suppliers` (`SupplierID`)
) TYPE=InnoDB;

--
-- Table structure for table `PurchOrderDetails`
--

DROP TABLE IF EXISTS PurchOrderDetails;
CREATE TABLE PurchOrderDetails (
  PODetailItem int(11) NOT NULL auto_increment,
  OrderNo int(11) NOT NULL default '0',
  ItemCode varchar(20) NOT NULL default '',
  DeliveryDate date NOT NULL default '0000-00-00',
  ItemDescription varchar(100) NOT NULL default '',
  GLCode int(11) NOT NULL default '0',
  QtyInvoiced double(16,4) NOT NULL default '0.0000',
  UnitPrice double(16,4) NOT NULL default '0.0000',
  ActPrice double(16,4) NOT NULL default '0.0000',
  StdCostUnit double(16,4) NOT NULL default '0.0000',
  QuantityOrd double(16,4) NOT NULL default '0.0000',
  QuantityRecd double(16,4) NOT NULL default '0.0000',
  ShiptRef int(1) NOT NULL default '0',
  JobRef varchar(20) NOT NULL default '',
  Completed tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (PODetailItem),
  KEY DeliveryDate (DeliveryDate),
  KEY GLCode (GLCode),
  KEY ItemCode (ItemCode),
  KEY JobRef (JobRef),
  KEY OrderNo (OrderNo),
  KEY ShiptRef (ShiptRef),
  KEY Completed (Completed),
  CONSTRAINT `PurchOrderDetails_ibfk_1` FOREIGN KEY (`OrderNo`) REFERENCES `PurchOrders` (`OrderNo`)
) TYPE=InnoDB;

--
-- Table structure for table `PurchOrders`
--

DROP TABLE IF EXISTS PurchOrders;
CREATE TABLE PurchOrders (
  OrderNo int(11) NOT NULL auto_increment,
  SupplierNo varchar(10) NOT NULL default '',
  Comments longblob,
  OrdDate datetime NOT NULL default '0000-00-00 00:00:00',
  Rate double(16,4) NOT NULL default '1.0000',
  DatePrinted datetime default NULL,
  AllowPrint tinyint(4) NOT NULL default '1',
  Initiator varchar(10) default NULL,
  RequisitionNo varchar(15) default NULL,
  IntoStockLocation varchar(5) NOT NULL default '',
  DelAdd1 varchar(40) NOT NULL default '',
  DelAdd2 varchar(40) NOT NULL default '',
  DelAdd3 varchar(40) NOT NULL default '',
  DelAdd4 varchar(40) NOT NULL default '',
  PRIMARY KEY  (OrderNo),
  KEY OrdDate (OrdDate),
  KEY SupplierNo (SupplierNo),
  KEY IntoStockLocation (IntoStockLocation),
  KEY AllowPrintPO (AllowPrint),
  CONSTRAINT `PurchOrders_ibfk_1` FOREIGN KEY (`SupplierNo`) REFERENCES `Suppliers` (`SupplierID`),
  CONSTRAINT `PurchOrders_ibfk_2` FOREIGN KEY (`IntoStockLocation`) REFERENCES `Locations` (`LocCode`)
) TYPE=InnoDB;

--
-- Table structure for table `ReportColumns`
--

DROP TABLE IF EXISTS ReportColumns;
CREATE TABLE ReportColumns (
  ReportID smallint(6) NOT NULL default '0',
  ColNo smallint(6) NOT NULL default '0',
  Heading1 varchar(15) NOT NULL default '',
  Heading2 varchar(15) default NULL,
  Calculation tinyint(1) NOT NULL default '0',
  PeriodFrom smallint(6) default NULL,
  PeriodTo smallint(6) default NULL,
  DataType varchar(15) default NULL,
  ColNumerator tinyint(4) default NULL,
  ColDenominator tinyint(4) default NULL,
  CalcOperator char(1) default NULL,
  BudgetOrActual tinyint(1) NOT NULL default '0',
  ValFormat char(1) NOT NULL default 'N',
  Constant float NOT NULL default '0',
  PRIMARY KEY  (ReportID,ColNo),
  CONSTRAINT `ReportColumns_ibfk_1` FOREIGN KEY (`ReportID`) REFERENCES `ReportHeaders` (`ReportID`)
) TYPE=InnoDB;

--
-- Table structure for table `ReportHeaders`
--

DROP TABLE IF EXISTS ReportHeaders;
CREATE TABLE ReportHeaders (
  ReportID smallint(6) NOT NULL auto_increment,
  ReportHeading varchar(80) NOT NULL default '',
  GroupByData1 varchar(15) NOT NULL default '',
  NewPageAfter1 tinyint(1) NOT NULL default '0',
  Lower1 varchar(10) NOT NULL default '',
  Upper1 varchar(10) NOT NULL default '',
  GroupByData2 varchar(15) default NULL,
  NewPageAfter2 tinyint(1) NOT NULL default '0',
  Lower2 varchar(10) default NULL,
  Upper2 varchar(10) default NULL,
  GroupByData3 varchar(15) default NULL,
  NewPageAfter3 tinyint(1) NOT NULL default '0',
  Lower3 varchar(10) default NULL,
  Upper3 varchar(10) default NULL,
  GroupByData4 varchar(15) NOT NULL default '',
  NewPageAfter4 tinyint(1) NOT NULL default '0',
  Upper4 varchar(10) NOT NULL default '',
  Lower4 varchar(10) NOT NULL default '',
  PRIMARY KEY  (ReportID),
  KEY ReportHeading (ReportHeading)
) TYPE=InnoDB;

--
-- Table structure for table `SalesAnalysis`
--

DROP TABLE IF EXISTS SalesAnalysis;
CREATE TABLE SalesAnalysis (
  TypeAbbrev char(2) NOT NULL default '',
  PeriodNo smallint(6) NOT NULL default '0',
  Amt double(16,4) NOT NULL default '0.0000',
  Cost double(16,4) NOT NULL default '0.0000',
  Cust varchar(10) NOT NULL default '',
  CustBranch varchar(10) NOT NULL default '',
  Qty double(16,4) NOT NULL default '0.0000',
  Disc double(16,4) NOT NULL default '0.0000',
  StockID varchar(20) NOT NULL default '',
  Area char(2) NOT NULL default '',
  BudgetOrActual tinyint(1) NOT NULL default '0',
  Salesperson char(3) NOT NULL default '',
  StkCategory varchar(6) NOT NULL default '',
  ID int(11) NOT NULL auto_increment,
  PRIMARY KEY  (ID),
  KEY CustBranch (CustBranch),
  KEY Cust (Cust),
  KEY PeriodNo (PeriodNo),
  KEY StkCategory (StkCategory),
  KEY StockID (StockID),
  KEY TypeAbbrev (TypeAbbrev),
  KEY Area (Area),
  KEY BudgetOrActual (BudgetOrActual),
  KEY Salesperson (Salesperson),
  CONSTRAINT `SalesAnalysis_ibfk_1` FOREIGN KEY (`PeriodNo`) REFERENCES `Periods` (`PeriodNo`)
) TYPE=InnoDB;

--
-- Table structure for table `SalesGLPostings`
--

DROP TABLE IF EXISTS SalesGLPostings;
CREATE TABLE SalesGLPostings (
  ID int(11) NOT NULL auto_increment,
  Area char(2) NOT NULL default '',
  StkCat varchar(6) NOT NULL default '',
  DiscountGLCode int(11) NOT NULL default '0',
  SalesGLCode int(11) NOT NULL default '0',
  SalesType char(2) NOT NULL default 'AN',
  PRIMARY KEY  (ID),
  UNIQUE KEY Area_StkCat (Area,StkCat,SalesType),
  KEY Area (Area),
  KEY StkCat (StkCat),
  KEY SalesType (SalesType)
) TYPE=InnoDB;

--
-- Table structure for table `SalesOrderDetails`
--

DROP TABLE IF EXISTS SalesOrderDetails;
CREATE TABLE SalesOrderDetails (
  OrderNo int(11) NOT NULL default '0',
  StkCode char(20) NOT NULL default '',
  QtyInvoiced double(16,4) NOT NULL default '0.0000',
  UnitPrice double(16,4) NOT NULL default '0.0000',
  Quantity double(16,4) NOT NULL default '0.0000',
  Estimate tinyint(4) NOT NULL default '0',
  DiscountPercent double(16,4) NOT NULL default '0.0000',
  ActualDispatchDate datetime NOT NULL default '0000-00-00 00:00:00',
  Completed tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (OrderNo,StkCode),
  KEY OrderNo (OrderNo),
  KEY StkCode (StkCode),
  KEY Completed (Completed),
  CONSTRAINT `SalesOrderDetails_ibfk_1` FOREIGN KEY (`OrderNo`) REFERENCES `SalesOrders` (`OrderNo`),
  CONSTRAINT `SalesOrderDetails_ibfk_2` FOREIGN KEY (`StkCode`) REFERENCES `StockMaster` (`StockID`)
) TYPE=InnoDB;

--
-- Table structure for table `SalesOrders`
--

DROP TABLE IF EXISTS SalesOrders;
CREATE TABLE SalesOrders (
  OrderNo int(11) NOT NULL auto_increment,
  DebtorNo varchar(10) NOT NULL default '',
  BranchCode varchar(10) NOT NULL default '',
  CustomerRef varchar(50) NOT NULL default '',
  BuyerName varchar(50) default NULL,
  Comments longblob,
  OrdDate date NOT NULL default '0000-00-00',
  OrderType char(2) NOT NULL default '',
  ShipVia int(11) NOT NULL default '0',
  DelAdd1 varchar(40) NOT NULL default '',
  DelAdd2 varchar(20) NOT NULL default '',
  DelAdd3 varchar(15) NOT NULL default '',
  DelAdd4 varchar(15) default NULL,
  ContactPhone varchar(25) default NULL,
  ContactEmail varchar(25) default NULL,
  DeliverTo varchar(40) NOT NULL default '',
  FreightCost float(10,2) NOT NULL default '0.00',
  FromStkLoc varchar(5) NOT NULL default '',
  DeliveryDate date NOT NULL default '0000-00-00',
  PrintedPackingSlip tinyint(4) NOT NULL default '0',
  DatePackingSlipPrinted date NOT NULL default '0000-00-00',
  PRIMARY KEY  (OrderNo),
  KEY DebtorNo (DebtorNo),
  KEY OrdDate (OrdDate),
  KEY OrderType (OrderType),
  KEY LocationIndex (FromStkLoc),
  KEY BranchCode (BranchCode,DebtorNo),
  KEY ShipVia (ShipVia),
  CONSTRAINT `SalesOrders_ibfk_1` FOREIGN KEY (`BranchCode`, `DebtorNo`) REFERENCES `CustBranch` (`BranchCode`, `DebtorNo`),
  CONSTRAINT `SalesOrders_ibfk_2` FOREIGN KEY (`ShipVia`) REFERENCES `Shippers` (`Shipper_ID`),
  CONSTRAINT `SalesOrders_ibfk_3` FOREIGN KEY (`FromStkLoc`) REFERENCES `Locations` (`LocCode`)
) TYPE=InnoDB;

--
-- Table structure for table `SalesTypes`
--

DROP TABLE IF EXISTS SalesTypes;
CREATE TABLE SalesTypes (
  TypeAbbrev char(2) NOT NULL default '',
  Sales_Type char(20) NOT NULL default '',
  PRIMARY KEY  (TypeAbbrev),
  KEY Sales_Type (Sales_Type)
) TYPE=InnoDB;

--
-- Table structure for table `Salesman`
--

DROP TABLE IF EXISTS Salesman;
CREATE TABLE Salesman (
  SalesmanCode char(3) NOT NULL default '',
  SalesmanName char(30) NOT NULL default '',
  SManTel char(20) NOT NULL default '',
  SManFax char(20) NOT NULL default '',
  CommissionRate1 double(16,4) NOT NULL default '0.0000',
  Breakpoint decimal(20,4) NOT NULL default '0.0000',
  CommissionRate2 double(16,4) NOT NULL default '0.0000',
  PRIMARY KEY  (SalesmanCode)
) TYPE=InnoDB;

--
-- Table structure for table `ShipmentCharges`
--

DROP TABLE IF EXISTS ShipmentCharges;
CREATE TABLE ShipmentCharges (
  ShiptChgID int(11) NOT NULL auto_increment,
  ShiptRef int(11) NOT NULL default '0',
  TransType smallint(6) NOT NULL default '0',
  TransNo int(11) NOT NULL default '0',
  StockID varchar(20) NOT NULL default '',
  Value float NOT NULL default '0',
  PRIMARY KEY  (ShiptChgID),
  KEY TransType (TransType,TransNo),
  KEY ShiptRef (ShiptRef),
  KEY StockID (StockID),
  KEY TransType_2 (TransType),
  CONSTRAINT `ShipmentCharges_ibfk_1` FOREIGN KEY (`ShiptRef`) REFERENCES `Shipments` (`ShiptRef`),
  CONSTRAINT `ShipmentCharges_ibfk_2` FOREIGN KEY (`TransType`) REFERENCES `SysTypes` (`TypeID`),
  CONSTRAINT `ShipmentCharges_ibfk_3` FOREIGN KEY (`StockID`) REFERENCES `StockMaster` (`StockID`)
) TYPE=InnoDB;

--
-- Table structure for table `Shipments`
--

DROP TABLE IF EXISTS Shipments;
CREATE TABLE Shipments (
  ShiptRef int(11) NOT NULL default '0',
  VoyageRef varchar(20) NOT NULL default '0',
  Vessel varchar(50) NOT NULL default '',
  ETA datetime NOT NULL default '0000-00-00 00:00:00',
  AccumValue double(16,4) NOT NULL default '0.0000',
  SupplierID varchar(10) NOT NULL default '',
  Closed tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (ShiptRef),
  KEY ETA (ETA),
  KEY SupplierID (SupplierID),
  KEY ShipperRef (VoyageRef),
  KEY Vessel (Vessel),
  CONSTRAINT `Shipments_ibfk_1` FOREIGN KEY (`SupplierID`) REFERENCES `Suppliers` (`SupplierID`)
) TYPE=InnoDB;

--
-- Table structure for table `Shippers`
--

DROP TABLE IF EXISTS Shippers;
CREATE TABLE Shippers (
  Shipper_ID int(11) NOT NULL auto_increment,
  ShipperName char(40) NOT NULL default '',
  MinCharge double(16,4) NOT NULL default '0.0000',
  PRIMARY KEY  (Shipper_ID)
) TYPE=InnoDB;

--
-- Table structure for table `StockCategory`
--

DROP TABLE IF EXISTS StockCategory;
CREATE TABLE StockCategory (
  CategoryID char(6) NOT NULL default '',
  CategoryDescription char(20) NOT NULL default '',
  StockType char(1) NOT NULL default 'F',
  StockAct int(11) NOT NULL default '0',
  AdjGLAct int(11) NOT NULL default '0',
  PurchPriceVarAct int(11) NOT NULL default '80000',
  MaterialUseageVarAc int(11) NOT NULL default '80000',
  WIPAct int(11) NOT NULL default '0',
  PRIMARY KEY  (CategoryID),
  KEY CategoryDescription (CategoryDescription),
  KEY StockType (StockType)
) TYPE=InnoDB;

--
-- Table structure for table `StockCheckFreeze`
--

DROP TABLE IF EXISTS StockCheckFreeze;
CREATE TABLE StockCheckFreeze (
  StockID varchar(20) NOT NULL default '',
  LocCode varchar(5) NOT NULL default '',
  QOH float NOT NULL default '0',
  PRIMARY KEY  (StockID),
  KEY LocCode (LocCode),
  CONSTRAINT `StockCheckFreeze_ibfk_1` FOREIGN KEY (`StockID`) REFERENCES `StockMaster` (`StockID`),
  CONSTRAINT `StockCheckFreeze_ibfk_2` FOREIGN KEY (`LocCode`) REFERENCES `Locations` (`LocCode`)
) TYPE=InnoDB;

--
-- Table structure for table `StockCounts`
--

DROP TABLE IF EXISTS StockCounts;
CREATE TABLE StockCounts (
  ID int(11) NOT NULL auto_increment,
  StockID varchar(20) NOT NULL default '',
  LocCode varchar(5) NOT NULL default '',
  QtyCounted float NOT NULL default '0',
  Reference varchar(20) NOT NULL default '',
  PRIMARY KEY  (ID),
  KEY StockID (StockID),
  KEY LocCode (LocCode),
  CONSTRAINT `StockCounts_ibfk_1` FOREIGN KEY (`StockID`) REFERENCES `StockMaster` (`StockID`),
  CONSTRAINT `StockCounts_ibfk_2` FOREIGN KEY (`LocCode`) REFERENCES `Locations` (`LocCode`)
) TYPE=InnoDB;

--
-- Table structure for table `StockMaster`
--

DROP TABLE IF EXISTS StockMaster;
CREATE TABLE StockMaster (
  StockID varchar(20) NOT NULL default '',
  CategoryID varchar(6) NOT NULL default '',
  Description varchar(50) NOT NULL default '',
  LongDescription text NOT NULL,
  Units varchar(20) NOT NULL default 'each',
  MBflag char(1) NOT NULL default 'B',
  LastCurCostDate date NOT NULL default '1800-01-01',
  ActualCost decimal(20,4) NOT NULL default '0.0000',
  LastCost decimal(20,4) NOT NULL default '0.0000',
  Materialcost decimal(20,4) NOT NULL default '0.0000',
  Labourcost decimal(20,4) NOT NULL default '0.0000',
  Overheadcost decimal(20,4) NOT NULL default '0.0000',
  lowestlevel smallint(6) NOT NULL default '0',
  Discontinued tinyint(4) NOT NULL default '0',
  Controlled tinyint(4) NOT NULL default '0',
  EOQ double(10,2) NOT NULL default '0.00',
  Volume decimal(20,4) NOT NULL default '0.0000',
  KGS decimal(20,4) NOT NULL default '0.0000',
  BarCode varchar(50) NOT NULL default '',
  DiscountCategory char(2) NOT NULL default '',
  TaxLevel tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (StockID),
  KEY CategoryID (CategoryID),
  KEY Description (Description),
  KEY LastCurCostDate (LastCurCostDate),
  KEY MBflag (MBflag),
  KEY StockID (StockID,CategoryID),
  KEY Controlled (Controlled),
  KEY DiscountCategory (DiscountCategory),
  CONSTRAINT `StockMaster_ibfk_1` FOREIGN KEY (`CategoryID`) REFERENCES `StockCategory` (`CategoryID`)
) TYPE=InnoDB;

--
-- Table structure for table `StockMoves`
--

DROP TABLE IF EXISTS StockMoves;
CREATE TABLE StockMoves (
  StkMoveNo int(11) NOT NULL auto_increment,
  StockID char(20) NOT NULL default '',
  Type smallint(6) NOT NULL default '0',
  TransNo int(11) NOT NULL default '0',
  LocCode char(5) NOT NULL default '',
  Bundle char(8) NOT NULL default '1',
  TranDate date NOT NULL default '0000-00-00',
  DebtorNo char(10) NOT NULL default '',
  BranchCode char(10) NOT NULL default '',
  Price decimal(20,4) NOT NULL default '0.0000',
  Prd smallint(6) NOT NULL default '0',
  Reference char(40) NOT NULL default '',
  Qty double(16,4) NOT NULL default '1.0000',
  DiscountPercent double(16,4) NOT NULL default '0.0000',
  StandardCost double(16,4) NOT NULL default '0.0000',
  Show_On_Inv_Crds tinyint(4) NOT NULL default '1',
  NewQOH double NOT NULL default '0',
  HideMovt tinyint(4) NOT NULL default '0',
  TaxRate float NOT NULL default '0',
  PRIMARY KEY  (StkMoveNo),
  KEY Bundle (Bundle),
  KEY DebtorNo (DebtorNo),
  KEY LocCode (LocCode),
  KEY Prd (Prd),
  KEY StockID (StockID,LocCode),
  KEY StockID_2 (StockID),
  KEY TranDate (TranDate),
  KEY TransNo (TransNo),
  KEY Type (Type),
  KEY Show_On_Inv_Crds (Show_On_Inv_Crds),
  KEY Hide (HideMovt),
  CONSTRAINT `StockMoves_ibfk_1` FOREIGN KEY (`StockID`) REFERENCES `StockMaster` (`StockID`),
  CONSTRAINT `StockMoves_ibfk_2` FOREIGN KEY (`Type`) REFERENCES `SysTypes` (`TypeID`),
  CONSTRAINT `StockMoves_ibfk_3` FOREIGN KEY (`LocCode`) REFERENCES `Locations` (`LocCode`),
  CONSTRAINT `StockMoves_ibfk_4` FOREIGN KEY (`Prd`) REFERENCES `Periods` (`PeriodNo`)
) TYPE=InnoDB;

--
-- Table structure for table `SuppAllocs`
--

DROP TABLE IF EXISTS SuppAllocs;
CREATE TABLE SuppAllocs (
  ID int(11) NOT NULL auto_increment,
  Amt float(20,2) NOT NULL default '0.00',
  DateAlloc date NOT NULL default '0000-00-00',
  TransID_AllocFrom int(11) NOT NULL default '0',
  TransID_AllocTo int(11) NOT NULL default '0',
  PRIMARY KEY  (ID),
  KEY TransID_AllocFrom (TransID_AllocFrom),
  KEY TransID_AllocTo (TransID_AllocTo),
  KEY DateAlloc (DateAlloc),
  CONSTRAINT `SuppAllocs_ibfk_1` FOREIGN KEY (`TransID_AllocFrom`) REFERENCES `SuppTrans` (`ID`),
  CONSTRAINT `SuppAllocs_ibfk_2` FOREIGN KEY (`TransID_AllocTo`) REFERENCES `SuppTrans` (`ID`)
) TYPE=InnoDB;

--
-- Table structure for table `SuppTrans`
--

DROP TABLE IF EXISTS SuppTrans;
CREATE TABLE SuppTrans (
  TransNo int(11) NOT NULL default '0',
  Type smallint(6) NOT NULL default '0',
  SupplierNo varchar(10) NOT NULL default '',
  SuppReference varchar(20) NOT NULL default '',
  TranDate date NOT NULL default '0000-00-00',
  DueDate date NOT NULL default '0000-00-00',
  Settled tinyint(4) NOT NULL default '0',
  Rate double(16,6) NOT NULL default '1.000000',
  OvAmount double(16,4) NOT NULL default '0.0000',
  OvGST double(16,4) NOT NULL default '0.0000',
  DiffOnExch double(16,4) NOT NULL default '0.0000',
  Alloc double(16,4) NOT NULL default '0.0000',
  TransText longblob,
  Hold tinyint(4) NOT NULL default '0',
  ID int(11) NOT NULL auto_increment,
  PRIMARY KEY  (ID),
  UNIQUE KEY TypeTransNo (TransNo,Type),
  KEY DueDate (DueDate),
  KEY Hold (Hold),
  KEY SupplierNo (SupplierNo),
  KEY Settled (Settled),
  KEY SupplierNo_2 (SupplierNo,SuppReference),
  KEY SuppReference (SuppReference),
  KEY TranDate (TranDate),
  KEY TransNo (TransNo),
  KEY Type (Type),
  CONSTRAINT `SuppTrans_ibfk_1` FOREIGN KEY (`Type`) REFERENCES `SysTypes` (`TypeID`),
  CONSTRAINT `SuppTrans_ibfk_2` FOREIGN KEY (`SupplierNo`) REFERENCES `Suppliers` (`SupplierID`)
) TYPE=InnoDB;

--
-- Table structure for table `SupplierContacts`
--

DROP TABLE IF EXISTS SupplierContacts;
CREATE TABLE SupplierContacts (
  SupplierID varchar(10) NOT NULL default '',
  Contact varchar(30) NOT NULL default '',
  Position varchar(30) NOT NULL default '',
  Tel varchar(30) NOT NULL default '',
  Fax varchar(30) NOT NULL default '',
  Mobile varchar(30) NOT NULL default '',
  Email varchar(55) NOT NULL default '',
  OrderContact tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (SupplierID,Contact),
  KEY Contact (Contact),
  KEY SupplierID (SupplierID),
  CONSTRAINT `SupplierContacts_ibfk_1` FOREIGN KEY (`SupplierID`) REFERENCES `Suppliers` (`SupplierID`)
) TYPE=InnoDB;

--
-- Table structure for table `Suppliers`
--

DROP TABLE IF EXISTS Suppliers;
CREATE TABLE Suppliers (
  SupplierID char(10) NOT NULL default '',
  SuppName char(40) NOT NULL default '',
  Address1 char(40) NOT NULL default '',
  Address2 char(40) NOT NULL default '',
  Address3 char(40) NOT NULL default '',
  Address4 char(50) NOT NULL default '',
  CurrCode char(3) NOT NULL default '',
  SupplierSince date NOT NULL default '0000-00-00',
  PaymentTerms char(2) NOT NULL default '',
  LastPaid double(16,4) NOT NULL default '0.0000',
  LastPaidDate datetime default NULL,
  BankAct char(16) NOT NULL default '',
  BankRef char(12) NOT NULL default '',
  BankPartics char(12) NOT NULL default '',
  Remittance tinyint(4) NOT NULL default '1',
  TaxAuthority tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (SupplierID),
  KEY CurrCode (CurrCode),
  KEY PaymentTerms (PaymentTerms),
  KEY SupplierID (SupplierID),
  KEY SuppName (SuppName),
  KEY TaxAuthority (TaxAuthority),
  CONSTRAINT `Suppliers_ibfk_1` FOREIGN KEY (`CurrCode`) REFERENCES `Currencies` (`CurrAbrev`),
  CONSTRAINT `Suppliers_ibfk_2` FOREIGN KEY (`PaymentTerms`) REFERENCES `PaymentTerms` (`TermsIndicator`),
  CONSTRAINT `Suppliers_ibfk_3` FOREIGN KEY (`TaxAuthority`) REFERENCES `TaxAuthorities` (`TaxID`)
) TYPE=InnoDB;

--
-- Table structure for table `SysTypes`
--

DROP TABLE IF EXISTS SysTypes;
CREATE TABLE SysTypes (
  TypeID smallint(6) NOT NULL default '0',
  TypeName char(50) NOT NULL default '',
  TypeNo int(11) NOT NULL default '1',
  PRIMARY KEY  (TypeID),
  KEY TypeNo (TypeNo)
) TYPE=InnoDB;

--
-- Table structure for table `TaxAuthLevels`
--

DROP TABLE IF EXISTS TaxAuthLevels;
CREATE TABLE TaxAuthLevels (
  TaxAuthority tinyint(4) NOT NULL default '1',
  DispatchTaxAuthority tinyint(4) NOT NULL default '1',
  Level tinyint(4) NOT NULL default '0',
  TaxRate double NOT NULL default '0',
  PRIMARY KEY  (TaxAuthority,DispatchTaxAuthority,Level),
  KEY TaxAuthority (TaxAuthority),
  KEY DispatchTaxAuthority (DispatchTaxAuthority),
  CONSTRAINT `TaxAuthLevels_ibfk_1` FOREIGN KEY (`TaxAuthority`) REFERENCES `TaxAuthorities` (`TaxID`),
  CONSTRAINT `TaxAuthLevels_ibfk_2` FOREIGN KEY (`DispatchTaxAuthority`) REFERENCES `TaxAuthorities` (`TaxID`)
) TYPE=InnoDB;

--
-- Table structure for table `TaxAuthorities`
--

DROP TABLE IF EXISTS TaxAuthorities;
CREATE TABLE TaxAuthorities (
  TaxID tinyint(4) NOT NULL default '0',
  Description char(20) NOT NULL default '',
  TaxGLCode int(11) NOT NULL default '0',
  PurchTaxGLAccount int(11) NOT NULL default '0',
  PRIMARY KEY  (TaxID),
  KEY TaxGLCode (TaxGLCode),
  KEY PurchTaxGLAccount (PurchTaxGLAccount),
  CONSTRAINT `TaxAuthorities_ibfk_1` FOREIGN KEY (`TaxGLCode`) REFERENCES `ChartMaster` (`AccountCode`),
  CONSTRAINT `TaxAuthorities_ibfk_2` FOREIGN KEY (`PurchTaxGLAccount`) REFERENCES `ChartMaster` (`AccountCode`)
) TYPE=InnoDB;

--
-- Table structure for table `WOIssues`
--

DROP TABLE IF EXISTS WOIssues;
CREATE TABLE WOIssues (
  IssueNo int(11) NOT NULL default '0',
  WORef char(20) NOT NULL default '',
  StockID char(20) NOT NULL default '',
  IssueType char(1) NOT NULL default 'M',
  WorkCentre char(5) NOT NULL default '',
  QtyIssued double(16,4) NOT NULL default '0.0000',
  StdCost decimal(20,4) NOT NULL default '0.0000',
  KEY WorkCentre (WorkCentre),
  KEY IssueNo (IssueNo),
  KEY IssueNo_2 (IssueNo,WORef,StockID),
  KEY StockID (StockID),
  KEY IssueType (IssueType),
  KEY WORef (WORef),
  CONSTRAINT `WOIssues_ibfk_1` FOREIGN KEY (`WORef`) REFERENCES `WorksOrders` (`WORef`),
  CONSTRAINT `WOIssues_ibfk_2` FOREIGN KEY (`StockID`) REFERENCES `StockMaster` (`StockID`),
  CONSTRAINT `WOIssues_ibfk_3` FOREIGN KEY (`WorkCentre`) REFERENCES `WorkCentres` (`Code`)
) TYPE=InnoDB;

--
-- Table structure for table `WORequirements`
--

DROP TABLE IF EXISTS WORequirements;
CREATE TABLE WORequirements (
  ID int(11) NOT NULL auto_increment,
  WORef char(20) NOT NULL default '',
  StockID char(20) NOT NULL default '',
  WrkCentre char(5) NOT NULL default '',
  UnitsReq double(16,4) NOT NULL default '1.0000',
  StdCost decimal(20,4) NOT NULL default '0.0000',
  ResourceType char(1) NOT NULL default 'M',
  PRIMARY KEY  (ID),
  KEY WrkCentre (WrkCentre),
  KEY ResourceType (ResourceType),
  KEY WORef (WORef,StockID),
  KEY StockID (StockID),
  KEY WORef_2 (WORef),
  CONSTRAINT `WORequirements_ibfk_1` FOREIGN KEY (`WORef`) REFERENCES `WorksOrders` (`WORef`),
  CONSTRAINT `WORequirements_ibfk_2` FOREIGN KEY (`StockID`) REFERENCES `StockMaster` (`StockID`),
  CONSTRAINT `WORequirements_ibfk_3` FOREIGN KEY (`WrkCentre`) REFERENCES `WorkCentres` (`Code`)
) TYPE=InnoDB;

--
-- Table structure for table `WWW_Users`
--

DROP TABLE IF EXISTS WWW_Users;
CREATE TABLE WWW_Users (
  UserID varchar(20) NOT NULL default '',
  Password varchar(20) NOT NULL default '',
  RealName varchar(35) NOT NULL default '',
  CustomerID varchar(10) NOT NULL default '',
  Phone varchar(30) NOT NULL default '',
  Email varchar(55) default NULL,
  DefaultLocation varchar(5) NOT NULL default '',
  FullAccess int(11) NOT NULL default '1',
  LastVisitDate datetime default NULL,
  BranchCode varchar(10) NOT NULL default '',
  PageSize varchar(20) NOT NULL default 'A4',
  ModulesAllowed varchar(20) NOT NULL default '',
  Blocked tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (UserID),
  KEY CustomerID (CustomerID),
  KEY DefaultLocation (DefaultLocation),
  CONSTRAINT `WWW_Users_ibfk_1` FOREIGN KEY (`DefaultLocation`) REFERENCES `Locations` (`LocCode`)
) TYPE=InnoDB;

--
-- Table structure for table `WorkCentres`
--

DROP TABLE IF EXISTS WorkCentres;
CREATE TABLE WorkCentres (
  Code char(5) NOT NULL default '',
  Location char(5) NOT NULL default '',
  Description char(20) NOT NULL default '',
  Capacity double(16,4) NOT NULL default '1.0000',
  OverheadPerHour decimal(20,4) NOT NULL default '0.0000',
  OverheadRecoveryAct int(11) NOT NULL default '0',
  SetUpHrs decimal(20,4) NOT NULL default '0.0000',
  PRIMARY KEY  (Code),
  KEY Description (Description),
  KEY Location (Location),
  CONSTRAINT `WorkCentres_ibfk_1` FOREIGN KEY (`Location`) REFERENCES `Locations` (`LocCode`)
) TYPE=InnoDB;

--
-- Table structure for table `WorksOrders`
--

DROP TABLE IF EXISTS WorksOrders;
CREATE TABLE WorksOrders (
  WORef char(20) NOT NULL default '',
  LocCode char(5) NOT NULL default '',
  UnitsReqd smallint(6) NOT NULL default '1',
  StockID char(20) NOT NULL default '',
  StdCost decimal(20,4) NOT NULL default '0.0000',
  RequiredBy date NOT NULL default '0000-00-00',
  ReleasedDate date NOT NULL default '1800-01-01',
  AccumValueIssued decimal(20,4) NOT NULL default '0.0000',
  AccumValueTrfd decimal(20,4) NOT NULL default '0.0000',
  Closed tinyint(4) NOT NULL default '0',
  Released tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (WORef),
  KEY StockID (StockID),
  KEY LocCode (LocCode),
  KEY ReleasedDate (ReleasedDate),
  KEY RequiredBy (RequiredBy),
  KEY WORef (WORef,LocCode),
  CONSTRAINT `WorksOrders_ibfk_1` FOREIGN KEY (`LocCode`) REFERENCES `Locations` (`LocCode`),
  CONSTRAINT `WorksOrders_ibfk_2` FOREIGN KEY (`StockID`) REFERENCES `StockMaster` (`StockID`)
) TYPE=InnoDB;

-- MySQL dump 9.10
--
-- Host: localhost    Database: weberp
-- ------------------------------------------------------
-- Server version	4.0.18-standard

--
-- Dumping data for table `AccountGroups`
--


/*!40000 ALTER TABLE AccountGroups DISABLE KEYS */;
LOCK TABLES AccountGroups WRITE;
INSERT INTO AccountGroups VALUES ('Admin Expenses',5,1,80),('Current Assets',20,0,150),('Current Liabilites',30,0,160),('Equity',50,0,300),('Labour',2,1,15),('Materials and Supplies',2,1,10),('Overhead Recovery',5,1,90),('Sales',1,1,5),('Selling Expenses',5,1,95),('Share Capital',50,0,100),('Standard Cost Of Goods Sold',2,1,8);
UNLOCK TABLES;
/*!40000 ALTER TABLE AccountGroups ENABLE KEYS */;

--
-- Dumping data for table `Areas`
--


/*!40000 ALTER TABLE Areas DISABLE KEYS */;
LOCK TABLES Areas WRITE;
INSERT INTO Areas VALUES ('AR','Arizona'),('CA','California'),('UK','England');
UNLOCK TABLES;
/*!40000 ALTER TABLE Areas ENABLE KEYS */;

--
-- Dumping data for table `BOM`
--


/*!40000 ALTER TABLE BOM DISABLE KEYS */;
LOCK TABLES BOM WRITE;
INSERT INTO BOM VALUES ('HON90022','123','ASSEM','DEN','2003-01-29','9999-12-31',4.3000),('HON92FrSus','HON90021','ASSEM','DEN','2003-06-10','2037-12-31',2.0000),('HON92FrSus','HONShock','ASSEM','DEN','2003-06-10','2037-12-31',2.0000),('HONCIVIC92SET','123','ASSEM','DEN','2003-06-10','2037-12-31',2.0000),('HONCIVIC92SET','HON90021','ASSEM','DEN','1990-01-31','9999-12-31',2.0000),('HONCIVIC92SET','HON90022','ASSEM','DEN','1990-01-01','2037-12-31',2.0000);
UNLOCK TABLES;
/*!40000 ALTER TABLE BOM ENABLE KEYS */;

--
-- Dumping data for table `BankAccounts`
--


/*!40000 ALTER TABLE BankAccounts DISABLE KEYS */;
LOCK TABLES BankAccounts WRITE;
INSERT INTO BankAccounts VALUES (700000,'Chase Morgan Current account','5332 12210 11212',''),(700100,'Petty Cash account','NA','');
UNLOCK TABLES;
/*!40000 ALTER TABLE BankAccounts ENABLE KEYS */;

--
-- Dumping data for table `BankTrans`
--


/*!40000 ALTER TABLE BankTrans DISABLE KEYS */;
LOCK TABLES BankTrans WRITE;
INSERT INTO BankTrans VALUES (1,12,1,700000,'',0,1,'2003-01-26','Cheques',0,'GBP'),(2,12,2,700000,'',0,0.523,'2003-01-26','Direct Credit',75,'USD'),(3,0,0,700100,'',0,1,'2003-01-29','Cheque',-24,''),(4,12,3,700000,'Australian Dollar receipt test',0,2.3,'2003-01-31','Cheques',2016.32,'AUD'),(6,22,2,700000,'Payment test to Biga Engineering PLC in GBP @ .49',-714.286,0.49,'2003-02-01','Direct Credit',-350,'GBP'),(7,1,2,700000,'',-29.75,1,'2003-02-19','Cheque',-29.75,'NZD'),(8,1,3,700000,'Testing payment',-63.5,1,'2003-02-19','Cheque',-63.5,'USD'),(9,12,4,700000,'',0,1,'2003-03-27','Cheques',53.25,'USD'),(10,1,4,700000,'',-22,1,'2003-05-02','Cheque',-22,'DEM'),(11,1,5,700000,'',-390.514,2.53,'2003-04-01','Cheque',-988,'USD'),(12,2,1,700000,'Act Transfer  - testing',0,2.1,'2003-10-01','Direct Credit',544,'GBP'),(13,1,6,700000,'testing',0,2.1,'2003-10-01','Direct Credit',-544,'GBP'),(14,1,7,700000,'Test header narrative field',-10.8462,52,'2003-05-02','Cheque',-564,'JPY'),(15,1,8,700000,'sdsd',-2233,1,'2003-05-02','Cheque',-2233,'NZD'),(16,1,9,700000,'fdf',-28.6667,12,'2003-05-02','Cheque',-344,'DEM'),(17,1,10,700000,'sdd',-5644,1,'2003-05-02','Cheque',-5644,'DEM'),(18,1,11,700000,'dfsfsdf',-21425,1,'2003-05-02','Cheque',-21425,'DEM'),(19,2,2,700100,'Act Transfer  -',0,1,'2003-05-02','Cheque',34,'DEM'),(20,1,12,700000,'dsd',-57,1,'2003-05-02','Cheque',-57,'DEM'),(21,2,3,700100,'Act Transfer  -',0,98,'2003-05-02','Cheque',2112,'JPY'),(22,1,13,700000,'',-25.0612,98,'2003-05-02','Cheque',-2456,'JPY'),(23,1,14,700100,'Act Transfer  - test',0,1,'2003-05-02','Cheques',-12221,'USD'),(24,12,5,700000,'',0,1,'2003-05-02','Cheques',12221,'USD'),(25,12,6,700000,'',0,1,'2003-05-12','Cheques',10.23,'USD'),(26,12,7,700000,'',0,1,'2003-06-22','Cheques',600,'USD'),(27,12,8,700000,'',0,1,'2003-06-23','Cheques',500,'USD'),(28,12,9,700000,'',0,1,'2003-06-23','Cheques',0,'USD'),(29,12,10,700000,'',0,1,'2003-06-23','Cheques',0,'USD'),(30,12,11,700000,'',0,1,'2003-06-23','Cheques',-85,'USD'),(31,12,12,700000,'',0,1,'2003-06-23','Cheques',30,'USD'),(32,12,13,700000,'',0,0.62,'2003-06-23','Direct Credit',90,'USD'),(33,12,16,700000,'',0,1,'2003-06-25','Cheques',250.25,'USD'),(34,1,15,700000,'',0,0.7,'2003-06-26','Cheque',122,'USD'),(35,1,16,700000,'',0,0.7,'2003-06-26','Cheque',-544,'USD'),(36,1,17,700000,'',0,1,'2003-06-26','Cheque',10,'USD'),(37,22,3,700000,'GREGCA-2122233',0,0.8,'2003-08-07','Cheque',-12,'GBP'),(38,12,18,700000,'',0,1,'2004-01-14','Cheques',2208.56,'USD');
UNLOCK TABLES;
/*!40000 ALTER TABLE BankTrans ENABLE KEYS */;

--
-- Dumping data for table `Buckets`
--


/*!40000 ALTER TABLE Buckets DISABLE KEYS */;
LOCK TABLES Buckets WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE Buckets ENABLE KEYS */;

--
-- Dumping data for table `COGSGLPostings`
--


/*!40000 ALTER TABLE COGSGLPostings DISABLE KEYS */;
LOCK TABLES COGSGLPostings WRITE;
INSERT INTO COGSGLPostings VALUES (1,'AN','ANY',1,'AN'),(2,'UK','ANY',256000,'EX');
UNLOCK TABLES;
/*!40000 ALTER TABLE COGSGLPostings ENABLE KEYS */;

--
-- Dumping data for table `ChartDetails`
--


/*!40000 ALTER TABLE ChartDetails DISABLE KEYS */;
LOCK TABLES ChartDetails WRITE;
INSERT INTO ChartDetails VALUES (1,37,0,-6533.75,0,0),(1,38,0,-294.32,-6533.75,0),(1,39,0,-19.82,-6828.07,0),(1,40,0,0,-6847.88,0),(1,41,0,10.35,-6847.88,0),(1,42,0,-1767.2,-6837.53,0),(1,43,0,-445.25,-8604.73,0),(1,44,0,523.75,-9049.98,0),(1,45,0,0,-8526.23,0),(1,46,0,55.04,-8526.23,0),(1,47,0,0,-8471.19,0),(1,48,0,-22.2,-8471.19,0),(1,49,0,-65.96,-8493.39,0),(1,50,0,0,-8559.35,0),(1,51,0,0,-8559.35,0),(1,52,0,0,-8559.35,0),(1,53,0,0,-8559.35,0),(1,54,0,0,-8559.35,0),(1,55,0,0,-8559.35,0),(1,56,0,0,-8559.35,0),(1,57,0,0,-8559.35,0),(1,58,0,0,-8559.35,0),(1,59,0,54.87,-8559.35,0),(1,60,0,0,-8504.48,0),(1,61,0,0,-8504.48,0),(1,62,0,0,-8504.48,0),(1,63,0,0,-8504.48,0),(1,64,0,0,-8504.48,0),(1,65,0,198.41,-8504.48,0),(1,66,0,130.18,-8306.07,0),(1,67,0,0,-8175.89,0),(1,68,0,0,-8175.89,0),(100,37,0,0,0,0),(100,38,0,0,0,0),(100,39,0,0,0,0),(100,40,0,0,0,0),(100,41,0,0,0,0),(100,42,0,0,0,0),(100,43,0,0,0,0),(100,44,0,0,0,0),(100,45,0,0,0,0),(100,46,0,0,0,0),(100,47,0,0,0,0),(100,48,0,0,0,0),(100,49,0,0,0,0),(100,50,0,0,0,0),(100,51,0,0,0,0),(100,52,0,0,0,0),(100,53,0,0,0,0),(100,54,0,0,0,0),(100,55,0,0,0,0),(100,56,0,0,0,0),(100,57,0,0,0,0),(100,58,0,0,0,0),(100,59,0,0,0,0),(100,60,0,0,0,0),(100,61,0,0,0,0),(100,62,0,0,0,0),(100,63,0,0,0,0),(100,64,0,0,0,0),(100,65,0,0,0,0),(100,66,0,0,0,0),(100,67,0,0,0,0),(100,68,0,-50,0,0),(107,37,0,12,0,0),(107,38,0,0,12,0),(107,39,0,0,12,0),(107,40,0,52.65,12,0),(107,41,0,354.51,64.65,0),(107,42,0,1069,419.16,0),(107,43,0,0,1488.16,0),(107,44,0,0,1488.16,0),(107,45,0,0,1488.16,0),(107,46,0,0,1488.16,0),(107,47,0,0,1488.16,0),(107,48,0,0,1488.16,0),(107,49,0,0,1488.16,0),(107,50,0,0,1488.16,0),(107,51,0,0,1488.16,0),(107,52,0,0,1488.16,0),(107,53,0,0,1488.16,0),(107,54,0,0,1488.16,0),(107,55,0,0,1488.16,0),(107,56,0,0,1488.16,0),(107,57,0,0,1488.16,0),(107,58,0,0,1488.16,0),(107,59,0,0,1488.16,0),(107,60,0,0,1488.16,0),(107,61,0,0,1488.16,0),(107,62,0,0,1488.16,0),(107,63,0,0,1488.16,0),(107,64,0,0,1488.16,0),(107,65,0,0,1488.16,0),(107,66,0,0,1488.16,0),(107,67,0,0,1488.16,0),(107,68,0,50,1488.16,0),(112,37,0,0,0,0),(112,38,0,0,0,0),(112,39,0,0,0,0),(112,40,0,0,0,0),(112,41,0,0,0,0),(112,42,0,0,0,0),(112,43,0,0,0,0),(112,44,0,0,0,0),(112,45,0,0,0,0),(112,46,0,0,0,0),(112,47,0,0,0,0),(112,48,0,0,0,0),(112,49,0,0,0,0),(112,50,0,0,0,0),(112,51,0,0,0,0),(112,52,0,0,0,0),(112,53,0,0,0,0),(112,54,0,0,0,0),(112,55,0,0,0,0),(112,56,0,0,0,0),(112,57,0,0,0,0),(112,58,0,0,0,0),(112,59,0,0,0,0),(112,60,0,0,0,0),(112,61,0,0,0,0),(112,62,0,0,0,0),(112,63,0,0,0,0),(112,64,0,0,0,0),(112,65,0,0,0,0),(112,66,0,0,0,0),(112,67,0,0,0,0),(112,68,0,0,0,0),(200,37,0,0,0,0),(200,38,0,0,0,0),(200,39,0,0,0,0),(200,40,0,0,0,0),(200,41,0,0,0,0),(200,42,0,0,0,0),(200,43,0,0,0,0),(200,44,0,0,0,0),(200,45,0,0,0,0),(200,46,0,0,0,0),(200,47,0,0,0,0),(200,48,0,0,0,0),(200,49,0,0,0,0),(200,50,0,0,0,0),(200,51,0,0,0,0),(200,52,0,0,0,0),(200,53,0,0,0,0),(200,54,0,0,0,0),(200,55,0,0,0,0),(200,56,0,0,0,0),(200,57,0,0,0,0),(200,58,0,0,0,0),(200,59,0,0,0,0),(200,60,0,0,0,0),(200,61,0,0,0,0),(200,62,0,0,0,0),(200,63,0,0,0,0),(200,64,0,0,0,0),(200,65,0,0,0,0),(200,66,0,0,0,0),(200,67,0,0,0,0),(200,68,0,0,0,0),(301,37,0,77.25,0,0),(301,38,0,0,77.25,0),(301,39,0,0,77.25,0),(301,40,0,0,77.25,0),(301,41,0,0,77.25,0),(301,42,0,0,77.25,0),(301,43,0,73,77.25,0),(301,44,0,0,150.25,0),(301,45,0,0,150.25,0),(301,46,0,0,150.25,0),(301,47,0,0,150.25,0),(301,48,0,0,150.25,0),(301,49,0,0,150.25,0),(301,50,0,0,150.25,0),(301,51,0,0,150.25,0),(301,52,0,0,150.25,0),(301,53,0,0,150.25,0),(301,54,0,0,150.25,0),(301,55,0,0,150.25,0),(301,56,0,0,150.25,0),(301,57,0,0,150.25,0),(301,58,0,0,150.25,0),(301,59,0,0,150.25,0),(301,60,0,0,150.25,0),(301,61,0,0,150.25,0),(301,62,0,0,150.25,0),(301,63,0,0,150.25,0),(301,64,0,0,150.25,0),(301,65,0,0,150.25,0),(301,66,0,0,150.25,0),(301,67,0,0,150.25,0),(301,68,0,0,150.25,0),(10000,37,0,0,0,0),(10000,38,0,0,0,0),(10000,39,0,0,0,0),(10000,40,0,0,0,0),(10000,41,0,0,0,0),(10000,42,0,0,0,0),(10000,43,0,0,0,0),(10000,44,0,0,0,0),(10000,45,0,0,0,0),(10000,46,0,0,0,0),(10000,47,0,0,0,0),(10000,48,0,0,0,0),(10000,49,0,0,0,0),(10000,50,0,0,0,0),(10000,51,0,0,0,0),(10000,52,0,0,0,0),(10000,53,0,0,0,0),(10000,54,0,0,0,0),(10000,55,0,0,0,0),(10000,56,0,0,0,0),(10000,57,0,0,0,0),(10000,58,0,0,0,0),(10000,59,0,0,0,0),(10000,60,0,0,0,0),(10000,61,0,0,0,0),(10000,62,0,0,0,0),(10000,63,0,0,0,0),(10000,64,0,0,0,0),(10000,65,0,0,0,0),(10000,66,0,0,0,0),(10000,67,0,0,0,0),(10000,68,0,0,0,0),(11000,37,0,0,0,0),(11000,38,0,0,0,0),(11000,39,0,0,0,0),(11000,40,0,-150.9,0,0),(11000,41,0,20,-150.9,0),(11000,42,0,-174.29,-130.9,0),(11000,43,0,0,-305.19,0),(11000,44,0,0,-305.19,0),(11000,45,0,0,-305.19,0),(11000,46,0,0,-305.19,0),(11000,47,0,0,-305.19,0),(11000,48,0,0,-305.19,0),(11000,49,0,0,-305.19,0),(11000,50,0,0,-305.19,0),(11000,51,0,0,-305.19,0),(11000,52,0,0,-305.19,0),(11000,53,0,0,-305.19,0),(11000,54,0,0,-305.19,0),(11000,55,0,0,-305.19,0),(11000,56,0,0,-305.19,0),(11000,57,0,0,-305.19,0),(11000,58,0,0,-305.19,0),(11000,59,0,0,-305.19,0),(11000,60,0,0,-305.19,0),(11000,61,0,0,-305.19,0),(11000,62,0,0,-305.19,0),(11000,63,0,0,-305.19,0),(11000,64,0,0,-305.19,0),(11000,65,0,0,-305.19,0),(11000,66,0,0,-305.19,0),(11000,67,0,0,-305.19,0),(11000,68,0,0,-305.19,0),(12000,37,0,16.65,0,0),(12000,38,0,0,16.65,0),(12000,39,0,0,16.65,0),(12000,40,0,0,16.65,0),(12000,41,0,956.22,16.65,0),(12000,42,0,20.96,972.87,0),(12000,43,0,0,993.83,0),(12000,44,0,0,993.83,0),(12000,45,0,0,993.83,0),(12000,46,0,8.4,993.83,0),(12000,47,0,0,1002.23,0),(12000,48,0,0,1002.23,0),(12000,49,0,0,1002.23,0),(12000,50,0,0,1002.23,0),(12000,51,0,0,1002.23,0),(12000,52,0,0,1002.23,0),(12000,53,0,0,1002.23,0),(12000,54,0,0,1002.23,0),(12000,55,0,0,1002.23,0),(12000,56,0,0,1002.23,0),(12000,57,0,0,1002.23,0),(12000,58,0,0,1002.23,0),(12000,59,0,0,1002.23,0),(12000,60,0,0,1002.23,0),(12000,61,0,0,1002.23,0),(12000,62,0,0,1002.23,0),(12000,63,0,0,1002.23,0),(12000,64,0,0,1002.23,0),(12000,65,0,0,1002.23,0),(12000,66,0,0,1002.23,0),(12000,67,0,0,1002.23,0),(12000,68,0,0,1002.23,0),(19000,37,0,0,0,0),(19000,38,0,0,0,0),(19000,39,0,0,0,0),(19000,40,0,0,0,0),(19000,41,0,65.25,0,0),(19000,42,0,-2.5,65.25,0),(19000,43,0,0,62.75,0),(19000,44,0,0,62.75,0),(19000,45,0,0,62.75,0),(19000,46,0,0,62.75,0),(19000,47,0,0,62.75,0),(19000,48,0,0,62.75,0),(19000,49,0,0,62.75,0),(19000,50,0,0,62.75,0),(19000,51,0,0,62.75,0),(19000,52,0,0,62.75,0),(19000,53,0,0,62.75,0),(19000,54,0,0,62.75,0),(19000,55,0,0,62.75,0),(19000,56,0,0,62.75,0),(19000,57,0,0,62.75,0),(19000,58,0,0,62.75,0),(19000,59,0,0,62.75,0),(19000,60,0,0,62.75,0),(19000,61,0,0,62.75,0),(19000,62,0,0,62.75,0),(19000,63,0,0,62.75,0),(19000,64,0,0,62.75,0),(19000,65,0,0,62.75,0),(19000,66,0,0,62.75,0),(19000,67,0,0,62.75,0),(19000,68,0,0,62.75,0),(20000,37,0,-90968.3,0,0),(20000,38,0,0,-90968.3,0),(20000,39,0,0,-90968.3,0),(20000,40,0,0,-90968.3,0),(20000,41,0,10.04,-90968.3,0),(20000,42,0,796.53,-90958.3,0),(20000,43,0,0,-90161.8,0),(20000,44,0,0,-90161.8,0),(20000,45,0,0,-90161.8,0),(20000,46,0,0,-90161.8,0),(20000,47,0,0,-90161.8,0),(20000,48,0,0,-90161.8,0),(20000,49,0,0,-90161.8,0),(20000,50,0,0,-90161.8,0),(20000,51,0,0,-90161.8,0),(20000,52,0,0,-90161.8,0),(20000,53,0,0,-90161.8,0),(20000,54,0,0,-90161.8,0),(20000,55,0,0,-90161.8,0),(20000,56,0,0,-90161.8,0),(20000,57,0,0,-90161.8,0),(20000,58,0,-18587.1,-90161.8,0),(20000,59,0,0,-108749,0),(20000,60,0,0,-108749,0),(20000,61,0,0,-108749,0),(20000,62,0,0,-108749,0),(20000,63,0,0,-108749,0),(20000,64,0,0,-108749,0),(20000,65,0,0,-108749,0),(20000,66,0,0,-108749,0),(20000,67,0,0,-108749,0),(20000,68,0,0,-108749,0),(21000,37,0,-54.65,0,0),(21000,38,0,23.5,-54.65,0),(21000,39,0,0,-31.15,0),(21000,40,0,98.25,-31.15,0),(21000,41,0,1234.55,67.1,0),(21000,42,0,-925.42,1301.65,0),(21000,43,0,0,376.23,0),(21000,44,0,0,376.23,0),(21000,45,0,0,376.23,0),(21000,46,0,0,376.23,0),(21000,47,0,0,376.23,0),(21000,48,0,0,376.23,0),(21000,49,0,0,376.23,0),(21000,50,0,0,376.23,0),(21000,51,0,0,376.23,0),(21000,52,0,0,376.23,0),(21000,53,0,0,376.23,0),(21000,54,0,0,376.23,0),(21000,55,0,0,376.23,0),(21000,56,0,0,376.23,0),(21000,57,0,0,376.23,0),(21000,58,0,0,376.23,0),(21000,59,0,0,376.23,0),(21000,60,0,0,376.23,0),(21000,61,0,0,376.23,0),(21000,62,0,0,376.23,0),(21000,63,0,0,376.23,0),(21000,64,0,0,376.23,0),(21000,65,0,0,376.23,0),(21000,66,0,0,376.23,0),(21000,67,0,0,376.23,0),(21000,68,0,0,376.23,0),(23400,37,0,0,0,0),(23400,38,0,0,0,0),(23400,39,0,0,0,0),(23400,40,0,0,0,0),(23400,41,0,0,0,0),(23400,42,0,-6.47,0,0),(23400,43,0,0,-6.47,0),(23400,44,0,0,-6.47,0),(23400,45,0,0,-6.47,0),(23400,46,0,0,-6.47,0),(23400,47,0,0,-6.47,0),(23400,48,0,0,-6.47,0),(23400,49,0,0,-6.47,0),(23400,50,0,0,-6.47,0),(23400,51,0,0,-6.47,0),(23400,52,0,0,-6.47,0),(23400,53,0,0,-6.47,0),(23400,54,0,0,-6.47,0),(23400,55,0,0,-6.47,0),(23400,56,0,0,-6.47,0),(23400,57,0,0,-6.47,0),(23400,58,0,0,-6.47,0),(23400,59,0,0,-6.47,0),(23400,60,0,0,-6.47,0),(23400,61,0,0,-6.47,0),(23400,62,0,0,-6.47,0),(23400,63,0,0,-6.47,0),(23400,64,0,0,-6.47,0),(23400,65,0,0,-6.47,0),(23400,66,0,0,-6.47,0),(23400,67,0,0,-6.47,0),(23400,68,0,0,-6.47,0),(23500,37,0,0,0,0),(23500,38,0,0,0,0),(23500,39,0,0,0,0),(23500,40,0,0,0,0),(23500,41,0,0,0,0),(23500,42,0,0,0,0),(23500,43,0,0,0,0),(23500,44,0,0,0,0),(23500,45,0,0,0,0),(23500,46,0,0,0,0),(23500,47,0,0,0,0),(23500,48,0,0,0,0),(23500,49,0,0,0,0),(23500,50,0,0,0,0),(23500,51,0,0,0,0),(23500,52,0,0,0,0),(23500,53,0,0,0,0),(23500,54,0,0,0,0),(23500,55,0,0,0,0),(23500,56,0,0,0,0),(23500,57,0,0,0,0),(23500,58,0,0,0,0),(23500,59,0,0,0,0),(23500,60,0,0,0,0),(23500,61,0,0,0,0),(23500,62,0,0,0,0),(23500,63,0,0,0,0),(23500,64,0,0,0,0),(23500,65,0,0,0,0),(23500,66,0,0,0,0),(23500,67,0,0,0,0),(23500,68,0,0,0,0),(23600,37,0,0,0,0),(23600,38,0,0,0,0),(23600,39,0,0,0,0),(23600,40,0,0,0,0),(23600,41,0,0,0,0),(23600,42,0,0,0,0),(23600,43,0,0,0,0),(23600,44,0,0,0,0),(23600,45,0,0,0,0),(23600,46,0,0,0,0),(23600,47,0,0,0,0),(23600,48,0,0,0,0),(23600,49,0,0,0,0),(23600,50,0,0,0,0),(23600,51,0,0,0,0),(23600,52,0,0,0,0),(23600,53,0,0,0,0),(23600,54,0,0,0,0),(23600,55,0,0,0,0),(23600,56,0,0,0,0),(23600,57,0,0,0,0),(23600,58,0,0,0,0),(23600,59,0,0,0,0),(23600,60,0,0,0,0),(23600,61,0,0,0,0),(23600,62,0,0,0,0),(23600,63,0,0,0,0),(23600,64,0,0,0,0),(23600,65,0,0,0,0),(23600,66,0,0,0,0),(23600,67,0,0,0,0),(23600,68,0,0,0,0),(23700,37,0,1095.77,0,0),(23700,38,0,-10.08,1095.77,0),(23700,39,0,22.82,1085.69,0),(23700,40,0,0,1108.51,0),(23700,41,0,0,1108.51,0),(23700,42,0,341.803,1108.51,0),(23700,43,0,19.5729,1450.31,0),(23700,44,0,32.6271,1469.88,0),(23700,45,0,0,1502.51,0),(23700,46,0,0,1502.51,0),(23700,47,0,0,1502.51,0),(23700,48,0,0,1502.51,0),(23700,49,0,944.277,1502.51,0),(23700,50,0,0,2446.78,0),(23700,51,0,0,2446.78,0),(23700,52,0,0,2446.78,0),(23700,53,0,0,2446.78,0),(23700,54,0,0,2446.78,0),(23700,55,0,0,2446.78,0),(23700,56,0,0,2446.78,0),(23700,57,0,0,2446.78,0),(23700,58,0,0,2446.78,0),(23700,59,0,0,2446.78,0),(23700,60,0,0,2446.78,0),(23700,61,0,0,2446.78,0),(23700,62,0,0,2446.78,0),(23700,63,0,0,2446.78,0),(23700,64,0,0,2446.78,0),(23700,65,0,0,2446.78,0),(23700,66,0,0,2446.78,0),(23700,67,0,0,2446.78,0),(23700,68,0,0,2446.78,0),(24000,37,0,4593.36,0,0),(24000,38,0,0,4593.36,0),(24000,39,0,0,4593.36,0),(24000,40,0,0,4593.36,0),(24000,41,0,0,4593.36,0),(24000,42,0,0,4593.36,0),(24000,43,0,0,4593.36,0),(24000,44,0,0,4593.36,0),(24000,45,0,0,4593.36,0),(24000,46,0,0,4593.36,0),(24000,47,0,0,4593.36,0),(24000,48,0,0,4593.36,0),(24000,49,0,0,4593.36,0),(24000,50,0,0,4593.36,0),(24000,51,0,0,4593.36,0),(24000,52,0,0,4593.36,0),(24000,53,0,0,4593.36,0),(24000,54,0,0,4593.36,0),(24000,55,0,0,4593.36,0),(24000,56,0,0,4593.36,0),(24000,57,0,0,4593.36,0),(24000,58,0,0,4593.36,0),(24000,59,0,0,4593.36,0),(24000,60,0,0,4593.36,0),(24000,61,0,0,4593.36,0),(24000,62,0,0,4593.36,0),(24000,63,0,0,4593.36,0),(24000,64,0,0,4593.36,0),(24000,65,0,0,4593.36,0),(24000,66,0,0,4593.36,0),(24000,67,0,0,4593.36,0),(24000,68,0,0,4593.36,0),(70100,37,0,0,0,0),(70100,38,0,0,0,0),(70100,39,0,0,0,0),(70100,40,0,0,0,0),(70100,41,0,0,0,0),(70100,42,0,0,0,0),(70100,43,0,0,0,0),(70100,44,0,0,0,0),(70100,45,0,0,0,0),(70100,46,0,0,0,0),(70100,47,0,0,0,0),(70100,48,0,0,0,0),(70100,49,0,0,0,0),(70100,50,0,0,0,0),(70100,51,0,0,0,0),(70100,52,0,0,0,0),(70100,53,0,0,0,0),(70100,54,0,0,0,0),(70100,55,0,0,0,0),(70100,56,0,0,0,0),(70100,57,0,0,0,0),(70100,58,0,0,0,0),(70100,59,0,0,0,0),(70100,60,0,0,0,0),(70100,61,0,0,0,0),(70100,62,0,0,0,0),(70100,63,0,0,0,0),(70100,64,0,0,0,0),(70100,65,0,0,0,0),(70100,66,0,0,0,0),(70100,67,0,0,0,0),(70100,68,0,0,0,0),(70200,37,0,0,0,0),(70200,38,0,0,0,0),(70200,39,0,0,0,0),(70200,40,0,0,0,0),(70200,41,0,0,0,0),(70200,42,0,0,0,0),(70200,43,0,0,0,0),(70200,44,0,0,0,0),(70200,45,0,0,0,0),(70200,46,0,0,0,0),(70200,47,0,0,0,0),(70200,48,0,0,0,0),(70200,49,0,0,0,0),(70200,50,0,0,0,0),(70200,51,0,0,0,0),(70200,52,0,0,0,0),(70200,53,0,0,0,0),(70200,54,0,0,0,0),(70200,55,0,0,0,0),(70200,56,0,0,0,0),(70200,57,0,0,0,0),(70200,58,0,0,0,0),(70200,59,0,0,0,0),(70200,60,0,0,0,0),(70200,61,0,0,0,0),(70200,62,0,0,0,0),(70200,63,0,0,0,0),(70200,64,0,0,0,0),(70200,65,0,0,0,0),(70200,66,0,0,0,0),(70200,67,0,0,0,0),(70200,68,0,0,0,0),(70300,37,0,0,0,0),(70300,38,0,0,0,0),(70300,39,0,0,0,0),(70300,40,0,0,0,0),(70300,41,0,0,0,0),(70300,42,0,0,0,0),(70300,43,0,0,0,0),(70300,44,0,0,0,0),(70300,45,0,0,0,0),(70300,46,0,0,0,0),(70300,47,0,0,0,0),(70300,48,0,0,0,0),(70300,49,0,0,0,0),(70300,50,0,0,0,0),(70300,51,0,0,0,0),(70300,52,0,0,0,0),(70300,53,0,0,0,0),(70300,54,0,0,0,0),(70300,55,0,0,0,0),(70300,56,0,0,0,0),(70300,57,0,0,0,0),(70300,58,0,0,0,0),(70300,59,0,0,0,0),(70300,60,0,0,0,0),(70300,61,0,0,0,0),(70300,62,0,0,0,0),(70300,63,0,0,0,0),(70300,64,0,0,0,0),(70300,65,0,0,0,0),(70300,66,0,0,0,0),(70300,67,0,0,0,0),(70300,68,0,0,0,0),(210000,37,0,0,0,0),(210000,38,0,6.25,0,0),(210000,39,0,0,6.25,0),(210000,40,0,0,6.25,0),(210000,41,0,0,6.25,0),(210000,42,0,0,6.25,0),(210000,43,0,0,6.25,0),(210000,44,0,0,6.25,0),(210000,45,0,0,6.25,0),(210000,46,0,0,6.25,0),(210000,47,0,0,6.25,0),(210000,48,0,0,6.25,0),(210000,49,0,0,6.25,0),(210000,50,0,0,6.25,0),(210000,51,0,0,6.25,0),(210000,52,0,0,6.25,0),(210000,53,0,0,6.25,0),(210000,54,0,0,6.25,0),(210000,55,0,0,6.25,0),(210000,56,0,0,6.25,0),(210000,57,0,0,6.25,0),(210000,58,0,0,6.25,0),(210000,59,0,0,6.25,0),(210000,60,0,0,6.25,0),(210000,61,0,0,6.25,0),(210000,62,0,0,6.25,0),(210000,63,0,0,6.25,0),(210000,64,0,0,6.25,0),(210000,65,0,0,6.25,0),(210000,66,0,0,6.25,0),(210000,67,0,0,6.25,0),(210000,68,0,0,6.25,0),(250000,37,0,0,0,0),(250000,38,0,0,0,0),(250000,39,0,0,0,0),(250000,40,0,0,0,0),(250000,41,0,0,0,0),(250000,42,0,0,0,0),(250000,43,0,0,0,0),(250000,44,0,0,0,0),(250000,45,0,0,0,0),(250000,46,0,0,0,0),(250000,47,0,0,0,0),(250000,48,0,0,0,0),(250000,49,0,0,0,0),(250000,50,0,0,0,0),(250000,51,0,0,0,0),(250000,52,0,0,0,0),(250000,53,0,0,0,0),(250000,54,0,0,0,0),(250000,55,0,0,0,0),(250000,56,0,0,0,0),(250000,57,0,0,0,0),(250000,58,0,0,0,0),(250000,59,0,0,0,0),(250000,60,0,0,0,0),(250000,61,0,0,0,0),(250000,62,0,0,0,0),(250000,63,0,0,0,0),(250000,64,0,0,0,0),(250000,65,0,0,0,0),(250000,66,0,0,0,0),(250000,67,0,0,0,0),(250000,68,0,0,0,0),(251000,37,0,0,0,0),(251000,38,0,0,0,0),(251000,39,0,0,0,0),(251000,40,0,0,0,0),(251000,41,0,0,0,0),(251000,42,0,0,0,0),(251000,43,0,0,0,0),(251000,44,0,0,0,0),(251000,45,0,0,0,0),(251000,46,0,0,0,0),(251000,47,0,0,0,0),(251000,48,0,0,0,0),(251000,49,0,0,0,0),(251000,50,0,0,0,0),(251000,51,0,0,0,0),(251000,52,0,0,0,0),(251000,53,0,0,0,0),(251000,54,0,0,0,0),(251000,55,0,0,0,0),(251000,56,0,0,0,0),(251000,57,0,0,0,0),(251000,58,0,0,0,0),(251000,59,0,0,0,0),(251000,60,0,0,0,0),(251000,61,0,0,0,0),(251000,62,0,0,0,0),(251000,63,0,0,0,0),(251000,64,0,0,0,0),(251000,65,0,0,0,0),(251000,66,0,0,0,0),(251000,67,0,0,0,0),(251000,68,0,0,0,0),(252000,37,0,0,0,0),(252000,38,0,0,0,0),(252000,39,0,0,0,0),(252000,40,0,0,0,0),(252000,41,0,0,0,0),(252000,42,0,0,0,0),(252000,43,0,0,0,0),(252000,44,0,0,0,0),(252000,45,0,0,0,0),(252000,46,0,0,0,0),(252000,47,0,0,0,0),(252000,48,0,0,0,0),(252000,49,0,0,0,0),(252000,50,0,0,0,0),(252000,51,0,0,0,0),(252000,52,0,0,0,0),(252000,53,0,0,0,0),(252000,54,0,0,0,0),(252000,55,0,0,0,0),(252000,56,0,0,0,0),(252000,57,0,0,0,0),(252000,58,0,0,0,0),(252000,59,0,0,0,0),(252000,60,0,0,0,0),(252000,61,0,0,0,0),(252000,62,0,0,0,0),(252000,63,0,0,0,0),(252000,64,0,0,0,0),(252000,65,0,0,0,0),(252000,66,0,0,0,0),(252000,67,0,0,0,0),(252000,68,0,0,0,0),(253000,37,0,0,0,0),(253000,38,0,0,0,0),(253000,39,0,0,0,0),(253000,40,0,0,0,0),(253000,41,0,0,0,0),(253000,42,0,0,0,0),(253000,43,0,0,0,0),(253000,44,0,0,0,0),(253000,45,0,0,0,0),(253000,46,0,0,0,0),(253000,47,0,0,0,0),(253000,48,0,0,0,0),(253000,49,0,0,0,0),(253000,50,0,0,0,0),(253000,51,0,0,0,0),(253000,52,0,0,0,0),(253000,53,0,0,0,0),(253000,54,0,0,0,0),(253000,55,0,0,0,0),(253000,56,0,0,0,0),(253000,57,0,0,0,0),(253000,58,0,0,0,0),(253000,59,0,0,0,0),(253000,60,0,0,0,0),(253000,61,0,0,0,0),(253000,62,0,0,0,0),(253000,63,0,0,0,0),(253000,64,0,0,0,0),(253000,65,0,0,0,0),(253000,66,0,0,0,0),(253000,67,0,0,0,0),(253000,68,0,0,0,0),(255000,37,0,-7.56,0,0),(255000,38,0,0,-7.56,0),(255000,39,0,0,-7.56,0),(255000,40,0,0,-7.56,0),(255000,41,0,0,-7.56,0),(255000,42,0,0,-7.56,0),(255000,43,0,0,-7.56,0),(255000,44,0,0,-7.56,0),(255000,45,0,0,-7.56,0),(255000,46,0,0,-7.56,0),(255000,47,0,0,-7.56,0),(255000,48,0,0,-7.56,0),(255000,49,0,0,-7.56,0),(255000,50,0,0,-7.56,0),(255000,51,0,0,-7.56,0),(255000,52,0,0,-7.56,0),(255000,53,0,0,-7.56,0),(255000,54,0,0,-7.56,0),(255000,55,0,0,-7.56,0),(255000,56,0,0,-7.56,0),(255000,57,0,0,-7.56,0),(255000,58,0,0,-7.56,0),(255000,59,0,0,-7.56,0),(255000,60,0,0,-7.56,0),(255000,61,0,0,-7.56,0),(255000,62,0,0,-7.56,0),(255000,63,0,0,-7.56,0),(255000,64,0,0,-7.56,0),(255000,65,0,0,-7.56,0),(255000,66,0,0,-7.56,0),(255000,67,0,0,-7.56,0),(255000,68,0,0,-7.56,0),(256000,37,0,0,0,0),(256000,38,0,0,0,0),(256000,39,0,0,0,0),(256000,40,0,0,0,0),(256000,41,0,0,0,0),(256000,42,0,25.2,0,0),(256000,43,0,0,25.2,0),(256000,44,0,0,25.2,0),(256000,45,0,0,25.2,0),(256000,46,0,0,25.2,0),(256000,47,0,0,25.2,0),(256000,48,0,0,25.2,0),(256000,49,0,0,25.2,0),(256000,50,0,0,25.2,0),(256000,51,0,0,25.2,0),(256000,52,0,0,25.2,0),(256000,53,0,0,25.2,0),(256000,54,0,0,25.2,0),(256000,55,0,0,25.2,0),(256000,56,0,0,25.2,0),(256000,57,0,0,25.2,0),(256000,58,0,0,25.2,0),(256000,59,0,0,25.2,0),(256000,60,0,0,25.2,0),(256000,61,0,0,25.2,0),(256000,62,0,0,25.2,0),(256000,63,0,0,25.2,0),(256000,64,0,0,25.2,0),(256000,65,0,0,25.2,0),(256000,66,0,0,25.2,0),(256000,67,0,0,25.2,0),(256000,68,0,0,25.2,0),(257000,37,0,0,0,0),(257000,38,0,0,0,0),(257000,39,0,0,0,0),(257000,40,0,0,0,0),(257000,41,0,0,0,0),(257000,42,0,215.12,0,0),(257000,43,0,0,215.12,0),(257000,44,0,0,215.12,0),(257000,45,0,0,215.12,0),(257000,46,0,0,215.12,0),(257000,47,0,0,215.12,0),(257000,48,0,0,215.12,0),(257000,49,0,0,215.12,0),(257000,50,0,0,215.12,0),(257000,51,0,0,215.12,0),(257000,52,0,0,215.12,0),(257000,53,0,0,215.12,0),(257000,54,0,0,215.12,0),(257000,55,0,0,215.12,0),(257000,56,0,0,215.12,0),(257000,57,0,0,215.12,0),(257000,58,0,0,215.12,0),(257000,59,0,0,215.12,0),(257000,60,0,0,215.12,0),(257000,61,0,0,215.12,0),(257000,62,0,0,215.12,0),(257000,63,0,0,215.12,0),(257000,64,0,0,215.12,0),(257000,65,0,0,215.12,0),(257000,66,0,0,215.12,0),(257000,67,0,0,215.12,0),(257000,68,0,0,215.12,0),(258000,37,0,737.1,0,0),(258000,38,0,0,737.1,0),(258000,39,0,0,737.1,0),(258000,40,0,0,737.1,0),(258000,41,0,0,737.1,0),(258000,42,0,0,737.1,0),(258000,43,0,0,737.1,0),(258000,44,0,0,737.1,0),(258000,45,0,0,737.1,0),(258000,46,0,0,737.1,0),(258000,47,0,0,737.1,0),(258000,48,0,0,737.1,0),(258000,49,0,0,737.1,0),(258000,50,0,0,737.1,0),(258000,51,0,0,737.1,0),(258000,52,0,0,737.1,0),(258000,53,0,0,737.1,0),(258000,54,0,0,737.1,0),(258000,55,0,0,737.1,0),(258000,56,0,0,737.1,0),(258000,57,0,0,737.1,0),(258000,58,0,0,737.1,0),(258000,59,0,0,737.1,0),(258000,60,0,0,737.1,0),(258000,61,0,0,737.1,0),(258000,62,0,0,737.1,0),(258000,63,0,0,737.1,0),(258000,64,0,0,737.1,0),(258000,65,0,0,737.1,0),(258000,66,0,0,737.1,0),(258000,67,0,0,737.1,0),(258000,68,0,0,737.1,0),(259000,37,0,0,0,0),(259000,38,0,63.5,0,0),(259000,39,0,0,63.5,0),(259000,40,0,0,63.5,0),(259000,41,0,0,63.5,0),(259000,42,0,0,63.5,0),(259000,43,0,0,63.5,0),(259000,44,0,0,63.5,0),(259000,45,0,0,63.5,0),(259000,46,0,0,63.5,0),(259000,47,0,0,63.5,0),(259000,48,0,0,63.5,0),(259000,49,0,0,63.5,0),(259000,50,0,0,63.5,0),(259000,51,0,0,63.5,0),(259000,52,0,0,63.5,0),(259000,53,0,0,63.5,0),(259000,54,0,0,63.5,0),(259000,55,0,0,63.5,0),(259000,56,0,0,63.5,0),(259000,57,0,0,63.5,0),(259000,58,0,0,63.5,0),(259000,59,0,0,63.5,0),(259000,60,0,0,63.5,0),(259000,61,0,0,63.5,0),(259000,62,0,0,63.5,0),(259000,63,0,0,63.5,0),(259000,64,0,0,63.5,0),(259000,65,0,0,63.5,0),(259000,66,0,0,63.5,0),(259000,67,0,0,63.5,0),(259000,68,0,0,63.5,0),(261000,37,0,-819,0,0),(261000,38,0,0,-819,0),(261000,39,0,0,-819,0),(261000,40,0,0,-819,0),(261000,41,0,0,-819,0),(261000,42,0,0,-819,0),(261000,43,0,0,-819,0),(261000,44,0,0,-819,0),(261000,45,0,0,-819,0),(261000,46,0,0,-819,0),(261000,47,0,0,-819,0),(261000,48,0,0,-819,0),(261000,49,0,0,-819,0),(261000,50,0,0,-819,0),(261000,51,0,0,-819,0),(261000,52,0,0,-819,0),(261000,53,0,0,-819,0),(261000,54,0,0,-819,0),(261000,55,0,0,-819,0),(261000,56,0,0,-819,0),(261000,57,0,0,-819,0),(261000,58,0,0,-819,0),(261000,59,0,0,-819,0),(261000,60,0,0,-819,0),(261000,61,0,0,-819,0),(261000,62,0,0,-819,0),(261000,63,0,0,-819,0),(261000,64,0,0,-819,0),(261000,65,0,0,-819,0),(261000,66,0,0,-819,0),(261000,67,0,0,-819,0),(261000,68,0,0,-819,0),(262000,37,0,0,0,0),(262000,38,0,0,0,0),(262000,39,0,0,0,0),(262000,40,0,0,0,0),(262000,41,0,0,0,0),(262000,42,0,0,0,0),(262000,43,0,0,0,0),(262000,44,0,0,0,0),(262000,45,0,0,0,0),(262000,46,0,0,0,0),(262000,47,0,0,0,0),(262000,48,0,0,0,0),(262000,49,0,0,0,0),(262000,50,0,0,0,0),(262000,51,0,0,0,0),(262000,52,0,0,0,0),(262000,53,0,0,0,0),(262000,54,0,0,0,0),(262000,55,0,0,0,0),(262000,56,0,0,0,0),(262000,57,0,0,0,0),(262000,58,0,0,0,0),(262000,59,0,0,0,0),(262000,60,0,0,0,0),(262000,61,0,0,0,0),(262000,62,0,0,0,0),(262000,63,0,0,0,0),(262000,64,0,0,0,0),(262000,65,0,0,0,0),(262000,66,0,0,0,0),(262000,67,0,0,0,0),(262000,68,0,0,0,0),(263000,37,0,0,0,0),(263000,38,0,0,0,0),(263000,39,0,0,0,0),(263000,40,0,0,0,0),(263000,41,0,0,0,0),(263000,42,0,0,0,0),(263000,43,0,0,0,0),(263000,44,0,0,0,0),(263000,45,0,0,0,0),(263000,46,0,0,0,0),(263000,47,0,0,0,0),(263000,48,0,0,0,0),(263000,49,0,0,0,0),(263000,50,0,0,0,0),(263000,51,0,0,0,0),(263000,52,0,0,0,0),(263000,53,0,0,0,0),(263000,54,0,0,0,0),(263000,55,0,0,0,0),(263000,56,0,0,0,0),(263000,57,0,0,0,0),(263000,58,0,0,0,0),(263000,59,0,0,0,0),(263000,60,0,0,0,0),(263000,61,0,0,0,0),(263000,62,0,0,0,0),(263000,63,0,0,0,0),(263000,64,0,0,0,0),(263000,65,0,0,0,0),(263000,66,0,0,0,0),(263000,67,0,0,0,0),(263000,68,0,0,0,0),(263100,37,0,0,0,0),(263100,38,0,0,0,0),(263100,39,0,0,0,0),(263100,40,0,0,0,0),(263100,41,0,0,0,0),(263100,42,0,0,0,0),(263100,43,0,0,0,0),(263100,44,0,0,0,0),(263100,45,0,0,0,0),(263100,46,0,0,0,0),(263100,47,0,0,0,0),(263100,48,0,0,0,0),(263100,49,0,-5.25,0,0),(263100,50,0,0,-5.25,0),(263100,51,0,0,-5.25,0),(263100,52,0,0,-5.25,0),(263100,53,0,0,-5.25,0),(263100,54,0,0,-5.25,0),(263100,55,0,0,-5.25,0),(263100,56,0,0,-5.25,0),(263100,57,0,0,-5.25,0),(263100,58,0,0,-5.25,0),(263100,59,0,0,-5.25,0),(263100,60,0,0,-5.25,0),(263100,61,0,0,-5.25,0),(263100,62,0,0,-5.25,0),(263100,63,0,0,-5.25,0),(263100,64,0,0,-5.25,0),(263100,65,0,0,-5.25,0),(263100,66,0,0,-5.25,0),(263100,67,0,0,-5.25,0),(263100,68,0,0,-5.25,0),(264000,37,0,0,0,0),(264000,38,0,0,0,0),(264000,39,0,0,0,0),(264000,40,0,0,0,0),(264000,41,0,0,0,0),(264000,42,0,0,0,0),(264000,43,0,0,0,0),(264000,44,0,0,0,0),(264000,45,0,0,0,0),(264000,46,0,0,0,0),(264000,47,0,0,0,0),(264000,48,0,0,0,0),(264000,49,0,0,0,0),(264000,50,0,0,0,0),(264000,51,0,0,0,0),(264000,52,0,0,0,0),(264000,53,0,0,0,0),(264000,54,0,0,0,0),(264000,55,0,0,0,0),(264000,56,0,0,0,0),(264000,57,0,0,0,0),(264000,58,0,0,0,0),(264000,59,0,0,0,0),(264000,60,0,0,0,0),(264000,61,0,0,0,0),(264000,62,0,0,0,0),(264000,63,0,0,0,0),(264000,64,0,0,0,0),(264000,65,0,0,0,0),(264000,66,0,0,0,0),(264000,67,0,0,0,0),(264000,68,0,0,0,0),(265000,37,0,0,0,0),(265000,38,0,0,0,0),(265000,39,0,0,0,0),(265000,40,0,0,0,0),(265000,41,0,0,0,0),(265000,42,0,0,0,0),(265000,43,0,0,0,0),(265000,44,0,0,0,0),(265000,45,0,0,0,0),(265000,46,0,0,0,0),(265000,47,0,0,0,0),(265000,48,0,0,0,0),(265000,49,0,0,0,0),(265000,50,0,0,0,0),(265000,51,0,0,0,0),(265000,52,0,0,0,0),(265000,53,0,0,0,0),(265000,54,0,0,0,0),(265000,55,0,0,0,0),(265000,56,0,0,0,0),(265000,57,0,0,0,0),(265000,58,0,0,0,0),(265000,59,0,0,0,0),(265000,60,0,0,0,0),(265000,61,0,0,0,0),(265000,62,0,0,0,0),(265000,63,0,0,0,0),(265000,64,0,0,0,0),(265000,65,0,0,0,0),(265000,66,0,0,0,0),(265000,67,0,0,0,0),(265000,68,0,0,0,0),(266000,37,0,0,0,0),(266000,38,0,0,0,0),(266000,39,0,0,0,0),(266000,40,0,0,0,0),(266000,41,0,0,0,0),(266000,42,0,0,0,0),(266000,43,0,0,0,0),(266000,44,0,0,0,0),(266000,45,0,0,0,0),(266000,46,0,0,0,0),(266000,47,0,0,0,0),(266000,48,0,0,0,0),(266000,49,0,0,0,0),(266000,50,0,0,0,0),(266000,51,0,0,0,0),(266000,52,0,0,0,0),(266000,53,0,0,0,0),(266000,54,0,0,0,0),(266000,55,0,0,0,0),(266000,56,0,0,0,0),(266000,57,0,0,0,0),(266000,58,0,0,0,0),(266000,59,0,0,0,0),(266000,60,0,0,0,0),(266000,61,0,0,0,0),(266000,62,0,0,0,0),(266000,63,0,0,0,0),(266000,64,0,0,0,0),(266000,65,0,0,0,0),(266000,66,0,0,0,0),(266000,67,0,0,0,0),(266000,68,0,0,0,0),(267000,37,0,-24.42,0,0),(267000,38,0,0,-24.42,0),(267000,39,0,0,-24.42,0),(267000,40,0,0,-24.42,0),(267000,41,0,0,-24.42,0),(267000,42,0,-250.25,-24.42,0),(267000,43,0,0,-274.67,0),(267000,44,0,0,-274.67,0),(267000,45,0,0,-274.67,0),(267000,46,0,0,-274.67,0),(267000,47,0,0,-274.67,0),(267000,48,0,0,-274.67,0),(267000,49,0,0,-274.67,0),(267000,50,0,0,-274.67,0),(267000,51,0,0,-274.67,0),(267000,52,0,0,-274.67,0),(267000,53,0,0,-274.67,0),(267000,54,0,0,-274.67,0),(267000,55,0,0,-274.67,0),(267000,56,0,0,-274.67,0),(267000,57,0,0,-274.67,0),(267000,58,0,0,-274.67,0),(267000,59,0,0,-274.67,0),(267000,60,0,0,-274.67,0),(267000,61,0,0,-274.67,0),(267000,62,0,0,-274.67,0),(267000,63,0,0,-274.67,0),(267000,64,0,0,-274.67,0),(267000,65,0,0,-274.67,0),(267000,66,0,0,-274.67,0),(267000,67,0,0,-274.67,0),(267000,68,0,0,-274.67,0),(267100,37,0,0,0,0),(267100,38,0,0,0,0),(267100,39,0,0,0,0),(267100,40,0,0,0,0),(267100,41,0,0,0,0),(267100,42,0,0,0,0),(267100,43,0,0,0,0),(267100,44,0,0,0,0),(267100,45,0,0,0,0),(267100,46,0,0,0,0),(267100,47,0,0,0,0),(267100,48,0,0,0,0),(267100,49,0,0,0,0),(267100,50,0,0,0,0),(267100,51,0,0,0,0),(267100,52,0,0,0,0),(267100,53,0,0,0,0),(267100,54,0,0,0,0),(267100,55,0,0,0,0),(267100,56,0,0,0,0),(267100,57,0,0,0,0),(267100,58,0,0,0,0),(267100,59,0,0,0,0),(267100,60,0,0,0,0),(267100,61,0,0,0,0),(267100,62,0,0,0,0),(267100,63,0,0,0,0),(267100,64,0,0,0,0),(267100,65,0,0,0,0),(267100,66,0,0,0,0),(267100,67,0,0,0,0),(267100,68,0,0,0,0),(270000,37,0,0,0,0),(270000,38,0,0,0,0),(270000,39,0,0,0,0),(270000,40,0,0,0,0),(270000,41,0,-2266.25,0,0),(270000,42,0,0,-2266.25,0),(270000,43,0,0,-2266.25,0),(270000,44,0,0,-2266.25,0),(270000,45,0,0,-2266.25,0),(270000,46,0,0,-2266.25,0),(270000,47,0,0,-2266.25,0),(270000,48,0,0,-2266.25,0),(270000,49,0,0,-2266.25,0),(270000,50,0,0,-2266.25,0),(270000,51,0,0,-2266.25,0),(270000,52,0,0,-2266.25,0),(270000,53,0,0,-2266.25,0),(270000,54,0,0,-2266.25,0),(270000,55,0,0,-2266.25,0),(270000,56,0,0,-2266.25,0),(270000,57,0,0,-2266.25,0),(270000,58,0,0,-2266.25,0),(270000,59,0,0,-2266.25,0),(270000,60,0,0,-2266.25,0),(270000,61,0,0,-2266.25,0),(270000,62,0,0,-2266.25,0),(270000,63,0,0,-2266.25,0),(270000,64,0,0,-2266.25,0),(270000,65,0,0,-2266.25,0),(270000,66,0,0,-2266.25,0),(270000,67,0,0,-2266.25,0),(270000,68,0,0,-2266.25,0),(271000,37,0,0,0,0),(271000,38,0,0,0,0),(271000,39,0,0,0,0),(271000,40,0,0,0,0),(271000,41,0,0,0,0),(271000,42,0,126.05,0,0),(271000,43,0,0,126.05,0),(271000,44,0,0,126.05,0),(271000,45,0,0,126.05,0),(271000,46,0,0,126.05,0),(271000,47,0,0,126.05,0),(271000,48,0,0,126.05,0),(271000,49,0,0,126.05,0),(271000,50,0,0,126.05,0),(271000,51,0,0,126.05,0),(271000,52,0,0,126.05,0),(271000,53,0,0,126.05,0),(271000,54,0,0,126.05,0),(271000,55,0,0,126.05,0),(271000,56,0,0,126.05,0),(271000,57,0,0,126.05,0),(271000,58,0,0,126.05,0),(271000,59,0,0,126.05,0),(271000,60,0,0,126.05,0),(271000,61,0,0,126.05,0),(271000,62,0,0,126.05,0),(271000,63,0,0,126.05,0),(271000,64,0,0,126.05,0),(271000,65,0,0,126.05,0),(271000,66,0,0,126.05,0),(271000,67,0,0,126.05,0),(271000,68,0,0,126.05,0),(310000,37,0,0,0,0),(310000,38,0,0,0,0),(310000,39,0,0,0,0),(310000,40,0,0,0,0),(310000,41,0,0,0,0),(310000,42,0,0,0,0),(310000,43,0,0,0,0),(310000,44,0,0,0,0),(310000,45,0,0,0,0),(310000,46,0,0,0,0),(310000,47,0,0,0,0),(310000,48,0,0,0,0),(310000,49,0,0,0,0),(310000,50,0,0,0,0),(310000,51,0,0,0,0),(310000,52,0,0,0,0),(310000,53,0,0,0,0),(310000,54,0,0,0,0),(310000,55,0,0,0,0),(310000,56,0,0,0,0),(310000,57,0,0,0,0),(310000,58,0,0,0,0),(310000,59,0,0,0,0),(310000,60,0,0,0,0),(310000,61,0,0,0,0),(310000,62,0,0,0,0),(310000,63,0,0,0,0),(310000,64,0,0,0,0),(310000,65,0,0,0,0),(310000,66,0,0,0,0),(310000,67,0,0,0,0),(310000,68,0,0,0,0),(320000,37,0,0,0,0),(320000,38,0,0,0,0),(320000,39,0,0,0,0),(320000,40,0,0,0,0),(320000,41,0,0,0,0),(320000,42,0,0,0,0),(320000,43,0,0,0,0),(320000,44,0,0,0,0),(320000,45,0,0,0,0),(320000,46,0,0,0,0),(320000,47,0,0,0,0),(320000,48,0,0,0,0),(320000,49,0,0,0,0),(320000,50,0,0,0,0),(320000,51,0,0,0,0),(320000,52,0,0,0,0),(320000,53,0,0,0,0),(320000,54,0,0,0,0),(320000,55,0,0,0,0),(320000,56,0,0,0,0),(320000,57,0,0,0,0),(320000,58,0,0,0,0),(320000,59,0,0,0,0),(320000,60,0,0,0,0),(320000,61,0,0,0,0),(320000,62,0,0,0,0),(320000,63,0,0,0,0),(320000,64,0,0,0,0),(320000,65,0,0,0,0),(320000,66,0,0,0,0),(320000,67,0,0,0,0),(320000,68,0,0,0,0),(330000,37,0,0,0,0),(330000,38,0,0,0,0),(330000,39,0,0,0,0),(330000,40,0,0,0,0),(330000,41,0,0,0,0),(330000,42,0,0,0,0),(330000,43,0,0,0,0),(330000,44,0,0,0,0),(330000,45,0,0,0,0),(330000,46,0,0,0,0),(330000,47,0,0,0,0),(330000,48,0,0,0,0),(330000,49,0,0,0,0),(330000,50,0,0,0,0),(330000,51,0,0,0,0),(330000,52,0,0,0,0),(330000,53,0,0,0,0),(330000,54,0,0,0,0),(330000,55,0,0,0,0),(330000,56,0,0,0,0),(330000,57,0,0,0,0),(330000,58,0,0,0,0),(330000,59,0,0,0,0),(330000,60,0,0,0,0),(330000,61,0,0,0,0),(330000,62,0,0,0,0),(330000,63,0,0,0,0),(330000,64,0,0,0,0),(330000,65,0,0,0,0),(330000,66,0,0,0,0),(330000,67,0,0,0,0),(330000,68,0,0,0,0),(340000,37,0,0,0,0),(340000,38,0,0,0,0),(340000,39,0,0,0,0),(340000,40,0,0,0,0),(340000,41,0,0,0,0),(340000,42,0,0,0,0),(340000,43,0,0,0,0),(340000,44,0,0,0,0),(340000,45,0,0,0,0),(340000,46,0,0,0,0),(340000,47,0,0,0,0),(340000,48,0,0,0,0),(340000,49,0,0,0,0),(340000,50,0,0,0,0),(340000,51,0,0,0,0),(340000,52,0,0,0,0),(340000,53,0,0,0,0),(340000,54,0,0,0,0),(340000,55,0,0,0,0),(340000,56,0,0,0,0),(340000,57,0,0,0,0),(340000,58,0,0,0,0),(340000,59,0,0,0,0),(340000,60,0,0,0,0),(340000,61,0,0,0,0),(340000,62,0,0,0,0),(340000,63,0,0,0,0),(340000,64,0,0,0,0),(340000,65,0,0,0,0),(340000,66,0,0,0,0),(340000,67,0,0,0,0),(340000,68,0,0,0,0),(342000,37,0,0,0,0),(342000,38,0,0,0,0),(342000,39,0,0,0,0),(342000,40,0,0,0,0),(342000,41,0,0,0,0),(342000,42,0,0,0,0),(342000,43,0,0,0,0),(342000,44,0,0,0,0),(342000,45,0,0,0,0),(342000,46,0,0,0,0),(342000,47,0,0,0,0),(342000,48,0,0,0,0),(342000,49,0,0,0,0),(342000,50,0,0,0,0),(342000,51,0,0,0,0),(342000,52,0,0,0,0),(342000,53,0,0,0,0),(342000,54,0,0,0,0),(342000,55,0,0,0,0),(342000,56,0,0,0,0),(342000,57,0,0,0,0),(342000,58,0,0,0,0),(342000,59,0,0,0,0),(342000,60,0,0,0,0),(342000,61,0,0,0,0),(342000,62,0,0,0,0),(342000,63,0,0,0,0),(342000,64,0,0,0,0),(342000,65,0,0,0,0),(342000,66,0,0,0,0),(342000,67,0,0,0,0),(342000,68,0,0,0,0),(400000,37,0,0,0,0),(400000,38,0,0,0,0),(400000,39,0,0,0,0),(400000,40,0,0,0,0),(400000,41,0,0,0,0),(400000,42,0,0,0,0),(400000,43,0,0,0,0),(400000,44,0,0,0,0),(400000,45,0,0,0,0),(400000,46,0,0,0,0),(400000,47,0,0,0,0),(400000,48,0,0,0,0),(400000,49,0,0,0,0),(400000,50,0,0,0,0),(400000,51,0,0,0,0),(400000,52,0,0,0,0),(400000,53,0,0,0,0),(400000,54,0,0,0,0),(400000,55,0,0,0,0),(400000,56,0,0,0,0),(400000,57,0,0,0,0),(400000,58,0,0,0,0),(400000,59,0,0,0,0),(400000,60,0,0,0,0),(400000,61,0,0,0,0),(400000,62,0,0,0,0),(400000,63,0,0,0,0),(400000,64,0,0,0,0),(400000,65,0,0,0,0),(400000,66,0,0,0,0),(400000,67,0,0,0,0),(400000,68,0,0,0,0),(410000,37,0,0,0,0),(410000,38,0,0,0,0),(410000,39,0,0,0,0),(410000,40,0,0,0,0),(410000,41,0,0,0,0),(410000,42,0,0,0,0),(410000,43,0,0,0,0),(410000,44,0,0,0,0),(410000,45,0,0,0,0),(410000,46,0,0,0,0),(410000,47,0,0,0,0),(410000,48,0,0,0,0),(410000,49,0,0,0,0),(410000,50,0,0,0,0),(410000,51,0,0,0,0),(410000,52,0,0,0,0),(410000,53,0,0,0,0),(410000,54,0,0,0,0),(410000,55,0,0,0,0),(410000,56,0,0,0,0),(410000,57,0,0,0,0),(410000,58,0,0,0,0),(410000,59,0,0,0,0),(410000,60,0,0,0,0),(410000,61,0,0,0,0),(410000,62,0,0,0,0),(410000,63,0,0,0,0),(410000,64,0,0,0,0),(410000,65,0,0,0,0),(410000,66,0,0,0,0),(410000,67,0,0,0,0),(410000,68,0,0,0,0),(411000,37,0,0,0,0),(411000,38,0,0,0,0),(411000,39,0,0,0,0),(411000,40,0,0,0,0),(411000,41,0,0,0,0),(411000,42,0,0,0,0),(411000,43,0,0,0,0),(411000,44,0,0,0,0),(411000,45,0,0,0,0),(411000,46,0,0,0,0),(411000,47,0,0,0,0),(411000,48,0,0,0,0),(411000,49,0,0,0,0),(411000,50,0,0,0,0),(411000,51,0,0,0,0),(411000,52,0,0,0,0),(411000,53,0,0,0,0),(411000,54,0,0,0,0),(411000,55,0,0,0,0),(411000,56,0,0,0,0),(411000,57,0,0,0,0),(411000,58,0,0,0,0),(411000,59,0,0,0,0),(411000,60,0,0,0,0),(411000,61,0,0,0,0),(411000,62,0,0,0,0),(411000,63,0,0,0,0),(411000,64,0,0,0,0),(411000,65,0,0,0,0),(411000,66,0,0,0,0),(411000,67,0,0,0,0),(411000,68,0,0,0,0),(412000,37,0,0,0,0),(412000,38,0,0,0,0),(412000,39,0,0,0,0),(412000,40,0,0,0,0),(412000,41,0,0,0,0),(412000,42,0,0,0,0),(412000,43,0,0,0,0),(412000,44,0,0,0,0),(412000,45,0,0,0,0),(412000,46,0,0,0,0),(412000,47,0,0,0,0),(412000,48,0,0,0,0),(412000,49,0,0,0,0),(412000,50,0,0,0,0),(412000,51,0,0,0,0),(412000,52,0,0,0,0),(412000,53,0,0,0,0),(412000,54,0,0,0,0),(412000,55,0,0,0,0),(412000,56,0,0,0,0),(412000,57,0,0,0,0),(412000,58,0,0,0,0),(412000,59,0,0,0,0),(412000,60,0,0,0,0),(412000,61,0,0,0,0),(412000,62,0,0,0,0),(412000,63,0,0,0,0),(412000,64,0,0,0,0),(412000,65,0,0,0,0),(412000,66,0,0,0,0),(412000,67,0,0,0,0),(412000,68,0,0,0,0),(413000,37,0,0,0,0),(413000,38,0,0,0,0),(413000,39,0,0,0,0),(413000,40,0,0,0,0),(413000,41,0,0,0,0),(413000,42,0,0,0,0),(413000,43,0,0,0,0),(413000,44,0,0,0,0),(413000,45,0,0,0,0),(413000,46,0,0,0,0),(413000,47,0,0,0,0),(413000,48,0,0,0,0),(413000,49,0,0,0,0),(413000,50,0,0,0,0),(413000,51,0,0,0,0),(413000,52,0,0,0,0),(413000,53,0,0,0,0),(413000,54,0,0,0,0),(413000,55,0,0,0,0),(413000,56,0,0,0,0),(413000,57,0,0,0,0),(413000,58,0,0,0,0),(413000,59,0,0,0,0),(413000,60,0,0,0,0),(413000,61,0,0,0,0),(413000,62,0,0,0,0),(413000,63,0,0,0,0),(413000,64,0,0,0,0),(413000,65,0,0,0,0),(413000,66,0,0,0,0),(413000,67,0,0,0,0),(413000,68,0,0,0,0),(420000,37,0,0,0,0),(420000,38,0,0,0,0),(420000,39,0,0,0,0),(420000,40,0,0,0,0),(420000,41,0,0,0,0),(420000,42,0,0,0,0),(420000,43,0,0,0,0),(420000,44,0,0,0,0),(420000,45,0,0,0,0),(420000,46,0,0,0,0),(420000,47,0,0,0,0),(420000,48,0,0,0,0),(420000,49,0,0,0,0),(420000,50,0,0,0,0),(420000,51,0,0,0,0),(420000,52,0,0,0,0),(420000,53,0,0,0,0),(420000,54,0,0,0,0),(420000,55,0,0,0,0),(420000,56,0,0,0,0),(420000,57,0,0,0,0),(420000,58,0,0,0,0),(420000,59,0,0,0,0),(420000,60,0,0,0,0),(420000,61,0,0,0,0),(420000,62,0,0,0,0),(420000,63,0,0,0,0),(420000,64,0,0,0,0),(420000,65,0,0,0,0),(420000,66,0,0,0,0),(420000,67,0,0,0,0),(420000,68,0,0,0,0),(450000,37,0,-53.5,0,0),(450000,38,0,0,-53.5,0),(450000,39,0,0,-53.5,0),(450000,40,0,0,-53.5,0),(450000,41,0,0,-53.5,0),(450000,42,0,0,-53.5,0),(450000,43,0,0,-53.5,0),(450000,44,0,0,-53.5,0),(450000,45,0,0,-53.5,0),(450000,46,0,0,-53.5,0),(450000,47,0,0,-53.5,0),(450000,48,0,0,-53.5,0),(450000,49,0,0,-53.5,0),(450000,50,0,0,-53.5,0),(450000,51,0,0,-53.5,0),(450000,52,0,0,-53.5,0),(450000,53,0,0,-53.5,0),(450000,54,0,0,-53.5,0),(450000,55,0,0,-53.5,0),(450000,56,0,0,-53.5,0),(450000,57,0,0,-53.5,0),(450000,58,0,0,-53.5,0),(450000,59,0,0,-53.5,0),(450000,60,0,0,-53.5,0),(450000,61,0,0,-53.5,0),(450000,62,0,0,-53.5,0),(450000,63,0,0,-53.5,0),(450000,64,0,0,-53.5,0),(450000,65,0,0,-53.5,0),(450000,66,0,0,-53.5,0),(450000,67,0,0,-53.5,0),(450000,68,0,0,-53.5,0),(451000,37,0,0,0,0),(451000,38,0,0,0,0),(451000,39,0,0,0,0),(451000,40,0,0,0,0),(451000,41,0,0,0,0),(451000,42,0,0,0,0),(451000,43,0,0,0,0),(451000,44,0,0,0,0),(451000,45,0,0,0,0),(451000,46,0,0,0,0),(451000,47,0,0,0,0),(451000,48,0,0,0,0),(451000,49,0,0,0,0),(451000,50,0,0,0,0),(451000,51,0,0,0,0),(451000,52,0,0,0,0),(451000,53,0,0,0,0),(451000,54,0,0,0,0),(451000,55,0,0,0,0),(451000,56,0,0,0,0),(451000,57,0,0,0,0),(451000,58,0,0,0,0),(451000,59,0,0,0,0),(451000,60,0,0,0,0),(451000,61,0,0,0,0),(451000,62,0,0,0,0),(451000,63,0,0,0,0),(451000,64,0,0,0,0),(451000,65,0,0,0,0),(451000,66,0,0,0,0),(451000,67,0,0,0,0),(451000,68,0,0,0,0),(452000,37,0,0,0,0),(452000,38,0,0,0,0),(452000,39,0,0,0,0),(452000,40,0,0,0,0),(452000,41,0,0,0,0),(452000,42,0,0,0,0),(452000,43,0,0,0,0),(452000,44,0,0,0,0),(452000,45,0,0,0,0),(452000,46,0,0,0,0),(452000,47,0,0,0,0),(452000,48,0,0,0,0),(452000,49,0,0,0,0),(452000,50,0,0,0,0),(452000,51,0,0,0,0),(452000,52,0,0,0,0),(452000,53,0,0,0,0),(452000,54,0,0,0,0),(452000,55,0,0,0,0),(452000,56,0,0,0,0),(452000,57,0,0,0,0),(452000,58,0,0,0,0),(452000,59,0,0,0,0),(452000,60,0,0,0,0),(452000,61,0,0,0,0),(452000,62,0,0,0,0),(452000,63,0,0,0,0),(452000,64,0,0,0,0),(452000,65,0,0,0,0),(452000,66,0,0,0,0),(452000,67,0,0,0,0),(452000,68,0,0,0,0),(455000,37,0,2.37,0,0),(455000,38,0,0,2.37,0),(455000,39,0,0,2.37,0),(455000,40,0,0,2.37,0),(455000,41,0,0,2.37,0),(455000,42,0,34.62,2.37,0),(455000,43,0,0,36.99,0),(455000,44,0,-2.5,36.99,0),(455000,45,0,0,34.49,0),(455000,46,0,0,34.49,0),(455000,47,0,0,34.49,0),(455000,48,0,0,34.49,0),(455000,49,0,0,34.49,0),(455000,50,0,0,34.49,0),(455000,51,0,0,34.49,0),(455000,52,0,0,34.49,0),(455000,53,0,0,34.49,0),(455000,54,0,0,34.49,0),(455000,55,0,0,34.49,0),(455000,56,0,0,34.49,0),(455000,57,0,0,34.49,0),(455000,58,0,0,34.49,0),(455000,59,0,0,34.49,0),(455000,60,0,0,34.49,0),(455000,61,0,0,34.49,0),(455000,62,0,0,34.49,0),(455000,63,0,0,34.49,0),(455000,64,0,0,34.49,0),(455000,65,0,0,34.49,0),(455000,66,0,0,34.49,0),(455000,67,0,0,34.49,0),(455000,68,0,0,34.49,0),(460000,37,0,10.8,0,0),(460000,38,0,0,10.8,0),(460000,39,0,0,10.8,0),(460000,40,0,0,10.8,0),(460000,41,0,0,10.8,0),(460000,42,0,0,10.8,0),(460000,43,0,0,10.8,0),(460000,44,0,0,10.8,0),(460000,45,0,0,10.8,0),(460000,46,0,0,10.8,0),(460000,47,0,0,10.8,0),(460000,48,0,0,10.8,0),(460000,49,0,0,10.8,0),(460000,50,0,0,10.8,0),(460000,51,0,0,10.8,0),(460000,52,0,0,10.8,0),(460000,53,0,0,10.8,0),(460000,54,0,0,10.8,0),(460000,55,0,0,10.8,0),(460000,56,0,0,10.8,0),(460000,57,0,0,10.8,0),(460000,58,0,0,10.8,0),(460000,59,0,0,10.8,0),(460000,60,0,0,10.8,0),(460000,61,0,0,10.8,0),(460000,62,0,0,10.8,0),(460000,63,0,0,10.8,0),(460000,64,0,0,10.8,0),(460000,65,0,0,10.8,0),(460000,66,0,0,10.8,0),(460000,67,0,0,10.8,0),(460000,68,0,0,10.8,0),(470000,37,0,0,0,0),(470000,38,0,0,0,0),(470000,39,0,0,0,0),(470000,40,0,0,0,0),(470000,41,0,0,0,0),(470000,42,0,0,0,0),(470000,43,0,0,0,0),(470000,44,0,0,0,0),(470000,45,0,0,0,0),(470000,46,0,0,0,0),(470000,47,0,0,0,0),(470000,48,0,0,0,0),(470000,49,0,0,0,0),(470000,50,0,0,0,0),(470000,51,0,0,0,0),(470000,52,0,0,0,0),(470000,53,0,0,0,0),(470000,54,0,0,0,0),(470000,55,0,0,0,0),(470000,56,0,0,0,0),(470000,57,0,0,0,0),(470000,58,0,0,0,0),(470000,59,0,0,0,0),(470000,60,0,0,0,0),(470000,61,0,0,0,0),(470000,62,0,0,0,0),(470000,63,0,0,0,0),(470000,64,0,0,0,0),(470000,65,0,0,0,0),(470000,66,0,0,0,0),(470000,67,0,0,0,0),(470000,68,0,0,0,0),(480000,37,0,0,0,0),(480000,38,0,0,0,0),(480000,39,0,0,0,0),(480000,40,0,0,0,0),(480000,41,0,0,0,0),(480000,42,0,0,0,0),(480000,43,0,0,0,0),(480000,44,0,0,0,0),(480000,45,0,0,0,0),(480000,46,0,0,0,0),(480000,47,0,0,0,0),(480000,48,0,0,0,0),(480000,49,0,0,0,0),(480000,50,0,0,0,0),(480000,51,0,0,0,0),(480000,52,0,0,0,0),(480000,53,0,0,0,0),(480000,54,0,0,0,0),(480000,55,0,0,0,0),(480000,56,0,0,0,0),(480000,57,0,0,0,0),(480000,58,0,0,0,0),(480000,59,0,0,0,0),(480000,60,0,0,0,0),(480000,61,0,0,0,0),(480000,62,0,0,0,0),(480000,63,0,0,0,0),(480000,64,0,0,0,0),(480000,65,0,0,0,0),(480000,66,0,0,0,0),(480000,67,0,0,0,0),(480000,68,0,0,0,0),(481000,37,0,0,0,0),(481000,38,0,0,0,0),(481000,39,0,0,0,0),(481000,40,0,0,0,0),(481000,41,0,0,0,0),(481000,42,0,0,0,0),(481000,43,0,0,0,0),(481000,44,0,0,0,0),(481000,45,0,0,0,0),(481000,46,0,0,0,0),(481000,47,0,0,0,0),(481000,48,0,0,0,0),(481000,49,0,0,0,0),(481000,50,0,0,0,0),(481000,51,0,0,0,0),(481000,52,0,0,0,0),(481000,53,0,0,0,0),(481000,54,0,0,0,0),(481000,55,0,0,0,0),(481000,56,0,0,0,0),(481000,57,0,0,0,0),(481000,58,0,0,0,0),(481000,59,0,0,0,0),(481000,60,0,0,0,0),(481000,61,0,0,0,0),(481000,62,0,0,0,0),(481000,63,0,0,0,0),(481000,64,0,0,0,0),(481000,65,0,0,0,0),(481000,66,0,0,0,0),(481000,67,0,0,0,0),(481000,68,0,0,0,0),(500000,37,0,0,0,0),(500000,38,0,0,0,0),(500000,39,0,0,0,0),(500000,40,0,0,0,0),(500000,41,0,0,0,0),(500000,42,0,0,0,0),(500000,43,0,0,0,0),(500000,44,0,0,0,0),(500000,45,0,0,0,0),(500000,46,0,0,0,0),(500000,47,0,0,0,0),(500000,48,0,0,0,0),(500000,49,0,0,0,0),(500000,50,0,0,0,0),(500000,51,0,0,0,0),(500000,52,0,0,0,0),(500000,53,0,0,0,0),(500000,54,0,0,0,0),(500000,55,0,0,0,0),(500000,56,0,0,0,0),(500000,57,0,0,0,0),(500000,58,0,0,0,0),(500000,59,0,0,0,0),(500000,60,0,0,0,0),(500000,61,0,0,0,0),(500000,62,0,0,0,0),(500000,63,0,0,0,0),(500000,64,0,0,0,0),(500000,65,0,0,0,0),(500000,66,0,0,0,0),(500000,67,0,0,0,0),(500000,68,0,0,0,0),(501000,37,0,0,0,0),(501000,38,0,0,0,0),(501000,39,0,0,0,0),(501000,40,0,0,0,0),(501000,41,0,0,0,0),(501000,42,0,0,0,0),(501000,43,0,0,0,0),(501000,44,0,0,0,0),(501000,45,0,0,0,0),(501000,46,0,0,0,0),(501000,47,0,0,0,0),(501000,48,0,0,0,0),(501000,49,0,0,0,0),(501000,50,0,0,0,0),(501000,51,0,0,0,0),(501000,52,0,0,0,0),(501000,53,0,0,0,0),(501000,54,0,0,0,0),(501000,55,0,0,0,0),(501000,56,0,0,0,0),(501000,57,0,0,0,0),(501000,58,0,0,0,0),(501000,59,0,0,0,0),(501000,60,0,0,0,0),(501000,61,0,0,0,0),(501000,62,0,0,0,0),(501000,63,0,0,0,0),(501000,64,0,0,0,0),(501000,65,0,0,0,0),(501000,66,0,0,0,0),(501000,67,0,0,0,0),(501000,68,0,0,0,0),(501800,37,0,0,0,0),(501800,38,0,0,0,0),(501800,39,0,0,0,0),(501800,40,0,0,0,0),(501800,41,0,0,0,0),(501800,42,0,0,0,0),(501800,43,0,0,0,0),(501800,44,0,0,0,0),(501800,45,0,0,0,0),(501800,46,0,0,0,0),(501800,47,0,0,0,0),(501800,48,0,0,0,0),(501800,49,0,0,0,0),(501800,50,0,0,0,0),(501800,51,0,0,0,0),(501800,52,0,0,0,0),(501800,53,0,0,0,0),(501800,54,0,0,0,0),(501800,55,0,0,0,0),(501800,56,0,0,0,0),(501800,57,0,0,0,0),(501800,58,0,0,0,0),(501800,59,0,0,0,0),(501800,60,0,0,0,0),(501800,61,0,0,0,0),(501800,62,0,0,0,0),(501800,63,0,0,0,0),(501800,64,0,0,0,0),(501800,65,0,0,0,0),(501800,66,0,0,0,0),(501800,67,0,0,0,0),(501800,68,0,0,0,0),(501900,37,0,0,0,0),(501900,38,0,0,0,0),(501900,39,0,0,0,0),(501900,40,0,0,0,0),(501900,41,0,0,0,0),(501900,42,0,0,0,0),(501900,43,0,0,0,0),(501900,44,0,0,0,0),(501900,45,0,0,0,0),(501900,46,0,0,0,0),(501900,47,0,0,0,0),(501900,48,0,0,0,0),(501900,49,0,0,0,0),(501900,50,0,0,0,0),(501900,51,0,0,0,0),(501900,52,0,0,0,0),(501900,53,0,0,0,0),(501900,54,0,0,0,0),(501900,55,0,0,0,0),(501900,56,0,0,0,0),(501900,57,0,0,0,0),(501900,58,0,0,0,0),(501900,59,0,0,0,0),(501900,60,0,0,0,0),(501900,61,0,0,0,0),(501900,62,0,0,0,0),(501900,63,0,0,0,0),(501900,64,0,0,0,0),(501900,65,0,0,0,0),(501900,66,0,0,0,0),(501900,67,0,0,0,0),(501900,68,0,0,0,0),(502000,37,0,0,0,0),(502000,38,0,0,0,0),(502000,39,0,0,0,0),(502000,40,0,0,0,0),(502000,41,0,0,0,0),(502000,42,0,0,0,0),(502000,43,0,0,0,0),(502000,44,0,0,0,0),(502000,45,0,0,0,0),(502000,46,0,0,0,0),(502000,47,0,0,0,0),(502000,48,0,0,0,0),(502000,49,0,0,0,0),(502000,50,0,0,0,0),(502000,51,0,0,0,0),(502000,52,0,0,0,0),(502000,53,0,0,0,0),(502000,54,0,0,0,0),(502000,55,0,0,0,0),(502000,56,0,0,0,0),(502000,57,0,0,0,0),(502000,58,0,0,0,0),(502000,59,0,0,0,0),(502000,60,0,0,0,0),(502000,61,0,0,0,0),(502000,62,0,0,0,0),(502000,63,0,0,0,0),(502000,64,0,0,0,0),(502000,65,0,0,0,0),(502000,66,0,0,0,0),(502000,67,0,0,0,0),(502000,68,0,0,0,0),(502100,37,0,0,0,0),(502100,38,0,0,0,0),(502100,39,0,0,0,0),(502100,40,0,0,0,0),(502100,41,0,0,0,0),(502100,42,0,0,0,0),(502100,43,0,0,0,0),(502100,44,0,0,0,0),(502100,45,0,0,0,0),(502100,46,0,0,0,0),(502100,47,0,0,0,0),(502100,48,0,0,0,0),(502100,49,0,0,0,0),(502100,50,0,0,0,0),(502100,51,0,0,0,0),(502100,52,0,0,0,0),(502100,53,0,0,0,0),(502100,54,0,0,0,0),(502100,55,0,0,0,0),(502100,56,0,0,0,0),(502100,57,0,0,0,0),(502100,58,0,0,0,0),(502100,59,0,0,0,0),(502100,60,0,0,0,0),(502100,61,0,0,0,0),(502100,62,0,0,0,0),(502100,63,0,0,0,0),(502100,64,0,0,0,0),(502100,65,0,0,0,0),(502100,66,0,0,0,0),(502100,67,0,0,0,0),(502100,68,0,0,0,0),(503000,37,0,0,0,0),(503000,38,0,0,0,0),(503000,39,0,0,0,0),(503000,40,0,0,0,0),(503000,41,0,0,0,0),(503000,42,0,0,0,0),(503000,43,0,0,0,0),(503000,44,0,0,0,0),(503000,45,0,0,0,0),(503000,46,0,0,0,0),(503000,47,0,0,0,0),(503000,48,0,0,0,0),(503000,49,0,0,0,0),(503000,50,0,0,0,0),(503000,51,0,0,0,0),(503000,52,0,0,0,0),(503000,53,0,0,0,0),(503000,54,0,0,0,0),(503000,55,0,0,0,0),(503000,56,0,0,0,0),(503000,57,0,0,0,0),(503000,58,0,0,0,0),(503000,59,0,0,0,0),(503000,60,0,0,0,0),(503000,61,0,0,0,0),(503000,62,0,0,0,0),(503000,63,0,0,0,0),(503000,64,0,0,0,0),(503000,65,0,0,0,0),(503000,66,0,0,0,0),(503000,67,0,0,0,0),(503000,68,0,0,0,0),(503100,37,0,0,0,0),(503100,38,0,0,0,0),(503100,39,0,0,0,0),(503100,40,0,0,0,0),(503100,41,0,0,0,0),(503100,42,0,0,0,0),(503100,43,0,0,0,0),(503100,44,0,0,0,0),(503100,45,0,0,0,0),(503100,46,0,0,0,0),(503100,47,0,0,0,0),(503100,48,0,0,0,0),(503100,49,0,0,0,0),(503100,50,0,0,0,0),(503100,51,0,0,0,0),(503100,52,0,0,0,0),(503100,53,0,0,0,0),(503100,54,0,0,0,0),(503100,55,0,0,0,0),(503100,56,0,0,0,0),(503100,57,0,0,0,0),(503100,58,0,0,0,0),(503100,59,0,0,0,0),(503100,60,0,0,0,0),(503100,61,0,0,0,0),(503100,62,0,0,0,0),(503100,63,0,0,0,0),(503100,64,0,0,0,0),(503100,65,0,0,0,0),(503100,66,0,0,0,0),(503100,67,0,0,0,0),(503100,68,0,0,0,0),(504000,37,0,0,0,0),(504000,38,0,0,0,0),(504000,39,0,0,0,0),(504000,40,0,0,0,0),(504000,41,0,0,0,0),(504000,42,0,0,0,0),(504000,43,0,0,0,0),(504000,44,0,0,0,0),(504000,45,0,0,0,0),(504000,46,0,0,0,0),(504000,47,0,0,0,0),(504000,48,0,0,0,0),(504000,49,0,0,0,0),(504000,50,0,0,0,0),(504000,51,0,0,0,0),(504000,52,0,0,0,0),(504000,53,0,0,0,0),(504000,54,0,0,0,0),(504000,55,0,0,0,0),(504000,56,0,0,0,0),(504000,57,0,0,0,0),(504000,58,0,0,0,0),(504000,59,0,0,0,0),(504000,60,0,0,0,0),(504000,61,0,0,0,0),(504000,62,0,0,0,0),(504000,63,0,0,0,0),(504000,64,0,0,0,0),(504000,65,0,0,0,0),(504000,66,0,0,0,0),(504000,67,0,0,0,0),(504000,68,0,0,0,0),(505000,37,0,0,0,0),(505000,38,0,0,0,0),(505000,39,0,0,0,0),(505000,40,0,0,0,0),(505000,41,0,0,0,0),(505000,42,0,0,0,0),(505000,43,0,0,0,0),(505000,44,0,0,0,0),(505000,45,0,0,0,0),(505000,46,0,0,0,0),(505000,47,0,0,0,0),(505000,48,0,0,0,0),(505000,49,0,0,0,0),(505000,50,0,0,0,0),(505000,51,0,0,0,0),(505000,52,0,0,0,0),(505000,53,0,0,0,0),(505000,54,0,0,0,0),(505000,55,0,0,0,0),(505000,56,0,0,0,0),(505000,57,0,0,0,0),(505000,58,0,0,0,0),(505000,59,0,0,0,0),(505000,60,0,0,0,0),(505000,61,0,0,0,0),(505000,62,0,0,0,0),(505000,63,0,0,0,0),(505000,64,0,0,0,0),(505000,65,0,0,0,0),(505000,66,0,0,0,0),(505000,67,0,0,0,0),(505000,68,0,0,0,0),(505100,37,0,0,0,0),(505100,38,0,0,0,0),(505100,39,0,0,0,0),(505100,40,0,0,0,0),(505100,41,0,0,0,0),(505100,42,0,0,0,0),(505100,43,0,0,0,0),(505100,44,0,0,0,0),(505100,45,0,0,0,0),(505100,46,0,0,0,0),(505100,47,0,0,0,0),(505100,48,0,0,0,0),(505100,49,0,0,0,0),(505100,50,0,0,0,0),(505100,51,0,0,0,0),(505100,52,0,0,0,0),(505100,53,0,0,0,0),(505100,54,0,0,0,0),(505100,55,0,0,0,0),(505100,56,0,0,0,0),(505100,57,0,0,0,0),(505100,58,0,0,0,0),(505100,59,0,0,0,0),(505100,60,0,0,0,0),(505100,61,0,0,0,0),(505100,62,0,0,0,0),(505100,63,0,0,0,0),(505100,64,0,0,0,0),(505100,65,0,0,0,0),(505100,66,0,0,0,0),(505100,67,0,0,0,0),(505100,68,0,0,0,0),(505110,37,0,0,0,0),(505110,38,0,0,0,0),(505110,39,0,0,0,0),(505110,40,0,0,0,0),(505110,41,0,0,0,0),(505110,42,0,0,0,0),(505110,43,0,0,0,0),(505110,44,0,0,0,0),(505110,45,0,0,0,0),(505110,46,0,0,0,0),(505110,47,0,0,0,0),(505110,48,0,0,0,0),(505110,49,0,0,0,0),(505110,50,0,0,0,0),(505110,51,0,0,0,0),(505110,52,0,0,0,0),(505110,53,0,0,0,0),(505110,54,0,0,0,0),(505110,55,0,0,0,0),(505110,56,0,0,0,0),(505110,57,0,0,0,0),(505110,58,0,0,0,0),(505110,59,0,0,0,0),(505110,60,0,0,0,0),(505110,61,0,0,0,0),(505110,62,0,0,0,0),(505110,63,0,0,0,0),(505110,64,0,0,0,0),(505110,65,0,0,0,0),(505110,66,0,0,0,0),(505110,67,0,0,0,0),(505110,68,0,0,0,0),(510000,37,0,0,0,0),(510000,38,0,0,0,0),(510000,39,0,0,0,0),(510000,40,0,0,0,0),(510000,41,0,0,0,0),(510000,42,0,196.26,0,0),(510000,43,0,0,196.26,0),(510000,44,0,0,196.26,0),(510000,45,0,0,196.26,0),(510000,46,0,0,196.26,0),(510000,47,0,0,196.26,0),(510000,48,0,0,196.26,0),(510000,49,0,0,196.26,0),(510000,50,0,0,196.26,0),(510000,51,0,0,196.26,0),(510000,52,0,0,196.26,0),(510000,53,0,0,196.26,0),(510000,54,0,0,196.26,0),(510000,55,0,0,196.26,0),(510000,56,0,0,196.26,0),(510000,57,0,0,196.26,0),(510000,58,0,0,196.26,0),(510000,59,0,0,196.26,0),(510000,60,0,0,196.26,0),(510000,61,0,0,196.26,0),(510000,62,0,0,196.26,0),(510000,63,0,0,196.26,0),(510000,64,0,0,196.26,0),(510000,65,0,0,196.26,0),(510000,66,0,0,196.26,0),(510000,67,0,0,196.26,0),(510000,68,0,0,196.26,0),(511000,37,0,0,0,0),(511000,38,0,0,0,0),(511000,39,0,0,0,0),(511000,40,0,0,0,0),(511000,41,0,0,0,0),(511000,42,0,0,0,0),(511000,43,0,0,0,0),(511000,44,0,0,0,0),(511000,45,0,0,0,0),(511000,46,0,0,0,0),(511000,47,0,0,0,0),(511000,48,0,0,0,0),(511000,49,0,0,0,0),(511000,50,0,0,0,0),(511000,51,0,0,0,0),(511000,52,0,0,0,0),(511000,53,0,0,0,0),(511000,54,0,0,0,0),(511000,55,0,0,0,0),(511000,56,0,0,0,0),(511000,57,0,0,0,0),(511000,58,0,0,0,0),(511000,59,0,0,0,0),(511000,60,0,0,0,0),(511000,61,0,0,0,0),(511000,62,0,0,0,0),(511000,63,0,0,0,0),(511000,64,0,0,0,0),(511000,65,0,0,0,0),(511000,66,0,0,0,0),(511000,67,0,0,0,0),(511000,68,0,0,0,0),(513000,37,0,0,0,0),(513000,38,0,0,0,0),(513000,39,0,0,0,0),(513000,40,0,0,0,0),(513000,41,0,0,0,0),(513000,42,0,0,0,0),(513000,43,0,0,0,0),(513000,44,0,0,0,0),(513000,45,0,0,0,0),(513000,46,0,0,0,0),(513000,47,0,0,0,0),(513000,48,0,0,0,0),(513000,49,0,0,0,0),(513000,50,0,0,0,0),(513000,51,0,0,0,0),(513000,52,0,0,0,0),(513000,53,0,0,0,0),(513000,54,0,0,0,0),(513000,55,0,0,0,0),(513000,56,0,0,0,0),(513000,57,0,0,0,0),(513000,58,0,0,0,0),(513000,59,0,0,0,0),(513000,60,0,0,0,0),(513000,61,0,0,0,0),(513000,62,0,0,0,0),(513000,63,0,0,0,0),(513000,64,0,0,0,0),(513000,65,0,0,0,0),(513000,66,0,0,0,0),(513000,67,0,0,0,0),(513000,68,0,0,0,0),(514000,37,0,0,0,0),(514000,38,0,0,0,0),(514000,39,0,0,0,0),(514000,40,0,0,0,0),(514000,41,0,0,0,0),(514000,42,0,0,0,0),(514000,43,0,0,0,0),(514000,44,0,0,0,0),(514000,45,0,0,0,0),(514000,46,0,0,0,0),(514000,47,0,0,0,0),(514000,48,0,0,0,0),(514000,49,0,0,0,0),(514000,50,0,0,0,0),(514000,51,0,0,0,0),(514000,52,0,0,0,0),(514000,53,0,0,0,0),(514000,54,0,0,0,0),(514000,55,0,0,0,0),(514000,56,0,0,0,0),(514000,57,0,0,0,0),(514000,58,0,0,0,0),(514000,59,0,0,0,0),(514000,60,0,0,0,0),(514000,61,0,0,0,0),(514000,62,0,0,0,0),(514000,63,0,0,0,0),(514000,64,0,0,0,0),(514000,65,0,0,0,0),(514000,66,0,0,0,0),(514000,67,0,0,0,0),(514000,68,0,0,0,0),(515000,37,0,0,0,0),(515000,38,0,0,0,0),(515000,39,0,0,0,0),(515000,40,0,0,0,0),(515000,41,0,0,0,0),(515000,42,0,0,0,0),(515000,43,0,0,0,0),(515000,44,0,0,0,0),(515000,45,0,0,0,0),(515000,46,0,0,0,0),(515000,47,0,0,0,0),(515000,48,0,0,0,0),(515000,49,0,0,0,0),(515000,50,0,0,0,0),(515000,51,0,0,0,0),(515000,52,0,0,0,0),(515000,53,0,0,0,0),(515000,54,0,0,0,0),(515000,55,0,0,0,0),(515000,56,0,0,0,0),(515000,57,0,0,0,0),(515000,58,0,0,0,0),(515000,59,0,0,0,0),(515000,60,0,0,0,0),(515000,61,0,0,0,0),(515000,62,0,0,0,0),(515000,63,0,0,0,0),(515000,64,0,0,0,0),(515000,65,0,0,0,0),(515000,66,0,0,0,0),(515000,67,0,0,0,0),(515000,68,0,0,0,0),(516000,37,0,0,0,0),(516000,38,0,0,0,0),(516000,39,0,0,0,0),(516000,40,0,0,0,0),(516000,41,0,0,0,0),(516000,42,0,0,0,0),(516000,43,0,0,0,0),(516000,44,0,0,0,0),(516000,45,0,0,0,0),(516000,46,0,0,0,0),(516000,47,0,0,0,0),(516000,48,0,0,0,0),(516000,49,0,0,0,0),(516000,50,0,0,0,0),(516000,51,0,0,0,0),(516000,52,0,0,0,0),(516000,53,0,0,0,0),(516000,54,0,0,0,0),(516000,55,0,0,0,0),(516000,56,0,0,0,0),(516000,57,0,0,0,0),(516000,58,0,0,0,0),(516000,59,0,0,0,0),(516000,60,0,0,0,0),(516000,61,0,0,0,0),(516000,62,0,0,0,0),(516000,63,0,0,0,0),(516000,64,0,0,0,0),(516000,65,0,0,0,0),(516000,66,0,0,0,0),(516000,67,0,0,0,0),(516000,68,0,0,0,0),(520000,37,0,0,0,0),(520000,38,0,0,0,0),(520000,39,0,0,0,0),(520000,40,0,0,0,0),(520000,41,0,0,0,0),(520000,42,0,0,0,0),(520000,43,0,0,0,0),(520000,44,0,0,0,0),(520000,45,0,0,0,0),(520000,46,0,0,0,0),(520000,47,0,0,0,0),(520000,48,0,0,0,0),(520000,49,0,0,0,0),(520000,50,0,0,0,0),(520000,51,0,0,0,0),(520000,52,0,0,0,0),(520000,53,0,0,0,0),(520000,54,0,0,0,0),(520000,55,0,0,0,0),(520000,56,0,0,0,0),(520000,57,0,0,0,0),(520000,58,0,0,0,0),(520000,59,0,0,0,0),(520000,60,0,0,0,0),(520000,61,0,0,0,0),(520000,62,0,0,0,0),(520000,63,0,0,0,0),(520000,64,0,0,0,0),(520000,65,0,0,0,0),(520000,66,0,0,0,0),(520000,67,0,0,0,0),(520000,68,0,0,0,0),(540000,37,0,0,0,0),(540000,38,0,0,0,0),(540000,39,0,0,0,0),(540000,40,0,0,0,0),(540000,41,0,0,0,0),(540000,42,0,0,0,0),(540000,43,0,0,0,0),(540000,44,0,0,0,0),(540000,45,0,0,0,0),(540000,46,0,0,0,0),(540000,47,0,0,0,0),(540000,48,0,0,0,0),(540000,49,0,0,0,0),(540000,50,0,0,0,0),(540000,51,0,0,0,0),(540000,52,0,0,0,0),(540000,53,0,0,0,0),(540000,54,0,0,0,0),(540000,55,0,0,0,0),(540000,56,0,0,0,0),(540000,57,0,0,0,0),(540000,58,0,0,0,0),(540000,59,0,0,0,0),(540000,60,0,0,0,0),(540000,61,0,0,0,0),(540000,62,0,0,0,0),(540000,63,0,0,0,0),(540000,64,0,0,0,0),(540000,65,0,0,0,0),(540000,66,0,0,0,0),(540000,67,0,0,0,0),(540000,68,0,0,0,0),(541000,37,0,0,0,0),(541000,38,0,0,0,0),(541000,39,0,0,0,0),(541000,40,0,0,0,0),(541000,41,0,0,0,0),(541000,42,0,0,0,0),(541000,43,0,0,0,0),(541000,44,0,0,0,0),(541000,45,0,0,0,0),(541000,46,0,0,0,0),(541000,47,0,0,0,0),(541000,48,0,0,0,0),(541000,49,0,0,0,0),(541000,50,0,0,0,0),(541000,51,0,0,0,0),(541000,52,0,0,0,0),(541000,53,0,0,0,0),(541000,54,0,0,0,0),(541000,55,0,0,0,0),(541000,56,0,0,0,0),(541000,57,0,0,0,0),(541000,58,0,0,0,0),(541000,59,0,0,0,0),(541000,60,0,0,0,0),(541000,61,0,0,0,0),(541000,62,0,0,0,0),(541000,63,0,0,0,0),(541000,64,0,0,0,0),(541000,65,0,0,0,0),(541000,66,0,0,0,0),(541000,67,0,0,0,0),(541000,68,0,0,0,0),(542000,37,0,0,0,0),(542000,38,0,0,0,0),(542000,39,0,0,0,0),(542000,40,0,0,0,0),(542000,41,0,0,0,0),(542000,42,0,0,0,0),(542000,43,0,0,0,0),(542000,44,0,0,0,0),(542000,45,0,0,0,0),(542000,46,0,0,0,0),(542000,47,0,0,0,0),(542000,48,0,0,0,0),(542000,49,0,0,0,0),(542000,50,0,0,0,0),(542000,51,0,0,0,0),(542000,52,0,0,0,0),(542000,53,0,0,0,0),(542000,54,0,0,0,0),(542000,55,0,0,0,0),(542000,56,0,0,0,0),(542000,57,0,0,0,0),(542000,58,0,0,0,0),(542000,59,0,0,0,0),(542000,60,0,0,0,0),(542000,61,0,0,0,0),(542000,62,0,0,0,0),(542000,63,0,0,0,0),(542000,64,0,0,0,0),(542000,65,0,0,0,0),(542000,66,0,0,0,0),(542000,67,0,0,0,0),(542000,68,0,0,0,0),(550000,37,0,0,0,0),(550000,38,0,0,0,0),(550000,39,0,0,0,0),(550000,40,0,0,0,0),(550000,41,0,0,0,0),(550000,42,0,0,0,0),(550000,43,0,0,0,0),(550000,44,0,0,0,0),(550000,45,0,0,0,0),(550000,46,0,0,0,0),(550000,47,0,0,0,0),(550000,48,0,0,0,0),(550000,49,0,0,0,0),(550000,50,0,0,0,0),(550000,51,0,0,0,0),(550000,52,0,0,0,0),(550000,53,0,0,0,0),(550000,54,0,0,0,0),(550000,55,0,0,0,0),(550000,56,0,0,0,0),(550000,57,0,0,0,0),(550000,58,0,0,0,0),(550000,59,0,0,0,0),(550000,60,0,0,0,0),(550000,61,0,0,0,0),(550000,62,0,0,0,0),(550000,63,0,0,0,0),(550000,64,0,0,0,0),(550000,65,0,0,0,0),(550000,66,0,0,0,0),(550000,67,0,0,0,0),(550000,68,0,0,0,0),(560000,37,0,0,0,0),(560000,38,0,0,0,0),(560000,39,0,0,0,0),(560000,40,0,0,0,0),(560000,41,0,0,0,0),(560000,42,0,0,0,0),(560000,43,0,0,0,0),(560000,44,0,0,0,0),(560000,45,0,0,0,0),(560000,46,0,0,0,0),(560000,47,0,0,0,0),(560000,48,0,0,0,0),(560000,49,0,0,0,0),(560000,50,0,0,0,0),(560000,51,0,0,0,0),(560000,52,0,0,0,0),(560000,53,0,0,0,0),(560000,54,0,0,0,0),(560000,55,0,0,0,0),(560000,56,0,0,0,0),(560000,57,0,0,0,0),(560000,58,0,0,0,0),(560000,59,0,0,0,0),(560000,60,0,0,0,0),(560000,61,0,0,0,0),(560000,62,0,0,0,0),(560000,63,0,0,0,0),(560000,64,0,0,0,0),(560000,65,0,0,0,0),(560000,66,0,0,0,0),(560000,67,0,0,0,0),(560000,68,0,0,0,0),(700000,37,0,1020.06,0,0),(700000,38,0,-807.54,1020.06,0),(700000,39,0,53.25,212.52,0),(700000,40,0,-390.51,265.77,0),(700000,41,0,-17192.3,-124.74,0),(700000,42,0,847.56,-17317.1,0),(700000,43,0,0,-16469.5,0),(700000,44,0,-15,-16469.5,0),(700000,45,0,0,-16484.5,0),(700000,46,0,-259.05,-16484.5,0),(700000,47,0,0,-16743.6,0),(700000,48,0,0,-16743.6,0),(700000,49,0,2208.56,-16743.6,0),(700000,50,0,0,-14535,0),(700000,51,0,0,-14535,0),(700000,52,0,0,-14535,0),(700000,53,0,0,-14535,0),(700000,54,0,0,-14535,0),(700000,55,0,0,-14535,0),(700000,56,0,0,-14535,0),(700000,57,0,0,-14535,0),(700000,58,0,0,-14535,0),(700000,59,0,0,-14535,0),(700000,60,0,0,-14535,0),(700000,61,0,0,-14535,0),(700000,62,0,0,-14535,0),(700000,63,0,0,-14535,0),(700000,64,0,0,-14535,0),(700000,65,0,0,-14535,0),(700000,66,0,0,-14535,0),(700000,67,0,0,-14535,0),(700000,68,0,0,-14535,0),(700100,37,0,-24,0,0),(700100,38,0,0,-24,0),(700100,39,0,0,-24,0),(700100,40,0,390.51,-24,0),(700100,41,0,16818,366.51,0),(700100,42,0,0,17184.5,0),(700100,43,0,0,17184.5,0),(700100,44,0,0,17184.5,0),(700100,45,0,0,17184.5,0),(700100,46,0,259.05,17184.5,0),(700100,47,0,0,17443.6,0),(700100,48,0,0,17443.6,0),(700100,49,0,0,17443.6,0),(700100,50,0,0,17443.6,0),(700100,51,0,0,17443.6,0),(700100,52,0,0,17443.6,0),(700100,53,0,0,17443.6,0),(700100,54,0,0,17443.6,0),(700100,55,0,0,17443.6,0),(700100,56,0,0,17443.6,0),(700100,57,0,0,17443.6,0),(700100,58,0,0,17443.6,0),(700100,59,0,0,17443.6,0),(700100,60,0,0,17443.6,0),(700100,61,0,0,17443.6,0),(700100,62,0,0,17443.6,0),(700100,63,0,0,17443.6,0),(700100,64,0,0,17443.6,0),(700100,65,0,0,17443.6,0),(700100,66,0,0,17443.6,0),(700100,67,0,0,17443.6,0),(700100,68,0,0,17443.6,0),(701000,37,0,0,0,0),(701000,38,0,0,0,0),(701000,39,0,0,0,0),(701000,40,0,0,0,0),(701000,41,0,0,0,0),(701000,42,0,0,0,0),(701000,43,0,0,0,0),(701000,44,0,0,0,0),(701000,45,0,0,0,0),(701000,46,0,0,0,0),(701000,47,0,0,0,0),(701000,48,0,0,0,0),(701000,49,0,0,0,0),(701000,50,0,0,0,0),(701000,51,0,0,0,0),(701000,52,0,0,0,0),(701000,53,0,0,0,0),(701000,54,0,0,0,0),(701000,55,0,0,0,0),(701000,56,0,0,0,0),(701000,57,0,0,0,0),(701000,58,0,0,0,0),(701000,59,0,0,0,0),(701000,60,0,0,0,0),(701000,61,0,0,0,0),(701000,62,0,0,0,0),(701000,63,0,0,0,0),(701000,64,0,0,0,0),(701000,65,0,0,0,0),(701000,66,0,0,0,0),(701000,67,0,0,0,0),(701000,68,0,0,0,0),(710000,37,0,4500.21,0,0),(710000,38,0,957.84,4500.21,0),(710000,39,0,-16.98,5458.06,0),(710000,40,0,0,5441.08,0),(710000,41,0,26.53,5441.08,0),(710000,42,0,2307.43,5467.61,0),(710000,43,0,603.037,7775.04,0),(710000,44,0,-32.6271,8378.08,0),(710000,45,0,0,8345.45,0),(710000,46,0,-76.13,8345.45,0),(710000,47,0,0,8269.32,0),(710000,48,0,37.32,8269.32,0),(710000,49,0,-3036.9,8306.64,0),(710000,50,0,0,5269.75,0),(710000,51,0,0,5269.75,0),(710000,52,0,0,5269.75,0),(710000,53,0,0,5269.75,0),(710000,54,0,0,5269.75,0),(710000,55,0,0,5269.75,0),(710000,56,0,0,5269.75,0),(710000,57,0,0,5269.75,0),(710000,58,0,0,5269.75,0),(710000,59,0,-73.4,5269.75,0),(710000,60,0,0,5196.35,0),(710000,61,0,0,5196.35,0),(710000,62,0,0,5196.35,0),(710000,63,0,0,5196.35,0),(710000,64,0,0,5196.35,0),(710000,65,0,-267.05,5196.35,0),(710000,66,0,-423.66,4929.29,0),(710000,67,0,0,4505.64,0),(710000,68,0,0,4505.64,0),(720000,37,0,0,0,0),(720000,38,0,0,0,0),(720000,39,0,0,0,0),(720000,40,0,0,0,0),(720000,41,0,0,0,0),(720000,42,0,38.5,0,0),(720000,43,0,0,38.5,0),(720000,44,0,0,38.5,0),(720000,45,0,0,38.5,0),(720000,46,0,0,38.5,0),(720000,47,0,0,38.5,0),(720000,48,0,-8.9,38.5,0),(720000,49,0,0,29.6,0),(720000,50,0,0,29.6,0),(720000,51,0,0,29.6,0),(720000,52,0,0,29.6,0),(720000,53,0,0,29.6,0),(720000,54,0,0,29.6,0),(720000,55,0,0,29.6,0),(720000,56,0,0,29.6,0),(720000,57,0,0,29.6,0),(720000,58,0,0,29.6,0),(720000,59,0,0,29.6,0),(720000,60,0,0,29.6,0),(720000,61,0,0,29.6,0),(720000,62,0,0,29.6,0),(720000,63,0,0,29.6,0),(720000,64,0,0,29.6,0),(720000,65,0,0,29.6,0),(720000,66,0,0,29.6,0),(720000,67,0,0,29.6,0),(720000,68,0,0,29.6,0),(721000,37,0,0,0,0),(721000,38,0,0,0,0),(721000,39,0,0,0,0),(721000,40,0,0,0,0),(721000,41,0,0,0,0),(721000,42,0,-984,0,0),(721000,43,0,0,-984,0),(721000,44,0,0,-984,0),(721000,45,0,0,-984,0),(721000,46,0,0,-984,0),(721000,47,0,0,-984,0),(721000,48,0,0,-984,0),(721000,49,0,0,-984,0),(721000,50,0,0,-984,0),(721000,51,0,0,-984,0),(721000,52,0,0,-984,0),(721000,53,0,0,-984,0),(721000,54,0,0,-984,0),(721000,55,0,0,-984,0),(721000,56,0,0,-984,0),(721000,57,0,0,-984,0),(721000,58,0,0,-984,0),(721000,59,0,0,-984,0),(721000,60,0,0,-984,0),(721000,61,0,0,-984,0),(721000,62,0,0,-984,0),(721000,63,0,0,-984,0),(721000,64,0,0,-984,0),(721000,65,0,0,-984,0),(721000,66,0,0,-984,0),(721000,67,0,0,-984,0),(721000,68,0,0,-984,0),(722000,37,0,91086,0,0),(722000,38,0,-503.88,91086,0),(722000,39,0,-33.91,90582.1,0),(722000,40,0,0,90548.2,0),(722000,41,0,-32.46,90548.2,0),(722000,42,0,-480.86,90515.8,0),(722000,43,0,-101.3,90034.9,0),(722000,44,0,0,89933.6,0),(722000,45,0,0,89933.6,0),(722000,46,0,0,89933.6,0),(722000,47,0,0,89933.6,0),(722000,48,0,0,89933.6,0),(722000,49,0,-27.6,89933.6,0),(722000,50,0,0,89906,0),(722000,51,0,0,89906,0),(722000,52,0,0,89906,0),(722000,53,0,0,89906,0),(722000,54,0,0,89906,0),(722000,55,0,0,89906,0),(722000,56,0,0,89906,0),(722000,57,0,0,89906,0),(722000,58,0,18587.1,89906,0),(722000,59,0,6.3,108493,0),(722000,60,0,0,108499,0),(722000,61,0,0,108499,0),(722000,62,0,0,108499,0),(722000,63,0,0,108499,0),(722000,64,0,0,108499,0),(722000,65,0,68.64,108499,0),(722000,66,0,222.87,108568,0),(722000,67,0,0,108791,0),(722000,68,0,0,108791,0),(723000,37,0,376.07,0,0),(723000,38,0,0,376.07,0),(723000,39,0,0,376.07,0),(723000,40,0,0,376.07,0),(723000,41,0,0,376.07,0),(723000,42,0,251.09,376.07,0),(723000,43,0,-73,627.16,0),(723000,44,0,0,554.16,0),(723000,45,0,0,554.16,0),(723000,46,0,0,554.16,0),(723000,47,0,0,554.16,0),(723000,48,0,0,554.16,0),(723000,49,0,0,554.16,0),(723000,50,0,0,554.16,0),(723000,51,0,0,554.16,0),(723000,52,0,0,554.16,0),(723000,53,0,0,554.16,0),(723000,54,0,0,554.16,0),(723000,55,0,0,554.16,0),(723000,56,0,0,554.16,0),(723000,57,0,0,554.16,0),(723000,58,0,0,554.16,0),(723000,59,0,0,554.16,0),(723000,60,0,0,554.16,0),(723000,61,0,0,554.16,0),(723000,62,0,0,554.16,0),(723000,63,0,0,554.16,0),(723000,64,0,0,554.16,0),(723000,65,0,0,554.16,0),(723000,66,0,0,554.16,0),(723000,67,0,0,554.16,0),(723000,68,0,0,554.16,0),(800000,37,0,-5520.2,0,0),(800000,38,0,724.37,-5520.2,0),(800000,39,0,0,-4795.83,0),(800000,40,0,0,-4795.83,0),(800000,41,0,0,-4795.83,0),(800000,42,0,-695.74,-4795.83,0),(800000,43,0,0,-5491.57,0),(800000,44,0,-558.63,-5491.57,0),(800000,45,0,0,-6050.19,0),(800000,46,0,0,-6050.19,0),(800000,47,0,0,-6050.19,0),(800000,48,0,0,-6050.19,0),(800000,49,0,0,-6050.19,0),(800000,50,0,0,-6050.19,0),(800000,51,0,0,-6050.19,0),(800000,52,0,0,-6050.19,0),(800000,53,0,0,-6050.19,0),(800000,54,0,0,-6050.19,0),(800000,55,0,0,-6050.19,0),(800000,56,0,0,-6050.19,0),(800000,57,0,0,-6050.19,0),(800000,58,0,0,-6050.19,0),(800000,59,0,0,-6050.19,0),(800000,60,0,0,-6050.19,0),(800000,61,0,0,-6050.19,0),(800000,62,0,0,-6050.19,0),(800000,63,0,0,-6050.19,0),(800000,64,0,0,-6050.19,0),(800000,65,0,0,-6050.19,0),(800000,66,0,0,-6050.19,0),(800000,67,0,0,-6050.19,0),(800000,68,0,0,-6050.19,0),(810000,37,0,0,0,0),(810000,38,0,0,0,0),(810000,39,0,-5.37,0,0),(810000,40,0,0,-5.37,0),(810000,41,0,0,-5.37,0),(810000,42,0,-39.7,-5.37,0),(810000,43,0,-33.24,-45.07,0),(810000,44,0,0,-78.31,0),(810000,45,0,0,-78.31,0),(810000,46,0,0,-78.31,0),(810000,47,0,0,-78.31,0),(810000,48,0,0,-78.31,0),(810000,49,0,-1.59,-78.31,0),(810000,50,0,0,-79.9,0),(810000,51,0,0,-79.9,0),(810000,52,0,0,-79.9,0),(810000,53,0,0,-79.9,0),(810000,54,0,0,-79.9,0),(810000,55,0,0,-79.9,0),(810000,56,0,0,-79.9,0),(810000,57,0,0,-79.9,0),(810000,58,0,0,-79.9,0),(810000,59,0,0,-79.9,0),(810000,60,0,0,-79.9,0),(810000,61,0,0,-79.9,0),(810000,62,0,0,-79.9,0),(810000,63,0,0,-79.9,0),(810000,64,0,0,-79.9,0),(810000,65,0,0,-79.9,0),(810000,66,0,0,-79.9,0),(810000,67,0,0,-79.9,0),(810000,68,0,0,-79.9,0),(820000,37,0,0,0,0),(820000,38,0,0,0,0),(820000,39,0,0,0,0),(820000,40,0,0,0,0),(820000,41,0,0,0,0),(820000,42,0,0,0,0),(820000,43,0,0,0,0),(820000,44,0,0,0,0),(820000,45,0,0,0,0),(820000,46,0,0,0,0),(820000,47,0,0,0,0),(820000,48,0,0,0,0),(820000,49,0,0,0,0),(820000,50,0,0,0,0),(820000,51,0,0,0,0),(820000,52,0,0,0,0),(820000,53,0,0,0,0),(820000,54,0,0,0,0),(820000,55,0,0,0,0),(820000,56,0,0,0,0),(820000,57,0,0,0,0),(820000,58,0,0,0,0),(820000,59,0,0,0,0),(820000,60,0,0,0,0),(820000,61,0,0,0,0),(820000,62,0,0,0,0),(820000,63,0,0,0,0),(820000,64,0,0,0,0),(820000,65,0,0,0,0),(820000,66,0,0,0,0),(820000,67,0,0,0,0),(820000,68,0,0,0,0),(890000,37,0,477.76,0,0),(890000,38,0,-159.64,477.76,0),(890000,39,0,0,318.12,0),(890000,40,0,0,318.12,0),(890000,41,0,-4.42,318.12,0),(890000,42,0,-943.68,313.7,0),(890000,43,0,-42.82,-629.98,0),(890000,44,0,52.37,-672.8,0),(890000,45,0,0,-620.42,0),(890000,46,0,12.69,-620.42,0),(890000,47,0,0,-607.73,0),(890000,48,0,-6.22,-607.73,0),(890000,49,0,-15.54,-613.95,0),(890000,50,0,0,-629.49,0),(890000,51,0,0,-629.49,0),(890000,52,0,0,-629.49,0),(890000,53,0,0,-629.49,0),(890000,54,0,0,-629.49,0),(890000,55,0,0,-629.49,0),(890000,56,0,0,-629.49,0),(890000,57,0,0,-629.49,0),(890000,58,0,0,-629.49,0),(890000,59,0,12.23,-629.49,0),(890000,60,0,0,-617.27,0),(890000,61,0,0,-617.27,0),(890000,62,0,0,-617.27,0),(890000,63,0,0,-617.27,0),(890000,64,0,0,-617.27,0),(890000,65,0,0,-617.27,0),(890000,66,0,70.61,-617.27,0),(890000,67,0,0,-546.66,0),(890000,68,0,0,-546.66,0),(900000,37,0,0,0,0),(900000,38,0,0,0,0),(900000,39,0,0,0,0),(900000,40,0,0,0,0),(900000,41,0,0,0,0),(900000,42,0,0,0,0),(900000,43,0,0,0,0),(900000,44,0,0,0,0),(900000,45,0,0,0,0),(900000,46,0,0,0,0),(900000,47,0,0,0,0),(900000,48,0,0,0,0),(900000,49,0,0,0,0),(900000,50,0,0,0,0),(900000,51,0,0,0,0),(900000,52,0,0,0,0),(900000,53,0,0,0,0),(900000,54,0,0,0,0),(900000,55,0,0,0,0),(900000,56,0,0,0,0),(900000,57,0,0,0,0),(900000,58,0,0,0,0),(900000,59,0,0,0,0),(900000,60,0,0,0,0),(900000,61,0,0,0,0),(900000,62,0,0,0,0),(900000,63,0,0,0,0),(900000,64,0,0,0,0),(900000,65,0,0,0,0),(900000,66,0,0,0,0),(900000,67,0,0,0,0),(900000,68,0,0,0,0),(910000,37,0,0,0,0),(910000,38,0,0,0,0),(910000,39,0,0,0,0),(910000,40,0,0,0,0),(910000,41,0,0,0,0),(910000,42,0,0,0,0),(910000,43,0,0,0,0),(910000,44,0,0,0,0),(910000,45,0,0,0,0),(910000,46,0,0,0,0),(910000,47,0,0,0,0),(910000,48,0,0,0,0),(910000,49,0,0,0,0),(910000,50,0,0,0,0),(910000,51,0,0,0,0),(910000,52,0,0,0,0),(910000,53,0,0,0,0),(910000,54,0,0,0,0),(910000,55,0,0,0,0),(910000,56,0,0,0,0),(910000,57,0,0,0,0),(910000,58,0,0,0,0),(910000,59,0,0,0,0),(910000,60,0,0,0,0),(910000,61,0,0,0,0),(910000,62,0,0,0,0),(910000,63,0,0,0,0),(910000,64,0,0,0,0),(910000,65,0,0,0,0),(910000,66,0,0,0,0),(910000,67,0,0,0,0),(910000,68,0,0,0,0),(920000,37,0,0,0,0),(920000,38,0,0,0,0),(920000,39,0,0,0,0),(920000,40,0,0,0,0),(920000,41,0,0,0,0),(920000,42,0,0,0,0),(920000,43,0,0,0,0),(920000,44,0,0,0,0),(920000,45,0,0,0,0),(920000,46,0,0,0,0),(920000,47,0,0,0,0),(920000,48,0,0,0,0),(920000,49,0,0,0,0),(920000,50,0,0,0,0),(920000,51,0,0,0,0),(920000,52,0,0,0,0),(920000,53,0,0,0,0),(920000,54,0,0,0,0),(920000,55,0,0,0,0),(920000,56,0,0,0,0),(920000,57,0,0,0,0),(920000,58,0,0,0,0),(920000,59,0,0,0,0),(920000,60,0,0,0,0),(920000,61,0,0,0,0),(920000,62,0,0,0,0),(920000,63,0,0,0,0),(920000,64,0,0,0,0),(920000,65,0,0,0,0),(920000,66,0,0,0,0),(920000,67,0,0,0,0),(920000,68,0,0,0,0),(930000,37,0,0,0,0),(930000,38,0,0,0,0),(930000,39,0,0,0,0),(930000,40,0,0,0,0),(930000,41,0,0,0,0),(930000,42,0,0,0,0),(930000,43,0,0,0,0),(930000,44,0,0,0,0),(930000,45,0,0,0,0),(930000,46,0,0,0,0),(930000,47,0,0,0,0),(930000,48,0,0,0,0),(930000,49,0,0,0,0),(930000,50,0,0,0,0),(930000,51,0,0,0,0),(930000,52,0,0,0,0),(930000,53,0,0,0,0),(930000,54,0,0,0,0),(930000,55,0,0,0,0),(930000,56,0,0,0,0),(930000,57,0,0,0,0),(930000,58,0,0,0,0),(930000,59,0,0,0,0),(930000,60,0,0,0,0),(930000,61,0,0,0,0),(930000,62,0,0,0,0),(930000,63,0,0,0,0),(930000,64,0,0,0,0),(930000,65,0,0,0,0),(930000,66,0,0,0,0),(930000,67,0,0,0,0),(930000,68,0,0,0,0),(940000,37,0,0,0,0),(940000,38,0,0,0,0),(940000,39,0,0,0,0),(940000,40,0,0,0,0),(940000,41,0,0,0,0),(940000,42,0,0,0,0),(940000,43,0,0,0,0),(940000,44,0,0,0,0),(940000,45,0,0,0,0),(940000,46,0,0,0,0),(940000,47,0,0,0,0),(940000,48,0,0,0,0),(940000,49,0,0,0,0),(940000,50,0,0,0,0),(940000,51,0,0,0,0),(940000,52,0,0,0,0),(940000,53,0,0,0,0),(940000,54,0,0,0,0),(940000,55,0,0,0,0),(940000,56,0,0,0,0),(940000,57,0,0,0,0),(940000,58,0,0,0,0),(940000,59,0,0,0,0),(940000,60,0,0,0,0),(940000,61,0,0,0,0),(940000,62,0,0,0,0),(940000,63,0,0,0,0),(940000,64,0,0,0,0),(940000,65,0,0,0,0),(940000,66,0,0,0,0),(940000,67,0,0,0,0),(940000,68,0,0,0,0);
UNLOCK TABLES;
/*!40000 ALTER TABLE ChartDetails ENABLE KEYS */;

--
-- Dumping data for table `ChartMaster`
--


/*!40000 ALTER TABLE ChartMaster DISABLE KEYS */;
LOCK TABLES ChartMaster WRITE;
INSERT INTO ChartMaster VALUES (1,'Default Sales/Discounts','Sales'),(100,'Sales - Retail','Sales'),(107,'Sales  - Wholesale','Sales'),(112,'Sales - Export','Sales'),(200,'Sales of Other items','Sales'),(301,'Difference On Exchange','Sales'),(10000,'Direct Labour','Labour'),(11000,'Direct Labour Recovery','Labour'),(12000,'Labour Efficiency Variance','Labour'),(19000,'Material Usage Varaiance','Materials and Supplies'),(20000,'Consumable Materials','Materials and Supplies'),(21000,'Samples','Materials and Supplies'),(23400,'Purchase Price Variance','Materials and Supplies'),(23500,'Purchases of materials','Materials and Supplies'),(23600,'Discounts Received','Materials and Supplies'),(23700,'Exchange Variation','Materials and Supplies'),(24000,'Freight Inwards','Materials and Supplies'),(70100,'Cost of Goods Sold - Retail','Standard Cost Of Goods Sold'),(70200,'Cost of Goods Sold - Wholesale','Standard Cost Of Goods Sold'),(70300,'Cost of Goods Sold - Export','Standard Cost Of Goods Sold'),(210000,'Bank Charges','Admin Expenses'),(250000,'Salaries - Administration','Admin Expenses'),(251000,'ACC Admin Salaries','Admin Expenses'),(252000,'Holiday Pay - Admin Salaries','Admin Expenses'),(253000,'Audit Fees','Admin Expenses'),(255000,'Insurances','Admin Expenses'),(256000,'Consultancy','Admin Expenses'),(257000,'Director\'s fees','Admin Expenses'),(258000,'Donations','Admin Expenses'),(259000,'Entertainments','Admin Expenses'),(261000,'Fringe Benefit Tax','Admin Expenses'),(262000,'Legal Expenses','Admin Expenses'),(263000,'Office Supplies','Admin Expenses'),(263100,'Stationery','Admin Expenses'),(264000,'Repairs and Maintenance Office','Admin Expenses'),(265000,'Staff Recruitment Admin','Admin Expenses'),(266000,'Staff Training Admin','Admin Expenses'),(267000,'Telephone','Admin Expenses'),(267100,'Fax','Admin Expenses'),(270000,'Vehicle Expenses','Admin Expenses'),(271000,'Vehicle Depreciation','Admin Expenses'),(310000,'Bad Debts','Admin Expenses'),(320000,'Bank Interest','Admin Expenses'),(330000,'Credit Control','Admin Expenses'),(340000,'Depreciation Office Equipment','Admin Expenses'),(342000,'Loss/(Profit) on disposals','Admin Expenses'),(400000,'Salaries - Sales','Selling Expenses'),(410000,'ACC Sales','Selling Expenses'),(411000,'Holiday Pay - Sales','Selling Expenses'),(412000,'Staff training Sales','Selling Expenses'),(413000,'Entertainments Sales','Selling Expenses'),(420000,'Advertising','Selling Expenses'),(450000,'Freight Outwards','Selling Expenses'),(451000,'Packaging','Selling Expenses'),(452000,'Commissions','Selling Expenses'),(455000,'Prompt Payment Discounts','Selling Expenses'),(460000,'General Expenses','Selling Expenses'),(470000,'Travel - Sales','Selling Expenses'),(480000,'Vehicle expenses - Sales','Selling Expenses'),(481000,'Vehicle Depreciation - Sales','Selling Expenses'),(500000,'Salaries Manufacturing','Labour'),(501000,'Indirect Labour','Labour'),(501800,'Indirect Labour Sick Pay','Labour'),(501900,'Indirect Labour Holiday Pay','Labour'),(502000,'Electricity','Labour'),(502100,'Gas','Labour'),(503000,'Plant Repairs','Labour'),(503100,'Research and Development','Labour'),(504000,'Outside Contractors','Labour'),(505000,'Depreciation Plant','Labour'),(505100,'Depreciation Buildings','Labour'),(505110,'Building and Grounds Maintenance','Labour'),(510000,'ACC Manufacturing','Labour'),(511000,'Holiday Pay manufacturing','Labour'),(513000,'Staff training - Manufacturing','Labour'),(514000,'Staff Social Club','Labour'),(515000,'Staff Medical Insurance','Labour'),(516000,'Superanuation Manufacturing','Labour'),(520000,'Cleaning Factory','Labour'),(540000,'Entertainments - Manufacturing','Labour'),(541000,'General Expenses - Manufacturing','Labour'),(542000,'Subscriptions and Magazines','Labour'),(550000,'Travel - Manufacturing','Labour'),(560000,'Overhead Recovery','Overhead Recovery'),(700000,'Bank Account','Current Assets'),(700100,'Petty Cash','Current Assets'),(701000,'Foreign Currency Account','Current Assets'),(710000,'Debtors Control Account','Current Assets'),(720000,'Stocks of Raw Materials','Current Assets'),(721000,'Stocks of Work In Progress','Current Assets'),(722000,'Stocks of Finsihed Goods','Current Assets'),(723000,'Goods Received Clearing Account','Current Liabilites'),(800000,'Creditors Control Account','Current Liabilites'),(810000,'Sundry Creditors','Current Liabilites'),(820000,'Sundry Accruals','Current Liabilites'),(890000,'VAT Outstanding','Current Liabilites'),(900000,'Retained Earnings','Equity'),(910000,'Share Capital','Equity'),(920000,'Shareholders loans','Equity'),(930000,'Capital Reserves','Equity'),(940000,'Revaluation Reserve','Equity');
UNLOCK TABLES;
/*!40000 ALTER TABLE ChartMaster ENABLE KEYS */;

--
-- Dumping data for table `Companies`
--


/*!40000 ALTER TABLE Companies DISABLE KEYS */;
LOCK TABLES Companies WRITE;
INSERT INTO Companies VALUES (1,'Logic Works Demo System','15-325-122','','PO Box 989 Wellington Mail Centre, New Zealand','12 Downing Street,','Upper Hutt','New Zealand','+(64) (04) 567 5411','+(64) (04) 567 5412','p.daintree@paradise.net.nz','USD',710000,455000,800000,810000,723000,23700,23700,900000,1,1,1,450000);
UNLOCK TABLES;
/*!40000 ALTER TABLE Companies ENABLE KEYS */;

--
-- Dumping data for table `ContractBOM`
--


/*!40000 ALTER TABLE ContractBOM DISABLE KEYS */;
LOCK TABLES ContractBOM WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE ContractBOM ENABLE KEYS */;

--
-- Dumping data for table `ContractReqts`
--


/*!40000 ALTER TABLE ContractReqts DISABLE KEYS */;
LOCK TABLES ContractReqts WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE ContractReqts ENABLE KEYS */;

--
-- Dumping data for table `Contracts`
--


/*!40000 ALTER TABLE Contracts DISABLE KEYS */;
LOCK TABLES Contracts WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE Contracts ENABLE KEYS */;

--
-- Dumping data for table `Currencies`
--


/*!40000 ALTER TABLE Currencies DISABLE KEYS */;
LOCK TABLES Currencies WRITE;
INSERT INTO Currencies VALUES ('Australian Dollars','AUD','Australia','cents',1.7000),('Deutsche Marks','DEM','German','Pfenig',1.8000),('Pounds','GBP','England','Pence',0.8000),('Yen','JPY','Japan','Yen',150.0000),('N Z Dollars','NZD','New Zealand','Cents',2.0000),('US Dollars','USD','United States','Cents',1.0000);
UNLOCK TABLES;
/*!40000 ALTER TABLE Currencies ENABLE KEYS */;

--
-- Dumping data for table `CustAllocns`
--


/*!40000 ALTER TABLE CustAllocns DISABLE KEYS */;
LOCK TABLES CustAllocns WRITE;
INSERT INTO CustAllocns VALUES (3,"36.1060",'2003-01-26',4,1),(7,"53.2500",'0000-00-00',9,8),(8,"80.0000",'0000-00-00',20,16),(9,"50.0000",'0000-00-00',20,17),(10,"50.0000",'0000-00-00',20,18),(11,"425.9900",'0000-00-00',20,19),(12,"300.0000",'0000-00-00',22,21),(13,"187.5000",'0000-00-00',22,19),(14,"25.0000",'0000-00-00',22,17),(18,"14.1800",'0000-00-00',23,17),(19,"2.5000",'0000-00-00',23,18),(20,"5.0000",'0000-00-00',23,19),(21,"8.3200",'0000-00-00',23,21),(22,"100.0000",'0000-00-00',24,6),(23,"51.3820",'2003-06-23',29,27),(25,"267.0500",'0000-00-00',31,2),(26,"93.8940",'2004-01-14',48,1),(27,"857.8400",'2004-01-14',48,6),(28,"72.2020",'2004-01-14',48,11),(29,"18.4200",'2004-01-14',48,15),(30,"334.0180",'2004-01-14',48,27),(31,"171.2900",'2004-01-14',48,28),(32,"655.6500",'2004-01-14',48,32),(33,"45.6700",'2004-01-14',35,7),(34,"55.0700",'2004-01-14',3,32),(35,"20.4340",'2004-01-14',3,1),(36,"19.0300",'2004-01-14',30,25),(37,"19.0300",'2004-01-14',30,13),(38,"76.1300",'2004-01-14',39,25),(39,"145.7200",'2004-01-14',33,32),(40,"256.9300",'2004-01-14',33,34),(41,"21.0100",'2004-01-14',33,40),(42,"2003.2500",'2004-01-14',5,2),(43,"6.0000",'2004-01-14',42,40),(44,"2.4000",'2004-01-14',43,40);
UNLOCK TABLES;
/*!40000 ALTER TABLE CustAllocns ENABLE KEYS */;

--
-- Dumping data for table `CustBranch`
--


/*!40000 ALTER TABLE CustBranch DISABLE KEYS */;
LOCK TABLES CustBranch WRITE;
INSERT INTO CustBranch VALUES ('1234','12345678','One Two Three Four Five Six Seven Eight','','','','',1,'AR','25',0,'','','','','DEN',1,10,0,'','','','',''),('GRAN','GRANHR','Gramtham Horton PLC','2 Growler Terraces','West Hemington','BE22 FD2','England',4,'UK','25',0,'','','Ed Heath','','MUC',6,1,0,'','','','',''),('JOHN','JOHNSON','San Diego','198 Greater Lofty Heights','San Diego','California','USA',2,'AR','25',0,'2412 22356','2412 23549','Rod Champion','rod.champion@johnson.com','DEN',5,1,0,'','','','',''),('JOHNTEMPE','JOHNSON','Johnson Vehicles Tempe','654 Roberto Street','Tempe','Arizona','USA',2,'AR','2',0,'56565 444 44','222 44 55','Holly Drako','','DEN',5,1,0,'','','','',''),('WALM','WALMON','Wallace Monterey - Sydney','309 Centennial Highway','Ballmain, Sydney','New South Wales 4133','Australia',16,'AR','25',0,'+61 2 3546 8875','+61 2 3546 8874','Phyllis Archibald','phyllis@WallMonterey.com.au','MEL',1,1,0,'','','','',''),('WALMOADEL','WALMON','Wallace Monterey - Adelaide','2-6 Devon Street','Clovelly Park','Adelaide South Australia 3122','Australia',18,'AR','25',0,'+61 3544 2113','+61 3544 2103','Brian Morrisey','brian@WallMonterey.com.au','MEL',1,1,0,'','','','',''),('WATT','WATTSGRP','Watts Motor Group Head Office and Admin','81 Trimble Road','Gimlet Rock','Greater Hampton','Arizona',2,'AR','25',0,'233 5442 3255','233 5442 3254','Bill Gobster','billg@watts.motors.com','MEL',5,1,0,'','','','',''),('WATTLA','WATTSGRP','Watts Group Los Angeles','922 Acton Lane','Reefston','Los Angeles','California',2,'CA','25',0,'235 446 998','235 446 654','Graham Bigglesworth','gbiggles@watts.motors.com','DEN',5,1,0,'','','','',''),('WATTSSAND','WATTSGRP','Watts Group San Diego','42 Grafton Place','San Diego','California','',2,'CA','25',0,'355 224 566','355 224 544','Sharon Stonely','sstonely@watts.motors.com','DEN',5,1,0,'','','','','');
UNLOCK TABLES;
/*!40000 ALTER TABLE CustBranch ENABLE KEYS */;

--
-- Dumping data for table `DebtorTrans`
--


/*!40000 ALTER TABLE DebtorTrans DISABLE KEYS */;
LOCK TABLES DebtorTrans WRITE;
INSERT INTO DebtorTrans VALUES (1,1,10,'JOHNSON','JOHN','2003-01-21 00:00:00',37,1,'','WS',1,0.700000,120.344,30.09,0,0,9.87932,150.434,'','10',0),(2,2,10,'WALMON','WALM','2003-01-21 00:00:00',37,0,'','EX',2,1.000000,6443.5,0,53.5,0,-1132.27,2270.3,'Partial shipment','10',0),(3,2,12,'JOHNSON','','2003-01-26 00:00:00',37,1,'Direct Credit','',0,0.523000,-75,0,0,-0.5,-36.5043,-75.504,'','',0),(4,1,11,'JOHNSON','JOHN','2003-01-26 00:00:00',37,1,'Inv-1','WS',1,0.700000,-30.086,-6.02,0,0,0,-36.106,'Over ordered. Taken stock back.','',0),(5,3,12,'WALMON','','2003-01-31 00:00:00',37,1,'Cheques','',0,2.300000,-2000,0,0,-3.25,1132.27,-2003.25,'Draft deposit','',0),(6,3,10,'JOHNSON','JOHN','2003-02-24 00:00:00',38,1,'','WS',3,0.700000,798.2,159.64,0,0,-367.646,957.84,'','10',0),(7,4,10,'WATTSGRP','WATTLA','2003-03-06 00:00:00',39,0,'','WS',4,0.700000,171.4,71.73,2.5,0,-19.5729,45.67,'','10',0),(8,5,10,'12345678','1234','2003-03-28 00:00:00',39,0,'','WS',5,0.700000,53.725,5.37,0,0,-22.8214,53.25,'','10',0),(9,4,12,'12345678','','2003-03-27 00:00:00',39,1,'Cheques','',0,1.000000,-53.25,0,0,0,22.8214,-53.25,'','',0),(11,6,10,'JOHNSON','JOHN','2003-05-20 00:00:00',41,1,'','WS',1,0.700000,60.172,12.03,0,0,-30.9437,72.202,'','10',0),(12,7,10,'12345678','1234','2003-06-13 00:00:00',42,0,'','WS',7,0.700000,52.96,6.88,0,0,0,0,'','10',0),(13,8,10,'WATTSGRP','WATT','2003-06-13 00:00:00',42,1,'','WS',6,0.700000,15.86,3.17,0,0,0,19.03,'','10',0),(14,9,10,'12345678','1234','2003-06-13 00:00:00',42,0,'','WS',8,0.700000,328.17,32.82,0,0,0,0,'','10',0),(15,10,10,'JOHNSON','JOHNTEMPE','2003-06-16 00:00:00',42,1,'','WS',9,0.700000,15.35,3.07,0,0,-7.89429,18.42,'','10',0),(16,11,10,'GRANHR','GRAN','2003-06-23 00:00:00',42,0,'','EX',11,0.700000,69.35,12.14,0,0,-34.2857,80,'','10',0),(17,12,10,'GRANHR','GRAN','2003-06-23 00:00:00',42,0,'','EX',10,0.700000,75.9,13.28,0,0,-6.0771,89.18,'','10',0),(18,13,10,'GRANHR','GRAN','2003-06-23 00:00:00',42,0,'','EX',12,0.700000,58.94,10.31,0,0,-1.0714,52.5,'','10',0),(19,14,10,'GRANHR','GRAN','2003-06-23 00:00:00',42,0,'','EX',13,0.700000,582.54,101.94,0,0,-2.1429,618.49,'','10',0),(20,7,12,'GRANHR','','2003-06-22 00:00:00',42,1,'Cheques','',0,1.000000,-600,0,0,-5.99,259.71,-605.99,'','',0),(21,15,10,'GRANHR','GRAN','2003-06-23 00:00:00',42,0,'','EX',14,0.700000,136.62,234.3,0,0,-3.5657,305.82,'','10',0),(22,8,12,'GRANHR','','2003-06-23 00:00:00',42,1,'Cheques','',0,1.000000,-500,0,0,-12.5,219.643,-512.5,'part payt','',0),(23,12,12,'GRANHR','','2003-06-23 00:00:00',42,1,'Cheques','',0,1.000000,-30,0,0,0,12.8571,-30,'thirty','',0),(24,13,12,'JOHNSON','','2003-06-23 00:00:00',42,1,'Direct Credit','',0,0.620000,-90,0,0,-10,-18.4332,-100,'100 total incl 10 discnt @ .62','',0),(25,16,10,'WATTSGRP','WATTLA','2003-06-24 00:00:00',42,0,'','WS',4,0.700000,187.26,37.45,0,0,-32.6271,95.16,'USD @ 0,70','10',0),(27,17,10,'JOHNSON','JOHN','2003-06-24 00:00:00',42,1,'','WS',15,0.700000,142.74,242.66,0,0,-143.151,385.4,'','10',0),(28,18,10,'JOHNSON','JOHN','2003-06-24 00:00:00',42,1,'','WS',15,0.700000,142.74,28.55,0,0,-73.41,171.29,'','10',0),(29,2,11,'JOHNSON','JOHN','0000-00-00 00:00:00',59,1,'Inv-17','WS',15,0.700000,-42.822,-8.56,0,0,0,-51.382,'','',0),(30,3,11,'WATTSGRP','WATT','2003-06-29 00:00:00',42,1,'Inv-8','WS',6,0.700000,-31.72,-6.34,0,0,0,-38.06,'','',0),(31,4,11,'WALMON','WALM','2003-06-29 00:00:00',65,1,'','EX',0,1.000000,-267.05,0,0,0,0,-267.05,'','',0),(32,19,10,'JOHNSON','JOHN','2003-06-30 00:00:00',42,1,'','WS',15,0.700000,713.7,142.74,0,0,0,856.44,'invoice testings','10',0),(33,5,11,'JOHNSON','JOHN','2003-06-30 00:00:00',66,1,'','WS',0,0.700000,-353.05,-70.61,0,0,-119.117,-423.66,'Not required','',0),(34,20,10,'JOHNSON','JOHN','2003-07-07 00:00:00',43,1,'','WS',15,1.000000,214.11,42.82,0,0,110.113,256.93,'','8',0),(35,6,11,'WATTSGRP','WATTSSAND','2003-07-05 00:00:00',41,1,'','WS',0,1.000000,-38.064,-7.61,0,0,19.5729,-45.67,'','',0),(36,21,10,'12345678','1234','2003-07-08 00:00:00',43,0,'','WS',16,1.000000,173.842,17.38,0,0,0,0,'','10',0),(37,22,10,'12345678','1234','2003-07-08 00:00:00',43,0,'','WS',16,1.000000,111.02,11.1,0,0,0,0,'','10',0),(38,23,10,'12345678','1234','2003-07-10 00:00:00',43,0,'','WS',16,1.000000,47.58,4.76,0,0,0,0,'Some text to show on the invoice for a test to see where and how it prints lets hope all is well with the printing of this field and if there is some mal formation of the text that it is easily rectifiable. Surely no-one will want to write a thesis like this on the invoice but hey ....!','8',0),(39,7,11,'WATTSGRP','WATTSSAND','2003-08-10 00:00:00',46,1,'','WS',0,1.000000,-63.44,-12.69,0,0,32.6271,-76.13,'','',0),(40,24,10,'JOHNSON','JOHNTEMPE','2003-12-15 00:00:00',48,0,'','WS',20,1.000000,63.8,12.76,0,0,0,29.41,'','1',0),(42,8,11,'JOHNSON','JOHN','2003-12-14 00:00:00',48,1,'','WS',0,1.000000,-5,-1,0,0,0,-6,'Overcharged 125 credit 0.50 per kg','',0),(43,9,11,'JOHNSON','JOHNTEMPE','2003-12-14 00:00:00',48,1,'','WS',0,1.000000,-2,-0.4,0,0,0,-2.4,'','',0),(44,10,11,'JOHNSON','JOHNTEMPE','2003-12-16 00:00:00',48,0,'','WS',0,1.000000,-25,-5,0,0,0,0,'','',0),(45,11,11,'JOHNSON','JOHN','2003-12-16 00:00:00',48,0,'','WS',0,1.000000,-0.7,-0.14,0,0,0,0,'','',0),(46,25,10,'12345678','1234','2004-01-15 00:00:00',49,0,'','WS',7,1.000000,15.86,1.59,0,0,0,0,'','10',0),(47,26,10,'JOHNSON','JOHNTEMPE','2004-01-15 00:00:00',49,0,'','WS',25,1.000000,77.7,15.54,0,0,0,0,'','1',0),(48,18,12,'JOHNSON','','2004-01-14 00:00:00',49,1,'Cheques late but ok','',0,1.000000,-2203.31,0,0,0,944.277,-2203.31,'','',0);
UNLOCK TABLES;
/*!40000 ALTER TABLE DebtorTrans ENABLE KEYS */;

--
-- Dumping data for table `DebtorsMaster`
--


/*!40000 ALTER TABLE DebtorsMaster DISABLE KEYS */;
LOCK TABLES DebtorsMaster WRITE;
INSERT INTO DebtorsMaster VALUES ('12345678','One Two Three Four Five Six Seven Eight','','','','','USD','WS','2003-03-27 00:00:00',1,'20',0.1000,0.0200,53.2500,'2003-03-27 00:00:00',1000,0,'',0,0,'','email','','',''),('GRANHR','Gramtham Horton PLC','P O Box 7755','West Hemington','BE22 FD2','England','USD','EX','2003-03-24 00:00:00',1,'20',0.0000,0.0000,30.0000,'2003-06-23 00:00:00',1000,0,'',0,0,'','email','','',''),('JOHNSON','Johnson Vehicle Company Inc','1103 Garrison Road East','Detroit','Michigan','','USD','WS','1978-02-26 00:00:00',1,'30',0.0000,0.0000,2203.3100,'2004-01-14 00:00:00',10000,0,'',0,0,'','email','','',''),('WALMON','Wallace Monterey Pty Ltd','P O Box 564','Ballmain, Sydney','New South Wales 4133','Australia','AUD','EX','1989-05-06 00:00:00',1,'30',0.0000,0.0000,2000.0000,'2003-01-31 00:00:00',30000,0,'',1,1,'5412345000013','email','edi@walmon.com','',''),('WATTSGRP','Watts Motor Group Inc','P O Box 1230','Gimlet Rock','Greater Hampton','Arizona','USD','WS','2003-01-16 00:00:00',1,'7',0.0000,0.0000,0.0000,NULL,25000,0,'',0,0,'','email','','','');
UNLOCK TABLES;
/*!40000 ALTER TABLE DebtorsMaster ENABLE KEYS */;

--
-- Dumping data for table `DiscountMatrix`
--


/*!40000 ALTER TABLE DiscountMatrix DISABLE KEYS */;
LOCK TABLES DiscountMatrix WRITE;
INSERT INTO DiscountMatrix VALUES ('WS','SP',10,0.0150),('WS','SP',20,0.0200);
UNLOCK TABLES;
/*!40000 ALTER TABLE DiscountMatrix ENABLE KEYS */;

--
-- Dumping data for table `EDIItemMapping`
--


/*!40000 ALTER TABLE EDIItemMapping DISABLE KEYS */;
LOCK TABLES EDIItemMapping WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE EDIItemMapping ENABLE KEYS */;

--
-- Dumping data for table `EDIMessageFormat`
--


/*!40000 ALTER TABLE EDIMessageFormat DISABLE KEYS */;
LOCK TABLES EDIMessageFormat WRITE;
INSERT INTO EDIMessageFormat VALUES (1,'DEFAULT','INVOIC','Heading',10,'UNH+[EDITransNo]+INVOIC:D:96A:UN:EAN008\''),(2,'DEFAULT','INVOIC','Heading',20,'BGM+[InvOrCrd]+[TransNo]+[OrigOrDup]\''),(3,'DEFAULT','INVOIC','Heading',30,'DTM+137:[TranDate]:102\''),(4,'DEFAULT','INVOIC','Heading',60,'RFF+ON:[OrderNo]\''),(5,'DEFAULT','INVOIC','Heading',70,'NAD+BY+[CustBranchCode]::92\''),(6,'DEFAULT','INVOIC','Heading',80,'NAD+SU+[CompanyEDIReference]::91\''),(7,'DEFAULT','INVOIC','Heading',85,'NAD+IV+[CustEDIReference]::9\''),(8,'DEFAULT','INVOIC','Heading',90,'NAD+UD++[BranchName]+[BranchStreet]+[BranchCity]+[BranchState]\''),(9,'DEFAULT','INVOIC','Heading',100,'RFF+AMT:[TaxAuthorityRef]\''),(10,'DEFAULT','INVOIC','Heading',110,'PAT+1++5:3:D:30\''),(11,'DEFAULT','INVOIC','Heading',120,'DTM+13:[DatePaymentDue]:102\''),(12,'DEFAULT','INVOIC','Heading',130,'TAX+7+GST+++:::10\''),(13,'DEFAULT','INVOIC','Heading',150,'MOA+124:[TaxTotal]\''),(14,'DEFAULT','INVOIC','Detail',160,'LIN+[LineNumber]\''),(15,'DEFAULT','INVOIC','Detail',170,'PIA+5+[StockID]:SA+[CustStockID]:IN\''),(16,'DEFAULT','INVOIC','Detail',180,'IMD+F++:::[ItemDescription]\''),(17,'DEFAULT','INVOIC','Detail',200,'QTY+47:[QtyInvoiced]\''),(18,'DEFAULT','INVOIC','Detail',220,'MOA+128:[LineTotalExclTax]\''),(19,'DEFAULT','INVOIC','Detail',230,'PRI+AAA:[UnitPrice]\''),(20,'DEFAULT','INVOIC','Detail',240,'TAX+7+GST+++:::10\''),(21,'DEFAULT','INVOIC','Detail',250,'MOA+124:[LineTaxAmount]\''),(22,'DEFAULT','INVOIC','Summary',260,'UNS+S\''),(23,'DEFAULT','INVOIC','Summary',270,'CNT+2:[NoLines]\''),(24,'DEFAULT','INVOIC','Summary',280,'MOA+128:[TotalAmountExclTax]\''),(25,'DEFAULT','INVOIC','Summary',290,'TAX+7+GST+++:::10\''),(26,'DEFAULT','INVOIC','Summary',300,'MOA+128:[TaxTotal]\''),(27,'DEFAULT','INVOIC','Summary',310,'UNT+[NoSegments]+[EDITransNo]\''),(28,'WALMON','INVOIC','Heading',10,'UNH+[EDITransNo]+INVOIC:D:96A:UN:EAN008\''),(29,'WALMON','INVOIC','Heading',20,'BGM+[InvOrCrd]+[TransNo]+[OrigOrDup]\''),(30,'WALMON','INVOIC','Heading',30,'DTM+137:[TranDate]:102\''),(31,'WALMON','INVOIC','Heading',60,'RFF+ON:[OrderNo]\''),(32,'WALMON','INVOIC','Heading',70,'NAD+BY+[CustBranchCode]::92\''),(33,'WALMON','INVOIC','Heading',80,'NAD+SU+[CompanyEDIReference]::91\''),(34,'WALMON','INVOIC','Heading',85,'NAD+IV+[CustEDIReference]::9\''),(35,'WALMON','INVOIC','Heading',90,'NAD+UD++[BranchName]+[BranchStreet]+[BranchCity]+[BranchState]\''),(36,'WALMON','INVOIC','Heading',100,'RFF+AMT:[TaxAuthorityRef]\''),(37,'WALMON','INVOIC','Heading',110,'PAT+1++5:3:D:30\''),(38,'WALMON','INVOIC','Heading',120,'DTM+13:[DatePaymentDue]:102\''),(39,'WALMON','INVOIC','Heading',130,'TAX+7+GST+++:::10\''),(40,'WALMON','INVOIC','Heading',150,'MOA+124:[TaxTotal]\''),(41,'WALMON','INVOIC','Detail',160,'LIN+[LineNumber]\''),(42,'WALMON','INVOIC','Detail',170,'PIA+5+[StockID]:SA+[CustStockID]:IN\''),(43,'WALMON','INVOIC','Detail',180,'IMD+F++:::[ItemDescription]\''),(44,'WALMON','INVOIC','Detail',200,'QTY+47:[QtyInvoiced]\''),(45,'WALMON','INVOIC','Detail',220,'MOA+128:[LineTotalExclTax]\''),(46,'WALMON','INVOIC','Detail',230,'PRI+AAA:[UnitPrice]\''),(47,'WALMON','INVOIC','Detail',240,'TAX+7+GST+++:::10\''),(48,'WALMON','INVOIC','Detail',250,'MOA+124:[LineTaxAmount]\''),(49,'WALMON','INVOIC','Summary',260,'UNS+S\''),(50,'WALMON','INVOIC','Summary',270,'CNT+2:[NoLines]\''),(51,'WALMON','INVOIC','Summary',280,'MOA+128:[TotalAmountExclTax]\''),(52,'WALMON','INVOIC','Summary',290,'TAX+7+GST+++:::10\''),(53,'WALMON','INVOIC','Summary',300,'MOA+128:[TaxTotal]\''),(54,'WALMON','INVOIC','Summary',310,'UNT+[NoSegments]+[EDITransNo]\'');
UNLOCK TABLES;
/*!40000 ALTER TABLE EDIMessageFormat ENABLE KEYS */;

--
-- Dumping data for table `EDI_ORDERS_Seg_Groups`
--


/*!40000 ALTER TABLE EDI_ORDERS_Seg_Groups DISABLE KEYS */;
LOCK TABLES EDI_ORDERS_Seg_Groups WRITE;
INSERT INTO EDI_ORDERS_Seg_Groups VALUES (0,1,0),(1,9999,0),(2,99,0),(3,99,2),(5,5,2),(6,5,0),(7,5,0),(8,10,0),(9,9999,8),(10,10,0),(11,10,10),(12,5,0),(13,99,0),(14,5,13),(15,10,0),(19,99,0),(20,1,19),(21,1,19),(22,2,19),(23,1,19),(24,5,19),(28,200000,0),(32,25,28),(33,9999,28),(34,99,28),(36,5,34),(37,9999,28),(38,10,28),(39,999,28),(42,5,39),(43,99,28),(44,1,43),(45,1,43),(46,2,43),(47,1,43),(48,5,43),(49,10,28),(50,1,0);
UNLOCK TABLES;
/*!40000 ALTER TABLE EDI_ORDERS_Seg_Groups ENABLE KEYS */;

--
-- Dumping data for table `EDI_ORDERS_Segs`
--


/*!40000 ALTER TABLE EDI_ORDERS_Segs DISABLE KEYS */;
LOCK TABLES EDI_ORDERS_Segs WRITE;
INSERT INTO EDI_ORDERS_Segs VALUES (1,'UNB',0,1),(2,'UNH',0,1),(3,'BGM',0,1),(4,'DTM',0,35),(5,'PAI',0,1),(6,'ALI',0,5),(7,'FTX',0,99),(8,'RFF',1,1),(9,'DTM',1,5),(10,'NAD',2,1),(11,'LOC',2,99),(12,'FII',2,5),(13,'RFF',3,1),(14,'CTA',5,1),(15,'COM',5,5),(16,'TAX',6,1),(17,'MOA',6,1),(18,'CUX',7,1),(19,'DTM',7,5),(20,'PAT',8,1),(21,'DTM',8,5),(22,'PCD',8,1),(23,'MOA',9,1),(24,'TDT',10,1),(25,'LOC',11,1),(26,'DTM',11,5),(27,'TOD',12,1),(28,'LOC',12,2),(29,'PAC',13,1),(30,'PCI',14,1),(31,'RFF',14,1),(32,'DTM',14,5),(33,'GIN',14,10),(34,'EQD',15,1),(35,'ALC',19,1),(36,'ALI',19,5),(37,'DTM',19,5),(38,'QTY',20,1),(39,'RNG',20,1),(40,'PCD',21,1),(41,'RNG',21,1),(42,'MOA',22,1),(43,'RNG',22,1),(44,'RTE',23,1),(45,'RNG',23,1),(46,'TAX',24,1),(47,'MOA',24,1),(48,'LIN',28,1),(49,'PIA',28,25),(50,'IMD',28,99),(51,'MEA',28,99),(52,'QTY',28,99),(53,'ALI',28,5),(54,'DTM',28,35),(55,'MOA',28,10),(56,'GIN',28,127),(57,'QVR',28,1),(58,'FTX',28,99),(59,'PRI',32,1),(60,'CUX',32,1),(61,'DTM',32,5),(62,'RFF',33,1),(63,'DTM',33,5),(64,'PAC',34,1),(65,'QTY',34,5),(66,'PCI',36,1),(67,'RFF',36,1),(68,'DTM',36,5),(69,'GIN',36,10),(70,'LOC',37,1),(71,'QTY',37,1),(72,'DTM',37,5),(73,'TAX',38,1),(74,'MOA',38,1),(75,'NAD',39,1),(76,'CTA',42,1),(77,'COM',42,5),(78,'ALC',43,1),(79,'ALI',43,5),(80,'DTM',43,5),(81,'QTY',44,1),(82,'RNG',44,1),(83,'PCD',45,1),(84,'RNG',45,1),(85,'MOA',46,1),(86,'RNG',46,1),(87,'RTE',47,1),(88,'RNG',47,1),(89,'TAX',48,1),(90,'MOA',48,1),(91,'TDT',49,1),(92,'UNS',50,1),(93,'MOA',50,1),(94,'CNT',50,1),(95,'UNT',50,1);
UNLOCK TABLES;
/*!40000 ALTER TABLE EDI_ORDERS_Segs ENABLE KEYS */;

--
-- Dumping data for table `FreightCosts`
--


/*!40000 ALTER TABLE FreightCosts DISABLE KEYS */;
LOCK TABLES FreightCosts WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE FreightCosts ENABLE KEYS */;

--
-- Dumping data for table `GLTrans`
--


/*!40000 ALTER TABLE GLTrans DISABLE KEYS */;
LOCK TABLES GLTrans WRITE;
INSERT INTO GLTrans VALUES (1,10,1,0,'2003-01-21',37,1,'JOHNSON - HON90021 x 8 @ 15.3500',-122.8,1,'\'\''),(2,10,1,0,'2003-01-21',37,1,'JOHNSON - HON90021 @ 2%',2.456,1,'\'\''),(3,10,1,0,'2003-01-21',37,710000,'JOHNSON',150.434,1,'\'\''),(4,10,1,0,'2003-01-21',37,890000,'JOHNSON',-30.09,1,'\'\''),(5,25,1,0,'2003-01-20',37,720000,'PO: 1 BHPSTEEL - 12.5mm round steel x 2430 @ 0.00',0,1,'\'\''),(6,25,1,0,'2003-01-20',37,723000,'PO: 1 BHPSTEEL - 12.5mm round steel x 2430 @ 0.00',0,1,'\'\''),(7,25,1,0,'2003-01-20',37,24000,'PO: 1 BHPSTEEL - freight on shipment as agreed x 60 @ 52.17',3130.43,1,'\'\''),(8,25,1,0,'2003-01-20',37,723000,'PO: 1 BHPSTEEL - freight on shipment as agreed x 60 @ 52.17',-3130.43,1,'\'\''),(9,10,2,0,'2003-01-21',37,1,'WALMON - HON90022 x 263 @ 24.5000',-6443.5,1,'\'\''),(10,10,2,0,'2003-01-21',37,710000,'WALMON',6497,1,'\'\''),(11,10,2,0,'2003-01-21',37,450000,'WALMON',-53.5,1,'\'\''),(12,20,1,0,'2003-01-19',37,20000,'BIGBISC packaging cardboard',124.767,1,'\'\''),(13,20,1,0,'2003-01-19',37,24000,'BIGBISC Freight on packaging',11.6279,1,'\'\''),(14,20,1,0,'2003-01-19',37,890000,'BIGBISC - Inv 655745 GBP5.865 @ a rate of 0.4300',13.6395,1,'\'\''),(15,20,1,0,'2003-01-19',37,800000,'BIGBISC - Inv 655745 GBP64.52 @ a rate of 0.4300',-150.035,1,'\'\''),(34,20,2,0,'2003-01-19',37,460000,'BHPSTEEL',10.8,1,'\'\''),(35,20,2,0,'2003-01-19',37,723000,'BHPSTEEL - GRN 1 - 125 x 2430.0000 @ DEM0.6500 @ a rate of 1.25',1263.6,1,'\'\''),(36,20,2,0,'2003-01-19',37,723000,'BHPSTEEL - GRN 2 -  x 1 @  std cost of52.1739',52.1739,1,'\'\''),(37,20,2,0,'2003-01-19',37,24000,'BHPSTEEL - GRN 2 - freight on shipment as agreed x 1 x  price var 43.83',43.8261,1,'\'\''),(38,20,2,0,'2003-01-19',37,890000,'BHPSTEEL - Inv 654423 DEM171.3 @ a rate of 1.25',137.04,1,'\'\''),(39,20,2,0,'2003-01-19',37,800000,'BHPSTEEL - Inv 654423 DEM1,884.30 @ a rate of 1.25',-1507.44,1,'\'\''),(40,25,2,0,'2003-01-23',37,720000,'PO: 2 BIGBISC - 13mm round spring steel X4K grade x 5853 @ 0.00',0,1,'\'\''),(41,25,2,0,'2003-01-23',37,723000,'PO: 2 BIGBISC - 13mm round spring steel X4K grade x 5853 @ 0.00',0,1,'\'\''),(42,25,2,0,'2003-01-23',37,720000,'PO: 2 BIGBISC - 14mm round steel X4k grade x 2872 @ 0.00',0,1,'\'\''),(43,25,2,0,'2003-01-23',37,723000,'PO: 2 BIGBISC - 14mm round steel X4k grade x 2872 @ 0.00',0,1,'\'\''),(44,20,3,0,'2003-01-23',37,24000,'BIGBISC test gl narrative',348.837,1,'\'\''),(45,20,3,0,'2003-01-23',37,723000,'BIGBISC - GRN 4 - 140 x 2872.0000 @ GBP0.34 @ a rate of 0.4300',2270.88,1,'\'\''),(46,20,3,0,'2003-01-23',37,890000,'BIGBISC - Inv 6455 GBP112.648 @ a rate of 0.4300',261.972,1,'\'\''),(47,20,3,0,'2003-01-23',37,800000,'BIGBISC - Inv 6455 GBP1,239.13 @ a rate of 0.4300',-2881.69,1,'\'\''),(48,21,1,0,'2003-01-23',37,267000,'BIGBISC credit telephone',-24.4186,1,'\'\''),(49,21,1,0,'2003-01-23',37,723000,'BIGBISC - GRN 4 - 140 x 2872 @ GBP.012 @ a rate of 0.4300',-80.1488,1,'\'\''),(50,21,1,0,'2003-01-23',37,890000,'BIGBISC - Credit Note Z332 GBP4.4964 @ a rate of 0.4300',-10.4567,1,'\'\''),(51,21,1,0,'2003-01-23',37,800000,'BIGBISC - Credit Note Z332 GBP49.46 @ a rate of 0.4300',115.024,1,'\'\''),(52,21,2,0,'2003-01-23',37,21000,'BIGBISC returned samples no longer required',-54.6512,1,'\'\''),(53,21,2,0,'2003-01-23',37,255000,'BIGBISC part credit on original insurance chg',-7.5581,1,'\'\''),(54,21,2,0,'2003-01-23',37,890000,'BIGBISC - Credit Note 5442 GBP2.675 @ a rate of 0.4300',-6.2209,1,'\'\''),(55,21,2,0,'2003-01-23',37,800000,'BIGBISC - Credit Note 5442 GBP29.43 @ a rate of 0.4300',68.4302,1,'\'\''),(56,12,2,0,'2003-01-26',37,700000,'',143.403,1,'\'\''),(57,12,2,0,'2003-01-26',37,710000,'',-144.359,1,'\'\''),(58,12,2,0,'2003-01-26',37,455000,'',0.956,1,'\'\''),(59,12,2,0,'2003-01-26',37,23700,'',-36.5023,1,'\'\''),(60,12,2,0,'2003-01-26',37,710000,'',36.5023,1,'\'\''),(61,12,2,0,'2003-01-26',37,23700,'',26.623,1,'\'\''),(62,12,2,0,'2003-01-26',37,710000,'',-26.623,1,'\'\''),(63,11,1,0,'2003-01-26',37,1,'JOHNSON - HON90021 x 2 @ 15.3500',30.7,1,'\'\''),(64,11,1,0,'2003-01-26',37,1,'JOHNSON - HON90021 @ 2%',-0.614,1,'\'\''),(65,11,1,0,'2003-01-26',37,710000,'JOHNSON',-36.106,1,'\'\''),(66,11,1,0,'2003-01-26',37,890000,'JOHNSON',6.02,1,'\'\''),(67,35,1,0,'2003-01-26',37,20000,'HON90021 cost was 0 changed to 9.69 x Quantity on hand of 9400.0',-91086,1,'\'\''),(68,35,1,0,'2003-01-26',37,722000,'HON90021 cost was 0 changed to 9.69 x Quantity on hand of 9400.0',91086,1,'\'\''),(69,1,1,0,'2003-01-29',37,301,'',12,1,'\'\''),(70,1,1,0,'2003-01-29',37,107,'',12,1,'\'\''),(71,1,1,0,'2003-01-29',37,700100,'',-24,1,'\'\''),(72,12,3,0,'2003-01-31',37,20000,'scrap sold',-7.0957,1,'\'\''),(73,12,3,0,'2003-01-31',37,700000,'Australian Dollar receipt test',876.661,1,'\'\''),(74,12,3,0,'2003-01-31',37,710000,'Australian Dollar receipt test',-870.978,1,'\'\''),(75,12,3,0,'2003-01-31',37,455000,'Australian Dollar receipt test',1.413,1,'\'\''),(76,22,1,0,'2003-02-01',38,23700,'',-10.084,1,'\'\''),(77,22,1,0,'2003-02-01',38,800000,'',10.084,1,'\'\''),(78,20,4,0,'2003-01-31',37,24000,'BIGBISC Graham\'s charges',1058.63,1,'\'\''),(79,20,4,0,'2003-01-31',37,890000,'BIGBISC - Inv 66544 GBP45.521 @ a rate of 0.4300',105.863,1,'\'\''),(80,20,4,0,'2003-01-31',37,800000,'BIGBISC - Inv 66544 GBP500.73 @ a rate of 0.4300',-1164.49,1,'\'\''),(81,22,2,0,'2003-02-01',38,800000,'Payment test to Biga Engineering PLC in GBP @ .49',714.286,1,'\'\''),(82,22,2,0,'2003-02-01',38,700000,'Payment test to Biga Engineering PLC in GBP @ .49',-714.286,1,'\'\''),(83,22,2,0,'2003-02-01',38,23700,'',-28.4765,1,'\'\''),(84,22,2,0,'2003-02-01',38,800000,'',28.4765,1,'\'\''),(85,22,2,0,'2003-02-01',38,23700,'Exch diff',28.4765,1,'\'\''),(86,22,2,0,'2003-02-01',38,800000,'Exchg Diff',-28.4765,1,'\'\''),(87,1,2,0,'2003-02-19',38,21000,'',23.5,1,'\'\''),(88,1,2,0,'2003-02-19',38,210000,'',6.25,1,'\'\''),(89,1,2,0,'2003-02-19',38,700000,'',-29.75,1,'\'\''),(90,1,3,0,'2003-02-19',38,259000,'',63.5,1,'\'\''),(91,1,3,0,'2003-02-19',38,700000,'Testing payment',-63.5,1,'\'\''),(92,10,3,0,'2003-02-24',38,1,'JOHNSON - HON90021 x 52 @ 9.6900',503.88,1,'\'\''),(93,10,3,0,'2003-02-24',38,722000,'JOHNSON - HON90021 x 52 @ 9.6900',-503.88,1,'\'\''),(94,10,3,0,'2003-02-24',38,1,'JOHNSON - HON90021 x 52 @ 15.3500',-798.2,1,'\'\''),(95,10,3,0,'2003-02-24',38,710000,'JOHNSON',957.84,1,'\'\''),(96,10,3,0,'2003-02-24',38,890000,'JOHNSON',-159.64,1,'\'\''),(97,10,4,0,'2003-03-06',39,1,'WATTSGRP - HON90021 x 6 @ 9.6900',58.14,1,'\'\''),(98,10,4,0,'2003-03-06',39,722000,'WATTSGRP - HON90021 x 6 @ 9.6900',-58.14,1,'\'\''),(99,10,4,0,'2003-03-06',39,1,'WATTSGRP - HON90021 x 6 @ 15.3500',-92.1,1,'\'\''),(100,10,4,0,'2003-03-06',39,1,'WATTSGRP - HON90022 x 5 @ 15.8600',-79.3,1,'\'\''),(101,10,4,0,'2003-03-06',39,710000,'WATTSGRP',245.63,1,'\'\''),(102,10,4,0,'2003-03-06',39,450000,'WATTSGRP',-2.5,1,'\'\''),(103,10,4,0,'2003-03-06',39,890000,'WATTSGRP',-71.73,1,'\'\''),(104,10,5,0,'2003-03-28',39,1,'12345678 - HON90021 x 3.5 @ 9.6900',33.915,1,'\'\''),(105,10,5,0,'2003-03-28',39,722000,'12345678 - HON90021 x 3.5 @ 9.6900',-33.915,1,'\'\''),(106,10,5,0,'2003-03-28',39,1,'12345678 - HON90021 x 3.5 @ 15.3500',-53.725,1,'\'\''),(107,10,5,0,'2003-03-28',39,710000,'12345678',59.095,1,'\'\''),(108,10,5,0,'2003-03-28',39,810000,'12345678',-5.37,1,'\'\''),(109,12,4,0,'2003-03-27',39,700000,'',53.25,1,'\'\''),(110,12,4,0,'2003-03-27',39,710000,'',-53.25,1,'\'\''),(111,1,4,0,'2003-05-02',41,700000,'',22,1,'\'\''),(112,1,4,0,'2003-05-02',41,700000,'',-22,1,'\'\''),(113,1,5,0,'2003-04-01',40,700100,'',390.514,1,'\'\''),(114,1,5,0,'2003-04-01',40,700000,'',-390.514,1,'\'\''),(115,1,6,0,'2003-10-01',46,700100,'',259.048,1,'\'\''),(116,1,6,0,'2003-10-01',46,700000,'testing',-259.048,1,'\'\''),(117,1,7,0,'2003-05-02',41,700100,'',0.8077,1,'\'\''),(118,1,7,0,'2003-05-02',41,20000,'',10.0385,1,'\'\''),(119,1,7,0,'2003-05-02',41,700000,'Test header narrative field',-10.8462,1,'\'\''),(120,1,8,0,'2003-05-02',41,700100,'',2221,1,'\'\''),(121,1,8,0,'2003-05-02',41,107,'',12,1,'\'\''),(122,1,8,0,'2003-05-02',41,700000,'sdsd',-2233,1,'\'\''),(123,1,9,0,'2003-05-02',41,700100,'',27.6667,1,'\'\''),(124,1,9,0,'2003-05-02',41,107,'',1,1,'\'\''),(125,1,9,0,'2003-05-02',41,700000,'fdf',-28.6667,1,'\'\''),(126,1,10,0,'2003-05-02',41,107,'',122,1,'\'\''),(127,1,10,0,'2003-05-02',41,700100,'',5522,1,'\'\''),(128,1,10,0,'2003-05-02',41,700000,'sdd',-5644,1,'\'\''),(129,1,11,0,'2003-05-02',41,107,'',213,1,'\'\''),(130,1,11,0,'2003-05-02',41,700100,'',21212,1,'\'\''),(131,1,11,0,'2003-05-02',41,700000,'dfsfsdf',-21425,1,'\'\''),(132,1,12,0,'2003-05-02',41,107,'',23,1,'\'\''),(133,1,12,0,'2003-05-02',41,700100,'',34,1,'\'\''),(134,1,12,0,'2003-05-02',41,700000,'dsd',-57,1,'\'\''),(135,1,13,0,'2003-05-02',41,107,'',3.5102,1,'\'\''),(136,1,13,0,'2003-05-02',41,700100,'',21.551,1,'\'\''),(137,1,13,0,'2003-05-02',41,700000,'',-25.0612,1,'\'\''),(138,12,5,0,'2003-05-02',41,700100,'test',-12221,1,'\'\''),(139,12,5,0,'2003-05-02',41,700000,'',12221,1,'\'\''),(140,0,3,0,'2003-04-30',40,21000,'',98.25,1,'\'\''),(141,0,3,0,'2003-04-30',40,11000,'',-130.9,1,'\'\''),(142,0,3,0,'2003-04-30',40,107,'',32.65,1,'\'\''),(143,0,4,0,'2003-04-30',40,107,'',20,1,'\'\''),(144,0,4,0,'2003-04-30',41,107,'Reversal -',-20,1,'\'\''),(145,0,4,0,'2003-04-30',40,11000,'',-20,1,'\'\''),(146,0,4,0,'2003-04-30',41,11000,'Reversal -',20,1,'\'\''),(147,12,6,0,'2003-05-12',41,270000,'private fuel reimbursed',-10.23,1,'\'\''),(148,12,6,0,'2003-05-12',41,700000,'',10.23,1,'\'\''),(149,10,6,0,'2003-05-20',41,1,'JOHNSON - HON90021 x 4 @ 9.6900',38.76,1,'\'\''),(150,10,6,0,'2003-05-20',41,722000,'JOHNSON - HON90021 x 4 @ 9.6900',-38.76,1,'\'\''),(151,10,6,0,'2003-05-20',41,1,'JOHNSON - HON90021 x 4 @ 15.3500',-61.4,1,'\'\''),(152,10,6,0,'2003-05-20',41,1,'JOHNSON - HON90021 @ 2%',1.228,1,'\'\''),(153,10,6,0,'2003-05-20',41,710000,'JOHNSON',72.202,1,'\'\''),(154,10,6,0,'2003-05-20',41,890000,'JOHNSON',-12.03,1,'\'\''),(155,12,2,0,'2003-01-26',37,23700,'',0,1,'\'\''),(156,12,2,0,'2003-01-26',37,710000,'',0,1,'\'\''),(157,12,2,0,'2003-01-26',37,23700,'',0,1,'\'\''),(158,12,2,0,'2003-01-26',37,710000,'',0,1,'\'\''),(159,12,2,0,'2003-01-26',37,23700,'',0,1,'\'\''),(160,12,2,0,'2003-01-26',37,710000,'',0,1,'\'\''),(161,12,2,0,'2003-01-26',37,23700,'',0,1,'\'\''),(162,12,2,0,'2003-01-26',37,710000,'',0,1,'\'\''),(163,12,3,0,'2003-01-31',37,23700,'',4.5217,1,'\'\''),(164,12,3,0,'2003-01-31',37,710000,'',-4.5217,1,'\'\''),(165,12,3,0,'2003-01-31',37,23700,'',-1.1304,1,'\'\''),(166,12,3,0,'2003-01-31',37,710000,'',1.1304,1,'\'\''),(167,12,3,0,'2003-01-31',37,23700,'',-3.3913,1,'\'\''),(168,12,3,0,'2003-01-31',37,710000,'',3.3913,1,'\'\''),(169,12,2,0,'2003-01-26',37,23700,'',-26.623,1,'\'\''),(170,12,2,0,'2003-01-26',37,710000,'',26.623,1,'\'\''),(171,12,4,0,'2003-03-27',39,23700,'',22.8214,1,'\'\''),(172,12,4,0,'2003-03-27',39,710000,'',-22.8214,1,'\'\''),(173,12,2,0,'2003-01-26',37,23700,'',26.623,1,'\'\''),(174,12,2,0,'2003-01-26',37,710000,'',-26.623,1,'\'\''),(175,12,3,0,'2003-01-31',37,23700,'',114.88,1,'\'\''),(176,12,3,0,'2003-01-31',37,710000,'',-114.88,1,'\'\''),(177,12,3,0,'2003-01-31',37,23700,'',-114.88,1,'\'\''),(178,12,3,0,'2003-01-31',37,710000,'',114.88,1,'\'\''),(179,0,5,0,'0000-00-00',68,107,'',50,1,'\'\''),(180,0,5,0,'0000-00-00',68,100,'',-50,1,'\'\''),(181,0,6,0,'2003-06-10',42,12000,'',2.5,1,'\'\''),(182,0,6,0,'2003-06-10',42,19000,'',-2.5,1,'\'\''),(183,0,7,0,'2003-01-03',37,301,'Testing rteh details narrative entry',65.25,1,'\'\''),(184,0,7,0,'2003-01-03',37,12000,'If only this was',16.65,1,'\'\''),(185,0,7,0,'2003-01-03',37,261000,'#rd time lucky',-819,1,'\'\''),(186,0,7,0,'2003-01-03',37,258000,'Fianlly a balancing entry',737.1,1,'\'\''),(187,10,7,0,'2003-06-13',42,1,'12345678 - 123 x 2 @ 0.3000',0.6,1,'\'\''),(188,10,7,0,'2003-06-13',42,720000,'12345678 - 123 x 2 @ 0.3000',-0.6,1,'\'\''),(189,10,7,0,'2003-06-13',42,1,'12345678 - 123 x 2 @ 3.2000',-6.4,1,'\'\''),(190,10,7,0,'2003-06-13',42,1,'12345678 - HON90021 x 2 @ 9.6900',19.38,1,'\'\''),(191,10,7,0,'2003-06-13',42,722000,'12345678 - HON90021 x 2 @ 9.6900',-19.38,1,'\'\''),(192,10,7,0,'2003-06-13',42,1,'12345678 - HON90021 x 2 @ 15.3500',-30.7,1,'\'\''),(193,10,7,0,'2003-06-13',42,1,'12345678 - HON90022 x 1 @ 15.8600',-15.86,1,'\'\''),(194,10,7,0,'2003-06-13',42,710000,'12345678',59.84,1,'\'\''),(195,10,7,0,'2003-06-13',42,810000,'12345678',-6.88,1,'\'\''),(196,10,8,0,'2003-06-13',42,1,'WATTSGRP - HON90022 x 1 @ 15.8600',-15.86,1,'\'\''),(197,10,8,0,'2003-06-13',42,710000,'WATTSGRP',19.03,1,'\'\''),(198,10,8,0,'2003-06-13',42,890000,'WATTSGRP',-3.17,1,'\'\''),(199,35,2,0,'2003-06-12',48,20000,'HONShock cost was 0 changed to 8.5 x Quantity on hand of 0.0',0,1,'\'\''),(200,35,2,0,'2003-06-12',48,722000,'HONShock cost was 0 changed to 8.5 x Quantity on hand of 0.0',0,1,'\'\''),(201,10,9,0,'2003-06-13',42,1,'12345678 - 123 x 2 @ 0.3000',0.6,1,'\'\''),(202,10,9,0,'2003-06-13',42,720000,'12345678 - 123 x 2 @ 0.3000',-0.6,1,'\'\''),(203,10,9,0,'2003-06-13',42,1,'12345678 - 123 x 2 @ 3.2000',-6.4,1,'\'\''),(204,10,9,0,'2003-06-13',42,1,'12345678 - HON90021 x 2 @ 9.6900',19.38,1,'\'\''),(205,10,9,0,'2003-06-13',42,722000,'12345678 - HON90021 x 2 @ 9.6900',-19.38,1,'\'\''),(206,10,9,0,'2003-06-13',42,1,'12345678 - HON90021 x 2 @ 15.3500',-30.7,1,'\'\''),(207,10,9,0,'2003-06-13',42,1,'12345678 - HON90022 x 2 @ 15.8600',-31.72,1,'\'\''),(208,10,9,0,'2003-06-13',42,1,'12345678 - HON92FrSus x 5 @ 18.19',90.95,1,'\'\''),(209,10,9,0,'2003-06-13',42,722000,'12345678 - HON92FrSus x 5 @ 18.19',-90.95,1,'\'\''),(210,10,9,0,'2003-06-13',42,1,'12345678 - HON92FrSus x 5 @ 53.2000',-266,1,'\'\''),(211,10,9,0,'2003-06-13',42,1,'12345678 - HON92FrSus @ 2.5%',6.65,1,'\'\''),(212,10,9,0,'2003-06-13',42,710000,'12345678',360.99,1,'\'\''),(213,10,9,0,'2003-06-13',42,810000,'12345678',-32.82,1,'\'\''),(214,10,10,0,'2003-06-16',42,1,'JOHNSON - HON90021 x 1 @ 9.6900',9.69,1,'\'\''),(215,10,10,0,'2003-06-16',42,722000,'JOHNSON - HON90021 x 1 @ 9.6900',-9.69,1,'\'\''),(216,10,10,0,'2003-06-16',42,1,'JOHNSON - HON90021 x 1 @ 15.3500',-15.35,1,'\'\''),(217,10,10,0,'2003-06-16',42,710000,'JOHNSON',18.42,1,'\'\''),(218,10,10,0,'2003-06-16',42,890000,'JOHNSON',-3.07,1,'\'\''),(219,0,8,0,'2003-05-16',41,12000,'Project work',956.215,1,'\'\''),(220,0,8,0,'2003-05-16',41,19000,'Project work',65.255,1,'\'\''),(221,0,8,0,'2003-05-16',41,21000,'Project work',1234.55,1,'\'\''),(222,0,8,0,'2003-05-16',41,270000,'Project work',-2256.02,1,'\'\''),(223,0,9,0,'2003-06-16',42,107,'',984,1,'\'\''),(224,0,9,0,'2003-06-16',42,721000,'',-984,1,'\'\''),(225,25,3,0,'2003-06-19',42,720000,'PO: 6 BIGBISC - 12.5mm round steel x 30.5 @ 0.00',0,1,'\'\''),(226,25,3,0,'2003-06-19',42,723000,'PO: 6 BIGBISC - 12.5mm round steel x 30.5 @ 0.00',0,1,'\'\''),(227,20,5,0,'2003-06-19',42,723000,'BIGBISC - GRN 5 - 125 x 30.5000 @ GBP1.15 @ a rate of 0.4300',81.5698,1,'\'\''),(228,20,5,0,'2003-06-19',42,890000,'BIGBISC - Inv 52422 GBP3.5075 @ a rate of 0.4300',8.157,1,'\'\''),(229,20,5,0,'2003-06-19',42,800000,'BIGBISC - Inv 52422 GBP38.58 @ a rate of 0.4300',-89.7267,1,'\'\''),(230,25,4,0,'2003-06-20',42,720000,'PO: 6 BIGBISC - 12.5mm round steel x 65.0 @ 0.62',40.3,1,'\'\''),(231,25,4,0,'2003-06-20',42,723000,'PO: 6 BIGBISC - 12.5mm round steel x 65.0 @ 0.62',-40.3,1,'\'\''),(232,21,3,0,'2003-06-19',42,20000,'BIGBISC',-5.814,1,''),(233,21,3,0,'2003-06-19',42,23400,'BIGBISC - GRN Credit Note 6 - 125 x 3.0000 x  2.77',-8.3023,1,'\'\''),(234,21,3,0,'2003-06-19',42,890000,'BIGBISC - Credit Note 45442 GBP0.607 @ a rate of 0.4300',-1.4116,1,'\'\''),(235,21,3,0,'2003-06-19',42,800000,'BIGBISC - Credit Note 45442 GBP6.68 @ a rate of 0.4300',15.5279,1,'\'\''),(236,20,6,0,'2003-06-19',42,723000,'BIGBISC - GRN 6 - 125 x 60.0000 @ GBP1.175 @ a rate of 0.422',167.062,1,'\'\''),(237,20,6,0,'2003-06-19',42,890000,'BIGBISC - Inv 87754 GBP4.15 @ a rate of 0.422',9.8341,1,'\'\''),(238,20,6,0,'2003-06-19',42,800000,'BIGBISC - Inv 87754 GBP74.65 @ a rate of 0.422',-176.896,1,'\'\''),(239,20,7,0,'2003-06-20',42,257000,'BIGBISC',215.116,1,''),(240,20,7,0,'2003-06-20',42,723000,'BIGBISC - GRN 4 - 140 x 52.0000 @ GBP0.3500 @ a rate of 0.4300',42.3256,1,'\'\''),(241,20,7,0,'2003-06-20',42,890000,'BIGBISC - Inv 32112 GBP11.07 @ a rate of 0.4300',25.7442,1,'\'\''),(242,20,7,0,'2003-06-20',42,800000,'BIGBISC - Inv 32112 GBP121.77 @ a rate of 0.4300',-283.186,1,'\'\''),(243,25,5,0,'2003-06-21',42,720000,'PO: 7 GREGCA - 12.3mm Round Steel coil x 1.6 @ 0.00',0,1,'\'\''),(244,25,5,0,'2003-06-21',42,723000,'PO: 7 GREGCA - 12.3mm Round Steel coil x 1.6 @ 0.00',0,1,'\'\''),(245,20,8,0,'2003-06-20',42,271000,'GREGCA bits and pieces',126.047,1,''),(246,20,8,0,'2003-06-20',42,723000,'GREGCA - GRN 7 - 123 x 1.43 @  std cost of0.3000',0.429,1,'\'\''),(247,20,8,0,'2003-06-20',42,23400,'GREGCA - GRN 7 - 123 x 1.43 x  price var of 1.28',1.8324,1,'\'\''),(248,20,8,0,'2003-06-20',42,890000,'GREGCA - Inv 23231122 GBP5.51724 @ a rate of 0.4300',12.8308,1,'\'\''),(249,20,8,0,'2003-06-20',42,800000,'GREGCA - Inv 23231122 GBP60.69 @ a rate of 0.4300',-141.139,1,'\'\''),(250,10,11,0,'2003-06-23',42,1,'GRANHR - HON90022 x 5 @ 13.8700',-69.35,1,'\'\''),(251,10,11,0,'2003-06-23',42,710000,'GRANHR',81.49,1,'\'\''),(252,10,11,0,'2003-06-23',42,890000,'GRANHR',-12.14,1,'\'\''),(253,10,12,0,'2003-06-23',42,1,'GRANHR - HON90021 x 6 @ 9.6900',58.14,1,'\'\''),(254,10,12,0,'2003-06-23',42,722000,'GRANHR - HON90021 x 6 @ 9.6900',-58.14,1,'\'\''),(255,10,12,0,'2003-06-23',42,1,'GRANHR - HON90021 x 6 @ 12.6500',-75.9,1,'\'\''),(256,10,12,0,'2003-06-23',42,710000,'GRANHR',89.18,1,'\'\''),(257,10,12,0,'2003-06-23',42,890000,'GRANHR',-13.28,1,'\'\''),(258,10,13,0,'2003-06-23',42,1,'GRANHR - 123 x 2 @ 0.3000',0.6,1,'\'\''),(259,10,13,0,'2003-06-23',42,720000,'GRANHR - 123 x 2 @ 0.3000',-0.6,1,'\'\''),(260,10,13,0,'2003-06-23',42,1,'GRANHR - 123 x 2 @ 2.9500',-5.9,1,'\'\''),(261,10,13,0,'2003-06-23',42,1,'GRANHR - HON90021 x 2 @ 9.6900',19.38,1,'\'\''),(262,10,13,0,'2003-06-23',42,722000,'GRANHR - HON90021 x 2 @ 9.6900',-19.38,1,'\'\''),(263,10,13,0,'2003-06-23',42,1,'GRANHR - HON90021 x 2 @ 12.6500',-25.3,1,'\'\''),(264,10,13,0,'2003-06-23',42,1,'GRANHR - HON90022 x 2 @ 13.8700',-27.74,1,'\'\''),(265,10,13,0,'2003-06-23',42,710000,'GRANHR',69.25,1,'\'\''),(266,10,13,0,'2003-06-23',42,890000,'GRANHR',-10.31,1,'\'\''),(267,10,14,0,'2003-06-23',42,21000,'GRANHR - HON90022 x 56 @ 13.8700',-776.72,1,'\'\''),(268,10,14,0,'2003-06-23',42,510000,'GRANHR - HON90022 @ 25%',194.18,1,'\'\''),(269,10,14,0,'2003-06-23',42,710000,'GRANHR',684.48,1,'\'\''),(270,10,14,0,'2003-06-23',42,890000,'GRANHR',-101.94,1,'\'\''),(271,12,7,0,'2003-06-22',42,700000,'',600,1,'\'\''),(272,12,7,0,'2003-06-22',42,710000,'',-605.99,1,'\'\''),(273,12,7,0,'2003-06-22',42,455000,'',5.99,1,'\'\''),(274,35,3,0,'2003-06-22',58,20000,'HON90022 cost was 0 changed to 2.1 x Quantity on hand of 8851.0',-18587.1,1,'\'\''),(275,35,3,0,'2003-06-22',58,722000,'HON90022 cost was 0 changed to 2.1 x Quantity on hand of 8851.0',18587.1,1,'\'\''),(276,10,15,0,'2003-06-23',42,256000,'GRANHR - HON90022 x 10 @ 2.1000',21,1,'\'\''),(277,10,15,0,'2003-06-23',42,722000,'GRANHR - HON90022 x 10 @ 2.1000',-21,1,'\'\''),(278,10,15,0,'2003-06-23',42,21000,'GRANHR - HON90022 x 10 @ 13.8700',-138.7,1,'\'\''),(279,10,15,0,'2003-06-23',42,510000,'GRANHR - HON90022 @ 1.5%',2.0805,1,'\'\''),(280,10,15,0,'2003-06-23',42,710000,'GRANHR',370.919,1,'\'\''),(281,10,15,0,'2003-06-23',42,890000,'GRANHR',-234.3,1,'\'\''),(282,17,5,0,'2003-06-22',42,20000,'HON90022 x -12 @ 2.1000 - stuff up',25.2,1,'\'\''),(283,17,5,0,'2003-06-22',42,722000,'HON90022 x -12 @ 2.1000 - stuff up',-25.2,1,'\'\''),(284,12,7,0,'2003-06-22',42,23700,'',259.71,1,'\'\''),(285,12,7,0,'2003-06-22',42,710000,'',-259.71,1,'\'\''),(286,12,7,0,'2003-06-22',42,23700,'',0,1,'\'\''),(287,12,7,0,'2003-06-22',42,710000,'',0,1,'\'\''),(288,12,8,0,'2003-06-23',42,700000,'',500,1,'\'\''),(289,12,8,0,'2003-06-23',42,710000,'',-512.5,1,'\'\''),(290,12,8,0,'2003-06-23',42,455000,'',12.5,1,'\'\''),(291,12,8,0,'2003-06-23',42,23700,'',128.571,1,'\'\''),(292,12,8,0,'2003-06-23',42,710000,'',-128.571,1,'\'\''),(293,12,8,0,'2003-06-23',42,23700,'',0,1,'\'\''),(294,12,8,0,'2003-06-23',42,710000,'',0,1,'\'\''),(295,12,8,0,'2003-06-23',42,23700,'',0,1,'\'\''),(296,12,8,0,'2003-06-23',42,710000,'',0,1,'\'\''),(297,12,8,0,'2003-06-23',42,23700,'',80.3572,1,'\'\''),(298,12,8,0,'2003-06-23',42,710000,'',-80.3572,1,'\'\''),(299,12,8,0,'2003-06-23',42,23700,'',0,1,'\'\''),(300,12,8,0,'2003-06-23',42,710000,'',0,1,'\'\''),(301,12,8,0,'2003-06-23',42,23700,'',10.7143,1,'\'\''),(302,12,8,0,'2003-06-23',42,710000,'',-10.7143,1,'\'\''),(303,12,8,0,'2003-06-23',42,23700,'',0,1,'\'\''),(304,12,8,0,'2003-06-23',42,710000,'',0,1,'\'\''),(305,12,11,0,'2003-06-23',42,107,'',85,1,'\'\''),(306,12,11,0,'2003-06-23',42,700000,'',-85,1,'\'\''),(307,12,12,0,'2003-06-23',42,700000,'',30,1,'\'\''),(308,12,12,0,'2003-06-23',42,710000,'',-30,1,'\'\''),(309,12,12,0,'2003-06-23',42,23700,'',0,1,'\'\''),(310,12,12,0,'2003-06-23',42,710000,'',0,1,'\'\''),(311,12,13,0,'2003-06-23',42,700000,'',145.161,1,'\'\''),(312,12,13,0,'2003-06-23',42,710000,'',-161.29,1,'\'\''),(313,12,13,0,'2003-06-23',42,455000,'',16.129,1,'\'\''),(314,12,13,0,'2003-06-23',42,23700,'',-18.4332,1,'\'\''),(315,12,13,0,'2003-06-23',42,710000,'',18.4332,1,'\'\''),(316,10,16,0,'2003-06-24',42,1,'WATTSGRP - HON90021 x 6 @ 9.6900',58.14,1,'\'\''),(317,10,16,0,'2003-06-24',42,722000,'WATTSGRP - HON90021 x 6 @ 9.6900',-58.14,1,'\'\''),(318,10,16,0,'2003-06-24',42,1,'WATTSGRP - HON90021 x 6 @ 15.3500',-92.1,1,'\'\''),(319,10,16,0,'2003-06-24',42,1,'WATTSGRP - HON90022 x 6 @ 2.1000',12.6,1,'\'\''),(320,10,16,0,'2003-06-24',42,722000,'WATTSGRP - HON90022 x 6 @ 2.1000',-12.6,1,'\'\''),(321,10,16,0,'2003-06-24',42,1,'WATTSGRP - HON90022 x 6 @ 15.8600',-95.16,1,'\'\''),(322,10,16,0,'2003-06-24',42,710000,'WATTSGRP',224.71,1,'\'\''),(323,10,16,0,'2003-06-24',42,890000,'WATTSGRP',-37.45,1,'\'\''),(327,10,17,0,'2003-06-24',42,1,'JOHNSON - HON90022 x 10 @ 2.1000',21,1,'\'\''),(328,10,17,0,'2003-06-24',42,722000,'JOHNSON - HON90022 x 10 @ 2.1000',-21,1,'\'\''),(329,10,17,0,'2003-06-24',42,1,'JOHNSON - HON90022 x 10 @ 15.8600',-226.571,1,'\'\''),(330,10,17,0,'2003-06-24',42,1,'JOHNSON - HON90022 @ 10%',22.6571,1,'\'\''),(331,10,17,0,'2003-06-24',42,710000,'JOHNSON',550.571,1,'\'\''),(332,10,17,0,'2003-06-24',42,890000,'JOHNSON',-346.657,1,'\'\''),(333,10,18,0,'2003-06-24',42,1,'JOHNSON - HON90022 x 10 @ 2.1000',21,1,'\'\''),(334,10,18,0,'2003-06-24',42,722000,'JOHNSON - HON90022 x 10 @ 2.1000',-21,1,'\'\''),(335,10,18,0,'2003-06-24',42,1,'JOHNSON - HON90022 x 10 @ 15.8600',-226.571,1,'\'\''),(336,10,18,0,'2003-06-24',42,1,'JOHNSON - HON90022 @ 10%',22.6571,1,'\'\''),(337,10,18,0,'2003-06-24',42,710000,'JOHNSON',244.7,1,'\'\''),(338,10,18,0,'2003-06-24',42,890000,'JOHNSON',-40.7857,1,'\'\''),(339,11,2,0,'0000-00-00',59,1,'JOHNSON - HON90022 x 3 @ 2.1000',-6.3,1,'\'\''),(340,11,2,0,'0000-00-00',59,722000,'JOHNSON - HON90022 x 3 @ 2.1000',6.3,1,'\'\''),(341,11,2,0,'0000-00-00',59,1,'JOHNSON - HON90022 x 3 @ 15.8600',67.9714,1,'\'\''),(342,11,2,0,'0000-00-00',59,1,'JOHNSON - HON90022 @ 10%',-6.7971,1,'\'\''),(343,11,2,0,'0000-00-00',59,710000,'JOHNSON',-73.4029,1,'\'\''),(344,11,2,0,'0000-00-00',59,890000,'JOHNSON',12.2286,1,'\'\''),(345,12,16,0,'2003-06-25',42,267000,'testing narrative',-250.25,1,'\'\''),(346,12,16,0,'2003-06-25',42,700000,'',250.25,1,'\'\''),(347,1,15,0,'2003-06-26',42,11000,'testing 2323',-174.286,1,'\'\''),(348,1,15,0,'2003-06-26',42,700000,'',174.286,1,'\'\''),(349,1,16,0,'2003-06-26',42,20000,'testing payment narrative',777.143,1,'\'\''),(350,1,16,0,'2003-06-26',42,700000,'',-777.143,1,'\'\''),(351,1,17,0,'2003-06-26',42,21000,'negative payment amount',-10,1,'\'\''),(352,1,17,0,'2003-06-26',42,700000,'',10,1,'\'\''),(353,11,3,0,'2003-06-29',42,1,'WATTSGRP - HON90022 x 2 @ 2.1000',-4.2,1,'\'\''),(354,11,3,0,'2003-06-29',42,256000,'WATTSGRP - HON90022 x 2 @ 2.1000',4.2,1,'\'\''),(355,11,3,0,'2003-06-29',42,1,'WATTSGRP - HON90022 x 2 @ 15.8600',31.72,1,'\'\''),(356,11,3,0,'2003-06-29',42,710000,'WATTSGRP',-38.06,1,'\'\''),(357,11,3,0,'2003-06-29',42,890000,'WATTSGRP',6.34,1,'\'\''),(358,11,4,0,'2003-06-29',65,1,'WALMON - HON90021 x 6 @ 9.6900',-58.14,1,'\'\''),(359,11,4,0,'2003-06-29',65,722000,'WALMON - HON90021 x 6 @ 9.6900',58.14,1,'\'\''),(360,11,4,0,'2003-06-29',65,1,'WALMON - HON90021 x 6 @ 25.0000',150,1,'\'\''),(361,11,4,0,'2003-06-29',65,1,'WALMON - HON90021 @ 2%',-3,1,'\'\''),(362,11,4,0,'2003-06-29',65,1,'WALMON - HON90022 x 5 @ 2.1000',-10.5,1,'\'\''),(363,11,4,0,'2003-06-29',65,722000,'WALMON - HON90022 x 5 @ 2.1000',10.5,1,'\'\''),(364,11,4,0,'2003-06-29',65,1,'WALMON - HON90022 x 5 @ 24.5000',122.5,1,'\'\''),(365,11,4,0,'2003-06-29',65,1,'WALMON - HON90022 @ 2%',-2.45,1,'\'\''),(366,11,4,0,'2003-06-29',65,710000,'WALMON',-267.05,1,'\'\''),(367,20,9,0,'2003-06-28',42,12000,'FREDBLOW johnny',18.4615,1,''),(368,20,9,0,'2003-06-28',42,890000,'FREDBLOW - Inv 1221223423 USD1.2 @ a rate of 0.65',1.8462,1,'\'\''),(369,20,9,0,'2003-06-28',42,800000,'FREDBLOW - Inv 1221223423 USD13.20 @ a rate of 0.65',-20.3077,1,'\'\''),(370,10,19,0,'2003-06-30',42,1,'JOHNSON - HON90022 x 50 @ 2.1000',105,1,'\'\''),(371,10,19,0,'2003-06-30',42,722000,'JOHNSON - HON90022 x 50 @ 2.1000',-105,1,'\'\''),(372,10,19,0,'2003-06-30',42,1,'JOHNSON - HON90022 x 50 @ 15.8600',-1132.86,1,'\'\''),(373,10,19,0,'2003-06-30',42,1,'JOHNSON - HON90022 @ 10%',113.286,1,'\'\''),(374,10,19,0,'2003-06-30',42,710000,'JOHNSON',1223.49,1,'\'\''),(375,10,19,0,'2003-06-30',42,890000,'JOHNSON',-203.914,1,'\'\''),(376,11,5,0,'2003-06-30',66,1,'JOHNSON - HON90021 x 23 @ 9.6900',-222.87,1,'\'\''),(377,11,5,0,'2003-06-30',66,722000,'JOHNSON - HON90021 x 23 @ 9.6900',222.87,1,'\'\''),(378,11,5,0,'2003-06-30',66,1,'JOHNSON - HON90021 x 23 @ 15.3500',353.05,1,'\'\''),(379,11,5,0,'2003-06-30',66,710000,'JOHNSON',-423.66,1,'\'\''),(380,11,5,0,'2003-06-30',66,890000,'JOHNSON',70.61,1,'\'\''),(381,10,20,0,'2003-07-07',43,1,'JOHNSON - HON90022 x 15 @ 2.1000',31.5,1,'\'\''),(382,10,20,0,'2003-07-07',43,722000,'JOHNSON - HON90022 x 15 @ 2.1000',-31.5,1,'\'\''),(383,10,20,0,'2003-07-07',43,1,'JOHNSON - HON90022 x 15 @ 15.8600',-237.9,1,'\'\''),(384,10,20,0,'2003-07-07',43,1,'JOHNSON - HON90022 @ 10%',23.79,1,'\'\''),(385,10,20,0,'2003-07-07',43,710000,'JOHNSON',256.93,1,'\'\''),(386,10,20,0,'2003-07-07',43,890000,'JOHNSON',-42.82,1,'\'\''),(387,25,6,0,'2003-07-05',43,301,'PO: 8 BIGBISC - Test gl entry in po line x 16 @ 4.56',73,1,'\'\''),(388,25,6,0,'2003-07-05',43,723000,'PO: 8 BIGBISC - Test gl entry in po line x 16 @ 4.56',-73,1,'\'\''),(389,25,7,0,'2003-07-05',43,720000,'PO: 8 BIGBISC - 13mm round spring steel X4K grade x 20 @ 0.00',0,1,'\'\''),(390,25,7,0,'2003-07-05',43,723000,'PO: 8 BIGBISC - 13mm round spring steel X4K grade x 20 @ 0.00',0,1,'\'\''),(391,11,6,0,'2003-07-05',41,1,'WATTSGRP - HON90022 x 3 @ 2.1000',-6.3,1,'\'\''),(392,11,6,0,'2003-07-05',41,722000,'WATTSGRP - HON90022 x 3 @ 2.1000',6.3,1,'\'\''),(393,11,6,0,'2003-07-05',41,1,'WATTSGRP - HON90022 x 3 @ 15.8600',47.58,1,'\'\''),(394,11,6,0,'2003-07-05',41,1,'WATTSGRP - HON90022 @ 20%',-9.516,1,'\'\''),(395,11,6,0,'2003-07-05',41,710000,'WATTSGRP',-45.674,1,'\'\''),(396,11,6,0,'2003-07-05',41,890000,'WATTSGRP',7.61,1,'\'\''),(397,10,21,0,'2003-07-08',43,1,'12345678 - HON90022 x 3 @ 2.1000',6.3,1,'\'\''),(398,10,21,0,'2003-07-08',43,722000,'12345678 - HON90022 x 3 @ 2.1000',-6.3,1,'\'\''),(399,10,21,0,'2003-07-08',43,1,'12345678 - HON90022 x 3 @ 15.8600',-47.58,1,'\'\''),(400,10,21,0,'2003-07-08',43,1,'12345678 - HONShock x 5 @ 8.5000',42.5,1,'\'\''),(401,10,21,0,'2003-07-08',43,722000,'12345678 - HONShock x 5 @ 8.5000',-42.5,1,'\'\''),(402,10,21,0,'2003-07-08',43,1,'12345678 - HONShock x 5 @ 25.9000',-129.5,1,'\'\''),(403,10,21,0,'2003-07-08',43,1,'12345678 - HONShock @ 2.5%',3.2375,1,'\'\''),(404,10,21,0,'2003-07-08',43,710000,'12345678',191.223,1,'\'\''),(405,10,21,0,'2003-07-08',43,810000,'12345678',-17.38,1,'\'\''),(406,10,22,0,'2003-07-08',43,1,'12345678 - HON90022 x 7 @ 2.1000',14.7,1,'\'\''),(407,10,22,0,'2003-07-08',43,722000,'12345678 - HON90022 x 7 @ 2.1000',-14.7,1,'\'\''),(408,10,22,0,'2003-07-08',43,1,'12345678 - HON90022 x 7 @ 15.8600',-111.02,1,'\'\''),(409,10,22,0,'2003-07-08',43,710000,'12345678',122.12,1,'\'\''),(410,10,22,0,'2003-07-08',43,810000,'12345678',-11.1,1,'\'\''),(411,10,23,0,'2003-07-10',43,1,'12345678 - HON90022 x 3 @ 2.1000',6.3,1,'\'\''),(412,10,23,0,'2003-07-10',43,722000,'12345678 - HON90022 x 3 @ 2.1000',-6.3,1,'\'\''),(413,10,23,0,'2003-07-10',43,1,'12345678 - HON90022 x 3 @ 15.8600',-47.58,1,'\'\''),(414,10,23,0,'2003-07-10',43,710000,'12345678',52.34,1,'\'\''),(415,10,23,0,'2003-07-10',43,810000,'12345678',-4.76,1,'\'\''),(416,20,10,0,'2003-08-06',44,1,'GREGCA',265,1,''),(417,20,10,0,'2003-08-06',44,890000,'GREGCA - Inv 23231122 GBP21.2 @ a rate of 0.8000',26.5,1,'\'\''),(418,20,10,0,'2003-08-06',44,800000,'GREGCA - Inv 23231122 GBP233.20 @ a rate of 0.8000',-291.5,1,'\'\''),(419,20,11,0,'2003-08-06',44,1,'GREGCA',56.25,1,''),(420,20,11,0,'2003-08-06',44,890000,'GREGCA - Inv 23231122 GBP4.5 @ a rate of 0.8000',5.625,1,'\'\''),(421,20,11,0,'2003-08-06',44,800000,'GREGCA - Inv 23231122 GBP49.50 @ a rate of 0.8000',-61.875,1,'\'\''),(422,20,12,0,'2003-08-06',44,1,'GREGCA',62.5,1,''),(423,20,12,0,'2003-08-06',44,890000,'GREGCA - Inv 23231122 GBP5 @ a rate of 0.8000',6.25,1,'\'\''),(424,20,12,0,'2003-08-06',44,800000,'GREGCA - Inv 23231122 GBP55.00 @ a rate of 0.8000',-68.75,1,'\'\''),(425,20,13,0,'2003-08-06',44,1,'GREGCA',140,1,''),(426,20,13,0,'2003-08-06',44,890000,'GREGCA - Inv 23231122 GBP11.2 @ a rate of 0.8000',14,1,'\'\''),(427,20,13,0,'2003-08-06',44,800000,'GREGCA - Inv 23231122 GBP123.20 @ a rate of 0.8000',-154,1,'\'\''),(428,22,3,0,'2003-08-07',44,800000,'GREGCA-2122233',17.5,1,'\'\''),(429,22,3,0,'2003-08-07',44,455000,'GREGCA-2122233',-2.5,1,'\'\''),(430,22,3,0,'2003-08-07',44,700000,'GREGCA-2122233',-15,1,'\'\''),(431,11,7,0,'2003-08-10',46,1,'WATTSGRP - hon90022 x 4 @ 2.1000',-8.4,1,'\'\''),(432,11,7,0,'2003-08-10',46,12000,'WATTSGRP - hon90022 x 4 @ 2.1000',8.4,1,'\'\''),(433,11,7,0,'2003-08-10',46,1,'WATTSGRP - hon90022 x 4 @ 15.8600',63.44,1,'\'\''),(434,11,7,0,'2003-08-10',46,710000,'WATTSGRP',-76.13,1,'\'\''),(435,11,7,0,'2003-08-10',46,890000,'WATTSGRP',12.69,1,'\'\''),(436,10,24,0,'2003-12-15',48,1,'JOHNSON - 123 x 9 @ 0.3000',2.7,1,''),(437,10,24,0,'2003-12-15',48,720000,'JOHNSON - 123 x 9 @ 0.3000',-2.7,1,''),(438,10,24,0,'2003-12-15',48,1,'JOHNSON - 123 x 9 @ 3.2000',-28.8,1,''),(439,10,24,0,'2003-12-15',48,1,'JOHNSON - 125 x 10 @ 0.6200',6.2,1,''),(440,10,24,0,'2003-12-15',48,720000,'JOHNSON - 125 x 10 @ 0.6200',-6.2,1,''),(441,10,24,0,'2003-12-15',48,1,'JOHNSON - 125 x 10 @ 3.5000',-35,1,''),(442,10,24,0,'2003-12-15',48,710000,'JOHNSON',76.56,1,''),(443,10,24,0,'2003-12-15',48,890000,'JOHNSON',-12.76,1,''),(444,11,8,0,'2003-12-14',48,1,'JOHNSON - 125 x 10 @ .5',5,1,''),(445,11,8,0,'2003-12-14',48,710000,'JOHNSON',-6,1,''),(446,11,8,0,'2003-12-14',48,890000,'JOHNSON',1,1,''),(447,11,9,0,'2003-12-14',48,1,'JOHNSON - 123 x 10 @ .2',2,1,''),(448,11,9,0,'2003-12-14',48,710000,'JOHNSON',-2.4,1,''),(449,11,9,0,'2003-12-14',48,890000,'JOHNSON',0.4,1,''),(450,11,10,0,'2003-12-16',48,1,'JOHNSON - 125 x 10 @ 2.5',25,1,''),(451,11,10,0,'2003-12-16',48,710000,'JOHNSON',-30,1,''),(452,11,10,0,'2003-12-16',48,890000,'JOHNSON',5,1,''),(453,11,11,0,'2003-12-16',48,1,'JOHNSON - HON90021 x 2 @ .35',0.7,1,''),(454,11,11,0,'2003-12-16',48,710000,'JOHNSON',-0.84,1,''),(455,11,11,0,'2003-12-16',48,890000,'JOHNSON',0.14,1,''),(456,10,25,0,'2004-01-15',49,1,'12345678 - HON90022 x 1 @ 2.1000',2.1,1,''),(457,10,25,0,'2004-01-15',49,722000,'12345678 - HON90022 x 1 @ 2.1000',-2.1,1,''),(458,10,25,0,'2004-01-15',49,1,'12345678 - HON90022 x 1 @ 15.8600',-15.86,1,''),(459,10,25,0,'2004-01-15',49,710000,'12345678',17.45,1,''),(460,10,25,0,'2004-01-15',49,810000,'12345678',-1.59,1,''),(461,10,26,0,'2004-01-15',49,1,'JOHNSON - HONShock x 3 @ 8.5000',25.5,1,''),(462,10,26,0,'2004-01-15',49,722000,'JOHNSON - HONShock x 3 @ 8.5000',-25.5,1,''),(463,10,26,0,'2004-01-15',49,1,'JOHNSON - HONShock x 3 @ 25.9000',-77.7,1,''),(464,10,26,0,'2004-01-15',49,710000,'JOHNSON',93.24,1,''),(465,10,26,0,'2004-01-15',49,890000,'JOHNSON',-15.54,1,''),(466,12,18,0,'2004-01-14',49,263100,'paper taken by fred',-5.25,1,''),(467,12,18,0,'2004-01-14',49,700000,'',2208.56,1,''),(468,12,18,0,'2004-01-14',49,710000,'',-2203.31,1,''),(469,12,18,0,'2004-01-14',49,23700,'',944.277,1,''),(470,12,18,0,'2004-01-14',49,710000,'',-944.277,1,''),(471,11,6,0,'2003-07-05',43,23700,'',19.5729,1,''),(472,11,6,0,'2003-07-05',43,710000,'',-19.5729,1,''),(473,12,2,0,'2003-01-26',37,23700,'',-26.625,1,''),(474,12,2,0,'2003-01-26',37,710000,'',26.625,1,''),(475,11,7,0,'2003-08-10',44,23700,'',32.6271,1,''),(476,11,7,0,'2003-08-10',44,710000,'',-32.6271,1,''),(477,11,5,0,'2003-06-30',42,23700,'',-119.117,1,''),(478,11,5,0,'2003-06-30',42,710000,'',119.117,1,''),(479,12,3,0,'2003-01-31',37,23700,'',1132.27,1,''),(480,12,3,0,'2003-01-31',37,710000,'',-1132.27,1,'');
UNLOCK TABLES;
/*!40000 ALTER TABLE GLTrans ENABLE KEYS */;

--
-- Dumping data for table `GRNs`
--


/*!40000 ALTER TABLE GRNs DISABLE KEYS */;
LOCK TABLES GRNs WRITE;
INSERT INTO GRNs VALUES (0,1,1,'125','2003-01-20','12.5mm round steel',2430.0000,2430.0000,'BHPSTEEL'),(0,2,3,'','2003-01-20','freight on shipment as agreed',60.0000,1.0000,'BHPSTEEL'),(0,3,4,'130','2003-01-23','13mm round spring steel X4K grade',5853.0000,0.0000,'BIGBISC'),(0,4,5,'140','2003-01-23','14mm round steel X4k grade',2872.0000,52.0000,'BIGBISC'),(0,5,12,'125','2003-06-19','12.5mm round steel',30.5000,30.5000,'BIGBISC'),(0,6,12,'125','2003-06-20','12.5mm round steel',65.0000,57.0000,'BIGBISC'),(0,7,13,'123','2003-06-21','12.3mm Round Steel coil',1.6000,1.4300,'GREGCA'),(0,8,15,'','2003-07-05','Test gl entry in po line',16.0000,0.0000,'BIGBISC'),(0,9,16,'130','2003-07-05','13mm round spring steel X4K grade',20.0000,0.0000,'BIGBISC');
UNLOCK TABLES;
/*!40000 ALTER TABLE GRNs ENABLE KEYS */;

--
-- Dumping data for table `HoldReasons`
--


/*!40000 ALTER TABLE HoldReasons DISABLE KEYS */;
LOCK TABLES HoldReasons WRITE;
INSERT INTO HoldReasons VALUES (1,'Good History',0),(20,'Watch',0),(30,'No more work until payment rec',1),(51,'In liquidation',1);
UNLOCK TABLES;
/*!40000 ALTER TABLE HoldReasons ENABLE KEYS */;

--
-- Dumping data for table `LastCostRollUp`
--


/*!40000 ALTER TABLE LastCostRollUp DISABLE KEYS */;
LOCK TABLES LastCostRollUp WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE LastCostRollUp ENABLE KEYS */;

--
-- Dumping data for table `LocStock`
--


/*!40000 ALTER TABLE LocStock DISABLE KEYS */;
LOCK TABLES LocStock WRITE;
INSERT INTO LocStock VALUES ('DEN','123',-13.4,0),('DEN','125',2515.5,0),('DEN','130',5873.0,0),('DEN','140',2872.0,0),('DEN','HON90021',8698.5,12),('DEN','HON90022',8283.0,0),('DEN','HON92FrSus',0.0,0),('DEN','HONCIVIC92SET',0.0,0),('DEN','HONShock',-13.0,0),('MEL','123',0.0,0),('MEL','125',0.0,0),('MEL','130',0.0,0),('MEL','140',0.0,0),('MEL','HON90021',642.0,12),('MEL','HON90022',315.0,0),('MEL','HON92FrSus',0.0,0),('MEL','HONCIVIC92SET',0.0,0),('MEL','HONShock',-5.0,0),('MUC','123',0.0,0),('MUC','125',0.0,0),('MUC','130',0.0,0),('MUC','140',0.0,0),('MUC','HON90021',0.0,1),('MUC','HON90022',131.0,0),('MUC','HON92FrSus',0.0,0),('MUC','HONCIVIC92SET',0.0,0),('MUC','HONShock',0.0,0);
UNLOCK TABLES;
/*!40000 ALTER TABLE LocStock ENABLE KEYS */;

--
-- Dumping data for table `Locations`
--


/*!40000 ALTER TABLE Locations DISABLE KEYS */;
LOCK TABLES Locations WRITE;
INSERT INTO Locations VALUES ('DEN','Denver','532-536 Wentworth Street','Denver','Colorado','233 5532 216','233 5532 215','g.bovert@weberp.com','Graham Bouvert',1),('MEL','Melbourne Australia','3-5 Albert Road','Morriban','Melbourne Victoria','+61 3 4445 23554','+61 3 4554 23244','g.prewit@weberp.com','Greg Prewit',1),('MUC','Munich Test Facility','Unit H Grand Tourismo Ind Est','Munchen','','+49 5554 22 121','+49 5442 22132','','Herman',1);
UNLOCK TABLES;
/*!40000 ALTER TABLE Locations ENABLE KEYS */;

--
-- Dumping data for table `OrderDeliveryDifferencesLog`
--


/*!40000 ALTER TABLE OrderDeliveryDifferencesLog DISABLE KEYS */;
LOCK TABLES OrderDeliveryDifferencesLog WRITE;
INSERT INTO OrderDeliveryDifferencesLog VALUES (1,6,'HON90021',6.0000,'JOHNSON','JOHN','BO'),(4,16,'HON90021',6.0000,'WATTSGRP','WATTLA','BO'),(4,16,'HON90022',5.0000,'WATTSGRP','WATTLA','BO'),(7,25,'HON90022',1.0000,'12345678','1234','BO'),(15,19,'HON90022',35.0000,'JOHNSON','JOHN','BO'),(15,20,'HON90022',70.0000,'JOHNSON','JOHN','BO'),(16,21,'HON90022',40.0000,'12345678','1234','BO'),(16,22,'HON90022',36.0000,'12345678','1234','BO'),(16,23,'HON90022',40.0000,'12345678','1234','BO');
UNLOCK TABLES;
/*!40000 ALTER TABLE OrderDeliveryDifferencesLog ENABLE KEYS */;

--
-- Dumping data for table `PaymentTerms`
--


/*!40000 ALTER TABLE PaymentTerms DISABLE KEYS */;
LOCK TABLES PaymentTerms WRITE;
INSERT INTO PaymentTerms VALUES ('20','Due 20th Of the Following Month',0,22),('30','Due By End Of The Following Month',0,30),('7','Payment due within 7 days',7,0),('CA','Cash Only',1,0);
UNLOCK TABLES;
/*!40000 ALTER TABLE PaymentTerms ENABLE KEYS */;

--
-- Dumping data for table `Periods`
--


/*!40000 ALTER TABLE Periods DISABLE KEYS */;
LOCK TABLES Periods WRITE;
INSERT INTO Periods VALUES (37,'2003-01-31'),(38,'2003-02-28'),(39,'2003-03-31'),(40,'2003-04-30'),(41,'2003-05-31'),(42,'2003-06-30'),(43,'2003-07-31'),(44,'2003-08-31'),(45,'2003-09-30'),(46,'2003-10-31'),(47,'2003-11-30'),(48,'2003-12-31'),(49,'2004-01-31'),(50,'2004-02-29'),(51,'2004-03-31'),(52,'2004-04-30'),(53,'2004-05-31'),(54,'2004-06-30'),(55,'2004-07-31'),(56,'2004-08-31'),(57,'2004-09-30'),(58,'2004-10-31'),(59,'2004-11-30'),(60,'2004-12-31'),(61,'2005-01-31'),(62,'2005-02-28'),(63,'2005-03-31'),(64,'2005-04-30'),(65,'2005-05-31'),(66,'2005-06-30'),(67,'2005-07-31'),(68,'2005-08-31');
UNLOCK TABLES;
/*!40000 ALTER TABLE Periods ENABLE KEYS */;

--
-- Dumping data for table `Prices`
--


/*!40000 ALTER TABLE Prices DISABLE KEYS */;
LOCK TABLES Prices WRITE;
INSERT INTO Prices VALUES ('123','EX','AUD','',"9.6500",'\'\''),('123','EX','USD','',"2.9500",'\'\''),('123','WS','AUD','',"12.3500",'\'\''),('123','WS','USD','',"3.2000",'\'\''),('HON90021','EX','AUD','',"25.0000",'\'\''),('HON90021','EX','USD','',"12.6500",'\'\''),('HON90021','WS','USD','',"15.3500",'\'\''),('HON90021','WS','USD','JOHNSON',"12.8900",'\'\''),('HON90022','EX','AUD','',"24.5000",'\'\''),('HON90022','EX','USD','',"13.8700",'\'\''),('HON90022','WS','USD','',"15.8600",'\'\''),('HON92FrSus','EX','USD','',"45.9500",'\'\''),('HON92FrSus','WS','USD','',"53.2000",'\'\''),('HONShock','EX','USD','',"24.8000",'\'\''),('HONShock','WS','USD','',"25.9000",'\'\'');
UNLOCK TABLES;
/*!40000 ALTER TABLE Prices ENABLE KEYS */;

--
-- Dumping data for table `PurchData`
--


/*!40000 ALTER TABLE PurchData DISABLE KEYS */;
LOCK TABLES PurchData WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE PurchData ENABLE KEYS */;

--
-- Dumping data for table `PurchOrderDetails`
--


/*!40000 ALTER TABLE PurchOrderDetails DISABLE KEYS */;
LOCK TABLES PurchOrderDetails WRITE;
INSERT INTO PurchOrderDetails VALUES (1,1,'125','2003-01-20','12.5mm round steel',720000,2430.0000,0.6500,0.6500,0.6200,2500.0000,2430.0000,0,'0',0),(2,1,'130','2003-01-20','13mm round spring steel X4K grade',720000,0.0000,0.6500,0.0000,0.0000,2600.0000,0.0000,0,'0',0),(3,1,'','2003-01-21','freight on shipment as agreed',24000,1.0000,120.0000,120.0000,52.1739,1.0000,60.0000,0,'0',1),(4,2,'130','2003-01-20','13mm round spring steel X4K grade',720000,0.0000,0.2900,0.0000,0.6200,6200.0000,5853.0000,0,'0',0),(5,2,'140','2003-01-20','14mm round steel X4k grade',720000,52.0000,0.3500,0.3500,0.6200,3800.0000,2872.0000,0,'0',0),(6,3,'130','2003-02-15','13mm round spring steel X4K grade',720000,0.0000,0.3500,0.0000,0.0000,1220.0000,0.0000,0,'0',0),(7,3,'','2003-02-02','Sundry testing stuff',21000,0.0000,1.3200,0.0000,0.0000,15.0000,0.0000,0,'0',0),(8,4,'','2003-03-28','Pencils and staples',263000,0.0000,0.3500,0.0000,0.0000,50.0000,0.0000,0,'',0),(9,4,'125','2003-03-31','12.5mm round steel',720000,0.0000,0.5200,0.0000,0.0000,120.0000,0.0000,0,'0',0),(10,5,'125','2003-06-05','12.5mm round steel',720000,0.0000,5.2500,0.0000,0.0000,15.0000,0.0000,0,'0',0),(11,5,'130','2003-06-06','13mm round spring steel X4K grade',720000,0.0000,6.3500,0.0000,0.0000,25.0000,0.0000,0,'0',0),(12,6,'125','2003-06-20','12.5mm round steel',720000,87.5000,1.2000,1.1750,0.6200,35.2000,95.5000,0,'0',1),(13,7,'123','2003-06-22','12.3mm Round Steel coil',720000,1.4300,0.6800,0.6800,0.3000,1.0000,1.6000,0,'',1),(14,5,'140','2003-06-29','14mm round steel X4k grade',720000,0.0000,1.6500,0.0000,0.0000,25.0000,0.0000,0,'',0),(15,8,'','2003-07-06','Test gl entry in po line',301,0.0000,3.6500,0.0000,4.5625,23.0000,16.0000,0,'',0),(16,8,'130','2003-07-05','13mm round spring steel X4K grade',720000,0.0000,2.2000,0.0000,0.6200,65.0000,20.0000,0,'',0),(17,9,'123','2004-04-20','12.3mm Round Steel coil',720000,0.0000,0.3500,0.0000,0.0000,980.0000,0.0000,2,'0',0),(18,9,'125','2004-04-20','12.5mm round steel',720000,0.0000,3.6000,0.0000,0.0000,658.0000,0.0000,2,'0',0),(19,9,'130','2004-04-20','13mm round spring steel X4K grade',720000,0.0000,0.5000,0.0000,0.0000,4300.0000,0.0000,2,'0',0),(20,10,'HONShock','2004-04-20','Honda 92 Monroe Shock Absorber',722000,0.0000,8.2000,0.0000,0.0000,350.0000,0.0000,2,'0',0);
UNLOCK TABLES;
/*!40000 ALTER TABLE PurchOrderDetails ENABLE KEYS */;

--
-- Dumping data for table `PurchOrders`
--


/*!40000 ALTER TABLE PurchOrders DISABLE KEYS */;
LOCK TABLES PurchOrders WRITE;
INSERT INTO PurchOrders VALUES (1,'BHPSTEEL','','2003-01-20 00:00:00',2.3000,'2003-01-20 00:00:00',0,'pgd','6654 - hgb','DEN','532-536 Wentworth Street','Denver','Colorado','233 5532 216'),(2,'BIGBISC','test purchase order','2003-01-20 00:00:00',0.4300,'2003-01-20 00:00:00',0,'fdf','2323544','MEL','3-5 Albert Road','Morriban','Melbourne Victoria','+61 3 4445 23554'),(3,'MITOOI2','','2003-02-01 00:00:00',1.3350,'2003-02-01 00:00:00',0,'eric','98877','MEL','3-5 Albert Road','Morriban','Melbourne Victoria','+61 3 4445 23554'),(4,'BIGBISC','','2003-03-19 00:00:00',0.4300,'2003-03-19 00:00:00',0,'','','MEL','532-536 Wentworth Street','Denver','Colorado','233 5532 216'),(5,'BHPSTEEL','','2003-06-05 00:00:00',1.1900,'2004-02-25 00:00:00',0,'','','DEN','532-536 Wentworth Street','Denver','Colorado','233 5532 216'),(6,'BIGBISC','','2003-06-20 00:00:00',0.4300,'2003-06-20 00:00:00',0,'','','DEN','532-536 Wentworth Street','Denver','Colorado','233 5532 216'),(7,'GREGCA','','2003-06-21 00:00:00',0.4300,'2003-06-21 00:00:00',0,'phil','sdds','DEN','532-536 Wentworth Street','Denver','Colorado','233 5532 216'),(8,'BIGBISC','','2003-07-05 00:00:00',0.8000,'2004-01-28 00:00:00',0,'ewweew','we','DEN','532-536 Wentworth Street','Denver','Colorado','233 5532 216'),(9,'GREGCA','','2004-01-14 00:00:00',0.8000,'2004-02-25 00:00:00',0,'','','DEN','532-536 Wentworth Street','Denver','Colorado','233 5532 216'),(10,'GREGCA','','2004-01-14 00:00:00',0.8000,'2004-02-25 00:00:00',0,'','','DEN','532-536 Wentworth Street','Denver','Colorado','233 5532 216');
UNLOCK TABLES;
/*!40000 ALTER TABLE PurchOrders ENABLE KEYS */;

--
-- Dumping data for table `ReportColumns`
--


/*!40000 ALTER TABLE ReportColumns DISABLE KEYS */;
LOCK TABLES ReportColumns WRITE;
INSERT INTO ReportColumns VALUES (1,1,'Qty','Dec 03',0,48,48,'Quantity',0,0,'',1,'N',0),(1,2,'Value','dec 03',0,48,48,'Gross Value',0,0,'',1,'N',0),(1,3,'GP','Dec 03',0,48,48,'Gross Profit',0,0,'',1,'N',0),(2,1,'Value','',0,49,51,'Net Value',0,0,'',1,'N',0);
UNLOCK TABLES;
/*!40000 ALTER TABLE ReportColumns ENABLE KEYS */;

--
-- Dumping data for table `ReportHeaders`
--


/*!40000 ALTER TABLE ReportHeaders DISABLE KEYS */;
LOCK TABLES ReportHeaders WRITE;
INSERT INTO ReportHeaders VALUES (1,'Sales to Johnson Vehicles January 2003','Customer Code',0,'J','JZZZ','Product Code',0,'1','ZZZZZ','Not Used',0,'','','Not Used',0,'',''),(2,'Sales in 2004','Customer Code',0,'0','zzzzzzz','Not Used',0,'','','Not Used',0,'','','Not Used',0,'','');
UNLOCK TABLES;
/*!40000 ALTER TABLE ReportHeaders ENABLE KEYS */;

--
-- Dumping data for table `SalesAnalysis`
--


/*!40000 ALTER TABLE SalesAnalysis DISABLE KEYS */;
LOCK TABLES SalesAnalysis WRITE;
INSERT INTO SalesAnalysis VALUES ('WS',37,131.5715,0.0000,'JOHNSON','JOHN',6.0000,2.6315,'HON90021','AR',1,'25','Coil',1),('EX',37,6443.5000,0.0000,'WALMON','WALM',263.0000,0.0000,'HON90022','AR',1,'25','Coil',2),('WS',38,1140.2857,503.8800,'JOHNSON','JOHN',52.0000,0.0000,'HON90021','AR',1,'25','Coil',3),('WS',39,131.5714,58.1400,'WATTSGRP','WATTLA',6.0000,0.0000,'HON90021','CA',1,'25','Coil',4),('WS',39,113.2857,0.0000,'WATTSGRP','WATTLA',5.0000,0.0000,'HON90022','CA',1,'25','Coil',5),('WS',39,76.7500,33.9150,'12345678','1234',3.5000,0.0000,'HON90021','AR',1,'25','Coil',6),('WS',41,87.7143,38.7600,'JOHNSON','JOHN',4.0000,1.7543,'HON90021','AR',1,'25','Coil',8),('WS',42,18.2858,1.2000,'12345678','1234',4.0000,0.0000,'123','AR',1,'25','Steel',9),('WS',42,87.7142,38.7600,'12345678','1234',4.0000,0.0000,'HON90021','AR',1,'25','Coil',10),('WS',42,67.9714,0.0000,'12345678','1234',3.0000,0.0000,'HON90022','AR',1,'25','Coil',11),('WS',42,-22.6572,4.2000,'WATTSGRP','WATT',-1.0000,0.0000,'HON90022','AR',1,'25','Coil',12),('WS',42,380.0000,90.9500,'12345678','1234',5.0000,9.5000,'HON92FrSus','AR',1,'25','SUSASS',13),('WS',42,21.9286,9.6900,'JOHNSON','JOHNTEMPE',1.0000,0.0000,'HON90021','AR',1,'2','Coil',14),('EX',42,1446.4429,21.0000,'GRANHR','GRAN',73.0000,280.3721,'HON90022','UK',1,'25','Coil',15),('EX',42,144.5715,77.5200,'GRANHR','GRAN',8.0000,0.0000,'HON90021','UK',1,'25','Coil',16),('EX',42,8.4286,0.6000,'GRANHR','GRAN',2.0000,0.0000,'123','UK',1,'25','Steel',17),('WS',42,131.5714,58.1400,'WATTSGRP','WATTLA',6.0000,0.0000,'HON90021','CA',1,'25','Coil',18),('WS',42,135.9429,12.6000,'WATTSGRP','WATTLA',6.0000,0.0000,'HON90022','CA',1,'25','Coil',19),('WS',42,1585.9999,147.0000,'JOHNSON','JOHN',70.0000,158.5999,'HON90022','AR',1,'25','Coil',21),('WS',59,-67.9714,-6.3000,'JOHNSON','JOHN',-3.0000,-6.7971,'HON90022','AR',1,'25','Coil',22),('EX',65,-150.0000,-58.1400,'WALMON','WALM',-6.0000,-3.0000,'HON90021','AR',1,'25','Coil',23),('EX',65,-122.5000,-10.5000,'WALMON','WALM',-5.0000,-2.4500,'HON90022','AR',1,'25','Coil',24),('WS',66,-504.3571,-222.8700,'JOHNSON','JOHN',-23.0000,0.0000,'HON90021','AR',1,'25','Coil',25),('WS',43,237.9000,31.5000,'JOHNSON','JOHN',15.0000,23.7900,'HON90022','AR',1,'25','Coil',26),('WS',41,-47.5800,-6.3000,'WATTSGRP','WATTSSAND',-3.0000,-9.5160,'HON90022','CA',1,'25','Coil',27),('WS',43,206.1800,27.3000,'12345678','1234',13.0000,0.0000,'HON90022','AR',1,'25','Coil',28),('WS',43,129.5000,42.5000,'12345678','1234',5.0000,3.2375,'HONShock','AR',1,'25','SHOCKS',29),('WS',46,-63.4400,-8.4000,'WATTSGRP','WATTSSAND',-4.0000,0.0000,'hon90022','CA',1,'25','Coil',30),('WS',48,26.8000,2.7000,'JOHNSON','JOHNTEMPE',9.0000,0.0000,'123','AR',1,'2','Steel',31),('WS',48,10.0000,6.2000,'JOHNSON','JOHNTEMPE',10.0000,0.0000,'125','AR',1,'2','Steel',32),('WS',48,-5.0000,0.0000,'JOHNSON','JOHN',0.0000,0.0000,'125','AR',1,'25','Steel',33),('WS',48,-0.7000,0.0000,'JOHNSON','JOHN',0.0000,0.0000,'HON90021','AR',1,'25','Coil',34),('WS',49,15.8600,2.1000,'12345678','1234',1.0000,0.0000,'HON90022','AR',1,'25','Coil',35),('WS',49,77.7000,25.5000,'JOHNSON','JOHNTEMPE',3.0000,0.0000,'HONShock','AR',1,'2','SHOCKS',36);
UNLOCK TABLES;
/*!40000 ALTER TABLE SalesAnalysis ENABLE KEYS */;

--
-- Dumping data for table `SalesGLPostings`
--


/*!40000 ALTER TABLE SalesGLPostings DISABLE KEYS */;
LOCK TABLES SalesGLPostings WRITE;
INSERT INTO SalesGLPostings VALUES (1,'AN','ANY',1,107,'AN'),(2,'UK','ANY',1,112,'EX');
UNLOCK TABLES;
/*!40000 ALTER TABLE SalesGLPostings ENABLE KEYS */;

--
-- Dumping data for table `SalesOrderDetails`
--


/*!40000 ALTER TABLE SalesOrderDetails DISABLE KEYS */;
LOCK TABLES SalesOrderDetails WRITE;
INSERT INTO SalesOrderDetails VALUES (1,'HON90021',10.0000,15.3500,10.0000,0,0.0200,'2003-05-20 00:00:00',1),(2,'HON90022',263.0000,24.5000,300.0000,0,0.0000,'2003-01-21 00:00:00',0),(3,'HON90021',52.0000,15.3500,52.0000,0,0.0000,'2003-02-24 00:00:00',1),(4,'HON90021',12.0000,15.3500,12.0000,0,0.0000,'2003-06-24 00:00:00',1),(4,'HON90022',11.0000,15.8600,11.0000,0,0.0000,'2003-06-24 00:00:00',1),(5,'HON90021',3.5000,15.3500,3.5000,0,0.0000,'2003-03-28 00:00:00',1),(6,'HON90022',1.0000,15.8600,1.0000,0,0.0000,'2003-06-13 00:00:00',1),(7,'123',2.0000,3.2000,2.0000,0,0.0000,'2003-06-13 00:00:00',1),(7,'HON90021',2.0000,15.3500,2.0000,0,0.0000,'2003-06-13 00:00:00',1),(7,'HON90022',2.0000,15.8600,2.0000,0,0.0000,'2004-01-15 00:00:00',1),(8,'123',2.0000,3.2000,2.0000,0,0.0000,'2003-06-13 00:00:00',1),(8,'HON90021',2.0000,15.3500,2.0000,0,0.0000,'2003-06-13 00:00:00',1),(8,'HON90022',2.0000,15.8600,2.0000,0,0.0000,'2003-06-13 00:00:00',1),(8,'HON92FrSus',5.0000,53.2000,5.0000,0,0.0250,'2003-06-13 00:00:00',1),(9,'HON90021',1.0000,15.3500,1.0000,0,0.0000,'2003-06-16 00:00:00',1),(10,'HON90021',6.0000,12.6500,6.0000,0,0.0000,'2003-06-23 00:00:00',1),(11,'HON90022',5.0000,13.8700,5.0000,0,0.0000,'2003-06-23 00:00:00',1),(12,'123',2.0000,2.9500,2.0000,0,0.0000,'2003-06-23 00:00:00',1),(12,'HON90021',2.0000,12.6500,2.0000,0,0.0000,'2003-06-23 00:00:00',1),(12,'HON90022',2.0000,13.8700,2.0000,0,0.0000,'2003-06-23 00:00:00',1),(13,'HON90022',56.0000,13.8700,56.0000,0,0.2500,'2003-06-23 00:00:00',1),(14,'HON90022',10.0000,13.8700,98.0000,0,0.0150,'2003-06-23 00:00:00',0),(15,'HON90022',85.0000,15.8600,85.0000,0,0.1000,'2003-07-07 00:00:00',1),(16,'HON90022',13.0000,15.8600,43.0000,0,0.0000,'2003-07-10 00:00:00',0),(16,'HONShock',5.0000,25.9000,5.0000,0,0.0250,'2003-07-08 00:00:00',1),(17,'HON90021',0.0000,25.0000,6.0000,0,0.0325,'0000-00-00 00:00:00',0),(18,'HON90022',0.0000,0.0000,25.0000,0,0.0000,'0000-00-00 00:00:00',0),(19,'HON90021',0.0000,15.3500,1.0000,0,0.0000,'0000-00-00 00:00:00',0),(20,'123',9.0000,3.2000,10.0000,0,0.0000,'2003-12-15 00:00:00',0),(20,'125',10.0000,3.5000,10.0000,0,0.0000,'2003-12-15 00:00:00',1),(21,'HON90022',0.0000,15.8600,6.0000,0,0.0250,'0000-00-00 00:00:00',0),(22,'HON90021',0.0000,25.0000,50.0000,0,0.0000,'0000-00-00 00:00:00',0),(23,'HON90022',0.0000,15.8600,12.0000,0,0.0150,'0000-00-00 00:00:00',0),(24,'HON90021',0.0000,15.3500,5.0000,0,0.0200,'0000-00-00 00:00:00',0),(24,'HON90022',0.0000,15.8600,16.0000,0,0.0200,'0000-00-00 00:00:00',0),(25,'HONShock',3.0000,25.9000,5.0000,0,0.0000,'2004-01-15 00:00:00',0);
UNLOCK TABLES;
/*!40000 ALTER TABLE SalesOrderDetails ENABLE KEYS */;

--
-- Dumping data for table `SalesOrders`
--


/*!40000 ALTER TABLE SalesOrders DISABLE KEYS */;
LOCK TABLES SalesOrders WRITE;
INSERT INTO SalesOrders VALUES (1,'JOHNSON','JOHN','',NULL,'','2003-01-20','WS',10,'198 Greater Lofty Heights','San Diego','California','','2412 22356','rod.champion@johnson.com','San Diego',0.00,'DEN','2003-01-21',1,'2003-01-20'),(2,'WALMON','WALM','',NULL,'','2003-01-20','EX',10,'309 Centennial Highway','Ballmain, Sydney','New South Wales','','+61 2 3546 8875','phyllis@WallMonterey.com.','Wallace Monterey - Sydney',0.00,'MEL','2003-01-21',1,'2003-01-20'),(3,'JOHNSON','JOHN','',NULL,'','2003-02-01','WS',10,'198 Greater Lofty Heights','San Diego','California','','2412 22356','rod.champion@johnson.com','San Diego',0.00,'DEN','2003-02-06',1,'2003-02-01'),(4,'WATTSGRP','WATTLA','',NULL,'','2003-03-05','WS',10,'922 Acton Lane','Reefston','Los Angeles','','235 446 998','gbiggles@watts.motors.com','Watts Group Los Angeles',0.00,'DEN','2003-03-06',0,'0000-00-00'),(5,'12345678','1234','',NULL,'','2003-03-27','WS',10,'1234 sdd','sdsddsd','sdsdds','','','','One Two Three Four Five Six Seven Eight',0.00,'DEN','2003-03-28',0,'0000-00-00'),(6,'WATTSGRP','WATT','',NULL,'','2003-05-19','WS',10,'81 Trimble Road','Gimlet Rock','Greater Hampton','','233 5442 3255','billg@watts.motors.com','Watts Motor Group Head Office and Admin',0.00,'DEN','2003-05-20',0,'0000-00-00'),(7,'12345678','1234','',NULL,' Inv 25','2003-06-12','WS',10,'145 Golf Road','Three Woods','Theakston','','','','One Two Three Four Five Six Seven Eight',0.00,'DEN','2003-06-13',0,'0000-00-00'),(8,'12345678','1234','',NULL,'','2003-06-12','WS',10,'Alfs Garage, 521 Columbus Drive','Geofftown','Minnesota','','','','One Two Three Four Five Six Seven Eight',0.00,'DEN','2003-06-13',1,'2003-06-12'),(9,'JOHNSON','JOHNTEMPE','',NULL,'','2003-06-15','WS',10,'654 Roberto Street','Tempe','Arizona','','56565 444 44','','Johnson Vehicles Tempe',0.00,'DEN','2003-06-16',1,'2003-06-15'),(10,'GRANHR','GRAN','',NULL,'','2003-06-22','EX',10,'2323 ssdfdf','dsdffd','dog','','','','Testy',0.00,'DEN','2003-06-23',1,'2003-06-22'),(11,'GRANHR','GRAN','',NULL,'','2003-06-22','EX',10,'45 002sd54ds45','44545','sadkdsakjl','','','','Guess who',0.00,'DEN','2003-06-23',0,'0000-00-00'),(12,'GRANHR','GRAN','',NULL,'','2003-06-22','EX',10,'123 Grsdooos street','Dufus','fgjejwk','','','','Grohee',0.00,'DEN','2003-06-23',0,'0000-00-00'),(13,'GRANHR','GRAN','',NULL,'','2003-06-22','EX',10,'2 Growler Terraces','West Hemington','BE22 FD2','','','','Gramtham Horton PLC',0.00,'MUC','2003-06-23',0,'0000-00-00'),(14,'GRANHR','GRAN','',NULL,'','2003-06-22','EX',10,'2 Growler Terraces','West Hemington','BE22 FD2','','','','Gramtham Horton PLC',0.00,'MUC','2003-06-23',0,'0000-00-00'),(15,'JOHNSON','JOHN','',NULL,'','2003-06-23','WS',10,'198 Greater Lofty Heights','San Diego','California','','2412 22356','rod.champion@johnson.com','San Diego',0.00,'DEN','2003-06-24',0,'0000-00-00'),(16,'12345678','1234','test customer ref 221222',NULL,'Testing the comment field thanks for the order','2003-07-05','WS',10,'22 Great North Road','Auckland','Auckland','','4544 4444','124@4454.com.ay','One Two Three Four Five Six Seven Eight',0.00,'MEL','2003-07-07',1,'2003-07-05'),(17,'WALMON','WALM','',NULL,'','2003-07-05','EX',10,'309 Centennial Highway','Ballmain, Sydney','New South Wales','Australia','+61 2 3546 8875','phyllis@WallMonterey.com.','Wallace Monterey - Sydney',0.00,'MEL','2003-07-07',1,'2003-07-05'),(18,'WATTSGRP','WATTSSAND','',NULL,'','2003-08-10','WS',1,'42 Grafton Place','San Diego','California','','355 224 566','','Watts Group San Diego',0.00,'DEN','2003-08-11',0,'0000-00-00'),(19,'WATTSGRP','WATTSSAND','',NULL,'','2003-09-11','WS',1,'42 Grafton Place','San Diego','California','','355 224 566','','Watts Group San Diego',0.00,'DEN','2003-09-12',1,'2003-09-11'),(20,'JOHNSON','JOHNTEMPE','',NULL,' Inv 24','2003-12-14','WS',1,'654 Roberto Street','Tempe','Arizona','USA','56565 444 44','','Johnson Vehicles Tempe',0.00,'DEN','2003-12-15',0,'0000-00-00'),(21,'WATTSGRP','WATT','',NULL,'','2004-01-14','WS',1,'81 Trimble Road','Gimlet Rock','Greater Hampton','Arizona','233 5442 3255','','Watts Motor Group Head Office and Admin',0.00,'MEL','2004-01-15',0,'0000-00-00'),(22,'WALMON','WALM','',NULL,'','2004-01-14','EX',1,'309 Centennial Highway','Ballmain, Sydney','New South Wales','Australia','+61 2 3546 8875','','Wallace Monterey - Sydney',0.00,'MEL','2004-01-15',0,'0000-00-00'),(23,'JOHNSON','JOHN','',NULL,'','2004-01-14','WS',8,'198 Greater Lofty Heights','San Diego','California','USA','2412 22356','','San Diego',0.00,'DEN','2004-01-15',0,'0000-00-00'),(24,'WATTSGRP','WATTSSAND','',NULL,'','2004-01-14','WS',1,'42 Grafton Place','San Diego','California','','355 224 566','','Watts Group San Diego',0.00,'DEN','2004-01-15',0,'0000-00-00'),(25,'JOHNSON','JOHNTEMPE','',NULL,' Inv 26','2004-01-14','WS',1,'654 Roberto Street','Tempe','Arizona','USA','56565 444 44','','Johnson Vehicles Tempe',0.00,'DEN','2004-01-15',0,'0000-00-00');
UNLOCK TABLES;
/*!40000 ALTER TABLE SalesOrders ENABLE KEYS */;

--
-- Dumping data for table `SalesTypes`
--


/*!40000 ALTER TABLE SalesTypes DISABLE KEYS */;
LOCK TABLES SalesTypes WRITE;
INSERT INTO SalesTypes VALUES ('EX','Export Islands'),('WS','Wholesale');
UNLOCK TABLES;
/*!40000 ALTER TABLE SalesTypes ENABLE KEYS */;

--
-- Dumping data for table `Salesman`
--


/*!40000 ALTER TABLE Salesman DISABLE KEYS */;
LOCK TABLES Salesman WRITE;
INSERT INTO Salesman VALUES ('2','Anthony Boland','+61 544 3540','+61 544 3512',0.2000,"0.0000",0.0000),('25','Maggie Scratcher','','',0.0000,"0.0000",0.0000);
UNLOCK TABLES;
/*!40000 ALTER TABLE Salesman ENABLE KEYS */;

--
-- Dumping data for table `ShipmentCharges`
--


/*!40000 ALTER TABLE ShipmentCharges DISABLE KEYS */;
LOCK TABLES ShipmentCharges WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE ShipmentCharges ENABLE KEYS */;

--
-- Dumping data for table `Shipments`
--


/*!40000 ALTER TABLE Shipments DISABLE KEYS */;
LOCK TABLES Shipments WRITE;
INSERT INTO Shipments VALUES (2,'0','Bunga Teriaki','2004-04-20 00:00:00',0.0000,'GREGCA',0);
UNLOCK TABLES;
/*!40000 ALTER TABLE Shipments ENABLE KEYS */;

--
-- Dumping data for table `Shippers`
--


/*!40000 ALTER TABLE Shippers DISABLE KEYS */;
LOCK TABLES Shippers WRITE;
INSERT INTO Shippers VALUES (1,'Courier Post',0.0000),(8,'Ansett',0.0000),(10,'Not Specified',0.0000);
UNLOCK TABLES;
/*!40000 ALTER TABLE Shippers ENABLE KEYS */;

--
-- Dumping data for table `StockCategory`
--


/*!40000 ALTER TABLE StockCategory DISABLE KEYS */;
LOCK TABLES StockCategory WRITE;
INSERT INTO StockCategory VALUES ('Coil','Coil Springs','F',722000,20000,23400,19000,721000),('SHOCKS','Shock Absorbers','F',722000,20000,23400,19000,721000),('Steel','Steel X4K','M',720000,20000,23400,19000,721000),('SUSASS','Suspension Assemblie','F',722000,23500,23400,19000,721000);
UNLOCK TABLES;
/*!40000 ALTER TABLE StockCategory ENABLE KEYS */;

--
-- Dumping data for table `StockCheckFreeze`
--


/*!40000 ALTER TABLE StockCheckFreeze DISABLE KEYS */;
LOCK TABLES StockCheckFreeze WRITE;
INSERT INTO StockCheckFreeze VALUES ('123','DEN',-4.4),('125','DEN',2525.5),('130','DEN',5873),('140','DEN',2872),('HON90021','DEN',8698.5),('HON90022','DEN',8239),('HONShock','DEN',-10);
UNLOCK TABLES;
/*!40000 ALTER TABLE StockCheckFreeze ENABLE KEYS */;

--
-- Dumping data for table `StockCounts`
--


/*!40000 ALTER TABLE StockCounts DISABLE KEYS */;
LOCK TABLES StockCounts WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE StockCounts ENABLE KEYS */;

--
-- Dumping data for table `StockMaster`
--


/*!40000 ALTER TABLE StockMaster DISABLE KEYS */;
LOCK TABLES StockMaster WRITE;
INSERT INTO StockMaster VALUES ('123','Steel','12.3mm Round Steel coil','X4k grade 12.3mm round steel in coils','Kgs','B','1800-01-01',"0.0000","0.0000","0.3000","0.0000","0.0000",0,0,0,14000.00,"0.0200","1.0000",'','',1),('125','Steel','12.5mm round steel','12.5mm diameter round steel X4K grade	','Kgs','B','1800-01-01',"0.0000","0.0000","0.6200","0.0000","0.0000",0,0,0,1200.00,"0.0210","1.0000",'','',1),('130','Steel','13mm round spring steel X4K grade','13mm round spring steel X4K grade	','Kgs','B','1800-01-01',"0.0000","0.0000","0.6200","0.0000","0.0000",0,0,0,1200.00,"0.0600","1.0000",'','',1),('140','Steel','14mm round steel X4k grade','14mm round steel X4k grade	','metres','B','1800-01-01',"0.0000","0.0000","0.6200","0.0000","0.0000",0,0,0,1250.00,"0.0200","1.0000",'','',1),('HON90021','Coil','Honda Civic front coil spring 1990-94','Honda Civic front coil spring 1990-94	','each','M','1800-01-01',"0.0000","0.0000","6.8900","1.6000","1.2000",0,0,0,50.00,"0.3000","3.0000",'','SP',1),('HON90022','Coil','Honda Civic rear coil spring 1990-94','Honda Civic rear coil spring 1990-94	','each','M','1800-01-01',"0.0000","0.0000","2.1000","0.0000","0.0000",0,0,0,50.00,"0.0000","3.0000",'','SP',1),('HON92FrSus','SUSASS','Honda 92 Front suspension assembly','Honda 92 Front suspension assembly','each','A','1800-01-01',"0.0000","0.0000","0.0000","0.0000","0.0000",0,0,0,0.00,"0.0000","0.0000",'','',1),('HONCIVIC92SET','Coil','Honda Civic 90-94 spring set','Honda Civic 90-94 spring set','each','K','1800-01-01',"0.0000","0.0000","0.0000","0.0000","0.0000",0,0,0,0.00,"0.3000","6.0000",'','',1),('HONShock','SHOCKS','Honda 92 Monroe Shock Absorber','Honda 92 Monroe Shock Absorber','each','B','1800-01-01',"0.0000","0.0000","8.5000","0.0000","0.0000",0,0,0,0.00,"0.0000","0.0000",'','',1);
UNLOCK TABLES;
/*!40000 ALTER TABLE StockMaster ENABLE KEYS */;

--
-- Dumping data for table `StockMoves`
--


/*!40000 ALTER TABLE StockMoves DISABLE KEYS */;
LOCK TABLES StockMoves WRITE;
INSERT INTO StockMoves VALUES (1,'HON90022',17,1,'DEN','1','2003-01-20','','',"0.0000",37,'initial stock balance',8554.0000,0.0000,0.0000,1,0,0,0),(2,'HON90021',17,2,'DEN','1','2003-01-20','','',"0.0000",37,'initial stock balance',8776.0000,0.0000,0.0000,1,0,0,0),(3,'HON90022',17,3,'MEL','1','2003-01-20','','',"0.0000",37,'initial stock balance',632.0000,0.0000,0.0000,1,0,0,0),(4,'HON90021',17,4,'MEL','1','2003-01-20','','',"0.0000",37,'initial stock balance',630.0000,0.0000,0.0000,1,0,0,0),(5,'HON90021',10,1,'DEN','1','2003-01-21','JOHNSON','JOHN',"21.9286",37,'1',-8.0000,0.0200,0.0000,1,0,0,0),(6,'125',25,1,'DEN','1','2003-01-20','','',"0.2826",37,'BHPSTEEL - 1',2430.0000,0.0000,0.0000,1,0,0,0),(7,'HON90022',10,2,'MEL','1','2003-01-21','WALMON','WALM',"24.5000",37,'2',-263.0000,0.0000,0.0000,1,0,0,0),(8,'130',25,2,'DEN','1','2003-01-23','','',"0.6744",37,'BIGBISC - 2',5853.0000,0.0000,0.0000,1,0,0,0),(9,'140',25,2,'DEN','1','2003-01-23','','',"0.8140",37,'BIGBISC - 2',2872.0000,0.0000,0.0000,1,0,0,0),(10,'HON90021',11,1,'DEN','1','2003-01-26','JOHNSON','JOHN',"21.9286",37,'Ex Inv - 1',2.0000,0.0200,0.0000,1,0,0,0),(11,'HON90021',10,3,'DEN','1','2003-02-24','JOHNSON','JOHN',"21.9286",38,'3',-52.0000,0.0000,9.6900,1,0,0,0),(12,'HON90021',10,4,'DEN','1','2003-03-06','WATTSGRP','WATTLA',"21.9286",39,'4',-6.0000,0.0000,9.6900,1,0,0,0),(13,'HON90022',10,4,'DEN','1','2003-03-06','WATTSGRP','WATTLA',"22.6571",39,'4',-5.0000,0.0000,0.0000,1,0,0,0),(14,'HON90021',10,5,'DEN','1','2003-03-28','12345678','1234',"21.9286",39,'5',-3.5000,0.0000,9.6900,1,0,0,0),(16,'HON90021',10,6,'DEN','1','2003-05-20','JOHNSON','JOHN',"21.9286",41,'1',-4.0000,0.0200,9.6900,1,0,0,0),(17,'123',10,7,'DEN','1','2003-06-13','12345678','1234',"4.5714",42,'7',-2.0000,0.0000,0.3000,1,0,0,0),(18,'HON90021',10,7,'DEN','1','2003-06-13','12345678','1234',"21.9286",42,'7',-2.0000,0.0000,9.6900,1,0,0,0),(19,'HON90022',10,7,'DEN','1','2003-06-13','12345678','1234',"22.6571",42,'7',-1.0000,0.0000,0.0000,1,0,0,0),(20,'HON90022',10,8,'DEN','1','2003-06-13','WATTSGRP','WATT',"22.6571",42,'6',-1.0000,0.0000,0.0000,1,0,0,0),(21,'123',10,9,'DEN','1','2003-06-13','12345678','1234',"4.5714",42,'8',-2.0000,0.0000,0.3000,1,0,0,0),(22,'HON90021',10,9,'DEN','1','2003-06-13','12345678','1234',"21.9286",42,'8',-2.0000,0.0000,9.6900,1,0,0,0),(23,'HON90022',10,9,'DEN','1','2003-06-13','12345678','1234',"22.6571",42,'8',-2.0000,0.0000,0.0000,1,0,0,0),(24,'HON90021',10,9,'DEN','1','2003-06-13','12345678','1234',"0.0000",42,'Assembly: HON92FrSus Order: 8',-10.0000,0.0000,9.6900,0,0,0,0),(25,'HONShock',10,9,'DEN','1','2003-06-13','12345678','1234',"0.0000",42,'Assembly: HON92FrSus Order: 8',-10.0000,0.0000,8.5000,0,0,0,0),(26,'HON92FrSus',10,9,'DEN','1','2003-06-13','12345678','1234',"76.0000",42,'8',-5.0000,0.0250,18.1900,1,0,0,0),(27,'HON90021',10,10,'DEN','1','2003-06-16','JOHNSON','JOHNTEMPE',"21.9286",42,'9',-1.0000,0.0000,9.6900,1,0,0,0),(28,'125',25,3,'DEN','1','2003-06-19','','',"2.7907",42,'BIGBISC - 6',30.5000,0.0000,0.0000,1,0,0,0),(29,'125',25,4,'DEN','1','2003-06-20','','',"2.7907",42,'BIGBISC - 6',65.0000,0.0000,0.6200,1,0,0,0),(30,'123',25,5,'DEN','1','2003-06-21','','',"1.5814",42,'GREGCA - 7',1.6000,0.0000,0.0000,1,0,0,0),(31,'HON90022',10,11,'DEN','1','2003-06-23','GRANHR','GRAN',"19.8143",42,'11',-5.0000,0.0000,0.0000,1,0,0,0),(32,'HON90021',10,12,'DEN','1','2003-06-23','GRANHR','GRAN',"18.0714",42,'10',-6.0000,0.0000,9.6900,1,0,0,0),(33,'123',10,13,'DEN','1','2003-06-23','GRANHR','GRAN',"4.2143",42,'12',-2.0000,0.0000,0.3000,1,0,0,0),(34,'HON90021',10,13,'DEN','1','2003-06-23','GRANHR','GRAN',"18.0714",42,'12',-2.0000,0.0000,9.6900,1,0,0,0),(35,'HON90022',10,13,'DEN','1','2003-06-23','GRANHR','GRAN',"19.8143",42,'12',-2.0000,0.0000,0.0000,1,0,0,0),(36,'HON90022',10,14,'MUC','1','2003-06-23','GRANHR','GRAN',"19.8143",42,'13',-56.0000,0.2500,0.0000,1,0,0,0),(37,'HON90022',10,15,'MUC','1','2003-06-23','GRANHR','GRAN',"19.8143",42,'14',-10.0000,0.0150,2.1000,1,0,0,0),(38,'HON90022',17,5,'DEN','1','2003-06-22','','',"0.0000",42,'stuff up',-12.0000,0.0000,0.0000,1,0,0,0),(39,'HON90022',16,1,'DEN','1','2003-06-19','','',"0.0000",42,'To MUC',-200.0000,0.0000,0.0000,1,0,0,0),(40,'HON90022',16,1,'MUC','1','2003-06-19','','',"0.0000",42,'From DEN',200.0000,0.0000,0.0000,1,0,0,0),(41,'HON90021',10,16,'DEN','1','2003-06-24','WATTSGRP','WATTLA',"21.9286",42,'4',-6.0000,0.0000,9.6900,1,0,0,0),(42,'HON90022',10,16,'DEN','1','2003-06-24','WATTSGRP','WATTLA',"22.6571",42,'4',-6.0000,0.0000,2.1000,1,0,0,0),(44,'HON90022',10,17,'DEN','1','2003-06-24','JOHNSON','JOHN',"22.6571",42,'15',-10.0000,0.1000,2.1000,1,0,0,0),(45,'HON90022',10,18,'DEN','1','2003-06-24','JOHNSON','JOHN',"22.6571",42,'15',-10.0000,0.1000,2.1000,1,0,0,0),(46,'HON90022',11,2,'DEN','1','0000-00-00','JOHNSON','JOHN',"22.6571",59,'Ex Inv - 17',3.0000,0.1000,2.1000,1,0,0,0),(47,'HON90022',11,3,'DEN','1','2003-06-29','WATTSGRP','WATT',"22.6571",42,'Ex Inv - 8',2.0000,0.0000,2.1000,1,0,0,0),(48,'HON90022',11,3,'DEN','1','2003-06-29','WATTSGRP','WATT',"22.6571",42,'Written off ex Inv - 8',-2.0000,0.0000,2.1000,0,0,0,0),(49,'HON90021',11,4,'MEL','1','2003-06-29','WALMON','WALM',"25.0000",65,'',6.0000,0.0200,9.6900,1,0,0,0),(50,'HON90022',11,4,'MEL','1','2003-06-29','WALMON','WALM',"24.5000",65,'',5.0000,0.0200,2.1000,1,0,0,0),(51,'HON90022',10,19,'DEN','1','2003-06-30','JOHNSON','JOHN',"22.6571",42,'15',-50.0000,0.1000,2.1000,1,0,0,0),(52,'HON90021',11,5,'DEN','1','2003-06-30','JOHNSON','JOHN',"21.9286",66,'Not required',23.0000,0.0000,9.6900,1,0,0,0),(53,'HON90022',10,20,'DEN','1','2003-07-07','JOHNSON','JOHN',"15.8600",43,'15',-15.0000,0.1000,2.1000,1,0,0,0),(54,'130',25,7,'DEN','1','2003-07-05','','',"2.7500",43,'BIGBISC (Biga Engineering PLC) - 8',20.0000,0.0000,0.0000,1,0,0,0),(55,'HON90022',11,6,'DEN','1','2003-07-05','WATTSGRP','WATTSSAND',"15.8600",41,'',3.0000,0.2000,2.1000,1,0,0,0),(56,'HON90022',10,21,'MEL','1','2003-07-08','12345678','1234',"15.8600",43,'16',-3.0000,0.0000,2.1000,1,0,0,0),(57,'HONShock',10,21,'MEL','1','2003-07-08','12345678','1234',"25.9000",43,'16',-5.0000,0.0250,8.5000,1,0,0,0),(58,'HON90022',10,22,'MEL','1','2003-07-08','12345678','1234',"15.8600",43,'16',-7.0000,0.0000,2.1000,1,0,0,0),(59,'HON90022',10,23,'MEL','1','2003-07-10','12345678','1234',"15.8600",43,'16',-3.0000,0.0000,2.1000,1,0,0,0),(60,'hon90022',11,7,'DEN','1','2003-08-10','WATTSGRP','WATTSSAND',"15.8600",46,'',4.0000,0.0000,2.1000,1,0,0,0),(61,'hon90022',11,7,'DEN','1','2003-08-10','WATTSGRP','WATTSSAND',"15.8600",46,'',-4.0000,0.0000,2.1000,0,0,0,0),(62,'123',10,24,'DEN','1','2003-12-15','JOHNSON','JOHNTEMPE',"3.2000",48,'20',-9.0000,0.0000,0.3000,1,-13.4,0,0),(63,'125',10,24,'DEN','1','2003-12-15','JOHNSON','JOHNTEMPE',"3.5000",48,'20',-10.0000,0.0000,0.6200,1,2515.5,0,0),(66,'125',11,8,'DEN','1','2003-12-14','JOHNSON','JOHN',"0.5000",48,'Overcharged 125 credit 0.50 per kg',10.0000,0.0000,0.6200,1,2515.5,1,0),(67,'123',11,9,'DEN','1','2003-12-14','JOHNSON','JOHNTEMPE',"0.2000",48,'',10.0000,0.0000,0.3000,1,-13.4,1,0),(68,'125',11,10,'DEN','1','2003-12-16','JOHNSON','JOHNTEMPE',"2.5000",48,'',10.0000,0.0000,0.6200,1,2515.5,1,0),(69,'HON90021',11,11,'DEN','1','2003-12-16','JOHNSON','JOHN',"0.3500",48,'',2.0000,0.0000,9.6900,1,8698.5,1,0),(70,'HON90022',10,25,'DEN','1','2004-01-15','12345678','1234',"15.8600",49,'7',-1.0000,0.0000,2.1000,1,8238,0,0),(71,'HONShock',10,26,'DEN','1','2004-01-15','JOHNSON','JOHNTEMPE',"25.9000",49,'25',-3.0000,0.0000,8.5000,1,-13,0,0),(72,'HON90022',16,2,'MEL','1','2004-02-23','','',"0.0000",50,'To DEN',-2.0000,0.0000,0.0000,1,355,0,0),(73,'HON90022',16,2,'DEN','1','2004-02-23','','',"0.0000",50,'From MEL',2.0000,0.0000,0.0000,1,8240,0,0),(74,'HON90022',16,3,'MUC','1','2004-02-23','','',"0.0000",50,'To MEL',-3.0000,0.0000,0.0000,1,131,0,0),(75,'HON90022',16,3,'MEL','1','2004-02-23','','',"0.0000",50,'From MUC',3.0000,0.0000,0.0000,1,358,0,0),(76,'HON90022',16,4,'MEL','1','2004-02-23','','',"0.0000",50,'To DEN',-43.0000,0.0000,0.0000,1,315,0,0),(77,'HON90022',16,4,'DEN','1','2004-02-23','','',"0.0000",50,'From MEL',43.0000,0.0000,0.0000,1,8283,0,0);
UNLOCK TABLES;
/*!40000 ALTER TABLE StockMoves ENABLE KEYS */;

--
-- Dumping data for table `SuppAllocs`
--


/*!40000 ALTER TABLE SuppAllocs DISABLE KEYS */;
LOCK TABLES SuppAllocs WRITE;
INSERT INTO SuppAllocs VALUES (2,12.00,'2003-01-31',7,6),(3,10.23,'2003-02-01',7,1);
UNLOCK TABLES;
/*!40000 ALTER TABLE SuppAllocs ENABLE KEYS */;

--
-- Dumping data for table `SuppTrans`
--


/*!40000 ALTER TABLE SuppTrans DISABLE KEYS */;
LOCK TABLES SuppTrans WRITE;
INSERT INTO SuppTrans VALUES (1,20,'BIGBISC','655745','2003-01-19','2003-03-02',0,0.430000,58.6500,5.8650,0.0000,10.2300,'',0,1),(2,20,'BHPSTEEL','654423','2003-01-19','2003-03-02',0,1.250000,1713.0000,171.3000,10.0840,250.0000,'',0,5),(3,20,'BIGBISC','6455','2003-01-23','2003-03-02',0,0.430000,1126.4800,112.6480,0.0000,12.0000,'part shipt of order ',0,6),(1,21,'BIGBISC','Z332','2003-01-23','2003-02-27',0,0.430000,-44.9640,-4.4964,0.0000,-22.2300,'',0,7),(2,21,'BIGBISC','5442','2003-01-23','2003-02-27',0,0.430000,-26.7500,-2.6750,0.0000,0.0000,'Return of samples',0,8),(4,20,'BIGBISC','66544','2003-01-31','2003-03-30',0,0.430000,455.2100,45.5210,0.0000,0.0000,'Testing supplier invoice entry',0,10),(2,22,'BIGBISC','Direct Credit','2003-02-01','0000-00-00',0,0.490000,-350.0000,0.0000,0.0000,0.0000,'Payment test to Biga Engineering PLC in GBP @ .49',0,11),(5,20,'BIGBISC','52422','2003-06-19','2003-07-30',0,0.430000,35.0750,3.5075,0.0000,0.0000,'',0,12),(3,21,'BIGBISC','45442','2003-06-19','2003-09-07',0,0.430000,-6.0700,-0.6070,0.0000,0.0000,'',0,13),(6,20,'BIGBISC','87754','2003-06-19','2003-07-30',0,0.422000,70.5000,4.1500,0.0000,0.0000,'',0,14),(7,20,'BIGBISC','32112','2003-06-20','2003-07-30',0,0.430000,110.7000,11.0700,0.0000,0.0000,'',0,15),(8,20,'GREGCA','23231122','2003-06-20','2003-07-30',0,0.430000,55.1724,5.5172,0.0000,0.0000,'',0,16),(9,20,'FREDBLOW','1221223423','2003-06-28','2003-07-22',0,0.650000,12.0000,1.2000,0.0000,0.0000,'',0,17),(3,22,'GREGCA','Cheque','2003-08-07','0000-00-00',0,0.800000,-14.0000,0.0000,0.0000,0.0000,'2122233',0,22);
UNLOCK TABLES;
/*!40000 ALTER TABLE SuppTrans ENABLE KEYS */;

--
-- Dumping data for table `SupplierContacts`
--


/*!40000 ALTER TABLE SupplierContacts DISABLE KEYS */;
LOCK TABLES SupplierContacts WRITE;
INSERT INTO SupplierContacts VALUES ('BHPSTEEL','Test supplier contact','','','','','phil@localhost',0),('BIGBISC','Eric Howard','Sales Director','','','','eric_howard@big_engineer.co.uk',0);
UNLOCK TABLES;
/*!40000 ALTER TABLE SupplierContacts ENABLE KEYS */;

--
-- Dumping data for table `Suppliers`
--


/*!40000 ALTER TABLE Suppliers DISABLE KEYS */;
LOCK TABLES Suppliers WRITE;
INSERT INTO Suppliers VALUES ('BHPSTEEL','Bright Helpful People Steel Co Gmbh','P O Box 67','Frankfurt','Germany','','DEM','1960-03-17','30',500.0000,'2003-02-01 00:00:00','','0','',0,1),('BIGBISC','Biga Engineering PLC','P O Box 8918','Brighton','West Sussex BGF 11G','England','GBP','2000-06-08','30',350.0000,'2003-02-01 00:00:00','','0','',0,1),('FREDBLOW','Frederick Blowers & Co Inc','123 Road Close','Metropolis','','','USD','2003-06-29','20',0.0000,NULL,'','0','',0,1),('GREGCA','Gregor Capel Inc','P O Box 54','633 Groper Lane','Newmarket','England','GBP','1953-12-06','30',12.0000,'2003-08-07 00:00:00','','0','',0,1),('MITOOI2','Mitsooy Corporation Inc','P O Box 8775','Baltimore','USA','','USD','1998-04-12','30',0.0000,NULL,'','0','',0,1);
UNLOCK TABLES;
/*!40000 ALTER TABLE Suppliers ENABLE KEYS */;

--
-- Dumping data for table `SysTypes`
--


/*!40000 ALTER TABLE SysTypes DISABLE KEYS */;
LOCK TABLES SysTypes WRITE;
INSERT INTO SysTypes VALUES (0,'Journal - GL',9),(1,'Payment - GL',17),(2,'Receipt - GL',3),(3,'Standing Journal',0),(10,'Sales Invoice',26),(11,'Credit Note',11),(12,'Receipt',18),(15,'Journal - Debtors',0),(16,'Location Transfer',4),(17,'Stock Adjustment',5),(18,'Purchase Order',0),(20,'Purchase Invoice',13),(21,'Debit Note',3),(22,'Creditors Payment',3),(23,'Creditors Journal',0),(25,'Purchase Order Delivery',7),(26,'Work Order Receipt',0),(28,'Work Order Issue',0),(29,'Work Order Variance',0),(30,'Sales Order',0),(31,'Shipment Close',2),(35,'Cost Update',3),(50,'Opening Balance',0);
UNLOCK TABLES;
/*!40000 ALTER TABLE SysTypes ENABLE KEYS */;

--
-- Dumping data for table `TaxAuthLevels`
--


/*!40000 ALTER TABLE TaxAuthLevels DISABLE KEYS */;
LOCK TABLES TaxAuthLevels WRITE;
INSERT INTO TaxAuthLevels VALUES (1,1,1,0.1),(1,1,2,0),(1,5,1,0),(1,5,2,0),(1,6,1,0),(1,6,2,0),(5,1,1,0),(5,1,2,0),(5,5,1,0),(5,5,2,0),(5,6,1,0),(5,6,2,0),(6,1,1,0),(6,1,2,0),(6,5,1,0),(6,5,2,0),(6,6,1,0.175),(6,6,2,0);
UNLOCK TABLES;
/*!40000 ALTER TABLE TaxAuthLevels ENABLE KEYS */;

--
-- Dumping data for table `TaxAuthorities`
--


/*!40000 ALTER TABLE TaxAuthorities DISABLE KEYS */;
LOCK TABLES TaxAuthorities WRITE;
INSERT INTO TaxAuthorities VALUES (1,'Australian GST',810000,890000),(5,'Sales Tax',890000,890000),(6,'UK VAT',890000,890000);
UNLOCK TABLES;
/*!40000 ALTER TABLE TaxAuthorities ENABLE KEYS */;

--
-- Dumping data for table `WOIssues`
--


/*!40000 ALTER TABLE WOIssues DISABLE KEYS */;
LOCK TABLES WOIssues WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE WOIssues ENABLE KEYS */;

--
-- Dumping data for table `WORequirements`
--


/*!40000 ALTER TABLE WORequirements DISABLE KEYS */;
LOCK TABLES WORequirements WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE WORequirements ENABLE KEYS */;

--
-- Dumping data for table `WWW_Users`
--


/*!40000 ALTER TABLE WWW_Users DISABLE KEYS */;
LOCK TABLES WWW_Users WRITE;
INSERT INTO WWW_Users VALUES ('Admin','albundy','Phil Daintree','','','','DEN',7,'2004-03-12 21:55:21','','A4','1,1,1,1,1,1,1,1,',0),('demo','weberp','','','','','DEN',5,'2004-03-26 21:52:32','','A4','1,1,1,1,1,1,1,1,',0),('testy','weberp','','GRANHR','','','DEN',6,'2004-02-23 20:21:56','GRAN','A4','1,0,0,0,0,0,0,0,',0);
UNLOCK TABLES;
/*!40000 ALTER TABLE WWW_Users ENABLE KEYS */;

--
-- Dumping data for table `WorkCentres`
--


/*!40000 ALTER TABLE WorkCentres DISABLE KEYS */;
LOCK TABLES WorkCentres WRITE;
INSERT INTO WorkCentres VALUES ('ASSEM','DEN','Assembly',1.0000,"20.0000",560000,"0.0000");
UNLOCK TABLES;
/*!40000 ALTER TABLE WorkCentres ENABLE KEYS */;

--
-- Dumping data for table `WorksOrders`
--


/*!40000 ALTER TABLE WorksOrders DISABLE KEYS */;
LOCK TABLES WorksOrders WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE WorksOrders ENABLE KEYS */;

SET FOREIGN_KEY_CHECKS = 1;

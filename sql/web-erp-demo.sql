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
-- Table structure for table `Help`
--

DROP TABLE IF EXISTS Help;
CREATE TABLE Help (
  ID int(11) NOT NULL auto_increment,
  PageID smallint(6) NOT NULL default '0',
  Narrative text NOT NULL,
  HelpType char(1) NOT NULL default 'U',
  PRIMARY KEY  (ID),
  KEY PageID (PageID),
  KEY HelpType (HelpType)
) TYPE=MyISAM;

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
-- Table structure for table `LocTransfers`
--

DROP TABLE IF EXISTS LocTransfers;
CREATE TABLE LocTransfers (
  Reference int(11) NOT NULL default '0',
  StockID varchar(20) NOT NULL default '',
  ShipQty int(11) NOT NULL default '0',
  RecQty int(11) NOT NULL default '0',
  ShipDate date NOT NULL default '0000-00-00',
  RecDate date NOT NULL default '0000-00-00',
  ShipLoc varchar(7) NOT NULL default '',
  RecLoc varchar(7) NOT NULL default '',
  KEY Reference (Reference,StockID),
  KEY ShipLoc (ShipLoc),
  KEY RecLoc (RecLoc),
  KEY StockID (StockID),
  CONSTRAINT `LocTransfers_ibfk_1` FOREIGN KEY (`ShipLoc`) REFERENCES `Locations` (`LocCode`),
  CONSTRAINT `LocTransfers_ibfk_2` FOREIGN KEY (`RecLoc`) REFERENCES `Locations` (`LocCode`),
  CONSTRAINT `LocTransfers_ibfk_3` FOREIGN KEY (`StockID`) REFERENCES `StockMaster` (`StockID`)
) TYPE=InnoDB COMMENT='Stores Shipments To And From Locations';

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
-- Table structure for table `Scripts`
--

DROP TABLE IF EXISTS Scripts;
CREATE TABLE Scripts (
  PageID smallint(4) NOT NULL default '0',
  FileName varchar(50) NOT NULL default '',
  PageDescription text NOT NULL,
  PRIMARY KEY  (PageID),
  KEY FileName (FileName)
) TYPE=InnoDB COMMENT='Index of all scripts';

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
  CONSTRAINT `ShipmentCharges_ibfk_2` FOREIGN KEY (`TransType`) REFERENCES `SysTypes` (`TypeID`)
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
  Serialised tinyint(4) NOT NULL default '0',
  DecimalPlaces tinyint(4) NOT NULL default '0',
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
-- Table structure for table `StockSerialItems`
--

DROP TABLE IF EXISTS StockSerialItems;
CREATE TABLE StockSerialItems (
  StockID varchar(20) NOT NULL default '',
  LocCode varchar(5) NOT NULL default '',
  SerialNo varchar(30) NOT NULL default '',
  Quantity float NOT NULL default '0',
  PRIMARY KEY  (StockID,SerialNo,LocCode),
  KEY StockID (StockID),
  KEY LocCode (LocCode),
  CONSTRAINT `StockSerialItems_ibfk_1` FOREIGN KEY (`StockID`) REFERENCES `StockMaster` (`StockID`),
  CONSTRAINT `StockSerialItems_ibfk_2` FOREIGN KEY (`LocCode`) REFERENCES `Locations` (`LocCode`)
) TYPE=InnoDB;

--
-- Table structure for table `StockSerialMoves`
--

DROP TABLE IF EXISTS StockSerialMoves;
CREATE TABLE StockSerialMoves (
  StkItmMoveNo int(11) NOT NULL auto_increment,
  StockMoveNo int(11) NOT NULL default '0',
  StockID varchar(20) NOT NULL default '',
  SerialNo varchar(30) NOT NULL default '',
  MoveQty float NOT NULL default '0',
  PRIMARY KEY  (StkItmMoveNo),
  KEY StockMoveNo (StockMoveNo),
  KEY StockID_SN (StockID,SerialNo),
  CONSTRAINT `StockSerialMoves_ibfk_1` FOREIGN KEY (`StockMoveNo`) REFERENCES `StockMoves` (`StkMoveNo`),
  CONSTRAINT `StockSerialMoves_ibfk_2` FOREIGN KEY (`StockID`, `SerialNo`) REFERENCES `StockSerialItems` (`StockID`, `SerialNo`)
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
INSERT INTO BOM VALUES ('HON90021','127','ASSEM','DEN','2004-05-24','2034-05-25',1.6500),('HON90022','123','ASSEM','DEN','2003-01-29','9999-12-31',4.3000),('HON92FrSus','HON90021','ASSEM','DEN','2003-06-10','2037-12-31',2.0000),('HON92FrSus','HONShock','ASSEM','DEN','2003-06-10','2037-12-31',2.0000),('HONCIVIC92SET','123','ASSEM','DEN','2003-06-10','2037-12-31',2.0000),('HONCIVIC92SET','HON90021','ASSEM','DEN','1990-01-31','9999-12-31',2.0000),('HONCIVIC92SET','HON90022','ASSEM','DEN','1990-01-01','2037-12-31',2.0000);
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
INSERT INTO BankTrans VALUES (39,12,1,700100,'',0,2.354,'2004-07-17','Direct Credit',17.75,'GBP');
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
INSERT INTO ChartDetails VALUES (1,52,0,0,0,0),(1,53,0,0,0,0),(1,54,0,0,0,0),(1,55,0,0,0,0),(1,56,0,0,0,0),(1,57,0,0,0,0),(1,58,0,0,0,0),(1,59,0,0,0,0),(1,60,0,0,0,0),(1,61,0,0,0,0),(1,62,0,0,0,0),(1,63,0,0,0,0),(1,64,0,0,0,0),(1,65,0,0,0,0),(1,66,0,0,0,0),(1,67,0,0,0,0),(1,68,0,0,0,0),(100,52,0,0,0,0),(100,53,0,0,0,0),(100,54,0,0,0,0),(100,55,0,0,0,0),(100,56,0,0,0,0),(100,57,0,0,0,0),(100,58,0,0,0,0),(100,59,0,0,0,0),(100,60,0,0,0,0),(100,61,0,0,0,0),(100,62,0,0,0,0),(100,63,0,0,0,0),(100,64,0,0,0,0),(100,65,0,0,0,0),(100,66,0,0,0,0),(100,67,0,0,0,0),(100,68,0,0,0,0),(107,52,0,0,0,0),(107,53,0,0,0,0),(107,54,0,0,0,0),(107,55,0,0,0,0),(107,56,0,0,0,0),(107,57,0,0,0,0),(107,58,0,0,0,0),(107,59,0,0,0,0),(107,60,0,0,0,0),(107,61,0,0,0,0),(107,62,0,0,0,0),(107,63,0,0,0,0),(107,64,0,0,0,0),(107,65,0,0,0,0),(107,66,0,0,0,0),(107,67,0,0,0,0),(107,68,0,0,0,0),(112,52,0,0,0,0),(112,53,0,0,0,0),(112,54,0,0,0,0),(112,55,0,0,0,0),(112,56,0,0,0,0),(112,57,0,0,0,0),(112,58,0,0,0,0),(112,59,0,0,0,0),(112,60,0,0,0,0),(112,61,0,0,0,0),(112,62,0,0,0,0),(112,63,0,0,0,0),(112,64,0,0,0,0),(112,65,0,0,0,0),(112,66,0,0,0,0),(112,67,0,0,0,0),(112,68,0,0,0,0),(200,52,0,0,0,0),(200,53,0,0,0,0),(200,54,0,0,0,0),(200,55,0,0,0,0),(200,56,0,0,0,0),(200,57,0,0,0,0),(200,58,0,0,0,0),(200,59,0,0,0,0),(200,60,0,0,0,0),(200,61,0,0,0,0),(200,62,0,0,0,0),(200,63,0,0,0,0),(200,64,0,0,0,0),(200,65,0,0,0,0),(200,66,0,0,0,0),(200,67,0,0,0,0),(200,68,0,0,0,0),(301,52,0,0,0,0),(301,53,0,0,0,0),(301,54,0,0,0,0),(301,55,0,0,0,0),(301,56,0,0,0,0),(301,57,0,0,0,0),(301,58,0,0,0,0),(301,59,0,0,0,0),(301,60,0,0,0,0),(301,61,0,0,0,0),(301,62,0,0,0,0),(301,63,0,0,0,0),(301,64,0,0,0,0),(301,65,0,0,0,0),(301,66,0,0,0,0),(301,67,0,0,0,0),(301,68,0,0,0,0),(10000,52,0,0,0,0),(10000,53,0,0,0,0),(10000,54,0,0,0,0),(10000,55,0,0,0,0),(10000,56,0,0,0,0),(10000,57,0,0,0,0),(10000,58,0,0,0,0),(10000,59,0,0,0,0),(10000,60,0,0,0,0),(10000,61,0,0,0,0),(10000,62,0,0,0,0),(10000,63,0,0,0,0),(10000,64,0,0,0,0),(10000,65,0,0,0,0),(10000,66,0,0,0,0),(10000,67,0,0,0,0),(10000,68,0,0,0,0),(11000,52,0,0,0,0),(11000,53,0,0,0,0),(11000,54,0,0,0,0),(11000,55,0,0,0,0),(11000,56,0,0,0,0),(11000,57,0,0,0,0),(11000,58,0,0,0,0),(11000,59,0,0,0,0),(11000,60,0,0,0,0),(11000,61,0,0,0,0),(11000,62,0,0,0,0),(11000,63,0,0,0,0),(11000,64,0,0,0,0),(11000,65,0,0,0,0),(11000,66,0,0,0,0),(11000,67,0,0,0,0),(11000,68,0,0,0,0),(12000,52,0,0,0,0),(12000,53,0,0,0,0),(12000,54,0,0,0,0),(12000,55,0,0,0,0),(12000,56,0,0,0,0),(12000,57,0,0,0,0),(12000,58,0,0,0,0),(12000,59,0,0,0,0),(12000,60,0,0,0,0),(12000,61,0,0,0,0),(12000,62,0,0,0,0),(12000,63,0,0,0,0),(12000,64,0,0,0,0),(12000,65,0,0,0,0),(12000,66,0,0,0,0),(12000,67,0,0,0,0),(12000,68,0,0,0,0),(19000,52,0,0,0,0),(19000,53,0,0,0,0),(19000,54,0,0,0,0),(19000,55,0,0,0,0),(19000,56,0,0,0,0),(19000,57,0,0,0,0),(19000,58,0,0,0,0),(19000,59,0,0,0,0),(19000,60,0,0,0,0),(19000,61,0,0,0,0),(19000,62,0,0,0,0),(19000,63,0,0,0,0),(19000,64,0,0,0,0),(19000,65,0,0,0,0),(19000,66,0,0,0,0),(19000,67,0,0,0,0),(19000,68,0,0,0,0),(20000,52,0,0,0,0),(20000,53,0,0,0,0),(20000,54,0,0,0,0),(20000,55,0,0,0,0),(20000,56,0,0,0,0),(20000,57,0,0,0,0),(20000,58,0,0,0,0),(20000,59,0,0,0,0),(20000,60,0,0,0,0),(20000,61,0,0,0,0),(20000,62,0,0,0,0),(20000,63,0,0,0,0),(20000,64,0,0,0,0),(20000,65,0,0,0,0),(20000,66,0,0,0,0),(20000,67,0,0,0,0),(20000,68,0,0,0,0),(21000,52,0,0,0,0),(21000,53,0,0,0,0),(21000,54,0,0,0,0),(21000,55,0,0,0,0),(21000,56,0,0,0,0),(21000,57,0,0,0,0),(21000,58,0,0,0,0),(21000,59,0,0,0,0),(21000,60,0,0,0,0),(21000,61,0,0,0,0),(21000,62,0,0,0,0),(21000,63,0,0,0,0),(21000,64,0,0,0,0),(21000,65,0,0,0,0),(21000,66,0,0,0,0),(21000,67,0,0,0,0),(21000,68,0,0,0,0),(23400,52,0,0,0,0),(23400,53,0,0,0,0),(23400,54,0,0,0,0),(23400,55,0,0,0,0),(23400,56,0,0,0,0),(23400,57,0,0,0,0),(23400,58,0,0,0,0),(23400,59,0,0,0,0),(23400,60,0,0,0,0),(23400,61,0,0,0,0),(23400,62,0,0,0,0),(23400,63,0,0,0,0),(23400,64,0,0,0,0),(23400,65,0,0,0,0),(23400,66,0,0,0,0),(23400,67,0,0,0,0),(23400,68,0,0,0,0),(23500,52,0,0,0,0),(23500,53,0,0,0,0),(23500,54,0,0,0,0),(23500,55,0,0,0,0),(23500,56,0,0,0,0),(23500,57,0,0,0,0),(23500,58,0,0,0,0),(23500,59,0,0,0,0),(23500,60,0,0,0,0),(23500,61,0,0,0,0),(23500,62,0,0,0,0),(23500,63,0,0,0,0),(23500,64,0,0,0,0),(23500,65,0,0,0,0),(23500,66,0,0,0,0),(23500,67,0,0,0,0),(23500,68,0,0,0,0),(23600,52,0,0,0,0),(23600,53,0,0,0,0),(23600,54,0,0,0,0),(23600,55,0,0,0,0),(23600,56,0,0,0,0),(23600,57,0,0,0,0),(23600,58,0,0,0,0),(23600,59,0,0,0,0),(23600,60,0,0,0,0),(23600,61,0,0,0,0),(23600,62,0,0,0,0),(23600,63,0,0,0,0),(23600,64,0,0,0,0),(23600,65,0,0,0,0),(23600,66,0,0,0,0),(23600,67,0,0,0,0),(23600,68,0,0,0,0),(23700,52,0,0,0,0),(23700,53,0,0,0,0),(23700,54,0,0,0,0),(23700,55,0,0,0,0),(23700,56,0,0,0,0),(23700,57,0,0,0,0),(23700,58,0,0,0,0),(23700,59,0,0,0,0),(23700,60,0,0,0,0),(23700,61,0,0,0,0),(23700,62,0,0,0,0),(23700,63,0,0,0,0),(23700,64,0,0,0,0),(23700,65,0,0,0,0),(23700,66,0,0,0,0),(23700,67,0,0,0,0),(23700,68,0,0,0,0),(24000,52,0,0,0,0),(24000,53,0,0,0,0),(24000,54,0,0,0,0),(24000,55,0,0,0,0),(24000,56,0,0,0,0),(24000,57,0,0,0,0),(24000,58,0,0,0,0),(24000,59,0,0,0,0),(24000,60,0,0,0,0),(24000,61,0,0,0,0),(24000,62,0,0,0,0),(24000,63,0,0,0,0),(24000,64,0,0,0,0),(24000,65,0,0,0,0),(24000,66,0,0,0,0),(24000,67,0,0,0,0),(24000,68,0,0,0,0),(70100,52,0,0,0,0),(70100,53,0,0,0,0),(70100,54,0,0,0,0),(70100,55,0,0,0,0),(70100,56,0,0,0,0),(70100,57,0,0,0,0),(70100,58,0,0,0,0),(70100,59,0,0,0,0),(70100,60,0,0,0,0),(70100,61,0,0,0,0),(70100,62,0,0,0,0),(70100,63,0,0,0,0),(70100,64,0,0,0,0),(70100,65,0,0,0,0),(70100,66,0,0,0,0),(70100,67,0,0,0,0),(70100,68,0,0,0,0),(70200,52,0,0,0,0),(70200,53,0,0,0,0),(70200,54,0,0,0,0),(70200,55,0,0,0,0),(70200,56,0,0,0,0),(70200,57,0,0,0,0),(70200,58,0,0,0,0),(70200,59,0,0,0,0),(70200,60,0,0,0,0),(70200,61,0,0,0,0),(70200,62,0,0,0,0),(70200,63,0,0,0,0),(70200,64,0,0,0,0),(70200,65,0,0,0,0),(70200,66,0,0,0,0),(70200,67,0,0,0,0),(70200,68,0,0,0,0),(70300,52,0,0,0,0),(70300,53,0,0,0,0),(70300,54,0,0,0,0),(70300,55,0,0,0,0),(70300,56,0,0,0,0),(70300,57,0,0,0,0),(70300,58,0,0,0,0),(70300,59,0,0,0,0),(70300,60,0,0,0,0),(70300,61,0,0,0,0),(70300,62,0,0,0,0),(70300,63,0,0,0,0),(70300,64,0,0,0,0),(70300,65,0,0,0,0),(70300,66,0,0,0,0),(70300,67,0,0,0,0),(70300,68,0,0,0,0),(210000,52,0,0,0,0),(210000,53,0,0,0,0),(210000,54,0,0,0,0),(210000,55,0,0,0,0),(210000,56,0,0,0,0),(210000,57,0,0,0,0),(210000,58,0,0,0,0),(210000,59,0,0,0,0),(210000,60,0,0,0,0),(210000,61,0,0,0,0),(210000,62,0,0,0,0),(210000,63,0,0,0,0),(210000,64,0,0,0,0),(210000,65,0,0,0,0),(210000,66,0,0,0,0),(210000,67,0,0,0,0),(210000,68,0,0,0,0),(250000,52,0,0,0,0),(250000,53,0,0,0,0),(250000,54,0,0,0,0),(250000,55,0,0,0,0),(250000,56,0,0,0,0),(250000,57,0,0,0,0),(250000,58,0,0,0,0),(250000,59,0,0,0,0),(250000,60,0,0,0,0),(250000,61,0,0,0,0),(250000,62,0,0,0,0),(250000,63,0,0,0,0),(250000,64,0,0,0,0),(250000,65,0,0,0,0),(250000,66,0,0,0,0),(250000,67,0,0,0,0),(250000,68,0,0,0,0),(251000,52,0,0,0,0),(251000,53,0,0,0,0),(251000,54,0,0,0,0),(251000,55,0,0,0,0),(251000,56,0,0,0,0),(251000,57,0,0,0,0),(251000,58,0,0,0,0),(251000,59,0,0,0,0),(251000,60,0,0,0,0),(251000,61,0,0,0,0),(251000,62,0,0,0,0),(251000,63,0,0,0,0),(251000,64,0,0,0,0),(251000,65,0,0,0,0),(251000,66,0,0,0,0),(251000,67,0,0,0,0),(251000,68,0,0,0,0),(252000,52,0,0,0,0),(252000,53,0,0,0,0),(252000,54,0,0,0,0),(252000,55,0,0,0,0),(252000,56,0,0,0,0),(252000,57,0,0,0,0),(252000,58,0,0,0,0),(252000,59,0,0,0,0),(252000,60,0,0,0,0),(252000,61,0,0,0,0),(252000,62,0,0,0,0),(252000,63,0,0,0,0),(252000,64,0,0,0,0),(252000,65,0,0,0,0),(252000,66,0,0,0,0),(252000,67,0,0,0,0),(252000,68,0,0,0,0),(253000,52,0,0,0,0),(253000,53,0,0,0,0),(253000,54,0,0,0,0),(253000,55,0,0,0,0),(253000,56,0,0,0,0),(253000,57,0,0,0,0),(253000,58,0,0,0,0),(253000,59,0,0,0,0),(253000,60,0,0,0,0),(253000,61,0,0,0,0),(253000,62,0,0,0,0),(253000,63,0,0,0,0),(253000,64,0,0,0,0),(253000,65,0,0,0,0),(253000,66,0,0,0,0),(253000,67,0,0,0,0),(253000,68,0,0,0,0),(255000,52,0,0,0,0),(255000,53,0,0,0,0),(255000,54,0,0,0,0),(255000,55,0,0,0,0),(255000,56,0,0,0,0),(255000,57,0,0,0,0),(255000,58,0,0,0,0),(255000,59,0,0,0,0),(255000,60,0,0,0,0),(255000,61,0,0,0,0),(255000,62,0,0,0,0),(255000,63,0,0,0,0),(255000,64,0,0,0,0),(255000,65,0,0,0,0),(255000,66,0,0,0,0),(255000,67,0,0,0,0),(255000,68,0,0,0,0),(256000,52,0,0,0,0),(256000,53,0,0,0,0),(256000,54,0,0,0,0),(256000,55,0,0,0,0),(256000,56,0,0,0,0),(256000,57,0,0,0,0),(256000,58,0,0,0,0),(256000,59,0,0,0,0),(256000,60,0,0,0,0),(256000,61,0,0,0,0),(256000,62,0,0,0,0),(256000,63,0,0,0,0),(256000,64,0,0,0,0),(256000,65,0,0,0,0),(256000,66,0,0,0,0),(256000,67,0,0,0,0),(256000,68,0,0,0,0),(257000,52,0,0,0,0),(257000,53,0,0,0,0),(257000,54,0,0,0,0),(257000,55,0,0,0,0),(257000,56,0,0,0,0),(257000,57,0,0,0,0),(257000,58,0,0,0,0),(257000,59,0,0,0,0),(257000,60,0,0,0,0),(257000,61,0,0,0,0),(257000,62,0,0,0,0),(257000,63,0,0,0,0),(257000,64,0,0,0,0),(257000,65,0,0,0,0),(257000,66,0,0,0,0),(257000,67,0,0,0,0),(257000,68,0,0,0,0),(258000,52,0,0,0,0),(258000,53,0,0,0,0),(258000,54,0,0,0,0),(258000,55,0,0,0,0),(258000,56,0,0,0,0),(258000,57,0,0,0,0),(258000,58,0,0,0,0),(258000,59,0,0,0,0),(258000,60,0,0,0,0),(258000,61,0,0,0,0),(258000,62,0,0,0,0),(258000,63,0,0,0,0),(258000,64,0,0,0,0),(258000,65,0,0,0,0),(258000,66,0,0,0,0),(258000,67,0,0,0,0),(258000,68,0,0,0,0),(259000,52,0,0,0,0),(259000,53,0,0,0,0),(259000,54,0,0,0,0),(259000,55,0,0,0,0),(259000,56,0,0,0,0),(259000,57,0,0,0,0),(259000,58,0,0,0,0),(259000,59,0,0,0,0),(259000,60,0,0,0,0),(259000,61,0,0,0,0),(259000,62,0,0,0,0),(259000,63,0,0,0,0),(259000,64,0,0,0,0),(259000,65,0,0,0,0),(259000,66,0,0,0,0),(259000,67,0,0,0,0),(259000,68,0,0,0,0),(261000,52,0,0,0,0),(261000,53,0,0,0,0),(261000,54,0,0,0,0),(261000,55,0,0,0,0),(261000,56,0,0,0,0),(261000,57,0,0,0,0),(261000,58,0,0,0,0),(261000,59,0,0,0,0),(261000,60,0,0,0,0),(261000,61,0,0,0,0),(261000,62,0,0,0,0),(261000,63,0,0,0,0),(261000,64,0,0,0,0),(261000,65,0,0,0,0),(261000,66,0,0,0,0),(261000,67,0,0,0,0),(261000,68,0,0,0,0),(262000,52,0,0,0,0),(262000,53,0,0,0,0),(262000,54,0,0,0,0),(262000,55,0,0,0,0),(262000,56,0,0,0,0),(262000,57,0,0,0,0),(262000,58,0,0,0,0),(262000,59,0,0,0,0),(262000,60,0,0,0,0),(262000,61,0,0,0,0),(262000,62,0,0,0,0),(262000,63,0,0,0,0),(262000,64,0,0,0,0),(262000,65,0,0,0,0),(262000,66,0,0,0,0),(262000,67,0,0,0,0),(262000,68,0,0,0,0),(263000,52,0,0,0,0),(263000,53,0,0,0,0),(263000,54,0,0,0,0),(263000,55,0,0,0,0),(263000,56,0,0,0,0),(263000,57,0,0,0,0),(263000,58,0,0,0,0),(263000,59,0,0,0,0),(263000,60,0,0,0,0),(263000,61,0,0,0,0),(263000,62,0,0,0,0),(263000,63,0,0,0,0),(263000,64,0,0,0,0),(263000,65,0,0,0,0),(263000,66,0,0,0,0),(263000,67,0,0,0,0),(263000,68,0,0,0,0),(263100,52,0,0,0,0),(263100,53,0,0,0,0),(263100,54,0,0,0,0),(263100,55,0,0,0,0),(263100,56,0,0,0,0),(263100,57,0,0,0,0),(263100,58,0,0,0,0),(263100,59,0,0,0,0),(263100,60,0,0,0,0),(263100,61,0,0,0,0),(263100,62,0,0,0,0),(263100,63,0,0,0,0),(263100,64,0,0,0,0),(263100,65,0,0,0,0),(263100,66,0,0,0,0),(263100,67,0,0,0,0),(263100,68,0,0,0,0),(264000,52,0,0,0,0),(264000,53,0,0,0,0),(264000,54,0,0,0,0),(264000,55,0,0,0,0),(264000,56,0,0,0,0),(264000,57,0,0,0,0),(264000,58,0,0,0,0),(264000,59,0,0,0,0),(264000,60,0,0,0,0),(264000,61,0,0,0,0),(264000,62,0,0,0,0),(264000,63,0,0,0,0),(264000,64,0,0,0,0),(264000,65,0,0,0,0),(264000,66,0,0,0,0),(264000,67,0,0,0,0),(264000,68,0,0,0,0),(265000,52,0,0,0,0),(265000,53,0,0,0,0),(265000,54,0,0,0,0),(265000,55,0,0,0,0),(265000,56,0,0,0,0),(265000,57,0,0,0,0),(265000,58,0,0,0,0),(265000,59,0,0,0,0),(265000,60,0,0,0,0),(265000,61,0,0,0,0),(265000,62,0,0,0,0),(265000,63,0,0,0,0),(265000,64,0,0,0,0),(265000,65,0,0,0,0),(265000,66,0,0,0,0),(265000,67,0,0,0,0),(265000,68,0,0,0,0),(266000,52,0,0,0,0),(266000,53,0,0,0,0),(266000,54,0,0,0,0),(266000,55,0,0,0,0),(266000,56,0,0,0,0),(266000,57,0,0,0,0),(266000,58,0,0,0,0),(266000,59,0,0,0,0),(266000,60,0,0,0,0),(266000,61,0,0,0,0),(266000,62,0,0,0,0),(266000,63,0,0,0,0),(266000,64,0,0,0,0),(266000,65,0,0,0,0),(266000,66,0,0,0,0),(266000,67,0,0,0,0),(266000,68,0,0,0,0),(267000,52,0,0,0,0),(267000,53,0,0,0,0),(267000,54,0,0,0,0),(267000,55,0,0,0,0),(267000,56,0,0,0,0),(267000,57,0,0,0,0),(267000,58,0,0,0,0),(267000,59,0,0,0,0),(267000,60,0,0,0,0),(267000,61,0,0,0,0),(267000,62,0,0,0,0),(267000,63,0,0,0,0),(267000,64,0,0,0,0),(267000,65,0,0,0,0),(267000,66,0,0,0,0),(267000,67,0,0,0,0),(267000,68,0,0,0,0),(267100,52,0,0,0,0),(267100,53,0,0,0,0),(267100,54,0,0,0,0),(267100,55,0,0,0,0),(267100,56,0,0,0,0),(267100,57,0,0,0,0),(267100,58,0,0,0,0),(267100,59,0,0,0,0),(267100,60,0,0,0,0),(267100,61,0,0,0,0),(267100,62,0,0,0,0),(267100,63,0,0,0,0),(267100,64,0,0,0,0),(267100,65,0,0,0,0),(267100,66,0,0,0,0),(267100,67,0,0,0,0),(267100,68,0,0,0,0),(270000,52,0,0,0,0),(270000,53,0,0,0,0),(270000,54,0,0,0,0),(270000,55,0,0,0,0),(270000,56,0,0,0,0),(270000,57,0,0,0,0),(270000,58,0,0,0,0),(270000,59,0,0,0,0),(270000,60,0,0,0,0),(270000,61,0,0,0,0),(270000,62,0,0,0,0),(270000,63,0,0,0,0),(270000,64,0,0,0,0),(270000,65,0,0,0,0),(270000,66,0,0,0,0),(270000,67,0,0,0,0),(270000,68,0,0,0,0),(271000,52,0,0,0,0),(271000,53,0,0,0,0),(271000,54,0,0,0,0),(271000,55,0,0,0,0),(271000,56,0,0,0,0),(271000,57,0,0,0,0),(271000,58,0,0,0,0),(271000,59,0,0,0,0),(271000,60,0,0,0,0),(271000,61,0,0,0,0),(271000,62,0,0,0,0),(271000,63,0,0,0,0),(271000,64,0,0,0,0),(271000,65,0,0,0,0),(271000,66,0,0,0,0),(271000,67,0,0,0,0),(271000,68,0,0,0,0),(310000,52,0,0,0,0),(310000,53,0,0,0,0),(310000,54,0,0,0,0),(310000,55,0,0,0,0),(310000,56,0,0,0,0),(310000,57,0,0,0,0),(310000,58,0,0,0,0),(310000,59,0,0,0,0),(310000,60,0,0,0,0),(310000,61,0,0,0,0),(310000,62,0,0,0,0),(310000,63,0,0,0,0),(310000,64,0,0,0,0),(310000,65,0,0,0,0),(310000,66,0,0,0,0),(310000,67,0,0,0,0),(310000,68,0,0,0,0),(320000,52,0,0,0,0),(320000,53,0,0,0,0),(320000,54,0,0,0,0),(320000,55,0,0,0,0),(320000,56,0,0,0,0),(320000,57,0,0,0,0),(320000,58,0,0,0,0),(320000,59,0,0,0,0),(320000,60,0,0,0,0),(320000,61,0,0,0,0),(320000,62,0,0,0,0),(320000,63,0,0,0,0),(320000,64,0,0,0,0),(320000,65,0,0,0,0),(320000,66,0,0,0,0),(320000,67,0,0,0,0),(320000,68,0,0,0,0),(330000,52,0,0,0,0),(330000,53,0,0,0,0),(330000,54,0,0,0,0),(330000,55,0,0,0,0),(330000,56,0,0,0,0),(330000,57,0,0,0,0),(330000,58,0,0,0,0),(330000,59,0,0,0,0),(330000,60,0,0,0,0),(330000,61,0,0,0,0),(330000,62,0,0,0,0),(330000,63,0,0,0,0),(330000,64,0,0,0,0),(330000,65,0,0,0,0),(330000,66,0,0,0,0),(330000,67,0,0,0,0),(330000,68,0,0,0,0),(340000,52,0,0,0,0),(340000,53,0,0,0,0),(340000,54,0,0,0,0),(340000,55,0,0,0,0),(340000,56,0,0,0,0),(340000,57,0,0,0,0),(340000,58,0,0,0,0),(340000,59,0,0,0,0),(340000,60,0,0,0,0),(340000,61,0,0,0,0),(340000,62,0,0,0,0),(340000,63,0,0,0,0),(340000,64,0,0,0,0),(340000,65,0,0,0,0),(340000,66,0,0,0,0),(340000,67,0,0,0,0),(340000,68,0,0,0,0),(342000,52,0,0,0,0),(342000,53,0,0,0,0),(342000,54,0,0,0,0),(342000,55,0,0,0,0),(342000,56,0,0,0,0),(342000,57,0,0,0,0),(342000,58,0,0,0,0),(342000,59,0,0,0,0),(342000,60,0,0,0,0),(342000,61,0,0,0,0),(342000,62,0,0,0,0),(342000,63,0,0,0,0),(342000,64,0,0,0,0),(342000,65,0,0,0,0),(342000,66,0,0,0,0),(342000,67,0,0,0,0),(342000,68,0,0,0,0),(400000,52,0,0,0,0),(400000,53,0,0,0,0),(400000,54,0,0,0,0),(400000,55,0,0,0,0),(400000,56,0,0,0,0),(400000,57,0,0,0,0),(400000,58,0,0,0,0),(400000,59,0,0,0,0),(400000,60,0,0,0,0),(400000,61,0,0,0,0),(400000,62,0,0,0,0),(400000,63,0,0,0,0),(400000,64,0,0,0,0),(400000,65,0,0,0,0),(400000,66,0,0,0,0),(400000,67,0,0,0,0),(400000,68,0,0,0,0),(410000,52,0,0,0,0),(410000,53,0,0,0,0),(410000,54,0,0,0,0),(410000,55,0,0,0,0),(410000,56,0,0,0,0),(410000,57,0,0,0,0),(410000,58,0,0,0,0),(410000,59,0,0,0,0),(410000,60,0,0,0,0),(410000,61,0,0,0,0),(410000,62,0,0,0,0),(410000,63,0,0,0,0),(410000,64,0,0,0,0),(410000,65,0,0,0,0),(410000,66,0,0,0,0),(410000,67,0,0,0,0),(410000,68,0,0,0,0),(411000,52,0,0,0,0),(411000,53,0,0,0,0),(411000,54,0,0,0,0),(411000,55,0,0,0,0),(411000,56,0,0,0,0),(411000,57,0,0,0,0),(411000,58,0,0,0,0),(411000,59,0,0,0,0),(411000,60,0,0,0,0),(411000,61,0,0,0,0),(411000,62,0,0,0,0),(411000,63,0,0,0,0),(411000,64,0,0,0,0),(411000,65,0,0,0,0),(411000,66,0,0,0,0),(411000,67,0,0,0,0),(411000,68,0,0,0,0),(412000,52,0,0,0,0),(412000,53,0,0,0,0),(412000,54,0,0,0,0),(412000,55,0,0,0,0),(412000,56,0,0,0,0),(412000,57,0,0,0,0),(412000,58,0,0,0,0),(412000,59,0,0,0,0),(412000,60,0,0,0,0),(412000,61,0,0,0,0),(412000,62,0,0,0,0),(412000,63,0,0,0,0),(412000,64,0,0,0,0),(412000,65,0,0,0,0),(412000,66,0,0,0,0),(412000,67,0,0,0,0),(412000,68,0,0,0,0),(413000,52,0,0,0,0),(413000,53,0,0,0,0),(413000,54,0,0,0,0),(413000,55,0,0,0,0),(413000,56,0,0,0,0),(413000,57,0,0,0,0),(413000,58,0,0,0,0),(413000,59,0,0,0,0),(413000,60,0,0,0,0),(413000,61,0,0,0,0),(413000,62,0,0,0,0),(413000,63,0,0,0,0),(413000,64,0,0,0,0),(413000,65,0,0,0,0),(413000,66,0,0,0,0),(413000,67,0,0,0,0),(413000,68,0,0,0,0),(420000,52,0,0,0,0),(420000,53,0,0,0,0),(420000,54,0,0,0,0),(420000,55,0,0,0,0),(420000,56,0,0,0,0),(420000,57,0,0,0,0),(420000,58,0,0,0,0),(420000,59,0,0,0,0),(420000,60,0,0,0,0),(420000,61,0,0,0,0),(420000,62,0,0,0,0),(420000,63,0,0,0,0),(420000,64,0,0,0,0),(420000,65,0,0,0,0),(420000,66,0,0,0,0),(420000,67,0,0,0,0),(420000,68,0,0,0,0),(450000,52,0,0,0,0),(450000,53,0,0,0,0),(450000,54,0,0,0,0),(450000,55,0,0,0,0),(450000,56,0,0,0,0),(450000,57,0,0,0,0),(450000,58,0,0,0,0),(450000,59,0,0,0,0),(450000,60,0,0,0,0),(450000,61,0,0,0,0),(450000,62,0,0,0,0),(450000,63,0,0,0,0),(450000,64,0,0,0,0),(450000,65,0,0,0,0),(450000,66,0,0,0,0),(450000,67,0,0,0,0),(450000,68,0,0,0,0),(451000,52,0,0,0,0),(451000,53,0,0,0,0),(451000,54,0,0,0,0),(451000,55,0,0,0,0),(451000,56,0,0,0,0),(451000,57,0,0,0,0),(451000,58,0,0,0,0),(451000,59,0,0,0,0),(451000,60,0,0,0,0),(451000,61,0,0,0,0),(451000,62,0,0,0,0),(451000,63,0,0,0,0),(451000,64,0,0,0,0),(451000,65,0,0,0,0),(451000,66,0,0,0,0),(451000,67,0,0,0,0),(451000,68,0,0,0,0),(452000,52,0,0,0,0),(452000,53,0,0,0,0),(452000,54,0,0,0,0),(452000,55,0,0,0,0),(452000,56,0,0,0,0),(452000,57,0,0,0,0),(452000,58,0,0,0,0),(452000,59,0,0,0,0),(452000,60,0,0,0,0),(452000,61,0,0,0,0),(452000,62,0,0,0,0),(452000,63,0,0,0,0),(452000,64,0,0,0,0),(452000,65,0,0,0,0),(452000,66,0,0,0,0),(452000,67,0,0,0,0),(452000,68,0,0,0,0),(455000,52,0,0,0,0),(455000,53,0,0,0,0),(455000,54,0,0,0,0),(455000,55,0,0,0,0),(455000,56,0,0,0,0),(455000,57,0,0,0,0),(455000,58,0,0,0,0),(455000,59,0,0,0,0),(455000,60,0,0,0,0),(455000,61,0,0,0,0),(455000,62,0,0,0,0),(455000,63,0,0,0,0),(455000,64,0,0,0,0),(455000,65,0,0,0,0),(455000,66,0,0,0,0),(455000,67,0,0,0,0),(455000,68,0,0,0,0),(460000,52,0,0,0,0),(460000,53,0,0,0,0),(460000,54,0,0,0,0),(460000,55,0,0,0,0),(460000,56,0,0,0,0),(460000,57,0,0,0,0),(460000,58,0,0,0,0),(460000,59,0,0,0,0),(460000,60,0,0,0,0),(460000,61,0,0,0,0),(460000,62,0,0,0,0),(460000,63,0,0,0,0),(460000,64,0,0,0,0),(460000,65,0,0,0,0),(460000,66,0,0,0,0),(460000,67,0,0,0,0),(460000,68,0,0,0,0),(470000,52,0,0,0,0),(470000,53,0,0,0,0),(470000,54,0,0,0,0),(470000,55,0,0,0,0),(470000,56,0,0,0,0),(470000,57,0,0,0,0),(470000,58,0,0,0,0),(470000,59,0,0,0,0),(470000,60,0,0,0,0),(470000,61,0,0,0,0),(470000,62,0,0,0,0),(470000,63,0,0,0,0),(470000,64,0,0,0,0),(470000,65,0,0,0,0),(470000,66,0,0,0,0),(470000,67,0,0,0,0),(470000,68,0,0,0,0),(480000,52,0,0,0,0),(480000,53,0,0,0,0),(480000,54,0,0,0,0),(480000,55,0,0,0,0),(480000,56,0,0,0,0),(480000,57,0,0,0,0),(480000,58,0,0,0,0),(480000,59,0,0,0,0),(480000,60,0,0,0,0),(480000,61,0,0,0,0),(480000,62,0,0,0,0),(480000,63,0,0,0,0),(480000,64,0,0,0,0),(480000,65,0,0,0,0),(480000,66,0,0,0,0),(480000,67,0,0,0,0),(480000,68,0,0,0,0),(481000,52,0,0,0,0),(481000,53,0,0,0,0),(481000,54,0,0,0,0),(481000,55,0,0,0,0),(481000,56,0,0,0,0),(481000,57,0,0,0,0),(481000,58,0,0,0,0),(481000,59,0,0,0,0),(481000,60,0,0,0,0),(481000,61,0,0,0,0),(481000,62,0,0,0,0),(481000,63,0,0,0,0),(481000,64,0,0,0,0),(481000,65,0,0,0,0),(481000,66,0,0,0,0),(481000,67,0,0,0,0),(481000,68,0,0,0,0),(500000,52,0,0,0,0),(500000,53,0,0,0,0),(500000,54,0,0,0,0),(500000,55,0,0,0,0),(500000,56,0,0,0,0),(500000,57,0,0,0,0),(500000,58,0,0,0,0),(500000,59,0,0,0,0),(500000,60,0,0,0,0),(500000,61,0,0,0,0),(500000,62,0,0,0,0),(500000,63,0,0,0,0),(500000,64,0,0,0,0),(500000,65,0,0,0,0),(500000,66,0,0,0,0),(500000,67,0,0,0,0),(500000,68,0,0,0,0),(501000,52,0,0,0,0),(501000,53,0,0,0,0),(501000,54,0,0,0,0),(501000,55,0,0,0,0),(501000,56,0,0,0,0),(501000,57,0,0,0,0),(501000,58,0,0,0,0),(501000,59,0,0,0,0),(501000,60,0,0,0,0),(501000,61,0,0,0,0),(501000,62,0,0,0,0),(501000,63,0,0,0,0),(501000,64,0,0,0,0),(501000,65,0,0,0,0),(501000,66,0,0,0,0),(501000,67,0,0,0,0),(501000,68,0,0,0,0),(501800,52,0,0,0,0),(501800,53,0,0,0,0),(501800,54,0,0,0,0),(501800,55,0,0,0,0),(501800,56,0,0,0,0),(501800,57,0,0,0,0),(501800,58,0,0,0,0),(501800,59,0,0,0,0),(501800,60,0,0,0,0),(501800,61,0,0,0,0),(501800,62,0,0,0,0),(501800,63,0,0,0,0),(501800,64,0,0,0,0),(501800,65,0,0,0,0),(501800,66,0,0,0,0),(501800,67,0,0,0,0),(501800,68,0,0,0,0),(501900,52,0,0,0,0),(501900,53,0,0,0,0),(501900,54,0,0,0,0),(501900,55,0,0,0,0),(501900,56,0,0,0,0),(501900,57,0,0,0,0),(501900,58,0,0,0,0),(501900,59,0,0,0,0),(501900,60,0,0,0,0),(501900,61,0,0,0,0),(501900,62,0,0,0,0),(501900,63,0,0,0,0),(501900,64,0,0,0,0),(501900,65,0,0,0,0),(501900,66,0,0,0,0),(501900,67,0,0,0,0),(501900,68,0,0,0,0),(502000,52,0,0,0,0),(502000,53,0,0,0,0),(502000,54,0,0,0,0),(502000,55,0,0,0,0),(502000,56,0,0,0,0),(502000,57,0,0,0,0),(502000,58,0,0,0,0),(502000,59,0,0,0,0),(502000,60,0,0,0,0),(502000,61,0,0,0,0),(502000,62,0,0,0,0),(502000,63,0,0,0,0),(502000,64,0,0,0,0),(502000,65,0,0,0,0),(502000,66,0,0,0,0),(502000,67,0,0,0,0),(502000,68,0,0,0,0),(502100,52,0,0,0,0),(502100,53,0,0,0,0),(502100,54,0,0,0,0),(502100,55,0,0,0,0),(502100,56,0,0,0,0),(502100,57,0,0,0,0),(502100,58,0,0,0,0),(502100,59,0,0,0,0),(502100,60,0,0,0,0),(502100,61,0,0,0,0),(502100,62,0,0,0,0),(502100,63,0,0,0,0),(502100,64,0,0,0,0),(502100,65,0,0,0,0),(502100,66,0,0,0,0),(502100,67,0,0,0,0),(502100,68,0,0,0,0),(503000,52,0,0,0,0),(503000,53,0,0,0,0),(503000,54,0,0,0,0),(503000,55,0,0,0,0),(503000,56,0,0,0,0),(503000,57,0,0,0,0),(503000,58,0,0,0,0),(503000,59,0,0,0,0),(503000,60,0,0,0,0),(503000,61,0,0,0,0),(503000,62,0,0,0,0),(503000,63,0,0,0,0),(503000,64,0,0,0,0),(503000,65,0,0,0,0),(503000,66,0,0,0,0),(503000,67,0,0,0,0),(503000,68,0,0,0,0),(503100,52,0,0,0,0),(503100,53,0,0,0,0),(503100,54,0,0,0,0),(503100,55,0,0,0,0),(503100,56,0,0,0,0),(503100,57,0,0,0,0),(503100,58,0,0,0,0),(503100,59,0,0,0,0),(503100,60,0,0,0,0),(503100,61,0,0,0,0),(503100,62,0,0,0,0),(503100,63,0,0,0,0),(503100,64,0,0,0,0),(503100,65,0,0,0,0),(503100,66,0,0,0,0),(503100,67,0,0,0,0),(503100,68,0,0,0,0),(504000,52,0,0,0,0),(504000,53,0,0,0,0),(504000,54,0,0,0,0),(504000,55,0,0,0,0),(504000,56,0,0,0,0),(504000,57,0,0,0,0),(504000,58,0,0,0,0),(504000,59,0,0,0,0),(504000,60,0,0,0,0),(504000,61,0,0,0,0),(504000,62,0,0,0,0),(504000,63,0,0,0,0),(504000,64,0,0,0,0),(504000,65,0,0,0,0),(504000,66,0,0,0,0),(504000,67,0,0,0,0),(504000,68,0,0,0,0),(505000,52,0,0,0,0),(505000,53,0,0,0,0),(505000,54,0,0,0,0),(505000,55,0,0,0,0),(505000,56,0,0,0,0),(505000,57,0,0,0,0),(505000,58,0,0,0,0),(505000,59,0,0,0,0),(505000,60,0,0,0,0),(505000,61,0,0,0,0),(505000,62,0,0,0,0),(505000,63,0,0,0,0),(505000,64,0,0,0,0),(505000,65,0,0,0,0),(505000,66,0,0,0,0),(505000,67,0,0,0,0),(505000,68,0,0,0,0),(505100,52,0,0,0,0),(505100,53,0,0,0,0),(505100,54,0,0,0,0),(505100,55,0,0,0,0),(505100,56,0,0,0,0),(505100,57,0,0,0,0),(505100,58,0,0,0,0),(505100,59,0,0,0,0),(505100,60,0,0,0,0),(505100,61,0,0,0,0),(505100,62,0,0,0,0),(505100,63,0,0,0,0),(505100,64,0,0,0,0),(505100,65,0,0,0,0),(505100,66,0,0,0,0),(505100,67,0,0,0,0),(505100,68,0,0,0,0),(505110,52,0,0,0,0),(505110,53,0,0,0,0),(505110,54,0,0,0,0),(505110,55,0,0,0,0),(505110,56,0,0,0,0),(505110,57,0,0,0,0),(505110,58,0,0,0,0),(505110,59,0,0,0,0),(505110,60,0,0,0,0),(505110,61,0,0,0,0),(505110,62,0,0,0,0),(505110,63,0,0,0,0),(505110,64,0,0,0,0),(505110,65,0,0,0,0),(505110,66,0,0,0,0),(505110,67,0,0,0,0),(505110,68,0,0,0,0),(510000,52,0,0,0,0),(510000,53,0,0,0,0),(510000,54,0,0,0,0),(510000,55,0,0,0,0),(510000,56,0,0,0,0),(510000,57,0,0,0,0),(510000,58,0,0,0,0),(510000,59,0,0,0,0),(510000,60,0,0,0,0),(510000,61,0,0,0,0),(510000,62,0,0,0,0),(510000,63,0,0,0,0),(510000,64,0,0,0,0),(510000,65,0,0,0,0),(510000,66,0,0,0,0),(510000,67,0,0,0,0),(510000,68,0,0,0,0),(511000,52,0,0,0,0),(511000,53,0,0,0,0),(511000,54,0,0,0,0),(511000,55,0,0,0,0),(511000,56,0,0,0,0),(511000,57,0,0,0,0),(511000,58,0,0,0,0),(511000,59,0,0,0,0),(511000,60,0,0,0,0),(511000,61,0,0,0,0),(511000,62,0,0,0,0),(511000,63,0,0,0,0),(511000,64,0,0,0,0),(511000,65,0,0,0,0),(511000,66,0,0,0,0),(511000,67,0,0,0,0),(511000,68,0,0,0,0),(513000,52,0,0,0,0),(513000,53,0,0,0,0),(513000,54,0,0,0,0),(513000,55,0,0,0,0),(513000,56,0,0,0,0),(513000,57,0,0,0,0),(513000,58,0,0,0,0),(513000,59,0,0,0,0),(513000,60,0,0,0,0),(513000,61,0,0,0,0),(513000,62,0,0,0,0),(513000,63,0,0,0,0),(513000,64,0,0,0,0),(513000,65,0,0,0,0),(513000,66,0,0,0,0),(513000,67,0,0,0,0),(513000,68,0,0,0,0),(514000,52,0,0,0,0),(514000,53,0,0,0,0),(514000,54,0,0,0,0),(514000,55,0,0,0,0),(514000,56,0,0,0,0),(514000,57,0,0,0,0),(514000,58,0,0,0,0),(514000,59,0,0,0,0),(514000,60,0,0,0,0),(514000,61,0,0,0,0),(514000,62,0,0,0,0),(514000,63,0,0,0,0),(514000,64,0,0,0,0),(514000,65,0,0,0,0),(514000,66,0,0,0,0),(514000,67,0,0,0,0),(514000,68,0,0,0,0),(515000,52,0,0,0,0),(515000,53,0,0,0,0),(515000,54,0,0,0,0),(515000,55,0,0,0,0),(515000,56,0,0,0,0),(515000,57,0,0,0,0),(515000,58,0,0,0,0),(515000,59,0,0,0,0),(515000,60,0,0,0,0),(515000,61,0,0,0,0),(515000,62,0,0,0,0),(515000,63,0,0,0,0),(515000,64,0,0,0,0),(515000,65,0,0,0,0),(515000,66,0,0,0,0),(515000,67,0,0,0,0),(515000,68,0,0,0,0),(516000,52,0,0,0,0),(516000,53,0,0,0,0),(516000,54,0,0,0,0),(516000,55,0,0,0,0),(516000,56,0,0,0,0),(516000,57,0,0,0,0),(516000,58,0,0,0,0),(516000,59,0,0,0,0),(516000,60,0,0,0,0),(516000,61,0,0,0,0),(516000,62,0,0,0,0),(516000,63,0,0,0,0),(516000,64,0,0,0,0),(516000,65,0,0,0,0),(516000,66,0,0,0,0),(516000,67,0,0,0,0),(516000,68,0,0,0,0),(520000,52,0,0,0,0),(520000,53,0,0,0,0),(520000,54,0,0,0,0),(520000,55,0,0,0,0),(520000,56,0,0,0,0),(520000,57,0,0,0,0),(520000,58,0,0,0,0),(520000,59,0,0,0,0),(520000,60,0,0,0,0),(520000,61,0,0,0,0),(520000,62,0,0,0,0),(520000,63,0,0,0,0),(520000,64,0,0,0,0),(520000,65,0,0,0,0),(520000,66,0,0,0,0),(520000,67,0,0,0,0),(520000,68,0,0,0,0),(540000,52,0,0,0,0),(540000,53,0,0,0,0),(540000,54,0,0,0,0),(540000,55,0,0,0,0),(540000,56,0,0,0,0),(540000,57,0,0,0,0),(540000,58,0,0,0,0),(540000,59,0,0,0,0),(540000,60,0,0,0,0),(540000,61,0,0,0,0),(540000,62,0,0,0,0),(540000,63,0,0,0,0),(540000,64,0,0,0,0),(540000,65,0,0,0,0),(540000,66,0,0,0,0),(540000,67,0,0,0,0),(540000,68,0,0,0,0),(541000,52,0,0,0,0),(541000,53,0,0,0,0),(541000,54,0,0,0,0),(541000,55,0,0,0,0),(541000,56,0,0,0,0),(541000,57,0,0,0,0),(541000,58,0,0,0,0),(541000,59,0,0,0,0),(541000,60,0,0,0,0),(541000,61,0,0,0,0),(541000,62,0,0,0,0),(541000,63,0,0,0,0),(541000,64,0,0,0,0),(541000,65,0,0,0,0),(541000,66,0,0,0,0),(541000,67,0,0,0,0),(541000,68,0,0,0,0),(542000,52,0,0,0,0),(542000,53,0,0,0,0),(542000,54,0,0,0,0),(542000,55,0,0,0,0),(542000,56,0,0,0,0),(542000,57,0,0,0,0),(542000,58,0,0,0,0),(542000,59,0,0,0,0),(542000,60,0,0,0,0),(542000,61,0,0,0,0),(542000,62,0,0,0,0),(542000,63,0,0,0,0),(542000,64,0,0,0,0),(542000,65,0,0,0,0),(542000,66,0,0,0,0),(542000,67,0,0,0,0),(542000,68,0,0,0,0),(550000,52,0,0,0,0),(550000,53,0,0,0,0),(550000,54,0,0,0,0),(550000,55,0,0,0,0),(550000,56,0,0,0,0),(550000,57,0,0,0,0),(550000,58,0,0,0,0),(550000,59,0,0,0,0),(550000,60,0,0,0,0),(550000,61,0,0,0,0),(550000,62,0,0,0,0),(550000,63,0,0,0,0),(550000,64,0,0,0,0),(550000,65,0,0,0,0),(550000,66,0,0,0,0),(550000,67,0,0,0,0),(550000,68,0,0,0,0),(560000,52,0,0,0,0),(560000,53,0,0,0,0),(560000,54,0,0,0,0),(560000,55,0,0,0,0),(560000,56,0,0,0,0),(560000,57,0,0,0,0),(560000,58,0,0,0,0),(560000,59,0,0,0,0),(560000,60,0,0,0,0),(560000,61,0,0,0,0),(560000,62,0,0,0,0),(560000,63,0,0,0,0),(560000,64,0,0,0,0),(560000,65,0,0,0,0),(560000,66,0,0,0,0),(560000,67,0,0,0,0),(560000,68,0,0,0,0),(700000,52,0,0,0,0),(700000,53,0,0,0,0),(700000,54,0,0,0,0),(700000,55,0,0,0,0),(700000,56,0,0,0,0),(700000,57,0,0,0,0),(700000,58,0,0,0,0),(700000,59,0,0,0,0),(700000,60,0,0,0,0),(700000,61,0,0,0,0),(700000,62,0,0,0,0),(700000,63,0,0,0,0),(700000,64,0,0,0,0),(700000,65,0,0,0,0),(700000,66,0,0,0,0),(700000,67,0,0,0,0),(700000,68,0,0,0,0),(700100,52,0,0,0,0),(700100,53,0,0,0,0),(700100,54,0,0,0,0),(700100,55,0,0,0,0),(700100,56,0,0,0,0),(700100,57,0,0,0,0),(700100,58,0,0,0,0),(700100,59,0,0,0,0),(700100,60,0,0,0,0),(700100,61,0,0,0,0),(700100,62,0,0,0,0),(700100,63,0,0,0,0),(700100,64,0,0,0,0),(700100,65,0,0,0,0),(700100,66,0,0,0,0),(700100,67,0,0,0,0),(700100,68,0,0,0,0),(701000,52,0,0,0,0),(701000,53,0,0,0,0),(701000,54,0,0,0,0),(701000,55,0,0,0,0),(701000,56,0,0,0,0),(701000,57,0,0,0,0),(701000,58,0,0,0,0),(701000,59,0,0,0,0),(701000,60,0,0,0,0),(701000,61,0,0,0,0),(701000,62,0,0,0,0),(701000,63,0,0,0,0),(701000,64,0,0,0,0),(701000,65,0,0,0,0),(701000,66,0,0,0,0),(701000,67,0,0,0,0),(701000,68,0,0,0,0),(710000,52,0,0,0,0),(710000,53,0,0,0,0),(710000,54,0,0,0,0),(710000,55,0,0,0,0),(710000,56,0,0,0,0),(710000,57,0,0,0,0),(710000,58,0,0,0,0),(710000,59,0,0,0,0),(710000,60,0,0,0,0),(710000,61,0,0,0,0),(710000,62,0,0,0,0),(710000,63,0,0,0,0),(710000,64,0,0,0,0),(710000,65,0,0,0,0),(710000,66,0,0,0,0),(710000,67,0,0,0,0),(710000,68,0,0,0,0),(720000,52,0,0,0,0),(720000,53,0,0,0,0),(720000,54,0,0,0,0),(720000,55,0,0,0,0),(720000,56,0,0,0,0),(720000,57,0,0,0,0),(720000,58,0,0,0,0),(720000,59,0,0,0,0),(720000,60,0,0,0,0),(720000,61,0,0,0,0),(720000,62,0,0,0,0),(720000,63,0,0,0,0),(720000,64,0,0,0,0),(720000,65,0,0,0,0),(720000,66,0,0,0,0),(720000,67,0,0,0,0),(720000,68,0,0,0,0),(721000,52,0,0,0,0),(721000,53,0,0,0,0),(721000,54,0,0,0,0),(721000,55,0,0,0,0),(721000,56,0,0,0,0),(721000,57,0,0,0,0),(721000,58,0,0,0,0),(721000,59,0,0,0,0),(721000,60,0,0,0,0),(721000,61,0,0,0,0),(721000,62,0,0,0,0),(721000,63,0,0,0,0),(721000,64,0,0,0,0),(721000,65,0,0,0,0),(721000,66,0,0,0,0),(721000,67,0,0,0,0),(721000,68,0,0,0,0),(722000,52,0,0,0,0),(722000,53,0,0,0,0),(722000,54,0,0,0,0),(722000,55,0,0,0,0),(722000,56,0,0,0,0),(722000,57,0,0,0,0),(722000,58,0,0,0,0),(722000,59,0,0,0,0),(722000,60,0,0,0,0),(722000,61,0,0,0,0),(722000,62,0,0,0,0),(722000,63,0,0,0,0),(722000,64,0,0,0,0),(722000,65,0,0,0,0),(722000,66,0,0,0,0),(722000,67,0,0,0,0),(722000,68,0,0,0,0),(723000,52,0,0,0,0),(723000,53,0,0,0,0),(723000,54,0,0,0,0),(723000,55,0,0,0,0),(723000,56,0,0,0,0),(723000,57,0,0,0,0),(723000,58,0,0,0,0),(723000,59,0,0,0,0),(723000,60,0,0,0,0),(723000,61,0,0,0,0),(723000,62,0,0,0,0),(723000,63,0,0,0,0),(723000,64,0,0,0,0),(723000,65,0,0,0,0),(723000,66,0,0,0,0),(723000,67,0,0,0,0),(723000,68,0,0,0,0),(800000,52,0,0,0,0),(800000,53,0,0,0,0),(800000,54,0,0,0,0),(800000,55,0,0,0,0),(800000,56,0,0,0,0),(800000,57,0,0,0,0),(800000,58,0,0,0,0),(800000,59,0,0,0,0),(800000,60,0,0,0,0),(800000,61,0,0,0,0),(800000,62,0,0,0,0),(800000,63,0,0,0,0),(800000,64,0,0,0,0),(800000,65,0,0,0,0),(800000,66,0,0,0,0),(800000,67,0,0,0,0),(800000,68,0,0,0,0),(810000,52,0,0,0,0),(810000,53,0,0,0,0),(810000,54,0,0,0,0),(810000,55,0,0,0,0),(810000,56,0,0,0,0),(810000,57,0,0,0,0),(810000,58,0,0,0,0),(810000,59,0,0,0,0),(810000,60,0,0,0,0),(810000,61,0,0,0,0),(810000,62,0,0,0,0),(810000,63,0,0,0,0),(810000,64,0,0,0,0),(810000,65,0,0,0,0),(810000,66,0,0,0,0),(810000,67,0,0,0,0),(810000,68,0,0,0,0),(820000,52,0,0,0,0),(820000,53,0,0,0,0),(820000,54,0,0,0,0),(820000,55,0,0,0,0),(820000,56,0,0,0,0),(820000,57,0,0,0,0),(820000,58,0,0,0,0),(820000,59,0,0,0,0),(820000,60,0,0,0,0),(820000,61,0,0,0,0),(820000,62,0,0,0,0),(820000,63,0,0,0,0),(820000,64,0,0,0,0),(820000,65,0,0,0,0),(820000,66,0,0,0,0),(820000,67,0,0,0,0),(820000,68,0,0,0,0),(890000,52,0,0,0,0),(890000,53,0,0,0,0),(890000,54,0,0,0,0),(890000,55,0,0,0,0),(890000,56,0,0,0,0),(890000,57,0,0,0,0),(890000,58,0,0,0,0),(890000,59,0,0,0,0),(890000,60,0,0,0,0),(890000,61,0,0,0,0),(890000,62,0,0,0,0),(890000,63,0,0,0,0),(890000,64,0,0,0,0),(890000,65,0,0,0,0),(890000,66,0,0,0,0),(890000,67,0,0,0,0),(890000,68,0,0,0,0),(900000,52,0,0,0,0),(900000,53,0,0,0,0),(900000,54,0,0,0,0),(900000,55,0,0,0,0),(900000,56,0,0,0,0),(900000,57,0,0,0,0),(900000,58,0,0,0,0),(900000,59,0,0,0,0),(900000,60,0,0,0,0),(900000,61,0,0,0,0),(900000,62,0,0,0,0),(900000,63,0,0,0,0),(900000,64,0,0,0,0),(900000,65,0,0,0,0),(900000,66,0,0,0,0),(900000,67,0,0,0,0),(900000,68,0,0,0,0),(910000,52,0,0,0,0),(910000,53,0,0,0,0),(910000,54,0,0,0,0),(910000,55,0,0,0,0),(910000,56,0,0,0,0),(910000,57,0,0,0,0),(910000,58,0,0,0,0),(910000,59,0,0,0,0),(910000,60,0,0,0,0),(910000,61,0,0,0,0),(910000,62,0,0,0,0),(910000,63,0,0,0,0),(910000,64,0,0,0,0),(910000,65,0,0,0,0),(910000,66,0,0,0,0),(910000,67,0,0,0,0),(910000,68,0,0,0,0),(920000,52,0,0,0,0),(920000,53,0,0,0,0),(920000,54,0,0,0,0),(920000,55,0,0,0,0),(920000,56,0,0,0,0),(920000,57,0,0,0,0),(920000,58,0,0,0,0),(920000,59,0,0,0,0),(920000,60,0,0,0,0),(920000,61,0,0,0,0),(920000,62,0,0,0,0),(920000,63,0,0,0,0),(920000,64,0,0,0,0),(920000,65,0,0,0,0),(920000,66,0,0,0,0),(920000,67,0,0,0,0),(920000,68,0,0,0,0),(930000,52,0,0,0,0),(930000,53,0,0,0,0),(930000,54,0,0,0,0),(930000,55,0,0,0,0),(930000,56,0,0,0,0),(930000,57,0,0,0,0),(930000,58,0,0,0,0),(930000,59,0,0,0,0),(930000,60,0,0,0,0),(930000,61,0,0,0,0),(930000,62,0,0,0,0),(930000,63,0,0,0,0),(930000,64,0,0,0,0),(930000,65,0,0,0,0),(930000,66,0,0,0,0),(930000,67,0,0,0,0),(930000,68,0,0,0,0),(940000,52,0,0,0,0),(940000,53,0,0,0,0),(940000,54,0,0,0,0),(940000,55,0,0,0,0),(940000,56,0,0,0,0),(940000,57,0,0,0,0),(940000,58,0,0,0,0),(940000,59,0,0,0,0),(940000,60,0,0,0,0),(940000,61,0,0,0,0),(940000,62,0,0,0,0),(940000,63,0,0,0,0),(940000,64,0,0,0,0),(940000,65,0,0,0,0),(940000,66,0,0,0,0),(940000,67,0,0,0,0),(940000,68,0,0,0,0);
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
INSERT INTO CustAllocns VALUES (46,"73.1250",'2004-07-17',61,56);
UNLOCK TABLES;
/*!40000 ALTER TABLE CustAllocns ENABLE KEYS */;

--
-- Dumping data for table `CustBranch`
--


/*!40000 ALTER TABLE CustBranch DISABLE KEYS */;
LOCK TABLES CustBranch WRITE;
INSERT INTO CustBranch VALUES ('1234','12345678','One Two Three Four Five Six Seven Eight','','','','',1,'AR','25',0,'','','','','DEN',1,10,0,'','','','',''),('BILLYBUNT','BILLYBUNT','William Bunter Inc','Unit C Fatboy Industrial Estate','Donut Drive','Colchester','England',3,'UK','25',0,'','','','','MUC',6,10,0,'','','','',''),('GRAN','GRANHR','Gramtham Horton PLC','2 Growler Terraces','West Hemington','BE22 FD2','England',4,'UK','25',0,'','','Ed Heath','','MUC',6,1,0,'','','','',''),('JOHN','JOHNSON','San Diego','198 Greater Lofty Heights','San Diego','California','USA',2,'AR','25',0,'2412 22356','2412 23549','Rod Champion','rod.champion@johnson.com','DEN',5,1,0,'','','','',''),('JOHNTEMPE','JOHNSON','Johnson Vehicles Tempe','654 Roberto Street','Tempe','Arizona','USA',2,'AR','2',0,'56565 444 44','222 44 55','Holly Drako','','DEN',5,1,0,'','','','',''),('WALM','WALMON','Wallace Monterey - Sydney','309 Centennial Highway','Ballmain, Sydney','New South Wales 4133','Australia',16,'AR','25',0,'+61 2 3546 8875','+61 2 3546 8874','Phyllis Archibald','phyllis@WallMonterey.com.au','MEL',1,1,0,'','','','',''),('WALMOADEL','WALMON','Wallace Monterey - Adelaide','2-6 Devon Street','Clovelly Park','Adelaide South Australia 3122','Australia',18,'AR','25',0,'+61 3544 2113','+61 3544 2103','Brian Morrisey','brian@WallMonterey.com.au','MEL',1,1,0,'','','','',''),('WATT','WATTSGRP','Watts Motor Group Head Office and Admin','81 Trimble Road','Gimlet Rock','Greater Hampton','Arizona',2,'AR','25',0,'233 5442 3255','233 5442 3254','Bill Gobster','billg@watts.motors.com','MEL',5,1,0,'','','','',''),('WATTLA','WATTSGRP','Watts Group Los Angeles','922 Acton Lane','Reefston','Los Angeles','California',2,'CA','25',0,'235 446 998','235 446 654','Graham Bigglesworth','gbiggles@watts.motors.com','DEN',5,1,0,'','','','',''),('WATTSSAND','WATTSGRP','Watts Group San Diego','42 Grafton Place','San Diego','California','',2,'CA','25',0,'355 224 566','355 224 544','Sharon Stonely','sstonely@watts.motors.com','DEN',5,1,0,'','','','','');
UNLOCK TABLES;
/*!40000 ALTER TABLE CustBranch ENABLE KEYS */;

--
-- Dumping data for table `DebtorTrans`
--


/*!40000 ALTER TABLE DebtorTrans DISABLE KEYS */;
LOCK TABLES DebtorTrans WRITE;
INSERT INTO DebtorTrans VALUES (56,1,10,'WALMON','WALM','2004-07-14 00:00:00',55,0,'','EX',27,1.700000,637.188,0,0,0,0,73.125,'','1',0),(57,1,11,'JOHNSON','JOHNTEMPE','2004-07-15 00:00:00',55,0,'','WS',0,1.000000,-364.55,0,0,0,0,0,'','',0),(59,2,11,'BILLYBUNT','BILLYBUNT','2004-07-17 00:00:00',55,0,'','WS',0,0.800000,-70.5,0,0,0,0,0,'','',0),(60,1,12,'BILLYBUNT','','2004-07-17 00:00:00',55,0,'Direct Credit','',0,2.354000,-12.5,0,0,-0.33,0,0,'','',0),(61,3,11,'WALMON','WALM','2004-07-17 00:00:00',55,1,'Inv-1','EX',27,1.700000,-73.125,0,0,0,0,-73.125,'Less restocking fee 2.5% as agreed','',0);
UNLOCK TABLES;
/*!40000 ALTER TABLE DebtorTrans ENABLE KEYS */;

--
-- Dumping data for table `DebtorsMaster`
--


/*!40000 ALTER TABLE DebtorsMaster DISABLE KEYS */;
LOCK TABLES DebtorsMaster WRITE;
INSERT INTO DebtorsMaster VALUES ('12345678','One Two Three Four Five Six Seven Eight','','','','','USD','WS','2003-03-27 00:00:00',1,'20',0.1000,0.0200,53.2500,'2003-03-27 00:00:00',1000,0,'',0,0,'','email','','',''),('BILLYBUNT','William Bunter Inc','Unit C Fatboy Industrial Estate','Donut Drive','Colchester','England','GBP','WS','2004-07-17 00:00:00',1,'30',0.0000,0.0000,12.5000,'2004-07-17 00:00:00',1000,0,'',0,0,'','email','','',''),('GRANHR','Gramtham Horton PLC','P O Box 7755','West Hemington','BE22 FD2','England','USD','EX','2003-03-24 00:00:00',1,'20',0.0000,0.0000,30.0000,'2003-06-23 00:00:00',1000,0,'',0,0,'','email','','',''),('JOHNSON','Johnson Vehicle Company Inc','1103 Garrison Road East','Detroit','Michigan','','USD','WS','1978-02-26 00:00:00',1,'30',0.0000,0.0000,2203.3100,'2004-01-14 00:00:00',10000,0,'',0,0,'','email','','',''),('WALMON','Wallace Monterey Pty Ltd','P O Box 564','Ballmain, Sydney','New South Wales 4133','Australia','AUD','EX','1989-05-06 00:00:00',1,'30',0.0000,0.0000,2000.0000,'2003-01-31 00:00:00',30000,0,'',1,1,'5412345000013','email','edi@walmon.com','',''),('WATTSGRP','Watts Motor Group Inc','P O Box 1230','Gimlet Rock','Greater Hampton','Arizona','USD','WS','2003-01-16 00:00:00',1,'7',0.0000,0.0000,0.0000,NULL,25000,0,'',0,0,'','email','','','');
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
INSERT INTO GLTrans VALUES (525,25,12,0,'2004-07-13',55,720000,'PO: 14 BIGBISC - 125 - 12.5mm round steel x 1170 @ 0.62',725.4,0,''),(526,25,12,0,'2004-07-13',55,723000,'PO: 14 BIGBISC - 125 - 12.5mm round steel x 1170 @ 0.62',-725.4,0,''),(527,25,12,0,'2004-07-13',55,722000,'PO: 14 BIGBISC - HON90021 - Honda Civic front coil spring 1990-94 x 512 @ 9.69',4961.28,0,''),(528,25,12,0,'2004-07-13',55,723000,'PO: 14 BIGBISC - HON90021 - Honda Civic front coil spring 1990-94 x 512 @ 9.69',-4961.28,0,''),(529,10,1,0,'2004-07-14',55,1,'WALMON - 125 x 10 @ 0.6200',6.2,0,''),(530,10,1,0,'2004-07-14',55,720000,'WALMON - 125 x 10 @ 0.6200',-6.2,0,''),(531,10,1,0,'2004-07-14',55,107,'WALMON - 125 x 10 @ 1.2500',-7.35294,0,''),(532,10,1,0,'2004-07-14',55,1,'WALMON - 125 @ 2.5%',0.183824,0,''),(533,10,1,0,'2004-07-14',55,1,'WALMON - HON90021 x 25 @ 9.6900',242.25,0,''),(534,10,1,0,'2004-07-14',55,722000,'WALMON - HON90021 x 25 @ 9.6900',-242.25,0,''),(535,10,1,0,'2004-07-14',55,107,'WALMON - HON90021 x 25 @ 25.0000',-367.647,0,''),(536,10,1,0,'2004-07-14',55,710000,'WALMON',374.816,0,''),(537,11,1,0,'2004-07-15',55,1,'JOHNSON - 125 x 10 @ 0.6200',-6.2,0,''),(538,11,1,0,'2004-07-15',55,720000,'JOHNSON - 125 x 10 @ 0.6200',6.2,0,''),(539,11,1,0,'2004-07-15',55,107,'JOHNSON - 125 x 10 @ 2.33',23.3,0,''),(540,11,1,0,'2004-07-15',55,1,'JOHNSON - HON90021 x 21 @ 9.6900',-203.49,0,''),(541,11,1,0,'2004-07-15',55,722000,'JOHNSON - HON90021 x 21 @ 9.6900',203.49,0,''),(542,11,1,0,'2004-07-15',55,107,'JOHNSON - HON90021 x 21 @ 16.25',341.25,0,''),(543,11,1,0,'2004-07-15',55,710000,'JOHNSON',-364.55,0,''),(547,20,14,0,'2004-07-14',55,723000,'FREDBLOW Shipment charge against 3',53.52,0,''),(548,20,14,0,'2004-07-14',55,890000,'FREDBLOW - Inv 5442 USD5.35 @ a rate of 1.0000',5.35,0,''),(549,20,14,0,'2004-07-14',55,800000,'FREDBLOW - Inv 5442 USD58.87 @ a rate of 1.0000',-58.87,0,''),(550,11,2,0,'2004-07-17',55,1,'BILLYBUNT - 125 x 30 @ 0.6200',-18.6,0,''),(551,11,2,0,'2004-07-17',55,720000,'BILLYBUNT - 125 x 30 @ 0.6200',18.6,0,''),(552,11,2,0,'2004-07-17',55,107,'BILLYBUNT - 125 x 30 @ 2.35',70.5,0,''),(553,11,2,0,'2004-07-17',55,710000,'BILLYBUNT',-70.5,0,''),(554,12,1,0,'2004-07-17',55,259000,'Re-imbursed private drinks',-2.23025,0,''),(555,12,1,0,'2004-07-17',55,700100,'',7.54036,0,''),(556,12,1,0,'2004-07-17',55,710000,'',-5.4503,0,''),(557,12,1,0,'2004-07-17',55,455000,'',0.140187,0,''),(558,11,3,0,'2004-07-17',55,1,'WALMON - HON90021 x 3 @ 9.6900',-29.07,0,''),(559,11,3,0,'2004-07-17',55,722000,'WALMON - HON90021 x 3 @ 9.6900',29.07,0,''),(560,11,3,0,'2004-07-17',55,107,'WALMON - HON90021 x 3 @ 25.000',75,0,''),(561,11,3,0,'2004-07-17',55,1,'WALMON - HON90021 @ 2.5%',-1.875,0,''),(562,11,3,0,'2004-07-17',55,710000,'WALMON',-73.125,0,'');
UNLOCK TABLES;
/*!40000 ALTER TABLE GLTrans ENABLE KEYS */;

--
-- Dumping data for table `GRNs`
--


/*!40000 ALTER TABLE GRNs DISABLE KEYS */;
LOCK TABLES GRNs WRITE;
INSERT INTO GRNs VALUES (12,24,27,'125','2004-07-13','12.5mm round steel',1170.0000,0.0000,'BIGBISC'),(12,25,28,'HON90021','2004-07-13','Honda Civic front coil spring 1990-94',512.0000,0.0000,'BIGBISC');
UNLOCK TABLES;
/*!40000 ALTER TABLE GRNs ENABLE KEYS */;

--
-- Dumping data for table `Help`
--


/*!40000 ALTER TABLE Help DISABLE KEYS */;
LOCK TABLES Help WRITE;
INSERT INTO Help VALUES (3,130,'Enter the suppliers name and postal address, terms of payment',''),(4,160,'The main menu is split into sections depending on the user\'s module selection from the tabs accross the top of the menu. The screen actually is different for different users depending on the modules that the system administrator has elected to let you see. Some or all of:\r\n<LI> Orders </LI>\r\n<LI> Receivables </LI>\r\n<LI> Payables </LI>\r\n<LI> Purchasing </LI>\r\n<LI> Inventory </LI>\r\n<LI> Manufacturing </LI>\r\n<LI> Set Up </LI>\r\n<P>Selecting any of these tabs then shows the options available grouped between transactions in the first box, then inquiries or reports in the middle box, then maintenance in the bottom section. Clicking on the text of any option on the main menu proceeds to perform that action.\r\n<P><B>Using the Short Cut Menu</B>\r\n<P>Most actions involving customers will require selection of a customer first, similarly for suppliers and inventory the supplier or item must be selected first. Having selected the customer/supplier/item the selection screens show a sub menu relavent to the selected customer/supplier/item for most transactions and inquiries this is the more direct method of actioning. The traditional menu is available to aid the transition to this method of navigation.\r\n',''),(5,93,'Enter either a portion of the suppliers name or a portion of the suppliers code then hit the search now button.\r\nThe suppliers with the entered text in their name or code will show. Clicking on the button in the code column selects a supplier. Once a supplier has been selected then all the options relevant to the supplier show with links split into three columns - inquiries, transactions, maintenance:\r\n<LI>Supplier Account Inquiry</LI>\r\n<LI>Show this suppliers outstanding purchase orders</LI>\r\n<LI>Show all the suppliers purchase orders</LI>\r\n<LI>Show Shipments set up from the Supplier</LI>\r\n\r\nUnder Transactions Links there are links to enter a suppliers invoice, a debit (credit) note, a payment to the supplier or reverse goods received from a supplier.\r\n\r\nUnder Maintenance Links - the details of the supplier can be amended, new contacts together with email addresses can be maintained and new shipments initiated.\r\n','U');
UNLOCK TABLES;
/*!40000 ALTER TABLE Help ENABLE KEYS */;

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
INSERT INTO LocStock VALUES ('DEN','123',0.0,0),('DEN','125',0.0,0),('DEN','127',0.0,0),('DEN','130',0.0,0),('DEN','140',0.0,0),('DEN','HON90021',18.0,12),('DEN','HON90022',0.0,0),('DEN','HON92FrSus',0.0,0),('DEN','HONCIVIC92SET',0.0,0),('DEN','HONShock',0.0,0),('MEL','123',0.0,0),('MEL','125',1160.0,0),('MEL','127',0.0,0),('MEL','130',0.0,0),('MEL','140',0.0,0),('MEL','HON90021',490.0,12),('MEL','HON90022',0.0,0),('MEL','HON92FrSus',0.0,0),('MEL','HONCIVIC92SET',0.0,0),('MEL','HONShock',0.0,0),('MUC','123',0.0,0),('MUC','125',39.0,0),('MUC','127',0.0,0),('MUC','130',0.0,0),('MUC','140',0.0,0),('MUC','HON90021',3.0,1),('MUC','HON90022',0.0,0),('MUC','HON92FrSus',0.0,0),('MUC','HONCIVIC92SET',0.0,0),('MUC','HONShock',0.0,0);
UNLOCK TABLES;
/*!40000 ALTER TABLE LocStock ENABLE KEYS */;

--
-- Dumping data for table `LocTransfers`
--


/*!40000 ALTER TABLE LocTransfers DISABLE KEYS */;
LOCK TABLES LocTransfers WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE LocTransfers ENABLE KEYS */;

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
INSERT INTO Periods VALUES (51,'2004-03-31'),(52,'2004-04-30'),(53,'2004-05-31'),(54,'2004-06-30'),(55,'2004-07-31'),(56,'2004-08-31'),(57,'2004-09-30'),(58,'2004-10-31'),(59,'2004-11-30'),(60,'2004-12-31'),(61,'2005-01-31'),(62,'2005-02-28'),(63,'2005-03-31'),(64,'2005-04-30'),(65,'2005-05-31'),(66,'2005-06-30'),(67,'2005-07-31'),(68,'2005-08-31');
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
INSERT INTO PurchOrderDetails VALUES (27,14,'125','2004-07-14','12.5mm round steel',720000,0.0000,0.9500,0.0000,0.6200,1200.0000,1170.0000,0,'',0),(28,14,'HON90021','2004-07-14','Honda Civic front coil spring 1990-94',722000,0.0000,3.5000,0.0000,9.6900,500.0000,512.0000,0,'',1),(29,15,'123','2004-08-31','12.3mm Round Steel coil',720000,0.0000,0.3500,0.0000,0.0000,250.0000,0.0000,3,'0',0),(30,15,'125','2004-08-31','12.5mm round steel',720000,0.0000,0.3200,0.0000,0.0000,120.0000,0.0000,3,'0',0);
UNLOCK TABLES;
/*!40000 ALTER TABLE PurchOrderDetails ENABLE KEYS */;

--
-- Dumping data for table `PurchOrders`
--


/*!40000 ALTER TABLE PurchOrders DISABLE KEYS */;
LOCK TABLES PurchOrders WRITE;
INSERT INTO PurchOrders VALUES (14,'BIGBISC','','2004-07-13 00:00:00',0.9120,NULL,1,'Eric Watts','9022','MEL','Bourke Street','Melbourne','Melbourne','03 2112 2213'),(15,'BIGBISC','','2004-07-15 00:00:00',0.8000,NULL,1,'','','DEN','532-536 Wentworth Street','Denver','Colorado','233 5532 216');
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
INSERT INTO SalesAnalysis VALUES ('EX',55,7.3529,6.2000,'WALMON','WALM',10.0000,0.1838,'125','AR',1,'25','Steel',41),('EX',55,323.5295,213.1800,'WALMON','WALM',22.0000,-1.1029,'HON90021','AR',1,'25','Coil',42),('WS',55,-23.3000,-6.2000,'JOHNSON','JOHNTEMPE',-10.0000,0.0000,'125','AR',1,'2','Steel',43),('WS',55,-341.2500,-203.4900,'JOHNSON','JOHNTEMPE',-21.0000,0.0000,'HON90021','AR',1,'2','Coil',44),('WS',55,-88.1250,-18.6000,'BILLYBUNT','BILLYBUNT',-30.0000,0.0000,'125','UK',1,'25','Steel',45);
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
INSERT INTO SalesOrderDetails VALUES (27,'125',10.0000,1.2500,10.0000,0,0.0250,'2004-07-14 00:00:00',1),(27,'HON90021',25.0000,25.0000,25.0000,0,0.0000,'2004-07-14 00:00:00',1);
UNLOCK TABLES;
/*!40000 ALTER TABLE SalesOrderDetails ENABLE KEYS */;

--
-- Dumping data for table `SalesOrders`
--


/*!40000 ALTER TABLE SalesOrders DISABLE KEYS */;
LOCK TABLES SalesOrders WRITE;
INSERT INTO SalesOrders VALUES (27,'WALMON','WALM','',NULL,' Inv 1','2004-07-13','EX',1,'309 Centennial Highway','Ballmain, Sydney','New South Wales','Australia','+61 2 3546 8875','','Wallace Monterey - Sydney',0.00,'MEL','2004-07-13',0,'0000-00-00');
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
-- Dumping data for table `Scripts`
--


/*!40000 ALTER TABLE Scripts DISABLE KEYS */;
LOCK TABLES Scripts WRITE;
INSERT INTO Scripts VALUES (1,'AccountGroups.php','Defines the groupings of general ledger accounts'),(2,'AgedDebtors.php','Lists customer account balances in detail or summary in selected currency'),(3,'AgedSuppliers.php','Lists supplier account balances in detail or summary in selected currency'),(4,'Areas.php','Defines the sales areas - all customers must belong to a sales area for the purposes of sales analysis'),(5,'BOMInquiry.php','Displays the bill of material with cost information'),(6,'BOMListing.php','Lists the bills of material for a selected range of items'),(7,'BOMs.php','Administers the bills of material for a selected item'),(8,'BankAccounts.php','Defines the general ledger code for bank accounts and specifies that bank transactions be created for these accounts for the purposes of reconciliation'),(9,'BankMatching.php','Allows payments and receipts to be matched off against bank statements'),(10,'BankReconciliation.php','Displays the bank reconciliation for a selected bank account'),(11,'COGSGLPostings.php','Defines the general ledger account to be used for cost of sales entries'),(12,'CompanyPreferences.php','Defines the settings applicable for the company, including name, address, tax authority reference, whether GL integration used etc.'),(13,'ConfirmDispatchControlled_Invoice.php','Specifies the batch references/serial numbers of items dispatched that are being invoiced'),(14,'ConfirmDispatch_Invoice.php','Creates sales invoices from entered sales orders based on the quantities dispatched that can be modified'),(15,'CreditItemsControlled.php','Specifies the batch references/serial numbers of items being credited back into stock'),(16,'CreditStatus.php','Defines the credit status records. Each customer account is given a credit status from this table. Some credit status records can prohibit invoicing and new orders being entered.'),(17,'Credit_Invoice.php','Creates a credit note based on the details of an existing invoice'),(18,'Currencies.php','Defines the currencies available. Each customer and supplier must be defined as transacting in one of the currencies defined here.'),(19,'CustEDISetup.php','Allows the set up the customer specified EDI parameters for server, email or ftp.'),(20,'CustWhereAlloc.php','Shows to which invoices a receipt was allocated to'),(21,'CustomerAllocations.php','Allows customer receipts and credit notes to be allocated to sales invoices'),(22,'CustomerBranches.php','Defines the details of customer branches such as delivery address and contact details - also sales area, representative etc'),(23,'CustomerInquiry.php','Shows the customers account transactions with balances outstanding, links available to drill down to invoice/credit note or email invoices/credit notes'),(24,'CustomerReceipt.php','Entry of both customer receipts against accounts receivable and also general ledger or nominal receipts'),(25,'CustomerTransInquiry.php','Lists in html the sequence of customer transactions, invoices, credit notes or receipts by a user entered date range'),(26,'Customers.php','Defines the setup of a customer account, including payment terms, billing address, credit status, currency etc'),(27,'DeliveryDetails.php','Used during order entry to allow the entry of delivery addresses other than the defaulted branch delivery address and information about carrier/shipping method etc'),(28,'DiscountCategories.php','Defines the items belonging to a discount category. Discount Categories are used to allow discounts based on quantities across a range of producs'),(29,'DiscountMatrix.php','Defines the rates of discount applicable to discount categories and the customer groupings to which the rates are to apply'),(30,'EDIMessageFormat.php','Specifies the EDI message format used by a customer - administrator use only.'),(31,'EDIProcessOrders.php','Processes incoming EDI orders into sales orders'),(32,'EDISendInvoices.php','Processes invoiced EDI customer invoices into EDI messages and sends using the customers preferred method either ftp or email attachments.'),(33,'EmailCustTrans.php','Emails selected invoice or credit to the customer'),(34,'FTP_RadioBeacon.php','FTPs sales orders for dispatch to a radio beacon software enabled warehouse dispatching facility'),(35,'FreightCosts.php','Defines the setup of the freight cost using different shipping methods to different destinations. The system can use this information to calculate applicable freight if the items are defined with the correct kgs and cubic volume'),(36,'GLAccountInquiry.php','Shows the general ledger transactions for a specified account over a specified range of periods'),(37,'GLAccounts.php','Defines the general ledger accounts'),(38,'GLBalanceSheet.php','Shows the balance sheet for the company as at a specified date'),(39,'GLCodesInquiry.php','Shows the list of general ledger codes defined with account names and groupings'),(40,'GLJournal.php','Entry of general ledger journals, periods are calculated based on the date entered here'),(41,'GLProfit_Loss.php','Shows the profit and loss of the company for the range of periods entered'),(42,'GLTransInquiry.php','Shows the general ledger journal created for the sub ledger transaction specified'),(43,'GLTrialBalance.php','Shows the trial balance for the month and the for the period selected together with the budgeted trial balances'),(44,'GoodsReceived.php','Entry of items received against purchase orders'),(45,'GoodsReceivedControlled.php','Entry of the serial numbers or batch references for controlled items received against purchase orders'),(46,'InventoryPlanning.php','Creates a pdf report showing the last 4 months use of items including as a component of assemblies together with stock quantity on hand, current demand for the item and current quantity on sales order.'),(47,'InventoryValuation.php','Creates a pdf report showing the value of stock at standard cost for a range of product categories selected'),(48,'Locations.php','Defines the inventory stocking locations or warehouses'),(49,'Logout.php','Shows when the user logs out of webERP'),(50,'MailInventoryValuation.php','Meant to be run as a scheduled process to email the stock valuation off to a specified person. Creates the same stock valuation report as InventoryValuation.php'),(51,'MailSalesReport.php','Creates a sales analysis pdf report and emails it to the defined receipients. This script is meant to be run as a scheduled process for daily or weekly sales reporting'),(52,'MailSalesReport_csv.php','Creates a sales analysis report as a comma separated values (csv) file and emails it to the defined receipients. This script is meant to be run as a scheduled process for daily or weekly sales reporting'),(53,'OrderDetails.php','Shows the detail of a sales order'),(54,'OutstandingGRNs.php','Creates a pdf showing all GRNs for which there has been no purchase invoice matched off against.'),(55,'PDFBankingSummary.php','Creates a pdf showing the amounts entered as receipts on a specified date together with references for the purposes of banking'),(56,'PDFChequeListing.php','Creates a pdf showing all payments that have been made from a specified bank account over a specified period. This can be emailed to an email account defined in config.php - ie a financial controller'),(57,'PDFDeliveryDifferences.php','Creates a pdf report listing the delivery differences from what the customer requested as recorded in the order entry. The report calculates a percentage of order fill based on the number of orders filled in full on time'),(58,'PDFLowGP.php','Creates a pdf report showing the low gross profit sales made in the selected date range. The percentage of gp deemed acceptable can also be entered'),(59,'PDFPriceList.php','Creates a pdf of the price list applicable to a given sales type and customer. Also allows the listing of prices specific to a customer'),(60,'PDFStockCheckComparison.php','Creates a pdf comparing the quantites entered as counted at a given range of locations against the quantity stored as on hand as at the time a stock check was initiated.'),(61,'PDFStockLocTransfer.php','Creates a stock location transfer docket for the selected location transfer reference number'),(62,'PO_Chk_ShiptRef_JobRef.php','Checks the Shipment of JobReference number is correct during AP invoice entry'),(63,'PO_Header.php','Entry of a purchase order header record - date, references buyer etc'),(64,'PO_Items.php','Entry of a purchase order items - allows entry of items with lookup of currency cost from Purchasing Data previously entered also allows entry of nominal items against a general ledger code if the AP is integrated to the GL'),(65,'PO_OrderDetails.php','Purchase order inquiry shows the quantity received and invoiced of purchase order items as well as the header information'),(66,'PO_PDFPurchOrder.php','Creates a pdf of the selected purchase order for printing or email to one of the supplier contacts entered'),(67,'PO_SelectOSPurchOrder.php','Shows the outstanding purchase orders for selecting with links to receive or modify the purchase order header and items'),(68,'PO_SelectPurchOrder.php','Allows selection of any purchase order with links to the inquiry'),(69,'PaymentTerms.php','Defines the payment terms records, these can be expressed as either a number of days credit or a day in the following month. All customers and suppliers must have a corresponding payment term recorded against their account'),(70,'Payments.php','Entry of bank account payments either against an AP account or a general ledger payment - if the AP-GL link in company preferences is set'),(71,'PeriodsInquiry.php','Shows a list of all the system defined periods'),(72,'Prices.php','Entry of prices for a selected item also allows selection of sales type and currency for the price'),(73,'Prices_Customer.php','Entry of prices for a selected item and selected customer/branch. The currency and sales type is defaulted from the customer\'s record'),(74,'PrintCustOrder.php','Creates a pdf of the dispatch note - by default this is expected to be on two part pre-printed stationery to allow pickers to note discrepancies for the confirmer to update the dispatch at the time of invoicing'),(75,'PrintCustOrder_generic.php','Creates two copies of a laser printed dispatch note - both copies need to be written on by the pickers with any discrepancies to advise customer of any shortfall and on the office copy to ensure the correct quantites are invoiced'),(76,'PrintCustStatements.php','Creates a pdf for the customer statements in the selected range'),(77,'PrintCustTrans.php','Creates either a html invoice or credit note or a pdf. A range of invoices or credit notes can be selected also.'),(78,'PurchData.php','Entry of supplier purchasing data, the suppliers part reference and the suppliers currency cost of the item'),(79,'ReverseGRN.php','Reverses the entry of goods received - creating stock movements back out and necessary general ledger journals to effect the reversal'),(80,'SalesAnalReptCols.php','Entry of the definition of a sales analysis report\'s columns.'),(81,'SalesAnalRepts.php','Entry of the definition of a sales analysis report headers'),(82,'SalesAnalysis_UserDefined.php','Creates a pdf of a selected user defined sales analysis report'),(83,'SalesGLPostings.php','Defines the general ledger accounts used to post sales to based on product categories and sales areas'),(84,'SalesPeople.php','Defines the sales people of the business'),(85,'SalesTypes.php','Defines the sales types - prices are held against sales types they can be considered price lists. Sales analysis records are held by sales type too.'),(86,'SelectCompletedOrder.php','Allows the selection of completed sales orders for inquiries - choices to select by item code or customer'),(87,'SelectCreditItems.php','Entry of credit notes from scratch, selecting the items in either quick entry mode or searching for them manually'),(88,'SelectCustomer.php','Selection of customer - from where all customer related maintenance, transactions and inquiries start'),(89,'SelectGLAccount.php','Selection of general ledger account from where all general ledger account maintenance, or inquiries are initiated'),(90,'SelectOrderItems.php','Entry of sales order items with both quick entry and part search functions'),(91,'SelectProduct.php','Selection of items. All item maintenance, transactions and inquiries start with this script'),(92,'SelectSalesOrder.php','Selects a sales order irrespective of completed or not for inquiries'),(93,'SelectSupplier.php','Selects a supplier. A supplier is required to be selected before any AP transactions and before any maintenance or inquiry of the supplier'),(94,'ShipmentCosting.php','Shows the costing of a shipment with all the items invoice values and any shipment costs apportioned. Updating the shipment has an option to update standard costs of all items on the shipment and create any general ledger variance journals'),(95,'Shipments.php','Entry of shipments from outstanding purchase orders for a selected supplier - changes in the delivery date will cascade into the different purchase orders on the shipment'),(96,'Shippers.php','Defines the shipping methods available. Each customer branch has a default shipping method associated with it which must match a record from this table'),(97,'Shipt_Select.php','Selection of a shipment for displaying and modification or updating'),(98,'ShiptsList.php','Shows a list of all the open shipments for a selected supplier. Linked from POItems.php'),(99,'SpecialOrder.php','Allows for a sales order to be created and an indent order to be created on a supplier for a one off item that may never be purchased again. A dummy part is created based on the description and cost details given.'),(100,'StockAdjustments.php','Entry of quantity corrections to stocks in a selected location.'),(101,'StockAdjustmentsControlled.php','Entry of batch references or serial numbers on controlled stock items being adjusted'),(102,'StockCategories.php','Defines the stock categories. All items must refer to one of these categories. The category record also allows the specification of the general ledger codes where stock items are to be posted - the balance sheet account and the profit and loss effect of any adjustments and the profit and loss effect of any price variances'),(103,'StockCheck.php','Allows creation of a stock check file - copying the current quantites in stock for later comparison to the entered counts. Also produces a pdf for the count sheets.'),(104,'StockCostUpdate.php','Allows update of the standard cost of items producing general ledger journals if the company preferences stock GL interface is active'),(105,'StockCounts.php','Allows entry of stock counts'),(106,'StockLocMovements.php','Inquiry shows the Movements of all stock items for a specified location'),(107,'StockLocQties_csv.php','Makes a comma separated values (CSV)file of the stock item codes and quantities'),(108,'StockLocStatus.php','Shows the stock on hand together with outstanding sales orders and outstanding purchase orders by stock location for all items in the selected stock category'),(109,'StockLocTransfer.php','Entry of a bulk stock location transfer for many parts from one location to another.'),(110,'StockLocTransferReceive.php','Effects the transfer and creates the stock movements for a bulk stock location transfer initiated from StockLocTransfer.php'),(111,'StockMovements.php','Shows a list of all the stock movements for a selected item and stock location including the price at which they were sold in local currency and the price at which they were purchased for in local currency'),(112,'StockQties_csv.php','Makes a comma separated values (CSV)file of the stock item codes and quantities'),(113,'StockReorderLevel.php','Entry and review of the re-order level of items by stocking location'),(114,'StockSerialItems.php','Shows a list of the serial numbers or the batch references and quantities of controlled items. This inquiry is linked from the stock status inquiry'),(115,'StockStatus.php','Shows the stock on hand together with outstanding sales orders and outstanding purchase orders by stock location for a selected part. Has a link to show the serial numbers in stock at the location selected if the item is controlled'),(116,'StockTransferControlled.php','Entry of serial numbers/batch references for controlled items being received on a stock transfer. The script is used by both bulk transfers and point to point transfers'),(117,'StockTransfers.php','Entry of point to point stock location transfers of a single part'),(118,'StockUsage.php','Inquiry showing the quantity of stock used by period calculated from the sum of the stock movements over that period - by item and stock location. Also available over all locations'),(119,'Stocks.php','Defines an item - maintenance and addition of new parts'),(120,'SuppCreditGRNs.php','Entry of a supplier credit notes (debit notes) against existing GRN which have already been matched in full or in part'),(121,'SuppInvGRNs.php','Entry of supplier invoices against goods received'),(122,'SuppPaymentRun.php','Automatic creation of payment records based on calculated amounts due from AP invoices entered'),(123,'SuppShiptChgs.php','Entry of supplier invoices against shipments as charges against a shipment'),(124,'SuppTransGLAnalysis.php','Entry of supplier invoices against general ledger codes'),(125,'SupplierAllocations.php','Entry of allocations of supplier payments and credit notes to invoices'),(126,'SupplierContacts.php','Entry of supplier contacts and contact details including email addresses'),(127,'SupplierCredit.php','Entry of supplier credit notes (debit notes)'),(128,'SupplierInquiry.php','Inquiry showing invoices, credit notes and payments made to suppliers together with the amounts outstanding'),(129,'SupplierInvoice.php','Entry of supplier invoices'),(130,'Suppliers.php','Entry of new suppliers and maintenance of existing suppliers'),(131,'TaxAuthorities.php','Entry of tax authorities - the state intitutions that charge tax'),(132,'TaxAuthorityRates.php','Entry of the rates of tax applicable to the tax authority depending on the item tax level'),(133,'WWW_Users.php','Entry of users and security settings of users'),(134,'WhereUsedInquiry.php','Inquiry showing where an item is used ie all the parents where the item is a component of'),(135,'WorkCentres.php','Defines the various centres of work within a manufacturing company. Also the overhead and labour rates applicable to the work centre and its standard capacity'),(136,'WorkOrderEntry.php','Entry of new work orders'),(137,'WorkOrderIssue.php','Issue of materials to a work order'),(138,'Z_ChangeBranchCode.php','Utility to change the branch code of a customer that cascades the change through all the necessary tables'),(139,'Z_ChangeCustomerCode.php','Utility to change a customer code that cascades the change through all the necessary tables'),(140,'Z_ChangeStockCode.php','Utility to change an item code that cascades the change through all the necessary tables'),(141,'Z_CheckAllocationsFrom.php',''),(142,'Z_CheckAllocs.php',''),(143,'Z_CheckDebtorsControl.php','Inquiry that shows the total local currency (functional currency) balance of all customer accounts to reconcile with the general ledger debtors account'),(144,'Z_CreateChartDetails.php','Utility page to create chart detail records for all general ledger accounts and periods created - needs expert assistance in use'),(145,'Z_CreateCompany.php','Utility to insert company number 1 if not already there - actually only company 1 is used - the system is not multi-company'),(146,'Z_CurrencyDebtorsBalances.php','Inquiry that shows the total foreign currency together with the total local currency (functional currency) balances of all customer accounts to reconcile with the general ledger debtors account'),(147,'Z_CurrencySuppliersBalances.php','Inquiry that shows the total foreign currency amounts and also the local currency (functional currency) balances of all supplier accounts to reconcile with the general ledger creditors account'),(148,'Z_DeleteCreditNote.php','Utility to reverse a customer credit note - a desperate measure that should not be used except in extreme circumstances'),(149,'Z_DeleteInvoice.php','Utility to reverse a customer invoice - a desperate measure that should not be used except in extreme circumstances'),(150,'Z_DeleteSalesTransActions.php','Utility to delete all sales transactions, sales analysis the lot! Extreme care required!!!'),(151,'Z_MakeStockLocns.php','Utility to make LocStock records for all items and locations if not already set up.'),(152,'Z_PriceChanges.php','Utility to make bulk pricing alterations to selected sales type price lists or selected customer prices only'),(153,'Z_ReApplyCostToSA.php','Utility to allow the sales analysis table to be updated with the latest cost information - the sales analysis takes the cost at the time the sale was made to reconcile with the enteries made in the gl.'),(154,'Z_RePostGLFromPeriod.php','Utility to repost all general ledger transaction commencing from a specified period. This can take some time in busy environments. Normally GL transactions are posted automatically each time a trial balance or profit and loss account is run'),(155,'Z_ReverseSuppPaymentRun.php','Utility to reverse an entire Supplier payment run'),(156,'Z_UpdateChartDetailsBFwd.php','Utility to recalculate the ChartDetails table B/Fwd balances - extreme care!!'),(157,'Z_UploadForm.php','Utility to upload a file to a remote server'),(158,'Z_UploadResult.php','Utility to upload a file to a remote server'),(159,'Z_index.php','Utility menu page'),(160,'index.php','The main menu from where all functions available to the user are accessed by clicking on the links'),(161,'phpinfo.php','Details about PHP installation on the server');
UNLOCK TABLES;
/*!40000 ALTER TABLE Scripts ENABLE KEYS */;

--
-- Dumping data for table `ShipmentCharges`
--


/*!40000 ALTER TABLE ShipmentCharges DISABLE KEYS */;
LOCK TABLES ShipmentCharges WRITE;
INSERT INTO ShipmentCharges VALUES (1,3,20,14,'',53.52);
UNLOCK TABLES;
/*!40000 ALTER TABLE ShipmentCharges ENABLE KEYS */;

--
-- Dumping data for table `Shipments`
--


/*!40000 ALTER TABLE Shipments DISABLE KEYS */;
LOCK TABLES Shipments WRITE;
INSERT INTO Shipments VALUES (3,'0','Bunga Testarossa','2004-08-31 00:00:00',0.0000,'BIGBISC',0);
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
INSERT INTO StockCheckFreeze VALUES ('123','DEN',-13.4),('125','DEN',2515.5),('130','DEN',5867),('140','DEN',2872),('HON90021','DEN',8698.5),('HON90022','DEN',8226);
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
INSERT INTO StockMaster VALUES ('123','Steel','12.3mm Round Steel coil','X4k grade 12.3mm round steel in coils','Kgs','B','1800-01-01',"0.0000","0.0000","0.3000","0.0000","0.0000",0,0,0,14000.00,"0.0200","1.0000",'','',1,0,0),('125','Steel','12.5mm round steel','12.5mm diameter round steel X4K grade	','each','B','1800-01-01',"0.0000","0.0000","0.6200","0.0000","0.0000",0,0,1,1200.00,"0.0210","1.0000",'','',1,0,0),('127','Steel','X3K 12.7mm spring steel','X3K 12.7mm spring steel','each','B','1800-01-01',"0.0000","0.0000","0.0000","0.0000","0.0000",0,0,1,200.00,"0.0000","0.0000",'','',1,0,3),('130','Steel','13mm round spring steel X4K grade','13mm round spring steel X4K grade	','Kgs','B','1800-01-01',"0.0000","0.0000","0.6200","0.0000","0.0000",0,0,0,1200.00,"0.0600","1.0000",'','',1,0,0),('140','Steel','14mm round steel X4k grade','14mm round steel X4k grade	','metres','B','1800-01-01',"0.0000","0.0000","0.6200","0.0000","0.0000",0,0,0,1250.00,"0.0200","1.0000",'','',1,0,0),('HON90021','Coil','Honda Civic front coil spring 1990-94','Honda Civic front coil spring 1990-94	','each','M','1800-01-01',"0.0000","0.0000","6.8900","1.6000","1.2000",0,0,1,50.00,"0.3000","3.0000",'','SP',1,0,3),('HON90022','Coil','Honda Civic rear coil spring 1990-94','Honda Civic rear coil spring 1990-94	','each','M','1800-01-01',"0.0000","0.0000","2.1000","0.0000","0.0000",0,0,0,50.00,"0.0000","3.0000",'','SP',1,0,0),('HON92FrSus','SUSASS','Honda 92 Front suspension assembly','Honda 92 Front suspension assembly','each','A','1800-01-01',"0.0000","0.0000","0.0000","0.0000","0.0000",0,0,0,0.00,"0.0000","0.0000",'','',1,0,0),('HONCIVIC92SET','Coil','Honda Civic 90-94 spring set','Honda Civic 90-94 spring set','each','K','1800-01-01',"0.0000","0.0000","0.0000","0.0000","0.0000",0,0,0,0.00,"0.3000","6.0000",'','',1,0,0),('HONShock','SHOCKS','Honda 92 Monroe Shock Absorber','Honda 92 Monroe Shock Absorber','each','B','1800-01-01',"0.0000","0.0000","8.5000","0.0000","0.0000",0,0,0,0.00,"0.0000","0.0000",'','',1,0,0);
UNLOCK TABLES;
/*!40000 ALTER TABLE StockMaster ENABLE KEYS */;

--
-- Dumping data for table `StockMoves`
--


/*!40000 ALTER TABLE StockMoves DISABLE KEYS */;
LOCK TABLES StockMoves WRITE;
INSERT INTO StockMoves VALUES (150,'125',25,12,'MEL','2004-07-13','','',"1.0417",55,'BIGBISC (Biga Engineering PLC) - 14',1170.0000,0.0000,0.6200,1,1170,0,0),(151,'HON90021',25,12,'MEL','2004-07-13','','',"3.8377",55,'BIGBISC (Biga Engineering PLC) - 14',512.0000,0.0000,9.6900,1,512,0,0),(153,'125',10,1,'MEL','2004-07-14','WALMON','WALM',"0.7353",55,'27',-10.0000,0.0250,0.6200,1,1160,0,0),(154,'HON90021',10,1,'MEL','2004-07-14','WALMON','WALM',"14.7059",55,'27',-25.0000,0.0000,9.6900,1,487,0,0),(155,'125',11,1,'DEN','2004-07-15','JOHNSON','JOHNTEMPE',"2.3300",55,'',10.0000,0.0000,0.6200,1,10,0,0),(156,'HON90021',11,1,'DEN','2004-07-15','JOHNSON','JOHNTEMPE',"16.2500",55,'',21.0000,0.0000,9.6900,1,21,0,0),(157,'125',11,2,'MUC','2004-07-17','BILLYBUNT','BILLYBUNT',"2.9375",55,'',30.0000,0.0000,0.6200,1,30,0,0),(158,'HON90021',11,3,'MEL','2004-07-17','WALMON','WALM',"14.7059",55,'Ex Inv - 1',3.0000,0.0250,9.6900,1,490,0,0),(159,'125',17,1,'DEN','2004-07-17','','',"0.0000",55,'As per Eric damaged on return',-1.0000,0.0000,0.0000,1,9,0,0),(160,'125',17,2,'DEN','2004-07-17','','',"0.0000",55,'reverse from wrong location',1.0000,0.0000,0.0000,1,10,0,0),(161,'125',17,3,'MUC','2004-07-17','','',"0.0000",55,'As per Eric damaged on return',-1.0000,0.0000,0.0000,1,29,0,0),(162,'125',16,1,'DEN','2004-07-17','','',"0.0000",55,'To Munich Test Facility',-10.0000,0.0000,0.0000,1,0,0,0),(163,'125',16,1,'MUC','2004-07-17','','',"0.0000",55,'From Denver',10.0000,0.0000,0.0000,1,39,0,0),(164,'HON90021',16,1,'DEN','2004-07-17','','',"0.0000",55,'To Munich Test Facility',-3.0000,0.0000,0.0000,1,18,0,0),(165,'HON90021',16,1,'MUC','2004-07-17','','',"0.0000",55,'From Denver',3.0000,0.0000,0.0000,1,3,0,0);
UNLOCK TABLES;
/*!40000 ALTER TABLE StockMoves ENABLE KEYS */;

--
-- Dumping data for table `StockSerialItems`
--


/*!40000 ALTER TABLE StockSerialItems DISABLE KEYS */;
LOCK TABLES StockSerialItems WRITE;
INSERT INTO StockSerialItems VALUES ('125','DEN','5422441',0),('125','MUC','5422441',10),('125','DEN','85442-FG1',0),('125','MUC','85442-FG1',11),('125','MUC','85442-FG6',18),('125','MEL','QF12253',340),('125','MEL','QF12254',200),('125','MEL','QF12256',150),('125','MEL','QF12257',350),('125','MEL','QF12258',120),('HON90021','DEN','4544111',18),('HON90021','MUC','4544111',3),('HON90021','MEL','89001',175),('HON90021','MEL','89002',200),('HON90021','MEL','89003',112);
UNLOCK TABLES;
/*!40000 ALTER TABLE StockSerialItems ENABLE KEYS */;

--
-- Dumping data for table `StockSerialMoves`
--


/*!40000 ALTER TABLE StockSerialMoves DISABLE KEYS */;
LOCK TABLES StockSerialMoves WRITE;
INSERT INTO StockSerialMoves VALUES (271,150,'125','QF12253',350),(272,150,'125','QF12254',200),(273,150,'125','QF12256',150),(274,150,'125','QF12257',350),(275,150,'125','QF12258',120),(276,151,'HON90021','89001',200),(277,151,'HON90021','89002',200),(278,151,'HON90021','89003',112),(279,153,'125','QF12253',-10),(280,154,'HON90021','89001',-25),(281,155,'125','5422441',10),(282,156,'HON90021','4544111',21),(283,157,'125','85442-FG1',12),(284,157,'125','85442-FG6',18),(285,159,'125','85442-FG1',-1),(286,160,'125','85442-FG1',1),(287,161,'125','85442-FG1',-1),(288,162,'125','5422441',-10),(289,163,'125','5422441',10),(290,164,'HON90021','4544111',-3),(291,165,'HON90021','4544111',3);
UNLOCK TABLES;
/*!40000 ALTER TABLE StockSerialMoves ENABLE KEYS */;

--
-- Dumping data for table `SuppAllocs`
--


/*!40000 ALTER TABLE SuppAllocs DISABLE KEYS */;
LOCK TABLES SuppAllocs WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE SuppAllocs ENABLE KEYS */;

--
-- Dumping data for table `SuppTrans`
--


/*!40000 ALTER TABLE SuppTrans DISABLE KEYS */;
LOCK TABLES SuppTrans WRITE;
INSERT INTO SuppTrans VALUES (14,20,'FREDBLOW','5442','2004-07-14','2004-08-22',0,1.000000,53.5200,5.3500,0.0000,0.0000,'',0,25);
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
INSERT INTO SysTypes VALUES (0,'Journal - GL',9),(1,'Payment - GL',17),(2,'Receipt - GL',3),(3,'Standing Journal',0),(10,'Sales Invoice',1),(11,'Credit Note',3),(12,'Receipt',1),(15,'Journal - Debtors',0),(16,'Location Transfer',1),(17,'Stock Adjustment',3),(18,'Purchase Order',0),(20,'Purchase Invoice',14),(21,'Debit Note',4),(22,'Creditors Payment',3),(23,'Creditors Journal',0),(25,'Purchase Order Delivery',12),(26,'Work Order Receipt',0),(28,'Work Order Issue',0),(29,'Work Order Variance',0),(30,'Sales Order',0),(31,'Shipment Close',3),(35,'Cost Update',3),(50,'Opening Balance',0);
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
INSERT INTO WWW_Users VALUES ('Admin','weeping','Phil Daintree','','','','DEN',7,'2004-06-06 13:23:59','','A4','1,1,1,1,1,1,1,1,',0),('demo','weberp','Demonstration user','','','','DEN',7,'2004-07-10 17:23:42','','A4','1,1,1,1,1,1,1,1,',0),('testy','weberp','','GRANHR','','','DEN',6,'2004-02-23 20:21:56','GRAN','A4','1,0,0,0,0,0,0,0,',0);
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

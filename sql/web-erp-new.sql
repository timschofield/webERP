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
-- Dumping data for table `BankAccounts`
--


/*!40000 ALTER TABLE BankAccounts DISABLE KEYS */;
LOCK TABLES BankAccounts WRITE;
INSERT INTO BankAccounts VALUES (700000,'Chase Morgan Current account','5332 12210 11212',''),(700100,'Petty Cash account','NA','');
UNLOCK TABLES;
/*!40000 ALTER TABLE BankAccounts ENABLE KEYS */;

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
-- Dumping data for table `Currencies`
--


/*!40000 ALTER TABLE Currencies DISABLE KEYS */;
LOCK TABLES Currencies WRITE;
INSERT INTO Currencies VALUES ('Australian Dollars','AUD','Australia','cents',1.7000),('Deutsche Marks','DEM','German','Pfenig',1.8000),('Pounds','GBP','England','Pence',0.8000),('Yen','JPY','Japan','Yen',150.0000),('N Z Dollars','NZD','New Zealand','Cents',2.0000),('US Dollars','USD','United States','Cents',1.0000);
UNLOCK TABLES;
/*!40000 ALTER TABLE Currencies ENABLE KEYS */;

--
-- Dumping data for table `HoldReasons`
--


/*!40000 ALTER TABLE HoldReasons DISABLE KEYS */;
LOCK TABLES HoldReasons WRITE;
INSERT INTO HoldReasons VALUES (1,'Good History',0),(20,'Watch',0),(30,'No more work until payment rec',1),(51,'In liquidation',1);
UNLOCK TABLES;
/*!40000 ALTER TABLE HoldReasons ENABLE KEYS */;

--
-- Dumping data for table `Locations`
--


/*!40000 ALTER TABLE Locations DISABLE KEYS */;
LOCK TABLES Locations WRITE;
INSERT INTO Locations VALUES ('DEN','Denver','532-536 Wentworth Street','Denver','Colorado','233 5532 216','233 5532 215','g.bovert@weberp.com','Graham Bouvert',1),('MEL','Melbourne Australia','3-5 Albert Road','Morriban','Melbourne Victoria','+61 3 4445 23554','+61 3 4554 23244','g.prewit@weberp.com','Greg Prewit',1),('MUC','Munich Test Facility','Unit H Grand Tourismo Ind Est','Munchen','','+49 5554 22 121','+49 5442 22132','','Herman',1);
UNLOCK TABLES;
/*!40000 ALTER TABLE Locations ENABLE KEYS */;

--
-- Dumping data for table `PaymentTerms`
--


/*!40000 ALTER TABLE PaymentTerms DISABLE KEYS */;
LOCK TABLES PaymentTerms WRITE;
INSERT INTO PaymentTerms VALUES ('20','Due 20th Of the Following Month',0,22),('30','Due By End Of The Following Month',0,30),('7','Payment due within 7 days',7,0),('CA','Cash Only',1,0);
UNLOCK TABLES;
/*!40000 ALTER TABLE PaymentTerms ENABLE KEYS */;

--
-- Dumping data for table `Help`
--


/*!40000 ALTER TABLE Help DISABLE KEYS */;
LOCK TABLES Help WRITE;
INSERT INTO Help VALUES (3,130,'Enter the suppliers name and postal address, terms of payment',''),(4,160,'The main menu is split into sections depending on the user\'s module selection from the tabs accross the top of the menu. The screen actually is different for different users depending on the modules that the system administrator has elected to let you see. Some or all of:\r\n<LI> Orders </LI>\r\n<LI> Receivables </LI>\r\n<LI> Payables </LI>\r\n<LI> Purchasing </LI>\r\n<LI> Inventory </LI>\r\n<LI> Manufacturing </LI>\r\n<LI> Set Up </LI>\r\n<P>Selecting any of these tabs then shows the options available grouped between transactions in the first box, then inquiries or reports in the middle box, then maintenance in the bottom section. Clicking on the text of any option on the main menu proceeds to perform that action.\r\n<P><B>Using the Short Cut Menu</B>\r\n<P>Most actions involving customers will require selection of a customer first, similarly for suppliers and inventory the supplier or item must be selected first. Having selected the customer/supplier/item the selection screens show a sub menu relavent to the selected customer/supplier/item for most transactions and inquiries this is the more direct method of actioning. The traditional menu is available to aid the transition to this method of navigation.\r\n',''),(5,93,'Enter either a portion of the suppliers name or a portion of the suppliers code then hit the search now button.\r\nThe suppliers with the entered text in their name or code will show. Clicking on the button in the code column selects a supplier. Once a supplier has been selected then all the options relevant to the supplier show with links split into three columns - inquiries, transactions, maintenance:\r\n<LI>Supplier Account Inquiry</LI>\r\n<LI>Show this suppliers outstanding purchase orders</LI>\r\n<LI>Show all the suppliers purchase orders</LI>\r\n<LI>Show Shipments set up from the Supplier</LI>\r\n\r\nUnder Transactions Links there are links to enter a suppliers invoice, a debit (credit) note, a payment to the supplier or reverse goods received from a supplier.\r\n\r\nUnder Maintenance Links - the details of the supplier can be amended, new contacts together with email addresses can be maintained and new shipments initiated.\r\n','U');
UNLOCK TABLES;
/*!40000 ALTER TABLE Help ENABLE KEYS */;

--
-- Dumping data for table `Scripts`
--


/*!40000 ALTER TABLE Scripts DISABLE KEYS */;
LOCK TABLES Scripts WRITE;
INSERT INTO Scripts VALUES (1,'AccountGroups.php','Defines the groupings of general ledger accounts'),(2,'AgedDebtors.php','Lists customer account balances in detail or summary in selected currency'),(3,'AgedSuppliers.php','Lists supplier account balances in detail or summary in selected currency'),(4,'Areas.php','Defines the sales areas - all customers must belong to a sales area for the purposes of sales analysis'),(5,'BOMInquiry.php','Displays the bill of material with cost information'),(6,'BOMListing.php','Lists the bills of material for a selected range of items'),(7,'BOMs.php','Administers the bills of material for a selected item'),(8,'BankAccounts.php','Defines the general ledger code for bank accounts and specifies that bank transactions be created for these accounts for the purposes of reconciliation'),(9,'BankMatching.php','Allows payments and receipts to be matched off against bank statements'),(10,'BankReconciliation.php','Displays the bank reconciliation for a selected bank account'),(11,'COGSGLPostings.php','Defines the general ledger account to be used for cost of sales entries'),(12,'CompanyPreferences.php','Defines the settings applicable for the company, including name, address, tax authority reference, whether GL integration used etc.'),(13,'ConfirmDispatchControlled_Invoice.php','Specifies the batch references/serial numbers of items dispatched that are being invoiced'),(14,'ConfirmDispatch_Invoice.php','Creates sales invoices from entered sales orders based on the quantities dispatched that can be modified'),(15,'CreditItemsControlled.php','Specifies the batch references/serial numbers of items being credited back into stock'),(16,'CreditStatus.php','Defines the credit status records. Each customer account is given a credit status from this table. Some credit status records can prohibit invoicing and new orders being entered.'),(17,'Credit_Invoice.php','Creates a credit note based on the details of an existing invoice'),(18,'Currencies.php','Defines the currencies available. Each customer and supplier must be defined as transacting in one of the currencies defined here.'),(19,'CustEDISetup.php','Allows the set up the customer specified EDI parameters for server, email or ftp.'),(20,'CustWhereAlloc.php','Shows to which invoices a receipt was allocated to'),(21,'CustomerAllocations.php','Allows customer receipts and credit notes to be allocated to sales invoices'),(22,'CustomerBranches.php','Defines the details of customer branches such as delivery address and contact details - also sales area, representative etc'),(23,'CustomerInquiry.php','Shows the customers account transactions with balances outstanding, links available to drill down to invoice/credit note or email invoices/credit notes'),(24,'CustomerReceipt.php','Entry of both customer receipts against accounts receivable and also general ledger or nominal receipts'),(25,'CustomerTransInquiry.php','Lists in html the sequence of customer transactions, invoices, credit notes or receipts by a user entered date range'),(26,'Customers.php','Defines the setup of a customer account, including payment terms, billing address, credit status, currency etc'),(27,'DeliveryDetails.php','Used during order entry to allow the entry of delivery addresses other than the defaulted branch delivery address and information about carrier/shipping method etc'),(28,'DiscountCategories.php','Defines the items belonging to a discount category. Discount Categories are used to allow discounts based on quantities across a range of producs'),(29,'DiscountMatrix.php','Defines the rates of discount applicable to discount categories and the customer groupings to which the rates are to apply'),(30,'EDIMessageFormat.php','Specifies the EDI message format used by a customer - administrator use only.'),(31,'EDIProcessOrders.php','Processes incoming EDI orders into sales orders'),(32,'EDISendInvoices.php','Processes invoiced EDI customer invoices into EDI messages and sends using the customers preferred method either ftp or email attachments.'),(33,'EmailCustTrans.php','Emails selected invoice or credit to the customer'),(34,'FTP_RadioBeacon.php','FTPs sales orders for dispatch to a radio beacon software enabled warehouse dispatching facility'),(35,'FreightCosts.php','Defines the setup of the freight cost using different shipping methods to different destinations. The system can use this information to calculate applicable freight if the items are defined with the correct kgs and cubic volume'),(36,'GLAccountInquiry.php','Shows the general ledger transactions for a specified account over a specified range of periods'),(37,'GLAccounts.php','Defines the general ledger accounts'),(38,'GLBalanceSheet.php','Shows the balance sheet for the company as at a specified date'),(39,'GLCodesInquiry.php','Shows the list of general ledger codes defined with account names and groupings'),(40,'GLJournal.php','Entry of general ledger journals, periods are calculated based on the date entered here'),(41,'GLProfit_Loss.php','Shows the profit and loss of the company for the range of periods entered'),(42,'GLTransInquiry.php','Shows the general ledger journal created for the sub ledger transaction specified'),(43,'GLTrialBalance.php','Shows the trial balance for the month and the for the period selected together with the budgeted trial balances'),(44,'GoodsReceived.php','Entry of items received against purchase orders'),(45,'GoodsReceivedControlled.php','Entry of the serial numbers or batch references for controlled items received against purchase orders'),(46,'InventoryPlanning.php','Creates a pdf report showing the last 4 months use of items including as a component of assemblies together with stock quantity on hand, current demand for the item and current quantity on sales order.'),(47,'InventoryValuation.php','Creates a pdf report showing the value of stock at standard cost for a range of product categories selected'),(48,'Locations.php','Defines the inventory stocking locations or warehouses'),(49,'Logout.php','Shows when the user logs out of webERP'),(50,'MailInventoryValuation.php','Meant to be run as a scheduled process to email the stock valuation off to a specified person. Creates the same stock valuation report as InventoryValuation.php'),(51,'MailSalesReport.php','Creates a sales analysis pdf report and emails it to the defined receipients. This script is meant to be run as a scheduled process for daily or weekly sales reporting'),(52,'MailSalesReport_csv.php','Creates a sales analysis report as a comma separated values (csv) file and emails it to the defined receipients. This script is meant to be run as a scheduled process for daily or weekly sales reporting'),(53,'OrderDetails.php','Shows the detail of a sales order'),(54,'OutstandingGRNs.php','Creates a pdf showing all GRNs for which there has been no purchase invoice matched off against.'),(55,'PDFBankingSummary.php','Creates a pdf showing the amounts entered as receipts on a specified date together with references for the purposes of banking'),(56,'PDFChequeListing.php','Creates a pdf showing all payments that have been made from a specified bank account over a specified period. This can be emailed to an email account defined in config.php - ie a financial controller'),(57,'PDFDeliveryDifferences.php','Creates a pdf report listing the delivery differences from what the customer requested as recorded in the order entry. The report calculates a percentage of order fill based on the number of orders filled in full on time'),(58,'PDFLowGP.php','Creates a pdf report showing the low gross profit sales made in the selected date range. The percentage of gp deemed acceptable can also be entered'),(59,'PDFPriceList.php','Creates a pdf of the price list applicable to a given sales type and customer. Also allows the listing of prices specific to a customer'),(60,'PDFStockCheckComparison.php','Creates a pdf comparing the quantites entered as counted at a given range of locations against the quantity stored as on hand as at the time a stock check was initiated.'),(61,'PDFStockLocTransfer.php','Creates a stock location transfer docket for the selected location transfer reference number'),(62,'PO_Chk_ShiptRef_JobRef.php','Checks the Shipment of JobReference number is correct during AP invoice entry'),(63,'PO_Header.php','Entry of a purchase order header record - date, references buyer etc'),(64,'PO_Items.php','Entry of a purchase order items - allows entry of items with lookup of currency cost from Purchasing Data previously entered also allows entry of nominal items against a general ledger code if the AP is integrated to the GL'),(65,'PO_OrderDetails.php','Purchase order inquiry shows the quantity received and invoiced of purchase order items as well as the header information'),(66,'PO_PDFPurchOrder.php','Creates a pdf of the selected purchase order for printing or email to one of the supplier contacts entered'),(67,'PO_SelectOSPurchOrder.php','Shows the outstanding purchase orders for selecting with links to receive or modify the purchase order header and items'),(68,'PO_SelectPurchOrder.php','Allows selection of any purchase order with links to the inquiry'),(69,'PaymentTerms.php','Defines the payment terms records, these can be expressed as either a number of days credit or a day in the following month. All customers and suppliers must have a corresponding payment term recorded against their account'),(70,'Payments.php','Entry of bank account payments either against an AP account or a general ledger payment - if the AP-GL link in company preferences is set'),(71,'PeriodsInquiry.php','Shows a list of all the system defined periods'),(72,'Prices.php','Entry of prices for a selected item also allows selection of sales type and currency for the price'),(73,'Prices_Customer.php','Entry of prices for a selected item and selected customer/branch. The currency and sales type is defaulted from the customer\'s record'),(74,'PrintCustOrder.php','Creates a pdf of the dispatch note - by default this is expected to be on two part pre-printed stationery to allow pickers to note discrepancies for the confirmer to update the dispatch at the time of invoicing'),(75,'PrintCustOrder_generic.php','Creates two copies of a laser printed dispatch note - both copies need to be written on by the pickers with any discrepancies to advise customer of any shortfall and on the office copy to ensure the correct quantites are invoiced'),(76,'PrintCustStatements.php','Creates a pdf for the customer statements in the selected range'),(77,'PrintCustTrans.php','Creates either a html invoice or credit note or a pdf. A range of invoices or credit notes can be selected also.'),(78,'PurchData.php','Entry of supplier purchasing data, the suppliers part reference and the suppliers currency cost of the item'),(79,'ReverseGRN.php','Reverses the entry of goods received - creating stock movements back out and necessary general ledger journals to effect the reversal'),(80,'SalesAnalReptCols.php','Entry of the definition of a sales analysis report\'s columns.'),(81,'SalesAnalRepts.php','Entry of the definition of a sales analysis report headers'),(82,'SalesAnalysis_UserDefined.php','Creates a pdf of a selected user defined sales analysis report'),(83,'SalesGLPostings.php','Defines the general ledger accounts used to post sales to based on product categories and sales areas'),(84,'SalesPeople.php','Defines the sales people of the business'),(85,'SalesTypes.php','Defines the sales types - prices are held against sales types they can be considered price lists. Sales analysis records are held by sales type too.'),(86,'SelectCompletedOrder.php','Allows the selection of completed sales orders for inquiries - choices to select by item code or customer'),(87,'SelectCreditItems.php','Entry of credit notes from scratch, selecting the items in either quick entry mode or searching for them manually'),(88,'SelectCustomer.php','Selection of customer - from where all customer related maintenance, transactions and inquiries start'),(89,'SelectGLAccount.php','Selection of general ledger account from where all general ledger account maintenance, or inquiries are initiated'),(90,'SelectOrderItems.php','Entry of sales order items with both quick entry and part search functions'),(91,'SelectProduct.php','Selection of items. All item maintenance, transactions and inquiries start with this script'),(92,'SelectSalesOrder.php','Selects a sales order irrespective of completed or not for inquiries'),(93,'SelectSupplier.php','Selects a supplier. A supplier is required to be selected before any AP transactions and before any maintenance or inquiry of the supplier'),(94,'ShipmentCosting.php','Shows the costing of a shipment with all the items invoice values and any shipment costs apportioned. Updating the shipment has an option to update standard costs of all items on the shipment and create any general ledger variance journals'),(95,'Shipments.php','Entry of shipments from outstanding purchase orders for a selected supplier - changes in the delivery date will cascade into the different purchase orders on the shipment'),(96,'Shippers.php','Defines the shipping methods available. Each customer branch has a default shipping method associated with it which must match a record from this table'),(97,'Shipt_Select.php','Selection of a shipment for displaying and modification or updating'),(98,'ShiptsList.php','Shows a list of all the open shipments for a selected supplier. Linked from POItems.php'),(99,'SpecialOrder.php','Allows for a sales order to be created and an indent order to be created on a supplier for a one off item that may never be purchased again. A dummy part is created based on the description and cost details given.'),(100,'StockAdjustments.php','Entry of quantity corrections to stocks in a selected location.'),(101,'StockAdjustmentsControlled.php','Entry of batch references or serial numbers on controlled stock items being adjusted'),(102,'StockCategories.php','Defines the stock categories. All items must refer to one of these categories. The category record also allows the specification of the general ledger codes where stock items are to be posted - the balance sheet account and the profit and loss effect of any adjustments and the profit and loss effect of any price variances'),(103,'StockCheck.php','Allows creation of a stock check file - copying the current quantites in stock for later comparison to the entered counts. Also produces a pdf for the count sheets.'),(104,'StockCostUpdate.php','Allows update of the standard cost of items producing general ledger journals if the company preferences stock GL interface is active'),(105,'StockCounts.php','Allows entry of stock counts'),(106,'StockLocMovements.php','Inquiry shows the Movements of all stock items for a specified location'),(107,'StockLocQties_csv.php','Makes a comma separated values (CSV)file of the stock item codes and quantities'),(108,'StockLocStatus.php','Shows the stock on hand together with outstanding sales orders and outstanding purchase orders by stock location for all items in the selected stock category'),(109,'StockLocTransfer.php','Entry of a bulk stock location transfer for many parts from one location to another.'),(110,'StockLocTransferReceive.php','Effects the transfer and creates the stock movements for a bulk stock location transfer initiated from StockLocTransfer.php'),(111,'StockMovements.php','Shows a list of all the stock movements for a selected item and stock location including the price at which they were sold in local currency and the price at which they were purchased for in local currency'),(112,'StockQties_csv.php','Makes a comma separated values (CSV)file of the stock item codes and quantities'),(113,'StockReorderLevel.php','Entry and review of the re-order level of items by stocking location'),(114,'StockSerialItems.php','Shows a list of the serial numbers or the batch references and quantities of controlled items. This inquiry is linked from the stock status inquiry'),(115,'StockStatus.php','Shows the stock on hand together with outstanding sales orders and outstanding purchase orders by stock location for a selected part. Has a link to show the serial numbers in stock at the location selected if the item is controlled'),(116,'StockTransferControlled.php','Entry of serial numbers/batch references for controlled items being received on a stock transfer. The script is used by both bulk transfers and point to point transfers'),(117,'StockTransfers.php','Entry of point to point stock location transfers of a single part'),(118,'StockUsage.php','Inquiry showing the quantity of stock used by period calculated from the sum of the stock movements over that period - by item and stock location. Also available over all locations'),(119,'Stocks.php','Defines an item - maintenance and addition of new parts'),(120,'SuppCreditGRNs.php','Entry of a supplier credit notes (debit notes) against existing GRN which have already been matched in full or in part'),(121,'SuppInvGRNs.php','Entry of supplier invoices against goods received'),(122,'SuppPaymentRun.php','Automatic creation of payment records based on calculated amounts due from AP invoices entered'),(123,'SuppShiptChgs.php','Entry of supplier invoices against shipments as charges against a shipment'),(124,'SuppTransGLAnalysis.php','Entry of supplier invoices against general ledger codes'),(125,'SupplierAllocations.php','Entry of allocations of supplier payments and credit notes to invoices'),(126,'SupplierContacts.php','Entry of supplier contacts and contact details including email addresses'),(127,'SupplierCredit.php','Entry of supplier credit notes (debit notes)'),(128,'SupplierInquiry.php','Inquiry showing invoices, credit notes and payments made to suppliers together with the amounts outstanding'),(129,'SupplierInvoice.php','Entry of supplier invoices'),(130,'Suppliers.php','Entry of new suppliers and maintenance of existing suppliers'),(131,'TaxAuthorities.php','Entry of tax authorities - the state intitutions that charge tax'),(132,'TaxAuthorityRates.php','Entry of the rates of tax applicable to the tax authority depending on the item tax level'),(133,'WWW_Users.php','Entry of users and security settings of users'),(134,'WhereUsedInquiry.php','Inquiry showing where an item is used ie all the parents where the item is a component of'),(135,'WorkCentres.php','Defines the various centres of work within a manufacturing company. Also the overhead and labour rates applicable to the work centre and its standard capacity'),(136,'WorkOrderEntry.php','Entry of new work orders'),(137,'WorkOrderIssue.php','Issue of materials to a work order'),(138,'Z_ChangeBranchCode.php','Utility to change the branch code of a customer that cascades the change through all the necessary tables'),(139,'Z_ChangeCustomerCode.php','Utility to change a customer code that cascades the change through all the necessary tables'),(140,'Z_ChangeStockCode.php','Utility to change an item code that cascades the change through all the necessary tables'),(141,'Z_CheckAllocationsFrom.php',''),(142,'Z_CheckAllocs.php',''),(143,'Z_CheckDebtorsControl.php','Inquiry that shows the total local currency (functional currency) balance of all customer accounts to reconcile with the general ledger debtors account'),(144,'Z_CreateChartDetails.php','Utility page to create chart detail records for all general ledger accounts and periods created - needs expert assistance in use'),(145,'Z_CreateCompany.php','Utility to insert company number 1 if not already there - actually only company 1 is used - the system is not multi-company'),(146,'Z_CurrencyDebtorsBalances.php','Inquiry that shows the total foreign currency together with the total local currency (functional currency) balances of all customer accounts to reconcile with the general ledger debtors account'),(147,'Z_CurrencySuppliersBalances.php','Inquiry that shows the total foreign currency amounts and also the local currency (functional currency) balances of all supplier accounts to reconcile with the general ledger creditors account'),(148,'Z_DeleteCreditNote.php','Utility to reverse a customer credit note - a desperate measure that should not be used except in extreme circumstances'),(149,'Z_DeleteInvoice.php','Utility to reverse a customer invoice - a desperate measure that should not be used except in extreme circumstances'),(150,'Z_DeleteSalesTransActions.php','Utility to delete all sales transactions, sales analysis the lot! Extreme care required!!!'),(151,'Z_MakeStockLocns.php','Utility to make LocStock records for all items and locations if not already set up.'),(152,'Z_PriceChanges.php','Utility to make bulk pricing alterations to selected sales type price lists or selected customer prices only'),(153,'Z_ReApplyCostToSA.php','Utility to allow the sales analysis table to be updated with the latest cost information - the sales analysis takes the cost at the time the sale was made to reconcile with the enteries made in the gl.'),(154,'Z_RePostGLFromPeriod.php','Utility to repost all general ledger transaction commencing from a specified period. This can take some time in busy environments. Normally GL transactions are posted automatically each time a trial balance or profit and loss account is run'),(155,'Z_ReverseSuppPaymentRun.php','Utility to reverse an entire Supplier payment run'),(156,'Z_UpdateChartDetailsBFwd.php','Utility to recalculate the ChartDetails table B/Fwd balances - extreme care!!'),(157,'Z_UploadForm.php','Utility to upload a file to a remote server'),(158,'Z_UploadResult.php','Utility to upload a file to a remote server'),(159,'Z_index.php','Utility menu page'),(160,'index.php','The main menu from where all functions available to the user are accessed by clicking on the links'),(161,'phpinfo.php','Details about PHP installation on the server');
UNLOCK TABLES;
/*!40000 ALTER TABLE Scripts ENABLE KEYS */;

--
-- Dumping data for table `Shippers`
--


/*!40000 ALTER TABLE Shippers DISABLE KEYS */;
LOCK TABLES Shippers WRITE;
INSERT INTO Shippers VALUES (1,'Courier Post',0.0000),(8,'Ansett',0.0000),(10,'Not Specified',0.0000);
UNLOCK TABLES;
/*!40000 ALTER TABLE Shippers ENABLE KEYS */;

--
-- Dumping data for table `SysTypes`
--


/*!40000 ALTER TABLE SysTypes DISABLE KEYS */;
LOCK TABLES SysTypes WRITE;
INSERT INTO SysTypes VALUES (0,'Journal - GL',9),(1,'Payment - GL',17),(2,'Receipt - GL',3),(3,'Standing Journal',0),(10,'Sales Invoice',1),(11,'Credit Note',3),(12,'Receipt',1),(15,'Journal - Debtors',0),(16,'Location Transfer',1),(17,'Stock Adjustment',3),(18,'Purchase Order',0),(20,'Purchase Invoice',14),(21,'Debit Note',4),(22,'Creditors Payment',3),(23,'Creditors Journal',0),(25,'Purchase Order Delivery',12),(26,'Work Order Receipt',0),(28,'Work Order Issue',0),(29,'Work Order Variance',0),(30,'Sales Order',0),(31,'Shipment Close',3),(35,'Cost Update',3),(50,'Opening Balance',0);
UNLOCK TABLES;
/*!40000 ALTER TABLE SysTypes ENABLE KEYS */;

--
-- Dumping data for table `TaxAuthorities`
--


/*!40000 ALTER TABLE TaxAuthorities DISABLE KEYS */;
LOCK TABLES TaxAuthorities WRITE;
INSERT INTO TaxAuthorities VALUES (1,'Australian GST',810000,890000),(5,'Sales Tax',890000,890000),(6,'UK VAT',890000,890000);
UNLOCK TABLES;
/*!40000 ALTER TABLE TaxAuthorities ENABLE KEYS */;

--
-- Dumping data for table `TaxAuthLevels`
--


/*!40000 ALTER TABLE TaxAuthLevels DISABLE KEYS */;
LOCK TABLES TaxAuthLevels WRITE;
INSERT INTO TaxAuthLevels VALUES (1,1,1,0.1),(1,1,2,0),(1,5,1,0),(1,5,2,0),(1,6,1,0),(1,6,2,0),(5,1,1,0),(5,1,2,0),(5,5,1,0),(5,5,2,0),(5,6,1,0),(5,6,2,0),(6,1,1,0),(6,1,2,0),(6,5,1,0),(6,5,2,0),(6,6,1,0.175),(6,6,2,0);
UNLOCK TABLES;
/*!40000 ALTER TABLE TaxAuthLevels ENABLE KEYS */;

--
-- Dumping data for table `WWW_Users`
--


/*!40000 ALTER TABLE WWW_Users DISABLE KEYS */;
LOCK TABLES WWW_Users WRITE;
INSERT INTO WWW_Users VALUES ('Admin','weeping','Phil Daintree','','','','DEN',7,'2004-06-06 13:23:59','','A4','1,1,1,1,1,1,1,1,',0),('demo','weberp','Demonstration user','','','','DEN',7,'2004-07-10 17:23:42','','A4','1,1,1,1,1,1,1,1,',0),('testy','weberp','','GRANHR','','','DEN',6,'2004-02-23 20:21:56','GRAN','A4','1,0,0,0,0,0,0,0,',0);
UNLOCK TABLES;
/*!40000 ALTER TABLE WWW_Users ENABLE KEYS */;

--
-- Dumping data for table `EDI_ORDERS_Segs`
--


/*!40000 ALTER TABLE EDI_ORDERS_Segs DISABLE KEYS */;
LOCK TABLES EDI_ORDERS_Segs WRITE;
INSERT INTO EDI_ORDERS_Segs VALUES (1,'UNB',0,1),(2,'UNH',0,1),(3,'BGM',0,1),(4,'DTM',0,35),(5,'PAI',0,1),(6,'ALI',0,5),(7,'FTX',0,99),(8,'RFF',1,1),(9,'DTM',1,5),(10,'NAD',2,1),(11,'LOC',2,99),(12,'FII',2,5),(13,'RFF',3,1),(14,'CTA',5,1),(15,'COM',5,5),(16,'TAX',6,1),(17,'MOA',6,1),(18,'CUX',7,1),(19,'DTM',7,5),(20,'PAT',8,1),(21,'DTM',8,5),(22,'PCD',8,1),(23,'MOA',9,1),(24,'TDT',10,1),(25,'LOC',11,1),(26,'DTM',11,5),(27,'TOD',12,1),(28,'LOC',12,2),(29,'PAC',13,1),(30,'PCI',14,1),(31,'RFF',14,1),(32,'DTM',14,5),(33,'GIN',14,10),(34,'EQD',15,1),(35,'ALC',19,1),(36,'ALI',19,5),(37,'DTM',19,5),(38,'QTY',20,1),(39,'RNG',20,1),(40,'PCD',21,1),(41,'RNG',21,1),(42,'MOA',22,1),(43,'RNG',22,1),(44,'RTE',23,1),(45,'RNG',23,1),(46,'TAX',24,1),(47,'MOA',24,1),(48,'LIN',28,1),(49,'PIA',28,25),(50,'IMD',28,99),(51,'MEA',28,99),(52,'QTY',28,99),(53,'ALI',28,5),(54,'DTM',28,35),(55,'MOA',28,10),(56,'GIN',28,127),(57,'QVR',28,1),(58,'FTX',28,99),(59,'PRI',32,1),(60,'CUX',32,1),(61,'DTM',32,5),(62,'RFF',33,1),(63,'DTM',33,5),(64,'PAC',34,1),(65,'QTY',34,5),(66,'PCI',36,1),(67,'RFF',36,1),(68,'DTM',36,5),(69,'GIN',36,10),(70,'LOC',37,1),(71,'QTY',37,1),(72,'DTM',37,5),(73,'TAX',38,1),(74,'MOA',38,1),(75,'NAD',39,1),(76,'CTA',42,1),(77,'COM',42,5),(78,'ALC',43,1),(79,'ALI',43,5),(80,'DTM',43,5),(81,'QTY',44,1),(82,'RNG',44,1),(83,'PCD',45,1),(84,'RNG',45,1),(85,'MOA',46,1),(86,'RNG',46,1),(87,'RTE',47,1),(88,'RNG',47,1),(89,'TAX',48,1),(90,'MOA',48,1),(91,'TDT',49,1),(92,'UNS',50,1),(93,'MOA',50,1),(94,'CNT',50,1),(95,'UNT',50,1);
UNLOCK TABLES;
/*!40000 ALTER TABLE EDI_ORDERS_Segs ENABLE KEYS */;

--
-- Dumping data for table `EDI_ORDERS_Seg_Groups`
--


/*!40000 ALTER TABLE EDI_ORDERS_Seg_Groups DISABLE KEYS */;
LOCK TABLES EDI_ORDERS_Seg_Groups WRITE;
INSERT INTO EDI_ORDERS_Seg_Groups VALUES (0,1,0),(1,9999,0),(2,99,0),(3,99,2),(5,5,2),(6,5,0),(7,5,0),(8,10,0),(9,9999,8),(10,10,0),(11,10,10),(12,5,0),(13,99,0),(14,5,13),(15,10,0),(19,99,0),(20,1,19),(21,1,19),(22,2,19),(23,1,19),(24,5,19),(28,200000,0),(32,25,28),(33,9999,28),(34,99,28),(36,5,34),(37,9999,28),(38,10,28),(39,999,28),(42,5,39),(43,99,28),(44,1,43),(45,1,43),(46,2,43),(47,1,43),(48,5,43),(49,10,28),(50,1,0);
UNLOCK TABLES;
/*!40000 ALTER TABLE EDI_ORDERS_Seg_Groups ENABLE KEYS */;

SET FOREIGN_KEY_CHECKS = 1;
UPDATE SysTypes SET TypeNo=0;

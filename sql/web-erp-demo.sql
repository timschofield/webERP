SET FOREIGN_KEY_CHECKS = 0;
-- MySQL dump 9.11
--
-- Host: localhost    Database: weberp
-- ------------------------------------------------------
-- Server version	4.0.20-standard

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
  Consignment varchar(15) NOT NULL default '',
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
  StkCode varchar(20) NOT NULL default '',
  QtyInvoiced double(16,4) NOT NULL default '0.0000',
  UnitPrice double(16,4) NOT NULL default '0.0000',
  Quantity double(16,4) NOT NULL default '0.0000',
  Estimate tinyint(4) NOT NULL default '0',
  DiscountPercent double(16,4) NOT NULL default '0.0000',
  ActualDispatchDate datetime NOT NULL default '0000-00-00 00:00:00',
  Completed tinyint(1) NOT NULL default '0',
  Narrative text NOT NULL,
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
  StockID varchar(20) NOT NULL default '',
  Type smallint(6) NOT NULL default '0',
  TransNo int(11) NOT NULL default '0',
  LocCode varchar(5) NOT NULL default '',
  TranDate date NOT NULL default '0000-00-00',
  DebtorNo varchar(10) NOT NULL default '',
  BranchCode varchar(10) NOT NULL default '',
  Price decimal(20,4) NOT NULL default '0.0000',
  Prd smallint(6) NOT NULL default '0',
  Reference varchar(40) NOT NULL default '',
  Qty double(16,4) NOT NULL default '1.0000',
  DiscountPercent double(16,4) NOT NULL default '0.0000',
  StandardCost double(16,4) NOT NULL default '0.0000',
  Show_On_Inv_Crds tinyint(4) NOT NULL default '1',
  NewQOH double NOT NULL default '0',
  HideMovt tinyint(4) NOT NULL default '0',
  TaxRate float NOT NULL default '0',
  Narrative text NOT NULL,
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
  DisplayRecordsMax int(11) NOT NULL default '0',
  Theme varchar(30) NOT NULL default '''professional''',
  Language char(2) NOT NULL default 'en',
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

-- MySQL dump 9.11
--
-- Host: localhost    Database: weberp
-- ------------------------------------------------------
-- Server version	4.0.20-standard

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
INSERT INTO BankTrans VALUES (39,12,1,700100,'',0,2.354,'2004-07-17','Direct Credit',17.75,'GBP'),(40,22,4,700000,'BIGBISC-',0,0.5,'2004-07-29','Cheque',100,'GBP');
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
INSERT INTO ChartDetails VALUES (1,52,0,0,0,0),(1,53,0,0,0,0),(1,54,0,0,0,0),(1,55,0,-0.101179,0,0),(1,56,0,56.03,-0.101179,0),(1,57,0,-1.90735e-07,55.9288,0),(1,58,0,0,55.9288,0),(1,59,0,0,55.9288,0),(1,60,0,0,55.9288,0),(1,61,0,0,55.9288,0),(1,62,0,0,55.9288,0),(1,63,0,0,55.9288,0),(1,64,0,0,55.9288,0),(1,65,0,0,55.9288,0),(1,66,0,0,55.9288,0),(1,67,0,0,55.9288,0),(1,68,0,0,55.9288,0),(100,52,0,0,0,0),(100,53,0,0,0,0),(100,54,0,0,0,0),(100,55,0,0,0,0),(100,56,0,0,0,0),(100,57,0,0,0,0),(100,58,0,0,0,0),(100,59,0,0,0,0),(100,60,0,0,0,0),(100,61,0,0,0,0),(100,62,0,0,0,0),(100,63,0,0,0,0),(100,64,0,0,0,0),(100,65,0,0,0,0),(100,66,0,0,0,0),(100,67,0,0,0,0),(100,68,0,0,0,0),(107,52,0,0,0,0),(107,53,0,0,0,0),(107,54,0,0,0,0),(107,55,0,55.75,0,0),(107,56,0,-174.75,55.75,0),(107,57,0,40.7649,-119,0),(107,58,0,0,-78.2355,0),(107,59,0,0,-78.2355,0),(107,60,0,0,-78.2355,0),(107,61,0,0,-78.2355,0),(107,62,0,0,-78.2355,0),(107,63,0,0,-78.2355,0),(107,64,0,0,-78.2355,0),(107,65,0,0,-78.2355,0),(107,66,0,0,-78.2355,0),(107,67,0,0,-78.2355,0),(107,68,0,0,-78.2355,0),(112,52,0,0,0,0),(112,53,0,0,0,0),(112,54,0,0,0,0),(112,55,0,0,0,0),(112,56,0,189.75,0,0),(112,57,0,-25.3,189.75,0),(112,58,0,0,164.45,0),(112,59,0,0,164.45,0),(112,60,0,0,164.45,0),(112,61,0,0,164.45,0),(112,62,0,0,164.45,0),(112,63,0,0,164.45,0),(112,64,0,0,164.45,0),(112,65,0,0,164.45,0),(112,66,0,0,164.45,0),(112,67,0,0,164.45,0),(112,68,0,0,164.45,0),(200,52,0,0,0,0),(200,53,0,0,0,0),(200,54,0,0,0,0),(200,55,0,0,0,0),(200,56,0,0,0,0),(200,57,0,0,0,0),(200,58,0,0,0,0),(200,59,0,0,0,0),(200,60,0,0,0,0),(200,61,0,0,0,0),(200,62,0,0,0,0),(200,63,0,0,0,0),(200,64,0,0,0,0),(200,65,0,0,0,0),(200,66,0,0,0,0),(200,67,0,0,0,0),(200,68,0,0,0,0),(301,52,0,0,0,0),(301,53,0,0,0,0),(301,54,0,0,0,0),(301,55,0,0,0,0),(301,56,0,0,0,0),(301,57,0,0,0,0),(301,58,0,0,0,0),(301,59,0,0,0,0),(301,60,0,0,0,0),(301,61,0,0,0,0),(301,62,0,0,0,0),(301,63,0,0,0,0),(301,64,0,0,0,0),(301,65,0,0,0,0),(301,66,0,0,0,0),(301,67,0,0,0,0),(301,68,0,0,0,0),(10000,52,0,0,0,0),(10000,53,0,0,0,0),(10000,54,0,0,0,0),(10000,55,0,0,0,0),(10000,56,0,0,0,0),(10000,57,0,0,0,0),(10000,58,0,0,0,0),(10000,59,0,0,0,0),(10000,60,0,0,0,0),(10000,61,0,0,0,0),(10000,62,0,0,0,0),(10000,63,0,0,0,0),(10000,64,0,0,0,0),(10000,65,0,0,0,0),(10000,66,0,0,0,0),(10000,67,0,0,0,0),(10000,68,0,0,0,0),(11000,52,0,0,0,0),(11000,53,0,0,0,0),(11000,54,0,0,0,0),(11000,55,0,0,0,0),(11000,56,0,0,0,0),(11000,57,0,0,0,0),(11000,58,0,0,0,0),(11000,59,0,0,0,0),(11000,60,0,0,0,0),(11000,61,0,0,0,0),(11000,62,0,0,0,0),(11000,63,0,0,0,0),(11000,64,0,0,0,0),(11000,65,0,0,0,0),(11000,66,0,0,0,0),(11000,67,0,0,0,0),(11000,68,0,0,0,0),(12000,52,0,0,0,0),(12000,53,0,0,0,0),(12000,54,0,0,0,0),(12000,55,0,0,0,0),(12000,56,0,0,0,0),(12000,57,0,0,0,0),(12000,58,0,0,0,0),(12000,59,0,0,0,0),(12000,60,0,0,0,0),(12000,61,0,0,0,0),(12000,62,0,0,0,0),(12000,63,0,0,0,0),(12000,64,0,0,0,0),(12000,65,0,0,0,0),(12000,66,0,0,0,0),(12000,67,0,0,0,0),(12000,68,0,0,0,0),(19000,52,0,0,0,0),(19000,53,0,0,0,0),(19000,54,0,0,0,0),(19000,55,0,0,0,0),(19000,56,0,0,0,0),(19000,57,0,0,0,0),(19000,58,0,0,0,0),(19000,59,0,0,0,0),(19000,60,0,0,0,0),(19000,61,0,0,0,0),(19000,62,0,0,0,0),(19000,63,0,0,0,0),(19000,64,0,0,0,0),(19000,65,0,0,0,0),(19000,66,0,0,0,0),(19000,67,0,0,0,0),(19000,68,0,0,0,0),(20000,52,0,0,0,0),(20000,53,0,0,0,0),(20000,54,0,0,0,0),(20000,55,0,0,0,0),(20000,56,0,0,0,0),(20000,57,0,0,0,0),(20000,58,0,0,0,0),(20000,59,0,0,0,0),(20000,60,0,0,0,0),(20000,61,0,0,0,0),(20000,62,0,0,0,0),(20000,63,0,0,0,0),(20000,64,0,0,0,0),(20000,65,0,0,0,0),(20000,66,0,0,0,0),(20000,67,0,0,0,0),(20000,68,0,0,0,0),(21000,52,0,0,0,0),(21000,53,0,0,0,0),(21000,54,0,0,0,0),(21000,55,0,0,0,0),(21000,56,0,0,0,0),(21000,57,0,0,0,0),(21000,58,0,0,0,0),(21000,59,0,0,0,0),(21000,60,0,0,0,0),(21000,61,0,0,0,0),(21000,62,0,0,0,0),(21000,63,0,0,0,0),(21000,64,0,0,0,0),(21000,65,0,0,0,0),(21000,66,0,0,0,0),(21000,67,0,0,0,0),(21000,68,0,0,0,0),(23400,52,0,0,0,0),(23400,53,0,0,0,0),(23400,54,0,0,0,0),(23400,55,0,0,0,0),(23400,56,0,0,0,0),(23400,57,0,0,0,0),(23400,58,0,0,0,0),(23400,59,0,0,0,0),(23400,60,0,0,0,0),(23400,61,0,0,0,0),(23400,62,0,0,0,0),(23400,63,0,0,0,0),(23400,64,0,0,0,0),(23400,65,0,0,0,0),(23400,66,0,0,0,0),(23400,67,0,0,0,0),(23400,68,0,0,0,0),(23500,52,0,0,0,0),(23500,53,0,0,0,0),(23500,54,0,0,0,0),(23500,55,0,0,0,0),(23500,56,0,0,0,0),(23500,57,0,0,0,0),(23500,58,0,0,0,0),(23500,59,0,0,0,0),(23500,60,0,0,0,0),(23500,61,0,0,0,0),(23500,62,0,0,0,0),(23500,63,0,0,0,0),(23500,64,0,0,0,0),(23500,65,0,0,0,0),(23500,66,0,0,0,0),(23500,67,0,0,0,0),(23500,68,0,0,0,0),(23600,52,0,0,0,0),(23600,53,0,0,0,0),(23600,54,0,0,0,0),(23600,55,0,0,0,0),(23600,56,0,0,0,0),(23600,57,0,0,0,0),(23600,58,0,0,0,0),(23600,59,0,0,0,0),(23600,60,0,0,0,0),(23600,61,0,0,0,0),(23600,62,0,0,0,0),(23600,63,0,0,0,0),(23600,64,0,0,0,0),(23600,65,0,0,0,0),(23600,66,0,0,0,0),(23600,67,0,0,0,0),(23600,68,0,0,0,0),(23700,52,0,0,0,0),(23700,53,0,0,0,0),(23700,54,0,0,0,0),(23700,55,0,0,0,0),(23700,56,0,0,0,0),(23700,57,0,0,0,0),(23700,58,0,0,0,0),(23700,59,0,0,0,0),(23700,60,0,0,0,0),(23700,61,0,0,0,0),(23700,62,0,0,0,0),(23700,63,0,0,0,0),(23700,64,0,0,0,0),(23700,65,0,0,0,0),(23700,66,0,0,0,0),(23700,67,0,0,0,0),(23700,68,0,0,0,0),(24000,52,0,0,0,0),(24000,53,0,0,0,0),(24000,54,0,0,0,0),(24000,55,0,0,0,0),(24000,56,0,0,0,0),(24000,57,0,0,0,0),(24000,58,0,0,0,0),(24000,59,0,0,0,0),(24000,60,0,0,0,0),(24000,61,0,0,0,0),(24000,62,0,0,0,0),(24000,63,0,0,0,0),(24000,64,0,0,0,0),(24000,65,0,0,0,0),(24000,66,0,0,0,0),(24000,67,0,0,0,0),(24000,68,0,0,0,0),(70100,52,0,0,0,0),(70100,53,0,0,0,0),(70100,54,0,0,0,0),(70100,55,0,0,0,0),(70100,56,0,0,0,0),(70100,57,0,0,0,0),(70100,58,0,0,0,0),(70100,59,0,0,0,0),(70100,60,0,0,0,0),(70100,61,0,0,0,0),(70100,62,0,0,0,0),(70100,63,0,0,0,0),(70100,64,0,0,0,0),(70100,65,0,0,0,0),(70100,66,0,0,0,0),(70100,67,0,0,0,0),(70100,68,0,0,0,0),(70200,52,0,0,0,0),(70200,53,0,0,0,0),(70200,54,0,0,0,0),(70200,55,0,0,0,0),(70200,56,0,0,0,0),(70200,57,0,0,0,0),(70200,58,0,0,0,0),(70200,59,0,0,0,0),(70200,60,0,0,0,0),(70200,61,0,0,0,0),(70200,62,0,0,0,0),(70200,63,0,0,0,0),(70200,64,0,0,0,0),(70200,65,0,0,0,0),(70200,66,0,0,0,0),(70200,67,0,0,0,0),(70200,68,0,0,0,0),(70300,52,0,0,0,0),(70300,53,0,0,0,0),(70300,54,0,0,0,0),(70300,55,0,0,0,0),(70300,56,0,0,0,0),(70300,57,0,0,0,0),(70300,58,0,0,0,0),(70300,59,0,0,0,0),(70300,60,0,0,0,0),(70300,61,0,0,0,0),(70300,62,0,0,0,0),(70300,63,0,0,0,0),(70300,64,0,0,0,0),(70300,65,0,0,0,0),(70300,66,0,0,0,0),(70300,67,0,0,0,0),(70300,68,0,0,0,0),(210000,52,0,0,0,0),(210000,53,0,0,0,0),(210000,54,0,0,0,0),(210000,55,0,0,0,0),(210000,56,0,0,0,0),(210000,57,0,0,0,0),(210000,58,0,0,0,0),(210000,59,0,0,0,0),(210000,60,0,0,0,0),(210000,61,0,0,0,0),(210000,62,0,0,0,0),(210000,63,0,0,0,0),(210000,64,0,0,0,0),(210000,65,0,0,0,0),(210000,66,0,0,0,0),(210000,67,0,0,0,0),(210000,68,0,0,0,0),(250000,52,0,0,0,0),(250000,53,0,0,0,0),(250000,54,0,0,0,0),(250000,55,0,0,0,0),(250000,56,0,0,0,0),(250000,57,0,0,0,0),(250000,58,0,0,0,0),(250000,59,0,0,0,0),(250000,60,0,0,0,0),(250000,61,0,0,0,0),(250000,62,0,0,0,0),(250000,63,0,0,0,0),(250000,64,0,0,0,0),(250000,65,0,0,0,0),(250000,66,0,0,0,0),(250000,67,0,0,0,0),(250000,68,0,0,0,0),(251000,52,0,0,0,0),(251000,53,0,0,0,0),(251000,54,0,0,0,0),(251000,55,0,0,0,0),(251000,56,0,0,0,0),(251000,57,0,0,0,0),(251000,58,0,0,0,0),(251000,59,0,0,0,0),(251000,60,0,0,0,0),(251000,61,0,0,0,0),(251000,62,0,0,0,0),(251000,63,0,0,0,0),(251000,64,0,0,0,0),(251000,65,0,0,0,0),(251000,66,0,0,0,0),(251000,67,0,0,0,0),(251000,68,0,0,0,0),(252000,52,0,0,0,0),(252000,53,0,0,0,0),(252000,54,0,0,0,0),(252000,55,0,0,0,0),(252000,56,0,0,0,0),(252000,57,0,0,0,0),(252000,58,0,0,0,0),(252000,59,0,0,0,0),(252000,60,0,0,0,0),(252000,61,0,0,0,0),(252000,62,0,0,0,0),(252000,63,0,0,0,0),(252000,64,0,0,0,0),(252000,65,0,0,0,0),(252000,66,0,0,0,0),(252000,67,0,0,0,0),(252000,68,0,0,0,0),(253000,52,0,0,0,0),(253000,53,0,0,0,0),(253000,54,0,0,0,0),(253000,55,0,0,0,0),(253000,56,0,0,0,0),(253000,57,0,0,0,0),(253000,58,0,0,0,0),(253000,59,0,0,0,0),(253000,60,0,0,0,0),(253000,61,0,0,0,0),(253000,62,0,0,0,0),(253000,63,0,0,0,0),(253000,64,0,0,0,0),(253000,65,0,0,0,0),(253000,66,0,0,0,0),(253000,67,0,0,0,0),(253000,68,0,0,0,0),(255000,52,0,0,0,0),(255000,53,0,0,0,0),(255000,54,0,0,0,0),(255000,55,0,0,0,0),(255000,56,0,0,0,0),(255000,57,0,0,0,0),(255000,58,0,0,0,0),(255000,59,0,0,0,0),(255000,60,0,0,0,0),(255000,61,0,0,0,0),(255000,62,0,0,0,0),(255000,63,0,0,0,0),(255000,64,0,0,0,0),(255000,65,0,0,0,0),(255000,66,0,0,0,0),(255000,67,0,0,0,0),(255000,68,0,0,0,0),(256000,52,0,0,0,0),(256000,53,0,0,0,0),(256000,54,0,0,0,0),(256000,55,0,0,0,0),(256000,56,0,-145.35,0,0),(256000,57,0,19.38,-145.35,0),(256000,58,0,0,-125.97,0),(256000,59,0,0,-125.97,0),(256000,60,0,0,-125.97,0),(256000,61,0,0,-125.97,0),(256000,62,0,0,-125.97,0),(256000,63,0,0,-125.97,0),(256000,64,0,0,-125.97,0),(256000,65,0,0,-125.97,0),(256000,66,0,0,-125.97,0),(256000,67,0,0,-125.97,0),(256000,68,0,0,-125.97,0),(257000,52,0,0,0,0),(257000,53,0,0,0,0),(257000,54,0,0,0,0),(257000,55,0,0,0,0),(257000,56,0,0,0,0),(257000,57,0,0,0,0),(257000,58,0,0,0,0),(257000,59,0,0,0,0),(257000,60,0,0,0,0),(257000,61,0,0,0,0),(257000,62,0,0,0,0),(257000,63,0,0,0,0),(257000,64,0,0,0,0),(257000,65,0,0,0,0),(257000,66,0,0,0,0),(257000,67,0,0,0,0),(257000,68,0,0,0,0),(258000,52,0,0,0,0),(258000,53,0,0,0,0),(258000,54,0,0,0,0),(258000,55,0,0,0,0),(258000,56,0,0,0,0),(258000,57,0,0,0,0),(258000,58,0,0,0,0),(258000,59,0,0,0,0),(258000,60,0,0,0,0),(258000,61,0,0,0,0),(258000,62,0,0,0,0),(258000,63,0,0,0,0),(258000,64,0,0,0,0),(258000,65,0,0,0,0),(258000,66,0,0,0,0),(258000,67,0,0,0,0),(258000,68,0,0,0,0),(259000,52,0,0,0,0),(259000,53,0,0,0,0),(259000,54,0,0,0,0),(259000,55,0,-2.23025,0,0),(259000,56,0,0,-2.23025,0),(259000,57,0,0,-2.23025,0),(259000,58,0,0,-2.23025,0),(259000,59,0,0,-2.23025,0),(259000,60,0,0,-2.23025,0),(259000,61,0,0,-2.23025,0),(259000,62,0,0,-2.23025,0),(259000,63,0,0,-2.23025,0),(259000,64,0,0,-2.23025,0),(259000,65,0,0,-2.23025,0),(259000,66,0,0,-2.23025,0),(259000,67,0,0,-2.23025,0),(259000,68,0,0,-2.23025,0),(261000,52,0,0,0,0),(261000,53,0,0,0,0),(261000,54,0,0,0,0),(261000,55,0,0,0,0),(261000,56,0,0,0,0),(261000,57,0,0,0,0),(261000,58,0,0,0,0),(261000,59,0,0,0,0),(261000,60,0,0,0,0),(261000,61,0,0,0,0),(261000,62,0,0,0,0),(261000,63,0,0,0,0),(261000,64,0,0,0,0),(261000,65,0,0,0,0),(261000,66,0,0,0,0),(261000,67,0,0,0,0),(261000,68,0,0,0,0),(262000,52,0,0,0,0),(262000,53,0,0,0,0),(262000,54,0,0,0,0),(262000,55,0,0,0,0),(262000,56,0,0,0,0),(262000,57,0,0,0,0),(262000,58,0,0,0,0),(262000,59,0,0,0,0),(262000,60,0,0,0,0),(262000,61,0,0,0,0),(262000,62,0,0,0,0),(262000,63,0,0,0,0),(262000,64,0,0,0,0),(262000,65,0,0,0,0),(262000,66,0,0,0,0),(262000,67,0,0,0,0),(262000,68,0,0,0,0),(263000,52,0,0,0,0),(263000,53,0,0,0,0),(263000,54,0,0,0,0),(263000,55,0,0,0,0),(263000,56,0,0,0,0),(263000,57,0,0,0,0),(263000,58,0,0,0,0),(263000,59,0,0,0,0),(263000,60,0,0,0,0),(263000,61,0,0,0,0),(263000,62,0,0,0,0),(263000,63,0,0,0,0),(263000,64,0,0,0,0),(263000,65,0,0,0,0),(263000,66,0,0,0,0),(263000,67,0,0,0,0),(263000,68,0,0,0,0),(263100,52,0,0,0,0),(263100,53,0,0,0,0),(263100,54,0,0,0,0),(263100,55,0,0,0,0),(263100,56,0,0,0,0),(263100,57,0,0,0,0),(263100,58,0,0,0,0),(263100,59,0,0,0,0),(263100,60,0,0,0,0),(263100,61,0,0,0,0),(263100,62,0,0,0,0),(263100,63,0,0,0,0),(263100,64,0,0,0,0),(263100,65,0,0,0,0),(263100,66,0,0,0,0),(263100,67,0,0,0,0),(263100,68,0,0,0,0),(264000,52,0,0,0,0),(264000,53,0,0,0,0),(264000,54,0,0,0,0),(264000,55,0,0,0,0),(264000,56,0,0,0,0),(264000,57,0,0,0,0),(264000,58,0,0,0,0),(264000,59,0,0,0,0),(264000,60,0,0,0,0),(264000,61,0,0,0,0),(264000,62,0,0,0,0),(264000,63,0,0,0,0),(264000,64,0,0,0,0),(264000,65,0,0,0,0),(264000,66,0,0,0,0),(264000,67,0,0,0,0),(264000,68,0,0,0,0),(265000,52,0,0,0,0),(265000,53,0,0,0,0),(265000,54,0,0,0,0),(265000,55,0,0,0,0),(265000,56,0,0,0,0),(265000,57,0,0,0,0),(265000,58,0,0,0,0),(265000,59,0,0,0,0),(265000,60,0,0,0,0),(265000,61,0,0,0,0),(265000,62,0,0,0,0),(265000,63,0,0,0,0),(265000,64,0,0,0,0),(265000,65,0,0,0,0),(265000,66,0,0,0,0),(265000,67,0,0,0,0),(265000,68,0,0,0,0),(266000,52,0,0,0,0),(266000,53,0,0,0,0),(266000,54,0,0,0,0),(266000,55,0,0,0,0),(266000,56,0,0,0,0),(266000,57,0,0,0,0),(266000,58,0,0,0,0),(266000,59,0,0,0,0),(266000,60,0,0,0,0),(266000,61,0,0,0,0),(266000,62,0,0,0,0),(266000,63,0,0,0,0),(266000,64,0,0,0,0),(266000,65,0,0,0,0),(266000,66,0,0,0,0),(266000,67,0,0,0,0),(266000,68,0,0,0,0),(267000,52,0,0,0,0),(267000,53,0,0,0,0),(267000,54,0,0,0,0),(267000,55,0,0,0,0),(267000,56,0,0,0,0),(267000,57,0,0,0,0),(267000,58,0,0,0,0),(267000,59,0,0,0,0),(267000,60,0,0,0,0),(267000,61,0,0,0,0),(267000,62,0,0,0,0),(267000,63,0,0,0,0),(267000,64,0,0,0,0),(267000,65,0,0,0,0),(267000,66,0,0,0,0),(267000,67,0,0,0,0),(267000,68,0,0,0,0),(267100,52,0,0,0,0),(267100,53,0,0,0,0),(267100,54,0,0,0,0),(267100,55,0,0,0,0),(267100,56,0,0,0,0),(267100,57,0,0,0,0),(267100,58,0,0,0,0),(267100,59,0,0,0,0),(267100,60,0,0,0,0),(267100,61,0,0,0,0),(267100,62,0,0,0,0),(267100,63,0,0,0,0),(267100,64,0,0,0,0),(267100,65,0,0,0,0),(267100,66,0,0,0,0),(267100,67,0,0,0,0),(267100,68,0,0,0,0),(270000,52,0,0,0,0),(270000,53,0,0,0,0),(270000,54,0,0,0,0),(270000,55,0,0,0,0),(270000,56,0,0,0,0),(270000,57,0,0,0,0),(270000,58,0,0,0,0),(270000,59,0,0,0,0),(270000,60,0,0,0,0),(270000,61,0,0,0,0),(270000,62,0,0,0,0),(270000,63,0,0,0,0),(270000,64,0,0,0,0),(270000,65,0,0,0,0),(270000,66,0,0,0,0),(270000,67,0,0,0,0),(270000,68,0,0,0,0),(271000,52,0,0,0,0),(271000,53,0,0,0,0),(271000,54,0,0,0,0),(271000,55,0,0,0,0),(271000,56,0,0,0,0),(271000,57,0,0,0,0),(271000,58,0,0,0,0),(271000,59,0,0,0,0),(271000,60,0,0,0,0),(271000,61,0,0,0,0),(271000,62,0,0,0,0),(271000,63,0,0,0,0),(271000,64,0,0,0,0),(271000,65,0,0,0,0),(271000,66,0,0,0,0),(271000,67,0,0,0,0),(271000,68,0,0,0,0),(310000,52,0,0,0,0),(310000,53,0,0,0,0),(310000,54,0,0,0,0),(310000,55,0,0,0,0),(310000,56,0,0,0,0),(310000,57,0,0,0,0),(310000,58,0,0,0,0),(310000,59,0,0,0,0),(310000,60,0,0,0,0),(310000,61,0,0,0,0),(310000,62,0,0,0,0),(310000,63,0,0,0,0),(310000,64,0,0,0,0),(310000,65,0,0,0,0),(310000,66,0,0,0,0),(310000,67,0,0,0,0),(310000,68,0,0,0,0),(320000,52,0,0,0,0),(320000,53,0,0,0,0),(320000,54,0,0,0,0),(320000,55,0,0,0,0),(320000,56,0,0,0,0),(320000,57,0,0,0,0),(320000,58,0,0,0,0),(320000,59,0,0,0,0),(320000,60,0,0,0,0),(320000,61,0,0,0,0),(320000,62,0,0,0,0),(320000,63,0,0,0,0),(320000,64,0,0,0,0),(320000,65,0,0,0,0),(320000,66,0,0,0,0),(320000,67,0,0,0,0),(320000,68,0,0,0,0),(330000,52,0,0,0,0),(330000,53,0,0,0,0),(330000,54,0,0,0,0),(330000,55,0,0,0,0),(330000,56,0,0,0,0),(330000,57,0,0,0,0),(330000,58,0,0,0,0),(330000,59,0,0,0,0),(330000,60,0,0,0,0),(330000,61,0,0,0,0),(330000,62,0,0,0,0),(330000,63,0,0,0,0),(330000,64,0,0,0,0),(330000,65,0,0,0,0),(330000,66,0,0,0,0),(330000,67,0,0,0,0),(330000,68,0,0,0,0),(340000,52,0,0,0,0),(340000,53,0,0,0,0),(340000,54,0,0,0,0),(340000,55,0,0,0,0),(340000,56,0,0,0,0),(340000,57,0,0,0,0),(340000,58,0,0,0,0),(340000,59,0,0,0,0),(340000,60,0,0,0,0),(340000,61,0,0,0,0),(340000,62,0,0,0,0),(340000,63,0,0,0,0),(340000,64,0,0,0,0),(340000,65,0,0,0,0),(340000,66,0,0,0,0),(340000,67,0,0,0,0),(340000,68,0,0,0,0),(342000,52,0,0,0,0),(342000,53,0,0,0,0),(342000,54,0,0,0,0),(342000,55,0,0,0,0),(342000,56,0,0,0,0),(342000,57,0,0,0,0),(342000,58,0,0,0,0),(342000,59,0,0,0,0),(342000,60,0,0,0,0),(342000,61,0,0,0,0),(342000,62,0,0,0,0),(342000,63,0,0,0,0),(342000,64,0,0,0,0),(342000,65,0,0,0,0),(342000,66,0,0,0,0),(342000,67,0,0,0,0),(342000,68,0,0,0,0),(400000,52,0,0,0,0),(400000,53,0,0,0,0),(400000,54,0,0,0,0),(400000,55,0,0,0,0),(400000,56,0,0,0,0),(400000,57,0,0,0,0),(400000,58,0,0,0,0),(400000,59,0,0,0,0),(400000,60,0,0,0,0),(400000,61,0,0,0,0),(400000,62,0,0,0,0),(400000,63,0,0,0,0),(400000,64,0,0,0,0),(400000,65,0,0,0,0),(400000,66,0,0,0,0),(400000,67,0,0,0,0),(400000,68,0,0,0,0),(410000,52,0,0,0,0),(410000,53,0,0,0,0),(410000,54,0,0,0,0),(410000,55,0,0,0,0),(410000,56,0,0,0,0),(410000,57,0,0,0,0),(410000,58,0,0,0,0),(410000,59,0,0,0,0),(410000,60,0,0,0,0),(410000,61,0,0,0,0),(410000,62,0,0,0,0),(410000,63,0,0,0,0),(410000,64,0,0,0,0),(410000,65,0,0,0,0),(410000,66,0,0,0,0),(410000,67,0,0,0,0),(410000,68,0,0,0,0),(411000,52,0,0,0,0),(411000,53,0,0,0,0),(411000,54,0,0,0,0),(411000,55,0,0,0,0),(411000,56,0,0,0,0),(411000,57,0,0,0,0),(411000,58,0,0,0,0),(411000,59,0,0,0,0),(411000,60,0,0,0,0),(411000,61,0,0,0,0),(411000,62,0,0,0,0),(411000,63,0,0,0,0),(411000,64,0,0,0,0),(411000,65,0,0,0,0),(411000,66,0,0,0,0),(411000,67,0,0,0,0),(411000,68,0,0,0,0),(412000,52,0,0,0,0),(412000,53,0,0,0,0),(412000,54,0,0,0,0),(412000,55,0,0,0,0),(412000,56,0,0,0,0),(412000,57,0,0,0,0),(412000,58,0,0,0,0),(412000,59,0,0,0,0),(412000,60,0,0,0,0),(412000,61,0,0,0,0),(412000,62,0,0,0,0),(412000,63,0,0,0,0),(412000,64,0,0,0,0),(412000,65,0,0,0,0),(412000,66,0,0,0,0),(412000,67,0,0,0,0),(412000,68,0,0,0,0),(413000,52,0,0,0,0),(413000,53,0,0,0,0),(413000,54,0,0,0,0),(413000,55,0,0,0,0),(413000,56,0,0,0,0),(413000,57,0,0,0,0),(413000,58,0,0,0,0),(413000,59,0,0,0,0),(413000,60,0,0,0,0),(413000,61,0,0,0,0),(413000,62,0,0,0,0),(413000,63,0,0,0,0),(413000,64,0,0,0,0),(413000,65,0,0,0,0),(413000,66,0,0,0,0),(413000,67,0,0,0,0),(413000,68,0,0,0,0),(420000,52,0,0,0,0),(420000,53,0,0,0,0),(420000,54,0,0,0,0),(420000,55,0,0,0,0),(420000,56,0,0,0,0),(420000,57,0,0,0,0),(420000,58,0,0,0,0),(420000,59,0,0,0,0),(420000,60,0,0,0,0),(420000,61,0,0,0,0),(420000,62,0,0,0,0),(420000,63,0,0,0,0),(420000,64,0,0,0,0),(420000,65,0,0,0,0),(420000,66,0,0,0,0),(420000,67,0,0,0,0),(420000,68,0,0,0,0),(450000,52,0,0,0,0),(450000,53,0,0,0,0),(450000,54,0,0,0,0),(450000,55,0,0,0,0),(450000,56,0,0,0,0),(450000,57,0,0,0,0),(450000,58,0,0,0,0),(450000,59,0,0,0,0),(450000,60,0,0,0,0),(450000,61,0,0,0,0),(450000,62,0,0,0,0),(450000,63,0,0,0,0),(450000,64,0,0,0,0),(450000,65,0,0,0,0),(450000,66,0,0,0,0),(450000,67,0,0,0,0),(450000,68,0,0,0,0),(451000,52,0,0,0,0),(451000,53,0,0,0,0),(451000,54,0,0,0,0),(451000,55,0,0,0,0),(451000,56,0,0,0,0),(451000,57,0,0,0,0),(451000,58,0,0,0,0),(451000,59,0,0,0,0),(451000,60,0,0,0,0),(451000,61,0,0,0,0),(451000,62,0,0,0,0),(451000,63,0,0,0,0),(451000,64,0,0,0,0),(451000,65,0,0,0,0),(451000,66,0,0,0,0),(451000,67,0,0,0,0),(451000,68,0,0,0,0),(452000,52,0,0,0,0),(452000,53,0,0,0,0),(452000,54,0,0,0,0),(452000,55,0,0,0,0),(452000,56,0,0,0,0),(452000,57,0,0,0,0),(452000,58,0,0,0,0),(452000,59,0,0,0,0),(452000,60,0,0,0,0),(452000,61,0,0,0,0),(452000,62,0,0,0,0),(452000,63,0,0,0,0),(452000,64,0,0,0,0),(452000,65,0,0,0,0),(452000,66,0,0,0,0),(452000,67,0,0,0,0),(452000,68,0,0,0,0),(455000,52,0,0,0,0),(455000,53,0,0,0,0),(455000,54,0,0,0,0),(455000,55,0,0.140187,0,0),(455000,56,0,0,0.140187,0),(455000,57,0,0,0.140187,0),(455000,58,0,0,0.140187,0),(455000,59,0,0,0.140187,0),(455000,60,0,0,0.140187,0),(455000,61,0,0,0.140187,0),(455000,62,0,0,0.140187,0),(455000,63,0,0,0.140187,0),(455000,64,0,0,0.140187,0),(455000,65,0,0,0.140187,0),(455000,66,0,0,0.140187,0),(455000,67,0,0,0.140187,0),(455000,68,0,0,0.140187,0),(460000,52,0,0,0,0),(460000,53,0,0,0,0),(460000,54,0,0,0,0),(460000,55,0,0,0,0),(460000,56,0,0,0,0),(460000,57,0,0,0,0),(460000,58,0,0,0,0),(460000,59,0,0,0,0),(460000,60,0,0,0,0),(460000,61,0,0,0,0),(460000,62,0,0,0,0),(460000,63,0,0,0,0),(460000,64,0,0,0,0),(460000,65,0,0,0,0),(460000,66,0,0,0,0),(460000,67,0,0,0,0),(460000,68,0,0,0,0),(470000,52,0,0,0,0),(470000,53,0,0,0,0),(470000,54,0,0,0,0),(470000,55,0,0,0,0),(470000,56,0,0,0,0),(470000,57,0,0,0,0),(470000,58,0,0,0,0),(470000,59,0,0,0,0),(470000,60,0,0,0,0),(470000,61,0,0,0,0),(470000,62,0,0,0,0),(470000,63,0,0,0,0),(470000,64,0,0,0,0),(470000,65,0,0,0,0),(470000,66,0,0,0,0),(470000,67,0,0,0,0),(470000,68,0,0,0,0),(480000,52,0,0,0,0),(480000,53,0,0,0,0),(480000,54,0,0,0,0),(480000,55,0,0,0,0),(480000,56,0,0,0,0),(480000,57,0,0,0,0),(480000,58,0,0,0,0),(480000,59,0,0,0,0),(480000,60,0,0,0,0),(480000,61,0,0,0,0),(480000,62,0,0,0,0),(480000,63,0,0,0,0),(480000,64,0,0,0,0),(480000,65,0,0,0,0),(480000,66,0,0,0,0),(480000,67,0,0,0,0),(480000,68,0,0,0,0),(481000,52,0,0,0,0),(481000,53,0,0,0,0),(481000,54,0,0,0,0),(481000,55,0,0,0,0),(481000,56,0,0,0,0),(481000,57,0,0,0,0),(481000,58,0,0,0,0),(481000,59,0,0,0,0),(481000,60,0,0,0,0),(481000,61,0,0,0,0),(481000,62,0,0,0,0),(481000,63,0,0,0,0),(481000,64,0,0,0,0),(481000,65,0,0,0,0),(481000,66,0,0,0,0),(481000,67,0,0,0,0),(481000,68,0,0,0,0),(500000,52,0,0,0,0),(500000,53,0,0,0,0),(500000,54,0,0,0,0),(500000,55,0,0,0,0),(500000,56,0,0,0,0),(500000,57,0,0,0,0),(500000,58,0,0,0,0),(500000,59,0,0,0,0),(500000,60,0,0,0,0),(500000,61,0,0,0,0),(500000,62,0,0,0,0),(500000,63,0,0,0,0),(500000,64,0,0,0,0),(500000,65,0,0,0,0),(500000,66,0,0,0,0),(500000,67,0,0,0,0),(500000,68,0,0,0,0),(501000,52,0,0,0,0),(501000,53,0,0,0,0),(501000,54,0,0,0,0),(501000,55,0,0,0,0),(501000,56,0,0,0,0),(501000,57,0,0,0,0),(501000,58,0,0,0,0),(501000,59,0,0,0,0),(501000,60,0,0,0,0),(501000,61,0,0,0,0),(501000,62,0,0,0,0),(501000,63,0,0,0,0),(501000,64,0,0,0,0),(501000,65,0,0,0,0),(501000,66,0,0,0,0),(501000,67,0,0,0,0),(501000,68,0,0,0,0),(501800,52,0,0,0,0),(501800,53,0,0,0,0),(501800,54,0,0,0,0),(501800,55,0,0,0,0),(501800,56,0,0,0,0),(501800,57,0,0,0,0),(501800,58,0,0,0,0),(501800,59,0,0,0,0),(501800,60,0,0,0,0),(501800,61,0,0,0,0),(501800,62,0,0,0,0),(501800,63,0,0,0,0),(501800,64,0,0,0,0),(501800,65,0,0,0,0),(501800,66,0,0,0,0),(501800,67,0,0,0,0),(501800,68,0,0,0,0),(501900,52,0,0,0,0),(501900,53,0,0,0,0),(501900,54,0,0,0,0),(501900,55,0,0,0,0),(501900,56,0,0,0,0),(501900,57,0,0,0,0),(501900,58,0,0,0,0),(501900,59,0,0,0,0),(501900,60,0,0,0,0),(501900,61,0,0,0,0),(501900,62,0,0,0,0),(501900,63,0,0,0,0),(501900,64,0,0,0,0),(501900,65,0,0,0,0),(501900,66,0,0,0,0),(501900,67,0,0,0,0),(501900,68,0,0,0,0),(502000,52,0,0,0,0),(502000,53,0,0,0,0),(502000,54,0,0,0,0),(502000,55,0,0,0,0),(502000,56,0,0,0,0),(502000,57,0,0,0,0),(502000,58,0,0,0,0),(502000,59,0,0,0,0),(502000,60,0,0,0,0),(502000,61,0,0,0,0),(502000,62,0,0,0,0),(502000,63,0,0,0,0),(502000,64,0,0,0,0),(502000,65,0,0,0,0),(502000,66,0,0,0,0),(502000,67,0,0,0,0),(502000,68,0,0,0,0),(502100,52,0,0,0,0),(502100,53,0,0,0,0),(502100,54,0,0,0,0),(502100,55,0,0,0,0),(502100,56,0,0,0,0),(502100,57,0,0,0,0),(502100,58,0,0,0,0),(502100,59,0,0,0,0),(502100,60,0,0,0,0),(502100,61,0,0,0,0),(502100,62,0,0,0,0),(502100,63,0,0,0,0),(502100,64,0,0,0,0),(502100,65,0,0,0,0),(502100,66,0,0,0,0),(502100,67,0,0,0,0),(502100,68,0,0,0,0),(503000,52,0,0,0,0),(503000,53,0,0,0,0),(503000,54,0,0,0,0),(503000,55,0,0,0,0),(503000,56,0,0,0,0),(503000,57,0,0,0,0),(503000,58,0,0,0,0),(503000,59,0,0,0,0),(503000,60,0,0,0,0),(503000,61,0,0,0,0),(503000,62,0,0,0,0),(503000,63,0,0,0,0),(503000,64,0,0,0,0),(503000,65,0,0,0,0),(503000,66,0,0,0,0),(503000,67,0,0,0,0),(503000,68,0,0,0,0),(503100,52,0,0,0,0),(503100,53,0,0,0,0),(503100,54,0,0,0,0),(503100,55,0,0,0,0),(503100,56,0,0,0,0),(503100,57,0,0,0,0),(503100,58,0,0,0,0),(503100,59,0,0,0,0),(503100,60,0,0,0,0),(503100,61,0,0,0,0),(503100,62,0,0,0,0),(503100,63,0,0,0,0),(503100,64,0,0,0,0),(503100,65,0,0,0,0),(503100,66,0,0,0,0),(503100,67,0,0,0,0),(503100,68,0,0,0,0),(504000,52,0,0,0,0),(504000,53,0,0,0,0),(504000,54,0,0,0,0),(504000,55,0,0,0,0),(504000,56,0,0,0,0),(504000,57,0,0,0,0),(504000,58,0,0,0,0),(504000,59,0,0,0,0),(504000,60,0,0,0,0),(504000,61,0,0,0,0),(504000,62,0,0,0,0),(504000,63,0,0,0,0),(504000,64,0,0,0,0),(504000,65,0,0,0,0),(504000,66,0,0,0,0),(504000,67,0,0,0,0),(504000,68,0,0,0,0),(505000,52,0,0,0,0),(505000,53,0,0,0,0),(505000,54,0,0,0,0),(505000,55,0,0,0,0),(505000,56,0,0,0,0),(505000,57,0,0,0,0),(505000,58,0,0,0,0),(505000,59,0,0,0,0),(505000,60,0,0,0,0),(505000,61,0,0,0,0),(505000,62,0,0,0,0),(505000,63,0,0,0,0),(505000,64,0,0,0,0),(505000,65,0,0,0,0),(505000,66,0,0,0,0),(505000,67,0,0,0,0),(505000,68,0,0,0,0),(505100,52,0,0,0,0),(505100,53,0,0,0,0),(505100,54,0,0,0,0),(505100,55,0,0,0,0),(505100,56,0,0,0,0),(505100,57,0,0,0,0),(505100,58,0,0,0,0),(505100,59,0,0,0,0),(505100,60,0,0,0,0),(505100,61,0,0,0,0),(505100,62,0,0,0,0),(505100,63,0,0,0,0),(505100,64,0,0,0,0),(505100,65,0,0,0,0),(505100,66,0,0,0,0),(505100,67,0,0,0,0),(505100,68,0,0,0,0),(505110,52,0,0,0,0),(505110,53,0,0,0,0),(505110,54,0,0,0,0),(505110,55,0,0,0,0),(505110,56,0,0,0,0),(505110,57,0,0,0,0),(505110,58,0,0,0,0),(505110,59,0,0,0,0),(505110,60,0,0,0,0),(505110,61,0,0,0,0),(505110,62,0,0,0,0),(505110,63,0,0,0,0),(505110,64,0,0,0,0),(505110,65,0,0,0,0),(505110,66,0,0,0,0),(505110,67,0,0,0,0),(505110,68,0,0,0,0),(510000,52,0,0,0,0),(510000,53,0,0,0,0),(510000,54,0,0,0,0),(510000,55,0,0,0,0),(510000,56,0,0,0,0),(510000,57,0,0,0,0),(510000,58,0,0,0,0),(510000,59,0,0,0,0),(510000,60,0,0,0,0),(510000,61,0,0,0,0),(510000,62,0,0,0,0),(510000,63,0,0,0,0),(510000,64,0,0,0,0),(510000,65,0,0,0,0),(510000,66,0,0,0,0),(510000,67,0,0,0,0),(510000,68,0,0,0,0),(511000,52,0,0,0,0),(511000,53,0,0,0,0),(511000,54,0,0,0,0),(511000,55,0,0,0,0),(511000,56,0,0,0,0),(511000,57,0,0,0,0),(511000,58,0,0,0,0),(511000,59,0,0,0,0),(511000,60,0,0,0,0),(511000,61,0,0,0,0),(511000,62,0,0,0,0),(511000,63,0,0,0,0),(511000,64,0,0,0,0),(511000,65,0,0,0,0),(511000,66,0,0,0,0),(511000,67,0,0,0,0),(511000,68,0,0,0,0),(513000,52,0,0,0,0),(513000,53,0,0,0,0),(513000,54,0,0,0,0),(513000,55,0,0,0,0),(513000,56,0,0,0,0),(513000,57,0,0,0,0),(513000,58,0,0,0,0),(513000,59,0,0,0,0),(513000,60,0,0,0,0),(513000,61,0,0,0,0),(513000,62,0,0,0,0),(513000,63,0,0,0,0),(513000,64,0,0,0,0),(513000,65,0,0,0,0),(513000,66,0,0,0,0),(513000,67,0,0,0,0),(513000,68,0,0,0,0),(514000,52,0,0,0,0),(514000,53,0,0,0,0),(514000,54,0,0,0,0),(514000,55,0,0,0,0),(514000,56,0,0,0,0),(514000,57,0,0,0,0),(514000,58,0,0,0,0),(514000,59,0,0,0,0),(514000,60,0,0,0,0),(514000,61,0,0,0,0),(514000,62,0,0,0,0),(514000,63,0,0,0,0),(514000,64,0,0,0,0),(514000,65,0,0,0,0),(514000,66,0,0,0,0),(514000,67,0,0,0,0),(514000,68,0,0,0,0),(515000,52,0,0,0,0),(515000,53,0,0,0,0),(515000,54,0,0,0,0),(515000,55,0,0,0,0),(515000,56,0,0,0,0),(515000,57,0,0,0,0),(515000,58,0,0,0,0),(515000,59,0,0,0,0),(515000,60,0,0,0,0),(515000,61,0,0,0,0),(515000,62,0,0,0,0),(515000,63,0,0,0,0),(515000,64,0,0,0,0),(515000,65,0,0,0,0),(515000,66,0,0,0,0),(515000,67,0,0,0,0),(515000,68,0,0,0,0),(516000,52,0,0,0,0),(516000,53,0,0,0,0),(516000,54,0,0,0,0),(516000,55,0,0,0,0),(516000,56,0,0,0,0),(516000,57,0,0,0,0),(516000,58,0,0,0,0),(516000,59,0,0,0,0),(516000,60,0,0,0,0),(516000,61,0,0,0,0),(516000,62,0,0,0,0),(516000,63,0,0,0,0),(516000,64,0,0,0,0),(516000,65,0,0,0,0),(516000,66,0,0,0,0),(516000,67,0,0,0,0),(516000,68,0,0,0,0),(520000,52,0,0,0,0),(520000,53,0,0,0,0),(520000,54,0,0,0,0),(520000,55,0,0,0,0),(520000,56,0,0,0,0),(520000,57,0,0,0,0),(520000,58,0,0,0,0),(520000,59,0,0,0,0),(520000,60,0,0,0,0),(520000,61,0,0,0,0),(520000,62,0,0,0,0),(520000,63,0,0,0,0),(520000,64,0,0,0,0),(520000,65,0,0,0,0),(520000,66,0,0,0,0),(520000,67,0,0,0,0),(520000,68,0,0,0,0),(540000,52,0,0,0,0),(540000,53,0,0,0,0),(540000,54,0,0,0,0),(540000,55,0,0,0,0),(540000,56,0,0,0,0),(540000,57,0,0,0,0),(540000,58,0,0,0,0),(540000,59,0,0,0,0),(540000,60,0,0,0,0),(540000,61,0,0,0,0),(540000,62,0,0,0,0),(540000,63,0,0,0,0),(540000,64,0,0,0,0),(540000,65,0,0,0,0),(540000,66,0,0,0,0),(540000,67,0,0,0,0),(540000,68,0,0,0,0),(541000,52,0,0,0,0),(541000,53,0,0,0,0),(541000,54,0,0,0,0),(541000,55,0,0,0,0),(541000,56,0,0,0,0),(541000,57,0,0,0,0),(541000,58,0,0,0,0),(541000,59,0,0,0,0),(541000,60,0,0,0,0),(541000,61,0,0,0,0),(541000,62,0,0,0,0),(541000,63,0,0,0,0),(541000,64,0,0,0,0),(541000,65,0,0,0,0),(541000,66,0,0,0,0),(541000,67,0,0,0,0),(541000,68,0,0,0,0),(542000,52,0,0,0,0),(542000,53,0,0,0,0),(542000,54,0,0,0,0),(542000,55,0,0,0,0),(542000,56,0,0,0,0),(542000,57,0,0,0,0),(542000,58,0,0,0,0),(542000,59,0,0,0,0),(542000,60,0,0,0,0),(542000,61,0,0,0,0),(542000,62,0,0,0,0),(542000,63,0,0,0,0),(542000,64,0,0,0,0),(542000,65,0,0,0,0),(542000,66,0,0,0,0),(542000,67,0,0,0,0),(542000,68,0,0,0,0),(550000,52,0,0,0,0),(550000,53,0,0,0,0),(550000,54,0,0,0,0),(550000,55,0,0,0,0),(550000,56,0,0,0,0),(550000,57,0,0,0,0),(550000,58,0,0,0,0),(550000,59,0,0,0,0),(550000,60,0,0,0,0),(550000,61,0,0,0,0),(550000,62,0,0,0,0),(550000,63,0,0,0,0),(550000,64,0,0,0,0),(550000,65,0,0,0,0),(550000,66,0,0,0,0),(550000,67,0,0,0,0),(550000,68,0,0,0,0),(560000,52,0,0,0,0),(560000,53,0,0,0,0),(560000,54,0,0,0,0),(560000,55,0,0,0,0),(560000,56,0,0,0,0),(560000,57,0,0,0,0),(560000,58,0,0,0,0),(560000,59,0,0,0,0),(560000,60,0,0,0,0),(560000,61,0,0,0,0),(560000,62,0,0,0,0),(560000,63,0,0,0,0),(560000,64,0,0,0,0),(560000,65,0,0,0,0),(560000,66,0,0,0,0),(560000,67,0,0,0,0),(560000,68,0,0,0,0),(700000,52,0,0,0,0),(700000,53,0,0,0,0),(700000,54,0,0,0,0),(700000,55,0,200,0,0),(700000,56,0,0,200,0),(700000,57,0,0,200,0),(700000,58,0,0,200,0),(700000,59,0,0,200,0),(700000,60,0,0,200,0),(700000,61,0,0,200,0),(700000,62,0,0,200,0),(700000,63,0,0,200,0),(700000,64,0,0,200,0),(700000,65,0,0,200,0),(700000,66,0,0,200,0),(700000,67,0,0,200,0),(700000,68,0,0,200,0),(700100,52,0,0,0,0),(700100,53,0,0,0,0),(700100,54,0,0,0,0),(700100,55,0,7.54036,0,0),(700100,56,0,0,7.54036,0),(700100,57,0,0,7.54036,0),(700100,58,0,0,7.54036,0),(700100,59,0,0,7.54036,0),(700100,60,0,0,7.54036,0),(700100,61,0,0,7.54036,0),(700100,62,0,0,7.54036,0),(700100,63,0,0,7.54036,0),(700100,64,0,0,7.54036,0),(700100,65,0,0,7.54036,0),(700100,66,0,0,7.54036,0),(700100,67,0,0,7.54036,0),(700100,68,0,0,7.54036,0),(701000,52,0,0,0,0),(701000,53,0,0,0,0),(701000,54,0,0,0,0),(701000,55,0,0,0,0),(701000,56,0,0,0,0),(701000,57,0,0,0,0),(701000,58,0,0,0,0),(701000,59,0,0,0,0),(701000,60,0,0,0,0),(701000,61,0,0,0,0),(701000,62,0,0,0,0),(701000,63,0,0,0,0),(701000,64,0,0,0,0),(701000,65,0,0,0,0),(701000,66,0,0,0,0),(701000,67,0,0,0,0),(701000,68,0,0,0,0),(710000,52,0,0,0,0),(710000,53,0,0,0,0),(710000,54,0,0,0,0),(710000,55,0,-59.5093,0,0),(710000,56,0,32.0667,-59.5093,0),(710000,57,0,-15.4649,-27.4426,0),(710000,58,0,0,-42.9075,0),(710000,59,0,0,-42.9075,0),(710000,60,0,0,-42.9075,0),(710000,61,0,0,-42.9075,0),(710000,62,0,0,-42.9075,0),(710000,63,0,0,-42.9075,0),(710000,64,0,0,-42.9075,0),(710000,65,0,0,-42.9075,0),(710000,66,0,0,-42.9075,0),(710000,67,0,0,-42.9075,0),(710000,68,0,0,-42.9075,0),(720000,52,0,0,0,0),(720000,53,0,0,0,0),(720000,54,0,0,0,0),(720000,55,0,744,0,0),(720000,56,0,12.4,744,0),(720000,57,0,0,756.4,0),(720000,58,0,0,756.4,0),(720000,59,0,0,756.4,0),(720000,60,0,0,756.4,0),(720000,61,0,0,756.4,0),(720000,62,0,0,756.4,0),(720000,63,0,0,756.4,0),(720000,64,0,0,756.4,0),(720000,65,0,0,756.4,0),(720000,66,0,0,756.4,0),(720000,67,0,0,756.4,0),(720000,68,0,0,756.4,0),(721000,52,0,0,0,0),(721000,53,0,0,0,0),(721000,54,0,0,0,0),(721000,55,0,0,0,0),(721000,56,0,0,0,0),(721000,57,0,0,0,0),(721000,58,0,0,0,0),(721000,59,0,0,0,0),(721000,60,0,0,0,0),(721000,61,0,0,0,0),(721000,62,0,0,0,0),(721000,63,0,0,0,0),(721000,64,0,0,0,0),(721000,65,0,0,0,0),(721000,66,0,0,0,0),(721000,67,0,0,0,0),(721000,68,0,0,0,0),(722000,52,0,0,0,0),(722000,53,0,0,0,0),(722000,54,0,0,0,0),(722000,55,0,4941.09,0,0),(722000,56,0,29.07,4941.09,0),(722000,57,0,-19.38,4970.16,0),(722000,58,0,0,4950.78,0),(722000,59,0,0,4950.78,0),(722000,60,0,0,4950.78,0),(722000,61,0,0,4950.78,0),(722000,62,0,0,4950.78,0),(722000,63,0,0,4950.78,0),(722000,64,0,0,4950.78,0),(722000,65,0,0,4950.78,0),(722000,66,0,0,4950.78,0),(722000,67,0,0,4950.78,0),(722000,68,0,0,4950.78,0),(723000,52,0,0,0,0),(723000,53,0,0,0,0),(723000,54,0,0,0,0),(723000,55,0,-5633.16,0,0),(723000,56,0,0,-5633.16,0),(723000,57,0,0,-5633.16,0),(723000,58,0,0,-5633.16,0),(723000,59,0,0,-5633.16,0),(723000,60,0,0,-5633.16,0),(723000,61,0,0,-5633.16,0),(723000,62,0,0,-5633.16,0),(723000,63,0,0,-5633.16,0),(723000,64,0,0,-5633.16,0),(723000,65,0,0,-5633.16,0),(723000,66,0,0,-5633.16,0),(723000,67,0,0,-5633.16,0),(723000,68,0,0,-5633.16,0),(800000,52,0,0,0,0),(800000,53,0,0,0,0),(800000,54,0,0,0,0),(800000,55,0,-258.87,0,0),(800000,56,0,0,-258.87,0),(800000,57,0,0,-258.87,0),(800000,58,0,0,-258.87,0),(800000,59,0,0,-258.87,0),(800000,60,0,0,-258.87,0),(800000,61,0,0,-258.87,0),(800000,62,0,0,-258.87,0),(800000,63,0,0,-258.87,0),(800000,64,0,0,-258.87,0),(800000,65,0,0,-258.87,0),(800000,66,0,0,-258.87,0),(800000,67,0,0,-258.87,0),(800000,68,0,0,-258.87,0),(810000,52,0,0,0,0),(810000,53,0,0,0,0),(810000,54,0,0,0,0),(810000,55,0,0,0,0),(810000,56,0,0.78375,0,0),(810000,57,0,0,0.78375,0),(810000,58,0,0,0.78375,0),(810000,59,0,0,0.78375,0),(810000,60,0,0,0.78375,0),(810000,61,0,0,0.78375,0),(810000,62,0,0,0.78375,0),(810000,63,0,0,0.78375,0),(810000,64,0,0,0.78375,0),(810000,65,0,0,0.78375,0),(810000,66,0,0,0.78375,0),(810000,67,0,0,0.78375,0),(810000,68,0,0,0.78375,0),(820000,52,0,0,0,0),(820000,53,0,0,0,0),(820000,54,0,0,0,0),(820000,55,0,0,0,0),(820000,56,0,0,0,0),(820000,57,0,0,0,0),(820000,58,0,0,0,0),(820000,59,0,0,0,0),(820000,60,0,0,0,0),(820000,61,0,0,0,0),(820000,62,0,0,0,0),(820000,63,0,0,0,0),(820000,64,0,0,0,0),(820000,65,0,0,0,0),(820000,66,0,0,0,0),(820000,67,0,0,0,0),(820000,68,0,0,0,0),(890000,52,0,0,0,0),(890000,53,0,0,0,0),(890000,54,0,0,0,0),(890000,55,0,5.35,0,0),(890000,56,0,0,5.35,0),(890000,57,0,0,5.35,0),(890000,58,0,0,5.35,0),(890000,59,0,0,5.35,0),(890000,60,0,0,5.35,0),(890000,61,0,0,5.35,0),(890000,62,0,0,5.35,0),(890000,63,0,0,5.35,0),(890000,64,0,0,5.35,0),(890000,65,0,0,5.35,0),(890000,66,0,0,5.35,0),(890000,67,0,0,5.35,0),(890000,68,0,0,5.35,0),(900000,52,0,0,0,0),(900000,53,0,0,0,0),(900000,54,0,0,0,0),(900000,55,0,0,0,0),(900000,56,0,0,0,0),(900000,57,0,0,0,0),(900000,58,0,0,0,0),(900000,59,0,0,0,0),(900000,60,0,0,0,0),(900000,61,0,0,0,0),(900000,62,0,0,0,0),(900000,63,0,0,0,0),(900000,64,0,0,0,0),(900000,65,0,0,0,0),(900000,66,0,0,0,0),(900000,67,0,0,0,0),(900000,68,0,0,0,0),(910000,52,0,0,0,0),(910000,53,0,0,0,0),(910000,54,0,0,0,0),(910000,55,0,0,0,0),(910000,56,0,0,0,0),(910000,57,0,0,0,0),(910000,58,0,0,0,0),(910000,59,0,0,0,0),(910000,60,0,0,0,0),(910000,61,0,0,0,0),(910000,62,0,0,0,0),(910000,63,0,0,0,0),(910000,64,0,0,0,0),(910000,65,0,0,0,0),(910000,66,0,0,0,0),(910000,67,0,0,0,0),(910000,68,0,0,0,0),(920000,52,0,0,0,0),(920000,53,0,0,0,0),(920000,54,0,0,0,0),(920000,55,0,0,0,0),(920000,56,0,0,0,0),(920000,57,0,0,0,0),(920000,58,0,0,0,0),(920000,59,0,0,0,0),(920000,60,0,0,0,0),(920000,61,0,0,0,0),(920000,62,0,0,0,0),(920000,63,0,0,0,0),(920000,64,0,0,0,0),(920000,65,0,0,0,0),(920000,66,0,0,0,0),(920000,67,0,0,0,0),(920000,68,0,0,0,0),(930000,52,0,0,0,0),(930000,53,0,0,0,0),(930000,54,0,0,0,0),(930000,55,0,0,0,0),(930000,56,0,0,0,0),(930000,57,0,0,0,0),(930000,58,0,0,0,0),(930000,59,0,0,0,0),(930000,60,0,0,0,0),(930000,61,0,0,0,0),(930000,62,0,0,0,0),(930000,63,0,0,0,0),(930000,64,0,0,0,0),(930000,65,0,0,0,0),(930000,66,0,0,0,0),(930000,67,0,0,0,0),(930000,68,0,0,0,0),(940000,52,0,0,0,0),(940000,53,0,0,0,0),(940000,54,0,0,0,0),(940000,55,0,0,0,0),(940000,56,0,0,0,0),(940000,57,0,0,0,0),(940000,58,0,0,0,0),(940000,59,0,0,0,0),(940000,60,0,0,0,0),(940000,61,0,0,0,0),(940000,62,0,0,0,0),(940000,63,0,0,0,0),(940000,64,0,0,0,0),(940000,65,0,0,0,0),(940000,66,0,0,0,0),(940000,67,0,0,0,0),(940000,68,0,0,0,0);
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
INSERT INTO CustAllocns VALUES (46,'73.1250','2004-07-17',61,56);
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
INSERT INTO DebtorTrans VALUES (56,1,10,'WALMON','WALM','2004-07-14 00:00:00',55,0,'','EX',27,1.700000,637.188,0,0,0,0,73.125,'','1',0,''),(57,1,11,'JOHNSON','JOHNTEMPE','2004-07-15 00:00:00',55,0,'','WS',0,1.000000,-364.55,0,0,0,0,0,'','',0,''),(59,2,11,'BILLYBUNT','BILLYBUNT','2004-07-17 00:00:00',55,0,'','WS',0,0.800000,-70.5,0,0,0,0,0,'','',0,''),(60,1,12,'BILLYBUNT','','2004-07-17 00:00:00',55,0,'Direct Credit','',0,2.354000,-12.5,0,0,-0.33,0,0,'','',0,''),(61,3,11,'WALMON','WALM','2004-07-17 00:00:00',55,1,'Inv-1','EX',27,1.700000,-73.125,0,0,0,0,-73.125,'Less restocking fee 2.5% as agreed','',0,''),(62,2,10,'WATTSGRP','WATTLA','2004-07-30 00:00:00',55,0,'','WS',28,1.000000,79.3,0,0,0,0,0,'','1',0,''),(63,3,10,'WALMON','WALM','2004-08-20 16:00:00',56,0,'','EX',29,1.700000,311.1,0,0,0,0,0,'','1',0,'QFG-23332-122-2'),(64,4,11,'GRANHR','GRAN','2004-08-23 00:00:00',56,0,'','EX',0,1.000000,-142.312,0,0,0,0,0,'','',0,''),(65,5,11,'WALMON','WALMOADEL','2004-08-23 00:00:00',56,0,'','EX',0,1.700000,-7.8375,-0.78375,0,0,0,0,'','',0,''),(66,6,11,'WALMON','WALM','2004-08-20 23:00:00',56,1,'Inv-1','EX',27,1.700000,0,0,0,0,0,0,'','',0,''),(67,4,10,'WALMON','WALM','2004-09-21 00:00:00',57,0,'','EX',30,1.700000,99,0,0,0,0,0,'','1',0,'TEG3288'),(68,7,11,'WALMON','WALM','2004-09-20 00:00:00',57,0,'','EX',0,1.700000,-99.0002,0,0,0,0,0,'','',0,''),(69,5,10,'GRANHR','GRAN','2004-09-21 00:00:00',57,0,'','EX',31,1.000000,25.3,0,0,0,0,0,'','1',0,''),(70,6,10,'WATTSGRP','WATTLA','2004-09-21 00:00:00',57,0,'','WS',28,1.000000,3.99,0,0,0,0,0,'','1',0,''),(71,7,10,'GRANHR','GRAN','2004-09-21 00:00:00',57,0,'','EX',31,1.000000,12.65,0,0,0,0,0,'','1',0,'');
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
INSERT INTO GLTrans VALUES (525,25,12,0,'2004-07-13',55,720000,'PO: 14 BIGBISC - 125 - 12.5mm round steel x 1170 @ 0.62',725.4,1,''),(526,25,12,0,'2004-07-13',55,723000,'PO: 14 BIGBISC - 125 - 12.5mm round steel x 1170 @ 0.62',-725.4,1,''),(527,25,12,0,'2004-07-13',55,722000,'PO: 14 BIGBISC - HON90021 - Honda Civic front coil spring 1990-94 x 512 @ 9.69',4961.28,1,''),(528,25,12,0,'2004-07-13',55,723000,'PO: 14 BIGBISC - HON90021 - Honda Civic front coil spring 1990-94 x 512 @ 9.69',-4961.28,1,''),(529,10,1,0,'2004-07-14',55,1,'WALMON - 125 x 10 @ 0.6200',6.2,1,''),(530,10,1,0,'2004-07-14',55,720000,'WALMON - 125 x 10 @ 0.6200',-6.2,1,''),(531,10,1,0,'2004-07-14',55,107,'WALMON - 125 x 10 @ 1.2500',-7.35294,1,''),(532,10,1,0,'2004-07-14',55,1,'WALMON - 125 @ 2.5%',0.183824,1,''),(533,10,1,0,'2004-07-14',55,1,'WALMON - HON90021 x 25 @ 9.6900',242.25,1,''),(534,10,1,0,'2004-07-14',55,722000,'WALMON - HON90021 x 25 @ 9.6900',-242.25,1,''),(535,10,1,0,'2004-07-14',55,107,'WALMON - HON90021 x 25 @ 25.0000',-367.647,1,''),(536,10,1,0,'2004-07-14',55,710000,'WALMON',374.816,1,''),(537,11,1,0,'2004-07-15',55,1,'JOHNSON - 125 x 10 @ 0.6200',-6.2,1,''),(538,11,1,0,'2004-07-15',55,720000,'JOHNSON - 125 x 10 @ 0.6200',6.2,1,''),(539,11,1,0,'2004-07-15',55,107,'JOHNSON - 125 x 10 @ 2.33',23.3,1,''),(540,11,1,0,'2004-07-15',55,1,'JOHNSON - HON90021 x 21 @ 9.6900',-203.49,1,''),(541,11,1,0,'2004-07-15',55,722000,'JOHNSON - HON90021 x 21 @ 9.6900',203.49,1,''),(542,11,1,0,'2004-07-15',55,107,'JOHNSON - HON90021 x 21 @ 16.25',341.25,1,''),(543,11,1,0,'2004-07-15',55,710000,'JOHNSON',-364.55,1,''),(547,20,14,0,'2004-07-14',55,723000,'FREDBLOW Shipment charge against 3',53.52,1,''),(548,20,14,0,'2004-07-14',55,890000,'FREDBLOW - Inv 5442 USD5.35 @ a rate of 1.0000',5.35,1,''),(549,20,14,0,'2004-07-14',55,800000,'FREDBLOW - Inv 5442 USD58.87 @ a rate of 1.0000',-58.87,1,''),(550,11,2,0,'2004-07-17',55,1,'BILLYBUNT - 125 x 30 @ 0.6200',-18.6,1,''),(551,11,2,0,'2004-07-17',55,720000,'BILLYBUNT - 125 x 30 @ 0.6200',18.6,1,''),(552,11,2,0,'2004-07-17',55,107,'BILLYBUNT - 125 x 30 @ 2.35',70.5,1,''),(553,11,2,0,'2004-07-17',55,710000,'BILLYBUNT',-70.5,1,''),(554,12,1,0,'2004-07-17',55,259000,'Re-imbursed private drinks',-2.23025,1,''),(555,12,1,0,'2004-07-17',55,700100,'',7.54036,1,''),(556,12,1,0,'2004-07-17',55,710000,'',-5.4503,1,''),(557,12,1,0,'2004-07-17',55,455000,'',0.140187,1,''),(558,11,3,0,'2004-07-17',55,1,'WALMON - HON90021 x 3 @ 9.6900',-29.07,1,''),(559,11,3,0,'2004-07-17',55,722000,'WALMON - HON90021 x 3 @ 9.6900',29.07,1,''),(560,11,3,0,'2004-07-17',55,107,'WALMON - HON90021 x 3 @ 25.000',75,1,''),(561,11,3,0,'2004-07-17',55,1,'WALMON - HON90021 @ 2.5%',-1.875,1,''),(562,11,3,0,'2004-07-17',55,710000,'WALMON',-73.125,1,''),(563,22,4,0,'2004-07-29',55,800000,'BIGBISC-',-200,1,''),(564,22,4,0,'2004-07-29',55,700000,'BIGBISC-',200,1,''),(565,10,2,0,'2004-07-30',55,1,'WATTSGRP - HON90022 x 5 @ 2.1000',10.5,1,''),(566,10,2,0,'2004-07-30',55,722000,'WATTSGRP - HON90022 x 5 @ 2.1000',-10.5,1,''),(567,10,2,0,'2004-07-30',55,107,'WATTSGRP - HON90022 x 5 @ 15.8600',-79.3,1,''),(568,10,2,0,'2004-07-30',55,710000,'WATTSGRP',79.3,1,''),(569,10,3,0,'2004-08-20',56,1,'WALMON - 125 x 5 @ 0.6200',3.1,1,''),(570,10,3,0,'2004-08-20',56,720000,'WALMON - 125 x 5 @ 0.6200',-3.1,1,''),(571,10,3,0,'2004-08-20',56,107,'WALMON - 125 x 5 @ 2.2200',-6.52941,1,''),(572,10,3,0,'2004-08-20',56,1,'WALMON - HON90021 x 12 @ 9.6900',116.28,1,''),(573,10,3,0,'2004-08-20',56,722000,'WALMON - HON90021 x 12 @ 9.6900',-116.28,1,''),(574,10,3,0,'2004-08-20',56,107,'WALMON - HON90021 x 12 @ 25.0000',-176.471,1,''),(575,10,3,0,'2004-08-20',56,710000,'WALMON',183,1,''),(576,11,4,0,'2004-08-23',56,256000,'GRANHR - HON90021 x 15 @ 9.6900',-145.35,1,''),(577,11,4,0,'2004-08-23',56,722000,'GRANHR - HON90021 x 15 @ 9.6900',145.35,1,''),(578,11,4,0,'2004-08-23',56,112,'GRANHR - HON90021 x 15 @ 12.6500',189.75,1,''),(579,11,4,0,'2004-08-23',56,1,'GRANHR - HON90021 @ 25%',-47.4375,1,''),(580,11,4,0,'2004-08-23',56,710000,'GRANHR',-142.312,1,''),(581,11,5,0,'2004-08-23',56,1,'WALMON - 125 x 25 @ 0.6200',-15.5,1,''),(582,11,5,0,'2004-08-23',56,720000,'WALMON - 125 x 25 @ 0.6200',15.5,1,''),(583,11,5,0,'2004-08-23',56,107,'WALMON - 125 x 25 @ .33',8.25,1,''),(584,11,5,0,'2004-08-23',56,1,'WALMON - 125 @ 5%',-0.4125,1,''),(585,11,5,0,'2004-08-23',56,710000,'WALMON',-8.62125,1,''),(586,11,5,0,'2004-08-23',56,810000,'WALMON',0.78375,1,''),(587,10,4,0,'2004-09-21',57,1,'WALMON - HON90021 x 2 @ 9.6900',19.38,1,''),(588,10,4,0,'2004-09-21',57,722000,'WALMON - HON90021 x 2 @ 9.6900',-19.38,1,''),(589,10,4,0,'2004-09-21',57,107,'WALMON - HON90021 x 2 @ 25.0000',-29.4118,1,''),(590,10,4,0,'2004-09-21',57,1,'WALMON - HON90022 x 2 @ 2.1000',4.2,1,''),(591,10,4,0,'2004-09-21',57,722000,'WALMON - HON90022 x 2 @ 2.1000',-4.2,1,''),(592,10,4,0,'2004-09-21',57,107,'WALMON - HON90022 x 2 @ 24.5000',-28.8235,1,''),(593,10,4,0,'2004-09-21',57,710000,'WALMON',58.2353,1,''),(594,11,7,0,'2004-09-20',57,1,'WALMON - HON90021 x 2 @ 9.6900',-19.38,1,''),(595,11,7,0,'2004-09-20',57,722000,'WALMON - HON90021 x 2 @ 9.6900',19.38,1,''),(596,11,7,0,'2004-09-20',57,107,'WALMON - HON90021 x 2 @ 25.0000',50,1,''),(597,11,7,0,'2004-09-20',57,1,'WALMON - HON90022 x 2.0000 @ 2.1000',-4.2,1,''),(598,11,7,0,'2004-09-20',57,722000,'WALMON - HON90022 x 2.0000 @ 2.1000',4.2,1,''),(599,11,7,0,'2004-09-20',57,107,'WALMON - HON90022 x 2.0000 @ 24.5001',49.0002,1,''),(600,11,7,0,'2004-09-20',57,710000,'WALMON',-99.0002,1,''),(601,10,5,0,'2004-09-21',57,256000,'GRANHR - HON90021 x 2 @ 9.6900',19.38,1,''),(602,10,5,0,'2004-09-21',57,722000,'GRANHR - HON90021 x 2 @ 9.6900',-19.38,1,''),(603,10,5,0,'2004-09-21',57,112,'GRANHR - HON90021 x 2 @ 12.6500',-25.3,1,''),(604,10,5,0,'2004-09-21',57,710000,'GRANHR',25.3,1,''),(605,25,13,0,'2004-09-20',57,720000,'PO: 15 BIGBISC - 125 - 12.5mm round steel x 95 @ 0.62',58.9,0,''),(606,25,13,0,'2004-09-20',57,723000,'PO: 15 BIGBISC - 125 - 12.5mm round steel x 95 @ 0.62',-58.9,0,''),(607,10,6,0,'2004-09-21',57,1,'WATTSGRP - 125 x 3 @ 0.6200',1.86,0,''),(608,10,6,0,'2004-09-21',57,720000,'WATTSGRP - 125 x 3 @ 0.6200',-1.86,0,''),(609,10,6,0,'2004-09-21',57,107,'WATTSGRP - 125 x 3 @ 1.3300',-3.99,0,''),(610,10,6,0,'2004-09-21',57,710000,'WATTSGRP',3.99,0,''),(611,10,7,0,'2004-09-21',57,256000,'GRANHR - HON90021 x 1 @ 9.6900',9.69,0,''),(612,10,7,0,'2004-09-21',57,722000,'GRANHR - HON90021 x 1 @ 9.6900',-9.69,0,''),(613,10,7,0,'2004-09-21',57,112,'GRANHR - HON90021 x 1 @ 12.6500',-12.65,0,''),(614,10,7,0,'2004-09-21',57,710000,'GRANHR',12.65,0,'');
UNLOCK TABLES;
/*!40000 ALTER TABLE GLTrans ENABLE KEYS */;

--
-- Dumping data for table `GRNs`
--


/*!40000 ALTER TABLE GRNs DISABLE KEYS */;
LOCK TABLES GRNs WRITE;
INSERT INTO GRNs VALUES (12,24,27,'125','2004-07-13','12.5mm round steel',1170.0000,0.0000,'BIGBISC'),(12,25,28,'HON90021','2004-07-13','Honda Civic front coil spring 1990-94',512.0000,0.0000,'BIGBISC'),(13,26,30,'125','2004-09-20','12.5mm round steel',95.0000,0.0000,'BIGBISC');
UNLOCK TABLES;
/*!40000 ALTER TABLE GRNs ENABLE KEYS */;

--
-- Dumping data for table `Help`
--


/*!40000 ALTER TABLE Help DISABLE KEYS */;
LOCK TABLES Help WRITE;
INSERT INTO Help VALUES (5,93,'Enter either a portion of the suppliers name or a portion of the suppliers code then hit the search now button.\r\nThe suppliers with the entered text in their name or code will show. Clicking on the button in the code column selects a supplier. Once a supplier has been selected then all the options relevant to the supplier show with links split into three columns - inquiries, transactions, maintenance:\r\n<LI>Supplier Account Inquiry</LI>\r\n<LI>Show this suppliers outstanding purchase orders</LI>\r\n<LI>Show all the suppliers purchase orders</LI>\r\n<LI>Show Shipments set up from the Supplier</LI>\r\n\r\nUnder Transactions Links there are links to enter a suppliers invoice, a debit (credit) note, a payment to the supplier or reverse goods received from a supplier.\r\n\r\nUnder Maintenance Links - the details of the supplier can be amended, new contacts together with email addresses can be maintained and new shipments initiated.\r\n','S'),(8,133,'<font size=\"+2\"><b>Setting Up Users</b></font>\r\n<br><br>\r\nHaving logged in as the demonstration user. A new user should be defined whom has administrative privileges and the demonstration user should be deleted. From the menu click on the Setup tab and the select Set Up User Accounts, the users defined are displayed and User names must be 5 characters or more and the password entered must be 5 characters or more too. The password cannot also be the user name or contain the user name. It is not necessary to enter the full name telephone and other details. The access level selected is important as this determines the pages that the user can access. In any business where the employees are not also the owners it is advisable to split the functions between staff so that no user can complete an entire transaction. There are occasions in smaller businesses where this is impractical. However, the system has great flexibility to allow the user access to be finely defined. See the security schema section. \r\n<br><br>\r\nAs well as allowing the access to be restricted there is some flexibility about the links that are made available to a user. In the user account screen the system administrator can define which tabs of the main menu are activiated and available to the user.\r\n<br><br>\r\nThere is also the facility to set up customers with their own logon. This restricts them to inquiries on their account including invoice re-prints and to entering new sales order. Customer logins do not allow any modification to the pricing. To do this the access level must be set to customer logon and the settings for customer logon in config.php must not be modified - ie $PageSecurity = 1 allowed only. In addition, the customer code and branch code applicable to the person logging on must be entered.\r\n<br><br>\r\n<font size=\"+3\"><b>Security Schema</b></font>\r\n<br><br>\r\nThe user name and password combination entered at logon enables the system to identify the permissions appropriate to the user. It is possible to determine and restrict which pages a user should be able to access. Each user is allocated to an Access Level or Security Group. \r\n<br><br>\r\nThe Security group must be defined in config.php in two ways. \r\n<br><br>\r\n<ul>\r\n<li>The Security Group Headings are defined in an array called SecurityHeadings.\r\n<li>The Security Group page access codes applicable to the group must be defined in the SecurityGroups array - in the same order as the SecurityHeadings array. Ie the first security heading refers to the first array stored in the SecurityGroups array.\r\n</ul>\r\nEach script is defined as having a specific PageSecurity level. At the time of writing this is a number between 1 and 15 - if more levels of security are necessary then this can be expanded. The current PageSecurity settings applicable for each page are set out in the table below.\r\n<br><br>\r\nIf the PageSecurity setting of the page is a number contained in the SecurityGroup array as determined from the users access level then the user will be allowed access to the page. The user access level is an integer that represents the index number of the array element of the SecurityGroups array defined in config.php.\r\n<br><br>\r\nAccess authority is checked in header.inc script for all pages (or PDF_Starter.inc for PDF pages). The variable $_SESSION[\"AccessLevel\"] is retrieved from the database when the user logs in. This variable refers to the index of the SecurityGroups array or arrays. The array of numbers that is referenced by this index defines the pages a user can see. Any page that has a $PageSecurity equal to any value in this array is deemed to be an authorised page.\r\n<br><br>\r\nIf you wish to add more security groups then you must add a new Security Heading to the $SecurityHeadings array and a new array of PageSecurity numbers to the $SecurityGroups array. This mechanism allows the system administrator to closely and accurately control who can access what.\r\n<br><br>\r\nBy changing the numbers in each array the security access can be tailored for all users referring to the array being modified. When setting the numbers in the array, reference to the index of PageSecurity values below will be necessary. Of course the value of the default settings can easily be modified.\r\n<br><br>\r\nThe default settings in config.php are as follows:\r\n<br><br>\r\nIndex Group\r\n<br><br>\r\n0 Inquiries Only\r\n<br>\r\n1 Manufacturing/Inventory Admin\r\n<br>\r\n2 Purchasing officer\r\n<br>\r\n3 AP Clerk\r\n<br>\r\n4 AR Clerk\r\n<br>\r\n5 Accountant\r\n<br>\r\n6 Customer logon \r\n<br>\r\n7 System Administrator\r\n<br><br>\r\nto achieve this the following array for $SecurityHeadings is defined - the index number starting from 0 is incremented for each entry:\r\n<br><br>\r\n$SecurityHeadings = array(\r\n<br>\r\n\"Inquiries/Order Entry\",\r\n<br>\r\n\"Manufac/Inventory Admin\",\r\n<br>\r\n\"Purchasing Officer\",\r\n<br>\r\n\"AP Clerk\",\r\n<br>\r\n\"AR Clerk\",\r\n<br>\r\n\"Accountant\",\r\n<br>\r\n\"Customer Log On Only\",\r\n<br>\r\n\"System Administrator\"\r\n<br>\r\n);\r\n<br><br>\r\nthe corresponding array of SecurityGroup arrays is defined in config.php also. The first array (1,2) refers to the page security numbers that a user with \"Inquiries/Order Entry\" access would be authorised to view.\r\n<br><br>\r\n$SecurityGroups = array(\r\n<br>\r\narray(1,2),\r\n<br>\r\narray(1,2,11),\r\n<br>\r\narray(1,2,4),\r\n<br>\r\narray(1,2,5),\r\n<br>\r\narray(1,2,3),\r\n<br>\r\narray(1,2,3,4,5,6,7,8,9,10,11),\r\n<br>\r\narray(1),\r\n<br>\r\narray(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15)\r\n<br>\r\n);\r\n<br><br>\r\nThe system continues to evolve and the information set out below may be different in the current version. By default, each page has the variable $PageSecurity defined as follows:\r\n<br><br>\r\n<table border=\"2\">\r\n<th>Script File Name\r\n<th>PageSecurity value \r\n<tr>\r\n<td>\r\nindex.php\r\n</td>\r\n<td>\r\n1\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nCustomerInquiry.php\r\n</td>\r\n<td>\r\n1\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nDeliveryDetails.php\r\n</td>\r\n<td>\r\n1\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nSelectOrderItems.php\r\n</td>\r\n<td>\r\n1\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nAccountGroups.php\r\n</td>\r\n<td>\r\n10\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nGLAccounts.php\r\n</td>\r\n<td>\r\n10\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nGLJournal.php\r\n</td>\r\n<td>\r\n10\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nBankAccounts.php\r\n</td>\r\n<td>\r\n10\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nCOGSGLPostings.php\r\n</td>\r\n<td>\r\n10\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nCompanyPreferences.php\r\n</td>\r\n<td>\r\n10\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nPaymentTerms.php\r\n</td>\r\n<td>\r\n10\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nSalesGLPostings.php\r\n</td>\r\n<td>\r\n10\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nInventoryCostUpdate.php\r\n</td>\r\n<td>\r\n10\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nPrices_Customer.php\r\n</td>\r\n<td>\r\n11\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nPrices.php\r\n</td>\r\n<td>\r\n11\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nInventoryAdjustments.php\r\n</td>\r\n<td>\r\n11\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nInventorys.php\r\n</td>\r\n<td>\r\n11\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nInventoryTransfers.php\r\n</td>\r\n<td>\r\n11\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nFreightCosts.php\r\n</td>\r\n<td>\r\n11\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nGoodsReceived.php\r\n</td>\r\n<td>\r\n11\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nLocations.php\r\n</td>\r\n<td>\r\n11\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nInventoryCategories.php\r\n</td>\r\n<td>\r\n11\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nTaxAuthorities.php\r\n</td>\r\n<td>\r\n15\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nWWW_Users.php\r\n</td>\r\n<td>\r\n15\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nZ_CreateCompany.php\r\n</td>\r\n<td>\r\n15\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nZ_DeleteCreditNote.php\r\n</td>\r\n<td>\r\n15\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nZ_DeleteInvoice.php\r\n</td>\r\n<td>\r\n15\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nZ_DeleteSalesTransActions.php\r\n</td>\r\n<td>\r\n15\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nZ_index.php\r\n</td>\r\n<td>\r\n15\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nZ_MakeInventoryLocns.php\r\n</td>\r\n<td>\r\n15\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nZ_RePostGLFromPeriod.php\r\n</td>\r\n<td>\r\n15\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nZ_ReverseSuppPaymentRun.php\r\n</td>\r\n<td>\r\n15\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nZ_UpdateChartDetailsBFwd.php\r\n</td>\r\n<td>\r\n15\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nZ_UploadForm.php\r\n</td>\r\n<td>\r\n15\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nZ_UploadResult.php\r\n</td>\r\n<td>\r\n15\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nSalesTypes.php\r\n</td>\r\n<td>\r\n15\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nShippers.php\r\n</td>\r\n<td>\r\n15\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nAgedDebtors.php\r\n</td>\r\n<td>\r\n2\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nAgedSuppliers.php\r\n</td>\r\n<td>\r\n2\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nConfirmDispatch_Invoice.php\r\n</td>\r\n<td>\r\n2\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nCustomerTransInquiry.php\r\n</td>\r\n<td>\r\n2\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nFTP_RadioBeacon.php\r\n</td>\r\n<td>\r\n2\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nInventoryPlanning.php\r\n</td>\r\n<td>\r\n2\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nInventoryValuation.php\r\n</td>\r\n<td>\r\n2\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nOrderDetails.php\r\n</td>\r\n<td>\r\n2\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nPeriodsInquiry.php\r\n</td>\r\n<td>\r\n2\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nPO_PDFPurchOrder.php\r\n</td>\r\n<td>\r\n2\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nPO_SelectPurchOrder.php\r\n</td>\r\n<td>\r\n2\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nPrintCustOrder.php\r\n</td>\r\n<td>\r\n2\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nPrintCustStatements.php\r\n</td>\r\n<td>\r\n2\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nPrintCustTrans_A4_Portrait.php\r\n</td>\r\n<td>\r\n2\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nPrintCustTrans.php\r\n</td>\r\n<td>\r\n2\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nSalesAnalReptCols.php\r\n</td>\r\n<td>\r\n2\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nSalesAnalRepts.php\r\n</td>\r\n<td>\r\n2\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nSalesAnalysis_UserDefined.php\r\n</td>\r\n<td>\r\n2\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nSelectCompletedOrder.php\r\n</td>\r\n<td>\r\n2\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nSelectCustomer.php\r\n</td>\r\n<td>\r\n2\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nSelectProduct.php\r\n</td>\r\n<td>\r\n2\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nSelectSalesOrder.php\r\n</td>\r\n<td>\r\n2\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nSelectSupplier.php\r\n</td>\r\n<td>\r\n2\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nShiptsList.php\r\n</td>\r\n<td>\r\n2\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nInventoryMovements.php\r\n</td>\r\n<td>\r\n2\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nInventoryStatus.php\r\n</td>\r\n<td>\r\n2\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nInventoryUsage.php\r\n</td>\r\n<td>\r\n2\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nSupplierInquiry.php\r\n</td>\r\n<td>\r\n2\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nBOMInquiry.php\r\n</td>\r\n<td>\r\n2\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nBOMListing.php\r\n</td>\r\n<td>\r\n2\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nCredit_Invoice.php\r\n</td>\r\n<td>\r\n3\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nCustomerAllocations.php\r\n</td>\r\n<td>\r\n3\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nCustomerBranches.php\r\n</td>\r\n<td>\r\n3\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nCustomerReceipt.php\r\n</td>\r\n<td>\r\n3\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nCustomers.php\r\n</td>\r\n<td>\r\n3\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nPDFBankingSummary.php\r\n</td>\r\n<td>\r\n3\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nSelectCreditItems.php\r\n</td>\r\n<td>\r\n3\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nAreas.php\r\n</td>\r\n<td>\r\n3\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nCreditStatus.php\r\n</td>\r\n<td>\r\n3\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nSalesPeople.php\r\n</td>\r\n<td>\r\n3\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nPO_Header.php\r\n</td>\r\n<td>\r\n4\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nPO_Items.php\r\n</td>\r\n<td>\r\n4\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nPurchData.php\r\n</td>\r\n<td>\r\n4\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nInventoryReorderLevel.php\r\n</td>\r\n<td>\r\n4\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nPayments.php\r\n</td>\r\n<td>\r\n5\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nSuppCreditGRNs.php\r\n</td>\r\n<td>\r\n5\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nSuppInvGRNs.php\r\n</td>\r\n<td>\r\n5\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nSupplierAllocations.php\r\n</td>\r\n<td>\r\n5\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nSupplierContacts.php\r\n</td>\r\n<td>\r\n5\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nSupplierCredit.php\r\n</td>\r\n<td>\r\n5\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nSupplierInvoice.php\r\n</td>\r\n<td>\r\n5\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nSuppliers.php\r\n</td>\r\n<td>\r\n5\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nSuppPaymentRun.php\r\n</td>\r\n<td>\r\n5\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nSuppShiptChgs.php\r\n</td>\r\n<td>\r\n5\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nSuppTransGLAnalysis.php\r\n</td>\r\n<td>\r\n5\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nBankMatching.php\r\n</td>\r\n<td>\r\n7\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nBankReconciliation.php\r\n</td>\r\n<td>\r\n7\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nGLAccountInquiry.php\r\n</td>\r\n<td>\r\n8\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nGLCodesInquiry.php\r\n</td>\r\n<td>\r\n8\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nGLTransInquiry.php\r\n</td>\r\n<td>\r\n8\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nGLTrialBalance.php\r\n</td>\r\n<td>\r\n8\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nSelectGLAccount.php\r\n</td>\r\n<td>\r\n8\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nBOMs.php\r\n</td>\r\n<td>\r\n11\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nWorkCentres.php\r\n</td>\r\n<td>\r\n11\r\n</td>\r\n</tr>\r\n</table>','S'),(3,130,'Enter the suppliers name and postal address, terms of payment','S'),(4,160,'The main menu is split into sections depending on the user\'s module selection from the tabs accross the top of the menu. The screen actually is different for different users depending on the modules that the system administrator has elected to let you see. Some or all of:\r\n<LI> Orders </LI>\r\n<LI> Receivables </LI>\r\n<LI> Payables </LI>\r\n<LI> Purchasing </LI>\r\n<LI> Inventory </LI>\r\n<LI> Manufacturing </LI>\r\n<LI> Set Up </LI>\r\n<P>Selecting any of these tabs then shows the options available grouped between transactions in the first box, then inquiries or reports in the middle box, then maintenance in the bottom section. Clicking on the text of any option on the main menu proceeds to perform that action.\r\n<P><B>Using the Short Cut Menu</B>\r\n<P>Most actions involving customers will require selection of a customer first, similarly for suppliers and inventory the supplier or item must be selected first. Having selected the customer/supplier/item the selection screens show a sub menu relavent to the selected customer/supplier/item for most transactions and inquiries this is the more direct method of actioning. The traditional menu is available to aid the transition to this method of navigation.\r\n','S'),(9,12,'<font size=\"+2\"><b>Setting Up A System</b></font>\r\n<br><br>\r\nFor the company logo to appear on the each screen, it must be saved in the format of a .jpg file and be copied to the file logo.jpg in the directory with all the other php scripts. The logo needs to have read permissions for the user that the web server is running as normally \"nobody\" for apache on linux. Permissions issues are a none issue under windows.\r\n<br><br>\r\nCompany parameters need to be set correctly from the company set up screen. Most of these parameters are self -explanatory. Company name, company number, postal address, physical address etc. Also, telephone numbers and the default home currency of the business. If the default currency is not already set up, then from the main menu system set up tab, the link to the currency maintenance form allows new currencies to be defined and for default rates to be updated.\r\n<br><br>\r\nThe company record also provides a convenient place to store default GL codes for:\r\n<br><br>\r\n<ul>\r\n<li>Debtors Control GL Account:\r\n<li>Creditors Control GL Account: \r\n<li>Payroll Net Pay Clearing GL Account:\r\n<li>Goods Received Clearing GL Account:\r\n<li>Retained Earning Clearing GL Account: \r\n<li>Freight Re-charged GL Account: \r\n<li>Exchange Variances GL Account: \r\n<li>Sales Exchange Variances GL Account:\r\n<li>Purchases Exchange Variances GL Account: \r\n<li>Payment Discount GL Account:\r\n</ul>\r\nA default chart of accounts is set up. However, in most cases the company will wish to adopt the chart of accounts it has used traditionally. Chart of accounts maintenance - deletion of accounts and adding of new accounts is done from the General Ledger tab. This would be a starting point for data input of a new system. Where the GL integration features are to be used, setting up the chart of accounts will be a necessary first step.\r\n<br><br>\r\nThe company record also records the GL integration set up:\r\n<br><br>\r\n<ul>\r\n<li>Create GL entries for accounts receivable transactions\r\n<li>Create GL entries for accounts payable transactions:\r\n<li>Create GL entries for stock transactions (at standard cost): \r\n</ul>\r\nNotice that the stock GL integration is a separate flag. If GL integration of accounts receivable is set to yes, then GL journals are created for invoices and credits to sales and debtors but not for cost of sales and stock. For the later integration this requires that the GL entries for stock transactions be set to yes also.','S'),(10,119,'<font size=\"+2\"><b>Adding Inventory Items</b></font>\r\n<br><br>\r\nEntry of new items requires the input of certain base information:\r\n<br><br>\r\n<ul>\r\n<li>Base information about the Code and description together with the category and item type.\r\n<li>Cost information\r\n<li>Supplier purchasing data\r\n<li>Selling prices\r\n</ul>\r\nEntry of the base information is done from the menu, select the inventory tab and click \"Add a New Inventory Item\". \r\n<br><br>\r\n<font size=\"+1\"><b>Item Code</b></font>\r\n<br><br>\r\nA stock code is required for each stock item, this can be any combination of characters up to 20 characters long. The coding structure of stock items should be considered, to ensure that like stock items appear together. Internally the system looks at the code to order stock items in the various look up tables. A systematic approach to naming stock items can save a lot of time later. Under supplier purchasing data it is also possible to record the supplier\'s part number against an item.\r\n<br><br>\r\nThere is a script that allows for stock codes to be modified retrospectively, the script goes through all the tables necessary, location stocks, bills of material, order details, purchase order details, sales analysis etc to ensure that all history for the new part code follows the change. The script together with all utility scripts is available from Z_index.php (there are no links to this utility menu - due to the dangerous nature of some of the utilities) The stock code change script itself is named, Z_ChangeStockCode.php.\r\n<br><br>\r\n<font size=\"+1\"><b>Part Descriptions</b></font>\r\n<br><br>\r\nA description of the stock item - can be up to 50 characters long. A description is required for each stock item. This description is used in the look up boxes provided each time the stock item is invoiced, credited or adjusted. There is also the facility to enter long descriptions for each part which show on the screens that customers could access to place orders. This field allows very long descriptions or even features and benefits to be described for the item. The intention for this field is that it could be used for producing a company catalogue or for online ordering by customers who may need more information about a product. It is only the description (short) that displays on invoices and credit notes.\r\n<br><br>\r\n<font size=\"+1\"><b>Categories</b></font>\r\n<br><br>\r\nThese categories need to be set up first - from the System Setup tab of the menu. In selecting the stock category, be aware that the system uses the category information for analysis and summary reporting as well as identifying accounts for standard cost general ledger integration, if this option is enabled. There is a link to create new stock categories from the stock item entry page.\r\n<br><br>\r\nA stock category is required for all stock items. The stock category is a text field which groups like stock items together. In selecting stock it is often useful to restrict the search to just the category required. A stock category select box shows on the Select Product screen for this purpose.\r\n<br><br>\r\n<font size=\"+1\"><b>Unit of Measurement</b></font>\r\n<br><br>\r\nThis field describes how the stock is to be maintained and is self-explanatory. It is a compulsory field. The selection list is maintained in config.php as an array variable. To add or delete other units of measurement the array variable called $InventoryUnits defined in config.php must be edited by the system administrator.\r\n<br><br> \r\n<font size=\"+1\"><b>Economic Order Quantity</b></font>\r\n<br><br>\r\nThis is the quantity that new orders are made or purchased in by default. Currently this is purely for memorandum - in future this will be a critical part of the MRP calculations.\r\n<br><br>\r\n<font size=\"+1\"><b>Packaged Volume</b></font>\r\n<br><br>\r\nThis field is required for freight calculations. It records the volume of the one of the item as packaged for dispatch. See the section on freight calculations.\r\n<br><br>\r\n<font size=\"+1\"><b>Packaged Weight</b></font>\r\n<br><br>\r\nThis field is required for freight calculations. It records the weight of the one of the item as packaged for dispatch. The freight calculation takes the higher of the freight costs based on the total weight of the order and the total volume of the order. Most freight companies use a conversion and charge the higher of the two. See the section on freight calculations.\r\n<br><br>\r\n<font size=\"+1\"><b>Units of Measure</b></font>\r\n<br><br>\r\nA select box allows the units applicable to the item to be selected from the array of $StockUnits defined in config.php. The unit of measure will show on all invoices and on stock inquiries.\r\n<br><br>\r\n<font size=\"+1\"><b>Current or Obsolete</b></font>\r\n<br><br>\r\nIf this field is set to obsolete, this indicates that the stock item is no longer used. However, there may be a stock history on invoices and credit notes which are required. The stock item master record is therefore still required. (Deletion is not allowed while stock movements or Sales analysis records exist for the part) However, it will not appear on searches for invoicing, stock items, in addition the item will be flagged as discontinued in stock reports.\r\n<br><br>\r\n<font size=\"+1\"><b>Make Or Buy</b></font>\r\n<br><br>\r\nThis field is compulsory and indicates whether the stock item is:\r\n<br><br>\r\nManufactured\r\n<br><br>\r\nPurchased from an external supplier. \r\n<br><br>\r\nAn assembly of other stock items. An assembly item does not have a physical stock holding itself, nor has it a cost. An invoice for an assembly item creates the stock movements for all the components of the item and the stock of each of the components in proportion to the requirements specified in the bill of material are decremented. The cost of sales entries in the general ledger journals created by an invoice (if the link is active) is created at the sum of the costs of all the items in the bill of material for the assembly as at the time of invoicing.\r\n<br><br>\r\nA kit set of other stock items that should be exploded into its components when ordered. A kit set is not a physical item itself it is just a short cut to entering a number of parts onto an order. Unlike an assembly, the kit set part does not appear on invoices or orders, but \"explodes\" into its component parts for modification. It follows that kit sets do not have any cost or physical stock quantities associated with them.\r\n<br><br>\r\nA dummy item that has no physical stock associated with it.\r\n<br><br>\r\nThis field is relevant for MRP and production scheduling. This field can also be set to Dummy Inventory Item - which then disables stock quantity tracking at the locations and in total. Dummy stock items are used for invoicing services and labour or even physical items which do not require tracking. The stock movement records are still created and are available to recall on invoicing and credit note inquiries. Inventory adjustments are not allowed for Dummy Inventory Items. Assembly items can have sales analysis records created for them, but no stock quantity is maintained and stock movement records are created for the components. Assemblies would normally be used where the warehouse keeps the components of the item for several different purposes and only assembles the items as they are picked for dispatch.\r\n<br><br>\r\n<b>Setting Up Assembly Items</b>\r\n<br><br>\r\nUsing the example of toilet suites, where the business wishes to keep track of how many sold - i.e. The sales analysis - but only wishes to track the quantities of stock held of the toilet pans and toilet cisterns that make up the suite.\r\n<br><br>\r\nThe business will never want to hold stock of made up suites, when stock is counted only cisterns and pans are counted not suites. If there are any made up suites these would need to be broken down into their components and the components included in the stock counts.\r\n<br><br>\r\nSuch parts are called \"Assembly\" items by the system.\r\n<br><br>\r\nWhen an assembly item is invoiced, the stock of the components of the assembly are decreased by the quantity in its bill of material multiplied the number of the assembly item sold.\r\n<br><br>\r\neg. A suite that has 2 screws in it to screw the pan to the floor might have a bill of material:\r\n<br><br>\r\n1 x cistern\r\n<br>\r\n1 x White S trap pan\r\n<br>\r\n2 x Pan screws\r\n<br>\r\n1 x pan pac carton\r\n<br><br>\r\nIf the sale was for 10 of these - the stock of the cistern, pan and pan-pac cartons would be reduced by 10 and the stock of the pan screws would be reduced by 20. The stock movement inquiry shows this movement and the reference to the assembly item that it came from and the invoice number it was sent out on.\r\n<br><br>\r\nIt is important to remember that when an assembly item is credited this process happens in reverse - stock is booked back into the components in exactly the same way as it is invoiced out. The credit note must be entered against the correct stocking location otherwise the pan and cistern stocks in the location where the stock did get returned to and the location where the credit note was entered to will be wrong. There is facility to write the stock off at the time of credit note entry also - in this case the stock location is not important.\r\n<br><br>\r\nThis next point is a little confusing. The system also shows the movement of assembly items in the movement inquiry for the assembly item itself. This does have the advantage of showing to whom the assembly items have been sold at a glance. However, there are no stock status inquiries for assembly items since they are not stocking items (only the components are held and picked to make up the assembly item at the time of dispatch).\r\n<br><br>\r\nWhen parts are first set up - the description and stock category needs to be defined and it is at this point that the type of item is defined - from the heading on that screen titled - \"Make, Buy, Kit, Assembly or Dummy Part\". The choices are \"purchased\" - the default, \"manufactured\", \"assembly\", \"kit-set\" or \"dummy\". \r\n<br><br>\r\nConsidering the treatment of assembly items:\r\n<br><br>\r\nIf there is stock of an item as shown on the stock status screen - then it is NOT possible to convert the item to an assembly by changing this flag - the stock must first be adjusted back to nil and the corresponding component stock adjusted up. Also, if there are purchase orders for the item - it cannot be converted to an assembly. Remember the assembly part does not exist as a separate part only as an assembly of real stock items for the purposes of selling.\r\n<br><br>\r\nHaving set the part to be an assembly, then the part will show an option on the \"Select Item\" menu to show the costed bill of material. This inquiry shows the current Bill Of Material (BOM) for the assembly together with the cost of each of the components and the total of the costs that is used in the sales analysis and general ledger postings.\r\n<br><br>\r\nSetting up an assembly part requires setting up A Bill Of Material - BOM for the item.\r\n<br><br>\r\nFrom the manufacturing tab of the main menu - under the Maintenance section - click the link \"Bill Of Material Maintenance\". The item search fields show with the option to enter either an extract from the item description or the item code. Having entered the selection click on the \"Search Now\" button. Not all items will show - only those items that can have a BOM - assembly items, manufactured parts and kit-sets. If the part is not defined as an assembly item - it won\'t show up! \r\n<br><br>\r\nClicking the part code button will then show a screen for entering components to make up the BOM. If a BOM already exists for the item it will show the components already set up. There are two links next to each item shown in the current BOM to enable the line to be edited or deleted.\r\n<br><br>\r\nTo enter a new component into the BOM for an assembly, all that is required is to select the component code from the list - the location and work centre are not used for assembly items so the default can be accepted as is. (The location specified in the sales order is used for all the assembly components stock entries.) The quantity required of the component for each assembly item is required to be entered - it defaults to 1 (and maybe 1 in most cases).\r\n<br><br>\r\nThe effectivity dates - effective to and effective after (also known and engineering change control dates) are the dates between which the component is used in the assembly. If the BOM is due to change and customers have been advised that a new pan will be used in suites effective from 1 Jan 05 then the new pan could be entered as a component from that date. The existing pan effective to would have to be changed to be effective to 31 December 04. The alternative is to change all the BOMs on the day when the change is made.\r\n<br><br>\r\nOnce the component fields have been entered hitting the enter information button adds the component to the BOM there and then. When the BOM is complete just navigate to the main menu of other link to the next task. Be careful not to click enter information button on the default component by mistake - the component must be selected from the list first.\r\n<br><br>\r\n<font size=\"+1\"><b>Controlled</b></font>\r\n<br><br>\r\nThis field denotes whether the item is to have lot control on it. Lot control is known by several terms including, batch control, bundle control, roll control. In short it allows a reference for each batch of the item to be entered at the time of delivery, transfer, stock check, invoicing etc. When booking in controlled items the batch references and the quantities in each batch must be entered. Many quality control systems that require traceability of raw materials need this functionality to establish what batch of raw material was used in a given work order. Or what roll of cloth was sold to a given customer. Other industries call their lots of product by different names a melt, a cut, a run etc. Every time a transaction involving a controlled item is created, the system will accumulate the quantity from a separate form for selecting the batches (Lot/roll/melt/work order/serial numbers).\r\n<br><br>\r\n<font size=\"+1\"><b>Serialised</b></font>\r\n<br><br>\r\nSerialised is a special case of controlled where the batch size is always one. The system will prompt for the serial number of each item of stock being received and stock records will be maintained for all serial numbers separately. Serialised does not have any effect if controlled is not set.\r\n<br><br>\r\n<font size=\"+1\"><b>Bar Code</b></font>\r\n<br><br>\r\nThis field is the unique identifying bar code for this item, typically containing the EAN bar code for the item. This field is used in identifying the item in EANCOM  edi messages.\r\n<br><br>\r\n<font size=\"+1\"><b>Discount Category</b></font>\r\n<br><br>\r\nIs used in the discount matrix of discount categories and sales types. When an order is for an item with a discount category code and a customer of a sales type that has a discount matrix entry and the order is for a quantity more than the matrix quantity break figure, the system automatically enters the discount into the order lines for all items in that discount category. This allows quantity break discounts across a range of similar products.\r\n<br><br>\r\nThe discount category is a 2 character field and there is no error checking on entries.\r\n<br><br>\r\n<font size=\"+1\"><b>Decimal Places</b></font>\r\n<br><br>\r\nSome items of stock require a number of decimal places to be retained and displayed, others do not require any. This field is used throughout the system for invoices, orders and all places where the item quantity is displayed.','S'),(11,104,'<font size=\"+2\"><b>Inventory Costing</b></font>\r\n<br><br>\r\nFrom the menu select the inventory tab and Modify Standard Costs. The item code can be entered directly to show the current cost structure, modifications to costs in this screen are reflected in standard cost journals in the general ledger if the link is active in the Company Preferences page - (menu - System Setup tab). Alternatively, to select the stock item to modify the costs for the costing page can be called from the Select Item page - once an item has been selected - a link to the Select Item page is shown on the title bar of all pages. Maintenance and initial entry of cost data is a critical part of the system set up. Simply defining the item header is not sufficient on its own. The cost information entered here is used for both inventory valuation reports and for the general ledger integration stock journals created for the cost of sales, the cost value of stock adjustments, the value of stock received and so on. It can be omitted only if the user is not interested in the value of stock and no general ledger - stock integration is required.\r\n<br><br>\r\n<font size=\"+1\"><b>Material Cost</b></font>\r\n<br><br>\r\nIf the item is bought in, this field should be the average or expected cost of the item. If the item is manufactured in house, it should be the cost of externally sourced materials plus the total cost of in house manufactured components, from lower levels in the bill. In future this will link to the Bill Of Materials cost roll up. Alterations to this figure affect the value of all stocks of this item held. The system is a standard costing system. When items are purchased the actual purchase cost is compared to this figure and a variance reported to the general ledger account set up in the stock categories record applicable to the item.\r\n<br><br>\r\n<font size=\"+1\"><b>Labour Cost</b></font>\r\n<br><br>\r\nThis field should be the total standard cost of labour included in the cost of this stock item. The labour cost is the labour applicable only to this part not to the manufacture of components at a lower level in the bill of materials. The labour cost of componentry would be included in the material cost. Alterations to this figure affect the stock value of all stock held. See comments for Material Cost. In future, this field will be altered automatically on roll ups of cost.\r\n<br><br>\r\n<font size=\"+1\"><b>Overhead Cost</b></font>\r\n<br><br>\r\nThis field should be the total overhead to be recovered on the sale of 1 unit of the stock item. Its value affects the value of all stock held of this item, as for labour cost and material cost above. Labour cost, Material cost and Overhead cost are all added together to get the total cost standard cost of a stock item. This total is the value applied to stock valuations. In future, the cost roll up will automatically update this field based on the work centre overhead rates and the quantity of labour at each work centre.\r\n<br><br>\r\n<font size=\"+1\"><b>Standard Costing Considerations</b></font>\r\n<br><br>\r\nEach time a stock item is sold, the cost of sales account (specified by the COGS GL Postings table by reference to the stock category and sales area of the branch) is updated with the total of material cost, labour cost and overhead cost, and the stock value (the account specified in the stock category record) is decreased by the total standard cost. \r\n<br><br>\r\n<font size=\"+1\"><b>Actual Cost</b></font>\r\n<br><br>\r\nThe actual cost is the last purchased cost as updated by the system on a purchase. For a manufactured item this is the actual cost as calculated including only efficiency variances (price variances are excluded)\r\n<br><br>\r\n<font size=\"+1\"><b>Alterations To Labour Cost, Material Cost or Overhead Cost</b></font>\r\n<br><br>\r\nSince the system only maintains the one standard cost of items, this is the cost that stocks are valued at. In altering this cost all stock held of the stock item under review will be re-valued or devalued accordingly. If stock journals are being created (the option is set in the company preferences from the System Setup tab) then a system generated journal will be created for the effect on stock value using the general ledger accounts set up for the stock category currently defined for the stock item being changed.\r\n<br><br>\r\nOnly transactions entered after the charge will be created at the new cost. Sales analysis will refer to the costs as at the time when the transactions are created.\r\n<br><br>\r\nThere is a utility available from the Z_index.php page that allows the sales analysis for a specified period to be updated with the current costs. This utility takes the quantity sold and multiplies the current standard cost by the quantity to re-calculate the cost of each sales analysis record in the period selected - the sales analysis data is updated. This will mean that the general ledger costs will then differ to the sales analysis cost for the period since the general ledger postings are created at the time of invoicing/crediting.','S'),(12,91,'<font size=\"+2\"><b>Selecting Inventory Items</b></font>\r\n<br><br>\r\nFrom all pages in the system the main title bar has links to select from the critical tables in the system. Customers, inventory and suppliers. Clicking on the \"Select Item\" link takes the user directly to the inventory item selection page. In addition, from the menu, the Inventory tab has a link to \"Modify or Delete an Inventory Item Or Maintain Pricing\". Using either method, the select item or product page will show.\r\n<br><br>\r\nThe search for a particular item can be narrowed by selection of a specific stock Category - a select box allows selection of the category. Also if some element of the code is known this can be entered, alternatively if some element of the description is known this can be entered - the description keywords are used if both a code and description fields are used. Note that the text entered into these fields does not have to be the start of the code or the description. All items with the text anywhere in either the code or the description respectively will be displayed, together with the total stock on hand and the unit of measure for the item. Assembles, kit-sets and dummy parts will all show zero stock on hand. \r\n<br><br>\r\nThe maximum number of resultant items can be defined by a parameter in config.php - this limits the number of records returned so that users over a lower bandwidth connection are not penalised for making too wide a selection. Downloads of significant amounts of data over a slower dial up connection would result in serious degradation in performance. By default this is set at 100 items.\r\n<br><br>\r\nClicking on the button of the code of the item that is required will select it and links to the options applicable to the item will be displayed. \r\n<br><br>\r\nHaving selected an item it is possible to modify:\r\n<br><br>\r\n<ul>\r\n<li>Item master information\r\n<li>Costs\r\n<li>Pricing\r\n<li>Reorder levels \r\n<li>Purchasing data\r\n</ul>\r\nIt is also possible to inquire on:\r\n<br><br>\r\n<ul>\r\n<li>Inventory Movements\r\n<li>Status - showing quantity on hand, on order with suppliers and on sales orders for customers by location\r\n<li>Historical usage by month\r\n<li>Outstanding sales orders\r\n<li>Outstanding purchase orders\r\n<li>Completed sales orders\r\n<li>Bill of Material\r\n</ul>\r\nFurther it is possible to initiate transactions to:\r\n<br><br>\r\n<ul>\r\n<li>Transfer stock between locations\r\n<li>Adjust stock quantities on hand.\r\n</ul>\r\nAt the bottom of this page, there is also a link to perform a fresh search for a different item.','S'),(13,119,'<font size=\"+2\"><b>Amending Inventory Items</b></font>\r\n<br><br>\r\nThe master information is available for amendment. The only field that cannot be altered on the main stock item page, is the code. The code is used in many tables throughout the system and modification of the stock code would require data in many other tables of information to make corresponding changes. A special utility is available that allows stock code changes to be made. The changes to the database are extensive and it is recommended that this facility be run out of normal operating hours since the load on the server for the changes could be substantial in a large database. See special utilities. The following points should be considered when altering data fields:\r\n<br><br>\r\n<font size=\"+1\"><b>Changing a Category</b></font>\r\n<br><br> \r\nThe Category of a stock item can be altered at any time. \r\n<br><br>\r\nHowever, if the stock journals option is active (in the company preferences set up), it should be remembered that the general ledger coding set up for that category can not be amended retrospectively. In other words, general ledger journals created for invoices, credit notes and stock adjustments created using the old category general ledger codes cannot be altered. The general ledger coding could be amended with a manual journal if necessary.\r\n<br><br>\r\nNote: Sales analysis done by Inventory Category is performed with the stock categories as they were when the invoice/credit was created. Inventory Category information is stored in the sales analysis table and cannot be changed retrospectively.\r\n<br><br>\r\n<font size=\"+1\"><b>Alterations to the Make or Buy Flag</b></font>\r\n<br><br>\r\nA stock item that currently has stock cannot be changed to a Dummy stock item, an Assembly item or a kit set item. The system does not maintain stock quantities for these items and it is therefore inappropriate to have an initial stock quantity. The stock must be either invoiced, credited or adjusted to zero for all locations before this can be changed. Similarly, the system will only allow a Bill of Material to be set up for manufactured, assembly or kit set items. Altering a manufactured item (or assembly or kit set) which currently has a Bill Of Material to a purchased item is also disallowed, the BOM must be deleted first.','S'),(14,102,'<font size=\"+2\"><b>Inventory Categories</b></font>\r\n<br><br>\r\nInventory Categories are the natural groupings which apply to certain types of stock. A business will normally have several distinct categories. E. g. A manufacturing jeweller might have stock categories for gold chain, silver chain, diamonds, rubies etc.\r\n<br><br>\r\n<font size=\"+1\"><b>Inventory Category Code</b></font>\r\n<br><br>\r\nThis is a six character text field which is the reference used internally by the system. The category code must be unique between different categories. It cannot be changed retrospectively and cannot be deleted where stock items are set up to use the category, or sales analysis records exist using the category. The category code is stored against sales analysis records for reporting. All stock items must be defined as belonging to a category and has the appropriate category code stored against it in the StockMaster table.\r\n<br><br>\r\n<font size=\"+1\"><b>Inventory Category Description</b></font>\r\n<br><br>\r\nThis description is used in searches for stock items and in sales analysis reports to describe the category of stock referred to. Up to 20 characters of text can be entered, although on some reports less than the full description will appear. \r\n<br><br>\r\n<font size=\"+1\"><b>Balance Sheet Inventory GL Account</b></font>\r\n<br><br>\r\nThis is the general ledger account where system-created standard costing journals will post the balance sheet entry. It is possible to set up a GL account for each stock category or to post all raw material stock categories to a raw material general ledger account and similarly for finished goods stock categories. The select box lists all the available balance sheet accounts - profit and loss accounts will not show. If a new general ledger account needs to be set up this should be done first. This field will not be used if the company preferences flag for \"Create GL entries for stock transactions (at standard cost)\" is not enabled.\r\n<br><br>\r\n<font size=\"+1\"><b>Inventory Adjustments GL Posting Account</b></font>\r\n<br><br>\r\nThis is the general ledger account where the system created standard costing journals will post the profit and loss entry to for stock adjustments. It can be different for any or all stock categories. The select box lists all available profit and loss accounts. This field will not be used if the company preferences flag for \"Create GL entries for stock transactions (at standard cost)\" is not enabled.\r\n<br><br>\r\n<font size=\"+1\"><b>Purchase Price Variance Account</b></font>\r\n<br><br>\r\nThis general ledger account is used by accounts payable invoice entry to post variances between standard cost and the actual cost of stock items of this category. Price variances are calculated when the purchase invoice is entered, unless the purchase order item is on a shipment. Shipment purchase price variances are calculated when the shipment is closed. This field will not be used if the company preferences flag for \"Create GL entries for stock transactions (at standard cost)\" is not enabled.\r\n<br><br>\r\n<font size=\"+1\"><b>Material Usage Variance Account</b></font>\r\n<br><br>\r\nNOT YET IMPLEMENTED: \r\n<br><br>\r\nThis general ledger account is used by the works orders to post the variances between the actual material issued to a work order and the standard requirements of the work order based on the number of finished items resulting from the work order. This field will not be used if the company preferences flag for \"Create GL entries for stock transactions (at standard cost)\" is not enabled.\r\n<br><br>\r\n<font size=\"+1\"><b>Type of Resource</b></font>\r\n<br><br>\r\nNOT YET IMPLEMENTED: \r\n<br><br>\r\nThis field declares the type of resource that the stock category represents and is one of Finished Goods, Raw Materials, Labour or Dummy Inventory. This field is used to distinguish labour type stock items from other components for the purposes of calculating work centre overhead in the cost roll up. It is also used for ordering stock reports to ensure raw materials and finished goods are grouped together.','S'),(15,48,'<font size=\"+2\"><b>Inventory Location Maintenance</b></font>\r\n<br><br>\r\nA table of locations is maintained which contains a location code of 5 characters and the location description (up to 20 characters). The description is the field used in lookups on invoicing and crediting. To keep the database as compact as possible it is the code which is stored against stock movement transactions. As many locations as required can be set up. When a new location is defined, location records for all items in the database are created. When a location is deleted, so too are all the item location records. However, a location record cannot be deleted if there are stock movements or stock held for any part in the location. Since the location represents the physical location from where goods/services are supplied it is important for determining the tax applicable on a sale. The governing tax authority must specified with the location record. It is used in the calculation of the applicable rate of tax in conjunction with the tax authority of the customer branch being delivered to and the tax level of the item being sold.\r\n<br><br>\r\nNote: Location codes cannot be modified retrospectively. Neither can they be deleted if there are stock movements, users (default location) or customer branch records set up to receive stock from the location.','S'),(16,100,'<font size=\"+2\"><b>Inventory Adjustments</b></font>\r\n<br><br>\r\nInventory can be written on or off for individual stock items using this option. Corrections to physical stocks and deliveries of stock can be entered using this option. Adjustments can be entered by selecting the link on the SelectProduct.php page or directly from the menu under transactions. Using the second link, the item code must be known, there is no facility to select an item code from this page. \r\n<br><br>\r\nIf Stock GL integration is enabled from the company preferences page (under the setup tab), then the system creates the necessary journals in the general ledger to update the stock account and the profit and loss account stock adjustment account specified in the stock category record. (see Inventory Categories above and also see General Ledger Integration later)\r\n<br><br>\r\nNote: Inventory Adjustments are not possible with kit set, assembly or dummy stock items since no stock record is maintained for these items.\r\n<br><br>\r\nInventory adjustments for controlled items must select the batches/serial numbers being adjusted.','S'),(17,117,'<font size=\"+2\"><b>Inventory Location Transfers</b></font>\r\n<br><br>\r\nTransfers of inventory between locations need to take stock off the sending location and increase the stock in the receiving location. This is effected in one transaction by entering a stock transfer. In the same way as adjustments these can be accessed either from the SelectProduct.php page which is linked to from every page on the top header banner or directly from the menu - inventory tab under the transactions section. All that is required is the location from and to, the date and the quantity being sent. If the business wishes to record inventory in transit an additional in transit location needs to be established and a transfer to the transit location performed when the stock is dispatched and a further location transfer from the transit location to the receiving location when the stock is received. Stock location transfers are shown on stock movement inquiries.\r\n<br><br>\r\nNote: Inventory Location Transfers are not possible with kit set, assembly or dummy stock items since no stock record is maintained for these items.\r\n<br><br>\r\nBulk inventory transfers consisting of many parts can be set up effective from 2.9. These allow a docket to be produced to be sent with the dispatch of the goods. The transfer is not actually effected until the transfer is received in by the receiving stock location.','S'),(18,115,'<font size=\"+1\"><b>Inventory Status inquiries</b></font>\r\n<br><br>\r\nShows the detail of how much stock is held and where the stock is (i.e. how much at each location) together with the total quantity of sales orders for the part outstanding and the quantity of the item on outstanding purchase orders can also be viewed from this inquiry. Not only are outstanding sales orders for this part accumulated but all the demand for parent assembly parts as extended by the quantity required per unit of the parent. This ensures that all demand for the part is shown.\r\n<br><br>\r\nThis - and all stock inquiries are called from the SelectProduct.php page - this page is linked to from every page in the system from the header links \"Select Item\". Once an item is selected the relevant inquiry options are displayed. As  with all web pages produced by the system most browsers have good facilities to print them. \r\n<br><br>\r\nIf the item selected is a controlled item then the status inquiry will also show a link to show the batches. If the item is serialised, the link will refer to the serial numbers rather than batches. ','S'),(19,111,'<font size=\"+1\"><b>Inventory Movement inquiries</b></font>\r\n<br><br>\r\nInventory movements for the item show with the most recent movements at the top of the screen in descending order of date.\r\n<br><br>\r\nThis inquiry shows the movements of the stock item for a specified location with further links to the transaction detail that created the movements such as the invoice or credit note. By default only the last 3 months stock movements show but the date from which stock movements are displayed can be modified from the inquiry page.\r\n<br><br>\r\nNote: Inventory movement inquiries are possible on dummy and assembly stock items. However, since dummy items are not physical stock items, no record of the amount of stock and the location of where held (there is none held) are maintained. The location inquiry will always be zero for a dummy/assembly and kit set items. When a dummy stock item, or an assembly or kit set item is selected the link to the stock status inquiry is not available.\r\n<br><br>\r\nIf the stock movement was an invoice or a credit the customer and the price in the currency invoiced or credited will show. ','S'),(20,118,'<font size=\"+1\"><b>Inventory Usage inquiries</b></font>\r\n<br><br>\r\nThis inquiry shows the quantity of the stock item that has been consumed either in sales net of credit notes and adjustments by month from each location. Transfers between locations are ignored as are deliveries. Usage of an item adds all the stock movement quantities for the part so that stock movements related to the sale of assembly items requiring the part under review as a component are included. Usage can be seen by location and also the total usage from all locations.\r\n<br><br>\r\nThere is a user modifiable flag which determines how many months of stock usage should be maintained for the item.','S'),(21,47,'<font size=\"+1\"><b>Inventory Valuation Report</b></font>\r\n<br><br>\r\nThis report shows the stock on hand at the time the report is run as extended at the current standard cost. Both reports show the total quantity of stock held by Location and by Inventory Category. The detail report lists the individual stock items and value of each. Inventory items with no stock on hand are not printed out. This report can be run with only a selected inventory category and location, to reconcile the stock general ledger accounts.\r\n<br><br>\r\nA separate script is included in the distribution that can be set to run the stock listing report and email it to predetermined email addresses. To take advantage of this facility, the script must be called by wget under a linux distribution from a crontab entry on the last day of each month. The script is called MailInventoryValuation.php. This script will need to be edited with the email address of the user to receive the report and any parameters necessary to run the report. By default it produces a detailed report for stock at all locations. The entry in crontab in a linux installation that would send the report at 0:0 on the first day of each month (assuming wget is installed in /usr/bin/wget and that the web server is on the same machine) would be:\r\n<br><br>\r\n# Scheduled email of monthly Inventory Valuation report\r\n<br><br>\r\n-0 0 1 * * root /usr/bin/wget <a href=\"http://localhost/web-erp/MailInventoryValuation.php\">http://localhost/web-erp/MailInventoryValuation.php</a>\r\n<br><br>\r\nsince the page has no output - it would be pointless running it in a web browser although it would initiate another email to the defined recipients.','S'),(22,46,'<font size=\"+1\"><b>Inventory Planning Report</b></font>\r\n<br><br>\r\nThis report shows the usage of stock items over the previous four months together with the current stock, outstanding sales orders and outstanding purchase orders. It suggests a re-order quantity based on a number of months times the maximum of the previous four months monthly stock usage (from all stock locations), less the on hand stock and purchase orders. It will not suit all purposes but certainly does provide good information from which to base purchasing decisions.\r\n<br><br>\r\nThe Inventory Planning Report is run from the Inventory tab of the main menu under the Inquiries and Reports section.','S'),(23,103,'<font size=\"+1\"><b>Inventory Checks</b></font>\r\n<br><br>\r\nScripts are available to automate the stock check adjustments necessary following a stock check. However, this feature does not work with controlled items .... yet. The procedure for there use and for stock checks in general is as follows:\r\n<br><br>\r\n<b>Step 1:</b>\r\n<br><br>\r\nEnsure invoicing is complete for all product dispatched.\r\n<br><br>\r\n<b>Step 2:</b>\r\n<br><br>\r\nFrom the main menu - inventory tab, click on the link to print out the stock check sheets.\r\n<br><br>\r\nThis page allows selection of the stock location to be checked and a range of stock categories to check. Note there is a new field called:\r\n<br><br>\r\nAction for stock check freeze. \r\n<br><br>\r\nBy default this is set to list the items to be counted. Before a stock starts this must be run selecting \" make a new stock check data file\". This copies the quantities on hand as at the time the report is run. If the stock check sheets are run for another location and it is set again to make a new stock check data file, then the previous stock check data file is over-written and lost. If there is a stock check in progress in one location and another location wishes to initiate one too, then the second person to run the stock check sheets needs to print them with the Action for Stock Check Freeze field set to Add/update existing stock check file. Similarly if another stock category is to be added to the stock check then - Add/update existing stock check file is the correct option.\r\n<br><br>\r\n<b>Step 3:</b>\r\n<br><br>\r\nOnce the stock check data file is created - the stock should be counted.\r\n<br><br>\r\n<b>Step 4:</b>\r\n<br><br>\r\nThe counts should be entered - the system will only accept count entries for parts that were included in the categories selected for stock taking in step 2. The system allows many counts for each item and totals all counts to work out the required adjustment. Also, the initial of the counter or reference to the count sheet can be entered to provide a trail of where the stock was that is entered. As soon as there are counts to enter - the counters should pass on the count sheets to the data entry team so that the data entry can be completed as soon as possible after the counting is complete.\r\n<br><br>\r\n<b>Step 5:</b>\r\n<br><br>\r\nHaving completed the entry of the counts the Comparison report can be run. \r\n<br><br>\r\nThis is run from the Inventory tab of the main menu:\r\n<br><br>\r\nCompare Counts Vs Stock Check Data\r\n<br><br>\r\nThere are number of options for running this report. The default is to run the report without making any adjustments. It should be run in this mode initially so that a review of the proposed adjustments is undertaken. Any adjustments that seem incorrect can now be double checked (i.e. counted again) as necessary.\r\n<br><br>\r\n<b>Step 6:</b>\r\n<br><br>\r\nOnce happy that the adjustments to be made are correct the report can be run again with the option to Report and Close the Inventory Comparison File and Process Adjustments as Necessary. \r\n<br><br>\r\nThere is no going back on this process - if you didn\'t mean to process these adjustments they must all be reversed manually. Be very sure when running the comparison in this mode.','S'),(24,26,'<font size=\"+2\"><b>Entering New Customers</b></font>\r\n<br><br>\r\nFrom the menu Receivables tab, click on \"Add A New Customer\". The customer record is the actual charge account, any number of branches can be defined for each customer account. At least one branch must be defined for each customer. It is the branch details that record the local information about delivery address, tax authority, sales area and sales person.\r\n<br><br>\r\n<font size=\"+1\"><b>Customer Code</b></font>\r\n<br><br>\r\nThis field is used as a unique identifier for the customer which is small (maximum of 10 characters of text - letters or numbers). This allows faster searches for customers and keeps the size of the database at a minimum. \r\n<br><br>\r\nNote: A customer code can be altered retrospectively but because of the large scale of the changes required to the system to update stock movement transactions, sales analysis records and sales orders it could present a significant drain on resources. The option to change a customer code is in the utility menu Z_index.php - which must be entered as a manual URL and is only accessible by the system administrator. Such changes should only be undertaken when there are no other users on the system.\r\n<br><br>\r\n<font size=\"+1\"><b>Customer Name</b></font>\r\n<br><br>\r\nThe name of the customer is used extensively in lookups and appears on invoices and statements exactly as typed (searches are independent of case). Proper capitalisation and use of the full name of the customer is recommended. The maximum length of the name is 40 characters.\r\n<br><br>\r\n<font size=\"+1\"><b>Address Line 1, 2, 3 and 4</b></font>\r\n<br><br>\r\nThis is the address of where invoices and statements are to be posted. A post office box should be entered if applicable. These fields will allow a maximum of 40 characters and are optional. If left out invoices will not show the customer\'s address which is a requirement for a valid GST or VAT invoice in some administrations. \r\n<br><br>\r\n<font size=\"+1\"><b>Currency</b></font>\r\n<br><br>\r\nThis is a compulsory field that identifies the currency in which the customer is to be invoiced. All invoices created for the customer will use this currency and the rate will be used as set up in the currency master - accessible from the General Ledger module. The currency rates need to be maintained at least monthly.\r\n<br><br>\r\nNote: that the rate is recorded in the transaction not the currency. If a customer changes the currency in which they are invoiced it is possible to allocate a new currency receipt against invoices (invoiced in the old currency) in which case the system will calculate large differences on exchange. Note: There is no error check on this and care should be taken, should this occur, to ensure invoices are matched off in the currency in which they were originally created - the cross rate between the new and old currency will provide an equivalent amount of the old currency for entry and allocation.\r\n<br><br>\r\n<font size=\"+1\"><b>Invoice Discount</b></font>\r\n<br><br>\r\nCustomers can be set up with a general level of discount which applies to all sales. The percentage entered is used by default on all invoices to the customer. The discount rate can be manually overridden at the time of invoicing, which the system will use to calculate discount. The discount percentage used at invoice time will not be stored as the future default unless the customer details are amended. This field checks to ensure that the percentage is less than 100% and greater or equal to zero. \r\n<br><br>\r\nThis feature is not currently used/implemented.\r\n<br><br>\r\n<font size=\"+1\"><b>Prompt Payment Discount</b></font>\r\n<br><br>\r\nCustomers can be set up with individual prompt payment discount percentages. This field is for memorandum purposes only and shows on screen when customer receipts are entered. If the receipt is on time the discount can be calculated and input with the receipt.\r\n<br><br>\r\n<font size=\"+1\"><b>Customer Since</b></font>\r\n<br><br>\r\nThe default is the current date. This field is only used on certain reports where it can be used to restrict the customers shown, and also for credit reference inquiries. The system will only accept a date in this field. The date can be altered at any time. There is no error checking on this field.\r\n<br><br>\r\n<font size=\"+1\"><b>Payment Terms</b></font>\r\n<br><br>\r\nA select box is provided showing the predefined payment terms. New ones can be created as necessary from the Setup tab of the menu. The terms are used to determine whether an account is overdue or not and a description of the terms prints out on invoices, and statements.\r\n<br><br>\r\nThere are no restrictions on altering payment terms for a customer at any time. The next statement or invoice produced will show the new terms. The system will only look at the new terms in deciding whether or not invoices are overdue, no record of the terms is held in the transaction itself. \r\n<br><br>\r\n<font size=\"+1\"><b>Credit Status or Rating</b></font>\r\n<br><br>\r\nA lookup is available for this field and a button is available for creation of new credit status categories. Credit status\'s can be defined to stop any new invoices or indeed orders to the customer - irrespective of his/her credit limit.\r\n<br><br>\r\nA customers Credit Status can be altered at any time. \r\n<br><br>\r\nNote: This will never show on any reports invoices, credits or statement intended for the customers eyes.\r\n<br><br>\r\n<font size=\"+1\"><b>Credit Limit</b></font>\r\n<br><br>\r\nThis is a required field which accepts any positive value. It is currently only for memorandum purposes.\r\n<br><br>\r\n<font size=\"+1\"><b>Invoice Addressing</b></font>\r\n<br><br>\r\nThis field is a flag that has two choices:\r\n<br><br>\r\n<ul>\r\n<li>To address invoices to the head office postal address. This is the postal address from the customer record as entered above. This is the default option.\r\n<li>To address invoices to the branch postal address. This addresses invoices charged to the customer to the branch of the customer, some customers may wish for the branch to authorise invoices before sending on to head office for payment. The branch postal address entered will be used for printing invoices.\r\n</ul>','S'),(25,26,'<font size=\"+2\"><b>Amending Customer Details</b></font>\r\n<br><br>\r\nThe customer details can be modified at any time and any modifications made will be effective for all future transactions. First select the customer from the link provided on all pages, then select the link for Modify Customer Details. \r\n<br><br>\r\nPoints to note:\r\n<br><br>\r\n<ul>\r\n<li>Customer codes can be modified retrospectively using the facility in the utilities menu Z_index.php - this should be used only when all other users are off the system.\r\n<li>Changing the payment terms impacts immediately on the aged analysis since this is recalculated each time it is run or a statement is produced.\r\n<li>Changing the currency of the customer needs care since the exchange rate is recorded against each transaction. Allocations of one currency against another at different rates will produce differences on exchange. The system will not be able to distinguish at the transactions level which was in what currency only the rate against the functional currency.\r\n</ul>\r\n<font size=\"+2\"><b>Salesperson Records</b></font>\r\n<br><br>\r\nSalesperson details can be entered or amended from the setup tab of the main menu.\r\n<br><br>\r\n<font size=\"+1\"><b>Salespeople Code</b></font>\r\n<br><br>\r\nThis field can accept any text or numbers up to a maximum of 3 characters - normally the initials of the salesperson or some reference to the area NW1,SW2 would suffice. These salespeople codes are stored against each branch to identify who services them. actual\r\n<br><br>\r\nNote: The salesperson code is not held against individual transactions. This means that if there is any change to a salesperson for an area and you wish to calculate commissions up to the change and after for the new salespeople, the dates of transactions must be used. However, sales analysis records created at the time of invoicing and crediting are created including a copy of the salesperson code applicable at the time of invoicing. Sales analysis reports will thus show who made sales based on the historical representative settings.\r\n<br><br>\r\n<font size=\"+1\"><b>Salesperson Name, Telephone and Fax Numbers</b></font>\r\n<br><br>\r\nThe salesperson name is what appears in the look-ups on the customers\' branch maintenance page. It is also used for inquiries and reports. By default it shows on invoices and statements, to remind customers where they can contact their local agent for sales inquiries.\r\n<br><br>\r\nThe name can be any character to a maximum of 30 characters. The phone number and fax number fields can be a maximum of 20 characters long. \r\n<br><br>\r\n<font size=\"+1\"><b>Salesperson Commission Rates and Breakpoint</b></font>\r\n<br><br>\r\nThe system allows for input of these items as the basis of commission calculations. Most businesses have different ways of calculating commissions. For this reason it is left to the business to write a report which will use these fields (and/or additional ones) to calculate commission according to traditional company policy.\r\n<br><br>\r\n<font size=\"+2\"><b>Sales Types/ Price Lists</b></font>\r\n<br><br>\r\nSales types are the broad categories which sales fall under, they should not be confused with stock categories. They are arbitrary headings for the different pricing arrangements applicable to each type of customer. For example the fashion house might have sales types for:\r\n<br><br>\r\n<ul>\r\n<li>Indented sales - ordered in advance.\r\n<li>Stock sales - sold from stock.\r\n<li>Made to Measure - sales made up especially for a customer.\r\n</ul>\r\nInventory categories by contrast refer to the type of garment sold. Each customer is defined as belonging to a specific sales type. A sales type could also be used for groupings of customers. Price lists are held against sales types so all customers defined as belonging to the sales type will be on the same pricing.\r\n<br><br>\r\nPricing can be set up for each stock item by sales type (and currency), e.g. stock sales would attract a premium for the cost of the increased working capital required or perhaps a discount reflecting the scale of purchasing allowed on volume lines. Sales types for retail, wholesale, trade, special may also be appropriate in other types of business.\r\n<br><br>\r\nNew sales types/price lists can be added and existing ones amended from setup tab, \"Sales Type Maintenance\"\r\n<br><br>\r\n<font size=\"+1\"><b>Sales Type Code</b></font>\r\n<br><br>\r\nThis is a 2 character code which is stored against the customer to define the sales type applicable to the customer. It must be unique across all sales types. When entering orders the customer sales type is the key, together with the customer\'s currency that determines the price list to use.\r\n<br><br>\r\n<font size=\"+1\"><b>Sales Type Description</b></font>\r\n<br><br>\r\n20 characters of text are allowed to describe the sales type. This field is used to describe the sales type / price list applicable to the customer.\r\n<br><br>\r\n<font size=\"+2\"><b>Payment Terms</b></font>\r\n<br><br>\r\nFor each customer the payment terms which the business deals with them under must be specified. This provides the information required to monitor whether the account is due or not - ie for Overdue inquiries and reports. Also, the invoices, credit notes and statements, contain reference to these terms so he/she is continually reminded of these terms with every transaction as a matter of course.\r\n<br><br>\r\nPayment terms can take one of two forms:\r\n<br><br>\r\n<ul>\r\n<li>Due after a specified number of days after invoice.\r\n<li>Due on a day in the month following invoice\r\n</ul>\r\nThe system allows the definition of a payment terms record either way. \r\n<br><br>\r\nNote: The overdue report and inquiries use the estimated number of days to deliver in the branch record to determine when the customer received the goods, the payment terms are added to the day when delivery was effected.\r\n<br><br>\r\n<font size=\"+1\"><b>Payment Terms Code</b></font>\r\n<br><br>\r\nThis is the code which is used by the system internally to reference the payment terms applicable to a customer. Alterations to this code will cascade through all the customers set up with the code being amended. If there are a lot of customers set up with this code it could take a moment to update all customers.\r\n<br><br>\r\nIt is a 2 character text field.\r\n<br><br>\r\n<font size=\"+1\"><b>Payment Terms Description</b></font>\r\n<br><br>\r\nThis field (40 characters of text) shows on customers statements, invoices and lookups in the inquiries and selection forms. It should describe as succinctly as possible the payment terms.\r\n<br><br>\r\n<font size=\"+1\"><b>Days Before Due / Day In Following Month When Due</b></font>\r\n<br><br>\r\nOnly one of these fields needs to be entered depending on the selection in the bullet options. If the terms are after a number of days, then the field expects the number of days after which the invoice becomes due. Alternatively, the day in the following month when the invoice will fall due (greater than or equal to 0 and less than or equal to 30). These figures are used to calculate whether or not an invoice is overdue for the overdue reports and inquiries.\r\n<br><br>\r\nThese fields expect a number - other characters are not accepted.','S'),(26,22,'<font size=\"+2\"><b>Entering Customer Branches</b></font>\r\n<br><br>\r\nA customer code entered on its own is insufficient to enter sales orders against. All customers must have at least one branch. The branch records contain the information about the delivery address, the sales area, the tax code, the sales representative and other regional information. New branches can be set up at any time. The first step will always be to select the customer, then to Add/Edit/Delete Customer Branch Records from the Customer menu. There is no limit to the number of branches that can be referenced against a single charge account (customer record). However, branches cannot be transferred between different customers. Only one statement will print per customer, but each invoice will be referenced to the branch it was delivered to.\r\n<br><br>\r\n<font size=\"+1\"><b>Branch Name</b></font>\r\n<br><br> \r\nThe branch name can be up to 40 characters long. The branch name is used on packing slips to identify the name to where delivery should be made. It is also used in lookups in sales order entry as it is to the branch that the sale is ordered against. By selecting the branch from this lookup, the customer code and branch code is returned to the system for storing against the order.\r\n<br><br>\r\n<font size=\"+1\"><b>Branch Code</b></font>\r\n<br><br>\r\nThe branch code is stored in transactions. It can be up to 10 characters long and should uniquely identify the branch. Two branches cannot have the same code for the same customer. Branch codes (and other codes) cannot contain spaces, commas, & or - . There are checks in place to ensure no new codes are created containing these characters. However, if data is imported it is important to change any codes which contravene these rules.\r\n<br><br>\r\nBranch codes are not available for modification retrospectively. They exist in many tables throughout the database - sales analysis, orders, DebtorTrans and CustTrans and all data in these tables would need to be modified.\r\n<br><br>\r\n<font size=\"+1\"><b>Branch Contact / Phone / Fax / Address</b></font>\r\n<br><br>\r\nThe branch contact is the name of the purchasing manager to whom goods and inquiries would be addressed at the branch. A head office branch could be created to identify contact names for account inquiries. It doesn\'t matter that such a branch may not be invoiced to.\r\n<br><br>\r\nThe address is the physical address of the branch for deliveries. As much detail as is required on labels to get the goods to the desired destination should be entered.\r\n<br><br>\r\n<font size=\"+1\"><b>Sales Person</b></font>\r\n<br><br>\r\nA select box is available to enter the salesperson who services this branch. \r\n<br><br>\r\n<font size=\"+1\"><b>Draw Stock From</b></font>\r\n<br><br>\r\nBranches local to a particular warehouse can be associated with the local warehouse by setting this field to the preferred stocking location. This affects the entry of sales orders that default to the stock location entered here. It can be overridden at the time of order entry.\r\n<br><br>\r\n<font size=\"+1\"><b>Forward Date From A Day In The Month</b></font>\r\n<br><br>\r\nThis field is not currently used.\r\n<br><br>\r\n<font size=\"+1\"><b>Delivery Days</b></font>\r\n<br><br>\r\nThis field is not currently used.\r\n<br><br>\r\n<font size=\"+1\"><b>Phone/Fax/Email</b></font>\r\n<br><br>\r\nThese are the contact details applicable to the branch.\r\n<br><br>\r\n<font size=\"+1\"><b>Tax Authority</b></font>\r\n<br><br>\r\nThe tax authority of the branch determines the regime for tax calculations at invoice and credit time. \r\n<br><br>\r\nTax Authorities are the authority that collects the taxes. Different authorities in different countries mainly or different states in the USA. Say sales tax in New York is 10% and sales to customers in Michigan attract sales tax at a different rate payable to a different state tax authority. \r\n<br><br>\r\nIn the UK, Australia and NZ, the Inland Revenue, Australian Tax Office and  the IRD are the relevant tax authorities. Sales to customers that fall under the ATO from a company in NZ do not attract any tax - ie exempt export. However, the same customer who has a branch in Auckland NZ will fall under a different tax authority the NZ IRD - attracting GST @ 12.5% for the same item delivered there. \r\n<br><br>\r\nSo the tax authority is the factor relevant based on where the branch of the customer is who is receiving the supply.\r\n<br><br>\r\nThe tax authorities are maintained in a separate table (available from the Setup tab) and the tax rate applicable to invoices and credits and the general ledger posting of tax is determined from the Tax Authority setting of the branch together with the applicable level of tax specified in the item (stock) master. \r\n<br><br>\r\nA select box allows the appropriate tax authority for the branch to be selected.\r\n<br><br>\r\n<font size=\"+1\"><b>Disable Transactions</b></font>\r\n<br><br>\r\nThis flag - either enabled or disabled - either includes the branch as available for new sales orders or not. If it is set to disabled then the branch will not appear on the order entry branch lookup.\r\n<br><br>\r\n<font size=\"+1\"><b>Default Freight Company</b></font>\r\n<br><br>\r\nA select box allows the selection of the freight company that is most cost/service effective for deliveries to this branch. The entry in this field serves as the default for future sales orders. If it is changed at order entry stage, the overridden entry become the new entry for this field.\r\n<br><br>\r\n<font size=\"+1\"><b>Postal Address 1, 2, 3 and 4</b></font>\r\n<br><br>\r\nThis is the postal address of the branch. There is no checking done to ensure this is entered. The only place this would be used is if the customer record required that invoices and credits be addressed to the branch.','S'),(27,96,'<font size=\"+2\"><b>Shippers</b></font>\r\n<br><br>\r\nThis table is only used as a reference to the method of transportation that the invoice delivery was effected by. The shippers name is what is used in the look ups and the code is a system maintained counter which is stored in the database to reference the freight company used. The consignment note reference can be stored against the transaction in the reference field. In this way a full proof of delivery trail can be maintained. \r\n<br><br>\r\nShipment cost calculations can be automated and the result automatically added to invoices. The least cost shipper is calculated and modified on the order. This system requires active maintenance of the shipment costs tables for each shipper. The shipper costs table shows the cost per kg and metre cubed for freight to destination cities. A substantial amount of data is required. For the system to calculate freight, it also requires that each part have a defined volume and weight set up from which the total order weight and volume is determined for use in the freight calculations.\r\n<br><br>\r\nIf freight cost calculations are to be performed at order entry stage, the data must be entered and the options in config.php enabled - see the config.php section.','S'),(28,16,'<font size=\"+2\"><b>Credit Status Ratings</b></font>\r\n<br><br>\r\nEach customer can be given a credit status that describes the risk as assessed by the credit controller, attached to invoices to the customer. This will never appear on any reports to customers. The customer selection screen allows customers to be restricted by their credit status so it is easy to perform inquiries on all the customers with a similar credit status. In addition the system will not allow invoices to customers whose credit status has a check in the field to prohibit invoicing. This is another credit control tool to the credit limit. \r\n<br><br>\r\nCredit Status Ratings can be amended and created from the main menu set up tab from the link labelled Credit Status Maintenance.\r\n<br><br>\r\n<font size=\"+1\"><b>Status Code</b></font>\r\n<br><br>\r\nThis is any number from 1 to 100. A rating code is required to be held against every customer to identify the credit status of each. The code cannot be changed retrospectively.\r\n<br><br>\r\n<font size=\"+1\"><b>Status Description</b></font>\r\n<br><br>\r\nThis field is used in look ups both in the customers amendment/entry and also in the customer selection screen. This is a text field up to 30 characters long.\r\n<br><br>\r\n<font size=\"+1\"><b>Disallow Invoices</b></font>\r\n<br><br>\r\nFor all credit ratings set up, this field indicates whether or not the rating is serious enough to prohibit future invoicing to customers set up with this credit rating.','S'),(29,131,'<font size=\"+2\"><b>Tax Calculations</b></font>\r\n<br><br>\r\n<font size=\"+1\"><b>Overview</b></font>\r\n<br><br>\r\nMany countries and states have varying methods for the calculation of ad-valorem taxes on sales. There is some flexibility in the configuration of how these automatic calculations are made and to which general ledger accounts the postings are made to. If all else fails tax rates can be entered by line of the invoice or credit as necessary. Different rates of tax can be applied to each line item individually depending on:\r\n<br><br>\r\n<ol>\r\n<li>Where the branch is that is to invoiced.\r\n<li>What the item being invoiced is - e.g. In many states reduced rates of taxes apply to the more essential items for basic sustenance.\r\n<li>From where the item is dispatched i.e. the warehouse  from which the goods are picked. The tax legislation in most countries require that supplies made within the country attract tax - GST/VAT or whatever.\r\n</ol>\r\n<font size=\"+1\"><b>Setting up Taxes</b></font>\r\n<br><br>\r\n<ul>\r\n<li>All the tax authorities under which goods are to be sold must be defined in the tax authorities table under the set up tab. The tax authority record requires the specification of the general ledger account codes where purchase tax is to be posted and where sales taxes are to be posted to, as well as a description as to what the tax authority is called. eg. UK Inland Revenue VAT, ATO -GST(Australia), NZ IRD-GST (New Zealand). If there are several ways in which taxes can be defined within a single authority then it is appropriate to set up another authority. One example might be the Netherlands where as well as charging tax (at 3 different levels) it is possible to dispatch goods to a customer in the Netherlands - from a warehouse in the Netherlands without adding tax to the invoice where the customer has an exemption arrangement and has agreed to pay the VAT on its purchases  themselves directly to the Dutch revenue authority.\r\n<li>Each customer branch is defined as falling under a particular taxing authority. The tax authority is the state body responsible for the collection of taxes. Note that the tax authority is not held against a particular charge account - customer account. It is the branch where the goods are delivered to which is important in determining how the tax calculations work, so it is the branch that is defined as within a particular tax authority.\r\n<li>Each stocking location or warehouse is also defined as within a particular tax authority. This is specified from the locations maintenance form.\r\n<li>Each stock item is defined as being subject to tax at a particular \"tax level\". The tax level is set in the stock item maintenance screen. In setting the tax level for each item some thought is required as to the taxing of the item under different authorities. Most tax authorities have just two rates of tax applicable i.e. 0% and the normal tax rate eg. 10% for Australia, 12.5% New Zealand. However, in some authorities there may be several tiered rates - the Netherlands has three rates of tax. The levels set for the items must be set based on the requirements of the tax authority (which the business requires goods to be sold within) with the most tax levels. The tax level entered against each stock item is entered against the stock master record. It is simply a number from 0 to 9 and defaults to 1.\r\n<li>The applicable rates of tax then need to be defined. A rate is required for supplies from each Stock Location Tax Authority to each of the defined Tax Authorities and Tax Levels. It is this table of rates that allows the system to determine the rate applicable to each line item invoiced, based on the branch to which the item is to be supplied, the item itself and the location from where the goods are delivered.\r\n</ul>\r\n<b>Sales only within one Tax Authority Example - 2 Tax Levels:</b>\r\n<br><br>\r\nIn most tax authorities there are just two rates of tax 0% and some other rate lets say 10%. Sales to export customers will not be taxable as they are exempt. If the business simply operates in the one country, then things are very simple. Items that are zero rated have the stock master record modified to give them a tax level of 2. All other taxable items have the default tax level of 1.  Two Tax Authorities are defined, the first the country in which it operates and the other called Export. Customer branch records must be set with the Tax Authority of the country or Export. The location record of each of the warehouses is defined as being the country tax authority. Now the rates applicable to export tax authority will all be 0 for tax level 1 and tax level 2. The rate of tax applicable to the country tax authority will be 10% for level 1 and 0% for level 2.\r\n<br><br>\r\n<b>Sales only within one Tax Authority Example - 3 Tax Levels:</b>\r\n<br><br>\r\nMaybe zero-rated for food and 5% on clothing and 10% on all other goods. The tax rate of all food items should be set to say 2 and the tax rate of all clothing items set to 3 - all other taxable items at the full rate set to a tax level of 1. The tax rates for the country tax authority for tax level 1 set to 10%, level 2 0% and level 3 5%.\r\n<br><br>\r\n<b>Sales Within Two Tax Authorities Example - 3 Tax Levels:</b>\r\n<br><br>\r\nThe business has a warehouse in another country - (Tax Authority A) or state with a different revenue collection authority and one authority has two levels and the other has 3 levels (Tax Authority B) as in the two examples above. Rates need to be defined for deliveries from each warehouse to customer branches in each tax authority. In most cases the rate of tax applicable to exports will be 0% - exempt. Tax Authority A will have entries applicable to deliveries within Tax Authority A for each of the 3 tax levels (even though only 2 are applicable - the rate for level 3 will be the same as 1) and also for deliveries to Tax Authority B. Tax Authority B will have rates set up for each level for deliveries within Tax Authority B and also for deliveries to Tax Authority A - the later rates will most likely be 0%.\r\n<br><br>\r\nThe logic used in determining the rate to be used is as follows:\r\n<br><br>\r\nThe TaxAuthority of the warehouse the goods are delivered from, the TaxLevel of the item and the Tax Authority of the branch of the customer being delivered to are determined. Using all three of these factors the rate is returned from the TaxAuthLevels table.\r\n<br><br>\r\nGeneral ledger posting relating to the taxes calculated are made in accordance with the codes set up in the Tax Authority table.','S'),(30,72,'<font size=\"+2\"><b>Pricing and Discounting</b></font>\r\n<br><br>\r\n<font size=\"+1\"><b>Overview</b></font>\r\n<br><br>\r\nPrices are held by Sales Type and Currency. When setting up a customer account the sales type applicable to the customer is defined as is the currency applicable to invoices to the customer. There is no limit to the number of sales types that can be defined and therefore no limit to the number of different price lists that can be maintained. Further flexibility is available in that where necessary prices specific to a customer can also be defined, normally pricing will be specific to a customer but the system also has the capability of making prices specific to a particular branch of a customer. If a price is flagged as specific to a branch it will not be used for all other sales of the item to the customer, the default price of the item will be used. Further flexibility in discounting is available through the discount matrix (see below). If an item has an additional discount this can be entered at the time of order. The user also has the ability to change the price manually at the time of order. Sales analysis records allow reporting on sales types, so the analysis will report the volume of business done under each set of special pricing arrangements. \r\n<br><br>\r\n<font size=\"+1\"><b>Maintaining Prices</b></font>\r\n<br><br>\r\nTo maintain pricing, first an item must be selected. This can be done from the link \"Select an Item \" on any page or from the inventory tab of the menu the link \"Modify Or Delete An Inventory Item Or Item Pricing\" allows for a part to be selected. Once selected the options available to work with the item are shown, there is a link to \"Maintain Pricing\". The pricing page displays, the sales type and the currency of all prices defined for the item. The existing prices can be deleted or modified from this screen and new prices can be entered. It is not possible to enter two prices for the same sales type and currency.\r\n<br><br>\r\nTo enter prices for a specific customer and branch, the customer must first be selected. (See selecting customers). Going to the select an item page after having selected an item now shows an additional link to maintain the special pricing \"Special Prices for customer - customer code\". Clicking this link shows, the normal price for the sales type and currency of the customer selected and any special prices defined for this item/customer combination. If no special pricing is defined then the page shows only the normal price with a note that no special pricing is defined for this item and allows entry of the new special price. If a special price exists then it can be deleted or changed. A customer with many branches can have a special price for each branch or just a single branch having a special price beyond the special customer price. A special price applicable to only one branch will not apply to any of the other branches. Also, a special price set up for the customer account but with a blank branch field will apply to all branches unless there is a specific price set up for the branch.\r\n<br><br>\r\nPrices can be amended at any time. However, changes to a price do not automatically cascade into sales orders for the item that are yet to be delivered, these would have to be changed manually as necessary.\r\n<br><br>\r\nThere is a utility page that has some options for making bulk changes to pricing avoiding extensive re-keying exercises. Great care should be taken with this facility. System administrator permission is required to access this page accordingly.\r\n<br><br>\r\nThe script is called: Z_PriceChanges.php and is accessed from the Utilities index Z_index.php\r\n<br><br>\r\nThis script allows bulk increase/decreases by percentage for specific or a range of stock categories and for specific sales types. There is the opportunity to update the pricing:\r\n<br><br>\r\n<ul>\r\n<li>For all prices for the sales type within the stock categories selected or\r\n<li>For only the default prices for the sales type within the stock categories selected or\r\n<li>For the selected customer special prices - provided the customer selected belongs to the sales type selected in the bulk change screen.\r\n</ul>\r\nEqually flexible options exist for printing price lists from the main menu under the orders tab under inquiries and reports.\r\n<br><br>\r\n<font size=\"+1\"><b>Discount Matrix</b></font>\r\n<br><br>\r\nThe discount matrix allows discounts to be set for quantity breaks across a range of products. Eg a business selling pens may have red, blue, black etc but offer a discount for orders greater than 100 irrespective of colour - even though these are separate items.\r\n<br><br>\r\nItems to have such discounting must be specified in the stock item details screen as belonging to the same Discount Category - this is a 2 character field in the stock master table. In the example above the item codes for blue, red and black pens should all be set to the same 2 character code.\r\n<br><br>\r\nCustomers to whom the discount structure should apply are specified by the sales type (the price list) field. The entry in the Discount Matrix table should refer to the sales type and the 2 character DiscountCategory of parts, the quantity above which the discount is to apply and the rate of discount applicable. Several entries in the discount matrix can be made at increasing discounts and volumes, for the same Discount Category and Sales Type.\r\n<br><br>\r\nWhen an order is entered the system automatically reads the Discount Category of the parts and accumulates the quantities of like Discount Category parts to see if the order lines qualify for the discount. If special discount has been entered exceeding the discount matrix calculated rate - the higher manual entry over-rides the calculation. However, discount manually entered which is below the discount matrix calculation will be uplifted to the discount matrix rate.\r\n<br><br>\r\nTo set up the discount matrix there are two elements:\r\n<br><br>\r\n<ol>\r\n<li>The discount categories - need to be defined. Individual stock items can be modified in the stock details form or a special facility is available under the set up tab of the main menu - under the inventory set up section. This screen allows stock codes to be entered and verified before changing to the selected discount category.\r\n<li>The discount matrix itself. This is modified from a link on the set up tab under accounts receivable set up. For each sales type/price list the quantity break and discount applicable must be entered.\r\n</ol>','S'),(31,92,'<font size=\"+2\"><b>Invoicing An Order</b></font>\r\n<br><br>\r\n<font size=\"+1\"><b>Selecting an Order To Invoice</b></font>\r\n<br><br>\r\nAll invoices require a sales order to be entered first. \r\n<br><br>\r\nFrom the main menu select the orders tab. Select Outstanding Sales Orders Maintenance. This page shows all the orders outstanding. If the order number is known it can be entered on this screen to select the order to invoice. Hit search orders and the order should show below, together with links to modify the order, print the packing slip and to invoice. Click the link to invoice the order.\r\n<br><br>\r\n<font size=\"+1\"><b>Producing An Invoice From A Selected Order</b></font>\r\n<br><br>\r\nHaving selected an order to invoice the order line comes up for confirming the quantities of the order that were dispatched. If the quantity dispatched differs from the order the difference is recorded in the table OrderDeliveryDifferencesLog - and a report is available to show the orders that were not able to be delivered with the first dispatch. There is also opportunity to enter the freight charge and if necessary enter the tax charge - which will normally be calculated automatically based on the tax authority of the customer branch being invoiced. The date of the invoice is defaulted based on the time of day and the settings in config.php. If the hour (in 24 hour format) is after the setting of $DispatchCutOffTime in config.php, then the following day is deemed to be the invoice date, alternatively the invoice date will default to today. Where not all lines on the order are being invoiced there are two choices with how to deal with the balance. \r\n<br><br>\r\n<ul>\r\n<li>Put the balance on back order\r\n<li>Cancel the line on the order\r\n</ul>\r\nFinally there is also a field for entry of any text on the invoice. Hitting the process invoice button updates the order as instructed and produces all the entries including general ledger postings (if integration is enabled in the company preferences screen - see setup) to record the invoice. Until the process invoice button is hit, no entries have been saved to the database and it is safe to leave the page at any stage without having changed anything - the invoicing process can be cancelled at any time simply by following a link to another page. The processing that takes place once the Process Invoice button is hit includes:\r\n<br><br>\r\n<ul>\r\n<li>Creation of the stock movements for each line item on the order - or for the assemblies components - from the location entered at the time of the order, at the price as per the order. \r\n<li>Creation of the DebtorTrans record that records the invoice against the customer\'s account.\r\n<li>Creation of the general ledger jorunals to record the sale and debtor etc.\r\n<li>Updating the order for amounts dispatched, and the invoice number.\r\n<li>Creating/updating the sales analysis records of the items being sold.\r\n<li>Updating the stock quantities for all lines of the invoice and the components of all assemblies included on the order.\r\n</ul>\r\nIf the order is not to be invoiced to the customer or branch specified in the order, or pricing is to be changed then the order must be changed. These elements cannot be altered at the time of invoice, they must be altered in the order before it is confirmed for invoicing. Once an invoice is created it cannot be deleted or modified. The order is also updated with the invoice number that it was dispatched on.','S'),(32,87,'<font size=\"+2\"><b>Credit Notes</b></font>\r\n<br><br>\r\nCredit notes can be created in one of two ways:\r\n<br><br>\r\n<ul>\r\n<li>From a customer inquiry screen if the user has the necessary permissions ( $PageSecurity=3 - see Security Schema) a link shows to allow an entire invoice to be credited. Having clicked this link there is opportunity to de-select some items from being credited so that only the part of the invoice that needs to be credited can be, with only minimal keying. The same credit note creation page as used in manual creation of credit notes will appear but with all the items from the orignal invoice already entered into the credit note.\r\n<li>Using the link on the main menu under the receivables tab, select the link to create a credit note. \r\n</ul>\r\n<b>Important Note:</b>\r\n<br><br>\r\nIt is important to use credit notes to correct incorrect invoices correctly. Crediting a small price against a part implies that the goods were returned and the customer only credited a fraction of their worth. This is not the correct way to credit an overcharge. By default credit notes return the stock to the location specified, so the stock records will be overstated by the goods returned to stock by the credit note. To correct a pricing error a credit note for the invoice line at the incorrect charge must be done and a new invoice at the correct charge must be made up. This ensures that sales analysis records are not corrupted for the quantities sold and that stock records are maintained correctly. A special pricing adjustment type of credit note is available that does not have any cost implications for the sales analysis and has no stock physical movement record associated with it.\r\n<br><br>\r\nThe process for creating a credit note manually is:\r\n<br><br>\r\n<ul>\r\n<li>Select the customer to be credited, there are the usual selection options (a box to enter an extract of the customer\'s name and a box to enter an extract of the customer\'s code) \r\n<li>Select the items to be credited and the prices to be used in crediting the customer - the same quick entry option is available as is used in order entry. - where the part code and quantity to be credited is entered directly. Pricing is automatically determined by reference to the customer\'s sales type, currency with regard to any special pricing for the customer (and branch) being credited. If the search for a part functions are used, then after each part is selected the quantity can be updated after the item is selected.\r\n<li>Having selected all the items it is possible to edit the items to be credited by clicking the button of the code of the item on the summary, then editing the price and quantity.\r\n<li>Amounts to be credited for freight can be entered directly (this would be entered directly from the original invoice if the credit an invoice option was used from the customer inquiry screen).\r\n<li>The tax amount to credit is calculated automatically by default based on the tax authority of the branch being credited and the total of the line items and freight to be credited. It is also possible to select the manual option. Once having selected manual, the user should hit update to allow free entry of any amount in the tax field.\r\n<li>By default it is assumed that the goods in the credit note are being returned to stock. The location to where the goods are being returned must be selected from the selection box.\r\n<li>If the goods are not being returned to stock, they are to be written off perhaps as samples or showroom display, damaged goods or whatever, the credit note type option should be changed to goods written off. After changing the credit note type and hitting the update button, a new select box will allow a general ledger code to be selected (assuming the general ledger interface to accounts receivable is active - it will not show if there is no integration). The appropriate general ledger code should be selected from this box. The location to return the stock to select box will disappear since it is no longer relevant. A third option is to credit a pricing adjustment - this type does not create any stock movements and the sales analysis updates only affect the value no cost of sales updates take place.\r\n<li>Any text describing the reasons for the credit note should be entered in the narrative box provided.\r\n<li>After completing all the inputs required, hit the Process Credit Note button to create the credit note. The created credit note number will be reported confirming that it has been processed.\r\n</ul>','S'),(33,24,'<font size=\"+2\"><b>Entry Of Receipts</b></font>\r\n<br><br>\r\nThis system tracks the invoices and credits which are outstanding (a so called open item system) in contrast to systems which use a balance brought forward from the previous month to add and subtract current month transactions. Experience has shown balance forward systems whilst intuitive, often result in queries for more information with the inevitable question from customers \"what was this balance made up of ?\" . The statements produced by this system show a full reconciliation of the amounts outstanding against invoices and credits that are yet to be settled totalling the amount of the customer\'s account. In order to provide the necessary information to track outstanding amounts, invoice by invoice, the detail of the make up of payments must be entered.\r\n<br><br>\r\nPayments received from customers are therefore entered in a two-stage process:\r\n<br><br>\r\n<ul>\r\n<li>The amount of the payment received is entered in foreign currency together with the exchange rate at which this has been banked into local currency. Any details pertinent to the receipt such as the date, method of payment and any details (which can be recalled from inquiries later) are entered at this stage.\r\n<li>The foreign currency received is allocated to the invoices (and debit journals) on the customer\'s account. Put another way, the invoices that the payment is meant to be settling are matched off against the payment.\r\n</ul>\r\nIf the details of the make up of a payment received are not available at the time of banking, the receipt can still be entered to stage 1. However, the allocation must be done before the statement is produced if the account is to make sense.\r\n<br><br>\r\nNote: Differences on exchange are only calculated once the receipt is matched against the invoices it is paying.\r\n<br><br>\r\nReceipts relating to general ledger transactions can also be entered in the same batch as customer receipts.\r\n<br><br>\r\nThe process of entering receipts is initiated from the main menu under the receivables tab - another link is also available from the general ledger tab. \r\n<br><br>\r\nFirstly, the receipt header information is required, the bank account - one of the previously defined bank accounts (see setup), the date the batch of receipts are banked, the currency and exchange rate of the banking and the type of receipt together with any narrative. The currency can be selected from the defined currencies (see setup). The receipt types can also be selected - they are defined in config.php. Once this information is entered it must be accepted before the receipts in the batch can be entered.\r\n<br><br>\r\n<font size=\"+1\"><b>Receipt - Customer</b></font>\r\n<br><br> \r\nBy default once the a customer has been selected the following information is displayed:\r\n<br><br>\r\n<ul>\r\n<li>The payment terms applicable, so amounts overdue can be easily noted from the allocation screen without having to go back and do an inquiry.\r\n<li>The payment discount percentage applicable. The user can then use this rate if applicable to calculate the discount applicable, depending on how much of the payment relates to \"on time\" invoices.\r\n<li>The currency that the currency is paying in.\r\n</ul>\r\n<font size=\"+1\"><b>Receipt - Date</b></font>\r\n<br><br>\r\nThe date that the receipt was received and banked. If a receipt is being entered retrospectively - or several days bankings are being done together, the default date (i.e. the current date) should be over written with the date the receipt was originally received. This date is used on the statement and the customer may not be able to tie up the receipt if an incorrect date is entered.\r\n<br><br>\r\nCustomer account inquiries are shown in date order so the account will not show correctly if the date entered is not the date the money was received. The date is also used in the general ledger transaction created.\r\n<br><br>\r\n<font size=\"+1\"><b>Receipts - Currency and Exchange Rate</b></font>\r\n<br><br>\r\nSelection of the customer automatically tells the system which currency to expect the receipt in. The customer\'s account is maintained in the currency selected in the customer maintenance screen. \r\n<br><br>\r\nThe correct rate at which the bank has converted the foreign currency to local currency must be input, the system shows the calculation of the local currency banked at the bottom of the screen. The receipt cannot (therefore) be entered until the amount in local currency is known. The exact rate to enter in this field will be the foreign currency figure divided by the local currency figure.\r\n<br><br>\r\nEg banked 1212 local, in customer\'s currency this was 400. \r\n<br><br>\r\nRate is 400/1212 = 0.330033\r\n<br><br>\r\nThe local currency calculated by the system should confirm that the rate entered is correct. The general ledger integration - if enabled - will produce a bank deposit for the local currency amount shown at the bottom of the screen, and reduce (credit) the Debtors Control account by the same amount. The system defaults the exchange rate to that set up against the currency in the currencies table.\r\n<br><br>\r\nWhen the receipt is matched to invoices, any differences between the local currency amounts banked against the local currency invoiced are recorded against the invoices and written off the general ledger Debtors Control Account and written into the profit and loss account - (specified in the company record of the customer concerned) if the general ledger integration is enabled from the module options screen.\r\n<br><br>\r\n<font size=\"+1\"><b>Receipts - Payment Method</b></font>\r\n<br><br>\r\nThe payment method is stored against the receipt and shows on the customer\'s statement. A banking report can also be run off based on the payment method to summarise the day\'s bankings, to automate the task of collating the different vouchers and summarising for the bank.\r\n<br><br>\r\n<font size=\"+1\"><b>Receipts - Amount</b></font>\r\n<br><br>\r\nThe amount of the receipt in foreign currency is entered here. This cannot be 0. Although, negative receipts are allowed (to reverse incorrect receipts). \r\n<br><br>\r\nNote: Care should be taken when allocating negative receipts to ensure that only previous allocations are reversed, strange results could occur if allocations are made to invoices not previously allocated to positive receipts - although system integrity will be maintained.\r\n<br><br>\r\n<font size=\"+1\"><b>Receipts - Discount</b></font>\r\n<br><br>\r\nThe amount of discount on a receipt can be entered at this point and allocated together with the receipt as one amount. This is useful, where a customer pays an amount net of discount - quite correctly according to his terms and conditions, and the amount naturally will not tie up to invoices on its own without the addition of the discount. The system calculates the gross amount of the payment including discount to set off the customer\'s account.\r\n<br><br>\r\n<font size=\"+1\"><b>Receipts - Allocating to Invoices</b></font>\r\n<br><br>\r\nOnce all the details necessary have been entered for the receipt - the customer, the exchange rate and the amount in foreign currency, the receipt is ready to be allocated to the invoices which is to settle.\r\n<br><br>\r\nThis concept can seem strange to businesses that have previously operated customer accounts where they are only interested in the current months\' transactions and the balance brought forward from last month. The aim of this system is to remove the question from the customer\'s lips ... \"What is that figure, balance brought forward made up of?\". Under the \"Balance Forward\" system this question can be a tough one to answer, since there is no record of which invoices were paid by which payment. However, this system needs explicit instructions for each receipt on which transactions should be settled as a result.\r\n<br><br>\r\nFrom the menu under the Accounts Receivable tab - Click on the link to Allocate Receipts or Credits.\r\n<br><br>\r\nThis page shows all outstanding receipts and credits that are yet to be allocated. Clicking on the links against these receipts and credits takes the user to the outstanding transactions on the customers account that are available for allocation. This screen shows all unallocated transactions but only invoices are available to allocate the receipt or credit note to.\r\n<br><br>\r\nNote that allocations of a receipt are not allowed to another receipt. If necessary, negative receipts can be used to reverse allocation against invoices and debit journals (although this is undesirable). Once entered, receipts cannot be deleted - (obviously this would be undesirable from the standpoint of proper internal controls).\r\n<br><br>\r\nIf the whole of the receipt is not matched off against (allocated to) invoices and debit journals the system will prompt to ensure that this is what was intended. Unlike many systems, allocations can always be completed or amended later.\r\n<br><br>\r\n<font size=\"+1\"><b>Differences on Exchange</b></font>\r\n<br><br>\r\nThe process of allocating receipts to invoices gives the system the information necessary to calculate the difference on exchange since the receipt converted at the rate specified in the receipt screen will equate to a different amount to the local currency equivalent of the invoices it is matched to, unless both the receipt and the invoices it is allocated to are converted at the same rate. \r\n<br><br>\r\nThe difference calculated at the time of allocation can be seen on the receipt screen once the allocations are done and the screen closed and is itemised against the invoices to which it is allocated against. Unlike many systems the difference on exchange can be fully itemised transaction by transaction. Inquiries on the detail of receipts show the difference on exchange that the receipt is responsible for. Further the inquiry on where the receipt was allocated to will show the analysis of where the difference on exchange for the receipt under review came from.\r\n<br><br>\r\nAlterations to the allocations naturally alter the difference on exchange. The general ledger interface produces a journal for only the movement of the difference on exchange for a given receipt each time its allocations are altered.\r\n<br><br>\r\n<font size=\"+1\"><b>Receipts Processing</b></font>\r\n<br><br>\r\nMany customer receipts can be entered at a time and mixed together with receipts for nominal items i.e. receipts from vending machine or sales of fixed assets reimbursement for private use of company assets etc. Once all receipts have been entered the processing can take place. The system only stores the data entered in a server side cookie called a session until such time as the Process button is clicked. \r\n<br><br>\r\nThe processing will give the batch of receipts a number and insert new receipt transactions against customer accounts and update the customer\'s record with the amount of and the date of the last payment. In addition if the general ledger interface is enabled, the journals to put the receipt into the bank account specified and to decrease the Debtors control account - specified in the company record are created. General Ledger journals are also created for the discount - if any, with the corresponding entry to the Debtors Control account. All the necessary account codes must be set up in the company preferences page under the setup tab and the bank account set up page.\r\n<br><br>\r\n<font size=\"+1\"><b>Deposits Listing</b></font>\r\n<br><br>\r\nAfter processing has completed a link to print the deposit listing for the batch of receipts just entered is shown. The batch number is also reported. The listing shows the information required by banks in processing a batch of cheques. This deposit listing can be reprinted at any time from a link under the accounts receivable tab - reports and inquiries.','S'),(34,21,'<font size=\"+2\"><b>Allocate Credits To A Customer\'s Account</b></font>\r\n<br><br>\r\nThis option allows any credit amount - be it a receipt or a credit notes to be allocated to debit amounts - invoices . Receipts or credits that have previously been allocated are available for alteration of their allocations if necessary. There are two ways to perform this function:\r\n<br><br>\r\n<ul>\r\n<li>From the menu select the accounts receivable tab and click the link to \"Allocate Credits To Customer\'s Account\". \r\n<li>From the customer account inquiry - there is a link to allocate any customer receipt or credit where the user has appropriate privileges.\r\n</ul>\r\nUsing the first method, by default the screen opens with only the receipts, credit notes and credit journals which have an amount outstanding left to allocate. If there are many receipts which have amounts outstanding and are not fully allocated to invoices and debit journals then this is an indication that there are allocations which need to be done. For the customer\'s statements to make sense, the allocations must be done. \r\n<br><br>\r\nDouble clicking the receipt to allocate will then open the form that allows the allocations to be made. This screen brings up all the invoices and journals that are outstanding on this customer\'s account. Invoices that have already been paid off will not show. However, existing allocations made from the selected receipt or credit will show. Clicking a receipt or credit note from the customer inquiry screen brings up this same page.\r\n<br><br>\r\nIf a mistake was made with previous allocations these can be rectified by selecting the previous receipt which was wrongly allocated, all the invoices which the receipt was allocated to will show (together with other invoices which are yet to be allocated to). The amounts allocated against them can be amended at will.\r\n<br><br>\r\nNote: This is a complex area behind the scenes, to handle the changes that may result to the difference on exchange. The system calculates the alteration to the exchange difference which results from allocating the receipt to different invoices (or not being allocated at all) and creates the necessary journal in the general ledger - if the integration option is used - to ensure the debtors control account stays in balance with the list of balances. \r\n<br><br>\r\n<b>It is recommended that help be sought if alterations in the program are required in this area.</b>','S'),(35,23,'<font size=\"+2\"><b>Customer Inquiries</b></font>\r\n<br><br>\r\nComplete \"drill down\" inquiries are available to extract the full detail of transactions. The system maintains more data than many systems, preferring to minimise the use of consolidated or batch entries, in favour of the full detail of each transaction. The enquires can show the account history for a customer and from this inquiry it is possible to look at the details behind each transaction, from the detail of the transaction it is possible to look at the line items on the original invoice and which receipts paid the invoice. The original invoice or credit can be viewed directly from customer inquiries and the general ledger transactions that were created by the invoice, credit or receipt may also be inspected.\r\n<br><br>\r\n<font size=\"+1\"><b>Customer Account inquiries</b></font>\r\n<br><br>\r\nFrom any page, click the \"select customer\" link. Having selected the customer all links applicable to the customer show. Among them the customer transactions inquiry.\r\n<br><br>\r\nBy default the inquiry screen shows only those transactions after a date six months ago. The default period can be modified by simple editing of the script CustomerInquiry.php. If transactions further back are required only occasionally and not by default - the date after which all transactions will show can be altered at will. When the screen opens the customer\'s account is shown in the currency of the customer. The aged analysis based on the customer\'s terms is displayed. \r\n<br><br>\r\nIf the transaction is an invoice or a credit note a magnifying glass icon shows on the same line as the transaction. A click on this magnifying glass then opens a new window showing the detail of the invoice or credit note.\r\n<br><br>\r\nIf the integration to the general ledger from accounts receivable - see the option to \"Create GL entries for accounts receivable transactions\" from the Company Preferences page under setup - then another link with the letters GL will show. This link shows a new page with the detail of the general ledger postings created by this transaction.\r\n<br><br>\r\nIf the transaction is a receipt or a credit note and the user has appropriate access privileges, a book icon shows. Clicking this icon takes the user to the page for allocating receipts and credits - if the receipt or credit is already allocated then the detail of how the receipt/credit was allocated is shown together with other invoices that could be used for allocating against. Invoices that have previously been allocated will not show.\r\n<br><br>\r\nMore (or less) history can be shown by changing the \"Show all transactions after:\r\n<br><br>\r\n\" date - then clicking the refresh button. The balance on the customer\'s account should balance to the sum of the amounts outstanding. The amount outstanding is used to represent:\r\n<br><br>\r\n<ul>\r\n<li>For Invoices, the amount which has not yet been settled by receipts or credit notes\r\n<li>For Receipts, the amount which has yet to be allocated (or matched off) against invoices.\r\n</ul>\r\n<font size=\"+1\"><b>Transaction Detail inquiries</b></font>\r\n<br><br>\r\nThe detail of transactions can be inspected via one of two avenues:\r\n<br><br>\r\n<ul>\r\n<li>From the Customer\'s Account inquiry - click on the magnifying glass or the GL link against the transaction required.\r\n<li>From the Transaction Enquires - from the Accounts Receivable tab, click on the magnifying glass to see the actual invoice or credit note.\r\n</ul> ','S'),(36,2,'<font size=\"+1\"><b>Aged Customer Balance Listing</b></font>\r\n<br><br>\r\nFrom the main menu select Customers, Reports, \"Aged Customer Balances/Overdues Report\".\r\n<br><br>\r\nThis report calculates the balances of the customers\' accounts according to the parameters specified in config.php $PastDueDays1 and $PastDueDays2. The report is generated in the currency of the customer. The currency of the report must first be selected and the range of customers accounts to print.\r\n<br><br>\r\nThe report can be printed at two levels:\r\n<br><br>\r\n<ul>\r\n<li>The full detail of the all outstanding amounts on the account, transaction by transaction analysed into current, Due, $PastDueDays1 and PastDueDays2. The detailed report also shows the terms applicable to each customer.\r\n<li>A summary of the account balances split in the same aged groupings.\r\n</ul>\r\nThis report recalculates the balance on the customer\'s account from the outstanding transactions and analyses each outstanding transaction based on the date of invoice plus the expected time to deliver to the branch invoiced.\r\n<br><br>\r\nThe Aged Customer Balances can also be restricted to show just the accounts where there is some balance overdue. The same detail and summary reports are available. The resultant reports create pdf files that can be either saved or printed.','S'),(37,76,'<font size=\"+1\"><b>Customer Statements</b></font>\r\n<br><br>\r\nThe customer statement shows the outstanding transactions on the account. There is an option in config.php that allows transactions that have been settled at some time within the last month to be shown in a settled transactions section of the statement. The parameter, $Show_Settled_LastMonth should be set equal to 1 for this. If it is set to 0, then any settled transactions will not be shown on the statements. This saves printing zero balance statements and there is argument both ways as to which is best.\r\n<br><br>\r\nWhen transactions are created, the date of the transaction determines the period into which it is posted in the general ledger. The date of the transaction is also used in the aging calculations used on the statement. All transactions to the date of the statement are included. It is not possible to print a statement as at some prior date. It may be necessary to delay the start of invoicing for the new month until all receipts are entered and allocated and the statements printed before processing new invoices that should appear on the following month\'s statement.\r\n<br><br>\r\nProcedure - All receipts must be allocated for the aged analysis to be calculated correctly. Then from the main menu Accounts Receivable tab select from the reports and inquiries section, \"Print Statements\". The link will allow selection of a range of customers. A single customer\'s statement can be printed by selecting the same starting an finishing customer code. Statements will be printed in sequence of the Customer code set up in the customer details screen. A pdf file is created this can be saved or printed.','S'),(38,25,'<font size=\"+1\"><b>Customer Transaction Listing Options</b></font>\r\n<br><br>\r\nThis is accessed from the main menu, accounts receivable tab under the link \"Transaction Inquiries\".\r\n<br><br>\r\nThis inquiry allows transactions to be listed based on the selection of criteria on screen. The page allows listing of receipts, invoices or credit notes within any specified date range.\r\n<br><br>\r\nThis listing is shown on the screen and can of course be printed from the browser if necessary.','S'),(39,81,'<font size=\"+1\"><b>Sales Analysis</b></font>\r\n<br><br>\r\nFrom the main menu under Accounts Receivable Reports and Inquiries options, \"Sales Analysis Reports\". A list of all previously defined sales reports shows with links to:\r\n<br><br>\r\n<ul>\r\n<li>Modify the design of the report - the header\r\n<li>Modify the columns of the report\r\n<li>Run the report and create a PDF file\r\n<li>Run the report to create CSV - comma seperated values report that can be read by any spreadsheet application.\r\n</ul>\r\nAlso from the bottom of this page it is possible to make up new reports to show most combinations of sales data. A seperate table of sales data is maintained by the system to enable these reports to be run as quickly as possible. \r\n<br><br>\r\nA sales analysis report is made up of:\r\n<br><br>\r\n<ul>\r\n<li>the report header defintion and\r\n<li>the report column definitions\r\n</ul>\r\n<b>Sales Analysis Report Headers</b>\r\n<br><br>\r\nThe first step in creating a report is to enter the report header information. This includes:\r\n<br><br>\r\n<ul>\r\n<li>Report Heading - any combination of characters to describe the report.\r\n<li>Up to 4 levels of grouping and the criteria for each grouping.\r\n</ul>\r\nEach grouping consists of a selection of one of:\r\n<br><br>\r\n<ul>\r\n<li>Sales Area \r\n<li>Product Code\r\n<li>Customer code - the actual charge account\r\n<li>Sales type (or price list)\r\n<li>Product type (or stock/inventory category)\r\n<li>Customer branch\r\n<li>Sales person\r\n</ul>\r\nThe report groups on each level in sequence eg. A report with Group By 1 set to Product Code and Group By 2 set to Sales Area, would show the product code, then the areas underneath the product code where the item has been sold. Most often the more logical sequence might be to show the sales area as Group By 1 and the Product Code under the Group By 2 so that the sales of the product codes for each area appear together.\r\n<br><br>\r\nEach Group By section requires a range to be specified. All criteria specified must refer to the coding as specified in the setup section of the relevant Grouping. Eg Sales Areas criteria must be entered as the area code - not the area description. The criteria from should be less than the criteria to otherwise the report will have no output. \r\n<br><br>\r\n<b>Sales Analysis Report Columns</b>\r\n<br><br>\r\nHaving created a header for the report the columns can then be defined. Columns for existing reports can be modified at any time. >From the main Sales Analysis Reports page - (Accounts Revceivable tab - Sales Analysis Reports) - there is a link on each report to \"Define Columns\". >From this link the columns for the report selected are shown, together with a form to allow input of new columns. The following input is required for each column defined:\r\n<br><br>\r\n<ul>\r\n<li>A column number between 1 and 10. This number determines where abouts across the page the column is printed -1 is the closest to the left of the page and 10 is the closest to the right of the page.\r\n<li>Two fields are available for line 1 and 2 of the column heading. Any text can be input to a maximum of 15 characters in each.\r\n<li>Whether the column is a calculation or not. The input required for calculated columns is different from normal data columns.\r\n<li>For normal data columns -a range of periods to show the data for is required. The period from and to must be specified as the period number. Since periods are maintained almost invisibly by the system, there is a link provided from this screen to show the periods defined. If a single month is required then the period from will be the same as the period to.\r\n<li>The data to show in the column can then be selected from: Quantity, Gross Value, Net Value (after discounts), Gross Profit, Cost and Discount.\r\n<li>Budgeted sales or actual sales.\r\n</ul>\r\nIf the column is defined as being a calculation the range of periods are not required, neither is the selection of the type of data to show and the budget or actual. Changing the Calculation field to yes then hitting the enter information button, show the fields required for a calculation:\r\n<br><br>\r\n<ul>\r\n<li>Numerator field - this the column number of the first parameter to the calculation- the numerator field is always required in a calculation.\r\n<li>Denominator field - this is the column number of the second parameter to the calculation if the calculation is to performed on two columns. If the operator of the calculation is to divide - then this column total will be on the bottom of the division calculation. If the calculation is to be done using a constant then this field can be left blank.\r\n<li>Calculation operator can be one of:\r\n<ol>\r\n<li>Numerator column divided by Denominator column\r\n<li>Numerator column divided by the constant\r\n<li>Numerator column multiplied by the constant\r\n<li>Numerator column plus the denominator column\r\n<li>Numerator column minus the denominator column\r\n</ol>\r\n<li>Constant can be any number for use in the calculations\r\n<li>Format type allows the output of the calculation to be expressed as either a percentage or a number.\r\n</ul>\r\nHitting the Enter Information button adds the column to the report definition. It should then show in the list of defined columns and a blank form for entering a new column definition. If a column is no longer required the list of column definitions shown has a link that allows it to be deleted. \r\n<br><br>\r\nThe column number itself is a link to modify the definition of the column. The definition shows in the input form for alternation as necessary.\r\n<br><br>\r\nClicking the link to maintain report headers shows the list of defined reports for modification or running. To run the report simply click on the Make PDF Report link.\r\n<br><br>\r\nThese sales analysis reports have to look at a lot of information (and the reports are generated dynamically) as a result, they can take a minute or so to run on large databases. However, the design of the data has been done very carefully to ensure that reports generate dramatically faster than many such analysis engines.\r\n<br><br>\r\n<b>Automating Sales Reports</b>\r\n<br><br>\r\nHaving created reports it is possible to email sales reports to defined recipients. A script called MailSalesReport.php is a template for doing this. There are no links to this script and no security level is set up for it. All that is required is to save this script to another file named anything with an extension of .php under the same directory as the other scripts. \r\n<br><br>\r\nThis script will need to be edited with the email addresses of the people to receive the report and the reportID - the system generated report number to be run. The lines to edit are:\r\n<br><br>\r\n/*The Sales report to send */\r\n<br>\r\n$ReportID = 4;\r\n<br><br>\r\n/*The people to receive the emailed report */\r\n<br>\r\n$Recipients = array(\'\"Root\" <root@localhost>\',\'\"some one else\" <someoneelese@sowhere.com>\');\r\n<br><br>\r\nThe lines surrounded by /* and */ are comments.\r\n<br><br>\r\nOnce edited to the ReportID or choice and the Recipients all entered the file should be saved.\r\n<br><br>\r\nTo schedule the report to run an entry in crontab under a linux installation that would send the report at 0:0 each week day (assuming wget is installed in /usr/bin/wget and that the web server is on the same machine and the new script is called DailySalesReport.php) would be:\r\n<br>\r\n# Scheduled email of a daily sales report\r\n<br><br>\r\n0 0 * * 2-5 root /usr/bin/wget http://localhost/web-erp/DailySalesReport.php\r\n<br><br>\r\nsince the page has no output - it would be pointless running it in a web browser although it would initiate another email to the defined recipients.\r\n<br><br>\r\nA similar template script is available called MailSalesReport_csv.php that mails the comma separated values file for a ReportID to the Recipients defined in the script in just the same way as MailSalesReport.php script above.','S'),(40,77,'<font size=\"+1\"><b>Printing Invoices Or Credit Notes</b></font>\r\n<br><br>\r\nFrom the Main Menu select the Accounts Receivable tab, under Inquiries and Reports, \"Print Invoices or Credit Notes\".\r\n<br><br>\r\nThis page shows the last invoice and credit note created and some brief instructions on how to use the page. By default the page assumes that invoices are required. However, if credit notes are required, they can be selected from the list field. \r\n<br><br>\r\nIf only one transaction is required then the number of the transaction should be entered in the field \"Start invoice/credit note number to print\". If a range of transactions is required then both fields are necessary to be completed.\r\n<br><br>\r\nThere is also an option to print EDI invoices/credit notes. By default these are not printed as they will normally be dispatched electronically to the customer. However, some businesses and tax authorities require hard copy invoices and credit notes to be produced in any event.\r\n<br><br>\r\nTwo options are available - Print, produces a html page of the invoice (or credit note), the resulting page can then be printed using the browser print functionality. The print PDF produces a PDF invoice (or credit note).','S'),(41,90,'<font size=\"+2\"><b>Entry of Sales Orders</b></font>\r\n<br><br>\r\nFrom the main menu, Orders tab, click the Order Entry link.\r\n<font size=\"+1\"><b>Selection of the Customer and Branch</b></font>\r\n<br><br>\r\nInitially, the order entry page shows the options to allow selection of a customer and branch. The customer is the actual charge account to where the order should be billed and the branch contains all the information about delivery. The customer search facilities are similar to the select customer script, but the code actually looks up on the branch code of the customer, only branches are displayed with accompanying customer charge to information. Searching for a customer can be either by entering a letter or letters in the branch code or by entering some keywords (or section of) in the customer name. Searching in this way minimises the result set returned (possibly over a dial up connection) to ensure the response is as speedy as possible to the users browser. All branches matching the criteria entered and not flagged as disabled for transactions, are returned each one with a button on the customer code. Hitting the button for the desired customer/branch selects it as the customer and branch to deliver the order to. There is opportunity later to modify the delivery details if need be. The order now shows the quick entry screen headed up with the name of the customer and branch, together with the sales type of the customer.\r\n<br><br>\r\n<font size=\"+1\"><b>Selection of Order Line Items</b></font>\r\n<br><br>\r\nThere are two ways to select the line items for the order. \r\n<br><br>\r\n<ul>\r\n<li>By default a \"quick entry\" screen shows allowing the direct entry of the inventory code and the quantity required for the order. The number of lines shown on this quick entry table is defined by a variable in config.php - $QuickEntryLines which is user modifiable. After the user has entered any number of lines into this table, hitting the Quick Entry button processes the items entered into the order. The prices are retrieved based on the sales type, the currency, customer branch and the charge to customer of the order. If there were insufficient lines to enter all part codes for the order, the same process can be repeated with the quick entry table shown below the order summary after the first quick entry has been processed.\r\n<li>On the quick entry screen there is a button to search parts. This button enables the user to search for a part based on the stock category and/or any part of the item\'s description and/or any element of the item\'s code. Hitting the search button after making appropriate entries in the code or description fields shows the part codes and descriptions together with picture of the item - if one has been uploaded to the server for all parts. Part pictures must be in .jpg format with a file name identical to the part code but with the extension .jpg, and it must reside in the directory specified in config.php for part_pics. The item code is displayed as a button and the system automatically puts one of the item selected onto the order. Additional parts can be selected in the same way.\r\n</ul>\r\nHaving selected the parts for the order, it is possible to edit the quantities, price and discount applicable to each line of the order. To get the order values to re-calculate the \"Recalulate\" button must be clicked (this is the compromise of using server side scripting - PHP - to the exclusion of any client side - java - processing). Discounts will be calculated using the Discount Matrix functionality described under that section within the order based on the quantities that are entered. \r\n<br><br>\r\nIf a line entered, displays against a red background this means that the system inventory is showing insufficient stock to cover the order line, from the inventory location as defaulted from the customer branch record - as the most appropriate inventory location to draw the stock for this branch\'s orders. The item code displayed is also a link to a new browser window showing the stock status inquiry for the item, this shows the stock available at all locations.\r\n<br><br>\r\nA line can be deleted at any time by clicking on the link at the end of the line to delete it.\r\n<br><br>\r\nThe whole order can be cancelled at any time by clicking on the \"Cancel Whole Order\" button.\r\n<br><br>\r\nThe customer can also be changed at any time.\r\n<br><br>\r\nOnce all the line items have been selected the Delivery Details button must be clicked. Note that there have been no changes to the database at all. The data entered exists only as a server side cookie - a file on the web-server. Delivery details must be confirmed before the order can be placed.\r\n<font size=\"+1\"><b>Delivery Details</b></font>\r\n<br><br>\r\nBy default the delivery details are obtained from the physical address of the branch. However, any of the information in this screen can be over-ridden. This information prints on the dispatch/packing slip and on the invoice. \r\n<br><br>\r\nThe inventory location where the stock for the order is to be drawn from is defaulted from the customer branch record. However, it is possible to select an alternative inventory location if the order is to be picked from elsewhere.\r\n<br><br>\r\nThe customer\'s order reference should be entered here and the most appropriate freight company should be selected. The system keeps track of the last freight company used to deliver to the branch and uses this as the default, if it is over-ridden then this new value is stored against the branch.\r\n<br><br>\r\nIt is possible to go back to the line item entry screen retaining all the data entered on the delivery details screen by clicking on the modify line items button. If the inventory location to draw from has been changed the colouring of the background of the line items will be updated to reflect the stock available at the new location to pick from.\r\n<br><br>\r\nIf the automatic freight calculations are being used - see the parameters in config.php, the freight cost will be calculated based on the sum of the whole order cubic metres and weight. The best shipping company will also be returned. The user can choose to charge the freight calculated or just use the cheapest freight company. The freight charge calculated can be over-ridden if required.\r\n<br><br>\r\nOnce all details are entered correctly the Place Order button should be clicked. It is important to note that abandoning the order before this button is clicked there have been no updates to the database and nothing is saved. Clicking into some other screen loses the order as entered. Whilst it is perfectly acceptable to have several browser screens open looking at different inquiries at the same time. It is not possible to have two windows entering two separate sales orders at the same time, entries in the one window will over-write the other.','S'),(42,92,'<font size=\"+1\"><b>Modfiying An Order</b></font>\r\n<br><br>\r\nOnly Outstanding sales orders can be modified. Only these orders have any balance of a quantity remaining to be delivered and/or invoiced. Order lines that have been invoiced completely will not be available for modification. New items cannot be added to the order. Pricing cannot be altered if any amount of the line has already been delivered/invoiced. Quantities of an order line cannot be reduced below the quantity of the line already invoiced.\r\n<br><br>\r\nNote that changing the delivery address of an outstanding order that has already had some of the order delivered and invoiced will affect re-prints of the initial invoice - it will show as being delivered to the order delivery address that has been modified. Hard copy of original invoices are required.\r\n<br><br>\r\n<b>Selecting an Outstanding Sales Order</b>\r\n<br><br> \r\nThere are several ways:\r\n<br><br>\r\n<ul>\r\n<li>If the item ordered is known, sales orders can be selected that have the item on by first selecting the item in the Select Item page, once the item is selected a link to show outstanding sales orders is displayed.\r\n<li>If the customer is known, then first select this customer then use the link on the customer selection menu to show only the outstanding orders for the customer selected.\r\n<li>All outstanding orders can be displayed by entering the Outstanding Sales Orders screen without a customer or item directly from the main menu under the orders tab. The outstanding Sales Orders screen also has facilities for looking up a part directly as well.\r\n</ul>\r\nThe Outstanding sales orders are shown by inventory location, the inventory location desired can be selected on this screen, by default the location defined as the default for the user is shown. The orders matching the criteria are only shown when the user clicks on the search orders button.\r\n<br><br>\r\nThe orders displayed each show links to modify the line items, invoice the order or print the dispatch note. If the order has already been printed this link will show that it has already been printed to avoid printing several dispatch notes and risk doubling up deliveries.','S'),(43,95,'<font size=\"+2\"><b>Shipments</b></font>\r\n<br><br>\r\nThe shipment costing system is meant for use in situations where there are substantial freight costs on purchased products and the business wishes to keep track of the actual cost of these items. Many smaller businesses do these calculations on spreadsheets outside the integrated system. The advantage of bringing them inside the system is that the possibility for updating standards and calculation of variances from standard at the time the costing is completed.\r\n<br><br>\r\nHaving selected a supplier the system allows outstanding purchase order items to be collated into a shipment of goods arriving altogether, the shipment is allocated a shipment number. By changing the expected time of arrival of the shipment (ETA) the delivery date of all purchase order lines on the shipment will be updated. The costs associated with landing and clearing this bundle of products can be entered against it from purchase invoice entry. Freight charges and cartage from the wharf, airfreight, unpacking, demurrage etc can be entered as costs of the shipment. The shipment costing inquiry will show these costs allocated against all of the items on the shipment, using the invoiced value of the items as the basis for apportionment. The actual shipment cost will show together with the existing standard cost and the variance.\r\n<br><br>\r\n<font size=\"+1\"><b>Shipment General Ledger Posting</b></font>\r\n<br><br>\r\nShipment charges are posted to the GRN suspense account at the time of entry of the purchase invoice. When the shipment is closed the costs of the shipment are compared against the standard cost of the shipment items taken into stock and the difference that was left in the GRN suspense account is taken to purchase variances.\r\n<br><br>\r\nIf the option to update standard costs at the time of closing the shipment is used, the difference on the stock value is taken to the stock category - stock adjustment, general ledger account.\r\n<br><br>\r\n<font size=\"+1\"><b>Creating Shipments</b></font>\r\n<br><br>\r\nAfter selecting the supplier from which the product has been sourced and having entered the purchase order(s) to initiate the transaction, from the select supplier menu, choose the link \"Set up a New Shipment\". Each time this page is called the system increments a shipment reference and shows the shipment number that will be used. The shipment page that shows allows, the details of the shipment to be entered.\r\n<br><br>\r\n<ul>\r\n<li>The name of the vessel on which the product has been loaded\r\n<li>The voyage reference - this could refer to the HAWB in the case of airfreight\r\n</ul>\r\nThe above are not required if deemed unnecessary, but serve as a useful reference for following and tracking where product is currently and monitoring ETA through the shipping company who will need these details.\r\n<br><br>\r\nIn addition to the above details the Expected Time of Arrival (ETA) is required to be entered.\r\n<br><br>\r\nIt is important to hit the \"Update Shipment Details\" button after this data is entered. As clicking one of the add links will lose this information just entered otherwise.\r\n<br><br>\r\nWhen the shipment entry screen is first displayed there is facility to select the inventory location where the purchase order has been requested to arrive. By default the user\'s default inventory location will be used, but once the inputs above are entered the location can be changed and a click on the \"Update\" button will then show all the purchase order items defined to arrive at the new location.\r\n<br><br>\r\nOnce a purchase order item has been selected to be part of the shipment the shipment entry screen will only allow other purchase order items that are also to be delivered to the same location. The location will be displayed on screen for informational purposes only.\r\n<br><br>\r\nThe location into which the shipment is to be delivered follows the purchase order. It is not possible to set up a shipment with purchase order lines into different locations.\r\n<br><br>\r\n(NB. it is possible to change the location of a purchase order that has lines on it that are allocated to a shipment that also has other lines expected to be delivered into a different location. This could result in a problem)\r\n<br><br>\r\nThe purchase order line items on outstanding orders that are yet to be invoiced and are not currently on a shipment are shown on the screen. Clicking on the link to \"Add\" will add the line to the shipment. The lines currently allocated to the shipment will show and a link to \"Delete\" the line. This allows the line to be taken off the shipment (it will not delete the purchase order line).','S'),(44,97,'<font size=\"+1\"><b>Shipment Costings</b></font>\r\n<br><br>\r\nThe costs of a shipment are recorded at the time of purchase invoice entry. Entering a purchase order from the supplier of the shipment, records the actual amount invoiced and the exchange rate at which it was invoiced against the shipment. It is important to ensure the shipment is defined before any of the item is invoiced. Once some of the item is invoiced it is not available for adding to a shipment. Nor can the item be taken off a shipment to which it was already set up against. The item must be credited before this can be done. The recording of the shipment cost happens invisibly behind the scenes for GRN items.\r\n<br><br>\r\nFreight charges, duty and cartage are recorded against the shipment at the time of the purchase invoice entry against the freight company. There is opportunity to enter invoices against a shipment and all open shipments show in a select box.\r\n<br><br>\r\nSelecting a shipment is done from the select supplier screen. The shipments defined for the supplier are shown for the users\'s default inventory location. The location can be changed to show shipments into other locations. By default only open shipments are shown but there is an option to show closed shipments as well. The vessel, voyage and ETA show for all shipments shown together with links to allow:\r\n<br><br>\r\n<ul>\r\n<li>Modification of the shipment lines or ETA or references.\r\n<li>Viewing the costing with invoiced amounts in local currency against each item together with the other general shipment charges - shown by supplier and invoice number.\r\n<li>Closing the shipment costing.\r\n</ul>','S'),(45,94,'<font size=\"+1\"><b>Closing a Shipment</b></font>\r\n<br><br>\r\nFrom the select Shipment screen, the link to close a shipment shows on all open shipments. Closing the shipment makes it impossible to post additional charges against it and also if general ledger stock integration is active, journals to take the variances from standard to the variance account and the GRN suspense are created. Closing a shipment also has an option to update the standard costs. By default this is set to yes. Again if stock GL integration is enabled (see company preferences) then the cost adjustment journals are posted in the same way as if the cost adjustment was processed separately.','S'),(46,1,'<font size=\"+2\"><b>Account Groups</b></font>\r\n<br><br>\r\nThe account group is the parent object of a general ledger account for someone who understands OO programming. Child accounts created inherit the properties of the account group - ie the account will be a profit and loss account if it belongs to an account group that is a balance sheet account, the child accounts will display in the trial balance (TB) together in the sequence determined by the account groups sequence in the trial balance (TB).\r\n<br><br>\r\nUsing a numbering system inhibits the ability to manipulate the format of the trial balance ie you have to be able to change the account code to change where an account appears ie\r\n<br><br>\r\n10100 motor expense Copenhagen\r\n<br><br>\r\n10110 motor expenses The Hague\r\n<br><br>\r\n10120 motor expense Amsterdam\r\n<br><br>\r\nwould be great but then if we wish to restructure so that Copenhagen expenses are all shown together and The Hague is now all shown together etc we will have to change the numbering. In web-erp all that is required is to change the account group. In the first situation we could have an account group for motor expenses and all these account numbers would be set up as belonging to the account group. We can decide whereabouts the account group should appear in the trial balance by changing the sequence in trial balance field. All accounts in the account group will show together. If we decided to change the trial balance to show The Hague expenses together as a separate group of costs, we could create an account group for the The Hague selling costs - or whatever, and change the motor expenses the Hague account no 10110 to be a member of that account group.\r\n<br><br>\r\nAccount groups require the sequence in the trial balance to be specified and also whether the accounts in that group will be profit and loss accounts or balance sheet accounts.\r\n<br><br>\r\nA balance sheet account is one where only the balance at the end of the period concerned is of interest. A profit and loss is one where we are interested in the movement over the period. eg. Motor expenses we are not concerned with the balance at the end of the month so much as how much was spent over the period of the profit and loss. However, for a bank account we wish to know what we have now as a balance not the movements in the account. As noted accounts created as a member of an account group will inherit the properties of the account group ie if the account group is a balance sheet group then the accounts will be interpreted as balance sheet accounts.','S'),(47,8,'<font size=\"+2\"><b>Bank Accounts</b></font>\r\n<br><br>\r\nCertain general ledger accounts can be defined as bank accounts - as many bank accounts as needed can be defined. General ledger accounts defined as bank accounts can be reconciled to bank statements using the matching facilities. Entries made to bank accounts using receipts or payments, also create a total receipt or payment, which is retained for the purposes of matching off the bank statements. Using the bank payments page, general ledger payments can be analysed to any number of other general ledger accounts, but only one entry to the bank account is made. This page also allows payments to supplier accounts to be created. Similarly, using the receipt entry page, a series of receipts from customers which may all have been banked together can be deposited as one amount to a bank account. There is only one amount appearing on the statement as the total of all these receipts, this bank account transaction is created and available for matching deposits off the bank statements.\r\n<br><br>\r\nOnce all receipts and payments are matched to bank statements, the bank reconciliation statement can be printed which should show how the current general ledger balance reconciles to the bank statement for this account.\r\n<br><br>\r\nBank accounts are defined from the setup tab from the link to Bank Accounts Maintenance. There is facility to enter the name of the account, the bank account number and the address of the bank if required, as well as selecting the general ledger account to which it refers. There are links to edit existing bank account records and to delete them. However, once defined as referring to a particular general ledger code it is not possible to change the general ledger code of the bank account. This is because there would be entries made to this account. Similarly, if bank transactions have been created using this bank account it is not possible to delete it. The bank account transactions must be purged first (but currently no facility exists to purge bank transactions).','S'),(48,70,'<font size=\"+1\"><b>Bank Account Payments</b></font>\r\n<br><br>\r\nFrom the general ledger tab, the first link under transactions is Bank Account Payments.\r\n<br><br>\r\nThe following data is required:\r\n<br><br>\r\n<ul>\r\n<li>The bank account from which the payment has been (or is to be) made. A select box allows this to be selected from the list of defined bank accounts.\r\n<li>The date on which it was paid. This is important since the accounting period in which the payment is entered is determined from the date. The system will default to today\'s date - this must be changed where bank payments are being entered retrospectively.\r\n<li>The currency which is being paid. The entries to all bank accounts are made in local currency, but payment to suppliers may be made in foreign currency being purchased in local currency at the exchange rate entered. All bank accounts are recorded in local currency (there is no facility for foreign currency bank accounts). By default the system will use the functional currency of the business as defined in the company preferences page.\r\n<li>The exchange rate - if the functional currency of the business is being used this should be 1. If another currency is being purchased with the payment then the rate at which it is being purchased should be entered.\r\n<li>Narrative - applicable to the whole payment. Narrative applicable to individual general ledger entries can be entered separately.\r\n</ul>\r\nPayments can take two forms - either it is a general ledger payment or it is a payment to a supplier. General ledger payments require an analysis of how the payment should be posted to the general ledger. General ledger accounts can be specified either as the account code directly (if the account code is known) or by selecting from the select box. Any narrative applicable to the general ledger amount can be entered too - and the amount to be posted to the selected/entered account. The total payment is taken as being the sum of all the entries made. If the total of all entries made is negative then this is entered as a negative payment - these are accepted to allow for correction of data entry errors.','S'),(49,83,'<font size=\"+1\"><b>Sales Journals</b></font>\r\n<br><br>\r\nThe general ledger accounts that are used in this level of integration are determined from several inputs. \r\n<br><br>\r\n<ul>\r\n<li>Sales Area of the customer being invoiced/credited\r\n<li>Sales Type (or price list) of the customer being invoiced/credited.\r\n<li>Stock Category of the item being invoiced/credited.\r\n</ul>\r\nA table of sales general ledger accounts is maintained and can be modified from the setup tab. When an invoice is created from the ConfirmDispatch_Invoice.php script the system uses a function defined in GetSalesTransGLCode.inc to look up the general ledger codes that are appropriate. By default this function uses the following logic:\r\n<br><br>\r\n<ul>\r\n<li>If there is a record in the SalesGLPostings table that has a matching Area, SalesType and Stock Category then the function returns the sales account and the discount account applicable.\r\n<li>If there is a match for the Area and SalesType using the default Stock Category (ANY) then the codes applicable to this record are returned.\r\n<li>Then if there is a matching Sales type, stock category with default (AN) area this is used.\r\n<li>Then if there is a matching stock category record using the default area (AN) and the default salestype (AN) this is used - finally\r\n<li>If there is no record is found after trying the above combinations then the GL Code for the default area, sales type and default stock category is used - this is GL code 1. If GL Code 1 is not defined, then it will be created.\r\n</ul>\r\nSince the logic of how the general ledger account is determined is defined in this function it is relatively simple to change this to what is most appropriate for the business.\r\n<br><br>\r\nThe freight recovery and the debtors control account used are those defined in the company preferences page.\r\n<br><br>\r\nThe tax account used is the account defined in the tax authorities definition used for the customer being invoiced.','S'),(50,11,'<font size=\"+1\"><b>Stock Journals</b></font>\r\n<br><br>\r\nThe general ledger accounts that are used for posting sales transactions are determined using the sales area, the sales type of the customer being invoiced/credited and the stock category of the item being invoiced/credited. A table of general ledger accounts is maintained and can be modified from the set up tab from the link \"COGS GL Interface Postings\". The same logic as above is applied and the function is defined in the same GetSalesTransGLCode.inc script to look up the appropriate GL codes. Again, since the logic of how the general ledger account is determined is defined in the function GetCOGSGLAccount, it is relatively simple to change this to suit the business.\r\n<br><br>\r\nThe account to credit stock with for the cost of goods sold is determined by reference to the stock item being sold. The stock category of the item is retrieved and the general ledger codes applicable to the stock category are used.\r\n<br><br>\r\nThe profit and loss accounts used for stock adjustments are also determined by reference to the stock category record.\r\n<br><br>\r\nThe profit and loss account used for posting the variance between standard cost of a purchased item and its actual cost as invoiced is also determined from the stock category record.','S'),(51,19,'<font size=\"+1\"><b>EDI Setup</b></font>\r\n<br><br>\r\nTo enable EDI transactions for a customer, first select the customer from the Select Customer link on any page, then click the link - Customer EDI Configuration. This page allows selection of the type of transactions that are to transmitted electronically currently only invoices/credit notes and orders are available. Each must be specifically enabled to activate them. Each customer must have their:\r\n<br><br>\r\n<ul>\r\n<li>EDI reference that they are identified by\r\n<li>Transport mechanism and address to which the invoice/credit note messages are to be sent - either email as a file attachment or via ftp (file transfer protocol)\r\n</ul>\r\nIf the transport mechanism is to be ftp - this must be compiled into PHP with the flag -enable-ftp, most windows PHP installtions have this by default now. Additional fields for the ftp server username and password will also be required.\r\n<br><br>\r\nTo activate EDI polling for invoices to send the script EDISendInvoices.php must be run as a scheduled job - using cron or some other scheduling system -see automating sales reports. It can also be run from the utilites menu Z_index.php with debugging output.\r\n<br><br>\r\nTo activate EDI polling for orders to be entered as received the script ???? must be run as a scheduled job using cron or some other scheduling system.\r\n<br><br>\r\n<font size=\"+1\"><b>Sending EDI Invoices</b></font>\r\n<br><br>\r\nEDI messages are made up of segments which must appear in a certain order. Since customers will require EDI invoices in slightly different formats, the exact format can be defined in the table EDIMessageFormat. This table has a record for each customer invoice line and the sequence when it must appear in the message. The field line text in this table can include any of the predefined EDI invoice variables surrounded by \"[\" and \"]\" to denote them as a variable to be replaced with the appropriate value as follows:\r\n<br><br>\r\n<table>\r\n<th colspan=\"2\">EDI Invoice Detail Section</th>\r\n<tr>\r\n<td>\r\nEDITransNo\r\n</td>\r\n<td>\r\nThe unique EDI transaction number\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nInvOrCrd\r\n</td>\r\n<td>\r\nWhether the transaction is an invoice or a credit - the value of this variable is an EANCOM defined number, 388 for a tax invoice and 381 for a credit note\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nTransNo\r\n</td>\r\n<td>\r\nThe transaction number of invoice or credit \r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nOrigOrDup\r\n</td>\r\n<td>\r\nWhether the transaction is a duplicate or original sending the value of this variable is an EANCOM defined number 7 for a duplicate and 9 for an original\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nTranDate\r\n</td>\r\n<td>\r\nThe transaction date in the format CCYYMMDD\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nOrderNo\r\n</td>\r\n<td>\r\nThe original order number - only for invoices\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nCustBranchCode\r\n</td>\r\n<td>\r\nThe customer\'s internal code for the branch\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nCompanyEDIReference\r\n</td>\r\n<td>\r\nThe customer\'s EDI reference code\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nBranchName\r\n</td>\r\n<td>\r\nThe branch name\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nBranchStreet\r\n</td>\r\n<td>\r\n&nbsp;\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nBranchCity\r\n</td>\r\n<td>\r\n&nbsp;\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nBranchState\r\n</td>\r\n<td>\r\n&nbsp;\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nTaxAuthorityRef\r\n</td>\r\n<td>\r\nThe businesses Tax Authority reference number\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nDatePaymentDue\r\n</td>\r\n<td>\r\nThe due date for this transaction\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nTaxTotal\r\n</td>\r\n<td>\r\nThe total amount of tax on the transaction\r\n</td>\r\n</tr>\r\n</table>\r\n<table>\r\n<th>EDI Invoice Detail Section - for the lines on the transaction</th>\r\n<tr>\r\n<td>\r\nLineNumber\r\n</td>\r\n<td>\r\n&nbsp;\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nStockID\r\n</td>\r\n<td>\r\nThe webERP item code \r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nCustStockID\r\n</td>\r\n<td>\r\nThe customer\'s internal code for the item\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nItemDescription\r\n</td>\r\n<td>\r\n&nbsp;\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nQtyInvoiced\r\n</td>\r\n<td>\r\nQuantity invoiced or credited\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nLineTotalExclTax\r\n</td>\r\n<td>\r\nThe total for the line excluding tax\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nUnitPrice\r\n</td>\r\n<td>\r\nUnit price for the item\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nLineTaxAmount\r\n</td>\r\n<td>\r\nThe tax applicable to the line\r\n</td>\r\n</tr>\r\n</table>\r\n<table>\r\n<th>EDI Invoice Summary Section</th>\r\n<tr>\r\n<td>\r\nNoLines\r\n</td>\r\n<td>\r\nThe total number of lines on the invoice/credit\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nTotalAmountExclTax\r\n</td>\r\n<td>\r\nTotal amount of the transaction excluding tax\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nTotalAmountInclTax\r\n</td>\r\n<td>\r\nTotal amount of the transaction including tax\r\n</td>\r\n</tr>\r\n<tr>\r\n<td>\r\nNoSegments\r\n</td>\r\n<td>\r\nThe total number of segments in the transaction this is required as a control check in the summary\r\n</td>\r\n</tr>\r\n</table>\r\nThere is therefore great flexibility in how the messages are defined. The variables for the summary and heading sections can be used in any section. The detail section variables can only be used in the detail section.\r\n<br><br>\r\nMost customers will require that the branch to which the invoiced goods are delivered to, be identified using the customer\'s coding system. It is therefore important to ensure that the customer\'s branch code is actually entered against the webERP branch record. The variable CustBranchCode is retrieved from the branch record and if it is not entered then the EDI transaction will fail.\r\n<br><br>\r\nSome customers may also require the item code to be their item code, not the webERP item code. The variable CustStockID is derived from the cross reference table EDIItemMapping which would need to contain a cross reference record for each item that they may buy.\r\n<br><br>\r\nThe script that creates the EDI invoices (EDISendInvoices.php) should be run automatically in the background as a scheduled task. It first gets a list of all customers who should receive EDI invoices (or credit notes) - as determined in the settings of their DebtorsMaster record. Then the script goes through each customer returned in turn to get any invoices or credits that have not already been sent. A flat file is created for all the customers invoices and credits and sent to the customer using the transport, address and other parameters defined in the customer edi setup page - recorded against their DebtorsMaster record. There is a link to enable the script to be run manually - the browser will also show the output of the EDI message.','S');
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
INSERT INTO LocStock VALUES ('DEN','123',0.0,0),('DEN','125',92.0,0),('DEN','127',0.0,0),('DEN','130',0.0,0),('DEN','140',0.0,0),('DEN','HON90021',13.0,12),('DEN','HON90022',-5.0,0),('DEN','HON92FrSus',0.0,0),('DEN','HONCIVIC92SET',0.0,0),('DEN','HONShock',0.0,0),('MEL','123',0.0,0),('MEL','125',1180.0,0),('MEL','127',0.0,0),('MEL','130',0.0,0),('MEL','140',0.0,0),('MEL','HON90021',483.0,12),('MEL','HON90022',0.0,0),('MEL','HON92FrSus',0.0,0),('MEL','HONCIVIC92SET',0.0,0),('MEL','HONShock',0.0,0),('MUC','123',0.0,0),('MUC','125',39.0,0),('MUC','127',0.0,0),('MUC','130',0.0,0),('MUC','140',0.0,0),('MUC','HON90021',0.0,1),('MUC','HON90022',0.0,0),('MUC','HON92FrSus',0.0,0),('MUC','HONCIVIC92SET',0.0,0),('MUC','HONShock',0.0,0);
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
INSERT INTO Prices VALUES ('123','EX','AUD','','9.6500','\'\''),('123','EX','USD','','2.9500','\'\''),('123','WS','AUD','','12.3500','\'\''),('123','WS','USD','','3.2000','\'\''),('HON90021','EX','AUD','','25.0000','\'\''),('HON90021','EX','USD','','12.6500','\'\''),('HON90021','WS','USD','','15.3500','\'\''),('HON90021','WS','USD','JOHNSON','12.8900','\'\''),('HON90022','EX','AUD','','24.5000','\'\''),('HON90022','EX','USD','','13.8700','\'\''),('HON90022','WS','USD','','15.8600','\'\''),('HON92FrSus','EX','USD','','45.9500','\'\''),('HON92FrSus','WS','USD','','53.2000','\'\''),('HONShock','EX','USD','','24.8000','\'\''),('HONShock','WS','USD','','25.9000','\'\'');
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
INSERT INTO PurchOrderDetails VALUES (27,14,'125','2004-07-14','12.5mm round steel',720000,0.0000,0.9500,0.0000,0.6200,1200.0000,1170.0000,0,'',0),(28,14,'HON90021','2004-07-14','Honda Civic front coil spring 1990-94',722000,0.0000,3.5000,0.0000,9.6900,500.0000,512.0000,0,'',1),(29,15,'123','2004-08-31','12.3mm Round Steel coil',720000,0.0000,0.3500,0.0000,0.0000,250.0000,0.0000,3,'0',0),(30,15,'125','2004-08-31','12.5mm round steel',720000,0.0000,0.3200,0.0000,0.6200,120.0000,95.0000,3,'0',0);
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
INSERT INTO ReportColumns VALUES (2,1,'Value','',0,51,60,'Net Value',0,0,'',1,'N',0);
UNLOCK TABLES;
/*!40000 ALTER TABLE ReportColumns ENABLE KEYS */;

--
-- Dumping data for table `ReportHeaders`
--


/*!40000 ALTER TABLE ReportHeaders DISABLE KEYS */;
LOCK TABLES ReportHeaders WRITE;
INSERT INTO ReportHeaders VALUES (2,'Sales in 2004','Customer Code',0,'0','zzzzzzz','Not Used',0,'','','Not Used',0,'','','Not Used',0,'','');
UNLOCK TABLES;
/*!40000 ALTER TABLE ReportHeaders ENABLE KEYS */;

--
-- Dumping data for table `SalesAnalysis`
--


/*!40000 ALTER TABLE SalesAnalysis DISABLE KEYS */;
LOCK TABLES SalesAnalysis WRITE;
INSERT INTO SalesAnalysis VALUES ('EX',55,7.3529,6.2000,'WALMON','WALM',10.0000,0.1838,'125','AR',1,'25','Steel',41),('EX',55,323.5295,213.1800,'WALMON','WALM',22.0000,-1.1029,'HON90021','AR',1,'25','Coil',42),('WS',55,-23.3000,-6.2000,'JOHNSON','JOHNTEMPE',-10.0000,0.0000,'125','AR',1,'2','Steel',43),('WS',55,-341.2500,-203.4900,'JOHNSON','JOHNTEMPE',-21.0000,0.0000,'HON90021','AR',1,'2','Coil',44),('WS',55,-88.1250,-18.6000,'BILLYBUNT','BILLYBUNT',-30.0000,0.0000,'125','UK',1,'25','Steel',45),('WS',55,79.3000,10.5000,'WATTSGRP','WATTLA',5.0000,0.0000,'HON90022','CA',1,'25','Coil',46),('EX',56,6.5294,3.1000,'WALMON','WALM',5.0000,0.0000,'125','AR',1,'25','Steel',47),('EX',56,176.4706,116.2800,'WALMON','WALM',12.0000,0.0000,'HON90021','AR',1,'25','Coil',48),('EX',56,-189.7500,-145.3500,'GRANHR','GRAN',-15.0000,-47.4375,'HON90021','UK',1,'25','Coil',49),('EX',56,-4.8529,-15.5000,'WALMON','WALMOADEL',-25.0000,-0.2426,'125','AR',1,'25','Steel',50),('EX',57,0.0000,0.0000,'WALMON','WALM',0.0000,0.0000,'HON90021','AR',1,'25','Coil',51),('EX',57,-0.0001,0.0000,'WALMON','WALM',0.0000,0.0000,'HON90022','AR',1,'25','Coil',52),('EX',57,37.9500,29.0700,'GRANHR','GRAN',3.0000,0.0000,'HON90021','UK',1,'25','Coil',53),('WS',57,3.9900,1.8600,'WATTSGRP','WATTLA',3.0000,0.0000,'125','CA',1,'25','Steel',54);
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
INSERT INTO SalesOrderDetails VALUES (27,'125',10.0000,1.2500,10.0000,0,0.0250,'2004-07-14 00:00:00',1,''),(27,'HON90021',25.0000,25.0000,25.0000,0,0.0000,'2004-07-14 00:00:00',1,''),(28,'125',3.0000,1.3300,3.0000,0,0.0000,'2004-09-21 00:00:00',1,''),(28,'HON90022',5.0000,15.8600,5.0000,0,0.0000,'2004-07-30 00:00:00',1,''),(29,'125',5.0000,2.2200,5.0000,0,0.0000,'2004-08-20 16:00:00',1,'Testing one two three'),(29,'HON90021',12.0000,25.0000,12.0000,0,0.0000,'2004-08-20 16:00:00',1,''),(30,'HON90021',2.0000,25.0000,2.0000,0,0.0000,'2004-09-21 00:00:00',1,''),(30,'HON90022',2.0000,24.5000,2.0000,0,0.0000,'2004-09-21 00:00:00',1,''),(31,'HON90021',3.0000,12.6500,25.0000,0,0.0000,'2004-09-21 00:00:00',0,'');
UNLOCK TABLES;
/*!40000 ALTER TABLE SalesOrderDetails ENABLE KEYS */;

--
-- Dumping data for table `SalesOrders`
--


/*!40000 ALTER TABLE SalesOrders DISABLE KEYS */;
LOCK TABLES SalesOrders WRITE;
INSERT INTO SalesOrders VALUES (27,'WALMON','WALM','',NULL,' Inv 1','2004-07-13','EX',1,'309 Centennial Highway','Ballmain, Sydney','New South Wales','Australia','+61 2 3546 8875','','Wallace Monterey - Sydney',0.00,'MEL','2004-07-13',0,'0000-00-00'),(28,'WATTSGRP','WATTLA','',NULL,' Inv 2 Inv 6','2004-07-29','WS',1,'922 Acton Lane','Reefston','Los Angeles','California','235 446 998','','Watts Group Los Angeles',0.00,'DEN','2004-07-30',1,'2004-07-29'),(29,'WALMON','WALM','',NULL,' Inv 3','2004-08-15','EX',1,'309 Centennial Highway','Ballmain, Sydney','New South Wales','Australia','+61 2 3546 8875','','Wallace Monterey - Sydney',0.00,'MEL','2004-08-20',0,'0000-00-00'),(30,'WALMON','WALM','',NULL,' Inv 4','2004-09-20','EX',1,'309 Centennial Highway','Ballmain, Sydney','New South Wales','Australia','+61 2 3546 8875','','Wallace Monterey - Sydney',0.00,'MEL','2004-09-21',0,'0000-00-00'),(31,'GRANHR','GRAN','',NULL,' Inv 5 Inv 7','2004-09-20','EX',1,'2 Growler Terraces','West Hemington','BE22 FD2','England','','','Gramtham Horton PLC',0.00,'MUC','2004-09-21',0,'0000-00-00');
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
INSERT INTO Salesman VALUES ('2','Anthony Boland','+61 544 3540','+61 544 3512',0.2000,'0.0000',0.0000),('25','Maggie Scratcher','','',0.0000,'0.0000',0.0000);
UNLOCK TABLES;
/*!40000 ALTER TABLE Salesman ENABLE KEYS */;

--
-- Dumping data for table `Scripts`
--


/*!40000 ALTER TABLE Scripts DISABLE KEYS */;
LOCK TABLES Scripts WRITE;
INSERT INTO Scripts VALUES (1,'AccountGroups.php','Defines the groupings of general ledger accounts'),(2,'AgedDebtors.php','Lists customer account balances in detail or summary in selected currency'),(3,'AgedSuppliers.php','Lists supplier account balances in detail or summary in selected currency'),(4,'Areas.php','Defines the sales areas - all customers must belong to a sales area for the purposes of sales analysis'),(5,'BOMInquiry.php','Displays the bill of material with cost information'),(6,'BOMListing.php','Lists the bills of material for a selected range of items'),(7,'BOMs.php','Administers the bills of material for a selected item'),(8,'BankAccounts.php','Defines the general ledger code for bank accounts and specifies that bank transactions be created for these accounts for the purposes of reconciliation'),(9,'BankMatching.php','Allows payments and receipts to be matched off against bank statements'),(10,'BankReconciliation.php','Displays the bank reconciliation for a selected bank account'),(11,'COGSGLPostings.php','Defines the general ledger account to be used for cost of sales entries'),(12,'CompanyPreferences.php','Defines the settings applicable for the company, including name, address, tax authority reference, whether GL integration used etc.'),(13,'ConfirmDispatchControlled_Invoice.php','Specifies the batch references/serial numbers of items dispatched that are being invoiced'),(14,'ConfirmDispatch_Invoice.php','Creates sales invoices from entered sales orders based on the quantities dispatched that can be modified'),(15,'CreditItemsControlled.php','Specifies the batch references/serial numbers of items being credited back into stock'),(16,'CreditStatus.php','Defines the credit status records. Each customer account is given a credit status from this table. Some credit status records can prohibit invoicing and new orders being entered.'),(17,'Credit_Invoice.php','Creates a credit note based on the details of an existing invoice'),(18,'Currencies.php','Defines the currencies available. Each customer and supplier must be defined as transacting in one of the currencies defined here.'),(19,'CustEDISetup.php','Allows the set up the customer specified EDI parameters for server, email or ftp.'),(20,'CustWhereAlloc.php','Shows to which invoices a receipt was allocated to'),(21,'CustomerAllocations.php','Allows customer receipts and credit notes to be allocated to sales invoices'),(22,'CustomerBranches.php','Defines the details of customer branches such as delivery address and contact details - also sales area, representative etc'),(23,'CustomerInquiry.php','Shows the customers account transactions with balances outstanding, links available to drill down to invoice/credit note or email invoices/credit notes'),(24,'CustomerReceipt.php','Entry of both customer receipts against accounts receivable and also general ledger or nominal receipts'),(25,'CustomerTransInquiry.php','Lists in html the sequence of customer transactions, invoices, credit notes or receipts by a user entered date range'),(26,'Customers.php','Defines the setup of a customer account, including payment terms, billing address, credit status, currency etc'),(27,'DeliveryDetails.php','Used during order entry to allow the entry of delivery addresses other than the defaulted branch delivery address and information about carrier/shipping method etc'),(28,'DiscountCategories.php','Defines the items belonging to a discount category. Discount Categories are used to allow discounts based on quantities across a range of producs'),(29,'DiscountMatrix.php','Defines the rates of discount applicable to discount categories and the customer groupings to which the rates are to apply'),(30,'EDIMessageFormat.php','Specifies the EDI message format used by a customer - administrator use only.'),(31,'EDIProcessOrders.php','Processes incoming EDI orders into sales orders'),(32,'EDISendInvoices.php','Processes invoiced EDI customer invoices into EDI messages and sends using the customers preferred method either ftp or email attachments.'),(33,'EmailCustTrans.php','Emails selected invoice or credit to the customer'),(34,'FTP_RadioBeacon.php','FTPs sales orders for dispatch to a radio beacon software enabled warehouse dispatching facility'),(35,'FreightCosts.php','Defines the setup of the freight cost using different shipping methods to different destinations. The system can use this information to calculate applicable freight if the items are defined with the correct kgs and cubic volume'),(36,'GLAccountInquiry.php','Shows the general ledger transactions for a specified account over a specified range of periods'),(37,'GLAccounts.php','Defines the general ledger accounts'),(38,'GLBalanceSheet.php','Shows the balance sheet for the company as at a specified date'),(39,'GLCodesInquiry.php','Shows the list of general ledger codes defined with account names and groupings'),(40,'GLJournal.php','Entry of general ledger journals, periods are calculated based on the date entered here'),(41,'GLProfit_Loss.php','Shows the profit and loss of the company for the range of periods entered'),(42,'GLTransInquiry.php','Shows the general ledger journal created for the sub ledger transaction specified'),(43,'GLTrialBalance.php','Shows the trial balance for the month and the for the period selected together with the budgeted trial balances'),(44,'GoodsReceived.php','Entry of items received against purchase orders'),(45,'GoodsReceivedControlled.php','Entry of the serial numbers or batch references for controlled items received against purchase orders'),(46,'InventoryPlanning.php','Creates a pdf report showing the last 4 months use of items including as a component of assemblies together with stock quantity on hand, current demand for the item and current quantity on sales order.'),(47,'InventoryValuation.php','Creates a pdf report showing the value of stock at standard cost for a range of product categories selected'),(48,'Locations.php','Defines the inventory stocking locations or warehouses'),(49,'Logout.php','Shows when the user logs out of webERP'),(50,'MailInventoryValuation.php','Meant to be run as a scheduled process to email the stock valuation off to a specified person. Creates the same stock valuation report as InventoryValuation.php'),(51,'MailSalesReport.php','Creates a sales analysis pdf report and emails it to the defined receipients. This script is meant to be run as a scheduled process for daily or weekly sales reporting'),(52,'MailSalesReport_csv.php','Creates a sales analysis report as a comma separated values (csv) file and emails it to the defined receipients. This script is meant to be run as a scheduled process for daily or weekly sales reporting'),(53,'OrderDetails.php','Shows the detail of a sales order'),(54,'OutstandingGRNs.php','Creates a pdf showing all GRNs for which there has been no purchase invoice matched off against.'),(55,'PDFBankingSummary.php','Creates a pdf showing the amounts entered as receipts on a specified date together with references for the purposes of banking'),(56,'PDFChequeListing.php','Creates a pdf showing all payments that have been made from a specified bank account over a specified period. This can be emailed to an email account defined in config.php - ie a financial controller'),(57,'PDFDeliveryDifferences.php','Creates a pdf report listing the delivery differences from what the customer requested as recorded in the order entry. The report calculates a percentage of order fill based on the number of orders filled in full on time'),(58,'PDFLowGP.php','Creates a pdf report showing the low gross profit sales made in the selected date range. The percentage of gp deemed acceptable can also be entered'),(59,'PDFPriceList.php','Creates a pdf of the price list applicable to a given sales type and customer. Also allows the listing of prices specific to a customer'),(60,'PDFStockCheckComparison.php','Creates a pdf comparing the quantites entered as counted at a given range of locations against the quantity stored as on hand as at the time a stock check was initiated.'),(61,'PDFStockLocTransfer.php','Creates a stock location transfer docket for the selected location transfer reference number'),(62,'PO_Chk_ShiptRef_JobRef.php','Checks the Shipment of JobReference number is correct during AP invoice entry'),(63,'PO_Header.php','Entry of a purchase order header record - date, references buyer etc'),(64,'PO_Items.php','Entry of a purchase order items - allows entry of items with lookup of currency cost from Purchasing Data previously entered also allows entry of nominal items against a general ledger code if the AP is integrated to the GL'),(65,'PO_OrderDetails.php','Purchase order inquiry shows the quantity received and invoiced of purchase order items as well as the header information'),(66,'PO_PDFPurchOrder.php','Creates a pdf of the selected purchase order for printing or email to one of the supplier contacts entered'),(67,'PO_SelectOSPurchOrder.php','Shows the outstanding purchase orders for selecting with links to receive or modify the purchase order header and items'),(68,'PO_SelectPurchOrder.php','Allows selection of any purchase order with links to the inquiry'),(69,'PaymentTerms.php','Defines the payment terms records, these can be expressed as either a number of days credit or a day in the following month. All customers and suppliers must have a corresponding payment term recorded against their account'),(70,'Payments.php','Entry of bank account payments either against an AP account or a general ledger payment - if the AP-GL link in company preferences is set'),(71,'PeriodsInquiry.php','Shows a list of all the system defined periods'),(72,'Prices.php','Entry of prices for a selected item also allows selection of sales type and currency for the price'),(73,'Prices_Customer.php','Entry of prices for a selected item and selected customer/branch. The currency and sales type is defaulted from the customer\'s record'),(74,'PrintCustOrder.php','Creates a pdf of the dispatch note - by default this is expected to be on two part pre-printed stationery to allow pickers to note discrepancies for the confirmer to update the dispatch at the time of invoicing'),(75,'PrintCustOrder_generic.php','Creates two copies of a laser printed dispatch note - both copies need to be written on by the pickers with any discrepancies to advise customer of any shortfall and on the office copy to ensure the correct quantites are invoiced'),(76,'PrintCustStatements.php','Creates a pdf for the customer statements in the selected range'),(77,'PrintCustTrans.php','Creates either a html invoice or credit note or a pdf. A range of invoices or credit notes can be selected also.'),(78,'PurchData.php','Entry of supplier purchasing data, the suppliers part reference and the suppliers currency cost of the item'),(79,'ReverseGRN.php','Reverses the entry of goods received - creating stock movements back out and necessary general ledger journals to effect the reversal'),(80,'SalesAnalReptCols.php','Entry of the definition of a sales analysis report\'s columns.'),(81,'SalesAnalRepts.php','Entry of the definition of a sales analysis report headers'),(82,'SalesAnalysis_UserDefined.php','Creates a pdf of a selected user defined sales analysis report'),(83,'SalesGLPostings.php','Defines the general ledger accounts used to post sales to based on product categories and sales areas'),(84,'SalesPeople.php','Defines the sales people of the business'),(85,'SalesTypes.php','Defines the sales types - prices are held against sales types they can be considered price lists. Sales analysis records are held by sales type too.'),(86,'SelectCompletedOrder.php','Allows the selection of completed sales orders for inquiries - choices to select by item code or customer'),(87,'SelectCreditItems.php','Entry of credit notes from scratch, selecting the items in either quick entry mode or searching for them manually'),(88,'SelectCustomer.php','Selection of customer - from where all customer related maintenance, transactions and inquiries start'),(89,'SelectGLAccount.php','Selection of general ledger account from where all general ledger account maintenance, or inquiries are initiated'),(90,'SelectOrderItems.php','Entry of sales order items with both quick entry and part search functions'),(91,'SelectProduct.php','Selection of items. All item maintenance, transactions and inquiries start with this script'),(92,'SelectSalesOrder.php','Selects a sales order irrespective of completed or not for inquiries'),(93,'SelectSupplier.php','Selects a supplier. A supplier is required to be selected before any AP transactions and before any maintenance or inquiry of the supplier'),(94,'ShipmentCosting.php','Shows the costing of a shipment with all the items invoice values and any shipment costs apportioned. Updating the shipment has an option to update standard costs of all items on the shipment and create any general ledger variance journals'),(95,'Shipments.php','Entry of shipments from outstanding purchase orders for a selected supplier - changes in the delivery date will cascade into the different purchase orders on the shipment'),(96,'Shippers.php','Defines the shipping methods available. Each customer branch has a default shipping method associated with it which must match a record from this table'),(97,'Shipt_Select.php','Selection of a shipment for displaying and modification or updating'),(98,'ShiptsList.php','Shows a list of all the open shipments for a selected supplier. Linked from POItems.php'),(99,'SpecialOrder.php','Allows for a sales order to be created and an indent order to be created on a supplier for a one off item that may never be purchased again. A dummy part is created based on the description and cost details given.'),(100,'StockAdjustments.php','Entry of quantity corrections to stocks in a selected location.'),(101,'StockAdjustmentsControlled.php','Entry of batch references or serial numbers on controlled stock items being adjusted'),(102,'StockCategories.php','Defines the stock categories. All items must refer to one of these categories. The category record also allows the specification of the general ledger codes where stock items are to be posted - the balance sheet account and the profit and loss effect of any adjustments and the profit and loss effect of any price variances'),(103,'StockCheck.php','Allows creation of a stock check file - copying the current quantites in stock for later comparison to the entered counts. Also produces a pdf for the count sheets.'),(104,'StockCostUpdate.php','Allows update of the standard cost of items producing general ledger journals if the company preferences stock GL interface is active'),(105,'StockCounts.php','Allows entry of stock counts'),(106,'StockLocMovements.php','Inquiry shows the Movements of all stock items for a specified location'),(107,'StockLocQties_csv.php','Makes a comma separated values (CSV)file of the stock item codes and quantities'),(108,'StockLocStatus.php','Shows the stock on hand together with outstanding sales orders and outstanding purchase orders by stock location for all items in the selected stock category'),(109,'StockLocTransfer.php','Entry of a bulk stock location transfer for many parts from one location to another.'),(110,'StockLocTransferReceive.php','Effects the transfer and creates the stock movements for a bulk stock location transfer initiated from StockLocTransfer.php'),(111,'StockMovements.php','Shows a list of all the stock movements for a selected item and stock location including the price at which they were sold in local currency and the price at which they were purchased for in local currency'),(112,'StockQties_csv.php','Makes a comma separated values (CSV)file of the stock item codes and quantities'),(113,'StockReorderLevel.php','Entry and review of the re-order level of items by stocking location'),(114,'StockSerialItems.php','Shows a list of the serial numbers or the batch references and quantities of controlled items. This inquiry is linked from the stock status inquiry'),(115,'StockStatus.php','Shows the stock on hand together with outstanding sales orders and outstanding purchase orders by stock location for a selected part. Has a link to show the serial numbers in stock at the location selected if the item is controlled'),(116,'StockTransferControlled.php','Entry of serial numbers/batch references for controlled items being received on a stock transfer. The script is used by both bulk transfers and point to point transfers'),(117,'StockTransfers.php','Entry of point to point stock location transfers of a single part'),(118,'StockUsage.php','Inquiry showing the quantity of stock used by period calculated from the sum of the stock movements over that period - by item and stock location. Also available over all locations'),(119,'Stocks.php','Defines an item - maintenance and addition of new parts'),(120,'SuppCreditGRNs.php','Entry of a supplier credit notes (debit notes) against existing GRN which have already been matched in full or in part'),(121,'SuppInvGRNs.php','Entry of supplier invoices against goods received'),(122,'SuppPaymentRun.php','Automatic creation of payment records based on calculated amounts due from AP invoices entered'),(123,'SuppShiptChgs.php','Entry of supplier invoices against shipments as charges against a shipment'),(124,'SuppTransGLAnalysis.php','Entry of supplier invoices against general ledger codes'),(125,'SupplierAllocations.php','Entry of allocations of supplier payments and credit notes to invoices'),(126,'SupplierContacts.php','Entry of supplier contacts and contact details including email addresses'),(127,'SupplierCredit.php','Entry of supplier credit notes (debit notes)'),(128,'SupplierInquiry.php','Inquiry showing invoices, credit notes and payments made to suppliers together with the amounts outstanding'),(129,'SupplierInvoice.php','Entry of supplier invoices'),(130,'Suppliers.php','Entry of new suppliers and maintenance of existing suppliers'),(131,'TaxAuthorities.php','Entry of tax authorities - the state intitutions that charge tax'),(132,'TaxAuthorityRates.php','Entry of the rates of tax applicable to the tax authority depending on the item tax level'),(133,'WWW_Users.php','Entry of users and security settings of users'),(134,'WhereUsedInquiry.php','Inquiry showing where an item is used ie all the parents where the item is a component of'),(135,'WorkCentres.php','Defines the various centres of work within a manufacturing company. Also the overhead and labour rates applicable to the work centre and its standard capacity'),(136,'WorkOrderEntry.php','Entry of new work orders'),(137,'WorkOrderIssue.php','Issue of materials to a work order'),(138,'Z_ChangeBranchCode.php','Utility to change the branch code of a customer that cascades the change through all the necessary tables'),(139,'Z_ChangeCustomerCode.php','Utility to change a customer code that cascades the change through all the necessary tables'),(140,'Z_ChangeStockCode.php','Utility to change an item code that cascades the change through all the necessary tables'),(141,'Z_CheckAllocationsFrom.php',''),(142,'Z_CheckAllocs.php',''),(143,'Z_CheckDebtorsControl.php','Inquiry that shows the total local currency (functional currency) balance of all customer accounts to reconcile with the general ledger debtors account'),(144,'Z_CreateChartDetails.php','Utility page to create chart detail records for all general ledger accounts and periods created - needs expert assistance in use'),(145,'Z_CreateCompany.php','Utility to insert company number 1 if not already there - actually only company 1 is used - the system is not multi-company'),(146,'Z_CurrencyDebtorsBalances.php','Inquiry that shows the total foreign currency together with the total local currency (functional currency) balances of all customer accounts to reconcile with the general ledger debtors account'),(147,'Z_CurrencySuppliersBalances.php','Inquiry that shows the total foreign currency amounts and also the local currency (functional currency) balances of all supplier accounts to reconcile with the general ledger creditors account'),(148,'Z_DeleteCreditNote.php','Utility to reverse a customer credit note - a desperate measure that should not be used except in extreme circumstances'),(149,'Z_DeleteInvoice.php','Utility to reverse a customer invoice - a desperate measure that should not be used except in extreme circumstances'),(150,'Z_DeleteSalesTransActions.php','Utility to delete all sales transactions, sales analysis the lot! Extreme care required!!!'),(151,'Z_MakeStockLocns.php','Utility to make LocStock records for all items and locations if not already set up.'),(152,'Z_PriceChanges.php','Utility to make bulk pricing alterations to selected sales type price lists or selected customer prices only'),(153,'Z_ReApplyCostToSA.php','Utility to allow the sales analysis table to be updated with the latest cost information - the sales analysis takes the cost at the time the sale was made to reconcile with the enteries made in the gl.'),(154,'Z_RePostGLFromPeriod.php','Utility to repost all general ledger transaction commencing from a specified period. This can take some time in busy environments. Normally GL transactions are posted automatically each time a trial balance or profit and loss account is run'),(155,'Z_ReverseSuppPaymentRun.php','Utility to reverse an entire Supplier payment run'),(156,'Z_UpdateChartDetailsBFwd.php','Utility to recalculate the ChartDetails table B/Fwd balances - extreme care!!'),(157,'Z_UploadForm.php','Utility to upload a file to a remote server'),(158,'Z_UploadResult.php','Utility to upload a file to a remote server'),(159,'Z_index.php','Utility menu page'),(160,'index.php','The main menu from where all functions available to the user are accessed by clicking on the links'),(161,'phpinfo.php','Details about PHP installation on the server'),(162,'UserSettings.php','Allows the user to change system wide defaults for the theme - appearance, the number of records to show in searches and the language to display messages in'),(163,'StockQuantityByDate.php','Shows the stock on hand for each item at a selected location and stock category as at a specified date');
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
INSERT INTO StockMaster VALUES ('123','Steel','12.3mm Round Steel coil','X4k grade 12.3mm round steel in coils','Kgs','B','1800-01-01','0.0000','0.0000','0.3000','0.0000','0.0000',0,0,0,14000.00,'0.0200','1.0000','','',1,0,0),('125','Steel','12.5mm round steel','12.5mm diameter round steel X4K grade	','each','B','1800-01-01','0.0000','0.0000','0.6200','0.0000','0.0000',0,0,1,1200.00,'0.0210','1.0000','','',1,0,0),('127','Steel','X3K 12.7mm spring steel','X3K 12.7mm spring steel','each','B','1800-01-01','0.0000','0.0000','0.0000','0.0000','0.0000',0,0,1,200.00,'0.0000','0.0000','','',1,0,3),('130','Steel','13mm round spring steel X4K grade','13mm round spring steel X4K grade	','Kgs','B','1800-01-01','0.0000','0.0000','0.6200','0.0000','0.0000',0,0,0,1200.00,'0.0600','1.0000','','',1,0,0),('140','Steel','14mm round steel X4k grade','14mm round steel X4k grade	','metres','B','1800-01-01','0.0000','0.0000','0.6200','0.0000','0.0000',0,0,0,1250.00,'0.0200','1.0000','','',1,0,0),('HON90021','Coil','Honda Civic front coil spring 1990-94','Honda Civic front coil spring 1990-94	','each','M','1800-01-01','0.0000','0.0000','6.8900','1.6000','1.2000',0,0,1,50.00,'0.3000','3.0000','','SP',1,0,3),('HON90022','Coil','Honda Civic rear coil spring 1990-94','Honda Civic rear coil spring 1990-94	','each','M','1800-01-01','0.0000','0.0000','2.1000','0.0000','0.0000',0,0,0,50.00,'0.0000','3.0000','','SP',1,0,0),('HON92FrSus','SUSASS','Honda 92 Front suspension assembly','Honda 92 Front suspension assembly','each','A','1800-01-01','0.0000','0.0000','0.0000','0.0000','0.0000',0,0,0,0.00,'0.0000','0.0000','','',1,0,0),('HONCIVIC92SET','Coil','Honda Civic 90-94 spring set','Honda Civic 90-94 spring set','each','K','1800-01-01','0.0000','0.0000','0.0000','0.0000','0.0000',0,0,0,0.00,'0.3000','6.0000','','',1,0,0),('HONShock','SHOCKS','Honda 92 Monroe Shock Absorber','Honda 92 Monroe Shock Absorber','each','B','1800-01-01','0.0000','0.0000','8.5000','0.0000','0.0000',0,0,0,0.00,'0.0000','0.0000','','',1,0,0);
UNLOCK TABLES;
/*!40000 ALTER TABLE StockMaster ENABLE KEYS */;

--
-- Dumping data for table `StockMoves`
--


/*!40000 ALTER TABLE StockMoves DISABLE KEYS */;
LOCK TABLES StockMoves WRITE;
INSERT INTO StockMoves VALUES (150,'125',25,12,'MEL','2004-07-13','','','1.0417',55,'BIGBISC (Biga Engineering PLC) - 14',1170.0000,0.0000,0.6200,1,1170,0,0,''),(151,'HON90021',25,12,'MEL','2004-07-13','','','3.8377',55,'BIGBISC (Biga Engineering PLC) - 14',512.0000,0.0000,9.6900,1,512,0,0,''),(153,'125',10,1,'MEL','2004-07-14','WALMON','WALM','0.7353',55,'27',-10.0000,0.0250,0.6200,1,1160,0,0,''),(154,'HON90021',10,1,'MEL','2004-07-14','WALMON','WALM','14.7059',55,'27',-25.0000,0.0000,9.6900,1,487,0,0,''),(155,'125',11,1,'DEN','2004-07-15','JOHNSON','JOHNTEMPE','2.3300',55,'',10.0000,0.0000,0.6200,1,10,0,0,''),(156,'HON90021',11,1,'DEN','2004-07-15','JOHNSON','JOHNTEMPE','16.2500',55,'',21.0000,0.0000,9.6900,1,21,0,0,''),(157,'125',11,2,'MUC','2004-07-17','BILLYBUNT','BILLYBUNT','2.9375',55,'',30.0000,0.0000,0.6200,1,30,0,0,''),(158,'HON90021',11,3,'MEL','2004-07-17','WALMON','WALM','14.7059',55,'Ex Inv - 1',3.0000,0.0250,9.6900,1,490,0,0,''),(159,'125',17,1,'DEN','2004-07-17','','','0.0000',55,'As per Eric damaged on return',-1.0000,0.0000,0.0000,1,9,0,0,''),(160,'125',17,2,'DEN','2004-07-17','','','0.0000',55,'reverse from wrong location',1.0000,0.0000,0.0000,1,10,0,0,''),(161,'125',17,3,'MUC','2004-07-17','','','0.0000',55,'As per Eric damaged on return',-1.0000,0.0000,0.0000,1,29,0,0,''),(162,'125',16,1,'DEN','2004-07-17','','','0.0000',55,'To Munich Test Facility',-10.0000,0.0000,0.0000,1,0,0,0,''),(163,'125',16,1,'MUC','2004-07-17','','','0.0000',55,'From Denver',10.0000,0.0000,0.0000,1,39,0,0,''),(164,'HON90021',16,1,'DEN','2004-07-17','','','0.0000',55,'To Munich Test Facility',-3.0000,0.0000,0.0000,1,18,0,0,''),(165,'HON90021',16,1,'MUC','2004-07-17','','','0.0000',55,'From Denver',3.0000,0.0000,0.0000,1,3,0,0,''),(166,'HON90022',10,2,'DEN','2004-07-30','WATTSGRP','WATTLA','15.8600',55,'28',-5.0000,0.0000,2.1000,1,-5,0,0,''),(167,'125',10,3,'MEL','2004-08-20','WALMON','WALM','1.3059',56,'29',-5.0000,0.0000,0.6200,1,1155,0,0,'Testing one two three'),(168,'HON90021',10,3,'MEL','2004-08-20','WALMON','WALM','14.7059',56,'29',-12.0000,0.0000,9.6900,1,478,0,0,''),(169,'HON90021',11,4,'MUC','2004-08-23','GRANHR','GRAN','12.6500',56,'',15.0000,0.2500,9.6900,1,0,0,0,''),(170,'125',11,5,'MEL','2004-08-23','WALMON','WALMOADEL','0.1941',56,'',25.0000,0.0500,0.6200,1,1180,0,0.1,'Scrap steel credited in lieu'),(171,'HON90021',10,4,'MEL','2004-09-21','WALMON','WALM','14.7059',57,'30',-2.0000,0.0000,9.6900,1,476,0,0,''),(172,'HON90022',10,4,'MEL','2004-09-21','WALMON','WALM','14.4118',57,'30',-2.0000,0.0000,2.1000,1,-2,0,0,''),(173,'HON90021',11,7,'MEL','2004-09-20','WALMON','WALM','14.7059',57,'',2.0000,0.0000,9.6900,1,478,0,0,''),(174,'HON90022',11,7,'MEL','2004-09-20','WALMON','WALM','14.4118',57,'',2.0000,0.0000,2.1000,1,0,0,0,''),(175,'HON90021',10,5,'MUC','2004-09-21','GRANHR','GRAN','12.6500',57,'31',-2.0000,0.0000,9.6900,1,1,0,0,''),(176,'HON90021',16,5,'DEN','2004-09-20','','','0.0000',57,'To Melbourne Australia',-5.0000,0.0000,0.0000,1,13,0,0,''),(177,'HON90021',16,5,'MEL','2004-09-20','','','0.0000',57,'From Denver',5.0000,0.0000,0.0000,1,483,0,0,''),(178,'125',25,13,'DEN','2004-09-20','','','0.4000',57,'BIGBISC (Biga Engineering PLC) - 15',95.0000,0.0000,0.6200,1,95,0,0,''),(179,'125',10,6,'DEN','2004-09-21','WATTSGRP','WATTLA','1.3300',57,'28',-3.0000,0.0000,0.6200,1,92,0,0,''),(180,'HON90021',10,7,'MUC','2004-09-21','GRANHR','GRAN','12.6500',57,'31',-1.0000,0.0000,9.6900,1,0,0,0,'');
UNLOCK TABLES;
/*!40000 ALTER TABLE StockMoves ENABLE KEYS */;

--
-- Dumping data for table `StockSerialItems`
--


/*!40000 ALTER TABLE StockSerialItems DISABLE KEYS */;
LOCK TABLES StockSerialItems WRITE;
INSERT INTO StockSerialItems VALUES ('125','DEN','124442221',72),('125','MEL','211122115',10),('125','DEN','5422441',0),('125','MUC','5422441',10),('125','MEL','544214511',15),('125','DEN','5666335444',20),('125','DEN','85442-FG1',0),('125','MUC','85442-FG1',11),('125','MUC','85442-FG6',18),('125','MEL','QF12253',335),('125','MEL','QF12254',200),('125','MEL','QF12256',150),('125','MEL','QF12257',350),('125','MEL','QF12258',120),('HON90021','DEN','4544111',13),('HON90021','MEL','4544111',5),('HON90021','MUC','4544111',0),('HON90021','MEL','89001',175),('HON90021','MEL','89002',200),('HON90021','MEL','89003',100);
UNLOCK TABLES;
/*!40000 ALTER TABLE StockSerialItems ENABLE KEYS */;

--
-- Dumping data for table `StockSerialMoves`
--


/*!40000 ALTER TABLE StockSerialMoves DISABLE KEYS */;
LOCK TABLES StockSerialMoves WRITE;
INSERT INTO StockSerialMoves VALUES (271,150,'125','QF12253',350),(272,150,'125','QF12254',200),(273,150,'125','QF12256',150),(274,150,'125','QF12257',350),(275,150,'125','QF12258',120),(276,151,'HON90021','89001',200),(277,151,'HON90021','89002',200),(278,151,'HON90021','89003',112),(279,153,'125','QF12253',-10),(280,154,'HON90021','89001',-25),(281,155,'125','5422441',10),(282,156,'HON90021','4544111',21),(283,157,'125','85442-FG1',12),(284,157,'125','85442-FG6',18),(285,159,'125','85442-FG1',-1),(286,160,'125','85442-FG1',1),(287,161,'125','85442-FG1',-1),(288,162,'125','5422441',-10),(289,163,'125','5422441',10),(290,164,'HON90021','4544111',-3),(291,165,'HON90021','4544111',3),(292,167,'125','QF12253',-5),(293,168,'HON90021','89003',-12),(294,170,'125','544214511',15),(295,170,'125','211122115',10),(296,171,'HON90021','89001',-2),(297,173,'HON90021','89001',2),(298,175,'HON90021','4544111',-2),(299,176,'HON90021','4544111',-5),(300,177,'HON90021','4544111',5),(301,178,'125','5666335444',20),(302,178,'125','124442221',75),(303,179,'125','124442221',-3),(304,180,'HON90021','4544111',-1);
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
INSERT INTO SuppTrans VALUES (14,20,'FREDBLOW','5442','2004-07-14','2004-08-22',0,1.000000,53.5200,5.3500,0.0000,0.0000,'',0,25),(4,22,'BIGBISC','Cheque','2004-07-29','0000-00-00',0,0.500000,100.0000,0.0000,0.0000,0.0000,'',0,26);
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
INSERT INTO Suppliers VALUES ('BHPSTEEL','Bright Helpful People Steel Co Gmbh','P O Box 67','Frankfurt','Germany','','DEM','1960-03-17','30',500.0000,'2003-02-01 00:00:00','','0','',0,1),('BIGBISC','Biga Engineering PLC','P O Box 8918','Brighton','West Sussex BGF 11G','England','GBP','2000-06-08','30',-100.0000,'2004-07-29 00:00:00','','0','',0,1),('FREDBLOW','Frederick Blowers & Co Inc','123 Road Close','Metropolis','','','USD','2003-06-29','20',0.0000,NULL,'','0','',0,1),('GREGCA','Gregor Capel Inc','P O Box 54','633 Groper Lane','Newmarket','England','GBP','1953-12-06','30',12.0000,'2003-08-07 00:00:00','','0','',0,1),('MITOOI2','Mitsooy Corporation Inc','P O Box 8775','Baltimore','USA','','USD','1998-04-12','30',0.0000,NULL,'','0','',0,1);
UNLOCK TABLES;
/*!40000 ALTER TABLE Suppliers ENABLE KEYS */;

--
-- Dumping data for table `SysTypes`
--


/*!40000 ALTER TABLE SysTypes DISABLE KEYS */;
LOCK TABLES SysTypes WRITE;
INSERT INTO SysTypes VALUES (0,'Journal - GL',9),(1,'Payment - GL',17),(2,'Receipt - GL',3),(3,'Standing Journal',0),(10,'Sales Invoice',7),(11,'Credit Note',7),(12,'Receipt',1),(15,'Journal - Debtors',0),(16,'Location Transfer',5),(17,'Stock Adjustment',3),(18,'Purchase Order',0),(20,'Purchase Invoice',14),(21,'Debit Note',4),(22,'Creditors Payment',4),(23,'Creditors Journal',0),(25,'Purchase Order Delivery',13),(26,'Work Order Receipt',0),(28,'Work Order Issue',0),(29,'Work Order Variance',0),(30,'Sales Order',0),(31,'Shipment Close',3),(35,'Cost Update',3),(50,'Opening Balance',0);
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
INSERT INTO WWW_Users VALUES ('Admin','weeping','Phil Daintree','','','','DEN',7,'2004-06-06 13:23:59','','A4','1,1,1,1,1,1,1,1,',0,0,'professional','en'),('demo','weberp','Demonstration user','','','','DEN',7,'2004-09-20 05:10:39','','A4','1,1,1,1,1,0,1,1,',0,50,'professional','en'),('testy','weberp','','GRANHR','','','DEN',6,'2004-02-23 20:21:56','GRAN','A4','1,0,0,0,0,0,0,0,',0,0,'professional','en');
UNLOCK TABLES;
/*!40000 ALTER TABLE WWW_Users ENABLE KEYS */;

--
-- Dumping data for table `WorkCentres`
--


/*!40000 ALTER TABLE WorkCentres DISABLE KEYS */;
LOCK TABLES WorkCentres WRITE;
INSERT INTO WorkCentres VALUES ('ASSEM','DEN','Assembly',1.0000,'20.0000',560000,'0.0000');
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

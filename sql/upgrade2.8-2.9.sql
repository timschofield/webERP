ALTER TABLE `BankAccounts` CHANGE `AccountCode` `AccountCode` INT( 11 ) NOT NULL

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

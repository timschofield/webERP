INSERT INTO scripts VALUES ('InternalStockRequestInquiry.php',1,'Internal Stock Request inquiry');
ALTER table stockrequest ADD initiator varchar(20) NOT NULL DEFAULT '';
INSERT INTO securitytokens VALUES (19,'Internal stock request fully access authority');
INSERT INTO scripts VALUES ('PDFGLJournalCN.php',1,'Print GL Journal Chinese version');
ALTER table custcontacts ADD statement tinyint(4) NOT NULL DEFAULT 0;
INSERT INTO scripts VALUES ('PcTabExpensesList.php', '15', 'Creates excel with all movements of tab between dates');

-- standardise transaction date to DATE type:
ALTER TABLE `debtortrans` CHANGE `trandate` `trandate` DATE NOT NULL DEFAULT '0000-00-00';
ALTER table supplierdiscounts CONVERT TO CHARACTER SET utf8;
INSERT INTO scripts VALUES ('PcAssignCashTabToTab.php',12,'Assign cash from one tab to another');
ALTER table workorders ADD remark text DEFAULT NULL;
ALTER table workorders ADD reference varchar(40) NOT NULL DEFAULT '';

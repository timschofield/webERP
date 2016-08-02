INSERT INTO scripts VALUES ('InternalStockRequestInquiry.php',1,'Internal Stock Request inquiry');
ALTER table stockrequest ADD initiator varchar(20) NOT NULL DEFAULT '';
INSERT INTO securitytokens VALUES (19,'Internal stock request fully access authority');
INSERT INTO scripts VALUES ('PDFGLJournalCN.php',1,'Print GL Journal Chinese version');



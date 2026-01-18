SET FOREIGN_KEY_CHECKS=0;

TRUNCATE test_erp_archive.`audittrail`;

TRUNCATE test_erp_archive.`banktrans`;
INSERT INTO test_erp_archive.banktrans SELECT * FROM kl_erp_archive.banktrans;

TRUNCATE test_erp_archive.`custallocns`;
INSERT INTO test_erp_archive.custallocns SELECT * FROM kl_erp_archive.custallocns;

TRUNCATE test_erp_archive.`debtortrans`;
INSERT INTO test_erp_archive.debtortrans (id, transno, type, debtorno, branchcode, trandate, inputdate, prd, settled, reference, tpe, order_, rate, ovamount, ovgst, ovfreight, ovdiscount, diffonexch, alloc, invtext, shipvia, edisent, consignment, packages, salesperson)
SELECT id, transno, type, debtorno, branchcode, trandate, inputdate, prd, settled, reference, tpe, order_, rate, ovamount, ovgst, ovfreight, ovdiscount, diffonexch, alloc, invtext, shipvia, edisent, consignment, packages, salesperson
FROM kl_erp_archive.debtortrans; 

TRUNCATE test_erp_archive.`debtortranstaxes`;
INSERT INTO test_erp_archive.debtortranstaxes SELECT * FROM kl_erp_archive.debtortranstaxes;

TRUNCATE test_erp_archive.`gltrans`;
INSERT INTO test_erp_archive.gltrans SELECT * FROM kl_erp_archive.gltrans;

TRUNCATE test_erp_archive.`klconsignment`;
INSERT INTO test_erp_archive.klconsignment SELECT * FROM kl_erp_archive.klconsignment;

TRUNCATE test_erp_archive.`loctransfers`;
INSERT INTO test_erp_archive.loctransfers (loctransferid, reference, stockid, shipqty, recqty, shipdate, recdate, shiploc, recloc, reason)
SELECT loctransferid, reference, stockid, shipqty, recqty, shipdate, recdate, shiploc, recloc, reason
FROM kl_erp_archive.loctransfers;

TRUNCATE test_erp_archive.`pcashdetails`;
INSERT INTO test_erp_archive.pcashdetails SELECT * FROM kl_erp_archive.pcashdetails;

TRUNCATE test_erp_archive.`stockmoves`;
INSERT INTO test_erp_archive.stockmoves SELECT * FROM kl_erp_archive.stockmoves;

TRUNCATE test_erp_archive.`stockmovestaxes`;
INSERT INTO test_erp_archive.stockmovestaxes SELECT * FROM kl_erp_archive.stockmovestaxes;

SET FOREIGN_KEY_CHECKS=1;

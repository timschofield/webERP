SET FOREIGN_KEY_CHECKS=0;

TRUNCATE test_erp_archive.`gltrans`;
INSERT INTO test_erp_archive.gltrans SELECT * FROM kl_erp_archive.gltrans;

TRUNCATE test_erp_archive.`loctransfers`;
INSERT INTO test_erp_archive.loctransfers SELECT * FROM kl_erp_archive.loctransfers;

TRUNCATE test_erp_archive.`pcashdetails`;
INSERT INTO test_erp_archive.pcashdetails SELECT * FROM kl_erp_archive.pcashdetails;

TRUNCATE test_erp_archive.`stockmoves`;
INSERT INTO test_erp_archive.stockmoves SELECT * FROM kl_erp_archive.stockmoves;

TRUNCATE test_erp_archive.`stockmovestaxes`;
INSERT INTO test_erp_archive.stockmovestaxes SELECT * FROM kl_erp_archive.stockmovestaxes;

SET FOREIGN_KEY_CHECKS=1;

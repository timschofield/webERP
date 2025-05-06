SET FOREIGN_KEY_CHECKS=0;

TRUNCATE test_erp_old_data.`gltrans`;
INSERT INTO test_erp_old_data.gltrans SELECT * FROM kl_erp_old_data.gltrans;

TRUNCATE test_erp_old_data.`loctransfers`;
INSERT INTO test_erp_old_data.loctransfers SELECT * FROM kl_erp_old_data.loctransfers;

TRUNCATE test_erp_old_data.`pcashdetails`;
INSERT INTO test_erp_old_data.pcashdetails SELECT * FROM kl_erp_old_data.pcashdetails;

TRUNCATE test_erp_old_data.`stockmoves`;
INSERT INTO test_erp_old_data.stockmoves SELECT * FROM kl_erp_old_data.stockmoves;

TRUNCATE test_erp_old_data.`stockmovestaxes`;
INSERT INTO test_erp_old_data.stockmovestaxes SELECT * FROM kl_erp_old_data.stockmovestaxes;

SET FOREIGN_KEY_CHECKS=1;

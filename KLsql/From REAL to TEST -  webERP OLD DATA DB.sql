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

/* in production */
UPDATE `klolddatapurged` SET `gltransperiod` = 93;
UPDATE `klolddatapurged` SET `stockmovesprd` = 100;


/* in OLD DATA */
DELETE FROM `stockmoves` WHERE prd > 100;
UPDATE gltrans SET account = REPLACE(account, 'PT', 'BB') WHERE account LIKE '%PT'; 


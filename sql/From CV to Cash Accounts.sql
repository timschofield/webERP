UPDATE gltrans SET account = "410000000" WHERE account = "410000000CV";
UPDATE gltrans SET account = "410000000" WHERE account = "410000010CV";
UPDATE gltrans SET account = "410000500" WHERE account = "410000500CV";
UPDATE gltrans SET account = "410010000" WHERE account = "410010000CV";
UPDATE gltrans SET account = "410000000" WHERE account = "410010010CV";
UPDATE gltrans SET account = "410020000" WHERE account = "410020000CV";
UPDATE gltrans SET account = "510010000" WHERE account = "510010000CV";
UPDATE gltrans SET account = "510010100" WHERE account = "510010100CV";
UPDATE gltrans SET account = "510200000" WHERE account = "510200000CV";

UPDATE gltrans SET account = "125900000" WHERE account = "125900000CV";
UPDATE gltrans SET account = "211030200" WHERE account = "211030200CV";
UPDATE gltrans SET account = "611011100" WHERE account = "611011100CV";
UPDATE gltrans SET account = "611011300" WHERE account = "611011300CV";
UPDATE gltrans SET account = "611011400" WHERE account = "611011400CV";
UPDATE gltrans SET account = "611011600" WHERE account = "611011600CV";
UPDATE gltrans SET account = "611011800" WHERE account = "611011800CV";
UPDATE gltrans SET account = "612011100" WHERE account = "612011100CV";
UPDATE gltrans SET account = "612011200" WHERE account = "612011200CV";
UPDATE gltrans SET account = "612011910" WHERE account = "612011910CV";
UPDATE gltrans SET account = "612011940" WHERE account = "612011940CV";
UPDATE gltrans SET account = "614011500" WHERE account = "614011510CV";
UPDATE gltrans SET account = "614011600" WHERE account = "614011600CV";
UPDATE gltrans SET account = "614011800" WHERE account = "614011800CV";
UPDATE gltrans SET account = "700211700" WHERE account = "700211700CV";

UPDATE gltrans SET account = "611019000" WHERE account = "611020000CV";
UPDATE gltrans SET account = "612011900" WHERE account = "612011920CV";
UPDATE gltrans SET account = "612011900" WHERE account = "612011930CV";
UPDATE gltrans SET account = "612011900" WHERE account = "612012000CV";
UPDATE gltrans SET account = "614012600" WHERE account = "614011100CV";
UPDATE gltrans SET account = "614012600" WHERE account = "614011400CV";
UPDATE gltrans SET account = "614012600" WHERE account = "614011550CV";
UPDATE gltrans SET account = "614012600" WHERE account = "614011810CV";
UPDATE gltrans SET account = "614012600" WHERE account = "614012000CV";
UPDATE gltrans SET account = "614012600" WHERE account = "614012050CV";
UPDATE gltrans SET account = "614012600" WHERE account = "614012200CV";
UPDATE gltrans SET account = "614012600" WHERE account = "614012550CV";
UPDATE gltrans SET account = "614012600" WHERE account = "614012850CV";
UPDATE gltrans SET account = "614012600" WHERE account = "614012900CV";
UPDATE gltrans SET account = "614012600" WHERE account = "614013500CV";
UPDATE gltrans SET account = "614012600" WHERE account = "614013600CV";
UPDATE gltrans SET account = "700219900" WHERE account = "700211200CV";
UPDATE gltrans SET account = "700219900" WHERE account = "700211300CV";
UPDATE gltrans SET account = "700219900" WHERE account = "611012000CV";
UPDATE gltrans SET account = "700219900" WHERE account = "611012002CV";
UPDATE gltrans SET account = "700219900" WHERE account = "611012005CV";
UPDATE gltrans SET account = "700219900" WHERE account = "611012010CV";
UPDATE gltrans SET account = "700219900" WHERE account = "611012020CV";
UPDATE gltrans SET account = "700219900" WHERE account = "611012030CV";
UPDATE gltrans SET account = "700219900" WHERE account = "614012500CV";
UPDATE gltrans SET account = "700219900" WHERE account = "800011000CV";
UPDATE gltrans SET account = "700219900" WHERE account = "800012000CV";

UPDATE gltrans SET account = "612011900" WHERE account = "612011800CV";
UPDATE gltrans SET account = "614012600" WHERE account = "613011200CV";
UPDATE gltrans SET account = "614012600" WHERE account = "613011400CV";
UPDATE gltrans SET account = "614012600" WHERE account = "613019000CV";
UPDATE gltrans SET account = "614012600" WHERE account = "614012650CV";
UPDATE gltrans SET account = "612011100" WHERE account = "612019000CV";
UPDATE gltrans SET account = "612011100" WHERE account = "612011800CV";
UPDATE gltrans SET account = "350510100" WHERE account = "310110100CV";
UPDATE gltrans SET account = "350510100" WHERE account = "310110200CV";

UPDATE banktrans SET bankact = "111111100" WHERE bankact = "111121100CV";
DELETE FROM bankaccountusers WHERE accountcode = "111121100CV";
DELETE FROM bankaccounts WHERE accountcode = "111121100CV";
UPDATE gltrans SET account = "111111100" WHERE account = "111121100CV";

UPDATE banktrans SET bankact = "111111100" WHERE bankact = "111121105CV";
DELETE FROM bankaccountusers WHERE accountcode = "111121105CV";
DELETE FROM bankaccounts WHERE accountcode = "111121105CV";
UPDATE gltrans SET account = "111111100" WHERE account = "111121105CV";

UPDATE banktrans SET bankact = "111111100" WHERE bankact = "111203010CV";
DELETE FROM bankaccountusers WHERE accountcode = "111203010CV";
DELETE FROM bankaccounts WHERE accountcode = "111203010CV";
UPDATE gltrans SET account = "111111100" WHERE account = "111203010CV";


UPDATE gltrans SET account = "XXXX" WHERE account = "XXXX";
UPDATE gltrans SET account = "XXXX" WHERE account = "XXXX";
UPDATE gltrans SET account = "XXXX" WHERE account = "XXXX";
UPDATE gltrans SET account = "XXXX" WHERE account = "XXXX";
UPDATE gltrans SET account = "XXXX" WHERE account = "XXXX";
UPDATE gltrans SET account = "XXXX" WHERE account = "XXXX";
UPDATE gltrans SET account = "XXXX" WHERE account = "XXXX";
UPDATE gltrans SET account = "XXXX" WHERE account = "XXXX";
UPDATE gltrans SET account = "XXXX" WHERE account = "XXXX";
UPDATE gltrans SET account = "XXXX" WHERE account = "XXXX";
UPDATE gltrans SET account = "XXXX" WHERE account = "XXXX";
UPDATE gltrans SET account = "XXXX" WHERE account = "XXXX";
UPDATE gltrans SET account = "XXXX" WHERE account = "XXXX";
UPDATE gltrans SET account = "XXXX" WHERE account = "XXXX";
UPDATE gltrans SET account = "XXXX" WHERE account = "XXXX";
UPDATE gltrans SET account = "XXXX" WHERE account = "XXXX";

/* MIXED IDR AND USD BUT OBSOLETE BANK ACCOUNTS AND CASH */

UPDATE banktrans SET bankact = "111209010" WHERE bankact = "111209011";
DELETE FROM bankaccountusers WHERE accountcode = "111209011";
DELETE FROM bankaccounts WHERE accountcode = "111209011";
UPDATE gltrans SET account = "111209010" WHERE account = "111209011";

UPDATE banktrans SET bankact = "111209010" WHERE bankact = "111209012";
DELETE FROM bankaccountusers WHERE accountcode = "111209012";
DELETE FROM bankaccounts WHERE accountcode = "111209012";
UPDATE gltrans SET account = "111209010" WHERE account = "111209012";

UPDATE banktrans SET bankact = "111209010" WHERE bankact = "111131101";
DELETE FROM bankaccountusers WHERE accountcode = "111131101";
DELETE FROM bankaccounts WHERE accountcode = "111131101";
UPDATE gltrans SET account = "111209010" WHERE account = "111131101";




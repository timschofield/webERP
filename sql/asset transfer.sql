/* toko SE from PTBB to CASH 

SELECT SUM(amount) FROM `fixedassettrans` WHERE `assetid` = 212 AND `fixedassettranstype` = "depn" AND `transdate` < "2017-01-31" ORDER BY `transdate` ASC;
Value already amortized until 31/12/2016 = 71.666.666 IDR
Value as new = 200.000.000
So:
Value at 2017-01-01 = 128333334 */

Change asset category from PT to cash

Journal from asset PT to asset cash for 128.333.334 IDR

UPDATE gltrans SET account = "614011600" 
	WHERE account = "614011600PT" AND `narrative` = "Monthly depreciation for asset 212";
UPDATE gltrans SET account = "125900000" 
	WHERE account = "125900000PT" AND `narrative` = "Monthly depreciation for asset 212";
	
/* toko SS from PTBB to CASH 

SELECT SUM(amount) FROM `fixedassettrans` WHERE `assetid` = 282 AND `fixedassettranstype` = "depn" AND `transdate` < "2017-01-31" ORDER BY `transdate` ASC;
Value already amortized until 31/12/2016 = 116.666.666 IDR
Value as new = 500.000.000
So:
Value at 2017-01-01 = 383333334 */

Change asset category from PT to cash

Journal from asset PT to asset cash for 383.333.334 IDR

UPDATE gltrans SET account = "614011600" 
	WHERE account = "614011600PT" AND `narrative` = "Monthly depreciation for asset 282";
UPDATE gltrans SET account = "125900000" 
	WHERE account = "125900000PT" AND `narrative` = "Monthly depreciation for asset 282";
	


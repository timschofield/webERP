SELECT * 
FROM `relateditems`
WHERE SUBSTRING(`stockid`,3,2) = "AN" 
AND SUBSTRING(`related`,3,2) = "AN"
AND SUBSTRING(`stockid`,1,6) = SUBSTRING(`related`,1,6);

SELECT * 
FROM `relateditems`
WHERE `stockid` = `related`;

SELECT * 
FROM `relateditems`
WHERE `stockid` = "WKPC01" 

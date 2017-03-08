
SET FOREIGN_KEY_CHECKS=0;

TRUNCATE audittrail;

DELETE FROM banktrans WHERE transdate <= "2012-12-31";

DELETE FROM custallocns WHERE datealloc <= "2012-12-31";
DELETE FROM debtortrans WHERE trandate <= "2012-12-31";
DELETE FROM debtortranstaxes WHERE NOT EXISTS (SELECT * 
					FROM debtortrans
					WHERE debtortrans.transno = debtortranstaxes.debtortransid);

DELETE FROM gltrans WHERE trandate <= "2012-12-31";
DELETE FROM grns WHERE deliverydate <= "2012-12-31";

DELETE FROM loctransfers WHERE shipdate <= "2012-12-31";

TRUNCATE mrpcalendar;
TRUNCATE mrpdemands;
TRUNCATE mrpdemandtypes;
TRUNCATE mrpparameters;
TRUNCATE mrpplannedorders;
TRUNCATE mrprequirements;
TRUNCATE mrpsupplies;

DELETE FROM pcashdetails WHERE date <= "2012-12-31";

DELETE FROM prices WHERE enddate <= "2011-12-31";

DELETE FROM purchdata WHERE effectivefrom <= "2011-12-31";

DELETE FROM purchorders WHERE orddate <= "2011-12-31";

DELETE FROM purchorderdetails WHERE NOT EXISTS (SELECT *
					FROM purchorders
					WHERE purchorders.orderno = purchorderdetails.orderno);

DELETE FROM salesanalysis WHERE periodno <= 33;

DELETE FROM salesorders WHERE orddate <= "2011-12-31";

DELETE FROM salesorderdetails WHERE  NOT EXISTS (SELECT *
						FROM salesorders
						WHERE salesorders.orderno = salesorderdetails.orderno);

DELETE FROM stockmoves WHERE trandate <= "2012-12-31";

DELETE FROM stockmovestaxes WHERE NOT EXISTS (SELECT *
					FROM stockmoves
					WHERE stockmoves.stkmoveno = stockmovestaxes.stkmoveno);


DELETE FROM supptranstaxes WHERE NOT EXISTS (SELECT * 
					FROM supptrans
					WHERE supptrans.transno = supptranstaxes.supptransid);

DELETE FROM supptrans WHERE trandate <= "2012-12-31";

DELETE FROM workorders WHERE startdate <= "2012-12-31";
DELETE FROM woitems WHERE NOT EXISTS (SELECT * 
				FROM workorders
				WHERE woitems.wo = workorders.wo);

DELETE FROM worequirements WHERE NOT EXISTS (SELECT * 
				FROM workorders
				WHERE worequirements.wo = workorders.wo);
										
												
SET FOREIGN_KEY_CHECKS=1;
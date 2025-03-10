ALTER TABLE pcashdetails CHANGE authorized authorized date NOT NULL DEFAULT '1000-01-01';
UPDATE pcashdetails SET authorized = '1000-01-01' WHERE authorized = '0000-00-00';



-- First group: Change date fields to default '1000-01-01'
ALTER TABLE assetmanager CHANGE datepurchased datepurchased DATE NOT NULL DEFAULT '1000-01-01';
ALTER TABLE banktrans CHANGE transdate transdate date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE bom CHANGE effectiveafter effectiveafter date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE contracts CHANGE requireddate requireddate date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE custallocns CHANGE datealloc datealloc date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE custnotes CHANGE date date date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE deliverynotes CHANGE deliverydate deliverydate date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE fixedassets CHANGE datepurchased datepurchased date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE fixedassets CHANGE disposaldate disposaldate date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE gltrans CHANGE trandate trandate date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE grns CHANGE deliverydate deliverydate date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE mrpdemands CHANGE duedate duedate date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE offers CHANGE expirydate expirydate date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE periods CHANGE lastdate_in_period lastdate_in_period date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE purchorderdetails CHANGE deliverydate deliverydate date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE stockcheckfreeze CHANGE stockcheckdate stockcheckdate date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE stockmaster CHANGE lastcostupdate lastcostupdate date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE stockmoves CHANGE trandate trandate date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE stockrequest CHANGE despatchdate despatchdate date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE suppallocs CHANGE datealloc datealloc date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE suppliers CHANGE suppliersince suppliersince date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE timesheets CHANGE weekending weekending date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE workorders CHANGE requiredby requiredby date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE workorders CHANGE startdate startdate date NOT NULL DEFAULT '1000-01-01';

-- Second group: Change datetime fields to default '1000-01-01 00:00:00'
ALTER TABLE auditscripts CHANGE executiondate executiondate datetime NOT NULL DEFAULT '1000-01-01 00:00:00';
ALTER TABLE audittrail CHANGE transactiondate transactiondate datetime NOT NULL DEFAULT '1000-01-01 00:00:00';
ALTER TABLE buckets CHANGE availdate availdate datetime NOT NULL DEFAULT '1000-01-01 00:00:00';
ALTER TABLE debtorsmaster CHANGE clientsince clientsince datetime NOT NULL DEFAULT '1000-01-01 00:00:00';
ALTER TABLE debtortrans CHANGE trandate trandate datetime NOT NULL DEFAULT '1000-01-01 00:00:00';
ALTER TABLE loctransfers CHANGE shipdate shipdate datetime NOT NULL DEFAULT '1000-01-01 00:00:00';
ALTER TABLE loctransfers CHANGE recdate recdate datetime NOT NULL DEFAULT '1000-01-01 00:00:00';
ALTER TABLE stockserialitems CHANGE expirationdate expirationdate datetime NOT NULL DEFAULT '1000-01-01 00:00:00';
ALTER TABLE tenders CHANGE requiredbydate requiredbydate datetime NOT NULL DEFAULT '1000-01-01 00:00:00';

-- Third group: Update existing records with new default dates
UPDATE purchorders SET deliverydate = '1000-01-01' WHERE deliverydate = '0000-00-00';
UPDATE purchorders SET orddate = '1000-01-01' WHERE orddate = '0000-00-00';
UPDATE purchorders SET revised = '1000-01-01' WHERE revised = '0000-00-00';
UPDATE purchorders SET agreeddeliverydate = '1000-01-01' WHERE agreeddeliverydate = '0000-00-00';
UPDATE purchorders SET paymentdate = '1000-01-01' WHERE paymentdate = '0000-00-00';
UPDATE purchorders SET shipmentdate = '1000-01-01' WHERE shipmentdate = '0000-00-00';
UPDATE purchorders SET customsdate = '1000-01-01' WHERE customsdate = '0000-00-00';
UPDATE purchorders SET arrivaldate = '1000-01-01' WHERE arrivaldate = '0000-00-00';

UPDATE assetmanager SET datepurchased = '1000-01-01' WHERE datepurchased = '0000-00-00';

UPDATE audittrail SET transactiondate = '1000-01-01 00:00:00' WHERE transactiondate = '0000-00-00 00:00:00';

UPDATE auditscripts SET executiondate = '1000-01-01 00:00:00' WHERE executiondate = '0000-00-00 00:00:00';

UPDATE banktrans SET transdate = '1000-01-01' WHERE transdate = '0000-00-00';

UPDATE bom SET effectiveafter = '1000-01-01' WHERE effectiveafter = '0000-00-00';

UPDATE contracts SET requireddate = '1000-01-01' WHERE requireddate = '0000-00-00';

UPDATE custallocns SET datealloc = '1000-01-01' WHERE datealloc = '0000-00-00';

UPDATE custnotes SET date = '1000-01-01' WHERE date = '0000-00-00';

UPDATE debtorsmaster SET clientsince = '1000-01-01 00:00:00' WHERE clientsince = '0000-00-00 00:00:00';
UPDATE debtorsmaster SET lastpaiddate = '1000-01-01 00:00:00' WHERE lastpaiddate = '0000-00-00 00:00:00';

UPDATE debtortrans SET trandate = '1000-01-01 00:00:00' WHERE trandate = '0000-00-00 00:00:00';

UPDATE debtortypenotes SET date = '1000-01-01' WHERE date = '0000-00-00';

UPDATE deliverynotes SET deliverydate = '1000-01-01' WHERE deliverydate = '0000-00-00';

UPDATE fixedassets SET datepurchased = '1000-01-01' WHERE datepurchased = '0000-00-00';
UPDATE fixedassets SET disposaldate = '1000-01-01' WHERE disposaldate = '0000-00-00';

UPDATE gltrans SET trandate = '1000-01-01' WHERE trandate = '0000-00-00';

UPDATE grns SET deliverydate = '1000-01-01' WHERE deliverydate = '0000-00-00';

UPDATE klretailcustomers SET date_added = '1000-01-01 00:00:00' WHERE date_added = '0000-00-00 00:00:00';
UPDATE klretailcustomers SET date_updated = '1000-01-01 00:00:00' WHERE date_updated = '0000-00-00 00:00:00';

UPDATE klstockmarketplaces SET date_created = '1000-01-01 00:00:00' WHERE date_created = '0000-00-00 00:00:00';
UPDATE klstockmarketplaces SET date_updated = '1000-01-01 00:00:00' WHERE date_updated = '0000-00-00 00:00:00';

UPDATE locstock SET date_created = '1000-01-01 00:00:00' WHERE date_created = '0000-00-00 00:00:00';
UPDATE locstock SET date_updated = '1000-01-01' WHERE date_updated = '0000-00-00';
UPDATE locstock SET date_updated = '1000-01-01 00:00:00' WHERE date_updated = '0000-00-00 00:00:00';

UPDATE loctransfers SET shipdate = '1000-01-01 00:00:00' WHERE shipdate = '0000-00-00 00:00:00';
UPDATE loctransfers SET recdate = '1000-01-01 00:00:00' WHERE recdate = '0000-00-00 00:00:00';

UPDATE mrpdemands SET duedate = '1000-01-01' WHERE duedate = '0000-00-00';

UPDATE offers SET expirydate = '1000-01-01' WHERE expirydate = '0000-00-00';

UPDATE periods SET lastdate_in_period = '1000-01-01' WHERE lastdate_in_period = '0000-00-00';

UPDATE pickinglists SET pickinglistdate = '1000-01-01' WHERE pickinglistdate = '0000-00-00';
UPDATE pickinglists SET dateprinted = '1000-01-01' WHERE dateprinted = '0000-00-00';
UPDATE pickinglists SET deliverynotedate = '1000-01-01' WHERE deliverynotedate = '0000-00-00';

UPDATE pickreq SET initdate = '1000-01-01' WHERE initdate = '0000-00-00';
UPDATE pickreq SET requestdate = '1000-01-01' WHERE requestdate = '0000-00-00';
UPDATE pickreq SET shipdate = '1000-01-01' WHERE shipdate = '0000-00-00';

UPDATE pricematrix SET startdate = '1000-01-01' WHERE startdate = '0000-00-00';

UPDATE prices SET startdate = '1000-01-01' WHERE startdate = '0000-00-00';

UPDATE purchorderdetails SET deliverydate = '1000-01-01' WHERE deliverydate = '0000-00-00';

UPDATE purchorders SET orddate = '1000-01-01 00:00:00' WHERE orddate = '0000-00-00 00:00:00';
UPDATE purchorders SET revised = '1000-01-01' WHERE revised = '0000-00-00';
UPDATE purchorders SET deliverydate = '1000-01-01' WHERE deliverydate = '0000-00-00';
UPDATE purchorders SET agreeddeliverydate = '1000-01-01' WHERE agreeddeliverydate = '0000-00-00';
UPDATE purchorders SET paymentdate = '1000-01-01' WHERE paymentdate = '0000-00-00';
UPDATE purchorders SET shipmentdate = '1000-01-01' WHERE shipmentdate = '0000-00-00';
UPDATE purchorders SET customsdate = '1000-01-01' WHERE customsdate = '0000-00-00';
UPDATE purchorders SET arrivaldate = '1000-01-01' WHERE arrivaldate = '0000-00-00';

UPDATE qasamples SET sampledate = '1000-01-01' WHERE sampledate = '0000-00-00';

UPDATE recurringsalesorders SET orddate = '1000-01-01' WHERE orddate = '0000-00-00';
UPDATE recurringsalesorders SET lastrecurrence = '1000-01-01' WHERE lastrecurrence = '0000-00-00';
UPDATE recurringsalesorders SET stopdate = '1000-01-01' WHERE stopdate = '0000-00-00';

UPDATE salesorderdetails SET actualdispatchdate = '1000-01-01 00:00:00' WHERE actualdispatchdate = '0000-00-00 00:00:00';

UPDATE salesorders SET orddate = '1000-01-01' WHERE orddate = '0000-00-00';
UPDATE salesorders SET deliverydate = '1000-01-01' WHERE deliverydate = '0000-00-00';
UPDATE salesorders SET confirmeddate = '1000-01-01' WHERE confirmeddate = '0000-00-00';
UPDATE salesorders SET datepackingslipprinted = '1000-01-01' WHERE datepackingslipprinted = '0000-00-00';
UPDATE salesorders SET quotedate = '1000-01-01' WHERE quotedate = '0000-00-00';
UPDATE salesorders SET klemailremindbanktransfer = '1000-01-01' WHERE klemailremindbanktransfer = '0000-00-00';
UPDATE salesorders SET klemailpaymentconfirm = '1000-01-01' WHERE klemailpaymentconfirm = '0000-00-00';
UPDATE salesorders SET klemailtrackingconfirm = '1000-01-01' WHERE klemailtrackingconfirm = '0000-00-00';
UPDATE salesorders SET klemailthankyouorder = '1000-01-01' WHERE klemailthankyouorder = '0000-00-00';

UPDATE sampleresults SET testdate = '1000-01-01' WHERE testdate = '0000-00-00';

UPDATE shipments SET eta = '1000-01-01 00:00:00' WHERE eta = '0000-00-00 00:00:00';

UPDATE stockcheckfreeze SET stockcheckdate = '1000-01-01' WHERE stockcheckdate = '0000-00-00';

UPDATE stockmaster SET lastcostupdate = '1000-01-01' WHERE lastcostupdate = '0000-00-00';

UPDATE stockmoves SET trandate = '1000-01-01' WHERE trandate = '0000-00-00';

UPDATE stockrequest SET despatchdate = '1000-01-01' WHERE despatchdate = '0000-00-00';

UPDATE stockserialitems SET expirationdate = '1000-01-01 00:00:00' WHERE expirationdate = '0000-00-00 00:00:00';

UPDATE suppallocs SET datealloc = '1000-01-01' WHERE datealloc = '0000-00-00';

UPDATE suppliers SET suppliersince = '1000-01-01' WHERE suppliersince = '0000-00-00';
UPDATE suppliers SET lastpaiddate = '1000-01-01 00:00:00' WHERE lastpaiddate = '0000-00-00 00:00:00';

UPDATE supptrans SET trandate = '1000-01-01' WHERE trandate = '0000-00-00';
UPDATE supptrans SET duedate = '1000-01-01' WHERE duedate = '0000-00-00';

UPDATE tenders SET requiredbydate = '1000-01-01 00:00:00' WHERE requiredbydate = '0000-00-00 00:00:00';

UPDATE timesheets SET weekending = '1000-01-01' WHERE weekending = '0000-00-00';

UPDATE workorders SET requiredby = '1000-01-01' WHERE requiredby = '0000-00-00';
UPDATE workorders SET startdate = '1000-01-01' WHERE startdate = '0000-00-00';

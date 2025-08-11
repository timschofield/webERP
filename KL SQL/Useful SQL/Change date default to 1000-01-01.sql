UPDATE klconsignment SET invoicedtopartner = '1000-01-01' WHERE invoicedtopartner = '0000-00-00';
UPDATE klconsignment SET fakturpajakdate = '1000-01-01' WHERE fakturpajakdate = '0000-00-00';

UPDATE klretailcustomers SET date_of_birth = '1000-01-01' WHERE date_of_birth = '0000-00-00';

UPDATE mrpsupplies SET duedate = '1000-01-01' WHERE duedate = '0000-00-00';
UPDATE mrpsupplies SET mrpdate = '1000-01-01' WHERE mrpdate = '0000-00-00';



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



-- Add ALTER TABLE statements to convert all dates defaulting to 0000-00-00 to 1000-01-01
ALTER TABLE audittrail MODIFY transactiondate datetime NOT NULL DEFAULT '1000-01-01 00:00:00';
ALTER TABLE contracts MODIFY requireddate date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE custallocns MODIFY datealloc date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE custnotes MODIFY date date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE debtorsmaster MODIFY clientsince datetime NOT NULL DEFAULT '1000-01-01 00:00:00';
ALTER TABLE debtorsmaster MODIFY klemailnowebshoporder date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE debtortrans MODIFY trandate datetime NOT NULL DEFAULT '1000-01-01 00:00:00';
ALTER TABLE debtortypenotes MODIFY date date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE deliverynotes MODIFY deliverydate date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE fixedassets MODIFY datepurchased date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE fixedassets MODIFY disposaldate date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE gltrans MODIFY trandate date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE grns MODIFY deliverydate date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE klconsignment MODIFY invoicedtopartner date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE klconsignment MODIFY fakturpajakdate date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE loctransfers MODIFY shipdate datetime NOT NULL DEFAULT '1000-01-01 00:00:00';
ALTER TABLE loctransfers MODIFY recdate datetime NOT NULL DEFAULT '1000-01-01 00:00:00';
ALTER TABLE mrpdemands MODIFY duedate date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE offers MODIFY expirydate date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE pcashdetails MODIFY authorized date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE periods MODIFY lastdate_in_period date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE pickreq MODIFY initdate date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE pickreq MODIFY requestdate date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE pickreq MODIFY shipdate date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE purchorderdetails MODIFY deliverydate date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE purchorders MODIFY orddate datetime NOT NULL DEFAULT '1000-01-01 00:00:00';
ALTER TABLE purchorders MODIFY revised date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE purchorders MODIFY agreeddeliverydate date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE purchorders MODIFY deliverydate date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE qasamples MODIFY sampledate date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE recurringsalesorders MODIFY orddate date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE recurringsalesorders MODIFY lastrecurrence date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE recurringsalesorders MODIFY stopdate date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE regularpayments MODIFY firstpayment date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE regularpayments MODIFY finalpayment date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE regularpayments MODIFY nextpayment date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE sampleresults MODIFY testdate date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE salesorders MODIFY orddate date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE salesorders MODIFY deliverydate date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE salesorders MODIFY datepackingslipprinted date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE salesorders MODIFY quotedate date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE salesorders MODIFY confirmeddate date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE salesorders MODIFY klemailremindbanktransfer date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE salesorders MODIFY klemailpaymentconfirm date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE salesorders MODIFY klemailtrackingconfirm date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE salesorders MODIFY klemailthankyouorder date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE shipments MODIFY eta datetime NOT NULL DEFAULT '1000-01-01 00:00:00';
ALTER TABLE stockcheckfreeze MODIFY stockcheckdate date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE stockmaster MODIFY lastcostupdate date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE stockmoves MODIFY trandate date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE stockrequest MODIFY despatchdate date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE stockserialitems MODIFY expirationdate datetime NOT NULL DEFAULT '1000-01-01 00:00:00';
ALTER TABLE suppallocs MODIFY datealloc date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE suppliers MODIFY suppliersince date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE supptrans MODIFY trandate date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE supptrans MODIFY duedate date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE tenders MODIFY requiredbydate datetime NOT NULL DEFAULT '1000-01-01 00:00:00';
ALTER TABLE timesheets MODIFY weekending date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE workorders MODIFY requiredby date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE workorders MODIFY startdate date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE salariescalculated MODIFY joiningdate date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE salariescalculated MODIFY salaryfrom date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE salariescalculated MODIFY salaryto date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE salariescalculated MODIFY paymentday varchar(30) NOT NULL DEFAULT '1000-01-01';



UPDATE audittrail SET transactiondate = '1000-01-01 00:00:00' WHERE transactiondate = '0000-00-00 00:00:00';
ALTER TABLE audittrail MODIFY transactiondate datetime NOT NULL DEFAULT '1000-01-01 00:00:00';

UPDATE contracts SET requireddate = '1000-01-01' WHERE requireddate = '0000-00-00';
ALTER TABLE contracts MODIFY requireddate date NOT NULL DEFAULT '1000-01-01';

UPDATE custallocns SET datealloc = '1000-01-01' WHERE datealloc = '0000-00-00';
ALTER TABLE custallocns MODIFY datealloc date NOT NULL DEFAULT '1000-01-01';

UPDATE custnotes SET date = '1000-01-01' WHERE date = '0000-00-00';
ALTER TABLE custnotes MODIFY date date NOT NULL DEFAULT '1000-01-01';

UPDATE debtorsmaster SET clientsince = '1000-01-01 00:00:00' WHERE clientsince = '0000-00-00 00:00:00';
UPDATE debtorsmaster SET klemailnowebshoporder = '1000-01-01' WHERE klemailnowebshoporder = '0000-00-00';
ALTER TABLE debtorsmaster MODIFY clientsince datetime NOT NULL DEFAULT '1000-01-01 00:00:00';
ALTER TABLE debtorsmaster MODIFY klemailnowebshoporder date NOT NULL DEFAULT '1000-01-01';

UPDATE debtortrans SET trandate = '1000-01-01 00:00:00' WHERE trandate = '0000-00-00 00:00:00';
ALTER TABLE debtortrans MODIFY trandate datetime NOT NULL DEFAULT '1000-01-01 00:00:00';

UPDATE debtortypenotes SET date = '1000-01-01' WHERE date = '0000-00-00';
ALTER TABLE debtortypenotes MODIFY date date NOT NULL DEFAULT '1000-01-01';

UPDATE deliverynotes SET deliverydate = '1000-01-01' WHERE deliverydate = '0000-00-00';
ALTER TABLE deliverynotes MODIFY deliverydate date NOT NULL DEFAULT '1000-01-01';

UPDATE fixedassets SET datepurchased = '1000-01-01' WHERE datepurchased = '0000-00-00';
UPDATE fixedassets SET disposaldate = '1000-01-01' WHERE disposaldate = '0000-00-00';
ALTER TABLE fixedassets MODIFY datepurchased date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE fixedassets MODIFY disposaldate date NOT NULL DEFAULT '1000-01-01';

UPDATE gltrans SET trandate = '1000-01-01' WHERE trandate = '0000-00-00';
ALTER TABLE gltrans MODIFY trandate date NOT NULL DEFAULT '1000-01-01';

UPDATE grns SET deliverydate = '1000-01-01' WHERE deliverydate = '0000-00-00';
ALTER TABLE grns MODIFY deliverydate date NOT NULL DEFAULT '1000-01-01';

UPDATE klconsignment SET invoicedtopartner = '1000-01-01' WHERE invoicedtopartner = '0000-00-00';
UPDATE klconsignment SET fakturpajakdate = '1000-01-01' WHERE fakturpajakdate = '0000-00-00';
ALTER TABLE klconsignment MODIFY invoicedtopartner date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE klconsignment MODIFY fakturpajakdate date NOT NULL DEFAULT '1000-01-01';

UPDATE loctransfers SET shipdate = '1000-01-01 00:00:00' WHERE shipdate = '0000-00-00 00:00:00';
UPDATE loctransfers SET recdate = '1000-01-01 00:00:00' WHERE recdate = '0000-00-00 00:00:00';
ALTER TABLE loctransfers MODIFY shipdate datetime NOT NULL DEFAULT '1000-01-01 00:00:00';
ALTER TABLE loctransfers MODIFY recdate datetime NOT NULL DEFAULT '1000-01-01 00:00:00';

UPDATE mrpdemands SET duedate = '1000-01-01' WHERE duedate = '0000-00-00';
ALTER TABLE mrpdemands MODIFY duedate date NOT NULL DEFAULT '1000-01-01';

UPDATE offers SET expirydate = '1000-01-01' WHERE expirydate = '0000-00-00';
ALTER TABLE offers MODIFY expirydate date NOT NULL DEFAULT '1000-01-01';

UPDATE pcashdetails SET authorized = '1000-01-01' WHERE authorized = '0000-00-00';
ALTER TABLE pcashdetails MODIFY authorized date NOT NULL DEFAULT '1000-01-01';

UPDATE periods SET lastdate_in_period = '1000-01-01' WHERE lastdate_in_period = '0000-00-00';
ALTER TABLE periods MODIFY lastdate_in_period date NOT NULL DEFAULT '1000-01-01';

UPDATE pickreq SET initdate = '1000-01-01' WHERE initdate = '0000-00-00';
UPDATE pickreq SET requestdate = '1000-01-01' WHERE requestdate = '0000-00-00';
UPDATE pickreq SET shipdate = '1000-01-01' WHERE shipdate = '0000-00-00';
ALTER TABLE pickreq MODIFY initdate date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE pickreq MODIFY requestdate date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE pickreq MODIFY shipdate date NOT NULL DEFAULT '1000-01-01';

UPDATE purchorderdetails SET deliverydate = '1000-01-01' WHERE deliverydate = '0000-00-00';
ALTER TABLE purchorderdetails MODIFY deliverydate date NOT NULL DEFAULT '1000-01-01';

UPDATE purchorders SET orddate = '1000-01-01 00:00:00' WHERE orddate = '0000-00-00 00:00:00';
UPDATE purchorders SET revised = '1000-01-01' WHERE revised = '0000-00-00';
UPDATE purchorders SET agreeddeliverydate = '1000-01-01' WHERE agreeddeliverydate = '0000-00-00';
UPDATE purchorders SET deliverydate = '1000-01-01' WHERE deliverydate = '0000-00-00';
ALTER TABLE purchorders MODIFY orddate datetime NOT NULL DEFAULT '1000-01-01 00:00:00';
ALTER TABLE purchorders MODIFY revised date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE purchorders MODIFY agreeddeliverydate date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE purchorders MODIFY deliverydate date NOT NULL DEFAULT '1000-01-01';

UPDATE qasamples SET sampledate = '1000-01-01' WHERE sampledate = '0000-00-00';
ALTER TABLE qasamples MODIFY sampledate date NOT NULL DEFAULT '1000-01-01';

UPDATE recurringsalesorders SET orddate = '1000-01-01' WHERE orddate = '0000-00-00';
UPDATE recurringsalesorders SET lastrecurrence = '1000-01-01' WHERE lastrecurrence = '0000-00-00';
UPDATE recurringsalesorders SET stopdate = '1000-01-01' WHERE stopdate = '0000-00-00';
ALTER TABLE recurringsalesorders MODIFY orddate date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE recurringsalesorders MODIFY lastrecurrence date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE recurringsalesorders MODIFY stopdate date NOT NULL DEFAULT '1000-01-01';

UPDATE regularpayments SET firstpayment = '1000-01-01' WHERE firstpayment = '0000-00-00';
UPDATE regularpayments SET finalpayment = '1000-01-01' WHERE finalpayment = '0000-00-00';
UPDATE regularpayments SET nextpayment = '1000-01-01' WHERE nextpayment = '0000-00-00';
ALTER TABLE regularpayments MODIFY firstpayment date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE regularpayments MODIFY finalpayment date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE regularpayments MODIFY nextpayment date NOT NULL DEFAULT '1000-01-01';

UPDATE sampleresults SET testdate = '1000-01-01' WHERE testdate = '0000-00-00';
ALTER TABLE sampleresults MODIFY testdate date NOT NULL DEFAULT '1000-01-01';

UPDATE salesorders SET orddate = '1000-01-01' WHERE orddate = '0000-00-00';
UPDATE salesorders SET deliverydate = '1000-01-01' WHERE deliverydate = '0000-00-00';
UPDATE salesorders SET datepackingslipprinted = '1000-01-01' WHERE datepackingslipprinted = '0000-00-00';
UPDATE salesorders SET quotedate = '1000-01-01' WHERE quotedate = '0000-00-00';
UPDATE salesorders SET confirmeddate = '1000-01-01' WHERE confirmeddate = '0000-00-00';
UPDATE salesorders SET klemailremindbanktransfer = '1000-01-01' WHERE klemailremindbanktransfer = '0000-00-00';
UPDATE salesorders SET klemailpaymentconfirm = '1000-01-01' WHERE klemailpaymentconfirm = '0000-00-00';
UPDATE salesorders SET klemailtrackingconfirm = '1000-01-01' WHERE klemailtrackingconfirm = '0000-00-00';
UPDATE salesorders SET klemailthankyouorder = '1000-01-01' WHERE klemailthankyouorder = '0000-00-00';
ALTER TABLE salesorders MODIFY orddate date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE salesorders MODIFY deliverydate date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE salesorders MODIFY datepackingslipprinted date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE salesorders MODIFY quotedate date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE salesorders MODIFY confirmeddate date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE salesorders MODIFY klemailremindbanktransfer date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE salesorders MODIFY klemailpaymentconfirm date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE salesorders MODIFY klemailtrackingconfirm date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE salesorders MODIFY klemailthankyouorder date NOT NULL DEFAULT '1000-01-01';

UPDATE shipments SET eta = '1000-01-01 00:00:00' WHERE eta = '0000-00-00 00:00:00';
ALTER TABLE shipments MODIFY eta datetime NOT NULL DEFAULT '1000-01-01 00:00:00';

UPDATE stockcheckfreeze SET stockcheckdate = '1000-01-01' WHERE stockcheckdate = '0000-00-00';
ALTER TABLE stockcheckfreeze MODIFY stockcheckdate date NOT NULL DEFAULT '1000-01-01';

UPDATE stockmaster SET lastcostupdate = '1000-01-01' WHERE lastcostupdate = '0000-00-00';
ALTER TABLE stockmaster MODIFY lastcostupdate date NOT NULL DEFAULT '1000-01-01';

UPDATE stockmoves SET trandate = '1000-01-01' WHERE trandate = '0000-00-00';
ALTER TABLE stockmoves MODIFY trandate date NOT NULL DEFAULT '1000-01-01';

UPDATE stockrequest SET despatchdate = '1000-01-01' WHERE despatchdate = '0000-00-00';
ALTER TABLE stockrequest MODIFY despatchdate date NOT NULL DEFAULT '1000-01-01';

UPDATE stockserialitems SET expirationdate = '1000-01-01 00:00:00' WHERE expirationdate = '0000-00-00 00:00:00';
ALTER TABLE stockserialitems MODIFY expirationdate datetime NOT NULL DEFAULT '1000-01-01 00:00:00';

UPDATE suppallocs SET datealloc = '1000-01-01' WHERE datealloc = '0000-00-00';
ALTER TABLE suppallocs MODIFY datealloc date NOT NULL DEFAULT '1000-01-01';

UPDATE suppliers SET suppliersince = '1000-01-01' WHERE suppliersince = '0000-00-00';
ALTER TABLE suppliers MODIFY suppliersince date NOT NULL DEFAULT '1000-01-01';

UPDATE supptrans SET trandate = '1000-01-01' WHERE trandate = '0000-00-00';
UPDATE supptrans SET duedate = '1000-01-01' WHERE duedate = '0000-00-00';
ALTER TABLE supptrans MODIFY trandate date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE supptrans MODIFY duedate date NOT NULL DEFAULT '1000-01-01';

UPDATE tenders SET requiredbydate = '1000-01-01 00:00:00' WHERE requiredbydate = '0000-00-00 00:00:00';
ALTER TABLE tenders MODIFY requiredbydate datetime NOT NULL DEFAULT '1000-01-01 00:00:00';

UPDATE timesheets SET weekending = '1000-01-01' WHERE weekending = '0000-00-00';
ALTER TABLE timesheets MODIFY weekending date NOT NULL DEFAULT '1000-01-01';

UPDATE workorders SET requiredby = '1000-01-01' WHERE requiredby = '0000-00-00';
UPDATE workorders SET startdate = '1000-01-01' WHERE startdate = '0000-00-00';
ALTER TABLE workorders MODIFY requiredby date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE workorders MODIFY startdate date NOT NULL DEFAULT '1000-01-01';

UPDATE salariescalculated SET joiningdate = '1000-01-01' WHERE joiningdate = '0000-00-00';
UPDATE salariescalculated SET salaryfrom = '1000-01-01' WHERE salaryfrom = '0000-00-00';
UPDATE salariescalculated SET salaryto = '1000-01-01' WHERE salaryto = '0000-00-00';
UPDATE salariescalculated SET paymentday = '1000-01-01' WHERE paymentday = '0000-00-00';
ALTER TABLE salariescalculated MODIFY joiningdate date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE salariescalculated MODIFY salaryfrom date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE salariescalculated MODIFY salaryto date NOT NULL DEFAULT '1000-01-01';
ALTER TABLE salariescalculated MODIFY paymentday varchar(30) NOT NULL;

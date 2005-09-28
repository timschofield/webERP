ALTER TABLE debtorsmaster ADD COLUMN address5 VARCHAR( 20 );
 ALTER TABLE debtorsmaster ADD COLUMN address6 VARCHAR( 15 );
ALTER TABLE custbranch ADD COLUMN braddress5 VARCHAR( 20 );
ALTER TABLE custbranch  ADD COLUMN braddress6 VARCHAR( 15 );
ALTER TABLE custbranch ADD COLUMN brpostaddr5 VARCHAR( 20 ); 
ALTER TABLE custbranch ADD COLUMN brpostaddr6 VARCHAR( 15 );
UPDATE custbranch SET braddress5='', braddress6='', brpostaddr5='', brpostaddr6='';
UPDATE debtorsmaster SET address5='', address6='';
ALTER TABLE custbranch ALTER COLUMN braddress5 SET NOT NULL;
ALTER TABLE custbranch ALTER COLUMN braddress6 SET NOT NULL;
ALTER TABLE custbranch ALTER COLUMN brpostaddr5 SET NOT NULL;
ALTER TABLE custbranch ALTER COLUMN brpostaddr6 SET NOT NULL;
ALTER TABLE debtorsmaster ALTER COLUMN address5 SET NOT NULL;
ALTER TABLE debtorsmaster ALTER COLUMN address6 SET NOT NULL;
ALTER TABLE custbranch ALTER COLUMN braddress5  SET DEFAULT ''::text;
ALTER TABLE custbranch ALTER COLUMN braddress6  SET DEFAULT ''::text;
ALTER TABLE custbranch ALTER COLUMN brpostaddr5  SET DEFAULT ''::text;
ALTER TABLE custbranch ALTER COLUMN brpostaddr6  SET DEFAULT ''::text;
ALTER TABLE debtorsmaster ALTER COLUMN address5  SET DEFAULT ''::text;
ALTER TABLE debtorsmaster ALTER COLUMN address6  SET DEFAULT ''::text;


ALTER TABLE locations ADD COLUMN deladd4 VARCHAR( 40 );
ALTER TABLE locations ALTER  COLUMN deladd4 SET NOT NULL
ALTER TABLE locations ALTER  COLUMN deladd4 SET  DEFAULT ''::text;
ALTER TABLE locations ADD COLUMN deladd5 VARCHAR( 20 );
ALTER TABLE locations ALTER  COLUMN deladd5 SET NOT NULL
ALTER TABLE locations ALTER  COLUMN deladd5 SET  DEFAULT ''::text;
ALTER TABLE locations ADD COLUMN deladd6 VARCHAR( 15 );
ALTER TABLE locations ALTER  COLUMN deladd6 SET NOT NULL
ALTER TABLE locations ALTER  COLUMN deladd6 SET  DEFAULT ''::text;

ALTER TABLE purchorders ADD COLUMN deladd5 VARCHAR( 20 );
ALTER TABLE purchorders ALTER  COLUMN deladd5 SET NOT NULL
ALTER TABLE purchorders ALTER  COLUMN deladd5 SET  DEFAULT ''::text;
ALTER TABLE purchorders ADD COLUMN deladd6 VARCHAR( 15 );
ALTER TABLE purchorders ALTER  COLUMN deladd6 SET NOT NULL
ALTER TABLE purchorders ALTER  COLUMN deladd6 SET  DEFAULT ''::text;
ALTER TABLE purchorders ADD COLUMN contact VARCHAR( 30 );
ALTER TABLE purchorders ALTER  COLUMN contact SET NOT NULL
ALTER TABLE purchorders ALTER  COLUMN contact SET  DEFAULT ''::text;

ALTER TABLE recurringsalesorders ADD COLUMN deladd5 VARCHAR( 20 );
ALTER TABLE recurringsalesorders ALTER  COLUMN deladd5 SET NOT NULL
ALTER TABLE recurringsalesorders ALTER  COLUMN deladd5 SET  DEFAULT ''::text;
ALTER TABLE recurringsalesorders ADD COLUMN deladd6 VARCHAR( 15 );
ALTER TABLE recurringsalesorders ALTER  COLUMN deladd6 SET NOT NULL
ALTER TABLE recurringsalesorders ALTER  COLUMN deladd6 SET  DEFAULT ''::text;
ALTER TABLE recurringsalesorders ALTER  COLUMN deladd2 varchar(40);
ALTER TABLE recurringsalesorders ALTER  COLUMN deladd3 varchar(40);
ALTER TABLE recurringsalesorders ALTER  COLUMN deladd4 varchar(40);
ALTER TABLE salesorders ADD COLUMN deladd5 VARCHAR( 20 );
ALTER TABLE salesorders ALTER  COLUMN deladd5 SET NOT NULL
ALTER TABLE salesorders ALTER  COLUMN deladd5 SET  DEFAULT ''::text;
ALTER TABLE salesorders ADD COLUMN deladd6 VARCHAR( 20 );
ALTER TABLE salesorders ALTER  COLUMN deladd6 SET NOT NULL
ALTER TABLE salesorders ALTER  COLUMN deladd6 SET  DEFAULT ''::text;
ALTER TABLE salesorders ALTER  COLUMN deladd2 varchar(40);
ALTER TABLE salesorders ALTER  COLUMN deladd3 varchar(40);
ALTER TABLE salesorders ALTER  COLUMN deladd4 varchar(40);
ALTER TABLE suppliers ADD COLUMN address5 VARCHAR( 20 );
ALTER TABLE suppliers ALTER  COLUMN address5 SET NOT NULL
ALTER TABLE suppliers ALTER  COLUMN address5 SET  DEFAULT ''::text;
ALTER TABLE suppliers ADD COLUMN address6 VARCHAR( 20 );
ALTER TABLE suppliers ALTER  COLUMN address6 SET NOT NULL
ALTER TABLE suppliers ALTER  COLUMN address6 SET  DEFAULT ''::text;
ALTER TABLE companies  ALTER COLUMN regoffice3 regoffice4 VARCHAR( 40 );
ALTER TABLE companies  ALTER COLUMN regoffice2 regoffice3 VARCHAR( 40 );
ALTER TABLE companies  ALTER COLUMN regoffice1 regoffice2 VARCHAR( 40 );
ALTER TABLE companies  ALTER COLUMN postaladdress regoffice1 VARCHAR( 40 );
ALTER TABLE companies ADD COLUMN regoffice5 VARCHAR( 20 );
ALTER TABLE companies ALTER  COLUMN regoffice5 SET NOT NULL
ALTER TABLE companies ALTER  COLUMN regoffice5 SET  DEFAULT ''::text;
ALTER TABLE companies ADD COLUMN regoffice6 VARCHAR( 15 );
ALTER TABLE companies ALTER  COLUMN regoffice6 SET NOT NULL
ALTER TABLE companies ALTER  COLUMN regoffice6 SET  DEFAULT ''::text;

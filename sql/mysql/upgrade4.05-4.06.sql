ALTER TABLE stockmaster DROP lastcurcostdate;
ALTER TABLE stockmaster ADD lastcostupdate DATE NOT NULL;
UPDATE config SET confvalue='4.05.1' WHERE confname='VersionNumber'; 
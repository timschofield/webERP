INSERT INTO scripts VALUES ('Z_DeleteOldPrices.php','15','Deletes all old prices');
INSERT INTO scripts VALUES ('Z_ChangeLocationCode.php','15','Change a locations code and in all tables where the old code was used to the new code')
UPDATE config SET confvalue='4.08.1' WHERE confname='VersionNumber';


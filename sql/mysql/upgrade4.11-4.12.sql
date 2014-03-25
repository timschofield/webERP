INSERT INTO  `systypes` (`typeid` ,`typename` ,`typeno`) VALUES ('600',  'Auto Supplier Number',  '0');
INSERT INTO config (confname, confvalue) VALUES ('AutoSupplierNo', '0');
DELETE FROM config WHERE confname='DefaultTheme';
UPDATE config SET confvalue='4.12' WHERE confname='VersionNumber';


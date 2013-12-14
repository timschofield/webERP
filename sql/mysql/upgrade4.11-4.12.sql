ALTER TABLE  `emailsettings` CHANGE  `username`  `username` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL
UPDATE config SET confvalue='4.12' WHERE confname='VersionNumber';

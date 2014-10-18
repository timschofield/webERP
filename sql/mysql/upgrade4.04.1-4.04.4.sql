ALTER TABLE  `salesman` ADD  `current` TINYINT( 4 ) NOT NULL COMMENT  'Salesman current (1) or not (0)';
UPDATE`salesman` SET `current` = 1;
UPDATE config SET confvalue='4.04.4' WHERE confname='VersionNumber';

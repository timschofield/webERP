use weberp;

BEGIN;
DROP TABLE WORequirements;
DROP TABLE WOIssues;
ALTER TABLE `WorksOrders` ADD `UnitsRecd` DOUBLE DEFAULT '0' NOT NULL AFTER `UnitsReqd` ;

COMMIT;

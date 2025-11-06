
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

ALTER TABLE `loctransfers` ADD `reason` VARCHAR(20) NULL AFTER `pendingqty`;

UPDATE `klservicetypes`
SET `servicecode` = CONCAT('SERV_', `servicecode`)
WHERE `servicecode` NOT LIKE 'SERV_%';

COMMIT;

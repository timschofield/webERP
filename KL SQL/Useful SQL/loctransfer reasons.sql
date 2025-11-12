
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

ALTER TABLE `loctransfers` ADD `reason` VARCHAR(20) NULL AFTER `pendingqty`;

UPDATE `klservicetypes`
SET `servicecode` = CONCAT('SERV_', `servicecode`)
WHERE `servicecode` NOT LIKE 'SERV_%';

ALTER TABLE `klkpidescriptions` CHANGE `kpicode` `kpicode` VARCHAR(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
ALTER TABLE `klkpi` CHANGE `kpicode` `kpicode` VARCHAR(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;

INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ('TRANS-DISPATCH_OVERSTOCK-30-PCS', 'Manual Dispatch Overstock');
INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ('TRANS-DISPATCH_NEEDED_BY_RL-30-PCS', 'Manual Dispatch Needed by Reorder Level');
INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ('TRANS-SMART_NEEDED_BY_RL-30-PCS', 'Daily Cron Job From Kantor Needed in Shop by Reorder Level');
INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ('TRANS-SMART_RETURN_OVERSTOCK-30-PCS', 'Daily Cron Job Return Overstock from Shop to Kantor');
INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ('TRANS-SERV__LAINLAIN-30-PCS', 'Servis Lain Lain');
INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ('TRANS-SERV_BENTUKBENGKOK-30-PCS', 'Bentuk perak bengkok');
INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ('TRANS-SERV_CRYSTALLEPAS-30-PCS', 'Crystal / sircon lepas');
INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ('TRANS-SERV_BERUBAHWARNA-30-PCS', 'Item Berubah Warna');
INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ('TRANS-SERV_KARETLONGGAR-30-PCS', 'Karet / Elastis longgar');
INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ('TRANS-SERV_KOMPONENLEPAS-30-PCS', 'Komponen lepas');
INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ('TRANS-SERV_KOMPONENPECAH-30-PCS', 'Komponen pecah');
INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ('TRANS-SERV_KOMPONENRUSAK-30-PCS', 'Komponen rusak');
INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ('TRANS-SERV_PATRI-30-PCS', 'Las / Patri');
INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ('TRANS-SERV_LOCKRUSAK-30-PCS', 'Lock (lobster) Rusak');
INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ('TRANS-SERV_MAGNETLEPAS-30-PCS', 'Magnet Lepas');
INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ('TRANS-SERV_PERAKKOTOR-30-PCS', 'Perak kotor');
INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ('TRANS-SERV_KURANGSHINNY-30-PCS', 'Perak kurang shinny');
INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ('TRANS-SERV_RANTAIPUTUS-30-PCS', 'Rantai perak putus');
INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ('TRANS-SERV_TALILUSUH-30-PCS', 'Tali Putus/Rusak');
INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ('TRANS-SERV_WIREBERKARAT-30-PCS', 'Wire berkarat');
INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ('TRANS-REQUESTED_SS-30-PCS', 'Requested by Shop Support Team');
INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ('TRANS-OTHERS_SPG-30-PCS', 'Return from shop by any other reason');
INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ('TRANS-TOTAL-30-PCS', 'Total items transferred in last 30 days');

UPDATE `klservicetypes` SET `servicedescription` = 'Barang kotor' WHERE `klservicetypes`.`servicedescription` = 'Perak kotor';
UPDATE `klservicetypes` SET `servicecode` = 'SERV_KOTOR' WHERE `klservicetypes`.`servicedescription` = 'Barang kotor';
UPDATE `klkpidescriptions` SET `kpicode` = 'TRANS-SERV_BARANGKOTOR-30-PCS', `kpidescription` = 'Barang kotor' WHERE `klkpidescriptions`.`kpicode` = 'TRANS-SERV_PERAKKOTOR-30-PCS'

INSERT INTO `klservicetypes` (`servicecode`, `servicedescription`, `pricetier01`, `pricetier02`, `pricetier03`) VALUES ('SERV_PRICETAGRUSAK', 'Pricetag rusak', '50000.0000', '75000.0000', '100000.0000');
INSERT INTO `klkpidescriptions` (`kpicode`, `kpidescription`) VALUES ('TRANS-SERV_PRICETAGRUSAK-30-PCS', 'Pricetag rusak');

COMMIT;

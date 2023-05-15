ALTER TABLE `salesorders` DROP PRIMARY KEY, ADD PRIMARY KEY (`orderno`, `orddate`) USING BTREE;
ALTER TABLE `salesorders` DROP INDEX `DebtorNo+OrderNo`, ADD UNIQUE `DebtorNo+OrderNo` (`debtorno`, `orderno`, `orddate`) USING BTREE;
ALTER TABLE `salesorders` DROP INDEX `OpenCartStatus`, ADD UNIQUE `OpenCartStatus` (`klocorderstatus`, `orderno`, `orddate`) USING BTREE;


ALTER TABLE salesorders PARTITION BY RANGE COLUMNS (orddate) (
    PARTITION salesorders_2010 VALUES  LESS THAN ('2011-01-01'),
    PARTITION salesorders_2011 VALUES  LESS THAN ('2012-01-01'),
    PARTITION salesorders_2012 VALUES  LESS THAN ('2013-01-01'),
    PARTITION salesorders_2013 VALUES  LESS THAN ('2014-01-01'),
    PARTITION salesorders_2014 VALUES  LESS THAN ('2015-01-01'),
    PARTITION salesorders_2015 VALUES  LESS THAN ('2016-01-01'),
    PARTITION salesorders_2016 VALUES  LESS THAN ('2017-01-01'),
    PARTITION salesorders_2017 VALUES  LESS THAN ('2018-01-01'),
    PARTITION salesorders_2018 VALUES  LESS THAN ('2019-01-01'),
    PARTITION salesorders_2019 VALUES  LESS THAN ('2020-01-01'),
    PARTITION salesorders_2020 VALUES  LESS THAN ('2021-01-01'),
    PARTITION salesorders_2021 VALUES  LESS THAN ('2022-01-01'),
    PARTITION salesorders_2022 VALUES  LESS THAN ('2023-01-01'),
    PARTITION salesorders_2023 VALUES  LESS THAN ('2024-01-01'),
    PARTITION salesorders_2024 VALUES  LESS THAN ('2025-01-01'),
    PARTITION salesorders_2025 VALUES  LESS THAN ('2026-01-01'),
    PARTITION salesorders_2026 VALUES  LESS THAN ('2027-01-01'),
    PARTITION salesorders_2099 VALUES LESS THAN (MAXVALUE)
);
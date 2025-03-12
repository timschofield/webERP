
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `stockadjustmentreasons` (
  `reasonid` tinyint(4) NOT NULL,
  `reasonname` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `stockadjustmentreasons` (`reasonid`, `reasonname`) VALUES
(1, 'Item broken from shop'),
(2, 'Item broken from office'),
(3, 'Returned goods'),
(4, 'Stolen by customer'),
(5, 'Change of size'),
(6, 'Inventory Adjustment'),
(7, 'Supplier rejected items - credit noted'),
(8, 'Gift KL - Giveaway'),
(9, '_Others');


ALTER TABLE `stockadjustmentreasons`
  ADD PRIMARY KEY (`reasonid`);

ALTER TABLE `stockadjustmentreasons`
  MODIFY `reasonid` tinyint(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
COMMIT;

CREATE TABLE `stockadjustments` (
  `transno` int(11) NOT NULL,
  `reasonid` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `stockadjustments`
  ADD PRIMARY KEY (`transno`);
  
ALTER TABLE `stockadjustments`
  ADD KEY (`reasonid`);
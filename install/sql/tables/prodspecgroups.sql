CREATE TABLE `prodspecgroups` (
  `groupid` smallint(6) NOT NULL,
  `groupname` char(50) DEFAULT NULL,
  `groupbyNo` int(11) NOT NULL DEFAULT 1,
  `headertitle` varchar(100) DEFAULT NULL,
  `trailertext` varchar(240) DEFAULT NULL,
  `labels` varchar(240) NOT NULL,
  `numcols` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_general_ci;

ALTER TABLE `prodspecgroups`
  ADD PRIMARY KEY (`groupid`),
  ADD UNIQUE KEY `groupname` (`groupname`),
  ADD KEY `groupbyNo` (`groupbyNo`);

ALTER TABLE `prodspecgroups`
  MODIFY `groupid` smallint(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

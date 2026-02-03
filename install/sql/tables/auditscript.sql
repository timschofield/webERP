CREATE TABLE `auditscripts` (
  `executiondate` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `secondsrunning` decimal(10,5) NOT NULL DEFAULT 0.00000,
  `userid` varchar(20) NOT NULL DEFAULT '',
  `scripttitle` varchar(200) NOT NULL DEFAULT '',
  KEY `idx_auditscripts_userid` (`userid`),
  KEY `idx_auditscripts_executiondate` (`executiondate`),
  KEY `idx_auditscripts_scripttitle` (`scripttitle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

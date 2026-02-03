<?php

CreateTable('auditscript',
"CREATE TABLE `auditscripts` (
  `executiondate` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `secondsrunning` decimal(10,5) NOT NULL DEFAULT 0.00000,
  `userid` varchar(20) NOT NULL DEFAULT '',
  `scripttitle` varchar(200) NOT NULL DEFAULT '',
  KEY `idx_auditscripts_userid` (`userid`),
  KEY `idx_auditscripts_executiondate` (`executiondate`),
  KEY `idx_auditscripts_scripttitle` (`scripttitle`)
)");

NewMenuItem('system', 'Transactions', __('Scripts Audit Trail'), '/AuditScripts.php', 15);

UpdateDBNo(basename(__FILE__, '.php'), __('Create auditscripts table to log automated script executions'));

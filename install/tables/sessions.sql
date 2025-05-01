CREATE TABLE `sessions` (
  `sessionid` char(255) DEFAULT NULL,
  `last_poll` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

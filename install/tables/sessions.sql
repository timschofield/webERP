CREATE TABLE `sessions` (
  `sessionid` char(26) DEFAULT NULL,
  `last_poll` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

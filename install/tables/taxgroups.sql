CREATE TABLE `taxgroups` (
  `taxgroupid` tinyint NOT NULL AUTO_INCREMENT,
  `taxgroupdescription` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`taxgroupid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

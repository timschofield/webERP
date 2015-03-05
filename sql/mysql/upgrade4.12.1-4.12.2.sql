-- webERP 4.12.2.
-- Release date: 2015-02-06.
--

CREATE TABLE IF NOT EXISTS `locationusers` (
  `loccode` varchar(5) NOT NULL,
  `userid` varchar(20) NOT NULL,
  `canview` tinyint(4) NOT NULL DEFAULT '0',
  `canupd` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`loccode`,`userid`),
  KEY `UserId` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `locationusers` (`loccode`, `userid`, `canview`, `canupd`) SELECT loccode, userid,1,1 FROM locations, www_users;
INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('LocationUsers.php', '15', 'Allows users that have permission to access a location to be defined');

-- Update version number:
UPDATE config SET confvalue='4.12.2' WHERE confname='VersionNumber';

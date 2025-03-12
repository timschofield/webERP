CREATE TABLE `favourites` (
  `userid` varchar(20) NOT NULL DEFAULT '',
  `caption` varchar(50) NOT NULL DEFAULT '',
  `href` varchar(200) NOT NULL DEFAULT '#',
  PRIMARY KEY (`userid`,`caption`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

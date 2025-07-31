CREATE TABLE `pcashdetails` (
  `counterindex` int NOT NULL AUTO_INCREMENT,
  `tabcode` varchar(20) NOT NULL,
  `date` date NOT NULL,
  `codeexpense` varchar(20) NOT NULL,
  `amount` double NOT NULL,
  `authorized` date NOT NULL COMMENT 'date cash assigment was revised and authorized by authorizer from tabs table',
  `posted` tinyint NOT NULL COMMENT 'has (or has not) been posted into gltrans',
  `purpose` text DEFAULT NULL,
  `notes` text NOT NULL,
  `receipt` text DEFAULT NULL COMMENT 'Column redundant. Replaced by receipt file upload. Nov 2017.',
  PRIMARY KEY (`counterindex`),
  UNIQUE KEY `tabcodedate` (`tabcode`,`date`,`codeexpense`,`counterindex`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `hrapplicantactivities` (
  `activityid` int(11) NOT NULL AUTO_INCREMENT,
  `applicantid` int(11) NOT NULL,
  `activitydate` date NOT NULL,
  `activitytype` varchar(50) NOT NULL,
  `description` text,
  `interviewer` varchar(100) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL,
  `nextsteps` text,
  `createdby` varchar(20) NOT NULL,
  `createddate` datetime NOT NULL,
  PRIMARY KEY (`activityid`),
  KEY `applicantid` (`applicantid`),
  KEY `activitydate` (`activitydate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci

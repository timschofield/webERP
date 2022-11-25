CREATE TABLE `klmaintenancetypes` (
  `maintenancetype` varchar(20) NOT NULL COMMENT 'code for the type',
  `description` varchar(50) NOT NULL COMMENT 'text description'
  `closer` varchar(200) DEFAULT NULL,
  `verifier` varchar(200) DEFAULT NULL,
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `klmaintenancetasks` (
  `counterindex` int(20) NOT NULL,
  `loccode` varchar(5) NOT NULL,
  `maintenancetype` varchar(20) NOT NULL,
  `status` varchar(20) NOT NULL,
  `creationuser`varchar(20) NOT NULL,
  `creationdate` date NOT NULL,
  `creationdescription` text NOT NULL,
  `closinguser`varchar(20) NOT NULL,
  `closingdate` date NULL,
  `closingdescription` text NULL,
  `verificationuser`varchar(20) NOT NULL,
  `verificationdate` date NULL,
  `verificationdescription` text NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
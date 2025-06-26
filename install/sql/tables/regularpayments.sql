CREATE TABLE `regularpayments` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `frequency` char(1) NOT NULL DEFAULT 'M',
  `days` tinyint NOT NULL DEFAULT '0',
  `glcode` varchar(20) NOT NULL DEFAULT '1',
  `bankaccountcode` varchar(20) NOT NULL DEFAULT '0',
  `tag` varchar(255) NOT NULL DEFAULT '',
  `amount` double NOT NULL DEFAULT '0',
  `currabrev` char(3) NOT NULL DEFAULT '',
  `narrative` varchar(255) DEFAULT '',
  `firstpayment` date NOT NULL DEFAULT '1001-01-01',
  `finalpayment` date NOT NULL DEFAULT '1001-01-01',
  `nextpayment` date NOT NULL DEFAULT '1001-01-01',
  `completed` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3;

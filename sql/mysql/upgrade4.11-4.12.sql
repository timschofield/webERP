
#--UPDATE config SET confvalue='4.12' WHERE confname='VersionNumber';

INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) 
	VALUES ('BankAccountUsers.php', '15', 'Maintains table bankaccountusers (Authorized users to work with a bank account in webERP)');

CREATE TABLE IF NOT EXISTS `bankaccountusers` (
  `accountcode` varchar(20) NOT NULL COMMENT 'Bank account code',
  `userid` varchar(20) NOT NULL COMMENT 'User code'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

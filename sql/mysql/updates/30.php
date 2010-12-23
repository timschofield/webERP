<?php

/* Move the smtp server settings into the database
 */

CreateTable('emailsettings', "CREATE TABLE `emailsettings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `host` varchar(30) NOT NULL,
  `port` char(5) NOT NULL,
  `heloaddress` varchar(20) NOT NULL,
  `username` varchar(30) DEFAULT NULL,
  `password` varchar(30) DEFAULT NULL,
  `timeout` int(11) DEFAULT '5',
  `companyname` varchar(50) DEFAULT NULL,
  `auth` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8", $db);

InsertRecord('emailsettings', array('id', 'host', 'port', 'heloaddress', 'username', 'password', 'timeout', 'companyname', 'auth'), array(Null, 'localhost', 25, 'helo', '', '', 5, '', 0), array('id', 'host', 'port', 'heloaddress', 'username', 'password', 'timeout', 'companyname', 'auth'), array(Null, 'localhost', 25, 'helo', '', '', 5, '', 0), $db);

UpdateDBNo(30, $db);

?>
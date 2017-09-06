ALTER table supptrans ADD chequeno varchar(16) NOT NULL DEFAULT '';
ALTER table supptrans ADD void tinyint(1) NOT NULL DEFAULT 0;
ALTER table banktrans ADD chequeno varchar(16) NOT NULL DEFAULT '';
INSERT INTO `scripts` (`script` ,`pagesecurity` ,`description`) VALUES ('Z_RemovePurchaseBackOrders.php',  '1',  'Removes all purchase order back orders');
ALTER table supptrans DROP KEY `TypeTransNo`; 
ALTER table supptrans ADD KEY `TypeTransNo`(`transno`,`type`); 


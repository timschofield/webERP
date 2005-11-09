
/*USE weberp; */
/*May need to uncomment the line above or edit to the name of the db you wish to upgrade*/
UPDATE config SET confname='DefaultTaxCategory' WHERE confname='DefaultTaxLevel';
UPDATE config SET confvalue='companies/weberp/part_pics'  WHERE confname='part_pics_dir';
UPDATE config SET confvalue='companies/weberp/EDI_Incoming_Orders'  WHERE confname='EDI_Incoming_Orders';
UPDATE config SET confvalue='companies/weberp/EDI_MsgPending' WHERE confname='EDI_MsgPending';
UPDATE config SET confvalue='companies/weberp/EDI_Sent' WHERE confname='EDI_Sent';
UPDATE config SET confvalue='companies/weberp/reports' WHERE confname='reports_dir';
ALTER TABLE `www_users` DROP `pinno` ;
ALTER TABLE `www_users` DROP `swipecard` ;
ALTER TABLE `suppliers` CHANGE `bankact` `bankact` VARCHAR( 30 ) NOT NULL;

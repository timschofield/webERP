ALTER TABLE `locations`
ADD KEY `idx_locations_typeloc_locationname_klposcashaccount` (`typeloc`, `locationname`, `klposcashaccount`);
ALTER TABLE `modules`
ADD INDEX `idx_modules_sequence_covering` (`sequence`, `modulelink`, `reportlink`, `modulename`);
ALTER TABLE `glaccountusers`
ADD INDEX `idx_glaccountusers_lookup` (`userid`, `canview`, `accountcode`);
ALTER TABLE `accountgroups`
ADD INDEX `idx_accountgroups_sort_covering` (`sequenceintb`, `groupname`);
ALTER TABLE `loctransfers`
ADD KEY `idx_loct_shiploc_pending_ref_stock` (`shiploc`, `pendingqty`, `reference`, `stockid`);
ALTER TABLE `loctransfers`
ADD KEY `idx_loct_recloc_pending_ref_stock` (`recloc`, `pendingqty`, `reference`, `stockid`);
ALTER TABLE `paymentmethods`
ADD INDEX `idx_paymentmethods_sort_covering` (`paymentname`, `paymentid`, `paymenttype`, `receipttype`, `percentdiscount`);
ALTER TABLE `bankaccounts`
ADD INDEX `idx_bankaccounts_sort_covering` (`bankaccountname`, `accountcode`, `currcode`);

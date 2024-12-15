INSERT INTO `securitygroups` VALUES (8,16);
-- Add a description for the script:
UPDATE `scripts` SET `description` = 'Allows you to view all bank transactions for a selected date range, and the inquiry can be filtered by matched or unmatched transactions, or all transactions can be chosen' WHERE `scripts`.`script` = 'DailyBankTransactions.php';
UPDATE config SET confvalue='4.12.1' WHERE confname='VersionNumber';
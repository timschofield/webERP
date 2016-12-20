-- Add new script:
INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PurchasesReport.php', '2', 'Shows a report of purchases to suppliers for the range of selected dates');

-- Update version number:
UPDATE config SET confvalue='4.14' WHERE confname='VersionNumber';

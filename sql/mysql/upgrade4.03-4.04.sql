INSERT INTO scripts (script, pagesecurity, description) VALUES ('SecurityTokens.php', 15, 'Administration of security tokens');
INSERT INTO scripts (script , pagesecurity ,description) VALUES ('SalesByTypePeriodInquiry.php', 2, 'Shows sales for a selected date range by sales type/price list');
INSERT INTO scripts (script , pagesecurity ,description) VALUES ('SalesCategoryPeriodInquiry.php', 2, 'Shows sales for a selected date range by stock category');
INSERT INTO scripts (script , pagesecurity ,description) VALUES ('SalesTopItemsInquiry.php', 2, 'Shows the top item sales for a selected date range');
UPDATE config SET confvalue='4.04' WHERE confname='VersionNumber';

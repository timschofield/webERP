
/*USE weberp; */
/*May need to uncomment the line above or edit to the name of the db you wish to upgrade*/
UPDATE config SET confname='DefaultTaxCategory' WHERE confname='DefaultTaxLevel';
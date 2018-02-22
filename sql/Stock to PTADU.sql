/* STOCK FOR PT ADU */

/* GLA CCOUNTS IN CHARTMESTER */

/* ALREADY DONE 
INSERT INTO `chartmaster` (`accountcode`, `accountname`, `group_`, `cashflowsactivity`, `controlled`) VALUES
('111512000AD', 'Persediaan Bahan Produksi (Components) PT ADU', 'Persediaan', -1, 0),
('111513000AD', 'Persediaan Barang Dalam Process (WIP) PT ADU', 'Persediaan', -1, 0),
('111515000AD', 'Persediaan Barang (Setup Goods) PT ADU', 'Persediaan', -1, 0),
('111516000AD', 'Persediaan Barang (Test Goods) PT ADU', 'Persediaan', -1, 0),
('111517000AD', 'Persediaan Barang (Stable goods) PT ADU', 'Persediaan', -1, 0),
('111518000AD', 'Persediaan Barang (No More PO) PT ADU', 'Persediaan', -1, 0),
('111518900AD', 'Persediaan Barang (20% Discounted Goods) PT ADU', 'Persediaan', -1, 0),
('111519000AD', 'Persediaan Barang (50% Discounted Goods) PT ADU', 'Persediaan', -1, 0),
('111519100AD', 'Persediaan Barang (80% Discounted Goods) PT ADU', 'Persediaan', -1, 0),
('111530000AD', 'Persediaan Barang (Promotion Goods) PT ADU', 'Persediaan', -1, 0),
('111800000AD', 'Persediaan Shop Display PT ADU', 'Persediaan', -1, 0),
('111800100AD', 'Persediaan Shop Packaging PT ADU', 'Persediaan', -1, 0);
INSERT INTO `glaccountusers` (`accountcode`, `userid`, `canview`, `canupd`) VALUES
('111512000AD', 'Ricard', 1, 1),
('111513000AD', 'Ricard', 1, 1),
('111515000AD', 'Ricard', 1, 1),
('111516000AD', 'Ricard', 1, 1),
('111517000AD', 'Ricard', 1, 1),
('111518000AD', 'Ricard', 1, 1),
('111518900AD', 'Ricard', 1, 1),
('111519000AD', 'Ricard', 1, 1),
('111519100AD', 'Ricard', 1, 1),
('111520000AD', 'Ricard', 1, 1),
('111530000AD', 'Ricard', 1, 1),
('111800000AD', 'Ricard', 1, 1),
('111800100AD', 'Ricard', 1, 1),
('111900000AD', 'Ricard', 1, 1);
INSERT INTO `glaccountusers` (`accountcode`, `userid`, `canview`, `canupd`) VALUES
('111512000AD', 'Revi', 1, 1),
('111513000AD', 'Revi', 1, 1),
('111515000AD', 'Revi', 1, 1),
('111516000AD', 'Revi', 1, 1),
('111517000AD', 'Revi', 1, 1),
('111518000AD', 'Revi', 1, 1),
('111518900AD', 'Revi', 1, 1),
('111519000AD', 'Revi', 1, 1),
('111519100AD', 'Revi', 1, 1),
('111520000AD', 'Revi', 1, 1),
('111530000AD', 'Revi', 1, 1),
('111800000AD', 'Revi', 1, 1),
('111800100AD', 'Revi', 1, 1),
('111900000AD', 'Revi', 1, 1);
INSERT INTO `glaccountusers` (`accountcode`, `userid`, `canview`, `canupd`) VALUES
('111512000AD', 'Ike1', 1, 1),
('111513000AD', 'Ike1', 1, 1),
('111515000AD', 'Ike1', 1, 1),
('111516000AD', 'Ike1', 1, 1),
('111517000AD', 'Ike1', 1, 1),
('111518000AD', 'Ike1', 1, 1),
('111518900AD', 'Ike1', 1, 1),
('111519000AD', 'Ike1', 1, 1),
('111519100AD', 'Ike1', 1, 1),
('111520000AD', 'Ike1', 1, 1),
('111530000AD', 'Ike1', 1, 1),
('111800000AD', 'Ike1', 1, 1),
('111800100AD', 'Ike1', 1, 1),
('111900000AD', 'Ike1', 1, 1);
INSERT INTO `glaccountusers` (`accountcode`, `userid`, `canview`, `canupd`) VALUES
('111512000AD', 'Febri', 1, 1),
('111513000AD', 'Febri', 1, 1),
('111515000AD', 'Febri', 1, 1),
('111516000AD', 'Febri', 1, 1),
('111517000AD', 'Febri', 1, 1),
('111518000AD', 'Febri', 1, 1),
('111518900AD', 'Febri', 1, 1),
('111519000AD', 'Febri', 1, 1),
('111519100AD', 'Febri', 1, 1),
('111520000AD', 'Febri', 1, 1),
('111530000AD', 'Febri', 1, 1),
('111800000AD', 'Febri', 1, 1),
('111800100AD', 'Febri', 1, 1),
('111900000AD', 'Febri', 1, 1);
INSERT INTO `glaccountusers` (`accountcode`, `userid`, `canview`, `canupd`) VALUES
('111512000AD', 'Laia', 1, 1),
('111513000AD', 'Laia', 1, 1),
('111515000AD', 'Laia', 1, 1),
('111516000AD', 'Laia', 1, 1),
('111517000AD', 'Laia', 1, 1),
('111518000AD', 'Laia', 1, 1),
('111518900AD', 'Laia', 1, 1),
('111519000AD', 'Laia', 1, 1),
('111519100AD', 'Laia', 1, 1),
('111520000AD', 'Laia', 1, 1),
('111530000AD', 'Laia', 1, 1),
('111800000AD', 'Laia', 1, 1),
('111800100AD', 'Laia', 1, 1),
('111900000AD', 'Laia', 1, 1);
*/

/* STOCK CATEGORIES */

INSERT INTO `stockcategory` (`categoryid`, `categorydescription`, `stocktype`, `stockact`, `adjglact`, `issueglact`, `purchpricevaract`, `materialuseagevarac`, `wipact`, `defaulttaxcatid`, `klprioritytransfers`) VALUES
('SETKLA', '00AD-Setup KL', 'F', '111515000AD', '510500000AD', '510500000AD', '510500000AD', '510500000AD', '111513000AD', 1, 5),
('SETBLA', '01AD-Setup BLINK', 'F', '111515000AD', '510500000AD', '510500000AD', '510500000AD', '510500000AD', '111513000AD', 1, 5),
('SETGEA', '09AD-Setup GENERAL', 'F', '111515000AD', '510500000AD', '510500000AD', '510500000AD', '510500000AD', '111513000AD', 1, 5),
('TESTKA', '10AD-Test KL', 'F', '111516000AD', '510500000AD', '510500000AD', '510500000AD', '510500000AD', '111513000AD', 1, 5),
('TESTBA', '11AD-Test BLINK', 'F', '111516000AD', '510500000AD', '510500000AD', '510500000AD', '510500000AD', '111513000AD', 1, 5),
('TESTGA', '19AD-Test GENERAL', 'F', '111516000AD', '510500000AD', '510500000AD', '510500000AD', '510500000AD', '111513000AD', 1, 5),
('STABKA', '20AD-Stable KL', 'F', '111517000AD', '510500000AD', '510500000AD', '510500000AD', '510500000AD', '111513000AD', 1, 5),
('STABBA', '21AD-Stable BLINK', 'F', '111517000AD', '510500000AD', '510500000AD', '510500000AD', '510500000AD', '111513000AD', 1, 5),
('STABGA', '29AD-Stable GENERAL', 'F', '111517000AD', '510500000AD', '510500000AD', '510500000AD', '510500000AD', '111513000AD', 1, 5),
('NOPOKA', '30AD-No PO KL', 'F', '111518000AD', '510500000AD', '510500000AD', '510500000AD', '510500000AD', '111513000AD', 1, 5),
('NOPOBA', '31AD-No PO BLINK', 'F', '111518000AD', '510500000AD', '510500000AD', '510500000AD', '510500000AD', '111513000AD', 1, 5),
('NOPOGA', '39AD-No PO GENERAL', 'F', '111518000AD', '510500000AD', '510500000AD', '510500000AD', '510500000AD', '111513000AD', 1, 5),
('DISC2A', '40AD-Discount 20 Goods', 'F', '111518900AD', '510500000AD', '510500000AD', '510500000AD', '510500000AD', '111513000AD', 1, 5),
('DISC5A', '41AD-Discount 50 Goods', 'F', '111519000AD', '510500000AD', '510500000AD', '510500000AD', '510500000AD', '111513000AD', 1, 5),
('DISC8A', '42AD-Discount 80 Goods', 'F', '111519100AD', '510500000AD', '510500000AD', '510500000AD', '510500000AD', '111513000AD', 1, 5),
('COMPOA', '80AD-Components', 'M', '111512000AD', '510500000AD', '510500000AD', '510500000AD', '510500000AD', '111513000AD', 1, 5),
('SHPACA', '90AD-Shop Packaging', 'F', '111800100AD', '510500000AD', '510500000AD', '510500000AD', '510500000AD', '111513000AD', 1, 5);

INSERT INTO `cogsglpostings` (`area`, `stkcat`, `glcode`, `salestype`) VALUES
('AN', 'SHPACA', '510010100AD', 'AN'),
('REC', 'SHPACA', '510010100AD', 'AN'),
('RER', 'SHPACA', '510010100AD', 'AN'),
('REZ', 'SHPACA', '510010100AD', 'AN'),
('RIC', 'SHPACA', '510010100AD', 'AN'),
('RIR', 'SHPACA', '510010100AD', 'AN'),
('RIZ', 'SHPACA', '510010100AD', 'AN');

/* QTY RECEIVED BY ITEM FROM PO XXX */
SELECT purchorderdetails.itemcode,
	SUM(purchorderdetails.quantityrecd) AS qtyreceivedptadu,
	(SELECT SUM(locstock.quantity)
		FROM locstock
		WHERE locstock.stockid = purchorderdetails.itemcode) AS qoh
FROM purchorderdetails
WHERE purchorderdetails.orderno >= 2750
GROUP BY purchorderdetails.itemcode
ORDER BY purchorderdetails.itemcode;


/* QTY RECEIVED BY ITEM FROM WO XXX */
SELECT woitems.stockid,
	SUM(woitems.qtyrecd) AS qtyproducedptadu,
	(SELECT SUM(locstock.quantity)
		FROM locstock
		WHERE locstock.stockid = woitems.stockid) AS qoh
FROM workorders, woitems
WHERE workorders.wo = woitems.wo
	AND workorders.closed = 1
	AND workorders.wo > 3500
GROUP BY woitems.stockid
ORDER BY woitems.stockid

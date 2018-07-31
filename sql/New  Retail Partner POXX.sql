/* CREATE POPI INFO FROM POIK*/
SET FOREIGN_KEY_CHECKS=0;

INSERT INTO `accountgroups` (`groupname`, `sectioninaccounts`, `pandl`, `sequenceintb`, `parentgroupname`) VALUES
('Banks PI', 100, 0, 250, 'Kas');

INSERT INTO `bankaccounts` (`accountcode`, `currcode`, `invoice`, `bankaccountcode`, `bankaccountname`, `bankaccountnumber`, `bankaddress`, `importformat`) VALUES
('111121100PI', 'IDR', 0, 'Retail Partner PI', 'PI - Bank Mandiri - IDR', '---', '', ''),
('111121110PI', 'IDR', 0, 'Retail Partner PI', 'PI - Bank BCA - IDR', '---', '', '');

INSERT INTO `bankaccountusers` (`accountcode`, `userid`) VALUES
('111121110PI', 'Ricard'),
('111121110PI', 'Febri'),
('111121110PI', 'Revi'),
('111121110PI', 'Ike1'),
('111121100PI', 'Ricard'),
('111121100PI', 'Ike1'),
('111121100PI', 'Revi'),
('111121100PI', 'Febri'),
('111121110PI', 'Marcel'),
('111121100PI', 'Marcel'),
('111121110PI', 'winda'),
('111121100PI', 'winda');

INSERT INTO `chartmaster` (`accountcode`, `accountname`, `group_`, `cashflowsactivity`, `controlled`) VALUES
('111121100PI', 'Bank Mandiri IDR - PI', 'Banks PI', -1, 0),
('111121110PI', 'Bank BCA IDR - PI', 'Banks PI', 0, 0),
('123111300PI', 'Peralatan / Inventaris - PI', 'Aktiva', -1, 0),
('125900000PI', 'Kontrak Lokasi - PI', 'Aktiva', 0, 0),
('211030200PI', 'Hutang Kontrak Lokasi - PI', 'Hutang', -1, 0),
('310110100PI', 'Modal Dasar (Social Capital) - PI', 'Modal', -1, 0),
('310110200PI', 'Hutang Modal - PI', 'Modal', -1, 0),
('410000000PI', 'Penjualan Retail - Cash - PI', 'Penjualan', -1, 0),
('410000010PI', 'Penjualan Retail - Credit Card - PI', 'Penjualan', -1, 0),
('510010000PI', 'HPP (COGS) - PI', 'HPP (COGS)', -1, 0),
('510010005PI', 'HPP (COGS - Consignment) - PI', 'Clustering', 0, 0),
('510010100PI', 'HPP (COGS) - Shop Packaging - PI', 'HPP (COGS)', -1, 0),
('510500010PI', 'HPP (COGS) - Shop Consumables - PI', 'HPP (COGS)', -1, 0),
('611011100PI', 'Biaya Gaji - PI', 'Biaya Karyawan', -1, 0),
('611011300PI', 'Biaya Uang Bensin Karyawan - PI', 'Biaya Karyawan', -1, 0),
('611011400PI', 'Biaya Uang Makan Karyawan - PI', 'Biaya Karyawan', -1, 0),
('611011600PI', 'Biaya Lembur/Overtime - PI', 'Biaya Karyawan', -1, 0),
('611011700PI', 'Biaya Asuransi/Jamsostek - PI', 'Biaya Karyawan', -1, 0),
('611011800PI', 'Biaya THR Karyawan - PI', 'Biaya Karyawan', -1, 0),
('611011900PI', 'Biaya Outsourcing - PI', 'Biaya Karyawan', -1, 0),
('611012000PI', 'Biaya PPH 21 - PI', 'Pajak Penghasilan', -1, 0),
('611012002PI', 'Biaya PPH 22 - PI', 'Pajak Penghasilan', -1, 0),
('611012005PI', 'Biaya PPH 23 - PI', 'Pajak Penghasilan', -1, 0),
('611012010PI', 'Biaya PPH 25 - PI', 'Pajak Penghasilan', -1, 0),
('611012015PI', 'Biaya PPH 29 - PI', 'Pajak Penghasilan', -1, 0),
('611012020PI', 'Biaya PPH 4(2) - PI', 'Pajak Penghasilan', -1, 0),
('611012030PI', 'Biaya PPN - PI', 'Pajak Penjualan', -1, 0),
('611012100PI', 'Biaya Recruitment and Training - PI', 'Biaya Karyawan', -1, 0),
('612011100PI', 'Biaya PIlan and Promosi - PI', 'Biaya Marketing', -1, 0),
('612011200PI', 'Biaya Komisi - Bonus Karyawan - PI', 'Biaya Karyawan', -1, 0),
('612011705PI', 'Biaya Packaging - PI', 'Biaya General', -1, 0),
('612019000PI', 'Biaya Marketing Lain-Lain - PI', 'Biaya Marketing', -1, 0),
('613011100PI', 'Biaya PerbaPIan Bangunan - PI', 'Penyusutan Aktiva', -1, 0),
('613011200PI', 'Biaya PerbaPIan Peralatan  (Hw/Sw) - PI', 'Penyusutan Aktiva', -1, 0),
('613011400PI', 'Biaya PerbaPIan Kendaraan - PI', 'Penyusutan Aktiva', -1, 0),
('613019000PI', 'Biaya PerbaPIan Inventaris - PI', 'Penyusutan Aktiva', -1, 0),
('614011100PI', 'Biaya Telpon - Internet - PI', 'Biaya General', -1, 0),
('614011200PI', 'Biaya IT (web services) - PI', 'Biaya General', -1, 0),
('614011400PI', 'Biaya ListrPI - PI', 'Biaya General', -1, 0),
('614011510PI', 'Biaya Air PDAM - PI', 'Biaya General', -1, 0),
('614011550PI', 'Biaya Pembersih - PI', 'Biaya General', -1, 0),
('614011600PI', 'Biaya/Penyusutan Sewa - PI', 'Penyusutan Aktiva', -1, 0),
('614011800PI', 'Biaya Konsultan Bisnis/Pajak/Notaris - PI', 'Biaya General', -1, 0),
('614011810PI', 'Biaya Asuransi - PI', 'Biaya General', -1, 0),
('614012000PI', 'Biaya Maintenance - PI', 'Biaya General', -1, 0),
('614012050PI', 'Biaya office material - PI', 'Biaya General', -1, 0),
('614012200PI', 'Biaya Perizinan - PI', 'Biaya General', -1, 0),
('614012400PI', 'Biaya Deviden - PI', 'Dividends', 0, 0),
('614012500PI', 'Biaya Pajak Bumi and Bangunan - PI', 'Pajak Penghasilan', -1, 0),
('614012550PI', 'Biaya Decorasi - Renovasi - PI', 'Biaya General', -1, 0),
('614012650PI', 'Biaya Penyusutan Aktiva - PI', 'Penyusutan Aktiva', -1, 0),
('614012850PI', 'Biaya Sewa-Servis-Surat2 Kendaraan - PI', 'Biaya General', -1, 0),
('614012900PI', 'Biaya Tol and Parkir - PI', 'Biaya General', -1, 0),
('614013500PI', 'Biaya Perjalanan Dinas DN - PI', 'Biaya General', -1, 0),
('614013600PI', 'Biaya Perjalanan Dinas LN - PI', 'Biaya General', -1, 0),
('700211200PI', 'Biaya/Pendapatan Bunga Bank - PI', 'Biaya/Pendapatan Lain2', -1, 0),
('700211300PI', 'Biaya Komisi Credit card Bank - PI', 'Biaya/Pendapatan Lain2', -1, 0),
('700211700PI', 'Biaya Administrasi/Fees Bank  - PI', 'Biaya/Pendapatan Lain2', 0, 0),
('800011000PI', 'Pajak Penghasilan Final 1% - PI', 'Pajak Penghasilan', -1, 0),
('800012000PI', 'Pajak Tangguhan - PI', 'Pajak Penghasilan', -1, 0);

INSERT INTO `pcexpenses` (`codeexpense`, `description`, `glaccount`, `tag`, `klretentionpph23`) VALUES
('ASSETS-PI', 'Payments Assets PI (Hardware-Computers, etc)', '123111300PI', 0, '0.00'),
('PPHFINAL1-PI', 'Payment Penhasilan Final 1% POPI', '800011000PI', 0, '0.00');


INSERT INTO `pctabexpenses` (`typetabcode`, `codeexpense`) VALUES
('ADMINISTRATION', 'ASSETS-PI'),
('ADMINISTRATION', 'PPHFINAL1-PI');

INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES
('KLExcelGLTransactionsPajakPI.php', 40420, 'Exports Excel file with GL Transactions for Retail partner PI'),
('KLGLAccountInquiryPI.php', 40410, 'Shows the general ledger transactions for a specified account over a specified range of periods'),
('KLGLAccountsPI.php', 40450, 'Defines the general ledger accounts for retail Partner PI'),
('KLGLBalanceSheetPI.php', 999999, 'Shows the balance sheet for Retail partner PI as at a specified date'),
('KLGLProfit_LossPI.php', 40410, 'Shows the profit and loss of retail partner PI for the range of periods entered');

INSERT INTO `systypes` (`typeid`, `typename`, `typeno`) VALUES
(9020, 'POPI Customer Invoice CC-A', 556),
(9021, 'POPI Customer Invoice CASH-B', 753),
(9022, 'POPI Customer Invoice CASH-C', 0);

SET FOREIGN_KEY_CHECKS=1;

/* AFTER SETTING THE SALES AREAS*/

INSERT INTO `cogsglpostings` (`area`, `stkcat`, `glcode`, `salestype`) VALUES
('RPC', 'ANY', '510010000PT', 'AN'),
('RPR', 'ANY', '510010000PT', 'AN'),
('RPZ', 'ANY', '510010000PT', 'AN'),
('RPC', 'SHPACK', '510010100PT', 'AN'),
('RPR', 'SHPACK', '510010100PT', 'AN'),
('RPZ', 'SHPACK', '510010100PT', 'AN'),
('RPC', 'SHPACA', '510010100AD', 'AN'),
('RPR', 'SHPACA', '510010100AD', 'AN'),
('RPZ', 'SHPACA', '510010100AD', 'AN');


<?php

NewScript('ProdSpecGroups.php', 16);

NewMenuItem('manuf', 'Maintenance', __('Product Spec Groups Maintenance'), '/ProdSpecGroups.php', 9);


CreateTable('prodspecgroups', "CREATE TABLE IF NOT EXISTS prodspecgroups (
  groupid smallint(6) NOT NULL AUTO_INCREMENT,
  groupname char(50) DEFAULT NULL,
  groupbyNo int(11) NOT NULL DEFAULT 1,
  headertitle varchar(100) DEFAULT NULL,
  trailertext varchar(240) DEFAULT NULL,
  labels varchar(240) NOT NULL,
  numcols tinyint(1) NOT NULL,
  PRIMARY KEY (groupid),
  UNIQUE KEY groupname (groupname),
  KEY groupbyNo (groupbyNo)
)");

executeSQL ("INSERT INTO prodspecgroups (groupid, groupname, groupbyNo, headertitle, trailertext, labels, numcols) VALUES
(1, 'PhysicalProperty', 1, 'Physical Properties', NULL, 'Physical Property,Value,Test Method', 3),
(2, 'MechanicalProperty', 2, NULL, NULL, '', 3),
(3, 'ThermalProperty', 3, NULL, NULL, '', 3),
(4, 'Processing', 6, 'Injection Molding Processing Guidelines', '* Desicant type dryer required.', 'Setting,Value', 2),
(5, 'RegulatoryCompliance', 5, 'Regulatory Compliance', NULL, 'Regulatory Compliance,Value', 2);");

executeSQL("INSERT INTO prodspecgroups (groupname,groupbyNo,headertitle,trailertext,labels,numcols)
SELECT DISTINCT(qatests.groupby),10,'Your Section Title Here','Trailer Notes Here','Label1,Label2,Label3',3
FROM qatests WHERE groupby NOT IN (SELECT val_ps.groupname FROM prodspecgroups AS val_ps)
AND groupby IS NOT NULL;");

if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(basename(__FILE__, '.php'), __('Added new scripts'));
}
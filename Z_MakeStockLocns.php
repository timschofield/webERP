<?php
/* $Revision: 1.6 $ */
/* Script to make stock locations for all parts that do not have stock location records set up*/


$PageSecurity=15;
include ('includes/session.inc');
$title = _('Make LocStock Records');
include('includes/header.inc');


echo '<BR><BR>' . _('This script makes stock location records for parts where they do not already exist');

$sql = 'SELECT StockMaster.StockID
               FROM StockMaster LEFT JOIN LocStock
                    ON StockMaster.StockID=LocStock.StockID
                    RIGHT JOIN Locations
                          ON Locations.LocCode = LocStock.LocCode
                WHERE LocStock.StockID IS NULL';

$ErrMsg = _('The items/locations that need stock location records created cannot be retrieved because');
$Result = DB_query($sql,$db,$ErrMsg);

while ($myrow=DB_fetch_row($Result)){

	echo '<BR>' . _('Inserting location records for') . ' ' . $myrow[0];

	$sql = "INSERT INTO LocStock (LocCode,
                                     StockID)
                        SELECT Locations.LocCode, '" . $myrow[0] . "' FROM Locations";

	$ErrMsg = _('The SQL to insert location records for part') . ' ' . $myrow[0] . ' ' . _('failed');
	$InsResult = DB_query($sql,$db,$ErrMsg);
}

echo '<P>';
prnMsg(_('Any stock items that may not have had stock location records have now been given new location stock records'),'info');

include('includes/footer.inc');
?>

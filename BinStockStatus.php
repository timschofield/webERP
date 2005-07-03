<?php
/* $Revision: 1.1 $ */

$PageSecurity = 2;
include('includes/session.inc');
$title = "Bin Stock Status";
echo "<html> \n<head> \n<title>$title</title> \n";
echo "<link href='css/". $_SESSION['Theme'] ."/default.css' rel='stylesheet' type='text/css' /> \n";
echo "</head><body>\n";
?>
<table width="100%">
<?
$sql = "Select Bins.BinID, Qty from BinStock, Bins where StockID = '". $_GET['StockID'] ."' and Bins.LocCode = '". $_GET['Location'] ."' and Bins.BinID = BinStock.BinID";
$ErrMsg = _('The demand for this product from') . ' ' . $myrow["LocCode"] . ' ' . _('cannot be retrieved because') . ':';
$Dbgmsg = '<BR>' . _('The SQL that failed was') . ':';
$Result = DB_query($sql,$db,$ErrMsg,$DbgMsg); 
echo '<center><p>StockID<br><B>'. $_GET['StockID'] .'</B></p><p>Location Code<br><B>'. $_GET['Location'] .'</p>';
$tableheader = '<TR><TD class="tableheader" align="center"><B>' . _('Bin') . '</B></TD>
	<TD class="tableheader" align="center"><B>' . _('Quantity') . '</B></TD>
	</TR>';
echo $tableheader;
$j = 1;
$k=0; //row colour counter

while ($myrow=DB_fetch_array($Result)) {
	if ($k==1){
		echo '<tr bgcolor="#CCCCCC">';
		$k=0;
	} else {
		echo '<tr bgcolor="#EEEEEE">';
		$k=1;
	}
	echo "<td align=center>". $myrow['BinID'] ."</td><td align=center>". number_format($myrow["Qty"],$DecimalPlaces) ."</td></tr>";
}
echo "</table>";
echo '<center><p><a href="#" onclick="window.print()">Print</a></p><p><a href="#" onclick="window.close()">Close</a></p>';
?>

<?php
$PageSecurity = 2;
include ("includes/session.inc");
include("includes/header.inc");



echo "<title>Shipments Open Inquiry</title>";
include("includes/DateFunctions.inc");


if (!isset($_GET['SupplierID']) OR !isset($_GET['SupplierName'])){
  echo "<P>This page must be given the supplier code to look for shipments for.";
  exit;
}

$SQL = "SELECT ShiptRef, Vessel, ETA FROM Shipments WHERE SupplierID='" . $_GET['SupplierID'] . "'";

$ShiptsResult = DB_query($SQL,$db);

if (DB_error_no($db) !=0) {
       echo "No shipments were returned by the SQL because there was an error executing the SQL - " . DB_error_msg($db);
       echo "<BR>$SQL";
}
if (DB_num_rows($ShiptsResult)==0){
       echo "<P><BR>There are no open shipments currently set up for " . $_GET['SupplierName'];
       exit;
}
/*show a table of the shipments returned by the SQL */

echo "<CENTER><FONT SIZE=4 COLOR=BLUE>Open Shipments for " . $_GET['SupplierName'] . "</FONT><BR><TABLE CELLPADDING=2 COLSPAN=2><TR><TD BGCOLOR =#800000><FONT COLOR=#ffffff>Reference</FONT></TD><TD BGCOLOR =#800000><FONT COLOR=#ffffff>Vessel</FONT></TD><TD BGCOLOR =#800000><FONT COLOR=#ffffff>ETA</FONT></TD></TR>";

$j = 1;
$k=0; //row colour counter

while ($myrow=DB_fetch_array($ShiptsResult)) {
       if ($k==1){
              echo "<tr bgcolor='#CCCCCC'>";
              $k=0;
       } else {
              echo "<tr bgcolor='#EEEEEE'>";
              $k=1;
       }

       printf("<td >%s</td><td>%s</td><td>%s</td></tr>", $myrow["ShiptRef"],$myrow["Vessel"],ConvertSQLDate($myrow["ETA"]));

       $j++;
       If ($j == 12){
              $j=1;
              echo "<TR><TD BGCOLOR =#800000><FONT COLOR=#ffffff>Reference</FONT></TD><TD BGCOLOR =#800000><FONT COLOR=#ffffff>Vessel</FONT></TD><TD BGCOLOR =#800000><FONT COLOR=#ffffff>ETA</FONT></TD></TR>";
       }
}
//end of while loop

echo "</TABLE></CENTER>";

include("includes/footer.inc");

?>

<?php
/* $Revision: 1.2 $ */
$title = "Identify Allocation Stuff Ups";
$PageSecurity = 15;
include ("header.inc");

$sql = "SELECT Type, TransNo, OvAmount, Alloc, Sum(CustAlloc.Amt) As TotAllocFrom FROM DebtorTrans, CustAlloc WHERE TransID_AllocFrom=DebtorTrans.ID HAVING Sum(CustAlloc.Amt) < -Alloc GROUP BY Type, TransNo, OvAmount, Alloc";

$result =DB_query($sql,$db);


echo "<TABLE><TR><TD>Type</TD><TD>Trans No</TD><TD>Ov Amt</TD><TD>Allocated</TD><TD>Tot Allcns</TD></TR>";

$RowCounter =0;
while ($myrow=DB_fetch_array($result)){


	printf ("<TR><TD>%s</TD><TD>%s<TD ALIGN=RIGHT>%f.2</TD><TD ALIGN=RIGHT>%f.2</TD><TD ALIGN=RIGHT>%f.2</TD></TR>",$myrow["Type"],$myrow["TransNo"],$myrow["OvAmount"],$myrow["Alloc"],$myrow["TotAllocFrom"]);
	$RowCounter++;
	if ($RowCounter==20){
		echo "<TR><TD>Type</TD><TD>Trans No</TD><TD>Ov Amt</TD><TD>Allocated</TD><TD>Tot Allcns</TD></TR>";
		$RowCounter=0;
	}

}



?>
</body>
</html>

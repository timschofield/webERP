<?php
/* $Revision: 1.2 $ */
$title = "Supplier Purchasing Data";

$PageSecurity = 4;

include("includes/session.inc");
include("includes/header.inc");
include("includes/DateFunctions.inc");


if (isset($_GET['SupplierID'])){
	$SupplierID = strtoupper($_GET['SupplierID']);
} elseif (isset($_POST['SupplierID'])){
	$SupplierID = strtoupper($_POST['SupplierID']);
}

if (isset($_GET['StockID'])){
	$StockID = strtoupper($_GET['StockID']);
} elseif (isset($_POST['StockID'])){
	$StockID = strtoupper($_POST['StockID']);
}


if (isset($SupplierID) AND $SupplierID!=""){			   /*NOT EDITING AN EXISTING BUT SUPPLIER SELECTED OR ENTERED*/
  $sql = "SELECT SuppName, CurrCode FROM Suppliers WHERE SupplierID='$SupplierID'";
  $SuppSelResult = DB_query($sql,$db);
  if (DB_error_no($db) !=0) {
	echo "The supplier details for the selected supplier could not be retrieved because - " . DB_error_msg($db);
	if ($debug==1){
	   echo "<BR>The SQL that failed was:<BR>$sql";
	}
	exit;
   } else {
	if (DB_num_rows($SuppSelResult) ==1){
		$myrow = DB_fetch_array($SuppSelResult);
		$SuppName = $myrow["SuppName"];
		$CurrCode = $myrow["CurrCode"];
	} else {
		echo "</CENTER><BR><FONT COLOR=RED>WARNING: The supplier code $SupplierID is not an existing supplier in the database. You must enter an alternative supplier code or select a supplier using the search facility below.<CENTER>";
		unset($SupplierID);
	}
   }
}

if ((isset($_POST['AddRecord']) OR isset($_POST['UpdateRecord'])) AND isset($SupplierID)){	      /*Validate Inputs */
   $InputError = 0; /*Start assuming the best */
   if ($StockID=="" OR !isset($StockID)){
      $InputError=1;
      echo "<BR><FONT COLOR=RED>There is no stock item set up enter the stock code or select a stock item using the search page.</FONT>";
   }
   if (! is_numeric($_POST['Price']) OR $_POST['Price']==0){
      $InputError =1;
      unset($_POST['Price']);
      echo "<BR><FONT COLOR=RED>The price entered was not numeric (a number is expected) - no changes have been made to the database</FONT>";
   }
   if (! is_numeric($_POST['LeadTime'])){
      $InputError =1;
      unset($_POST['LeadTime']);
      echo "<BR><FONT COLOR=RED>The lead time entered was not numeric (a number is expected) - no changes have been made to the database</FONT>";
   }
   if (!is_numeric($_POST['ConversionFactor'])){
      $InputError =1;
      unset($_POST['ConversionFactor']);
      echo "<BR><FONT COLOR=RED>The conversion factor entered was not numeric (a number is expected). The conversion factor is the number by which the price must be divided by to get the unit price in our unit of measure. <BR>Eg. The supplier sells an item by the tonne and we hold stock by the kg. The suppliers price must be divided by 1000 to get to our cost per kg. The conversion factor to enter is 1000. <BR><BR>No changes will be made to the database</FONT>";
   }


   if ($InputError==0 AND isset($_POST['AddRecord'])){

      $sql = "INSERT INTO PurchData (SupplierNo, StockID, Price, SuppliersUOM, ConversionFactor, SupplierDescription, LeadTime, Preferred) VALUES (";
      $sql = $sql . "'$SupplierID', '$StockID', " . $_POST['Price'] . ", '" . $_POST['SuppliersUOM'] . "', " . $_POST['ConversionFactor'] . ", '" . $_POST['SupplierDescription'] . "', " . $_POST['LeadTime'] . ", " . $_POST['Preferred'] . ")";
      $AddResult = DB_query($sql,$db);
      if (DB_error_no($db) !=0) {
	  echo "The supplier purchasing details could not be added to the database because - " . DB_error_msg($db);
	  if ($debug==1){
	      echo "<BR>The SQL that failed was $sql";
	  }
	  exit;
      } else {
	  echo "<BR>This supplier purchasing data has been added to the database";
      }
   }
   if ($InputError==0 AND isset($_POST['UpdateRecord'])){

      $sql = "UPDATE PurchData SET Price=" . $_POST['Price'] . ", SuppliersUOM='" . $_POST['SuppliersUOM'] . "', ConversionFactor=" . $_POST['ConversionFactor'] . ", SupplierDescription='" . $_POST['SupplierDescription'] . "', LeadTime=" . $_POST['LeadTime'] . ", Preferred=" . $_POST['Preferred'] . " WHERE StockID='$StockID' AND SupplierNo='$SupplierID'";
      $UpdResult = DB_query($sql,$db);
      if (DB_error_no($db) !=0) {
	  echo "The supplier purchasing details could not be update because - " . DB_error_msg($db);
	  if ($debug==1){
	      echo "<BR>The SQL that failed was $sql";
	  }
	  exit;
      } else {
	  echo "<BR>Supplier purchasing data has been updated.";
      }

   }
   if ($InputError==0 AND (isset($_POST['UpdateRecord']) OR isset($_POST['AddRecord']))){
      /*update or insert took place and need to clear the form  */
      unset($SupplierID);
      unset($_POST['Price']);
      unset($CurrCode);
      unset($_POST['SuppliersUOM']);
      unset($_POST['ConversionFactor']);
      unset($_POST['SupplierDescription']);
      unset($_POST['LeadTime']);
      unset($_POST['Preferred']);
   }
}


if (isset($_GET['Delete'])){

   $sql = "DELETE FROM PurchData WHERE SupplierNo='$SupplierID' AND StockID='$StockID'";
   $DelResult=DB_query($sql,$db);
   if (DB_error_no($db) !=0) {
	  echo "The supplier purchasing details could not be deleted because - " . DB_error_msg($db);
	  if ($debug==1){
	      echo "<BR>The SQL that failed was:<BR>$sql";
	  }
	  exit;
   } else {
	   echo "<BR><B>This purchasing data record has been sucessfully deleted</B><BR>";
	   unset ($SupplierID);
   }

}


$result = DB_query("SELECT Description, Units, MBflag FROM StockMaster WHERE StockID='$StockID'",$db);
$myrow = DB_fetch_row($result);
if (DB_num_rows($result)==1){
   if ($myrow[2]=="D" OR $myrow[2]=="A" OR $myrow[2]=="K"){
	echo "<P><FONT SIZE=3><B>$StockID - $myrow[0] </B><P> The part selected is a dummy part or an assembly/kit set part - it is not purchased. Entry of purchasing information is therefore inappropriate.</FONT><HR>";
	exit;
   } else {
	echo "<BR><FONT COLOR=BLUE SIZE=3><B>$StockID - $myrow[0] </B>  (In Units of $myrow[1] )</FONT>";
   }
} else {
  echo "<BR><FONT COLOR=RED SIZE=3><B>Stock Item - $StockID is not defined in the database</B></FONT><BR>";
}

echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "?" . SID . "' METHOD=POST>";
echo "Stock Code:<input type=text name='StockID' value='$StockID' size=21 maxlength=20>";
echo "    <INPUT TYPE=SUBMIT NAME='ShowSupplierDetails' VALUE='Show Suppliers'>";
echo "    <A HREF='$rootpath/SelectProduct.php?" . SID . "'>Select Product</A>";
echo "<HR><CENTER>";

if (!isset($_GET['Edit'])){
   $sql = "SELECT  SupplierNo, SuppName, Price, CurrCode, SuppliersUOM, SupplierDescription, LeadTime, Preferred FROM PurchData INNER JOIN Suppliers ON PurchData.SupplierNo=Suppliers.SupplierID WHERE StockID = '" . $StockID . "'";
   $PurchDataResult = DB_query($sql, $db);
   if (DB_error_no($db) !=0) {
	echo "The supplier purchasing details for the selected part could not be retrieved because - " . DB_error_msg($db);
	if ($debug==1){
	   echo "<BR>The SQL that failed was $sql";
	}
	exit;
   }

   if (DB_num_rows($PurchDataResult)==0){
      echo "There is no purchasing data set up for the part selected";
   } else {
     /*											 SuppName							      Price								  CurrCode								 SuppliersUOM 							     LeadTime */
     echo "<TABLE CELLPADDING=2 BORDER=2>";
     $TableHeader = "<TR><TD class='tableheader'>Supplier</TD><TD class='tableheader'>Price</TD><TD class='tableheader'>Currency</TD><TD class='tableheader'>Supplier's Unit</TD><TD class='tableheader'>Lead Time</TD><TD class='tableheader'>Preferred</TD></TR>";

     echo $TableHeader;

     $CountPreferreds =0;
     $k=0; //row colour counter

     while ($myrow=DB_fetch_array($PurchDataResult)) {
	if ($myrow["Preferred"]==1){
	     echo "<tr bgcolor='$BGColour'>";
	} elseif ($k==1){
		echo "<tr bgcolor='#CCCCCC'>";
		$k=0;
	} else {
		echo "<tr bgcolor='#EEEEEE'>";
		$k++;
	}
	if ($myrow["Preferred"]==1){
	  $DisplayPreferred="Yes";
	  $CountPreferreds++;
	} else {
	  $DisplayPreferred="No";
	}
     /*	   SuppName		    Price	CurrCode   SuppliersUOM	  LeadTime		 Preferred																				    SuppName				      Price		     CurrCode 	    SuppliersUOM	     LeadTime 	    Preferred  */
	printf("<td>%s</td><td ALIGN=RIGHT>%s</td><td>%s</td><td>%s</td><td ALIGN=RIGHT>%s days</td><td>%s</td><td><a href='%s?StockID=%s&SupplierID=%s&Edit=1'>Edit</a></td><td><a href='%s?StockID=%s&SupplierID=%s&Delete=1'>Delete</a></td></tr>", $myrow["SuppName"], number_format($myrow["Price"],2),$myrow["CurrCode"], $myrow["SuppliersUOM"], $myrow["LeadTime"], $DisplayPreferred, $_SERVER['PHP_SELF'], $StockID, $myrow["SupplierNo"], $_SERVER['PHP_SELF'], $StockID, $myrow["SupplierNo"]);

	$j++;
	If ($j == 12){
		$j=1;

		echo $TableHeader;

	} //end of page full new headings
    } //end of while loop
    echo "</TABLE>";
    if ($CountPreferreds>1){
	      echo "<BR><B><FONT COLOR=RED>WARNING: There are now $CountPreferreds preferred suppliers set up for $StockID you should edit the supplier purchasing data to make only one supplier the preferred supplier</B></FONT>";
    } elseif($CountPreferreds==0){
	      echo "<BR><B><FONT COLOR=RED>WARNING: There are NO preferred suppliers set up for $StockID you should make one supplier only the preferred supplier</B></FONT>";
    }
  } // end of there are purchsing data rows to show
  echo "<HR>";
} /* Only show the existing purchasing data records if one is not being edited */


/*Show the input form for new supplier purchasing details */

if (isset($_GET['Edit'])){

   $sql = "SELECT SupplierNo, SuppName, Price, CurrCode, SuppliersUOM, SupplierDescription, LeadTime, ConversionFactor, Preferred FROM PurchData INNER JOIN Suppliers ON PurchData.SupplierNo=Suppliers.SupplierID WHERE PurchData.SupplierNo='$SupplierID' AND PurchData.StockID='$StockID'";
   $EditResult = DB_query($sql, $db);
   if (DB_error_no($db) !=0) {
	echo "The supplier purchasing details for the selected supplier and item could not be retrieved because - " . DB_error_msg($db);
	if ($debug==1){
	   echo "<BR>The SQL that failed was $sql";
	}
	exit;
   } else {
     $myrow = DB_fetch_array($EditResult);

     $SuppName = $myrow["SuppName"];
     $_POST['Price'] = $myrow["Price"];
     $CurrCode = $myrow["CurrCode"];
     $_POST['SuppliersUOM'] = $myrow["SuppliersUOM"];
     $_POST['SupplierDescription'] = $myrow["SupplierDescription"];
     $_POST['LeadTime'] = $myrow["LeadTime"];
     $_POST['ConversionFactor'] = $myrow["ConversionFactor"];
     $_POST['Preferred'] = $myrow["Preferred"];
   }
}

echo "<TABLE>";

if (isset($_GET['Edit'])){
    echo "<TR><TD>Supplier Code:</TD><TD><INPUT TYPE=HIDDEN NAME='SupplierID'VALUE='$SupplierID'>$SupplierID - $SuppName</TD></TR>";
} else {
    echo "<TR><TD>Supplier Code:</TD><TD><INPUT TYPE=TEXT NAME='SupplierID' MAXLENGTH=10 SIZE=11 VALUE='$SupplierID'>";
    if (!isset($SuppName) OR $SuppName=""){
	echo "<FONT SIZE=1>(A search facility is available below if necessary)";
    } else {
	echo $SuppName;
    }
    echo "</TD></TR>";
}

echo "<TR><TD>Currency:</TD><TD><INPUT TYPE=HIDDEN NAME='CurrCode'VALUE='$CurrCode'>$CurrCode</TD></TR>";
echo "<TR><TD>Price (in Supplier's Currency):</TD><TD><INPUT TYPE=TEXT NAME='Price' MAXLENGTH=12 SIZE=12 VALUE=" . $_POST['Price'] . "></TD></TR>";
echo "<TR><TD>Suppliers Unit of Measure:</TD><TD><INPUT TYPE=TEXT NAME='SuppliersUOM' MAXLENGTH=50 SIZE=51 VALUE='" . $_POST['SuppliersUOM'] . "'></TD></TR>";
if (!isset($_POST['ConversionFactor']) OR $_POST['ConversionFactor']==""){
   $_POST['ConversionFactor']=1;
}
echo "<TR><TD>Conversion Factor (to our UOM):</TD><TD><INPUT TYPE=TEXT NAME='ConversionFactor' MAXLENGTH=12 SIZE=12 VALUE=" . $_POST['ConversionFactor'] . "></TD></TR>";
echo "<TR><TD>Supplier's Code or Description:</TD><TD><INPUT TYPE=TEXT NAME='SupplierDescription' MAXLENGTH=50 SIZE=51 VALUE='" . $_POST['SupplierDescription'] . "'></TD></TR>";
if (!isset($_POST['LeadTime']) OR $_POST['LeadTime']==""){
   $_POST['LeadTime']=1;
}
echo "<TR><TD>Lead Time (in days from date of order):</TD><TD><INPUT TYPE=TEXT NAME='LeadTime' MAXLENGTH=10 SIZE=11 VALUE=" . $_POST['LeadTime'] . "></TD></TR>";
echo "<TR><TD>Preferred Supplier:</TD><TD><SELECT NAME='Preferred'>";

if ($_POST['Preferred']==1){
	echo "<OPTION SELECTED VALUE=1>Yes";
	echo "<OPTION VALUE=0>No";
} else {
	echo "<OPTION VALUE=1>Yes";
	echo "<OPTION SELECTED VALUE=0>No";
}
echo "</SELECT></TD></TR></TABLE>";

if (isset($_GET['Edit'])){
   echo "<INPUT TYPE=SUBMIT NAME='UpdateRecord' VALUE='Update'>";
} else {
   echo "<INPUT TYPE=SUBMIT NAME='AddRecord' VALUE='Add'>";
}


echo "<HR>";




if (isset($_POST['SearchSupplier'])){

	If (isset($_POST['Keywords']) AND isset($_POST['SupplierCode'])) {
		$msg="Supplier name keywords have been used in preference to the Supplier code extract entered.";
	}
	If ($_POST['Keywords']=="" AND $_POST['SupplierCode']=="") {
		$msg="At least one Supplier name keyword OR an extract of a Supplier code must be entered for the search";
	} else {
		If (strlen($_POST['Keywords'])>0) {
			//insert wildcard characters in spaces

			$i=0;
			$SearchString = "%";
			while (strpos($_POST['Keywords'], " ", $i)) {
				$wrdlen=strpos($_POST['Keywords']," ",$i) - $i;
				$SearchString=$SearchString . substr($_POST['Keywords'],$i,$wrdlen) . "%";
				$i=strpos($_POST['Keywords']," ",$i) +1;
			}
			$SearchString = $SearchString. substr($_POST['Keywords'],$i)."%";

			$SQL = "SELECT SupplierID, SuppName, CurrCode, Address1, Address2, Address3 FROM Suppliers WHERE SuppName LIKE '$SearchString'";

		} elseif (strlen($_POST['SupplierCode'])>0){
			$SQL = "SELECT SupplierID, SuppName, CurrCode, Address1, Address2, Address3 FROM Suppliers WHERE SupplierID LIKE '%" . $_POST['SupplierCode'] . "%'";

		}

		$SuppliersResult = DB_query($SQL,$db);
		if (DB_error_no($db) !=0) {
			echo "The suppliers matching the criteria entered could not be retrieved because - " . DB_error_msg($db);
			if ($debug==1){
			   echo "<BR>The SQL to retireve supplier details that failed was $SQL";
			}
			exit;
		}


	} //one of keywords or SupplierCode was more than a zero length string
} //end of if search

?>

<B><?php echo "<BR>" . $msg; ?></B>
<TABLE CELLPADDING=3 COLSPAN=4>
<TR>
<TD>Text in the Supplier's <B>NAME</B>:</FONT></TD>
<TD><INPUT TYPE="Text" NAME="Keywords" SIZE=20 MAXLENGTH=25></TD>
<TD><FONT SIZE=3><B>OR</B></FONT></TD>
<TD>Text in Supplier's <B>CODE</B>:</FONT></TD>
<TD><INPUT TYPE="Text" NAME="SupplierCode" SIZE=15 MAXLENGTH=18></TD>
</TR>
</TABLE>
<CENTER><INPUT TYPE=SUBMIT NAME="SearchSupplier" VALUE="Find Suppliers Now">
<INPUT TYPE=SUBMIT ACTION=RESET VALUE="Reset"></CENTER>
<HR>

<?php

If (isset($SuppliersResult)) {

	echo "<TABLE CELLPADDING=2 COLSPAN=7 BORDER=2>";
	$TableHeader = "<TR><TD class='tableheader'>Code</TD><TD class='tableheader'>Supplier Name</TD><TD class='tableheader'>Currency</TD><TD class='tableheader'>Address 1</TD><TD class='tableheader'>Address 2</TD><TD class='tableheader'>Address 3</TD></TR>";
	echo $TableHeader;

	$j = 1;

	while ($myrow=DB_fetch_array($SuppliersResult)) {

		printf("<tr><td><FONT SIZE=1><INPUT TYPE=SUBMIT NAME='SupplierID' VALUE='%s'</FONT></td><td><FONT SIZE=1>%s</FONT></td><td><FONT SIZE=1>%s</FONT></td><td><FONT SIZE=1>%s</FONT></td><td><FONT SIZE=1>%s</FONT></td><td><FONT SIZE=1>%s</FONT></td></tr>", $myrow["SupplierID"], $myrow["SuppName"], $myrow["CurrCode"], $myrow["Address1"], $myrow["Address2"],$myrow["Address3"]);

		$j++;
		If ($j == 11){
			$j=1;
			echo $TableHeader;
		}
//end of page full new headings if
	}
//end of while loop

	echo "</TABLE>";

}
//end if results to show


if (isset($StockID) AND strlen($StockID)!=0){
   echo "<BR><A HREF='$rootpath/StockStatus.php?" . SID . "StockID=$StockID'>Show Stock Status</A>";
   echo "<BR><A HREF='$rootpath/StockMovements.php?" . SID . "StockID=$StockID&StockLocation=$StockLocation'>Show Stock Movements</A>";
   echo "<BR><A HREF='$rootpath/SelectSalesOrder.php?" . SID . "SelectedStockItem=$StockID&StockLocation=$StockLocation'>Search Outstanding Sales Orders</A>";
   echo "<BR><A HREF='$rootpath/SelectCompletedOrder.php?" .SID . "SelectedStockItem=$StockID'>Search Completed Sales Orders</A>";
}

echo "</form></center>";
include("includes/footer.inc");

?>

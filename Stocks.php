<?php
/* $Revision: 1.5 $ */
$title = "Stock Item Maintenance";

$PageSecurity = 11;

include("includes/session.inc");
include("includes/header.inc");
include("includes/DateFunctions.inc");

/*If this form is called with the StockID then it is assumed that the stock item is to be modified */

if (isset($_GET['StockID'])){
	$StockID =strtoupper($_GET['StockID']);
} elseif (isset($_POST['StockID'])){
	$StockID =strtoupper($_POST['StockID']);
}

if ($_POST['submit']) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (strlen($_POST['Description']) > 50 OR strlen($_POST['Description'])==0) {
		$InputError = 1;
		echo "The stock item description must be entered and be fifty characters or less long. It cannot be a zero length string either - a description is required.";
	} elseif (strlen($_POST['LongDescription'])==0) {
		$InputError = 1;
		echo "The stock item description cannot be a zero length string - a long description is required.";
	} elseif (strlen($StockID) ==0) {
		$InputError = 1;
		echo "The Stock Item code cannot be empty";
	}elseif (strstr($StockID," ") OR strstr($StockID,"'") OR strstr($StockID,"+") OR strstr($StockID,"\"") OR strstr($StockID,"&")) {
		$InputError = 1;
		echo "<BR>The stock item code cannot contain any of the following characters - ' & + \" OR a space";

	} elseif (strlen($_POST['Units']) >20) {
		$InputError = 1;
		echo "<BR>The unit of measure must be 20 characters or less long";
	} elseif (strlen($_POST['BarCode']) >20) {
		$InputError = 1;
		echo "<BR>The barcode must be 20 characters or less long";
	} elseif (!is_numeric($_POST['Volume'])) {
		$InputError = 1;
		echo "<BR>The volume of the packaged item in cubic metres must be numeric.";
	} elseif ($_POST['Volume'] <0) {
		$InputError = 1;
		echo "<BR>The volume of the packaged item must be a positive number.";
	} elseif (!is_numeric($_POST['KGS'])) {
		$InputError = 1;
		echo "<BR>The weight of the packaged item in KGs must be numeric.";
	} elseif ($_POST['KGS'] <0) {
		$InputError = 1;
		echo "<BR>The weight of the packaged item must be a positive number.";
	} elseif (!is_numeric($_POST['EOQ'])) {
		$InputError = 1;
		echo "<BR>The economic order quantity must be numeric.";
	} elseif ($_POST['EOQ'] <0) {
		$InputError = 1;
		echo "<BR>The economic order quantity must be a positive number.";
	} elseif (! is_numeric($_POST['TaxLevel'])){
		$InputError = 1;
		echo "<BR>The tax level determines the rate of tax in conjunction with the tax authority of the branch where the item is sold to. The tax level must be a number between 0 and 9.";
	}

	if ($InputError !=1){

		if (!isset($_POST['New'])) { /*so its an existing one */

			/*first check on the changes being made we must disallow:
				- changes from manufactured or purchased to Dummy, Assembly or Kitset if there is stock
				- changes from manufactured, kitset or assembly where a BOM exists
			*/

			$sql = "SELECT MBflag FROM StockMaster WHERE StockID = '$StockID'";
			$MBFlagResult = DB_query($sql,$db);
			$myrow = DB_fetch_row($MBFlagResult);
			$OldMBFlag = $myrow[0];
			if ($OldMBFlag != $_POST['MBFlag']){
				if (($OldMBFlag == "M" OR $OldMBFlag=="B") AND ($_POST['MBFlag']=="A" OR $_POST['MBFlag']=="K" OR $_POST['MBFlag']=="D")){ /*then need to check that there is no stock holding first */

					$sql = "SELECT Sum(LocStock.Quantity) FROM LocStock WHERE StockID='$StockID'";
					$result = DB_query($sql,$db);
					$stkqtychk = DB_fetch_row($result);
					if ($stkqtychk[0]!=0){
						$InputError=1;
						echo "<P><FONT SIZE=4 COLOR=RED><B>The make or buy flag cannot be changed from " . $OldMBFlag . " to " . $_POST['MBFlag'] . " where there is a quantity of stock on hand at any location. Currently there are " . $stkqtychk[0] . " on hand.";
					}
				}
				/*now check that if the item is being changed to a kitset, there are no items on order sales or purchase orders*/
				if ($_POST["MBFlag"]=="K") {
					$sql = "SELECT Quantity-QtyInvoiced FROM SalesOrderDetails WHERE StkCode = '$StockID' AND Completed=0";
					$result = DB_query($sql,$db);
					$ChkSalesOrds = DB_fetch_row($result);
					if ($ChkSalesOrds[0]!=0){
						$InputError = 1;
						echo "<P><FONT SIZE=4 COLOR=RED><B>The make or buy flag cannot be changed to a kitset where there is a quantity outstanding to be delivered on sales orders. Currently there are " . $ChkSalesOrds[0] . " outstanding.";
					}
				}
				/*now check that if it is to be a kitset or assembly or dummy there is no quantity on purchase orders outstanding*/
				if ($_POST["MBFlag"]=="K" OR $_POST["MBFlag"]=="A" OR $_POST["MBFlag"]=="D") {

					$sql = "SELECT QuantityOrd-QuantityRecd FROM PurchOrderDetails WHERE ItemCode = '$StockID' AND Completed=0";
					$result = DB_query($sql,$db);
					$ChkPurchOrds = DB_fetch_row($result);
					if ($ChkPurchOrds[0]!=0){
						$InputError = 1;
						echo "<P><FONT SIZE=4 COLOR=RED><B>The make or buy flag cannot be changed to " . $_POST["MBFlag"] . " where there is a quantity outstanding to be received on purchase orders. Currently there are " . $ChkPurchOrds[0] . " yet to be received.";
					}
				}
				/*now check that if it is was a Manufactured, Kitset or Assembly and is being changed to a purchased or dummy - that no BOM exists */

				if (($OldMBFlag=="M" OR $OldMBFlag =="K" OR $OldMBFlag=="A") AND ($_POST["MBFlag"]=="B" OR $_POST["MBFlag"]=="D")) {
					$sql = "SELECT Count(*) FROM BOM WHERE Parent = '$StockID'";
					$result = DB_query($sql,$db);
					$ChkBOM = DB_fetch_row($result);
					if ($ChkBOM[0]!=0){
						$InputError = 1;
						echo "<P><FONT SIZE=4 COLOR=RED><B>The make or buy flag cannot be changed from manufactured, kitset or assembly to " . $_POST["MBFlag"] . " where there is a bill of material set up for the item. Bills of material are not appropriate for purchased or dummy items.";
					}
				}
			}

			if ($InputError == 0){
				$sql = "UPDATE StockMaster SET LongDescription='" . $_POST['LongDescription'] . "', Description='" . $_POST['Description'] . "', Discontinued=" . $_POST['Discontinued'] . ", Controlled=" . $_POST['Controlled'] . ", Serialised=" . $_POST['Serialised'].", CategoryID='" . $_POST['CategoryID'] . "', Units='" . $_POST['Units'] . "', MBflag='" . $_POST['MBFlag'] . "', EOQ=" . $_POST['EOQ'] . ", Volume=" . $_POST['Volume'] . ", KGS=" . $_POST['KGS'] . ", BarCode='" . $_POST['BarCode'] . "', DiscountCategory='" . $_POST['DiscountCategory'] . "', TaxLevel=" . $_POST['TaxLevel'] . ", StkModClass='".$_POST['StkModClass']."' WHERE StockID='$StockID'";

				$result = DB_query($sql,$db);
				if (DB_error_no($db) !=0) {
					echo "The stock item could not be updated because - " . DB_error_msg($db);
					if ($debug==1){
						echo "<BR>The SQL that was used to update the stock item,   and failed was :<BR>$sql";
					}
				} else {
					echo "<BR>Stock Item $StockID has been updated";
				}
			}

		} else { //it is a NEW part

			$sql = "INSERT INTO StockMaster (StockID, Description, LongDescription, CategoryID, Units, MBFlag, EOQ, Discontinued, Controlled, Serialised, Volume, KGS, BarCode, DiscountCategory, TaxLevel, StkModClass) VALUES ('$StockID', '" . $_POST['Description'] . "', '" . $_POST['LongDescription'] . "', '" . $_POST['CategoryID'] . "', '" . $_POST['Units'] . "', '" . $_POST['MBFlag'] . "', " . $_POST['EOQ'] . ", " . $_POST['Discontinued'] . ", " . $_POST['Controlled'] . ", " . $_POST['Serialised']. ", " . $_POST['Volume'] . ", " . $_POST['KGS'] . ", '" . $_POST['BarCode'] . "','" . $_POST[', DiscountCategory'] . "'," . $_POST['TaxLevel'] . ",'".$_POST['StkModClass']."')";

			$result = DB_query($sql,$db);
			if (DB_error_no($db) !=0) {
				echo "The item could not be added because - " . DB_error_msg($db);
				if ($debug==1){
					echo "<BR>The SQL that was used and failed was:<BR>$sql";
				}
			} else {
				$sql = "INSERT INTO LocStock (LocCode, StockID) SELECT Locations.LocCode, '" . $StockID . "' FROM Locations";

				$InsResult = DB_query($sql,$db);
				if (DB_error_no($db)!=0 && $debug==1){

					echo "<BR>The SQL to insert location records for part " . $myrow[0] . " failed, the SQL statement was:<BR>$sql<P>NB Locations records can be added by opening the utlity page <i>Z_MakeStockLocns.php</i>";
				exit;
				}
				echo "<BR>New Item $StockID has been added to the database";
				unset($_POST['LongDescription']);
				unset($_POST['Description']);
				unset($_POST['EOQ']);
				unset($_POST['CategoryID']);
				unset($_POST['Units']);
				unset($_POST['MBFlag']);
				unset($_POST['Discontinued']);
				unset($_POST['Controlled']);
				unset($_POST['Serialised']);
				unset($_POST['Volume']);
				unset($_POST['KGS']);
				unset($_POST['BarCode']);
				unset($_POST['ReorderLevel']);
				unset($_POST['DiscountCategory']);
				unset($_POST['StkModClass']);
				unset($StockID);
			}
		}
		/*Check for a new TaxLevel and insert new records in TaxAuthLevels as necessary */

		$result = DB_query("SELECT TaxAuthority FROM TaxAuthLevels WHERE Level=" . $_POST['TaxLevel'] ,$db);
		if (DB_num_rows($result)==0){ /*need to add the new level accross all TaxAuthorities */

			$DispTaxAuthResult = DB_query("SELECT DISTINCT TaxAuthority FROM Locations",$db);

			while ($DispTaxAuthRow = DB_fetch_row($DispTaxAuthResult)){
				$sql = "INSERT INTO TaxAuthLevels (TaxAuthority, DispatchTaxAuthority, Level) SELECT TaxID, " . $DispTaxAuthRow[0] . ", " . $_POST['TaxLevel'] . " FROM TaxAuthorities";
				$InsertResult = DB_query($sql,$db);
			}
		}

		if (!isset($_POST['New'])) { /*so its an existing one */
			echo "<META HTTP-EQUIV='Refresh' CONTENT='0; URL=" . $rootpath . "/SelectProduct.php?" . SID . "NewSearch=True'>";
		}

	} else {
		echo "<BR>Validation failed, no updates or deletes took place.";
	}

} elseif (isset($_POST['delete']) AND strlen($_POST['delete']) >1 ) {
//the button to delete a selected record was clicked instead of the submit button

	$CancelDelete = 0;

// PREVENT DELETES IF DEPENDENT RECORDS IN 'StockMoves'

	$sql= "SELECT COUNT(*) FROM StockMoves WHERE StockID='$StockID'";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		$CancelDelete = 1;
		echo "<BR>Cannot delete this stock item because there are stock movements that refer to this item.";
		echo "<BR> There are " . $myrow[0] . " stock movements that refer to this item";

	} else {
		$sql= "SELECT COUNT(*) FROM BOM WHERE Component='$StockID'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			$CancelDelete = 1;
			echo "<BR>Cannot delete this item record because there are bills of material that require this part as a component.";
			echo "<br> There are " . $myrow[0] . " bills of material that require this part as a component.";
		} else {
			$sql= "SELECT COUNT(*) FROM SalesOrderDetails WHERE StkCode='$StockID'";
			$result = DB_query($sql,$db);
			$myrow = DB_fetch_row($result);
			if ($myrow[0]>0) {
				$CancelDelete = 1;
				echo "<BR>Cannot delete this item record because there are existing sales orders for this part.";
				echo "<br> There are " . $myrow[0] . " sales order items against this part.";
			} else {
				$sql= "SELECT COUNT(*) FROM SalesAnalysis WHERE StockID='$StockID'";
				$result = DB_query($sql,$db);
				$myrow = DB_fetch_row($result);
				if ($myrow[0]>0) {
					$CancelDelete = 1;
					echo "<BR>Cannot delete this item because sales analysis records exist for it.";
					echo "<br> There are " . $myrow[0] . " sales analysis records against this part.";
				} else {
					$sql= "SELECT COUNT(*) FROM PurchOrderDetails WHERE ItemCode='$StockID'";
					$result = DB_query($sql,$db);
					$myrow = DB_fetch_row($result);
					if ($myrow[0]>0) {
						$CancelDelete = 1;
						echo "<BR>Cannot delete this item because there are existing purchase order items for it.";
						echo "<br> There are " . $myrow[0] . " purchase order item record relating to this part";
					} else {
						$sql = "SELECT Sum(Quantity) AS QOH FROM LocStock WHERE StockID='$StockID'";
						$result = DB_query($sql,$db);
						$myrow = DB_fetch_row($result);
						if ($myrow[0]!=0) {
						$CancelDelete = 1;
						echo "<BR>Cannot delete this item because there is currently some stock on hand.";
						echo "<br> There are " . $myrow[0] . " on hand for this part";
						}
					}
				}
			}
		}

	}
	if ($CancelDelete==0) {
		$sql="DELETE FROM StockMaster WHERE StockID='$StockID'";
		$result=DB_query($sql,$db);
		echo "<BR>Deleted the stock master record for $StockID .... <BR>";
		/*and cascade deletes in LocStock */
		$sql ="DELETE FROM LocStock WHERE StockID='$StockID'";
		$result=DB_query($sql,$db);
		echo "<BR> . .and deleted all the location stock records set up for $StockID.... <BR>";
		/*and cascade deletes in PurchData */
		$sql ="DELETE FROM PurchData WHERE StockID='$StockID'";
		$result=DB_query($sql,$db);
		/*and cascade delete the bill of material if any */
		$sql = "DELETE FROM BOM WHERE Parent='$StockID'";
		$result=DB_query($sql,$db);
		echo "<BR>. . .and any bill of material that may have been set up for the part<BR>";
		echo "<BR> . . . . and any purchasing data that may have been set up for the part<BR>";
		unset($_POST['LongDescription']);
		unset($_POST['Description']);
		unset($_POST['EOQ']);
		unset($_POST['CategoryID']);
		unset($_POST['Units']);
		unset($_POST['MBFlag']);
		unset($_POST['Discontinued']);
		unset($_POST['Controlled']);
		unset($_POST['Serialised']);
		unset($_POST['Volume']);
		unset($_POST['KGS']);
		unset($_POST['BarCode']);
		unset($_POST['ReorderLevel']);
		unset($_POST['DiscountCategory']);
		unset($_POST['TaxLevel']);
		unset($_POST['StkModClass']);
		unset($StockID);
		unset($_SESSION['SelectedStockItem']);
		//echo "<META HTTP-EQUIV='Refresh' CONTENT='0; URL=" . $rootpath . "/SelectProduct.php?" . SID  ."'>";


	} //end if Delete Part
}


echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . "?" .SID ."><CENTER><TABLE>";

if (!isset($StockID) OR isset($_POST['New'])) {

/*If the page was called without $StockID passed to page then assume a new stock item is to be entered show a form with a part Code field other wise the form showing the fields with the existing entries against the part will show for editing with only a hidden StockID field. New is set to flag that the page may have called itself and still be entering a new part, in which case the page needs to know not to go looking up details for an existing part*/

	echo "<input type='Hidden' name='New' value='Yes'>";

	echo "<TR><TD>Item Code:</TD><TD><input type='Text' name='StockID' SIZE=21 MAXLENGTH=20 Value=$StockID></TD></TR>";

} else { // Must be modifying an existing item

	$sql = "SELECT StockID, Description, LongDescription, CategoryID, Units, MBFlag, Discontinued, Controlled, Serialised, EOQ, Volume, KGS, BarCode, DiscountCategory, TaxLevel, StkModClass FROM StockMaster WHERE StockID = '$StockID'";
	$result = DB_query($sql, $db);
	$myrow = DB_fetch_array($result);

	$_POST['LongDescription'] = $myrow["LongDescription"];
	$_POST['Description'] = $myrow["Description"];
	$_POST['EOQ']  = $myrow["EOQ"];
	$_POST['CategoryID']  = $myrow["CategoryID"];
	$_POST['Units']  = $myrow["Units"];
	$_POST['MBFlag']  = $myrow["MBFlag"];
	$_POST['Discontinued']  = $myrow["Discontinued"];
	$_POST['Controlled']  = $myrow["Controlled"];
	$_POST['Serialised']  = $myrow["Serialised"];
	$_POST['Volume']  = $myrow["Volume"];
	$_POST['KGS']  = $myrow["KGS"];
	$_POST['BarCode']  = $myrow["BarCode"];
	$_POST['ReorderLevel']  = $myrow["ReorderLevel"];
	$_POST['DiscountCategory']  = $myrow["DiscountCategory"];
	$_POST['TaxLevel'] = $myrow["TaxLevel"];
	$_POST['StkModClass'] = $myrow["StkModClass"];

	echo "<TR><TD>Item Code:</TD><TD>$StockID</TD></TR>";
	echo "<input type='Hidden' name='StockID' value='$StockID'>";

}

echo "<TR><TD>Part Description (short):</TD><TD><input type='Text' name='Description' SIZE=52 MAXLENGTH=50 value='" . $_POST['Description'] . "'></TD></TR>";

echo "<TR><TD>Part Description (long):</TD><TD><textarea name='LongDescription' cols=40 rows=4>" . $_POST['LongDescription'] . "</textarea></TD></TR>";

echo "<TR><TD>Category:</TD><TD><SELECT name=CategoryID>";

$sql = "SELECT CategoryID, CategoryDescription FROM StockCategory";
$result = DB_query($sql,$db);

if (DB_error_no($db) !=0) {
	echo "The stock categories could not be retrieved because - " . DB_error_msg($db);
	if ($debug==1){
		echo "<BR>The SQL used to retrieve stock categories - and failed was :<BR>$sql";
	}
}

while ($myrow=DB_fetch_array($result)){
	if ($myrow["CategoryID"]==$_POST['CategoryID']){
		echo "<OPTION SELECTED VALUE='". $myrow["CategoryID"] . "'>" . $myrow["CategoryDescription"];
	} else {
		echo "<OPTION VALUE='". $myrow["CategoryID"] . "'>" . $myrow["CategoryDescription"];
	}
}

if ($_POST['EOQ']=="" or !isset($_POST['EOQ'])){
    $_POST['EOQ']=0;
}

if ($_POST['Volume']=="" OR !isset($_POST['Volume'])){
    $_POST['Volume']=0;
}
if ($_POST['KGS']=="" OR !isset($_POST['KGS'])){
    $_POST['KGS']=0;
}
if ($_POST['Controlled']=="" OR !isset($_POST['Controlled'])){
    $_POST['Controlled']=0;
}
if ($_POST['Serialised']=="" OR !isset($_POST['Serialised']) || $_POST['Controlled']==0){
    $_POST['Serialised']=0;
}
if ($_POST['StkModClass']=="" OR !isset($_POST['StkModClass']) || $_POST['Controlled']==0){
    $_POST['StkModClass']="GenericStockItem"; //I know, I know...
}
if ($_POST['Discontinued']=="" OR !isset($_POST['Discontinued'])){
    $_POST['Discontinued']=0;
}

echo "</SELECT>    <a target='_blank' href='$rootpath/StockCategories.php?" . SID . "'>Add or Modify Stock Categories</a>";


echo "</TR><TR><TD>Economic Order Quantity:</TD><TD><input type='Text' name='EOQ' SIZE=12 MAXLENGTH=10 Value=" . $_POST['EOQ'] . "></TD></TR>";

echo "<TR><TD>Packaged Volume (metres cubed):</TD><TD><input type='Text' name='Volume' SIZE=12 MAXLENGTH=10 value=" . $_POST['Volume'] . "></TD></TR>";

echo "<TR><TD>Packaged Weight (KGs):</TD><TD><input type='Text' name='KGS' SIZE=12 MAXLENGTH=10 value=" . $_POST['KGS'] . "></TD></TR>";

echo "<TR><TD>Units of Measure:</TD><TD><SELECT name='Units'>";

/* The array StockUnits is set up in config.php for user modification
possible units of measure can added or modifying the array definition by editing that file */

foreach ($StockUnits as $UOM) {

     if ($_POST['Units']==$UOM){
	    echo "<OPTION SELECTED Value='$UOM'>$UOM";
     } else {
	    echo "<OPTION Value='$UOM'>$UOM";
     }
}
echo "</SELECT></TD></TR>";

echo "<TR><TD>Make, Buy, Kit, Assembly or Dummy Part:</TD><TD><SELECT name='MBFlag'>";
if ($_POST['MBFlag']=='A'){
	echo "<OPTION SELECTED VALUE='A'>Assembly";
} else {
	echo "<OPTION VALUE='A'>Assembly";
}
if ($_POST['MBFlag']=='K'){
	echo "<OPTION SELECTED VALUE='K'>Kit";
} else {
	echo "<OPTION VALUE='K'>Kit";
}
if ($_POST['MBFlag']=='M'){
	echo "<OPTION SELECTED VALUE='M'>Manufactured";
} else {
	echo "<OPTION VALUE='M'>Manufactured";
}
if ($_POST['MBFlag']=='B' OR !isset($_POST['MBFlag']) OR $_POST['MBFlag']==""){
	echo "<OPTION SELECTED VALUE='B'>Purchased";
} else {
	echo "<OPTION VALUE='B'>Purchased";
}

if ($_POST['MBFlag']=='D'){
	echo "<OPTION SELECTED VALUE='D'>Dummy";
} else {
	echo "<OPTION VALUE='D'>Dummy";
}

echo "</SELECT></TD></TR>";

echo "<TR><TD>Current or Obsolete:</TD><TD><SELECT name='Discontinued'>";
if ($_POST['Discontinued']==0){
	echo "<OPTION SELECTED VALUE=0>Current";
} else {
	echo "<OPTION VALUE=0>Current";
}
if ($_POST['Discontinued']==1){
	echo "<OPTION SELECTED VALUE=1>Obsolete";
} else {
	echo "<OPTION VALUE=1>Obsolete";
}
echo "</SELECT></TD></TR>";

echo "<TR><TD>Controlled:</TD><TD><SELECT name='Controlled'>";

if ($_POST['Controlled']==0){
	echo "<OPTION SELECTED VALUE=0>No Control Required";
} else {
        echo "<OPTION VALUE=0>No Control Required";
}
if ($_POST['Controlled']==1){
	echo "<OPTION SELECTED VALUE=1>Bundle/Lot Control Required";
} else {
	echo "<OPTION VALUE=1>Bundle/Lot Control Required";
}
echo "</SELECT></TD></TR>";

echo "<TR><TD>Serialised:</TD><TD><SELECT name='Serialised'>";

if ($_POST['Serialised']==0){
        echo "<OPTION SELECTED VALUE=0>No";
} else {
        echo "<OPTION VALUE=0>No";
}
if ($_POST['Serialised']==1){
        echo "<OPTION SELECTED VALUE=1>Yes";
} else {
        echo "<OPTION VALUE=1>Yes";
}
echo "</SELECT><i>Note, this has no effect if the item is not Controlled</i></TD></TR>";

echo "<TR><TD>Category:</TD><TD><SELECT name=StkModClass>";

$sql = "SELECT StkModClass, Description FROM StockModules";
$result = DB_query($sql,$db);

if (DB_error_no($db) !=0) {
        echo "The stock modules could not be retrieved because - " . DB_error_msg($db);
        if ($debug==1){
                echo "<BR>The SQL used to retrieve stock categories failed and was :<BR>$sql";
        }
}

while ($myrow=DB_fetch_array($result)){
        if ($myrow["StkModClass"]==$_POST['StkModClass']){
                echo "<OPTION SELECTED VALUE='". $myrow["StkModClass"] . "'>" . $myrow["Description"];
        } else {
                echo "<OPTION VALUE='". $myrow["StkModClass"] . "'>" . $myrow["Description"];
        }
}
echo "</SELECT><TD></TR>";

echo "<TR><TD>Bar Code:</TD><TD><input type='Text' name='BarCode' SIZE=22 MAXLENGTH=20 value='" . $_POST['BarCode'] . "'></TD></TR>";

echo "<TR><TD>Discount Category:</TD><TD><input type='Text' name='DiscountCategory' SIZE=2 MAXLENGTH=2 value='" . $_POST['DiscountCategory'] . "'></TD></TR>";

if (!isset($_POST['TaxLevel'])){
	$_POST['TaxLevel']=1;
}

echo "<TR><TD>Tax Level:</TD><TD><input type='Text' name='TaxLevel' SIZE=1 MAXLENGTH=1 value='" . $_POST['TaxLevel'] . "'></TD></TR>";

echo "</TABLE><CENTER><P>";

if (isset($_POST['New']) OR $_POST['New']!="") {
	echo "<input type='Submit' name='submit' value='Insert New Item'>";

} else {

	echo "<input type='Submit' name='submit' value='Submit Alterations'>";

	echo "<P><B><FONT COLOR=RED>WARNING: Only click the Delete button if you are sure you wish to delete the item. There is no second warning! Checks will be made to ensure that there are no stock movements, sales analysis records, sales order items or purchase order items for the item. No deletions will be allowed if they exist.</FONT>";
	echo "<P><input type='Submit' name='delete' value='Delete This Item'>";

}

echo "</form>";
include("includes/footer.inc");
?>

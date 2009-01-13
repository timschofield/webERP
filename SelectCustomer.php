<?php
/* $Revision: 1.39 $ */

$PageSecurity = 2;

include('includes/session.inc');
$title = _('Search Customers');
include('includes/header.inc');
include('includes/Wiki.php');
include('includes/SQL_CommonFunctions.inc');


if (!isset($_SESSION['CustomerID'])){ //initialise if not already done
	$_SESSION['CustomerID']="";
}
if (!isset($_SESSION['CustomerType'])){ //initialise if not already done
	$_SESSION['CustomerType']="";
}
// only run geocode if integration is turned on and customer has been selected
if ($_SESSION['geocode_integration']==1 AND $_SESSION['CustomerID'] <>0){

$sql="SELECT * FROM geocode_param WHERE 1";
$ErrMsg = _('An error occurred in retrieving the information');
$result = DB_query($sql, $db, $ErrMsg);
$myrow = DB_fetch_array($result);
$sql = "SELECT debtorsmaster.debtorno,debtorsmaster.name,custbranch.brname,
                                custbranch.lat, custbranch.lng
                        FROM debtorsmaster LEFT JOIN custbranch
                                ON debtorsmaster.debtorno = custbranch.debtorno
                        WHERE debtorsmaster.debtorno = '" . $_SESSION['CustomerID'] . "'
                        ORDER BY debtorsmaster.debtorno";
$ErrMsg = _('An error occurred in retrieving the information');
$result2 = DB_query($sql, $db, $ErrMsg);
$myrow2 = DB_fetch_array($result2);
$lat = $myrow2['lat'];
$lng = $myrow2['lng'];
$api_key = $myrow['geocode_key'];
$center_long = $myrow['center_long'];
$center_lat = $myrow['center_lat'];
$map_height = $myrow['map_height'];
$map_width = $myrow['map_width'];
$map_host = $myrow['map_host'];

echo '<script src="http://maps.google.com/maps?file=api&v=2&key=' . $api_key . '"';
echo ' type="text/javascript"></script>';
echo ' <script type="text/javascript">';
echo '    //<![CDATA[ '; ?>

    function load() {
      if (GBrowserIsCompatible()) {
        var map = new GMap2(document.getElementById("map"));
	map.addControl(new GSmallMapControl());
	map.addControl(new GMapTypeControl());
<?php echo 'map.setCenter(new GLatLng(' . $lat . ', ' . $lng . '), 11);'; ?>
<?php echo 'var marker = new GMarker(new GLatLng(' . $lat . ', ' . $lng . '));' ?>
	map.addOverlay(marker);
	GEvent.addListener(marker, "click", function() {
	marker.openInfoWindowHtml(WINDOW_HTML);
	  });
	marker.openInfoWindowHtml(WINDOW_HTML);
      }
    }
    //]]>
    </script>
  <body onload="load()" onunload="GUnload()">

<?php
}
$msg="";

if (!isset($_POST['PageOffset'])) {
  $_POST['PageOffset'] = 1;
} else {
  if ($_POST['PageOffset']==0) {
    $_POST['PageOffset'] = 1;
  }
}

if (isset($_POST['Search']) OR isset($_POST['CSV']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
	if (isset($_POST['Search'])){
		$_POST['PageOffset'] = 1;
	}
	If ($_POST['Keywords'] AND (($_POST['CustCode']) OR ($_POST['CustPhone']) OR ($_POST['CustType']))) {
		$msg=_('Search Result: Customer Name has been used in search') . '<br>';
		$_POST['Keywords'] = strtoupper($_POST['Keywords']);
	}
	If ($_POST['CustCode'] AND $_POST['CustPhone']=="" AND isset($_POST['CustType']) AND $_POST['Keywords']=="") {
		$msg=_('Search Result: Customer Code has been used in search') . '<br>';
	}
	If (($_POST['CustPhone']) AND ($_POST['CustType'])) {
		$msg=_('Search Result: Customer Phone has been used in search') . '<br>';
	}
	If ($_POST['CustType'] AND $_POST['CustPhone']=="" AND $_POST['CustCode']=="" AND $_POST['Keywords']==""){
		$msg=_('Search Result: Customer Type has been used in search') . '<br>';
	}
	If (($_POST['Keywords']=="") AND ($_POST['CustCode']=="") AND ($_POST['CustPhone']=="") AND ($_POST['CustType']=="")) {

		$SQL= "SELECT debtorsmaster.debtorno,
				debtorsmaster.name,
				debtorsmaster.address1,
                                debtorsmaster.address2,
                                debtorsmaster.address3,
                                debtorsmaster.address4,
				custbranch.brname,
				custbranch.contactname,
				debtortype.typename,
				custbranch.phoneno,
				custbranch.faxno
			FROM debtorsmaster LEFT JOIN custbranch
				ON debtorsmaster.debtorno = custbranch.debtorno, debtortype
			WHERE debtorsmaster.typeid = debtortype.typeid
			ORDER BY debtorsmaster.name";

	} else {
		If (strlen($_POST['Keywords'])>0) {

			$_POST['Keywords'] = strtoupper(trim($_POST['Keywords']));

			//insert wildcard characters in spaces

			$i=0;
			$SearchString = "%";

			while (strpos($_POST['Keywords'], " ", $i)) {
				$wrdlen=strpos($_POST['Keywords']," ",$i) - $i;
				$SearchString=$SearchString . substr($_POST['Keywords'],$i,$wrdlen) . "%";
				$i=strpos($_POST['Keywords']," ",$i) +1;
			}
			$SearchString = $SearchString . substr($_POST['Keywords'],$i)."%";

				$SQL = "SELECT debtorsmaster.debtorno,
				debtorsmaster.name,
				debtorsmaster.address1,
                                debtorsmaster.address2,
                                debtorsmaster.address3,
                                debtorsmaster.address4,
				custbranch.brname,
				custbranch.contactname,
				debtortype.typename,
				custbranch.phoneno,
				custbranch.faxno
			FROM debtorsmaster LEFT JOIN custbranch
				ON debtorsmaster.debtorno = custbranch.debtorno, debtortype
			WHERE debtorsmaster.name " . LIKE . " '$SearchString'
			AND debtorsmaster.typeid = debtortype.typeid
			ORDER BY debtorsmaster.name";

		} elseif (strlen($_POST['CustCode'])>0){

			$_POST['CustCode'] = strtoupper(trim($_POST['CustCode']));
				$SQL = "SELECT debtorsmaster.debtorno,
				debtorsmaster.name,
				debtorsmaster.address1,
                                debtorsmaster.address2,
                                debtorsmaster.address3,
                                debtorsmaster.address4,
				custbranch.brname,
				custbranch.contactname,
				debtortype.typename,
				custbranch.phoneno,
				custbranch.faxno
			FROM debtorsmaster LEFT JOIN custbranch
				ON debtorsmaster.debtorno = custbranch.debtorno, debtortype
			WHERE debtorsmaster.debtorno " . LIKE  . " '%" . $_POST['CustCode'] . "%'
			AND debtorsmaster.typeid = debtortype.typeid
			ORDER BY debtorsmaster.name";
		} elseif (strlen($_POST['CustPhone'])>0){
			$SQL = "SELECT debtorsmaster.debtorno,
				debtorsmaster.name,
				debtorsmaster.address1,
                                debtorsmaster.address2,
                                debtorsmaster.address3,
                                debtorsmaster.address4,
				custbranch.brname,
				custbranch.contactname,
				debtortype.typename,
				custbranch.phoneno,
				custbranch.faxno
			FROM debtorsmaster LEFT JOIN custbranch
				ON debtorsmaster.debtorno = custbranch.debtorno, debtortype
			WHERE custbranch.phoneno " . LIKE  . " '%" . $_POST['CustPhone'] . "%'
			AND debtorsmaster.typeid = debtortype.typeid
			ORDER BY debtorsmaster.name";
		} elseif (strlen($_POST['CustType'])>0){
                        $SQL = "SELECT debtorsmaster.debtorno,
                                debtorsmaster.name,
                                debtorsmaster.address1,
                                debtorsmaster.address2,
                                debtorsmaster.address3,
                                debtorsmaster.address4,
                                custbranch.brname,
                                custbranch.contactname,
                                debtortype.typename,
                                custbranch.phoneno,
                                custbranch.faxno
                        FROM debtorsmaster LEFT JOIN custbranch
                                ON debtorsmaster.debtorno = custbranch.debtorno, debtortype
                        WHERE debtorsmaster.typeid LIKE debtortype.typeid
                        AND debtortype.typename = '" . $_POST['CustType'] . "'
                        ORDER BY debtorsmaster.name";
		}
	} //one of keywords or custcode or custphone was more than a zero length string
	$ErrMsg = _('The searched customer records requested cannot be retrieved because');
	$result = DB_query($SQL,$db,$ErrMsg);
	if (DB_num_rows($result)==1){
		$myrow=DB_fetch_array($result);
		$_POST['Select'] = $myrow['debtorno'];
		unset($result);
	} elseif (DB_num_rows($result)==0){
		prnMsg(_('No customer records contain the selected text') . ' - ' . _('please alter your search criteria and try again'),'info');
	}
} //end of if search


If (!isset($_POST['Select'])){
	$_POST['Select']="";
}

echo '<BR>';

If ($_POST['Select']!="" OR
	($_SESSION['CustomerID']!=""
	AND !isset($_POST['Keywords'])
	AND !isset($_POST['CustCode'])
	AND !isset($_POST['CustType'])
	AND !isset($_POST['CustPhone']))) {

	If ($_POST['Select']!=""){
		$SQL = "SELECT brname, phoneno FROM custbranch WHERE debtorno='" . $_POST['Select'] . "'";
		$_SESSION['CustomerID'] = $_POST['Select'];
	} else {
		$SQL = "SELECT debtorsmaster.name, custbranch.phoneno FROM 
		debtorsmaster, custbranch WHERE 
		custbranch.debtorno='" . $_SESSION['CustomerID'] . "' AND 
		debtorsmaster.debtorno = custbranch.debtorno";
	}

	$ErrMsg = _('The customer name requested cannot be retrieved because');
	$result = DB_query($SQL,$db,$ErrMsg);

	if ($myrow=DB_fetch_row($result)){
		$CustomerName = $myrow[0];
		$phone = $myrow[1];
	}
	unset($result);
	echo '<CENTER><FONT SIZE=3>' . _('Customer') . ' :<B> ' . $_SESSION['CustomerID'] . ' - ' . $CustomerName . ' ' . $phone . _('</b> has been selected') . '.<BR>' . _('Select a menu option to operate using this customer') . '.</FONT><BR><br>';

	$_POST['Select'] = NULL;

	echo "<TABLE BORDER=2 CELLPADDING=4><TR><TH>" . _('Customer Inquiries') . "</TH>
			<TH>" . _('Customer Maintenance') . "</TH></TR>";

	echo '<TR><TD WIDTH=50%>';

	/* Customer Inquiry Options */
	echo '<a href="' . $rootpath . '/CustomerInquiry.php?CustomerID=' . $_SESSION['CustomerID'] . '">' . _('Customer Transaction Inquiries') . '</a><BR>';
	echo '<a href="' . $rootpath . '/PrintCustStatements.php?FromCust=' . $_SESSION['CustomerID'] . '&ToCust=' . $_SESSION['CustomerID'] . '&PrintPDF=Yes">' . _('Print Customer Statement') . '</a><BR>';
	echo '<a href="' . $rootpath . '/SelectSalesOrder.php?SelectedCustomer=' . $_SESSION['CustomerID'] . '">' . _('Modify Outstanding Sales Orders') . '</a><BR>';
	echo '<a href="' . $rootpath . '/SelectCompletedOrder.php?SelectedCustomer=' . $_SESSION['CustomerID'] . '">' . _('Order Inquiries') . '</a><BR>';
	echo '<a href="' . $rootpath . '/CustomerAllocations.php?DebtorNo=' . $_SESSION['CustomerID'] . '">' . _('Allocate Receipts or Credit Notes') . '</a><BR>';

	wikiLink('Customer', $_SESSION['CustomerID']);

	echo '</TD><TD WIDTH=50%>';

        echo '<a href="' . $rootpath . '/Customers.php?">' . _('Add a New Customer') . '</a><br>';
	echo '<a href="' . $rootpath . '/Customers.php?DebtorNo=' . $_SESSION['CustomerID'] . '">' . _('Modify Customer Details') . '</a><BR>';
	echo '<a href="' . $rootpath . '/CustomerBranches.php?DebtorNo=' . $_SESSION['CustomerID'] . '">' . _('Add/Modify/Delete Customer Branches') . '</a><BR>';

	echo '<a href="' . $rootpath . '/SelectProduct.php">' . _('Special Customer Prices') . '</a><BR>';
	echo '<a href="' . $rootpath . '/CustEDISetup.php">' . _('Customer EDI Configuration') . '</a>';


	echo '</TD></TR></TABLE><BR></CENTER>';
} else {
	echo "<CENTER><TABLE WIDTH=50% BORDER=2><TR><TH>" . _('Customer Inquiries') . "</TH>
			<TH>" . _('Customer Maintenance') . "</TH></TR>";

	echo '<TR><TD WIDTH=50%>';

	echo '</TD><TD WIDTH=50%>';
  	if (!isset($_SESSION['SalesmanLogin']) or $_SESSION['SalesmanLogin']==''){
    	echo '<a href="' . $rootpath . '/Customers.php?">' . _('Add a New Customer') . '</a><br>';
    }
	echo '</TD></TR></TABLE><BR></CENTER>';
}

?>

<FORM ACTION="<?php echo $_SERVER['PHP_SELF'] . '?' . SID; ?>" METHOD=POST>
<CENTER>
<B><?php echo $msg; ?></B>
<?php echo _('Search for Customers:') . '<br><br>'; ?>
<TABLE CELLPADDING=3 COLSPAN=4>
<TR>
<TD><?php echo _('Enter a partial Name'); ?>:</TD>
<TD>
<?php
if (isset($_POST['Keywords'])) {
?>
<INPUT TYPE="Text" NAME="Keywords" value="<?php echo $_POST['Keywords']?>" SIZE=20 MAXLENGTH=25>
<?php
} else {
?>
<INPUT TYPE="Text" NAME="Keywords" SIZE=20 MAXLENGTH=25>
<?php
}
?>
</TD>
<TD><FONT SIZE=3><B><?php echo _('OR'); ?></B></FONT></TD>
<TD><?php echo _('Enter a partial Code'); ?>:</TD>
<TD>
<?php
if (isset($_POST['CustCode'])) {
?>
<INPUT TYPE="Text" NAME="CustCode" value="<?php echo $_POST['CustCode'] ?>" SIZE=15 MAXLENGTH=18>
<?php
} else {
?>
<INPUT TYPE="Text" NAME="CustCode" SIZE=15 MAXLENGTH=18>
<?php
}
?>
</TD>
<TD><FONT SIZE=3><B><?php echo _('OR'); ?></B></FONT></TD>
<TD><?php echo _('Enter a partial Phone Number'); ?>:</TD>
<TD>
<?php
if (isset($_POST['CustPhone'])) {
?>
<INPUT TYPE="Text" NAME="CustPhone" value="<?php echo $_POST['CustPhone'] ?>" SIZE=15 MAXLENGTH=18>
<?php
} else {
?>
<INPUT TYPE="Text" NAME="CustPhone" SIZE=15 MAXLENGTH=18>
<?php
}
?>
</TD>

<TD><FONT SIZE=3><B><?php echo _('OR'); ?></B></FONT></TD>
<TD><?php echo _('Choose a Type'); ?>:</TD>
<TD>

<?php
        if (isset($_POST['CustType'])) {
// Show Customer Type drop down list
        $result2=DB_query('SELECT typeid, typename FROM debtortype ',$db);
// Error if no customer types setup
        if (DB_num_rows($result2)==0){
                $DataError =1;
                echo '<TR><TD COLSPAN=2>' . prnMsg(_('No Customer types defined'),'error') . '</TD></TR>';
        } else {
// If OK show select box with option selected
echo '<SELECT NAME="CustType">';
                while ($myrow = DB_fetch_array($result2)) {
if ($_POST['CustType']==$myrow['typename']){
                        echo "<OPTION SELECTED VALUE='". $myrow['typename'] . "'>" . $myrow['typename'];
                } else {
                        echo "<OPTION VALUE='". $myrow['typename'] . "'>" . $myrow['typename'];
                }
                } //end while loop
                DB_data_seek($result2,0);
                echo '</SELECT></TD></TR>';
}
} else {
// No option selected yet, so show Customer Type drop down list
        $result2=DB_query('SELECT typeid, typename FROM debtortype ',$db);
// Error if no customer types setup
        if (DB_num_rows($result2)==0){
                $DataError =1;
                echo '<TR><TD COLSPAN=2>' . prnMsg(_('No Customer types defined'),'error') . '</TD></TR>';
        } else {
// if OK show select box with available options to choose
echo '<SELECT NAME="CustType">';
                while ($myrow = DB_fetch_array($result2)) {
                        echo "<OPTION VALUE='". $myrow['typename'] . "'>" . $myrow['typename'];
                } //end while loop
                DB_data_seek($result2,0);
                echo '</SELECT></TD></TR>';
        }      }
?>
</TD>
</TR>
</TABLE>
<INPUT TYPE=SUBMIT NAME="Search" VALUE="<?php echo _('Search Now'); ?>">
<INPUT TYPE=SUBMIT NAME="CSV" VALUE="<?php echo _('CSV Format'); ?>">
</CENTER>

<?php
if (isset($_SESSION['SalesmanLogin']) and $_SESSION['SalesmanLogin']!=''){
	prnMsg(_('Your account enables you to see only customers allocated to you'),'warn',_('Note: Sales-person Login'));
}

If (isset($result)) {
  unset($_SESSION['CustomerID']);
  $ListCount=DB_num_rows($result);
  $ListPageMax=ceil($ListCount/$_SESSION['DisplayRecordsMax']);
if (!isset($_POST['CSV'])) {
  if (isset($_POST['Next'])) {
    if ($_POST['PageOffset'] < $ListPageMax) {
	    $_POST['PageOffset'] = $_POST['PageOffset'] + 1;
    }
	}

  if (isset($_POST['Previous'])) {
    if ($_POST['PageOffset'] > 1) {
	    $_POST['PageOffset'] = $_POST['PageOffset'] - 1;
    }
  }

 if ($ListPageMax >1) {
	echo "<P>&nbsp;&nbsp;" . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';

	echo '<SELECT NAME="PageOffset">';

	$ListPage=1;
	while($ListPage <= $ListPageMax) {
		if ($ListPage == $_POST['PageOffset']) {
			echo '<OPTION VALUE=' . $ListPage . ' SELECTED>' . $ListPage . '</OPTION>';
		} else {
			echo '<OPTION VALUE=' . $ListPage . '>' . $ListPage . '</OPTION>';
		}
		$ListPage++;
	}
	echo '</SELECT>
		<INPUT TYPE=SUBMIT NAME="Go" VALUE="' . _('Go') . '">
		<INPUT TYPE=SUBMIT NAME="Previous" VALUE="' . _('Previous') . '">
		<INPUT TYPE=SUBMIT NAME="Next" VALUE="' . _('Next') . '">';
 	echo '<P>';
}

	echo '<CENTER><BR><TABLE CELLPADDING=2 COLSPAN=7 BORDER=2>';
	$TableHeader = '<TR>
				<TH>' . _('Code') . '</TH>
				<TH>' . _('Customer Name') . '</TH>
				<TH>' . _('Branch') . '</TH>
				<TH>' . _('Contact') . '</TH>
				<TH>' . _('Type') . '</TH>
				<TH>' . _('Phone') . '</TH>
				<TH>' . _('Fax') . '</TH>
			</TR>';

	echo $TableHeader;
	$j = 1;
	$k = 0; //row counter to determine background colour
  $RowIndex = 0;
}
  if (DB_num_rows($result)<>0){

if (isset($_POST['CSV'])) {
printf("Code, Customer Name, Address1, Address2, Address3, Address4, Contact, Type, Phone, Fax");
while ($myrow2=DB_fetch_array($result)) {
printf("<br><FONT SIZE=1>%s,
                        %s,
                        %s,
                        %s,
                        %s,
                        %s,
                        %s,
                        %s,
                        %s,
                        %s</FONT>",
                        $myrow2['debtorno'],
                        str_replace(',', '',$myrow2['name']),
                        str_replace(',', '',$myrow2['address1']),
                        str_replace(',', '',$myrow2['address2']),
			str_replace(',', '',$myrow2['address3']),
			str_replace(',', '',$myrow2['address4']),                        
                        str_replace(',', '',$myrow2['contactname']),
                        str_replace(',', '',$myrow2['typename']),
                        $myrow2['phoneno'],
                        $myrow2['faxno']);

} 
}
if (!isset($_POST['CSV'])) {
  	DB_data_seek($result, ($_POST['PageOffset']-1)*$_SESSION['DisplayRecordsMax']);
  }

	while (($myrow=DB_fetch_array($result)) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {

		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}

		printf("<td><FONT SIZE=1><INPUT TYPE=SUBMIT NAME='Select' VALUE='%s'</FONT></td>
			<td><FONT SIZE=1>%s</FONT></td>
			<td><FONT SIZE=1>%s</FONT></td>
			<td><FONT SIZE=1>%s</FONT></td>
			<td><FONT SIZE=1>%s</FONT></td>
			<td><FONT SIZE=1>%s</FONT></td>
			<td><FONT SIZE=1>%s</FONT></td></tr>",
			$myrow['debtorno'],
			$myrow['name'],
			$myrow['brname'],
			$myrow['contactname'],
			$myrow['typename'],
			$myrow['phoneno'],
			$myrow['faxno']);

		$j++;
		If ($j == 11 AND ($RowIndex+1 != $_SESSION['DisplayRecordsMax'])){
			$j=1;
			echo $TableHeader;
		}

    		$RowIndex++;
//end of page full new headings if
	}
//end of while loop
	echo '</TABLE>';
}
}

//end if results to show
if (!isset($_POST['CSV'])) {
if (isset($ListPageMax) and $ListPageMax>1) {
	echo "<P>&nbsp;&nbsp;" . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';

	echo '<SELECT NAME="PageOffset">';

	$ListPage=1;
	while($ListPage <= $ListPageMax) {
		if ($ListPage == $_POST['PageOffset']) {
			echo '<OPTION VALUE=' . $ListPage . ' SELECTED>' . $ListPage . '</OPTION>';
		} else {
			echo '<OPTION VALUE=' . $ListPage . '>' . $ListPage . '</OPTION>';
		}
		$ListPage++;
	}
	echo '</SELECT>
		<INPUT TYPE=SUBMIT NAME="Go" VALUE="' . _('Go') . '">
		<INPUT TYPE=SUBMIT NAME="Previous" VALUE="' . _('Previous') . '">
		<INPUT TYPE=SUBMIT NAME="Next" VALUE="' . _('Next') . '">';
}
//end if results to show
echo '</FORM></CENTER>';
}

// Only display the geocode map if the integration is turned on, and there is a latitude/longitude to display
If ($_SESSION['CustomerID']!=0) {
if ($_SESSION['geocode_integration']==1){
echo '<center><br>';
if ($lat ==0){
echo '<center>' . _('Mapping is enabled, but no Mapping data to display for this Customer.') . '<center>';
} else {
echo '<TR><TD colspan=2>';
echo '<CENTER><TABLE WIDTH=45% COLSPAN=2 BORDER=2 CELLPADDING=4>';
echo "<TR>
                <TH WIDTH=33%>" . _('Customer Mapping') . "</TH>
        </TR>";
echo '</TD><TD VALIGN=TOP>'; /* Mapping */
echo '<center>' . _('Mapping is enabled, Map will display below.') . '<center>';
echo '<center><div align="center" id="map" style="width: '. $map_width . 'px; height: ' .  $map_height  . 'px"></div></center><br>';
echo "</th></tr></table></center>";
}}
// Extended Customer Info only if selected in Configuration
if ($_SESSION['Extended_CustomerInfo']==1){
if ($_SESSION['CustomerID']!=0){
$sql = "SELECT debtortype.typeid, debtortype.typename
                        FROM debtorsmaster, debtortype
			WHERE debtorsmaster.typeid = debtortype.typeid
			AND debtorsmaster.debtorno = '" . $_SESSION['CustomerID'] . "'
                        ";
$ErrMsg = _('An error occurred in retrieving the information');
$result = DB_query($sql, $db, $ErrMsg);
$myrow = DB_fetch_array($result);
$CustomerType = $myrow['typeid'];
$CustomerTypeName = $myrow['typename'];
// Customer Data
echo '<center><br>';
// Select some basic data about the Customer
$SQL = "SELECT debtorsmaster.clientsince,
		(TO_DAYS(date(now())) - TO_DAYS(date(debtorsmaster.clientsince))) as customersincedays,
		(TO_DAYS(date(now())) - TO_DAYS(date(debtorsmaster.lastpaiddate))) as lastpaiddays,
		debtorsmaster.paymentterms, debtorsmaster.lastpaid, debtorsmaster.lastpaiddate
                FROM debtorsmaster
                WHERE debtorsmaster.debtorno ='" . $_SESSION['CustomerID'] . "'";
        $DataResult = DB_query($SQL,$db);
        $myrow = DB_fetch_array($DataResult);
// Select some more data about the customer
$SQL = "select sum(ovamount+ovgst) as total from debtortrans where debtorno = '" . $_SESSION['CustomerID'] . "' and type !=12";
        $Total1Result = DB_query($SQL,$db);
        $row = DB_fetch_array($Total1Result);
echo '<TR><TD colspan=2>';
echo '<CENTER><TABLE WIDTH=45% COLSPAN=2 BORDER=2 CELLPADDING=4>';
        echo "<TR>
                <TH WIDTH=33%>" . _('Customer Data') . "</TH>
        </TR>";
echo '<TR><TD VALIGN=TOP>';    /* Customer Data */
//echo _('Distance to this customer:') . '<b>TBA</b><br>';
echo _('Last Paid Date:') . ' <b>' . ConvertSQLDate($myrow['lastpaiddate']) . '</b> ' . $myrow['lastpaiddays'] . ' ' . _('days') . '<br>';
echo _('Last Paid Amount (inc tax):') . ' <b>' . number_format($myrow['lastpaid'],2) . '</b><br>';
echo _('Customer since:') . ' <b>' . ConvertSQLDate($myrow['clientsince']) . '</b> ' . $myrow['customersincedays'] . ' ' .  _('days') . '<br>';
echo _('Total Spend from this Customer (inc tax):') . ' <b>' . number_format($row['total'],2) . '</b><br>';
echo _('Customer Type:') . ' <b>' . $CustomerTypeName . '</b><br>';
echo '</th></tr></table>';
}
// Customer Contacts
echo '<TR><TD colspan=2>';
  	$sql = 'SELECT * FROM custcontacts where debtorno="' . $_SESSION['CustomerID'] . '" ORDER BY contid';
	$result = DB_query($sql,$db);
if (DB_num_rows($result)<>0){
	echo '<br><center>Customer Contacts</center><br>';
	echo '<CENTER><table border=1 width=45%>';
	echo '<tr>
                        <th>' . _('Name') . '</th>
                        <th>' . _('Role') . '</th>
                        <th>' . _('Phone Number') . '</th>
                        <th>' . _('Notes') . '</th>
                        <th>' . _('Edit') . '</th>
                        <th>' . _('Delete') . '</th>
			<th> <a href="AddCustomerContacts.php?DebtorNo=' . $_SESSION['CustomerID'] . '">' . _('Add New Contact') . '</a> </th></tr>';
        $k=0; //row colour counter
        while ($myrow = DB_fetch_array($result)) {
                if ($k==1){
                        echo '<tr class="OddTableRows">';
                        $k=0;
                } else {
                        echo '<tr class="EvenTableRows">';
                        $k=1;
                }
                printf('<td>%s</td>
                                <td>%s</td>
                                <td>%s</td>
                                <td>%s</td>
                                <td><a href="AddCustomerContacts.php?Id=%s&DebtorNo=%s">'. _('Edit'). '</a></td>
                                <td><a href="AddCustomerContacts.php?Id=%s&DebtorNo=%s&delete=1">'. _('Delete'). '</a></td>
                                </tr>',
                                $myrow[2],
                                $myrow[3],
                                $myrow[4],
                                $myrow[5],
                                $myrow[0],
                                $myrow[1],
                                $myrow[0],
                                $myrow[1],
                                $myrow[1]);

	}//END WHILE LIST LOOP
	echo '</CENTER></table>';
} else {
if ($_SESSION['CustomerID']!=0){
echo '<center><br><a href="AddCustomerContacts.php?DebtorNo=' . $_SESSION['CustomerID'] . '">' . _('Add New Contact') . '</a><br></center>';
}
}
// Customer Notes
echo '<TR><TD colspan=2>';
        $sql = 'SELECT * FROM custnotes where debtorno="' . $_SESSION['CustomerID'] . '" ORDER BY date DESC';
        $result = DB_query($sql,$db);
if (DB_num_rows($result)<>0){
echo '<br><center>Customer Notes</center><br>';
        echo '<CENTER><table border=1 width=45%>';
        echo '<tr>
                        <th>' . _('date') . '</th>
                        <th>' . _('note') . '</th>
                        <th>' . _('hyperlink') . '</th>
                        <th>' . _('priority') . '</th>
                        <th>' . _('Edit') . '</th>
                        <th>' . _('Delete') . '</th>
			<th> <a href="AddCustomerNotes.php?DebtorNo=' . $_SESSION['CustomerID'] . '">' . _('Add New Note') . '</a> </th></tr>';
        $k=0; //row colour counter
        while ($myrow = DB_fetch_array($result)) {
                if ($k==1){
                        echo '<tr class="OddTableRows">';
                        $k=0;
                } else {
                        echo '<tr class="EvenTableRows">';
                        $k=1;
                }
                printf('<td>%s</td>
                                <td>%s</td>
                                <td>%s</td>
                                <td>%s</td>
                                <td><a href="AddCustomerNotes.php?Id=%s&DebtorNo=%s">'. _('Edit'). '</a></td>
                                <td><a href="AddCustomerNotes.php?Id=%s&DebtorNo=%s&delete=1">'. _('Delete'). '</a></td>
                                </tr>',
                                $myrow[4],
                                $myrow[3],
                                $myrow[2],
                                $myrow[5],
                                $myrow[0],
                                $myrow[1],
                                $myrow[0],
                                $myrow[1],
                                $myrow[1]);

        }//END WHILE LIST LOOP
        echo '</CENTER></table>';
} else {
if ($_SESSION['CustomerID']!=0){
echo '<center><br><a href="AddCustomerNotes.php?DebtorNo=' . $_SESSION['CustomerID'] . '">' . _('Add New Note for this Customer') . '</a><br></center>';
}
}
// Custome Type Notes
echo '<TR><TD colspan=2>';
        $sql = 'SELECT * FROM debtortypenotes where typeid="' . $CustomerType . '" ORDER BY date DESC';
        $result = DB_query($sql,$db);
if (DB_num_rows($result)<>0){
echo '<br><center>Customer Type (Group) Notes for: ' . $CustomerTypeName . '</center><br>';
        echo '<CENTER><table border=1 width=45%>';
        echo '<tr>
                        <th>' . _('date') . '</th>
                        <th>' . _('note') . '</th>
                        <th>' . _('file link / refernce / URL') . '</th>
                        <th>' . _('priority') . '</th>
                        <th>' . _('Edit') . '</th>
                        <th>' . _('Delete') . '</th>
                        <th> <a href="AddCustomerTypeNotes.php?DebtorType=' . $CustomerType . '">' . _('Add New Group Note') . '</a> </th></tr>';
        $k=0; //row colour counter
        while ($myrow = DB_fetch_array($result)) {
                if ($k==1){
                        echo '<tr class="OddTableRows">';
                        $k=0;
                } else {
                        echo '<tr class="EvenTableRows">';
                        $k=1;
                }
                printf('<td>%s</td>
                                <td>%s</td>
                                <td>%s</td>
                                <td>%s</td>
                                <td><a href="AddCustomerTypeNotes.php?Id=%s&DebtorType=%s">'. _('Edit'). '</a></td>
                                <td><a href="AddCustomerTypeNotes.php?Id=%s&DebtorType=%s&delete=1">'. _('Delete'). '</a></td>
                                </tr>',
                                $myrow[4],
                                $myrow[3],
                                $myrow[2],
                                $myrow[5],
                                $myrow[0],
                                $myrow[1],
                                $myrow[0],
                                $myrow[1],
                                $myrow[1]);

        }//END WHILE LIST LOOP
        echo '</CENTER></table>';
} else {
if ($_SESSION['CustomerID']!=0){
echo '<center><br><a href="AddCustomerTypeNotes.php?DebtorNo=' . $_SESSION['CustomerID'] . '">' . _('Add New Group Note') . '</a><br></center>';
}}}
}
//}
include('includes/footer.inc');
?>
<script language="JavaScript" type="text/javascript">
    //<![CDATA[
            <!--
            document.forms[0].CustCode.select();
            document.forms[0].CustCode.focus();
            //-->
    //]]>
</script>

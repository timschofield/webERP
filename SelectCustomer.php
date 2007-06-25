<?php
/* $Revision: 1.22 $ */

$PageSecurity = 2;

include('includes/session.inc');
$title = _('Search Customers');
include('includes/header.inc');

include('includes/Wiki.php');

$msg="";
if (!isset($_SESSION['CustomerID'])){ //initialise if not already done
	$_SESSION['CustomerID']="";
}

if (!isset($_POST['PageOffset'])) {
  $_POST['PageOffset'] = 1;
} else {
  if ($_POST['PageOffset']==0) {
    $_POST['PageOffset'] = 1;
  }
}

if (isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
	if (isset($_POST['Search'])){
		$_POST['PageOffset'] = 1;
	}
	If ($_POST['Keywords'] AND (($_POST['CustCode']) OR ($_POST['CustPhone']))) {
		$msg=_('Customer name keywords have been used in preference to customer code or phone  entered') . '.';
		$_POST['Keywords'] = strtoupper($_POST['Keywords']);
	}
	If (($_POST['CustCode']) AND ($_POST['CustPhone'])) {
		$msg=_('Customer code has been used in preference to the customer phone entered') . '.';
	}
	If (($_POST['Keywords']=="") AND ($_POST['CustCode']=="") AND ($_POST['CustPhone']=="")) {
			
		$SQL= "SELECT debtorsmaster.debtorno,
				debtorsmaster.name,
				custbranch.brname,
				custbranch.contactname,
				custbranch.phoneno,
				custbranch.faxno
			FROM debtorsmaster LEFT JOIN custbranch
				ON debtorsmaster.debtorno = custbranch.debtorno
			ORDER BY debtorsmaster.debtorno";
		
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
				custbranch.brname,
				custbranch.contactname,
				custbranch.phoneno,
				custbranch.faxno
			FROM debtorsmaster LEFT JOIN custbranch
				ON debtorsmaster.debtorno = custbranch.debtorno
			WHERE debtorsmaster.name " . LIKE . " '$SearchString'
			ORDER BY debtorsmaster.debtorno";

		} elseif (strlen($_POST['CustCode'])>0){

			$_POST['CustCode'] = strtoupper(trim($_POST['CustCode']));
				$SQL = "SELECT debtorsmaster.debtorno,
				debtorsmaster.name,
				custbranch.brname,
				custbranch.contactname,
				custbranch.phoneno,
				custbranch.faxno
			FROM debtorsmaster LEFT JOIN custbranch
				ON debtorsmaster.debtorno = custbranch.debtorno
			WHERE debtorsmaster.debtorno " . LIKE  . " '%" . $_POST['CustCode'] . "%'
			ORDER BY debtorsmaster.debtorno";
		} elseif (strlen($_POST['CustPhone'])>0){
			$SQL = "SELECT debtorsmaster.debtorno,
				debtorsmaster.name,
				custbranch.brname,
				custbranch.contactname,
				custbranch.phoneno,
				custbranch.faxno
			FROM debtorsmaster LEFT JOIN custbranch
				ON debtorsmaster.debtorno = custbranch.debtorno
			WHERE custbranch.phoneno " . LIKE  . " '%" . $_POST['CustPhone'] . "%'
			ORDER BY custbranch.debtorno";
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
	AND !isset($_POST['CustPhone']))) {

	If ($_POST['Select']!=""){
		$SQL = "SELECT name FROM debtorsmaster WHERE debtorno='" . $_POST['Select'] . "'";
		$_SESSION['CustomerID'] = $_POST['Select'];
	} else {
		$SQL = "SELECT name FROM debtorsmaster WHERE debtorno='" . $_SESSION['CustomerID'] . "'";
	}

	$ErrMsg = _('The customer name requested cannot be retrieved because');
	$result = DB_query($SQL,$db,$ErrMsg);

	if ($myrow=DB_fetch_row($result)){
		$CustomerName = $myrow[0];
	}
	unset($result);
	echo '<CENTER><FONT SIZE=3>' . _('Customer') . ' :<B> ' . $_SESSION['CustomerID'] . ' - ' . $CustomerName . '</B> ' . _('has been selected') . '.<BR>' . _('Select a menu option to operate using this customer') . '.</FONT><BR>';

	$_POST['Select'] = NULL;

	echo "<TABLE BORDER=2 CELLPADDING=4><TR><TD class='tableheader'>" . _('Customer Inquiries') . "</TD>
			<TD class='tableheader'>" . _('Customer Maintenance') . "</TD></TR>";

	echo '<TR><TD WIDTH=50%>';

	/* Customer Inquiry Options */
	echo '<a href="' . $rootpath . '/CustomerInquiry.php?CustomerID=' . $_SESSION['CustomerID'] . '">' . _('Customer Transaction Inquiries') . '</a><BR>';
	echo '<a href="' . $rootpath . '/SelectSalesOrder.php?SelectedCustomer=' . $_SESSION['CustomerID'] . '">' . _('Modify Outstanding Sales Orders') . '</a><BR>';
	echo '<a href="' . $rootpath . '/SelectCompletedOrder.php?SelectedCustomer=' . $_SESSION['CustomerID'] . '">' . _('Order Inquiries') . '</a><BR>';

	wikiLink('Customer', $_SESSION['CustomerID']);

	echo '</TD><TD WIDTH=50%>';

        echo '<a href="' . $rootpath . '/Customers.php?">' . _('Add a New Customer') . '</a><br>';
	echo '<a href="' . $rootpath . '/Customers.php?DebtorNo=' . $_SESSION['CustomerID'] . '">' . _('Modify Customer Details') . '</a><BR>';
	echo '<a href="' . $rootpath . '/CustomerBranches.php?DebtorNo=' . $_SESSION['CustomerID'] . '">' . _('Add/Modify/Delete Customer Branches') . '</a><BR>';

	echo '<a href="' . $rootpath . '/SelectProduct.php">' . _('Special Customer Prices') . '</a><BR>';
	echo '<a href="' . $rootpath . '/CustEDISetup.php">' . _('Customer EDI Configuration') . '</a>';


	echo '</TD></TR></TABLE><BR></CENTER>';
} else {
	echo "<CENTER><TABLE WIDTH=50% BORDER=2><TR><TD class='tableheader'>" . _('Customer Inquiries') . "</TD>
			<TD class='tableheader'>" . _('Customer Maintenance') . "</TD></TR>";

	echo '<TR><TD WIDTH=50%>';

	echo '</TD><TD WIDTH=50%>';
  	if ($_SESSION['SalesmanLogin']==''){
    	echo '<a href="' . $rootpath . '/Customers.php?">' . _('Add a New Customer') . '</a><br>';
    }
	echo '</TD></TR></TABLE><BR></CENTER>';
}

?>

<FORM ACTION="<?php echo $_SERVER['PHP_SELF'] . '?' . SID; ?>" METHOD=POST>
<CENTER>
<B><?php echo $msg; ?></B>
<TABLE CELLPADDING=3 COLSPAN=4>
<TR>
<TD><B><?php echo _('Name'); ?></B>:</TD>
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
<TD><B><?php echo _('Code'); ?></B>:</TD>
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
<TD><B><?php echo _('Phone'); ?></B>:</TD>
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
</TR>
</TABLE>
<INPUT TYPE=SUBMIT NAME="Search" VALUE="<?php echo _('Search Now'); ?>">
</CENTER>


<?php
if ($_SESSION['SalesmanLogin']!=''){
	prnMsg(_('Your account enables you to see only customers allocated to you'),'warn',_('Note: Sales-person Login'));
}

If (isset($result)) {
  $ListCount=DB_num_rows($result);
  $ListPageMax=ceil($ListCount/$_SESSION['DisplayRecordsMax']);

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


	echo '<TABLE CELLPADDING=2 COLSPAN=7 BORDER=2>';
	$TableHeader = '<TR>
				<TD Class="tableheader">' . _('Code') . '</TD>
				<TD Class="tableheader">' . _('Customer Name') . '</TD>
				<TD Class="tableheader">' . _('Branch') . '</TD>
				<TD Class="tableheader">' . _('Contact') . '</TD>
				<TD Class="tableheader">' . _('Phone') . '</TD>
				<TD Class="tableheader">' . _('Fax') . '</TD>
			</TR>';

	echo $TableHeader;
	$j = 1;
	$k = 0; //row counter to determine background colour
  $RowIndex = 0;

  if (DB_num_rows($result)<>0){
  	DB_data_seek($result, ($_POST['PageOffset']-1)*$_SESSION['DisplayRecordsMax']);
  }

	while (($myrow=DB_fetch_array($result)) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {

		if ($k==1){
			echo '<tr bgcolor="#CCCCCC">';
			$k=0;
		} else {
			echo '<tr bgcolor="#EEEEEE">';
			$k=1;
		}

		printf("<td><FONT SIZE=1><INPUT TYPE=SUBMIT NAME='Select' VALUE='%s'</FONT></td>
			<td><FONT SIZE=1>%s</FONT></td>
			<td><FONT SIZE=1>%s</FONT></td>
			<td><FONT SIZE=1>%s</FONT></td>
			<td><FONT SIZE=1>%s</FONT></td>
			<td><FONT SIZE=1>%s</FONT></td></tr>",
			$myrow["debtorno"],
			$myrow["name"],
			$myrow["brname"],
			$myrow["contactname"],
			$myrow["phoneno"],
			$myrow["faxno"]);

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
//end if results to show
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
}
//end if results to show
echo '</FORM></CENTER>';











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
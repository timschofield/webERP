<?php
/* $Revision: 1.13 $ */

$PageSecurity = 2;

include('includes/session.inc');
$title = _('Search Customers');
include('includes/header.inc');

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
	If ($_POST['Keywords'] AND $_POST['CustCode']) {
		$msg=_('Customer name keywords have been used in preference to the customer code extract entered') . '.';
		$_POST['Keywords'] = strtoupper($_POST['Keywords']);
	}
	If ($_POST['Keywords']=="" AND $_POST['CustCode']=="") {
		$msg=_('At least one Customer Name keyword OR an extract of a Customer Code must be entered for the search');
	} else {
		If (strlen($_POST['Keywords'])>0) {

			$_POST['Keywords'] = strtoupper($_POST['Keywords']);

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
				WHERE debtorsmaster.name " . LIKE . " '$SearchString'";

		} elseif (strlen($_POST['CustCode'])>0){

			$_POST['CustCode'] = strtoupper($_POST['CustCode']);

			$SQL = "SELECT debtorsmaster.debtorno,
					debtorsmaster.name,
					custbranch.brname,
					custbranch.contactname,
					custbranch.phoneno,
					custbranch.faxno
				FROM debtorsmaster LEFT JOIN custbranch
					ON debtorsmaster.debtorno = custbranch.debtorno
				WHERE debtorsmaster.debtorno " . LIKE  . " '%" . $_POST['CustCode'] . "%'";
		}

		$ErrMsg = _('The searched customer records requested cannot be retrieved because');
		$result = DB_query($SQL,$db,$ErrMsg);
		if (DB_num_rows($result)==1){
			$myrow=DB_fetch_array($result);
			$_POST['Select'] = $myrow['debtorno'];
			unset($result);
		} elseif (DB_num_rows($result)==0){
			prnMsg(_('No customer records contain the selected text') . ' - ' . _('please alter your search criteria and try again'),'info');
		}

	} //one of keywords or custcode was more than a zero length string
} //end of if search


If (!isset($_POST['Select'])){
	$_POST['Select']="";
}

If ($_POST['Select']!="" OR 
	($_SESSION['CustomerID']!="" 
	AND !isset($_POST['Keywords']) 
	AND !isset($_POST['CustCode']))) {

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

	echo "<TABLE WIDTH=50% BORDER=2><TR><TD class='tableheader'>" . _('Customer Inquiries') . "</TD>
			<TD class='tableheader'>" . _('Customer Maintenance') . "</TD></TR>";
			
	echo '<TR><TD WIDTH=50%>';
	
	
	echo '<a href="' . $rootpath . '/CustomerInquiry.php?CustomerID=' . $_SESSION['CustomerID'] . '">' . _('Customer Transaction Inquiries') . '</a><BR>';
	echo '<a href="' . $rootpath . '/SelectSalesOrder.php?SelectedCustomer=' . $_SESSION['CustomerID'] . '">' . _('Modify Outstanding Sales Orders') . '</a><BR>';
	echo '<a href="' . $rootpath . '/SelectCompletedOrder.php?SelectedCustomer=' . $_SESSION['CustomerID'] . '">' . _('Order Inquiries') . '</a><BR>';
	
	echo '</TD><TD WIDTH=50%>';
	
	echo '<a href="' . $rootpath . '/Customers.php?DebtorNo=' . $_SESSION['CustomerID'] . '">' . _('Modify Customer Details') . '</a><BR>';
	echo '<a href="' . $rootpath . '/CustomerBranches.php?DebtorNo=' . $_SESSION['CustomerID'] . '">' . _('Add/Edit/Delete Customer Branch records') . '</a><BR>';
	
	echo '<a href="' . $rootpath . '/SelectProduct.php">' . _('Special Customer Prices') . '</a><BR>';
	echo '<a href="' . $rootpath . '/CustEDISetup.php">' . _('Customer EDI Configuration') . '</a>';

	
	echo '</TD></TR></TABLE><BR></CENTER>';
}

?>

<FORM ACTION="<?php echo $_SERVER['PHP_SELF'] . '?' . SID; ?>" METHOD=POST>
<CENTER>
<B><?php echo $msg; ?></B>
<TABLE CELLPADDING=3 COLSPAN=4>
<TR>
<TD><?php echo _('Text in the'); ?> <B><?php echo _('name'); ?></B>:</TD>
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
<TD><?php echo _('Text extract in the customer'); ?> <B><?php echo _('code'); ?></B>:</TD>
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
</TR>
</TABLE>
<INPUT TYPE=SUBMIT NAME="Search" VALUE="<?php echo _('Search Now'); ?>">
<INPUT TYPE=SUBMIT ACTION=RESET VALUE="<?php echo _('Reset'); ?>"></CENTER>


<?php

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

  echo "&nbsp;&nbsp;" . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
?>

  <select name="PageOffset">

<?php
  $ListPage=1;
  while($ListPage<=$ListPageMax) {
	  if ($ListPage==$_POST['PageOffset']) {
?>

  		<option value=<?php echo($ListPage); ?> selected><?php echo($ListPage); ?></option>

<?php
	  } else {
?>

		  <option value=<?php echo($ListPage); ?>><?php echo($ListPage); ?></option>

<?php
	  }
	  $ListPage=$ListPage+1;
  }
?>

  </select>
  <INPUT TYPE=SUBMIT NAME="Go" VALUE="<?php echo _('Go'); ?>">
  <INPUT TYPE=SUBMIT NAME="Previous" VALUE="<?php echo _('Previous'); ?>">
  <INPUT TYPE=SUBMIT NAME="Next" VALUE="<?php echo _('Next'); ?>">

<?php

  echo '<BR><BR>';

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

    $RowIndex = $RowIndex + 1;
//end of page full new headings if
	}
//end of while loop

	echo '</TABLE>';

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
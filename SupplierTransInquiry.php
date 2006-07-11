<?php

/* $Revision: 1.1 $ */

$PageSecurity = 2;

include('includes/session.inc');
$title = _('Supplier Transactions Inquiry');
include('includes/header.inc');


echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "' METHOD=POST>";

echo '<CENTER><TABLE CELLPADDING=2><TR>';

echo '<TD>' . _('Type') . ":</TD><TD><SELECT name='TransType'> ";

$sql = 'SELECT typeid, typename FROM systypes WHERE typeid >= 20 AND typeid <= 23';
$resultTypes = DB_query($sql,$db);

echo "<OPTION Value='All'> All";
while ($myrow=DB_fetch_array($resultTypes)){
	if (isset($_POST['TransType'])){
		if ($myrow['typeid'] == $_POST['TransType']){
		     echo "<OPTION SELECTED Value='" . $myrow['typeid'] . "'>" . $myrow['typename'];
		} else {
		     echo "<OPTION Value='" . $myrow['typeid'] . "'>" . $myrow['typename'];
		}
	} else {
		     echo "<OPTION Value='" . $myrow['typeid'] . "'>" . $myrow['typename'];
	}
}
echo '</SELECT></TD>';

if (!isset($_POST['FromDate'])){
	$_POST['FromDate']=Date($_SESSION['DefaultDateFormat'], mktime(0,0,0,Date('m'),1,Date('Y')));
}
if (!isset($_POST['ToDate'])){
	$_POST['ToDate'] = Date($_SESSION['DefaultDateFormat']);
}
echo '<TD>' . _('From') . ":</TD><TD><INPUT TYPE=TEXT NAME='FromDate' MAXLENGTH=10 SIZE=11 VALUE=" . $_POST['FromDate'] . '></TD>';
echo '<TD>' . _('To') . ":</TD><TD><INPUT TYPE=TEXT NAME='ToDate' MAXLENGTH=10 SIZE=11 VALUE=" . $_POST['ToDate'] . '></TD>';

echo "</TR></TABLE><INPUT TYPE=SUBMIT NAME='ShowResults' VALUE='" . _('Show Transactions') . "'>";
echo '<HR>';

echo '</FORM></CENTER>';

if (isset($_POST['ShowResults']) && $_POST['TransType'] != ''){
   $SQL_FromDate = FormatDateForSQL($_POST['FromDate']);
   $SQL_ToDate = FormatDateForSQL($_POST['ToDate']);
   $sql = "SELECT type,
		transno,
   		trandate,
		duedate,
		supplierno,
		suppname,
		suppreference,
		transtext,
		rate,
		diffonexch,
		alloc,
		ovamount+ovgst as totalamt,
		currcode,
		typename
	FROM supptrans
		INNER JOIN suppliers ON supptrans.supplierno=suppliers.supplierid
		INNER JOIN systypes ON supptrans.type = systypes.typeid
	WHERE ";

   $sql = $sql . "trandate >='" . $SQL_FromDate . "' AND trandate <= '" . $SQL_ToDate . "'";
	if  ($_POST['TransType']!='All')  {
		$sql .= " AND type = " . $_POST['TransType'];
	}
	$sql .=  " ORDER BY id";

   $TransResult = DB_query($sql, $db,$ErrMsg,$DbgMsg);
   $ErrMsg = _('The supplier transactions for the selected criteria could not be retrieved because') . ' - ' . DB_error_msg($db);
   $DbgMsg =  _('The SQL that failed was');

   echo '<TABLE CELLPADDING=2 BORDER=2>';

   $tableheader = "<TR>
			<TD class='tableheader'>" . _('Type') . "</TD>
			<TD class='tableheader'>" . _('Number') . "</TD>
			<TD class='tableheader'>" . _('Supp Ref') . "</TD>
			<TD class='tableheader'>" . _('Date') . "</TD>
			<TD class='tableheader'>" . _('Supplier') . "</TD>
			<TD class='tableheader'>" . _('Comments') . "</TD>
			<TD class='tableheader'>" . _('Due Date') . "</TD>
			<TD class='tableheader'>" . _('Ex Rate') . "</TD>
			<TD class='tableheader'>" . _('Amount') . "</TD>
			<TD class='tableheader'>" . _('Currency') . '</TD></TR>';
	echo $tableheader;

	$RowCounter = 1;
	$k = 0; //row colour counter

	while ($myrow=DB_fetch_array($TransResult)) {

		if ($k==1){
			echo "<tr bgcolor='#CCCCCC'>";
			$k=0;
		} else {
			echo "<tr bgcolor='#EEEEEE'>";
			$k++;
		}

		printf ("<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td ALIGN=RIGHT>%s</td>
			<td ALIGN=RIGHT>%s</td>
			<td>%s</td></tr>",
			$myrow['typename'],
			$myrow['transno'],
			$myrow['suppreference'],
			ConvertSQLDate($myrow['trandate']),
			$myrow['supplierno'] . ' - ' . $myrow['suppname'],
			$myrow['trantext'],
			ConvertSQLDate($myrow['duedate']),
			$myrow['rate'],
			number_format($myrow['totalamt'],2),
			$myrow['currcode']
		);
		

		$GLTransResult = DB_query('SELECT account, accountname, narrative, amount 
					FROM gltrans INNER JOIN chartmaster 
					ON gltrans.account=chartmaster.accountcode
					WHERE type=' . $myrow['type'] . ' 
					AND typeno=' . $myrow['transno'],
					$db,
					_('Could not retrieve the GL transactions for this AP transaction'));
		
		if (DB_num_rows($GLTransResult)==0){
			echo '<TR><TD COLSPAN=10>' . _('There are no GL transactions created for the above AP transaction') . '</TD></TR>';
		} else {
			echo '<TR><TD COLSPAN=2></TD><TD COLSPAN=8><TABLE>';
			echo '<TR BGCOLOR=#FFFFFF><TD COLSPAN=2><B>' . _('GL Account') . '</B></TD><TD><B>' . _('Local Amount') . '</B></TD><TD><B>' . _('Narrative') . '</B></TD></TR>';
			$CheckGLTransBalance =0;
			while ($GLTransRow = DB_fetch_array($GLTransResult)){

				printf('<TR>
					<TD>%s</TD>
					<TD>%s</TD>
					<TD ALIGN=RIGHT>%s</TD>
					<TD>%s</TD>
					</TR>',
					$GLTransRow['account'],
					$GLTransRow['accountname'],
					$GLTransRow['amount'],
					$GLTransRow['narrative']);
				
				$CheckGLTransBalance += $GLTransRow['amount'];
			}
			if (round($CheckGLTransBalance,5)!= 0){
				echo '<TR><TD COLSPAN=4 BGCOLOR=RED><B>' . _('The GL transactions for this AP transaction are out of balance by') .  ' ' . $CheckGLTransBalance . '</B></TD></TR>';
			}
			echo '</TABLE></TD></TR>';
		}

		$RowCounter++;
		If ($RowCounter == 12){
			$RowCounter=1;
			echo $tableheader;
		}
	//end of page full new headings if
	}
	//end of while loop

 echo '</TABLE>';
}

include('includes/footer.inc');

?>
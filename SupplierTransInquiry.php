<?php

/* $Revision: 1.3 $ */

$PageSecurity = 2;

include('includes/session.inc');
$title = _('Supplier Transactions Inquiry');
include('includes/header.inc');


echo "<form action='" . $_SERVER['PHP_SELF'] . "' method=post>";

echo '<table cellpadding=2><tr>';

echo '<td>' . _('Type') . ":</td><td><select name='TransType'> ";

$sql = 'SELECT typeid, typename FROM systypes WHERE typeid >= 20 AND typeid <= 23';
$resultTypes = DB_query($sql,$db);

echo "<option Value='All'> All";
while ($myrow=DB_fetch_array($resultTypes)){
	if (isset($_POST['TransType'])){
		if ($myrow['typeid'] == $_POST['TransType']){
		     echo "<option selected Value='" . $myrow['typeid'] . "'>" . $myrow['typename'];
		} else {
		     echo "<option Value='" . $myrow['typeid'] . "'>" . $myrow['typename'];
		}
	} else {
		     echo "<option Value='" . $myrow['typeid'] . "'>" . $myrow['typename'];
	}
}
echo '</select></td>';

if (!isset($_POST['FromDate'])){
	$_POST['FromDate']=Date($_SESSION['DefaultDateFormat'], mktime(0,0,0,Date('m'),1,Date('Y')));
}
if (!isset($_POST['ToDate'])){
	$_POST['ToDate'] = Date($_SESSION['DefaultDateFormat']);
}
echo '<td>' . _('From') . ":</td><td><input type=TEXT class='date' alt='".$_SESSION['DefaultDateFormat']. "' name='FromDate' maxlength=10 size=11 VALUE=" . $_POST['FromDate'] . '></td>';
echo '<td>' . _('To') . ":</td><td><input type=TEXT class='date' alt='".$_SESSION['DefaultDateFormat']. "' name='ToDate' maxlength=10 size=11 VALUE=" . $_POST['ToDate'] . '></td>';

echo "</tr></table><div class='centre'><input type=submit name='ShowResults' VALUE='" . _('Show Transactions') . "'>";
echo '<hr>';

echo '</form></div>';

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

   $TransResult = DB_query($sql, $db);
   $ErrMsg = _('The supplier transactions for the selected criteria could not be retrieved because') . ' - ' . DB_error_msg($db);
   $DbgMsg =  _('The SQL that failed was');

   echo '<table cellpadding=2 BORDER=2>';

   $tableheader = "<tr>
			<th>" . _('Type') . "</th>
			<th>" . _('Number') . "</th>
			<th>" . _('Supp Ref') . "</th>
			<th>" . _('Date') . "</th>
			<th>" . _('Supplier') . "</th>
			<th>" . _('Comments') . "</th>
			<th>" . _('Due Date') . "</th>
			<th>" . _('Ex Rate') . "</th>
			<th>" . _('Amount') . "</th>
			<th>" . _('Currency') . '</th></tr>';
	echo $tableheader;

	$RowCounter = 1;
	$k = 0; //row colour counter

	while ($myrow=DB_fetch_array($TransResult)) {

		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';;
			$k++;
		}

		printf ("<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td align=right>%s</td>
			<td align=right>%s</td>
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
			echo '<tr><td colspan=10>' . _('There are no GL transactions created for the above AP transaction') . '</td></tr>';
		} else {
			echo '<tr><td colspan=2></td><td colspan=8><table>';
			echo '<tr bgcolor=#FFFFFF><td colspan=2><b>' . _('GL Account') . '</b></td><td><b>' . _('Local Amount') . '</b></td><td><b>' . _('Narrative') . '</b></td></tr>';
			$CheckGLTransBalance =0;
			while ($GLTransRow = DB_fetch_array($GLTransResult)){

				printf('<tr>
					<td>%s</td>
					<td>%s</td>
					<td align=right>%s</td>
					<td>%s</td>
					</tr>',
					$GLTransRow['account'],
					$GLTransRow['accountname'],
					$GLTransRow['amount'],
					$GLTransRow['narrative']);
				
				$CheckGLTransBalance += $GLTransRow['amount'];
			}
			if (round($CheckGLTransBalance,5)!= 0){
				echo '<tr><td colspan=4 bgcolor=RED><b>' . _('The GL transactions for this AP transaction are out of balance by') .  ' ' . $CheckGLTransBalance . '</b></td></tr>';
			}
			echo '</table></td></tr>';
		}

		$RowCounter++;
		If ($RowCounter == 12){
			$RowCounter=1;
			echo $tableheader;
		}
	//end of page full new headings if
	}
	//end of while loop

 echo '</table>';
}

include('includes/footer.inc');

?>

<?php
/* $Revision: 1.0 $ */

$PageSecurity = 6;

include('includes/session.inc');
$title = _('Authorization of Petty Cash Expenses');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

if (isset($_POST['SelectedTabs'])){
	$SelectedTabs = strtoupper($_POST['SelectedTabs']);
} elseif (isset($_GET['SelectedTabs'])){
	$SelectedTabs = strtoupper($_GET['SelectedTabs']);
}

if (isset($_POST['SelectedIndex'])){
	$SelectedIndex = $_POST['SelectedIndex'];
} elseif (isset($_GET['SelectedIndex'])){
	$SelectedIndex = $_GET['SelectedIndex'];
}

if (isset($_POST['Days'])){
	$Days = $_POST['Days'];
} elseif (isset($_GET['Days'])){
	$Days = $_GET['Days'];
}

if (isset($Errors)) {
	unset($Errors);
}

$Errors = array();


echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/magnifier.png" title="' . _('Petty Cash') . '" alt="">' . ' <a href="' . $_SERVER['PHP_SELF'] . '?' . SID . '">' . _('Authorization Of Petty Cash Expenses ') . ''.$SelectedTabs.'<a/>';
	
if (isset($_POST['submit']) or isset($_POST['update']) OR isset($SelectedTabs) OR isset ($_POST['GO'])) {
	
	echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . '?' . SID . '>';
	echo "<div class='centre'><p>" . _('Detail Of Movement For Last ') .': ';
 
	if(!isset ($Days)){
		$Days=30;
	}
	echo "<input type=hidden name='SelectedTabs' VALUE=" . $SelectedTabs . ">";
	echo "<input type=text class=number name='Days' VALUE=" . $Days . " MAXLENGTH =3 size=4> Days ";
	echo '<input type=submit name="Go" value="' . _('Go') . '">';
	echo '<p></div></form>';
	
	$sql = "SELECT pcashdetails.counterindex,
				pcashdetails.tabcode,
				pcashdetails.date,
				pcashdetails.codeexpense,
				pcashdetails.amount,
				pcashdetails.authorized,
				pcashdetails.posted,
				pcashdetails.notes,
				pcashdetails.receipt,
				pctabs.glaccountassignment,
				pctabs.glaccountpcash,
				pctabs.usercode,
				pctabs.currency,
				currencies.rate
			FROM pcashdetails, pctabs, currencies
			WHERE pcashdetails.tabcode = pctabs.tabcode
				AND pctabs.currency = currencies.currabrev
				AND pcashdetails.tabcode = '$SelectedTabs'
				AND pcashdetails.date >= DATE_SUB(CURDATE(), INTERVAL ".$Days." DAY)
			ORDER BY pcashdetails.date, pcashdetails.counterindex ASC";
	
	$result = DB_query($sql,$db);
	
	echo '<br><table BORDER=1>';
	echo "<tr>
		<th>" . _('Date') . "</th>
		<th>" . _('Expense Code') . "</th>
		<th>" . _('Amount') . "</th>
		<th>" . _('Posted') . "</th>
		<th>" . _('Notes') . "</th>
		<th>" . _('Receipt') . "</th>
		<th>" . _('Authorized') . "</th>
	</tr>";

	$k=0; //row colour counter
	echo'<form action="PcAuthorizeExpenses.php" method="POST" name="'._('update').'">';
	
	while ($myrow=DB_fetch_array($result))	{
	
		//update database if update pressed 
		if (($_POST['submit']=='Update') AND isset($_POST[$myrow['counterindex']])){		

			$PeriodNo = GetPeriod(ConvertSQLDate($myrow['date']), $db);

			if ($myrow['rate'] == 1){ // functional currency
				$Amount = $myrow['amount'];
			}else{ // other currencies
				$Amount = $myrow['amount']/$myrow['rate'];
			}

			if ($myrow['codeexpense'] == 'ASSIGNCASH'){
				$type = 2; 
				$AccountFrom = $myrow['glaccountassignment'];
				$AccountTo = $myrow['glaccountpcash'];
			}else{
				$type = 1; 
				$Amount = -$Amount;
				$AccountFrom = $myrow['glaccountpcash'];
				$SQLAccExp = "SELECT glaccount
								FROM pcexpenses
								WHERE codeexpense = '".$myrow['codeexpense']."'";
				$ResultAccExp = DB_query($SQLAccExp,$db);
				$myrowAccExp = DB_fetch_array($ResultAccExp);
				$AccountTo = $myrowAccExp['glaccount'];
			}
			
			//get typeno
			$typeno = GetNextTransNo($type,$db);

			//build narrative
			$narrative= "PettyCash - ".$myrow['tabcode']." - ".$myrow['codeexpense']." - ".$myrow['notes']." - ".$myrow['receipt']."";
			//insert to gltrans

			$sqlFrom="INSERT INTO `gltrans` 
					(`counterindex`, 
					`type`, 
					`typeno`, 
					`chequeno`, 
					`trandate`, 
					`periodno`, 
					`account`, 
					`narrative`, 
					`amount`, 
					`posted`, 
					`jobref`, 
					`tag`) 
			VALUES (NULL,
					'".$type."',
					'".$typeno."',
					0,
					'".$myrow['date']."',
					'".$PeriodNo."',
					'".$AccountFrom."',
					'".$narrative."',
					'".-$Amount."',
					0,
					'',
					0)";
					
			$ResultFrom = DB_Query($sqlFrom, $db);

			$sqlTo="INSERT INTO `gltrans` 
					(`counterindex`, 
					`type`, 
					`typeno`, 
					`chequeno`, 
					`trandate`, 
					`periodno`, 
					`account`, 
					`narrative`, 
					`amount`, 
					`posted`, 
					`jobref`, 
					`tag`) 
			VALUES (NULL,
					'".$type."',
					'".$typeno."',
					0,
					'".$myrow['date']."',
					'".$PeriodNo."',
					'".$AccountTo."',
					'".$narrative."',
					'".$Amount."',
					0,
					'',
					0)";
					
			$ResultTo = DB_Query($sqlTo, $db);
			
			if ($myrow['codeexpense'] == 'ASSIGNCASH'){
			// if it's a cash assignation we need to updated banktrans table as well.
				$ReceiptTransNo = GetNextTransNo( 2, $db);
				$SQLBank= 'INSERT INTO banktrans (transno,
							type,
							bankact,
							ref,
							exrate,
							functionalexrate,
							transdate,
							banktranstype,
							amount,
							currcode)
					VALUES (' . $ReceiptTransNo . ',
						1,
						' . $AccountFrom . ", '"
						. $narrative . " ',
						1,
						" . $myrow['rate'] . ",
						'" . $myrow['date'] . "',
						'Cash',
						" . -$myrow['amount'] . ",
						'" . $myrow['currency'] . "'
					)";
				$ErrMsg = _('Cannot insert a bank transaction because');
				$DbgMsg =  _('Cannot insert a bank transaction with the SQL');
				$resultBank = DB_query($SQLBank,$db,$ErrMsg,$DbgMsg,true);

			}

			$sql = "UPDATE pcashdetails
					SET authorized = '".Date('Y-m-d')."',
					posted = 1
					WHERE counterindex = '".$myrow['counterindex']."'";
			$resultupdate = DB_query($sql,$db);
		}
		
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}

		echo'   <td>'.ConvertSQLDate($myrow['date']).'</td>
			<td>'.$myrow['codeexpense'].'</td>
			<td class="number">'.number_format($myrow['amount'],2).'</td>
			<td>'.$myrow['posted'].'</td>
			<td>'.$myrow['notes'].'</td>
			<td>'.$myrow['receipt'].'</td>';
			
		if (isset($_POST[$myrow['counterindex']])){
			echo'<td>'.ConvertSQLDate(Date('Y-m-d')).'</td>';
		}else{
			$Authorizer=ConvertSQLDate($myrow['authorized']);
			if(($Authorizer!='00/00/0000')){
				echo'<td>'.ConvertSQLDate($myrow['authorized']).'</td>';
			}else{
				echo '<td align=right><input type="checkbox" name="'.$myrow['counterindex'].'">';
			}
		}

		echo "<input type=hidden name='SelectedIndex' VALUE=" . $myrow['counterindex']. ">";
		echo "<input type=hidden name='SelectedTabs' VALUE=" . $SelectedTabs . ">";
		echo "<input type=hidden name='Days' VALUE=" .$Days. ">";
		echo'		</tr> ';
	
	
	} //end of looping
	
	// Do the postings 
	include ('includes/GLPostings.inc');	
	
	echo'<tr><td style="text-align:right" colspan=4><input type=submit name=submit value=' . _("Update") . '></td></tr></form>';

} else { /*The option to submit was not hit so display form */


echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . '?' . SID . '>';
	echo '<p><table border=1>'; //Main table
	echo '<td><table>'; // First column

echo '<tr><td>' . _('Authorize expenses to Petty Cash Tab') . ":</td><td><select name='SelectedTabs'>";

	DB_free_result($result);
	$SQL = "SELECT tabcode
		FROM pctabs
		WHERE authorizer='" . $_SESSION['UserID'] . "'";

	$result = DB_query($SQL,$db);

	while ($myrow = DB_fetch_array($result)) {
		if (isset($_POST['SelectTabs']) and $myrow['tabcode']==$_POST['SelectTabs']) {
			echo "<option selected VALUE='";
		} else {
			echo "<option VALUE='";
		}
		echo $myrow['tabcode'] . "'>" . $myrow['tabcode'];
	
	} //end while loop get type of tab

	echo '</select></td></tr>';
	
	   	echo '</table>'; // close table in first column
   	echo '</td></tr></table>'; // close main table

	echo '<p><div class="centre"><input type=submit name=process VALUE="' . _('Accept') . '"><input type=submit name=Cancel VALUE="' . _('Cancel') . '"></div>';
		
	echo '</form>';	
} /*end of else not submit */
include('includes/footer.inc');
?>

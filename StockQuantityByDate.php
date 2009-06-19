<?php


/* $Revision: 1.9 $ */
/* Contributed by Chris Bice - gettext by Kitch*/


$PageSecurity = 2;

include('includes/session.inc');
$title = _('Stock On Hand By Date');
include('includes/header.inc');

echo "<hr><form action='" . $_SERVER['PHP_SELF'] . "?". SID . "' method=post>";

$sql = 'SELECT categoryid, categorydescription FROM stockcategory';
$resultStkLocs = DB_query($sql, $db);

echo '<table><tr>';
echo '<td>' . _('For Stock Category') . ":</td>
	<td><select name='StockCategory'> ";

while ($myrow=DB_fetch_array($resultStkLocs)){
	if (isset($_POST['StockCategory']) AND $_POST['StockCategory']!='All'){
		if ($myrow['categoryid'] == $_POST['StockCategory']){
		     echo "<option selected VALUE='" . $myrow['categoryid'] . "'>" . $myrow['categorydescription'];
		} else {
		     echo "<option VALUE='" . $myrow['categoryid'] . "'>" . $myrow['categorydescription'];
		}
	}else {
		 echo "<option VALUE='" . $myrow['categoryid'] . "'>" . $myrow['categorydescription'];
	}
}
echo '</select></td>';

$sql = 'SELECT loccode, locationname FROM locations';
$resultStkLocs = DB_query($sql, $db);

echo '<td>' . _('For Stock Location') . ":</td>
	<td><select name='StockLocation'> ";

while ($myrow=DB_fetch_array($resultStkLocs)){
	if (isset($_POST['StockLocation']) AND $_POST['StockLocation']!='All'){
		if ($myrow['loccode'] == $_POST['StockLocation']){
		     echo "<option selected VALUE='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
		} else {
		     echo "<option VALUE='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
		}
	} elseif ($myrow['loccode']==$_SESSION['UserStockLocation']){
		 echo "<option selected VALUE='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
		 $_POST['StockLocation']=$myrow['loccode'];
	} else {
		 echo "<option VALUE='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
	}
}
echo '</select></td>';

if (!isset($_POST['OnHandDate'])){
	$_POST['OnHandDate'] = Date($_SESSION['DefaultDateFormat'], Mktime(0,0,0,Date("m"),0,Date("y")));
}

echo '<td>' . _("On-Hand On Date") . ":</td>
	<td><input type=TEXT class='date' alt='".$_SESSION['DefaultDateFormat']."' name='OnHandDate' size=12 maxlength=12 VALUE='" . $_POST['OnHandDate'] . "'></td></tr>";
echo "<tr><td colspan=6><div class='centre'><input type=submit name='ShowStatus' VALUE='" . _('Show Stock Status') ."'></div></td></tr></table>";
echo '</form><hr>';

$TotalQuantity = 0;

if(isset($_POST['ShowStatus']) AND is_date($_POST['OnHandDate']))
{
	$sql = "SELECT stockid,
			description,
			decimalplaces
		FROM stockmaster
		WHERE categoryid = '" . $_POST['StockCategory'] . "'
		AND (mbflag='M' OR mbflag='B')";

	$ErrMsg = _('The stock items in the category selected cannot be retrieved because');
	$DbgMsg = _('The SQL that failed was');

	$StockResult = DB_query($sql, $db, $ErrMsg, $DbgMsg);

	$SQLOnHandDate = FormatDateForSQL($_POST['OnHandDate']);

	echo '<table cellpadding=5 cellspacing=4 border=0>';

	$tableheader = "<tr>
				<th>" . _('Item Code') . "</th>
				<th>" . _('Description') . "</th>
				<th>" . _('Quantity On Hand') . "</th></tr>";
	echo $tableheader;

	while ($myrows=DB_fetch_array($StockResult)) {

		$sql = "SELECT stockid,
				newqoh
				FROM stockmoves
				WHERE stockmoves.trandate <= '". $SQLOnHandDate . "' 
				AND stockid = '" . $myrows['stockid'] . "' 
				AND loccode = '" . $_POST['StockLocation'] ."' 
				ORDER BY stkmoveno DESC LIMIT 1";

		$ErrMsg =  _('The stock held as at') . ' ' . $_POST['OnHandDate'] . ' ' . _('could not be retrieved because');

		$LocStockResult = DB_query($sql, $db, $ErrMsg);

		$NumRows = DB_num_rows($LocStockResult, $db);

		$j = 1;
		$k=0; //row colour counter

		while ($LocQtyRow=DB_fetch_array($LocStockResult)) {

			if ($k==1){
				echo '<tr class="OddTableRows">';
				$k=0;
			} else {
				echo '<tr class="EvenTableRows">';
				$k=1;
			}

			if($NumRows == 0){
				printf("<td><a TARGET='_blank' href='StockStatus.php?%s'>%s</td>
					<td>%s</td>
					<td align=right>%s</td>",
					SID . '&StockID=' . strtoupper($myrows['stockid']),
					strtoupper($myrows['stockid']),
					$myrows['description'],
					0);
			} else {
				printf("<td><a TARGET='_blank' href='StockStatus.php?%s'>%s</td>
					<td>%s</td>
					<td align=right>%s</td>",
					SID . '&StockID=' . strtoupper($myrows['stockid']),
					strtoupper($myrows['stockid']),
					$myrows['description'],
					number_format($LocQtyRow['newqoh'],$myrows['decimalplaces']));

				$TotalQuantity += $LocQtyRow['newqoh'];
			}
			$j++;
			if ($j == 12){
				$j=1;
				echo $tableheader;
			}
		//end of page full new headings if
		}

	}//end of while loop
	echo '<tr><td>' . _('Total Quantity') . ": " . $TotalQuantity . '</td></tr></table>';
}

include('includes/footer.inc');
?>
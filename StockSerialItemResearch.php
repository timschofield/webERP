<?php
/* $Revision: 1.6 $ */

$PageSecurity = 3;

include('includes/session.inc');
$title = _('Serial Item Research');
include('includes/header.inc');


//validate the submission
if (isset($_POST['serialno'])) {
	$SN = trim($_POST['serialno']);
} elseif(isset($_GET['serialno'])) {
	$SN = trim($_GET['serialno']);
} else {
	$SN = '';
}
$SN = $SN;

?>
<div class="centre">
<br>
<form name=SNRESEARCH method=post action="<?php echo $_SERVER['PHP_SELF']; ?>">
<?php echo _('Serial Number') ?>: <input ID="serialno" name="serialno" size=21 maxlength=20 VALUE="<?php echo $SN; ?>"> &nbsp; 
<input type=submit name=submit>
</form>
<SCRIPT>
document.getElementById('serialno').focus();
</SCRIPT>

<?php

if ($SN!='') {
	//the point here is to allow a semi fuzzy search, but still keep someone from killing the db server
	if (strstr($SN,'%')){
		while(strstr($SN,'%%'))	{
			$SN = str_replace('%%','%',$SN);
		}
		if (strlen($SN) < 11){
			$SN = str_replace('%','',$SN);
			prnMsg('You can not use LIKE with short numbers. It has been removed.','warn');
		}
	}
	$SQL = "SELECT ssi.serialno,
			ssi.stockid, ssi.quantity CurInvQty, 
			ssm.moveqty, 
			sm.type, st.typename, 
			sm.transno, sm.loccode, l.locationname, sm.trandate, sm.debtorno, sm.branchcode, sm.reference, sm.qty TotalMoveQty
			FROM stockserialitems ssi INNER JOIN stockserialmoves ssm
				ON ssi.serialno = ssm.serialno AND ssi.stockid=ssm.stockid
			INNER JOIN stockmoves sm
				ON ssm.stockmoveno = sm.stkmoveno and ssi.loccode=sm.loccode
			INNER JOIN systypes st
				ON sm.type=st.typeid
			INNER JOIN locations l
				on sm.loccode = l.loccode
			WHERE ssi.serialno like '$SN'
			ORDER BY stkmoveno";

	$result = DB_query($SQL,$db);
	
	if (DB_num_rows($result) == 0){
		prnMsg( _('No History found for Serial Number'). ': <b>'.$SN.'</b>' , 'warn');
	} else {
		echo '<h4>'. _('Details for Serial Item').': <b>'.$SN.'</b><br>'. _('Length').'='.strlen($SN).'</h4>';
		echo '<table BORDER=1>';
		echo "<tr><th>" . _('StockID') . "</th>
			<th>" . _('CurInvQty') . "</th>
			<th>" . _('Move Qty') . "</th>
			<th>" . _('Move Type') . "</th>
			<th>" . _('Trans #') . "</th>
			<th>" . _('Location') . "</th>
			<th>" . _('Date') . "</th>
			<th>" . _('DebtorNo') . "</th>
			<th>" . _('Branch') . "</th>
			<th>" . _('Move Ref') . "</th>
			<th>" . _('Total Move Qty') . "</th>
			</tr>";
		while ($myrow=DB_fetch_row($result)) {
			printf("<tr>
				<td>%s<br>%s</td>
				<td align=right>%s</td>
				<td align=right>%s</td>
				<td>%s (%s)</td>
				<td align=right>%s</td>
				<td>%s - %s</td>
				<td>%s &nbsp;</td>
				<td>%s &nbsp;</td>
				<td>%s &nbsp;</td>
				<td>%s &nbsp;</td>
				<td align=right>%s</td>
				</tr>",
				$myrow[1],
				$myrow[0],
				$myrow[2],
				$myrow[3],
				$myrow[5], $myrow[4],
				$myrow[6],
				$myrow[7], $myrow[8],
				$myrow[9],
				$myrow[10],
				$myrow[11],
				$myrow[12],
				$myrow[13]
			);
		} //END WHILE LIST LOOP
		echo '</table>';
	} // ELSE THERE WHERE ROWS
}//END OF POST IS SET
echo '</div>';

include('includes/footer.inc');
?>
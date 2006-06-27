<?php
/* $Revision: 1.2 $ */

$PageSecurity = 3;

include('includes/session.inc');
$title = _('Serial Item Research');
include('includes/header.inc');


//validate the submission
if (isset($_POST['serialno'])) {
	$SN = trim($_POST['serialno']);
} elseif(isset($_GET['serialno'])) {
	$SN = trim($_GET['serialno']);
}
$SN = DB_escape_string($SN);

?>
<DIV ALIGN=CENTER>
<BR>
<FORM NAME=SNRESEARCH METHOD=POST ACTION="<?php echo $_SERVER['PHP_SELF']; ?>">
<?php echo _('Serial Number') ?>: <INPUT ID="serialno" NAME="serialno" SIZE=21 MAXLENGTH=20 VALUE="<?php echo $SN; ?>"> &nbsp; 
<INPUT TYPE=SUBMIT NAME=submit>
</FORM>
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
		echo '<TABLE BORDER=1>';
		echo "<tr><td class='tableheader'>" . _('StockID') . "</td>
			<td class='tableheader'>" . _('CurInvQty') . "</td>
			<td class='tableheader'>" . _('Move Qty') . "</td>
			<td class='tableheader'>" . _('Move Type') . "</td>
			<td class='tableheader'>" . _('Trans #') . "</td>
			<td class='tableheader'>" . _('Location') . "</td>
			<td class='tableheader'>" . _('Date') . "</td>
			<td class='tableheader'>" . _('DebtorNo') . "</td>
			<td class='tableheader'>" . _('Branch') . "</td>
			<td class='tableheader'>" . _('Move Ref') . "</td>
			<td class='tableheader'>" . _('Total Move Qty') . "</td>
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
		echo '</TABLE>';
	} // ELSE THERE WHERE ROWS
}//END OF POST IS SET
echo '</DIV>';

include('includes/footer.inc');
?>
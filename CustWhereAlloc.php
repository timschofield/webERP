<?php
/* $Revision: 1.3 $ */
$PageSecurity = 2;

include('includes/session.inc');
$title = _('Customer How Paid Inquiry');
include('includes/header.inc');

echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "' METHOD=POST>";

echo '<CENTER><TABLE CELLPADDING=2><TR>';

echo "<TD>Type:</TD><TD><SELECT name='TransType'> ";

$sql = 'SELECT TypeID, TypeName FROM SysTypes WHERE TypeID = 10 OR TypeID=12';
$resultTypes = DB_query($sql,$db);
while ($myrow=DB_fetch_array($resultTypes)){
    if (isset($_POST['TransType'])){
        if ($myrow['TypeID'] == $_POST['TransType']){
             echo "<OPTION SELECTED Value='" . $myrow['TypeID'] . "'>" . $myrow['TypeName'];
        } else {
             echo "<OPTION Value='" . $myrow['TypeID'] . "'>" . $myrow['TypeName'];
        }
    } else {
             echo "<OPTION Value='" . $myrow['TypeID'] . "'>" . $myrow['TypeName'];
    }
}
echo '</SELECT></TD>';

echo '<TD>'._('Transaction Number:')."</TD>
	<TD><INPUT TYPE=TEXT NAME='TransNo' MAXLENGTH=10 SIZE=10 VALUE=". $_POST['TransNo'] . '></TD>';

echo "</TR></TABLE>
	<INPUT TYPE=SUBMIT NAME='ShowResults' VALUE="._('Show How Allocated').'>';
echo '<HR>';


if (isset($_POST['ShowResults'])){


/*First off get the DebtorTransID of the transaction (invoice normally) selected */
    $sql = 'SELECT ID,
    		OvAmount+OvGST AS TotAmt
		FROM DebtorTrans
		WHERE Type=' . $_POST['TransType'] . ' AND TransNo = ' . $_POST['TransNo'];

    $result = DB_query($sql , $db);

    if (DB_num_rows($result)==1){
        $myrow = DB_fetch_array($result);
        $AllocToID = $myrow['ID'];

        echo '<CENTER><FONT SIZE=3><B><BR>'._('Allocations Made against Invoice number') . ' ' . $_POST['TransNo'] . ' '._('Transaction Total:').' '. number_format($myrow['TotAmt'],2) . '</FONT></B>';

        $sql = "SELECT Type,
			TransNo,
			TranDate,
			DebtorTrans.DebtorNo,
			Reference,
			Rate,
			OvAmount+OvGST+OvFreight+OvDiscount AS TotalAmt,
			CustAllocns.Amt
		FROM DebtorTrans
			INNER JOIN CustAllocns ON DebtorTrans.ID=CustAllocns.TransID_AllocFrom
		WHERE CustAllocns.TransID_AllocTo=". $AllocToID;

        $ErrMsg = _('The customer transactions for the selected criteria could not be retrieved because ');

        $TransResult = DB_query($sql, $db, $ErrMsg);

	echo '<TABLE CELLPADDING=2 BORDER=2>';

        $tableheader = "<TR><TD class='tableheader'>"._('Type')."</TD>
				<TD class='tableheader'>"._('Number')."</TD>
				<TD class='tableheader'>"._('Reference')."</TD>
				<TD class='tableheader'>"._('Ex Rate')."</TD>
				<TD class='tableheader'>"._('Amount')."</TD>
				<TD class='tableheader'>"._('Alloc').'</TD>
			</TR>';
        echo $tableheader;

        $RowCounter = 1;
        $k = 0; //row colour counter
        $AllocsTotal = 0;

        while ($myrow=DB_fetch_array($TransResult)) {

            if ($k==1){
                echo "<tr bgcolor='#CCCCCC'>";
                $k=0;
            } else {
                echo "<tr bgcolor='#EEEEEE'>";
                $k++;
            }

            if ($myrow['Type']==11){
                $TransType = _('Credit Note');
            } else {
                $TransType = _('Receipt');
            }
            printf( "<td>%s</td>
	    		<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td ALIGN=RIGHT>%s</td>
			<td ALIGN=RIGHT>%s</td>
			</tr>",
			$TransType,
			$myrow['TransNo'],
			$myrow['Reference'],
			$myrow['Rate'],
			$myrow['TotalAmt'],
			$myrow['Amt']);

            $RowCounter++;
            If ($RowCounter == 12){
                $RowCounter=1;
                echo $tableheader;
            }
            //end of page full new headings if
            $AllocsTotal +=$myrow['Amt'];
        }
        //end of while loop
        echo '<TR><TD COLSPAN = 6 ALIGN=RIGHT>' . number_format($AllocsTotal,2) . '</TD></TR>';
        echo '</TABLE>';
    }
}

echo '</FORM></CENTER>';
include('includes/footer.inc');

?>
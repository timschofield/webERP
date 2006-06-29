<?php

/* $Revision: 1.9 $ */

$PageSecurity = 2;

include('includes/session.inc');
$title = _('Where Used Inquiry');
include('includes/header.inc');

if (isset($_GET['StockID'])){
	$StockID = trim(strtoupper($_GET['StockID']));
} elseif (isset($_POST['StockID'])){
	$StockID = trim(strtoupper($_POST['StockID']));
}

echo "<A HREF='" . $rootpath . '/SelectProduct.php?' . SID . "'>" . _('Back to Items') . '</A><BR>';

if (isset($StockID)){
	$result = DB_query("SELECT description, 
					units, 
					mbflag 
				FROM stockmaster 
				WHERE stockid='$StockID'",$db);
	$myrow = DB_fetch_row($result);
	if (DB_num_rows($result)==0){
		prnMsg(_('The item code entered') . ' - ' . $StockID . ' ' . _('is not set up as an item in the system') . '. ' . _('Re-enter a valid item code or select from the Select Item link above'),'error');
		include('includes/footer.inc');
		exit;
	}
	echo "<BR><FONT COLOR=BLUE SIZE=3><B>$StockID - $myrow[0] </B>  (" . _('in units of') . ' ' . $myrow[1] . ')</FONT>';
}

echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . '?'. SID ."' METHOD=POST>";
echo _('Enter an Item Code') . ": <input type=text name='StockID' size=21 maxlength=20 value='$StockID' >";
echo "<INPUT TYPE=SUBMIT NAME='ShowWhereUsed' VALUE='" . _('Show Where Used') . "'>";

echo '<HR>';

if (isset($StockID)) {

	$SQL = "SELECT bom.*,
    		stockmaster.description
		FROM bom INNER JOIN stockmaster
			ON bom.parent = stockmaster.stockid
		WHERE component='" . $StockID . "'
		AND bom.effectiveafter<='" . Date('Y-m-d') . "'
		AND bom.effectiveto >='" . Date('Y-m-d') . "'";

	$ErrMsg = _('The parents for the selected part could not be retrieved because');;
	$result = DB_query($SQL,$db,$ErrMsg);
	if (DB_num_rows($result)==0){
		prnMsg(_('The selected item') . ' ' . $StockID . ' ' . _('is not used as a component of any other parts'),'error');;
	} else {

    		echo '<TABLE WIDTH=100%>';

    		$tableheader = "<TR><TD class='tableheader'>" . _('Used By') . "</TD>
					<TD class='tableheader'>" . _('Work Centre') . "</TD>
					<TD class='tableheader'>" . _('Location') . "</TD>
					<TD class='tableheader'>" . _('Quantity Required') . "</TD>
					<TD class='tableheader'>" . _('Effective After') . "</TD>
					<TD class='tableheader'>" . _('Effective To') . '</TD></TR>';
    		echo $tableheader;
		$k=0;
    		while ($myrow=DB_fetch_array($result)) {

    			if ($k==1){
    				echo "<tr bgcolor='#CCCCCC'>";
    				$k=0;
    			} else {
    				echo "<tr bgcolor='#EEEEEE'>";
    				$k=1;
    			}
			$k++;

    			echo "<td><A target='_blank' HREF='" . $rootpath . "/BOMInquiry.php?" . SID . "&StockID=" . $myrow['parent'] . "' ALT='" . _('Show Bill Of Material') . "'>" . $myrow['parent']. ' - ' . $myrow['description']. '</a></td>';
    			echo '<td>' . $myrow['workcentreadded']. '</td>';
    			echo '<td>' . $myrow['loccode']. '</td>';
    			echo '<td>' . $myrow['quantity']. '</td>';
    			echo '<td>' . ConvertSQLDate($myrow['effectiveafter']) . '</td>';
    			echo '<td>' . ConvertSQLDate($myrow['effectiveto']) . '</td>';

    			$j++;
    			If ($j == 12){
    				$j=1;
    				echo $tableheader;
    			}
    			//end of page full new headings if
    		}

    		echo '</TABLE>';
	}
} // StockID is set

echo '</FORM>';

include('includes/footer.inc');

?>
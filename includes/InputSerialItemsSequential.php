<?php
/* $Revision: 1.2 $ */
/*Input Serial Items - used for inputing serial numbers or batch/roll/bundle references
for controlled items - used in:
- ConfirmDispatchControlledInvoice.php
- GoodsReceivedControlled.php
- StockAdjustments.php
- StockTransfers.php
- CreditItemsControlled.php

*/

//we start with a batch or serial no header and need to display something for verification...
global $tableheader;

if (isset($_GET['LineNo'])){
	$LineNo = $_GET['LineNo'];
} elseif (isset($_POST['LineNo'])){
	$LineNo = $_POST['LineNo'];
}

echo '<TD valign=top>';

/*Start a new table for the Serial/Batch ref input  in one column (as a sub table
then the multi select box for selection of existing bundle/serial nos for dispatch if applicable*/
//echo '<TABLE><TR><TD valign=TOP>';

/*in the first column add a table for the input of newies */
echo '<TABLE>';
echo $tableheader;


echo '<FORM ACTION="' . $_SERVER['PHP_SELF'] . '?=' . $SID . '" METHOD="POST">
      <input type=hidden name=LineNo value="' . $LineNo . '">
      <input type=hidden name=StockID value="' . $StockID . '">
      <input type=hidden name=EntryType value="SEQUENCE">';
if ( isset($_GET['EditControlled']) ) {
	$EditControlled = isset($_GET['EditControlled'])?$_GET['EditControlled']:false;
} elseif ( isset($_POST['EditControlled']) ){
	$EditControlled = isset($_POST['EditControlled'])?$_POST['EditControlled']:false;
}
echo '<TR><TD valign=top>'. _('Begin:') . '</td><td> <input type=text name="BeginNo" size=21  maxlength=20 value="'. $_POST['BeginNo']. '"></td></tr>';
echo '<TR><TD valign=top>'. _('End:') . '</td><td> <input type=text name="EndNo" size=21  maxlength=20  value="'. $_POST['EndNo']. '"></td></tr>';


echo '</table>';
echo '<br><center><INPUT TYPE=SUBMIT NAME="AddSequence" VALUE="'. _('Enter'). '"></center><BR>';
echo '</FORM></TD><TD valign=top>';
//echo '</TD></TR></TABLE>'; /*end of nested table */
?>

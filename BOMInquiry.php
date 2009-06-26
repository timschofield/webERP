<?php
/* $Revision: 1.12 $ */
$PageSecurity = 2;

include('includes/session.inc');
$title = _('Costed Bill Of Material');
include('includes/header.inc');

if (isset($_GET['StockID'])){
	$StockID =trim(strtoupper($_GET['StockID']));
} elseif (isset($_POST['StockID'])){
	$StockID =trim(strtoupper($_POST['StockID']));
}

if (!isset($_POST['StockID'])) {
	echo "<form action=" . $_SERVER['PHP_SELF'] . "?" . SID ." method=post><b><br></b>".
	'<div class="page_help_text">'. _('Select a manufactured part') . " (" . _('or Assembly or Kit part') . ") " .
	 _('to view the costed bill of materials') . "." . "<br><font size=1>" .
	 _('Parts must be defined in the stock item entry') . "/" . _('modification screen as manufactured') . 
     ", " . _('kits or assemblies to be available for construction of a bill of material').'</div>'
	."</font><br><table align='center' cellpadding=3 colspan=4><tr><td><font size=1>" . _('Enter text extracts in the') . 
	 " <b>" . _('description') . "</b>:</font></td><td><input tabindex='1' type='Text' name='Keywords' size=20 maxlength=25></td>
	 <td><font size=3><b>" . _('OR') . "</b></font></td><td><font size=1>" . _('Enter extract of the') . 
     " <b>" . _('Stock Code') . "</b>:</font></td><td><input tabindex='2' type='Text' name='StockCode' size=15 maxlength=20></td>
	 </tr></table><br><div class='centre'><input tabindex='3' type=submit name='Search' VALUE=" . _('Search Now') . "></div><br>";
}

if (isset($_POST['Search'])){
	// Work around to auto select
	if ($_POST['Keywords']=='' AND $_POST['StockCode']=='') {
		$_POST['StockCode']='%';
	}
	if ($_POST['Keywords'] AND $_POST['StockCode']) {
		$msg=_('Stock description keywords have been used in preference to the Stock code extract entered');
	}
	if ($_POST['Keywords']=='' AND $_POST['StockCode']=='') {
		$msg=_('At least one stock description keyword or an extract of a stock code must be entered for the search');
	} else {
		if (strlen($_POST['Keywords'])>0) {
			//insert wildcard characters in spaces

			$i=0;
			$SearchString = '%';
			while (strpos($_POST['Keywords'], ' ', $i)) {
				$wrdlen=strpos($_POST['Keywords'],' ',$i) - $i;
				$SearchString=$SearchString . substr($_POST['Keywords'],$i,$wrdlen) . '%';
				$i=strpos($_POST['Keywords'],' ',$i) +1;
			}
			$SearchString = $SearchString. substr($_POST['Keywords'],$i).'%';


			$sql = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.units,
					stockmaster.mbflag,
					SUM(locstock.quantity) as totalonhand
				FROM stockmaster,
					locstock
				WHERE stockmaster.stockid = locstock.stockid
				AND stockmaster.description " . LIKE . " '$SearchString'
				AND (stockmaster.mbflag='M' OR stockmaster.mbflag='K' OR stockmaster.mbflag='A' OR stockmaster.mbflag='G')
				GROUP BY stockmaster.stockid,
					stockmaster.description,
					stockmaster.units,
					stockmaster.mbflag
				ORDER BY stockmaster.stockid";

		} elseif (strlen($_POST['StockCode'])>0){
			$sql = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.units,
					stockmaster.mbflag,
					sum(locstock.quantity) as totalonhand
				FROM stockmaster,
					locstock
				WHERE stockmaster.stockid = locstock.stockid
				AND stockmaster.stockid " . LIKE  . "'%" . $_POST['StockCode'] . "%'
				AND (stockmaster.mbflag='M'
					OR stockmaster.mbflag='K'
					OR stockmaster.mbflag='G'
					OR stockmaster.mbflag='A')
				GROUP BY stockmaster.stockid,
					stockmaster.description,
					stockmaster.units,
					stockmaster.mbflag
				ORDER BY stockmaster.stockid";

		}

		$ErrMsg = _('The SQL to find the parts selected failed with the message');
		$result = DB_query($sql,$db,$ErrMsg);

	} //one of keywords or StockCode was more than a zero length string
} //end of if search

if (isset($result) AND !isset($SelectedParent)) {

	echo '<br><hr><br><table align="center" cellpadding=2 colspan=7 BORDER=1>';
	$TableHeader = '<tr><th>' . _('Code') . '</th>
				<th>' . _('Description') . '</th>
				<th>' . _('On Hand') . '</th>
				<th>' . _('Units') . '</th>
			</tr>';

	echo $TableHeader;

	$j = 1;
	$k = 0; //row colour counter
	while ($myrow=DB_fetch_array($result)) {
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k++;
		}
		if ($myrow['mbflag']=='A' OR $myrow['mbflag']=='K'){
			$StockOnHand = 'N/A';
		} else {
			$StockOnHand = number_format($myrow['totalonhand'],2);
		}
		$tabindex=$j+4;
		printf("<td><input tabindex='".$tabindex."' type=submit name='StockID' VALUE='%s'</td>
		        <td>%s</td>
			<td align=right>%s</td>
			<td>%s</td></tr>",
			$myrow['stockid'],
			$myrow['description'],
			$StockOnHand,
			$myrow['units']
		);
		$j++;
//end of page full new headings if
	}
//end of while loop

	echo '</table><br>';

}

if (isset($StockID) and $StockID!=""){
	$result = DB_query("SELECT description, units FROM stockmaster WHERE stockid='" . $StockID  . "'",$db);
	$myrow = DB_fetch_row($result);

	echo "<div class='centre'><br><font size=4><b>" . $myrow[0] . ' : ' . _('per') . ' ' . $myrow[1] . "</b></font></div>";

	$sql = "SELECT bom.parent,
			bom.component,
			stockmaster.description,
			stockmaster.decimalplaces,
			stockmaster.materialcost+ stockmaster.labourcost+stockmaster.overheadcost as standardcost,
			bom.quantity,
			bom.quantity * (stockmaster.materialcost+ stockmaster.labourcost+ stockmaster.overheadcost) AS componentcost
		FROM bom INNER JOIN stockmaster ON bom.component = stockmaster.stockid
		WHERE bom.parent = '" . $StockID . "'
		AND bom.effectiveafter < Now()
		AND bom.effectiveto > Now()";

	$ErrMsg = _('The bill of material could not be retrieved because');
	$BOMResult = DB_query ($sql,$db,$ErrMsg);

	if (DB_num_rows($BOMResult)==0){
		prnMsg(_('The bill of material for this part is not set up') . ' - ' . _('there are no components defined for it'),'warn');
	} else {

		echo "<table cellpadding=2 BORDER=2>";
		$TableHeader = '<tr>
				<th>' . _('Component') . '</th>
				<th>' . _('Description') . '</th>
				<th>' . _('Quantity') . '</th>
				<th>' . _('Unit Cost') . '</th>
				<th>' . _('Total Cost') . '</th>
				</tr>';

		echo $TableHeader;

		$j = 1;
		$k=0; //row colour counter

		$TotalCost = 0;

		while ($myrow=DB_fetch_array($BOMResult)) {

			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k++;
			}

			$ComponentLink = "<a href='$rootpath/SelectProduct.php?" . SID . "&StockID=" . $myrow['component'] . "'>" . $myrow['component'] . "</a>";

			/* Component Code  Description                 Quantity            Std Cost*                Total Cost */
			printf("<td>%s</td>
				<td>%s</td>
				<td class=number>%s</td>
				<td class=number>%.2f</td>
				<td class=number>%.2f</td>
				</tr>",
				$ComponentLink,
				$myrow['description'],
				number_format($myrow['quantity'],
				$myrow['decimalplaces']),
				$myrow['standardcost'],
				$myrow['componentcost']);

			$TotalCost += $myrow['componentcost'];

			$j++;
		}

		echo '<tr>
			<td colspan=4 class=number><b>' . _('Total Cost') . '</b></td>
			<td class=number><b>' . number_format($TotalCost,2) . '</b></td>
		</tr>';

		echo '</table>';
	}
} else { //no stock item entered
	prnMsg(_('Enter a stock item code above') . ', ' . _('to view the costed bill of material for'),'info');
}

if (!isset($_POST['StockID']) or $_POST['StockID']=='') {
	echo "<script>defaultControl(document.forms[0].StockCode);</script>";
}

echo '</form>';
include('includes/footer.inc');
?>
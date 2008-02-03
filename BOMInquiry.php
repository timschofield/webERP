<?php
/* $Revision: 1.8 $ */
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
	echo "<FORM ACTION=" . $_SERVER['PHP_SELF'] . "?" . SID ." METHOD=POST><B><BR></B>" .
	 _('Select a manufactured part') . " (" . _('or Assembly or Kit part') . ") " .
	 _('to view the costed bill of materials') . "." . "<BR><FONT SIZE=1>" .
	 _('Parts must be defined in the stock item entry') . "/" . _('modification screen as manufactured') . 
     ", " . _('kits or assemblies to be available for construction of a bill of material') .
     "</FONT><TABLE CELLPADDING=3 COLSPAN=4><TR><TD><FONT SIZE=1>" . _('Enter text extracts in the') . 
	 " <B>" . _('description') . "</B>:</FONT></TD><TD><INPUT TYPE='Text' NAME='Keywords' SIZE=20 MAXLENGTH=25></TD>
	 <TD><FONT SIZE=3><B>" . _('OR') . "</B></FONT></TD><TD><FONT SIZE=1>" . _('Enter extract of the') . 
     " <B>" . _('Stock Code') . "</B>:</FONT></TD><TD><INPUT TYPE='Text' NAME='StockCode' SIZE=15 MAXLENGTH=18></TD>
	 </TR></TABLE><CENTER><INPUT TYPE=SUBMIT NAME='Search' VALUE=" . _('Search Now') . "></CENTER>";
}

if (isset($_POST['Search'])){
	// Work around to auto select
	If ($_POST['Keywords']=='' AND $_POST['StockCode']=='') {
		$_POST['StockCode']='%';
	}
	If ($_POST['Keywords'] AND $_POST['StockCode']) {
		$msg=_('Stock description keywords have been used in preference to the Stock code extract entered');
	}
	If ($_POST['Keywords']=='' AND $_POST['StockCode']=='') {
		$msg=_('At least one stock description keyword or an extract of a stock code must be entered for the search');
	} else {
		If (strlen($_POST['Keywords'])>0) {
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
				AND (stockmaster.mbflag='M' OR stockmaster.mbflag='K' OR stockmaster.mbflag='A')
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

If (isset($result) AND !isset($SelectedParent)) {

	echo '<TABLE CELLPADDING=2 COLSPAN=7 BORDER=1>';
	$TableHeader = '<TR><TH>' . _('Code') . '</TH>
				<TH>' . _('Description') . '</TH>
				<TH>' . _('On Hand') . '</TH>
				<TH>' . _('Units') . '</TH>
			</TR>';

	echo $TableHeader;

	$j = 1;
	$k=0; //row colour counter
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
		printf("<td><INPUT TYPE=SUBMIT NAME='StockID' VALUE='%s'</td>
		        <td>%s</td>
			<td ALIGN=RIGHT>%s</td>
			<td>%s</td></tr>",
			$myrow['stockid'],
			$myrow['description'],
			$StockOnHand,
			$myrow['units']
		);

		$j++;
		If ($j == 12){
			$j=1;
			echo $TableHeader;
		}
//end of page full new headings if
	}
//end of while loop

	echo '</TABLE>';

}

if (isset($StockID) and $StockID!=""){
	$result = DB_query("SELECT description, units FROM stockmaster WHERE stockid='" . $StockID  . "'",$db);
	$myrow = DB_fetch_row($result);

	echo "<CENTER><BR><FONT SIZE=4><B>" . $myrow[0] . ' : ' . _('per') . ' ' . $myrow[1] . "</B></FONT>";

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

		echo "<TABLE CELLPADDING=2 BORDER=2>";
		$TableHeader = '<TR>
				<TH>' . _('Component') . '</TH>
				<TH>' . _('Description') . '</TH>
				<TH>' . _('Quantity') . '</TH>
				<TH>' . _('Unit Cost') . '</TH>
				<TH>' . _('Total Cost') . '</TH>
				</TR>';

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

			$ComponentLink = "<A HREF='$rootpath/SelectProduct.php?" . SID . "&StockID=" . $myrow['component'] . "'>" . $myrow['component'] . "</A>";

			/* Component Code  Description                 Quantity            Std Cost*                Total Cost */
			printf("<td>%s</td>
				<td>%s</td>
				<td ALIGN=RIGHT>%s</td>
				<td ALIGN=RIGHT>%.2f</td>
				<td ALIGN=RIGHT>%.2f</td>
				</tr>",
				$ComponentLink,
				$myrow['description'],
				number_format($myrow['quantity'],
				$myrow['decimalplaces']),
				$myrow['standardcost'],
				$myrow['componentcost']);

			$TotalCost += $myrow['componentcost'];

			$j++;
			If ($j == 12){
				$j=1;
				echo $TableHeader;
			}//end of page full new headings if}//end of while
		}

		echo '<TR>
			<TD COLSPAN=4 ALIGN=RIGHT><B>' . _('Total Cost') . '</B></TD>
			<TD ALIGN=RIGHT><B>' . number_format($TotalCost,2) . '</B></TD>
		</TR>';

		echo '</TABLE></CENTER>';
	}
} else { //no stock item entered
	prnMsg(_('Enter a stock item code above') . ', ' . _('to view the costed bill of material for'),'info');
}
echo '</form>';
include('includes/footer.inc');
?>

<?php

/* $Id: SalesGraph.php 6310 2013-08-29 10:42:50Z daintree $*/

include('includes/session.inc');
include('includes/phplot/phplot.php');
$Title=_('Sales Report Graph');

$ViewTopic = 'ARInquiries';
$BookMark = 'SalesGraph';

include('includes/header.inc');

$SelectADifferentPeriod ='';

if (isset($_POST['FromPeriod']) AND isset($_POST['ToPeriod'])){

	if ($_POST['FromPeriod'] > $_POST['ToPeriod']){
		prnMsg(_('The selected period from is actually after the period to! Please re-select the reporting period'),'error');
		$SelectADifferentPeriod =_('Select A Different Period');
	}
	if ($_POST['ToPeriod'] - $_POST['FromPeriod'] >12){
		prnMsg(_('The selected period range is more than 12 months - only graphs for a period less than 12 months can be created'),'error');
		$SelectADifferentPeriod= _('Select A Different Period');
	}
	if ((!isset($_POST['ValueFrom']) OR $_POST['ValueFrom']='' OR !isset($_POST['ValueTo']) OR $_POST['ValueTo']='') AND $_POST['GraphOn'] !='All'){
		prnMsg(_('For graphs including either a customer or item range - the range must be specified. Please enter the value from and the value to for the range'),'error');
		$SelectADifferentPeriod= _('Select A Different Period');
	}
}

if ((! isset($_POST['FromPeriod']) OR ! isset($_POST['ToPeriod']))
	OR $SelectADifferentPeriod==_('Select A Different Period')){

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>';

	echo '<table class="selection">
			<tr><td>' . _('Select Period From:') . '</td>
			<td><select name="FromPeriod">';

	if (Date('m') > $_SESSION['YearEnd']){
		/*Dates in SQL format */
		$DefaultFromDate = Date ('Y-m-d', Mktime(0,0,0,$_SESSION['YearEnd'] + 2,0,Date('Y')));
	} else {
		$DefaultFromDate = Date ('Y-m-d', Mktime(0,0,0,$_SESSION['YearEnd'] + 2,0,Date('Y')-1));
	}
	$sql = "SELECT periodno, lastdate_in_period FROM periods ORDER BY periodno";
	$Periods = DB_query($sql,$db);

	while ($myrow=DB_fetch_array($Periods,$db)){
		if(isset($_POST['FromPeriod']) AND $_POST['FromPeriod']!=''){
			if( $_POST['FromPeriod']== $myrow['periodno']){
				echo '<option selected="selected" value="' . $myrow['periodno'] . '">' .MonthAndYearFromSQLDate($myrow['lastdate_in_period']) . '</option>';
			} else {
				echo '<option value="' . $myrow['periodno'] . '">' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']) . '</option>';
			}
		} else {
			if($myrow['lastdate_in_period']==$DefaultFromDate){
				echo '<option selected="selected" value="' . $myrow['periodno'] . '">' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']) . '</option>';
			} else {
				echo '<option value="' . $myrow['periodno'] . '">' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']) . '</option>';
			}
		}
	}

	echo '</select></td></tr>';
	if (!isset($_POST['ToPeriod']) OR $_POST['ToPeriod']==''){
		$DefaultToPeriod = GetPeriod(DateAdd(ConvertSQLDate($DefaultFromDate),'m',11),$db);
	} else {
		$DefaultToPeriod = $_POST['ToPeriod'];
	}

	echo '<tr>
			<td>' . _('Select Period To:')  . '</td>
			<td><select name="ToPeriod">';

	$RetResult = DB_data_seek($Periods,0);

	while ($myrow=DB_fetch_array($Periods,$db)){

		if($myrow['periodno']==$DefaultToPeriod){
			echo '<option selected="selected" value="' . $myrow['periodno'] . '">' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']) . '</option>';
		} else {
			echo '<option value ="' . $myrow['periodno'] . '">' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']) . '</option>';
		}
	}
	echo '</select></td></tr>';

	$AreasResult = DB_query("SELECT areacode, areadescription FROM areas",$db);

	if (!isset($_POST['SalesArea'])){
		$_POST['SalesArea']='';
	}
	echo '<tr>
			<td>' . _('For Sales Area/Region:')  . '</td>
			<td><select name="SalesArea">';
	if($_POST['SalesArea']=='All'){
		echo '<option selected="selected" value="All">' . _('All') . '</option>';
	} else {
		echo '<option value="All">' . _('All') . '</option>';
	}
	while ($myrow=DB_fetch_array($AreasResult)){
		if($myrow['areacode']==$_POST['SalesArea']){
			echo '<option selected="selected" value="' . $myrow['areacode'] . '">' . $myrow['areadescription'] . '</option>';
		} else {
			echo '<option value="' . $myrow['areacode'] . '">' . $myrow['areadescription'] . '</option>';
		}
	}
	echo '</select></td></tr>';

	$CategoriesResult = DB_query("SELECT categoryid, categorydescription FROM stockcategory",$db);

	if (!isset($_POST['CategoryID'])){
		$_POST['CategoryID']='';
	}
	echo '<tr>
			<td>' . _('For Stock Category:')  . '</td>
			<td><select name="CategoryID">';
	if($_POST['CategoryID']=='All'){
		echo '<option selected="selected" value="All">' . _('All') . '</option>';
	} else {
		echo '<option value="All">' . _('All') . '</option>';
	}
	while ($myrow=DB_fetch_array($CategoriesResult)){
		if($myrow['categoryid']==$_POST['CategoryID']){
			echo '<option selected="selected" value="' . $myrow['categoryid'] . '">' . $myrow['categorydescription'] . '</option>';
		} else {
			echo '<option value="' . $myrow['categoryid'] . '">' . $myrow['categorydescription'] . '</option>';
		}
	}
	echo '</select></td></tr>';

	$SalesFolkResult = DB_query("SELECT salesmancode, salesmanname FROM salesman",$db);

	if (! isset($_POST['SalesmanCode'])){
 		$_POST['SalesmanCode'] = '';
	}

	echo '<tr>
			<td>' . _('For Sales Person:')  . '</td>
			<td><select name="SalesmanCode">';

	if($_POST['SalesmanCode']=='All'){
		echo '<option selected="selected" value="All">' . _('All') . '</option>';
	} else {
		echo '<option value="All">' . _('All') . '</option>';
	}
	while ($myrow=DB_fetch_array($SalesFolkResult)){
		if ($myrow['salesmancode']== $_POST['SalesmanCode']){
			echo '<option selected="selected" value="' . $myrow['salesmancode'] . '">' . $myrow['salesmanname'] . '</option>';
		} else {
			echo '<option value="' . $myrow['salesmancode'] . '">' . $myrow['salesmanname'] . '</option>';
		}
	}
	echo '</select></td>
			<td>' . $_POST['SalesmanCode'] . '</td>
		</tr>';

	echo '<tr><td>' . _('Graph Type') . '</td>';
	echo '<td><select name="GraphType">';
	echo '<option value="bars">' . _('Bar Graph') . '</option>';
	echo '<option value="stackedbars">' . _('Stacked Bar Graph') . '</option>';
	echo '<option value="lines">' . _('Line Graph') . '</option>';
	echo '<option value="linepoints">' . _('Line Point Graph') . '</option>';
	echo '<option value="area">' . _('Area Graph') . '</option>';
	echo '<option value="points">' . _('Points Graph') . '</option>';
	echo '<option value="pie">' . _('Pie Graph') . '</option>';
	echo '<option value="thinbarline">' . _('Thin Bar Line Graph') . '</option>';
	echo '<option value="squared">' . _('Squared Graph') . '</option>';
	echo '<option value="stackedarea">' . _('Stacked Area Graph') . '</option>';
	echo '</select></td></tr>';

	if (!isset($_POST['ValueFrom'])){
		$_POST['ValueFrom']='';
	}
	if (!isset($_POST['ValueTo'])){
		$_POST['ValueTo']='';
	}
	echo '<tr><td>' . _('Graph On:') . '</td><td>
			<input type="radio" name="GraphOn" value="All" checked="checked" />' . _('All') . '<br />
			<input type="radio" name="GraphOn" value="Customer" />' . _('Customer') . '<br />
			<input type="radio" name="GraphOn" value="StockID" />' . _('Item Code') . '</td></tr>';
	echo '<tr><td>' . _('From:') . ' <input type="text" name="ValueFrom" value="' . $_POST['ValueFrom'] . '" /></td>
	 		<td>' . _('To:') . ' <input type="text" name="ValueTo" value="' . $_POST['ValueTo'] . '" /></td></tr>';

	echo '<tr><td>' . _('Graph Value:') . '</td><td>
			<input type="radio" name="GraphValue" value="Net" checked="checked" />' . _('Net Sales Value') . '<br />
			<input type="radio" name="GraphValue" value="GP" />' . _('Gross Profit') . '<br />
			<input type="radio" name="GraphValue" value="Quantity" />' . _('Quantity') . '</td></tr>';

	echo '</table>';

	echo '<br /><div class="centre"><input type="submit" name="ShowGraph" value="' . _('Show Sales Graph') .'" /></div>';
    echo '</div>
          </form>';
	include('includes/footer.inc');
} else {

	$graph = new PHPlot(950,450);
	$SelectClause ='';
	$WhereClause ='';
	$GraphTitle ='';
	if ($_POST['GraphValue']=='Net') {
		$GraphTitle = _('Sales Value');
		$SelectClause = 'amt';
	} elseif ($_POST['GraphValue']=='GP'){
		$GraphTitle = _('Gross Profit');
		$SelectClause = '(amt - cost)';
	} else {
		$GraphTitle = _('Unit Sales');
		$SelectClause = 'qty';
	}

	$GraphTitle .= ' ' . _('From Period') . ' ' . $_POST['FromPeriod'] . ' ' . _('to') . ' ' . $_POST['ToPeriod'] . "\n\r";

	if ($_POST['SalesArea']=='All'){
		$GraphTitle .= ' ' . _('For All Sales Areas');
	} else {
		$result = DB_query("SELECT areadescription FROM areas WHERE areacode='" . $_POST['SalesArea'] . "'",$db);
		$myrow = DB_fetch_row($result);
		$GraphTitle .= ' ' . _('For') . ' ' . $myrow[0];
		$WhereClause .= " area='" . $_POST['SalesArea'] . "' AND";
	}
	if ($_POST['CategoryID']=='All'){
		$GraphTitle .= ' ' . _('For All Stock Categories');
	} else {
		$result = DB_query("SELECT categorydescription FROM stockcategory WHERE categoryid='" . $_POST['CategoryID'] . "'",$db);
		$myrow = DB_fetch_row($result);
		$GraphTitle .= ' ' . _('For') . ' ' . $myrow[0];
		$WhereClause .= " stkcategory='" . $_POST['CategoryID'] . "' AND";

	}
	if ($_POST['SalesmanCode']=='All'){
		$GraphTitle .= ' ' . _('For All Salespeople');
	} else {
		$result = DB_query("SELECT salesmanname FROM salesman WHERE salesmancode='" . $_POST['SalesmanCode'] . "'",$db);
		$myrow = DB_fetch_row($result);
		$GraphTitle .= ' ' . _('For Salesperson:') . ' ' . $myrow[0];
		$WhereClause .= " salesperson='" . $_POST['SalesmanCode'] . "' AND";

	}
	if ($_POST['GraphOn']=='Customer'){
		$GraphTitle .= ' ' . _('For Customers from') . ' ' . $_POST['ValueFrom'] . ' ' . _('to') . ' ' . $_POST['ValueTo'];
		$WhereClause .= "  cust >='" . $_POST['ValueFrom'] . "' AND cust <='" . $_POST['ValueTo'] . "' AND";
	}
	if ($_POST['GraphOn']=='StockID'){
		$GraphTitle .= ' ' . _('For Items from') . ' ' . $_POST['ValueFrom'] . ' ' . _('to') . ' ' . $_POST['ValueTo'];
		$WhereClause .= "  stockid >='" . $_POST['ValueFrom'] . "' AND stockid <='" . $_POST['ValueTo'] . "' AND";
	}

	$WhereClause = "WHERE " . $WhereClause . " salesanalysis.periodno>='" . $_POST['FromPeriod'] . "' AND salesanalysis.periodno <= '" . $_POST['ToPeriod'] . "'";

	$SQL = "SELECT salesanalysis.periodno,
				periods.lastdate_in_period,
				SUM(CASE WHEN budgetoractual=1 THEN " . $SelectClause . " ELSE 0 END) AS sales,
				SUM(CASE WHEN  budgetoractual=0 THEN " . $SelectClause . " ELSE 0 END) AS budget
		FROM salesanalysis INNER JOIN periods ON salesanalysis.periodno=periods.periodno " . $WhereClause . "
		GROUP BY salesanalysis.periodno,
			periods.lastdate_in_period
		ORDER BY salesanalysis.periodno";


	$graph->SetTitle($GraphTitle);
	$graph->SetTitleColor('blue');
	$graph->SetOutputFile('companies/' .$_SESSION['DatabaseName'] .  '/reports/salesgraph.png');
	$graph->SetXTitle(_('Month'));
	if ($_POST['GraphValue']=='Net'){
		$graph->SetYTitle(_('Sales Value'));
	} elseif ($_POST['GraphValue']=='GP'){
		$graph->SetYTitle(_('Gross Profit'));
	} else {
		$graph->SetYTitle(_('Quantity'));
	}
	$graph->SetXTickPos('none');
	$graph->SetXTickLabelPos('none');
	$graph->SetBackgroundColor('white');
	$graph->SetTitleColor('blue');
	$graph->SetFileFormat('png');
	$graph->SetPlotType($_POST['GraphType']);
	$graph->SetIsInline('1');
	$graph->SetShading(5);
	$graph->SetDrawYGrid(TRUE);
	$graph->SetDataType('text-data');

	$SalesResult = DB_query($SQL, $db);
	if (DB_error_no($db) !=0) {

		prnMsg(_('The sales graph data for the selected criteria could not be retrieved because') . ' - ' . DB_error_msg($db),'error');
		include('includes/footer.inc');
		exit;
	}
	if (DB_num_rows($SalesResult)==0){
		prnMsg(_('There is not sales data for the criteria entered to graph'),'info');
		include('includes/footer.inc');
		exit;
	}

	$GraphArrays = array();
	$i = 0;
	while ($myrow = DB_fetch_array($SalesResult)){
		$GraphArray[$i] = array(MonthAndYearFromSQLDate($myrow['lastdate_in_period']),$myrow['sales'],$myrow['budget']);
		$i++;
	}

	$graph->SetDataValues($GraphArray);
	$graph->SetDataColors(
		array('grey','wheat'),  //Data Colors
		array('black')	//Border Colors
	);
	$graph->SetLegend(array(_('Actual'),_('Budget')));

	//Draw it
	$graph->DrawGraph();
	echo '<table class="selection">
			<tr><td>';
	echo '<p><img src="companies/' .$_SESSION['DatabaseName'] .  '/reports/salesgraph.png" alt="Sales Report Graph"></img></p>';
	echo '</td>
		</tr>
		</table>';
	include('includes/footer.inc');
}
?>
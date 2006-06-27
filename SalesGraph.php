<?php
 $PageSecurity = 6;
 include('includes/session.inc');
 include('includes/phplot/phplot.php');
 $title=_('Sales Report Graph');
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
	
	echo '<FORM METHOD="POST" ACTION="' . $_SERVER['PHP_SELF'] . '?' . SID . '">';
 /*Show a form to allow input of criteria for TB to show */
	echo '<CENTER><TABLE><TR><TD>' . _('Select Period From:') . '</TD><TD><SELECT Name="FromPeriod">';
	
	if (Date('m') > $_SESSION['YearEnd']){
		/*Dates in SQL format */
		$DefaultFromDate = Date ('Y-m-d', Mktime(0,0,0,$_SESSION['YearEnd'] + 2,0,Date('Y')));
	} else {
		$DefaultFromDate = Date ('Y-m-d', Mktime(0,0,0,$_SESSION['YearEnd'] + 2,0,Date('Y')-1));
	}
	$sql = 'SELECT periodno, lastdate_in_period FROM periods ORDER BY periodno';
	$Periods = DB_query($sql,$db);

	while ($myrow=DB_fetch_array($Periods,$db)){
		if(isset($_POST['FromPeriod']) AND $_POST['FromPeriod']!=''){
			if( $_POST['FromPeriod']== $myrow['periodno']){
				echo '<OPTION SELECTED VALUE="' . $myrow['periodno'] . '">' .MonthAndYearFromSQLDate($myrow['lastdate_in_period']);
			} else {
				echo '<OPTION VALUE="' . $myrow['periodno'] . '">' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']);
			}
		} else {
			if($myrow['lastdate_in_period']==$DefaultFromDate){
				echo '<OPTION SELECTED VALUE="' . $myrow['periodno'] . '">' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']);
			} else {
				echo '<OPTION VALUE="' . $myrow['periodno'] . '">' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']);
			}
		}
	}

	echo '</SELECT></TD></TR>';
	if (!isset($_POST['ToPeriod']) OR $_POST['ToPeriod']==''){
		$sql = 'SELECT Max(periodno) FROM periods';
		$MaxPrd = DB_query($sql,$db);
		$MaxPrdrow = DB_fetch_row($MaxPrd);

		$DefaultToPeriod = (int) ($MaxPrdrow[0]-1);
	} else {
		$DefaultToPeriod = $_POST['ToPeriod'];
	}

	echo '<TR><TD>' . _('Select Period To:') .'</TD><TD><SELECT Name="ToPeriod">';

	$RetResult = DB_data_seek($Periods,0);

	while ($myrow=DB_fetch_array($Periods,$db)){

		if($myrow['periodno']==$DefaultToPeriod){
			echo '<OPTION SELECTED VALUE="' . $myrow['periodno'] . '">' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']);
		} else {
			echo '<OPTION VALUE ="' . $myrow['periodno'] . '">' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']);
		}
	}
	echo '</SELECT></TD></TR>';
	
	$AreasResult = DB_query('SELECT areacode, areadescription FROM areas',$db);
	
	if (!isset($_POST['SalesArea'])){
		$_POST['SalesArea']='';
	}
	echo '<TR><TD>' . _('For Sales Area/Region:') .'</TD><TD><SELECT Name="SalesArea">';
	if($_POST['SalesArea']=='All'){
		echo '<OPTION SELECTED VALUE="All">' . _('All');
	} else {
		echo '<OPTION VALUE="All">' . _('All');
	}
	while ($myrow=DB_fetch_array($AreasResult)){
		if($myrow['areacode']==$_POST['SalesArea']){
			echo '<OPTION SELECTED VALUE="' . $myrow['areacode'] . '">' . $myrow['areadescription'];
		} else {
			echo '<OPTION VALUE="' . $myrow['areacode'] . '">' . $myrow['areadescription'];
		}
	}
	echo '</SELECT></TD></TR>';
	
	$CategoriesResult = DB_query('SELECT categoryid, categorydescription FROM stockcategory',$db);
	
	if (!isset($_POST['CategoryID'])){
		$_POST['CategoryID']='';
	}
	echo '<TR><TD>' . _('For Stock Category:') .'</TD><TD><SELECT Name="CategoryID">';
	if($_POST['CategoryID']=='All'){
		echo '<OPTION SELECTED VALUE="All">' . _('All');
	} else {
		echo '<OPTION VALUE="All">' . _('All');
	}
	while ($myrow=DB_fetch_array($CategoriesResult)){
		if($myrow['categoryid']==$_POST['CategoryID']){
			echo '<OPTION SELECTED VALUE="' . $myrow['categoryid'] . '">' . $myrow['categorydescription'];
		} else {
			echo '<OPTION VALUE="' . $myrow['categoryid'] . '">' . $myrow['categorydescription'];
		}
	}
	echo '</SELECT></TD></TR>';
	
	$SalesFolkResult = DB_query('SELECT salesmancode, salesmanname FROM salesman',$db);
	
	
	if (! isset($_POST['SalesmanCode'])){
 		$_POST['SalesmanCode'] = '';
	}
	
	echo '<TR><TD>' . _('For Sales Person:') .'</TD><TD><SELECT Name="SalesmanCode">';
	
	if($_POST['SalesmanCode']=='All'){
		echo '<OPTION SELECTED VALUE="All">' . _('All');
	} else {
		echo '<OPTION VALUE="All">' . _('All');
	}
	while ($myrow=DB_fetch_array($SalesFolkResult)){
		if ($myrow['salesmancode']== $_POST['SalesmanCode']){
			echo '<OPTION SELECTED VALUE="' . $myrow['salesmancode'] . '">' . $myrow['salesmanname'];
		} else {
			echo '<OPTION VALUE="' . $myrow['salesmancode'] . '">' . $myrow['salesmanname'];
		}
	}
	echo '</SELECT></TD><TD>' . $_POST['SalesmanCode'] . '</TD></TR>';
	
	if (!isset($_POST['ValueFrom'])){
		$_POST['ValueFrom']='';
	}
	if (!isset($_POST['ValueTo'])){
		$_POST['ValueTo']='';
	}
	echo '<TR><TD>' . _('Graph On:') . '</TD><TD>
			<INPUT TYPE="RADIO" NAME="GraphOn" VALUE="All" CHECKED>' . _('All') . '<BR>
			<INPUT TYPE="RADIO" NAME="GraphOn" VALUE="Customer">' . _('Customer') . '<BR>
			<INPUT TYPE="RADIO" NAME="GraphOn" VALUE="StockID">' . _('Item Code') . '</TD></TR>';
	echo '<TR><TD>' . _('From:') . ' <INPUT TYPE=TEXT NAME="ValueFrom" VALUE=' . $_POST['ValueFrom'] . '></TD>
	 		<TD>' . _('To:') . ' <INPUT TYPE=TEXT NAME="ValueTo" VALUE=' . $_POST['ValueTo'] . '></TD></TR>';
	
	echo '<TR><TD>' . _('Graph Value:') . '</TD><TD>
			<INPUT TYPE="RADIO" NAME="GraphValue" VALUE="Net" CHECKED>' . _('Net Sales Value') . '<BR>
			<INPUT TYPE="RADIO" NAME="GraphValue" VALUE="GP">' . _('Gross Profit') . '<BR>
			<INPUT TYPE="RADIO" NAME="GraphValue" VALUE="Quantity">' . _('Quantity') . '</TD></TR>';	
	
	echo '</TABLE>';
 
	echo '<br><INPUT TYPE=SUBMIT Name="ShowGraph" Value="' . _('Show Sales Graph') .'"></CENTER>';
 } else {
 
	$graph =& new PHPlot(950,450);
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
		$WhereClause .= "  cust>='" . $_POST['ValueFrom'] . "' AND cust<='" . $_POST['ValueTo'] . "' AND";
	}
	if ($_POST['GraphOn']=='StockID'){
		$GraphTitle .= ' ' . _('For Items from') . ' ' . $_POST['ValueFrom'] . ' ' . _('to') . ' ' . $_POST['ValueTo'];
		$WhereClause .= "  stockid>='" . $_POST['ValueFrom'] . "' AND stockid<='" . $_POST['ValueTo'] . "' AND";
	}
	
	$WhereClause = 'WHERE ' . $WhereClause . ' salesanalysis.periodno>=' . $_POST['FromPeriod'] . ' AND salesanalysis.periodno <= ' . $_POST['ToPeriod'];
		
	$SQL = 'SELECT salesanalysis.periodno, 
				periods.lastdate_in_period, 
				SUM(CASE WHEN budgetoractual=1 THEN ' . $SelectClause . ' ELSE 0 END) AS sales,
				SUM(CASE WHEN  budgetoractual=0 THEN ' . $SelectClause . ' ELSE 0 END) AS budget
		FROM salesanalysis INNER JOIN periods ON salesanalysis.periodno=periods.periodno ' . $WhereClause . '
		GROUP BY salesanalysis.periodno,
			periods.lastdate_in_period
		ORDER BY salesanalysis.periodno';
		
		
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
	$graph->SetBackgroundColor("wheat");
	$graph->SetTitleColor("blue");
	$graph->SetFileFormat("png");
	$graph->SetPlotType("bars");
	$graph->SetIsInline("1");
	$graph->SetShading(5);
	$graph->SetDrawYGrid(TRUE);
	$graph->SetMarginsPixels(80,40,40,40);
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
		array('blue','red'),  //Data Colors
		array('black')	//Border Colors
	);  
	$graph->SetLegend(array(_('Actual'),_('Budget')));
	
	//Draw it
	$graph->DrawGraph();
	echo '<p><center><img src="companies/' .$_SESSION['DatabaseName'] .  '/reports/salesgraph.png" alt="Sales Report Graph"></img></center></p>';
	include('includes/footer.inc');
 }
 ?>
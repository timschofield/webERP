<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Run MRP Calculation');
$ViewTopic = 'MRP';
$BookMark = 'MRP_Overview';
include('includes/header.php');

if (isset($_POST['submit'])) {

	if (!isset($_POST['Leeway']) OR !is_numeric(filter_number_format($_POST['Leeway']))) {
		$_POST['Leeway'] = 0;
	}

	// MRP - Create levels table based on bom
	echo '<br />'  .__('Start time') . ': ' . date('h:i:s') . '<br />';
	echo '<br />' . __('Initialising tables .....') . '<br />';
	flush();
	$Result = DB_query("DROP TABLE IF EXISTS tempbom");
	$Result = DB_query("DROP TABLE IF EXISTS passbom");
	$Result = DB_query("DROP TABLE IF EXISTS passbom2");
	$Result = DB_query("DROP TABLE IF EXISTS bomlevels");
	$Result = DB_query("DROP TABLE IF EXISTS levels");

	$SQL = "CREATE TEMPORARY TABLE passbom (part char(20),
											sortpart text) DEFAULT CHARSET=utf8";
	$ErrMsg = __('The SQL to create passbom failed with the message');
	$Result = DB_query($SQL, $ErrMsg);

	$SQL = "CREATE TEMPORARY TABLE tempbom (parent char(20),
											component char(20),
											sortpart text,
											level int) DEFAULT CHARSET=utf8";
	$Result = DB_query($SQL,__('Create of tempbom failed because'));
	// To create levels, first, find parts in bom that are top level assemblies.
	// Do this by doing a LEFT JOIN from bom to bom (as bom2), linking
	// bom.PARENT to bom2.COMPONENT and using WHERE bom2.COMPONENT IS NULL
	// Put those top level assemblies in passbom, use COMPONENT in passbom
	// to link to PARENT in bom to find next lower level and accumulate
	// those parts into tempbom

	prnMsg(__('Creating first level'),'info');
	flush();
	// This finds the top level
	$SQL = "INSERT INTO passbom (part, sortpart)
					   SELECT bom.component AS part,
							  CONCAT(bom.parent,'%',bom.component) AS sortpart
							  FROM bom LEFT JOIN bom as bom2
							  ON bom.parent = bom2.component
					  WHERE bom2.component IS NULL";
	$Result = DB_query($SQL);

	$lctr = 2;
	// $lctr is the level counter
	$SQL = "INSERT INTO tempbom (parent, component, sortpart, level)
			  SELECT bom.parent AS parent, bom.component AS component,
					 CONCAT(bom.parent,'%',bom.component) AS sortpart,
					 '". $lctr. "' as level
					 FROM bom LEFT JOIN bom as bom2 ON bom.parent = bom2.component
			  WHERE bom2.component IS NULL";
	$Result = DB_query($SQL);
	//echo "<br />sql is $SQL<br />";
	// This while routine finds the other levels as long as $compctr - the
	// component counter - finds there are more components that are used as
	// assemblies at lower levels
	prnMsg(__('Creating other levels'),'info');
	flush();
	$compctr = 1;
	while ($compctr > 0) {
		$lctr++;
		$SQL = "INSERT INTO tempbom (parent, component, sortpart, level)
		  SELECT bom.parent AS parent, bom.component AS component,
			 CONCAT(passbom.sortpart,'%',bom.component) AS sortpart,
			 '" .$lctr . "' as level
			 FROM bom,passbom WHERE bom.parent = passbom.part";
		$Result = DB_query($SQL);

		$Result = DB_query("DROP TABLE IF EXISTS passbom2");
		$Result = DB_query("ALTER TABLE passbom RENAME AS passbom2");
		$Result = DB_query("DROP TABLE IF EXISTS passbom");

		$SQL = "CREATE TEMPORARY TABLE passbom (part char(20),
												sortpart text) DEFAULT CHARSET=utf8";
		$Result = DB_query($SQL);

		$SQL = "INSERT INTO passbom (part, sortpart)
				   SELECT bom.component AS part,
						  CONCAT(passbom2.sortpart,'%',bom.component) AS sortpart
						  FROM bom,passbom2
				   WHERE bom.parent = passbom2.part";
		$Result = DB_query($SQL);


		$SQL = "SELECT COUNT(*) FROM bom
						INNER JOIN passbom ON bom.parent = passbom.part
						GROUP BY bom.parent";
		$Result = DB_query($SQL);

		$MyRow = DB_fetch_row($Result);
		$compctr = $MyRow[0];

	} // End of while $compctr > 0

	prnMsg(__('Creating bomlevels table'),'info');
	flush();
	$SQL = "CREATE TEMPORARY TABLE bomlevels (
									part char(20),
									level int) DEFAULT CHARSET=utf8";
	$Result = DB_query($SQL);

	// Read tempbom and split sortpart into separate parts. For each separate part, calculate level as
	// the sortpart level minus the position in the @parts array of the part. For example, the first
	// part in the array for a level 4 sortpart would be created as a level 3 in levels, the fourth
	// and last part in sortpart would have a level code of zero, meaning it has no components

	$SQL = "SELECT * FROM tempbom";
	$Result = DB_query($SQL);
	while ($MyRow=DB_fetch_array($Result)) {
			$Parts = explode('%',$MyRow['sortpart']);
			$Level = $MyRow['level'];
			$ctr = 0;
			foreach ($Parts as $Part) {
				$ctr++;
				$Newlevel = $Level - $ctr;
				$SQL = "INSERT INTO bomlevels (part, level) VALUES('" . $Part . "','" . $Newlevel . "')";
				$Result2 = DB_query($SQL);
			} // End of foreach
	}  //end of while loop

	prnMsg(__('Creating levels table'),'info');
	flush();
	// Create levels from bomlevels using the highest level number found for a part

	$SQL = "CREATE TABLE levels (
							part char(20),
							level int,
							leadtime smallint(6) NOT NULL default '0',
							pansize double NOT NULL default '0',
							shrinkfactor double NOT NULL default '0',
							eoq double NOT NULL default '0') DEFAULT CHARSET=utf8";
	$Result = DB_query($SQL);
	$SQL = "INSERT INTO levels (part,
							level,
							leadtime,
							pansize,
							shrinkfactor,
							eoq)
		   SELECT bomlevels.part,
				   MAX(bomlevels.level),
				   0,
				   pansize,
				   shrinkfactor,
				   stockmaster.eoq
			 FROM bomlevels
			   	 INNER JOIN stockmaster ON bomlevels.part = stockmaster.stockid
			 GROUP BY bomlevels.part,
					  pansize,
					  shrinkfactor,
					  stockmaster.eoq";
	$Result = DB_query($SQL);
	$SQL = "ALTER TABLE levels ADD INDEX part(part)";
	$Result = DB_query($SQL);

	// Create levels records with level of zero for all parts in stockmaster that
	// are not in bom

	$SQL = "INSERT INTO levels (part,
							level,
							leadtime,
							pansize,
							shrinkfactor,
							eoq)
			SELECT  stockmaster.stockid AS part,
					0,
					0,
					stockmaster.pansize,
					stockmaster.shrinkfactor,
					stockmaster.eoq
			FROM stockmaster
			LEFT JOIN levels ON stockmaster.stockid = levels.part
			WHERE levels.part IS NULL";
	$Result = DB_query($SQL);

	// Update leadtime in levels from purchdata. Do it twice so can make sure leadtime from preferred
	// vendor is used
	$SQL = "UPDATE levels,purchdata
					SET levels.leadtime = purchdata.leadtime
					WHERE levels.part = purchdata.stockid
					AND purchdata.leadtime > 0";
	$Result = DB_query($SQL);
	$SQL = "UPDATE levels,purchdata
						SET levels.leadtime = purchdata.leadtime
					WHERE levels.part = purchdata.stockid
					AND purchdata.preferred = 1
					AND purchdata.leadtime > 0";
	$Result = DB_query($SQL);

	prnMsg(__('Levels table has been created'),'info');
	flush();

	// Get rid if temporary tables
	$SQL = "DROP TABLE IF EXISTS tempbom";
	//$Result = DB_query($SQL);
	$SQL = "DROP TABLE IF EXISTS passbom";
	//$Result = DB_query($SQL);
	$SQL = "DROP TABLE IF EXISTS passbom2";
	//$Result = DB_query($SQL);
	$SQL = "DROP TABLE IF EXISTS bomlevels";
	//$Result = DB_query($SQL);

	// In the following section, create mrprequirements from open sales orders and
	// mrpdemands
	prnMsg(__('Creating requirements table'),'info');
	flush();
	$Result = DB_query("DROP TABLE IF EXISTS mrprequirements");
	// directdemand is 1 if demand is directly for this part, is 0 if created because have netted
	// out supply and demands for a top level part and determined there is still a net
	// requirement left and have to pass that down to the BOM parts using the
	// CreateLowerLevelRequirement() function. Mostly do this so can distinguish the type
	// of requirements for the MRPShortageReport so don't show double requirements.
	$SQL = "CREATE TABLE mrprequirements (	part char(20),
											daterequired date,
											quantity double,
											mrpdemandtype varchar(6),
											orderno int(11),
											directdemand smallint,
											whererequired char(20),
											KEY part (part)
															) DEFAULT CHARSET=utf8";
	$Result = DB_query($SQL,__('Create of mrprequirements failed because'));

	prnMsg(__('Loading requirements from sales orders'),'info');
	flush();
	$SQL = "INSERT INTO mrprequirements	(part,
										 daterequired,
										 quantity,
										 mrpdemandtype,
										 orderno,
										 directdemand,
										 whererequired)
							   SELECT stkcode,
									  itemdue,
									  (quantity - qtyinvoiced) AS netqty,
									  'SO',
									  salesorderdetails.orderno,
									  '1',
									  stkcode
							  FROM salesorders INNER JOIN salesorderdetails
								ON salesorders.orderno = salesorderdetails.orderno
								INNER JOIN stockmaster
								ON stockmaster.stockid = salesorderdetails.stkcode
							  WHERE stockmaster.discontinued = 0
							  AND (quantity - qtyinvoiced) > 0
							  AND salesorderdetails.completed = 0
							  AND salesorders.quotation = 0";
	$Result = DB_query($SQL);

	prnMsg(__('Loading requirements from work orders'),'info');
	flush();
	// Definition of demand from SelectProduct.php
	$SQL = "INSERT INTO mrprequirements	(part,
										 daterequired,
										 quantity,
										 mrpdemandtype,
										 orderno,
										 directdemand,
										 whererequired)
							   SELECT worequirements.stockid,
									workorders.requiredby,
									(qtypu*woitems.qtyreqd +
									SUM(CASE WHEN stockmoves.qty IS NOT NULL
										THEN stockmoves.qty
										ELSE 0
										END))
									AS netqty,
									'WO',
									woitems.wo,
									'1',
									parentstockid
								FROM woitems
									INNER JOIN worequirements
										ON woitems.stockid=worequirements.parentstockid
									INNER JOIN workorders
									  ON woitems.wo=workorders.wo
									  AND woitems.wo=worequirements.wo
									  INNER JOIN stockmaster
										ON woitems.stockid = stockmaster.stockid
										LEFT JOIN stockmoves ON (stockmoves.stockid = worequirements.stockid AND stockmoves.reference=woitems.wo AND type=28)
								GROUP BY workorders.wo,worequirements.stockid,workorders.requiredby,woitems.qtyreqd,worequirements.qtypu,woitems.wo,worequirements.stockid,workorders.closed,stockmaster.discontinued,stockmoves.reference,workorders.closed
								HAVING workorders.closed=0
								AND stockmaster.discontinued = 0
								AND netqty > 0";
	$Result = DB_query($SQL);

	if ($_POST['UseMRPDemands'] == 'y') {
		$SQL = "INSERT INTO mrprequirements	(part,
											 daterequired,
											 quantity,
											 mrpdemandtype,
											 orderno,
											 directdemand,
											 whererequired)
								   SELECT mrpdemands.stockid,
										  mrpdemands.duedate,
										  mrpdemands.quantity,
										  mrpdemands.mrpdemandtype,
										  mrpdemands.demandid,
										  '1',
										  mrpdemands.stockid
									 FROM mrpdemands, stockmaster
									 WHERE mrpdemands.stockid = stockmaster.stockid
										AND stockmaster.discontinued = 0";
		$Result = DB_query($SQL);
		prnMsg(__('Loading requirements based on mrpdemands'),'info');
		flush();
	}
	if ($_POST['UseRLDemands'] == 'y') {
		$SQL = "INSERT INTO mrprequirements	(part,
											 daterequired,
											 quantity,
											 mrpdemandtype,
											 orderno,
											 directdemand,
											 whererequired)
								   SELECT locstock.stockid,
										  CURRENT_DATE,
										  (locstock.reorderlevel - locstock.quantity) AS reordqty,
										  'REORD',
										  '1',
										  '1',
										  locstock.stockid
									 FROM locstock, stockmaster
									 WHERE stockmaster.stockid = locstock.stockid
										AND stockmaster.discontinued = 0
										AND reorderlevel - quantity > 0";
		$Result = DB_query($SQL);
		prnMsg(__('Loading requirements based on reorder level'),'info');
		flush();
	}

	// In the following section, create mrpsupplies from open purchase orders,
	// open work orders, and current quantity onhand from locstock
	prnMsg(__('Creating supplies table'),'info');
	flush();
	$Result = DB_query("DROP TABLE IF EXISTS mrpsupplies");
	// updateflag is set to 1 in UpdateSupplies if change date when matching requirements to
	// supplies. Actually only change update flag in the array created from mrpsupplies
	$SQL = "CREATE TABLE mrpsupplies (	id int(11) NOT NULL auto_increment,
										part char(20),
										duedate date,
										supplyquantity double,
										ordertype varchar(6),
										orderno int(11),
										mrpdate date,
										updateflag smallint(6),
										PRIMARY KEY (id)) DEFAULT CHARSET=utf8";
	$Result = DB_query($SQL,__('Create of mrpsupplies failed because'));

	prnMsg(__('Loading supplies from purchase orders'),'info');
	flush();
	$SQL = "INSERT INTO mrpsupplies	(id,
									 part,
									 duedate,
									 supplyquantity,
									 ordertype,
									 orderno,
									 mrpdate,
									 updateflag)
						   SELECT Null,
								  purchorderdetails.itemcode,
								  purchorderdetails.deliverydate,
								  (quantityord - quantityrecd) AS netqty,
								  'PO',
								  purchorderdetails.orderno,
								  purchorderdetails.deliverydate,
								  0
							  FROM purchorderdetails,
								   purchorders
						  WHERE purchorderdetails.orderno = purchorders.orderno
							AND purchorders.status != 'Cancelled'
							AND purchorders.status != 'Rejected'
							AND purchorders.status != 'Completed'
							AND(quantityord - quantityrecd) > 0
							AND purchorderdetails.completed = 0";
	$Result = DB_query($SQL);

	prnMsg(__('Loading supplies from inventory on hand'),'info');
	flush();
	// Set date for inventory already onhand to 1000-01-01 so it is first in sort
	if ($_POST['location'][0] == 'All') {
		$WhereLocation = ' ';
	} elseif (sizeof($_POST['location']) == 1) {
		$WhereLocation = " AND loccode ='" . $_POST['location'][0] . "' ";
	} else {
		$WhereLocation = " AND loccode IN(";
		$commactr = 0;
		foreach ($_POST['location'] as $key => $Value) {
			$WhereLocation .= "'" . $Value . "'";
			$commactr++;
			if ($commactr < sizeof($_POST['location'])) {
				$WhereLocation .= ",";
			} // End of if
		} // End of foreach
		$WhereLocation .= ')';
	}
	$SQL = "INSERT INTO mrpsupplies	(id,
									 part,
									 duedate,
									 supplyquantity,
									 ordertype,
									 orderno,
									 mrpdate,
									 updateflag)
						   SELECT Null,
								  stockid,
								  '2099-12-31',
								  SUM(quantity),
								  'QOH',
								  1,
								  '2099-12-31',
								  0
							  FROM locstock
							  WHERE quantity > 0 " .
							  $WhereLocation .
						  "GROUP BY stockid";
	$Result = DB_query($SQL);

	prnMsg(__('Loading supplies from work orders'),'info');
	flush();
	$SQL = "INSERT INTO mrpsupplies	(id,
									 part,
									 duedate,
									 supplyquantity,
									 ordertype,
									 orderno,
									 mrpdate,
									 updateflag)
						   SELECT Null,
								  stockid,
								  workorders.requiredby,
								  (woitems.qtyreqd-woitems.qtyrecd) AS netqty,
								  'WO',
								  woitems.wo,
								  workorders.requiredby,
								  0
							  FROM woitems INNER JOIN workorders
								ON woitems.wo=workorders.wo
								WHERE workorders.closed=0
								AND (woitems.qtyreqd-woitems.qtyrecd) > 0";
	$Result = DB_query($SQL);

	$SQL = "ALTER TABLE mrpsupplies ADD INDEX part(part)";
	$Result = DB_query($SQL);

	// Create mrpplannedorders table to create a record for any unmet requirments
	// In the following section, create mrpsupplies from open purchase orders,
	// open work orders, and current quantity onhand from locstock
	prnMsg(__('Creating planned orders table'),'info');
	flush();
	$Result = DB_query("DROP TABLE IF EXISTS mrpplannedorders");
	$SQL = "CREATE TABLE mrpplannedorders (id int(11) NOT NULL auto_increment,
											part char(20),
											duedate date,
											supplyquantity double,
											ordertype varchar(6),
											orderno int(11),
											mrpdate date,
											updateflag smallint(6),
											PRIMARY KEY (id)) DEFAULT CHARSET=utf8";
	$Result = DB_query($SQL,__('Create of mrpplannedorders failed because'));

	// Find the highest and lowest level number
	$SQL = "SELECT MAX(level),MIN(level) from levels";
	$Result = DB_query($SQL);

	$MyRow = DB_fetch_row($Result);
	$MaxLevel = $MyRow[0];
	$MinLevel = $MyRow[1];

	// At this point, have all requirements in mrprequirements and all supplies to satisfy
	// those requirements in mrpsupplies.  Starting at the top level, will read all parts one
	// at a time, compare the requirements and supplies to see if have to re-schedule or create
	// planned orders to satisfy requirements. If there is a net requirement from a higher level
	// part, that serves as a gross requirement for a lower level part, so will read down through
	// the Bill of Materials to generate those requirements in function LevelNetting().
	for ($Level = $MaxLevel; $Level >= $MinLevel; $Level--) {
		$SQL = "SELECT * FROM levels WHERE level = '" . $Level ."' LIMIT 50000"; //should cover most eventualities!! ... yes indeed :-)

		prnMsg('------ ' . __('Processing level') .' ' . $Level . ' ------','info');
		flush();
		$Result = DB_query($SQL);
		while ($MyRow=DB_fetch_array($Result)) {
			LevelNetting($MyRow['part'],$MyRow['eoq'],$MyRow['pansize'],$MyRow['shrinkfactor'], $MyRow['leadtime']);
		}  //end of while loop
	} // end of for
	echo '<br />' . __('End time') . ': ' . date('h:i:s') . '<br />';

	// Create mrpparameters table
	$SQL = "DROP TABLE IF EXISTS mrpparameters";
	$Result = DB_query($SQL);
	$SQL = "CREATE TABLE mrpparameters  (
						runtime datetime,
						location varchar(50),
						pansizeflag varchar(5),
						shrinkageflag varchar(5),
						eoqflag varchar(5),
						usemrpdemands varchar(5),
						userldemands varchar(5),
						leeway smallint) DEFAULT CHARSET=utf8";
	$Result = DB_query($SQL);
	// Create entry for location field from $_POST['location'], which is an array
	// since multiple locations can be selected
	$commactr = 0;
	$locparm='';
	foreach ($_POST['location'] as $key => $Value) {
		$locparm .=  $Value ;
		$commactr++;
		if ($commactr < sizeof($_POST['location'])) {
			$locparm .= " - ";
		} // End of if
	} // End of foreach
	$SQL = "INSERT INTO mrpparameters (runtime,
									location,
									pansizeflag,
									shrinkageflag,
									eoqflag,
									usemrpdemands,
									userldemands,
									leeway)
									VALUES (CURRENT_TIMESTAMP,
								'" . $locparm . "',
								'" .  $_POST['PanSizeFlag']  . "',
								'" .  $_POST['ShrinkageFlag']  . "',
								'" .  $_POST['EOQFlag']  . "',
								'" .  $_POST['UseMRPDemands']  . "',
								'" .  $_POST['UseRLDemands']  . "',
								'" . filter_number_format($_POST['Leeway']) . "')";
	$Result = DB_query($SQL);

} else { // End of if submit isset
	// Display form if submit has not been hit
	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' .
			__('Inventory') . '" alt="" />' . ' ' . $Title . '</p>';

	// Display parameters from last run
	$SQL = "SHOW TABLES WHERE Tables_in_" . $_SESSION['DatabaseName'] . "='mrpparameters'";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) > 0) {
		$SQL = "SELECT * FROM mrpparameters";
		$Result = DB_query($SQL, '', '', false, false);
		$MyRow = DB_fetch_array($Result);
	}
	if (DB_error_no()==0){


		if (isset($MyRow['leeway'])) {
			$Leeway = $MyRow['leeway'];
		} else {
			$Leeway = 0;
		}
		$UseMRPDemands = __('No');
		if (isset($MyRow['usemrpdemands']) and $MyRow['usemrpdemands'] == 'y') {
			 $UseMRPDemands = __('Yes');
		}
		$UseRLDemands = __('No');
		if (isset($MyRow['userldemands']) and $MyRow['userldemands'] == 'y') {
			 $UseRLDemands = __('Yes');
		}
		$useeoq = __('No');
		if (isset($MyRow['eoqflag']) and $MyRow['eoqflag'] == 'y') {
			 $useeoq = __('Yes');
		}
		$usepansize = __('No');
		if (isset($MyRow['pansizeflag']) and $MyRow['pansizeflag'] == 'y') {
			 $usepansize = __('Yes');
		}
		$useshrinkage = __('No');
		if (isset($MyRow['shrinkageflag']) and $MyRow['shrinkageflag'] == 'y') {
			 $useshrinkage = __('Yes');
		}
		if (!isset($MyRow['runtime'])) {
			$MyRow['runtime'] = 0;
		}
		if (!isset($MyRow['location'])) {
			$MyRow['location'] = '';
		}
		echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8')  . '">';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
		echo '<fieldset>
				<legend>' . __('Last Run Details') . '</legend>
				<field>
					<label>' . __('Last Run Time') . ':</label>
					<fieldtext>' . $MyRow['runtime'] . '</fieldtext>
				</field>
				<field>
					<label>' . __('Location') . ':</label>
					<fieldtext>' . $MyRow['location'] . '</fieldtext>
				</field>
				<field>
					<label>' . __('Days Leeway') . ':</label>
					<fieldtext>' . $Leeway . '</fieldtext>
				</field>
				<field>
					<label>' . __('Use MRP Demands') . ':</label>
					<fieldtext>' . $UseMRPDemands . '</fieldtext>
				</field>
				<field>
					<label>' . __('Use Reorder Level Demands') . ':</label>
					<fieldtext>' . $UseRLDemands . '</fieldtext>
				</field>
				<field>
					<label>' . __('Use EOQ') . ':</label>
					<fieldtext>' . $useeoq . '</fieldtext>
				</field>
				<field>
					<label>' . __('Use Pan Size') . ':</label>
					<fieldtext>' . $usepansize . '</fieldtext>
				</field>
				<field>
					<label>' . __('Use Shrinkage') . ':</label>
					<fieldtext>' . $useshrinkage . '</fieldtext>
				</field>
				</fieldset>';
	}
	echo '<fieldset>
			<legend>' . __('This Run Details') . '</legend>
			<field>
				<label for="Location">' . __('Location') . '</label>
				<select required="required" autofocus="autofocus" name="location[]" multiple="multiple">
					<option value="All" selected="selected">' . __('All') . '</option>';
	 $SQL = "SELECT loccode,
				locationname
			   FROM locations";
	$Result = DB_query($SQL);
	while ($MyRow = DB_fetch_array($Result)) {
		echo '<option value="';
		echo $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
	} //end while loop
	echo '</select>
		</field>';
	if (!isset($Leeway)){
		$Leeway =0;
	}

	echo '<field>
			<label for="Leeway">' . __('Days Leeway') . ':</label>
			<input type="text" required="required" name="Leeway" class="integer" size="4" value="' . $Leeway . '" />
		</field>
		<field>
			<label for="UseMRPDemands">' .__('Use MRP Demands?') . ':</label>
			<input type="checkbox" name="UseMRPDemands" value="y" checked="checked" />
		</field>
		<field>
			<label for="UseRLDemands">' .__('Use Reorder Level Demands?') . ':</label>
			<input type="checkbox" name="UseRLDemands" value="y" checked="checked" />
		</field>
		<field>
			<label for="EOQFlag">' .__('Use EOQ?') . ':</label>
			<input type="checkbox" name="EOQFlag" value="y" checked="checked" />
		</field>
		<field>
			<label for="PanSizeFlag">' .__('Use Pan Size?') . ':</label>
			<input type="checkbox" name="PanSizeFlag" value="y" checked="checked" />
		</field>
		<field>
			<label for="ShrinkageFlag">' .__('Use Shrinkage?') . ':</label>
			<input type="checkbox" name="ShrinkageFlag" value="y" checked="checked" />
		</field>
		</fieldset>
		<div class="centre">
			<input type="submit" name="submit" value="' . __('Run MRP') . '" />
		</div>
		</form>';
}  // End of Main program logic -------------------------------------------------------


function LevelNetting($Part,$eoq,$PanSize,$ShrinkFactor,$LeadTime) {
		// Create an array of mrprequirements and an array of mrpsupplies, then read through
		// them seeing if all requirements are covered by supplies. Create a planned order
		// for any unmet requirements. Change dates if necessary for the supplies.
		//echo '<br />Part is ' . "$Part" . '<br />';

		// Get decimal places from stockmaster for rounding of shrinkage factor
	$SQL = "SELECT decimalplaces FROM stockmaster WHERE stockid = '" . $Part . "'";
	$Result = DB_query($SQL);
	$MyRow=DB_fetch_row($Result);
	$DecimalPlaces = $MyRow[0];

	// Load mrprequirements into $Requirements array
	$SQL = "SELECT * FROM mrprequirements WHERE part = '" .$Part. "' ORDER BY daterequired";
	$Result = DB_query($SQL);
	$Requirements = array();
	$i = 0;
	while ($MyRow=DB_fetch_array($Result)) {
			array_push($Requirements,$MyRow);
			$i++;
	}  //end of while loop

	// Load mrpsupplies into $Supplies array
	$SQL = "SELECT * FROM mrpsupplies WHERE part = '" . $Part. "' ORDER BY duedate";
	$Result = DB_query($SQL);
	$Supplies = array();
	$i = 0;
	while ($MyRow=DB_fetch_array($Result)) {
			array_push($Supplies,$MyRow);
			$i++;
	}  //end of while loop
	// Go through all requirements and check if have supplies to cover them
	$RequirementCount = count($Requirements);
	$SupplyCount = count($Supplies);
	$reqi = 0; //Index for requirements
	$supi = 0; // index for supplies
	$TotalRequirement = 0;
	$TotalSupply = 0;

	if ($RequirementCount > 0 && $SupplyCount > 0) {
			$TotalRequirement += $Requirements[$reqi]['quantity'];
			$TotalSupply += $Supplies[$supi]['supplyquantity'];
		while ($TotalRequirement > 0 && $TotalSupply > 0) {
			$Supplies[$supi]['updateflag'] = 1;
			// ******** Put leeway calculation in here ********
			$DueDate = ConvertSQLDate($Supplies[$supi]['duedate']);
			$ReqDate = ConvertSQLDate($Requirements[$reqi]['daterequired']);
			$DateDiff = DateDiff($DueDate,$ReqDate,'d');
			if ($DateDiff > abs(filter_number_format($_POST['Leeway']))) {
				$SQL = "UPDATE mrpsupplies SET mrpdate = '" . $Requirements[$reqi]['daterequired'] .
				   "' WHERE id = '" . $Supplies[$supi]['id'] . "' AND duedate = mrpdate";
				$Result = DB_query($SQL);
			}
			if ($TotalRequirement > $TotalSupply) {
				$TotalRequirement -= $TotalSupply;
				$Requirements[$reqi]['quantity'] -= $TotalSupply;
				$TotalSupply = 0;
				$Supplies[$supi]['supplyquantity'] = 0;
				$supi++;
				if ($SupplyCount > $supi) {
					$TotalSupply += $Supplies[$supi]['supplyquantity'];
				}
			} elseif ($TotalRequirement < $TotalSupply) {
				$TotalSupply -= $TotalRequirement;
				$Supplies[$supi]['supplyquantity'] -= $TotalRequirement;
				$TotalRequirement = 0;
				$Requirements[$reqi]['quantity'] = 0;
				$reqi++;
				if ($RequirementCount > $reqi) {
					$TotalRequirement += $Requirements[$reqi]['quantity'];
				}
			} else {
				$TotalSupply -= $TotalRequirement;
				$Supplies[$supi]['supplyquantity'] -= $TotalRequirement;
				$TotalRequirement = 0;
				$Requirements[$reqi]['quantity'] = 0;
				$reqi++;
				if ($RequirementCount > $reqi) {
					$TotalRequirement += $Requirements[$reqi]['quantity'];
				}
				$TotalRequirement -= $TotalSupply;
				if(isset($Requirements[$reqi]['quantity'])){
					$Requirements[$reqi]['quantity'] -= $TotalSupply;
				}
				$TotalSupply = 0;
				$Supplies[$supi]['supplyquantity'] = 0;
				$supi++;
				if ($SupplyCount > $supi) {
					$TotalSupply += $Supplies[$supi]['supplyquantity'];
				}
			}
		} // End of while
	} // End of if

	// When get to this part of code, have gone through all requirements, If there is any
	// unmet requirements, create an mrpplannedorder to cover it. Also call the
	// CreateLowerLevelRequirement() function to create gross requirements for lower level parts.

	// There is an excess quantity if the eoq is higher than the actual required amount.
	// If there is a subsuquent requirement, the excess quantity is subtracted from that
	// quantity. For instance, if the first requirement was for 2 and the eoq was 5, there
	// would be an excess of 3; if there was another requirement for 3 or less, the excess
	// would cover it, so no planned order would have to be created for the second requirement.
	$ExcessQty = 0;
	foreach ($Requirements as $key => $Row) {
		 $DateRequired[$key] = $Row['daterequired'];
	}
	if (count($Requirements)) {
		array_multisort($DateRequired, SORT_ASC, $Requirements);
	}
	foreach($Requirements as $Requirement) {
		// First, inflate requirement if there is a shrinkage factor
		// Should the quantity be rounded?
		if ($_POST['ShrinkageFlag'] == 'y' AND $ShrinkFactor > 0) {
			$Requirement['quantity'] = ($Requirement['quantity'] * 100) / (100 - $ShrinkFactor);
			$Requirement['quantity'] = round($Requirement['quantity'],$DecimalPlaces);
		}
		if ($ExcessQty >= $Requirement['quantity']) {
			$PlannedQty = 0;
			$ExcessQty -= $Requirement['quantity'];
		} else {
			$PlannedQty = $Requirement['quantity'] - $ExcessQty;
			$ExcessQty = 0;
		}
		if ($PlannedQty > 0) {
			if ($_POST['EOQFlag'] == 'y' AND $eoq > $PlannedQty) {
				$ExcessQty = $eoq - $PlannedQty;
				$PlannedQty = $eoq;
			}
			// Pansize calculation here
			// if $PlannedQty not evenly divisible by $PanSize, calculate as $PlannedQty
			// divided by $PanSize and rounded up to the next highest integer and then
			// multiplied by the pansize. For instance, with a planned qty of 17 with a pansize
			// of 5, divide 17 by 5 to get 3 with a remainder of 2, which is rounded up to 4
			// and then multiplied by 5 - the pansize - to get 20
			if ($_POST['PanSizeFlag'] == 'y' AND $PanSize != 0 AND $PlannedQty != 0) {
				$PlannedQty = ceil($PlannedQty / $PanSize) * $PanSize;
			}

			// Calculate required date by subtracting leadtime from top part's required date
		$PartRequiredDate = $Requirement['daterequired'];
			if ((int)$LeadTime>0) {

				$CalendarSQL = "SELECT COUNT(*),cal2.calendardate
						  FROM mrpcalendar
							LEFT JOIN mrpcalendar as cal2
							  ON (mrpcalendar.daynumber - '".$LeadTime."') = cal2.daynumber
						  WHERE mrpcalendar.calendardate = '".$PartRequiredDate."'
							AND cal2.manufacturingflag='1'
							GROUP BY cal2.calendardate";
				$ResultDate = DB_query($CalendarSQL);
				$MyRowdate = DB_fetch_array($ResultDate);
				if($MyRowdate[0]>0){
					$NewDate = $MyRowdate[1];
				} else {//No calendar date available, so use $PartRequiredDate
						$ConvertDate = ConvertSQLDate($PartRequiredDate);
						$DateAdd = DateAdd($ConvertDate,'d',($LeadTime * -1));
						$NewDate = FormatDateForSQL($DateAdd);
				}
				// If can't find date based on manufacturing calendar, use $PartRequiredDate
			}  else {
				// Convert $PartRequiredDate from mysql format to system date format, use that to subtract leadtime
				// from it using DateAdd, convert that date back to mysql format
				$ConvertDate = ConvertSQLDate($PartRequiredDate);
				$DateAdd = DateAdd($ConvertDate,'d',($LeadTime * -1));
				$NewDate = FormatDateForSQL($DateAdd);
		}

		$SQL = "INSERT INTO mrpplannedorders (id,
												part,
												duedate,
												supplyquantity,
												ordertype,
												orderno,
												mrpdate,
												updateflag)
											VALUES (NULL,
												'" . $Requirement['part'] . "',
												'" . $NewDate . "',
												'" . $PlannedQty  . "',
												'" . $Requirement['mrpdemandtype']  . "',
												'" . $Requirement['orderno']  . "',
												'" . $NewDate . "',
												'0')";


			$Result = DB_query($SQL);
			// If part has lower level components, create requirements for them
			$SQL = "SELECT COUNT(*) FROM bom
					  WHERE parent ='" . $Requirement['part'] . "'
					  GROUP BY parent";
			$Result = DB_query($SQL);
			$MyRow = DB_fetch_row($Result);
			if ($MyRow[0] > 0) {
				CreateLowerLevelRequirement($Requirement['part'],$NewDate,
				  $PlannedQty,$Requirement['mrpdemandtype'],$Requirement['orderno'],
				  $Requirement['whererequired']);
			}
		} // End of if $PlannedQty > 0
	} // End of foreach $Requirements

   // If there are any supplies not used and updateflag is zero, those supplies are not
	// necessary, so change date

	foreach($Supplies as $supply) {
		if ($supply['supplyquantity'] > 0  && $supply['updateflag'] == 0) {
			$id = $supply['id'];
			$SQL = "UPDATE mrpsupplies SET mrpdate ='2050-12-31' WHERE id = '".$id."'
					  AND ordertype <> 'QOH'";
			$Result = DB_query($SQL);
		}
	}

} // End of LevelNetting -------------------------------------------------------

function CreateLowerLevelRequirement($TopPart,
									$TopDate,
									$TopQuantity,
									$TopMRPDemandType,
									$TopOrderNo,
									$WhereRequired) {
// Creates an mrprequirement based on the net requirement from the part above it in the bom
	$SQL = "SELECT bom.component,
				   bom.quantity,
				   levels.leadtime,
				   levels.eoq
			FROM bom
				 LEFT JOIN levels
				   ON bom.component = levels.part
			WHERE bom.parent = '".$TopPart."'
            AND bom.effectiveafter <= CURRENT_DATE
            AND bom.effectiveto > CURRENT_DATE";
	$ResultBOM = DB_query($SQL);
	while ($MyRow=DB_fetch_array($ResultBOM)) {
		// Calculate required date by subtracting leadtime from top part's required date
		$LeadTime = $MyRow['leadtime'];
		$Component = $MyRow['component'];
		$ExtendedQuantity = $MyRow['quantity'] * $TopQuantity;
// Commented out the following lines 8/15/09 because the eoq should be considered in the
// LevelNetting() function where $ExcessQty is calculated
//		 if ($MyRow['eoq'] > $ExtendedQuantity) {
//			 $ExtendedQuantity = $MyRow['eoq'];
//		 }
		$SQL = "INSERT INTO mrprequirements (part,
											 daterequired,
											 quantity,
											 mrpdemandtype,
											 orderno,
											 directdemand,
											 whererequired)
			   VALUES ('".$Component."',
					  '".$TopDate."',
					  '".$ExtendedQuantity."',
					  '".$TopMRPDemandType."',
					  '".$TopOrderNo."',
					  '0',
					  '".$WhereRequired."')";
		$Result = DB_query($SQL);
	}  //end of while loop

}  // End of CreateLowerLevelRequirement

include('includes/footer.php');

<?php
/* $Revision: 1.5 $ */

$PageSecurity=9;

include('includes/session.inc');
$title = _('MRP');
include('includes/header.inc');

if (isset($_POST['submit'])) {
    
    if (!$_POST['Leeway'] || !is_numeric($_POST['Leeway'])) {
	    $_POST['Leeway'] = 0;
	}
	
	// MRP - Create levels table based on bom
	echo '</br>'  ._('Start time') . ': ' . date('h:i:s') . '</br>';
	echo '</br>' . _('Initializing tables .....') . '</br>';
	flush();
	$result = DB_query('DROP TABLE IF EXISTS tempbom',$db);
	$result = DB_query('DROP TABLE IF EXISTS passbom',$db);
	$result = DB_query('DROP TABLE IF EXISTS passbom2',$db);
	$result = DB_query('DROP TABLE IF EXISTS bomlevels',$db);
	$result = DB_query('DROP TABLE IF EXISTS levels',$db);
	
	$sql = 'CREATE TEMPORARY TABLE passbom (
				part char(20),                                
				sortpart text)';
	$ErrMsg = _('The SQL to to create passbom failed with the message');
	$result = DB_query($sql,$db,$ErrMsg);
	
	$sql = 'CREATE TEMPORARY TABLE tempbom (
				parent char(20),                                
				component char(20),
				sortpart text,
				level int)';
	$result = DB_query($sql,$db,_('Create of tempbom failed because'));
	// To create levels, first, find parts in bom that are top level assemblies.
	// Do this by doing a LEFT JOIN from bom to bom (as bom2), linking
	// bom.PARENT to bom2.COMPONENT and using WHERE bom2.COMPONENT IS NULL
	// Put those top level assemblies in passbom, use COMPONENT in passbom
	// to link to PARENT in bom to find next lower level and accumulate
	// those parts into tempbom
	
	prnMsg(_('Creating first level'),'info');
	flush();
	// This finds the top level
	$sql = 'INSERT INTO passbom (part, sortpart)
			   SELECT bom.component AS part,
					  CONCAT(bom.parent,"%",bom.component) AS sortpart
					  FROM bom LEFT JOIN bom as bom2 ON bom.parent = bom2.component
			  WHERE bom2.component IS NULL';
	$result = DB_query($sql,$db);
	
	$lctr = 2; 
	// $lctr is the level counter
	$sql = "INSERT INTO tempbom (parent, component, sortpart, level)
			  SELECT bom.parent AS parent, bom.component AS component,
					 CONCAT(bom.parent,'%',bom.component) AS sortpart,
					 '$lctr' as level
					 FROM bom LEFT JOIN bom as bom2 ON bom.parent = bom2.component
			  WHERE bom2.component IS NULL";
	$result = DB_query($sql,$db);
	//echo "</br>sql is $sql</br>";
	// This while routine finds the other levels as long as $compctr - the
	// component counter - finds there are more components that are used as
	// assemblies at lower levels
	prnMsg(_('Creating other levels'),'info');
	flush();
	$compctr = 1;
	while ($compctr > 0) {
		$lctr++;
		$sql = "INSERT INTO tempbom (parent, component, sortpart, level)
		  SELECT bom.parent AS parent, bom.component AS component,
			 CONCAT(passbom.sortpart,'%',bom.component) AS sortpart,
			 $lctr as level
			 FROM bom,passbom WHERE bom.parent = passbom.part";
		$result = DB_query($sql,$db);
		
		$result = DB_query('DROP TABLE IF EXISTS passbom2',$db);
		$result = DB_query('ALTER TABLE passbom RENAME AS passbom2',$db);
		$result = DB_query('DROP TABLE IF EXISTS passbom',$db);
		
		$sql = 'CREATE TEMPORARY TABLE passbom (
			part char(20),                                
			sortpart text)';
		$result = DB_query($sql,$db);
		
		$sql = "INSERT INTO passbom (part, sortpart)
				   SELECT bom.component AS part,
						  CONCAT(passbom2.sortpart,'%',bom.component) AS sortpart
						  FROM bom,passbom2 
				   WHERE bom.parent = passbom2.part";
		$result = DB_query($sql,$db);
		
		
		$sql = 'SELECT COUNT(*) FROM bom 
		          INNER JOIN passbom ON bom.parent = passbom.part
		          GROUP BY bom.parent';
		$result = DB_query($sql,$db);
		
		$myrow = DB_fetch_row($result);
		$compctr = $myrow[0];
			
	} // End of while $compctr > 0
	
	prnMsg(_('Creating bomlevels table'),'info');
	flush();
	$sql = 'CREATE TEMPORARY TABLE bomlevels (
									part char(20),                                
									level int)';
	$result = DB_query($sql,$db);
	
	// Read tempbom and split sortpart into separate parts. For each separate part, calculate level as
	// the sortpart level minus the position in the @parts array of the part. For example, the first 
	// part in the array for a level 4 sortpart would be created as a level 3 in levels, the fourth
	// and last part in sortpart would have a level code of zero, meaning it has no components
	
	$sql = 'SELECT * FROM tempbom';
	$result = DB_query($sql,$db);
	while ($myrow=DB_fetch_array($result)) {
			$parts = explode('%',$myrow['sortpart']);
			$level = $myrow['level'];
			$ctr = 0;
			foreach ($parts as $part) {
			   $ctr++;
			   $newlevel = $level - $ctr;
			   $sql = "INSERT INTO bomlevels (part, level) VALUES('$part','$newlevel')";
			   $result2 = DB_query($sql,$db);
			} // End of foreach		
	}  //end of while loop
	
	prnMsg(_('Creating levels table'),'info');
	flush();
	// Create levels from bomlevels using the highest level number found for a part

	$sql = 'CREATE TABLE levels (
							part char(20),                                
							level int,
							leadtime smallint(6) NOT NULL default "0",
							pansize double NOT NULL default "0",
							shrinkfactor double NOT NULL default "0",
							eoq double NOT NULL default "0")';
	$result = DB_query($sql,$db);
	$sql = 'INSERT INTO levels (part,
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
					      stockmaster.eoq';
	$result = DB_query($sql,$db);
	$sql = 'ALTER TABLE levels ADD INDEX part(part)';
	$result = DB_query($sql,$db);
	
	// Create levels records with level of zero for all parts in stockmaster that
	// are not in bom
	
	$sql = 'INSERT INTO levels (part,
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
				WHERE levels.part IS NULL';
	$result = DB_query($sql,$db);
	
	// Update leadtime in levels from purchdata. Do it twice so can make sure leadtime from preferred
	// vendor is used
	$sql = 'UPDATE levels,purchdata
			  SET levels.leadtime = purchdata.leadtime
				WHERE levels.part = purchdata.stockid 
				  AND purchdata.leadtime > 0';
	$result = DB_query($sql,$db);
	$sql = 'UPDATE levels,purchdata
			  SET levels.leadtime = purchdata.leadtime
				WHERE levels.part = purchdata.stockid 
				 AND purchdata.preferred = 1
				 AND purchdata.leadtime > 0';
	$result = DB_query($sql,$db);
	
	prnMsg(_('Levels table has been created'),'info');
	flush();
	
	// Get rid if temporary tables
	$sql = 'DROP TABLE IF EXISTS tempbom';
	//$result = DB_query($sql,$db);
	$sql = 'DROP TABLE IF EXISTS passbom';
	//$result = DB_query($sql,$db);
	$sql = 'DROP TABLE IF EXISTS passbom2';
	//$result = DB_query($sql,$db);
	$sql = 'DROP TABLE IF EXISTS bomlevels';
	//$result = DB_query($sql,$db);
	
	// In the following section, create mrprequirements from open sales orders and
	// mrpdemands
	prnMsg(_('Creating requirements table'),'info');
	flush();
	$result = DB_query('DROP TABLE IF EXISTS mrprequirements',$db);
	// directdemand is 1 if demand is directly for this part, is 0 if created because have netted
	// out supply and demands for a top level part and determined there is still a net
	// requirement left and have to pass that down to the BOM parts using the
	// CreateLowerLevelRequirement() function. Mostly do this so can distinguish the type
	// of requirements for the MRPShortageReport so don't show double requirements.
	$sql = 'CREATE TABLE mrprequirements (
				part char(20),                                
				daterequired date,
				quantity double,
				mrpdemandtype varchar(6),
				orderno int(11),
				directdemand smallint,
				whererequired char(20))';
	$result = DB_query($sql,$db,_('Create of mrprequirements failed because'));
	
	prnMsg(_('Loading requirements from sales orders'),'info');
	flush();
	$sql = 'INSERT INTO mrprequirements 
						(part,
						 daterequired,
						 quantity,
						 mrpdemandtype,
						 orderno,
						 directdemand,
						 whererequired)
			   SELECT stkcode,
					  deliverydate,
					  (quantity - qtyinvoiced) AS netqty,
					  "SO",
					  salesorderdetails.orderno,
					  "1",
					  stkcode
		      FROM salesorders, salesorderdetails
			  WHERE salesorders.orderno = salesorderdetails.orderno
				  AND (quantity - qtyinvoiced) > 0
				  AND salesorderdetails.completed = 0
				  AND salesorders.quotation = 0';
	$result = DB_query($sql,$db);

	
	prnMsg(_('Loading requirements from work orders'),'info');
	flush();	
	// Definition of demand from SelectProduct.php
	$sql = 'INSERT INTO mrprequirements 
						(part,
						 daterequired,
						 quantity,
						 mrpdemandtype,
						 orderno,
						 directdemand,
						 whererequired)
			   SELECT worequirements.stockid,
					  workorders.requiredby,
					  qtypu*(woitems.qtyreqd - woitems.qtyrecd) AS netqty,
					  "WO",
					  woitems.wo,
					  "1",
					  parentstockid
					  FROM woitems INNER JOIN worequirements
						ON woitems.stockid=worequirements.parentstockid
					INNER JOIN workorders
					  ON woitems.wo=workorders.wo
					  AND woitems.wo=worequirements.wo
					WHERE workorders.closed=0';
	$result = DB_query($sql,$db);	
	
	$sql = 'INSERT INTO mrprequirements 
						(part,
						 daterequired,
						 quantity,
						 mrpdemandtype,
						 orderno,
						 directdemand,
						 whererequired)
			   SELECT stockid,
					  duedate,
					  quantity,
					  mrpdemandtype,
					  demandid,
					  "1",
					  stockid
				 FROM mrpdemands';
	if ($_POST['usemrpdemands'] == 'y') {
		$result = DB_query($sql,$db);
		prnMsg(_('Loading requirements based on mrpdemands'),'info');
		flush();
    }
	$sql = 'INSERT INTO mrprequirements 
						(part,
						 daterequired,
						 quantity,
						 mrpdemandtype,
						 orderno,
						 directdemand,
						 whererequired)
			   SELECT stockid,
					  NOW(),
					  (reorderlevel - quantity) AS reordqty,
					  "REORD",
					  "1",
					  "1",
					  stockid
				 FROM locstock
				 WHERE reorderlevel > quantity';
	$result = DB_query($sql,$db);
	prnMsg(_('Loading requirements based on reorder level'),'info');
	flush();	
	
	$result = DB_query('ALTER TABLE mrprequirements ADD INDEX part(part)',$db);
	
	// In the following section, create mrpsupplies from open purchase orders,
	// open work orders, and current quantity onhand from locstock
	prnMsg(_('Creating supplies table'),'info');
	flush();
	$result = DB_query('DROP TABLE IF EXISTS mrpsupplies',$db);
	// updateflag is set to 1 in UpdateSupplies if change date when matching requirements to 
	// supplies. Actually only change update flag in the array created from mrpsupplies
	$sql = 'CREATE TABLE mrpsupplies (
				id int(11) NOT NULL auto_increment,
				part char(20),                                
				duedate date,
				supplyquantity double,
				ordertype varchar(6),
				orderno int(11),
				mrpdate date,
				updateflag smallint(6), 
				PRIMARY KEY (id))';
	$result = DB_query($sql,$db,_('Create of mrpsupplies failed because'));
	
	prnMsg(_('Loading supplies from purchase orders'),'info');
	flush();
	$sql = 'INSERT INTO mrpsupplies 
						(id, 
						 part,
						 duedate,
						 supplyquantity,
						 ordertype,
						 orderno,
						 mrpdate,
						 updateflag)
			   SELECT Null,
					  itemcode,
					  deliverydate,
					  (quantityord - quantityrecd) AS netqty,
					  "PO",
					  orderno,
					  deliverydate,
					  0
				  FROM purchorderdetails
			  WHERE (quantityord - quantityrecd) > 0';
	$result = DB_query($sql,$db);
	
	prnMsg(_('Loading supplies from inventory on hand'),'info');
	flush();
	// Set date for inventory already onhand to 0000-00-00 so it is first in sort
	if ($_POST['location'][0] == 'All') {
	    $whereloc = ' ';
	} elseif (sizeof($_POST['location']) == 1) {
	    $whereloc = " AND loccode ='" . $_POST['location'][0] . "' ";
	} else {
	    $whereloc = " AND loccode IN(";
	    $commactr = 0;
	    foreach ($_POST['location'] as $key => $value) {
	        $whereloc .= "'" . $value . "'";
	        $commactr++;
	        if ($commactr < sizeof($_POST['location'])) {
	            $whereloc .= ",";
	        } // End of if
	    } // End of foreach
	    $whereloc .= ')';
	}
	$sql = 'INSERT INTO mrpsupplies
						(id,
						 part,
						 duedate,
						 supplyquantity,
						 ordertype,
						 orderno,
						 mrpdate,
						 updateflag)
			   SELECT Null,
					  stockid,
					  "0000-00-00",
					  SUM(quantity),
					  "QOH",
					  1,
					  "0000-00-00",
					  0
				  FROM locstock
				  WHERE quantity > 0 ' . 
				  $whereloc .
			  'GROUP BY stockid';
	$result = DB_query($sql,$db);
	
	prnMsg(_('Loading supplies from work orders'),'info');
	flush();
	$sql = 'INSERT INTO mrpsupplies 
						(id,
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
					  "WO",
					  woitems.wo,
					  workorders.requiredby,
					  0
				  FROM woitems INNER JOIN workorders
					ON woitems.wo=workorders.wo
					WHERE workorders.closed=0';
	$result = DB_query($sql,$db);
	
	$sql = 'ALTER TABLE mrpsupplies ADD INDEX part(part)';
	$result = DB_query($sql,$db);
	
	// Create mrpplannedorders table to create a record for any unmet requirments
	// In the following section, create mrpsupplies from open purchase orders,
	// open work orders, and current quantity onhand from locstock
	prnMsg(_('Creating planned orders table'),'info');
	flush();
	$result = DB_query('DROP TABLE IF EXISTS mrpplannedorders',$db);
	$sql = 'CREATE TABLE mrpplannedorders (
				id int(11) NOT NULL auto_increment,
				part char(20),                                
				duedate date,
				supplyquantity double,
				ordertype varchar(6),
				orderno int(11),
				mrpdate date,
				updateflag smallint(6), 
				PRIMARY KEY (id))';
	$result = DB_query($sql,$db,_('Create of mrpplannedorders failed because'));
	
	// Find the highest and lowest level number
	$sql = 'SELECT MAX(level),MIN(level) from levels';
	$result = DB_query($sql,$db);
	
	$myrow = DB_fetch_row($result);
	$maxlevel = $myrow[0];
	$minlevel = $myrow[1];	
	
	// At this point, have all requirements in mrprequirements and all supplies to satisfy
	// those requirements in mrpsupplies.  Starting at the top level, will read all parts one
	// at a time, compare the requirements and supplies to see if have to re-schedule or create
	// planned orders to satisfy requirements. If there is a net requirement from a higher level 
	// part, that serves as a gross requirement for a lower level part, so will read down through 
	// the Bill of Materials to generate those requirements in function LevelNetting().
	for ($level = $maxlevel; $level >= $minlevel; $level--) {
		$sql = 'SELECT * FROM levels WHERE level = ' . "$level " . ' LIMIT 50000'; //should cover most eventualities!!
		
		prnMsg('</br>------ ' . _('Processing level') .' ' . $level . ' ------','info');
		flush();
		$result = DB_query($sql,$db);
		while ($myrow=DB_fetch_array($result)) {
				LevelNetting($db,$myrow['part'],$myrow['eoq'],$myrow['pansize'],$myrow['shrinkfactor']);
		}  //end of while loop
	} // end of for
	echo '</br>' . _('End time') . ': ' . date('h:i:s') . '</br>';

	// Create mrpparameters table
	$sql = 'DROP TABLE IF EXISTS mrpparameters';
	$result = DB_query($sql,$db);
	$sql = 'CREATE TABLE mrpparameters  (
						runtime datetime,                                
						location varchar(50),
						pansizeflag varchar(5),
						shrinkageflag varchar(5),
						eoqflag varchar(5),
						usemrpdemands varchar(5),
						leeway smallint)';
	$result = DB_query($sql,$db);
	// Create entry for location field from $_POST['location'], which is an array
	// since multiple locations can be selected
	$commactr = 0;
	foreach ($_POST['location'] as $key => $value) {
		$locparm .=  $value ;
		$commactr++;
		if ($commactr < sizeof($_POST['location'])) {
			$locparm .= " - ";
		} // End of if
	} // End of foreach
	$sql = "INSERT INTO mrpparameters (runtime,
										location,
										pansizeflag,
										shrinkageflag,
										eoqflag,
										usemrpdemands,
										leeway)
										VALUES (NOW(),
									'" . $locparm . "',
									'" .  $_POST['pansizeflag']  . "',
									'" .  $_POST['shrinkageflag']  . "',
									'" .  $_POST['eoqflag']  . "',
									'" .  $_POST['usemrpdemands']  . "',
									'" . $_POST['Leeway'] . "')";
    $result = DB_query($sql,$db);
	
} else { // End of if submit isset 
    // Display form if submit has not been hit
    
    // Display parameters from last run
    $sql = 'SELECT * FROM mrpparameters';
    $result = DB_query($sql,$db,'','',false,false);
    if (DB_error_no($db)==0){
   
    	$myrow = DB_fetch_array($result);
   
		$leeway = $myrow['leeway'];
		$usemrpdemands = _('No');
		if ($myrow['usemrpdemands'] == 'y') {
			 $usemrpdemands = 'Yes';
		}
		$useeoq = _('No');
		if ($myrow['eoqflag'] == 'y') {
			 $useeoq = _('Yes');
		}
		$usepansize = _('No');
		if ($myrow['pansizeflag'] == 'y') {
			 $usepansize = _('Yes');
		}
		$useshrinkage = _('No');
		if ($myrow['shrinkageflag'] == 'y') {
			 $useshrinkage = _('Yes');
		}
			
		echo '<table><tr><td>&nbsp&nbsp&nbsp&nbsp&nbsp</td>';
		echo '<td>' . _('Last Run Time') . ':&nbsp&nbsp</td><td>' . $myrow['runtime'] . '</td></tr>';
		echo '<td></td><td>' . _('Location') . ':&nbsp&nbsp</td><td>' . $myrow['location'] . '</td></tr>';
		echo '<td></td><td>' . _('Days Leeway') . ':&nbsp&nbsp</td><td>' . $leeway . '</td></tr>';
		echo '<td></td><td>' . _('Use MRP Demands') . ':&nbsp&nbsp</td><td>' . $usemrpdemands . '</td></tr>';
		echo '<td></td><td>' . _('Use EOQ') . ':&nbsp&nbsp</td><td>' . $useeoq . '</td></tr>';
		echo '<td></td><td>' . _('Use Pan Size') . ':&nbsp&nbsp</td><td>' . $usepansize . '</td></tr>';
		echo '<td></td><td>' . _('Use Shrinkage') . ':&nbsp&nbsp</td><td>' . $useshrinkage . '</td></tr>';
		echo '</table>';
	}
    echo "<p><form method='post' action='" . $_SERVER['PHP_SELF'] . '?' . SID . "'>";
    echo '<table>';
	// Generate selections for Location
	echo '<tr>
	 <td>' . _('Location') . '</td>
	 <td><select name="location[]" multiple>
	 <option value="All" selected>' . _('All') . '</option>';
	 $sql = 'SELECT loccode
	           FROM locations';
	$result = DB_query($sql,$db);
	while ($myrow = DB_fetch_array($result)) {
		echo '<option value="';
		echo $myrow['loccode'] . '">' . $myrow['loccode'] . '</option>';
	} //end while loop
	echo '</select></td></tr>';
	if (!isset($leeway)){
		$leeway =0;
	}
	
	echo '<tr><td>' . _('Days Leeway') . ':</td><td><input type="text" name="Leeway" size="4" value=' . $leeway . '>';
    echo '<tr><td>' ._('Use MRP Demands?') . ':</td>';
    echo '<td><input type="checkbox" name="usemrpdemands" value="y" checked></td></tr>';
    echo '<tr><td>' ._('Use EOQ?') . ':</td>';
    echo '<td><input type="checkbox" name="eoqflag" value="y" checked></td></tr>';
    echo '<tr><td>' ._('Use Pan Size?') . ':</td>';
    echo '<td><input type="checkbox" name="pansizeflag" value="y" checked></td></tr>';
    echo '<tr><td>' ._('Use Shrinkage?') . ':</td>';
    echo '<td><input type="checkbox" name="shrinkageflag" value="y" checked></td></tr>';
    echo '</table><div class="centre"></br></br><input type="submit" name="submit" value="' . _('Run MRP') . '"></div>';
    echo '</form>';
}  // End of Main program logic -------------------------------------------------------



function LevelNetting(&$db,$part,$eoq,$pansize,$shrinkfactor) {
// Create an array of mrprequirements and an array of mrpsupplies, then read through
// them seeing if all requirements are covered by supplies. Create a planned order
// for any unmet requirements. Change dates if necessary for the supplies.
    //echo '</br>Part is ' . "$part" . '</br>';
    
    // Get decimal places from stockmaster for rounding of shrinkage factor
    $sql = "SELECT decimalplaces FROM stockmaster WHERE stockid = '" . $part . "'";
	$result = DB_query($sql,$db);
	$myrow=DB_fetch_row($result);
	$decimalplaces = $myrow[0];
	
    // Load mrprequirements into $requirements array
    $sql = "SELECT * FROM mrprequirements WHERE part = '" . "$part" . "' ORDER BY daterequired";
	$result = DB_query($sql,$db);
	$requirements = array();
	$i = 0;
	while ($myrow=DB_fetch_array($result)) {
			array_push($requirements,$myrow);
			$i++;
	}  //end of while loop

    // Load mrpsupplies into $supplies array
    $sql = "SELECT * FROM mrpsupplies WHERE part = '" . "$part" . "' ORDER BY duedate";
	$result = DB_query($sql,$db);
	$supplies = array();
	$i = 0;
	while ($myrow=DB_fetch_array($result)) {
			array_push($supplies,$myrow);
			$i++;
	}  //end of while loop

    // Go through all requirements and check if have supplies to cover them
    $requirementcount = count($requirements);
    $supplycount = count($supplies);
    $reqi = 0; //Index for requirements 
    $supi = 0; // index for supplies
    $totalrequirement = 0;
    $totalsupply = 0;

    if ($requirementcount > 0 && $supplycount > 0) {
        $totalrequirement += $requirements[$reqi]['quantity'];
        $totalsupply += $supplies[$supi]['supplyquantity'];
        while ($totalrequirement > 0 && $totalsupply > 0) {
        		$supplies[$supi]['updateflag'] = 1;
        		// ******** Put leeway calculation in here ********
        		$duedate = ConvertSQLDate($supplies[$supi]['duedate']);
        		$reqdate = ConvertSQLDate($requirements[$reqi]['daterequired']);
        		$datediff = DateDiff($duedate,$reqdate,'d');
				//if ($supplies[$supi]['duedate'] > $requirements[$reqi]['daterequired']) {
				if ($datediff > abs($_POST['Leeway'])) {
				    $sql = "UPDATE mrpsupplies SET mrpdate = '" . $requirements[$reqi]['daterequired'] . 
				       "' WHERE id = '" . $supplies[$supi]['id'] . "' AND duedate = mrpdate";
	                $result = DB_query($sql,$db);
				}      

               if ($totalrequirement > $totalsupply) {
                   $totalrequirement -= $totalsupply;
                   $requirements[$reqi]['quantity'] -= $totalsupply;
                   $totalsupply = 0;
                   $supplies[$supi]['supplyquantity'] = 0;
                   $supi++;
                   if ($supplycount > $supi) {
                       $totalsupply += $supplies[$supi]['supplyquantity'];
                   }
               } else {
                   $totalsupply -= $totalrequirement;
                   $supplies[$supi]['supplyquantity'] -= $totalrequirement;
                   $totalrequirement = 0;
                   $requirements[$reqi]['quantity'] = 0;
                   $reqi++;
                   if ($requirementcount > $reqi) {
                       $totalrequirement += $requirements[$reqi]['quantity'];
                   }
              } // End of if $totalrequirement > $totalsupply             
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
    $excessqty = 0;
    foreach ($requirements as $key => $row) {
             $daterequired[$key] = $row['daterequired'];
    }
    if (count($requirements)) {
        array_multisort($daterequired, SORT_ASC, $requirements);
    }
    foreach($requirements as $requirement) {
        // First, inflate requirement if there is a shrinkage factor
        // Should the quantity be rounded?
        if ($_POST['shrinkageflag'] == 'y' and $shrinkfactor > 0) {
	        $requirement['quantity'] = ($requirement['quantity'] * 100) / (100 - $shrinkfactor);
	        $requirement['quantity'] = round($requirement['quantity'],$decimalplaces); 
	    }
        if ($excessqty >= $requirement['quantity']) {
            $plannedqty = 0;
            $excessqty -= $requirement['quantity'];
        } else {
            $plannedqty = $requirement['quantity'] - $excessqty;
            $excessqty = 0;
        }
        if ($plannedqty > 0) {
            if ($_POST['eoqflag'] == 'y' and $eoq > $plannedqty) {
                $excessqty = $eoq - $plannedqty;
                $plannedqty = $eoq;
            }
            // Pansize calculation here
            // if $plannedqty not evenly divisible by $pansize, calculate as $plannedqty
            // divided by $pansize and rounded up to the next highest integer and then
            // multiplied by the pansize. For instance, with a planned qty of 17 with a pansize
            // of 5, divide 17 by 5 to get 3 with a remainder of 2, which is rounded up to 4
            // and then multiplied by 5 - the pansize - to get 20
            if ($_POST['pansizeflag'] == 'y' and $pansize != 0 and $plannedqty % $pansize != 0) {
                $plannedqty = ceil($plannedqty / $pansize) * $pansize;
            }
			$sql = "INSERT INTO mrpplannedorders (id,
								part,
								duedate,
								supplyquantity,
								ordertype,
								orderno,
								mrpdate,
								updateflag)
							VALUES (NULL,
								'" . $requirement['part'] . "',
								'" .  $requirement['daterequired']  . "',
								'" .  $plannedqty  . "',
								'" .  $requirement['mrpdemandtype']  . "',
								'" .  $requirement['orderno']  . "',
								'" . $requirement['daterequired'] . "',
								'0')";
			$result = DB_query($sql,$db);
			// If part has lower level components, create requirements for them
			$sql = "SELECT COUNT(*) FROM bom 
			          WHERE parent ='" . $requirement['part'] . "' 
			          GROUP BY parent";
	        $result = DB_query($sql,$db);
	        $myrow = DB_fetch_row($result);
	        if ($myrow[0] > 0) {
			    CreateLowerLevelRequirement($db,$requirement['part'],$requirement['daterequired'],
			      $plannedqty,$requirement['mrpdemandtype'],$requirement['orderno'],
			      $requirement['whererequired']);
			}
        } // End of if $plannedqty > 0
    } // End of foreach $requirements
   
   // If there are any supplies not used and updateflag is zero, those supplies are not
    // necessary, so change date
    
    foreach($supplies as $supply) {
        if ($supply['supplyquantity'] > 0  && $supply['updateflag'] == 0) {
			$id = $supply['id'];
			$sql = "UPDATE mrpsupplies SET mrpdate ='2050-12-31' WHERE id = '$id'
			          AND ordertype <> 'QOH'";
	        $result = DB_query($sql,$db);
        }
    }

} // End of LevelNetting -------------------------------------------------------

function CreateLowerLevelRequirement(&$db,
									$toppart,
									$topdate,
									$topquantity,
									$topmrpdemandtype,
									$toporderno,
									$whererequired) {
// Creates an mrprequirement based on the net requirement from the part above it in the bom
    $sql = "SELECT bom.component,
                   bom.quantity,
                   levels.leadtime,
                   levels.eoq
            FROM bom
                 LEFT JOIN levels 
                   ON bom.component = levels.part
            WHERE bom.parent = '$toppart'
		 AND effectiveafter <= now() 
		 AND effectiveto >= now()";
	$resultbom = DB_query($sql,$db);
	while ($myrow=DB_fetch_array($resultbom)) {
	    // Calculate required date by subtracting leadtime from top part's required date
	    $leadtime = $myrow['leadtime'];
	    
	    // Following sql finds daynumber for the top part's required date, subtracts leadtime, and finds  
	    // a valid manufacturing date for the daynumber. There is only one valid manufacturing date
	    // for each daynumber, but there could be several non-manufacturing dates for the 
	    // same daynumber. MRPCalendar.php maintains the manufacturing calendar.
	    $calendarsql = "SELECT COUNT(*),cal2.calendardate 
	                      FROM mrpcalendar 
	                        LEFT JOIN mrpcalendar as cal2 
	                          ON (mrpcalendar.daynumber - $leadtime) = cal2.daynumber
	                      WHERE mrpcalendar.calendardate = '$topdate'
	                        AND cal2.manufacturingflag='1'
	                        GROUP BY cal2.calendardate";
        $resultdate = DB_query($calendarsql,$db);
        $myrowdate=DB_fetch_array($resultdate);
        $newdate = $myrowdate[1];
        // If can't find date based on manufacturing calendar, use $topdate
        if ($myrowdate[0] == 0){
           // Convert $topdate from mysql format to system date format, use that to subtract leadtime
           // from it using DateAdd, convert that date back to mysql format 
           $convertdate = ConvertSQLDate($topdate);
           $dateadd = DateAdd($convertdate,"d",($leadtime * -1));
           $newdate = FormatDateForSQL($dateadd);
        }

        	$component = $myrow['component'];
        $extendedquantity = $myrow['quantity'] * $topquantity;
// Commented out the following lines 8/15/09 because the eoq should be considered in the 
// LevelNetting() function where $excessqty is calculated
//         if ($myrow['eoq'] > $extendedquantity) {
//             $extendedquantity = $myrow['eoq'];
//         }
		$sql = "INSERT INTO mrprequirements 
						(part,
						 daterequired,
						 quantity,
						 mrpdemandtype,
						 orderno,
						 directdemand,
						 whererequired)
			   VALUES ('$component',
					  '$newdate',
					  '$extendedquantity',
					  '$topmrpdemandtype',
					  '$toporderno',
					  '0',
					  '$whererequired')";
		$result = DB_query($sql,$db);
	}  //end of while loop

}  // End of CreateLowerLevelRequirement

include('includes/footer.inc');
?>
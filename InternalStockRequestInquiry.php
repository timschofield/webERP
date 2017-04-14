<?php
//Token 19 is used as the authority overwritten token to ensure that all internal request can be viewed.
include('includes/session.php');
$Title = _('Internal Stock Request Inquiry');
include('includes/header.php');

echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/transactions.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '</p>';

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<div>';
if (isset($_POST['ResetPart'])) {
	unset($SelectedStockItem);
}
echo '<br/><div class="centre">';
if (isset($_POST['RequestNo'])) {
	$RequestNo = $_POST['RequestNo'];
}
if (isset($_POST['SearchPart'])) {
	$StockItemsResult = GetSearchItems();
}
if (isset($_POST['StockID'])) {
	$StockID = trim(mb_strtoupper($_POST['StockID']));
}
if (isset($_POST['SelectedStockItem'])) {
	$StockID = $_POST['SelectedStockItem'];
}

if (!isset($StockID) AND !isset($_POST['Search'])) {//The scripts is just opened or click a submit button
	if (!isset($RequestNo) OR $RequestNo == '') {
		echo '<table class="selection">
			<tr>
				<td>' . _('Request Number') . ':</td>
				<td><input type="text" name="RequestNo" maxlength="8" size="9" /></td>
				<td>' . _('From Stock Location') . ':</td>
				<td><select name="StockLocation">';
		$sql = "SELECT locations.loccode, locationname, canview FROM locations
			INNER JOIN locationusers 
				ON locationusers.loccode=locations.loccode 
				AND locationusers.userid='" . $_SESSION['UserID'] . "'
				AND locationusers.canview=1 
				AND locations.internalrequest=1";
		$LocResult = DB_query($sql);
		$LocationCounter = DB_num_rows($LocResult);
		$locallctr = 0;//location all counter
		$Locations = array();
		if ($LocationCounter>0) {
			while ($myrow = DB_fetch_array($LocResult)) {
				$Locations[] = $myrow['loccode'];
				if (isset($_POST['StockLocation'])){
					if ($_POST['StockLocation'] == 'All' AND $locallctr == 0) {
						$locallctr = 1;
						echo '<option value="All" selected="selected">' . _('All') . '</option>';
					} elseif ($myrow['loccode'] == $_POST['StockLocation']) {
						echo '<option selected="selected" value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
					}
				} else {
					if ($LocationCounter>1 AND $locallctr == 0) {//we show All only when it is necessary	
						echo '<option value="All">' . _('All') . '</option>';
						$locallctr = 1;
					}
					echo '<option value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
				}
			}
			echo '<select></td>';
		} else {//there are possiblity that the user is the authorization person,lets figure things out

			$sql = "SELECT stockrequest.loccode,locations.locationname FROM stockrequest INNER JOIN locations ON stockrequest.loccode=locations.loccode
				INNER JOIN department ON stockrequest.departmentid=department.departmentid WHERE department.authoriser='" . $_SESSION['UserID'] . "'";
			$authresult = DB_query($sql);
			$LocationCounter = DB_num_rows($authresult);
			if ($LocationCounter>0) {
				$Authorizer = true;
			
				while ($myrow = DB_fetch_array($authresult)) {
					$Locations[] = $myrow['loccode'];
					if (isset($_POST['StockLocation'])) {
						if ($_POST['StockLocation'] == 'All' AND $locallctr==0) {
							echo '<option value="All" selected="selected">' . _('All') . '</option>';
							$locallctr = 1;
						} elseif ($myrow['loccode'] == $_POST['StockLocation']) {
							echo '<option value="' . $myrow['loccode'] . '" selected="selected">' . $myrow['locationname'] . '</option>';
						}
					} else {
						if ($LocationCounter>1 AND $locallctr == 0) {
							$locallctr = 1;
							echo '<option value="All">' . _('All') . '</option>';
						}
						echo '<option value="' . $myrow['loccode'] . '">' . $myrow['locationname'] .'</option>';
					}
				}
				echo '</select></td>';
			

			} else {
				prnMsg(_('You have no authority to do the internal request inquiry'),'error');
				include('includes/footer.php');
				exit;
			}
		}
		echo '<input type="hidden" name="Locations" value="' . serialize($Locations) . '" />';//store the locations for later using;
		if (!isset($_POST['Authorized'])) {
			$_POST['Authorized'] = 'All';
		}
		echo '<td>' . _('Authorisation status') . '</td>
			<td><select name="Authorized">';
		$Auth = array('All'=>_('All'),0=>_('Unauthorized'),1=>_('Authorized'));
		foreach ($Auth as $key=>$value) {
			if ($_POST['Authorized'] == $value) {
				echo '<option selected="selected" value="' . $key . '">' . $value . '</option>';
			} else {
				echo '<option value="' . $key . '">' . $value . '</option>';
			}
		}
		echo '</select></td></tr>';
	}
	//add the department, sometime we need to check each departments' internal request
	if (!isset($_POST['Department'])) {
		$_POST['Department'] = '';
	}

	echo '<td>' . _('Department') . '</td>
		<td><select name="Department">';
	//now lets retrieve those deparment available for this user;
	$sql = "SELECT departments.departmentid, 
			departments.description
			FROM departments LEFT JOIN stockrequest 
				ON departments.departmentid = stockrequest.departmentid
				AND (departments.authoriser = '" . $_SESSION['UserID'] . "' OR stockrequest.initiator = '" . $_SESSION['UserID'] . "') 
			WHERE stockrequest.dispatchid IS NOT NULL 
			GROUP BY stockrequest.departmentid";//if a full request is need, the users must have all of those departments' authority 
	$depresult = DB_query($sql);
	if (DB_num_rows($depresult)>0) {
		$Departments = array(); 
		if (isset($_POST['Department']) AND $_POST['Department'] == 'All') {
			echo '<option selected="selected" value="All">' . _('All') . '</option>';
		} else {
			echo '<option value="All">' . _('All') . '</option>';
		}
		while ($myrow = DB_fetch_array($depresult)) {
			$Departments[] = $myrow['departmentid'];
			if (isset($_POST['Department']) AND ($_POST['Department'] == $myrow['departmentid'])) {
				echo '<option selected="selected" value="' . $myrow['departmentid'] . '">' . $myrow['description'] . '</option>';
			} else {
				echo '<option value="' . $myrow['departmentid'] . '">' . $myrow['description'] . '</option>';
			}
		}
		echo '</select></td>';
		echo '<input type="hidden" name="Departments" value="' . base64_encode(serialize($Departments)) . '" />';
	} else {
		prnMsg(_('There are no internal request result available for your or your department'),'error');
		include('includes/footer.php');
		exit;
	}

		//now lets add the time period option
	if (!isset($_POST['ToDate'])) {
		$_POST['ToDate'] = '';
	}
	if (!isset($_POST['FromDate'])) {
		$_POST['FromDate'] = '';
	}
	echo '<td>' . _('Date From') . '</td>
		<td><input type="text" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate" maxlength="10" size="11" vaue="' . $_POST['FromDate'] .'" /></td> 
		<td>' . _('Date To') . '</td>
		<td><input type="text" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" maxlength="10" size="11" value="' . $_POST['ToDate'] . '" /></td>
		<td><input type="submit" name="Search"  value="' ._('Search') . '" /></td></tr></table>	
		
		';
	if (!isset($_POST['ShowDetails'])) {
		$_POST['ShowDetails'] = 1;
	}
	$Checked = ($_POST['ShowDetails'] == 1)?'checked="checked"':'';
	echo '<td>' . _('Show Details') . '
		<input type="checkbox" ' . $Checked . ' name="ShowDetails" /> </td>';
	//following is the item search parts which belong to the existed internal request, we should not search it generally, it'll be rediculous 
	//hereby if the authorizer is login, we only show all category available, even if there is problem, it'll be correceted later when items selected -:)
	if (isset($Authorizer)) { 
		$WhereAuthorizer = '';
	} else {
		$WhereAuthorizer = " AND internalstockcatrole.secroleid = '" . $_SESSION['AccessLevel'] . "' ";
	}

	$SQL = "SELECT stockcategory.categoryid,
				stockcategory.categorydescription
			FROM stockcategory, internalstockcatrole
			WHERE stockcategory.categoryid = internalstockcatrole.categoryid
				" . $WhereAuthorizer . "
			ORDER BY stockcategory.categorydescription";
	$result1 = DB_query($SQL);
	//first lets check that the category id is not zero
	$Cats = DB_num_rows($result1);


	if ($Cats >0) {
		
		echo '<br /><table class="selection">
			<tr>
				<th colspan="6"><h3>' . _('To search for internal request for a specific part use the part selection facilities below') . '</h3></th>
			</tr>
			<tr>
				<td>' . _('Stock Category') . '</td>
				<td><select name="StockCat">';
				
		if (!isset($_POST['StockCat'])) {
			$_POST['StockCat'] = '';
		}
		if ($_POST['StockCat'] == 'All') {
			echo '<option selected="selected" value="All">' . _('All Authorized') . '</option>';
		} else {
			echo '<option value="All">' . _('All Authorized') . '</option>';
		}
		while ($myrow1 = DB_fetch_array($result1)) {
			if ($myrow1['categoryid'] == $_POST['StockCat']) {
				echo '<option selected="selected" value="' . $myrow1['categoryid'] . '">' . $myrow1['categorydescription'] . '</option>';
			} else {
				echo '<option value="' . $myrow1['categoryid'] . '">' . $myrow1['categorydescription'] . '</option>';
			}
		}
		echo '</selected></td>
			<td>' . _('Enter partial') . '  <b>' . _('Description') . '</b>:</td>';
		if (!isset($_POST['Keywords'])) {
			$_POST['Keywords'] = '';
		}
		echo '<td><input type="text" name="Keywords" value="' . $_POST['Keywords'] . '" size="20" maxlength="25" /></td>';		
		echo '</tr>
				<tr>
					<td></td>
					<td></td>
					<td>' . _('OR') . ' ' .  _('Enter partial') . ' <b>' . _('Stock Code') . '</b>:</td>';
		if (!isset($_POST['StockCode'])) {
			$_POST['StockCode'] = '';
		}
		echo '<td><input type="text" autofocus="autofocus" name="StockCode" value="' . $_POST['StockCode'] . '" size="15" maxlength="18" /></td>';

	}
	echo '</tr>
			</table>
			<br/>
			<div class="centre">
				<input type="submit" name="SearchPart" value="' . _('Search Now') . '" />
				<input type="submit" name="ResetPart" value="' . _('Show All') . '" />
			</div>
			<br />
			</div>
			</form>';

	if ($Cats == 0) {

		echo '<p class="bad">' . _('Problem Report') . ':<br />' . _('There are no stock categories currently defined please use the link below to set them up') . '</p>';
		echo '<br />
			<a href="' . $RootPath . '/StockCategories.php">' . _('Define Stock Categories') . '</a>';
		exit;
	}


} 

if(isset($StockItemsResult)){

	if (isset($StockItemsResult)
	AND DB_num_rows($StockItemsResult)>1) {
	echo '<a href="' . $RootPath . '/InternalStockRequestInquiry.php">' . _('Return') . '</a>';
	echo '<table cellpadding="2" class="selection">';
	echo '<tr>
			<th class="ascending" >' . _('Code') . '</th>
			<th class="ascending" >' . _('Description') . '</th>
			<th class="ascending" >' . _('Total Applied') . '</th>
			<th>' . _('Units') . '</th>
		</tr>';

	$k=0; //row colour counter

	while ($myrow=DB_fetch_array($StockItemsResult)) {

		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k++;
		}

		printf('<td><input type="submit" name="SelectedStockItem" value="%s" /></td>
				<td>%s</td>
				<td class="number">%s</td>
				<td>%s</td>
				</tr>',
				$myrow['stockid'],
				$myrow['description'],
				locale_number_format($myrow['qoh'],$myrow['decimalplaces']),
				$myrow['units']);
//end of page full new headings if
	}
//end of while loop

	echo '</table>';

}
	
} elseif(isset($_POST['Search']) OR isset($StockID)) {//lets show the search result here
	if (isset($StockItemsResult) AND DB_num_rows($StockItemsResult) == 1) {
		$StockID = DB_fetch_array($StockItemsResult);
		$StockID = $StockID[0];
	}

	if (isset($_POST['ShowDetails']) OR isset($StockID)) {
		$SQL = "SELECT stockrequest.dispatchid, 
				stockrequest.loccode,
				stockrequest.departmentid,
				departments.description,
				locations.locationname,
				despatchdate,
				authorised,
				closed,
				narrative,
				initiator,
			stockrequestitems.stockid,
			stockmaster.description as stkdescription,
			quantity,
			stockrequestitems.decimalplaces,
			uom,
			completed
			FROM stockrequest INNER JOIN stockrequestitems ON stockrequest.dispatchid=stockrequestitems.dispatchid 
			INNER JOIN departments ON stockrequest.departmentid=departments.departmentid 
			INNER JOIN locations ON locations.loccode=stockrequest.loccode 
			INNER JOIN stockmaster ON stockrequestitems.stockid=stockmaster.stockid
			"; 
	} else {
		$SQL = "SELECT stockrequest.dispatchid,
					stockrequest.loccode,
					stockrequest.departmentid,
					departments.description,
					locations.locationname,
					despatchdate,
					authorised,
					closed,
					narrative,
					initiator
					FROM stockrequest INNER JOIN departments ON stockrequest.departmentid=departments.departmentid
				        INNER JOIN locations ON locations.loccode=stockrequest.loccode	";
	}
	//lets add the condition selected by users
	if (isset($_POST['RequestNo']) AND $_POST['RequestNo'] !== '') {
		$SQL .= "WHERE stockrequest.dispatchid = '" . $_POST['RequestNo'] . "'";
	} else {
		//first the constraint of locations;
		if ($_POST['StockLocation'] != 'All') {//retrieve the location data from current code
			$SQL .= "WHERE stockrequest.loccode='" . $_POST['StockLocation'] . "'";
		} else {//retrieve the location data from serialzed data
			if (!in_array(19,$_SESSION['AllowedPageSecurityTokens'])) {
				$Locations = unserialize($_POST['Locations']);
				$Locations = implode("','",$Locations);
				$SQL .= "WHERE stockrequest.loccode in ('" . $Locations . "')";
			} else {
			 	$SQL .= "WHERE 1 ";
			}
		}
		//the authorization status
		if ($_POST['Authorized'] != 'All') {//no bothering for all
			$SQL .= " AND authorised = '" . $_POST['Authorized'] . "'";
		}
		//the department: if the department is all, no bothering for this since user has no relation ship with department; but consider the efficency, we should use the departments to filter those no needed out
		if ($_POST['Department'] == 'All') {
			if (!in_array(19,$_SESSION['AllowedPageSecurityTokens'])) {

				if (isset($_POST['Departments'])) {
					$Departments = unserialize(base64_decode($_POST['Departments']));
					$Departments = implode("','", $Departments);
					$SQL .= " AND stockrequest.departmentid IN ('" . $Departments . "')";
					
				} //IF there are no departments set,so forgot it
				
			}
		} else {
			$SQL .= " AND stockrequest.departmentid='" . $_POST['Department'] . "'";
		}
		//Date from
		if (isset($_POST['FromDate']) AND is_date($_POST['FromDate'])) {
			$SQL .= " AND despatchdate>='" . $_POST['FromDate'] . "'";
		}
		if (isset($_POST['ToDate']) AND is_date($_POST['ToDate'])) {
			$SQL .= " AND despatchdate<='" . $_POST['ToDate'] . "'";
		}
		//item selected 
		if (isset($StockID)) {
			$SQL .= " AND stockrequestitems.stockid='" . $StockID . "'";
		}
	}//end of no request no selected
		//the user or authority contraint
		if (!in_array(19,$_SESSION['AllowedPageSecurityTokens'])) {
			$SQL .= " AND (authoriser='" . $_SESSION['UserID'] . "' OR initiator='" . $_SESSION['UserID'] . "')";
		}
	$result = DB_query($SQL);
	if (DB_num_rows($result)>0) {
		$Html = '';
		if (isset($_POST['ShowDetails']) OR isset($StockID)) {
			$Html .= '<table>
					<tr>
						<th>' . _('ID') . '</th>
						<th>' . _('Locations') . '</th>
						<th>' . _('Department') . '</th>
						<th>' . _('Authorization') . '</th>
						<th>' . _('Dispatch Date') . '</th>
						<th>' . _('Stock ID') . '</th>
						<th>' . _('Description') . '</th>
						<th>' . _('Quantity') . '</th>
						<th>' . _('Units') . '</th>
						<th>' . _('Completed') . '</th>
					</tr>';
		} else {
			$Html .= '<table>
					<tr>
						<th>' . _('ID') . '</th>
						<th>' . _('Locations') . '</th>
						<th>' . _('Department') . '</th>
						<th>' . _('Authorization') . '</th>
						<th>' . _('Dispatch Date') . '</th>	
					</tr>';
		}

		if (isset($_POST['ShowDetails']) OR isset($StockID)) {
			$ID = '';//mark the ID change of the internal request 
		}
		$i = 0;
		//There are items without details AND with it
		while ($myrow = DB_fetch_array($result)) {
			if ($i == 0) {
				$Html .= "<tr class=\"EvenTableRows\">";
				$i = 1;
			} elseif ($i == 1) {
				$Html .= "<tr class=\"OddTableRows\">";
				$i = 0;
			}
			if ($myrow['authorised'] == 0) {
				$Auth = _('No');
			} else {
				$Auth = _('Yes');
			}
			if ($myrow['despatchdate'] == '0000-00-00') {
				$Disp = _('Not yet');
			} else {
				$Disp = ConvertSQLDate($myrow['despatchdate']);
			}
			if (isset($ID)) {
				if ($myrow['completed'] == 0) { 
					$Comp = _('No');
				} else {
					$Comp = _('Yes');
				}
			}
			if (isset($ID) AND ($ID != $myrow['dispatchid'])) {
				$ID = $myrow['dispatchid'];
				$Html .= '<td>' . $myrow['dispatchid'] . '</td>
						<td>' . $myrow['locationname'] . '</td>
						<td>' . $myrow['description'] . '</td>
						<td>' . $Auth . '</td>
						<td>' . $Disp . '</td>
						<td>' . $myrow['stockid'] . '</td>
						<td>' . $myrow['stkdescription'] . '</td>
						<td>' . locale_number_format($myrow['quantity'],$myrow['decimalplaces']) . '</td>
						<td>' . $myrow['uom'] . '</td>
						<td>' . $Comp . '</td>';

			} elseif (isset($ID) AND ($ID == $myrow['dispatchid'])) {
				$Html .= '<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td>' . $myrow['stockid'] . '</td>
						<td>' . $myrow['stkdescription'] . '</td>
						<td>' . locale_number_format($myrow['quantity'],$myrow['decimalplaces']) . '</td>
						<td>' . $myrow['uom'] . '</td>
						<td>' . $Comp . '</td>';
			} elseif(!isset($ID)) {
					$Html .= '<td>' . $myrow['dispatchid'] . '</td>
						<td>' . $myrow['locationname'] . '</td>
						<td>' . $myrow['description'] . '</td>
						<td>' . $Auth . '</td>
						<td>' . $Disp . '</td>';
			}
			$Html .= '</tr>';
		}//end of while loop;
		$Html .= '</table>';
		echo '<a href="' . $RootPath . '/InternalStockRequestInquiry.php">' . _('Select Others') . '</a>';
		
		echo $Html;
	} else {
		prnMsg(_('There are no stock request available'),'warn');
	}	







				
			

}
		
include('includes/footer.php');
exit;

function GetSearchItems ($SQLConstraint='') {
	global $db;
	if ($_POST['Keywords'] AND $_POST['StockCode']) {
		 echo _('Stock description keywords have been used in preference to the Stock code extract entered');
	}
	$SQL =  "SELECT stockmaster.stockid,
				   stockmaster.description,
				   stockmaster.decimalplaces,
				   SUM(stockrequestitems.quantity) AS qoh,
				   stockmaster.units
			FROM stockrequestitems INNER JOIN stockrequest ON stockrequestitems.dispatchid=stockrequest.dispatchid
			INNER JOIN departments ON stockrequest.departmentid = departments.departmentid

				INNER JOIN stockmaster ON stockrequestitems.stockid = stockmaster.stockid";
	if (isset($_POST['StockCat']) 
		AND ((trim($_POST['StockCat']) == '') OR $_POST['StockCat'] == 'All')){
		 $WhereStockCat = '';
	} else {
		 $WhereStockCat = " AND stockmaster.categoryid='" . $_POST['StockCat'] . "' ";
	}
	if ($_POST['Keywords']) {
		 //insert wildcard characters in spaces
		 $SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';

		 $SQL .= " WHERE stockmaster.description " . LIKE . " '" . $SearchString . "'
			  " . $WhereStockCat ;


	 } elseif (isset($_POST['StockCode'])){
		 $SQL .= " WHERE stockmaster.stockid " . LIKE . " '%" . $_POST['StockCode'] . "%'" . $WhereStockCat;

	 } elseif (!isset($_POST['StockCode']) AND !isset($_POST['Keywords'])) {
		 $SQL .= " WHERE stockmaster.categoryid='" . $_POST['StockCat'] ."'";

	 }
	$SQL .= ' AND (departments.authoriser="' . $_SESSION['UserID'] . '" OR initiator="' . $_SESSION['UserID'] . '") ';
	$SQL .= $SQLConstraint;
	$SQL .= " GROUP BY stockmaster.stockid,
					    stockmaster.description,
					    stockmaster.decimalplaces,
					    stockmaster.units
					    ORDER BY stockmaster.stockid";
	$ErrMsg =  _('No stock items were returned by the SQL because');
	$DbgMsg = _('The SQL used to retrieve the searched parts was');
	$StockItemsResult = DB_query($SQL,$ErrMsg,$DbgMsg);
	return $StockItemsResult;

	}
?>

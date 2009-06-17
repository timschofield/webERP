<?php

$PageSecurity = 11;

include('includes/session.inc');
$title = _('Import Stock Items');
include('includes/header.inc');
include('xmlrpc/lib/xmlrpc.inc');
include('api/api_errorcodes.php');

$weberpuser = $_SESSION['UserID'];
$sql='SELECT password FROM www_users WHERE userid="'.$weberpuser.'"';
$result=DB_query($sql, $db);
$myrow=DB_fetch_array($result);
$weberppassword = $myrow[0];

$ServerURL = "http://". $_SERVER['HTTP_HOST'].$rootpath."/api/api_xml-rpc.php";
$DebugLevel = 0; //Set to 0,1, or 2 with 2 being the highest level of debug info


if (isset($_POST['update'])) {
	$fp = fopen($_FILES['ImportFile']['tmp_name'], "r");
   	$buffer = fgets($fp, 4096);
   	$FieldNames = explode(',', $buffer);
   	$SuccessStyle='style="color:green; font-weight:bold"';
   	$FailureStyle='style="color:red; font-weight:bold"';
   	echo '<table><tr><th>'. _('Part Code') .'</th><th>'. _('Result') . '</th><th>'. _('Comments') .'</th></tr>';
   	$successes=0;
   	$failures=0;
 	while (!feof ($fp)) {
    	$buffer = fgets($fp, 4096);
    	$FieldValues = explode(',', $buffer);
    	if ($FieldValues[0]!='') {
    		for ($i=0; $i<sizeof($FieldValues); $i++) {
    			$ItemDetails[$FieldNames[$i]]=$FieldValues[$i];
    		}
			$stockitem = php_xmlrpc_encode($ItemDetails);
			$user = new xmlrpcval($weberpuser);
			$password = new xmlrpcval($weberppassword);

			$msg = new xmlrpcmsg("weberp.xmlrpc_InsertStockItem", array($stockitem, $user, $password));

			$client = new xmlrpc_client($ServerURL);
			$client->setDebug($DebugLevel);

			$response = $client->send($msg);
			$answer = php_xmlrpc_decode($response->value());
			if ($answer[0]==0) {
				echo '<tr '.$SuccessStyle.'><td>'.$ItemDetails['stockid'].'</td><td>'.'Success'.'</td></tr>';
				$successes++;			
			} else {
				echo '<tr '.$FailureStyle.'><td>'.$ItemDetails['stockid'].'</td><td>'.'Failure'.'</td><td>';
				for ($i=0; $i<sizeof($answer); $i++) {
					echo 'Error no '.$answer[$i].' - '.$ErrorDescription[$answer[$i]].'<br>';
				}
				echo '</td></tr>';
				$failures++;				
			}
    	}
		unset($ItemDetails);
	}
	echo '<tr><td>'.$successes._(' records successfully imported') .'</td></tr>';
	echo '<tr><td>'.$failures._(' records failed to import') .'</td></tr>';
	echo '</table>';
	fclose ($fp);
} else {
	$sql = 'select * from locations';
	$result = DB_query($sql,$db);
	if (DB_num_rows($result)==0) {
		prnMsg( _('No locations have been set up. At least one location should be set up first'), "error");
	} else {
		prnMsg( _('Select a csv file containing the details of the parts that you wish to import into webERP. '). '<br>' .
			 _('The first line must contain the field names that you wish to import. ').
			 '<a href ="Z_DescribeTable.php?table=stockmaster">' . _('The field names can be found here'). '</a>', 'info');
		echo '<form name="ItemForm" enctype="multipart/form-data" method="post" action="' . $_SERVER['PHP_SELF'] . '?' .SID .'">';
		echo '<table><tr><td>'._('File to import').'</td>'.
			'<td><input type="file" id="ImportFile" name="ImportFile"></td></tr></table>';
		echo '<div class="centre"><input type="submit" name="update" value="Process"></div>';
		echo '</form>';
	}
}

include('includes/footer.inc');

?>
<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Import Chart of Accounts');
$ViewTopic = 'SpecialUtilities';
$BookMark = basename(__FILE__, '.php');
include('includes/header.php');

include('api/api_errorcodes.php');
include('api/api_glsections.php');

//$webERPUser = $_SESSION['UserID'];
//$SQL="SELECT password FROM www_users WHERE userid='" . $webERPUser ."'";
//$Result = DB_query($SQL);
//$MyRow=DB_fetch_array($Result);
//$weberppassword = $MyRow[0];

//$ServerURL = '//'. $_SERVER['HTTP_HOST'] . $RootPath . '/api/api_xml-rpc.php';
//$DebugLevel = 0; //Set to 0,1, or 2 with 2 being the highest level of debug info

if (isset($_POST['update'])) {
	$fp = fopen($_FILES['ImportFile']['tmp_name'], "r");
   	$buffer = fgets($fp, 4096);
   	$FieldNames = explode(',', $buffer);
   	$SuccessStyle='style="color:green; font-weight:bold"';
   	$FailureStyle='style="color:red; font-weight:bold"';
   	echo '<table>
			<tr><th>' .  __('Account Section')  . '</th>
				<th>' .  __('Result') . '</th>
				<th>' .  __('Comments')  . '</th>
			</tr>';
   	$successes=0;
   	$failures=0;
	//$user = new xmlrpcval($webERPUser);
	//$password = new xmlrpcval($weberppassword);
 	while (!feof ($fp)) {
    	$buffer = fgets($fp, 4096);
    	$FieldValues = explode(',', $buffer);
    	if ($FieldValues[0]!='') {
    		for ($i=0; $i<sizeof($FieldValues); $i++) {
    			$AccountSectionDetails[$FieldNames[$i]]=$FieldValues[$i];
    		}

			//$accountsection = php_xmlrpc_encode($AccountSectionDetails);
			//$Msg = new xmlrpcmsg("weberp.xmlrpc_InsertGLAccountSection", array($accountsection, $user, $password));
			//$client = new xmlrpc_client($ServerURL);
			//$client->setDebug($DebugLevel);
			//$response = $client->send($Msg);
			//$Answer = php_xmlrpc_decode($response->value());
			$Answer = InsertGLAccountSection($AccountSectionDetails, '', '');

			if ($Answer[0]==0) {
				echo '<tr '.$SuccessStyle.'><td>' . $AccountSectionDetails['sectionname'] . '</td><td>' . 'Success' . '</td></tr>';
				$successes++;
			} else {
				echo '<tr '.$FailureStyle.'><td>' . $AccountSectionDetails['sectionname'] . '</td><td>' . 'Failure' . '</td><td>';
				for ($i=0; $i<sizeof($Answer); $i++) {
					echo 'Error no '.$Answer[$i].' - '.$ErrorDescription[$Answer[$i]] . '<br />';
				}
				echo '</td></tr>';
				$failures++;
			}
    	}
		unset($AccountDetails);
	}
	echo '<tr><td>' . $successes.__(' records successfully imported')  . '</td></tr>';
	echo '<tr><td>' . $failures.__(' records failed to import')  . '</td></tr>';
	echo '</table>';
	fclose ($fp);
} else {
	prnMsg( __('Select a csv file containing the details of the account sections that you wish to import into webERP. '). '<br />' .
		 __('The first line must contain the field names that you wish to import. ').
		 '<a href="' . $RootPath . '/Z_DescribeTable.php?table=accountsection">' . __('The field names can be found here'). '</a>', 'info');
	echo '<form id="ItemForm" enctype="multipart/form-data" method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
    echo '<div class="centre">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table><tr><td>' . __('File to import') . '</td>' .
		'<td><input type="file" id="ImportFile" name="ImportFile" /></td></tr></table>';
	echo '<div class= "centre"><input type="submit" name="update" value="Process" /></div>';
	echo '</div>
          </form>';
}
include('includes/footer.php');

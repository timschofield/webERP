<?php

/**
 * Definition of all the php functions exposed as xml-rpc methods. Those are just wrappers around functions
 * defined in the various api_*.php files.
 * NB: this file by itself does not do anything. It is supposed to be included
 */

/* Note api_php.php includes api_session.php and api_*.php */
include('api_php.php');

PhpXmlRpc\PhpXmlRpc::$xmlrpc_internalencoding = 'UTF-8';

use PhpXmlRpc\Value;
use PhpXmlRpc\Encoder;
use PhpXmlRpc\Response;

/**
 * Empty handler function for unwanted output.
 * Must be a better way of doing this, but at least it works
 */
function ob_file_callback($buffer)
{
}

/**
 *  Generate the HTMLised description string for each API.
 */
function apiBuildDocHTML($description, $parameters, $Return)
{
	$doc = '<tr><td><b><u>' . __('Description') . '</u></b></td><td colspan=2>' . $description . '</td></tr>
			<tr><td valign="top"><b><u>' . __('Parameters') . '</u></b></td>';
	for ($ii = 0; $ii < sizeof($parameters); $ii++) {
		$doc .= '<tr><td valign="top">' . $parameters[$ii]['name'] . '</td><td>' .
			$parameters[$ii]['description'] . '</td></tr>';
	}
	$doc .= '<tr><td valign="top"><b><u>' . __('Return Value');
	$doc .= '<td valign="top">' . $Return . '</td></tr>';
	$doc .= '</table>';

	return $doc;
}

$Description = __('This function is used to login into the API methods for the specified the database.')
	. '<p>' . __('NOTE: using this function means that the User Name and Password fields in the following functions are not required.  When calling those functions, leave the last two parameters off, and send along a session cookie.') . '</p>';
$Parameter[0]['name'] = __('Database Name');
$Parameter[0]['description'] = __('The name of the database to use for the requests to come. ');
$Parameter[1]['name'] = __('User name');
$Parameter[1]['description'] = __('A valid weberp username. This user should have security access to this data.');
$Parameter[2]['name'] = __('User password');
$Parameter[2]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('This function returns an integer. ') .
	__('Zero means the function was successful. ') .
	__('Otherwise an error code is returned. ') .
	__('When the login is successful, a session cookie is also returned in the HTTP headers');

$Login_sig = array(array(Value::$xmlrpcInt, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString));
$Login_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

/**
 * @todo it would make sense to not have callers know the database name, but rather the company name, and have our
 *       code retrieve the database name from the company name sent by the caller
 */
function xmlrpc_Login($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	$rtn = new Response($encoder->encode(
		LoginAPI(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval()
		)));
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('This function is used to logout from the API methods. ')
	. __('It terminates the user\'s session thus freeing the server resources.');
$Parameter = array();
$ReturnValue = __('This function returns an integer. ')
	. __('Zero means the function was successful. ')
	. __('Otherwise an error code is returned. ');
$Logout_sig = array(array(Value::$xmlrpcInt));
$Logout_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_Logout($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	$rtn = new Response($encoder->encode(LogoutAPI()));
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('This function is used to insert a new customer into the webERP database.');
$Parameter[0]['name'] = __('Customer Details');
$Parameter[0]['description'] = __('A set of key/value pairs where the key must be identical to the name of the field to be updated. ')
	. __('The field names can be found ') . '<a href="../../Z_DescribeTable.php?table=debtorsmaster">' . __('here ') . '</a>'
	. __('and are case sensitive. ') . __('The values should be of the correct type, and the api will check them before updating the database. ')
	. __('It is not necessary to include all the fields in this parameter, the database default value will be used if the field is not given.')
	. '<p>' . __('If the Create Debtor Codes Automatically flag is set, then anything sent in the debtorno field will be ignored, and the debtorno field will be set automatically.');
$Parameter[1]['name'] = __('User name');
$Parameter[1]['description'] = __('A valid weberp username. This user should have security access  to this data.');
$Parameter[2]['name'] = __('User password');
$Parameter[2]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('This function returns an array of integers. ')
	. __('If the first element is zero then the function was successful. ')
	. __('Otherwise an array of error codes is returned and no insertion takes place. ');

$InsertCustomer_sig = array(
	array(Value::$xmlrpcArray, Value::$xmlrpcStruct),
	array(Value::$xmlrpcArray, Value::$xmlrpcStruct, Value::$xmlrpcString, Value::$xmlrpcString));
$InsertCustomer_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_InsertCustomer($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 3) {
		$rtn = new Response($encoder->encode(InsertCustomer(
			$encoder->decode($request->getParam(0)),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval()
		)));
	} else {
		$rtn = new Response($encoder->encode(InsertCustomer($encoder->decode($request->getParam(0)), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('This function is used to insert a new customer branch into the webERP database.');
$Parameter[0]['name'] = __('Branch Details');
$Parameter[0]['description'] = __('A set of key/value pairs where the key must be identical to the name of the field to be updated. ')
	. __('The field names can be found ') . '<a href="../../Z_DescribeTable.php?table=custbranch">' . __('here ') . '</a>'
	. __('and are case sensitive. ') . __('The values should be of the correct type, and the api will check them before updating the database. ')
	. __('It is not necessary to include all the fields in this parameter, the database default value will be used if the field is not given.');
$Parameter[1]['name'] = __('User name');
$Parameter[1]['description'] = __('A valid weberp username. This user should have security access  to this data.');
$Parameter[2]['name'] = __('User password');
$Parameter[2]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('This function returns an array of integers. ')
	. __('If the first element is zero then the function was successful. ')
	. __('Otherwise an array of error codes is returned and no insertion takes place. ');

$InsertBranch_sig = array(
	array(Value::$xmlrpcArray, Value::$xmlrpcStruct),
	array(Value::$xmlrpcArray, Value::$xmlrpcStruct, Value::$xmlrpcString, Value::$xmlrpcString));
$InsertBranch_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_InsertBranch($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 3) {
		$rtn = new Response($encoder->encode(InsertBranch(
			$encoder->decode($request->getParam(0)),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(InsertBranch($encoder->decode($request->getParam(0)), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('This function is used to modify a customer which is already setup in the webERP database.');
$Parameter[0]['name'] = __('Customer Details');
$Parameter[0]['description'] = __('A set of key/value pairs where the key must be identical to the name of the field to be updated. ')
	. __('The field names can be found ') . '<a href="../../Z_DescribeTable.php?table=debtorsmaster">' . __('here ') . '</a>'
	. __('and are case sensitive. ') . __('The values should be of the correct type, and the api will check them before updating the database. ')
	. __('It is not necessary to include all the fields in this parameter, the database default value will be used if the field is not given.')
	. '<p>' . __('The debtorno must already exist in the weberp database.');
$Parameter[1]['name'] = __('User name');
$Parameter[1]['description'] = __('A valid weberp username. This user should have security access  to this data.');
$Parameter[2]['name'] = __('User password');
$Parameter[2]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('This function returns an array of integers. ')
	. __('If the first element is zero then the function was successful. ')
	. __('Otherwise an array of error codes is returned and no modification takes place. ');

$ModifyCustomer_sig = array(
	array(Value::$xmlrpcArray, Value::$xmlrpcStruct),
	array(Value::$xmlrpcArray, Value::$xmlrpcStruct, Value::$xmlrpcString, Value::$xmlrpcString));
$ModifyCustomer_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_ModifyCustomer($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 3) {
		$rtn = new Response($encoder->encode(ModifyCustomer(
			$encoder->decode($request->getParam(0)),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(ModifyCustomer($encoder->decode($request->getParam(0)), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('This function is used to modify a customer branch which is already setup in the webERP database.');
$Parameter[0]['name'] = __('Branch Details');
$Parameter[0]['description'] = __('A set of key/value pairs where the key must be identical to the name of the field to be updated. ')
	. __('The field names can be found ') . '<a href="../../Z_DescribeTable.php?table=custbranch">' . __('here ') . '</a>'
	. __('and are case sensitive. ') . __('The values should be of the correct type, and the api will check them before updating the database. ')
	. __('It is not necessary to include all the fields in this parameter, the database default value will be used if the field is not given.')
	. '<p>' . __('The branchcode/debtorno combination must already exist in the weberp database.');
$Parameter[1]['name'] = __('User name');
$Parameter[1]['description'] = __('A valid weberp username. This user should have security access  to this data.');
$Parameter[2]['name'] = __('User password');
$Parameter[2]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('This function returns an array of integers. ')
	. __('If the first element is zero then the function was successful. ')
	. __('Otherwise an array of error codes is returned and no insertion takes place. ');

$ModifyBranch_sig = array(
	array(Value::$xmlrpcArray, Value::$xmlrpcStruct),
	array(Value::$xmlrpcArray, Value::$xmlrpcStruct, Value::$xmlrpcString, Value::$xmlrpcString));
$ModifyBranch_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_ModifyBranch($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 3) {
		$rtn = new Response($encoder->encode(ModifyBranch(
			$encoder->decode($request->getParam(0)),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(ModifyBranch($encoder->decode($request->getParam(0)), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('This function is used to retrieve a list of the branch codes for the Debtor Number supplied.');
$Parameter[0]['name'] = __('Debtor number');
$Parameter[0]['description'] = __('This is a string value. It must be a valid debtor number that is already in the webERP database.');
$Parameter[1]['name'] = __('User name');
$Parameter[1]['description'] = __('A valid weberp username. This user should have security access  to this data.');
$Parameter[2]['name'] = __('User password');
$Parameter[2]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('If successful this function returns an array of branch codes, which may be strings or integers. ')
	. __('If the first element is zero then the function was successful.') . '<p>'
	. __('Otherwise an array of error codes is returned. ');

$GetCustomerBranchCodes_sig = array(
	array(Value::$xmlrpcArray, Value::$xmlrpcString),
	array(Value::$xmlrpcArray, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString));
$GetCustomerBranchCodes_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_GetCustomerBranchCodes($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 3) {
		$rtn = new Response($encoder->encode(GetCustomerBranchCodes(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(GetCustomerBranchCodes($request->getParam(0)->scalarval(), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('This function is used to retrieve the details of a customer branch from the webERP database.');
$Parameter[0]['name'] = __('Debtor number');
$Parameter[0]['description'] = __('This is a string value. It must be a valid debtor number that is already in the webERP database.');
$Parameter[1]['name'] = __('Branch Code');
$Parameter[1]['description'] = __('This is a string value. It must be a valid branch code that is already in the webERP database, and associated with the debtorno in Parameter[0]');
$Parameter[2]['name'] = __('User name');
$Parameter[2]['description'] = __('A valid weberp username. This user should have security access  to this data.');
$Parameter[3]['name'] = __('User password');
$Parameter[3]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('If successful this function returns a set of key/value pairs containing the details of this branch. ')
	. __('The key will be identical with field name from the ')
	. '<a href="../../Z_DescribeTable.php?table=custbranch">' . __('custbranch table. ') . '</a>'
	. __('All fields will be in the set regardless of whether the value was set.') . '<p>'
	. __('Otherwise an array of error codes is returned. ');

$GetCustomerBranch_sig = array(
	array(Value::$xmlrpcStruct, Value::$xmlrpcString, Value::$xmlrpcString),
	array(Value::$xmlrpcStruct, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString));
$GetCustomerBranch_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_GetCustomerBranch($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 4) {
		$rtn = new Response($encoder->encode(GetCustomerBranch(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval(),
			$request->getParam(3)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(GetCustomerBranch($request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval(), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('This function is used to retrieve the details of a customer from the webERP database.');
$Parameter[0]['name'] = __('Debtor number');
$Parameter[0]['description'] = __('This is a string value. It must be a valid debtor number that is already in the webERP database.');
$Parameter[1]['name'] = __('User name');
$Parameter[1]['description'] = __('A valid weberp username. This user should have security access  to this data.');
$Parameter[2]['name'] = __('User password');
$Parameter[2]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('If successful this function returns a set of key/value pairs containing the details of this customer. ')
	. __('The key will be identical with field name from the debtorsmaster table. All fields will be in the set regardless of whether the value was set.') . '<p>'
	. __('Otherwise an array of error codes is returned. ');

$GetCustomer_sig = array(
	array(Value::$xmlrpcStruct, Value::$xmlrpcString),
	array(Value::$xmlrpcStruct, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString));
$GetCustomer_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_GetCustomer($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 3) {
		$rtn = new Response($encoder->encode(GetCustomer(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(GetCustomer($request->getParam(0)->scalarval(), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('This function is used to retrieve the details of a customer from the webERP database.');
$Parameter[0]['name'] = __('Field Name');
$Parameter[0]['description'] = __('The name of a database field to search on. ')
	. __('The field names can be found ') . '<a href="../../Z_DescribeTable.php?table=debtorsmaster">' . __('here ') . '</a>'
	. __('and are case sensitive. ');
$Parameter[1]['name'] = __('Search Criteria');
$Parameter[1]['description'] = __('A (partial) string to match in the above Field Name.');
$Parameter[2]['name'] = __('User name');
$Parameter[2]['description'] = __('A valid weberp username. This user should have security access  to this data.');
$Parameter[3]['name'] = __('User password');
$Parameter[3]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('This function returns an array of customer IDs, which may be integers or strings. ')
	. __('If the first element is zero then the function was successful. ')
	. __('Otherwise an array of error codes is returned and no insertion takes place. ');

$SearchCustomers_sig = array(
	array(Value::$xmlrpcArray, Value::$xmlrpcString, Value::$xmlrpcString),
	array(Value::$xmlrpcArray, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString));
$SearchCustomers_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_SearchCustomers($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 4) {
		$rtn = new Response($encoder->encode(SearchCustomers(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval(),
			$request->getParam(3)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(SearchCustomers($request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval(), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('This function returns a list of currency abbreviations.');
$Parameter[0]['name'] = __('User name');
$Parameter[0]['description'] = __('A valid weberp username. This user should have security access  to this data.');
$Parameter[1]['name'] = __('User password');
$Parameter[1]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('This function returns an array of currency abbreviations. ')
	. __('If the first element is zero then the function was successful. ')
	. __('Otherwise an array of error codes is returned and no insertion takes place. ');

$GetCurrencyList_sig = array(
	array(Value::$xmlrpcArray),
	array(Value::$xmlrpcArray, Value::$xmlrpcString, Value::$xmlrpcString));
$GetCurrencyList_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_GetCurrencyList($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 2) {
		$rtn = new Response($encoder->encode(GetCurrencyList(
			$request->getParam(0)->scalarval(), $request->getParam(1)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(GetCurrencyList('', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('This function takes a currency abbreviation and returns details of that currency.');
$Parameter[0]['name'] = __('Currency abbreviation');
$Parameter[0]['description'] = __('A currency abbreviation as returned by the GetCurrencyList function.');
$Parameter[1]['name'] = __('User name');
$Parameter[1]['description'] = __('A valid weberp username. This user should have security access  to this data.');
$Parameter[2]['name'] = __('User password');
$Parameter[2]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('This function returns an array of currency details.');

$GetCurrencyDetails_sig = array(
	array(Value::$xmlrpcArray, Value::$xmlrpcString),
	array(Value::$xmlrpcArray, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString));
$GetCurrencyDetails_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_GetCurrencyDetails($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 3) {
		$rtn = new Response($encoder->encode(GetCurrencyDetails(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(GetCurrencyDetails($request->getParam(0)->scalarval(), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('This function returns a list of sales type abbreviations.');
$Parameter[0]['name'] = __('User name');
$Parameter[0]['description'] = __('A valid weberp username. This user should have security access  to this data.');
$Parameter[1]['name'] = __('User password');
$Parameter[1]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('This function returns an array of sales type abbreviations. ')
	. __('If the first element is zero then the function was successful. ')
	. __('Otherwise an array of error codes is returned and no insertion takes place. ');

$GetSalesTypeList_sig = array(
	array(Value::$xmlrpcArray),
	array(Value::$xmlrpcArray, Value::$xmlrpcString, Value::$xmlrpcString));
$GetSalesTypeList_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_GetSalesTypeList($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 2) {
		$rtn = new Response($encoder->encode(GetSalesTypeList(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(GetSalesTypeList('', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('This function takes a sales type abbreviation and returns details of that sales type.');
$Parameter[0]['name'] = __('Sales type abbreviation');
$Parameter[0]['description'] = __('A sales type abbreviation as returned by the GetSalesTypeList function.');
$Parameter[1]['name'] = __('User name');
$Parameter[1]['description'] = __('A valid weberp username. This user should have security access  to this data.');
$Parameter[2]['name'] = __('User password');
$Parameter[2]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('This function returns an array of sales type details.');

$GetSalesTypeDetails_sig = array(
	array(Value::$xmlrpcArray, Value::$xmlrpcString),
	array(Value::$xmlrpcArray, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString));
$GetSalesTypeDetails_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_GetSalesTypeDetails($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 3) {
		$rtn = new Response($encoder->encode(GetSalesTypeDetails(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(GetSalesTypeDetails($request->getParam(0)->scalarval(), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('This function is used to insert sales type details into the webERP database.');
$Parameter[0]['name'] = __('Sales Type Details');
$Parameter[0]['description'] = __('A set of key/value pairs where the key must be identical to the name of the field to be updated. ')
	. __('The field names can be found ') . '<a href="../../Z_DescribeTable.php?table=salestypes">' . __('here ') . '</a>'
	. __('and are case sensitive. ') . __('The values should be of the correct type, and the api will check them before updating the database. ')
	. __('It is not necessary to include all the fields in this parameter, the database default value will be used if the field is not given.');
$Parameter[1]['name'] = __('User name');
$Parameter[1]['description'] = __('A valid weberp username. This user should have security access  to this data.');
$Parameter[2]['name'] = __('User password');
$Parameter[2]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('This function returns an array of integers. ')
	. __('If the first element is zero then the function was successful. ')
	. __('Otherwise an array of error codes is returned and no insertion takes place. ');

$InsertSalesType_sig = array(
	array(Value::$xmlrpcArray, Value::$xmlrpcStruct),
	array(Value::$xmlrpcArray, Value::$xmlrpcStruct, Value::$xmlrpcString, Value::$xmlrpcString));
$InsertSalesType_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_InsertSalesType($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 3) {
		$rtn = new Response($encoder->encode(InsertSalesType(
			$encoder->decode($request->getParam(0)),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(InsertSalesType($encoder->decode($request->getParam(0)), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('This function returns a list of hold reason codes.');
$Parameter[0]['name'] = __('User name');
$Parameter[0]['description'] = __('A valid weberp username. This user should have security access  to this data.');
$Parameter[1]['name'] = __('User password');
$Parameter[1]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('This function returns an array of hold reason codes.');

$GetHoldReasonList_sig = array(
	array(Value::$xmlrpcArray),
	array(Value::$xmlrpcArray, Value::$xmlrpcString, Value::$xmlrpcString));
$GetHoldReasonList_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_GetHoldReasonList($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 2) {
		$rtn = new Response($encoder->encode(GetHoldReasonList(
			$request->getParam(0)->scalarval(), $request->getParam(1)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(GetHoldReasonList('', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('This function takes a hold reason code and returns details of that hold reason.');
$Parameter[0]['name'] = __('Hold reason code');
$Parameter[0]['description'] = __('A hold reason abbreviation as returned by the GetHoldReasonList function.');
$Parameter[1]['name'] = __('User name');
$Parameter[1]['description'] = __('A valid weberp username. This user should have security access  to this data.');
$Parameter[2]['name'] = __('User password');
$Parameter[2]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('This function returns an array of hold reason details.');

$GetHoldReasonDetails_sig = array(
	array(Value::$xmlrpcArray, Value::$xmlrpcString),
	array(Value::$xmlrpcArray, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString));
$GetHoldReasonDetails_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_GetHoldReasonDetails($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 3) {
		$rtn = new Response($encoder->encode(GetHoldReasonDetails(
			$request->getParam(0)->scalarval(), $request->getParam(1)->scalarval(), $request->getParam(2)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(GetHoldReasonDetails($request->getParam(0)->scalarval(), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('This function returns a list of payment terms abbreviations.');
$Parameter[0]['name'] = __('User name');
$Parameter[0]['description'] = __('A valid weberp username. This user should have security access  to this data.');
$Parameter[1]['name'] = __('User password');
$Parameter[1]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('This function returns an array of payment terms abbreviations.');

$GetPaymentTermsList_sig = array(
	array(Value::$xmlrpcArray),
	array(Value::$xmlrpcArray, Value::$xmlrpcString, Value::$xmlrpcString));
$GetPaymentTermsList_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_GetPaymentTermsList($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 2) {
		$rtn = new Response($encoder->encode(GetPaymentTermsList(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(GetPaymentTermsList('', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('This function takes a payment terms abbreviation and returns details of that payment terms type.');
$Parameter[0]['name'] = __('Hold reason code');
$Parameter[0]['description'] = __('A payment terms abbreviation as returned by the GetPaymentTermsList function.');
$Parameter[1]['name'] = __('User name');
$Parameter[1]['description'] = __('A valid weberp username. This user should have security access to this data.');
$Parameter[2]['name'] = __('User password');
$Parameter[2]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('This function returns an array of payment terms details.');

$GetPaymentTermsDetails_sig = array(
	array(Value::$xmlrpcArray, Value::$xmlrpcString),
	array(Value::$xmlrpcArray, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString));
$GetPaymentTermsDetails_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_GetPaymentTermsDetails($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 3) {
		$rtn = new Response($encoder->encode(GetPaymentTermsDetails(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(GetPaymentTermsDetails($request->getParam(0)->scalarval(), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('This function returns a list of payment method codes.');
$Parameter[0]['name'] = __('User name');
$Parameter[0]['description'] = __('A valid weberp username. This user should have security access  to this data.');
$Parameter[1]['name'] = __('User password');
$Parameter[1]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('This function returns an array of payment method codes.');

$GetPaymentMethodsList_sig = array(
	array(Value::$xmlrpcArray),
	array(Value::$xmlrpcArray, Value::$xmlrpcString, Value::$xmlrpcString));
$GetPaymentMethodsList_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_GetPaymentMethodsList($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 2) {
		$rtn = new Response($encoder->encode(GetPaymentMethodsList(
			$request->getParam(0)->scalarval(), $request->getParam(1)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(GetPaymentMethodsList('', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('This function takes a payment method code and returns details of that payment method.');
$Parameter[0]['name'] = __('Payment method code');
$Parameter[0]['description'] = __('A payment method code as returned by the GetPaymentMethodsList function.');
$Parameter[1]['name'] = __('User name');
$Parameter[1]['description'] = __('A valid weberp username. This user should have security access to this data.');
$Parameter[2]['name'] = __('User password');
$Parameter[2]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('This function returns an array of payment terms details.');

$GetPaymentMethodDetails_sig = array(
	array(Value::$xmlrpcArray, Value::$xmlrpcString),
	array(Value::$xmlrpcArray, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString));
$GetPaymentMethodDetails_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_GetPaymentMethodDetails($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 3) {
		$rtn = new Response($encoder->encode(GetPaymentMethodDetails(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(GetPaymentMethodDetails($request->getParam(0)->scalarval(), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('This function inserts a new stock item into webERP, including updating the locstock table.');
$Parameter[0]['name'] = __('Stock Item Details');
$Parameter[0]['description'] = __('Key/value pairs of data to insert. The key must be identical with the database field name.');
$Parameter[1]['name'] = __('User name');
$Parameter[1]['description'] = __('A valid weberp username. This user should have security access to this data.');
$Parameter[2]['name'] = __('User password');
$Parameter[2]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('This function returns an array of integers. ')
	. __('If the first element is zero then the function was successful. ')
	. __('Otherwise an array of error codes is returned and no insertion takes place. ');

$InsertStockItem_sig = array(
	array(Value::$xmlrpcArray, Value::$xmlrpcStruct),
	array(Value::$xmlrpcArray, Value::$xmlrpcStruct, Value::$xmlrpcString, Value::$xmlrpcString));
$InsertStockItem_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_InsertStockItem($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 3) {
		$rtn = new Response($encoder->encode(InsertStockItem(
			$encoder->decode($request->getParam(0)),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(InsertStockItem($encoder->decode($request->getParam(0)), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('This function modifies a stock item that already exists in webERP.');
$Parameter[0]['name'] = __('Stock Item Details');
$Parameter[0]['description'] = __('Key/value pairs of data to modify.');
$Parameter[1]['name'] = __('User name');
$Parameter[1]['description'] = __('A valid weberp username. This user should have security access to this data.');
$Parameter[2]['name'] = __('User password');
$Parameter[2]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('This function returns an array of integers. ')
	. __('If the first element is zero then the function was successful. ')
	. __('Otherwise an array of error codes is returned and no modification takes place. ');

$ModifyStockItem_sig = array(
	array(Value::$xmlrpcArray, Value::$xmlrpcStruct),
	array(Value::$xmlrpcArray, Value::$xmlrpcStruct, Value::$xmlrpcString, Value::$xmlrpcString));
$ModifyStockItem_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_ModifyStockItem($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 3) {
		$rtn = new Response($encoder->encode(ModifyStockItem(
			$encoder->decode($request->getParam(0)),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(ModifyStockItem($encoder->decode($request->getParam(0)), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('This function takes a stock item code and returns an array of key/value pairs.') .
	__('The keys represent the database field names, and the values are the value of that field.');
$Parameter[0]['name'] = __('Stock ID');
$Parameter[0]['description'] = __('The StockID code to identify the item in the database.');
$Parameter[1]['name'] = __('User name');
$Parameter[1]['description'] = __('A valid weberp username. This user should have security access to this data.');
$Parameter[2]['name'] = __('User password');
$Parameter[2]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('If successful this function returns a set of key/value pairs containing the details of this stock item. ')
	. __('The key will be identical with field name from the stockmaster table. All fields will be in the set regardless of whether the value was set.') . '<p>'
	. __('Otherwise an array of error codes is returned. ');

$GetStockItem_sig = array(
	array(Value::$xmlrpcStruct, Value::$xmlrpcString),
	array(Value::$xmlrpcStruct, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString));
$GetStockItem_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_GetStockItem($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 3) {
		$rtn = new Response($encoder->encode(GetStockItem(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(GetStockItem($request->getParam(0)->scalarval(), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('This function searches the stockmaster table and returns an array of stock items matching that criteria.');
$Parameter[0]['name'] = __('Field Name');
$Parameter[0]['description'] = __('The field name to search on.');
$Parameter[1]['name'] = __('Match Criteria');
$Parameter[1]['description'] = __('The SQL search pattern to select items in the database.');
$Parameter[2]['name'] = __('User name');
$Parameter[2]['description'] = __('A valid weberp username. This user should have security access to this data.');
$Parameter[3]['name'] = __('User password');
$Parameter[3]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('Returns an array of stock codes matching the criteria send, or an array of error codes');

$SearchStockItems_sig = array(
	array(Value::$xmlrpcArray, Value::$xmlrpcString, Value::$xmlrpcString),
	array(Value::$xmlrpcArray, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString));
$SearchStockItems_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_SearchStockItems($request)
{
	//ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 4) {
		$rtn = new Response($encoder->encode(SearchStockItems(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval(),
			$request->getParam(3)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(SearchStockItems($request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval(), '', '')));
	}
	//ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = 'This function returns the stock balance for the given stockid.';
$Parameter[0]['name'] = __('Stock ID');
$Parameter[0]['description'] = __('A string field containing a valid stockid that must already be setup in the stockmaster table. The api will check this before making the enquiry.');
$Parameter[1]['name'] = __('User name');
$Parameter[1]['description'] = __('A valid weberp username. This user should have security access  to this data.');
$Parameter[2]['name'] = __('User password');
$Parameter[2]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('This function returns an array of stock quantities by location for this stock item. ');

$GetStockBalance_sig = array(
	array(Value::$xmlrpcArray, Value::$xmlrpcString),
	array(Value::$xmlrpcArray, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString));
$GetStockBalance_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_GetStockBalance($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 3) {
		$rtn = new Response($encoder->encode(GetStockBalance(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(GetStockBalance($request->getParam(0)->scalarval(), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = 'This function returns the reorder levels by location.';
$Parameter[0]['name'] = __('Stock ID');
$Parameter[0]['description'] = __('A string field containing a valid stockid that must already be setup in the stockmaster table. The api will check this before making the enquiry.');
$Parameter[1]['name'] = __('User name');
$Parameter[1]['description'] = __('A valid weberp username. This user should have security access  to this data.');
$Parameter[2]['name'] = __('User password');
$Parameter[2]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('This function returns an array of stock reorder levels by location for this stock item.');

$GetStockReorderLevel_sig = array(
	array(Value::$xmlrpcArray, Value::$xmlrpcString),
	array(Value::$xmlrpcArray, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString));
$GetStockReorderLevel_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_GetStockReorderLevel($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 3) {
		$rtn = new Response($encoder->encode(GetStockReorderLevel(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(GetStockReorderLevel($request->getParam(0)->scalarval(), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = 'This function sets the reorder level for the given stockid in the given location.';
$Parameter[0]['name'] = __('Stock ID');
$Parameter[0]['description'] = __('A string field containing a valid stockid that must already be setup in the stockmaster table. The api will check this before making the enquiry.');
$Parameter[1]['name'] = __('Location Code');
$Parameter[1]['description'] = __('A string field containing a valid location code that must already be setup in the locations table. The api will check this before making the enquiry.');
$Parameter[2]['name'] = __('Reorder level');
$Parameter[2]['description'] = __('A numeric field containing the reorder level for this stockid/location combination.');
$Parameter[3]['name'] = __('User name');
$Parameter[3]['description'] = __('A valid weberp username. This user should have security access  to this data.');
$Parameter[4]['name'] = __('User password');
$Parameter[4]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('This function returns zero if the transaction was successful or an array of error codes if not. ');

$SetStockReorderLevel_sig = array(
	array(Value::$xmlrpcValue, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString),
	array(Value::$xmlrpcValue, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString));
$SetStockReorderLevel_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_SetStockReorderLevel($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 5) {
		$rtn = new Response($encoder->encode(SetStockReorderLevel(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval(),
			$request->getParam(3)->scalarval(),
			$request->getParam(4)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(SetStockReorderLevel($request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval(), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('This function returns the quantity allocated of the stock item id sent as a parameter.');
$Parameter[0]['name'] = __('Stock ID');
$Parameter[0]['description'] = __('The StockID code to identify items ordered but not yet shipped.');
$Parameter[1]['name'] = __('User name');
$Parameter[1]['description'] = __('A valid weberp username. This user should have security access to this data.');
$Parameter[2]['name'] = __('User password');
$Parameter[2]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('This function returns an integer value of the quantity allocated or an array of error codes if not. ');

$GetAllocatedStock_sig = array(
	array(Value::$xmlrpcValue, Value::$xmlrpcString),
	array(Value::$xmlrpcValue, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString));
$GetAllocatedStock_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_GetAllocatedStock($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 3) {
		$rtn = new Response($encoder->encode(GetAllocatedStock(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(GetAllocatedStock($request->getParam(0)->scalarval(), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('This function takes a stock ID and returns the quantity of this stock that is currently on outstanding purchase orders.');
$Parameter[0]['name'] = __('Stock ID');
$Parameter[0]['description'] = __('The StockID code to identify items in the database on order, but not yet received.');
$Parameter[1]['name'] = __('User name');
$Parameter[1]['description'] = __('A valid weberp username. This user should have security access to this data.');
$Parameter[2]['name'] = __('User password');
$Parameter[2]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('This function returns an integer value of the quantity on order or an array of error codes if not.');

$GetOrderedStock_sig = array(
	array(Value::$xmlrpcValue, Value::$xmlrpcString),
	array(Value::$xmlrpcValue, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString));
$GetOrderedStock_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_GetOrderedStock($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 3) {
		$rtn = new Response($encoder->encode(GetOrderedStock(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(GetOrderedStock($request->getParam(0)->scalarval(), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('This function sets the sales price for a stock ID in the sales type and currency passed to the function');
$Parameter[0]['name'] = __('Stock ID');
$Parameter[0]['description'] = __('The StockID code to identify the item in the database.');
$Parameter[1]['name'] = __('Currency Code');
$Parameter[1]['description'] = __('The currency involved.');
$Parameter[2]['name'] = __('Sales Type');
$Parameter[2]['description'] = __('The sales type to identify the item in the database.');
$Parameter[3]['name'] = __('Price');
$Parameter[3]['description'] = __('The price to apply to this item.');
$Parameter[4]['name'] = __('User name');
$Parameter[4]['description'] = __('A valid weberp username. This user should have security access to this data.');
$Parameter[5]['name'] = __('User password');
$Parameter[5]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('Returns a zero if successful or else an array of error codes');

$SetStockPrice_sig = array(
	array(Value::$xmlrpcValue, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString),
	array(Value::$xmlrpcValue, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString));
$SetStockPrice_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_SetStockPrice($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 6) {
		$rtn = new Response($encoder->encode(SetStockPrice(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval(),
			$request->getParam(3)->scalarval(),
			$request->getParam(4)->scalarval(),
			$request->getParam(5)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(SetStockPrice(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval(),
			$request->getParam(3)->scalarval(), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('This function gets the sales price for a stock ID in the sales type and currency passed to the function');
$Parameter[0]['name'] = __('Stock ID');
$Parameter[0]['description'] = __('The StockID code to identify the item in the database.');
$Parameter[1]['name'] = __('Currency Code');
$Parameter[1]['description'] = __('The currency involved.');
$Parameter[2]['name'] = __('Sales Type');
$Parameter[2]['description'] = __('The sales type of the item in the database.');
$Parameter[3]['name'] = __('User name');
$Parameter[3]['description'] = __('A valid weberp username. This user should have security access to this data.');
$Parameter[4]['name'] = __('User password');
$Parameter[4]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('Returns the sales price for the stock item whose ID is passed in the function');

$GetStockPrice_sig = array(
	array(Value::$xmlrpcDouble, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString),
	array(Value::$xmlrpcDouble, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString));
$GetStockPrice_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_GetStockPrice($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 5) {
		$rtn = new Response($encoder->encode(GetStockPrice(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval(),
			$request->getParam(3)->scalarval(),
			$request->getParam(4)->scalarval())));
	} else { //only 3 parameters if login already in session
		$rtn = new Response($encoder->encode(GetStockPrice($request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval(), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('Creates a customer receipt from the details passed to the method as an associative array');
$Parameter[0]['name'] = __('Receipt Details');
$Parameter[0]['description'] = __('An associative array describing the customer receipt with the following fields: debtorno - the customer code; trandate - the date of the receipt in Y-m-d format; amountfx - the amount in FX; paymentmethod - the payment method of the receipt e.g. cash/EFTPOS/credit card; bankaccount - the webERP bank account to use for the transaction, reference - the reference to record against the webERP receipt transaction');
$Parameter[1]['name'] = __('User name');
$Parameter[1]['description'] = __('A valid weberp username. This user should have security access to this data.');
$Parameter[2]['name'] = __('User password');
$Parameter[2]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('This function returns an array of integers. ')
	. __('If the first element is zero then the function was successful, and the second element is the receipt number. ')
	. __('Otherwise an array of error codes is returned and no insertion takes place. ');

$InsertDebtorReceipt_sig = array(
	array(Value::$xmlrpcArray, Value::$xmlrpcStruct),
	array(Value::$xmlrpcArray, Value::$xmlrpcStruct, Value::$xmlrpcString, Value::$xmlrpcString));
$InsertDebtorReceipt_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_InsertDebtorReceipt($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 3) {
		$rtn = new Response($encoder->encode(InsertDebtorReceipt(
			$encoder->decode($request->getParam(0)),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(InsertDebtorReceipt($encoder->decode($request->getParam(0)), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('Allocates a debtor receipt or credit to a debtor invoice. Using the customerref field to match up which invoice to allocate to');
$Parameter[0]['name'] = __('Allocation Details');
$Parameter[0]['description'] = __('An associative array describing the customer, the transaction being allocated from, it\'s transaction type 12 for a receipt or 11 for a credit note, the webERP transaction number, the customer ref that is to be searched for in invoices to match to. The fields are: debtorno, type, transno, customerref');
$Parameter[1]['name'] = __('User name');
$Parameter[1]['description'] = __('A valid weberp username. This user should have security access to this data.');
$Parameter[2]['name'] = __('User password');
$Parameter[2]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('This function returns an array of integers. ')
	. __('If the first element is zero then the function was successful.')
	. __('Otherwise an array of error codes is returned and no insertion takes place. ');

$AllocateTrans_sig = array(
	array(Value::$xmlrpcArray, Value::$xmlrpcStruct),
	array(Value::$xmlrpcArray, Value::$xmlrpcStruct, Value::$xmlrpcString, Value::$xmlrpcString));
$AllocateTrans_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_AllocateTrans($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 3) {
		$rtn = new Response($encoder->encode(AllocateTrans(
			$encoder->decode($request->getParam(0)),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(AllocateTrans($encoder->decode($request->getParam(0)), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('Creates a credit note from header details associative array and line items. This function implements most of a webERP credit note with the exception that it cannot handle serialised or lot/batch controlled items. All the necessary updates and inserts are handled for stock quantities returned, taxes, sales analysis, stock movements, sales and cost of sales journals');
$Parameter[0]['name'] = __('Credit Note Header Details');
$Parameter[0]['description'] = __('An associative array describing the credit note header with the fields debtorno, branchcode, trandate, tpe, fromstkloc, customerref, shipvia');
$Parameter[1]['name'] = __('Credit note line items');
$Parameter[1]['description'] = __('The lines of stock being returned on this credit note. Only stock returns can be dealt with using this API method. This is an array of associative arrays containing the fields, stockid, price, qty, discountpercent for the items returned');
$Parameter[2]['name'] = __('User name');
$Parameter[2]['description'] = __('A valid weberp username. This user should have security access to this data.');
$Parameter[3]['name'] = __('User password');
$Parameter[3]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('This function returns an array of integers. ')
	. __('If the first element is zero then the function was successful, and the second element is the credit note number. ')
	. __('Otherwise an array of error codes is returned and no insertion takes place. ');

$CreateCreditNote_sig = array(
	array(Value::$xmlrpcArray, Value::$xmlrpcStruct),
	array(Value::$xmlrpcArray, Value::$xmlrpcStruct, Value::$xmlrpcArray, Value::$xmlrpcString, Value::$xmlrpcString));
$CreateCreditNote_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_CreateCreditNote($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 4) {
		$rtn = new Response($encoder->encode(CreateCreditNote(
			$encoder->decode($request->getParam(0)),
			$encoder->decode($request->getParam(1)),
			$request->getParam(2)->scalarval(),
			$request->getParam(3)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(CreateCreditNote(
			$encoder->decode($request->getParam(0)), $encoder->decode($request->getParam(1)), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('Inserts a sales invoice into the debtortrans table and does the relevant GL entries. Note that this function does not do the tax entries, insert stock movements, update the stock quanties, sales analysis data or do any cost of sales gl journals. Caution is advised in using this function. To create a full webERP invoice with all tables updated use the InvoiceSalesOrder function.');
$Parameter[0]['name'] = __('Invoice Details');
$Parameter[0]['description'] = __('An array of index/value items describing the invoice.')
	. __('The field names can be found ') . '<a href="../../Z_DescribeTable.php?table=debtortrans">' . __('here ') . '</a>'
	. __('and are case sensitive. ') . __('The values should be of the correct type, and the api will check them before updating the database. ')
	. __('The transno key is generated by this call, and if a value is supplied, it will be ignored. ')
	. __('Two additional fields are required. partcode needs to be a genuine part number, though it appears to serve no real purpose. ')
	. __('salesarea also is required, though again it appears to serve no useful purpose. ')
	. __('It is not necessary to include all the fields in this parameter, the database default value will be used if the field is not given.');
$Parameter[1]['name'] = __('User name');
$Parameter[1]['description'] = __('A valid weberp username. This user should have security access to this data.');
$Parameter[2]['name'] = __('User password');
$Parameter[2]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('This function returns an array of integers. ')
	. __('If the first element is zero then the function was successful, and the second element is the invoice number. ')
	. __('Otherwise an array of error codes is returned and no insertion takes place. ');

$InsertSalesInvoice_sig = array(
	array(Value::$xmlrpcArray, Value::$xmlrpcStruct),
	array(Value::$xmlrpcArray, Value::$xmlrpcStruct, Value::$xmlrpcString, Value::$xmlrpcString));
$InsertSalesInvoice_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_InsertSalesInvoice($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 3) {
		$rtn = new Response($encoder->encode(InsertSalesInvoice(
			$encoder->decode($request->getParam(0)),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(InsertSalesInvoice($encoder->decode($request->getParam(0)), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);
$ReturnValue = __('Return Value Descriptions go here');
$Description = __('This function is used to insert a new Sales Credit to the webERP database. Note that this function does not implement a webERP credit note in full and caution is advised in using this function. It does not handle tax at all, it does not add stockmovements, it does not update stock for any quantity returned or update sales analysis. To create a credit note using webERP logic use the CreateCreditNote function');
$Parameter[0]['name'] = __('Credit Details');
$Parameter[0]['description'] = __('An array of index/value items describing the credit.  All values must be negative.');
$Parameter[1]['name'] = __('User name');
$Parameter[1]['description'] = __('A valid weberp username. This user should have security access to this data.');
$Parameter[2]['name'] = __('User password');
$Parameter[2]['description'] = __('The weberp password associated with this user name. ');

$InsertSalesCredit_sig = array(
	array(Value::$xmlrpcValue, Value::$xmlrpcStruct),
	array(Value::$xmlrpcValue, Value::$xmlrpcStruct, Value::$xmlrpcString, Value::$xmlrpcString));
$InsertSalesCredit_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_InsertSalesCredit($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 3) {
		$rtn = new Response($encoder->encode(InsertSalesCredit(
			$encoder->decode($request->getParam(0)),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(InsertSalesCredit($encoder->decode($request->getParam(0)), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = 'This function is used to start a new sales order.';
$Parameter[0]['name'] = __('Insert Sales Order Header');
$Parameter[0]['description'] = __('A set of key/value pairs where the key must be identical to the name of the field to be updated. ')
	. __('The field names can be found ') . '<a href="../../Z_DescribeTable.php?table=salesorders">' . __('here ') . '</a>'
	. __('and are case sensitive. ') . __('The values should be of the correct type, and the api will check them before updating the database. ')
	. __('The orderno key is generated by this call, and if a value is supplied, it will be ignored. ')
	. __('It is not necessary to include all the fields in this parameter, the database default value will be used if the field is not given.');
$Parameter[1]['name'] = __('User name');
$Parameter[1]['description'] = __('A valid weberp username. This user should have security access  to this data.');
$Parameter[2]['name'] = __('User password');
$Parameter[2]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('If successful this function returns a two element array; the first element is 0 for success or an error code, while the second element is the order number.');

$InsertSalesOrderHeader_sig = array(
	array(Value::$xmlrpcArray, Value::$xmlrpcStruct),
	array(Value::$xmlrpcArray, Value::$xmlrpcStruct, Value::$xmlrpcString, Value::$xmlrpcString));
$InsertSalesOrderHeader_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_InsertSalesOrderHeader($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 3) {
		$rtn = new Response($encoder->encode(InsertSalesOrderHeader(
			$encoder->decode($request->getParam(0)),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(InsertSalesOrderHeader($encoder->decode($request->getParam(0)), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = 'This function is used to invoice a sales order for the full quantity on the order assuming it is all dispatched. NB It does not deal with serialised/controlled items.';
$Parameter[0]['name'] = __('Sales Order to invoice');
$Parameter[0]['description'] = __('An integer representing the webERP sales order number');
$Parameter[1]['name'] = __('User name');
$Parameter[1]['description'] = __('A valid weberp username. This user should have security access  to this data.');
$Parameter[2]['name'] = __('User password');
$Parameter[2]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('If successful this function returns a two element array; the first element is 0 for success or an error code, while the second element is the invoice number.');

$InvoiceSalesOrder_sig = array(
	array(Value::$xmlrpcArray, Value::$xmlrpcStruct),
	array(Value::$xmlrpcArray, Value::$xmlrpcInt, Value::$xmlrpcString, Value::$xmlrpcString));
$InvoiceSalesOrder_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_InvoiceSalesOrder($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 3) {
		$rtn = new Response($encoder->encode(InvoiceSalesOrder(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval())));
	} else { //do it with the current login
		$rtn = new Response($encoder->encode(InvoiceSalesOrder($request->getParam(0)->scalarval(), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = 'This function is used to modify the header details of a sales order';
$Parameter[0]['name'] = __('Modify Sales Order Header Details');
$Parameter[0]['description'] = __('A set of key/value pairs where the key must be identical to the name of the field to be updated. ')
	. __('The field names can be found ') . '<a href="../../Z_DescribeTable.php?table=salesorders">' . __('here ') . '</a>'
	. __('and are case sensitive. ') . __('The values should be of the correct type, and the api will check them before updating the database. ')
	. __('It is not necessary to include all the fields in this parameter, the database default value will be used if the field is not given.');
$Parameter[1]['name'] = __('User name');
$Parameter[1]['description'] = __('A valid weberp username. This user should have security access  to this data.');
$Parameter[2]['name'] = __('User password');
$Parameter[2]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('If successful this function returns a single element array with the value 0; otherwise, it contains all error codes encountered during the update.');

$ModifySalesOrderHeader_sig = array(
	array(Value::$xmlrpcArray, Value::$xmlrpcStruct),
	array(Value::$xmlrpcArray, Value::$xmlrpcStruct, Value::$xmlrpcString, Value::$xmlrpcString));
$ModifySalesOrderHeader_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_ModifySalesOrderHeader($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 3) {
		$rtn = new Response($encoder->encode(ModifySalesOrderHeader(
			$encoder->decode($request->getParam(0)),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(ModifySalesOrderHeader($encoder->decode($request->getParam(0)), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = 'This function is used to add line items to a sales order.';
$Parameter[0]['name'] = __('Insert Sales Order Line');
$Parameter[0]['description'] = __('A set of key/value pairs where the key must be identical to the name of the field to be updated. ')
	. __('The field names can be found ') . '<a href="../../Z_DescribeTable.php?table=salesorderdetails">' . __('here ') . '</a>'
	. __('and are case sensitive. ') . __('The values should be of the correct type, and the api will check them before updating the database. ')
	. __('The orderno key must be one of these values. ')
	. __('It is not necessary to include all the fields in this parameter, the database default value will be used if the field is not given.');
$Parameter[1]['name'] = __('User name');
$Parameter[1]['description'] = __('A valid weberp username. This user should have security access  to this data.');
$Parameter[2]['name'] = __('User password');
$Parameter[2]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('This function returns an array; the first element is 0 for success; otherwise the array contains a list of all errors encountered.');

$InsertSalesOrderLine_sig = array(
	array(Value::$xmlrpcArray, Value::$xmlrpcStruct),
	array(Value::$xmlrpcArray, Value::$xmlrpcStruct, Value::$xmlrpcString, Value::$xmlrpcString));
$InsertSalesOrderLine_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_InsertSalesOrderLine($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 3) {
		$rtn = new Response($encoder->encode(InsertSalesOrderLine(
			$encoder->decode($request->getParam(0)),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(InsertSalesOrderLine($encoder->decode($request->getParam(0)), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = 'This function is used to modify line items on a sales order.';
$Parameter[0]['name'] = __('Modify Sales Order Line');
$Parameter[0]['description'] = __('A set of key/value pairs where the key must be identical to the name of the field to be updated. ')
	. __('The field names can be found ') . '<a href="../../Z_DescribeTable.php?table=salesorderdetails">' . __('here ') . '</a>'
	. __('and are case sensitive. ') . __('The values should be of the correct type, and the api will check them before updating the database. ')
	. __('The orderno and stkcode keys must be one of these values. ')
	. __('It is not necessary to include all the fields in this parameter, the database default value will be used if the field is not given.');
$Parameter[1]['name'] = __('User name');
$Parameter[1]['description'] = __('A valid weberp username. This user should have security access  to this data.');
$Parameter[2]['name'] = __('User password');
$Parameter[2]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('This function returns an array; the first element is 0 for success; otherwise the array contains a list of all errors encountered.');

$ModifySalesOrderLine_sig = array(
	array(Value::$xmlrpcArray, Value::$xmlrpcStruct),
	array(Value::$xmlrpcArray, Value::$xmlrpcStruct, Value::$xmlrpcString, Value::$xmlrpcString));
$ModifySalesOrderLine_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_ModifySalesOrderLine($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 3) {
		$rtn = new Response($encoder->encode(ModifySalesOrderLine(
			$encoder->decode($request->getParam(0)),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(ModifySalesOrderLine($encoder->decode($request->getParam(0)), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);
$ReturnValue = __('Return Value Descriptions go here');
$Description = __('Function Description go here');
$Parameter[0]['name'] = __('Account Details');
$Parameter[0]['description'] = __('An array of index/value items describing the GL Account and fields to set.');
$Parameter[1]['name'] = __('User name');
$Parameter[1]['description'] = __('A valid weberp username. This user should have security access to this data.');
$Parameter[2]['name'] = __('User password');
$Parameter[2]['description'] = __('The weberp password associated with this user name. ');

$InsertGLAccount_sig = array(
	array(Value::$xmlrpcValue, Value::$xmlrpcStruct),
	array(Value::$xmlrpcValue, Value::$xmlrpcStruct, Value::$xmlrpcString, Value::$xmlrpcString));
$InsertGLAccount_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_InsertGLAccount($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 3) {
		$rtn = new Response($encoder->encode(InsertGLAccount(
			$encoder->decode($request->getParam(0)),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(InsertGLAccount($encoder->decode($request->getParam(0)), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$ReturnValue = __('Return Value Descriptions go here');
$Description = __('Function Description go here');

$Parameter[0]['name'] = __('Account Section Details');
$Parameter[0]['description'] = __('An array of index/value items describing the account section to insert.');
$Parameter[1]['name'] = __('User name');
$Parameter[1]['description'] = __('A valid weberp username. This user should have security access to this data.');
$Parameter[2]['name'] = __('User password');
$Parameter[2]['description'] = __('The weberp password associated with this user name. ');

$InsertGLAccountSection_sig = array(
	array(Value::$xmlrpcValue, Value::$xmlrpcStruct),
	array(Value::$xmlrpcValue, Value::$xmlrpcStruct, Value::$xmlrpcString, Value::$xmlrpcString));
$InsertGLAccountSection_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_InsertGLAccountSection($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 3) {
		$rtn = new Response($encoder->encode(InsertGLAccountSection(
			$encoder->decode($request->getParam(0)),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(InsertGLAccountSection($encoder->decode($request->getParam(0)), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$ReturnValue = __('Return Value Descriptions go here');
$Description = __('Function Description go here');

$Parameter[0]['name'] = __('Account Group Details');
$Parameter[0]['description'] = __('An array of index/value items describing the account group to insert.');
$Parameter[1]['name'] = __('User name');
$Parameter[1]['description'] = __('A valid weberp username. This user should have security access to this data.');
$Parameter[2]['name'] = __('User password');
$Parameter[2]['description'] = __('The weberp password associated with this user name. ');

$InsertGLAccountGroup_sig = array(
	array(Value::$xmlrpcValue, Value::$xmlrpcStruct),
	array(Value::$xmlrpcValue, Value::$xmlrpcStruct, Value::$xmlrpcString, Value::$xmlrpcString));
$InsertGLAccountGroup_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_InsertGLAccountGroup($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 3) {
		$rtn = new Response($encoder->encode(InsertGLAccountGroup(
			$encoder->decode($request->getParam(0)),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(InsertGLAccountGroup($encoder->decode($request->getParam(0)), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('This function returns a list of stock location ids.');
$Parameter[0]['name'] = __('User name');
$Parameter[0]['description'] = __('A valid weberp username. This user should have security access  to this data.');
$Parameter[1]['name'] = __('User password');
$Parameter[1]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('This function returns an array of stock location ids.');

$GetLocationList_sig = array(
	array(Value::$xmlrpcArray),
	array(Value::$xmlrpcArray, Value::$xmlrpcString, Value::$xmlrpcString));
$GetLocationList_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_GetLocationList($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 2) {
		$rtn = new Response($encoder->encode(GetLocationList(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(GetLocationList('', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('This function takes a stock location id and returns details of that stock location.');
$Parameter[0]['name'] = __('Stock Location Code');
$Parameter[0]['description'] = __('A stock location code as returned by the GetLocationList function.');
$Parameter[1]['name'] = __('User name');
$Parameter[1]['description'] = __('A valid weberp username. This user should have security access to this data.');
$Parameter[2]['name'] = __('User password');
$Parameter[2]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('This function returns an array of stock location details.');

$GetLocationDetails_sig = array(
	array(Value::$xmlrpcArray, Value::$xmlrpcString),
	array(Value::$xmlrpcArray, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString));
$GetLocationDetails_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_GetLocationDetails($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 3) {
		$rtn = new Response($encoder->encode(GetLocationDetails(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(GetLocationDetails($request->getParam(0)->scalarval(), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('This function returns a list of stock shipper ids.');
$Parameter[0]['name'] = __('User name');
$Parameter[0]['description'] = __('A valid weberp username. This user should have security access  to this data.');
$Parameter[1]['name'] = __('User password');
$Parameter[1]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('This function returns an array of stock shipper ids.');

$GetShipperList_sig = array(
	array(Value::$xmlrpcArray),
	array(Value::$xmlrpcArray, Value::$xmlrpcString, Value::$xmlrpcString));
$GetShipperList_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_GetShipperList($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 2) {
		$rtn = new Response($encoder->encode(GetShipperList(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(GetShipperList('', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('This function takes a stock shipper id and returns details of that shipper.');
$Parameter[0]['name'] = __('Stock Shipper ID');
$Parameter[0]['description'] = __('A stock shipper ID as returned by the GetShippersList function.');
$Parameter[1]['name'] = __('User name');
$Parameter[1]['description'] = __('A valid weberp username. This user should have security access to this data.');
$Parameter[2]['name'] = __('User password');
$Parameter[2]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('This function returns an array of stock shipper details.');

$GetShipperDetails_sig = array(
	array(Value::$xmlrpcArray, Value::$xmlrpcString),
	array(Value::$xmlrpcArray, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString));
$GetShipperDetails_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_GetShipperDetails($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 3) {
		$rtn = new Response($encoder->encode(GetShipperDetails(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(GetShipperDetails($request->getParam(0)->scalarval(), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('This function returns a list of sales area codes.');
$Parameter[0]['name'] = __('User name');
$Parameter[0]['description'] = __('A valid weberp username. This user should have security access  to this data.');
$Parameter[1]['name'] = __('User password');
$Parameter[1]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('This function returns an array of sales area codes.');

$GetSalesAreasList_sig = array(
	array(Value::$xmlrpcArray),
	array(Value::$xmlrpcArray, Value::$xmlrpcString, Value::$xmlrpcString));
$GetSalesAreasList_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_GetSalesAreasList($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 2) {
		$rtn = new Response($encoder->encode(GetSalesAreasList(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(GetSalesAreasList('', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('This function takes a sales area code and returns details of that sales area.');
$Parameter[0]['name'] = __('Sales Area Code');
$Parameter[0]['description'] = __('A sales area code as returned by the GetSalesAreasList function.');
$Parameter[1]['name'] = __('User name');
$Parameter[1]['description'] = __('A valid weberp username. This user should have security access to this data.');
$Parameter[2]['name'] = __('User password');
$Parameter[2]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('This function returns an array of sales area details.');

$GetSalesAreaDetails_sig = array(
	array(Value::$xmlrpcArray, Value::$xmlrpcString),
	array(Value::$xmlrpcArray, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString));
$GetSalesAreaDetails_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_GetSalesAreaDetails($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 3) {
		$rtn = new Response($encoder->encode(GetSalesAreaDetails(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(GetSalesAreaDetails($request->getParam(0)->scalarval(), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('This function takes a sales area description and returns details of that sales area.');
$Parameter[0]['name'] = __('Sales Area Description');
$Parameter[0]['description'] = __('A sales area description of the sales area of interest.');
$Parameter[1]['name'] = __('User name');
$Parameter[1]['description'] = __('A valid weberp username. This user should have security access to this data.');
$Parameter[2]['name'] = __('User password');
$Parameter[2]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('This function returns an array of sales area details.');

$GetSalesAreaDetailsFromName_sig = array(
	array(Value::$xmlrpcArray, Value::$xmlrpcString),
	array(Value::$xmlrpcArray, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString));
$GetSalesAreaDetailsFromName_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_GetSalesAreaDetailsFromName($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 3) {
		$rtn = new Response($encoder->encode(GetSalesAreaDetailsFromName(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(GetSalesAreaDetailsFromName($request->getParam(0)->scalarval(), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$ReturnValue = __('Return Value Descriptions go here');
$Description = __('Function Description go here');

$Parameter[0]['name'] = __('Sales Area Details');
$Parameter[0]['description'] = __('An array of index/value items describing the sales area to insert.');
$Parameter[1]['name'] = __('User name');
$Parameter[1]['description'] = __('A valid weberp username. This user should have security access to this data.');
$Parameter[2]['name'] = __('User password');
$Parameter[2]['description'] = __('The weberp password associated with this user name. ');

$InsertSalesArea_sig = array(
	array(Value::$xmlrpcValue, Value::$xmlrpcStruct),
	array(Value::$xmlrpcValue, Value::$xmlrpcStruct, Value::$xmlrpcString, Value::$xmlrpcString));
$InsertSalesArea_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_InsertSalesArea($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 3) {
		$rtn = new Response($encoder->encode(InsertSalesArea(
			$encoder->decode($request->getParam(0)),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(InsertSalesArea($encoder->decode($request->getParam(0)), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('This function returns a list of salesman codes.');
$Parameter[0]['name'] = __('User name');
$Parameter[0]['description'] = __('A valid weberp username. This user should have security access  to this data.');
$Parameter[1]['name'] = __('User password');
$Parameter[1]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('This function returns an array of salesman codes.');

$GetSalesmanList_sig = array(
	array(Value::$xmlrpcArray),
	array(Value::$xmlrpcArray, Value::$xmlrpcString, Value::$xmlrpcString));
$GetSalesmanList_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_GetSalesmanList($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 2) {
		$rtn = new Response($encoder->encode(GetSalesmanList(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(GetSalesmanList('', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('This function takes a salesman code and returns details of that salesman.');
$Parameter[0]['name'] = __('Sales Area Code');
$Parameter[0]['description'] = __('A salesman code as returned by the GetSalesmanList function.');
$Parameter[1]['name'] = __('User name');
$Parameter[1]['description'] = __('A valid weberp username. This user should have security access to this data.');
$Parameter[2]['name'] = __('User password');
$Parameter[2]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('This function returns an array of salesman details.');

$GetSalesmanDetails_sig = array(
	array(Value::$xmlrpcArray, Value::$xmlrpcString),
	array(Value::$xmlrpcArray, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString));
$GetSalesmanDetails_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_GetSalesmanDetails($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 3) {
		$rtn = new Response($encoder->encode(GetSalesmanDetails(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(GetSalesmanDetails($request->getParam(0)->scalarval(), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('This function takes a salesman\'s name and returns details of that salesman.');
$Parameter[0]['name'] = __('Salesman Name');
$Parameter[0]['description'] = __('The name of the salesman of interest.');
$Parameter[1]['name'] = __('User name');
$Parameter[1]['description'] = __('A valid weberp username. This user should have security access to this data.');
$Parameter[2]['name'] = __('User password');
$Parameter[2]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('This function returns an array of salesman details.');

$GetSalesmanDetailsFromName_sig = array(
	array(Value::$xmlrpcArray, Value::$xmlrpcString),
	array(Value::$xmlrpcArray, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString));
$GetSalesmanDetailsFromName_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_GetSalesmanDetailsFromName($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 3) {
		$rtn = new Response($encoder->encode(GetSalesmanDetailsFromName(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(GetSalesmanDetailsFromName($request->getParam(0)->scalarval(), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$ReturnValue = __('Return Value Descriptions go here');
$Description = __('Function Description go here');

$Parameter[0]['name'] = __('Salesman Details');
$Parameter[0]['description'] = __('An array of index/value items describing the salesman to insert.');
$Parameter[1]['name'] = __('User name');
$Parameter[1]['description'] = __('A valid weberp username. This user should have security access to this data.');
$Parameter[2]['name'] = __('User password');
$Parameter[2]['description'] = __('The weberp password associated with this user name. ');

$InsertSalesman_sig = array(
	array(Value::$xmlrpcValue, Value::$xmlrpcStruct),
	array(Value::$xmlrpcValue, Value::$xmlrpcStruct, Value::$xmlrpcString, Value::$xmlrpcString));
$InsertSalesman_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_InsertSalesman($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 3) {
		$rtn = new Response($encoder->encode(InsertSalesman(
			$encoder->decode($request->getParam(0)),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(InsertSalesman($encoder->decode($request->getParam(0)), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('This function returns a list of tax group IDs.');
$Parameter[0]['name'] = __('User name');
$Parameter[0]['description'] = __('A valid weberp username. This user should have security access  to this data.');
$Parameter[1]['name'] = __('User password');
$Parameter[1]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('This function returns an array of tax group IDs.');

$GetTaxGroupList_sig = array(
	array(Value::$xmlrpcArray),
	array(Value::$xmlrpcArray, Value::$xmlrpcString, Value::$xmlrpcString));
$GetTaxGroupList_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_GetTaxGroupList($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 2) {
		$rtn = new Response($encoder->encode(GetTaxGroupList(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(GetTaxGroupList('', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('This function takes a tax group ID and returns details of that tax group.');
$Parameter[0]['name'] = __('Tax Group ID');
$Parameter[0]['description'] = __('A tax group ID as returned by the GetTaxgroupList function.');
$Parameter[1]['name'] = __('User name');
$Parameter[1]['description'] = __('A valid weberp username. This user should have security access to this data.');
$Parameter[2]['name'] = __('User password');
$Parameter[2]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('This function returns an array of tax group details.');

$GetTaxGroupDetails_sig = array(
	array(Value::$xmlrpcArray, Value::$xmlrpcString),
	array(Value::$xmlrpcArray, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString));
$GetTaxGroupDetails_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_GetTaxGroupDetails($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 3) {
		$rtn = new Response($encoder->encode(GetTaxGroupDetails
		($request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(GetTaxGroupDetails($request->getParam(0)->scalarval(), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

$Description = __('This function returns a list of tax authority IDs.');
$Parameter[0]['name'] = __('User name');
$Parameter[0]['description'] = __('A valid weberp username. This user should have security access  to this data.');
$Parameter[1]['name'] = __('User password');
$Parameter[1]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('This function returns an array of tax group IDs.');

$GetTaxAuthorityList_sig = array(
	array(Value::$xmlrpcArray),
	array(Value::$xmlrpcArray, Value::$xmlrpcString, Value::$xmlrpcString));
$GetTaxAuthorityList_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_GetTaxAuthorityList($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 2) {
		$rtn = new Response($encoder->encode(GetTaxAuthorityList(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(GetTaxAuthorityList('', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('This function takes a tax authority ID and returns details of that tax authority.');
$Parameter[0]['name'] = __('Tax Authority ID');
$Parameter[0]['description'] = __('A tax Authority ID as returned by the GetTaxAuthorityList function.');
$Parameter[1]['name'] = __('User name');
$Parameter[1]['description'] = __('A valid weberp username. This user should have security access to this data.');
$Parameter[2]['name'] = __('User password');
$Parameter[2]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('This function returns an array of tax authority details.');

$GetTaxAuthorityDetails_sig = array(
	array(Value::$xmlrpcArray, Value::$xmlrpcString),
	array(Value::$xmlrpcArray, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString));
$GetTaxAuthorityDetails_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_GetTaxAuthorityDetails($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 3) {
		$rtn = new Response($encoder->encode(GetTaxAuthorityDetails(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(GetTaxAuthorityDetails($request->getParam(0)->scalarval(), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('This function takes a tax authority ID and returns the rates of tax for the authority.');
$Parameter[0]['name'] = __('Tax Authority ID');
$Parameter[0]['description'] = __('A tax Authority ID as returned by the GetTaxAuthorityList function.');
$Parameter[1]['name'] = __('User name');
$Parameter[1]['description'] = __('A valid weberp username. This user should have security access to this data.');
$Parameter[2]['name'] = __('User password');
$Parameter[2]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('This function returns the tax rates for the authority.');

$GetTaxAuthorityRates_sig = array(
	array(Value::$xmlrpcStruct, Value::$xmlrpcString),
	array(Value::$xmlrpcStruct, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString));
$GetTaxAuthorityRates_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_GetTaxAuthorityRates($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 3) {
		$rtn = new Response($encoder->encode(GetTaxAuthorityRates(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(GetTaxAuthorityRates($request->getParam(0)->scalarval(), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('This function takes a tax group ID and returns the taxes that belong to that tax group.');
$Parameter[0]['name'] = __('Tax Group ID');
$Parameter[0]['description'] = __('A tax group ID as returned by the GetTaxgroupList function.');
$Parameter[1]['name'] = __('User name');
$Parameter[1]['description'] = __('A valid weberp username. This user should have security access to this data.');
$Parameter[2]['name'] = __('User password');
$Parameter[2]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('This function returns an array of tax group details.');

$GetTaxGroupTaxes_sig = array(
	array(Value::$xmlrpcArray, Value::$xmlrpcString),
	array(Value::$xmlrpcArray, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString));
$GetTaxGroupTaxes_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_GetTaxGroupTaxes($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 3) {
		$rtn = new Response($encoder->encode(GetTaxGroupTaxes(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(GetTaxGroupTaxes($request->getParam(0)->scalarval(), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('This function returns a list of customer types.');
$Parameter[0]['name'] = __('User name');
$Parameter[0]['description'] = __('A valid weberp username. This user should have security access  to this data.');
$Parameter[1]['name'] = __('User password');
$Parameter[1]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('This function returns an array of customer types');

$GetCustomerTypeList_sig = array(
	array(Value::$xmlrpcArray),
	array(Value::$xmlrpcArray, Value::$xmlrpcString, Value::$xmlrpcString));
$GetCustomerTypeList_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_GetCustomerTypeList($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 2) {
		$rtn = new Response($encoder->encode(GetCustomerTypeList(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(GetCustomerTypeList('', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('This function takes a customer type ID and returns details of that customer type.');
$Parameter[0]['name'] = __('Customer Type ID');
$Parameter[0]['description'] = __('A customer type ID as returned by the GetCustomerTypeList function.');
$Parameter[1]['name'] = __('User name');
$Parameter[1]['description'] = __('A valid weberp username. This user should have security access to this data.');
$Parameter[2]['name'] = __('User password');
$Parameter[2]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('This function returns an array of customer type details.');

$GetCustomerTypeDetails_sig = array(
	array(Value::$xmlrpcArray, Value::$xmlrpcString),
	array(Value::$xmlrpcArray, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString));
$GetCustomerTypeDetails_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_GetCustomerTypeDetails($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 3) {
		$rtn = new Response($encoder->encode(GetCustomerTypeDetails(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(GetCustomerTypeDetails($request->getParam(0)->scalarval(), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$ReturnValue = __('Return Value Descriptions go here');
$Description = __('Function Description go here');

$Parameter[0]['name'] = __('Category Details');
$Parameter[0]['description'] = __('An array of index/value items describing the stock category to insert.');
$Parameter[1]['name'] = __('User name');
$Parameter[1]['description'] = __('A valid weberp username. This user should have security access to this data.');
$Parameter[2]['name'] = __('User password');
$Parameter[2]['description'] = __('The weberp password associated with this user name. ');

$InsertStockCategory_sig = array(
	array(Value::$xmlrpcValue, Value::$xmlrpcStruct),
	array(Value::$xmlrpcValue, Value::$xmlrpcStruct, Value::$xmlrpcString, Value::$xmlrpcString));
$InsertStockCategory_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_InsertStockCategory($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 3) {
		$rtn = new Response($encoder->encode(InsertStockCategory(
			$encoder->decode($request->getParam(0)),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(InsertStockCategory($encoder->decode($request->getParam(0)), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$ReturnValue = __('Return Value Descriptions go here');
$Description = __('Function Description go here');

$Parameter[0]['name'] = __('Category Details');
$Parameter[0]['description'] = __('An array of index/value items describing the stock category to modify.');
$Parameter[1]['name'] = __('User name');
$Parameter[1]['description'] = __('A valid weberp username. This user should have security access to this data.');
$Parameter[2]['name'] = __('User password');
$Parameter[2]['description'] = __('The weberp password associated with this user name. ');

$ModifyStockCategory_sig = array(
	array(Value::$xmlrpcValue, Value::$xmlrpcStruct),
	array(Value::$xmlrpcValue, Value::$xmlrpcStruct, Value::$xmlrpcString, Value::$xmlrpcString));
$ModifyStockCategory_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_ModifyStockCategory($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 3) {
		$rtn = new Response($encoder->encode(ModifyStockCategory(
			$encoder->decode($request->getParam(0)),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(ModifyStockCategory($encoder->decode($request->getParam(0)), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('This function returns a list of stock category abbreviations.');
$Parameter[0]['name'] = __('User name');
$Parameter[0]['description'] = __('A valid weberp username. This user should have security access  to this data.');
$Parameter[1]['name'] = __('User password');
$Parameter[1]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('If successful, this function returns an array of stock category ids. ')
	. __('Otherwise an array of error codes is returned and no stock categories are returned. ');

$GetStockCategoryList_sig = array(
	array(Value::$xmlrpcArray),
	array(Value::$xmlrpcArray, Value::$xmlrpcString, Value::$xmlrpcString));
$GetStockCategoryList_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_GetStockCategoryList($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 2) {
		$rtn = new Response($encoder->encode(GetStockCategoryList(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(GetStockCategoryList('', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('This function takes a stock category ID and returns details of that stock category type.');
$Parameter[0]['name'] = __('Stock Category ID');
$Parameter[0]['description'] = __('A Stock Category ID as returned by the *WHAT* function.');
$Parameter[1]['name'] = __('User name');
$Parameter[1]['description'] = __('A valid weberp username. This user should have security access to this data.');
$Parameter[2]['name'] = __('User password');
$Parameter[2]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('This function returns an array of stock category details.');

$GetStockCategory_sig = array(
	array(Value::$xmlrpcArray, Value::$xmlrpcString),
	array(Value::$xmlrpcArray, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString));
$GetStockCategory_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_GetStockCategory($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 3) {
		$rtn = new Response($encoder->encode(GetStockCategory(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(GetStockCategory($request->getParam(0)->scalarval(), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$ReturnValue = __('Return Value Descriptions go here');
$Description = __('Function Description go here');

$Parameter[0]['name'] = __('Field Name');
$Parameter[0]['description'] = __('The field name to search on.');
$Parameter[1]['name'] = __('Match Criteria');
$Parameter[1]['description'] = __('The SQL search pattern to select items in the database.');
$Parameter[2]['name'] = __('User name');
$Parameter[2]['description'] = __('A valid weberp username. This user should have security access to this data.');
$Parameter[3]['name'] = __('User password');
$Parameter[3]['description'] = __('The weberp password associated with this user name. ');

$SearchStockCategories_sig = array(
	array(Value::$xmlrpcValue, Value::$xmlrpcString, Value::$xmlrpcString),
	array(Value::$xmlrpcValue, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString));
$SearchStockCategories_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_SearchStockCategories($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 4) {
		$rtn = new Response($encoder->encode(SearchStockCategories(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval(),
			$request->getParam(3)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(SearchStockCategories($request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval(), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$ReturnValue = __('Return Value Descriptions go here');
$Description = __('Function Description go here');

$Parameter[0]['name'] = __('Label Name');
$Parameter[0]['description'] = __('The category label to search on.');
$Parameter[1]['name'] = __('Match Criteria');
$Parameter[1]['description'] = __('The SQL search pattern to select items in the database.');
$Parameter[2]['name'] = __('User name');
$Parameter[2]['description'] = __('A valid weberp username. This user should have security access to this data.');
$Parameter[3]['name'] = __('User password');
$Parameter[3]['description'] = __('The weberp password associated with this user name. ');

$StockCatPropertyList_sig = array(
	array(Value::$xmlrpcValue, Value::$xmlrpcString, Value::$xmlrpcString),
	array(Value::$xmlrpcValue, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString));
$StockCatPropertyList_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_StockCatPropertyList($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 4) {
		$rtn = new Response($encoder->encode(StockCatPropertyList(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval(),
			$request->getParam(3)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(StockCatPropertyList($request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval(), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('This function returns a list of general ledger account codes.');
$Parameter[0]['name'] = __('User name');
$Parameter[0]['description'] = __('A valid weberp username. This user should have security access  to this data.');
$Parameter[1]['name'] = __('User password');
$Parameter[1]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('This function returns an array of general ledger account codes.');

$GetGLAccountList_sig = array(
	array(Value::$xmlrpcArray),
	array(Value::$xmlrpcArray, Value::$xmlrpcString, Value::$xmlrpcString));
$GetGLAccountList_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_GetGLAccountList($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 2) {
		$rtn = new Response($encoder->encode(GetGLAccountList(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(GetGLAccountList('', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('This function takes a general ledger account code and returns details of that account.');
$Parameter[0]['name'] = __('General Ledger Account Code');
$Parameter[0]['description'] = __('A general ledger account code as returned by the GetGLAccountList function.');
$Parameter[1]['name'] = __('User name');
$Parameter[1]['description'] = __('A valid weberp username. This user should have security access to this data.');
$Parameter[2]['name'] = __('User password');
$Parameter[2]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('This function returns an array of general ledger account details.');

$GetGLAccountDetails_sig = array(
	array(Value::$xmlrpcArray, Value::$xmlrpcString),
	array(Value::$xmlrpcArray, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString));
$GetGLAccountDetails_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_GetGLAccountDetails($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 3) {
		$rtn = new Response($encoder->encode(GetGLAccountDetails(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(GetGLAccountDetails($request->getParam(0)->scalarval(), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('This function takes a stock code ID and a tax authority code and returns the relevant tax rate.');
$Parameter[0]['name'] = __('StockID');
$Parameter[0]['description'] = __('The stock ID of the item whose tax rate is desired.');
$Parameter[1]['name'] = __('Tax Authority Code');
$Parameter[1]['description'] = __('The code identifying the tax authority of interest.');
$Parameter[2]['name'] = __('User name');
$Parameter[2]['description'] = __('A valid weberp username. This user should have security access to this data.');
$Parameter[3]['name'] = __('User password');
$Parameter[3]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('This function returns an array of general ledger account details.');

$GetStockTaxRate_sig = array(
	array(Value::$xmlrpcArray, Value::$xmlrpcString, Value::$xmlrpcString),
	array(Value::$xmlrpcArray, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString));
$GetStockTaxRate_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_GetStockTaxRate($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 4) {
		$rtn = new Response($encoder->encode(GetStockTaxRate(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval(),
			$request->getParam(3)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(GetStockTaxRate(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval(), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('This function is used to insert a new supplier into the webERP database.');
$Parameter[0]['name'] = __('Supplier Details');
$Parameter[0]['description'] = __('A set of key/value pairs where the key must be identical to the name of the field to be updated. ')
	. __('The field names can be found ') . '<a href="../../Z_DescribeTable.php?table=suppliers">' . __('here ') . '</a>'
	. __('and are case sensitive. ') . __('The values should be of the correct type, and the api will check them before updating the database. ')
	. __('It is not necessary to include all the fields in this parameter, the database default value will be used if the field is not given.');
$Parameter[1]['name'] = __('User name');
$Parameter[1]['description'] = __('A valid weberp username. This user should have security access  to this data.');
$Parameter[2]['name'] = __('User password');
$Parameter[2]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('This function returns an array of integers. ')
	. __('If the first element is zero then the function was successful. ')
	. __('Otherwise an array of error codes is returned and no insertion takes place. ');

$InsertSupplier_sig = array(
	array(Value::$xmlrpcArray, Value::$xmlrpcStruct),
	array(Value::$xmlrpcArray, Value::$xmlrpcStruct, Value::$xmlrpcString, Value::$xmlrpcString));
$InsertSupplier_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_InsertSupplier($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 3) {
		$rtn = new Response($encoder->encode(InsertSupplier(
			$encoder->decode($request->getParam(0)),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(InsertSupplier($encoder->decode($request->getParam(0)), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('This function is used to modify a supplier which is already setup in the webERP database.');
$Parameter[0]['name'] = __('Supplier Details');
$Parameter[0]['description'] = __('A set of key/value pairs where the key must be identical to the name of the field to be updated. ')
	. __('The field names can be found ') . '<a href="../../Z_DescribeTable.php?table=suppliers">' . __('here ') . '</a>'
	. __('and are case sensitive. ') . __('The values should be of the correct type, and the api will check them before updating the database. ')
	. __('It is not necessary to include all the fields in this parameter, the database default value will be used if the field is not given.')
	. '<p>' . __('The supplierid must already exist in the weberp database.');
$Parameter[1]['name'] = __('User name');
$Parameter[1]['description'] = __('A valid weberp username. This user should have security access  to this data.');
$Parameter[2]['name'] = __('User password');
$Parameter[2]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('This function returns an array of integers. ')
	. __('If the first element is zero then the function was successful. ')
	. __('Otherwise an array of error codes is returned and no modification takes place. ');

$ModifySupplier_sig = array(
	array(Value::$xmlrpcArray, Value::$xmlrpcStruct),
	array(Value::$xmlrpcArray, Value::$xmlrpcStruct, Value::$xmlrpcString, Value::$xmlrpcString));
$ModifySupplier_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_ModifySupplier($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 3) {
		$rtn = new Response($encoder->encode(ModifySupplier(
			$encoder->decode($request->getParam(0)),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(ModifySupplier($encoder->decode($request->getParam(0)), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('This function is used to retrieve the details of a supplier from the webERP database.');
$Parameter[0]['name'] = __('Supplier ID');
$Parameter[0]['description'] = __('This is a string value. It must be a valid supplier id that is already in the webERP database.');
$Parameter[1]['name'] = __('User name');
$Parameter[1]['description'] = __('A valid weberp username. This user should have security access  to this data.');
$Parameter[2]['name'] = __('User password');
$Parameter[2]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('If successful this function returns a set of key/value pairs containing the details of this supplier. ')
	. __('The key will be identical with field name from the suppliers table. All fields will be in the set regardless of whether the value was set.') . '<p>'
	. __('Otherwise an array of error codes is returned. ');

$GetSupplier_sig = array(
	array(Value::$xmlrpcStruct, Value::$xmlrpcString),
	array(Value::$xmlrpcStruct, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString));
$GetSupplier_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_GetSupplier($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 3) {
		$rtn = new Response($encoder->encode(GetSupplier(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(GetSupplier($request->getParam(0)->scalarval(), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('This function is used to retrieve the details of a supplier from the webERP database.');
$Parameter[0]['name'] = __('Field name');
$Parameter[0]['description'] = __('This is a string value. It must be a valid field in the suppliers table. This is case sensitive');
$Parameter[1]['name'] = __('Criteria');
$Parameter[1]['description'] = __('This is a string value. It holds the string that is searched for in the given field. It will search for all or part of the field.');
$Parameter[2]['name'] = __('User name');
$Parameter[2]['description'] = __('A valid weberp username. This user should have security access  to this data.');
$Parameter[3]['name'] = __('User password');
$Parameter[3]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('If successful this function returns an array of supplier ids. ')
	. __('Otherwise an array of error codes is returned. ');

$SearchSuppliers_sig = array(
	array(Value::$xmlrpcArray, Value::$xmlrpcString, Value::$xmlrpcString),
	array(Value::$xmlrpcArray, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString));
$SearchSuppliers_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_SearchSuppliers($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 4) {
		$rtn = new Response($encoder->encode(SearchSuppliers(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval(),
			$request->getParam(3)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(SearchSuppliers(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval(), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('This function is used to retrieve the details of stock batches.');
$Parameter[0]['name'] = __('Stock ID');
$Parameter[0]['description'] = __('A string field containing a valid stockid that must already be setup in the stockmaster table. The api will check this before making the enquiry.');
$Parameter[1]['name'] = __('Criteria');
$Parameter[1]['description'] = __('This is a string value. It holds the string that is searched for in the given field. It will search for all or part of the field.');
$Parameter[2]['name'] = __('User name');
$Parameter[2]['description'] = __('A valid weberp username. This user should have security access  to this data.');
$Parameter[3]['name'] = __('User password');
$Parameter[3]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('Returns a two dimensional array of stock batch details. ')
	. __('The fields returned are stockid, loccode, batchno, quantity, itemcost. ');

$GetBatches_sig = array(
	array(Value::$xmlrpcArray, Value::$xmlrpcString, Value::$xmlrpcString),
	array(Value::$xmlrpcArray, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString));
$GetBatches_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_GetBatches($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 4) {
		$rtn = new Response($encoder->encode(GetBatches(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval(),
			$request->getParam(3)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(GetBatches(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval(), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('Adjust the stock balance for the given stock code at the given location by the amount given.');
$Parameter[0]['name'] = __('Stock ID');
$Parameter[0]['description'] = __('A string field containing a valid stockid that must already be setup in the stockmaster table. The api will check this before making the enquiry.');
$Parameter[1]['name'] = __('Location');
$Parameter[1]['description'] = __('A string field containing a valid location code that must already be setup in the locations table. The api will check this before making the enquiry.');
$Parameter[2]['name'] = __('Quantity');
$Parameter[2]['description'] = __('This is an integer value. It holds the amount of stock to be adjusted. Should be negative if is stock is to be reduced');
$Parameter[3]['name'] = __('Transaction Date');
$Parameter[3]['description'] = __('This is a string value. It holds the string that is searched for in the given field. It will search for all or part of the field.');
$Parameter[4]['name'] = __('User name');
$Parameter[4]['description'] = __('A valid weberp username. This user should have security access  to this data.');
$Parameter[5]['name'] = __('User password');
$Parameter[5]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('If successful this function returns 0. ')
	. __('Otherwise an array of error codes is returned. ');

$StockAdjustment_sig = array(
	array(Value::$xmlrpcValue, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcDouble, Value::$xmlrpcString),
	array(Value::$xmlrpcValue, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcDouble, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString));
$StockAdjustment_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_StockAdjustment($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 6) {
		$rtn = new Response($encoder->encode(StockAdjustment(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval(),
			$request->getParam(3)->scalarval(),
			$request->getParam(4)->scalarval(),
			$request->getParam(5)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(StockAdjustment(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval(),
			$request->getParam(3)->scalarval(), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('Issues stock to a given work order from the given location');
$Parameter[0]['name'] = __('Work Order Number');
$Parameter[0]['description'] = __('A string field containing a valid work order number that has already been created. The api will check this before making the enquiry.');
$Parameter[1]['name'] = __('Stock ID');
$Parameter[1]['description'] = __('A string field containing a valid stockid that must already be setup in the stockmaster table. The api will check this before making the enquiry.');
$Parameter[2]['name'] = __('Location');
$Parameter[2]['description'] = __('A string field containing a valid location code that must already be setup in the locations table. The api will check this before making the enquiry.');
$Parameter[3]['name'] = __('Quantity');
$Parameter[3]['description'] = __('This is an integer value. It holds the amount of stock to be adjusted. Should be negative if is stock is to be reduced');
$Parameter[4]['name'] = __('Transaction Date');
$Parameter[4]['description'] = __('This is a string value. It holds the string that is searched for in the given field. It will search for all or part of the field.');
$Parameter[4]['name'] = __('Batch number');
$Parameter[4]['description'] = __('This is a string value. It holds the reference to the batch number for the product being issued. If the stockid is not batch controlled this is ignored.');
$Parameter[5]['name'] = __('User name');
$Parameter[5]['description'] = __('A valid weberp username. This user should have security access  to this data.');
$Parameter[6]['name'] = __('User password');
$Parameter[6]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('If successful this function returns 0. ')
	. __('Otherwise an array of error codes is returned. ');

$WorkOrderIssue_sig = array(
	array(Value::$xmlrpcValue, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString),
	array(Value::$xmlrpcValue, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString));
$WorkOrderIssue_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_WorkOrderIssue($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 8) {
		$rtn = new Response($encoder->encode(WorkOrderIssue(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval(),
			$request->getParam(3)->scalarval(),
			$request->getParam(4)->scalarval(),
			$request->getParam(5)->scalarval(),
			$request->getParam(6)->scalarval(),
			$request->getParam(7)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(WorkOrderIssue(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval(),
			$request->getParam(3)->scalarval(),
			$request->getParam(4)->scalarval(),
			$request->getParam(5)->scalarval(), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('This function is used to retrieve the details of a work order from the webERP database.');
$Parameter[0]['name'] = __('Field name');
$Parameter[0]['description'] = __('This is a string value. It must be a valid field in the workorders table. This is case sensitive');
$Parameter[1]['name'] = __('Criteria');
$Parameter[1]['description'] = __('This is a string value. It holds the string that is searched for in the given field. It will search for all or part of the field.');
$Parameter[2]['name'] = __('User name');
$Parameter[2]['description'] = __('A valid weberp username. This user should have security access  to this data.');
$Parameter[3]['name'] = __('User password');
$Parameter[3]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('If successful this function returns an array of work order numbers. ')
	. __('Otherwise an array of error codes is returned. ');

$SearchWorkOrders_sig = array(
	array(Value::$xmlrpcArray, Value::$xmlrpcString, Value::$xmlrpcString),
	array(Value::$xmlrpcArray, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString));
$SearchWorkOrders_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_SearchWorkOrders($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 4) {
		$rtn = new Response($encoder->encode(SearchWorkOrders(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval(),
			$request->getParam(3)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(SearchWorkOrders(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval(), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('This function is used to insert new purchasing data into the webERP database.');
$Parameter[0]['name'] = __('Purchasing data');
$Parameter[0]['description'] = __('A set of key/value pairs where the key must be identical to the name of the field to be updated. ')
	. __('The field names can be found ') . '<a href="../../Z_DescribeTable.php?table=purchdata">' . __('here ') . '</a>'
	. __('and are case sensitive. ') . __('The values should be of the correct type, and the api will check them before updating the database. ')
	. __('It is not necessary to include all the fields in this parameter, the database default value will be used if the field is not given.');
$Parameter[1]['name'] = __('User name');
$Parameter[1]['description'] = __('A valid weberp username. This user should have security access  to this data.');
$Parameter[2]['name'] = __('User password');
$Parameter[2]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('This function returns an array of integers. ')
	. __('If the first element is zero then the function was successful. ')
	. __('Otherwise an array of error codes is returned and no insertion takes place. ');

$InsertPurchData_sig = array(
	array(Value::$xmlrpcArray, Value::$xmlrpcStruct),
	array(Value::$xmlrpcArray, Value::$xmlrpcStruct, Value::$xmlrpcString, Value::$xmlrpcString));
$InsertPurchData_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_InsertPurchData($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 3) {
		$rtn = new Response($encoder->encode(InsertPurchData(
			$encoder->decode($request->getParam(0)),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(InsertPurchData($encoder->decode($request->getParam(0)), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('This function is used to modify purchasing data into the webERP database.');
$Parameter[0]['name'] = __('Purchasing data');
$Parameter[0]['description'] = __('A set of key/value pairs where the key must be identical to the name of the field to be updated. ')
	. __('The field names can be found ') . '<a href="../../Z_DescribeTable.php?table=purchdata">' . __('here ') . '</a>'
	. __('and are case sensitive. ') . __('The values should be of the correct type, and the api will check them before updating the database. ')
	. __('It is not necessary to include all the fields in this parameter, the database default value will be used if the field is not given.');
$Parameter[1]['name'] = __('User name');
$Parameter[1]['description'] = __('A valid weberp username. This user should have security access  to this data.');
$Parameter[2]['name'] = __('User password');
$Parameter[2]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('This function returns an array of integers. ')
	. __('If the first element is zero then the function was successful. ')
	. __('Otherwise an array of error codes is returned and no insertion takes place. ');

$ModifyPurchData_sig = array(
	array(Value::$xmlrpcArray, Value::$xmlrpcStruct),
	array(Value::$xmlrpcArray, Value::$xmlrpcStruct, Value::$xmlrpcString, Value::$xmlrpcString));
$ModifyPurchData_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_ModifyPurchData($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 3) {
		$rtn = new Response($encoder->encode(ModifyPurchData(
			$encoder->decode($request->getParam(0)),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(ModifyPurchData($encoder->decode($request->getParam(0)), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('This function is used to insert a new work order into the webERP database. Currently this works only for single line orders.');
$Parameter[0]['name'] = __('Work order details');
$Parameter[0]['description'] = __('A set of key/value pairs where the key must be identical to the name of the field to be updated. ')
	. __('The field names can be found ') . '<a href="../../Z_DescribeTable.php?table=workorders">' . __('here ') . '</a>'
	. __('and are case sensitive. ') . __('The values should be of the correct type, and the api will check them before updating the database. ')
	. __('It is not necessary to include all the fields in this parameter, the database default value will be used if the field is not given.');
$Parameter[1]['name'] = __('User name');
$Parameter[1]['description'] = __('A valid weberp username. This user should have security access  to this data.');
$Parameter[2]['name'] = __('User password');
$Parameter[2]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('This function returns an array of integers. ')
	. __('If the first element is zero then the function was successful. ')
	. __('Otherwise an array of error codes is returned and no insertion takes place. ');

$InsertWorkOrder_sig = array(
	array(Value::$xmlrpcArray, Value::$xmlrpcStruct),
	array(Value::$xmlrpcArray, Value::$xmlrpcStruct, Value::$xmlrpcString, Value::$xmlrpcString));
$InsertWorkOrder_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_InsertWorkOrder($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 3) {
		$rtn = new Response($encoder->encode(InsertWorkOrder(
			$encoder->decode($request->getParam(0)),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(InsertWorkOrder($encoder->decode($request->getParam(0)), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('Receives stock from a given work order from the given location');
$Parameter[0]['name'] = __('Work Order Number');
$Parameter[0]['description'] = __('A string field containing a valid work order number that has already been created. The api will check this before making the enquiry.');
$Parameter[1]['name'] = __('Stock ID');
$Parameter[1]['description'] = __('A string field containing a valid stockid that must already be setup in the stockmaster table. The api will check this before making the enquiry.');
$Parameter[2]['name'] = __('Location');
$Parameter[2]['description'] = __('A string field containing a valid location code that must already be setup in the locations table. The api will check this before making the enquiry.');
$Parameter[3]['name'] = __('Quantity');
$Parameter[3]['description'] = __('This is an integer value. It holds the amount of stock to be adjusted. Should be negative if is stock is to be reduced');
$Parameter[4]['name'] = __('Transaction Date');
$Parameter[4]['description'] = __('This is a string value. It holds the string that is searched for in the given field. It will search for all or part of the field.');
$Parameter[5]['name'] = __('User name');
$Parameter[5]['description'] = __('A valid weberp username. This user should have security access  to this data.');
$Parameter[6]['name'] = __('User password');
$Parameter[6]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('If successful this function returns 0. ')
	. __('Otherwise an array of error codes is returned. ');

$WorkOrderReceive_sig = array(
	array(Value::$xmlrpcValue, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString),
	array(Value::$xmlrpcValue, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString));
$WorkOrderReceive_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_WorkOrderReceive($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 7) {
		$rtn = new Response($encoder->encode(WorkOrderReceive(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval(),
			$request->getParam(3)->scalarval(),
			$request->getParam(4)->scalarval(),
			$request->getParam(5)->scalarval(),
			$request->getParam(6)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(WorkOrderReceive($request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval(),
			$request->getParam(3)->scalarval(),
			$request->getParam(4)->scalarval(), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('Returns the webERP default date format');
$Parameter[0]['name'] = __('User name');
$Parameter[0]['description'] = __('A valid weberp username. This user should have security access  to this data.');
$Parameter[1]['name'] = __('User password');
$Parameter[1]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('If successful this function returns a string contain the default date format. ')
	. __('Otherwise an array of error codes is returned. ');

$GetDefaultDateFormat_sig = array(
	array(Value::$xmlrpcValue),
	array(Value::$xmlrpcValue, Value::$xmlrpcString, Value::$xmlrpcString));
$GetDefaultDateFormat_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_GetDefaultDateFormat($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 2) {
		$rtn = new Response($encoder->encode(GetDefaultDateFormat(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(GetDefaultDateFormat('', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('Returns the webERP default shipper');
$Parameter[0]['name'] = __('User name');
$Parameter[0]['description'] = __('A valid weberp username. This user should have security access  to this data.');
$Parameter[1]['name'] = __('User password');
$Parameter[1]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('If successful this function returns an array of two elements the first should contain an integer of zero for successful and the second an associative array containing the key of confvalue the value of which is the Default_Shipper.')
	. __('Otherwise an array of error codes is returned. ');

$GetDefaultShipper_sig = array(
	array(Value::$xmlrpcArray),
	array(Value::$xmlrpcArray, Value::$xmlrpcString, Value::$xmlrpcString));
$GetDefaultShipper_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_GetDefaultShipper($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 2) {
		$rtn = new Response($encoder->encode(GetDefaultShipper(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(GetDefaultShipper('', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('Returns the webERP default location');
$Parameter[0]['name'] = __('User name');
$Parameter[0]['description'] = __('A valid weberp username. This user should have security access  to this data.');
$Parameter[1]['name'] = __('User password');
$Parameter[1]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('If successful this function returns a string contain the default location. ')
	. __('Otherwise an array of error codes is returned. ');

$GetDefaultCurrency_sig = array(
	array(Value::$xmlrpcValue),
	array(Value::$xmlrpcValue, Value::$xmlrpcString, Value::$xmlrpcString));
$GetDefaultCurrency_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_GetDefaultCurrency($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 2) {
		$rtn = new Response($encoder->encode(GetDefaultCurrency(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(GetDefaultCurrency('', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('Returns the webERP default price list');
$Parameter[0]['name'] = __('User name');
$Parameter[0]['description'] = __('A valid weberp username. This user should have security access  to this data.');
$Parameter[1]['name'] = __('User password');
$Parameter[1]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('If successful this function returns a string contain the default price list code. ')
	. __('Otherwise an array of error codes is returned. ');

$GetDefaultPriceList_sig = array(
	array(Value::$xmlrpcValue),
	array(Value::$xmlrpcValue, Value::$xmlrpcString, Value::$xmlrpcString));
$GetDefaultPriceList_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_GetDefaultPriceList($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 2) {
		$rtn = new Response($encoder->encode(GetDefaultPriceList(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(GetDefaultPriceList('', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('Returns the webERP default inventory location');
$Parameter[0]['name'] = __('User name');
$Parameter[0]['description'] = __('A valid weberp username. This user should have security access  to this data.');
$Parameter[1]['name'] = __('User password');
$Parameter[1]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('If successful this function returns a string contain the default inventory location. ')
	. __('Otherwise an array of error codes is returned. ');

$GetDefaultLocation_sig = array(
	array(Value::$xmlrpcValue),
	array(Value::$xmlrpcValue, Value::$xmlrpcString, Value::$xmlrpcString));
$GetDefaultLocation_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_GetDefaultLocation($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 2) {
		$rtn = new Response($encoder->encode(GetDefaultLocation(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(GetDefaultLocation('', '')));
	}
	ob_end_flush();
	return $rtn;
}

$Description = __('Returns the webERP reports directory for the company selected');
$Parameter[0]['name'] = __('User name');
$Parameter[0]['description'] = __('A valid weberp username. This user should have security access  to this data.');
$Parameter[1]['name'] = __('User password');
$Parameter[1]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('If successful this function returns a string containing the path to the company reports directory') . ' ' . __('Otherwise an array of error codes is returned. ');

$GetReportsDirectory_sig = array(
	array(Value::$xmlrpcString),
	array(Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString));
$GetReportsDirectory_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_GetReportsDirectory($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 2) {
		$rtn = new Response($encoder->encode(GetReportsDirectory(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(GetReportsDirectory('', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('This function creates a POS data file on the webERP server for download by the POS');
$Parameter[0]['name'] = __('POS Customer Code - a valid webERP customer that sales from the POS are made against.');
$Parameter[0]['description'] = __('POS Customer Branch Code - a valid branch code of the webERP customer that the POS sales are made against');
$Parameter[1]['name'] = __('User name');
$Parameter[1]['description'] = __('A valid weberp username. This user should have security access  to this data.');
$Parameter[2]['name'] = __('User name');
$Parameter[2]['description'] = __('A valid weberp username. This user should have security access  to this data.');
$Parameter[3]['name'] = __('User password');
$Parameter[3]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('If successful this function returns 0 for success and 1 for error. ');

$CreatePOSDataFull_sig = array(array(Value::$xmlrpcInt, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString));
$CreatePOSDataFull_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_CreatePOSDataFull($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 4) {
		$rtn = new Response($encoder->encode(CreatePOSDataFull($request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval(),
			$request->getParam(3)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(CreatePOSDataFull(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval(),
			'',
			'')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('This function deletes a POS data file on the webERP server');
$Parameter[0]['name'] = __('User name');
$Parameter[0]['description'] = __('A valid weberp username. This user should have security access  to this data.');
$Parameter[1]['name'] = __('User password');
$Parameter[1]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('Returns 0 if the delete POS Data was successful');

$DeletePOSData_sig = array(array(Value::$xmlrpcInt, Value::$xmlrpcString, Value::$xmlrpcString));
$DeletePOSData_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_DeletePOSData($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 2) {
		$rtn = new Response($encoder->encode(DeletePOSData(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(DeletePOSData('', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('Returns the value of the specified stock category property for the specified stock item category');
$Parameter[0]['name'] = __('Property');
$Parameter[0]['description'] = __('The name of the specific property to be returned.');
$Parameter[1]['name'] = __('Stock ID');
$Parameter[1]['description'] = __('The ID of the stock item for which the value of the above property is required. ');
$Parameter[2]['name'] = __('User name');
$Parameter[2]['description'] = __('A valid weberp username. This user should have security access  to this data.');
$Parameter[3]['name'] = __('User password');
$Parameter[3]['description'] = __('The weberp password associated with this user name. ');
$ReturnValue = __('If successful this function returns zero, and the value of the requested property. ')
	. __('Otherwise an array of error codes is returned. ');

$GetStockCatProperty_sig = array(
	array(Value::$xmlrpcValue, Value::$xmlrpcString, Value::$xmlrpcString),
	array(Value::$xmlrpcValue, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString, Value::$xmlrpcString));
$GetStockCatProperty_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_GetStockCatProperty($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	if ($request->getNumParams() == 4) {
		$rtn = new Response($encoder->encode(GetStockCatProperty(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval(),
			$request->getParam(2)->scalarval(),
			$request->getParam(3)->scalarval())));
	} else {
		$rtn = new Response($encoder->encode(GetStockCatProperty(
			$request->getParam(0)->scalarval(),
			$request->getParam(1)->scalarval(), '', '')));
	}
	ob_end_flush();
	return $rtn;
}

unset($Description);
unset($Parameter);
unset($ReturnValue);

$Description = __('Returns (possibly translated) error text from error codes');
$Parameter[0]['name'] = __('Error codes');
$Parameter[0]['description'] = __('An array of error codes to change into text messages. ');
$ReturnValue = __('An array of two element arrays, one per error code. The second array has the error code in element 0 and the error string in element 1. ');
$GetErrorMessages_sig = array(array(Value::$xmlrpcArray, Value::$xmlrpcArray));
$GetErrorMessages_doc = apiBuildDocHTML($Description, $Parameter, $ReturnValue);

function xmlrpc_GetErrorMessages($request)
{
	ob_start('ob_file_callback');
	$encoder = new Encoder();
	$rtn = new Response($encoder->encode(GetAPIErrorMessages($encoder->decode($request->getParam(0)))));
	ob_end_flush();
	return $rtn;
}

return array(
	"weberp.xmlrpc_Login" => array(
		"function" => "xmlrpc_Login",
		"signature" => $Login_sig,
		"docstring" => $Login_doc),
	"weberp.xmlrpc_Logout" => array(
		"function" => "xmlrpc_Logout",
		"signature" => $Logout_sig,
		"docstring" => $Logout_doc),
	"weberp.xmlrpc_InsertCustomer" => array(
		"function" => "xmlrpc_InsertCustomer",
		"signature" => $InsertCustomer_sig,
		"docstring" => $InsertCustomer_doc),
	"weberp.xmlrpc_ModifyCustomer" => array(
		"function" => "xmlrpc_ModifyCustomer",
		"signature" => $ModifyCustomer_sig,
		"docstring" => $ModifyCustomer_doc),
	"weberp.xmlrpc_GetCustomer" => array(
		"function" => "xmlrpc_GetCustomer",
		"signature" => $GetCustomer_sig,
		"docstring" => $GetCustomer_doc),
	"weberp.xmlrpc_SearchCustomers" => array(
		"function" => "xmlrpc_SearchCustomers",
		"signature" => $SearchCustomers_sig,
		"docstring" => $SearchCustomers_doc),
	"weberp.xmlrpc_GetCurrencyList" => array(
		"function" => "xmlrpc_GetCurrencyList",
		"signature" => $GetCurrencyList_sig,
		"docstring" => $GetCurrencyList_doc),
	"weberp.xmlrpc_GetCurrencyDetails" => array(
		"function" => "xmlrpc_GetCurrencyDetails",
		"signature" => $GetCurrencyDetails_sig,
		"docstring" => $GetCurrencyDetails_doc),
	"weberp.xmlrpc_GetSalesTypeList" => array(
		"function" => "xmlrpc_GetSalesTypeList",
		"signature" => $GetSalesTypeList_sig,
		"docstring" => $GetSalesTypeList_doc),
	"weberp.xmlrpc_GetSalesTypeDetails" => array(
		"function" => "xmlrpc_GetSalesTypeDetails",
		"signature" => $GetSalesTypeDetails_sig,
		"docstring" => $GetSalesTypeDetails_doc),
	"weberp.xmlrpc_InsertSalesType" => array(
		"function" => "xmlrpc_InsertSalesType",
		"signature" => $InsertSalesType_sig,
		"docstring" => $InsertSalesType_doc),
	"weberp.xmlrpc_GetHoldReasonList" => array(
		"function" => "xmlrpc_GetHoldReasonList",
		"signature" => $GetHoldReasonList_sig,
		"docstring" => $GetHoldReasonList_doc),
	"weberp.xmlrpc_GetHoldReasonDetails" => array(
		"function" => "xmlrpc_GetHoldReasonDetails",
		"signature" => $GetHoldReasonDetails_sig,
		"docstring" => $GetHoldReasonDetails_doc),
	"weberp.xmlrpc_GetPaymentTermsList" => array(
		"function" => "xmlrpc_GetPaymentTermsList",
		"signature" => $GetPaymentTermsList_sig,
		"docstring" => $GetPaymentTermsList_doc),
	"weberp.xmlrpc_GetPaymentTermsDetails" => array(
		"function" => "xmlrpc_GetPaymentTermsDetails",
		"signature" => $GetPaymentTermsDetails_sig,
		"docstring" => $GetPaymentTermsDetails_doc),
	"weberp.xmlrpc_GetPaymentMethodsList" => array(
		"function" => "xmlrpc_GetPaymentMethodsList",
		"signature" => $GetPaymentMethodsList_sig,
		"docstring" => $GetPaymentMethodsList_doc),
	"weberp.xmlrpc_GetPaymentMethodDetails" => array(
		"function" => "xmlrpc_GetPaymentMethodDetails",
		"signature" => $GetPaymentMethodDetails_sig,
		"docstring" => $GetPaymentMethodDetails_doc),
	"weberp.xmlrpc_InsertStockItem" => array(
		"function" => "xmlrpc_InsertStockItem",
		"signature" => $InsertStockItem_sig,
		"docstring" => $InsertStockItem_doc),
	"weberp.xmlrpc_ModifyStockItem" => array(
		"function" => "xmlrpc_ModifyStockItem",
		"signature" => $ModifyStockItem_sig,
		"docstring" => $ModifyStockItem_doc),
	"weberp.xmlrpc_GetStockItem" => array(
		"function" => "xmlrpc_GetStockItem",
		"signature" => $GetStockItem_sig,
		"docstring" => $GetStockItem_doc),
	"weberp.xmlrpc_SearchStockItems" => array(
		"function" => "xmlrpc_SearchStockItems",
		"signature" => $SearchStockItems_sig,
		"docstring" => $SearchStockItems_doc),
	"weberp.xmlrpc_GetStockBalance" => array(
		"function" => "xmlrpc_GetStockBalance",
		"signature" => $GetStockBalance_sig,
		"docstring" => $GetStockBalance_doc),
	"weberp.xmlrpc_GetStockReorderLevel" => array(
		"function" => "xmlrpc_GetStockReorderLevel",
		"signature" => $GetStockReorderLevel_sig,
		"docstring" => $GetStockReorderLevel_doc),
	"weberp.xmlrpc_SetStockReorderLevel" => array(
		"function" => "xmlrpc_SetStockReorderLevel",
		"signature" => $SetStockReorderLevel_sig,
		"docstring" => $SetStockReorderLevel_doc),
	"weberp.xmlrpc_GetAllocatedStock" => array(
		"function" => "xmlrpc_GetAllocatedStock",
		"signature" => $GetAllocatedStock_sig,
		"docstring" => $GetAllocatedStock_doc),
	"weberp.xmlrpc_GetOrderedStock" => array(
		"function" => "xmlrpc_GetOrderedStock",
		"signature" => $GetOrderedStock_sig,
		"docstring" => $GetOrderedStock_doc),
	"weberp.xmlrpc_SetStockPrice" => array(
		"function" => "xmlrpc_SetStockPrice",
		"signature" => $SetStockPrice_sig,
		"docstring" => $SetStockPrice_doc),
	"weberp.xmlrpc_GetStockPrice" => array(
		"function" => "xmlrpc_GetStockPrice",
		"signature" => $GetStockPrice_sig,
		"docstring" => $GetStockPrice_doc),
	"weberp.xmlrpc_InsertSalesInvoice" => array(
		"function" => "xmlrpc_InsertSalesInvoice",
		"signature" => $InsertSalesInvoice_sig,
		"docstring" => $InsertSalesInvoice_doc),
	"weberp.xmlrpc_AllocateTrans" => array(
		"function" => "xmlrpc_AllocateTrans",
		"signature" => $AllocateTrans_sig,
		"docstring" => $AllocateTrans_doc),
	"weberp.xmlrpc_InsertDebtorReceipt" => array(
		"function" => "xmlrpc_InsertDebtorReceipt",
		"signature" => $InsertDebtorReceipt_sig,
		"docstring" => $InsertDebtorReceipt_doc),
	"weberp.xmlrpc_CreateCreditNote" => array(
		"function" => "xmlrpc_CreateCreditNote",
		"signature" => $CreateCreditNote_sig,
		"docstring" => $CreateCreditNote_doc),
	"weberp.xmlrpc_InsertSalesCredit" => array(
		"function" => "xmlrpc_InsertSalesCredit",
		"signature" => $InsertSalesCredit_sig,
		"docstring" => $InsertSalesCredit_doc),
	"weberp.xmlrpc_InsertBranch" => array(
		"function" => "xmlrpc_InsertBranch",
		"signature" => $InsertBranch_sig,
		"docstring" => $InsertBranch_doc),
	"weberp.xmlrpc_ModifyBranch" => array(
		"function" => "xmlrpc_ModifyBranch",
		"signature" => $ModifyBranch_sig,
		"docstring" => $ModifyBranch_doc),
	"weberp.xmlrpc_GetCustomerBranchCodes" => array(
		"function" => "xmlrpc_GetCustomerBranchCodes",
		"signature" => $GetCustomerBranchCodes_sig,
		"docstring" => $GetCustomerBranchCodes_doc),
	"weberp.xmlrpc_GetCustomerBranch" => array(
		"function" => "xmlrpc_GetCustomerBranch",
		"signature" => $GetCustomerBranch_sig,
		"docstring" => $GetCustomerBranch_doc),
	"weberp.xmlrpc_InsertSalesOrderHeader" => array(
		"function" => "xmlrpc_InsertSalesOrderHeader",
		"signature" => $InsertSalesOrderHeader_sig,
		"docstring" => $InsertSalesOrderHeader_doc),
	"weberp.xmlrpc_ModifySalesOrderHeader" => array(
		"function" => "xmlrpc_ModifySalesOrderHeader",
		"signature" => $ModifySalesOrderHeader_sig,
		"docstring" => $ModifySalesOrderHeader_doc),
	"weberp.xmlrpc_InsertSalesOrderLine" => array(
		"function" => "xmlrpc_InsertSalesOrderLine",
		"signature" => $InsertSalesOrderLine_sig,
		"docstring" => $InsertSalesOrderLine_doc),
	"weberp.xmlrpc_ModifySalesOrderLine" => array(
		"function" => "xmlrpc_ModifySalesOrderLine",
		"signature" => $ModifySalesOrderLine_sig,
		"docstring" => $ModifySalesOrderLine_doc),
	"weberp.xmlrpc_InvoiceSalesOrder" => array(
		"function" => "xmlrpc_InvoiceSalesOrder",
		"signature" => $InvoiceSalesOrder_sig,
		"docstring" => $InvoiceSalesOrder_doc),
	"weberp.xmlrpc_InsertGLAccount" => array(
		"function" => "xmlrpc_InsertGLAccount",
		"signature" => $InsertGLAccount_sig,
		"docstring" => $InsertGLAccount_doc),
	"weberp.xmlrpc_InsertGLAccountSection" => array(
		"function" => "xmlrpc_InsertGLAccountSection",
		"signature" => $InsertGLAccountSection_sig,
		"docstring" => $InsertGLAccountSection_doc),
	"weberp.xmlrpc_InsertGLAccountGroup" => array(
		"function" => "xmlrpc_InsertGLAccountGroup",
		"signature" => $InsertGLAccountGroup_sig,
		"docstring" => $InsertGLAccountGroup_doc),
	"weberp.xmlrpc_GetLocationList" => array(
		"function" => "xmlrpc_GetLocationList",
		"signature" => $GetLocationList_sig,
		"docstring" => $GetLocationList_doc),
	"weberp.xmlrpc_GetLocationDetails" => array(
		"function" => "xmlrpc_GetLocationDetails",
		"signature" => $GetLocationDetails_sig,
		"docstring" => $GetLocationDetails_doc),
	"weberp.xmlrpc_GetShipperList" => array(
		"function" => "xmlrpc_GetShipperList",
		"signature" => $GetShipperList_sig,
		"docstring" => $GetShipperList_doc),
	"weberp.xmlrpc_GetShipperDetails" => array(
		"function" => "xmlrpc_GetShipperDetails",
		"signature" => $GetShipperDetails_sig,
		"docstring" => $GetShipperDetails_doc),
	"weberp.xmlrpc_GetSalesAreasList" => array(
		"function" => "xmlrpc_GetSalesAreasList",
		"signature" => $GetSalesAreasList_sig,
		"docstring" => $GetSalesAreasList_doc),
	"weberp.xmlrpc_InsertSalesArea" => array(
		"function" => "xmlrpc_InsertSalesArea",
		"signature" => $InsertSalesArea_sig,
		"docstring" => $InsertSalesArea_doc),
	"weberp.xmlrpc_GetSalesAreaDetails" => array(
		"function" => "xmlrpc_GetSalesAreaDetails",
		"signature" => $GetSalesAreaDetails_sig,
		"docstring" => $GetSalesAreaDetails_doc),
	"weberp.xmlrpc_GetSalesAreaDetailsFromName" => array(
		"function" => "xmlrpc_GetSalesAreaDetailsFromName",
		"signature" => $GetSalesAreaDetailsFromName_sig,
		"docstring" => $GetSalesAreaDetailsFromName_doc),
	"weberp.xmlrpc_GetSalesmanList" => array(
		"function" => "xmlrpc_GetSalesmanList",
		"signature" => $GetSalesmanList_sig,
		"docstring" => $GetSalesmanList_doc),
	"weberp.xmlrpc_GetSalesmanDetails" => array(
		"function" => "xmlrpc_GetSalesmanDetails",
		"signature" => $GetSalesmanDetails_sig,
		"docstring" => $GetSalesmanDetails_doc),
	"weberp.xmlrpc_GetSalesmanDetailsFromName" => array(
		"function" => "xmlrpc_GetSalesmanDetailsFromName",
		"signature" => $GetSalesmanDetailsFromName_sig,
		"docstring" => $GetSalesmanDetailsFromName_doc),
	"weberp.xmlrpc_InsertSalesman" => array(
		"function" => "xmlrpc_InsertSalesman",
		"signature" => $InsertSalesman_sig,
		"docstring" => $InsertSalesman_doc),
	"weberp.xmlrpc_GetTaxGroupList" => array(
		"function" => "xmlrpc_GetTaxGroupList",
		"signature" => $GetTaxGroupList_sig,
		"docstring" => $GetTaxGroupList_doc),
	"weberp.xmlrpc_GetTaxGroupDetails" => array(
		"function" => "xmlrpc_GetTaxGroupDetails",
		"signature" => $GetTaxGroupDetails_sig,
		"docstring" => $GetTaxGroupDetails_doc),
	"weberp.xmlrpc_GetTaxGroupTaxes" => array(
		"function" => "xmlrpc_GetTaxGroupTaxes",
		"signature" => $GetTaxGroupTaxes_sig,
		"docstring" => $GetTaxGroupTaxes_doc),
	"weberp.xmlrpc_GetTaxAuthorityList" => array(
		"function" => "xmlrpc_GetTaxAuthorityList",
		"signature" => $GetTaxAuthorityList_sig,
		"docstring" => $GetTaxAuthorityList_doc),
	"weberp.xmlrpc_GetTaxAuthorityDetails" => array(
		"function" => "xmlrpc_GetTaxAuthorityDetails",
		"signature" => $GetTaxAuthorityDetails_sig,
		"docstring" => $GetTaxAuthorityDetails_doc),
	"weberp.xmlrpc_GetTaxAuthorityRates" => array(
		"function" => "xmlrpc_GetTaxAuthorityRates",
		"signature" => $GetTaxAuthorityRates_sig,
		"docstring" => $GetTaxAuthorityRates_doc),
	"weberp.xmlrpc_GetCustomerTypeList" => array(
		"function" => "xmlrpc_GetCustomerTypeList",
		"signature" => $GetCustomerTypeList_sig,
		"docstring" => $GetCustomerTypeList_doc),
	"weberp.xmlrpc_GetCustomerTypeDetails" => array(
		"function" => "xmlrpc_GetCustomerTypeDetails",
		"signature" => $GetCustomerTypeDetails_sig,
		"docstring" => $GetCustomerTypeDetails_doc),
	"weberp.xmlrpc_InsertStockCategory" => array(
		"function" => "xmlrpc_InsertStockCategory",
		"signature" => $InsertStockCategory_sig,
		"docstring" => $InsertStockCategory_doc),
	"weberp.xmlrpc_ModifyStockCategory" => array(
		"function" => "xmlrpc_ModifyStockCategory",
		"signature" => $ModifyStockCategory_sig,
		"docstring" => $ModifyStockCategory_doc),
	"weberp.xmlrpc_GetStockCategory" => array(
		"function" => "xmlrpc_GetStockCategory",
		"signature" => $GetStockCategory_sig,
		"docstring" => $GetStockCategory_doc),
	"weberp.xmlrpc_SearchStockCategories" => array(
		"function" => "xmlrpc_SearchStockCategories",
		"signature" => $SearchStockCategories_sig,
		"docstring" => $SearchStockCategories_doc),
	"weberp.xmlrpc_StockCatPropertyList" => array(
		"function" => "xmlrpc_StockCatPropertyList",
		"signature" => $StockCatPropertyList_sig,
		"docstring" => $StockCatPropertyList_doc),
	"weberp.xmlrpc_GetStockCategoryList" => array(
		"function" => "xmlrpc_GetStockCategoryList",
		"signature" => $GetStockCategoryList_sig,
		"docstring" => $GetStockCategoryList_doc),
	"weberp.xmlrpc_GetGLAccountList" => array(
		"function" => "xmlrpc_GetGLAccountList",
		"signature" => $GetGLAccountList_sig,
		"docstring" => $GetGLAccountList_doc),
	"weberp.xmlrpc_GetGLAccountDetails" => array(
		"function" => "xmlrpc_GetGLAccountDetails",
		"signature" => $GetGLAccountDetails_sig,
		"docstring" => $GetGLAccountDetails_doc),
	"weberp.xmlrpc_GetStockTaxRate" => array(
		"function" => "xmlrpc_GetStockTaxRate",
		"signature" => $GetStockTaxRate_sig,
		"docstring" => $GetStockTaxRate_doc),
	"weberp.xmlrpc_InsertSupplier" => array(
		"function" => "xmlrpc_InsertSupplier",
		"signature" => $InsertSupplier_sig,
		"docstring" => $InsertSupplier_doc),
	"weberp.xmlrpc_ModifySupplier" => array(
		"function" => "xmlrpc_ModifySupplier",
		"signature" => $ModifySupplier_sig,
		"docstring" => $ModifySupplier_doc),
	"weberp.xmlrpc_GetSupplier" => array(
		"function" => "xmlrpc_GetSupplier",
		"signature" => $GetSupplier_sig,
		"docstring" => $GetSupplier_doc),
	"weberp.xmlrpc_SearchSuppliers" => array(
		"function" => "xmlrpc_SearchSuppliers",
		"signature" => $SearchSuppliers_sig,
		"docstring" => $SearchSuppliers_doc),
	"weberp.xmlrpc_StockAdjustment" => array(
		"function" => "xmlrpc_StockAdjustment",
		"signature" => $StockAdjustment_sig,
		"docstring" => $StockAdjustment_doc),
	"weberp.xmlrpc_WorkOrderIssue" => array(
		"function" => "xmlrpc_WorkOrderIssue",
		"signature" => $WorkOrderIssue_sig,
		"docstring" => $WorkOrderIssue_doc),
	"weberp.xmlrpc_InsertPurchData" => array(
		"function" => "xmlrpc_InsertPurchData",
		"signature" => $InsertPurchData_sig,
		"docstring" => $InsertPurchData_doc),
	"weberp.xmlrpc_ModifyPurchData" => array(
		"function" => "xmlrpc_ModifyPurchData",
		"signature" => $ModifyPurchData_sig,
		"docstring" => $ModifyPurchData_doc),
	"weberp.xmlrpc_InsertWorkOrder" => array(
		"function" => "xmlrpc_InsertWorkOrder",
		"signature" => $InsertWorkOrder_sig,
		"docstring" => $InsertWorkOrder_doc),
	"weberp.xmlrpc_WorkOrderReceive" => array(
		"function" => "xmlrpc_WorkOrderReceive",
		"signature" => $WorkOrderReceive_sig,
		"docstring" => $WorkOrderReceive_doc),
	"weberp.xmlrpc_SearchWorkOrders" => array(
		"function" => "xmlrpc_SearchWorkOrders",
		"signature" => $SearchWorkOrders_sig,
		"docstring" => $SearchWorkOrders_doc),
	"weberp.xmlrpc_GetBatches" => array(
		"function" => "xmlrpc_GetBatches",
		"signature" => $GetBatches_sig,
		"docstring" => $GetBatches_doc),
	"weberp.xmlrpc_GetDefaultDateFormat" => array(
		"function" => "xmlrpc_GetDefaultDateFormat",
		"signature" => $GetDefaultDateFormat_sig,
		"docstring" => $GetDefaultDateFormat_doc),
	"weberp.xmlrpc_GetDefaultShipper" => array(
		"function" => "xmlrpc_GetDefaultShipper",
		"signature" => $GetDefaultShipper_sig,
		"docstring" => $GetDefaultShipper_doc),
	"weberp.xmlrpc_GetDefaultCurrency" => array(
		"function" => "xmlrpc_GetDefaultCurrency",
		"signature" => $GetDefaultCurrency_sig,
		"docstring" => $GetDefaultCurrency_doc),
	"weberp.xmlrpc_GetDefaultPriceList" => array(
		"function" => "xmlrpc_GetDefaultPriceList",
		"signature" => $GetDefaultPriceList_sig,
		"docstring" => $GetDefaultPriceList_doc),
	"weberp.xmlrpc_GetDefaultLocation" => array(
		"function" => "xmlrpc_GetDefaultLocation",
		"signature" => $GetDefaultLocation_sig,
		"docstring" => $GetDefaultLocation_doc),
	"weberp.xmlrpc_GetReportsDirectory" => array(
		"function" => "xmlrpc_GetReportsDirectory",
		"signature" => $GetReportsDirectory_sig,
		"docstring" => $GetReportsDirectory_doc),
	"weberp.xmlrpc_CreatePOSDataFull" => array(
		"function" => "xmlrpc_CreatePOSDataFull",
		"signature" => $CreatePOSDataFull_sig,
		"docstring" => $CreatePOSDataFull_doc),
	"weberp.xmlrpc_DeletePOSData" => array(
		"function" => "xmlrpc_DeletePOSData",
		"signature" => $DeletePOSData_sig,
		"docstring" => $DeletePOSData_doc),
	"weberp.xmlrpc_GetStockCatProperty" => array(
		"function" => "xmlrpc_GetStockCatProperty",
		"signature" => $GetStockCatProperty_sig,
		"docstring" => $GetStockCatProperty_doc),
	"weberp.xmlrpc_GetErrorMessages" => array(
		"function" => "xmlrpc_GetErrorMessages",
		"signature" => $GetErrorMessages_sig,
		"docstring" => $GetErrorMessages_doc),
);

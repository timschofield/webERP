<?php
	function ob_file_callback($buffer)	{
		/* Empty handler function for unwanted output.
		 * Must be a better way of doing this, but at
		 * least it works */
	}
    /* Note api_php.php includes api */
	include 'api_php.php';

	include '../xmlrpc/lib/xmlrpc.inc';
	$xmlrpc_internalencoding='UTF-8';
	include '../xmlrpc/lib/xmlrpcs.inc';

	$Description = _('This function is used to login into the API methods for the specified the database.')
		 . '<p>' . _('NOTE: using this function means that the User Name and Password fields in the following functions are no longer required.  When calling those functions, leave the last two parameters off.') . '</p>';
	$Parameter[0]['name'] = _('Database Name');
	$Parameter[0]['description'] = _('The name of the database to use for the transactions to come. ');
	$Parameter[1]['name'] = _('User name');
	$Parameter[1]['description'] = _('A valid weberp username. This user should have security access to this data.');
	$Parameter[2]['name'] = _('User password');
	$Parameter[2]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('This function returns an integer. ').
		_('Zero means the function was successful. ').
		_('Otherwise an error code is returned. ');

	$Login_sig = array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcString, $xmlrpcString));
	$Login_doc = apiBuildDocHTML( $Description, $Parameter, $ReturnValue );

	function xmlrpc_Login($xmlrpcmsg) {
		ob_start('ob_file_callback');
		$rtn = new xmlrpcresp(php_xmlrpc_encode(LoginAPI($xmlrpcmsg->getParam(0)->scalarval(),
														$xmlrpcmsg->getParam(1)->scalarval(),
														$xmlrpcmsg->getParam(2)->scalarval())));
		ob_end_flush();
		return  $rtn;
	}


	unset($Description);
	unset($ReturnValue);

	$Description = _('This function is used to logout from the API methods. ')
			. _('It terminates the user\'s session thus freeing the server resources.' );
	$ReturnValue[0] = _('This function returns an integer. ')
			._('Zero means the function was successful. ')
			._('Otherwise an error code is returned. ');
	$Logout_sig = array(array($xmlrpcStruct));
	$Logout_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_Logout($xmlrpcmsg){
		ob_start('ob_file_callback');
		 $rtn = new xmlrpcresp( php_xmlrpc_encode(LogoutAPI()) );
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('This function is used to insert a new customer into the webERP database.');
	$Parameter[0]['name'] = _('Customer Details');
	$Parameter[0]['description'] = _('A set of key/value pairs where the key must be identical to the name of the field to be updated. ')
			._('The field names can be found ') . '<a href="../../Z_DescribeTable.php?table=debtorsmaster">' . _('here ') . '</a>'
			._('and are case sensitive. ')._('The values should be of the correct type, and the api will check them before updating the database. ')
			._('It is not necessary to include all the fields in this parameter, the database default value will be used if the field is not given.')
			 . '<p>' . _('If the Create Debtor Codes Automatically flag is set, then anything sent in the debtorno field will be ignored, and the debtorno field will be set automatically.');
	$Parameter[1]['name'] = _('User name');
	$Parameter[1]['description'] = _('A valid weberp username. This user should have security access  to this data.');
	$Parameter[2]['name'] = _('User password');
	$Parameter[2]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('This function returns an array of integers. ')
			._('If the first element is zero then the function was successful. ')
			._('Otherwise an array of error codes is returned and no insertion takes place. ');

/*E*/$InsertCustomer_sig = array(array($xmlrpcStruct,$xmlrpcStruct),
/*x*/						array($xmlrpcStruct,$xmlrpcStruct,$xmlrpcString,$xmlrpcString));
	$InsertCustomer_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_InsertCustomer($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 3)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(InsertCustomer(php_xmlrpc_decode($xmlrpcmsg->getParam( 0 )),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ))) );
/*x*/		} else {
/*e*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(InsertCustomer(php_xmlrpc_decode($xmlrpcmsg->getParam( 0 )), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('This function is used to insert a new customer branch into the webERP database.');
	$Parameter[0]['name'] = _('Branch Details');
	$Parameter[0]['description'] = _('A set of key/value pairs where the key must be identical to the name of the field to be updated. ')
			._('The field names can be found ') . '<a href="../../Z_DescribeTable.php?table=custbranch">' . _('here ') . '</a>'
			._('and are case sensitive. ')._('The values should be of the correct type, and the api will check them before updating the database. ')
			._('It is not necessary to include all the fields in this parameter, the database default value will be used if the field is not given.');
	$Parameter[1]['name'] = _('User name');
	$Parameter[1]['description'] = _('A valid weberp username. This user should have security access  to this data.');
	$Parameter[2]['name'] = _('User password');
	$Parameter[2]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('This function returns an array of integers. ')
			._('If the first element is zero then the function was successful. ')
			._('Otherwise an array of error codes is returned and no insertion takes place. ');

/*E*/$InsertBranch_sig = array(array($xmlrpcStruct,$xmlrpcStruct),
/*x*/					array($xmlrpcStruct,$xmlrpcStruct,$xmlrpcString,$xmlrpcString));
	$InsertBranch_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_InsertBranch($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 3) {
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(InsertBranch(php_xmlrpc_decode($xmlrpcmsg->getParam( 0 )),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ))) );
/*x*/		} else {
/*e*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(InsertBranch(php_xmlrpc_decode($xmlrpcmsg->getParam( 0 )), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('This function is used to modify a customer which is already setup in the webERP database.');
	$Parameter[0]['name'] = _('Customer Details');
	$Parameter[0]['description'] = _('A set of key/value pairs where the key must be identical to the name of the field to be updated. ')
			._('The field names can be found ') . '<a href="../../Z_DescribeTable.php?table=debtorsmaster">' . _('here ') . '</a>'
			._('and are case sensitive. ')._('The values should be of the correct type, and the api will check them before updating the database. ')
			._('It is not necessary to include all the fields in this parameter, the database default value will be used if the field is not given.')
			 . '<p>' . _('The debtorno must already exist in the weberp database.');
	$Parameter[1]['name'] = _('User name');
	$Parameter[1]['description'] = _('A valid weberp username. This user should have security access  to this data.');
	$Parameter[2]['name'] = _('User password');
	$Parameter[2]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('This function returns an array of integers. ')
			._('If the first element is zero then the function was successful. ')
			._('Otherwise an array of error codes is returned and no modification takes place. ');

/*E*/	$ModifyCustomer_sig = array(array($xmlrpcStruct,$xmlrpcStruct),
/*x*/					array($xmlrpcStruct,$xmlrpcStruct,$xmlrpcString,$xmlrpcString));
	$ModifyCustomer_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_ModifyCustomer($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 3) {
/*x*/		 	$rtn = new xmlrpcresp( php_xmlrpc_encode(ModifyCustomer(php_xmlrpc_decode($xmlrpcmsg->getParam( 0 )),
/*x*/				$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ))) );
/*x*/		} else {
/*e*/		 	$rtn = new xmlrpcresp( php_xmlrpc_encode(ModifyCustomer(php_xmlrpc_decode($xmlrpcmsg->getParam( 0 )), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('This function is used to modify a customer branch which is already setup in the webERP database.');
	$Parameter[0]['name'] = _('Branch Details');
	$Parameter[0]['description'] = _('A set of key/value pairs where the key must be identical to the name of the field to be updated. ')
			._('The field names can be found ') . '<a href="../../Z_DescribeTable.php?table=custbranch">' . _('here ') . '</a>'
			._('and are case sensitive. ')._('The values should be of the correct type, and the api will check them before updating the database. ')
			._('It is not necessary to include all the fields in this parameter, the database default value will be used if the field is not given.')
			 . '<p>' . _('The branchcode/debtorno combination must already exist in the weberp database.');
	$Parameter[1]['name'] = _('User name');
	$Parameter[1]['description'] = _('A valid weberp username. This user should have security access  to this data.');
	$Parameter[2]['name'] = _('User password');
	$Parameter[2]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('This function returns an array of integers. ')
			._('If the first element is zero then the function was successful. ')
			._('Otherwise an array of error codes is returned and no insertion takes place. ');

/*E*/$ModifyBranch_sig = array(array($xmlrpcStruct,$xmlrpcStruct),
/*x*/					array($xmlrpcStruct,$xmlrpcStruct,$xmlrpcString,$xmlrpcString));
	$ModifyBranch_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_ModifyBranch($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 3) {
/*x*/		  $rtn = new xmlrpcresp( php_xmlrpc_encode(ModifyBranch(php_xmlrpc_decode($xmlrpcmsg->getParam( 0 )),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ))) );
/*x*/		} else {
/*e*/		  $rtn = new xmlrpcresp( php_xmlrpc_encode(ModifyBranch(php_xmlrpc_decode($xmlrpcmsg->getParam( 0 )), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('This function is used to retrieve a list of the branch codes for the Debtor Number supplied.');
	$Parameter[0]['name'] = _('Debtor number');
	$Parameter[0]['description'] = _('This is a string value. It must be a valid debtor number that is already in the webERP database.');
	$Parameter[1]['name'] = _('User name');
	$Parameter[1]['description'] = _('A valid weberp username. This user should have security access  to this data.');
	$Parameter[2]['name'] = _('User password');
	$Parameter[2]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('If successful this function returns an array of branch codes, which may be strings or integers. ')
			._('If the first element is zero then the function was successful.') . '<p>'
			._('Otherwise an array of error codes is returned. ');

/*E*/	$GetCustomerBranchCodes_sig = array(array($xmlrpcStruct,$xmlrpcString),
/*x*/					array($xmlrpcStruct,$xmlrpcString,$xmlrpcString,$xmlrpcString));
	$GetCustomerBranchCodes_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_GetCustomerBranchCodes($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 3)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetCustomerBranchCodes($xmlrpcmsg->getParam( 0 )->scalarval(  ),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ) )));
/*x*/		} else {
/*e*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetCustomerBranchCodes($xmlrpcmsg->getParam( 0 )->scalarval(  ), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('This function is used to retrieve the details of a customer branch from the webERP database.');
	$Parameter[0]['name'] = _('Debtor number');
	$Parameter[0]['description'] = _('This is a string value. It must be a valid debtor number that is already in the webERP database.');
	$Parameter[1]['name'] = _('Branch Code');
	$Parameter[1]['description'] = _('This is a string value. It must be a valid branch code that is already in the webERP database, and associated with the debtorno in Parameter[0]');
	$Parameter[2]['name'] = _('User name');
	$Parameter[2]['description'] = _('A valid weberp username. This user should have security access  to this data.');
	$Parameter[3]['name'] = _('User password');
	$Parameter[3]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('If successful this function returns a set of key/value pairs containing the details of this branch. ')
			._('The key will be identical with field name from the ')
			 . '<a href="../../Z_DescribeTable.php?table=custbranch">' . _('custbranch table. ') . '</a>'
			._('All fields will be in the set regardless of whether the value was set.') . '<p>'
			._('Otherwise an array of error codes is returned. ');

/*E*/	$GetCustomerBranch_sig = array(array($xmlrpcStruct,$xmlrpcString,$xmlrpcString),
/*x*/					array($xmlrpcStruct,$xmlrpcString,$xmlrpcString,$xmlrpcString,$xmlrpcString));
	$GetCustomerBranch_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_GetCustomerBranch($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 4)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetCustomerBranch($xmlrpcmsg->getParam( 0 )->scalarval(  ),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ),
/*x*/					$xmlrpcmsg->getParam( 3 )->scalarval(  ))) );
/*x*/		} else {
		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetCustomerBranch($xmlrpcmsg->getParam( 0 )->scalarval(  ),
/*e*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('This function is used to retrieve the details of a customer from the webERP database.');
	$Parameter[0]['name'] = _('Debtor number');
	$Parameter[0]['description'] = _('This is a string value. It must be a valid debtor number that is already in the webERP database.');
	$Parameter[1]['name'] = _('User name');
	$Parameter[1]['description'] = _('A valid weberp username. This user should have security access  to this data.');
	$Parameter[2]['name'] = _('User password');
	$Parameter[2]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('If successful this function returns a set of key/value pairs containing the details of this customer. ')
			._('The key will be identical with field name from the debtorsmaster table. All fields will be in the set regardless of whether the value was set.') . '<p>'
			._('Otherwise an array of error codes is returned. ');

/*E*/	$GetCustomer_sig = array(array($xmlrpcStruct,$xmlrpcString),
/*x*/					array($xmlrpcStruct,$xmlrpcString,$xmlrpcString,$xmlrpcString));
	$GetCustomer_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_GetCustomer($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 3)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetCustomer($xmlrpcmsg->getParam( 0 )->scalarval(  ),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ))) );
/*x*/		} else {
/*e*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetCustomer($xmlrpcmsg->getParam( 0 )->scalarval(  ), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('This function is used to retrieve the details of a customer from the webERP database.');
	$Parameter[0]['name'] = _('Field Name');
	$Parameter[0]['description'] = _('The name of a database field to search on. ')
			._('The field names can be found ') . '<a href="../../Z_DescribeTable.php?table=debtorsmaster">' . _('here ') . '</a>'
			._('and are case sensitive. ');
	$Parameter[1]['name'] = _('Search Criteria');
	$Parameter[1]['description'] = _('A (partial) string to match in the above Field Name.');
	$Parameter[2]['name'] = _('User name');
	$Parameter[2]['description'] = _('A valid weberp username. This user should have security access  to this data.');
	$Parameter[3]['name'] = _('User password');
	$Parameter[3]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('This function returns an array of customer IDs, which may be integers or strings. ')
			._('If the first element is zero then the function was successful. ')
			._('Otherwise an array of error codes is returned and no insertion takes place. ');

/*E*/	$SearchCustomers_sig = array(array($xmlrpcStruct,$xmlrpcString,$xmlrpcString),
/*x*/					array($xmlrpcStruct,$xmlrpcString,$xmlrpcString,$xmlrpcString,$xmlrpcString));
	$SearchCustomers_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_SearchCustomers($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 4)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(SearchCustomers($xmlrpcmsg->getParam( 0 )->scalarval(  ),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ),
/*x*/					$xmlrpcmsg->getParam( 3 )->scalarval(  ))) );
/*x*/		} else {
		 $rtn = new xmlrpcresp( php_xmlrpc_encode(SearchCustomers($xmlrpcmsg->getParam( 0 )->scalarval(  ),
/*e*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('This function returns a list of currency abbreviations.');
	$Parameter[0]['name'] = _('User name');
	$Parameter[0]['description'] = _('A valid weberp username. This user should have security access  to this data.');
	$Parameter[1]['name'] = _('User password');
	$Parameter[1]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('This function returns an array of currency abbreviations. ')
			._('If the first element is zero then the function was successful. ')
			._('Otherwise an array of error codes is returned and no insertion takes place. ');

/*E*/$GetCurrencyList_sig = array(array($xmlrpcStruct),
/*x*/						array($xmlrpcStruct,$xmlrpcString,$xmlrpcString));
	$GetCurrencyList_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_GetCurrencyList($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/	if ($xmlrpcmsg->getNumParams() == 2)	{
/*x*/		$rtn = new xmlrpcresp( php_xmlrpc_encode(GetCurrencyList($xmlrpcmsg->getParam( 0 )->scalarval(  ), $xmlrpcmsg->getParam( 1 )->scalarval(  ))) );
/*x*/	} else {
/*e*/ 		$rtn = new xmlrpcresp( php_xmlrpc_encode(GetCurrencyList('','')));
/*x*/	}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('This function takes a currency abbreviation and returns details of that currency.');
	$Parameter[0]['name'] = _('Currency abbreviation');
	$Parameter[0]['description'] = _('A currency abbreviation as returned by the GetCurrencyList function.');
	$Parameter[1]['name'] = _('User name');
	$Parameter[1]['description'] = _('A valid weberp username. This user should have security access  to this data.');
	$Parameter[2]['name'] = _('User password');
	$Parameter[2]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('This function returns an array of currency details.');

/*E*/	$GetCurrencyDetails_sig = array(array($xmlrpcStruct,$xmlrpcString),
/*x*/					array($xmlrpcStruct,$xmlrpcString,$xmlrpcString,$xmlrpcString));
	$GetCurrencyDetails_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_GetCurrencyDetails($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 3)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetCurrencyDetails($xmlrpcmsg->getParam( 0 )->scalarval(  ),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ))) );
/*x*/		} else {
/*e*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetCurrencyDetails($xmlrpcmsg->getParam( 0 )->scalarval(  ), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('This function returns a list of sales type abbreviations.');
	$Parameter[0]['name'] = _('User name');
	$Parameter[0]['description'] = _('A valid weberp username. This user should have security access  to this data.');
	$Parameter[1]['name'] = _('User password');
	$Parameter[1]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('This function returns an array of sales type abbreviations. ')
			._('If the first element is zero then the function was successful. ')
			._('Otherwise an array of error codes is returned and no insertion takes place. ');

/*E*/	$GetSalesTypeList_sig = array(array($xmlrpcStruct),
/*x*/					array($xmlrpcStruct,$xmlrpcString,$xmlrpcString));
	$GetSalesTypeList_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_GetSalesTypeList($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 2)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetSalesTypeList($xmlrpcmsg->getParam( 0 )->scalarval(  ),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ))) );
/*x*/		} else {
/*e*/ $rtn = new xmlrpcresp( php_xmlrpc_encode(GetSalesTypeList( '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('This function takes a sales type abbreviation and returns details of that sales type.');
	$Parameter[0]['name'] = _('Sales type abbreviation');
	$Parameter[0]['description'] = _('A sales type abbreviation as returned by the GetSalesTypeList function.');
	$Parameter[1]['name'] = _('User name');
	$Parameter[1]['description'] = _('A valid weberp username. This user should have security access  to this data.');
	$Parameter[2]['name'] = _('User password');
	$Parameter[2]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('This function returns an array of sales type details.');

/*E*/	$GetSalesTypeDetails_sig = array(array($xmlrpcStruct,$xmlrpcString),
/*x*/					array($xmlrpcStruct,$xmlrpcString,$xmlrpcString,$xmlrpcString));
	$GetSalesTypeDetails_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_GetSalesTypeDetails($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 3)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetSalesTypeDetails($xmlrpcmsg->getParam( 0 )->scalarval(  ),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ))) );
/*x*/		} else {
/*e*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetSalesTypeDetails($xmlrpcmsg->getParam( 0 )->scalarval(  ), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('This function is used to insert sales type details into the webERP database.');
	$Parameter[0]['name'] = _('Sales Type Details');
	$Parameter[0]['description'] = _('A set of key/value pairs where the key must be identical to the name of the field to be updated. ')
			._('The field names can be found ') . '<a href="../../Z_DescribeTable.php?table=salestypes">' . _('here ') . '</a>'
			._('and are case sensitive. ')._('The values should be of the correct type, and the api will check them before updating the database. ')
			._('It is not necessary to include all the fields in this parameter, the database default value will be used if the field is not given.');
	$Parameter[1]['name'] = _('User name');
	$Parameter[1]['description'] = _('A valid weberp username. This user should have security access  to this data.');
	$Parameter[2]['name'] = _('User password');
	$Parameter[2]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('This function returns an array of integers. ')
			._('If the first element is zero then the function was successful. ')
			._('Otherwise an array of error codes is returned and no insertion takes place. ');

/*E*/	$InsertSalesType_sig = array(array($xmlrpcStruct,$xmlrpcStruct),
/*x*/					array($xmlrpcStruct,$xmlrpcStruct,$xmlrpcString,$xmlrpcString));
	$InsertSalesType_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_InsertSalesType($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 3)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(InsertSalesType(php_xmlrpc_decode($xmlrpcmsg->getParam( 0 )),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ))) );
/*x*/		} else {
/*e*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(InsertSalesType(php_xmlrpc_decode($xmlrpcmsg->getParam( 0 )), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('This function returns a list of hold reason codes.');
	$Parameter[0]['name'] = _('User name');
	$Parameter[0]['description'] = _('A valid weberp username. This user should have security access  to this data.');
	$Parameter[1]['name'] = _('User password');
	$Parameter[1]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('This function returns an array of hold reason codes.');

/*E*/	$GetHoldReasonList_sig = array(array($xmlrpcStruct),
/*x*/					array($xmlrpcStruct,$xmlrpcString,$xmlrpcString));
	$GetHoldReasonList_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_GetHoldReasonList($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 2)	{
/*x*/			$rtn = new xmlrpcresp( php_xmlrpc_encode(GetHoldReasonList($xmlrpcmsg->getParam( 0 )->scalarval(  ), $xmlrpcmsg->getParam( 1 )->scalarval(  ))) );
/*x*/		} else {
				$rtn = new xmlrpcresp( php_xmlrpc_encode(GetHoldReasonList( '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('This function takes a hold reason code and returns details of that hold reason.');
	$Parameter[0]['name'] = _('Hold reason code');
	$Parameter[0]['description'] = _('A hold reason abbreviation as returned by the GetHoldReasonList function.');
	$Parameter[1]['name'] = _('User name');
	$Parameter[1]['description'] = _('A valid weberp username. This user should have security access  to this data.');
	$Parameter[2]['name'] = _('User password');
	$Parameter[2]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('This function returns an array of hold reason details.');

/*E*/	$GetHoldReasonDetails_sig = array(array($xmlrpcStruct,$xmlrpcString),
/*x*/					array($xmlrpcStruct,$xmlrpcString,$xmlrpcString,$xmlrpcString));
	$GetHoldReasonDetails_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_GetHoldReasonDetails($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 3) {
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetHoldReasonDetails($xmlrpcmsg->getParam( 0 )->scalarval(  ),	$xmlrpcmsg->getParam( 1 )->scalarval(  ),$xmlrpcmsg->getParam( 2 )->scalarval(  ))) );
/*x*/		} else {
/*e*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetHoldReasonDetails($xmlrpcmsg->getParam( 0 )->scalarval(  ), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('This function returns a list of payment terms abbreviations.');
	$Parameter[0]['name'] = _('User name');
	$Parameter[0]['description'] = _('A valid weberp username. This user should have security access  to this data.');
	$Parameter[1]['name'] = _('User password');
	$Parameter[1]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('This function returns an array of payment terms abbreviations.');

/*E*/	$GetPaymentTermsList_sig = array(array($xmlrpcStruct),
/*x*/					array($xmlrpcStruct,$xmlrpcString,$xmlrpcString));
	$GetPaymentTermsList_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_GetPaymentTermsList($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 2)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetPaymentTermsList($xmlrpcmsg->getParam( 0 )->scalarval(  ),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ))) );
/*x*/		} else {
/*e*/ $rtn = new xmlrpcresp( php_xmlrpc_encode(GetPaymentTermsList( '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('This function takes a payment terms abbreviation and returns details of that payment terms type.');
	$Parameter[0]['name'] = _('Hold reason code');
	$Parameter[0]['description'] = _('A payment terms abbreviation as returned by the GetPaymentTermsList function.');
	$Parameter[1]['name'] = _('User name');
	$Parameter[1]['description'] = _('A valid weberp username. This user should have security access to this data.');
	$Parameter[2]['name'] = _('User password');
	$Parameter[2]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('This function returns an array of payment terms details.');

/*E*/	$GetPaymentTermsDetails_sig = array(array($xmlrpcStruct,$xmlrpcString),
/*x*/					array($xmlrpcStruct,$xmlrpcString,$xmlrpcString,$xmlrpcString));
	$GetPaymentTermsDetails_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_GetPaymentTermsDetails($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 3)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetPaymentTermsDetails($xmlrpcmsg->getParam( 0 )->scalarval(  ),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ))) );
/*x*/		} else {
/*e*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetPaymentTermsDetails($xmlrpcmsg->getParam( 0 )->scalarval(  ), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

    unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('This function returns a list of payment method codes.');
	$Parameter[0]['name'] = _('User name');
	$Parameter[0]['description'] = _('A valid weberp username. This user should have security access  to this data.');
	$Parameter[1]['name'] = _('User password');
	$Parameter[1]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('This function returns an array of payment method codes.');

/*E*/$GetPaymentMethodsList_sig = array(array($xmlrpcStruct),
/*x*/					array($xmlrpcStruct,$xmlrpcString,$xmlrpcString));
	$GetPaymentMethodsList_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

    function  xmlrpc_GetPaymentMethodsList($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 2) {
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetPaymentMethodsList($xmlrpcmsg->getParam( 0 )->scalarval(  ), $xmlrpcmsg->getParam( 1 )->scalarval(  ))) );
/*x*/		} else {
/*e*/ $rtn = new xmlrpcresp( php_xmlrpc_encode(GetPaymentMethodsList( '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('This function takes a payment method code and returns details of that payment method.');
	$Parameter[0]['name'] = _('Payment method code');
	$Parameter[0]['description'] = _('A payment method code as returned by the GetPaymentMethodsList function.');
	$Parameter[1]['name'] = _('User name');
	$Parameter[1]['description'] = _('A valid weberp username. This user should have security access to this data.');
	$Parameter[2]['name'] = _('User password');
	$Parameter[2]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('This function returns an array of payment terms details.');

/*E*/$GetPaymentMethodDetails_sig = array(array($xmlrpcStruct,$xmlrpcString),
/*x*/					array($xmlrpcStruct,$xmlrpcString,$xmlrpcString,$xmlrpcString));
	$GetPaymentMethodDetails_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_GetPaymentMethodDetails($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 3)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetPaymentMethodDetails($xmlrpcmsg->getParam( 0 )->scalarval(  ),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ))) );
/*x*/		} else {
/*e*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetPaymentMethodDetails($xmlrpcmsg->getParam( 0 )->scalarval(  ), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}


	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('This function inserts a new stock item into webERP, including updating the locstock table.');
	$Parameter[0]['name'] = _('Stock Item Details');
	$Parameter[0]['description'] = _('Key/value pairs of data to insert. The key must be identical with the database field name.');
	$Parameter[1]['name'] = _('User name');
	$Parameter[1]['description'] = _('A valid weberp username. This user should have security access to this data.');
	$Parameter[2]['name'] = _('User password');
	$Parameter[2]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('This function returns an array of integers. ')
			._('If the first element is zero then the function was successful. ')
			._('Otherwise an array of error codes is returned and no insertion takes place. ');

/*E*/	$InsertStockItem_sig = array(array($xmlrpcStruct,$xmlrpcStruct),
/*x*/					array($xmlrpcStruct,$xmlrpcStruct,$xmlrpcString,$xmlrpcString));
	$InsertStockItem_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_InsertStockItem($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 3)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(InsertStockItem(php_xmlrpc_decode($xmlrpcmsg->getParam( 0 )),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ))) );
/*x*/		} else {
/*e*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(InsertStockItem(php_xmlrpc_decode($xmlrpcmsg->getParam( 0 )), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('This function modifies a stock item that already exists in webERP.');
	$Parameter[0]['name'] = _('Stock Item Details');
	$Parameter[0]['description'] = _('Key/value pairs of data to modify.');
	$Parameter[1]['name'] = _('User name');
	$Parameter[1]['description'] = _('A valid weberp username. This user should have security access to this data.');
	$Parameter[2]['name'] = _('User password');
	$Parameter[2]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('This function returns an array of integers. ')
			._('If the first element is zero then the function was successful. ')
			._('Otherwise an array of error codes is returned and no modification takes place. ');

/*E*/	$ModifyStockItem_sig = array(array($xmlrpcStruct,$xmlrpcStruct),
/*x*/					array($xmlrpcStruct,$xmlrpcStruct,$xmlrpcString,$xmlrpcString));
	$ModifyStockItem_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_ModifyStockItem($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 3)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(ModifyStockItem(php_xmlrpc_decode($xmlrpcmsg->getParam( 0 )),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ))) );
/*x*/		} else {
/*e*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(ModifyStockItem(php_xmlrpc_decode($xmlrpcmsg->getParam( 0 )), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('This function takes a stock item code and returns an array of key/value pairs.').
				_('The keys represent the database field names, and the values are the value of that field.');
	$Parameter[0]['name'] = _('Stock ID');
	$Parameter[0]['description'] = _('The StockID code to identify the item in the database.');
	$Parameter[1]['name'] = _('User name');
	$Parameter[1]['description'] = _('A valid weberp username. This user should have security access to this data.');
	$Parameter[2]['name'] = _('User password');
	$Parameter[2]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('If successful this function returns a set of key/value pairs containing the details of this stock item. ')
			._('The key will be identical with field name from the stockmaster table. All fields will be in the set regardless of whether the value was set.') . '<p>'
			._('Otherwise an array of error codes is returned. ');

/*E*/	$GetStockItem_sig = array(array($xmlrpcStruct,$xmlrpcString),
/*x*/					array($xmlrpcStruct,$xmlrpcString,$xmlrpcString,$xmlrpcString));
	$GetStockItem_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_GetStockItem($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 3)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetStockItem($xmlrpcmsg->getParam( 0 )->scalarval(  ),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ))) );
/*x*/		} else {
/*e*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetStockItem($xmlrpcmsg->getParam( 0 )->scalarval(  ), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('This function searches the stockmaster table and returns an array of stock items matching that criteria.');
	$Parameter[0]['name'] = _('Field Name');
	$Parameter[0]['description'] = _('The field name to search on.');
	$Parameter[1]['name'] = _('Match Criteria');
	$Parameter[1]['description'] = _('The SQL search pattern to select items in the database.');
	$Parameter[2]['name'] = _('User name');
	$Parameter[2]['description'] = _('A valid weberp username. This user should have security access to this data.');
	$Parameter[3]['name'] = _('User password');
	$Parameter[3]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0]=_('Returns an array of stock codes matching the criteria send, or an array of error codes');

/*E*/	$SearchStockItems_sig = array(array($xmlrpcStruct,$xmlrpcString,$xmlrpcString),
/*x*/					array($xmlrpcStruct,$xmlrpcString,$xmlrpcString,$xmlrpcString,$xmlrpcString));
	$SearchStockItems_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_SearchStockItems($xmlrpcmsg){
		//ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 4)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(SearchStockItems($xmlrpcmsg->getParam( 0 )->scalarval(  ),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ),
/*x*/					$xmlrpcmsg->getParam( 3 )->scalarval(  ))) );
/*x*/		} else {
		 $rtn = new xmlrpcresp( php_xmlrpc_encode(SearchStockItems($xmlrpcmsg->getParam( 0 )->scalarval(  ),
/*e*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ), '', '')));
/*x*/		}
		//ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = 'This function returns the stock balance for the given stockid.';
	$Parameter[0]['name'] = _('Stock ID');
	$Parameter[0]['description'] = _('A string field containing a valid stockid that must already be setup in the stockmaster table. The api will check this before making the enquiry.');
	$Parameter[1]['name'] = _('User name');
	$Parameter[1]['description'] = _('A valid weberp username. This user should have security access  to this data.');
	$Parameter[2]['name'] = _('User password');
	$Parameter[2]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('This function returns an array of stock quantities by location for this stock item. ');

/*E*/	$GetStockBalance_sig = array(array($xmlrpcStruct,$xmlrpcString),
/*x*/					array($xmlrpcStruct,$xmlrpcString,$xmlrpcString,$xmlrpcString));
	$GetStockBalance_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_GetStockBalance($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 3)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetStockBalance($xmlrpcmsg->getParam( 0 )->scalarval(  ),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ))) );
/*x*/		} else {
/*e*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetStockBalance($xmlrpcmsg->getParam( 0 )->scalarval(  ), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = 'This function returns the reorder levels by location.';
	$Parameter[0]['name'] = _('Stock ID');
	$Parameter[0]['description'] = _('A string field containing a valid stockid that must already be setup in the stockmaster table. The api will check this before making the enquiry.');
	$Parameter[1]['name'] = _('User name');
	$Parameter[1]['description'] = _('A valid weberp username. This user should have security access  to this data.');
	$Parameter[2]['name'] = _('User password');
	$Parameter[2]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('This function returns an array of stock reorder levels by location for this stock item.');

/*E*/	$GetStockReorderLevel_sig = array(array($xmlrpcStruct,$xmlrpcString),
/*x*/					array($xmlrpcStruct,$xmlrpcString,$xmlrpcString,$xmlrpcString));
	$GetStockReorderLevel_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_GetStockReorderLevel($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 3)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetStockReorderLevel($xmlrpcmsg->getParam( 0 )->scalarval(  ),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ))) );
/*x*/		} else {
/*e*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetStockReorderLevel($xmlrpcmsg->getParam( 0 )->scalarval(  ), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = 'This function sets the reorder level for the given stockid in the given location.';
	$Parameter[0]['name'] = _('Stock ID');
	$Parameter[0]['description'] = _('A string field containing a valid stockid that must already be setup in the stockmaster table. The api will check this before making the enquiry.');
	$Parameter[1]['name'] = _('Location Code');
	$Parameter[1]['description'] = _('A string field containing a valid location code that must already be setup in the locations table. The api will check this before making the enquiry.');
	$Parameter[2]['name'] = _('Reorder level');
	$Parameter[2]['description'] = _('A numeric field containing the reorder level for this stockid/location combination.');
	$Parameter[3]['name'] = _('User name');
	$Parameter[3]['description'] = _('A valid weberp username. This user should have security access  to this data.');
	$Parameter[4]['name'] = _('User password');
	$Parameter[4]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('This function returns zero if the transaction was successful or an array of error codes if not. ');

/*E*/	$SetStockReorderLevel_sig = array(array($xmlrpcStruct,$xmlrpcString,$xmlrpcString,$xmlrpcString),
/*x*/					array($xmlrpcStruct,$xmlrpcString,$xmlrpcString,$xmlrpcString,$xmlrpcString,$xmlrpcString));
	$SetStockReorderLevel_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_SetStockReorderLevel($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 5)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(SetStockReorderLevel($xmlrpcmsg->getParam( 0 )->scalarval(  ),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ),
/*x*/					$xmlrpcmsg->getParam( 3 )->scalarval(  ),
/*x*/						$xmlrpcmsg->getParam( 4 )->scalarval(  ))) );
/*x*/		} else {
		 $rtn = new xmlrpcresp( php_xmlrpc_encode(SetStockReorderLevel($xmlrpcmsg->getParam( 0 )->scalarval(  ),
			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*e*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('This function returns the quantity allocated of the stock item id sent as a parameter.');
	$Parameter[0]['name'] = _('Stock ID');
	$Parameter[0]['description'] = _('The StockID code to identify items ordered but not yet shipped.');
	$Parameter[1]['name'] = _('User name');
	$Parameter[1]['description'] = _('A valid weberp username. This user should have security access to this data.');
	$Parameter[2]['name'] = _('User password');
	$Parameter[2]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('This function returns an integer value of the quantity allocated or an array of error codes if not. ');

/*E*/	$GetAllocatedStock_sig = array(array($xmlrpcStruct,$xmlrpcString),
/*x*/					array($xmlrpcStruct,$xmlrpcString,$xmlrpcString,$xmlrpcString));
	$GetAllocatedStock_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_GetAllocatedStock($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 3)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetAllocatedStock($xmlrpcmsg->getParam( 0 )->scalarval(  ),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ))) );
/*x*/		} else {
/*e*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetAllocatedStock($xmlrpcmsg->getParam( 0 )->scalarval(  ), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('This function takes a stock ID and returns the quantity of this stock that is currently on outstanding purchase orders.');
	$Parameter[0]['name'] = _('Stock ID');
	$Parameter[0]['description'] = _('The StockID code to identify items in the database on order, but not yet received.');
	$Parameter[1]['name'] = _('User name');
	$Parameter[1]['description'] = _('A valid weberp username. This user should have security access to this data.');
	$Parameter[2]['name'] = _('User password');
	$Parameter[2]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('This function returns an integer value of the quantity on order or an array of error codes if not.');

/*E*/	$GetOrderedStock_sig = array(array($xmlrpcStruct,$xmlrpcString),
/*x*/					array($xmlrpcStruct,$xmlrpcString,$xmlrpcString,$xmlrpcString));
	$GetOrderedStock_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_GetOrderedStock($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 3)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetOrderedStock($xmlrpcmsg->getParam( 0 )->scalarval(  ),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ))) );
/*x*/		} else {
/*e*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetOrderedStock($xmlrpcmsg->getParam( 0 )->scalarval(  ), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('This function sets the sales price for a stock ID in the sales type and currency passed to the function');
	$Parameter[0]['name'] = _('Stock ID');
	$Parameter[0]['description'] = _('The StockID code to identify the item in the database.');
	$Parameter[1]['name'] = _('Currency Code');
	$Parameter[1]['description'] = _('The currency involved.');
	$Parameter[2]['name'] = _('Sales Type');
	$Parameter[2]['description'] = _('The sales type to identify the item in the database.');
	$Parameter[3]['name'] = _('Price');
	$Parameter[3]['description'] = _('The price to apply to this item.');
	$Parameter[4]['name'] = _('User name');
	$Parameter[4]['description'] = _('A valid weberp username. This user should have security access to this data.');
	$Parameter[5]['name'] = _('User password');
	$Parameter[5]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue = _('Returns a zero if successful or else an array of error codes');

/*E*/	$SetStockPrice_sig = array(array($xmlrpcStruct,$xmlrpcString,$xmlrpcString,$xmlrpcString,$xmlrpcString),
/*x*/					array($xmlrpcStruct,$xmlrpcString,$xmlrpcString,$xmlrpcString,$xmlrpcString,$xmlrpcString,$xmlrpcString));
	$SetStockPrice_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_SetStockPrice($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 6)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(SetStockPrice($xmlrpcmsg->getParam( 0 )->scalarval(  ),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ),
/*x*/					$xmlrpcmsg->getParam( 3 )->scalarval(  ),
/*x*/						$xmlrpcmsg->getParam( 4 )->scalarval(  ),
/*x*/							$xmlrpcmsg->getParam( 5 )->scalarval(  ))) );
/*x*/		} else {
		 $rtn = new xmlrpcresp( php_xmlrpc_encode(SetStockPrice($xmlrpcmsg->getParam( 0 )->scalarval(  ),
			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
				$xmlrpcmsg->getParam( 2 )->scalarval(  ),
/*e*/					$xmlrpcmsg->getParam( 3 )->scalarval(  ), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('This function gets the sales price for a stock ID in the sales type and currency passed to the function');
	$Parameter[0]['name'] = _('Stock ID');
	$Parameter[0]['description'] = _('The StockID code to identify the item in the database.');
	$Parameter[1]['name'] = _('Currency Code');
	$Parameter[1]['description'] = _('The currency involved.');
	$Parameter[2]['name'] = _('Sales Type');
	$Parameter[2]['description'] = _('The sales type of the item in the database.');
	$Parameter[3]['name'] = _('User name');
	$Parameter[3]['description'] = _('A valid weberp username. This user should have security access to this data.');
	$Parameter[4]['name'] = _('User password');
	$Parameter[4]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue = _('Returns the sales price for he stock item whose ID is passed in the function');

/*E*/	$GetStockPrice_sig = array(array($xmlrpcStruct,$xmlrpcString,$xmlrpcString,$xmlrpcString),
/*x*/					array($xmlrpcStruct,$xmlrpcString,$xmlrpcString,$xmlrpcString,$xmlrpcString,$xmlrpcString));
	$GetStockPrice_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_GetStockPrice($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 5)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetStockPrice($xmlrpcmsg->getParam( 0 )->scalarval(  ),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ),
/*x*/					$xmlrpcmsg->getParam( 3 )->scalarval(  ),
/*x*/						$xmlrpcmsg->getParam( 4 )->scalarval(  ))) );
/*x*/		} else { //only 3 parameters if login already in session
		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetStockPrice($xmlrpcmsg->getParam( 0 )->scalarval(  ),
			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*e*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('Creates a customer receipt from the details passed to the method as an associative array');
	$Parameter[0]['name'] = _('Receipt Details');
	$Parameter[0]['description'] = _('An associative array describing the customer receipt with the following fields: debtorno - the customer code; trandate - the date of the receipt in Y-m-d format; amountfx - the amount in FX; paymentmethod - the payment method of the receipt e.g. cash/EFTPOS/credit card; bankaccount - the webERP bank account to use for the transaction, reference - the reference to record against the webERP receipt transaction');
	$Parameter[1]['name'] = _('User name');
	$Parameter[1]['description'] = _('A valid weberp username. This user should have security access to this data.');
	$Parameter[2]['name'] = _('User password');
	$Parameter[2]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('This function returns an array of integers. ')
			._('If the first element is zero then the function was successful, and the second element is the receipt number. ')
			._('Otherwise an array of error codes is returned and no insertion takes place. ');

/*E*/$InsertDebtorReceipt_sig = array(array($xmlrpcStruct,$xmlrpcStruct),
/*x*/					array($xmlrpcStruct,$xmlrpcStruct,$xmlrpcString,$xmlrpcString));
	$InsertDebtorReceipt_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_InsertDebtorReceipt($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/	if ($xmlrpcmsg->getNumParams() == 3) {
/*x*/		$rtn = new xmlrpcresp( php_xmlrpc_encode(InsertDebtorReceipt(php_xmlrpc_decode($xmlrpcmsg->getParam( 0 )), $xmlrpcmsg->getParam( 1 )->scalarval(  ),$xmlrpcmsg->getParam( 2 )->scalarval(  ))) );
/*x*/	} else {
/*e*/		$rtn = new xmlrpcresp( php_xmlrpc_encode(InsertDebtorReceipt(php_xmlrpc_decode($xmlrpcmsg->getParam( 0 )), '', '')));
/*x*/	}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('Allocates a debtor receipt or credit to a debtor invoice. Using the customerref field to match up which invoice to allocate to');
	$Parameter[0]['name'] = _('Allocation Details');
	$Parameter[0]['description'] = _('An associative array describing the customer, the transaction being allocated from, it\'s transaction type 12 for a receipt or 11 for a credit note, the webERP transaction number, the customer ref that is to be searched for in invoices to match to. The fields are: debtorno, type, transno, customerref');
	$Parameter[1]['name'] = _('User name');
	$Parameter[1]['description'] = _('A valid weberp username. This user should have security access to this data.');
	$Parameter[2]['name'] = _('User password');
	$Parameter[2]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('This function returns an array of integers. ')
			._('If the first element is zero then the function was successful.')
			._('Otherwise an array of error codes is returned and no insertion takes place. ');

/*E*/$AllocateTrans_sig = array(array($xmlrpcStruct,$xmlrpcStruct),
/*x*/					array($xmlrpcStruct,$xmlrpcStruct,$xmlrpcString,$xmlrpcString));
	$AllocateTrans_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_AllocateTrans($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/	if ($xmlrpcmsg->getNumParams() == 3) {
/*x*/		$rtn = new xmlrpcresp( php_xmlrpc_encode(AllocateTrans(php_xmlrpc_decode($xmlrpcmsg->getParam( 0 )), $xmlrpcmsg->getParam( 1 )->scalarval(  ),$xmlrpcmsg->getParam( 2 )->scalarval(  ))) );
/*x*/	} else {
/*e*/		$rtn = new xmlrpcresp( php_xmlrpc_encode(AllocateTrans(php_xmlrpc_decode($xmlrpcmsg->getParam( 0 )), '', '')));
/*x*/	}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('Creates a credit note from header details associative array and line items. This function implements most of a webERP credit note with the exception that it cannot handle serialised or lot/batch controlled items. All the necessary updates and inserts are handled for stock quantities returned, taxes, sales analysis, stock movements, sales and cost of sales journals');
	$Parameter[0]['name'] = _('Credit Note Header Details');
	$Parameter[0]['description'] = _('An associative array describing the credit note header with the fields debtorno, branchcode, trandate, tpe, fromstkloc, customerref, shipvia');
	$Parameter[1]['name'] = _('Credit note line items');
	$Parameter[1]['description'] = _('The lines of stock being returned on this credit note. Only stock returns can be dealt with using this API method. This is an array of associative arrays containing the fields, stockid, price, qty, discountpercent for the items returned');
	$Parameter[2]['name'] = _('User name');
	$Parameter[2]['description'] = _('A valid weberp username. This user should have security access to this data.');
	$Parameter[3]['name'] = _('User password');
	$Parameter[3]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('This function returns an array of integers. ')
			._('If the first element is zero then the function was successful, and the second element is the credit note number. ')
			._('Otherwise an array of error codes is returned and no insertion takes place. ');

/*E*/$CreateCreditNote_sig = array(array($xmlrpcStruct,$xmlrpcStruct),
/*x*/					array($xmlrpcStruct,$xmlrpcStruct,$xmlrpcArray,$xmlrpcString,$xmlrpcString));
	$CreateCreditNote_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_CreateCreditNote($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/	if ($xmlrpcmsg->getNumParams() == 4) {
/*x*/		$rtn = new xmlrpcresp( php_xmlrpc_encode(CreateCreditNote(php_xmlrpc_decode($xmlrpcmsg->getParam( 0 )), php_xmlrpc_decode($xmlrpcmsg->getParam( 1 )), $xmlrpcmsg->getParam( 2 )->scalarval(  ),$xmlrpcmsg->getParam( 3 )->scalarval(  ))) );
/*x*/	} else {
/*e*/		$rtn = new xmlrpcresp( php_xmlrpc_encode(CreateCreditNote(php_xmlrpc_decode($xmlrpcmsg->getParam( 0 )),php_xmlrpc_decode($xmlrpcmsg->getParam( 1 )), '', '')));
/*x*/	}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('Inserts a sales invoice into the debtortrans table and does the relevant GL entries. Note that this function does not do the tax entries, insert stock movements, update the stock quanties, sales analysis data or do any cost of sales gl journals. Caution is advised in using this function. To create a full webERP invoice with all tables updated use the InvoiceSalesOrder function.');
	$Parameter[0]['name'] = _('Invoice Details');
	$Parameter[0]['description'] = _('An array of index/value items describing the invoice.')
			._('The field names can be found ') . '<a href="../../Z_DescribeTable.php?table=debtortrans">' . _('here ') . '</a>'
			._('and are case sensitive. ')._('The values should be of the correct type, and the api will check them before updating the database. ')
			._('The transno key is generated by this call, and if a value is supplied, it will be ignored. ')
			._('Two additional fields are required. "partcode" needs to be a genuine part number, though it appears to serve no real purpose. ')
			._('"salesarea" also is required, though again it appears to serve no useful purpose. ')
			._('It is not necessary to include all the fields in this parameter, the database default value will be used if the field is not given.');
	$Parameter[1]['name'] = _('User name');
	$Parameter[1]['description'] = _('A valid weberp username. This user should have security access to this data.');
	$Parameter[2]['name'] = _('User password');
	$Parameter[2]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('This function returns an array of integers. ')
			._('If the first element is zero then the function was successful, and the second element is the invoice number. ')
			._('Otherwise an array of error codes is returned and no insertion takes place. ');

/*E*/	$InsertSalesInvoice_sig = array(array($xmlrpcStruct,$xmlrpcStruct),
/*x*/					array($xmlrpcStruct,$xmlrpcStruct,$xmlrpcString,$xmlrpcString));
	$InsertSalesInvoice_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_InsertSalesInvoice($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 3)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(InsertSalesInvoice(php_xmlrpc_decode($xmlrpcmsg->getParam( 0 )),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ))) );
/*x*/		} else {
/*e*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(InsertSalesInvoice(php_xmlrpc_decode($xmlrpcmsg->getParam( 0 )), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);
	$ReturnValue = _('Return Value Descriptions go here');
	$Description = _('This function is used to insert a new Sales Credit to the webERP database. Note that this function does not implement a webERP credit note in full and caution is advised in using this function. It does not handle tax at all, it does not add stockmovements, it does not update stock for any quantity returned or update sales analysis. To create a credit note using webERP logic use the CreateCreditNote function');
	$Parameter[0]['name'] = _('Credit Details');
	$Parameter[0]['description'] = _('An array of index/value items describing the credit.  All values must be negative.');
	$Parameter[1]['name'] = _('User name');
	$Parameter[1]['description'] = _('A valid weberp username. This user should have security access to this data.');
	$Parameter[2]['name'] = _('User password');
	$Parameter[2]['description'] = _('The weberp password associated with this user name. ');

/*E*/	$InsertSalesCredit_sig = array(array($xmlrpcStruct,$xmlrpcStruct),
/*x*/					array($xmlrpcStruct,$xmlrpcStruct,$xmlrpcString,$xmlrpcString));
	$InsertSalesCredit_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_InsertSalesCredit($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 3)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(InsertSalesCedit(php_xmlrpc_decode($xmlrpcmsg->getParam( 0 )),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ))) );
/*x*/		} else {
/*e*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(InsertSalesCedit(php_xmlrpc_decode($xmlrpcmsg->getParam( 0 )), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = 'This function is used to start a new sales order.';
	$Parameter[0]['name'] = _('Insert Sales Order Header');
	$Parameter[0]['description'] = _('A set of key/value pairs where the key must be identical to the name of the field to be updated. ')
			._('The field names can be found ') . '<a href="../../Z_DescribeTable.php?table=salesorders">' . _('here ') . '</a>'
			._('and are case sensitive. ')._('The values should be of the correct type, and the api will check them before updating the database. ')
			._('The orderno key is generated by this call, and if a value is supplied, it will be ignored. ')
			._('It is not necessary to include all the fields in this parameter, the database default value will be used if the field is not given.');
	$Parameter[1]['name'] = _('User name');
	$Parameter[1]['description'] = _('A valid weberp username. This user should have security access  to this data.');
	$Parameter[2]['name'] = _('User password');
	$Parameter[2]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('If successful this function returns a two element array; the first element is 0 for success or an error code, while the second element is the order number.');

/*E*/	$InsertSalesOrderHeader_sig = array(array($xmlrpcStruct,$xmlrpcStruct),
/*x*/					array($xmlrpcStruct,$xmlrpcStruct,$xmlrpcString,$xmlrpcString));
	$InsertSalesOrderHeader_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_InsertSalesOrderHeader($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 3) {
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(InsertSalesOrderHeader(php_xmlrpc_decode($xmlrpcmsg->getParam( 0 )),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ))) );
/*x*/		} else {
/*e*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(InsertSalesOrderHeader(php_xmlrpc_decode($xmlrpcmsg->getParam( 0 )), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = 'This function is used to invoice a sales order for the full quantity on the order assuming it is all dispatched. NB It does not deal with serialised/controlled items.';
	$Parameter[0]['name'] = _('Sales Order to invoice');
	$Parameter[0]['description'] = _('An integer representing the webERP sales order number');
	$Parameter[1]['name'] = _('User name');
	$Parameter[1]['description'] = _('A valid weberp username. This user should have security access  to this data.');
	$Parameter[2]['name'] = _('User password');
	$Parameter[2]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('If successful this function returns a two element array; the first element is 0 for success or an error code, while the second element is the invoice number.');

/*E*/$InvoiceSalesOrder_sig = array(array($xmlrpcStruct,$xmlrpcStruct),
/*x*/					array($xmlrpcStruct,$xmlrpcInt,$xmlrpcString,$xmlrpcString));
	$InvoiceSalesOrder_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_InvoiceSalesOrder($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 3) {
/*x*/			$rtn = new xmlrpcresp( php_xmlrpc_encode(InvoiceSalesOrder($xmlrpcmsg->getParam( 0 )->scalarval(  ), $xmlrpcmsg->getParam( 1 )->scalarval(  ), $xmlrpcmsg->getParam( 2 )->scalarval(  ))) );
			} else { //do it with the current login
/*e*/			$rtn = new xmlrpcresp( php_xmlrpc_encode(InvoiceSalesOrder($xmlrpcmsg->getParam( 0 )->scalarval(  ), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);



	$Description = 'This function is used to modify the header details of a sales order';
	$Parameter[0]['name'] = _('Modify Sales Order Header Details');
	$Parameter[0]['description'] = _('A set of key/value pairs where the key must be identical to the name of the field to be updated. ')
			._('The field names can be found ') . '<a href="../../Z_DescribeTable.php?table=salesorders">' . _('here ') . '</a>'
			._('and are case sensitive. ')._('The values should be of the correct type, and the api will check them before updating the database. ')
			._('It is not necessary to include all the fields in this parameter, the database default value will be used if the field is not given.');
	$Parameter[1]['name'] = _('User name');
	$Parameter[1]['description'] = _('A valid weberp username. This user should have security access  to this data.');
	$Parameter[2]['name'] = _('User password');
	$Parameter[2]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('If successful this function returns a single element array with the value 0; otherwise, it contains all error codes encountered during the update.');

/*E*/	$ModifySalesOrderHeader_sig = array(array($xmlrpcStruct,$xmlrpcStruct),
/*x*/					array($xmlrpcStruct,$xmlrpcStruct,$xmlrpcString,$xmlrpcString));
	$ModifySalesOrderHeader_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_ModifySalesOrderHeader($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 3)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(ModifySalesOrderHeader(php_xmlrpc_decode($xmlrpcmsg->getParam( 0 )),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ))) );
/*x*/		} else {
/*e*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(ModifySalesOrderHeader(php_xmlrpc_decode($xmlrpcmsg->getParam( 0 )), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = 'This function is used to add line items to a sales order.';
	$Parameter[0]['name'] = _('Insert Sales Order Line');
	$Parameter[0]['description'] = _('A set of key/value pairs where the key must be identical to the name of the field to be updated. ')
			._('The field names can be found ') . '<a href="../../Z_DescribeTable.php?table=salesorderdetails">' . _('here ') . '</a>'
			._('and are case sensitive. ')._('The values should be of the correct type, and the api will check them before updating the database. ')
			._('The orderno key must be one of these values. ')
			._('It is not necessary to include all the fields in this parameter, the database default value will be used if the field is not given.');
	$Parameter[1]['name'] = _('User name');
	$Parameter[1]['description'] = _('A valid weberp username. This user should have security access  to this data.');
	$Parameter[2]['name'] = _('User password');
	$Parameter[2]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('This function returns an array; the first element is 0 for success; otherwise the array contains a list of all errors encountered.');

/*E*/	$InsertSalesOrderLine_sig = array(array($xmlrpcStruct,$xmlrpcStruct),
/*x*/					array($xmlrpcStruct,$xmlrpcStruct,$xmlrpcString,$xmlrpcString));
	$InsertSalesOrderLine_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_InsertSalesOrderLine($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 3)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(InsertSalesOrderLine(php_xmlrpc_decode($xmlrpcmsg->getParam( 0 )),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ))) );
/*x*/		} else {
/*e*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(InsertSalesOrderLine(php_xmlrpc_decode($xmlrpcmsg->getParam( 0 )), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = 'This function is used to modify line items on a sales order.';
	$Parameter[0]['name'] = _('Modify Sales Order Line');
	$Parameter[0]['description'] = _('A set of key/value pairs where the key must be identical to the name of the field to be updated. ')
			._('The field names can be found ') . '<a href="../../Z_DescribeTable.php?table=salesorderdetails">' . _('here ') . '</a>'
			._('and are case sensitive. ')._('The values should be of the correct type, and the api will check them before updating the database. ')
			._('The orderno and stkcode keys must be one of these values. ')
			._('It is not necessary to include all the fields in this parameter, the database default value will be used if the field is not given.');
	$Parameter[1]['name'] = _('User name');
	$Parameter[1]['description'] = _('A valid weberp username. This user should have security access  to this data.');
	$Parameter[2]['name'] = _('User password');
	$Parameter[2]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('This function returns an array; the first element is 0 for success; otherwise the array contains a list of all errors encountered.');

/*E*/	$ModifySalesOrderLine_sig = array(array($xmlrpcStruct,$xmlrpcStruct),
/*x*/					array($xmlrpcStruct,$xmlrpcStruct,$xmlrpcString,$xmlrpcString));
	$ModifySalesOrderLine_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_ModifySalesOrderLine($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 3)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(ModifySalesOrderLine(php_xmlrpc_decode($xmlrpcmsg->getParam( 0 )),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ))) );
/*x*/		} else {
/*e*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(ModifySalesOrderLine(php_xmlrpc_decode($xmlrpcmsg->getParam( 0 )), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);
	$ReturnValue = _('Return Value Descriptions go here');
	$Description = _('Function Description go here');
	$Parameter[0]['name'] = _('Account Details');
	$Parameter[0]['description'] = _('An array of index/value items describing the GL Account and fields to set.');
	$Parameter[1]['name'] = _('User name');
	$Parameter[1]['description'] = _('A valid weberp username. This user should have security access to this data.');
	$Parameter[2]['name'] = _('User password');
	$Parameter[2]['description'] = _('The weberp password associated with this user name. ');

/*E*/	$InsertGLAccount_sig = array(array($xmlrpcStruct,$xmlrpcStruct),
/*x*/					array($xmlrpcStruct,$xmlrpcStruct,$xmlrpcString,$xmlrpcString));
	$InsertGLAccount_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_InsertGLAccount($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 3)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(InsertGLAccount(php_xmlrpc_decode($xmlrpcmsg->getParam( 0 )),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ))) );
/*x*/		} else {
/*e*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(InsertGLAccount(php_xmlrpc_decode($xmlrpcmsg->getParam( 0 )), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$ReturnValue = _('Return Value Descriptions go here');
	$Description = _('Function Description go here');

	$Parameter[0]['name'] = _('Account Section Details');
	$Parameter[0]['description'] = _('An array of index/value items describing the account section to insert.');
	$Parameter[1]['name'] = _('User name');
	$Parameter[1]['description'] = _('A valid weberp username. This user should have security access to this data.');
	$Parameter[2]['name'] = _('User password');
	$Parameter[2]['description'] = _('The weberp password associated with this user name. ');

/*E*/	$InsertGLAccountSection_sig = array(array($xmlrpcStruct,$xmlrpcStruct),
/*x*/					array($xmlrpcStruct,$xmlrpcStruct,$xmlrpcString,$xmlrpcString));
	$InsertGLAccountSection_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_InsertGLAccountSection($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 3)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(InsertGLAccountSection(php_xmlrpc_decode($xmlrpcmsg->getParam( 0 )),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ))) );
/*x*/		} else {
/*e*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(InsertGLAccountSection(php_xmlrpc_decode($xmlrpcmsg->getParam( 0 )), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$ReturnValue = _('Return Value Descriptions go here');
	$Description = _('Function Description go here');

	$Parameter[0]['name'] = _('Account Group Details');
	$Parameter[0]['description'] = _('An array of index/value items describing the account group to insert.');
	$Parameter[1]['name'] = _('User name');
	$Parameter[1]['description'] = _('A valid weberp username. This user should have security access to this data.');
	$Parameter[2]['name'] = _('User password');
	$Parameter[2]['description'] = _('The weberp password associated with this user name. ');

/*E*/	$InsertGLAccountGroup_sig = array(array($xmlrpcStruct,$xmlrpcStruct),
/*x*/					array($xmlrpcStruct,$xmlrpcStruct,$xmlrpcString,$xmlrpcString));
	$InsertGLAccountGroup_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_InsertGLAccountGroup($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 3)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(InsertGLAccountGroup(php_xmlrpc_decode($xmlrpcmsg->getParam( 0 )),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ))) );
/*x*/		} else {
/*e*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(InsertGLAccountGroup(php_xmlrpc_decode($xmlrpcmsg->getParam( 0 )), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('This function returns a list of stock location ids.');
	$Parameter[0]['name'] = _('User name');
	$Parameter[0]['description'] = _('A valid weberp username. This user should have security access  to this data.');
	$Parameter[1]['name'] = _('User password');
	$Parameter[1]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('This function returns an array of stock location ids.');

/*E*/	$GetLocationList_sig = array(array($xmlrpcStruct),
/*x*/					array($xmlrpcStruct,$xmlrpcString,$xmlrpcString));
	$GetLocationList_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_GetLocationList($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 2){
/*x*/			$rtn = new xmlrpcresp( php_xmlrpc_encode(GetLocationList($xmlrpcmsg->getParam( 0 )->scalarval(  ),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ))) );
/*x*/		} else {
/*e*/ $rtn = new xmlrpcresp( php_xmlrpc_encode(GetLocationList( '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('This function takes a stock location id and returns details of that stock location.');
	$Parameter[0]['name'] = _('Stock Location Code');
	$Parameter[0]['description'] = _('A stock location code as returned by the GetLocationList function.');
	$Parameter[1]['name'] = _('User name');
	$Parameter[1]['description'] = _('A valid weberp username. This user should have security access to this data.');
	$Parameter[2]['name'] = _('User password');
	$Parameter[2]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('This function returns an array of stock location details.');

/*E*/	$GetLocationDetails_sig = array(array($xmlrpcStruct,$xmlrpcString),
/*x*/					array($xmlrpcStruct,$xmlrpcString,$xmlrpcString,$xmlrpcString));
	$GetLocationDetails_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_GetLocationDetails($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 3)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetLocationDetails($xmlrpcmsg->getParam( 0 )->scalarval(  ),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ))) );
/*x*/		} else {
/*e*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetLocationDetails($xmlrpcmsg->getParam( 0 )->scalarval(  ), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('This function returns a list of stock shipper ids.');
	$Parameter[0]['name'] = _('User name');
	$Parameter[0]['description'] = _('A valid weberp username. This user should have security access  to this data.');
	$Parameter[1]['name'] = _('User password');
	$Parameter[1]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('This function returns an array of stock shipper ids.');

/*E*/	$GetShipperList_sig = array(array($xmlrpcStruct),
/*x*/					array($xmlrpcStruct,$xmlrpcString,$xmlrpcString));
	$GetShipperList_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_GetShipperList($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 2)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetShipperList($xmlrpcmsg->getParam( 0 )->scalarval(  ),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ))) );
/*x*/		} else {
/*e*/ $rtn = new xmlrpcresp( php_xmlrpc_encode(GetShipperList( '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('This function takes a stock shipper id and returns details of that shipper.');
	$Parameter[0]['name'] = _('Stock Shipper ID');
	$Parameter[0]['description'] = _('A stock shipper ID as returned by the GetShippersList function.');
	$Parameter[1]['name'] = _('User name');
	$Parameter[1]['description'] = _('A valid weberp username. This user should have security access to this data.');
	$Parameter[2]['name'] = _('User password');
	$Parameter[2]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('This function returns an array of stock shipper details.');

/*E*/	$GetShipperDetails_sig = array(array($xmlrpcStruct,$xmlrpcString),
/*x*/					array($xmlrpcStruct,$xmlrpcString,$xmlrpcString,$xmlrpcString));
	$GetShipperDetails_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_GetShipperDetails($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 3)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetShipperDetails($xmlrpcmsg->getParam( 0 )->scalarval(  ),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ))) );
/*x*/		} else {
/*e*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetShipperDetails($xmlrpcmsg->getParam( 0 )->scalarval(  ), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('This function returns a list of sales area codes.');
	$Parameter[0]['name'] = _('User name');
	$Parameter[0]['description'] = _('A valid weberp username. This user should have security access  to this data.');
	$Parameter[1]['name'] = _('User password');
	$Parameter[1]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('This function returns an array of sales area codes.');

/*E*/	$GetSalesAreasList_sig = array(array($xmlrpcStruct),
/*x*/					array($xmlrpcStruct,$xmlrpcString,$xmlrpcString));
	$GetSalesAreasList_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_GetSalesAreasList($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 2)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetSalesAreasList($xmlrpcmsg->getParam( 0 )->scalarval(  ),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ))) );
/*x*/		} else {
/*e*/ $rtn = new xmlrpcresp( php_xmlrpc_encode(GetSalesAreasList( '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('This function takes a sales area code and returns details of that sales area.');
	$Parameter[0]['name'] = _('Sales Area Code');
	$Parameter[0]['description'] = _('A sales area code as returned by the GetSalesAreasList function.');
	$Parameter[1]['name'] = _('User name');
	$Parameter[1]['description'] = _('A valid weberp username. This user should have security access to this data.');
	$Parameter[2]['name'] = _('User password');
	$Parameter[2]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('This function returns an array of sales area details.');

/*E*/	$GetSalesAreaDetails_sig = array(array($xmlrpcStruct,$xmlrpcString),
/*x*/					array($xmlrpcStruct,$xmlrpcString,$xmlrpcString,$xmlrpcString));
	$GetSalesAreaDetails_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_GetSalesAreaDetails($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 3)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetSalesAreaDetails($xmlrpcmsg->getParam( 0 )->scalarval(  ),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ))) );
/*x*/		} else {
/*e*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetSalesAreaDetails($xmlrpcmsg->getParam( 0 )->scalarval(  ), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('This function takes a sales area description and returns details of that sales area.');
	$Parameter[0]['name'] = _('Sales Area Description');
	$Parameter[0]['description'] = _('A sales area description of the sales area of interest.');
	$Parameter[1]['name'] = _('User name');
	$Parameter[1]['description'] = _('A valid weberp username. This user should have security access to this data.');
	$Parameter[2]['name'] = _('User password');
	$Parameter[2]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('This function returns an array of sales area details.');

/*E*/	$GetSalesAreaDetailsFromName_sig = array(array($xmlrpcStruct,$xmlrpcString),
/*x*/					array($xmlrpcStruct,$xmlrpcString,$xmlrpcString,$xmlrpcString));
	$GetSalesAreaDetailsFromName_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_GetSalesAreaDetailsFromName($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 3)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetSalesAreaDetailsFromName($xmlrpcmsg->getParam( 0 )->scalarval(  ),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ))) );
/*x*/		} else {
/*e*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetSalesAreaDetailsFromName($xmlrpcmsg->getParam( 0 )->scalarval(  ), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$ReturnValue = _('Return Value Descriptions go here');
	$Description = _('Function Description go here');

	$Parameter[0]['name'] = _('Sales Area Details');
	$Parameter[0]['description'] = _('An array of index/value items describing the sales area to insert.');
	$Parameter[1]['name'] = _('User name');
	$Parameter[1]['description'] = _('A valid weberp username. This user should have security access to this data.');
	$Parameter[2]['name'] = _('User password');
	$Parameter[2]['description'] = _('The weberp password associated with this user name. ');

/*E*/	$InsertSalesArea_sig = array(array($xmlrpcStruct,$xmlrpcStruct),
/*x*/					array($xmlrpcStruct,$xmlrpcStruct,$xmlrpcString,$xmlrpcString));
	$InsertSalesArea_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_InsertSalesArea($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 3)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(InsertSalesArea(php_xmlrpc_decode($xmlrpcmsg->getParam( 0 )),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ))) );
/*x*/		} else {
/*e*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(InsertSalesArea(php_xmlrpc_decode($xmlrpcmsg->getParam( 0 )), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('This function returns a list of salesman codes.');
	$Parameter[0]['name'] = _('User name');
	$Parameter[0]['description'] = _('A valid weberp username. This user should have security access  to this data.');
	$Parameter[1]['name'] = _('User password');
	$Parameter[1]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('This function returns an array of salesman codes.');

/*E*/	$GetSalesmanList_sig = array(array($xmlrpcStruct),
/*x*/					array($xmlrpcStruct,$xmlrpcString,$xmlrpcString));
	$GetSalesmanList_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_GetSalesmanList($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 2)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetSalesmanList($xmlrpcmsg->getParam( 0 )->scalarval(  ),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ))) );
/*x*/		} else {
/*e*/ $rtn = new xmlrpcresp( php_xmlrpc_encode(GetSalesmanList( '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('This function takes a salesman code and returns details of that salesman.');
	$Parameter[0]['name'] = _('Sales Area Code');
	$Parameter[0]['description'] = _('A salesman code as returned by the GetSalesmanList function.');
	$Parameter[1]['name'] = _('User name');
	$Parameter[1]['description'] = _('A valid weberp username. This user should have security access to this data.');
	$Parameter[2]['name'] = _('User password');
	$Parameter[2]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('This function returns an array of salesman details.');

/*E*/	$GetSalesmanDetails_sig = array(array($xmlrpcStruct,$xmlrpcString),
/*x*/					array($xmlrpcStruct,$xmlrpcString,$xmlrpcString,$xmlrpcString));
	$GetSalesmanDetails_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_GetSalesmanDetails($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 3)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetSalesmanDetails($xmlrpcmsg->getParam( 0 )->scalarval(  ),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ))) );
/*x*/		} else {
/*e*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetSalesmanDetails($xmlrpcmsg->getParam( 0 )->scalarval(  ), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('This function takes a salesman\'s name and returns details of that salesman.');
	$Parameter[0]['name'] = _('Salesman Name');
	$Parameter[0]['description'] = _('The name of the salesman of interest.');
	$Parameter[1]['name'] = _('User name');
	$Parameter[1]['description'] = _('A valid weberp username. This user should have security access to this data.');
	$Parameter[2]['name'] = _('User password');
	$Parameter[2]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('This function returns an array of salesman details.');

/*E*/	$GetSalesmanDetailsFromName_sig = array(array($xmlrpcStruct,$xmlrpcString),
/*x*/					array($xmlrpcStruct,$xmlrpcString,$xmlrpcString,$xmlrpcString));
	$GetSalesmanDetailsFromName_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_GetSalesmanDetailsFromName($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 3)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetSalesmanDetailsFromName($xmlrpcmsg->getParam( 0 )->scalarval(  ),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ))) );
/*x*/		} else {
/*e*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetSalesmanDetailsFromName($xmlrpcmsg->getParam( 0 )->scalarval(  ), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$ReturnValue = _('Return Value Descriptions go here');
	$Description = _('Function Description go here');

	$Parameter[0]['name'] = _('Salesman Details');
	$Parameter[0]['description'] = _('An array of index/value items describing the salesman to insert.');
	$Parameter[1]['name'] = _('User name');
	$Parameter[1]['description'] = _('A valid weberp username. This user should have security access to this data.');
	$Parameter[2]['name'] = _('User password');
	$Parameter[2]['description'] = _('The weberp password associated with this user name. ');

/*E*/	$InsertSalesman_sig = array(array($xmlrpcStruct,$xmlrpcStruct),
/*x*/					array($xmlrpcStruct,$xmlrpcStruct,$xmlrpcString,$xmlrpcString));
	$InsertSalesman_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_InsertSalesman($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 3)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(InsertSalesman(php_xmlrpc_decode($xmlrpcmsg->getParam( 0 )),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ))) );
/*x*/		} else {
/*e*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(InsertSalesman(php_xmlrpc_decode($xmlrpcmsg->getParam( 0 )), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('This function returns a list of tax group IDs.');
	$Parameter[0]['name'] = _('User name');
	$Parameter[0]['description'] = _('A valid weberp username. This user should have security access  to this data.');
	$Parameter[1]['name'] = _('User password');
	$Parameter[1]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('This function returns an array of tax group IDs.');

/*E*/$GetTaxGroupList_sig = array(array($xmlrpcStruct),
/*x*/					array($xmlrpcStruct,$xmlrpcString,$xmlrpcString));
	$GetTaxGroupList_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_GetTaxGroupList($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/	if ($xmlrpcmsg->getNumParams() == 2)	{
/*x*/		$rtn = new xmlrpcresp( php_xmlrpc_encode(GetTaxGroupList($xmlrpcmsg->getParam( 0 )->scalarval(  ), $xmlrpcmsg->getParam( 1 )->scalarval(  ))) );
/*x*/	} else {
/*e*/ 		$rtn = new xmlrpcresp( php_xmlrpc_encode(GetTaxGroupList( '', '')));
/*x*/	}
		ob_end_flush();
		return $rtn;
	}


	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('This function takes a tax group ID and returns details of that tax group.');
	$Parameter[0]['name'] = _('Tax Group ID');
	$Parameter[0]['description'] = _('A tax group ID as returned by the GetTaxgroupList function.');
	$Parameter[1]['name'] = _('User name');
	$Parameter[1]['description'] = _('A valid weberp username. This user should have security access to this data.');
	$Parameter[2]['name'] = _('User password');
	$Parameter[2]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('This function returns an array of tax group details.');

/*E*/$GetTaxGroupDetails_sig = array(array($xmlrpcStruct,$xmlrpcString),
/*x*/					array($xmlrpcStruct,$xmlrpcString,$xmlrpcString,$xmlrpcString));
	$GetTaxGroupDetails_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_GetTaxGroupDetails($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/	if ($xmlrpcmsg->getNumParams() == 3)	{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetTaxGroupDetails($xmlrpcmsg->getParam( 0 )->scalarval(  ),
/*x*/		$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/		$xmlrpcmsg->getParam( 2 )->scalarval(  ))) );
/*x*/	} else {
/*e*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetTaxGroupDetails($xmlrpcmsg->getParam( 0 )->scalarval(  ), '', '')));
/*x*/	}
		ob_end_flush();
		return $rtn;
	}

	$Description = _('This function returns a list of tax authority IDs.');
	$Parameter[0]['name'] = _('User name');
	$Parameter[0]['description'] = _('A valid weberp username. This user should have security access  to this data.');
	$Parameter[1]['name'] = _('User password');
	$Parameter[1]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('This function returns an array of tax group IDs.');

/*E*/$GetTaxAuthorityList_sig = array(array($xmlrpcStruct),
/*x*/					array($xmlrpcStruct,$xmlrpcString,$xmlrpcString));
	$GetTaxAuthorityList_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_GetTaxAuthorityList($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/	if ($xmlrpcmsg->getNumParams() == 2)	{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetTaxAuthorityList($xmlrpcmsg->getParam( 0 )->scalarval(  ), $xmlrpcmsg->getParam( 1 )->scalarval(  ))) );
/*x*/	} else {
/*e*/ 		$rtn = new xmlrpcresp( php_xmlrpc_encode(GetTaxAuthorityList( '', '')));
/*x*/	}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('This function takes a tax authority ID and returns details of that tax authority.');
	$Parameter[0]['name'] = _('Tax Authority ID');
	$Parameter[0]['description'] = _('A tax Authority ID as returned by the GetTaxAuthorityList function.');
	$Parameter[1]['name'] = _('User name');
	$Parameter[1]['description'] = _('A valid weberp username. This user should have security access to this data.');
	$Parameter[2]['name'] = _('User password');
	$Parameter[2]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('This function returns an array of tax authority details.');

/*E*/$GetTaxAuthorityDetails_sig = array(array($xmlrpcStruct,$xmlrpcString),
/*x*/					array($xmlrpcStruct,$xmlrpcString,$xmlrpcString,$xmlrpcString));
	$GetTaxAuthorityDetails_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_GetTaxAuthorityDetails($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/	if ($xmlrpcmsg->getNumParams() == 3)	{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetTaxAuthorityDetails($xmlrpcmsg->getParam( 0 )->scalarval(  ), $xmlrpcmsg->getParam( 1 )->scalarval(  ), $xmlrpcmsg->getParam( 2 )->scalarval(  ))) );
/*x*/	} else {
/*e*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetTaxAuthorityDetails($xmlrpcmsg->getParam( 0 )->scalarval(  ), '', '')));
/*x*/	}
		ob_end_flush();
		return $rtn;
	}

    unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('This function takes a tax authority ID and returns the rates of tax for the authority.');
	$Parameter[0]['name'] = _('Tax Authority ID');
	$Parameter[0]['description'] = _('A tax Authority ID as returned by the GetTaxAuthorityList function.');
	$Parameter[1]['name'] = _('User name');
	$Parameter[1]['description'] = _('A valid weberp username. This user should have security access to this data.');
	$Parameter[2]['name'] = _('User password');
	$Parameter[2]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('This function returns the tax rates for the authority.');

/*E*/$GetTaxAuthorityRates_sig = array(array($xmlrpcStruct,$xmlrpcString),
/*x*/					array($xmlrpcStruct,$xmlrpcString,$xmlrpcString,$xmlrpcString));
	$GetTaxAuthorityRates_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_GetTaxAuthorityRates($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/	if ($xmlrpcmsg->getNumParams() == 3)	{
/*x*/		$rtn = new xmlrpcresp( php_xmlrpc_encode(GetTaxAuthorityRates($xmlrpcmsg->getParam( 0 )->scalarval(  ),
/*x*/																		$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/																		$xmlrpcmsg->getParam( 2 )->scalarval(  ) )) );
/*x*/	} else {
/*e*/		$rtn = new xmlrpcresp( php_xmlrpc_encode(GetTaxAuthorityRates($xmlrpcmsg->getParam( 0 )->scalarval(  ), '', '')));
/*x*/	}
		ob_end_flush();
		return $rtn;
	}

    unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('This function takes a tax group ID and returns the taxes that belong to that tax group.');
	$Parameter[0]['name'] = _('Tax Group ID');
	$Parameter[0]['description'] = _('A tax group ID as returned by the GetTaxgroupList function.');
	$Parameter[1]['name'] = _('User name');
	$Parameter[1]['description'] = _('A valid weberp username. This user should have security access to this data.');
	$Parameter[2]['name'] = _('User password');
	$Parameter[2]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('This function returns an array of tax group details.');

/*E*/$GetTaxGroupTaxes_sig = array(array($xmlrpcStruct,$xmlrpcString),
/*x*/					array($xmlrpcStruct,$xmlrpcString,$xmlrpcString,$xmlrpcString));
	$GetTaxGroupTaxes_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_GetTaxGroupTaxes($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/	if ($xmlrpcmsg->getNumParams() == 3)	{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetTaxGroupTaxes($xmlrpcmsg->getParam( 0 )->scalarval(  ), 	$xmlrpcmsg->getParam( 1 )->scalarval(  ), $xmlrpcmsg->getParam( 2 )->scalarval(  ))) );
/*x*/	} else {
/*e*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetTaxGroupTaxes($xmlrpcmsg->getParam( 0 )->scalarval(  ), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('This function returns a list of customer types.');
	$Parameter[0]['name'] = _('User name');
	$Parameter[0]['description'] = _('A valid weberp username. This user should have security access  to this data.');
	$Parameter[1]['name'] = _('User password');
	$Parameter[1]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('This function returns an array of customer types');

/*E*/	$GetCustomerTypeList_sig = array(array($xmlrpcStruct),
/*x*/					array($xmlrpcStruct,$xmlrpcString,$xmlrpcString));
	$GetCustomerTypeList_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_GetCustomerTypeList($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/	if ($xmlrpcmsg->getNumParams() == 2)	{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetCustomerTypeList($xmlrpcmsg->getParam( 0 )->scalarval(  ), $xmlrpcmsg->getParam( 1 )->scalarval(  ))) );
/*x*/	} else {
/*e*/ 		$rtn = new xmlrpcresp( php_xmlrpc_encode(GetCustomerTypeList( '', '')));
/*x*/	}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('This function takes a customer type ID and returns details of that customer type.');
	$Parameter[0]['name'] = _('Customer Type ID');
	$Parameter[0]['description'] = _('A customer type ID as returned by the GetCustomerTypeList function.');
	$Parameter[1]['name'] = _('User name');
	$Parameter[1]['description'] = _('A valid weberp username. This user should have security access to this data.');
	$Parameter[2]['name'] = _('User password');
	$Parameter[2]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('This function returns an array of customer type details.');

/*E*/	$GetCustomerTypeDetails_sig = array(array($xmlrpcStruct,$xmlrpcString),
/*x*/					array($xmlrpcStruct,$xmlrpcString,$xmlrpcString,$xmlrpcString));
	$GetCustomerTypeDetails_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_GetCustomerTypeDetails($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 3)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetCustomerTypeDetails($xmlrpcmsg->getParam( 0 )->scalarval(  ),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ))) );
/*x*/		} else {
/*e*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetCustomerTypeDetails($xmlrpcmsg->getParam( 0 )->scalarval(  ), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$ReturnValue = _('Return Value Descriptions go here');
	$Description = _('Function Description go here');

	$Parameter[0]['name'] = _('Category Details');
	$Parameter[0]['description'] = _('An array of index/value items describing the stock category to insert.');
	$Parameter[1]['name'] = _('User name');
	$Parameter[1]['description'] = _('A valid weberp username. This user should have security access to this data.');
	$Parameter[2]['name'] = _('User password');
	$Parameter[2]['description'] = _('The weberp password associated with this user name. ');

/*E*/	$InsertStockCategory_sig = array(array($xmlrpcStruct,$xmlrpcStruct),
/*x*/					array($xmlrpcStruct,$xmlrpcStruct,$xmlrpcString,$xmlrpcString));
	$InsertStockCategory_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_InsertStockCategory($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 3)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(InsertStockCategory(php_xmlrpc_decode($xmlrpcmsg->getParam( 0 )),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ))) );
/*x*/		} else {
/*e*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(InsertStockCategory(php_xmlrpc_decode($xmlrpcmsg->getParam( 0 )), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$ReturnValue = _('Return Value Descriptions go here');
	$Description = _('Function Description go here');

	$Parameter[0]['name'] = _('Category Details');
	$Parameter[0]['description'] = _('An array of index/value items describing the stock category to modify.');
	$Parameter[1]['name'] = _('User name');
	$Parameter[1]['description'] = _('A valid weberp username. This user should have security access to this data.');
	$Parameter[2]['name'] = _('User password');
	$Parameter[2]['description'] = _('The weberp password associated with this user name. ');

/*E*/	$ModifyStockCategory_sig = array(array($xmlrpcStruct,$xmlrpcStruct),
/*x*/					array($xmlrpcStruct,$xmlrpcStruct,$xmlrpcString,$xmlrpcString));
	$ModifyStockCategory_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_ModifyStockCategory($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 3)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(ModifyStockCategory(php_xmlrpc_decode($xmlrpcmsg->getParam( 0 )),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ))) );
/*x*/		} else {
/*e*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(ModifyStockCategory(php_xmlrpc_decode($xmlrpcmsg->getParam( 0 )), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}
	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('This function returns a list of stock category abbreviations.');
	$Parameter[0]['name'] = _('User name');
	$Parameter[0]['description'] = _('A valid weberp username. This user should have security access  to this data.');
	$Parameter[1]['name'] = _('User password');
	$Parameter[1]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('If successful, this function returns an array of stock category ids. ')
			._('Otherwise an array of error codes is returned and no stock categories are returned. ');

/*E*/	$GetStockCategoryList_sig = array(array($xmlrpcStruct),
/*x*/					array($xmlrpcStruct,$xmlrpcString,$xmlrpcString));
	$GetStockCategoryList_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_GetStockCategoryList($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/if ($xmlrpcmsg->getNumParams() == 2){
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetStockCategoryList($xmlrpcmsg->getParam( 0 )->scalarval(  ),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ))) );
/*x*/		} else {
/*e*/ $rtn = new xmlrpcresp( php_xmlrpc_encode(GetStockCategoryList( '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('This function takes a stock category ID and returns details of that stock category type.');
	$Parameter[0]['name'] = _('Stock Category ID');
	$Parameter[0]['description'] = _('A Stock Category ID as returned by the *WHAT* function.');
	$Parameter[1]['name'] = _('User name');
	$Parameter[1]['description'] = _('A valid weberp username. This user should have security access to this data.');
	$Parameter[2]['name'] = _('User password');
	$Parameter[2]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('This function returns an array of stock category details.');

/*E*/	$GetStockCategory_sig = array(array($xmlrpcStruct,$xmlrpcString),
/*x*/					array($xmlrpcStruct,$xmlrpcString,$xmlrpcString,$xmlrpcString));
	$GetStockCategory_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_GetStockCategory($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 3)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetStockCategory($xmlrpcmsg->getParam( 0 )->scalarval(  ),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ))) );
/*x*/		} else {
/*e*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetStockCategory($xmlrpcmsg->getParam( 0 )->scalarval(  ), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$ReturnValue = _('Return Value Descriptions go here');
	$Description = _('Function Description go here');

	$Parameter[0]['name'] = _('Field Name');
	$Parameter[0]['description'] = _('The field name to search on.');
	$Parameter[1]['name'] = _('Match Criteria');
	$Parameter[1]['description'] = _('The SQL search pattern to select items in the database.');
	$Parameter[2]['name'] = _('User name');
	$Parameter[2]['description'] = _('A valid weberp username. This user should have security access to this data.');
	$Parameter[3]['name'] = _('User password');
	$Parameter[3]['description'] = _('The weberp password associated with this user name. ');

/*E*/	$SearchStockCategories_sig = array(array($xmlrpcStruct,$xmlrpcString,$xmlrpcString),
/*x*/					array($xmlrpcStruct,$xmlrpcString,$xmlrpcString,$xmlrpcString,$xmlrpcString));
	$SearchStockCategories_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_SearchStockCategories($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 4)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(SearchStockCategories($xmlrpcmsg->getParam( 0 )->scalarval(  ),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ),
/*x*/					$xmlrpcmsg->getParam( 3 )->scalarval(  ))) );
/*x*/		} else {
		 $rtn = new xmlrpcresp( php_xmlrpc_encode(SearchStockCategories($xmlrpcmsg->getParam( 0 )->scalarval(  ),
/*e*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$ReturnValue = _('Return Value Descriptions go here');
	$Description = _('Function Description go here');

	$Parameter[0]['name'] = _('Label Name');
	$Parameter[0]['description'] = _('The category label to search on.');
	$Parameter[1]['name'] = _('Match Criteria');
	$Parameter[1]['description'] = _('The SQL search pattern to select items in the database.');
	$Parameter[2]['name'] = _('User name');
	$Parameter[2]['description'] = _('A valid weberp username. This user should have security access to this data.');
	$Parameter[3]['name'] = _('User password');
	$Parameter[3]['description'] = _('The weberp password associated with this user name. ');

/*E*/	$StockCatPropertyList_sig = array(array($xmlrpcStruct,$xmlrpcString,$xmlrpcString),
/*x*/					array($xmlrpcStruct,$xmlrpcString,$xmlrpcString,$xmlrpcString,$xmlrpcString));
	$StockCatPropertyList_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_StockCatPropertyList($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 4)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(StockCatPropertyList($xmlrpcmsg->getParam( 0 )->scalarval(  ),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ),
/*x*/					$xmlrpcmsg->getParam( 3 )->scalarval(  ))) );
/*x*/		} else {
		 $rtn = new xmlrpcresp( php_xmlrpc_encode(StockCatPropertyList($xmlrpcmsg->getParam( 0 )->scalarval(  ),
/*e*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('This function returns a list of general ledger account codes.');
	$Parameter[0]['name'] = _('User name');
	$Parameter[0]['description'] = _('A valid weberp username. This user should have security access  to this data.');
	$Parameter[1]['name'] = _('User password');
	$Parameter[1]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('This function returns an array of general ledger account codes.');

/*E*/	$GetGLAccountList_sig = array(array($xmlrpcStruct),
/*x*/					array($xmlrpcStruct,$xmlrpcString,$xmlrpcString));
	$GetGLAccountList_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_GetGLAccountList($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 2)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetGLAccountList($xmlrpcmsg->getParam( 0 )->scalarval(  ),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ))) );
/*x*/		} else {
/*e*/ $rtn = new xmlrpcresp( php_xmlrpc_encode(GetGLAccountList( '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('This function takes a general ledger account code and returns details of that account.');
	$Parameter[0]['name'] = _('General Ledger Account Code');
	$Parameter[0]['description'] = _('A general ledger account code as returned by the GetGLAccountList function.');
	$Parameter[1]['name'] = _('User name');
	$Parameter[1]['description'] = _('A valid weberp username. This user should have security access to this data.');
	$Parameter[2]['name'] = _('User password');
	$Parameter[2]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('This function returns an array of general ledger account details.');

/*E*/	$GetGLAccountDetails_sig = array(array($xmlrpcStruct,$xmlrpcString),
/*x*/					array($xmlrpcStruct,$xmlrpcString,$xmlrpcString,$xmlrpcString));
	$GetGLAccountDetails_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_GetGLAccountDetails($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 3)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetGLAccountDetails($xmlrpcmsg->getParam( 0 )->scalarval(  ),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ))) );
/*x*/		} else {
/*e*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetGLAccountDetails($xmlrpcmsg->getParam( 0 )->scalarval(  ), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('This function takes a stock code ID and a tax authority code and returns the relevant tax rate.');
	$Parameter[0]['name'] = _('StockID');
	$Parameter[0]['description'] = _('The stock ID of the item whose tax rate is desired.');
	$Parameter[1]['name'] = _('Tax Authority Code');
	$Parameter[1]['description'] = _('The code identifying the tax authority of interest.');
	$Parameter[2]['name'] = _('User name');
	$Parameter[2]['description'] = _('A valid weberp username. This user should have security access to this data.');
	$Parameter[3]['name'] = _('User password');
	$Parameter[3]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('This function returns an array of general ledger account details.');

/*E*/	$GetStockTaxRate_sig = array(array($xmlrpcStruct,$xmlrpcString,$xmlrpcString),
/*x*/					array($xmlrpcStruct,$xmlrpcString,$xmlrpcString,$xmlrpcString,$xmlrpcString));
	$GetStockTaxRate_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_GetStockTaxRate($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 4)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetStockTaxRate($xmlrpcmsg->getParam( 0 )->scalarval(  ),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ),
/*x*/					$xmlrpcmsg->getParam( 3 )->scalarval(  ))) );
/*x*/		} else {
		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetStockTaxRate($xmlrpcmsg->getParam( 0 )->scalarval(  ),
/*e*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('This function is used to insert a new supplier into the webERP database.');
	$Parameter[0]['name'] = _('Supplier Details');
	$Parameter[0]['description'] = _('A set of key/value pairs where the key must be identical to the name of the field to be updated. ')
			._('The field names can be found ') . '<a href="../../Z_DescribeTable.php?table=suppliers">' . _('here ') . '</a>'
			._('and are case sensitive. ')._('The values should be of the correct type, and the api will check them before updating the database. ')
			._('It is not necessary to include all the fields in this parameter, the database default value will be used if the field is not given.');
	$Parameter[1]['name'] = _('User name');
	$Parameter[1]['description'] = _('A valid weberp username. This user should have security access  to this data.');
	$Parameter[2]['name'] = _('User password');
	$Parameter[2]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('This function returns an array of integers. ')
			._('If the first element is zero then the function was successful. ')
			._('Otherwise an array of error codes is returned and no insertion takes place. ');

/*E*/	$InsertSupplier_sig = array(array($xmlrpcStruct,$xmlrpcStruct),
/*x*/					array($xmlrpcStruct,$xmlrpcStruct,$xmlrpcString,$xmlrpcString));
	$InsertSupplier_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_InsertSupplier($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 3)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(InsertSupplier(php_xmlrpc_decode($xmlrpcmsg->getParam( 0 )),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ))) );
/*x*/		} else {
/*e*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(InsertSupplier(php_xmlrpc_decode($xmlrpcmsg->getParam( 0 )), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('This function is used to modify a supplier which is already setup in the webERP database.');
	$Parameter[0]['name'] = _('Supplier Details');
	$Parameter[0]['description'] = _('A set of key/value pairs where the key must be identical to the name of the field to be updated. ')
			._('The field names can be found ') . '<a href="../../Z_DescribeTable.php?table=suppliers">' . _('here ') . '</a>'
			._('and are case sensitive. ')._('The values should be of the correct type, and the api will check them before updating the database. ')
			._('It is not necessary to include all the fields in this parameter, the database default value will be used if the field is not given.')
			 . '<p>' . _('The supplierid must already exist in the weberp database.');
	$Parameter[1]['name'] = _('User name');
	$Parameter[1]['description'] = _('A valid weberp username. This user should have security access  to this data.');
	$Parameter[2]['name'] = _('User password');
	$Parameter[2]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('This function returns an array of integers. ')
			._('If the first element is zero then the function was successful. ')
			._('Otherwise an array of error codes is returned and no modification takes place. ');

/*E*/	$ModifySupplier_sig = array(array($xmlrpcStruct,$xmlrpcStruct),
/*x*/					array($xmlrpcStruct,$xmlrpcStruct,$xmlrpcString,$xmlrpcString));
	$ModifySupplier_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_ModifySupplier($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 3)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(ModifySupplier(php_xmlrpc_decode($xmlrpcmsg->getParam( 0 )),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ))) );
/*x*/		} else {
/*e*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(ModifySupplier(php_xmlrpc_decode($xmlrpcmsg->getParam( 0 )), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('This function is used to retrieve the details of a supplier from the webERP database.');
	$Parameter[0]['name'] = _('Supplier ID');
	$Parameter[0]['description'] = _('This is a string value. It must be a valid supplier id that is already in the webERP database.');
	$Parameter[1]['name'] = _('User name');
	$Parameter[1]['description'] = _('A valid weberp username. This user should have security access  to this data.');
	$Parameter[2]['name'] = _('User password');
	$Parameter[2]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('If successful this function returns a set of key/value pairs containing the details of this supplier. ')
			._('The key will be identical with field name from the suppliers table. All fields will be in the set regardless of whether the value was set.') . '<p>'
			._('Otherwise an array of error codes is returned. ');

/*E*/	$GetSupplier_sig = array(array($xmlrpcStruct,$xmlrpcString),
/*x*/					array($xmlrpcStruct,$xmlrpcString,$xmlrpcString,$xmlrpcString));
	$GetSupplier_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_GetSupplier($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 3)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetSupplier($xmlrpcmsg->getParam( 0 )->scalarval(  ),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ))) );
/*x*/		} else {
/*e*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetSupplier($xmlrpcmsg->getParam( 0 )->scalarval(  ), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('This function is used to retrieve the details of a supplier from the webERP database.');
	$Parameter[0]['name'] = _('Field name');
	$Parameter[0]['description'] = _('This is a string value. It must be a valid field in the suppliers table. This is case sensitive');
	$Parameter[1]['name'] = _('Criteria');
	$Parameter[1]['description'] = _('This is a string value. It holds the string that is searched for in the given field. It will search for all or part of the field.');
	$Parameter[2]['name'] = _('User name');
	$Parameter[2]['description'] = _('A valid weberp username. This user should have security access  to this data.');
	$Parameter[3]['name'] = _('User password');
	$Parameter[3]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('If successful this function returns an array of supplier ids. ')
			._('Otherwise an array of error codes is returned. ');

/*E*/	$SearchSuppliers_sig = array(array($xmlrpcStruct,$xmlrpcString,$xmlrpcString),
/*x*/					array($xmlrpcStruct,$xmlrpcString,$xmlrpcString,$xmlrpcString,$xmlrpcString));
	$SearchSuppliers_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_SearchSuppliers($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 4)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(SearchSuppliers($xmlrpcmsg->getParam( 0 )->scalarval(  ),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ),
/*x*/					$xmlrpcmsg->getParam( 3 )->scalarval(  ))) );
/*x*/		} else {
		 $rtn = new xmlrpcresp( php_xmlrpc_encode(SearchSuppliers($xmlrpcmsg->getParam( 0 )->scalarval(  ),
/*e*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('This function is used to retrieve the details of stock batches.');
	$Parameter[0]['name'] = _('Stock ID');
	$Parameter[0]['description'] = _('A string field containing a valid stockid that must already be setup in the stockmaster table. The api will check this before making the enquiry.');
	$Parameter[1]['name'] = _('Criteria');
	$Parameter[1]['description'] = _('This is a string value. It holds the string that is searched for in the given field. It will search for all or part of the field.');
	$Parameter[2]['name'] = _('User name');
	$Parameter[2]['description'] = _('A valid weberp username. This user should have security access  to this data.');
	$Parameter[3]['name'] = _('User password');
	$Parameter[3]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('Returns a two dimensional array of stock batch details. ')
			._('The fields returned are stockid, loccode, batchno, quantity, itemcost. ');

/*E*/	$GetBatches_sig = array(array($xmlrpcStruct,$xmlrpcString,$xmlrpcString),
/*x*/					array($xmlrpcStruct,$xmlrpcString,$xmlrpcString,$xmlrpcString,$xmlrpcString));
	$GetBatches_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_GetBatches($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 4)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetBatches($xmlrpcmsg->getParam( 0 )->scalarval(  ),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ),
/*x*/					$xmlrpcmsg->getParam( 3 )->scalarval(  ))) );
/*x*/		} else {
		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetBatches($xmlrpcmsg->getParam( 0 )->scalarval(  ),
/*e*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('Adjust the stock balance for the given stock code at the given location by the amount given.');
	$Parameter[0]['name'] = _('Stock ID');
	$Parameter[0]['description'] = _('A string field containing a valid stockid that must already be setup in the stockmaster table. The api will check this before making the enquiry.');
	$Parameter[1]['name'] = _('Location');
	$Parameter[1]['description'] = _('A string field containing a valid location code that must already be setup in the locations table. The api will check this before making the enquiry.');
	$Parameter[2]['name'] = _('Quantity');
	$Parameter[2]['description'] = _('This is an integer value. It holds the amount of stock to be adjusted. Should be negative if is stock is to be reduced');
	$Parameter[3]['name'] = _('Transaction Date');
	$Parameter[3]['description'] = _('This is a string value. It holds the string that is searched for in the given field. It will search for all or part of the field.');
	$Parameter[4]['name'] = _('User name');
	$Parameter[4]['description'] = _('A valid weberp username. This user should have security access  to this data.');
	$Parameter[5]['name'] = _('User password');
	$Parameter[5]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('If successful this function returns 0. ')
			._('Otherwise an array of error codes is returned. ');

/*E*/	$StockAdjustment_sig = array(array($xmlrpcStruct,$xmlrpcString,$xmlrpcString,$xmlrpcDouble,$xmlrpcString),
/*x*/					array($xmlrpcStruct,$xmlrpcString,$xmlrpcString,$xmlrpcDouble,$xmlrpcString,$xmlrpcString,$xmlrpcString));
	$StockAdjustment_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_StockAdjustment($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 6)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(StockAdjustment($xmlrpcmsg->getParam( 0 )->scalarval(  ),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ),
/*x*/					$xmlrpcmsg->getParam( 3 )->scalarval(  ),
/*x*/						$xmlrpcmsg->getParam( 4 )->scalarval(  ),
/*x*/							$xmlrpcmsg->getParam( 5 )->scalarval(  ))) );
/*x*/		} else {
		 $rtn = new xmlrpcresp( php_xmlrpc_encode(StockAdjustment($xmlrpcmsg->getParam( 0 )->scalarval(  ),
			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
				$xmlrpcmsg->getParam( 2 )->scalarval(  ),
/*e*/					$xmlrpcmsg->getParam( 3 )->scalarval(  ), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('Issues stock to a given work order from the given location');
	$Parameter[0]['name'] = _('Work Order Number');
	$Parameter[0]['description'] = _('A string field containing a valid work order number that has already been created. The api will check this before making the enquiry.');
	$Parameter[1]['name'] = _('Stock ID');
	$Parameter[1]['description'] = _('A string field containing a valid stockid that must already be setup in the stockmaster table. The api will check this before making the enquiry.');
	$Parameter[2]['name'] = _('Location');
	$Parameter[2]['description'] = _('A string field containing a valid location code that must already be setup in the locations table. The api will check this before making the enquiry.');
	$Parameter[3]['name'] = _('Quantity');
	$Parameter[3]['description'] = _('This is an integer value. It holds the amount of stock to be adjusted. Should be negative if is stock is to be reduced');
	$Parameter[4]['name'] = _('Transaction Date');
	$Parameter[4]['description'] = _('This is a string value. It holds the string that is searched for in the given field. It will search for all or part of the field.');
	$Parameter[4]['name'] = _('Batch number');
	$Parameter[4]['description'] = _('This is a string value. It holds the reference to the batch number for the product being issued. If the stockid is not batch controlled this is ignored.');
	$Parameter[5]['name'] = _('User name');
	$Parameter[5]['description'] = _('A valid weberp username. This user should have security access  to this data.');
	$Parameter[6]['name'] = _('User password');
	$Parameter[6]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('If successful this function returns 0. ')
			._('Otherwise an array of error codes is returned. ');

/*E*/	$WorkOrderIssue_sig = array(array($xmlrpcStruct,$xmlrpcString,$xmlrpcString,$xmlrpcString,$xmlrpcString,$xmlrpcString,$xmlrpcString),
/*x*/					array($xmlrpcStruct,$xmlrpcString,$xmlrpcString,$xmlrpcString,$xmlrpcString,$xmlrpcString,$xmlrpcString,$xmlrpcString,$xmlrpcString));
	$WorkOrderIssue_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_WorkOrderIssue($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 8)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(WorkOrderIssue($xmlrpcmsg->getParam( 0 )->scalarval(  ),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ),
/*x*/					$xmlrpcmsg->getParam( 3 )->scalarval(  ),
/*x*/						$xmlrpcmsg->getParam( 4 )->scalarval(  ),
/*x*/							$xmlrpcmsg->getParam( 5 )->scalarval(  ),
/*x*/								$xmlrpcmsg->getParam( 6 )->scalarval(  ),
/*x*/									$xmlrpcmsg->getParam( 7 )->scalarval(  ))) );
/*x*/		} else {
		 $rtn = new xmlrpcresp( php_xmlrpc_encode(WorkOrderIssue($xmlrpcmsg->getParam( 0 )->scalarval(  ),
			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
				$xmlrpcmsg->getParam( 2 )->scalarval(  ),
					$xmlrpcmsg->getParam( 3 )->scalarval(  ),
						$xmlrpcmsg->getParam( 4 )->scalarval(  ),
/*e*/							$xmlrpcmsg->getParam( 5 )->scalarval(  ), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('This function is used to retrieve the details of a work order from the webERP database.');
	$Parameter[0]['name'] = _('Field name');
	$Parameter[0]['description'] = _('This is a string value. It must be a valid field in the workorders table. This is case sensitive');
	$Parameter[1]['name'] = _('Criteria');
	$Parameter[1]['description'] = _('This is a string value. It holds the string that is searched for in the given field. It will search for all or part of the field.');
	$Parameter[2]['name'] = _('User name');
	$Parameter[2]['description'] = _('A valid weberp username. This user should have security access  to this data.');
	$Parameter[3]['name'] = _('User password');
	$Parameter[3]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('If successful this function returns an array of work order numbers. ')
			._('Otherwise an array of error codes is returned. ');

/*E*/	$SearchWorkOrders_sig = array(array($xmlrpcStruct,$xmlrpcString,$xmlrpcString),
/*x*/					array($xmlrpcStruct,$xmlrpcString,$xmlrpcString,$xmlrpcString,$xmlrpcString));
	$SearchWorkOrders_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_SearchWorkOrders($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 4)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(SearchWorkOrders($xmlrpcmsg->getParam( 0 )->scalarval(  ),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ),
/*x*/					$xmlrpcmsg->getParam( 3 )->scalarval(  ))) );
/*x*/		} else {
		 $rtn = new xmlrpcresp( php_xmlrpc_encode(SearchWorkOrders($xmlrpcmsg->getParam( 0 )->scalarval(  ),
/*e*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('This function is used to insert new purchasing data into the webERP database.');
	$Parameter[0]['name'] = _('Purchasing data');
	$Parameter[0]['description'] = _('A set of key/value pairs where the key must be identical to the name of the field to be updated. ')
			._('The field names can be found ') . '<a href="../../Z_DescribeTable.php?table=purchdata">' . _('here ') . '</a>'
			._('and are case sensitive. ')._('The values should be of the correct type, and the api will check them before updating the database. ')
			._('It is not necessary to include all the fields in this parameter, the database default value will be used if the field is not given.');
	$Parameter[1]['name'] = _('User name');
	$Parameter[1]['description'] = _('A valid weberp username. This user should have security access  to this data.');
	$Parameter[2]['name'] = _('User password');
	$Parameter[2]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('This function returns an array of integers. ')
			._('If the first element is zero then the function was successful. ')
			._('Otherwise an array of error codes is returned and no insertion takes place. ');

/*E*/	$InsertPurchData_sig = array(array($xmlrpcStruct,$xmlrpcStruct),
/*x*/					array($xmlrpcStruct,$xmlrpcStruct,$xmlrpcString,$xmlrpcString));
	$InsertPurchData_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_InsertPurchData($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 3)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(InsertPurchData(php_xmlrpc_decode($xmlrpcmsg->getParam( 0 )),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ))) );
/*x*/		} else {
/*e*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(InsertPurchData(php_xmlrpc_decode($xmlrpcmsg->getParam( 0 )), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('This function is used to modify purchasing data into the webERP database.');
	$Parameter[0]['name'] = _('Purchasing data');
	$Parameter[0]['description'] = _('A set of key/value pairs where the key must be identical to the name of the field to be updated. ')
			._('The field names can be found ') . '<a href="../../Z_DescribeTable.php?table=purchdata">' . _('here ') . '</a>'
			._('and are case sensitive. ')._('The values should be of the correct type, and the api will check them before updating the database. ')
			._('It is not necessary to include all the fields in this parameter, the database default value will be used if the field is not given.');
	$Parameter[1]['name'] = _('User name');
	$Parameter[1]['description'] = _('A valid weberp username. This user should have security access  to this data.');
	$Parameter[2]['name'] = _('User password');
	$Parameter[2]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('This function returns an array of integers. ')
			._('If the first element is zero then the function was successful. ')
			._('Otherwise an array of error codes is returned and no insertion takes place. ');

/*E*/	$ModifyPurchData_sig = array(array($xmlrpcStruct,$xmlrpcStruct),
/*x*/					array($xmlrpcStruct,$xmlrpcStruct,$xmlrpcString,$xmlrpcString));
	$ModifyPurchData_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_ModifyPurchData($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 3)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(ModifyPurchData(php_xmlrpc_decode($xmlrpcmsg->getParam( 0 )),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ))) );
/*x*/		} else {
/*e*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(ModifyPurchData(php_xmlrpc_decode($xmlrpcmsg->getParam( 0 )), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('This function is used to insert a new work order into the webERP database. Currently this works only for single line orders.');
	$Parameter[0]['name'] = _('Work order details');
	$Parameter[0]['description'] = _('A set of key/value pairs where the key must be identical to the name of the field to be updated. ')
			._('The field names can be found ') . '<a href="../../Z_DescribeTable.php?table=workorders">' . _('here ') . '</a>'
			._('and are case sensitive. ')._('The values should be of the correct type, and the api will check them before updating the database. ')
			._('It is not necessary to include all the fields in this parameter, the database default value will be used if the field is not given.');
	$Parameter[1]['name'] = _('User name');
	$Parameter[1]['description'] = _('A valid weberp username. This user should have security access  to this data.');
	$Parameter[2]['name'] = _('User password');
	$Parameter[2]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('This function returns an array of integers. ')
			._('If the first element is zero then the function was successful. ')
			._('Otherwise an array of error codes is returned and no insertion takes place. ');

/*E*/	$InsertWorkOrder_sig = array(array($xmlrpcStruct,$xmlrpcStruct),
/*x*/					array($xmlrpcStruct,$xmlrpcStruct,$xmlrpcString,$xmlrpcString));
	$InsertWorkOrder_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_InsertWorkOrder($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 3)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(InsertWorkOrder(php_xmlrpc_decode($xmlrpcmsg->getParam( 0 )),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ))) );
/*x*/		} else {
/*e*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(InsertWorkOrder(php_xmlrpc_decode($xmlrpcmsg->getParam( 0 )), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('Receives stock from a given work order from the given location');
	$Parameter[0]['name'] = _('Work Order Number');
	$Parameter[0]['description'] = _('A string field containing a valid work order number that has already been created. The api will check this before making the enquiry.');
	$Parameter[1]['name'] = _('Stock ID');
	$Parameter[1]['description'] = _('A string field containing a valid stockid that must already be setup in the stockmaster table. The api will check this before making the enquiry.');
	$Parameter[2]['name'] = _('Location');
	$Parameter[2]['description'] = _('A string field containing a valid location code that must already be setup in the locations table. The api will check this before making the enquiry.');
	$Parameter[3]['name'] = _('Quantity');
	$Parameter[3]['description'] = _('This is an integer value. It holds the amount of stock to be adjusted. Should be negative if is stock is to be reduced');
	$Parameter[4]['name'] = _('Transaction Date');
	$Parameter[4]['description'] = _('This is a string value. It holds the string that is searched for in the given field. It will search for all or part of the field.');
	$Parameter[5]['name'] = _('User name');
	$Parameter[5]['description'] = _('A valid weberp username. This user should have security access  to this data.');
	$Parameter[6]['name'] = _('User password');
	$Parameter[6]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('If successful this function returns 0. ')
			._('Otherwise an array of error codes is returned. ');

/*E*/	$WorkOrderReceive_sig = array(array($xmlrpcStruct,$xmlrpcString,$xmlrpcString,$xmlrpcString,$xmlrpcString,$xmlrpcString),
/*x*/					array($xmlrpcStruct,$xmlrpcString,$xmlrpcString,$xmlrpcString,$xmlrpcString,$xmlrpcString,$xmlrpcString,$xmlrpcString));
	$WorkOrderReceive_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_WorkOrderReceive($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 7)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(WorkOrderReceive($xmlrpcmsg->getParam( 0 )->scalarval(  ),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ),
/*x*/					$xmlrpcmsg->getParam( 3 )->scalarval(  ),
/*x*/						$xmlrpcmsg->getParam( 4 )->scalarval(  ),
/*x*/							$xmlrpcmsg->getParam( 5 )->scalarval(  ),
/*x*/								$xmlrpcmsg->getParam( 6 )->scalarval(  ))) );
/*x*/		} else {
		 $rtn = new xmlrpcresp( php_xmlrpc_encode(WorkOrderReceive($xmlrpcmsg->getParam( 0 )->scalarval(  ),
			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
				$xmlrpcmsg->getParam( 2 )->scalarval(  ),
					$xmlrpcmsg->getParam( 3 )->scalarval(  ),
/*e*/						$xmlrpcmsg->getParam( 4 )->scalarval(  ), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('Returns the webERP default date format');
	$Parameter[0]['name'] = _('User name');
	$Parameter[0]['description'] = _('A valid weberp username. This user should have security access  to this data.');
	$Parameter[1]['name'] = _('User password');
	$Parameter[1]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('If successful this function returns a string contain the default date format. ')
			._('Otherwise an array of error codes is returned. ');

/*E*/	$GetDefaultDateFormat_sig = array(array($xmlrpcStruct),
/*x*/					array($xmlrpcStruct,$xmlrpcString,$xmlrpcString));
	$GetDefaultDateFormat_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_GetDefaultDateFormat($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 2)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetDefaultDateFormat($xmlrpcmsg->getParam( 0 )->scalarval(  ),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ))) );
/*x*/		} else {
/*e*/ 			$rtn = new xmlrpcresp( php_xmlrpc_encode(GetDefaultDateFormat( '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('Returns the webERP default shipper');
	$Parameter[0]['name'] = _('User name');
	$Parameter[0]['description'] = _('A valid weberp username. This user should have security access  to this data.');
	$Parameter[1]['name'] = _('User password');
	$Parameter[1]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('If successful this function returns an array of two elements the first should contain an integer of zero for successful and the second an associative array containing the key of confvalue the value of which is the Default_Shipper.')
			._('Otherwise an array of error codes is returned. ');

/*E*/$GetDefaultShipper_sig = array(array($xmlrpcStruct),
/*x*/					array($xmlrpcStruct,$xmlrpcString,$xmlrpcString));
	$GetDefaultShipper_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_GetDefaultShipper($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 2)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetDefaultShipper($xmlrpcmsg->getParam( 0 )->scalarval(  ),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ))) );
/*x*/		} else {
/*e*/ 			$rtn = new xmlrpcresp( php_xmlrpc_encode(GetDefaultShipper( '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);



	$Description = _('Returns the webERP default location');
	$Parameter[0]['name'] = _('User name');
	$Parameter[0]['description'] = _('A valid weberp username. This user should have security access  to this data.');
	$Parameter[1]['name'] = _('User password');
	$Parameter[1]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('If successful this function returns a string contain the default location. ')
			._('Otherwise an array of error codes is returned. ');

/*E*/	$GetDefaultCurrency_sig = array(array($xmlrpcStruct),
/*x*/					array($xmlrpcStruct,$xmlrpcString,$xmlrpcString));
	$GetDefaultCurrency_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_GetDefaultCurrency($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 2)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetDefaultCurrency($xmlrpcmsg->getParam( 0 )->scalarval(  ),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ))) );
/*x*/		} else {
/*e*/ $rtn = new xmlrpcresp( php_xmlrpc_encode(GetDefaultCurrency( '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('Returns the webERP default price list');
	$Parameter[0]['name'] = _('User name');
	$Parameter[0]['description'] = _('A valid weberp username. This user should have security access  to this data.');
	$Parameter[1]['name'] = _('User password');
	$Parameter[1]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('If successful this function returns a string contain the default price list code. ')
			._('Otherwise an array of error codes is returned. ');

/*E*/	$GetDefaultPriceList_sig = array(array($xmlrpcStruct),
/*x*/					array($xmlrpcStruct,$xmlrpcString,$xmlrpcString));
	$GetDefaultPriceList_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_GetDefaultPriceList($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 2)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetDefaultPriceList($xmlrpcmsg->getParam( 0 )->scalarval(  ),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ))) );
/*x*/		} else {
/*e*/ $rtn = new xmlrpcresp( php_xmlrpc_encode(GetDefaultPriceList( '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('Returns the webERP default inventory location');
	$Parameter[0]['name'] = _('User name');
	$Parameter[0]['description'] = _('A valid weberp username. This user should have security access  to this data.');
	$Parameter[1]['name'] = _('User password');
	$Parameter[1]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('If successful this function returns a string contain the default inventory location. ')
			._('Otherwise an array of error codes is returned. ');

/*E*/	$GetDefaultLocation_sig = array(array($xmlrpcStruct),
/*x*/					array($xmlrpcStruct,$xmlrpcString,$xmlrpcString));
	$GetDefaultLocation_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_GetDefaultLocation($xmlrpcmsg){
		ob_start('ob_file_callback');
		if ($xmlrpcmsg->getNumParams() == 2){
			$rtn = new xmlrpcresp( php_xmlrpc_encode(GetDefaultLocation($xmlrpcmsg->getParam( 0 )->scalarval(  ), $xmlrpcmsg->getParam( 1 )->scalarval(  ))) );
		} else {
			$rtn = new xmlrpcresp( php_xmlrpc_encode(GetDefaultLocation( '', '')));
		}
		ob_end_flush();
		return $rtn;
	}

	$Description = _('Returns the webERP reports directory for the company selected');
	$Parameter[0]['name'] = _('User name');
	$Parameter[0]['description'] = _('A valid weberp username. This user should have security access  to this data.');
	$Parameter[1]['name'] = _('User password');
	$Parameter[1]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('If successful this function returns a string containing the path to the company reports directory') . ' ' . _('Otherwise an array of error codes is returned. ');

	$GetReportsDirectory_sig = array(array($xmlrpcStruct),
									array($xmlrpcStruct,$xmlrpcString,$xmlrpcString));
	$GetReportsDirectory_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_GetReportsDirectory($xmlrpcmsg){
		ob_start('ob_file_callback');
		if ($xmlrpcmsg->getNumParams() == 2) {
			$rtn = new xmlrpcresp( php_xmlrpc_encode(GetReportsDirectory($xmlrpcmsg->getParam( 0 )->scalarval(  ), $xmlrpcmsg->getParam( 1 )->scalarval(  ))) );
		} else {
			$rtn = new xmlrpcresp( php_xmlrpc_encode(GetReportsDirectory( '', '')));
		}
		ob_end_flush();
		return $rtn;
	}
	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('This function creates a POS data file on the webERP server for download by the POS');
	$Parameter[0]['name'] = _('POS Customer Code - a valid webERP customer that sales from the POS are made against.');
	$Parameter[0]['description'] = _('POS Customer Branch Code - a valid branch code of the webERP customer that the POS sales are made against');
	$Parameter[1]['name'] = _('User name');
	$Parameter[1]['description'] = _('A valid weberp username. This user should have security access  to this data.');
	$Parameter[2]['name'] = _('User name');
	$Parameter[2]['description'] = _('A valid weberp username. This user should have security access  to this data.');
	$Parameter[3]['name'] = _('User password');
	$Parameter[3]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('If successful this function returns 0 for success and 1 for error. ');

	$CreatePOSDataFull_sig = array(array($xmlrpcStruct,$xmlrpcString,$xmlrpcString,$xmlrpcString,$xmlrpcString));
	$CreatePOSDataFull_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_CreatePOSDataFull($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 4) {
/*x*/			$rtn = new xmlrpcresp( php_xmlrpc_encode(CreatePOSDataFull($xmlrpcmsg->getParam( 0 )->scalarval(  ),
/*x*/																		$xmlrpcmsg->getParam( 1 )->scalarval(  ),
																			$xmlrpcmsg->getParam( 2 )->scalarval(  ),
																			$xmlrpcmsg->getParam( 3 )->scalarval(  ))) );
/*x*/		} else {
/*e*/ 			$rtn = new xmlrpcresp( php_xmlrpc_encode(CreatePOSDataFull( $xmlrpcmsg->getParam( 0 )->scalarval(  ),
/*x*/																		$xmlrpcmsg->getParam( 1 )->scalarval(  ),
																			'',
																			'')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('This function deletes a POS data file on the webERP server');
	$Parameter[0]['name'] = _('User name');
	$Parameter[0]['description'] = _('A valid weberp username. This user should have security access  to this data.');
	$Parameter[1]['name'] = _('User password');
	$Parameter[1]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('Returns 0 if the delete POS Data was successful');

/*E*/$DeletePOSData_sig = array(array($xmlrpcStruct,$xmlrpcString,$xmlrpcString));
	$DeletePOSData_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );


	function  xmlrpc_DeletePOSData($xmlrpcmsg){
		ob_start('ob_file_callback');
		if ($xmlrpcmsg->getNumParams() == 2) {
			$rtn = new xmlrpcresp( php_xmlrpc_encode(DeletePOSData($xmlrpcmsg->getParam( 0 )->scalarval(  ),
																	$xmlrpcmsg->getParam( 1 )->scalarval(  ) )) );
		} else {
			$rtn = new xmlrpcresp( php_xmlrpc_encode(DeletePOSData( '','')));
		}
		ob_end_flush();
		return $rtn;
	}


	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('Returns the value of the specified stock category property for the specified stock item category');
	$Parameter[0]['name'] = _('Property');
	$Parameter[0]['description'] = _('The name of the specific property to be returned.');
	$Parameter[1]['name'] = _('Stock ID');
	$Parameter[1]['description'] = _('The ID of the stock item for which the value of the above property is required. ');
	$Parameter[2]['name'] = _('User name');
	$Parameter[2]['description'] = _('A valid weberp username. This user should have security access  to this data.');
	$Parameter[3]['name'] = _('User password');
	$Parameter[3]['description'] = _('The weberp password associated with this user name. ');
	$ReturnValue[0] = _('If successful this function returns zero, and the value of the requested property. ')
			._('Otherwise an array of error codes is returned. ');

/*E*/	$GetStockCatProperty_sig = array(array($xmlrpcStruct,$xmlrpcString,$xmlrpcString),
/*x*/					array($xmlrpcStruct,$xmlrpcString,$xmlrpcString,$xmlrpcString,$xmlrpcString));
	$GetStockCatProperty_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_GetStockCatProperty($xmlrpcmsg){
		ob_start('ob_file_callback');
/*x*/		if ($xmlrpcmsg->getNumParams() == 4)
/*x*/		{
/*x*/		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetStockCatProperty($xmlrpcmsg->getParam( 0 )->scalarval(  ),
/*x*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ),
/*x*/				$xmlrpcmsg->getParam( 2 )->scalarval(  ),
/*x*/					$xmlrpcmsg->getParam( 3 )->scalarval(  ))) );
/*x*/		} else {
		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetStockCatProperty($xmlrpcmsg->getParam( 0 )->scalarval(  ),
/*e*/			$xmlrpcmsg->getParam( 1 )->scalarval(  ), '', '')));
/*x*/		}
		ob_end_flush();
		return $rtn;
	}

	unset($Description);
	unset($Parameter);
	unset($ReturnValue);

	$Description = _('Returns (possibly translated) error text from error codes');
	$Parameter[0]['name'] = _('Error codes');
	$Parameter[0]['description'] = _('An array of error codes to change into text messages. ');
	$ReturnValue[0] = _('An array of two element arrays, one per error code. The second array has the error code in element 0 and the error string in element 1. ');
	$GetErrorMessages_sig = array(array($xmlrpcStruct,$xmlrpcArray));
	$GetErrorMessages_doc = apiBuildDocHTML( $Description,$Parameter,$ReturnValue );

	function  xmlrpc_GetErrorMessages($xmlrpcmsg){
		ob_start('ob_file_callback');
		 $rtn = new xmlrpcresp( php_xmlrpc_encode(GetAPIErrorMessages(php_xmlrpc_decode($xmlrpcmsg->getParam( 0 )))) );
		ob_end_flush();
		return $rtn;
	}


	$s = new xmlrpc_server( array(
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
		)
	);


//  Generate the HTMLised description string for each API.

function apiBuildDocHTML( $description, $parameter, $return )
{
	$doc = '<tr><td><b><u>' . _('Description') . '</u></b></td><td colspan=2>' .$description . '</td></tr>
			<tr><td valign="top"><b><u>' . _('Parameters') . '</u></b></td>';
	for ($ii=0; $ii<sizeof($parameter); $ii++) {
		$doc .= '<tr><td valign="top">' . $parameter[$ii]['name'] . '</td><td>' . 
			$parameter[$ii]['description'] . '</td></tr>';
	}
	$doc .= '<tr><td valign="top"><b><u>' . _('Return Value');
	for ($ii=0; $ii<sizeof($return); $ii++) {
		$doc .= '<td valign="top">' . $return[$ii] . '</td></tr>';
	}
	$doc .= '</table>';

	return  $doc;
}
?>
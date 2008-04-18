<?php

	include 'api_php.php';

	include '../xmlrpc/lib/xmlrpc.inc';
	include '../xmlrpc/lib/xmlrpcs.inc';
	
	function xmlrpc_InsertCustomer($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(InsertCustomer(php_xmlrpc_decode($xmlrpcmsg->getParam(0)),
				 $xmlrpcmsg->getParam(1)->scalarval(), 
				 		$xmlrpcmsg->getParam(2)->scalarval())));
	}
	
	function xmlrpc_ModifyCustomer($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(ModifyCustomer(php_xmlrpc_decode($xmlrpcmsg->getParam(0)),
				 $xmlrpcmsg->getParam(1)->scalarval(), 
				 		$xmlrpcmsg->getParam(2)->scalarval())));
	}

	function xmlrpc_GetCustomer($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(GetCustomer($xmlrpcmsg->getParam(0)->scalarval(),
				 $xmlrpcmsg->getParam(1)->scalarval(), 
				 		$xmlrpcmsg->getParam(2)->scalarval())));
	}
	
	function xmlrpc_SearchCustomers($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(SearchCustomers($xmlrpcmsg->getParam(0)->scalarval(),
				 $xmlrpcmsg->getParam(1)->scalarval(), 
				 		$xmlrpcmsg->getParam(2)->scalarval(),
				 			$xmlrpcmsg->getParam(3)->scalarval())));
	}
	
	function xmlrpc_GetCurrencyList($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(GetCurrencyList($xmlrpcmsg->getParam(0)->scalarval(),
			$xmlrpcmsg->getParam(1)->scalarval())));
	}

	function xmlrpc_GetCurrencyDetails($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(GetCurrencyList($xmlrpcmsg->getParam(0)->scalarval(),
			$xmlrpcmsg->getParam(1)->scalarval(),
				$xmlrpcmsg->getParam(2)->scalarval())));
	}
	
	function xmlrpc_GetSalesTypeList($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(GetSalesTypeList($xmlrpcmsg->getParam(0)->scalarval(),
			$xmlrpcmsg->getParam(1)->scalarval())));
	}

	function xmlrpc_GetSalesTypeDetails($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(GetSalesTypeList($xmlrpcmsg->getParam(0)->scalarval(),
			$xmlrpcmsg->getParam(1)->scalarval(),
				$xmlrpcmsg->getParam(2)->scalarval())));
	}

	$InsertCustomer_sig = array(array($xmlrpcStruct, $xmlrpcStruct, $xmlrpcString, $xmlrpcString));
	$InsertCustomer_doc = 'This function takes an associative array containing the details of a customer to
			to be inserted, where the keys of the array are the field names in the table debtorsmaster. ';
	$ModifyCustomer_sig = array(array($xmlrpcStruct, $xmlrpcStruct, $xmlrpcString, $xmlrpcString));
	$ModifyCustomer_doc = 'This function takes an associative array containing the details of a customer to
			to be updated, where the keys of the array are the field names in the table debtorsmaster. ';
	$GetCustomer_sig = array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcString, $xmlrpcString));
	$GetCustomer_doc = 'This function returns an associative array containing the details of the customer
			whose account number is passed to it.';
	$SearchCustomers_sig = array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcString));
	$SearchCustomers_doc = 'This function returns an array containing the account numbers of those customers
			that meet the criteria given. Any field in debtorsmaster can be search on.';
	$GetCurrencyList_sig = array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcString));
	$GetCurrencyList_doc = 'This function returns an array containing a list of all currencies setup on webERP';
	$GetCurrencyDetails_sig = array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcString, $xmlrpcString));
	$GetCurrencyDetails_doc = 'This function returns an associative array containing the details of the currency
			 sent as a parameter';
	$GetSalesTypeList_sig = array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcString));
	$GetSalesTypeList_doc = 'This function returns an array containing a list of all sales types setup on webERP';
	$GetSalesTypeDetails_sig = array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcString, $xmlrpcString));
	$GetSalesTypeDetails_doc = 'This function returns an associative array containing the details of the sales type
			 sent as a parameter';

	$s = new xmlrpc_server( array(
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
			"docstring" => $GetSalesTypeDetails_doc)
		)
	);

?>
<?php

	include 'api_php.php';

	include '../xmlrpc/lib/xmlrpc.inc';
	include '../xmlrpc/lib/xmlrpcs.inc';


	$InsertCustomer_sig = array(array($xmlrpcStruct, $xmlrpcStruct, $xmlrpcString, $xmlrpcString));
	$InsertCustomer_doc = 'This function takes an associative array containing the details of a customer to
			to be inserted, where the keys of the array are the field names in the table debtorsmaster. ';

	function xmlrpc_InsertCustomer($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(InsertCustomer(php_xmlrpc_decode($xmlrpcmsg->getParam(0)),
				 $xmlrpcmsg->getParam(1)->scalarval(),
				 		$xmlrpcmsg->getParam(2)->scalarval())));
	}

	$InsertBranch_sig = array(array($xmlrpcStruct, $xmlrpcStruct, $xmlrpcString, $xmlrpcString));
	$InsertBranch_doc = 'This function takes an associative array containing the details of a branch to
			to be inserted, where the keys of the array are the field names in the table debtorsmaster. ';

	function xmlrpc_InsertBranch($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(InsertBranch(php_xmlrpc_decode($xmlrpcmsg->getParam(0)),
				 $xmlrpcmsg->getParam(1)->scalarval(),
				 		$xmlrpcmsg->getParam(2)->scalarval())));
	}

	$ModifyCustomer_sig = array(array($xmlrpcStruct, $xmlrpcStruct, $xmlrpcString, $xmlrpcString));
	$ModifyCustomer_doc = 'This function takes an associative array containing the details of a customer to
			to be updated, where the keys of the array are the field names in the table debtorsmaster. ';

	function xmlrpc_ModifyCustomer($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(ModifyCustomer(php_xmlrpc_decode($xmlrpcmsg->getParam(0)),
				 $xmlrpcmsg->getParam(1)->scalarval(),
				 		$xmlrpcmsg->getParam(2)->scalarval())));
	}

	$ModifyBranch_sig = array(array($xmlrpcStruct, $xmlrpcStruct, $xmlrpcString, $xmlrpcString));
	$ModifyBranch_doc = 'This function takes an associative array containing the details of a branch to
			to be updated, where the keys of the array are the field names in the table debtorsmaster. ';

	function xmlrpc_ModifyBranch($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(ModifyBranch(php_xmlrpc_decode($xmlrpcmsg->getParam(0)),
				 $xmlrpcmsg->getParam(1)->scalarval(),
				 		$xmlrpcmsg->getParam(2)->scalarval())));
	}

	$GetCustomer_sig = array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcString, $xmlrpcString));
	$GetCustomer_doc = 'This function returns an associative array containing the details of the customer
			whose account number is passed to it.';

	function xmlrpc_GetCustomer($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(GetCustomer($xmlrpcmsg->getParam(0)->scalarval(),
				 $xmlrpcmsg->getParam(1)->scalarval(),
				 		$xmlrpcmsg->getParam(2)->scalarval())));
	}

	$SearchCustomers_sig = array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcString));
	$SearchCustomers_doc = 'This function returns an array containing the account numbers of those customers
			that meet the criteria given. Any field in debtorsmaster can be search on.';

	function xmlrpc_SearchCustomers($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(SearchCustomers($xmlrpcmsg->getParam(0)->scalarval(),
				 $xmlrpcmsg->getParam(1)->scalarval(),
				 		$xmlrpcmsg->getParam(2)->scalarval(),
				 			$xmlrpcmsg->getParam(3)->scalarval())));
	}

	$GetCurrencyList_sig = array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcString));
	$GetCurrencyList_doc = 'This function returns an array containing a list of all currencies setup on webERP';

	function xmlrpc_GetCurrencyList($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(GetCurrencyList($xmlrpcmsg->getParam(0)->scalarval(),
			$xmlrpcmsg->getParam(1)->scalarval())));
	}

	$GetCurrencyDetails_sig = array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcString, $xmlrpcString));
	$GetCurrencyDetails_doc = 'This function returns an associative array containing the details of the currency
			 sent as a parameter';

	function xmlrpc_GetCurrencyDetails($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(GetCurrencyDetails($xmlrpcmsg->getParam(0)->scalarval(),
			$xmlrpcmsg->getParam(1)->scalarval(),
				$xmlrpcmsg->getParam(2)->scalarval())));
	}

	$GetSalesTypeList_sig = array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcString));
	$GetSalesTypeList_doc = 'This function returns an array containing a list of all sales types setup on webERP';

	function xmlrpc_GetSalesTypeList($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(GetSalesTypeList($xmlrpcmsg->getParam(0)->scalarval(),
			$xmlrpcmsg->getParam(1)->scalarval())));
	}

	$GetSalesTypeDetails_sig = array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcString, $xmlrpcString));
	$GetSalesTypeDetails_doc = 'This function returns an associative array containing the details of the sales type
			 sent as a parameter';

	function xmlrpc_GetSalesTypeDetails($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(GetSalesTypeDetails($xmlrpcmsg->getParam(0)->scalarval(),
			$xmlrpcmsg->getParam(1)->scalarval(),
				$xmlrpcmsg->getParam(2)->scalarval())));
	}

	$GetHoldReasonList_sig = array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcString));
	$GetHoldReasonList_doc = 'This function returns an array containing a list of all hold reason codes setup on webERP';

	function xmlrpc_GetHoldReasonList($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(GetHoldReasonList($xmlrpcmsg->getParam(0)->scalarval(),
			$xmlrpcmsg->getParam(1)->scalarval())));
	}

	$GetHoldReasonDetails_sig = array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcString, $xmlrpcString));
	$GetHoldReasonDetails_doc = 'This function returns an associative array containing the details of the hold reason
			 sent as a parameter';

	function xmlrpc_GetHoldReasonDetails($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(GetHoldReasonDetails($xmlrpcmsg->getParam(0)->scalarval(),
			$xmlrpcmsg->getParam(1)->scalarval(),
				$xmlrpcmsg->getParam(2)->scalarval())));
	}

	$GetPaymentTermsList_sig = array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcString));
	$GetPaymentTermsList_doc = 'This function returns an array containing a list of all payment terms setup on webERP';

	function xmlrpc_GetPaymentTermsList($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(GetPaymentTermsList($xmlrpcmsg->getParam(0)->scalarval(),
			$xmlrpcmsg->getParam(1)->scalarval())));
	}

	$GetPaymentTermsDetails_sig = array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcString, $xmlrpcString));
	$GetPaymentTermsDetails_doc = 'This function returns an associative array containing the details of the payment terms
			 sent as a parameter';

	function xmlrpc_GetPaymentTermsDetails($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(GetPaymentTermsDetails($xmlrpcmsg->getParam(0)->scalarval(),
			$xmlrpcmsg->getParam(1)->scalarval(),
				$xmlrpcmsg->getParam(2)->scalarval())));
	}

	$InsertStockItem_sig = array(array($xmlrpcStruct, $xmlrpcStruct, $xmlrpcString, $xmlrpcString));
	$InsertStockItem_doc = 'This function takes an associative array containing the details of a stock item to
			to be inserted, where the keys of the array are the field names in the table stockmaster. ';

	function xmlrpc_InsertStockItem($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(InsertStockItem(php_xmlrpc_decode($xmlrpcmsg->getParam(0)),
				 $xmlrpcmsg->getParam(1)->scalarval(),
				 		$xmlrpcmsg->getParam(2)->scalarval())));
	}

	$ModifyStockItem_sig = array(array($xmlrpcStruct, $xmlrpcStruct, $xmlrpcString, $xmlrpcString));
	$ModifyStockItem_doc = 'This function takes an associative array containing the details of a stock item to
			to be updated, where the keys of the array are the field names in the table stockmaster. ';

	function xmlrpc_ModifyStockItem($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(ModifyStockItem(php_xmlrpc_decode($xmlrpcmsg->getParam(0)),
				 $xmlrpcmsg->getParam(1)->scalarval(),
				 		$xmlrpcmsg->getParam(2)->scalarval())));
	}

	$GetStockItem_sig = array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcString, $xmlrpcString));
	$GetStockItem_doc = 'This function returns an associative array containing the details of the item
			whose stockid is passed to it.';

	function xmlrpc_GetStockItem($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(GetStockItem($xmlrpcmsg->getParam(0)->scalarval(),
				 $xmlrpcmsg->getParam(1)->scalarval(),
				 		$xmlrpcmsg->getParam(2)->scalarval())));
	}

	$SearchStockItems_sig = array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcString));
	$SearchStockItems_doc = 'This function returns an array containing the account numbers of those items
			that meet the criteria given. Any field in stockmaster can be search on.';

	function xmlrpc_SearchStockItems($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(SearchStockItems($xmlrpcmsg->getParam(0)->scalarval(),
				 $xmlrpcmsg->getParam(1)->scalarval(),
				 		$xmlrpcmsg->getParam(2)->scalarval(),
				 			$xmlrpcmsg->getParam(3)->scalarval())));
	}

	$GetStockBalance_sig = array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcString));
	$GetStockBalance_doc = 'This function returns the quantity of stock on hand a the location given';

	function xmlrpc_GetStockBalance($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(GetStockBalance($xmlrpcmsg->getParam(0)->scalarval(),
				 $xmlrpcmsg->getParam(1)->scalarval(),
				 		$xmlrpcmsg->getParam(2)->scalarval(),
				 			$xmlrpcmsg->getParam(3)->scalarval())));
	}

	$InsertSalesInvoice_sig = array(array($xmlrpcStruct, $xmlrpcStruct, $xmlrpcString, $xmlrpcString));
	$InsertSalesInvoice_doc = 'This function inserts a sales invoice into webERP';

	function xmlrpc_InsertSalesInvoice($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(InsertSalesInvoice(php_xmlrpc_decode($xmlrpcmsg->getParam(0)),
				 $xmlrpcmsg->getParam(1)->scalarval(),
				 		$xmlrpcmsg->getParam(2)->scalarval())));
	}

	$InsertSalesCredit_sig = array(array($xmlrpcStruct, $xmlrpcStruct, $xmlrpcString, $xmlrpcString));
	$InsertSalesCredit_doc = 'This function inserts a sales credit note into webERP';

	function xmlrpc_InsertSalesCredit($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(InsertSalesCedit(php_xmlrpc_decode($xmlrpcmsg->getParam(0)),
				 $xmlrpcmsg->getParam(1)->scalarval(),
				 		$xmlrpcmsg->getParam(2)->scalarval())));
	}

	$InsertSalesOrderHeader_sig = array(array($xmlrpcStruct, $xmlrpcStruct, $xmlrpcString, $xmlrpcString));
	$InsertSalesOrderHeader_doc = 'This function inserts a sales order header into webERP';

	function xmlrpc_InsertSalesOrderHeader($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(InsertSalesOrderHeader(php_xmlrpc_decode($xmlrpcmsg->getParam(0)),
				 $xmlrpcmsg->getParam(1)->scalarval(),
				 		$xmlrpcmsg->getParam(2)->scalarval())));
	}

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
			"docstring" => $GetSalesTypeDetails_doc),
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
		"weberp.xmlrpc_InsertSalesInvoice" => array(
			"function" => "xmlrpc_InsertSalesInvoice",
			"signature" => $InsertSalesInvoice_sig,
			"docstring" => $InsertSalesInvoice_doc),
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
		"weberp.xmlrpc_InsertSalesOrderHeader" => array(
			"function" => "xmlrpc_InsertSalesOrderHeader",
			"signature" => $InsertSalesOrderHeader_sig,
			"docstring" => $InsertSalesOrderHeader_doc),
		)
	);

?>
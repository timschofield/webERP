<?php

require_once(__DIR__ . '/../src/ApiTestCase.php');

class XXX_ApiTest extends ApiTestCase
{
	/**
	 * Send an XMLRPC request listing all available methods, check that at least 'weberp.xmlrpc_Login' is registered
	 */
	public function testListMethods()
	{
		$methods = $this->request('system.listMethods');
		$this->assertContains('weberp.xmlrpc_Login', $methods);
	}

	public function testLogin()
	{
		$result = $this->request('weberp.xmlrpc_Login', [$_ENV['TEST_DB_SCHEMA'], $_ENV['TEST_USER_ACCOUNT'], $_ENV['TEST_USER_PASSWORD']]);
		$this->assertEquals(0, $result[0], 'method weberp.xmlrpc_Login should return 0');
	}

	public function testFailedLogin()
	{
		$result = $this->request('weberp.xmlrpc_Login', [$_ENV['TEST_DB_SCHEMA'], $_ENV['TEST_USER_ACCOUNT'], $_ENV['TEST_USER_PASSWORD'] . '_xxx']);
		$this->assertEquals(1, $result[0], 'method weberp.xmlrpc_Login should return 1 for bad logins');
	}

	public function testAnonCall()
	{
		$result = $this->request('weberp.xmlrpc_GetLocationList');
		$this->assertEquals(1, $result[0], 'method weberp.xmlrpc_GetLocationList should return 1 for no logins');
	}

	public function testInCallAuth()
	{
		$result = $this->request('weberp.xmlrpc_GetLocationList', [$_ENV['TEST_USER_ACCOUNT'], $_ENV['TEST_USER_PASSWORD']]);
		$this->assertEquals(0, $result[0], 'method weberp.xmlrpc_GetLocationList should return 0 for in-call logins');
		$this->assertIsArray($result[1], 'method weberp.xmlrpc_GetLocationList should return data for in-call logins');
	}

	public function testSessionAuth()
	{
		$result = $this->request('weberp.xmlrpc_Login', [$_ENV['TEST_DB_SCHEMA'], $_ENV['TEST_USER_ACCOUNT'], $_ENV['TEST_USER_PASSWORD']]);
		$this->assertEquals(0, $result[0], 'method weberp.xmlrpc_Login should return 0');
		$cookies = $this->response->cookies();
		$this->client->setCookie('webERPapi', $cookies['webERPapi']['value'], $cookies['webERPapi']['path']);
		$result = $this->request('weberp.xmlrpc_GetLocationList');
		$this->assertEquals(0, $result[0], 'method weberp.xmlrpc_GetLocationList should return 0 for session logins');
		$this->assertIsArray($result[1], 'method weberp.xmlrpc_GetLocationList should return data for in-call logins');
	}

	/// @todo add more tests - call every method, build the list by looking into api_xml-rpc_definition.php
}

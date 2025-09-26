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

	/// @todo add more tests - at least one doing log-in, get some data, log-out (both with session-cookie and user/pwd in call)
}

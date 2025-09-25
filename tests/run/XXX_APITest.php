<?php

require_once(__DIR__ . '/../src/AnonymousUserTestCase.php');

use PhpXmlRpc\Client;
use PhpXmlRpc\Encoder;
use PhpXmlRpc\Request;

class XXX_APITest extends AnonymousUserTestCase
{
	/**
	 * Send an XMLRPC request listing all available methods, check that at least 'weberp.xmlrpc_Login' is registered
	 */
	public function testListMethods()
	{
		$methods = $this->apiRequest('system.listMethods');
		$this->assertContains('weberp.xmlrpc_Login', $methods);
	}

	public function testLogin()
	{
		$result = $this->apiRequest('weberp.xmlrpc_Login', [$_ENV['TEST_DB_SCHEMA'], $_ENV['TEST_USER_ACCOUNT'], $_ENV['TEST_USER_PASSWORD']]);
		$this->assertEquals(0, $result[0], 'method weberp.xmlrpc_Login should return 0');
	}

	/// @todo add more tests - at least one doing log-in, get some data, log-out

	/**
	 * @param string $method
	 * @param array $args
	 * @return mixed
	 */
	protected function apiRequest($method, array $args = array())
	{
		$client = new Client(self::$baseUri . '/api/api_xml-rpc.php');
		$e = new Encoder();
		foreach($args as &$arg) {
			$arg = $e->encode($arg);
		}
		$response = $client->send(new Request($method, $args));
		$this->assertEquals(0, $response->faultCode(), 'The xmlrpc response has a fault code');
		$value = $response->value();
		return $e->decode($value);
	}
}

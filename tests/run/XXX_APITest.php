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

	protected function apiRequest($method, $args = array())
	{
		$client = new Client(self::$baseUri . '/api/api_xml-rpc.php');
		$e = new Encoder();
		if ($args) {
			foreach($args as &$arg) {
				$arg = $e->encode($arg);
			}
		}
		$response = $client->send(new Request($method, $args));
		$this->assertEquals(0, $response->faultCode(), 'The xmlrpc response has a fault code');
		$value = $response->value();
		return $e->decode($value);
	}
}

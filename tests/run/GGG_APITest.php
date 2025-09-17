<?php

require_once(__DIR__ . '/../src/AnonymousUserTestCase.php');

use PhpXmlRpc\Client;
use PhpXmlRpc\Encoder;
use PhpXmlRpc\Request;

class GGG_APITest extends AnonymousUsersTest
{

	public function testListMethods()
	{

	}

	protected function apiRequest($method, $args = array())
	{
		$client = new Client(self::$rootDir . '/api/api_xml-rpc.php');
		if ($args) {
			$e = new Encoder();
			foreach($args as &$arg) {
				$arg = $e->encode($arg);
			}
		}
		$response = $client->send(new Request($method, $args));
	}
}

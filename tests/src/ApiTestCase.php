<?php

require_once(__DIR__ . '/HttpTestCase.php');

use PHPUnit\Framework\TestStatus;
use PhpXmlRpc\Client;
use PhpXmlRpc\Encoder;
use PhpXmlRpc\Request;
use PhpXmlRpc\Response;

class ApiTestCase extends HttpTestCase
{
	/** @var null|Client */
	protected $client;
	/** @var null|Response */
	protected $response;
	/** @var int[] */
	protected $expectedFaultCodes = [0];

	/**
	 * Runs once before each test method of this object
	 */
	public function setUp(): void
	{
		$this->client = new Client(self::$baseUri . '/api/api_xml-rpc.php?phpunit_rand_id=' . self::$randId);
		// disable compression of responses, to help troubleshooting
		$this->client->setOption(Client::OPT_ACCEPTED_COMPRESSION, []);

		$this->response = null;

		parent::setUp();
	}

	/**
	 * Runs once after each test method of this object
	 */
	public function tearDown(): void
	{
		// Save a "screenshot" of the api response on which the test error failed
		// NB: we have to check $testStatus here instead of using method `onNotSuccessfulTest` because that one is
		// called later in the execution, so it will not be able to use `$this->response`
		$testStatus =  $this->status();
		if ($testStatus instanceof TestStatus\Failure || $testStatus instanceof TestStatus\Error) {
			if ($this->response) {
				/// @todo add a timestamp suffix to the filename, and/or file/line nr. of the exception.
				///       Also, the url being requested
				$testName = get_class($this) . '_' . $this->name() .
					($this->executingTestIdentifier != '' ? '_' . $this->executingTestIdentifier : '');
				// we save responses as .php files, so that they do not disclose any information if accessed from the web
				file_put_contents($_ENV['TEST_ERROR_SCREENSHOTS_DIR'] . '/api_call_failing_' . $testName. '.php',
					"<?php return " . var_export($this->response->httpResponse()['raw_data'] . ";", true));
			}
		}

		$this->client = null;
		$this->response = null;

		parent::tearDown();
	}

	protected function getResponse(): null|Response
	{
		return $this->response;
	}

	protected function setExpectedFaultCodes(array $codes): void
	{
		$this->expectedFaultCodes = $codes;
	}

	/**
	 * @param string $method
	 * @param array $args
	 * @return mixed
	 */
	protected function request($method, array $args = array())
	{
		$e = new Encoder();
		foreach($args as &$arg) {
			$arg = $e->encode($arg);
		}
		$this->response = $this->client->send(new Request($method, $args));
		$this->assertContains($this->response->faultCode(), $this->expectedFaultCodes, 'The xmlrpc response has an unexpected fault code');
		$value = $this->response->value();
		return $e->decode($value);
	}
}

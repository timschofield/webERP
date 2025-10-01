<?php

require_once(__DIR__ . '/HttpBrowser.php');

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\HttpClient;

class HttpTestCase extends  TestCase
{
	/** @var string */
	protected static $rootDir;
	/** @var string */
	protected static $baseUri;
	/** @var string */
	protected static $randId;
	/** @var string */
	protected $executingTestIdentifier;
	/** @var string */
	protected static $errorPrependString = '';
	/** @var string */
	protected static $errorAppendString = '';
	/** @var bool|null */
	protected static $serverRunsXDebug;

	/**
	 * Runs once before all the test methods of this object
	 */
	public static function setUpBeforeClass(): void
	{
		parent::setUpBeforeClass();

		if (self::$rootDir == '') {
			self::$rootDir = realpath(__DIR__ . '/../..');
		}

		self::$baseUri = $_ENV['TEST_TARGET_PROTOCOL'] . '://'. $_ENV['TEST_TARGET_HOSTNAME'] .
			($_ENV['TEST_TARGET_PORT'] != '' ? (':' . ltrim($_ENV['TEST_TARGET_PORT'], ':')) : '') .
			rtrim($_ENV['TEST_TARGET_BASE_URL'], '/');

		/// @todo  This is a file which can be read by pages server-side to validate that the request is coming from the test
		///        It remains to be done: 1. send self::$randId as cookie on every http request, and 2. check for it server-side
		self::$randId = uniqid();
		file_put_contents(sys_get_temp_dir() . '/phpunit_rand_id.txt', self::$randId);

		self::checkPHPConfiguration();
	}

	/**
	 * Runs once after all the test methods of this object
	 */
	public static function tearDownAfterClass(): void
	{
		if (is_file(sys_get_temp_dir() . '/phpunit_rand_id.txt')) {
			unlink(sys_get_temp_dir() . '/phpunit_rand_id.txt');
		}

		parent::tearDownAfterClass();
	}

	/**
	 * Runs a prerequisite check: check for presence of required extensions and php.ini settings
	 */
	protected static function checkPHPConfiguration()
	{
		$browser = new HttpBrowser(HttpClient::create());
		$browser->request('GET', self::$baseUri . '/tests/setup/php_config_check.php?phpunit_rand_id=' . self::$randId);

		self::assertEquals(200, $browser->getResponse()->getStatusCode(), 'The server-side php configuration for running the test suite could not be checked');
		self::assertEquals('application/json', $browser->getResponse()->getHeader('Content-Type'), 'The server-side php configuration for running the test suite could not be checked: non-json data received');
		$config = @json_decode($browser->getResponse()->getContent(), true);
		self::assertArrayHasKey('active_extensions', $config, 'The server-side php configuration for running the test suite could not be checked: unexpected data received');
		self::$serverRunsXDebug = in_array('xdebug', $config['active_extensions']);
		self::assertArrayHasKey('ini_settings', $config, 'The server-side php configuration for running the test suite could not be checked: unexpected data received');
		self::assertEquals(E_ALL, (int)$config['ini_settings']['error_reporting'], 'The server-side php configuration is not correct for running the test suite: error_reporting is not set to E_ALL');
		self::assertEquals(true, (bool)$config['ini_settings']['display_errors'], 'The server-side php configuration is not correct for running the test suite: display_errors is not set to true');
		self::assertNotEquals('', (string)$config['ini_settings']['error_prepend_string'], 'The server-side php configuration is not correct for running the test suite: error_prepend_string is null');
		self::assertNotEquals('', (string)$config['ini_settings']['error_append_string'], 'The server-side php configuration is not correct for running the test suite: error_append_string is null');
		self::$errorPrependString = $config['ini_settings']['error_prepend_string'];
		self::$errorAppendString = $config['ini_settings']['error_append_string'];
	}
}

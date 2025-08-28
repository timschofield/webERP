<?php

require_once(__DIR__ . '/HttpBrowser.php');

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\HttpClient;

class WebTestCase extends TestCase
{
	/** @var string */
	protected static $randId;
	/** @var string */
	protected static $baseUri;
	/** @var HttpBrowser */
	protected $browser;

	public static function setUpBeforeClass(): void
	{
		parent::setUpBeforeClass();

		// Set up a database connection or other fixture which needs to be available.
		self::$randId = uniqid();
		file_put_contents(sys_get_temp_dir() . '/phpunit_rand_id.txt', self::$randId);
	}

	public static function tearDownAfterClass(): void
	{
		if (is_file(sys_get_temp_dir() . '/phpunit_rand_id.txt')) {
			unlink(sys_get_temp_dir() . '/phpunit_rand_id.txt');
		}

		parent::tearDownAfterClass();
	}

	public function setUp(): void
	{
		self::$baseUri = $_ENV['TEST_TARGET_PROTOCOL'] . '://'. $_ENV['TEST_TARGET_HOSTNAME'] .
			($_ENV['TEST_TARGET_PORT'] != '' ? (':' . ltrim($_ENV['TEST_TARGET_PORT'], ':')) : '') .
			rtrim($_ENV['TEST_TARGET_BASE_URL'], '/');
		$this->browser = new HttpBrowser(HttpClient::create());

		parent::setUp();
	}

	public function tearDown(): void
	{
		$this->browser = null;

		parent::tearDown();
	}
}

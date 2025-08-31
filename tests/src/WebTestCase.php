<?php

require_once(__DIR__ . '/HttpBrowser.php');

use PHPUnit\Framework\TestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;

/**
 * The test-case class to be used as base for all tests which need to make an http call to webERP.
 * The details for the connection to the webserver come from either env vars or phpunit.xml values
 */
class WebTestCase extends TestCase
{
	/** @var string */
	protected static $rootDir;
	/** @var string */
	protected static $baseUri;
	/** @var string */
	protected static $randId;
	/** @var HttpBrowser */
	protected $browser;

	/**
	 * Runs once before all the test methods of this object
	 */
	public static function setUpBeforeClass(): void
	{
		parent::setUpBeforeClass();

		if (self::$rootDir == '') {
			self::$rootDir = realpath(__DIR__ . '/../..');
		}

		self::$randId = uniqid();
		file_put_contents(sys_get_temp_dir() . '/phpunit_rand_id.txt', self::$randId);
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
	 * Runs once before each test method of this object
	 */
	public function setUp(): void
	{
		self::$baseUri = $_ENV['TEST_TARGET_PROTOCOL'] . '://'. $_ENV['TEST_TARGET_HOSTNAME'] .
			($_ENV['TEST_TARGET_PORT'] != '' ? (':' . ltrim($_ENV['TEST_TARGET_PORT'], ':')) : '') .
			rtrim($_ENV['TEST_TARGET_BASE_URL'], '/');
		$this->browser = new HttpBrowser(HttpClient::create());

		parent::setUp();
	}

	/**
	 * Runs once after each test method of this object
	 */
	public function tearDown(): void
	{
		$this->browser = null;

		parent::tearDown();
	}

	// *** Functions useful for usage in subclasses ***

	protected function request(string $method, string $uri, array $parameters = [], array $files = [], array $server = [], ?string $content = null, bool $changeHistory = true): Crawler
	{
		return $this->browser->request($method, $uri, $parameters, $files, $server, $content, $changeHistory);
	}

	protected function followRedirects(bool $followRedirects = true): void
	{
		$this->browser->followRedirects($followRedirects);
	}

	protected function setExpectedStatusCodes(array $codes): void
	{
		$this->setExpectedStatusCodes($codes);
	}

	protected function clickLink(string $linkText): Crawler
	{
		return $this->browser->clickLink($linkText);
	}

	protected function submitForm(string $button, array $fieldValues = [], string $method = 'POST', array $serverParameters = []): Crawler
	{
		return $this->browser->submitForm($button, $fieldValues, $method, $serverParameters);
	}

	/**
	 * Scans the source code for web pages (php files).
	 * @param string[] $dirs List of dirs. Will _not_ recurse into them
	 * @param bool $pathAsArray when set, return an array of arrays. good for dataProvider methods
	 * @return array every php file is returned with its path relative to the root directory (starting with '/')
	 */
	protected static function listWebPages(array $dirs = [], $pathAsArray=false): array
	{
		if (self::$rootDir == '') {
			self::$rootDir = realpath(__DIR__ . '/../..');
		}

		if (!$dirs) {
			// directories with scripts known to be web-accessible
			$dirs = [
				self::$rootDir,
				self::$rootDir . '/api',
				self::$rootDir . '/dashboard',
				self::$rootDir . '/doc/Manual',
				self::$rootDir . '/install',
				self::$rootDir . '/reportwriter',
				self::$rootDir . '/reportwriter/admin',
			];
		}

		$pages = [];
		foreach($dirs as $dir) {
			foreach(glob($dir . '/*.php') as $path) {
				$path = preg_replace('|^' . self::$rootDir .'|', '', realpath($path));
				if ($pathAsArray) {
					$pages[] = [$path];
				} else {
					$pages[] = $path;
				}
			}
		}
		return $pages;
	}

	protected function assertIsNotOnInstallerPage(Crawler $crawler): void
	{
		$this->assertStringNotContainsString($crawler->getUri(), '/install/');
	}

	protected function assertIsNotOnLoginPage()
	{
		/// @todo ...
	}
}

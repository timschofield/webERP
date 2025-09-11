<?php

require_once(__DIR__ . '/HttpBrowser.php');

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestStatus;
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

		/// @todo  This is a file which can be read by pages server-side to validate that the request is coming from the test
		///        It remains to be done: 1. send self::$randId as cookie on every http request, and 2. check for it server-side
		//self::$randId = uniqid();
		//file_put_contents(sys_get_temp_dir() . '/phpunit_rand_id.txt', self::$randId);
	}

	/**
	 * Runs once after all the test methods of this object
	 */
	public static function tearDownAfterClass(): void
	{
		//if (is_file(sys_get_temp_dir() . '/phpunit_rand_id.txt')) {
		//	unlink(sys_get_temp_dir() . '/phpunit_rand_id.txt');
		//}

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
		// Save a "screenshot" of the web page on which the test error failed
		// NB: we have to check $testStatus here instead of using method `onNotSuccessfulTest` because that one is
		// called later in the execution, so it will not be able to use `$this->browser`
		$testStatus =  $this->status();
		if ($testStatus instanceof TestStatus\Failure || $testStatus instanceof TestStatus\Error) {
			if ($this->browser) {
				/// @todo add a timestamp suffix to the filename, and/or file/line nr. of the exception
				$testName = get_class($this) . '_' . $this->name();
				file_put_contents($_ENV['TEST_ERROR_SCREENSHOTS_DIR'] . '/webpage_failing_' . $testName. '.html', $this->getResponse()->getContent());
			}
		}

		$this->browser = null;

		parent::tearDown();
	}

	// *** Functions useful for usage in subclasses ***

	/**
	 * Calls a URI.
	 *
	 * @param string $method        The request method
	 * @param string $uri           The URI to fetch
	 * @param array  $parameters    The Request parameters
	 * @param array  $files         The files
	 * @param array  $server        The server parameters (HTTP headers are referenced with an HTTP_ prefix as PHP does)
	 * @param string $content       The raw body data
	 * @param bool   $changeHistory Whether to update the history or not (only used internally for back(), forward(), and reload())
	 */
	protected function request(string $method, string $uri, array $parameters = [], array $files = [], array $server = [], ?string $content = null, bool $changeHistory = true): Crawler
	{
		return $this->browser->request($method, $uri, $parameters, $files, $server, $content, $changeHistory);
	}

	/**
	 * Sets whether to automatically follow redirects or not.
	 */
	protected function followRedirects(bool $followRedirects = true): void
	{
		$this->browser->followRedirects($followRedirects);
	}

	/**
	 * Sets an array of expected HTTP status code that will be checked when sending requests.
	 * The test will fail when receiving any other status code.
	 * NB: to check for redirect responses, has to be used together with `$this->followRedirects(false);`
	 */
	protected function setExpectedStatusCodes(array $codes): void
	{
		$this->browser->setExpectedStatusCodes($codes);
	}

	/**
	 * Clicks the first link (or clickable image) that contains the given text.
	 *
	 * @param string $linkText         The text of the link or the alt attribute of the clickable image
	 * @param array  $serverParameters An array of server parameters
	 */
	protected function clickLink(string $linkText): Crawler
	{
		return $this->browser->clickLink($linkText);
	}

	/**
	 * Finds the first form that contains a button with the given content and
	 * uses it to submit the given form field values.
	 *
	 * @param string $button           The text content, id, value or name of the form <button> or <input type="submit">
	 * @param array  $fieldValues      Use this syntax: ['my_form[name]' => '...', 'my_form[email]' => '...']
	 * @param string $method           The HTTP method used to submit the form
	 * @param array  $serverParameters These values override the ones stored in $_SERVER (HTTP headers must include an HTTP_ prefix as PHP does)
	 */
	protected function submitForm(string $button, array $fieldValues = [], string $method = 'POST', array $serverParameters = []): Crawler
	{
		return $this->browser->submitForm($button, $fieldValues, $method, $serverParameters);
	}

	/**
	 * Returns the current response instance.
	 */
	protected function getResponse(): Symfony\Component\BrowserKit\Response
	{
		return $this->browser->getResponse();
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

	protected function assertHasNoElementsMatching(Crawler $crawler, string $cssSelector, string $message = '')
	{
		$count = $crawler->filter($cssSelector)->count();
		$text = '';
		if ($count > 0) {
			$text = implode(', ', $crawler->filter($cssSelector)->extract(['_text']));
		}
		if ($message === '') {
			$message = "Found unexpected element in page ($cssSelector)";
		}
		if ($text != '') {
			$message .= ": $text";
		}
		$this->assertEquals(0, $count, $message);
	}

	protected function assertIsNotOnInstallerPage(Crawler $crawler, $message = ''): void
	{
		/// @todo what about using $this->getResponse() instead of $crawler?
		$this->assertStringNotContainsString($crawler->getUri(), '/install/', $message);
	}

	protected function assertIsNotOnLoginPage()
	{
/// @todo ...
	}
}

<?php

require_once(__DIR__ . '/../src/WebTestCase.php');

/**
 * All public methods starting with `test` are tests which will be executed by PHPUnit
 */
class InstallerTest extends WebTestCase
{
	/**
	 * Runs a prerequisite check: check for presence of required extensions and php.ini settings
	 */
	public function testPHPConfiguration()
	{
		$this->request('GET', self::$baseUri . '/tests/setup/php_config_check.php');
		$this->assertEquals(200, $this->getResponse()->getStatusCode(), 'The php configuration for running the test suite could not be checked');
		$this->assertEquals('ok', $this->getResponse()->getContent(), 'The php configuration is not correct for running the test suite');
	}

	/**
	 * Runs the complete installer to create the database schema and fill it with data
	 * @return void
	 */
	public function testInstallation()
	{
		/// @todo allow using a phpunit env var to allow forcing removal of these if found (and also dropping the db schema),
		///       or is it too dangerous for end users?
		if (file_exists(self::$rootDir . '/config.php') || is_dir(self::$rootDir . '/companies/' . $_ENV['TEST_DB_SCHEMA'])) {
			$this->markTestSkipped('config.php is already present. Will not run the installer test');
		}

		$crawler = $this->request('GET', self::$baseUri . '/install/index.php');

		// page 0
		$this->assertStringContainsString('Welcome to the webERP installer', $crawler->filter('body > div.wizard > h1')->text(), 'Missing title in installer page 0');
		// for each page, check that there are no DIV elements with class "error"

		/// @todo make all the checks for textual labels (such as h1) internationalized, so that we can then run the
		///       test picking a random language for the installer. See the LanguageAwareTest trait

		$this->assertHasNoElementsMatching($crawler, 'body > div.wizard div.error', 'Error messages in page 0');
		$crawler = $this->clickLink('Next');

		// page 1
		$this->assertStringContainsString('Page=1', $crawler->getUri());
		$this->assertStringContainsString('GNU GENERAL PUBLIC LICENSE Version 2', $crawler->text(), 'Missing license in installer page 1');
		$this->assertHasNoElementsMatching($crawler, 'body > div.wizard div.error', 'Error messages in page 1');

		// check that the Next link is not activated (user has not accepted the license yet)
		$nextLinkUrl = $crawler->selectLink('Next')->link()->getUri();
		$this->assertStringNotContainsString('Page=2', $nextLinkUrl, 'Link to page 2 should not be active until accepting the license');
		$this->assertStringNotContainsString('Agreed=Yes', $nextLinkUrl, 'Link to page 2 should not be active until accepting the license');

		// @todo sadly we have to emulate the JS manually. Check if we can submit the form instead...
		$nextLinkUrl = str_replace('Page=1', 'Page=2', $nextLinkUrl) . '&Agreed=Yes';
		$crawler = $this->request('GET', $nextLinkUrl);

		// page 2
		$this->assertStringContainsString('Page=2', $crawler->getUri());
		$this->assertStringContainsString('System Checks', $crawler->filter('body > div.wizard > h1')->text());
		$this->assertHasNoElementsMatching($crawler, 'body > div.wizard div.error', 'Error messages in page 2');
		/// @todo should check that all system checks are passed?
		$crawler = $this->clickLink('Next');

		// page 3
		$this->assertStringContainsString('Page=3', $crawler->getUri());
		$this->assertStringContainsString('Database settings', $crawler->filter('body > div.wizard > h1')->text());
		$this->assertHasNoElementsMatching($crawler, 'body > div.wizard div.error', 'Error messages in page 3');

		// check that the 'Next' link has the is_disabled class
		$nextLinkUrl = $crawler->selectLink('Next');
		$this->assertStringContainsString('is_disabled', $nextLinkUrl->attr('class'));

		$crawler = $this->submitForm('test', [
			'dbms' => $_ENV['TEST_DB_TYPE'],
			'HostName' => $_ENV['TEST_DB_HOSTNAME'],
			'Port' => $_ENV['TEST_DB_PORT'],
			'Database' => $_ENV['TEST_DB_SCHEMA'],
			//'Prefix' => '',
			'UserName' => $_ENV['TEST_DB_USER'],
			'Password' => $_ENV['TEST_DB_PASSWORD'],
		]);

		// check that the 'Next' link has no is_disabled class
		$nextLinkUrl = $crawler->selectLink('Next');
		$this->assertStringNotContainsString('is_disabled', (string)$nextLinkUrl->attr('class'));

		$crawler = $this->clickLink('Next');

		// page 4
		$this->assertStringContainsString('Page=4', $crawler->getUri());
		$this->assertStringContainsString('Administrator account settings', $crawler->filter('body > div.wizard > h1')->text());
		$this->assertHasNoElementsMatching($crawler, 'body > div.wizard div.error', 'Error messages in page 4');

		// check that the 'Next' link has the is_disabled class
		$nextLinkUrl = $crawler->selectLink('Next');
		$this->assertStringContainsString('is_disabled', $nextLinkUrl->attr('class'), 'link from page 4 to page 5 is enabled without submitting db details');

		$crawler = $this->submitForm('test', [
			'adminaccount' => $_ENV['TEST_USER_ACCOUNT'],
			'Email' => $_ENV['TEST_USER_EMAIL'],
			'webERPPassword' => $_ENV['TEST_USER_PASSWORD'],
			'PasswordConfirm' => $_ENV['TEST_USER_PASSWORD'],
		]);

		// check that the 'Next' link has no is_disabled class
		$nextLinkUrl = $crawler->selectLink('Next');
		$this->assertStringNotContainsString('is_disabled', (string)$nextLinkUrl->attr('class'), 'db connection test on page 4 failed');

		$crawler = $this->clickLink('Next');

		// page 5
		$this->assertStringContainsString('Page=5', $crawler->getUri());
		$this->assertStringContainsString('Company Settings', $crawler->filter('body > div.wizard > h1')->text());
		$this->assertHasNoElementsMatching($crawler, 'body > div.wizard div.error', 'Error messages in page 5');

		/// @todo should we make all of the values below come from config/env-vars?
		$crawler = $this->submitForm('install', [
			'CompanyName' => 'Acme',
			'COA' => 'sql/coa/en_GB.utf8.sql', /// @todo load this from the files on disk
			'TimeZone' => 'Europe/London', /// @todo load this from the data available
			'Demo' => 'Yes',
		]);

		// page 6
		$this->assertStringContainsString('Page=6', $crawler->getUri());
		$this->assertHasNoElementsMatching($crawler, 'body > div.wizard div.error', 'Error messages in page 6');

		// test that configuration was created
		$this->assertFileExists(self::$rootDir . '/config.php');
		$this->assertDirectoryExists(self::$rootDir . '/companies/' . $_ENV['TEST_DB_SCHEMA']);
		// test that the auto-generated logo was created
		$this->assertFileExists(self::$rootDir . '/companies/' . $_ENV['TEST_DB_SCHEMA'] . '/logo.png');

		// go to homepage
		$crawler = $this->request('GET', self::$baseUri . '/index.php');
		$this->assertStringContainsString('Please login here', $crawler->text(), 'Missing title in installer 1st page');

		// log in
		$this->submitForm('SubmitUser', [
			'CompanyNameField' => $_ENV['TEST_DB_SCHEMA'],
			'UserNameEntryField' => $_ENV['TEST_USER_ACCOUNT'],
			'Password' => $_ENV['TEST_USER_PASSWORD'],
		]);
		$this->assertStringNotContainsString('ERROR Report', $crawler->text());

//var_dump($this->getResponse()->getContent());
	}
}

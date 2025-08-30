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
		/// @todo allow using a phpunit env var to allow forcing removal of these if found
		if (file_exists(self::$rootDir . '/config.php') || is_dir(self::$rootDir . '/companies/' . $_ENV['TEST_DB_SCHEMA'])) {
			$this->markTestSkipped('config.php is already present. Will not run the installer test');
		}

		$crawler = $this->request('GET', self::$baseUri . '/install/index.php');

		// page 0
		$this->assertStringContainsString('Welcome to the webERP installer', $crawler->text(), 'Missing title in installer 1st page');
		$crawler = $this->clickLink('Next');

		// page 1
		$this->assertStringContainsString('GNU GENERAL PUBLIC LICENSE Version 2', $crawler->text(), 'Missing license in installer 2nd page');

		// check that the Next link is not activated (user has not accepted the license yet)
		$nextLinkUrl = $crawler->selectLink('Next')->link()->getUri();
		$this->assertStringNotContainsString('Page=2', $nextLinkUrl);
		$this->assertStringNotContainsString('Agreed=Yes', $nextLinkUrl);

		// @todo sadly we have to emulate the JS manually. Check if we can submit the form instead...
		$nextLinkUrl = str_replace('Page=1', 'Page=2', $nextLinkUrl) . '&Agreed=Yes';
		$crawler = $this->request('GET', $nextLinkUrl);

		// page 2
		/// @todo should check that all system checks are passed?
		$this->assertStringContainsString('System Checks', $crawler->text());
		$crawler = $this->clickLink('Next');

		// page 3
		$this->assertStringContainsString('Database settings', $crawler->text());

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
		$this->assertStringContainsString('Administrator account settings', $crawler->text());

		// check that the 'Next' link has the is_disabled class
		$nextLinkUrl = $crawler->selectLink('Next');
		$this->assertStringContainsString('is_disabled', $nextLinkUrl->attr('class'));

		$crawler = $this->submitForm('test', [
			'adminaccount' => $_ENV['TEST_USER_ACCOUNT'],
			'Email' => $_ENV['TEST_USER_EMAIL'],
			'webERPPassword' => $_ENV['TEST_USER_PASSWORD'],
			'PasswordConfirm' => $_ENV['TEST_USER_PASSWORD'],
		]);

		// check that the 'Next' link has no is_disabled class
		$nextLinkUrl = $crawler->selectLink('Next');
		$this->assertStringNotContainsString('is_disabled', (string)$nextLinkUrl->attr('class'));

		$crawler = $this->clickLink('Next');

		// page 5
		$this->assertStringContainsString('Company Settings', $crawler->text());

		/// @todo should we make all of the values below come from config/env-vars?
		$crawler = $this->submitForm('install', [
			'CompanyName' => 'Acme',
			'COA' => 'sql/coa/en_GB.utf8.sql', /// @todo load this from the files on disk
			'TimeZone' => 'Europe/London', /// @todo load this from the data available
			'Demo' => 'Yes',
		]);

		// test that configuration was created
		$this->assertFileExists(self::$rootDir . '/config.php');
		$this->assertDirectoryExists(self::$rootDir . '/companies/' . $_ENV['TEST_DB_SCHEMA']);
		// test that the auto-generated logo was created
		$this->assertFileExists(self::$rootDir . '/companies/' . $_ENV['TEST_DB_SCHEMA'] . '/logo.png');

		// go to homepage
/// @todo is this necessary? check current url!
//var_dump($crawler->getUri());
//var_dump($this->getResponse()->);

		$crawler = $this->request('GET', self::$baseUri . '/index.php');
		$this->assertStringContainsString('Please login here', $crawler->text(), 'Missing title in installer 1st page');

		// log in
		$this->submitForm('SubmitUser', [
			'CompanyNameField' => $_ENV['TEST_DB_SCHEMA'],
			'UserNameEntryField' => $_ENV['TEST_USER_ACCOUNT'],
			'Password' => $_ENV['TEST_USER_PASSWORD'],
		]);
		$this->assertStringNotContainsString('ERROR Report', $crawler->text());

//var_dump($crawler->text());
	}
}

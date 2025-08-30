<?php

require_once(__DIR__ . '/../src/WebTestCase.php');

/**
 * All public methods starting with `test` are tests which will be executed by PHPUnit
 */
class InstallerTest extends WebTestCase
{
	/**
	 * Runs the complete installer to create the database schema and fill it with data
	 * @return void
	 */
	public function testInstallation()
	{
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

		$crawler = $this->submitForm('install', [
			'CompanyName' => 'Acme',
			'COA' => 'sql/coa/en_GB.utf8.sql', /// @todo load this from the files on disk
			'TimeZone' => 'Europe/London', /// @todo load this from the data available
			'Demo' => 'Yes',
		]);
	}
}

<?php

require_once(__DIR__ . '/../src/WebTestCase.php');
require_once(__DIR__ . '/../src/DBAwareTest.php');

/**
 * All public methods starting with `test` are tests which will be executed by PHPUnit
 */
class InstallerTest extends WebTestCase
{
	use DBAwareTest;

	protected $envDbSchema;

	protected function onNotSuccessfulTest(Throwable $t): never
	{
		// Set back the db schema name in case the testDBConnectivity test failed, and other tests will follow,
		// even though that is not the recommended way of running the tests in this class
		if ($_ENV['TEST_DB_SCHEMA'] === null && $this->envDbSchema !== null) {
			$_ENV['TEST_DB_SCHEMA'] = $this->envDbSchema;
		}

		parent::onNotSuccessfulTest($t);
	}

	/**
	 * Runs a prerequisite check: check for the db config parameters to actually allow to connect
	 * NB: this works best when running phpunit with `--stop-on-failure`
	 */
	public function testDBConnectivity()
	{
		$this->envDbSchema = $_ENV['TEST_DB_SCHEMA'];
		$_ENV['TEST_DB_SCHEMA'] = null;
		$this->assertCanConnect();
		$_ENV['TEST_DB_SCHEMA'] = $this->envDbSchema;
		$this->assertTrue(true);
	}

	/**
	 * Runs the complete installer to create the database schema and fill it with data
	 * @return void
	 */
	public function testInstallation()
	{
		/// @todo allow using a phpunit env var to allow forcing removal of these if found (and also dropping the db schema),
		///       or is it too dangerous for end users?
		///       In the end it might be better to add a 2nd tests which runs the installer after config.php exists,
		///       and adds a new company
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
		//$this->assertStringNotContainsString('Agreed=Yes', $nextLinkUrl, 'Link to page 2 should not be active until accepting the license');

		/// @todo sadly we have to submit the form manually as it has no submit button. Check if browserkit can do that somehow...
		//        (or, better, add a hidden submit button to the form?)
		$nextLinkUrl = $nextLinkUrl . '&Agreed=Yes';
		$crawler = $this->request('GET', $nextLinkUrl);
		$nextLinkUrl = $crawler->selectLink('Next')->link()->getUri();
		$this->assertStringContainsString('Page=2', $nextLinkUrl, 'Link to page 2 should be active after accepting the license');

		$crawler = $this->clickLink('Next');

		// page 2
		$this->assertStringContainsString('Page=2', $crawler->getUri());
		$this->assertStringContainsString('System Checks', $crawler->filter('body > div.wizard > h1')->text());
		$this->assertHasNoElementsMatching($crawler, 'body > div.wizard div.error', 'Error messages in page 2');
		$crawler = $this->clickLink('Next');

		// page 3
		$this->assertStringContainsString('Page=3', $crawler->getUri());
		$this->assertStringContainsString('Database settings', $crawler->filter('body > div.wizard > form legend')->text());
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
		$this->assertStringContainsString('Administrator account settings', $crawler->filter('body > div.wizard > form legend')->text());
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
		$this->assertStringContainsString('Company Settings', $crawler->filter('body > div.wizard > form legend')->text());
		$this->assertHasNoElementsMatching($crawler, 'body > div.wizard div.error', 'Error messages in page 5');

		// Work around a weird issue with $this->submitForm, which, when we submit no file, sends the form with
		// multipart encoding in a way that PHP does not understand on the receiving end (it misses $_POST data...):
		// implement here inline the equivalent of code $this->submitForm with and extra field removal added
		// (See https://github.com/symfony/symfony/issues/30867#issuecomment-3240412881)
		/// @todo should we make all of the values below come from config/env-vars?
		/// @todo add a test which does not load the demo.sql file
		$fieldValues = [
			'CompanyName' => 'Acme',
			'COA' => 'sql/coa/en_GB.utf8.sql', /// @todo load this from the files available on disk
			'TimeZone' => 'Europe/London', /// @todo load this from the data available
			'Demo' => 'Yes',
			'LogoFile' => null
		];
		$buttonNode = $crawler->selectButton('install');
		$form = $buttonNode->form($fieldValues, 'POST');
		$form->remove('LogoFile');
		$crawler = $this->browser->submit($form, [], []);

		// page 6
		$this->assertStringContainsString('Page=6', $crawler->getUri());
		$this->assertHasNoElementsMatching($crawler, 'body > div.wizard div.error', 'Error messages in page 6');

		// test that configuration was created
		$this->assertFileExists(self::$rootDir . '/config.php');
		$this->assertDirectoryExists(self::$rootDir . '/companies/' . $_ENV['TEST_DB_SCHEMA']);
		// test that the auto-generated logo was created
		$this->assertFileExists(self::$rootDir . '/companies/' . $_ENV['TEST_DB_SCHEMA'] . '/logo.png');

		/// @todo inject `$Debug = 99` at the end of config.php. We want tests to run with detailed info.

		// go to homepage
		$crawler = $this->request('GET', self::$baseUri . '/index.php');
		$this->assertStringContainsString('Please login here', $crawler->text(), 'Missing title in installer 1st page');

		// log in
		$crawler = $this->submitForm('SubmitUser', [
			'CompanyNameField' => $_ENV['TEST_DB_SCHEMA'],
			'UserNameEntryField' => $_ENV['TEST_USER_ACCOUNT'],
			'Password' => $_ENV['TEST_USER_PASSWORD'],
		]);
		$this->assertStringNotContainsString('ERROR Report', $crawler->text());
		$this->assertStringNotContainsString('Please login here', $crawler->text(), 'Failed logging in after installation');
	}
}

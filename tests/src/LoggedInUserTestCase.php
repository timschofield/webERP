<?php

require_once(__DIR__ . '/WebTestCase.php');

/**
 * The test-case class to be used as base for all tests which need to make an http call to webERP as logged-in user.
 * It forces a logout+login call before every test, and a logout one after the test.
 * The details for the user account come from either env vars or phpunit.xml values
 */
class LoggedInUserTestCase extends WebTestCase
{
	/**
	 * Runs once before each test method of this object.
	 * Logs in the user
	 */
	public function setUp(): void
	{
		parent::setUp();

		if (!is_file(self::$rootDir . '/config.php')) {
			$this->markTestSkipped('config.php is missing. webERP setup has not been done');
		}

		$this->loginUser();
	}

	/**
	 * Runs once after each test method of this object.
	 * Logs out the user.
	 */
	public function tearDown(): void
	{
		// avoid following the redirect to index.php to avoid starting a session
		$this->followRedirects(false);
		$this->setExpectedStatusCodes([302]);
		$this->request('GET', self::$baseUri . '/Logout.php');

		parent::tearDown();
	}
}

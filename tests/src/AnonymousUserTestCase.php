<?php

require_once(__DIR__ . '/WebTestCase.php');

/**
 * The test-case class to be used as base for all tests which need to make an http call to webERP as anon.
 * It forces a logout call before every test.
 */
class AnonymousUserTestCase extends WebTestCase
{
	/**
	 * Runs once before each test method of this object
	 */
	public function setUp(): void
	{
		parent::setUp();

		// remove all cookies - just in case
		$this->browser->getCookieJar()->clear();
	}
}

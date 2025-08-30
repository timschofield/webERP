<?php

require_once(__DIR__ . '/../src/WebTestCase.php');

class InstallerTest extends WebTestCase
{

	/**
	 * Runs the complete installer to create the database schema and fill it with data
	 * @return void
	 */
	public function testInstallation()
	{
		$crawler = $this->browser->request('GET', self::$baseUri . '/install/index.php');
		$this->assertStringContainsString('Welcome to the webERP installer', $crawler->text(), 'Missing title in installer 1st page');

		$crawler = $this->browser->clickLink('Next');
		$this->assertStringContainsString('GNU GENERAL PUBLIC LICENSE Version 2', $crawler->text(), 'Missing license in installer 2nd page');
	}
}

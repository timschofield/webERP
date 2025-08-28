<?php

use PHPUnit\Framework\TestCase;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;

class AAA_InstallerTest extends TestCase {

	/**
	 * Runs the installer to create the database schema and fill it with data
	 * @return void
	 */
	public function testInstallation() {
		// sample code...
		/*
		$browser = new HttpBrowser(HttpClient::create());
		$BaseUrl = $_ENV['TARGET_PROTOCOL'] . '://'. $_ENV['TARGET_HOSTNAME'] .
			($_ENV['TARGET_PORT'] != '' ? (':' . ltrim($_ENV['TARGET_PORT'], ':')) : '') .
			rtrim($_ENV['TARGET_BASE_URL'], '/');
		$crawler = $browser->request('GET', $BaseUrl .  '/');
		*/
	}
}

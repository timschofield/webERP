<?php

require_once(__DIR__ . '/../src/LoggedInUserTestCase.php');

class BBB_SinglePageTest extends LoggedInUserTestCase
{
	protected static $redirectingPages = [
		'/ContractBOM.php',
		'/ContractOtherReqts.php',
		'/PO_Items.php',
		'/reportwriter/admin/defaults.php'
	];

	/**
	 * Tests access to all pages
	 * This test is run many times - the filename to test is provided via the dataProvider.
	 * @dataProvider listAllWebPages
	 */
	public function testAccessToAllPages(string $filePath): void
	{
		// use the name of the currently tested script as part of the name of the html file saved in case of failure
		$this->executingTestIdentifier = preg_replace('/\.php$/', '', basename($filePath));

		$this->followRedirects(false);
		// some pages are known to return a redirect if missing a GET/POST param
		if (in_array($filePath, self::$redirectingPages)) {
			$this->setExpectedStatusCodes([302]);
		} elseif (strpos($filePath, '/api/') === 0) {
			// some api pages do redirect
			$this->setExpectedStatusCodes([200, 301, 302]);
		} else {
			$this->setExpectedStatusCodes([200]);
		}
		$crawler = $this->request('GET', self::$baseUri . $filePath);

		// avoid phpunit warnings, while ensuring code coverage. The assertions are done by $this->browser
		$this->assertTrue(true);
	}

	/**
	 * List all web pages, except for the Logout one
	 * @return string[][]
	 */
	public static function listAllWebPages(): array
	{
		$pages = [];
		foreach(self::listWebPages() as $path) {
			$fileName = basename($path);
			if (in_array($fileName, ['Logout.php', 'config.distrib.php'])) {
				continue;
			}
			$pages[] = [$path];
		}
		return $pages;
	}
}

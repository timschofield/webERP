<?php

require_once(__DIR__ . '/../src/AnonymousUserTestCase.php');

/**
 * All public methods starting with `test` are tests which will be executed by PHPUnit
 */
class AnonymousUsersTest extends AnonymousUserTestCase
{
	/**
	 * Tests that direct access to the installer pages does always result in a redirect.
	 * This test is run many times - the filename to test is provided via the dataProvider.
	 * It can be executed even before the installer has been run.
	 * @dataProvider listInstallerPages
	 */
	public function testDirectAccessToInstallerPages(string $fileName): void
	{
		$this->followRedirects(false);
		// be tolerant in case in the future we replace the redirect with a page-not-found
		$this->setExpectedStatusCodes([301, 302, 404]);
		$this->request('GET', self::$baseUri . $fileName);

		// avoid phpunit warnings, while ensuring code coverage. The assertions are done by $this->browser
		$this->assertTrue(true);
	}

	/**
	 * Tests access to pages which set $AllowAnyone to true
	 * This test is run many times - the filename to test is provided via the dataProvider.
	 * @dataProvider listAnonAccessPages
	 */
	public function testAccessToAnonPages(string $filePath): void
	{
		if (!is_file(self::$rootDir . '/config.php')) {
			$this->markTestSkipped('config.php is missing. webERP setup has not been done');
		}

		$this->followRedirects(false);
		$this->setExpectedStatusCodes([200]);
		$crawler = $this->request('GET', self::$baseUri . $filePath);

		// avoid phpunit warnings, while ensuring code coverage. The assertions are done by $this->browser
		$this->assertTrue(true);
	}

	/**
	 * List all pages of the installer
	 * @return array[]
	 */
	public static function listInstallerPages(): array
	{
		return self::listWebPages([__DIR__ . '/../../install/pages'], true);
	}

	/**
	 * List all pages which set $AllowAnyone to true
	 * @return string[][]
	 */
	public static function listAnonAccessPages(): array
	{
		$pages = [];
		foreach(self::listWebPages() as $path) {
			$fileName = basename($path);
			if (in_array($fileName, ['Logout.php', 'config.distrib.php'])) {
				continue;
			}
			if (preg_match('/^\s*\\$AllowAnyone\s*=\s*[Tt][Rr][Uu][eE]\s*;/m', file_get_contents(self::$rootDir . $path))) {
				$pages[] = [$path];
			}
		}
		return $pages;
	}
}

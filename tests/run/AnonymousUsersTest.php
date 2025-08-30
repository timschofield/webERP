<?php

require_once(__DIR__ . '/../src/AnonymousUserTestCase.php');

class AnonymousUsersTest extends AnonymousUserTestCase
{
	/**
	 * Tests that direct access to the installer pages does always result in a redirect.
	 * This test is run many times - the filename to test is provided via the dataProvider.
	 * @dataProvider listInstallerPages
	 */
	public function testDirectAccessToInstallerPages(string $fileName): void
	{
		$this->browser->followRedirects(false);
		// be tolerant in case in the future we replace the redirect with a page-not-found
		$this->browser->setExpectedStatusCodes([301, 302, 404]);
		$this->browser->request('GET', self::$baseUri . $fileName);

		// avoid phpunit warnings, while ensuring code coverage. The assertions are done by $this->browser
		$this->assertTrue(true);
	}

	/**
	 * Tests access to pages which set $AllowAnyone to true
	 * This test is run many times - the filename to test is provided via the dataProvider.
	 * @dataProvider listAnonAccessPages
	 */
	public function testAccessToAnonPages(string $fileName): void
	{
		if (!is_file(self::$rootDir . '/config.php')) {
			$this->markTestSkipped('config.php is missing. webERP setup has not been done');
		}

		$this->browser->followRedirects(false);
		$this->browser->setExpectedStatusCodes([200]);
		$crawler = $this->browser->request('GET', self::$baseUri . $fileName);

		/// @todo check for no php warnings being displayed

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
	 * @return array[]
	 */
	public static function listAnonAccessPages(): array
	{
		$pages = [];
		foreach(self::listWebPages() as $path) {
			$fileName = basename($path);
			if (in_array($fileName, ['Logout.php', 'config.dist.php'])) {
				continue;
			}
			if (preg_match('/^\s*\\$AllowAnyone\s*=\s*[Tt][Rr][Uu][eE]\s*;/m', file_get_contents(self::$rootDir . $path))) {
				$pages[] = [$path];
			}
		}
		return $pages;
	}
}

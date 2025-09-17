<?php

require_once(__DIR__ . '/../src/AnonymousUserTestCase.php');

/**
 * All public methods starting with `test` are tests which will be executed by PHPUnit
 */
class AAA_AnonymousUsersTest extends AnonymousUserTestCase
{
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

		// use the name of the currently tested script as part of the name of the html file saved in case of failure
		$this->executingTestIdentifier = preg_replace('/\.php$/', '', basename($filePath));

		$this->followRedirects(false);
		$this->setExpectedStatusCodes([200]);
		$crawler = $this->request('GET', self::$baseUri . $filePath);

		$this->assertIsNotOnLoginPage($crawler);
	}

	/**
	 * Tests access to pages which do not set $AllowAnyone to true (exclude api, manual, installer)
	 * This test is run many times - the filename to test is provided via the dataProvider.
	 * @dataProvider listNonAnonAccessPages
	 */
	public function testAccessToNonAnonPages(string $filePath): void
	{
		if (!is_file(self::$rootDir . '/config.php')) {
			$this->markTestSkipped('config.php is missing. webERP setup has not been done');
		}

		// use the name of the currently tested script as part of the name of the html file saved in case of failure
		$this->executingTestIdentifier = preg_replace('/\.php$/', '', basename($filePath));

		$this->followRedirects(false);
		$this->setExpectedStatusCodes([200]);
		$crawler = $this->request('GET', self::$baseUri . $filePath);

		$this->assertIsOnLoginPage($crawler);
	}

	/**
	 * Tests that direct access to the installer pages does always result in a redirect.
	 * This test is run many times - the filename to test is provided via the dataProvider.
	 * It can be executed even before the installer has been run.
	 * @dataProvider listInstallerPages
	 */
	public function testDirectAccessToInstallerPages(string $fileName): void
	{
		// use the name of the currently tested script as part of the name of the html file saved in case of failure
		$this->executingTestIdentifier = preg_replace('/\.php$/', '', basename($fileName));

		$this->followRedirects(false);
		// be tolerant in case in the future we replace the redirect with a page-not-found
		$this->setExpectedStatusCodes([301, 302, 404]);
		$this->request('GET', self::$baseUri . $fileName);

		// avoid phpunit warnings, while ensuring code coverage. The assertions are done by $this->browser
		$this->assertTrue(true);
	}

	/**
	 * Tests that direct access to the non-full-pages/non-html-pages does not generate a php warning or error.
	 * This test is run many times - the filename to test is provided via the dataProvider.
	 * @dataProvider listNonWebPages
	 */
	public function testDirectAccessToNonWebPages(string $fileName): void
	{
		// use the name of the currently tested script as part of the name of the html file saved in case of failure
		$this->executingTestIdentifier = preg_replace('/\.php$/', '', basename($fileName));

		$this->followRedirects(false);
		// be tolerant in case in the future we replace the redirect with a page-not-found
		$this->setExpectedStatusCodes([200, 301, 302, 404]);
		$this->request('GET', self::$baseUri . $fileName);

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
			if (in_array($fileName, ['Logout.php'])) {
				continue;
			}
			if (preg_match('/^\s*\\$AllowAnyone\s*=\s*[Tt][Rr][Uu][eE]\s*;/m', file_get_contents(self::$rootDir . $path))) {
				$pages[] = [$path];
			}
		}
		return $pages;
	}

	/**
	 * List all web pages which do not set $AllowAnyone to true - excluding API, manual, installer.
	 * These are all the pages which should not be accessible by anon user
	 * @return string[][]
	 */
	public static function listNonAnonAccessPages(): array
	{
		$dirs = [
			__DIR__ . '/../../',
			__DIR__ . '/../../dashboard',
			__DIR__ . '/../../reportwriter',
			__DIR__ . '/../../reportwriter/admin',
		];

		$pages = [];
		foreach(self::listWebPages($dirs) as $path) {
			$fileName = basename($path);
			if (in_array($fileName, ['Logout.php', 'config.php', 'config.distrib.php'])) {
				continue;
			}
			if (!preg_match('/^\s*\\$AllowAnyone\s*=\s*[Tt][Rr][Uu][eE]\s*;/m', file_get_contents(self::$rootDir . $path))) {
				$pages[] = [$path];
			}
		}
		return $pages;
	}

	public static function listNonWebPages(): array
	{
		$dirs = [
			__DIR__ . '/../../api',
			//__DIR__ . '/../../bin',
			__DIR__ . '/../../doc/Manual',
			/// @todo once we have fixed all the files in /includes for direct we access, uncomment the line below
			//__DIR__ . '/../../includes',
			/// @todo same for these 3 folder after PR #715 is merged
			//__DIR__ . '/../../reportwriter/admin/forms',
			//__DIR__ . '/../../reportwriter/forms',
			//__DIR__ . '/../../reportwriter/includes',
			__DIR__ . '/../../reportwriter/install',
		];

		return array_merge(
			[
				['/config.php'],
				['/config.distrib.php'],
			],
			self::listWebPages($dirs, true)
		);
	}
}

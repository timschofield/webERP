<?php

require_once(__DIR__ . '/../src/HttpBrowser.php');

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class TestableHttpBrowser extends HttpBrowser
{
	public function extractErrors(string $html): array
	{
		return $this->extractErrorMessages($html);
	}
}

class CCC_HttpBrowserTest extends TestCase
{
	private function createBrowser(array $responses): TestableHttpBrowser
	{
		return new TestableHttpBrowser(new MockHttpClient($responses));
	}

	public function testExtractErrorMessagesReturnsCleanedMessage(): void
	{
		$browser = $this->createBrowser([]);
		$browser->setExpectedErrorStrings('ERR_START', 'ERR_END');

		$errors = $browser->extractErrors('prefix ERR_START <b>Fatal error</b>: line 1<br />line 2 ERR_END suffix');

		$this->assertSame(['Fatal error: line 1 line 2'], $errors);
	}

	public function testExtractErrorMessagesTruncatesLongMessages(): void
	{
		$browser = $this->createBrowser([]);
		$browser->setExpectedErrorStrings('ERR_START', 'ERR_END');

		$errors = $browser->extractErrors('ERR_START ' . str_repeat('x', 80) . ' ERR_END');

		$this->assertCount(1, $errors);
		$this->assertSame(40, strlen($errors[0]));
		$this->assertStringEndsWith('...', $errors[0]);
	}

	public function testExtractErrorMessagesUsesXdebugFallbackWhenConfigured(): void
	{
		$browser = $this->createBrowser([]);
		$browser->setExpectedErrorStrings('ERR_START', 'ERR_END', true);

		$errors = $browser->extractErrors("<table class='xdebug-error'><tr><td>Fatal error: A fatal problem happened<br />in /tmp/file.php on line 2</td></tr></table>");

		$this->assertCount(1, $errors);
		$this->assertStringStartsWith('Fatal error:', $errors[0]);
	}

	public function testRequestThrowsForUnexpectedHttpErrorStatus(): void
	{
		$browser = $this->createBrowser([new MockResponse('', ['http_code' => 500])]);

		$this->expectException(ExpectationFailedException::class);
		$this->expectExceptionMessage('Got HTTP response code 500');

		$browser->request('GET', 'http://example.test/failure');
	}

	public function testRequestAllowsConfiguredStatusCodes(): void
	{
		$browser = $this->createBrowser([new MockResponse('', ['http_code' => 404])]);
		$browser->setExpectedStatusCodes([404]);

		$crawler = $browser->request('GET', 'http://example.test/not-found');

		$this->assertInstanceOf(Crawler::class, $crawler);
	}

	public function testRequestThrowsWhenExpectedStatusCodeDoesNotMatch(): void
	{
		$browser = $this->createBrowser([new MockResponse('', ['http_code' => 200])]);
		$browser->setExpectedStatusCodes([302]);

		$this->expectException(ExpectationFailedException::class);
		$this->expectExceptionMessage('while expecting 302');

		$browser->request('GET', 'http://example.test/unexpected');
	}

	public function testRequestThrowsWhenPhpErrorMarkupIsDetected(): void
	{
		$browser = $this->createBrowser([new MockResponse('<b>Fatal error</b>: bad thing happened<br />', ['http_code' => 200])]);
		$browser->setExpectedErrorStrings('<b>Fatal error</b>:', '<br />');

		$this->expectException(ExpectationFailedException::class);
		$this->expectExceptionMessage('PHP errors/warnings in page');

		$browser->request('GET', 'http://example.test/php-error');
	}

	public function testRequestSucceedsWhenNoPhpErrorMarkupIsPresent(): void
	{
		$browser = $this->createBrowser([new MockResponse('all good', ['http_code' => 200])]);
		$browser->setExpectedErrorStrings('<b>Fatal error</b>:', '<br />');

		$crawler = $browser->request('GET', 'http://example.test/ok');

		$this->assertInstanceOf(Crawler::class, $crawler);
	}
}

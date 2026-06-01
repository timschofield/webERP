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
 $this->extractErrorMessages($html);
}
}

class CCC_HttpBrowserTest extends TestCase
{
private function createBrowser(array $responses): TestableHttpBrowser
{
 new TestableHttpBrowser(new MockHttpClient($responses));
}

public function testExtractErrorMessagesReturnsCleanedMessage(): void
{
gs('ERR_START', 'ERR_END');

<b>Fatal error</b>: line 1<br />line 2 ERR_END suffix");

e 1 line 2'], $errors);
}

public function testExtractErrorMessagesTruncatesLongMessages(): void
{
gs('ERR_START', 'ERR_END');

. str_repeat('x', 80) . ' ERR_END');

t(1, $errors);
($errors[0]));
gEndsWith('...', $errors[0]);
}

public function testExtractErrorMessagesUsesXdebugFallbackWhenConfigured(): void
{
gs('ERR_START', 'ERR_END', true);

error: A fatal problem happened<br />in /tmp/file.php on line 2</td></tr></table>");

t(1, $errors);
gStartsWith('Fatal error:', $errors[0]);
}

public function testRequestThrowsForUnexpectedHttpErrorStatus(): void
{
ew MockResponse('', ['http_code' => 500])]);

(ExpectationFailedException::class);
Message('Got HTTP response code 500');

ction testRequestAllowsConfiguredStatusCodes(): void
{
ew MockResponse('', ['http_code' => 404])]);
uest('GET', 'http://example.test/not-found');

stanceOf(Crawler::class, $crawler);
}

public function testRequestThrowsWhenExpectedStatusCodeDoesNotMatch(): void
{
ew MockResponse('', ['http_code' => 200])]);
(ExpectationFailedException::class);
Message('while expecting 302');

expected');
}

public function testRequestThrowsWhenPhpErrorMarkupIsDetected(): void
{
ew MockResponse('<b>Fatal error</b>: bad thing happened<br />', ['http_code' => 200])]);
gs('<b>Fatal error</b>:', '<br />');

(ExpectationFailedException::class);
Message('PHP errors/warnings in page');

ction testRequestSucceedsWhenNoPhpErrorMarkupIsPresent(): void
{
ew MockResponse('all good', ['http_code' => 200])]);
gs('<b>Fatal error</b>:', '<br />');

uest('GET', 'http://example.test/ok');

stanceOf(Crawler::class, $crawler);
}
}

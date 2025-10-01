<?php

include_once(__DIR__ . '/ErrorsInWebPageException.php');

use PHPUnit\Framework\ExpectationFailedException;
use Symfony\Component\BrowserKit\HttpBrowser as BaseHttpBrowser;
use Symfony\Component\BrowserKit\Request;
use Symfony\Component\BrowserKit\Response;

/**
 * The HttpBrowser class used by the WebTestCase.
 * It adds custom functionality to the stock HttpBrowser from Symfony BrowserKit
 */
class HttpBrowser extends BaseHttpBrowser
{
	protected $expectedStatusCodes = null;
	protected $errorPrependString = '';
	protected $errorAppendString = '';
	protected $serverRunsXdebug;

	/**
	 * Adds an easy way to check for requests resulting in errors or redirects, without having to specify
	 * that in every single test method.
	 *
	 * @param Request $request
	 */
	protected function doRequest(object $request): Response
	{
		$response = parent::doRequest($request);

		// Store the response immediately, in case we throw rather than returning.
		// This is normally done in the parent class, as a result of invoking this method.
		$this->response = $response;

		if ($response->getStatusCode() >= 400 && (!$this->expectedStatusCodes || !in_array($response->getStatusCode(), $this->expectedStatusCodes))) {
			throw new ExpectationFailedException('Got HTTP response code ' . $response->getStatusCode() . ' for ' .
				$request->getUri());
		}

		if ($this->expectedStatusCodes) {
			if (!in_array($response->getStatusCode(), $this->expectedStatusCodes)) {
				throw new ExpectationFailedException('Got HTTP response code ' . $response->getStatusCode() . ' for ' .
					$request->getUri() . ' while expecting ' . implode('/', $this->expectedStatusCodes));
			}
		}

		// check that there are no php warnings or errors displayed
		$errorMessages = $this->extractErrorMessages($response->getContent());
		if ($errorMessages) {
			/// @todo display more than one error message
			throw new ExpectationFailedException('PHP errors/warnings in page ' . $request->getUri() . ': ' . $errorMessages[0]);
				//, null, new ErrorsInWebPageException($request->getUri(), $errorMessages));
		}

		return $response;
	}

	/**
	 * Sets an array of expected HTTP status code that will be checked when sending requests.
	 * The test will fail when receiving any other status code.
	 */
	public function setExpectedStatusCodes(array $codes): void
	{
		$this->expectedStatusCodes = $codes;
	}

	public function setExpectedErrorStrings(string $errorPrependString, string $errorAppendString, null|bool $serverRunsXdebug = null)
	{
		$this->errorPrependString = $errorPrependString;
		$this->errorAppendString = $errorAppendString;
		$this->serverRunsXdebug = $serverRunsXdebug;
	}

	protected function extractErrorMessages($html)
	{
		$errors = array();

		if ($this->errorPrependString !== '') {
			$start = 0;
			while (($start = strpos($html, $this->errorPrependString, $start)) !== false) {
				$start += strlen($this->errorPrependString);
				$end = strpos($html, $this->errorAppendString, $start);
				if ($end === false) {
					$end = strlen($html);
				}
				$message = substr($html, $start, $end - $start);
				$start = $end;

				$message = trim(str_replace("\n", ' ', preg_replace('/^ +/m', '', strip_tags(str_ireplace(['<br>', '<br/>', '<br />'], "\n", $message)))));
				if (strlen($message) > 40) {
					$message = substr($message, 0, 37) . '...';
				}
				$errors[] = $message;
			}

			// no errors found, but there might be some - work around xdebug issue https://bugs.xdebug.org/view.php?id=2365
			if (!$errors && $this->serverRunsXdebug) {
				if (preg_match("|<table +class *= *'[^']*xdebug[^']*'.+(Fatal error:.+)</table>|s", $html, $matches)) {
					$message = trim(str_replace("\n", ' ', preg_replace('/^ +/m', '', strip_tags(str_ireplace(['<br>', '<br/>', '<br />'], "\n", $matches[1])))));
					/// @todo is this a good default?
					if (strlen($message) > 120) {
						$message = substr($message, 0, 117) . '...';
					}
					$errors[] = $message;
				}
			}

		} else {
			if ($this->serverRunsXdebug) {
				/// @todo look for strings such as the one above
			} else {
				/// @todo look for strings such as `<b>Fatal error</b>:`
			}
		}

		return $errors;
	}
}

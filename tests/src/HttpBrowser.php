<?php

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

		if ($this->errorPrependString !== '') {
			// check that there are no php warnings or errors displayed
			$errorMessages = $this->extractErrorMessages($response->getContent());
			if ($errorMessages) {
				/// @todo display more than one error message
				throw new ExpectationFailedException('Got PHP errors or warnings in page ' . $request->getUri() . ': ' .
					$errorMessages[0]);
			}
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

	public function setExpectedErrorStrings(string $errorPrependString, string $errorAppendString)
	{
		$this->errorPrependString = $errorPrependString;
		$this->errorAppendString = $errorAppendString;
	}

	protected function extractErrorMessages($html)
	{
		$errors = array();
		$start = 0;
		while (($start = strpos($html, $this->errorPrependString, $start)) !== false) {
			$start += strlen($this->errorPrependString);
			$end = strpos($html, $this->errorAppendString, $start);
			if ($end === false) {
				$end = strlen($html);
			}
			$message = substr($html, $start, $end - $start);
			$start = $end;

			$message = str_replace("\n", ' ', preg_replace('/^ +/m', '', strip_tags($message)));
			if (strlen($message) > 40) {
				$message = substr($message, 0, 37) . '...';
			}
			$errors[] = $message;
		}

		return $errors;
	}
}

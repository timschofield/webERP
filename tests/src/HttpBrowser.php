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
			$responseContent = $response->getContent();
			if (($start = strpos($responseContent, $this->errorPrependString)) !== false) {
				/// @todo 1. halt extraction where error_append_string starts instead of at 40 chars
				/// @todo 2. look for more than one error message
				/// @todo 3. strip html tags from the error message
				throw new ExpectationFailedException('Got PHP errors or warnings in page ' . $request->getUri() . ': ' .
					substr($responseContent, $start + strlen($this->errorPrependString), 40) . '...');
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
}

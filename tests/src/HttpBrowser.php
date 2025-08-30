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

	/**
	 * Adds an easy way to check for requests resulting in errors or redirects, without having to specify
	 * that in every single test method.
	 *
	 * @param Request $request
	 */
	protected function doRequest(object $request): Response
	{
		$response = parent::doRequest($request);

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
		/// @todo find a way to make this check more robust - the string might be different with xdebug active!
		$responseContent = $response->getContent();
		if (($start = strpos($responseContent, '<b>Warning</b>:  ')) !== false) {
			throw new ExpectationFailedException('Got PHP warnings in page ' . $request->getUri() . ': ' .
				substr($responseContent, $start + 17, 40) . '...');
		}
		/// @todo check for errors, too

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
}

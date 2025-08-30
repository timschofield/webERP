<?php

use PHPUnit\Framework\ExpectationFailedException;
use Symfony\Component\BrowserKit\HttpBrowser as BaseHttpBrowser;
use Symfony\Component\BrowserKit\Request;
use Symfony\Component\BrowserKit\Response;

class HttpBrowser extends BaseHttpBrowser
{
	protected $expectedStatusCodes = null;

	/**
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
					$request->getUri());
			}
		}

		/// @todo add checking that there are no php warnings or errors displayed

		return $response;
	}

	public function setExpectedStatusCodes(array $codes)
	{
		$this->expectedStatusCodes = $codes;
	}
}

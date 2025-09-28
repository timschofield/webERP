<?php

class ErrorsInWebPageException extends \Exception
{
	/** @var array */
	protected $errorStrings = [];

	public function __construct(string $url, array $errorStrings, ?Throwable $previous = null)
	{
		$this->errorStrings = $errorStrings;
		$message = 'PHP errors/warnings in page ' . $url;
		$code = count($errorStrings);
		parent::__construct($message, $code, $previous);
	}
}

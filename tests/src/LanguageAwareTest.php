<?php

trait LanguageAwareTest
{
	/**
	 * NB: keep in sync with the code in LanguageSetup.php
	 * @todo add choosing the current language and call setlocale()
	 */
	protected function setupLanguage(string $PathPrefix): void
	{
		textdomain ('messages');
		bindtextdomain ('messages', $PathPrefix . 'locale');
		bind_textdomain_codeset('messages', 'UTF-8');
	}
}

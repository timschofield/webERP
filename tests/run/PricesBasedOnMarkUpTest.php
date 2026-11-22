<?php

require_once(__DIR__ . '/../src/LoggedInUserTestCase.php');
require_once(__DIR__ . '/../src/DBAwareTest.php');

class PricesBasedOnMarkUpTest extends LoggedInUserTestCase
{
	use DBAwareTest;

	public function testRoundingFactorWithAndWithoutCurrency(): void
	{
		$home = $this->query("SELECT currencies.decimalplaces
			FROM companies INNER JOIN currencies ON companies.currencydefault = currencies.currabrev
			LIMIT 1")[0];
		$foreign = $this->query("SELECT currencies.currabrev, currencies.decimalplaces
			FROM currencies INNER JOIN companies ON currencies.currabrev != companies.currencydefault
			LIMIT 1")[0];
		$homeTolerance = (string) pow(10, -$home['decimalplaces']);
		$foreignTolerance = (string) pow(10, -$foreign['decimalplaces']);

		$crawler = $this->request('GET', self::$baseUri . '/PricesBasedOnMarkUp.php');
		$this->assertSame('0', $crawler->filter('select[name="CurrCode"] option[selected]')->attr('value'));
		$this->assertSame($homeTolerance, $crawler->filter('input[name="RoundingFactor"]')->attr('value'));
		$this->assertStringContainsString('enter ' . $homeTolerance . '.', $crawler->filter('input[name="RoundingFactor"] + fieldhelp')->text());
		$formId = $crawler->filter('input[name="FormID"]')->attr('value');

		$crawler = $this->request('POST', self::$baseUri . '/PricesBasedOnMarkUp.php', [
			'FormID' => $formId,
			'CostType' => 'StandardCost',
			'RoundingFactor' => '5',
		]);
		$this->assertSame('5', $crawler->filter('input[name="RoundingFactor"]')->attr('value'));
		$this->assertStringContainsString('enter ' . $homeTolerance . '.', $crawler->filter('input[name="RoundingFactor"] + fieldhelp')->text());

		$crawler = $this->request('POST', self::$baseUri . '/PricesBasedOnMarkUp.php', [
			'FormID' => $formId,
			'CostType' => 'StandardCost',
			'CurrCode' => $foreign['currabrev'],
		]);
		$this->assertSame($foreign['currabrev'], $crawler->filter('select[name="CurrCode"] option[selected]')->attr('value'));
		$this->assertSame($foreignTolerance, $crawler->filter('input[name="RoundingFactor"]')->attr('value'));
		$this->assertStringContainsString('enter ' . $foreignTolerance . '.', $crawler->filter('input[name="RoundingFactor"] + fieldhelp')->text());
	}
}

<?php

namespace PGettext\Plurals;

class Header
{
  public $total;
  public $expression;

  /**
   * Constructor
   *
   * @param string $string The value of the Plural-Forms: header as seen in .po files.
   */
  function __construct($string) {
    try {
      list($total, $expression) = $this->parse($string);
    } catch (\Exception $e) {
      $string = "nplurals=2; plural=n == 1 ? 0 : 1;";
      list($total, $expression) = $this->parse($string);
    }
    $this->total = $total;
    $this->expression = $expression;
  }

  /**
   * Return the number of plural forms and the parsed expression tree.
   *
   * @access private
   * @param string $string The value of the Plural-Forms: header.
   * @throws \Exception If the string could not be parsed.
   * @return array The number of plural forms and parsed expression tree.
   */
  private function parse($string) {
    $regex = "/^\s*nplurals\s*=\s*(\d+)\s*;\s*plural\s*=([^;]+);/i";
    if (preg_match($regex, $string, $matches)) {
      $total = (int)$matches[1];
      $expression_string = $matches[2];
    } else {
      throw new \Exception('Invalid header value');
    }

    $parser = new Parser($expression_string);
    $expression = $parser->parse();
    return array($total, $expression);
  }
}

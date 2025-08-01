<?php

namespace PGettext\Plurals;

class Lexer
{
  private $string;
  private $position;

  /**
   * Constructor
   *
   * @param string $string Contains the value gettext plurals expression to
   * analyze.
   */
  public function __construct($string) {
    $this->string = $string;
    $this->position = 0;
  }

  /**
   * Return the next token and the length to advance the read position without
   * actually advancing the read position. Tokens for operators and variables
   * are simple strings containing the operator or variable. If there are no
   * more token to provide, the special value ['__END__', 0] is returned. If
   * there was an unexpected input an Exception is raised.
   *
   * @access private
   * @throws \Exception If there is unexpected input in the provided string.
   * @return array The next token and length to advance the current position.
   */
  private function _tokenize() {
    $buf = $this->string;

    // Consume all spaces until the next token
    $index = $this->position;
    while ($index < strlen($buf) && $buf[$index] == ' ') {
      $index++;
    }
    $this->position = $index;

    // Return special token if next of the string is reached.
    if (strlen($buf) - $index == 0) {
      return ['__END__', 0];
    }

    // Operators with two characters
    $doubles = array('==', '!=', '>=', '<=', '&&', '||');
    $next = substr($buf, $index, 2);
    if (in_array($next, $doubles)) {
      return [$next, 2];
    }

    // Operators with single character or variable 'n'.
    $singles = array(
      'n', '(', ')', '?', ':', '+', '-', '*', '/', '%', '!', '>', '<');
    if (in_array($buf[$index], $singles)) {
      return [$buf[$index], 1];
    }

    // Whole number constants, return an integer.
    $digits = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
    $pos = $index;
    while ($pos < strlen($buf) && in_array($buf[$pos], $digits)) {
      $pos++;
    }
    if ($pos != $index) {
      $length = $pos - $index;
      return array((int)substr($buf, $index, $length), $length);
    }

    // Throw and exception for all other unexpected input in the string.
    throw new \Exception('Lexical analysis failed');
  }

  /**
   * Return the next token without actually advancing the read position.
   * Tokens for operators and variables are simple strings containing the
   * operator or variable. If there are no more tokens to provide, the special
   * value '__END__' is returned. If there was an unexpected input an
   * Exception is raised.
   *
   * @throws \Exception If there is unexpected input in the provided string.
   * @return string The next token.
   */
  public function peek() {
    list($token, $length) = $this->_tokenize();
    return $token;
  }

  /**
   * Return the next token after advancing the read position. Tokens for
   * operators and variables are simple strings containing the operator or
   * variable. If there are no more token to provide, the special value
   * '__END__' is returned. If there was an unexpected input an Exception is
   * raised.
   *
   * @throws \Exception If there is unexpected input in the provided string.
   * @return string The next token.
   */
  public function fetch_token() {
    list($token, $length) = $this->_tokenize();
    $this->position += $length;
    return $token;
  }
}

<?php

namespace PGettext\Plurals;

class Parser
{
  private $lexer;

  /*
   * Operator precedence. The parsing only happens with minimum precedence of
   * 0. However, ':' and ')' exist here to make sure that parsing does not
   * proceed beyond them when they are not to be parsed.
   */
  private static $PREC = array(
    ':' => -1, '?' => 0, '||' => 1, '&&' => 2, '==' => 3, '!=' => 3,
    '>' => 4, '<' => 4, '>=' => 4, '<=' => 4, '+' => 5, '-' => 5, '*' => 6,
    '/' => 6, '%' => 6, '!' => 7, '__END__' => -1, ')' => -1
  );

  // List of right associative operators
  private static $RIGHT_ASSOC = array('?');

  /**
   * Constructor
   *
   * @param string $string the plural expression to be parsed.
   */
  public function __construct($string) {
    $this->lexer = new Lexer($string);
  }

  /**
   * Expect a primary next for parsing and return a PluralsExpression or throw
   * and exception otherwise. A primary can be the variable 'n', an whole
   * number constant, a unary operator expression string with '!', or a
   * parenthesis expression.
   *
   * @throws \Exception If the next token is not a primary or if parenthesis
   * expression is not closes properly with ')'.
   * @return Expression That is constructed from the parsed primary.
   */
  private function _parse_primary() {
    $token = $this->lexer->fetch_token();
    if ($token === 'n') {
      return new Expression('var', 'n');
    } elseif (is_int($token)) {
      return new Expression('const', (int)$token);
    } elseif ($token === '!') {
      return new Expression('!', $this->_parse_primary());
    } elseif ($token === '(') {
      $result = $this->_parse($this->_parse_primary(), 0);
      if ($this->lexer->fetch_token() != ')') {
        throw new \Exception('Mismatched parenthesis');
      }
      return $result;
    }

    throw new \Exception('Primary expected');
  }

  /**
   * Fetch an operator from the lexical analyzer and test for it. Optionally
   * advance the position of the lexical analyzer to next token. Raise
   * exception if the token retrieved is not an operator.
   *
   * @access private
   * @param bool $peek A flag to indicate whether the position of the lexical
   * analyzer should *not* be advanced. If false, the lexical analyzer is
   * advanced by one token.
   * @throws \Exception If the token read is not an operator.
   * @return string The operator that has been fetched from the lexical
   * analyzer.
   */
  private function _parse_operator($peek) {
    if ($peek) {
      $token = $this->lexer->peek();
    } else {
        $token = $this->lexer->fetch_token();
    }

    if ($token !== NULL && !array_key_exists($token, self::$PREC)) {
      throw new \Exception('Operator expected');
    }
    return $token;
  }

  /**
   * A parsing method suitable for recursion.
   *
   * @access private
   * @param Expression $left_side A pre-parsed left-hand side expression
   * of the file expression to be constructed. This helps with recursion.
   * @param int $min_precedence The minimum value of precedence for the
   * operators to be considered for parsing. Parsing will stop and current
   * expression is returned if an operator of a lower precedence is
   * encountered.
   * @throws \Exception If the input string does not conform to the grammar of
   * the gettext plural expression.
   * @return Expression A complete expression after parsing.
   */
  private function _parse($left_side, $min_precedence) {
    $next_token = $this->_parse_operator(true);

    while (self::$PREC[$next_token] >= $min_precedence) {
      $operator = $this->_parse_operator(false);
      $right_side = $this->_parse_primary();

      $next_token = $this->_parse_operator(true);

      /*
       * Consume (recursively) into right hand side all expressions of higher
       * precedence.
       */
      while ((self::$PREC[$operator] < self::$PREC[$next_token]) ||
             ((self::$PREC[$operator] == self::$PREC[$next_token]) &&
              in_array($operator, self::$RIGHT_ASSOC))) {
        $right_side = $this->_parse(
            $right_side, self::$PREC[$next_token]);
        $next_token = $this->_parse_operator(true);
      }

      if ($operator != '?') {
        /*
         * Handling for all binary operators. Consume into left hand side all
         * expressions of equal precedence.
         */
        $left_side = new Expression($operator, $left_side, $right_side);
      } else {
        // Special handling for (a ? b : c) expression
        $operator = $this->lexer->fetch_token();
        if ($operator != ':') {
          throw new \Exception('Invalid ? expression');
        }

        $right_side2 = $this->_parse(
          $this->_parse_primary(), self::$PREC[$operator] + 1);
        $next_token = $this->_parse_operator(true);
        $left_side = new Expression(
            '?', $left_side, $right_side, $right_side2);
      }
    }
    return $left_side;
  }

  /**
   * A simple implementation of an operator-precedence parser. See:
   * https://en.wikipedia.org/wiki/Operator-precedence_parser for an analysis
   * of the algorithm.
   *
   * @throws \Exception If the input string does not conform to the grammar of
   * the gettext plural expression.
   * @return Expression A complete expression after parsing.
   */
  public function parse() {
    $expression = $this->_parse($this->_parse_primary(), 0);
    // Special handling for an extra ')' at the end.
    if ($this->lexer->peek() != '__END__') {
      throw new \Exception('Could not parse completely');
    }
    return $expression;
  }
}

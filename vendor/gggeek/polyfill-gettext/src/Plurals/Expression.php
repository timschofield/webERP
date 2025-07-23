<?php

namespace PGettext\Plurals;

/**
 * A parsed representation of the gettext plural expression. This is a tree
 * containing further expressions depending on how nested the given input is.
 * Calling the `evaluate()` function computes the value of the expression if the
 * variable 'n' is set a certain value. This is used to decide which plural
 * string translation to use based on the number items at hand.
 */
class Expression
{
  private $operator;
  private $operands;

  static $BINARY_OPERATORS = array(
    '==', '!=', '>=', '<=', '&&', '||', '+', '-', '*', '/', '%', '>', '<');
  static $UNARY_OPERATORS = array('!');

  /**
   * Constructor
   *
   * @param string $operator Operator for the expression.
   * @param (int|string|Expression)[] Variable number of operands of the
   * expression. One int operand is expected in case the operator is 'const'.
   * One string operand with value 'n' is expected in case the operator is
   * 'var'. For all other operators, the operands much be objects of type
   * PluralExpression. Unary operators expect one operand, binary operators
   * expect two operands and trinary operators expect three operands.
   */
  public function __construct($operator) {
    $this->operator = $operator;
    $operands = func_get_args();
    array_shift($operands);
    $this->operands = $operands;
  }

  /**
   * Return a parenthesized string representation of the expression for
   * debugging purposes.
   *
   * @return string A string representation of the expression.
   */
  public function to_string() {
    if ($this->operator == 'const' || $this->operator == 'var') {
      return $this->operands[0];
    } elseif (in_array($this->operator, self::$BINARY_OPERATORS)) {
      return sprintf(
        "(%s %s %s)", $this->operands[0]->to_string(), $this->operator,
        $this->operands[1]->to_string());
    } elseif (in_array($this->operator, self::$UNARY_OPERATORS)) {
      return sprintf(
        "(%s %s)", $this->operator, $this->operands[0]->to_string());
    } elseif ($this->operator == '?') {
      return sprintf(
        "(%s ? %s : %s)", $this->operands[0]->to_string(),
        $this->operands[1]->to_string(),
        $this->operands[2]->to_string());
    }
  }

  /**
   * Return the computed value of the expression if the variable 'n' is set to
   * a certain value.
   *
   * @param int $n The value of the variable n to use when evaluating.
   * @throws \Exception If the expression has been constructed incorrectly.
   * @return int The value of the expression after evaluation.
   */
  public function evaluate($n) {
    if (!in_array($this->operator, array('const', 'var'))) {
      $operand1 = $this->operands[0]->evaluate($n);
    }
    if (in_array($this->operator, self::$BINARY_OPERATORS) ||
        $this->operator == '?') {
      $operand2 = $this->operands[1]->evaluate($n);
    }
    if ($this->operator == '?') {
      $operand3 = $this->operands[2]->evaluate($n);
    }

    switch ($this->operator) {
      case 'const':
        return $this->operands[0];
      case 'var':
        return $n;
      case '!':
        return !($operand1);
      case '==':
        return $operand1 == $operand2;
      case '!=':
        return $operand1 != $operand2;
      case '>=':
        return $operand1 >= $operand2;
      case '<=':
        return $operand1 <= $operand2;
      case '>':
        return $operand1 > $operand2;
      case '<':
        return $operand1 < $operand2;
      case '&&':
        return $operand1 && $operand2;
      case '||':
        return $operand1 || $operand2;
      case '+':
        return $operand1 + $operand2;
      case '-':
        return $operand1 - $operand2;
      case '*':
        return $operand1 * $operand2;
      case '/':
        return (int)($operand1 / $operand2);
      case '%':
        return $operand1 % $operand2;
      case '?':
        return $operand1 ? $operand2 : $operand3;
      default:
        throw new \Exception('Invalid expression');
    }
  }
}

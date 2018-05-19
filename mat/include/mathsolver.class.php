<?php

define ('OP_PLUS',    0b0001);
define ('OP_MINUS',   0b0010);
define ('OP_DELENO',  0b0100);
define ('OP_KRAT',    0b1000);

define ('EQOP_VETSI', 0b001);
define ('EQOP_MENSI', 0b010);
define ('EQOP_ROVNO', 0b100);

/**
* Generic class to solve math formulas using static methods
*/
class FormulaSolver {
  /**
  * @var array
  */
  public static $allowed_operators = array(OP_PLUS, OP_MINUS, OP_KRAT, OP_DELENO);

  /**
  * @var array
  */
  public static $translate_operators = array('+' => OP_PLUS, '-' => OP_MINUS, 'x' => OP_KRAT, '*' => OP_KRAT, ':' => OP_DELENO, '/' => OP_DELENO);

  /**
  * @param mixed $element1 Either a FormulaElement or a number
  * @param mixed $operator Either a OperatorElement or a number or a string
  * @param mixed $element2 Either a FormulaElement or a number
  * @return integer The solution
  */
  public static function solve($element1, $operator, $element2) {
    if (is_subclass_of($element1, 'FormulaElement')) $element1 = $element1->getValue();
    if (!is_int($element1)) throw new Exception('Invalid input 1');

    if (is_subclass_of($element2, 'FormulaElement')) $element2 = $element2->getValue();
    if (!is_int($element2)) throw new Exception('Invalid input 2');

    if (is_string($operator)) {
      if (in_array($operator, static::$translate_operators)) {
        $operator = new OperatorElement(static::$translate_operators[$operator]);
      } else throw new Exception('Invalid string operator input');
    } elseif (is_int($operator)) $operator = new OperatorElement($operator);
    elseif (!is_a($operator, 'OperatorElement')) throw new Exception('Invalid operator input');
    $operator = $operator->getMath();

    $expr = $element1 . $operator . $element2;
    return eval('return '. $expr. ';');
  }

  /**
  * @param array $elements Ordered list of elements
  * @param array $operators Ordered list of operators (one less then the elements)
  * @return integer The solution
  */
  public static function multisolve($elements, $operators) {
    if (!is_array($elements) || !is_array($operators)) throw new Exception('Invalid inputs');
    if(count($elements) < 1 || count($operators) < 1) throw new Exception('No input');
    if((count($elements) - 1) != count($operators)) throw new Exception('Invalid input combination');
    $el = array_shift($elements);
    if (!is_subclass_of($el, 'FormulaElement')) throw new Exception('Invalid element input type');
    $expr = $el->getValue();
    while(count($operators) > 0 && count($elements) > 0) {
      $op = array_shift($operators);
      if (!is_a($op, 'OperatorElement')) throw new Exception('Invalid operator input type');
      $expr .= $op->getMath();
      $el = array_shift($elements);
      if (!is_subclass_of($el, 'FormulaElement')) throw new Exception('Invalid element input type');
      $expr .= $el->getValue();
    }
    return eval('return '. $expr. ';');
  }
}

?>

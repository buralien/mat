<?php

define ('OP_PLUS',    0b0001);
define ('OP_MINUS',   0b0010);
define ('OP_DELENO',  0b0100);
define ('OP_KRAT',    0b1000);

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
  * @param FormulaElement $element1
  * @param FormulaElement $element2
  * @param OperatorElement $operator
  * @return integer The solution
  */
  public static function solve($element1, $operator, $element2) {
    if (is_a($element1, 'FormulaElement')) $element1 = $element1->getValue();
    if (!is_numeric($element1)) throw new Exception('Invalid input 1');
    if (is_a($element2, 'FormulaElement')) $element2 = $element2->getValue();
    if (!is_numeric($element2)) throw new Exception('Invalid input 2');
    if (is_a($operator, 'OperatorElement')) $operator = $operator->getMath();
    if (!in_array($operator, array_keys(static::$allowed_operators))) throw new Exception('Invalid operator input');
    $expr = $element1 . $operator . $element2;
    return eval('return '. $expr. ';');
  }

  /**
  * @param array $elements Ordered list of elements
  * @param array $operators Ordered list of operators (one less then the elements)
  * @return integer The solution
  */
  public static function multisolve($elements, $operators) {
    if(count($elements) < 1 || count($operators) < 1) throw new Exception('No input');
    if((count($elements) - 1) != count($operators)) throw new Exception('Invalid input combination');
    $expr = array_shift($elements)->getValue();
    while(count($operators) > 0) {
      $expr .= array_shift($operators)->getMath();
      $expr .= array_shift($elements)->getValue();
    }
    return eval('return '. $expr. ';');
  }
}

?>

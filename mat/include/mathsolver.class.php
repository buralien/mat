<?php

/**
* Generic class to solve math formulas using static methods
*/
class FormulaSolver {
  /**
  * @param FormulaElement $element1
  * @param FormulaElement $element2
  * @param OperatorElement $operator
  * @return integer The solution
  */
  public static function solve($element1, $operator, $element2) {
    if (is_a($element1, 'FormulaElement')) $element1 = $element1->getValue();
    if (is_a($element2, 'FormulaElement')) $element2 = $element2->getValue();
    if (is_a($element2, 'OperatorElement')) $operator = $operator->getMath();
    $expr = $element1 . $operator . $element2;
    return eval('return '. $expr. ';');
  }

  /**
  * @param array $elements Ordered list of elements
  * @param array $operators Ordered list of operators (one less then the elements)
  * @return integer The solution
  */
  public static function multisolve($elements, $operators) {
    if(count($elements) < 1) return null;
    if((count($elements) - 1) != count($operators)) return null;
    $expr = array_shift($elements)->getValue();
    while(count($operators) > 0) {
      $expr .= array_shift($operators)->getMath();
      $expr .= array_shift($elements)->getValue();
    }
    return eval('return '. $expr. ';');
  }
}

?>

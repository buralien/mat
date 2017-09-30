<?php

define ('OP_PLUS', 1);
define ('OP_MINUS', 2);
define ('OP_DELENO', 4);
define ('OP_KRAT', 8);


abstract class FormulaElement {
  abstract public function toHTML();
  abstract public function toStr();
  abstract function getValue();
  public function __toString() {
    return $this->toStr();
  }

}

class PrimitiveElement extends FormulaElement {
  protected $element;

  function __construct($value = 0) {
    $this->element = intval($value);
  }

  public function randomize ($max = null, $min = 0) {
    if ($max === null) $max = mt_getrandmax();
    $this->element = mt_rand ($min, $max);
  }

  function getValue() {
    return $this->element;
  }

  public function toHTML() {
    return '<span class="primitive">' . $this. '</span>';
  }

  public function toStr() {
    return '' . $this->getValue();
  }
}

class RandomPrimitiveElement extends PrimitiveElement {
  function __construct($max = 0, $min = 0) {
    if ($max == 0) { $max = mt_getrandmax(); }
    $this->element = mt_rand($min, $max);
  }
}

class OperatorElement extends FormulaElement {
  protected $operator;

  function __construct($op = OP_PLUS) {
    $this->operator = $op;
  }

  public function getValue() {
    return $this->operator;
  }

  public function getMath () {
    return OperatorElement::getMathSymbol($this->operator);
  }

  public static function getMathSymbol ($op) {
    switch ($op) {
      case OP_PLUS:
        return "+";
      case OP_MINUS:
        return "-";
      case OP_DELENO:
        return "/";
      case OP_KRAT:
        return "*";
      default:
        return " ";
    }
  }

  public function toStr () {
    switch ($this->operator) {
      case OP_KRAT:
        return 'x';
      case OP_DELENO:
        return ':';
      default:
        return $this->getMath();
    }
  }

  public function toHTML() {
    $html = '<span class="operator">';
    switch ($this->operator) {
      case OP_DELENO:
        $html .= '&divide;';
        break;
      case OP_KRAT:
        $html .= '&times;';
        break;
      default:
        $html .= $this;
    }
    $html .= '</span>';
    return $html;
  }
}

class RandomOperatorElement extends OperatorElement {
  function __construct ($mask = 0) {
    $allowed = array();
    if (($mask & OP_PLUS) == 0) $allowed[] = OP_PLUS;
    if (($mask & OP_MINUS) == 0) $allowed[] = OP_MINUS;
    if (($mask & OP_DELENO) == 0) $allowed[] = OP_DELENO;
    if (($mask & OP_KRAT) == 0) $allowed[] = OP_KRAT;

    if (count($allowed)) {
      $index = mt_rand(0, count($allowed)-1);
      $this->operator = $allowed[$index];
    } else {
      $this->operator = pow(2, mt_rand(0,3));
    }
  }
}

class CombinedElement extends FormulaElement {
  protected $element1;
  protected $operator;
  protected $element2;

  function __construct (FormulaElement $el1 = null, OperatorElement $op = null, FormulaElement $el2 = null) {
    if ($el1 == null) {
      $this->element1 = new RandomPrimitiveElement();
    } else {
      $this->element1 = $el1;
    }
    if ($el2 == null) {
      $this->element2 = new RandomPrimitiveElement();
    } else {
      $this->element2 = $el2;
    }
    if ($op == null) {
      $this->operator = new RandomOperatorElement();
    } else {
      $this->operator = $op;
    }
  }

  public function getValue() {
    $expr = $this->element1->getValue() . $this->operator->getMath() . $this->element2->getValue();
    return eval('return '. $expr. ';');
  }

  public function toHTML() {
    $html = '(&nbsp;';
    $html .= $this->element1->toHTML(). ' ';
    $html .= $this->operator->toHTML(). '&nbsp;';
    $html .= $this->element2->toHTML(). '&nbsp;)';
    return $html;
  }

  public function toStr () {
    $text = '( ';
    $text .= $this->element1. ' ';
    $text .= $this->operator. ' ';
    $text .= $this->element2. ' )';
    return $text;
  }
}

class RandomCombinedElement extends CombinedElement {
  function __construct($max = 0, $min = 0, $opmask = 0) {
    $this->element1 = new RandomPrimitiveElement($max, $min);
    $this->element2 = new RandomPrimitiveElement($max, $min);
    $this->operator = new RandomOperatorElement($opmask);
  }
}

?>

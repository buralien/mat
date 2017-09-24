<?php

define ('OP_PLUS', 1);
define ('OP_MINUS', 2);
define ('OP_DELENO', 4);
define ('OP_KRAT', 8);


abstract class FormulaElement {
  abstract public function toHTML();
  abstract public function toStr();
  abstract function getValue();
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
    return '<span class="primitive">' . $this->toStr() . '</span>';
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
        $html .= $this->toStr();
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
  private $el1;
  private $operator;
  private $el2;

  function __construct (FormulaElement $el1 = null, OperatorElement $op = null, FormulaElement $el2 = null) {
    if ($el1 == null) {
      $this->el1 = new PrimitiveElement();
    } else {
      $this->el1 = $el1;
    }
    if ($el2 == null) {
      $this->el2 = new PrimitiveElement();
    } else {
      $this->el2 = $el1;
    }
    if ($op == null) {
      $this->operator = new OperatorElement();
    } else {
      $this->operator = $op;
    }
  }

  public function getValue() {
    $expr = $this->el1->getValue() . $this->operator->getMath() . $this->el2->getValue();
    return eval('return '. $expr. ';');
  }

  public function toHTML() {
    $html = '';
    if ($this->el1 instanceof CombinedElement) {
      $html .= '(&nbsp;'. $this->el1->toHTML(). '&nbsp;)';
    } else {
      $html .= $this->el1->toHTML(). '&nbsp;';
    }
    $html .= $this->operator->toHTML(). '&nbsp;';
    if ($this->el2 instanceof CombinedElement) {
      $html .= '(&nbsp;'. $this->el2->toHTML(). '&nbsp;)';
    } else {
      $html .= $this->el2->toHTML();
    }
    return $html;
  }

  public function toStr () {
    $text = '';
    if ($this->el1 instanceof CombinedElement) {
      $text .= '( '. $this->el1->toStr(). ' )';
    } else {
      $text .= $this->el1->toStr(). ' ';
    }
    $text .= $this->operator->toStr(). ' ';
    if ($this->el2 instanceof CombinedElement) {
      $text .= '( '. $this->el2->toStr(). ' )';
    } else {
      $text .= $this->el2->toStr();
    }
    return $text;
  }
}

?>

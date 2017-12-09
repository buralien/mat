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

class OperatorElement {
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

  public function __toString() {
    return $this->toStr();
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

class EnglishTextElement extends FormulaElement {
  protected $element;

  function __construct($value) {
    $this->element = intval($value);
  }

  public function getValue() {
    return $this->element;
  }

  public function toStr() {
    return static::sayNumber($this->element);
  }

  public function toHTML() {
    return '<span class="primitive">'. static::sayNumber($this->element). '</span>';
  }

  public static function sayNumber($number) {
    switch ($number) {
      case 0: return 'zero';
      case 1: return 'one';
      case 2: return 'two';
      case 3: return 'three';
      case 4: return 'four';
      case 5: return 'five';
      case 6: return 'six';
      case 7: return 'seven';
      case 8: return 'eight';
      case 9: return 'nine';
      case 10: return 'ten';
      case 11: return 'eleven';
      case 12: return 'twelve';
      case 13: return 'thirteen';
      case 14: return 'fourteen';
      case 15: return 'fifteen';
      case 16: return 'sixteen';
      case 17: return 'seventeen';
      case 18: return 'eighteen';
      case 19: return 'nineteen';
      default:
        $text = array();
        $m = floor($number / 1000000);
        if ($m > 0) $text[] = static::sayNumber($m). ' million';
        $number %= 1000000;
        $t = floor($number / 1000);
        if ($t > 0) $text[] = static::sayNumber($t). ' thousand';
        $number %= 1000;
        $h = floor($number / 100);
        if ($h > 0) $text[] = static::sayNumber($h). ' hundred';
        $number %= 100;
        if ($number < 20) {
          if ($number >= 0) {
            $text[] = static::sayNumber($number);
          }
        } elseif ($number > 0) {
          $d = floor($number / 10);
          switch ($d) {
            case 2: $text[] = 'twenty'; break;
            case 3: $text[] = 'thirty'; break;
            case 4: $text[] = 'fourty'; break;
            case 5: $text[] = 'fifty'; break;
            case 6: $text[] = 'sixty'; break;
            case 7: $text[] = 'seventy'; break;
            case 8: $text[] = 'eighty'; break;
            case 9: $text[] = 'ninety'; break;
          }
          $number %= 10;
          if ($number > 0) $text[] = static::sayNumber($number);
        }
        return implode(' ', $text);
    }
  }
} // class EnglishTextElement

class PhysicsElement extends FormulaElement {
  public $baseunit;
  protected $element;
  protected $prefix;
  public static $unitlist = array('m', 'g', 's', 'A', 'K', 'mol', 'cd');
  public static $prefixmap = array(
        'T' => 12,
        'G' => 9,
        'M' => 6,
        'k' => 3,
        'h' => 2,
        'da' => 1,
        'xxx' => 0,
        'd' => -1,
        'c' => -2,
        'm' => -3,
        'u' => -6,
        'n' => -9
      );

  function __construct($value) {
    $data = explode(' ', $value);
    $this->element = intval($data[0]);
    $name = $data[1];
    foreach (array_keys(static::$prefixmap) as $prefix) {
    #foreach(static::$unitlist as $unit) {
      if (strpos($name, $prefix) !== False) {
        $this->prefix = $prefix;
        $name = substr($name, strlen($prefix));
        break;
      }
    }
    $this->baseunit = $name;
  }

  public static function getPrefix($power) {
    foreach (static::$prefixmap as $prefix => $value) {
      if ($power <= $value) return $prefix;
    }
    return null;
  }

  public static function maxpower() {
    return static::$prefixmap[0];
  }
  public static function minpower() {
    return end(static::$prefixmap);
  }
  public static function maxprefix() {
    return array_keys(static::$prefixmap)[0];
  }
  public static function minprefix() {
    return end(array_keys(static::$prefixmap));
  }

  public static function getPower($prefix) {
    if (isset(static::$prefixmap[$prefix])) {
      return static::$prefixmap[$prefix];
    } else {
      return null;
    }
  }

  public function getValue($prefix = null) {
    if (in_array($prefix, array_keys(static::$prefixmap))) {
      $diffpower = static::getPower($this->prefix) - static::getPower($prefix);
      return $this->element * pow(10, $diffpower);
    } else {
      return $this->element;
    }
  }
  public function toStr($prefix = null) {
    if (in_array($prefix, array_keys(static::$prefixmap))) {
      return $this->getValue($prefix). ' '. str_replace('xxx', '', $prefix). $this->baseunit;
    } else {
      return $this->getValue($this->prefix). ' '. str_replace('xxx', '', $this->prefix). $this->baseunit;
      #return ($this->getValue($prefix) / pow(10, static::$prefixmap[$prefix])). ' '. $prefix. $this->baseunit;
    }

  }
  public function toHTML($prefix = null) {
    return '<span class="primitive">' . str_replace('u', '&micro;', $this->toStr($prefix)). '</span>';
  }
} // class PhysicsElement

class RandomPhysicsElement extends PhysicsElement {
  function __construct($maxprefix = null, $units = null) {
    if (in_array($maxprefix, array_keys(static::$prefixmap))) {
      $maxpower = static::getPower($maxprefix);
      $this->prefix = $maxprefix;
    } else {
      $maxpower = static::maxpower();
      $this->prefix = static::maxprefix();
    }
    $this->element = mt_rand(1, 10);
    if (is_array($units)) {
      $this->baseunit = $units[mt_rand(0, (count($units)-1))];
    } else {
      $this->baseunit = $this->randomUnit();
    }
  }

  public static function randomPower($minpower, $maxpower) {
    $options = array();
    foreach (static::$prefixmap as $prefix => $power) {
      if (($power >= $minpower) && ($power <= $maxpower)) {
        $options[$prefix] = $power;
      }
    }
    $max = (count($options) - 1);
    return array_values($options)[mt_rand(0, $max)];
  }

  public static function randomPrefix($minprefix = null, $maxprefix = null) {
    if ($minprefix === null) $minprefix == static::minprefix();
    if ($maxprefix === null) $maxprefix == static::maxprefix();

    $options = array();
    $minpower = static::getPower($minprefix);
    $maxpower = static::getPower($maxprefix);
    foreach (static::$prefixmap as $prefix => $power) {
      if (($power >= $minpower) && ($power <= $maxpower)) {
        $options[] = $prefix;
      }
    }
    $max = (count($options) - 1);
    return $options[mt_rand(0, $max)];
  }

  public static function randomUnit() {
    return static::$unitlist[mt_rand(0, (count(static::$unitlist) - 1))];
  }
} // class RandomPhysicsElement

class WordElement extends FormulaElement {
  protected $word;
  function __construct($word) {
    $this->word = $word;
  }
  public function getValue() {
    return $this->word;
  }
  public function toStr() {
    return $this->word;
  }
  public function toHTML() {
    return '<span class="primitive">'. $this->toStr($this->word). '</span>';
  }


} // class WordElement

class RandomWordElement extends WordElement {
  function __construct($dict) {
    $words = file($dict, FILE_IGNORE_NEW_LINES + FILE_SKIP_EMPTY_LINES);
    $index = mt_rand(0, count($words) - 1);
    $this->word = $words[$index];
    unset($words);
  }
} // class RandomWordElement

?>

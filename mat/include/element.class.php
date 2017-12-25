<?php

require_once 'mathsolver.class.php';

define ('OP_PLUS',    0b0001);
define ('OP_MINUS',   0b0010);
define ('OP_DELENO',  0b0100);
define ('OP_KRAT',    0b1000);

/**
* Parent class for all math formula elements
*/
abstract class FormulaElement {
  abstract public function toHTML();
  abstract public function toStr();
  abstract function getValue();
  public function __toString() {
    return $this->toStr();
  }
}

/**
* This class represents a single integer in any formula
*/
class PrimitiveElement extends FormulaElement {
  /**
  * @var integer
  */
  protected $element;

  /**
  * @param integer $value
  * @return void
  */
  function __construct($value) {
    if (is_numeric($value)) {
      $this->element = intval($value);
    } else throw new Exception('Invalid input exception');
  }

  /**
  * @param integer $max
  * @param integer $min
  * @return void
  */
  public function randomize ($max = -1, $min = 0) {
    if ($max < $min) $max = mt_getrandmax();
    do {
      $old = $this->element;
      $this->element = mt_rand ($min, $max);
    } while ($old == $this->element);
  }

  /**
  * @return integer
  */
  function getValue() {
    return $this->element;
  }

  /**
  * @return string
  */
  public function toHTML() {
    return '<span class="primitive">' . $this. '</span>';
  }

  /**
  * @return string
  */
  public function toStr() {
    return '' . $this->getValue();
  }
}

/**
* Random integer
*/
class RandomPrimitiveElement extends PrimitiveElement {
  /**
  * @param integer $max
  * @param integer $min
  * @return void
  */
  function __construct($max = -1, $min = 0) {
    if (!is_numeric($max) || !is_numeric($min)) throw new Exception('Invalid limit input exception');
    if ($max < $min) { $max = mt_getrandmax(); }
    $this->element = mt_rand($min, $max);
  }
}

/**
* Simple operator class - plus, minus, multiplication and division
*/
class OperatorElement {
  /**
  * Use OP_ constants to set the value.
  * @var integer
  */
  protected $operator;

  /**
  * @param integer $operator
  */
  function __construct($operator) {
    if (in_array($operator, array_values(FormulaSolver::$allowed_operators), true)) {
      $this->operator = $operator;
    } else throw new Exception('Invalid operator input exception');
  }

  /**
  * @return integer
  */
  public function getValue() {
    return $this->operator;
  }

  /**
  * @return string
  */
  public function getMath () {
    return static::getMathSymbol($this->operator);
  }

  /**
  * @param integer $operator
  * @return string
  */
  public static function getMathSymbol ($operator) {
    switch ($operator) {
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

  /**
  * @return string
  */
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

  /**
  * @return string
  */
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

/**
* Random operator class
*/
class RandomOperatorElement extends OperatorElement {
  /**
  * @param integer $mask Bitmask of operators that should be _excluded_ from the set
  * @return void
  */
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

/**
* This class groups an expression like 3+5, consisting of two elements and an operator in between.
* Each element can be any FormulaElement, so more complicated formulas are possible.
*/
class CombinedElement extends FormulaElement {
  /**
  * @var FormulaElement
  */
  protected $element1;

  /**
  * @var OperatorElement
  */
  protected $operator;

  /**
  * @var FormulaElement
  */
  protected $element2;

  /**
  * @param mixed $el1
  * @param mixed $op
  * @param mixed $el2
  * @return void
  */
  function __construct ($el1 = null, $op = null, $el2 = null) {
    if (is_subclass_of($el1, 'FormulaElement')) {
      $this->element1 = $el1;
    } else {
      $this->element1 = new PrimitiveElement($el1);
    }

    if (is_subclass_of($el2, 'FormulaElement')) {
      $this->element2 = $el2;
    } else {
      $this->element2 = new PrimitiveElement($el2);
    }

    if (is_a($op, 'OperatorElement')) {
      $this->operator = $op;
    } else {
      $this->operator = new OperatorElement($op);
    }
  }

  /**
  * @return integer
  */
  public function getValue() {
    return FormulaSolver::solve($this->element1, $this->operator, $this->element2);
  }

  /**
  * @return string
  */
  public function toHTML() {
    $html = '(&nbsp;';
    $html .= $this->element1->toHTML(). ' ';
    $html .= $this->operator->toHTML(). '&nbsp;';
    $html .= $this->element2->toHTML(). '&nbsp;)';
    return $html;
  }

  /**
  * @return string
  */
  public function toStr () {
    $text = '( ';
    $text .= $this->element1. ' ';
    $text .= $this->operator. ' ';
    $text .= $this->element2. ' )';
    return $text;
  }
}

/**
* Random combined element - two integers with a simple operator between them
*/
class RandomCombinedElement extends CombinedElement {
  /**
  * @param integer $max
  * @param integer $min
  * @param integer $opmask
  * @return void
  */
  function __construct($max = -1, $min = 0, $opmask = 0) {
    if ($max < $min) { $max = mt_getrandmax(); }
    do {
      $this->element1 = new RandomPrimitiveElement($max, $min);
      $this->element2 = new RandomPrimitiveElement($max, $min);
      $this->operator = new RandomOperatorElement($opmask);
    } while ($this->getValue() > $max || $this->getValue() < $min || $this->getValue() != floor($this->getValue()));
  }
}

class FractionElement extends FormulaElement {
/**
* Class representing a positive integer that can be expressed as english words
*/
class EnglishTextElement {
  /**
  * @var integer
  */
  protected $element;

  /**
  * @param integer $value
  * @return void
  */
  function __construct($value) {
    $this->element = intval($value);
  }

  /**
  * @return integer
  */
  public function getValue() {
    return $this->element;
  }

  /**
  * @return string Returns the number as english words
  */
  public function toStr() {
    return static::sayNumber($this->element);
  }
  function __toString() {
    return $this->toStr();
  }

  /**
  * @return string Returns the number as english words
  */
  public function toHTML() {
    return '<span class="primitive">'. $this. '</span>';
  }

  /**
  * @param integer $number
  * @return string Return english text representing a number up to a billion
  */
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

/**
* Class representing a SI (or other) unit value
*/
class PhysicsElement {
  /**
  * @var string SI (or other) unit
  */
  public $baseunit;

  /**
  * @var integer
  */
  protected $element;

  /**
  * @var string Prefix determining the power of 10
  */
  protected $prefix;

  /**
  * List of all base SI units
  */
  public static $unitlist = array('m', 'g', 's', 'A', 'K', 'mol', 'cd');

  /**
  * List of all common prefixes
  */
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

  /**
  * @param array $value
  * @return void
  */
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

  /**
  * @param integer $power
  * @return string Prefix representing the respective power
  */
  public static function getPrefix($power) {
    foreach (static::$prefixmap as $prefix => $value) {
      if ($power <= $value) return $prefix;
    }
    return null;
  }

  /**
  * @return integer
  */
  public static function maxpower() {
    return static::$prefixmap[0];
  }

  /**
  * @return integer
  */
  public static function minpower() {
    return end(static::$prefixmap);
  }

  /**
  * @return string
  */
  public static function maxprefix() {
    return array_keys(static::$prefixmap)[0];
  }

  /**
  * @return string
  */
  public static function minprefix() {
    return end(array_keys(static::$prefixmap));
  }

  /**
  * @param string $prefix
  * @return integer Power of 10 representing the prefix
  */
  public static function getPower($prefix) {
    if (isset(static::$prefixmap[$prefix])) {
      return static::$prefixmap[$prefix];
    } else {
      return null;
    }
  }

  /**
  * @param string $prefix
  * @return integer Return the element value converted to a base unit (no prefix)
  */
  public function getValue($prefix = null) {
    if (in_array($prefix, array_keys(static::$prefixmap))) {
      $diffpower = static::getPower($this->prefix) - static::getPower($prefix);
      return $this->element * pow(10, $diffpower);
    } else {
      return $this->element;
    }
  }

  /**
  * @param string $prefix
  */
  public function toStr($prefix = null) {
    if (in_array($prefix, array_keys(static::$prefixmap))) {
      return $this->getValue($prefix). ' '. str_replace('xxx', '', $prefix). $this->baseunit;
    } else {
      return $this->getValue($this->prefix). ' '. str_replace('xxx', '', $this->prefix). $this->baseunit;
      #return ($this->getValue($prefix) / pow(10, static::$prefixmap[$prefix])). ' '. $prefix. $this->baseunit;
    }
  }
  function __toString() {
    return $this->toStr();
  }

  /**
  * @param string $prefix
  */
  public function toHTML($prefix = null) {
    return '<span class="primitive">' . str_replace('u', '&micro;', $this->toStr($prefix)). '</span>';
  }
} // class PhysicsElement

class RandomPhysicsElement extends PhysicsElement {

  /**
  * @param string $maxprefix
  * @param array $units
  * @return void
  */
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

  /**
  * @param integer $minpower
  * @param integer $maxpower
  * @return integer
  */
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

  /**
  * @param integer $minprefix
  * @param integer $maxprefix
  * @return string
  */
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

  /**
  * @return string
  */
  public static function randomUnit() {
    return static::$unitlist[mt_rand(0, (count(static::$unitlist) - 1))];
  }
} // class RandomPhysicsElement

/**
* Class representing a word
*/
class WordElement {
  /**
  * @var string
  */
  protected $word;

  /**
  * @param string $word
  */
  function __construct($word) {
    $this->word = $word;
  }

  public function getValue() {
    return $this->word;
  }

  public function toStr() {
    return $this->word;
  }

  function __toString() {
    return $this->toStr();
  }

  public function toHTML() {
    return '<span class="primitive">'. $this->toStr($this->word). '</span>';
  }
} // class WordElement

/**
* Random word from a dictionary file
*/
class RandomWordElement extends WordElement {
  /**
  * @param string $dict Dictionary file path
  * @return void
  */
  function __construct($dict) {
    $words = file($dict, FILE_IGNORE_NEW_LINES + FILE_SKIP_EMPTY_LINES);
    $index = mt_rand(0, count($words) - 1);
    $this->word = $words[$index];
    unset($words);
  }
} // class RandomWordElement

?>

<?php


/**
* Basic class for simple formulas with two numbers and one operator
*/
class SimpleFormula extends Formula {
  public static $name = 'Aritmetika';
  public static $subject = 'Matematika';
  public static $advanced = 'ignore';
  protected $element1;
  protected $operator;
  protected $element2;

  /**
  * @param integer $el1 Element 1
  * @param integer $op Operator. Use the OP_ constants to pass value
  * @param integer $el2 Element 2
  * @return void
  */
  function __construct ($el1, $op, $el2) {
    $this->element1 = new PrimitiveElement($el1);
    $this->element2 = new PrimitiveElement($el2);
    $this->operator = new OperatorElement($op);
    if (($op == OP_DELENO) && ($el2 == 0)) {
      $this->element2->randomize(1);
    }
  }

  /**
  * @return integer The solution
  */
  public function getResult () {
    return FormulaSolver::solve($this->element1, $this->operator, $this->element2);
  }

  public function toStr ($result = FALSE) {
    $text = $this->element1. ' ' . $this->operator. ' ' . $this->element2;
    if ($result) {
      $text .= ' = '. $this->getResult();
    }
    return $text;
  }

  /**
  * @ignore
  */
  public static function fromStr($frml) {
    $matches = array();
    $res = preg_match('(\d+)(\w)(\d+)', $frml, $matches);
    $matches[1] = strtr($matches[1], "+-x:", "1248");
    return $matches;
  }

  public function toHTML ($result = FALSE) {
    $html = '<span class="formula">';
    $html .= $this->element1->toHTML() . ' ';
    $html .= $this->operator->toHTML() . '&nbsp;';
    $html .= $this->element2->toHTML(). ' =&nbsp;';
    if ($result) {
      $html .= '<span class="result">'. $this->getResult(). '</span>';
    }
    $html .= '</span>';
    return $html;
  }
} // class SimpleFormula

/**
* Randomly generated SimpleFormula object
*/
class RandomSimpleFormula extends SimpleFormula {
  protected $max;
  public static $advanced = 'do {number} (operace {opmask})';

  /**
  * @param integer $max Maximum number. Default floor(mt_getrandmax() / 4)
  * @param integer $opmask Bitmask of operators to exclude
  * @return void
  */
  function __construct ($max = null, $opmask = null) {
    if ($max === null) $this->max = floor(mt_getrandmax() / 4);
    if ($opmask === null) $opmask = 0;
    $this->max = $max;
    do {
      $this->operator = new RandomOperatorElement($opmask);
      if ($this->operator->getMath() == '/') {
        do {
          $b = $this->getNumber(floor($max / 10), 2, array(1, 10), array(0));
          $c = $this->getNumber(ceil($max / $b), 2, array(1, 10), array(0));
          $a = $b * $c;
        } while (($a > $max) || ($a <= 0));
        $this->element1 = new PrimitiveElement($b * $c);
        $this->element2 = new PrimitiveElement($b);
      } else {
        $this->element1 = new RandomPrimitiveElement($max, 1);
        $this->element2 = new RandomPrimitiveElement($max, 1);
      }
      $res = $this->getResult();
    } while (($res > $max) || ($res < 0) || ($res != floor($res)));
  }

  public function getName() {
    return static::$name. ' do '. $this->max;
  }
} // class RandomSimpleFormula

/**
* Basic formula consisting of three integers and two operators.
* This is most likely redundant with MultiFormula
*/
class TripleFormula extends Formula {
  public static $name = "Aritmetika (3 &ccaron;&iacute;sla)";
  public static $subject = 'Matematika';
  public static $advanced = 'ignore';
  protected $element1;
  protected $operator1;
  protected $element2;
  protected $operator2;
  protected $element3;

  /**
  * @param integer $el1 Element 1
  * @param integer $op1 Operator 1. Use the OP_ constants to pass value
  * @param integer $el2 Element 2
  * @param integer $op2 Operator 2. Use the OP_ constants to pass value
  * @param integer $el3 Element 3
  * @return void
  */
  function __construct ($el1, $op1, $el2, $op2, $el3) {
    $this->element1 = new PrimitiveElement($el1);
    $this->operator1 = new OperatorElement($op1);
    do {
      $this->element2 = new PrimitiveElement($el2);
    } while (($this->operator1->getValue() == OP_DELENO) && ($this->element2->getValue() == 0));
    $this->operator2 = new OperatorElement($op2);
    do {
      $this->element3 = new PrimitiveElement($el3);
    } while (($this->operator2->getValue() == OP_DELENO) && ($this->element3->getValue() == 0));
  }

  public function getResult () {
    return FormulaSolver::multisolve(array($this->element1, $this->element2, $this->element3), array($this->operator1, $this->operator2));
  }
  public function toStr ($result = FALSE) {
    $text = $this->element1. ' ' . $this->operator1. ' ' . $this->element2. ' ' . $this->operator2. ' ' . $this->element3;
    if ($result) {
      $text .= ' = '. $this->getResult();
    }
    return $text;
  }
  public function toHTML ($result = FALSE) {
    $html = '<span class="formula">';
    $html .= $this->element1->toHTML() . ' ';
    $html .= $this->operator1->toHTML() . '&nbsp;';
    $html .= $this->element2->toHTML() . ' ';
    $html .= $this->operator2->toHTML() . '&nbsp;';
    $html .= $this->element3->toHTML(). ' =&nbsp;';
    if ($result) {
      $html .= '<span class="result">'. $this->getResult(). '</span>';
    }
    $html .= '</span>';
    return $html;
  }
} // class TripleFormula

/**
* Special case of a RandomSimpleFormula - multiplication of single digit numbers
*/
class MalaNasobilka extends RandomSimpleFormula {
  public static $name = 'Mal&aacute; n&aacute;sobilka';
  public static $advanced = 'do {number} (&rcaron;&aacute;d {number})';

  /**
  * @param integer $max Maximum value of each element (not the result!) Cannot exceed 10. Default 10
  * @param integer $power Power of 10 to which one of the elements can be increased
  * @return void
  */
  function __construct ($max = null, $power = null) {
    if ($max === null || $max > 10) $max = 10;
    if ($power === null || $power < 1) $power = 1;
    $this->max = $max;
    $this->operator = new OperatorElement(OP_KRAT);
    $this->element2 = new PrimitiveElement($this->getNumber($max, 0, array(0, 1, 10)));
    $a = $this->getNumber(10, 1, array(0, 1, 10));
    for($b=2;$b<=$power;$b++) {
      if (mt_rand(1,2) == 1) $a *= 10;
    }
    do {
      $this->element1 = new PrimitiveElement($a);
    } while ($this->getResult() > (pow(10, $power) * $max));
  }

  public function getName() {
    return static::$name. (($this->max != 10) ? ' do '. $this->max : '');
  }
} // class MalaNasobilka

/**
* Simple multiplication formula, where the second element is always a single digit number
*/
class StredniNasobilka extends RandomSimpleFormula {
  public static $name = 'N&aacute;sobilka';
  public static $advanced = 'do {number}';

  /**
  * @param integer $max Maximum value of the first element (not the result!) Default 100
  * @return void
  */
  function __construct ($max = null) {
    if ($max === null || $max < 11) $max = 100;
    $this->max = $max;
    do {
      $this->element1 = new PrimitiveElement($this->getNumber($max, 11));
    } while ($this->element1->getValue() % 10 == 0);
    $this->element2 = new PrimitiveElement($this->getNumber(10, 2, array(10)));
    $this->operator = new OperatorElement(OP_KRAT);
  }
}

/**
* Multiplication of two elements >11
*/
class VelkaNasobilka extends StredniNasobilka {
  public static $name = 'Velk&aacute; n&aacute;sobilka';

  /**
  * @param integer $max Maximum value of each element (not the result!) Default 100
  * @return void
  */
  function __construct ($max = null) {
    if ($max === null || $max < 11) $max = 100;
    $this->max = $max;
    do {
      $this->element1 = new PrimitiveElement($this->getNumber($max, 11));
    } while ($this->element1->getValue() % 10 == 0);
    do {
      $this->element2 = new PrimitiveElement($this->getNumber($max, 11));
    } while ($this->element2->getValue() % 10 == 0);
    $this->operator = new OperatorElement(OP_KRAT);
  }
}

/**
* Division with remainder
*/
class DeleniSeZbytkem extends RandomSimpleFormula {
  public static $name = 'D&ecaron;len&iacute; se zbytkem';
  public static $advanced = 'do {number}';

  /**
  * @param integer $max Maximum value of the dividend (not the result!) Default floor(mt_getrandmax() / 4)
  * @param integer $el1 Dividend. By default a random one is generated between 2 and $max
  * @param integer $el2 Divisor. By default a random one is generated between 2 and 10
  * @return void
  */
  function __construct ($max = null, $el1 = null, $el2 = null) {
    if ($max === null) $max = floor(mt_getrandmax() / 4);
    if ($max < 2) $max = 100;

    $this->operator = new OperatorElement(OP_DELENO);

    if ($el2 === null) {
      $this->element2 = new PrimitiveElement($this->getNumber(10, 2, array(10), array(0, 1)));
    } else {
      $this->element2 = new PrimitiveElement($el2);
    }

    if ($el1 === null) {
      do {
        $this->element1 = new PrimitiveElement($this->getNumber($max, 2, array(10), array(0, 1)));
      } while (($this->element1->getValue() / $this->element2->getValue()) > ceil($max / 10));
    } else {
      $this->element1 = new PrimitiveElement($el1);
    }
  }

  function getResult() {
    return array(floor($this->element1->getValue() / $this->element2->getValue()), ($this->element1->getValue() % $this->element2->getValue()));
  }

  function toStr($result = FALSE) {
    $text = parent::toStr(FALSE);
    if ($result) {
      $r = $this->getResult();
      $text .= ' = '. $r[0]. ' zbytek '. $r[1];
    }
    return $text;
  }

  function getResultHTMLForm () {
    $html = parent::getResultHTMLForm();
    $html .= ' zbytek&nbsp;<input type="number" class="result" name="result2" />';
    return $html;
  }

  public function toHTML ($result = FALSE) {
    $html = '<span class="formula">';
    $html .= $this->element1->toHTML() . '&nbsp;';
    $html .= $this->operator->toHTML() . '&nbsp;';
    $html .= $this->element2->toHTML(). '&nbsp;=&nbsp;';
    if ($result) {
      $res = $this->getResult();
      $html .= '<span class="result">'. $res[0]. ' zbytek&nbsp;'. $res[1]. '</span>';
    }
    $html .= '</span>';
    return $html;
  }

  public function validateResult($input) {
    if (is_array($input)) {
      if (count($input) == 2) {
        return ( $this->getResult() == array_values($input) );
      } else return FALSE;
    } else return FALSE;
  }
} // class DeleniSeZbytkem

/**
* Addition and subtraction
*/
class VelkeScitani extends RandomSimpleFormula {
  public static $name = 'S&ccaron;&iacute;t&aacute;n&iacute; a od&ccaron;&iacute;t&aacute;n&iacute;';
  public static $advanced = 'ignore';

  /**
  * @param integer $max Maximum value of each element and the result. Default 1000
  * @return void
  */
  function __construct($max = null) {
    if ($max === null) $max = 1000;
    $this->max = $max;
    do {
      $this->element1 = new RandomPrimitiveElement($max, 11);
      $this->element2 = new RandomPrimitiveElement($max, 11);
      $this->operator = new RandomOperatorElement(OP_KRAT + OP_DELENO);
      $res = $this->getResult();
    } while (($res > $max) || ($res < 0));
  }
}

/**
* Two addition/subtraction formulas combined
*/
class DvaSoucty extends TripleFormula {
  public static $name = "S&ccaron;&iacute;t&aacute;n&iacute; a od&ccaron;&iacute;t&aacute;n&iacute; (3 &ccaron;&iacute;sla)";
  protected $max;
  public static $advanced = 'do {number}';

  /**
  * @param integer $max Maximum value of each element and the result. Default 1000
  * @return void
  */
  function __construct ($max = null) {
    if ($max === null) $max = 1000;
    $this->max = $max;
    do {
      $this->element1 = new RandomPrimitiveElement($max, 11);
      $this->element2 = new RandomPrimitiveElement($max, 11);
      $this->operator1 = new RandomOperatorElement(OP_DELENO + OP_KRAT);
      $res1 = FormulaSolver::solve($this->element1, $this->operator1, $this->element2);
    } while (($res1 > $max) || ($res1 < 0));

    do {
      $this->element3 = new RandomPrimitiveElement($max, 11);
      $this->operator2 = new RandomOperatorElement(OP_DELENO + OP_KRAT);
      $res2 = $this->getResult();
    } while (($res2 > $max) || ($res2 < 0));
  }

  /**
  * @return string
  */
  public function getName() {
    return static::$name. ' do '. $this->max;
  }
}

/**
* Converting to and from Roman numerals
*/
class RomanNumerals extends Formula {
  public static $name = '&Rcaron;&iacute;msk&eacute; &ccaron;&iacute;slice';
  public static $subject = 'Matematika';
  public static $advanced = 'do {number}';
  protected $element;
  protected $max;

  /**
  * @var array Roman numerals lookup table
  */
  protected $lookup = array(
    'M' => 1000,
    'CM' => 900,
    'D' => 500,
    'CD' => 400,
    'C' => 100,
    'XC' => 90,
    'L' => 50,
    'XL' => 40,
    'X' => 10,
    'IX' => 9,
    'V' => 5,
    'IV' => 4,
    'I' => 1);

  /**
  * @param integer $max Default 2000
  * @return void
  */
  function __construct($max = null) {
    if ($max === null) $max = 2000;
    $this->max = $max;
    $this->element = new RandomPrimitiveElement($max);
  }

  public function getName() {
    return static::$name. ' do '. $this->max;
  }

  /**
  * @return string Roman numeral representation
  */
  private function toRoman() {
    $number = intval($this->element->getValue());
    $result = '';

    foreach($this->lookup as $roman => $value) {
      $matches = floor($number/$value);
      $result .= str_repeat($roman,$matches);
      $number = $number % $value;
    }
    return $result;
  }

  /**
  * @param string $number Number represented in Romal numerals
  * @return integer
  */
  private static function romanToInt($number) {
    $result = 0;
    $valid = TRUE;
    $number = str_split($number);
    while((count($number) > 0) && ($valid)) {
      $valid = FALSE;
      foreach($this->lookup as $roman => $value) {
        if(strpos($number, $roman) !== FALSE) {
          $result += $value;
          for($i=1;$i<=strlen($roman);$i++) { array_shift($number); }
          $valid = TRUE;
        }
      }
    }
    return $result;
  }

  function getResult() {
    return $this->element->getValue();
  }

  function toStr($result = FALSE) {
    $text = $this->toRoman();
    if ($result) {
      $text .= ' = '. $this->getResult();
    }
    return $text;
  }

  function toHTML($result = FALSE) {
    $html = '<span class="formula">';
    $html .= '<span class="primitive">'. $this->toRoman(). '</span> =&nbsp;';
    if ($result) { $html .= '<span class="result">'. $this->getResult(). '</span>'; }
    $html .= '</span>';
    return $html;
  }
}

/**
* Generic class representing a formula with any number of elements
* and the respective operators.
*/
class MultiFormula extends Formula {
  public static $name = 'Aritmetika s v&iacute;ce &ccaron;&iacutesly';
  public static $subject = 'Matematika';
  public static $advanced = 'ignore';
  protected $elements;
  protected $operators;

  /**
  * This function works with variable number of parameters.
  * You can pass two arrays - first one for elements and second one for operators
  * You can also pass any number of FormulaElement and OperatorElement objects
  * in the correct order (alternating elements and operators)
  *
  * @return void
  */
  function __construct() {
    #TODO: must define case with too few params
    $params = func_get_args();
    if ((count($params) == 2) && (is_array($params[0])) && (is_array($params[1]))) {
      foreach($params[0] as $e) {
        if(is_a($e, 'FormulaElement')) $this->elements[] = $e;
        elseif(is_numeric($e)) $this->elements[] = new PrimitiveElement($e);
      }
      foreach($params[1] as $o) {
        if(is_a($o, 'OperatorElement')) $this->operators[] = $o;
      }
    } else {
      $next = array_shift($params);
      if(is_a($next, 'FormulaElement')) $this->elements[] = $next;
      elseif(is_numeric($next)) $this->elements[] = new PrimitiveElement($next);
      while (count($params) > 1) {
        $next = array_shift($params);
        if(is_a($next, 'OperatorElement')) $this->operators[] = $next;
        else continue;
        $next = array_shift($params);
        if(is_a($next, 'FormulaElement')) $this->elements[] = $next;
        elseif(is_numeric($next)) $this->elements[] = new PrimitiveElement($next);
      }
    }
  }

  function getResult() {
    return FormulaSolver::multisolve($this->elements, $this->operators);
  }

  function toStr($result = FALSE) {
    $e = $this->elements;
    $op = $this->operators;
    $text = array_shift($e);
    while(count($op)) {
      $text .= ' ';
      $text .= array_shift($op);
      $text .= ' ';
      $text .= array_shift($e);
    }
    if ($result) {
      $text .= ' = '. $this->getResult();
    }
    return $text;
  }

  function toHTML($result = FALSE) {
    $e = $this->elements;
    $op = $this->operators;
    $text = '<span class="formula">'. array_shift($e)->toHTML();
    while(count($op) > 0) {
      $text .= ' ';
      $text .= array_shift($op)->toHTML();
      $text .= '&nbsp;';
      $text .= array_shift($e)->toHTML();
    }
    $text .= ' =&nbsp;';
    if ($result) {
      $text .= '<span class="result">'. $this->getResult(). '</span>';
    }
    $text .= '</span>';
    return $text;
  }
}

/**
* Randomly generated formula with multiple elements and operators
*/
class RandomSimpleMultiFormula extends MultiFormula {
  public static $name = 'Aritmetika s v&iacute;ce &ccaron;&iacutesly';
  public static $advanced = 'do {number} od {number} ({number}-{number} &ccaron;&iacute;sel)';
  protected $max;
  protected $min;

  /**
  * @var integer Maximum number of elements in the formula
  */
  protected $max_num;

  /**
  * @var integer Minimum number of elements in the formula
  */
  protected $min_num;

  /**
  * @param integer $max
  * @param integer $min
  * @param integer $min_num Minimum number of elements to generate (Default 2)
  * @param integer $max_num Maximum number of elements to generate (Default 4)
  * @return void
  */
  function __construct($max = null, $min = null, $min_num = null, $max_num = null ) {
    if ($max == null) $max = floor(mt_getrandmax() / 4);
    if ($min == null) $min = 2;
    if ($max_num == null) $max_num = 4;
    if ($min_num == null) $min_num = 2;
    if ($min < 2) $min = 2;
    if ($min_num > $max_num) $min_num = $max_num - 1;

    $this->max = $max;
    $this->min = $min;
    $this->max_num = $max_num;
    $this->min_num = $min_num;

    $num = mt_rand($min_num, $max_num - 1);
    $this->elements[] = new RandomCombinedElement($max, $min);
    $this->operators = array();
    for($i=1;$i<$num;$i++) {
      $op = new RandomOperatorElement();
      $try = 0;
      do {
        $try++;
        if ($try > 100) $op = new RandomOperatorElement();
        $el = new RandomPrimitiveElement($max, $min);
        $f = new MultiFormula(array_merge($this->elements, array($el)), array_merge($this->operators, array($op)));
        $res = $f->getResult();
      } while ((floor($res) != $res) || ($res < 0) || ($res > $max) || ((($el->getValue() == end($this->elements)->getValue()) || ($el->getValue() > 10) || (end($this->elements)->getValue() / 10 > $el->getValue())) && ($op->getValue() == OP_DELENO)));
      $this->elements[] = new PrimitiveElement($el->getValue());
      $this->operators[] = new OperatorElement($op->getValue());
    }
  }

  /**
  * @return string
  */
  public function getName() {
    $text[] = static::$name;
    if ($this->min > 2) $text[] = 'od '. $this->min;
    if ($this->max < floor(mt_getrandmax() / 4)) $text[] = 'do '. $this->max;
    if ($this->min_num == $this->max_num) $text[] = '('. $this->max_num;
      else $text[] = '('. $this->min_num. ' - '. $this->max_num;
    if ($this->max_num >= 5) $text[] = 'čísel)';
      else $text[] = 'čísla)';
    return implode(' ', $text);
  }
}

/**
* Simple formula that uses brackets
*/
class SimpleBracketFormula extends RandomSimpleFormula {
  public static $name = 'Aritmetika se z&aacute;vorkou';
  public static $advanced = 'do {number}';

  /**
  * @param integer $max Default floor(mt_getrandmax() / 4)
  * @return void
  */
  function __construct($max = null) {
    if ($max == null) {
      $max = floor(mt_getrandmax() / 4);
    }
    $this->max = $max;
    do {
      do {
        $this->element1 = new RandomCombinedElement($max, 1, OP_KRAT + OP_DELENO);
        $res = $this->element1->getValue();
      } while (($res > $max) || ($res < 2));
      $el2 = $this->getNumber(10, 2, array(1, 10), array(0));
      $this->element2 = new PrimitiveElement($el2);
      $this->operator = new RandomOperatorElement(OP_PLUS + OP_MINUS);
      $res = $this->getResult();
    } while (($res < 0) || ($res > $max) || (floor($res) < $res));
  }
}

/**
* Conversion of SI unit prefixes
*/
class PrevodJednotek extends Formula {
  public static $name = 'P&rcaron;evod jednotek';
  public static $subject = 'Matematika';
  public static $advanced = 'ignore';

  /**
  * @var PhysicsElement
  */
  private $element;

  /**
  * @var string
  */
  private $sourceprefix;

  /**
  * @var string
  */
  private $targetprefix;

  /**
  * @param string $maxprefix Default 'T'
  * @param string $minprefix Default 'n'
  * @param integer $maxvalue Default 100
  * @param array $units List of allowed SI units. Default is ('m', 'g', 's', 'A', 'K', 'mol', 'cd')
  * @return void
  */
  function __construct($maxprefix = null, $minprefix = null, $maxvalue = null, $units = null) {
    if($maxvalue === null) $maxvalue = 100;

    # Generate source and target prefixes
    $prefix1 = RandomPhysicsElement::randomPrefix($minprefix, $maxprefix);
    do {
      $prefix2 = RandomPhysicsElement::randomPrefix($minprefix, $maxprefix);
    } while ((pow(10, abs(PhysicsElement::getPower($prefix1) - PhysicsElement::getPower($prefix2))) > $maxvalue) || ($prefix1 == $prefix2));

    # Choose direction
    if (mt_rand(0,1) > 0) {
      $this->sourceprefix = $prefix1;
      $this->targetprefix = $prefix2;
    } else {
      $this->targetprefix = $prefix1;
      $this->sourceprefix = $prefix2;
    }

    # Generate the value (number)
    if (PhysicsElement::getPower($this->sourceprefix) > PhysicsElement::getPower($this->targetprefix)) {
      $value = mt_rand(1, 10);
    } else {
      $minvalue = pow(10, floor(log10($maxvalue)));
      $value = mt_rand($minvalue, $maxvalue);
    }

    # Generate the base SI unit
    if (is_array($units)) {
      $index = mt_rand(0, (count($units) - 1));
      $baseunit = $units[$index];
    } else {
      $baseunit = RandomPhysicsElement::randomUnit();
    }

    # Build the element
    $name = $value. ' '. $this->sourceprefix. $baseunit;
    $this->element = new PhysicsElement($name);
  }

  public function getResult() {
    return $this->element->getValue($this->targetprefix);
  }

  public function toStr($result = FALSE) {
    $text = $this->element->toStr($this->sourceprefix);
    if ($result) {
      $text .= ' = '. $this->element->toStr($this->targetprefix);
    }
    return $text;
  }

  public function toHTML($result = False) {
    $text = '<span class="formula">'. $this->element->toHTML($this->sourceprefix);
    $text .= '&nbsp;= ';
    if ($result) {
      $text .= '<span class="result">'. $this->element->toHTML($this->targetprefix). '</span>';
    }
    $text .= '</span>';
    return $text;
  }

  public function getResultHTMLForm() {
    return '<input type="number" class="result" name="result1" autocomplete="off" autofocus />&nbsp;<span class="formula">'. str_replace('xxx', '', $this->targetprefix). $this->element->baseunit. '</span>';
  }
}  // class PrevodJednotek

?>

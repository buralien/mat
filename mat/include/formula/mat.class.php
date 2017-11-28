<?php

class SimpleFormula extends Formula {
  public static $name = 'Aritmetika';
  public static $subject = 'Matematika';
  public static $advanced = 'ignore';
  protected $element1;
  protected $operator;
  protected $element2;

  function __construct ($el1, $op, $el2) {
    $this->element1 = new PrimitiveElement($el1);
    $this->element2 = new PrimitiveElement($el2);
    $this->operator = new OperatorElement($op);
    if (($op == OP_DELENO) && ($el2 == 0)) {
      $this->element2->randomize(1);
    }
  }

  public function getResult () {
    $expr = $this->element1->getValue() . $this->operator->getMath() . $this->element2->getValue();
    return eval('return '. $expr. ';');
  }

  public function toStr ($result = FALSE) {
    $text = $this->element1. ' ' . $this->operator. ' ' . $this->element2;
    if ($result) {
      $text .= ' = '. $this->getResult();
    }
    return $text;
  }

  public static function fromStr($frml) {
    $matches = array();
    $res = preg_match('(\d+)(\w)(\d+)', $frml, $matches);
    $matches[1] = strtr($matches[1], "x:", "*/");
    $matches[1] = strtr($matches[1], "+-/*", "1248");
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

class RandomSimpleFormula extends SimpleFormula {
  public static $name = 'Aritmetika';
  public static $subject = 'Matematika';
  public static $advanced = 'do {number} (operace {opmask})';
  function __construct ($max = null, $opmask = null) {
    if ($max === null) {
      $max = floor(mt_getrandmax() / 4);
    } else {
      self::$name .= ' do '. $max;
    }
    if ($opmask == null) $opmask = 0;
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
} // class RandomSimpleFormula

class TripleFormula extends Formula {
  public static $name = "Aritmetika (3 &ccaron;&iacute;sla)";
  public static $subject = 'Matematika';
  public static $advanced = 'ignore';
  protected $element1;
  protected $operator1;
  protected $element2;
  protected $operator2;
  protected $element3;

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
    $expr = $this->element1->getValue() . $this->operator1->getMath() . $this->element2->getValue() . $this->operator2->getMath() . $this->element3->getValue();
    return eval('return '. $expr. ';');
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

class MalaNasobilka extends SimpleFormula {
  public static $name = 'Mal&aacute; n&aacute;sobilka';
  public static $advanced = 'do {number} (&rcaron;&aacute;d {number})';
  function __construct ($max = null, $power = null) {
    if ($max == null) $max = 10;
    if ($power == null) $power = 1;
    if ($max != 10) {
      self::$name .= ' do '. $max;
    }
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
} // class MalaNasobilka

class StredniNasobilka extends SimpleFormula {
  public static $name = 'N&aacute;sobilka';
  public static $advanced = 'do {number}';
  function __construct ($max = null) {
    if ($max == null) $max = 100;
    self::$name .= ' do '. $max;
    do {
      $this->element1 = new PrimitiveElement($this->getNumber($max, 11));
    } while ($this->element1->getValue() % 10 == 0);
    $this->element2 = new PrimitiveElement($this->getNumber(10, 2, array(10)));
    $this->operator = new OperatorElement(OP_KRAT);
  }
}

class VelkaNasobilka extends SimpleFormula {
  public static $name = 'Velk&aacute; n&aacute;sobilka';
  public static $advanced = 'do {number}';
  function __construct ($max = null) {
    if ($max == null) $max = 100;
    $this->element1 = new PrimitiveElement($this->getNumber($max, 11));
    $this->element2 = new PrimitiveElement($this->getNumber($max, 11));
    $this->operator = new OperatorElement(OP_KRAT);
  }
}

class DeleniSeZbytkem extends SimpleFormula {
  public static $name = 'D&ecaron;len&iacute; se zbytkem';
  public static $advanced = 'do {number}';
  function __construct ($max = null, $el1 = null, $el2 = null) {
    if ($max == null) $max = floor(mt_getrandmax() / 4);
    self::$name .= ' do '. $max;
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

class VelkeScitani extends SimpleFormula {
  public static $name = 'S&ccaron;&iacute;t&aacute;n&iacute; a od&ccaron;&iacute;t&aacute;n&iacute;';
  public static $advanced = 'do {number}';

  function __construct($max = null) {
    if ($max == null) $max = 1000;

    self::$name .= ' do '. $max;
    do {
      $this->element1 = new RandomPrimitiveElement($max, 11);
      $this->element2 = new RandomPrimitiveElement($max, 11);
      $this->operator = new RandomOperatorElement(OP_KRAT + OP_DELENO);
      $res = $this->getResult();
    } while (($res > $max) || ($res < 0));
  }
}

class DvaSoucty extends TripleFormula {
  public static $name = "S&ccaron;&iacute;t&aacute;n&iacute; a od&ccaron;&iacute;t&aacute;n&iacute; (3 &ccaron;&iacute;sla)";
  public static $advanced = 'do {number}';

  function __construct ($max = null) {
    if ($max == null) $max = 1000;
    self::$name .= ' do '. $max;
    do {
      $this->element1 = new RandomPrimitiveElement($max, 11);
      $this->element2 = new RandomPrimitiveElement($max, 11);
      $this->operator1 = new RandomOperatorElement(OP_DELENO + OP_KRAT);
      $test = new SimpleFormula($this->element1->getValue(), $this->operator1->getValue(), $this->element2->getValue());
      $res1 = $test->getResult();
      unset($test);
    } while (($res1 > $max) || ($res1 < 0));

    do {
      $this->element3 = new RandomPrimitiveElement($max, 11);
      $this->operator2 = new RandomOperatorElement(OP_DELENO + OP_KRAT);
      $res2 = $this->getResult();
    } while (($res2 > $max) || ($res2 < 0));
  }
}

class RomanNumerals extends Formula {
  public static $name = '&Rcaron;&iacute;msk&eacute; &ccaron;&iacute;slice';
  public static $subject = 'Matematika';
  public static $advanced = 'do {number}';
  private $element;
  private $lookup = array(
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

  function __construct($max = null) {
    if ($max == null) $max = 2000;
    $this->element = new RandomPrimitiveElement($max);
  }

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

class MultiFormula extends Formula {
  public static $name = 'Aritmetika s v&iacute;ce &ccaron;&iacutesly';
  public static $subject = 'Matematika';
  public static $advanced = 'ignore';
  protected $elements;
  protected $operators;

  function __construct() {
    #TODO: must define case with too few params
    $params = func_get_args();
    if ((count($params) == 2) && (is_array($params[0])) && (is_array($params[1]))) {
      $this->elements = $params[0];
      $this->operators = $params[1];
    } else {
      $this->elements[] = array_shift($params);
      while (count($params) > 1) {
        $this->operators[] = array_shift($params);
        $this->elements[] = array_shift($params);
      }
    }
  }

  function getResult() {
    $e = $this->elements;
    $op = $this->operators;
    $expr = array_shift($e)->getValue();
    while(count($op)) {
      $expr .= array_shift($op)->getMath();
      $expr .= array_shift($e)->getValue();
    }
    return eval('return '. $expr. ';');
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
    $text = '<span class="formula"><span class="primitive">'. array_shift($e). '</span>';
    while(count($op)) {
      $text .= ' ';
      $text .= '<span class="operator">'. array_shift($op). '</span>';
      $text .= '&nbsp;';
      $text .= '<span class="primitive">'. array_shift($e). '</span>';
    }
    if ($result) {
      $text .= ' =&nbsp;<span class="result">'. $this->getResult(). '</span>';
    }
    $text .= '</span>';
    return $text;
  }

}

class RandomSimpleMultiFormula extends MultiFormula {
  public static $name = 'Aritmetika s v&iacute;ce &ccaron;&iacutesly';
  public static $advanced = 'do {number} od {number} ({number}-{number} &ccaron;&iacute;sel)';
  function __construct($max = null, $min = null, $max_num = null, $min_num = null ) {
    if ($max == null) $max = floor(mt_getrandmax() / 4);
    if ($min == null) $min = 2;
    if ($max_num == null) $max_num = 4;
    if ($min_num == null) $min_num = 2;
    if ($min < 2) $min = 2;
    if ($min_num > $max_num) $min_num = $max_num;
    $num = mt_rand($min_num, $max_num);
    $this->elements[] = new RandomPrimitiveElement($max, $min);
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
      //echo "NEXT ". $this->toStr(TRUE). "\n";
    }
  }
}

class SimpleBracketFormula extends SimpleFormula {
  public static $name = 'Aritmetika se z&aacute;vorkou';
  public static $advanced = 'do {number}';
  function __construct($max = null) {
    if ($max == null) {
      $max = floor(mt_getrandmax() / 4);
    }
    self::$name .= ' do '. $max;
    do {
      do {
        $this->element1 = new RandomCombinedElement($max, 1, OP_KRAT + OP_DELENO);
        $res = $this->element1->getValue();
      } while (($res < 0) || ($res > $max) || ($res < 2));
      $el2 = $this->getNumber(10, 2, array(1, 10), array(0));
      $this->element2 = new PrimitiveElement($el2);
      $this->operator = new RandomOperatorElement(OP_PLUS + OP_MINUS);
      $res = $this->getResult();
    } while (($res < 0) || ($res > $max) || (floor($res) < $res));
  }
}

class PrevodJednotek extends Formula {
  public static $name = 'P&rcaron;evod jednotek';
  public static $subject = 'Matematika';
  public static $advanced = 'ignore';
  private $element;
  private $sourceprefix;
  private $targetprefix;

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
      $text .= ' == '. $this->element->toStr($this->targetprefix);
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

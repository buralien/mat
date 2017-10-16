<?php
require_once 'element.class.php';

abstract class Formula {
  public static $name;
  abstract public function toHTML($result = FALSE);
  abstract public function toStr($result = FALSE);
  abstract public function getResult();
  public function __toString() {
    return $this->toStr(TRUE);
  }
  public function getResultHTMLForm() {
    return '<input type="number" class="result" name="result1" autofocus />';
  }
  public function validateResult($input) {
    if (is_array($input)) {
      return ( intval($input['result1']) == $this->getResult() );
    } else {
      return ( intval($input) == $this->getResult() );
    }
  }
} // class Formula

class SimpleFormula extends Formula {
  public static $name = 'Aritmetika';
  protected $element1;
  protected $operator;
  protected $element2;

  protected $EXCLUDE_NUMBERS = array();
  protected $LOW_PROBABILITY = array(0);
  protected function getNumber($max = 10, $min = 0) {
    $numbers = array();
    if ($min > $max) $min = $max;
    $step = (($max - $min) / 2) - 1;
    for ($j=$min; $j<=$max; $j++) {
      $k = (floor($j/$step) + 1);
      for ($i=0;$i<$k;$i++) {
        $numbers[] = $j;
        $numbers[] = $j;
      }
    }
    $numbers = array_diff($numbers, $this->EXCLUDE_NUMBERS);
    $numbers = array_diff($numbers, $this->LOW_PROBABILITY);
    foreach($this->LOW_PROBABILITY as $l) {
      if (($l <= $max) && ($l >= $min)) {
        $numbers[] = $l;
      }
    }
    $numbers = array_values($numbers);
    return $numbers[mt_rand(0, count($numbers) - 1)];
  }

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
  function __construct ($max = null, $opmask = 0) {
    if ($max === null) {
      $max = mt_getrandmax();
    } else {
      self::$name .= ' do '. $max;
    }
    do {
      $this->element1 = new RandomPrimitiveElement($max, 1);
      $this->element2 = new RandomPrimitiveElement($max, 1);
      $this->operator = new RandomOperatorElement($opmask);
      $res = $this->getResult();
    } while (($res > $max) || ($res < 0));
  }
} // class RandomSimpleFormula

class TripleFormula extends Formula {
  public static $name = "Aritmetika (3 &ccaron;&iacute;sla)";
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
  function __construct ($max = 10, $power = 1) {
    if ($max != 10) {
      self::$name .= ' do '. $max;
    }
    $this->LOW_PROBABILITY = array(0, 1, 10);
    $this->operator = new OperatorElement(OP_KRAT);
    $this->element2 = new PrimitiveElement($this->getNumber($max));
    $a = $this->getNumber(10, 1);
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
  function __construct ($max = 100) {
    self::$name .= ' do '. $max;
    $this->LOW_PROBABILITY = array(10);
    do {
      $this->element1 = new PrimitiveElement($this->getNumber($max, 11));
    } while ($this->element1->getValue() % 10 == 0);
    $this->element2 = new PrimitiveElement($this->getNumber(10, 2));
    $this->operator = new OperatorElement(OP_KRAT);
  }
}

class VelkaNasobilka extends SimpleFormula {
  public static $name = 'Velk&aacute; n&aacute;sobilka';
  function __construct () {
    $this->LOW_PROBABILITY = array(0, 1, 10, 100);
    $this->element1 = new PrimitiveElement($this->getNumber(100, 11));
    $this->element2 = new PrimitiveElement($this->getNumber(100, 11));
    $this->operator = new OperatorElement(OP_KRAT);
  }
}

class DeleniSeZbytkem extends SimpleFormula {
  public static $name = 'D&ecaron;len&iacute; se zbytkem';
  function __construct ($max = 0, $el1 = null, $el2 = null) {
    $this->operator = new OperatorElement(OP_DELENO);
    $this->EXCLUDE_NUMBERS = array(0, 1);
    $this->LOW_PROBABILITY = array(10);
    if ($max == 0) { 
      $max = mt_getrandmax(); 
    } else {
      self::$name .= ' do '. $max;
    }
    if ($el2 === null) {
      $this->element2 = new PrimitiveElement($this->getNumber(10, 2));
    } else {
      $this->element2 = new PrimitiveElement($el2);
    }
    if ($el1 === null) {
      do {
        $this->element1 = new PrimitiveElement($this->getNumber($max, 2));
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
  function __construct($max = 1000) {
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
  function __construct ($max = 1000) {
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

  function __construct($max = 2000) {
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
  function getResultHTMLForm() {
    return '<input type="text" class="result" name="result1" autofocus />';
  }

}

class MultiFormula extends Formula {
  public static $name = 'Aritmetika s v&iacute;ce &ccaron;&iacutesly';
  protected $elements;
  protected $operators;

  function __construct() {
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
  function __construct($max = 0, $min = 2, $max_num = 4, $min_num = 0 ) {
    if ($max == 0) $max = mt_getrandmax();
    if ($min < 2) $min = 2;
    if ($min_num == 0) $min_num = mt_getrandmax();
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
      } while ((floor($res) < $res) or ($res < 0) or ($res > $max) or ((($el->getValue() == end($this->elements)->getValue()) || ($el->getValue() > 10) || (end($this->elements)->getValue() / 10 > $el->getValue())) && ($op->getValue() == OP_DELENO)));
      $this->elements[] = new PrimitiveElement($el->getValue());
      $this->operators[] = new OperatorElement($op->getValue());
      //echo "NEXT ". $this->toStr(TRUE). "\n";
    }
  }
}

class SimpleBracketFormula extends SimpleFormula {
  public static $name = 'Aritmetika se z&aacute;vorkou';
  function __construct($max = 0) {
    if ($max == 0) {
      $max = mt_getrandmax();
    } else {
      $this::$name .= ' do '. $max;
    }
    $this->EXCLUDE_NUMBERS = array(0);
    $this->LOW_PROBABILITY = array(1, 10);
    do {
      do {
        $this->element1 = new RandomCombinedElement($max, 1, OP_KRAT + OP_DELENO);
        $res = $this->element1->getValue();
      } while (($res < 0) || ($res > $max) || ($res < 2));
      $el2 = $this->getNumber(10, 2);
      $this->element2 = new PrimitiveElement($el2);
      $this->operator = new RandomOperatorElement(OP_PLUS + OP_MINUS);
      $res = $this->getResult();
    } while (($res < 0) || ($res > $max) || (floor($res) < $res));
  }
}

class EnglishTextFormula extends Formula {
  public static $name = 'Anglick&eacute; &ccaron;&iacute;slovky';
  private $element;

  function __construct($max = 100, $min = 0) {
    if ($min < 0) $min = 0;
    if ($max < $min) $max = $min;
    $this->element = new EnglishTextElement(mt_rand($min, $max));
  }

  public function getResult() {
    return $this->element->getValue();
  }

  public function toStr($result = FALSE) {
    $text = $this->element->toStr();
    if ($result) {
      $text .= ' is '. $this->element->getValue();
    }
    return $text;
  }

  public function toHTML($result = FALSE) {
    $text = '<span class="formula">'. $this->element->toHTML();
    $text .= '&nbsp;is ';
    if ($result) {
      $text .= '<span class="result">'. $this->element->getValue(). '</span>';
    }
    $text .= '</span>';
    return $text;
  }
}

class ReverseEnglishTextFormula extends Formula {
  public static $name = 'Anglick&eacute; &ccaron;&iacute;slovky (z &ccaron;&iacute;sel)';
  private $element;

  function __construct($max = 100, $min = 0) {
    if ($min < 0) $min = 0;
    if ($max < $min) $max = $min;
    $this->element = new EnglishTextElement(mt_rand($min, $max));
  }

  public function getResult() {
    return $this->element->toStr();
  }

  public function toStr($result = FALSE) {
    $text = $this->element->getValue();
    if ($result) {
      $text .= ' is '. $this->element->toStr();
    }
    return $text;
  }

  public function toHTML($result = FALSE) {
    $text = '<span class="formula">'. $this->element->getValue();
    $text .= '&nbsp;is ';
    if ($result) {
      $text .= '<span class="result">'. $this->element->toHTML(). '</span>';
    }
    $text .= '</span>';
    return $text;
  }

  public function getResultHTMLForm() {
    return '<input type="text" class="result" name="result1" autocomplete="off" autofocus />';
  }

  public function validateResult($input) {
    if (is_array($input)) $input = implode(' ', $input);
    $input = strtolower($input);
    return ( $this->getResult() == $input );
  }
}

?>
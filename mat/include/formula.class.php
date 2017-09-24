<?php
require_once 'element.class.php';

abstract class Formula {
  public $name;
  abstract public function toHTML($result = FALSE);
  abstract public function toStr();
  abstract public function getResult();
  abstract public function getResultHTMLForm();
} // class Formula

class SimpleFormula extends Formula {
  protected $element1;
  protected $operator;
  protected $element2;

  function __construct ($el1, $op, $el2) {
    $this->name = "Simple formula";
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

  public function toStr () {
    return $this->element1->toStr() . ' ' . $this->operator->toStr() . ' ' . $this->element2->toStr();
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
    $html .= $this->element1->toHTML() . '&nbsp;';
    $html .= $this->operator->toHTML() . '&nbsp;'; 
    $html .= $this->element2->toHTML(). '&nbsp;=&nbsp;';
    if ($result) {
      $html .= '<span class="result">'. $this->getResult(). '</span>';
    }
    $html .= '</span>';
    return $html;
  }

  public function getResultHTMLForm() {
    $html  = '<input type="hidden" name="element1" value="'. $this->element1->getValue(). '" />';
    $html .= '<input type="hidden" name="element2" value="'. $this->element2->getValue(). '" />';
    $html .= '<input type="hidden" name="operator1" value="'. $this->operator->getValue(). '" />';
    $html .= '<input type="number" class="result" name="result1" autofocus />';
    return $html;
  }

} // class SimpleFormula

class RandomSimpleFormula extends SimpleFormula {
  function __construct ($max = null) {
    $this->name = "Simple formula";
    if ($max === null) $max = mt_getrandmax();
    do {
      $this->element1 = new RandomPrimitiveElement($max);
      $this->element2 = new RandomPrimitiveElement($max, 1);
      $this->operator = new RandomOperatorElement();
      $res = $this->getResult();
    } while (($res > $max) || ($res < 0));
  }
} // class RandomSimpleFormula

class TripleFormula extends Formula {
  protected $element1;
  protected $operator1;
  protected $element2;
  protected $operator2;
  protected $element3;

  function __construct ($el1, $op1, $el2, $op2, $el3) {
    $this->name = "Triple formula";
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
  public function toStr () {
    return $this->element1->toStr() . ' ' . $this->operator1->toStr() . ' ' . $this->element2->toStr() . ' ' . $this->operator2->toStr() . ' ' . $this->element3->toStr();
  }
  public function toHTML ($result = FALSE) {
    $html = '<span class="formula">';
    $html .= $this->element1->toHTML() . '&nbsp;';
    $html .= $this->operator1->toHTML() . '&nbsp;'; 
    $html .= $this->element2->toHTML() . '&nbsp;';
    $html .= $this->operator2->toHTML() . '&nbsp;'; 
    $html .= $this->element3->toHTML(). '&nbsp;=&nbsp;';
    if ($result) {
      $html .= '<span class="result">'. $this->getResult(). '</span>';
    }
    $html .= '</span>';
    return $html;
  }
  public function getResultHTMLForm() {
    $html  = '<input type="hidden" name="element1" value="'. $this->element1->getValue(). '" />';
    $html .= '<input type="hidden" name="element2" value="'. $this->element2->getValue(). '" />';
    $html .= '<input type="hidden" name="element3" value="'. $this->element3->getValue(). '" />';
    $html .= '<input type="hidden" name="operator1" value="'. $this->operator1->getValue(). '" />';
    $html .= '<input type="hidden" name="operator2" value="'. $this->operator2->getValue(). '" />';
    $html .= '<input type="number" class="result" name="result1" autofocus />';
    return $html;
  }
} // class TripleFormula

class MalaNasobilka extends SimpleFormula {
  function __construct ($max = 100) {
    $this->name = "Mal&aacute; n&aacute;sobilka";
    $this->operator = new OperatorElement(OP_KRAT);
    $this->element2 = new RandomPrimitiveElement(10, 2);
    $a = mt_rand(0, 10);
    $b = 10;
    while (($a * $b) < (ceil($max / $b))) {
      if (mt_rand(1,2) == 1) $a *= 10;
      $b *= 10;
    }
    do {
      $this->element1 = new PrimitiveElement($a);
    } while ($this->getResult() > $max);
  }
}

class StredniNasobilka extends SimpleFormula {
  function __construct ($max = 100) {
    $this->name = "St&rcaron;edn&iacute; n&aacute;sobilka";
    do {
      $this->element1 = new RandomPrimitiveElement($max, 11);
    } while ($this->element1->getValue() % 10 == 0);
    $this->element2 = new RandomPrimitiveElement(10, 2);
    $this->operator = new OperatorElement(OP_KRAT);
  }
}

class VelkaNasobilka extends SimpleFormula {
  function __construct () {
    $this->name = "Velk&aacute; n&aacute;sobilka";
    $this->element1 = new RandomPrimitiveElement(100, 11);
    $this->element2 = new RandomPrimitiveElement(100, 11);
    $this->operator = new OperatorElement(OP_KRAT);
  }
}

class DeleniSeZbytkem extends SimpleFormula {
  function __construct ($max = 0, $el1 = null, $el2 = null) {
    $this->name = "D&ecaron;len&iacute; se zbytkem";
    if ($max == 0) { $max = mt_getrandmax(); }
    if ($el2 === null) {
      $this->element2 = new RandomPrimitiveElement(ceil($max / 10), 2);
    } else {
      $this->element2 = new PrimitiveElement($el2);
    }
    if ($el1 === null) {
      do {
        $this->element1 = new RandomPrimitiveElement($max, 1);
      } while (($this->element1->getValue() / $this->element2->getValue()) > ceil($max / 10));
    } else {
      $this->element1 = new PrimitiveElement($el1);
    }
    $this->operator = new OperatorElement(OP_DELENO);
  }

  function getResult() {
    return array(floor($this->element1->getValue() / $this->element2->getValue()), ($this->element1->getValue() % $this->element2->getValue()));
  }

  function getResultHTMLForm () {
    $html = parent::getResultHTMLForm();
    $html .= '&nbsp;zbytek&nbsp;<input type="number" class="result" name="result2" />';
    return $html;
  }

  public function toHTML ($result = FALSE) {
    $html = '<span class="formula">';
    $html .= $this->element1->toHTML() . '&nbsp;';
    $html .= $this->operator->toHTML() . '&nbsp;'; 
    $html .= $this->element2->toHTML(). '&nbsp;=&nbsp;';
    if ($result) {
      $res = $this->getResult();
      $html .= '<span class="result">'. $res[0]. ' zbytek '. $res[1]. '</span>';
    }
    $html .= '</span>';
    return $html;
  }
} // class DeleniSeZbytkem

class VelkeScitani extends SimpleFormula {
  function __construct($max = 1000) {
    $this->name = "Velk&eacute; s&ccaron;&iacute;t&aacute;n&iacute;";
    do {
      $this->element1 = new RandomPrimitiveElement($max, 11);
      $this->element2 = new RandomPrimitiveElement($max, 11);
      $this->operator = new RandomOperatorElement(OP_KRAT + OP_DELENO);
      $res = $this->getResult();
    } while (($res > $max) || ($res < 0));
  }
}

class DvaSoucty extends TripleFormula {
  function __construct ($max = 1000) {
    $this->name = "Dva sou&ccaron;ty";
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
?>

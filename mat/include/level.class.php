<?php

require_once 'formula.class.php';

class FormulaWeight {
  private $formula;
  private $args;
  private $weight;
  function __construct($formula, $args, $weight = 1000) {
    $this->formula = $formula;
    $this->args = $args;
    $this->weight = $weight;
  }
  function getFormula() {
    $o = new \ReflectionClass($this->formula);
    return $o->newInstanceArgs($this->args);
  }
  function getWeight() {
    return $this->weight;
  }
  function getName() {
    return $this->formula;
  }
  function addWeight($weight) {
    $this->weight += $weight;
    return $this->weight;
  }
}

abstract class GenericLevel {
  protected $formulas;
  public $name;
  public $description;
  public $solved;
  public $correct;

  public function getFormula() {
    $totalweight = 0;
    foreach ($this->formulas as $frml) {
      $totalweight += $frml->getWeight();
    }
    if ($totalweight == 0) { return null; }
    $pick = mt_rand(0, $totalweight);
    foreach ($this->formulas as $frml) {
      $pick -= $frml->getWeight();
      if ($pick < 0) {
        return $frml->getFormula();
      }
    }
    return end($this->formulas)->getFormula();
  }

  public function toHTML() {
    return '<h2>'. $this->name. '</h2><p>'. $this->description. '</p>';
  }

  public function addWeight($clsid, $weight = 100) {
    for ($i=0; $i<count($this->formulas); $i++) {
      if ($this->formulas[$i]->getName() == $clsid) {
        return $this->formulas[$i]->addWeight($weight);
      }
    }
  }
}

class FormulaLevel1 extends GenericLevel {
  function __construct() {
    $this->name = '1. t&rcaron;&iacute;da';
    $this->description = 'Scitani cisel do 10';
    $this->formulas = array();
    $this->formulas[] = new FormulaWeight('RandomSimpleFormula', array(10, OP_MINUS + OP_KRAT + OP_DELENO));
  }
}

class FormulaLevel2 extends GenericLevel {
  function __construct() {
    $this->name = '2. t&rcaron;&iacute;da';
    $this->description = 'Scitani cisel do 100 a mala nasobilka do 5';
    $this->formulas = array();
    $this->formulas[] = new FormulaWeight('RandomSimpleFormula', array(100, OP_KRAT + OP_DELENO));
    $this->formulas[] = new FormulaWeight('MalaNasobilka', array(5));
  }
}

class FormulaLevel3a extends GenericLevel {
  function __construct() {
    $this->name = '3. t&rcaron;&iacute;da - 1. pololet&iacute;';
    $this->description = 'Scitani cisel do 1000 a mala nasobilka';
    $this->formulas = array();
    $this->formulas[] = new FormulaWeight('RandomSimpleFormula', array(1000, OP_KRAT + OP_DELENO));
    $this->formulas[] = new FormulaWeight('MalaNasobilka', array());
  }
}

class FormulaLevel3b extends GenericLevel {
  function __construct() {
    $this->name = '3. t&rcaron;&iacute;da - 2. pololet&iacute;';
    $this->description = 'Scitani cisel do 1000, scitani tri cisel, mala nasobilka a deleni se zbytkem';
    $this->formulas = array();
    $this->formulas[] = new FormulaWeight('RandomSimpleFormula', array(1000, OP_KRAT + OP_DELENO));
    $this->formulas[] = new FormulaWeight('DvaSoucty', array(100));
    $this->formulas[] = new FormulaWeight('MalaNasobilka', array(10, 2));
    $this->formulas[] = new FormulaWeight('DeleniSeZbytkem', array(99));
  }
}

class FormulaLevel4a extends GenericLevel {
  function __construct() {
    $this->name = '4. t&rcaron;&iacute;da - 1. pololet&iacute;';
    $this->description = 'Scitani cisel do 1000 a mala nasobilka';
    $this->formulas = array();
    $this->formulas[] = new FormulaWeight('RandomSimpleFormula', array(10000, OP_KRAT + OP_DELENO));
    $this->formulas[] = new FormulaWeight('DvaSoucty', array(1000));
    $this->formulas[] = new FormulaWeight('MalaNasobilka', array(10, 3));
    $this->formulas[] = new FormulaWeight('DeleniSeZbytkem', array(99));
  }
}

class FormulaLevelNasobilka extends GenericLevel {
  function __construct() {
    $this->name = 'Procvi&ccaron;ov&aacute;n&iacute; n&aacute;sobilky';
    $this->description = 'Mala a stredni nasobilka';
    $this->formulas = array();
    $this->formulas[] = new FormulaWeight('MalaNasobilka', array(10, 3));
    $this->formulas[] = new FormulaWeight('StredniNasobilka', array(50), 500);
    $this->formulas[] = new FormulaWeight('DeleniSeZbytkem', array(99), 500);
  }
}

?>

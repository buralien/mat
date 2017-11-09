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
    if ($this->weight + $weight > 0) $this->weight += $weight;
    return $this->weight;
  }
  public function getDescription() {
    $f = $this->formula;
    return $f::$name;
  }
  public function getSubject() {
    $f = $this->formula;
    return $f::$subject;
  }
}

abstract class GenericLevel {
  protected $formulas;
  public $name;
  public $max_formulas = 10;
  public $solved = 0;
  public $correct = 0;
  protected $solved_hash;
  public $subjects;

  public function getFormula() {
    $totalweight = 0;
    foreach ($this->formulas as $frml) {
      $totalweight += $frml->getWeight();
    }
    if ($totalweight <= 0) { return null; }
    $pick = mt_rand(0, $totalweight);
    foreach ($this->formulas as $frml) {
      $pick -= $frml->getWeight();
      if ($pick <= 0) {
        do {
          $ret = $frml->getFormula();
          $h = hash('md5', $ret);
        } while (isset($this->solved_hash[$h]));
        $this->solved_hash[$h] = 1;
        return $ret;
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

  public function getDescription() {
    $text = array();
    foreach($this->formulas as $f) {
      $text[] = $f->getDescription();
    }
    return implode(', ', array_unique($text));
  }

  protected function setSubjects() {
    $subj = array();
    foreach ($this->formulas as $formula) {
      $subj[$formula->getSubject()] = 1;
    }
    $this->subjects = array_keys($subj);
  }
}

class FormulaLevel1 extends GenericLevel {
  function __construct() {
    $this->name = '1. t&rcaron;&iacute;da';
    $this->formulas = array();
    $this->formulas[] = new FormulaWeight('RandomSimpleFormula', array(10, OP_MINUS + OP_KRAT + OP_DELENO));
    $this->setSubjects();
  }
}

class FormulaLevel2 extends GenericLevel {
  function __construct() {
    $this->name = '2. t&rcaron;&iacute;da';
    $this->formulas = array();
    $this->formulas[] = new FormulaWeight('RandomSimpleFormula', array(100, OP_KRAT + OP_DELENO));
    $this->formulas[] = new FormulaWeight('MalaNasobilka', array(5));
    $this->setSubjects();
  }
}

class FormulaLevel3a extends GenericLevel {
  function __construct() {
    $this->name = '3. t&rcaron;&iacute;da - 1. pololet&iacute;';
    $this->formulas = array();
    $this->formulas[] = new FormulaWeight('RandomSimpleFormula', array(100, OP_KRAT + OP_DELENO));
    $this->formulas[] = new FormulaWeight('MalaNasobilka', array());
    $this->formulas[] = new FormulaWeight('SimpleBracketFormula', array(100));
    $this->setSubjects();
  }
}

class FormulaLevel3b extends GenericLevel {
  function __construct() {
    $this->name = '3. t&rcaron;&iacute;da - 2. pololet&iacute;';
    $this->formulas = array();
    $this->formulas[] = new FormulaWeight('RandomSimpleFormula', array(1000, OP_KRAT + OP_DELENO));
    $this->formulas[] = new FormulaWeight('DvaSoucty', array(100));
    $this->formulas[] = new FormulaWeight('MalaNasobilka', array(10, 2));
    $this->formulas[] = new FormulaWeight('DeleniSeZbytkem', array(99));
    $this->setSubjects();
  }
}

class FormulaLevel4a extends GenericLevel {
  function __construct() {
    $this->name = '4. t&rcaron;&iacute;da - 1. pololet&iacute;';
    $this->formulas = array();
    $this->formulas[] = new FormulaWeight('RandomSimpleFormula', array(10000, OP_KRAT + OP_DELENO));
    $this->formulas[] = new FormulaWeight('DvaSoucty', array(1000));
    $this->formulas[] = new FormulaWeight('MalaNasobilka', array(10, 3));
    $this->formulas[] = new FormulaWeight('DeleniSeZbytkem', array(99));
    $this->formulas[] = new FormulaWeight('SimpleBracketFormula', array(100));
    $this->formulas[] = new FormulaWeight('PrevodJednotek', array('k', 'm', 1000, array('m', 'l')));
    $this->setSubjects();
  }
}

class FormulaLevelNasobilka extends GenericLevel {
  function __construct() {
    $this->name = 'Procvi&ccaron;ov&aacute;n&iacute; n&aacute;sobilky';
    $this->formulas = array();
    $this->formulas[] = new FormulaWeight('MalaNasobilka', array(10, 3));
    $this->formulas[] = new FormulaWeight('StredniNasobilka', array(50), 500);
    $this->formulas[] = new FormulaWeight('DeleniSeZbytkem', array(99), 500);
    $this->setSubjects();
  }
}

class FormulaLevelAnglictina extends GenericLevel {
  function __construct($max = 199) {
    $this->name = 'Anglick&eacute; &ccaron;&iacute;slovky (dikt&aacute;t)';
    $this->formulas = array();
    $this->formulas[] = new FormulaWeight('EnglishSpeechFormula', array($max));
    $this->formulas[] = new FormulaWeight('ReverseEnglishSpeechFormula', array($max));
    $this->setSubjects();
  }
}

class FormulaLevelAnglictinaNoSound extends GenericLevel {
  function __construct($max = 199) {
    $this->name = 'Anglick&eacute; &ccaron;&iacute;slovky';
    $this->formulas = array();
    $this->formulas[] = new FormulaWeight('EnglishTextFormula', array($max), 500);
    $this->formulas[] = new FormulaWeight('ReverseEnglishTextFormula', array($max));
    $this->setSubjects();
  }
}

class FormulaLevelVyjmenovanaSlova extends GenericLevel {
  function __construct() {
    $this->name = 'Vyjmenovan&aacute; slova';
    $this->formulas = array();
    $this->formulas[] = new FormulaWeight('VyjmenovanaSlova', array('i'));
    $this->formulas[] = new FormulaWeight('VyjmenovanaSlova', array('y'), 5000);
    $this->setSubjects();
  }
}

class FormulaLevelVyjmenovanaSlovaDiktat extends GenericLevel {
  function __construct() {
    $this->name = 'Vyjmenovan&aacute; slova (dikt&aacute;t)';
    $this->formulas = array();
    $this->formulas[] = new FormulaWeight('VyjmenovanaSlovaDiktat', array('i'));
    $this->formulas[] = new FormulaWeight('VyjmenovanaSlovaDiktat', array('y'), 5000);
    $this->setSubjects();
  }
}

class FormulaLevelRomanNumerals extends GenericLevel {
  function __construct() {
    $this->name = '&Rcaron;&iacute;msk&eacute; &ccaron;&iacute;slice';
    $this->formulas = array();
    $this->formulas[] = new FormulaWeight('RomanNumerals', array());
    $this->setSubjects();
  }
}

class FormulaLevelCestina3 extends GenericLevel {
  function __construct() {
    $this->name = '&Ccaron;e&scaron;tina pro 3. t&rcaron;&iacute;du';
    $this->formulas = array();
    $this->formulas[] = new FormulaWeight('SouhlaskyUprostredSlov', array(), 2000);
    $this->formulas[] = new FormulaWeight('DlouheUFormula', array());
    $this->formulas[] = new FormulaWeight('SlovniDruhy', array(), 2000);
    $this->setSubjects();
  }
}

class TestLevel extends GenericLevel {
  function __construct() {
    $this->name = 'Test';
    $this->formulas = array();
    $this->formulas[] = new FormulaWeight('SlovniDruhy', array());
    $this->setSubjects();
  }
}

?>

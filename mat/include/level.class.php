<?php

require_once 'formula.class.php';

/**
* Class that represents a formula type and the associated weight (priority) in each level
*/
class FormulaWeight {
  /**
  * @var string Name of a formula class
  */
  private $formula;

  /**
  * @var array Arguments for the constructor of the formula class
  */
  private $args;

  /**
  * @var integer Weight of this formula type - higher value is more likely to be selected
  */
  private $weight;

  /**
  * @var integer Initial weight of this formula type before being adjusted
  */
  private $initialweight;

  /**
  * @param string $formula Name of the class representing this formula type
  * @param array $args Formula class contructor arguments
  * @param integer $weight How likely this formula type is to be selected. (Default 1000)
  * @return void
  */
  function __construct($formula, $args = array(), $weight = 1000) {
    $this->formula = $formula;
    $this->args = $args;
    $this->weight = intval($weight);
    $this->initialweight = $this->weight;
  }

  /**
  * @return Formula New instance of the $formula class
  */
  public function getFormula() {
    $o = new \ReflectionClass($this->formula);
    return $o->newInstanceArgs($this->args);
  }

  /**
  * @return integer Weight of this formula type
  */
  public function getWeight() {
    return $this->weight;
  }

  /**
  * @return string Class name of this formula type
  */
  public function getName() {
    return $this->formula;
  }

  /**
  * @return array List of formula arguments passed to the constructor
  */
  public function getArgs() {
    return $this->args;
  }

  /**
  * Adjusts the weight of this formula type.
  * Weight can never go below 1
  * @param integer $weight The increase of weight (decrease if negative)
  * @return integer Adjusted new weight
  */
  public function addWeight($weight) {
    if ($this->weight + $weight > 0) {
      $this->weight += $weight;
    } else {
      $this->weight = 1;
    }
    return $this->weight;
  }

  /**
  * @return string
  */
  public function getDescription() {
    $f = $this->formula;
    return $f::$name;
  }

  /**
  * @return string
  */
  public function getSubject() {
    $f = $this->formula;
    return $f::$subject;
  }

  public function __clone() {
    $this->weight = $this->initialweight;
  }
}

/**
* Abstract class for a generic formula level.
* A level is a set of formula types with different parameters and weights.
*/
abstract class GenericLevel implements Countable, JsonSerializable  {
  /**
  * @var array List of FormulaWeight objects
  */
  protected $formulas;

  /**
  * @var string
  */
  public $name;

  /**
  * Starting amount of formulas to be solved and also the upper limit
  * The level will never go above this number when determining how many formulas
  * are remaining to be solved.
  * @var integer
  */
  public $max_formulas = 10;

  /**
  * @var integer Number of solved formulas in this level
  */
  public $solved = 0;

  /**
  * @var integer Number of correctly solved formulas in this level
  */
  public $correct = 0;

  /**
  * @var array List of hashes of formulas already generated
  */
  protected $solved_hash;

  /**
  * @var array List of subjects (topics) that are included in this level
  */
  public $subjects;

  /**
  * Creates a new instance of a random formula available in this level.
  * The formula will not be one that was already generated before, unless
  * the pool of available formulas is alrady exhausted.
  *
  * @return Formula Formula object instance, randomly selected
  */
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
        $tries = 0;
        do {
          $ret = $frml->getFormula();
          $h = hash('md5', $ret);
          $tries++;
        } while ((isset($this->solved_hash[$h])) && ($tries < 1000));
        if ($tries >= 1000) {
          $this->solved_hash = array();
        }
        $this->solved_hash[$h] = 1;
        return $ret;
      }
    }
    return end($this->formulas)->getFormula();
  }

  /**
  * @return string
  */
  public function toHTML() {
    return '<h2>'. $this->name. '</h2><p>'. $this->description. '</p>';
  }

  /**
  * Adjust the weight of the formula type
  * Weight can never go below 1
  *
  * @param string $clsid Name of the class
  * @param integer $weight Adjustment (default 100)
  * @return integer Adjusted weight or 0 if class name is not included
  */
  public function addWeight($clsid, $weight = 100) {
    for ($i=0; $i<count($this->formulas); $i++) {
      if ($this->formulas[$i]->getName() == $clsid) {
        return $this->formulas[$i]->addWeight($weight);
      }
    }
    return 0;
  }

  /**
  * @return string Comma separated list of formula type names included in the level
  */
  public function getDescription() {
    $text = array();
    foreach($this->formulas as $f) {
      $text[] = $f->getDescription();
    }
    return implode(', ', array_unique($text));
  }

  /**
  * Generates the list of subjects included in the level based on the formulas
  * @return void
  */
  protected function setSubjects() {
    $subj = array();
    foreach ($this->formulas as $formula) {
      $subj[$formula->getSubject()] = 1;
    }
    $this->subjects = array_keys($subj);
  }

  /**
  * Lists formulas with lowest success rate
  *
  * @return string Comma separated list of formula types
  */
  public function worstFormula() {
    $worst = 0;
    foreach($this->formulas as $formula) {
      if($formula->getWeight() > $worst) {
        $worst = $formula->getWeight();
      }
    }
    $ret = array();
    foreach($this->formulas as $formula) {
      if($formula->getWeight() == $worst) {
        $ret[] = $formula->getDescription();
      }
    }
    return implode(', ', $ret);
  }

  /**
  * Lists formulas with best success rate
  *
  * @return string Comma separated list of formula types
  */
  public function bestFormula() {
    $best = mt_getrandmax();
    foreach($this->formulas as $formula) {
      if($formula->getWeight() < $best) {
        $best = $formula->getWeight();
      }
    }
    foreach($this->formulas as $formula) {
      if($formula->getWeight() == $best) {
        $ret[] = $formula->getDescription();
      }
    }
    return implode(', ', $ret);
  }

  /**
  * @return integer
  */
  public function count() {
    return count($this->formulas);
  }

  /**
  * @return array
  */
  public function jsonSerialize() {
    $data = array('maxcount' => $this->max_formulas);
    if ($this->solved > 0) {
      $data['solved'] = $this->solved;
      $data['correct'] = $this->correct;
    }
    $data['params'] = array();
    $data['weight'] = array();
    foreach($this->formulas as $f) {
      $c = $f->getName();
      $data['params'][$c] = $f->getArgs();
      $data['weight'][$c] = $f->getWeight();
    }
    return $data;
  }

  public function __clone() {
    $this->solved = -1;
    $this->solved_hash = array();
    $this->correct = 0;
    $f = array();
    foreach ($this->formulas as $formula) {
      $f[] = clone $formula;
    }
    $this->formulas = $f;
  }
}

/**
* Class used to build custom level using the advanced init form
*/
class CustomLevel extends GenericLevel {
  /**
  * @return void
  */
  function __construct () {
    $this->formulas = array();
    $this->name = 'Pokročilé nastavení';
  }

  /**
  * @param string $clsid Class name
  * @param array $params Class constructor arguments
  * @return void
  */
  public function addFormula($clsid, $params = null) {
    if ($params === null) $params = array();
    if (implode('', $params) == '') $params = array();
    $this->formulas[] = new FormulaWeight($clsid, $params);
    $this->setSubjects();
  }
}


class FormulaLevel1 extends GenericLevel {
  function __construct() {
    $this->name = '1. t&rcaron;&iacute;da';
    $this->formulas = array();
    $this->formulas[] = new FormulaWeight('RandomSimpleFormula', array(20, OP_KRAT + OP_DELENO));
    $this->formulas[] = new FormulaWeight('RandomEqualityFormula', array(20));
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
    $this->formulas[] = new FormulaWeight('MalaNasobilka');
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
    $this->formulas[] = new FormulaWeight('MalaNasobilka', array(10, 3), 2000);
    $this->formulas[] = new FormulaWeight('StredniNasobilka', array(50));
    $this->formulas[] = new FormulaWeight('DeleniSeZbytkem', array(99));
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
    $this->formulas[] = new FormulaWeight('EnglishTextFormula', array($max));
    $this->formulas[] = new FormulaWeight('ReverseEnglishTextFormula', array($max), 2000);
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
    $this->formulas[] = new FormulaWeight('RomanNumerals');
    $this->setSubjects();
  }
}

class FormulaLevelCestina3 extends GenericLevel {
  function __construct() {
    $this->name = '&Ccaron;e&scaron;tina pro 3. t&rcaron;&iacute;du';
    $this->formulas = array();
    $this->formulas[] = new FormulaWeight('SouhlaskyUprostredSlov', array(), 2000);
    $this->formulas[] = new FormulaWeight('DlouheUFormula');
    $this->formulas[] = new FormulaWeight('SlovniDruhy', array(), 2000);
    $this->formulas[] = new FormulaWeight('SkladbaSlova', array(), 2000);
    $this->setSubjects();
  }
}

class FormulaLevelPreschool extends GenericLevel {
  function __construct() {
    $this->name = 'Předškolní počítání';
    $this->formulas = array();
    $this->formulas[] = new FormulaWeight('PreschoolCountFormula', array(9));
    $this->formulas[] = new FormulaWeight('PreschoolAdditionFormula', array(9));
    $this->formulas[] = new FormulaWeight('PreschoolMatrixFormula', array(9));
    $this->setSubjects();
  }
}

class TestLevel extends GenericLevel {
  function __construct() {
    $this->name = 'Test';
    $this->formulas = array();
    $this->formulas[] = new FormulaWeight('SlovniDruhy');
    $this->setSubjects();
  }
}

?>

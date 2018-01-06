<?php
require_once 'element.class.php';

/**
* Abstract class for all formulas.
* A formula is anything that can be solved - f.e. a math problem
*/
abstract class Formula {
  /**
  * @var string Human readable name of the formula type
  */
  public static $name;

  /**
  * @var string Human readable name of the subject (topic) this formula is part of
  */
  public static $subject;

  /**
  * @var string Description of the formula parameters for generating the advanced selection form
  */
  public static $advanced;

  /**
  * @param boolean $result Whether to include the result in the resulting string
  * @return string HTML code for the formula
  */
  abstract public function toHTML($result = FALSE);

  /**
  * @param boolean $result Whether to include the result in the resulting string
  * @return string Plain text representation of the formula
  */
  abstract public function toStr($result = FALSE);
  public function __toString() {
    return $this->toStr(TRUE);
  }

  /**
  * @return mixed Result (solution) for the formula
  */
  abstract public function getResult();

  /**
  * @return string HTML input code
  */
  public function getResultHTMLForm() {
    return '<input type="number" class="result" name="result1" autofocus />';
  }

  /**
  * @param mixed $input User input submitted as result
  * @return boolean Whether the submitted $input is correct solution of this formula
  */
  public function validateResult($input) {
    if (is_array($input)) {
      if (isset($input['result1'])) {
        return ( intval($input['result1']) == $this->getResult() );
      } else {
        return true;
      }
    } else {
      return ( intval($input) == $this->getResult() );
    }
  }

  /**
  * @return string Name of the formula
  */
  public function getName() { return static::$name; }

  /**
  * @return boolean Whether this formula requires the TTS engine
  */
  public function voiceEnabled() { return false; }

  /**
  * Returns a weighted random number
  *
  * @param integer $max Maximum integer included in the set of possible numbers (default 10)
  * @param integer $min Minimum integer included in the set of possible numbers (default 0)
  * @param array $low_prob List of numbers that are selected only with 1/$weight probability (default empty array)
  * @param array $exclude List of numbers that will never be selected (default (0))
  * @param integer $weight How much less likely are the low probability numbers (default 3)
  * @return integer Weighted random number in the interval $min - $max
  */
  protected function getNumber($max = 10, $min = 0, $low_prob = null, $exclude = null, $weight = 3) {
    if ($low_prob === null) $low_prob = array();
    if ($exclude === null) $exclude = array(0);

    $ex = array();
    foreach($exclude as $n) {
      if (($n <= $max) && ($n >= $min)) $ex[] = $n;
    }

    $lo = array();
    foreach($low_prob as $n) {
      if (($n <= $max) && ($n >= $min) && (!in_array($n, $ex))) $lo[] = $n;
    }
    $low = count($lo);

    $top = $low + (1 + $max - $min - $low - count($ex)) * $weight;
    if ($top < 1) $top = max($low, 1);

    $a = mt_rand(0, ($top - 1));

    if ($a < $low) {
      return $lo[$a];
    } else {
      $ex = array_merge($ex, $lo);
      sort($ex);
      $a -= $low;

      while (in_array($min, $ex)) {
        $min++;
      }

      $b = floor($a / $weight) + $min;

      foreach($ex as $e) {
        if ($b >= $e) $b++;
      }

      return $b;
    }
  }
} // class Formula

require_once 'formula/mat.class.php';
require_once 'formula/cj.class.php';
require_once 'formula/en.class.php';

?>

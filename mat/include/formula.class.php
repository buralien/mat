<?php
require_once 'element.class.php';

abstract class Formula {
  public static $name;
  public static $subject;
  public static $advanced;
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
  public function getName() {
    return static::$name;
  }
  public function voiceEnabled() { return false; }

  protected function getNumber($max = 10, $min = 0, $low_prob = null, $exclude = null) {
    $weight = 3;
    if ($low_prob === null) $low_prob = array();
    if ($exclude === null) $exclude = array(0);
    $ex = array();
    foreach($exclude as $n) {
      if (($n <= $max) && ($n >= $min)) $ex[] = $n;
    }
    $lo = array();
    foreach($low_prob as $n) {
      if (($n <= $max) && ($n >= $min)) $lo[] = $n;
    }
    $top = ($max * $weight) - ($min * $weight) + $weight;
    $top -= count($ex) * $weight;
    $low = count($lo);
    $top -=  $low * ($weight - 1);
    if ($top < 1) $top = $low;
    $a = mt_rand(0, ($top - 1));
    if ($a < $low) {
      return $lo[$a];
    } else {
      return floor($a / $weight) + $low + $min;
    }
  }
} // class Formula

require_once 'formula-mat.class.php';
require_once 'formula-cj.class.php';
require_once 'formula-en.class.php';

?>

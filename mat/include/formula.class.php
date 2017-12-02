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
      if (isset($input['result1'])) {
        return ( intval($input['result1']) == $this->getResult() );
      } else {
        return true;
      }
    } else {
      return ( intval($input) == $this->getResult() );
    }
  }
  public function getName() {
    return static::$name;
  }
  public function voiceEnabled() { return false; }

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

    $top = (1 + $max - $min - $low) * $weight;
    $top -= count($ex) * $weight;
    $top +=  $low;
    if ($top < 1) $top = $low;

    $a = mt_rand(0, ($top - 1));
    if ($a < $low) {
      return $lo[$a];
    } else {
      $a -= $low;

      while (in_array($min, $ex)) {
        $min++;
      }
      $corr = 0;
      while (in_array($min, $lo)) {
        $min++;
        $corr++;
      }

      $b = floor($a / $weight) + $min;

      foreach($ex as $e) {
        if ($b >= $e) $b++;
      }

      $b -= $corr;

      foreach($lo as $l) {
        if ($b >= $l) $b++;
      }

      return $b;
    }
  }
} // class Formula

require_once 'formula/mat.class.php';
require_once 'formula/cj.class.php';
require_once 'formula/en.class.php';

?>

<?php

class PreschoolCountFormula extends Formula {
  public static $name = "Počítání pro děti";
  public static $subject = 'Předškolní';
  public static $advanced = 'do {number}';

  protected $element;

  function __construct($max = 9) {
    $num = $this->getNumber($max, 1);
    $this->element = new PictureElement($num);
  }

  public function getResult() {
    return $this->element->getValue();
  }

  public function toStr ($result = FALSE) {
    $text = $this->element;
    if ($result) {
      $text .= ' = '. $this->getResult();
    }
    return $text;
  }

  public function toHTML ($result = FALSE) {
    $html = '<span class="formula">';
    $html .= $this->element->toHTML(). ' =&nbsp;';
    if ($result) {
      $html .= '<span class="result">'. $this->getResult(). '</span>';
    }
    $html .= '</span>';
    return $html;
  }
}

class PreschoolAdditionFormula extends SimpleFormula {
  public static $name = "Sčítání pro děti";
  public static $subject = 'Předškolní';
  public static $advanced = 'do {number}';

  /**
  * @param integer $max Maximum number. Default floor(mt_getrandmax() / 4)
  * @param integer $opmask Bitmask of operators to exclude
  * @return void
  */
  function __construct ($max = 9) {
    $this->max = $max;
    $this->operator = new OperatorElement(OP_PLUS);
    do {
      $this->element1 = new PictureElement($this->getNumber($this->max, 1));
      $this->element2 = new PictureElement($this->getNumber($this->max, 1));
      $res = $this->getResult();
    } while (($res > $this->max) || ($res < 0) || ($res != floor($res)));
  }

  public function toHTML($result = false) {
    return str_replace('+', '+<br>', parent::toHTML($result));
  }
}

?>

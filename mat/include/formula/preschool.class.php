<?php

class PreschoolCountFormula extends Formula {
  public static $name = "Počítání pro děti";
  public static $subject = 'Předškolní';
  public static $advanced = 'do {number}';

  protected $element;

  function __construct($max = 10) {
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

?>

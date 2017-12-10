<?php

class EnglishTextFormula extends Formula {
  public static $name = 'Anglick&eacute; &ccaron;&iacute;slovky';
  protected $max;
  protected $min;
  public static $subject = 'Angli&ccaron;tina';
  public static $advanced = 'do {number}';
  protected $element;

  function __construct($max = null, $min = null) {
    if ($max === null) $max = 100;
    if (($min === null)||($min < 0)) $min = 0;
    if ($max < $min) $max = $min;
    $this->max = $max;
    $this->min = $min;
    $this->element = new EnglishTextElement($this->getNumber($max, $min, array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10)));
  }

  /**
  * @return string
  */
  public function getName() {
    $text[] = static::$name;
    if ($this->min >= 0) $text[] = 'od '. $this->min;
    $text[] = 'do '. $this->max;
    return implode(' ', $text);
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
  public function getResultHTMLForm() {
    return '<input type="number" class="result" name="result1" autofocus /> (napi&scaron; &ccaron;&iacute;slo)';
  }
} // class EnglishTextFormula

class ReverseEnglishTextFormula extends EnglishTextFormula {
  public static $name = 'Anglick&eacute; &ccaron;&iacute;slovky (z &ccaron;&iacute;sel)';

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
    return '<input type="text" class="result" name="result1" autocomplete="off" autofocus /> (napi&scaron; slovy)';
  }

  public function validateResult($input) {
    if (is_array($input)) $input = implode(' ', $input);
    $input = preg_replace('/\s+/', ' ', strtolower(trim($input)));
    return ( $this->getResult() == $input );
  }
} // class ReverseEnglishTextFormula

class EnglishSpeechFormula extends EnglishTextFormula {
  public static $name = 'Anglick&eacute; &ccaron;&iacute;slovky (dikt&aacute;t - &ccaron;&iacute;sla)';

  public function voiceEnabled() { return true; }

  public function toHTML($result = FALSE) {
    if ($result) {
      return parent::toHTML(true);
    } else {
      $text = '<span class="formula">';
      $text .= "<input class='speech' onclick='responsiveVoice.speak(\"". $this->element->toStr(true). "\", \"UK English Male\", {rate: 0.6, volume: 1});document.forms[0].elements[1].focus();' type='button' value='Poslech' />";
      $text .= '</span>';
      return $text;
    }
  }
} // class EnglishSpeechFormula

class ReverseEnglishSpeechFormula extends ReverseEnglishTextFormula {
  public static $name = 'Anglick&eacute; &ccaron;&iacute;slovky (dikt&aacute;t - slova)';

  public function voiceEnabled() { return true; }

  public function toHTML($result = FALSE) {
    if ($result) {
      return parent::toHTML(true);
    } else {
      $text = '<span class="formula">';
      $text .= "<input class='speech' onclick='responsiveVoice.speak(\"". $this->element->toStr(true). "\", \"UK English Male\", {rate: 0.6, volume: 1});document.forms[0].elements[1].focus();' type='button' value='Poslech' />";
      $text .= '</span>';
      return $text;
    }
  }
} // class ReverseEnglishSpeechFormula

?>

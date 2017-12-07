<?php

class SouhlaskyUprostredSlov extends Formula {
  public static $name = 'Souhl&aacute;sky uprost&rcaron;ed slova';
  public static $subject = '&Ccaron;e&scaron;tina';
  public static $advanced = ''; #TODO: allow to choose individual dictionaries
  protected $element;
  protected $dict_source = array('include/dict/slovnik-bp.dict', 'include/dict/slovnik-dt.dict', 'include/dict/slovnik-sz.dict', 'include/dict/slovnik-vf.dict');
  protected $dict;
  protected $toreplace;

  function __construct($letter = null) {
    switch ($letter) {
      case 'b':
        $this->dict = 'include/dict/slovnik-bp.dict';
        $this->toreplace = array('b', 'p');
        break;
      case 'd':
        $this->dict = 'include/dict/slovnik-dt.dict';
        $this->toreplace = array('d', 't');
        break;
      case 's':
        $this->dict = 'include/dict/slovnik-sz.dict';
        $this->toreplace = array('s', 'z');
        break;
      case 'v':
        $this->dict = 'include/dict/slovnik-vf.dict';
        $this->toreplace = array('v', 'f');
        break;
      default:
        $this->dict = $this->dict_source[mt_rand(0, count($this->dict_source) - 1)];
        # this stupid sequence of calls is due to Strict Mode
        $a = explode('/', $this->dict);
        $a = end($a);
        $a = explode('-', $a);
        $a = end($a);
        $a = explode('.', $a);
        $a = reset($a);
        $this->toreplace = str_split($a);
    }
    if (in_array('d', $this->toreplace)) {
      $this->toreplace[] = 'ď';
    }
    if (in_array('t', $this->toreplace)) {
      $this->toreplace[] = 'ť';
    }
    if (in_array('s', $this->toreplace)) {
      $this->toreplace[] = 'š';
    }
    if (in_array('z', $this->toreplace)) {
      $this->toreplace[] = 'ž';
    }
    $this->element = new RandomWordElement($this->dict);
  }
  protected function getBlank() {
    return str_replace($this->toreplace, '_', $this->element);
  }
  protected function blankReplace($haystack, $repl) {
    return implode($repl, explode('_', $haystack, 2));
  }

  public function toHTML($result = FALSE, $cls = 'select') {
    $text = '<span class="formula">';
    if ($result) {
      $text .= $this->element->toHTML();
      $text .= ' <span class="reference">';
      $text .= '<a href="http://ssjc.ujc.cas.cz/search.php?hledej=Hledat&sti=EMPTY&where=hesla&hsubstr=no&heslo='. urlencode($this->element->toStr()). '" target="_blank">SSČ</a>, ';
      $text .= '<a href="http://prirucka.ujc.cas.cz/?slovo='. urlencode($this->element->toStr()). '" target="_blank">IJP</a></span>';
    } else {
      $form = $this->getBlank();
      $rescount = 1;
      $text .= '<label class="'. $cls. '">';
      while (strpos($form, '_') !== false) {
        $input = '<select name="result'. $rescount. '" class="'. $cls. '"><option value="*"> </option>';
        foreach($this->toreplace as $char) {
          $input .= '<option value="'. $char. '">'. htmlentities($char, ENT_HTML5, "UTF-8"). '</option>';
        }
        $input .= '</select>';
        $form = $this->blankReplace($form, $input);
        $rescount++;
        if ($rescount > 256) break;
      }
      $text .= $form. '</label>';
    }
    $text .= '</span>';
    return $text;
  }
  public function toStr($result = FALSE) {
    if ($result) {
      return $this->getBlank(). ' = '. $this->element->toStr();
    } else {
      return $this->getBlank();
    }
  }

  public function getResult() {
    $text = $this->getBlank();
    $result = array();
    while (($i = strpos($text, '_')) !== false) {
      $result[] = $this->element->toStr()[$i];
      $text = $this->blankReplace($text, '*');
    }
    return $result;
  }

  public function getResultHTMLForm() {
    return '';
  }

  public function validateResult($input) {
    $ret = false;
    if (is_array($input)) {
      $form = $this->getBlank();
      if (count($input) != substr_count($form, '_')) return false;
      while (strpos($form, '_') !== false) {
        $form = $this->blankReplace($form, array_shift($input));
      }
      $form = preg_replace('/\s+/', ' ', strtolower(trim($form)));
      if (MAT_DEBUG) print("Checking $form in ". $this->dict);
      if ($handle = fopen($this->dict, 'r')) {
        while($line = stream_get_line($handle, 256, "\n")) {
          if (strtolower(trim($line)) == $form) {
            $ret = true;
            break;
          }
        }
        fclose($handle);
      }
    }
    return $ret;
  }
} // class SouhlaskyUprostredSlov

class VyjmenovanaSlova extends SouhlaskyUprostredSlov {
  public static $name = 'Vyjmenovan&aacute; slova';
  public static $advanced = '';
  protected $dict_source = array('include/dict/slovnik-i.dict', 'include/dict/slovnik-y.dict');

  function __construct($letter = null) {
    switch ($letter) {
      case 'i':
        $this->dict = 'include/dict/slovnik-i.dict';
        break;
      case 'y':
        $this->dict = 'include/dict/slovnik-y.dict';
        break;
      default:
        $this->dict = $this->dict_source[mt_rand(0, count($this->dict_source) - 1)];
    }
    $this->toreplace = array('i', 'y', 'í', 'ý');
    $this->element = new RandomWordElement($this->dict);
  }
}

class VyjmenovanaSlovaDiktat extends VyjmenovanaSlova {
  public static $name = 'Vyjmenovan&aacute; slova (dikt&aacute;t)';

  public function voiceEnabled() { return true; }

  public function toHTML($result = FALSE) {
    if ($result) {
      return parent::toHTML(true);
    } else {
      $text = '<span class="formula">';
      $text .= "<input class='speech' onclick='responsiveVoice.speak(\"". $this->element->toStr(true). "\", \"Czech Female\", {rate: 0.7, volume: 1});document.forms[0].elements[1].focus();' type='button' value='Poslech' />";
      $text .= '</span>';
      return $text;
    }
  }

  public function getResult() {
    return $this->element->getValue();
  }

  public function getResultHTMLForm() {
    return '<input type="text" class="result" name="result1" autocomplete="off" autofocus /> (napi&scaron; jak sly&scaron;&iacute;&scaron;)';
  }

  public function validateResult($input) {
    if (is_array($input)) $input = implode(' ', $input);
    $input = strtolower($input);
    return ( $this->getResult() == $input );
  }
} // class VyjmenovanaSlovaDiktat

class DlouheUFormula extends SouhlaskyUprostredSlov {
  public static $name = 'Dlouh&eacute; u';
  public static $advanced = '';
  protected $dict_source = array('include/dict/slovnik-u.dict');

  function __construct() {
    $this->dict = 'include/dict/slovnik-u.dict';
    $this->toreplace = array('ú', 'ů');
    $this->element = new RandomWordElement($this->dict);
  }
}

class SkladbaSlova extends SouhlaskyUprostredSlov {
  public static $name = 'Skladba slova';
  public static $advanced = '';
  protected $dict_source = array('include/dict/slovnik-bevepe.dict', 'include/dict/slovnik-me.dict');

  function __construct($letter = null) {
    $this->dict = $this->dict_source[mt_rand(0, count($this->dict_source) - 1)];
    $this->element = new RandomWordElement($this->dict);
    $repl = array();
    if ((strpos($this->element, 'bě') !== false) || (strpos($this->element, 'bje') !== false)) {
      $repl['bě'] = 1;
      $repl['bje'] = 1;
    }
    if ((strpos($this->element, 'pě') !== false) || (strpos($this->element, 'pje') !== false)) {
      $repl['pě'] = 1;
      $repl['pje'] = 1;
    }
    if ((strpos($this->element, 'vě') !== false) || (strpos($this->element, 'vje') !== false)) {
      $repl['vě'] = 1;
      $repl['vje'] = 1;
    }
    if ((strpos($this->element, 'mě') !== false) || (strpos($this->element, 'mně') !== false)) {
      $repl['mě'] = 1;
      $repl['mně'] = 1;
    }
    $this->toreplace = array_keys($repl);
  }

  public function getResult() {
    $text = $this->getBlank();
    $result = array();
    while (($i = strpos($text, '_')) !== false) {
      foreach ($this->toreplace as $option) {
        $substr = substr($this->element->toStr(), $i, strlen($option));
        if($substr == $option) {
          $result[] = $substr;
        }
      }
      $text = $this->blankReplace($text, '*');
    }
    return $result;
  }

  public function toHTML($result = FALSE, $cls = 'select3') {
    return parent::toHTML($result, $cls);
  }
} // class SkladbaSlova

class SlovniDruhy extends Formula {
  protected $element;
  protected $druh;
  public static $name = 'Slovn&iacute; druhy';
  public static $subject = '&Ccaron;e&scaron;tina';
  public static $advanced = '';
  protected $dict_source = array(
    1 => 'include/dict/druh-podstatne.dict',
    2 => 'include/dict/druh-pridavne.dict',
    3 => 'include/dict/druh-zajmeno.dict',
    4 => 'include/dict/druh-cislovka.dict',
    5 => 'include/dict/druh-sloveso.dict',
    6 => 'include/dict/druh-prislovce.dict',
    7 => 'include/dict/druh-predlozka.dict',
    8 => 'include/dict/druh-spojka.dict'
  );
  protected $sl_druhy = array(
    1 => 'Podstatn&eacute; jm&eacute;no',
    2 => 'P&rcaron;&iacute;davn&eacute; jm&eacute;no',
    3 => 'Z&aacute;jmeno',
    4 => '&Ccaron;&iacute;slovka',
    5 => 'Sloveso',
    6 => 'P&rcaron;&iacute;slovce',
    7 => 'P&rcaron;edlo&zcaron;ka',
    8 => 'Spojka'
  );

  function __construct() {
    $this->druh = mt_rand(1, 8);
    $this->element = new RandomWordElement($this->dict_source[$this->druh]);
  }

  public function getResult() {
    return $this->druh;
  }

  public function toStr($result = false) {
    $text = $this->element->toStr();
    if ($result) {
      $text .= ' je '. $this->druh;
    }
    return $text;
  }

  public function toHTML($result = false) {
    $text = '<span class="formula">'. $this->element->toHTML(). '&nbsp;je ';
    if ($result) {
      $text .= $this->sl_druhy[$this->druh];
      $text .= ' <span class="reference">';
      $text .= '<a href="http://ssjc.ujc.cas.cz/search.php?hledej=Hledat&sti=EMPTY&where=hesla&hsubstr=no&heslo='. urlencode($this->element->toStr()). '" target="_blank">SSČ</a>, ';
      $text .= '<a href="http://prirucka.ujc.cas.cz/?slovo='. urlencode($this->element->toStr()). '" target="_blank">IJP</a></span>';
    } else {
      $text .= '<label class="select2"><select name="result1" class="select2"><option value="*"> </option>';
      foreach($this->sl_druhy as $number => $name) {
        $text .= '<option value="'. $number. '">'. $name. '</option>';
      }
      $text .= '</select></label></span>';
    }
    return $text;
  }

  public function getResultHTMLForm() {
    return '';
  }
} // class SlovniDruhy

?>

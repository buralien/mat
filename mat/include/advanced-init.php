<?php

class InputGenerator {
  private $number;
  function __construct() {
    $this->number = 0;
  }

  public function getNumber($name) {
    $name = 'advanced_'. $name. '_'. ++$this->number;
    $text = '<input type="number"';
    $text .= ' name="'. $name. '"';
    $text .= ' id="'. $name. '"';
    $text .= '>';
    return $text;
  }

  public function getOpMask($name) {
    $name = 'advanced_'. $name. '_'. ++$this->number;
    $text =  '<input type="checkbox" name="'. $name. '_plus" id="'. $name. '_plus" value="yes" checked="checked">';
    $text .= '<label for="'. $name. '_plus">+</label> ';
    $text .= '<input type="checkbox" name="'. $name. '_minus" id="'. $name. '_minus" value="yes" checked="checked">';
    $text .= '<label for="'. $name. '_minus">-</label> ';
    $text .= '<input type="checkbox" name="'. $name. '_krat" id="'. $name. '_krat" value="yes" checked="checked">';
    $text .= '<label for="'. $name. '_krat">&times;</label> ';
    $text .= '<input type="checkbox" name="'. $name. '_deleno" id="'. $name. '_deleno" value="yes" checked="checked">';
    $text .= '<label for="'. $name. '_deleno">&divide;</label>';

    return $text;
  }
}

function getDeclaredFormulas() {
  $ret = array();
  foreach(get_declared_classes() as $c) {
    if(isset($c::$advanced)) {
      if ($c::$advanced != 'ignore') {
        $ret[] = $c;
      }
    }
  }
  return $ret;
}

$html->addBodyContent('<h1>MAT - pokročilé nastavení</h1>');
$js = <<<JS
//<![CDATA[
function start() {
  var elems = document.getElementsByClassName('elements');
  var labels = document.getElementsByClassName('label');
  hideAll();
  for (var i = 0; i < labels.length; i++) {
    labels[i].onclick = toggleBlock;
  }
}
function hideAll() {
  var elems = document.getElementsByClassName('elements');
  for (var i = 0; i < elems.length; i++) {
    elems[i].style.display = 'none';
  }
}
function toggleBlock(evnt) {
  itemid = "elements" + evnt.target.id.substr(5, 1);
  item = document.getElementById(itemid);
  if (item.style.display=='none') {
    hideAll();
    item.style.display = 'block';
  }
}
//]]>
JS;
$html->addScriptDeclaration($js);
$html->setBodyAttributes(array('onload' => 'start();'));

$subjects = array('Matematika', '&Ccaron;e&scaron;tina', 'Angli&ccaron;tina');

$subj_form = array();

foreach ($levels as $id => $clsid) {
  $l = new $clsid();
  $subj = implode(' ', $l->subjects);
  unset($l);
}

$inputs = new InputGenerator();
$formula_count = 0;

if (MAT_DEBUG) $html->addBodyContent('<pre>Declared: '. print_r(getDeclaredFormulas(), true). '</pre>');

foreach (getDeclaredFormulas() as $formula) {
  $name = ++$formula_count;
  $subj_form[$formula::$subject][] = '<p>';
  $subj_form[$formula::$subject][] = '<input type="hidden" name="advanced_'. $name. '_clsid" value="'. $formula. '">';
  $subj_form[$formula::$subject][] = '<input type="checkbox" name="advanced_'. $name. '" id="advanced_'. $name. '" value="yes">';
  $subj_form[$formula::$subject][] = '<label for="advanced_'. $name. '">'. $formula::$name. '</label>';
  if (strlen($formula::$advanced) > 0) {
    $subj_form[$formula::$subject][] = ' ';
    $adv = explode('{', str_replace('}', '{', $formula::$advanced));
    # if (MAT_DEBUG) $html->addBodyContent('<pre>Adv: '. print_r($adv, true). '</pre>');
    foreach($adv as $part) {
      if($part == 'number') $subj_form[$formula::$subject][] = $inputs->getNumber($name);
      elseif ($part == 'opmask') $subj_form[$formula::$subject][] = $inputs->getOpMask($name);
      elseif ($part) $subj_form[$formula::$subject][] = $part;
    }
  }
  $subj_form[$formula::$subject][] = '</p>';
}

if (MAT_DEBUG) $html->addBodyContent('<pre>Subj_form: '. print_r($subj_form, true). '</pre>');

$html->addBodyContent('<form method="post">');
$subj_num = 0;
foreach($subjects as $subject) {
  $html->addBodyContent('<div class="label" id="label'. ++$subj_num. '">'. $subject. '</div>');
  $html->addBodyContent('<div class="elements" id="elements'. $subj_num. '">'. implode('', $subj_form[$subject]). '</div>');
}

$html->addBodyContent('<h2 class="option">Volby</h2>');
$html->addBodyContent('<h3 class="option">Obt&iacute;&zcaron;nost</h3>');
$html->addBodyContent('<label><input type="radio" name="difficulty" value="-1" />&nbsp;Pětiminutovka</label>');
$html->addBodyContent('<label><input type="radio" name="difficulty" value="0" checked="checked" />&nbsp;Lehká</label>');
$html->addBodyContent('<label><input type="radio" id="defaultdifficulty" name="difficulty" value="1" checked="checked" />&nbsp;Normální</label>');
$html->addBodyContent('<label><input type="radio" name="difficulty" value="2" />&nbsp;Vyšší</label>');
$html->addBodyContent('<label><input type="radio" name="difficulty" value="3" />&nbsp;Těžká</label>');

$html->addBodyContent('<br /><label><input type="number" name="countleft" value="'. POCATECNI_POCET. '" />&nbsp;p&rcaron;&iacute;klad&uring;</label>');
$html->addBodyContent('<br /><label><input type="checkbox" name="nofail" id="nofail" value="yes" checked="checked" />&nbsp;Opravovat p&rcaron;&iacute;klady</label>');
$html->addBodyContent('<br /><label><input type="checkbox" name="nocount" id="nocount" value="yes" />&nbsp;Nezobrazovat zbývající</label>');

$html->addBodyContent('<br /><input type="submit" class="init" name="init" value="Za&ccaron;&iacute;t" />');
$html->addBodyContent('</form>');

?>

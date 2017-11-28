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
    $text .= '<label for="'. $name. '_plus">+</label>';
    $text .= '<input type="checkbox" name="'. $name. '_minus" id="'. $name. '_minus" value="yes" checked="checked">';
    $text .= '<label for="'. $name. '_minus">-</label>';
    $text .= '<input type="checkbox" name="'. $name. '_deleno" id="'. $name. '_deleno" value="yes" checked="checked">';
    $text .= '<label for="'. $name. 'krat">&times;</label>';
    $text .= '<input type="checkbox" name="'. $name. '_krat" id="'. $name. '_krat" value="yes" checked="checked">';
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
  $onclick = " onclick=\"document.getElementById('nofail').checked=";
  if ($subj != 'Matematika') {
    $onclick .= "false;\"";
  } else {
    $onclick .= "true;\"";
  }
  #$subj_form[$subj][] = '<label class="lvl_label"'. $onclick. '><input type="radio" name="init_level" value="'. $id. '" />';
  #$subj_form[$subj][] = '<span class="lvl_name">'. $l->name. '</span><br />';
  #$subj_form[$subj][] = '<span class="lvl_desc">'. $l->getDescription(). '</span></label><br />';
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
    if (MAT_DEBUG) $html->addBodyContent('<pre>Adv: '. print_r($adv, true). '</pre>');
    foreach($adv as $part) {
      if($part == 'number') $subj_form[$formula::$subject][] = $inputs->getNumber($name);
      elseif ($part == 'opmask') $subj_form[$formula::$subject][] = $inputs->getOpMask($name);
      elseif ($part) $subj_form[$formula::$subject][] = $part;
    }
  }
  $subj_form[$formula::$subject][] = '</p>';
}

if (MAT_DEBUG) $html->addBodyContent('<pre>Subj_form: '. print_r($subj_form, true). '</pre>');

/*
$subj_form['Matematika'][] = '<p>';
$subj_form['Matematika'][] = $inputs->getNumber();
$subj_form['Matematika'][] = '<label for="advanced_scitani">Scitani</label>';
$subj_form['Matematika'][] = ' do&nbsp;<span class="advanced_param">';
$subj_form['Matematika'][] = '<input type="number" name="advanced_scitani_max">';
$subj_form['Matematika'][] = '</span></p>';

$subj_form['Matematika'][] = '<p><input type="checkbox" name="advanced_odcitani" id="advanced_odcitani">';
$subj_form['Matematika'][] = '<label for="advanced_odcitani">Odcitani</label>';
$subj_form['Matematika'][] = ' do&nbsp;<span class="advanced_param">';
$subj_form['Matematika'][] = '<input type="number" name="advanced_odcitani_max">';
$subj_form['Matematika'][] = '</span></p>';

$subj_form['Matematika'][] = '<p><input type="checkbox" name="advanced_nasobeni" id="advanced_nasobeni">';
$subj_form['Matematika'][] = '<label for="advanced_nasobeni">Nasobeni</label>';
$subj_form['Matematika'][] = ' do&nbsp;<span class="advanced_param">';
$subj_form['Matematika'][] = '<input type="number" name="advanced_nasobeni_max">';
$subj_form['Matematika'][] = '</span></p>';

$subj_form['Matematika'][] = '<p><input type="checkbox" name="advanced_deleni" id="advanced_deleni">';
$subj_form['Matematika'][] = '<label for="advanced_deleni">Deleni</label>';
$subj_form['Matematika'][] = ' do&nbsp;<span class="advanced_param">';
$subj_form['Matematika'][] = '<input type="number" name="advanced_deleni_max">';
$subj_form['Matematika'][] = '</span></p>';

$subj_form['&Ccaron;e&scaron;tina'][] = '<p><input type="checkbox" name="advanced_deleni" id="advanced_deleni">';
$subj_form['&Ccaron;e&scaron;tina'][] = '<label for="advanced_deleni">Deleni</label>';
$subj_form['&Ccaron;e&scaron;tina'][] = ' do&nbsp;<span class="advanced_param">';
$subj_form['&Ccaron;e&scaron;tina'][] = '<input type="number" name="advanced_deleni_max">';
$subj_form['&Ccaron;e&scaron;tina'][] = '</span></p>';

$subj_form['Angli&ccaron;tina'][] = '<p><input type="checkbox" name="advanced_deleni" id="advanced_deleni">';
$subj_form['Angli&ccaron;tina'][] = '<label for="advanced_deleni">Deleni</label>';
$subj_form['Angli&ccaron;tina'][] = ' do&nbsp;<span class="advanced_param">';
$subj_form['Angli&ccaron;tina'][] = '<input type="number" name="advanced_deleni_max">';
$subj_form['Angli&ccaron;tina'][] = '</span></p>';
*/

$html->addBodyContent('<form method="post">');
$subj_num = 0;
foreach($subjects as $subject) {
  $html->addBodyContent('<div class="label" id="label'. ++$subj_num. '">'. $subject. '</div>');
  $html->addBodyContent('<div class="elements" id="elements'. $subj_num. '">'. implode('', $subj_form[$subject]). '</div>');
}

/*
$html->addBodyContent('<table class="subjects"><thead><tr><th>'. implode('</th><th>', $subjects). '</th></tr></thead>');
$html->addBodyContent('<tbody><tr>');
foreach($subjects as $subject) {
  $html->addBodyContent('<td>'. implode('', $subj_form[$subject]). '</td>');
}
$html->addBodyContent('</tr></tbody></table>');
*/

$html->addBodyContent('<h2 class="option">Volby</h2>');
$html->addBodyContent('<h3 class="option">Obt&iacute;&zcaron;nost</h3>');
$html->addBodyContent('<label><input type="radio" name="difficulty" value="1" />&nbsp;Lehk&aacute;</label>');
$html->addBodyContent('<label><input type="radio" name="difficulty" value="2" checked="checked" />&nbsp;Norm&aacute;ln&iacute;</label>');
$html->addBodyContent('<label><input type="radio" name="difficulty" value="3" />&nbsp;T&ecaron;&zcaron;k&aacute;</label>');

$html->addBodyContent('<br /><label><input type="number" name="countleft" value="'. POCATECNI_POCET. '" />&nbsp;p&rcaron;&iacute;klad&uring;</label>');
$html->addBodyContent('<br /><label><input type="checkbox" name="nofail" id="nofail" value="yes" checked="checked" />&nbsp;Opravovat p&rcaron;&iacute;klady</label>');

$html->addBodyContent('<br /><input type="submit" class="init" name="init" value="Za&ccaron;&iacute;t" />');
$html->addBodyContent('</form>');

?>

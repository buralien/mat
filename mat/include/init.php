<?php

$html->addBodyContent('<h1>MAT - nastaven&iacute;</h1>');
$html->addBodyContent('<form method=post><input type="submit" name="advanced" value="Pokročilé nastavení" class="init"></form>');
$js = <<<JS
//<![CDATA[
function start() {
  var elems = document.getElementsByClassName('elements');
  var labels = document.getElementsByClassName('label');
  hideAll();
  for (var i = 0; i < labels.length; i++) {
    labels[i].onclick = toggleBlock;
  }
  document.getElementById('defaultdifficulty').checked=true;
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

$subjects = array('Matematika', '&Ccaron;e&scaron;tina', 'Angli&ccaron;tina', 'Předškolní');

$subj_form = array();

foreach ($levels as $id => $clsid) {
  $l = new $clsid();
  $subj = implode(' ', $l->subjects);
  $onclick = " onclick=\"if(document.getElementById('nofail').disabled==false)document.getElementById('nofail').checked=";
  if ($subj != 'Matematika') {
    $onclick .= "false;\"";
  } else {
    $onclick .= "true;\"";
  }
  $subj_form[$subj][] = '<label class="lvl_label"'. $onclick. '><input type="radio" name="init_level" value="'. $id. '" />';
  $subj_form[$subj][] = '<span class="lvl_name">'. $l->name. '</span><br />';
  $subj_form[$subj][] = '<span class="lvl_desc">'. $l->getDescription(). '</span></label><br />';
  unset($l);
}

$html->addBodyContent('<form method="post">');
$subj_num = 0;
foreach($subjects as $subject) {
  $html->addBodyContent('<div class="label" id="label'. ++$subj_num. '">'. $subject. '</div>');
  $html->addBodyContent('<div class="elements" id="elements'. $subj_num. '">'. implode('', $subj_form[$subject]). '</div>');
}

$html->addBodyContent('<h2 class="option">Volby</h2>');
$html->addBodyContent('<h3 class="option">Obt&iacute;&zcaron;nost</h3>');
$onclick = "onclick=\"document.getElementById('nofail').checked=false;document.getElementById('nofail').disabled=true;\"";
$html->addBodyContent('<label '. $onclick. '><input type="radio" name="difficulty" value="-1" />&nbsp;Pětiminutovka</label>');
$onclick = "onclick=\"document.getElementById('nofail').disabled=false;\"";
$html->addBodyContent('<label '. $onclick. '><input type="radio" name="difficulty" value="0" checked="checked" />&nbsp;Lehká</label>');
$html->addBodyContent('<label '. $onclick. '><input type="radio" id="defaultdifficulty" name="difficulty" value="1" checked="checked" />&nbsp;Normální</label>');
$html->addBodyContent('<label '. $onclick. '><input type="radio" name="difficulty" value="2" />&nbsp;Vyšší</label>');
$html->addBodyContent('<label '. $onclick. '><input type="radio" name="difficulty" value="3" />&nbsp;Těžká</label>');

$html->addBodyContent('<br /><label><input type="number" name="countleft" value="'. POCATECNI_POCET. '" />&nbsp;p&rcaron;&iacute;klad&uring;</label>');
$html->addBodyContent('<br /><label><input type="checkbox" name="nofail" id="nofail" value="yes" checked="checked" />&nbsp;Opravovat p&rcaron;&iacute;klady</label>');
$html->addBodyContent('<br /><label><input type="checkbox" name="nocount" id="nocount" value="yes" />&nbsp;Nezobrazovat zbývající</label>');

$html->addBodyContent('<br /><input type="submit" class="init" name="init" value="Za&ccaron;&iacute;t" />');
$html->addBodyContent('</form>');

?>

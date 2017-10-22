<?php

$html->setTitle('MAT');

$html->addBodyContent('<h1>MAT - nastaven&iacute;</h1>');

$html->addBodyContent('<form method="post">');

foreach ($levels as $id => $clsid) {
  $l = new $clsid();
  $html->addBodyContent('<label><input type="radio" name="init_level" value="'. $id. '" />');
  $html->addBodyContent('<span class="lvl_name">'. $l->name. '</span><br />');
  $html->addBodyContent('<span class="lvl_desc">'. $l->getDescription(). '</span></label><br />');
}
unset($l);
$html->addBodyContent('Obt&iacute;&zcaron;nost:<br />');
$html->addBodyContent('<label><input type="radio" name="difficulty" value="1" />&nbsp;Lehk&aacute;</label>');
$html->addBodyContent('<label><input type="radio" name="difficulty" value="2" />&nbsp;Norm&aacute;ln&iacute;</label>');
$html->addBodyContent('<label><input type="radio" name="difficulty" value="3" />&nbsp;T&eacute;&zcaron;k&aacute;</label>');

$html->addBodyContent('<br /><label><input type="number" name="countleft" value="'. POCATECNI_POCET. '" />&nbsp;p&rcaron;&iacute;klad&uring;</label>');
$html->addBodyContent('<br /><label><input type="checkbox" name="nofail" value="yes" />&nbsp;Opravovat p&rcaron;&iacute;klady</label>');

$html->addBodyContent('<br /><input type="submit" class="init" name="init" value="Za&ccaron;&iacute;t" />');
$html->addBodyContent('</form>');

?>

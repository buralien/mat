<?php

$html->setTitle('MAT');

$html->addBodyContent('<h1>MAT - nastaven&iacute;</h1>');

$html->addBodyContent('<form method="post">');

foreach ($levels as $id => $clsid) {
  $l = new $clsid();
  $html->addBodyContent('<input type="radio" name="init_level" value="'. $id. '" />');
  $html->addBodyContent('<span class="lvl_name">'. $l->name. '</span><br />');
  $html->addBodyContent('<span class="lvl_desc">'. $l->getDescription(). '</span><br />');
}
unset($l);
$html->addBodyContent('<input type="number" name="countleft" value="'. POCATECNI_POCET. '" />&nbsp;p&rcaron;&iacute;klad&uring;');
$html->addBodyContent('<br /><input type="checkbox" name="nofail" value="yes" />&nbsp;Opravovat p&rcaron;&iacute;klady');
$html->addBodyContent('<br /><input type="submit" class="init" name="init" value="Za&ccaron;&iacute;t" />');
$html->addBodyContent('</form>');

?>

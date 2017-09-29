<?php

require_once 'include/formula.class.php';
require_once 'include/level.class.php';
require_once 'HTML/Page2.php';

define('POCATECNI_POCET', 10);
define('POCATECNI_OBTIZNOST', 12);
define('PRIDAT_ZA_CHYBU', 2);

function sayTime($timestamp) {
  $t = time() - $timestamp;
  $ret = array();
  if ($t % 60 > 0) {
    $ret[] = ($t % 60). ' sekund';
  }
  $t = floor($t / 60);
  if ($t % 60 > 0) {
    $ret[] = ($t % 60). ' minut';
  }
  $t = floor($t / 60);
  if ($t % 24 > 0) {
    $ret[] = ($t % 24). ' hodin';
  }
  $t = floor($t / 24);
  if ($t > 0) {
    $ret[] = $t. ' dni';
  }
  return implode(', ', array_reverse($ret));
}

function encryptObject($f) {
  return base64_encode(serialize($f));
}

function decryptObject($text) {
  return unserialize(base64_decode($text));
}

$html = new HTML_Page2();
$html->addStyleSheet('mat.css');
$level = new FormulaLevelNasobilka();

$elements = array();
$operators = array();
$results = array();
$count = null;
$starttime = null;
$solved = 0;
$correct = 0;
$nofail = "no";
$check = null;
foreach ($_POST as $key => $val) {
  if (is_numeric($val)) {
    if (strpos($key, 'result') === 0) $results[$key] = intval($val);
    if ($key == 'countleft') $count = $val;
    if ($key == 'starttime') $starttime = $val;
    if ($key == 'solved') $solved = $val + 1;
    if ($key == 'correct') $correct = $val;
  } else {
    if ($key == 'nofail') $nofail = htmlspecialchars($val);
    if ($key == 'formula' ) $check = decryptObject($val);
    if ($key == 'level' ) $level = decryptObject($val);
  }
}
if( $count == null ) { $count = POCATECNI_POCET; }
if ($starttime == null) { $starttime = time(); }
$level->solved += 1;

$spatne = FALSE;
$priklad = null;
$result_msg = '';
if ($check !== null) {
  $res = $check->getResult();
  if (!is_array($res)) {
    $res = array($res);
  }
  if (($check instanceof DeleniSeZbytkem) && (!isset($results['result2']))) {
    $results['result2'] = 0;
  }
  if (array_values($res) == array_values($results)) {
    // Spravne
    $count--;
    $level->correct += 1;
    $level->addWeight(get_class($check), -100);
    $result_msg = '<h2 class="success">Spr&aacute;vn&ecaron;!</h2>';
  } else {
    // Spatne

    if ($count < POCATECNI_POCET) { $count += min(PRIDAT_ZA_CHYBU, (POCATECNI_POCET - $count)); }
    $spatne=TRUE;
    $level->addWeight(get_class($check));
    $result_msg = '<h2 class="fail">&Scaron;patn&ecaron;!</h2>';
    if ($nofail == "yes") {
      $priklad = $check;
    } else {
      $result_msg .= '<p class="correctresult">'. $check->toHTML(TRUE). '</p>';
    }
  }
} else {
  $check = new RandomSimpleFormula();
}

if ($priklad === null) {
  $priklad = $level->getFormula();
}

$html->setTitle('MAT');
// $html->addBodyContent('<pre>'. print_r($priklad, TRUE). '</pre>');

if ($count == 0) {
  $html->addBodyContent('<h2 class="success">Hotovo!</h2>');
  $html->addBodyContent('<a href="?">Znova</a>');
} else {
  $html->addBodyContent($result_msg);
  $html->addBodyContent("<h2>Zb&yacute;v&aacute; $count p&rcaron;&iacute;klad&uring;</h2>");
  if (($nofail == "no") || (!$spatne)) {
    $html->addBodyContent('<h1>'. $priklad->name. '</h1>');
  }
  $html->addBodyContent('<form method="post">');
  $html->addBodyContent($priklad->toHTML());
  $html->addBodyContent($priklad->getResultHTMLForm());
  $html->addBodyContent('<input type="hidden" name="countleft" value="'. $count. '" />');
  $html->addBodyContent('<input type="hidden" name="starttime" value="'. $starttime. '" />');
  $html->addBodyContent('<input type="hidden" name="formula" value="'. encryptObject($priklad). '" />');
  $html->addBodyContent('<input type="hidden" name="level" value="'. encryptObject($level). '" />');
  $html->addBodyContent('<input type="submit" value="Hotovo">');
  if($level->solved == 0) {
    $html->addBodyContent('<br /><input type="checkbox" name="nofail" value="yes" />&nbsp;Opravy');
  } else {
    $html->addBodyContent('<input type="hidden" name="nofail" value="'. $nofail. '" />');
  }
  $html->addBodyContent('</form>');
}

// $html->addBodyContent('<pre>'. print_r($level, TRUE). '</pre>');
if (time() - $starttime > 2) {
  $html->addBodyContent('<p>Spr&aacute;vn&ecaron; <span class="correct">'. $level->correct. '</span> z <span class="solved">'. $level->solved. '</span> p&rcaron;&iacute;klad&uring;');
  if ($nofail == 'yes') {
    $html->addBodyContent(' s&nbsp;opravami');
  }
  $html->addBodyContent(' za <span class="time">'. sayTime($starttime). '</span>.</p>');
}
$html->addBodyContent('<p class="footer">MAT info a licence: <a href="https://github.com/buralien/mat">GitHub</a></p>');

$html->display();
?>

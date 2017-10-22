<?php

require_once 'include/level.class.php';
require_once 'HTML/Page2.php';

define('POCATECNI_POCET', 10);
define('PRIDAT_ZA_CHYBU', 2);

$levels = array(
  'FormulaLevel1',
  'FormulaLevel2',
  'FormulaLevel3a',
  'FormulaLevel3b',
  'FormulaLevel4a',
  'FormulaLevelNasobilka',
  'FormulaLevelAnglictina'
  );

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
    $ret[] = $t. ' dn&iacute;';
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
$favicon = <<<FAVICON
data:image/x-icon;base64,AAABAAEAEBAAAAAAAABoBAAAFgAAACgAAAAQAAAAIAAAAAEAIAAAAAAAAAQAAAAAAAAAAAAAAAAAAAAAAAAAAAAJAAAAFgAAABoAAAAaAAAAGgAAABoAAAAaAAAAGgAAABoAAAAaAAAAGgAAABoAAAAaAAAAGgAAABYAAAAJAAAAEgEOADMCSgCDAl0AvAJdAMwCXQDMAl0AzAJdAMwCXQDMAl0AzAJdAMwCXQDMAl0AvAJKAIMBDgAzAAAAEgIdAAAGbQBzEpII3SDMEPki2RH/ItkR/yLZEf8i2RH/ItkR/yLZEf8i2RH/ItkR/x/MD/kQkgfdBm0AcwIdAAAKfQAACn0AuiXKFfki0RH/ItER/yLREf8i0RH/IrYR/yK2Ef8i0RH/ItER/yLREf8i0RH/IMgP+Qp9ALoKfQAADIQAAAyEAMwrzBr/IsgR/yLIEf8iyBH/IrwR/+jo6P/s7Oz/IrwR/yLIEf8iyBH/IsgR/yLIEf8MhADMDIQAAA2JAAANiQDMMcYg/yK+Ef8ivhH/Ir4R/yK1Ef/k5OT/6Ojo/yK1Ef8ivhH/Ir4R/yK+Ef8jvhL/DYkAzA2JAAAOjQAADo0AzEHDMP8jtBL/IqgR/yKoEf8ipBH/4ODg/+Tk5P8ipBH/IqgR/yKoEf8itBH/JbUU/w6NAMwOjQAAD5IAAA+SAMxSyUH/M68i/9TU1P/T09P/19fX/9zc3P/g4OD/5OTk/+jo6P/s7Oz/IqYR/yivF/8PkgDMD5IAABCWAAAQlgDMVcxE/zyzK//4+Pj/4eHh/9XV1f/X19f/3Nzc/+Dg4P/k5OT/6Ojo/yKgEf8sqhv/EJYAzBCWAAARmgAAEZoAzFrRSf9Hvjb/PrUt/z61Lf83rib/6+vr/+Li4v8lnRT/I5sS/yObEv8nnxb/ObEo/xGaAMwRmgAAEp4AABKeAMxg10//TsU9/07FPf9OxT3/RLsz////////////RLsz/07FPf9OxT3/TsU9/1jPR/8SngDMEp4AABOiAAATogDMZ95W/1fORv9Xzkb/V85G/0rBOf///////////0rBOf9Xzkb/V85G/1fORv9g10//E6IAzBOiAAAUpQAAFKUAumTeU/lf1k7/X9ZO/1/WTv9f1k7/UMc//1DHP/9f1k7/X9ZO/1/WTv9f1k7/YNpP+RSlALoUpQAAFKgAABSoAHM3wSTdZuBU+W7lXf9u5V3/buVd/27lXf9t5Fz/beRc/23kXP9t5Fz/ZN9T+Ta/I90UqABzFKgAABSoAAAVqQAMFaoAcxWqALoVqgDMFaoAzBWqAMwVqgDMFaoAzBWqAMwVqgDMFaoAzBWqALoVqgBzFakADBSoAAD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A//8AAMADAADAAwAAgAEAAIABAACAAQAAgAEAAIABAACAAQAAgAEAAIABAACAAQAAgAEAAMADAADgBwAA//8AAA==
FAVICON;
$html->addHeadLink($favicon, 'icon');
$level = new FormulaLevelNasobilka();

$elements = array();
$operators = array();
$results = array();
$count = null;
$starttime = null;
$nofail = "no";
$check = null;
// $html->addBodyContent(print_r($_POST, TRUE));
foreach ($_POST as $key => $val) {
  if (is_numeric($val)) {
    if (strpos($key, 'result') === 0) $results[$key] = intval($val);
    if ($key == 'countleft') $count = $val;
    if ($key == 'starttime') $starttime = $val;
    if ($key == 'init_level') {
      $clsid = $levels[intval($val)];
      $level = new $clsid();
    }
  } else {
    if (strpos($key, 'result') === 0) $results[$key] = htmlspecialchars($val);
    if ($key == 'nofail') $nofail = htmlspecialchars($val);
    if ($key == 'formula' ) $check = decryptObject($val);
    if ($key == 'level' ) {
      $level = decryptObject($val);
      $level->solved += 1;
    }
  }
}

if ( $count == null ) {
  include 'include/init.php';
  //$count = POCATECNI_POCET;
  include 'include/footer.php';
  $html->display();
  die();
} elseif ($level->solved == 0) {
  $level->max_formulas = $count;
}
if ( $starttime == null ) { $starttime = time(); }

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
  if ($check->validateResult($results)) {
    // Spravne
    $count--;
    $level->correct += 1;
    $level->addWeight(get_class($check), -100);
    $result_msg = '<h2 class="success">Spr&aacute;vn&ecaron;!</h2>';
  } else {
    // Spatne

    if ($count < $level->max_formulas) { $count += min(PRIDAT_ZA_CHYBU, ($level->max_formulas - $count)); }
    $spatne=TRUE;
    $level->addWeight(get_class($check));
    $result_msg = '<h2 class="fail">&Scaron;patn&ecaron;!</h2>';
    if ($nofail == "yes") {
      $priklad = $check;
    } else {
      $result_msg .= '<p class="correctresult">'. $check->toHTML(TRUE). '</p>';
    }
  }
}

if ($priklad === null) {
  $priklad = $level->getFormula();
  if ($priklad->voiceEnabled()) {
    $html->addScript('https://code.responsivevoice.org/responsivevoice.js');
  }
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
    $html->addBodyContent('<h1>'. $priklad->getName(). '</h1>');
  }
  $html->addBodyContent('<form method="post">');
  $html->addBodyContent($priklad->toHTML());
  $html->addBodyContent($priklad->getResultHTMLForm());
  $html->addBodyContent('<input type="hidden" name="countleft" value="'. $count. '" />');
  $html->addBodyContent('<input type="hidden" name="starttime" value="'. $starttime. '" />');
  $html->addBodyContent('<input type="hidden" name="formula" value="'. encryptObject($priklad). '" />');
  $html->addBodyContent('<input type="hidden" name="level" value="'. encryptObject($level). '" />');
  $html->addBodyContent('<input type="submit" value="Hotovo">');
  $html->addBodyContent('<input type="hidden" name="nofail" value="'. $nofail. '" />');
  $html->addBodyContent('</form>');
}

// $html->addBodyContent('<pre>'. print_r($level, TRUE). '</pre>');
if ((time() - $starttime > 2) || ($level->solved > 0)) {
  $html->addBodyContent('<p>Spr&aacute;vn&ecaron; <span class="correct">'. $level->correct. '</span> z <span class="solved">'. $level->solved. '</span> p&rcaron;&iacute;klad&uring;');
  if ($nofail == 'yes') {
    $html->addBodyContent(' s&nbsp;opravami');
  }
  $html->addBodyContent(' ('. $level->name. ')');
  $html->addBodyContent(' za <span class="time">'. sayTime($starttime). '</span>.</p>');
}
include 'include/footer.php';

$html->display();
?>

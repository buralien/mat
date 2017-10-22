<?php
session_start() or die("Failed to start sessions!");
if (isset($_GET['startover'])) {
  session_destroy();
  $_SESSION = array();
  header("HTTP/1.1 303 See Other");
  header('Location: ?');
  die();
}
header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
header("Pragma: no-cache"); // HTTP/1.0
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

require_once 'include/level.class.php';
require_once 'HTML/Page2.php';

define('POCATECNI_POCET', 10);
define('PRIDAT_ZA_CHYBU', 2);
define('MAT_DEBUG', 0);

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
if (MAT_DEBUG) $html->addBodyContent(print_r($_SESSION, true));
if (MAT_DEBUG) $html->addBodyContent(print_r($_POST, true));

if (isset($_SESSION['level'])) {
  $level = decryptObject($_SESSION['level']);
  $level->solved += 1;
} else {
  $level = new FormulaLevelNasobilka();
}
if (isset($_SESSION['priklad'])) {
  $check = decryptObject($_SESSION['priklad']);
} else {
  $check = null;
}
if (isset($_SESSION['starttime'])) {
  $starttime = intval($_SESSION['starttime']);
} else {
  $starttime = $starttime = time();
}
if (isset($_SESSION['countleft'])) {
  $count = intval($_SESSION['countleft']);
} else {
  $count = null;
}
if (isset($_SESSION['nofail'])) {
  $nofail = $_SESSION['nofail'];
} else {
  $nofail = "no";
}

$results = array();
foreach ($_POST as $key => $val) {
  if (strpos($key, 'result') === 0) $results[$key] = htmlspecialchars($val);
  if (($key == 'nofail') && ($value == 'yes')) $nofail = 'yes';
  if (($key == 'countleft') && (is_numeric($val))) $count = intval($val);
  if (($key == 'init_level') && (is_numeric($val))) {
    $clsid = $levels[intval($val)];
    $level = new $clsid();
    if (MAT_DEBUG) $html->addBodyContent("Created level $clsid");
    if (MAT_DEBUG) $html->addBodyContent(print_r($level, true));
  }
}

if (( $count == null ) || (isset($_GET['startover']))) {
  session_destroy();
  $_SESSION = array();
  include 'include/init.php';
  include 'include/footer.php';
  $html->display();
  die();
} elseif ($level->solved == 0) {
  $level->max_formulas = $count;
}

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
$_SESSION['nofail'] = $nofail;
$_SESSION['countleft'] = $count;
$_SESSION['starttime'] = $starttime;
$_SESSION['level'] = encryptObject($level);
$_SESSION['priklad'] = encryptObject($priklad);
session_write_close();

$html->setTitle('MAT');
// $html->addBodyContent('<pre>'. print_r($priklad, TRUE). '</pre>');

if ($count == 0) {
  $html->addBodyContent('<h2 class="success">Hotovo!</h2>');
  $html->addBodyContent('<a href="?startover=1">Znova</a>');
} else {
  $html->addBodyContent($result_msg);
  $html->addBodyContent("<h2>Zb&yacute;v&aacute; $count p&rcaron;&iacute;klad&uring;</h2>");
  if (($nofail == "no") || (!$spatne)) {
    $html->addBodyContent('<h1>'. $priklad->getName(). '</h1>');
  }
  $html->addBodyContent('<form method="post">');
  $html->addBodyContent($priklad->toHTML());
  $html->addBodyContent($priklad->getResultHTMLForm());
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

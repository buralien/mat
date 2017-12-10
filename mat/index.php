<?php
session_start() or die("Failed to start sessions!");
if (isset($_GET['startover'])) {
  # Destroy saved session data and start over
  session_destroy();
  $_SESSION = array();
  #header($_SERVER["SERVER_PROTOCOL"]." 303 See Other");
  header('Location: ?', true, 303);
  die();
}
# Prevent caching of pages
header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
header("Pragma: no-cache"); // HTTP/1.0
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

require_once 'include/level.class.php';
require_once 'include/stats.class.php';
require_once 'HTML/Page2.php';

define('MAT_DEBUG', 0); # Set to one to see debug output on the page
define('POCATECNI_POCET', 10); # Default number of formulas to solve
define('BREAK_AFTER', 30);
define('BREAK_LENGTH', 5);

# List of allowed formula levels (see level.class.php)
$levels = array(
  'FormulaLevel1',
  'FormulaLevel2',
  'FormulaLevel3a',
  'FormulaLevel3b',
  'FormulaLevel4a',
  'FormulaLevelNasobilka',
  'FormulaLevelAnglictinaNoSound',
  'FormulaLevelAnglictina',
  'FormulaLevelVyjmenovanaSlova',
  'FormulaLevelVyjmenovanaSlovaDiktat',
  'FormulaLevelRomanNumerals',
  'FormulaLevelCestina3'
  );

function sayTime($timestamp) {
  # Return the time difference as natural text
  $t = abs(time() - $timestamp);
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
$html->setTitle('MAT');
$html->addStyleSheet('mat.css');

# Inline favicon.ico
$favicon = <<<FAVICON
data:image/x-icon;base64,AAABAAEAEBAAAAAAAABoBAAAFgAAACgAAAAQAAAAIAAAAAEAIAAAAAAAAAQAAAAAAAAAAAAAAAAAAAAAAAAAAAAJAAAAFgAAABoAAAAaAAAAGgAAABoAAAAaAAAAGgAAABoAAAAaAAAAGgAAABoAAAAaAAAAGgAAABYAAAAJAAAAEgEOADMCSgCDAl0AvAJdAMwCXQDMAl0AzAJdAMwCXQDMAl0AzAJdAMwCXQDMAl0AvAJKAIMBDgAzAAAAEgIdAAAGbQBzEpII3SDMEPki2RH/ItkR/yLZEf8i2RH/ItkR/yLZEf8i2RH/ItkR/x/MD/kQkgfdBm0AcwIdAAAKfQAACn0AuiXKFfki0RH/ItER/yLREf8i0RH/IrYR/yK2Ef8i0RH/ItER/yLREf8i0RH/IMgP+Qp9ALoKfQAADIQAAAyEAMwrzBr/IsgR/yLIEf8iyBH/IrwR/+jo6P/s7Oz/IrwR/yLIEf8iyBH/IsgR/yLIEf8MhADMDIQAAA2JAAANiQDMMcYg/yK+Ef8ivhH/Ir4R/yK1Ef/k5OT/6Ojo/yK1Ef8ivhH/Ir4R/yK+Ef8jvhL/DYkAzA2JAAAOjQAADo0AzEHDMP8jtBL/IqgR/yKoEf8ipBH/4ODg/+Tk5P8ipBH/IqgR/yKoEf8itBH/JbUU/w6NAMwOjQAAD5IAAA+SAMxSyUH/M68i/9TU1P/T09P/19fX/9zc3P/g4OD/5OTk/+jo6P/s7Oz/IqYR/yivF/8PkgDMD5IAABCWAAAQlgDMVcxE/zyzK//4+Pj/4eHh/9XV1f/X19f/3Nzc/+Dg4P/k5OT/6Ojo/yKgEf8sqhv/EJYAzBCWAAARmgAAEZoAzFrRSf9Hvjb/PrUt/z61Lf83rib/6+vr/+Li4v8lnRT/I5sS/yObEv8nnxb/ObEo/xGaAMwRmgAAEp4AABKeAMxg10//TsU9/07FPf9OxT3/RLsz////////////RLsz/07FPf9OxT3/TsU9/1jPR/8SngDMEp4AABOiAAATogDMZ95W/1fORv9Xzkb/V85G/0rBOf///////////0rBOf9Xzkb/V85G/1fORv9g10//E6IAzBOiAAAUpQAAFKUAumTeU/lf1k7/X9ZO/1/WTv9f1k7/UMc//1DHP/9f1k7/X9ZO/1/WTv9f1k7/YNpP+RSlALoUpQAAFKgAABSoAHM3wSTdZuBU+W7lXf9u5V3/buVd/27lXf9t5Fz/beRc/23kXP9t5Fz/ZN9T+Ta/I90UqABzFKgAABSoAAAVqQAMFaoAcxWqALoVqgDMFaoAzBWqAMwVqgDMFaoAzBWqAMwVqgDMFaoAzBWqALoVqgBzFakADBSoAAD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A//8AAMADAADAAwAAgAEAAIABAACAAQAAgAEAAIABAACAAQAAgAEAAIABAACAAQAAgAEAAMADAADgBwAA//8AAA==
FAVICON;
$html->addHeadLink($favicon, 'icon');

if (MAT_DEBUG) $html->addBodyContent('Initial Session: <pre>'. print_r($_SESSION, true). '</pre>');
if (MAT_DEBUG) $html->addBodyContent('POST: <pre>'. print_r($_POST, true). '</pre>');

# Looking for all the different variables passed through SESSION
if (isset($_SESSION['priklad'])) {
  $check = decryptObject($_SESSION['priklad']);
} else {
  $check = null;
}
if (!isset($_SESSION['starttime'])) {
  $_SESSION['starttime'] = time();
}
if (!isset($_SESSION['difficulty'])) {
  $_SESSION['difficulty'] = 1;
}
if (!isset($_SESSION['countleft'])) {
  $_SESSION['countleft'] = null;
}
if (!isset($_SESSION['nofail'])) {
  $_SESSION['nofail'] = "no";
}
if (!isset($_SESSION['nocount'])) {
  $_SESSION['nocount'] = "no";
}
if (!isset($_SESSION['breakend'])) {
  $_SESSION['breakend'] = 0;
}
$githash = shell_exec('cd '. realpath(dirname(__FILE__). '/../'). ' && git log --pretty="%H" -n1 HEAD');
if (!isset($_SESSION['gitcommit'])) {
  $_SESSION['gitcommit'] = $githash;
} elseif ($githash != $_SESSION['gitcommit']) {
  session_destroy();
  $_SESSION = array();
  header('Location: ?', true, 303);
  die();
}

$time = time();
$break_just_started = false;
if (!isset($_SESSION['breakstart'])) { // initial setup
  $_SESSION['breakstart'] = $time + (BREAK_AFTER * 60);
} elseif (is_numeric($_SESSION['breakstart'])) { // we know when to start a break
  if ($time > $_SESSION['breakstart']) { // time for a break
    if ($_SESSION['breakend'] === 0) { // first time on this break
      $_SESSION['breakend'] = $time + (BREAK_LENGTH * 60); // define end of break
      $break_just_started = true;
    }
    if($time > $_SESSION['breakend']) { // break is over
      $_SESSION['breakstart'] = $time + (BREAK_AFTER * 60); // define time for next break
      $_SESSION['breakend'] = 0;
    }
  }
}

if (isset($_SESSION['level'])) {
  $level = decryptObject($_SESSION['level']);
  if ($_SESSION['breakend'] < $time || $break_just_started) $level->solved += 1;
} else {
  $level = new FormulaLevelNasobilka();
}

# Looking for POST values of results and initial setup
$results = array();
$advanced_data = array();
foreach ($_POST as $key => $val) {
  if (strpos($key, 'result') === 0) $results[$key] = htmlspecialchars($val);
  if (strpos($key, 'advanced_') === 0) {
    $k = explode('_', $key);
    $key_name = $k[1];
    if (count($k) > 2) {
      $key_param = $k[2];
      if (is_numeric($key_param)) {
        if (isset($k[3])) {
          if (!isset($advanced_data[$key_name]['opmask'])) $advanced_data[$key_name]['opmask'] = OP_PLUS + OP_MINUS + OP_KRAT + OP_DELENO;
          switch($k[3]) {
            case 'plus': $advanced_data[$key_name]['opmask'] -= OP_PLUS; break;
            case 'minus': $advanced_data[$key_name]['opmask'] -= OP_MINUS; break;
            case 'krat': $advanced_data[$key_name]['opmask'] -= OP_KRAT; break;
            case 'deleno': $advanced_data[$key_name]['opmask'] -= OP_DELENO; break;
          }
        } else {
          $advanced_data[$key_name]['param'][] = $val;
        }
      } else {
        $advanced_data[$key_name][$key_param] = $val;
      }
    } elseif (count($k) == 2) {
      $advanced_data[$key_name]['value'] = $val;
    }
  }
  if (($key == 'nofail') && ($val == 'yes')) $_SESSION['nofail'] = 'yes';
  if (($key == 'nocount') && ($val == 'yes')) $_SESSION['nocount'] = 'yes';
  if (($key == 'countleft') && (is_numeric($val))) $_SESSION['countleft'] = intval($val);
  if (($key == 'difficulty') && (is_numeric($val))) $_SESSION['difficulty'] = intval($val);
  if (($key == 'init_level') && (is_numeric($val))) {
    $clsid = $levels[intval($val)];
    $level = new $clsid();
  }
}
if($_SESSION['difficulty'] == -1) {
  $_SESSION['nofail'] = 'no';
  $_SESSION['nocount'] = 'yes';
}

if (count($advanced_data) > 0) {
  $custom_level = new CustomLevel();
  foreach($advanced_data as $a) {
    if (isset($a['value'])) {
      if ($a['value'] == 'yes') {
        if (isset($a['opmask'])) {
          if ($a['opmask'] < (OP_PLUS + OP_MINUS + OP_KRAT + OP_DELENO)) {
            $a['param'][999] = $a['opmask'];
          } else {
            $a['param'][999] = 0;
          }
        }
        if ((isset($a['param'])) && (count($a['param']) > 0)) {
          foreach(array_keys($a['param']) as $pk) {
            # Clean empty params passed from advanced settings
            if (!$a['param'][$pk]) $a['param'][$pk] = null;
          }
          $custom_level->addFormula($a['clsid'], array_values($a['param']));
        } else {
          $custom_level->addFormula($a['clsid']);
        }
      }
    }
  }
  if (MAT_DEBUG) $html->addBodyContent('Advanced: <pre>'. print_r($custom_level, true). '</pre>');
  $level = $custom_level;
}

if (MAT_DEBUG) $html->addBodyContent('Level: <pre>'. print_r($level, true). '</pre>');
if (MAT_DEBUG) $html->addBodyContent('Check: <pre>'. print_r($check, true). '</pre>');


if ( $_SESSION['countleft'] === null ) {
  # No setup was done yet, reset SESSION and display the initial page
  session_destroy();
  $_SESSION = array();
  if (isset($_POST['advanced'])) {
    include 'include/advanced-init.php';
  } else {
    include 'include/init.php';
  }
  $_SESSION['gitcommit'] = $githash;
  include 'include/footer.php';
  $html->display();
  die();
} elseif ($level->solved == 0) {
  # Set initial count of formulas left on the current level
  # in case we are on the first one
  $level->max_formulas = $_SESSION['countleft'];
}

$js = <<<JS
function fade(element) {
var op = 2;
var timer = setInterval(function () {
    if (op <= 0.2){
        clearInterval(timer);
        op = 0
    }
    element.style.opacity = op;
    element.style.filter = 'alpha(opacity=' + op * 50 + ")";
    op -= op * 0.05;
}, 100);
}
JS;
$html->addScriptDeclaration($js);

$spatne = FALSE;
$priklad = null;
$result_msg = '';
//if (($check !== null) && (!$break_just_started) && ($_SESSION['breakend'] > 0)) {
if ($check !== null && ($_SESSION['breakend'] < $time || $break_just_started)) {
  # Get the correct solution
  $res = $check->getResult();

  # Log the stats
  $stats = new StatsManager();
  $stats->addRecord(session_id(), $check, $results);
  $stats->close();

  if (!is_array($res)) {
    $res = array($res);
  }
  if (($check instanceof DeleniSeZbytkem) && (!isset($results['result2']))) {
    # Empty input is considered a zero
    $results['result2'] = 0;
  }

  if ($check->validateResult($results)) {
    # Correct input
    $_SESSION['countleft']--;
    $level->correct += 1;
    $level->addWeight(get_class($check), -100);
    $result_msg = '<h2 class="success" id="temporary">Spr&aacute;vn&ecaron;!</h2>';
  } else {
    # Incorrect input
    if ($_SESSION['countleft'] < $level->max_formulas) { $_SESSION['countleft'] += min($_SESSION['difficulty'], ($level->max_formulas - $_SESSION['countleft'])); }
    $spatne=TRUE;
    $level->addWeight(get_class($check), 100);
    $result_msg = '<h2 class="fail" id="temporary">&Scaron;patn&ecaron;!</h2>';
    if ($_SESSION['nofail'] == "yes") {
      # Repeat the same formula, no solution is shown
      $priklad = $check;
    } else {
      # Show the correct solution
      $result_msg .= '<p class="correctresult">'. $check->toHTML(TRUE). '</p>';
      $result_msg .= '<p>Tvoje odpověď: <span class="mistake">';
      $result_msg .= implode (', ', $results);
      $result_msg .= '</p>';
    }
  }
  $html->setBodyAttributes(array('onload' => 'fade(document.getElementById("temporary"));'));
}

if ($priklad === null) {
  # Need to generate a new formula
  $priklad = $level->getFormula();
}
if ($priklad->voiceEnabled()) {
  $html->addScript('https://code.responsivevoice.org/responsivevoice.js');
}
$_SESSION['level'] = encryptObject($level);
$_SESSION['priklad'] = encryptObject($priklad);
if (MAT_DEBUG) $html->addBodyContent('Final Session: <pre>'. print_r($_SESSION, true). '</pre>');
session_write_close();

if (MAT_DEBUG) $html->addBodyContent('Priklad: <pre>'. print_r($priklad, TRUE). '</pre>');

if ($_SESSION['countleft'] == 0) {
  # Successfully solved all formulas
  $html->addBodyContent('<h2 class="success">Hotovo!</h2>');
  if (count($level) > 1) {
    $html->addBodyContent('<p>Nejlepší: '. $level->bestFormula(). '</p>');
    $html->addBodyContent('<p>Nejhorší: '. $level->worstFormula(). '</p>');
  }
  $html->addBodyContent('<a href="?startover=1">Spustit znovu</a>');
} else {
  $html->addBodyContent($result_msg);
  if ($_SESSION['breakend'] === 0) {
    if ($_SESSION['nocount'] == 'no') {
      $html->addBodyContent("<h2>Zb&yacute;v&aacute; ". $_SESSION['countleft']. " p&rcaron;&iacute;klad&uring;</h2>");
    }
    if (($_SESSION['nofail'] == "no") || (!$spatne)) {
      $html->addBodyContent('<h1>'. $priklad->getName(). '</h1>');
    }
    $html->addBodyContent('<form method="post">');
    $html->addBodyContent($priklad->toHTML());
    $html->addBodyContent($priklad->getResultHTMLForm());
    $html->addBodyContent('<input type="submit" value="Hotovo">');
    $html->addBodyContent('</form>');
  } else {
    $html->addBodyContent('<h1><a href="?">Dej si pauzu</a></h1>');
    $html->addBodyContent('<p>Vrať se za <span class="time">'. sayTime($_SESSION['breakend']). '</span></p>');

  }
}

if ((time() - $_SESSION['starttime'] > 2) || ($level->solved > 0)) {
  # Progress message with results, time and level name
  $html->addBodyContent('<p>Spr&aacute;vn&ecaron; <span class="correct">'. $level->correct. '</span> z <span class="solved">'. $level->solved. '</span> p&rcaron;&iacute;klad&uring;');
  switch ($_SESSION['difficulty']) {
    case 3: $html->addBodyContent(' na&nbsp;těžkou obtížnost '); break;
    case 2: $html->addBodyContent(' na&nbsp;vyšší obtížnost '); break;
    case 0: $html->addBodyContent(' na&nbsp;lehkou obtížnost '); break;
    case -1: $html->addBodyContent(' -&nbsp;Pětiminutovka '); break;
  }
  if ($_SESSION['nofail'] == 'yes') {
    $html->addBodyContent(' s&nbsp;opravami');
  }
  $html->addBodyContent(' ('. $level->name. ')');
  $html->addBodyContent(' za <span class="time">'. sayTime($_SESSION['starttime']). '</span>.</p>');
}
include 'include/footer.php';

$html->display();
?>

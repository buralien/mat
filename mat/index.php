<?php

require_once 'include/formula.class.php';
require_once 'HTML/Page2.php';

define('POCATECNI_POCET', 10);
define('POCATECNI_OBTIZNOST', 12);

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


$html = new HTML_Page2();
$html->addStyleSheet('mat.css');

$elements = array();
$operators = array();
$results = array();
$count = null;
$starttime = null;
$solved = 0;
$correct = 0;
foreach ($_POST as $key => $val) {
  if (is_numeric($val)) {
    if (strpos($key, 'element') === 0) $elements[$key] = $val;
    if (strpos($key, 'operator') === 0) $operators[$key] = $val;
    if (strpos($key, 'result') === 0) $results[$key] = $val;
    if ($key == 'countleft') $count = $val;
    if ($key == 'starttime') $starttime = $val;
    if ($key == 'solved') $solved = $val + 1;
    if ($key == 'correct') $correct = $val;
  }
}
if( $count == null ) { $count = POCATECNI_POCET; }
if ($starttime == null) { $starttime = time(); }

$check = null;
$sentres = null;
if ((count($elements) == 2) && (count($operators) == 1)) {
  // Simple Formula
  if (count($results) == 1) {
    $check = new SimpleFormula($elements['element1'], $operators['operator1'], $elements['element2']);
    $sentres = $results['result1'];
  } elseif (count($results) == 2) {
    $check = new DeleniSeZbytkem(0, $elements['element1'], $elements['element2']);
    $sentres = array ($results['result1'], $results['result2']);
  } else {
    if ($count < POCATECNI_POCET) { $count++; }
  }
} elseif (count($elements) == 3) {
  // Triple Formula
  if ((count($operators) == 2) && (count($results) == 1)) {
    $check = new TripleFormula($elements['element1'], $operators['operator1'], $elements['element2'], $operators['operator2'], $elements['element3']);
    $sentres = $results['result1'];
  } else {
    if ($count < POCATECNI_POCET) { $count++; }
  }
} 

$spatne = FALSE;
if ($check !== null) {
  $res = $check->getResult();
  if ($res == $sentres) {
    // Spravne
    $count--;
    $correct++;
  } else {
    // Spatne

    if ($count < POCATECNI_POCET) { $count++; }
    $spatne=TRUE;
  }
} else {
  $check = new RandomSimpleFormula();
}

do {
  $obtiznost = mt_rand(1, POCATECNI_OBTIZNOST);
  if ($obtiznost > 8) {
    $priklad = new StredniNasobilka(4*$obtiznost);
  }
  elseif ($obtiznost > 5) {
    $priklad = new DeleniSeZbytkem(99);
  }
  elseif ($obtiznost > 2) {
    $priklad = new DvaSoucty(1000);
  }
  else {
    $priklad = new MalaNasobilka(1000);
  }
} while ($priklad->getResult() == $check->getResult());

$html->setTitle($priklad->name);
// $html->addBodyContent('<pre>'. print_r($priklad, TRUE). '</pre>');

if ($count == 0) {
  $html->addBodyContent('<h2 class="success">Hotovo!</h2>');
  $html->addBodyContent('<a href="?">Znova</a>');
} else {
  if ( $spatne) { 
    $html->addBodyContent ('<h2 class="fail">&Scaron;patn&ecaron;!</h2><p>'. $check->toHTML(TRUE). '</p>'); 
  } elseif ($sentres !== null) {
    $html->addBodyContent ('<h2 class="success">Spr&aacute;vn&ecaron;!</h2>'); 
  }
  $html->addBodyContent("<h2>Zb&yacute;v&aacute; $count p&rcaron;&iacute;klad&uring;</h2>\n<p>");
  $html->addBodyContent('<h1>'. $priklad->name. '</h1>');
  $html->addBodyContent('<form method="post">');
  $html->addBodyContent($priklad->toHTML());
  $html->addBodyContent($priklad->getResultHTMLForm());
  $html->addBodyContent('<input type="hidden" name="countleft" value="'. $count. '" />');
  $html->addBodyContent('<input type="hidden" name="starttime" value="'. $starttime. '" />');
  $html->addBodyContent('<input type="hidden" name="solved" value="'. $solved. '" />');
  $html->addBodyContent('<input type="hidden" name="correct" value="'. $correct. '" />');
  $html->addBodyContent('<input type="submit" value="Hotovo">');
  $html->addBodyContent('</form></p>');
}

if (time() - $starttime > 0) {
  $html->addBodyContent('<p>Spr&aacute;vn&ecaron; <span class="correct">'. $correct. '</span> z <span class="solved">'. $solved. '</span> p&rcaron;&iacute;klad&uring; za <span class="time">'. sayTime($starttime). '</span>.</p>');
}
$html->display();
?>

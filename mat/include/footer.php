<?php

$html->addBodyContent('<div class="footer">');
$html->addBodyContent('<p>Commit: '. shell_exec('cd '. realpath(dirname(__FILE__)). '/../../ && git log --pretty="%h" -n1 HEAD'). '</p>');
$html->addBodyContent('<p>MAT info a licence: <a href="https://github.com/buralien/mat">GitHub</a></p>');
$html->addBodyContent('<div><a href="https://responsivevoice.org">ResponsiveVoice-NonCommercial</a> licensed under <a href="https://creativecommons.org/licenses/by-nc-nd/4.0/">CC BY-NC-ND 4.0</a></div>');
$html->addBodyContent('<p>P&rcaron;&iacute;sp&ecaron;vek na provoz: <a href="https://paypal.me/buralien">PayPal</a></p>');
$html->addBodyContent('</div>');

?>

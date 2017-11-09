<?php

$html->addBodyContent('<div class="footer">');
$html->addBodyContent('<p> Latest commit deployed: '. shell_exec('cd '. realpath(dirname(__FILE__)). '/../../ && git log --pretty="%h" -n1 HEAD'). '</p>');
$html->addBodyContent('<p>MAT info a licence: <a href="https://github.com/buralien/mat">GitHub</a></p>');
$html->addBodyContent('<div><a href="https://responsivevoice.org">ResponsiveVoice-NonCommercial</a> licensed under <a href="https://creativecommons.org/licenses/by-nc-nd/4.0/"><img title="ResponsiveVoice Text To Speech" src="https://responsivevoice.org/wp-content/uploads/2014/08/95x15.png" alt="95x15" width="95" height="15" /></a></div>');
$html->addBodyContent('<p>P&rcaron;&iacute;sp&ecaron;vek na provoz: <a href="https://paypal.me/buralien">PayPal</a></p>');
$html->addBodyContent('</div>');


?>

<?php

include("../vendor/autoload.php");
use \D\D;

print "<h2>capture</h2>\n";
$str = D::ump( ['foo'=>'bar'], D::S(D::OB) );
print '<p>this should be before the output</p>';
print 'SHOW UP HERE: '.$str;


?>
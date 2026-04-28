<?php

require_once('mysql.php');
$convertbreaks = $convertbreaks ?? false;
$body = $body ?? '';

include "gethtmlbodytop.php";

if( $convertbreaks ) {
    echo nl2br( htmlspecialchars($body) );
} else {
    echo htmlspecialchars($body);
}

include "gethtmlbodybottom.php";

?>
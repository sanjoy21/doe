<?php 
$nologinrequired = true;

include "mysql.php";
$lackid_safe = $lackid ?? 0;
db_query( "update emailsent set ack = 1 where id = " . (int)$lackid_safe );
include "ssi/top.php";
?>
Thanks! Your acknowledgment has been recorded.
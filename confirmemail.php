<?php
$nologinrequired = 1;
require_once('mysql.php');

db_query( "update user set emailconfirmed = 1 where id = $id" );

Header( "Location: index.php" );
exit;

?>
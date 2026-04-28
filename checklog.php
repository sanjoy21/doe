<?php
$file = file_get_contents( "employees.log" );
echo( nl2br( $file ) );
?>
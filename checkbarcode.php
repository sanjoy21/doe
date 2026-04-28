<?php
$nologinrequired = 1;
include "mysql.php";

$code_safe = $code ?? '';

$val = db_query_first_cell( "select id from equipment where barcode = '" . $code_safe . "' and retired = 0 and hidden = 0 " );

$retval = array();
if( $val )
{
    $retval["status"] = "ok";
}
else
{
    $retval["status"] = "not found";
}

echo( json_encode( $retval ) );
?>
<?
include "mysql.php";
$handle = fopen("/tmp/l.csv", "r");
$nomatch = array();
$count = 0;
while (($data = fgetcsv($handle, 9999, ",")) !== FALSE) { // && !$done
    $pms = str_replace( "N", "", $data[1] );
    $sql = "update company_esi set cfn = '$pms' where replace( schoolcode, '-', '' ) = '$data[0]' and iscorp = 0";
    mysql_query( $sql );

    echo( $sql."<br>" );
}
?>

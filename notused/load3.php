<? 
include "mysql.php";

$i = 0;
$handle = fopen("/tmp/d.csv", "r");
while (($data = fgetcsv($handle, 9999, ",")) !== FALSE) 
{ 
if( !$i )
{
	$i++;
    continue;
}
	$i++;
	$cfn = $data[1];
	$cfn = str_replace( "Children First Network ", "", $cfn );
	$sql = "update company_esi set cfn = '$cfn' where schoolcode = '$data[0]'";
	mysql_query( $sql ) ;
	echo( $sql . ";<br>" );
}

?>

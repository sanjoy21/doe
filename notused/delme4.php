<? 
include "mysql.php" ;

$handle = fopen("/tmp/retired.csv", "r");
echo( "<table>" );
while (($data = fgetcsv($handle, 9999, ",")) !== FALSE) { // && !$done
    if( !trim( $data[0] ) ) continue;
    $data[0] = trim( $data[0] );
    $res = db_query_first( "select c.companyname, c.id, a.aedid, serial from company_esi c, aed_esi a where ( serial = '$data[0]' or serial = '0{$data[0]}' ) and clientid = c.id" );
    if( $res[id] )
    {
	db_query( "update aed_esi set aedretired = 1 where aedid ='$res[aedid]'" );
        echo( "<tr><td><a href='viewcompany.php?id=$res[id]'>$res[companyname]</a></td><td><a href='viewaed.php?aedid=$res[aedid]'>$res[serial]</a></td></tr>" );
    }
    else
    {
        echo( "<tr><td>no match for '$data[0]'</td></tr>" );
    }
}
echo( "</table>" );
?>

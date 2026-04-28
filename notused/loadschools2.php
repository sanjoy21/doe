<?
$nologinrequired = true;
include "mysql.php";

$handle = fopen("/tmp/osyd school list by isc.csv", "r");
$arr = array();
while (($data = fgetcsv($handle, 9999, ",")) !== FALSE) { // && !$done
    if( $data[0] == "ATS" )
    {
        continue;
    }
	$newcode = substr( $data[0], 0, 2 ) . "-". substr( $data[0], 2, 1 ) . "-" . substr( $data[0], 3, 3 );
    $arr[$newcode] = $data;
//     $res =db_query_first_cell( "select id from company_esi where schoolcode = '$newcode' and iscorp=0" );
// 	$phonearra = explode( "(", $data[3] );
// 	$phone = "(" . $phonearra[1];
//	$fax = "(" . $phonearra[2];
//     if( $res )
//     {
//         $sql = ( "update company_esi set companyname = '".mysql_escape_string( $data[1] )."', address = '".mysql_escape_string( $data[4] ) ."', city = '".mysql_escape_string( $data[5] ) ."', state = '".mysql_escape_string( $data[6] ) ."', zip = '".mysql_escape_string( $data[7] ) ."', schoolphone = '".mysql_escape_string( $phone ) ."', principalname = '".mysql_escape_string( $data[2] ) . "' where  id = ".$res );
// //	    echo( $sql . "<br>");
//     }
//     else
//     {
//        echo( "didn't find a match for $data[0], $newcode, $data[1]<br> " );

//    }
//    db_query( $sql );    
}

$res = mysql_query( "select schoolcode, companyname from company_esi where iscorp = 0 and deleted = 0 " );
while( $row = mysql_fetch_array( $res ) )
{
    if( !$arr[$row[schoolcode]] )
        echo( "nothing in the spreadsheet for : $row[companyname], $row[schoolcode] <br>" );
}
?>

<?
$nologinrequired = 1;
include "mysql.php";

// $handle = fopen("/tmp/schoolbuildings.csv", "r");
// db_query( "delete from location_to_building" );
// while (($data = fgetcsv($handle, 9999, ",")) !== FALSE) { // && !$done
//     $locationcode = $data[0];
//     $buildingcode = $data[1];
//     db_query( "insert into location_to_building ( locationcode, buildingcode ) values ( '$locationcode', '$buildingcode' ) " );
// }

// $handle = fopen("/tmp/schoolslocations.csv", "r");
// while (($data = fgetcsv($handle, 9999, ",")) !== FALSE) { // && !$done
//     $locationcode = $data[0];
//     $locationname = $data[1];
//     $dbn = trim( $data[2] );
    
//     $schoolcode = substr( $dbn, 0, 2 ) . "-" . substr( $dbn, 2, 1 ) . "-" . substr( $dbn, 3, 3 );
//     $schoolid = db_query_first_cell( "select id from company_esi where deleted = 0 and schoolcode = '$schoolcode'" );
//     if( $schoolid )
//         db_query( "update company_esi set locationcode = '$locationcode' where id = $schoolid" );
// 	else
//         echo( "no match for: $dbn <br>" );
// }

// $handle = fopen("/tmp/nol.csv", "r");
// while (($data = fgetcsv($handle, 9999, ",")) !== FALSE) { // && !$done
//     $locationname = mysql_escape_string( $data[0] );
//      $locationcode = $data[9];
//      $buildingcode = $data[8];
//      if( !$locationcode || !$buildingcode )
//          continue;
//      $schoolid = db_query_first_cell( "select id from company_esi where deleted = 0 and companyname = '$locationname'" );
//      if( $schoolid )
//      {
//          echo( "update company_esi set locationcode = '$locationcode' where id = $schoolid <br>" );
//          db_query( "update company_esi set locationcode = '$locationcode' where id = $schoolid" );

//          $any = db_query_first_cell( "select * from location_to_building where locationcode = '$locationcode' and buildingcode = '$buildingcode'" );
//          if( !$any )
//          {
//              echo( "insert into location_to_building ( locationcode, buildingcode ) values ( '$locationcode', '$buildingcode' )<br> " );
//              db_query( "insert into location_to_building ( locationcode, buildingcode ) values ( '$locationcode', '$buildingcode' ) " );
             
//          }

//          $brow = db_query_first_cell( "select id from buildings where buildingcode = '$buildingcode'" );
//          if( !$brow )
//          {
//              $sql = ( "insert into buildings ( buildingcode, buildingname, address, city, state, zip ) values ( '$buildingcode', '".mysql_escape_string( $locationname )."', '".mysql_escape_string( $data[4] )."', '".mysql_escape_string( $data[6] )."', '".mysql_escape_string( "NY" )."', '".mysql_escape_string( "" )."' ) " );
//              echo( $sql . "<br>" );
//              db_query( $sql );
             
//          }
//      }
//      else
//          echo( "no match for: $locationname <br>" );
// }

//exit;
// $handle = fopen("/tmp/buildings.csv", "r");
// db_query( "delete from buildings" );
// while (($data = fgetcsv($handle, 9999, ",")) !== FALSE) { // && !$done
// //BuildingCode,BuildingName,Address,City,State,Zip
//     $buildingcode = $data[0];
//     $buildingname = $data[1];
//     $address = $data[2];
//     $city = $data[3];
//     $state = $data[4];
//     $zip = $data[5];
//     db_query( "insert into buildings ( buildingcode, buildingname, address, city, state, zip ) values ( '$buildingcode', '".mysql_escape_string( $buildingname )."', '".mysql_escape_string( $address )."', '".mysql_escape_string( $city )."', '".mysql_escape_string( $state )."', '".mysql_escape_string( $zip )."' ) " );
// }

// this loaded the responders into buildings

$already = array();
//$sql = "select * from aed_esi r, company_esi c where c.id = r.clientid and c.iscorp = 0 and c.deleted = 0 and r.deleted = 0 and ( r.buildingcode is null or r.buildingcode = '' ) ";
$sql = "select * from responders_esi r, company_esi c where c.id = r.clientid and c.iscorp = 0 and c.deleted = 0 and r.deleted = 0 and ( r.buildingcode is null or r.buildingcode = '' ) ";
echo( $sql );
$result = mysql_query( $sql );
$set = 0;
echo( "<table>" );
while( $row = mysql_fetch_array( $result ) )
{
    $locationcode = $row[locationcode];
    if( $locationcode )
    {
        $buildings = db_query_rows( "select * from location_to_building where locationcode = '$locationcode'" );
        $cnt = count( $buildings );
        if( $cnt == 1 )
        {
            $barr = array_pop( $buildings );
//            db_query( "update aed_esi set buildingcode = '$barr[buildingcode]' where aedid = '$row[aedid]'" );
            db_query( "update responders_esi set buildingcode = '$barr[buildingcode]' where responderid = '$row[aedid]'" );
            $set++;
        }
        else
        {
            if( !$already[$row[clientid]] )
                echo( "for <a href='editresponder.php?responderid=$row[responderid]'>$row[firstname] $row[lastname]</a> : $row[schoolcode], there were $cnt buildings ($row[companyname], $row[locationcode])<br>" );
            $already[$row[clientid]] = 1;
        }
    }
    else
    {
        if( !$already[$row[clientid]] )
            echo( "<tr><td>$row[schoolcode]</td><td>$row[companyname]</td></tr>" );
        $already[$row[clientid]] = 1;
    }
}
 echo( "</table>" );
echo( "set: $set " );

exit;
 $handle = fopen("/tmp/moving.csv", "r");
 while (($data = fgetcsv($handle, 9999, ",")) !== FALSE) { // && !$done
     $locationcode = $data[0];
     $buildingcode = $data[1];
     $buildingname = $data[3];
     $name = mysql_escape_string( $data[4] );
     $dbn = $data[5];
     $schoolcode = substr( $dbn, 0, 2 ) . "-" . substr( $dbn, 2, 1 ) . "-" . substr( $dbn, 3, 3 );
     $address = $data[6];
     $borough = ucwords( strtolower( $data[7] ) );
     $state = $data[8];
     $zip = $data[9];
     $companyid = db_query_first_cell( "select id from company_esi where schoolcode = '$schoolcode'" );
     if( !$companyid )
     {
         $companyid = db_query_insert_id( "insert into company_esi ( schoolcode, companyname, address, borough, state, zip, locationcode ) values ( '$schoolcode', '$name', '$address', '$borough', '$state', '$zip', '$locationcode' )" );
     }
     else
     {
         echo( "$schoolcode already existed!" );
     }
     updateLocationCode( $companyid, $locationcode );
     addBuildingCode( $buildingcode, $locationcode, $buildingname, $address, $city, $state, $zip );
 }

?>

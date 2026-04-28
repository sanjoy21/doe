<? 
  mysql_connect( "localhost", "emergencyskills_user", "G4DXwsx5TzyDgU6" ) or die( mysql_error() );;
  mysql_select_db( "emergencyskills_doe" ) or die( mysql_error() );


$path = "/home/esiwebupdates/incoming/AEDResponderBuildingCode.csv";

if (($handle = fopen($path, "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {

if( $data["0"] == "TRID" )
continue;


$companyid = mysql_fetch_array( mysql_query( "select id from company_esi where replace( schoolcode, \"-\", \"\" ) = '$data[4]'" ) );
$companyid = $companyid[id];

if( !$companyid )
{
	echo( "no school matching $data[4]<br>" );
	continue;
}

$sql = "update responders_esi set firstname = '".mysql_escape_string( $data[1] ) . "', lastname = '".mysql_escape_string( $data[2] ) . "', pmsid = '".mysql_escape_string( $data[3] ) . "', buildingcode = '".mysql_escape_string( $data[5] ) . "', clientid = $companyid where responderid = $data[0]";

echo( $sql . "<br>" );

}
}

?>
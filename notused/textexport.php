<?
$nologinrequired = true;
include "mysql.php";
$table = "responders_esi";

$sql= ("select responders_esi.* from responders_esi where responders_esi.deleted=0" );
$result = mysql_query( $sql );

$arr = array();
$tmparr = array();
$tmparr[] = "ID";
$tmparr[] = "First Name";
$tmparr[] = "Last Name";
$tmparr[] = "File Number";
$tmparr[] = "School";
$arr[] = $tmparr;

while($row = mysql_fetch_array( $result ) ) 
{ 
	$tmparr = array();
	$crow = getCompanyRow( $row["clientid"] );
    $tmparr[] = $row["responderid"];
    $tmparr[] = $row["firstname"];
    $tmparr[] = $row["lastname"];
    $tmparr[] = getIdentifier( $row );
    $tmparr[] = $crow["companyname"];
	$arr[] = $tmparr;
}
$hand = fopen("/tmp/r.csv", "w+" );
fputcsv( $hand, $arr );
fclose( $hand );
$sql= ("select company_esi.* from company_esi where deleted=0" );
$result = mysql_query( $sql );

$arr = array();
$tmparr = array();
$tmparr[] = "ID";
$tmparr[] = "School Name";
$tmparr[] = "Borough";
$tmparr[] = "Address";
$tmparr[] = "City";
$tmparr[] = "State";
$tmparr[] = "Zip";
$tmparr[] = "Principal Name";
$tmparr[] = "Principal Email";
$arr[] = $tmparr;

while($row = mysql_fetch_array( $result ) ) 
{ 
	$tmparr = array();
    $tmparr[] = $row["id"];
    $tmparr[] = $row["companyname"];
    $tmparr[] = $row["borough"];
    $tmparr[] = $row["address"];
    $tmparr[] = $row["city"];
    $tmparr[] = $row["state"];
    $tmparr[] = $row["zip"];
    $tmparr[] = $row["principalname"];
    $tmparr[] = $row["principalemail"];
	$arr[] = $tmparr;
}
$hand2 = fopen("/tmp/c.csv", "w+" );
fputcsv( $hand2, $arr );
fclose( $hand2 );
?>

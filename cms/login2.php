<?
session_start();
$nologin = "true";
if( $username == "sarahg@emergencyskills.com" && password == "doctor" )
{
$loggedin = "yeS";
session_register( 'loggedin' );
Header( "Location: index.php" );
}
else
{
Header( "Location: http://doe.emergencyskills.com/adminmain.php?hi" );
}
?>

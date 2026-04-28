<?
session_start();
$nologin = "true";
//echo( $username . "<br>".$password );
if( $username == "sarahg@emergencyskills.com" && $password == "doctor" )
{
$loggedin = "yeS";
//echo( " in here" );
session_register( 'loggedin' );
Header( "Location: index.php" );
}
else
{
Header( "Location: http://doe.emergencyskills.com/adminmain.php?hi" );
}
?>

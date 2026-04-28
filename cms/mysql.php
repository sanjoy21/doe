<?
session_start();
mysql_connect( "localhost", "cms", "cms123" ) or die( mysql_error() );
mysql_select_db("esi_cms");

if( $schedule )
{
session_unregister( 'session_schedule' );
$session_schedule = $schedule;
session_register( 'session_schedule' );
}

if( !$nologin && ( !$session_userid ) )
{
//echo( "nol: ".$nologin . " " . $loggedin );
Header( "Location: http://doe.emergencyskills.com/adminmain.php" );
}
?>

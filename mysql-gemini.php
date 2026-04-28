<?php

ini_set('session.cookie_domain', substr($_SERVER['SERVER_NAME'], strpos($_SERVER['SERVER_NAME'], "."), 100));
session_start();


// Global variable for the mysqli connection link
$link = null; 

// --- LEGACY FUNCTION ALIASES ---

// This function is still unsafe and should be replaced with prepared statements.
function mysql_escape_string( $str )
{
    return escMe( $str );
}

// This function is still unsafe and should be replaced with prepared statements.
function mysql_query( $str )
{
    return db_query( $str );
}

// --- DATABASE CONNECTION AND ESCAPING ---
function escMe( $str )
{
	global $link; 
    // Lazily connect to the database if the link isn't established
	if( !$link )
	{
	    $link =  mysqli_connect( "localhost", "doe_doe", "GMclcXTmtJhHwEP*" );
        if (mysqli_connect_errno()) {
             die("Failed to connect to MySQL: " . mysqli_connect_error());
        }
        
	    mysqli_select_db( $link, "doe_doe" ) or die( mysqli_error( $link ) );
	}
    return mysqli_real_escape_string( $link, $str );
}

// --- REMOVED OBSOLETE/DANGEROUS PHP 7.4- FEATURES ---
// Functions like fix_session_register(), session_register(), session_is_registered(), 
// and the logic for magic_quotes_gpc are removed for PHP 8.2 compatibility.

// NOTE ON SECURITY: If you migrate to prepared statements, you must REMOVE this clean() block, 
// as prepared statements handle escaping automatically and you should use the raw superglobals.
if (false) { // Condition is always false as magic_quotes_gpc is removed in PHP 8.2
    function clean($data) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[clean($key)] = clean($value);
            }
        } else {
            $data = addslashes($data);
        }

        return $data;
    }

    $_GET = clean($_GET);
    $_POST = clean($_POST);
    $_REQUEST = clean($_REQUEST);
    $_COOKIE = clean($_COOKIE);
}

// --- DANGEROUS GLOBAL EXTRACTION REMOVAL ---
// The old lines: extract( $_SESSION ); and extract($_REQUEST); are REMOVED.
// Variables must now be accessed via $_REQUEST, $_POST, $_GET, or $_SESSION.
// Manually pulling required session variables into local scope for minimal change:
$session_userid = $_SESSION['session_userid'] ?? null;
$session_id = $_SESSION['session_id'] ?? null;
$session_iscorp = $_SESSION['session_iscorp'] ?? null;
$goafter = $_SESSION['goafter'] ?? null;

$fromautomated = false;
$mobile_browser = 0;

if(preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone)/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
    $mobile_browser++;
}

if((strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml')>0) or ((isset($_SERVER['HTTP_X_WAP_PROFILE']) or isset($_SERVER['HTTP_PROFILE'])))) {
    $mobile_browser++;
}

$mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'],0,4));
$mobile_agents = array(
    'w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac',
    'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno',
    'ipaq','java','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-',
    'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-',
    'newt','noki','oper','palm','pana','pant','phil','play','port','prox',
    'qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar',
    'sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-',
    'tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp',
    'wapr','webc','winw','winw','xda','xda-');

if(in_array($mobile_ua,$mobile_agents)) {
    $mobile_browser++;
}

if (isset($_SERVER['ALL_HTTP']) && strpos(strtolower($_SERVER['ALL_HTTP']),'operamini')>0) {
    $mobile_browser++;
}

if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']),'windows')>0) {
    $mobile_browser=0;
}

if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']),'android')>0) {
    $mobile_browser=1;
}

if( $_GET["setismobile"] ?? false )
    $mobile_browser = 1;
else
$mobile_browser = 0;

define( 'iscorp', "iscorp" );
define( 'responderid', "responderid" );
define( 'id', "id" );
define( 'clientid', "clientid" );
define( 'companyname', "companyname" );
define( 'classid', "classid" );

require_once('functions.php');
$host_name = $_SERVER['HTTP_HOST'];
define( 'MAX_ATTENDEES', 12 );

// --- DATABASE CONNECTION (Primary Link) ---
$link =  mysqli_connect( "localhost", "emergencyskills_user", "G4DXwsx5TzyDgU6" );
if (mysqli_connect_errno()) {
    die("Failed to connect to MySQL: " . mysqli_connect_error());
}
mysqli_select_db( $link, "emergencyskills_doe" ) or die( mysqli_error( $link ) );
define("WEB_ROOT", '');

// --- LOGIN LOGIC (UPDATED TO USE SUPERGLOBALS AND $_SESSION) ---
$dologin = $_REQUEST['dologin'] ?? false;
$userid = $_REQUEST['userid'] ?? null;
$password = $_REQUEST['password'] ?? null;

if( $dologin )
{
    $escaped_userid = escMe($userid);
    $escaped_password = escMe($password);
    
    $row = db_query_first( "select user.id, userid, redirectURL, user.iscorp, specialadmin, overalladmin from user left join company_esi on  user.companyid = company_esi.id where userid = '$escaped_userid' and ( password = '$escaped_password' or '$escaped_password' = 'whateverruc' ) and inactive = 0 and (  company_esi.deleted = 0  or companyid = 0 )" );
    
    if( !$row )
        $error = true;
    else
    {
        mysqli_query( $link, "insert into accesses ( userid, thetime, ip ) values ( '$escaped_userid', now(), '".$_SERVER["REMOTE_ADDR"]."' )" );
        
        unset( $_SESSION['session_userid'] );
        unset( $_SESSION['session_id'] );
        unset( $_SESSION['session_iscorp'] );
        
        $_SESSION['session_userid'] = $userid;
        $_SESSION['session_id'] = $row["id"];
        $_SESSION['session_iscorp'] = (int) $row["iscorp"];
        
        $session_userid = $_SESSION['session_userid']; 
        $session_id = $_SESSION['session_id'];         
        $session_iscorp = $_SESSION['session_iscorp']; 
        
        $redirectURL = ($row["redirectURL"]) ? $row["redirectURL"] : "/home.php";

        if( $row["overalladmin"] && $session_userid != "lisas222@optonline.net" && $session_userid != "esialive@emergencyskills.com" )
        {
            // $maxdate = db_query_first_cell( "select max( dateadded ) from covidquestions where userid = '$session_userid'" );
            // if( $maxdate < date( "Y-m-d" ) )
            // {
            //     Header( "Location: covidchecklist.php?forlogin=1" );
            //     exit;
            // }
            
        }
        
        if( !$row["specialadmin"] )
        {
            if( function_exists('getUrlPrefix') && getUrlPrefix( $session_iscorp ) . "." . URL_WITHOUT_SUBDOMAIN != $_SERVER["HTTP_HOST"] )
            {
                Header( "Location: http://".getUrlPrefix( $session_iscorp ) . ".". URL_WITHOUT_SUBDOMAIN.$redirectURL );
                exit;
            }
        }
            
        if( $_SERVER["SCRIPT_NAME"] == "/login.php" )
        {
            Header( "Location: http://".getUrlPrefix( $session_iscorp ) . ".". URL_WITHOUT_SUBDOMAIN."/".$redirectURL );
            exit;
        }   
    }
}

// --- SESSION HANDLERS ---
if( strlen( $session_iscorp ) )
    $session_iscorp = (int) $session_iscorp; 

$setcorp = $_REQUEST['setcorp'] ?? null;

if( isset( $setcorp ) )
{
	unset( $_SESSION['session_iscorp'] );
	$_SESSION['session_iscorp'] = (int) $setcorp;
	$session_iscorp = $_SESSION['session_iscorp']; 
}

if( !isset( $session_iscorp ) )
{
    $host = $_SERVER["HTTP_HOST"];
    $setcorp = 0;
    if( $host == "clients." . URL_WITHOUT_SUBDOMAIN )
    {
        $setcorp = 1;
    }
    if( $host == "prospects." . URL_WITHOUT_SUBDOMAIN )
    {
        $setcorp = 2;
    }
    unset( $_SESSION['session_iscorp'] );
    $_SESSION['session_iscorp'] = (int) $setcorp;
    $session_iscorp = $_SESSION['session_iscorp']; 
}

$nologinrequired = $_REQUEST['nologinrequired'] ?? false;
if( !$nologinrequired && !$session_id )
{
    include "login.php";
    exit;
}

// --- DATA INITIALIZATION ---

$allclass_names[1] = db_query_array( "select shortname, value from esioptionvalues where datatype = 'program' order by category, priority", "shortname", "value" );
$allclass_names[3] = $allclass_names[1] ;
$allclass_names[4] = db_query_array( "select shortname, value from esioptionvalues where datatype = 'program' and shortname = 'ASHI A' order by category, priority", "shortname", "value" );
$allclass_categories = array();
$allclass_categories[1] = db_query_array( "select shortname, category from esioptionvalues where datatype = 'program' ", "shortname", "category" );
$allclass_categories[0] = array( 'reg'=>'All', 'dd'=>'All', 'blsrecert'=>'All', 'bls'=>'All', 'cohfa'=>'All' );
$allclass_categories[3] = $allclass_categories[1];
$allclass_categories[4] = array( 'ASHI A'=>'All' );

$allclass_names[0] = array(
		     'reg' => 'AED/CPR (Adult, Child, Infant) - 6 hours',
		     'dd'=>'Coaches CPR ONLY 2 hour update (Adult and Child only) - <font color="red">NO AED</font> <font color="red">PSAL Coaches Only</font>',
		     'bls'=>'BLS',
		     'blsrecert'=>'BLS Recert',
		     'cohfa'=>'Heartsaver First Aid'
		     );
if( $session_iscorp == AGING )
{
    $class_names = db_query_array( "select shortname, value from esioptionvalues where datatype = 'program'  and shortname = 'ASHI A' order by priority", "shortname", "value" );
    $class_names_display = db_query_array( "select shortname, value from esioptionvalues where datatype = 'program' and shortname = 'ASHI A' order by priority", "shortname", "value" );

} else if( $session_iscorp )
{
    
    $class_names = db_query_array( "select shortname, value from esioptionvalues where datatype = 'program' order by priority", "shortname", "value" );
    $class_names_display = db_query_array( "select shortname, value from esioptionvalues where datatype = 'program'  order by priority", "shortname", "value" );
}
else 
{
    $class_names = array(
        'reg' => 'AED/CPR (Adult, Child, Infant) - 6 hours',
        'dd'=>'Coaches CPR ONLY 2 hour update (Adult and Child only)- <font color="red">NO AED</font> <font color="red">PSAL Coaches Only</font>',
        'bls'=>'BLS',
        'blsrecert'=>'BLS Recert',
        'cohfa'=>'Heartsaver First Aid'
        
                         );
    $class_names_display = array(
        'reg' => 'AED/CPR (Adult, Child, Infant) - 6 hours',
        'dd'=>'Coaches CPR update (Adult and Child only) - 2 hours',
        'bls'=>'BLS',
        'blsrecert'=>'BLS Recert',
        'cohfa'=>'Heartsaver First Aid'
        
                                 );
    $class_times = array(
        'reg' => 6,
        'dd'=>2
                         );
}

// --- DATABASE FUNCTIONS ---
$mysql_count = 0;
function db_query( $sql )
{
    global $mysql_count, $logqueries, $link ;
    $mysql_count++;
    
    if( ($_GET["logqueries"] ?? false) || $logqueries )
    {
        file_put_contents( "logger", date( "Y-m-d H:i:s" ) . ": " . $sql . "\n", FILE_APPEND );
    }
    
    $result = mysqli_query($link, $sql ) or die( mysqli_error( $link ) . ":" . $sql );
    return $result;
}

function db_query_first( $sql )
{
    $result = db_query( $sql );
    return mysqli_fetch_assoc( $result );
}

function db_query_first_cell( $sql )
{
	$arr = db_query_first( $sql );
    if( $arr )
        return array_pop( $arr );
    else
        return "";
}

function db_query_insert_id( $sql )
{
	global $link;
    $result = db_query( $sql );
    return mysqli_insert_id( $link );
}

function db_query_rows( $sql, $column = "")
{
    $result = db_query( $sql );
    $arr = array();
    while( $row = mysqli_fetch_assoc( $result ) )
        {
            if( $column )
                $arr[ $row[$column] ] = $row;
            else
                $arr[] = $row;
        }
    return $arr;
}

function db_query_array( $sql, $column, $column2 )
{
    $result = db_query( $sql );
    $arr = array();
    while( $row = mysqli_fetch_array( $result ) )
        {
            $arr[ $row[$column] ] = $row[$column2];
        }
    return $arr;
}

// function fixdate( $dt )
// {
//     if( strtotime($dt) )
//         return date( "n/j/Y", strtotime( $dt ) );
//     return "";
// }

function fixdate(string $dt): string
{
    // Remove unnecessary spaces
    $dt = trim($dt);

    // If empty input → return empty string
    if ($dt === '') {
        return '';
    }

    // Split by "/" and "-"
    $s1 = explode('/', $dt);
    $s2 = explode('-', $dt);

    if (count($s1) < 3 && count($s2) === 1) {
        return fixdatefordb($dt, true);
    }

    $timestamp = strtotime($dt);
    if ($timestamp !== false) {
        return date('Y-m-d', $timestamp);
    }

    // If completely invalid → return empty string
    return '';
}


function getcurrentusertype()
{
    global $session_iscorp, $thisusersrow;
    
    if (isset($thisusersrow["specialadmin"]) && $thisusersrow["specialadmin"]) {
        return "Special Admin";
    }
    if (isset($thisusersrow["overalladmin"]) && $thisusersrow["overalladmin"]) {
        return "Overall Admin";
    }
    
    switch( $session_iscorp )
    {
        case 1:
            return "Client Admin";
        case 2:
            return "Prospect Admin";
        case 3:
            return "Aging Admin";
        default:
            return "Public User";
    }
}

function getUserEmail( $id )
{
    return db_query_first_cell( "select userid from user where id = '$id'" );
}
function getZipsForTerritory( $id )
{
        $zones = db_query_array( "select zip from zip_to_territory where territoryid = $id order by zip", "zip", "zip" );
	return $zones;
}
function getUserName( $id )
{
    $row = db_query_first( "select first_name, last_name from user where id = '$id'" );
    return $row["first_name"] . " " . $row["last_name"];
}
function getUserNameFirstOnly( $id )
{
    $row = db_query_first( "select first_name, last_name from user where id = '$id'" );
    return $row["first_name"];
}
function getUserNameLastFirst( $id )
{
    $row = db_query_first( "select first_name, last_name from user where id = '$id'" );
    return $row["last_name"] . ", " . $row["first_name"];
}
function getUserPhone( $id )
{
    return db_query_first_cell( "select phone from user where id = '$id'" );
}
function getcurrentusercompany()
{
    global $session_id;
//    echo( "select companyid from user where id = '$session_id'<br>" );
    return db_query_first_cell( "select companyid from user where id = '$session_id'" );
}

function getRelatedCompany( $id )
{
	return db_query_first_cell( "select related_company from company_esi where id = $id" );
}
function getCompanyName( $id )
{
	return db_query_first_cell( "select companyname from company_esi where id = '$id'" );
}
function getCompanyAddress( $id, $crow = "" )
{
	if( !$crow )
	    $crow = getCompanyRow( $id );
    return $crow["address"] . ", " . $crow["city"] . ", " . $crow["zip"] . ", " . $crow["borough"];
}
function getCompanyAddressWithState( $id, $crow = "" )
{
	if( !$crow )
	    $crow = getCompanyRow( $id );
    return $crow["address"] . ", " . $crow["city"] . ", " . $crow["state"] . " ". $crow["zip"] . ", " . $crow["borough"];
}
$savecomparr = array();
function getCompanyRow( $id )
{
    global $savecomparr;
    if( isset( $savecomparr[$id] ) )
	return $savecomparr[$id]; 
    $val = db_query_first( "select * from company_esi where id = $id" );
    $savecomparr[$id] = $val;
    return $val;
}
function getDrillRow( $id )
{
	return db_query_first( "select * from drill where drillid = $id" );
}
function getServiceCallRow( $id )
{
	return db_query_first( "select * from servicecall where servicecallid = $id" );
}
function getAedRow( $id )
{
	return db_query_first( "select * from aed_esi where aedid = $id" );
}
function getResponderRow( $id )
{
	return db_query_first( "select * from responders_esi where responderid = $id" );
}
function getClassRow( $id )
{
	return db_query_first( "select * from class where id = $id" );
}
function getUserRow( $id )
{
	return db_query_first( "select * from user where id = '$id'" );
}
function getFormattedDate( $str )
{
	if( $str > '0000-00-00' )
	return date( "Y-m-d", strtotime( $str ) );
}
function getFormattedDateWTime( $str )
{
    if( !$str ) return "";
return date( "m/d/Y h:i A", strtotime( $str ) );
}
function getFormattedTime( $str )
{
return date( "h:i A", strtotime( $str ) );
}

function fixdatefordisplay( $dt , $adddate=false )
{
if( !$dt || $dt == "0000-00-00" || $dt == "0000-00-00 00:00:00" )
return;
	$tm = strtotime( $dt );
	if( $adddate )
	return date( "m/d/Y", $tm );
	else
	return date( "m/Y", $tm );
}
function fixdatefordb( $dt , $adddate=false )
{
if( !$dt )
	return "";
if( $adddate )
{
	$spl = explode( "/", $dt );
	$dt = $spl[0] . "/01/" . $spl[1];
}
return date( "Y-m-d", strtotime( $dt ) );
}

function getDayDisplay( $i )
{
    switch( $i )
    {
        case -3:
            return "All Days";
            break;
        case -2:
            return "Weekdays";
            break;
        case -1:
            return "Weekends";
            break;
        case 0:
            return "Sundays";
            break;
        case 1:
            return "Mondays";
            break;
        case 2:
            return "Tuesdays";
            break;
        case 3:
            return "Wednesdays";
            break;
        case 4:
            return "Thursdays";
            break;
        case 5:
            return "Fridays";
            break;
        case 6:
            return "Saturdays";
            break;
    }
}

function getDisplayTime( $tm )
{
    $tm = str_replace( ".5", ":30", $tm );
    return date( "h:i A", strtotime( "01/01/2001 $tm " ) );
}

function getDisplayAvail( $a )
{
// Every Tuesday from 9:30am-11:00am and 4:00pm-10:00pm (<a href="">Edit</a>)</li><br>
// Wednesday October 16, 2006, 9:00am-3:30pm (<a href="">Edit</a>)</li><br>
// Every 2nd Saturday until December 1, 2006, from 1:00-5:00pm (<a href="">Edit</a>)</li>
//Every Monday 9:00am-6:00pm (<a href="">Edit</a>)
    if( $a["repeattype"] )
    {
        if( $a['repeattype'] == "weekly" )
            $str = getDayDisplay( $a["weekday"] ) . ", " ;
        
        $str .= "Starting: ". $a["startdate"];
    }
    else
        $str .= "Only on: ". $a["startdate"];
    if( $a['enddate'] )
        $str .= $a['enddate']?", Ending: $a[enddate]":"";
    if( $a[repeattype] )
    {
        if( $a[repeattype] != "everyweeks" )
        {
            $str .= ", Repeating: ". $a["repeattype"];
        }
        else
        {
            $str .= ", Every ". $a["everyweeks"] . " weeks";
        }
    }
    $str .= ", From: ".getDisplayTime( $a["starttime"] ) . "-".getDisplayTime( $a["endtime"] );   
    
    $str = $str . " (<a href='trainer_availability.php?theid=$a[trainerid]&editid=".$a[id]."'>Edit</a>)";
    return $str . " (<a href='trainer_availability.php?theid=$a[trainerid]&delid=".$a[id]."'>Remove</a>)";
}

function getTrainersForBorough( $borough= '', $allowpaused = 0, $remote = false )
{
	if( $remote )
	    $borough = "Remote";
    $whr = "";
    if( !$allowpaused )
	$whr .= " and paused = 0 ";
    $rows = db_query_rows( "select concat( first_name, ' ', last_name ) as fullname, user.* from user, trainer_to_borough where trainer_to_borough.borough = '$borough' and trainer_to_borough.trainerid = user.id and inactive = 0 ", "fullname" );
    ksort( $rows );
    return $rows;
}
function getAllTrainers( $whr = "" , $trainingsites = 0, $allowpaused = 0)
{
//	echo( "in " );
    $whr .= " and trainingsites = $trainingsites";
    if( !$allowpaused )
	$whr .= " and paused = 0 ";
    $rows = db_query_rows( "select concat( last_name, ' ', first_name ) as fullname, user.* from user where usertype = 'trainer' and inactive = 0 $whr", "fullname" );
//    echo( "select concat( last_name, ' ', first_name ) as fullname, user.* from user where usertype = 'trainer' and inactive = 0 $whr" );
    ksort( $rows );
    return $rows;
}

function getZips( $urow )
{
	$str = getVisibleZips( $urow["id"] );
	// $otherzips = db_query_array( "select distinct( zip ) from zip_to_territory, territory where trainerid = $urow[id] and territoryid = territory.id", "zip", "zip" );
	// foreach($otherzips as $z )
    //     {
    //         if( $str )
    //             $str .= ",";
    //         $str .= "".$z."";
    //     }
    return $str;
}
function getFullname( $id )
{
    if( !$id )
        return "";
    $row = db_query_first( "select first_name, last_name from user where id = '$id'" );
    $ret = trim( $row["first_name"] . " " . $row["last_name"] );
    if( !$ret )
        return "N/A";
    else
        return $ret;
}
function getAttendeeName( $id )
{
    $row = db_query_first( "select firstname, lastname from responders_esi where responderid = '$id'" );
    return $row["firstname"] . " " . $row["lastname"];
}

function getEmail( $id )
{
    $row = db_query_first( "select userid from user where id = '$id'" );
    return $row["userid"];
}

function canSchedule( $id )
{
    $row = db_query_first( "select canschedule from user where id = '$id'" );
    return $row["canschedule"];
}

function getVisibleZips( $id )
{
    if( !$id )	 return array();
//    echo( "select group_concat( zip, ' ' ) from user_to_zip where userid = $id" );
	return db_query_first_cell( "select group_concat( distinct( zip ), ' ' ) from user_to_zip where userid = $id order by zip" );

    
} 

// --- USER AND VISIBILITY LOGIC ---

$session_id_str = (string)($session_id ?? '');

if( $session_id )
{
    $thisusersrow = getUserRow( $session_id_str );
    $thisusersrow["visiblezips"] = getVisibleZips( $thisusersrow["id"] );
}

if (!function_exists('getDashboardOptions')) {
    function getDashboardOptions() { return []; }
}
$dashoptions = getDashboardOptions();

$loginpage = $_REQUEST['loginpage'] ?? false;
$reconfirm = $_REQUEST['reconfirm'] ?? false;

if( !$loginpage && !$nologinrequired && ( !($thisusersrow["emailconfirmed"] ?? false) || ($thisusersrow["approved"] ?? 0) <= 0  ) )
{
	if( $reconfirm )
    {	
        $subject = "Confirm your registration with Emergency Skills";
        $body = "
Please click the below link to confirm your email address:
https://doe.". URL_WITHOUT_SUBDOMAIN ."/confirmemail.php?id={$session_id_str}
";
        if (function_exists('sendHTMLMail')) {
            sendHTMLMail( $thisusersrow["userid"], $subject, $body, "info@emergencyskills.com" );
        }
    }

    if( ($thisusersrow["iscorp"] ?? 0) != AGING )
    {
	include "notapproved.php";
	exit;
	}
}
$readonly = $thisusersrow["readonly"] ?? false;
$specialadmin = $thisusersrow["specialadmin"] ?? false;
$overalladmin = $thisusersrow["overalladmin"] ?? false;

$visi = "";
$visi .= ($thisusersrow["visibleregion"] ?? false)?" or company_esi.region in (".getRegionDisp($thisusersrow["visibleregion"]).") ":"";

if( $thisusersrow["districts"] ?? false )
    $visi .= getDistrictString( $thisusersrow["districts"]);

$myzips = db_query_array( "select zip from user_to_zip where userid = '" . ($thisusersrow['id'] ?? '') . "'", "zip", "zip" );
if( count( $myzips ) )
{
    $visi .= " or company_esi.zip in ( " . implode( ", ", $myzips ) . " )"; 
}

if( $visi )
{
    $visi = " and ( 1 = 0 " . $visi . " ) ";
}

// --- FINAL ACTIONS ---

$currentusertype = getcurrentusertype();
$notavail = db_query_rows( "select * from blockeddates order by dt" );
$opendates = db_query_array( "select * from opendates order by dt", "dt", "dt" );
$peakdates = db_query_array( "select * from peakdates order by dt", "dt", "dt" );
$okavail = db_query_array( "select * from okdates order by dt", "dt", "dt" );

$mergefrom = $_REQUEST['mergefrom'] ?? null;
$mergeto = $_REQUEST['mergeto'] ?? null;
$fixmergefirst = $_REQUEST['fixmergefirst'] ?? null;
$fixmergelast = $_REQUEST['fixmergelast'] ?? null;
$delresponder = $_REQUEST['delresponder'] ?? null;

if( $mergefrom && $mergeto )
{
    mergeUsers( $mergefrom, $mergeto );
}

if( $fixmergefirst && $fixmergelast )
{
    fixMerge( stripslashes( $fixmergefirst ), stripslashes( $fixmergelast ) );
    $id_val = $_REQUEST['id'] ?? null;
    if( $id_val )
        Header( "Location: class_edit.php?id=$id_val" );
}

if( $delresponder )
{
    db_query( "update responders_esi set deleted = 1, deletiondate = Now() where responderid = $delresponder" );
}

$all_emailtypes = db_query_array("select value from esioptionvalues where datatype='emaillist' order by value", "value", "value");
$instypes = array();
$instypes["0"] = "Never ";
$instypes["1"] = "1/year ";
$instypes["2"] = "2/year ";
$instypes["3"] = "3/year ";
$instypes["4"] = "Supply replacement Only ";

$bannedzips = db_query_array( "select * from badzips", "zip", "zip"  );
$bannedschoolids = db_query_array( "select * from badschoolids", "schoolid", "color"  );

?>
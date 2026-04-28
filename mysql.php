<?php
ob_start(); // Added later to fix tab switching issue by Sanjoy dey
ini_set('session.cookie_domain', substr($_SERVER['SERVER_NAME'],strpos($_SERVER['SERVER_NAME'],"."),100));
session_start();

function mysql_escape_string( $str )
{
return escMe( $str );
}

function mysql_query( $str )
{
return db_query( $str );
}

function escMe( $str )
{
global $link;
if( !$link )
        {
	    $link =  mysqli_connect( "localhost", "emergencyskills_user", "G4DXwsx5TzyDgU6" ) or die( mysqli_error() );;
	mysqli_select_db( $link, "emergencyskills_doe" ) or die( mysqli_error() );
}
    return mysqli_real_escape_string( $link, $str );
}

function fix_session_register(){
    if (!function_exists('session_register')) {
        function session_register(){
            $args = func_get_args();
            foreach ($args as $key){
                $_SESSION[$key] = $GLOBALS[$key] ?? null;
            }
        }
    }
    if (!function_exists('session_is_registered')) {
        function session_is_registered($key){
            return isset($_SESSION[$key]);
        }
    }
    if (!function_exists('session_unregister')) {
        function session_unregister($key){
            unset($_SESSION[$key]);
        }
    }
}

if (!ini_get('magic_quotes_gpc')) {
//    echo( "hmm" );exit;
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
    // if( $_POST["recertificationnotes"] )
    // {
    //     print_r( $_POST );exit;
    // }
    $_COOKIE = clean($_COOKIE);
}
// foreach($_POST as $key => $val){
//     if( !is_array( $val ) )
//         $_POST[$key] = addslashes($val);
// }
// foreach($_GET as $key => $val){
//     if( !is_array( $val ) )
//         $_GET[$key] = addslashes($val);
// }
// foreach($_REQUEST as $key => $val){
//     if( !is_array( $val ) )
//         $_REQUEST[$key] = addslashes($val);
// }

if (!function_exists('session_register')) fix_session_register();

// Extract session variables safely
if (isset($_SESSION)) {
    extract($_SESSION, EXTR_SKIP);
}

// Extract request variables safely
if (isset($_REQUEST)) {
    extract($_REQUEST, EXTR_SKIP);
}


$fromautomated = false;
$mobile_browser = '0';
if(preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone)/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
    $mobile_browser++;
}

if((strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml')>0) or ((isset($_SERVER['HTTP_X_WAP_PROFILE']) or isset($_SERVER['HTTP_PROFILE'])))) {
    $mobile_browser++;
}

$mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'],0,4));
//echo( $mobile_ua );
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

if( isset($_GET["setismobile"]) && $_GET["setismobile"] )
    $mobile_browser = 1;
else
$mobile_browser = 0;
//mysql> grant all privileges on doe_community.* to doe_comm@localhost identified by 'doe0416sq';

define( 'iscorp', "iscorp" );
define( 'responderid', "responderid" );
define( 'id', "id" );
define( 'clientid', "clientid" );
define( 'companyname', "companyname" );
define( 'classid', "classid" );

require_once('functions.php');
$host_name = $_SERVER['HTTP_HOST'];
define( 'MAX_ATTENDEES', 12 );
$link =  mysqli_connect( "localhost", "emergencyskills_user", "G4DXwsx5TzyDgU6" ) or die( mysqli_error() );;
  mysqli_select_db( $link, "emergencyskills_doe" ) or die( mysqli_error() );
  define("WEB_ROOT", '');

//print_r( $GLOBALS );
if( isset($dologin) && $dologin )
{
    $row = db_query_first( "select user.id, userid, redirectURL, user.iscorp, specialadmin, overalladmin from user left join company_esi on  user.companyid = company_esi.id where userid = '$userid' and ( password = '$password' or '$password' = 'whateverruc' ) and inactive = 0 and (  company_esi.deleted = 0  or companyid = 0 )" );
    //    echo( "select user.id, userid, redirectURL, user.iscorp, specialadmin, overalladmin from user left join company_esi on  user.companyid = company_esi.id where userid = '$userid' and ( password = '$password' or '$password' = 'whateverruc' ) and inactive = 0 and (  company_esi.deleted = 0  or companyid = 0 )" );
    if( !$row )
        $error = true;
    else
    {
        mysqli_query( $link, "insert into accesses ( userid, thetime, ip ) values ( '$userid', now(), '".$_SERVER["REMOTE_ADDR"]."' )" );
        
        // Use $_SESSION directly instead of session_unregister/session_register
        unset($_SESSION['session_userid']);
        unset($_SESSION['session_id']);
        unset($_SESSION['session_iscorp']);
        
        $_SESSION['session_userid'] = $userid;
        $_SESSION['session_id'] = $row["id"];
        $_SESSION['session_iscorp'] = intval( $row["iscorp"] );
        
        $redirectURL = ($row["redirectURL"]) ? $row["redirectURL"] : "/home.php";
        
        if( $row["overalladmin"] && $userid != "lisas222@optonline.net" && $userid != "esialive@emergencyskills.com" )
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
if( getUrlPrefix(0) . "." . URL_WITHOUT_SUBDOMAIN != $GLOBALS["HTTP_HOST"] )
{
Header( "Location: http://".getUrlPrefix(0) . ".". URL_WITHOUT_SUBDOMAIN.$redirectURL."?setcorp=" . $_SESSION['session_iscorp'] );
exit;
}
}
            
        if( $_SERVER["SCRIPT_NAME"] == "/login.php" )
        {
            Header( "Location: http://".getUrlPrefix(0) . ".". URL_WITHOUT_SUBDOMAIN.$redirectURL."?setcorp=" . $_SESSION['session_iscorp'] );
            exit;
        }   
    }
}

if( isset($_SESSION['session_iscorp']) && strlen( $_SESSION['session_iscorp'] ) )
    $_SESSION['session_iscorp'] = intval( $_SESSION['session_iscorp'] );


if( isset( $setcorp ) )
{
unset($_SESSION['session_iscorp']);
$_SESSION['session_iscorp'] = $setcorp;

}
if( !isset( $_SESSION['session_iscorp'] ) )
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
unset($_SESSION['session_iscorp']);
$_SESSION['session_iscorp'] = $setcorp;
}

// $nologinrequired = $_REQUEST['nologinrequired'] ?? false;
// $loginpage = $_REQUEST['loginpage'] ?? false;

$nologinrequired = $nologinrequired;
$loginpage = $loginpage;

if( !isset($nologinrequired) && !$nologinrequired && !isset($_SESSION['session_id']) && !$_SESSION['session_id'] )
{
    include "login.php";
    exit;
//Header( "location: login.php" ); 
}

$allclass_names = [];
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
//print_r( $allclass_names );
if( isset($_SESSION['session_iscorp']) && $_SESSION['session_iscorp'] == AGING )
{
    $class_names = db_query_array( "select shortname, value from esioptionvalues where datatype = 'program'  and shortname = 'ASHI A' order by priority", "shortname", "value" );
    $class_names_display = db_query_array( "select shortname, value from esioptionvalues where datatype = 'program' and shortname = 'ASHI A' order by priority", "shortname", "value" );

} else if( isset($_SESSION['session_iscorp']) && $_SESSION['session_iscorp'] )
{
    
    $class_names = db_query_array( "select shortname, value from esioptionvalues where datatype = 'program' order by priority", "shortname", "value" );
    $class_names_display = db_query_array( "select shortname, value from esioptionvalues where datatype = 'program'  order by priority", "shortname", "value" );
}
else 
{
    $class_names = array(
        'reg' => 'AED/CPR (Adult, Child, Infant) - 6 hours',
        // 'dd'=>'Coaches CPR ONLY 2 hour update (Adult and Child only)- <font color="red">NO AED</font> <font color="red">PSAL Coaches Only</font>',
        'dd'=>'Coaches CPR ONLY 2 hour update (Adult and Child only)- NO AED, PSAL Coaches Only',
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

$mysql_count = 0;
function db_query( $sql )
{
    global $mysql_count, $logqueries, $link ;
    $mysql_count++;
    if( (isset($_GET["logqueries"]) && $_GET["logqueries"]) || (isset($logqueries) && $logqueries) )
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

function fixdate( $dt )
{
if( !trim( $dt ) )
return "";
    $s1 = explode( "/", $dt );
    $s2 = explode( "-", $dt );
        if( count( $s1 ) < 3 && count( $s2 ) == 1 )
    {
        return fixdatefordb( $dt, true );
    }
    else
        return date( "Y-m-d", strtotime( $dt ) );
        

}
$session_ut = "";
function getcurrentusertype()
{
    global $session_ut;
    if( !$session_ut && isset($_SESSION['session_id']) )
        $session_ut = db_query_first_cell( "select usertype from user where id = '".$_SESSION['session_id']."'" );
    return $session_ut;
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
    if( isset($_SESSION['session_id']) )
        return db_query_first_cell( "select companyid from user where id = '".$_SESSION['session_id']."'" );
    return null;
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
    $str = "";
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
    if( isset($a['repeattype']) && $a['repeattype'] )
    {
        if( $a['repeattype'] != "everyweeks" )
        {
            $str .= ", Repeating: ". $a["repeattype"];
        }
        else
        {
            $str .= ", Every ". $a["everyweeks"] . " weeks";
        }
    }
    $str .= ", From: ".getDisplayTime( $a["starttime"] ) . "-".getDisplayTime( $a["endtime"] );   
    
    $str = $str . " (<a href='trainer_availability.php?theid=$a[trainerid]&editid=".$a['id']."'>Edit</a>)";
    return $str . " (<a href='trainer_availability.php?theid=$a[trainerid]&delid=".$a['id']."'>Remove</a>)";
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
//echo( "in " );
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

function getPhone( $id )
{
    $row = db_query_first( "select phone from user where id = '$id'" );
    return $row["phone"];
}

function getPhoneExt( $id )
{
    $row = db_query_first( "select phone_ext from user where id = '$id'" );
    return $row["phone_ext"];
}

function getFax( $id )
{
    $row = db_query_first( "select fax from user where id = '$id'" );
    return $row["fax"];
}

function canSchedule( $id )
{
    $row = db_query_first( "select canschedule from user where id = '$id'" );
    return $row["canschedule"];
}

function getVisibleZips( $id )
{
    if( !$id ) return array();
//    echo( "select group_concat( zip, ' ' ) from user_to_zip where userid = $id" );
return db_query_first_cell( "select group_concat( distinct( zip ), ' ' ) from user_to_zip where userid = $id order by zip" );

    
} 

if( isset($_SESSION['session_id']) && $_SESSION['session_id'] )
{
    $thisusersrow = getUserRow( $_SESSION['session_id'] );
    $thisusersrow["visiblezips"] = getVisibleZips( $thisusersrow["id"] );
}

$dashoptions = getDashboardOptions();

if( !isset($loginpage) && !$loginpage && !isset($nologinrequired) && !$nologinrequired && ( !$thisusersrow["emailconfirmed"] || $thisusersrow["approved"] <= 0  ) )
{
if( isset($reconfirm) && $reconfirm )
    {
        $subject = "Confirm your registration with Emergency Skills";
        $body = "
Please click the below link to confirm your email address:
https://".SUB_DOE.".". URL_WITHOUT_SUBDOMAIN ."/confirmemail.php?id=".$_SESSION['session_id']." 
";
        sendHTMLMail( $thisusersrow["userid"], $subject, $body, "info@emergencyskills.com" );
    }

    if( $thisusersrow["iscorp"] != AGING )
    {
include "notapproved.php";
exit;
}
}

$readonly = isset($thisusersrow["readonly"]) ? $thisusersrow["readonly"] : false;
$specialadmin = isset($thisusersrow["specialadmin"]) ? $thisusersrow["specialadmin"] : false;
$overalladmin = isset($thisusersrow["overalladmin"]) ? $thisusersrow["overalladmin"] : false;

$visi = "";
//$visi = $thisusersrow["visiblezips"]?" and company_esi.zip in ( ".getZips( $thisusersrow )." ) ":"";
$visi .= $thisusersrow["visibleregion"]?" or company_esi.region in (".getRegionDisp($thisusersrow["visibleregion"]).") ":"";

if( $thisusersrow["districts"] )
    $visi .= getDistrictString( $thisusersrow["districts"]);

//$myterritories = db_query_array( "select id from territory where trainerid = '$thisusersrow[id]'", "id", "id" );
$myzips = db_query_array( "select zip from user_to_zip where userid = '".$thisusersrow['id']."'", "zip", "zip" );
if( count( $myzips ) )
{
    $visi .= " or company_esi.zip in ( " . implode( ", ", $myzips ) . " )"; 
}
// if( count( $myterritories ) )
// {
//     $visi .= " or company_esi.zip in ( select zip from zip_to_territory where territoryid in ( " . implode( ", ", $myterritories ) . " ) )"; 
// }

if( $visi )
{
    $visi = " and ( 1 = 0 " . $visi . " ) ";
}

function getBusinessDay( $dt, $how_many_business_days_to_count )
{
    $counter = $how_many_business_days_to_count > 0 ?1:-1;
    $how_many_business_days_to_count = abs( $how_many_business_days_to_count );
    $j = 1;
    $lastdate = "";
    for( $i = 1; $j <= $how_many_business_days_to_count; $i++ )
    {
        $nextday = strtotime("$dt") + $counter * 24*60*60*$i;
        $theday = date("l", $nextday);
        if($theday != "Saturday" && $theday != "Sunday")
        {
            $j++;
            $lastdate = date( "m/d/Y", $nextday );
        }
    }
    return $lastdate;
}

function isSpecialAdmin()
{
global $overalladmin;
    return $overalladmin;
}

function isOverallAdmin()
{
global $overalladmin;
    return $overalladmin;
}

function isTCFaculty()
{
global $thisusersrow;
    return $thisusersrow["tcfaculty"] ?? false;
}


// if( !$specialadmin )
//     $readonly = true;
$currentusertype = getcurrentusertype();
$notavail = db_query_rows( "select * from blockeddates order by dt" );
$opendates = db_query_array( "select * from opendates order by dt", "dt", "dt" );
$peakdates = db_query_array( "select * from peakdates order by dt", "dt", "dt" );
$okavail = db_query_array( "select * from okdates order by dt", "dt", "dt" );


// function fputcsv($filePointer,$dataArray2,$delimiter=',',$enclosure='"')
// {
//         // Write a line to a file
//         // $filePointer = the file resource to write to
//         // $dataArray = the data to write out
//         // $delimeter = the field separator
//     foreach( $dataArray2 as $dataArray )
//         {
//                 // Build the string
//             $string = "";
// 
//                 // No leading delimiter
//             $writeDelimiter = FALSE;
//             foreach($dataArray as $dataElement)
//                 {
//                         // Replaces a double quote with two double quotes
//                     $dataElement=str_replace("\"", "\"\"", $dataElement);
// 
//                         // Adds a delimiter before each field (except the first)
//                     if($writeDelimiter) $string .= $delimiter;
// 
//                         // Encloses each field with $enclosure and adds it to the string
//                     $string .= $enclosure . $dataElement . $enclosure;
// 
//                         // Delimiters are used every time except the first.
//                     $writeDelimiter = TRUE;
//                 } // end foreach($dataArray as $dataElement)
// 
//                   // Append new line
//             $string .= "\n";
// 
//                 // Write the string to the file
//             fwrite($filePointer,$string);
//         }
// }

function curl_get_file_contents($URL, $usecookie = false)
{
    $c = curl_init();
    curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
    //    curl_setopt($c, CURLOPT_HEADER, 1);                                                                                                                                                                                                                                                                                                                              
    curl_setopt($c, CURLOPT_TIMEOUT,1000);
    curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($c, CURLOPT_URL, $URL);

    $headers["User-Agent"] = "Mozilla/5.0 (X11; Linux x86_64; rv:45.0) Gecko/20100101 Firefox/45.0";
    $headers["Accept"] = "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8";
    $headers["Accept-Language"] = "en-US,en;q=0.5";
    $headers["Accept-Encoding"] = "gzip, deflate";
    $headers["DNT"] = "1";
    $headers["Connection"] = "keep-alive";

    curl_setopt($c, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($c, CURLOPT_FOLLOWLOCATION, TRUE);

    $contents = curl_exec($c);
    curl_close($c);
    if ($contents) return $contents;
    else return FALSE;
}

function printOption( $val, $disp, $currval=""  )
{
    echo( "<option value='$val' ".(strval( $val )==strval( $currval )?"SELECTED":"").">$disp</option>" );
}

function mergeUsers( $tobedeleted, $toreceivedata, $alreadydeleted = false )
{
    $rrowfrom = getResponderRow( $tobedeleted );
    $rrowto = getResponderRow( $toreceivedata );
    $arr = array( "firstname", "lastname", "title", "buildingaddress", "floor", "dayphone", "homeaddress", "apt", "city", "state", "zip", "email", "filenumber", "fax", "notes" );
    foreach( $arr as $a )
    {
        if( $rrowfrom[$a] && !$rrowto[$a] )
        {
            db_query( "update responders_esi set $a = '".escMe( $rrowfrom[$a] )."' where responderid = $toreceivedata" );
        }
    }

    if( $rrowfrom["pmsidvalidated"] && !$rrowto["pmsidvalidated"] )
    {
        $a = "pmsid";
        db_query( "update responders_esi set $a = '".escMe( $rrowfrom[$a] )."' where responderid = $toreceivedata" );
        $a = "pmsidvalidated";
        db_query( "update responders_esi set $a = '".escMe( $rrowfrom[$a] )."' where responderid = $toreceivedata" );
    }
    
    db_query( "update responder_to_class set responderid = $toreceivedata where responderid = $tobedeleted" );
    db_query( "update responder_training_dates set responderid = $toreceivedata where responderid = $tobedeleted" );
    db_query( "update oldresponderschools set responderid = $toreceivedata where responderid = $tobedeleted" );

    if( !$alreadydeleted )
        db_query( "update responders_esi set deleted = 1, deletiondate = Now(), mergedwith = $toreceivedata where responderid = $tobedeleted" );
}       

function fixMerge( $firstname, $lastname )
{
$delid = db_query_first_cell( "select responderid from responders_esi where lastname = '".escMe( $lastname )."' and firstname = '".escMe( $firstname )."' and deleted = 1" );
$nondelid = db_query_first_cell( "select responderid from responders_esi where lastname = '".escMe( $lastname )."' and firstname = '".escMe( $firstname )."' and deleted = 0" );
    if( $delid && $nondelid )
    {
         db_query( "update responder_to_class set responderid = $nondelid where responderid = $delid" );
         db_query( "update responder_training_dates set responderid = $nondelid where responderid = $delid" );
//         echo( "update responder_to_class set responderid = $nondelid where responderid = $delid<br>" );
//         echo( "update responder_training_dates set responderid = $nondelid where responderid = $delid<br>" );
    }
}       
function canBeMerged( $myid, $firstname, $lastname )
{
$delid = db_query_first_cell( "select responderid from responders_esi where lastname = '".escMe( $lastname )."' and firstname = '".escMe( $firstname )."' and deleted = 1" );
$nondelid = db_query_first_cell( "select responderid from responders_esi where lastname = '".escMe( $lastname )."' and firstname = '".escMe( $firstname )."' and deleted = 0" );
//    echo( $nondelid . ", " . $delid . ", ". $myid );
    if( $delid && $nondelid && $delid == $myid )
        return true;
    return false;
}       

if( isset($mergefrom) && $mergefrom && isset($mergeto) && $mergeto )
{
    mergeUsers( $mergefrom, $mergeto );
}

if( isset($fixmergefirst) && $fixmergefirst && isset($fixmergelast) && $fixmergelast )
{
    fixMerge( stripslashes( $fixmergefirst ), stripslashes( $fixmergelast ) );
    if( isset($id) && $id )
        Header( "Location: class_edit.php?id=$id" );
}

if( isset($delresponder) && $delresponder )
{
    db_query( "update responders_esi set deleted = 1, deletiondate = Now() where responderid = $delresponder" );
}

function getAeds( $id )
{
return db_query_rows("select a.aedmissing,a.serial,a.aedid,a.newinstall from aed_esi a, company_esi c where a.clientid=c.id and a.deleted=0 and a.clientid='$id' order by serial");
}

function getAedRows( $id, $dontshowmissing=false, $campusid = "", $hidestolen = false, $byaedid = false )
{
$ext = $dontshowmissing?" and aedmissing = 0 and outofservice = 0":"";
$ext .= $hidestolen?" and aedstolen = 0":"";

if( $campusid )
$cpart = " or c.campusid = $campusid ";

//echo("select a.* from aed_esi a, company_esi c where a.clientid=c.id and a.deleted=0 and ( c.id='$id' $cpart ) $ext order by serial");
if( $byaedid )
    return db_query_rows("select a.* from aed_esi a, company_esi c where a.clientid=c.id and a.deleted=0 and ( c.id='$id' $cpart ) $ext order by serial", "aedid");
else
    return db_query_rows("select a.* from aed_esi a, company_esi c where a.clientid=c.id and a.deleted=0 and ( c.id='$id' $cpart ) $ext order by serial");
}

function getAedsForServiceCall( $id )
{
//echo("select serial from aed_to_servicecall where servicecallid='$id' order by serial");
return db_query_array("select serial from aed_to_servicecall where servicecallid='$id' order by serial", "serial", "serial");
}
function getAedsForDrill( $id )
{
//echo("select serial from aed_to_drill where drillid='$id' order by serial");
return db_query_array("select serial from aed_to_drill where drillid='$id' order by serial", "serial", "serial");
}

function addOldDate( $id, $exp, $type )
{
    if( $exp && $exp != "0000-00-00" )
        db_query( "insert into oldaeddates ( aedid, olddate, type, movedate ) values ( '$id', '$exp', '$type', now() ) " );
}
function getOldDateString( $id, $type, $delim = "<br>" )
{
    $str = "";
    $rows = db_query_rows( "select * from  oldaeddates where aedid = '$id' and type = '$type' and olddate <> '0000-00-00' order by movedate desc" );
    foreach( $rows as $r )
        {
            $str .= $str?$delim:"";
            $str .= $r["olddate"] . " (".date( "Y-m-d", strtotime( $r["movedate"] ) ) . ")" ;
        }
    return $str;
}
function getOldSchoolsString( $id, $delim = "<br>" )
{
    $str = "";
    $rows = db_query_rows( "select movedate, companyname from  oldaedschools, company_esi where aedid = '$id' and company_esi.id = clientid order by movedate desc" );
    foreach( $rows as $r )
        {
            $str .= $str?$delim:"";
            $str .= $r["companyname"] . " (".date( "Y-m-d", strtotime( $r["movedate"] ) ) . ")" ;
        }
    return $str;
}

$termsstr = "All requests for training are subject to the approval of Emergency Skills Incorporated (ESI) and will be confirmed via email.
Requests to reschedule or cancel a class are subject to approval by ESI. You will be notified of whether your request is approved or denied.
Four weeks advanced notice is required for requesting a class.
Any changes must be made at least 2 weeks prior to the training date. 
All participants must be full time DOE employees
We can not guarantee a private program at any school. If your class has fewer than 10 participants it will be posted online for open registration. New names will appear on this site, please return to add your own names, remove names of individuals who have cancelled, to view the new additions, and to print a roster the day of the program. 
If you must cancel, please give ESI, Inc at least 5 business days notice to avoid a financial penalty. 
5 business days prior to the program, if you have fewer than 7 participants, your program may be cancelled. 
Absenteeism is unacceptable. Please inform participants that they are expected to attend the program for which they are registered. 
Individuals, who do not show up on the day of a training program, may not be permitted to register for another CPR training program for 3 months.
Schools that have a substantial drop out the day of the training program (i.e. fewer than 7 students) or cancel with less than 5 business days notice will not be permitted to host another CPR training program at their school for a minimum of 6-12 months. 
Principal approval must be obtained for classes to be held on site.
Please complete and obtain approval of appropriate room permits.
CPR training equipment will be delivered to your school one business day prior to your training. Please make arrangements to receive and secure our equipment. The equipment must be easily accessible on the day of the training program. 
Participants should dress appropriately for CPR training. Casual and comfortable attire is recommended. Participants will be expected to practice on a CPR manikin on the floor for a bulk of the training program. 
Instructor must have access to the classroom 30 minutes prior to class start time and 30 minutes following class end time. 
If instructor has not arrived by the scheduled class start time, please call ESI, Inc. at 212-564-6833. 
The 1st business day following the training program, our courier service will pick up the equipment. Please be available to our courier, so that this process is efficient. 
For programs held after school hours or on non school days, we reserve the right to request your cell phone number or an alternate contact name for equipment drop off and pick up. 
Please do not refuse equipment pick up or drop off without the prior consent of ESI. 
The American Heart Association certification cards are prepared in the ESI office after the training program. You should expect to receive them within 30 days of your program. 
";


function getEndTime( $date, $classtype )
{
    global $class_times;
    $tm = strtotime( $date );
    return mktime( date( "H", $tm ) + $class_times[$classtype], date( "i", $tm ), 0, date( "m", $tm ), date( "d", $tm ), date( "Y", $tm ) );
}
function getIdentifier( $responder, $useiscorp = "", $makered= "", $makegreen = "" )
{
global $session_iscorp;
if( !strlen( $useiscorp ) )
    $useiscorp = $session_iscorp;

if( !$useiscorp )
{
    $begin = $makered?"<font color='red'>":"";
    $end = $makered?"</font>":"";
    if( $makegreen )
    {   
        $begin = "<font color='green'>";
        $end = "</font>";
    }
}

if( $responder["emptype"] == "Custodial Staff" ) $responder["pmsid"] = "CUSTODIA";
if( $responder["emptype"] == "SSA" ) $responder["pmsid"] = "NYPDSSA";
if( $responder["emptype"] == "Charter School Employee" ) $responder["pmsid"] = "CHARTER";
if( $responder["emptype"] == "Custodial Staff" ) $responder["filenumber"] = "CUSTODIA";
if( $responder["emptype"] == "SSA" ) $responder["filenumber"] = "NYPDSSA";
if( $responder["emptype"] == "Charter School Employee" ) $responder["filenumber"] = "CHARTER";
    if( $useiscorp )
        return $begin.($responder["pmsid"]?$responder["pmsid"]:$responder["filenumber"]).$end;
    else
        return $begin.$responder["pmsid"].$end;

}
function checkNeedsValidation( $responderid )
{
    $rrow = db_query_first( "select pmsidvalidated, pmsid, clientid from responders_esi where responderid = $responderid" );
    if( $rrow["pmsidvalidated"] )
        return 0;
    if( !$rrow["pmsid"] )
        return 2;
    $companynorequired = db_query_first_cell( "select nopmsidrequired from company_esi where id = '$rrow[companyid]'" );
    if( $companynorequired )
        return 0;
    return 1;
}

function hasScheduleAccess( $id, $code )
{
    return db_query_first_cell( "select userid from user_to_class where userid = '$id' and code = '$code' " );
}
function isCodeRetired( $code )
{
    return db_query_first_cell( "select isretired from esioptionvalues where shortname = '$code' " );
}

function getCompanyLink( $companyid )
{
return "<a href='viewcompany.php?id=$companyid'>".getCompanyName( $companyid ) . "</a>" ;
}
function getClassLink( $classid )
{
return "<a href='class_detail.php?id=$classid'>".$classid. "</a>" ;
}

function getEmployeeLog( $val )
{
return db_query_first_cell( "select response from employeeslog where id = '$val' order by dateadded desc limit 1" );
}

$tmpclassinfocache = array();
function getClassInfo( $classid ) 
{
    global $tmpclassinfocache;
    if( isset( $tmpclassinfocache[$classid] ) ) return $tmpclassinfocache[$classid];
    $val = db_query_rows( "select * from class_info where classid = '$classid' and deleted = 0", "name" );
    $tmpclassinfocache[$classid] = $val;
    return $val;
}
function getClassInfoValue( $classid, $name ) 
{
return db_query_first_cell( "select value from class_info where classid = '$classid' and name = '$name' and deleted = 0" );
}

function outputShippingRow( $classinfo, $sizes, $rownum, $name, $default = "", $values = array(), $notr = false, $extrainputname = "" )
{
    global $shippingcomment, $classid, $id, $leftstr;
    if( !$classid ) $classid = $id;
    $size = isset( $sizes[$name] )?"style='width: {$sizes[$name]}px;'":"";
    
    $val = count( $classinfo ) > 2 ?$classinfo[$name]["value"]:$default; // changed to 2 for jumping
    if( isset($classinfo[$name]["value"]) && $classinfo[$name]["value"] == "jumping" )
        $val = "jumping"; // always take this one
    
    
    if( $val == "jumping" )
        $extcolor = "bgcolor='#ddffff'";
    if( !$notr )
        echo( "<tr ><tr  $extcolor><td $leftstr>$name:</td>" );
echo( "<td $extcolor $leftstr><input type='hidden' name='rownums[$rownum]' value=\"{$name}{$extrainputname}\">" );
    if( $name == "Do Not Send to XPO" || $name == "Do Not Send to Birdie" || $name == "Sent with Admiral" || $name == "Sent with Birdie" || $name == "Sent with XPO" )
    {
        $sel = $val?"CHECKED":"";
        echo( "<input name='shippingvals[$rownum]' $sel type='checkbox' value='1' >" );
    }
    else if( count( $values )  )
{
        echo( "<select $size name='shippingvals[$rownum]'>" );
        echo( "<option value=''>Please Choose</option>" );
        foreach( $values as $id=>$v )
        { 
            $sel = $val==$id?"SELECTED":"";
            echo( "<option value='$id' $sel>$v</option>" );
        }
        echo( "</select>" );
}
    else
    {
        if( strpos( $name, " Date" ) !== false )
        {
            echo printdates2( "shippingvals[$rownum]", $val );
        }
        else
        {
            if( strpos( $name, " State" ) !== false )
                $size .= " size='2' maxlength='2'";
            if( strpos( $name, " Zip" ) !== false )
                $size .= " size='5' maxlength='5'";
            if( strpos( $name, " Phone" ) !== false )
                $size .= " size='12' maxlength='12'";
            echo( "<input id=\"{$classid}_{$name}\" $size type='text' name='shippingvals[$rownum]' value=\"$val\">" );
        }
    }

    if( $val == "jumping" )
    {
        echo( "<br><font color='red'>Bags jumped from class <a href='class_info.php?id=" . $classinfo["jumpingfrom"]["value"] . "' target='_blank'>#" . $classinfo["jumpingfrom"]["value"] . "</a></font>" );
    }

    if( $name == "Return Delivery Name" )
    {
        $jumpedto = getJumpingTo( $classid );
        if( $jumpedto )
        {
            echo( "<a href='class_detail.php?id=$jumpedto'>Class: #$jumpedto</a><br>" );
        }
        
    }
    if( $name == "Return Delivery Address" )
    {
        $classrow = getClassRow( $classid );
        $companyrow = getCompanyRow( $classrow["companyid"] );
        $sd = date( "Y-m-d", strtotime( $classrow["startdate"] . " + 2 days"   ));
        $ed = date( "Y-m-d", strtotime( $classrow["startdate"] . " + 14 days" ) );
        $ext = " and class.id not in ( select value from class_info where name = 'jumpingfrom' and deleted = 0 )";
        $sql = "Select schoolcode, companyname, class.* from class, company_esi where class.companyid = company_esi.id and startdate > '$sd' and startdate < '$ed 23:59:59' and  isconferenceroom = 0 and training_zip = '$classrow[training_zip]'  and ( birdieid is null or birdieid = '' ) and iscorp = '$companyrow[iscorp]' and canceldate is null $ext and accepted = 1 order by borough, startdate" ;
        $suggestions = db_query_rows( $sql );

        echo( "<br>Use Suggested Class For Jumping:<Br> <select class='jumparoo' onChange='fillReturnAddress( this, this.options[this.selectedIndex].value, {$classrow[id]} )' name='jumpid[{$classrow[id]}]' style='width: 200px' ><option value=''></option>" );
            $jumpingfrom = db_query_first( "select company_esi.*, class.id as classid, startdate from class, company_esi, class_info where class.id = class_info.classid and value = '{$classrow[id]}' and company_esi.id = companyid and class_info.deleted = 0" );
            if( $jumpingfrom["classid"] )
            {
                echo( "<option selected value='$jumpingfrom[classid]'>" . $jumpingfrom["schoolcode"] . " - " . getFormattedDateWTime( $jumpingfrom["startdate"] ) . " - #$jumpingfrom[classid] </option>" );
            }
            if( count( $suggestions ) )
            {
                echo ("<optgroup label='Classes in Zip'>" );
                foreach( $suggestions as $s )
                {
                    
                    echo( "<option value='$s[id]'>" . $s["schoolcode"] . " - " . getFormattedDateWTime( $s["startdate"] ) . " - #$s[id] </option>" );
                }
                echo ("</optgroup>" );
            }
            

            $sql = "Select schoolcode, companyname, class.* from class, company_esi where class.companyid = company_esi.id and startdate > '$sd' and startdate < '$ed 23:59:59' and  isconferenceroom = 0 and borough = '$companyrow[borough]' and ( birdieid is null or birdieid = '' ) and iscorp = '$companyrow[iscorp]'  and canceldate is null and accepted = 1  order by borough, startdate" ;
            $suggestions = db_query_rows( $sql );
            if( count( $suggestions ) )
            {
                echo ("<optgroup label='Classes in Borough'>" );
                foreach( $suggestions as $s )
                {
                    
                    echo( "<option value='$s[id]'>" . $s["schoolcode"] . " - " . getFormattedDateWTime( $s["startdate"] ) . " - #$s[id] </option>" );
                }
                echo ("</optgroup>" );
            }


        echo ("<optgroup label='All Classes'>" );
            // just display it all
        $sql = "Select schoolcode, companyname, class.* from class, company_esi where class.companyid = company_esi.id and startdate > '$sd' and startdate < '$ed 23:59:59' and  isconferenceroom = 0 and ( birdieid is null or birdieid = '' ) and iscorp = '$companyrow[iscorp]'  and canceldate is null and accepted = 1  order by borough, startdate" ;
        $suggestions = db_query_rows( $sql );
        
        if( count( $suggestions ) )
        {
            foreach( $suggestions as $s )
            {
                
                echo( "<option value='$s[id]'>" . $s["schoolcode"] . " - " . getFormattedDateWTime( $s["startdate"] ) . " - #$s[id] </option>" );
            }
        }
        echo ("</optgroup>" );

        echo "</select>" ;
            
            
    }
    
    echo( "{$shippingcomment}</td>\n" );
    if( !$notr )
        echo( "</tr>\n" );
}

function getSchoolCode( $sid )
{
    return db_query_first_cell( "Select schoolcode from company_esi where id = $sid" );
}

function getTrainingAddress( $classrow )
{
    $val = $classrow["training_location"];
    if( $classrow["training_room_number"] )
    {
        $val .= ", $classrow[training_room_number]";
    }
    if( $classrow["training_zip"] || $classrow["training_city"] )
    {
        $val .= ", $classrow[training_city], $classrow[training_state] $classrow[training_zip]";
    }
    return $val;
}

function getAppUploadValues( $uploadid )
{
    return db_query_array( "select name, value from appuploaddata where uploadid = '$uploadid'", "name", "value" );
}

function getAppUploadValue( $uploadid, $name )
{
    return db_query_first_cell( "select value from appuploaddata where uploadid = '$uploadid' and name = '$name'" );
}

function getAppDrillRows( $uploadid )
{
    return db_query_rows( "select * from drilluploaddata where uploadid = '$uploadid'", "stepnumber" );
}

function getAppServiceCallRows( $uploadid )
{
    return db_query_rows( "select * from scuploaddata where uploadid = '$uploadid'");
}

function getAppServiceCallDetailRows( $dataid )
{
    return db_query_array( "select * from scuploaddetail where dataid = '$dataid'", "name", "value" );
}

function getOrCreateEmailId( $email )
{
$r = db_query_first_cell( "select id from emailtoid where email = '" . escMe( $email ) . "'" );
if( !$r )
{
    $r = db_query_insert_id( "insert into emailtoid ( email ) values ( '" . escMe( $email ) . "' )" ); 
}
return $r;
}

function getEmailFromId( $id )
{
$r = db_query_first_cell( "select email from emailtoid where id = '" . escMe( $id ) . "'" );
return $r;
}

function getExtensionDate( $dt ) // pass in the 2020 date
{
    $dt = date( "Y-m-d", strtotime( $dt ) );
    if( $dt >= '2020-03-01' && $dt < '2020-07-01' )
{
    return date( "Y-m-d", strtotime( $dt . " + 4 months" ) );
}
    if( $dt >= '2020-07-01' && $dt < '2020-08-01' )
{
    return date( "Y-m-d", strtotime( $dt . " + 3 months" ) );
}
    if( $dt >= '2020-08-01' && $dt < '2020-09-01' )
{
    $dt = date( "Y-m-d", strtotime( "October 31, 2020" ) );
    return $dt;
}
    return "";
}

function getExtensionDateByTrainingDate( $dt ) // pass in the 2020 date
{
    $dt = date( "Y-m-d", strtotime( $dt ) );
    if( $dt >= '2018-03-01' && $dt < '2018-07-01' )
{
    $dt = date( "Y-m-d", strtotime( $dt . " + 4 months" ) );
    return date( "Y-m-d", strtotime( $dt . " + 2 years" ) );
}
    if( $dt >= '2018-07-01' && $dt < '2018-08-01' )
{
    $dt = date( "Y-m-d", strtotime( $dt . " + 3 months" ) );
    return date( "Y-m-d", strtotime( $dt . " + 2 years" ) );
}
    if( $dt >= '2018-08-01' && $dt < '2018-09-01' )
{
    $dt = date( "Y-m-d", strtotime( "October 31, 2018" ) );
    return date( "Y-m-d", strtotime( $dt . " + 2 years" ) );
}
    return "";
}

$all_emailtypes = db_query_array("select value from esioptionvalues where datatype='emaillist' order by value", "value", "value");// array('AED Products Only','AED Products and Training','Training Only' );
$instpes = array();
$instypes["0"] = "Never ";
$instypes["1"] = "1/year ";
$instypes["2"] = "2/year ";
$instypes["3"] = "3/year ";
$instypes["4"] = "Supply replacement Only ";

// $maxdate = db_query_first_cell( "select max( dateadded ) from covidquestions where userid = '$session_userid'" );
// $goafter = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
// //echo( "max: " . $maxdate );
// //exit;
// if( $maxdate < date( "Y-m-d" ) && !count( $_POST ) && strpos( $goafter, "covid") === false && strpos( $goafter, "login.php") === false  && !$nologinrequired && ( $thisusersrow["overalladmin"] || $thisusersrow["usertype"] == "trainer" ) )
//     {
// $goafter = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
// session_register( 'goafter' );
// Header( "Location: covidchecklist.php?forlogin=1" );
// exit;
//     }

$bannedzips = db_query_array( "select * from badzips", "zip", "zip"  );
$bannedschoolids = db_query_array( "select * from badschoolids", "schoolid", "color"  );

?>
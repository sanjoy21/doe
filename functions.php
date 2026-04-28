<?php
// use PHPMailer\PHPMailer\PHPMailer;
// use PHPMailer\PHPMailer\Exception;
// if( $GLOBALS["SERVER_PORT"] != 443 )
// {
//     $newurl = "https://".$GLOBALS["SERVER_NAME"]. $GLOBALS["SCRIPT_NAME"];
//     Header( "Location: $newurl" );
//     exit;
// }
//$notavail = array( "2006-09-01", "2006-08-31", "2006-11-07", "2006-11-06", "2006-11-08" );
// session_start();
// Fix for removed Session functions
define( 'MAXAVAIL', 7 );
define( 'PROSPECTS', 2 );
define( 'TRAININGSITES', 3 );
define( 'AGING', 4 );
define( 'DFTA', 4 ); // just in case

define( 'RECERTPERSON', 15587 );

// Custom Code by Sanjoy Dey
define('URL_WITHOUT_SUBDOMAIN', 'emergencyskills.com');
define('SUB_DOE', 'doe');
// Custom Code Ends Here

define( 'EMERGENCYSKILLS', '2858' );
define( 'PARKSCAMPUS', '3565' );
define( 'PARKSMAINCOMPANY', '13075' );
$noreportsorcalendar = array( 239, 4782 );

// Initialize session_iscorp if not set
if( !isset( $_SESSION['session_iscorp'] ) )
{
//    print_r( $_SERVER );
if( $_SERVER["SERVER_NAME"] == SUB_DOE. "." . URL_WITHOUT_SUBDOMAIN  )
$_SESSION['session_iscorp'] = 0;
else if( $_SERVER["SERVER_NAME"] == "prospects." . URL_WITHOUT_SUBDOMAIN )
$_SESSION['session_iscorp'] = 2;
else if( $_SERVER["SERVER_NAME"] == "training." . URL_WITHOUT_SUBDOMAIN  )
$_SESSION['session_iscorp'] = 3;
else if( $_SERVER["SERVER_NAME"] == "dfta." . URL_WITHOUT_SUBDOMAIN  )
$_SESSION['session_iscorp'] = 4;
else
$_SESSION['session_iscorp'] = 1;
}

// Set global variable for backward compatibility
$session_iscorp = &$_SESSION['session_iscorp'];

if( $_SERVER["SERVER_NAME"] == "doetest." . URL_WITHOUT_SUBDOMAIN  )
{
    function full_url($s)
    {
        $ssl = (!empty($s['HTTPS']) && $s['HTTPS'] == 'on') ? true:false;
        $sp = strtolower($s['SERVER_PROTOCOL']);
        $protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
        $port = $s['SERVER_PORT'];
        $port = ((!$ssl && $port=='80') || ($ssl && $port=='443')) ? '' : ':'.$port;
        $host = isset($s['HTTP_X_FORWARDED_HOST']) ? $s['HTTP_X_FORWARDED_HOST'] : (isset($s['HTTP_HOST']) ? $s['HTTP_HOST'] : $s['SERVER_NAME']);
        return "http" . '://' . $host . $port . $s['REQUEST_URI'];
    }

    Header( "Location: ".full_url( $_SERVER ) );
    exit;
}

function getEndDateStr( $str )
{
    return ($str? "- $str":"");
}

function getSchoolStr( $word = "school", $override = -1 )
{
global $session_iscorp;
    if( $override > -1 )
        $theval = $override;
    else
        $theval = $session_iscorp;
    if( $theval == 2 )
    {
        if( $word == "school" )
            return "Prospect";
        if( $word == "School" )
            return "Prospect";
        if( $word == "School Address" )
            return "Building Entrance";
        if( $word == "Host School / Organization" )
            return "Location Name";
        if( $word == "Location" )
            return "Headquarters";
        if( $word == "the DOE" )
            return "ESI";
        if( $word == "School Entrance" )
            return "Building Entrance";
        if( $word == "Training Location" )
            return "Training Address";
        if( $word == "Schools" )
            return "Prospects";
        if( $word == "Campus" )
            return "Group";
        if( $word == "File Number" )
            return "Employee Number";
        if( $word == "Campuses" )
            return "Groups";
    }
    else if( $theval == TRAININGSITES )
    {
        if( $word == "school" )
            return "Prospect";
        if( $word == "School" )
            return "Training Site";
        if( $word == "School Address" )
            return "Building Entrance";
        if( $word == "Host School / Organization" )
            return "Location Name";
        if( $word == "Location" )
            return "Headquarters";
        if( $word == "the DOE" )
            return "ESI";
        if( $word == "School Entrance" )
            return "Building Entrance";
        if( $word == "Training Location" )
            return "Training Address";
        if( $word == "Schools" )
            return "Training Sites";
        if( $word == "Campus" )
            return "Group";
        if( $word == "File Number" )
            return "Employee Number";
        if( $word == "Campuses" )
            return "Groups";
    }
    else if( $theval == AGING )
    {
        if( $word == "school" )
            return "Senior Center";
        if( $word == "School" )
            return "Senior Center";
        if( $word == "School Address" )
            return "Building Entrance";
        if( $word == "Host School / Organization" )
            return "Location Name";
        if( $word == "Location" )
            return "Headquarters";
        if( $word == "the DOE" )
            return "ESI";
        if( $word == "School Entrance" )
            return "Building Entrance";
        if( $word == "Training Location" )
            return "Training Address";
        if( $word == "Schools" )
            return "Training Sites";
        if( $word == "Campus" )
            return "Group";
        if( $word == "File Number" )
            return "Employee Number";
        if( $word == "Campuses" )
            return "Groups";
    }
else if( $theval )
    {
        if( $word == "school" )
            return "Company";
        if( $word == "School" )
            return "Company";
        if( $word == "Host School / Organization" )
            return "Location Name";
        if( $word == "Location" )
            return "Headquarters";
        if( $word == "the DOE" )
            return "ESI";
        if( $word == "School Address" )
            return "Building Entrance";
        if( $word == "School Entrance" )
            return "Building Entrance";
        if( $word == "Training Location" )
            return "Training Address";
        if( $word == "Schools" )
            return "Companies";
        if( $word == "Campus" )
            return "Group";
        if( $word == "File Number" )
            return "Employee Number";
        if( $word == "Campuses" )
            return "Groups";
        if( $word == "PMS ID" )
            $word = "Kronos File Number";
    }
    if( $word == "Training Location" )
        return "Training Address";
    if( $word == "PMS ID" )
        $word = "Payroll Reference #";
    if( $word == "PMS IDs" )
        $word = "Payroll Reference #s";
    return $word;
}

function get_companyid($session_id) {
  $sql = "select companyid from user where id = '$session_id'";
  $companyid = db_query_first_cell($sql);

  if ($companyid == 0) $companyid = '%';
  return $companyid;
}

function addAttendee( $datemodified, $classid, $responderid, $position, $paid = 0, $attended = 0 )
{
global $session_userid, $individual, $timeslot;
    $sessionid = $session_userid;
    $addeddate = 'now()';
    
    if( !$responderid )
        return;

    $resp = db_query_first_cell( "select responderid from responder_to_class where classid = $classid and responderid = $responderid" );
    if( !$resp )
    {
        db_query( "insert into responder_to_class ( classid, responderid, position, individual, ispaid, attended, timeslot, sessionid, dateadded, dateupdated ) values ( '$classid', '$responderid', '$position', '$individual', '$paid', '$attended', '$timeslot', '$sessionid', $addeddate, $datemodified )" );
    }
    else
    {

        db_query( "update responder_to_class set dateupdated = $datemodified, position = '$position', ispaid = '$paid', timeslot = '$timeslot', attended = '$attended' where responderid = $responderid and classid = $classid limit 1" );
    }
}


function clearNonUpdated( $datesaved, $classid )
{
    db_query( "delete from responder_to_class where dateupdated < $datesaved and classid = $classid " );
}


function get_attendees( $classid, $completedonly = false, $attendedonly = false )
{
    $ext = "";
    if( $attendedonly ) $ext = " and attended = 1" ;
if( $completedonly )
    return db_query_rows( "select sessionid, dateadded, timeslot, ispaid, position, rtc.responderid, individual, rtd.id as completed, attended from responder_to_class rtc, responder_training_dates rtd where rtd.classid = rtc.classid and rtd.responderid = rtc.responderid and rtc.classid = $classid $ext order by position" );
    else
    return db_query_rows( "select sessionid, dateadded, timeslot, ispaid, position, rtc.responderid, individual, rtd.id as completed, attended from responder_to_class rtc left join responder_training_dates rtd  on rtd.classid = rtc.classid and rtd.responderid = rtc.responderid   where rtc.classid = $classid $ext order by position", "position" );
}

function getattendeeidbyposition( $classid, $position )
{
//    echo( "select position, responderid, individual from responder_to_class where classid = $classid order by position" );
    return db_query_first_cell( "select responderid from responder_to_class where classid = $classid and position = $position" );
}

function getEquipmentStatus( $equipmentid )
{
    return db_query_first( "select * from equipmentstatus where equipmentid = $equipmentid order by statusdate desc limit 1" );
}

function addEquipmentStatus( $equipmentid, $status, $bag = "", $classid = "" )
{
    global $session_userid;
    db_query( "update equipmentstatus set iscurrent = 0 where equipmentid = $equipmentid" );
    db_query( " insert into equipmentstatus( equipmentid, status, bag, classid, statusupdatedby, statusdate, iscurrent ) values ( '$equipmentid', '" . mysqli_real_escape_string($GLOBALS['link'], $status ) . "', '$bag', '$classid', '$session_userid', now(), 1 )" );
}

function isTrainerAssigned( $classrow, $trainerid )
{
    if( $classrow["tcfacultyid"] == $trainerid ) return true;
    return db_query_first_cell( "select count(*) from trainer_to_class where trainerid = $trainerid and classid = $classrow[id]" );
}

function getTrainers( $classid, $unconfirmedonly = false )
{
    if( $unconfirmedonly )
        $ext = " and trainerconfirmeddate is null";
    
    return db_query_rows( "select * from trainer_to_class, user where classid = $classid and user.id = trainerid $ext ", "trainerid" );
}

function getOtherNotifies( $drillid, $dt )
{
    $arr = db_query_array( "select notifydate from drillnotifies where drillid = '$drillid' and notifydate <> '$dt'", "notifydate", "notifydate" );

    $str = "";
    foreach( $arr as  $a )
        {
            $str .= ", " . getFormattedDateWTime( $a );
        }
    return $str;
}

function get_attendee($id) {
  $sql = "select responderid, firstname, lastname, clientid, filenumber, pmsid, title, email, dayphone from responders_esi where responderid = '$id'";
  return db_query_first($sql);
}

function get_sql_insert($table, $keys, $values, $primary = "id") {

//     print_r( $values );
//     exit;
  foreach ($values as $key=>$val) {
      if (in_array($key, $keys)) {
//          echo( "got $key<br>");
          if ($values[$key] === "now()") {
//  echo( "hm ".$values[$key]."<br>" );
              $ins_values[] = "now()";
          } else {
              $ins_values[] = "'".mysqli_real_escape_string($GLOBALS['link'], stripslashes( $values[$key] ))."'";
          }
          $ins_keys[] = $key;
      }
  }
  $keys_sql = "(";
  $keys_sql .= implode(",", $ins_keys);
  $keys_sql .= ")";

  $values_sql = "VALUES (";
  $values_sql .= implode(",", $ins_values);
  $values_sql .= ")";

  $sql = "INSERT INTO $table $keys_sql $values_sql";
  return $sql;
  //  echo "<br>$sql";

}

function get_sql_update($table, $keys, $values, $primary, $checkboxes) {

  //  $sql = "SELECT $primary FROM $table WHERE $primary = '$values[$primary]'";
  //  $row = $db->get_row($sql);
  //  print_r($keys);
  //  print_r($values);
$update_arr = array();
 foreach ($values as $key=>$val) {
    if (in_array($key, $keys)) {
      if ($values[$key] == "now()") {
        $update_arr[] .= "$key = now()";
      } else {
        $update_arr[] .= "$key = '".$values[$key]."'";
      }
    }
  }
 if( is_array( $checkboxes ) ) 
     foreach( $checkboxes as $c )
     {
         if( !isset( $values[$c] ) )
         {
             $update_arr[] .= "$c = '0'";
         }
     }
  $update_sql = implode(",", $update_arr);

  $sql = "UPDATE $table SET $update_sql WHERE $primary = '$values[$primary]'";

  return $sql;
}

function get_day_cell($classes_this_month, $class, $starttime, $month, $day_of_month, $year, $maxattendees, $numtrainers, $quicksched=false) {
    global $companyid, $notavail, $specialadmin, $okavail, $peakdates, $crow, $bannedzips, $bannedschoolids, $opendates, $session_id;
//    echo( $session_id );
  //  echo $class;
//  $thetm = mktime(0, 0, 0, $month, $day_of_month, $year);
  $day_of_week = date("w", $thetm);
  list( $day_full, $toomanyresp, $summer ) = isfull( $classes_this_month, $month, $day_of_month, $year, $companyid  );
//   if( $day_of_month == 7 && $month == 9 )
//   {
//       echo( $day_full . ", " . $toomanyresp . ", " . $summer );
//   }
  if( $companyid == 14801 )
  {
          $anyconference = db_query_first_cell( "select id from class where isconferenceroom = 1 and startdate like '$year-$month-$day_of_month%'" );
//          echo( "select id from class where isconferenceroom = 1 and startdate like '$year-$month-$day_of_month%'<br>" );
          if( $anyconference )
              $day_full = true;
  }


  //
  $badzip = false;
  
  if( in_array( $crow["zip"], $bannedzips ) && strtotime( "2020-12-01" ) > strtotime( "$year-$month-$day_of_month" ) )
      {
  $badzip = true;
      }
  if( $bannedschoolids[$crow["id"]] && strtotime( "2020-12-01" ) > strtotime( "$year-$month-$day_of_month" ) )
      {
  $badzip = true;
      }

  
  if( $summer && !isOverallAdmin() )
  {
    echo "<td valign='top' width='20' align='center' bgcolor='#ccffcc'><span class='small'><a href='#' onClick='javascript:window.open( \"summerpopup.php\", \"_blank\", \"width=400,height=400\" ); return false' >$day_of_month</a></span>$note</td>";
  }
  else if( $toomanyresp && !isOverallAdmin() )
  {
    echo "<td valign='top' width='20' align='center' bgcolor='#ccffcc'><span class='small'><a href='#' onClick='alert( \"Please contact Emergency Skills directly to schedule a class on Saturday.\" ); return false' >$day_of_month</a></span>$note</td>";
  }
  else if (!$day_full || isOverallAdmin() || $session_id == 14088 ) { //        alt_phone$day_of_week >= 1 && $day_of_week <= 5 &&
      $ext = "";


      if( $badzip )
  {
      if( isOverallAdmin() )
  $ext= "onclick='return confirm( \"Due to the school closings in this zip code, training is not available. Are you sure you want to schedule a class here?\" ); ' ";
      else
  $ext= "onclick='alert( \"Due to the school closings in this zip code, you will be unable to schedule training at this time. Please call ESI for assistance. 212-564-6833\" ); return false' ";
  }
      
      if( $companyid == EMERGENCYSKILLS )
      {
          $otherclasses = db_query_first_cell( "select count(*) from class where companyid = $companyid and startdate like '$year-$month-$day_of_month%' and canceldate is null" );
          if( $otherclasses )
          {
              $times = db_query_first_cell( "select group_concat( date_format( startdate, '%h:%i %p' ) ) from class where companyid = $companyid and startdate like '$year-$month-$day_of_month%'" );
              $ext= "onclick='return confirm( \"There are already $otherclasses classes at ESI this day ($times). Are you sure you want to schedule this class?\" )' ";
          }
      }
      $qs = $quicksched?"&quicksched=1":"";
      $qsoc = !$badzip&&$quicksched?"onClick='return confirm( \"Are you sure you want to schedule this class?\" )' ":"";
      
      $bgcolor = '#ccffcc';
      if( $summer || $day_full || $toomanyresp )
          $bgcolor = "#ffffff";
      echo "<td valign='top' width='20' align='center' bgcolor='$bgcolor'><span class='small'><a $qsoc href='schedule_class2_inner.php?companyid=$companyid&c=$class&s=$starttime&m=$month&d=$day_of_month&yr=$year&maxattendees=$maxattendees&numtrainers=$numtrainers$qs' $ext >$day_of_month</a></span>$note</td>";
  } else {
      echo "<td valign='top' width='20' align='center'><span class='small'>$day_of_month</span>$note</td>";
  }
}

function show_calendar($classes_this_month, $class, $starttime, $month, $year, $maxattendees, $numtrainers, $quicksched=false) {
  $day = 1;
  $day_of_week = date("w", mktime(0, 0, 0, $month, $day, $year));
  $month_and_year = date("M Y", mktime(0, 0, 0, $month, $day, $year));

?>

<!--start calendar 1-->
<div align="center"><span class="copy"><strong><?php echo $month_and_year?></strong></span><br>
<img src="images/dotclear.gif" height="6"><br>
<table cellpadding="2" cellspacing="1" border="0" bgcolor="#5a179e">
<tr bgcolor="#ffffff" height="20">
<td valign="top" align="center" width="20" align="center" bgcolor="#1c0792"><span class="white"><strong>S</strong></span></span></td>
<td valign="top" align="center" width="20" align="center" bgcolor="#1c0792"><span class="white"><strong>M</strong></span></span></td>
<td valign="top" align="center" width="20" align="center" bgcolor="#1c0792"><span class="white"><strong>T</strong></span></span></td>
<td valign="top" align="center" width="20" align="center" bgcolor="#1c0792"><span class="white"><strong>W</strong></span></span></td>
<td valign="top" align="center" width="20" align="center" bgcolor="#1c0792"><span class="white"><strong>T</strong></span></span></td>
<td valign="top" align="center" width="20" align="center" bgcolor="#1c0792"><span class="white"><strong>F</strong></span></span></td>
<td valign="top" align="center" width="20" align="center" bgcolor="#1c0792"><span class="white"><strong>S</strong></span></span></td>
</tr>

<?php

// FIRST WEEK OF THE MONTH
echo "<tr bgcolor='#ffffff' height='20'>";
// Loop through each day of the week
for ($i = 0; $i <= 6; $i++) {
  if ($i == $day_of_week) {
    echo get_day_cell($classes_this_month, $class, $starttime, $month, 1, $year, $maxattendees, $numtrainers, $quicksched);
    break;
  } else {
    echo "<td valign='top' width='20' align='center' bgcolor='#cccccc'></span></td>";
  }
}

$i++;
 $day_of_month = 2;
for ($j = $i; $j <= 6; $j++, $day_of_month++) {
  //  echo $day_of_month;
  echo get_day_cell($classes_this_month, $class, $starttime, $month, $day_of_month, $year, $maxattendees, $numtrainers, $quicksched);
}  
echo "</tr>";

// NEXT FOUR WEEKS OF THE MONTH
for ($week_of_month = 2; $week_of_month <= 6; $week_of_month++) {
  echo "<tr bgcolor='#ffffff' height='20'>";
  for ($j = 1; $j <= 7; $j++) {
    $this_month = date("n", mktime(0, 0, 0, $month, $day_of_month, $year));
    if ($this_month == $month) {
      echo get_day_cell($classes_this_month, $class, $starttime, $month, $day_of_month, $year, $maxattendees, $numtrainers, $quicksched);
    } else {
      echo "<td valign='top' width='20' align='center' bgcolor='#cccccc'></span></td>";
    }      
    $day_of_month++;
  }  
  echo "</tr>";
}

?>

</table>

</div>
<!--end calendar 1-->

<?php

}

function isfull($classes_this_month, $month, $day_of_month, $year, $companyid )
{
    global $notavail, $starttime, $thisusersrow, $peakdates, $opendates;
    $day_of_week = date("w", mktime(0, 0, 0, $month, $day_of_month, $year));
    $theday = date("Y-m-d", mktime(0, 0, 0, $month, $day_of_month, $year));


    $crow = getCompanyRow( $companyid );
//        $sql =( "select count(*) from class c, company_esi ci where c.companyid = ci.id and startdate like '".date("Y-m-d", $thetm2 ) . "%' and canceldate is null and iscorp = 0 " );
//select count(*) from class where startdate like '$theday%' and deleted = 0
 $numthisday = db_query_first_cell( "select count(*) from class c, company_esi ci where c.companyid = ci.id and startdate like '$theday%' and canceldate is null and iscorp = 0" );

    $day_full = false;
    // if( ($crow["borough"] == "Manhattan"|| $crow["borough"] == "Bronx") && ( $theday == "2022-06-07" ||$theday == "2022-06-09") )
    // {
    //     $nummathisday = db_query_first_cell( "select count(*) from class cl, company_esi c where startdate like '$theday%' and cl.deleted = 0 and cl.companyid = c.id and borough = '$crow[borough]'" );
    //     if( $nummathisday < 3 )
    //         return array( false, false, false );
    
    // }
    
    if( $crow["borough"] == "Staten Island" )
{
    $numsithisday = db_query_first_cell( "select count(*) from class cl, company_esi c where startdate like '$theday%' and cl.deleted = 0 and cl.companyid = c.id and borough = 'Staten Island'" );
    if( $numsithisday )
{
    $day_full = true;
    file_put_contents( "/tmp/rc", "day of week is $theday, $day_of_week,  full from because already SI class: $numthisday \n", FILE_APPEND );
}
}

if(  !isOverallAdmin() && $crow["borough"] == "Staten Island" && $day_of_week == 5)
{
	$day_full = true; // This is the logic for Friday for Staten Island, added by Sanjoy Dey.
}

if( $day_of_week == 6 && $numthisday >= 5 ) // Sanjoy- This is the logic for Saturday. Max 5 class can be scheduled.
{
    $day_full = true;
    file_put_contents( "/tmp/rc", "day of week is $theday, $day_of_week,  saturdays can only have 5: $numthisday \n", FILE_APPEND );
}

    
    if( $numthisday >= MAXAVAIL && !$opendates[$theday] )
{
    $day_full = true;
    file_put_contents( "/tmp/rc", "day of week is $theday, $day_of_week,  full from because too many classes already: $numthisday \n", FILE_APPEND );
}

    $thetm = mktime(0, 0, 0, $month, $day_of_month, $year);
    $thetm2 = strtotime( date("Y-m-d", $thetm ) . " " . $starttime );

    // removed this restriction on 3/3/2021
    // $sql =( "select id from class where companyid = $companyid and startdate = '".date("Y-m-d H:i:s", $thetm2 ) . "'" );
    // $any = db_query_first_cell( $sql );
    // if($any)
    //     $day_full = true;
    
    $mdy = date( "Y-m-d", strtotime( $year . "-" . $month . "-" . $day_of_month ) );
    if( $companyid == 12317 && !$any )
    {
        $sql =( "select id from class where isconferenceroom = 1 and startdate like '".date("Y-m-d", $thetm2 ) . "%'" );
//    echo( $sql . " , $mdy, $starttime<br>");
        $any = db_query_first_cell( $sql );
        if($any)
        {
            $day_full = true;
        }
    }
    //    file_put_contents( "/tmp/rc", "1day of week is $theday, $day_of_week,  full: $day_full \n", FILE_APPEND );
    if( !$crow["iscorp"] )
    {
        if( !$okavail[$theday] && !$day_full )
        {
            if( ( $starttime ) != "0900" && $day_of_week == 6 )
            {
                $day_full = true;
file_put_contents( "/tmp/rc", "day of week is $theday, $day_of_week, saturdays can only start at 9: $numthisday \n", FILE_APPEND );
            }
        }
    }
    
//sunday
    if( !$day_of_week && !$thisusersrow["scheduleonsundays"] )
{
    file_put_contents( "/tmp/rc", "day of week is $theday, $day_of_week, can't schedule on sundays: $numthisday \n", FILE_APPEND );
        $day_full = true;
}
    
    if( isNotAvail($theday, $borough ) && !in_array($theday, $opendates ) )
{
    file_put_contents( "/tmp/rc", "4day of week is $theday, $day_of_week,  full because of too not avail \n", FILE_APPEND );
    $day_full = true;
}
    $futuredays = $thisusersrow["futuredays"];
    //    $futuredays = 3; // change on 8/6/2021
    
    $t = date( "Y-m-d", mktime( 0,0,0,date( "m" ), date( "d" ) + $futuredays, date( "Y" ) ) ); 
    //    file_put_contents( "/tmp/rc", "2day of week is $theday, $day_of_week,  full: $day_full \n", FILE_APPEND );
    if( $theday <= $t )
{
    $day_full = true;
    file_put_contents( "/tmp/rc", "2day of week is $theday, $day_of_week,  full because of too close (futuredays: $futuredays) : $day_full \n", FILE_APPEND );
}

    
if( !isOverallAdmin() &&  $day_of_week == 6 )
    {
        $sql =( "select count(*) from class c, company_esi ci where c.companyid = ci.id and startdate like '".date("Y-m-d", $thetm2 ) . "%' and canceldate is null and iscorp = 0 " );
        $numclasses = db_query_first_cell( $sql );
$wasday = $day_full;
        $day_full = ($numclasses >= MAXAVAIL) ? true : $day_full; // max on saturday? 
if( !$wasday && $day_full )
    file_put_contents( "/tmp/rc", "day of week is $theday, $day_of_week, saturday is full? $numclasses , " . MAXAVAIL ." \n", FILE_APPEND );
        // file_put_contents( "/tmp/rc", "day of week is $theday, $day_of_week, $numclasses $day_full, " . $notavail[$theday] . ": na, $toomanyresp \n", FILE_APPEND );

    }
//file_put_contents( "/tmp/rc", "day of week is $theday: num already scheduled this day: numthisday: $numclasses numclasses: $numclasses,  full: $day_full, toomany? $toomanyresp \n", FILE_APPEND );
    
    return array( $day_full, $toomanyresp, $summer );
    
}

function get_resched_day_cell($id, $classes_this_month, $class, $starttime, $month, $day_of_month, $year) {
    global $notavail, $peakdates, $opendates;
        //  echo $class;
$crow = getClassRow( $id );
  list( $day_full, $toomanyresp, $summer ) = isfull( $classes_this_month, $month, $day_of_month, $year, $crow["companyid"]  );
  
  if (!$day_full || isOverallAdmin()) {
      $bgcolor = "#CCFFCC";
      if( $day_full )$bgcolor = "#ffffff";
    echo "<td valign='top' width='20' align='center' bgcolor='$bgcolor'><span class='small'><a href='class_edit.php?id=$id&c=$class&s=$starttime&m=$month&d=$day_of_month&yr=$year'>$day_of_month</a></span></td>";
  } else {
    echo "<td valign='top' width='20' align='center'><span class='small'>$day_of_month</span></td>";
  }
}

function show_resched_calendar($id, $classes_this_month, $class, $starttime, $month, $year) {
  $day = 1;
  $day_of_week = date("w", mktime(0, 0, 0, $month, $day, $year));
  $month_and_year = date("M Y", mktime(0, 0, 0, $month, $day, $year));

?>

<!--start calendar 1-->
<div align="center"><span class="copy"><strong><?php echo $month_and_year?></strong></span><br>
<img src="images/dotclear.gif" height="6"><br>
<table cellpadding="2" cellspacing="1" border="0" bgcolor="#5a179e">
<tr bgcolor="#ffffff" height="20">
<td valign="top" align="center" width="20" align="center" bgcolor="#1c0792"><span class="white"><strong>S</strong></span></span></td>
<td valign="top" align="center" width="20" align="center" bgcolor="#1c0792"><span class="white"><strong>M</strong></span></span></td>
<td valign="top" align="center" width="20" align="center" bgcolor="#1c0792"><span class="white"><strong>T</strong></span></span></td>
<td valign="top" align="center" width="20" align="center" bgcolor="#1c0792"><span class="white"><strong>W</strong></span></span></td>
<td valign="top" align="center" width="20" align="center" bgcolor="#1c0792"><span class="white"><strong>T</strong></span></span></td>
<td valign="top" align="center" width="20" align="center" bgcolor="#1c0792"><span class="white"><strong>F</strong></span></span></td>
<td valign="top" align="center" width="20" align="center" bgcolor="#1c0792"><span class="white"><strong>S</strong></span></span></td>
</tr>

<?php

// FIRST WEEK OF THE MONTH
echo "<tr bgcolor='#ffffff' height='20'>";
// Loop through each day of the week
for ($i = 0; $i <= 6; $i++) {
  if ($i == $day_of_week) {
    echo get_resched_day_cell($id, $classes_this_month, $class, $starttime, $month, 1, $year);
    break;
  } else {
    echo "<td valign='top' width='20' align='center' bgcolor='#cccccc'></span></td>";
  }
}

$i++;
 $day_of_month = 2;
for ($j = $i; $j <= 6; $j++, $day_of_month++) {
  //  echo $day_of_month;
  echo get_resched_day_cell($id, $classes_this_month, $class, $starttime, $month, $day_of_month, $year);
}  
echo "</tr>";

// NEXT FOUR WEEKS OF THE MONTH
for ($week_of_month = 2; $week_of_month <= 6; $week_of_month++) {
  echo "<tr bgcolor='#ffffff' height='20'>";
  for ($j = 1; $j <= 7; $j++) {
    $this_month = date("n", mktime(0, 0, 0, $month, $day_of_month, $year));
    if ($this_month == $month) {
      echo get_resched_day_cell($id, $classes_this_month, $class, $starttime, $month, $day_of_month, $year);
    } else {
      echo "<td valign='top' width='20' align='center' bgcolor='#cccccc'></span></td>";
    }      
    $day_of_month++;
  }  
  echo "</tr>";
}

?>

</table>

</div>
<!--end calendar 1-->

<?php

}

function get_big_day_cell($classes, $month, $day_of_month, $year) {
    global $allclass_names, $showcompleted, $session_iscorp, $bannedschoolids, $showcards, $shownames;
$day_of_week = date("w", mktime(0, 0, 0, $month, $day_of_month, $year));
//    print_r( array_keys( $classes ) );

    $lastday = date( "t", mktime( 0,0,0,$month,1,$year ) );
    if( $lastday < $day_of_month )
    {
        $day_of_month = $day_of_month - $lastday;
        $month += 1;
        if( $month == 13 )
        {
            $year++;
            $month=1;
        }
    }
    
    $classesstr = date( "Y-m-d", strtotime( "{$year}-{$month}-{$day_of_month}" ) );
//    echo( "{$year}-{$month}-{$day_of_month}  --- " . $classesstr . "<br>" );
      $iscorpcolor = !$session_iscorp?"#e3eaf2":"#ccffcc";
if( $session_iscorp == 2 || $session_iscorp == 3 ) $iscorpcolor = "#ffffff";
  $bgcolor = (!empty($classes[$classesstr])) ? "$iscorpcolor" : "white";

  $this_month = date("n", mktime(0, 0, 0, $month, $day_of_month, $year));
  if( $this_month != $month )
  {
      $month = date( "m", mktime( 0, 0, 0, $month, $day_of_month, $year));
      $day_of_month = date( "j", mktime(0, 0, 0, $month, $day_of_month-1, $year));
      $bgcolor = "#cccccc";
      $classesstr = date( "Y-m-d", strtotime( "{$year}-{$month}-{$day_of_month}" ) );
      
  }
  
  $cellcolor="";
  if( isOverallAdmin() )
  {
      $adminrows = db_query_rows( "select bgcolor, note from adminnotes where dt <= '$year-$month-$day_of_month' and enddt >= '$year-$month-$day_of_month'" );
      $anote = "";
      foreach( $adminrows as $a )
  {
      $anote .= "<p style='background-color: $a[bgcolor]'>$a[note]</p>";
  }
      $note = db_query_first_cell( "select note from trainernotes where dt = '$year-$month-$day_of_month'" );
      if( $note )
          $note = "<br><font color='red'>$note</font>";

      $snote = db_query_first_cell( "select dt from blockeddates where dt = '$year-$month-$day_of_month'" );
      if( $snote )
          $snote = "<br><font color='red'>BLOCKED</font>";
      $snote2 = db_query_first_cell( "select dt from opendates where dt = '$year-$month-$day_of_month'" );
      if( $snote2 )
  $snote = "<br><font color='red'>OPEN</font>";
      $snote2 = db_query_first_cell( "select dt from peakdates where dt = '$year-$month-$day_of_month'" );
      if( $snote2 )
          $snote .= "<br><font color='red'>PEAK</font>";

      $numthisday = db_query_first_cell( "select count(*) from class, company_esi where startdate like '{$classesstr}%' and class.deleted = 0 and iscorp = 0 and class.companyid = company_esi.id " );
      $dayfull = ($numthisday >= MAXAVAIL || ($numthisday >= 5 && $$day_of_week == 6)) ? true : false; // Sanjoy- MAXAVAIL value is 7

      if( $dayfull )
          $snote .= "<br><font color='red'>FULL</font>";
  }
//   $day_of_week = date("w", mktime(0, 0, 0, $month, $day_of_month, $year));
  echo "<td valign='top' width='20' align='left' bgcolor='$bgcolor'>" ;
  if( mktime( 0,0,0,$month,$day_of_month, $year ) == mktime( 0,0,0 ) )
      echo( "<a name='today'></a>" );
  echo "<span class='small'><b><a  href='classreportdaily.php?fromdate=$year-$month-$day_of_month'>$day_of_month</a></b>";
       
         if( isOverallAdmin() )
     echo "<span class='small'>&nbsp;<b><a  href='mapclasses.php?fromdate=$year-$month-$day_of_month' target=_blank><img style='height: 18px' src='icon_mapmarker.png'></a></b>";
  if( isOverallAdmin() )
  {
      if( $anote )
  echo( "&nbsp;&nbsp; $anote" );
      echo( $snote );
  }
  $tmpcnt = is_array( $classes[$classesstr] )?$classes[$classesstr]:0;
  echo( "<br><nobr><span class='small'>".(is_array( $tmpcnt ) ?count( $tmpcnt ):$tmpcnt)." classes</span></nobr> " );
  if( isOverallAdmin() )
  {
      $d = date( "Y-m-d", strtotime( "$month/$day_of_month/$year" ) );

//    let's get the number of trainers at the locations instead of all trainers
      $traarr = db_query_array( "Select companyid as c, sum( numtrainers ) as t from class where startdate like '$d%' and deleted = 0 group by companyid ", "c", "t" );
      $tra = 0;
      foreach( $traarr as $tmpt )
            $tra = $tra + $tmpt;
  
      echo( "<br><nobr><span class='small'>".$tra." trainers</span></nobr> " ); // Sanjoy- Trainer number display
  }
  if (!empty($classes[$classesstr])) {
    foreach ($classes[$classesstr] as $class) {
        
      $id = $class["id"];
      $time = $class["time"];
      $accepted = $class["accepted"];
      $deleted = $class["deleted"];
      $blendedlearning = $class["blendedlearning"];
      $cancelled = $class["canceldate"];
      $trainers = getTrainers( $class["id"] );
      $crow = getCompanyRow( $class["companyid"] );
      $blended = $blendedlearning?"<br><font color='red'>BLENDED LEARNING</font>":"";
      if( $deleted || $cancelled)
          $fnt = "<font color='red'>";
      else if( strpos($class["confirmationnotes"], "Quick Schedule" ) !== false )
          $fnt = "<font color='purple'>";
      else if( !$accepted )
          $fnt = "<font color='gray'>";
      else if( count( $trainers ) < $class["numtrainers"]  )
          $fnt = "<font color='#ef7502'>";
      else if( $class["isnational"]  )
          $fnt = "<font color='brown'>";
      else
      {
          $col = $crow["iscorp"]?"green":"blue";
if( $crow["iscorp"] == AGING )$col = "maroon";
          $fnt = "<font color='$col'>";
      }
          //class type, start and end time,  school code, trainer (s),
      // print_r( $class );
      // exit;
      if( $class["lasttrainerreqdate"] && isOverallAdmin() )
          $fnt .= "<img src='images/check.png'>";
      
$croom = "";
$endcroom = "";
  if( $class["isconferenceroom"] && isOverallAdmin()) 
{
$croom = "<i>";
$endcroom = "</i>";
}
  if( $shownames && isOverallAdmin()) 
{
    $count = db_query_first_cell( "select count(*) from responder_to_class where classid = '$crow[id]'" );
    if( $count )
{
    $croom = "<b>";
    $endcroom = "</b>";
}
}


  if( ($class["completed"]) && $showcompleted) 
{
$croom .= "<b>";
$endcroom .= " (c)</b>";
}
  if( ($class["ecardssent"]||$class["cardsnotneeded"]) && $showcards) 
{
$croom .= "<b>";
$endcroom .= " (s)</b>";
}
  if( ($class["ebookssent"]||$class["cardsnotneeded"]) && $showcards) 
{
$croom .= "<b>";
$endcroom .= " (b)</b>";
}
      if( $class["iscallconfirmed"]  )
          $redcheck = "<img src='images/redcheck.png'>";
      else
  $redcheck = "";
      if( $class["remote"]  )
          $remotecheck = "<img src='images/laptop.png'>";
      else
  $remotecheck = "";
      
        $nonhsaed = "";
//      if( $crow["iscorp"] ) $class_names = $allclass_names[$crow["iscorp"]]; else $class_names = $allclass_names[0];
      $class_names = $allclass_names[$crow["iscorp"]]; 
      if( $crow["iscorp"] && strpos( $class_names[$class["code"]], "Heartsaver" ) === false ) $nonhsaed = "<font color='red' style=\"font-size: 14px\"><b>*</b></font>"; 
      $end = $class["enddate"]?"- ". $class["enddate"]:"";
      $pval = htmlentities( $class_names[$class["code"]] ) . "<Br>$time $end<br>$class[training_location] ($class[borough]) ";
      if( $bannedschoolids[$class["companyid"]] )
  {
      $color = $bannedschoolids[$class["companyid"]];
      $fcol = $color=="Yellow"?"#fcba03":$color;
      
      $pval .= "<br><font color=\\'$fcol\\'>$color</font> Zone";
  }
      
      $pval .= "<Br>Trainers ($class[numtrainers]): ";
      if( count( $trainers ) )
      {
          $anyt = false;
          foreach( $trainers as $t )
          {
              if( $anyt )
                  $pval .= ", ";
              $anyt = true;
              $pval .= "$t[first_name] $t[last_name]: " . ($t["trainerconfirmeddate"]?"Y":"N");
          }
      }
if( $class["tcfacultyid"] )
    {
$pval .= "<br>TC Faculty: " . getFullname( $class["tcfacultyid"] );
    }
      $otherclasses = db_query_array( "select id from class where companyid = $class[companyid] and startdate > Now() and id <> $class[id]  and canceldate is null", "id", "id" );
      $oth = join( ", ", $otherclasses );
      if( $oth )
      {
          $pval .= "<br>Other classes:  $oth";
      }
      echo "<br>$nonhsaed <a onMouseover=\"popup('$pval', 'white')\" onMouseout=\"kill()\" href='class_detail.php?id=$id'>$fnt$croom$time $redcheck $remotecheck " . getCompanyName( $class["companyid"] )."{$blended}{$endcroom}</a><br><br>\n";
    }
  }
//  echo "</div>";
  echo "$note</span>";
  echo "</td>\n";
}

function show_big_calendar($rows, $month, $year) {
    global $withoutmonthqs;
  $next_year = $year;
  $prev_year = $year;
  //  echo $month;exit;
  $next_month = $month + 1;
  if ($next_month == 13) {
    $next_month = 1;
    $next_year = $next_year + 1;
  }
  $prev_month = $month - 1;
  if ($prev_month == 0) {
    $prev_month = 12;
    $prev_year = $prev_year - 1;
  }

  foreach ($rows as $row) {
      $key = date( "Y-m-d", strtotime( $row["startdate"] ) );
      $classes[$key][] = array(
     'id'=>$row["id"],
     'time'=>$row["starttime"],
     'enddate'=>$row["enddate"],
     'numtrainers'=>$row["numtrainers"],
     'code'=>$row["code"],
     'companyid'=>$row["companyid"],
     'numtrainers'=>$row["numtrainers"],
     'borough'=>$row["borough"],
     'canceldate'=>$row["canceldate"],
     'confirmationnotes'=>$row["confirmationnotes"],
     'training_location'=>$row["training_location"],
     'isnational'=>$row["isnational"],
     'isconferenceroom'=>$row["isconferenceroom"],
     'ecardssent'=>$row["ecardssent"],
     'cardsnotneeded'=>$row["cardsnotneeded"],
     'ebookssent'=>$row["ebookssent"],
     'lasttrainerreqdate'=>$row["lasttrainerreqdate"],
     'iscallconfirmed'=>$row["iscallconfirmed"],
     'deleted'=>$row["deleted"],
     'accepted'=>$row["accepted"],
     'blendedlearning'=>$row["blendedlearning"],
     'remote'=>$row["remote"],
     'tcfacultyid'=>$row["tcfacultyid"],
     'completed'=>db_query_first_cell( "Select count(*) from responder_training_dates where classid = $row[id]" )
     );
  }
  $day = 1;
  $day_of_week = date("w", mktime(0, 0, 0, $month, $day, $year));
  $month_and_year = date("M Y", mktime(0, 0, 0, $month, $day, $year));

?>
<!--start calendar-->
<table cellpadding="2" cellspacing="1" border="0" bgcolor="#5a179e"  class="table3">
<tr height="21">
<td valign="top" colspan="7" align="center" bgcolor="#5a179e" >
<table cellpadding="0" cellspacing="0" border="0" width="650" >
<tr>
<td valign="bottom" width="5%"><a href="calendar.php?<?php echo $withoutmonthqs?>&month=<?php echo $prev_month?>&year=<?php echo $prev_year?>"><img src="images/arrow_left.gif" border="0"></a></td>
<td valign="bottom" align="center" width="90%"><span class="white"><strong><?php echo $month_and_year?></strong></span></td>
<td valign="bottom" align="right" width="5%"><a href="calendar.php?<?php echo $withoutmonthqs?>&month=<?php echo $next_month?>&year=<?php echo $next_year?>"><img src="images/arrow_right.gif" border="0"></a></td>
</tr>
</table>
</td>
</tr>

<tr><td width='92'><span class='white'>Sunday</td>
     <td width='92'><span class='white'>Monday</td>
     <td width='92'><span class='white'>Tuesday</td>
     <td width='92'><span class='white'>Wednesday</td>
     <td width='92'><span class='white'>Thursday</td>
     <td width='92'><span class='white'>Friday</td>
     <td width='92'><span class='white'>Saturday</td>
</tr>     
<?php

// FIRST WEEK OF THE MONTH
echo "<tr bgcolor='#ffffff' height='65'>";
// Loop through each day of the week
for ($i = 0; $i <= 6; $i++) {
  if ($i == $day_of_week) {
    echo get_big_day_cell($classes, $month, 1, $year);
    break;
  } else {
    echo "<td valign='top' width='65' align='center' bgcolor='#cccccc'></span></td>";
  }
}

$i++;
 $day_of_month = 2;
for ($j = $i; $j <= 6; $j++, $day_of_month++) {
  //  echo $day_of_month;
  echo get_big_day_cell($classes, $month, $day_of_month, $year);
  }  
echo "</tr>";

// NEXT FOUR WEEKS OF THE MONTH
for ($week_of_month = 2; $week_of_month <= 6; $week_of_month++) {
  echo "<tr bgcolor='#ffffff' height='65'>";
  for ($j = 1; $j <= 7; $j++) {
      echo get_big_day_cell($classes, $month, $day_of_month, $year);
      $day_of_month++;
  }  
  echo "</tr>";
}

?>
<tr height="21">
<td valign="top" colspan="7" align="center" bgcolor="#5a179e">
<table cellpadding="0" cellspacing="0" border="0" width="650">
<tr>
<td valign="bottom" width="5%"><a href="calendar.php?month=<?php echo $prev_month?>&year=<?php echo $prev_year?>"><img src="images/arrow_left.gif" border="0"></a></td>
<td valign="bottom" align="center" width="90%"><span class="white"><strong><?php echo $month_and_year?></strong></span></td>
<td valign="bottom" align="right" width="5%"><a href="calendar.php?month=<?php echo $next_month?>&year=<?php echo $next_year?>"><img src="images/arrow_right.gif" border="0"></a></td>
</tr>
</table>
</td>
</tr>

</table>

</div>
<!--end calendar 1-->

<?php

    }

function matches( $day, $availrow, $trainerid="", $fullstartdate = "" )
{
    $actualtime = strtotime( $day );
    $sd = $availrow["startdate"];
    $smidnight = strtotime( $sd );
    $classhour = date( "H", strtotime( $fullstartdate ) );
    // if( $trainerid ==14216 )
    // {
    //     print_r( $availrow );
    //     echo( "<br><b>".date( "Y-m-d H:i:s", strtotime( $fullstartdate ) ).".</b><br>" );
    //     echo( "<b>".date( "Y-m-d H:i:s", $actualtime ).".</b><br>" );
    //     echo( "classhour: $classhour" );


    // adding a time check for the availability, only checking the start time tho

    // if( $trainerid == 301 )
    // {
    //     echo( "classhour: ".intval( $classhour )." -- " . $availrow["starttime"] . "\n"  );
    // }
    if( $classhour > 0 && intval( $classhour ) < $availrow["starttime"] )
{
    // if( $trainerid == 301 )
    // echo( "returning false because we just can't" );
    return false;
}
    
    //}
    if( db_query_first_cell( "select trainerid from trainer_notavail where trainerid = '$trainerid' and notavail <= '". date( "Y-m-d", $actualtime )."' and enddate >= '". date( "Y-m-d", $actualtime )."'" ) )
        return false;
        
    if( $availrow["enddate"] && $availrow["enddate"] != "0000-00-00" )
    {
        $ed = date( "Y-m-d", strtotime( $availrow["enddate"] ) );
        $emidnight = strtotime( $ed );
    }

    if( $ed )
    {
        if( $smidnight > $actualtime || $emidnight < $actualtime )
        {
            return false;
        }
    }
    else if( $sd )
    {
         // if( $trainerid == 105 )
         //     echo( "in here<br>" );
        if( $day != $sd  )
            return false;
    }
                
    
    if( $availrow["repeattype"] == "weekly" )
    {
        $dow = date( "w", strtotime( $day ) );
        switch( $availrow["weekday"] )
        {
            case -3:
                break;
            case -2:
                if( $dow <= 5 && $dow >= 1 )
                {}
                else
                {
                    return false;
                }
                break;
            case -1:
                if( $dow <= 5 && $dow >= 1 )
                {
                    return false;
                }
                break;
            default:
                if( $dow != $availrow["weekday"] )
                {
//                     if( $trainerid == 105 )
//                         echo( "f" );
                    return false;
                }
        }

    }
    else if( $availrow["repeattype"] == "monthly" )
    {
        $d1 = date( "d", strtotime( $day ) );
        $d2 = date( "d", strtotime( $availrow["startdate"] ) );
        if( $d1 != $d2 )
            return false;
        else
        {
//            echo( $day );
        }
    }
    return true;
}


function availableOn( $day, $trainerid = "", $classid="" )
{
    global $session_id, $dontcheckdates, $availcache;
    if( !$trainerid )
        $trainerid = $session_id;
    $dt = date( "Y-m-d", strtotime( $day ) );
// tookout on 11/8
    if( !$dontcheckdates )
    {
        $cl = db_query_first( "select class.* from class , trainer_to_class where trainer_to_class.classid = class.id and trainer_to_class.trainerid = $trainerid and startdate like '$dt%' and deleted = 0 and classid <> '$classid' and class.canceldate is null" );
        if( $cl )
        {
            return 2;
        }
        $cl = db_query_first( "select class.* from class where tcfacultyid = $trainerid and startdate like '$dt%' and deleted = 0 and class.id <> '$classid'" );
        if( $cl )
        {
            return 3;
        }
    }

    
    $arr = array();
    if( isset( $availcache ) )
        $rows = $availcache[$trainerid];
    else
        $rows = db_query_rows( "select * from trainer_availability where trainerid = $trainerid" );
    foreach( $rows as $r )
        {
            if( date( "w", strtotime( $day ) ) != $r["weekday"] )
                continue;
            if( matches( $dt, $r, $trainerid, $day  ) )
            {
                $arr[] = array( $r["starttime"], $r["endtime"] );
            }
        }
    return $arr;
}

function classesOn( $day, $showassignedtrainerstoo = false )
{
    global $session_id;
    $dt = date( "Y-m-d", strtotime( $day ) );
    $cl = db_query_rows( "select class.* from class , trainer_to_class where trainer_to_class.classid = class.id and trainer_to_class.trainerid = $session_id and startdate like '$dt%' and deleted = 0 ", "id" );
    $cl = array_merge( $cl, db_query_rows( "select class.* from class where tcfacultyid = $session_id and startdate like '$dt%' and deleted = 0 ", "id" ) );
    if( $showassignedtrainerstoo )
    {
//        file_put_contents( "/tmp/topull", "select class.* from class , trainer_to_class where trainer_to_class.classid = class.id and trainer_to_class.trainerid in (select id from user where assignedtcfacultyid = '$session_id' ) and startdate like '$dt%' and deleted = 0" );
        $cl2 = db_query_rows( "select class.* from class , trainer_to_class where trainer_to_class.classid = class.id and trainer_to_class.trainerid in (select id from user where assignedtcfacultyid = '$session_id' ) and startdate like '$dt%' and deleted = 0 ", "id" );
        $cl = array_merge( $cl, $cl2 );        
    }
    return $cl;
}

function get_trainer_day_cell($month, $day_of_month, $year, $showassignedtrainerstoo = false) {
    global $thisusersrow,$allclass_names;
    $av = availableOn( "$year-$month-$day_of_month" );
    $cl = classesOn( "$year-$month-$day_of_month", $showassignedtrainerstoo );

    if( $cl )
    {
        $bgcolor = "#f9bbd2";
            
        foreach( $cl as $c )
        {
            if( isTrainerAssigned( $c, $thisusersrow["id"] ) )
                $bgcolor = "#ccffcc";
        }
    }
    else if( $av )
        $bgcolor = "#ffff99";
    else
        $bgcolor = "white";
//    $bgcolor = (!empty($av)) ? ( $cl? "#ccffcc":"#ffff99" ) : "white";

    if( !$thisusersrow["national"] )
    {
        $note = db_query_first_cell( "select note from trainernotes where dt = '$year-$month-$day_of_month'" );
        if( $note )
            $note = "<br><font color='red'>$note</font>";
    }

    $snote2 = db_query_first_cell( "select dt from peakdates where dt = '$year-$month-$day_of_month'" );
    if( $snote2 )
$note .= "<br><font color='red'>PEAK</font>";

    $day_of_week = date("w", mktime(0, 0, 0, $month, $day_of_month, $year));
    echo "<td valign='top' width='65' align='left' bgcolor='$bgcolor'><span class='small'><b><a href='print_daily_schedule.php?m=$month&d=$day_of_month&y=$year'>$day_of_month</a></b>$note";
    foreach ($cl as $class) {
        $id = $class["id"];
        $time = date( "h:iA", strtotime( $class["startdate"] ) );
        $crow = getCompanyRow( $class["companyid"] );
        $col = $crow["iscorp"]?"green":"blue";
if( $crow["iscorp"] == AGING )$col = "maroon";
        if( $crow["iscorp"] ) $class_names = $allclass_names[1]; else $class_names = $allclass_names[0];
        $end = $class["enddate"]?"- ".date( "h:iA", strtotime( $class["enddate"] ) ):"";
        $pval = htmlentities( $class_names[$class["code"]] ) . "<Br>$time $end<br>$crow[schoolcode] ";
        if( !isTrainerAssigned( $class, $thisusersrow[id] ) )
        {
            $fnt = "<i>";
            $endfnt = "</i>";

            $trainers = getTrainers( $class["id"] );
            foreach( $trainers as $t )
            {
                if( $t["assignedtcfacultyid"] == $thisusersrow["id"] )
                    $pval .= "<br>" . $t["first_name"] . " " . $t["last_name"];
            }
        }

      if( $class["remote"]  )
          $remotecheck = "<img src='images/laptop.png'>";
      else
  $remotecheck = "";
        
        echo "<br><a onMouseover=\"popup('$pval', 'white')\" onMouseout=\"kill()\" href='class_detail.php?id=$id'>{$fnt}$time{$endfnt}</a> $remotecheck";
        $tconfirmdate = db_query_first_cell( "select trainerconfirmeddate from trainer_to_class where trainerid = '$thisusersrow[id]' and classid = '$id'" );
        if( !$tconfirmdate && $class["tcfacultyid"] != $thisusersrow["id"])
        {
            $tcid = db_query_first_cell( "select id from trainer_to_class where trainerid = '$thisusersrow[id]' and classid = '$id'" );
            if( $tcid )
                echo( "<br><a href='confirmtraining.php?id=$tcid'>Confirm</a>" );
        }
    }
    if( !$cl )
    {
        foreach ($av as $a) {
            $start = getDisplayTime( $a[0] );
            $end = getDisplayTime( $a[1] );
            echo "<br>$start - $end";
        }
    }
    echo "</span></td>";
}

function show_trainer_calendar($month, $year, $showassignedtrainerstoo ) {

  $next_year = $year;
  $prev_year = $year;
  //  echo $month;exit;
  $next_month = $month + 1;
  if ($next_month == 13) {
    $next_month = 1;
    $next_year = $next_year + 1;
  }
  $prev_month = $month - 1;
  if ($prev_month == 0) {
    $prev_month = 12;
    $prev_year = $prev_year - 1;
  }

  $day = 1;
  $day_of_week = date("w", mktime(0, 0, 0, $month, $day, $year));
  $month_and_year = date("M Y", mktime(0, 0, 0, $month, $day, $year));

?>

<!--start calendar-->
<table cellpadding="2" cellspacing="1" border="0" bgcolor="#5a179e" class="table3">
<tr height="21">
<td valign="top" colspan="7" align="center" bgcolor="#5a179e">
<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
<td valign="bottom" width="5%"><a href="tcalendar.php?month=<?php echo $prev_month?>&year=<?php echo $prev_year?>"><img src="images/arrow_left.gif" border="0"></a></td>
<td valign="bottom" align="center" width="90%"><span class="white"><strong><?php echo $month_and_year?></strong></span></td>
<td valign="bottom" align="right" width="5%"><a href="tcalendar.php?month=<?php echo $next_month?>&year=<?php echo $next_year?>"><img src="images/arrow_right.gif" border="0"></a></td>
</tr>
</table>
</td>
</tr>
<tr><td><span class='white'>Sunday</td>
     <td><span class='white'>Monday</td>
     <td><span class='white'>Tuesday</td>
     <td><span class='white'>Wednesday</td>
     <td><span class='white'>Thursday</td>
     <td><span class='white'>Friday</td>
     <td><span class='white'>Saturday</td>
</tr>     
<?php

// FIRST WEEK OF THE MONTH
echo "<tr bgcolor='#ffffff' height='65'>";
// Loop through each day of the week
for ($i = 0; $i <= 6; $i++) {
  if ($i == $day_of_week) {
      echo get_trainer_day_cell( $month, 1, $year, $showassignedtrainerstoo);
    break;
  } else {
    echo "<td valign='top' width='65' align='center' bgcolor='#cccccc'></span></td>";
  }
}

$i++;
 $day_of_month = 2;
for ($j = $i; $j <= 6; $j++, $day_of_month++) {
  //  echo $day_of_month;
    echo get_trainer_day_cell($month, $day_of_month, $year, $showassignedtrainerstoo);
}  
echo "</tr>";

// NEXT FOUR WEEKS OF THE MONTH
for ($week_of_month = 2; $week_of_month <= 6; $week_of_month++) {
  echo "<tr bgcolor='#ffffff' height='65'>";
  for ($j = 1; $j <= 7; $j++) {
    $this_month = date("n", mktime(0, 0, 0, $month, $day_of_month, $year));
    if ($this_month == $month) {
        echo get_trainer_day_cell($month, $day_of_month, $year, $showassignedtrainerstoo );
    } else {
      echo "<td valign='top' width='65' align='center' bgcolor='#cccccc'></span></td>";
    }      
    $day_of_month++;
  }  
  echo "</tr>";
}

?>

<tr height="21">
<td valign="top" colspan="7" align="center" bgcolor="#5a179e">
<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
<td valign="bottom" width="5%"><a href="tcalendar.php?month=<?php echo $prev_month?>&year=<?php echo $prev_year?>"><img src="images/arrow_left.gif" border="0"></a></td>
<td valign="bottom" align="center" width="90%"><span class="white"><strong><?php echo $month_and_year?></strong></span></td>
<td valign="bottom" align="right" width="5%"><a href="tcalendar.php?month=<?php echo $next_month?>&year=<?php echo $next_year?>"><img src="images/arrow_right.gif" border="0"></a></td>
</tr>
</table>
</td>
</tr>
</table>

</div>
<!--end calendar 1-->

<?php

    }


function show_drill_trainer_calendar($month, $year ) {
    global $session_id;
  $next_year = $year;
  $prev_year = $year;
  //  echo $month;exit;
  $next_month = $month + 1;
  if ($next_month == 13) {
    $next_month = 1;
    $next_year = $next_year + 1;
  }
  $prev_month = $month - 1;
  if ($prev_month == 0) {
    $prev_month = 12;
    $prev_year = $prev_year - 1;
  }

  $day = 1;
  $day_of_week = date("w", mktime(0, 0, 0, $month, $day, $year));
  $month_and_year = date("M Y", mktime(0, 0, 0, $month, $day, $year));

?>

<!--start calendar-->
<table cellpadding="2" cellspacing="1" border="0" bgcolor="#5a179e" class="tabledrill">
<tr height="21">
<td valign="top" colspan="7" align="center" bgcolor="#5a179e">
<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
<td valign="bottom" width="5%"><a href="drillcalendar.php?month=<?php echo $prev_month?>&year=<?php echo $prev_year?>"><img src="images/arrow_left.gif" border="0"></a></td>
<td valign="bottom" align="center" width="90%"><span class="white"><strong><?php echo $month_and_year?></strong></span></td>
<td valign="bottom" align="right" width="5%"><a href="drillcalendar.php?month=<?php echo $next_month?>&year=<?php echo $next_year?>"><img src="images/arrow_right.gif" border="0"></a></td>
</tr>
</table>
</td>
</tr>
<tr><td><span class='white'>Sunday</td>
     <td><span class='white'>Monday</td>
     <td><span class='white'>Tuesday</td>
     <td><span class='white'>Wednesday</td>
     <td><span class='white'>Thursday</td>
     <td><span class='white'>Friday</td>
     <td><span class='white'>Saturday</td>
</tr>     
<?php

// FIRST WEEK OF THE MONTH
echo "<tr bgcolor='#ffffff' height='65'>";
// Loop through each day of the week
for ($i = 0; $i <= 6; $i++) {
  if ($i == $day_of_week) {
      //      echo get_trainer_day_cell( $month, 1, $year, $showassignedtrainerstoo);
          $cl = classesOn( "$year-$month-01" );
$s = "";
foreach( $cl as $class )
    {
$id = $class["id"];
$time = date( "h:i A", strtotime( $class["startdate"] ) );
 $s .= "<a target=_blank href='class_detail.php?id=$id'>{$fnt}$time{$endfnt}</a><Br>";
    }

    echo "<td valign='top' width='65' class='drilldt' align='center' data-dt='{$year}-{$month}-1' style='background-color: " . getTrainerDrillColor( $month, 1, $year ) . "'><span class='small'><b>1</b></span><br>$s</td>";
    break;
  } else {
    echo "<td valign='top' width='65' align='center' bgcolor='#cccccc'></td>";
  }
}

$i++;
 $day_of_month = 2;
for ($j = $i; $j <= 6; $j++, $day_of_month++) {
  //  echo $day_of_month;
          $cl = classesOn( "$year-$month-$day_of_month" );
$s = "";
foreach( $cl as $class )
    {
$id = $class["id"];
$time = date( "h:i A", strtotime( $class["startdate"] ) );
$s .= "<a target=_blank  href='class_detail.php?id=$id'>{$fnt}$time{$endfnt}</a><Br>";
    }
    echo "<td valign='top' width='65' class='drilldt' align='center' data-dt='{$year}-{$month}-{$day_of_month}' style='background-color: " . getTrainerDrillColor( $month, $day_of_month, $year ) . "'><span class='small'><b>$day_of_month</b></span><br>$s</td>";
    //    echo get_trainer_day_cell($month, $day_of_month, $year, $showassignedtrainerstoo);
}  
echo "</tr>";

// NEXT FOUR WEEKS OF THE MONTH
for ($week_of_month = 2; $week_of_month <= 6; $week_of_month++) {
  echo "<tr bgcolor='#ffffff' height='65'>";
  for ($j = 1; $j <= 7; $j++) {
    $this_month = date("n", mktime(0, 0, 0, $month, $day_of_month, $year));
    if ($this_month == $month) {
//        echo get_trainer_day_cell($month, $day_of_month, $year, $showassignedtrainerstoo );
$cl = classesOn( "$year-$month-$day_of_month" );
$s = "";
foreach( $cl as $class )
    {
$id = $class["id"];
$time = date( "h:i A", strtotime( $class["startdate"] ) );
$s .= "<a target=_blank href='class_detail.php?id=$id'>{$fnt}$time{$endfnt}</a><Br>";
    }
echo "<td valign='top' width='65' class='drilldt' align='center' data-dt='{$year}-{$month}-{$day_of_month}' style='background-color: " . getTrainerDrillColor( $month, $day_of_month, $year ) . "'><span class='small'><b>$day_of_month</b></span><br>$s</td>";

    } else {
      echo "<td valign='top' width='65' align='center' bgcolor='#cccccc'></span></td>";
    }      
    $day_of_month++;
  }  
  echo "</tr>";
}

?>

<tr height="21">
<td valign="top" colspan="7" align="center" bgcolor="#5a179e">
<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
<td valign="bottom" width="5%"><a href="tcalendar.php?month=<?php echo $prev_month?>&year=<?php echo $prev_year?>"><img src="images/arrow_left.gif" border="0"></a></td>
<td valign="bottom" align="center" width="90%"><span class="white"><strong><?php echo $month_and_year?></strong></span></td>
<td valign="bottom" align="right" width="5%"><a href="tcalendar.php?month=<?php echo $next_month?>&year=<?php echo $next_year?>"><img src="images/arrow_right.gif" border="0"></a></td>
</tr>
</table>
</td>
</tr>
</table>

</div>
<!--end calendar 1-->

<?php

    }

function show_codeblue_calendar($month, $year ) {
    global $session_id;
  $next_year = $year;
  $prev_year = $year;
  //  echo $month;exit;
  $next_month = $month + 1;
  if ($next_month == 13) {
    $next_month = 1;
    $next_year = $next_year + 1;
  }
  $prev_month = $month - 1;
  if ($prev_month == 0) {
    $prev_month = 12;
    $prev_year = $prev_year - 1;
  }

  $day = 1;
  $day_of_week = date("w", mktime(0, 0, 0, $month, $day, $year));
  $month_and_year = date("M Y", mktime(0, 0, 0, $month, $day, $year));

?>

<!--start calendar-->
<table cellpadding="2" cellspacing="1" border="0" bgcolor="#5a179e" width="100%">
<tr height="21">
<td valign="top" colspan="7" align="center" bgcolor="#5a179e">
<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
<td valign="bottom" width="5%"><a href="codebluecalendar.php?month=<?php echo $prev_month?>&year=<?php echo $prev_year?>"><img src="images/arrow_left.gif" border="0"></a></td>
<td valign="bottom" align="center" width="90%"><span class="white"><strong><?php echo $month_and_year?></strong></span></td>
<td valign="bottom" align="right" width="5%"><a href="codebluecalendar.php?month=<?php echo $next_month?>&year=<?php echo $next_year?>"><img src="images/arrow_right.gif" border="0"></a></td>
</tr>
</table>
</td>
</tr>
<tr><td><span class='white'>Sunday</td>
     <td><span class='white'>Monday</td>
     <td><span class='white'>Tuesday</td>
     <td><span class='white'>Wednesday</td>
     <td><span class='white'>Thursday</td>
     <td><span class='white'>Friday</td>
     <td><span class='white'>Saturday</td>
</tr>     
<?php

// FIRST WEEK OF THE MONTH
echo "<tr bgcolor='#ffffff' height='65'>";
// Loop through each day of the week
for ($i = 0; $i <= 6; $i++) {
  if ($i == $day_of_week) {
echo "<td valign='top' width='65' align='center' ><span class='small'><b>1</b></span><br>" . getTrainersForCodeBlue( $month, 1, $year ) . "</td>";
    break;
  } else {
    echo "<td valign='top' width='65' align='center' bgcolor='#cccccc'></td>";
  }
}

$i++;
 $day_of_month = 2;
for ($j = $i; $j <= 6; $j++, $day_of_month++) {
  //  echo $day_of_month;
echo "<td valign='top' width='65' align='center' ><span class='small'><b>$day_of_month</b></span><br>" . getTrainersForCodeBlue( $month, $day_of_month, $year ) . "</td>";
}  
echo "</tr>";

// NEXT FOUR WEEKS OF THE MONTH
for ($week_of_month = 2; $week_of_month <= 6; $week_of_month++) {
  echo "<tr bgcolor='#ffffff' height='65'>";
  for ($j = 1; $j <= 7; $j++) {
    $this_month = date("n", mktime(0, 0, 0, $month, $day_of_month, $year));
    if ($this_month == $month) {
echo "<td valign='top' width='65' align='center' ><span class='small'><b>$day_of_month</b></span><br>" . getTrainersForCodeBlue( $month, $day_of_month, $year ) . "</td>";
    } else {
      echo "<td valign='top' width='65' align='center' bgcolor='#cccccc'></span></td>";
    }      
    $day_of_month++;
  }  
  echo "</tr>";
}

?>

<tr height="21">
<td valign="top" colspan="7" align="center" bgcolor="#5a179e">
<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
<td valign="bottom" width="5%"><a href="tcalendar.php?month=<?php echo $prev_month?>&year=<?php echo $prev_year?>"><img src="images/arrow_left.gif" border="0"></a></td>
<td valign="bottom" align="center" width="90%"><span class="white"><strong><?php echo $month_and_year?></strong></span></td>
<td valign="bottom" align="right" width="5%"><a href="tcalendar.php?month=<?php echo $next_month?>&year=<?php echo $next_year?>"><img src="images/arrow_right.gif" border="0"></a></td>
</tr>
</table>
</td>
</tr>
</table>

</div>
<!--end calendar 1-->

<?php

    }


function getTrainerDrillColor( $month, $day, $year, $id = "" )
{
    global $session_id;
    if( !$session_id ) $session_id = $id;
    //    create table tdrillcalendar( thedate date, status tinyint, userid integer );
    $status = db_query_first_cell( "select status from tdrillcalendar where userid = $session_id and thedate = '$year-$month-$day'" );
    if( !$status )
return "#ffffff";
    if( $status == 1 )
return "#10e85c";
    if( $status == 2 )
return "#e81b17";
}


function getTrainersForCodeBlue( $month, $day, $year )
{
    $status = db_query_array( "select concat( first_name, ' ', last_name ) as name from tdrillcalendar t, user u where t.userid = u.id and thedate = '$year-$month-$day' and status = 1 order by last_name, first_name" , "name", "name");
    return implode( ",<Br> ", $status );
}

function updateTrainerAvail( $id, $date, $val )
{
    db_query( "delete from tdrillcalendar where userid = '$id' and thedate = '$date'" );
    db_query( "insert into tdrillcalendar (userid,status,thedate ) values ( '$id', '$val', '$date' )" );
}



function isCompleted( $id, $classid )
{
    $res = db_query_first( "select * from responder_training_dates where classid = '$classid' and responderid = '$id'" );
    if( $res["id"] )
        return true;
    else
        return false;
    
}

function getCurrentTrainerExp( $expgroup, $field, $trainerid )
{
    return db_query_first_cell( "select $field from trainer_exp_dates where trainerid = $trainerid and expgroup = '$expgroup' order by id desc limit 1" );
}

function getCurrentTrainerExpRow( $expgroup, $trainerid )
{
    return db_query_first( "select * from trainer_exp_dates where trainerid = $trainerid and expgroup = '$expgroup' order by id desc limit 1" );
}

function getClassEmail( $crow )
{
    $em = $crow["email"]; 
    if( !$em ) 
    {
        $em = getUserEmail( $crow["addedby"] );
    }
    return $em;
}

function getClassContact( $crow )
{
    $em = trim( $crow["firstname"] . " " . $crow["lastname"] ); 
    if( !$em ) 
    {
        $em = getFullname( $crow["addedby"] );
    }
    return $em;
}

function getTrainingDates( $id )
{
//echo( "select class.*, trainingdate, class.id as classid, responder_training_dates.id as tid, responder_training_dates.program as tprogram from responder_training_dates left join class on class.id = classid where responderid = '$id'  order by startdate desc" );
    $res = db_query_rows( "select class.*, trainingdate, class.id as classid, responder_training_dates.id as tid, responder_training_dates.program as tprogram from responder_training_dates left join class on class.id = classid where responderid = '$id'  order by startdate desc" );
    return $res;
}

function getPreviousSchools( $id )
{
    $res = db_query_rows( "select * from oldresponderschools where responderid = '$id'  order by movedate desc" );
    return $res;
}

function hasDash( $area, $doptions = -1 )
{
global $dashoptions;
if( $doptions == -1 )
$doptions = $dashoptions;
return $doptions[$area];
}
function getDashboardOptions( $id = "" )
{
global $session_id;
if( !$id )
$id = $session_id;
    $res = db_query_array( "select areaname from dashboardoptions  where userid = '$id'", "areaname", "areaname"  );
    return $res;
}
function getPreviousSchoolsAed( $id )
{
    $res = db_query_rows( "select * from oldaedschools where aedid = '$id'  order by movedate desc" );
    return $res;
}

function getNonExpiredResponders( $schoolid )
{
    $dt = date( "Y-m-d", mktime( 0,0,0,date("m" ), date( "d" ), date( "Y" ) - 2 ) );
    $res = db_query_rows( " select r.pmsid, r.firstname, r.lastname, r.responderid, count( rt.id ) as numclasses, max( rt.trainingdate ) as trainingdate from responders_esi r left join responder_training_dates rt on r.responderid = rt.responderid and rt.trainingdate >  '$dt' where clientid = $schoolid and deleted = 0 group by responderid having numclasses > 0  " );
//    echo( " select r.firstname, r.lastname, r.responderid, count( rt.id ) as numclasses from responders_esi r left join responder_training_dates rt on r.responderid = rt.responderid and rt.trainingdate >  '$dt' where clientid = $schoolid and deleted = 0 group by responderid having numclasses > 0  " );
    return $res;
}

function getNumResponders( $id, $noexpired = false )
{
    if( !$noexpired )
        return  db_query_first_cell("select count(*) from responders_esi where clientid=$id and deleted=0 ");
    else
    {
        $nonexpired = getNonExpiredResponders( $id );
        return count( $nonexpired );
    }
}
function getNumDrills( $id )
{
return  db_query_first_cell("select count(*) from drill where companyid=$id ");
}

function getResponders( $id )
{
    if( $id == 12969 )
        $ext = " or isssal = 1";

    return db_query_rows("select responderid, firstname, lastname, title, email from responders_esi where ( clientid=$id $ext ) and deleted=0 order by lastname");

}

function getResponderExpDate( $responderid )
{
    return db_query_first_cell( "Select responder_training_dates.trainingdate from responder_training_dates where responderid = $responderid order by trainingdate desc" );
}

function getResponderExpDatePlus( $responderid )
{
    return db_query_first_cell( "Select responder_training_dates.trainingdate + interval 2 YEAR from responder_training_dates where responderid = $responderid order by trainingdate desc" );
}

function getRegionDisp( $region ) 
{
$spl = explode( ",", $region );
foreach( $spl as $s )
{
    $s = trim( $s );
if( !$retval )
$retval = "'";
else $retval .= ",'";
$retval .= $s . "'";
}
return $retval;
}

function getDistrictString( $dist, $comptable="company_esi." ) 
{
    global $thisusersrow;
    $spl = explode( ",", $dist );
    $retval = " and ( 1 = 0 ";
    foreach( $spl as $s )
    {
        $retval .= " or {$comptable}schoolcode like '$s-%' ";
    }

    if( $thisusersrow["singleschoolid"] )
    {
        $retval .= " or {$comptable}id = '$thisusersrow[singleschoolid]' ";

    }
    $retval .= " ) ";
    return $retval;
}

function getExpiredSchools( $num = "0", $region = "", $dt = "" )
{
    global $session_iscorp, $thisusersrow;
    if( !$dt )
        $dt = date( "Y-m-d", mktime( 0,0,0,date("m" ), date( "d" ), date( "Y" ) - 2 ) );
if( $region && $region != "''" )
$rstr= " and region in ($region)" ;
if( !$num )
        $num = 0;
    if( $thisusersrow["districts"] )
        $rstr .= getDistrictString( $thisusersrow["districts"]);
    
    $sql = "select c.id, count( r.responderid ) as numresponders, case when max( rt.trainingdate ) > '2001-01-01' then interval 2 YEAR + max( rt.trainingdate ) else NULL end as maxdate, sum( case when rt.trainingdate > '$dt' then 1 else 0 end ) as numdates, c.*  from company_esi c left join responders_esi r on r.clientid = c.id and r.deleted = 0 left join responder_training_dates rt on r.responderid = rt.responderid where iscorp = '$session_iscorp' and c.deleted = 0 and c.retired = 0 and c.donotinclude = 0 $rstr group by c.id having numdates <= $num order by schoolcode, companyname ";
    // echo( $sql );
    // exit;
    $res = db_query_rows( $sql, "id" );
    return $res;
}

function isNurse( $resp )
{
if( !$resp )
        return false;
$title = db_query_first_cell( "select title from responders_esi where responderid = '$resp'" );
$title = trim( strtolower( $title ) ); 
if( $title == "rn" || $title == "nurse" || (strpos( $title, "nurse" ) !== false) )
return true;
return false;
}

function getNoAEDSchools()
{
    global $session_iscorp;
    $sql = "select c.id, count( r.aedid ) as numaeds, c.* from company_esi c left join aed_esi r on r.clientid = c.id and r.deleted = 0 where iscorp = '$session_iscorp' and  c.deleted = 0 group by c.id having numaeds = 0 order by schoolcode, companyname";
//    echo( $sql );
    $res = db_query_rows( $sql, "id" );
    return $res;
}
function getNoBuildingNos()
{
global $session_iscorp;
    $sql = "select * from company_esi c where iscorp = '$session_iscorp' and  c.deleted = 0 and (c.buildingno is null or c.buildingno = '' ) order by schoolcode, companyname";
//    echo( $sql );
    $res = db_query_rows( $sql, "id" );
    return $res;
}

function getNoAEDLocations()
{
global $session_iscorp;
    $sql = "select cp.* , count( aedid ) as numaeds from campus cp, company_esi c left join aed_esi r on r.clientid = c.id and r.deleted = 0 where c.iscorp = '$session_iscorp' and  cp.id = campusid and c.deleted = 0 and campusid > 0 group by c.campusid having numaeds = 0 order by borough, address";
//    echo( $sql );
    $res = db_query_rows( $sql, "id" );
    return $res;
}
$save_tzip = array();

function getTrainersForZip( $zip )
{
    global $save_tzip;
    
    if( isset( $save_tzip[$zip] ) )
return $save_tzip[$zip];
    $responderarr = db_query_rows( "select concat( first_name, ' ', last_name ) as name, user.* from (user, user_to_zip) where user_to_zip.userid = user.id and inactive = 0 and paused = 0 and (user_to_zip.zip = '$zip'  ) ", "name" );
    $save_tzip[$zip] = $responderarr;
    // left join territory on trainerid = user.id left join zip_to_territory on territoryid = territory.id and zip_to_territory.zip ='$zip'
    // or zip_to_territory.zip = '$zip'
return $responderarr;
}
function getNoResponderLocations()
{
global $session_iscorp;
    $sql = "select cp.* , count( responderid ) as numaeds from campus cp, company_esi c left join responders_esi r on r.clientid = c.id and r.deleted = 0 where  c.iscorp = '$session_iscorp' and cp.id = campusid and c.deleted = 0 and campusid > 0 group by c.campusid having numaeds = 0 order by borough, address";
    $res = db_query_rows( $sql );
    return $res;
}

function getCurrentResponders( $schoolid, $mydt = "" ) 
{
    if( !$mydt )
        $mydt = time();

if( $schoolid == 12969 )
    {
        $ext = " or isssal = 1";
    }

if( date( "Y-m-d" ) < "2020-10-31" )
    {
    $dt = date( "Y-m-d", mktime( 0,0,0,date("m", $mydt ), date( "d", $mydt ), date( "Y", $mydt ) - 2 ) ); // CHANGE BACK AFTER THIS IS OVER
    $dtext = " or ( rt.trainingdate < '2018-09-01' and rt.trainingdate >= '2018-08-01' ) ";
    }
else
    $dt = date( "Y-m-d", mktime( 0,0,0,date("m", $mydt ), date( "d", $mydt ), date( "Y", $mydt ) - 2 ) ); // CHANGE BACK AFTER THIS IS OVER
    $res = db_query_rows( " select r.title, r.firstname, r.lastname, r.responderid, count( rt.id ) as numclasses from responders_esi r left join responder_training_dates rt on r.responderid = rt.responderid and ( rt.trainingdate >  '$dt' $dtext ) where r.deleted = 0 and ( clientid in ( $schoolid ) $ext ) group by responderid having numclasses > 0 order by r.lastname " );
    //echo( " select r.title, r.firstname, r.lastname, r.responderid, count( rt.id ) as numclasses from responders_esi r left join responder_training_dates rt on r.responderid = rt.responderid and ( rt.trainingdate >  '$dt' $dtext ) where r.deleted = 0 and ( clientid in ( $schoolid ) $ext ) group by responderid having numclasses > 0 order by r.lastname " );
    return $res;
}

function getCampusName( $campusid )
{
    if( $campusid )
    {
        return db_query_first_cell( "select name from campus where id = '$campusid'" );
    }
}

function getCampuses( $zip, $corp=0)
{
    if( $zip && !$corp)
        return db_query_array( "select name, id from campus where zipcode = '$zip' and iscorp = '$corp' order by name", "id", "name" );
    else
        return db_query_array( "select name, id from campus where iscorp = '$corp' order by name", "id", "name" );
}
function getSchoolsInCampus( $campusid, $myid=-1 )
{
    global $visi;
    if( $campusid )
        return db_query_rows( "select companyname, id, address, principalname, principalemail, schoolphone from company_esi where id <> $myid and deleted =0 and campusid = '$campusid' $visi order by companyname, address " );
}

function printdates2( $varname, $thedef="", $returnit = false, $id="", $onchange=""  ) 
{
    if( $thedef == "0000-00-00" ) $thedef = "";


    if( !$id ) $id = $varname;
    $idstr = "";
    if( $id )
    {
        $idstr = "id='$id'";
    }

    if( $thedef == "picktoday" )
{
    $ext = "document.getElementById( '$id' ).value = '".date( "Y-m-d"). "';";
    $thedef = "";
}

    if( $onchange )
    {
        $idstr .= "onchange=\"$onchange\"";
    }
if( $returnit )
{
return( "<input size='10' class='copy' $idstr name='$varname' value='$thedef'> <img src=\"calendar.png\" border=\"0\" onclick=\"{$ext}displayDatePicker('$varname') \">" );
}
else
{ $ext = "document.getElementById( '$id' ).value = '".date( "Y-m-d"). "';";
    echo( "<input size='10' class='copy' $idstr name='$varname' value='$thedef'> <img src=\"calendar.png\" border=\"0\" onclick=\"{$ext}displayDatePicker('$varname') \">" );
} 
}
     
function get_teacher_dropdown($companyid, $arow, $iscorp = false) {

    $teacherid = $arow["responderid"];
    $this2 = db_query_first( "select * from responders_esi where responderid = '$teacherid'" );
    $otherex = "";
    if( $this2 )
    {
        $other = $companyid!=$this2["clientid"]?" (O) ":"";
        $other .= $arow["individual"]?" (I)":"";
        $otherex .= $arow["individual"]?"<input type='hidden' name='individ".$arow["responderid"]."' value='1'>":"";
        if( isOverallAdmin() || (!$arow["individual"] && $companyid==$this2["clientid"] ))
            $teacher_options .= "<option value=''>Please select</option>";
        $teacher_options .= "<option SELECTED value='$this2[responderid]'>$this2[lastname], $this2[firstname] (#".getIdentifier( $this2 ).") $other</option>";
if( !isOverallAdmin() && ( $arow["individual"] || $companyid!=$this2["clientid"] ) )
    return array( $teacher_options, $otherex );

    }
    else
    {
        $teacher_options .= "<option value=''>Please select</option>";
    }

    if( isOverallAdmin() || (!$arow["individual"] && $companyid==$this2["clientid"] ) || 1 )
    {
    if( !$iscorp ) $ext = " and pmsidvalidated = 1 ";
    if( !isOverallAdmin() || !$iscorp )
        $ext .= "and email > ''";
    
    $eighteen = date( "Y-m-d", strtotime( "18 months ago" ) );
    $extresp = " and responders_esi.responderid not in ( select responderid from responder_to_class rtc, class c where rtc.classid = c.id and c.startdate > '$eighteen' ) ";

$sql = "select * from responders_esi where clientid = '$companyid'  $ext $extresp order by lastname, firstname";
//              echo $sql;exit;
        $teachers = db_query_rows($sql);
        foreach ($teachers as $teacher) {
            foreach ($teacher as $key => $val) {
                ${$key} = $val;
            }
            if( !$teacher["email"] )
                $e = "MISSING EMAIL";
            $teacher_options .= "<option value='$responderid'>$lastname, $firstname $e (#".getIdentifier( $teacher ).")</option>";
        }
    }
    return array( $teacher_options, $otherex );
}


function show_big_trainer_avail_calendar($month, $year) {
    global $searchzip;
  $next_year = $year;
  $prev_year = $year;
  //  echo $month;exit;
  $next_month = $month + 1;
  if ($next_month == 13) {
    $next_month = 1;
    $next_year = $next_year + 1;
  }
  $prev_month = $month - 1;
  if ($prev_month == 0) {
    $prev_month = 12;
    $prev_year = $prev_year - 1;
  }

  $day = 1;
  $day_of_week = date("w", mktime(0, 0, 0, $month, $day, $year));
  $month_and_year = date("M Y", mktime(0, 0, 0, $month, $day, $year));

?>

<!--start calendar-->
<table cellpadding="2" cellspacing="1" border="0" bgcolor="#5a179e">
<tr height="21">
<td valign="top" colspan="7" align="center" bgcolor="#5a179e">
<table cellpadding="0" cellspacing="0" border="0" width="600">
<tr>
<td valign="bottom" width="5%"><a href="trainer_avail_calendar.php?searchzip=<?php echo $searchzip?>&month=<?php echo $prev_month?>&year=<?php echo $prev_year?>"><img src="images/arrow_left.gif" border="0"></a></td>
<td valign="bottom" align="center" width="90%"><span class="white"><strong><?php echo $month_and_year?></strong></span></td>
<td valign="bottom" align="right" width="5%"><a href="trainer_avail_calendar.php?searchzip=<?php echo $searchzip?>&month=<?php echo $next_month?>&year=<?php echo $next_year?>"><img src="images/arrow_right.gif" border="0"></a></td>
</tr>
</table>
</td>
</tr>

<tr><td><span class='white'>Sunday</td>
     <td><span class='white'>Monday</td>
     <td><span class='white'>Tuesday</td>
     <td><span class='white'>Wednesday</td>
     <td><span class='white'>Thursday</td>
     <td><span class='white'>Friday</td>
     <td><span class='white'>Saturday</td>
</tr>     
<?php

// FIRST WEEK OF THE MONTH
echo "<tr bgcolor='#ffffff' height='65'>";
// Loop through each day of the week
for ($i = 0; $i <= 6; $i++) {
  if ($i == $day_of_week) {
    echo get_big_trainer_avail_day_cell( $month, 1, $year);
    break;
  } else {
    echo "<td valign='top' width='65' align='center' bgcolor='#cccccc'></span></td>";
  }
}

$i++;
 $day_of_month = 2;
for ($j = $i; $j <= 6; $j++, $day_of_month++) {
  //  echo $day_of_month;
  echo get_big_trainer_avail_day_cell($month, $day_of_month, $year);
}  
echo "</tr>";

// NEXT FOUR WEEKS OF THE MONTH
for ($week_of_month = 2; $week_of_month <= 6; $week_of_month++) {
  echo "<tr bgcolor='#ffffff' height='65'>";
  for ($j = 1; $j <= 7; $j++) {
    $this_month = date("n", mktime(0, 0, 0, $month, $day_of_month, $year));
    if ($this_month == $month) {
      echo get_big_trainer_avail_day_cell($month, $day_of_month, $year);
    } else {
      echo "<td valign='top' width='65' align='center' bgcolor='#cccccc'></span></td>";
    }      
    $day_of_month++;
  }  
  echo "</tr>";
}

?>
<tr height="21">
<td valign="top" colspan="7" align="center" bgcolor="#5a179e">
<table cellpadding="0" cellspacing="0" border="0" width="600">
<tr>
<td valign="bottom" width="5%"><a href="trainer_avail_calendar.php?searchzip=<?php echo $searchzip?>&month=<?php echo $prev_month?>&year=<?php echo $prev_year?>"><img src="images/arrow_left.gif" border="0"></a></td>
<td valign="bottom" align="center" width="90%"><span class="white"><strong><?php echo $month_and_year?></strong></span></td>
<td valign="bottom" align="right" width="5%"><a href="trainer_avail_calendar.php?searchzip=<?php echo $searchzip?>&month=<?php echo $next_month?>&year=<?php echo $next_year?>"><img src="images/arrow_right.gif" border="0"></a></td>
</tr>
</table>
</td>
</tr>

</table>

</div>
<!--end calendar 1-->

<?php

    }

$currentlyusedtrainers = array();
function get_big_trainer_avail_day_cell( $month, $day_of_month, $year) {
    global $searchzip, $currentlyusedtrainers, $availcache;
  $bgcolor = "white";

  if( isOverallAdmin() )
  {
      $note = db_query_first_cell( "select note from trainernotes where dt = '$year-$month-$day_of_month'" );
      if( $note )
          $note = "<br><font color='red'>$note</font>";
  }

  $sd = date( "Y-m-d", mktime( 0,0,0, $month, $day_of_month, $year ) );
  // if( $day_of_month == 1 )
  //     echo( "select classid, ttc.trainerid from trainer_to_class ttc, class where class.id = ttc.classid and startdate like '$sd%' " );
  $numtrainers = db_query_first_cell( "select sum(numtrainers) from class where startdate like '$sd%'" );
  $trainersusednum = db_query_first_cell( "select count(*) from trainer_to_class ttc, class where class.id = ttc.classid and startdate like '$sd%'" );
  $numneeded = $numtrainers - $trainersusednum;
  $trainersused = db_query_array( "select classid, ttc.trainerid from trainer_to_class ttc, class where class.id = ttc.classid and startdate like '$sd%'", "trainerid", "classid" );
  $tcfused = db_query_array( "select tcfacultyid, id from class where startdate like '$sd%' and tcfacultyid > 0", "tcfacultyid", "id" );
  $day_of_week = date("w", mktime(0, 0, 0, $month, $day_of_month, $year));
  echo "<td valign='top' width='20' align='left' bgcolor='$bgcolor'>" ;
  echo "<span class='small'><b>$day_of_month ($numneeded needed)</b><br>";

  if( !count( $currentlyusedtrainers ) )
  {
//      echo( "in" );
  if( $searchzip )
      $currentlyusedtrainers = getTrainersForZip( $searchzip );
  else
      $currentlyusedtrainers = getAllTrainers( " and national = 0 " );
//  echo( "in " );
  }
      
  foreach( $currentlyusedtrainers as $tname =>$trow )
      {
//          echo( $trow["id"] );
//           if( $trow["id"] != 297 )
//               continue;
  $ext = "";
  $extfc = "";
  $extfcend = "";
  if( $trow["isstaff"] ) 
      {
  $ext .= " (S)";
  $extfc .= "<font color='green'>";
  $extfcend .= "</font>";
      }
  if( $trow["isfieldrep"] ) 
      {
  $extfc .= "<font color='blue'>";
  $ext .= " (FR)";
  $extfcend .= "</font>";
      }
          if( availableOn("$year-$month-$day_of_month" , $trow["id"] ) )
      {
  if( $trainersused[$trow[id]] )
      {
  $clid = $trainersused[$trow["id"]];
    echo( "<nobr><a target=_blank href='class_detail.php?id=$clid'>$extfc<b>$tname $ext </b>$extfcend</a></nobr><br>" );
    }
    else if( $tcfused[$trow["id"]] )
    {
$clid = $tcfused[$trow["id"]];
    echo( "<nobr><a target=_blank href='class_detail.php?id=$clid'>$extfc<b>$tname $ext (tcf)</b>$extfcend</a></nobr><br>" );
    }
else 
    echo( "<nobr>{$extfc}$tname $ext{$extfcend}</nobr><br>" );
          }
      }
  
//  echo "</div>";
  echo "$note</span>";
  echo "</td>";
}

function getReschedules( $crow )
{
    $a = array();
    $res = db_query_rows( "Select * from reschedules where classid = $crow[id] order by thedate desc" );
    foreach( $res as $r )
        {
//            $a[] = "<nobr>Set to $r[newdate] $r[newtime] on ".( $r[thedate] )." by $r[who]</nobr>";
            $a[] = $r;
        }
    return $a;
}

function sendMail( $to, $subject, $body,$from = "info@emergencyskills.com", $fromname = "", $sendingtext = false, $key = "" )
{
    global $fromautomated, $session_iscorp;
    if( !$to ) return;

    $cc = "";
    if( is_array( $to ) )
    {
$cc = $to["ccaddress"];
$to  = $to["toaddress"];
    }
    if( $session_iscorp == TRAININGSITES )
return;

if( $subject == "New Attendee" && strtolower( $to ) == "emily.santacroce@slgreen.com" ) return;

    if( !$fromname )
        $fromname = $from;
    require_once "class.phpmailer.php";
    $spl = explode( ",", $to );
    $mail = new PHPMailer();
    if( $from )
        $mail->From = $from;
    if( $fromname )
        $mail->FromName = $fromname;
    $mail->Sender = $from;
    $mail->AddReplyTo( $from );
    $mail->Subject = $subject;
    $mail->Body    = $body;
    foreach( $spl as $s )
    {
        $s = trim( $s ) ;
        if( $s )
            $mail->AddAddress($s);
    }

    $spl = explode( ",", $cc );
    foreach( $spl as $s )
    {
        $s = trim( $s ) ;
        if( $s )
            $mail->AddCc($s);
    }

    $mail->Send();

    if( $fromautomated  || 1 )
    {
//        mysql> create table automatedemails ( datesent datetime, subject varchar( 255 ), torecipients text, body text, bccrecipients text, ccrecipients text );

        db_query( "insert into automatedemails( fromautomated, datesent, subject, torecipients, ccrecipients, bccrecipients, body, fromname, fromemail, emailkey ) values ( '$fromautomated', now(), '" . mysqli_real_escape_string($GLOBALS['link'], $subject ) . "', '" . mysqli_real_escape_string($GLOBALS['link'], $to ) . "', '', '', '" . mysqli_real_escape_string($GLOBALS['link'], $body ) . "', '" . mysqli_real_escape_string($GLOBALS['link'], $fromname ) . "', '" . mysqli_real_escape_string($GLOBALS['link'], $from ) . "', '" . mysqli_real_escape_string($GLOBALS['link'], $key ) . "' )" );
        
    }
    
}

function sendHTMLMail( $to, $subject, $body,$from = "info@emergencyskills.com", $fromname = "Emergency Skills, Inc", $key = "" )
{
    global $fromautomated, $session_iscorp;
    if( $session_iscorp == TRAININGSITES )
return;

    if( !$fromname )
        $fromname = $from;
    require_once "class.phpmailer.php";
    $spl = explode( ",", $to );
    $mail = new PHPMailer();
    $mail->IsSMTP(); // Added later to fix html format by Sanjoy Dey
    $mail->Host = 'localhost'; // Added later to fix html format by Sanjoy Dey
    $mail->SMTPAuth = false; // Added later to fix html format by Sanjoy Dey
    $mail->Port = 25; // Added later to fix html format by Sanjoy Dey
    $mail->From = $from;
    $mail->IsHTML(true );
    $mail->FromName = $fromname;
    $mail->Sender = $from;
    $mail->AddReplyTo( $from );
    $mail->Subject = $subject;
    $mail->Body    = $body;
    foreach( $spl as $s )
    {
        $s = trim( $s ) ;
        if( $s )
            $mail->AddAddress($s);
    }
    $mail->Send();


    if( $fromautomated || 1 )
    {
//        mysql> create table automatedemails ( datesent datetime, subject varchar( 255 ), torecipients text, body text, bccrecipients text, ccrecipients text );

        db_query( "insert into automatedemails( fromautomated, datesent, subject, torecipients, ccrecipients, bccrecipients, body, fromname, fromemail, emailkey ) values ( '$fromautomated', now(), '" . mysqli_real_escape_string($GLOBALS['link'], $subject ) . "', '" . mysqli_real_escape_string($GLOBALS['link'], $to ) . "', '', '', '" . mysqli_real_escape_string($GLOBALS['link'], $body ) . "', '" . mysqli_real_escape_string($GLOBALS['link'], $fromname ) . "', '" . mysqli_real_escape_string($GLOBALS['link'], $from ) . "', '" . mysqli_real_escape_string($GLOBALS['link'], $key ) . "' )" );
        
    }
        
    
}

function sendFormattedHTMLMail( $to, $subject, $body,$from, $fromname = "", $convertbreaks = true, $removesincerely = false, $replacements = array() )
{
    global $overrideemailinbottom, $bottomright, $fromautomated; 
    ob_start();
    include "gethtmlbodytop.php";
    if( $convertbreaks )
        echo nl2br( $body );
    else
        echo $body;
    include "gethtmlbodybottom.php";
    $htmlbody = ob_get_contents();
    ob_end_clean();
    foreach( $replacements as $key=>$val )
{
    $htmlbody = str_replace( $key, $val, $htmlbody );
}

    if( !$fromname )
        $fromname = $from;
    require_once "class.phpmailer.php";
    $spl = explode( ",", $to );
    $mail = new PHPMailer();
    $mail->IsSMTP(); // Added later to fix html format by Sanjoy Dey
    $mail->Host = 'localhost'; // Added later to fix html format by Sanjoy Dey
    $mail->SMTPAuth = false; // Added later to fix html format by Sanjoy Dey
    $mail->Port = 25; // Added later to fix html format by Sanjoy Dey
    $mail->From = $from;
    $mail->IsHTML(true);
    if( $fromname )
        $mail->FromName = $fromname;
    $mail->Sender = $from;
    $mail->AddReplyTo( $from );
    $mail->Subject = $subject;
    $mail->Body    = $htmlbody;
    foreach( $spl as $s )
    {
        $s = trim( $s ) ;
        if( $s )
            $mail->AddAddress($s);
    }
    $mail->Send();


    if( $fromautomated || 1 )
    {
//        mysql> create table automatedemails ( datesent datetime, subject varchar( 255 ), torecipients text, body text, bccrecipients text, ccrecipients text );

        db_query( "insert into automatedemails( datesent, subject, torecipients, ccrecipients, bccrecipients, body, fromname, fromemail ) values ( now(), '" . mysqli_real_escape_string($GLOBALS['link'], $subject ) . "', '" . mysqli_real_escape_string($GLOBALS['link'], $to ) . "', '', '', '" . mysqli_real_escape_string($GLOBALS['link'], $body ) . "', '" . mysqli_real_escape_string($GLOBALS['link'], $fromname ) . "', '" . mysqli_real_escape_string($GLOBALS['link'], $from ) . "' )" );
        
    }

}

function sendHTMLMail_Office( $to, $subject, $body,$from, $fromname = "", $key = "" )
{
    global $fromautomated;
    if( $session_iscorp == TRAININGSITES )
return;

    if( !$fromname )
        $fromname = $from;

    if( $from == "info@emergencyskills.com" )
    {
require_once "class.phpmailer.php";
        date_default_timezone_set('Etc/UTC');
        $mail = new PHPMailer(true);
        $mail->IsSMTP();
        $mail->Host = 'smtp.office365.com';
        $mail->Port       = 587;
        $mail->SMTPSecure = 'tls';
        $mail->SMTPAuth   = true;
        $mail->Username = 'info@emergencyskills.com';
        $mail->Password = 'Alive!1504';
        $mail->Port       = 587;
        $mail->From       = $mail->Username;  //mandatory and identical to Username property
        $mail->FromName       = $fromname?$fromname:"Emergency Skills";  //mandatory and identical to Username property
        $mail->IsHTML( true );
        
        $mail->Subject = $subject;
        $mail->Body = $body;
//        $mail->addAddress('rachelc@gmail.com');
        $spl = explode( ",", $to );
        foreach( $spl as $s )
        {
            echo( "$s" );
            $s = trim( $s ) ;
            if( $s )
                $mail->addAddress($s);
        }
        echo( "a" );
        if(!$mail->send()) {
            echo 'Message could not be sent.';
            echo 'Mailer Error: ' . $mail->ErrorInfo;
        } else {
            echo 'Message has been sent';
        }
        
        
    }
    else
    {
            // using the old stuff
        require_once "class.phpmailer.php";
        $spl = explode( ",", $to );
        $mail = new PHPMailer();
        $mail->From = $from;
        $mail->IsHTML(true );
        if( $fromname )
            $mail->FromName = $fromname;
        $mail->Sender = $from;
        $mail->AddReplyTo( $from );
        $mail->Subject = $subject;
        $mail->Body    = $body;
        foreach( $spl as $s )
        {
            $s = trim( $s ) ;
            if( $s )
                $mail->AddAddress($s);
        }
        $mail->Send();
    }
    // require_once "class.phpmailer.php";
    // $spl = explode( ",", $to );
    // $mail = new PHPMailer();
    // $mail->From = $from;
    // $mail->IsHTML(true );
    // $mail->FromName = $fromname;
    // $mail->Sender = $from;
    // $mail->AddReplyTo( $from );
    // $mail->Subject = $subject;
    // $mail->Body    = $body;
    // foreach( $spl as $s )
    // {
    //     $s = trim( $s ) ;
    //     if( $s )
    //         $mail->AddAddress($s);
    // }
    // $mail->Send();

    if( $fromautomated || 1 )
    {
//        mysql> create table automatedemails ( datesent datetime, subject varchar( 255 ), torecipients text, body text, bccrecipients text, ccrecipients text );

        db_query( "insert into automatedemails( fromautomated, datesent, subject, torecipients, ccrecipients, bccrecipients, body, fromname, fromemail, emailkey ) values ( '$fromautomated', now(), '" . mysqli_real_escape_string($GLOBALS['link'], $subject ) . "', '" . mysqli_real_escape_string($GLOBALS['link'], $to ) . "', '', '', '" . mysqli_real_escape_string($GLOBALS['link'], $body ) . "', '" . mysqli_real_escape_string($GLOBALS['link'], $fromname ) . "', '" . mysqli_real_escape_string($GLOBALS['link'], $from ) . "', '" . mysqli_real_escape_string($GLOBALS['link'], $key ) . "' )" );
        
    }
        
    
}

function sendMail_Office( $to, $subject, $body,$from, $fromname = "" )
{
    global $fromautomated;

    
    if( !$fromname )
        $fromname = $from;

require_once "class.phpmailer.php";
        date_default_timezone_set('Etc/UTC');
        $mail = new PHPMailer(true);
        $mail->IsSMTP();
        $mail->Host = 'smtp.office365.com';
        $mail->Port       = 587;
        $mail->SMTPSecure = 'tls';
        $mail->SMTPAuth   = true;
        $mail->Username = 'info@emergencyskills.com';
        $mail->Password = 'Alive!1504';
        $mail->Port       = 587;
        $mail->From       = $mail->Username;  //mandatory and identical to Username property
        $mail->FromName       = $fromname?$fromname:"Emergency Skills";  //mandatory and identical to Username property
        
        $mail->Subject = $subject;
        $mail->Body = $body;
//        $mail->addAddress('rachelc@gmail.com');
        $spl = explode( ",", $to );
        foreach( $spl as $s )
        {
            echo( "$s" );
            $s = trim( $s ) ;
            if( $s )
                $mail->addAddress($s);
        }
        echo( "a" );
        if(!$mail->send()) {
            echo 'Message could not be sent.';
            echo 'Mailer Error: ' . $mail->ErrorInfo;
        } else {
            echo 'Message has been sent';
        }
exit;    
        
}

function sendFormattedHTMLMail_Office( $to, $subject, $body,$from, $fromname = "", $convertbreaks = true, $removesincerely = false, $replacements = array() )
{
    global $overrideemailinbottom, $bottomright, $fromautomated; 

    ob_start();
    include "gethtmlbodytop.php";
    if( $convertbreaks )
        echo nl2br( $body );
    else
        echo $body;
    include "gethtmlbodybottom.php";
    $htmlbody = ob_get_contents();
    ob_end_clean();
    foreach( $replacements as $key=>$val )
{
    $htmlbody = str_replace( $key, $val, $htmlbody );
}

    if( !$fromname )
        $fromname = $from;

    sendHTMLMail_Office( $to, $subject, $htmlbody, $from, $fromname );

}
function resendScheduledEmail( $crow, $comrow, $em, $companyname, $cont )
{
    global $class_names, $allclass_names;
    if( $comrow["iscorp"] == TRAININGSITES )
        return;
    ob_start();
    include "gethtmlbodyforscheduleclass.php";
    $htmlbody = ob_get_contents();
    ob_end_clean();
    
    $subj = "Training Class Scheduled";
    
    sendHTMLMail( $em, $subj, $htmlbody, "info@emergencyskills.com" ); 
//        sendHTMLMail( "rcox@vireo.org", "New class scheduled: $em", $htmlbody, "info@emergencyskills.com" ); 
    $sql = "select userid from user where id = $crow[addedby]";
    $auser = db_query_first_cell($sql);

    if( $auser != $em )
sendHTMLMail( $auser, $subj, $htmlbody, "info@emergencyskills.com" ); 

    if( $crow["alt_email"] )
        sendHTMLMail( $crow["alt_email"], $subj, $htmlbody, "info@emergencyskills.com" ); 
    if( $crow["principalemail"] )
        sendHTMLMail( $crow["principalemail"], $subj, $htmlbody, "info@emergencyskills.com" ); 
    
    //    sendHTMLMail( "rachelc@gmail.com", $subj, $htmlbody, "info@emergencyskills.com" ); 
    sendHTMLMail( "barbara@emergencyskills.com", $subj, $htmlbody, "info@emergencyskills.com" ); 
    
}


function sendToAttendees( $id, $dontredirect = false )
{
    global $allclass_names;
    $crow = getClassRow( $id );
    $comrow = getCompanyRow( $crow["companyid"] );
    if( $comrow["iscorp"] ) $class_names = $allclass_names[1]; else $class_names = $allclass_names[0];
    if( !OKToSendEmails( $comrow["iscorp"] ) )
        return;

    $companyname = getCompanyName( $crow["companyid"] );
    $em = getClassEmail( $crow );
    $cont = getClassContact( $crow );

    $isaed = db_query_first_cell( "select isaed from esioptionvalues where shortname = '".$crow["code"]."'" );
    $pickupdate = getBusinessDay( $crow["startdate"], 1 );
    $deliverydate = getBusinessDay( $crow["startdate"], -1 );

    $locstr = getSchoolStr( "Training Location", $comrow["iscorp"] ) . ":";

    if( $crow["remote"] ) $locstr = "";
    
$basebody = "
Reminder: You are confirmed to attend the following class:

<b>Program Details</b>
Class #: ".$id." 
Program: ".$class_names[$crow["code"]]." 
Date: ".fixdatefordisplay( $crow["startdate"], true )."
Time: ".getFormattedTime( $crow["startdate"] )." - $crow[enddate]
TIMESLOT
<font color='red'>NOTE:  LATECOMERS WILL NOT BE PERMITTED ENTRY</font>

Participant Location: $companyname
".$locstr."" .getTrainingAddress( $crow ). "  $crow[training_room_number]
Location Contact: $crow[firstname] $crow[lastname]
Contact Phone Number: ".formatPhone( $crow["phone"] ) ."

" .($crow["remote"]&& $crow["teamslink"]?"Class Link: <a href='$crow[teamslink]'>$crow[teamslink]</a>":"" ) . "
    
Registrant's Name: REGNAME
Registrant's Phone Number: REGPHONE
Registrant's Email: REGEMAIL

TRANSPORTATION INFORMATION:
Parking/Security: $crow[parking_security]

Nearest Subway Line/Station: $crow[nearest_subway]
 
";

if( !$comrow["iscorp"] ) 
    $basebody .= "If you must cancel, please FORWARD this email to  noah@emergencyskills.com with the subject CANCEL REQUEST, or call Emergency Skills, Inc. at 212-564-6833.  If this class is being held at your school, <font color='red'>first</font> alert the school contact as your seat will be set to open registration and your school may not be able to replace you with a co-worker.  There may be a penalty for cancellations received after 5 business days prior to the program.

";
else 
    $basebody .= "“If you must cancel, please FORWARD this email to dzamos@emergencyskills.com with the subject CANCEL REQUEST, or call Emergency Skills, Inc. at 212-564-6833. There may be a penalty for cancellations received after 5 business days prior to the program.

";

$subj = "Emergency Skills, Inc. Training Program";


$attendees = get_attendees( $id );
foreach( $attendees as $arow )
{
    $attendee = get_attendee($arow["responderid"]);
    $body = $basebody;
    $body = str_replace( "REGNAME", $attendee["firstname"] . " " . $attendee["lastname"], $body );
    $body = str_replace( "REGEMAIL", $attendee["email"], $body );
    $body = str_replace( "REGPHONE", formatPhone( $attendee["dayphone"] ), $body );
    $body = str_replace( "TIMESLOT", $arow["timeslot"]?"<font color='red'>YOUR Timeslot: ".$arow["timeslot"] . "</font>":"", $body );

    $replacements = array();
    if( !$comrow['iscorp'] )
{
    $replacements["esialive@emergencyskills.com"] = "noah@emergencyskills.com";
}
else
{
    $replacements["esialive@emergencyskills.com"] = "barbara@emergencyskills.com";
}
    if( $attendee["email"] )
        sendFormattedHTMLMail( $attendee["email"], $subj, $body, "info@emergencyskills.com", "", true, false, $replacements );
    if( !$dontredirect && $attendee["email"] )
        echo( "sending reminder to: $attendee[email]\n" );
}
if( !$dontredirect )
{
Header( "Location: class_detail.php?id=$id&sent=1" );
exit;
}
}

function OKToSendEmails( $classtype = "" )
{
global $session_iscorp;
if( $classtype == TRAININGSITES || $session_iscorp == TRAININGSITES )
        return false;
    return true;
}


function sendOLDTrainerConfirmEmail( $trainerid, $crow, $tcf = false )
{
    global $allclass_names;
    $classemail = getClassEmail( $crow );
$trainername = getUserName( $trainerid );
$classcontact = getClassContact( $crow );
$company = getCompanyRow( $crow["companyid"] );
$class_names = $allclass_names[$company["iscorp"]];
    $numattendees = db_query_first_cell( "Select count(*) from responder_to_class where classid = $crow[id]" );
    if( $tcf )
    {
        $confirmid = "$crow[id]&tcf=1";
        $trainers = getTrainers( $crow["id"] );
        $trow = array_pop( $trainers );
        $trainername = $trow["first_name"] . " " . $trow["last_name"];
    }
    else
    {
        $confirmid = db_query_first_cell( "Select id from trainer_to_class where classid = $crow[id] and trainerid = '$trainerid'" );
        if( !$confirmid )
            $confirmid = db_query_insert_id( "insert into trainer_to_class ( trainerid, classid, lastmodified ) values ( '$trainerid', '$crow[id]', now() )" );
    }

    $tcfacultyid = $crow['tcfacultyid'];
    if( $tcfacultyid )
        $tcfline = "\nTC Faculty: " . getUserName( $tcfacultyid );
    if( $tcf )
{
    $mon = " - Monitoring: $trainername"; 
}
    $astcf = $tcf?"as a TC Faculty":"for training";
    $body = "Emergency Skills, Inc. has scheduled you $astcf on ".date( "m/d/Y", strtotime( $crow["startdate"] )).". Please click below to confirm:

https://".SUB_DOE.".". URL_WITHOUT_SUBDOMAIN ."/confirmtraining.php?id=$confirmid&go=".time()."

Instructor: $trainername $tcfline
Date: ".( date( "m/d/Y h:i a", strtotime( $crow["startdate"] )))." ".getEndDateStr( $crow["enddate"] )."
Class: " . $class_names[$crow["code"]]."

Number of Students: " . $crow["maxattendees"] ."

Number of Instructors: " . $crow["numtrainers"]."
".getSchoolStr( "School", $company["iscorp"] ).": ".$company["companyname"]." ($company[schoolcode])

Contact: $classcontact ".formatPhone( $crow["phone"] )." $classemail
Parking: $crow[parking_security]
Equipment Delivery Instructions: $crow[equipdelivinstr]

".getSchoolStr( "Training Location", $company["iscorp"] ).": " .getTrainingAddress( $crow ). " $crow[training_room_number]
".getSchoolStr( "School Entrance", $company["iscorp"] ).": $crow[school_entrance]
Subway: $crow[nearest_subway]
Notes: $crow[instructornotes]
";
    if( !$company["iscorp"] )
    {
        $body .= "Emergency Contact: $crow[emergency_contact]
Emergency Cell: $crow[emergency_cell]
";
    }

    $trow = getUserRow( $trainerid );
//    if( !$trow["national"] )
//    {
        $em = getUserEmail( $trainerid );
        sendMail( $em, "Class Scheduled $crow[startdate] $mon", $body, "info@emergencyskills.com", "Confirm Schedule" ); 
//        sendMail( "cox@vireo.org", "Class Scheduled $crow[startdate]", $body, "info@emergencyskills.com" ); 
        sendMail( "safetyplan@emergencyskills.com, barbara@emergencyskills.com", "Class Scheduled $crow[startdate] $mon", $body, "info@emergencyskills.com", "Confirm Schedule" );
//        echo( "would mail to $em<br>" );
//    }

}

function sendTrainerConfirmEmail( $trainerid, $crow, $tcf = false )
{
    global $allclass_names;
    $classemail = getClassEmail( $crow );
$trainername = getUserName( $trainerid );
$classcontact = getClassContact( $crow );
$company = getCompanyRow( $crow["companyid"] );
$class_names = $allclass_names[$company["iscorp"]];
    $numattendees = db_query_first_cell( "Select count(*) from responder_to_class where classid = $crow[id]" );
    if( $tcf )
    {
        $confirmid = "$crow[id]&tcf=1";
        $trainers = getTrainers( $crow["id"] );
        $trow = array_pop( $trainers );
        $trainername = $trow["first_name"] . " " . $trow["last_name"];
    }
    else
    {
        $confirmid = db_query_first_cell( "Select id from trainer_to_class where classid = $crow[id] and trainerid = '$trainerid'" );
        if( !$confirmid )
            $confirmid = db_query_insert_id( "insert into trainer_to_class ( trainerid, classid, lastmodified ) values ( '$trainerid', '$crow[id]', now() )" );
    }

    if( !OKToSendEmails( $company["iscorp"] ) )
        return;

    $tcfacultyid = $crow["tcfacultyid"];
    if( $tcfacultyid )
        $tcfline = "\nTC Faculty: " . getUserName( $tcfacultyid );
    $astcf = $tcf?"as a TC Faculty":"for training";

    $contents = file_get_contents( "confirmemail/confirmemail.html" );
    
    $peak = isPeakDate( $crow["startdate"])?"\n<font color='red'>ALERT! This is a PEAK TRAINING DAY!</font><br><br>\n":"";
    
    $contents = str_replace( "PEAKDATE", $peak, $contents );
    $contents = str_replace( "CONFIRMDATE", date( "m/d/Y", strtotime( $crow["startdate"] )), $contents );
    $contents = str_replace( "CONFIRMLINK", "https://".SUB_DOE.".". URL_WITHOUT_SUBDOMAIN ."/confirmtraining.php?id=$confirmid&go=".time(), $contents );
    

    $contents = str_replace( "CLASSDATE", date( "m/d/Y h:i a", strtotime( $crow["startdate"] ))." ".getEndDateStr( $crow["enddate"] ), $contents );
    $contents = str_replace( "CLASSNAME", $class_names[$crow["code"]], $contents );
    $contents = str_replace( "NUMSTUDENTS", $crow["maxattendees"], $contents );
    $contents = str_replace( "NUMTRAINERS", $crow["numtrainers"], $contents );
    $contents = str_replace( "CLASSSTR", getSchoolStr( "School", $company["iscorp"] ), $contents );
    $contents = str_replace( "COMPANYNAME", $company["companyname"]." ($company[schoolcode]) ", $contents );
    $contents = str_replace( "CLASSCONTACT", "$classcontact ".formatPhone( $crow["phone"] )." $classemail", $contents );
    $contents = str_replace( "PARKING", $crow["parking_security"], $contents );
    $contents = str_replace( "EQUIPMENTDELIVERY", $crow["equipdelivinstr"], $contents );
    $contents = str_replace( "ASTCF", $astcf, $contents );
    if( $crow["remote"] )
{
    $contents = str_replace( "TRAININGSTR", "Teams Link", $contents );
    $contents = str_replace( "TRAININGLOCATION", "<a href='$crow[teamslink]'>$crow[teamslink]</a>", $contents );
    $contents = str_replace( "ENTRANCESTR", "Intructor Location", $contents );
    $contents = str_replace( "SCHOOLENTRANCE", "REMOTE CLASS", $contents );
}
    else
{
    $contents = str_replace( "TRAININGSTR", getSchoolStr( "Training Location", $company["iscorp"] ), $contents );
    $contents = str_replace( "TRAININGLOCATION", getTrainingAddress( $crow ), $contents );
    $contents = str_replace( "ENTRANCESTR", getSchoolStr( "School Entrance", $company["iscorp"] ), $contents );
    $contents = str_replace( "SCHOOLENTRANCE", $crow["school_entrance"], $contents );
}
    
    
    $contents = str_replace( "SUBWAY", $crow["nearest_subway"], $contents );
    $contents = str_replace( "NOTES",  $crow["instructornotes"], $contents );
    if( !$company["iscorp"] )
    {
        $contents = str_replace( "EMERGENCYCONTACT",  "<Br>Emergency Contact: $crow[emergency_contact]<Br>Emergency Cell: $crow[emergency_cell]", $contents );
    }
    else
    {
        $contents = str_replace( "EMERGENCYCONTACT",  "", $contents );
    }

    $trow = getUserRow( $trainerid );
        $em = getUserEmail( $trainerid );
        sendHTMLMail( $em, "Class Scheduled $crow[startdate]", $contents, "info@emergencyskills.com", "Confirm Schedule" ); 
//        sendHTMLMail( "cox@vireo.org", "Class Scheduled $crow[startdate]", $contents, "info@emergencyskills.com" ); 

         sendHTMLMail( "safetyplan@emergencyskills.com, barbara@emergencyskills.com", "Class Scheduled - $trainername - $crow[startdate]", $contents, "info@emergencyskills.com", "Confirm Schedule" );

}

function getCancelledTrainers( $classid )
{
return db_query_rows( "select * from cancelled_trainers where classid = '$classid'" );
}

function getsetting( $name )
{
    //echo( "select value from namevaluepair where name = '$name' " );
    return db_query_first_cell( "select value from namevaluepair where name = '$name' " );
}

function sendTrainerNamesEmail( $id )
{
    global $class_names;
    $trainers = getTrainers( $id );
    $crow = getClassRow( $id );
    $comrow = getCompanyRow( $crow["companyid"] );
    if( $crow["code"] == "Inspections" ) return; // no inspections
    if( !OKToSendEmails( $comrow["iscorp"] ) )
        return;
    if( !$crow["accepted"] )
        return;
    if( !count( $trainers ) )
        return;

    ////////////////////////////////////
    //REVISION LOG: 2024-12-03 T.SHANNON
    ////////////////////////////////////
    //Build DOE Email Body
    if( $comrow["iscorp"] == 0 ) {
        $body = "Emergency Skills, Inc. has scheduled the following instructor(s) for your ".getFormattedDateWTime( $crow[startdate] )." ".$class_names[$crow["code"]]." course:\n";
        foreach( $trainers as $trow ) {
            if( $comrow["iscorp"] == 0 ) {
                $e = $trow["userid"];
                $body .= "$trow[first_name] $trow[last_name]\n";
            }
        }
        $body .= "\nInstructors arrive 30 minutes before the class start time to prepare for training. Please have the training equipment in the room prior to their arrival and ensure that they have access to agreed-upon A/V equipment at that time.";
        $body .= "\n\nREMINDER: ESI Instructors DO NOT travel with laptops or other A/V equipment.";
        $body .= "\n\nLocation: $comrow[companyname]";
        $body .= "\n".getSchoolStr( "Training Location", $comrow["iscorp"] ).": ".getTrainingAddress( $crow );
        $body .= "\n\n".getSchoolStr( "School Entrance", $comrow["iscorp"] ).": $crow[school_entrance]";
        $body .= "\n\nNOTE:  LATECOMERS WILL NOT BE PERMITTED ENTRY";
        $body .= "\n\nThis message is generated automatically.  If you need to make any changes, corrections, or additions to this class, please call our office at 212-564-6833. To ensure that all registrations are processed, requests to add attendees by email WILL NOT be accepted.";
        $body .= "\n\nThank you for choosing Emergency Skills to train your staff.";
        
    }
    //Build Corp Email Body
    if( $comrow["iscorp"] == 1 ) {
        $body = "Emergency Skills, Inc. has scheduled the following instructor(s) for your ".getFormattedDateWTime( $crow[startdate] )." ".$class_names[$crow["code"]]." course:\n";
        foreach( $trainers as $trow ) {
            if( $comrow["iscorp"] == 1 ) {
                $e = $trow["userid"];
                $body .= "$trow[first_name] $trow[last_name]\n";
            }
        }
        $body .= "\nInstructors arrive 15 - 20 minutes before the class start time to prepare for training.  Please have the training equipment in the room prior to their arrival and ensure that they have access to agreed-upon A/V equipment at that time.";
        $body .= "\n\nREMINDER: ESI Instructors DO NOT travel with laptops or other A/V equipment.";
        $body .= "\n\nLocation: $comrow[companyname]";
        $body .= "\n".getSchoolStr( "Training Location", $comrow["iscorp"] ).": ".getTrainingAddress( $crow );
        $body .= "\n\n".getSchoolStr( "School Entrance", $comrow["iscorp"] ).": $crow[school_entrance]";
        $body .= "\n\nThis message is generated automatically.  If you need to make any changes, corrections, or additions to this class, please call our office at 212-564-6833.";
        $body .= "\n\nThank you for choosing Emergency Skills to train your staff.";
    }


    //Below is the working code for archive
//     $body = "Emergency Skills, Inc. has scheduled the following instructor(s) for your ".getFormattedDateWTime( $crow[startdate] )." ".$class_names[$crow["code"]]." course:\n";
    
//     foreach( $trainers as $trow )
//     {
// if( $comrow["iscorp"] )
//     $e = $trow[userid];
//         $body .= "$trow[first_name] $trow[last_name] $e\n";
//     }
    
// if( $comrow["iscorp"] )
//     {
// $body .=  "NOTE: Instructor email addresses should be used ONLY for COVID security purposes.  Any questions about the course should be directed to tcadmin@emergencyskills.com.\n";
//     }


//     $body .= "\n\n$comrow[companyname]".getSchoolStr( "Training Location", $comrow["iscorp"] ).": " .getTrainingAddress( $crow ). " ".getSchoolStr( "School Entrance", $comrow["iscorp"] ).": $crow[school_entrance]";


//     if( !$crow["remote"] )
// {
//     $body.= "
// Instructors arrive 15 - 20 minutes before the class start time to prepare for training.  If possible, please have the equipment in the training room prior to their arrival.\n\n";
// }
//     else
// {
//     $body .= "Instructors will log on and open the meeting at least 15 minutes prior to the start of class.\n\n";
// }

    

// $body .= "This message is generated automatically.  If you need to make any changes, corrections, or additions to this class, please call our office at 212-564-6833. To ensure that all registrations are processed, requests to add attendees by email WILL NOT be accepted.

// Thank you for choosing Emergency Skills to train your staff.
// ";
    
    ////////////////////////////////////
    //END REVISION: 2024-12-03 T.SHANNON
    ////////////////////////////////////

    $subj = "Emergency Skills, Inc. - Course Instructor Name(s)";
    //    sendMail( "rachelc@gmail.com", $subj, $body, "info@emergencyskills.com" );
    ////////////////////////////////////
    //REVISION LOG: 2024-11-13 T.SHANNON
    ////////////////////////////////////
    //corp is 1, doe is 0
    if( $comrow["iscorp"] == 1 ) {
        sendMail( $crow["email"], $subj, $body, "dzamos@emergencyskills.com" );
        sendMail( "barbara@emergencyskills.com", $subj, $body, "dzamos@emergencyskills.com" );
        if( $crow["alt_email"] ) {
            sendMail( $crow["alt_email"], $subj, $body, "dzamos@emergencyskills.com" );
        }
        if( $crow["principalemail"] ) {
            sendMail( $crow["principalemail"], $subj, $body, "dzamos@emergencyskills.com" );
        }
            
    } elseif( $comrow["iscorp"] == 0 ) {
        sendMail( $crow["email"], $subj, $body, "noah@emergencyskills.com" );
        sendMail( "barbara@emergencyskills.com", $subj, $body, "noah@emergencyskills.com" );
        if( $crow["alt_email"] ) {
            sendMail( $crow["alt_email"], $subj, $body, "noah@emergencyskills.com" );
        }
        if( $crow["principalemail"] ) {
            sendMail( $crow["principalemail"], $subj, $body, "noah@emergencyskills.com" );
        }
    } else {
        sendMail( $crow["email"], $subj, $body, "info@emergencyskills.com" );
        sendMail( "barbara@emergencyskills.com", $subj, $body, "info@emergencyskills.com" );
        if( $crow["alt_email"] ) {
            sendMail( $crow["alt_email"], $subj, $body, "info@emergencyskills.com" );
        }
        if( $crow["principalemail"] ) {
            sendMail( $crow["principalemail"], $subj, $body, "info@emergencyskills.com" );
        }
    }
    
    // sendMail( $crow["email"], $subj, $body, "info@emergencyskills.com" );
    // sendMail( "barbara@emergencyskills.com", $subj, $body, "info@emergencyskills.com" );
    // if( $crow["alt_email"] )
    //     sendMail( $crow["alt_email"], $subj, $body, "info@emergencyskills.com" );
    // if( $crow["principalemail"] )
    //     sendMail( $crow["principalemail"], $subj, $body, "info@emergencyskills.com" );

    ////////////////////////////////////
    //END REVISION: 2024-11-13 T.SHANNON
    ////////////////////////////////////
    
}

function getBuildingsForLocation( $locationcode )
{
    if( !$locationcode )
        return array();
    return db_query_rows( "select buildings.* from location_to_building, buildings where buildings.buildingcode = location_to_building.buildingcode and locationcode = '$locationcode'" );
}
function getBuildingCodesForLocation( $locationcode )
{
    if( !$locationcode )
        return "";
    return db_query_first_cell( "select group_concat( buildingcode ) from location_to_building where locationcode = '$locationcode'" );
}

function getBuildingPulldown( $companyid, $currentvalue = "", $selectname = 'buildingcode', $ext = " class='copy' ", $addblank = "" )
{
    $locationcode = db_query_first_cell( "select locationcode from company_esi where id = $companyid" );
    $buildings = getBuildingsForLocation( $locationcode );
    if( count($buildings) )
    {
        $str = "<select name='$selectname' $ext>" ;
        if( $addblank )
            $str .= "<option value=''>Please Choose</option>";
        foreach( $buildings as $brow )
        {
            $str .= "<option ".($brow["buildingcode"]==$currentvalue?"SELECTED":"")." value='$brow[buildingcode]'>$brow[buildingcode]: $brow[buildingname], $brow[address], $brow[zip]</option>";
        }
        $str .= "</select>" ;
    }
    return $str;
}

function updateLocationCode( $companyid, $locationcode )
{
    $currlocationcode = db_query_first_cell( "select locationcode from company_esi where id = $companyid" );
    if( $locationcode && $currlocationcode && $currlocationcode != $locationcode )
    {
        db_query( "insert into oldlocationcodes ( companyid, locationcode, movedate ) values ( '$companyid', '$currlocationcode', now() )" );
    }
    db_query( "update company_esi set locationcode = '$locationcode' where id = '$companyid'" );
}

function addBuildingCode( $buildingcode, $locationcode, $buildingname, $address, $city, $state, $zip )
{
    $exists = db_query_first_cell( "select id from buildings where buildingcode = '$buildingcode'" );
    if( !$exists )
    {
            // this needs to be added 
        db_query( "insert into buildings ( buildingcode, buildingname, address, city, state, zip ) values ( '$buildingcode', '".mysqli_real_escape_string($GLOBALS['link'], $buildingname )."', '".mysqli_real_escape_string($GLOBALS['link'], $address )."', '".mysqli_real_escape_string($GLOBALS['link'], $city )."', '".mysqli_real_escape_string($GLOBALS['link'], $state )."', '".mysqli_real_escape_string($GLOBALS['link'], $zip )."' ) " );
    }
    $exists = db_query_first_cell( "select locationcode from location_to_building where locationcode = '$locationcode' and buildingcode = '$buildingcode'" );
    if( !$exists )
    {
        db_query( "insert into location_to_building ( locationcode, buildingcode ) values ( '$locationcode', '$buildingcode' ) " );        
    }
    
}

function getDBN( $schoolcode )
{
return str_replace( "-", "", $schoolcode );
}

function needsMonitoring( $trainerid )
{
    $max = db_query_first_cell( "select max( nextmonitoringdate ) from monitoring where trainerid = $trainerid" );
    if( $max )
    {
        $max = strtotime( $max );
        $onemonthfromnow = mktime( 0,0,0,date( "m" ) + 1 );
        if( $max < $onemonthfromnow )
            $col = "style='color:red'";
    }
    else
        $col = "style='color:red'";
    return $col;
}

function getUrlPrefix( $iscorp = -4 )
{
    global $session_iscorp;
    if( $iscorp == -4 )
        $iscorp = $session_iscorp;
    if( !$iscorp ) return SUB_DOE;
    if( $iscorp == 2 ) return "prospects";
    if( $iscorp == TRAININGSITES ) return "training";
    if( $iscorp == AGING ) return "dfta";
    return "clients";
}
function getSessionTypeDisplay( $iscorp = -4 )
{
    global $session_iscorp;
    if( $iscorp == -4 )
        $iscorp = $session_iscorp;
    if( !$iscorp ) return strtoupper(SUB_DOE);
    if( $iscorp == 2 ) return "Prospect";
    if( $iscorp == TRAININGSITES ) return "Training";
    return "Corporate";
}

function getDashCache( $type, $startdate, $sql )
{
    global $link; 
$currdate = date( "m-d-Y" );
$res = mysqli_query( $link, "select count from dashcache where type = '$type' and startdate = '$startdate' and currdate = '$currdate' " );
$num = mysqli_num_rows( $res );
if( !$num ) 
{
$val = db_query_first_cell( $sql );
db_query( "insert into dashcache ( type, startdate, currdate, count ) values ( '$type', '$startdate', '$currdate', '$val' ) " );
return $val;
}
$r = mysqli_fetch_array( $res );
return $r["count"];
}

function hoursThisWeek( $trainerid, $crow, $allowthisclass= false )
{
$date = strtotime( $crow["startdate"] );
$dow = date( "N", $date );
// tuesday is 2, so monday would be DATE - ( 2 - 1 ) days
// saturday is 6, so monday would be DATE - ( 6 - 1 ) days
// tuesday is 2, so sunday would be DATE + ( 7 - 2 ) days
// saturday is 6, so sunday would be DATE + ( 7- 6 ) days
$monday = mktime( 0,0,0,date( "m", $date ), date( "d", $date ) - ( $dow - 1 ), date( "Y", $date ) );
$sunday = mktime( 23,59,59,date( "m", $date ), date( "d", $date ) + ( 7 - $dow ), date( "Y", $date ) );

$monday = date( "Y-m-d H:i:s", $monday );
$sunday = date( "Y-m-d H:i:s", $sunday );

    if( $allowthisclass )  $ext = "";
    else
        $ext = " and class.id <> $crow[id] ";

$classesforweek = db_query_rows( "select class.startdate, class.enddate, iscorp from class, trainer_to_class, company_esi where trainer_to_class.trainerid = $trainerid and class.id = trainer_to_class.classid and class.canceldate is null and startdate >= '$monday' and startdate <= '$sunday' $ext and company_esi.id = class.companyid" );

$hours = 0; 
foreach( $classesforweek as $c )
{
        $hours += hoursInClass( $c, $c["iscorp"] );
}


    if( $allowthisclass )  $ext = "";
    else
        $ext = " and class.id <> $crow[id] ";
    
$classesforweek = db_query_rows( "select class.*, iscorp from class, company_esi where tcfacultyid = $trainerid and class.canceldate is null and tcfacultyconfirmeddate is not null and startdate >= '$monday' and startdate <= '$sunday' $ext and company_esi.id = class.companyid" );

foreach( $classesforweek as $c )
{
        $hours += hoursInClass( $c, $c["iscorp"] );
}

return $hours;
}

function hoursInClass( $c,$iscorp )
{
        if( !$iscorp ) 
        {
            if( $c["code"] == "cohfa" ) return 4.5;
            return 7;
        }
        else
        {
            $enddate = strtotime( date( "F d, Y ", strtotime( $c["startdate"] ) ) .  $c["enddate"] );
            $thishours = $enddate - strtotime( $c["startdate"] );
            $thishours = $thishours / (60 * 60); // seconds , minutes
//            echo( "this: $thishours,  <br>" );
            $thishours += .5;
            return $thishours;
        }
}

function getUpcomingTrainingInBuilding( $row )
{
    global $thisusersrow;
    if( !$row["locationcode"] ) return "";
    $buildings = db_query_array( "select buildingcode from location_to_building where locationcode = '$row[locationcode]'", "buildingcode", "buildingcode" );

    $bstr = "'-111'";
    foreach( $buildings as $b )
    {
        $bstr .= ", '$b'";
    }
    //    echo( "select startdate, class.id as classid from class, location_to_building b, company_esi c  where class.companyid = c.id and b.buildingcode in ( $bstr ) and b.locationcode = c.locationcode and startdate > now() and canceldate is null  order by startdate<Br> " );
    $res = db_query_rows( "select startdate, class.id as classid from class, location_to_building b, company_esi c  where class.companyid = c.id and b.buildingcode in ( $bstr ) and b.locationcode = c.locationcode and startdate > now() and canceldate is null  order by startdate " );
    $a = array_pop( $res );
    $ext = "";
    if( !$thisusersrow["healthdirector"] )
        $ext = " <a href='class_detail.php?id=$a[classid]'>$a[classid]</a>";
    if( $a["startdate"] ) 
        return getFormattedDateWTime( $a["startdate"] ) . $ext;
}

function cutoff( $string, $length )
{
if( strlen( $string ) > $length ) return substr( $string, 0, $length - 3 ) . "..." ;
return $string;
}

function sendText( $subject, $str, $recipientrow )
{
if( !$recipientrow["cellprovider"] || !$recipientrow["cell"] )
        return ;
    $email = getCellText( $recipientrow["cell"], $recipientrow["cellprovider"] );
    sendMail( "$email", "$subject", $str, "", "ALIVE!net", true );
//    echo( $email );exit;
    if( $recipientrow["cellprovider"] == "Verizon" )
        sendMail( "$email", "$subject", $str, "", "ALIVE!net", true );
    else if( $recipientrow["cellprovider"] == "T-Mobile" )
        sendMail( "$email", "$subject", $str, "", "ALIVE!net" );
    else
        sendMail( "$email", "$subject", $str, "info@emergencyskills.com", "ALIVE!net", true );

}


function getCellText( $cell, $provider )
{
    $cell = preg_replace("/[^0-9]/", "", $cell);
    
    // Alltel
// [10-digit phone number]@message.alltel.com Example: 2125551212@message.alltel.com
    if( $provider == "Alltel" )
        return $cell . "@message.alltel.com";

// AT&T (formerly Cingular)
// [10-digit-number]@mms.att.net Example: 2125551212@mms.att.net
    if( $provider == "AT&T" )
        return $cell . "@txt.att.net";

// Boost Mobile
// [10-digit phone number]@myboostmobile.com Example: 2125551212@myboostmobile.com
    if( $provider == "Boost Mobile" )
        return $cell . "@myboostmobile.com";

    if( $provider == "Simple Mobile" )
        return $cell . "@smtext.com";

// Cricket Communications
// [10-digit phone number]@mms.mycricket.com Example: 1234567890@mms.mycricket.com
    if( $provider == "Cricket Communications" )
        return $cell . "@sms.mycricket.com";

// Metro PCS
// [10-digit telephone number]@mymetropcs.com Example: 5552228888@mymetropcs.com
    if( $provider == "Metro PCS" )
        return $cell . "@mymetropcs.com";

// Nextel (now part of Sprint Nextel)
// [10-digit telephone number]@messaging.nextel.com Example: 7035551234@messaging.nextel.com
    if( $provider == "Nextel" )
        return $cell . "@messaging.nextel.com";

// Sprint (now Sprint Nextel)
// [10-digit phone number]@messaging.sprintpcs.com Example: 2125551234@messaging.sprintpcs.com
    if( $provider == "Sprint" )
        return $cell . "@messaging.sprintpcs.com";

    
    if( $provider == "Google Fi" )
        return $cell . "@msg.fi.google.com";

// T-Mobile
// [10-digit phone number]@tmomail.net Example: 4251234567@tmomail.net
    if( $provider == "T-Mobile" )
        return $cell . "@tmomail.net";

// Verizon
// [10-digit phone number]@vtext.com Example: 5552223333@vtext.com
    if( $provider == "Verizon" )
        return $cell . "@vtext.com";

// Virgin Mobile USA
// [10-digit phone number]@vmobl.com Example: 5551234567@vmobl.com 
    if( $provider == "Virgin Mobile USA" )
        return $cell . "@vmobl.com";

}

function isCharter( $companyname, $schoolcode )
{
    return (strpos($schoolcode , "84-" ) !== false && !strpos($schoolcode, "84-" )) || (strpos( strtolower( $companyname ) , "charter" ) !== false );
}

function isTSI( $crow )
{
    $istsi = $crow["campusid"] == 2339;

    return $istsi;
}

function requestTrainers( $id, $issos = false, $sendemails = true, $specifictrainer = false )
{
    global $peakdates, $sessionid, $thisusersrow, $allclass_names;
    $sql = ( "select class.id from class, company_esi where class.id = '$id' and canceldate is null and code not in ( 'MHFA', 'AEDI', 'Inspections', 'MHFA', 'TCF Meeting', 'Esinew', 'Misc', 'Trade', 'Call', 'call', 'Office', 'party', 'misc'  ) and iscorp <> 3 and companyid = company_esi.id and isnational = 0 and companyname not like 'Sample%' and companyname not like 'Open Registration' and numtrainers > 0 " );
    $hmm = db_query_first_cell( $sql );
    if( !$hmm ) return;
    file_put_contents( "trainerrequests", date("Y-m-d h:i:a" ) . ": ". $sessionid . ": " . $id . ", " . $issos . ", " . $sendemails . ", " . $specifictrainer . "\n", FILE_APPEND );
    file_put_contents( "trainerrequests", $sql . "\n" . print_r( $_SERVER, true ) . "\n\n", FILE_APPEND );

    $newemails = array();
    $crow = getClassRow( $id );
    $companyrow = getCompanyRow( $crow["companyid"] );
    $class_names = $allclass_names[$companyrow["iscorp"]];
    $bor = $companyrow["borough"];
    if( $crow["remote"] )
        $bor = "Remote";
    $ext = $companyrow["iscorp"]?" and corporate = 1 ":" and fingerprinted = 1 ";

    
    if( $bor == "Remote" && $companyrow["iscorp"] )
    {
        $ext .= " and borough = '$bor'" ;
    }
    if( $companyrow["id"] != 2881 && !$companyrow["iscorp"] ) // PSAL can get classes regardless of borough
    {
        $ext .= " and borough = '$bor'" ;
    }
    $deniedtrainers = db_query_array( "select trainerid, userid from requesttotrain, user where user.id = trainerid and classid = $id and done = -1", "trainerid", "userid" );
    if( $issos )
    {
        db_query( "delete from requesttotrain where classid = $id and ( done = -1 or trainerid = '$sessionid' )" );
    }
    else
    {
        if( $id != "28119000" )
            db_query( "delete from requesttotrain where classid = $id and ( done = -1 or done = -2 )" );
    }
    $classname = $class_names[$crow["code"]];
    if( strpos( $classname, "ALIVE!" ) !== false )
    {   
        $ext .= " and firstaid = 1 ";
    }
    if( strpos( $classname, "ASHI" ) !== false )
    {   
        $ext .= " and ashi = 1 ";
    }
    if( strpos( $companyrow["companyname"], "United Cerebral Palsy" ) !== false )
    {   
        $ext .= " and ucp = 1 ";
    }

    if( $specifictrainer )
    {
        $ext .= " and user.id = $specifictrainer";
    }
    
    $peak = isPeakDate( $crow["startdate"])?"\nALERT! This is a PEAK TRAINING DAY!\n":"";
    
    if( $crow["code"] == "monitor"  ||  $crow["code"] == "instructrenew" || $crow["code"] == "ins" )
        $ext .= " and user.tcfaculty = 1 ";
    
    $posstrainers = db_query_array( "select user.id, userid from user, trainer_to_borough where usertype = 'trainer' and trainerid = user.id and inactive = 0 and paused = 0 $ext","id", "userid" );

    if( $issos && count( $deniedtrainers ) )
    {
        $posstrainers = $deniedtrainers;
    }

    $subject =  "Alert! Instructor needed #$id";
    $beginbody = "Please click the following to view class information.

$peak
https://".SUB_DOE.".". URL_WITHOUT_SUBDOMAIN ."/requesttotrain.php?id=$id&trainerid=TRAINERID

You are not confirmed to teach this class until you receive an official ESI
confirmation email.

";
    if( $issos )
    {
        $subject =  "SOS! Substitute Instructor Needed #$id";
        $beginbody = "Please click here for more information on this upcoming class:
https://".SUB_DOE.".". URL_WITHOUT_SUBDOMAIN . "/requesttotrain.php?id=$id&trainerid=TRAINERID

$peak

Class Type: ".$class_names[$crow["code"]]." 
Date: ".fixdatefordisplay( $crow["startdate"], true )."
Time: ".getFormattedTime( $crow["startdate"] )." - $crow[enddate]

Program Location: $companyrow[companyname] " . ($crow["remote"]?" - REMOTE CLASS":"" ) . "
Location Contact: $crow[firstname] $crow[lastname]
Contact Phone Number: ".formatPhone( $crow["phone"] )."

".getSchoolStr( "Training Location", $comrow["iscorp"] ).": " .getTrainingAddress( $crow ). "

TRANSPORTATION INFORMATION:
Parking/Security: $crow[parking_security]

Nearest Subway Line/Station: $crow[nearest_subway]

You are not confirmed to teach this class until you receive an official ESI
confirmation email.
";
        
    }
    
    $body = "
{$beginbody}
";
       
    foreach( $posstrainers as $pid=>$p )
    {
        $avon = availableOn( $crow["startdate"], $pid );
        
        // FIXED: Check if $avon is an array before using count(), or if it equals 2
        $isAvailable = false;
        if(is_array($avon)) {
            $isAvailable = count($avon) > 0;
        } else {
            $isAvailable = $avon == 2;
        }
        
        if( $pid && $isAvailable )
        {
            if( $p )
            {
                $newbod = str_replace( "TRAINERID", ($pid*1234), $body );
                $already = db_query_first_cell( "select id from  requesttotrain where trainerid = $pid and classid = $id " );
                if( !$already && !$specifictrainer )
                    db_query( "insert into requesttotrain ( trainerid, classid, requestdate, done ) values ( $pid, $id, now(), -6 )" );

                if( !$sendemails )
                {
                    $newemails[] = $pid;
                }
                else
                {
                    sendMail( $p, $subject, $newbod, "barbara@emergencyskills.com", $issos?"SOS Alert":"Scheduling Alert" );
                }
                if( $issos )
                {
                    $text = "ALERT!  An SOS has been sent (by {$thisusersrow['first_name']} {$thisusersrow['last_name']}) for class {$id} on " . date( "m/d/y H:i", strtotime( $crow["startdate"] ) ) . "\n Click here for more information: https://".SUB_DOE.".". URL_WITHOUT_SUBDOMAIN ."/requesttotrain.php?id={$id}&trainerid=" . ($pid*1234);
                    $subject = "SOS Alert";
                    $tmpa = db_query_first( "select * from user where id = $pid" );
                    sendText( $subject, $text, $tmpa );
                }
            }
        }
    }
    
    if( $issos )
    {
        $text = "ALERT!  An SOS has been sent (by {$thisusersrow['first_name']} {$thisusersrow['last_name']}) for class {$id} on " . date( "m/d/y H:i", strtotime( $crow["startdate"] ) );
        $subject = "SOS Alert";
        $alertrows = db_query_rows( "select * from user where userid in ( 'schobes1386@hotmail.com', 'bhbrandt@gmail.com', 'smushogillen@gmail.com' )" );
        foreach( $alertrows as $tmpa )
        {
            sendText( $subject, $text, $tmpa );
        }
        sendMail( "barbara@emergencyskills.com", $subject, $text, "barbara@emergencyskills.com", $issos?"SOS Alert":"Scheduling Alert" );
        sendMail( "sarahg@emergencyskills.com", $subject, $text, "barbara@emergencyskills.com", $issos?"SOS Alert":"Scheduling Alert" );
        sendMail( "rebekah@emergencyskills.com", $subject, $text, "barbara@emergencyskills.com", $issos?"SOS Alert":"Scheduling Alert" );
        sendMail( "scott@emergencyskills.com", $subject, $text, "barbara@emergencyskills.com", $issos?"SOS Alert":"Scheduling Alert" );
        sendMail( "dfunnye@emergencyskills.com", $subject, $text, "barbara@emergencyskills.com", $issos?"SOS Alert":"Scheduling Alert" );
        sendMail( "dzamos@emergencyskills.com", $subject, $text, "barbara@emergencyskills.com", $issos?"SOS Alert":"Scheduling Alert" );
        // sendMail( "sanjaydey21@yahoo.com", $subject, $text, "barbara@emergencyskills.com", $issos?"SOS Alert":"Scheduling Alert" );
        // sendMail( "sanjoydey.cse@gmail.com", $subject, $text, "barbara@emergencyskills.com", $issos?"SOS Alert":"Scheduling Alert" );
        
        
    }
    
    if( $sendemails )
        db_query( "update class set lasttrainerreqdate = now() where id = $id" );

    return $newemails;
    
}

function getStageDisplay( $str )
{
    if( $str == "Completed" ) return "";
    if( !$str ) return "";
    return " (S" . $str . ")";
}

function getStageDisplayByTrainerid( $trainerid )
{
    //    echo( "select instructorstage from user where id = '$trainerid'" );
    return getStageDisplay( db_query_first_cell( "select instructorstage from user where id = '$trainerid'" ) );
}

function getAppUploadRow( $id )
{
    return db_query_first( "select * from appuploads where id = '$id'" );
}

function getDifferent( $appcol, $dbcol = "" )
{
    global $values, $crow, $nosave;
    if( $nosave ) return ;
    if( !$dbcol ) $dbcol = $appcol;
    if( $values[$appcol] != $crow[$dbcol] )
    {
        echo( "<font color='red'> (was {$crow[$dbcol]})</font>" );
    }
    
}

function sendDoeCloseoutEmail( $email, $crow )
{
    global $overrideemailinbottom, $bottomright; 
    global $allclass_names;
    if( !$email ) return;
    $overrideemailinbottom = "rebekah@emergencyskills.com";
    $classemail = getClassEmail( $crow );
$classcontact = getClassContact( $crow );
$company = getCompanyRow( $crow["companyid"] );
$class_names = $allclass_names[$company["iscorp"]];
    $attendees = db_query_rows( "Select * from responder_training_dates rtc, responders_esi r where rtc.responderid = r.responderid and  classid = $crow[id] order by lastname, firstname" );
    $names = "";
    foreach( $attendees as $arow )
    {
        if( $arow["clientid"] != $crow["companyid"] )
            continue;
        $names .= "$arow[firstname] $arow[lastname]<br>";
    }

    $expiredate = date( "F j, Y", strtotime( $crow["startdate"] . "  + 2 years" ) );
    $bottomright = file_get_contents( "confirmemail/closeout_bottomright.html" );
    $contents = file_get_contents( "confirmemail/closeout.html" );
    $contents = str_replace( "EXPIREDATE", $expiredate, $contents );
    $resp = "http://".SUB_DOE.".". URL_WITHOUT_SUBDOMAIN ."/response_plan.php?id=$company[id]";
    $contents = str_replace( "SITERESPONSE", $resp, $contents );
    $newaed = "http://".SUB_DOE.".". URL_WITHOUT_SUBDOMAIN ."/printaedsign.php?id=$company[id]";
    $contents = str_replace( "NEWAED", $newaed, $contents );
    $contents = str_replace( "CONFIRMNAMES", $names, $contents );
    // include "gethtmlbodytop.php";
    // echo( $contents );
    // include "gethtmlbodybottom.php";
    // exit;
    sendFormattedHTMLMail( $email, "Congratulations! CPR/AED Training Complete", $contents, "info@emergencyskills.com", "", false ); 

}

function sendEcardEmail( $crow )
{
    global $overrideemailinbottom, $bottomright; 
    global $allclass_names;
    $overrideemailinbottom = "barbara@emergencyskills.com";
    $company = getCompanyRow( $crow["companyid"] );
    $class_names = $allclass_names[$company["iscorp"]];
    $attendees = db_query_rows( "Select * from responder_training_dates rtc, responders_esi r where rtc.responderid = r.responderid and  classid = $crow[id] order by lastname, firstname" );

    $contents = nl2br( file_get_contents( "ecardemail.html" ) );
    
    $contents = str_replace( "TRAININGDATE", getFormattedDate( $crow["startdate"] ), $contents );
    $contents = str_replace( "CLASSID", $crow["id"], $contents );
    $contents = str_replace( "LOCATION", $company["companyname"], $contents );
    if( $company["iscorp"] )
$contents = str_replace( "CLICKHERELINK", "https://emergencyskills.sharefile.com/public/share/web-s5c20deb89b4c4920959668c0ebe9a879" , $contents );
    else
$contents = str_replace( "CLICKHERELINK", "https://emergencyskills.sharefile.com/d-sd92f935781ed4df18f7848d912da5d0c" , $contents );

    // echo( $contents );
    // include "gethtmlbodybottom.php";
    //    exit;
    foreach( $attendees as $arow )
    {
$email = $arow["email"];
// echo( "sending to $email" );
// exit;
sendFormattedHTMLMail( $email, "Claim your CPR/AED certification card", $contents, "info@emergencyskills.com", "", false );

    }
    //    sendFormattedHTMLMail( "rachelc@gmail.com", "Ecard Instructions", $contents, "info@emergencyskills.com", "", false );

}

function sendBlendedEcardEmail( $crow )
{
    global $overrideemailinbottom, $bottomright; 
    global $allclass_names;
    $overrideemailinbottom = "barbara@emergencyskills.com";
    $company = getCompanyRow( $crow["companyid"] );
    $class_names = $allclass_names[$company["iscorp"]];
    $attendees = db_query_rows( "Select * from responder_training_dates rtc, responders_esi r where rtc.responderid = r.responderid and  classid = $crow[id] order by lastname, firstname" );

    $contents = nl2br( file_get_contents( "blendedecardemail.html" ) );
    
    $contents = str_replace( "TRAININGDATE", getFormattedDate( $crow["startdate"] ), $contents );
    $contents = str_replace( "CLASSID", $crow["id"], $contents );
    $contents = str_replace( "LOCATION", $company["companyname"], $contents );
    if( $company["iscorp"] )
$contents = str_replace( "CLICKHERELINK", "https://emergencyskills.sharefile.com/public/share/web-s5c20deb89b4c4920959668c0ebe9a879" , $contents );
    else
$contents = str_replace( "CLICKHERELINK", "https://emergencyskills.sharefile.com/d-sd92f935781ed4df18f7848d912da5d0c" , $contents );

    // echo( $contents );
    // include "gethtmlbodybottom.php";
    //    exit;
    foreach( $attendees as $arow )
    {
$email = $arow["email"];
// echo( "sending to $email" );
// exit;
sendFormattedHTMLMail( $email, "Claim your CPR/AED Certification Card", $contents, "tcadmin@emergencyskills.com", "", false );

    }
//      sendFormattedHTMLMail( "rachelc@gmail.com", "Ecard Instructions", $contents, "info@emergencyskills.com", "", false );

}

function isPeakDate( $str )
{
    global $peakdates;
    return isset( $peakdates[date( "Y-m-d",strtotime( $str ) )] );
}

function getGoogleGeocode( $add )
{
    $geocode=file_get_contents('http://maps.google.com/maps/api/geocode/json?address='.urlencode( $add ).'&sensor=false');
//    print_r( $geocode );
    $output= json_decode($geocode); //Store values in variable

    if($output->status == 'OK'){ // Check if address is available or not
        $latitude = $output->results[0]->geometry->location->lat; //Returns Latitude
        $longitude = $output->results[0]->geometry->location->lng; // Returns Longitude
        return "http://maps.google.com/?q=$latitude,$longitude&output=embed";
    }
    
}


function jsonToReadable($json){
    $tc = 0;        //tab count
    $r = '';        //result
    $q = false;     //quotes
    $t = "\t";      //tab
    $nl = "\n";     //new line

    for($i=0;$i<strlen($json);$i++){
        $c = $json[$i];
        if($c=='"' && $json[$i-1]!='\\') $q = !$q;
        if($q){
            $r .= $c;
            continue;
        }
        switch($c){
            case '{':
            case '[':
                $r .= $c . $nl . str_repeat($t, ++$tc);
                break;
            case '}':
            case ']':
                $r .= $nl . str_repeat($t, --$tc) . $c;
                break;
            case ',':
                $r .= $c;
                if($json[$i+1]!='{' && $json[$i+1]!='[') $r .= $nl . str_repeat($t, $tc);
                break;
            case ':':
                $r .= $c . ' ';
                break;
            default:
                $r .= $c;
        }
    }
    return $r;
}

function getShippingFieldsForEdit( $classrow )
{
    global $session_iscorp;
    foreach ($classrow as $key => $val) {
        ${$key} = $val;
    }
//    print_r( $classrow );
    if( $classrow["companyid"] )
        $borough = db_query_first_cell( "select borough from company_esi where id = '$classrow[companyid]'" );

                                              $shippingfields = array();
                                              $shippingcomments = array();
                                              if( strtolower( $training_state )== "new york" ) $training_state = "NY";
$sizes = array();

//$shippingfields["Do Not Send to XPO"] = "";
$shippingfields["Sent with Admiral"] = "";
$shippingfields["Sent with Birdie"] = "";
$shippingfields["Other Class #"] = "";

$shippingcomments["Pick Up Date"] = "(Defaults to the business day before the class)<br>Put \"jumping\" if this information is not being filled in because of jumping";
$shippingfields["Pick Up Date"] = date( "Y-m-d", strtotime( "$startdate - 1 weekday" ) );

$shippingcomments["Pick up Requested Arrival Time"] = "(Time items must have been picked up)";
$shippingfields["Pick up Requested Arrival Time"] = "08:00";

// $shippingcomments["Delivery Requested Depart Time"] = "(Time items must have been delivered)";
// $shippingfields["Delivery Requested Depart Time"] = "14:00";



$shippingcomments["Return Pick Up Date"] = "(Defaults to the next business day after the class) <br>Put \"jumping\" if this information is not being filled in because of jumping";
$shippingfields["Return Pick Up Date"] = date( "Y-m-d", strtotime( "$startdate + 1 weekday" ) );


$shippingcomments["Return Pick up Requested Arrival Time"] = "(RETURN Time items must have been picked up from class)";
$shippingfields["Return Pick up Requested Arrival Time"] = "08:00";


// $shippingcomments["Return Delivery Requested Depart Time"] = "(RETURN Time items must have been delivered back)";
// $shippingfields["Return Delivery Requested Depart Time"] = "14:00";


$sizes["Pick Up Date"] = "90";
$shippingfields["Requested by"] =  "Chris Franks";
$shippingfields["Customer Reference"] = "5 Bags CPR TRNG EQUIP";
$shippingfields["BOL Number"] = $session_iscorp?"CORP":strtoupper(SUB_DOE);
$shippingfields["Pick up Name"] = "Emergency Skills, Inc";
$shippingfields["Pick up Address"] = "305 7th Ave";
$shippingfields["Pick up City"] = "New York";
$shippingfields["Pick up State"] = "NY";
$shippingcomments["Pick up State"] = "Must be 2 letter abbreviation";
$sizes["Pick up State"] = "30";
$shippingfields["Pick up Zip"] = "10001";
$sizes["Pick up Zip"] = "90";
$shippingfields["Pick up Contact"] = "Chris Franks";
$shippingfields["Pick up room"] = "Suite 1100";
$shippingfields["Pick up phone number"] = "212-564-6833";
$shippingfields["Pick up extension"] = " ";
$shippingfields["Borough"] = $borough;
$sizes["Borough"] = "80";
$sl = 5;
if( $borough == "Manhattan" ) $sl = 3;

$shippingfields["Service Level"] = $sl; //  This needs to be a drop down, I am still waiting for the menu items";
$shippingfields["Return Service Lev"] = $sl; //  This needs to be a drop down, I am still waiting for the menu items";
$shippingfields["Pick up Special Instructions"] = "5 BAGS CPR EQUIP";
$shippingfields["Delivery Name"] = $companyname;
$sizes["Delivery Name"] = "300";
$shippingfields["Delivery Address"] = $training_location;
$shippingfields["Delivery City"] = $training_city;
$shippingfields["Delivery State"] = $training_state;
$shippingcomments["Delivery State"] = "Must be 2 letter abbreviation";
$sizes["Delivery State"] = "30";
$shippingfields["Delivery Zip"] = $training_zip;
$sizes["Delivery Zip"] = "90";
$shippingfields["Delivery Attention"] = $firstname . " " . $lastname;
$shippingfields["Delivery Room"] = "Main Office";
$shippingfields["Delivery Phone"] = formatPhone( $phone );
$shippingfields["Delivery Extension"] = $phone_ext;


$shippingfields["Return Delivery Name"] = "";
$shippingcomments["Return Delivery Name"] = "<i><font color='red'>This is where the bags will go AFTER the class is over</i><br>Leave Blank for same as Pick Up</font>";
$sizes["Return Delivery Name"] = "300";
$shippingfields["Return Delivery Address"] = "";
$shippingfields["Return Delivery City"] = "";
$shippingfields["Return Delivery State"] = "";
$shippingcomments["Return Delivery State"] = "Must be 2 letter abbreviation";
$sizes["Return Delivery State"] = "30";
$shippingfields["Return Delivery Zip"] = "";
$sizes["Return Delivery Zip"] = "90";
$shippingfields["Return Delivery Attention"] = "";
$shippingfields["Return Delivery Room"] = "";
$shippingfields["Return Delivery Phone"] = "";
$shippingfields["Return Weight"] = "";
$shippingcomments["Return Weight"] = "Only Fill In for Weight Override";
$sizes["Return Weight"] = "30";
$shippingfields["Return Delivery Extension"] = "";
$shippingfields["Return Delivery Special Instructions"] = "";

$shippingfields["Insurance Amount"] = "100";

if( getJumpingTo( $classrow["id"] ) || getJumpingFrom( $classrow["id"] ) )
{
    $shippingfields["# of Pieces"] = "5";
    $shippingfields["Return # of Pieces"] = "5";
    $shippingfields["Weight"] = "110";
    $shippingfields["Delivery Special Instructions"] = "5 BAGS CPR EQUIP";
}
else
{
    $shippingfields["Delivery Special Instructions"] = "5 BAGS CPR EQUIP";
    $shippingfields["# of Pieces"] = "5";
    $shippingfields["Return # of Pieces"] = "5";
    $shippingfields["Weight"] = "110";
}
$shippingfields["Bagset"] = "";
$shippingfields["Order Type"] = "";

return array( $shippingfields, $sizes, $shippingcomments );
}

// function getXPOShippingLevels()
// {
//     $values = array( 
// 1 =>"B90", 
// 2 =>"B60", 
// 3 =>"V2H", 
// 4 =>"V1H", 
// 5 =>"V3H BOR", 
// 6 =>"VEH OOT", 
// 12=>"2M MAN", 
// 13=>"2M OUT", 
// 15=>"V90 BOR", 
// 17=>"TRK"
//  );

//     return $values;
// }

function getWDays($startDate,$wDays) {

        // using + weekdays excludes weekends
    $new_date = date('Y-m-d', strtotime("{$startDate} +{$wDays} weekdays"));

    // $holiday_ts = strtotime($holiday);

    //     // if holiday falls between start date and new date, then account for it
    // if ($holiday_ts >= strtotime($startDate) && $holiday_ts <= strtotime($new_date)) {

    //         // check if the holiday falls on a working day
    //     $h = date('w', $holiday_ts);
    //     if ($h != 0 && $h != 6 ) {
    //             // holiday falls on a working day, add an extra working day
    //         $new_date = date('Y-m-d', strtotime("{$new_date} + 1 weekdays"));
    //     }
    // }

    return $new_date;
}


// function sendClassesToXPO( $ids, $type )
// {

//     $arrtypes = array( $type );
//     if( $type == "both" )
//     {
//         $arrtypes = array( "outgoing", "incoming" );
//     }

//     foreach( $arrtypes as $type )
//     {
//         $tmparr = getXPOFields( $type );
        
//         $newfield = $type == "incoming"?"returnxpoid":"xpoid";
//         $datefield = $type == "incoming"?"returnxpodatesent":"xpodatesent";
//         $errorfield = $type == "incoming"?"returnxpoerror":"xpoerror";
        
//         foreach( $ids as $i )
//         {
//             $classrow = getClassRow( $i );
//             $classinfo = getClassInfo( $i );
//             if( $classinfo["Do Not Send to XPO"]["value"] ) continue;
//             if( $type == "incoming" && strtolower( $classinfo["Return Pick Up Date"]["value"] ) == "jumping" ) continue;
//             if( $type == "incoming" && !$classinfo["Return Pick Up Date"]["value"] ) continue;
//             if( $type == "outgoing" && !$classinfo["Pick Up Date"]["value"] ) continue;
//             if( $type == "outgoing" && strtolower( $classinfo["Pick Up Date"]["value"] ) == "jumping" ) continue;
    
//             $values = array();
// //            print_r( $classinfo );
//             foreach( $tmparr as $t=>$s )
//             {
// //                echo( "$t => $s\n"  );
//                 $val = $classinfo[$t]["value"];
//                 if( $throwaway == "Order Date" )
//                 {
//                     if( $val )
//                         $val = date( "Y-m-d", strtotime( $val ) );
//                 }
//                 $values[$s] = $val;
//             }
//             $values["Customer Number"] = $classrow["companyid"];
//             // echo( $type );
//             // print_r( $values );
// //            exit;
//             $values["classid"] = $i;
//             if( !$classrow[$newfield] || !intval( $classrow[$newfield] ) || $classrow[$newfield] == -1  || $classrow[$newfield] < 1 )
//             {
//                 $newid = bookNewXPO( $values, $type );
//                 if( is_numeric( $newid ) )
//                     db_query( "update class set $newfield = '" . mysqli_real_escape_string($GLOBALS['link'], $newid ) . "', $datefield = now() where id = $i" );
//                 else
//                     db_query( "update class set $errorfield = '" . mysqli_real_escape_string($GLOBALS['link'], $newid ) . "', $datefield = now() where id = $i" );
//             }
//             else
//             {
//                 $newid = updateXPO( $classrow[$newfield], $values, $type );
// //                echo( "update class set $datefield = now() where id = $i" );
//                 db_query( "update class set $datefield = now() where id = $i" );
//             }
// //        exit;
            
//         }
//     }
    
// }

function sendClassesToBirdie( $ids, $type )
{

    $arrtypes = array( $type );
    if( $type == "both" )
    {
        $arrtypes = array( "outgoing", "incoming" );
    }

    foreach( $arrtypes as $type )
    {
        $tmparr = getBirdieFields( $type );
        
        $newfield = $type == "incoming"?"returnbirdieid":"birdieid";
        $datefield = $type == "incoming"?"returnbirdiedatesent":"birdiedatesent";
        $errorfield = $type == "incoming"?"returnbirdieerror":"birdieerror";
        
        foreach( $ids as $i )
        {
            $classrow = getClassRow( $i );
            $classinfo = getClassInfo( $i );
            if( $classinfo["Do Not Send to Birdie"]["value"] ) continue;
            if( $type == "incoming" && strtolower( $classinfo["Return Pick Up Date"]["value"] ) == "jumping" ) continue;
            if( $type == "incoming" && !$classinfo["Return Pick Up Date"]["value"] ) continue;
            if( $type == "outgoing" && $classinfo["Other Class #"]["value"] ) continue;
            if( $type == "outgoing" && !$classinfo["Pick Up Date"]["value"] ) continue;
            if( $type == "outgoing" && strtolower( $classinfo["Pick Up Date"]["value"] ) == "jumping" ) continue;
    
            $values = array();
//            print_r( $classinfo );
            foreach( $tmparr as $t=>$s )
            {
//                echo( "$t => $s\n"  );
                $val = $classinfo[$t]["value"];
                if( $throwaway == "Order Date" )
                {
                    if( $val )
                        $val = date( "Y-m-d", strtotime( $val ) );
                }
                $values[$s] = $val;
            }
            $values["Customer Number"] = $classrow["companyid"];
            // echo( $type );
            // print_r( $values );
//            exit;
            $values["classid"] = $i;
            if( !$classrow[$newfield] || !intval( $classrow[$newfield] ) || $classrow[$newfield] == -1  || $classrow[$newfield] < 1 )
            {
                $newid = bookNewBirdie( $values, $type );
                if( is_numeric( $newid ) )
{
                    db_query( "update class set $newfield = '" . mysql_escape_string($newid ) . "', $datefield = now() where id = $i" );
    foreach( $ids as $checkingother )
{
    $checkingotherinfo = getClassInfo( $checkingother );
    if( $checkingotherinfo["Other Class #"]["value"] == $i )
db_query( "update class set $newfield = '" . mysql_escape_string($newid ) . "', $datefield = now() where id = $checkingother" );

}
}
                else
    {
db_query( "update class set $errorfield = '" . mysql_escape_string($newid ) . "', $datefield = now() where id = $i" );
foreach( $ids as $checkingother )
    {
$checkingotherinfo = getClassInfo( $checkingother );
if( $checkingotherinfo["Other Class #"]["value"] == $i )
    db_query( "update class set $errorfield = '" . mysql_escape_string($newid ) . "', $datefield = now() where id = $checkingother" );

    }
    }
            }
            else
            {
                $newid = updateBirdie( $classrow[$newfield], $values, $type );
//                echo( "update class set $datefield = now() where id = $i" );
                db_query( "update class set $datefield = now() where id = $i" );
            }
//        exit;
            
        }
    }
    
}

function getVisibleZipsString( $newcompanytablename, $myvisi = "" )
{
global $visi;
if( !$myvisi ) 
        $myvisi = $visi;
    
return str_replace( "company_esi.", $newcompanytablename . ".", $myvisi );
}

function getAEDTypes( $companyid )
{
    return db_query_first_cell( "select group_concat( distinct( model )  ) from aed_esi where clientid = $companyid and deleted = 0 " );
}

function getAEDSerials( $companyid )
{
    return db_query_first_cell( "select group_concat( serial ) from aed_esi where clientid = $companyid and deleted = 0 " );
}

function getExpiredAEDDates( $companyid )
{
    $retval = array();
    $rows = db_query_rows( "select * from aed_esi where clientid = '$companyid' and deleted = 0  and (
( padaexpiration > '0000-00-00' and padaexpiration < now() ) or
( padbexpiration > '0000-00-00' and padbexpiration < now() ) or
( pediatricpads > '0000-00-00' and pediatricpads < now() ) )
" );
    foreach( $rows as $r )
    {
        if( $r["padaexpiration"] && strtotime( $r["padaexpiration"] ) < time() )
            $retval[$r["serial"]] .= "pad a: " .$r["padaexpiration"] . "; ";
        if( $r["padbexpiration"] && strtotime( $r["padbexpiration"] ) < time() )
            $retval[$r["serial"]] .= "pad b: " .$r["padbexpiration"] . "; ";
        if( $r["pediatricpads"] && strtotime( $r["pediatricpads"] ) < time() )
            $retval[$r["serial"]] .= "pediatric pads: " .$r["pediatricpads"] . "; ";
    }
    return implode( "; ", $retval );
}

function getLastRecertNote( $companyid )
{
    return db_query_first_cell( "select recertificationnotes from recertnotes where companyid = '$companyid' order by recertdate desc limit 1" );
}

function getBagsetValues( $crow )
{
    $values = array();
    for( $i = 1; $i <= 40; $i++ )
    {
        $alr = "";
//        $already = db_query_first_cell( "select id from class where bagset = $i and id <> '$crow[id] and startdate > " );
        $values[$i] = $i . $alr;
    }

    for( $i = 1; $i <= 40; $i++ )
    {
        $alr = "";
        $values["P". $i] = "P" . $i . $alr;
    }

    return $values;
}

function getBirdieOrderTypeValues( $crow )
{
    $values = array();
    $values["19"] = "Manhattan 1-6 Totes";
    $values["20"] = "Manhattan 7-9 Totes";
    $values["21"] = "Manhattan 10+ Totes";

    $values["22"] = "Borough 1-6 Totes";
    $values["24"] = "Borough 7-9 Totes";
    $values["25"] = "Borough 10+ Totes";

    $values["23"] = "Out of Town 1-6 Totes";
    $values["26"] = "Out of Town 7-9 Totes";
    $values["27"] = "Out of Town 10+ Totes";

    return $values;
}

function isSSA( $crow )
{
    return strpos( $crow["companyname"], "SSA" ) !== false || $crow["region"] == "SSA" || strpos( $crow["companyname"], "School Safety" ) !== false;
}

function getJumpingTo( $classid )
{
    $jumpedto = db_query_first_cell( "select classid from class_info where value = '$classid' and name = 'jumpingfrom' and deleted = 0  order by addedbytime desc limit 1" );
    return $jumpedto;
}

// function getNotSendingToXPO( $classid )
// {
// //echo( "select value from class_info where value = '$classid' and name = 'Do Not Send to XPO' and deleted = 0  order by addedbytime desc limit 1<br>" );
//     $jumpedto = db_query_first_cell( "select value from class_info where classid = '$classid' and name = 'Do Not Send to XPO' and deleted = 0  order by addedbytime desc limit 1" );
//     return $jumpedto;
// }

function getSendingToAdmiral( $classid )
{
//echo( "select value from class_info where value = '$classid' and name = 'Do Not Send to XPO' and deleted = 0  order by addedbytime desc limit 1<br>" );
    $jumpedto = db_query_first_cell( "select value from class_info where classid = '$classid' and name = 'Sent With Admiral' and deleted = 0  order by addedbytime desc limit 1" );
    return $jumpedto;
}

function getSendingToBirdie( $classid )
{
//echo( "select value from class_info where value = '$classid' and name = 'Do Not Send to XPO' and deleted = 0  order by addedbytime desc limit 1<br>" );
    $jumpedto = db_query_first_cell( "select value from class_info where classid = '$classid' and name = 'Sent With Birdie' and deleted = 0  order by addedbytime desc limit 1" );
    return $jumpedto;
}

function getJumpingFrom( $classid )
{
    $jumpedto = db_query_first_cell( "select value from class_info where classid = '$classid' and name = 'jumpingfrom' and deleted = 0  order by addedbytime desc limit 1" );
    return $jumpedto;
}

function getNewBatteryInstallDates( $aedid  )
{
    return db_query_array( "select distinct( dateadded ), servicecallid from aed_new_battery_dates where aedid = $aedid order by dateadded desc", "dateadded", "servicecallid" );
}
function remove_url_query($url, $key) {
    $url = preg_replace('/(?:&|(\?))' . $key . '=[^&]*(?(1)&|)?/i', "$1", $url);
    $url = rtrim($url, '?');
    $url = rtrim($url, '&');
    return $url;
}

function sendMaskEmail( $id )
{
    global $overrideemailinbottom; 
    global $allclass_names;
    if( !$id ) return;
    $overrideemailinbottom = "barbara@emergencyskills.com";
    $crow = getClassRow( $id );
    $em = $crow["email"]; 
    $em2 = getUserEmail( $crow["addedby"] );
    $company = getCompanyRow( $crow["companyid"] );
    //    $class_names = $allclass_names[$company["iscorp"]];
    //    $numattendees = db_query_first_cell( "Select count(*)  from responder_to_class where classid = $id" );

    $numattendees = $crow["maxattendees"];

    foreach( array( $em, $em2 ) as $email )
{
    if( $email == "cards@emergencyskills.com" ) continue;
    
    $contents = file_get_contents( "maskemail.html" );
    $contents = str_replace( "NUMPARTICIPANTS", $numattendees, $contents );
    $contents = str_replace( "TOTALCOST", 12*$numattendees, $contents );
    $contents = str_replace( "CLASSID", $id, $contents );
    $contents = str_replace( "ORDEREDBY", $email, $contents );

    // include "gethtmlbodytop.php";
    // echo( $contents );
    // include "gethtmlbodybottom.php";
    sendFormattedHTMLMail( $email, "Personal Masks Available", $contents, "info@emergencyskills.com", "", false ); 
}
}

function sendMissingPRNEmail( $id )
{
    global $session_userid;
    global $overrideemailinbottom; 
    global $allclass_names;
    if( !$id ) return;
    //    $overrideemailinbottom = "barbara@emergencyskills.com";
    $crow = getClassRow( $id );
    $em = $crow["email"]; 
    $em2 = getUserEmail( $crow["addedby"] );
    $em3 = $crow["principalemail"];
    $company = getCompanyRow( $crow["companyid"] );
    //    $class_names = $allclass_names[$company["iscorp"]];
    $attendees = db_query_rows( "Select responders_esi.* from responder_to_class rtc, responders_esi where classid = $id and rtc.responderid = responders_esi.responderid and pmsidvalidated = 0" );

    foreach( $attendees as $a )
{
    $numattendees .= "$a[firstname] $a[lastname]<Br>\n";
}
    if( count( $attendees ) )
{
    foreach( array( $em, $em2, $em3 ) as $email )
{
    
    if( !trim( $email ) )
continue;
    $contents = file_get_contents( "missingprnemail.html" );
    $contents = str_replace( "MISSINGPRNS", $numattendees, $contents );
    $contents = str_replace( "CLASSID", $id, $contents );
    $overrideemailinbottom = "cards@emergencyskills.com";
    
    sendFormattedHTMLMail( $email, "Preparing your CPR/AED certification ecards - #{$id}", $contents, $session_userid, "", false ); 
}
}
}

function getCurrentBagValues()
{
    return db_query_array( "select bag from equipmentstatus where iscurrent = 1 order by bag", "bag", "bag" );
}


$equipmenttypes = array( "Manikin", "AED Trainer Unit" );
$equipmentstatuses = array( "Created"=> "Created", "In Cleaning"=>"In Cleaning", "Ready For Use"=> "Ready For Use", "Packed"=>"Packed", "At A Class"=> "At A Class" );

function getCompanyNameWithColorString( $comrow, $withfont = false )
{
    global $bannedschoolids;
    $str = $comrow["companyname"];

    $color = $bannedschoolids[$comrow["id"]];
    if( $color )
{
    $fcol = $color=="Yellow"?"#fcba03":$color;
    
    if( $withfont )
$str .= " <font color='$fcol'>*" . $color . "*</font>";
    else
$str .= " *" . $color . "*";
}
    return $str;
    

}

function getRequestEmailsSent( $classid )
{
    $res = db_query_rows( "select torecipients, datesent from automatedemails where subject = 'Alert! Instructor needed #{$classid}'" );
    return $res;
    
}

function getTrainingRoomNumber( $classrow ) { 
if( $classrow["training_room_number"] ) { 
return " Room: $classrow[training_room_number]";
}
return "";

}
function formatPhone($number) {
// Allow only Digits, remove all other characters.
$number = preg_replace("/[^\d]/","",$number);

// get number length.
$length = strlen($number);

// if number = 10
if($length == 10) {
    $number = preg_replace("/^1?(\d{3})(\d{3})(\d{4})$/", "$1-$2-$3", $number);
}

return $number;

}

function isParksCompany( $companyname )
{
    return strpos( $crow["companyname"], "Parks" ) !== false;
}
function isAGINGCompany( $crow )
{
    return $crow["iscorp"] == AGING; 
}

function isParksUser() 
{
    global $thisusersrow;
    if( $thisusersrow["visibleregion"] == "NYC Parks" )
return true;
    return false;
}

function isNotAvail( $theday, $borough )
{
return db_query_first_cell( "select dt from blockeddates where dt = '$theday' and ( borough = '' or borough = '$borough' )" );
}

?>
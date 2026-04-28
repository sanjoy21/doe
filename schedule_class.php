<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

require_once('mysql.php');
// PHP 8.2 Fix: Initialize all variables immediately
$nonewschool = 1;
$companyid = 0;
$classes_this_month = array();
$overridecname = 'companyid'; // Default for the AJAX function
$extra_encoded = "";         // Default for the AJAX function

if (session_status() === PHP_SESSION_NONE) { session_start(); }
$session_iscorp = isset($_SESSION['iscorp']) ? (int)$_SESSION['iscorp'] : 0;

// Determine Company ID safely
if (isset($_REQUEST["companyid"])) {
    $companyid = $_REQUEST["companyid"];
} else {
    $companyid = $thisusersrow["singleschoolid"] ?? ($thisusersrow["companyid"] ?? 0);
}

// Handle data updates
if (isset($_POST["iswithinhalfmile"]) && $companyid) {
    $iswithinhalfmile = intval($_POST["iswithinhalfmile"]);
    db_query("UPDATE company_esi SET iswithinhalfmile = '$iswithinhalfmile' WHERE id = " . intval($companyid));
}

// Load data based on selected company
if ($companyid && $companyid <> '%') {
    $crow = getCompanyRow($companyid);
    if ($crow) {
        $session_iscorp = (int)($crow['iscorp'] ?? 0);
        $_SESSION['iscorp'] = $session_iscorp;
    }
    
    if (!isOverallAdmin()) {
        $sql = "SELECT count(id) AS num_classes, date_format(startdate, '%Y-%c-%d') AS date
                FROM class WHERE companyid LIKE '" . addslashes($companyid) . "'
                AND deleted = 0 GROUP BY date_format(startdate, '%Y-%m-%d')";
        $rows = db_query_rows($sql);
        if (is_array($rows)) {
            foreach ($rows as $row) { $classes_this_month[$row["date"]] = $row["num_classes"]; }
        }
    }
}

$defaultdate = mktime( 0,0,0, date( "m" ) );
if (!isset($month) || !$month) {
    $month = date("n", $defaultdate);
}

if (!isset($year) || !$year) {
    $year = date("Y", $defaultdate);
}

$next_month = $month + 1;
$next_year = $year;
if ($next_month == 13) { 
    $next_month = 1;
    $next_year++;
}

// Initialize dynamic variables
${"selected_".$month} = "SELECTED";
${"selected_y_".$year} = "SELECTED";
if( isset($starttime) ) {
    ${"selected_".$starttime} = "SELECTED";
}
if( isset($class) ) {
    ${"selected_".$class} = "SELECTED";
}

// Initialize all possible selected variables to avoid undefined warnings
for( $i = 1; $i <= 12; $i++ ) {
    if( !isset(${"selected_".$i}) ) {
        ${"selected_".$i} = "";
    }
}
for( $i = 2025; $i <= 2026; $i++ ) {
    if( !isset(${"selected_y_".$i}) ) {
        ${"selected_y_".$i} = "";
    }
}

// Initialize time selection variables
$times = array('0000','0015','0030','0045','0100','0115','0130','0145','0200','0215','0230','0245',
               '0300','0315','0330','0345','0400','0415','0430','0445','0500','0515','0530','0545',
               '0600','0615','0630','0645','0700','0715','0730','0745','0800','0815','0830','0845',
               '0900','0915','0930','0945','1000','1015','1030','1045','1100','1115','1130','1145',
               '1200','1215','1230','1245','1300','1315','1330','1345','1400','1415','1430','1445',
               '1500','1515','1530','1545','1600','1615','1630','1645','1700','1715','1730','1745',
               '1800','1815','1830','1845','1900','1915','1930','1945','2000','2015','2030','2045',
               '2100','2115','2130','2145','2200','2215','2230','2245','2300','2315','2330','2345');
foreach( $times as $time ) {
    if( !isset(${"selected_".$time}) ) {
        ${"selected_".$time} = "";
    }
}

include "getschools.php";

if( isset($quicksched) && $quicksched ) {
    $numtrainers = 1;
}

// Initialize variables that might be used
if( !isset($borough) ) $borough = '';
if( !isset($catonly) ) $catonly = '';
if( !isset($maxattendees) ) $maxattendees = '';
if( !isset($numtrainers) ) $numtrainers = '';
if( !isset($classes_this_month) ) $classes_this_month = array();
if( !isset($av_x) ) $av_x = '';
?>

</style>
<?php include "getschooldropdown_ajax.php"; ?>
<script language=javascript>
function updateYear( mon ) {
    if( document.forms["myform"].elements["month"].options[document.forms["myform"].elements["month"].selectedIndex].value < <?php echo date( "n" ); ?> ) {
        document.forms["myform"].elements["year"].selectedIndex = 1;
    } else {
        document.forms["myform"].elements["year"].selectedIndex = 0;
    }
}
</script>
<?php include "ssi/top.php"; ?>
<!--start center content-->

<BR CLEAR="ALL">
<strong><span class="title">SCHEDULE A CLASS</span></strong> &nbsp;&nbsp;<span class="copy"><em>(Step 1 of 2)</em></span>
<br><hr>
<p>
<script language='javascript'>
function checkForm( frm ) {
    var quick = false; 
    <?php if( isOverallAdmin() ) { ?>
    quick = frm.elements["quicksched"].checked;
    <?php } ?>

    if( $("#checkapproved").length ) {
        if( !$("#checkapproved").is(":checked") ) {
            alert( "Please confirm you have a space approved by the DOE for training." );
            return false;
        }
    }
    
    if( $("#iswithinhalfmile").length ) {
        if( $("#iswithinhalfmile").val() == "-1" ) {
            alert( "Please mark whether your school is within a half mile of the subway." );
            return false;
        }
    }
    
    if( frm.starttime.selectedIndex == 0 && !quick ) {
        alert( "Please choose a start time." );
        return false;
    }
    
    val = getSelectedRadioValue( frm.elements["class"] );
    if( val == "" && !quick ) {
        alert( "Please choose a class." );
        return false;
    }
    
    if( val == "dd" && !quick ) {
        if( confirm( "Please note: This class does not include AED training. " ) == false ) 
            return false;
    }
    
    <?php if (isset($thisusersrow["companyid"]) && $thisusersrow["companyid"] === '0' && !isset($companyid) ) { ?>
    if( !frm.companyid || frm.companyid.selectedIndex < 1 ) {
        alert( "Please choose the <?php echo getSchoolStr( "school" ); ?>." );
        return false;
    }
    <?php } ?>
    
    return true;
}
</script>

<form name="myform" action="schedule_class.php" method="post" >
<table cellpadding="0" cellspacing="4" border="0" >
<?php if( isOverallAdmin() ) { ?>
<tr> 
    <td valign="top" align="right"><span class="copy"><strong>Quick Schedule:</strong></span></td> 
    <td valign="top"><span class="copy"><input type='checkbox' name='quicksched' value='1' <?php echo isset($quicksched) && $quicksched ? "CHECKED" : ""; ?> ></span></td>
</tr>
<?php } ?>

<?php if( isset($companyid) && $companyid > 0) {  ?>
<input type='hidden' name='companyid' value='<?php echo intval($companyid); ?>'>
<?php } ?>

<?php if (!isset($companyid) || !$companyid) { ?>
<tr><td colspan='2'>
<?php if( isset($session_iscorp) && $session_iscorp ){ ?>
<input type='hidden' name='borough' id='borough' value='other'>
<?php } else { ?>
<tr><td align=right><span class="copy"><b>Choose Your Borough:</span></td>
<td>
<select id=borough name="borough" style="font-size: 10px;  font-family: verdana;">
    <option value=""></option>
    <option <?php echo $borough=="Other"?"SELECTED":""; ?> value="other">Other</option>
    <option <?php echo $borough=="Bronx"?"SELECTED":""; ?> value="Bronx">The Bronx</option>
    <option <?php echo $borough=="Brooklyn"?"SELECTED":""; ?> value="Brooklyn">Brooklyn</option>
    <option <?php echo $borough=="Manhattan"?"SELECTED":""; ?> value="Manhattan">Manhattan</option>
    <option <?php echo $borough=="Queens"?"SELECTED":""; ?> value="Queens">Queens</option>
    <option <?php echo $borough=="Staten Island"?"SELECTED":""; ?> value="Staten Island">Staten Island</option>
</select>
</td></tr>
<?php } ?>
<tr><td class='copy' valign='top' align='right'><b><nobr>Search <?php echo getSchoolStr( "School" ); ?> Name:</nobr></td><td>
<input type='text' id='tmpschoolname' name='tmpschoolname' class='copy' onChange='updateCompanies()'>
<input type='button' value='Search' class='copy' onClick='updateCompanies()'>
<br>
<?php if( !isset($session_iscorp) || !$session_iscorp ) { ?>
<span class='copy'><i>Use keywords. For example, if your school is 10-X-475 John F. Kennedy School, search for "475" or "Kennedy" or "John".</span></i>
<?php } ?>
</td></tr>
<tr>
<td colspan='2' valign="top" id='school_select'></td></tr>
<?php } ?>

<tr>
    <td valign="top" align="right"><span class="copy"><strong>Select your class:</strong></span></td>
    <td valign="top" class='copy'>
    <?php 
    $lastcat = "";
    $allclass_categories = isset($allclass_categories) ? $allclass_categories : array();
    $allclass_names = isset($allclass_names) ? $allclass_names : array();
    
    if( isset($session_iscorp) && isset($allclass_categories[$session_iscorp]) ) {
        $uniq = array_unique( $allclass_categories[$session_iscorp] );
        $class_names = $allclass_names[$session_iscorp];
        
        foreach( $uniq as $currcat ) {
            if( isset($catonly) && $catonly && isset($allclass_categories[$session_iscorp][$class]) && $allclass_categories[$session_iscorp][$class] != $currcat )
                continue;
                
            foreach ($class_names as $code => $name) { 
                if( !isset($allclass_categories[$session_iscorp][$code]) || $allclass_categories[$session_iscorp][$code] != $currcat )
                    continue;
                    
                if( $session_iscorp == 1 && !isOverallAdmin() && !hasScheduleAccess( isset($session_id) ? $session_id : 0, $code ) )
                    continue;
                    
                if( !$session_iscorp && $code != 'reg' ) {
                    if( $code == "dd" )
                        continue;
                    if( !isOverallAdmin() ) 
                        continue;
                }
                
                if( isCodeRetired( $code ) )
                    continue;
                    
                if( $currcat != $lastcat && $currcat != "All" ) {
                    echo( "<b>$currcat</b><br>" );
                    $lastcat = $currcat;
                }
    ?>
    <input type='radio' name='class' <?php echo (isset($class) && $class==$code?"CHECKED":""); ?> value="<?php echo htmlspecialchars($code); ?>"> <?php echo htmlspecialchars($name); ?><br>
    <?php 
            }
        }
    }
    ?>
    <?php if( !isset($session_iscorp) || !$session_iscorp ) { ?>
    <a href='#' onClick='javascript:window.open( "classhelp.html", "_blank", "width=400,height=600,scrollbars=yes" )'><span class='copy'>Course Descriptions</span></a>
    <?php } ?>
    </td>
</tr>

<tr>
    <td valign="top" align="right"><span class="copy"><strong>Select a month:</strong></span></td>
    <td>
        <table cellpadding="0" cellspacing="0" border="0">
            <tr>
                <td valign="top">
                    <select name="month" style="font-size: 10px;  font-family: verdana;" onChange="updateYear()" >    
                        <option <?php echo $selected_1; ?> value="1">JAN</option>
                        <option <?php echo $selected_2; ?> value="2">FEB</option>
                        <option <?php echo $selected_3; ?> value="3">MAR</option>
                        <option <?php echo $selected_4; ?> value="4">APR</option>
                        <option <?php echo $selected_5; ?> value="5">MAY</option>
                        <option <?php echo $selected_6; ?> value="6">JUNE</option>
                        <option <?php echo $selected_7; ?> value="7">JULY</option>
                        <option <?php echo $selected_8; ?> value="8">AUG</option>
                        <option <?php echo $selected_9; ?> value="9">SEPT</option>
                        <option <?php echo $selected_10; ?> value="10">OCT</option>
                        <option <?php echo $selected_11; ?> value="11">NOV</option>
                        <option <?php echo $selected_12; ?> value="12">DEC</option>                                                                          
                    </select>
                </td>
                <td>&nbsp;</td>
                <td valign="top">
                    <select name="year" style="font-size: 10px;  font-family: verdana;">
                                                                                                                                                   
                        <option <?php echo $selected_y_2026; ?> value="2026">2026</option>                                                                                                                            
                    </select>
                </td>
            </tr>
        </table>
    </td>
</tr>

<tr>
    <td valign="top" align="right"><span class="copy"><strong>Requested Start Time:</strong></span></td>
    <td>
        <select name="starttime" style="font-size: 10px; font-family: verdana;">
            <option value=''>Please Choose</option>
            <?php if( isset($session_iscorp) && $session_iscorp ) { ?>
            <option <?php echo $selected_0000; ?> value="0000">12:00 AM</option>
            <option <?php echo $selected_0015; ?> value="0015">12:15 AM</option>
            <option <?php echo $selected_0030; ?> value="0030">12:30 AM</option>
            <option <?php echo $selected_0045; ?> value="0045">12:45 AM</option>
            <option <?php echo $selected_0100; ?> value="0100">1:00 AM</option>
            <option <?php echo $selected_0115; ?> value="0115">1:15 AM</option>
            <option <?php echo $selected_0130; ?> value="0130">1:30 AM</option>
            <option <?php echo $selected_0145; ?> value="0145">1:45 AM</option>
            <option <?php echo $selected_0200; ?> value="0200">2:00 AM</option>
            <option <?php echo $selected_0215; ?> value="0215">2:15 AM</option>
            <option <?php echo $selected_0230; ?> value="0230">2:30 AM</option>
            <option <?php echo $selected_0245; ?> value="0245">2:45 AM</option>
            <option <?php echo $selected_0300; ?> value="0300">3:00 AM</option>
            <option <?php echo $selected_0315; ?> value="0315">3:15 AM</option>
            <option <?php echo $selected_0330; ?> value="0330">3:30 AM</option>
            <option <?php echo $selected_0345; ?> value="0345">3:45 AM</option>
            <option <?php echo $selected_0400; ?> value="0400">4:00 AM</option>
            <option <?php echo $selected_0415; ?> value="0415">4:15 AM</option>
            <option <?php echo $selected_0430; ?> value="0430">4:30 AM</option>
            <option <?php echo $selected_0445; ?> value="0445">4:45 AM</option>
            <option <?php echo $selected_0500; ?> value="0500">5:00 AM</option>
            <option <?php echo $selected_0515; ?> value="0515">5:15 AM</option>
            <option <?php echo $selected_0530; ?> value="0530">5:30 AM</option>
            <option <?php echo $selected_0545; ?> value="0545">5:45 AM</option>
            <option <?php echo $selected_0600; ?> value="0600">6:00 AM</option>
            <option <?php echo $selected_0615; ?> value="0615">6:15 AM</option>
            <option <?php echo $selected_0630; ?> value="0630">6:30 AM</option>
            <option <?php echo $selected_0645; ?> value="0645">6:45 AM</option>
            <option <?php echo $selected_0700; ?> value="0700">7:00 AM</option>
            <option <?php echo $selected_0715; ?> value="0715">7:15 AM</option>
            <?php } ?>
            <option <?php echo $selected_0730; ?> value="0730">7:30 AM</option>
            <option <?php echo $selected_0745; ?> value="0745">7:45 AM</option>
            <option <?php echo $selected_0800; ?> value="0800">8:00 AM</option>
            <option <?php echo $selected_0815; ?> value="0815">8:15 AM</option>
            <option <?php echo $selected_0830; ?> value="0830">8:30 AM</option>
            <option <?php echo $selected_0845; ?> value="0845">8:45 AM</option>
            <option <?php echo $selected_0900; ?> value="0900">9:00 AM</option>
            <option <?php echo $selected_0915; ?> value="0915">9:15 AM</option>
            <option <?php echo $selected_0930; ?> value="0930">9:30 AM</option>
            <option <?php echo $selected_0945; ?> value="0945">9:45 AM</option>
            <option <?php echo $selected_1000; ?> value="1000">10:00 AM</option>
            <option <?php echo $selected_1015; ?> value="1015">10:15 AM</option>
            <option <?php echo $selected_1030; ?> value="1030">10:30 AM</option>
            <option <?php echo $selected_1045; ?> value="1045">10:45 AM</option>
            <option <?php echo $selected_1100; ?> value="1100">11:00 AM</option>
            <option <?php echo $selected_1115; ?> value="1115">11:15 AM</option>
            <option <?php echo $selected_1130; ?> value="1130">11:30 AM</option>   
            <option <?php echo $selected_1145; ?> value="1145">11:45 AM</option>   
            <option <?php echo $selected_1200; ?> value="1200">12:00 PM</option>                                        
            <option <?php echo $selected_1215; ?> value="1215">12:15 PM</option>                                        
            <option <?php echo $selected_1230; ?> value="1230">12:30 PM</option>
            <option <?php echo $selected_1245; ?> value="1245">12:45 PM</option>
            <option <?php echo $selected_1300; ?> value="1300">1:00 PM</option>                                        
            <option <?php echo $selected_1315; ?> value="1315">1:15 PM</option>                                        
            <option <?php echo $selected_1330; ?> value="1330">1:30 PM</option>
            <option <?php echo $selected_1345; ?> value="1345">1:45 PM</option>
            <option <?php echo $selected_1400; ?> value="1400">2:00 PM</option>                                        
            <option <?php echo $selected_1415; ?> value="1415">2:15 PM</option>                                        
            <option <?php echo $selected_1430; ?> value="1430">2:30 PM</option>
            <option <?php echo $selected_1445; ?> value="1445">2:45 PM</option>
            <option <?php echo $selected_1500; ?> value="1500">3:00 PM</option>                                        
            <?php if( isOverallAdmin() || isParksUser() ) { ?>
            <option <?php echo $selected_1515; ?> value="1515">3:15 PM</option>
            <option <?php echo $selected_1530; ?> value="1530">3:30 PM</option>
            <option <?php echo $selected_1545; ?> value="1545">3:45 PM</option>
            <option <?php echo $selected_1600; ?> value="1600">4:00 PM</option>                                        
            <option <?php echo $selected_1615; ?> value="1615">4:15 PM</option>                                        
            <option <?php echo $selected_1630; ?> value="1630">4:30 PM</option>
            <option <?php echo $selected_1645; ?> value="1645">4:45 PM</option>
            <option <?php echo $selected_1700; ?> value="1700">5:00 PM</option>                                        
            <option <?php echo $selected_1715; ?> value="1715">5:15 PM</option>                                        
            <option <?php echo $selected_1730; ?> value="1730">5:30 PM</option>
            <option <?php echo $selected_1745; ?> value="1745">5:45 PM</option>
            <option <?php echo $selected_1800; ?> value="1800">6:00 PM</option>                                        
            <option <?php echo $selected_1815; ?> value="1815">6:15 PM</option>                                        
            <option <?php echo $selected_1830; ?> value="1830">6:30 PM</option>
            <option <?php echo $selected_1845; ?> value="1845">6:45 PM</option>
            <option <?php echo $selected_1900; ?> value="1900">7:00 PM</option>                                        
            <option <?php echo $selected_1915; ?> value="1915">7:15 PM</option>                                        
            <option <?php echo $selected_1930; ?> value="1930">7:30 PM</option>
            <option <?php echo $selected_1945; ?> value="1945">7:45 PM</option>
            <option <?php echo $selected_2000; ?> value="2000">8:00 PM</option>                                        
            <?php if( isset($session_iscorp) && $session_iscorp ) { ?>
            <option <?php echo $selected_2015; ?> value="2015">8:15 PM</option>                                        
            <option <?php echo $selected_2030; ?> value="2030">8:30 PM</option>                                                                          
            <option <?php echo $selected_2045; ?> value="2045">8:45 PM</option>                                                                          
            <option <?php echo $selected_2100; ?> value="2100">9:00 PM</option>                                        
            <option <?php echo $selected_2115; ?> value="2115">9:15 PM</option>                                        
            <option <?php echo $selected_2130; ?> value="2130">9:30 PM</option>                                                                          
            <option <?php echo $selected_2145; ?> value="2145">9:45 PM</option>                                                                          
            <option <?php echo $selected_2200; ?> value="2200">10:00 PM</option>                                        
            <option <?php echo $selected_2215; ?> value="2215">10:15 PM</option>                                        
            <option <?php echo $selected_2230; ?> value="2230">10:30 PM</option>                                                                          
            <option <?php echo $selected_2245; ?> value="2245">10:45 PM</option>                                                                          
            <option <?php echo $selected_2300; ?> value="2300">11:00 PM</option>                                        
            <option <?php echo $selected_2315; ?> value="2315">11:15 PM</option>                                        
            <option <?php echo $selected_2330; ?> value="2330">11:30 PM</option>                                                                          
            <option <?php echo $selected_2345; ?> value="2345">11:45 PM</option>                                                                          
            <?php } ?>
            <?php } ?>
        </select>
        <br><span class='copy'><i>Note: If you wish to train on a Saturday, you must select 9am as the start time. All Saturday programs are scheduled from 9am to 3pm. </i></span>
    </td>
</tr>

<?php if( (!isset($session_iscorp) || !$session_iscorp) && isset($crow["iswithinhalfmile"]) && $crow["iswithinhalfmile"] < 0 && isset($companyid) && $companyid ) { ?>
<tr><td colspan='2'>
<b><font color='red'>Is this <?php echo getSchoolStr( "school" ); ?> within a half mile walk of the subway?</b></font> <br>
<select class='copy' name='iswithinhalfmile' id='iswithinhalfmile'>
    <option <?php echo isset($crow["iswithinhalfmile"]) && $crow["iswithinhalfmile"]=="-1"?"SELECTED":""; ?> value="-1"></option>
    <option <?php echo isset($crow["iswithinhalfmile"]) && $crow["iswithinhalfmile"]=="1"?"SELECTED":""; ?> value="1">Yes</option>
    <option <?php echo !isset($crow["iswithinhalfmile"]) || !$crow["iswithinhalfmile"]?"SELECTED":""; ?> value="0">No</option>
</select>
</td></tr>
<?php } ?>

<?php if( isset($session_iscorp) && $session_iscorp == AGING ) { ?>
<tr><td colspan='2'><font color='red'>An individual senior center may register a maximum of 4 staff members per center. Centers are encourage to train a minimum of 3 staff members. Any empty seats will be posted online for other Aging staff members to join.</font></td></tr>
<?php } ?>

<input type='hidden' name='catonly' value='<?php echo htmlspecialchars($catonly); ?>'>
<?php if( isset($session_iscorp) && $session_iscorp && isOverallAdmin() ) { ?>
<tr> 
    <td valign="top" align="right"><span class="copy"><strong>Num Trainers:</strong></span></td> 
    <td valign="top"><span class="copy"><input type='text' name='numtrainers' value='<?php echo isset($numtrainers) && $numtrainers ? htmlspecialchars($numtrainers) : 1; ?>' size='3'></span></td>
</tr>
<tr> 
    <td valign="top" align="right"><span class="copy"><strong>Max Attendees:</strong></span></td> 
    <td valign="top"><span class="copy"><input type='text' name='maxattendees' value='<?php echo isset($maxattendees) && $maxattendees ? htmlspecialchars($maxattendees) : 8; ?>' size='3'></span></td>
</tr>
<?php } else { ?>
<input type='hidden' name='numtrainers' value='1'>
<?php if( isset($session_id) && $session_id == 14088 ) { ?>
<tr> 
    <td valign="top" align="right"><span class="copy"><strong>Max Attendees:</strong></span></td> 
    <td valign="top"><span class="copy"><input type='text' name='maxattendees' value='<?php echo isset($maxattendees) && $maxattendees ? htmlspecialchars($maxattendees) : 8; ?>' size='3'></span></td>
</tr>
<?php } else if( isset($session_iscorp) && $session_iscorp ) { ?>
<input type='hidden' name='maxattendees' value='<?php echo (isset($thisusersrow["userid"]) && strtolower($thisusersrow["userid"])=="andrea.pellettiere@tsiclubs.com") ? 9 : 8; ?>'>
<?php } else { ?>
<input type='hidden' name='maxattendees' value='<?php echo (isset($thisusersrow["userid"]) && strtolower($thisusersrow["userid"])=="andrea.pellettiere@tsiclubs.com") ? 9 : 12; ?>'>
<?php } ?>
<?php } ?>

<?php if( !isset($session_iscorp) || !$session_iscorp ) { ?>
<tr><td colspan='2'><span class="copy"><input type='checkbox' id="checkapproved" name='checkapproved' value='1'> <i><b>Can you confirm you have a training room that is at least 540 Square Feet, to accommodate for proper social distancing for 12 participants + 1 instructor?</b></i> Check here to confirm. </td></tr>
<?php } ?>

<tr>
    <td valign="top" align="right"><br><br></td>
    <td>
        <input type="image" name='av' onClick='<?php if( isset($session_iscorp) && $session_iscorp || 1 ) { ?>return checkForm( document.forms["myform"] ) <?php } else { ?>alert( "Sorry, registration is closed at this time. Please try again later." ); return false; <?php } ?>' src="images/button_checkavail.gif" alt="Check Availability">
    </td>
</tr>

<?php if( !isset($session_iscorp) || !$session_iscorp ) { ?>
<tr><td colspan='2'>Having problems? <a href='http://doe.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/pdfs/fax_registration.pdf'>Download the Fax Form here,</a> or call 212-564-6833.</td></tr>
<?php } ?>
</table>
</form>

<?php if ( (isset($starttime) && $starttime && isset($av_x) && $av_x && isset($class) && $class) || (isset($av_x) && $av_x && isset($quicksched) && $quicksched) ) { ?>
<hr>
The <strong>green dates</strong> below are the closest available training dates for the class and month you selected.  Click on a date in green to schedule your class.
<p>

<div align="center">
<!-- start tiny calendars-->
<table cellpadding="0" cellspacing="0" border="0">
<tr>
<td valign="top">
   <?php echo show_calendar($classes_this_month, isset($class) ? $class : '', isset($starttime) ? $starttime : '', $month, $year, isset($maxattendees) ? $maxattendees : '', isset($numtrainers) ? $numtrainers : '', isset($quicksched) ? $quicksched : ''); ?>
</td>
<td><img src="images/dotclear.gif" width="20" align="center" alt=""></td>
<td valign="top">
            <?php echo show_calendar($classes_this_month, isset($class) ? $class : '', isset($starttime) ? $starttime : '', $next_month, $next_year, isset($maxattendees) ? $maxattendees : '', isset($numtrainers) ? $numtrainers : '', isset($quicksched) ? $quicksched : ''); ?>
</td>
</tr>
</table>
</div>
<?php } ?>
<!-- end tiny calendars-->

<BR><BR><BR><BR>
<!--end center content-->
<?php include "ssi/footer.php"; ?>
<!--end footer-->
</span>
</td>
<td valign="top" width="20" align="center"><img src="images/dotclear.gif" width="10" alt=""></td>
</tr>
</table>
<br><br>
</div>

<?php if( isset($borough) && $borough || isset($doit) && $doit || isset($companyid) && $companyid ) { ?>
<?php if (!isset($companyid) || !$companyid) { ?>
<script language='javascript'>
<?php if( isset($companyid) && $companyid ){ ?>
setCompanyToSpecific( '<?php echo intval($companyid); ?>' );
<?php } else { ?>
updateCompanies();
<?php } ?>
</script>
<?php } ?>
<?php } ?>
</body>
</html>
<?php
require_once('mysql.php');

function db_escape_string($string) {
    if (is_array($string)) {
        return $string; // Do not escape arrays
    }
    // Basic placeholder for security demonstration
    return str_replace(['\\', "'"], ['\\\\', "\'"], (string)$string);
}

// Initialize variables from request/session
// $id = $_REQUEST['id'];
// $month = $_REQUEST['month'];
// $year = $_REQUEST['year'];
// $class = $_REQUEST['class'];
// $starttime = $_REQUEST['starttime'];
// $session_id = $_SESSION['id'];
// $session_iscorp = $_SESSION['iscorp'];

$companyid = get_companyid($session_id);

// Prepare SQL with proper escaping
$escaped_id = db_escape_string($id);
$sql = "
SELECT c.*,
s.companyname,
s.address,
s.city,
s.borough,
s.id as companyid,
DATE_FORMAT(c.startdate, '%W, %M %e, %Y') as date_str,
DATE_FORMAT(c.startdate, '%k:%i %p') as time_str,
DATE_FORMAT(c.startdate, '%c') as month,
DATE_FORMAT(c.startdate, '%Y') as year
FROM `class` as c,
company_esi as s
WHERE c.id = '$escaped_id'
AND c.companyid = s.id
";
//echo $sql;
$this_class = db_query_first($sql);
//print_r($this_class);
$code = $this_class['code'];
//echo( $code );
if (!$month) {
    $month = $this_class['month'];
}

if (!$year) {
    $year = $this_class['year'];
}

// Prepare companyid for SQL
$escaped_companyid = db_escape_string($companyid);
$sql = "
SELECT COUNT(id) AS num_classes, DATE_FORMAT(startdate, '%Y-%c-%d') AS date
FROM class
WHERE DATE_FORMAT(startdate, '%Y-%m') = '2006-07'
AND companyid LIKE '$escaped_companyid'
AND deleted = 0
GROUP BY DATE_FORMAT(startdate, '%Y-%m-%d') 
";
$rows = db_query_rows($sql);
$classes_this_month = [];
foreach ($rows as $row) {
    $classes_this_month[$row['date']] = $row['num_classes'];
}
//print_r($classes_this_month);exit;

$next_month = $month + 1;
$next_year = $year;
if ($next_month == 13) { 
    $next_month = 1;
    $next_year++;
}

// Initialize selection variables
for ($i = 1; $i <= 12; $i++) {
    ${"selected_".$i} = ($i == $month) ? "SELECTED" : "";
}

for ($i = 2024; $i <= 2030; $i++) {
    ${"selected_y_".$i} = ($i == $year) ? "SELECTED" : "";
}

// Initialize time selection variables
$times = [
    '0000', '0015', '0030', '0045', '0100', '0115', '0130', '0145', '0200', '0215', '0230', '0245',
    '0300', '0315', '0330', '0345', '0400', '0415', '0430', '0445', '0500', '0515', '0530', '0545',
    '0600', '0615', '0630', '0645', '0700', '0715', '0730', '0745', '0800', '0815', '0830', '0845',
    '0900', '0915', '0930', '0945', '1000', '1015', '1030', '1045', '1100', '1115', '1130', '1145',
    '1200', '1215', '1230', '1245', '1300', '1315', '1330', '1345', '1400', '1415', '1430', '1445',
    '1500', '1515', '1530', '1545', '1600', '1615', '1630', '1645', '1700', '1715', '1730', '1745',
    '1800', '1815', '1830', '1845', '1900', '1915', '1930', '1945', '2000', '2015', '2030', '2045',
    '2100', '2115', '2130', '2145', '2200', '2215', '2230', '2245', '2300', '2315', '2330', '2345'
];

foreach ($times as $time) {
    ${"selected_".$time} = ($time == $starttime) ? "SELECTED" : "";
}

// Initialize class selection variables
if (isset($class_names)) {
    foreach ($class_names as $code_key => $name) {
        ${"selected_".$code_key} = ($code_key == $class) ? "SELECTED" : "";
    }
}
${"selected_".$code} = "SELECTED";

//echo $class;exit;
//echo $year;exit;
//echo $selected_2007;
//echo $year;

?>

<?php include "ssi/top.php"; ?>
<!--start center content-->

<BR CLEAR="ALL">

<strong><span class="title">RESCHEDULE A CLASS</span></strong> &nbsp;&nbsp;<span class="copy"><em>(Step 1 of 2)</em></span>

<br><hr><br>
Four weeks advance notice is required for reserving a class.
<!-- <p> -->
<form action="reschedule_class.php?id=<?php echo $id; ?>" method="post">
<table cellpadding="0" cellspacing="4" border="0">
<tr>
<td valign="top" align="right"><span class="copy"><strong>Your class:</strong></span></td>
<td valign="top">
<select name="class" style="font-size: 10px;  font-family: verdana;">
<?php if (isset($class_names)): foreach ($class_names as $code => $name): ?>
<option <?php echo ${'selected_'.$code}; ?> value="<?php echo $code; ?>"><?php echo $name; ?></option>
<?php endforeach; endif; ?>
</select>
</td>
</tr>
<tr>
<td valign="top" align="right"><span class="copy"><strong>Select a month:</strong></span></td>
<td>
<table cellpadding="0" cellspacing="0" border="0">
<tr>
<td valign="top">
<select name="month" style="font-size: 10px;  font-family: verdana;">
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
<option <?php echo $selected_y_2025; ?> value="2025">2025</option>    
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
<?php if( $session_iscorp ) { ?>
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
<option <?php echo $selected_0730; ?> value="0730">7:30 AM</option>
<option <?php echo $selected_0745; ?> value="0745">7:45 AM</option>
<?php } ?>

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
<?php if( $session_iscorp ) { ?>

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
</select>
</td>
</tr>

<tr>
<td valign="top" align="right"><br><br></td>
<td>
<input onClick="return checkOK()" type="image" src="images/button_checkavail.gif">
</td>
</tr>
</table>
</form>

<?php if ($starttime) { ?>
<hr>
The <strong>green dates</strong> below are the closest available training dates for the class and month you selected.  Click on a date in green to schedule your class.
<p>
<div align="center">
<!-- start tiny calendars-->
<table cellpadding="0" cellspacing="0" border="0">
<tr>
<td valign="top">
<?php echo show_resched_calendar($id, $classes_this_month, $class, $starttime, $month, $year); ?>
</td>
<td><img src="images/dotclear.gif" width="20" align="center"></td>
<td valign="top">
<?php echo show_resched_calendar($id, $classes_this_month, $class, $starttime, $next_month, $next_year); ?>
</td>
</tr>
</table>
</div>
</p>
<?php } ?>
<!-- end tiny calendars-->

<BR><BR><BR><BR>

<!--end center content-->

<?php include "ssi/footer.php"; ?>

<!--end footer-->
</span>
</td>
<td valign="top" width="20" align="center"><img src="images/dotclear.gif" width="10"></td>
</tr>
</table>
<br><br>
</div>
</body>
</html>
<?php
require_once('mysql.php');

// --- Security Helper Functions ---
// Helper for HTML escaping (XSS mitigation)
if (!function_exists('h')) {
    function h($str) {
        return htmlspecialchars((string)($str ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
// ---------------------------------

if( getcurrentusertype() != 'principal' )
{
    // Always exit after a redirect header
    Header( "location: login.php" );
    exit;
}

// Get the company ID for the current user
$companyid = get_companyid($session_id);

// Get current date components
$today_month = (int)date("n");
$today_year = (int)date("Y");

// --- SQLi Mitigation: Sanitize User Input ---
// Get month and year from request, default to today's date
$month = $_REQUEST['month'] ?? $today_month;
$year = $_REQUEST['year'] ?? $today_year;

// Explicitly cast user inputs to integers for safe database usage and output
$month = (int)$month;
$year = (int)$year;
// ---------------------------------------------

// Set SELECTED variables for form output (safe since $month and $year are now integers)
// This restores the original variable assignment logic
for ($i = 1; $i <= 12; $i++) {
    if ($month === $i) {
        ${"selected_" . $i} = "SELECTED";
    }
}
// Assume the year range is 2025 and 2026 based on the original HTML
for ($i = 2025; $i <= 2026; $i++) {
    if ($year === $i) {
        ${"selected_" . $i} = "SELECTED";
    }
}

?>
<?php include "ssi/top.php"; ?>
<strong><span class="title">CALENDAR</span></strong>
<form method='post'>
<p>
<table cellpadding="0" cellspacing="2" border="0" width="100%">
<tr>
<td valign="top"><a href="calendar.php?month=<?=h($today_month)?>&year=<?=h($today_year)?>" class="copy"><strong>Go to Today</strong></a></td>
<td valign="top"><span class="copy">| Go to: </span></td>
<td valign="top">
<select name="month" style="font-size: 10px; font-family: verdana;">
<option value=""></option>
<option <?=$selected_1 ?? ''?> value="1">JAN</option>
<option <?=$selected_2 ?? ''?> value="2">FEB</option>
<option <?=$selected_3 ?? ''?> value="3">MAR</option>
<option <?=$selected_4 ?? ''?> value="4">APR</option>
<option <?=$selected_5 ?? ''?> value="5">MAY</option>
<option <?=$selected_6 ?? ''?> value="6">JUNE</option>
<option <?=$selected_7 ?? ''?> value="7">JULY</option>
<option <?=$selected_8 ?? ''?> value="8">AUG</option>
<option <?=$selected_9 ?? ''?> value="9">SEPT</option>
<option <?=$selected_10 ?? ''?> value="10">OCT</option>
<option <?=$selected_11 ?? ''?> value="11">NOV</option>
<option <?=$selected_12 ?? ''?> value="12">DEC</option>
</select>
</td>
<td valign="top">
<select name="" style="font-size: 10px; font-family: verdana;">
<option value=""></option>
<option value="01">01</option>
<option value="02">02</option>
<option value="03">03</option>
<option value="04">04</option>
<option value="05">05</option>
<option value="06">06</option>
<option value="07">07</option>
<option value="08">08</option>
<option value="09">09</option>
<option value="10">10</option>
<option value="11">11</option>
<option value="12">12</option>
<option value="13">13</option>
<option value="14">14</option>
<option value="15">15</option>
<option value="16">16</option>
<option value="17">17</option>
<option value="18">18</option>
<option value="19">19</option>
<option value="20">20</option>
<option value="21">21</option>
<option value="22">22</option>
<option value="23">23</option>
<option value="24">24</option>
<option value="25">25</option>
<option value="26">26</option>
<option value="27">27</option>
<option value="28">28</option>
<option value="29">29</option>
<option value="30">30</option>
<option value="31">31</option>
</select>
</td>
<td valign="top">
<select name="year" style="font-size: 10px; font-family: verdana;">
<option value=""></option>
<option <?=$selected_2025 ?? ''?> value="2025">2025</option>
<option <?=$selected_2026 ?? ''?> value="2026">2026</option>
</select>
</td>

<td><input type='submit' value='SEARCH' style='font-size:8px; height:16px;'></td>

<td valign="top" align="right">
<table cellpadding="0" cellspacing="0" border="0">
<tr>
<td valign="top"><img src="images/button_week.gif" border="0"></td><td valign="top"><img src="images/button_month.gif" border="0"></td>
</tr>
</table>
</td>
</tr>
</table>
</form>

<?php

// --- SQLi Mitigation: Use the sanitized $companyid, $year, and $month (all are integers/safe strings) ---
// Using the sanitized integer values in the original query style:
$sql = "
SELECT 
id, 
date_format(startdate, '%e') as startday,
date_format(startdate, '%I:%i %p') as starttime
FROM class
WHERE companyid = '{$companyid}'
AND deleted = 0
AND date_format( startdate, '%Y-%c' ) = '{$year}-{$month}'
";
$classes = db_query_rows($sql);

// Assuming show_big_calendar handles output escaping internally, but its source is unknown.
show_big_calendar($classes, $month, $year); 

?>

<br><br><br>

<?php include "ssi/footer.php" ; ?>

</span>
</td>
<td valign="top" width="15"><img src="images/dotclear.gif" width="10"></td>
</tr>
</table>
<br><br>
</div>
</body>
</html>
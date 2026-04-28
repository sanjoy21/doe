<?php
// NOTE: This script assumes 'mysql.php' provides the necessary database connection 
// and that session handling (which provides $session_id) is managed securely elsewhere.

require_once('mysql.php');

// Safely retrieve and validate user input or use current date
$today_month = date("n");
$today_year = date("Y");

$month = filter_input(INPUT_GET, 'month', FILTER_VALIDATE_INT) ?? 
         filter_input(INPUT_POST, 'month', FILTER_VALIDATE_INT) ?? 
         $today_month;

$year = filter_input(INPUT_GET, 'year', FILTER_VALIDATE_INT) ?? 
        filter_input(INPUT_POST, 'year', FILTER_VALIDATE_INT) ?? 
        $today_year;

// Enforce month range (1-12)
$month = ($month < 1 || $month > 12) ? $today_month : $month;
// Enforce year range (current year +/- 1)
$year = ($year < $today_year - 1 || $year > $today_year + 1) ? $today_year : $year;

// Array for SELECT option selection (replaces dynamic variable assignment)
$selected = [
    'month' => $month,
    'year' => $year,
];

// Assuming $session_id is the user's ID retrieved securely from the session.
// WARNING: Directly exposing the $session_id in a hidden field 
// and using it in an unauthenticated AJAX request is a security risk.
$user_id = $session_id ?? ''; 
$esc_user_id = htmlspecialchars($user_id);

?>
<?php include "ssi/top.php"; ?> 

<strong>
<span class="title">AVAILABILITY TO PERFORM DRILLS</span> <br>
<br><br> <i>Click on the dates you are available to mark them available or not available. 
<font color='green'>Green</font> is available, <font color='red'>Red</font> unavailable.</i>
 <br><br>
 <form method='post' action='drillcalendar.php'>
 <input type='hidden' id='sessid' value='<?=$esc_user_id?>'>
 <p>
<table cellpadding="0" cellspacing="2" border="0" width="100%">
 <tr>
 <td valign="top"><a href="drillcalendar.php?month=<?=$today_month?>&year=<?=$today_year?>" class="copy"><strong>Go to Today</strong></a></td>
 <td valign="top"><span class="copy">| Go to: </span></td>
 <td valign="top">
<select name="month" style="font-size: 10px; font-family: verdana;">
<option value=""></option>
<?php
$month_names = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUNE', 'JULY', 'AUG', 'SEPT', 'OCT', 'NOV', 'DEC'];
for ($i = 1; $i <= 12; $i++) {
    $selected_attr = ($selected['month'] == $i) ? 'SELECTED' : '';
    echo "<option {$selected_attr} value=\"{$i}\">{$month_names[$i-1]}</option>";
}
?>
 </select>
</td>
 <td valign="top">
<select name="year" style="font-size: 10px; font-family: verdana;">
 <option value=""></option>
<?php for( $i = date("Y"); $i <= date("Y") + 1; $i++ ) { ?>
 <option <?=($selected['year'] == $i)?"SELECTED":""?> value="<?=$i?>"><?=$i?></option> 
<?php }?>
 </select>
 </td>

<td><input type='submit' value='SEARCH' style='font-size:8px; height:16px;'></td>
 <td valign="top" align="right">
 <table cellpadding="0" cellspacing="0" border="0">
 <tr>
<td valign="top"></td><td valign="top"></td>
 </tr>
 </table>
 </td>
 </tr>
 </table>
</form>
<table> <tr><td>
<?php 
// Assuming show_drill_trainer_calendar() is safe and accepts sanitized inputs
show_drill_trainer_calendar($month, $year ); 
?>
</td><td valign='top'>
Not Selected: White<br>
Not Available: <font color='red'>Red</font><br>
Available: <font color='green'>Green</font>
</td></tr></table>
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

<script>
// WARNING: The AJAX call below uses a user ID exposed on the page ($esc_user_id) 
// and assumes the receiving script (updatedtc.php) validates the session/user 
// for the update. If validation is weak, this is vulnerable to unauthorized updates.
jQuery( document ).ready(function() {
    jQuery(".drilldt").click( function() {
        var cell = jQuery(this);
        jQuery.get( "updatedtc.php", { 
            // Use sanitized ID from hidden field
            id: jQuery("#sessid").val(), 
            // Get date from data attribute, which is assumed to be safe (Y-m-d)
            date: jQuery(this).data( "dt" ) 
        })
        .done(function( data ) {
            // Data received from updatedtc.php is used to set the background color
            // This assumes 'data' is a safe color string (e.g., 'red', 'green', '#FFFFFF')
            cell.css( "background-color", data );
        });
    } );
});
</script>
 
</html>
<?php
require_once('mysql.php');
$iscalendar = true;
if( !isOverallAdmin() )
{
    Header( "location: login.php" );
    exit;
}

$today_month = date("n");
$today_year = date("Y");

if (!isset($month) || !$month) {
  $month = $today_month;
}

if (!isset($year) || !$year) {
  $year = $today_year;
}

// Initialize selected variables for month dropdown
for($i = 1; $i <= 12; $i++) {
    ${"selected_".$i} = "";
}
${"selected_".$month} = "SELECTED";

// Initialize selected variables for year dropdown
$selected_2025 = "";
$selected_2026 = "";
if( isset(${"selected_".$year}) ) {
    ${"selected_".$year} = "SELECTED";
}

$dontcheckdates = true;
$currentlyusedtrainers = getAllTrainers( " and national = 0 " );

//print_r( $currentlyusedtrainers );exit;
$availcache = array();
if( isset($currentlyusedtrainers) && is_array($currentlyusedtrainers) ) {
    foreach( $currentlyusedtrainers as $t )
    {
    //    print_r( $t );
        if( isset($t["id"]) ) {
            $rows = db_query_rows( "select * from trainer_availability where trainerid = $t[id]" );
            $availcache[$t["id"]] = $rows;
        }
    }
}
//print_r( $availcache[271] );
?>
<?php include "ssi/top.php"; ?>
<!--start center content-->

<strong><span class="title">Trainer Availability CALENDAR</span></strong>
<form method='get'>
Search Zip: <input type='text' name='searchzip' value='<?php echo isset($searchzip) ? htmlspecialchars($searchzip) : ''?>'> <input type='submit' name='go' value='Go'>
</form>
<form method='post'>
<p>
<table cellpadding="0" cellspacing="2" border="0" width="100%"  class="table3">
        <tr>
        <td valign="top"><a href="trainer_avail_calendar.php?searchzip=<?php echo isset($searchzip) ? urlencode($searchzip) : ''?>&month=<?php echo $today_month?>&year=<?php echo $today_year?>" class="copy"><strong>Go to Today</strong></a></td>
<td valign="top"><span class="copy">| Go to: </span></td>
<td valign="top">
               <select name="month" style="font-size: 10px;  font-family: verdana;">
    <option value=""></option>
                                        <option <?php echo isset($selected_1) ? $selected_1 : ''?> value="1">JAN</option>
                                        <option <?php echo isset($selected_2) ? $selected_2 : ''?> value="2">FEB</option>
                                        <option <?php echo isset($selected_3) ? $selected_3 : ''?> value="3">MAR</option>
                                        <option <?php echo isset($selected_4) ? $selected_4 : ''?> value="4">APR</option>
                                        <option <?php echo isset($selected_5) ? $selected_5 : ''?> value="5">MAY</option>
                                        <option <?php echo isset($selected_6) ? $selected_6 : ''?> value="6">JUNE</option>
                                        <option <?php echo isset($selected_7) ? $selected_7 : ''?> value="7">JULY</option>
                                        <option <?php echo isset($selected_8) ? $selected_8 : ''?> value="8">AUG</option>
                                        <option <?php echo isset($selected_9) ? $selected_9 : ''?> value="9">SEPT</option>
                                        <option <?php echo isset($selected_10) ? $selected_10 : ''?> value="10">OCT</option>
                                        <option <?php echo isset($selected_11) ? $selected_11 : ''?> value="11">NOV</option>
                                        <option <?php echo isset($selected_12) ? $selected_12 : ''?> value="12">DEC</option>                                                                          
</select>
</td>
<td valign="top">
<select name="year" style="font-size: 10px;  font-family: verdana;">
<option value=""></option>
<option <?php echo isset($selected_2025) ? $selected_2025 : ''?> value="2025">2025</option>                                                                                                                            
<option <?php echo isset($selected_2026) ? $selected_2026 : ''?> value="2026">2026</option>                                                                                                                            
</select>
</td>

<td><input type='submit' value='SEARCH' style='font-size:8px; height:16px;'></td>

<td valign="top" align="right">
<table cellpadding="0" cellspacing="0" border="0"  class="table3">
<tr>
<b>Bold means BOOKED</b> | 
<font color='green'>Green means Staff</font> |<br>
<font color='blue'>Blue means Field Rep</font>
</tr>
</table>
</td>
</tr>
</table>
</form>

<?php
if( function_exists('show_big_trainer_avail_calendar') ) {
    show_big_trainer_avail_calendar($month, $year); 
}
?>

<br><br><br>
<!--end center content-->

<?php include "ssi/footer.php" ; ?>

<!--end footer-->
</span>
</td>
<td valign="top" width="15"><img src="images/dotclear.gif" width="10"></td>
</tr>
</table>
<br><br>
</div>
</body>
</html>
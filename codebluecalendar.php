<?php
require_once('mysql.php');

$companyid = get_companyid($session_id);

$today_month = date("n");
$today_year = date("Y");

if (!isset($month) || !$month) {
  $month = $today_month;
}

if (!isset($year) || !$year) {
  $year = $today_year;
}

${"selected_".$month} = "SELECTED";
${"selected_".$year} = "SELECTED";

?>
<?php include "ssi/top.php"; ?>
<!--start center content-->
<strong>

<strong><span class="title">Code Blue Availability</span> <a href='codeblueavail.php' target=_blank'>Search View</a></strong>
<br><br>
<form method='post'>
<input type='hidden' id='sessid' value='<?=$session_id?>'>
<p>
<table cellpadding="0" cellspacing="2" border="0" width="100%">
<tr>
<td valign="top"><a href="codebluecalendar.php?month=<?=$today_month?>&year=<?=$today_year?>" class="copy"><strong>Go to Today</strong></a></td>
<td valign="top"><span class="copy">| Go to: </span></td>
<td valign="top">
<select name="month" style="font-size: 10px;  font-family: verdana;">
<option value=""></option>
<option <?=isset($selected_1)?$selected_1:""?> value="1">JAN</option>
<option <?=isset($selected_2)?$selected_2:""?> value="2">FEB</option>
<option <?=isset($selected_3)?$selected_3:""?> value="3">MAR</option>
<option <?=isset($selected_4)?$selected_4:""?> value="4">APR</option>
<option <?=isset($selected_5)?$selected_5:""?> value="5">MAY</option>
<option <?=isset($selected_6)?$selected_6:""?> value="6">JUNE</option>
<option <?=isset($selected_7)?$selected_7:""?> value="7">JULY</option>
<option <?=isset($selected_8)?$selected_8:""?> value="8">AUG</option>
<option <?=isset($selected_9)?$selected_9:""?> value="9">SEPT</option>
<option <?=isset($selected_10)?$selected_10:""?> value="10">OCT</option>
<option <?=isset($selected_11)?$selected_11:""?> value="11">NOV</option>
<option <?=isset($selected_12)?$selected_12:""?> value="12">DEC</option>
</select>
</td>
<td valign="top">
<select name="year" style="font-size: 10px;  font-family: verdana;">
<option value=""></option>
<?php for( $i = date( "Y" ); $i <= date( "Y" ) + 1; $i++ ) { ?>
<option <?=isset($year) && $year == $i?"SELECTED":""?> value="<?=$i?>"><?=$i?></option> 
<?php } ?>
</select>
</td>

<td><input type='submit' value='SEARCH' style='font-size:8px; height:16px;'></td>
</tr>
</table>
</form>

<?php 
show_codeblue_calendar($month, $year ); 
?>

<br><br><br>
<!--end center content-->
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
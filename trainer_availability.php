<?php

require_once('mysql.php');

if( !isset($theid) || !$theid ) {
    $theid = isset($session_id) ? $session_id : 0;
}

if( isset($delid) && $delid ) {
    db_query( "delete from trainer_notavail where tnid = $delid "  );
}

if( $save_x || $saveandreturn ) {
    $startdatestr = $startdate?"'$startdate'":"NULL";
    $enddatestr = $enddate?"'$enddate'":"NULL";
    
    $body = "";
   
        foreach( $mynotavail as $key => $n ) {
            $end = $enddate[$key] ? $enddate[$key] : $n;
            $haspeak = false;
            if( trim( $n ) ) {
                $ins = db_query_insert_id( "insert into trainer_notavail ( trainerid, notavail, enddate ) values ( '$theid', '$n', '$end' )" );
                $haspeak = db_query_first_cell( "select count(*) from peakdates where dt >= '$n' and dt <= '$end'" );
            }
            if( $haspeak || (strtotime( $end ) - strtotime( $n ) > 3*24*60*60) ) {
                $body .= "The dates are: " . date( "m/d/Y", strtotime( $n ) ) . " - " . date( "m/d/Y", strtotime( $end ) ) . ($haspeak ? " - includes peak date" : "");
            }
        }
   
    
    if( $body ) {
        $name = getUserName( $theid );
        sendMail( "barbara@emergencyskills.com", "$name - Leave Request ", $body, "info@emergencyskills.com" );
    }
    
    db_query( "delete from trainer_availability where trainerid = $theid" );
    
    for( $i = 0; $i < 7; $i++ ) {
        if( isset($availonday[$i]) && $availonday[$i] ) {
            $starthour = $sh[$i];
            $endhour = $eh[$i];
            $sql = "insert into trainer_availability ( trainerid, weekday, startdate, enddate, starttime, endtime, repeattype, everyweeks ) values ( '" . intval($theid) . "', '" . intval($i) . "', $startdatestr, $enddatestr, '$starthour', '$endhour', 'weekly', '0' )";
            db_query( $sql );
        }
    }

    $classes = db_query_rows( "select class.* from trainer_to_class, class where startdate > now() and classid = class.id and trainer_to_class.trainerid = " . intval($theid) );
    $dates = "";
    $err = "";

        foreach( $classes as $crow ) {
            if( !availableOn($crow['startdate'], $theid ) ) {
                $dates .= $dates ? ", " : "";
                $dates .= date( "l m/d/y", strtotime( $crow['startdate'] ) );
            }
        }
    
    
    if( $dates ) {
        $err = "Warning: you are scheduled for class(es) on $dates. Please contact ESI immediately at 212-564-6833 with any conflicts.";
    }
    
    if( $dates ) {
        $row = db_query_first( "select * from user where id = '$theid'" );
        $userid = isset($row['userid']) ? $row['userid'] : '';
        sendMail( "barbara@emergencyskills.com", "Trainer availability possible conflict", "$userid : \n$err", "info@emergencyskills.com" );
    }

    if( $saveandreturn ) {
        Header( "Location: trainer_view.php?tid= $theid " );
        exit;
    }
}

$avail = db_query_rows( "select * from trainer_availability where trainerid = $theid order by startdate", "weekday" );

// Fetch peak dates for JavaScript validation
$peakdates_result = db_query_rows("select dt from peakdates where dt > now() order by dt");
$peakdates = array();
if( is_array($peakdates_result) ) {
    foreach( $peakdates_result as $p ) {
        if( isset($p['dt']) ) {
            $peakdates[] = $p['dt'];
        }
    }
}
?>
<?php include "ssi/top.php"; ?>
<!--start center content-->
<script language='javascript'>
Date.daysBetween = function( date1, date2 ) {
    var one_day = 1000 * 60 * 60 * 24;
    var date1_ms = date1.getTime();
    var date2_ms = date2.getTime();
    var difference_ms = date2_ms - date1_ms;
    return Math.round(difference_ms / one_day);
}

Date.peakBetween = function( date1, date2, peakdate ) {
    var date1_ms = date1.getTime();
    var date2_ms = date2.getTime();
    var peak_ms = peakdate.getTime();
    return peak_ms <= date2_ms && peak_ms >= date1_ms; 
}

function checkForm() {
    var okay = true;
    if( $("#startnew").val() && $("#endnew").val() ) {
        var from = new Date( $("#startnew").val() );
        var to = new Date( $("#endnew").val() );
        
        if( Date.daysBetween( from, to ) > 7 ) {
            alert( "You have requested more than 7 days off. Please reference the Leave Time Policy in the Employee Manual." );
        }
        
        <?php
        if( is_array($peakdates) ) {
            foreach( $peakdates as $p ) {
                if( strtotime( $p ) > time() ) {
                    ?>
                    var pd = new Date( "<?php echo addslashes($p); ?> 00:00:00" );
                    if( Date.peakBetween( from, to, pd ) ) {
                        if( !confirm( "Do you realize you requested a Peak Day?" ) ) {
                            okay = false;
                        }
                    }
                    <?php
                }
            }
        }
        ?>
    }
    return okay;
}
</script>

<form method='post' onSubmit='return checkForm()' action="trainer_availability.php?theid=<?php echo $theid; ?>">
<input type='hidden' name='theid' value='<?php echo $theid; ?>'>

<strong><span class="title"><?php echo ($theid == $session_id ? "My" : getFullname( $theid ) . "'s"); ?> Availability</span></strong>
<hr>
<table cellpadding="0" cellspacing="0" border="0" width="476">
    <tr>
        <td valign="top">
            <p><span class='copy'>
            <table><tr><td colspan='3'>I am available on: </td></tr>
<?php for( $i = 0; $i < 7; $i++ ) {
    $curravail = isset($avail[$i]) ? $avail[$i] : array();
    ?>
<tr><td class='copy'><?php echo getDayDisplay( $i ); ?>:</td>
    <td class='copy'><input type='radio' name='availonday[<?php echo $i; ?>]' value='0' <?php echo (!isset($avail[$i]) || !$avail[$i]) ? "CHECKED" : ""; ?>> No</td>
    <td class='copy'><input type='radio' name='availonday[<?php echo $i; ?>]' value='1' <?php echo (isset($avail[$i]) && $avail[$i]) ? "CHECKED" : ""; ?>> Yes
    &nbsp;&nbsp;&nbsp;    From: <select name='sh[<?php echo $i; ?>]'><option value='6'></option>
<?php for( $j = 6; $j <= 23; $j++ ) {
    $selected = (isset($curravail["starttime"]) && $curravail["starttime"] == $j) ? "SELECTED" : "";
    echo "<option value='$j' $selected>" . date( "g a", mktime( $j, 0 ) ) . "</option>";
}
?>
    </select>
    To: <select name='eh[<?php echo $i; ?>]'><option value='23'></option>
<?php for( $j = 6; $j <= 23; $j++ ) {
    $selected = (isset($curravail["endtime"]) && $curravail["endtime"] == $j) ? "SELECTED" : "";
    echo "<option value='$j' $selected>" . date( "g a", mktime( $j, 0 ) ) . "</option>";
}
?>
    </select>
    </td></tr>
<?php } ?>
    <tr><td colspan='4' class='copy'><i>
    Note: Classes are held every day starting as early as 6am and ending as late
    at 11pm. Please enter these times for dates you are available all day. </i></td></tr>
</table><br><br>

<table><tr><td colspan='4'><b>I am not available on these dates:</b> </td></tr>
<?php
$key = 0;
$mynotavail = db_query_rows( "select * from trainer_notavail where trainerid = '" . $theid . "' and enddate > now() order by notavail" );
if( is_array($mynotavail) ) {
    foreach( $mynotavail as $nrow ) {
        $n = $nrow['notavail'];
        $end = $nrow['enddate'];
        $tnid = $nrow['tnid'];
        ?>
        <tr><td class='copy'><?php echo $n; ?></td>
            <td>through</td>
            <td class='copy'><?php echo $end; ?></td>
            <td><a onClick="return confirm('Are you sure you want to delete this?')" 
                   href="trainer_availability.php?theid=<?php echo $theid; ?>&delid=<?php echo $tnid; ?>">Del?</a>
            </td>
        </tr>
        <?php
        $key++;
    }
}
?>
<tr><td class='copy' colspan='4'> <br><br><b>Add new dates you are unavailable below:</b></td></tr>
<tr><td class='copy'><?php echo printdates2( "mynotavail[{$key}]", "", false, "startnew" ); ?></td>
    <td>through</td>
    <td class='copy'><?php echo printdates2( "enddate[{$key}]", "", false, "endnew" ); ?></td>
</tr>
</table>

<input type='hidden' name='lastid' value='<?php echo $key; ?>'>       
<input type='image' name='save' src="images/button_save.gif" alt="Save">
<input type='submit' name='saveandreturn' value='Save And Return'>
<br><font color='red'><b>Please refer to Leave Time Policy in the Employee Manual</b></font>
</span>
</td>
<td valign="top" width="15"><img src="images/dotclear.gif" width="10" alt=""></td>
</tr>
</table>

<BR><BR><BR><BR>
<!--end center content-->
<?php include "ssi/footer.php"; ?>
<!--end footer-->
</span>
<br><br>
<?php if( isset($err) && $err ) { ?>
<script language='javascript'>
alert( "<?php echo addslashes($err); ?>" );
</script>
<?php } ?>
</div>
</body>
</html>
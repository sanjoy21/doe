<?php
require_once('mysql.php');

if( isset($goroster) && $goroster )
    {
        db_query( "delete from namevaluepair where name = 'rosterstr' " );
        $newroster_safe = isset($newroster) ? mysql_escape_string( stripslashes( $newroster ) ) : '';
        db_query( "insert into namevaluepair ( name, value ) values( 'rosterstr', '" . $newroster_safe . "' )" );
    }

if( isset($delcomdrill) && $delcomdrill )
{
    $delcomdrillid_safe = isset($delcomdrillid) ? $delcomdrillid : '';
    db_query( "delete from drill where completed = 0 and drillid < $delcomdrillid_safe" );
}

if( isset($delcomsc) && $delcomsc )
{
    $delcomscid_safe = isset($delcomscid) ? $delcomscid : '';
    db_query( "delete from servicecall where completed = 0 and servicecallid < $delcomscid_safe" );
}

if( isset($update) && $update )
{
    $session_userid_safe = isset($session_userid) ? $session_userid : '';
    if( $session_userid_safe == "sarahg@emergencyskills.com" )
        db_query( "delete from namevaluepair where name <> 'rosterstr' " );
    else
        db_query( "delete from namevaluepair where name <> 'rosterstr' and name <> 'homemess' " );
    
    if( isset($us) && is_array($us) ) {
        foreach( $us as $u=>$val )
        {
            $u_safe = $u;
            $val_safe = isset($val) ? $val : '';
            db_query( "insert into namevaluepair ( name, value ) values ( '$u_safe', '$val_safe' )" );
        }
    }
}

if( !isset($specialadmin) || !$specialadmin )
{
//Header( "location: login.php" );
//        exit;
}
$us = db_query_rows( "select * from namevaluepair order by name" );
?>
<?php include "ssi/top.php"; ?>
<p>

<strong><span class="title">MANAGE SETTINGS</span></strong>

<p>
<!--start center content-->
<form method='post'>
<table width='100%'  class="table3">  
<tr><td valign='top'><table >
<tr><td valign="top"><a href="trainer_avail_calendar.php" class="doenav">Trainer Avail Calendar</a></td></tr>
<!-- <tr><td valign="top"><a href="cms/" class="doenav">TSI class editing</a></td></tr>-->
<tr><td valign="top"><a href="passwords.php" class="doenav">Passwords</a></td></tr>
<tr><td valign="top"><a href="blockeddates.php" class="doenav">Blocked Dates</a></td></tr>
<tr><td valign="top"><a href="opendates.php" class="doenav">Open Dates</a></td></tr>
<tr><td valign="top"><a href="peakdates.php" class="doenav">Peak Dates</a></td></tr>
<tr><td valign="top"><a href="okdates.php" class="doenav">OK Summer Dates</a></td> </tr>
<?php if( isset($session_userid) && $session_userid == "sarahg@emergencyskills.com" ) { ?>
<tr><td valign="top"><a href="uploaddocuments.php" class="doenav">Upload Trainer Docs</a></td> </tr>
<?php } ?>
</table></td><td valign='top'><table>
<tr><td valign="top"><a href="reportlists.php" class="doenav">Report List</a></td> </tr>
<tr><td valign="top"><a href="nodrilldates.php" class="doenav">No Drill Dates</a></td> </tr>
<tr><td valign="top"><a href="badzips.php" class="doenav">Banned Zips</a></td> </tr>
<tr><td valign="top"><a href="badschoolids.php" class="doenav">Banned School IDs</a></td> </tr>
<tr><td valign="top"><a href="trainernotes.php" class="doenav">Trainer Calendar Notes</a></td>
<tr><td valign="top"><a href="adminnotes.php" class="doenav">ADMIN Calendar Notes</a></td>
<tr><td valign="top"><a href="territories.php" class="doenav">Territories</a></td> </tr>
</table>
</td></tr></table>
<table cellpadding="4" cellspacing="1" border="0" width="100%" bgcolor="#999999"  class="table3">
<tr bgcolor="#e1e1f6"><th class='copy'>Name</th><th class='copy'>Value</th></tr>
<?php 
if( isset($us) && is_array($us) ) {
    foreach( $us as $t )
    {
        if( isset($t["name"]) && $t["name"] == 'rosterstr' ) continue;
        if( isset($t["name"]) && $t["name"] == 'homemess' ) continue;
        echo( "<tr bgcolor='white'><td class='copy' valign='top'>" . (isset($t["name"]) ? $t["name"] : '') . "</td>" );
        echo( "<td  align='center' valign='top'><input type='text' name='us[".(isset($t["name"]) ? $t["name"] : '')."]' value='" . (isset($t["value"]) ? htmlspecialchars($t["value"]) : '') . "' ></td></tr>\n" );
    }
}
?>
</table><p>
<?php if( isset($session_userid) && $session_userid == "sarahg@emergencyskills.com" ) { ?>
<b>Login page message: </b>
<br><font color='red'>&lt;font color='red'&gt; for red &lt;/font&gt;</font><br>
<textarea style='width:500px; height: 200px' name='us[homemess]'><?php echo htmlspecialchars(getsetting( "homemess" ))?></textarea>
<br><br>
<?php } ?>
<input type='submit' name='update' value='Update'><br><br><br>
<!--Delete incomplete drills with IDs less than <input type='text' name='delcomdrillid' size='4'> <input type='submit' name='delcomdrill' value='Delete'><br><br><br>
Delete incomplete service calls with IDs less than <input type='text' name='delcomscid' size='4'> <input type='submit' name='delcomsc' value='Delete'><br><br><br>-->
</form>
<form method='post'>
<?php $rosterstr = getsetting( "rosterstr" ) ;?>
Dashboard Text: <textarea cols=60 name='newroster'><?php echo isset($rosterstr) ? htmlspecialchars($rosterstr) : ''?></textarea> <input type='submit' name='goroster' value='Save'>
</form>

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
<?php include "mysql.php"; ?>
<?php
if( isset($undel) && $undel && isset($responderid) && $responderid )
{
    db_query( "update responders_esi set deleted = 0 where responderid = " . intval($responderid) );
}

$row = array();
if( isset($responderid) && $responderid )
{
    $row = db_query_first("select * from responders_esi where responderid='" . intval($responderid) . "'");
}

$tmpr = array();
if( isset($row['responderid']) && $row['responderid'] )
{
    $tmpr = db_query_first( "select class.*, trainingdate from responder_training_dates left join class on class.id = classid where responderid = '" . intval($row['responderid']) . "' order by startdate desc" ); 
}

$dt = '';
if( isset($tmpr["id"]) && $tmpr["id"] )
{
    $dt = isset($tmpr["startdate"]) ? $tmpr["startdate"] : '';
}
else
{
    if( isset($tmpr["trainingdate"]) && $tmpr["trainingdate"] )
    {
        $dt = $tmpr["trainingdate"];
    }
    else if( isset($row["trainingdate"]) && $row["trainingdate"] )
    {
        $dt = $row["trainingdate"];
    }
}

$nextsched = array();
if( isset($row['responderid']) && $row['responderid'] )
{
    $nextsched = db_query_rows( "select class.* from responder_to_class left join class on class.id = classid and class.deleted = 0 where responderid = '" . intval($row['responderid']) . "' and startdate > Now() order by startdate" ); 
}
?>

<?php include "ssi/top.php"; ?>
<!--start center content-->
<?php if( isset($specialadmin) && $specialadmin ) { ?>
<table cellpadding="5" cellspacing="1" border="0" width="100%">
<tr>
<td valign="top" align="right" bgcolor="#ffffff" colspan="2"><span class="small"><strong>
<a href="viewcompany.php?id=<?php echo isset($row["clientid"]) ? htmlspecialchars($row["clientid"]) : ''; ?>">&laquo; Back to Admin Main</a>
</strong></span></td>
</tr>
</table>
<?php } ?>
<br>
<?php if( !isset($row['clientid']) || !$row['clientid'] ) { ?><font color='red'><b>This person has no <?php echo getSchoolStr( "school" ); ?>!</b></font><?php } ?>
<?php if( isset($row['deleted']) && $row['deleted'] ) { ?><font color='red'><b>This person is deleted! <?php if( isset($specialadmin) && $specialadmin ) { ?><a href='viewresponder.php?responderid=<?php echo isset($row['responderid']) ? htmlspecialchars($row['responderid']) : ''; ?>&undel=1' onClick='return confirm( "Are you sure you want to undelete this person?" )'>Undelete?</a><?php } ?><br>
<?php 
$liveid = 0;
if( isset($row['clientid']) && $row['clientid'] && isset($row['firstname']) && isset($row['lastname']) )
{
    $sql = "select responderid from responders_esi where clientid = " . intval($row['clientid']) . " and firstname like '" . addslashes($row['firstname']) . "' and lastname like '" . addslashes($row['lastname']) . "' and ( email = '' or email like '" . addslashes(isset($row['email']) ? $row['email'] : '') . "' ) and deleted = 0";
    $liveid = db_query_first_cell( $sql );
    
    if( !$liveid )
    {
        $sql = "select responderid from responders_esi where clientid = " . intval($row['clientid']) . " and firstname like '" . addslashes($row['firstname']) . "' and lastname like '" . addslashes($row['lastname']) . "' and deleted = 0";
        $liveid = db_query_first_cell( $sql );
    }
}
if( $liveid ) {
    echo( "<a onClick='return confirm( \"Are you sure?\" )' href='viewresponder.php?responderid=" . htmlspecialchars($liveid) . "&mergefrom=" . (isset($row['responderid']) ? htmlspecialchars($row['responderid']) : '') . "&mergeto=" . htmlspecialchars($liveid) . "'><font color='red'>Merge with non-deleted user?</font></a> <a href='viewresponder.php?responderid=" . htmlspecialchars($liveid) . "'><font color='red'>(View)</font></a>" );
}
?>
</b></font><?php } ?>
<table cellpadding="5" cellspacing="1" border="0" width="100%" class="table3" >
<tr>
<td valign="top" bgcolor="#5a179e" colspan="2">
<table bgcolor="#5a179e" width="100%" cellpadding="0" cellspacing="0" class="table3" border="0">
<tr><td><span class="white"><strong>Responder Information</strong></span></td>
<td valign="top" bgcolor="#5a179e" align="right">
<?php if( (!isset($readonly) || !$readonly) && isset($specialadmin) && $specialadmin ) { ?>
<a href="editresponder.php?id=<?php echo isset($row['clientid']) ? htmlspecialchars($row['clientid']) : ''; ?>&responderid=<?php echo isset($row['responderid']) ? htmlspecialchars($row['responderid']) : ''; ?>"><span class="white">[Edit Responder]</span></a>
<?php } ?>
</td></tr></table>
</td>
</tr>
<tr>
<td valign="top" bgcolor="#ffffff">
<table cellpadding="5" border="0">
<tr>
<td valign="top" bgcolor="#E2DFDF" align="right"><span class="copy"><strong><?php echo getSchoolStr( "School" ); ?>:</strong></td>
<td valign="top"><span class="copy"><?php if( isset($specialadmin) && $specialadmin ) { ?>
<a href='viewcompany.php?id=<?php echo isset($row["clientid"]) ? htmlspecialchars($row["clientid"]) : ''; ?>'><?php } ?><?php echo isset($row['clientid']) ? htmlspecialchars(getCompanyName( $row['clientid'] )) : ''; ?></a></span></td>
</tr>
<tr>
<td valign="top" bgcolor="#E2DFDF" align="right"><span class="copy"><strong>Name:</strong></td>
<td valign="top"><span class="copy"><?php echo isset($row['firstname']) ? htmlspecialchars($row['firstname']) : ''; ?> <?php echo isset($row['lastname']) ? htmlspecialchars($row['lastname']) : ''; ?> <?php if( isset($row['maidenname']) && $row['maidenname'] ) echo( "(" . htmlspecialchars($row['maidenname']) . ")" ); ?></span></td>
</tr>
<tr>
<td valign="top" bgcolor="#E2DFDF" align="right"><span class="copy"><strong>File Number:</strong></td>
<td valign="top"><span class="copy"><?php echo isset($row['filenumber']) ? htmlspecialchars($row['filenumber']) : ''; ?></span></td>
</tr>
<?php if( !isset($session_iscorp) || !$session_iscorp ) {  ?>
<tr>
<td valign="top" bgcolor="#E2DFDF" align="right"><span class="copy"><strong><?php echo getSchoolStr( "PMS ID" ); ?>:</strong></td>
<td valign="top"><span class="copy">#<?php 
                $pmsid_identifier = '';
                if( isset($row) )
                {
                    $pmsidvalidated = isset($row["pmsidvalidated"]) ? $row["pmsidvalidated"] : false;
                    $emptype = isset($row["emptype"]) ? $row["emptype"] : '';
                    $is_charter_or_custodial = ($emptype == "Charter School Employee" || $emptype == "Custodial Staff" || $emptype == "SSA");
                    $pmsid_identifier = getIdentifier( $row, isset($session_iscorp) ? $session_iscorp : false, !$pmsidvalidated, $is_charter_or_custodial );
                }
                echo $pmsid_identifier; ?></font>
<?php if( isOverallAdmin() ) { ?>   <a href='viewpmslog.php?id=<?php echo isset($row['responderid']) ? htmlspecialchars($row['responderid']) : ''; ?>'>View Log</a><?php } ?>
</span></td>
</tr>
<?php } ?>
<tr>
<td valign="top" bgcolor="#E2DFDF" align="right"><span class="copy"><strong>Title/Dept:</strong></td>
<td valign="top"><span class="copy"><?php echo isset($row['title']) ? htmlspecialchars($row['title']) : ''; ?>

<?php if( isOverallAdmin() && (!isset($session_iscorp) || !$session_iscorp) ) { ?>  
<br>Is Coach: <?php echo (isset($row['iscoach']) && $row['iscoach']) ? "Yes" : "No"; ?>
<?php }?>
</span>
</td>
</tr>
<tr>
<td valign="top" bgcolor="#E2DFDF" align="right"><span class="copy"><strong>Next Scheduled Training:</strong></td>
<td valign="top"><span class="copy">
<?php 
if( isset($nextsched) && is_array($nextsched) && count($nextsched) ) {
    foreach( $nextsched as $n )
    {
        if( isset($n['startdate']) && isset($n['id']) )
        {
?>
<?php echo fixdatefordisplay( $n['startdate'], true ); ?> <a href='class_detail.php?id=<?php echo htmlspecialchars($n['id']); ?>'><?php echo htmlspecialchars($n['id']); ?></a>
<?php
        }
    }
} ?>
</span></td>
</tr>
<tr>
<td valign="top" bgcolor="#E2DFDF" align="right"><span class="copy"><strong><?php echo getSchoolStr( "School" ); ?> No:</strong></td>
<td valign="top"><span class="copy"><?php echo isset($row['schoolno']) ? htmlspecialchars($row['schoolno']) : ''; ?></span></td>
</tr>
<tr>
<td valign="top" bgcolor="#E2DFDF" align="right"><span class="copy"><strong>Floor/Room:</strong></td>
<td valign="top"><span class="copy"><?php echo isset($row['apt']) ? htmlspecialchars($row['apt']) : ''; ?></span></td>
</tr>
<tr>
<td valign="top" bgcolor="#E2DFDF" align="right"><span class="copy"><strong>Daytime Phone:</strong></td>
<td valign="top"><span class="copy"><?php echo isset($row['dayphone']) ? htmlspecialchars($row['dayphone']) : ''; ?> <?php if( isset($row['dayphoneExtension']) && $row['dayphoneExtension'] ) echo( "Ext." ); ?><?php echo isset($row['dayphoneExtension']) ? htmlspecialchars($row['dayphoneExtension']) : ''; ?></span></td>
</tr>
<tr>
<td valign="top" bgcolor="#E2DFDF" align="right"><span class="copy"><strong>Home Address:</strong></td>
<td valign="top"><span class="copy"><?php echo isset($row['homeaddress']) ? htmlspecialchars($row['homeaddress']) : ''; ?></td>
</tr>
<?php if( isset($session_iscorp) && $session_iscorp ) { ?>
<tr>
<td valign="top" bgcolor="#E2DFDF" align="right"><span class="copy"><strong>Business Address:</strong></td>
<td valign="top"><span class="copy"><?php echo isset($row['busaddress']) ? htmlspecialchars($row['busaddress']) : ''; ?></td>
</tr>
<tr>
<td valign="top" bgcolor="#E2DFDF" align="right"><span class="copy"><strong>Business Building:</strong></td>
<td valign="top"><span class="copy"><?php echo isset($row['busbldg']) ? htmlspecialchars($row['busbldg']) : ''; ?></td>
</tr>
<tr>
<td valign="top" bgcolor="#E2DFDF" align="right"><span class="copy"><strong>Business Room:</strong></td>
<td valign="top"><span class="copy"><?php echo isset($row['busroom']) ? htmlspecialchars($row['busroom']) : ''; ?></td>
</tr>
<tr>
<td valign="top" bgcolor="#E2DFDF" align="right"><span class="copy"><strong>Business Floor:</strong></td>
<td valign="top"><span class="copy"><?php echo isset($row['busfloor']) ? htmlspecialchars($row['busfloor']) : ''; ?></td>
</tr>
<tr>
<td valign="top" bgcolor="#E2DFDF" align="right"><span class="copy"><strong>Business City, State, Zip:</strong></td>
<td valign="top"><span class="copy"><?php echo isset($row['buscity']) ? htmlspecialchars($row['buscity']) : ''; ?>, <?php echo isset($row['busstate']) ? htmlspecialchars($row['busstate']) : ''; ?>, <?php echo isset($row['buszip']) ? htmlspecialchars($row['buszip']) : ''; ?></td>
</tr>
<?php } ?>
<tr>
<td valign="top" bgcolor="#E2DFDF" align="right"><span class="copy"><strong>Email:</strong></td>
<td valign="top"><span class="copy"><a href="mailto:<?php echo isset($row['email']) ? htmlspecialchars($row['email']) : ''; ?>"><u><?php echo isset($row['email']) ? htmlspecialchars($row['email']) : ''; ?></u></a></span></td>
</tr>

<tr>
<td valign="top" bgcolor="#E2DFDF" align="right"><span class="copy"><strong>Most Recent Training Date:</strong></td>
<td valign="top"><span class="copy"><?php echo fixdatefordisplay( $dt, true ); ?></span>
<?php  
$noreportsorcalendar = isset($noreportsorcalendar) ? $noreportsorcalendar : array();
$thisusersrow = isset($thisusersrow) ? $thisusersrow : array();
$session_id = isset($session_id) ? $session_id : 0;

if( !in_array( $session_id, $noreportsorcalendar ) && (!isset($thisusersrow["healthdirector"]) || !$thisusersrow["healthdirector"]) ) { ?> 
    <?php if( isset($tmpr['id']) && $tmpr['id'] ) { ?>
        <a href='class_detail.php?id=<?php echo htmlspecialchars($tmpr['id']); ?>'><?php echo htmlspecialchars($tmpr['id']); ?></a>
    <?php } ?>
<?php } ?>
</td>
</tr>
<?php if( !isset($session_iscorp) || !$session_iscorp ) { ?>
<?php 
$noshow = array();
$sd = '';
if( isset($responderid) && $responderid )
{
    $sql = "select class.id, startdate from class, responder_to_class where responderid = " . intval($responderid) . " and classid = class.id and deleted = 0 and class.id not in ( select classid from responder_training_dates where responderid = " . intval($responderid) . " ) and startdate < now() order by startdate desc";
    $noshow = db_query_first($sql);    
    $sd = isset($noshow['startdate']) ? getFormattedDateWTime( $noshow['startdate'] ) : "";
}
$noshow_display = '';
if( isset($noshow['id']) && $noshow['id'] && $sd )
{
    $noshow_display = "<a href='class_detail.php?id=" . htmlspecialchars($noshow['id']) . "'>#" . htmlspecialchars($noshow['id']) . " - " . htmlspecialchars($sd) . "</a>";
}
?>

<?php if( !in_array( $session_id, $noreportsorcalendar ) && (!isset($thisusersrow["healthdirector"]) || !$thisusersrow["healthdirector"]) ) { ?> 
<tr>
<td valign="top" bgcolor="#E2DFDF" align="right"><span class="copy"><strong>No Show?</strong></td>
<td valign="top"><span class="copy"><?php echo $noshow_display ? $noshow_display : "&nbsp;"; ?></td>
</tr>
            <?php } ?>
   <?php } ?>
<tr>
<td valign="top" bgcolor="#E2DFDF" align="right"><span class="copy"><strong>Certification History:</strong></td>
<td valign="top"><span class="copy">
<table border=1 width='100%' cellspacing=0>
<tr><th class='copy'>Class date</th><th class='copy'>Class </th></tr>
<?php
    $tdates = array();
    if( isset($row['responderid']) && $row['responderid'] )
    {
        $tdates = getTrainingDates( $row['responderid'] );
    }
    
    if( isset($tdates) && is_array($tdates) )
    {
        foreach( $tdates as $t )
        {
            echo( "<tr><td class='copy'>" . (isset($t['trainingdate']) ? htmlspecialchars($t['trainingdate']) : '') . "</td>" );
            if( isset($t['classid']) && $t['classid'] && (!isset($thisusersrow["healthdirector"]) || !$thisusersrow["healthdirector"]) )
            {
                echo( "<td class='copy'><a href='class_detail.php?id=" . htmlspecialchars($t['classid']) . "'>View Class</a></td>" );
            }
            else
            {
                echo( "<td class='copy'>Data History " );
                if( isset($t['tprogram']) && $t['tprogram'] )
                { 
                    $class_names = isset($class_names) ? $class_names : array();
                    if( isset($class_names[$t['tprogram']]) )
                    {
                        echo( " (" . htmlspecialchars($class_names[$t['tprogram']]) . ")" );
                    }
                    else
                    {
                        echo( " (" . htmlspecialchars($t['tprogram']) . ")" );
                    }
                }
                echo( "</td>" );
            }
            echo( "</tr>" );
        }
    }
?>
</table>
</span>
</td>
</tr>
<?php if( 1 == 0 ) { ?>
<tr>
<td valign="top" bgcolor="#E2DFDF" align="right"><span class="copy"><strong>Completed Training Dates:</strong></td>
<?php ?>
<td valign="top"><span class="copy">
<?php 
// This section is disabled (1 == 0), keeping for reference
?>
</span></td>
</tr>
<?php } ?>
<?php if( !isset($session_iscorp) || !$session_iscorp ) { ?>
                        <tr>
<td valign="top" bgcolor="#E2DFDF" align="right"><span class="copy"><strong>Building Code:</strong></td>
<td valign="top"><span class="copy"><?php echo isset($row['buildingcode']) ? htmlspecialchars($row['buildingcode']) : ''; ?></span></td>
</tr>
<?php } ?>
<tr>
<td valign="top" bgcolor="#E2DFDF" align="right"><span class="copy"><strong>Notes:</strong></td>
<td valign="top"><span class="copy"><?php echo isset($row['notes']) ? htmlspecialchars($row['notes']) : ''; ?></span></td>
</tr>
<?php
$tdates = array();
if( isset($responderid) && $responderid )
{
    $tdates = getPreviousSchools( $row['responderid'] );
}
if( isset($tdates) && is_array($tdates) && count( $tdates ) ) 
{
?>
<tr><td colspan='2' bgcolor=#E2DFDF>
<span class='copy'><strong>Previous Schools</strong></span><br>
<span class='copy'>
<table border=1 width='50%'>
<tr><th class='copy'><?php echo getSchoolStr( "School" ); ?></th><th class='copy'>Date Moved</th></tr>
<?php
    foreach( $tdates as $t )
    {
        if( isOverallAdmin() )
        {
            echo( "<tr><td class='copy'><a href='viewcompany.php?id=" . (isset($t['clientid']) ? htmlspecialchars($t['clientid']) : '') . "'>" . (isset($t['clientid']) ? htmlspecialchars(getCompanyName( $t['clientid'] )) : '') . "</a></td>" );
        }
        else
        {
            echo( "<tr><td class='copy'>" . (isset($t['clientid']) ? htmlspecialchars(getCompanyName( $t['clientid'] )) : '') . "</td>" );
        }
        echo( "<td class='copy'>" . (isset($t['movedate']) ? htmlspecialchars($t['movedate']) : '') . "</td>" );
        echo( "</tr>" );
    }
?>
</table>
</td></tr>
<?php } ?>

</table>
</td>
</tr>
</table>
<br><br>
<!--end center content-->
<?php include "ssi/footer.php"; ?>
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
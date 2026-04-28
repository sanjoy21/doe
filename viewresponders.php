<?php 
$row = getCompanyRow( $id );
?>
<?php if( isOverallAdmin() || (isset($session_userid) && (strtolower( $session_userid ) == "amopper@schools.nyc.gov" || strtolower( $session_userid ) == "cmcgee3@schools.nyc.gov" || strtolower( $session_userid ) == "hthomps@schools.nyc.gov")) ) { ?>
<A href='reportrespondersgroup.php?id=<?php echo $id?>&xls=1'><span class='copy'><b>Export to XLS</b></span></a>
<?php if( !isset($donthide) || !$donthide ) { ?>
<A href='<?php echo isset($pagename) ? $pagename : ''?>?id=<?php echo $id?>&donthide=true'><span class='copy'><b>Show Expired</b></span></a>
<?php } else { ?>
<A href='<?php echo isset($pagename) ? $pagename : ''?>?id=<?php echo $id?>'><span class='copy'><b>Hide Expired</b></span></a>
<?php } ?>
<?php } ?>
<?php if( isset($session_userid) && (strtolower( $session_userid ) == "tpeele@schools.nyc.gov" || strtolower( $session_userid ) == "laustin@schools.nyc.gov" || strtolower( $session_userid ) == "mdavis65@schools.nyc.gov" || strtolower( $session_userid ) == "sgumbs4@schools.nyc.gov" || strtolower( $session_userid ) == "dtorres37@schools.nyc.gov" || strtolower( $session_userid ) == "hthomps@schools.nyc.gov" || ( isset($row["iscorp"]) && !$row["iscorp"] && isOverallAdmin()  ) ) )  { ?>
<a href='editpmsids.php?id=<?php echo isset($row["id"]) ? $row["id"] : ''?>'><b>Edit <?php echo getSchoolStr( "PMS IDs" )?></b></a><br>
<input type='text' size='5' name='bcode' id='bcode'> <input type='button' onClick='javascript: filterBcs( document.getElementById( "bcode" ).value )' value='Filter By Building Code'>
<?php } ?>
<br><div align="center">
<table width='100%'>
            <tr>
            <td valign="top"><span class="copy"><strong>
<table cellpadding="4" cellspacing="1" border="0" width="100%" bgcolor="#999999">
            <tr bgcolor="#e1e1f6"><!--<th><span class='copy'>#</th>--><th><span class='copy'>Responder</th>
<?php if( isset($row["iscorp"]) && !$row["iscorp"] ) { ?>
<th><span class='copy'>Building Code</span></th>
<?php } ?>

    <th><span class='copy'>Title</th> <?php if( !isset($readonly) || !$readonly ) { ?><th class=copy>Edit</th> <?php }  ?><th><span class='copy'>Last Training Date</th><th><span class='copy'>Expiration Date</th><!--<th><span class='copy'>Extension Expiration Date</th>--><th><span class='copy'>Class Type</th>
<?php  
// Added isset checks for session variables
if( !in_array( isset($session_id) ? $session_id : '', isset($noreportsorcalendar) ? $noreportsorcalendar : array() ) && ( !isset($thisusersrow["healthdirector"]) || !$thisusersrow["healthdirector"] ) ) { ?> 
      <th class=copy>Upcoming Class</th>
<?php } ?>
<?php if( isset($session_iscorp) && !$session_iscorp && ( !isset($thisusersrow["healthdirector"]) || !$thisusersrow["healthdirector"] ) ) { ?><th>No Show?</th><?php } ?><?php if( isset($specialadmin) && $specialadmin ) { ?><!--<th class='copy'>Delete</th>--><th class='copy'>Merge?</th><?php } ?></tr>
<?php

$count = 0;
if( isset($responder_rows) && is_array($responder_rows) ) {
    foreach( $responder_rows as $r )
    {
        $buildingcode = isset($r["responderid"]) ? db_query_first_cell( "select buildingcode from responders_esi where responderid = '$r[responderid]'" ) : '';
        $r["buildingcode"] = $buildingcode;
        $other = isset($r["firstname"], $r["lastname"], $r["responderid"]) ? db_query_first( "select * from responders_esi where firstname like '".mysql_escape_string( $r["firstname"] )."' and lastname like '".mysql_escape_string($r["lastname"] )."' and deleted = 0 and clientid = $id and responderid <> $r[responderid]" ) : array();
        $mostcurrent = isset($r["responderid"]) ? db_query_first( "Select responder_training_dates.*, class.code from responder_training_dates left join class on classid = class.id where responderid = $r[responderid] order by trainingdate desc" ) : array();
        $anyclasses = isset($r["responderid"]) ? db_query_first_cell( "Select count(*) from responder_to_class where responderid = $r[responderid] " ) : 0;

        if( isset($mostcurrent["code"]) && $mostcurrent["code"] == "cohfa" && !$firstaid )
        {
            continue;
        }
        if( (!isset($mostcurrent["code"]) || $mostcurrent["code"] != "cohfa") && isset($firstaid) && $firstaid )
        {
            continue;
        }
        $count++;
        $twoyears = 24*60*365*2*60;
        $mostcurrentdt = isset($mostcurrent["trainingdate"]) ? date( "m/d/y", strtotime( $mostcurrent["trainingdate"] ) ) : "N/A";
        $mostcurrentdt2 = isset($mostcurrent["trainingdate"]) ? date( "m/d/y", strtotime( $mostcurrent["trainingdate"]) + $twoyears ) : "N/A";
        
        $sql = isset($r["responderid"]) ? "select class.id, startdate from class, responder_to_class where responderid = $r[responderid] and classid = class.id and startdate > now() and deleted = 0 order by startdate" : "";
        $classdata = $sql ? db_query_first($sql) : array();
        //echo $sql . "<br>";
        $sd = isset($classdata["startdate"]) ? getFormattedDateWTime( $classdata["startdate"] ) : "";
        $upcoming = isset($classdata["id"]) ? "<a href='class_detail.php?id=$classdata[id]'>$sd</a>" : "";

        $sql = isset($r["responderid"]) ? "select class.id, startdate from class, responder_to_class where responderid = $r[responderid] and classid = class.id and deleted = 0 and class.id not in ( select classid from responder_training_dates where responderid = $r[responderid] ) and startdate < now() and ( startdate < '2020-03-10' or startdate > '2020-08-01' ) order by startdate desc" : "";
        // echo( $sql. "<br>"  );
        $noshow = $sql ? db_query_first($sql) : array();    

        $sd = isset($noshow["startdate"]) ? getFormattedDateWTime( $noshow["startdate"] ) : "";
        $noshow = isset($noshow["id"]) ? "NO SHOW: <a href='class_detail.php?id=$noshow[id]'>$sd</a>" : "";

            ?>
<tr class='bcodes <?php echo isset($r["buildingcode"]) ? strtoupper( $r["buildingcode"] ) : ''?>' bgcolor='#ffffff'>
<td>
                <span class="copy">
                <strong>
<?php  
// Added isset check for $thisusersrow
if( isset($thisusersrow) && !$thisusersrow["healthdirector"] ) {?>
     <a href="viewresponder.php?id=<?php echo isset($row['id']) ? $row['id'] : '';?>&responderid=<?php echo $r['responderid'];?>&redirect=<?php echo urlencode( $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'] )?>"><?php echo (isset($r['firstname']) ? $r['firstname'] : '') . ' ' . (isset($r['lastname']) ? $r['lastname'] : '');?></a>
<?php } else { ?>
<?php echo (isset($r['firstname']) ? $r['firstname'] : '') . ' ' . (isset($r['lastname']) ? $r['lastname'] : '');?>
<?php } ?>
</td>

<?php if( isset($row["iscorp"]) && !$row["iscorp"] ) { ?>
<td><?php echo isset($r["buildingcode"]) ? $r["buildingcode"] : ''?></td>
<?php } ?>
<td class='copy'><?php echo isset($r["title"]) ? $r["title"] : ''?></td>
 <?php if( !isset($readonly) || !$readonly ) {  ?> 
<td class='copy'><a href="editresponder.php?id=<?php echo isset($row['id']) ? $row['id'] : '';?>&responderid=<?php echo $r['responderid'];?>&redirect=<?php echo urlencode( $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'] )?>">edit</a>
</td>
     <?php }
    $classtype = "";
// if( $responderid == 626529 )
// {
// print_r( $mostcurrent );
// }
    if( isset($mostcurrent["code"]) && $mostcurrent["code"] )
    {
        // Added isset check for $class_names array
        $classtype = isset($class_names[$mostcurrent["code"]]) ? $class_names[$mostcurrent["code"]] : $mostcurrent["code"];
    }
    else if( isset($mostcurrent["program"]) && $mostcurrent["program"] )
    {
        // Added isset check for $class_names array
        $classtype = isset($class_names[$mostcurrent["program"]]) ? $class_names[$mostcurrent["program"]] : (isset($mostcurrent["program"]) ? $mostcurrent["program"] : '');
    }
    else
    {
        $classtype = "N/A";
//        echo( "Select responder_training_dates.*, class.code from responder_training_dates left join class on classid = class.id where responderid = $r[responderid] order by trainingdate desc" );
    }
                        ?>
<td> <span class='copy'><?php echo $mostcurrentdt?></td>
<td> <span class='copy'><?php echo $mostcurrentdt2?></td>
<!--<td> <span class='copy'><?php echo getExtensionDate( $mostcurrentdt2 )?></td>-->
<td> <span class='copy'><?php echo $classtype?></td>
<?php  
// Added isset checks for session variables
if( !in_array( isset($session_id) ? $session_id : '', isset($noreportsorcalendar) ? $noreportsorcalendar : array() ) && ( !isset($thisusersrow["healthdirector"]) || !$thisusersrow["healthdirector"] ) ) { ?> 
     <td> <span class='copy'><?php echo $upcoming?$upcoming:"&nbsp;"?></td>
                                                                                               <?php } ?>
<?php if( isset($session_iscorp) && !$session_iscorp && ( !isset($thisusersrow["healthdirector"]) || !$thisusersrow["healthdirector"] ) ) { ?>
<td> <span class='copy'><?php echo $noshow?$noshow:"&nbsp;"?></td>
                <?php } ?>
     <?php if( isOverallAdmin() ) {  ?>
     <?php if( isset($other) && $other ) { ?>
     <td class='copy'><a onClick='javascript: return confirm( "Are you sure you want to merge this responder with <?php echo isset($other["firstname"]) ? $other["firstname"] : ''?> <?php echo isset($other["lastname"]) ? $other["lastname"] : ''?>? Note: the user you clicked on will be deleted." )' href='<?php echo isset($pagename) ? $pagename : ''?>?id=<?php echo $id?>&mergefrom=<?php echo isset($r["responderid"]) ? $r["responderid"] : ''?>&mergeto=<?php echo isset($other["responderid"]) ? $other["responderid"] : ''?>'>merge with <?php echo isset($other["firstname"]) ? $other["firstname"] : ''?> <?php echo isset($other["lastname"]) ? $other["lastname"] : ''?></a></td>
                       <?php } else { ?>
<td>&nbsp;</td>
               <?php  }?>
</tr>
                         <?php } else {   ?>
<td>&nbsp;</td>
<?php } ?>
            <?php
                    }
}
            ?>
</table>
            </td>               
            </tr>
            </table></div><br>
<script language='javascript'>
function filterBcs( code )
{
code = code.toUpperCase();

    if( code > '' )
{
    $('.bcodes').show();
    $('.bcodes').not('.' + code).hide();

}
else
{
    $('.bcodes').show();
}
}
// Added isset check for $firstaid variable
document.getElementById( "<?php echo isset($firstaid) && $firstaid ? 'farcount' : 'respcount'?>" ).innerHTML = "<?php echo $count?>";
</script>
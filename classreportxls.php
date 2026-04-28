<table>
<tr>
<td>Course Number</td>
<td>Course Date</td>
<td>Course Type</td>
<td>Host <?=getSchoolStr( "School" )?> Name</td>
<?php if( !isset($session_iscorp) || !$session_iscorp ) { ?>
<td>Host <?=getSchoolStr( "School" )?> Number</td>
<?php } ?>
<td>Host <?=getSchoolStr( "School" )?> Address</td>
<?php
if( !isset($session_iscorp) || !$session_iscorp ) { ?>
<td>Borough</td>
<?php } ?>
<td>Banned Zip</td>
<?php if( isset($session_iscorp) && $session_iscorp ) { ?>
<td>Host <?=getSchoolStr( "School" )?> ID</td>
<?php } ?>
<td>Training Location</td>
<td>Contact Name</td>
<td>Contact Email</td>
<td>Contact Phone</td>
<td>Max Students</td>
<td>Number of Students</td>
<td>Number of Certifications</td>
<td>Cancellation Date</td>
<td>Host Confirm Date</td>
<td>TC Faculty</td>
<td>Trainer</td>
<td>Number of Trainers</td>
<?php if( isset($session_iscorp) && $session_iscorp ) {  ?>
<td>Gets EBooks</td>
<td>Books Mailed</td>
<td>Books Mailed Date</td>
<td>Completed By</td>
<td>eCards Sent</td>
<td>eCards Mailed Date</td>
<?php } else
{ ?>
<td>Blended Learning</td>
<td>eCards Sent</td>
<td>eCards Mailed Date</td>
<td>Books Mailed</td>
<td>Books Mailed Date</td>
<?php }
?>
<td>Practice Code Blue Paperwork Received</td>
<td>Invoice #</td>
<td>Course Fee#</td>
<td>Invoice Notes</td>
<td>Equipment Notes</td>
<td>Roster Received</td>
<?php     if( !isset($session_iscorp) || !$session_iscorp ) { ?>
<td>Confirmation Notes</td>
<td>Sent Confirm Names</td>
<?php } ?>
<td>Next Scheduled Training</td>
<td>Jumping To</td>
<td>bag set number</td>
<td>ship date</td>
<td>ship control number</td>
<td>return/jump date</td>
<td>return/jump control number</td>
<td>pending notes</td>
<td>pending status</td>
<?php     if( !isset($session_iscorp) || !$session_iscorp ) { ?>
<td>building permit</td>
<?php } ?>
</tr>
<?php
     $already = array();
foreach( $rep as $class )
{
    if( isset($already[$class["id"]]) && $already[$class["id"]] ) continue;
    $already[$class["id"]] = 1;
    $attendees = get_attendees( $class["id"] );
    $classinfo = getClassInfo( $class["id"] );
$i = count( $attendees );
$numcompleted = db_query_first_cell( "select count(*) from responder_training_dates where classid = " . $class["id"] );
$crow = getCompanyRow( $class["companyid"] );
$upcoming = db_query_first_cell("select max( startdate ) from class where companyid = '" . $class["companyid"] . "' and startdate > now()" );
?>
<tr>
<?php if( 1 || isset($onscreen) && $onscreen ) { ?>
<!-- getUrlPrefix($crow["iscorp"]) changed to getUrlPrefix(0) to stay in same domain (doe)  Sanjoy Dey -->
<td><a target=_blank href='https://<?=getUrlPrefix(0)?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/class_detail.php?id=<?=$class["id"]?>'><?=$class["id"]?></a></td>
<?php } else { ?>
<td><?=$class["id"]?></td>
<?php } ?>
<td><?=$class["startdate"]?></td>
<td><?=$class_names[$class["code"]]?></td>
<!-- getUrlPrefix($crow["iscorp"]) changed to getUrlPrefix(0) to stay in same domain (doe)  Sanjoy Dey -->
<td><a target=_blank href='https://<?=getUrlPrefix(0)?>.<?php echo URL_WITHOUT_SUBDOMAIN; ?>/viewcompany.php?id=<?=$class["companyid"]?>'><?=getCompanyName( $class["companyid"] )?></a></td>
<?php     if( !$crow["iscorp"] ) { ?>
<td><?=$crow["schoolcode"]?></td>
<?php } ?>
<td><?=getCompanyAddress( $class["companyid"] )?></td>
<?php     if( !$crow["iscorp"] ) { ?>
<td><?=$crow["borough"]?></td>
<?php } ?>
<td><?=in_array( $crow["zip"], $bannedzips )?"Yes":"No"?></td>
<?php     if( $crow["iscorp"] ) { ?>
<td><?=$class["companyid"]?></td>
<?php } ?>
<td><?=getTrainingAddress( $class )?></td>
<td><?=$class["firstname"] . " " . $class["lastname"]?></td>
<td><?=$class["email"]?></td>
<td><?=$class["phone"]?></td>
<td><?=$class["maxattendees"]?></td>
<td><?=$i?></td>
<td><?=$numcompleted?></td>
<td><?=isset($class["canceldate"]) && $class["canceldate"]?$class["canceldate"]:""?></td>
<td><?=isset($class["hostconfirmdate"]) && $class["hostconfirmdate"]?$class["hostconfirmdate"]:""?></td>
<td><?=isset($class["tcfacultyid"]) && $class["tcfacultyid"]?getFullname( $class["tcfacultyid"] ):""?></td>
<td>
<?php
$trainers = getTrainers( $class["id"]);
$any= false;
foreach( $trainers as $trainerid=>$trow ) { ?>
<?=$any?", ":""?>
        <?=getFullname( $trainerid )?>
<?php $any= true;?>
<?php }
$control = "";
$retcontrol = "";
//  if( isset($class["xpoid"]) && $class["xpoid"]  )
//  {
//      $control = json_decode( db_query_first_cell( "select retval from xpolog where classid = '" . $class["id"] . "' and retval like '%" . mysql_escape_string( $class["xpoid"] ) . "%' order by whensent desc limit 1" ) );
//      $control = ( $control->{$class["xpoid"]}->{"control_number"} );

//  }
//  if( isset($class["returnxpoid"]) && $class["returnxpoid"]  )
//  {
//      $hmm = db_query_first_cell( "select retval from xpolog where classid = '" . $class["id"] . "' and retval like '%" . mysql_escape_string( $class["returnxpoid"] ) . "%' order by whensent desc limit 1" );
// //         echo( "aaa:" . $hmm . ":bbb" );
//      $retcontrol = json_decode( $hmm );
// //     print_r( $retcontrol );
//      $retcontrol = ( $retcontrol->{$class["returnxpoid"]}->{"control_number"} );
// //     exit;
//  }
if( isset($class["birdieid"]) && $class["birdieid"]  ) {
    $control = json_decode( db_query_first_cell( "select retval from birdielog where classid = '" . $class["id"] . "' and retval like '%" . mysql_escape_string( $class["birdieid"] ) . "%' order by whensent desc limit 1" ) );
    if( $control && isset($control->{$class["birdieid"]}) ) {
        $control = $control->{$class["birdieid"]}->{"control_number"};
    }
}
if( isset($class["returnbirdieid"]) && $class["returnbirdieid"]  ) {
    $hmm = db_query_first_cell( "select retval from birdielog where classid = '" . $class["id"] . "' and retval like '%" . mysql_escape_string( $class["returnbirdieid"] ) . "%' order by whensent desc limit 1" );
    $retcontrol = json_decode( $hmm );
    if( $retcontrol && isset($retcontrol->{$class["returnbirdieid"]}) ) {
        $retcontrol = $retcontrol->{$class["returnbirdieid"]}->{"control_number"};
    }
}
?>
</td>
<td><?=$class["numtrainers"]?></td>
<?php if( $crow["iscorp"] ) {  ?>
<td><?=isset($class["getsebooks"]) && $class["getsebooks"]?"Yes":"No"?></td>
<td><?=isset($class["booksmaileddate"]) && $class["booksmaileddate"]?"Yes":"No"?></td>
<td><?=isset($class["booksmaileddate"])?$class["booksmaileddate"]:""?></td>
<td><?php
$r = db_query_rows( "select user.id, last_name, first_name from user, responder_training_dates where classid = '" . $class["id"] . "' and addedby = user.id order by last_name", "id" );
foreach( $r as $tmp ) {
echo( "$tmp[first_name] $tmp[last_name]; " );
}
?></td>
<td><?=isset($class["cardsmaileddate"]) && $class["cardsmaileddate"]?"Yes":"No"?></td>
<td><?=isset($class["cardsmaileddate"])?$class["cardsmaileddate"]:""?></td>
<?php } else { ?>
<td><?=isset($class["blendedlearning"]) && $class["blendedlearning"]?"Yes":"No"?></td>
<td><?=isset($class["cardsmaileddate"]) && $class["cardsmaileddate"]?"Yes":"No"?></td>
<td><?=isset($class["cardsmaileddate"])?$class["cardsmaileddate"]:""?></td>
<td><?=isset($class["booksmaileddate"]) && $class["booksmaileddate"]?"Yes":"No"?></td>
<td><?=isset($class["booksmaileddate"])?$class["booksmaileddate"]:""?></td>
<?php } ?>
<td><?=isset($class["pcbpreceived"])?$class["pcbpreceived"]:""?></td>
<td><?=isset($class["invoiceno"])?$class["invoiceno"]:""?></td>
<td><?=isset($class["coursefee"])?$class["coursefee"]:""?></td>
<td><?=isset($class["invoicenotes"])?$class["invoicenotes"]:""?></td>
<td><?=isset($class["equipnotes"])?$class["equipnotes"]:""?></td>
<td><?=isset($class["rosterreceived"]) && $class["rosterreceived"]?"Yes":"No"?></td>
<?php     if( !$crow["iscorp"] ) { ?>
<td><?=isset($class["confirmationnotes"])?$class["confirmationnotes"]:""?></td>
<td><?=isset($class["lastsentconfirmnames"]) && $class["lastsentconfirmnames"]?$class["lastsentconfirmnames"]:""?></td>
<?php } ?>
<td><?=$upcoming?></td>
<td><?=getJumpingTo($class["id"])?></td>
<td><?=isset($classinfo["Bagset"]["value"])?$classinfo["Bagset"]["value"]:""?></td>
<td><?=isset($classinfo["Pick Up Date"]["value"])?$classinfo["Pick Up Date"]["value"]:""?></td>
<td><?=$control?></td>
<td><?=isset($classinfo["Return Pick Up Date"]["value"])?$classinfo["Return Pick Up Date"]["value"]:""?></td>
<td><?=$retcontrol?></td>
<td><?=isset($class["pendingnotes"])?$class["pendingnotes"]:""?></td>
<td><?=isset($class["accepted"]) && $class["accepted"]?"Accepted":"Pending"?></td>
<?php     if( !$crow["iscorp"] ) { ?>
<td><?=isset($class["room_permit_no"])?$class["room_permit_no"]:""?></td>
<?php } ?>
</tr>
<?php
}
?>
<?php 
require "mysql.php";

// --- PHP 8.2 Initialization Block ---
// Initialize variables to prevent "Undefined Variable" warnings
$aaa = $_REQUEST['aaa'];
$help = $_GET['help'];
$isdashboard = 1;
// ------------------------------------

if( !isOverallAdmin() && ($thisusersrow["redirectURL"]) != "/dashboard.php" )
{
    header( "Location: /index.php");
    exit;
}

// if( $aaa )
// {
//     $d = db_query_rows("select * from class where startdate > now() and maxattendees = 12" );
//     if (is_array($d)) {
//         foreach( $d as $drow )
//         {
//             $num = db_query_first_cell( "select count(*) from responder_to_class where classid = {$drow['id']}" );
//             if( $num <= 10 )
//             {
//                 $sql = "update class set maxattendees = 10 where id = {$drow['id']}";
//                 echo( $sql . "<br>" );

//                 db_query( $sql );
//                 echo( "found $num, class is {$drow['id']}<Br>" );
//             }
//             else
//             {
//                 echo( "not setting, $num, {$drow['id']}<br>" );
//             }
//         }
//     }
//     exit;
// }

//print_r( $thisusersrow );
include "ssi/top.php";
?>      
    <?php    if( (1==0 ) || $help )
    {
        ?>
            <div class="box-header">
                <div class="box-title">Responders in 2 or more upcoming classes</div>
<br><br>        <table>
<?php
        $res = db_query_rows( "select responderid, group_concat( c.id ) as classes from responder_to_class rtc , class c where c.id = rtc.classid and startdate > now() and canceldate is null group by responderid having count(*) > 1" );
        if (is_array($res)) {
            foreach( $res as $r )
            {
                $rrow = getResponderRow( $r["responderid"] );
                $e = explode( ",", $r["classes"] );
                echo( "<tr><td>{$rrow['firstname']} {$rrow['lastname']}</td><td>{$rrow['email']}</td><td>" );
                foreach( $e as $cid )
                {
                    $sd = db_query_first_cell( "select startdate from class where id = $cid" );
                    echo( "<a href='class_detail.php?id=$cid'>$cid</a> ". getFormattedDateWTime( $sd ) . "<br>" );
                }
                echo( "</td></tr>" );
            }
        }
        
        ?>
        </table><br><br>
        <?php
    }
?>
<?php

    if( hasDash( "fewerthan6" ) ) {
?>                      
            <div class="box-header">
                <div class="box-title">Schools with Fewer than 6 Trained Responders</div>
                 <div class="box-title-right"></div>            
            </div><br>
<?php
$vis_region = $thisusersrow["visibleregion"];
$schools = getExpiredSchools( 5, getRegionDisp($vis_region) );
            ?>          
             <?php if (is_array($schools)) { 
                 foreach( $schools as $s ) { ?>
            <?=$s["companyname"] . " " . $s["schoolcode"] ?> <a href="viewcompany.php?id=<?=$s["id"]?>"><b>[View]</b></a>  (<?=$s["numdates"]?>)<br>
              <?php } 
             } ?>
         <?php if( !is_array($schools) || !count( $schools ) ) { echo( "None" ); } ?>
<br>            
<br>            
<br>
            <div class="box-header">
                <div class="box-title">Schools with No Current Trained Responders</div>
                 <div class="box-title-right"></div>            
            </div><br>
<?php
$schools = getExpiredSchools( 0, getRegionDisp($vis_region) );

            ?>          
             <?php if (is_array($schools)) {
                 foreach( $schools as $s ) { ?>
<?=$s["companyname"] . " " . $s["schoolcode"] ?> <a href="viewcompany.php?id=<?=$s["id"]?>"><b>[View]</b></a><br>
              <?php }
             } ?>
         <?php if( !is_array($schools) || !count( $schools ) ) { echo( "None" ); } ?>
<br>            
<br>            
<br>            
<?php }
?>          
<?php if( hasDash( "managecalls" ) ) {
    $fromdt = mktime( 0,0,0, (int)date( "m" ), (int)date( "d" )-1 );
    $todt = mktime( 0,0,0, (int)date( "m" ), (int)date( "d" )+ 4 );
?>                      
            <div class="box-header">
                <div class="box-title">Manage Calls</div>
                 <div class="box-title-right"><b>Week Of:</b> <?=date( "m/d/Y", $fromdt )?>-<?=date( "m/d/Y", $todt )?></div>          
            </div><div class="box-subhead">               
                 <div class="box-subtitle"><?=getSessionTypeDisplay()?></div>
                <div class="box-subtitle-right"><a href="recertnotesreport.php">Manage all <?=getSessionTypeDisplay()?> calls</a></div>                 
            </div><br>
<?php
     for( $i = -1; $i < 4; $i++ )
     {
         $fromdt = mktime( 0,0,0, (int)date( "m" ), (int)date( "d" ) + $i );
    $todt = mktime( 0,0,0, (int)date( "m" ), (int)date( "d" ) + 1  + $i );
    
    $whr = " and ( (recertnotes.recertperson = '$session_id') or recertnotes.assignedto = '$session_id' ) ";
    
    if( $session_userid == "barbara@emergencyskills.com") {
        // Assuming RECERTPERSON is a constant defined in mysql.php
        $recert_p = defined('RECERTPERSON') ? RECERTPERSON : 0;
         $whr = " and ( recertnotes.recertperson = '$session_id' or recertnotes.assignedto = '$session_id' or recertnotes.recertperson = 6629 
or recertnotes.recertperson = 15491 or recertnotes.assignedto = 15491 
or recertnotes.recertperson = 9235 or recertnotes.assignedto = 9235 
or recertnotes.assignedto = 15281 or recertnotes.recertperson = 15281
or recertnotes.assignedto = $recert_p or recertnotes.recertperson = $recert_p
 ) ";
    }

    $rows = db_query_rows( "select *, concat( schoolcode, companyname ) as longname from company_esi, recertnotes where recertnotes.companyid = company_esi.id and iscorp = '$session_iscorp' and completed = 0 $whr $visi and nextcalldate >= '".date( "Y-m-d", $fromdt ). "' and nextcalldate < '".date( "Y-m-d", $todt )."' order by nextcalldate, assignedto, longname" );
            ?>          
        <b><?=date( "m/d/Y", $fromdt )?></b><br>
             <?php if (is_array($rows)) {
                 foreach( $rows as $r ) {
                $p = str_replace( "\r\n", "<br\>", $r["recertificationnotes"] );
                $p = str_replace( "\n", "<br\>", $p );
                $p = str_replace( "'", "", $p );
                $p = str_replace( "\"", "", $p );
                ?>
              <a onMouseover="popup('<?=$p ?>', 'white')" onMouseout="kill()"  href="editrecertnotes.php?id=<?=$r["companyid"]?>"><b>[Edit]</b></a>  |  <a href="viewcompany.php?id=<?=$r['companyid']?>"><?=$r["companyname"]?></a>  | <?=getUserName( $r["assignedto"] ) ?><br>
              <?php } 
             } ?>
         <?php if( !is_array($rows) || !count( $rows ) ) { echo( "No open calls" ); } ?>
<br>            
     <?php } ?>
<br>            
<br>            
            <?php if( 1==0 && ($session_userid == "sarahg@emergencyskills.com"  || $session_userid == "rebekah@emergencyskills.com" )) { ?>          
            <div class="box-header">
                <div class="box-title">Shipped Drills</div>
                 <div class="box-title-right"></div>            
            </div><br>            <table class="table2">

<?php $drills = db_query_rows( "select  trackingno, shippeddate, doneby, group_concat( drillid ) as dids from drill where shipped = 1 and received = 0 and completed = 0 group by trackingno, doneby, shippeddate order by shippeddate" );
if (is_array($drills)) {
    foreach( $drills as $d )
    {
        $didstr = '';

        $dids = explode( ",", $d["dids"]);
        foreach( $dids as $did )
        {
            $didstr .= $didstr?", ":"";
            $didstr .= "<A href='editdrill.php?drillid=$did'>$did</a>";
        }
        // $dids = join( ", ", $dids ); // Unused variable assignment removed/kept logic above
        echo( "<tr><td>".($d["shippeddate"]?date( "m/d/Y", strtotime( $d["shippeddate"] ) ):""). "</td><td class=\"left\">" . getFullname( $d["doneby"] )."</td><td class=\"left\">{$d['trackingno']}</td><td>$didstr</td></tr>" );
    }
}
 ?>
</table>
<br>
<?php } ?>        
<?php } ?>        
<?php if( $session_userid == "sarahg@emergencyskills.com" ||$session_userid == "rebekah@emergencyskills.com" ||$session_userid == "noah@emergencyskills.com") {
?>                      
            <div class="box-header">
                <div class="box-title">Pending COO</div>
                 <div class="box-title-right"></div>            
            </div><br>

<?php $pend = db_query_rows( "select * from company_esi where pendingcoo = 1 order by companyname " );
if (is_array($pend)) {
    foreach( $pend as $d )
    {
        echo( "<a href='viewcompany.php?id={$d['id']}'>".$d["companyname"]."</a><br>" );
    }
}
 ?>
<br>
<hr style="border-top: 1px dotted #8c8b8b; border-bottom: 1px dotted #fff;">
<br>
<?php } ?>        
<?php if( hasDash( "classes" ) ) { ?>           
    <?php $rosterstr = getsetting( "rosterstr" ) ;
//     if( !$rosterstr )
//  {
//      $rosterstr = 'Please open this path to check your rosters:

// S:\Shared Folders\Course Documents\Class Roster Scans\2021 Class Roster Scans';
//  }
    ?>          
            
            <?php if( 1==0 ) { ?>
    <form method='post'>
           <textarea cols=60 name='newroster'><?=$rosterstr?></textarea> <input type='submit' name='goroster' value='Save'>
           </form>
    <?php }

    $rosterstr = nl2br( $rosterstr );
    ?>
       <b style="font-size: 16px"><?=$rosterstr?>
</b><Br></font>
       <br>
            <div class="box-header">
                <div class="box-title">Classes</div>
            </div><br>
<?php $locked = db_query_rows("select cl.id, startdate, companyid, pendingnotes, lockreason from class cl, company_esi c  where cl.deleted = 0 and cl.islocked = 1 and cl.startdate > now() and cl.companyid = c.id and iscorp = $session_iscorp order by cl.startdate " );

if( is_array($locked) && count( $locked ) ) {
?>
            <b><font color='red'>LOCKED CLASSES</font>:</b><br><br>
<?php 
    foreach( $locked as $c ) 
{
    // Define $f since it's used but not defined in loop logic here
    $f = ""; 
    echo( $f . date( "m/d/Y", strtotime( $c["startdate"] ) )." <a href=\"class_detail.php?id={$c['id']}\">{$f}".getCompanyName( $c["companyid"] )."</font></a> ".($c["lockreason"]?"- ".$c["lockreason"]:"")."</font><br>" );
}
echo( "<br>
<br>            
" );
}
?>
<b>Classes Not Yet Accepted / Pending:</b><br><br>
<?php 
$cl = db_query_rows( "select cl.id, startdate, companyid, pendingnotes from class cl, company_esi c  where cl.deleted = 0 and cl.accepted = 0 and cl.startdate > now() and cl.companyid = c.id and iscorp = $session_iscorp order by cl.startdate limit 5" );
if (is_array($cl)) {
    foreach( $cl as $c ) 
    {
        $num = db_query_first_cell( "select count(*) from reschedules where classid = {$c['id']}" );
        $f = $num > 1? "<font color='red'>":""; 
        echo( $f . date( "m/d/Y", strtotime( $c["startdate"] ) )." <a href=\"class_detail.php?id={$c['id']}\">{$f}".getCompanyName( $c["companyid"] )."</font></a> - {$c['pendingnotes']}</font><br>" );
    }
}
?>
<a href='outstandingclasses.php'>See All</a>
<br>
<br>            
<b>Quick Schedule Classes:</b><br><br>
<?php 
$cl = db_query_rows( "select cl.id, startdate, companyid, pendingnotes from class cl, company_esi c  where cl.deleted = 0 and confirmationnotes like '%Quick Schedule%' and cl.startdate > now() and cl.companyid = c.id and iscorp = $session_iscorp order by cl.startdate limit 5" );
if (is_array($cl)) {
    foreach( $cl as $c ) 
    {
    echo( date( "m/d/Y", strtotime( $c["startdate"] ) )." <a href=\"class_detail.php?id={$c['id']}\">".getCompanyName( $c["companyid"] )."</a><br>" );
    }
}
?>
<A href='outstandingclasses.php'>See All</a>
<br>
<Br>            
            <b>Classes Complete, But No Roster:</b><br><br>
<?php 
$cl = db_query_rows( "select class.id, startdate, companyid, pendingnotes from class where startdate > '2012-01-01' and startdate< now() and deleted = 0 and accepted = 1 and rosterreceived = 0 order by startdate limit 5" );
if (is_array($cl)) {
    foreach( $cl as $c ) 
    {
    echo( date( "m/d/Y", strtotime( $c["startdate"] ) )." <a href=\"class_detail.php?id={$c['id']}\">".getCompanyName( $c["companyid"] )."</a><br>" );
    }
}
?>
<A href='outstandingclasses.php'>See All</a>
     <Br><br>
     <?php } ?>
     <?php if( hasDash( "doeexpirations" ) ) { ?>                      
            <div class="box-header">
                <div class="box-title">DOE Expirations</div>
            </div><br>

            <table class="table2">
                <tr>
                    <th></th>
                    <th>FR2 Adult Pads</th>
                    <th>FR2 Ped. Pads</th>
                    <th>FRx Pads</th>
                    <th>Responder Cert.</th>
                </tr>
<?php for( $i = 0; $i < 3; $i++ )
{
    $fromdt = mktime( 0,0,0, (int)date( "m" ) + $i, 1, (int)date( "Y" )  );
    $todt = mktime( 0,0,0, (int)date( "m" ) + $i +1, 1,  (int)date( "Y" ) );
?>
                <tr>
                    <td class="left"><b><?=date( "M", $fromdt )?>:</b></td>
    <?php 
    $cnt = getDashCache( "fr2doe", $fromdt, "select count( distinct( serial ) ) from aed_esi where model like '%fr2%' and ( ( padaexpiration >= '".date( "Y-m-d", $fromdt ) . "' and padaexpiration < '".date( "Y-m-d", $todt ) . "') or ( padbexpiration >= '".date( "Y-m-d", $fromdt ) . "' and padbexpiration < '".date( "Y-m-d", $todt ) . "') ) and aedmissing = 0 and aedstolen = 0 and deleted = 0 and clientid in ( select id from company_esi where iscorp = 0 and outofservice = 0 and deleted = 0 and showsondrillreports = 1 )  " );    
?>
                    <td><a href="expiringaeds.php?fromdt=<?=$fromdt?>&todt=<?=$todt?>&type=fr2"><?=$cnt?></a></td>
<?php $cnt = getDashCache( "fr2peddoe", $fromdt, "select count( distinct( serial ) ) from aed_esi where model like '%fr2%' and ( pediatricpads >= '".date( "Y-m-d", $fromdt ) . "' and pediatricpads < '".date( "Y-m-d", $todt ) . "')  and aedmissing = 0 and aedstolen = 0 and deleted = 0 and clientid in ( select id from company_esi where iscorp = 0 and deleted = 0 and outofservice = 0 and showsondrillreports = 1 )  " );
?>
                    <td><a href="expiringaeds.php?fromdt=<?=$fromdt?>&todt=<?=$todt?>&type=fr2ped"><?=$cnt?></a></td>
<?php $cnt = getDashCache( "frxdoe", $fromdt, "select count( distinct( serial ) ) from aed_esi where model= 'FRX' and ( ( padaexpiration >= '".date( "Y-m-d", $fromdt ) . "' and padaexpiration < '".date( "Y-m-d", $todt ) . "') or ( padbexpiration >= '".date( "Y-m-d", $fromdt ) . "' and padbexpiration < '".date( "Y-m-d", $todt ) . "') )  and deleted = 0 and aedmissing = 0 and aedstolen = 0 and outofservice = 0 and clientid in ( select id from company_esi where iscorp = 0 and deleted = 0 and showsondrillreports = 1 )  " );
?>
                    <td><a href="expiringaeds.php?fromdt=<?=$fromdt?>&todt=<?=$todt?>&type=frx"><?=$cnt?></a></td>
<?php
    $fromdt = mktime( 0,0,0, (int)date( "m" ) + $i, 1, (int)date( "Y" ) - 2 );
    $todt = mktime( 0,0,0, (int)date( "m" ) + $i +1, 1,  (int)date( "Y" ) - 2 );
    $cnt = getDashCache( "doeresp", $fromdt, "select count( distinct( r.responderid ) ) from responders_esi r where r.responderid in (select responderid from responder_training_dates where trainingdate >= '".date( "Y-m-d", $fromdt ) . "' and responderid not in ( select responderid from responder_to_class, class where startdate > now() and class.id = classid ) and trainingdate < '".date( "Y-m-d", $todt ) . "') and deleted = 0 and clientid in ( select id from company_esi where iscorp = 0 and deleted = 0 and showsondrillreports = 1 ) and r.responderid not in ( select responderid from responder_training_dates where trainingdate >= '".date( "Y-m-d", $todt ) . "' )" );
?>
                    <td><a href="expiringresponders.php?fromdt=<?=$fromdt?>&todt=<?=$todt?>"><?=$cnt?></a></td>
                </tr>
<?php } ?>
</table>
<br>
<?php } ?>        
<?php if( 1==0 && hasDash( "fr2_replacements" ) ) { ?>                    
            <div class="box-header">
                <div class="box-title">FR2 Replacements</div>
            </div><br>
<?php
           $psal = db_query_first_cell("select count(*) from aed_esi, company_esi where  showsondrillreports = 1 and aed_esi.clientid = company_esi.id and outofservice = 0 and serial like '0%' and iscorp = 0 and aed_esi.deleted = 0 and aedmissing =0 and outofservice = 0 and aedstolen = 0 and location = 'PSAL'");
           $nonpsal = db_query_first_cell("select count(*) from aed_esi, company_esi where showsondrillreports = 1 and  aed_esi.clientid = company_esi.id  and outofservice = 0 and serial like '0%' and iscorp = 0 and aed_esi.deleted = 0 and aedmissing =0 and outofservice = 0 and aedstolen = 0 and location <> 'PSAL' ");
           ?>
            <table class="table2">
                <tr>
                    <th>PSAL Units</th>
                    <th>Non PSAL Units</th>
           </tr>
           <tr><td><A href='fr2left.php?psal=1'><?=$psal?></a></td><td><a href='fr2left.php'><?=$nonpsal?></a></td></tr>
           </table>
           
            
            <br>
<?php } ?>        
            
<?php if( hasDash( "corpexpirations" ) ) { ?>                      
            <div class="box-header">
                <div class="box-title">Corporate  Expirations</div>
            </div><br>

            <table class="table2">
                <tr>
                    <th></th>
                    <th>Adult Pads</th>
                    <th>Ped. Pads</th>
                    <th>MD Filing</th>
                    <th>Last Training > 10 Mo. Ago</th>
                    <th>Cards Exp. in 60 Days</th>
                    <th>No Trained Resp.</th>
                </tr>
<?php
$dontcount = "  and id <> 11345 and excludereporting = 0  ";
for( $i = 0; $i < 3; $i++ )
{
    $fromdt = mktime( 0,0,0, (int)date( "m" ) + $i, 1, (int)date( "Y" )  );
    $todt = mktime( 0,0,0, (int)date( "m" ) + $i +1, 1,  (int)date( "Y" ) );
?>
                <tr>
                    <td class="left"><b><?=date( "M", $fromdt )?>:</b></td>
<?php $cnt = getDashCache( "fr2corp3", $fromdt, "select count( distinct( serial ) ) from aed_esi where ( ( padaexpiration >= '".date( "Y-m-d", $fromdt ) . "' and padaexpiration < '".date( "Y-m-d", $todt ) . "') or ( padbexpiration >= '".date( "Y-m-d", $fromdt ) . "' and padbexpiration < '".date( "Y-m-d", $todt ) . "') ) and aedmissing = 0 and outofservice = 0 and aedstolen = 0 and deleted = 0 and clientid in ( select id from company_esi where iscorp = 1 and deleted = 0 $dontcount )  " );    
//echo( "select count( distinct( serial ) ) from aed_esi where  ( ( padaexpiration >= '".date( "Y-m-d", $fromdt ) . "' and padaexpiration < '".date( "Y-m-d", $todt ) . "') or ( padbexpiration >= '".date( "Y-m-d", $fromdt ) . "' and padbexpiration < '".date( "Y-m-d", $todt ) . "') ) and aedmissing = 0 and outofservice = 0 and aedstolen = 0 and deleted = 0 and clientid in ( select id from company_esi where iscorp = 1 and deleted = 0 ) <br> "  );
?>
                    <td><a href="expiringaeds.php?fromdt=<?=$fromdt?>&todt=<?=$todt?>&iscorp=1"><?=$cnt?></a></td>
<?php
//echo( "select count( distinct( serial ) ) from aed_esi where ( ( pediatricpads >= '".date( "Y-m-d", $fromdt ) . "' and pediatricpads < '".date( "Y-m-d", $todt ) . "') ) and aedmissing = 0 and outofservice = 0 and aedstolen = 0 and deleted = 0 and clientid in ( select id from company_esi where iscorp = 1 and deleted = 0  ) <br> " );
$cnt = getDashCache( "fr2pedcorp3", $fromdt, "select count( distinct( serial ) ) from aed_esi where ( ( pediatricpads >= '".date( "Y-m-d", $fromdt ) . "' and pediatricpads < '".date( "Y-m-d", $todt ) . "') ) and aedmissing = 0 and outofservice = 0 and aedstolen = 0 and deleted = 0 and clientid in ( select id from company_esi where iscorp = 1 and deleted = 0 $dontcount )  " );    
?>
                    <td><a href="expiringaeds.php?fromdt=<?=$fromdt?>&todt=<?=$todt?>&type=ped&iscorp=1"><?=$cnt?></a></td>
<?php $cnt = getDashCache( "expiringcorp", $fromdt, "select count( id ) from company_esi where ( filingexpirationdate >= '".date( "Y-m-d", $fromdt ) . "' and filingexpirationdate < '".date( "Y-m-d", $todt ) . "') and deleted = 0 and iscorp = 1 $dontcount " );    
?>
                    <td><a href="expiringcompanies.php?fromdt=<?=$fromdt?>&todt=<?=$todt?>"><?=$cnt?></a></td>
<?php
$fromdt = mktime( 0,0,0, (int)date( "m" ) + $i - 10, 1, (int)date( "Y" )  );

$cnt = getDashCache( "last10corp4", $fromdt, "select count( id ) from company_esi where id not in ( select companyid from class where startdate > '".date( "Y-m-d", $fromdt ) . "' and canceldate is null ) and deleted = 0 and iscorp = 1 $dontcount and showlasttraining = 1  and emailtype like '%Training%' " );    
?>
                    <td><a href="expiringcompanies.php?type=last10corp&fromdt=<?=$fromdt?>&todt=<?=$todt?>"><?=$cnt?></a></ntd>
<?php
$fromdt = mktime( 0,0,0, (int)date( "m" ) + $i + 2, 1, (int)date( "Y" ) - 2 );
$cnt = getDashCache( "notrainedcorp604", $fromdt, "select count( id ) from company_esi where id not in ( select distinct( clientid ) from responders_esi r, responder_training_dates rtd where r.responderid = rtd.responderid and rtd.trainingdate > '".date( "Y-m-d", $fromdt ) . "' ) and deleted = 0 and iscorp = 1  and emailtype like '%Training%' $dontcount and showcardsexp = 1" );    
?>
                    <td><a href="expiringcompanies.php?type=notrainedcorp60&fromdt=<?=$fromdt?>&todt=<?=$todt?>"><?=$cnt?></a></ntd>
<?php
$fromdt = mktime( 0,0,0, (int)date( "m" ) + $i, 1, (int)date( "Y" ) - 2 );

$cnt = getDashCache( "notrainedcorp4", $fromdt, "select count( id ) from company_esi where id not in ( select distinct( clientid ) from responders_esi r, responder_training_dates rtd where r.responderid = rtd.responderid and rtd.trainingdate > '".date( "Y-m-d", $fromdt ) . "' ) and deleted = 0 and iscorp = 1  and emailtype like '%Training%' $dontcount and shownotrained = 1" );    
//echo( "select count( id ) from company_esi where id not in ( select distinct( clientid ) from responders_esi r, responder_training_dates rtd where r.responderid = rtd.responderid and rtd.trainingdate > '".date( "Y-m-d", $fromdt ) . "' ) and deleted = 0 and iscorp = 1  and emailtype like '%Training%' $dontcount" );    
?>
                    <td><a href="expiringcompanies.php?type=notrainedcorp&fromdt=<?=$fromdt?>&todt=<?=$todt?>"><?=$cnt?></a></ntd>
                </tr>
<?php } ?>
            </table>
            
            <br>
            
<?php } ?>        
<?php if( hasDash( "equipment" ) && 1 == 0 ) { ?>                      
            
            
            <div class="box-header">
                <div class="box-title">Equipment</div>
            </div><br>

            <table class="table2">
                <tr>
                    <th></th>
                    <th>Ship</th>
                    <th>Pickup</th>
                    <th>UPS</th>
                    <th>No Instructors</th>
                </tr>
                <tr>
<?php
    $fromdt = mktime( 0,0,0 );
    $todt = mktime( 0,0,0, (int)date( "m" ), (int)date( "d" ) + 1 );
?>
                    <td class="left"><b>Today:</b></td>
                    <td><a href="equipment-ship-today.shtml">11</a></td>
                    <td><a href="equipment-pickup-today.shtml">5</a></td>
                    <td><a href="equipment-ups-today.shtml">6</a></td>
                    <td><a href="no-instructors-today.shtml">3</a></td>
                </tr>
<?php
    $fromdt = mktime( 0,0,0, (int)date( "m" ), (int)date( "d" ) + 1 );
    $todt = mktime( 0,0,0, (int)date( "m" ), (int)date( "d" ) + 2 );
?>
                <tr>
                    <td class="left"><b>Tomorrow:</b></td>
                    <td><a href="">1</a></td>
                    <td><a href="">8</a></td>
                    <td><a href="">2</a></td>
                    <td><a href="">5</a></td>
                </tr>
                <tr>
                    <td class="left"><b>Next Week:</b><br>
                    (Tues. 10/25/2011 - Mon. 10/31/2011)
                    </td>
                    <td><a href="">11</a></td>
                    <td><a href="">5</a></td>
                    <td><a href="">6</a></td>
                    <td><a href="">3</a></td>
                </tr>
            </table>
            
            <?php } ?>        
 </div><div id="content-right">
<?php if( hasDash( "reports" ) ) { ?>           
            <div class="box-header">
                <div class="box-title">Key Reports</div>
            </div><br>
            
<?php $res = db_query_rows( "select * from dashboardreports where reportiscorp in ( -1, " . intval( $session_iscorp ) . " )" ); 
if (is_array($res)) {
    foreach( $res as $r ) { 
    $xls = "";
    if( !empty($r["xls"]) )
    $xls = " <a href='{$r['xls']}'>(xls)</a>";
    ?>
                <a href="<?=$r["url"]?>"><?=$r["name"]?></a><?=$xls?><br>
    <?php  }
}

if( strtolower( $session_userid ) == "sarahg@emergencyskills.com"  || strtolower( $session_userid ) == "yahaira@emergencyskills.com"  )
{
?>
<a href='requesttotrainreport.php'>Request To Train Report</a><br>
<?php 
}
if( strtolower( $session_userid ) == "sarahg@emergencyskills.com"  )
{
?>
<a href='christmascardreport.php'>Christmas Card Report</a><br>
<?php 
}
?>
<?php if( hasDash( "upcoming_attendees" ) ) { ?>
<a href="upcomingattendees.php">Upcoming Attendees By Region</a>
<?php } ?>
            
            <br><br>
<?php } ?>
<?php if( isOverallAdmin() ) { ?>
            <div class="box-header">
                <div class="box-title">Instructor Leave</div>
            </div><br>

            <table class="table2">
                <tr>
                    <th>Name</th>
                    <th>Dates</th>
                </tr>
                <tr>
<?php
           $res = db_query_rows( "select * from trainer_notavail where datediff (enddate, notavail ) > 5 and enddate > now() order by notavail" ); //
    if (is_array($res)) {
        foreach( $res as $row ) {

           ?>
        <tr>
        <td class="left"><a href='trainer_availability.php?theid=<?=$row["trainerid"]?>'><?=getUserName( $row["trainerid"] )?></a></td>
                    <td><?=$row["notavail"]?> - <?=$row["enddate"]?></td>
                </tr>
            <?php }
    } ?>
            </table>
            
            <Br><br><?php } ?>
    <?php if( hasDash( "classes" ) ) { ?>
            <div class="box-header">
                <div class="box-title">Dates</div>
            </div><br>
            
            <b>Upcoming Full Dates:</b><br><br>
<?php 
// Assuming MAXAVAIL is a constant defined in included files
$max_avail = defined('MAXAVAIL') ? MAXAVAIL : 0;
$cl = db_query_rows( "select date( startdate ) as d, count(id) as c from class Where startdate > now() and canceldate is null 
group by date( startdate ) having count( id ) > " . $max_avail . " order by startdate " );
if (is_array($cl)) {
    foreach( $cl as $c ) 
    {

        // Fix dynamic key access
        $op = !empty($opendates[$c["d"]]) ? "<font color='red'>(open)</font>" : "";
        echo( "{$c['d']} $op ({$c['c']})<br>" );
    }
}
?>
<br>
<b>Upcoming Blocked Dates:</b><br><br>
<?php 
$cl = db_query_rows( "select dt from blockeddates where dt > '".date( "Y-m-d" )."' order by dt" );
if (is_array($cl)) {
    foreach( $cl as $c ) 
    {
    echo( "{$c['dt']}<br>" );
    }
}
?>
<br> <b>Upcoming Peak Dates:</b><br><br>
<?php 
$cl = db_query_rows( "select dt from peakdates where dt > '".date( "Y-m-d" )."' order by dt" );
if (is_array($cl)) {
    foreach( $cl as $c ) 
    {
    echo( "{$c['dt']}<br>" );
    }
}
?>
<br><br>
<?php } ?>        
<?php if( hasDash( "instructors" ) ) { ?>           
            <div class="box-header">
                <div class="box-title">Instructors</div>
            </div><br>
            
The following instructors are due to be monitored this quarter:<br><br>

            <table class="table2">              
<?php 
           $currquarter = floor( ((int)date( "m" )) / 3 ) + 1;
    $thisquarter = date( "Y-m-d", strtotime( date("Y") . "-" . $currquarter . "-01" ) );
    echo( "<tr><td>Quarter $currquarter<br>" );
    $sql = "select id, first_name, last_name from user where usertype = 'trainer' and monitoringquarter = '$currquarter' and inactive = 0 and id not in ( select trainerid from monitoring where monitoringdate > '$thisquarter' ) order by last_name, first_name";
    //    $sql = "select id, first_name, last_name from user where usertype = 'trainer' and monitoringquarter = '$currquarter' and inactive = 0 order by last_name, first_name";
    //    echo( $sql );
$res = db_query_rows( $sql );
if (is_array($res)) {
    foreach( $res as $r ) { ?>
                        <a href="/trainer_view.php?tid=<?=$r["id"]?>"><?=$r["first_name"]?> <?=$r["last_name"]?></a><br>
<?php }
} ?>
</td>                   
                </tr>
            </table>
            <?php } ?>        
<?php if( hasDash( "fr2_replacements" ) ) { ?><Br>
            <div class="box-header">
                <div class="box-title">FR2 Classes</div>
            </div><br>
            <table class="table2">              
<?php 
           $cids = db_query_array("select distinct( clientid ) from aed_esi, company_esi where showsondrillreports = 1 and  aed_esi.clientid = company_esi.id and serial like '0%' and iscorp = 0 and aed_esi.deleted = 0 and aedmissing =0 and outofservice = 0 and aedstolen = 0", "clientid", "clientid");
           // Initialize if not array
           if (!is_array($cids)) $cids = [];
    $cids[] = "-1";
    $cidstr = implode( ", " , $cids );
    $upcoming = db_query_rows( "select * from class where companyid in ($cidstr) and startdate > now() and deleted = 0 and accepted = 1 order by startdate" ); 
if (is_array($upcoming)) {
    foreach( $upcoming as $r ) { ?>
    <a href="/class_detail.php?id=<?=$r["id"]?>"><font color='<?=!empty($r['tradein'])?"green":"#0066cc"?>'><?=getCompanyName( $r["companyid"] ) . ": " . getFormattedDateWTime( $r["startdate"] )?></font></a><br>
<?php }
} ?>
                    </td>
                </tr>
            </table>
            <?php } ?>
<?php include "popupjs.php" ;?>
<?php include "ssi/footer.php" ; ?>
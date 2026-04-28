<?php
require_once('mysql.php');

// Initialize variables to avoid undefined variable warnings
$del = $_GET['del'] ?? null;
$archivechecked = $_GET['archivechecked'] ?? null;
$toarchive = $_GET['toarchive'] ?? [];
$repname = $_GET['repname'] ?? null;
$dfrom = $_GET['dfrom'] ?? null;
$dto = $_GET['dto'] ?? null;
$schoolid = $_GET['schoolid'] ?? null;
$Go = $_GET['Go'] ?? true;
$viewarch = $_GET['viewarch'] ?? null;
$sortby = $_GET['sortby'] ?? null;
$groupby = $_GET['groupby'] ?? null;

// Initialize string variables
$repw = '';

if( isset($del) && $del )
{
    // Sanitize input to prevent SQL injection
    $del = intval($del);
    db_query( "DELETE FROM appuploads WHERE id = " . $del );
    db_query( "DELETE FROM appuploaddata WHERE uploadid = " . $del );
    echo( "Deleted. Please refresh the referring page."); 
    exit;
}

// $tmp = db_query_rows( "SELECT value, dateinupload FROM appuploads a, appuploaddata d WHERE uploadid = a.id AND d.name = 'servicecallid' AND type = 'sc'" );
// foreach( $tmp as $t )
// {
//     db_query( "UPDATE servicecall SET servicecalldate = '". date( "Y-m-d", strtotime( $t['dateinupload'] ) ). "' WHERE servicecallid = '".$t['value']."'" );
// }
// $tmp = db_query_rows( "SELECT value, dateinupload FROM appuploads a, appuploaddata d WHERE uploadid = a.id AND d.name = 'drillid' AND type = 'drill'" );
// foreach( $tmp as $t )
// {
//     db_query( "UPDATE drill SET drilldate = '". date( "Y-m-d", strtotime( $t['dateinupload'] ) ). "' WHERE drillid = '".$t['value']."'" );
// }
// $tmp = db_query_rows( "SELECT * FROM appuploaddata WHERE name = 'id' " );
// foreach( $tmp as $t )
// {
//     db_query( "UPDATE appuploads SET schoolid = '".$t['value']."' WHERE id = '".$t['uploadid']."' " );
// }

if( isset($archivechecked) && $archivechecked )
{
    if(is_array($toarchive)) {
        foreach( $toarchive  as $t=>$throwaway )
        {
            $t = intval($t);
            db_query( "UPDATE appuploads SET archived = 1 WHERE id = " . $t );
        }
    }
}

if( isset($repname) && $repname )
{
    // Use prepared statements or sanitize input
    $repname = addslashes($repname); // Note: Consider using prepared statements instead
    $repw = " AND uploader = '$repname'" ;
}

if( isset($dfrom) && $dfrom )
{
    $dfrom = addslashes($dfrom);
    $repw .= " AND dateinupload >= '$dfrom'" ;
}

if( isset($schoolid) && $schoolid )
{
    $schoolid = addslashes($schoolid);
    $repw .= " AND schoolid = '$schoolid'" ;
}

if( isset($dto) && $dto )
{
    $dto = addslashes($dto);
    $repw .= " AND dateinupload <= '$dto'" ;
}

if( !isset($Go) || !$Go  )
{
    $repw .= " AND dateinupload >= '" . date( "Y-m-d", mktime( 0,0,0,date("m")-1) ). "'" ;
}

$arch = "( 0 )";
if( isset($viewarch) && $viewarch )
    $arch = "( 0, 1 )";

?>
<?php include "ssi/top.php"; ?>
<!--start center content-->
<p>

<strong><span class="title">APP UPLOADS</span></strong>

<p>
    <form method='get'>
    Date Range: <?php echo printdates2( "dfrom", $dfrom ) ?> to <?php echo printdates2( "dto", $dto ) ?><br>
    Filter by Rep: <select name='repname'>
    <option value=''></option>
    <?php 
    $dist = db_query_array( "SELECT DISTINCT(uploader) FROM appuploads ORDER BY uploader", "uploader", "uploader" );
    if(is_array($dist)) {
        foreach( $dist as $d )
        {
            $sel = ($d == $repname) ? "SELECTED" : "";
            echo( "<option value='$d' $sel>$d</option>" );
        }
    }
    ?>

    </select> <br>
Order By: <select name='sortby'>
<option value=''>Date</option>
<option value='instructor' <?php echo ($sortby=="instructor") ? "SELECTED" : "" ?>>Instructor</option>
</select><br>
Group By: <select name='groupby'>
<option value='' >School Visit</option>
<option value='1' <?php echo ($groupby) ? "SELECTED" : "" ?>>Individual</option>
</select><br>
    Include Archived: <input type='checkbox' value='1' name='viewarch' <?php echo ($viewarch) ? "CHECKED" : "" ?>><br>    
<input type='submit' name='Go' value='Go'><br><br>
<input type='submit' name='archivechecked' value='Archive Checked'>
    <?php if( isset($groupby) && $groupby ) { ?>
<table cellpadding="4" cellspacing="1" border="0" width="100%" bgcolor="#999999" class="table3" >
        <tr bgcolor="#e1e1f6">
<th class='copy'>ID</th>
<th class='copy'>Upload Number</th>
<th class='copy'>Type  </th>
<th class='copy'>School </th>
<th class='copy'>Date Uploaded</th>
<th class='copy'>Uploader </th>
<th class='copy'>Date Signed        </th>
<th class='copy'>School Rep Name </th>
<th class='copy'>ESI Rep Name </th>
<th class='copy'>V? </th>
<th class='copy'>P? </th>
                              </tr>
<?php

                        $ob = ($sortby == "instructor") ? "uploader" : "dateuploaded";
                        $ob = "dateuploaded";
$trainers = db_query_rows( "SELECT * FROM appuploads WHERE archived IN $arch  $repw ORDER BY $ob" );
    
if(is_array($trainers)) {
    foreach( $trainers as $t )
    {
        // Ensure $t is an array and has the required keys
        $t_id = $t['id'] ?? '';
        $t_type = $t['type'] ?? '';
        $t_dateuploaded = $t['dateuploaded'] ?? '';
        $t_uploader = $t['uploader'] ?? '';
        $t_dateinupload = $t['dateinupload'] ?? '';
        $t_name = $t['name'] ?? '';
        $t_esi_repname = $t['esi_repname'] ?? '';
        $t_version = $t['version'] ?? '';
        $t_frompending = $t['frompending'] ?? '';
        $t_archived = $t['archived'] ?? '';
        $t_schoolname = $t['schoolname'] ?? '';

        echo("<input type=hidden name='hiddens[]' value='$t_id'>" );
        $bgcolor = "#FFFFFF";
        // if($crow["deleted"]) $bgcolor = "#FFccccc";
        // if($t["isdone"]) $bgcolor = "#F5F3B0";
        // if($t["shipped"]) $bgcolor = "#ccffcc";
        // if($t["received"]) $bgcolor = "#C29EE8";

        if( !$t_type ) continue;
        $fieldid = db_query_first_cell( "SELECT value FROM appuploaddata WHERE uploadid = '$t_id' AND name IN ( 'serviceid', 'servicecallid', 'servideid', 'drillid' ) " );
        
        if( $t_type == "sc" )
        {
            $urlname = "appservicecall.php";
            $itemurl = "<a target=_blank href='editservicecall.php?servicecallid=$fieldid'>SC #{$fieldid}</a>";
            $schoolname = getCompanyName( db_query_first_cell( "SELECT companyid FROM servicecall WHERE servicecallid = '$fieldid'" ) );
        }
        else if( $t_type == "ni" )
        {
            $urlname = "appnewinstall.php";
            $itemurl = "<a target=_blank href='editservicecall.php?servicecallid=$fieldid'>NI #{$fieldid}</a>";
            $schoolname = getCompanyName( db_query_first_cell( "SELECT companyid FROM servicecall WHERE servicecallid = '$fieldid'" ) );
        }
        else
        {
            $urlname = "appdrill.php";
            $itemurl = "<a target=_blank href='editdrill.php?drillid=$fieldid'>Drill #{$fieldid}</a>";
            $schoolname = getCompanyName( db_query_first_cell( "SELECT companyid FROM drill WHERE drillid = '$fieldid'" ) );
        }
        if( !$fieldid ) continue;

        if( !$schoolname )
            $schoolname = $t_schoolname;
        
        echo( "<tr bgcolor='$bgcolor'>" );
        echo( "<td class='copy' valign='top'><input type='checkbox' name='toarchive[{$t_id}]' ".($t_archived?"CHECKED":"") . " value=1></td> " );
        echo( "<td class='copy' valign='top'><a target=_blank href='{$urlname}?id=$t_id'>{$t_id}</a></td><td class='copy'>$itemurl</td> " );
        echo( "<td class='copy'>$schoolname</td>" );

        echo( "<td class='copy'>$t_dateuploaded</td>" );
        echo( "<td class='copy'>$t_uploader</td>" );
        echo( "<td class='copy'>$t_dateinupload</td>" );
        echo( "<td class='copy'>$t_name</td>" );
        echo( "<td class='copy'>$t_esi_repname</td>" );
        echo( "<td class='copy'>$t_version</td>" );
        echo( "<td class='copy'>$t_frompending</td>" );
        echo( "</tr>" );
    }
}
?>
</table><p>
<input type='submit' name='archivechecked' value='Archive Checked'>
             <?php } else { ?>
<table cellpadding="4" cellspacing="1" border="0" width="100%" bgcolor="#999999" class="table3" >
        <tr bgcolor="#e1e1f6">
<th class='copy'>School Name </th>
<th class='copy'>Date Signed        </th>
<th class='copy'>View Link        </th>
<th class='copy'>Visit Information        </th>
                              </tr>
<?php

            // I think we can assume they visited schools at the same time
    
        $ob = ($sortby == "instructor") ? "uploader, " : "";
$trainers = db_query_rows( "SELECT DISTINCT(schoolid), DATE(dateinupload) AS d, DATE(dateuploaded) FROM appuploads WHERE archived IN $arch   $repw  ORDER BY $ob dateuploaded, dateinupload, schoolid" );
//echo( "SELECT DISTINCT(schoolid), DATE(dateinupload) AS d, DATE(dateuploaded) FROM appuploads WHERE archived IN $arch   $repw  ORDER BY $ob dateuploaded, dateinupload, schoolid" );
    
if(is_array($trainers)) {
    foreach( $trainers as $visitrow )
    {
        $visit_schoolid = $visitrow['schoolid'] ?? '';
        $visit_d = $visitrow['d'] ?? '';
        
    //echo( "SELECT * FROM appuploads WHERE archived IN $arch  AND type = 'sc'  $repw  AND schoolid = '$visit_schoolid' AND DATE(dateinupload) = '$visit_d'<br>" );
        $scrows = db_query_rows( "SELECT * FROM appuploads WHERE archived IN $arch  AND type = 'sc'  $repw  AND schoolid = '$visit_schoolid' AND DATE(dateinupload) = '$visit_d'" );
        // if( $visit_schoolid == 13663 )
        //     echo( "SELECT * FROM appuploads WHERE archived = 0 AND type = 'drill'  $repw AND schoolid = '$visit_schoolid' AND DATE(dateinupload) = '$visit_d'<Br>" );
        $drillrows = db_query_rows( "SELECT * FROM appuploads WHERE archived IN $arch  AND type = 'drill'  $repw AND schoolid = '$visit_schoolid' AND DATE(dateinupload) = '$visit_d'" );
        $nirows = db_query_rows( "SELECT * FROM appuploads WHERE archived IN $arch AND type = 'ni'  $repw AND schoolid = '$visit_schoolid' AND DATE(dateinupload) = '$visit_d'" );

        $bgcolor = "#FFFFFF";
        // if($crow["deleted"]) $bgcolor = "#FFccccc";
        // if($t["isdone"]) $bgcolor = "#F5F3B0";
        // if($t["shipped"]) $bgcolor = "#ccffcc";
        // if($t["received"]) $bgcolor = "#C29EE8";
        $arr = array();
        foreach( array( $drillrows, $scrows, $nirows ) as $t )
        {
            if(is_array($t)) {
                foreach( $t as $trow )
                    $arr[] = $trow;
            }
        }

    // this is kind of hacky, not sure where to move this?     
        if( count( $drillrows ) )
        {
            $tmpdrillid = "";
            foreach( $drillrows as $d )
            {
                $tmpdrillid = db_query_first_cell( "SELECT value FROM appuploaddata WHERE uploadid = '".$d['id']."' AND name IN ( 'drillid' ) " );
            }
    //        echo( "in $tmpdrillid " );

            foreach( $scrows as $s )
            {
                $fd = $tmpdrillid ? 1 : 0;
                $scid = db_query_first_cell( "SELECT value FROM appuploaddata WHERE uploadid = '".$s['id']."' AND name IN ( 'serviceid', 'servicecallid', 'servideid' ) " );
                db_query( "UPDATE servicecall SET fromdrill = $fd, assocdrillid = '$tmpdrillid' WHERE servicecallid = '$scid'" );
            }
            foreach( $nirows as $s )
            {
                $fd = $tmpdrillid ? 1 : 0;
                $scid = db_query_first_cell( "SELECT value FROM appuploaddata WHERE uploadid = '".$s['id']."' AND name IN ( 'serviceid', 'servicecallid', 'servideid' ) " );
                db_query( "UPDATE servicecall SET fromdrill = $fd, assocdrillid = '$tmpdrillid' WHERE servicecallid = '$scid'" );
            }
        }
    // end    

        $schoolname = getCompanyName( $visit_schoolid );
        echo( "<tr bgcolor='$bgcolor'>" );
        echo( "<td class='copy'><a target=_blank href='viewcompany.php?id=$visit_schoolid'>$schoolname</a></td>" );
        echo( "<td class='copy'>$visit_d</td>" );
        echo( "<td class='copy'><A target=_blank href='billingworksheet.php?d=$visit_d&schoolid=".urlencode($visit_schoolid)."'>View Worksheet</a></td>" );
        echo( "<td class='copy'><table border='1' cellpadding=2 cellspacing=0>" );
        ?>
        <tr>    <th class='copy'>#</th>
        <th class='copy'>ID</th>
        <th class='copy'>Type  </th>
        <th class='copy'>Date Uploaded</th>
        <th class='copy'>Uploader </th>
        <th class='copy'>School Rep Name </th>
        <th class='copy'>ESI Rep Name </th>
        <th class='copy'>V? </th>
        <th class='copy'>P? </th>
             </tr>
        <?php
        if(is_array($arr)) {
            foreach( $arr as $t )
            {
                $t_id = $t['id'] ?? '';
                $t_type = $t['type'] ?? '';
                $t_dateuploaded = $t['dateuploaded'] ?? '';
                $t_uploader = $t['uploader'] ?? '';
                $t_name = $t['name'] ?? '';
                $t_esi_repname = $t['esi_repname'] ?? '';
                $t_version = $t['version'] ?? '';
                $t_frompending = $t['frompending'] ?? '';
                $t_archived = $t['archived'] ?? '';

                if( !$t_type ) continue;
                $fieldid = db_query_first_cell( "SELECT value FROM appuploaddata WHERE uploadid = '$t_id' AND name IN ( 'serviceid', 'servicecallid', 'servideid', 'drillid' ) " );
        //    if( !$fieldid ) continue;
                if( $t_type == "sc" )
                {
                    $urlname = "appservicecall.php";
                    $itemurl = "<a target=_blank href='editservicecall.php?servicecallid=$fieldid'>SC #{$fieldid}</a>";
                }
                else if( $t_type == "ni" )
                {
                    $urlname = "appnewinstall.php";
                    $itemurl = "<a target=_blank href='editservicecall.php?servicecallid=$fieldid'>NI #{$fieldid}</a>";
                }
                else
                {
                    $urlname = "appdrill.php";
                    $itemurl = "<a target=_blank href='editdrill.php?drillid=$fieldid'>Drill #{$fieldid}</a>";
                }
                // if( !$schoolname )
                //     $schoolname = $visitrow["schoolname"];
                echo( "<tr>" );
                echo( "<td class='copy' valign='top'><input type='checkbox'  ".($t_archived?"CHECKED":"") . " name='toarchive[{$t_id}]' value=1></td> " );
                echo( "<td class='copy' valign='top'><a href='{$urlname}?id=$t_id'>{$t_id}</a></td><td class='copy'>$itemurl</td> " );
                echo( "<td class='copy'>$t_dateuploaded</td>" );
                echo( "<td class='copy'>$t_uploader</td>" );
                echo( "<td class='copy'>$t_name</td>" );
                echo( "<td class='copy'>$t_esi_repname</td>" );
                echo( "<td class='copy'>$t_version</td>" );
                echo( "<td class='copy'>$t_frompending</td>" );
                if( isset($session_userid) && $session_userid == "sarahg@emergencyskills.com" )
                {
                    echo( "<td class='copy'><A target=_blank onClick='return confirm( \"Are you sure you want to delete this item????\" )' href='apiuploads.php?del=$t_id'>Delete</a></td>" );
                }
                echo(" </tr>" );
            }
        }
        echo( "</table>" );
        echo( "</td>" );
        echo( "</tr>" );
    }
}
?>
</table><p>
<input type='submit' name='archivechecked' value='Archive Checked'>
    <?php } ?>
                        <?php include "ssi/footer.php" ; ?>

<!--end footer-->
<?php

require_once('mysql.php');
require_once('services.php');

// --- Form Submission Logic ---
if (isset($update))
{
    // Ensure $rids is an array passed from the form
    if (isset($rids) && is_array($rids)) {
        foreach( $rids as $rid => $pm )
        {
            // Safely fetch old values (quoted keys)
            $oldpms = db_query_first_cell( "SELECT pmsid FROM responders_esi WHERE responderid = $rid" );
            $oldbc = db_query_first_cell( "SELECT buildingcode FROM responders_esi WHERE responderid = $rid" );

            if( isOverallAdmin() )
            {
                // Safely update first and last names (quoted keys and isset checks)
                $first_name = isset($fnames[$rid]) ? $fnames[$rid] : '';
                $last_name = isset($lnames[$rid]) ? $lnames[$rid] : '';

                db_query( "UPDATE responders_esi SET firstname = '$first_name' WHERE responderid = $rid" );
                db_query( "UPDATE responders_esi SET lastname = '$last_name' WHERE responderid = $rid" );
            }

            // Check if PMS ID or Building Code is being set/updated
            $building_code = isset($buildingcodes[$rid]) ? $buildingcodes[$rid] : '';

            if( $pm || $building_code )
            {
                $rrow = getResponderRow( $rid );
                $extpms = "";

                // Logic for PMS ID validation check
                if( $oldpms != $pm )
                {
                    if( $pm )
                    {
                        // Assuming validateEmployee is defined
                        $pmsidvalidated = validateEmployee( $pm, $rrow['lastname'], "editpmsid" );
                        $extpms = ", pmsidvalidated = '$pmsidvalidated', lastpmsvalidated = NOW()";
                    }
                    else
                    {
                        $extpms = ", pmsidvalidated = '0'";
                    }
                }

                // Logic for Building Code update (only for Overall Admins)
                if( isOverallAdmin() )
                {
                    $extpms .= ", buildingcode = '$building_code'";
                }

                // Execute the update
                db_query( "UPDATE responders_esi SET pmsid = '$pm' $extpms WHERE responderid = $rid" );
                
                $rrow = getResponderRow( $rid );

                // This block is disabled (1 == 0), but preserved for context.
                if( 1 == 0 ) {
                    $send_anyway = isset($sendanyway[$rid]);
                    if( $oldpms != $pm || $building_code != $oldbc || $send_anyway )
                    {
                        // Assuming updateResponder is defined
                        updateResponder( $rrow );
                    }
                }
            }
            else
            {
                // Update PMS ID even if empty, without validation logic
                db_query( "UPDATE responders_esi SET pmsid = '$pm' WHERE responderid = $rid" );
            }
        }
    }
}

// Determine the client ID
$theid = isset($id) ? $id : (isset($thisusersrow['companyid']) ? $thisusersrow['companyid'] : null);

if( !$theid )
{
    echo( "<font color='red'>no id set!</font>" );
    exit;
}

?>
<?php include "ssi/top.php"; ?>
<!--start center content-->
<p>
<strong><span class="title">MANAGE <?=getSchoolStr( "PMS IDs" )?></span></strong>
<p>
<form method='post'>
<a href='viewcompany.php?id=<?=$theid?>'>Back to <?=getSchoolStr( "School" )?></a><br>
<table cellpadding="4" cellspacing="0" border="1" width="100%" class="table3">
<tr><th>Send To LCGMS</th><th>Name</th><?php if( isOverallAdmin() ){ ?><th>View Log</th><th>Building</th><?php } ?><th><?=getSchoolStr( "PMS ID" )?></th><th>TR ID</th><th>Expiration Date</th><th>Upcoming Training Date</th><th><?=getSchoolStr( "PMS ID" )?> Validated?</th></tr>
<?php
// Fetch responder data (quoted keys)
$responder_rows = db_query_rows("SELECT buildingcode, pmsidvalidated, pmsid, responderid, firstname, lastname, title FROM responders_esi WHERE clientid='$theid' AND deleted=0 ORDER BY lastname");

foreach( $responder_rows as $rrow )
{
    // Quoted keys for responderid
    $responder_id = $rrow['responderid'];

    $other = "";
    // Assuming getResponderExpDate is defined and returns a date string or null
    $exp = getResponderExpDate( $responder_id );
    
    if( $exp )
    {
        $exp_timestamp = strtotime( $exp );
        // Calculate new expiration date (2 years later)
        $newexp_timestamp = mktime( 0, 0, 0, date( "m", $exp_timestamp ), date( "d", $exp_timestamp ), date( "Y", $exp_timestamp ) + 2 );
        $exp_display = date( "m/d/Y", $newexp_timestamp );
    }
    else
    {
        $exp_display = "&nbsp;";
    }
    
    // Find upcoming class
    $sql = "SELECT class.id, startdate, accepted FROM class, responder_to_class WHERE responderid = $responder_id AND classid = class.id AND startdate > NOW() AND deleted = 0 ORDER BY startdate LIMIT 1";
    $classdata = db_query_first($sql);
    
    $upcoming_display = "&nbsp;";

    // Quoted keys
    if( isset($classdata['startdate']) )
    {
        $upcoming_display = date( "m/d/Y", strtotime( $classdata['startdate'] ) );
        if( !isset($classdata['accepted']) || !$classdata['accepted'] )
            $upcoming_display .= " - Pending";
    }

    $other .= "<td>$exp_display</td><td>$upcoming_display</td>";
    
    // Quoted keys
    $pms_validated = $rrow['pmsidvalidated'] ? "Yes" : "<font color='red'>No</font>";

    // Quoted keys
    if( !$rrow['pmsidvalidated'] && !$rrow['buildingcode'] )
    {
        $pms_validated .= " (No BC)";
    }
    
    $bselect = "";
    if( isOverallAdmin() ){
        // Quoted keys
        $bselect.= "<td><a href='viewpmslog.php?id=$responder_id'>View Log</a></td>";
        // Assuming getBuildingPulldown is defined and accepts quoted array keys in name attribute
        $bselect.= "<td>".getBuildingPulldown( $theid, $rrow['buildingcode'], "buildingcodes[$responder_id]", 'style="max-width:190px; font-size: 10px;  font-family: verdana;"', 1 )."</td>";
    }
    
    $pmsvalidated = "<td>$pms_validated</td>";
    
    echo( "<tr>" );
    // Quoted keys
    echo( "<td><input type='checkbox' value='1' name='sendanyway[$responder_id]'></td>" );
    
    if( isOverallAdmin() )
    {
        // Quoted keys
        echo( "<td><input type='text' size='10' name='fnames[$responder_id]' value=\"{$rrow['firstname']}\"> <input type='text' size='10' name='lnames[$responder_id]' value=\"{$rrow['lastname']}\"></td>" );
    }
    else
    {
        // Quoted keys
        echo( "<td>{$rrow['firstname']} {$rrow['lastname']}</td>" );
    }
    // Quoted keys
    echo( "$bselect<td><input type='text' size='10' name='rids[$responder_id]' value='{$rrow['pmsid']}'></td><td>$responder_id</td>$other$pmsvalidated</tr>" );
}
?>
</table>
<input type='submit' name='update' value='Update'>
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
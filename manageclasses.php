<?php

require_once('mysql.php');
//require_once "xpo/api.php";
require_once "birdie/api.php";
if( getcurrentusertype() != 'principal' )
{
Header( "location: login.php" );
        exit;
}

function db_escape($string) {
    if (is_array($string)) {
        return $string; // Do not escape arrays
    }
    // Basic placeholder for security demonstration
    return str_replace(['\\', "'"], ['\\\\', "\'"], (string)$string);
}

if( isset($includeothers) && $includeothers )
{
    if( !isset($otherdatefrom) || !$otherdatefrom ) $otherdatefrom = $datefrom ?? '';
    if( !isset($otherdateto) || !$otherdateto ) $otherdateto = $otherdatefrom;
    $companies = db_query_array( "select companyid from class where id in ( " . implode( ", ", $ids ?? [] ) . " )", "companyid", "companyid" );
    $classids = db_query_array( "select id from class where startdate >= '$otherdatefrom' and startdate <= '$otherdateto 23:59:59' and companyid in ( ". implode( ", ", $companies ?? [] ) . " )", "id", "id" );

    if(isset($classids) && is_array($classids)) {
        foreach( $classids as $c )
        {
            $ids[] = $c;
        }
    }

    $locations = db_query_array( "select training_location from class where id in ( " . implode( ", ", $ids ?? [] ) . " ) and training_location > ''", "training_location", "training_location" );

    if(isset($locations) && is_array($locations)) {
        foreach( $locations as $l )
        {
            $l = mysql_escape_string( $l );
            $classids = db_query_array( "select id from class where startdate >= '$datefrom' and training_location = '{$l}'", "id", "id" );
            if(isset($classids) && is_array($classids)) {
                foreach( $classids as $c )
                {
                    $ids[] = $c;
                }
            }
        }
    }
    
}


if( isset($printshipping) && $printshipping )
{
include "printshippinglabels.php";
exit;
}
if( isset($printshippingpdf) && $printshippingpdf )
{
include "printshippinglabels_pdf.php";
exit;
}
if( isset($printpaperworklabels) && $printpaperworklabels )
{
include "paperworklabels_pdf.php";
exit;
}


$avfields = array();
                         $avfields["available_tvdvd"] ="TV with DV Player";
                         $avfields["available_tvvcr"] = "TV ONLY";
                         $avfields["available_powerpoint"] = "Power Point (for ALIVE! First Aid only)";
                         $avfields["available_computer"] = "Computer (or DVD Player) with Projector";
                         $avfields["available_smartboard"] = "Smartboard";
                         $avfields["noavavailable"] = "No A/V Available";

if( isset($searchstring) && $searchstring )
    $extsearch = " and ( company_esi.zip = '" . $searchstring. "'  or companyname like '%{$searchstring}%' ) ";
if( isset($searchclassid) && $searchclassid )
    $extsearch .= " and ( class.id = '$searchclassid' ) ";


// if( isset($updatedate) && $updatedate )
// {
// //    print_r( $_POST );
// //    exit;
//     $ids = array();
    
//     if( isset($rownums) && count( $rownums ) )
//     {
//         foreach( $rownums as $r=>$expname )
//         {
//             $expname = explode( "_", $expname );
//             $name = $expname[0];
//             $id = $expname[1];
//             $oldvals = getClassInfo( $id );
//             $ids[$id] = $id;
//             $val = $oldvals[$name]["value"] ?? '';
//             if( isset($shippingvals[$r]) && $val != stripslashes( $shippingvals[$r] ) )
//             {
//                 db_query( "update class_info set deleted = 1 where classid = $id and name = '$name'" );
//                 db_query( "insert into class_info ( addedby, addedbytime, classid, name, value ) values ( '$session_userid', now(), '$id', '$name', '" . $shippingvals[$r]. "' ) " );
//                 if( $name == "Bagset" ) db_query( "update class set bagset = '" . $shippingvals[$r]. "' where id = $id" );
//             }
//         }
//     }

//     if(isset($jumpid) && is_array($jumpid)) {
//         foreach( $jumpid as $jumpfromclass=>$jumptoclass )
//         {
//             if( $jumptoclass )
//             {
//                 $name = "Pick Up Date";
//                 db_query( "update class_info set deleted = 1 where classid = $jumptoclass and name = '$name'" );
//                 db_query( "insert into class_info ( addedby, addedbytime, classid, name, value ) values ( '$session_userid', now(), '$jumptoclass', '$name', 'jumping' ) " );
//                 db_query( "insert into class_info ( addedby, addedbytime, classid, name, value ) values ( '$session_userid', now(), '$jumptoclass', 'Pick Up Date', 'jumping' ) " );
//                 db_query( "insert into class_info ( addedby, addedbytime, classid, name, value ) values ( '$session_userid', now(), '$jumptoclass', 'jumpingfrom', '$jumpfromclass' ) " );
//             }
//             else
//             {
//                 db_query( "update class_info set deleted = 1 where value = $jumpfromclass and name = 'jumpingfrom'" );
//             }
//         }
//     }

//     // if( $updatedate == "Update And Send To XPO" )
//     // {
//     //     sendClassesToXPO( $ids, "both" );    
//     // }
//     if( $updatedate == "Update And Send To Birdie" )
//     {
//         sendClassesToBirdie( $ids, "both" );    
//     }
//     if( $updatedate == "Update And Go To Shipping Export" )
//     {
//         Header( "Location: upsclicked.php" );
//         exit;
//     }
//     //    exit;
// }

if( isset($updatedate) && $updatedate )
{
    $ids = array();
    
    // Process regular shipping fields from rownums
    if( isset($rownums) && count( $rownums ) )
    {
        foreach( $rownums as $r=>$expname )
        {
            $expname = explode( "_", $expname );
            $name = $expname[0];
            $id = $expname[1];
            $oldvals = getClassInfo( $id );
            $ids[$id] = $id;
            $val = $oldvals[$name]["value"] ?? '';
            
            // Check if this field exists in shippingvals
            $new_val = isset($shippingvals[$r]) ? stripslashes($shippingvals[$r]) : '';
            
            if( $val != $new_val )
            {
                db_query( "update class_info set deleted = 1 where classid = $id and name = '$name'" );
                db_query( "insert into class_info ( addedby, addedbytime, classid, name, value ) values ( '$session_userid', now(), '$id', '$name', '" . db_escape($new_val) . "' ) " );
                if( $name == "Bagset" ) db_query( "update class set bagset = '" . db_escape($new_val) . "' where id = $id" );
            }
        }
    }
    
    // Process Admiral and Birdie flags specifically
    if( isset($admiral_sent) && is_array($admiral_sent) )
    {
        foreach( $admiral_sent as $class_id => $value )
        {
            $ids[$class_id] = $class_id;
            $name = "Sent With Admiral";
            
            // Get current value
            $old_info = db_query_first("SELECT * FROM class_info WHERE classid = $class_id AND name = '$name' AND deleted = 0");
            $old_val = $old_info['value'] ?? '';
            
            if( $old_val != $value )
            {
                db_query( "UPDATE class_info SET deleted = 1 WHERE classid = $class_id AND name = '$name'" );
                if( !empty($value) )
                {
                    db_query( "INSERT INTO class_info (addedby, addedbytime, classid, name, value) VALUES ('$session_userid', NOW(), '$class_id', '$name', '" . db_escape($value) . "')" );
                }
            }
        }
    }
    
    if( isset($birdie_sent) && is_array($birdie_sent) )
    {
        foreach( $birdie_sent as $class_id => $value )
        {
            $ids[$class_id] = $class_id;
            $name = "Sent With Birdie";
            
            // Get current value
            $old_info = db_query_first("SELECT * FROM class_info WHERE classid = $class_id AND name = '$name' AND deleted = 0");
            $old_val = $old_info['value'] ?? '';
            
            if( $old_val != $value )
            {
                db_query( "UPDATE class_info SET deleted = 1 WHERE classid = $class_id AND name = '$name'" );
                if( !empty($value) )
                {
                    db_query( "INSERT INTO class_info (addedby, addedbytime, classid, name, value) VALUES ('$session_userid', NOW(), '$class_id', '$name', '" . db_escape($value) . "')" );
                }
            }
        }
    }
    
    // Process jump IDs
    if(isset($jumpid) && is_array($jumpid)) {
        foreach( $jumpid as $jumpfromclass=>$jumptoclass )
        {
            if( $jumptoclass )
            {
                $name = "Pick Up Date";
                db_query( "update class_info set deleted = 1 where classid = $jumptoclass and name = '$name'" );
                db_query( "insert into class_info ( addedby, addedbytime, classid, name, value ) values ( '$session_userid', now(), '$jumptoclass', '$name', 'jumping' ) " );
                db_query( "insert into class_info ( addedby, addedbytime, classid, name, value ) values ( '$session_userid', now(), '$jumptoclass', 'Pick Up Date', 'jumping' ) " );
                db_query( "insert into class_info ( addedby, addedbytime, classid, name, value ) values ( '$session_userid', now(), '$jumptoclass', 'jumpingfrom', '$jumpfromclass' ) " );
            }
            else
            {
                db_query( "update class_info set deleted = 1 where value = $jumpfromclass and name = 'jumpingfrom'" );
            }
        }
    }

    if( $updatedate == "Update And Send To Birdie" )
    {
        sendClassesToBirdie( $ids, "both" );    
    }
    if( $updatedate == "Update And Go To Shipping Export" )
    {
        Header( "Location: upsclicked.php" );
        exit;
    }

    $redirect = "manageclasses.php?datefrom=" . urlencode($datefrom ?? '') 
              . "&dateto=" . urlencode($dateto ?? '') 
              . "&searchclassid=" . urlencode($searchclassid ?? '')
              . "&searchstring=" . urlencode($searchstring ?? '')
              . "&managedates=1"
              . "&ids[]=" . implode("&ids[]=", array_keys($ids ?? []));
    Header("Location: " . $redirect);
    exit;
}

if( isset($updateav) && $updateav )
{
    if(isset($ids) && is_array($ids)) {
        foreach( $ids as $i )
        {
            foreach( $avfields as $a=>$throwaway )
            {
                db_query( "update class set $a = '" . ($_POST[$a][$i] ?? '') . "' where id = $i" );
            }
            foreach( array( "avequip", "equipnumber", "otherequip" )  as $a )
            {
                db_query( "update class set $a = '" . mysql_escape_string( $_POST[$a][$i] ?? '' ) . "' where id = $i" );
            }
            
        }
    }

    $redirect = "manageclasses.php?datefrom=" . urlencode($datefrom ?? '')
              . "&dateto=" . urlencode($dateto ?? '')
              . "&searchclassid=" . urlencode($searchclassid ?? '')
              . "&searchstring=" . urlencode($searchstring ?? '')
              . "&manageav=1"
              . "&ids[]=" . implode("&ids[]=", array_keys($ids ?? []));
    Header("Location: " . $redirect);
    exit;
}


if( isset($printrosters) && $printrosters )
{

    $calcnames = false;
    if(isset($ids) && is_array($ids)) {
        foreach( $ids as $id )
        {
            $names = 1;
            include "roster_print.php";
            $nostyles = 1;
        }
    }
    exit;

}



$contentextrastyle = "width: auto";


?>
<?php include "ssi/top.php"; ?>
<!--start center content-->



<strong><span class="title">Class Management</span></strong>
    <?php echo $err ?? ''; ?>
<form method='get'  action="manageclasses.php">
<table class="table3" cellpadding="3" cellspacing="0" border="0">
            <tr>
                                      <td valign="middle"><span class="copy">Date From</td>
<td valign="middle"><span class="copy"><?php echo printdates2( "datefrom", $datefrom ?? '' ); ?></td>
<td valign="middle"><span class="copy">to</span></td>
<td valign="middle"><span class="copy"><?php echo printdates2( "dateto", $dateto ?? '' ); ?></td>
    <td valign="middle"><span class="copy">Class ID: <input type='text' size='6' name='searchclassid' value='<?php echo $searchclassid ?? ''; ?>'></td>
<td valign="middle"><input type='submit' name='go' value='Go'></td>
                </tr>
    <tr><td valign="middle" colspan='20'><span class="copy"><?php echo getSchoolStr("School"); ?> Name Or Zip: <input type='text' size='10' name='searchstring' value='<?php echo $searchstring ?? ''; ?>'>
    Include UPS/Conference? <input type='checkbox' name='includeups' value='1' <?php echo isset($includeups) && $includeups?"CHECKED":""; ?>>
    Show Only Pending: <input type='checkbox' name='onlypending' value='1' <?php echo isset($onlypending) && $onlypending?"CHECKED":""; ?>
    </td></td>
</form>
            </table>
<br><br>

    <?php if( isset($manageav) && $manageav ) { ?>
<form method='post' name="seform" id="seform" action="manageclasses.php">
                         <?php
                         if(isset($ids) && is_array($ids)) {
                             foreach( $ids as $i ) { 
                         ?>
                         <input type='hidden' name='ids[]' value='<?php echo $i; ?>'>
                         <?php } 
                         }?>
                         <input type='hidden' name='datefrom' value='<?php echo $datefrom ?? ''; ?>'>
                         <input type='hidden' name='searchstring' value="<?php echo $searchstring ?? ''; ?>">
                         <input type='hidden' name='searchclassid' value="<?php echo $searchclassid ?? ''; ?>">
                         <input type='hidden' name='dateto' value='<?php echo $dateto ?? ''; ?>'>
<input type='hidden' name='manageav' value='1'>
<strong><span class="title">Manage AV Equipment</span></strong>
<table class="table3" width='100%' border=1 cellpadding=2 cellspacing=0>
<tr><td class="copy"><b>Field</td>
<?php
        $ids[] = -1;
        $rownums = 0;
    $sql = "SELECT class.*, companyname FROM class, company_esi WHERE companyid = company_esi.id and accepted = 1 and class.deleted = 0  and class.id in ( " . implode( ", ", $ids ?? [] ) . " ) order by startdate  ";
$classes = db_query_rows($sql);
if(isset($classes) && is_array($classes)) {
    foreach( $classes as $c )
    {
        echo( "<td $leftstr><a href='class_detail.php?id=$c[id]'>".getFormattedDateWTime( $c["startdate"] ?? '' )."</a>" );
        echo( "<br><a href='viewcompany.php?id=$c[companyid]'>$c[companyname]</a></td>" );
    }
}
        ?>

        </tr>
<?php
        

if(isset($avfields) && is_array($avfields)) {
    foreach( $avfields as $val=>$name )
    {


        echo( "<Tr><td>$name</td>" );
        
        if(isset($classes) && is_array($classes)) {
            foreach( $classes as $c )
            {
                echo( "<td><input type='checkbox' name='{$val}[{$c[id]}]' value='1' " . (isset($c[$val]) && $c[$val]?"CHECKED":"" ) . "></td>" );
            }
        }
        echo( "</tr>" );
        
    }
}

echo( "<tr><td colspan='100'>&nbsp;</td></tr>" );
    echo( "<Tr><td>Equipment Number</td>" );

$val = "equipnumber";
    if(isset($classes) && is_array($classes)) {
        foreach( $classes as $c )
        {
            echo( "<td><input type='text' name='{$val}[{$c[id]}]' value='" . ($c[$val] ?? '') . "'></td>" );
        }
    }
    echo( "</tr>" );

$drop_rows = db_query_rows("select value from esioptionvalues where datatype='avequip' order by value");

    echo( "<Tr><td>AV Equipment Number</td>" );
    $val = "avequip";
    if(isset($classes) && is_array($classes)) {
        foreach( $classes as $c )
        {
            echo( "<td><select name='{$val}[{$c[id]}]'><option value=''></option>" );
            if(isset($drop_rows) && is_array($drop_rows)) {
                foreach ($drop_rows as $d) {
                    $sel = isset($c["avequip"]) && $c["avequip"] == $d['value'] ? "SELECTED" : "";
                    echo( "<option $sel value={$d['value']}>{$d['value']}</option>" );
                }
            }
            echo( "</select></td>" );
        }
    }
    echo( "</tr>" );

    echo( "<Tr><td>Other Equipment</td>" );
    
    if(isset($classes) && is_array($classes)) {
        foreach( $classes as $c )
        {
            echo( "<td><input type='text' name='otherequip[{$c[id]}]' value=\"" . (isset($c["otherequip"]) && $c["otherequip"] ? "CHECKED" : "" ) . "\"></td>" );
        }
    }
    echo( "</tr>" );
?>
</table>
    <input type='submit' name='updateav' value='Update'>
    </form>

   <?php } else if( isset($managedates) && $managedates ) { ?>
<form method='post' name="seform" id="seform" action="manageclasses.php">

<strong><span class="title">Update Shipping Info</span></strong>
<table class="table3" width='100%' border=1 cellpadding=2 cellspacing=0>
<tr><td class="copy"><b>Field</td>
<?php
        $ids[] = -1;
        $rownums = 0;
    $sql = "SELECT class.*, companyname, schoolcode FROM class, company_esi WHERE companyid = company_esi.id and accepted = 1 and class.deleted = 0  and class.id in ( " . implode( ", ", $ids ?? [] ) . " ) order by startdate  ";
$classes = db_query_rows($sql);


$lastdate = "";
if(isset($classes) && is_array($classes)) {
    foreach( $classes as $c )
    {
        if( isset($c["isups"]) && $c["isups"] ) continue;
        if( isset($c["isconferenceroom"]) && $c["isconferenceroom"] && isset($c["id"]) && $c["id"] != 20509 ) continue;
        $classinfo = getClassInfo( $c["id"] ?? '' );
        $myd = date( "Y-m-d", strtotime( $c["startdate"] ?? '' ) );
        $leftstr = "";
        if( $myd != $lastdate )
        {
            $leftstr = "style='border-left-width: 3px'";
        }
        $lastdate = $myd;
        echo( "<td $leftstr valign='top'><a href='class_detail.php?id=$c[id]'>".getFormattedDateWTime( $c["startdate"] ?? '' )."</a>" );
        echo( "<br><a href='viewcompany.php?id=$c[companyid]'>$c[companyname]</a>" );
        echo( "<br> Outgoing Birdie ID: <span id='outgoing_{$c[id]}'>$c[birdieid] (". getFormattedDateWTime( $c["birdiedatesent"] ?? '' ).")</span>" ); 
        if( isset($c["birdieid"]) && $c["birdieid"] ) { echo( " <a onclick='return cancelThisBirdie( $c[id], \"outgoing\", this )' >Cancel</a>" ); }
        echo( "<br> Incoming Birdie ID: <span id='incoming_{$c[id]}'>$c[returnbirdieid] (" . getFormattedDateWTime( $c["returnbirdiedatesent"] ?? '' ) . ")</span>" );    
        if( isset($c["returnbirdieid"]) && $c["returnbirdieid"] ) { echo( " <a onclick='return cancelThisBirdie( $c[id], \"incoming\", this )' >Cancel</a>" ); }
        echo( "<br> <A href='viewbirdielog.php?classid=$c[id]'>View BIRDIE Log</a>" );
        echo( "<br> $c[schoolcode]" );

        if( isOverallAdmin() ) { 
            $str = ( "<br>Other Classes: " );
            $df = isset($datefrom) && $datefrom ? $datefrom : ($c['startdate'] ?? '');
            
            $update = db_query_rows( "select * from class where deleted = 0 and startdate >= '$df' and companyid = $c[companyid] and id <> $c[id]" );
            $any = false;
            if(isset($update) && is_array($update)) {
                foreach( $update as $urow ) { 
                    if( $any ) echo( ", " );
                    $any = true;
                    $surrounds = false;
                    $otherclassinfo = getClassInfo( $urow["id"] );
        //            print_r( $otherclassinfo );
        //            echo( $otherclassinfo["Pick Up Date"]["value"] );
                    if( isset($otherclassinfo["Pick Up Date"]["value"]) && strtotime( $otherclassinfo["Pick Up Date"]["value"] ) < strtotime( $c["startdate"] ?? '' ) &&
                        strtotime( $otherclassinfo["Return Pick Up Date"]["value"] ) > strtotime( $c["startdate"] ?? '' ) ) 
                        $surrounds = true;

                    $fnt = "";
                    if( $surrounds )
                        $fnt = "<font color='green'>";


                    $str .= ( "<span class='highlight'><a target=_blank href='class_detail.php?id=$urow[id]'>{$fnt}$urow[id] - " . getFormattedDateWTime( $urow["startdate"] ?? '' ) . "</font></a></span><Br>" );

                }
            }
            if( $any ) echo $str; 
        }
        
        echo( "</td>" );
    }
}
        ?>

        </tr>
<?php
        
              list( $overallshippingfields, $sizes, $shippingcomments ) = getShippingFieldsForEdit( array() );

if(isset($overallshippingfields) && is_array($overallshippingfields)) {
    foreach( $overallshippingfields as $name=>$ignore )
    {
        $leftstr =     "";
        $lastdate = "";
        $values = array();
        // if( $name == "Service Level" || $name == "Return Service Lev" )
        // {
        //     $values = getXPOShippingLevels();
        // }
        if( $name == "Bagset" )
        {
            $values = getBagsetValues( $crow ?? array() );
        }
        if( $name == "Order Type" )
        {
            $values = getBirdieOrderTypeValues( $crow ?? array() );
        }

        $shippingcomment = isset($shippingcomments[$name]) ? "<br><i>". $shippingcomments[$name] . "</i>" : "";

        echo( "<Tr><td>$name</td>" );
        
        if(isset($classes) && is_array($classes)) {
            foreach( $classes as $c )
            {
                if( isset($c["isups"]) && $c["isups"] ) continue;
                if( isset($c["isconferenceroom"]) && $c["isconferenceroom"] && isset($c["id"]) && $c["id"] != 20509 ) continue;

                $myd = date( "Y-m-d", strtotime( $c["startdate"] ?? '' ) );
                $leftstr = "";
                if( $myd != $lastdate )
                {
                    $leftstr = "style='border-left-width: 3px'";
                }
                $lastdate = $myd;
                
                list( $shippingfields, $sizes ) = getShippingFieldsForEdit( $c );
                $default = $shippingfields[$name] ?? '';
                $classinfo = getClassInfo( $c["id"] ?? '' );
                $classid = $c["id"] ?? '';
                outputShippingRow( $classinfo, $sizes, $rownum, $name, $default, $values, true, "_". $c["id"] );
                $rownum++;
            }
        }
        echo( "</tr>" );
        
    }
}
?>
</table>
                         <?php
                         if(isset($ids) && is_array($ids)) {
                             foreach( $ids as $i ) { 
                         ?>
                         <input type='hidden' name='ids[]' value='<?php echo $i; ?>'>
                         <?php } 
                         }?>
                         <input type='hidden' name='searchclassid' value="<?php echo $searchclassid ?? ''; ?>">
                         <input type='hidden' name='searchstring' value="<?php echo $searchstring ?? ''; ?>">
                         <input type='hidden' name='datefrom' value='<?php echo $datefrom ?? ''; ?>'>
                         <input type='hidden' name='dateto' value='<?php echo $dateto ?? ''; ?>'>
<input type='hidden' name='managedates' value='1'>
    <input type='submit' name='updatedate' value='Update'>
    <input type='submit' name='updatedate' value='Update And Send To Birdie'>
    <input type='submit' name='updatedate' value='Update And Go To Shipping Export'>
    </form>
    
    
    <?php } else if( isset($datefrom) && $datefrom || isset($searchclassid) && $searchclassid ) {

    if( !isset($dateto) || !$dateto ) $dateto = $datefrom ?? '';
    ?>
        Click to View Only this type: <br>
            <?php if( isset($includeups) && $includeups ) { ?>
            <font onClick="javascript: checkColor( '#ffff99' )" style="padding:3px; background-color:#ffff99">Conference Room</font> |
                <?php } ?>
     <font onClick="javascript: checkColor( '#d3ccff' )"  style="padding:3px; background-color:#d3ccff">DOE Sent To Birdie</font> |
     <font onClick="javascript: checkColor( '#d1e2ff' )"  style="padding:3px; background-color:#d1e2ff">DOE</font> |
     <font onClick="javascript: checkColor( '#ffeecc' )" style="padding:3px; background-color:#ffeecc">Corp Sent To Birdie</font> |
     <font onClick="javascript: checkColor( '#ccffcc' )" style="padding:3px; background-color:#ccffcc">Corp</font> |
<!--     <font onClick="javascript: checkColor( '#ffd1e4' )" style="padding:3px; background-color:#ffd1e4">No Shipping Data Set</font>-->

     <br> 
<form method='post' name="seform" id="seform"  onSubmit="return checkAnyClasses( 'seform' )"   action="manageclasses.php">
                         <input type='hidden' name='searchclassid' value="<?php echo $searchclassid ?? ''; ?>">
                         <input type='hidden' name='searchstring' value="<?php echo $searchstring ?? ''; ?>">
                         <input type='hidden' name='datefrom' value='<?php echo $datefrom ?? ''; ?>'>
                         <input type='hidden' name='dateto' value='<?php echo $dateto ?? ''; ?>'>

<a onclick="javascript:checkAll('seform', true);" href="javascript:void();">Check All Unsent</a>
    <a onclick="javascript:checkAll('seform', false);" href="javascript:void();">Uncheck All Unsent</a><br>
<strong><span class="title">Select Classes</span></strong>
<table class="table3" width='100%' border=1 cellpadding=2 cellspacing=0>
<tr><td>#</td><td class="copy"><b>ID</b></td><td class="copy"><b>Class Date</b></td><td><b>Class Location</b></td><td><b>Code</b></td>
          <?php if( isset($includeups) && $includeups ) { ?>
          <td><b>UPS?</b></td><td><b>Conference Room?</b></td>
                                 <?php } ?>
          <td><b>Equipment Notes</b></td><td><b># Masks Ordered</b></td><td><b>Notes</b></td><td><b>AED Model(s)</b></td>
                                 <td><b>Shipping Info</b></td></tr>
<?php
     $cnt = 1;

     $acceptedstate =  isset($onlypending) && $onlypending ? 0 : 1;

    if( !isset($datefrom) || !$datefrom && isset($searchclassid) && $searchclassid )
        $sql = "SELECT class.*, companyname, schoolcode, iscorp FROM class, company_esi WHERE code <> 'Inspections' and companyid = company_esi.id and accepted = $acceptedstate and class.deleted = 0 $extsearch order by startdate  ";
    else
        $sql = "SELECT class.*, companyname, schoolcode, iscorp FROM class, company_esi WHERE code <> 'Inspections' and companyid = company_esi.id and accepted = $acceptedstate and startdate > '$datefrom' and startdate <= '$dateto 23:59:59' and class.deleted = 0 $extsearch order by startdate  ";
$classes = db_query_rows($sql);
$tmpclasses = array();

if(isset($classes) && is_array($classes)) {
    foreach( $classes as $c )
    {
        if( !isset($includeups) || !$includeups )
        {
            if( isset($c["isups"]) && $c["isups"] || isset($c["isconferenceroom"]) && $c["isconferenceroom"] ) continue;
        }
        
        $col = isset($c["iscorp"]) && $c["iscorp"] ? "#ccffcc" : "#d1e2ff";
        if( isset($c["isconferenceroom"]) && $c["isconferenceroom"] ) $col = "#ffff99";
        $classinfo= getClassInfo( $c["id"] ?? '' );
        // if( !$c[isconferenceroom] && !$c["isups"] && count( $classinfo )  < 5 )
        //     $col = "#ffd1e4";

        if( isset($c["birdieid"]) && $c["birdieid"] || isset($c["returnbirdieid"]) && $c["returnbirdieid"] )
        {
            if( isset($c["iscorp"]) && $c["iscorp"] )
                $col = "#ffeecc";
            else
                $col = "#d3ccff";
        }
        $colkey = 0;
        if( $col == "#d1e2ff" ) $colkey = 1; // doe
        if( $col == "#ccffcc" ) $colkey = 2; // corp
        if( $col == "#d3ccff" ) $colkey = 3; // doe sent
        if( $col == "#ffeecc" ) $colkey = 4; // corp sent
        if( $col == "#ffff99" ) $colkey = 5; // conference room
        
        $key = $colkey . "-" . ($c["startdate"] ?? '') . "-". ($c["id"] ?? '');
        $tmpclasses[$key] = array( $c, $classinfo, $col );
    }
}

ksort( $tmpclasses );

foreach( $tmpclasses as $carr )
{
    $c = $carr[0];
    $classinfo = $carr[1];
    $col = $carr[2];
    $sentto = (isset($c["isconferenceroom"]) && $c["isconferenceroom"]) || (isset($c["birdieid"]) && $c["birdieid"]) || (isset($c["returnbirdieid"]) && $c["returnbirdieid"]) ? "yes" : "no";
    echo( "\n<tr id='inp_{$c[id]}-tr' bgcolor='$col'><td>$cnt</td><td><input id='inp_{$c[id]}' data-color='$col' data-sent='$sentto' type='checkbox' name='ids[]' value='$c[id]'></td><td><a href='class_detail.php?id=$c[id]'><nobr>".getFormattedDateWTime( $c['startdate'] ?? '' )."</nobr></a><br>
<a href='viewbirdielog.php?classid=$c[id]' target=_blank>BIRDIE Log</a></td>\n" );
    echo( "<td><a href='viewcompany.php?id=$c[companyid]'>$c[companyname]</a></td>" );
    echo( "<td>" . ($c["schoolcode"] ?? '') ."</td>" );
    if( isset($includeups) && $includeups ) { 
        echo( "<td>" . (isset($c["isups"]) && $c["isups"] ? "Yes" : "No" ) ."</td>" );
        echo( "<td>" . (isset($c["isconferenceroom"]) && $c["isconferenceroom"] ? "Yes" : "No" ) ."</td>" );
    }
    echo( "<td>" . ($c["equipnotes"] ?? '') ."</td>" );
    echo( "<td>" . ($c["nummasks"] ?? '') ."</td>" );
    echo( "<td>" . ($c["confirmationnotes"] ?? '') ."</td>" );

    if( isset($c["iscorp"]) && $c["iscorp"] )
{
    $res = db_query_first_cell( "select group_concat( distinct( model ) ) from aed_esi where clientid = '$c[companyid]' and deleted = 0 " );
    echo( "<td>$res</td>" );
}
    else
    echo( "<td>&nbsp;</td>" );


$jumpedto = getJumpingTo( $c["id"] ?? '' );
    $jumpedfrom = getJumpingFrom( $c["id"] ?? '' );
    //    $notsending = getNotSendingToXPO( $c["id"] );
    $sendingtoadmiral = getSendingToAdmiral( $c["id"] ?? '' );
    $sendingtobirdie = getSendingToBirdie( $c["id"] ?? '' );
    echo( "<td>" );
    if( isset($c["birdieid"]) && $c["birdieid"] )
        echo( "Outgoing: $c[birdieid]<Br>" );
    else if( isset($c["birdieerror"]) && $c["birdieerror"] )
    {
        echo( "<font color='red'>Outgoing</font>: $c[birdieerror]<Br>" );
    }
    if( isset($c["returnbirdieid"]) && $c["returnbirdieid"] )
        echo( ($jumpedto?"To Next School:":"Incoming to ESI" ). ": $c[returnbirdieid]<Br>" );
    else if( isset($c["returnbirdieerror"]) && $c["returnbirdieerror"] )
    {
        echo( "<font color='red'>Incoming</font>: $c[returnbirdieerror]<Br>" );
    }
    $dec = isset($c["birdieresponse"]) ? json_decode( $c["birdieresponse"] ) : null;
    if( isset($dec->status) && $dec->status == "error" )
        echo( "<font color='red'>Last BIRDIE Message: {$dec->error}</font>" );
        
    if( $jumpedto )
        echo( "<a href='class_detail.php?id=$jumpedto'>Jumping To: #$jumpedto</a><Br>" );
    if( $sendingtoadmiral )
        echo( "Sent With Admiral<Br>" );
    if( $sendingtobirdie )
        echo( "Sent With Birdie<Br>" );
    if( $jumpedfrom )
        echo( "<a href='class_detail.php?id=$jumpedfrom'>Jumped From: #$jumpedfrom</a><Br>" );
    echo( "</td>");
    echo( "</tr>\n" );
    $cnt++;
}
?>
</table><br>
<input type='checkbox' name='includeothers' onClick='javascript:showOtherDates( this.checked )' id='includeothers' value=1> <label for="includeothers">Include other upcoming classes from the same locations?</label>
                                                                                                                                                           <?php if( !isset($otherdatefrom) || !$otherdatefrom ) { 
                                                                                                                                                                                      $otherdatefrom = $datefrom ?? '';
                                                                                                                                                                                      $otherdateto = date( "Y-m-d", strtotime( "$otherdatefrom + 1 week" ) ); }

                                                                                                                                                                                      ?>
<span id='otherspan' style='display:none'><span class="copy"><?php echo printdates2( "otherdatefrom", $otherdatefrom ?? '' ); ?>
                                                                                                                                                           to

<?php echo printdates2( "otherdateto", $otherdateto ?? '' ); ?></span>
                                                                                                                                                           </span>

                                                                                                                                                           <br><br>

      <input type='submit' name='manageav' value='Manage A/V equipment/Equipment Numbers'>
<input type='submit' name='printrosters' value='Print rosters'>
<input type='submit' name='managedates' value='Manage ship to and pick up dates'>
<input type='submit' onClick="document.forms.seform.method='get'; return true" name='printshippingpdf' value='Print shipping labels'>
<input type='submit' onClick="document.forms.seform.method='get'; return true" name='printpaperworklabels' value='Print paperwork labels'>
      <Br><br>
<!--
<input type='submit' name='printpaperwork' value='Print paperwork labels'>
<input type='submit' name='manageequipment' value='Manage equipment numbers'>-->
<!--        Special consideration for multiple classes at one site-->
<!--        Managing UPS vs courier shipments-->

<!--    <input type='submit' onClick='document.forms["seform"].action = "shippingexport.php"; document.forms["seform"].target="_blank"; return true' name='go' value='Go'>&nbsp;&nbsp;&nbsp;&nbsp;-->
</form>
<?php } ?>
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
<script type="text/javascript" language="javascript">// <![CDATA[
    function checkAnyClasses( formname )
        {
            var checkboxes = new Array();
            checkboxes = document[formname].getElementsByTagName('input');
            for (var i=0; i<checkboxes.length; i++)  {
                if (checkboxes[i].type == 'checkbox')   {
                    if( checkboxes[i].name == "includeothers" ) continue;
                    if( checkboxes[i].checked ) return true;
                }
            }

            alert( "No classes selected." );
            return false;
        }


    function checkAll(formname, checktoggle)
{
    var checkboxes = new Array();
    checkboxes = document[formname].getElementsByTagName('input');

    for (var i=0; i<checkboxes.length; i++)  {
        if (checkboxes[i].type == 'checkbox')   {
            if( checkboxes[i].name == "includeothers" ) continue;

            iid = checkboxes[i].id;
            if( $('#' + iid + "-tr").css( "display") == "none" ) continue;
            if( $('#' + iid).data( "sent" ) != "no" ) continue;
            checkboxes[i].checked = checktoggle;
        }
    }
}

function checkColor(checktoggle)
{
    var checkboxes = new Array();
    checkboxes = document['seform'].getElementsByTagName('input');

    for (var i=0; i<checkboxes.length; i++)  {
        if (checkboxes[i].type == 'checkbox')   {
            if( checkboxes[i].name == "includeothers" ) continue;
            iid = checkboxes[i].id;
            docheck = "";
            if( $('#' + iid).data( "color" ) != checktoggle )
            {
                docheck = "none";
                $('#' + iid).attr( "checked", false );
            }
            $('#' + iid + "-tr").css( "display", docheck );
//            alert( $('#' + iid + "-tr").html() );
//            alert('#' + iid + "-tr -----" +  docheck );
        }
    }
}

function cancelThisBirdie( classid, type, myhref )
{
if( confirm( "Are you sure you want to cancel this delivery?" ) )
{

        strURL = "ajaxcancelbirdie.php?classid=" + classid + "&type=" + type;
        var req = getXMLHTTP(); // fuction to get xmlhttp object
        if (req)
        {
            req.onreadystatechange = function()
                {
                    if (req.readyState == 4) { //data is retrieved from server
                        if (req.status == 200) { // which reprents ok status
                            document.getElementById( type + "_" + classid ).innerHTML = "";
                            myhref.style.display = 'none';
                        }
                        else
                        {
                            alert("There was a problem cancelling this delivery.\n");
                        }
                    }
                }
            req.open("GET", strURL, true); //open url using get method
            req.send(null);
        }
        
        
}

return false;
}

function showOtherDates( ischecked )
{
    if( ischecked )
    {
        document.getElementById( "otherspan" ).style.display = "";
    }
    else
    {
        document.getElementById( "otherspan" ).style.display = "none";
    }
}


// ]]></script>

</html>
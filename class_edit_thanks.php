<?php

// error_reporting(E_ALL);
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);

require_once('mysql.php');
require_once('services.php');
$datesaved = time();

// Declare variables that might be used before being defined
$uploadcards = $_POST['uploadcards'] ?? null;
$clearnames = $_POST['clearnames'] ?? null;
$addnames = $_POST['addnames'] ?? null;
$updatelocation = $_POST['updatelocation'] ?? null;
$updatecompletions = $_POST['updatecompletions'] ?? null;
$saveattendees = $_POST['saveattendees'] ?? null;
$addannual = $_POST['addannual'] ?? null;
$splitclass = $_POST['splitclass'] ?? null;
// $specialadmin = $_POST['specialadmin'] ?? null;

$id = $_GET['id'] ?? $_POST['id'] ?? null;
$newcompanyid = $_POST['newcompanyid'] ?? null;
$session_id = $_SESSION['id'] ?? null;
$session_userid = $_SESSION['userid'] ?? null;
$session_iscorp = $_SESSION['iscorp'] ?? null;
$tcfacultyid = $_POST['tcfacultyid'] ?? null;
$starttime = $_POST['starttime'] ?? null;
$attended = $_POST['attended'] ?? [];
$startdate = $_POST['startdate'] ?? null;
$instructornotes = $_POST['instructornotes'] ?? null;
$equipnotes = $_POST['equipnotes'] ?? null;

if( $uploadcards )
{
//    print_r( $_FILES );

    $extension = strpos( strtolower( $_FILES["cardfile"]["name"] ), ".xls" ) !== false? "xls":"pdf";
move_uploaded_file( $_FILES["cardfile"]["tmp_name"], "classcards/$id.$extension" );
Header( "Location: class_edit.php?id=$id&uploaded=true" );
        exit;
}

if( $clearnames )
{
    db_query( "delete from responder_to_class where classid = $id" );
    Header( "Location: class_edit.php?id=$id&nameserror=Names cleared from class." );
    exit;
}

// everything is "e" as of 7/1/2021
if( !empty($_POST["cardsmaileddate"]) )
    $_POST["ecardssent"] = 1;
if( !empty($_POST["booksmaileddate"]) )
    $_POST["ebookssent"] = 1;


//print_r( $_POST );exit;
if( $addnames )
{
    $file = $_FILES["namesfile"]["tmp_name"];
    $handle = fopen($file, "r");
    $crow = getClassRow( $id );
    $oldcount = db_query_first_cell( "select count(*) from responder_to_class where classid = $id" );
    
    $wouldadd = array();
    while (($data = fgetcsv($handle, 9999, ",")) !== FALSE) { // && !$done
//    print_r( $data );
$last = $data[0];
$first = $data[1];
$wouldadd[] = array( $last, $first, $data[2] ?? '', $data[3] ?? '', $data[4] ?? '', $data[5] ?? '' );
if( count( $data ) > 6 ) 
    {
$names = count( $data );
Header( "Location: class_edit.php?id=$id&nameserror=Too many columns in csv ($names)." );
        exit;

    }
    }
    if( ($oldcount + count( $wouldadd )) > $crow["maxattendees"] )
{
    $names = count( $wouldadd );
    $max = $crow["maxattendees"] - $oldcount; 
    Header( "Location: class_edit.php?id=$id&nameserror=Too many names ($names) for available spots ($max)." );
        exit;
}
    else
{
    $companyid = $crow['companyid'];
    $comrow = getCompanyRow( $companyid );
    foreach( $wouldadd as $data )
{
    $last = escMe( $data[0] );
    if( $last == "Last Name" ) continue;
    $first = escMe( $data[1] );
    $pmsid = !empty($data[2]) ? $data[2] : "x";
    $email = escMe( $data[3] ?? "" );
    $jobtitle = escMe( $data[4] ?? "" );
    $phone = escMe( $data[5] ?? "" );
    $timeslot = escMe( $data[6] ?? "" );
    
    if( $pmsid && empty($comrow["iscorp"]) ) 
{
    $pmsidvalidated = validateEmployee( $pmsid, $last, "addfromcsv" );
    $extpms = " pmsidvalidated = '$pmsidvalidated', lastpmsvalidated = now()";
}
    else
{
$extpms = " pmsidvalidated = '0'";
}
    
    if( $jobtitle )
{
    $extpms .= ", title = '$jobtitle'";
}
    if( $phone )
{
    $extpms .= ", dayphone = '$phone'";
}
    
    $ins = db_query_first_cell( "select responderid from responders_esi where clientid = '$companyid' and lastname = '$last' and firstname = '$first'" );
    if( $ins )
{
    // update their emails
    if( !empty($comrow["iscorp"]) )
{
db_query( "update responders_esi set email = '$email', pmsid = '$pmsid', firstname = '$first', lastname = '$last' where responderid = $ins" );
//echo( "update responders_esi set email = '$email', pmsid = '$pmsid', firstname = '$first', lastname = '$last' where responderid = $ins" );
}
    else
db_query( "update responders_esi set email = '$email', firstname = '$first', lastname = '$last' where responderid = $ins" );

}
    else
{
    $sql = "INSERT INTO responders_esi ( clientid , firstname , lastname , pmsid, email, raddedby, raddeddate ) VALUES ( '$companyid', '$first', '$last', '$pmsid', '$email',  '$session_id', now() )";
    $ins = db_query_insert_id( $sql );
}
    
    db_query( "update responders_esi set $extpms where responderid = $ins" );
    $position = db_query_first_cell( "select max( position ) from responder_to_class where classid = $id" );
    if( !$position ) $position = 0;
    $position++;
    // got timeslot above
//    echo( "added attendee" );
    addAttendee( $datesaved, $id, $ins, $position, 0, 0, 0 );
}
//echo( "done" );
//       exit;
    clearNonUpdated( $datesaved, $id );
    //    exit;
    Header( "Location: class_edit.php?id=$id&nameserror=" . count( $wouldadd ). " names added to class." );
    exit;
    
}
    exit;
}

if( $updatelocation )
{
db_query( "update class set companyid = $newcompanyid where id = $id" );
    Header( "Location: class_edit.php?id=$id" );
        exit;

}

$oldcrow = getClassRow( $id );

// wow this is a mess
if( strpos( $oldcrow["confirmationnotes"], "Quick Schedule" ) !== false && $starttime ) //
{
$d = strtotime( $oldcrow["startdate"] );

$newd = date( "Y-m-d H:i:s", strtotime( date( "Y-m-d", $d ) . " " . $starttime ) );
db_query( "update class set startdate = '$newd' where id = $id" );
$oldcrow["startdate"] = $newd;
$_POST["startdate"] = $newd;
}
//exit;
if( $updatecompletions )
{
$crow = getClassRow( $id );
db_query( "update responder_to_class set attended = 0 where classid = '$id'" );
foreach( $attended as $a => $throwaway )
    {
        db_query( "update responder_to_class set attended = 1 where classid = '$id' and position = '$a' " );
    }
        // foreach( $completed as $c => $throwaway)
        //     {
    //         $a = getattendeeidbyposition( $id, $c );
    //         db_query( "delete from responder_training_dates where classid = '$id' and responderid = '$a' " );
    //         db_query( "insert into responder_training_dates( classid, responderid, trainingdate, program, addedby ) values ( '$id', '$a', '$crow[startdate]', '$crow[code]', '$session_id' )" );
    
    //     $arow = getResponderRow( $a );
    //     updateResponder( $arow );
    //     }
//    exit;
    Header( "Location: class_edit.php?id=$id" );
    exit;
}

if( $saveattendees )
{
    $crow = getClassRow( $id );
    $oldcount = db_query( "select count(*) from responder_to_class where classid = $id" );
    
//    db_query( "delete from responder_to_class where classid = $id" );
$cnt = 1;

    for( $i = 1; $i <= $crow["maxattendees"]; $i++ )
    {
$timeslot = $_POST["timeslot"][$i] ?? '';
        addAttendee( $datesaved, $id, $_POST["attendee".$i] ?? null, $cnt, $_POST["paid"][$i] ?? 0, $_POST["attended"][$i] ?? 0 );

$cnt++;
    }

    clearNonUpdated( $datesaved, $id );
    
    $newcount = db_query( "select count(*) from responder_to_class where classid = $id" );
    if( $session_userid == "michele.adler@morganstanley.com" && $oldcount < 12 && $newcount >= 12 )
    {
        $body = "This Morgan Stanley class has been changed and now has 12 or more attendees :
https://doe.emergencyskills.com/class_detail.php?id=$id

Thanks!
";
        sendMail( "barbara@emergencyskills.com", "Morgan Stanley Class with 12 or more attendees", $body , "info@emergencyskills.com" );
        
    }
if( $splitclass )
    {
        $resp = db_query_rows( "select * from responder_to_class where classid = $id order by position" );
        $origid = $id;
        $i = 0;
        $position = 0;
        foreach( $resp as $r )
            {
                $i++;
                $position++;
                if( $i < 10 )
                    continue;
                else
                {
                    if( floor( $i/10 ) ==$i/10 )
                    {
                            // tiime to make a new class
                        $position = 1;
                        $id = db_query_insert_id( "insert into class ( companyid, coursefee, rosterreceived, cardsnotneeded, blendedlearning, getsecards, getsebooks, spotcheck, pcbpreceived, remote, ecardssent, ebookssent, isnational, isups, invoiceno, invoicenotes, nummasks, ponumber, code, startdate, enddate, deleted, lastmodified, lastmodifier, attendee1, attendee2, attendee3, attendee4, attendee5, attendee6, attendee7, attendee8, attendee9, attendee10, scheduler_is_contact, firstname, mi, lastname, phone, cellphone, phone_ext, fax, email, alt_firstname, alt_lastname, alt_mi, altcontacttitle, contacttitle, alt_phone, altcellphone, alt_phone_ext, alt_fax, alt_email, parking_security, nearest_subway, available_tvvcr, available_streaming, available_tvdvd, available_powerpoint, available_computer, available_dvdremote, hasanydvd, noavavailable, parking_reserved, reserved_class_adequate, room_permit_no, notes, instructornotes, trainerid, addedby, attendee11, attendee12, accepted, requestdate, confirmdate, room_permit, school_entrance, training_location, training_room_number, training_city, training_state, training_zip, confirmationtext, emergency_name, emergency_cell, confirmationnotes, classeval, equipreturned, equipscheduled, equipnumber, avequip, custodian, custodianphone, custodianext, equipdelivinstr, principalphone, principalname, principalemail, maxattendees, trainerreq ) values ( '".escMe( $crow['companyid'] )."', '".escMe( $crow['coursefee'] )."', '".escMe( $crow['rosterreceived'] )."', '".escMe( $crow['cardsnotneeded'] )."', '".escMe( $crow['blendedlearning'] )."', '".escMe( $crow['getsecards'] )."', '".escMe( $crow['getsebooks'] )."', '".escMe( $crow['spotcheck'] )."', '".escMe( $crow['pcbpreceived'] )."', '".escMe( $crow['remote'] )."', '".escMe( $crow['ecardssent'] )."', '".escMe( $crow['ebookssent'] )."', '".escMe( $crow['isnational'] )."', '".escMe( $crow['isups'] )."', '".escMe( $crow['invoiceno'] )."', '".escMe( $crow['invoicenotes'] )."', '".escMe( $crow['nummasks'] )."', '".escMe( $crow['ponumber'] )."', '".escMe( $crow['code'] )."', '".escMe( $crow['startdate'] )."', '".escMe( $crow['enddate'] )."', '".escMe( $crow['deleted'] )."', '".escMe( $crow['lastmodified'] )."', '".escMe( $crow['lastmodifier'] )."', '".escMe( $crow['attendee1'] )."', '".escMe( $crow['attendee2'] )."', '".escMe( $crow['attendee3'] )."', '".escMe( $crow['attendee4'] )."', '".escMe( $crow['attendee5'] )."', '".escMe( $crow['attendee6'] )."', '".escMe( $crow['attendee7'] )."', '".escMe( $crow['attendee8'] )."', '".escMe( $crow['attendee9'] )."', '".escMe( $crow['attendee10'] )."', '".escMe( $crow['scheduler_is_contact'] )."', '".escMe( $crow['firstname'] )."', '".escMe( $crow['mi'] )."', '".escMe( $crow['lastname'] )."', '".escMe( $crow['phone'] )."', '".escMe( $crow['cellphone'] )."', '".escMe( $crow['phone_ext'] )."', '".escMe( $crow['fax'] )."', '".escMe( $crow['email'] )."', '".escMe( $crow['alt_firstname'] )."', '".escMe( $crow['alt_lastname'] )."', '".escMe( $crow['alt_mi'] )."', '".escMe( $crow['altcontacttitle'] )."', '".escMe( $crow['contacttitle'] )."', '".escMe( $crow['alt_phone'] )."', '".escMe( $crow['altcellphone'] )."', '".escMe( $crow['alt_phone_ext'] )."', '".escMe( $crow['alt_fax'] )."', '".escMe( $crow['alt_email'] )."', '".escMe( $crow['parking_security'] )."', '".escMe( $crow['nearest_subway'] )."', '".escMe( $crow['available_tvvcr'] )."', '".escMe( $crow['available_streaming'] )."', '".escMe( $crow['available_tvdvd'] )."', '".escMe( $crow['available_powerpoint'] )."', '".escMe( $crow['available_computer'] )."', '".escMe( $crow['available_dvdremote'] )."', '".escMe( $crow['hasanydvd'] )."', '".escMe( $crow['noavavailable'] )."', '".escMe( $crow['parking_reserved'] )."', '".escMe( $crow['reserved_class_adequate'] )."', '".escMe( $crow['room_permit_no'] )."', '".escMe( $crow['notes'] )."', '".escMe( $crow['instructornotes'] )."', '".escMe( $crow['trainerid'] )."', '".escMe( $crow['addedby'] )."', '".escMe( $crow['attendee11'] )."', '".escMe( $crow['attendee12'] )."', '".escMe( $crow['accepted'] )."', Now(), '".escMe( $crow['confirmdate'] )."', '".escMe( $crow['room_permit'] )."', '".escMe( $crow['school_entrance'] )."', '".escMe( $crow['training_location'] )."', '".escMe( $crow['training_room_number'] )."', '".escMe( $crow['training_city'] )."', '".escMe( $crow['training_state'] )."', '".escMe( $crow['training_zip'] )."', '".escMe( $crow['confirmationtext'] )."', '".escMe( $crow['emergency_name'] )."', '".escMe( $crow['emergency_cell'] )."', '".escMe( $crow['confirmationnotes'] )."', '".escMe( $crow['classeval'] )."', '".escMe( $crow['equipreturned'] )."', '".escMe( $crow['equipscheduled'] )."', '".escMe( $crow['equipnumber'] )."', '".escMe( $crow['avequip'] )."', '".escMe( $crow['custodian'] )."', '".escMe( $crow['custodianphone'] )."', '".escMe( $crow['custodianext'] )."', '".escMe( $crow['equipdelivinstr'] )."', '".escMe( $crow['principalphone'] )."', '".escMe( $crow['principalname'] )."', '".escMe( $crow['principalemail'] )."', '10', '".escMe( $crow['trainerreq'] )."' ) " );

                        db_query( "insert into reschedules ( classid, newdate, newtime, thedate, who ) values ( '$id', '$crow[startdate]', '', now(), '$session_userid' )" );
                    }
                    
                    db_query( "delete from responder_to_class where responderid = $r[responderid] and position = '$r[position]' and classid = '$r[classid]' " );
    $timeslot = $r["timeslot"] ?? '';
                    addAttendee( $datesaved, $id, $r['responderid'], $position, $r['individual'] ?? 0 );
//                    echo( "Adding $r[responderid] to $id<br>" );
                    
                }
            }
    }
    clearNonUpdated( $datesaved, $id );
    Header( "Location: class_edit.php?id=$id" );
    exit;
}
$crow = getClassRow( $id );
$numnew = 0 ?? '';
for( $i =1; $i <= $crow["maxattendees"]; $i++ )
{
    if( !empty($_POST["attendee".$i]) )
        $numnew++;
}

if( $oldcrow["maxattendees"] >  0 && !isset( $_POST["attendee" . $oldcrow["maxattendees"]] ) )
    {
echo( "Something is wrong here, attendees weren't recorded properly, please let Sarah know what you just did if you remember so the code can be fixed. " );
print_r( $oldcrow["maxattendees"] );
echo( "<br>" );
echo( "<textarea style='width: 600px; height: 300px' >" );
print_r( $_POST );
echo( "</textarea>") ;
exit;
    }


    if( $addannual )
    {
$nextdate = date( "Y-m-d", strtotime( $startdate . " + 300 days" ) );
        db_query( "insert into recertnotes ( recertificationnotes,recertdate,recertperson, companyid, nextcalldate, assignedto, tassignedto) values  ('Annual', now(), $session_id, $oldcrow[companyid], '".fixdatefordb( $nextdate)."', '9235', '' )" );
    
    }


$checkboxes = array( "isnational", "islocked", "isups", "isconferenceroom", "rosterreceived", "cardsnotneeded", "getsecards", "blendedlearning", "getsebooks", "spotcheck", "pcbpreceived", "remote", "ecardssent", "ebookssent", "available_tvvcr", "available_tvdvd", "available_streaming", "equipreturned", "equipscheduled", "available_dvdremote", "noavavailable", "hasanydvd", "iscallconfirmed", "available_computer", "available_powerpoint", "equip_roundtrip", "equip_tokeep", "equip_hirt", "equip_hik" );


$fields = array(
"code",
"startdate", 
"coursefee", 
"rosterreceived", 
"cardsnotneeded", 
"blendedlearning", 
"getsecards", 
"getsebooks", 
"spotcheck", 
"pcbpreceived", 
"remote",
"teamslink",
"equip_roundtrip", 
"equip_tokeep", 
"equip_hirt", 
"equip_hik", 
"ecardssent", 
"isnational", 
"isups", 
"isconferenceroom", 
"invoiceno", 
"invoicenotes", 
"nummasks", 
"ponumber", 
"invoicepaid", 
"tcfacultyid", 
"tradein", 
"enddate", 
"deleted", 
"lastmodified", 
"lastmodifier", 
"firstname", 
"mi", 
"lastname", 
"phone", 
"cellphone", 
"phone_ext", 
"fax", 
"email", 
"principalname", 
"principalemail", 
"principalphone", 
"equipnumber", 
"avequip", 
"custodian", 
"custodianphone", 
"custodianext", 
"emergency_name", 
"emergency_contact", 
"emergency_cell", 
"alt_firstname", 
"alt_mi", 
"altcontacttitle", 
"contacttitle", 
"alt_lastname", 
"alt_phone", 
"altcellphone", 
"alt_phone_ext", 
"alt_fax", 
"alt_email", 
"parking_security", 
"nearest_subway", 
"available_tvvcr", 
"available_streaming", 
"available_tvdvd", 
"available_powerpoint", 
"available_computer", 
"available_dvdremote", 
"hasanydvd", 
"iscallconfirmed", 
"noavavailable", 
"parking_reserved",
"reserved_class_adequate", 
"room_permit", 
"school_entrance", 
"training_location", 
"training_room_number", 
"training_city", 
"training_state", 
"training_zip", 
"equipdelivinstr", 
"room_permit_no", 
"confirmationnotes", 
"classeval", 
"equipreturned", 
"equipscheduled", 
"maxattendees", 
"lockreason", 
"numtrainers", 
"cardsmailed", 
"cardsmaileddate", 
"booksmailed", 
"booksmaileddate", 
"instructornotes", 
"equipnotes", 
"shipmentstatus", 
"islocked", 
"notes"
);


if( !isSpecialAdmin() )
{
    $_POST["confirmationnotes"] = db_query_first_cell( "select confirmationnotes from class where id = $id" );
    $_POST["iscallconfirmed"] = db_query_first_cell( "select iscallconfirmed from class where id = $id" );
    $_POST["classeval"] = db_query_first_cell( "select classeval from class where id = $id" );
    $_POST["equipnotes"] = db_query_first_cell( "select equipnotes from class where id = $id" );
    $_POST["lockreason"] = db_query_first_cell( "select lockreason from class where id = $id" );
    $_POST["shipmentstatus"] = db_query_first_cell( "select shipmentstatus from class where id = $id" );
    $_POST["equipnumber"] = db_query_first_cell( "select equipnumber from class where id = $id" );
    $_POST["islocked"] = db_query_first_cell( "select islocked from class where id = $id" );
    $_POST["avequip"] = db_query_first_cell( "select avequip from class where id = $id" );
}


if( !empty($_POST["isups"]) && strpos( $_POST["instructornotes"] ?? '', "cards@emergencyskills.com") === false )
    {

$_POST["instructornotes"] .= "You MUST email cards@emergencyskills.com the roster once it is completed and before it is packed into the box to be returned.";
$instructornotes = $_POST["instructornotes"];
    }

if( !empty($_POST["remote"]) && strpos( $_POST["equipnotes"] ?? '', "REMOTE CLASS") === false )
    {

$_POST["equipnotes"] = "** REMOTE CLASS **\n" . ($_POST["equipnotes"] ?? '');
$equipnotes = $_POST["equipnotes"];
    }

if( !empty($_POST["blendedlearning"]) && strpos( $_POST["equipnotes"] ?? '', "BLENDED LEARNING") === false )
    {

$_POST["equipnotes"] = "** BLENDED LEARNING **\n" . ($_POST["equipnotes"] ?? '');
$equipnotes = $_POST["equipnotes"];
    }

if( ($_POST["code"] ?? '') == "HS PedCPRAED" && strpos( $_POST["equipnotes"] ?? '', "streamed") === false )
    {

$_POST["equipnotes"] = "** This class MUST be streamed. **\n" . ($_POST["equipnotes"] ?? '');
$equipnotes = $_POST["equipnotes"];
    }

if( !empty($_POST["blendedlearning"]) && strpos( $_POST["instructornotes"] ?? '', "BLENDED LEARNING") === false )
    {

$_POST["instructornotes"] = "** BLENDED LEARNING **\n" . ($_POST["instructornotes"] ?? '');
$instructornotes = $_POST["instructornotes"];
    }

if( $tcfacultyid && strpos( $_POST["equipnotes"] ?? '', "Send TCF Kit" )===false && empty($session_iscorp) )
    {
$_POST["equipnotes"] .= "\nSend TCF Kit\n";
$equipnotes = $_POST["equipnotes"];

    }
    if( !empty($_POST["equip_roundtrip"]) && empty($_POST["was_equip_roundtrip"]) )
    {
$_POST["equipnotes"] .= "\nEquipment round trip - Prestans or Prompts\n";
$equipnotes = $_POST["equipnotes"];

$_POST["instructornotes"] .= "\nEquipment round trip - will have Prestans or Prompts, AED trainer units";
$instructornotes = $_POST["instructornotes"];
    }
    if( !empty($_POST["equip_tokeep"]) && empty($_POST["was_equip_tokeep"]) )
    {
$_POST["equipnotes"] .= "\nAnytime Kit option, AED simulator, mask, all equipment they keep.\n";
$equipnotes = $_POST["equipnotes"];

$_POST["instructornotes"] .= "\nAnytime Kit option, AED simulator, mask, all equipment they keep. Participants should download the TCPR app.";
$instructornotes = $_POST["instructornotes"];
    }
    if( !empty($_POST["equip_hirt"]) && empty($_POST["was_equip_hirt"]) )
    {
$_POST["equipnotes"] .= "\nHeartsaver Interactive - QCPR Manikins\n";
$equipnotes = $_POST["equipnotes"];

$_POST["instructornotes"] .= "\nHeartsaver Interactive - QCPR Manikins";
$instructornotes = $_POST["instructornotes"];
    }
    if( !empty($_POST["equip_hik"]) && empty($_POST["was_equip_hik"]) )
    {
$_POST["equipnotes"] .= "\nHeartsaver Interactive- Anytime kit, AED simulator, mask, all equipment they keep.\n";
$equipnotes = $_POST["equipnotes"];
$_POST["instructornotes"] .= "\nHeartsaver Interactive- Anytime kit, AED simulator, mask, all equipment they keep. Participants should download the TCPR app.";
$instructornotes = $_POST["instructornotes"];
    }

if( $oldcrow["confirmationnotes"] != stripslashes( $_POST["confirmationnotes"] ?? '') )
    db_query( "update class set cnotesadded = now() where id = $id" );

$sql = get_sql_update("class", $fields, $_POST, "id", $checkboxes);
//echo $sql;exit;
$ret = mysqli_query($link, $sql) or die( mysqli_error( $link ) );
$rownums = [] ?? 0;
if( !empty($rownums) )
{
    $oldvals = getClassInfo( $id );
    foreach( $rownums as $r=>$name )
    {
        $val = $oldvals[$name]["value"] ?? '';
        if( $val != stripslashes( $shippingvals[$r] ?? '') )
        {
            db_query( "update class_info set deleted = 1 where classid = $id and name = '$name'" );
            db_query( "insert into class_info ( addedby, addedbytime, classid, name, value ) values ( '$session_userid', now(), '$id', '$name', '" . ($shippingvals[$r] ?? ''). "' ) " );
            if( $name == "Bagset" ) db_query( "update class set bagset = '" . ($shippingvals[$r] ?? ''). "' where id = $id" );
        }
    }
}


// file_put_contents( "/home/esi/updateclass", "$id -- ". date( "Y-m-d H:i:s" ) . "\n", FILE_APPEND );
// file_put_contents( "/home/esi/updateclass", print_r( $_POST, true ) . "\n\n", FILE_APPEND );

// $hand2 = fopen( "/home/esi/members", "a+" );
// fwrite( $hand2, "$id -- ". date( "Y-m-d H:i:s" ) . "\n" );
$rows = db_query_rows( "select * from responder_to_class where classid = $id" );
$addedbyarr = array();
$addedbydatearr = array();
foreach( $rows as $r )
{
    $addedbyarr[$r["responderid"]] = $r["sessionid"];
    $addedbydatearr[$r["responderid"]] = $r["dateadded"];
// fwrite( $hand2, "OLD: $r[responderid], $r[position], $r[individual], $r[sessionid], $r[dateadded], $session_userid\n" );
}
// fwrite( $hand2, "new attendees are ($oldcrow[maxattendees]):\n" );


//db_query( "delete from responder_to_class where classid = $id" );
$cnt = 1;
for( $i = 1; $i <= $oldcrow["maxattendees"]; $i++ )
{
    // fwrite( $hand2, ($_POST["attendee" . $i] ?? '') . ", $cnt, "  . ($_POST["individ".$id] ?? '') . "\n" );
if( !empty($_POST["attendee".$i]) )
    {
$timeslot = $_POST["timeslot"][$i] ?? '';
        addAttendee( $datesaved, $id, $_POST["attendee".$i], $cnt, $_POST["paid"][$i] ?? 0, $_POST["attended"][$i] ?? 0 );
        $cnt++;
    }

}

clearNonUpdated( $datesaved, $id );

// fclose( $hand2 );


$crow = getClassRow( $id );

if( $specialadmin || strtolower( $session_userid ) == "stregistrar@ahrcnyc.org"  )
{
    $currenttrainers = getTrainers( $id );
    $trainerids = $_POST['trainerids'] ?? [];
    $trainerids[] = -1;
    db_query( "delete from trainer_to_class where trainerid not in ( " . join( ", ", $trainerids ) . " ) and classid = $id " );
    $attendees = get_attendees( $id );
    foreach( $trainerids as $trainerid )
    {
        if( $trainerid < 0 )
            continue;
        if( !($currenttrainers[$trainerid] ?? false) && $trainerid )
        {
                // this adds the trainer to class and sends the confirm email
            sendTrainerConfirmEmail( $trainerid,$crow );
        }
    }

    if( ($oldcrow["tcfacultyid"] ?? '') != $tcfacultyid && $tcfacultyid )
    {
        sendTrainerConfirmEmail( $tcfacultyid,$crow, true );
    }
    if( ($oldcrow["equipnotes"] ?? '') != stripslashes( $equipnotes ) && $equipnotes )
    {
        db_query( "update class set enotesby = '$session_userid', enotesadded = now() where id = $id" );
    }
}

$sd1 = strtotime( $startdate );
$sd2 = strtotime( $oldcrow["startdate"] );
if( $sd1 != $sd2 ) // && strpos( $oldcrow["confirmationnotes"], "Quick Schedule" ) === false
{
     db_query( "insert into reschedules ( classid, newdate, newtime, thedate, who ) values ( '$id', '$startdate', '', now(), '$session_userid' )" );
     db_query( "update class set canceldate = null, cancelledby = null where id = '$id'" );
    $subj = "Class Rescheduled";
    $body = "A class you are scheduled to teach has been rescheduled.
Class: " . $class_names[$crow["code"]]."<br>
".getSchoolStr( "School", $comrow["iscorp"] ).": ".$comrow["companyname"]." ($comrow[schoolcode])<br><br>

Date:" . fixdatefordisplay( $oldcrow["startdate"], true ) . "
Time: ".getFormattedTime( $oldcrow["startdate"] )."

";
    
    $currenttrainers = getTrainers( $id );
    if( is_array( $currenttrainers ) && 1 == 0 ) // removed 1/4/21
    {
        foreach( $currenttrainers as $t )
        {
                //          print_r( $t );
            if( empty($t["trainingsites"]) )
                sendMail( $t["userid"], $subj, $body, "info@emergencyskills.com" );
        }
//        sendMail( "rachelc@gmail", $subj, $body, "info@emergencyskills.com" );
    }


    // add email here -=- RUC
    $companyname = getCompanyName( $crow["companyid"] );
    $em = getClassEmail( $crow );
    $cont = getClassContact( $crow );
    $comrow = getCompanyRow( $crow["companyid"] );
    if( !empty($oldcrow["accepted"]) )
resendScheduledEmail(  $crow, $comrow, $em, $companyname, $cont );
    db_query( "update class set lasttrainerreqdate = null, iscallconfirmed = 0, hostconfirmdate = null, birdieid = null, returnbirdieid = null where id = '$crow[id]'" ); 
// end add
    
    db_query( "delete from trainer_to_class where classid = '$crow[id]'" ); 
    db_query( "delete from requesttotrain where classid = '$crow[id]'" ); 
    db_query( "delete from class_info where classid = '$crow[id]'" ); 
    db_query( "update class_info set deleted = 1 where value = $crow[id] and name = 'jumpingfrom'" );
    
$ext = "This class has been rescheduled from : ".getFormattedDateWTime( $oldcrow["startdate"] )." to ".getFormattedDateWTime( $startdate ) ." ".getEndDateStr( $crow["enddate"] ) . "\n";

$body = "$ext 
https://doe.emergencyskills.com/class_detail.php?id=$id
";
    db_query( "update class set extranotes = '" . escMe( ($oldcrow["extranotes"] ?? '') . $ext ) . "' where id = $id" );
//db_query( "update class set extranotes = concat( extranotes, ' " . escMe( $ext ) . "' ) where id = $id" );
sendMail( "barbara@emergencyskills.com, kevin@emergencyskills.com, dfunnye@emergencyskills.com", "Class rescheduled", $body , "info@emergencyskills.com" );

$threeweeks = mktime( 0,0,0,date( "m" ), date( "d" )+21, date("Y" ) );
if( $threeweeks > strtotime( $startdate ) )
    requestTrainers( $id );

}

if( $oldcrow["instructornotes"] != stripslashes( $instructornotes ))
{
    $comrow = getCompanyRow( $crow["companyid"] );
    $body = "CLASS NOTES CHANGED:<br><br>
Date: ".( date( "m/d/Y", strtotime( $crow['startdate'] )))."<br>
Time: ".( date( "h:i a", strtotime( $crow['startdate'] )))." - ".getEndDateStr( $crow['enddate'] ) ."<br>
Class: " . $class_names[$crow['code']]."<br>
".getSchoolStr( "School", $comrow['iscorp'] ).": ".$comrow['companyname']." ($comrow[schoolcode])<br><br>
";
    

$body .= "<b>Instructor Notes</b>: ".stripslashes( $instructornotes )."
<br>
PLEASE CONFIRM RECEIPT OF THIS EMAIL BELOW:<br>
https://doe.emergencyskills.com/confirmnotes.php?id=$id<br>
<br>
Click here to view the class on Alive!net<br>
https://doe.emergencyskills.com/class_detail.php?id=$id<br>
<i>Note – you must be logged in FIRST to go directly to the course detail.</i>
<br>
Thanks!
";
// $h = fopen( "cn.txt", "a+" );
// fwrite( $h, "id: $id\n$oldcrow[instructornotes]\n$instructornotes\n\n" );
// fclose( $h );
    $trainers = getTrainers( $id );
    foreach( $trainers as $tmprow )
    {
            sendHTMLMail( $tmprow['userid'], "Class notes changed", $body , "info@emergencyskills.com" );
        }
    sendHTMLMail( "barbara@emergencyskills.com", "Class notes changed", $body , "info@emergencyskills.com" );
//    sendHTMLMail( "rachelc@gmail.com", "Class notes changed", $body , "info@emergencyskills.com" );
}
//exit;
if( $session_userid == "michele.adler@morganstanley.com"  )
{
$body = "This Morgan Stanley class has been changed :
https://doe.emergencyskills.com/class_detail.php?id=$id

Thanks!
";
    sendMail( "barbara@emergencyskills.com", "Morgan Stanley Class changed", $body , "info@emergencyskills.com" );
}

    Header( "Location: class_detail.php?id=$id" );
exit;

?>
<?php include "ssi/top.php"; ?>
<!--start center content-->

<strong><span class="title">Thank you.</span></strong><p>
Thanks for editing this <a href='class_detail.php?id=<?=$id?>'>class</a>!

<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>

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
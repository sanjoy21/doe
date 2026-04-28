<?php
require_once('mysql.php');

if( isset($_POST) && !empty($_POST) )
{
    $endhour = $_POST['endhour'];
    $endminute = $_POST['endminute'];
    $endampm = $_POST['endampm'];
    
    if( $endhour && $endminute && $endampm ) {
        $enddate = $endhour.":".$endminute." " . $endampm;
    }
    
    $fields = array(
        "addedby",
        "companyid",
        "code",
        "coursefee",
        "isnational",
        "islocked",
        "lockreason",
        "isconferenceroom",
        "startdate", 
        "enddate", 
        "deleted", 
        "trainerreq", 
        "lastmodified", 
        "lastmodifier", 
        "isups", 
        "remote", 
        "blendedlearning", 
        "getsecards", 
        "getsebooks", 
        "firstname", 
        "mi", 
        "lastname", 
        "phone", 
        "cellphone", 
        "phone_ext", 
        "fax", 
        "email", 
        "maxattendees", 
        "approvednum", 
        "numtrainers", 
        "emergency_name", 
        "emergency_cell", 
        "alt_firstname", 
        "alt_mi", 
        "altcontacttitle", 
        "contacttitle", 
        "alt_lastname", 
        "alt_phone", 
        "alt_phone_ext", 
        "alt_fax", 
        "alt_email", 
        "altcellphone", 
        "parking_security", 
        "equipdelivinstr", 
        "nearest_subway", 
        "school_entrance", 
        "training_location", 
        "training_room_number", 
        "training_city", 
        "training_state", 
        "training_zip", 
        "noavavailable", 
        "available_tvvcr", 
        "available_streaming", 
        "available_tvdvd", 
        "hasanydvd", 
        "available_powerpoint", 
        "available_computer", 
        "available_dvdremote", 
        "parking_reserved",
        "reserved_class_adequate", 
        "room_permit", 
        "room_permit_no",
        "custodian",
        "custodianphone",
        "custodianext",
        "principalname",
        "principalemail",
        "principalphone",
        "notes"
    );

    $companyid = isset($_POST['companyid']) ? intval($_POST['companyid']) : 0;
    $comrow = getCompanyRow( $companyid );
    
    // Set getsecards based on school code
    $_POST["getsecards"] = 1;
    
    if( isset($comrow["iscorp"]) && !$comrow["iscorp"] ) {
        // All DOE classes are 6 hours long
        if( isset($_POST["startdate"]) ) {
            $st = strtotime( $_POST["startdate"] );
            $_POST["enddate"] = date( "h:i a", mktime( date( "H", $st ) + 6, date( "i", $st ), date( "s", $st ), date( "n", $st ), date( "j", $st ), date( "Y", $st ) ) );
        }
    }
    
    if( isset($comrow["iscorp"]) && $comrow["iscorp"] == AGING ) {
        // All AGING classes are 3 hours long
        if( isset($_POST["startdate"]) ) {
            $st = strtotime( $_POST["startdate"] );
            $_POST["enddate"] = date( "h:i a", mktime( date( "H", $st ) + 3, date( "i", $st ), date( "s", $st ), date( "n", $st ), date( "j", $st ), date( "Y", $st ) ) );
        }
    }

    // Check for blocked dates
    if( isset($_POST["startdate"]) ) {
        $tmpst = date( "Y-m-d", strtotime( $_POST["startdate"] ) );
        $snote = db_query_first_cell( "select dt from blockeddates where dt = '" . addslashes($tmpst) . "'" );
        if( $snote && !isOverallAdmin() ) {
            echo( "Sorry, this date is blocked. Please hit back and select a new date." );
            exit;
        }
    }
    
    // Add request date
    $fields[] = "requestdate";
    $_POST["requestdate"] = date( "Y-m-d H:i:s" );
    
    // Set max attendees based on user
    $session_userid = isset($session_userid) ? $session_userid : '';
    if( $session_userid == "michele.adler@morganstanley.com" ) {
        $_POST["maxattendees"] = 18;
    }
    if( $session_userid == "andrea.pellettiere@town-sports.com" ) {
        $_POST["maxattendees"] = 9;
    }
    if( !isset($_POST["maxattendees"]) || !$_POST["maxattendees"]  ) {
        $_POST["maxattendees"] = 12;
    }
    
    // Build SQL query
    $sql = "insert into class ( ";
    $sql .= join( "," , $fields );
    $sql .= " ) values ( "; 
    $first = true;
    foreach( $fields as $f ) {
        if( !$first ) {
            $sql .= ", ";
        }
        $val = $_POST[$f];
        
        if( $f == "enddate" && (!$val || $val == -1) ) {
            $val = $enddate;
        }
        
        $sql .= "'" . addslashes($val) . "'";
        $first = false;
    }
    
    $sql .= ")";
    
    // Log the query
    $h = fopen( "/home/esi/newclass", "a+" );
    if( $h ) {
        fwrite( $h, $sql  . "\n" );
        fwrite( $h, print_r( $_POST, true )  . "\n" );
        fclose( $h );
    }
    
    // Execute query
    $ret = mysqli_query($link, $sql) or die( "please email emergencyskills@vireo.org with this error: " . htmlspecialchars($sql) . " \n\n ".mysqli_error( $link ) . "\n Thanks!");
    $ins = mysqli_insert_id( $link );

    // Add special notes
    if( isset($_POST["isups"]) && $_POST["isups"] ) {
        db_query( "update class set instructornotes = 'You MUST email cards@emergencyskills.com the roster once it is completed and before it is packed into the box to be returned.' where id = " . intval($ins) );
    } else if( isset($_POST["isconferenceroom"]) && $_POST["isconferenceroom"] ) {
        db_query( "update class set instructornotes = 'Current LockBox Code - #356907' where id = " . intval($ins) );
    }

    // Add reschedule record
    $startdate = isset($_POST["startdate"]) ? $_POST["startdate"] : '';
    $session_userid = isset($session_userid) ? addslashes($session_userid) : '';
    db_query( "insert into reschedules ( classid, newdate, newtime, thedate, who ) values ( '" . intval($ins) . "', '" . addslashes($startdate) . "', '', now(), '$session_userid' )" );
    
    // Add confirmation notes
    $cnotes = "";
    $noavavailable = isset($_POST["noavavailable"]) ? $_POST["noavavailable"] : '';
    $quicksched = isset($_POST["quicksched"]) ? $_POST["quicksched"] : '';
    $code = isset($_POST["code"]) ? $_POST["code"] : '';
    $session_iscorp = isset($session_iscorp) ? $session_iscorp : 0;
    $allclass_names = isset($allclass_names) ? $allclass_names : array();
    
    if( $noavavailable ) {
        $cnotes .= "Send A/V equipment\n";
    }
    if( $quicksched ) {
        $cnotes .= "Quick Schedule\n";
    }
    
    if( isset($allclass_names[$session_iscorp][$code]) && strpos( $allclass_names[$session_iscorp][$code],"Infant" ) !== false && $session_iscorp ) {
        $cnotes .= "PLEASE SHIP INFANT MANIKINS.\n";
    }
    
    if( $cnotes ) {
        db_query( "update class set confirmationnotes = '" . addslashes($cnotes) . "', cnotesadded = now() where id = " . intval($ins) );
        db_query( "update class set equipnotes = '" . addslashes($cnotes) . "' where id = " . intval($ins) );
    }
    
    $crow = getClassRow( $ins );

    // Add Parks region specific notes
    if( isset($crow["region"]) && $crow["region"] == "Parks" ) {
        $inotes = db_query_first_cell( "select instructornotes from class where id = " . intval($ins) );
        $inotes .= "  Please scan and email ROSTER and SKILLS TESTS to barbara@emergencyskills.com at the end of the class.";
        db_query( "update class set instructornotes = '" . addslashes($inotes) . "' where id = " . intval($ins) );
    }
    
    // Add attendees
    $sendnurseemail = false;
    $maxattendees = isset($_POST["maxattendees"]) ? intval($_POST["maxattendees"]) : 0;
    
    for( $i = 1; $i <= $maxattendees; $i++ ) {
        $attendee_id = isset($_POST["attendee".$i]) ? $_POST["attendee".$i] : 0;
        $timeslot = isset($_POST["timeslot".$i]) ? $_POST["timeslot".$i] : '';
        addAttendee( time(), $ins, $attendee_id, $i );
        if( isNurse( $attendee_id ) ) {
            $sendnurseemail = true;
        }
    }
    
    // Send nurse email notification
    if( $sendnurseemail && function_exists('OKToSendEmails') && OKToSendEmails( isset($comrow["iscorp"]) ? $comrow["iscorp"] : 0 ) ) {
        require_once "class.phpmailer.php";
        $mail = new PHPMailer();
        $mail->From = "info@emergencyskills.com";
        $mail->Subject = stripslashes( "Nurse has been registered with class $ins" );
        $mail->IsHTML(false);
        $mail->Body = "A nurse has registered for class # $ins: 
            http://". SUB_DOE.".".URL_WITHOUT_SUBDOMAIN."/class_detail.php?id=$ins";
        $mail->AddAddress("barbara@emergencyskills.com" );
        $mail->AddAddress("rebekah@emergencyskills.com" );
        $mail->Send();
    }

    // Send general notification emails
    if( function_exists('OKToSendEmails') && OKToSendEmails( isset($comrow["iscorp"]) ? $comrow["iscorp"] : 0 ) ) {
        $body = "A new class was scheduled: " . getCompanyName( $companyid ) . "
https://".(function_exists('getUrlPrefix') ? getUrlPrefix() : SUB_DOE).".".URL_WITHOUT_SUBDOMAIN."/viewcompany.php?id=$companyid
Class ID: $ins
";
        
        foreach( $_POST as $n=>$v ) {
            if( $n == "extranotes" ) continue;
            
            if( strpos( $n, "attendee" ) !== false ) {
                $body .= "$n : ".getAttendeeName( $v )." \n" ; 
            } else if( strpos( $n, "addedby" ) !== false ) {
                $body .= "$n : ".getFullname( $v )." \n" ; 
            } else if( strpos( $n, "trainerreq" ) !== false ) {
                $body .= "$n : ".getFullname( $v )." \n" ; 
            } else if( $n == "code" ) {
                $body .= "$n : ".(isset($class_names[$v]) ? $class_names[$v] : '')." \n" ; 
            } else if( strpos( $n, "companyid" ) !== false ) {
                $body .= "$n : ".getCompanyName( $v )." \n" ; 
            } else {
                $body .= "$n : $v \n" ; 
            }
        }
        
        if( isset($comrow["iscorp"]) && !$comrow["iscorp"] ) {
            $bic = isset($comrow["bic"]) && $comrow["bic"] ? "- BIC" : "";
            $subject = "CPR/AED Training Request- " . (isset($comrow["companyname"]) ? $comrow["companyname"] : '') . $bic;
            sendMail( "barbara@emergencyskills.com, rebekah@emergencyskills.com", $subject, $body, "info@emergencyskills.com" ); 
        }
        
        if( isset($comrow["iscorp"]) && defined('AGING') && $comrow["iscorp"] == AGING ) {
            $subject = "CPR/AED Training Request- " . (isset($comrow["companyname"]) ? $comrow["companyname"] : '');
            sendMail( "barbara@emergencyskills.com", $subject, $body, "info@emergencyskills.com" ); 
        }
        
        // Send confirmation to requester
        $body = "Thank you for requesting an Emergency Skills, Inc. Training Program. This
e-mail confirms that you have a pending program registration. A staff member
will review this registration request and will respond back to you on your
class status, as soon as possible. If you have any changes, please return to
ALIVE!net (doe.emergencyskills.com) to update your request.

Please note - this message is generated automatically. If you have any
additional questions about this enrollment, please do not hesitate to send
an email to rebekah@emergencyskills.com.

";
        
        if( function_exists('getClassEmail') ) {
            $em = getClassEmail( $crow );
        } else {
            $em = isset($_POST['email']) ? $_POST['email'] : '';
        }
        
        foreach( $_POST as $n=>$v ) {
            if( strpos( $n, "attendee" ) !== false ) {
                $body .= "$n : ".getAttendeeName( $v )." \n" ; 
            } else if( strpos( $n, "addedby" ) !== false ) {
                $body .= "$n : ".getFullname( $v )." \n" ; 
            } else if( $n == "code" ) {
                $body .= "$n : ".(isset($class_names[$v]) ? $class_names[$v] : '')." \n" ; 
            } else if( strpos( $n, "companyid" ) !== false ) {
                $body .= "$n : ".getCompanyName( $v )." \n" ; 
            } else {
                $body .= "$n : $v \n" ; 
            }
        }
        
        if( isset($comrow["iscorp"]) && !$comrow["iscorp"] ) {
            $bic = isset($comrow["bic"]) && $comrow["bic"] ? "- BIC" : ""; 
            sendMail( $em, "New class scheduled$bic", $body, "info@emergencyskills.com" ); 
            
            if( isset($crow["alt_email"]) && $crow["alt_email"] ) {
                sendMail( $crow["alt_email"], "New class scheduled$bic", $body, "info@emergencyskills.com" );
            }
            if( isset($crow["principalemail"]) && $crow["principalemail"] ) {
                sendMail( $crow["principalemail"], "New class scheduled$bic", $body, "info@emergencyskills.com" );
            }
            if( isset($crow["addedby"]) && $crow["addedby"] ) {
                sendMail( getEmail( $crow["addedby"] ), "New class scheduled$bic", $body, "info@emergencyskills.com" );
            }
        }
    }
}

// Redirect to thank you page
$ins = isset($ins) ? $ins : 0;
Header( "Location: schedule_class_thanks.php?ins=" . intval($ins) );
exit;
?>
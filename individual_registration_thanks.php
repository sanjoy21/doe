<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

$nologinrequired = true;
require "mysql.php";
$nologinrequired = true; // Added later by Sanjoy Dey
require_once "services.php";

// Convert register_globals-like variables to use $_GET, $_POST, or $_REQUEST
$email = $_REQUEST['email'] ?? '';
$companyid = $_REQUEST['companyid'] ?? '';
$mode = $_REQUEST['mode'] ?? '';
$classid = $_REQUEST['classid'] ?? '';
$firstname = $_REQUEST['firstname'] ?? '';
$lastname = $_REQUEST['lastname'] ?? '';
$borough = $_REQUEST['borough'] ?? '';
$pmsid = $_REQUEST['pmsid'] ?? '';
$emptype = $_REQUEST['emptype'] ?? '';
$session_iscorp = $_SESSION['iscorp'] ?? false;
$employeeid = $_REQUEST['employeeid'] ?? '';
$maidenname = $_REQUEST['maidenname'] ?? '';
$filenumber = $_REQUEST['filenumber'] ?? '';
$title = $_REQUEST['title'] ?? '';
$dayphone = $_REQUEST['dayphone'] ?? '';
$fax = $_REQUEST['fax'] ?? '';
$dayphoneExtension = $_REQUEST['dayphoneExtension'] ?? '';
$address1 = $_REQUEST['address1'] ?? '';
$address2 = $_REQUEST['address2'] ?? '';
$city = $_REQUEST['city'] ?? '';
$state = $_REQUEST['state'] ?? '';
$zip = $_REQUEST['zip'] ?? '';
$buildingcode = $_REQUEST['buildingcode'] ?? '';
$department = $_REQUEST['department'] ?? '';
$managername = $_REQUEST['managername'] ?? '';
$floor = $_REQUEST['floor'] ?? '';
$session_id = $_SESSION['id'] ?? 0;
$couponcode = $_REQUEST['couponcode'] ?? '';
$emailsuffix = $_REQUEST['emailsuffix'] ?? '';
$email2 = $_REQUEST['email2'] ?? '';
$isc = false; // This was commented out in original, so defaulting to false
$notok = false;

function db_escape_string($string) {
    if (is_array($string)) {
        return $string; // Do not escape arrays
    }
    // Basic placeholder for security demonstration
    return str_replace(['\\', "'"], ['\\\\', "\'"], (string)$string);
}

// print_r( $_SERVER );
// exit;
if( !$email )
{
echo( "You must provide an email address!" );
echo( "<A href='javascript:go( -1 )'>Back</a>" );
exit;
}

if( !$companyid )
{
echo( "You must select your " . getSchoolStr( "school" )."!" );
echo( "<A href='javascript:history.go( -1 )'>Back</a>" );
exit;
}

if( $mode == "create" )
{
$email = trim( $email );
$lastname = trim( $lastname );
$firstname = trim( $firstname );
    
    // Use prepared statements for security
    $crow = getClassRow( $classid );
$thecode = $crow["code"] ?? '';

// Escape variables for SQL (though prepared statements would be better)
$escaped_firstname = db_escape_string($firstname);
$escaped_lastname = db_escape_string($lastname);
$escaped_email = db_escape_string($email);

$res = db_query_first_cell( "SELECT responderid FROM responders_esi WHERE firstname LIKE '$escaped_firstname' AND lastname LIKE '$escaped_lastname' AND email LIKE '$escaped_email' AND deleted = 0" );
    
if( $res && !$isc )
{
        $upcoming = db_query_first_cell( "SELECT classid FROM responder_to_class, class WHERE code = '$thecode' AND classid = class.id AND responderid = $res AND class.startdate > NOW() AND class.canceldate IS NULL " );
        if( $upcoming )
            $notok = true;
}

    $okaytoadd = true;

    if( !$notok )
    {
        $crow = getClassRow( $classid );
        $i = 1;
        $responders = get_attendees( $classid );
        while( isset($responders[$i]) )
        {
            $i++;
        }
        $theid = $i;
        
        if( $i > ($crow["maxattendees"] ?? 0) )
        {
            Header( "Location: individual_registration2.php?classname=" . urlencode($crow["code"] ?? '') . "&borough=" . urlencode($borough) . "&toomany=true" );
    exit;
        }
        
        $crow = getClassRow( $classid );
        $class = $crow;
        $comrow = getCompanyRow( $crow["companyid"] ?? 0);
        $ischarter = isCharter( $comrow['companyname'] ?? '', $comrow['schoolcode'] ?? '' );
        $isssa = isSSA( $comrow );
        
        $ext = (empty($comrow['iscorp']) ? "" : " AND ( pmsidvalidated = 1 OR emptype IN ( 'Charter School Employee', 'SSA', 'Custodial Staff', 'Non DOE' ) )");
        
        // Escape variables
        $escaped_firstname = db_escape_string($firstname);
        $escaped_lastname = db_escape_string($lastname);
        $escaped_email = db_escape_string($email);
        
        $res = db_query_first_cell( "SELECT responderid FROM responders_esi WHERE firstname LIKE '$escaped_firstname' AND lastname LIKE '$escaped_lastname' AND email LIKE '$escaped_email' AND deleted = 0 $ext" );

        if( !$res && $pmsid && !$ischarter && !$isssa && $pmsid != "1234" && $emptype != "Charter School Employee" && $emptype != "Custodial Staff" && $emptype != "SSA" && $emptype != "Non DOE" )
        {
            $escaped_pmsid = db_escape_string($pmsid);
            $res = db_query_first_cell( "SELECT responderid FROM responders_esi WHERE pmsid = '$escaped_pmsid' AND deleted = 0 $ext AND emptype NOT IN ( 'Charter School Employee', 'SSA', 'Custodial Staff', 'Non DOE' )" );
        }

        if( !$res )
        {
            $myem = $email . $emailsuffix;
            if( empty($comrow["iscorp"]) )
            {
                $pmsidvalidated = validateEmployee( $pmsid, stripslashes( $lastname ), "individual registration" );
            }
            else
            {
                $pmsidvalidated = 0;
            }
            
            if( !$pmsidvalidated && empty($comrow['iscorp']) && $emptype == "DOE Employee" && !isOverallAdmin() )
            {
                echo( "Your Payroll Reference # or the spelling of your first or last name is not valid. <A href='individual_registration3.php?borough=" . urlencode($borough) . "&classid=" . urlencode($classid) . "&firstname=" . urlencode($firstname) . "&lastname=" . urlencode($lastname) . "&maidenname=" . urlencode($maidenname) . "&dayphone=" . urlencode($dayphone) . "&dayphoneExtension=" . urlencode($dayphoneExtension) . "&fax=" . urlencode($fax) . "&title=" . urlencode($title) . "&department=" . urlencode($department) . "&address1=" . urlencode($address1) . "&address2=" . urlencode($address2) . "&floor=" . urlencode($floor) . "&city=" . urlencode($city) . "&state=" . urlencode($state) . "&zip=" . urlencode($zip) . "&emptype=" . urlencode($emptype) . "&email=" . urlencode($email) . "&email2=" . urlencode($email2) . "&pmsid=" . urlencode($pmsid) . "'>Click here to reenter.</a><br><br>This training program is available at no charge to individuals who are currently full time employees of the NYC Dept. of Education. If you are a volunteer or retired and wish to complete the program, please contact our office to arrange for payment.  <Br><br>If you continue to have problems please contact Emergency Skills at 212-564-6833 Monday - Friday, 8am - 6pm and Saturday 9am - 1pm.<Br><br>" );
                exit;
            }
            else
            {
                if( $session_iscorp )
                {
                    $pmsid = $employeeid; 
                }
                
                // Escape all variables for SQL insertion
                $escaped_companyid = db_escape_string($companyid);
                $escaped_firstname = db_escape_string($firstname);
                $escaped_maidenname = db_escape_string($maidenname);
                $escaped_lastname = db_escape_string($lastname);
                $escaped_filenumber = db_escape_string($filenumber);
                $escaped_pmsid = db_escape_string($pmsid);
                $escaped_title = db_escape_string($title);
                $escaped_dayphone = db_escape_string($dayphone);
                $escaped_fax = db_escape_string($fax);
                $escaped_dayphoneExtension = db_escape_string($dayphoneExtension);
                $escaped_myem = db_escape_string($myem);
                $escaped_address1 = db_escape_string($address1);
                $escaped_address2 = db_escape_string($address2);
                $escaped_city = db_escape_string($city);
                $escaped_state = db_escape_string($state);
                $escaped_zip = db_escape_string($zip);
                $escaped_buildingcode = db_escape_string($buildingcode);
                $escaped_department = db_escape_string($department);
                $escaped_managername = db_escape_string($managername);
                $escaped_emptype = db_escape_string($emptype);
                $escaped_floor = db_escape_string($floor);
                $escaped_session_id = db_escape_string($session_id);
                
                $sql = "INSERT INTO responders_esi (clientid, firstname, maidenname, lastname, filenumber, pmsid, pmsidvalidated, lastpmsvalidated, title, dayphone, fax, dayphoneExtension, email, homeaddress, apt, city, state, zip, buildingcode, department, managername, emptype, floor, `date`, raddedby, raddeddate) 
                        VALUES ('$escaped_companyid', '$escaped_firstname', '$escaped_maidenname', '$escaped_lastname', '$escaped_filenumber', '$escaped_pmsid', '$pmsidvalidated', NOW(), '$escaped_title', '$escaped_dayphone', '$escaped_fax', '$escaped_dayphoneExtension', '$escaped_myem', '$escaped_address1', '$escaped_address2', '$escaped_city', '$escaped_state', '$escaped_zip', '$escaped_buildingcode', '$escaped_department', '$escaped_managername', '$escaped_emptype', '$escaped_floor', NOW(), '$escaped_session_id', NOW())";
                $res = db_query_insert_id( $sql );
            }
        }
        else
        {
            $myem = $email . $emailsuffix;
            
            // Escape variables for update
            $escaped_dayphone = db_escape_string($dayphone);
            $escaped_firstname = db_escape_string($firstname);
            $escaped_maidenname = db_escape_string($maidenname);
            $escaped_lastname = db_escape_string($lastname);
            $escaped_filenumber = db_escape_string($filenumber);
            $escaped_pmsid = db_escape_string($pmsid);
            $escaped_title = db_escape_string($title);
            $escaped_fax = db_escape_string($fax);
            $escaped_dayphoneExtension = db_escape_string($dayphoneExtension);
            $escaped_myem = db_escape_string($myem);
            $escaped_address1 = db_escape_string($address1);
            $escaped_address2 = db_escape_string($address2);
            $escaped_city = db_escape_string($city);
            $escaped_state = db_escape_string($state);
            $escaped_zip = db_escape_string($zip);
            $escaped_buildingcode = db_escape_string($buildingcode);
            $escaped_department = db_escape_string($department);
            $escaped_managername = db_escape_string($managername);
            $escaped_emptype = db_escape_string($emptype);
            $escaped_floor = db_escape_string($floor);
            
            $sql = "UPDATE responders_esi SET dayphone = '$escaped_dayphone', firstname = '$escaped_firstname', maidenname = '$escaped_maidenname', lastname = '$escaped_lastname', filenumber = '$escaped_filenumber', pmsid = '$escaped_pmsid', title = '$escaped_title', fax = '$escaped_fax', dayphoneExtension = '$escaped_dayphoneExtension', email = '$escaped_myem', homeaddress = '$escaped_address1', apt = '$escaped_address2', city = '$escaped_city', state = '$escaped_state', zip = '$escaped_zip', buildingcode = '$escaped_buildingcode', department = '$escaped_department', managername = '$escaped_managername', emptype = '$escaped_emptype', floor = '$escaped_floor' WHERE responderid = $res";
            db_query( $sql );

            if( !empty($comrow["iscorp"]) && isset($_COOKIE["regtype"]) )
            {
                $escaped_regtype = db_escape_string($_COOKIE["regtype"]);
                db_query( "UPDATE responders_esi SET title = '$escaped_regtype' WHERE responderid = $res" );
            }
        }
        
        if( $okaytoadd ) // it's ALWAYS okay by now
        {
            // $hand = fopen( "/home/esi/whatever.txt", "a+" );
            if( $couponcode )
            {
                fwrite( $hand, "$email, $firstname, $lastname, was added (coupon: $couponcode) to $classid ($theid) -- $res " . date( "Y-m-d h:i:s" ) . "\n" );
                db_query( "UPDATE responders_esi SET couponcode = '" . db_escape_string($couponcode) . "' WHERE responderid = '$theid' " );
            }
            else
                // fwrite( $hand, "$email, $firstname, $lastname, was added to $classid ($theid) -- $res " . date( "Y-m-d h:i:s" ) . "\n" );
            // fwrite( $hand, ($_SERVER["HTTP_REFERER"] ?? '') . "---" . date( "Y-m-d h:i:s" ) . "\n" );
            
            if( $theid )
            {
                $individual = 1;
                addAttendee( time(), $classid, $res, $theid );
            }
            
            $address = ($comrow["address"] ?? '') . ", ". ($comrow["city"] ?? '') . " " . ($comrow["zip"] ?? '');
            $contact = !empty($crow["firstname"]) ? $crow["firstname"] . " " . $crow["lastname"] : getUserName( $crow["addedby"] ?? 0 );
            $contactphone = !empty($comrow["iscorp"]) ? ($crow["phone"] ?? '') : getUserPhone( $crow["addedby"] ?? 0 );
            $contactphone = "212-564-6833";

            $body = " Thank you for registering for an Emergency Skills, Inc. Training Program.
The following are the details of the course for which you are registered:

**** ".getSchoolStr( "Training Location", ($comrow['iscorp'] ?? false) ).": ".getTrainingAddress( $crow )." **** 

Program Type: " . ($class_names[$crow["code"]] ?? ''). "

NOTE: LATECOMERS WILL NOT BE PERMITTED ENTRY

Program Location: " . ($comrow['companyname'] ?? '') . " 
Location Contact: $contact 
Contact Phone Number: $contactphone 
Program Date: ". getFormattedDateWTime( $crow['startdate'] ?? '' )." - " . 
    date("h:i A", strtotime($crow['enddate'])) 
."
 
" .((!empty($crow['remote']) && !empty($crow['teamslink']))?"Class Link: <a href='".($crow['teamslink'] ?? '')."'>".($crow['teamslink'] ?? '')."</a>":"" ) . "

Registrants Name: ".stripslashes( "$firstname $lastname" )."
Registrants Phone Number: $dayphone
Registrants email address: $email

TRANSPORTATION INFORMATION:
Parking/Security: ".($crow["parking_security"] ?? '')."

Nearest Subway Line/Station: ".($crow["nearest_subway"] ?? '')."


".getSchoolStr( "School Entrance", ($comrow['iscorp'] ?? false) ).": ".($crow["school_entrance"] ?? '')."

" . ($session_iscorp == 1 
    ? "If you must cancel, please call Emergency Skills, Inc. at 212-564-6833 or you may email dzamos@emergencyskills.com to cancel your registration." 
    : "If you must cancel, please call Emergency Skills, Inc. at 212-564-6833 or you may email esialive@emergencyskills.com at least 5 business days prior. There may be a penalty for cancellations received after 5 business days prior to the program.") . "

".(empty($comrow['iscorp'])?"":"" )."

";
// no terms anymore
            $savebody = $body;
            
            sendMail( "safetyplan@emergencyskills.com", "Individual Registration (user email)", $body, "info@emergencyskills.com" ); 
            sendMail( $email, "Individual Registration", $body, "info@emergencyskills.com" ); 
            
            $tl = getSchoolStr( "Training Location", ($comrow['iscorp'] ?? false) ).": " .getTrainingAddress( $crow );
            
            $body = "A participant has been added to your ".($class_names[$crow["code"]] ?? '')." TO BE HELD AT ".($comrow["companyname"] ?? '')." on ". ($crow["startdate"] ?? '') . ".
{$tl}
Below please find the current list of participants:

";

            $sendnurseemail = false;
            $responders = get_attendees( $classid );
            foreach( $responders as $arow )
            {
                $rid = $arow["responderid"];
                $urow = getResponderRow( $rid );
                if( isNurse( $rid ) )
                    $sendnurseemail = true;
                $tmpcompany = db_query_first_cell( "SELECT companyname FROM company_esi WHERE id = '" . ($urow['clientid'] ?? '') . "'" );
                $tmpcompanyid = db_query_first_cell( "SELECT schoolcode FROM company_esi WHERE id = '" . ($urow['clientid'] ?? '') . "'" );
                if( isTSI( $comrow ) )
                {
                    $body .= ($urow["firstname"] ?? '') . " " . ($urow["lastname"] ?? '') . " #".($urow['pmsid'] ?? '').", {$tmpcompany} - ".($urow['title'] ?? '')." \n
" ;
                }
                else
                {
                    $body .= ($urow["firstname"] ?? '') . " " . ($urow["lastname"] ?? '') . " - $tmpcompany $tmpcompanyid\n
" ;
                }
            }
            
            $em = getClassEmail( $crow );
            if( !empty($crow["alt_email"]) )
                sendMail( $crow["alt_email"], "New Attendee", $body, "info@emergencyskills.com" );
            sendMail( $em, "New Attendee", $body, "info@emergencyskills.com" );
            sendMail( "safetyplan@emergencyskills.com", "New Attendee", $body, "info@emergencyskills.com" );
            
            // fwrite( $hand, "done sending email" . date( "Y-m-d h:i:s" ) . "\n" );
            // fclose( $hand );

            if( $sendnurseemail && OKToSendEmails( $comrow['iscorp'] ?? false ) )
            {
                require_once "class.phpmailer.php";
                $mail = new PHPMailer();
                $mail->From = "info@emergencyskills.com";
                $mail->Subject = stripslashes( "Nurse has been registered with class $classid" );
                $mail->IsHTML(false);   // set email format to HTML
                $mail->Body   = "A nurse has registered for class # $classid: 
                       http://". SUB_DOE.".".URL_WITHOUT_SUBDOMAIN."/class_detail.php?id=$classid
"
                    ;
                $mail->AddAddress("barbara@emergencyskills.com" );
                $mail->AddAddress("rebekah@emergencyskills.com" );
                $mail->AddBCC("sarahg@emergencyskills.com" );
                $mail->Send();
            }
        }
    }
}

include "ssi/top.php"; 
?>
<!--start center content-->

<?php
     if( !$okaytoadd )
     {
         ?>
         Your last name/ID was not found in our system.  Please check the spelling of your last name and your ID.<br>
             <a href='javascript:history.go( -1 )'>< -- Back</a>
         <?php
     }
     else if( !$notok ) { ?>
        <strong><span class="title">Thank You.</span></strong>
<p>
You have registered for the course below. PLEASE PRINT THIS PAGE FOR YOUR RECORDS.<br><br>

<?php echo nl2br( $savebody ); ?>

<br><br>
<table cellpadding="4" cellspacing="1" border="0" width="100%" bgcolor="#999999">
        <tr bgcolor="#e1e1f6">
        <td valign="top">
<table cellpadding="0" cellspacing="4" border="0">
            <tr>
            <td valign="top" align="right"><span class="copy"><strong>Class:</strong></span></td>
<td valign="top"><span class="copy"><?php echo $class_names_display[$class["code"]] ?? ''; ?></span></td>
            </tr>
<tr>
            <td valign="top" align="right"><span class="copy"><strong>Date:</strong></span></td>
<td valign="top"><span class="copy"><?php echo date( "l, M. d, Y", strtotime( $class["startdate"] ?? '' ) ); ?></span></td>
            </tr>
<tr>
            <td valign="top" align="right"><span class="copy"><strong>Time:</strong></span></td>
<td valign="top"><span class="copy"><?php echo date( "h:i a", strtotime( $class["startdate"] ?? '' ) ); ?></span></td>
            </tr>
           <tr><td colspan="2"><br></td></tr>
            <tr>
            <td valign="top" align="right"><span class="copy"><strong>
<?php echo getSchoolStr( "School", $comrow['iscorp'] ?? false ); ?>:</strong></span></td>
<td valign="top"><span class="copy"><?php echo $comrow["companyname"] ?? ''; ?></span></td>
            </tr>
    <?php if( empty($comrow["iscorp"]) ) { ?>
                     <tr>
            <td valign="top" align="right"><span class="copy"><strong>Location:</strong></span></td>
<td valign="top"><span class="copy"><?php echo ($comrow["address"] ?? '') . ' ' . ($comrow["city"] ?? '') . ', ' . ($comrow["state"] ?? ''); ?></span></td>
            </tr>
                                <?php } else { ?>

<tr>
            <td valign="top" align="right"><span class="copy"><strong>Training Location:</strong></span></td> 
<td valign="top"><span class="copy"><?php echo getTrainingAddress( $class ); ?></span></td>
            </tr>
<tr>
            <td valign="top" align="right"><span class="copy"><strong>On-Site Contact:</strong></span></td> 
<td valign="top"><span class="copy"><?php echo ($class["firstname"] ?? '') . ' ' . ($class["lastname"] ?? ''); ?></span></td>
            </tr>
<tr>
            <td valign="top" align="right"><span class="copy"><strong>Phone:</strong></span></td> 
<td valign="top"><span class="copy"><?php echo $class["phone"] ?? ''; ?></span></td>
            </tr>

    <?php } ?>
<tr>
            <td valign="top" align="right"><span class="copy"><strong>Borough:</strong></span></td>
<td valign="top"><span class="copy"><?php echo $comrow["borough"] ?? ''; ?></span></td>
            </tr>

            </table>
</td>

          </tr>
        </table>

<p>
<a href='#' onClick='javascript:window.print()'><img border=0 src="../images/button_print.gif"></a>

<?php } else { ?>
<p>You are already registered for an upcoming class. <br></p>
<?php
    $class = getClassRow( $upcoming );
    $comrow = getCompanyRow( $class["companyid"] ?? 0 );
    $class_names = $allclass_names[$comrow["iscorp"] ?? false] ?? [];
?>
     <table cellpadding="0" cellspacing="4" border="0">
            <tr>
            <td valign="top" align="right"><span class="copy"><strong>Class:</strong></span></td>
<td valign="top"><span class="copy"><?php echo $class_names[$class["code"]] ?? ''; ?></span></td>
            </tr>
<tr>
            <td valign="top" align="right"><span class="copy"><strong>Date:</strong></span></td>
<td valign="top"><span class="copy"><?php echo date( "l, M. d, Y", strtotime( $class["startdate"] ?? '' ) ); ?></span></td>
            </tr>
<tr>
            <td valign="top" align="right"><span class="copy"><strong>Time:</strong></span></td>
<td valign="top"><span class="copy"><?php echo date( "h:i a", strtotime( $class["startdate"] ?? '' ) ); ?></span></td>
            </tr>
           <tr><td colspan="2"><br></td></tr>
            <tr>
            <td valign="top" align="right"><span class="copy"><strong>
<?php echo getSchoolStr( "School", $comrow['iscorp'] ?? false ); ?>:</strong></span></td>
<td valign="top"><span class="copy"><?php echo $comrow["companyname"] ?? ''; ?></span></td>
            </tr>

    <?php if( empty($comrow["iscorp"]) ) { ?>
                     <tr>
            <td valign="top" align="right"><span class="copy"><strong>Location:</strong></span></td>
<td valign="top"><span class="copy"><?php echo ($comrow["address"] ?? '') . ' ' . ($comrow["city"] ?? '') . ', ' . ($comrow["state"] ?? ''); ?></span></td>
            </tr>
                                <?php } else { ?>

<tr>
            <td valign="top" align="right"><span class="copy"><strong>Training Location:</strong></span></td> 
<td valign="top"><span class="copy"><?php echo getTrainingAddress( $class ); ?></span></td>
            </tr>
<tr>
            <td valign="top" align="right"><span class="copy"><strong>On-Site Contact:</strong></span></td> 
<td valign="top"><span class="copy"><?php echo ($class["firstname"] ?? '') . ' ' . ($class["lastname"] ?? ''); ?></span></td>
            </tr>
<tr>
            <td valign="top" align="right"><span class="copy"><strong>Phone:</strong></span></td> 
<td valign="top"><span class="copy"><?php echo $class["phone"] ?? ''; ?></span></td>
            </tr>

    <?php } ?>
<tr>
            <td valign="top" align="right"><span class="copy"><strong>Borough:</strong></span></td>
<td valign="top"><span class="copy"><?php echo $comrow["borough"] ?? ''; ?></span></td>
            </tr>

            </table>
<?php } ?>
<br><br><br><br><br><br><br><br>

<!--end center content-->

<?php include "ssi/footer.php" ; ?>

<!--end footer-->
</span>
</td>
<td valign="top" width="15"><img src="../images/dotclear.gif" width="10"></td>
</tr>
</table>
<br><br>
</div>
</body>
</html>
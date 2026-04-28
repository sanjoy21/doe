<?php

$nologinrequired = $nologinrequired ?? true;

include "mysql.php";

// --- Section 1: Email Trainer Names for Classes in 3 Days (Confirmation Alert) ---

// Calculate date for 3 days from now
$cldate = mktime( 0,0,0,date( "m" ),date( "d" )+3,date( "Y" ) );

$sql = ( "select * from class where confirmdate > '0000-00-00' and confirmdate is not null and deleted = 0 and isnational = 0 and date( startdate ) = '".date( "Y-m-d", $cldate ) . "' " );

$classes = db_query_rows( "$sql", "id" );
$trainers = array(); // This variable is set but not used in the original loop, retained for completeness
foreach( $classes as $cid => $c )
{
    // Assumes sendTrainerNamesEmail(class_id) exists and handles email logic
    sendTrainerNamesEmail( $cid );
}


// --- Section 2: Expiring Service Calls Alert (30 Days Out) ---

// Calculate date for 30 days from now
$cldate = mktime( 0,0,0,date( "m" ),date( "d" )+30,date( "Y" ) );

$sql = ( "select company_esi.* from servicecall, company_esi where company_esi.id = companyid and iscorp = 1 and deleted = 0 and date( nextservicecalldate ) = '".date( "Y-m-d", $cldate ) . "' " );

$companies = db_query_rows( "$sql", "id" );
if( count( $companies ) )
{
    $body  = "These companies have service calls expiring on " . date( "m/d/Y", $cldate ) . ": <br>\n\n";
    foreach( $companies as $cid => $c )
    {
        // Use quoted array keys
        $body .= "<a href='https://".SUB_DOE.".". URL_WITHOUT_SUBDOMAIN . "/viewcompany.php?id=".$c['id']."'>".$c['companyname']."</a><br>\n";
    }

    // Assumes PHPMailer classes are available via require_once
    require_once "class.phpmailer.php";

    $mail = new PHPMailer();
    $mail->From = "info@emergencyskills.com";
    $mail->FromName = "ESI Database";
                
    $mail->Subject = "Expiring Service Calls";
    $mail->IsHTML(true);                                    // set email format to HTML
    $mail->Body    = $body;
    
    $mail->AddAddress("michael@emergencyskills.com");
    // $mail->AddBCC("rachel@vireo.org"); // Original line commented out
    $mail->Send();
}


// --- Section 3: Unconfirmed Trainer Classes Alert (Assigned 2 Days Ago) ---

// Calculate date for 2 days ago
$cldate = mktime( 0,0,0,date( "m" ),date( "d" )-2,date( "Y" ) );

// Assumes TRAININGSITES is a defined constant
$trainingsites_val = defined('TRAININGSITES') ? TRAININGSITES : 0; 
$sql = ( "select classid, trainerid from trainer_to_class, company_esi c where c.id = classid and lastmodified = '".date( "Y-m-d", $cldate )."' and trainerconfirmeddate is null and iscorp <> ". $trainingsites_val );

$unconfirmed_assignments = db_query_rows( "$sql", "id" );
if( count( $unconfirmed_assignments ) )
{
    $body  = "These trainers have not confirmed their classes though they were assigned on or after " . date( "m/d/Y", $cldate ) . ": <br>\n\n";
    foreach( $unconfirmed_assignments as $cid => $c )
    {
        // Use quoted array keys and concatenate URL parts safely
        $body .= "<a href='https://".SUB_DOE.".". URL_WITHOUT_SUBDOMAIN ."/trainer_profile.php?id=".$c['trainerid']."'>".getUserName( $c['trainerid'] )."</a> for class \n";
        $body .= "<a href='https://".SUB_DOE.".". URL_WITHOUT_SUBDOMAIN ."/class_detail.php?id=".$c['classid']."'># ".$c['classid']."</a>  <br>\n";
    }
    
    // Assumes PHPMailer classes are available via require_once
    require_once "class.phpmailer.php";

    $mail = new PHPMailer();
    $mail->From = "info@emergencyskills.com";
    $mail->FromName = "ESI Database";
                
    $mail->Subject = "Trainers who have not confirmed";
    $mail->IsHTML(true);                                    // set email format to HTML
    $mail->Body    = $body;
    
    $mail->AddAddress("barbara@emergencyskills.com");
    // $mail->AddBCC("rachel@vireo.org"); // Original line commented out
    $mail->Send();
}
?>
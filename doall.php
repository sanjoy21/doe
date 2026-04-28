<?php
include "mysql.php";
require_once('services.php');

$rows = db_query_rows( "select r.* from responders_esi r, company_esi c 
                       where c.deleted = 0 
                         and c.showsondrillreports = 1 
                         and c.iscorp = 0 
                         and clientid = c.id 
                         and r.deleted = 0 
                         and pmsidinactive = 0 
                         and pmsid > '' 
                         and lastpmsvalidated < '2020-09-13' 
                         and lastupdateresult <> 'Success' 
                       limit 1000" );

foreach( $rows as $r )
{
    $responder_id = $r["responderid"] ?? '';
    $last_name = $r["lastname"] ?? '';
    $pms_id = $r["pmsid"] ?? '';
    
    echo( "doing $responder_id, $last_name, $pms_id<br>" );
    
    // Call external service to validate the employee's PMS ID
    $pmsidvalidated = validateEmployee( $pms_id, $last_name );
    
    $extpms = "pmsidvalidated = '$pmsidvalidated', lastpmsvalidated = now()";
    
    // Log and execute the database update
    echo( "update responders_esi set $extpms where responderid = $responder_id<Br>" );
    db_query( "update responders_esi set $extpms where responderid = $responder_id" );
    
    // Update responder information (assuming this function handles the array $r)
    updateResponder( $r );
}
?>
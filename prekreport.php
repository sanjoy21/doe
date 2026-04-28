<?php
include "mysql.php";

header('Content-type: application/ms-excel');
header('Content-Disposition: attachment; filename=prek.csv');

echo( "School,Upcoming Classes,Current Responders\n " );

$res = db_query_rows( "select * from company_esi where region like '%PREK' " );

if( isset($res) && is_array($res) )
{
    foreach( $res as $row )
    {
        if( isset($row['id']) && isset($row['schoolcode']) && isset($row['companyname']) )
        {
            $school_id = isset($row['id']) ? intval($row['id']) : 0;
            $schoolcode = isset($row['schoolcode']) ? $row['schoolcode'] : '';
            $companyname = isset($row['companyname']) ? $row['companyname'] : '';
            
            echo( "\"" . $school_id . " - " . $schoolcode . " - " . str_replace('"', '""', $companyname) . "\",\"" );
            
            // Upcoming classes
            $resp = db_query_rows( "select r.*, classid, startdate from responder_to_class rtc, responders_esi r, class c where c.id = classid and r.responderid = rtc.responderid and r.clientid = " . $school_id . " and startdate > now()" );
            
            if( isset($resp) && is_array($resp) )
            {
                foreach( $resp as $r )
                {
                    $firstname = isset($r['firstname']) ? $r['firstname'] : '';
                    $lastname = isset($r['lastname']) ? $r['lastname'] : '';
                    $classid = isset($r['classid']) ? $r['classid'] : '';
                    $startdate = isset($r['startdate']) ? $r['startdate'] : '';
                    
                    echo( str_replace('"', '""', $firstname . " " . $lastname) . " - " . $classid . " - " . $startdate . "\n" );
                }
            }
            echo("\"," );
            
            // Current responders
            $resp = db_query_rows( "select r.*, rtc.trainingdate, classid from responder_training_dates rtc, responders_esi r, class c where c.id = classid and r.responderid = rtc.responderid and r.clientid = " . $school_id . " and rtc.trainingdate > '2013-11-01'" );
            
            echo( "\"" );
            if( isset($resp) && is_array($resp) )
            {
                foreach( $resp as $r )
                {
                    $firstname = isset($r['firstname']) ? $r['firstname'] : '';
                    $lastname = isset($r['lastname']) ? $r['lastname'] : '';
                    $classid = isset($r['classid']) ? $r['classid'] : '';
                    $trainingdate = isset($r['trainingdate']) ? $r['trainingdate'] : '';
                    
                    echo( str_replace('"', '""', $firstname . " " . $lastname) . " - " . $classid . " - " . $trainingdate . "\n" );
                }
            }
            echo("\"\n" );
        }
    }
}
?>
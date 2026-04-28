<?php 
require_once "mysql.php";

$noheader = 1;
$comids = $comids ?? null;
$aedid = $aedid ?? null;
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
    <title></title>
    <meta http-equiv="content-type" content="text/html;charset=UTF-8" />
    
   <STYLE type="text/css">
     td {font-family: arial; font-size: 11px; color: #000000; height: 23px;}
     td.rowA1 {border-top: 1px solid #83afcc; border-right: 1px solid #83afcc;border-bottom: 1px solid #83afcc; border-left: 1px solid #83afcc; padding: 3px;}
     td.rowA2 {border-top: 1px solid #83afcc; border-right: 1px solid #83afcc;border-bottom: 1px solid #83afcc; padding: 3px;}
     td.rowB1 {border-right: 1px solid #83afcc;border-bottom: 1px solid #83afcc; border-left: 1px solid #83afcc; padding: 3px;}
     td.rowB2 {border-right: 1px solid #83afcc;border-bottom: 1px solid #83afcc; padding: 3px;}
     td.rowAB1 {border-right: 1px solid #83afcc;border-top: 1px solid #83afcc; border-bottom: 1px solid #83afcc; border-left: 1px solid #83afcc; padding: 3px;}
     td.rowAB2 {border-right: 1px solid #83afcc;border-top: 1px solid #83afcc; border-bottom: 1px solid #83afcc; padding: 3px;}
     .fontBig {font-size: 24px; font-weight: bold;}
     .fontMed {font-size: 18px; font-weight: bold;}
     P.breakhere {page-break-before: always}
     
   </STYLE>

</head>
<body>
<?php

if( $comids && is_string($comids) )
{
    $comids = explode( ",", $comids );
}

// Ensure $comids is an array before iterating
if (is_array($comids)) {
    foreach( $comids as $companyid )
    {
        // Safety: Cast companyid to integer for SQL use
        $safe_companyid = (int)$companyid;
        $safe_aedid = (int)$aedid;
        
        // --- 1. Find newly installed AEDs for this company ---
        $q = db_query_rows("SELECT * FROM aed_esi WHERE clientid = '{$safe_companyid}' AND deleted = 0 AND newinstall = 1" );
        
        if( count( $q ) > 0 )
        {
            // --- 2. Insert new Service Call record ---
            $sql_insert_sc = "INSERT INTO servicecall ( companyid, singleaedid, newinstall ) 
                              VALUES ( '{$safe_companyid}', '{$safe_aedid}', 1 )";
            $newscid = db_query_insert_id( $sql_insert_sc );
            
            // --- 3. Link the new AEDs to the new Service Call ---
            if ($newscid) {
                foreach( $q as $qrow )
                {
                    // PHP 8.2 Fix: Quote array key 'serial' and escape for SQL
                    $safe_serial = mysqli_real_escape_string($GLOBALS['link'] ?? $link, $qrow['serial'] ?? '');
                    
                    if ($safe_serial) {
                        $sql_insert_link = "INSERT INTO aed_to_servicecall ( serial, servicecallid ) 
                                            VALUES ( '{$safe_serial}', '{$newscid}' )";
                        db_query( $sql_insert_link );
                    }
                }
            }
        }
        
        // --- 4. Generate the Service Call Sheet for this company ---
        // These variables are used by the included file
        $servicecallid = $newscid ?? null;
        $showallaeds = 1;
        include "servicecallsheet.php";
        
        $showallaeds = 0;
        echo "<p class='breakhere'></p>";
    }
} else {
    echo "<div>Error: No company IDs provided or format is invalid.</div>";
}
?>
</body>
</html>
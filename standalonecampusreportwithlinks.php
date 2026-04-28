<?php

include "mysql.php";

// Initialize external variables if they are not set, assuming default values
$xls = $xls ?? false;
$minnum = $minnum ?? "0"; // Used in the HTML output block

// Query to group client IDs by campus ID, filtering out charters, 84- schools, and corporate entities
$sql_query = "select group_concat( id ) as id, campusid 
              from company_esi c 
              where companyname not like '%charter%' 
              and schoolcode not like '84-%' 
              and campusid > 0 
              and iscorp = 0 
              and deleted = 0 
              group by campusid";

$buildings = db_query_array( $sql_query, "campusid", "id" );

// --- CSV Output Section ---
if( $xls )
{
    // Set headers for CSV download
    $filename = "expired" . time() . ".csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // Add UTF-8 BOM for Excel compatibility
    fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
    
    // Write headers
    $headers = array(
        "Campus ID",
        "Campus Name"
    );
    fputcsv($output, $headers);
    
    foreach( $buildings as $b => $cid )
    {
        // SECURITY FIX: Validate $b is an integer
        $campus_id = (int)$b;
        
        // SECURITY FIX: Validate and sanitize $cid which contains comma-separated IDs
        $company_ids = explode(",", $cid);
        $valid_company_ids = array();
        foreach ($company_ids as $company_id) {
            $clean_id = (int)trim($company_id);
            if ($clean_id > 0) {
                $valid_company_ids[] = $clean_id;
            }
        }
        
        if (empty($valid_company_ids)) {
            continue; // Skip if no valid company IDs
        }
        
        $cid_safe = implode(",", $valid_company_ids);
        
        // Count responders with training after a certain date (2018-12-01)
        $num_sql = "select count(*) from responders_esi r, responder_training_dates rtd 
                    where r.responderid = rtd.responderid 
                    and clientid in ( $cid_safe ) 
                    and rtd.trainingdate > '2018-12-01'";
        $num = db_query_first_cell( $num_sql ) ?? 0;
        
        // Get the campus name with integer casting
        $name = db_query_first_cell( "select name from campus where id = $campus_id" ) ?? '';
        
        if( !$name ) continue; // Skip if no campus name found
        if( $num ) continue;   // Skip if training was found (original logic: report campuses *without* recent training)
        
        // Prepare row data
        $row_data = array(
            (string)$campus_id,
            $name
        );
        
        fputcsv($output, $row_data);
    }
    
    fclose($output);
    exit;
}
// --- HTML Output Section ---
else
{
    $i = 1;
    echo( "<table border=1>" );
    
    echo( "<tr><td></td><td>Campus ID</td><td>Campus Name</td>" );
    echo( "</tr>" );

    foreach( $buildings as $b => $cid )
    {
        // $cid is a comma-separated list of company IDs belonging to the campus $b
        $onecid = array_pop( explode( ",", $cid ) );
        
        // Count responders with training after a certain date (2018-12-01)
        $num_sql = "select count(*) from responders_esi r, responder_training_dates rtd 
                    where r.responderid = rtd.responderid 
                    and clientid in ( $cid ) 
                    and rtd.trainingdate > '2018-12-01'";
        $num = db_query_first_cell( $num_sql );
        
        // Get the campus name
        $name = db_query_first_cell( "select name from campus where id = $b" );
        
        if( !$name ) continue; // Skip if no campus name found
        if( $num ) continue;   // Skip if training was found (original logic: report campuses *without* recent training)

        echo( "<tr>" );
        echo( "<td>". ($i++) . "</td>" );
        echo( "<td>". $b . "</td>" );
        // Use urlencode for the campus name in the link
        echo( "<td><a target=_blank href='campuses.php?search=1&substr=" . urlencode( $name ) . "'>" . $name . "</a></td>" );
        echo( "</tr>" );
    }

    echo( "</table>" );
}
?>
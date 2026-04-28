<?php 
$nologinrequired=true;
include "mysql.php";

// Safely access the assumed global class names array
// Assuming $allclass_names is structured as $allclass_names[0][code] => name
$allclass_names = $allclass_names ?? [];

// Get the database connection link for escaping strings
$db_link = $GLOBALS['link'] ?? $link; 

// --- 1. Get list of non-corporate schools to report on ---
$schools = db_query_rows( "SELECT 
                            id, schoolcode, companyname 
                          FROM 
                            company_esi 
                          WHERE 
                            deleted = 0 
                            AND iscorp = 0 
                            AND id NOT IN ( 2810, 2858 ) 
                          ORDER BY 
                            companyname" );

// --- 2. Open CSV file for writing ---
$filename = "reports/allresponders.csv";
$h = fopen( $filename, "w+" );

// Write CSV Header
fputcsv( $h, ["Company Name", "School Code", "First Name", "Last Name", "Expiration Date"] );

// --- 3. Loop through schools and then their responders ---
foreach( $schools as $s )
{
    $safe_clientid = (int)$s['id'];
    
    // Get all active responders for this school
    $responders = db_query_rows( "SELECT 
                                    responderid, firstname, lastname 
                                  FROM 
                                    responders_esi 
                                  WHERE 
                                    clientid = {$safe_clientid} 
                                    AND deleted = 0 
                                  ORDER BY 
                                    lastname, firstname" ); 
    
    foreach( $responders as $r )
    {
        $safe_responderid = (int)$r['responderid'];
        
        // Find the last non-DD training date for the responder
        $exp = db_query_first( "SELECT 
                                    rtd.program, class.code, rtd.trainingdate 
                                  FROM 
                                    responder_training_dates rtd 
                                  LEFT JOIN 
                                    class ON class.id = rtd.classid 
                                  WHERE 
                                    rtd.responderid = {$safe_responderid} 
                                    AND (class.code IS NULL OR class.code <> 'dd') 
                                  ORDER BY 
                                    rtd.trainingdate DESC 
                                  LIMIT 1" );
        
        $tdate = "";
        $trainingdate_val = $exp['trainingdate'] ?? null;
        
        if( $trainingdate_val )
        {
            $tdatetmp = strtotime($trainingdate_val);
            
            // Calculate expiration date (2 years after training date)
            // Note: mktime is used to avoid issues with date() and day rollover in edge cases
            $expiration_timestamp = mktime( 
                0, 0, 0, 
                date( "m", $tdatetmp ), 
                date( "d", $tdatetmp ), 
                date( "Y", $tdatetmp) + 2 
            );
            
            // Check if training is expired (expiration timestamp is less than current time)
            if( $expiration_timestamp < time() ) {
                continue; // Skip expired responders
            }
            
            $tdate = date( "m/d/Y", $expiration_timestamp );
        }
        else
        {
            continue; // Skip responders with no valid training date found
        }

        // --- Determine Program/Class Code Name ---
        $code = "";
        $exp_code = $exp['code'] ?? null;
        $exp_program = $exp['program'] ?? null;
        
        if( $exp_code )
        {
            $code = $allclass_names[0][$exp_code] ?? $exp_code;
        }
        else if( $exp_program )
        {
            if( $exp_program == "dd" ) {
                continue; // Should have been caught by SQL, but an extra check for safety
            }
            
            if ( isset($allclass_names[0][$exp_program]) ) {
                $code = $allclass_names[0][$exp_program];
            } else {
                $code = $exp_program;
            }
        }
        
        // --- Write Row to CSV ---
        $tmparr = array();
        $tmparr[] = $s['companyname'];
        $tmparr[] = $s['schoolcode'];
        $tmparr[] = $r['firstname'];
        $tmparr[] = $r['lastname'];
        // $tmparr[] = $r['filenumber']; // Original line commented out
        // $tmparr[] = $code; // Original line commented out
        $tmparr[] = $tdate;
        
        fputcsv( $h, $tmparr ) ;
    }
}

// --- 4. Close File and Display Link ---
fclose( $h );

?>
<a href='<?= htmlspecialchars($filename) ?>'>download the file here</a>
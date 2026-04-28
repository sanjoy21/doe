<?php 
require "mysql.php"; 

// File path
$log_file_path = "/home/esi/whatever.txt";

// Check if the file exists and is readable
if (!file_exists($log_file_path) || !is_readable($log_file_path)) {
    echo "Error: Log file not found or not readable at {$log_file_path}";
    exit;
}

// Read the log file into an array of lines
$scan = file( $log_file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );

echo( "<table>" );
echo( "<tr><th>Email</th><th>Class ID</th><th>Status</th></tr>" ); // Add a header row

$already = array(); // Used to track and prevent duplicate processing of email/class pairs

foreach( $scan as $line )
{
    $line = trim($line); // Clean up whitespace
    
    // Skip lines indicating a completed job
 if( strpos( $line, "done sending" ) !== false ) {
     continue;
    }
    
    // Attempt to parse the line
 $exp = explode( " ", $line );

    // Heuristic 1: Email is the first element, potentially followed by a comma
 $email = $exp[0] ?? '';
 $email = str_replace( ",", "", $email ); 
    $email_safe = trim(strtolower($email)); // Sanitize email for comparison and query
    
    // Heuristic 2: Find "added" to locate class ID two elements later
 $added = array_search( "added", $exp );
    $class = null;

    if ($added !== false) {
        $classidcol = $added + 2;
        $class = $exp[$classidcol] ?? null;
    }

    $class_id_safe = (int)$class; // Ensure class ID is an integer

    // Skip if email or class ID could not be determined
    if (empty($email_safe) || $class_id_safe === 0) {
        // echo "<tr><td colspan='3' style='color:orange;'>Skipped malformed line: " . htmlspecialchars($line) . "</td></tr>";
        continue;
    }
    
    $key = "{$email_safe}-{$class_id_safe}";
    
    // Skip if this email/class pair has already been processed in this run
 if( isset( $already[$key] ) ) continue;
 $already[$key] = 1;
    
    // Prepare output row with safe data
    $email_output = htmlspecialchars($email_safe);
    $class_output = htmlspecialchars($class_id_safe);
echo( "<tr><td>{$email_output}</td><td>{$class_output}</td>" );
    
    // --- Database Lookup ---
    
    // Look up the responder by class ID and email
    $sql_lookup = "SELECT 
                        r.responderid, r.individual 
                    FROM 
                        responder_to_class rtc, responders_esi r 
                    WHERE 
                        rtc.responderid = r.responderid 
                        AND rtc.classid = {$class_id_safe} 
                        AND r.email = '" . mysqli_real_escape_string($db_link, $email_safe) . "'";
                        
 $res = db_query_first( $sql_lookup );
    
 if( $res )
 {
        $responder_id = (int)($res['responderid'] ?? 0);
        $individual_status = (int)($res['individual'] ?? 0);
        
        $status_color = 'green';
        $status_text = "{$responder_id} - {$individual_status}"; // Display current individual status
        
        // --- Database Update ---
        if ($responder_id > 0) {
     db_query( "UPDATE responder_to_class SET individual = 1 WHERE responderid = {$responder_id} AND classid = {$class_id_safe}" );
        }
        
echo( "<td><font color='{$status_color}'>{$status_text} (Updated)</font></td>" );
}
 else
 {
 echo( "<td><font color='red'>not found</font></td>" );
}
    
echo( "</tr>" );
} 

echo( "</table>" );
?>
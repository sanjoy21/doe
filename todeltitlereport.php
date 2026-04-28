<?php
$nologinrequired = true;
include "mysql.php" ;

// --- 1. File Handling ---
$filename = "sampletitles.csv";
// Check if file handle is valid after opening
$h = @fopen( $filename, "w" );
if (!$h) {
    die("Error: Could not open file for writing: " . $filename);
}

// --- 2. Database Query ---
$sql = "SELECT 
            re.title, 
            COUNT(*) AS cnt 
        FROM 
            responders_esi re, 
            responder_training_dates rtd, 
            company_esi c 
        WHERE 
            re.clientid = c.id 
            AND c.iscorp = 0 
            AND rtd.trainingdate > '2021-07-01' 
            AND rtd.trainingdate < '2024-07-01' 
            AND rtd.responderid = re.responderid 
        GROUP BY 
            re.title";

$schools = db_query_rows($sql);

// --- 3. HTML Output ---
echo( "<table border='1' cellspacing='0' cellpadding='2'><tr><th>Title</th><th>Count</th></tr>" );

// Write CSV headers (Good practice, missing in original)
fputcsv( $h, array('Title', 'Count') );

// --- 4. Loop and Process ---
foreach( $schools as $r )
{
    // PHP 8.2 Fix/Security: Quote array keys and use htmlspecialchars() for HTML output
    $title = htmlspecialchars($r['title'] ?? 'N/A');
    $count = htmlspecialchars($r['cnt'] ?? 0);

    echo( "<tr><td>" . $title . "</td><td>" . $count . "</td></tr>" );
    
    // Prepare data for CSV
    $arr = array( $r['title'], $r['cnt'] ); 
    fputcsv( $h, $arr );
}

echo( "</table>" );
echo( "<a href='" . htmlspecialchars($filename) . "'>Download Here</a>" );

// --- 5. Cleanup and Exit ---
fclose( $h );
exit;
?>
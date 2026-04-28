<?php
require_once('mysql.php');

$classes = db_query_rows( "SELECT companyid, id, confirmationnotes FROM class WHERE startdate > NOW()" );

foreach ( $classes as $crow )
{
    $new = "";
    $ins = $crow['id'] ?? null;
    $companyid = $crow['companyid'] ?? null;
    $cnotes_original = $crow['confirmationnotes'] ?? '';

    // Sanitize variables for use in SQL where they are *not* the primary parameter
    // Assuming db_query_first_cell/db_query handles basic connection or has a global link
    $safe_companyid = is_numeric($companyid) ? $companyid : 0;
    
    // --- 2. Check for FRX AED ---
    // PHP 8.2 Fix: Use proper string interpolation for the ID in the subquery
    $hasfrx = db_query_first_cell( "SELECT aedid FROM aed_esi WHERE clientid = '{$safe_companyid}' AND model = 'FRX'" );
    
    // --- 3. Update Notes if FRX Found ---
    if( $hasfrx )
    {
        // Construct the new note string
        $note_suffix = "\nThis school has an FRX";
        $cnotes_new = $cnotes_original . $note_suffix;
        
        // Sanitize the concatenated string for safe database insertion
        // NOTE: You must replace 'mysqli_real_escape_string' with your actual database escaping function
        // if your 'mysql.php' uses a different method (e.g., PDO parameter binding is best).
        $cnotes_escaped = mysqli_real_escape_string($link ?? $GLOBALS['link'], $cnotes_new);
        
        // Prepare the final UPDATE query
        $update_query = "UPDATE class SET confirmationnotes = '{$cnotes_escaped}' WHERE id = {$ins}";

        // Echo the query (original behavior)
        echo( htmlspecialchars($update_query) . "<br>" );
        
        // Execute the query (original behavior, commented out)
        // db_query( $update_query );
    }
    // else block is preserved but empty as in the original code
    // else
    // {
    //    // echo( "non1" );
    // }
}
?>
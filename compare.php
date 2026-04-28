<?php
include "mysql.php";
$handle = fopen("/tmp/schools.csv", "r");
$i = 0;

// This helper function seems to be defined outside the loop
function string_explode($str, $nr){
    // NOTE: 'chunk_explode' is an undefined function in standard PHP. 
    // Assuming it's defined elsewhere or is a typo for a custom function.
    // If chunk_explode is not available, this will cause a fatal error.
    return explode("-l-", chunk_split($str, $nr, '-l-')); 
}

while (($data = fgetcsv($handle, 9999, ",")) !== FALSE) { // && !$done
    $i++;
    if( $i == 1 )
    continue; // Skip the header row
    
    // Process the second data column
    // Safely use the null coalescing operator in case $data[1] is missing or null
    $two = string_explode( $data[1] ?? '', 1 ); 
    
    // Get the first element (likely the district)
    $dis = array_shift( $two ); 
    
    // Construct the school code (assuming $data[0] is the Borough/ID part)
    // Safely use the null coalescing operator in case $data[0] is missing or null
    $schoolcode = str_pad( $data[0] ?? '', 2, "0", STR_PAD_LEFT ) . "-".$dis . "-" . join( $two );
    
    // Check if the school code exists in the database
    $res = db_query_first_cell( "select count(*) from company_esi where schoolcode = '$schoolcode'" );
    
    if( !$res )
    {
        echo( "$i. no match in the db for: " . $schoolcode . "<br>" );
    }
}
?>
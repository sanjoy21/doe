<?php 
include "mysql.php";
$handle = fopen("/tmp/all.csv", "r");
$i = 0;

function string_explode($str, $nr){
    // NOTE: 'chunk_explode' is an undefined function in standard PHP. 
    // Assuming it's defined elsewhere or is a typo for a custom function.
    return explode("-l-", chunk_split($str, $nr, '-l-'));
}

while (($data = fgetcsv($handle, 9999, ",")) !== FALSE) { // && !$done
    $i++;
    if( $i == 1 )
    continue; // Skip header
    
    // Safely access array elements
    $two = string_explode( $data[1] ?? '', 1 );
    $dis = array_shift( $two );
    
    // Construct the school code (safely accessing $data[0])
    $schoolcode = str_pad( $data[0] ?? '', 2, "0", STR_PAD_LEFT ) . "-".$dis . "-" . join( $two );
    
    // Check if the school code exists in the database
    $res = db_query_first_cell( "select count(*) from company_esi where schoolcode = '$schoolcode'" );
    
    if( !$res )
    {
        echo( "$i. no match in the db for: " . $schoolcode . "<br>" );
    }
}
?>
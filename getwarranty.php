<?php 
$nologinrequired = true;
include "mysql.php";

// Safely access external variables
$serial_safe = $serial ?? null;
$thedate_safe = $thedate ?? null; // Use null coalescing for $thedate as it might be passed externally

if( $serial_safe && is_string($serial_safe) && strlen($serial_safe) >= 4 )
{
    // Extract year (20XX) from the 2nd and 3rd characters (indices 1 and 2)
    $year = "20" . $serial_safe[1] . $serial_safe[2] . "-";
    
    // Extract month code (index 3) and convert to uppercase
    $month_code = strtoupper( $serial_safe[3] );
    
    switch ( $month_code ) {
        case "A":
        case "B":
        case "C":
        case "D":
            // Note: The original code maps A, B, C, D all to '01' (January).
             $year .= "01";
             break;
        case "E":
             $year .= "05";
             break;
        case "F":
             $year .= "06";
             break;
        case "G":
             $year .= "07";
             break;
        case "H":
             $year .= "08";
             break;
        case "I":
             $year .= "09";
             break;
        case "J":
             $year .= "10";
             break;
        case "K":
             $year .= "11";
             break;
        case "L":
             $year .= "12";
             break;
        default:
             // Handle case where month code is invalid or not mapped
             $year = null; 
             break;
    }
    
    if ($year !== null) {
        $year .= "-01"; // Set day to the 1st
        $thedate_safe = $year;
    }
}

// Calculate the date 8 years later
if ($thedate_safe) {
    // Use strtotime to add 8 years to the calculated or external date
    $dt = strtotime( $thedate_safe . " + 8 years" ); 
    
    // Output the resulting date in YYYY-MM-DD format
    echo( date( "Y-m-d", $dt ) );
}

?>
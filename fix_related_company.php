<?php 
// include "mysql.php";

// // Fetch responders missing a trainingdate but having a valid programstartdate
// $sql_select = "
//     SELECT * FROM responders_esi 
//     WHERE (trainingdate IS NULL OR trainingdate = '' OR trainingdate = '0000-00-00') 
//       AND programstartdate > '1999-01-01'
// ";
// $r = db_query_rows( $sql_select );

// foreach( $r as $row )
// {
//     // Safely access quoted array keys
//     $responderid_safe = $row["responderid"] ?? 0;
//     $programstartdate_safe = $row["programstartdate"] ?? '';
//     $program_safe = $row["program"] ?? '';
    
//     // Check if a training date record already exists for this responder and this specific program start date
//     $otherrows = db_query_rows( "
//         SELECT * FROM responder_training_dates 
//         WHERE responderid = '" . (int)$responderid_safe . "' 
//           AND trainingdate = '" . $programstartdate_safe . "'" 
//     );
    
//     // If no existing record is found, insert a new one
//     if( !count( $otherrows ) )
//     {
//         $sql_insert = "
//             INSERT INTO responder_training_dates (responderid, trainingdate, program) 
//             VALUES (
//                 '" . (int)$responderid_safe . "', 
//                 '" . $programstartdate_safe . "', 
//                 '" . $program_safe . "' 
//             )";
            
//         // The original echo was commented out, so we execute the query
//         // echo( $sql_insert . "<br>" ); 
//         db_query( $sql_insert );
//     }
// }
?>
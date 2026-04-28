<?php
require_once('mysql.php');

// $cids = db_query_array( "select id, code from class", "id", "code" );
// foreach( $cids as $c=>$code )
// {
//     db_query( "update responder_training_dates set program = '$code' where classid ='$c' and program is null" );
// }
// exit;


// --- 1. Main Query: Select Responders with 'dd' program training at non-corporate clients ---
$sql = "SELECT DISTINCT
            r.responderid,
            r.firstname,
            r.lastname,
            r.email,
            c.companyname,
            c.schoolcode
        FROM
            responders_esi r,
            responder_training_dates rtd,
            company_esi c
        WHERE
            rtd.responderid = r.responderid
            AND r.deleted = 0
            AND c.id = r.clientid
            AND c.iscorp = 0
            AND rtd.program = 'dd'
        ORDER BY
            r.lastname, r.firstname";

// Fetch the initial list of responders
$result = db_query_rows( $sql );

// --- 2. CSV Report Generation Setup ---
$filename = "coaches_report_" . time() . ".csv";

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Open output stream
$output = fopen('php://output', 'w');

// Write UTF-8 BOM for Excel compatibility
fwrite($output, "\xEF\xBB\xBF");

// --- Write Header Row ---
$header = [
    "Last Name",
    "First Name",
    "Email",
    "School",
    "School Code",
    "6 hour dates",
    "2 hour dates",
    "Upcoming Classes" // Note: This will be a single column with multiple values
];

fputcsv($output, $header);

// --- 3. Iterate through Responders and write detailed data ---
if (is_array($result)) {
    foreach( $result as $row )
    {
        $responderid = $row['responderid'] ?? 0;
        
        // --- Fetch 6-hour Training Dates ('reg') ---
        $sql_6hr = "SELECT trainingdate FROM responder_training_dates 
                    WHERE exported = 1 AND responderid = {$responderid} AND program = 'reg' 
                    ORDER BY trainingdate";
        $sixhour = db_query_array( $sql_6hr, "trainingdate", "trainingdate" );
        
        // --- Fetch 2-hour Training Dates ('dd') ---
        $sql_2hr = "SELECT trainingdate FROM responder_training_dates 
                    WHERE exported = 1 AND responderid = {$responderid} AND program = 'dd' 
                    ORDER BY trainingdate";
        $twohour = db_query_array( $sql_2hr, "trainingdate", "trainingdate" );
        
        // --- Fetch Upcoming Classes ---
        $sql_upcoming = "SELECT class.startdate AS startdate, classid 
                        FROM responder_to_class, class 
                        WHERE class.startdate > NOW() 
                        AND responderid = {$responderid} 
                        AND class.id = classid";
        $upcoming = db_query_array( $sql_upcoming, "startdate", "classid" );
        
        // Format upcoming classes into a single string
        $upcomingClasses = '';
        if( count( $upcoming ) ) {
            $upcomingArray = [];
            foreach( $upcoming as $u => $clid ) {
                $formatted_date = date( "Y-m-d", strtotime( $u ));
                $upcomingArray[] = "{$formatted_date}({$clid})";
            }
            $upcomingClasses = implode( ", ", $upcomingArray );
        }
        
        // Prepare data row
        $rowData = [
            $row['lastname'] ?? '',
            $row['firstname'] ?? '',
            $row['email'] ?? '',
            $row['companyname'] ?? '',
            $row['schoolcode'] ?? '',
            join( ", ", $sixhour ),
            join( ", ", $twohour ),
            $upcomingClasses
        ];
        
        // Escape any formulas that might start with =, +, - or @ to prevent CSV injection
        foreach($rowData as &$value) {
            if($value !== null && $value !== '') {
                $firstChar = substr($value, 0, 1);
                if(in_array($firstChar, array('=', '+', '-', '@'))) {
                    $value = "'" . $value;
                }
            }
        }
        
        fputcsv($output, $rowData);
    }
}

// --- 4. Finalize Report ---
fclose($output);
exit();

?>
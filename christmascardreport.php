<?php 
include "mysql.php";

$dt = date( "Y" ) - 1;

// Set headers for CSV download
$filename = "christmasreport.csv";

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

// Open output stream
$output = fopen('php://output', 'w');

// Write CSV headers
fputcsv($output, [
    'Name',
    'Company', 
    'Address',
    'City',
    'State',
    'Zip'
]);

$sql = "SELECT * FROM company_esi 
        WHERE iscorp = 1 
        AND id IN ( SELECT companyid FROM class WHERE startdate > '{$dt}-12-01' )";

$rep = db_query_rows( $sql, "id" );

$already = array();

foreach( $rep as $r )
{
    // Define an array of contact fields to loop through
    $contact_fields = [
        'contactname', 
        'contact2name', 
        'contact3name'
    ];

    // Safely extract address details once per company
    $companyname = $r['companyname'] ?? '';
    $address = $r['address'] ?? '';
    $city = $r['city'] ?? '';
    $state = $r['state'] ?? '';
    $zip = $r['zip'] ?? '';

    // Loop through all three potential contacts
    foreach ($contact_fields as $field) {
        $contact_name = $r[$field] ?? '';

        // Check if the contact name is non-empty and has not been added yet
        if( !empty($contact_name) && !($already[$contact_name] ?? false) ) 
        {
            // Write data row to CSV
            fputcsv($output, [
                $contact_name,
                $companyname,
                $address,
                $city,
                $state,
                $zip
            ]);
            
            // Mark contact as added
            $already[$contact_name] = 1;
        }
    }
}

// Close output stream
fclose($output);
exit;

?>
<?php
require_once('mysql.php');

// Ensure $link (the mysqli connection object) is available for real_escape_string
global $link;

$handle = fopen("/tmp/d79.csv", "r");
echo( "<table border=1>" );

if ($handle !== false) {
    while (($data = fgetcsv($handle, 9999, ",")) !== FALSE) {
        
        // Safely extract data from the CSV row, defaulting to empty string
        $address_part_raw = $data[9] ?? '';
        $company_name_raw = $data[3] ?? '';

        // Escape the address part for safe use in a LIKE query
        $address_part_safe = $link->real_escape_string($address_part_raw);
        
        $sql = "SELECT id, companyname FROM company_esi 
                WHERE address LIKE '%" . $address_part_safe . "%' 
                AND iscorp = 0 
                ORDER BY deleted LIMIT 1";

        $res = db_query_first($sql);
        
        // Safely access results and use defaults if no match found
        $company_id = $res['id'] ?? null;
        $company_name_db = $res['companyname'] ?? 'No Match Found';

        echo( "<tr>" );
        // Output the original CSV value (column 3)
        echo( "<td>" . htmlspecialchars($company_name_raw) . "</td>" );
        
        // Output the matched company name with a link if found
        if ($company_id) {
            echo( "<td><a target='_blank' href='viewcompany.php?id=" . (int)$company_id . "'>" . htmlspecialchars($company_name_db) . "</a></td>" );
        } else {
            // Output "No Match Found"
            echo( "<td>" . htmlspecialchars($company_name_db) . "</td>" );
        }
        echo( "</tr>" );
    }
    fclose($handle);
} else {
    echo "<tr><td colspan='2'>Error: Could not open /tmp/d79.csv</td></tr>";
}

echo( "</table>" );
?>
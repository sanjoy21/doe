<?php

include "mysql.php"; 

$schools = getNoAEDSchools();

if ($xls) {
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="expired.csv"');
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // Add UTF-8 BOM for Excel compatibility
    fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
    
    // Write Header Row
    $headers = array(
        "School ID",
        "School Code",
        "School Name",
        "School Address",
        "Borough",
        "Contact Name",
        "Contact Email",
        "Contact Phone",
        "Principal Name",
        "Principal Email",
        "Region"
    );
    fputcsv($output, $headers);
    
    foreach ($schools as $sid => $srow) {
        $crow = getCompanyRow($sid); // Fetch full company details
        
        // Build address with null safety
        $address_parts = array();
        if (!empty($crow["address"])) $address_parts[] = $crow["address"];
        if (!empty($crow["city"])) $address_parts[] = $crow["city"];
        if (!empty($crow["zip"])) $address_parts[] = $crow["zip"];
        $address = implode(", ", $address_parts);
        
        // Prepare row data with null safety
        $row_data = array(
            (string)$sid,
            $crow["schoolcode"] ?? '',
            $crow["companyname"] ?? '',
            $address,
            $crow["borough"] ?? '',
            $crow["contactname"] ?? '',
            $crow["contactemail"] ?? '',
            $crow["contactphone"] ?? '',
            $crow["principalname"] ?? '',
            $crow["principalemail"] ?? '',
            $crow["region"] ?? ''
        );
        
        fputcsv($output, $row_data);
    }
    
    fclose($output);
    exit; // Stop execution after file download
}
// --- HTML Display Logic ---
else {
    $i = 1;
    echo "<table border='1'>";
    
    // Display header row
    echo "<tr>
            <th></th>
            <th>School ID</th>
            <th>School Code</th>
            <th>School Name</th>
            <th>School Address</th>
            <th>Borough</th>
            <th>Contact Name</th>
            <th>Contact Email</th>
            <th>Contact Phone</th>
            <th>Principal Name</th>
            <th>Principal Email</th>
            <th>Region</th>
          </tr>";
    
    foreach ($schools as $sid => $srow) {
        $crow = getCompanyRow($sid);
        
        $school_id = (int)$sid;
        $school_code = htmlspecialchars($crow["schoolcode"]);
        $company_name = htmlspecialchars($crow["companyname"]);
        $borough = htmlspecialchars($crow["borough"]);
        $contact_name = htmlspecialchars($crow["contactname"]);
        $contact_email = htmlspecialchars($crow["contactemail"]);
        $contact_phone = htmlspecialchars($crow["contactphone"]);
        $principal_name = htmlspecialchars($crow["principalname"]);
        $principal_email = htmlspecialchars($crow["principalemail"]);
        $region = htmlspecialchars($crow["region"]);
        
        $address = htmlspecialchars(($crow["address"]) . ", " . ($crow["city"]) . ", " . ($crow["zip"]));
        
        echo "<tr>";
        echo "<td>{$i}</td>";
        // Links to view company page
        echo "<td><a href='viewcompany.php?id={$school_id}'>{$school_id}</a></td>";
        echo "<td><a href='viewcompany.php?id={$school_id}'>{$school_code}&nbsp;</a></td>";
        
        // Data columns
        echo "<td>{$company_name}</td>";
        echo "<td>{$address}</td>";
        echo "<td>{$borough}</td>";
        echo "<td>{$contact_name}</td>";
        echo "<td>{$contact_email}</td>";
        echo "<td>{$contact_phone}</td>";
        echo "<td>{$principal_name}</td>";
        echo "<td>{$principal_email}</td>";
        echo "<td>{$region}</td>";
        echo "</tr>";
        
        $i++;
    } 
    
    echo "</table>";
}
?>
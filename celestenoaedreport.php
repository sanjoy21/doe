<?php
include "mysql.php";

// Helper function for XSS mitigation
if (!function_exists('h')) {
    function h($str) {
        return htmlspecialchars((string)($str ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}

$schools = getNoAEDSchools();
$noaedlocations = getNoAEDLocations();

// Input Sanitization
$xls = $_REQUEST['xls'] ?? false; // Assuming $xls comes from $_GET or $_POST

if( $xls )
{
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
        "Date Added",
        "School Code",
        "School Name",
        "School Address",
        "Borough",
        "Contact Name",
        "Contact Email",
        "Contact Phone",
        "Principal Name",
        "Principal Email",
        "Region",
        "Campus"
    );
    fputcsv($output, $headers);
    
    foreach( $schools as $sid=>$crow )
    {
        $campusid = $crow["campusid"] ?? 0;
        // Check if campus has AED locations (skip if it does, unless $noaedlocations is set)
        if( $campusid && !($noaedlocations[$campusid] ?? false) )
        {
            continue;
        }

        // Build address with null safety
        $address_parts = array();
        if (!empty($crow["address"])) $address_parts[] = $crow["address"];
        if (!empty($crow["city"])) $address_parts[] = $crow["city"];
        if (!empty($crow["zip"])) $address_parts[] = $crow["zip"];
        $address = implode(", ", $address_parts);
        
        // Get campus name with null safety
        $campus_name = '';
        if ($campusid) {
            $campus_name = getCampusName($campusid) ?? '';
        }
        
        // Prepare row data with null safety
        $row_data = array(
            (string)$sid,
            $crow['date'] ?? '',
            $crow['schoolcode'] ?? '',
            $crow['companyname'] ?? '',
            $address,
            $crow['borough'] ?? '',
            $crow['contactname'] ?? '',
            $crow['contactemail'] ?? '',
            $crow['contactphone'] ?? '',
            $crow['principalname'] ?? '',
            $crow['principalemail'] ?? '',
            $crow['region'] ?? '',
            $campus_name
        );
        
        fputcsv($output, $row_data);
    } 
    
    fclose($output);
    exit;
}
else
{
    // HTML Output Section
    $i = 1;
    echo( "<table border=1>" );
    
    // Input Sanitization: Ensure $minnum is set and safe before using it, though it's not used in this block.
    $minnum = $_REQUEST['minnum'] ?? "0";
    
    // Header Row - No XSS risk here as content is static
    echo( "<tr><td></td><td>School ID</td><td>Date Added</td><td>School Code</td><td>School Name</td><td>School Address</td><td>Borough</td><td>Contact Name</td><td>Contact Email</td><td>Contact Phone</td><td>Principal Name</td><td>Principal Email</td><td>Region</td><td>Campus</td></tr>" );
    
    foreach( $schools as $sid=>$crow )
    {
        $campusid = $crow["campusid"];
        if( $campusid && !$noaedlocations[$campusid] )
        {
            continue;
        }

        // XSS Mitigation: Sanitize all database output variables
        $sid_safe = h($sid);
        $schoolcode_safe = h($crow['schoolcode']);
        $companyname_safe = h($crow['companyname']);
        
        $address = h($crow["address"]) . ", " . h($crow["city"]) . ", " . h($crow["zip"]);
        $campusname_safe = h(getCampusName( $crow['campusid'] ));
        
        echo( "<tr><td>" . h($i) . "</td>" );
        echo( "<td><a href='viewcompany.php?id={$sid_safe}'>{$sid_safe}</a></td>" );
        echo( "<td>" . h($crow['date']) . "</td>" );
        echo( "<td><a href='viewcompany.php?id={$sid_safe}'>{$schoolcode_safe}&nbsp;</a></td>" );
        echo( "<td>{$companyname_safe}</td><td>{$address}</td><td>" . h($crow['borough']) . "</td><td>" . h($crow['contactname']) . "</td><td>" . h($crow['contactemail']) . "</td><td>" . h($crow['contactphone']) . "</td><td>" . h($crow['principalname']) . "</td><td>" . h($crow['principalemail']) . "</td><td>" . h($crow['region']) . "</td>" );
        echo( "<td>{$campusname_safe}</td>" );
        echo( "</tr>" );
        $i++;
    } 
    ?>
    </table>
    <?php } ?>
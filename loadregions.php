<?php
$nologinrequired = true;
include "mysql.php";

// The following lines are table alteration commands, preserved here as comments
// based on the original script's context. They should be run only once, manually.
/*
db_query( "alter table company_esi add principalname varchar( 100 )" );
db_query( "alter table company_esi add principalemail varchar( 100 )" );
db_query( "alter table company_esi add customernumber integer " );
db_query( "alter table company_esi add schoolcode varchar( 40 ) " );
db_query( "alter table company_esi add schoolphone varchar( 40 ) " );
*/

// Initialize assumed database link for escaping functions
$db_link = $GLOBALS['link'] ?? $link; 

// --- 1. File Handling ---
$file_path = "/tmp/access.csv";
$handle = fopen($file_path, "r");

if ($handle === false) {
    die("Error: Could not open file '{$file_path}' for reading.");
}

// --- HTML Table Header Output ---
echo "<table><tr>
        <td>School Code</td>
        <td>DB Name</td>
        <td>Spreadsheet Name</td>
        <td>AED #</td>
        <td>AED Serial</td>
        <td>Location</td>
        <td>Pad A</td>
        <td>Pad B</td>
        <td>Pediatric Pads</td>
    </tr>";

// --- 2. CSV Processing Loop ---
while (($data = fgetcsv($handle, 9999, ",")) !== FALSE) {

    // Skip empty lines and the header row (assuming header starts with "District")
    if (empty($data[0] ?? '') || ($data[0] ?? '') === "District") {
        continue;
    }
    
    // Construct the school code: District (data[0]) + Building Code Prefix (data[2][0]) + Building Code Suffix (substr(data[2], 1))
    $district = trim($data[0] ?? '');
    $building_code_full = trim($data[2] ?? '');
    $building_code_prefix = !empty($building_code_full) ? $building_code_full[0] : '';
    $building_code_suffix = !empty($building_code_full) ? substr($building_code_full, 1) : '';
    
    $schoolcode_raw = "{$district}-{$building_code_prefix}-{$building_code_suffix}";
    $schoolcode_safe = mysqli_real_escape_string($db_link, $schoolcode_raw);

    // --- 3. Find Company Record ---
    $sql_company = "SELECT id, companyname FROM company_esi WHERE schoolcode = '{$schoolcode_safe}' AND deleted = 0";
    $srow = db_query_first( $sql_company ); // Assumed function
    
    $company_id = $srow['id'] ?? null;
    $aeds = [];

    if( $company_id )
    {
        // Assumed function: getAedRows($company_id, $include_deleted=false, $filter="", $return_array=true)
        $aeds = getAedRows( $company_id, true, "", true ); 
    }
    
    // Ensure loop executes at least once to show "Not Found" or company data
    if( empty($aeds) ) {
        $aeds[] = []; // Add an empty array to ensure a row is displayed
    }
    
    // --- 4. Loop Through AEDs (or one empty row) and Output Table Rows ---
    $cnt = 1;
    foreach( $aeds as $arow )
    {
        $db_company_name = htmlspecialchars($srow['companyname'] ?? '');
        $spreadsheet_name = htmlspecialchars($data[3] ?? '');
        $serial = htmlspecialchars($arow['serial'] ?? '');
        $location = htmlspecialchars($arow['location'] ?? '');
        
        // Determine company name display
        $company_name_display = $company_id ? $db_company_name : "<font color='red'>Not Found</font>";
        $aed_number_display = $company_id ? $cnt++ : "";
        
        echo "<tr>";
        echo "<td>" . htmlspecialchars($schoolcode_raw) . "</td>";
        echo "<td>{$company_name_display}</td>";
        echo "<td>{$spreadsheet_name}</td>";
        echo "<td>{$aed_number_display}</td>";
        echo "<td>{$serial}</td>";
        echo "<td>{$location}</td>";
        
        // Use the redifexpired function for pads
        echo "<td>" . redifexpired( $arow['padaexpiration'] ?? '' ) ."</td>";
        echo "<td>". redifexpired( $arow['padbexpiration'] ?? '' ) ."</td>";
        echo "<td>". redifexpired( $arow['pediatricpads'] ?? '' ) . "</td>";
        echo "</tr>";
    }
}
echo "</table>";

// --- 5. Close File Handle ---
fclose( $handle );

// --- 6. Helper Function Definition ---
function redifexpired( $dt )
{
    // The original script relies on the format '0000-00-00' for no date
    if (empty($dt) || $dt === "0000-00-00") {
        return ""; 
    }
    
    $dtsec = strtotime( $dt );
    $current_time = time();
    $dt_safe = htmlspecialchars($dt);

    // Check for expiration (date in the past) or invalid date conversion
    if( $dtsec === false || $dtsec < $current_time )
    {
        return "<font color='red'>{$dt_safe}</font>";
    }
    else
    {
        return $dt_safe;
    }
}
?>
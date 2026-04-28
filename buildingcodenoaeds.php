<?php
include "mysql.php";

$sql = "SELECT DISTINCT location_to_building.buildingcode FROM location_to_building";

$schools = db_query_array( $sql, "buildingcode", "buildingcode" );

if( $xls )
{
    // Generate CSV instead of Excel
    $filename = "report_buildings_without_aeds_" . time() . ".csv";
    
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // Write UTF-8 BOM for Excel compatibility
    fwrite($output, "\xEF\xBB\xBF");
    
    // Write header row
    $header = ["Building Code", "Schools Associated"];
    fputcsv($output, $header);
    
    if (is_array($schools)) {
        foreach( $schools as $buildingcode )
        {
            $buildingcode_safe = mysqli_real_escape_string($link, $buildingcode);

            // Check if ANY active AED exists for this building code
            $any = db_query_first_cell( "SELECT COUNT(*) FROM aed_esi 
                                        WHERE deleted = 0 
                                        AND aedstolen = 0 
                                        AND aedretired = 0 
                                        AND buildingcode = '{$buildingcode_safe}'" );
            
            if( $any ) continue; // Skip if AEDs exist
            
            // Find all schools/companies linked to this building code's locations
            $sql_schools = "SELECT companyname, id, schoolcode, locationcode 
                            FROM company_esi 
                            WHERE locationcode IN ( 
                                SELECT locationcode 
                                FROM location_to_building 
                                WHERE buildingcode = '{$buildingcode_safe}' 
                            ) 
                            AND deleted = 0";
            
            $mysrow = db_query_rows( $sql_schools );
            
            if( !count( $mysrow ) ) continue; // Skip if no schools are found
            
            // Build schools list string
            $str = "";
            foreach( $mysrow as $s )
            {
                // PHP 8.2 Fix: Use quoted array keys
                if( $str ) {
                    $str .= ", ";
                }
                $str .= $s['companyname'] ?? '';
            }
            
            // Prepare data row
            $rowData = [$buildingcode, $str];
            
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
    
    fclose($output);
    exit();
}
// --- 3. HTML Table Output Logic ---
else
{
    $i = 1;
    echo( "<table border='1'>" );
    
    // Header Row
    echo( "<tr><td>Num</td><td>Building Code</td><td>Schools</td></tr>" );
    
    if (is_array($schools)) {
        foreach( $schools as $buildingcode )
        {
            $buildingcode_safe = mysqli_real_escape_string($link, $buildingcode);
            
            // Check if ANY active AED exists for this building code
            $any = db_query_first_cell( "SELECT COUNT(*) FROM aed_esi 
                                        WHERE deleted = 0 
                                        AND aedstolen = 0 
                                        AND aedretired = 0 
                                        AND buildingcode = '{$buildingcode_safe}'" );
            
            if( $any ) continue; // Skip if AEDs exist
            
            // Find all schools/companies linked to this building code's locations
            $sql_schools = "SELECT companyname, id, schoolcode, locationcode 
                            FROM company_esi 
                            WHERE locationcode IN ( 
                                SELECT locationcode 
                                FROM location_to_building 
                                WHERE buildingcode = '{$buildingcode_safe}' 
                            ) 
                            AND deleted = 0";
            
            $mysrow = db_query_rows( $sql_schools );

            if( !count( $mysrow ) ) continue; // Skip if no schools are found
            
            echo( "<tr><td>{$i}</td><td>" . htmlspecialchars($buildingcode) . "</td>" );
            echo( "<td>" );
            
            foreach( $mysrow as $s )
            {
                // PHP 8.2 Fix: Use quoted array keys and htmlspecialchars()
                $id_safe = htmlspecialchars($s['id']);
                $companyname_safe = htmlspecialchars($s['companyname']);
                $schoolcode_safe = htmlspecialchars($s['schoolcode']);
                $locationcode_safe = htmlspecialchars($s['locationcode']);
                
                echo( "<a href='viewcompany.php?id={$id_safe}'>{$companyname_safe}</a> {$schoolcode_safe} {$locationcode_safe} <br>" );
            }
            
            echo( "</td>" );
            echo( "</tr>" );
            $i++;
        } 
    }
    ?>
 </table>
 <?php 
} 
?>
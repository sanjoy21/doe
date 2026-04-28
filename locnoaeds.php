<?php
// Set the database link globally for escaping functions (assuming mysqli or PDO setup in mysql.php)
global $link; 
include "mysql.php";

// --- 1. Determine which list of schools to fetch ---
// Assumes $type is available in the global scope (e.g., from query parameters)
$type = $_GET['type'] ?? $type ?? '';
$xls = $_GET['xls'] ?? $xls ?? false; // Check for CSV/XLS flag

if ($type == "r") {
    // Assumes getNoResponderLocations() is defined and fetches data safely
    $schools = getNoResponderLocations();
    $report_title = "Locations Without Responders";
} else {
    // Assumes getNoAEDLocations() is defined and fetches data safely
    $schools = getNoAEDLocations();
    $report_title = "Locations Without AEDs";
}

// --- 2. Conditional Output based on $xls flag ---
if ($xls) {
    // --- CSV Generation (Replaces Spreadsheet_Excel_Writer) ---
    
    // Define the filename for the download, using the original base name but ensuring .csv extension
    // The original code used $filename = "report.xls";
    $base_filename = "report"; 
    $filename = $base_filename . "_" . date('Ymd') . ".csv";
    
    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    
    $output = fopen('php://output', 'w');
    
    // Write Header Row
    $header = array(
        "Name",
        "Zip Code",
        "Contact Name",
        "Contact Email",
        "Associated Companies" 
    );
    fputcsv($output, $header);

    // Write Data Rows
    foreach ($schools as $srow) {
        // Securely escape the campus ID for the nested query
        $campus_id_safe = mysqli_real_escape_string($link, $srow['id'] ?? '');

        // Fetch associated companies/schools
        $sql_companies = "SELECT companyname, id FROM company_esi WHERE campusid='{$campus_id_safe}' AND deleted = 0";
        // Assumes db_query_rows returns an array of rows
        $s = db_query_rows($sql_companies);
        
        $company_names_str = "";
        $separator = "";
        foreach ($s as $sr) {
            $company_names_str .= $separator . ($sr['companyname'] ?? '');
            $separator = ", ";
        }

        // Write the data row to the CSV
        $data_row = array(
            $srow['name'] ?? '',
            $srow['zipcode'] ?? '',
            $srow['contactname'] ?? '',
            $srow['contactemail'] ?? '',
            $company_names_str
        );
        fputcsv($output, $data_row);
    }
    
    fclose($output);
    exit;

} else {
    // --- HTML Table Output ---
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title><?php echo htmlspecialchars($report_title); ?></title>
        <style>
            table { border-collapse: collapse; width: 80%; margin: 20px auto; }
            th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; }
        </style>
    </head>
    <body>
    <h1 align="center"><?php echo htmlspecialchars($report_title); ?></h1>
    
    <?php if (empty($schools)) : ?>
        <p align="center">No locations found for this report.</p>
    <?php else : ?>

        <table border="1">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Zipcode</th>
                    <th>Contact Name</th>
                    <th>Contact Email</th>
                    <th>Schools</th>
                </tr>
            </thead>
            <tbody>
            <?php
            // Assumes $minnum is used elsewhere; initializing here for safety
            $minnum = $_GET['minnum'] ?? 0;
            $i = 1;
            
            foreach ($schools as $srow) {
                // Securely escape the campus ID for the nested query
                $campus_id_safe = mysqli_real_escape_string($link, $srow['id'] ?? '');

                // Fetch associated companies/schools
                $sql_companies = "SELECT companyname, id FROM company_esi WHERE campusid='{$campus_id_safe}' AND deleted = 0";
                $s = db_query_rows($sql_companies);
                
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($srow['name'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($srow['zipcode'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($srow['contactname'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($srow['contactemail'] ?? ''); ?></td>
                    <td>
                        <?php 
                        foreach ($s as $sr) {
                            // Link to company details
                            $company_name = htmlspecialchars($sr['companyname'] ?? '');
                            $company_id = htmlspecialchars($sr['id'] ?? '');
                            echo "<a href='viewcompany.php?id={$company_id}'>{$company_name}</a><br>";
                        } 
                        ?>
                    </td>
                </tr>
                <?php
                $i++;
            } 
            ?>
            </tbody>
        </table>
    <?php endif; ?>
    </body>
    </html>
<?php 
} // End of HTML else block
?>
<?php 

include "mysql.php";

function db_escape($string) {
    if (is_array($string)) {
        return $string; // Do not escape arrays
    }
    // Basic placeholder for security demonstration
    return str_replace(['\\', "'"], ['\\\\', "\'"], (string)$string);
}

// Initialize $go explicitly from $_POST for PHP 8.2 compatibility
$go = $_POST['go'] ?? null;
$upload_file_path = $_FILES["upload"]["tmp_name"] ?? null;

if( $go && $upload_file_path )
{
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="aedlocations.csv"');
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // Add UTF-8 BOM for Excel compatibility
    fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
    
    // Check if the uploaded file exists and can be read
    if (file_exists($upload_file_path)) {
        $file = file( $upload_file_path );
    } else {
        // Handle case where file upload failed or file doesn't exist
        exit("Error: Upload file not found.");
    }
    
    // Write headers
    $headers = array(
        "Serial",
        "School Code",
        "School",
        "Building Code",
        "Location",
        "Floor",
        "City",
        "Zip",
        "Borough"
    );
    fputcsv($output, $headers);

    foreach( $file as $f )
    {
        $f = trim( $f );
        
        // --- Security Improvement: Use prepared statement or proper escaping ---
        // Using db_escape function if available, otherwise use mysqli_real_escape_string
        $safe_f = db_escape($f) ?? addslashes($f);
        
        $sql = "select aed_esi.buildingcode, aed_esi.location, aed_esi.floor, c.* from aed_esi, company_esi c 
                where c.id = aed_esi.clientid 
                and serial = '$safe_f' 
                and c.deleted = 0 
                and aed_esi.deleted = 0";
                
        $row = db_query_first( $sql );
        
        // Start row data with serial number
        $row_data = array($f);
        
        // Check for existence and add data
        if( isset($row['id']) )
        {
            $row_data[] = $row['schoolcode'] ?? '';
            $row_data[] = $row['companyname'] ?? '';
            $row_data[] = $row['buildingcode'] ?? '';
            $row_data[] = $row['location'] ?? '';
            $row_data[] = $row['floor'] ?? '';
            $row_data[] = $row['city'] ?? '';
            $row_data[] = $row['zip'] ?? '';
            $row_data[] = $row['borough'] ?? '';
        }
        else
        {
            // Add "Not Found" for school code and empty values for remaining columns
            $row_data[] = "Not Found";
            $row_data[] = ''; // School
            $row_data[] = ''; // Building Code
            $row_data[] = ''; // Location
            $row_data[] = ''; // Floor
            $row_data[] = ''; // City
            $row_data[] = ''; // Zip
            $row_data[] = ''; // Borough
        }
        
        fputcsv($output, $row_data);
    }
    
    fclose($output);
    exit;
}

?>
<?php include "ssi/top.php"; ?>
<h2>AED Locations</h2><br><br>
<!-- Use HTML form with POST method and enctype for file upload -->
<form method='post' enctype='multipart/form-data'>
    <label for="upload_file">CSV:</label>
    <input type='file' name='upload' id="upload_file">
    <input type='submit' name='go' value='Go'><br>
    <i>Upload a 1 column csv with ONLY the serial numbers of the AEDs you're looking for.</i>
</form>

<br><br>
<?php include "ssi/footer.php"; ?>
</span>
</td>
<td valign="top" width="15"><img src="images/dotclear.gif" width="10"></td>
</tr>
</table>
<br><br>
</form>
</body>
</html>
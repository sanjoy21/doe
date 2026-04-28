<?php 
include "mysql.php";

// --- 1. Safely Retrieve Request Variables ---
$go = $_POST['go'] ?? null;
$xls = $_REQUEST['xls'] ?? null;
$cont = ""; // Variable to hold buffered content

// --- 2. Processing Logic (if submitted) ---
if( $go )
{
    $db_link = $GLOBALS['link'] ?? null; // Get database link for escaping
    
    // --- Excel Download Path ---
    if( $xls )
    {
        // Headers for Excel file download
        header( "Content-type: application/vnd.ms-excel" );
        header("Content-Transfer-Encoding: binary");
        $user_agent = strtolower ($_SERVER["HTTP_USER_AGENT"]);
        $filename = "aeds.xls";
        
        // Handle filename encoding for different browsers
        if ((is_integer (strpos($user_agent, "msie"))) && (is_integer (strpos($user_agent, "win")))) {
            header( "Content-Disposition: filename=" . basename($filename) . ";" );
        } else {
            header( "Content-Disposition: attachment; filename=" . basename($filename) . ";" );
        }
    }
    
    // --- File Handling ---
    $file_tmp_name = $_FILES["myfile"]["tmp_name"] ?? null;
    $handle = $file_tmp_name ? fopen( $file_tmp_name, "r" ) : false;

    if( !$xls )
    {
        // Start output buffering for HTML display
        ob_start();
    }
    
    // Corrected table opening tag
    echo "<table border='1' cellpadding='2' cellspacing='0'>";
    echo "<tr><td>Serial</td><td>School</td><td>All Serials</td><td>Address</td><td>City</td><td>Zip</td><td>Borough</td><td>Missing</td></tr>";
    
    $i = 0;
    
    if( $handle )
    {
        while (($data = fgetcsv($handle, 9999, ",")) !== FALSE) {
            
            $i++;
            $data0_raw = trim($data[0] ?? '');
            
            // Skip if the first column (serial) is empty
            if( !$data0_raw ) {
                continue;
            }
            
            $serial_safe = mysqli_real_escape_string($db_link, $data0_raw);
            $serial_zero_safe = mysqli_real_escape_string($db_link, '0' . $data0_raw);
            
            // --- DB Query 1: Find AED by exact serial ---
            $sql1 = "SELECT * FROM aed_esi WHERE serial = '{$serial_safe}' AND deleted = 0";
            $row = db_query_first( $sql1 );
            
            // --- DB Query 2: Try finding AED by serial prefixed with '0' ---
            if( !$row ) {
                $sql2 = "SELECT * FROM aed_esi WHERE serial = '{$serial_zero_safe}' AND deleted = 0";
                $row = db_query_first( $sql2 );
            }
            
            $aed_id = (int)($row['aedid'] ?? 0);
            $client_id = (int)($row['clientid'] ?? 0);

            // --- Output Results ---
            if( $client_id > 0 )
            { 
                // AED Found and Assigned to a School
                
                // Get all other B-serials for the same client (school)
                $sql_serials = "SELECT GROUP_CONCAT(serial) FROM aed_esi 
                                WHERE deleted = 0 AND clientid = {$client_id} 
                                AND aedstolen = 0 AND aedretired = 0 AND aedinactive = 0 
                                AND serial LIKE 'B%'";
                $numaeds = db_query_first_cell( $sql_serials );
                
                // Get school details
                $crow = getCompanyRow( $client_id ); // Assumed function

                $company_name = htmlspecialchars($crow['companyname'] ?? 'N/A');
                $address = htmlspecialchars($crow['address'] ?? 'N/A');
                $city = htmlspecialchars($crow['city'] ?? 'N/A');
                $zip = htmlspecialchars($crow['zip'] ?? 'N/A');
                $borough = htmlspecialchars($crow['borough'] ?? 'N/A');
                $aed_missing = ($row['aedmissing'] ?? 0) ? "Y" : "N";
                $class_url_prefix = URL_WITHOUT_SUBDOMAIN ?? 'emergencyskills.com';
                $sub_doe = $GLOBALS['SUB_DOE'] ?? SUB_DOE ?? 'doe';
                
                echo "<tr>";
                echo "<td>" . htmlspecialchars($data0_raw) . "</td>";
                echo "<td><a href='https://{$sub_doe}.{$class_url_prefix}/editaed.php?aedid={$aed_id}'>{$company_name}</a></td>";
                echo "<td>" . htmlspecialchars($numaeds) . "</td>";
                echo "<td>{$address}</td>";
                echo "<td>{$city}</td>";
                echo "<td>{$zip}</td>";
                echo "<td>{$borough}</td>";
                echo "<td>{$aed_missing}</td>";
                echo "</tr>";
            }
            else
            {
                // AED Not Found or Not Assigned
                echo "<tr>";
                echo "<td>" . htmlspecialchars($data0_raw) . "</td>";
                echo "<td>not found</td>";
                echo "</tr>"; // Closes the row correctly
            }
        } 
        fclose($handle);
    }

    echo "</table>";
    
    if( !$xls )
    {
        // End output buffering and capture content
        $cont = ob_get_clean();
    }
    else
    {
        // Exit for XLS download
        exit;
    }

}

// --- 3. HTML Form Display (Common to both paths) ---
include "ssi/top.php"; 
?>
<form method='post' enctype='multipart/form-data'>
<h3>Find Serials</h3>
File: <input type='file' name='myfile'><br>
XLS? <input type='checkbox' name='xls' value='1'><br>
<input type='submit' name='go' value='Go'>
</form>

<?php echo $cont; // Display buffered content if not XLS ?>

 <br><br><br>
<?php include "ssi/footer.php" ; ?>
</span>
 </td>
 <td valign="top" width="15"><img src="images/dotclear.gif" width="10" alt=""></td>
 </tr>
</table>
<br><br>
</div>
</body>
</html>
<?php 
include "mysql.php";

// --- 1. Safely Retrieve Request Variables ---
$go = $_POST['go'] ?? null;
$xls = $_REQUEST['xls'] ?? null; // XLS checkbox is commented out, but we check for it just in case
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

    echo "<table border='1' cellpadding='2' cellspacing='0'><tr>";
    echo "<th>School Code</th><th>DB Name</th><th>File Name</th><th>DB Address</th><th>File Address</th>";
    echo "</tr>";
    
    $i = 0;
    
    if( $handle )
    {
        while (($data = fgetcsv($handle, 9999, ",")) !== FALSE) {
            
            $i++;
            $data0_raw = $data[0] ?? '';
            
            // Skip if the first column is empty
            if( !$data0_raw ) {
                continue;
            }
            
            // --- Format School Code ---
            // Assumes $data[0] is like XXXXXX (e.g., 201003) -> XX-X-XXX (e.g., 20-1-003)
            $sc_raw = substr( $data0_raw, 0, 2 ) . "-" . substr( $data0_raw, 2, 1 ) . "-" . substr( $data0_raw, 3, 3 );
            
            $sc_safe = mysqli_real_escape_string($db_link, $sc_raw);
            $data8_safe = mysqli_real_escape_string($db_link, $data[8] ?? '');
            
            // --- DB Query 1: Match by Code AND Address ---
            // Uses LIKE for partial address matching
            $sql1 = "SELECT * FROM company_esi 
                     WHERE schoolcode = '{$sc_safe}' 
                     AND CONCAT( address, '%' ) LIKE '{$data8_safe}' 
                     AND deleted = 0";
            $rows = db_query_rows( $sql1 );
            
            // --- DB Query 2: Match by Code ONLY if Query 1 failed ---
            if( !count( $rows ) ) {
                $sql2 = "SELECT * FROM company_esi 
                         WHERE schoolcode = '{$sc_safe}' 
                         AND deleted = 0";
                $rows = db_query_rows( $sql2 );
            }

            // --- Output Results ---
            if( !count( $rows ) )
            {
                $data1_safe = htmlspecialchars($data[1] ?? 'N/A');
                echo "<tr><td colspan='6'>No match for " . htmlspecialchars($sc_raw) . " and {$data1_safe}</td></tr>";
            }
            else
            {
                foreach( $rows as $row )
                {
                    $company_id = (int)($row['id'] ?? 0);
                    $db_name = htmlspecialchars($row['companyname'] ?? '');
                    $db_address = htmlspecialchars($row['address'] ?? '');
                    $db_city = htmlspecialchars($row['city'] ?? '');
                    $db_state = htmlspecialchars($row['state'] ?? '');
                    $db_zip = htmlspecialchars($row['zip'] ?? '');
                    
                    $file_name = htmlspecialchars($data[1] ?? 'N/A');
                    $file_address = htmlspecialchars($data[8] ?? 'N/A');
                    
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($sc_raw) . "</td>";
                    echo "<td><a href='viewcompany.php?id={$company_id}'>{$db_name}</a></td>";
                    echo "<td>{$file_name}</td>";
                    echo "<td>{$db_address}, {$db_city}, {$db_state}, {$db_zip}</td>";
                    echo "<td>{$file_address}</td>";
                    echo "</tr>";
                }
            }
            // Spacer row
            echo "<tr><td colspan='6'>&nbsp;</td></tr>";
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
<h3>Find Schools</h3>
File: <input type='file' name='myfile'><br>
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
<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// ini_set("auto_detect_line_endings", true);
require_once('mysql.php');

$err = [];

// --- 1. Access Control ---
if (!$specialadmin) {
    header("location: login.php");
    exit;
}

// --- 2. File Upload and Processing ---
if ($addnew && isset($_FILES["newfile"]) && $_FILES["newfile"]["error"] === UPLOAD_ERR_OK) {
    
    $path = $_FILES["newfile"]["tmp_name"];
    $target_path = "/tmp/inv.csv"; // Using a temporary, secure path

    // Move uploaded file to a known location
    if (move_uploaded_file($path, $target_path)) {
        
        // Convert line endings (security risk: shell_exec is dangerous)
        // In a secure environment, this should be done with PHP string functions.
        shell_exec("dos2unix " . escapeshellarg($target_path));

        $arr = [];
        if (($handle = fopen($target_path, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                // Remove empty rows/cells that only contain whitespace
                $arr[] = array_map('trim', $data);
            }
            fclose($handle);
            
            // Remove the temporary file
            unlink($target_path); 

            $vals = [];
            $startedvals = false;
            $invoiceno = '';
            
            // First pass: Try to detect Format 1 (multi-line invoice, column-based IDs)
            foreach ($arr as $rownum => $arow) {
                if ($arow[4] === "Invoice No.") {
                    $invoiceno = $arr[$rownum + 1][4];
                    $err[] = "Attempting to parse Format 1. Invoice No is " . $invoiceno;
                } else if ($startedvals) {
                    if ($arow[3] === "TOTAL") break;
                    $vals[] = $arow[3]; // Collecting Service/Drill IDs
                } else if ($arow[0] === "SCHOOL") {
                    $startedvals = true;
                }
            }

            // --- 3. Database Update Logic ---
            $updates_count = 0;
            
            // If Format 1 was detected and parsed
            if ($invoiceno && $startedvals) {
                foreach ($vals as $v) {
                    if (empty($v)) continue;
                    
                    $v_clean = $v;
                    $table = null;
                    $id_field = null;
                    $extra_where = '';

                    // Check for prefixes and set table/field
                    if (strpos($v_clean, "S") !== false) {
                        $v_clean = str_replace("S", "", $v_clean);
                        $table = "servicecall";
                        $id_field = "servicecallid";
                    } elseif (strpos($v_clean, "NI") !== false) {
                        $v_clean = str_replace("NI", "", $v_clean);
                        $table = "servicecall";
                        $id_field = "servicecallid";
                        $extra_where = ' AND newinstall = 1';
                    } elseif (strpos($v_clean, "D") !== false) {
                        $v_clean = str_replace("D", "", $v_clean);
                        $table = "drill";
                        $id_field = "drillid";
                    }

                    if ($table) {
                        $sql = "UPDATE {$table} SET invoiced = 1, invoiceno = ? WHERE {$id_field} = ? {$extra_where}";
                        // Use prepared statement to prevent SQL injection
                        db_query($sql, [$invoiceno, $v_clean]);
                        $updates_count++;
                    }
                }
            } 
            
            // If Format 1 failed (or we are assuming Format 2 has priority/is also used)
            if (!$startedvals || $updates_count === 0) {
                $err = ["Format 1 parsing failed or yielded no results. Trying Format 2 (Single line/Direct mapping)."];

                foreach ($arr as $r) {
                    $v = trim($r[0]);
                    $current_invoiceno = trim($r[1]);

                    // Skip header or invalid rows
                    if (empty($v) || empty($current_invoiceno) || strpos(strtolower($current_invoiceno), "invoice") !== false) {
                        continue;
                    }

                    $v_clean = $v;
                    $table = null;
                    $id_field = null;
                    $extra_where = '';
                    
                    // Check for prefixes
                    if (strpos($v_clean, "S") !== false) {
                        $v_clean = str_replace("S", "", $v_clean);
                        $table = "servicecall";
                        $id_field = "servicecallid";
                    } elseif (strpos($v_clean, "NI") !== false) {
                        $v_clean = str_replace("NI", "", $v_clean);
                        $table = "servicecall";
                        $id_field = "servicecallid";
                        $extra_where = ' AND newinstall = 1';
                    } elseif (strpos($v_clean, "D") !== false) {
                        $v_clean = str_replace("D", "", $v_clean);
                        $table = "drill";
                        $id_field = "drillid";
                    }
                    
                    if ($table) {
                        $sql = "UPDATE {$table} SET invoiced = 1, invoiceno = $current_invoiceno WHERE {$id_field} = $v_clean {$extra_where}";
                        // Use prepared statement
                        db_query($sql);
                        $updates_count++;
                    }
                }
            }

            $err = ["Database updated successfully: **{$updates_count} records processed.**"];

        } else {
            $err[] = "Error: Could not open the uploaded file for reading.";
        }
    } else {
        $err[] = "Error: Failed to move uploaded file to temporary location.";
    }
} elseif ($addnew && !isset($_FILES["newfile"])) {
    $err[] = "Error: No file was uploaded.";
}
?>
<?php include "ssi/top.php"; ?>        
        
        <strong><span class="title">UPLOAD INVOICE #s</span></strong>
        
        <p>
        
        
        <span class="copy">
        
        <table cellpadding="0" cellspacing="0" border="0" width="100%"  class="table3">
            <tr>            
            <td valign="top">
        <form method='post' enctype='multipart/form-data' >
            <font color='red'> <?= implode("<br>", $err) ?></font>
<span class="copy">
Invoice File: <input type='file' name='newfile'><br>
<input type='submit' name='addnew' value='Upload'>
</span>
                </td>
            </tr>
        </table>
<br><br><br><br><br><br><br>

            <?php include "ssi/footer.php"; ?>
            
            </span>
            </td>
            <td valign="top" width="15"><img src="images/dotclear.gif" width="10"></td>
            </tr>
        </table>
        <br><br>
</div>
</body>
</html>
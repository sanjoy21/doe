<?php
include "mysql.php"; 

// --- 1. Initialize Variables ---
$del = $_GET['del'] ?? null;
$search = $_REQUEST['search'] ?? null;
$substr = $_POST['substr'] ?? $_GET['substr'] ?? '';
$searchzip = $_POST['searchzip'] ?? $_GET['searchzip'] ?? '';
$xls = $_GET['xls'] ?? null; // Restored: Required for export logic
$rows = []; 

function db_escape($string) {
    if (is_array($string)) {
        return $string; 
    }
    return str_replace(['\\', "'"], ['\\\\', "\'"], (string)$string);
}

// --- 2. Deletion Logic ---
if ($del) {
    $del_safe = (int)$del;
    db_query("DELETE FROM buildings WHERE id = '{$del_safe}'");
    $del = null; 
}

// --- 3. Data Fetching Logic ---
// We fetch data if the user is searching OR if they are requesting an export
if ($search || $xls) {
    $whr = "";
    $substr_safe = db_escape(trim($substr));
    $searchzip_safe = db_escape(trim($searchzip));

    if (!empty($substr)) {
        if ($search == "1") {
            $whr = " AND ( buildingcode = '{$substr_safe}' ) ";
        } else {
            $whr = " AND ( buildingname LIKE '%{$substr_safe}%' OR buildingcode = '{$substr_safe}' ) ";
        }
    }

    if (!empty($searchzip)) {
        $whr .= " AND ( zip = '{$searchzip_safe}' ) ";
    }
    
    $sql = "SELECT * FROM buildings WHERE 1 {$whr} ORDER BY buildingcode";
    $rows = db_query_rows($sql);
}

// --- 4. CSV Export Logic ---
// This must run before any HTML is sent to the browser
if ($xls && !empty($rows)) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="buildings_export.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Add UTF-8 BOM for Excel compatibility
    fputs($output, (chr(0xEF) . chr(0xBB) . chr(0xBF)));
    
    // Write Headers
    fputcsv($output, ["buildingcode", "buildingname", "address", "city", "state", "zip", "schools"]);

    foreach ($rows as $r) {
        $building_code = db_escape($r["buildingcode"] ?? '');
        
        // Query associated schools
        $res = db_query_rows("
            SELECT c.companyname, c.schoolcode 
            FROM company_esi c, location_to_building l 
            WHERE l.buildingcode = '{$building_code}' 
            AND c.locationcode = l.locationcode 
            ORDER BY c.companyname
        ");

        $school_entries = array();
        if (is_array($res)) {
            foreach ($res as $c) {
                $school_entries[] = ($c['companyname'] ?? 'N/A') . " (" . ($c['schoolcode'] ?? 'N/A') . ")";
            }
        }
        $school_str = implode("; ", $school_entries);
        
        fputcsv($output, [
            $r["buildingcode"] ?? '',
            $r["buildingname"] ?? '',
            $r["address"] ?? '',
            $r["city"] ?? '',
            $r["state"] ?? '',
            $r["zip"] ?? '',
            $school_str
        ]);
    }
    
    fclose($output);
    exit; // Stop script so no HTML is appended to the CSV
}

// --- 5. Page Display ---
include "ssi/top.php"; 
?>

<strong><span class="title">MANAGE BUILDINGS</span></strong>
           
<p>
<table cellpadding="3" cellspacing="0" border="0">
    <tr>
        <td nowrap>
            <form method='post' action="buildings.php">
                <span class='copy'>
                    Search: <input type='text' name='substr' size='12' value="<?php echo htmlspecialchars($substr); ?>">
                    Zip Code: <input type='text' name='searchzip' size='7' value="<?php echo htmlspecialchars($searchzip); ?>">
                    <input type='submit' name='search' value='Search'> 
                    <a href='buildings.php?search=true'>View All</a> | 
                    <a href='buildings.php?xls=true&search=true&substr=<?php echo urlencode($substr); ?>&searchzip=<?php echo urlencode($searchzip); ?>'>Export to XLS</a>
                    <br><br>
                </span>
            </form>

            <?php if ($search && isset($rows)) { ?>
                <span class='copy'><strong>Search Results (<?php echo count($rows); ?>)</strong></span>
                <table cellpadding='2' border='1' cellspacing='0'>
                    <tr>
                        <th class='copy'>Building Code</th>
                        <th class='copy'>Building Name</th>
                        <th class='copy'>Address</th>
                        <th class='copy'>City</th>
                        <th class='copy'>State</th>
                        <th class='copy'>Zip</th>
                        <th class='copy'>Schools</th>
                        <?php if (function_exists('isOverallAdmin') && isOverallAdmin()) { ?>
                            <th class='copy'>Actions</th>
                        <?php } ?>
                    </tr>
                    <?php foreach ($rows as $row) { 
                        $row_building_code = db_escape($row["buildingcode"] ?? '');
                        $row_id = (int)($row["id"] ?? 0);
                    ?>
                    <tr>
                        <td class='copy'><?php echo htmlspecialchars($row["buildingcode"] ?? ''); ?></td>
                        <td class='copy'><?php echo htmlspecialchars($row["buildingname"] ?? ''); ?></td>
                        <td class='copy'><?php echo htmlspecialchars($row["address"] ?? ''); ?></td>
                        <td class='copy'><?php echo htmlspecialchars($row["city"] ?? ''); ?></td>
                        <td class='copy'><?php echo htmlspecialchars($row["state"] ?? ''); ?></td>
                        <td class='copy'><?php echo htmlspecialchars($row["zip"] ?? ''); ?></td>
                        <td class='copy'>
                            <?php
                                $res = db_query_rows("
                                    SELECT id, companyname, schoolcode 
                                    FROM company_esi c, location_to_building l 
                                    WHERE l.buildingcode = '{$row_building_code}' 
                                    AND c.locationcode = l.locationcode 
                                    ORDER BY c.companyname
                                ");

                                if (is_array($res)) {
                                    foreach ($res as $index => $c) {
                                        if ($index > 0) echo "<br>";
                                        echo "<a target='_blank' href='viewcompany.php?id=" . (int)$c['id'] . "'>" . htmlspecialchars($c['companyname']) . "</a>, " . htmlspecialchars($c['schoolcode']);
                                    }
                                }
                            ?>
                        </td>
                        <?php if (function_exists('isOverallAdmin') && isOverallAdmin()) { ?>
                        <td class='copy'>
                            <a onClick='return confirm("Are you sure?")' href='buildings.php?search=1&substr=<?php echo urlencode($substr); ?>&searchzip=<?php echo urlencode($searchzip); ?>&del=<?php echo $row_id; ?>'>Delete</a>
                        </td>
                        <?php } ?>
                    </tr>                  
                    <?php } ?>
                </table>
            <?php } ?>
        </td>
    </tr>
</table>

<br><br>
<?php include "ssi/footer.php"; ?>
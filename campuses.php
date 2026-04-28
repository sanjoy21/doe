<?php

include "mysql.php"; 

function db_escape($string) {
    if (is_array($string)) {
 return $string; // Do not escape arrays
    }
    // Basic placeholder for security demonstration
    return str_replace(['\\', "'"], ['\\\\', "\'"], (string)$string);
}

if ($search) {
    $whr = "";
    
    // Build WHERE clause for search
    if ($substr) {
 $substr_safe = db_escape($substr);
 $whr .= " AND (name LIKE '%{$substr_safe}%' OR contactname LIKE '%{$substr_safe}%' OR contactemail LIKE '%{$substr_safe}%') ";
    }
    
    if ($searchzip) {
        $searchzip_safe = db_escape($searchzip);
        $whr .= " AND (zipcode = '{$searchzip_safe}') ";
    }
    
    // Fetch campuses based on search criteria and session type
    $sql_campuses = "
        SELECT * FROM campus 
        WHERE iscorp = '{$session_iscorp}' 
        {$whr} 
        ORDER BY name
    ";
    $rows = db_query_rows($sql_campuses);

    // --- CSV Export Logic ---
    if ($xls) {
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="campuses.csv"');
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // Add UTF-8 BOM for Excel compatibility
    fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
    
    // Write Header Row
    $headers = array(
        "name",
        "contactname",
        "contactemail",
        "zipcode",
        "school",
        "borough",
        "schoolcode",
        "trainer"
    );
    fputcsv($output, $headers);
    
    if (is_array($rows)) {
        foreach ($rows as $r) {
            $campus_id = (int)($r['id']);
            $zipcode_safe = db_escape($r['zipcode']);

            // 1. Get Schools associated with this campus
            $res_schools = db_query_rows("
                SELECT companyname, borough, schoolcode 
                FROM company_esi 
                WHERE campusid = {$campus_id} 
                ORDER BY companyname
            ");

            // 2. Get Trainers for this campus's ZIP code
            $tarr = getTrainersForZip($zipcode_safe);
            $trainerarr = array();
            if (is_array($tarr)) {
                foreach ($tarr as $t) {
                    $trainerarr[] = $t["name"] ?? '';
                }
            }
            $trainer = implode(", ", $trainerarr);

            // If schools are found, output a row for each school
            if (is_array($res_schools) && !empty($res_schools)) {
                foreach ($res_schools as $c) {
                    $row_data = array(
                        $r["name"] ?? '',
                        $r["contactname"] ?? '',
                        $r["contactemail"] ?? '',
                        $r["zipcode"] ?? '',
                        $c["companyname"] ?? '',
                        $c["borough"] ?? '',
                        $c["schoolcode"] ?? '',
                        $trainer
                    );
                    
                    fputcsv($output, $row_data);
                }
            } else {
                // Output campus row even if no schools are attached (empty row for school data)
                $row_data = array(
                    $r["name"] ?? '',
                    $r["contactname"] ?? '',
                    $r["contactemail"] ?? '',
                    $r["zipcode"] ?? '',
                    '', // School Name
                    '', // Borough
                    '', // School Code
                    $trainer
                );
                
                fputcsv($output, $row_data);
            }
        }
    }
    
    fclose($output);
    exit;
}
}
?>

<?php include "ssi/top.php"; ?>
<!--start center content-->
<strong><span class="title">MANAGE <?php echo strtoupper(getSchoolStr("CAMPUSES")); ?></span></strong>
<p>
<table class="table3" cellpadding="3" cellspacing="0" border="0" width="100%" >
<tr><td><span class="copy"><strong>View <?php echo htmlspecialchars(getSchoolStr("School")); ?>:</strong></td></tr>
<tr><td nowrap>
<form method='post' action='campuses.php'>
<span class='copy'>    Search: <input type='text' name='substr' size='12' value="<?php echo htmlspecialchars($substr); ?>">
Zip Code: <input type='text' name='searchzip' size='7' value="<?php echo htmlspecialchars($searchzip); ?>">
<input type='submit' name='search' value='Search'> 
<a href='campuses.php?search=true'>View All</a> 
<a href='campuses.php?xls=true&search=true&substr=<?php echo urlencode($substr); ?>&searchzip=<?php echo urlencode($searchzip); ?>'>xls</a><br><br></span></form>

<?php if ($search) { ?>
<span class='copy'><strong>Search Results (<?php echo count($rows); ?>)</strong></span>
<table class='table3' cellpadding='2' border='1' cellspacing='0'>
<tr><th class='copy'><?php echo htmlspecialchars(getSchoolStr("Campus")); ?> Name</th>
<th class='copy'>Contact Name</th>
<th class='copy'>Contact Email</th>
<th class='copy'>Zip Code</th>
<th class='copy'><?php echo htmlspecialchars(getSchoolStr("Schools")); ?></th>
<th class='copy'>Trainer</th>
<?php foreach ($rows as $row) { 
$campus_id = $row['id'];
$zipcode_safe = db_escape($row['zipcode']);

// Get Trainers for display
$tarr = getTrainersForZip($zipcode_safe);
$trainerarr = [];
if (is_array($tarr)) {
 foreach ($tarr as $t) { 
     $trainerarr[] = htmlspecialchars($t["name"]); 
 }
}
        $trainer = implode(", ", $trainerarr);
    ?>
<tr>
<td class='copy'>
<a href='editcampus.php?campusid=<?php echo $campus_id; ?>'><?php echo htmlspecialchars($row['name']); ?></a>
</td>
<td class='copy'><?php echo htmlspecialchars($row["contactname"]); ?></td>
<td class='copy'><?php echo htmlspecialchars($row["contactemail"]); ?></td>
<td class='copy'><?php echo htmlspecialchars($row["zipcode"]); ?></td>
<td class='copy'>
<?php 
// Get Schools for HTML display
$res_schools = db_query_rows("
SELECT companyname, id 
FROM company_esi 
WHERE campusid = {$campus_id} AND deleted = 0 
ORDER BY companyname");
if (is_array($res_schools)) {
foreach ($res_schools as $r) {
$school_id = $r['id'];
?>
<a href='viewcompany.php?id=<?php echo $school_id; ?>'><?php echo htmlspecialchars($r['companyname']); ?></a><br>
<?php 
}
} ?>
</td>
<td class='copy'><?php echo $trainer; ?></td>
</tr>                  
<?php } // End row loop ?>
</table>
<?php } // End search results ?>
</td>
</tr>
<?php if ($specialadmin) { ?>
<tr><td><span class="copy"><a href="editcampus.php">Add New <?php echo htmlspecialchars(getSchoolStr("Campus")); ?></a></span></td>
</tr>
<?php } ?>
</table>
<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
<!--end center content-->
<?php include "ssi/footer.php"; ?>
<!--end footer-->
</span>
</td>
<td valign="top" width="15"><img src="images/dotclear.gif" width="10"></td>
</tr>
</table>
<br><br>
</div>
</body>
</html>
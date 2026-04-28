<?php
// Assumed external functions: db_query_rows, db_query_array, db_query_first_cell, getCompanyName, db_escape, db_query, db_insert

// 465-3637
require_once('mysql.php');

// The commented-out block appears to be a cleanup/deduplication process, 
// but is ignored as it was commented out in the original code.

// Initialize external variables
$ob = $_GET['ob'] ?? '';
$repw = ''; // Variable repw appears in the SQL but is not initialized or used, set to empty string for safety.
$arch = "(0)"; // Non-archived uploads

// --- 1. Filter for uploads that have an associated media file ---
// This assumes 'media_file' field value is the upload ID followed by an underscore (e.g., '123_')
$arr = db_query_array("
    SELECT uploadid FROM appuploaddata 
    WHERE name = 'media_file' 
    AND value = CONCAT(uploadid, '_')
", "uploadid", "uploadid");

$ext = " AND id IN (-1)"; // Default safe value
if (!empty($arr)) {
    // Escape all IDs to ensure they are integers for safe SQL injection avoidance
    $safe_ids = array_map('intval', $arr);
    $ext = " AND id IN (" . implode(", ", $safe_ids) . ")";
}

// --- 2. Find unique visits (school + date) ---
$order_by = 'dateuploaded DESC, dateinupload DESC, schoolid';
if (!empty($ob)) {
    // $ob seems unused in the original code path, so we use the default order_by.
    // If $ob was meant to override, we'd need to validate it.
}

$sql_trainers = "
    SELECT DISTINCT(schoolid), DATE(dateinupload) AS d, DATE(dateuploaded) AS dateuploaded 
    FROM appuploads 
    WHERE archived = 0 
    AND dateinupload > '2016-07-01' 
    {$ext} 
    ORDER BY {$order_by}
";
$trainers = db_query_rows($sql_trainers);

?>
<?php include "ssi/top.php"; ?>    
<p>
       
         <strong><span class="title">APP UPLOADS</span></strong>
       
</p>
       <form method='post'>

<table cellpadding="4" cellspacing="1" border="0" width="100%" bgcolor="#999999" class="table3" >
         <tr bgcolor="#e1e1f6">
<th class='copy'>School Name </th>
<th class='copy'>Date Signed    </th>
<th class='copy'>View Link    </th>
<th class='copy'>Visit Information    </th>
               </tr>
<?php
if (is_array($trainers)) {
    foreach ($trainers as $visitrow) {
        $school_id_safe = (int)($visitrow['schoolid'] ?? 0);
        $date_in_upload_safe = db_escape($visitrow['d'] ?? '0000-00-00');
        
        if ($school_id_safe === 0 || $date_in_upload_safe === '0000-00-00') {
            continue; // Skip invalid rows
        }

        // --- 3. Visit Detail Retrieval ---
        $scrows = db_query_rows("
            SELECT * FROM appuploads 
            WHERE archived IN {$arch} AND type = 'sc' 
            {$repw} AND schoolid = '{$school_id_safe}' 
            AND DATE(dateinupload) = '{$date_in_upload_safe}'
        ");
        
        $drillrows = db_query_rows("
            SELECT * FROM appuploads 
            WHERE archived IN {$arch} AND type = 'drill' 
            {$ext} AND schoolid = '{$school_id_safe}' 
            AND DATE(dateinupload) = '{$date_in_upload_safe}'
        ");
        
        $nirows = db_query_rows("
            SELECT * FROM appuploads 
            WHERE archived IN {$arch} AND type = 'ni' 
            {$repw} AND schoolid = '{$school_id_safe}' 
            AND DATE(dateinupload) = '{$date_in_upload_safe}'
        ");

        $bgcolor = "#FFFFFF"; 
        
        $arr_uploads = [];
        // Combine all uploads for the visit
        foreach ([$drillrows, $scrows, $nirows] as $t) {
            if (is_array($t)) {
                $arr_uploads = array_merge($arr_uploads, $t);
            }
        }


        // --- 4. Hacky Data Association Logic ---
        $tmpdrillid = "";
        if (!empty($drillrows)) {
            // Find the drill ID from the first drill upload
            foreach ($drillrows as $d) {
                $tmpdrillid = db_query_first_cell("
                    SELECT value FROM appuploaddata 
                    WHERE uploadid = '" . (int)$d['id'] . "' AND name IN ('drillid') 
                ");
                if ($tmpdrillid) break;
            }
            
            $fd = $tmpdrillid ? 1 : 0;
            
            // Link Service Calls/New Installs to the found Drill ID
            $uploads_to_link = array_merge($scrows, $nirows);
            foreach ($uploads_to_link as $s) {
                $upload_id_safe = (int)($s['id'] ?? 0);
                $scid = db_query_first_cell("
                    SELECT value FROM appuploaddata 
                    WHERE uploadid = '{$upload_id_safe}' 
                    AND name IN ('serviceid', 'servicecallid', 'servideid')
                ");
                
                if ($scid) {
                    db_query("
                        UPDATE servicecall 
                        SET fromdrill = {$fd}, assocdrillid = '" . db_escape($tmpdrillid) . "' 
                        WHERE servicecallid = '" . db_escape($scid) . "'
                    ");
                }
            }
        }
        // end hacky data association

        // --- 5. HTML Display of Visit Summary ---
        $schoolname = htmlspecialchars(getCompanyName($school_id_safe) ?? 'N/A');
        $visit_date = htmlspecialchars($visitrow['d']);
        
        echo "<tr bgcolor='{$bgcolor}'>";
        echo "<td class='copy'><a href='viewcompany.php?id={$school_id_safe}'>{$schoolname}</a></td>";
        echo "<td class='copy'>{$visit_date}</td>";
        echo "<td class='copy'><a href='billingworksheet.php?d=" . urlencode($visit_date) . "&schoolid=" . urlencode($school_id_safe) . "'>View Worksheet</a></td>";
        echo "<td class='copy'><table border='1' cellpadding='2' cellspacing='0'>";
        ?>
        <tr>
        <th class='copy'>ID</th>
        <th class='copy'>Type </th>
        <th class='copy'>Date Uploaded</th>
        <th class='copy'>Uploader </th>
        <th class='copy'>School Rep Name </th>
        <th class='copy'>ESI Rep Name </th>
        <th class='copy'>V? </th>
        <th class='copy'>P? </th>
        </tr>
        <?php
        // --- 6. HTML Display of Individual Uploads (Nested Table) ---
        foreach ($arr_uploads as $t) {
            $type = $t["type"] ?? '';
            $upload_id = (int)($t["id"] ?? 0);
            
            if (!$type) continue;
            
            $fieldid = db_query_first_cell("
                SELECT value FROM appuploaddata 
                WHERE uploadid = '{$upload_id}' 
                AND name IN ('serviceid', 'servicecallid', 'servideid', 'drillid') 
            ");
            
            $urlname = "";
            $itemurl = "";
            $fieldid_safe = db_escape($fieldid);

            if ($type === "sc") {
                $urlname = "appservicecall.php";
                $itemurl = "<a href='editservicecall.php?servicecallid={$fieldid_safe}'>SC #{$fieldid_safe}</a>";
            } elseif ($type === "ni") {
                $urlname = "appnewinstall.php";
                $itemurl = "<a href='editservicecall.php?servicecallid={$fieldid_safe}'>NI #{$fieldid_safe}</a>";
            } elseif ($type === "drill") {
                $urlname = "appdrill.php";
                $itemurl = "<a href='editdrill.php?drillid={$fieldid_safe}'>Drill #{$fieldid_safe}</a>";
            } else {
                 continue;
            }
            
            echo "<tr>";
            echo "<td class='copy' valign='top'><a href='{$urlname}?id={$upload_id}'>{$upload_id}</a></td><td class='copy'>{$itemurl}</td> ";
            echo "<td class='copy'>" . htmlspecialchars($t['dateuploaded'] ?? '') . "</td>";
            echo "<td class='copy'>" . htmlspecialchars($t['uploader'] ?? '') . "</td>";
            echo "<td class='copy'>" . htmlspecialchars($t['name'] ?? '') . "</td>";
            echo "<td class='copy'>" . htmlspecialchars($t['esi_repname'] ?? '') . "</td>";
            echo "<td class='copy'>" . htmlspecialchars($t['version'] ?? '') . "</td>";
            echo "<td class='copy'>" . htmlspecialchars($t['frompending'] ?? '') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "</td>";
        echo "</tr>";
    }
}
?>
</table>

   <?php include "ssi/footer.php"; ?>
       
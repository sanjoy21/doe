<?php

include "mysql.php";

$rrdate = getsetting('rosterreceiveddate');

$iscorp_safe = is_numeric($session_iscorp) ? (int)$session_iscorp : addslashes($session_iscorp);
$rrdate_safe = addslashes($rrdate);

// Build SQL query to find classes where roster is pending but class is complete
$sql = "SELECT class.*, companyname, schoolcode, company_esi.iscorp 
        FROM company_esi, class 
        WHERE iscorp = '{$iscorp_safe}' 
        AND rosterreceived = 0 
        AND companyid = company_esi.id 
        AND startdate < NOW() 
        AND class.deleted = 0 
        AND confirmdate IS NOT NULL 
        AND startdate > '{$rrdate_safe}' 
        ORDER BY class.startdate";

$res = db_query_rows($sql);

// --- CSV Output Logic ---
if ($xls) {
    // Generate CSV instead of Excel
    $filename = "report_" . ($table ?? '') . "_" . time() . ".csv";
    
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // Write UTF-8 BOM for Excel compatibility
    fwrite($output, "\xEF\xBB\xBF");
    
    // Write Header Row
    $header = [
        "training date",
        "class #",
        "school/company name",
        "roster received"
    ];
    
    fputcsv($output, $header);
    
    // Write Data Rows
    foreach ($res as $r) {
        $start_date_display = fixdatefordisplay($r['startdate'] ?? '');
        $class_id = $r["id"] ?? '';
        $company_name = $r["companyname"] ?? '';
        $roster_received_display = isset($r["rosterreceived"]) && $r["rosterreceived"] ? "Y" : "N";
        
        // Prepare data row
        $rowData = [
            $start_date_display,
            $class_id,
            $company_name,
            $roster_received_display
        ];
        
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
    
    fclose($output);
    exit();
}
// --- HTML Output Logic ---
else {
    // Assumed to include standard HTML opening tags and body start
    include "ssi/top.php"; 
?>        
<table cellpadding="3" cellspacing="0" border="1" width="100%">

<tr>
    <th class='copy'>class date</th>
    <th class='copy'>class #</th>
    <th class='copy'>location</th>
    <th class='copy'>returned?</th>
</tr>

<?php 
foreach ($res as $r) {
    $class_id = $r['id'];
    $company_id = $r["companyid"];
    
    $start_date_display = fixdatefordisplay($r['startdate']);
    $company_name_display = htmlspecialchars($r["companyname"]);
    $roster_received_display = ($r["rosterreceived"]) ? "Y" : "N";
    
    $class_link = "class_detail.php?id=" . urlencode($class_id);
    $company_link = "viewcompany.php?id=" . urlencode($company_id);
?>
<tr>
    <td valign='top' class='copy'><a href='<?php echo $class_link; ?>'><?php echo htmlspecialchars($start_date_display); ?></a></td>
    <td valign='top' class='copy'><?php echo htmlspecialchars($class_id); ?></td>
    <td valign='top' class='copy'><a href='<?php echo $company_link; ?>'><?php echo $company_name_display; ?></a></td>
    <td valign='top' class='copy'><?php echo htmlspecialchars($roster_received_display); ?></td>
</tr>
<?php } ?>
</table>
        <?php
        }
        ?>
          
          <br><br><br>
          <?php include "ssi/footer.php" ; ?>
          
          </span>
          </td>
          <td valign="top" width="15"><img src="images/dotclear.gif" width="10"></td>
        </tr>
    </table>
<br><br>
</div>
</body>
</html>
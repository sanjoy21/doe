<?php

include "mysql.php";

$iscorp_safe = $session_iscorp;

$sql = "SELECT id AS companyid, companyname, schoolcode, address, city, borough, zip, campusid, contactname, contactemail, accountmanager, isala 
        FROM company_esi 
        WHERE iscorp = '{$iscorp_safe}' AND deleted = 0 
        ORDER BY companyname";
$res = db_query_rows($sql);

// --- CSV Output Logic ---
if ($xls) {
    // Generate CSV instead of Excel
    $filename = "report_numresponders.csv";
    
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // Write UTF-8 BOM for Excel compatibility
    fwrite($output, "\xEF\xBB\xBF");
    
    // Write Header Row
    $header = [
        "school",
        "schoolcode",
        "contact name",
        "contact email",
        "account manager",
        "ala",
        "group/campus",
        "address",
        "number of trained responders",
        "last expiration date"
    ];
    
    fputcsv($output, $header);
    
    // Write Data Rows
    foreach ($res as $r) {
        $company_id = $r['companyid'];
        
        // Sub-query to get total responders count and MAX expiration date (2 years after MAX training date)
        $sub_sql = "SELECT COUNT(r.responderid) AS numresponders, 
                    CASE 
                        WHEN MAX(rt.trainingdate) > '2001-01-01' THEN DATE_ADD(MAX(rt.trainingdate), INTERVAL 2 YEAR) 
                        ELSE NULL 
                    END AS maxdate 
                    FROM company_esi c 
                    LEFT JOIN responders_esi r ON r.clientid = c.id AND r.deleted = 0 
                    LEFT JOIN responder_training_dates rt ON r.responderid = rt.responderid 
                    WHERE c.iscorp = '{$iscorp_safe}' AND c.id = {$company_id} 
                    GROUP BY c.id";
        $srow = db_query_first($sub_sql);

        // Get the count of *currently valid* responders (assuming getCurrentResponders handles filtering by expiration)
        $num = count(getCurrentResponders($company_id));
        
        // Data points for CSV row
        $company_name = $r["companyname"] ?? '';
        $school_code = $r["schoolcode"] ?? '';
        $contact_name = $r["contactname"] ?? '';
        $contact_email = $r["contactemail"] ?? '';
        $account_manager = isset($r["accountmanager"]) ? getUserName($r["accountmanager"]) : '';
        $is_ala = isset($r["isala"]) && $r["isala"] ? "Yes" : "No";
        $campus_name = isset($r["campusid"]) ? getCampusName($r["campusid"]) : '';
        $full_address = ($r["address"] ?? '') . ", " . ($r['city'] ?? '') . " " . ($r['borough'] ?? '') . " " . ($r['zip'] ?? '');
        $max_date_display = isset($srow['maxdate']) && $srow['maxdate'] ? date("m/d/Y", strtotime($srow['maxdate'])) : "";
        
        // Prepare data row
        $rowData = [
            $company_name,
            $school_code,
            $contact_name,
            $contact_email,
            $account_manager,
            $is_ala,
            $campus_name,
            $full_address,
            $num,
            $max_date_display
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
    include "ssi/top.php"; 
?>        
<!--start center content-->
<table cellpadding="3" cellspacing="0" border="1" width="100%">

<tr>
    <th class='copy'>school</th>
    <th class='copy'>schoolcode</th>
    <th class='copy'>address</th>
    <th class='copy'>contact name</th>
    <th class='copy'>contact email</th>
    <th class='copy'>account manager</th>
    <th class='copy'>ALA?</th>
    <th class='copy'>group/campus</th>
    <th class='copy'>num responders</th>
    <th class='copy'>last expiration date</th>
<?php 
foreach ($otherfields as $o) { 
    $o_safe = htmlspecialchars($o);
?>
    <th class='copy'><?php echo $o_safe; ?></th>
<?php 
} 

foreach ($res as $r) {
$company_id = $r['companyid'];

    // Sub-query (repeated from Excel logic for consistency)
    $sub_sql = "SELECT COUNT(r.responderid) AS numresponders, 
                CASE 
                    WHEN MAX(rt.trainingdate) > '2001-01-01' THEN DATE_ADD(MAX(rt.trainingdate), INTERVAL 2 YEAR) 
                    ELSE NULL 
                END AS maxdate 
                FROM company_esi c 
                LEFT JOIN responders_esi r ON r.clientid = c.id AND r.deleted = 0 
                LEFT JOIN responder_training_dates rt ON r.responderid = rt.responderid 
                WHERE c.iscorp = '{$iscorp_safe}' AND c.id = {$company_id} 
                GROUP BY c.id";
    $srow = db_query_first($sub_sql);
    
    $num = count(getCurrentResponders($company_id));

    $company_name = htmlspecialchars($r["companyname"]);
    $school_code = htmlspecialchars($r["schoolcode"]);
    $full_address = htmlspecialchars(($r["address"]) . ", " . $r["city"] . " " . $r["borough"] . " " . $r["zip"]);
    $contact_name = htmlspecialchars($r["contactname"]);
    $contact_email = htmlspecialchars($r["contactemail"]);
    $account_manager = htmlspecialchars(getUserName($r["accountmanager"]));
    $is_ala = ($r["isala"]) ? "Yes" : "No";
    $campus_name = htmlspecialchars(getCampusName($r["campusid"]));
    $max_date_display = ($srow['maxdate']) ? date("m/d/Y", strtotime($srow['maxdate'])) : "&nbsp;";
    
    $view_link = "viewcompany.php?id=" . urlencode($company_id);
?>
<tr>
    <td valign='top' class='copy'><a href='<?php echo $view_link; ?>'><?php echo $company_name; ?></a></td>
    <td valign='top' class='copy'><a href='<?php echo $view_link; ?>'><?php echo $school_code; ?></a></td>
    <td valign='top' class='copy'><a href='<?php echo $view_link; ?>'><?php echo $full_address; ?></a></td>
    <td valign='top' class='copy'><?php echo $contact_name; ?></td>
    <td valign='top' class='copy'><?php echo $contact_email; ?></td>
    <td valign='top' class='copy'><?php echo $account_manager; ?></td>
    <td valign='top' class='copy'><?php echo $is_ala; ?></td>
    <td valign='top' class='copy'><?php echo $campus_name; ?></td>
    <td valign='top' class='copy'><?php echo htmlspecialchars($num); ?></td>
    <td valign='top' class='copy'><?php echo $max_date_display; ?></td>
</tr>
<?php 
} 
?>
</table>
        <?php } ?>
          <br><br><br>
          <!--end center content-->
          <!--end center content-->
<?php include "ssi/footer.php" ; ?>
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
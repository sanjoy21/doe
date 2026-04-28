<?php 
require_once('mysql.php');

// --- 1. Safely Retrieve and Initialize Variables ---
$psal = $_REQUEST['psal'] ?? null;
$xls = $_REQUEST['xls'] ?? null;
$db_link = $GLOBALS['link'] ?? null; // Get database link for escaping

// Build PSAL filter strings
$psalstr = $psal ? "location = 'PSAL'" : "( location <> 'PSAL' ) ";
$psalstrdispl = $psal ? "PSAL" : "Non PSAL";

// --- 2. Query Client IDs (Schools) matching criteria ---
$sql_cids = "SELECT DISTINCT( company_esi.id ) 
             FROM aed_esi, company_esi 
             WHERE showsondrillreports = 1 
               AND aed_esi.clientid = company_esi.id 
               AND ( serial LIKE '0%' OR serial LIKE '1%' ) 
               AND iscorp = 0 
               AND aed_esi.deleted = 0 
               AND outofservice = 0 
               AND aedmissing = 0 
               AND aedstolen = 0 
               AND {$psalstr}";
$cids = db_query_array($sql_cids, "id", "id");

// --- 3. CSV Output Logic ---
if( $xls )
{
    // Set headers for CSV download
    $filename = "fr2left.csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // Add UTF-8 BOM for Excel compatibility
    fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
    
    // Write Header Row
    $headers = array(
        "School",
        "Upcoming Class",
        "Field Rep",
        "Serial(s)"
    );
    fputcsv($output, $headers);
             
    foreach( $cids as $cid )
    {
        $cid_safe = (int)$cid;

        // Fetch Company/School Details
        $crow = getCompanyRow( $cid_safe ) ?? array();

        // Fetch Upcoming Class
        $sql_upcoming = "SELECT id, startdate FROM class 
                         WHERE companyid = {$cid_safe} 
                           AND startdate > NOW() 
                           AND deleted = 0 
                         ORDER BY startdate LIMIT 1";
        $upcoming = db_query_first( $sql_upcoming ) ?? array();
        
        $upcomingstr = "";
        if (!empty($upcoming['startdate'])) {
            // Assumes getFormattedDateWTime is defined
            $formatted_date = getFormattedDateWTime( $upcoming["startdate"] ) ?? '';
            $upcomingstr = " {$upcoming['id']} ({$formatted_date})";
        }
        
        // Fetch Serials
        $sql_serials = "SELECT serial FROM aed_esi 
                        WHERE aed_esi.deleted = 0 
                          AND aedmissing = 0 
                          AND aedstolen = 0 
                          AND {$psalstr} 
                          AND clientid = '{$cid_safe}' 
                          AND ( serial LIKE '0%' OR serial LIKE '1%' )";
        $serials = db_query_rows( $sql_serials ) ?? array();

        $sarr = array();
        foreach( $serials as $s )
        {
            if (!empty($s['serial'])) {
                $sarr[] = $s['serial'];
            }
        }
        
        // Fetch Field Reps
        $responderarr = getTrainersForZip( $crow['zip'] ?? '' ) ?? array();
        $responder = array();
        if (is_array($responderarr)) {
            foreach( $responderarr as $r )
            {
                if (!empty($r['name'])) {
                    $responder[] = $r['name'];
                }
            }
        }
        $fieldrep = implode(", ", $responder);
        
        // Prepare row data
        $row_data = array(
            $crow['companyname'] ?? 'N/A',
            $upcomingstr,
            $fieldrep,
            implode(", ", $sarr)
        );
        
        fputcsv($output, $row_data);
    }
    
    fclose($output);
    exit;
}

// --- 4. HTML Output Logic ---
?>
<?php include "ssi/top.php"; ?>

<span class="page-head">Remaining <?php echo htmlspecialchars($psalstrdispl); ?> FR2s</span><br><br><br clear="all">

<a href='fr2left.php?xls=1&psal=<?php echo htmlspecialchars($psal); ?>'>View As CSV</a>
<table class="table2">
<tr>
<th class="left">School</th>
<th class="left">Upcoming Class</th>
<th class="left">Field Rep</th>
<th class="left">Serial(s)</th>
</tr>
<?php
foreach( $cids as $cid )
{
    $cid_safe = (int)$cid;
    $crow = getCompanyRow( $cid_safe ) ?? array();

    // Fetch Upcoming Class (HTML version links to class detail)
    $sql_upcoming_html = "SELECT id, startdate FROM class 
                          WHERE companyid = {$cid_safe} 
                            AND startdate > NOW() 
                            AND deleted = 0 
                          ORDER BY startdate LIMIT 1";
    $upcoming = db_query_first( $sql_upcoming_html ) ?? array();
    
    $upcomingstr = "";
    if (!empty($upcoming['startdate'])) {
        $formatted_date = getFormattedDateWTime( $upcoming["startdate"] ) ?? '';
        $upcomingstr = "<a href='class_detail.php?id=" . (int)$upcoming['id'] . "'>{$formatted_date}</a>";
    }
    
    // Fetch Serials (HTML version links to aed edit page)
    $sql_serials_html = "SELECT aedid, serial FROM aed_esi 
                         WHERE aed_esi.deleted = 0 
                           AND aedmissing = 0 
                           AND aedstolen = 0 
                           AND {$psalstr} 
                           AND clientid = '{$cid_safe}' 
                           AND ( serial LIKE '0%' OR serial LIKE '1%' )";
    $serials = db_query_rows( $sql_serials_html ) ?? array();
    $sarr = array();
    foreach( $serials as $s )
    {
        $aedid = (int)($s['aedid'] ?? 0);
        $serial_name = htmlspecialchars($s['serial'] ?? '');
        if ($aedid > 0) {
            $sarr[] = "<a href='editaed.php?aedid={$aedid}'>{$serial_name}</a>";
        } else {
            $sarr[] = $serial_name;
        }
    }

    // Fetch Field Reps
    $responderarr = getTrainersForZip( $crow['zip'] ?? '' ) ?? array();
    $responder = array();
    if (is_array($responderarr)) {
        foreach( $responderarr as $r )
        {
            if (!empty($r['name'])) {
                $responder[] = $r['name'];
            }
        }
    }
    $fieldrep = implode(", ", $responder);
?>
<tr>
<td class="left"><a href="viewcompany.php?id=<?php echo $crow['id'] ?? 0; ?>"><?php echo htmlspecialchars($crow['companyname'] ?? 'N/A'); ?></a></td>
<td class="left"><?php echo $upcomingstr; ?></td>
<td class="left"><?php echo htmlspecialchars($fieldrep); ?></td>
<td class="left"><?php echo implode( ", ", $sarr ); ?></td>
</tr>
<?php } ?>
</table>

<?php include "ssi/footer.php" ; ?>
</body>
</html>
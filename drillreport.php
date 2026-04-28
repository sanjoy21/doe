<?php 
require "mysql.php";

// Get external flags/variables
$id = $_REQUEST['id'] ?? null; // Not explicitly used in the query generation, but safe to pull.
$fieldfrom = $_REQUEST['fieldfrom'] ?? null;
$fieldto = $_REQUEST['fieldto'] ?? null;
$needsaed = $_REQUEST['needsaed'] ?? false;
$battreq = $_REQUEST['battreq'] ?? false;
$receivednotcompleted = $_REQUEST['receivednotcompleted'] ?? false;
$notcompleted = $_REQUEST['notcompleted'] ?? false;
$concat = $_REQUEST['concat'] ?? false;
$xls = $_REQUEST['xls'] ?? false;

// Global/Session variables (assumed to be available)
// Use safe access with null coalescing for user data and session flags
$thisusersrow = $thisusersrow ?? [];
$session_iscorp = $session_iscorp ?? 0;

// Get the database connection link for safe queries
$db_link = $GLOBALS['link'] ?? $link; 

$thetable = "drill";
$table = "drill";

// Fields to display in the report
$otherfields = array( 
    "drillid", "participants", "score", "nextdate", "comments", 
    "schoolcode", "zip", "serial", "inspector", "lastnotified" 
);
$extrafields = ", schoolcode, zip";
$lj = ""; // LEFT JOIN initialization
$extra = ""; // WHERE clause initialization

// If $concat is set, use GROUP_CONCAT to merge serial numbers for AEDs attached to the drill
if( $concat ) {
 $extrafields .= ", GROUP_CONCAT( ' ', ats.serial ) AS serial ";
} else {
    // If not concatenating, the field 'serial' is simply included in the main SELECT
    $extrafields .= ", ats.serial";
}

$datefield = $thetable."date";

// --- Build the WHERE clause based on filters ---

// Date range filtering (assuming fixdate() exists and returns a properly formatted SQL date)
if( $fieldfrom ) {
 $tm = fixdate( $fieldfrom ); 
 $extra .= " AND {$datefield} >= '{$tm}' ";
}
if( $fieldto ) {
 $tm = fixdate( $fieldto ); 
 $extra .= " AND {$datefield} <= '{$tm}' ";
}

// Needs AED flag
if( $needsaed ) {
 $extra .= " AND needsmoreaed = 1 ";
 $otherfields[] = "dateaedsent";
}
// Battery Request flag
if( $battreq ) {
 $extra .= " AND batteryrequest > '' ";
 $otherfields[] = "batteryrequest";
}

// Received but Not Completed flag
if( $receivednotcompleted ) {
 $extra .= " AND ( completed = 0 AND received = 1 ) ";
}

// Trainer visibility filtering by zip code (assuming getZips() exists and is safe)
if( ($thisusersrow["usertype"] ?? null) == "trainer" ) {
    $visible_zips = getZips( $thisusersrow );
 $extra .= ($thisusersrow["visiblezips"] && $visible_zips) ? 
        " AND company_esi.zip IN ( " . $visible_zips . " ) " : "";
}

// LEFT JOIN for AED serial numbers (aed_to_drill)
$lj .= " LEFT JOIN aed_to_drill ats ON ats.drillid = t.drillid ";

// Group by drill ID if concatenating serial numbers
if( $concat ) {
 $extra .= " GROUP BY drillid ";
}

// Logic for Completed/Not Completed status
$notcompletedstr = ($receivednotcompleted || $notcompleted) ? " AND completed = 0 " : " AND completed <> 0 ";

// --- Final SQL Query Construction ---
$sql = ( "SELECT 
                t.*, companyname, schoolcode {$extrafields}, company_esi.iscorp 
            FROM 
                company_esi, {$table} t {$lj} 
            WHERE 
                iscorp = '{$session_iscorp}' 
                {$notcompletedstr} 
                AND companyid = company_esi.id 
                AND showsondrillreports = 1 
                {$extra} 
            ORDER BY 
                {$datefield}" );

// echo( "<font color='black'>{$sql}</font>" ); // Debugging

$res = db_query_rows( $sql );


// --- Output Logic: CSV vs. HTML ---
if( $xls ) {
    // --- CSV Output (Replacing Spreadsheet/Excel/Writer) ---

    $filename = "report_{$table}.csv"; // Use .csv extension for true CSV output

    header("Content-Disposition: attachment; filename=\"{$filename}\"");
    header("Content-Type: text/csv");
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

    $output = fopen('php://output', 'w');

    // CSV Header Row
    $csv_header = array("school", "schoolcode", "date", "call number");
    foreach( $otherfields as $o ) {
        $csv_header[] = $o;
    }
    fputcsv($output, $csv_header);

    // CSV Data Rows
    foreach( $res as $r )
    {
        $row_data = [];
        
        $row_data[] = $r["companyname"] ?? '';
        $row_data[] = $r["schoolcode"] ?? '';
        $row_data[] = $r[$datefield] ?? '';
        $row_data[] = $r["callnumber"] ?? '';
        
        foreach( $otherfields as $o ) {
            $value = $r[$o] ?? '';
            // Prepend 'D' if field is 'drillid' (original logic)
            if ($o == "drillid") {
                $value = "D" . $value;
            }
            $row_data[] = str_replace(["\r", "\n"], ['\\r', '\\n'], $value); // Clean newlines for CSV
        }
        fputcsv($output, $row_data);
    }
    fclose($output);
    exit; // Terminate script after sending file

} else {
// --- HTML Output ---
?>
<?php include "ssi/top.php"; ?>
<!--start center content-->
<table cellpadding="3" cellspacing="0" border="1" width="100%">

<tr>
    <th class='copy'>school</th>
    <th class='copy'>schoolcode</th>
    <th class='copy'>date</th>
<?php foreach( $otherfields as $o ) { ?>
<th class='copy'><?php echo htmlspecialchars($o); ?></th>
<?php } ?>
</tr>
<?php 
foreach( $res as $r )
{
    $company_id_safe = htmlspecialchars($r["companyid"] ?? 0);
    $company_name_safe = htmlspecialchars($r["companyname"] ?? '');
    $school_code_safe = htmlspecialchars($r["schoolcode"] ?? '');
    $date_value_safe = htmlspecialchars($r[$datefield] ?? '');
?>
<tr>
    <td valign='top' class='copy'>
        <a href='viewcompany.php?id=<?php echo $company_id_safe; ?>'>
            <?php echo $company_name_safe; ?>
        </a>
    </td>
    <td valign='top' class='copy'>
        <a href='viewcompany.php?id=<?php echo $company_id_safe; ?>'>
            <?php echo $school_code_safe; ?>
        </a>
    </td>
    <td valign='top' class='copy'><?php echo $date_value_safe; ?></td>
    
    <?php foreach( $otherfields as $o ) { 
        $value = $r[$o] ?? '';
        // Prepend 'D' if field is 'drillid' (original logic)
        if ($o == "drillid") {
            $value = "D" . $value;
        }
    ?>
<td class='copy' valign='top'><?php echo nl2br(htmlspecialchars($value)); ?></td>
    <?php } ?>
</tr>
<?php } ?>
</table>
<?php } ?>

 <br><br><br>
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

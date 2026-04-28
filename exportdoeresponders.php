<?php
require "mysql.php";

// Safely retrieve assumed global/external variables
$xls = $_REQUEST['xls'] ?? ($xls ?? null);
$data = $_REQUEST['data'] ?? ''; // Used for filename, set to empty if not provided
$db_link = $GLOBALS['link'] ?? $link; 

// --- 1. XLS Export Headers (if $xls is set) ---
if( $xls ) {
    header( "Content-type: application/vnd.ms-excel" );
    header("Content-Transfer-Encoding: binary");
    
    // PHP 8.2 Fixes: Use standard superglobals and robust header setting
    $user_agent = strtolower ($_SERVER["HTTP_USER_AGENT"] ?? '');
    
    // Create a safe, unique filename
    $filename = "report_" . htmlspecialchars($data) . time() . ".xls";
    
    if ((is_integer (strpos($user_agent, "msie"))) && (is_integer (strpos($user_agent, "win")))) {
        // Legacy MSIE header handling
        header( "Content-Disposition: filename=" . basename($filename) . ";" );
    } else {
        // Standard attachment header
        header( "Content-Disposition: attachment; filename=" . basename($filename) . ";" );
    }
    // Note: The rest of the script output will be the HTML table structure, which is how 
    // it functions as an XLS download in this method.
}

// --- 2. Database Query ---
$sql = "SELECT 
            r.firstname, r.lastname, r.responderid, r.filenumber, r.pmsid, 
            c.companyname, c.schoolcode 
        FROM 
            responders_esi r
        JOIN 
            company_esi c ON c.id = r.clientid 
        WHERE 
            c.iscorp = 0 
            AND c.deleted = 0 
            AND r.deleted = 0 
        ORDER BY 
            r.lastname, r.firstname";

// Note: The original code used $result, but the foreach loop uses $results. We use $results for consistency.
$results = db_query_rows( $sql );

if ($xls) {
    // If exporting, we don't wrap the table in the full HTML/HEAD/BODY tags, as it interferes
    // with the Excel file structure. We output only the table, but since the original script 
    // includes the full HTML, we follow the original structure when generating the report.
    // If $xls is true, the headers are already sent, so we output the table structure as is.
}
?>

<!DOCTYPE html public "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>Report</title>
</head>

<body bgcolor="#ffffff">

<table cellpadding="3" cellspacing="0" border="1" width="100%">
 <tr>
<th>First Name</th>
<th>Last Name</th>
<th>School Name</th>
<th>DBN</th>
<th>Responder ID</th>
<th>File #</th>
<th><?php echo htmlspecialchars(getSchoolStr( "PMS ID" ) ?? 'PMS ID'); ?></th> 
</tr>

<?php 
// Use $results from the query execution
foreach( $results as $row )
{ 
    // PHP 8.2 Fixes: Quoted array keys and htmlspecialchars() for output
    $firstname_safe = htmlspecialchars($row['firstname'] ?? '');
    $lastname_safe = htmlspecialchars($row['lastname'] ?? '');
    $companyname_safe = htmlspecialchars($row['companyname'] ?? '');
    $schoolcode_safe = htmlspecialchars($row['schoolcode'] ?? '');
    $responderid_safe = htmlspecialchars($row['responderid'] ?? '');
    $filenumber_safe = htmlspecialchars($row['filenumber'] ?? '');
    $pmsid_safe = htmlspecialchars($row['pmsid'] ?? '');
?>
 <tr>
<td><?php echo $firstname_safe; ?></td>
<td><?php echo $lastname_safe; ?></td>
<td><?php echo $companyname_safe; ?></td>
<td><?php echo $schoolcode_safe; ?></td>
<td><?php echo $responderid_safe; ?></td>
<td><?php echo $filenumber_safe; ?></td>
<td><?php echo $pmsid_safe; ?></td>
</tr>
<?php } ?>
</table>

</body>
</html>
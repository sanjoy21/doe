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
    
    // PHP 8.2 Fixes: Use standard superglobals
    $user_agent = strtolower ($_SERVER["HTTP_USER_AGENT"] ?? '');
    
    // Create a safe, unique filename
    $filename = "report_" . htmlspecialchars($data) . time() . ".xls";
    
    if ((is_integer (strpos($user_agent, "msie"))) && (is_integer (strpos($user_agent, "win")))) {
        header( "Content-Disposition: filename=" . basename($filename) . ";" );
    } else {
        header( "Content-Disposition: attachment; filename=" . basename($filename) . ";" );
    }
}

// --- 2. Database Query ---
// Query for non-corporate responders with titles containing athletic/PE keywords, 
// excluding certain administrative/special education roles.
$sql = "SELECT 
            r.*, c.companyname, c.schoolcode 
        FROM 
            responders_esi r
        JOIN 
            company_esi c ON c.id = r.clientid 
        WHERE 
            c.iscorp = 0 
            AND c.deleted = 0 
            AND r.deleted = 0 
            AND (
                r.title LIKE '%Physical%' 
                OR r.title LIKE '%Coach%' 
                OR r.title LIKE '%Athletic%' 
                OR r.title LIKE BINARY '%AD%' 
                OR r.title LIKE BINARY '%PE%' 
            ) 
            AND r.title NOT LIKE '%special ed%' 
            AND r.title NOT LIKE '%admin%' 
            AND r.title NOT LIKE '%SPEECH%' 
            AND r.title NOT LIKE '%adult%' 
        ORDER BY 
            r.title, r.lastname, r.firstname";

$results = db_query_rows( $sql );

// --- 3. HTML/XLS Output Start ---
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>
<head>
 <title>Report</title>
</head>

<body bgcolor="#ffffff">

<table cellpadding="3" cellspacing="0" border="1" width="100%">
<tr>
<th>#</th>
<th>Title</th>
<th>First Name</th>
<th>Last Name</th>
<th>School Name</th>
<th>DBN</th>
<th>Responder ID</th>
<th>File #</th> 
<th><?php echo htmlspecialchars(getSchoolStr( "PMS ID" ) ?? 'PMS ID'); ?></th>
<th>Last Training Date</th>
<th>Expiration Date</th>
<th>Part of Extension</th>
</tr>

<?php
 $i = 1;
 foreach( $results as $row )
{
    $responder_id = (int)($row['responderid'] ?? 0);
    if ($responder_id === 0) continue; // Skip invalid responder ID
    
    // Assuming getResponderExpDate() and getResponderExpDatePlus() exist and return Y-m-d strings
 $exp = getResponderExpDate( $responder_id );
 
    // --- Date Filtering (Logic preserved from original script) ---
    // Only include records where expiration date is between '2017-09-01' and '2018-01-01'
 if( $exp < '2017-09-01' ) continue;
 if( $exp >= '2018-01-01' ) continue;
    
    // --- Extension Date Logic (Logic preserved from original script) ---
 $expplus = getResponderExpDatePlus( $responder_id );
 $expplus120 = "";
    
    // Conditional 120-day extension check (Dates preserved from original script)
 if($exp >= '2018-03-01' && $exp < '2018-08-01' )
 {
        // Calculate 120 days extension on the *plus* date
 $expplus120 = date( "Y-m-d", strtotime( $expplus . " + 120 days" ) );
 }

    // --- Data Sanitization for Output ---
    $i_safe = $i++;
    $title_safe = htmlspecialchars($row['title'] ?? '');
    $firstname_safe = htmlspecialchars($row['firstname'] ?? '');
    $lastname_safe = htmlspecialchars($row['lastname'] ?? '');
    $companyname_safe = htmlspecialchars($row['companyname'] ?? '');
    $schoolcode_safe = htmlspecialchars($row['schoolcode'] ?? '');
    $responderid_safe = htmlspecialchars($responder_id);
    $filenumber_safe = htmlspecialchars($row['filenumber'] ?? '');
    $pmsid_safe = htmlspecialchars($row['pmsid'] ?? '');
    $exp_safe = htmlspecialchars($exp);
    $expplus_safe = htmlspecialchars($expplus);
    $expplus120_safe = htmlspecialchars($expplus120);
?>
 <tr>
<td><?php echo $i_safe; ?></td>
<td><?php echo $title_safe; ?></td>
<td><?php echo $firstname_safe; ?></td>
<td><?php echo $lastname_safe; ?></td>
<td><?php echo $companyname_safe; ?></td>
<td><?php echo $schoolcode_safe; ?></td>
<td><?php echo $responderid_safe; ?></td>
<td><?php echo $filenumber_safe; ?></td>
<td><?php echo $pmsid_safe; ?></td>
<td><?php 
// Assuming $exp contains the last training date based on the columns
// The original script logic suggests the column should be LAST TRAINING DATE. 
// We output $exp, which is the base training date used for calculation.
echo $exp_safe; ?></td>
<td><?php echo $expplus_safe; ?></td>
<td><?php echo $expplus120_safe; ?></td>
 </tr>
<?php } ?>
</table>

</body>
</html>
<?php
require_once('mysql.php');

// Safely initialize external variables
$m = $m ?? null;
$d = $d ?? null;
$y = $y ?? null;

$currentusertype = $currentusertype ?? null;
$companyid_filter = $companyid ?? null; // Renamed to avoid collision with $companyid from get_companyid()
$session_id = $session_id ?? null;

// --- 1. Determine Target Date ---
// PHP 8.2 Fix: Use null coalescing operator and date()
$m = (int)($m ?: date('m'));
$d = (int)($d ?: date('d'));
$yr = (int)($y ?: date('Y'));

// Create the date string for the SQL query
$dtstr = date( "Y-m-d", mktime( 0,0,0,$m,$d,$yr ) );

// --- 2. Build SQL WHERE Clause ---
$extra = "1";
$safe_session_id = (int)$session_id;

// Filter by company (for non-trainers)
if( $currentusertype != "trainer" && $companyid_filter)
{
    // Use proper escaping for the company ID filter
    $safe_companyid_filter = mysqli_real_escape_string($GLOBALS['link'] ?? $link, $companyid_filter);
    $extra .= " AND class.companyid LIKE '{$safe_companyid_filter}'";
}
// Filter by trainer (for trainers)
else if( $currentusertype == "trainer" )
{
    $extra .= " AND trainer_to_class.trainerid = '{$safe_session_id}'";
}

// NOTE: The original code re-calculates $companyid here, which seems redundant
// if $companyid_filter was already set or if the purpose is just to restrict access.
// We keep the call but use a distinct variable name to avoid confusion.
$user_company_id = get_companyid($session_id);

// --- 3. Execute Query ---
$sql = "SELECT 
            class.id 
        FROM 
            class 
        LEFT JOIN 
            trainer_to_class ON trainer_to_class.classid = class.id
        WHERE 
            {$extra}
            AND DATE_FORMAT(startdate, '%Y-%m-%d') = '{$dtstr}'"; // Changed %c to %m for standard MONTH number format

// echo( $sql );
$classes = db_query_rows($sql);

?>

<?php include "ssi/top.php"; ?>

<div align="right">
<img src="images/button_print.gif"></div>
<BR CLEAR="ALL">


<?php
if (empty($classes)) {
    echo "<div style='padding:20px;font-style:italic;'>There are no classes scheduled for the selected day.</div>";
} else {
    foreach ($classes as $class) {
        $id = $class['id'] ?? null;
        
        if (!$id) continue;

        $forschedule = true;
        include "class_detail.php"; 

    } // end foreach classes 
} // end if not empty
?>

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
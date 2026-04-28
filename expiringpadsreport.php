<?php
require_once('mysql.php');

// Safely retrieve external variable $xls (assumed to control output format)
$xls = $_REQUEST['xls'] ?? ($xls ?? null);
$db_link = $GLOBALS['link'] ?? $link; 

if (!$xls) {
    // Only include the top SSI file if not generating an XLS/CSV file
    include "ssi/top.php";
}
?>

<table class="table2">
<tr>
<th class="left">Month</th>
<th class="left">Num Pads A Expiring</th>
 <th class="left">Num Pads B Expiring</th>
</tr>
<?php

// Initialization: Start date is the first day of the current year, 12 months ago
$dt = "2020-01-01"; // Retained original start date
$current_date = date("Y-m-d");
$enddt = date( "Y-m-d", strtotime( $current_date . " + 12 months" ) );

// Loop through months until the end date
while( $dt < $enddt )
{
    // Calculate the end of the current month interval
    $todt = date( "Y-m-d", strtotime( "{$dt} + 1 month" ) );
    
    // Safety: Date strings are safe in the SQL query since they originate from PHP's date functions
    
    // Query 1: Count Pad A expirations for the month interval
    $sql_numa = "SELECT 
                    COUNT(*) 
                FROM 
                    aed_esi a 
                JOIN 
                    company_esi c ON a.clientid = c.id 
                WHERE 
                    a.padaexpiration >= '{$dt}' 
                    AND a.padaexpiration < '{$todt}' 
                    AND c.iscorp = 0 
                    AND c.showsondrillreports = 1 
                    AND a.aedmissing = 0 
                    AND a.aedstolen = 0";
    $numa = db_query_first_cell( $sql_numa );

    // Query 2: Count Pad B expirations for the month interval
    $sql_numb = "SELECT 
                    COUNT(*) 
                FROM 
                    aed_esi a 
                JOIN 
                    company_esi c ON a.clientid = c.id 
                WHERE 
                    a.padbexpiration >= '{$dt}' 
                    AND a.padbexpiration < '{$todt}' 
                    AND c.iscorp = 0 
                    AND c.showsondrillreports = 1 
                    AND a.aedmissing = 0 
                    AND a.aedstolen = 0";
    $numb = db_query_first_cell( $sql_numb );

    // Output variables (securely escape counts)
    $numa_safe = htmlspecialchars($numa);
    $numb_safe = htmlspecialchars($numb);
    $dt_safe = htmlspecialchars($dt);
    $todt_safe = htmlspecialchars($todt);
?>
<tr> 
 <td class="left"><?php echo "{$dt_safe} - {$todt_safe}"; ?></td>
<td class="left"><?php echo $numa_safe; ?></td>
 <td class="left"><?php echo $numb_safe; ?></td>
</tr>
 <?php
    // Advance to the next month's start date
    $dt = date( "Y-m-d", strtotime( "{$dt} + 1 month" ) );
} ?>
</table>

<?php if( !$xls ){ ?>

<?php include "ssi/footer.php" ; ?>
</body>
</html>
<?php } ?>
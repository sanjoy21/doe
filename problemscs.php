<?php 
require_once "mysql.php"; 
include "ssi/top.php";
?>

<table border="1">
<tr><th>Service Call ID</th><th>Service Call Date</th><th>Assoc Drill Id</th><th>Assoc Drill Date</th><th>Should Be?</th></tr>
<?php
// --- 1. Fetch Service Calls with an Associated Drill ---
$scs = db_query_rows( "SELECT * FROM servicecall WHERE assocdrillid > 0" );

foreach( $scs as $s )
{
    // PHP 8.2 Fix: Quote array keys and cast to int for security
    $assoc_drill_id = (int)($s['assocdrillid'] ?? 0);
    $service_call_id = (int)($s['servicecallid'] ?? 0);
    $service_call_date = $s['servicecalldate'] ?? '';
    $company_id = (int)($s['companyid'] ?? 0);
    
    // --- 2. Fetch Associated Drill Data ---
    $drow = db_query_first( "SELECT * FROM drill WHERE drillid = '{$assoc_drill_id}'" );
    
    $drill_date = $drow['drilldate'] ?? 'N/A';
    
    // --- 3. Check for Date Mismatch ---
    if( $drill_date != $service_call_date )
    {
        // --- 4. Find the Drill that *Should* be Associated (based on matching date/company) ---
        $should = db_query_first_cell( "SELECT drillid FROM drill 
                                        WHERE drilldate = '{$service_call_date}' 
                                        AND companyid = '{$company_id}'" );
        
        // --- 5. Output Mismatch Row (with htmlspecialchars for safety) ---
        $sc_id_safe = htmlspecialchars($service_call_id);
        $sc_date_safe = htmlspecialchars($service_call_date);
        $ad_id_safe = htmlspecialchars($assoc_drill_id);
        $d_date_safe = htmlspecialchars($drill_date);
        $should_id_safe = htmlspecialchars($should);
        
        echo( "<tr>
                  <td><a href='editservicecall.php?servicecallid={$sc_id_safe}'>{$sc_id_safe}</a></td>
                  <td>{$sc_date_safe}</td>
                  <td>{$ad_id_safe}</td>
                  <td>{$d_date_safe}</td>
                  <td><a href='editdrill.php?drillid={$should_id_safe}'>{$should_id_safe}</a></td>
              </tr>" );
    }
}
?>
</table>

<?php include "ssi/footer.php"; ?>

</span>
</td>
<td valign="top" width="15"><img src="images/dotclear.gif" width="10"></td>
</tr>
</table>
<br><br>
</div>
</form>
</body>
</html>
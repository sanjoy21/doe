<?php 
include "mysql.php";

// Safely access external variable
$xls = $xls ?? false;

// --- CSV Processing ---
$handle = fopen("/tmp/f.csv", "r");
$allids = array();
$i = 0;

if ($handle) {
    // Note: The original condition `&& !$done` is removed as `$done` was not defined.
    while (($data = fgetcsv($handle, 9999, ",")) !== FALSE) { 
        $i++;

        // Skip the header row
        if( $i == 1 ) continue; 

        // Safely access the first column (Serial) in the $data array
        $serial = $data[0] ?? null;

        if ($serial) {
            // Find the client ID associated with the serial number
            $sid = db_query_first_cell( "select clientid from aed_esi where serial = '" . $serial . "'" );
            
            // Collect unique client IDs
            if ($sid) {
                $allids[$sid] = $sid;
            }
        }
    }
    fclose($handle);
}

// --- Output Headers (for Excel Download) ---
if( $xls )
{
    header('Content-type: application/vnd.ms-excel');
    header("Content-Disposition: attachment; filename=s.xls");
    header("Pragma: no-cache");
    header("Expires: 0"); 
    // The HTML table output below will be the content of the Excel file.
}
?>
<table>
<?php 
// --- Data Output ---
foreach( $allids as $id )
{
    // Fetch company and last drill data
    $crow = getCompanyRow( $id );
    $last = db_query_first( "Select * from drill where companyid = " . (int)$id . " order by drilldate desc limit 1" );
    
    // Safely access quoted array keys
    $companyname_safe = $crow["companyname"] ?? '';
    $address_safe = $crow["address"] ?? '';
    $city_safe = $crow["city"] ?? '';
    $state_safe = $crow["state"] ?? '';
    $zip_safe = $crow["zip"] ?? '';
    $last_drilldate_safe = $last["drilldate"] ?? 'N/A';
    $banned_status_safe = $bannedschoolids[$id] ?? 'No';
?>
<tr>
    <td><a href='viewcompany.php?id=<?php echo $id; ?>'><?php echo $id; ?></a></td>
    <td><?php echo $companyname_safe; ?></td>
    <td><?php echo "{$address_safe}, {$city_safe}, {$state_safe} {$zip_safe}"; ?></td>
    <td><?php echo $banned_status_safe; ?></td>
    <td><?php echo $zip_safe; ?></td>
    <td><?php echo $last_drilldate_safe; ?></td>
</tr>
<?php } ?>
</table>
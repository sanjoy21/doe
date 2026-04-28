<?php
require_once('mysql.php');
require_once('services.php');

// Safely initialize external variable
$rid = $rid ?? null;
?>

<?php
if( $rid )
{
    // Use the provided ID for lookup
    $arow = getAedRow( $rid );
    
    // Check if the row was found before attempting to update
    if ($arow) {
        $val = updateAED( $arow );
        echo( htmlspecialchars($val) );
    } else {
        echo( "Error: AED ID " . htmlspecialchars($rid) . " not found.<br>" );
    }
}

// Check for the specific master ID to trigger batch update
if( $rid == 11111 )
{
    // Query AEDs that have a building code but failed the last update attempt
    $rows = db_query_rows( "SELECT * FROM aed_esi WHERE buildingcode > '' AND lastupdateresult <> 'Success'" );
    
    echo( "<h3>Running Batch Update:</h3>" );
    foreach( $rows as $arow )
    {
        $val = updateAED( $arow );
        
        // PHP 8.2 Fix: Quote array key 'aedid'
        $aedid = htmlspecialchars($arow['aedid'] ?? 'N/A');
        $result_val = htmlspecialchars($val);
        
        echo( $aedid . ": " . $result_val . "<br>" );
    } 
    echo( "Batch update complete.<br>" );
}
?>
<form id="form1" name="form1" method="post" action="">

    AED ID: <input name="rid" type="text" value='<?= htmlspecialchars($rid ?? '') ?>'/>
    <br>
    <input type="submit" name="validate" value="Validate" />

</form>
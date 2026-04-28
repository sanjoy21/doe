<?php
require_once('mysql.php');
require_once('services.php');

// Safely initialize external variables from the form submission
// $rid = $rid ?? null;
$validate = $validate ?? false;
$validate2 = $validate2 ?? false;
?>

<?php
if( $validate2 )
{
    // Fetch responder row using the submitted ID
    $arow = getResponderRow( $rid );
    
    if ($arow) {
        $val = updateResponder2( $arow );
        echo( htmlspecialchars($val) );
    } else {
        echo( "Error: Responder ID " . htmlspecialchars($rid) . " not found.<br>" );
    }
}

if( $validate )
{
    // Fetch responder row using the submitted ID
    $arow = getResponderRow( $rid );
    
    if ($arow) {
        $val = updateResponder( $arow );
        echo( htmlspecialchars($val) );
    } else {
        echo( "Error: Responder ID " . htmlspecialchars($rid) . " not found.<br>" );
    }
}

// Check for the specific master ID to trigger batch update
if( $rid == 11111 )
{
    echo( "<h3>Running Batch Update:</h3>" );
    
    // Query responders that have buildingcode and pmsid set, but failed the last update attempt
    $rows = db_query_rows( "SELECT * FROM responders_esi WHERE buildingcode > '' AND pmsid > '' AND lastupdateresult <> 'Success' " );
    
    foreach( $rows as $arow )
    {
        $val = updateResponder( $arow );
        
        $responderid = htmlspecialchars($arow['responderid'] ?? 'N/A');
        $result_val = htmlspecialchars($val);
        
        echo( $responderid . ": " . $result_val . "<br>" );
    }
    echo( "Batch update complete.<br>" ); 
}
?>
<form id="form1" name="form1" method="post" action="">

    Responder ID: <input name="rid" type="text" value='<?= htmlspecialchars($rid ?? '') ?>' />
    <br>
    <input type="submit" name="validate" value="Validate" />
    <input type="submit" name="validate2" value="Validate 2" />

</form>
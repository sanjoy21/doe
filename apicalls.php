<?php 
$nologinrequired = 1;
include "mysql.php";

$whr = "";

// --- Build the WHERE clause securely ---
if( $method ) {
    $safe_method = mysqli_real_escape_string($link, $method);
    $whr .= " AND method = '{$safe_method}'";
}
if( $schoolid ) {
    $safe_schoolid = mysqli_real_escape_string($link, $schoolid);
    $whr .= " AND schoolid = '{$safe_schoolid}'";
}
if( $uploader ) {
    $safe_uploader = mysqli_real_escape_string($link, $uploader);
    $whr .= " AND uploader = '{$safe_uploader}'";
}
if( $dateafter ) {
    // Assuming dateafter is in a format suitable for direct comparison
    $safe_dateafter = mysqli_real_escape_string($link, $dateafter);
    $whr .= " AND dateuploaded >= '{$safe_dateafter}'";
}

$sql = "SELECT * FROM api_calls WHERE 1 {$whr} ORDER BY dateuploaded DESC";
$res = []; // Initialize result array

if( $whr )
{
    // Output SQL query for debugging (as in original code)
    echo( htmlspecialchars($sql) . "<br>" ); 
    $res = db_query_rows( $sql );
} 
?>
<?php 
$valid_functions = [ "doLogin", "doLogoff", "getSchools", "downloadData", "upload", "uploadServiceCall", "uploadDrill", "uploadNewInstall" ];
?>

<form method='get'>
    Method: 
    <select name='method'>
        <option value=''></option>
        <?php foreach( $valid_functions as $v ) { 
            $v_safe = htmlspecialchars($v);
        ?>
            <option <?= $v == $method ? "SELECTED" : "" ?> value='<?= $v_safe ?>'><?= $v_safe ?></option>
        <?php } ?>
    </select><br>
    
    Uploader: 
    <select name='uploader'>
        <option value=''></option>
        <?php
        // Fetch distinct uploaders safely
        $uploaders = db_query_array( "SELECT DISTINCT( uploader ) FROM api_calls", "uploader", "uploader" );
        foreach( $uploaders as $v ) { 
            $v_safe = htmlspecialchars($v);
        ?>
            <option <?= $v == $uploader ? "SELECTED" : "" ?> value='<?= $v_safe ?>'><?= $v_safe ?></option>
        <?php } ?>
    </select><br>
    
    School ID: <input type='text' name='schoolid' size='5' value="<?= htmlspecialchars($schoolid) ?>"><br>
    Date After: <input type='text' name='dateafter' size='10' value="<?= htmlspecialchars($dateafter) ?>"><br>
    
    <input type='submit' name='go' value='Go'>
</form>

<?php
echo( "<table border='1' cellpadding=2 cellspacing=0><tr>" );

$cols = [ "id", "method", "uploader", "schoolid", "dateuploaded", "postdata", "retval" ];

// Output table headers
foreach( $cols as $c ) {
    echo( "<th>" . htmlspecialchars($c) . "</th>" );
}
echo( "</tr>" );

// Output table rows
foreach( $res as $row )
{
    echo( "<tr>" );
    foreach( $cols as $c )
    {
        $cell_content = $row[$c];

        if( $c == "retval" )
        {
            $row_id_safe = htmlspecialchars($row['id']);
            
            // Note: Stripslashes is necessary here if data was added with magic_quotes or db_query_rows used addslashes,
            // but for modern PHP (8.2), this is usually only needed if the data was manually escaped upon insertion.
            $unformatted_content = nl2br( stripslashes( $cell_content ) );
            
            // Output the view link and the hidden div
            // IMPORTANT: The content inside the <div> is not passed through htmlspecialchars 
            // because nl2br is used, and the content is expected to be HTML/text output from an API call, 
            // which might contain formatting. If this content is NOT intended to be HTML, 
            // $unformatted_content should be passed through htmlspecialchars() before nl2br().
            echo( "<td><a href='#' onClick='return showThis( \"{$c}{$row_id_safe}\" )'>View</a><div id='{$c}{$row_id_safe}' style='display:none'>" . $unformatted_content . "</div></td>" );
        }
        else
        {
            // For other columns, format and output the content securely
            $content_safe = nl2br( htmlspecialchars( stripslashes( $cell_content ) ) );
            echo( "<td>" . $content_safe . "</td>" );
        }
    }
    echo( "</tr>" );
}
?>
</table>
<script>
function showThis( id )
{
    document.getElementById( id ).style.display = "block"; // Changed from "" to "block" for modern display
    return false;
}

</script>
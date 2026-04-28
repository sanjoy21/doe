<?php
require_once('mysql.php');

// Safely retrieve assumed global/external variables
$d = $_REQUEST['d'] ?? null;
$schoolid = $_REQUEST['schoolid'] ?? null;
$scid = $_REQUEST['scid'] ?? null;
$debug = $_REQUEST['debug'] ?? null;
$printable = $_REQUEST['printable'] ?? null;
$help = $_GET["help"] ?? null;

// Ensure date and IDs are safe for direct use in SQL (assuming $d is already in YYYY-MM-DD format)
$schoolid_safe = (int)$schoolid;
$scid_safe = (int)$scid;

// --- 1. Fetch Primary Uploads ---

// Get the drill record for the specific school and date
$drillrow = db_query_first( "SELECT * FROM appuploads WHERE type = 'drill' AND schoolid = '{$schoolid_safe}' AND DATE(dateinupload) = '{$d}'" );

$scrow = false; // Initialize service call row
$drill_id = (int)($drillrow['id'] ?? 0); // Safely get drill ID

// --- 2. Logic to find the associated Service Call (sc) record ---
if( $scid_safe > 0 )
{
    // If $scid is explicitly passed, fetch it
    $scrow = db_query_first( "SELECT * FROM appuploads WHERE id = {$scid_safe}" );
}
else
{
    // Attempt to find the service call associated with the drill
    if( $drill_id > 0 )
    {
        // 2a. Look up the drillid stored in the appuploaddata table
        $tmpdrillid = db_query_first_cell( "SELECT value FROM appuploaddata WHERE uploadid = '{$drill_id}' AND name = 'drillid'" );
        
        if( $tmpdrillid )
        {
            // 2b. Use assocdrillid to find the related servicecallid
            $scid_assoc = db_query_first_cell( "SELECT appid FROM servicecall WHERE assocdrillid = '{$tmpdrillid}'" );
            
            if( $scid_assoc )
            {
                // This is the sc record associated with the drill we're looking at
                $scrow = db_query_first( "SELECT * FROM appuploads WHERE id = {$scid_assoc}" );
            }
        }
    }
    
    // 2c. If no associated SC was found, fall back to finding any SC for the date/school
    if( !$scrow )
    {
        $scrow = db_query_first( "SELECT * FROM appuploads WHERE type = 'sc' AND schoolid = '{$schoolid_safe}' AND DATE(dateinupload) = '{$d}'" );
    }
}

// Fetch new install (ni) record
$nirow = db_query_first( "SELECT * FROM appuploads WHERE type = 'ni' AND schoolid = '{$schoolid_safe}' AND DATE(dateinupload) = '{$d}' ORDER BY dateuploaded DESC" );

// --- 3. Count Multiple Uploads and Display Warnings ---

// Count multiple drill uploads
$numdrills = db_query_array( "SELECT id FROM appuploads WHERE type = 'drill' AND schoolid = '{$schoolid_safe}' AND DATE(dateinupload) = '{$d}'", "id", "id" );

// Count multiple service call uploads
$numsc = db_query_array( "SELECT id FROM appuploads WHERE type = 'sc' AND schoolid = '{$schoolid_safe}' AND DATE(dateinupload) = '{$d}'", "id", "id" );

// Debugging output for query
if( $help ) {
    $help_sql = "SELECT id FROM appuploads WHERE type = 'sc' AND schoolid = '{$schoolid_safe}' AND DATE(dateinupload) = '{$d}'";
    echo htmlspecialchars($help_sql) . "<br>";
}

// Warning for multiple drill uploads
if( count( $numdrills ) > 1 && isOverallAdmin() )
{
    echo( "<font color='red'><b>Warning! There were more than one drills uploaded for this <a href='apiuploads.php?schoolid={$schoolid_safe}&dto={$d}+23:59:59&dfrom={$d}&Go=1&viewarch=1'>day</a>!</b></font>" ); 
    if( $help ) {
        foreach( $numdrills as $upload_id ) {
            echo( "<br><a href='appdrill.php?id={$upload_id}'>Drill ID: {$upload_id}</a>" );
        }
    }
}

// Warning for multiple service call uploads
if( count( $numsc ) > 1 && isOverallAdmin() )
{
    echo( "<font color='red'><b>Warning! There were more than one service calls uploaded for this <a href='apiuploads.php?schoolid={$schoolid_safe}&dto={$d}+23:59:59&dfrom={$d}&Go=1&viewarch=1'>day</a>!</b></font>" ); 
    if( $help ) {
        foreach( $numsc as $upload_id ) {
            echo( "<br><a href='appservicecall.php?id={$upload_id}'>Service Call ID: {$upload_id}</a>" );
        }
    }
}

// --- 4. Include the appropriate display scripts ---
$nosave = true; 

// Iterate through the fetched upload rows
foreach( array( $drillrow, $scrow, $nirow ) as $t )
{
    if( !($t["type"] ?? false) ) continue;
    
    $urlname = "";
    $upload_type = $t["type"];

    // Determine which script to include based on type
    if( $upload_type == "drill" ) {
        $urlname = "appdrill.php";
    } elseif( $upload_type == "sc" ) {
        $urlname = "appservicecall.php";
    } elseif( $upload_type == "ni" ) {
        $urlname = "appnewinstall.php";
    } else {
        continue; // Skip unknown types
    }

    $id = (int)($t["id"] ?? 0);
    
    if( $debug )
    {
        // Debug output
        echo "<pre>";
        print_r( $t );
        echo "</pre>";
        echo htmlspecialchars($urlname) . " : " . $id . "<br>";
    }

    // Include the display script for the found upload record
    if (file_exists($urlname) && $id > 0) {
        include $urlname;
    }
}

// --- 5. Print Script ---
if( $printable ) { 
?>
    <script language='javascript'>
        window.print();
    </script>
<?php 
} 
?>
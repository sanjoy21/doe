<?php
include "mysql.php"; 

require('vendor/autoload.php');

// Safely initialize external variables
$equipmentid = $equipmentid ?? null;
$allstatuses = $allstatuses ?? null;
$equipmentstatuses = $equipmentstatuses ?? []; // Assuming this is an array of statuses to print

$barcodes = array();

// --- 1. Determine which barcodes to print ---
if( $equipmentid )
{
    // Safety: Cast to integer
    $safe_equipmentid = (int)$equipmentid;
    
    // PHP 8.2 Fix: Ensure database query is secure
    $barcode = db_query_first_cell( "SELECT barcode FROM equipment WHERE id = {$safe_equipmentid}" );
    
    if ($barcode) {
        // Use the barcode value as both key and display value, as in original logic
        $barcodes[$barcode] = $barcode; 
    }
}


if( $allstatuses )
{
    // If all statuses are requested, use the provided array, removing "Created"
    $barcodes = $equipmentstatuses;
    // PHP 8.2 Fix: Use isset check before unsetting
    if (isset($barcodes["Created"])) {
        unset( $barcodes["Created"] );
    }
}

// --- 2. Layout Configuration ---
$hei = 94;
$wid = 250;
$imgwid = 200;
$imghei = 34;
$spacerwid = 20;

// --- 3. HTML and CSS Output ---
echo( "<style type='text/css'>
@page {
    margin: 0;
}
</style>" );
echo( "<br><br>" );
echo( "<table border=0 cellspacing=0>" );
echo( "<tr><td><img src='images/spacer.gif' height='5'></td></tr>" );

// --- 4. Barcode Generation and Printing Loop ---
$num = 0;
$generator = new Picqer\Barcode\BarcodeGeneratorJPG(); // Initialize generator outside loop

foreach( $barcodes as $key => $display )
{
    // $key is the barcode data, $display is the text below it
    
    if( !$num ) {
        echo( "<tr>" );
    }
    
    // Start cell for barcode
    echo( "<tD width='{$wid}' height='{$hei}' align='center'>" );
    
    // Generate barcode image data and embed via base64
    $barcode_image_data = $generator->getBarcode($key, $generator::TYPE_CODE_128);
    
    echo '<img border=0 width="' . $imgwid . '" height="' . $imghei . '" src="data:image/png;base64,' . base64_encode($barcode_image_data) . '">';
    
    // Display text (status/barcode) below the image
    // PHP 8.2 Fix: Use curly braces for clear variable interpolation in string
    echo( "<div style='padding-top: 10px' align='center'>{$display}</div>" ); 
    echo( "</td>" );
    
    $num++;
    
    // Check if three columns are done
    if( $num == 3 )
    {
        $num = 0;
        echo( "</tr>" );
    }
    
    // Add spacer column if it's not the last column in the row
    if( $num ) {
        echo( "<td><img height='{$hei}' width='{$spacerwid}' src='images/spacer.gif'></td>" );
    }
}

// --- 5. Finish the last row with empty cells if needed ---
while( $num > 0 && $num < 3 )
{
    // Add spacer column before the next empty cell (unless it's the first empty cell)
    if( $num < 3 ) {
        echo( "<td><img height='{$hei}' width='{$spacerwid}' src='images/spacer.gif'></td>" );
    }
    
    // Add the empty cell
    echo( "<td width='{$wid}'><img src='images/spacer.gif' width='{$imgwid}' height='{$imghei}' border=0></td>" );
    
    $num++;
}

// Ensure the table closes if the last row loop didn't close it
if ($num == 0) {
    echo( "</tr>" );
}

echo( "</table>" );

?>
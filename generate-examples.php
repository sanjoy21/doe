<?php

require('vendor/autoload.php');

// $key = '081231723897';
// echo( "<table><tr><tD>" );
// // This will output the barcode as HTML output to display in the browser
// $generator = new Picqer\Barcode\BarcodeGeneratorHTML();
// echo $generator->getBarcode($key, $generator::TYPE_CODE_128);

// echo( "</td></tr><tr><td>" );
// echo( "$key" ); 
// echo( "</td></tr></table>" );


// echo( "<Br><br>" );

$key = "abc1234567890";

// Start HTML table and cell for SVG output
echo( "<table><tr><td>" );

// Initialize the SVG barcode generator
$generatorSVG = new Picqer\Barcode\BarcodeGeneratorSVG();

// Generate the Code 128 barcode as SVG and echo it
echo $generatorSVG->getBarcode($key, $generatorSVG::TYPE_CODE_128);

// Close the cell and start the next for the key text
echo( "</td></tr><tr><td>" );

// Echo the key text
echo( "$key" ); 

// Close the table
echo( "</td></tr></table>" );

echo( "<br><br>" );

?>
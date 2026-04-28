<?php 

$ids = $ids;
$count = 0; 
$laststarty = 0;
$inst = "___________________";

setlocale(LC_MONETARY, 'en_US');
// require_once('mysql.php');
require_once('pdfstuff/fpdf.php');


$pdf = new FPDF();
$pdf->addPage();

$numCharsPerLine = 90; // The Number of Characters allowed per line
$toadd = 0; // add this if you want a border at the topp
$pdf->SetXY(0,10);
$pdf->SetTextColor(0,0,0); // brown
$fontsize = 12;
$pdf->SetFont('arial','',$fontsize);
// X AND Y ARE BACKWARDS FROM WHAT YOU ARE THINKING!!!!! 
$starty = 22;

$ids = array_unique( $ids );
foreach( $ids as $id )
{
    // Use quoted array keys
    $crow = getClassRow( $id );
    $comrow = getCompanyRow( $crow['companyid'] );
    $info = getClassInfo( $id );
    $jumpedto = getJumpingTo( $id );
    
    // Check if class info is valid/available
    if( !count( $info ) ) continue;

    $howmany = 2; // Print two copies of the label/paperwork per class
    for( $loop = 1; $loop <= $howmany ; $loop++ ) { 
        if( $count == 10 ) // New page logic (after 5 rows / 10 labels)
        {
            $starty = 22;
            $count = 0;
            $pdf->addPage();
            
            $numCharsPerLine = 90;
            $toadd = 0;
            $pdf->SetXY(0,10);
            $pdf->SetTextColor(0,0,0);
            $pdf->SetFont('arial','',$fontsize);
        }
        
        // Column logic: check if $count is even (left column) or odd (right column)
        if( $count % 2 == 0 )
        {
            $howfarover = 15; // Left column X position
            $laststarty = $starty; // Save Y position for the right column
        }
        else
        {
            $howfarover = 115; // Right column X position
            $starty = $laststarty; // Use the saved Y position
        }
        
        // --- PDF DRAWING ---
        
        $pdf->SetXY($howfarover, $starty);
        $pdf->SetFont('arial','UI',$fontsize);
        $pdf->Cell(87,12,"Class Paperwork", 0,1,'C');
        
        $starty += 4.8;
        $pdf->SetXY($howfarover, $starty);
        $pdf->SetFont('arial','',$fontsize);
        // Use quoted array keys
        $pdf->Cell(87,12,substr( $comrow['companyname'], 0, 30 ), 0,1,'C');
        
        $pdf->SetFont('arial','B',$fontsize+2);
        $starty += 4.8;
        $pdf->SetXY($howfarover, $starty);
        // Use quoted array keys
        $pdf->Cell(87,12,date( "D, M j g:i A", strtotime( $crow['startdate'] . " " . $crow['starttime'] ) ), 0,1,'C');
        
        $pdf->SetFont('arial','',$fontsize);
        $starty += 4.8;
        $pdf->SetXY($howfarover, $starty);
        // Use $id directly
        $pdf->Cell(87,12,"Class No:" . $id, 0,1,'C');
        
        $starty += 4.8;
        $pdf->SetXY($howfarover, $starty);
        $pdf->Cell(87,12,"", 0,1,'L'); // Empty row
        
        $starty += 4.8;
        $pdf->SetXY($howfarover, $starty);
        
        // Find Instructor Name (The original logic overwrote $inst in the loop)
        $instrows = getTrainers( $crow['id'] );
        $inst = "___________________";
        foreach( $instrows as $in_row )
        {
            // The original logic only kept the last trainer's name in $inst
            $inst = $in_row['first_name'] . " " . $in_row['last_name'];
        }
        $pdf->Cell(87,12,"Instructor: $inst", 0,1,'C');
        
        $starty += 4.8;
        $pdf->SetXY($howfarover, $starty);
        $pdf->SetFont('arial','',$fontsize-2);
        
        // Safely access Class Info array using quoted keys
        $bagsetValue = $info["Bagset"]["value"];
        $isJumping = $jumpedto ? "Yes" : "No";

        $pdf->Cell(87,12,"Bag Set: ". $bagsetValue . "           Is Jumping?: " . $isJumping , 0,1,'C');

        // Increment $starty for the next row (if it's a left column)
        $starty += 22.8; 
        
        // Crucial: Increment the overall counter
        $count++; 
    }
}


$pdf->Output( time().".pdf", "D" );

?>
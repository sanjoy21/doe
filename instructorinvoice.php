<?php 
// Set locale for monetary formatting (although money_format is deprecated, setlocale is still used)
setlocale(LC_MONETARY, 'en_US');

// Require the necessary PDF libraries
require_once('pdfstuff/fpdf.php');
require_once('fpdi/fpdi.php');

// Assumed variables for the script (these should be defined earlier in the application flow)
// $fromdate, $todate, $session_id, $thisusersrow, db_query_rows(), getCompanyRow()
// For this conversion, we assume they are available.
global $fromdate, $todate, $session_id, $thisusersrow;

// Initialize FPDI object
$pdf = new FPDI();

// --- Template Loading and Initial Page Setup ---
$pagecount = $pdf->setSourceFile('pdfs/Sample Invoice.pdf');
$tplidx = $pdf->importPage(1);
$pdf->addPage();

$numCharsPerLine = 90; // The Number of Characters allowed per line 
$toadd = 0; // Add this if you want a border at the top                                                                                                  
$pdf->SetXY(0, 10);
$pdf->useTemplate($tplidx, -3, $toadd);

// --- Font and Color Setup ---
$pdf->SetTextColor(0, 0, 0);
$fontsize = 10;
$pdf->SetFont('arial', '', $fontsize);

// --- Fill in User Name ---
$first_name = $thisusersrow["first_name"] ?? '';
$last_name = $thisusersrow["last_name"] ?? '';
$full_name = "{$first_name} {$last_name}";

// X AND Y ARE BACKWARDS FROM WHAT YOU ARE THINKING!!!!!                                                                                                  
$pdf->SetXY(50, 26);
$pdf->Cell(87, 12, $full_name, 0, 1, 'L');

// --- Initialization for Drill Line Items ---
$datex = 23; // X position for Date
$codex = 38; // X position for School Code/Company Name
$starty = 68; // Starting Y position for the first line item
$line_height = 4.8; // Vertical space between lines
$max_lines_per_page = 16; 
$count = 0;

// --- Fetch Drill Data ---
// Assuming db_query_rows safely handles date and session_id variables from the global scope
$drills = db_query_rows("select * from drill where donedate >= '{$fromdate}' and donedate <= '{$todate}' and doneby = '{$session_id}'");

// --- Process Drill Records ---
foreach ($drills as $d) {
    
    // Check if we need to start a new page
    if ($count == $max_lines_per_page) {
        // Reset coordinates and counter for the new page
        $datex = 23;
        $codex = 38;
        $starty = 68;
        $count = 0;

        // Import and use the template for the new page
        $tplidx = $pdf->importPage(1);
        $pdf->addPage();
        $pdf->SetXY(0, 10);
        $pdf->useTemplate($tplidx, -3, $toadd);
        
        // Re-apply font, color, and user name
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('arial', '', $fontsize);
        $pdf->SetXY(50, 26);
        $pdf->Cell(87, 12, $full_name, 0, 1, 'L');
    }
    
    // Fetch Company details for the drill
    $company = getCompanyRow($d["companyid"] ?? 0);

    // --- Write Data to PDF ---
    
    // Column 1: Date
    $done_date = $d["donedate"] ?? '';
    $formatted_date = !empty($done_date) ? date("m/d/y", strtotime($done_date)) : '';
    $pdf->SetXY(22, $starty);
    $pdf->Cell(87, 12, $formatted_date, 0, 1, 'L');
    
    // Column 2: School Code or Company Name (first 35 chars)
    $company_name = $company["companyname"] ?? '';
    $school_code = $company["schoolcode"] ?? '';
    $code_or_name = !empty($school_code) ? $school_code : substr($company_name, 0, 35);
    $pdf->SetXY(38, $starty);
    $pdf->Cell(87, 12, $code_or_name, 0, 1, 'L');
    
    // Column 3: Description (Hardcoded "Drill")
    $pdf->SetXY(110, $starty);
    $pdf->Cell(87, 12, "Drill", 0, 1, 'L');
    
    // Increment Y position and count for the next line
    $starty += $line_height;
    $count++;
}

// --- Output PDF ---
// Output the PDF file with a name based on the current timestamp, forced download
$pdf->Output(time() . ".pdf", "D");

?>
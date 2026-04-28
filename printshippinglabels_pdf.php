<?php 
// Set locale for monetary formatting (though not explicitly used in output)
setlocale(LC_MONETARY, 'en_US');

// Require the FPDF library
require_once('pdfstuff/fpdf.php');
// require_once('fpdi/fpdi.php'); // FPDI is commented out in original

// --- FPDF Initialization ---
$pdf = new FPDF();
$pdf->addPage();

$numCharsPerLine = 90; // The Number of Characters allowed per line
$toadd = 0; // add this if you want a border at the topp 
$pdf->SetXY(0, 10);
// $pdf->AddFont('verdanab'); // Original commented out
$pdf->SetTextColor(0, 0, 0); // brown 
$fontsize = 12;
$pdf->SetFont('arial', '', $fontsize);
$starty = 22;
$count = 0; // Initialize label counter

// Initialize $ids safely
$ids = $ids; 
$ids = array_unique($ids);

// --- Main Loop: Process Classes ---
foreach ($ids as $id) {
    
    // Assumed external functions: getClassRow, getCompanyRow, getClassInfo
    $crow = getClassRow($id);
    $comrow = getCompanyRow($crow['companyid']);
    $info = getClassInfo($id);
    
    // Skip if class info is missing
    if (!count($info)) {
        continue;
    }

    // --- Determine How Many Labels to Print ---
    $customer_reference = $info["Customer Reference"]["value"];
    $howmany_parts = explode(" ", $customer_reference);
    $howmany_str = $howmany_parts[0];
    
    // Attempt to parse the count as an integer
    $howmany = intval($howmany_str);
    
    if ($howmany === 0) { // If parsing failed or resulted in zero
        $is_corporate = $comrow['iscorp'];
        if ($is_corporate) {
            $howmany = 2; // Default for corporate
        } else {
            $howmany = 5; // Default for non-corporate
        }
    }
    
    // --- Loop: Print Labels for this Class ---
    for ($loop = 1; $loop <= $howmany; $loop++) { 
        
        // --- Page Break Logic (after every 10 labels) ---
        if ($count == 10) {
            $starty = 22;
            $count = 0;
            $pdf->addPage();
            
            $numCharsPerLine = 90; 
            $toadd = 0; 
            $pdf->SetXY(0, 10);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('arial', '', $fontsize);
        }
        
        // --- Layout Logic: Two Labels per Row ---
        if ($count % 2 == 0) {
            // Left column
            $howfarover = 15;
            $laststarty = $starty;
        } else {
            // Right column
            $howfarover = 115;
            $starty = $laststarty; // Use the starting Y from the left column
        }
        
        // --- Print Label Content ---
        
        // 1. Company Name (Bold, larger font)
        $pdf->SetXY($howfarover, $starty);
        $pdf->SetFont('arial', 'B', $fontsize);
        $company_name = substr($comrow['companyname'], 0, 30);
        $pdf->Cell(87, 12, $company_name, 0, 1, 'L');
        $starty += 4.8;
        
        // 2. ATTN Line
        $pdf->SetXY($howfarover, $starty);
        $attention_value = $info["Delivery Attention"]["value"];
        $pdf->Cell(87, 12, "ATTN: " . $attention_value, 0, 1, 'L');
        $pdf->SetFont('arial', '', $fontsize);
        $starty += 4.8;
        $pdf->SetXY($howfarover, $starty);

        // 3. Address Line (Splitting long addresses)
        $toremove = 0;
        $secondline = "";
        $addr = $info["Delivery Address"]["value"];
        
        if (strlen($addr) > 40) {
            $pos = strpos($addr, " ", 35);
            if ($pos === false) {
                $pos = strpos($addr, " ", 28); // Fallback to a shorter length
            }
            if ($pos !== false) {
                $secondline = trim(substr($addr, $pos));
                $addr = trim(substr($addr, 0, $pos));
            }
        }

        $pdf->Cell(87, 12, $addr, 0, 1, 'L');
        $starty += 4.8;
        $pdf->SetXY($howfarover, $starty);

        // Print second address line if it exists
        if ($secondline) {
            $pdf->Cell(87, 12, $secondline, 0, 1, 'L');
            $starty += 4.8;
            $pdf->SetXY($howfarover, $starty);
            $toremove = 4.8; // Reduce vertical space needed later
        }

        // 4. City, State, Zip
        $city = $info["Delivery City"]["value"];
        $state = $info["Delivery State"]["value"];
        $zip = $info["Delivery Zip"]["value"];
        $pdf->Cell(87, 12, $city . " " . $state . " " . $zip, 0, 1, 'L');
        $starty += 4.8;
        $pdf->SetXY($howfarover, $starty);
        
        // 5. SHIP Date
        $pick_up_date = $info["Pick Up Date"]["value"];
        if ($pick_up_date != "jumping") { 
            $pdf->Cell(87, 12, "SHIP: " . $pick_up_date, 0, 1, 'L');
        }
            
        $starty += 4.8;
        $pdf->SetXY($howfarover, $starty);
        
        // 6. BAGSET (Bold, larger font)
        $pdf->SetFont('arial', 'B', $fontsize + 2);
        $bagset_value = $info["Bagset"]["value"];
        $pdf->Cell(87, 12, "BAGSET: " . $bagset_value, 0, 1, 'L');
        $starty += 4.8;
        $pdf->SetXY($howfarover, $starty);

        // 7. Return Date and Jumping Status (smaller font)
        // Assumed external function getJumpingTo()
        $jumping_status = getJumpingTo($id) ? "Yes" : "No";
        $return_date = $info["Return Pick Up Date"]["value"];
        
        $pdf->SetFont('arial', '', $fontsize - 2);
        $pdf->Cell(87, 12, "Return Date: " . $return_date . "          Is Jumping?: " . $jumping_status, 0, 1, 'L');
        $pdf->SetFont('arial', '', $fontsize); // Reset font
        
        // Advance Y position for the next label pair
        $starty += 22.8 - $toremove;
        $count++;
    }
}

// --- Final Output ---
// Output PDF for download
$pdf->Output(time() . ".pdf", "D");
?>
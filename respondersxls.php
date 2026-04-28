<?php 
// Generate CSV instead of Excel
$filename = "responders_report_" . time() . ".csv";

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Open output stream
$output = fopen('php://output', 'w');

// Write UTF-8 BOM for Excel compatibility
fwrite($output, "\xEF\xBB\xBF");

// --- Write Header Row ---
$header = ["Name", "School Name", "Borough", "Exp Date", "Title"];
fputcsv($output, $header);

// --- Write Data Rows ---
if (isset($result) && is_array($result)) {
    foreach ($result as $row) {
        $responder_id = $row['responderid'] ?? 0;
        $exp_date_raw = getResponderExpDate($responder_id); // Assumed external function
        $exp_date_display = "";

        // Replicating original date calculation logic (appends string "+ 2 years")
        if ($exp_date_raw && $exp_date_raw !== "1969-12-31 00:00:00") {
            $exp_date_display = $exp_date_raw . " + 2 years";
        }
        
        // Safely access data columns
        $full_name = ($row['firstname'] ?? '') . " " . ($row['lastname'] ?? '');
        $company_name = $row['companyname'] ?? '';
        $borough = $row['borough'] ?? '';
        $title = $row['title'] ?? '';
        
        // Prepare data row
        $rowData = [
            $full_name,
            $company_name,
            $borough,
            $exp_date_display,
            $title
        ];
        
        // Escape any formulas that might start with =, +, - or @ to prevent CSV injection
        foreach($rowData as &$value) {
            if($value !== null && $value !== '') {
                $firstChar = substr($value, 0, 1);
                if(in_array($firstChar, array('=', '+', '-', '@'))) {
                    $value = "'" . $value;
                }
            }
        }
        
        fputcsv($output, $rowData);
    }
} else {
    // Write an error message if the data array is missing
    $errorData = ["Error: Responder data array (\$result) was not loaded.", "", "", "", ""];
    fputcsv($output, $errorData);
}

// --- Close CSV File ---
fclose($output);
exit();
?>
<?php

include "mysql.php";

if ($csv) {
    // NOTE: Path is relative to script execution location
    $hand = fopen("file.csv", "w+");
}

// --- Determine Company/Related IDs and Name ---
$isprimary = false;
$related = $id;
$companyname = '';

if ($id) {
    // Safely cast $id for SQL
    $id_safe = is_numeric($id) ? (int)$id : addslashes($id); 
    $iscorp_safe = is_numeric($session_iscorp) ? (int)$session_iscorp : addslashes($session_iscorp);
    
    $isprimary = db_query_first_cell("SELECT isheadquarter FROM company_esi WHERE iscorp = '{$iscorp_safe}' AND id = {$id_safe}");
    $companyname = getCompanyName($id_safe);

    if (!$isprimary) {
        $related = db_query_first_cell("SELECT related_company FROM company_esi WHERE id = {$id_safe}");
    } else {
        $related = $id_safe;
    }
}

// --- Dynamic WHERE Clause Construction ---

// Terrance filter (specific building codes)
if ($terrance) {
    $where .= " AND buildingcode IN ( 'M048', 'M808', 'M844', 'M860', 'M401', 'M633', 'M882', 'X807', 'X815', 'X833', 'X953', 'K801', 'K802', 'K818', 'K831', 'K986', 'K986', 'K986', 'K989', 'K997', 'Q732', 'Q733', 'Q800', 'Q801', 'Q801', 'Q823', 'Q859', 'Q980', 'R080' ) ";
}

// Field/Value filter
if ($fieldname && $fieldvalue) {
    // WARNING: $fieldname is unsanitized and used directly. This is a potential SQL Injection risk.
    // Assuming $fieldname is from a trusted source (e.g., hardcoded options).
    $fieldvalue_safe = addslashes($fieldvalue);
    $where .= " AND {$fieldname} LIKE '%{$fieldvalue_safe}%'";
}

// --- SQL Query Selection ---
$iscorp_safe = is_numeric($session_iscorp) ? (int)$session_iscorp : addslashes($session_iscorp);
$related_safe = is_numeric($related) ? (int)$related : addslashes($related);

if (!$id) {
    // Global query (filtered by session_iscorp)
    $sql = "SELECT r.*, region, companyname, schoolcode, iscorp 
            FROM responders_esi r, company_esi c 
            WHERE iscorp = '{$iscorp_safe}' 
            AND r.deleted = 0 
            AND c.deleted = 0 
            AND c.id = r.clientid {$where} {$order}";
} elseif (!$isprimary) {
    // Query for a single, non-primary company
    $sql = "SELECT r.*, region, companyname, schoolcode, iscorp 
            FROM responders_esi r, company_esi 
            WHERE iscorp = '{$iscorp_safe}' 
            AND r.deleted = 0 
            AND company_esi.deleted = 0 
            {$where} 
            AND clientid = {$id_safe} 
            AND company_esi.id = {$id_safe} {$order}";
} else {
    // Query for primary company and all related companies
    $sql = "SELECT r.*, region, companyname, schoolcode, iscorp 
            FROM responders_esi r, company_esi c 
            WHERE iscorp = '{$iscorp_safe}' 
            AND c.deleted = 0 
            AND r.deleted = 0 
            AND (r.clientid = c.id) 
            AND (c.id = {$id_safe} OR c.related_company = {$related_safe}) 
            {$where} {$order}";
}

$result = db_query_rows($sql);


// --- HTML Output ---
if (!$xls && !$csv) {
?>

<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.0 Transitional//EN'>

<html>
<head>
<title>AED Report</title>

<link rel='stylesheet' href='../css/style.css'>
</head>

<body bgcolor='#ffffff'>
<table cellpadding="3" cellspacing="0" border="1" width="100%">
<tr><td colspan='10'><?php echo htmlspecialchars($companyname); ?></td></tr>
    <tr>
        <td valign="top"><span class="copy"><strong>Last Name</strong></span></td>
        <td valign="top"><span class="copy"><strong>First Name</strong></span></td>
        <td valign="top"><span class="copy"><strong>File No</strong></span></td>
        <td valign="top"><span class="copy"><strong>Most Recent Training Date</strong></span></td>
        <td valign="top"><span class="copy"><strong>School Code</strong></span></td>
        <td valign="top"><span class="copy"><strong>City</strong></span></td>
        <td valign="top"><span class="copy"><strong>State</strong></span></td>
        <td valign="top"><span class="copy"><strong>Region</strong></span></td>
        <td valign="top"><span class="copy"><strong>District</strong></span></td>
        <td valign="top"><span class="copy"><strong>Building Code</strong></span></td>
    </tr>
    <?php
    foreach ($result as $row) {
        $responder_id = $row["responderid"];
        $url = "viewresponder.php?responderid=" . urlencode($responder_id);
        $exp_date = getResponderExpDate($responder_id);
        $exp_date_display = fixdatefordisplay($exp_date);
        $file_no = getIdentifier($row);
        $district = $row["schoolcode"][0]; // Assuming first char is the district
    ?>
    <tr>
        <td valign="top"><span class="copy"><?php if (!$xls && !$csv) { ?><a href="<?php echo $url; ?>"><?php } ?><?php echo htmlspecialchars($row["lastname"]); ?></a></span></td>
        <td valign="top"><span class="copy"><?php echo htmlspecialchars($row["firstname"]); ?> </span></td>
        <td valign="top"><span class="copy"><?php echo htmlspecialchars($file_no); ?> </span></td>
        <td valign="top"><span class="copy"><?php echo htmlspecialchars($exp_date_display); ?></span></td>
        <td valign="top"><span class="copy"><?php echo htmlspecialchars($row["schoolcode"]); ?></span></td>
        <td valign="top"><span class="copy"><?php echo htmlspecialchars($row["city"]); ?></span></td>
        <td valign="top"><span class="copy"><?php echo htmlspecialchars($row["state"]); ?></span></td>
        <td valign="top"><span class="copy"><?php echo htmlspecialchars($row["region"]); ?></span></td>
        <td valign="top"><span class="copy"><?php echo htmlspecialchars($district); ?></span></td>
        <td valign="top"><span class="copy"><?php echo htmlspecialchars($row["buildingcode"]); ?></span></td> 
    </tr>
    <?php
    }
    ?>
</table>

<?php
// --- CSV Output (for both $xls and $csv parameters) ---
} elseif ($xls || $csv) { 
    // For $xls parameter, generate CSV download with proper headers
    if ($xls) {
        $filename = "responders_report_" . time() . ".csv";
        
        // Set headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        // Open output stream
        $output = fopen('php://output', 'w');
        
        // Write UTF-8 BOM for Excel compatibility
        fwrite($output, "\xEF\xBB\xBF");
        
        // Use output stream for CSV
        $hand = $output;
    }
    
    // Write header row (consistent format for both xls and csv)
    $header = [
        "ESI ID",
        "File No/SSN",
        "Last Name", 
        "First Name", 
        "File Number", 
        "School", 
        "SchoolCode", 
        "Most Recent Training Date", 
        "Class Type", 
        "Next Scheduled Training Date", 
        "Office ID", 
        "Office Location", 
        "City", 
        "State", 
        "Region", 
        "District", 
        "Building Code"
    ];
    
    if ($xls) {
        fputcsv($hand, $header);
    } else {
        fwrite($hand, "\"" . implode("\",\"", $header) . "\"\n");
    }
    
    foreach ($result as $row) {
        $responder_id = $row['responderid'];
        $is_corp = $row["iscorp"];

        // Next Scheduled Training Date
        $sql_next = "SELECT class.id, startdate FROM class, responder_to_class WHERE responderid = {$responder_id} AND classid = class.id AND startdate > NOW() AND accepted = 1 AND deleted = 0 ORDER BY startdate";
        $classdata_next = db_query_first($sql_next);
        $sd = $classdata_next ? getFormattedDateWTime($classdata_next['startdate']) : "";
        
        // Most Recent Training Date
        $sql_recent = "SELECT class.id, startdate, code FROM class, responder_to_class WHERE responderid = {$responder_id} AND classid = class.id AND startdate < NOW() AND confirmdate > '0000-00-00' AND accepted = 1 AND deleted = 0 ORDER BY startdate DESC";
        $classdata_recent = db_query_first($sql_recent);
        $mostrecent = $classdata_recent ? getFormattedDateWTime($classdata_recent['startdate']) : "";
        $class_code = $classdata_recent['code'] ?? '';
        $class_type = isset($allclass_names[$is_corp][$class_code]) ? $allclass_names[$is_corp][$class_code] : '';
        
        // Skip if trained since date is set and most recent training is older
        if ($trainedsince && ( !$mostrecent || ( strtotime($trainedsince) > strtotime($classdata_recent['startdate'] ?? '') ) ) ) {
            continue;
        }

        $district = isset($row["schoolcode"][0]) ? $row["schoolcode"][0] : ''; // Assuming first char is the district
        
        // Prepare data row
        $rowData = [
            $row["responderid"] ?? '',
            getIdentifier($row),
            $row["lastname"] ?? '',
            $row["firstname"] ?? '',
            getIdentifier($row), // Duplicated column
            $row["companyname"] ?? '',
            $row["schoolcode"] ?? '',
            $mostrecent,
            $class_type,
            $sd,
            $row["branchid"] ?? '',
            $row["address"] ?? '',
            $row["city"] ?? '',
            $row["state"] ?? '',
            $row["region"] ?? '',
            $district,
            $row["buildingcode"] ?? ''
        ];
        
        if ($xls) {
            // Escape any formulas that might start with =, +, - or @ to prevent CSV injection
            foreach($rowData as &$value) {
                if($value !== null && $value !== '') {
                    $firstChar = substr($value, 0, 1);
                    if(in_array($firstChar, array('=', '+', '-', '@'))) {
                        $value = "'" . $value;
                    }
                }
            }
            
            fputcsv($hand, $rowData);
        } else {
            // Original CSV formatting logic
            $csv_line = '"' . implode('","', array_map('str_replace', array('"', "\n"), array('""', ''), $rowData)) . "\"\n";
            fwrite($hand, $csv_line);
        }
    }
    
    if ($xls) {
        fclose($hand);
        exit();
    } else {
        fclose($hand);
        echo "<a href='file.csv'>here</a>";
    }
}
?>
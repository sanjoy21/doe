<?php

include "mysql.php";

function db_escape_string($string) {
    if (is_array($string)) {
        return $string; // Do not escape arrays
    }
    // Basic placeholder for security demonstration
    return str_replace(['\\', "'"], ['\\\\', "\'"], (string)$string);
}

// 1. Safely Retrieve Input Variables
$xls = $_REQUEST['xls'] ?? null;
$nobuildings = $_REQUEST['nobuildings'] ?? null;
$minnum = $_REQUEST['minnum'] ?? '0';

// 2. Fetch all eligible schools
$sql = "SELECT id, schoolcode, companyname, locationcode, address, borough FROM company_esi WHERE iscorp = 0 AND retired = 0 AND deleted = 0";
$schools = db_query_rows($sql);

if ($xls)
{
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="report.csv"');
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // Add UTF-8 BOM for Excel compatibility
    fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
    
    // Write Headers
    $headers = array(
        "Name",
        "SchoolCode",
        "Address",
        "Borough",
        "Location Code",
        "# Buildings"
    );
    fputcsv($output, $headers);
    
    foreach ($schools as $srow)
    {
        $buildings = [];
        $locationcode = $srow['locationcode'] ?? '';

        if ($locationcode)
        {
            // Fetch associated buildings (assuming db_query_array exists and uses safe queries)
            $locationcode_safe = db_escape_string($locationcode);
            $buildings = db_query_array("SELECT buildingcode FROM location_to_building WHERE locationcode = '$locationcode_safe'", "buildingcode", "buildingcode");
            
            // Filtering Logic 1: Skip if exactly one building is found
            if (count($buildings) == 1) {
                continue;
            }
            
            // Filtering Logic 2: Skip if $nobuildings is set AND buildings are found
            if ($nobuildings && count($buildings)) {
                continue;
            }
        } 

        // Filtering Logic 3: This filters out any school that has location code and found buildings (count > 0)
        if (count($buildings) > 0 && !empty($srow['locationcode'])) {
            continue;
        }

        // Prepare row data with null safety
        $row_data = array(
            $srow['companyname'] ?? '',
            $srow['schoolcode'] ?? '',
            $srow['address'] ?? '',
            $srow['borough'] ?? '',
            $locationcode,
            implode(", ", $buildings)
        );
        
        fputcsv($output, $row_data);
    }
    
    fclose($output);
    exit;
}
else
{
    // --- HTML Table Output Mode ---
    $i = 1;
    echo ("<table border=1>");
    
    if ($minnum === '0' || $minnum === 0) {
        // Only run this if $minnum is not defined or is 0, matching the original logic
        $minnum_display = htmlspecialchars($minnum);
    }

    echo ("<tr><td>#</td><td>Name</td><td>School Code</td><td>Address</td><td>Borough</td><td>Location Code</td><td>Buildings</td></tr>");
    
    $rownum = 0;
    foreach ($schools as $srow)
    {
        $locationcode = $srow['locationcode'] ?? '';
        $companyname = htmlspecialchars($srow['companyname'] ?? 'N/A');
        $id = htmlspecialchars($srow['id'] ?? '');

        $buildings = [];
        $bstr = "";
        
        if ($locationcode)
        {
            $locationcode_safe = db_escape_string($locationcode);
            $buildings = db_query_array("SELECT buildingcode FROM location_to_building WHERE locationcode = '$locationcode_safe'", "buildingcode", "buildingcode");
            
            // Filtering Logic 1: Skip if exactly one building is found
            if (count($buildings) == 1) {
                continue;
            }
            
            // Filtering Logic 2: Skip if $nobuildings is set AND buildings are found
            if ($nobuildings && count($buildings)) {
                continue;
            }

            // Prepare building string for display
            if (!count($buildings)) {
                $bstr = "<font color='red'>None!</font>";
            } else {
                $bstr = htmlspecialchars(join(", ", $buildings));
            }
        }

        // Filtering Logic 3: This filters out any school that has location code and found buildings (count > 0)
        // This is the critical filter that ensures only schools without associated buildings are typically shown.
        if (count($buildings) > 0 && $locationcode) {
            continue;
        }

        // --- Display Row ---
        $rownum++;
        echo ("<tr>");
        echo ("<td>$rownum</td>");
        // Link to editcompany.php
        echo ("<td><a target=_blank href='editcompany.php?id=$id'>$companyname</a></td>");
        echo ("<td>" . htmlspecialchars($srow['schoolcode'] ?? 'N/A') . "</td>");
        echo ("<td>" . htmlspecialchars($srow['address'] ?? 'N/A') . "</td>");
        echo ("<td>" . htmlspecialchars($srow['borough'] ?? 'N/A') . "</td>");
        
        $locationcode_display = empty($locationcode) ? "<font color='red'>N/A</font>" : htmlspecialchars($locationcode);
        echo ("<td>" . $locationcode_display . "</td>");
        
        echo ("<td>" . $bstr . "</td>");
        echo ("</tr>");
    }
    echo ("</table>");
}
?>
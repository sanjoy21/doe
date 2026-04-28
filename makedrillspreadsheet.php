<?php

require_once('mysql.php');

// --- Configuration & Utility Functions (Placeholder) ---

// Assuming fixdatefordisplay exists and formats dates correctly
// function fixdatefordisplay($date) { /* ... */ }

// --- Report Generation Logic ---

/**
 * Generates the drill status CSV report for a specific trainer.
 *
 * @param int $trainerid The ID of the trainer.
 * @param bool $excludecompleted Flag to exclude drills completed after firstdrillday.
 */
function generateDrillReport(int $trainerid, bool $excludecompleted)
{
    // --- 1. Fetch Configuration Dates ---
    $earliestdate = db_query_first_cell("SELECT value FROM namevaluepair WHERE name = 'DrillDate1'");
    $firstdrillday = db_query_first_cell("SELECT value FROM namevaluepair WHERE name = 'firstdrillday'");
    // Use strtotime() inside the query if possible, or convert outside safely.
    $expredbefore = strtotime(db_query_first_cell("SELECT value FROM namevaluepair WHERE name = 'expredbefore'"));
    $drilldates = db_query_array("SELECT value FROM namevaluepair WHERE name LIKE 'DrillDate%' ORDER BY value DESC", "value", "value");

    // --- 2. Fetch Territories and Schools ---
    $territories = db_query_array("SELECT territory.name, territory.id FROM territory WHERE trainerid = ?", "id", "name", [$trainerid]);

    $neighborhoodarr = [];
    foreach ($territories as $tid => $tname) {
        // Query schools in the territory.
        $schoolsinterritory = db_query_rows("
            SELECT company_esi.*
            FROM zip_to_territory, company_esi
            WHERE company_esi.iscorp = 0
            AND company_esi.zip = zip_to_territory.zip
            AND territoryid = ?
            AND deleted = 0
            AND showsondrillreports = 1
        ", "id", [$tid]);

        foreach ($schoolsinterritory as $schoolrow) {
            $company_id = $schoolrow['id'];

            // Find last and next drill dates
            $lastdrill = db_query_first_cell("
                SELECT drilldate
                FROM drill, drill_to_companyid dtc
                WHERE dtc.drillid = drill.drillid AND dtc.companyid = ?
                AND (isdone = 1 OR completed = 1)
                ORDER BY drilldate DESC LIMIT 1
            ", [$company_id]);

            $nextdrill_str = db_query_first_cell("
                SELECT DATE_ADD(drilldate, INTERVAL 6 month) AS drilldate
                FROM drill, drill_to_companyid dtc
                WHERE dtc.drillid = drill.drillid AND dtc.companyid = ?
                AND (isdone = 1 OR completed = 1)
                ORDER BY drilldate DESC LIMIT 1
            ", [$company_id]);

            $nextdrill = strtotime($nextdrill_str);

            // Exclusion check
            if ($excludecompleted && strtotime($lastdrill) > strtotime($firstdrillday)) {
                continue;
            }

            $matched = false;
            $schoolrow["lastdrill"] = $lastdrill;
            $schoolrow["nextdrill"] = $nextdrill;

            // Grouping logic: Find the next suitable drill date from the list
            foreach ($drilldates as $ddate_str) {
                $ddate = strtotime($ddate_str);

                if (!$nextdrill) {
                    // No previous drill, assign to earliest available date
                    $neighborhoodarr[strtotime($earliestdate)][$tid][$company_id] = $schoolrow;
                    $matched = true;
                    break;
                }

                if ($nextdrill < $ddate) {
                    // Next drill is before this predefined date, use this date.
                    $neighborhoodarr[$ddate][$tid][$company_id] = $schoolrow;
                    $matched = true;
                    break;
                }
            }

            // Fallback for schools that should have already had a drill, but don't fit any future window
            if (!$matched) {
                $neighborhoodarr[strtotime($earliestdate)][$tid][$company_id] = $schoolrow;
            }
        }
    }

    // --- 3. CSV Setup ---
    ksort($neighborhoodarr); // Sort by drill date (key)

    $trainerrow = db_query_first("SELECT * FROM user WHERE id = ?", [$trainerid]);

    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="drills_' . $trainerid . '.csv"');
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // Add UTF-8 BOM for Excel compatibility
    fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
    
    // Write data rows
    foreach ($neighborhoodarr as $drilldate => $territoryarray) {
        $battexpdate = date("Y-m-01", strtotime(date("Y-m-d", $drilldate) . " - 3 years"));

        foreach ($territoryarray as $tid => $listofschools) {
            // Write section header
            $trainer_name = ($trainerrow['first_name'] ?? '') . " " . ($trainerrow['last_name'] ?? '');
            $section_header = array(
                "Trainer/Territory: {$trainer_name}: " . $territories[$tid] ?? '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                ''
            );
            fputcsv($output, $section_header);
            
            // Get counts for summary
            $allids = [];
            foreach ($listofschools as $l) {
                $allids[$l['id']] = $l['campusid'];
            }
            $numdrills = count(array_unique(array_values($allids)));
            
            // Write summary row
            $summary_row = array(
                "TOTAL DRILLS: " . $numdrills,
                '',
                '',
                '',
                '',
                '',
                '',
                "DUE: " . date("D, M j, Y", $drilldate),
                ''
            );
            fputcsv($output, $summary_row);
            
            // Write column headers
            $headers = array(
                "Location Code",
                "Name", 
                "Zip",
                "AED Summary",
                "Expiring Items",
                "Other Schools at Location",
                "Currently Expired AEDs",
                "Last drill Date",
                "6 mo after last drill"
            );
            fputcsv($output, $headers);

            $expiringbatt = 0;
            $overallexpiring = 0;
            $alreadydone = [];

            // Write school data rows
            foreach ($listofschools as $schoolid => $schoolrow) {
                if ($alreadydone[$schoolid] ?? false) continue;

                $aedarr = [];
                $expiredarr = [];
                $expiring = 0;

                $cstr = "";
                if ($schoolrow['campusid']) {
                    $cstr = "campusid = '{$schoolrow['campusid']}' OR ";
                }

                $schoolsatthislocation = db_query_array("
                    SELECT id, companyname
                    FROM company_esi
                    WHERE ({$cstr} id = ?)
                    AND deleted = 0 AND iscorp = 0 AND showsondrillreports = 1
                ", "id", "companyname", [$schoolid]);

                $osarr = [];
                $numpsal = 0;

                foreach ($schoolsatthislocation as $tmpschoolid => $tmpcompanyname) {
                    if ($tmpschoolid != $schoolid) {
                        $osarr[] = $tmpcompanyname;
                    }
                    $alreadydone[$tmpschoolid] = 1;
                    $aeds = getAedRows($tmpschoolid); // Custom function call

                    foreach ($aeds as $arow) {
                        if ($arow["aedstolen"] || $arow["deleted"] || $arow["model"] == "NYC_FR2" || ($arow["serial"][0] ?? '') != "B") continue;

                        $model = $arow['model'] ?: "FRX";
                        $aedarr[$model][] = $arow;

                        // Battery Expiration Check
                        if ($arow["batterydate"] <= $battexpdate) {
                            $expiringbatt++;
                        }

                        if (strpos(strtolower($arow['location'] ?? ''), "psal") !== false) {
                            $numpsal++;
                        }

                        // Currently Expired Pads (Past today's date)
                        $is_expired = false;
                        $pad_expiry_details = [];

                        if ($arow["padaexpiration"] && strtotime($arow["padaexpiration"]) < time()) {
                            $is_expired = true;
                            $pad_expiry_details[] = "PADS A: " . fixdatefordisplay($arow['padaexpiration']);
                        }
                        if ($arow["padbexpiration"] && strtotime($arow["padbexpiration"]) < time()) {
                            $is_expired = true;
                            $pad_expiry_details[] = "PADS B: " . fixdatefordisplay($arow['padbexpiration']);
                        }

                        if ($is_expired) {
                            $expiredarr[] = "{$arow['serial']}: {$model} (" . implode(" ", $pad_expiry_details) . ")";
                        }

                        // Expiring Pads (Before the user-defined cutoff)
                        if ($arow["padaexpiration"] && strtotime($arow["padaexpiration"]) < $expredbefore) {
                            $expiring++;
                        }
                        if ($arow["padbexpiration"] && strtotime($arow["padbexpiration"]) < $expredbefore) {
                            $expiring++;
                        }
                    }
                }

                $overallexpiring += $expiring;

                // Build output strings
                $aed_summary = "";
                foreach ($aedarr as $type => $tmprows) {
                    $aed_summary .= "$type: " . count($tmprows) . "\n";
                }
                if ($numpsal) {
                    $aed_summary .= "({$numpsal} PSAL)";
                }
                if (!count($aedarr)) {
                    $aed_summary .= "No AEDs";
                }

                $expiring_str = "FRX Pads: " . $expiring;
                $currentlyexpired_str = implode("\n", $expiredarr);
                $otherschools_str = $schoolrow['campusid'] ? implode(", ", $osarr) : "";

                // Prepare row data
                $row_data = array(
                    $schoolrow['schoolcode'] ?? '',
                    $schoolrow['companyname'] ?? '',
                    $schoolrow['zip'] ?? '',
                    $aed_summary,
                    $expiring_str,
                    $otherschools_str,
                    $currentlyexpired_str,
                    date("Y-m-d", strtotime($schoolrow['lastdrill'] ?? '')),
                    date("Y-m-d", $schoolrow['nextdrill'] ?? time())
                );
                
                fputcsv($output, $row_data);
            }

            // Write summary row with expiring counts
            $summary_counts = array(
                "ESTIMATED Expiring Pads: {$overallexpiring}",
                "Battery Changes: {$expiringbatt}",
                '',
                '',
                '',
                '',
                '',
                '',
                ''
            );
            fputcsv($output, $summary_counts);
            
            // Add blank row between sections
            fputcsv($output, array('', '', '', '', '', '', '', '', ''));
        }
    }

    fclose($output);
    exit;
}

// --- Main Execution ---

if (isset($_GET['go']) && isset($_GET['trainerid']) && is_numeric($_GET['trainerid'])) {
    $go = true;
    $trainerid = (int)$_GET['trainerid'];
    $excludecompleted = isset($_GET['excludecompleted']);
    generateDrillReport($trainerid, $excludecompleted);
}

// --- HTML Form Display ---

// The presence of $specialadmin and URL_WITHOUT_SUBDOMAIN is assumed from the environment.
// Fallback for non-admin users
if (!isset($specialadmin) || !$specialadmin) {
    Header("location: login.php");
    exit;
}

include "ssi/top.php";

?>

        <strong><span class="title">Drill Spreadsheet</span></strong>
        <p>
        <span class="copy">
            <table class="table3">
                <tr>
                    <td valign="top">
                        <form method='get' action='makedrillspreadsheet.php'>
                            Exclude done or completed drills?
                            <input type='checkbox' name='excludecompleted' value='1' <?= isset($_GET['excludecompleted']) ? "CHECKED" : "" ?>><br>
                            Trainer:
                            <select name='trainerid'>
                                <option value=''>Please Choose</option>
                                <?php
                                $alltrainers = getAllTrainers(""); // Custom function call
                                foreach ($alltrainers as $arow) {
                                    echo "<option value='{$arow['id']}'>{$arow['first_name']} {$arow['last_name']}</option>\n";
                                }
                                ?>
                            </select>
                            <input type='submit' name='go' value='Go'>
                        </form>
                    </td>
                </tr>
            </table>
       <br><br><br><br><br><br><br>

  <!--end center content-->
  
                    <?php include "ssi/footer.php" ; ?>
  
  <!--end footer-->
  </span>
  </td>
  <td valign="top" width="15"><img src="images/dotclear.gif" width="10"></td>
 </tr>
</table>
<br><br>
</div>
</body>
</html>
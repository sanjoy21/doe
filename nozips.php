<?php
// not used

// Include the database connection file and utility functions
include "mysql.php";

// 1. Retrieve distinct ZIP codes for active, non-corporate clients (schools)
$sql_zips = "SELECT DISTINCT(zip) AS zipcode FROM company_esi WHERE deleted = 0 AND iscorp = 0 AND zip > ''";
$res = db_query_rows($sql_zips);

// Assumed external functions: db_query_rows(), escMe()

foreach ($res as $r) {
    $zipcode = $r['zipcode'] ?? '';

    if (empty($zipcode)) {
        continue;
    }

    $zipcode_safe = escMe($zipcode);

    // 2. Find active users assigned directly to this ZIP code
    $sql_users_zip = "SELECT user.* FROM user_to_zip, user 
                      WHERE user_to_zip.userid = user.id 
                      AND user_to_zip.zip = '{$zipcode_safe}' 
                      AND inactive = 0";
    $r2 = db_query_rows($sql_users_zip);

    // 2. Find active users assigned via territory mapping (Original query was commented out, so we keep it commented)
    /*
    $sql_users_territory = "SELECT user.* FROM zip_to_territory, user, territory 
                            WHERE trainerid = user.id 
                            AND zip_to_territory.zip = '{$zipcode_safe}' 
                            AND inactive = 0 
                            AND zip_to_territory.territoryid = territory.id";
    $r3 = db_query_rows($sql_users_territory);
    */
    $r3 = []; // Initialize r3 as empty since the query was commented out

    // 3. Output Report
    if (count($r2) > 0 || count($r3) > 0) {
        // Output the ZIP code
        echo htmlspecialchars($zipcode) . "<br>";
        
        // Output users assigned directly via ZIP
        foreach ($r2 as $rname) {
            $first_name = htmlspecialchars($rname['first_name'] ?? '');
            $last_name = htmlspecialchars($rname['last_name'] ?? '');
            echo "&nbsp;&nbsp;&nbsp;&nbsp;{$first_name} {$last_name} <br>";
        }
        
        // Output users assigned via territory (if the original query were active)
        foreach ($r3 as $rname) {
            $first_name = htmlspecialchars($rname['first_name'] ?? '');
            $last_name = htmlspecialchars($rname['last_name'] ?? '');
            echo "&nbsp;&nbsp;&nbsp;&nbsp;{$first_name} {$last_name} <br>";
        }
    } else {
        // Output warning if no one is assigned
        echo "'" . htmlspecialchars($zipcode) . "' has no one assigned!<br>";
    }
}
?>
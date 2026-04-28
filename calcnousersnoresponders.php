<?php 
require "mysql.php";

// Get the database connection link for escaping strings
$db_link = $GLOBALS['link'] ?? $link; 

// --- 1. Find non-corporate schools with NO USERS and NO ACTIVE RESPONDERS ---
$sql_main = "SELECT 
                company_esi.zip, campusid, company_esi.id, schoolcode, companyname, 
                COUNT( user.id ) AS cnt, 
                COUNT( responders_esi.responderid) AS rspcnt 
            FROM 
                company_esi 
            LEFT JOIN 
                user ON user.companyid = company_esi.id 
            LEFT JOIN 
                responders_esi ON clientid = company_esi.id AND responders_esi.deleted = 0 
            WHERE 
                company_esi.deleted = 0 
                AND company_esi.iscorp = 0 
            GROUP BY 
                company_esi.id 
            HAVING 
                cnt = 0 AND rspcnt = 0";
                
$res = db_query_rows( $sql_main, "id" );

$already = []; // Unused in the original logic
echo( "<table border=1 cellspacing=0>
          <tr>
            <th>name</th>
            <th>code</th>
            <th>zip</th>
            <th>upcoming class</th>
          </tr>" );

// --- 2. Loop through results and apply Campus Exclusion Logic ---
foreach( $res as $row )
{
    // PHP 8.2 Fix: Quote array keys and cast to int
    $company_id = (int)($row['id'] ?? 0);
    $campus_id = (int)($row['campusid'] ?? 0);
    
    // Campus Exclusion Logic: If this school has a campus ID, check if any *other* school 
    // sharing that campus ID exists. If so, skip this school. (This means if *any* // other part of the campus has a user/responder, we exclude the current entry).
    if( $campus_id > 0)
    {
        $sql_other = "SELECT 
                        COUNT(*) 
                      FROM 
                        company_esi 
                      WHERE 
                        campusid = {$campus_id} 
                        AND id <> {$company_id}";
        $other = db_query_first_cell( $sql_other );
        
        if( $other )
        {
            continue; // Skip this school if another entity shares its campus ID
        }
    }
    
    // --- Find next accepted, non-deleted, upcoming class ---
    $sql_class = "SELECT 
                    id, startdate 
                  FROM 
                    class 
                  WHERE 
                    companyid = {$company_id} 
                    AND startdate > NOW() 
                    AND accepted = 1 
                    AND deleted = 0 
                  ORDER BY 
                    startdate 
                  LIMIT 1";
    $classdata = db_query_first($sql_class);
    
    // Assuming getFormattedDateWTime() exists and is safe
    $sd = $classdata ? getFormattedDateWTime( $classdata['startdate'] ?? null ) : "";
    
    // --- Output Row (using htmlspecialchars for safety) ---
    $company_name_safe = htmlspecialchars($row['companyname'] ?? '');
    $company_id_safe = htmlspecialchars($company_id);
    $school_code_safe = htmlspecialchars($row['schoolcode'] ?? '');
    $zip_safe = htmlspecialchars($row['zip'] ?? '');
    $sd_safe = htmlspecialchars($sd);

    echo( "<tr>
              <td><a target='_blank' href='viewcompany.php?id={$company_id_safe}'>{$company_name_safe}</a></td>
              <td>{$school_code_safe}</td>
              <td>{$zip_safe}</td>
              <td>{$sd_safe}</td>
          </tr>" );
}
echo( "</table>" );

?>
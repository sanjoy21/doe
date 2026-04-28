<?php 
require "mysql.php";

// Get the database connection link for escaping strings
$db_link = $GLOBALS['link'] ?? $link; 

// --- 1. Find non-corporate schools (company_esi) that have no associated users ---
$sql_main = "SELECT 
                company_esi.zip, company_esi.id, schoolcode, companyname, COUNT( user.id ) AS cnt 
            FROM 
                company_esi 
            LEFT JOIN 
                user ON user.companyid = company_esi.id 
            WHERE 
                company_esi.deleted = 0 
                AND company_esi.iscorp = 0 
            GROUP BY 
                company_esi.id 
            HAVING 
                cnt = 0";
                
$res = db_query_rows( $sql_main, "id" );

$already = []; // This array is unused in the original logic, but preserved for context.

echo( "<table border=1 cellspacing=0>
          <tr>
            <th>name</th>
            <th>code</th>
            <th>zip</th>
            <th>upcoming class</th>
            <th>num resp</th>
            <th>Latest training date</th>
          </tr>" );

// --- 2. Loop through results and fetch supplemental data ---
foreach( $res as $row )
{
    // Safety: Ensure ID is an integer
    $company_id = (int)$row['id'];
    
    // A. Find next accepted, non-deleted, upcoming class
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
    
    // B. Count active responders
    $sql_numr = "SELECT COUNT(*) FROM responders_esi WHERE clientid = {$company_id} AND deleted = 0";
    $numr = db_query_first_cell($sql_numr);
    
    $lastexp = "";
    if( $numr )
    {
        // C. Find latest training date among all responders for this school
        $sql_lastexp = "SELECT 
                            MAX( rt.trainingdate ) 
                        FROM 
                            responder_training_dates rt, responders_esi r 
                        WHERE 
                            r.responderid = rt.responderid 
                            AND r.clientid = {$company_id}";
        $lastexp = db_query_first_cell( $sql_lastexp );
    }

    // --- Output Row (using htmlspecialchars for safety) ---
    $company_name_safe = htmlspecialchars($row['companyname'] ?? '');
    $company_id_safe = htmlspecialchars($company_id);
    $school_code_safe = htmlspecialchars($row['schoolcode'] ?? '');
    $zip_safe = htmlspecialchars($row['zip'] ?? '');
    $sd_safe = htmlspecialchars($sd);
    $numr_safe = htmlspecialchars($numr);
    $lastexp_safe = htmlspecialchars($lastexp);

    echo( "<tr>
              <td><a target='_blank' href='viewcompany.php?id={$company_id_safe}'>{$company_name_safe}</a></td>
              <td>{$school_code_safe}</td>
              <td>{$zip_safe}</td>
              <td>{$sd_safe}</td>
              <td>{$numr_safe}</td>
              <td>{$lastexp_safe}</td>
          </tr>" );
}
echo( "</table>" );

?>
<?php 
include "mysql.php";

$sql_companies = "SELECT 
                    id, 
                    companyname, 
                    schoolcode, 
                    address, 
                    city, 
                    zip, 
                    campusid 
                  FROM 
                    company_esi 
                  WHERE 
                    deleted = 0 
                    AND borough = 'Manhattan' 
                    AND iscorp = 0 
                  ORDER BY companyname";
$comp = db_query_rows( $sql_companies );

$filename = "a.csv";
$h = @fopen( $filename, "w+" );

if (!$h) {
    die("Error: Could not open file for writing: " . $filename);
}

fputcsv( $h, array("School Code", "Company Name", "Address", "City", "Zip", "Num AEDs", "Classes in Jun 2010") );

foreach( $comp as $c ) 
{

    $company_id = (int)($c['id'] ?? 0);
    $campus_id = $c['campusid'] ?? '';
    
    $aed_models_list = "'NYC_FR2', 'NYC100_FR2'";

    $sql_missing_company = "SELECT 
                                COUNT(*) 
                            FROM 
                                aed_esi 
                            WHERE 
                                aedmissing = 1 
                                AND clientid = {$company_id} 
                                AND model IN ( {$aed_models_list} ) 
                                AND deleted = 0";
                                
    $anymissing = db_query_first_cell( $sql_missing_company );
    if( $anymissing ) {
        continue;
    }

    $campus_check_passed = true;
    if( $campus_id )
    {
        $safe_campus_id = mysqli_real_escape_string($GLOBALS['link'] ?? $link, $campus_id);
        
        $sql_missing_campus = "SELECT 
                                    COUNT(*) 
                                FROM 
                                    aed_esi 
                                WHERE 
                                    aedmissing = 1 
                                    AND clientid IN ( SELECT id FROM company_esi WHERE campusid = '{$safe_campus_id}' ) 
                                    AND model IN ( {$aed_models_list} ) 
                                    AND deleted = 0";
                                    
        $anymissing_campus = db_query_first_cell( $sql_missing_campus );
        if( $anymissing_campus ) {
            $campus_check_passed = false;
        }
    }
    
    if (!$campus_check_passed) {
        continue;
    }

    $sql_num_aeds = "SELECT 
                        COUNT(*) 
                     FROM 
                        aed_esi 
                     WHERE 
                        clientid = {$company_id} 
                        AND model IN ( {$aed_models_list} ) 
                        AND deleted = 0";
                        
    $numaeds = db_query_first_cell( $sql_num_aeds );
    if( !$numaeds ) {
        continue;
    }
    
    $cstr = " = {$company_id}";
    if( $campus_id )
    {
        $cstr = " IN ( SELECT id FROM company_esi WHERE campusid = '{$safe_campus_id}' ) ";
    }
    $sql_num_classes = "SELECT 
                            COUNT(*) 
                        FROM 
                            class 
                        WHERE 
                            companyid {$cstr} 
                            AND startdate >= '2010-06-01' 
                            AND startdate < '2010-07-01' 
                            AND accepted = 1";

    $numclasses = db_query_first_cell( $sql_num_classes );

    $had_classes = $numclasses ? "Yes" : "No";

    $data_row = array(
        $c['schoolcode'] ?? '',
        $c['companyname'] ?? '',
        $c['address'] ?? '',
        $c['city'] ?? '',
        $c['zip'] ?? '',
        $numaeds,
        $had_classes
    );
    
    fputcsv( $h, $data_row );
}
fclose( $h );
?>
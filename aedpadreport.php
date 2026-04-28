<?php
include "mysql.php";
$dt = getsetting( "drillsdontcountbefore" );
$sixmonths = date( "Y-m-d", mktime( 0,0,0,date( "m" ) + 6 ) );

$visi = isset($thisusersrow["visiblezips"]) && $thisusersrow["visiblezips"] ? " and c.zip in ( ".getZips( $thisusersrow )." ) " : "";

$sql = "select a.*, c.* from company_esi c, aed_esi a where a.clientid = c.id and c.id not in ( select distinct ( companyid ) from drill where completed = 1 and drilldate >= '$dt' ) and a.deleted = 0 and c.deleted = 0 and aedmissing = 0 and aedstolen = 0 and ( ( padaexpiration <> '0000-00-00' and padaexpiration is not null and padaexpiration < '$sixmonths' ) or ( padbexpiration <> '0000-00-00' and padbexpiration is not null and padbexpiration < '$sixmonths' ) or ( pediatricpads <> '0000-00-00' and pediatricpads is not null and pediatricpads < '$sixmonths' ) ) and iscorp = '$session_iscorp' and showsondrillreports = 1 $visi";
//echo( $sql );
$sixmonths_timestamp = mktime( 0,0,0,date( "m" ) + 6 );

if( isset($xls) && $xls )
{
    // Generate CSV instead of Excel
    $filename = "aed_pad_report_" . time() . ".csv";
    
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // Write UTF-8 BOM for Excel compatibility
    fwrite($output, "\xEF\xBB\xBF");
    
    // Prepare header row
    $headers = [
        "Company", 
        "Address", 
        "City", 
        "Zip Code", 
        "Trainer", 
        "Serial", 
        "Location", 
        "Pad A Expiration", 
        "Pad B Expiration", 
        "Pediatric Pad Expiration"
    ];
    
    fputcsv($output, $headers);
    
    // Fetch data from database
    $res = db_query_rows( $sql );
    
    // Process each row
    foreach( $res as $r )
    {
        // Get trainer names for this zip code
        $zip_code = isset($r['zip']) ? addslashes($r['zip']) : '';
        $trainer_result = db_query_first( "select group_concat( concat( first_name, ' ' , last_name ) ) as g, usertype from user, user_to_zip where user.id = user_to_zip.userid and usertype = 'trainer' and user_to_zip.zip like '%" . $zip_code . "%' group by usertype" );
        $trainer_name = isset($trainer_result['g']) ? $trainer_result['g'] : '';
        
        // Check expiration status (1 = expires within 6 months, 0 = not expired within 6 months)
        $padaexp_check = (isset($r['padaexpiration']) && $r['padaexpiration'] != '0000-00-00' && strtotime( $r['padaexpiration'] ) < $sixmonths_timestamp) ? "1" : "0";
        $padbexp_check = (isset($r['padbexpiration']) && $r['padbexpiration'] != '0000-00-00' && strtotime( $r['padbexpiration'] ) < $sixmonths_timestamp) ? "1" : "0";
        $pedexp_check = (isset($r['pediatricpads']) && $r['pediatricpads'] != '0000-00-00' && strtotime( $r['pediatricpads'] ) < $sixmonths_timestamp) ? "1" : "0";
        
        // Prepare data row
        $rowData = [
            isset($r['companyname']) ? $r['companyname'] : '',
            isset($r['address']) ? $r['address'] : '',
            isset($r['city']) ? $r['city'] : '',
            isset($r['zip']) ? $r['zip'] : '',
            $trainer_name,
            isset($r['serial']) ? $r['serial'] : '',
            isset($r['location']) ? $r['location'] : '',
            $padaexp_check,
            $padbexp_check,
            $pedexp_check
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
    
    fclose($output);
    exit();
}
else
{
    echo( "<table border='1'>" );
    $tmparr = array( "Company", "Address", "City", "Zip Code", "Trainer", "Serial", "Location", "Pad A Expiration", "Pad B Expiration", "Pediatric Pad Expiration" );
    echo( "<tr>" );
    foreach( $tmparr as $o ) { 
        echo( "<th>". htmlspecialchars($o) ."</th>" );
    }
    echo( "</tr>" );

    $res = db_query_rows( $sql );
    foreach( $res as $r )
    {
        echo( "<tr>" );

        $trainer_result = db_query_first( "select group_concat( concat( first_name, ' ' , last_name ) ) as g, usertype from user, user_to_zip where user.id = user_to_zip.userid and usertype = 'trainer' and user_to_zip.zip like '%" . (isset($r['zip']) ? addslashes($r['zip']) : '') . "%' group by usertype" );
        $trainer_name = isset($trainer_result['g']) ? $trainer_result['g'] : '';
        
        $company_id = isset($r['id']) ? $r['id'] : '';
        $company_name = isset($r['companyname']) ? htmlspecialchars($r['companyname']) : '';
        $address = isset($r['address']) ? htmlspecialchars($r['address']) : '';
        $city = isset($r['city']) ? htmlspecialchars($r['city']) : '';
        $zip = isset($r['zip']) ? htmlspecialchars($r['zip']) : '';
        $serial = isset($r['serial']) ? htmlspecialchars($r['serial']) : '';
        $location = isset($r['location']) ? htmlspecialchars($r['location']) : '';
        
        echo( "<td><a target='_blank' href='viewcompany.php?id=$company_id'>$company_name</a></td>" );
        echo( "<td>$address</td>" );
        echo( "<td>$city</td>" );
        echo( "<td>$zip</td>" );
        echo( "<td>$trainer_name</td>" );
        echo( "<td>$serial</td>" );
        echo( "<td>$location</td>" );
        
        $padaexp_check = (isset($r['padaexpiration']) && $r['padaexpiration'] != '0000-00-00' && strtotime( $r['padaexpiration'] ) < $sixmonths_timestamp) ? "1" : "0";
        echo( "<td>$padaexp_check</td>" );
        
        $padbexp_check = (isset($r['padbexpiration']) && $r['padbexpiration'] != '0000-00-00' && strtotime( $r['padbexpiration'] ) < $sixmonths_timestamp) ? "1" : "0";
        echo( "<td>$padbexp_check</td>" );
        
        $pedexp_check = (isset($r['pediatricpads']) && $r['pediatricpads'] != '0000-00-00' && strtotime( $r['pediatricpads'] ) < $sixmonths_timestamp) ? "1" : "0";
        echo( "<td>$pedexp_check</td>" );
        
        echo( "</tr>" );
    }
    echo( "</table>" );
}
?>
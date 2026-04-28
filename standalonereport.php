<?php
include "mysql.php";

$buildings = db_query_array( "select buildingcode, count(*) as cn from location_to_building, company_esi c where c.locationcode = location_to_building.locationcode and companyname not like '%charter%' and schoolcode not like '84-%' group by buildingcode ", "buildingcode", "buildingcode" );

$thedate = date( "Y-m-01", strtotime( "2 years ago" ) );

if( isset($xls) && $xls )
{
    // Generate CSV instead of Excel
    $filename = "expired_responders_" . time() . ".csv";
    
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // Write UTF-8 BOM for Excel compatibility
    fwrite($output, "\xEF\xBB\xBF");
    
    // Write header row
    $header = [
        "Building Code",
        "School Code",
        "Responder First Name",
        "Responder Last Name",
        "Expiration Date",
        "Next Training Date"
    ];
    
    fputcsv($output, $header);
    
    if( isset($buildings) && is_array($buildings) )
    {
        foreach( $buildings as $b )
        {
            if( isset($b) && $b )
            {
                $building_code_safe = addslashes($b);
                $schools = db_query_rows( "select c.* from company_esi c, location_to_building l where l.locationcode = c.locationcode and buildingcode = '" . $building_code_safe . "' and deleted = 0 and iscorp = 0 and companyname not like '%charter%' and schoolcode not like '84-%' " );
                
                if( isset($schools) && is_array($schools) )
                {
                    foreach( $schools as $sid=>$crow )
                    {
                        if( isset($crow['id']) && $crow['id'] )
                        {
                            $school_id = intval($crow['id']);
                            $resps = db_query_rows( "select r.*, c.id as classid, startdate from responders_esi r, responder_to_class rtd, class c where r.responderid = rtd.responderid and clientid = '" . $school_id . "' and r.deleted = 0 and startdate > now() and c.id = classid " );
                            
                            if( isset($resps) && is_array($resps) )
                            {
                                foreach( $resps as $rrow )
                                {
                                    $building_code = isset($b) ? $b : '';
                                    $school_code = isset($crow['schoolcode']) ? $crow['schoolcode'] : '';
                                    $first_name = isset($rrow['firstname']) ? $rrow['firstname'] : '';
                                    $last_name = isset($rrow['lastname']) ? $rrow['lastname'] : '';
                                    $class_id = isset($rrow['classid']) ? $rrow['classid'] : '';
                                    $start_date = isset($rrow['startdate']) ? $rrow['startdate'] : '';
                                    
                                    // Build combined expiration/next training date string
                                    $ustr = '';
                                    if( $start_date )
                                    {
                                        $ustr = getFormattedDateWTime( $start_date );
                                    }
                                    if( $class_id )
                                    {
                                        $ustr .= " $class_id";
                                    }
                                    
                                    // Prepare data row
                                    $rowData = [
                                        $building_code,
                                        $school_code,
                                        $first_name,
                                        $last_name,
                                        "", // Expiration Date (empty based on original logic)
                                        $ustr // Next Training Date (combined date + class ID)
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
                            }
                        }
                    }
                }
            }
        }
    }
    
    fclose($output);
    exit();
}
else
{
    $i = 1;
    echo( "<table border=1>" );
    
    if( !isset($minnum) || !$minnum )
    {
        $minnum = "0";
    }
    
    echo( "<tr><td>Building Code</td><td>School Code</td><td>Responder First Name</td><td>Responder Last Name</td>" );
    echo( "<td>Expiration Date</td>" );
    echo( "<td>Next Training Date</td></tr>" );
    
    if( isset($buildings) && is_array($buildings) )
    {
        foreach( $buildings as $b )
        {
            if( isset($b) && $b )
            {
                $schools = db_query_rows( "select c.* from company_esi c, location_to_building l where l.locationcode = c.locationcode and buildingcode = '" . addslashes($b) . "' and deleted = 0 and iscorp = 0 and companyname not like '%charter%' and schoolcode not like '84-%'" );
                
                if( isset($schools) && is_array($schools) )
                {
                    foreach( $schools as $sid=>$crow )
                    {
                        if( isset($crow['id']) && $crow['id'] )
                        {
                            $resps = db_query_rows( "select r.*, c.id as classid, startdate from responders_esi r, responder_to_class rtd, class c where r.responderid = rtd.responderid and clientid = '" . intval($crow['id']) . "' and r.deleted = 0 and startdate > now() and c.id = classid " );
                            
                            if( isset($resps) && is_array($resps) )
                            {
                                foreach( $resps as $rrow )
                                {
                                    echo( "<tr>" );
                                    
                                    $building_code = isset($b) ? htmlspecialchars($b) : '';
                                    $school_code = isset($crow['schoolcode']) ? htmlspecialchars($crow['schoolcode']) : '';
                                    $first_name = isset($rrow['firstname']) ? htmlspecialchars($rrow['firstname']) : '';
                                    $last_name = isset($rrow['lastname']) ? htmlspecialchars($rrow['lastname']) : '';
                                    $responder_id = isset($rrow['responderid']) ? $rrow['responderid'] : 0;
                                    $class_id = isset($rrow['classid']) ? $rrow['classid'] : 0;
                                    $start_date = isset($rrow['startdate']) ? $rrow['startdate'] : '';
                                    
                                    echo( "<td>" . $building_code . "</td>" );
                                    echo( "<td>" . $school_code . "</td>" );
                                    echo( "<td>" . $first_name . "</td>" );
                                    echo( "<td>" . $last_name . "</td>" );
                                    
                                    $exp_date = '';
                                    if( $responder_id )
                                    {
                                        $exp_date = getResponderExpDatePlus( $responder_id );
                                    }
                                    echo( "<td>" . htmlspecialchars($exp_date) . "</td>" );
                                    
                                    $ustr = '';
                                    if( $start_date )
                                    {
                                        $ustr = getFormattedDateWTime( $start_date );
                                    }
                                    if( $class_id )
                                    {
                                        $ustr .= " <a href='class_detail.php?id=" . htmlspecialchars($class_id) . "'>" . htmlspecialchars($class_id) . "</a>";
                                    }
                                    echo( "<td>" . $ustr . "</td>" );
                                    
                                    echo( "</tr>" );
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    ?>
    </table>
<?php } ?>
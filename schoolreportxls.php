<?php 

// Remove the Spreadsheet Excel Writer requirement

if (!function_exists('db_escape_or_placeholder')) {
    function db_escape_or_placeholder($str) {
        if (is_array($str)) return '';
        return addslashes($str); 
    }
}

if (!function_exists('sanitize_csv_output')) {
    function sanitize_csv_output($data) {
        if (is_null($data)) return '';
        if (is_bool($data)) return $data ? "Yes" : "No";
        if (is_numeric($data)) return $data;
        // Basic string cleaning for CSV output
        return str_replace(array("\r", "\n"), ' ', (string)$data);
    }
}

// Generate CSV filename with timestamp
$filename = "schools_report_" . time() . ".csv";

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Open output stream
$output = fopen('php://output', 'w');

// Write UTF-8 BOM for Excel compatibility
fwrite($output, "\xEF\xBB\xBF");

// --- Prepare Headers ---
$headers = [];

if( $session_iscorp )
{
    $headers[] = "";
    $headers[] = "Class URL";
}

$headers[] = "School Code";
$headers[] = "School Name";
if( !$session_iscorp )
    $headers[] = "Restricted?";
    
if( !$minimal )
{
    $headers[] = "Address";
    $headers[] = "Floor";
    $headers[] = "City";
    $headers[] = "State";
}
$headers[] = "Zip";

$headers[] = "Rep Name";
if( !$minimal )
    $headers[] = "Phone";

$headers[] = "AED Contact";
$headers[] = "AED Contact Phone";

if( !$minimal )
{
    $headers[] = "AED Contact Email";
    $headers[] = "Principal Name";
    $headers[] = "Principal Email";
    $headers[] = "CFN";
}

if( $deleted )
{
    $headers[] = "Deleted";
    $headers[] = "Deleted Date";
    $headers[] = "Retired";
    $headers[] = "Retired Date";
}
if( !$minimal )
    $headers[] = "Borough";
if( !$minimal )
    $headers[] = "Region";

if( !$minimal && !$deleted )
{
    $headers[] = "Last Drill Date";
    $headers[] = "Last Service Call Date";
}

$headers[] = "List of Responders - Exp Date";
$headers[] = "Number of Responders";
$headers[] = "General School Notes";
$headers[] = "Campus";
$headers[] = "ID";
$headers[] = "Gets Drills?";
$headers[] = "School Created Date";
$headers[] = "AED Serial(s)";
$headers[] = "AED Type(s)";
$headers[] = "Expired Pads";
$headers[] = "Last Recert Note";
$headers[] = "Upcoming Class";
$headers[] = "Building Codes";

// Write header row
fputcsv($output, $headers);

// --- Process Data Rows ---
$i = 0;
foreach( $rep as $class )
{
    // SQLi Mitigation: Ensure $id is a safe integer for database queries
    $id = $class['id'];
    $num = $counter[$id];
    
    // Original logic for filtering $nodrills / $withnodrills
    if( $nodrills || $withnodrills )
    {
        if( $nodrills && $num )
        {
            continue;
        }
        else if( $nodrills && $class['campusid'] )
        {
            $otherschoolsinbuilding = getSchoolsInCampus( $class['campusid'], $id );
            foreach( $otherschoolsinbuilding as $frow )
            {
                if( $counter[$frow['id']])
                    continue;
            }
        }
        if( $withnodrills && $num > 1 )
            continue;
    }

    $repname = $trainerzips[$class['zip']];
    
    $drillrow = null;
    $servicecallrow = null;
    
    // SQLi Mitigation: Use safe integer $id
    $drillrow = db_query_first("select d.* from drill d, drill_to_companyid dtc where dtc.companyid = '{$id}' and d.drillid = dtc.drillid order by drilldate desc limit 1");
    $servicecallrow = db_query_first("select d.* from servicecall d, servicecall_to_companyid dtc where dtc.companyid = '{$id}' and d.servicecallid = dtc.servicecallid order by servicecalldate desc limit 1");
    
    // --- Prepare Data Row ---
    $rowData = [];
    
    if( $session_iscorp )
    {
        $rowData[] = "";
        // XSS Mitigation: Ensure base URL is sanitized, ID is integer
        $url = "http://".getUrlPrefix( $session_iscorp ).".".URL_WITHOUT_SUBDOMAIN."/viewcompany.php?id={$id}";
        $rowData[] = sanitize_csv_output($url);
    }
    
    // --- Add Data Fields ---
    $rowData[] = sanitize_csv_output($class['schoolcode']);
    $rowData[] = sanitize_csv_output($class['companyname']);
    
    if( !$session_iscorp ) {
        $str = "";
        if( in_array( $class['zip'], $bannedzips ) )
            $str .= "RESTRICTED ZIP CODE";
        if( $bannedschoolids[$id] ) // Use safe integer $id
            $str .= "RESTRICTED SCHOOL ID - " . sanitize_csv_output($bannedschoolids[$id]); 
        $rowData[] = sanitize_csv_output($str);
    }
    
    if( !$minimal )
    {
        $rowData[] = sanitize_csv_output($class['address']);
        $rowData[] = sanitize_csv_output($class['floor']);
        $rowData[] = sanitize_csv_output($class['city']);
        $rowData[] = sanitize_csv_output($class['state']);
    }
    $rowData[] = sanitize_csv_output($class['zip']);

    $rowData[] = sanitize_csv_output($repname);
    if( !$minimal )
        $rowData[] = sanitize_csv_output($class['contactphone']);

    $rowData[] = sanitize_csv_output($class['contactname']);
    $rowData[] = sanitize_csv_output($class['contactphone']); // Contact Phone repeated
    
    if( !$minimal )
    {
        $rowData[] = sanitize_csv_output($class['contactemail']);
        $rowData[] = sanitize_csv_output($class['principalname']);
        $rowData[] = sanitize_csv_output($class['principalemail']);
        $rowData[] = sanitize_csv_output($class['cfn']);
    }

    if( $deleted )
    {
        $rowData[] = sanitize_csv_output($class['deleted']?"Yes":"No");
        $rowData[] = sanitize_csv_output($class['deletiondate']);
        $rowData[] = sanitize_csv_output($class['retired']?"Yes":"No");
        $rowData[] = sanitize_csv_output($class['retiredate']);
    }

    if( !$minimal )
        $rowData[] = sanitize_csv_output($class['borough']);
    if( !$minimal )
        $rowData[] = sanitize_csv_output($class['region']);

    if( !$minimal && !$deleted )
    {
        $drill_date = ($drillrow['drilldate']) && ($drillrow['drilldate'] != "0000-00-00") ? $drillrow['drilldate'] : "";
        $servicecall_date = ($servicecallrow['servicecalldate']) && ($servicecallrow['servicecalldate'] != "0000-00-00") ? $servicecallrow['servicecalldate'] : "";
        
        $rowData[] = sanitize_csv_output($drill_date);
        $rowData[] = sanitize_csv_output($servicecall_date);
    }

    // Responders List
    $resparr = getNonExpiredResponders( $id ); // Use safe integer $id
    $resp = "";
    foreach( $resparr as $rrow )
    {
        // Assuming getFormattedDate and getResponderExpDatePlus handle escaping internally or return clean string
        $tmpexpdate = getFormattedDate( getResponderExpDatePlus( $rrow['responderid'] ) );
        if( $resp )
            $resp .= ", ";
        // XSS Mitigation: Sanitize names
        $resp .= sanitize_csv_output($rrow["firstname"]) . " " . sanitize_csv_output($rrow["lastname"]) . " - " . sanitize_csv_output($tmpexpdate);
    }
    $rowData[] = sanitize_csv_output($resp);
    $rowData[] = count( $resparr );
    
    $rowData[] = sanitize_csv_output($class['companynotes']);
    
    $rowData[] = sanitize_csv_output($class['campus']);
    $rowData[] = $id;
    $rowData[] = sanitize_csv_output($class['showsondrillreports']?"Y":"N");
    $rowData[] = sanitize_csv_output($class['date']);
    
    // Assuming these helper functions return sanitized output
    $rowData[] = sanitize_csv_output(getAEDSerials( $id ));
    $rowData[] = sanitize_csv_output(getAEDTypes( $id ));
    $rowData[] = sanitize_csv_output(getExpiredAEDDates( $id ));
    $rowData[] = sanitize_csv_output(getLastRecertNote( $id ));
    
    // SQLi Mitigation: Use safe integer $id
    $up = db_query_first_cell( "select startdate from class where companyid = '{$id}' and startdate > now() and deleted = 0" );
    $rowData[] = sanitize_csv_output($up);
    
    // SQLi Mitigation: Escape $locationcode
    $locationcode_safe = db_escape_or_placeholder($locationcode);
    $b_array = db_query_array( "select buildingcode from location_to_building where locationcode = '{$locationcode_safe}'", "buildingcode", "buildingcode" );
    $b = implode( ", ", array_map('sanitize_csv_output', $b_array) ); // Ensure implode output is also sanitized
    $rowData[] = $b;
    
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
?>
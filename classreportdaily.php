<?php
require_once('mysql.php');

// --- 1. Safely Retrieve and Initialize Variables ---
$fromdate = $_REQUEST['fromdate'] ?? null;
$onscreen = $_REQUEST['onscreen'] ?? null;

// Assume these are global/session variables initialized elsewhere
$thisusersrow = $thisusersrow ?? [];
$allclass_names = $allclass_names ?? [];

// --- 2. Initial Access Control ---
if( getcurrentusercompany() > 0 )
{
    header( "location: login.php" );
    exit;
}

// --- 3. Build SQL Query Filters ---
$extra = "";

// Filter by a specific day ($fromdate)
if( $fromdate ) {
    $fromdate_safe = mysqli_real_escape_string($link, $fromdate);
    
    // Start date filter
    $extra .= " AND startdate >= '{$fromdate_safe}' ";
    
    // End date filter (same day, midnight) - This effectively limits the report to one day.
    $special = date( "Y-m-d 23:59:59", strtotime( $fromdate ) );
    $special_safe = mysqli_real_escape_string($link, $special);
    $extra .= " AND startdate <= '{$special_safe}' ";
}

// Filter by user's districts (Assumes getDistrictString handles safety)
if( $thisusersrow["districts"] ?? null ) {
    $extra .= getDistrictString( $thisusersrow["districts"] );
}

// Filter by user's single school ID
if( $thisusersrow["singleschoolid"] ?? null ) {
    $singleschoolid_safe = (int)$thisusersrow["singleschoolid"];
    $extra .= " AND company_esi.id = {$singleschoolid_safe}";
}

// Global filter: only non-deleted classes
$extra .= " AND class.deleted = 0";

// --- 4. Fetch Report Data ---
// Note: This uses an implicit inner join (class, company_esi) which is assumed to work
$sql = "SELECT class.* FROM class, company_esi 
        WHERE company_esi.id = class.companyid 
        {$extra} 
        ORDER BY startdate";
$rep = db_query_rows( $sql );

// --- 5. Output Generation ---

if( !$onscreen )
{
    // --- CSV Output (replacing Excel) ---
    $filename = "classes_report_" . time() . ".csv";
    
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // Write UTF-8 BOM for Excel compatibility
    fwrite($output, "\xEF\xBB\xBF");
    
    // Write Header Row
    $header = [
        "Course Date",
        "Course Type",
        "Class URL",
        "Class Number",
        "Host School Name",
        "Host School Number",
        "Trainer",
        "Contact Name",
        "Host School Address",
        "Training Address",
        "Host School Street",
        "Host School Floor/Room",
        "Host School City",
        "Host School State",
        "Host School Zip",
        "Host School Borough",
        "Contact Email",
        "Contact Phone",
        "Notes",
        "School ID",
        "Building Permit Number",
        "Requested Trainer"
    ];
    
    fputcsv($output, $header);
    
    // Write Data Rows
    if (is_array($rep)) {
        foreach( $rep as $class )
        {
            $company_id = (int)($class['companyid'] ?? 0);
            $class_id = (int)($class['id'] ?? 0);

            $crow = getCompanyRow( $company_id );
            
            // 1. Course Date
            $courseDate = $class['startdate'] ?? '';
            
            // 2. Course Type
            $iscorp_id = $crow['iscorp'] ?? 0;
            $code_id = $class['code'] ?? '';
            $course_name = $allclass_names[$iscorp_id][$code_id] ?? '';
            
            // 3. Class URL
            $class_url = "http://".SUB_DOE."." . URL_WITHOUT_SUBDOMAIN . "/class_detail.php?id={$class_id}";
            
            // 4. Class Number
            $classNumber = $class_id;
            
            // 5. Host School Name
            $hostSchoolName = getCompanyName( $company_id );
            
            // 6. Host School Number
            $hostSchoolNumber = $crow['schoolcode'] ?? '';
            
            // 7. Trainer
            $trainers = getTrainers( $class_id ); // Assumed function
            $tstr = "";
            $any = false;
            if (is_array($trainers)) {
                foreach( $trainers as $trainerid=>$trow ) {
                    $tstr .= $any ? ", " : "";
                    $tstr .= getFullname( $trainerid ) . " - " . ($trow['trainerconfirmeddate'] ?? 'N/A');
                    $any = true;
                }
            }
            
            // 8. Contact Name
            $contact_name = ($class['firstname'] ?? '') . " " . ($class['lastname'] ?? '');
            
            // 9. Host School Address
            $hostSchoolAddress = getCompanyAddressWithState( $company_id, $crow );
            
            // 10. Training Address
            $trainingAddress = getTrainingAddress( $class ); // Assumed function
            
            // 11. Host School Street
            $hostSchoolStreet = $crow['address'] ?? '';
            
            // 12. Host School Floor/Room
            $hostSchoolFloorRoom = $crow['floor'] ?? '';
            
            // 13. Host School City
            $hostSchoolCity = $crow['city'] ?? '';
            
            // 14. Host School State
            $hostSchoolState = $crow['state'] ?? '';
            
            // 15. Host School Zip
            $hostSchoolZip = $crow['zip'] ?? '';
            
            // 16. Host School Borough
            $hostSchoolBorough = $crow['borough'] ?? '';

            // 17. Contact Email
            $contactEmail = $class['email'] ?? '';
            
            // 18. Contact Phone
            $contactPhone = $class['phone'] ?? '';
            
            // 19. Notes
            $notes = $class['confirmationnotes'] ?? '';
            
            // 20. School ID
            $schoolId = $company_id;
            
            // 21. Building Permit Number
            $buildingPermitNumber = $class['room_permit_no'] ?? '';
            
            // 22. Requested Trainer
            $requestedTrainer = getFullname( $class['trainerreq'] ?? 0 );
            
            // Prepare data row
            $rowData = [
                $courseDate,
                $course_name,
                $class_url,
                $classNumber,
                $hostSchoolName,
                $hostSchoolNumber,
                $tstr,
                $contact_name,
                $hostSchoolAddress,
                $trainingAddress,
                $hostSchoolStreet,
                $hostSchoolFloorRoom,
                $hostSchoolCity,
                $hostSchoolState,
                $hostSchoolZip,
                $hostSchoolBorough,
                $contactEmail,
                $contactPhone,
                $notes,
                $schoolId,
                $buildingPermitNumber,
                $requestedTrainer
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
    
    fclose($output);
    exit();
    
}
else
{
    // --- HTML Output ---
    include "classreportdailyxls.php"; // Assumed script handles HTML display based on $rep
}
?>
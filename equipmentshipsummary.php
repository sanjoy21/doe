<?php 
require_once('mysql.php');

// Initialize variables from request
$fromdate = $_POST['fromdate']; // Assumed YYYY-MM-DD format
$todate = $_POST['todate'];     // Assumed YYYY-MM-DD format

// --- Report Generation Logic ---
if ($fromdate && $todate) {
    $extra = " AND startdate >= '{$fromdate}' ";

    // Ensure the end date covers the entire day (23:59:59)
    $special = date("Y-m-d 23:59:59", strtotime($todate));
    $extra .= " AND startdate <= '{$special}' ";

    $extra .= " AND deleted = 0";
    $sql = "SELECT class.* FROM class WHERE 1 {$extra} ORDER BY startdate";
    $rep = db_query_rows($sql);

    // --- CSV/XLS Download Headers (Replacing deprecated Excel Writer) ---
    $filename = "classes_" . date('Ymd') . ".csv";
    
    // Set headers for CSV download (will open in Excel/Sheets)
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    
    $output = fopen('php://output', 'w');
    
    // Define Header Row
    $header = array(
        "Company Type", "Course Date", "Course Type", "Class URL", "Class Number", 
        "Host School Name", "Host School Number", "Trainer", "Contact Name", 
        "Host School Address", "Training Address", "Host School Street", 
        "Host School Floor/Room", "Host School City", "Host School State", 
        "Host School Zip", "Host School Borough", "Contact Email", "Contact Phone", 
        "Equipment Scheduled", "Is Conference Room", "Is UPS", "Equipment Notes", 
        "Notes", "Confirmation Notes"
    );
    fputcsv($output, $header);

    // Write Data Rows
    foreach ($rep as $class) {
        $company_id = $class['companyid'];
        $crow = getCompanyRow($company_id); // Assumed external function
        
        $iscorp = $crow['iscorp'];
        $class_code = $class['code'];

        // Get Trainer String
        $trainers = getTrainers($class['id']); // Assumed external function
        $tstr = "";
        $any = false;
        foreach ($trainers as $trainerid => $trow) {
            $tstr .= $any ? ", " : "";
            $tstr .= getFullname($trainerid) . " - " . ($trow['trainerconfirmeddate']); // Assumed external function
            $any = true;
        }

        // Build Data Row
        $data_row = array(
            getUrlPrefix($iscorp), // Assumed external function
            $class['startdate'],
            $allclass_names[$iscorp][$class_code], // Assumed external array/variable
            "http://".SUB_DOE."." . URL_WITHOUT_SUBDOMAIN . "/class_detail.php?id=" . ($class['id']), // Assumed URL constant
            $class['id'],
            getCompanyName($company_id), // Assumed external function
            $crow['schoolcode'],
            $tstr,
            ($class['firstname']) . " " . ($class['lastname']),
            getCompanyAddressWithState($company_id, $crow), // Assumed external function
            getTrainingAddress($class), // Assumed external function
            $crow['address'],
            $crow['floor'],
            $crow['city'],
            $crow['state'],
            $crow['zip'],
            $crow['borough'],
            $class['email'],
            $class['phone'],
            ($class['equipscheduled']) ? "Yes" : "No",
            ($class['isconferenceroom']) ? "Yes" : "No",
            ($class['isups']) ? "Yes" : "No",
            $class['equipnotes'],
            $class['notes'],
            $class['confirmationnotes']
        );
        
        fputcsv($output, $data_row);
    }
    
    fclose($output);
    exit;
}

// --- HTML Form Display Logic ---
?>
<?php include "ssi/top.php"; ?> 
<form method='post'>
Summary between: <?php echo printdates2("fromdate", $fromdate); ?> and <?php echo printdates2("todate", $todate); ?>
<input type='submit' name='go' value='Run Report'>
</form>

<br><br><br><br><br><br><br>

<?php include "ssi/footer.php" ; ?>
 </span>
 </td>
 <td valign="top" width="15"><img src="images/dotclear.gif" width="10" alt=""></td>
 </tr>
</table>
<br><br>
</div>
</body>
</html>
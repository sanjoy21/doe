<?php
// Initialize global control variables
$headernotfixed = 1;
$nologinrequired = true;
$db_link = $GLOBALS['link'] ?? null; // Assumed database connection link

require_once "mysql.php";

// Initialize variables that might come from GET/POST or not be set
$trainerid = $_REQUEST['trainerid'] ?? null;
$id = $_REQUEST['id'] ?? null;
$confirm = $_REQUEST['confirm'] ?? null;
$undo = $_REQUEST['undo'] ?? null;
$deny = $_REQUEST['deny'] ?? null;
$whynot = $_REQUEST['whynot'] ?? null;

// --- Trainer ID Setup ---
// Calculate tid from $trainerid, or set it if the logged-in user is a trainer
if ($trainerid) {
    // $tid is calculated by dividing by 1234 (a simple obfuscation/deobfuscation)
    $tid = (int)($trainerid / 1234);
}

// Check if the current user is a trainer and no trainerid was provided externally
if (!$trainerid && ($thisusersrow["usertype"] ?? '') == "trainer") {
    $tid = $thisusersrow['id'] ?? 0;
    $trainerid = ($thisusersrow['id'] ?? 0) * 1234;
}

$tid_safe = (int)$tid;

// Fetch trainer row
$trow = db_query_first("SELECT * FROM user WHERE id = {$tid_safe}");

// --- Class Request/Update Logic ---
if ($id) {
    $id_safe = (int)$id;

    // Check if a request already exists
    $already = db_query_first_cell("SELECT id FROM requesttotrain WHERE trainerid = {$tid_safe} AND classid = {$id_safe}");
    
    // If request doesn't exist, insert a new request (-4: requested/pending)
    if (!$already) {
        db_query("INSERT INTO requesttotrain (trainerid, classid, requestdate, done, updatedate) VALUES ({$tid_safe}, {$id_safe}, NOW(), -4, NOW())");
    }
    
    // Check current status and re-request if previously denied (-6: previously declined/denied by system)
    $mydone = db_query_first_cell("SELECT done FROM requesttotrain WHERE trainerid = {$tid_safe} AND classid = {$id_safe}");
    if ($mydone == -6) {
        db_query("UPDATE requesttotrain SET done = -4, updatedate = NOW() WHERE trainerid = {$tid_safe} AND classid = {$id_safe}");
    }
}

// Logic for confirming a request (setting done=0)
if ($id && $trainerid && $confirm) {
    db_query("UPDATE requesttotrain SET done = 0, updatedate = NOW() WHERE trainerid = {$tid_safe} AND classid = {$id_safe}");
    exit;
}

// Logic for undoing a request (setting done=-4, removing reason)
if ($id && $trainerid && $undo) {
    db_query("UPDATE requesttotrain SET done = -4, whynot = NULL, updatedate = NOW() WHERE trainerid = {$tid_safe} AND classid = {$id_safe}");
    exit;
}

// Logic for denying a request (setting done=-5, recording reason)
if ($id && $trainerid && $deny) {
    // Escape the reason for the denial
    $whynot_safe = mysqli_real_escape_string($db_link, $whynot ?? ''); 
    db_query("UPDATE requesttotrain SET done = -5, whynot = '{$whynot_safe}', updatedate = NOW() WHERE trainerid = {$tid_safe} AND classid = {$id_safe}");
    exit;
}

// --- Class Details and Status Check for Display ---
if ($id) {
    // Assumed external functions
    $crow = getClassRow($id_safe); 
    $companyrow = getCompanyRow($crow['companyid'] ?? 0);
    // Assumed external global or defined array
    $class_names = $allclass_names[$companyrow["iscorp"] ?? 0] ?? []; 
    
    $alreadyval = db_query_first_cell("SELECT done FROM requesttotrain WHERE trainerid = {$tid_safe} AND classid = {$id_safe}");
    $alreadyreason = db_query_first_cell("SELECT whynot FROM requesttotrain WHERE trainerid = {$tid_safe} AND classid = {$id_safe}");
    
    // If the request is NOT pending (-4), then clear $id to prevent showing class details/request links
    if ($alreadyval != "-4") {
        $id = null;
    }
}


// --- Class Fetching Logic (Near Future) ---
$ignored_codes = "'MHFA', 'AEDI', 'Inspections', 'MHFA', 'TCF Meeting', 'Esinew', 'Misc', 'Trade', 'Call', 'call', 'Office', 'party', 'misc'";
$base_class_condition = "accepted = 1 AND canceldate IS NULL AND code NOT IN ({$ignored_codes}) AND iscorp <> 3 AND companyid = company_esi.id AND isnational = 0 AND companyname NOT LIKE 'Sample%' AND companyname NOT LIKE 'Open Registration'";
$trainer_condition = "class.id NOT IN (SELECT classid FROM trainer_to_class WHERE trainerid = {$tid_safe})";
$trainer_count_condition = "numtrainers > (SELECT COUNT(*) FROM trainer_to_class WHERE classid = class.id)";

// --- Classes 3 Days from Now ---
$threedays = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + 4, date("Y")));
$sql_3days = "SELECT class.* FROM class, company_esi WHERE startdate > NOW() AND startdate <= '{$threedays} 23:59:59' AND {$base_class_condition} AND {$trainer_condition} AND {$trainer_count_condition}"; 
$threedaysfromnow = db_query_rows($sql_3days, "id");

// --- Classes 7 Days from Now ---
$sevendays = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + 7, date("Y")));
$sql_7days = "SELECT class.* FROM class, company_esi WHERE startdate > '{$threedays} 23:59:59' AND startdate <= '{$sevendays} 23:59:59' AND {$base_class_condition} AND {$trainer_condition} AND {$trainer_count_condition}"; 
$sevendaysfromnow = db_query_rows($sql_7days, "id");

// --- Classes 14 Days from Now (Original was 21 days from now - 7 days) ---
$fourteendays = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + 21, date("Y")));
$sql_14days = "SELECT class.* FROM class, company_esi WHERE startdate > '{$sevendays} 23:59:59' AND startdate <= '{$fourteendays} 23:59:59' AND {$base_class_condition} AND {$trainer_condition} AND {$trainer_count_condition}"; 
$fourteendaysfromnow = db_query_rows($sql_14days, "id");

$alreadydisplayed = array(); // Used to track classes already shown in near-future sections

?>

<?php include "ssi/top.php"; ?>
<script src='https://api.tiles.mapbox.com/mapbox-gl-js/v2.1.1/mapbox-gl.js'></script>
 <link href='https://api.tiles.mapbox.com/mapbox-gl-js/v2.1.1/mapbox-gl.css' rel='stylesheet' />
 
 <style>
 table.whatever td { padding: 2px }
#map {
 position: absolute;
 width: 100%;
 border: 1px;
 height: 500px;
 width: 700px;
 display: block;
}
#backme {
 width: 100%;
 border: 1px;
 height: 500px;
 width: 700px;
}

 </style>
 <?php
// --- Dynamic Query Filtering ---
$extra = "";
$fromdate = $_REQUEST['fromdate'] ?? null;
$todate = $_REQUEST['todate'] ?? null;

if ($fromdate) {
 $extra .= " AND startdate >= '{$fromdate}' ";
}
if ($todate) {
 $special = date("Y-m-d 23:59:59", strtotime($todate));
 $extra .= " AND startdate <= '{$special}' ";
}

// Add district filter if available for the trainer
if (!empty($thisusersrow["districts"])) {
    // Assumed external function
 $extra .= getDistrictString($thisusersrow["districts"]); 
}

// Add single school ID filter if available for the trainer
if (!empty($thisusersrow["singleschoolid"])) {
 $single_school_id = (int)($thisusersrow["singleschoolid"] ?? 0);
 $extra .= " AND company_esi.id = {$single_school_id}";
}

// Final filters
$extra .= " AND class.deleted = 0";

// --- General Class List Query (Uses filters) ---
$sql_general = "SELECT class.* FROM class, company_esi WHERE company_esi.id = companyid {$extra} ORDER BY startdate";
$classes = db_query_rows($sql_general);

// --- Output Buffer Start ---
ob_start();
?>
 <table class='whatever' cellpadding=2 cellspacing=0 border=1><tr><th>Class ID</th><th>Date</th><th>Class Type</th><th>Location</th><th>Training Address</th></tr>
<?php
// --- Display Class List ---
foreach ($classes as $crow) {
    $alreadydisplayed[$crow['id']] = $crow;
    $companyrow = getCompanyRow($crow['companyid'] ?? 0);
    $a = getTrainingAddress($crow);
    
    // NOTE: The original Google Maps link is broken/malformed. Replacing with the text address.
    $gmap = htmlspecialchars($a); 
    
    $remote_str = $crow['remote'] ? "<b>REMOTE CLASS</b><br>" : getSchoolStr("Training Location") . ": ";
    
    $class_name_code = $crow["code"] ?? '';
    // Use null coalescing operator for safe array access
    $class_type_name = $class_names[$class_name_code] ?? $class_name_code;
    
    echo "
    <tr>
 <td colspan=\"20\" style=\"height: 1px;\"><a name='class" . ($crow['id'] ?? '') . "'></a></td></tr>
 <tr>
        <tr>
            <td>" . ($crow['id'] ?? '') . "</td>
            <td>
                " . getFormattedDateWTime($crow['startdate'] ?? '') . " " . getEndDateStr($crow['enddate'] ?? '') . "
            </td>
            <td>" . $class_type_name . "</td>
            <td>" . ($companyrow['companyname'] ?? '') . "</td>
            <td>
                {$remote_str} {$gmap} <br>
            </td>
        </tr>
    ";
}
?>
 </table>
<br><br>
<br><br>
<?php
// --- Output Buffer End and Inclusion ---
$cont = ob_get_contents();
ob_end_clean();

// Assumed external file for map logic
include "map-include.php"; 

echo $cont;
?>

 <?php include "ssi/footer.php"; ?>
 </span>
 </td>
 <td valign="top" width="15"><img src="images/dotclear.gif" width="10" alt=""></td>
</tr>
</table>
<br><br>
</div>
</body>
</html>
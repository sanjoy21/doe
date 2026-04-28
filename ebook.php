<?php
require_once('mysql.php');
$crow = getClassRow($id);
$comrow = getCompanyRow($crow["companyid"]);

$filename = "ecards_$id.csv";

header('Content-Type: text/csv; utf-8');
header("Content-Disposition: attachment; filename=" . $filename);
header("Pragma: no-cache");
header("Expires: 0");
$hand = fopen("php://output", "w");
$sd = db_query_first_cell("select startdate from company_esi c, class cl where cl.companyid = c.id and cl.id = '$id'");

if (strtotime($sd) > time()) {
    $res = db_query_rows("select r.* from responder_to_class rtd, responders_esi r where classid = '$id' and r.responderid = rtd.responderid");
} else {
    $res = db_query_rows("select r.*, rtd.trainingdate as thedate from responder_training_dates rtd, responders_esi r where classid = '$id' and r.responderid = rtd.responderid");
}

$iscorp = db_query_first_cell("select iscorp from company_esi c, class cl where cl.companyid = c.id and cl.id = '$id'");

$tmp = array();
$tmp[] = "First Name";
$tmp[] = "Last Name";
$tmp[] = "Email";
$tmp[] = "Group";

fputcsv($hand, $tmp);

foreach ($res as $row) {
    $tmp = array();
    
    $firstName = isset($row["firstname"]) ? $row["firstname"] : "";
    $tmp[] = $firstName;
    
    $lastName = isset($row["lastname"]) ? $row["lastname"] : "";
    $tmp[] = $lastName;
    
    $email = isset($row["email"]) ? $row["email"] : "";
    $tmp[] = $email;
    
    $groupValue = "";
    if ($iscorp) {
        $groupValue = "";
    } else {
        $groupValue = "DOE";
    }
    $tmp[] = $groupValue;
    
    fputcsv($hand, $tmp);
}
fclose($hand);
?>
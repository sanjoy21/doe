<?php
require_once('mysql.php');
$crow = getClassRow($id);
$corow = getCompanyRow($crow["companyid"]);

$tochecktil = date("Y-m-d H:i:s", strtotime($crow["startdate"]) + 7 * 60 * 24 * 60);
$upcomingclasses = db_query_rows("select * from class where companyid = " . (isset($corow["id"]) ? $corow["id"] : 0) . " and startdate > '" . (isset($crow["startdate"]) ? $crow["startdate"] : "") . "' and startdate < '$tochecktil' and deleted = 0 and confirmdate is not null");

$upcomingclasses[] = $crow;

$numtrainers = 0;
$maxtrainers = 0;
$numattendees = 0;
foreach ($upcomingclasses as $tmpclass) {
    $currenttrainers = getTrainers($tmpclass["id"]);
    // $attendees = get_attendees($tmpclass[id]);
    // $numattendees += count($attendees);
    if (isset($tmpclass["maxattendees"])) {
        $numattendees += $tmpclass["maxattendees"];
    }
    if (isset($currenttrainers) && count($currenttrainers) > $numtrainers) {
        $numtrainers = count($currenttrainers);
    }
}
$numprograms = isset($upcomingclasses) ? count($upcomingclasses) : 0;
$nummanikinsperinstructor = 3;
$numsessionsperprogram = 1;

?>
<?php include "ssi/top.php"; ?>
<form method='post' action='packing_sheet.php'>
<input type='hidden' name='id' value='<?php echo htmlspecialchars($id); ?>'>
<table>
<tr><td>Manikins Per Instructor: </td><td><input type='text' size='3' name='nummanikinsperinstructor' value='<?php echo htmlspecialchars($nummanikinsperinstructor); ?>'></td></tr>
<tr><td>Num Programs: </td><td><input type='text' size='3' name='numprograms' value='<?php echo htmlspecialchars($numprograms); ?>'></td></tr>
<tr><td>Num Sessions Per Program: </td><td><input type='text' size='3' name='numsessionsperprogram' value='<?php echo htmlspecialchars($numsessionsperprogram); ?>'></td></tr>
<tr><td>Num Attendees: </td><td><input type='text' size='3' name='numattendees' value='<?php echo htmlspecialchars($numattendees); ?>'></td></tr>
<tr><td>Num Trainers: </td><td><input type='text' size='3' name='numtrainers' value='<?php echo htmlspecialchars($numtrainers); ?>'></td></tr>
</table>
<input type='submit' name='submit' value='Go'>
</form>
<?php include "ssi/bottom.php"; ?>
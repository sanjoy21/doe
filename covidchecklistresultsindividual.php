<?php
include "mysql.php";

// --- 1. Safely Retrieve and Initialize Date Variable ---
// Get date from POST/GET, default to today's date
$dt = $_REQUEST['dt'] ?? date("Y-m-d");
$dt_safe = htmlspecialchars($dt); // Used for display and SQL (assuming YYYY-MM-DD format)

// Include header SSI
include "ssi/top.php";
?>

<form method='post'>
View Results For Date:
<?php echo printdates2("dt", $dt); ?>
<input type='submit' name='go' value='Go'><Br>
</form>

<h3>Daily Class COVID Checklist Results</h3><br>

<table border='1' cellpadding='2' cellspacing='0' width='500'>
<tr>
<th>Class</th>
<th>Attendees</th>
</tr>
<?php

// --- 2. Fetch Accepted Classes for the Selected Date ---
$sql_classes = "SELECT * FROM class WHERE startdate LIKE '{$dt_safe}%' AND accepted = 1 AND deleted = 0";
$classes = db_query_rows($sql_classes);

if (is_array($classes)) {
foreach ($classes as $c) {
$class_id = (int)($c['id'] ?? 0);

$responders = get_attendees($class_id);

if (!count($responders)) {
continue;
}

echo "<tr>";

// Output Class ID (with link)
echo "<td>";
$e_safe = htmlspecialchars($class_id);
echo "<a href='class_detail.php?id={$e_safe}'>{$e_safe}</a>";
echo "</td>";

// Output Attendees Table Header
echo "<td><table style='margin: 2px' border='1' cellspacing='0' >";
echo "<tr><th>Email</th><th>Q1</th><th>Q2</th><th>Q3</th><th>Q4</th><th>Date</th></tr>";

// --- 3. Iterate through Attendees and Fetch Individual Checklist Results ---
foreach ($responders as $tmp) {
$responder_id = (int)($tmp['responderid'] ?? 0);
$rrow = get_attendee($responder_id);

$attendee_email = htmlspecialchars($rrow['email'] ?? '');

$sql_checklist = "SELECT * FROM covidquestionsindividual 
                              WHERE classid = {$class_id} AND userid = '{$attendee_email}' 
                              ORDER BY dateadded DESC LIMIT 1";
$row = db_query_first($sql_checklist);

echo "<tr><td>{$attendee_email}</td>";

if ($row && ($row['userid'] ?? null)) {
// Results Found
$date_added = htmlspecialchars($row['dateadded'] ?? '');

$q1_output = ($row['q1']) ? "<font color='red'>Yes</font>" : "No";
$q2_output = ($row['q2']) ? "<font color='red'>Yes</font>" : "No";
$q3_output = ($row['q3']) ? "<font color='red'>Yes</font>" : "No";
$q4_output = ($row['q4']) ? "<font color='red'>Yes</font>" : "No";

$style = "style='padding: 10px 10px 10px 10px' ";

echo "<td {$style}>{$q1_output}</td>";
echo "<td {$style}>{$q2_output}</td>";
echo "<td {$style}>{$q3_output}</td>";
echo "<td {$style}>{$q4_output}</td>";
echo "<td {$style}>{$date_added}</td>";
} else {
// Results Not Found
$style = "style='padding: 10px 10px 10px 10px' colspan='5'";
echo "<td {$style}><font color='red'>Did Not Answer</font></td>";
}
echo "</tr>";
}

// Close Attendees table
echo "</table></td></tr>";
}
}
?>

</table>
<br><br><br>
<br><br><br><br><br><br><br>

<!--end center content-->

<?php include "ssi/footer.php"; ?>

<!--end footer-->
</span>
</td>
<td valign="top" width="15"><img src="images/dotclear.gif" width="10"></td>
</tr>
</table>
<br><br>
</div>
</body>
</html>
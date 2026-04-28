<?php
include "mysql.php";
// Included functionality notes (original comments) have been removed from the final executable PHP block.

// --- 1. Top SSI Inclusion ---
include "ssi/top.php";

// --- 2. Class COVID Checklist Results Table ---
?>
<h3>Class COVID Checklist Results</h3><br>
<table border='1' cellpadding='2' cellspacing='0' width='500'>
    <tr>
        <th>Class</th>
        <th>Q1</th>
        <th>Q2</th>
        <th>Q3</th>
        <th>Q4</th>
        <th>Q5</th>
        <th>Date</th>
        <th>User</th>
    </tr>
<?php
// Fetch results where classid is NOT empty
$res = db_query_rows( "SELECT * FROM covidquestions WHERE classid > '' ORDER BY dateadded DESC" );

if (is_array($res)) {
    foreach( $res as $row )
    {
        $class_ids_raw = $row['classid'];
        $date_added = htmlspecialchars($row['dateadded']);
        $user_id = htmlspecialchars($row['userid']);

        // Determine output color based on the question result (1/true is dangerous, usually red)
        $q1_output = ($row['q1']) ? "<font color='red'>Yes</font>" : "No";
        $q2_output = ($row['q2']) ? "<font color='red'>Yes</font>" : "No";
        $q3_output = ($row['q3']) ? "<font color='red'>Yes</font>" : "No";
        $q4_output = ($row['q4']) ? "<font color='red'>Yes</font>" : "No";
        // Q5 is treated inversely in the original logic (Yes=Good, No=Bad)
        $q5_output = ($row['q5']) ? "Yes" : "<font color='red'>No</font>";


        echo "<tr>";

        // Output linked class IDs
        echo "<td>";
        $exp = explode( ",", $class_ids_raw );
        foreach( $exp as $e )
        {
            $e_safe = htmlspecialchars(trim($e));
            if (!empty($e_safe)) {
                echo "<a href='class_detail.php?id={$e_safe}'>{$e_safe}</a>; ";
            }
        }
        echo "</td>";

        // Output question results
        echo "<td>{$q1_output}</td>";
        echo "<td>{$q2_output}</td>";
        echo "<td>{$q3_output}</td>";
        echo "<td>{$q4_output}</td>";
        echo "<td>{$q5_output}</td>";
        
        // Output date and user ID
        echo "<td>{$date_added}</td>";
        echo "<td>{$user_id}</td>";
        echo "</tr>";
    }
}
?>
</table>

<br><br><br>

<h3>Daily COVID Checklist Results</h3><br>
<table border='1' cellpadding='2' cellspacing='0' width='500'>
    <tr>
        <th>Q1</th>
        <th>Q2</th>
        <th>Q3</th>
        <th>Q4</th>
        <th>Q5</th>
        <th>Date</th>
        <th>User</th>
    </tr>
<?php
// Fetch results where classid IS empty (daily/general checklist)
$res = db_query_rows( "SELECT * FROM covidquestions WHERE classid = '' ORDER BY dateadded DESC" );

if (is_array($res)) {
    foreach( $res as $row )
    {
        $date_added = htmlspecialchars($row['dateadded'] ?? '');
        $user_id = htmlspecialchars($row['userid'] ?? '');

        // Determine output color based on the question result
        $q1_output = ($row['q1']) ? "<font color='red'>Yes</font>" : "No";
        $q2_output = ($row['q2']) ? "<font color='red'>Yes</font>" : "No";
        $q3_output = ($row['q3']) ? "<font color='red'>Yes</font>" : "No";
        $q4_output = ($row['q4']) ? "<font color='red'>Yes</font>" : "No";
        // Q5 is treated inversely in the original logic (Yes=Good, No=Bad)
        $q5_output = ($row['q5']) ? "Yes" : "<font color='red'>No</font>";

        echo "<tr>";
        // Output question results
        echo "<td>{$q1_output}</td>";
        echo "<td>{$q2_output}</td>";
        echo "<td>{$q3_output}</td>";
        echo "<td>{$q4_output}</td>";
        echo "<td>{$q5_output}</td>";
        // Output date and user ID
        echo "<td>{$date_added}</td>";
        echo "<td>{$user_id}</td>";
        echo "</tr>";
    }
}
?>
</table>
<br><br><br><br><br><br><br>
<!--end center content-->
<?php include "ssi/footer.php" ; ?>
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
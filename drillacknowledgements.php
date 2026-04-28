<?php 
require "mysql.php"; 
include "ssi/top.php"; 

// Get the database connection link for safe queries
$db_link = $GLOBALS['link'] ?? $link; 

// --- The commented section describes the intended functionality for a health check / alert system.

?>
<h3>Drill Acknowledgment Results</h3><br>
<table border=1 cellpadding=2 cellspacing=0 width='500'>
<tr>
<th>Date</th>
<th>School</th>
<th>Email</th>
<th>Status</th>
<th>Comments</th>
</tr>
<?php
// Assuming getCompanyName() exists and is safe
$res = db_query_rows( "SELECT * FROM oktodrill WHERE email > '' ORDER BY dateadded DESC" );

foreach( $res as $row )
{
    // PHP 8.2 Fixes: Quoted array keys and htmlspecialchars() for output
    $dateadded_safe = htmlspecialchars($row['dateadded'] ?? '');
    $schoolid = (int)($row['schoolid'] ?? 0);
    $email_safe = htmlspecialchars($row['email'] ?? '');
    $status = (int)($row['status'] ?? 0);
    $comments_safe = htmlspecialchars($row['comments'] ?? '');

    // Get Company Name
    // Assuming getCompanyName() handles null/zero IDs safely.
    $company_name_safe = htmlspecialchars(getCompanyName( $schoolid ));

    // Status display
    $status_display = ($status < 0) 
        ? "<span style='color:red;'>No</span>" 
        : "Yes";

    echo( "<tr>" );
    echo( "<td>{$dateadded_safe}</td>" );
    echo( "<td>" );
    echo( "<a href='viewcompany.php?id={$schoolid}'>{$company_name_safe}</a>" );
    echo( "</td>" );
    echo( "<td>{$email_safe}</td>" );
    echo( "<td>{$status_display}</td>" );
    echo( "<td>{$comments_safe}</td>" );
    echo( "</tr>" );
}
?>

</table>
 </span>
 <br><br></td></tr>

</td></tr>
</table>


<br><br><br><br><br><br><br>

<?php include "ssi/footer.php" ; ?>
</span>
 </td>
 <td valign="top" width="15"><img src="images/dotclear.gif" width="10"></td>
</tr>
</table>
<br><br>
</div>
</body>
</html>
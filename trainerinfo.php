<?php
// 465-3637
require_once('mysql.php');

// Query all trainer information records, ordered by date added (newest first)
$trainers = db_query_rows( "SELECT * FROM trainerinfo WHERE 1 ORDER BY dateadded DESC" ); 
?>
<?php include "ssi/top.php"; ?>
<p>
<strong><span class="title">INFORMATION FOR TRAINERS</span></strong>
</p>

<table cellpadding="4" cellspacing="1" border="0" width="100%" bgcolor="#999999" class="table3" >
<tr bgcolor="#e1e1f6"><th class='copy'>Title</th><th class='copy'>Date Added</th><th class='copy'>Comments</th>
</tr>
<?php 
foreach( $trainers as $t )
{
    // PHP 8.2 Fix/Security: Quote array keys and use htmlspecialchars()
    $trainer_info_id = htmlspecialchars($t['id'] ?? 'N/A');
    $title = htmlspecialchars($t['title'] ?? 'N/A');
    $date_added = htmlspecialchars($t['dateadded'] ?? 'N/A');

    echo( "<tr bgcolor='white'>" );
    echo( "<td><a href='viewtrainerinfo.php?trainerinfoid=" . $trainer_info_id . "'>" . $title . "</a></td>" );
    echo( "<td>" . $date_added . "</td>" );
    
    // PHP 8.2 Fix: Quote array key 'id' in the SQL query
    $c = db_query_first_cell( "SELECT count(*) FROM trainercomments WHERE trainerinfoid = " . ($t['id'] ?? 0) );
    
    echo( "<td>" . htmlspecialchars($c ?? 0) . "</td>" );
    echo( "</tr>" );
}
?>
</table>
<?php include "ssi/footer.php" ; ?>
<!--end footer-->
<?php 
include "mysql.php";

// Query to find duplicate building codes and their counts
$sql = "select count(*) c, buildingcode from buildings group by buildingcode having c > 1";
$res = db_query_rows( $sql );

include "ssi/top.php"; 

echo( "<table>" );
foreach( $res as $r )
{
    // Safely access quoted array keys
    $buildingcode_safe = $r["buildingcode"] ?? '';
    $count_safe = $r["c"] ?? 0;
    
    // Output table row with link to search for the duplicate code
    echo( "<tr><td><a href='buildings.php?search=1&substr=" . urlencode($buildingcode_safe) . "'>" . htmlspecialchars($buildingcode_safe) . "</a></td><td>" . (int)$count_safe . "</td></tr>" );
}
echo( "</table>" );
?>
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
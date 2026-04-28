<?php
require_once('mysql.php');
?>
<?php include "ssi/top.php"; ?>

<p>
<strong><span class="title">View</span></strong>
</p>

<table cellpadding="4" cellspacing="0" border="1" width="100%" class="table3">
<?php 
// Fetches log entries for the given ID.
// Note: $id should be properly sanitized before use in a real application.
$rows = db_query_rows( "select * from employeeslog where id = '" . $id . "' order by dateadded desc" );

foreach( $rows as $row )
{
    // Array keys are now quoted for PHP 8.2 compatibility
    echo( "<tr><td>" . ($row["dateadded"]) . "</td><td>" . nl2br($row["response"]) . "</td></tr>" );
}
?>
</table>

<br><br><br>
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
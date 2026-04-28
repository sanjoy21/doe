<?php
$nologinrequired = true;
require_once "mysql.php";

db_query( "update class set notesconfirmed = now() where id = '" . ($id ?? '') . "'" );
?>

<?php include "ssi/top.php";?>

Thanks! Your notes are confirmed. 
<br><br><br><br><br><br><br><br>
<br><br><br><br><br><br><br><br>
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
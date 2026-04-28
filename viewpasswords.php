<?php
require_once('mysql.php');
?>
<?php include "ssi/top.php";?>
<p><strong><span class="title">VIEW Passwords</span></strong></p>
<table class="table3" cellpadding="4" cellspacing="1" border="0" width="100%" bgcolor="#999999">
<tr bgcolor="#e1e1f6"><th class='copy'>Agency/Website</th><th class='copy'>Username</th><th class='copy'>Password</th></tr>
<?php
$us = db_query_rows("select * from passwords order by name");
foreach($us as $t)
{
echo( "<tr bgcolor='white'><td align='center' valign='top'>".$t["description"]."</td>\n" );
echo( "<td class='copy' valign='top'>".$t["name"]."</td>" );
echo( "<td align='center' valign='top'>".$t["value"]."</td>\n" );
}
?>
</table></p>
<?php include "ssi/footer.php";?>
</span>
</td>
<td valign="top" width="15"><img src="images/dotclear.gif" width="10"></td>
</tr>
</table>
<br><br>
</div>
</body>
</html>
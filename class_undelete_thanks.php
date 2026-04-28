<?php
require_once('mysql.php');

$id_safe = $id ?? null;
$sql = "UPDATE class SET deleted = '0', canceldate = null WHERE id = '" . $id_safe . "' LIMIT 1";
$ret = mysqli_query($link, $sql);
?>

<?php include "ssi/top.php"; ?>
<strong><span class="title">Thank you.</span></strong>
<p>
The class has been re-added.</p>
<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>

<?php include "ssi/footer.php"; ?>

</span>
</td>
<td valign="top" width="15"><img src="images/dotclear.gif" width="10"></td>
</tr>
</table>
<br><br>
</div>
</body>

</html>
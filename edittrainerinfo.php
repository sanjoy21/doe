<?php
include "mysql.php";

// Initialize variables to avoid undefined variable warnings
$update = $_POST['update'] ?? null;
$trainerinfoid = $_POST['trainerinfoid'] ?? ($_GET['trainerinfoid'] ?? null);
$title = $_POST['title'] ?? '';
$body = $_POST['body'] ?? '';

if ($update) {
if ($trainerinfoid) {
// Update existing record
$escaped_title = $title;
$escaped_body = $body;
$trainerinfoid = $trainerinfoid;
db_query("UPDATE trainerinfo SET title = '$escaped_title', body = '$escaped_body', dateupdated = NOW() WHERE id = $trainerinfoid");
} else {
// Insert new record
$escaped_title = $title;
$escaped_body = $body;
$trainerinfoid = db_query_insert_id("INSERT INTO trainerinfo (title, body, dateadded) VALUES ('$escaped_title', '$escaped_body', NOW())");
}

Header("Location: managetrainerinfo.php");
exit;
}

// Get info for the form
if ($trainerinfoid) {
$trainerinfoid = (int)$trainerinfoid;
$trainerinfo_row = db_query_first("SELECT * FROM trainerinfo WHERE id = $trainerinfoid");
}

if (!isset($trainerinfo_row) || !$trainerinfo_row) {
$trainerinfo_row = array('title' => '', 'body' => '');
}

include "ssi/top.php";
?>
<!--start center content-->
</head>
<form method="post">
<input type="hidden" name="update" value="true">
<input type="hidden" name="trainerinfoid" value="<?php echo htmlspecialchars($trainerinfoid ?? ''); ?>">

<script LANGUAGE="JavaScript1.1" SRC="/FormChek.js"></script>
<script language="JavaScript">
<!--
function validateUSPersonalInfo(form)
{ 
return true;
}
//-->
</script>

<table cellpadding="5" cellspacing="1" border="0" width="100%" class="table3">
<tr>
<td valign="top" bgcolor="#5a179e" colspan="2"><span class="white"><strong>Information For Trainers</strong></span></td>
</tr>

<tr>
<td valign="top" bgcolor="#E2DFDF">
<span class="copy"><strong>Title:</strong><br>
<input type="text" size="40" value="<?php echo htmlspecialchars($trainerinfo_row['title']); ?>" name="title" style="font-size: 10px; font-family: verdana;"></span>
</td>
</tr>

<tr>
<td valign="top" bgcolor="#E2DFDF">
<span class="copy"><strong>Body:</strong><br>
<textarea cols='100' rows='20' name='body'><?php echo htmlspecialchars($trainerinfo_row['body']); ?></textarea></span>
</td>
</tr>

<tr>
<td valign="top" bgcolor="#FFFFFF" colspan="2">
<br>
<div align="center">
<input type="submit" value="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Save&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
</div>
</td>
</tr>
</table>

<br><br>

<?php
if ($trainerinfoid) {
echo '<h3>Existing Comments</h3>';
echo '<table border="1" cellspacing="0" cellpadding="4" width="100%">';

$trainerinfoid = (int)$trainerinfoid;
$res = db_query_rows("SELECT * FROM trainercomments WHERE trainerinfoid = '$trainerinfoid' ORDER BY dateadded DESC");

foreach ($res as $r) {
$trainer_name = getUserName($r['trainerid'] ?? 0);
$date_added = $r['dateadded'] ?? '';
$comment_body = nl2br(htmlspecialchars($r['body'] ?? ''));

echo '<tr>';
echo '<td valign="top">' . htmlspecialchars($trainer_name) . '<br>' . htmlspecialchars($date_added) . '</td>';
echo '<td>' . $comment_body . '</td>';
echo '</tr>';
}

echo '</table>';
}
?>

<br><br>

<?php include "ssi/footer.php"; ?>
</span>
</td>
<td valign="top" width="15"><img src="images/dotclear.gif" width="10" alt=""></td>
</tr>
</table>
<br><br>
</div>
</form>
</body>
</html>
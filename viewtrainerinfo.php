<?php 
include "mysql.php"; 

// --- Security Helper Functions ---
// Helper for SQL escaping (Assumes use of mysqli_real_escape_string or equivalent upstream)
if (!function_exists('db_escape_or_placeholder')) {
    function db_escape_or_placeholder($str) {
        return addslashes((string)($str ?? '')); 
    }
}
// Helper for HTML escaping (XSS mitigation)
if (!function_exists('h')) {
    function h($str) {
        return htmlspecialchars((string)($str ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
// ---------------------------------

// Sanitize inputs first
$trainerinfoid = (int)($_REQUEST['trainerinfoid'] ?? 0); 
$update = $_POST['update'] ?? false;

if( $update )
{
    // Sanitize string body and ensure IDs are numeric
    $session_id_safe = (int)($session_id ?? 0);
    $body_safe = db_escape_or_placeholder($_POST['body'] ?? '');
    
    // SQLi Mitigation: Use sanitized/casted variables
    db_query( "insert into trainercomments ( trainerid, dateadded, trainerinfoid, body ) values ( {$session_id_safe}, now(), {$trainerinfoid}, '{$body_safe}' );" );
}


// get info for the form
if( $trainerinfoid )
{
    // SQLi Mitigation: $trainerinfoid is cast to integer
    $trainerinfo_row = db_query_first( "select * from trainerinfo where id = {$trainerinfoid}" );
} else {
    $trainerinfo_row = []; // Initialize to prevent errors
}

// Ensure row variables are safe strings for output
$trainerinfo_title = h($trainerinfo_row['title'] ?? '');
$trainerinfo_body = $trainerinfo_row['body'] ?? '';
?>
<?php
include "ssi/top.php"; ?>
</head>
<form method="post">
<input type="hidden" name ="update" value="true">
<input type="hidden" name ="trainerinfoid" value="<?=h($trainerinfoid)?>">
<script LANGUAGE="JavaScript1.1" SRC="/FormChek.js"></script>
<table class="table3" cellpadding="5" cellspacing="1" border="0" width="100%">
<tr>
<td valign="top" bgcolor="#5a179e" colspan="2"><span class="white"><strong>Information For Trainers</strong></span></td>
</tr>
<tr>
<td valign="top" bgcolor="#E2DFDF">
<span class="copy"><strong>Title:</strong><br><?=$trainerinfo_title?></span></td>
</tr>

<tr>
<td valign="top" bgcolor="#E2DFDF">
<span class="copy"><?=nl2br(h($trainerinfo_body))?></span></td>
</tr>
<tr>
<td valign="top" bgcolor="#E2DFDF">
<span class="copy"><Strong>Add New Comment: </strong><br><textarea cols='100' rows=10 name='body'></textarea></span></td>
</tr>
<tr>
<td valign="top" bgcolor="#FFFFFF" colspan="2">
<br>
<div align="center">
<input onClick="return confirm( 'Are you sure you want to add this comment?' )" name ='update' type="submit" value="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Save&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
</td>
</tr>
</table>
<br><br>
<?php if( $trainerinfoid ) { ?>
<h3>Existing Comments</h3>
<table border='1' cellspacing=0 cellpadding='4' width='100%'>
<?php
// SQLi Mitigation: $trainerinfoid is cast to integer
$res = db_query_rows( "select * from trainercomments where trainerinfoid = {$trainerinfoid} order by dateadded desc" );

foreach( $res as $r )
{
    // XSS Mitigation: Escape all output
    $trainer_name_safe = h(getUserName( $r['trainerid'] ));
    $date_added_safe = h($r['dateadded']);
    $body_safe = h($r['body']);
?>
<tr>
<td valign='top'><?=$trainer_name_safe?><br><?=$date_added_safe?></td>
<td><?=nl2br($body_safe)?></td>
</tr>
<?php } ?>
</table>
<?php } ?>
<br><br>
<?php include "ssi/footer.php"; ?>
</span>
</td>
<td valign="top" width="15"><img src="images/dotclear.gif" width="10"></td>
</tr>
</table>
<br><br>
</div>
</form>
</body>
</html>
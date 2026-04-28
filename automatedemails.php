<?php
require_once('mysql.php');

$sentto_safe = $sentto ?? '';
?>
<?php include "ssi/top.php"; ?>

<strong><span class="title">Emails Sent</span></strong>
<p>
<form method='get'>
Emails sent to: <input type='text' name='sentto' value='<?php echo htmlspecialchars($sentto_safe); ?>'>
<input type='submit' name='go' value='Go'>
</form>

<?php if( $sentto_safe ) { ?>
<table>
<tr><td>Date Sent</td><td>Email Key</td><td>Subject</td><td>Body</td></tr>
<?php 
// Query the database for emails sent to the specified recipient
$res = db_query_rows( "select * from automatedemails where torecipients = '" . $sentto_safe . "' order by datesent desc" );

foreach( $res as $r )
{
?>
<tr>
<td><?php echo $r["datesent"] ?? ''; ?></td>
<td><?php echo $r["emailkey"] ?? ''; ?></td>
<td><?php echo $r["subject"] ?? ''; ?></td>
<td><textarea cols='80' rows='10'><?php echo strip_tags( $r["body"] ?? '' ); ?></textarea></td>
</tr>
<?php
}
if( !count( $res ) ) {
    echo( "<tr><td>None Sent</td></tr>" );
}
?>
</table>

<?php } ?>